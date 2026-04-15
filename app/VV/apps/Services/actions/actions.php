<?php
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
session_start();
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/vv_services_error.log');

define('DEBUG_SERVICES', false);

$action = param("ac");

function json_exit(array $data, int $httpCode = 200): void {
  if (ob_get_length()) { ob_clean(); }
  http_response_code($httpCode);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

function normalize_files_array($arr): array {
  if (!is_array($arr)) return [$arr];
  $first = reset($arr);
  if (is_array($first)) {
    $out = [];
    foreach ($arr as $chunk) {
      $vals = is_array($chunk) ? array_values($chunk) : [$chunk];
      $out[] = $vals[0] ?? null;
    }
    return $out;
  }
  return array_values($arr);
}

$uploadDir   = dirname(__DIR__) . '/uploads/adjuntos/';
$allowedMimes = [
  'image/jpeg','image/png','image/webp','image/gif',
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'text/plain'
];

function save_adjuntos_servicios(string $tipoEntidad, int $entidadId, string $uploadDir, array $allowedMimes): array {
  $filesKey = 'adjuntos';
  $saved = [];
  $hasFiles = isset($_FILES[$filesKey]) && !empty($_FILES[$filesKey]['name']);
  if (!$hasFiles) return $saved;

  $names = normalize_files_array($_FILES[$filesKey]['name']);
  $tmps  = normalize_files_array($_FILES[$filesKey]['tmp_name']);
  $errs  = normalize_files_array($_FILES[$filesKey]['error']);
  $sizes = normalize_files_array($_FILES[$filesKey]['size']);
  $count = count($names);

  if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
  if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    json_exit(['control'=>"0",'error'=>"No se puede escribir en uploads/adjuntos."], 500);
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $maxSize = 16 * 1024 * 1024;

  for ($i = 0; $i < $count; $i++) {
    $name = (string)($names[$i] ?? '');
    $tmp  = (string)($tmps[$i] ?? '');
    $err  = (int)($errs[$i] ?? 0);
    $size = (int)($sizes[$i] ?? 0);

    if ($name === '' || $tmp === '') continue;
    if ($err !== UPLOAD_ERR_OK) json_exit(['control'=>"0",'error'=>"Error subiendo archivo (code $err): $name"], 400);
    if ($size > $maxSize)        json_exit(['control'=>"0",'error'=>"Archivo muy grande: $name"], 400);

    $mime = $finfo->file($tmp);
    if (!in_array($mime, $allowedMimes, true)) {
      json_exit(['control'=>"0",'error'=>"Tipo no permitido ($mime): $name"], 400);
    }

    $ext = match ($mime) {
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp',
      'image/gif'  => 'gif',
      'application/pdf' => 'pdf',
      'application/msword' => 'doc',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
      'application/vnd.ms-excel' => 'xls',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
      'text/plain' => 'txt',
      default => 'bin',
    };

    $safeName = bin2hex(random_bytes(16)) . "." . $ext;
    $dest = $uploadDir . $safeName;

    if (!move_uploaded_file($tmp, $dest)) {
      json_exit(['control'=>"0",'error'=>"No se pudo guardar el archivo: $name"], 500);
    }

    $adj = new _Adjuntos_Servicios();
    $adj->vars['tipo_entidad']    = $tipoEntidad;
    $adj->vars['entidad_id']      = $entidadId;
    $adj->vars['nombre_original'] = $name;
    $adj->vars['nombre_archivo']  = $safeName;
    $adj->vars['extension']       = $ext;
    $adj->vars['mime_type']       = $mime;
    $adj->vars['tamano']          = $size;
    $adj->vars['orden']           = $i;
    $adj->vars['subido_por']      = $_SESSION['user'];
    $adj->vars['estado']          = 1;
    $adj->insert();

    $saved[] = $safeName;
  }

  return $saved;
}

switch ($action) {

  // ----------------------------------------
  // CREAR SERVICIO
  // ----------------------------------------
  case "topic":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada. Vuelve a ingresar."], 401);
    }

    $title    = isset($_POST['t'])        ? trim((string)$_POST['t'])        : trim((string)param('t', ''));
    $cat      = isset($_POST['c'])        ? trim((string)$_POST['c'])        : trim((string)param('c', ''));
    $desc     = isset($_POST['desc'])     ? trim((string)$_POST['desc'])     : trim((string)param('desc', ''));
    $tipo     = isset($_POST['tipo'])     ? trim((string)$_POST['tipo'])     : trim((string)param('tipo', 'int'));
    $telefono = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : trim((string)param('telefono', ''));

    if ($title === '' || $desc === '' || $cat === '') {
      json_exit(['control' => "0", 'error' => "Campos obligatorios faltantes"], 422);
    }

    $servicio = new _Servicios();
    $servicio->vars['titulo']       = $title;
    $servicio->vars['detalle']      = $desc;
    $servicio->vars['creador']      = $_SESSION['user'];
    $servicio->vars['id_categoria'] = (int)$cat;
    $servicio->vars['tipo']         = in_array($tipo, ['int','ext']) ? $tipo : 'int';
    $servicio->vars['telefono']     = $telefono;
    $servicio->vars['fecha']        = date("Y-m-d H:i:s");
    $servicio->vars['estado']       = 1;
    $servicio->insert();

    if (empty($servicio->vars["id"])) {
      json_exit(['control' => "2", 'error' => "No se pudo crear el servicio"], 500);
    }

    $saved = save_adjuntos_servicios('tema', (int)$servicio->vars["id"], $uploadDir, $allowedMimes);

    $data = ['control' => "1", 'topic_id' => $servicio->vars['id']];
    if (!empty($saved)) $data['adjuntos'] = $saved;
    json_exit($data);
    break;


  // ----------------------------------------
  // RESPONDER
  // ----------------------------------------
  case "reply":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada."], 401);
    }

    header('Content-Type: application/json; charset=utf-8');

    $tema  = isset($_POST['t'])     ? (int)$_POST['t']                : (int)param('t');
    $reply = isset($_POST['reply']) ? trim((string)$_POST['reply'])   : param('reply', false);
    if ($reply !== false) $reply = trim(urldecode((string)$reply));

    if (!$tema || !$reply || strlen($reply) < 1) {
      json_exit(['control' => "0", 'error' => "Campos obligatorios faltantes"]);
    }

    $r = new _Respuestas_Servicios();
    $r->vars["id_tema"]  = $tema;
    $r->vars["id_user"]  = $_SESSION['user'];
    $r->vars["detalle"]  = $reply;
    $r->vars["fecha"]    = date("Y-m-d H:i:s");
    $r->vars["estado"]   = 1;
    $r->insert();

    $reply_id = (int)($r->vars["id"] ?? 0);
    if (!$reply_id) {
      json_exit(['control' => "0", 'error' => "No se pudo guardar la respuesta"]);
    }

    $saved = save_adjuntos_servicios('respuesta', $reply_id, $uploadDir, $allowedMimes);

    $data = ['control' => "1", 'topic_id' => $tema, 'reply_id' => $reply_id];
    if (!empty($saved)) $data['adjuntos'] = $saved;
    json_exit($data);
    break;


  // ----------------------------------------
  // CREAR CATEGORIA
  // ----------------------------------------
  case "newCat":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada."], 401);
    }

    $nombre = isset($_POST['nombre']) ? trim((string)$_POST['nombre']) : trim((string)param('nombre', ''));
    if ($nombre === '') {
      json_exit(['control' => "0", 'error' => "Nombre de categoría requerido"], 422);
    }

    $cat = new _Categorias_Servicios();
    $cat->vars['nombre'] = $nombre;
    $cat->insert();

    if (empty($cat->vars["id"])) {
      json_exit(['control' => "0", 'error' => "No se pudo crear la categoría"], 500);
    }

    json_exit(['control' => "1", 'id' => $cat->vars["id"], 'nombre' => $nombre]);
    break;


  // ----------------------------------------
  // EDITAR SERVICIO
  // ----------------------------------------
  case "editTopic":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada."], 401);
    }

    $id       = isset($_POST['t'])        ? (int)$_POST['t']              : (int)param('t');
    $title    = isset($_POST['title'])    ? trim((string)$_POST['title'])    : trim((string)param('title', ''));
    $cat      = isset($_POST['c'])        ? trim((string)$_POST['c'])        : trim((string)param('c', ''));
    $desc     = isset($_POST['desc'])     ? trim((string)$_POST['desc'])     : trim((string)param('desc', ''));
    $tipo     = isset($_POST['tipo'])     ? trim((string)$_POST['tipo'])     : trim((string)param('tipo', 'int'));
    $telefono = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : trim((string)param('telefono', ''));

    if (!$id || $title === '' || $desc === '' || $cat === '') {
      json_exit(['control' => "0", 'error' => "Campos obligatorios faltantes"], 422);
    }

    $servicio = get_servicio($id);
    if (!$servicio || empty($servicio->vars['id'])) {
      json_exit(['control' => "0", 'error' => "Servicio no encontrado"], 404);
    }

    // Solo el creador o comisión puede editar
    if ((int)$_SESSION['user'] !== (int)$servicio->vars['creador'] && (int)$_SESSION['rol'] <= 1) {
      json_exit(['control' => "0", 'error' => "Sin permiso"], 403);
    }

    $servicio->vars['titulo']       = $title;
    $servicio->vars['detalle']      = $desc;
    $servicio->vars['id_categoria'] = (int)$cat;
    $servicio->vars['tipo']         = in_array($tipo, ['int','ext']) ? $tipo : 'int';
    $servicio->vars['telefono']     = $telefono;
    $servicio->update(['titulo', 'detalle', 'id_categoria', 'tipo', 'telefono']);

    json_exit(['control' => "1", 'topic_id' => $id]);
    break;


  // ----------------------------------------
  // CERRAR SERVICIO
  // ----------------------------------------
  case "topicClose":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada."], 401);
    }

    $tema_id = isset($_POST['t']) ? (int)$_POST['t'] : (int)param('t');
    $reply   = isset($_POST['reply']) ? trim((string)$_POST['reply']) : trim((string)param('reply', ''));

    $servicio = get_servicio($tema_id);
    if (!$servicio || empty($servicio->vars['id'])) {
      json_exit(['control' => "0", 'error' => "Servicio no encontrado"]);
    }

    $servicio->vars['estado'] = 2;
    $servicio->update(["estado"]);

    if ($reply !== '') {
      $r = new _Respuestas_Servicios();
      $r->vars["id_tema"]  = $tema_id;
      $r->vars["id_user"]  = $_SESSION['user'];
      $r->vars["detalle"]  = $reply;
      $r->vars["fecha"]    = date("Y-m-d H:i:s");
      $r->vars["estado"]   = 1;
      $r->insert();
    }

    json_exit(['control' => "1"]);
    break;


  // ----------------------------------------
  // MODERAR SERVICIO (pendiente)
  // ----------------------------------------
  case "topicPending":
    if (ob_get_length()) { ob_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    $tema_id = isset($_POST['t']) ? (int)$_POST['t'] : (int)param('t');
    $estado  = isset($_POST['r']) ? (int)$_POST['r'] : (int)param('r');
    $reply   = isset($_POST['reply']) ? trim((string)$_POST['reply']) : param('reply', false);
    if ($reply !== false) $reply = trim(urldecode((string)$reply));

    if (!$tema_id || !$estado || ($estado !== 1 && ($reply === false || $reply === ""))) {
      json_exit(['control' => "0", 'error' => "Campos obligatorios faltantes"]);
    }

    $servicio = get_servicio($tema_id);
    if (!$servicio || empty($servicio->vars['id'])) {
      json_exit(['control' => "0", 'error' => "Servicio no encontrado"]);
    }

    $servicio->vars['estado'] = $estado;
    $servicio->update(["estado"]);

    $reply_id = 0;
    if ($reply !== false && $reply !== "") {
      $r = new _Respuestas_Servicios();
      $r->vars['id_tema']  = $tema_id;
      $r->vars['id_user']  = $_SESSION['user'];
      $r->vars['detalle']  = $reply;
      $r->vars['fecha']    = date("Y-m-d H:i:s");
      $r->vars['estado']   = 1;
      $r->insert();
      $reply_id = (int)($r->vars["id"] ?? 0);
    }

    $data = ['control' => "1", 'topic_id' => $tema_id];
    if ($reply_id) $data['reply_id'] = $reply_id;
    json_exit($data);
    break;


  // ----------------------------------------
  // AGREGAR ADJUNTOS A SERVICIO EXISTENTE
  // ----------------------------------------
  case "addAdjuntos":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada."], 401);
    }

    $tema_id = isset($_POST['t']) ? (int)$_POST['t'] : (int)param('t');
    if (!$tema_id) {
      json_exit(['control' => "0", 'error' => "ID de servicio requerido"], 422);
    }

    $servicio = get_servicio($tema_id);
    if (!$servicio || empty($servicio->vars['id'])) {
      json_exit(['control' => "0", 'error' => "Servicio no encontrado"], 404);
    }
    if ((int)$_SESSION['user'] !== (int)$servicio->vars['creador'] && (int)$_SESSION['rol'] <= 1) {
      json_exit(['control' => "0", 'error' => "Sin permiso"], 403);
    }

    // Subir archivo único (Dropzone envía de uno en uno por defecto)
    $filesKey = 'adjuntos';
    $hasFile  = isset($_FILES[$filesKey]) && !empty($_FILES[$filesKey]['name']);
    if (!$hasFile) {
      json_exit(['control' => "0", 'error' => "No se recibió ningún archivo"], 400);
    }

    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
      json_exit(['control' => "0", 'error' => "No se puede escribir en uploads/adjuntos."], 500);
    }

    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $maxSize = 16 * 1024 * 1024;
    $names   = normalize_files_array($_FILES[$filesKey]['name']);
    $tmps    = normalize_files_array($_FILES[$filesKey]['tmp_name']);
    $errs    = normalize_files_array($_FILES[$filesKey]['error']);
    $sizes   = normalize_files_array($_FILES[$filesKey]['size']);

    $results = [];
    for ($i = 0; $i < count($names); $i++) {
      $name = (string)($names[$i] ?? '');
      $tmp  = (string)($tmps[$i] ?? '');
      $err  = (int)($errs[$i] ?? 0);
      $size = (int)($sizes[$i] ?? 0);

      if ($name === '' || $tmp === '') continue;
      if ($err !== UPLOAD_ERR_OK)      json_exit(['control'=>"0",'error'=>"Error subiendo archivo (code $err): $name"], 400);
      if ($size > $maxSize)            json_exit(['control'=>"0",'error'=>"Archivo muy grande: $name"], 400);

      $mime = $finfo->file($tmp);
      if (!in_array($mime, $allowedMimes, true)) {
        json_exit(['control'=>"0",'error'=>"Tipo no permitido ($mime): $name"], 400);
      }

      $ext = match ($mime) {
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
        'application/pdf' => 'pdf', 'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/plain' => 'txt', default => 'bin',
      };

      $safeName = bin2hex(random_bytes(16)) . "." . $ext;
      if (!move_uploaded_file($tmp, $uploadDir . $safeName)) {
        json_exit(['control'=>"0",'error'=>"No se pudo guardar el archivo: $name"], 500);
      }

      $adj = new _Adjuntos_Servicios();
      $adj->vars['tipo_entidad']    = 'tema';
      $adj->vars['entidad_id']      = $tema_id;
      $adj->vars['nombre_original'] = $name;
      $adj->vars['nombre_archivo']  = $safeName;
      $adj->vars['extension']       = $ext;
      $adj->vars['mime_type']       = $mime;
      $adj->vars['tamano']          = $size;
      $adj->vars['orden']           = 0;
      $adj->vars['subido_por']      = $_SESSION['user'];
      $adj->vars['estado']          = 1;
      $adj->insert();

      $results[] = [
        'id'             => (int)$adj->vars['id'],
        'nombre_archivo' => $safeName,
        'nombre_original'=> $name,
        'mime_type'      => $mime,
        'extension'      => $ext,
      ];
    }

    json_exit(['control' => "1", 'adjuntos' => $results]);
    break;


  // ----------------------------------------
  // ELIMINAR ADJUNTO
  // ----------------------------------------
  case "deleteAdjunto":
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada."], 401);
    }

    $adj_id = isset($_POST['adj_id']) ? (int)$_POST['adj_id'] : (int)param('adj_id');
    if (!$adj_id) {
      json_exit(['control' => "0", 'error' => "ID de adjunto requerido"], 422);
    }

    $adj = get_adjunto_servicio_by_id($adj_id);
    if (!$adj || empty($adj->vars['id'])) {
      json_exit(['control' => "0", 'error' => "Adjunto no encontrado"], 404);
    }

    // Solo tipo_entidad 'tema' (adjuntos del servicio principal, no de respuestas)
    if ($adj->vars['tipo_entidad'] !== 'tema') {
      json_exit(['control' => "0", 'error' => "Solo se pueden eliminar adjuntos del servicio"], 403);
    }

    // Verificar que el usuario es creador del servicio
    $servicio = get_servicio((int)$adj->vars['entidad_id']);
    if (!$servicio || empty($servicio->vars['id'])) {
      json_exit(['control' => "0", 'error' => "Servicio no encontrado"], 404);
    }
    if ((int)$_SESSION['user'] !== (int)$servicio->vars['creador'] && (int)$_SESSION['rol'] <= 1) {
      json_exit(['control' => "0", 'error' => "Sin permiso"], 403);
    }

    // Borrar archivo del disco
    $filePath = $uploadDir . $adj->vars['nombre_archivo'];
    if (is_file($filePath)) @unlink($filePath);

    // Borrar registro de BD
    $adj->delete();

    json_exit(['control' => "1", 'adj_id' => $adj_id]);
    break;


  // ----------------------------------------
  // LIKE / UNLIKE SERVICIO
  // ----------------------------------------
  case "like":
    $tema_id = param('tema');
    $user_id = param('user');
    $like    = param('like');

    $l = new _SLikes();
    $l->vars['id_tema']  = $tema_id;
    $l->vars['id_user']  = $user_id;
    $l->vars['likes']    = $like;
    $l->insert();
    break;

  // ----------------------------------------
  // LIKE / UNLIKE RESPUESTA DE SERVICIO
  // ----------------------------------------
  case "Rlike":
    $res_id  = param('res');
    $user_id = param('user');
    $like    = param('like');

    $l = new _SRLikes();
    $l->vars['id_respuesta'] = $res_id;
    $l->vars['id_user']      = $user_id;
    $l->vars['likes']        = $like;
    $l->insert();
    break;

  // ----------------------------------------
  // VIEW (registrar visita - sin tabla, no hay en servicios)
  // ----------------------------------------
  case "view":
    // Servicios no tiene tabla de visitas, solo ignoramos
    break;


  default:
    break;
}
?>
