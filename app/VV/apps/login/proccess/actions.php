<?php

//header("Access-Control-Allow-Origin: https://www.lab.lacallecr.com");

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

$user      = param("user");
$pass_raw  = param("pass");   // valor limpio antes de encriptar (para validar vacío)
$action    = param("ac");

$pass = biencript($pass_raw);

switch ($action){


  case "login":

   // Validar credenciales vacías ANTES de consultar la DB.
   // secure_input() elimina caracteres no permitidos (ej. *), dejando cadenas vacías.
   if (empty(trim($user)) || empty(trim($pass_raw))) {
       $data['login'] = "2";
       echo json_encode($data);
       break;
   }

   $login = get_master_login($user,$pass);
   $total = 0;
  
    $log_ip = get_ip();
    $log = new _logs();
    if(isset($login->vars["id"])){ $id = $login->vars["id"];} else { $id = 0;}
    $log->vars["user"] = $id;
    $log->vars["ip"] = $log_ip;
    $log->vars["date"] = date("Y-m-d H:i:s");

  if(!is_null($login)){
    
    session_start();
    session_regenerate_id(true);
    $_SESSION['loged'] = "1";
    $_SESSION['user'] = $login->vars["id"];
    $_SESSION['filial'] = $login->vars["filial"];
    $_SESSION['rol'] = $login->vars["rol"];
    
    if($login->vars["activo"] == 0){
      $log->vars["data"] = "Unactive user ";
      $data['login'] = "3"; 
      
    } else {
       $log->vars["data"] = "Login Succesfully ".$login->vars["filial"];
       
       if($login->vars["rol"] == 2){
        $pendings = get_pending_topics_total();
        
        $total = $pendings['total'] ;
       }
        $data['total'] = $total;      


       $data['login'] = "1"; 
    }
  
  }else{
      $data['login'] = "2"; 
     $log->vars["data"]= "Failure Login ".$user; 
   }
    $log->insert();

   echo json_encode($data);
   break;

   case "logout":

          session_start();
          session_unset();
          session_destroy();
          $data['login'] = "1"; 
          echo json_encode($data);


    break;



  // ── Enviar credenciales si el correo existe en condominos ──────────────────
  case "resetPass":
    header('Content-Type: application/json; charset=utf-8');
    ob_start();

    $email_raw = trim((string)param('email'));
    if (!$email_raw || !filter_var($email_raw, FILTER_VALIDATE_EMAIL)) {
      ob_clean(); echo json_encode(['status' => 'error', 'msg' => 'Correo inválido.']); exit;
    }

    db_connect('master');
    global $conn_db;
    $email_esc = $conn_db->mysqli_connector->real_escape_string($email_raw);
    $rows = get_str("SELECT * FROM condominos WHERE email = '{$email_esc}' LIMIT 1", false);

    if (empty($rows)) {
      ob_clean(); echo json_encode(['status' => 'not_found']); exit;
    }

    $r = $rows[0];
    $dest = [
      'id'    => (int)$r['id'],
      'name'  => trim($r['nombre'] . ' ' . $r['apellido']),
      'email' => $r['email'],
      'filal' => $r['filial'],
      'pass'  => explode('@', $r['email'])[0],
    ];

    require_once ROOT_PATH . '/utilities/mail/Mailer.php';
    require_once ROOT_PATH . '/apps/plantillas/unsuscribe_block.php';

    $filial_txt = htmlspecialchars((string)$dest['filal'], ENT_QUOTES, 'UTF-8');
    $pass_txt   = htmlspecialchars($dest['pass'],          ENT_QUOTES, 'UTF-8');
    $nombre_txt = htmlspecialchars($dest['name'],          ENT_QUOTES, 'UTF-8');
    $login_url  = (defined('BASE_URL') ? BASE_URL : 'https://lab.lacallecr.com/VV') . '/';
    $logo_url   = 'https://lab.lacallecr.com/VV/apps/ui/images/Logo_VV.png';
    $unsub      = plantilla_unsub_block($dest['id'], $dest['email']);

    $html_reset = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0"
           style="background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:600px;width:100%;">
      <tr><td style="background:#2e7d32;padding:28px 40px;text-align:center;">
        <h1 style="margin:0;color:#fff;font-size:20px;font-weight:600;">Condominio Valle Verde</h1>
      </td></tr>
      <tr><td style="padding:28px 40px 0;">
        <p style="margin:0;font-size:15px;color:#333;">Hola, <strong>{$nombre_txt}</strong> 👋</p>
      </td></tr>
      <tr><td style="padding:20px 40px 28px;">
        <p style="margin:0 0 14px;font-size:15px;color:#555;line-height:1.7;">
          Recibimos una solicitud para enviarte tus credenciales de acceso a la plataforma comunitaria.
        </p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr><td style="background:#f0f7f0;border:1px solid #a5d6a7;border-radius:6px;padding:18px 22px;">
            <p style="margin:0 0 12px;font-size:14px;color:#2e7d32;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
              🔑 Datos de acceso
            </p>
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-size:14px;color:#555;padding-bottom:6px;padding-right:16px;"><strong>Usuario:</strong></td>
                <td style="font-size:14px;color:#222;font-weight:700;font-family:monospace;
                           background:#fff;border:1px solid #c8e6c9;border-radius:3px;padding:3px 10px;">{$filial_txt}</td>
              </tr>
              <tr>
                <td style="font-size:14px;color:#555;padding-right:16px;"><strong>Contraseña:</strong></td>
                <td style="font-size:14px;color:#222;font-weight:700;font-family:monospace;
                           background:#fff;border:1px solid #c8e6c9;border-radius:3px;padding:3px 10px;">{$pass_txt}</td>
              </tr>
            </table>
          </td></tr>
        </table>
        <p style="margin:0;font-size:14px;color:#777;line-height:1.6;">
          Si no solicitaste este correo, puedes ignorarlo. Saludos,<br>
          <strong>Comité Asesor — Condominio Valle Verde</strong>
        </p>
      </td></tr>
      <tr><td style="padding:0 40px 32px;text-align:center;">
        <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
          <tr><td style="border-radius:5px;background:#2e7d32;">
            <a href="{$login_url}" style="display:inline-block;padding:13px 36px;color:#fff;
               font-size:15px;font-weight:600;text-decoration:none;border-radius:5px;">
              Entrar al sistema →
            </a>
          </td></tr>
        </table>
      </td></tr>
      <tr><td style="padding:0 40px;"><hr style="border:none;border-top:1px solid #eee;margin:0;"></td></tr>
      {$unsub}
      <tr><td style="padding:20px 40px 28px;text-align:center;background:#fafafa;">
        <img src="{$logo_url}" alt="Valle Verde" width="110" style="display:inline-block;max-width:110px;height:auto;margin-bottom:10px;">
        <p style="margin:6px 0 0;font-size:11px;color:#bbb;">© 2025 Condominio Valle Verde. Todos los derechos reservados.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;

    try {
      ob_clean();
      (new \VV\Mail\Mailer())->send($dest['email'], 'Tus credenciales — Condominio Valle Verde', $html_reset, $dest['name']);
      echo json_encode(['status' => 'sent', 'msg' => 'Correo enviado. Revisa tu bandeja de entrada.']);
    } catch (\Throwable $e) {
      echo json_encode(['status' => 'error', 'msg' => 'No se pudo enviar el correo. Intenta más tarde.']);
    }
    exit;


  // ── Solicitud de nuevo usuario (correo no existe en sistema) ────────────────
  case "newUserRequest":
    header('Content-Type: application/json; charset=utf-8');
    ob_start();

    $nr_filial   = (int)param('filial');
    $nr_email    = trim((string)param('email'));
    $nr_nombre   = trim((string)param('nombre'));
    $nr_apellido = trim((string)param('apellido'));
    $nr_telefono = trim((string)param('telefono'));

    if (!$nr_nombre || !$nr_apellido || !$nr_email) {
      ob_clean(); echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos.']); exit;
    }

    require_once ROOT_PATH . '/utilities/mail/Mailer.php';

    $base_url   = defined('BASE_URL') ? BASE_URL : 'https://lab.lacallecr.com/VV';
    $btn_params = http_build_query([
      'filial'   => $nr_filial,
      'email'    => $nr_email,
      'nombre'   => $nr_nombre,
      'apellido' => $nr_apellido,
      'telefono' => $nr_telefono,
    ]);
    $btn_url = $base_url . '/apps/login/proccess/crear_usuario.php?' . $btn_params;

    $h_filial   = htmlspecialchars((string)$nr_filial,   ENT_QUOTES, 'UTF-8');
    $h_email    = htmlspecialchars($nr_email,             ENT_QUOTES, 'UTF-8');
    $h_nombre   = htmlspecialchars($nr_nombre,            ENT_QUOTES, 'UTF-8');
    $h_apellido = htmlspecialchars($nr_apellido,          ENT_QUOTES, 'UTF-8');
    $h_telefono = htmlspecialchars($nr_telefono ?: '—',  ENT_QUOTES, 'UTF-8');
    $h_btn_url  = htmlspecialchars($btn_url,              ENT_QUOTES, 'UTF-8');
    $h_fecha    = date('d/m/Y H:i');

    $html_request = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0"
           style="background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:600px;width:100%;">
      <tr><td style="background:#1a237e;padding:24px 40px;text-align:center;">
        <h1 style="margin:0;color:#fff;font-size:18px;font-weight:600;">🆕 NEW USER REQUEST</h1>
        <p style="margin:6px 0 0;color:#c5cae9;font-size:13px;">Condominio Valle Verde — {$h_fecha}</p>
      </td></tr>
      <tr><td style="padding:28px 40px;">
        <p style="margin:0 0 18px;font-size:15px;color:#333;line-height:1.7;">
          Un visitante solicitó acceso a la plataforma con los siguientes datos:
        </p>
        <table width="100%" cellpadding="0" cellspacing="0"
               style="border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;margin-bottom:28px;">
          <tr style="background:#f5f5f5;">
            <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#555;width:35%;">Filial</td>
            <td style="padding:10px 16px;font-size:14px;color:#222;">{$h_filial}</td>
          </tr>
          <tr>
            <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#555;border-top:1px solid #eee;">Nombre</td>
            <td style="padding:10px 16px;font-size:14px;color:#222;border-top:1px solid #eee;">{$h_nombre}</td>
          </tr>
          <tr style="background:#f5f5f5;">
            <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#555;border-top:1px solid #eee;">Apellido</td>
            <td style="padding:10px 16px;font-size:14px;color:#222;border-top:1px solid #eee;">{$h_apellido}</td>
          </tr>
          <tr>
            <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#555;border-top:1px solid #eee;">Teléfono</td>
            <td style="padding:10px 16px;font-size:14px;color:#222;border-top:1px solid #eee;">{$h_telefono}</td>
          </tr>
          <tr style="background:#f5f5f5;">
            <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#555;border-top:1px solid #eee;">Correo</td>
            <td style="padding:10px 16px;font-size:14px;color:#222;border-top:1px solid #eee;">{$h_email}</td>
          </tr>
        </table>
        <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
          <tr><td style="border-radius:6px;background:#1a237e;">
            <a href="{$h_btn_url}"
               style="display:inline-block;padding:14px 40px;color:#fff;font-size:15px;
                      font-weight:700;text-decoration:none;border-radius:6px;letter-spacing:0.3px;">
              ➕ Agregar Usuario
            </a>
          </td></tr>
        </table>
        <p style="margin:20px 0 0;font-size:12px;color:#aaa;text-align:center;">
          Este correo fue generado automáticamente desde la plataforma Valle Verde.
        </p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;

    try {
      ob_clean();
      (new \VV\Mail\Mailer())->send(
        'comiteasesorvalleverde@gmail.com',
        'NEW USER REQUEST — Valle Verde',
        $html_request,
        'Comité Asesor'
      );
      echo json_encode(['status' => 'ok']);
    } catch (\Throwable $e) {
      echo json_encode(['status' => 'error', 'msg' => 'Error al notificar al comité.']);
    }
    exit;


default: break;

}


?>