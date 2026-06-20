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
        <form action="" method="post">
            <h1>VALLE VERDE</h1>
            <div class="input-box">
                <input type="text" id="user" placeholder="Filial" required autocomplete="username">
                <!-- <i class='bx bxs-user'></i> -->
            </div>
            <div class="input-box" style="position:relative;">
                <input type="password" id="pass" placeholder="Password" required autocomplete="current-password">
                <span id="togglePass" title="Mostrar/Ocultar contraseña"
                      style="position:absolute;right:14px;top:50%;transform:translateY(-50%);
                             cursor:pointer;color:#aaa;font-size:16px;user-select:none;">👁</span>
            </div>

            <div class="remember-forgot" style="color:red;font-size:18px;">
                <label id="loginMsg" style="display:none">Filial o Passwords Incorrectos</label>
            </div>

            <button type="button" class="btn" id="loginBtn">Login</button>

            <div class="register-link">
                <p><a href="#" id="openResetModal" style="font-size:12px;color:#aaa;text-decoration:none;">
                    Registrarse / Resetear Password
                </a></p>
            </div>
        </form>
    </div>

    <!-- Modal Registrarse / Resetear Password -->
    <div id="resetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);
         z-index:9999;align-items:center;justify-content:center;">
      <div style="background:#fff;border-radius:12px;padding:32px 28px;width:360px;max-width:95%;
                  box-shadow:0 8px 32px rgba(0,0,0,0.2);font-family:'Segoe UI',Arial,sans-serif;">

        <h3 style="margin:0 0 6px;font-size:17px;color:#2c3e50;">Registrarse / Resetear Password</h3>
        <hr style="border:none;border-top:1px solid #eee;margin:0 0 16px;">

        <!-- PASO 1: filial + correo -->
        <div id="resetStep1">
          <p style="font-size:13px;color:#666;margin:0 0 14px;line-height:1.6;">
            Si tu correo ya está registrado en el sistema, recibirás un correo con tu usuario y contraseña.
          </p>
          <input type="number" id="rFilial" placeholder="# Filial"
                 style="width:100%;margin-bottom:10px;padding:9px 12px;box-sizing:border-box;
                        border:1px solid #ccc;border-radius:6px;font-size:14px;">
          <input type="email" id="rEmail" placeholder="Correo electrónico"
                 style="width:100%;margin-bottom:10px;padding:9px 12px;box-sizing:border-box;
                        border:1px solid #ccc;border-radius:6px;font-size:14px;">
          <div id="resetMsg" style="font-size:13px;margin-bottom:10px;display:none;padding:8px 12px;
               border-radius:5px;background:#fef9f9;border:1px solid #f5c6cb;"></div>
          <button id="btnResetSubmit"
                  style="width:100%;padding:10px;background:#337ab7;color:#fff;border:none;
                         border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;">Enviar</button>
        </div>

        <!-- PASO 2: correo no encontrado → solicitar registro -->
        <div id="resetStep2" style="display:none;">
          <p style="font-size:13px;color:#c0392b;margin:0 0 14px;line-height:1.6;">
            Tu correo no está registrado. Completa tus datos y el Comité Asesor procesará tu solicitud.
          </p>
          <input type="text" id="rNombre" placeholder="Nombre"
                 style="width:100%;margin-bottom:8px;padding:9px 12px;box-sizing:border-box;
                        border:1px solid #ccc;border-radius:6px;font-size:14px;">
          <input type="text" id="rApellido" placeholder="Apellido"
                 style="width:100%;margin-bottom:8px;padding:9px 12px;box-sizing:border-box;
                        border:1px solid #ccc;border-radius:6px;font-size:14px;">
          <input type="text" id="rTelefono" placeholder="Teléfono (opcional)"
                 style="width:100%;margin-bottom:10px;padding:9px 12px;box-sizing:border-box;
                        border:1px solid #ccc;border-radius:6px;font-size:14px;">
          <div id="resetMsg2" style="font-size:13px;margin-bottom:10px;display:none;padding:8px 12px;
               border-radius:5px;background:#fef9f9;border:1px solid #f5c6cb;"></div>
          <button id="btnSolicitarAcceso"
                  style="width:100%;padding:10px;background:#e74c3c;color:#fff;border:none;
                         border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;">Solicitar Acceso</button>
        </div>

        <p style="text-align:center;margin:14px 0 0;">
          <a href="#" id="closeResetModal" style="font-size:12px;color:#bbb;text-decoration:none;">Cerrar</a>
        </p>
      </div>
    </div>

</body>

</html>
