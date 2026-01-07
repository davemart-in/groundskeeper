<?php if (!defined('COREPATH')) exit('No direct script access allowed');
// --------------------------------------------------------------
// PREP URL PARAMS
// --------------------------------------------------------------
// Trim the trailing slash
$uri_minus_trailing_slash = rtrim($_SERVER['REQUEST_URI'], '/');
// Trim URL params
$uri_minus_params = strtok($uri_minus_trailing_slash, '?');
// Set route array to global after also removing starting slash
$glob['route'] = explode("/", substr($uri_minus_params, 1));
// --------------------------------------------------------------
// API ROUTES
// --------------------------------------------------------------
// Handle versioned API routes (e.g., /api/v1/users, /api/v1/users/123)
if (isset($glob['route'][0]) && $glob['route'][0] == 'api') {
	// Check if version is specified (e.g., v1, v2)
	if (isset($glob['route'][1]) && preg_match('/^v\d+$/', $glob['route'][1])) {
		$api_version = $glob['route'][1]; // e.g., "v1"
		
		// Check if resource is specified (e.g., users, orders)
		if (!isset($glob['route'][2]) || empty($glob['route'][2])) {
			// No resource specified - return API info or 404
			http_response_code(404);
			header('Content-Type: application/json');
			echo json_encode([
				'code' => 'api/invalid_endpoint',
				'message' => 'No resource specified.'
			]);
			exit;
		}
		
		$resource = $glob['route'][2]; // e.g., "users"
		
		// Build controller path: app/api/v1/users.php
		$controller = APPPATH . 'api/' . $api_version . '/' . $resource . '.php';
		
		// Check if controller exists
		if (file_exists($controller)) {
			// Store API-specific data in global
			$glob['api'] = [
				'version' => $api_version,
				'resource' => $resource,
				'method' => $_SERVER['REQUEST_METHOD'],
				'segments' => array_slice($glob['route'], 3) // Everything after resource name
			];
			
			// Load and execute the API controller
			return require_once($controller);
		} else {
			// Controller not found - return 404
			http_response_code(404);
			header('Content-Type: application/json');
			echo json_encode([
				'code' => 'api/endpoint_not_found',
				'message' => 'The requested API endpoint does not exist.'
			]);
			exit;
		}
	} else {
		// Invalid version format - must be v1, v2, etc.
		http_response_code(404);
		header('Content-Type: application/json');
		echo json_encode([
			'code' => 'api/invalid_version',
			'message' => 'Invalid API version. Please use a valid version (e.g., v1).'
		]);
		exit;
	}
}

// --------------------------------------------------------------
// INTERNAL API CONTROLLERS
// --------------------------------------------------------------
// Handle internal API routes that start with "api-" (e.g., /api-terminal)
if (isset($glob['route'][0]) && strpos($glob['route'][0], 'api-') === 0) {
	// Build controller path with proper capitalization: Api-terminal.php
	$controller = APPPATH . 'controllers/' . ucfirst($glob['route'][0]) . '.php';
	
	// Check if controller exists
	if (file_exists($controller)) {
		// Set JSON response as default for API controllers
		header('Content-Type: application/json');
		
		// Store API controller data in global
		$glob['api_controller'] = [
			'name' => $glob['route'][0],
			'method' => $_SERVER['REQUEST_METHOD'],
			'segments' => array_slice($glob['route'], 1)
		];
		
		// Load and execute the API controller
		return require_once($controller);
	} else {
		// Controller not found - return 404
		http_response_code(404);
		echo json_encode([
			'success' => false,
			'error' => 'API endpoint not found.'
		]);
		exit;
	}
}

// --------------------------------------------------------------
// OTHER ROUTES
// --------------------------------------------------------------
// Set view and controller paths
$glob['view'] = APPPATH.'views/' . $glob['route'][0] . '.php';
$controller = APPPATH.'controllers/' . ucfirst($glob['route'][0]) . '.php';

// If view has secondary path, add it
if (isset($glob['route'][1]) && file_exists(APPPATH.'views/' . $glob['route'][0] . '-' . $glob['route'][1] . '.php')) {
	$glob['view'] = APPPATH.'views/' . $glob['route'][0] . '-' . $glob['route'][1] . '.php';
}

/* ROUTING ------------------------------------------- */
if (empty($glob['route'][0])) {
	$glob['is_home'] = true;
	// Homepage
	$glob['view'] = APPPATH.'views/index.php';
	return require_once(APPPATH.'controllers/Index.php');
	exit;
} elseif (file_exists($controller)) {
	return require_once($controller);
} else {
	// Show 404 if no controller exists
	return redirect('404/');
}