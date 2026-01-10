<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* GET CURRENT USER ---- */
$user = User::getCurrentUser();
$glob['user'] = $user ? $user->toArray() : null;

/* LOAD REPOSITORIES ---- */
$repoModel = new Repository();
$glob['repositories'] = $repoModel->findAll();
$glob['selected_repo'] = !empty($glob['repositories']) ? $glob['repositories'][0] : null;

/* LOAD ISSUES FOR SELECTED REPO ---- */
if ($glob['selected_repo']) {
	$issueModel = new Issue();
	$glob['issues'] = $issueModel->findByRepository($glob['selected_repo']['id']);
	$totalIssues = count($glob['issues']);

	/* LOAD AREAS WITH ISSUE COUNTS ---- */
	$areaModel = new Area();
	$areas = $areaModel->findByRepository($glob['selected_repo']['id']);

	// Get issue counts for each area
	$areaStats = [];
	foreach ($areas as $area) {
		$count = 0;
		foreach ($glob['issues'] as $issue) {
			if ($issue['area_id'] == $area['id']) {
				$count++;
			}
		}
		if ($count > 0) {
			$areaStats[] = [
				'name' => $area['name'],
				'count' => $count,
				'percentage' => $totalIssues > 0 ? round(($count / $totalIssues) * 100) : 0
			];
		}
	}

	// Sort by count descending
	usort($areaStats, function($a, $b) {
		return $b['count'] - $a['count'];
	});

	$glob['area_stats'] = $areaStats;

	/* GET HIGH SIGNAL ISSUES ---- */
	$highSignalIssues = array_filter($glob['issues'], function($issue) {
		return !empty($issue['is_high_signal']);
	});
	$glob['high_signal_issues'] = array_values($highSignalIssues);

	/* GET CLEANUP CANDIDATES ---- */
	$cleanupCandidates = array_filter($glob['issues'], function($issue) {
		return !empty($issue['is_cleanup_candidate']);
	});
	$glob['cleanup_candidates'] = array_values($cleanupCandidates);

	/* GET MISSING CRITICAL INFO ---- */
	$missingInfo = array_filter($glob['issues'], function($issue) {
		return !empty($issue['is_missing_context']);
	});
	$glob['missing_info_issues'] = array_values($missingInfo);

	/* GET LABEL SUGGESTIONS ---- */
	$labelSuggestions = array_filter($glob['issues'], function($issue) {
		return !empty($issue['is_missing_labels']) && !empty($issue['suggested_labels']);
	});
	$glob['label_suggestions'] = array_values($labelSuggestions);
} else {
	$glob['issues'] = [];
	$glob['area_stats'] = [];
	$glob['high_signal_issues'] = [];
	$glob['cleanup_candidates'] = [];
	$glob['missing_info_issues'] = [];
	$glob['label_suggestions'] = [];
}

/* LOAD ANALYSIS RESULTS ---- */
$glob['analysis'] = $_SESSION['analysis_results'] ?? null;

/* LOAD DUPLICATES ---- */
$glob['duplicates'] = isset($_SESSION['analysis_results']['duplicates']) ? $_SESSION['analysis_results']['duplicates'] : [];

/* LOAD PENDING AREAS ---- */
$glob['pending_areas'] = $_SESSION['pending_areas'] ?? null;

/* LOAD VIEW ---- */
if (isset($glob['view'])) {
	require_once($glob['view']);
}
