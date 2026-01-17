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
	$db = Database::getInstance();
	$repoId = $glob['selected_repo']['id'];

	// Get total issue count
	$totalSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ?";
	$totalResult = $db->fetch($totalSql, [$repoId]);
	$glob['total_issues'] = $totalResult['count'] ?? 0;

	/* LOAD AREAS WITH ISSUE COUNTS (using SQL GROUP BY) ---- */
	$areaModel = new Area();
	$areas = $areaModel->findByRepository($repoId);

	// Get issue counts per area using SQL
	$areaSql = "SELECT area_id, COUNT(*) as count
	            FROM issues
	            WHERE repository_id = ? AND area_id IS NOT NULL
	            GROUP BY area_id";
	$areaCounts = $db->fetchAll($areaSql, [$repoId]);

	// Create lookup map
	$areaCountMap = [];
	foreach ($areaCounts as $row) {
		$areaCountMap[$row['area_id']] = $row['count'];
	}

	// Build area stats array
	$areaStats = [];
	foreach ($areas as $area) {
		$count = $areaCountMap[$area['id']] ?? 0;
		if ($count > 0) {
			$areaStats[] = [
				'id' => $area['id'],
				'name' => $area['name'],
				'count' => $count,
				'percentage' => $glob['total_issues'] > 0 ? round(($count / $glob['total_issues']) * 100) : 0
			];
		}
	}

	// Sort by count descending
	usort($areaStats, function($a, $b) {
		return $b['count'] - $a['count'];
	});

	$glob['area_stats'] = $areaStats;

	/* GET CATEGORY COUNTS ---- */
	// High signal count
	$highSignalSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_high_signal = 1";
	$highSignalResult = $db->fetch($highSignalSql, [$repoId]);
	$glob['high_signal_count'] = $highSignalResult['count'] ?? 0;

	// Cleanup candidates count
	$cleanupSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_cleanup_candidate = 1";
	$cleanupResult = $db->fetch($cleanupSql, [$repoId]);
	$glob['cleanup_count'] = $cleanupResult['count'] ?? 0;

	// Missing info count
	$missingInfoSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_missing_context = 1";
	$missingInfoResult = $db->fetch($missingInfoSql, [$repoId]);
	$glob['missing_info_count'] = $missingInfoResult['count'] ?? 0;

	// Label suggestions count
	$suggestionsSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_missing_labels = 1 AND suggested_labels IS NOT NULL";
	$suggestionsResult = $db->fetch($suggestionsSql, [$repoId]);
	$glob['suggestions_count'] = $suggestionsResult['count'] ?? 0;

	// Duplicates count from session
	$glob['duplicates_count'] = isset($_SESSION['analysis_results']['duplicates']) ? count($_SESSION['analysis_results']['duplicates']) : 0;
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
$glob['duplicates'] = isset($_SESSION['analysis_results']['duplicates']) ? $_SESSION['analysis_results']['duplicates'] : [];

/* LOAD PENDING AREAS ---- */
// Clear pending areas if areas already exist for this repo
if (isset($_SESSION['pending_areas']) && $glob['selected_repo']) {
    $areaModel = new Area();
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
