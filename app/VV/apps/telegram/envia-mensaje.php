<?php 

class Mensajes{

	public function enviaMensajes($mensaje){
		$token = "1014135132:AAEl1BtfQD0fNU-XQAnHvmk0wlHk-C9SpRM";
		$id = "643719922";
		$urlMsg = "https://api.telegram.org/bot{$token}/sendMessage";
		$msg = $mensaje;
		 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlMsg);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id={$id}&parse_mode=HTML&text=$msg");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		$server_output = curl_exec($ch);
		curl_close($ch);
	}
}

?>