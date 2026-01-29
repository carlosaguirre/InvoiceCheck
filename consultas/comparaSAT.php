<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
if (isAlta()) doAlta(); else
if (isConsulta()) doConsulta(); else
if (isComparaAmbas()) doComparaAmbas(); else
if (isAltaB()) doAltaB(); else
if (isConsultaB()) doConsultaB(); else
if (isComparaAmbasB()) doComparaAmbasB(); else
if (isAltaC()) doAltaC(); else
if (isAltaPrv()) doAltaPrv(); else
if (isConsultaC()) doConsultaC(); else
if (isComparaAmbasC()) doComparaAmbasC(); else
if (isConsultaD()) doConsultaD(); else
if (isConsultaPrv()) doConsultaPrv(); else
if (isComparaAmbasD()) doComparaAmbasD(); else
if (isComparaAmbasPrv()) doComparaAmbasPrv(); else
if (isEliminaD()) doEliminaD(); else
if (isEliminaPrv()) doEliminaPrv();

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isAlta() {
    return isset($_POST["accion"])&&$_POST["accion"]==="alta";
}
function doAlta() {
    if (!isset($_POST["name"])) return echoError("No se recibió nombre de datos", $_POST);
    if (!isset($_FILES)) return echoError("No se recibieron archivos", $_POST);
    if (empty($_FILES["file"])) return echoError("No se recibieron archivos validos", $_POST+$_FILES);
    $file=$_FILES["file"];
    if (!empty($file["error"])) return echoError("Ocurrio un error durante la captura del archivo", $file);
    if ($file["size"]==0) return echoError("Carga de archivo sin datos", $file);
    $handle=fopen($file["tmp_name"], "r");
    if ($handle===FALSE) return echoError("No se pudo leer el archivo", $file);

    $time_start = microtime(true);
    $fields=[]; $data=[]; $isFirst=true; $loops=0; $numqry=0; $inserted=0;
    $tablename=$_POST["name"];
    if (!in_array($tablename,["clientes","comparaavance","comparasat","comparaavanceprv","comparasatprv"])) return echoError("No se reconoce tabla de datos $tablename como valida", $_POST);
    $beginquery="INSERT INTO $tablename ";
    $duplicates=0;
    $noneuuid=0;
    $errors=[];
    $warnings=[];
    $infos=[];
    while (($lineArr=fgetcsv($handle,1000,","))!==FALSE) {
        $loops++;
        if (!isset($lineArr[0])) continue;
        if ($isFirst) { // la primer fila, de encabezados, deben corresponder a campos validos en la tabla <name>
            $fields=$lineArr; // preg_replace("/[^a-v,]/", "",mb_strtolower(implode(",",$lineArr)));
            array_walk($fields, function(&$item,$idx) { $item=preg_replace("/[^a-z]/", "",mb_strtolower($item));});
            if (!isset($fields[0][0])) break; // La primer columna debe tener encabezado
            $beginquery.="(".implode(",", $fields).") VALUES ";
            $isFirst=false; continue;
        }
        // if (isset($dataquery[0])) $dataquery.=", "; $dataquery.="(";
        $data[$numqry]=[];
        $currUUID="";
        $dataquery="";
        for ($n=0;isset($fields[$n]);$n++) {
            if ($n>0) $dataquery.=",";
            if ($fields[$n]==="fecha") {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=str_replace("/","-", $lineArr[$n]);
                    $data[$numqry][$n]=preg_replace("/[^0-9\:\-T ]/","",$data[$numqry][$n]);
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            } else if (in_array($fields[$n],["rfc","rfcemisor","rfcreceptor"])) {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=str_replace([" ","-"],"",$lineArr[$n]);
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            } else if (in_array($fields[$n], ["subtotal","descuento","trasladoiva","retencionisr","retencioniva","impuestos","total"])) {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=preg_replace("/[^0-9\.]/","",$lineArr[$n]);
                    $dataquery.=$data[$numqry][$n];
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            } else {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=trim($lineArr[$n]);
                    $data[$numqry][$n]=str_replace(["\"","\\"],["'",""], $data[$numqry][$n]);
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                    if ($fields[$n]==="uuid") {
                        $currUUID=$data[$numqry][$n];
                        $iniUUID=substr($currUUID,0,4);
                        $endUUID=substr($currUUID,-4);
                        $currUUID=$iniUUID."..".$endUUID;
                    }
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            }
        }
        $numqry++;
        $result=DBi::query($beginquery."(".$dataquery.")", new class extends DBObject { public function __construct() { $this->tablename = $tbnm; } });
        //$dataquery.=")";
        if ($result&&DBi::$affected_rows>0) {
            $inserted++;
            $info=trim(DBi::$query_info);
            if (isset($info[5])) $infos[$currUUID]=$info;
            $warn=trim(DBi::$warnings);
            if (isset($warn[5])) $warnings[$currUUID]=$warn;
        } else {
            $errno=DBi::$errno;
            $error=DBi::$error;
            if ($errno==1062) $duplicates++;
            else if ($errno==1048 && $error==="Column 'uuid' cannot be null") $noneuuid++;
            else if ($errno==1064) $errors[$currUUID]=$error."\n".$beginquery."(".$dataquery.")";
            else $errors[$currUUID]=$errno.":".$error;
        }
    }
    asort($errors);
    asort($warnings);
    asort($infos);
    $duration = (microtime(true) - $time_start);
    //$query=$beginquery.$dataquery;
    echo json_encode(["result"=>$inserted>0?"success":"error","loops"=>$loops,"numqry"=>$numqry,"inserted"=>$inserted??0,"info"=>$infos,"errors"=>$errors,"warnings"=>$warnings,"table"=>$tablename,"fields"=>$fields,"duplicates"=>$duplicates,"noneuuid"=>$noneuuid,"duration"=>$duration]);
}
function isAltaB() {
    return isset($_POST["accion"])&&$_POST["accion"]==="alta_b";
}
function doAltaB() {
    if (!isset($_POST["name"])) return echoError("No se recibió nombre de datos", $_POST);
    if (!isset($_FILES)) return echoError("No se recibieron archivos", $_POST);
    if (empty($_FILES["file"])) return echoError("No se recibieron archivos validos", $_POST+$_FILES);
    $file=$_FILES["file"];
    if (!empty($file["error"])) return echoError("Ocurrio un error durante la captura del archivo", $file);
    if ($file["size"]==0) return echoError("Carga de archivo sin datos", $file);
    $handle=fopen($file["tmp_name"], "r");
    if ($handle===FALSE) return echoError("No se pudo leer el archivo", $file);

    $time_start = microtime(true);
    $fields=[]; $data=[]; $isFirst=true; $loops=0; $numqry=0; $inserted=0;
    $tablename=$_POST["name"];
    $beginquery="INSERT INTO $tablename ";
    $duplicates=0;
    $noneuuid=0;
    $errors=[];
    $warnings=[];
    $infos=[];
    while (($lineArr=fgetcsv($handle,1000,","))!==FALSE) {
        $loops++;
        if (!isset($lineArr[0])) continue;
        if (isset($lineArr[3])) {
            return echoError("El archivo debe contar únicamente con las tres columnas UUID, FECHA y TIPOCOMPROBANTE", ["lineArr"=>$lineArr]+$_POST);
        }
        if ($isFirst) { // la primer fila, de encabezados, deben corresponder a campos validos en la tabla <name>
            $fields=$lineArr; // preg_replace("/[^a-v,]/", "",mb_strtolower(implode(",",$lineArr)));
            $fieldsTxt=implode(",",$fields);
            if (strtolower($fieldsTxt)!=="uuid,fecha,tipocomprobante") {
                return echoError("El archivo debe incluir los encabezados de columna UUID, FECHA y TIPOCOMPROBANTE", $_POST);
            }
            array_walk($fields, function(&$item,$idx) { $item=preg_replace("/[^a-z]/", "",mb_strtolower($item));});
            if (!isset($fields[0][0])) break; // La primer columna debe tener encabezado
            $beginquery.="(".$fieldsTxt.") VALUES ";
            $isFirst=false; continue;
        }
        // if (isset($dataquery[0])) $dataquery.=", "; $dataquery.="(";
        $data[$numqry]=[];
        $currUUID="";
        $dataquery="";
        for ($n=0;isset($fields[$n]);$n++) {
            if ($n>0) $dataquery.=",";
            if ($fields[$n]==="fecha") {
                if (isset($lineArr[$n][0])) {
                    // Verificar formato de fecha. Detectar si viene con el formato Excel default 'dd/mm/aaaa hh:mm' y convertirlo al formato valido 'aaaa-mm-dd hh:mm'

                    $data[$numqry][$n]=str_replace("/","-", $lineArr[$n]);
                    $data[$numqry][$n]=preg_replace("/[^0-9\:\-T ]/","",$data[$numqry][$n]);
                    $data[$numqry][$n]=str_replace("T", " ", $data[$numqry][$n]);
                    if ($data[$numqry][$n][2]==="-" && $data[$numqry][$n][5]==="-") {
                        $dia=substr($data[$numqry][$n], 0, 2);
                        $mes=substr($data[$numqry][$n], 3, 2);
                        $anv=substr($data[$numqry][$n], 6, 4);
                        $tim="";
                        if (isset($data[$numqry][$n][10])) $tim=substr($data[$numqry][$n],10);
                        $data[$numqry][$n]="$anv-$mes-$dia$tim";
                        // ya se reemplaza / con -, si tiene - en indices 2 y 5 voltear los numeros
                        //$data[$numqry][$n]==="-"
                    }
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            } else {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=trim($lineArr[$n]);
                    $data[$numqry][$n]=str_replace(["\"","\\"],["'",""], $data[$numqry][$n]);
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                    if ($fields[$n]==="uuid") {
                        $currUUID=$data[$numqry][$n];
                        $iniUUID=substr($currUUID,0,4);
                        $endUUID=substr($currUUID,-4);
                        $currUUID=$iniUUID."..".$endUUID;
                    }
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            }
        }
        $numqry++;
        $result=DBi::query($beginquery."(".$dataquery.")", new class extends DBObject { public function __construct() { $this->tablename = $tbnm; } });
        //$dataquery.=")";
        if ($result&&DBi::$affected_rows>0) {
            $inserted++;
            $info=trim(DBi::$query_info);
            if (isset($info[5])) $infos[$currUUID]=$info;
            $warn=trim(DBi::$warnings);
            if (isset($warn[5])) $warnings[$currUUID]=$warn;
        } else {
            $errno=DBi::$errno;
            $error=DBi::$error;
            if ($errno==1062) $duplicates++;
            else if ($errno==1048 && $error==="Column 'uuid' cannot be null") $noneuuid++;
            else if ($errno==1064) $errors[$currUUID]=$error."\n".$beginquery."(".$dataquery.")";
            else $errors[$currUUID]=$errno.":".$error;
        }
    }
    asort($errors);
    asort($warnings);
    asort($infos);
    $duration = (microtime(true) - $time_start);
    //$query=$beginquery.$dataquery;
    echo json_encode(["result"=>$inserted>0?"success":"error","loops"=>$loops,"numqry"=>$numqry,"inserted"=>$inserted??0,"info"=>$infos,"errors"=>$errors,"warnings"=>$warnings,"table"=>$tablename,"fields"=>$fields,"duplicates"=>$duplicates,"noneuuid"=>$noneuuid,"duration"=>$duration]);
}
function isAltaC() {
    return isset($_POST["accion"])&&$_POST["accion"]==="alta_c";
}
function doAltaC() {
    if (!isset($_POST["name"])) return echoError("No se recibió nombre de datos", $_POST);
    if (!isset($_FILES)) return echoError("No se recibieron archivos", $_POST);
    if (empty($_FILES["file"])) return echoError("No se recibieron archivos validos", $_POST+$_FILES);
    $file=$_FILES["file"];
    if (!empty($file["error"])) return echoError("Ocurrio un error durante la captura del archivo", $file);
    if ($file["size"]==0) return echoError("Carga de archivo sin datos", $file);
    $handle=fopen($file["tmp_name"], "r");
    if ($handle===FALSE) return echoError("No se pudo leer el archivo", $file);

    $time_start = microtime(true);
    $fields=[]; $data=[]; $isFirst=true; $loops=0; $numqry=0; $inserted=0;
    $tablename=$_POST["name"];
    $beginquery="INSERT INTO $tablename ";
    $duplicates=0;
    $noneuuid=0;
    $errors=[];
    $warnings=[];
    $infos=[];
    while (($lineArr=fgetcsv($handle,1000,","))!==FALSE) {
        $loops++;
        if (!isset($lineArr[0])) continue;
        if (isset($lineArr[5])) {
            return echoError("El archivo debe contar únicamente con las cinco columnas UUID, FECHA, TIPOCOMPROBANTE, RFCEMISOR Y RFCRECEPTOR", ["lineArr"=>$lineArr]+$_POST);
        }
        if ($isFirst) { // la primer fila, de encabezados, deben corresponder a campos validos en la tabla <name>
            $fields=$lineArr; // preg_replace("/[^a-v,]/", "",mb_strtolower(implode(",",$lineArr)));
            $fieldsTxt=preg_replace("/[^a-z\,]/", "",mb_strtolower(implode(",",$fields)));
            $fields=explode(",",$fieldsTxt);
            if ($fieldsTxt!=="uuid,fecha,tipocomprobante,rfcemisor,rfcreceptor") {
                return echoError("El archivo debe incluir los encabezados de columna UUID, FECHA, TIPOCOMPROBANTE, RFCEMISOR Y RFCRECEPTOR.\n$fieldsTxt", ["lineArr"=>$lineArr, "fieldsTxt"=>$fieldsTxt]+$_POST);
            }
            //array_walk($fields, function(&$item,$idx) { $item=preg_replace("/[^a-z]/", "",mb_strtolower($item));});
            $beginquery.="(".$fieldsTxt.") VALUES ";
            $isFirst=false; continue;
        }
        // if (isset($dataquery[0])) $dataquery.=", "; $dataquery.="(";
        $data[$numqry]=[];
        $currUUID="";
        $dataquery="";
        for ($n=0;isset($fields[$n]);$n++) {
            if ($n>0) $dataquery.=",";
            if ($fields[$n]==="fecha") {
                if (isset($lineArr[$n][0])) {
                    // Verificar formato de fecha. Detectar si viene con el formato Excel default 'dd/mm/aaaa hh:mm' y convertirlo al formato valido 'aaaa-mm-dd hh:mm'
                    $data[$numqry][$n]=str_replace(["/","T"],["-"," "], $lineArr[$n]);
                    $data[$numqry][$n]=preg_replace("/[^0-9\:\- ]/","",$data[$numqry][$n]);
                    if ($data[$numqry][$n][2]==="-" && $data[$numqry][$n][5]==="-") {
                        $dia=substr($data[$numqry][$n], 0, 2);
                        $mes=substr($data[$numqry][$n], 3, 2);
                        $anv=substr($data[$numqry][$n], 6, 4);
                        $tim="";
                        if (isset($data[$numqry][$n][10])) $tim=substr($data[$numqry][$n],10);
                        $data[$numqry][$n]="$anv-$mes-$dia$tim";
                        // ya se reemplaza / con -, si tiene - en indices 2 y 5 voltear los numeros
                        //$data[$numqry][$n]==="-"
                    }
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            } else {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=trim($lineArr[$n]);
                    $data[$numqry][$n]=str_replace(["\"","\\"],["'",""], $data[$numqry][$n]);
                    switch($fields[$n]) {
                        case "uuid":
                            $data[$numqry][$n]=strtoupper($data[$numqry][$n]);
                            $currUUID=$data[$numqry][$n];
                            $iniUUID=substr($currUUID,0,4);
                            $endUUID=substr($currUUID,-4);
                            $currUUID=$iniUUID."..".$endUUID;
                            break;
                        case "tipocomprobante":
                            $data[$numqry][$n] = iconv('UTF-8', 'ASCII//TRANSLIT', strtolower($data[$numqry][$n]));
                            break;
                        case "rfcemisor":
                        case "rfcreceptor":
                            $data[$numqry][$n]=strtoupper($data[$numqry][$n]);
                            break;
                    } 
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            }
        }
        $numqry++;
        $result=DBi::query($beginquery."(".$dataquery.")", new class extends DBObject { public function __construct() { $this->tablename = $tbnm; } });
        //$dataquery.=")";
        if ($result&&DBi::$affected_rows>0) {
            $inserted++;
            $info=trim(DBi::$query_info);
            if (isset($info[5])) $infos[$currUUID]=$info;
            $warn=trim(DBi::$warnings);
            if (isset($warn[5])) $warnings[$currUUID]=$warn;
        } else {
            $errno=DBi::$errno;
            $error=DBi::$error;
            if ($errno==1062) $duplicates++;
            else if ($errno==1048 && $error==="Column 'uuid' cannot be null") $noneuuid++;
            else if ($errno==1064) $errors[$currUUID]=$error."\n".$beginquery."(".$dataquery.")";
            else $errors[$currUUID]=$errno.":".$error;
        }
    }
    asort($errors);
    asort($warnings);
    asort($infos);
    $duration = (microtime(true) - $time_start);
    //$query=$beginquery.$dataquery;
    echo json_encode(["result"=>$inserted>0?"success":"error","loops"=>$loops,"numqry"=>$numqry,"inserted"=>$inserted??0,"info"=>$infos,"errors"=>$errors,"warnings"=>$warnings,"table"=>$tablename,"fields"=>$fields,"duplicates"=>$duplicates,"noneuuid"=>$noneuuid,"duration"=>$duration]);
}
function isAltaPrv() {
    return isset($_POST["accion"])&&$_POST["accion"]==="altaprv";
}
function doAltaPrv() {
    if (!isset($_POST["name"])) return echoError("No se recibió nombre de datos", $_POST);
    if (!isset($_FILES)) return echoError("No se recibieron archivos", $_POST);
    if (empty($_FILES["file"])) return echoError("No se recibieron archivos validos", $_POST+$_FILES);
    $file=$_FILES["file"];
    if (!empty($file["error"])) return echoError("Ocurrio un error durante la captura del archivo", $file);
    if ($file["size"]==0) return echoError("Carga de archivo sin datos", $file);
    $handle=fopen($file["tmp_name"], "r");
    if ($handle===FALSE) return echoError("No se pudo leer el archivo", $file);

    $time_start = microtime(true);
    $fields=[]; $data=[]; $isFirst=true; $loops=0; $numqry=0; $inserted=0;
    $tablename=$_POST["name"];
    $beginquery="INSERT INTO $tablename ";
    $duplicates=0;
    $noneuuid=0;
    $errors=[];
    $warnings=[];
    $infos=[];
    while (($lineArr=fgetcsv($handle,1000,","))!==FALSE) {
        $loops++;
        if (!isset($lineArr[0])) continue;
        if (isset($lineArr[5])) {
            return echoError("El archivo debe contar únicamente con las cinco columnas UUID, FECHA, TIPOCOMPROBANTE, RFCEMISOR Y RFCRECEPTOR", ["lineArr"=>$lineArr]+$_POST);
        }
        if ($isFirst) { // la primer fila, de encabezados, deben corresponder a campos validos en la tabla <name>
            $fields=$lineArr; // preg_replace("/[^a-v,]/", "",mb_strtolower(implode(",",$lineArr)));
            $fieldsTxt=preg_replace("/[^a-z\,]/", "",mb_strtolower(implode(",",$fields)));
            $fields=explode(",",$fieldsTxt);
            if ($fieldsTxt!=="uuid,fecha,tipocomprobante,rfcemisor,rfcreceptor") {
                return echoError("El archivo debe incluir los encabezados de columna UUID, FECHA, TIPOCOMPROBANTE, RFCEMISOR Y RFCRECEPTOR.\n$fieldsTxt", ["lineArr"=>$lineArr, "fieldsTxt"=>$fieldsTxt]+$_POST);
            }
            //array_walk($fields, function(&$item,$idx) { $item=preg_replace("/[^a-z]/", "",mb_strtolower($item));});
            $beginquery.="(".$fieldsTxt.") VALUES ";
            $isFirst=false; continue;
        }
        // if (isset($dataquery[0])) $dataquery.=", "; $dataquery.="(";
        $data[$numqry]=[];
        $currUUID="";
        $dataquery="";
        for ($n=0;isset($fields[$n]);$n++) {
            if ($n>0) $dataquery.=",";
            if ($fields[$n]==="fecha") {
                if (isset($lineArr[$n][0])) {
                    // Verificar formato de fecha. Detectar si viene con el formato Excel default 'dd/mm/aaaa hh:mm' y convertirlo al formato valido 'aaaa-mm-dd hh:mm'
                    $data[$numqry][$n]=str_replace(["/","T"],["-"," "], $lineArr[$n]);
                    $data[$numqry][$n]=preg_replace("/[^0-9\:\- ]/","",$data[$numqry][$n]);
                    if ($data[$numqry][$n][2]==="-" && $data[$numqry][$n][5]==="-") {
                        $dia=substr($data[$numqry][$n], 0, 2);
                        $mes=substr($data[$numqry][$n], 3, 2);
                        $anv=substr($data[$numqry][$n], 6, 4);
                        $tim="";
                        if (isset($data[$numqry][$n][10])) $tim=substr($data[$numqry][$n],10);
                        $data[$numqry][$n]="$anv-$mes-$dia$tim";
                        // ya se reemplaza / con -, si tiene - en indices 2 y 5 voltear los numeros
                        //$data[$numqry][$n]==="-"
                    }
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            } else {
                if (isset($lineArr[$n][0])) {
                    $data[$numqry][$n]=trim($lineArr[$n]);
                    $data[$numqry][$n]=str_replace(["\"","\\"],["'",""], $data[$numqry][$n]);
                    switch($fields[$n]) {
                        case "uuid":
                            $data[$numqry][$n]=strtoupper($data[$numqry][$n]);
                            $currUUID=$data[$numqry][$n];
                            $iniUUID=substr($currUUID,0,4);
                            $endUUID=substr($currUUID,-4);
                            $currUUID=$iniUUID."..".$endUUID;
                            break;
                        case "tipocomprobante":
                            $data[$numqry][$n] = iconv('UTF-8', 'ASCII//TRANSLIT', strtolower($data[$numqry][$n]));
                            break;
                        case "rfcemisor":
                        case "rfcreceptor":
                            $data[$numqry][$n]=strtoupper($data[$numqry][$n]);
                            break;
                    } 
                    $dataquery.="\"".$data[$numqry][$n]."\"";
                } else {
                    $data[$numqry][$n]=null;
                    $dataquery.="null";
                }
            }
        }
        $numqry++;
        $result=DBi::query($beginquery."(".$dataquery.")", new class extends DBObject { public function __construct() { $this->tablename = $tbnm; } });
        //$dataquery.=")";
        if ($result&&DBi::$affected_rows>0) {
            $inserted++;
            $info=trim(DBi::$query_info);
            if (isset($info[5])) $infos[$currUUID]=$info;
            $warn=trim(DBi::$warnings);
            if (isset($warn[5])) $warnings[$currUUID]=$warn;
        } else {
            $errno=DBi::$errno;
            $error=DBi::$error;
            if ($errno==1062) $duplicates++;
            else if ($errno==1048 && $error==="Column 'uuid' cannot be null") $noneuuid++;
            else if ($errno==1064) $errors[$currUUID]=$error."\n".$beginquery."(".$dataquery.")";
            else $errors[$currUUID]=$errno.":".$error;
        }
    }
    asort($errors);
    asort($warnings);
    asort($infos);
    $duration = (microtime(true) - $time_start);
    //$query=$beginquery.$dataquery;
    echo json_encode(["result"=>$inserted>0?"success":"error","loops"=>$loops,"numqry"=>$numqry,"inserted"=>$inserted??0,"info"=>$infos,"errors"=>$errors,"warnings"=>$warnings,"table"=>$tablename,"fields"=>$fields,"duplicates"=>$duplicates,"noneuuid"=>$noneuuid,"duration"=>$duration]);
}
function isConsulta() {
    return isset($_POST["accion"])&&$_POST["accion"]==="consulta";
}
function doConsulta() {
    global $query;
    $tablename=$_POST["tablename"]??null;
    $registros=$_POST["registros"]??10;
    $pagina=$_POST["pagina"]??1;
    $orden=$_POST["sortBy"]??"";
    $query="SELECT * FROM $tablename";
    $maxQuery="SELECT count(1) n from $tablename";

    if (isset($orden[0])) $query.=" ORDER BY ".$orden;
    $result=DBi::query($maxQuery);
    $max=0;
    if ($result) {
        $max = +$result->fetch_assoc()["n"];
        $result->close();
    }
    $totpag=intdiv($max,$registros);
    if (($max%$registros)>0) $totpag++;
    if ($pagina>$totpag) $pagina=$totpag;
    $query.=" LIMIT ";
    if ($pagina==1) $query.="$registros";
    else {
        $offset=$registros*($pagina-1);
        $query.="$offset,$registros";
    }

    $result=DBi::query($query);
    $data=[];
    $info=[];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[]=$row;
        }
        $result->close();
    }
    if (isset(DBi::$query_info)) $info=DBi::$query_info;
    echo json_encode(["result"=>"success","data"=>$data,"info"=>$info,"query"=>$query,"totpag"=>$totpag]);
}
function isConsultaB() {
    return isset($_POST["accion"])&&$_POST["accion"]==="consulta_b";
}
function doConsultaB() {
    global $query;
    $tablename=$_POST["tablename"]??null;
    $registros=$_POST["registros"]??10;
    $pagina=$_POST["pagina"]??1;
    $orden=$_POST["sortBy"]??"";
    $fieldnames=$_POST["fieldnames"]??"*";
    $query="SELECT $fieldnames FROM $tablename";
    $maxQuery="SELECT count(1) n from $tablename";

    if (isset($orden[0])) $query.=" ORDER BY ".$orden;
    $result=DBi::query($maxQuery);
    $max=0;
    if ($result) {
        $max = +$result->fetch_assoc()["n"];
        $result->close();
    }
    $totpag=intdiv($max,$registros);
    if (($max%$registros)>0) $totpag++;
    if ($pagina>$totpag) $pagina=$totpag;
    $query.=" LIMIT ";
    if ($pagina==1) $query.="$registros";
    else {
        $offset=$registros*($pagina-1);
        $query.="$offset,$registros";
    }

    $result=DBi::query($query);
    $data=[];
    $info=[];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[]=$row;
        }
        $result->close();
    }
    if (isset(DBi::$query_info)) $info=DBi::$query_info;
    echo json_encode(["result"=>"success","data"=>$data,"info"=>$info,"query"=>$query,"totpag"=>$totpag]);
}
function isConsultaC() {
    return isset($_POST["accion"])&&$_POST["accion"]==="consulta_c";
}
function doConsultaC() {
    global $query;
    $tablename=$_POST["tablename"]??null;
    $registros=$_POST["registros"]??10;
    $pagina=$_POST["pagina"]??1;
    $orden=$_POST["sortBy"]??"";
    $fieldnames=$_POST["fieldnames"]??"*";
    $query="SELECT $fieldnames FROM $tablename";
    $maxQuery="SELECT count(1) n from $tablename";

    if (isset($orden[0])) $query.=" ORDER BY ".$orden;
    $result=DBi::query($maxQuery);
    $max=0;
    if ($result) {
        $max = +$result->fetch_assoc()["n"];
        $result->close();
    }
    $totpag=intdiv($max,$registros);
    if (($max%$registros)>0) $totpag++;
    if ($pagina>$totpag) $pagina=$totpag;
    $query.=" LIMIT ";
    if ($pagina==1) $query.="$registros";
    else {
        $offset=$registros*($pagina-1);
        $query.="$offset,$registros";
    }

    $result=DBi::query($query);
    $data=[];
    $info=[];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[]=$row;
        }
        $result->close();
    }
    if (isset(DBi::$query_info)) $info=DBi::$query_info;
    echo json_encode(["result"=>"success","data"=>$data,"info"=>$info,"query"=>$query,"totpag"=>$totpag]);
}
function isConsultaD() {
    return isset($_POST["accion"])&&$_POST["accion"]==="consulta_d";
}
function doConsultaD() {
    global $query;
    $tablename=$_POST["tablename"]??null;
    $registros=$_POST["registros"]??10;
    $pagina=$_POST["pagina"]??1;
    $orden=$_POST["sortBy"]??"";
    $fieldnames=$_POST["fieldnames"]??"*";
    $ini=$_POST["finicio"]??"";
    $fin=$_POST["ffin"]??"";
    $emp=$_POST["empresa"]??"";
    $tip=$_POST["tipo"]??"";
    $query="SELECT $fieldnames FROM $tablename";
    $maxQuery="SELECT count(1) n from $tablename";
    $where="";
    if (isset($ini[0])&&isset($fin[0])) $where.="fecha BETWEEN \"$ini\" AND \"$fin\"";
    else if (isset($ini[0])) $where.="fecha>\"$ini\"";
    else if (isset($fin[0])) $where.="fecha<\"$fin\"";
    if (isset($emp[0])) $where.=(isset($where[0])?" AND ":"")."(rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\")";
    if (isset($tip[0])) {
        $where.=(isset($where[0])?" AND ":"")."tipoComprobante";
        if ($tip==="otros") $where.=" not in ('ingreso','nomina','egreso','gasto')";
        else $where.="=\"$tip\"";
    }
    if (isset($where[0])) {
        $query.=" WHERE $where";
        $maxQuery.=" WHERE $where";
    }

    if (isset($orden[0])) $query.=" ORDER BY ".$orden;
    $result=DBi::query($maxQuery);
    $max=0;
    if ($result) {
        $max = +$result->fetch_assoc()["n"];
        $result->close();
    }
    $data=[];
    $info=[];
    $totpag=0;
    if ($max>0) {
        $totpag=intdiv($max,$registros);
        if (($max%$registros)>0) $totpag++;
        if ($pagina>$totpag) $pagina=$totpag;
        $query.=" LIMIT ";
        if ($pagina==1) $query.="$registros";
        else {
            $offset=$registros*($pagina-1);
            $query.="$offset,$registros";
        }

        $result=DBi::query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[]=$row;
            }
            $result->close();
        }
        if (isset(DBi::$query_info)) $info=DBi::$query_info;
    }
    echo json_encode(["result"=>"success","data"=>$data,"info"=>$info,"query"=>$query,"totReg"=>$max,"maxQuery"=>$maxQuery,"totpag"=>$totpag]);
}
function isConsultaPrv() {
    return isset($_POST["accion"])&&$_POST["accion"]==="consultaprv";
}
function doConsultaPrv() {
    global $query;
    $tablename=$_POST["tablename"]??null;
    $registros=$_POST["registros"]??10;
    $pagina=$_POST["pagina"]??1;
    $orden=$_POST["sortBy"]??"";
    $fieldnames=$_POST["fieldnames"]??"*";
    $ini=$_POST["finicio"]??"";
    $fin=$_POST["ffin"]??"";
    $emp=$_POST["empresa"]??"";
    $tip=$_POST["tipo"]??"";
    $query="SELECT $fieldnames FROM $tablename";
    $maxQuery="SELECT count(1) n from $tablename";
    $where="";
    if (isset($ini[0])&&isset($fin[0])) $where.="fecha BETWEEN \"$ini\" AND \"$fin\"";
    else if (isset($ini[0])) $where.="fecha>\"$ini\"";
    else if (isset($fin[0])) $where.="fecha<\"$fin\"";
    if (isset($emp[0])) $where.=(isset($where[0])?" AND ":"")."(rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\")";
    if (isset($tip[0])) {
        $where.=(isset($where[0])?" AND ":"")."tipoComprobante";
        if ($tip==="otros") $where.=" not in ('ingreso','nomina','egreso','gasto')";
        else $where.="=\"$tip\"";
    }
    if (isset($where[0])) {
        $query.=" WHERE $where";
        $maxQuery.=" WHERE $where";
    }

    if (isset($orden[0])) $query.=" ORDER BY ".$orden;
    $result=DBi::query($maxQuery);
    $max=0;
    if ($result) {
        $max = +$result->fetch_assoc()["n"];
        $result->close();
    }
    $data=[];
    $info=[];
    $totpag=0;
    if ($max>0) {
        $totpag=intdiv($max,$registros);
        if (($max%$registros)>0) $totpag++;
        if ($pagina>$totpag) $pagina=$totpag;
        $query.=" LIMIT ";
        if ($pagina==1) $query.="$registros";
        else {
            $offset=$registros*($pagina-1);
            $query.="$offset,$registros";
        }

        $result=DBi::query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[]=$row;
            }
            $result->close();
        }
        if (isset(DBi::$query_info)) $info=DBi::$query_info;
    }
    echo json_encode(["result"=>"success","data"=>$data,"info"=>$info,"query"=>$query,"totReg"=>$max,"maxQuery"=>$maxQuery,"totpag"=>$totpag]);
}
function isComparaAmbas() {
    return isset($_POST["accion"])&&$_POST["accion"]==="comparaAmbas";
}
function doComparaAmbas() {
    if (!isset($_POST["finicio"])) return echoError("No se recibió fecha inicial", $_POST);
    if (!isset($_POST["ffin"])) return echoError("No se recibió fecha final", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $info=[];

    // En el rango mencionado se requiere:
    // Numero de facturas del SAT
    $qTotSat="SELECT COUNT(1) n FROM comparasat WHERE fecha BETWEEN \"$ini\" AND \"$fin\"";
    $result=DBi::query($qTotSat);
    $totsat=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totsat=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totsat"]=DBi::$query_info;
    // Numero de facturas de Avance
    $qTotAvn="SELECT COUNT(1) n FROM comparaavance WHERE fecha BETWEEN \"$ini\" AND \"$fin\"";
    $result=DBi::query($qTotAvn);
    $totavn=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totavn=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totavn"]=DBi::$query_info;
    // Numero de facturas con uuid en Avance q tambien existen en SAT con cantidades iguales
    $qNumAmbos1="SELECT COUNT(1) n FROM comparaavance a INNER JOIN comparasat s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE a.subtotal=(s.subtotal-s.descuento) AND a.total=s.total AND a.impuestos=(s.trasladoIVA-s.retencionISR-s.retencionIVA) AND a.fecha BETWEEN \"$ini\" and \"$fin\"";
    $result=DBi::query($qNumAmbos1);
    $numAmbos1=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $numAmbos1=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["ambos1"]=DBi::$query_info;
    // Numero de facturas con uuid en Avance q tambien existen en SAT sin cantidades
    $qNumAmbos2="SELECT COUNT(1) n FROM comparaavance a INNER JOIN comparasat s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE (a.subtotal IS NULL OR a.total IS NULL OR a.impuestos IS NULL) AND a.fecha BETWEEN \"$ini\" and \"$fin\"";
    $result=DBi::query($qNumAmbos2);
    $numAmbos2=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $numAmbos2=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["ambos2"]=DBi::$query_info;

    $maxNum=2000;
    // Facturas que solo existen en Avance
    $qSoloAvance="SELECT UPPER(uuid) uuid,fecha,remision,codigoReceptor receptor,tipocomprobante tipo,trim(subtotal)+0 subtotal,trim(impuestos)+0 impuestos, trim(total)+0 total FROM comparaavance WHERE fecha BETWEEN \"$ini\" and \"$fin\" AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparasat WHERE fecha BETWEEN \"$ini\" and \"$fin\") ORDER BY tipo,fecha";
    $result=DBi::query($qSoloAvance);
    $soloAvance=[];
    $numSoloAvance=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloAvance[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloAvance=0;
    }
    if (isset(DBi::$query_info)) $info["soloavn"]=DBi::$query_info;
    // Facturas que solo existen en SAT
    $qSoloSat="SELECT UPPER(s.uuid) uuid,s.fecha,CONCAT(\"F-\",s.serie,\"00\",s.folio) remision,COALESCE(c.codigo,s.rfcreceptor) receptor,s.tipoComprobante tipo,trim(s.subtotal-s.descuento)+0 subtotal, trim(s.trasladoIVA-s.retencionISR-s.retencionIVA)+0 impuestos, trim(s.total)+0 total FROM comparasat s LEFT JOIN (SELECT codigo,rfc from clientes GROUP BY rfc) c ON s.rfcreceptor=c.rfc WHERE s.fecha BETWEEN \"$ini\" AND \"$fin\" AND upper(s.uuid) NOT IN (SELECT upper(uuid) FROM comparaavance WHERE fecha BETWEEN \"$ini\" AND \"$fin\") ORDER BY tipo,s.fecha";
    $result=DBi::query($qSoloSat);
    $soloSat=[];
    $numSoloSat=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloSat[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloSat=0;
    }
    if (isset(DBi::$query_info)) $info["solosat"]=DBi::$query_info;
    // Factura que existen en Avance y SAT pero que no coinciden las cantidades
    $qDiferentes="SELECT UPPER(a.uuid) uuid, s.fecha, a.remision, CONCAT(a.codigoReceptor,' (',s.rfcReceptor,')') receptor, s.tipoComprobante tipo, IF(a.subtotal=(s.subtotal-s.descuento),trim(a.subtotal)+0,CONCAT((trim(a.subtotal)+0),'!=',(trim(s.subtotal-s.descuento)+0))) subtotal, IF(a.impuestos=(s.trasladoIVA-s.retencionISR-s.retencionIVA),trim(a.impuestos)+0,CONCAT((trim(a.impuestos)+0),'!=',(trim(s.trasladoIVA-s.retencionISR-s.retencionIVA)+0))) impuestos, IF(a.total=s.total,trim(a.total)+0,CONCAT((trim(a.total)+0),'!=',(trim(s.total)+0))) total FROM comparaavance a INNER JOIN comparasat s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE (a.subtotal!=(s.subtotal-s.descuento) OR a.total!=s.total OR a.impuestos!=(s.trasladoIVA-s.retencionISR-s.retencionIVA)) AND a.fecha BETWEEN \"$ini\" AND \"$fin\"";
    $result=DBi::query($qDiferentes);
    $diferentes=[];
    $numDiferentes=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $diferentes[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numDiferentes=0;
    }
    if (isset(DBi::$query_info)) $info["diferentes"]=DBi::$query_info;
    // UUID's encontrados solo en sat
    echo json_encode(["result"=>"success","ini"=>$ini, "fin"=>$fin,"affectedRows"=>DBi::$affected_rows??0,"queryInfo"=>$info,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","totsat"=>$totsat,"totavn"=>$totavn,"numAmbos1"=>$numAmbos1,"numAmbos2"=>$numAmbos2,"numSoloAvance"=>$numSoloAvance,"soloAvance"=>$soloAvance,"numSoloSat"=>$numSoloSat,"soloSat"=>$soloSat,"numDiferentes"=>$numDiferentes,"diferentes"=>$diferentes,"queries"=>["totsat"=>$qTotSat,"totavn"=>$qTotAvn,"numAmbos1"=>$qNumAmbos1,"numAmbos2"=>$qNumAmbos2,"soloAvance"=>$qSoloAvance,"soloSat"=>$qSoloSat,"diferentes"=>$qDiferentes]]);
}
function isComparaAmbasB() {
    return isset($_POST["accion"])&&$_POST["accion"]==="comparaAmbas_b";
}
function doComparaAmbasB() {
    if (!isset($_POST["finicio"])) return echoError("No se recibió fecha inicial", $_POST);
    if (!isset($_POST["ffin"])) return echoError("No se recibió fecha final", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $info=[];

    // En el rango mencionado se requiere:
    // Numero de facturas del SAT
    $qTotSat="SELECT COUNT(1) n FROM comparasat WHERE fecha BETWEEN \"$ini\" AND \"$fin\"";
    $result=DBi::query($qTotSat);
    $totsat=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totsat=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totsat"]=DBi::$query_info;
    // Numero de facturas de Avance
    $qTotAvn="SELECT COUNT(1) n FROM comparaavance WHERE fecha BETWEEN \"$ini\" AND \"$fin\"";
    $result=DBi::query($qTotAvn);
    $totavn=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totavn=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totavn"]=DBi::$query_info;
    // Numero de facturas con uuid en Avance q tambien existen en SAT con cantidades iguales
    $qNumAmbos="SELECT COUNT(1) n FROM comparaavance a INNER JOIN comparasat s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE a.fecha BETWEEN \"$ini\" and \"$fin\"";
    $result=DBi::query($qNumAmbos);
    $numAmbos=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $numAmbos=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["ambos"]=DBi::$query_info;

    $maxNum=2000;
    $qSoloAvance="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo FROM comparaavance WHERE fecha BETWEEN \"$ini\" and \"$fin\" AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparasat WHERE fecha BETWEEN \"$ini\" and \"$fin\") ORDER BY tipo,fecha";
    $result=DBi::query($qSoloAvance);
    $soloAvance=[];
    $numSoloAvance=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloAvance[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloAvance=0;
    }
    if (isset(DBi::$query_info)) $info["soloavn"]=DBi::$query_info;
    $qSoloSat="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo FROM comparasat WHERE fecha BETWEEN \"$ini\" AND \"$fin\" AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparaavance WHERE fecha BETWEEN \"$ini\" AND \"$fin\") ORDER BY tipo,fecha";
    $result=DBi::query($qSoloSat);
    $soloSat=[];
    $numSoloSat=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloSat[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloSat=0;
    }
    if (isset(DBi::$query_info)) $info["solosat"]=DBi::$query_info;
    echo json_encode(["result"=>"success","ini"=>$ini, "fin"=>$fin,"affectedRows"=>DBi::$affected_rows??0,"queryInfo"=>$info,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","totsat"=>$totsat,"totavn"=>$totavn,"numAmbos"=>$numAmbos,"numSoloAvance"=>$numSoloAvance,"soloAvance"=>$soloAvance,"numSoloSat"=>$numSoloSat,"soloSat"=>$soloSat,"queries"=>["totsat"=>$qTotSat,"totavn"=>$qTotAvn,"numAmbos"=>$qNumAmbos,"soloAvance"=>$qSoloAvance,"soloSat"=>$qSoloSat]]);
}
function isComparaAmbasC() {
    return isset($_POST["accion"])&&$_POST["accion"]==="comparaAmbas_c";
}
function doComparaAmbasC() {
    if (!isset($_POST["finicio"])) return echoError("No se recibió fecha inicial", $_POST);
    if (!isset($_POST["ffin"])) return echoError("No se recibió fecha final", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $emp=$_POST["empresa"];
    $info=[];

    if (isset($emp[0])) {
        $empQry=" (rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\") AND";
        $empQryA=" (a.rfcEmisor=\"$emp\" or a.rfcReceptor=\"$emp\") AND";
    } else {
        $empQry="";
        $empQryA="";
    }
    $dateQry=" fecha BETWEEN \"$ini\" AND \"$fin\"";
    $dateQryA=" a.fecha BETWEEN \"$ini\" AND \"$fin\"";
    // En el rango mencionado se requiere:
    // Numero de facturas del SAT
    $qTotSat="SELECT COUNT(1) n FROM comparasat WHERE$empQry$dateQry";
    $result=DBi::query($qTotSat);
    $totsat=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totsat=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totsat"]=DBi::$query_info;
    // Numero de facturas de Avance
    $qTotAvn="SELECT COUNT(1) n FROM comparaavance WHERE$empQry$dateQry";
    $result=DBi::query($qTotAvn);
    $totavn=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totavn=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totavn"]=DBi::$query_info;
    // Numero de facturas con uuid en Avance q tambien existen en SAT con cantidades iguales
    $qNumAmbos="SELECT COUNT(1) n FROM comparaavance a INNER JOIN comparasat s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE$empQryA$dateQryA";
    $result=DBi::query($qNumAmbos);
    $numAmbos=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $numAmbos=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["ambos"]=DBi::$query_info;

    $maxNum=2000;
    $qSoloAvance="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo,UPPER(rfcEmisor) rfcEmisor,UPPER(rfcReceptor) rfcReceptor FROM comparaavance WHERE$empQry$dateQry AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparasat WHERE$empQry$dateQry) ORDER BY tipo,fecha";
    $result=DBi::query($qSoloAvance);
    $soloAvance=[];
    $numSoloAvance=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloAvance[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloAvance=0;
    }
    if (isset(DBi::$query_info)) $info["soloavn"]=DBi::$query_info;
    $qSoloSat="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo,UPPER(rfcEmisor) rfcEmisor,UPPER(rfcReceptor) rfcReceptor FROM comparasat WHERE$empQry$dateQry AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparaavance WHERE$empQry$dateQry) ORDER BY tipo,fecha";
    $result=DBi::query($qSoloSat);
    $soloSat=[];
    $numSoloSat=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloSat[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloSat=0;
    }
    if (isset(DBi::$query_info)) $info["solosat"]=DBi::$query_info;
    echo json_encode(["result"=>"success","ini"=>$ini, "fin"=>$fin,"affectedRows"=>DBi::$affected_rows??0,"queryInfo"=>$info,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","totsat"=>$totsat,"totavn"=>$totavn,"numAmbos"=>$numAmbos,"numSoloAvance"=>$numSoloAvance,"soloAvance"=>$soloAvance,"numSoloSat"=>$numSoloSat,"soloSat"=>$soloSat,"queries"=>["totsat"=>$qTotSat,"totavn"=>$qTotAvn,"numAmbos"=>$qNumAmbos,"soloAvance"=>$qSoloAvance,"soloSat"=>$qSoloSat]]);
}
function isEliminaD() {
    return isset($_POST["accion"])&&$_POST["accion"]==="elimina_d";
}
function doEliminaD() {
    if (!isset($_POST["tabla"])) return echoError("El origen de datos no fue recibida", $_POST);
    if (!isset($_POST["finicio"])) return echoError("La fecha inicial no fue recibida", $_POST);
    if (!isset($_POST["ffin"])) return echoError("La fecha final no fue recibida", $_POST);
    $tbnm=$_POST["tabla"];
    if ($tbnm!=="comparasat"&&$tbnm!=="comparaavance") return echoError("El origen de datos fue manipulado", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $emp=$_POST["empresa"];
    $tip=$_POST["tipo"]??"";
    if (isset($emp[0])) $empQry=" (rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\") AND";
    else $empQry="";
    if (isset($tip[0])) {
        $empQry.=" tipoComprobante";
        if ($tip==="otros") $empQry.=" not in ('ingreso','nomina','egreso','gasto')";
        else $empQry.="=\"$tip\"";
        $empQry.=" AND";
    }
    
    $query="DELETE FROM $tbnm WHERE$empQry fecha BETWEEN \"$ini\" AND \"$fin\" and id>0";
    if (DBi::query($query, new class extends DBObject { public function __construct() { $this->tablename = $tbnm; } })) {
        echo json_encode(["result"=>"exito","title"=>"Datos Eliminados","message"=>"Se eliminaron ".(DBi::$affected_rows??0)." registros","query"=>$query,"info"=>DBi::$query_info??"","affectedRows"=>DBi::$affected_rows??0,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","numRows"=>DBi::$num_rows]);
    } else echo json_encode(["result"=>"error","message"=>"Los datos no fueron eliminados","query"=>$query,"info"=>DBi::$query_info??"","affectedRows"=>DBi::$affected_rows??0,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??""]);
}
function isEliminaPrv() {
    return isset($_POST["accion"])&&$_POST["accion"]==="eliminaprv";
}
function doEliminaPrv() {
    if (!isset($_POST["tabla"])) return echoError("El origen de datos no fue recibida", $_POST);
    if (!isset($_POST["finicio"])) return echoError("La fecha inicial no fue recibida", $_POST);
    if (!isset($_POST["ffin"])) return echoError("La fecha final no fue recibida", $_POST);
    $tbnm=$_POST["tabla"];
    if ($tbnm!=="comparasatprv"&&$tbnm!=="comparaavanceprv") return echoError("El origen de datos fue manipulado", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $emp=$_POST["empresa"];
    $tip=$_POST["tipo"]??"";
    if (isset($emp[0])) $empQry=" (rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\") AND";
    else $empQry="";
    if (isset($tip[0])) {
        $empQry.=" tipoComprobante";
        if ($tip==="otros") $empQry.=" not in ('ingreso','nomina','egreso','gasto')";
        else $empQry.="=\"$tip\"";
        $empQry.=" AND";
    }
    
    $query="DELETE FROM $tbnm WHERE$empQry fecha BETWEEN \"$ini\" AND \"$fin\" and id>0";
    if (DBi::query($query, new class extends DBObject { public function __construct() { $this->tablename = $tbnm; } })) {
        echo json_encode(["result"=>"exito","title"=>"Datos Eliminados","message"=>"Se eliminaron ".(DBi::$affected_rows??0)." registros","query"=>$query,"info"=>DBi::$query_info??"","affectedRows"=>DBi::$affected_rows??0,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","numRows"=>DBi::$num_rows]);
    } else echo json_encode(["result"=>"error","message"=>"Los datos no fueron eliminados","query"=>$query,"info"=>DBi::$query_info??"","affectedRows"=>DBi::$affected_rows??0,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??""]);
}
function isComparaAmbasD() {
    return isset($_POST["accion"])&&$_POST["accion"]==="comparaAmbas_d";
}
function doComparaAmbasD() {
    if (!isset($_POST["finicio"])) return echoError("No se recibió fecha inicial", $_POST);
    if (!isset($_POST["ffin"])) return echoError("No se recibió fecha final", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $emp=$_POST["empresa"];
    $tip=$_POST["tipo"]??"";
    $info=[];

    if (isset($emp[0])) {
        $empQry=" (rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\") AND";
        $empQryA=" (a.rfcEmisor=\"$emp\" or a.rfcReceptor=\"$emp\") AND";
    } else {
        $empQry="";
        $empQryA="";
    }
    if (isset($tip[0])) {
        $empAux="tipoComprobante";
        if ($tip==="otros") $empAux.=" not in ('ingreso','nomina','egreso','gasto')";
        else $empAux.="=\"$tip\"";
        $empQry.=" $empAux AND";
        $empQryA.=" a.$empAux AND";
    }
    $dateQry=" fecha BETWEEN \"$ini\" AND \"$fin\"";
    $dateQryA=" a.fecha BETWEEN \"$ini\" AND \"$fin\"";
    // En el rango mencionado se requiere:
    // Numero de facturas del SAT
    $qTotSat="SELECT COUNT(1) n FROM comparasat WHERE$empQry$dateQry";
    $result=DBi::query($qTotSat);
    $totsat=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totsat=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totsat"]=DBi::$query_info;
    // Numero de facturas de Avance
    $qTotAvn="SELECT COUNT(1) n FROM comparaavance WHERE$empQry$dateQry";
    $result=DBi::query($qTotAvn);
    $totavn=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totavn=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totavn"]=DBi::$query_info;
    // Numero de facturas con uuid en Avance q tambien existen en SAT con cantidades iguales
    $qNumAmbos="SELECT COUNT(1) n FROM comparaavance a INNER JOIN comparasat s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE$empQryA$dateQryA";
    $result=DBi::query($qNumAmbos);
    $numAmbos=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $numAmbos=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["ambos"]=DBi::$query_info;

    $maxNum=2000;
    $qSoloAvance="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo,UPPER(rfcEmisor) rfcEmisor,UPPER(rfcReceptor) rfcReceptor FROM comparaavance WHERE$empQry$dateQry AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparasat WHERE$empQry$dateQry) ORDER BY tipo,fecha";
    $result=DBi::query($qSoloAvance);
    $soloAvance=[];
    $numSoloAvance=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloAvance[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloAvance=0;
    }
    if (isset(DBi::$query_info)) $info["soloavn"]=DBi::$query_info;
    $qSoloSat="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo,UPPER(rfcEmisor) rfcEmisor,UPPER(rfcReceptor) rfcReceptor FROM comparasat WHERE$empQry$dateQry AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparaavance WHERE$empQry$dateQry) ORDER BY tipo,fecha";
    $result=DBi::query($qSoloSat);
    $soloSat=[];
    $numSoloSat=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloSat[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloSat=0;
    }
    if (isset(DBi::$query_info)) $info["solosat"]=DBi::$query_info;
    echo json_encode(["result"=>"success","ini"=>$ini, "fin"=>$fin,"affectedRows"=>DBi::$affected_rows??0,"queryInfo"=>$info,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","totsat"=>$totsat,"totavn"=>$totavn,"numAmbos"=>$numAmbos,"numSoloAvance"=>$numSoloAvance,"soloAvance"=>$soloAvance,"numSoloSat"=>$numSoloSat,"soloSat"=>$soloSat,"queries"=>["totsat"=>$qTotSat,"totavn"=>$qTotAvn,"numAmbos"=>$qNumAmbos,"soloAvance"=>$qSoloAvance,"soloSat"=>$qSoloSat]]);
}
function isComparaAmbasPrv() {
    return isset($_POST["accion"])&&$_POST["accion"]==="comparaAmbasPrv";
}
function doComparaAmbasPrv() {
    if (!isset($_POST["finicio"])) return echoError("No se recibió fecha inicial", $_POST);
    if (!isset($_POST["ffin"])) return echoError("No se recibió fecha final", $_POST);
    $ini=$_POST["finicio"];
    $fin=$_POST["ffin"];
    $emp=$_POST["empresa"];
    $tip=$_POST["tipo"]??"";
    $info=[];

    if (isset($emp[0])) {
        $empQry=" (rfcEmisor=\"$emp\" or rfcReceptor=\"$emp\") AND";
        $empQryA=" (a.rfcEmisor=\"$emp\" or a.rfcReceptor=\"$emp\") AND";
    } else {
        $empQry="";
        $empQryA="";
    }
    if (isset($tip[0])) {
        $empAux="tipoComprobante";
        if ($tip==="otros") $empAux.=" not in ('ingreso','nomina','egreso','gasto')";
        else $empAux.="=\"$tip\"";
        $empQry.=" $empAux AND";
        $empQryA.=" a.$empAux AND";
    }
    $dateQry=" fecha BETWEEN \"$ini\" AND \"$fin\"";
    $dateQryA=" a.fecha BETWEEN \"$ini\" AND \"$fin\"";
    // En el rango mencionado se requiere:
    // Numero de facturas del SAT
    $qTotSat="SELECT COUNT(1) n FROM comparasatprv WHERE$empQry$dateQry";
    $result=DBi::query($qTotSat);
    $totsat=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totsat=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totsat"]=DBi::$query_info;
    // Numero de facturas de Avance
    $qTotAvn="SELECT COUNT(1) n FROM comparaavanceprv WHERE$empQry$dateQry";
    $result=DBi::query($qTotAvn);
    $totavn=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $totavn=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["totavn"]=DBi::$query_info;
    // Numero de facturas con uuid en Avance q tambien existen en SAT con cantidades iguales
    $qNumAmbos="SELECT COUNT(1) n FROM comparaavanceprv a INNER JOIN comparasatprv s ON UPPER(a.uuid)=UPPER(s.uuid) WHERE$empQryA$dateQryA";
    $result=DBi::query($qNumAmbos);
    $numAmbos=0;
    if ($result) {
        $row = $result->fetch_assoc();
        $numAmbos=+$row["n"];
        $result->close();
    }
    if (isset(DBi::$query_info)) $info["ambos"]=DBi::$query_info;

    $maxNum=2000;
    $qSoloAvance="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo,UPPER(rfcEmisor) rfcEmisor,UPPER(rfcReceptor) rfcReceptor FROM comparaavanceprv WHERE$empQry$dateQry AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparasatprv WHERE$empQry$dateQry) ORDER BY tipo,fecha";
    $result=DBi::query($qSoloAvance);
    $soloAvance=[];
    $numSoloAvance=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloAvance[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloAvance=0;
    }
    if (isset(DBi::$query_info)) $info["soloavn"]=DBi::$query_info;
    $qSoloSat="SELECT UPPER(uuid) uuid,fecha,tipoComprobante tipo,UPPER(rfcEmisor) rfcEmisor,UPPER(rfcReceptor) rfcReceptor FROM comparasatprv WHERE$empQry$dateQry AND upper(uuid) NOT IN (SELECT upper(uuid) FROM comparaavanceprv WHERE$empQry$dateQry) ORDER BY tipo,fecha";
    $result=DBi::query($qSoloSat);
    $soloSat=[];
    $numSoloSat=DBi::$num_rows;
    if ($result) {
        $num=0;
        while ($row = $result->fetch_assoc()) {
            $soloSat[]=$row;
            $num++;
            if ($num>=$maxNum) break;
        }
        $result->close();
        if ($num==0) $numSoloSat=0;
    }
    if (isset(DBi::$query_info)) $info["solosat"]=DBi::$query_info;
    echo json_encode(["result"=>"success","ini"=>$ini, "fin"=>$fin,"affectedRows"=>DBi::$affected_rows??0,"queryInfo"=>$info,"errors"=>DBi::$errors,"warnCount"=>DBi::$warning_count,"warnings"=>DBi::$warnings??"","totsat"=>$totsat,"totavn"=>$totavn,"numAmbos"=>$numAmbos,"numSoloAvance"=>$numSoloAvance,"soloAvance"=>$soloAvance,"numSoloSat"=>$numSoloSat,"soloSat"=>$soloSat,"queries"=>["totsat"=>$qTotSat,"totavn"=>$qTotAvn,"numAmbos"=>$qNumAmbos,"soloAvance"=>$qSoloAvance,"soloSat"=>$qSoloSat]]);
}
function echoError($message, $data=null) {
    echoJSDoc("error", $message, null, $data, "error"); 
    return false;
}
/*
function getProveedores() {
    require_once "clases/Proveedores.php";
    $prvObj=new Proveedores();
    $data=$prvObj->getData(false,"codigo,rfc");
    $retval=[];
    foreach ($data as $vals) {
        $retval[$vals["codigo"]]=$vals["rfc"];
    }
    return $retval;
}
*/
