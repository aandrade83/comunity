<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
session_start();

function json_exit(array $data, int $code = 200): void {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user']))                                   json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] < 3)     json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST')                      json_exit(['ok' => false, 'error' => 'Método inválido'], 405);

$reply_id = (int)($_POST['id'] ?? 0);
if (!$reply_id) json_exit(['ok' => false, 'error' => 'ID inválido']);

db_connect('master');
$db = $GLOBALS['conn_db']->mysqli_connector;

$st = $db->prepare('SELECT id FROM respuestas WHERE id = ?');
$st->bind_param('i', $reply_id);
$st->execute();
$st->store_result();
if ($st->num_rows === 0) json_exit(['ok' => false, 'error' => 'Respuesta no encontrada'], 404);
$st->close();

$upload_base = $_SERVER['DOCUMENT_ROOT'] . '/VV/apps/Forum/uploads/adjuntos/';

// ── 1. Borrar archivos físicos de adjuntos de la respuesta ────────────────
$st = $db->prepare('SELECT nombre_archivo FROM adjuntos WHERE tipo_entidad = "respuesta" AND entidad_id = ?');
$st->bind_param('i', $reply_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $path = $upload_base . $row['nombre_archivo'];
    if (file_exists($path)) @unlink($path);
}
$st->close();

// ── 2. Eliminar registros de adjuntos ─────────────────────────────────────
$st = $db->prepare('DELETE FROM adjuntos WHERE tipo_entidad = "respuesta" AND entidad_id = ?');
$st->bind_param('i', $reply_id);
$st->execute();
$st->close();

// ── 3. Eliminar likes de la respuesta ─────────────────────────────────────
$st = $db->prepare('DELETE FROM respuestas_likes WHERE id_respuesta = ?');
$st->bind_param('i', $reply_id);
$st->execute();
$st->close();

// ── 4. Eliminar la respuesta ──────────────────────────────────────────────
$st = $db->prepare('DELETE FROM respuestas WHERE id = ?');
$st->bind_param('i', $reply_id);
$st->execute();
$st->close();

json_exit(['ok' => true]);
