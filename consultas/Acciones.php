<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Acciones.php";

$actObj = new Acciones();
doclog("CALL","acciones",$_POST);
if (isValueService()) getValueService($actObj);
else if (isTestService()) getTestService($actObj);
else if (isCatalogService()) getCatalogService($actObj);
else if (isActionService()) doActionService();

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    global $actObj,$prcObj;
    if (!hasUser()) {
        echo "REFRESH";
        return;
    }
    global $query;
    $queries=[];
    switch($_POST["action"]??"") {
        case "adminDelete":
            $actId=$_POST["id"]??"";
            if (!isset($actId[0])) {
                echo "ERROR:No se identifica la acción a borrar";
                doclog("Error: No se incluye identificador a borrar","usuarios",$_POST);
                return;
            }
            $actData=$actObj->getData("id='$actId'", 1);
            if (!isset($actData[0])) {
                echo $actId; // No marcar error, regresar id inexistente para que se borre de la lista.
                doclog("Error: No existe el id","usuarios",$_POST);
                return;
            }
            if (!$actObj->deleteRecord(["id"=>$actId])) {
                echo "ERROR:Error de datos ".DBi::getErrno()." : ".DBi::getError();
                doclog("Error: Falló borrar datos","usuarios",$_POST+["errno"=>DBi::getErrno(),"error"=>DBi::getError(),"query"=>$query]);
                return;
            }
            $queries["delete"]=$query;
            echo $actId;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioAdmin($actId,"BorrarAccion","",http_build_query($actData[0], '', ';'));
            $queries["proceso"]=$query;
            break;
        case "adminSave":
            $trace=[];
            $actId=$_POST["id"]??"";
            $trace[]="ACT ID = '$actId'";
            $actFields=[];
            if (isset($_POST["name"])) {
                $actFields["nombre"]=$_POST["name"];
                $trace[]="NOMBRE = '$actFields[nombre]'";
            }
            if (isset($_POST["desc"])) {
                $actFields["descripcion"]=$_POST["desc"];
                $trace[]="DESCRIPCION = '$actFields[descripcion]'";
            }
            if (!isset($actFields["nombre"][0])) {
                echo "ERROR:No se identifica la acción a guardar";
                doclog("Error: No se incluye identificador a guardar","usuarios",$_POST+["trace"=>$trace]);
                return;
            }
            $trace[]="VALIDA NOMBRE";
            if (isset($actId[0]) && $actId!=="undefined") {
                $actFields["id"]=$actId;
                $trace[]="HAS ID = '$actId'";
                if (!$actObj->saveRecord($actFields)) {
                    echo "ERROR:No se pudo guardar acción";
                    doclog("Error: Falló guardar datos","usuarios",$_POST+["errno"=>DBi::getErrno(),"error"=>DBi::getError(),"query"=>$query,"trace"=>$trace]);
                    return;
                }
                $trace[]="SAVE ACCIONES: '$query'";
                $queries["save"]=$query;
            } else if (!$actObj->insertRecord($actFields)) {
                echo "ERROR:No se pudo guardar acción";
                doclog("Error: Falló insertar datos","usuarios",$_POST+["errno"=>DBi::getErrno(),"error"=>DBi::getError(),"query"=>$query,"trace"=>$trace]);
                return;
            } else {
                $trace[]="INSERT ACCIONES: '$query'";
                $queries["insert"]=$query;
                $actId=$actObj->lastId;
                if (!isset($actId) || $actId==="undefined") {
                    doclog("Error: No se encontró lastId","error",$_POST+["log"=>$actObj->log,"lastId"=>$actObj->lastId,"actId"=>$actId,"trace"=>$trace]);
                    $actId=DBi::getLastId();
                }
                $trace[]="NEW ID: '$actId'";
                $_POST["id"]=$actId;
            }
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioAdmin($actId,"GuardarAccion","",http_build_query($actFields, '', ','));
            $queries["proceso"]=$query;
            doclog("DATA","usuarios",["queries"=>$queries,"post"=>$_POST, "trace"=>$trace]);
            echo $actId;
            break;
        default: echo "ERROR:Petición inválida ($_POST[action])";
    }
}
