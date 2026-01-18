<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Audit Controller
 *
 * Handles repository audit operations (fetching and storing issues)
 */

$repoModel = new Repository();
$issueModel = new Issue();
$segment1 = $glob['route'][1] ?? '';
$segment2 = $glob['route'][2] ?? '';

// Route: /audit/run/{repo_id}
if ($segment1 === 'run' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment2;

    // Return JSON for AJAX requests
    header('Content-Type: application/json');

    // Load repository
    $repo = $repoModel->findById($repoId);

    if (!$repo) {
        echo json_encode(['success' => false, 'error' => 'Repository not found']);
        exit;
    }

    try {
        // Initialize GitHub API
        $github = new GitHubAPI(getGitHubToken());

        // Fetch issues from GitHub with bug label filter
        $filters = [
            'state' => 'open',
            'labels' => $repo['bug_label']
        ];

        $githubIssues = $github->getIssues($repo['owner'], $repo['name'], $filters);

        if ($githubIssues === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch issues from GitHub']);
            exit;
        }

        // Clear existing issues for this repository
        $issueModel->deleteByRepository($repoId);

        // Store issues locally
        $issueCount = 0;
        foreach ($githubIssues as $githubIssue) {
            if ($issueModel->create($repoId, $githubIssue)) {
                $issueCount++;
            }
        }

        // Update last_audited_at timestamp
        $repoModel->update($repoId, [
            'last_audited_at' => time()
        ]);

        echo json_encode([
            'success' => true,
            'issue_count' => $issueCount,
            'repo_name' => $repo['full_name']
        ]);
    } catch (Exception $e) {
        error_log('Audit error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// If we get here, invalid route
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid route']);
exit;
