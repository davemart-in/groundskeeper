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
} elseif (file_exists($controller)) {
	return require_once($controller);
} else {
	// Show 404 if no controller exists
	return redirect('404/');
}