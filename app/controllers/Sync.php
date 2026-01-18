<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Sync Controller
 *
 * Handles unified sync and analysis workflow
 */

/**
 * Load analysis functions from Analyze controller if not already loaded
 */
function loadAnalysisFunctions() {
    if (!function_exists('discoverAreas')) {
        $GLOBALS['LOADING_FUNCTIONS_ONLY'] = true;
        require_once(APPPATH . 'controllers/Analyze.php');
        unset($GLOBALS['LOADING_FUNCTIONS_ONLY']);
    }
}

$repoModel = new Repository();
$issueModel = new Issue();
$areaModel = new Area();
$jobModel = new AnalysisJob();
$segment1 = $glob['route'][1] ?? '';
$segment2 = $glob['route'][2] ?? '';

// Get current user
$user = User::getCurrentUser();

// Route: /sync/run/{repo_id} - Start unified sync & analysis
if ($segment1 === 'run' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment2;

    // Load repository
    $repo = $repoModel->findById($repoId);

    if (!$repo) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Repository not found']);
        exit;
    }

    // Check for existing incomplete job
    $existingJob = $jobModel->findActive($repoId);

    if ($existingJob && !isset($_POST['force_restart'])) {
        // Return existing job for user to decide
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'has_existing_job' => true,
            'job_id' => $existingJob['id'],
            'status' => $existingJob['status'],
            'processed' => $existingJob['processed_issues'],
            'total' => $existingJob['total_issues']
        ]);
        exit;
    }

    // Cancel old job if restarting
    if ($existingJob && isset($_POST['force_restart'])) {
        $jobModel->fail($existingJob['id'], 'Restarted by user');
    }

    // Create new job with 'syncing' status
    $jobId = $jobModel->create($repoId, 0); // Total issues unknown until sync completes

    // Return JSON for AJAX handling
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'has_existing_job' => false,
        'job_id' => $jobId
    ]);
    exit;
}

// Route: /sync/process-sync/{job_id} - Sync issues from GitHub
if ($segment1 === 'process-sync' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $jobId = (int)$segment2;
    $job = $jobModel->findById($jobId);

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }

    try {
        // Get repository
        $repo = $repoModel->findById($job['repository_id']);

        // Initialize GitHub API
        $githubToken = $user ? $user->getDecryptedToken() : null;
        $github = new GitHubAPI($githubToken);

        // Fetch issues from GitHub with bug label filter
        $filters = [
            'state' => 'open',
            'labels' => $repo['bug_label']
        ];

        $githubIssues = $github->getIssues($repo['owner'], $repo['name'], $filters);

        // Get existing issues for comparison
        $existingIssues = $issueModel->findByRepository($repo['id']);
        $existingMap = [];
        foreach ($existingIssues as $issue) {
            $existingMap[$issue['github_issue_id']] = $issue;
        }

        // Build map of GitHub issue IDs that are currently open
        $githubIssueIds = [];
        foreach ($githubIssues as $githubIssue) {
            $githubIssueIds[$githubIssue['id']] = true;
        }

        // Track sync stats
        $stats = [
            'added' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'removed' => 0,
            'total' => count($githubIssues)
        ];

        // Process each GitHub issue
        foreach ($githubIssues as $githubIssue) {
            $githubId = $githubIssue['id'];

            if (isset($existingMap[$githubId])) {
                // Issue exists - check if updated
                $existingIssue = $existingMap[$githubId];
                $githubUpdatedAt = strtotime($githubIssue['updated_at']);

                if ($githubUpdatedAt > $existingIssue['updated_at']) {
                    // Issue was updated on GitHub - update locally and clear analysis
                    $issueModel->update($existingIssue['id'], [
                        'title' => $githubIssue['title'],
                        'body' => $githubIssue['body'] ?? '',
                        'state' => $githubIssue['state'],
                        'updated_at' => $githubUpdatedAt,
                        'labels' => json_encode(array_column($githubIssue['labels'], 'name')),
                        'label_colors' => json_encode(array_combine(
                            array_column($githubIssue['labels'], 'name'),
                            array_column($githubIssue['labels'], 'color')
                        ) ?: []),
                        'assignees' => json_encode(array_column($githubIssue['assignees'] ?? [], 'login')),
                        'milestone' => isset($githubIssue['milestone']) ? $githubIssue['milestone']['title'] : null,
                        'comments_count' => $githubIssue['comments'] ?? 0,
                        'reactions_total' => ($githubIssue['reactions']['total_count'] ?? 0),
                        'is_locked' => $githubIssue['locked'] ? 1 : 0,
                        'analyzed_at' => null // Clear analysis since issue changed
                    ]);
                    $stats['updated']++;
                } else {
                    // Issue unchanged
                    $stats['unchanged']++;
                }
            } else {
                // New issue - create it
                $issueModel->create($repo['id'], $githubIssue);
                $stats['added']++;
            }
        }

        // Remove issues that are no longer open on GitHub
        foreach ($existingIssues as $existingIssue) {
            if (!isset($githubIssueIds[$existingIssue['github_issue_id']])) {
                // Issue is closed or no longer matches bug label - remove it
                $issueModel->delete($existingIssue['id']);
                $stats['removed']++;
            }
        }

        // Update repository last_audited_at
        $repoModel->update($repo['id'], [
            'last_audited_at' => time()
        ]);

        // Update job to 'processing' status and set total issues
        $allIssues = $issueModel->findByRepository($repo['id']);
        $jobModel->update($jobId, [
            'status' => 'processing',
            'total_issues' => count($allIssues),
            'current_step' => 'sync_complete'
        ]);

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    } catch (Exception $e) {
        error_log('Sync error: ' . $e->getMessage());
        $jobModel->fail($jobId, $e->getMessage());

        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Route: /sync/check-areas/{job_id} - Check if area discovery is needed
if ($segment1 === 'check-areas' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $jobId = (int)$segment2;
    $job = $jobModel->findById($jobId);

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }

    try {
        // Check if areas exist
        $areas = $areaModel->findByRepository($job['repository_id']);

        if (empty($areas)) {
            // Need to discover areas
            $issues = $issueModel->findByRepository($job['repository_id']);

            // Load analysis functions
            loadAnalysisFunctions();

            $discoveredAreas = discoverAreas($job['repository_id'], $issues);

            if ($discoveredAreas) {
                // Store for approval modal
                $_SESSION['pending_areas'] = [
                    'repo_id' => $job['repository_id'],
                    'areas' => $discoveredAreas,
                    'job_id' => $jobId
                ];

                // Update job status
                $jobModel->updateProgress($jobId, 0, 'awaiting_area_approval');

                echo json_encode([
                    'success' => true,
                    'needs_area_approval' => true
                ]);
            } else {
                throw new Exception('Failed to discover areas');
            }
        } else {
            // Areas exist, proceed to analysis
            echo json_encode([
                'success' => true,
                'needs_area_approval' => false
            ]);
        }
    } catch (Exception $e) {
        error_log('Area check error: ' . $e->getMessage());
        $jobModel->fail($jobId, $e->getMessage());

        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Route: /sync/process-analyze/{job_id} - Analyze issues that need analysis
if ($segment1 === 'process-analyze' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $jobId = (int)$segment2;
    $job = $jobModel->findById($jobId);

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }

    try {
        // Get repository and areas
        $repo = $repoModel->findById($job['repository_id']);
        $areas = $areaModel->findByRepository($job['repository_id']);

        // Get issues needing analysis (new or updated since last analysis)
        $needsAnalysis = $issueModel->findNeedingAnalysis($job['repository_id']);

        if (empty($needsAnalysis)) {
            // All done, finalize
            $jobModel->complete($jobId);

            // Run final analysis aggregation (duplicates, etc.)
            set_time_limit(300); // 5 minutes for final analysis
            loadAnalysisFunctions();

            $allIssues = $issueModel->findByRepository($job['repository_id']);
            $results = runAnalysis($job['repository_id'], $allIssues);
            $_SESSION['analysis_results'] = $results;

            echo json_encode([
                'success' => true,
                'completed' => true,
                'processed' => $job['processed_issues'],
                'total' => $job['total_issues']
            ]);
            exit;
        }

        // Process chunk (5 issues at a time)
        $chunkSize = 5;
        $chunk = array_slice($needsAnalysis, 0, $chunkSize);

        // Analyze chunk
        loadAnalysisFunctions();
        $success = analyzeIssueChunk($job['repository_id'], $chunk, $areas);

        if ($success) {
            $processed = $job['processed_issues'] + count($chunk);
            $jobModel->updateProgress($jobId, $processed, "Analyzing issues");

            echo json_encode([
                'success' => true,
                'completed' => false,
                'processed' => $processed,
                'total' => $job['total_issues'],
                'percent' => round(($processed / $job['total_issues']) * 100)
            ]);
        } else {
            throw new Exception('Failed to analyze chunk');
        }
    } catch (Exception $e) {
        error_log('Analysis error: ' . $e->getMessage());
        $jobModel->fail($jobId, $e->getMessage());

        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
