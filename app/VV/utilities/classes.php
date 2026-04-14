<?php
ob_start();

class Debug{

	var $vars = array();

	function initial(){}

//////////////////////////////////////////////////
//
//////////////////////////////////////////////////

	function checkErrorCode($code,$error =""){

	    //echo $code;
	   if(isset($_SERVER['HTTP_REFERER'])) {
	     $url = $_SERVER['HTTP_REFERER'];
	   }
	   //switch to Alerts
		switch($code){

			//MYSQL ERRORS
			case '1062': 
		    	$alert = "<script>window.alert('Error # 1452:  Problema de llave Primaria');</script>"; 
		    	break;
			case '1452': 
		    	$alert = "<script>window.alert('Error # 1452:  Problema de llave Secundaria');</script>"; 
		    	break;

			case '1054': 
				$alert = "<script>window.alert('Error # 1054: Columna no encontrada');</script>";
				 break;

			case '1146': 
				$alert = "<script>window.alert('Error # 1146: Tabla no encontrada');</script>";
				 break;

			// WARNINGS ERRORS
			case 'W02': 
				$alert ="<script>window.alert('Error # w02: Faltan Parametros obligatorios');</script>";
				break;
			case 'W03': 
				$alert ="<script>window.alert('Error # w03: Ciclo Foreach vacio');</script>";
				break;	
			case 'W04': 
				$alert ="<script>window.alert('Error # w04: Objeto no encontrado');</script>";
				break;		
			case 'W05': 
				$alert ="<script>window.alert('Error # w05: Falta parametro a Funcion');</script>";
				break;				

            case '6000': 
				$alert ="<script>window.alert('Error # 6000: La clase no existe');</script>";
				break;

			default:
				// $alert ="<script>window.alert('Error # $code: NO AGREGADO $error');</script>";
			      break;


		}
        //header("Refresh:0; url=$url");
		echo $alert;


	}
}
/*
class Vars{

	var $vars = array();

	function initial(){}

}
*/


class _Logs{
    var $vars = array();
    function initial(){}
    function update($specific = NULL){
         db_connect("master");
       return update($this, "logs", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "logs");
    }
    function delete(){
          db_connect("master");
       delete("logs", $this->vars["id"]);
    }
}


class _Categorias{
    var $vars = array();
    function initial(){}
    function update($specific = NULL){
         db_connect("master");
       return update($this, "categorias", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "categorias");
    }
    function delete(){
          db_connect("master");
       delete("categorias", $this->vars["id"]);
    }
}

class _Temas{
    var $vars = array();
    function initial(){
        $this->vars['info'] = get_user($this->vars['creador']);
        $this->vars['respuestas'] = get_responses($this->vars['id']);
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "temas", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "temas");
    }
    function delete(){
          db_connect("master");
       delete("temas", $this->vars["id"]);
    }
}



class _Temas_Active{
    var $vars = array();
    function initial(){
        //print_r($this);
        $this->vars['info'] = get_user($this->vars['creador']);
        $this->vars['views'] = get_total_views($this->vars['id']);
        $this->vars['responses'] = get_total_responses($this->vars['id']);
        if($this->vars['responses']['responses'] > 0){
         $this->vars['last_response'] = get_last_responses_time($this->vars['id']);
         if ($this->vars['last_response']['fecha'] == "") { $fecha = $this->vars['fecha']; } else { $fecha = $this->vars['last_response']['fecha']; }
        } else {   $fecha = $this->vars['fecha']; }
        $this->vars['last_update'] = calcularDiferencia($fecha);
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "temas", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "temas");
    }
    function delete(){
          db_connect("master");
       delete("temas", $this->vars["id"]);
    }
}


class _Respuestas{
    var $vars = array();
    function initial(){
        $this->vars['info'] = get_user($this->vars['id_user']);
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "respuestas", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "respuestas");
    }
    function delete(){
          db_connect("master");
       delete("respuestas", $this->vars["id"]);
    }
}


class _Adjuntos{
    var $vars = array();
    function initial(){
       
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "adjuntos", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "adjuntos");
    }
    function delete(){
          db_connect("master");
       delete("adjuntos", $this->vars["id"]);
    }
}


class _Categorias_Servicios{
    var $vars = array();
    function initial(){}
    function update($specific = NULL){
         db_connect("master");
       return update($this, "categorias_servicios", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "categorias_servicios");
        return  $this->vars["id"];
    }
    function delete(){
          db_connect("master");
       delete("categorias_servicios", $this->vars["id"]);
    }
}

class _Servicios{
    var $vars = array();
    function initial(){
        $this->vars['info'] = get_user($this->vars['creador']);
        $this->vars['respuestas'] = get_responses_services($this->vars['id']);
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "servicios", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "servicios");
    }
    function delete(){
          db_connect("master");
       delete("servicios", $this->vars["id"]);
    }
}






class _Respuestas_Servicios{
    var $vars = array();
    function initial(){
        $this->vars['info'] = get_user($this->vars['id_user']);
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "servicios_respuestas", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "servicios_respuestas");
    }
    function delete(){
          db_connect("master");
       delete("servicios_rservicios_respuestas", $this->vars["id"]);
    }
}


class _Adjuntos_Servicios{
    var $vars = array();
    function initial(){
       
    }
    function update($specific = NULL){
         db_connect("master");
       return update($this, "adjuntos_servicios", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "adjuntos_servicios");
    }
    function delete(){
          db_connect("master");
       delete("adjuntos_servicios", $this->vars["id"]);
    }
}


class _Views{
    var $vars = array();
    function initial(){}
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "temas_vistos");
    }
    
}


class _Likes{
    var $vars = array();
    function initial(){}
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "temas_likes");
    }
    
}



class _RLikes{
    var $vars = array();
    function initial(){}
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "respuestas_likes");
    }
    
}


class _telegram{
    var $vars = array();
   // function _construct($pvars = array()){$this->vars = $pvars;}
    function initial(){}
    function update($specific = NULL){
        db_connect("master"); 
       return update($this, "telegram", $specific);
    }
    function insert(){
        db_connect("master"); 
       $this->vars["id"] = insert_test($this, "telegram");
       return $this->vars["id"];
    }
    function delete(){
        db_connect("master"); 
       delete("telegram", $this->vars["id"]);
    }
    
}


class _Usuarios{
    var $vars = array();
    function initial(){}
    function update($specific = NULL){
         db_connect("master");
       return update($this, "usuarios", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "usuarios");
    }
    function delete(){
          db_connect("master");
       delete("usuarios", $this->vars["id"]);
    }
}





class _Encuestas{
    var $vars = array();
    function initial(){}
    function update($specific = NULL){
         db_connect("master");
       return update($this, "encuesta", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "encuesta");
    }
    function delete(){
          db_connect("master");
       delete("encuesta", $this->vars["id"]);
    }
}



class _Respuesta_Encuestas{
    var $vars = array();
    function initial(){}
    function update($specific = NULL){
         db_connect("master");
       return update($this, "respuestas_encuesta", $specific);
    }
    function insert(){
          db_connect("master");
        $this->vars["id"] = insert($this, "respuestas_encuesta");
    }
    function delete(){
          db_connect("master");
       delete("respuestas_encuesta", $this->vars["id"]);
    }
}











?>