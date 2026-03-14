<?php
ob_start(); // turn on output buffering

define("PRIVATE_PATH", dirname(__FILE__));
define("PROJECT_PATH", dirname(PRIVATE_PATH));
define("PUBLIC_PATH", PROJECT_PATH . '/public_html');
define("SHARED_PATH", PRIVATE_PATH . '/shared');
define('ENVIRONMENT', 'development');

require_once(PROJECT_PATH . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_PATH);
$dotenv->load();

$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$pos = strpos($script_name, '/public_html');
if ($pos !== false) {
  $public_dir = substr($script_name, 0, $pos) . '/public_html';
} else {
  $public_dir = '';
}
define('WWW_ROOT', $public_dir);

$db_config = require(PRIVATE_PATH . '/config/db_credentials.php');

require_once('functions.php');
require_once('status_error_functions.php');
require_once('database_functions.php');
require_once('validation_functions.php');
require_once('recipe_functions.php');
require_once('image_upload_functions.php');

// Autoload class definitions
function my_autoload($class)
{
  if (preg_match('/\A\w+\Z/', $class)) {
    include 'classes/' . strtolower($class) . '.class.php';
  }
}

spl_autoload_register('my_autoload');

// DB Connect
try {
  $database = db_connect();
} catch (Exception $e) {
  if (is_development_environment()) {
    echo $e->getMessage();
  } else {
    echo "Database connection error.";
  }
  exit();
}
DatabaseObject::set_database($database);

$session = new Session;
