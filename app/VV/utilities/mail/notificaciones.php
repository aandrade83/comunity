<?php
/**
 * notificaciones.php — Encola notificaciones masivas en notif_queue.
 *
 * Uso:
 *   require_once ROOT_PATH . '/utilities/mail/notificaciones.php';
 *   vv_notificar('Tema', 'Título del tema', $tema_id);
 *   vv_notificar('Actividad', 'Nombre de la actividad', $actividad_id);
 *   vv_notificar('Encuesta', 'Nombre de la encuesta', $encuesta_id);
 *
 * El envío real lo realiza el cron:
 *   utilities/process/jobs/notif_queue_process.php  (cada 15 min)
 */

function vv_notificar(string $tipo, string $titulo, int $entidad_id = 0): void
{
    $log = sys_get_temp_dir() . '/vv_notif_debug.log';
    try {
        db_connect('master');
        $db  = $GLOBALS['conn_db']->mysqli_connector;
        $id  = insert([
            'tipo'       => $tipo,
            'entidad_id' => $entidad_id,
            'titulo'     => $titulo,
            'email_sent' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ], 'notif_queue');
        file_put_contents($log, date('c') . " OK insert_id={$id} tipo={$tipo}\n", FILE_APPEND);
        if ($db->error) {
            file_put_contents($log, date('c') . " DB_ERROR: " . $db->error . "\n", FILE_APPEND);
        }
    } catch (\Throwable $e) {
        file_put_contents($log, date('c') . " EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}
