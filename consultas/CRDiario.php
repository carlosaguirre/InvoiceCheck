<?php
require_once dirname(__DIR__)."/bootstrap.php";

if (isActionService()) doActionService();
die();
function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    sessionInit();
    if (!hasUser()) {
        echo json_encode(["result"=>"refresh"]);
        die();
    }
    global $query;
    $queries=[];
    switch($_POST["action"]??"") {
        case "deldoc":
            if (!isset($_POST["filename"][0])) {
                doclog("No se indica documento a borrar","docs",$_POST);
                die(header("HTTP/1.0 400 FALTA INDICAR DOCUMENTO")); // BAD REQUEST
            }
            require_once "clases/PDFCR.php";
            $pcrObj=new PDFCR(null, null, true);
            $filename=$pcrObj->getFilePath().$_POST["filename"];
            if(@unlink($filename)!==true) {
                if (!file_exists($filename)) {
                    $docmsg="No existe el documento indicado";
                    $hdrmsg="404 NO EXISTE EL DOCUMENTO"; // NOT FOUND
                } else if (!is_file($filename)) {
                    $docmsg="La ruta indicada no corresponde a un archivo";
                    $hdrmsg="406 NO ES UN DOCUMENTO"; // NOT ACCEPTABLE
                } else {
                    $docmsg="No se pudo borrar el archivo";
                    $hdrmsg="403 NO SE PUDO BORRAR EL DOCUMENTO"; // 403=FORBIDDEN, 401=UNAUTHORIZED
                }
                doclog($docmsg,"docs",$_POST);
                die(header("HTTP/1.0 $hdrmsg"));
            }
            echo json_encode(["result"=>"success"]);
            break;
        case "snddoc":
            $dataValue=$_POST["value"]??"";
            if (!isset($dataValue[0])) {
                doclog("No se indica valor de envío","docs",$_POST);
                die(header("HTTP/1.0 400 FALTA VALOR DE REGISTRO")); // BAD REQUEST
            }
            require_once "clases/PDFCR.php";
            $result=PDFCR::sendReportByMail($dataValue,"snddoc");
            if (!isset($result["result"]))
                die(header("HTTP/1.0 400 BAD REQUEST"));
            if ($result["result"]==="nocontent")
                die(header("HTTP/1.0 204 NO CONTENT"));
            if ($result["result"]==="empty") $result["result"]="error";
            echo json_encode($result);
            break;
        case "pdfcr":
            require_once "clases/PDFCR.php";
            $result=PDFCR::autoReport($_POST["date"]??null);
            if (!isset($result["result"]))
                die(header("HTTP/1.0 400 BAD REQUEST"));
            if ($result["result"]==="nocontent")
                die(header("HTTP/1.0 204 NO CONTENT"));
            if ($result["result"]==="empty") $result["result"]="error";
            echo json_encode($result);
            break;
        case "changelog":
            $day=$_POST["dt"];
            $shf=+$_POST["shift"];
            $filename=$_POST["fname"]??"crdiario";
            $date = DateTime::createFromFormat('ymd', $day);
            if ($shf<0) $shf="$shf";
            else if ($shf>0) $shf="+".$shf;
            else if ($filename===($_POST["oname"]??"crdiario")) errNDie("SIN CAMBIOS");
            $newday=$date->modify("$shf days")->format('ymd');
            $logInfo=file_get_contents(getBasePath()."LOGS/$newday/{$filename}.log");
            $msgTxt="ALL GOOD";
            if ($logInfo===false) {
                $logInfo="SIN LOG";
                $msgTxt="SIN LOG";
            }
            echo json_encode(["result"=>"success","message"=>$msgTxt,"log"=>$logInfo,"dt"=>$newday]);
            break;
        default: 
            doclog("Petición Inválida","crdiario",$_POST);
            echo json_encode(["result"=>"error", "message"=>"PETICION INVALIDA ($_POST[action])"]);
            die(header("HTTP/1.0 412 Precondition failed")); //Throw an error on failure
    }
}
