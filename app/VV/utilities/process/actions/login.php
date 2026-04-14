<?php
ob_start();
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

$user = $_POST["usuario"];
$pass = two_way($_POST["password"]);
$cel = $_POST["celula"];

if($cel == 99){ // Oficina. Un superAdmin puede estar asociado a varias Celulas por eso se revisa asi
    $sa = get_superadmins();

    if(isset($sa[$user."_".$pass]["id_base"])){
        $cel  = $sa[$user."_".$pass]["id_base"]; // Un super admin pudo salir en cualquier Celula con esto aseguramos que entre en la que salio 
 
    }
}

$login = get_master_login($user,$pass,$cel);


if(!empty($login['password'])){

	session_start();
	session_regenerate_id(true);
	
	$_SESSION['rol'] = $login["rol"];
	$_SESSION['usuario'] = $login["id"];
	header("Location: https://lab.lacallecr.com/VV/operaciones/usuarios");

}else{ 

echo '<script>alert("Usuario o contraseña incorrectos");</script>';
header("refresh:0; url=' https://lab.lacallecr.com/VV/'");

}


?>