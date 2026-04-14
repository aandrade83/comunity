<?php

header('X-Frame-Options: DENY');

header("X-XSS-Protection: 1; mode=block");

header('Content-Type: text/html; charset=utf-8');

//date_default_timezone_set("America/Costa_Rica");



include($_SERVER['DOCUMENT_ROOT'].'/VV/utilities/vars.php');

include($_SERVER['DOCUMENT_ROOT'].'/VV/utilities/functions.php');

include($_SERVER['DOCUMENT_ROOT'].'/VV/utilities/classes.php');

include($_SERVER['DOCUMENT_ROOT'].'/VV/utilities/db/handler.php');





?>