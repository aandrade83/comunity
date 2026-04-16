<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
session_start();

function json_exit(array $data, int $code = 200): void {
    if (ob_get_length()) ob_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user']))                                    json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] < 3)      json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST')                       json_exit(['ok' => false, 'error' => 'Método inválido'], 405);

$topic_id = (int)($_POST['id'] ?? 0);
if (!$topic_id) json_exit(['ok' => false, 'error' => 'ID inválido']);

db_connect('master');
$db = $GLOBALS['conn_db']->mysqli_connector;

// ── 1. Verificar que el tema existe ───────────────────────────────────────
$st = $db->prepare('SELECT id FROM temas WHERE id = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$st->store_result();
if ($st->num_rows === 0) json_exit(['ok' => false, 'error' => 'Tema no encontrado'], 404);
$st->close();

$upload_base = $_SERVER['DOCUMENT_ROOT'] . '/VV/apps/Forum/uploads/adjuntos/';

// ── 2. Borrar archivos físicos + registros de adjuntos de respuestas ──────
$st = $db->prepare(
    'SELECT a.nombre_archivo FROM adjuntos a
     INNER JOIN respuestas r ON r.id = a.entidad_id
     WHERE a.tipo_entidad = "respuesta" AND r.id_tema = ?'
);
$st->bind_param('i', $topic_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $path = $upload_base . $row['nombre_archivo'];
    if (file_exists($path)) @unlink($path);
}
$st->close();

$st = $db->prepare(
    'DELETE a FROM adjuntos a
     INNER JOIN respuestas r ON r.id = a.entidad_id
     WHERE a.tipo_entidad = "respuesta" AND r.id_tema = ?'
);
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

// ── 3. Borrar archivos físicos + registros de adjuntos del tema ───────────
$st = $db->prepare('SELECT nombre_archivo FROM adjuntos WHERE tipo_entidad = "tema" AND entidad_id = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $path = $upload_base . $row['nombre_archivo'];
    if (file_exists($path)) @unlink($path);
}
$st->close();

$st = $db->prepare('DELETE FROM adjuntos WHERE tipo_entidad = "tema" AND entidad_id = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

// ── 4. Likes de respuestas ────────────────────────────────────────────────
$st = $db->prepare(
    'DELETE rl FROM respuestas_likes rl
     INNER JOIN respuestas r ON r.id = rl.id_respuesta
     WHERE r.id_tema = ?'
);
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

// ── 5. Likes del tema ─────────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM temas_likes WHERE id_tema = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

// ── 6. Vistas del tema ────────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM temas_vistos WHERE id_tema = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

// ── 7. Respuestas ─────────────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM respuestas WHERE id_tema = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

// ── 8. El tema ────────────────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM temas WHERE id = ?');
$st->bind_param('i', $topic_id);
$st->execute();
$st->close();

json_exit(['ok' => true]);
