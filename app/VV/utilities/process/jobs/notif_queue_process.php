<?php
/**
 * notif_queue_process.php — Cron job para procesar cola de notificaciones masivas.
 *
 * Correr cada 15 minutos:
 *   * /15 * * * * /usr/bin/php /path/to/VV/utilities/process/jobs/notif_queue_process.php >> /tmp/vv_notif_cron.log 2>&1
 *
 * Solo ejecutable por CLI.
 */

// Acepta CLI (cron) o HTTP con token de seguridad
if (php_sapi_name() !== 'cli') {
    define('VV_JOB_TOKEN', 'vv_notif_2024');
    $token = $_GET['token'] ?? $_SERVER['HTTP_X_JOB_TOKEN'] ?? '';
    if ($token !== VV_JOB_TOKEN) {
        http_response_code(403);
        echo 'Acceso denegado';
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
}

ignore_user_abort(true);
set_time_limit(0);

// ROOT_PATH = VV/  (jobs/ → process/ → utilities/ → VV/)
define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));

require_once ROOT_PATH . '/config.php';
require_once ROOT_PATH . '/utilities/functions.php';
require_once ROOT_PATH . '/utilities/db/handler.php';
require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/notificacion.php';

try {
    db_connect('master');
    $db = $GLOBALS['conn_db']->mysqli_connector;

    // Obtener jobs pendientes
    $res = $db->query('SELECT * FROM notif_queue WHERE email_sent = 0 ORDER BY created_at ASC');
    if (!$res) exit(0);

    $pending = [];
    while ($row = $res->fetch_assoc()) $pending[] = $row;

    if (empty($pending)) {
        echo date('Y-m-d H:i:s') . " — Sin pendientes.\n";
        exit(0);
    }

    echo date('Y-m-d H:i:s') . " — Procesando " . count($pending) . " job(s).\n";

    // Obtener destinatarios una sola vez para todos los jobs
    $st = $db->prepare('SELECT id, nombre, apellido, email FROM condominos WHERE email_flag = 1');
    $st->execute();
    $r  = $st->get_result();
    $destinatarios = [];
    while ($row = $r->fetch_assoc()) $destinatarios[] = $row;
    $st->close();

    $total = count($destinatarios);
    echo date('Y-m-d H:i:s') . " — Destinatarios: {$total}\n";

    $asuntos = [
        'Tema'      => 'Nuevo Tema en Valle Verde',
        'Actividad' => 'Nueva Actividad en Valle Verde',
        'Encuesta'  => 'Nueva Encuesta en Valle Verde',
    ];

    foreach ($pending as $job) {
        $jobId  = (int)$job['id'];
        $tipo   = $job['tipo'];
        $asunto = $asuntos[$tipo] ?? 'Nueva notificación de Valle Verde';

        $enviados = 0;
        $errores  = 0;

        if (!empty($destinatarios)) {
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
                        'titulo' => $job['titulo'],
                    ]),
                ];
            }

            try {
                $result   = (new \VV\Mail\Mailer())->sendBatch($asunto, $recipients);
                $enviados = (int)($result['sent']   ?? 0);
                $errores  = count($result['errors'] ?? []);
            } catch (\Throwable $e) {
                $errores = $total;
            }
        }

        $db->query(sprintf(
            "UPDATE notif_queue SET email_sent=1, total=%d, enviados=%d, errores=%d, sent_at=NOW() WHERE id=%d",
            $total, $enviados, $errores, $jobId
        ));

        echo date('Y-m-d H:i:s') . " — Job #{$jobId} [{$tipo}] → enviados={$enviados} errores={$errores}\n";
    }

} catch (\Throwable $e) {
    echo date('Y-m-d H:i:s') . " — ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo date('Y-m-d H:i:s') . " — Listo.\n";
