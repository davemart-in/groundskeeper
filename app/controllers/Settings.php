<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Settings Controller
 *
 * Handles settings page and related actions
 */

$repoModel = new Repository();
$segment1 = $glob['route'][1] ?? '';
$segment2 = $glob['route'][2] ?? '';

// Route: /settings/add-repo - Add new repository
if ($segment1 === 'add-repo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoSlug = $_POST['repo_slug'] ?? '';

    $parsed = Repository::parseSlug($repoSlug);

    if (!$parsed) {
        $_SESSION['error'] = 'Invalid repository format. Use: owner/repo-name';
        redirect('settings');
        exit;
    }

    // Check if repo already exists
    $existing = $repoModel->findByOwnerName($parsed['owner'], $parsed['name']);
    if ($existing) {
        $_SESSION['error'] = 'Repository already added';
        redirect('settings/' . $existing['id']);
        exit;
    }

    try {
        // Auto-detect bug label from GitHub
        $githubApi = new GitHubAPI(getGitHubToken());
        $bugLabel = Repository::detectBugLabel($parsed['owner'], $parsed['name'], $githubApi);

        // Create repository with detected bug label
        $newRepo = $repoModel->create([
            'owner' => $parsed['owner'],
            'name' => $parsed['name'],
            'bug_label' => $bugLabel
        ]);

        // Mark as synced
        $repoModel->update($newRepo['id'], [
            'priority_labels' => json_encode([]),
            'last_synced_at' => time()
        ]);

        $_SESSION['success'] = 'Repository added successfully!';
        redirect('settings/' . $newRepo['id']);
    } catch (Exception $e) {
        error_log('Add repo error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to add repository';
        redirect('settings');
    }
    exit;
}

// Route: /settings/{repo_id}/update - Update repository settings
if (is_numeric($segment1) && $segment2 === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment1;
    $bugLabel = $_POST['bug_label'] ?? 'bug';

    $repo = $repoModel->findById($repoId);
    if (!$repo) {
        $_SESSION['error'] = 'Repository not found';
        redirect('settings');
        exit;
    }

    try {
        $updateData = ['bug_label' => $bugLabel];

        // Handle priority labels from textarea
        if (isset($_POST['priority_labels_text'])) {
            $text = trim($_POST['priority_labels_text']);
            if (!empty($text)) {
                $lines = explode("\n", $text);
                $labels = array_filter(array_map('trim', $lines));
                $updateData['priority_labels'] = json_encode(array_values($labels));
            } else {
                // Empty textarea = no priority labels
                $updateData['priority_labels'] = json_encode([]);
            }
        }

        $repoModel->update($repoId, $updateData);
        $_SESSION['success'] = 'Settings updated successfully!';
    } catch (Exception $e) {
        error_log('Update repo error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to update settings';
    }

    redirect('settings/' . $repoId);
    exit;
}

// Route: /settings/{repo_id}/sync - Sync labels from GitHub
if (is_numeric($segment1) && $segment2 === 'sync' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment1;

    $repo = $repoModel->findById($repoId);
    if (!$repo) {
        $_SESSION['error'] = 'Repository not found';
        redirect('settings');
        exit;
    }

    try {
        $githubApi = new GitHubAPI(getGitHubToken());
        $bugLabel = Repository::detectBugLabel($repo['owner'], $repo['name'], $githubApi);

        $repoModel->update($repoId, [
            'bug_label' => $bugLabel,
            'priority_labels' => json_encode([]),
            'last_synced_at' => time()
        ]);

        $_SESSION['success'] = 'Labels detected successfully!';
    } catch (Exception $e) {
        error_log('Sync labels error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to detect labels';
    }

    redirect('settings/' . $repoId);
    exit;
}

// Route: /settings/{repo_id}/delete - Delete repository
if (is_numeric($segment1) && $segment2 === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment1;

    $repo = $repoModel->findById($repoId);
    if (!$repo) {
        $_SESSION['error'] = 'Repository not found';
        redirect('settings');
        exit;
    }

    try {
        // Delete all related data first (cascading delete)
        $issueModel = new Issue();
        $areaModel = new Area();
        $analysisJobModel = new AnalysisJob();

        $issueModel->deleteByRepository($repoId);
        $areaModel->deleteByRepository($repoId);
        $analysisJobModel->deleteByRepository($repoId);

        // Finally delete the repository itself
        $repoModel->delete($repoId);

        $_SESSION['success'] = 'Repository and all related data removed successfully!';
    } catch (Exception $e) {
        error_log('Delete repo error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to remove repository';
    }

    redirect('settings');
    exit;
}

// Route: /settings/{repo_id}/reset-areas - Reset areas for repository
if (is_numeric($segment1) && $segment2 === 'reset-areas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment1;

    $repo = $repoModel->findById($repoId);
    if (!$repo) {
        $_SESSION['error'] = 'Repository not found';
        redirect('settings');
        exit;
    }

    try {
        $areaModel = new Area();
        $issueModel = new Issue();
        
        // Clear area_id from all issues first
        $issueModel->clearAreaIds($repoId);
        
        // Then delete the areas
        $areaModel->deleteByRepository($repoId);
        
        // Clear any pending areas from session
        unset($_SESSION['pending_areas']);
        
        // Also clear analysis results which may contain cached area data
        unset($_SESSION['analysis_results']);
        
        $_SESSION['success'] = 'Areas reset! Run analysis again to discover new areas.';
    } catch (Exception $e) {
        error_log('Reset areas error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to reset areas';
    }

    redirect('settings/' . $repoId);
    exit;
}

// Load repositories for sidebar
$glob['repositories'] = $repoModel->findAll();

// Determine selected repository
$selectedRepo = null;
if (is_numeric($segment1)) {
    // Specific repo requested
    $selectedRepo = $repoModel->findById((int)$segment1);
    if (!$selectedRepo) {
        $_SESSION['error'] = 'Repository not found';
        redirect('settings');
        exit;
    }
} elseif (!empty($glob['repositories'])) {
    // Default to first repo
    $selectedRepo = $glob['repositories'][0];
}

$glob['selected_repo'] = $selectedRepo;

// Load issues for selected repo
if ($selectedRepo) {
    $issueModel = new Issue();
    $glob['issues'] = $issueModel->findByRepository($selectedRepo['id']);

    // Load areas for selected repo
    $areaModel = new Area();
    $glob['areas'] = $areaModel->findByRepository($selectedRepo['id']);
} else {
    $glob['issues'] = [];
    $glob['areas'] = [];
}

// Initialize dashboard variables (needed by index.php view even when on Settings tab)
$glob['area_stats'] = [];
$glob['high_signal_issues'] = [];
$glob['cleanup_candidates'] = [];
$glob['missing_info_issues'] = [];
$glob['label_suggestions'] = [];
$glob['duplicates'] = [];
$glob['analysis'] = null;
$glob['pending_areas'] = $_SESSION['pending_areas'] ?? null;

// Set active tab to settings
$glob['active_tab'] = 'settings';

// Load the main index view (Settings is a tab within it)
$glob['view'] = APPPATH.'views/index.php';
require_once($glob['view']);
