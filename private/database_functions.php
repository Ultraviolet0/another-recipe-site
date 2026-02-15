<?php

function db_connect()
{
  global $db_config;

  $connection = new mysqli(
    $db_config['host'],
    $db_config['username'],
    $db_config['password'],
    $db_config['dbname']
  );

  $connection->set_charset($db_config['charset'] ?? 'utf8mb4');

  confirm_db_connect($connection);
  return $connection;
}

function confirm_db_connect($connection)
{
  if ($connection->connect_errno) {
    $msg = "Database connection failed: ";
    $msg .= $connection->connect_error;
    $msg .= " (" . $connection->connect_errno . ")";
    exit($msg);
  }
}

function db_disconnect($connection)
{
  if (isset($connection)) {
    $connection->close();
  }
}
