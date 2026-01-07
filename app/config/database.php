<?php if (!defined('COREPATH')) exit('No direct script access allowed');

$db_config = array();

// Validate ENV constant is set
if (!defined('ENV')) {
	error_log('Security Error: ENV constant not defined in database.php');
	exit('Configuration error');
}

if ('local' == ENV) {
	// Validate all required local environment variables are set
	$required_vars = array('LOCAL_DB_HOST', 'LOCAL_DB_USERNAME', 'LOCAL_DB_PASSWORD', 'LOCAL_DB_NAME');
	foreach ($required_vars as $var) {
		if (getenv($var) === false || empty(getenv($var))) {
			error_log("Security Error: Required environment variable $var is not set");
			exit('Configuration error');
		}
	}
	
	$db_config['host'] = getenv('LOCAL_DB_HOST');
	$db_config['user'] = getenv('LOCAL_DB_USERNAME');
	$db_config['pass'] = getenv('LOCAL_DB_PASSWORD');
	$db_config['dbname'] = getenv('LOCAL_DB_NAME');
	$db_config['error_email'] = getenv('ERROR_EMAIL') ?: 'designpro@gmail.com';
} else {
	// Validate all required production environment variables are set
	$required_vars = array('PROD_DB_HOST', 'PROD_DB_USERNAME', 'PROD_DB_PASSWORD', 'PROD_DB_NAME');
	foreach ($required_vars as $var) {
		if (getenv($var) === false || empty(getenv($var))) {
			error_log("Security Error: Required environment variable $var is not set");
			exit('Configuration error');
		}
	}
	
	$db_config['host'] = getenv('PROD_DB_HOST');
	$db_config['user'] = getenv('PROD_DB_USERNAME');
	$db_config['pass'] = getenv('PROD_DB_PASSWORD');
	$db_config['dbname'] = getenv('PROD_DB_NAME');
	$db_config['error_email'] = getenv('ERROR_EMAIL') ?: 'designpro@gmail.com';
}

// Additional security: Validate database configuration values
if (empty($db_config['host']) || empty($db_config['user']) || empty($db_config['dbname'])) {
	error_log('Security Error: Invalid database configuration detected');
	exit('Configuration error');
}