<?php

function db_connect()
{
  global $db_config;

  $connection = @new mysqli(
    $db_config['host'],
    $db_config['username'],
    $db_config['password'],
    $db_config['dbname']
  );

  confirm_db_connect($connection);

  $connection->set_charset($db_config['charset'] ?? 'utf8mb4');

  return $connection;
}

function confirm_db_connect($connection)
{
  if (!($connection instanceof mysqli)) {
    error_log('Database connection failed: connection is not a mysqli instance.');
    throw new Exception('Database connection failed.');
  }

  if ($connection->connect_errno) {
    $detailed_error = "Database connection failed: " .
      $connection->connect_error .
      " (" . $connection->connect_errno . ")";

    error_log($detailed_error);

    if (is_development_environment()) {
      throw new Exception($detailed_error);
    } else {
      throw new Exception('Database connection failed.');
    }
  }
}

function db_disconnect($connection)
{
  if ($connection instanceof mysqli) {
    $connection->close();
    return true;
  }

  return false;
}

function is_development_environment()
{
  return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
}
