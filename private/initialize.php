<?php
ob_start(); // turn on output buffering

define("PRIVATE_PATH", dirname(__FILE__));
define("PROJECT_PATH", dirname(PRIVATE_PATH));
define("PUBLIC_PATH", PROJECT_PATH . '/public_html');
define("SHARED_PATH", PRIVATE_PATH . '/shared');

require_once(PROJECT_PATH . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_PATH);
$dotenv->load();

$public_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$public_dir = ($public_dir === '.' || $public_dir === '/') ? '' : rtrim($public_dir, '/');
define('WWW_ROOT', $public_dir);

$db_config = require(PRIVATE_PATH . '/config/db_credentials.php');

require_once('functions.php');
require_once('status_error_functions.php');
require_once('database_functions.php');
require_once('validation_functions.php');

// Autoload class definitions
function my_autoload($class)
{
  if (preg_match('/\A\w+\Z/', $class)) {
    include 'classes/' . strtolower($class) . '.class.php';
  }
}

spl_autoload_register('my_autoload');

// DB Connect
$database = db_connect();
DatabaseObject::set_database($database);

$session = new Session;
