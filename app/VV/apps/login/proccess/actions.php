<?php

//header("Access-Control-Allow-Origin: https://www.lab.lacallecr.com");

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

$user      = param("user");
$pass      = param("pass");
$action  =   param("ac");


$pass = biencript($pass);

switch ($action){


  case "login":
  
   $login = get_master_login($user,$pass);
   $total = 0;
  
    $log_ip = get_ip();
    $log = new _logs();
    if(isset($login->vars["id"])){ $id = $login->vars["id"];} else { $id = 0;}
    $log->vars["user"] = $id;
    $log->vars["ip"] = $log_ip;
    $log->vars["date"] = date("Y-m-d H:i:s");

  if(!is_null($login)){
    
    session_start();
    session_regenerate_id(true);
    $_SESSION['loged'] = "1";
    $_SESSION['user'] = $login->vars["id"];
    $_SESSION['filial'] = $login->vars["filial"];
    $_SESSION['rol'] = $login->vars["rol"];
    
    if($login->vars["activo"] == 0){
      $log->vars["data"] = "Unactive user ";
      $data['login'] = "3"; 
      
    } else {
       $log->vars["data"] = "Login Succesfully ".$login->vars["filial"];
       
       if($login->vars["rol"] == 2){
        $pendings = get_pending_topics_total();
        
        $total = $pendings['total'] ;
       }
        $data['total'] = $total;      


       $data['login'] = "1"; 
    }
  
  }else{
      $data['login'] = "2"; 
     $log->vars["data"]= "Failure Login ".$user; 
   }
    $log->insert();

   echo json_encode($data);
   break;

   case "logout":

          session_start();
          session_unset();
          session_destroy();
          $data['login'] = "1"; 
          echo json_encode($data);


    break;



default: break;

}


?>