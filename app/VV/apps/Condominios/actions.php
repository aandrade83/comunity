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

if (empty($_SESSION['user']))                                   json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] < 3)     json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST')                      json_exit(['ok' => false, 'error' => 'Método inválido'], 405);

$id         = (int)($_POST['id']         ?? 0);
$nombre     = trim($_POST['nombre']      ?? '');
$apellido   = trim($_POST['apellido']    ?? '');
$email      = trim($_POST['email']       ?? '');
$telefono   = trim($_POST['telefono']    ?? '');
$rol        = trim($_POST['rol']         ?? '');
$filial     = (int)($_POST['filial']     ?? 0);
$email_flag = (int)($_POST['email_flag'] ?? 0);

if (!$id)      json_exit(['ok' => false, 'error' => 'ID inválido']);
if (!$nombre)  json_exit(['ok' => false, 'error' => 'Nombre requerido']);
if (!$apellido) json_exit(['ok' => false, 'error' => 'Apellido requerido']);
if (!$email)   json_exit(['ok' => false, 'error' => 'Email requerido']);
if (!in_array($rol, ['Dueño', 'Inquilino'])) json_exit(['ok' => false, 'error' => 'Rol inválido']);

db_connect('master');
$db = $GLOBALS['conn_db']->mysqli_connector;

$st = $db->prepare(
    'UPDATE condominos SET nombre=?, apellido=?, email=?, telefono=?, rol=?, filial=?, email_flag=?
     WHERE id=?'
);
$st->bind_param('sssssiii', $nombre, $apellido, $email, $telefono, $rol, $filial, $email_flag, $id);
$st->execute();
if ($st->affected_rows < 0) {
    $st->close();
    json_exit(['ok' => false, 'error' => 'Error al actualizar']);
}
$st->close();

json_exit(['ok' => true]);
