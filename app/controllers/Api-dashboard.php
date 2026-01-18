<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Dashboard API Controller
 *
 * Handles AJAX requests for dashboard data
 */

// Get request method and segments
$method = $_SERVER['REQUEST_METHOD'];
$segments = $glob['api_controller']['segments'] ?? [];

// Only allow GET requests
if ($method !== 'GET') {
	http_response_code(405);
	echo json_encode([
		'success' => false,
		'error' => 'Method not allowed'
	]);
	exit;
}

// Route to appropriate handler
$action = $segments[0] ?? 'stats';

switch ($action) {
	case 'stats':
		handleStats();
		break;

	case 'high-signal':
		handleHighSignal();
		break;

	case 'duplicates':
		handleDuplicates();
		break;

	case 'cleanup':
		handleCleanup();
		break;

	case 'missing-info':
		handleMissingInfo();
		break;

	case 'suggestions':
		handleSuggestions();
		break;

	default:
		http_response_code(404);
		echo json_encode([
			'success' => false,
			'error' => 'Endpoint not found'
		]);
		exit;
}

/**
 * Get dashboard statistics (counts only)
 */
function handleStats() {
	$repoId = $_GET['repo_id'] ?? null;

	if (!$repoId) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'error' => 'Repository ID required'
		]);
		exit;
	}

	$issueModel = new Issue();

	echo json_encode([
		'success' => true,
		'data' => [
			'total' => $issueModel->countByRepository($repoId),
			'high_signal' => $issueModel->countHighSignal($repoId),
			'duplicates' => count($_SESSION['analysis_results']['duplicates'] ?? []),
			'cleanup' => $issueModel->countCleanupCandidates($repoId),
			'missing_info' => $issueModel->countMissingContext($repoId),
			'suggestions' => $issueModel->countMissingLabels($repoId)
		]
	]);
}

/**
 * Get high signal issues
 */
function handleHighSignal() {
	$repoId = $_GET['repo_id'] ?? null;
	$areaId = $_GET['area_id'] ?? null;

	if (!$repoId) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'error' => 'Repository ID required'
		]);
		exit;
	}

	$issueModel = new Issue();
	$rows = $issueModel->findHighSignal($repoId, $areaId);

	// Calculate priority scores and filter
	$issues = [];
	foreach ($rows as $issue) {
		$score = calculatePriorityScore($issue);
		$issue['priority_score'] = $score;

		// Only include issues with score >= 50
		if ($score >= 50) {
			$issues[] = $issue;
		}
	}

	// Sort by priority score descending
	usort($issues, function($a, $b) {
		return $b['priority_score'] - $a['priority_score'];
	});

	echo json_encode([
		'success' => true,
		'data' => $issues
	]);
}

/**
 * Calculate priority score for an issue
 *
 * @param array $issue Issue data
 * @return int Priority score (0-100)
 */
function calculatePriorityScore($issue) {
	$score = 0;

	// Factor 1: Community engagement (max 40 points)
	$engagement = ($issue['reactions_total'] ?? 0) + ($issue['comments_count'] ?? 0);
	$score += min(40, $engagement * 2);

	// Factor 2: Age (newer issues get more points, max 25 points)
	$age = time() - $issue['created_at'];
	$daysOld = $age / 86400;
	if ($daysOld < 7) {
		$score += 25;
	} elseif ($daysOld < 30) {
		$score += 20;
	} elseif ($daysOld < 90) {
		$score += 15;
	} elseif ($daysOld < 180) {
		$score += 10;
	} else {
		$score += 5;
	}

	// Factor 3: Has assignee (max 15 points)
	if (!empty($issue['assignees'])) {
		$score += 15;
	}

	// Factor 4: Has milestone (max 10 points)
	if (!empty($issue['milestone'])) {
		$score += 10;
	}

	// Factor 5: Label signals (max 10 points)
	$priorityLabels = ['critical', 'urgent', 'high priority', 'p0', 'p1', 'blocker', 'security'];
	$labels = $issue['labels'] ?? [];
	foreach ($labels as $label) {
		$labelLower = strtolower($label);
		foreach ($priorityLabels as $priorityLabel) {
			if (strpos($labelLower, $priorityLabel) !== false) {
				$score += 10;
				break 2;
			}
		}
	}

	return min(100, $score);
}

/**
 * Get duplicate issues
 */
function handleDuplicates() {
	$areaId = $_GET['area_id'] ?? null;

	// Get duplicates from session
	$allDuplicates = $_SESSION['analysis_results']['duplicates'] ?? [];

	// Filter by area if specified
	if ($areaId) {
		$duplicates = array_filter($allDuplicates, function($dup) use ($areaId) {
			// Check if any issue in the group matches the area
			foreach ($dup['issues'] as $issue) {
				if (isset($issue['area_id']) && $issue['area_id'] == $areaId) {
					return true;
				}
			}
			return false;
		});
		$duplicates = array_values($duplicates);
	} else {
		$duplicates = $allDuplicates;
	}

	echo json_encode([
		'success' => true,
		'data' => $duplicates
	]);
}

/**
 * Get cleanup candidate issues
 */
function handleCleanup() {
	$repoId = $_GET['repo_id'] ?? null;
	$areaId = $_GET['area_id'] ?? null;

	if (!$repoId) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'error' => 'Repository ID required'
		]);
		exit;
	}

	$issueModel = new Issue();

	echo json_encode([
		'success' => true,
		'data' => $issueModel->findCleanupCandidates($repoId, $areaId)
	]);
}

/**
 * Get issues missing critical info
 */
function handleMissingInfo() {
	$repoId = $_GET['repo_id'] ?? null;
	$areaId = $_GET['area_id'] ?? null;

	if (!$repoId) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'error' => 'Repository ID required'
		]);
		exit;
	}

	$issueModel = new Issue();

	echo json_encode([
		'success' => true,
		'data' => $issueModel->findMissingContext($repoId, $areaId)
	]);
}

/**
 * Get issues with label suggestions
 */
function handleSuggestions() {
	$repoId = $_GET['repo_id'] ?? null;
	$areaId = $_GET['area_id'] ?? null;

	if (!$repoId) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'error' => 'Repository ID required'
		]);
		exit;
	}

	$issueModel = new Issue();

	echo json_encode([
		'success' => true,
		'data' => $issueModel->findMissingLabels($repoId, $areaId)
	]);
}
