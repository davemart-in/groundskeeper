<?php if (!defined('COREPATH')) die('No direct script access allowed');

require_once(APPPATH.'models/auth.php');

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function auth_is_logged_in() {
	return isset($_SESSION['uid']) && !empty($_SESSION['uid']);
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function auth_get_user_id() {
	return isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
}

/**
 * Get current company ID
 * 
 * @return int|null Company ID or null if not set
 */
function auth_get_company_id() {
	return isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : null;
}

/**
 * Start user session
 * 
 * @param array $user User data from database
 * @return void
 */
function auth_start_session($user) {
	$_SESSION['uid'] = $user['id'];
	$_SESSION['company_id'] = $user['company_id'];
	$_SESSION['email'] = $user['email'];
	$_SESSION['is_super'] = (bool)$user['is_super'];
}

/**
 * End user session (logout)
 * 
 * @return void
 */
function auth_end_session() {
	session_unset();
	session_destroy();
}

/**
 * Require user to be logged in (redirect to signup if not)
 * 
 * @return void
 */
function auth_require_login() {
	if (!auth_is_logged_in()) {
		redirect('signup/', 'Please log in to continue.');
	}
}

/**
 * Redirect if already logged in
 * 
 * @return void
 */
function auth_redirect_if_logged_in() {
	if (auth_is_logged_in()) {
		redirect('/', 'You are already logged in.');
	}
}
