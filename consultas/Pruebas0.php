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
            $dayValue=$_POST["value"]??"";
            if (!isset($dayValue[0])) {
                doclog("No se indica valor de envío","docs",$_POST);
                die(header("HTTP/1.0 400 FALTA VALOR DE REGISTRO")); // BAD REQUEST
            }
            require_once "clases/PDFCR.php";
            $pcrObj=new PDFCR(null, null, true);
            $filename=$pcrObj->getFilePath()."prueba{$dayValue}.pdf";
            if(!is_file($filename)) {
                doclog("No existe documento a enviar","docs",$_POST+["filename"=>$filename]);
                die(header("HTTP/1.0 404 NOT FOUND"));
            }
            $subject="Acumulado de Facturas del dia $dayValue";
            $from=null;
            $to=getMailAddressesByProfile("Reporte FCRD"); // ,$grpId=false
            $base = file_get_contents(getBasePath()."templates/respGralSolPago.html");
            $webPath=getBaseURL();
            $baseKeyMap = ["%ENCABEZADO%"=>"ACUMULADO DE FACTURAS DIARIAS","%RESPUESTA%"=>"<h2><a href=\"{$webPath}consultas/docs.php?daydoc={$dayValue}\">Documento del dia $dayValue</a></h2>","%BTNSTY%"=>"display:none;","%HOSTNAME%"=>$webPath];
            $mensaje=str_replace(array_keys($baseKeyMap),array_values($baseKeyMap),$base);
            if (sendMail($subject,$mensaje,$from,$to))
                echo json_encode(["result"=>"success"]);
            else
                echo json_encode(["result"=>"error"]);
            break;
        case "pdfcr":
        require_once "clases/PDFCR.php";
        $pcrObj=new PDFCR($_POST["date"]??null);
        if ($pcrObj->hasErrors()) {
            doclog("PDFCR ERROR","pruebas", ["error"=>$pcrObj->getErrors()]);
            echo json_encode(["result"=>"error", "message"=>"ERROR EN PREPARACION DE DATOS", "errors"=>$pcrObj->getErrors()]);
            die(header("HTTP/1.0 512 SETUP ERROR"));
        } else if ($pcrObj->isEmpty()) {
            doclog("PDFCR VACIO","pruebas", ["query"=>$query]);
            echo json_encode(["result"=>"error", "message"=>"DIA $_POST[date] SIN REGISTROS"]);
        } else {
            //select idUsuario, group_concat(idGrupo) idGrupos from usuarios_grupo where idPerfil=120 group by idUsuario;
            // ToDo: do { $progress=$pcrObj->createFileProgressively(); echo "|".$progress; flush_all(); } while(!in_array($progress,["100.00","ERROR","EMPTY"]));
            $newFileNames=$pcrObj->createFiles();
            $newFileNamesTxt=implode(", ", array_values($newFileNames));
            if ($pcrObj->hasErrors()) {
                doclog("PDFCR ERROR","pruebas",["files"=>$newFileNames,"error"=>$pcrObj->getErrors(),"idErr"=>$pcrObj->errIds]);
                if (empty($newFileNames)) {
                    echo json_encode(["result"=>"error", "message"=>"ERROR EN CREACION DE ARCHIVO", "errors"=>$pcrObj->getErrors()]);
                    die(header("HTTP/1.0 513 FILE ERROR"));
                }
                $message="Proceso PDFCR concluido con errores: $newFileNamesTxt";
            } else $message="Proceso PDFCR concluido sin errores: $newFileNamesTxt";
            $fileList=glob($pcrObj->getFilePath()."*.pdf");
            $lastWeekTime=strtotime("-1 week");
            natsort($fileList);
            $fileDataList=[];
            foreach (array_reverse($fileList) as $idx => $filePath) {
                $fileTime=getCorrectMTime($filePath);
                if ($fileTime<$lastWeekTime) {
                    unlink($filePath);
                    //unset($fileList[$idx]);
                } else {
                    $fileDataList[]=["name"=>basename($filePath),"size"=>sizeFix(filesize($filePath))];
                }
            }
            echo json_encode(["result"=>"success", "message"=>$message, "errors"=>$pcrObj->getErrors(), "list"=>$fileDataList]);
        }
        break;
        default: 
        doclog("Petición Inválida","pruebas",$_POST);
        echo json_encode(["result"=>"error", "message"=>"PETICION INVALIDA ($_POST[action])"]);
        die(header("HTTP/1.0 412 Precondition failed")); //Throw an error on failure
    }
}
