<?
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
require_once($_SERVER['DOCUMENT_ROOT']."/VV/apps/telegram/class.php");



$msj = $_GET['msj']; 
$msj = str_replace("$","\n",$msj);
$msj= str_replace("__"," ",$msj);
  //$phone_id   = $_GET['phone'];
$msj = utf8_encode ($msj); 
$var = "";
$action = param('ac');
$control = param('c');

switch($action){


  case 'new':

  $commisions = get_telegram_users(1);

  if(!empty($commisions)){

    foreach($commisions as $comision){

      $telegram = new _Bot($var);
      $chatid= $comision->vars['phone_id'];
      $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
    }
  }

  break;

  case 'check':
  $filial = param('f');

  if($control == 1) {

    $commisions = get_telegram_users();

    if(!empty($commisions)){

      foreach($commisions as $comision){

        $telegram = new _Bot($var);
        $chatid= $comision->vars['phone_id'];
        $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
      }
    }
  }     



  if($control == 3) {

    $commisions = get_telegram_users_by_filial($filial);

    if(!empty($commisions)){

      foreach($commisions as $comision){

        $telegram = new _Bot($var);
        $chatid= $comision->vars['phone_id'];
        $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
      }
    }


  $commisions = get_telegram_users(1);

  $msj = str_replace("SU TEMA","UN TEMA",$msj);

  if(!empty($commisions)){

    foreach($commisions as $comision){

      $telegram = new _Bot($var);
      $chatid= $comision->vars['phone_id'];
      $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
    }
  }




  }
  break;

  case 'close':
  $filial = param('f');

  
    $commisions = get_telegram_users_by_filial($filial);

    if(!empty($commisions)){

      foreach($commisions as $comision){

        $telegram = new _Bot($var);
        $chatid= $comision->vars['phone_id'];
        $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
      }
    }


  $commisions = get_telegram_users(1);

  if(!empty($commisions)){

    foreach($commisions as $comision){

      $telegram = new _Bot($var);
      $chatid= $comision->vars['phone_id'];
      $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
    }
  }


  break;     


}




/*

   $telegram = new _Bot($var);
   //$chatid= $phone_id;
 //  $result = json_decode($telegram->envioMensajeProcesos($chatid,$msj),true);
   
   $result2 = json_decode($telegram->envioMensajeProcesos(691036561,$msj),true); // ALEXIS's PHONE ID 691036561

*/
   ?>
