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
	$analysisResultModel = new AnalysisResult();
	$analysisResults = $analysisResultModel->findByRepository($repoId);

	// Get repository priority labels
	$repoModel = new Repository();
	$repo = $repoModel->findById($repoId);
	$priorityLabels = json_decode($repo['priority_labels'] ?? '[]', true);

	echo json_encode([
		'success' => true,
		'data' => [
			'total' => $issueModel->countByRepository($repoId),
			'high_signal' => $issueModel->countHighSignal($repoId, $priorityLabels),
			'duplicates' => count($analysisResults['duplicates'] ?? []),
			'cleanup' => $issueModel->countCleanupCandidates($repoId),
			'missing_info' => $issueModel->countMissingContext($repoId),
			'suggestions' => $issueModel->countMissingLabels($repoId)
		]
	]);
}

/**
 * Get high signal issues missing priority labels
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

	// Get repository priority labels
	$repoModel = new Repository();
	$repo = $repoModel->findById($repoId);
	$priorityLabels = json_decode($repo['priority_labels'] ?? '[]', true);

	$issueModel = new Issue();
	$issues = $issueModel->findHighSignal($repoId, $areaId, $priorityLabels);

	// Calculate priority scores for display
	foreach ($issues as &$issue) {
		$issue['priority_score'] = $issueModel->calculatePriorityScore($issue);
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

	// Get duplicates from database
	$analysisResultModel = new AnalysisResult();
	$analysisResults = $analysisResultModel->findByRepository($repoId);
	$allDuplicates = $analysisResults['duplicates'] ?? [];

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
