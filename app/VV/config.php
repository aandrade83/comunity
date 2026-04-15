<?php

/**
 * config.php — Central configuration file
 *
 * Auto-detects environment based on hostname.
 * No URLs are hardcoded — everything derives from the detected host.
 *
 * HOW TO USE PATHS:   require_once ROOT_PATH . '/utilities/includes.php';
 * HOW TO USE URLS:    echo BASE_URL . '/apps/login/css/style.css';
 * HOW TO USE ASSETS:  echo ASSETS_URL . '/tema/images/img.jpg';
 */

// ─── FILE SYSTEM PATHS ────────────────────────────────────────────────────────
// ROOT_PATH = the /VV directory on disk (works in any environment).
define('ROOT_PATH', dirname(__FILE__));

// ─── ENVIRONMENT AUTO-DETECTION ───────────────────────────────────────────────
// Detects local (Docker/localhost) vs production automatically.
// To force an environment, set the APP_ENV server/env variable.
$_detected_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_detected_env  = getenv('APP_ENV') ?: null;

if (!$_detected_env) {
    $localHosts = ['localhost', '127.0.0.1', '0.0.0.0'];
    $isLocal    = in_array(strtolower(explode(':', $_detected_host)[0]), $localHosts);
    $_detected_env = $isLocal ? 'development' : 'production';
}

define('APP_ENV', $_detected_env);
unset($_detected_env, $_detected_host, $localHosts, $isLocal);

// ─── BASE URL ─────────────────────────────────────────────────────────────────
// Built dynamically from the current request — no hardcoded domain.
// Result examples:
//   local:      http://localhost:8080/VV
//   production: https://lab.lacallecr.com/VV
$_scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL',   $_scheme . '://' . $_host . '/VV');
define('ASSETS_URL', BASE_URL . '/utilities/tema');
unset($_scheme, $_host);

// ─── TIMEZONE ─────────────────────────────────────────────────────────────────
define('APP_TIMEZONE', 'America/Costa_Rica');

// ─── DATABASE ─────────────────────────────────────────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: 'db');
define('DB_USER',     getenv('DB_USER')     ?: 'user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'password');
define('DB_NAME',     getenv('DB_NAME')     ?: 'valleverde_db');
define('DB_CHARSET',  'utf8');

// ─── ERROR REPORTING ──────────────────────────────────────────────────────────
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
    ini_set('display_errors', '1');
}
