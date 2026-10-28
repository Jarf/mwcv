<?php
// Loads secrets from .env (never committed; see .env.example)
$env = parse_ini_file(dirname(__DIR__) . '/.env', false, INI_SCANNER_RAW);
if ($env === false) {
	throw new RuntimeException('Missing or unreadable .env file');
}
define('DB_USER', $env['DB_USER'] ?? '');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? '');
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_TYPE', $env['DB_TYPE'] ?? 'mysql');
unset($env);
