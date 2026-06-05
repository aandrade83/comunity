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
 * El envío ocurre en un proceso PHP CLI separado (exec) para no bloquear
 * el hilo HTTP durante el SMTP masivo. Fallback a fastcgi_finish_request
 * si exec no está disponible.
 */

require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/notificacion.php';

function vv_notificar(string $tipo, string $titulo): void
{
    $script  = __DIR__ . '/notificaciones_worker.php';
    $jobFile = sys_get_temp_dir() . '/vv_notif_' . bin2hex(random_bytes(8)) . '.json';

    // ── Camino principal: proceso CLI en background ───────────────────────────
    // Funciona con mod_php y PHP-FPM. El proceso hijo hereda las env vars de
    // Apache (DB_HOST, DB_USER, etc.) y envía los correos sin bloquear la respuesta HTTP.
    if (
        @file_put_contents($jobFile, json_encode(['tipo' => $tipo, 'titulo' => $titulo])) !== false
        && function_exists('exec')
        && is_file($script)
    ) {
        $cmd = PHP_BINARY . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($jobFile)
             . ' > /dev/null 2>&1 &';
        exec($cmd);
        return;
    }

    // Limpiar si el archivo quedó huérfano
    @unlink($jobFile);

    // ── Fallback: shutdown function + fastcgi_finish_request (PHP-FPM) ────────
    register_shutdown_function(function() use ($tipo, $titulo) {
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
            (new \VV\Mail\Mailer())->sendBatch($asunto, $recipients);
        } catch (\Throwable $e) {}
    });
}
