<?php if (!defined('COREPATH')) exit('No direct script access allowed');

// Config
require_once(APPPATH.'config/database.php');

if (!isset($db_config) OR count($db_config) == 0) {
	// Log the error for debugging
	error_log('Database configuration not found in ' . APPPATH . 'config/database.php');
	
	// Return a generic error response
	http_response_code(500);
	echo 'Configuration error. Please contact the administrator.';
	
	// Properly exit without using die()
	exit(1);
}

// Exceptions log
require_once(COREPATH.'database/Log.class.php');

// PDO wrapper
require_once(COREPATH.'database/DB_mysql.php');