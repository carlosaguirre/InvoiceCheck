<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
const GPO="gpo", PRV="prv", AGT="agt", CEE="cee", CEC="cec", CED="ced";
const TAB=[GPO=>"Grupo",PRV=>"Proveedores",AGT=>"Proveedores",CEE=>"ComExtExpediente",CEC=>"ComExtCatalogo",CED=>"ComExtDocumentos"];
//const NAM=[GPO=>"empresa",PRV=>"proveedor",CEE=>"expediente",CEC=>"catalogo",CED=>"documento"];
const TNAM=["foreign"=>"proveedor","customs"=>"proveedor","nvoexpd"=>"expediente","srchexp"=>"expediente","vwexpdn"=>"expediente"/*,"fixexpd"=>"expediente"*/];
global $detLog,$defOpt,$defOptAll;
$detLog=true;
// require_once "clases/Grupo.php";
// toDo: validar si no existe _SESSION[optDefaultValue] que se cierre sesion para que se vuelva a generar, o al menos que se recargue la pagina
$defOpt=$_SESSION['optDefaultValue']; // definido en Grupo en un momento de sesion previo
$defOptAll=($defOpt==="razon"?"Todas":"Todos");
if ($detLog) doclog("CONSULTAS COMEXT","comext",$_POST);
if (isActionService()) doActionService();
else {
    if ($hasUser) {
        //echo $username;
        $dt = new DateTime();
        $dia=$_now["ymd"];
        $path="C:\\Apache24\\htdocs\\invoice\\LOGS\\$dia\\";
        $isAdmin=($username==="admin");
        $names=["comext","error"];
        $sentLines=0;
        foreach ($names as $fn) {
            $absname="$path{$fn}.log";
            $headerLine="<H2>".strtoupper($fn).".LOG</H2><PRE>";
            if (file_exists($absname)) {
                $n=0;
                foreach (file($absname) as $line) {
                    if (isset($line[0])&&(($isAdmin&&empty($_GET["self"]))||($line[0]==="["&&strpos($line, $username)!==false))) {
                        if ($n==0) echo $headerLine;
                        echo $line.PHP_EOL;
                        $n++;
                    }
                }
                if ($n>0) {
                    echo "</PRE>";
                    $sentLines+=$n;
                } else if ($isAdmin) echo "{$headerLine}ZERO ADMIN REFERENCES</PRE>";
            } else if ($isAdmin) echo "{$headerLine}LOGFILE NOT FOUND</PRE>";
        }
        if ($sentLines==0&&!$isAdmin) echo "Forbidden access";
    } else echo "Forbidden Access!";
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    if (!hasUser()) {
        echo json_encode(["result"=>"refresh","action"=>"refresh"]);
        return;
    }
    switch($_POST["action"]) {
        case "Buscar":
        case "Consulta": doComExtView(); break;
        case "Guardar": doComExtSave(); break;
        case "Eliminar": doComExtDel(); break;
        case "Lista": doComExtBrowse(); break;
        default: echoJSDoc("error", "ERROR:Petición inválida", null, ["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__]+$_POST, "error");
    }
}
function getObj($prefixName) {
    if (is_array($prefixName)) {
        $objList=[];
        foreach ($prefixName as $val) $objList[$val]=getObj($val);
        return $objList;
    }
    $objName=$prefixName."Obj";
    $className=TAB[$prefixName]??null;
    if (!isset($className[0])) return null;
    $obj=$GLOBALS[$objName]??null;
    if (!isset($obj)) {
        require_once "clases/{$className}.php";
        $obj=new $className();
        $GLOBALS[$objName]=$obj;
    }
    return $obj;
}
function getData($prefixName,$where,$numRec=1) {
    global $detLog;
    if (/*$numRec==0*/empty($numRec)) return null;
    getObj($prefixName);
    // toDo: usar limit con numRec
    // toDo: agregar argumento offset para paginacion
    $data=$GLOBALS["{$prefixName}Obj"]->getData($where);
    if ($numRec==1 && isset($data[0])) {
        if ($detLog) doclog("getData","comext",["prefixName"=>$prefixName,"where"=>$where,"numRec"=>$numRec,"data"=>$data[0]]);
        return $data[0];
    }
    if ($numRec>0 && isset($data[$numRec])) {
        $retVal=array_slice($data, 0, $numRec);
        if ($detLog) doclog("getData","comext",["prefixName"=>$prefixName,"where"=>$where,"numRec"=>$numRec,"data"=>$retVal]);
        return $retVal;
    }
    if ($detLog) doclog("getData","comext",["prefixName"=>$prefixName,"where"=>$where,"numRec"=>$numRec,"dataLen"=>count($data),"data"=>$data]);
    return $data;
}
function getMem($prefixName, $recordId) {
    global $detLog;
    $memName=$prefixName."Mem";
    if (!isset($GLOBALS[$memName])) $GLOBALS[$memName]=[];
    else if ($detLog && isset($GLOBALS[$memName][$recordId])) doclog("getMem","comext",["prefixName"=>$prefixName,"recordId"=>$recordId]);
    return $GLOBALS[$memName][$recordId]??null;
}
function setMem($prefixName, $recordId, $value=null) {
    global $detLog;
    $memName=$prefixName."Mem";
    if (!isset($GLOBALS[$memName])) $GLOBALS[$memName]=[];
    if (isset($value)) {
        $GLOBALS[$memName][$recordId]=$value;
        if ($detLog) doclog("setMem","comext",["prefixName"=>$prefixName,"recordId"=>$recordId]);
    } else {
        unset($GLOBALS[$memName][$recordId]);
        if ($detLog) doclog("unsetMem","comext",["prefixName"=>$prefixName,"recordId"=>$recordId]);
    }
}
function getMemData($prefixName,$recordId,$preData=null) {
// preData : arreglo asociativo. (key = value)
    // forceDB = Consulta base datos forzosa, no se usa cache.
    // numRec = Número de registros a extraer de la base de datos, si solo es uno regresa ese elemento, si son varios regresa un arreglo secuencial. Si es 0, false o null regresa null. Si es negativo regresa arreglo original con todos los registros encontrados.
    // fieldRec = El registro default a buscar es "id", para obtener un conjunto de registros en base a un solo campo, usar esta llave con el nombre del campo
    // forceWhere = Reemplaza where construido por el indicado aqui
    if (empty($preData["forceDB"])) {
        $data=getMem($prefixName,$recordId);
    }
    if(empty($data)) {
        global $query;
        $numRec=+($preData["numRec"]??"1");
        $fldname=$preData["fieldRec"]??"id";
        $isId=($fldname=="id");
        if (empty($preData["forceWhere"])) {
            $where="$fldname=$recordId";
            if ($isId) $numRec=1; // se considera que en todas las tablas el campo id es primary key (y unico)
        } else {
            $where=str_replace(["%FIELDREC%","%FIELDVAL%"], [$fldname,$recordId], $preData["forceWhere"]);
            // toDo: se podría omitir la línea anterior y que directamente el script en "forceWhere" ya traiga el query construido
        }
        $data=getData($prefixName,$where,$numRec);
        if (empty($data)) {
            doclog("EMPTY DB DATA","comext",["errors"=>DBi::$errors,"query"=>$query,$prefixName."Id"=>$recordId]+$preData);
        } else if ($isId && empty($preData["forceWhere"])) {
            setMem($prefixName,$recordId,$data);
        //} else if ($numRec>0 && isset($data[0]) && !isset($data[1])) {
        //    $data=$data[0];
        //    setMem($prefixName,$data["id"],$data);
        }
    }
    return $data;
}

function isErrorMessage($result, $defaultMessage=null) {
    if (isset($result["result"]) && ($result["result"]==="error")) {
        $errorMessage=$result["message"]??$defaultMessage??"Ocurrió un error";
        unset($result["result"], $result["message"]);
        echoJSDoc("error", $errorMessage, null, $result, "error");
        return true;
    }
}
function doComExtBrowse() {
    global $ceeObj, $cedObj, $gpoObj, $prvObj, $query;
    getObj([CEE,CED,GPO,PRV]);
    $retData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre]+$_POST;
    $where="";//$fieldArray=[];
    if (!empty($_POST["list"])) foreach ($_POST["list"] as $key => $value) {
        //$fieldArray[$key]=json_decode($value);
        //if (isset($where[0])) $where.=" and ";
        //$where.="{$key}='$value'";
        $value=json_decode($value,true);
        if (isset($value["op"])&&isset($value["value"])) $value=new DBExpression($value["value"],$value["op"]);
        $where.=$ceeObj->getWhereCondition($key,$value);
    }
    if (isset($where[0])) $where=rtrim($where," AND ");
    //$ceeData = $ceeObj->getDataByFieldArray($fieldArray);
    $ceeData = getMemData(CEE,"browseAlways",["forceDB"=>"1","forceWhere"=>$where,"numRec"=>-1]);
    $browseQuery=$query;
    $preData=$retData+["forceDB"=>($_POST["forceDB"]??false)];
    $sumTot=[/*"MXN"=>0,"USD"=>0,"EUR"=>0*/]; // Suma de importes encontrados
    // ToDo: Falta relacionar con solicitudes creadas para estos registros (facturas para proveedores y agentes) y sumar total de importes de las solicitudes encontradas
    foreach ($ceeData as $idx => $row) {
        if (!prepareViewData(CEE, $ceeData[$idx],$preData)) return;
        $moneda=$row["moneda"];
        $importe=$row["importe"];
        if ($importe!=0) {
            if (!isset($sumTot[$moneda])) $sumTot[$moneda]=$importe;
            else $sumTot[$moneda]+=$importe;
        }
    }
    $totalVisible="";
    foreach ($sumTot as $mon => $tot) {
        if (isset($totalVisible[0])) $totalVisible.=", ";
        $totalVisible.=formatCurrency($tot,$mon);
    }
    // id, fechaAlta, tipoOperacion, operacion.nombre, grupoId, grupo.alias, grupo.rfc, grupo.razonSocial, folio, proveedorId, proveedor.codigo, proveedor.rfc, proveedor.taxId, proveedor.razonSocial, orden, agenteId, agente.codigo, agente.rfc, agente.razonSocial, importe, moneda, pedimento, descripcion, status, status.desc, modifiedTime
    $reviewData=["where"=>$where, "data"=>$ceeData, "query"=>$browseQuery];
    if (isset($totalVisible[0])) $reviewData+=["total"=>$sumTot,"totalVisible"=>$totalVisible];
    if (empty(DBi::$errors)) echoJSDoc("success", "Datos encontrados", null, $reviewData);
    else echoJSDoc("error", "Ocurrió un error al obtener datos",null, $retData+["line"=>__LINE__,"errors"=>DBi::$errors/*,"list"=>$fieldArray*/]+$reviewData, "error");
}
function doComExtView() {
    global $detLog, $prvObj, $query;
    getObj(PRV);
    $retData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre]+$_POST;
    $type=$_POST["type"];
    //$line=0;
    //doclog("VIEW INI","comext",$retData);
    switch($type) {
        case "foreign":
            $id=$_POST["{$type}Id"]??"";
            $code=strtoupper($_POST["{$type}Code"]??""); // sin Id = buscar por codigo, con Id=cambiar codigo si no existe
            $byCode=isset($code[0]); // buscar por código, si no hay id y existe codigo agregar campos con datos, si no agregar campos vacíos, si si hay Id y existe codigo con diferente Id agregar campos con datos, si no no agregar nada (ni codigo, para no alterar defaultValue y permitir modificar si se guarda)
            $taxId=strtoupper($_POST["{$type}Taxid"]??"");
            $byTaxId=isset($taxId[0]);
            $isNew=!isset($id[0]);
            $query=null;
            if ($byCode) {
                if (isset($code[4])) {
                    $prvData=getData(PRV,"codigo='$code'");
                    if (!empty($prvData)) {
                        $id=$prvData["id"];
                        setMem(PRV,$id,$prvData);
                    }
                } else $prvData=getData(PRV,"codigo like '$code%'",-1);
            } else if ($byTaxId) {
                if (isset($taxId[7])) {
                    $prvData=getData(PRV,"taxId='$taxId'");
                    if (!empty($prvData)) {
                        $id=$prvData["id"];
                        setMem(PRV,$id,$prvData);
                    }
                } else $prvData=getData(PRV,"taxId like '$taxId%'", -1);
            }
            if (empty($prvData)) {
                $retData["line"]=__LINE__;
                if (isset($query[0])) {
                    $retData["query"]=$query;
                    $retData["errors"]=DBi::$errors;
                }
                if (!$isNew) {
                    $retData["{$type}Id"]="";
                    if ($byCode) $retData["{$type}Taxid"]="";
                    else if ($byTaxId) $retData["{$type}Code"]="";
                    $retData["{$type}Name"]="";
                }
                $retData["dataEmpty"]="El proveedor no existe";
                $retData["logname"]="comext";
                break;
            }
            if ($detLog) {
                if (isset($prvData[1])) doclog("doComExtView","comext",["step"=>"MULTIPLE RESULTS","prvData"=>array_column($prvData, "codigo"),"query"=>$query]+$_POST);
                else doclog("doComExtView","comext",["step"=>"HASPRV","prvData"=>array_filter($prvData),"id"=>$id,"code"=>$code,"taxId"=>$taxId,"query"=>$query]);
            }
            if (isset($query[0])) $retData["query"]=$query;
            if (!isset($prvData["id"])) {
                if (isset($prvData[1])) {

                }
                $prvData=$prvData[0];
            }
            if (isset($prvData["id"])) {
                if ($prvData["idTipo"]==2) {
                    if ($prvData["status"]==="eliminado") {
                        $retData["dataWarning"]="El proveedor está eliminado, guarde para recuperarlo";
                        $retData["isDeleted"]=true;
                    }
                    if (isset($prvData["id"])/* && $prvData["id"]!==$id*/) $retData["{$type}Id"]=$prvData["id"];
                    if (isset($prvData["codigo"][0]) && ($prvData["codigo"]!==$code)) {
                        $retData["{$type}Code"]=$prvData["codigo"];
                    }
                    if (isset($prvData["taxId"][0]) && $prvData["taxId"]!==$taxId) $retData["{$type}Taxid"]=$prvData["taxId"];
                    if (isset($prvData["razonSocial"][0]) && $prvData["razonSocial"]!==$retData["{$type}Name"])
                        $retData["{$type}Name"]=$prvData["razonSocial"];
                } else {
                    $retData["dataConfirm"]="Se encontró proveedor '$prvData[codigo]' ($prvData[razonSocial]), pero no es extranjero. ¿Desea reconocerlo como uno?";
                    $retData["defaultOnReject"]=true;
                    if (isset($prvData["taxId"][0])) $retData["{$type}Taxid"]=$prvData["taxId"];
                    if (isset($prvData["id"])) $retData["{$type}Id"]=$prvData["id"];
                    if (isset($prvData["codigo"][0])) $retData["{$type}Code"]=$prvData["codigo"];
                    if (isset($prvData["razonSocial"][0])) $retData["{$type}Name"]=$prvData["razonSocial"];
                }
            }
            if ($detLog) doclog("doComExtView","comext",["retData"=>$retData,"query"=>$query]);
            break;
        case "customs":
            $id=$_POST["{$type}Id"]??"";
            $code=strtoupper($_POST["{$type}Code"]??"");
            $byCode=isset($code[0]);
            $rfc=strtoupper($_POST["{$type}Rfc"]??"");
            $byRfc=isset($rfc[0]);
            $isNew=!isset($id[0]);
            if ($byCode) {
                if (isset($code[4])) {
                    $prvData=getData(PRV,"codigo='$code'");
                    if (!empty($prvData)) {
                        $id=$prvData["id"];
                        setMem(PRV,$id,$prvData);
                    }
                } else $prvData=null;
            } else if ($byRfc) {
                if (isset($rfc[11])) {
                    $prvData=getData(PRV,"rfc='$rfc'");
                    if (!empty($prvData)) {
                        $id=$prvData["id"];
                        setMem(PRV,$id,$prvData);
                    }
                } else $prvData=null;
            }
            if (empty($prvData)) {
                $retData["line"]=__LINE__;
                if (isset($query[0])) {
                    $retData["query"]=$query;
                    $retData["errors"]=DBi::$errors;
                }
                if (!$isNew) {
                    $retData["{$type}Id"]="";
                    if ($byCode) $retData["{$type}Rfc"]="";
                    else if ($byRfc) $retData["{$type}Code"]="";
                    $retData["{$type}Name"]="";
                }
                $retData["dataEmpty"]="El agente aduanal no existe";
                $retData["logname"]="comext";
                break;
            }
            if ($detLog) doclog("doComExtView","comext",["step"=>"HASPRV","prvData"=>array_filter($prvData),"id"=>$id,"code"=>$code,"rfc"=>$rfc,"query"=>$query]);
            if (isset($query[0])) $retData["query"]=$query;
            if ($prvData["idTipo"]==1) {
                if ($prvData["status"]==="eliminado") {
                    $retData["dataWarning"]="El proveedor está eliminado, guarde para reactivarlo";
                    $retData["isDeleted"]=true;
                }
                if (isset($prvData["id"][0])/* && $prvData["id"]!==$id*/) $retData["{$type}Id"]=$prvData["id"];
                if (isset($prvData["codigo"][0]) && $prvData["codigo"]!==$code) $retData["{$type}Code"]=$prvData["codigo"];
                if (isset($prvData["rfc"][0]) && $prvData["rfc"]!==$rfc) $retData["{$type}Rfc"]=$prvData["rfc"];
                if (isset($prvData["razonSocial"][0]) && $prvData["razonSocial"]!==$retData["{$type}Name"])
                    $retData["{$type}Name"]=$prvData["razonSocial"];
            } else {
                $retData["dataConfirm"]="Se encontró agente '$prvData[codigo]' ($prvData[razonSocial]), pero no es agente aduanal. ¿Desea reconocerlo como uno?";
                $retData["defaultOnReject"]=true;
                if (isset($prvData["rfc"][0])) $retData["{$type}Rfc"]=$prvData["rfc"];
                if (isset($prvData["id"])) $retData["{$type}Id"]=$prvData["id"];
                if (isset($prvData["codigo"][0])) $retData["{$type}Code"]=$prvData["codigo"];
                if (isset($prvData["razonSocial"][0])) $retData["{$type}Name"]=$prvData["razonSocial"];
            }
            if ($detLog) doclog("doComExtView","comext",["retData"=>$retData,"query"=>$query]);
            break;
        case "nvoexpd":
            $retData["dataWarning"]="En construcción(1)"; $retData["line"]=__LINE__;
            break;
        case "srchexp":
            $retData["dataWarning"]="En construcción(2)"; $retData["line"]=__LINE__;
            break;
        default:
            $retData["dataWarning"]="Consulta inválida"; $retData["line"]=__LINE__;
    }
    if (!isset($retData["dataError"][0]) && !isset($retData["dataWarning"][0]) && !isset($retData["dataConfirm"][0]) && !isset($retData["query"][0])) {
        $retData["dataWarning"]="No se encontraron coincidencias"; if (isset($id[0])) $retData["ignoreWarning"]=true; $retData["line"]=__LINE__;
    }
    doclog("TO PUBLISH","comext",$retData);
    if (isset($retData["dataError"][0])) echoJSDoc("error", $retData["dataError"], null, $retData, "error");
    else if (isset($retData["dataWarning"][0])) echoJSDoc("warning", $retData["dataWarning"], null, $retData, $retData["logname"]??null);
    else if (isset($retData["dataConfirm"][0])) echoJSDoc("confirm", $retData["dataConfirm"], null, $retData, $retData["logname"]??null);
    else if (isset($retData["dataEmpty"][0])) echoJSDoc("empty", $retData["dataEmpty"], null, $retData, $retData["logname"]??null);
    else echoJSDoc("success", "Datos encontrados", null, $retData);
}
function doComExtSave() {
    global $ceeObj, $cedObj, $cecObj, $prvObj, $query, $query_b;
    getObj([CEE,CED,CEC,PRV]);
    $retData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre];
    $type=$_POST["type"];
    $utype=strtoupper($type);
    if (!isset(TNAM[$type])) { echoJSDoc("error", "Acción inválida", null, $retData+["line"=>__LINE__]+$_POST, "error"); return; }
    $preData=$retData+["forceDB"=>($_POST["forceDB"]??false)];
    switch($type) {
        case "foreign": // doComExtSave | switch($type)
            $id=$_POST["{$type}Id"]??"";
            $code=$_POST["{$type}Code"]??"";
            $taxId=$_POST["{$type}Taxid"]??"";
            $name=$_POST["{$type}Name"]??"";
            $fieldArray=[];
            if (isset($id[0])) {
                $prvData = getMemData(PRV, $id, $preData+["line"=>__LINE__]);
                if (!isset($code[0]) && !isset($taxId[0]) && !isset($name[0]) && $prvData["idTipo"]==2 && $prvData["status"]!="eliminado") { $retData["line"]=__LINE__; $retData["dataWarning"]="No hay cambios por guardar"; $retData["ignoreWarning"]=true;
                } else {
                    $fieldArray["id"]=$id;
                    if (isset($code[0]) && $code!==$prvData["codigo"]) $fieldArray["codigo"]=$code;
                    if (isset($taxId[0]) && $taxId!==$prvData["taxId"]) $fieldArray["taxId"]=$taxId;
                    if (isset($name[0]) && $name!==$prvData["razonSocial"]) $fieldArray["razonSocial"]=$name;
                    if ($prvData["idTipo"]!=2) $fieldArray["idTipo"]=2;
                    if ($prvData["status"]==="eliminado") $fieldArray["status"]="activo";
                }
            } else {
                if (!isset($code[0])) { $retData["line"]=__LINE__;
                    $retData["dataError"]="Debe indicar un código de proveedor válido";
                    $errm="código";
                } else $errm="";
                if (!isset($taxId[0])) {
                    $isErr=isset($errm[0]);
                    $retData["dataError"]="Debe indicar ".($isErr?"$errm y ":"")."TaxId para guardar proveedor extranjero"; $retData["line"]=__LINE__;
                    $errm.=($isErr?", ":"")."taxId";
                }
                if (!isset($name[0])) {
                    $isErr=isset($errm[0]);
                    $retData["dataError"]="Debe indicar ".($isErr?"$errm y ":"un ")."nombre de empresa extranjera"; $retData["line"]=__LINE__;
                    $errm.=($isErr?", ":"")."empresa extranjera";
                }
                if (!isset($retData["dataError"][0])) {
                    try {
                        $xxRfc=getXXId();
                        $fieldArray["codigo"]=$code;
                        $fieldArray["taxId"]=$taxId;
                        $fieldArray["razonSocial"]=$name;
                        $fieldArray["rfc"]=$xxRfc;
                        $fieldArray["idTipo"]=2;
                        $fieldArray["status"]="activo";
                    } catch (Exception $ex) {
                        $retData["dataError"]="No se generó Identificador de Proveedor Externo"; $retData["line"]=__LINE__;
                        doclog("COMEXT SAVE EXTRANJERO: PSEUDO RFC","error",$retData+["exception"=>getErrorData($ex)]+$_POST);
                    }
                } 
            }
            if (!empty($fieldArray)) {
                $retData["data"]=$fieldArray;
                if ($prvObj->saveRecord($fieldArray)) {
                    if (!isset($id[0]))
                        $retData["{$type}Id"]=$prvObj->lastId;
                    global $defOpt,$defOptAll;
                    $prvObj->setIdOptSessions(1000,$defOpt,true);
                    $extIdOpt=$_SESSION['extIdOpt']=$_SESSION['prvIdOpt'];
                    if (!empty($extIdOpt)) {
                        $extIdOpt=[""=>["rfc"=>"Todos","razon"=>"Todas","codigo"=>"Todos","value"=>$defOptAll]]+$extIdOpt;
                    }
                    $extOptList=getEOBJOptions($extIdOpt);
                    $retData["optdata"]=$extOptList;
                } else { $retData["line"]=__LINE__;
                    $retData["dataError"]="No se pudieron guardar los datos del proveedor extranjero";
                    doclog("COMEXT SAVE EXTRANJERO: saveRecord","error",$retData+["query"=>$query,"dberror"=>["code"=>DBi::getErrno(),"message"=>DBi::getError(),"errors"=>DBi::$errors,"oerrors"=>$prvObj->errors]]+$_POST);
                }
            }
            break;
        case "customs": // doComExtSave | switch($type)
            $id=$_POST["{$type}Id"]??"";
            $code=$_POST["{$type}Code"]??"";
            $rfc=$_POST["{$type}Rfc"]??"";
            $name=$_POST["{$type}Name"]??"";
            $fieldArray=[];
            if (isset($id[0])) {
                $prvData = getMemData(PRV, $id, $preData+["line"=>__LINE__]);
                if (!isset($code[0]) && !isset($rfc[0]) && !isset($name[0]) && $prvData["idTipo"]==1 && $prvData["status"]!="eliminado") { $retData["line"]=__LINE__;
                    $retData["dataWarning"]="No hay cambios por guardar";
                    $retData["ignoreWarning"]=true;
                } else {
                    $fieldArray["id"]=$id;
                    if (isset($code[0]) && $code!==$prvData["codigo"]) $fieldArray["codigo"]=$code;
                    if (isset($rfc[0]) && $rfc!==$prvData["rfc"]) $fieldArray["rfc"]=$rfc;
                    if (isset($name[0]) && $name!==$prvData["razonSocial"]) $fieldArray["razonSocial"]=$name;
                    if ($prvData["idTipo"]!=1) $fieldArray["idTipo"]=1;
                    if ($prvData["status"]==="eliminado") $fieldArray["status"]="activo";
                }
            } else {
                if (!isset($code[0])) { $retData["line"]=__LINE__;
                    $retData["dataError"]="Debe indicar un código de proveedor válido";
                    $errm="código";
                } else $errm="";
                if (!isset($rfc[0])) { $retData["line"]=__LINE__;
                    $isErr=isset($errm[0]);
                    $retData["dataError"]="Debe indicar ".($isErr?"$errm y ":"el ")."RFC para guardar agente aduanal";
                    $errm.=($isErr?", ":"")."rfc";
                }
                if (!isset($name[0])) { $retData["line"]=__LINE__;
                    $isErr=isset($errm[0]);
                    $retData["dataError"]="Debe indicar ".($isErr?"$errm y ":"una ")."razón social";
                    $errm.=($isErr?", ":"")."razon social";
                }
                if (!isset($retData["dataError"][0])) {
                    $fieldArray["codigo"]=$code;
                    $fieldArray["rfc"]=$rfc;
                    $fieldArray["razonSocial"]=$name;
                    $fieldArray["idTipo"]=1;
                    $fieldArray["status"]="activo";
                }
            }
            if (!empty($fieldArray)) {
                $retData["data"]=$fieldArray;
                if ($prvObj->saveRecord($fieldArray)) {
                    if (!isset($id[0]))
                        $retData["{$type}Id"]=$prvObj->lastId;
                    global $defOpt,$defOptAll;
                    $prvObj->setIdOptSessions(4,$defOpt,true);
                    $agtIdOpt=$_SESSION['agtIdOpt']=$_SESSION['prvIdOpt'];
                    if (!empty($agtIdOpt)) {
                        $agtIdOpt=[""=>["rfc"=>"Todos","razon"=>"Todas","codigo"=>"Todos","value"=>$defOptAll]]+$agtIdOpt;
                    }
                    $agtOptList=getEOBJOptions($agtIdOpt);
                    $retData["optdata"]=$agtOptList;
                } else { $retData["line"]=__LINE__;
                    $retData["dataError"]="No se pudieron guardar los datos del agente aduanal";
                    doclog("COMEXT SAVE AGENTE ADUANAL: saveRecord","error",$retdata+["query"=>$query,"dberror"=>["code"=>DBi::getErrno(),"message"=>DBi::getError(),"errors"=>DBi::$errors,"oerrors"=>$prvObj->errors]]); // +$_POST
                }
            }
            break;
        case "nvoexpd": // doComExtSave | switch($type)
        case "vwexpdn":
            $id=$_POST["{$type}Id"]??"";
            if (isset($id[0])) {
                $ceeData=getMemData(CEE,$id,$preData+["line"=>__LINE__]);
            }
            $errm="";
            $isOldExt=isset($ceeData);
            $tipo=$isOldExt?"":"NUEVO ";

            $date=$_POST["{$type}Date"]??""; // $d-$m-$year
            if (!isset($date[0])) {
                if ($isOldExt) {
                } else { $retData["line"]=__LINE__;
                    $retData["dataError"]="Debe indicar la fecha de alta";
                    $errm="fecha";
                }
            } else {
                $year=substr($date, 6);
                $month=substr($date,3,2);
                $day=substr($date,0,2);
            }
            if (!isset($year)) {
                // No debería ocurrir
                if(!$isOldExt) $retData["dataError"]="No se reconoce la fecha de alta";
                else {
                    $fechaAlta=$ceeData["fechaAlta"]; // "{$year}-{$month}-{$day} 00:00:00"
                    $dbyear=substr($fechaAlta, 0, 4);
                    $dbmonth=substr($fechaAlta, 5, 2);
                    $cexpath="{$dbyear}/{$dbmonth}/";
                }
            } else {
                $cexpath="{$year}/{$month}/";
                // toDo: Hay que mover el directorio
            }
            $opId=$_POST["{$type}Opid"]??"";
            if (!in_array($opId, [ComExtExpediente::TIPO_OPERACION_IMPORTACION, ComExtExpediente::TIPO_OPERACION_IMPORTACION_ACTIVOS, ComExtExpediente::TIPO_OPERACION_EXPORTACION])) { 
                if ($isOldExt) {
                } else { $retData["line"]=__LINE__;
                    if (isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y operación inválidos";
                        $errm.=", ";
                    } else $retData["dataError"]="La operación $opId no es válida";
                    $errm.="operación";
                }
            }
            $gpoId=$_POST["{$type}GpoId"]??"";
            if(!isset($gpoId[0])) { 
                if ($isOldExt) {
                    $dbgpoId=$ceeData["gpoId"];
                    $gpoData=getMemData(GPO,$dbgpoId,$preData+["line"=>__LINE__]);
                } else {
                    $retData["line"]=__LINE__;
                    if(isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y empresa inválidos"; $errm.=", ";
                    } else $retData["dataError"]="Debe indicar una empresa del corporativo";
                    $errm.="empresa";
                }
            } else {
                $gpoData=getMemData(GPO,$gpoId,$preData+["line"=>__LINE__]);
            }
            $gpoAlias=$gpoData["alias"]??"";
            $fpath=(isset($gpoAlias[0]))?"$gpoAlias/$cexpath":"";
            $extId=$_POST["{$type}ExtId"]??"";
            if (!isset($extId[0])) {
                if ($isOldExt) {
                } else {
                    $retData["line"]=__LINE__;
                    if (isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y proveedor extranjero inválidos"; $errm.=", ";
                    } else $retData["dataError"]="Debe indicar un proveedor extranjero registrado";
                    $errm.="proveedor extranjero";
                }
            }
            $ordId=$_POST["{$type}OrdId"]??"";
            if (!isset($ordId[0])) { $retData["line"]=__LINE__;
                if ($isOldExt) {
                } else {
                    if (isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y orden inválidos"; $errm.=", "; }
                    else $retData["dataError"]="Se necesita que indique la orden relacionada";
                    $errm.="orden";
                }
            }
            $agtId=$_POST["{$type}AgtId"]??"";
            if (!isset($agtId[0])) { $retData["line"]=__LINE__;
                if ($isOldExt) {
                } else {
                    if (isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y agente aduanal inválidos"; $errm.=", "; }
                    else $retData["dataError"]="Debe indicar un agente aduanal registrado";
                    $errm.="agente aduanal";
                }
            }
            $total=$_POST["{$type}Total"]??"";
            $currency=$_POST["{$type}Curr"]??($isOldExt?"":"MXN");
            $desc=$_POST["{$type}Desc"]??"";
            /*
            $ordFile=$_FILES["{$type}OrdFile"]??null;
            if (isset($ordFile)) {
                // toDo: usar getFixedFileArray($ordFile) si se cambia a "multiple" y meter las validaciones de esta sección en un loop. Ignorar archivos con error, cambiar dataError por dataWarning si es que alguno es válido
                if (!isValidFile($ordFile,$ferr,["type"=>"application/pdf"])) { $retData["line"]=__LINE__;
                    if (isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y archivo inválidos"; $errm.=", ";
                    } else $retData["dataError"]="El documento indicado no es válido";
                    $errm.="archivo";
                    doclog("COMEXT SAVE $utype: no valid file","error",["file"=>$ordFile,"error"=>$ferr]+$_POST);
                } else if ($ordFile["size"]<100) { $retData["line"]=__LINE__;
                    if (isset($errm[0])) { $retData["dataError"]=ucfirst($errm)." y tamaño de archivo inválidos"; $errm.=", ";
                    } else $retData["dataError"]="El archivo está vacío";
                    $errm.="tamaño de archivo";
                    doclog("COMEXT SAVE ORDEN $utype REGISTRO","error",["file"=>$ordFile,"error"=>"El archivo está vacío"]+$_POST);
                //} else {
                    // ToDo: Si existe ceeData hay que agregar sufijo incremental para que siempre se actualice el archivo al cambiar (evitar cache de navegador)
                // toDo: si se cambia a "multiple" verificar y crear ruta una sola vez
                    //$ferror=$ordFile["error"];
                    //$ftype=$ordFile["type"];
                    //$oldname=$ordFile["name"];
                    //$fsize=$ordFile["size"];
                }
            }
            */
            if (!isset($retData["dataError"][0])) {
                $fieldArray=[];
                if ($isOldExt) { // isset($id[0])
                    $fieldArray["id"]=$id;
                    $folio=$ceeData["folio"];
                }
                // fechaAlta (datetime yyyy-MM-dd hh:mm:ss)
                if (!$isOldExt||$ceeData["fechaAlta"]!=="{$year}-{$month}-{$day} 00:00:00")
                    $fieldArray["fechaAlta"]="{$year}-{$month}-{$day} 00:00:00";
                // $opId
                // tipoOperacion (int 1)
                if (!$isOldExt||$ceeData["tipoOperacion"]!=$opId)
                    $fieldArray["tipoOperacion"]=$opId;
                // $gpoId
                // grupoId (int 11)
                if (!$isOldExt||$ceeData["grupoId"]!=$gpoId)
                    $fieldArray["grupoId"]=$gpoId;
                if (!$isOldExt) {
                    $xKy=dechex($gpoId);
                    if (!isset($xKy[1])) $xKy="0$xKy";
                    $xKy.=substr($year, -2);
                    $xKy.=dechex($month);
                    $xKy="'CXT".$xKy."'";
                    $usrId=getUser()->id;
                    //$start=hrtime(true);
                    $result=DBi::query("CALL NEXTGRALID($xKy,$usrId)");
                    //$duration=(hrtime(true)-$start)/1000000000.0;
                    doclog("COMEXT $utype AUTO INCREMENT ID","call",["query"=>$query,"query_b"=>$query_b/*,"duration"=>$duration*/]);
                    if ($result) {
                        $row=$result->fetch_assoc();
                        $folio=$row["nextId"]??false;
                        //$duration=(hrtime(true)-$start)/1000000000.0;
                        doclog("COMEXT SAVE NEXTGRALID+FETCH","call",["query"=>$query,"query_b"=>$query_b,"row"=>$row,"folio"=>$folio/*,"duration"=>$duration*/]+$_POST);
                        $result->free();
                        DBi::nextResult();
                        $fieldArray["folio"]=$folio;
                    } else { $retData["line"]=__LINE__;
                        $retData["dataError"]="No pudo crearse el folio del registro";
                        $errParsed="";
                        if (isset(DBi::$errors)) foreach(DBi::$errors as $sErn=>$sErr) {
                            $fixerror=DBi::getErrorTranslated($sErn, $sErr);
                            if (isset($fixerror[0])) {
                                if (isset($errParsed[0])) $errParsed.=", ";
                                $errParsed.=$sErn.":".$fixerror;
                            } else $errParsed.=$sErn.":".$sErr;
                        }
                        //$duration=(hrtime(true)-$start)/1000000000.0;
                        doclog("COMEXT SAVE NEXTGRALID NORESULT","error",["query"=>$query,"query_b"=>$query_b,"error"=>$errParsed/*,"duration"=>$duration*/]+$_POST);
                    }
                }
            }
            $docFldArr=[];
            if (!isset($retData["dataError"][0])) {
                global $cecMap;
                if (!isset($cecMap)) {
                    $cecIdList=[ComExtCatalogo::ID_ORDEN,ComExtCatalogo::ID_CFDIXML,ComExtCatalogo::ID_CFDIPDF];
                    $cecMap=[];
                    foreach ($cecIdList as $cecId) {
                        $cecMap[$cecId]=$cecObj->getData("id=$cecId");
                    }
                }
                if (isset($gpoAlias[0]) && isset($cexpath[0])) {
                    $docErrors=[];
                    $ordFile=$_FILES["{$type}OrdFile"]??null;
                    if (isset($ordFile)) {
                        if (!isValidFile($ordFile,$ferr,["type"=>"application/pdf"])) {
                            $retData["dataWarning"]="El archivo Orden no es válido";
                            doclog("COMEXT $utype SAVE ORDEN: invalid file","error",["line"=>__LINE__,"file"=>$ordFile,"path"=>$fpath,"error"=>$ferr]+$_POST);
                        } else if ($ordFile["size"]<100) { 
                            $retData["dataWarning"]="El archivo Orden está vacío";
                            doclog("COMEXT $utype SAVE ORDEN: empty file","error",["line"=>__LINE__,"file"=>$ordFile,"path"=>$fpath,"error"=>"El archivo está vacío"]+$_POST);
                        } else {
                            $idCat=ComExtCatalogo::ID_ORDEN;
                            $docCat=$cecMap[$idCat];
                            $docPfx=$docCat["prefijoArchivo"];
                            $resMF=doMoveFile($ordFile, $docPfx.$folio,"pdf", $fpath);
                            if ($resMF["toSave"]) {
                                $retData["ruta"]=$fpath;
                                $fname=$resMF["archivo"];
                                $retData["archivo"]=$fname.".pdf";
                                $oname=$ordFile["name"];
                                if (isset($oname[100])) $oname=substr($oname, -99);
                                $docFld=["idCatalogo"=>$idCat,"nombreOriginal"=>$oname,"referencia"=>"$cexpath{$fname}.pdf"];
                                if (empty($total)&&$isOldExt) $docFld["importe"]=$ceeData["importe"];
                                else $docFld["importe"]=$total??0;
                                if (empty($currency)&&$isOldExt) $docFld["moneda"]=$ceeData["moneda"];
                                else $docFld["moneda"]=$currency;
                                if (empty($ordId)&&$isOldExt) $docFld["descripcion"]=$ceeData["orden"];
                                else $docFld["descripcion"]=$ordId??"";
                                $docFldArr[]=$docFld;
                                //"idExpediente"=>$ceeObj->lastId,
                                //"titulo"=>$cecData["titulo"],

                                //"idCatalogo"=>ComExtCatalogo::ID_ORDEN,
                                //"referencia"=>"$cexpath{$fname}.pdf",
                                //"importe"=>$total??0,
                                //"moneda"=>$currency??"MXN",
                                //"descripcion"=>$fieldArray["orden"]??$cecData["descripcion"]??"",
                            } else if (isset($resMF["error"][0])) {
                                $retData["dataWarning"]=$resMF["error"];
                                doclog("COMEXT $utype SAVE ORDEN: error moving","error",["line"=>__LINE__,"file"=>$ordFile,"path"=>$fpath,"error"=>$resMF["error"]]+$_POST);
                            } else {
                                $errmsg="Error al mover archivo";
                                $retData["dataWarning"]="$errmsg '$ordFile[name]'";
                                doclog("COMEXT $utype SAVE ORDEN: error moving","error",["line"=>__LINE__,"file"=>$ordFile,"path"=>$fpath,"error"=>$errmsg]+$_POST);
                            }
                        }
                    }
                    $delDocIds=$_POST["{$type}DelDocIds"]??null;
                    $deletedFiles=0;
                    if (isset($delDocIds[0])) {
                        doclog("BORRAR DOCUMENTOS","comext",["ids"=>$delDocIds]);
                        if ($cedObj->deleteRecord(["id"=>$delDocIds])) {
                            $numRemoved=$cedObj->affectedrows;
                            doclog("DOCUMENTOS BORRADOS","comext",["affectedrows"=>$numRemoved]);
                            if (isset($delDocIds[$numRemoved])) {//$numRemoved==0||$numRemoved<count($delDocIds)
                                $delErrno=DBi::$errno;
                                $delError=DBi::$error;
                                $delErrors=DBi::$errors;
                                global $query;
                                $retData["dataWarning"]=($numRemoved==0)?"No se pudieron borrar las facturas indicadas":"Algunas facturas no pudieron eliminarse";
                                $cedData=$cedObj->getData("id in (".implode(",", $delDocIds).")",0,"id");
                                $retData["NotDeletedIds"]=array_column($cedData, "id");
                                doclog("COMEXT $utype DELETE DOC IDS","error",["query"=>$query,"errno"=>$delErrno,"error"=>$delError,"errors"=>$delErrors]);
                            }
                            $deletedFiles+=$numRemoved;
                        }
                    }
                    $invFiles=getFixedFileArray($_FILES["{$type}InvFiles"]??null);
                    if (isset($invFiles[0])) doclog("CARGA DE FACTURAS","comext",["files"=>$invFiles]);
                    $cfdiFiles=[];
                    foreach ($invFiles as $invIdx => $invFile) {
                        if (isset($invFile)) {
                            $invName=$invFile["name"];
                            if (substr($invName, -4,1)!==".") {
                                if (isset($retData["dataWarning"][0])) $retData["dataWarning"]="Algunos archivos no son válidos";
                                else $retData["dataWarning"]="El archivo '$invName' no tiene formato adecuado";
                                doclog("COMEXT $utype SAVE CFDI: wrong extension size","error",["line"=>__LINE__,"file"=>$invFile,"path"=>$fpath,"error"=>"badExtensionSize"]+$_POST);
                                continue;
                            }
                            $invExt=strtolower(substr($invName, -3));
                            $invName=substr($invName, 0, -4);
                            $ferr="";
                            if (($invExt==="pdf" && isValidFile($invFile,$ferr,["type"=>"application/pdf"]))||($invExt==="xml" && isValidFile($invFile,$ferr,["type"=>["text/xml","application/xml","text/plain"]]))) {
                                if ($invFile["size"]<100) {
                                    if (isset($retData["dataWarning"][0])) $retData["dataWarning"]="Algunos archivos no son válidos";
                                    else $retData["dataWarning"].="El archivo {$invName}.{$invExt} está vacío";
                                    doclog("COMEXT $utype SAVE CFDI: low size","error",["line"=>__LINE__,"file"=>$invFile,"path"=>$fpath,"error"=>"tooSmallSize"]+$_POST);
                                } else if (!isset($cfdiFiles[$invName])) $cfdiFiles[$invName]=[$invExt=>$invFile];
                                else $cfdiFiles[$invName][$invExt]=$invFile;
                            } else {
                                if (isset($retData["dataWarning"][0])) $retData["dataWarning"]="Algunos archivos no son válidos";
                                else if (isset($ferr[0])) $retData["dataWarning"]=$ferr;
                                else $retData["dataWarning"]="El archivo {$invName}.{$invExt} no es válido como factura";
                                doclog("COMEXT $utype SAVE CFDI: Invalid file","error",["line"=>__LINE__,"file"=>$invFile,"path"=>$fpath,"error"=>$ferr??"Invalid File"]+$_POST);
                            }
                        }
                    }
                    $cfdiKeys=array_keys($cfdiFiles);
                    doclog("FACTURAS IDENTIFICADAS","comext",["files"=>$cfdiFiles]);
                    foreach ($cfdiFiles as $cfdiName => $cfdiBlock) {
                        if(!isset($cfdiBlock["xml"])) {
                            if (isset($retData["dataWarning"][0])) $retData["dataWarning"]="Algunos archivos no son válidos";
                            else $retData["dataWarning"]="La factura '$cfdiName' debe incluir archivo XML";
                            doclog("COMEXT $utype SAVE CFDI: Missing XML","error",["line"=>__LINE__,"file"=>$cfdiBlock,"name"=>$cfdiName,"path"=>$fpath,"error"=>"Requiere archivo xml"]+$_POST);
                            unset($cfdiFiles[$cfdiName]);
                            continue;
                        }
                        $nameTen="_".isset($cfdiName[10])?substr($cfdiName, -10):$cfdiName;
                        $idCat=ComExtCatalogo::ID_CFDIXML;
                        $docCat=$cecMap[$idCat];
                        $docPfx=$docCat["prefijoArchivo"];
                        $xname="";
                        $resXML=doMoveFile($cfdiBlock["xml"], $docPfx.$folio.$nameTen, "xml", $fpath);
                        if ($resXML["toSave"]) {
                            global $docRoot,$baseCEDPath;
                            $xname=$resXML["archivo"].".xml";
                            $absName="$docRoot$baseCEDPath$fpath$xname";
                            require_once "clases/CFDI.php";
                            $cfdiObj = CFDI::newInstanceByFileName($absName, $xname, $xerr, $xstk, $xok, $xlog);
                            if ($cfdiObj!==null) {
                                $xtco=strtolower($cfdiObj->get("tipo_comprobante")??"");
                                if ($xtco!=="i") {
                                    if (isset($retData["dataWarning"][0])) $retData["dataWarning"]="Algunos archivos no son válidos";
                                    else $retData["dataWarning"]="El comprobante '$cfdiName', tipo '$xtco', no es una factura";
                                    doclog("COMEXT $utype SAVE CFDI: BAD CFDI TYPE","error",["line"=>__LINE__,"file"=>$cfdiBlock,"name"=>$cfdiName,"path"=>$fpath,"error"=>"No es Factura","tc"=>$xtco]+$_POST);
                                    unset($cfdiFiles[$cfdiName]);
                                    continue;
                                }
                                $xtot=$cfdiObj->get("total")??0;
                                $xmon=$cfdiObj->get("moneda")??"MXN";
                                $xtcm=$cfdiObj->get("tipocambio")??1;
                                $xfol=$cfdiObj->get("folio")??"";
                                if(!isset($xfol[0])) $xfol=substr($cfdiObj->get("uuid"), -10);
                                $oname=$cfdiBlock["xml"]["name"];
                                if (isset($oname[100])) $oname=substr($oname, -99);
                                $xmlFld=["idCatalogo"=>$idCat,"titulo"=>$cfdiName,"nombreOriginal"=>$oname,"referencia"=>"$cexpath{$xname}","importe"=>$xtot,"moneda"=>$xmon,"descripcion"=>$xfol];
                                //$docFldArr[]=$docFld;
                            } else {
                                if (isset($retData["dataWarning"][0])) $retData["dataWarning"]="Algunos archivos no son válidos";
                                else $retData["dataWarning"]="El archivo '{$xname}' tiene errores";
                                doclog("COMEXT $utype SAVE CFDI: Error moving XML","error",["line"=>__LINE__,"file"=>$cfdiBlock,"name"=>$xname,"path"=>$fpath,"error"=>"Archivo '$xname' tiene errores","cfdiError"=>$xerr, "cfdiStack"=>$xstk, "cfdiLog"=>$xlog]+$_POST);
                                unset($cfdiFiles[$cfdiName]);
                                continue;
                            }
                        } else {
                            $errmsg="Error al mover archivo";
                            $hasResXML=isset($resXML["error"][0]);
                            if (isset($retData["dataWarning"][0])) { $retData["dataWarning"]="Algunos archivos no son válidos";
                            } else if ($hasResXML) { $retData["dataWarning"]=$resXML["error"];
                            } else {
                                $retData["dataWarning"]="$errmsg '$cfdiName'";
                            }
                            doclog("COMEXT $utype SAVE CFDI: error moving XML","error",["line"=>__LINE__,"file"=>$cfdiBlock["xml"],"path"=>$fpath,"error"=>$hasResXML?$resXML["error"]:$errmsg]+$_POST);
                            unset($cfdiFiles[$cfdiName]);
                            continue;
                        }
                        $pdfFld=null;
                        if (isset($xname[0])&&isset($cfdiBlock["pdf"])) {
                            $idCat=ComExtCatalogo::ID_CFDIPDF;
                            $docCat=$cecMap[$idCat];
                            $docPfx=$docCat["prefijoArchivo"];
                            if (isset($resXML["num"])) {
                                $pname0=$docPfx.$folio.$nameTen;
                            } else $pname0=$resXML["archivo"];
                            $resPDF=doMoveFile($cfdiBlock["pdf"], $pname0, "pdf", $fpath,$resXML["num"]??null);
                            if ($resPDF["toSave"]) {
                                global $docRoot,$baseCEDPath;
                                $pname=$resPDF["archivo"].".pdf";
                                $absPName="$docRoot$baseCEDPath$fpath$pname";
                                if ($resPDF["archivo"]!==$pname0) {
                                    $xmlOld=$fpath.$xname;
                                    $absOldXML="$docRoot$baseCEDPath$xmlOld";
                                    $xmlNew=$fpath.$resPDF["archivo"].".xml";
                                    $absNewXML="$docRoot$baseCEDPath$xmlNew";
                                    rename($absOldXML, $absNewXML);
                                    $xname=$resPDF["archivo"].".xml";
                                    $xmlFld["referencia"]="$cexpath{$xname}";
                                    doclog("Ajuste de nombre por PDF existente","comext",["cfdiBlock"=>$cfdiBlock,"moveResult"=>["xml"=>$resXML,"pdf"=>$resPDF],"xmlOld"=>$xmlOld,"xmlNew"=>$xmlNew,"path"=>$fpath]);
                                }
                                $oname=$cfdiBlock["pdf"]["name"];
                                if (isset($oname[100])) $oname=substr($oname, -99);
                                $pdfFld=["idCatalogo"=>$idCat,"titulo"=>$cfdiName,"nombreOriginal"=>$oname,"referencia"=>"$cexpath{$pname}","importe"=>$xtot,"moneda"=>$xmon,"descripcion"=>$xfol];
                            } else {
                                $errmsg="Error al mover archivo";
                                $hasResPDF=isset($resPDF["error"][0]);
                                if (isset($retData["dataWarning"][0])) { $retData["dataWarning"]="Algunos archivos no son válidos";
                                } else if ($hasResPDF) { $retData["dataWarning"]=$resPDF["error"];
                                } else { $retData["dataWarning"]="$errmsg '$cfdiName'"; }
                                doclog("COMEXT $utype SAVE CFDI: error moving PDF","error",["line"=>__LINE__,"file"=>$cfdiBlock["pdf"],"path"=>$fpath,"error"=>$hasResPDF?$resPDF["error"]:$errmsg]+$_POST);
                                unset($cfdiFiles[$cfdiName]);
                                continue;
                            }
                        } else {
                            if (!isset($xname[0])) doclog("COMEXT $utype SAVE CFDI: no se encuentra nombre de XML","error",["cfdiName"=>$cfdiName,"cfdiBlock"=>$cfdiBlock]);
                            if (!isset($cfdiBlock["pdf"])) doclog("COMEXT $utype SAVE CFDI: no se anexó PDF","comext",["cfdiName"=>$cfdiName,"cfdiBlock"=>$cfdiBlock,"cfdiNames"=>$cfdiKeys]);
                        }
                        $docFldArr[]=$xmlFld;
                        if (isset($pdfFld)) $docFldArr[]=$pdfFld;
                    }
                    $newCfdiKeys=array_keys($cfdiFiles);
                    if (isset($cfdiKeys[count($newCfdiKeys)])) $retData["validFileKeys"]=$newCfdiKeys;
                    if (isset($cfdiKeys[0]) && !isset($docFldArr[0]))
                        $retData["dataError"]=$retData["dataWarning"]??"No hay archivos válidos";
                } else doclog("COMEXT SAVE NOFPATH","error",["line"=>__LINE__,"alias"=>$gpoAlias,"cexpath"=>$cexpath]+$_POST);
                // $extId
                // proveedorId (int 11)
                if (!$isOldExt||$ceeData["proveedorId"]!=$extId)
                    $fieldArray["proveedorId"]=$extId;
                // $ordId
                // orden (varchar 45)
                if (isset($ordId[0]) && (!$isOldExt||$ceeData["orden"]!=$ordId))
                    $fieldArray["orden"]=$ordId;
                // $agtId
                // agenteId (varchar 45)
                if (isset($agtId[0]) && (!$isOldExt||$ceeData["agenteId"]!=$agtId))
                    $fieldArray["agenteId"]=$agtId;
                // $total
                // importe (decimal 14,3)
                if (isset($total[0]) && (!$isOldExt||$ceeData["importe"]!=$total))
                    $fieldArray["importe"]=$total;
                // $currency
                // moneda (varchar 5)
                if (isset($currency[0]) && (!$isOldExt||$ceeData["moneda"]!=$currency))
                    $fieldArray["moneda"]=$currency;
                // $pedimento
                // pedimento (varchar 45)
                //if (isset($pedimento[0]) && (!isset($ceeData["pedimento"])||$ceeData["pedimento"]!=$pedimento))
                //    $fieldArray["pedimento"]=$pedimento;
                // $desc
                // descripcion (varchar 500)
                if (isset($desc[0]) && (!$isOldExt||$ceeData["descripcion"]!=$desc))
                    $fieldArray["descripcion"]=$desc;
                // status (int 1)
                //0=En Proceso, 1=Con Anticipo, 2=Importada, 4=Exportada, 8=Pagada, 16=Cerrada, 32=Auditada, 64=No usado, 128=Cancelada
                if (!$isOldExt) $fieldArray["status"]="0";
                $ceeObj->lastId=null;
                //$fpath
                $fieldKeys=array_keys($fieldArray);
                DBi::autocommit(false);
                if ($isOldExt && isset($fieldArray["id"]) && !isset($fieldKeys[1])) {
                    // Nada que guardar en Expedientes
                    if (!isset($docFldArr[0]) && !isset($retData["dataError"][0]) && $deletedFiles==0) {
                        $retData["dataWarning"]="No hay cambios";
                    }
                } else if (!isset($retData["dataError"][0]) && !$ceeObj->saveRecord($fieldArray)) { 
                    // ToDo: si no hay errores en base de datos es porque no había nada que cambiar y debería aceptarse sin error pues puede que los cambios unicamente sean en documentos
                    // Aunque tratandose de un {$tipo}expediente, claro que debe haber datos para guardar

                    $errData=["fieldArray"=>$fieldArray,"query"=>$query,"query_b"=>$query_b];
                    try {
                        $errData["dbierrno"]=DBi::getErrno();
                        $errData["dbierror"]=DBi::getError();
                        $errData["dbierrors"]=DBi::$errors;
                        $errData["oerrors"]=$ceeObj->errors;
                    } catch (Exception $ex) {
                        try {
                            $errData["exception"]=getErrorData($ex);
                        } catch (Throwable $th) {
                            $errData["throwable"]=json_encode($th);
                        }
                    }
                    $retData["line"]=__LINE__;
                    $retData["dataError"]="No se pudo guardar el {$tipo}registro";
                    doclog("COMEXT ON SAVE $utype RECORD","error",$retData+$_POST+$errData);
                }
                if (!isset($retData["dataError"][0]) && (isset($ceeObj->lastId)||$isOldExt)) {
                    if (!$isOldExt) {
                        $id=$ceeObj->lastId;
                        $retData["insertId"]=$id;
                    }
                    if (isset($docFldArr[0])) {
                        foreach ($docFldArr as $docFldIdx => $docFldRow) {
                            $cecData=getMemData(CEC,$docFldRow["idCatalogo"], $preData+["line"=>__LINE__]);
                            $docFldRow["idExpediente"]=$id;
                            if(!isset($docFldRow["titulo"])) $docFldRow["titulo"]=$cecData["titulo"]??"";
                            if (!$cedObj->saveRecord($docFldRow)) { 
                                $retData["line"]=__LINE__;
                                $retData["dataError"]="No se pudo guardar el documento '$fldArr[titulo]'";
                                doclog("COMEXT ON SAVE {$tipo}DOC ORDEN","error",$retData+["query"=>$query,"query_b"=>$query_b,"dberror"=>["code"=>DBi::getErrno(),"message"=>DBi::getError(),"errors"=>DBi::$errors,"oerrors"=>$cedObj->errors],"fld"=>$fldArr]+$_POST);
                            }
                        }
                    }
                }
                $retData["query"]=$query;
                $retData["query_b"]=$query_b;
                if (!isset($retData["dataError"][0])) {
                    DBi::commit();
                    $preData["forceDB"]="1";
                    $ceeData=getMemData(CEE,$ceeObj->lastId??$id,$preData+["line"=>__LINE__]);
                    prepareViewData(CEE,$ceeData,$preData);
                    if (isset($ceeData)) $retData["data"]=$ceeData;
                }
                else DBi::rollback();
                DBi::autocommit(true);
            }
            break;
        case "srchexp":
            $retData["dataWarning"]="En construcción(4)"; $line=__LINE__;
            break;
        default:
            $retData["dataError"]="Accion inválida"; $line=__LINE__;
    }
    if (isset($retData["dataError"][0])) echoJSDoc("error", $retData["dataError"], null, $retData, "error");
    else if (isset($retData["dataWarning"][0])) echoJSDoc("warning", $retData["dataWarning"], null, $retData);
    else if (isset($retData["dataConfirm"][0])) echoJSDoc("confirm", $retData["dataConfirm"], null, $retData);
    else echoJSDoc("success", "Los datos del ".TNAM[$type]." han sido guardados satisfactoriamente", null, $retData);
} // doComExtSave
function doMoveFile($file,$baseName,$ext,$filePath,$_num=null) {
    global $docRoot,$baseCEDPath;
    if (!isset($docRoot[0])) $docRoot=$_SERVER["DOCUMENT_ROOT"];
    if (!isset($baseCEDPath[0])) {
        if (substr($docRoot, -1)!=="/") $docRoot.="/";
        $baseCEDPath="docs/cex/";
    }
    $sitePath=$baseCEDPath.$filePath;
    $absPath=$docRoot.$sitePath;
    $retData=["toSave"=>false];
    if (!file_exists($absPath)) mkdir($absPath, 0644, true);
    if (!file_exists($absPath)) {
        $retData["error"]="La ubicacion '$filePath' no existe";
        doclog("COMEXT MOVE FILE: PATH DOESNT EXIST","error",["file"=>$file,"baseName"=>$baseName,"ext"=>$ext,"filePath"=>$sitePath]);
    } else if (!is_dir($absPath)) {
        $retData["error"]="La ubicacion '$filePath' no es directorio";
        doclog("COMEXT MOVE FILE: PATH ISNT DIR","error",["file"=>$file,"baseName"=>$baseName,"ext"=>$ext,"filePath"=>$sitePath]);
    } else if (!is_writable($absPath)) {
        $retData["error"]="La ubicacion '$filePath' no permite escritura";
        doclog("COMEXT MOVE FILE: PATH ISNT WRITABLE","error",["file"=>$file,"baseName"=>$baseName,"ext"=>$ext,"filePath"=>$sitePath]);
    } else if (isset($baseName[0])) {
        if (empty($filePath)) {
            doclog("COMEXT MOVE FILE: EMPTY PATH","error",["file"=>$file,"baseName"=>$baseName,"ext"=>$ext,"filePath"=>$filePath]);
        }
        $fname=$baseName;
        if (isset($_num) || file_exists("$absPath{$fname}.{$ext}")) {
            if(!isset($_num) || !is_numeric($_num) || (+$_num)<2) $num=2;
            else if(!is_integer(+$_num)) $num=(int)round(+$_num,0);
            else $num=$_num;
            while(file_exists("$absPath{$fname}_{$num}.{$ext}")) $num++;
            $fname.="_$num";
        }
        if (!move_uploaded_file($file["tmp_name"], "$absPath{$fname}.{$ext}")) {
            $retData["error"]="Error al mover archivo '$file[name]'";
            doclog("COMEXT MOVE FILE: FAILED MOVE","error",["file"=>$file,"baseName"=>$baseName,"ext"=>$ext,"filePath"=>$filePath]);
        } else {
            $retData["archivo"]=$fname;
            $retData["toSave"]=true;
            if (isset($num)) $retData["num"]=$num;
        }
    }
    return $retData;
}
function getXXId() {
    global $bd_servidor, $bd_base, $bd_usuario, $bd_clave;
    $ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?($_SERVER['REMOTE_ADDR']):$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
    try {
        $dsn="mysql:host=$bd_servidor;dbname=$bd_base;charset=utf8";
        $pdo = new PDO($dsn, $bd_usuario, $bd_clave);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        // toDo: generate rfc XEXX consecutive
        if ($pdo->beginTransaction()) {
            
            $stmt=$pdo->query("select concat('XEXX',lpad(right(valor,9)+1,9,'0')) from infolocal where nombre='lastXEXX' FOR UPDATE");
            $newRfc=$stmt->fetchColumn();
            if (isset($newRfc[0])) {
                $fixQry="UPDATE infolocal set valor=? where nombre='lastXEXX'";
            } else {
                $stmt=$pdo->query("select concat('XEXX',lpad(right(rfc,9)+1,9,'0')) nrfc from proveedores where rfc like 'XEXX%' order by rfc desc limit 1 FOR UPDATE");
                $newRfc=$stmt->fetchColumn();
                if (!isset($newRfc[0])) $newRfc="XEXX110101001";
                $fixQry="INSERT INTO infolocal (nombre,valor) VALUES ('lastXEXX',?)";
            }
            $stmt=$pdo->prepare($fixQry);
            $stmt->execute([$newRfc]);
            $pdo->commit();
            return $newRfc;
        }
    } catch (Exception $ex) {
        if(isset($pdo)) {
            $pdo->rollback();
            $pdo=null;
        }
        throw $ex;
    }
    throw new Exception("Transacción fallida, consulte al administrador");
}
function getKey($baseKey, $prefix="") {
    if (!isset($baseKey[0])) return false;
    $hasPrefix=isset($prefix[0]);
    if (!$hasPrefix) $prefix="";
    $keyInitial=$hasPrefix?strtoupper($baseKey[0]):strtolower($baseKey[0]);
    return $prefix.$keyInitial.substr($baseKey, 1);
}
function prepareViewData($tabName, &$data, $extra, $pfx="") {
    $retData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre,"tabName"=>$tabName,"extra"=>$extra,"pfx"=>$pfx];
    $localRoot=$_SERVER["DOCUMENT_ROOT"]; // C:\inetpub\wwwroot\InvoiceCheck\ // C:/Apache24/htdocs 
    $webRoot=$_SERVER["HTTP_ORIGIN"]."/"; // https://globaltycloud.com.mx/ // http://invoicecheck.dyndns-web.com:81/
    $docPath="docs/cex/";
    $appPath="invoice/";
    $imgPath="imagenes/icons/";
    switch($tabName) {
        case CEE: // "id", "grupoId", "proveedorId", "agenteId", "fechaAlta", "tipoOperacion", "folio", "moneda", "importe", "status"
        // , "orden", "pedimento", "descripcion"
            $gpoId=$data["grupoId"];
            $gpoData=getMemData(GPO,$gpoId,$extra+["line"=>__LINE__]);
            if (empty($gpoData)) return false;
            $ky=getKey("Grupo",$pfx);
            if (isset($pfx[0])) $data[$ky."Id"]=$gpoId;
            $gpoAlias=$gpoData["alias"];
            $data[$ky.".alias"]=$gpoAlias;
            $data[$ky.".rfc"]=$gpoData["rfc"];
            $data[$ky.".razonSocial"]=$gpoData["razonSocial"];
            $gpoCut=$gpoData["cut"];
            $data[$ky.".cut"]=$gpoCut;
            $prvId=$data["proveedorId"];
            $prvData=getMemData(PRV,$prvId,$extra+["line"=>__LINE__]);
            if (empty($prvData)) return false;
            $ky=getKey("Proveedor",$pfx);
            if (isset($pfx[0])) $data[$ky."Id"]=$prvId;
            $data[$ky.".codigo"]=$prvData["codigo"];
            $data[$ky.".rfc"]=$prvData["rfc"];
            $data[$ky.".taxId"]=$prvData["taxId"];
            $data[$ky.".razonSocial"]=$prvData["razonSocial"];
            $agtId=$data["agenteId"];
            $agtData=getMemData(AGT,$agtId,$extra+["line"=>__LINE__]);
            if (empty($agtData)) return false;
            $ky=getKey("Agente",$pfx);
            if (isset($pfx[0])) $data[$ky."Id"]=$agtId;
            $data[$ky.".codigo"]=$agtData["codigo"];
            $data[$ky.".rfc"]=$agtData["rfc"];
            $data[$ky.".razonSocial"]=$agtData["razonSocial"];

            $kyOrd=getKey("Orden",$pfx);
            if (isset($pfx[0])) $data[$kyOrd]=$data["orden"];
            $kyCfdXml=getKey("Xml",$pfx);
            $kyCfdPdf=getKey("Pdf",$pfx);
            $cedData=getMemData(CED,$data["id"],$extra+["line"=>__LINE__,"numRec"=>-1,"fieldRec"=>"idExpediente"]);
            $ky=getKey("Documentos",$pfx);
            $thisDocPath=$docPath.$gpoAlias."/";
            $data[$ky.".docRoot"]=$webRoot.$thisDocPath;
            $data[$ky.".imgRoot"]= /* $appPath. */ $imgPath;
            //$data[$ky.".pdfSrc"]
            if (isset($cedData[0]["id"])) $data[$ky]=$cedData;
            $saldoFacturado=0;
            $moneda=$data["moneda"];
            foreach ($cedData as $docIdx=>$doc) {
                $docRef="$doc[referencia]";
                if (isset($docRef[0]) && !file_exists($localRoot.$thisDocPath.$docRef)) {
                    doclog("COMEXT: Archivo no encontrado","error",$retData+["line"=>__LINE__,"docRef"=>$thisDocPath.$docRef,"data"=>$data]);
                    continue;
                }
                $data[$ky][$docIdx]["name"]=$gpoAlias."/".$docRef;
                $lastSlash=strrpos($docRef, "/");
                if($lastSlash>0)
                    $data[$ky][$docIdx]["name.short"]=substr($docRef, $lastSlash+1, -4);
                $data[$ky][$docIdx]["size"]=filesize($localRoot.$thisDocPath.$docRef);
                $data[$ky][$docIdx]["size.fix"]=sizeFix($data[$ky][$docIdx]["size"]);
                if (substr($data[$ky][$docIdx]["size.fix"], -1)!=="B") $data[$ky][$docIdx]["size.fix"].="B";
                $data[$ky][$docIdx]["type"]=mime_content_type($localRoot.$thisDocPath.$docRef);
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $data[$ky][$docIdx]["type".((isset($data[$ky][$docIdx]["type"][0])?".finfo":""))]=finfo_file($finfo, $localRoot.$thisDocPath.$docRef);
                finfo_close($finfo);
                $data[$ky][$docIdx]["type.ext"]=substr($docRef, -3);
                $docKey=false; $imgName=false; $docType=false;
                //$cecData=getMemData(CEC,$doc["idCatalogo"]);
                // toDo: agregar en ComExtCatalogo key=(Orden, Xml, Pdf), imagen=("pdf200EA.png", "xml200.png", "pdf200.png")
                switch($doc["idCatalogo"]) {
                    case "1": $docKey=$kyOrd; $imgName="pdf200EA.png"; $docType="Orden de Compra"; break;
                    case "2": $docKey=$kyCfdXml; $imgName="xml200.png"; $docType="Factura"; 
                        if ($moneda===$doc["moneda"]&&is_numeric($saldoFacturado))
                            $saldoFacturado+=$doc["importe"];
                        else $saldoFacturado="Diferente moneda";
                        break;
                    case "3": $docKey=$kyCfdPdf; $imgName="pdf200.png"; $docType="Factura"; break;
                }
                if ($docKey) {
                    $data[$ky][$docIdx]["docType"]=$docType;
                    $data[$ky][$docIdx]["key"]=$docKey;
                    $docImgKey=$docKey.".src";
                    if (!isset($data[$docImgKey])) $data[$docImgKey]=$imgName;
                    $docRefKey=$docKey.".href";
                    $docRefOld=$data[$docRefKey]??false;
                    if (is_string($docRefOld)) $data[$docRefKey]=[$docRefOld,$docRef];
                    else if (is_array($docRefOld)) $data[$docRefKey].push($docRef);
                    else $data[$docRefKey]=$docRef;
                }
            }
            $fechaAlta=$data["fechaAlta"];
            $ky=getKey("fechaAlta",$pfx);
            $data[$ky.".fecha"]=substr($fechaAlta, 0, 10);
            $data[$ky.".calendarValue"]=substr($fechaAlta,8,2)."/".substr($fechaAlta,5,2)."/".substr($fechaAlta, 0, 4);
            $tipoOperacion=$data["tipoOperacion"];
            $ky=getKey("Operacion",$pfx);
            if (isset($pfx[0])) $data[$ky."Id"]=$tipoOperacion;
            $data[$ky.".nombre"]=$tipoOperacion>0?ComExtExpediente::TIPOS_OPERACION[$tipoOperacion]:"Sin Operacion";
            $folio=$data["folio"];
            $ky=getKey("Folio",$pfx);
            if (isset($pfx[0])) $data[$ky]=$folio;
            $data[$ky.".desc"]=$gpoCut.substr($fechaAlta, 2, 2).substr($fechaAlta, 5, 2)."-".str_pad($folio, 2, "0", STR_PAD_LEFT);
            //$moneda=$data["moneda"]; //definida antes, comentario mantenido por referencia
            $ky=getKey("Moneda",$pfx);
            if (isset($data[$ky.".old"][0]) && $moneda!==$data[$ky.".old"]) {
                $monedaOld=$data[$ky.".old"];
                //$data[$ky.".new"]=$moneda;
            }
            $data[$ky.".new"]=$moneda;
            $importe=$data["importe"];
            $ky=getKey("importe",$pfx);
            $data[$ky.".numero"]=formatTwoFractionDigits($importe);
            $data[$ky.".visible"]=formatCurrency($importe,$moneda);
            if (isset($data[$ky.".old"]) && ($importe!==$data[$ky.".old"]||isset($monedaOld))) {
                if(isset($monedaOld) && isset($data["equivalencia"])) { // tipoCambio
                    $importeOldEqv=$data[$ky.".old"]*$data["equivalencia"];
                    $data[$ky.".diferencia"]=$importe-$importeOldEqv;
                } else if (!isset($monedaOld)) {
                    $data[$ky.".diferencia"]=$importe-$data[$ky.".old"];
                } else {
                    $data[$ky.".diferencia"]="No puede calcularse sin equivalencia";
                }
            }
            if (is_numeric($saldoFacturado) && $saldoFacturado>0) {
                $diferencia=$importe-$saldoFacturado;
                $data[$ky.".diferencia"]=$diferencia;
                $data[$ky.".difnumero"]=formatTwoFractionDigits($diferencia);
                $data[$ky.".difvisible"]=formatCurrency($diferencia,$moneda);
            } else if (is_string($saldoFacturado) && isset($saldoFacturado[0])) {
                $data[$ky.".diferencia"]=$saldoFacturado;
            }
            $ky=getKey("Descripcion",$pfx);
            if (isset($pfx[0])) $data[$ky]=$data["desrcipcion"];
            $ky=getKey("Pedimento",$pfx);
            if (isset($pfx[0])) $data[$ky]=$data["pedimento"];
            /*
                //$savedData["ceeMoneda.old"]=$ceeData["moneda"];
                //$savedData["ceeImporte.old"]=$ceeData["importe"];

                $savedData["ceeGrupoId"]=$gpoId;
                $savedData["ceeGrupo.alias"]=$gpoAlias;
                $savedData["ceeGrupo.rfc"]=$gpoData["rfc"];
                $savedData["ceeGrupo.razonSocial"]=$gpoData["razonSocial"];
                $savedData["ceeGrupo.cut"]=$gpoData["cut"];

                $savedData["ceeProveedorId"]=$extId;
                $savedData["ceeProveedor.codigo"]=$extData["codigo"];
                $savedData["ceeProveedor.rfc"]=$extData["rfc"];
                $savedData["ceeProveedor.taxId"]=$extData["taxId"];
                $savedData["ceeProveedor.razonSocial"]=$extData["razonSocial"];

                $savedData["ceeAgenteId"]=$agtId;
                $savedData["ceeAgente.codigo"]=$agtData["codigo"];
                $savedData["ceeAgente.rfc"]=$agtData["rfc"];
                $savedData["ceeAgente.razonSocial"]=$agtData["razonSocial"];

                $savedData["ceeDocumentos"]=[$docData];

                $savedData["ceeFechaAlta.fecha"]=$dbDate;
                $savedData["ceeFechaAlta.calendarValue"]="{$day}/{$month}/{$year}";
                $savedData["ceeOperacion.nombre"]=ComExtExpediente::TIPOS_OPERACION[$opId];

                "ceeFolio.desc"

                $savedData["ceeMoneda.new"]=$moneda;
                $savedData["ceeImporte.numero"]=formatTwoFractionDigits($total);
                $savedData["ceeImporte.visible"]=formatCurrency($total,$moneda);
                $savedData["ceeImporte.diferencia"]=$total-(+$ceeData["importe"]);

                $savedData["ceeOrden"]=$ordId;
                $savedData["ceeDescripcion"]=$desc;
                $savedData["ceePedimento"]=$pedimento;
            */

            $status=$data["status"];
            $sttKys=array_reverse(array_keys(ComExtExpediente::STATUSES));
            $maxStt=array_sum($sttKys);
            $numStt=(int)$status;
            while($numStt>$maxStt) $numStt-=$maxStt;
            $sttDesc=[];
            foreach ($sttKys as $sk) {
                if ($numStt<$sk) continue;
                $sttDesc[]=ComExtExpediente::STATUSES[$sk];
                $numStt-=$sk;
            }
            $data[$ky.".desc"]=implode(", ",array_reverse($sttDesc));
            break;
    }
    return true;
}
function doComExtDel() {
    global $prvObj, $query;
    getObj(PRV);
    $type=$_POST["type"];
    $line;
    $id=$_POST["{$type}Id"]??"";
    switch($type) {
        case "foreign":
        case "customs":
            $isExt=($type==="foreign");
            if (isset($id[0])) {
                if ($prvObj->exists("id=$id")) {
                    if ($prvObj->saveRecord(["id"=>$id,"status"=>"eliminado"])) {
                        global $defOpt,$defOptAll;
                        $prvObj->setIdOptSessions($isExt?1000:4,$defOpt,true);
                        if ($isExt) { // foreign
                            $extIdOpt=$_SESSION['extIdOpt']=$_SESSION['prvIdOpt'];
                            if (!empty($extIdOpt)) {
                                $extIdOpt=[""=>["rfc"=>"Todos","razon"=>"Todas","codigo"=>"Todos","value"=>$defOptAll]]+$extIdOpt;
                            }
                            $extOptList=getEOBJOptions($extIdOpt);
                            $_POST["optdata"]=$extOptList;
                        } else { // customs
                            $agtIdOpt=$_SESSION['agtIdOpt']=$_SESSION['prvIdOpt'];
                            if (!empty($agtIdOpt)) {
                                $agtIdOpt=[""=>["rfc"=>"Todos","razon"=>"Todas","codigo"=>"Todos","value"=>$defOptAll]]+$agtIdOpt;
                            }
                            $agtOptList=getEOBJOptions($agtIdOpt);
                            $_POST["optdata"]=$agtOptList;
                        }
                    } else {
                        $_POST["dataError"]="No se pudieron guardar los datos del ".($isExt?"proveedor extranjero":"agente aduanal"); $line=__LINE__;
                        $_POST["errno"]=DBi::getErrno();
                        $_POST["error"]=DBi::getError();
                        $_POST["errors"]=DBi::$errors;
                        $_POST["oerrors"]=$prvObj->errors;
                    }
                } else {
                    //$_POST["dataError"]="El proveedor ya no se encuentra en el sistema"; $line=__LINE__;
                }
            } else {
                $_POST["dataError"]="No se reconoce al proveedor"; $line=__LINE__;
            }
            break;
        default:
            $_POST["dataError"]="No está implementado"; $line=__LINE__;
    }
    // check
    // cambiar status a inactivo 
    $baseData=array_merge(["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre],$_POST);
    if (isset($_POST["dataError"][0]))
        echoJSDoc("error", $_POST["dataError"], null, $baseData+["line"=>$line], "error");
    echoJSDoc("success", "Los datos del proveedor han sido eliminados satisfactoriamente", null, $baseData);
}
