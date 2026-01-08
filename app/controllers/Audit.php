<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Audit Controller
 *
 * Handles repository audit operations (fetching and storing issues)
 */

$repoModel = new Repository();
$issueModel = new Issue();
$user = User::getCurrentUser();
$segment1 = $glob['route'][1] ?? '';
$segment2 = $glob['route'][2] ?? '';

// Route: /audit/run/{repo_id}
if ($segment1 === 'run' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment2;

    // Load repository
    $repo = $repoModel->findById($repoId);

    if (!$repo) {
        $_SESSION['error'] = 'Repository not found.';
        redirect('');
        exit;
    }

    // Initialize GitHub API
    $githubToken = ($user && !empty($user->github_access_token)) ? $user->github_access_token : null;
    $github = new GitHubAPI($githubToken);

    // Fetch issues from GitHub with bug label filter
    $filters = [
        'state' => 'open',
        'labels' => $repo['bug_label']
    ];

    $githubIssues = $github->getIssues($repo['owner'], $repo['name'], $filters);

    if ($githubIssues === false) {
        $_SESSION['error'] = 'Failed to fetch issues from GitHub. Please check your repository settings and try again.';
        redirect('');
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

    // Set success message
    $_SESSION['success'] = "Audit complete! Imported {$issueCount} issue" . ($issueCount !== 1 ? 's' : '') . " from {$repo['full_name']}.";

    // Redirect back to dashboard
    redirect('');
    exit;
}

// If we get here, invalid route
redirect('');
exit;
