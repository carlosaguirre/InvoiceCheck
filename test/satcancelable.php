<?php
error_reporting(E_ALL);
//echo "satcancelable\n";
date_default_timezone_set("America/Mexico_City");
$mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
$includePath = "C:\\PHP7\\includes\\";
$path = dirname(__FILE__);
$basePath = dirname($path)."\\";
$path.="\\";
require_once dirname(__DIR__)."/bootstrap.php";
require_once "configuracion\\cfdi.php";
class SCLOG {
    private static function basic($txt, $suffix="", $hasDatePrompt=true) {
        global $path;
        $dt=new DateTime();
        $year = +$dt->format("Y");
        $logFileName=$path."satcancelable{$suffix}.log";
        if (file_exists($logFileName)) {
            $trace="";
            $dateM=+date("Y-m-d H:i:s", filemtime($logFileName));
            $trace.="M:$dateM";
            $dateC=+date("Y-m-d H:i:s", filectime($logFileName));
            $trace.=", C:$dateC";
            $fileStats = stat($logFileName);
            $dateS = date('Y-m-d H:i:s', $fileStats['ctime']);
            $trace.=", S:$dateS";
            file_put_contents($path."satcancelable.trc.log",$trace."\r\n", FILE_APPEND | LOCK_EX);
            $fileYear = date('Y', $fileStats['ctime']);
            

            $maxSize = 5*1024*1024;
            $fileSize=filesize($logFileName);
            if ($fileYear<$year) {
                file_put_contents($path."satcancelable.trc.log","Exceeded Year: $year > $fileYear\r\n", FILE_APPEND | LOCK_EX);
                $newLogFileName=$path."satcancelable{$suffix}_{$fileYear}";
            } else if ($fileSize > $maxSize) {
                file_put_contents($path."satcancelable.trc.log","Exceeded Size: $fileSize > $maxSize\r\n", FILE_APPEND | LOCK_EX);
                $newLogFileName=$path."satcancelable{$suffix}_{$year}";
            }
            if (isset($newLogFileName)) {
                $fileList=glob($newLogFileName."*.log");
                if ($fileList===false) return;
                $n=count($fileList);
                if ($n>0) {
                    $n++;
                    $newLogFileName.="_{$n}";
                }
                rename($logFileName, $newLogFileName.".log");
            }
        }
        $prompt=$hasDatePrompt?"[".(new DateTime())->format("Y-m-d H:i:s")."] ":"";
        $txt=trim($txt);
        file_put_contents($logFileName,"{$prompt}$txt\r\n", FILE_APPEND | LOCK_EX);
    }
    public static function message($txt) {
        self::basic($txt);
    }
    public static function error($txt) {
        self::basic($txt, ".err");
    }
    public static function dot() {
        self::basic(".", "", false);
    }
}
class SCDB {
    const SCDB_RESULT = 0;
    const SCDB_FETCHASSOC = 1;
    const SCDB_FETCHROW =2;
    const SCDB_FETCHBOTH = 3;
    const SCDB_FETCHOBJ = 4;
    const SCDB_FETCHALL = 5;
    private static $conn=null;
    public static function connect() {
        global $bd_servidor, $bd_usuario, $bd_clave, $bd_base;
        if (self::$conn === null) {
            self::$conn = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
            if (self::$conn->connect_error) {
                SCLOG::error("Connect Error ".self::$conn->connect_errno.": ".self::$conn->connect_error);
                return false;
            }
        }
        return true;
    }
    private static function getResultData($res, $preMsg, $sql, $posMsg, $queryret) {
        if (isset($posMsg[0])) $posMsg=" | $posMsg";
        $posMsg=" $sql{$posMsg}";
        if ($res===false) {
            if (isset($preMsg[0])) $preMsg.=" ";
            if (empty(self::$conn->errno)) $preMsg.="EMPTY RESULT";
            else if (!isset($errMsg[0])) $preMsg.="QUERY ERROR";
            else $preMsg.="ERROR";
            SCLOG::error("$preMsg [".self::$conn->errno."|".self::$conn->error."]$posMsg");
            return false;
        }
        if ($res===true) {
            SCLOG::message("$preMsg SUCCESS!$posMsg");
            return true;
        }
        if (is_object($res)) {
            $num_rows=$res->num_rows;
            if ($num_rows==0) {
                // SCLOG::message("$preMsg EMPTY!$posMsg");
                return ["rows"=>0];
            }
            switch($queryret) {
                case self::SCDB_RESULT: $retVal=$res; break;
                case self::SCDB_FETCHASSOC: $retVal=$res->fetch_assoc(); $res->free(); break;
                case self::SCDB_FETCHROW: $retVal=$res->fetch_row(); $res->free(); break;
                case self::SCDB_FETCHBOTH: $retVal=$res->fetch_array(MYSQLI_BOTH); $res->free(); break;
                case self::SCDB_FETCHOBJ: $retVal=$res->fetch_object(); $res->free(); break;
                case self::SCDB_FETCHALL: $retVal=$res->fetch_all(); $res->free(); break;
                default: 
                    SCLOG::error("$preMsg BAD RESULT TYPE: $queryret |$posMsg");
                    $retVal=false;
            }
            return ["rows"=>$num_rows, "data"=>$retVal/*, "query"=>$sql*/, "timestamp"=>(new DateTime())->format("Y-m-d H:i:s")];
        }
        if (isset($preMsg[0])) $preMsg.=" ";
        $preMsg.="UNKNOWN RESULT TYPE";
        SCLOG::error("$preMsg (".gettype($res).")".(is_scalar($res)?" = '$res'":" _not_scalar_").$posMsg);
        return false;
    }
    public static function query($msg, $sql, $queryret = self::SCDB_FETCHASSOC, $data = null) {
        if (!self::connect()) return false;
        $dataMsg=isset($data)?" ".json_encode($data):"";
        $res=self::$conn->query($sql);
        return self::getResultData($res, $msg, $sql, $dataMsg, $queryret);
    }
    public static function query_prepared($msg, $sql, $types, $params, $queryret = self::SCDB_FETCHASSOC) {
        if (!self::connect()) return false;
        $stmt = self::$conn->prepare($sql);
        if (!$stmt) {
            SCLOG::error("$msg PREPARE ERROR [{self::$conn->errno}|{self::$conn->error}] $sql | ".json_encode($types)." | ".json_encode($params));
            return false;
        }
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            SCLOG::error("$msg EXECUTE ERROR [{$stmt->errno}|{$stmt->error}] $sql | ".json_encode($types)." | ".json_encode($params));
            return false;
        }
        $res = $stmt->get_result();
        return self::getResultData($res, $msg, $sql, json_encode($types)." | ".json_encode($params), $queryret);
    }
    public static function close() {
        if (self::$conn !== null) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}

//SCLOG::dot();
$query="select f.id,p.rfc rfcE,f.rfcGrupo rfcR,trim(f.total)+0 total,f.uuid,f.estadocfdi estado,f.solicitaCFDI,f.statusn from facturas f inner join proveedores p on f.codigoProveedor=p.codigo where (f.cancelableCFDI is null or f.solicitaCFDI is not null) and f.statusn>=0 and f.statusn<128 and f.estadoCFDI='Vigente' order by f.solicitaCFDI desc,f.id desc limit 1";
$row=SCDB::query("Retrieve Invoice Data", $query, 1)["data"]??false;
if ($row!==false) {
    $rfcDemo="RCO050301314";
    $id=$row["id"];
    $rfcE = str_replace("&","&amp;",$row["rfcE"]); // utf8_encode($row["rfcE"]));
    $rfcR = str_replace("&","&amp;",$row["rfcR"]); // utf8_encode($row["rfcR"]));
    if ($rfcR===$rfcDemo) { SCDB::close(); exit(); }
    $total = str_pad(sprintf("%.6f", (double)$row["total"]), 17, "0", STR_PAD_LEFT);
    $uuid = $row["uuid"];
    $estado = $row["estado"];
    $statusn = $row["statusn"];
    $solicita = $row["solicitaCFDI"];
    
    $qr="?re=$rfcE&rr=$rfcR&tt=$total&id=$uuid";
    //SCLOG::message("InvId=$id, Qr=$qr");
    $factura = valida_en_sat($qr);
    $valData=["id"=>$id, "qr"=>$factura["expresionImpresa"], "cfdi"=>$factura["cfdi"], "estado"=>$factura["estado"], "esCancelable"=>$factura["escancelable"], "estatusCancelacion"=>$factura["estatuscancelacion"]];

    $dt = new DateTime();
    $fmt = $dt->format("Y-m-d H:i:s");
    if ($factura["cfdi"]==="S - Comprobante obtenido satisfactoriamente.") {
        if ($factura["estado"]==="Vigente") {
            $esCancelableValue=$factura["escancelable"]??"";
            $query="UPDATE facturas SET consultaCFDI='$fmt', cancelableCFDI='$esCancelableValue'";
            if (!empty($solicita)) $query.=", solicitaCFDI=NULL, numConsultasCFDI=numConsultasCFDI+1";
            if (!empty($factura["estatuscancelacion"])) $query.=", canceladoCFDI='$factura[estatuscancelacion]'";
            $query.=" WHERE id=$id";
            SCDB::query("Update Valid Invoice", $query, 0, $valData);
            // toDo: se podria documentar proceso, aunque el propósito original es mostrar cambios de status, lo cual no sería el caso
        } else if ($factura["estado"]==="Cancelado") {
            $usrQuery="SELECT id FROM usuarios where nombre='eventos'";
            $usrRow=SCDB::query("Retrieve Event User Id", $usrQuery)["data"]??false;
            if ($usrRow===false) {
                SCLOG::error("Retrieve Event User Id [".self::$conn->errno."|".self::$conn->error."] No existe usuario 'eventos'");
            } else $usrId=$usrRow["id"];
            $facturaCanceladaSAT=256;
            $query="UPDATE facturas SET status='Rechazado', statusn=statusn|{$facturaCanceladaSAT}, consultaCFDI='$fmt', estadoCFDI='$factura[estado]', cancelableCFDI='$factura[escancelable]'";
            if (!empty($solicita)) $query.=", solicitaCFDI=NULL, numConsultasCFDI=numConsultasCFDI+1";
            if (!empty($factura["estatuscancelacion"])) $query.=", canceladoCFDI='$factura[estatuscancelacion]'";
            $query.=" WHERE id=$id";
            $queryResult = SCDB::query("Update Cancelled Invoice", $query, 0, $valData);
            if ($queryResult===true) { // ($queryResult["data"]??false)!==false // update solo puede regresar TRUE o FALSE
                $solQuery="SELECT * FROM SolicitudPago WHERE idFactura=$id AND idAutoriza IS NOT null AND status IS NOT null AND status>0";
                $solRow=SCDB::query("Retrieve Payment Request Data", $solQuery, 1)["data"]??false;
                if ($solRow!==false) {
                    $solId=$solRow["id"];
                    $solicitudCancelada=128;
                    $solQuery="UPDATE solicitudpago SET status=status|{$solicitudCancelada} WHERE id=$solId";
                    SCDB::query("Update Cancelled Payment Request", $solQuery, 0, $solRow);
                    $tokQuery="UPDATE tokens SET status='cancelado', usos='0' WHERE refId=$solId and status='activo'";
                    SCDB::query("Cancel active tokens", $tokQuery);
                    if ($usrRow!==false) {
                        $firQuery="INSERT INTO firmas ('modulo', 'idReferencia', 'idUsuario', 'accion', 'motivo') VALUES ('solpago', $ctrId, $usrId, 'rechaza', 'Factura cancelada en SAT')";
                        SCDB::query("Record signature reference", $firQuery);
                        $prcQuery="INSERT INTO proceso (modulo, identif, status, detalle, fecha, usuario, region) VALUES ('SolPago', $solId, 'RECHAZADA', 'Factura cancelada en SAT', now(), 'eventos', '127.0.0.1')";
                        SCDB::query("Record cancel process reference", $prcQuery);
                    }
                }
                if ($usrRow!==false) {
                    $prcQuery="INSERT INTO proceso (modulo, identif, status, detalle, fecha, usuario, region) VALUES ('Factura', $id, 'Rechazado', 'Cancelación obtenida de ConsultaCFDIService en sat.gob.mx', now(), 'eventos', '127.0.0.1')";
                    SCDB::query("Record cancel process reference", $prcQuery);
                }
                $ctfQuery="SELECT idContrarrecibo FROM contrafacturas WHERE idFactura=$id";
                $ctfRow=SCDB::query("Obtain Receipt Data", $ctfQuery)["data"]??false;
                if ($ctfRow!==false) {
                    $ctrId=$ctfRow["idContrarrecibo"];
                    $ctfQuery="DELETE FROM contrafacturas WHERE idFactura=$id and idContrarrecibo=$ctrId and id>0";
                    if ((SCDB::query("Delete invoice from receipt", $ctfQuery)["data"]??false)!==false) {
                        $ctfQuery="SELECT count(1) num, SUM(autorizadaPor IS NOT NULL) auth FROM contrafacturas WHERE idContrarrecibo=$ctrId";
                        $ctfRow=SCDB::query("Count CFDI in receipt", $ctfQuery)["data"]??false;
                        if ($ctfRow!==false) {
                            if ((+$ctfRow["num"])>0) {
                                $ctrQuery="UPDATE contrarrecibos SET numAutorizadas=$ctfRow[auth], numContraRegs=$ctfRow[num] WHERE id=$ctrId";
                                SCDB::query("ReSet count in receipt", $ctrQuery);
                            } else {
                                $ctrQuery="DELETE FROM contrarrecibos WHERE id=$ctrId";
                                SCDB::query("Delete receipt with no invoices", $ctrQuery);
                                if (!empty($usrRow)) {
                                    $firQuery="INSERT INTO firmas ('modulo', 'idReferencia', 'idUsuario', 'accion', 'motivo') VALUES ('contrarrecibo', $ctrId, $usrId, 'elimina', 'Factura cancelada')";
                                    SCDB::query("Record signature reference", $firQuery);
                                }
                            }
                        }
                    }
                }
                // ToDo: Mandar correo a proveedor, a solicitante y a autorizadores
            }
            // aqui hay que hacer mas cosas
            // Si la factura no está vigente, igual hay que guardar la info obtenida, pero también hay que rechazar la factura
        } else SCLOG::error("ESTADO NO CONTEMPLADO! Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
    } else if ($factura["cfdi"]==="N - 601: La expresión impresa proporcionada no es válida.") {
        $query="UPDATE facturas SET estadoCFDI='Invalido', solicitaCFDI=NULL, consultaCFDI='$fmt', cancelableCFDI='601' WHERE id=$id";
        SCDB::query("Invalid SAT Expression", $query, 0, $valData);
    } else if ($factura["cfdi"]==="N - 602: Comprobante no encontrado.") {
        $query="UPDATE facturas SET estadoCFDI='No encontrado', solicitaCFDI=NULL, consultaCFDI='$fmt', cancelableCFDI='602' WHERE id=$id";
        SCDB::query("Not Found SAT State", $query, 0, $valData);
    } else {
        $query="UPDATE facturas SET solicitaCFDI=NULL, consultaCFDI='$fmt', cancelableCFDI='ERR' WHERE id=$id";
        SCDB::query("Unknown SAT State", $query, 0, $valData);
    }
}
SCDB::close();
