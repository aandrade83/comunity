<?php
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
exit;
/**
 * enviar_correo.php — Script de envío personalizado (blast único / debug).
 *
 * Editar las secciones marcadas con ──► para configurar cada envío.
 */

if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'lab.lacallecr.com';
    define('BASE_URL', $scheme . '://' . $host . '/VV');
    unset($scheme, $host);
}

require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/unsuscribe_block.php';

// ──► QUERY — ajusta el WHERE según el grupo a notificar ──────────────────────
function get_destinatarios(): array
{
    db_connect('master');
    $sql = 'SELECT * FROM condominos c WHERE c.email in (select correo from usuarios u where u.rol = 2)';
    $rows = get_str($sql, false);

    $data = [];
    foreach ($rows as $p) {
        $prefix = explode('@', $p['email'])[0];
        $data[] = [
            'id'     => (int)$p['id'],
            'name'   => trim($p['nombre'] . ' ' . $p['apellido']),
            'email'  => $p['email'],       // email directo de DB → token siempre válido
            'filal'  => $p['filial'],
            'pass'   => $prefix,
        ];
    }
    return $data;
}

$data = get_destinatarios();

// ──► ASUNTO ───────────────────────────────────────────────────────────────────
$asunto = 'Invitación a la Plataforma Comunitaria — Condominio Valle Verde';

// ─── Contenido personalizado por destinatario ─────────────────────────────────
function construir_contenido(array $dest): string
{
    $filial = htmlspecialchars($dest['filal'], ENT_QUOTES, 'UTF-8');
    $pass   = htmlspecialchars($dest['pass'],  ENT_QUOTES, 'UTF-8');

    return <<<HTML
     
      <p style="margin:0 0 14px; font-size:15px; color:#555555; line-height:1.7;">
        Reciba un cordial saludo de parte del <strong>Comité Asesor</strong>.
      </p>

      <p style="margin:0 0 14px; font-size:15px; color:#555555; line-height:1.7;">
        Queremos invitarle a utilizar la <strong>plataforma comunitaria</strong> del condominio,
        un espacio creado para mantenernos mejor informados, compartir ideas y participar
        activamente en los temas importantes de nuestra comunidad.
      </p>

      <!-- Título lista -->
      <p style="margin:0 0 10px; font-size:15px; color:#444444; font-weight:600;">
        En la plataforma podrán:
      </p>

      <!-- Bullets -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
        <tr>
          <td style="padding:4px 0 4px 6px; font-size:14px; color:#555555; line-height:1.6;">
            <span style="color:#2e7d32; font-weight:700; margin-right:8px;">•</span>
            Ver y discutir <strong>temas relevantes</strong> del día a día del condominio.
          </td>
        </tr>
        <tr>
          <td style="padding:4px 0 4px 6px; font-size:14px; color:#555555; line-height:1.6;">
            <span style="color:#2e7d32; font-weight:700; margin-right:8px;">•</span>
            Informarse sobre situaciones, mejoras y propuestas para Valle Verde.
          </td>
        </tr>
        <tr>
          <td style="padding:4px 0 4px 6px; font-size:14px; color:#555555; line-height:1.6;">
            <span style="color:#2e7d32; font-weight:700; margin-right:8px;">•</span>
            Participar en <strong>actividades y encuestas</strong>.
          </td>
        </tr>
        <tr>
          <td style="padding:4px 0 4px 6px; font-size:14px; color:#555555; line-height:1.6;">
            <span style="color:#2e7d32; font-weight:700; margin-right:8px;">•</span>
            Utilizar la sección de <strong>Servicios</strong> para ofrecer o buscar proveedores
            recomendados de la zona: jardinería, mecánicos, limpieza, mantenimiento y más.
          </td>
        </tr>
      </table>

      <!-- Nota asamblea -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
        <tr>
          <td style="background:#fff8e1; border-left:4px solid #f9a825; border-radius:4px;
                     padding:14px 18px;">
            <p style="margin:0 0 6px; font-size:13px; color:#e65100; font-weight:700;">
              ⚠️ Próximamente: Asamblea General
            </p>
            <p style="margin:0; font-size:14px; color:#5d4037; line-height:1.6;">
              Actualmente ya somos cerca de <strong>80 propietarios</strong>, lo que significa
              que la participación de cada vecino y cada voto realmente cuentan para el futuro
              del condominio. La mejor forma de generar cambios positivos es
              <strong>involucrándonos e informándonos</strong>.
            </p>
          </td>
        </tr>
      </table>

      <!-- Credenciales -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
        <tr>
          <td style="background:#f0f7f0; border:1px solid #a5d6a7; border-radius:6px;
                     padding:18px 22px;">
            <p style="margin:0 0 12px; font-size:14px; color:#2e7d32; font-weight:700;
                      text-transform:uppercase; letter-spacing:0.5px;">
              🔑 Datos de acceso a la plataforma
            </p>
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-size:14px; color:#555555; padding-bottom:6px; padding-right:16px;">
                  <strong>Usuario:</strong>
                </td>
                <td style="font-size:14px; color:#222222; font-weight:700; padding-bottom:6px;
                            font-family:monospace; background:#ffffff; border:1px solid #c8e6c9;
                            border-radius:3px; padding:3px 10px;">
                  {$filial}
                </td>
              </tr>
              <tr>
                <td style="font-size:14px; color:#555555; padding-right:16px;">
                  <strong>Contraseña:</strong>
                </td>
                <td style="font-size:14px; color:#222222; font-weight:700;
                            font-family:monospace; background:#ffffff; border:1px solid #c8e6c9;
                            border-radius:3px; padding:3px 10px;">
                  {$pass}
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <p style="margin:0 0 14px; font-size:15px; color:#555555; line-height:1.7;">
        Los invitamos cordialmente a ingresar, explorar la plataforma y
        <strong>formar parte activa de la comunidad</strong>.
      </p>

      <!-- Contacto -->
      <p style="margin:0 0 20px; font-size:14px; color:#777777; line-height:1.6;">
        Si tienen alguna duda o problema para ingresar, no duden en contactarnos directamente
        con la administración del condominio.
      </p>

      <!-- Firma -->
      <p style="margin:0; font-size:14px; color:#444444; line-height:1.7;">
        Saludos cordiales,<br>
        <strong>Comité Asesor</strong><br>
        <span style="color:#888888;">Condominio Valle Verde</span>
      </p>
HTML;
}

// ─── Plantilla HTML ───────────────────────────────────────────────────────────
function plantilla_blast(array $dest, string $contenido): string
{
    $id     = (int)($dest['id'] ?? 0);
    $nombre = htmlspecialchars($dest['name'] ?? 'Estimado/a', ENT_QUOTES, 'UTF-8');
    $email  = $dest['email'] ?? '';

    $logo_url     = 'https://lab.lacallecr.com/VV/apps/ui/images/Logo_VV.png';
    $login_url    = 'https://lab.lacallecr.com/VV/';
    $color_header = '#2e7d32';

    // Email viene directo de DB → token siempre coincide con unsuscribe.php
    $unsub = plantilla_unsub_block($id, $email);

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Condominio Valle Verde</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI', Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:40px 0;">
    <tr>
      <td align="center">

        <table width="600" cellpadding="0" cellspacing="0"
               style="background:#ffffff; border-radius:8px;
                      box-shadow:0 2px 8px rgba(0,0,0,0.08); overflow:hidden;
                      max-width:600px; width:100%;">

          <!-- Header -->
          <tr>
            <td style="background:{$color_header}; padding:28px 40px; text-align:center;">
              <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:600;
                         letter-spacing:0.3px;">
                Condominio Valle Verde
              </h1>
            </td>
          </tr>

          <!-- Saludo personal -->
          <tr>
            <td style="padding:28px 40px 0;">
              <p style="margin:0; font-size:15px; color:#333333;">
                Hola, <strong>{$nombre}</strong> 👋
              </p>
            </td>
          </tr>

          <!-- Cuerpo -->
          <tr>
            <td style="padding:20px 40px 28px;">
              {$contenido}
            </td>
          </tr>

          <!-- Botón CTA -->
          <tr>
            <td style="padding:0 40px 32px; text-align:center;">
              <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                  <td style="border-radius:5px; background:{$color_header};">
                    <a href="{$login_url}"
                       style="display:inline-block; padding:13px 36px;
                              color:#ffffff; font-size:15px; font-weight:600;
                              text-decoration:none; border-radius:5px;
                              font-family:'Segoe UI',Arial,sans-serif;">
                      Entrar al sistema →
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding:0 40px;">
              <hr style="border:none; border-top:1px solid #eeeeee; margin:0;">
            </td>
          </tr>

          {$unsub}

          <!-- Footer -->
          <tr>
            <td style="padding:20px 40px 28px; text-align:center; background:#fafafa;">
              <img src="{$logo_url}" alt="Valle Verde" width="110"
                   style="display:inline-block; max-width:110px; height:auto; margin-bottom:10px;">
              <p style="margin:6px 0 0; font-size:11px; color:#bbbbbb;">
                © 2025 Condominio Valle Verde. Todos los derechos reservados.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
HTML;
}

// ─── Envío ────────────────────────────────────────────────────────────────────
$mailer   = new \VV\Mail\Mailer();
$enviados = 0;
$errores  = [];

foreach ($data as $dest) {
    if (empty($dest['email'])) continue;

    try {
        $contenido = construir_contenido($dest);
        $html      = plantilla_blast($dest, $contenido);
        $mailer->send($dest['email'], $asunto, $html, $dest['name'] ?? '');
        $enviados++;
        echo "OK  → {$dest['email']}\n";
    } catch (\Throwable $e) {
        $errores[] = $dest['email'];
        echo "ERR → {$dest['email']}: " . $e->getMessage() . "\n";
    }
}

echo "\n--- Resultado ---\n";
echo "Enviados : {$enviados}\n";
echo "Errores  : " . count($errores) . "\n";
if ($errores) {
    echo "Fallidos : " . implode(', ', $errores) . "\n";
}
