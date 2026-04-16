<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Actividades/db.php';

if (empty($_SESSION['user'])) { header('Location: ' . BASE_URL . '/apps/Actividades/index.php'); exit; }
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] < 2) { header('Location: ' . BASE_URL . '/apps/Actividades/index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/apps/Actividades/index.php'); exit; }

$act_id      = (int)($_POST['id']           ?? 0);
$titulo      = trim($_POST['titulo']        ?? '');
$fecha       = trim($_POST['fecha']         ?? '');
$hora        = trim($_POST['hora']          ?? '');
$lugar       = trim($_POST['lugar']         ?? '');
$colaboracion = trim($_POST['colaboracion'] ?? '');
$detalle     = trim($_POST['detalle']       ?? '');

if (!$act_id) { header('Location: ' . BASE_URL . '/apps/Actividades/index.php'); exit; }

$act = act_row('SELECT id FROM actividades WHERE id = ?', 'i', $act_id);
if (!$act) { header('Location: ' . BASE_URL . '/apps/Actividades/index.php'); exit; }

if ($titulo === '' || $fecha === '' || $hora === '') {
    header('Location: edit.php?id=' . $act_id . '&err=' . urlencode('Título, fecha y hora son obligatorios'));
    exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Location: edit.php?id=' . $act_id . '&err=' . urlencode('Fecha inválida'));
    exit;
}

// Delete marked adjuntos
if (!empty($_POST['del_adj']) && is_array($_POST['del_adj'])) {
    $dir = act_upload_dir();
    foreach ($_POST['del_adj'] as $adj_id) {
        $adj_id = (int)$adj_id;
        $adj = act_row('SELECT archivo FROM actividad_adjuntos WHERE id = ? AND actividad_id = ?', 'ii', $adj_id, $act_id);
        if ($adj) {
            @unlink($dir . $adj['archivo']);
            act_exec('DELETE FROM actividad_adjuntos WHERE id = ?', 'i', $adj_id);
        }
    }
}

act_exec(
    'UPDATE actividades SET titulo=?, fecha=?, hora=?, lugar=?, colaboracion=?, detalle=? WHERE id=?',
    'ssssssi', $titulo, $fecha, $hora, $lugar, $colaboracion, $detalle, $act_id
);

act_process_uploads($act_id, 'actividad');

header('Location: view.php?id=' . $act_id);
exit;
