<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* GET CURRENT USER ---- */
$user = User::getCurrentUser();
$glob['user'] = $user ? $user->toArray() : null;

/* LOAD REPOSITORIES ---- */
$repoModel = new Repository();
$glob['repositories'] = $repoModel->findAll();
$glob['selected_repo'] = !empty($glob['repositories']) ? $glob['repositories'][0] : null;

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
	$glob['high_signal_count'] = $issueModel->countHighSignal($repoId);
	$glob['cleanup_count'] = $issueModel->countCleanupCandidates($repoId);
	$glob['missing_info_count'] = $issueModel->countMissingContext($repoId);
	$glob['suggestions_count'] = $issueModel->countMissingLabels($repoId);
	$glob['duplicates_count'] = count($_SESSION['analysis_results']['duplicates'] ?? []);
} else {
	$glob['total_issues'] = 0;
	$glob['area_stats'] = [];
	$glob['high_signal_count'] = 0;
	$glob['cleanup_count'] = 0;
	$glob['missing_info_count'] = 0;
	$glob['suggestions_count'] = 0;
	$glob['duplicates_count'] = 0;
}

/* LOAD ANALYSIS RESULTS ---- */
$glob['analysis'] = $_SESSION['analysis_results'] ?? null;

/* LOAD DUPLICATES ---- */
$glob['duplicates'] = $_SESSION['analysis_results']['duplicates'] ?? [];

/* LOAD PENDING AREAS ---- */
// Clear pending areas if areas already exist for this repo
if (isset($_SESSION['pending_areas']) && $glob['selected_repo']) {
    $existingAreas = $areaModel->findByRepository($glob['selected_repo']['id']);
    if (!empty($existingAreas)) {
        unset($_SESSION['pending_areas']);
    }
}
$glob['pending_areas'] = $_SESSION['pending_areas'] ?? null;

/* LOAD VIEW ---- */
if (isset($glob['view'])) {
	require_once($glob['view']);
}
