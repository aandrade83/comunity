<?php 
// TELEGRAM


class _Bot{
	private $btoken;private $website;
	private $data;private $update;
	private $nombre;private $apellido;
	private $chatID;private $chatType;
	private $message;

	function __construct($data){
		$this->btoken="7112681619:AAFxz0ig4NnZrHCQKpiIvcOFPYYGrDiX4V8";
		$this->website="https://api.telegram.org/bot".$this->btoken;
	}


//////////////////////////////////////////////////
//
//////////////////////////////////////////////////


public function explodeMensaje($msg){
	$dato=explode(" ", $msg);
	return $dato;
}


	public function AddUserAgent($entrada){
		$mensaje=$this->fileGetContents($entrada);
		$dato=$this->explodeMensaje($mensaje["message"]);
		//appendToLog("Este es un mensaje de prueba AAS.");
//
		$datos = implode("-",$dato);
		$phone_id = implode("-",$mensaje);
		$phone_id = explode("-",$phone_id);
		$phone_id = $phone_id[0]; 
		$user  = $dato[1];
		$filial  = $dato[2];
		$pass  = $dato[3];
		$control = 1;



    if($pass != ""){
    $pass = biencript($pass);
    $login = get_master_login($filial,$pass);
    
    $control = 1;
      } else {$control = 0;}

    
    
        
    if(!isset($login->vars["id"])){
         $control = 0; $msj = "Password Incorrecto";
    } else {
        $control = 1; $msj = "Password Correcto".$login->vars["id"];  
    } 
    
    
     if (!is_numeric($filial)) { $control = 0 ;  $msj = "Formato Incorrecto";} 
      else {
        $check = get_telegram_login($filial,$phone_id); 
        if(isset($check->vars["id"])){ $control = 2 ;  $msj = "Ya te habias registrado anteriormente";
      }
     }




    if($control){
    //Insert data to BD
    $telegram = new _telegram();
    
    $telegram->vars['nickname'] = strtoupper($user);
    $telegram->vars['filial'] = $filial;
    $telegram->vars['phone_id'] = $phone_id;
    $telegram->vars['type'] = 2;
    $telegram->vars['date_added'] = date("Y-m-d");
    $telegram->insert();
      if($telegram->vars['id'] > 0){ $control = 1; $msj = 'Perfecto';}
    }

	
		
	}


  	

//////////////////////////////////////////////////
//
//////////////////////////////////////////////////

	public function envioMensaje($response,$fgc){
		$resultado=$this->fileGetContents($fgc);
		$params=['chat_id'=>$resultado["chatID"],'text'=>$response,];
		$ch = curl_init($this->website . '/sendMessage');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		return $result = curl_exec($ch);
		curl_close($ch);
	}
	
	
	public function envioMensajeProcesos($chatid,$response){

		$params=['chat_id'=>$chatid,'text'=>$response,];
		$ch = curl_init($this->website . '/sendMessage');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		return $result = curl_exec($ch);
		curl_close($ch);
	}

	
//////////////////////////////////////////////////
//
//////////////////////////////////////////////////

	public function fileGetContents($fgc){
		$var=file_get_contents($fgc);
		$var=json_decode($var, TRUE);

		$nombre=$var['message']['chat']['first_name'];
		$apellidos=$var['message']['chat']['last_name'];
		$chatID=$var['message']['chat']['id'];
		$chatType=$var['message']['chat']['type'];
		$message=$var['message']['text'];
		return array("chatID"=>$chatID,"chatType"=>$chatType,"message"=>$message);
	}
}



 ?>