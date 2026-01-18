<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Reset Controller
 *
 * Clears all issues, areas, and analysis jobs to start fresh
 */

$issueModel = new Issue();
$areaModel = new Area();
$jobModel = new AnalysisJob();
$repoModel = new Repository();

// Get repository ID from route
$repoId = isset($glob['route'][1]) && is_numeric($glob['route'][1]) ? (int)$glob['route'][1] : null;

if (!$repoId) {
    die('Error: No repository ID provided. Usage: /reset/{repo_id}');
}

// Verify repository exists
$repo = $repoModel->findById($repoId);
if (!$repo) {
    die('Error: Repository not found');
}

// Confirm deletion (require POST or confirmation parameter)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['confirm'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Repository Data</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-50 p-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-4">Reset Repository Data</h1>
            <p class="text-slate-600 mb-4">
                You are about to delete all data for repository:
                <strong><?php echo htmlspecialchars($repo['full_name']); ?></strong>
            </p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="text-yellow-800 font-semibold mb-2">This will delete:</h3>
                <ul class="text-yellow-700 text-sm space-y-1">
                    <li>• All issues</li>
                    <li>• All areas</li>
                    <li>• All analysis jobs</li>
                </ul>
            </div>
            <form method="POST">
                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        Yes, Reset Everything
                    </button>
                    <a href="<?php echo BASEURL; ?>" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Perform deletion
try {
    // Delete all related data using model methods
    $issueModel->deleteByRepository($repoId);
    $areaModel->deleteByRepository($repoId);
    $jobModel->deleteByRepository($repoId);

    // Reset repository last_audited_at timestamp
    $repoModel->update($repoId, ['last_audited_at' => null]);

    // Clear session data
    unset($_SESSION['analysis_results']);
    unset($_SESSION['pending_areas']);

    // Show success message
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Complete</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-50 p-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-check text-emerald-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Reset Complete</h1>
                <p class="text-slate-600 mb-6">
                    All data has been cleared for <strong><?php echo htmlspecialchars($repo['full_name']); ?></strong>
                </p>
                <a href="<?php echo BASEURL; ?>" class="inline-block px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">
                    Return to Dashboard
                </a>
            </div>
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </body>
    </html>
    <?php
} catch (Exception $e) {
    // Show detailed error
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-50 p-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Error Resetting Data</h1>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                    <p class="text-red-800 font-mono text-sm"><?php echo htmlspecialchars($e->getMessage()); ?></p>
                    <details class="mt-4">
                        <summary class="text-red-700 cursor-pointer font-medium">Stack Trace</summary>
                        <pre class="text-xs text-red-600 mt-2 overflow-auto"><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
                    </details>
                </div>
                <a href="<?php echo BASEURL; ?>" class="inline-block px-6 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 font-medium">
                    Return to Dashboard
                </a>
            </div>
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </body>
    </html>
    <?php
    exit;
}
