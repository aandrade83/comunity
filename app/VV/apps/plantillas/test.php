<?php
/**
 * Plantilla de correo: test
 *
 * Uso:
 *   $html = plantilla_test([
 *       'id'       => 12,
 *       'nombre'   => 'Juan',
 *       'apellido' => 'Pérez',
 *       'email'    => 'juan@email.com',
 *   ]);
 *
 * @param  array $data  Datos del condomino (id, nombre, apellido, email, …)
 * @return string  HTML listo para enviar.
 */

require_once __DIR__ . '/unsuscribe_block.php';

function plantilla_test(array $data = []): string
{
    $id       = (int)($data['id']       ?? 0);
    $nombre   = htmlspecialchars($data['nombre']   ?? 'Estimado residente', ENT_QUOTES, 'UTF-8');
    $apellido = htmlspecialchars($data['apellido'] ?? '', ENT_QUOTES, 'UTF-8');
    $email    = $data['email'] ?? '';
    $saludo   = trim("$nombre $apellido");

    // Logo: URL absoluta accesible desde internet en producción.
    $logo_url = 'https://lab.lacallecr.com/VV/apps/ui/images/Logo_VV.png';

    // Bloque de baja de notificaciones (reutilizable en todas las plantillas)
    $unsub = ($id && $email) ? plantilla_unsub_block($id, $email) : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Correo de prueba — Valle Verde</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI', Arial, sans-serif;">

  <!-- Wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:40px 0;">
    <tr>
      <td align="center">

        <!-- Card -->
        <table width="600" cellpadding="0" cellspacing="0"
               style="background:#ffffff; border-radius:8px;
                      box-shadow:0 2px 8px rgba(0,0,0,0.08); overflow:hidden;
                      max-width:600px; width:100%;">

          <!-- Header verde -->
          <tr>
            <td style="background:#2e7d32; padding:30px 40px; text-align:center;">
              <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:600;
                         letter-spacing:0.5px;">
                Condominio Valle Verde
              </h1>
            </td>
          </tr>

          <!-- Cuerpo -->
          <tr>
            <td style="padding:36px 40px 28px;">

              <p style="margin:0 0 16px; font-size:16px; color:#333333; font-weight:600;">
                Hola, {$saludo} 👋
              </p>

              <p style="margin:0 0 16px; font-size:15px; color:#555555; line-height:1.6;">
                Este es un <strong>correo de prueba</strong> del sistema de comunicación
                del <strong>Condominio Valle Verde</strong>.
              </p>

              <p style="margin:0 0 24px; font-size:15px; color:#555555; line-height:1.6;">
                Si recibiste este mensaje, significa que tu dirección de correo está
                correctamente registrada en nuestra plataforma y que el sistema de
                notificaciones está funcionando correctamente.
              </p>

              <!-- Recuadro informativo -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#f0f7f0; border-left:4px solid #2e7d32;
                              border-radius:4px; padding:16px 20px;">
                    <p style="margin:0; font-size:14px; color:#2e7d32; font-weight:600;">
                      ℹ️ Información
                    </p>
                    <p style="margin:6px 0 0; font-size:13px; color:#555555;">
                      Este correo fue generado automáticamente por el sistema Valle Verde.
                      Por favor no respondas a este mensaje.
                    </p>
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

          <!-- Footer con logo -->
          <tr>
            <td style="padding:20px 40px 28px; text-align:center; background:#fafafa;">
              <img src="{$logo_url}"
                   alt="Valle Verde" width="120"
                   style="display:inline-block; max-width:120px; height:auto; margin-bottom:10px;">
              <p style="margin:8px 0 0; font-size:11px; color:#bbbbbb;">
                © 2025 Condominio Valle Verde. Todos los derechos reservados.
              </p>
            </td>
          </tr>

        </table>
        <!-- /Card -->

      </td>
    </tr>
  </table>
  <!-- /Wrapper -->

</body>
</html>
HTML;
}
