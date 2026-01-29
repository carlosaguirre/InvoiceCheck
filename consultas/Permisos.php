<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Permisos.php";

$prmObj = new Permisos();
if (isValueService()) getValueService($prmObj);
else if (isDataService()) getDataService();
else if (isTestService()) getTestServicePRM();
else if (isCatalogService()) getCatalogService($prmObj);
else if (isActionServicePRM()) doActionServicePRM();
die();
function isActionServicePRM() {
    return isset($_POST["action"]);
}
function doActionServicePRM() {
    global $prmObj, $prcObj;
    sessionInit();
    if (!hasUser()) {
        echo "REFRESH";
        die();
    }
    switch($_POST["action"]??"") {
        case "adminSave":
            $prfId=$_POST["id"]??"";
            if (!isset($prfId[0])) {
                echo "ERROR:No se identifica el perfil a modificar";
                doclog("Error: No se identifica el perfil","usuarios",$_POST);
                die();
            }
            $hasReadList=isset($_POST["readList"]);
            $hasWriteList=isset($_POST["writeList"]);
            if (!$hasReadList && !$hasWriteList) {
                echo "ERROR:No se identifican acciones para dar permiso";
                doclog("Error: No se identifican las acciones","usuarios",$_POST);
                die();
            }
            global $query;
            $readList=$hasReadList?explode(",", $_POST["readList"]):null;
            $writeList=$hasWriteList?explode(",", $_POST["writeList"]):null;
            $mergeList=$hasReadList?($hasWriteList?array_unique(array_merge($readList,$writeList)):$readList):$writeList;
            sort($mergeList);
            doclog("DATA","usuarios",["read"=>$readList,"write"=>$writeList,"merge"=>$mergeList]);
            $permissionList=[];
            foreach ($mergeList as $idx => $actConId) {
                $readId=($hasReadList&&in_array($actConId,$readList))?"1":"0";
                $writeId=$hasWriteList&&in_array($actConId,$writeList)?"1":"0";
                $permissionList[]=[$prfId,$actConId,$readId,$writeId];
            }
            $prmObj->deleteRecord(["idPerfil"=>$prfId]);
            $queries=["delete"=>$query];
            if (isset($permissionList[0])) {
                $prmObj->insertMultipleRecords(["idPerfil","idAccion","consulta","modificacion"],$permissionList);
                $queries["insert"]=$query;
            }
            doclog("QUERY","usuarios",["queries"=>$queries,"post"=>$_POST]);
            echo $prfId; //implode(",", $permissionList);
            break;
    }
}
function getTestServicePRM() {
    global $prmObj;
    switch($_GET["test"]) {
        case "acciones": {
            echo "Acciones:\n";
            require_once "clases/Acciones.php";
            $actObj = new Acciones();
            echo $actObj->getList(false,false,"id,nombre");
        }
        break;
        case "usuario":
            sessionInit();
            $usr = getUser();
            if (empty($usr)) echo "USUARIO DESCONOCIDO";
            else echo "USUARIO: ".$usr->id." : ".$usr->nombre." : ".$usr->persona;
        break;
        case "validar":
            sessionInit();
            $usr = getUser();
            echo "Validar Servicio: ".get_class($prmObj);
            if (empty($usr)) echo "<br>USUARIO DESCONOCIDO";
            else if (isset($_GET["accion"])) {
                $accion = $_GET["accion"];
                $miConsulta = $prmObj->consultaValida($usr, $accion);
                $miModifica = $prmObj->modificacionValida($usr, $accion);
                $separator = ($miConsulta&&$miModifica)?" y ":"";
                echo "<br>Usuario $usr->nombre, Accion $accion : ".($miConsulta?"Consultar":"").$separator.($miModifica?"Modificar":"");
            } else echo "<br>SIN ACCION";
            break;
        default:
            getTestService($prmObj);
    }
}

function isDataService() {
    return isset($_GET["clase"]) && $_GET["clase"]=="Permisos" && isset($_GET["perfil"]);
}
function getDataService() {
    global $prmObj;
    DBi::connect();
    $prmObj->rows_per_page=0;
    $perfilData = $prmObj->getData("idPerfil=".$_GET["perfil"]);
    if (isset($_GET["modo"])) $modo = $_GET["modo"];
    if (!isset($modo) || empty($modo) || $modo=="JSON") {
        $returnArray = [];
        foreach($perfilData as $row) {
            $returnArray[intval($row["idAccion"])] = [$row["consulta"], $row["modificacion"]];
        }
        echo json_encode($returnArray);
    } else if ($modo=="HTML") {
        echo "<html><head><title>Permisos</title></head><body>";
        echo "<h1>PERFIL ".$_GET["perfil"]."</h1>";
        echo "<table><thead><tr><th>idPerfil</th><th>idAccion</th><th>Consulta</th><th>Modificaci&oacute;n</th></tr></thead><tbody>";
        foreach($perfilData as $row) {
            echo "<tr><td>$row[idPerfil]</td><td>$row[idAccion]</td><td>$row[consulta]</td><td>$row[modificacion]</td></tr>";
        }
        echo "</tbody></table>";
        echo "LOG: ".$prmObj->log;
        echo "</body></html>";
    }
    DBi::close();
}
