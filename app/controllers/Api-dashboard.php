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
	global $glob;

	// Get repository ID from query param
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
	$db = Database::getInstance();

	// Get total count
	$totalSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ?";
	$totalResult = $db->fetch($totalSql, [$repoId]);
	$totalCount = $totalResult['count'] ?? 0;

	// Get high signal count
	$highSignalSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_high_signal = 1";
	$highSignalResult = $db->fetch($highSignalSql, [$repoId]);
	$highSignalCount = $highSignalResult['count'] ?? 0;

	// Get cleanup count
	$cleanupSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_cleanup_candidate = 1";
	$cleanupResult = $db->fetch($cleanupSql, [$repoId]);
	$cleanupCount = $cleanupResult['count'] ?? 0;

	// Get missing info count
	$missingInfoSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_missing_context = 1";
	$missingInfoResult = $db->fetch($missingInfoSql, [$repoId]);
	$missingInfoCount = $missingInfoResult['count'] ?? 0;

	// Get label suggestions count
	$suggestionsSql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_missing_labels = 1 AND suggested_labels IS NOT NULL";
	$suggestionsResult = $db->fetch($suggestionsSql, [$repoId]);
	$suggestionsCount = $suggestionsResult['count'] ?? 0;

	// Get duplicates count from session
	$duplicatesCount = isset($_SESSION['analysis_results']['duplicates']) ? count($_SESSION['analysis_results']['duplicates']) : 0;

	echo json_encode([
		'success' => true,
		'data' => [
			'total' => $totalCount,
			'high_signal' => $highSignalCount,
			'duplicates' => $duplicatesCount,
			'cleanup' => $cleanupCount,
			'missing_info' => $missingInfoCount,
			'suggestions' => $suggestionsCount
		]
	]);
}

/**
 * Get high signal issues
 */
function handleHighSignal() {
	global $glob;

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

	$db = Database::getInstance();

	// Build query
	$sql = "SELECT id, issue_number, title, labels, assignees, milestone, comments_count, reactions_total,
	               created_at, url, is_high_signal, area_id
	        FROM issues
	        WHERE repository_id = ? AND is_high_signal = 1";

	$params = [$repoId];

	if ($areaId) {
		$sql .= " AND area_id = ?";
		$params[] = $areaId;
	}

	$sql .= " ORDER BY created_at DESC";

	$rows = $db->fetchAll($sql, $params);

	// Process rows - decode JSON and calculate priority scores
	$issues = [];
	foreach ($rows as $row) {
		$issue = [
			'id' => $row['id'],
			'issue_number' => $row['issue_number'],
			'title' => $row['title'],
			'labels' => $row['labels'] ? json_decode($row['labels'], true) : [],
			'assignees' => $row['assignees'] ? json_decode($row['assignees'], true) : [],
			'milestone' => $row['milestone'],
			'comments_count' => $row['comments_count'] ?? 0,
			'reactions_total' => $row['reactions_total'] ?? 0,
			'created_at' => $row['created_at'],
			'url' => $row['url'],
			'area_id' => $row['area_id']
		];

		// Calculate priority score
		$score = 0;

		// Factor 1: Community engagement (max 40 points)
		$engagement = $issue['reactions_total'] + $issue['comments_count'];
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
		$hasPriorityLabel = false;
		foreach ($issue['labels'] as $label) {
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

		// Only include issues with score >= 50
		if ($issue['priority_score'] >= 50) {
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
	global $glob;

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

	$db = Database::getInstance();

	// Build query
	$sql = "SELECT id, issue_number, title, labels, created_at, url, area_id
	        FROM issues
	        WHERE repository_id = ? AND is_cleanup_candidate = 1";

	$params = [$repoId];

	if ($areaId) {
		$sql .= " AND area_id = ?";
		$params[] = $areaId;
	}

	$sql .= " ORDER BY created_at DESC";

	$rows = $db->fetchAll($sql, $params);

	// Process rows
	$issues = array_map(function($row) {
		return [
			'id' => $row['id'],
			'issue_number' => $row['issue_number'],
			'title' => $row['title'],
			'labels' => $row['labels'] ? json_decode($row['labels'], true) : [],
			'created_at' => $row['created_at'],
			'url' => $row['url'],
			'area_id' => $row['area_id']
		];
	}, $rows);

	echo json_encode([
		'success' => true,
		'data' => $issues
	]);
}

/**
 * Get issues missing critical info
 */
function handleMissingInfo() {
	global $glob;

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

	$db = Database::getInstance();

	// Build query
	$sql = "SELECT id, issue_number, title, labels, missing_elements, created_at, url, area_id
	        FROM issues
	        WHERE repository_id = ? AND is_missing_context = 1";

	$params = [$repoId];

	if ($areaId) {
		$sql .= " AND area_id = ?";
		$params[] = $areaId;
	}

	$sql .= " ORDER BY created_at DESC";

	$rows = $db->fetchAll($sql, $params);

	// Process rows
	$issues = array_map(function($row) {
		return [
			'id' => $row['id'],
			'issue_number' => $row['issue_number'],
			'title' => $row['title'],
			'labels' => $row['labels'] ? json_decode($row['labels'], true) : [],
			'missing_elements' => $row['missing_elements'] ? json_decode($row['missing_elements'], true) : [],
			'created_at' => $row['created_at'],
			'url' => $row['url'],
			'area_id' => $row['area_id']
		];
	}, $rows);

	echo json_encode([
		'success' => true,
		'data' => $issues
	]);
}

/**
 * Get issues with label suggestions
 */
function handleSuggestions() {
	global $glob;

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

	$db = Database::getInstance();

	// Build query
	$sql = "SELECT id, issue_number, title, labels, suggested_labels, created_at, url, area_id
	        FROM issues
	        WHERE repository_id = ? AND is_missing_labels = 1 AND suggested_labels IS NOT NULL";

	$params = [$repoId];

	if ($areaId) {
		$sql .= " AND area_id = ?";
		$params[] = $areaId;
	}

	$sql .= " ORDER BY created_at DESC";

	$rows = $db->fetchAll($sql, $params);

	// Process rows
	$issues = array_map(function($row) {
		return [
			'id' => $row['id'],
			'issue_number' => $row['issue_number'],
			'title' => $row['title'],
			'labels' => $row['labels'] ? json_decode($row['labels'], true) : [],
			'suggested_labels' => $row['suggested_labels'] ? json_decode($row['suggested_labels'], true) : [],
			'created_at' => $row['created_at'],
			'url' => $row['url'],
			'area_id' => $row['area_id']
		];
	}, $rows);

	echo json_encode([
		'success' => true,
		'data' => $issues
	]);
}
