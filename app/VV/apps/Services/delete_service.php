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

$service_id = (int)($_POST['id'] ?? 0);
if (!$service_id) json_exit(['ok' => false, 'error' => 'ID inválido']);

db_connect('master');
$db = $GLOBALS['conn_db']->mysqli_connector;

$st = $db->prepare('SELECT id FROM servicios WHERE id = ?');
$st->bind_param('i', $service_id);
$st->execute();
$st->store_result();
if ($st->num_rows === 0) json_exit(['ok' => false, 'error' => 'Servicio no encontrado'], 404);
$st->close();

$upload_base = $_SERVER['DOCUMENT_ROOT'] . '/VV/apps/Services/uploads/adjuntos/';

// ── 1. Borrar archivos físicos de adjuntos de respuestas ──────────────────
$st = $db->prepare(
    'SELECT a.nombre_archivo FROM adjuntos_servicios a
     INNER JOIN servicios_respuestas r ON r.id = a.entidad_id
     WHERE a.tipo_entidad = "respuesta" AND r.id_tema = ?'
);
$st->bind_param('i', $service_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $path = $upload_base . $row['nombre_archivo'];
    if (file_exists($path)) @unlink($path);
}
$st->close();

// ── 2. Eliminar registros de adjuntos de respuestas ───────────────────────
$st = $db->prepare(
    'DELETE a FROM adjuntos_servicios a
     INNER JOIN servicios_respuestas r ON r.id = a.entidad_id
     WHERE a.tipo_entidad = "respuesta" AND r.id_tema = ?'
);
$st->bind_param('i', $service_id);
$st->execute();
$st->close();

// ── 3. Borrar archivos físicos de adjuntos del servicio ───────────────────
$st = $db->prepare('SELECT nombre_archivo FROM adjuntos_servicios WHERE tipo_entidad = "tema" AND entidad_id = ?');
$st->bind_param('i', $service_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $path = $upload_base . $row['nombre_archivo'];
    if (file_exists($path)) @unlink($path);
}
$st->close();

// ── 4. Eliminar registros de adjuntos del servicio ────────────────────────
$st = $db->prepare('DELETE FROM adjuntos_servicios WHERE tipo_entidad = "tema" AND entidad_id = ?');
$st->bind_param('i', $service_id);
$st->execute();
$st->close();

// ── 5. Likes de respuestas ────────────────────────────────────────────────
$st = $db->prepare(
    'DELETE rl FROM respuestas_servicios_likes rl
     INNER JOIN servicios_respuestas r ON r.id = rl.id_respuesta
     WHERE r.id_tema = ?'
);
$st->bind_param('i', $service_id);
$st->execute();
$st->close();

// ── 6. Likes del servicio ─────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM servicios_likes WHERE id_tema = ?');
$st->bind_param('i', $service_id);
$st->execute();
$st->close();

// ── 7. Respuestas ─────────────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM servicios_respuestas WHERE id_tema = ?');
$st->bind_param('i', $service_id);
$st->execute();
$st->close();

// ── 8. El servicio ────────────────────────────────────────────────────────
$st = $db->prepare('DELETE FROM servicios WHERE id = ?');
$st->bind_param('i', $service_id);
$st->execute();
$st->close();

json_exit(['ok' => true]);
