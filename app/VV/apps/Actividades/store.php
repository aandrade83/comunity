<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Actividades/db.php';

if (empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/apps/Actividades/index.php');
    exit;
}
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 2) {
    header('Location: ' . BASE_URL . '/apps/Actividades/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/apps/Actividades/create.php');
    exit;
}

$titulo      = trim($_POST['titulo']      ?? '');
$fecha       = trim($_POST['fecha']       ?? '');
$hora        = trim($_POST['hora']        ?? '');
$lugar       = trim($_POST['lugar']       ?? '');
$colaboracion = trim($_POST['colaboracion'] ?? '');
$detalle     = trim($_POST['detalle']     ?? '');
$id_user     = (int)($_SESSION['user']   ?? 0);

// Validate
if ($titulo === '' || $fecha === '' || $hora === '') {
    header('Location: create.php?err=' . urlencode('Título, fecha y hora son obligatorios'));
    exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Location: create.php?err=' . urlencode('Fecha inválida'));
    exit;
}
if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
    header('Location: create.php?err=' . urlencode('Hora inválida'));
    exit;
}

$act_id = act_exec(
    'INSERT INTO actividades (titulo, fecha, hora, lugar, colaboracion, detalle, creado_por) VALUES (?,?,?,?,?,?,?)',
    'ssssssi', $titulo, $fecha, $hora, $lugar, $colaboracion, $detalle, $id_user
);

if (!$act_id) {
    header('Location: create.php?err=' . urlencode('Error al crear la actividad'));
    exit;
}

act_process_uploads($act_id, 'actividad');

header('Location: view.php?id=' . $act_id);
exit;
