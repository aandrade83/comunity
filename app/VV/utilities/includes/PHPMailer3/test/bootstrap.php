<?php

require_once '../../PHPMailer-PRUEBAS/test/vendor/autoload.php';

spl_autoload_register(function ($class) {

    require_once strtr($class, '\\_', '//').'.php';

});

