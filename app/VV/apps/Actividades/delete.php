<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Actividades/db.php';

function json_exit(array $data, int $code = 200): void {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user']))                                   json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] < 2)   json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST')                      json_exit(['ok' => false, 'error' => 'Método inválido'], 405);

$act_id = (int)($_POST['id'] ?? 0);
if (!$act_id) json_exit(['ok' => false, 'error' => 'ID inválido']);

$act = act_row('SELECT id FROM actividades WHERE id = ?', 'i', $act_id);
if (!$act) json_exit(['ok' => false, 'error' => 'Actividad no encontrada']);

// Delete physical files
$dir      = act_upload_dir();
$adjuntos = act_adjuntos($act_id);
foreach ($adjuntos as $adj) {
    @unlink($dir . $adj['archivo']);
}

// Delete adjuntos de comentarios
$comentarios = act_rows('SELECT id FROM actividad_comentarios WHERE actividad_id = ?', 'i', $act_id);
foreach ($comentarios as $c) {
    $cadj = act_rows('SELECT archivo FROM actividad_adjuntos WHERE entidad = "comment" AND entidad_id = ?', 'i', (int)$c['id']);
    foreach ($cadj as $ca) @unlink($dir . $ca['archivo']);
    act_exec('DELETE FROM actividad_adjuntos WHERE entidad = "comment" AND entidad_id = ?', 'i', (int)$c['id']);
}
act_exec('DELETE FROM actividad_comentarios   WHERE actividad_id = ?', 'i', $act_id);
act_exec('DELETE FROM actividad_adjuntos      WHERE entidad = "actividad" AND entidad_id = ?', 'i', $act_id);
act_exec('DELETE FROM actividad_participantes WHERE actividad_id = ?',  'i', $act_id);
act_exec('DELETE FROM actividades             WHERE id = ?',             'i', $act_id);

json_exit(['ok' => true]);
