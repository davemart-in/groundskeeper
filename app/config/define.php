<?php

// Prevent direct access to this configuration file
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
	exit('No direct script access allowed');
}

// Email ESP
define('ESP', 'ses'); // postmark, ses, smtp

// Maintenance mode?
define('MAINTENANCE_MODE', false);

// DEBUG MODE?
define('DEBUG', false);

// Secure web-based environment detection
$detected_env = 'production'; // Default to production for security

// If domain is groundskeeper.dev, set to staging
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'groundskeeper.dev') {
	$detected_env = 'staging';
}

// Validate SERVER_PORT exists and is numeric
if (isset($_SERVER['SERVER_PORT']) && is_numeric($_SERVER['SERVER_PORT'])) {
	$server_port = (int)$_SERVER['SERVER_PORT'];
	if ($server_port === 7890) {
		$detected_env = 'local';
	}
}

define('ENV', $detected_env);

// Environment-specific configuration with security considerations
if ('local' == ENV) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	define('ROOTPATH', '/Users/davem/Sites/groundskeeper/');
	define('COREPATH', ROOTPATH.'core/');
	define('APPPATH', ROOTPATH.'app/');
	define('PUBPATH', ROOTPATH.'public/');
	define('VENDORPATH', ROOTPATH.'vendor/');
	define('CONTROLLERSPATH', APPPATH.'controllers/');
	define('BASEURL', 'https://groundskeeper.dev:7890/');
}