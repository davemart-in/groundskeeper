<?php if (!defined('COREPATH')) exit('No direct script access allowed');

// Creates a single nonce, saves it to session, and returns nonce
// This consolidates all CSRF protection into one nonce that gets reset after each use
function csrf_set($name = null) {
	// Generate new nonce if none exists
	if (!isset($_SESSION['csrf_nonce'])) {
		$token = bin2hex(random_bytes(32));
		$_SESSION['csrf_nonce'] = $token;
	} else {
		$token = $_SESSION['csrf_nonce'];
	}

	return $token;
}

// Checks $_POST nonce against $_SESSION nonce and resets it after validation
function csrf_check($name = null) {
	$nonce_post = isset($_POST['nonce']) ? $_POST['nonce'] : '';
	$nonce_session = isset($_SESSION['csrf_nonce']) ? $_SESSION['csrf_nonce'] : null;

	if (empty($nonce_post)) {
		return false;
	}
	
	if (empty($nonce_session)) {
		return false;
	}

	if ($nonce_post !== $nonce_session) {
		return false;
	}

	// Remove nonce from $_POST
	unset($_POST['nonce']);
	
	// Reset the nonce in session after successful validation
	$_SESSION['csrf_nonce'] = bin2hex(random_bytes(32));
	
	return true;
}

// Legacy function to maintain backward compatibility
// This allows existing code to work without changes
function csrf_set_legacy($name) {
	if (isset($_SESSION[$name.'nonce'])) {
		$token = $_SESSION[$name.'nonce'];
	} else {
		$token = bin2hex(random_bytes(32));
		$_SESSION[$name.'nonce'] = $token;
	}

	return $token;
}

// Legacy function to maintain backward compatibility  
function csrf_check_legacy($name) {
	$nonce_post = $_POST['nonce'];
	$nonce_session = isset($_SESSION[$name.'nonce']) ? $_SESSION[$name.'nonce'] : null;

	if (!isset($nonce_post) || $nonce_post == '') {
		return false;
	}
	
	if (!isset($nonce_session) || $nonce_session == null) {
		return false;
	}

	if ($nonce_post != $nonce_session) {
		return false;
	}

	// Remove nonce from $_POST and $_COOKIE
	unset($_POST['nonce']);
	unset($_SESSION[$name.'nonce']);
	
	return true;
}