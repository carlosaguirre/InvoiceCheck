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
            $pcrObj=new PDFCR(null, null, true);
            $filename=$pcrObj->getFilePath()."prueba{$dataValue}.pdf";
            if(!is_file($filename)) {
                doclog("No existe documento a enviar","docs",$_POST+["filename"=>$filename]);
                die(header("HTTP/1.0 404 NOT FOUND"));
            }
            $usrId=substr($dataValue, 0, -6);
            $dayValue=substr($dataValue, -6);
            $subject="Acumulado de Facturas del dia $dayValue";
            $from=null; // ToDo: Obtener correo de usrId, validar que usrId tenga perfil ReporteFCRD
            if (isset($usrId[0])) {
                global $usrObj;
                if (!$usrObj) {
                    require_once "clases/Usuarios.php";
                    $usrObj=new Usuarios();
                }
                $to=$usrObj->getData("id=$usrId",0,"email address,persona name");
                if (!isset($to[0])) $to=getMailAddressesByProfile("Reporte FCRD");
            } else $to=getMailAddressesByProfile("Reporte FCRD");
            $base = file_get_contents(getBasePath()."templates/respGralSolPago.html");
            $webPath=getBaseURL();
            $baseKeyMap = ["%ENCABEZADO%"=>"ACUMULADO DE FACTURAS DIARIAS","%RESPUESTA%"=>"<h2><a href=\"{$webPath}consultas/docs.php?daydoc={$dataValue}\">Documento del dia $dayValue</a></h2>","%BTNSTY%"=>"display:none;","%HOSTNAME%"=>$webPath];
            $mensaje=str_replace(array_keys($baseKeyMap),array_values($baseKeyMap),$base);
            if (sendMail($subject,$mensaje,$from,$to))
                echo json_encode(["result"=>"success"]);
            else
                echo json_encode(["result"=>"error"]);
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
        default: 
        doclog("Petición Inválida","pruebas",$_POST);
        echo json_encode(["result"=>"error", "message"=>"PETICION INVALIDA ($_POST[action])"]);
        die(header("HTTP/1.0 412 Precondition failed")); //Throw an error on failure
    }
}
