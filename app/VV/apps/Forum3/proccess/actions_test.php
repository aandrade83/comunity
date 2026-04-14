<?
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
session_start();
 echo "<pre>";
   print_r($_POST);

error_reporting(E_ALL); 
ini_set('display_errors', '1');


$action  = param("ac");

 
switch ($action){


  case "test":

  

 
  $title  = param('t',false);
  $cat  = param("c");
  $desc  = param('desc',false);

  /*
    $topic = new _Temas();
    $topic->vars['titulo'] = $title;
    $topic->vars['detalle'] = $desc;
    $topic->vars['creador'] = $_SESSION['user'];
    $topic->vars['likes'] = 0;
    $topic->vars['unlikes'] = 0;
    $topic->vars['id_categoria'] = $cat;
    $topic->vars['fecha'] = date("Y-m-d H:i:s");
    $topic->vars['estado'] = 0;
    $topic->insert();
  */
    
    if($topic->vars["id"]){
      $data['control'] = "1"; 
      
    } else {
       $data['control'] = "2"; 

    }
  
     
  
     echo json_encode($data);
   break;


 case "topic":

  // ✅ Ahora viene por POST (FormData). Si tu param() lee $_REQUEST, igual sirve.
  $title = isset($_POST['t']) ? trim($_POST['t']) : param('t', false);
  $cat   = isset($_POST['c']) ? $_POST['c'] : param('c');
  $desc  = isset($_POST['desc']) ? trim($_POST['desc']) : param('desc', false);

  // Validación básica
  if (!$title || !$desc || !$cat) {
    $data['control'] = "0";
    $data['error'] = "Campos obligatorios faltantes";
    echo json_encode($data);
    break;
  }

  // ==========================
  // VALIDAR ADJUNTOS (server-side)
  // ==========================
  $hasFiles = isset($_FILES['adjuntos']) && !empty($_FILES['adjuntos']['name'][0]);

  $maxSize = 8 * 1024 * 1024; // 8MB por archivo (ajustalo)
  $allowedMimes = [
    'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    'application/pdf'
  ];

  $pdfCount = 0;
  $imgCount = 0;

  if ($hasFiles) {
    $count = count($_FILES['adjuntos']['name']);

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    for ($i = 0; $i < $count; $i++) {
      $err = $_FILES['adjuntos']['error'][$i];

      if ($err !== UPLOAD_ERR_OK) {
        $data['control'] = "0";
        $data['error'] = "Error subiendo archivo (code $err)";
        echo json_encode($data);
        break 2;
      }

      if ($_FILES['adjuntos']['size'][$i] > $maxSize) {
        $data['control'] = "0";
        $data['error'] = "Archivo muy grande: " . $_FILES['adjuntos']['name'][$i];
        echo json_encode($data);
        break 2;
      }

      $tmp = $_FILES['adjuntos']['tmp_name'][$i];
      $mime = $finfo->file($tmp);

      if (!in_array($mime, $allowedMimes, true)) {
        $data['control'] = "0";
        $data['error'] = "Tipo no permitido: " . $_FILES['adjuntos']['name'][$i];
        echo json_encode($data);
        break 2;
      }

      if ($mime === 'application/pdf') $pdfCount++;
      if (str_starts_with($mime, 'image/')) $imgCount++;
    }

    // Regla: varias imágenes O un solo PDF (no mezclado)
    if ($pdfCount > 1 || ($pdfCount === 1 && $imgCount > 0)) {
      $data['control'] = "0";
      $data['error'] = "Puedes subir varias imágenes O un solo PDF (no mezclado).";
      echo json_encode($data);
      break;
    }
  }

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
  $topic->vars['estado']       = 0;
  $topic->insert();

  if (!$topic->vars["id"]) {
    $data['control'] = "2";
    echo json_encode($data);
    break;
  }

  // ==========================
  // GUARDAR ARCHIVOS + INSERT EN adjuntos
  // ==========================
  $saved = [];
  $uploadDir = dirname(__DIR__) . '/uploads/adjuntos/'; // Forum/uploads/adjuntos/

  if ($hasFiles) {
    if (!is_dir($uploadDir)) {
      @mkdir($uploadDir, 0755, true);
    }

    $count = count($_FILES['adjuntos']['name']);
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    for ($i = 0; $i < $count; $i++) {

      $tmp   = $_FILES['adjuntos']['tmp_name'][$i];
      $mime  = $finfo->file($tmp);
      $orig  = $_FILES['adjuntos']['name'][$i];
      $size  = (int)$_FILES['adjuntos']['size'][$i];

      // extensión segura por MIME
      $ext = match ($mime) {
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'image/webp'       => 'webp',
        'image/gif'        => 'gif',
        'application/pdf'  => 'pdf',
        default            => 'bin',
      };

      $safeName = bin2hex(random_bytes(16)) . "." . $ext;
      $dest = $uploadDir . $safeName;

      if (!move_uploaded_file($tmp, $dest)) {
        // Si falla un archivo, podés decidir si abortás o seguís.
        // Yo sigo pero registro el error.
        $data['adjuntos_error'][] = "No se pudo guardar: $orig";
        continue;
      }

      // INSERT en tabla adjuntos (tu clase)
      $adj = new _adjuntos();
      $adj->vars['tipo_entidad']    = 'tema';
      $adj->vars['entidad_id']      = $topic->vars['id'];
      $adj->vars['nombre_original'] = $orig;
      $adj->vars['nombre_archivo']  = $safeName;
      $adj->vars['extension']       = $ext;
      $adj->vars['mime_type']       = $mime;
      $adj->vars['tamaño']          = $size;   // ojo: tu columna tiene ñ según el CREATE
      $adj->vars['orden']           = $i;
      $adj->vars['subido_por']      = $_SESSION['user'];
      $adj->vars['estado']          = 1;

      $adj->insert();

      $saved[] = $safeName;
    }
  }

  // ==========================
  // RESPUESTA OK + TELEGRAM
  // ==========================
  $data['control'] = "1";
  $data['topic_id'] = $topic->vars['id'];
  $data['adjuntos'] = $saved;

  $msj  = "---------------------------------------------------------------$";
  $msj .= "        HAY UN NUEVO TEMA PARA REVISION                  $";
  $msj .= "---------------------------------------------------------------$";
  $msj .= "Filial: ".$_SESSION['filial']."$" ;
  $msj .= "Tema :".$topic->vars['titulo']."$" ;
  $msj .= "---------------------------------------------------------$";
  $msj  = str_replace(" ","%20",$msj);
   // @file_get_contents("https://lab.lacallecr.com/VV/apps/telegram/bridge.php?ac=new&msj=".$msj);

  echo json_encode($data);
  break;

  
 


 case "topicPending":

   $tema_id  = param('t');
   $estado  = param("r");
   $reply  = param('reply',false);

    $tema = get_topic($tema_id);

    $tema->vars['estado'] = $estado;
    $tema->update(array("estado"));
      
    if($reply != "") {

    $r_topic = new _Respuestas();
    $r_topic->vars['id_tema'] = $tema_id;
    $r_topic->vars['id_user'] = $_SESSION['user'];
    $r_topic->vars['detalle'] = $reply;
    $r_topic->vars['fecha'] = date("Y-m-d H:i:s");
    $r_topic->vars['estado'] = 1;
    $r_topic->insert();
  
    
    if($r_topic->vars["id"]){
      $data['control'] = "1"; 
            
    } else {
       $data['control'] = "2"; //error 


    }

    } else {
        $data['control'] = "1"; 
    }

  if($estado == "1"){ 
     $msj =  "---------------------------------------------------------------$";
     $msj .= "        HAY UN NUEVO TEMA EN EL SISTEMA                  $";
     $msj .= "---------------------------------------------------------------$";
     $msj .= "Filial: ".$tema->vars['info']->vars['filial']."$" ;
     $msj .= "Tema :".$tema->vars['titulo']."$" ;
     $msj .= "---------------------------------------------------------$";

  }   

  if($estado == "3"){ 
     $msj =  "----------------------------------------------------------$";
     $msj .= "        SU TEMA HA SIDO ACTUALIZADO                      $";
     $msj .= "---------------------------------------------------------$";
     $msj .= "Filial: ".$tema->vars['info']->vars['filial']."$" ;
     $msj .= "Tema :".$tema->vars['titulo']."$" ;
     $msj .= "Estado : Rechazado$" ;
     $msj .= "Razon :".$reply."$" ;
     $msj .= "--> Cualquier consulta porfavor contactar a la comision $";

  }   

   if( $data['control'] != 2){
    $msj = str_replace(" ","%20",$msj);
    @file_get_contents("https://lab.lacallecr.com/VV/apps/telegram/bridge.php?ac=check&c=".$estado."&msj=".$msj."&f=".$tema->vars['info']->vars['filial']);
  }
 

   echo json_encode($data);
   break;


 case "topicClose":

   $tema_id  = param('t');
   $estado  = 2;
   $reply  = param('reply',false);

    $tema = get_topic($tema_id);
    $tema->vars['estado'] = $estado;
    $tema->update(array("estado"));

    $r_topic = new _Respuestas();
    $r_topic->vars['id_tema'] = $tema_id;
    $r_topic->vars['id_user'] = $_SESSION['user'];
    $r_topic->vars['detalle'] = $reply;
    $r_topic->vars['fecha'] = date("Y-m-d H:i:s");
    $r_topic->vars['estado'] = 2;
    $r_topic->insert();
  
    
    if($r_topic->vars["id"]){
      $data['control'] = "1"; 
      
    } else {
       $data['control'] = "2"; 

    }


     

  if($data['control'] == "1"){ 
     $msj =  "----------------------------------------------------------$";
     $msj .= "               TEMA HA SIDO CERRADO                       $";
     $msj .= "----------------------------------------------------------$";
     $msj .= "Filial: ".$tema->vars['info']->vars['filial']."$" ;
     $msj .= "Tema :".$tema->vars['titulo']."$" ;
     $msj .= "Estado : Cerrado$" ;
     $msj .= "Razon :".$reply."$" ;
     $msj .= "--> Cualquier consulta porfavor contactar a la comision $";

  


    $msj = str_replace(" ","%20",$msj);
    @file_get_contents("https://lab.lacallecr.com/VV/apps/telegram/bridge.php?ac=close&msj=".$msj."&f=".$tema->vars['info']->vars['filial']);
  }
 


  
     echo json_encode($data);
   break;


   


case "reply":

   $tema_id  = param('t');
   $reply  = param('reply',false);
$text = $_GET['reply'];
   $reply = urldecode($text);

    //$tema = get_topic($tema_id);
/* ACTUALIZAR LUEGO LAST UPDATE
    $tema->vars['estado'] = $estado;
    $tema->update(array("estado"));
      
*/

    $r_topic = new _Respuestas();
    $r_topic->vars['id_tema'] = $tema_id;
    $r_topic->vars['id_user'] = $_SESSION['user'];
    $r_topic->vars['detalle'] = $reply;
    $r_topic->vars['fecha'] = date("Y-m-d H:i:s");
    $r_topic->vars['estado'] = 1;
    $r_topic->insert();
  
    
    if($r_topic->vars["id"]){
      $data['control'] = "1"; 
      
    } else {
       $data['control'] = "2"; 

    }
  
     echo json_encode($data);
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