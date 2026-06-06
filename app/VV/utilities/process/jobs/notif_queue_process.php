<?php
/**
 * notif_queue_process.php — Cron job para procesar cola de notificaciones masivas.
 *
 * Envía en lotes de 20 por ejecución, con registro individual por destinatario.
 * No reenvía a quien ya recibió el correo (UNIQUE KEY en notif_queue_recipients).
 *
 * Cron cada 15 min:
 *   * /15 * * * * /usr/bin/php /ruta/VV/utilities/process/jobs/notif_queue_process.php >> /tmp/vv_notif_cron.log 2>&1
 *
 * Manual (HTTP):
 *   https://dominio/VV/utilities/process/jobs/notif_queue_process.php?token=vv_notif_2024
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

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';
require_once ROOT_PATH . '/utilities/functions.php';
require_once ROOT_PATH . '/utilities/db/handler.php';
require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/notificacion.php';

const BATCH_SIZE = 20;

$asuntos = [
    'Tema'      => 'Nuevo Tema en Valle Verde',
    'Actividad' => 'Nueva Actividad en Valle Verde',
    'Encuesta'  => 'Nueva Encuesta en Valle Verde',
];

function log_line(string $msg): void {
    echo date('Y-m-d H:i:s') . ' — ' . $msg . "\n";
    if (php_sapi_name() !== 'cli') ob_flush();
}

try {
    db_connect('master');
    $db = $GLOBALS['conn_db']->mysqli_connector;

    // ── Jobs pendientes ───────────────────────────────────────────────────────
    $res = $db->query('SELECT * FROM notif_queue WHERE email_sent = 0 ORDER BY created_at ASC');
    if (!$res) { log_line('Sin resultados de notif_queue'); exit(0); }

    $pending = [];
    while ($row = $res->fetch_assoc()) $pending[] = $row;

    if (empty($pending)) {
        log_line('Sin pendientes.');
        exit(0);
    }

    log_line('Jobs pendientes: ' . count($pending));

    foreach ($pending as $job) {
        $queueId = (int)$job['id'];
        $tipo    = $job['tipo'];
        $titulo  = $job['titulo'];
        $asunto  = $asuntos[$tipo] ?? 'Nueva notificación de Valle Verde';

        log_line("Job #{$queueId} [{$tipo}]: \"{$titulo}\"");

        // ── Paso 1: Poblar destinatarios la primera vez ───────────────────────
        $row = $db->query("SELECT COUNT(*) AS c FROM notif_queue_recipients WHERE queue_id = {$queueId}")->fetch_assoc();
        if ((int)$row['c'] === 0) {
            $st = $db->prepare(
                'SELECT id, nombre, apellido, email FROM condominos WHERE email_flag = 1'
            );
            $st->execute();
            $rr = $st->get_result();
            $insertados = 0;
            $ins = $db->prepare(
                'INSERT IGNORE INTO notif_queue_recipients (queue_id, condomino_id, email, nombre, sent)
                 VALUES (?, ?, ?, ?, 0)'
            );
            while ($c = $rr->fetch_assoc()) {
                $nombre = trim($c['nombre'] . ' ' . ($c['apellido'] ?? ''));
                $ins->bind_param('iiss', $queueId, $c['id'], $c['email'], $nombre);
                $ins->execute();
                $insertados++;
            }
            $st->close();
            $ins->close();
            log_line("  Destinatarios registrados: {$insertados}");
        }

        // ── Paso 2: Tomar próximos BATCH_SIZE no enviados ────────────────────
        $st = $db->prepare(
            'SELECT * FROM notif_queue_recipients WHERE queue_id = ? AND sent = 0 LIMIT ' . BATCH_SIZE
        );
        $st->bind_param('i', $queueId);
        $st->execute();
        $batch = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        if (empty($batch)) {
            // Todos enviados — marcar job completo
            $totRow = $db->query(
                "SELECT COUNT(*) AS total,
                        SUM(sent = 1) AS enviados
                 FROM notif_queue_recipients WHERE queue_id = {$queueId}"
            )->fetch_assoc();
            $db->query(sprintf(
                "UPDATE notif_queue SET email_sent=1, total=%d, enviados=%d, errores=%d, sent_at=NOW() WHERE id=%d",
                (int)$totRow['total'], (int)$totRow['enviados'],
                (int)$totRow['total'] - (int)$totRow['enviados'], $queueId
            ));
            log_line("  Job #{$queueId} completado.");
            continue;
        }

        log_line("  Enviando lote de " . count($batch) . " destinatarios...");

        // ── Paso 3: Construir recipients y enviar ─────────────────────────────
        $recipients = [];
        foreach ($batch as $r) {
            $recipients[] = [
                'email' => $r['email'],
                'name'  => $r['nombre'],
                'html'  => plantilla_notificacion([
                    'id'     => (int)$r['condomino_id'],
                    'nombre' => $r['nombre'],
                    'email'  => $r['email'],
                    'tipo'   => $tipo,
                    'titulo' => $titulo,
                ]),
            ];
        }

        $enviados = 0;
        $errores  = 0;
        try {
            $result   = (new \VV\Mail\Mailer())->sendBatch($asunto, $recipients);
            $enviados = (int)($result['sent']   ?? 0);
            $errores  = count($result['errors'] ?? []);
        } catch (\Throwable $e) {
            $errores = count($batch);
            log_line("  ERROR sendBatch: " . $e->getMessage());
        }

        // ── Paso 4: Marcar lote como enviado ─────────────────────────────────
        $ids = implode(',', array_map(fn($r) => (int)$r['id'], $batch));
        $db->query("UPDATE notif_queue_recipients SET sent=1, sent_at=NOW() WHERE id IN ({$ids})");

        log_line("  Lote: enviados={$enviados} errores={$errores}");

        // ── Paso 5: Actualizar totals en notif_queue ──────────────────────────
        $totRow = $db->query(
            "SELECT COUNT(*) AS total,
                    SUM(sent = 1) AS enviados,
                    SUM(sent = 0) AS pendientes
             FROM notif_queue_recipients WHERE queue_id = {$queueId}"
        )->fetch_assoc();

        $totalEnviados = (int)$totRow['enviados'];
        $totalTotal    = (int)$totRow['total'];
        $pendientes    = (int)$totRow['pendientes'];

        if ($pendientes === 0) {
            $db->query(sprintf(
                "UPDATE notif_queue SET email_sent=1, total=%d, enviados=%d, errores=%d, sent_at=NOW() WHERE id=%d",
                $totalTotal, $totalEnviados, $totalTotal - $totalEnviados, $queueId
            ));
            log_line("  Job #{$queueId} completado ({$totalEnviados}/{$totalTotal}).");
        } else {
            $db->query(sprintf(
                "UPDATE notif_queue SET total=%d, enviados=%d WHERE id=%d",
                $totalTotal, $totalEnviados, $queueId
            ));
            log_line("  Progreso: {$totalEnviados}/{$totalTotal} — quedan {$pendientes}.");
        }
    }

} catch (\Throwable $e) {
    log_line('ERROR FATAL: ' . $e->getMessage());
    exit(1);
}

log_line('Listo.');
