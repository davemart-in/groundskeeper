<?php if (!defined('COREPATH')) die('No direct script access allowed');
	
function cookie_delete($name) {
	if (!isset($_COOKIE[$name])) {
		return false;
	}
	
	cookie_set($name, "", time() - 3600);
	return true;
}

function cookie_get($name) {
	
	if (!isset($_COOKIE[$name])) {
		return false;
	}
	
	return safe($_COOKIE[$name]);
}

function cookie_set($name='', $value='', $expires=0, $domain='', $path='/', $prefix='', $samesite='Lax') {
    if ($expires > 0) {
        $expires = time() + $expires;
    }

    $samesite = in_array($samesite, ['None', 'Lax', 'Strict']) ? $samesite : 'Lax';

    $cookie_options = [
        'expires' => $expires,
        'path' => $path,
        'domain' => $domain,
        'secure' => true, // Secure flag should be set if SameSite=None
        'httponly' => true, // HttpOnly flag is a good practice for security
        'samesite' => $samesite
    ];

    return setcookie($prefix.$name, $value, $cookie_options);
}