<?php

$host = "db";
$user = "user";
$password = "password";
$database = "valleverde_db"; // o valleverde_db

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "✅ Conexión exitosa a la base de datos";

?>


<?php

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALLE VERDE</title>
    <link rel="stylesheet" href="https://lab.lacallecr.com/VV/apps/login/css/style.css">
    <link href='https://lab.lacallecr.com/VV/apps/login/css/style_login.css' rel='stylesheet'>
    
</head>
 <?php  require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/ui/head.php"); ?>
 <script type="text/javascript" src="https://lab.lacallecr.com/VV/apps/login/js/functions.js"></script>
<body>

    <div class="wrapper">
        <form action="">
            <h1>VALLE VERDE</h1>
            <div class="input-box">
                <input type="text" id="user" placeholder="Filial" required>
                <!-- <i class='bx bxs-user'></i> -->
            </div>
            <div class="input-box">
                <input type="password" id="pass" placeholder="Password" value="*******" required>
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
