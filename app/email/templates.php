<?php if (!defined('APPPATH')) exit('No direct script access allowed');

// ----------------------------------------------------
// HELP FORM
// ----------------------------------------------------
function template_help($type, $data) {
	// Escape data for HTML email
	$username = htmlspecialchars($data['username'], ENT_QUOTES, 'UTF-8');
	$email = htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8');
	$question = htmlspecialchars($data['question'], ENT_QUOTES, 'UTF-8');
	
	$message = <<<EOF

Username: {$username}<br>

Email: {$email}<br>

Question: {$question}

EOF;
	return $message;
}