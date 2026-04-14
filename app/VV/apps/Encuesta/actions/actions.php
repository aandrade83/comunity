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



$action = param("ac");

function json_exit(array $data, int $httpCode = 200): void {
  if (ob_get_length()) { ob_clean(); }
  http_response_code($httpCode);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}


switch ($action) {

  case "enc":


    // ✅ Si no hay sesión
    if (empty($_SESSION['user'])) {
      json_exit(['control' => "0", 'error' => "Sesión expirada. Vuelve a ingresar."], 401);
    }
    
    $enc   = param("encuestaId");
    $detalle  = param("valor");

    // ==========================
    // INSERT TOPIC
    // ==========================
    $resp = new _Respuesta_Encuestas();
    $resp->vars['id_encuesta']       = $enc;
    $resp->vars['id_user']      = $_SESSION['filial'];
    $resp->vars['detalle']        = $detalle;
    $resp->vars['fecha']        = date("Y-m-d H:i:s");
    $resp->insert();

    if (empty($resp->vars["id"])) {
      json_exit(['control' => "2", 'error' => "No se Guardar la Encuesta"], 500);
    }


    // OK
    $data = ['control'=>"1",'resp'=>$resp->vars['id']];
    
    json_exit($data);
    break;


default: break;

}
?>
 
