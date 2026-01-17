<?php

// Prevent direct access to this configuration file
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
	exit('No direct script access allowed');
}

// Debug mode?
define('DEBUG', false);

// Environment
define('ENV', 'local');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Paths
define('ROOTPATH', '/Users/davem/Sites/groundskeeper/');
define('COREPATH', ROOTPATH.'core/');
define('APPPATH', ROOTPATH.'app/');
define('PUBPATH', ROOTPATH.'public/');
define('VENDORPATH', ROOTPATH.'vendor/');
define('CONTROLLERSPATH', APPPATH.'controllers/');
define('BASEURL', 'https://groundskeeper.dev:7890/');