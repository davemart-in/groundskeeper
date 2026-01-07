<?php if (!defined('APPPATH')) exit('No direct script access allowed');
// Inspiration from: https://dev.to/fadymr/php-create-your-own-php-dotenv-3k2i
function env_init() {
	$path = APPPATH.'config/.env'; // Local
    if ('local' != ENV) {
        $path = ROOTPATH.'.env'; // Staging & Production
    }
	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignore comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Parse line
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Add to ENV
        putenv(sprintf('%s=%s', $name, $value));
    }
}