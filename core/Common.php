<?php if (!defined('COREPATH')) exit('No direct script access allowed');

function closeDbAndSessions() {
	session_write_close();
}

function getVersionNumber() {
	$filePath = '../VERSION.txt';
	
	if (file_exists($filePath)) {
		$version = file_get_contents($filePath);
		return trim($version); // Trim to remove any whitespace or newline characters
	} else {
		// Return todays date in format YYYY-MM-DD
		return date('Y-m-d');
	}
}

function redirect($location, $msg='') {
	// Set a cookie named 'flash-msg' with the message and a 60 second expiry
	if (!empty($msg)) {
		cookie_set('flash-msg', $msg, 60);
	}
	// Prevent // in the URL
	if ($location == '/') {
		$location = '';
	}
	// Close the database connection and any open sessions
	closeDbAndSessions();
	// Redirect to the new location
	header('Location: ' . BASEURL . $location);
	// Terminate the script
	exit();
}

function safe($input, bool $escape_html = true) {
	switch (gettype($input)) {
		case 'string':
			return $escape_html ? htmlspecialchars($input, ENT_QUOTES, 'UTF-8') : $input;
		case 'array':
			foreach ($input as $key => $value) {
				$input[$key] = safe($value, $escape_html); // Recursively sanitize array elements
			}
			return $input;
		case 'object':
			foreach ($input as $key => $value) {
				$input->$key = safe($value, $escape_html); // Recursively sanitize object properties
			}
			return $input;
		default:
			return $input; // Return as-is for other data types
	}
}

/**
 * Check if user is authenticated
 *
 * @return bool True if user is logged in
 */
function isAuthenticated() {
	return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication (redirect to home if not authenticated)
 *
 * @param string $redirectTo URL to redirect to if not authenticated
 */
function requireAuth($redirectTo = '') {
	if (!isAuthenticated()) {
		redirect($redirectTo, 'You must be logged in to access this page.');
	}
}

/**
 * Get current authenticated user
 *
 * @return User|null Current user or null if not authenticated
 */
function getCurrentUser() {
	return User::getCurrentUser();
}