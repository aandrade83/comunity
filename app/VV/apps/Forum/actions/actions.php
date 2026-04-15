<?php
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
session_start();
ob_start();

/**
 * ✅ Producción: no mostrar errores en salida (rompe JSON)
 * Si querés verlos, activás DEBUG_UPLOADS abajo.
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/vv_actions_error.log');

define('DEBUG_UPLOADS', false); // 👈 ponelo true solo para depurar

$action = param("ac");

function json_exit(array $data, int $httpCode = 200): void {
  if (ob_get_length()) { ob_clean(); }
  http_response_code($httpCode);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

function dbg(string $msg): void {
  if (!DEBUG_UPLOADS) return;
  $log = __DIR__ . '/topic_debug.log';
  @file_put_contents($log, $msg . "\n", FILE_APPEND);
}

function normalize_files_array($arr): array {
  if (!is_array($arr)) return [$arr];
  $first = reset($arr);

  // Caso raro: [ {'0':'a'}, {'1':'b'} ] => flatten
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

switch ($action) {

  case "topic":

    // ✅ Si no hay sesión
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada. Vuelve a ingresar."], 401);
    }

    // Campos (permitir categoria "0")
    $title = isset($_POST['t']) ? trim((string)$_POST['t']) : trim((string)param('t', ''));
    $cat   = isset($_POST['c']) ? trim((string)$_POST['c']) : trim((string)param('c', ''));
    $desc  = isset($_POST['desc']) ? trim((string)$_POST['desc']) : trim((string)param('desc', ''));

    if ($title === '' || $desc === '' || $cat === '') {
      json_exit(['control' => "0", 'error' => "Campos obligatorios faltantes"], 422);
    }

    // Debug (opcional)
    dbg("==== " . date('c') . " ====");
    dbg("POST: " . json_encode($_POST));
    dbg("FILES: " . json_encode($_FILES));

    // ==========================
    // INSERT TOPIC
    // ==========================
    $topic = new _Temas();
    $topic->vars['titulo']       = $title;
    $topic->vars['detalle']      = $desc;
    $topic->vars['creador']      = $_SESSION['user'];
    $topic->vars['likes']        = 0;
    $topic->vars['unlikes']      = 0;
    $topic->vars['id_categoria'] = $cat;
    $topic->vars['fecha']        = date("Y-m-d H:i:s");
    $topic->vars['estado']       = 1;
    $topic->insert();

    if (empty($topic->vars["id"])) {
      json_exit(['control' => "2", 'error' => "No se pudo crear el tema"], 500);
    }

    // ==========================
    // ADJUNTOS
    // ==========================
    $filesKey = 'adjuntos';
    $saved = [];

    $hasFiles = isset($_FILES[$filesKey]) && !empty($_FILES[$filesKey]['name']);
    if ($hasFiles) {

      $names = normalize_files_array($_FILES[$filesKey]['name']);
      $tmps  = normalize_files_array($_FILES[$filesKey]['tmp_name']);
      $errs  = normalize_files_array($_FILES[$filesKey]['error']);
      $sizes = normalize_files_array($_FILES[$filesKey]['size']);

      $count = count($names);

      $maxSize = 16 * 1024 * 1024; // 16MB
      $allowedMimes = [
        'image/jpeg','image/png','image/webp','image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain'
      ];

      $uploadDir = dirname(__DIR__) . '/uploads/adjuntos/';
      if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

      if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        json_exit(['control'=>"0",'error'=>"No se puede escribir en uploads/adjuntos. Revisa permisos."], 500);
      }

      $finfo = new finfo(FILEINFO_MIME_TYPE);

      for ($i=0; $i<$count; $i++) {
        $name = (string)($names[$i] ?? '');
        $tmp  = (string)($tmps[$i] ?? '');
        $err  = (int)($errs[$i] ?? 0);
        $size = (int)($sizes[$i] ?? 0);

        if ($name === '' || $tmp === '') continue;

        if ($err !== UPLOAD_ERR_OK) {
          json_exit(['control'=>"0",'error'=>"Error subiendo archivo (code $err): $name"], 400);
        }
        if ($size > $maxSize) {
          json_exit(['control'=>"0",'error'=>"Archivo muy grande: $name"], 400);
        }

        $mime = $finfo->file($tmp);
        if (!in_array($mime, $allowedMimes, true)) {
          json_exit(['control'=>"0",'error'=>"Tipo no permitido ($mime): $name"], 400);
        }

        $ext = match ($mime) {
          'image/jpeg' => 'jpg',
          'image/png' => 'png',
          'image/webp' => 'webp',
          'image/gif' => 'gif',
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

        $adj = new _Adjuntos();
        $adj->vars['tipo_entidad']    = 'tema';
        $adj->vars['entidad_id']      = $topic->vars['id'];
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
    }

    // OK
    $data = ['control'=>"1",'topic_id'=>$topic->vars['id']];
    if (!empty($saved)) $data['adjuntos'] = $saved;

    json_exit($data);
    break;

 
case "topicPending":

  if (ob_get_length()) { ob_clean(); }
  header('Content-Type: application/json; charset=utf-8');

  // ---- inputs: POST (Dropzone/FormData) + fallback legacy ----
  $tema_id = isset($_POST['t']) ? (int)$_POST['t'] : (int)param('t');
  $estado  = isset($_POST['r']) ? (int)$_POST['r'] : (int)param('r');

  $reply = isset($_POST['reply'])
    ? trim((string)$_POST['reply'])
    : param('reply', false);

  if ($reply !== false) {
    $reply = trim(urldecode((string)$reply));
  }

  // Regla original: si estado == 1 (Aprobado) puede ir sin reply
  if (!$tema_id || !$estado || ($estado !== 1 && ($reply === false || $reply === ""))) {
    echo json_encode(['control' => "0", 'error' => "Campos obligatorios faltantes"]);
    break;
  }

  // ---- buscar tema ----
  $tema = get_topic($tema_id);
  if (!$tema || empty($tema->vars['id'])) {
    echo json_encode(['control' => "0", 'error' => "Tema no encontrado"]);
    break;
  }

  // ---- actualizar estado ----
  $tema->vars['estado'] = $estado;
  $tema->update(array("estado"));

  // ---- insertar respuesta (solo 1 vez) ----
  $reply_id = 0;
  $passed_reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;

  if ($passed_reply_id > 0) {
    $reply_id = $passed_reply_id;
  } else {
    if ($reply !== false && $reply !== "") {
      $r_topic = new _Respuestas();
      $r_topic->vars['id_tema'] = $tema_id;
      $r_topic->vars['id_user'] = $_SESSION['user'];
      $r_topic->vars['detalle'] = $reply;
      $r_topic->vars['fecha']   = date("Y-m-d H:i:s");
      $r_topic->vars['estado']  = 1;
      $r_topic->insert();
      $reply_id = (int)($r_topic->vars["id"] ?? 0);

      if (!$reply_id) {
        echo json_encode(['control' => "2", 'error' => "No se pudo guardar la respuesta"]);
        break;
      }
    }
  }

  

  // ---- telegram (igual que tu lógica, mínimo cambio) ----
  if ($estado == 10) {
    $msj  = "---------------------------------------------------------------$";
    $msj .= "        HAY UN NUEVO TEMA EN EL SISTEMA                  $";
    $msj .= "---------------------------------------------------------------$";
    $msj .= "Filial: ".$tema->vars['info']->vars['filial']."$";
    $msj .= "Tema :".$tema->vars['titulo']."$";
    $msj .= "---------------------------------------------------------$";
  }

  if ($estado == 30) {
    $msj  = "----------------------------------------------------------$";
    $msj .= "        SU TEMA HA SIDO ACTUALIZADO                      $";
    $msj .= "---------------------------------------------------------$";
    $msj .= "Filial: ".$tema->vars['info']->vars['filial']."$";
    $msj .= "Tema :".$tema->vars['titulo']."$";
    $msj .= "Estado : Rechazado$";
    $msj .= "Razon :".$reply."$";
    $msj .= "--> Cualquier consulta porfavor contactar a la comision $";
  }

  if (!empty($msj)) {
    $msj = str_replace(" ","%20",$msj);
    @file_get_contents("https://lab.lacallecr.com/VV/apps/telegram/bridge.php?ac=check&c=".$estado."&msj=".$msj."&f=".$tema->vars['info']->vars['filial']);
  }

  // ---- respuesta final JSON ----
  $data = ['control' => "1", 'topic_id' => $tema_id];
  if ($reply_id) $data['reply_id'] = $reply_id;
  //if (!empty($saved)) $data['adjuntos'] = $saved;
//
  echo json_encode($data);
  break;


   


case "reply":

  header('Content-Type: application/json; charset=utf-8');

  // Aceptar POST (Dropzone/FormData) y también GET legacy
  $tema  = isset($_POST['t']) ? (int)$_POST['t'] : (int)param('t');
  $reply = isset($_POST['reply']) ? trim((string)$_POST['reply']) : param('reply', false);
  if ($reply !== false) $reply = trim(urldecode((string)$reply));

  if (!$tema || !$reply || strlen($reply) < 1) {
    json_exit(['control' => "0", 'error' => "Campos obligatorios faltantes"]);
  }

  // Insertar respuesta
  $r_topic = new _Respuestas();
  $r_topic->vars["id_tema"] = $tema;
  $r_topic->vars["id_user"] = $_SESSION['user'];
  $r_topic->vars["detalle"] = $reply;
  $r_topic->vars["fecha"]   = date("Y-m-d H:i:s");
  $r_topic->vars["estado"]  = 1;
  $r_topic->insert();

  $reply_id = (int)($r_topic->vars["id"] ?? 0);
  if (!$reply_id) {
    json_exit(['control' => "0", 'error' => "No se pudo guardar la respuesta"]);
  }

  // ==========================
  // ADJUNTOS (opcionales)
  // ==========================
  $saved = [];
  $filesKey = 'adjuntos';

  $hasFiles = isset($_FILES[$filesKey]) && !empty($_FILES[$filesKey]['name']);
  if ($hasFiles) {

    $names = normalize_files_array($_FILES[$filesKey]['name']);
    $types = normalize_files_array($_FILES[$filesKey]['type']);
    $tmps  = normalize_files_array($_FILES[$filesKey]['tmp_name']);
    $errs  = normalize_files_array($_FILES[$filesKey]['error']);
    $sizes = normalize_files_array($_FILES[$filesKey]['size']);

    $count = count($names);

    $maxSize = 16 * 1024 * 1024; // 16MB por archivo
    $allowedMimes = [
      'image/jpeg', 'image/png', 'image/webp', 'image/gif',
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'text/plain'
    ];

    $uploadDir = dirname(__DIR__) . '/uploads/adjuntos/'; // Forum/uploads/adjuntos/
    if (!is_dir($uploadDir)) {
      @mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
      json_exit(['control' => "0", 'error' => "Directorio de uploads no existe o no es writable"]);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    for ($i = 0; $i < $count; $i++) {

      $name = (string)$names[$i];
      $err  = (int)$errs[$i];
      $tmp  = (string)$tmps[$i];
      $size = (int)$sizes[$i];

      if ($err !== UPLOAD_ERR_OK) {
        json_exit(['control' => "0", 'error' => "Error subiendo archivo (code $err): $name"]);
      }

      if ($size > $maxSize) {
        json_exit(['control' => "0", 'error' => "Archivo muy grande: $name"]);
      }

      $mime = $finfo->file($tmp);
      if (!in_array($mime, $allowedMimes, true)) {
        json_exit(['control' => "0", 'error' => "Tipo no permitido ($mime): $name"]);
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
        json_exit(['control' => "0", 'error' => "No se pudo guardar el archivo: $name"]);
      }

      $adj = new _Adjuntos();
      $adj->vars['tipo_entidad']    = 'respuesta';
      $adj->vars['entidad_id']      = $reply_id;
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
  }

  $data = ['control' => "1", 'topic_id' => $tema, 'reply_id' => $reply_id];
  if (!empty($saved)) $data['adjuntos'] = $saved;

  json_exit($data);
  break;



case "view":
    
   $tema_id  = param('tema');
   $user_id  = param('user');
   
   $views = get_views_tema_user($tema_id,$user_id);
   

   if(empty($views)){
    $view = new _Views();
    $view->vars['id_tema'] = $tema_id ;
    $view->vars['id_user'] = $user_id ;
    $view->insert() ;
   
   } 
   break;

   case "like":
    
   $tema_id  = param('tema');
   $user_id  = param('user');
   $like  = param('like');
   
   
    $likes = new _Likes();
    $likes->vars['id_tema'] = $tema_id ;
    $likes->vars['id_user'] = $user_id ;
    $likes->vars['likes'] = $like ;
    $likes->insert();
   
   break;

    case "Rlike":
    
   $res_id  = param('res');
   $user_id  = param('user');
   $like  = param('like');
   
   
    $likes = new _RLikes();
    $likes->vars['id_respuesta'] = $res_id ;
    $likes->vars['id_user'] = $user_id ;
    $likes->vars['likes'] = $like ;
    $likes->insert();
   
   break;




default: break;

}
?>