<?php

// Bootstrap: load central config first (defines ROOT_PATH, BASE_URL, DB_*, APP_ENV).
// dirname(__DIR__) resolves to /VV from utilities/includes.php.
require_once dirname(__DIR__) . '/config.php';

header('X-Frame-Options: DENY');
header("X-XSS-Protection: 1; mode=block");
header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set(APP_TIMEZONE);

require_once ROOT_PATH . '/utilities/vars.php';
require_once ROOT_PATH . '/utilities/functions.php';
require_once ROOT_PATH . '/utilities/classes.php';
require_once ROOT_PATH . '/utilities/db/handler.php';





?>