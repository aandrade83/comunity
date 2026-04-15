<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/VV/utilities/db/connection.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/VV/utilities/db/manager.php');



function get_master_login($user,$pass){

	db_connect("master");
	$sql = "SELECT * FROM usuarios WHERE filial = '".$user."' AND pass = '".$pass."'";
	//appendToLog($sql);
	return get($sql,'_Usuarios',true); 
}


function get_user($id){

	db_connect("master");
	$sql = "SELECT * FROM usuarios WHERE id = '".$id."'";
	
	return get($sql,'_Usuarios',true); 
}



function get_all_categorias(){
	db_connect("master");
	$sql = "SELECT * FROM categorias ";
	return get($sql,'_Categorias',false,'id'); 
}



function get_encuestas($act){
	db_connect("master");
	$sql = "SELECT * FROM encuesta WHERE estado = $act ";
	return get($sql,'_Encuestas',false,'id'); 
}


function get_resp_encuesta_user($user,$enc){
	db_connect("master");
	$sql = "SELECT * FROM respuestas_encuesta WHERE id_user = $user AND id_encuesta = $enc " ;
	return get_str($sql,true); 
}

function get_resp_encuesta($enc){
	db_connect("master");
	$sql = "SELECT detalle FROM respuestas_encuesta WHERE id_encuesta = $enc" ;
	return get_str($sql); 
}



function get_all_categorias_servicios(){
	db_connect("master");
	$sql = "SELECT * FROM categorias_servicios ";
	return get($sql,'_Categorias_Servicios',false,'id'); 
}



function get_pending_topics(){
	db_connect("master");
	$sql = "SELECT * FROM temas where estado = 0";
	return get($sql,'_Temas'); 
}


function get_declined_topics(){
	db_connect("master");
	$sql = "SELECT * FROM temas where estado = 3 order by fecha DESC LIMIT 5 ";
	return get_str($sql); 
}


function get_active_topics($search = "",$categoria = ""){
	db_connect("master");

	$sql_search	= "";
	$sql_categoria	= "";
	if($categoria != "") { $sql_categoria = " AND id_categoria	= $categoria"; }
	if($search != "") { $sql_search = " AND (titulo like ('%".$search."%') or detalle like ('%".$search."%') )"; }
	$sql = "SELECT * FROM temas where estado = 1 $sql_search $sql_categoria Order by Fecha Desc";
	
	return get($sql,'_Temas_Active'); 
}

function get_pending_topics_total(){
	db_connect("master");
	$sql = "SELECT count(*) as 'total' FROM temas where estado = 0";
	
	return get_str($sql,true); 
}


function get_responses($id){
	db_connect("master");
	$sql = "SELECT *  FROM respuestas where id_tema = $id";
	
	return get($sql,"_Respuestas"); 
}

function get_responses_services($id){
	db_connect("master");
	$sql = "SELECT *  FROM servicios_respuestas where id_tema = $id";
	
	return get($sql,"_Respuestas_Servicios"); 
}



function get_topic($id){
	db_connect("master");
	$sql = "SELECT * FROM temas where id = $id";
	return get($sql,'_Temas',true); 
}




function get_servicio($id){
	db_connect("master");
	$sql = "SELECT * FROM servicios where id = $id";
	return get($sql,'_Servicios',true); 
}

function get_servicios_special($cat = "",$tipo = ""){
	db_connect("master");
	$sql_cat = "";
	if($cat != "") { $sql_cat =  " AND id_categoria = $cat ";}
	$sql_tipo = "";
	if($tipo != "") { $sql_tipo =  " AND tipo = $tipo ";};
	$sql = "SELECT * FROM servicios WHERE 1 $sql_cat $sql_tipo ";
	return get($sql,'_Servicios'); 
}

function get_views_tema_user($tema,$user){
	db_connect("master");
	$sql = "SELECT id_user FROM temas_vistos where id_tema = $tema and id_user =$user ";
	return get($sql,'_Views',true); 
}



function get_total_views($tema){
	db_connect("master");
	$sql = "SELECT count(*) as 'views' FROM temas_vistos where id_tema = $tema";
	return get_str($sql,true); 
}

function get_total_responses($id){
	db_connect("master");
	$sql = "SELECT count(*) as 'responses'  FROM respuestas where id_tema = $id";
	return get_str($sql,true); 
}



function get_last_responses_time($id){
	db_connect("master");
	$sql = "SELECT fecha FROM respuestas where id_tema = $id ORDER BY fecha DESC LIMIT 1";
	return get_str($sql,true); 
}

function get_total_categoria(){
	db_connect("master");
	$sql = "SELECT count(*) as 'total', id_categoria FROM temas WHERE estado = 1 GROUP BY id_categoria";
	return get_str($sql,false,'id_categoria'); 
}

function get_total_tema_likes($tema){
	db_connect("master");
	$sql = "SELECT count(*) as 'total' FROM temas_likes WHERE id_tema = $tema and likes = 1";
	return get_str($sql,true); 
}


function get_total_tema_unlikes($tema){
	db_connect("master");
	$sql = "SELECT count(*) as total  FROM temas_likes WHERE id_tema = $tema and likes = 0";
	return get_str($sql,true); 
}

function get_users_tema_likes($tema){
	db_connect("master");
	$sql = "SELECT id_user as 'users' FROM `temas_likes` WHERE id_tema = $tema";
  return get_str($sql); 
}

function get_users_list_tema_likes($tema,$likes = '-1'){
	db_connect("master");
	$sql_likes	= "";
	if($likes != "-1") { $sql_likes = " AND likes	= $likes"; }	
    $sql =  "SELECT GROUP_CONCAT(id_user ORDER BY id_user SEPARATOR ',') AS 'usuarios' FROM temas_likes WHERE id_tema = $tema $sql_likes;";
    return get_str($sql,true); 
}

function get_total_respuesta_likes($respuesta){
	db_connect("master");
	$sql = "SELECT count(*) as 'total' FROM respuestas_likes WHERE id_respuesta = $respuesta and likes = 1";
	return get_str($sql,true); 
}


function get_total_respuesta_unlikes($respuesta){
	db_connect("master");
	$sql = "SELECT count(*) as total  FROM respuestas_likes WHERE id_respuesta = $respuesta and likes = 0";
	return get_str($sql,true); 
}

function get_users_respuesta_likes($respuesta){
	db_connect("master");
	$sql = "SELECT id_user as 'users' FROM `respuestas_likes` WHERE id_respuesta = $respuesta";
  return get_str($sql); 
}

function get_users_list_respuesta_likes($respuesta,$likes = '-1'){
	db_connect("master");
	$sql_likes	= "";
	if($likes != "-1") { $sql_likes = " AND likes	= $likes"; }	
    $sql =  "SELECT GROUP_CONCAT(id_user ORDER BY id_user SEPARATOR ',') AS 'usuarios' FROM respuestas_likes WHERE id_respuesta = $respuesta $sql_likes;";
    return get_str($sql,true); 
}


function get_active_user_temas($user){
 db_connect("master");
 $sql = "SELECT * FROM temas t WHERE estado = 1 and ($user in (select DISTINCT r.id_user from respuestas r where r.id_tema = t.id) OR t.creador = $user) order by fecha DESC  LIMIT 10";
 return get_str($sql); 
}

function get_adjuntos_tema($tema,$type){

	db_connect("master");
	$sql = "SELECT * FROM adjuntos WHERE entidad_id = '".$tema."' AND tipo_entidad = '".$type."'";
	//echo $sql;
	return get($sql,'_Adjuntos');
}


function get_active_servicios($search = "", $categoria = "", $tipo = "") {
	db_connect("master");
	$sql_search    = "";
	$sql_categoria = "";
	$sql_tipo      = "";
	if ($categoria != "") { $sql_categoria = " AND id_categoria = " . (int)$categoria; }
	if ($search    != "") { $sql_search    = " AND (titulo LIKE ('%" . $search . "%') OR detalle LIKE ('%" . $search . "%'))"; }
	if ($tipo      != "") { $sql_tipo      = " AND tipo = '" . $tipo . "'"; }
	$sql = "SELECT * FROM servicios WHERE estado = 1 $sql_search $sql_categoria $sql_tipo ORDER BY fecha DESC";
	return get($sql, '_Servicios');
}

function get_pending_servicios() {
	db_connect("master");
	$sql = "SELECT * FROM servicios WHERE estado = 0";
	return get($sql, '_Servicios');
}

function get_adjuntos_servicio($id, $type) {
	db_connect("master");
	$sql = "SELECT * FROM adjuntos_servicios WHERE entidad_id = '" . $id . "' AND tipo_entidad = '" . $type . "'";
	return get($sql, '_Adjuntos_Servicios');
}

function get_adjunto_servicio_by_id($adj_id) {
	db_connect("master");
	$sql = "SELECT * FROM adjuntos_servicios WHERE id = " . (int)$adj_id;
	return get($sql, '_Adjuntos_Servicios', true);
}


function get_total_servicio_likes($id) {
	db_connect("master");
	$sql = "SELECT count(*) as 'total' FROM servicios_likes WHERE id_tema = $id AND likes = 1";
	return get_str($sql, true);
}

function get_total_servicio_unlikes($id) {
	db_connect("master");
	$sql = "SELECT count(*) as total FROM servicios_likes WHERE id_tema = $id AND likes = 0";
	return get_str($sql, true);
}

function get_users_list_servicio_likes($id, $likes = '-1') {
	db_connect("master");
	$sql_likes = ($likes != "-1") ? " AND likes = $likes" : "";
	$sql = "SELECT GROUP_CONCAT(id_user ORDER BY id_user SEPARATOR ',') AS 'usuarios' FROM servicios_likes WHERE id_tema = $id $sql_likes";
	return get_str($sql, true);
}

function get_total_respuesta_servicio_likes($id) {
	db_connect("master");
	$sql = "SELECT count(*) as 'total' FROM respuestas_servicios_likes WHERE id_respuesta = $id AND likes = 1";
	return get_str($sql, true);
}

function get_total_respuesta_servicio_unlikes($id) {
	db_connect("master");
	$sql = "SELECT count(*) as total FROM respuestas_servicios_likes WHERE id_respuesta = $id AND likes = 0";
	return get_str($sql, true);
}

function get_users_list_respuesta_servicio_likes($id, $likes = '-1') {
	db_connect("master");
	$sql_likes = ($likes != "-1") ? " AND likes = $likes" : "";
	$sql = "SELECT GROUP_CONCAT(id_user ORDER BY id_user SEPARATOR ',') AS 'usuarios' FROM respuestas_servicios_likes WHERE id_respuesta = $id $sql_likes";
	return get_str($sql, true);
}



function get_telegram_login($filial,$phone){

	db_connect("master");
	$sql = "SELECT * FROM telegram WHERE filial = '".$filial."' AND phone_id = '".$phone."'";
	//echo $sql;
	return get($sql,'_telegram',true); 
}


function get_telegram_users($type = ""){
	db_connect("master");
	if($type != ""){ $sql_type = " AND type = $type"; }
	$sql = "SELECT * FROM telegram WHERE 1 $sql_type";
	return get($sql,'_telegram'); 
}

function get_telegram_users_by_filial($filial){
	db_connect("master");
	$sql = "SELECT * FROM telegram WHERE filial = $filial";
	return get($sql,'_telegram'); 
}

?>

