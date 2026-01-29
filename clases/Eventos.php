<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "DBObject.php";
class Eventos extends DBObject {
    private static $GLOBALEVENTS=["type","action","duedate","data","class","function","functype","times","frequency","query","file"];
    function __construct() {
        $this->tablename      = "eventos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "tipo", "termino", "accion", "data");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Eventos xxxxxxxxxxxxxx //\n";
    }
    static function getVencidos() {
        global $evtObj;
        if (!isset($evtObj)) {
            $evtObj=new Eventos();
        }
        // ultimos 10 : order by id desc, limit 10
        // primeros 10 : cada consulta debe eliminar el registro por lo que siempre debe estar avanzando
        // dependiendo de la frecuencia de uso de eventos, habrá que aumentar o disminuir los registros evaluados simultáneamente
        $evtObj->rows_per_page=10;
        $evtObj->clearOrder();
        $evtObj->addOrder("termino", "asc");
        $data_array = $evtObj->getData("termino<=now()");
        return $data_array;
    }
    static function testRepetir($fila) {
        $tipoFila=$fila["tipo"]??"";
        if ($tipoFila!=="funcion") return;
        $accion=$fila["accion"]??"";
        if ($accion!=="repite" && $accion!=="ejecuta") return;
        if (!isset($fila["termino"][0])) return;
        if (!isset($fila["data"][0])) return;
        $data=json_decode($fila["data"],true);
        //if (!isset($data["clase"][0])) return;
        $clase=$data["clase"]??null;
        if (!isset($data["funcion"][0])) return;
        $funcion=$data["funcion"];
        $tipo=$data["tipofuncion"]??""; // objeto o clase
        if ($tipo!=="objeto"&&$tipo!=="clase"&&$tipo!=="global") return;
        doclog(strtoupper($accion),"eventos",$fila);
        try {
            if (isset($clase[0]))
                require_once "clases/".$clase.".php";
            if ($tipo==="objeto") {
                $obj=new $clase(); $obj->{$funcion}();
            } else if ($tipo==="clase") {
                $clase::{$funcion}();
            } else { // if ($tipo==="global") {
                $funcion();
            }
            $veces=+$data["veces"]??-1;
            $ciclo=+($data["ciclo"]??60*5); // segundos*60*5 = cada 5 minutos
            // ToDo: Validar que veces sea mayor a cero y calcular ahora+rango
            // En la base hay que cambiar el valor de veces y termino para que esté listo para la siguiente vez
            if ($veces!==0) {
                $fecha=date("Y-m-d H:i:s",time()+$ciclo);
                $fldata=["funcion"=>$funcion,"tipofuncion"=>$tipo,"veces"=>$veces,"ciclo"=>$ciclo];
                if (isset($clase)) $fldata["clase"]=$clase;
                $fldarr=["tipo"=>"repite","termino"=>$fecha,"accion"=>"funcion","data"=>json_encode($fldata)];
                global $evtObj;
                if (!isset($evtObj)) {
                    //require_once "clases/Eventos.php";
                    $evtObj=new Eventos();
                }
                if ($evtObj->saveRecord($fldarr)) {
                }
            }
        } catch (Exception $ex) {
            doclog("ERROR","error",["excepcion"=>$ex]);
        }
    }
    static function procesar() {
        $resultado=[];
        $vencidos=Eventos::getVencidos();
        if (isset($vencidos[0]))
            $resultado[]="Eventos Vencidos: ".count($vencidos);
        global $evtObj;
        if (!isset($evtObj)) {
            $evtObj=new Eventos();
        }
        foreach ($vencidos as $num => $fila) {
            try {
                $txt1=$evtObj->procesaEvento($fila);
                if (isset($txt1[0])) {
                    $mensaje[]=$txt1;
                    $resultado[]="$num) $txt1";
                }
                //if ($fila["tipo"]!=="repite")
                $txt2=Eventos::elimina($fila["id"]);
                if (isset($txt2[0])) {
                    $mensaje[]=$txt2;
                    $resultado[]="$num) BD: $txt2";
                }
                doclog("PROCESADO","eventos",["num"=>$num, "fila"=>$fila,"procesado"=>$txt1,"eliminado"=>$txt2]);
            } catch (Throwable $ex) {
                doclog("EVENTO NO PROCESADO","error",["num"=>$num, "fila"=>$fila,"error"=>getErrorData($ex)]);
            }
        }
        return $resultado;
    }
    function procesaEvento($fila) {
        foreach (self::$GLOBALEVENTS as $idx => $val) {
            $current="_current_event_{$val}"; $last="_last_event_{$val}";
            if (isset($GLOBALS[$current][0])) $GLOBALS[$last]=$GLOBALS[$current];
            else $GLOBALS[$last]=null; $GLOBALS[$current]=null;
        }
        $GLOBALS["_current_event_type"]=$fila["tipo"]??"";
        $GLOBALS["_current_event_action"]=$fila["accion"]??"";
        $GLOBALS["_current_event_duedate"]=$fila["termino"]??"";
        $GLOBALS["_current_event_data"]=$fila["data"];
        switch($fila["tipo"]) {
            case "funcion":
                $accion=$fila["accion"]??"";
                if ($accion!=="ejecuta"&&$accion!=="repite") return "Error en evento 'funcion': accion no contemplada '$fila[accion]'";
                if (!isset($fila["termino"][0])) return "Evento incompleto: debe indicar fecha de término";
                $termino=$fila["termino"];
                if (!isset($fila["data"][0])) return "Evento incompleto: debe indicar datos de evento";
                $data=json_decode($fila["data"],true);
                //$data=$fila["data"];
                //if (!isset($data["clase"][0])) return "Evento incompleto: debe indicar clase de accion a realizar";
                $clase=$data["clase"]??null;
                if (!isset($data["funcion"][0])) return "Evento incompleto: debe indicar funcion a ejecutar";
                $funcion=$data["funcion"];
                $tipo=$data["tipofuncion"]??"";
                if ($tipo!=="objeto"&&$tipo!=="clase"&&$tipo!=="global") return "Evento fallido: debe indicar si la función es de instancia, clase o global";
                if ($accion==="repite") {
                    $veces=+($data["veces"]??"-1"); // -1=siempre
                    $ciclo=+($data["ciclo"]??"300"); // 60*5 = 5 minutos
                } else $veces=1;
                doclog("FUNCION","eventos",$fila);
                if ($veces==0) return "Evento Repite Sin Repeticiones";
                if (isset($clase[0])) $GLOBALS["_current_event_class"]=$clase;
                $GLOBALS["_current_event_function"]=$funcion;
                $GLOBALS["_current_event_functype"]=$tipo;
                $GLOBALS["_current_event_times"]=$veces;
                if (isset($ciclo))
                    $GLOBALS["_current_event_frequency"]=$ciclo;
                try {
                    if (isset($clase[0]))
                        require_once "clases/".$clase.".php";
                    if ($tipo==="objeto") {
                        $obj=new $clase();
                        // ToDo: Permitir argumentos
                        // ToDo: Permitir valor de regreso
                        $obj->{$funcion}();
                    } else if ($tipo==="clase") {
                        // ToDo: Permitir argumentos
                        // ToDo: Permitir valor de regreso
                        $clase::{$funcion}();
                    } else // if ($tipo==="global") {
                        $funcion();
                    //}
                    if($clase!=="Eventos") $this->logCurrent();
                    if ($accion==="repite") {
                        $now=time(); //$ts=0;
                        //for ($ts=strtotime($termino)+$ciclo; $ts<$now; $ts+=$ciclo);
                        $ts=strtotime($termino)+$ciclo;
                        if ($ts<$now) $ts=$now;
                        $fecha=date("Y-m-d H:i:s", $ts);
                        //$fecha=date("Y-m-d H:i:s",time()+$ciclo);
                        if ($veces>0) $veces--;
                        if ($veces==0) return "Evento concluido satisfactoriamente";
                        if ($veces==1) $accion="ejecuta"; // la última vez ya no hay repeticiones
                        $fldarr=["tipo"=>"funcion","termino"=>$fecha,"accion"=>$accion,"data"=>json_encode(["clase"=>$clase,"funcion"=>$funcion,"tipofuncion"=>$tipo,"veces"=>$veces,"ciclo"=>$ciclo])];
                        if (!$this->saveRecord($fldarr)) {
                            global $query;
                            doclog("Error de Evento","error",["query"=>$query,"classErrors"=>DBi::$errors,"objectErrors"=>$this->errors,"errno"=>$this->getErrno(),"error"=>$this->getError(),"log"=>$this->log]);
                            return "Evento fallido: no se pudo guardar resultado de evento";
                        }
                        $veces++;
                        return "Repeticion de Evento exitosa ( $veces )";
                    } else "Ejecución de Evento exitosa";
                } catch (Exception $ex) {
                    doclog("Error critico de Evento","error",["excepcion"=>$ex]);
                    return "Evento fallido: ocurrió un problema de ejecución";
                }
            case "prueba":
                $this->logCurrent();
                if ($fila["accion"]==="avisa") return "Aviso de prueba: $fila[data]";
                return "Prueba: $fila[accion]: $fila[data]";
            case "query":
                if ($fila["accion"]==="ejecuta") {
                    if (!isset($fila["data"][0])) return "Evento incompleto: debe indicar datos de ejecucion";
                    $data=json_decode($fila["data"],true);
                    global $query;
                    if (!isset($data["query"][0])) return "No se especificó query a ejecutar";
                    $qry=$data["query"];
                    $GLOBALS["_current_event_query"]=$query;
                    $this->logCurrent();
                    $result=DBi::query($qry);
                    if (is_bool($result)) {
                        if ($result) {
                            $rows=DBi::$affected_rows;
                            return "Query ejecutado '$query' con $rows registros afectados";
                        }
                        if (DBi::$errno==0) return "Query ejecutado '$query' sin registros afectados";
                        $errno=DBi::$errno;
                        $error=DBi::$error;
                        return "Error $errno al ejecutar query '$query': $error";
                    }
                    if (is_numeric($result)) return "Query ejecutado '$query' con $result registros insertados";
                    if (is_object($result)) return "Query ejecutado '$query' con datos extraidos pero no hay modulo para utilizarlos";
                    return "Query ejecutado con resultado no contemplado '$query'";
                }
                return "Accion de query indeterminada '$fila[accion]'. Data: $fila[data]";
            case "archivo":
                if ($fila["accion"]==="elimina") {
                    if (!isset($fila["data"][0])) return "Evento incompleto: debe indicar datos de archivo";
                    $data=$fila["data"];
                    //$data=str_replace("\\", "", $data);
                    $data=str_replace("'", "\"", $data);
                    $dataarr=json_decode($data,true);
                    //doclog("evento","eventos",["tipo"=>$fila["tipo"],"accion"=>$fila["accion"],"data"=>$data,"dataType"=>gettype($data),"arr"=>$dataarr]);
                    if (!isset($dataarr["archivo"][0])) {
                        $dbqtData=str_replace("'", "\"", $data);
                        return "No se ha definido archivo a eliminar \"".$dbqtData."\"=>\"".json_encode($dataarr)."\": ".json_last_error_msg();
                    }
                    $archivo=$dataarr["archivo"];
                    $GLOBALS["_current_event_file"]=$archivo;
                    if (!isset($archivo[0])) return "No se indicó archivo a eliminar";
                    $lookoutFilePath = "";
                    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT']))
                        $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
                    else if (!empty($_SERVER['DOCUMENT_ROOT']))
                        $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
                    $this->logCurrent();
                    if (file_exists($lookoutFilePath.$archivo)) {
                        if (rename($lookoutFilePath.$archivo,$lookoutFilePath.$archivo."x"))
                            return "Archivo eliminado '$archivo'";
                        return "Ocurrió un error al eliminar el archivo '$archivo'";
                    }
                    return "No existe el archivo a eliminar '$archivo'";
                } else if ($fila["accion"]==="incluye") {
                    if (!isset($fila["data"][0])) return "Evento incompleto: debe indicar datos de archivo";
                    $data=$fila["data"];
                    $data=str_replace("'", "\"", $data);
                    $dataarr=json_decode($data,true);
                    if (!isset($dataarr["archivo"][0])) {
                        $dbqtData=str_replace("'", "\"", $data);
                        return "No se ha definido archivo a incluir \"".$dbqtData."\"=>\"".json_encode($dataarr)."\": ".json_last_error_msg();
                    }
                    $archivo=$dataarr["archivo"];
                    $GLOBALS["_current_event_file"]=$archivo;
                    if (!isset($archivo[0])) return "No se indicó archivo a incluir";
                    if (isset($datarr["ruta"][0])) {
                        $lookoutFilePath=$datarr["ruta"];
                        if (isset($lookoutFilePath[0])) $GLOBALS["_current_event_path"]=$lookoutFilePath;
                    } else {
                        $lookoutFilePath = "";
                        if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT']))
                            $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
                        else if (!empty($_SERVER['DOCUMENT_ROOT']))
                            $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
                    }
                    $this->logCurrent();
                    if (file_exists($lookoutFilePath.$archivo)) {
                        include $lookoutFilePath.$archivo;
                        return "Ejecución de archivo incluida";
                    }
                    return "No existe el archivo a incluir '$lookoutFilePath' '$archivo'";
                } else if ($fila["accion"]==="ejecuta") {
                    if (!isset($fila["data"][0])) return "Evento incompleto: debe indicar datos de archivo a ejecutar";
                    $data=$fila["data"];
                    $data=str_replace("'", "\"", $data);
                    $dataarr=json_decode($data,true);
                    if (!isset($dataarr["archivo"][0])) {
                        $dbqtData=str_replace("'", "\"", $data);
                        return "No se ha definido archivo a incluir \"".$dbqtData."\"=>\"".json_encode($dataarr)."\": ".json_last_error_msg();
                    }
                    $archivo=$dataarr["archivo"];
                    $GLOBALS["_current_event_file"]=$archivo;
                    if (!isset($archivo[0])) return "No se indicó archivo a eliminar";
                    if (isset($datarr["ruta"][0])) {
                        $lookoutFilePath=$datarr["ruta"];
                        if (isset($lookoutFilePath[0])) $GLOBALS["_current_event_path"]=$lookoutFilePath;
                    } else {
                        $lookoutFilePath = "";
                        if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT']))
                            $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
                        else if (!empty($_SERVER['DOCUMENT_ROOT']))
                            $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
                    }
                    $this->logCurrent();
                    if (file_exists($lookoutFilePath.$archivo)) {
                        if (isset($dataarr["outfile"][0])) {
                            $log=$lookoutFilePath.$dataarr["outfile"];
                            $GLOBALS["_current_event_outfile"]=$dataarr["outfile"];
                        }
                        //else $log=$lookoutFilePath.substr($archivo, 0, -4).".log";
                        if (isset($log[0]) && !is_dir($log)) {
                            file_put_contents($log, "[".date("YmdHis")."] ".PHP_EOL, FILE_APPEND);
                        } else $log="nul";
                        if (substr(php_uname(), 0, 7) == "Windows") { //windows
                            pclose(popen("start /B php ".$lookoutFilePath.$archivo." 1> $log 2>&1", "r"));
                        } else { //linux
                            shell_exec( $command . " 1> $log 2>&1" );
                        }
                        return "Ejecucion de archivo realizada";
                    }
                    return "No existe el archivo a ejecutar '$lookoutFilePath' '$archivo'";
                }
                return "Accion de archivo no contemplada '$fila[accion]'. Datos: $fila[data]";
        }
        return "Registro indeterminado: ".json_encode($fila);
    }
    static function elimina($id) {
        global $evtObj;
        if (!isset($evtObj)) {
            $evtObj=new Eventos();
        }
        if ($evtObj->deleteRecord(["id"=>$id])) {
            return "Registro eliminado $id";
        } else if (DBi::$errno<=0) {
            return "No se encontró registro $id a eliminar";
        } else {
            return "Ocurrió un error ".DBi::$errno.": ".DBi::$error;
        }
        return "FIN elimina Eventos";
    }
    static function getPendientes() {
        global $evtObj;
        if (!isset($evtObj)) {
            $evtObj=new Eventos();
        }
        $data_array = $evtObj->getData("termino>now()");
        return $data_array;
    }
    static function pendientes() {
        $resultado=[];
        $pendientes=Eventos::getPendientes();

        $txt="Eventos Pendientes: ".count($pendientes);
        $txt.="<TABLE><THEAD><TR><TH>ACCION</TH><TH>VENCIMIENTO</TH><TH>DATOS</TH></TR></THEAD><TBODY>";
        foreach ($pendientes as $idx => $row) {
            if ($idx>=10) break;
            $data=http_build_query(json_decode($row["data"],true));
            $txt.="<TR><TD>$row[accion] $row[tipo]</TD><TD>$row[termino]</TD><TD>$data</TD></TR>";
        }
        $txt.="</TBODY></TABLE>";
        $resultado[]=$txt;
        return $resultado;
    }
    function borraArchivo($archivo, $segundos) {
        $fecha=date("Y-m-d H:i:s",time()+$segundos);
        $fldarr=["tipo"=>"archivo","accion"=>"elimina","termino"=>$fecha,"data"=>json_encode(["archivo"=>$archivo])];
        if ($this->saveRecord($fldarr)) {
            doclog("AGREGA","eventos",$fldarr);
            return true;
        } else if (empty(DBi::$errors)) {
            global $query;
            doclog("SIN AGREGAR","eventos",$fldarr+["query"=>$query]);
            return false;
        } else {
            global $query;
            doclog("ERROR AL AGREGAR","eventos",$fldarr+["query"=>$query,"errors"=>DBi::$errors]);
            return "Error al borrar archivo";
        }
    }
    function ejecuta($query,$segundos) {
        $fecha=date("Y-m-d H:i:s",time()+$segundos);
        $fldarr=["tipo"=>"query","accion"=>"ejecuta","termino"=>$fecha,"data"=>json_encode(["query"=>$query])];
        if ($this->saveRecord($fldarr)) {
            doclog("AGREGA","eventos",$fldarr);
            if (isset($this->lastId)) return $this->lastId; //DBi::$insert_id;
            return true;
        } else if (DBi::$errno>0) {
            global $query;
            doclog("ERROR AL AGREGAR","eventos",$fldarr+["query"=>$query,"errno"=>DBi::$errno,"error"=>DBi::$error]);
        } else {
            global $query;
            doclog("SIN AGREGAR","eventos",$fldarr+["query"=>$query]);
        }
        return false;
    }
    function logCurrent() {
        $this->logFunc("current");
    }
    function logLast() {
        $this->logFunc("last");
    }
    private function logFunc($eventType) { // ["duedate","action","type","class","function","data","functype","times","frequency","query","file"]
        if (!isset($eventType)) return;
        global $logObj;
        if (!isset($logObj)) {
            require_once "clases/Logs.php";
            $logObj=new Logs();
        }
        $systemUserId=1038;
        $eventType="_{$eventType}_event_";
        $typeValue=$GLOBALS[$eventType."type"]??"";
        $text=$GLOBALS[$eventType."duedate"]." : ".$GLOBALS[$eventType."action"]." ".$typeValue;
        if ($typeValue==="prueba") {
            $data=$GLOBALS[$eventType."data"]??"";
            if (isset($data[0])) $text.=" $data";
        }
        if (isset($GLOBALS[$eventType."functype"])) {
            $text.=" : ";
            if (isset($GLOBALS[$eventType."class"])) {
                switch($GLOBALS[$eventType."functype"]) {
                    case "clase": $text.=$GLOBALS[$eventType."class"]."::".$GLOBALS[$eventType."function"]."()"; break;
                    case "objeto": $text.="{".$GLOBALS[$eventType."class"]."}->".$GLOBALS[$eventType."function"]."()"; break;
                    default: $text.="(".$GLOBALS[$eventType."data"].")";
                }
            } else if (isset($GLOBALS[$eventType."function"])) $text.=$GLOBALS[$eventType."function"]."()";
        }
        $gquery=$GLOBALS[$eventType."query"]??"";
        if (isset($gquery[0])) $text.=" '".$gquery."'";
        $pathnm=$GLOBALS[$eventType."path"]??"";
        if (isset($pathnm[0])) $text.=" '".$pathnm."'";
        $filenm=$GLOBALS[$eventType."file"]??"";
        if (isset($filenm[0])) {
            if (isset($pathnm[0])) $text.="/";
            else $text.=" ";
            $text.="'".$filenm."'";
        }
        $outnm=$GLOBALS[$eventType."outfile"]??"";
        if (isset($outnm[0])) $text.=" >'".$outnm."'";
        if (isset($GLOBALS[$eventType."frequency"])) $text.=" cada ".$GLOBALS[$eventType."frequency"]." segundos";
        $times="".($GLOBALS[$eventType."times"]??"");
        if (isset($times[0])) {
            if ($times=="-1") $text.=" indefinidamente";
            else if ($times=="1") $text.=" última vez";
            else {
                $times=(+$times)-1;
                if ($times==1)
                    $text.=" ".$times." vez más";
                else
                    $text.=" $times veces más";
            }
        }
        /* foreach (self::$GLOBALEVENTS as $idx => $val) {
            if ($val==="data") continue;
            $evtname="{$eventType}{$val}";
            if (isset($GLOBALS[$evtname][0])) {
                if (isset($text[0])) $text.=", ";
                $text.="$val='".$GLOBALS[$evtname]."'";
            }
        } */
        if (isset($text[0])) $logObj->agrega($systemUserId, "EVENTOS", $text);
    }
}
