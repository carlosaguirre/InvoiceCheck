<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Perfiles.php";

$prfObj = new Perfiles();
if (isValueService()) getValueService($prfObj);
else if (isTestService()) getTestService($prfObj);
else if (isCatalogService()) getCatalogService($prfObj);
else if (isActionService()) doActionService();
die();
function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    global $prfObj, $prcObj;
    sessionInit();
    if (!hasUser()) {
        echo "REFRESH";
        die();
    }
    global $query;
    $queries=[];
    switch($_POST["action"]??"") {
        case "adminDelete":
            $prfId=$_POST["id"]??"";
            if (!isset($prfId[0])) {
                echo "ERROR:No se identifica el perfil a borrar";
                doclog("Error: No se incluye identificador a borrar","usuarios",$_POST);
                die();
            }
            $prfData=$prfObj->getData("id='$prfId'");
            if (!isset($prfData[0])) {
                echo $prfId; // Regresar mismo Id para que se borre.
                doclog("Error: No existe el id","usuarios",$_POST);
                die();
            }
            if (!$prfObj->deleteRecord(["id"=>$prfId])) {
                echo "ERROR:Error de datos ".DBi::getErrno()." : ".DBi::getError();
                doclog("Error: Falló borrar datos","usuarios",$_POST+["errno"=>DBi::getErrno(),"error"=>DBi::getError(),"query"=>$query]);
                die();
            }
            $queries["delete"]=$query;
            echo $prfId;
            if (!isset(($prcObj))) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioAdmin($prfId,"BorrarPerfil","",http_build_query($prfData[0],'',';'));
            $queries["proceso"]=$query;
            doclog("DATA","usuarios",["queries"=>$queries,"post"=>$_POST]);
            break;
        case "adminSave":
            $prfId=$_POST["id"]??"";
            $prfFields=[];
            if (isset($_POST["name"])) $prfFields["nombre"]=$_POST["name"];
            if (isset($_POST["desc"])) $prfFields["detalle"]=$_POST["desc"];
            if (isset($_POST["stat"])) $prfFields["estado"]=$_POST["stat"];
            if (!isset($prfFields["nombre"][0])) {
                echo "ERROR:No se identifica el perfil a guardar";
                doclog("Error: No se incluye identificador a guardar","usuarios",$_POST);
                die();
            }
            if (isset($prfId[0])) {
                $prfFields["id"]=$prfId;
                if (!$prfObj->saveRecord($prfFields)) {
                    echo "ERROR:No se pudo guardar perfil";
                    doclog("Error: Falló guardar datos","usuarios",$_POST+["errno"=>DBi::getErrno(),"error"=>DBi::getError(),"query"=>$query]);
                    die();
                }
                $queries["save"]=$query;
            } else if (!$prfObj->insertRecord($prfFields)) {
                echo "ERROR:No se pudo guardar perfil";
                doclog("Error: Falló insertar datos","usuarios",$_POST+["errno"=>DBi::getErrno(),"error"=>DBi::getError(),"query"=>$query]);
                die();
            } else {
                $queries["insert"]=$query;
                $prfId=$prfObj->lastId;
                if (!isset($prfId) || $prfId==="undefined") {
                    doclog("Error: No se encontró lastId","error",$_POST+["log"=>$prfObj->log,"lastId"=>$prfObj->lastId,"prfId"=>$prfId]);
                    $prfId=DBi::getLastId();
                }
                $_POST["id"]=$prfId;
            }
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioAdmin($prfId,"GuardarPerfil","",http_build_query($prfFields, '', ','));
            $queries["proceso"]=$query;
            doclog("DATA","usuarios",["queries"=>$queries,"post"=>$_POST]);
            if (isset($_POST["readList"])||isset($_POST["writeList"])) {
                include "consultas/Permisos.php";
            } else echo $prfId;
            break;
        default: echo "ERROR:Petición inválida ($_POST[action])";
    }
}
