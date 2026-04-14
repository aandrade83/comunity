<?php
class conn_db{
	var $databases = array();
	var $sql_server_connector = NULL;
	var $mysqli_connector = NULL;
	function __construct(){
		//Example: $this->databases["id name"] = new database(host,user,pass,name);
		$this->databases["master"]   = new database("localhost","sql_automover_spaa","5h7dD5TTCZRDfG4F11","sql_automover_sp1");	
		//SQL server 1
		$this->databases["dgs"]   = new database("192.168.10.411","s1bo","S0urs3C0nnect0r!","DGSDATA",true);
		$this->databases["dgs_reports"]        = new database("192.168.10.411","sbo","S0urs3C0nnect0r!111","DGSDATA",true);//new 
	
	}

	function connect($id){

		global $__dgs_reports_replacement;
		
		if($id == "dgs_reports" && $__dgs_reports_replacement != ""){
			$id = $__dgs_reports_replacement;
		}
	
		$db = $this->databases[$id];

    	if($db->sqlServer){
			$this->sqlServer_conection_process($db->host,$db->user,$db->pass,$db->name);
		}else{			
			$check_db = $this->conection_process($db->host,$db->user,$db->pass,$db->name);
		}
	    return $check_db;	
	}
	
	
	
	function sqlServer_conection_process($dbhost, $dbuser, $dbpass, $dbname) {
		$connectionInfo = array( "UID"=>$dbuser,                              
								 "PWD"=>$dbpass,                              
								 "Database"=>$dbname,
								 "ReturnDatesAsStrings"=> true);   
		$this->sql_server_connector = sqlsrv_connect($dbhost, $connectionInfo);
		if(!$this->sql_server_connector){
			echo "Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}
		
	}
	function close_connection() {
		if(!is_null($this->sql_server_connector)){
			sqlsrv_close($this->sql_server_connector); 
		}
	}	
	
	
	function conection_process($dbhost, $dbuser, $dbpass, $dbname) {
	  global $mysqli;	
	  $check = true;	
	  //$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	  $this->mysqli_connector = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

        // Check connection
      if ($this->mysqli_connector->connect_error) {
      	    insert_error_log("MySQL","Access denied for user $dbuser@$dbhost");
      	   $check = false;  
           die("Connection failed: " . $this->mysqli_connector->connect_error);
      }


	   if ($this->mysqli_connector->connect_error) {
	  	     insert_error_log("MySQL",$this->mysqli_connector->connect_error);
    		die("Connection failed: " . $this->mysqli_connector->connect_error);
    		return $check; 
		    exit();

	  } else {
    	echo "Connected successfully";
	  }
		
	   
	  return $check;   
	   
	}	
	
	
	
}

class database{
	var $host, $user, $pass, $name, $sqlServer;
	function __construct($phost, $puser, $ppass, $pname, $psqlServer = false){
		$this->host = $phost;
		$this->user = $puser;
		$this->pass = $ppass;
		$this->name = $pname;
		$this->sqlServer = $psqlServer;
	}
}
?>