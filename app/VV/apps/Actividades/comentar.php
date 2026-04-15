<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Actividades/db.php';

ob_start();

function json_exit(array $data, int $code = 200): void {
    if (ob_get_length()) ob_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user']))              json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_exit(['ok' => false, 'error' => 'Método inválido'], 405);

$act_id  = (int)($_POST['actividad_id'] ?? 0);
$detalle = trim($_POST['detalle']       ?? '');
$user_id = (int)($_SESSION['user']      ?? 0);
$filial  = (int)($_SESSION['filial']    ?? 0);

if (!$act_id)        json_exit(['ok' => false, 'error' => 'Actividad inválida']);
if ($detalle === '') json_exit(['ok' => false, 'error' => 'El comentario no puede estar vacío']);
if (!$user_id)       json_exit(['ok' => false, 'error' => 'Sesión inválida']);

$act = act_row('SELECT id FROM actividades WHERE id = ?', 'i', $act_id);
if (!$act) json_exit(['ok' => false, 'error' => 'Actividad no encontrada']);

$now = date('Y-m-d H:i:s');
$comment_id = act_exec(
    'INSERT INTO actividad_comentarios (actividad_id, user_id, filial_id, detalle, created_at) VALUES (?,?,?,?,?)',
    'iiiss', $act_id, $user_id, $filial, $detalle, $now
);

if (!$comment_id) json_exit(['ok' => false, 'error' => 'Error al guardar el comentario']);

act_process_uploads($comment_id, 'comment');

json_exit(['ok' => true, 'comment_id' => $comment_id]);
