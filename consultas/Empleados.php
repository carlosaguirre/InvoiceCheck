<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Empleados.php";

$obj = new Empleados();
if (isEditService()) doEditService($obj);
else if (isBrowseService()) doBrowseService($obj);
else if (isMassUploadService()) doMassUploadService($obj);
else if (isValueService()) getValueService($obj);
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
die();
function isEditService() {
    return isset($_POST["accion"])&&$_POST["accion"]==="edit";
}
function doEditService($obj) {
    
}
function isBrowseService() {
    return isset($_POST["accion"])&&$_POST["accion"]==="browse";
}
function doBrowseService($obj) {
    global $query;
    if(isset($_POST["rowsPerPage"])) $obj->rows_per_page=+$_POST["rowsPerPage"];
    else $obj->rows_per_page=10;
    if(isset($_POST["pageNum"])) $obj->pageno=+$_POST["pageNum"];
    else $obj->pageno=0;
    $data=$obj->getDataByFieldArray($_POST);
    echo json_encode(["result"=>$data,"query"=>$query,"lastPage"=>$obj->lastpage]);
}
function isMassUploadService() {
    return isset($_POST["accion"])&&$_POST["accion"]==="massUpload";
}
function doMassUploadService($obj) {
    if (!isset($_FILES)) dieError("No se recibieron archivos");
    if (empty($_FILES["file"])) dieError("Sin archivo valido");
    $file=$_FILES["file"];
    if (!empty($file["error"])) dieError("Error en captura de archivo $file[name]: $file[error]");
    if ($file["size"]==0) dieError("Archivo $file[name] Vacio");
    $handle=fopen($file["tmp_name"],"r");
    if($handle===FALSE) dieError("Error al cargar archivo $file[name]");
    $isWinEncoding=!isset($_POST["encoding"])||$_POST["encoding"]==="Windows-1252";
    $time_start=microtime(true);
    DBi::autocommit(false);
    $loops=0;
    $accepted=0;
    $errors=[];
    $allIds=[];
    $lap_max=0;
    $lap_min=0;
    $lap_sum=0;
    while (($lineArr=fgetcsv($handle,1000,","))!==FALSE) {
        if ($isWinEncoding) $lineArr=array_map("convert", $lineArr);
        $loops++;
        if (!isset($lineArr[0])) {
            $errors[]="Linea $loops vacia";
            continue;
        }
        if (!isset($lineArr[5])) {
            $errors[]="Linea $loops incompleta";
            continue;
        }
        if (!isset($lineArr[0][0])) {
            $errors[]="Linea $loops sin numero";
            continue;
        }
        $lap_start=microtime(true);
        if (isset($lineArr[6])) {
            $errors[]="Exceso de campos en linea $loops";
        }
        $fieldarr=["numero"=>$lineArr[0]];
        if (!isset($lineArr[1][0])) {
            $errors[]="Linea $loops sin nombre";
        } else $fieldarr["nombre"]=$lineArr[1];
        if (!isset($lineArr[2][0])&&!isset($lineArr[3][0])) {
            $errors[]="Linea $loops sin cuenta bancaria";
        } else {
            if (isset($lineArr[2][0])) $fieldarr["cuentaTC"]=$lineArr[2];
            if (isset($lineArr[3][0])) $fieldarr["cuentaCLABE"]=$lineArr[3];
        }
        if (!isset($lineArr[4][0])) {
            $errors[]="Linea $loops sin empresa";
        } else $fieldarr["empresa"]=$lineArr[4];
        if (!isset($lineArr[5][0])) {
            $errors[]="Linea $loops sin status inicial";
            $fieldarr["status"]="activo";
        } else $fieldarr["status"]=$lineArr[5];
        if ($obj->saveRecord($fieldarr)) $accepted++;
    }
    DBi::autocommit(TRUE);
    echo json_encode(["result"=>"RECIBIDOS $loops, ACEPTADOS $accepted","error"=>$errors]);
}
function convert( $str ) {
    return iconv("Windows-1252", "UTF-8", $str);
}
function dieError($message) {
    echo json_encode(["result"=>"Error","message"=>$message]);
    die();
}
