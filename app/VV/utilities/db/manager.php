<?php
//predifined fetch constants
/*
define('MYSQL_BOTH',MYSQLI_BOTH);
define('MYSQL_NUM',MYSQLI_NUM);
define('MYSQL_ASSOC',MYSQLI_ASSOC);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_error_handler("warning_handler", E_WARNING ^ E_NOTICE);

*/

// Compatible with php 8

//Main DB Connection Object
$conn_db = new conn_db();

function execute($query) {
    global $conn_db;
    if (!is_null($conn_db->sql_server_connector)) {
        return execute_sql_server($query);
    } else {
        $good = true;
        $res = $conn_db->mysqli_connector->query($query . " #system query: " . curPageURL());
        insert_error_log("MySQL", $query, true);
        if (!$res) {
            $good = false;
            insert_error_log("MySQL", $conn_db->mysqli_connector->error);
        }
        return $good;
    }
}

function get_str($selections_sql, $unique = false, $index_field = "NO_FIELD") {
    global $conn_db;
    $has = true;
    if (!is_null($conn_db->sql_server_connector)) {
        return get_str_sql_server($selections_sql, $unique, $index_field);
    } else {
        $strs = array();
        $selections_sql_res = $conn_db->mysqli_connector->query($selections_sql . " #system query: " . curPageURL());
        insert_error_log("MySQL", $selections_sql, true);
        while ($selections_info = $selections_sql_res->fetch_assoc()) {
            $has = true;
            $vars = array_keys($selections_info);
            $str_array = array();
            foreach ($vars as $var) {
                $str_array[$var] = $selections_info[$var];
            }

            if ($index_field != "NO_FIELD") {
                $strs[$selections_info[$index_field]] = $str_array;
            } else {
                $strs[] = $str_array;
            }
        }
        if ($unique) {
            if (!$has) {
                $strs[0] = NULL;
            }

            $strs[0] = $strs[0] ?? null;
            return $strs[0];
        } else {
            return $strs;
        }
    }
}

function get($selections_sql, $type, $unique = false, $index_field = "NO_FIELD") {
    global $conn_db;
    if (!is_null($conn_db->sql_server_connector)) {
        return get_sql_server($selections_sql, $type, $unique, $index_field);
    } else {
        $objects = array();
        $has = false;
        $selections_sql_res = $conn_db->mysqli_connector->query($selections_sql . " #system query: " . curPageURL());
        insert_error_log("MySQL", $selections_sql, true);
        while ($selections_info = $selections_sql_res->fetch_assoc()) {
            $has = true;
            $vars = array_keys($selections_info);
           eval("\$object = new $type();");
           foreach ($vars as $var) {
                $object->vars[$var] = $selections_info[$var];
            }
            $object->initial();
            if ($index_field != "NO_FIELD") {
                if (is_object($object->vars[$index_field])) {
                    $objects[$object->vars[$index_field]->vars[$index_sub_field]] = $object;
                } else {
                    $objects[$object->vars[$index_field]] = $object;
                }
            } else {
                $objects[] = $object;
            }
        }
        if ($unique) {
            if (!$has) {
                $objects[0] = NULL;
            }
            return $objects[0];
        } else {
            return $objects;
        }
    }
}

function insert($object, $table, $print = false) {
    global $conn_db;
    if (is_object($object)) {
        $vars = $object->vars;
    } else {
        $vars = $object;
    }

    $insert_id = -1;
    $params = "";
    $values = "";
    if (count($vars) > 0) {
        foreach (array_keys($vars) as $key) {
            $params .= "`$key`, ";
        }
        foreach ($vars as $var) {
            if (is_string($var) || is_numeric($var)) {
                $values .= "'" . $conn_db->mysqli_connector->real_escape_string($var) . "', ";
            } else {
                $values .= "'" . $conn_db->mysqli_connector->real_escape_string($var->vars["id"]) . "', ";
            }
        }
        $params = substr($params, 0, strlen($params) - 2);
        $values = substr($values, 0, strlen($values) - 2);
        $update = "INSERT INTO $table ($params) VALUES ($values)";
        if ($print) {
            echo $update;
        }
        $res = $conn_db->mysqli_connector->query($update . " #system query: " . curPageURL());
        $insert_id = $conn_db->mysqli_connector->insert_id;
        insert_error_log("MySQL", $update, true);
    }
    return $insert_id;
}

function insert_test($object, $table, $print = false) {
    global $conn_db;
    if (is_object($object)) {
        $vars = $object->vars;
    } else {
        $vars = $object;
    }

    $insert_id = -1;
    $params = "";
    $values = "";
    if (count($vars) > 0) {
        foreach (array_keys($vars) as $key) {
            $params .= "`$key`, ";
        }
        foreach ($vars as $var) {
            if (is_string($var) || is_numeric($var)) {
                $values .= "'" . $conn_db->mysqli_connector->real_escape_string($var) . "', ";
            } else {
                $values .= "'" . $conn_db->mysqli_connector->real_escape_string($var->vars["id"]) . "', ";
            }
        }
        $params = substr($params, 0, strlen($params) - 2);
        $values = substr($values, 0, strlen($values) - 2);
        $update = "INSERT INTO $table ($params) VALUES ($values)";
       // appendToLog($update);
      // return $update;
        echo $update;
        if ($print) {
            echo $update;
        }
        $res = $conn_db->mysqli_connector->query($update . " #system query: " . curPageURL());
        $insert_id = $conn_db->mysqli_connector->insert_id;
        insert_error_log("MySQL", $update, true);
    }
    return $insert_id;
}

function update($object, $table, $specific = NULL) {
    global $conn_db;
    if (is_null($specific)) {
        $vars = $object->vars;
    } else {
        $vars = array();
        foreach ($specific as $sp_var) {
            $vars[$sp_var] = $object->vars[$sp_var];
        }
    }
    $keys = array_keys($vars);
    $good = true;
    $params = "";
    if (count($vars) > 0) {
        foreach ($vars as $key => $value) {
            if ($key != "id") {
                $params .= $key . " = '" . $conn_db->mysqli_connector->real_escape_string($value) . "', ";
            }
        }
        $params = substr($params, 0, strlen($params) - 2);
        $update = "UPDATE $table SET $params WHERE id = '" . $object->vars["id"] . "'";
        $res = $conn_db->mysqli_connector->query($update . " #system query: " . curPageURL());
        if (!$res) {
            $good = false;
            insert_error_log("MySQL", $conn_db->mysqli_connector->error);
        }
    } else {
        $good = false;
    }
    insert_error_log("MySQL", $update, true);
    return $good;
}

function update_test($object, $table, $specific = NULL) {
    global $conn_db;
    if (is_null($specific)) {
        $vars = $object->vars;
    } else {
        $vars = array();
        foreach ($specific as $sp_var) {
            $vars[$sp_var] = $object->vars[$sp_var];
        }
    }
    $keys = array_keys($vars);
    $good = true;
    $params = "";
    if (count($vars) > 0) {
        foreach ($vars as $key => $value) {
            if ($key != "id") {
                $params .= $key . " = '" . $conn_db->mysqli_connector->real_escape_string($value) . "', ";
            }
        }
        $params = substr($params, 0, strlen($params) - 2);
        $update = "UPDATE $table SET $params WHERE id = '" . $object->vars["id"] . "'";
        echo $update . "<BR>";
        $res = $conn_db->mysqli_connector->query($update . " #system query: " . curPageURL());
        if (!$res) {
            $good = false;
            insert_error_log("MySQL", $conn_db->mysqli_connector->error);
        }
    } else {
        $good = false;
    }
    insert_error_log("MySQL", $update, true);
    return $good;
}

function delete($table, $id, $where = "") {
    global $conn_db;
    if ($where == "") {
        $where = " id = '$id' ";
    }
    $good = true;
    $update = "DELETE FROM $table WHERE $where";
    $res = $conn_db->mysqli_connector->query($update . " #system query: " . curPageURL());
    if (!$res) {
        $good = false;
        insert_error_log("MySQL", $conn_db->mysqli_connector->error);
    }
    insert_error_log("MySQL", $update, true);
    return $good;
}

function get_sql_server($selections_sql, $type, $unique, $index_field = "NO_FIELD") {
    global $conn_db;
    $objects = array();
    $has = false;

    $stmt = sqlsrv_query($conn_db->sql_server_connector, $selections_sql);

    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            eval("\$object = new $type();");
            $has = true;
            $vars = array_keys($row);
            foreach ($vars as $var) {
                $object->vars[$var] = str_replace(".0000", "", $row[$var]);
            }

            if ($index_field != "NO_FIELD") {
                if (is_object($object->vars[$index_field])) {
                    $objects[$object->vars[$index_field]->vars[$index_sub_field]] = $object;
                } else {
                    $objects[$object->vars[$index_field]] = $object;
                }
            } else {
                $objects[] = $object;
            }
        }
    } else {
        insert_error_log("SQL SERVER", sqlsrv_errors());
    }

    @sqlsrv_free_stmt($stmt);
    $conn_db->close_connection();

    foreach ($objects as $object) {
        $object->initial();
    }

    if ($unique) {
        if (!$has) {
            $objects[0] = NULL;
        }
        return $objects[0];
    } else {
        return $objects;
    }
}

function get_str_sql_server($selections_sql, $unique, $index_field = "NO_FIELD") {
    global $conn_db;
    $strs = array();

    $stmt = sqlsrv_query($conn_db->sql_server_connector, $selections_sql);

    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $has = true;
            $vars = array_keys($row);
            $str_array = array();
            foreach ($vars as $var) {
                $str_array[$var] = str_replace(".0000", "", $row[$var]);
            }

            if ($index_field != "NO_FIELD") {
                $strs[str_replace(" 00:00:00.000", "", $row[$index_field])] = $str_array;
            } else {
                $strs[] = $str_array;
            }
        }
    } else {
        insert_error_log("SQL SERVER", sqlsrv_errors());
    }

    @sqlsrv_free_stmt($stmt);
    $conn_db->close_connection();

    if ($unique) {
        if (!$has) {
            $strs[0] = NULL;
        }
        return $strs[0];
    } else {
        return $strs;
    }
}

function execute_sql_server($query) {
    global $conn_db;
    $stmt = sqlsrv_query($conn_db->sql_server_connector, $query);

    if (!$stmt) {
        insert_error_log("SQL SERVER", sqlsrv_errors());
    }

    insert_error_log("MySQL", $query, true);
    return $stmt;
}
?>
