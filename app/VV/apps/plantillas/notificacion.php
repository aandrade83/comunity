<?php
/**
 * Plantilla de correo: notificacion
 *
 * Uso:
 *   $html = plantilla_notificacion([
 *       'id'     => 12,
 *       'nombre' => 'Juan',
 *       'email'  => 'juan@email.com',
 *       'tipo'   => 'Tema',          // 'Tema' | 'Actividad' | 'Encuesta'
 *       'titulo' => 'Título del elemento',
 *   ]);
 */

require_once __DIR__ . '/unsuscribe_block.php';

function plantilla_notificacion(array $data = []): string
{
    $id     = (int)($data['id']     ?? 0);
    $nombre = htmlspecialchars($data['nombre'] ?? 'Residente', ENT_QUOTES, 'UTF-8');
    $email  = $data['email'] ?? '';
    $tipo   = $data['tipo']  ?? 'Elemento';
    $titulo = htmlspecialchars($data['titulo'] ?? '', ENT_QUOTES, 'UTF-8');

    // Gramática y color según tipo
    $config = [
        'Tema'      => ['articulo' => 'un',  'genero' => 'nuevo',  'color' => '#2980b9', 'icono' => '💬'],
        'Actividad' => ['articulo' => 'una', 'genero' => 'nueva',  'color' => '#e67e22', 'icono' => '📅'],
        'Encuesta'  => ['articulo' => 'una', 'genero' => 'nueva',  'color' => '#8e44ad', 'icono' => '📋'],
    ];
    $cfg      = $config[$tipo] ?? ['articulo' => 'un', 'genero' => 'nuevo', 'color' => '#2e7d32', 'icono' => '🔔'];
    $color    = $cfg['color'];
    $icono    = $cfg['icono'];
    $articulo = $cfg['articulo'];
    $genero   = $cfg['genero'];
    $tipo_esc = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');

    $logo_url  = 'https://lab.lacallecr.com/VV/apps/ui/images/Logo_VV.png';
    $login_url = 'https://lab.lacallecr.com/VV/';

    $unsub = ($id && $email) ? plantilla_unsub_block($id, $email) : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva {$tipo_esc} — Valle Verde</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI', Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:40px 0;">
    <tr>
      <td align="center">

        <table width="600" cellpadding="0" cellspacing="0"
               style="background:#ffffff; border-radius:8px;
                      box-shadow:0 2px 8px rgba(0,0,0,0.08); overflow:hidden;
                      max-width:600px; width:100%;">

          <!-- Header con color según tipo -->
          <tr>
            <td style="background:{$color}; padding:28px 40px; text-align:center;">
              <p style="margin:0 0 6px; font-size:30px;">{$icono}</p>
              <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:600;
                         letter-spacing:0.3px;">
                Condominio Valle Verde
              </h1>
            </td>
          </tr>

          <!-- Cuerpo -->
          <tr>
            <td style="padding:36px 40px 28px;">

              <p style="margin:0 0 18px; font-size:15px; color:#333333;">
                Hola, <strong>{$nombre}</strong> 👋
              </p>

              <p style="margin:0 0 10px; font-size:15px; color:#555555; line-height:1.6;">
                Se ha creado {$articulo} <strong>{$tipo_esc}</strong> {$genero} en el sistema:
              </p>

              <!-- Recuadro con el título -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                <tr>
                  <td style="background:#f8f9fa; border-left:4px solid {$color};
                              border-radius:4px; padding:14px 18px;">
                    <p style="margin:0; font-size:13px; color:#888888; text-transform:uppercase;
                               letter-spacing:0.5px; margin-bottom:4px;">{$tipo_esc}</p>
                    <p style="margin:0; font-size:16px; color:#222222; font-weight:600;">
                      {$titulo}
                    </p>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 24px; font-size:14px; color:#777777; line-height:1.6;">
                Ingresa al sistema para revisarlo y participar.
              </p>

              <!-- Botón CTA -->
              <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                  <td style="border-radius:5px; background:{$color};">
                    <a href="{$login_url}"
                       style="display:inline-block; padding:12px 32px;
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
