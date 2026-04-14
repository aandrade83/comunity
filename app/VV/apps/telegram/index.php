<?php
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
	session_start();
   require_once("class.php"); 

	$entrada=array();
	$entrada='php://input';
	$bot= new _Bot($var);
	$mensaje=$bot->fileGetContents($entrada);

	///////////////////////////////////////

	$dato=$bot->explodeMensaje($mensaje['message']);

	switch(strtolower($dato[0])){


		case "/dolares":
		     $compra = 555;
			 $venta =  655;
			 $resultado = "Ventas: ".$venta." Comprass: ".$compra;
			
			 $bot->envioMensaje($resultado,$entrada);
		
			break;

		case "/add":
			 $addID=$bot->AddUserAgent($entrada);

			if($addID['control'] == 1){
			$message =  $addID["msj"]. " Gracias ".$addID["user"]." Su telefono fue registrado, ahora podras recibir notificaciones de nuestro sistema ";
			} 
			if($addID['control'] == 0){
			 $message = $addID["msj"]. " Recuerda enviar add/ UNNOMBREPEGADO #FILIAL PASSWORD ";	
			}

			if($addID['control'] == 2){
			 $message = $addID["msj"]. " Solamente puedes registrar un usuario filial a un telefono Puedes contactar a la comision si necesitas soporte";	
			}
			
			$bot->envioMensaje($message	,$entrada);
		    break;

	case "/start":
			
			$message = "Hola, Gracias por ingresar a nuestro sistema de notificaciones, para afiliarse favor enviar el comando /add  Nombre #Filial PASWWORD ej: /add Juancho 200 PassdeJuancho";			
			$bot->envioMensaje($message,$entrada);
		    break;

		

        default:
        	$response="Must send a Valid command";
        	$bot->envioMensaje($response,$entrada);
        
        break;


	}

echo "asa";
?>



