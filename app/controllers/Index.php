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
				'id' => $area['id'],
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

	// Calculate priority score for each high signal issue
	foreach ($highSignalIssues as &$issue) {
		$score = 0;

		// Factor 1: Community engagement (max 40 points)
		$engagement = ($issue['reactions_total'] ?? 0) + ($issue['comments_count'] ?? 0);
		$score += min(40, $engagement * 2);

		// Factor 2: Age (newer issues get more points, max 25 points)
		$age = time() - $issue['created_at'];
		$daysOld = $age / 86400;
		if ($daysOld < 7) {
			$score += 25; // Very recent
		} elseif ($daysOld < 30) {
			$score += 20; // Recent
		} elseif ($daysOld < 90) {
			$score += 15; // Moderate
		} elseif ($daysOld < 180) {
			$score += 10; // Aging
		} else {
			$score += 5; // Old but still high signal
		}

		// Factor 3: Has assignee (shows active work, max 15 points)
		if (!empty($issue['assignees']) && count($issue['assignees']) > 0) {
			$score += 15;
		}

		// Factor 4: Has milestone (shows planning/prioritization, max 10 points)
		if (!empty($issue['milestone'])) {
			$score += 10;
		}

		// Factor 5: Label signals (max 10 points)
		$labels = is_array($issue['labels']) ? $issue['labels'] : [];
		$priorityLabels = ['critical', 'urgent', 'high priority', 'p0', 'p1', 'blocker', 'security'];
		$hasPriorityLabel = false;
		foreach ($labels as $label) {
			$labelLower = strtolower($label);
			foreach ($priorityLabels as $priorityLabel) {
				if (strpos($labelLower, $priorityLabel) !== false) {
					$hasPriorityLabel = true;
					break 2;
				}
			}
		}
		if ($hasPriorityLabel) {
			$score += 10;
		}

		$issue['priority_score'] = min(100, $score);
	}
	unset($issue);

	// Filter to only show issues with score of 50 or more
	$highSignalIssues = array_filter($highSignalIssues, function($issue) {
		return $issue['priority_score'] >= 50;
	});

	// Sort by priority score (highest first)
	usort($highSignalIssues, function($a, $b) {
		return $b['priority_score'] - $a['priority_score'];
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
