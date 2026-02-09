<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Config.php";
class DBi {
    public static $errors;
    public static $error;
    public static $errno;
    public static $insert_id;
    public static $affected_rows;
    public static $query_info;
    public static $num_rows;
    public static $warning_count;
    public static $warnings;
    public static $countReads=0;
    public static $countWrites=0;

    public static ?\mysqli $conn=null;
    public static array $connections = [];
    private static array $refCounts = [];
    private static array $lastTest = [];
    private static $mailObj;
    //public static $spDtFmt;

    private $map = array("%d" => "i", "%f" => "d", "%s" => "s");
    private static function sendAlertMail(string $message, $subject="") {
        global $hasUser,$user; //$mail_monitor;
        $mail_monitor=Config::get("mail","monitor")??"";
        if (!isset($mail_monitor[0])) { return; }// log error: no hay mail monitor
        if (empty(self::$mailObj)) {
            require_once "clases/Correo.php";
            self::$mailObj = new Correo();
            self::$mailObj->addAddress($mail_monitor);
        }
        echo "<!-- Mail Monitor : $mail_monitor -->";
        if (!isset($subject[0])) $subject="Alerta de Base de Datos";
        echo "<!-- Mail Subject : $subject -->";
        if ($hasUser) {
            $message.="<p>Usuario: ".json_encode($user)."</p>";
        } else {
            $message.="<p>Sin usuario</p>";
        }
        try {
            self::$mailObj->setSubject($subject);
            self::$mailObj->setBody($message);
            if (!self::$mailObj->send()) {
                doclog("SEND ERROR: ".self::$mailObj->getErrorInfo(),"mail");
                doclog("MSG: $message","mail");
            }
        } catch (Exception $ex) {
            doclog("EXCEPTION: ".json_encode(getErrorData($ex)),"mail");
            doclog("MSG: $message","mail");
        }
    }
    function __destruct() {
        foreach (self::$connections as $key=>$conn) {
            try {
                $conn->close();
            } catch (Exception $ex) {
            }
        }
        //self::$conn=null;
        //self::$connections = [];
        //self::$refCounts = [];
        //self::$lastTest = [];
    }
    public static function connect(?string $key = null): ?string {
        global $_pryNm;
        $db = Config::get("db");
        if (!($db["enable"]??false)) {
            doclog("DB DISABLED","error",["db"=>"$db[enable]", "trace"=>generateArrayTrace()]);
            return null;
        }
        if (!$key) $key=$_pryNm;
        // If already connected, reuse
        if (isset(self::$connections[$key]) && self::$connections[$key] instanceof \mysqli) { 
            self::$refCounts[$key]++;
            return $key; // self::$connections[$key];
        }
        $dbHost=$db["host"][$key]??"";
        $dbBase=$db["base"][$key]??"";
        $dbUsr=$db["user"][$key]??"";
        $dbPwd=$db["pass"][$key]??"";
        $dbSsl=$db["ssl"][$key]??true;

        if (!$dbHost) {
            doclog("NO DB HOST","error",["key"=>"$key","hosts"=>$db["host"], "trace"=>generateArrayTrace()]);
            return null;
        }
        try {
            if (!$dbSsl) {
                $conn = \mysqli_init();
                $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
                $conn->real_connect($dbHost, $dbUsr, $dbPwd, $dbBase, 3306, null, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
            } else $conn = new \mysqli($dbHost, $dbUsr, $dbPwd, $dbBase);
        } catch (Exception $ex) {
            doclog("ERROR: No se pudo crear mysqli","error",["key"=>$key,"dbUsr"=>"'$dbUsr'@'$dbHost'/'$dbBase'","ip"=>getIP(),"exception"=>getErrorData($ex)]);
            return null;
        }
        if ($conn->connect_error) {
            doclog("ERROR AL CREAR mysqli","error",["ip"=>getIP(),"connect_errno"=>$conn->connect_errno,"connect_error"=>$conn->connect_error, "trace"=>generateArrayTrace()]);
            $conn->close();
            return null;
        }
        if ($conn->character_set_name()!=="utf8mb4" && !$conn->set_charset("utf8mb4")) {
            doclog("Error al cambiar a charset utf8mb4","error",["ip"=>getIP(),"errno"=>$conn->errno,"error"=>$conn->error, "trace"=>generateArrayTrace()]);
            self::$conn->close();
            return null;
        }
        self::$connections[$key] = $conn;
        self::$refCounts[$key]   = 1;
        if ($key===$_pryNm) {
            self::$conn = $conn;
        }
        self::conlog("Connection created",["key"=>$key]);
        return $key;
    }
    public static function getCount($key=null): int {
        global $_pryNm;
        if (!$key) $key=$_pryNm;
        if (!isset(self::$refCounts[$key])) return 0;
        return self::$refCounts[$key];
    }
    public static function getConnection($connKey=null): ?\mysqli {
        if ($connKey && ($connKey instanceof \mysqli)) return $connKey;
        self::conlog("INI DBi::getConnection", ["key"=>$connKey??"null", "type"=>$connKey?gettype($connKey):"null"]);
        if (!$connKey) {
            self::conlog("END DBi::getConnection DEFAULT");
            return self::$conn;
        }
        if (isset(self::$connections[$connKey])) {
            $conn=self::$connections[$connKey];
            self::conlog("END DBi::getConnection MAP", ["key"=>$connKey, "type"=>gettype($connKey)]);
            return $conn;
        }
        self::conlog("END DBi::getConnection UNKNOWN",["key"=>$connKey, "type"=>gettype($connKey)]);
        return null;
    }
    public static function isConnected($connKey = null): bool {
        global $_now;
        $conn = self::getConnection($connKey);
        if (!is_null($conn) && ($conn === $connKey)) foreach (self::$connections as $key => $val) if ($conn === $val) {
            $connKey = $key;
            break;
        }
        if (isset($GLOBALS['_now']) && isset($_now["now"]) && ($conn !== $connKey) && isset(self::$lastTest[$connKey]) && self::$lastTest[$connKey]===$_now["now"]) {
            return true;
        }
        self::conlog("INI DBi::isConnected", ["key"=>$connKey??"null", "type"=>$connKey?gettype($connKey):"null"]);
        $retVal = false;
        if ($conn instanceof \mysqli) {
            try {
                $result = @$conn->query("SELECT 1");
                if ($result===false) {
                    self::close($conn, true);
                } else {
                    $result->free();
                    $retVal=true;
                    if (isset($GLOBALS['_now']) && isset($_now["now"]) && ($conn !== $connKey)) {
                        self::$lastTest[$connKey] = $_now["now"];
                    }
                }
            } catch (\Throwable $e) {
                self::conlog("EXC DBi::isConnected", ["arg"=>$connKey, "conn"=>$conn, "connType"=>$conn?gettype($conn):null, "retVal"=>($retVal?"true":"false"), "exc"=>getErrorData($e)]);
                $retVal = false;
                // cualquier excepción indica que la conexión no es válida
            }
        }
        self::conlog("END DBi::isConnected",["arg"=>$connKey, "conn"=>$conn, "retVal"=>($retVal?"true":"false")]);
        return $retVal;
    }
    public static function close($connOrKey=null, $force=false) {
        self::conlog("INI DBi::close",["arg1"=>$connOrKey, "arg2"=>$force]);
        $key=null;
        if (!$connOrKey || $connOrKey===self::$conn) {
            global $_pryNm;
            $conn=self::$conn;
            $key=$_pryNm;
        } else if ($connOrKey instanceof \mysqli) {
            $conn = $connOrKey;
            foreach (self::$connections as $k=>$c) {
                if ($c===$conn) {
                    $key=$k;
                    break;
                }
            }
        } else {
            $conn=self::getConnection($connOrKey);
            if ($conn) $key=$connOrKey;
            else {
                doclog("END DBi::close: NO VALID CONNECTION","error",["ip"=>getIP(),"trace"=>generateArrayTrace()]);
                return;
            }
        }
        global $query;

        if ($conn instanceof \mysqli) {
            if ($key) {
                if (!isset(self::$refCounts[$key])) {
                    self::$refCounts[$key]=1;
                }
                self::$refCounts[$key]--;
            }
            if ($force||!$key||self::$refCounts[$key]<=0) {
                if ($key && isset(self::$lastTest[$key])) unset(self::$lastTest[$key]);
                $conn->close();
                if ($key) {
                    $lastCount=self::$refCounts[$key];
                    unset(self::$connections[$key],self::$refCounts[$key]);
                    if ($conn===self::$conn) self::$conn=null;
                    self::conlog("END DBi::close. Connection closed",["key" => $key, "refCount" => $lastCount, "lastQuery"=>$query, "forced"=>($force?"true":"false")]);
                } else doclog("END DBi::close. Unregistered connection closed", "connection", ["ip"=>getIP(), "conn"=>json_encode($conn), "lastQuery"=>$query, "forced"=>($force?"true":"false")]);
            } else self::conlog("END DBi::close. Connection still in use", ["key" => $key, "refCount" => self::$refCounts[$key], "lastQuery"=>$query]);
        } else if ($conn !== null) {
            if ($key)
                doclog("END DBi::close. Trying to close not-connection", "error", ["ip" => getIP(), "key" => $key, "refCount" => self::$refCounts[$key], "conn"=>json_encode($conn)]);
            else
                doclog("END DBi::close. Trying to close unregistered not-connection", "error", ["ip" => getIP(), "conn"=>json_encode($conn), "lastQuery"=>$query]);
        }
    }
    private static function conlog($message,$data=null) {
        static $depth=0;
        $pfx=substr($message, 0, 3);
        if ($pfx==="END") $depth--;
        $prompt="";
        for ($i=0; $i < $depth; $i++) $prompt.="  ";
        if ($pfx==="INI") $depth++;
        global $username;
        if (!$data) $data=["ip"=>getIP()];
        else if (is_array($data)) $data+=["ip"=>getIP()];
        else $data=["data"=>$data,"ip"=>getIP()];
        $data["conlogTrace"]=generateArrayTrace();
        $data["conlogDepth"]=$depth;
        if ($username!=="tareaPagos") doclog($prompt.$message,"connection",$data);
    }
    public static function autocommit($binval, $connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) {
            mysqli_autocommit($conn, $binval);
            self::conlog("Autocommit ".($binval?"ON":"OFF"));
        }
    }
    public static function isAutocommit($connOrKey=null) {
        $val=false;
        $conn=self::getConnection($connOrKey);
        if ($conn!==null) {
            $result=$conn->query("SELECT @@autocommit isAuto");
            if (is_object($result)) {
                $obj=$result->fetch_object();
                if ($obj) $val=$obj->isAuto?true:false;
                $result->close();
            }
        }
        return $val;
    }
    public static function commit($connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) {
            mysqli_commit($conn);
            self::conlog("Commit done");
        }
    }
    public static function rollback($connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) {
            mysqli_rollback($conn);
            self::conlog("Rollback done");
        }
    }
//    public static function freeResult($connOrKey=null) {
//        $conn=self::getConnection($connOrKey);
//        if (self::isConnected($conn)) {
//            $conn->free_result();
//        }
//    }
    public static function nextResult($connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) {
            $conn->next_result();
        }
    }
    public static function clearErrors() {
        self::$errors = array();
        self::$warnings = "";
        self::$warning_count = 0;
    }
    public static function getError($connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) {
            if ($conn->connect_error)
                return $conn->connect_error;
            return $conn->error;
        }
        return null;
    }
    public static function getErrno($connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) {
            if ($conn->connect_errno)
                return $conn->connect_errno;
            return $conn->errno;
        }
        return null;
    }
    private static function runReplicationQueries() {
        if (!self::isConnected() || !self::isConnected("replica"))
            return false;
        /** TEMPORALMENTE DESHABILITADA LA REPLICA DE QUERIES. EL PROBLEMA ES CON LOS ID AUTOGENERADOS QUE NO ESTAN INCLUIDOS EN LOS QUERIES **/
        if (true) return false; // TODO: reemplazar queries por referencias a tabla, id y referencia de insert, update o delete. Para cada caso se genere un query que considere todos los campos:
        // En los inserts q se copien todos los campos incluyendo id y modifiedtime
        // En los updates se corre el query tal cual... por el momento
        // En los deletes se corre el query tal cual también, aunque verificar q el query tenga el id o un campo unico. Lo importante es que no dependa de la fecha de modificacion (modifiedTime)
        if (false) {
            $connR=self::getConnection("replica");
        $result=self::$conn->query("SELECT id,query FROM replicationqueries where successTimes=0 order by id limit 10");
        if ($result) {
            while ($obj = $result->fetch_object()) {
                $rresult = $connR->query($obj->query);
                if ($rresult) {
                    self::$conn->query("UPDATE replicationqueries set successTimes=successTimes+1 where id=".$obj->id);
                } else {
                    $errnoR=$connR->errno;
                    $errorR=$connR->error;
                    self::$conn->query("UPDATE replicationqueries set failureTimes=failureTimes+1 where id=".$obj->id);
                    //self::sendAlertMail("<p>QUERY FALLIDO: ".$obj->query."</p><p>$errnoR : $errorR</p>","ERROR EN REPLICAR QUERY");
                    doclog("QUERY FALLIDO ($errnoR: $errorR): ".$obj->query,"replica");
                    break;
                }
                //$rresult->close();//$rresult es boolean
            }
            $result->close();
        }
        }
    }
    /** Solo se replican DML queries, siempre que no sea a las tabla Proceso, Logs, Trace o Replicationqueries  **/
    private static function queryReplica($qryStr) {
        //$queryRQ = "INSERT INTO replicationqueries (query) VALUES (\"".filter_var($qryStr, FILTER_SANITIZE_ADD_SLASHES)."\")";
        //doclog($qryStr,"replica");
        doclog($qryStr,"replica");
        //doclog($queryRQ,"replica");
        /*
            $connR=self::getConnection("replica");
        $result=self::$conn->query($queryRQ);
        if (!$result) {
            $errno = self::getErrno();
            $error = self::getError();
            if ($error!==null) doclog("ERROR $errno: $error","replica");
        }
        if ($connR!==null && self::isAutocommit($connR)) {
            self::runReplicationQueries();
        }
        */
    }
    private static $noLogTables=["logs","proceso","replicationqueries","trace"];
    public static function query($qryTxt, $tableObj=null, $connOrKey=null) { // tableObj requerido para replicar inserts
        self::conlog("INI DBi::query",["qryTxt"=>$qryTxt, "tableObj"=>$tableObj, "connOrKey"=>$connOrKey]);
        $conn=self::getConnection($connOrKey);
        $isLoggable=(!isset($tableObj) || !in_array($tableObj->tablename, self::$noLogTables));
        if (!self::isConnected($conn)) {
            if ($isLoggable) doclog("QUERY: ".trim($qryTxt)."\n        RESULT:NO CONNECTION","error");
            self::$errno = -1;
            self::$error = "No connection";
            self::$errors[self::$errno] = self::$error;
            self::conlog("END DBi::query",["qryTxt"=>$qryTxt,"retval"=>"false","error"=>"NO CONNECTION"]);
            return false;
        }
        $start_time=microtime(true);
        self::$insert_id=null;
        if (!isset(self::$countReads)) self::$countReads=0;
        if (!isset(self::$countWrites)) self::$countWrites=0;
        $qryUp=strtoupper($qryTxt);
        $qryPfx=substr($qryUp, 0,4);
        // SELECT, SHOW, DESCRIBE, INSERT, INSERT ON DUPLICATE, UPDATE, DELETE, CREATE, ALTER, CALL
        if($qryPfx==="SELE"||$qryPfx==="SHOW"||$qryPfx==="DESC") {
            self::$countReads++;
            $logkey="read";
        } else {
            if ($qryPfx==="INSE" && strpos($qryUp, "ON DUPLICATE KEY UPDATE")!==FALSE) $qryPfx="IDKU";
            self::$countWrites++;
            $logkey="";
        }
        //if ($isLoggable) $logText="QUERY: ".trim($qryTxt);
        $qdbtxt=$qryPfx;
//        try {
//            if (!isset($logkey[0])) { // insert, update, delete
//                $rl=lockFile("dblock.txt");
//            }
            $result = $conn->query($qryTxt);
            $currentErrno = self::getErrno($conn);
            $currentError = self::getError($conn);
            if ($result===true && isset($conn->insert_id) && $conn->insert_id>0) {
                $result=$conn->insert_id;
                self::$insert_id = $result;
            } else if (is_bool($result) && isset($conn->affected_rows)) {
                $affectedRows = $conn->affected_rows;
                if ((+$affectedRows)>0) { // INSERT FOR UPDATE regresa 1 en insert, 2 en update, -1 si no hay cambios
                    $result=true;         // INSERT ON DUPLICATE KEY UPDATE regresa 1 en insert, 2 en update, 
                } else {
                    $result=false;
                    //$affectedRows=0;
                }
                self::$affected_rows = $affectedRows;
            }
//            if (($rl??false)!==false) {
//                unlockFile("dblock.txt",$rl);
//            }
//        } catch (Exception $ex) {
//            doclog("QUERY: ".trim($qryTxt)."\n        EXCEPTION: ".$ex->getMessage(),"error");
//            return false;
//        }
        //$addQdb=true;
        if (is_object($result)) {
            $qdbtxt.=" OBJ";
            if (substr(strtoupper($qryTxt),0,20)==="SELECT count(1) FROM") {
                $qdbtxt.=" #".$result->fetch_row()[0];
                $result->data_seek(0);
            }
            if ($result->num_rows) {
                self::$num_rows = $result->num_rows;
                //if($addQdb)
                    $qdbtxt.=" NROW:".self::$num_rows;
            } else //if ($addQdb)
                $qdbtxt.=" NONROW";
        } else if (is_numeric($result)) {
            $qdbtxt.=" IID:".$result;
        } else if (is_bool($result)) {
            //if ($addQdb) {
                $qdbtxt.=($result?" TRUE":" FALSE");
            //}
        } else $qdbtxt.=" NOTYPE ".gettype($result);
        if (isset($affectedRows)) $qdbtxt.=" AFR:$affectedRows";
        if (isset($conn->info)) {
            self::$query_info = $conn->info;
        }
        self::$errno = $currentErrno;
        self::$error = $currentError;
        if (isset($conn->warning_count)) {
            self::$warning_count = $conn->warning_count;
            if (!empty($result2 = $conn->query("SHOW WARNINGS"))) {
                $row = $result2->fetch_row();
                if (isset($row[2])) self::$warnings = sprintf("%s (%d): %s\n", $row[0], $row[1], $row[2]);
                else if (isset($row[0])) self::$warnings = implode(" | ", $row);
                else self::$warnings = "";
                if (isset(self::$warnings[0])) $qdbtxt.=" WRN:".self::$warnings;
                $result2->close();
            } else self::$warnings = "";
        } else {
            self::$warning_count = 0;
            $qdbtxt.=" NOWRN";
        }
        if (empty($currentErrno)&&empty($currentError)) $qdbtxt.=" NOERR";
        else {
            $qdbtxt.=" ERN:".$currentErrno." ERR:".$currentError;
            self::$errors[$currentErrno] = $currentError;
        }
        //if (!empty(self::$errors)) clog3("DBi errors: ".json_encode(self::$errors));
        $qdbtxt.=" DUR:".(microtime(true)-$start_time)."s";
        if ($isLoggable) doclog("QUERY: ".trim($qryTxt)."\n        RESULT:$qdbtxt",$logkey);
        if (false && $result) {
            //VALIDAR Q SOLO SE LLAME LA FUNCION QUERYREPLICA CON UPDATES O DELETES
            if (isset($tableObj) && $isLoggable) {
                $qryCmd=strtoupper(substr($qryTxt,0,6)); // SELECT,INSERT,UPDATE,DELETE
                if ($qryCmd==="UPDATE"||$qryCmd==="DELETE") self::queryReplica($qryTxt);
                else if ($qryCmd==="INSERT" && (self::$insert_id??0)>0 && (self::$affected_rows??0)>0) {
                    //GENERAR NUEVO QUERY PARA REPLICA Q TENGA TODOS LOS CAMPOS  INCLUYENDO ID Y MODIFIEDTIME Y LLAMAR QUERYREPLICA
                    $rresult=$conn->query("SELECT * FROM ".$tableObj->tablename." WHERE id>=".self::$insert_id." ORDER BY id LIMIT ".self::$affected_rows);
                    if ($rresult) {
                        while ($rreg = $rresult->fetch_assoc()) {
                            // ToDo: Corregir query para insertar los datos correctamente
                            $qryRpl="INSERT INTO $tableObj->tablename ".json_encode($rreg);
                            self::queryReplica($qryRpl);
                        }
                    }
                }
            }
        }
        self::conlog("END DBi::query",["qryTxt"=>$qryTxt,"retval"=>(is_null($result)?"null":(is_bool($result)?($result?"true":"false"):(is_array($result)?"array(".count($result).")":(is_string($result)?"'".(isset($result[101])?substr($result, 0, 49)."...".substr($result, -49):$result)."'":$result))))]);
        return $result;
    }
    /* Usage:
     // With Select:
     $params = array(1, "first");
     $stmt = self::prepare("SELECT * FROM table WHERE Id = %d OR Position = %s", $params);
     $stmt->execute();
     $result = $stmt->get_result();
     // With Insert, Update or Delete:
     $params = array(1, "first");
     $stmt = self::prepare("UPDATE table SET Id = %d AND Position = %s WHERE Code='one'", $params);
     $stmt->execute();
     */
    /*
    public static function prepare($query, &$params = array(), $connOrKey=null) {
        $conn=self::getConnection($connOrKey);

        self::connect($conn);
        
        $expr = "/(" . implode("|", array_keys($this->map)) . ")/";
        
        if (preg_match_all($expr, $query, $matches)) {
            $types = implode("", $matches[0]);
            $types = strtr($types, $this->map);
            
            $query = preg_replace($expr, "?", $query);
            
            if ($stmt = $conn->prepare($query)) {
                array_unshift($params, $types);
                
                if (call_user_func_array(array($stmt, "bind_param"), & $params)) {
                    return $stmt;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return conn->prepare($query);
        }
    }
     */
    public static function getLastId($connOrKey=null) {
        $conn=self::getConnection($connOrKey);
        if (self::isConnected($conn)) return $conn->insert_id;
        return -1;
    }
    public static function params2Where($params,$equalList=[],$emptyIsNull=false) {
        $where="";
        foreach ($params as $pvalue=>$value) {
            $esExacto=in_array($pvalue, $equalList);
            if (isset($value) && $value!==null && $value!==false && isset($value[0])) {
                $value=str_replace("*","%",$value);
                if ($value[0]==="=") {
                    $esExacto=true;
                    $value=substr($value, 1);
                }
                $esNegativo=$value[0]==="!";
                if ($esNegativo) $value=substr($value, 1);
                if (strlen(str_replace(["%","\"","'"], "", $value))==0) {
                    $esExacto=true;
                    $value="";
                }
                if ($esExacto) {
                    if (strlen($where)>0) $where .= " AND ";
                    if (!isset($value[0])&&$emptyIsNull) {
                        if ($esNegativo) $where .= "$pvalue is not null";
                        else $where .= "$pvalue is null";
                    } else if ($esNegativo) {
                        $where .= "$pvalue!='$value'";
                    } else {
                        $where .= "$pvalue='$value'";
                    }
                } else {
                    if (strpos($value, "%")===false) $value = "%".$value."%";
                    if (strlen($where)>0) $where .= " AND ";
                    $where .= "$pvalue LIKE '" . $value . "'";
                }
            }
        }
        return $where;
    }
    public static function real_escape_string($value, $connOrKey=null) {
        // ToDo: Check for double single quotes '', avoid real escape and instead
        if (is_array($value)) doclog("real_escape_string expects string, array given","warning",["trace"=>debug_backtrace()]);
        else {
            $value=str_replace("´", "'", $value);
            $value=str_replace("\u00b4", "'", $value);
            $value=str_replace("''","\"",$value);
            $conn=self::getConnection($connOrKey);
            if (self::isConnected($conn)) return $conn->real_escape_string($value);
        }
        return $value;
//        return mysqli_real_escape_string ($conn, $value);
//          return $value;
    }
    public static function agregaLog($texto, $connOrKey=null) {
        $info = self::get_info_array(3);
        //if (!empty($usr->id)) $usrId = $usr->id;
        //if ($info['clas']=="Logs") self::$logObj->agrega($usrId, "Logs", self::getErrno($connOrKey).":".self::getError($connOrKey));
        //else {
            $section = "";
            if (!empty($info['file'])) $section .= $info['file'].": ";
            if (!empty($info['clas'])) $section .= $info['clas']."->";
            if (!empty($info['func'])) $section .= $info['func'];
            $section .= "#".$info['line'];
            $texto.="\r\n$section";
        //    self::$logObj->agrega($usrId, $section, $texto);
        //}
        doclog("LOG: $texto","dblog");
    }
    public static function get_info_array($depth) {
        if (!isset($depth)) $depth=0;
        $arr = [];
        $trace = debug_backtrace();
        if (isset($trace[2+$depth])) {
            $arr['file'] = $trace[1+$depth]['file'];
            $arr['clas'] = '';
            $arr['func'] = $trace[2+$depth]['function'];
            if ((substr($arr['func'], 0, 7) == 'include') || (substr($arr['func'], 0, 7) == 'require')) {
                $arr['func'] = '';
            }
            $arr['line'] = $trace[1+$depth]['line'];
        } else if (isset($trace[1+$depth])) {
            $arr['file'] = $trace[1+$depth]['file'];
            $arr['clas'] = '';
            $arr['func'] = '';
            $arr['line'] = $trace[1+$depth]['line'];
        }
        if (isset($trace[3+$depth]['class'])) {
            $arr['file'] = $trace[2+$depth]['file'];
            $arr['clas'] = $trace[3+$depth]['class'];
            $arr['func'] = $trace[3+$depth]['function'];
            $arr['line'] = $trace[2+$depth]['line'];
        } else if (isset($trace[2+$depth]['class'])) {
            $arr['file'] = $trace[1+$depth]['file'];
            $arr['clas'] = $trace[2+$depth]['class'];
            $arr['func'] = $trace[2+$depth]['function'];
            $arr['line'] = $trace[1+$depth]['line'];
        }

        if (isset($arr['file']) && $arr['file']!=="") {
            $idx = strpos($arr['file'], "PROY");
            if ($idx !== false)
                $arr['file'] = substr($arr['file'], $idx+5);
        }
        if (isset($arr['func']) && $arr['func']!=="" && substr($arr['func'],-2)!=="()") {
            $arr['func'] .= "()";
        }
        return $arr ;
    }
    public static function get_info() {
        $info = "";
        $info_num_rows = "".self::$num_rows;
        if (isset($info_num_rows[0])) $info .= "// Num rows      = $info_num_rows\n";
        $info_insert_id = "".self::$insert_id;
        if (isset($info_insert_id[0])) $info .= "// Inserted id   = $info_insert_id\n";
        $info_affected_rows = "".self::$affected_rows;
        if (isset($info_affected_rows[0])) $info .= "// Affected rows = $info_affected_rows\n";
        $info_info = "".self::$query_info;
        if (isset($info_info[0])) $info .= "// Info          = $info_info\n";
        $info_warning_count = "".self::$warning_count;
        if (isset($info_warning_count[0])) $info .= "// Warnings      = $info_warning_count\n";
        if (self::$warning_count && isset(self::$warnings) && isset(self::$warnings[0]))
        if (isset($info_warning_count[0])) $info .= "//          :    = ".self::$warnings."\n";
        return $info;
    }
    public static function get_caller_info() {
        $c = '';
        $file = '';
        $func = '';
        $class = '';
        $line = '';
        $trace = debug_backtrace();
        if (isset($trace[2])) {
            $file = $trace[1]['file'];
            $func = $trace[2]['function'];
            if ((substr($func, 0, 7) == 'include') || (substr($func, 0, 7) == 'require')) {
                $func = '';
            }
            $line = $trace[1]['line'];
        } else if (isset($trace[1])) {
            $file = $trace[1]['file'];
            $func = '';
            $line = $trace[1]['line'];
        }
        if (isset($trace[3]['class'])) {
            $file = $trace[2]['file'];
            $class = $trace[3]['class'];
            $func = $trace[3]['function'];
            $line = $trace[2]['line'];
        } else if (isset($trace[2]['class'])) {
            $file = $trace[1]['file'];
            $class = $trace[2]['class'];
            $func = $trace[2]['function'];
            $line = $trace[1]['line'];
        }

        if ($file != '') {
            $idx = strpos($file, "PROY");
            $file = substr($file, $idx+5, -4);
        }
            
        $c = $file . ": ";
        $c .= ($class != '') ? ":" . $class . "->" : "";
        $c .= ($func != '') ? $func . "(): " : "";
        $c .= $line;
        return $c ;
    }
    static $DBERROR = [
        1062 => [
            "pattern" => "/Duplicate entry '([^']*)' for key '([^']*)'/",
            "keySuffix" => [2=>"_UNIQUE"],
            "conditions" => [2=>"nombreInterno"],
            "results" => [2=>"El folio compuesto (año-RFC_folioFactura) '#1#' ya existe en el sistema y no puede reutilizarse"],
            "default" => "Entrada duplicada '#1#' para el campo '#2#'", 
        ]
    ];
    public static function getErrorTranslated($errno, $errmsg, $defaultGlobal=null, $connOrKey=null) {
        if (!isset($errno)) {
            $errno = self::getErrno($connOrKey);
            $errmsg = self::getError($connOrKey);
        }
        if (isset(self::$DBERROR[$errno])) {
            $errData = self::$DBERROR[$errno];
            preg_match($errData["pattern"], $errmsg, $matches);
            if (isset($errData["keySuffix"])) $keySuffix = $errData["keySuffix"];
            if (isset($errData["conditions"])) $conditions = $errData["conditions"];
            if (isset($errData["results"])) $results = $errData["results"];
            if (isset($errData["default"])) $default = $errData["default"];
            
            for ($i=1;isset($matches[$i]);$i++) {
                if (isset($keySuffix) && isset($keySuffix[$i])) {
                    $suffixLen = strlen($keySuffix[$i]);
                    if (substr($matches[$i],-$suffixLen)===$keySuffix[$i]) $matches[$i]=substr($matches[$i],0,-$suffixLen);
                }
                if (isset($conditions) && isset($conditions[$i]) && $matches[$i]===$conditions[$i] && isset($results) && isset($results[$i])) {
                    $retval = $results[$i];
                    break;
                }
            }
            if (!isset($retval) && isset($default)) $retval = $default;
            if (isset($retval)) for($m=1; isset($matches[$m]); $m++) $retval = str_replace("#$m#", $matches[$m], $retval);
        }
        if (!isset($retval) && $errno>0) {
            if (!isset($defaultGlobal)) $defaultGlobal = "No pudo realizarse el registro.";
            $retval = $defaultGlobal;
        }
        return $retval??"";
    }
}
