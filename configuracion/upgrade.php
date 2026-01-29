<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$filepackMessage="";
$filepackClass=" class=\"preline\"";
$scriptpackMessage="";
$scriptpackClass=" class=\"preline\"";
if (!$_esDesarrollo) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$keyphrase = $_POST["keyphrase"]??""; $checkphrase=null;
if (isset($keyphrase[0])) {
    require_once "clases/InfoLocal.php";
    $infObj = new InfoLocal();
    $infData = $infObj->getData("nombre in ('upgradeCode','upgradeSeed')",0,"nombre,valor");
    $dbpass = null; $dbseed = null;
    foreach ($infData as $idx => $infRow) {
        if ($infRow["nombre"]==="upgradeCode")
            $dbpass = $infRow["valor"];
        else if ($infRow["nombre"]==="upgradeSeed")
            $dbseed = $infRow["valor"];
    }
    $checkphrase = hash('sha256', $keyphrase.$dbseed);
    for($round = 0; $round < 65536; $round++) {
        $checkphrase = hash('sha256', $checkphrase.$dbseed);
    }
}
$showContent=(isset($keyphrase[0])&&$dbpass===$checkphrase)||isset($_FILES["filepack"])||isset($_FILES["scriptpack"]);
if (isset($_FILES["filepack"])) { 
    $paraERollos=(($_POST["erollos"]??"0")==="1");
    $validMimeType=["application/x-zip-compressed"];
    $pack=$_FILES["filepack"];
    $filepackMessage.="Recibido Archivo $pack[name], tipo $pack[type], tamaño ".sizeFix($pack["size"]);
    $filepackError=getFileUploadError($pack, $validMimeType);
    if (isset($filepackError[0])) {
        $filepackMessage.="\nError $filepackError";
        $filepackClass=" class=\"preline bgred2\"";
    } else {
        $filepackClass=" class=\"preline bgwhite\"";
        $basePath="";
        if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $basePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
        else if (!empty($_SERVER['DOCUMENT_ROOT'])) $basePath = $_SERVER['DOCUMENT_ROOT'];
        if ($paraERollos)
            $basePath = dirname($basePath)."\\Rollos\\";
        //$filepackMessage.=", basePath '$basePath'";
        $sharePath="C:/InvoiceCheckShare/";
        if(file_exists($sharePath) && is_dir($sharePath)) {
            $sharePath.="actualizaciones/";
            if (file_exists($sharePath)||mkdir($sharePath,0777,true)) {
                if ($paraERollos) {
                    $sharePath.="erollos/";
                    file_exists($sharePath)||mkdir($sharePath,0777,true);
                } else {
                    $sharePath.="invoicecheck/";
                    file_exists($sharePath)||mkdir($sharePath,0777,true);
                }
                $timeSuffix=date("YmdHis");
                if (move_uploaded_file($pack["tmp_name"], $sharePath."upgrade{$timeSuffix}.zip")===false) { // $pack["name"]
                    $filepackMessage.="\nError: Falló la copia del archivo";
                    $filepackClass=" class=\"preline bgred2\"";
                } else {
                    $filepackMessage.="\nArchivo copiado en $sharePath";
                    $zip=new ZipArchive();
                    $res=$zip->open($sharePath."upgrade{$timeSuffix}.zip", ZipArchive::CREATE); // $pack["name"] // | ZipArchive::OVERWRITE);
                    if ($res===true) {
                        //$timeSuffix=date("YmdHis");
                        $zipBK=new ZipArchive();
                        $bkupList=[];
                        if ($zipBK->open($sharePath."backup{$timeSuffix}.zip", ZipArchive::CREATE)=== true) {
                            for ($i=0; $i<$zip->numFiles; $i++) {
                                $absExtractFile=$basePath.$zip->getNameIndex($i);
                                if (file_exists($absExtractFile) && !is_dir($absExtractFile)) {
                                    $zipBK->addFile($absExtractFile,$zip->getNameIndex($i));
                                    $bkupList[$zip->getNameIndex($i)]=1;
                                }
                            }
                        }
                        $zipBK->close();

                        for ($i=0; $i<$zip->numFiles; $i++) {
                            $absExtractFile=$basePath.$zip->getNameIndex($i);
                            $fileExists=file_exists($absExtractFile);
                            $fileIsDir=is_dir($absExtractFile);
                            if (!$fileExists || !$fileIsDir) {
                                $filepackMessage.="\nArchivo $absExtractFile";
                                if ($fileExists) $filepackMessage.=" encontrado";
                                $resX=$zip->extractTo($basePath,array($zip->getNameIndex($i)));
                                if ($resX===true) {
                                    if ($fileExists) {
                                        if (isset($bkupList[$zip->getNameIndex($i)])) $filepackMessage.=", respaldado";
                                        $filepackMessage.=" y reemplazado!";
                                    } else $filepackMessage.=" creado!";
                                } else {
                                    if ($fileExists) $filepackMessage.=" pero";
                                    $filepackMessage.=" extracción fallida!";
                                }
                            }
                        }
                        /*
                        $res2=$zip->extractTo($basePath);
                        if ($res2===false) {
                            $filepackMessage.="\nError: Falló la extracción del archivo";
                            $filepackClass=" class=\"preline bgred2\"";
                        } else if ($res2===true) {
                            $filepackMessage.="\nActualización Satisfactoria!";
                        } else {
                            $filepackMessage.="\nActualización generó valor $res2";
                        }
                        */
                        $zip->close();
                    } else {
                        switch($res) {
                            case ZipArchive::ER_EXISTS:
                                $filepackMessage.="\nError: Falló la apertura del archivo. El archivo ya existe"; break;
                            case ZipArchive::ER_INCONS:
                                $filepackMessage.="\nError: Falló la apertura del archivo. El archivo es inconsistente"; break;
                            case ZipArchive::ER_MEMORY:
                                $filepackMessage.="\nError: Falló la apertura del archivo. Memoria insuficiente"; break;
                            case ZipArchive::ER_NOENT:
                                $filepackMessage.="\nError: Falló la apertura del archivo. No existe el archivo"; break;
                            case ZipArchive::ER_NOZIP:
                                $filepackMessage.="\nError: Falló la apertura del archivo. No es un archivo ZIP"; break;
                            case ZipArchive::ER_OPEN:
                                $filepackMessage.="\nError: Falló la apertura del archivo. No fue posible abrir el archivo"; break;
                            case ZipArchive::ER_READ:
                                $filepackMessage.="\nError: Falló la apertura del archivo. No fue posible leer el archivo"; break;
                            case ZipArchive::ER_SEEK:
                                $filepackMessage.="\nError: Falló la apertura del archivo. No fue posible buscar en el archivo"; break;
                            default:
                                $filepackMessage.="\nError: Falló la apertura del archivo. Código desconocido ($res)"; break;
                        }
                        $filepackClass=" class=\"preline bgred2\"";
                    }
                }
            } else {
                $filepackMessage.="\nError: Falló la ubicación de carpeta Actualizaciones";
                $filepackClass=" class=\"preline bgred2\"";
            }
        } else {
            $filepackMessage.="\nError: Falló la ubicación de carpeta InvoiceCheckShare";
            $filepackClass=" class=\"preline bgred2\"";
        }
    }

} else if (isset($_FILES["scriptpack"])) {
    $pack=$_FILES["scriptpack"];
    $scriptpackMessage="Recibido Archivo $pack[name], tipo $pack[type], tamaño ".sizeFix($pack["size"]);
    $scriptpackError=getFileUploadError($pack);
    if (isset($scriptpackError[0])) {
        $scriptpackMessage.=", error $scriptpackError";
        $scriptpackClass=" class=\"preline bgred2\"";
    } else $scriptpackClass=" class=\"preline bgwhite\"";
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function getFileUploadError($file, $validType=null, $validExt=null) {
    switch($file["error"]) {
        case UPLOAD_ERR_OK: 
            if (empty($file["name"])) return "No se identifica el nombre de archivo.";
            else if (empty($file["type"])) return "No se identifica el tipo de archivo.";
            else if (!isValidType($file["type"],$validType)) return "Tipo de archivo no v&aacute;lido."; // $validType=["text/xml","application/xml","text/plain"];
            else {
                $ext=pathinfo($file["name"], PATHINFO_EXTENSION);
                $validExt=["zip"];
                if (!isValidType(strtolower($ext),$validExt)) return "Tipo de archivo inv&aacute;lido.";
            }
/*
            else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $tmpType = $finfo->file($file["tmp_name"]);
                if ($tmpType!="text/xml" && $tmpType!="application/xml" && $tmpType!="text/plain") {
                    if ($tmpType==="application/octet-stream") {
                        $fp = fopen($file["tmp_name"],"r");
                        $ftxt = fread($fp, 10);
                        fclose($fp);
                        if (strpos($ftxt,"<?xml")!==false) {
                            break;
                        }
                    }
                    anexaError("El archivo no tiene formato XML reconocible: $tmpType.");
                }
            }
            */
            break;
        case UPLOAD_ERR_NO_FILE: return "No se envi&oacute; el archivo.";
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: return "El archivo es demasiado grande.";
        default: return "Error de carga de archivo desconocido ($file[error]).";
    }
    return "";
}
function isValidType($filetype, $validTypes=null) {
    if (!isset($validTypes)) return true;
    if (is_array($validTypes) && isset($validTypes[0])) return in_array($filetype, $validTypes);
    if (is_string($validTypes)) return $filetype===$validTypes;
    return false;
}
