<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/* MAINTENANCE MODE ------------------------------------------- */
if (MAINTENANCE_MODE) {
	return require_once(APPPATH.'views/maintenance.php');
}

/* GLOBALS ------------------------------------------- */
$glob = array();

/* LOCAL FILE MANIPULATION ------------------------------------------- */
require_once(COREPATH.'libraries/File.php');

/* ENCRYPTION ------------------------------------------- */
require_once(COREPATH.'libraries/Encryption.php');

/* ERROR TRACKING ------------------------------------------- */
require_once(COREPATH.'libraries/Error.php');
// Set default error function for PHP
set_error_handler('php_error_handler');
// Catch fatal PHP errors
register_shutdown_function('php_fatal_handler');

/* LOAD COMPOSER LIBRARIES ------------------------------------------- */
// Predis
require_once(ROOTPATH.'vendor/autoload.php');

/* ENV FILE CONVERSION ------------------------------------------- */
require_once(COREPATH.'libraries/Env.php');
env_init();

/* LOAD COMMON FUNCTIONS ------------------------------------------- */
require_once(COREPATH.'Common.php');

/* SESSIONS ------------------------------------------- */
// Handled in Redis
Predis\Autoloader::register();
$redis = new Predis\Client();

require_once(COREPATH.'libraries/Session.php');
$handler = new RedisSession($redis);
session_set_save_handler($handler, true);

/* SESSION ------------------------------------------- */
// Kick start their session
session_start();

/* COOKIES ------------------------------------------- */
require_once (COREPATH.'libraries/Cookie.php');

/* CSRF PROTECTION ------------------------------------------- */
require_once(COREPATH.'libraries/Csrf.php');

/* AUTH ------------------------------------------- */
require_once (COREPATH.'libraries/Auth.php');

/* IMAGES ------------------------------------------- */
require_once (COREPATH.'libraries/Image.php');

/* EMAIL TEMPLATES ------------------------------------------- */
require_once(APPPATH.'email/templates.php');

/* HASHID ------------------------------------------- */
require_once(COREPATH.'libraries/hashid/HashGenerator.php');
require_once(COREPATH.'libraries/hashid/Hashid.php');
$hashid = new Hashids\Hashids();

/* GITHUB OAUTH ------------------------------------------- */
require_once(COREPATH.'libraries/GitHubOAuth.php');

/* GITHUB API ------------------------------------------- */
require_once(COREPATH.'libraries/GitHubAPI.php');

/* CLAUDE API ------------------------------------------- */
require_once(COREPATH.'libraries/ClaudeAPI.php');

/* OPENAI API ------------------------------------------- */
require_once(COREPATH.'libraries/OpenAIAPI.php');

/* MODELS ------------------------------------------- */
require_once(APPPATH.'models/User.php');
require_once(APPPATH.'models/Repository.php');
require_once(APPPATH.'models/Issue.php');
require_once(APPPATH.'models/Area.php');
require_once(APPPATH.'models/AnalysisJob.php');

/* ROUTER ------------------------------------------- */
require_once(COREPATH.'libraries/Router.php');