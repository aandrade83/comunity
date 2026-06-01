<? 

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
  $aa = "_en";

$condominos = get_master_test();
$users = get_users_Test();
echo "<pre>";
print_r($users);

/*
$pass = "ComiteVV";
echo $pass."<BR><BR>";
$pass = biencript($pass);
echo $pass;
?*/
foreach($condominos as $cd){

if (!isset($users[$cd['email']]->vars['id'])) {   
     print_r($cd); echo " NEW TO BE INSERTED";
     $user = new _Usuarios();
     $user->vars['nombre'] = $cd['nombre']." ".$cd['apellido'];
     $user->vars['correo'] = $cd['email'];
     $user->vars['filial'] = $cd['filial'];
     $correo = explode('@',$user->vars['correo']);
     $pass = biencript($correo[0]);
     $user->vars['pass'] = $pass;
     $user->vars['rol'] = 1;
     $user->vars['activo'] =1;
     echo "INSERTA  ".$user->vars['nombre']."<BR>";
     $user->insert();


   } else {
     $correo = explode('@',$users[$cd['email']]->vars['correo']);
     echo "Actualizar Correo ".$correo[0]."<BR>";
     $pass = biencript($correo[0]);
     $users[$cd['email']]->vars['pass']=$pass;
     $users[$cd['email']]->update(array('pass'));

   }

}


echo "<BR><BR><BR><BR>---------------";
/*
foreach($users as $user){
  if (!$condominos[$user['correo']]) {
    print_r($user);
  }
*/

function get_master_test(){

	db_connect("master");
	$sql = "SELECT * FROM condominos c WHERE c.id = ( SELECT MIN(id) FROM condominos WHERE email = c.email )";
	//appendToLog($sql);
	return get_str($sql,false,'email'); 
}

function get_users_Test(){

	db_connect("master");
	$sql = "SELECT * FROM usuarios";
	
	return get($sql,'_Usuarios',false, 'correo'); 
}




exit;
