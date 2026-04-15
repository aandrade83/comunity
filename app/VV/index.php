<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/VV/utilities/includes.php");
// After this point ROOT_PATH and BASE_URL are available (defined in config.php).
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALLE VERDE</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/apps/login/css/style.css?v=<?= filemtime(ROOT_PATH . '/apps/login/css/style.css') ?>">
    <link href="<?= BASE_URL ?>/apps/login/css/style_login.css?v=<?= filemtime(ROOT_PATH . '/apps/login/css/style_login.css') ?>" rel="stylesheet">

</head>
<?php require_once ROOT_PATH . '/utilities/ui/head.php'; ?>
<script type="text/javascript" src="<?= BASE_URL ?>/apps/login/js/functions.js?v=<?= filemtime(ROOT_PATH . '/apps/login/js/functions.js') ?>"></script>
<body>

    <div class="wrapper">
        <form action="">
            <h1>VALLE VERDE</h1>
            <div class="input-box">
                <input type="text" id="user" placeholder="Filial" required>
                <!-- <i class='bx bxs-user'></i> -->
            </div>
            <div class="input-box">
                <input type="password" id="pass" placeholder="Password" required>
                <!-- <i class='bx bxs-lock-alt'></i> -->
            </div>

            <div class="remember-forgot" style="color: red;
    font-size: 18px;">
                <label id="loginMsg" style="display: none"> Filial o Passwords Incorrectos</label>
                
            </div>

            <button type="button" class="btn" id="loginBtn">Login</button>

            <div class="register-link">
                <!-- <p>Don't have an account? <a href="#">Register</a></p> -->
            </div>
        </form>
    </div>

</body>

</html>
