<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/* GLOBALS ------------------------------------------- */
$glob = array();

/* LOCAL FILE MANIPULATION ------------------------------------------- */
require_once(COREPATH.'libraries/File.php');

/* DATABASE ------------------------------------------- */
require_once(COREPATH.'libraries/Database.php');

/* ENCRYPTION ------------------------------------------- */
require_once(COREPATH.'libraries/Encryption.php');

/* ERROR TRACKING ------------------------------------------- */
require_once(COREPATH.'libraries/Error.php');
// Set default error function for PHP
set_error_handler('php_error_handler');
// Catch fatal PHP errors
register_shutdown_function('php_fatal_handler');

/* ENV FILE CONVERSION ------------------------------------------- */
require_once(COREPATH.'libraries/Env.php');
env_init();

/* LOAD COMMON FUNCTIONS ------------------------------------------- */
require_once(COREPATH.'Common.php');

/* SESSIONS ------------------------------------------- */
require_once(COREPATH.'libraries/Session.php');
$handler = new SQLiteSession(Database::getInstance());
session_set_save_handler($handler, true);

/* SESSION ------------------------------------------- */
// Kick start their session
session_start();

/* COOKIES ------------------------------------------- */
require_once (COREPATH.'libraries/Cookie.php');

/* GITHUB API ------------------------------------------- */
require_once(COREPATH.'libraries/GitHubAPI.php');

/* OPENAI API ------------------------------------------- */
require_once(COREPATH.'libraries/OpenAIAPI.php');

/* MODELS ------------------------------------------- */
require_once(APPPATH.'models/User.php');
require_once(APPPATH.'models/Repository.php');
require_once(APPPATH.'models/Issue.php');
require_once(APPPATH.'models/Area.php');
require_once(APPPATH.'models/AnalysisJob.php');
require_once(APPPATH.'models/AnalysisResult.php');

/* ROUTER ------------------------------------------- */
require_once(COREPATH.'libraries/Router.php');