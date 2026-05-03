<?php

/**
 * Connect to the database.
 *
 * @return mysqli database connection
 */
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

/**
 * Confirm that the database connection was successful.
 *
 * @param mysqli $connection - database connection to check
 */
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

/**
 * Disconnect from the database.
 *
 * @param mysqli $connection - database connection to close
 * 
 * @return bool true if the database connection was closed
 */
function db_disconnect($connection)
{
  if ($connection instanceof mysqli) {
    $connection->close();
    return true;
  }

  return false;
}

/**
 * Check whether the site is running in the development environment.
 *
 * @return bool true if the current environment is development
 */
function is_development_environment()
{
  return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
}
