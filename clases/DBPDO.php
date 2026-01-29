<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Config.php";
class DBPDO {
    private static $lock=false;
    private static $conn=[];
    private static $stt=null;
    private static $corp=[];
    public static $query="";
    public static $lastFetched=null;
    public static $lastCount=0;

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

    private static function getConnection($dbKey) {
        $db = Config::get("db");
        if (!($db["enable"]??false)) {
            doclog("DB DISABLED in PDO","error",["db"=>"$db[enable]"]);
            return null;
        }
        if (!isset(self::$conn[$dbKey])) try {
            // global $sqlsrv_dsn, $sqlsrv_username, $sqlsrv_password;
            $dbHost=$db["host"][$dbKey]??"";
            if (!isset($dbHost[0])) {
                doclog("NO DB HOST in PDO","error",["hosts"=>$db["host"],"dbKey"=>"$dbKey"]);
                return null;
            }
            $dbBase=$db["base"][$dbKey]??"";
            $dbDrv=$db["driver"][$dbKey]??"";
            $dbIns=$db["instance"][$dbKey]??"";
            $dbUsr=$db["user"][$dbKey]??"";
            $dbPwd=$db["pass"][$dbKey]??"";
            if (isset($dbIns[0])) $dbIns="/$dbIns";
            $dbDsn="$dbDrv:Server=$dbHost{$dbIns};Database=$dbBase";
            self::$conn[$dbKey] = new PDO($dbDsn, $dbUsr, $dbPwd);
            self::$conn[$dbKey]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $pe) {
            doclog("Error PDO en la conexión PDO a SQL Server","error",["error"=>getErrorData($pe)]);
            unset(self::$conn[$dbKey]);
        } catch (Error $e) {
            doclog("Error en la conexión PDO a SQL Server","error",["error"=>getErrorData($e)]);
            unset(self::$conn[$dbKey]);
        }
        return self::$conn[$dbKey]??null;
    }
    public static function isConnected($dbKey=null) {
        global $_pryNm;
        if (empty($dbKey)) $dbKey=$_pryNm;
        if (!isset($dbKey[0])) {
            doclog("Empty DB KEY","error",["db"=>"$db[enable]"]);
            return false;
        }
        return !empty(self::getConnection($dbKey));
    }
    public static function getCount($stt=null) {
        if (!isset($stt)) $stt=self::$stt;
        if (isset($stt)) {
            if (is_array($stt) || $stt instanceof Countable) {
                $count = count($stt);
                if ($stt===self::$stt) self::$lastCount = $count;
                return $count;
            //} else if (self::$stt instanceof Traversable) { // interfiere con fetch
            //    $num =  iterator_count(self::$stt);
            //    return $num;
            } else {
                doclog("Error resultado de query NO es NULL ni Array ni Countable","error",["stt"=>get_object_vars($stt),"class"=>get_class($stt)]);
            }
        }
        return null;
    }
    public static function fetch($stt=null, $PDOFlags=PDO::FETCH_ASSOC) {
        if (!isset($stt)) $stt=self::$stt;
        try {
            if (isset($stt)) {
                $fetched = $stt->fetch($PDOFlags);
                if ($stt===self::$stt) self::$lastFetched = $fetched;
                return $fetched;
            }
        } catch(PDOException $pe) {
            doclog("Error PDO en obtención de fila (fetch)","error",["error"=>getErrorData($pe)]);
        } catch (Error $e) {
            doclog("Error en obtención de fila (fetch)","error",["error"=>getErrorData($e)]);
        }
        return null;
    }
    public static function fetchColumn($stt=null, $colIdx=0) {
        if (!isset($stt)) $stt=self::$stt;
        try {
            if (isset($stt)) {
                $fetched = $stt->fetchColumn($colIdx);
                if ($stt===self::$stt) self::$lastFetched = $fetched;
                return $fetched;
            }
        } catch(PDOException $pe) {
            doclog("Error PDO en obtención de dato (fetchColumn $colIdx)","error",["error"=>getErrorData($pe)]);
        } catch (Error $e) {
            doclog("Error en obtención de fila (fetchColumn $colIdx)","error",["error"=>getErrorData($e)]);
        }
        return null;
    }
    public static function query($query,$dbKey=null) {
        global $_pryNm;
        if (empty($dbKey)) $dbKey=$_pryNm;
        if (!isset($dbKey[0])) {
            doclog("Empty DB KEY","error",["db"=>"$db[enable]"]);
            return null;
        }
        try {
            $pdo=self::getConnection($dbKey);
            if (isset($pdo)) {
                self::$stt=$pdo->query($query);
                self::$query=$query;
                return self::$stt;
            }
        } catch(PDOException $pe) {
            doclog("Error PDO en ejecución de query","error",["error"=>getErrorData($pe)]);
        } catch(Error $e) {
            doclog("Error en ejecución de query","error",["error"=>getErrorData($e)]);
        }
        return null;
    }
    public static function validaAceptacion($rfc, $fechaYMD, $monto) { // yyyy-mm-dd hh:ii:ss
        $codzon=self::getCodigoZona($rfc);
        $fecha=isset($fechaYMD[9])?substr($fechaYMD, 0, 4)."-".substr($fechaYMD, 8, 2)."-".substr($fechaYMD, 5,2):null;
        $query="SELECT count(*) AS n FROM view_Movimientos_Pagos where Forma = 'Monto de Credito' and Status = 'Activo'";
        if (isset($fecha[0])) $query.=" and fecha = '$fecha'";
        if (isset($monto[0])) $query.=" and Cargo = '$monto'";
        if (isset($codzon[0])) $query.=" and CodigoZona = '$codzon'";
        $lastStt=self::$stt;
        $lastQuery=self::$query;
        $lastFetched=self::$lastFetched;
        //$lastCount=self::$lastCount;
        $stt=DBPDO::query($query,"conpro");
        $row=DBPDO::fetch($stt);
        doclog("VALIDA ACEPTACION","pagos",["rfc"=>$rfc,"codigoZona"=>$codzon,"fechaYMD"=>$fechaYMD,"fecha"=>$fecha,"monto"=>$monto,"query"=>$query,"result"=>$row]);
        return (isset($row) && $row["n"]==1);
    }
    private static function getCodigoZona($rfc) {
        if (!isset($rfc[0])) return null;
        if (!isset(self::$corp[$rfc])) {
            global $gpoObj;
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj = new Grupo();
            }
            $gpoData = $gpoObj->getData("rfc='$rfc'");
            if (isset($gpoData[0]["id"])) $gpoData=$gpoData[0];
            $alias=$gpoData["alias"];
            $codZn=null;
            switch($alias) {
                case "APSA": case "BIDASOA": case "DESA": case "FOAMYMEX": case "GLAMA": case "JYL": case "LAISA": case "MELO": case "MORYSAN": case "RGA": case "SKARTON": $codZn=$alias[0].strtolower(substr($alias, 1)); break;
                case "COREPACK": $codZn="CorePack"; break;
                case "BIDARENA": $codZn="BidaArena"; break;
                case "CAPITALH": $codZn="Capital Hall"; break;
                case "SERVICIOS": $codZn="ServiCarton"; break;
                case "MARLOT": $codZn="Transportes"; break;
                //case "APEL": case "CASABLANCA": case "DANIEL": case "DEMO": case "ENVASES": case "ESMERALDA": case "FIDEICOMIS": case "FIDEMIFEL": case "GYL": case "JLA": 
            }
            self::$corp[$rfc]=["alias"=>$alias,"CodigoZona"=>$codZn];
        }
        return self::$corp[$rfc]["CodigoZona"];
    }
    private static function close() {
        if (isset(self::$conn)) foreach (self::$conn as $dbKey => $dbConn) {
            $dbConn->close();
        }
    }
    function __destruct() {
        DBPDO::close();
    }
}
?>