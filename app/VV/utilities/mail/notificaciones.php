<?php
/**
 * notificaciones.php — Helper centralizado para envío de notificaciones masivas.
 *
 * Uso:
 *   require_once ROOT_PATH . '/utilities/mail/notificaciones.php';
 *   vv_notificar('Tema', 'Título del tema');
 *   vv_notificar('Actividad', 'Nombre de la actividad');
 *   vv_notificar('Encuesta', 'Nombre de la encuesta');
 *
 * Solo envía a condominos con email_flag = 1.
 * El envío ocurre después de que la respuesta HTTP es enviada al cliente
 * (via register_shutdown_function + fastcgi_finish_request) para no bloquear
 * el hilo HTTP durante el SMTP masivo.
 */

require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/notificacion.php';

function vv_notificar(string $tipo, string $titulo): void
{
    register_shutdown_function(function() use ($tipo, $titulo) {
        // Flush la respuesta HTTP al cliente antes de iniciar el SMTP
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        ignore_user_abort(true);
        set_time_limit(0);

        try {
            db_connect('master');
            $db = $GLOBALS['conn_db']->mysqli_connector;

            $st = $db->prepare(
                'SELECT id, nombre, apellido, email FROM condominos WHERE email_flag = 1'
            );
            $st->execute();
            $res = $st->get_result();
            $destinatarios = [];
            while ($row = $res->fetch_assoc()) {
                $destinatarios[] = $row;
            }
            $st->close();

        } catch (\Throwable $e) {
            return;
        }

        if (empty($destinatarios)) return;

        $asuntos = [
            'Tema'      => 'Nuevo Tema en Valle Verde',
            'Actividad' => 'Nueva Actividad en Valle Verde',
            'Encuesta'  => 'Nueva Encuesta en Valle Verde',
        ];
        $asunto = $asuntos[$tipo] ?? 'Nueva notificación de Valle Verde';

        $mailer     = new \VV\Mail\Mailer();
        $recipients = [];

        foreach ($destinatarios as $c) {
            $recipients[] = [
                'email' => $c['email'],
                'name'  => trim($c['nombre'] . ' ' . ($c['apellido'] ?? '')),
                'html'  => plantilla_notificacion([
                    'id'     => (int)$c['id'],
                    'nombre' => $c['nombre'],
                    'email'  => $c['email'],
                    'tipo'   => $tipo,
                    'titulo' => $titulo,
                ]),
            ];
        }

        try {
            $mailer->sendBatch($asunto, $recipients);
        } catch (\Throwable $e) {
            // sin respuesta HTTP abierta — no hay nada que reportar
        }
    });
}
