<?php
/**
 * Bloque reutilizable de baja de notificaciones para plantillas de correo.
 *
 * Uso en cualquier plantilla:
 *   require_once ROOT_PATH . '/apps/plantillas/unsuscribe_block.php';
 *   $footer_unsub = plantilla_unsub_block($condomino['id'], $condomino['email']);
 *   // Insertar $footer_unsub dentro del HTML de la plantilla.
 *
 * El token generado es un hash que identifica al destinatario de forma segura
 * sin exponer datos sensibles en la URL.
 */

// Clave secreta interna — no cambiar una vez en producción.
if (!defined('VV_UNSUB_SECRET')) {
    define('VV_UNSUB_SECRET', 'VV_unsub_2024_k9x!');
}

function unsub_token(int $id, string $email): string
{
    return hash('sha256', $id . '|' . $email . '|' . VV_UNSUB_SECRET);
}

/**
 * Devuelve el bloque HTML de pie de correo con enlace de baja.
 *
 * @param int    $id    ID del condomino.
 * @param string $email Email del condomino.
 * @return string  Fragmento HTML listo para embeber en cualquier plantilla.
 */
function plantilla_unsub_block(int $id, string $email): string
{
    // BASE_URL puede no estar definido si se llama desde CLI; fallback seguro.
    $base = defined('BASE_URL') ? BASE_URL : 'http://localhost:8080/VV';

    $token = unsub_token($id, $email);
    $url   = $base . '/apps/plantillas/unsuscribe.php'
           . '?id='    . $id
           . '&token=' . $token;

    return <<<HTML

          <!-- Bloque unsubscribe — incluido en todas las plantillas -->
          <tr>
            <td style="padding:0 40px 28px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#f9f9f9; border:1px solid #e8e8e8;
                              border-radius:6px; padding:18px 22px; text-align:center;">

                    <p style="margin:0 0 6px; font-size:12px; color:#999999;">
                      Este correo fue generado automáticamente por el sistema Valle Verde.<br>
                      Por favor no respondas a este mensaje.
                    </p>

                    <a href="{$url}"
                       style="display:inline-block; margin-top:10px;
                              padding:8px 20px; border-radius:4px;
                              background:#ffffff; border:1px solid #cccccc;
                              color:#888888; font-size:12px;
                              text-decoration:none; font-family:'Segoe UI',Arial,sans-serif;">
                      Salir de Notificaciones
                    </a>

                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- /Bloque unsubscribe -->

HTML;
}
