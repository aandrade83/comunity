<?php

 // Conection compatible with PHP 8 

class conn_db {
    public array $databases = [];
    //public ?object $sql_server_connector = null;
    var $sql_server_connector = NULL;
    public ?object $mysqli_connector = null;

    public function __construct() {
        // Example: $this->databases["id name"] = new database(host, user, pass, name);
        $this->databases["master"] = new database("db", "user", "password", "valleverde_db");
       
    }

    public function connect($id) {
      
        global $__dgs_reports_replacement;

        if ($id == "dgs_reports" && $__dgs_reports_replacement != "") {
            $id = $__dgs_reports_replacement;
        }

        $db = $this->databases[$id];

        if ($db->sqlServer) {
            $this->sqlServerConnectionProcess($db->host, $db->user, $db->pass, $db->name);
        } else {
            $checkDb = $this->connectionProcess($db->host, $db->user, $db->pass, $db->name);
        }

        return $checkDb;
    }

    public function sqlServerConnectionProcess($dbhost, $dbuser, $dbpass, $dbname) {
        $connectionInfo = [
            "UID" => $dbuser,
            "PWD" => $dbpass,
            "Database" => $dbname,
            "ReturnDatesAsStrings" => true,
        ];
        $this->sql_server_connector = sqlsrv_connect($dbhost, $connectionInfo);

        // Después de la llamada a sqlsrv_connect()
if ($this->sql_server_connector === false) {
    $errors = sqlsrv_errors();
    if ($errors !== null) {
        foreach ($errors as $error) {
            echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
            echo "Code: " . $error['code'] . "<br />";
            echo "Message: " . $error['message'] . "<br />";
        }
    } else {
        echo "Error desconocido al intentar conectar a SQL Server.<br />";
    }
}

        if (!$this->sql_server_connector) {
             $errors = sqlsrv_errors();
    if ($errors !== null) {
        foreach ($errors as $error) {
            echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
            echo "Code: " . $error['code'] . "<br />";
            echo "Message: " . $error['message'] . "<br />";
        }
    }
            echo "Connection could not be established.<br />";
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public function closeConnection() {
        if (!is_null($this->sql_server_connector)) {
            sqlsrv_close($this->sql_server_connector);
        }
    }

    public function connectionProcess($dbhost, $dbuser, $dbpass, $dbname) {
        global $mysqli;
        $check = true;

        $this->mysqli_connector = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
         
        // Check connection
        if ($this->mysqli_connector->connect_error) {
            insert_error_log("MySQL", "Access denied for user $dbuser@$dbhost");
            $check = false;
            die("Connection failed: " . $this->mysqli_connector->connect_error);
        }


          if (!$this->mysqli_connector->set_charset("utf8")) {
        insert_error_log("MySQL", "Error setting charset UTF-8: " . $this->mysqli_connector->error);
        die("Error setting charset UTF-8: " . $this->mysqli_connector->error);
          }

/*
        if ($this->mysqli_connector->connect_error) {
            insert_error_log("MySQL", $this->mysqli_connector->connect_error);
            die("Connection failed: " . $this->mysqli_connector->connect_error);
            return $check;
            exit();
        } else {
           // echo "Connected successfully";
        }

        */

        return $check;
    }
}

class database {
    public string $host, $user, $pass, $name;
    public bool $sqlServer;

    public function __construct($phost, $puser, $ppass, $pname, $psqlServer = false) {
        $this->host = $phost;
        $this->user = $puser;
        $this->pass = $ppass;
        $this->name = $pname;
        $this->sqlServer = $psqlServer;
    }
}

?>