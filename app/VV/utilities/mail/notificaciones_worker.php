<?php
/**
 * notificaciones_worker.php — Proceso CLI para envío masivo de correos.
 *
 * Ejecutado en background por vv_notificar() via exec().
 * NO debe ser accesible vía HTTP.
 *
 * Uso: php notificaciones_worker.php /tmp/vv_notif_xxxx.json
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit;
}

ignore_user_abort(true);
set_time_limit(0);

// ── Job file ──────────────────────────────────────────────────────────────────
$jobFile = $argv[1] ?? '';
if (!$jobFile || !is_file($jobFile)) exit;

$job = json_decode(file_get_contents($jobFile), true);
@unlink($jobFile);

$tipo   = $job['tipo']   ?? '';
$titulo = $job['titulo'] ?? '';
if (!$tipo || !$titulo) exit;

// ── Bootstrap mínimo (sin headers HTTP) ──────────────────────────────────────
// ROOT_PATH = directorio VV/ (2 niveles arriba desde utilities/mail/)
define('ROOT_PATH', dirname(dirname(__DIR__)));

// config.php define DB_* desde getenv() — hereda env vars de Apache vía exec()
require_once ROOT_PATH . '/config.php';
require_once ROOT_PATH . '/utilities/vars.php';
require_once ROOT_PATH . '/utilities/functions.php';
require_once ROOT_PATH . '/utilities/db/handler.php';
require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/notificacion.php';

// ── Consultar destinatarios ───────────────────────────────────────────────────
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
    exit;
}

if (empty($destinatarios)) exit;

// ── Preparar y enviar ─────────────────────────────────────────────────────────
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
} catch (\Throwable $e) {
    // silencio — proceso CLI sin salida visible
}
