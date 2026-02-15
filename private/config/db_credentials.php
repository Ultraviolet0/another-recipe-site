<?php
// config/db_credentials.php
$config = [
'host' => $_ENV['DB_HOST'] ?? 'localhost',
'dbname' => $_ENV['DB_NAME'] ?? null,
'username' => $_ENV['DB_USER'] ?? null,
'password' => $_ENV['DB_PASS'] ?? null,
'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

if(empty($config['dbname']) || empty($config['username'])) {
  exit("Database credentials missing. Check .env");
}

return $config;
