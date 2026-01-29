<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Logs.php";

$obj = new Logs();
sessionInit();
if (!hasUser()) echoJsNDie("refresh","Sin sesion");
global $docRoot;
$docRoot = $_SERVER["DOCUMENT_ROOT"];
if (isValueService()) getValueService($obj);
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
else if (isCommandService()) doCommandService($obj);
else if (isLogService()) doLogService();
else if (isReadRequest()) doReadRequest();
else if (isset($_GET["userid"]) && isset($_GET["section"]) && isset($_GET["text"])) {
    if ($obj->agrega($_GET["userid"], $_GET["section"], $_GET["text"])) {
        echo $obj->lastId . " : " . $_GET["userid"] . " (" . $_GET["section"] . ") " . $_GET["text"];
    } else {
        echo $obj->lastError;
    }
}
else if (isset($_GET["trace"])) {
    thirdFunction($obj, $_GET["trace"]);
    echo "<br><xmp>";
    print_r($_SERVER);
    echo "</xmp>";
}
else {
    echo "Logs";
}

// action  : "doclog",
// message : "[SOLPAGO.REQAUTH] url:"+url+", params:"+JSON.stringify(params,jsonCircularReplacer())
function isLogService() {
    return isset($_POST["action"][0])&&$_POST["action"]==="doclog";
}
function doLogService() {
    $message=$_POST["message"]??"";
    $filebase=$_POST["filebase"]??null;
    if (isset($_POST["data"])) {
        try {
            if (is_string($_POST["data"]))
                $data=json_decode($_POST["data"],true,512,JSON_THROW_ON_ERROR);
            else $data=$_POST["data"];
        } catch (JsonException $je) {
            $data=["data"=>$_POST["data"],"error"=>["name"=>get_class($je),"code"=>$je->getCode(),"message"=>$je->getMessage()]];
        }
    } else $data=null;
    if (isset($message[0])) {
        doclog($message,$filebase,$data);
        if (array_key_exists("json", $_POST)) successNDie("1",$data);
        echo "1";
    } // else echo "0";
}
function isReadRequest() {
    return isset($_POST["action"][0])&&$_POST["action"]==="readRequest";
}
function doReadRequest() {
    global $docRoot;
    $logRoot = $docRoot."LOGS/";
    $logLen = strlen($logRoot);
    $path=$_POST["path"]??"";
    if (!isset($path[0])) errNDie("No se reconoce la ruta de búsqueda",$_POST);
    $resultData=["result"=>""];
    if (isset($path[6])) {
        $path=$logRoot.$path.".log";
    } else if (isset($path[5])) {
        $list=glob($logRoot.$path."/*.log");
        natcasesort($list);
        $resultData["list"]=[];
        $fileIdx=$_POST["index"]??0;
        foreach ($list as $idx => $filepath) {
            if (is_dir($filepath)) continue;
            $userlog=substr($filepath, $logLen, -4);
            $resultData["list"][]=$userlog;
            if (substr($userlog,7)==="error" && !isset($_POST["index"])) $fileIdx=count($resultData["list"])-1;
        }
        $path=$logRoot.$resultData["list"][$fileIdx].".log";
        $resultData["filename"]=substr($resultData["list"][$fileIdx], 7);
    } else $path="";
    if (isset($path[0])) {
        $lines=[]; $block=[];
        $fd = fopen ($path, "r");
        $len=0;
        while (!feof ($fd)) {
            $buffer = trim(fgets($fd, 4096));
            if (isset($buffer[0])) {
                if ($buffer[0]==="[") {
                    if (isset($block[0])) {
                        array_splice($lines, 0, 0, $block);
                        $block=[];
                    }
                }
                $block[]=$buffer;
                $len++;
            }
        }
        fclose ($fd);
        if (isset($block[0])) {
            array_splice($lines, 0, 0, $block);
            $block=[];
        }
        //$lines=array_reverse($lines);
        $resultData["message"]=implode("\n", $lines);
        if (isset($resultData["message"][0])) $resultData["result"]="success";
        $resultData["result"]="success";
        if (isset($fileIdx)) $resultData["index"]=$fileIdx;
    }
    if (!isset($resultData["result"][0])) {
        $resultData["result"]="error";
        $resultData["message"]="Archivo vacío o no encontrado.";
    }
    echo json_encode($resultData);
}
function isCommandService() {
    return isset($_POST["command"][0]);
}
function doCommandService($obj) {
    switch ($_POST["command"]) {
        case "eliminar":
            deleteLogByKey($_POST["key"]??"");
            break;
        case "lectura":
            readLogByKey($_POST["key"]??"");
            break;
    }
}
function thirdFunction($obj, $traceStr) {
    echo $obj->trace_test($traceStr);
}
function readLogByKey($key) {
    $path="C:\\InvoiceCheckShare\\";
    $sizeunits="BKMGTP";
    switch($key) {
        case "autoCFDIP":
            if (!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Alta Pagos")&&!validaPerfil("Carga Egresos")) return;
            $logName="cfdis.log";
            $subpathList=["CFDIs"=>[],"aceptados"=>[],"rechazados"=>[],"fallidos"=>[],"reintentar"=>[],"ignorados"=>[]];
            $retlog="";
            foreach ($subpathList as $subpathKey=>$subpathArray) {
                $tmplog="";
                foreach (glob("{$path}{$subpathKey}\\*.*") as $filename) {
                    $filebytes=filesize($filename);
                    $filefactor=intval((strlen("".$filebytes)-1)/3);
                    $powfactor=pow(1024,$filefactor);
                    $fileunits=@$sizeunits[$filefactor].($filefactor>0?"B":"yte");
                    $filesizeh=sprintf("%.2f", $filebytes/$powfactor).$fileunits;
                    $fileIdx=strrpos($filename, "\\");
                    if ($fileIdx!==false) $filename=substr($filename, $fileIdx+1);
                    $subpathList[$subpathKey][]=["fname"=>$filename,"fsize"=>$filesizeh];
                    if (isset($tmplog[0])) $tmplog.=", ";
                    $tmplog.=$filename;
                }
                if(!isset($tmplog[0])) $tmplog="empty";
                //if(isset($tmplog[0])) {
                    if(isset($retlog[0])) $retlog.=",";
                    $retlog.=$path.$subpathKey.":".$tmplog;
                //}
            }
            break;
        case "pagos":
            if (!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Carga Egresos")) return;
            $logName="pagos.log";
            break;
        default: return;
    }
    //if(!isset($path[0]) || !file_exists($path) || filetype($path)!==file || filesize($path)==0) return;
    $message=file_get_contents($path.$logName);
    $retval=["result"=>"exito","message"=>$message];
    if (isset($subpathList)) $retval["filelist"]=$subpathList;
    if (isset($retlog[0])) $retval["filelog"]=$retlog;
    echo json_encode($retval);
}
function deleteLogByKey($key) {
    $path="C:\\InvoiceCheckShare\\";
    switch($key) {
        case "autoCFDIP":
            if (!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Alta Pagos")&&!validaPerfil("Carga Egresos")) return;
            $path.="cfdis.log";
            break;
        case "pagos":
            if (!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Carga Egresos")) return;
            $path.="pagos.log";
            break;
        default: return;
    }
    file_put_contents($path, "");
    echo json_encode(["result"=>"exito","message"=>"Borrado de Log satisfactorio"]);
}
