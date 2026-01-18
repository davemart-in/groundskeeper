<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* LOAD REPOSITORIES ---- */
$repoModel = new Repository();
$glob['repositories'] = $repoModel->findAll();

// Determine selected repository (priority: URL param > session > first repo)
$selectedRepoId = null;
if (isset($_GET['repo']) && is_numeric($_GET['repo'])) {
	$selectedRepoId = (int)$_GET['repo'];
	$_SESSION['selected_repo_id'] = $selectedRepoId;
} elseif (isset($_SESSION['selected_repo_id'])) {
	$selectedRepoId = $_SESSION['selected_repo_id'];
}

// Find the selected repository
$glob['selected_repo'] = null;
if ($selectedRepoId) {
	foreach ($glob['repositories'] as $repo) {
		if ($repo['id'] == $selectedRepoId) {
			$glob['selected_repo'] = $repo;
			break;
		}
	}
}

// Fallback to first repo if selected repo not found
if (!$glob['selected_repo'] && !empty($glob['repositories'])) {
	$glob['selected_repo'] = $glob['repositories'][0];
	$_SESSION['selected_repo_id'] = $glob['selected_repo']['id'];
}

/* LOAD ISSUE COUNTS FOR SELECTED REPO ---- */
if ($glob['selected_repo']) {
	$repoId = $glob['selected_repo']['id'];
	$issueModel = new Issue();
	$areaModel = new Area();

	// Get total issue count
	$glob['total_issues'] = $issueModel->countByRepository($repoId);

	// Get area stats with issue counts
	$glob['area_stats'] = $areaModel->getStatsForRepository($repoId, $glob['total_issues']);

	/* GET CATEGORY COUNTS ---- */
	// Get priority labels for filtering
	$priorityLabels = json_decode($glob['selected_repo']['priority_labels'] ?? '[]', true);
	$glob['high_signal_count'] = $issueModel->countHighSignal($repoId, $priorityLabels);
	$glob['cleanup_count'] = $issueModel->countCleanupCandidates($repoId);
	$glob['missing_info_count'] = $issueModel->countMissingContext($repoId);
	$glob['suggestions_count'] = $issueModel->countMissingLabels($repoId);

	// Get duplicates count from database
	$analysisResultModel = new AnalysisResult();
	$analysisResults = $analysisResultModel->findByRepository($repoId);
	$glob['duplicates_count'] = count($analysisResults['duplicates'] ?? []);

	// Store whether priority labels are configured
	$hasPriorityLabels = !empty($priorityLabels);
} else {
	$glob['total_issues'] = 0;
	$glob['area_stats'] = [];
	$glob['high_signal_count'] = 0;
	$glob['cleanup_count'] = 0;
	$glob['missing_info_count'] = 0;
	$glob['suggestions_count'] = 0;
	$glob['duplicates_count'] = 0;
	$hasPriorityLabels = false;
}

/* LOAD ANALYSIS RESULTS (kept for backward compatibility but not used) ---- */
$glob['analysis'] = null;

/* LOAD DUPLICATES (no longer needed in controller, loaded via API) ---- */
$glob['duplicates'] = [];

/* LOAD PENDING AREAS ---- */
// Only show pending areas if they're for the currently selected repo
if (isset($_SESSION['pending_areas']) && $glob['selected_repo']) {
    $pendingRepoId = $_SESSION['pending_areas']['repo_id'] ?? null;
    if ($pendingRepoId == $glob['selected_repo']['id']) {
        // Check if areas now exist (maybe user approved them elsewhere)
        $existingAreas = $areaModel->findByRepository($glob['selected_repo']['id']);
        if (!empty($existingAreas)) {
            unset($_SESSION['pending_areas']);
            $glob['pending_areas'] = null;
        } else {
            $glob['pending_areas'] = $_SESSION['pending_areas'];
        }
    } else {
        // Pending areas are for a different repo, don't show them
        $glob['pending_areas'] = null;
    }
} else {
    $glob['pending_areas'] = null;
}

/* LOAD VIEW ---- */
if (isset($glob['view'])) {
	require_once($glob['view']);
}
