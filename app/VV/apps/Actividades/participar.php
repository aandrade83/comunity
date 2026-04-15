<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Actividades/db.php';

function json_exit(array $data, int $code = 200): void {
    if (ob_get_length()) ob_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user']))  json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_exit(['ok' => false, 'error' => 'Método inválido'], 405);

$act_id  = (int)($_POST['actividad_id'] ?? 0);
$estado  = $_POST['estado'] ?? '';
$user_id = (int)($_SESSION['user']   ?? 0);
$filial  = (int)($_SESSION['filial'] ?? 0);

if (!$act_id)                           json_exit(['ok' => false, 'error' => 'Actividad inválida']);
if (!in_array($estado, ['si', 'no']))   json_exit(['ok' => false, 'error' => 'Estado inválido']);
if (!$user_id)                          json_exit(['ok' => false, 'error' => 'Sesión inválida']);

// Verify activity exists
$act = act_row('SELECT id, fecha FROM actividades WHERE id = ?', 'i', $act_id);
if (!$act) json_exit(['ok' => false, 'error' => 'Actividad no encontrada']);

// Block if today or past
if ($act['fecha'] <= date('Y-m-d')) {
    json_exit(['ok' => false, 'error' => 'Solo puede registrar participación en eventos futuros']);
}

$now = date('Y-m-d H:i:s');
$existing = act_get_participante($act_id, $user_id);

if ($existing) {
    act_exec(
        'UPDATE actividad_participantes SET estado = ?, updated_at = ? WHERE actividad_id = ? AND user_id = ?',
        'ssii', $estado, $now, $act_id, $user_id
    );
} else {
    act_exec(
        'INSERT INTO actividad_participantes (actividad_id, user_id, filial_id, estado, updated_at) VALUES (?,?,?,?,?)',
        'iiiss', $act_id, $user_id, $filial, $estado, $now
    );
}

json_exit(['ok' => true, 'estado' => $estado]);
