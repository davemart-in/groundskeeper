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
 * Get GitHub Personal Access Token from environment
 *
 * @return string|null GitHub token or null if not set
 */
function getGitHubToken() {
	return getenv('GITHUB_PERSONAL_ACCESS_TOKEN') ?: null;
}