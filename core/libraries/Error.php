<?php if (!defined('APPPATH')) exit('No direct script access allowed');

function error($type, $message, $file='', $line='', $url='') {
	$year = date("y");
	$month = date("m");
	$day = date("d");
	$path = APPPATH.'errors/'.$year.'-'.$month.'-'.$day.'.log';
	// Prep file path
	if ($file === null) {
		$file = ''; // Or some other appropriate default value
	}
	$file = str_replace(ROOTPATH, '', $file);
	$file = str_replace(BASEURL, 'public/', $file);
	// Prep URL
	$url = str_replace(BASEURL, '', $url);
	$server_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	if (isset($server_uri) && substr($server_uri, 0, 1) == '/') {
		$server_uri = substr($server_uri, 1);
	}	
	/* Prep data --------------------------------- */
	$error_data = [
		'type' => $type,
		'time' => date('H:i:s'),
		'message' => $message,
		'file' => $file,
		'line' => $line,
		'username' => (!empty($_SESSION['username'])) ? $_SESSION['username'] : '',
		'url' => (!empty($url)) ? $url : $server_uri
	];
	// Sanitize all values in $error_data to prevent log injection
	$error_data = array_map(function($value) {
		if (is_null($value)) {
			return '';
		}
		// Convert to string
		$value = (string) $value;
		// Remove pipe delimiter
		$value = str_replace('|', '_', $value);
		// Remove all control characters and newlines
		$value = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value);
		// Replace multiple spaces with single space
		$value = preg_replace('/\s+/', ' ', $value);
		// Trim whitespace
		$value = trim($value);
		// Limit length to prevent excessive log entries
		if (strlen($value) > 1000) {
			$value = substr($value, 0, 997) . '...';
		}
		return $value;
	}, $error_data);
	// Format log message into single string concatenated by |
	$error_log = implode('|', $error_data);
	// Add new line to the end of the log message
	$error_log = $error_log . PHP_EOL;
	/* Log the error --------------------------------- */
	file_write($path, $error_log);
}

function php_error_handler($errno, $errstr, $errfile, $errline) {
	$message = $errno . ' - ' . $errstr;
	return error('php', $message, $errfile, $errline);
}

function php_fatal_handler() {
	$errfile = '';
	$errstr  = 'Fatal error';
	$errno   = E_CORE_ERROR;
	$errline = 0;

	$error = error_get_last();

	if($error !== NULL) {
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		$message = $errno . ' - ' . $errstr;

		error('php', $message, $errfile, $errline);
	}
}