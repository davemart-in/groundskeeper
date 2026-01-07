<?php if (!defined('COREPATH')) exit('No direct script access allowed');

function closeDbAndSessions() {
	global $DB;

	// Close DB if open connection
	if (class_exists('NTRVRTS_DB') AND isset($DB)) {
		$DB->CloseConnection();
	}
	// Close sessions
	session_write_close();
}

function debug($val, $description = '') {
	$output = '';
	$today = date('Y-m-d');
	// If $description is set, add it to the output
	if (!empty($description)) {
		$output .= "## " . $description . ":\n";
	}
	// If $val is an object, convert to string
	if (is_object($val)) {
		$output .= print_r($val, true);
	} elseif (is_array($val)) {
		$output .= print_r($val, true);
	} else {
		$output .= $val;
	}
	$output .= "\n" . '====================================' . "\n";
	file_write(APPPATH . 'debug/' . $today . '.txt', $output, 'a+');
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

function message_check() {
	global $glob;

	// Check for flash cookie
	$flash_msg = cookie_get('flash-msg');
	if (isset($flash_msg)) {
		cookie_delete('flash-msg');
		$glob['msg'] = $flash_msg;
	}
}

function pp($array) {
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

function ppe($array) {
	echo "<pre>";
	print_r($array);
	echo "</pre>";
	exit();
}
function redirect($location, $msg='') {
	global $glob;
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

function str_random($length = 64, $type = 'alphanumeric') {
	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	if ($type == 'numeric') {
		$pool = '0123456789';
	}
	return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

function username_blacklist_check($username) {
	$blacklist = array('about','abuse','account','accounts','admin','administrator','anal','anus','arse','ass','balls','ballsack','bastard','biatch','bitch','blog','bloody','blowjob','bollock','bollok','boner','boob','bugger','bum','business','businesses','butt','buttplug','clitoris','cock','companies','company','contact','coon','crap','cunt','damn','dick','dildo','dyke','email','fag','fagget','faggit','faggot','faq','feck','felching','fellate','fellatio','flange','forum','ftp','fuck','fucker','fucking','fuckn','fudgepacker','games','goddamn','guest','guests','hell','help','homo','horny','hosting','hostmaster','info','jesus','jew','jizz','knobend','labia','legal','masterbate','mohhamed','muff','nigga','nigger','office','penis','piss','policy','poop','prick','privacy','profile','profiles','pube','pussy','rape','root','scrotum','sex','sexual','shit','slut','smegma','spunk','staging','suck','support','tits','tosser','turd','twat','vagina','wank','webmaster','whore','wtf');
	// If $username contains any word in $blacklist array
	foreach ($blacklist as $badWord) {
		if (strpos($username, $badWord) !== false) {
			return true;
		}
	}
	return false;
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