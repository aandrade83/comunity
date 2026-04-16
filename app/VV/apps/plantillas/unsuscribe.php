<?php
/**
 * unsuscribe.php — Página de confirmación de baja de notificaciones.
 *
 * Flujo:
 *   GET  ?id=X&token=HASH  → muestra advertencia + botón de confirmación.
 *   POST id=X, token=HASH  → actualiza email_flag = 0 y muestra resultado.
 *
 * No requiere sesión activa; la seguridad recae en el token HMAC.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once __DIR__ . '/unsuscribe_block.php';     // unsub_token()

$logo_url = BASE_URL . '/apps/ui/images/Logo_VV.png';

// ── Leer parámetros ────────────────────────────────────────────────────────
$id    = (int)($_REQUEST['id']    ?? 0);
$token = trim($_REQUEST['token']  ?? '');

// ── Validación básica ──────────────────────────────────────────────────────
$error = '';
$row   = null;

if (!$id || !$token) {
    $error = 'Enlace inválido o incompleto.';
} else {
    db_connect('master');
    $db = $GLOBALS['conn_db']->mysqli_connector;

    $st = $db->prepare('SELECT id, nombre, apellido, email, email_flag FROM condominos WHERE id = ?');
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $st->close();

    if (!$row) {
        $error = 'Registro no encontrado.';
    } elseif (!hash_equals(unsub_token($id, $row['email']), $token)) {
        $error = 'Token inválido. El enlace puede haber expirado o sido alterado.';
    }
}

// ── Procesar confirmación (POST) ───────────────────────────────────────────
$done = false;
if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((int)$row['email_flag'] === 0) {
        $done = true; // ya estaba dado de baja
    } else {
        $st2 = $db->prepare('UPDATE condominos SET email_flag = 0 WHERE id = ?');
        $st2->bind_param('i', $id);
        $st2->execute();
        $st2->close();
        $done = true;
    }
}

$nombre_display = $row ? htmlspecialchars($row['nombre'] . ' ' . $row['apellido'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notificaciones — Valle Verde</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #f4f6f8;
      font-family: 'Segoe UI', Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 16px;
    }
    .card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.10);
      max-width: 520px;
      width: 100%;
      overflow: hidden;
    }
    .card-header {
      background: #2e7d32;
      padding: 24px 32px;
      text-align: center;
    }
    .card-header img { height: 50px; }
    .card-body { padding: 32px; }
    h2 { font-size: 20px; color: #222; margin-bottom: 12px; }
    p  { font-size: 14px; color: #555; line-height: 1.7; margin-bottom: 14px; }
    .warning-box {
      background: #fff8e1;
      border-left: 4px solid #f9a825;
      border-radius: 4px;
      padding: 14px 18px;
      margin: 20px 0;
    }
    .warning-box p { margin: 0; color: #5d4037; font-size: 14px; }
    .warning-box strong { display: block; margin-bottom: 6px; color: #e65100; }
    .btn {
      display: inline-block;
      padding: 11px 24px;
      border-radius: 5px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      border: none;
      width: 100%;
      text-align: center;
      margin-top: 6px;
    }
    .btn-danger  { background: #c0392b; color: #fff; }
    .btn-default { background: #eee;    color: #555; }
    .btn-danger:hover  { background: #a93226; }
    .btn-default:hover { background: #ddd; }
    .success-icon { font-size: 48px; text-align: center; margin-bottom: 12px; }
    .error-icon   { font-size: 48px; text-align: center; margin-bottom: 12px; }
  </style>
</head>
<body>
<div class="card">

  <div class="card-header">
    <img src="<?= htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8') ?>" alt="Valle Verde">
  </div>

  <div class="card-body">

    <?php if ($error): ?>

      <!-- ── Error ──────────────────────────────────────────────────── -->
      <div class="error-icon">⚠️</div>
      <h2>Enlace no válido</h2>
      <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <p>Si necesitas darte de baja, escríbenos directamente al administrador del condominio.</p>

    <?php elseif ($done): ?>

      <!-- ── Confirmado ─────────────────────────────────────────────── -->
      <div class="success-icon">✅</div>
      <h2>Listo, <?= $nombre_display ?></h2>
      <p>
        Has sido dado de baja del sistema de notificaciones de Valle Verde.<br>
        Ya no recibirás correos de nuestra plataforma.
      </p>
      <p style="color:#999; font-size:12px;">
        Si esto fue un error, puedes comunicarte con la administración del condominio
        para volver a activar tus notificaciones.
      </p>

    <?php else: ?>

      <!-- ── Confirmación (GET) ─────────────────────────────────────── -->
      <h2>Hola, <?= $nombre_display ?></h2>
      <p>
        Recibimos tu solicitud para salir del sistema de notificaciones
        de <strong>Valle Verde</strong>.
      </p>

      <div class="warning-box">
        <strong>⚠️ Antes de continuar, ten en cuenta:</strong>
        <p>
          Al salirte del sistema de notificaciones podrías perderte de
          <strong>Temas</strong>, <strong>Actividades</strong> y
          <strong>Encuestas</strong> de nuestro condominio que se comunican
          por esta vía.
        </p>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="id"    value="<?= $id ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-danger">
          Abandonar Notificaciones
        </button>
      </form>

      <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/apps/Forum/index.php"
         class="btn btn-default" style="margin-top:10px;">
        Cancelar, quiero seguir recibiendo correos
      </a>

    <?php endif; ?>

  </div>
</div>
</body>
</html>
