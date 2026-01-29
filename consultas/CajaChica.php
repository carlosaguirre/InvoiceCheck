<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
if ($hasUser) {
    if (isset($_POST["accion"])) switch($_POST["accion"]) {
        case "temporales": doTemporales(); break;                    // VT
        case "addFiles": doAddFiles(); break;                        // CC
        case "delFiles": doDelFiles(); break;
        case "transferFiles": doTransferFiles(); break;
        case "newPettyCashReq": doNewPettyCash(); break;             // CC
        case "getPettyCashReq": doGetPettyCash(); break;             // CC
        case "savePettyCashReq": doSavePettyCash(); break;           // CC
        case "deletePettyCashReq": doDeletePettyCashReq(); break;    // CC
        case "deletePettyCashFile": doDeletePettyCashFile(); break;  // CC
        case "newPerDiem": doNewRecord(); break;                     // VT
        case "getPerDiem": doGetRecord(); break;                     // VT
        case "deleteRecord": doDeleteRecord(); break;                // VT
        case "saveRecord": doSaveRecord(); break;                    // VT
        case "addPerDiem": doAddPerDiem(); break;                    // VT
        case "delPerDiem": doDelPerDiem(); break;                    // VT
        case "fixPerDiem": doFixPerDiem(); break;                    // VT
        case "generaReporte": doReview(); break;
        case "muestraRegistro": doViewRecord(); break;
        case "ajustaStatus": doFixStatus(); break;
    } else if ($_esAdministrador) {
        if (isset($_GET["rename"])) {
            if ($_GET["rename"]==="all") renameAllFiles();
            else if ($_GET["rename"]==="db") renameDBFiles();
        } else if (isset($_GET["transfer"])) {
            if ($_GET["transfer"]==="all") transferAllFiles();
            else if ($_GET["transfer"]==="test") transferTestFile();
        }
    }
} else if (isset($_POST["accion"])) {
    echo json_encode(["result"=>"refresh"]+["post"=>$_POST,"cookies"=>$_COOKIE]);
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function renameAllFiles() {
    $basepath="C:/InvoiceCheckShare/invoiceDocs/viajes/";
    $stats=["xml"=>0, "xmlData"=>0, "pdf"=>0, "mkdir"=>0, "rename"=>0, "errors"=>["xmlData"=>0, "mkdir"=>0, "rename"=>0]];
    $files=getFileList($basepath,$stats);
    echo "<span style=\"font-weight: bold;\">$basepath</span>";
    echo arr2List($files);
}
function getFileList($path,&$stats) {
    $files=glob($path."*");
    $pathLen=strlen($path);
    $fixed=[];
    foreach($files as $abspath) {
        $filepath=substr($abspath, $pathLen);
        if (file_exists($abspath)) {
            if (is_dir($abspath)) $fixed[$filepath]=getFileList($abspath."/",$stats);
            else {
                $mimeType=mime_content_type($abspath);
                $extension=substr($filepath,-3);
                $filename=substr($filepath, 0,-4);
                if ($mimeType==="text/xml"||($mimeType==="text/plain"&&$extension==="xml")) {
                    $stats["xml"]++;
                    $xmlData=getCFDIData($abspath, $filepath, false);
                    if (isset($xmlData["filename"][0])) $stats["xmlData"]++;
                    else { $stats["errors"]["xmlData"]++; continue; }
                    $parentPath=substr($abspath,$pathLen-7,6);
                    $basePath=substr($abspath,0,$pathLen-7);
                    $dtPath=substr($xmlData["fecha"],0,4).substr($xmlData["fecha"],5,2);
                    $fixed[$filename]=["parentPath"=>$parentPath];
                    if ($parentPath!==$dtPath) {
                        $fixed[$filename]["newDatePath"]=$dtPath;
                        $viatpath=$basePath.$dtPath;
                        if (!file_exists($viatpath)) {
                            if (mkdir($viatpath,0777,true)) $stats["mkdir"]++;
                            else { $stats["errors"]["mkdir"]++; continue; }
                        }
                    }
                    if ($filename!==$xmlData["filename"]) {
                        $fixed[$filename]["filename"]=$xmlData["filename"];
                        if (rename($abspath,$basePath.$dtPath."/".$xmlData["filename"].".xml")) {
                            $stats["rename"]++;
                            $fixed[$filename]["filename"].=" => RENAMED!";
                        } else { $stats["errors"]["rename"]++; }
                        if (file_exists($path.$filename.".pdf")) {
                            $stats["pdf"]++;
                            $fixed[$filename]["filepdf"]="EXISTS";
                            if (rename($path.$filename.".pdf",$basePath.$dtPath."/".$xmlData["filename"].".pdf")) {
                                $stats["rename"]++;
                                $fixed[$filename]["filepdf"].=" & RENAMED";
                            } else { $stats["errors"]["rename"]++; }
                        }
                    }
                } else {
                    $fixed[$filename]=["mimeType"=>$mimeType,"extension"=>$extension];
                }
            }
        }
    }
    return $fixed;
}
function renameDBFiles() {
    global $rvcObj, $rarObj, $query;
    $filepath="C:/InvoiceCheckShare/invoiceDocs/viajes/";
    echo "<H1>RENAME TABLE FILES</H1>";
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    $rvcObj->rows_per_page=0;
    $rvcData=$rvcObj->getData("archivoxml is not null", 0, "id,vid,archivoxml,archivopdf");
    $stats=["view"=>0,"xmlNotFound"=>0,"processed"=>0,"fileNotFound"=>0,"mkdir"=>0,"noMkdir"=>0,"updated"=>0,"notUpdated"=>0,"hasPDF"=>0,"xmlRenamed"=>0,"pdfRenamed"=>0,"notRenamed"=>0,"sameXML"=>0,"errors"=>[]];
    foreach ($rvcData as $rvcRow) {
        $stats["view"]++;
        if (!isset($rvcRow["archivoxml"][0])) {
            $stats["xmlNotFound"]++;
            continue;
        }
        $fullpathxml=$filepath.$rvcRow["archivoxml"];
        $fullpathpdf=$filepath.$rvcRow["archivopdf"];
        $xmlData=getCFDIData($fullpathxml, $rvcRow["archivoxml"], false, "CC".$rvcRow["vid"]);
        if (isset($xmlData["filename"][0])) {
            $stats["processed"]++;
            $dtpath=substr($xmlData["fecha"],0,4).substr($xmlData["fecha"],5,2)."/";
            $viatpath=$filepath.$dtpath;
            if (!file_exists($viatpath)) {
                if (mkdir($viatpath,0777,true)) $stats["mkdir"]++;
                else {
                    $stats["noMkdir"]++;
                    continue;
                }
            }
            $newxmlpath=$dtpath.$xmlData["filename"].".xml";
            $newpdfpath=$dtpath.$xmlData["filename"].".pdf";
            $fieldarr=["id"=>$rvcRow["id"]];
            if ($rvcRow["archivoxml"]===$newxmlpath) {
                $stats["sameXML"]++;
                continue;
            }
            $fieldarr["archivoxml"]=$newxmlpath;
            $fieldarr["originalxml"]=$rvcRow["archivoxml"];
            if (isset($xmlData["uuid"][0])) $fieldarr["uuid"]=$xmlData["uuid"];
            if (isset($rvcRow["archivopdf"][0])&&$rvcRow["archivopdf"]!==$newpdfpath) $fieldarr["archivopdf"]=$newpdfpath;
            DBi::autocommit(false);
            if ($rvcObj->updateRecord($fieldarr)) {
                if (rename($fullpathxml, $filepath.$newxmlpath)) {
                    $stats["xmlRenamed"]++;
                    DBi::commit();
                } else {
                    $stats["notRenamed"]++;
                    $stats["errors"][]="NOTRENAMED: $fullpathxml => $newxmlpath";
                    DBi::rollback();
                    DBi::autocommit(true);
                    continue;
                }
                if (isset($fieldarr["archivopdf"])) {
                    $stats["hasPDF"]++;
                    if (rename($fullpathpdf, $filepath.$newpdfpath)) {
                        $stats["pdfRenamed"]++;
                    } else {
                        $stats["notRenamed"]++;
                        $stats["errors"][]="NOTRENAMED: $fullpathpdf => $newpdfpath";
                    }
                }
                $stats["updated"]++;
            } else {
                $stats["notUpdated"]++;
                $stats["errors"][]="NOTUPDATED: $query<br>".implode("<br>",$rvcObj->errors);
            }
            DBi::autocommit(true);
        } else $stats["fileNotFound"]++;
    }
    echo "VIATICOS__: viewed=$stats[view], noXMLFound=$stats[xmlNotFound], processed=$stats[processed], noFilenameFound=$stats[fileNotFound], mkdir=$stats[mkdir], noMkdir=$stats[noMkdir], updated=$stats[updated], notUpdated=$stats[notUpdated], renamedXML=$stats[xmlRenamed], hasPDF=$stats[hasPDF], renamedPDF=$stats[pdfRenamed], notRenamed=$stats[notRenamed], sameXML=$stats[sameXML]<br>";
    if (isset($stats["errors"])) {
        echo "<ul>";
        for ($i=0; isset($stats["errors"][$i]); $i++) {
            echo "<li>".$stats["errors"][$i]."</li>";
        }
        echo "</ul>";
    }
    if (!isset($rarObj)) {
        require_once "clases/ReposicionArchivos.php";
        $rarObj = new ReposicionArchivos();
    }
    $rarObj->rows_per_page=0;
    $rarData=$rarObj->getData("archivoxml is not null", 0, "id,repid,archivoxml,archivopdf");
    $stats=["view"=>0,"xmlNotFound"=>0,"processed"=>0,"fileNotFound"=>0,"mkdir"=>0,"noMkdir"=>0,"updated"=>0,"notUpdated"=>0,"hasPDF"=>0,"xmlRenamed"=>0,"pdfRenamed"=>0,"notRenamed"=>0,"sameXML"=>0,"errors"=>[]];
    foreach ($rarData as $rarRow) {
        $stats["view"]++;
        if (!isset($rarRow["archivoxml"][0])) {
            $stats["xmlNotFound"]++;
            continue;
        }
        $fullpathxml=$filepath.$rarRow["archivoxml"];
        $fullpathpdf=$filepath.$rarRow["archivopdf"];
        $xmlData=getCFDIData($fullpathxml, $rarRow["archivoxml"], false,"CC".$rarRow["repid"]);
        if (isset($xmlData["filename"][0])) {
            $stats["processed"]++;
            $dtpath=substr($xmlData["fecha"],0,4).substr($xmlData["fecha"],5,2)."/";
            $viatpath=$filepath.$dtpath;
            if (!file_exists($viatpath)) {
                if(mkdir($viatpath,0777,true)) $stats["mkdir"]++;
                else {
                    $stats["noMkdir"]++;
                    continue;
                }
            }
            $newxmlpath=$dtpath.$xmlData["filename"].".xml";
            $newpdfpath=$dtpath.$xmlData["filename"].".pdf";
            $fieldarr=["id"=>$rarRow["id"]];
            if ($rarRow["archivoxml"]===$newxmlpath) {
                $stats["sameXML"]++;
                continue;
            }
            $fieldarr["archivoxml"]=$newxmlpath;
            $fieldarr["originalxml"]=$rarRow["archivoxml"];
            if (isset($xmlData["uuid"][0])) $fieldarr["uuid"]=$xmlData["uuid"];
            if (isset($rarRow["archivopdf"][0])&&$rarRow["archivopdf"]!==$newpdfpath) $fieldarr["archivopdf"]=$newpdfpath;
            DBi::autocommit(false);
            if ($rarObj->updateRecord($fieldarr)) {
                if (rename($fullpathxml, $filepath.$newxmlpath)) {
                    $stats["xmlRenamed"]++;
                    DBi::commit();
                } else {
                    $stats["notRenamed"]++;
                    $stats["errors"][]="NOTRENAMED: $fullpathxml => $newxmlpath";
                    DBi::rollback();
                    DBi::autocommit(true);
                    continue;
                }
                if (isset($fieldarr["archivopdf"])) {
                    $stats["hasPDF"]++;
                    if (rename($fullpathpdf, $filepath.$newpdfpath)) {
                        $stats["pdfRenamed"]++;
                    } else {
                        $stats["notRenamed"]++;
                        $stats["errors"][]="NOTRENAMED: $fullpathpdf => $newpdfpath";
                    }
                }
                $stats["updated"]++;
            } else {
                $stats["notUpdated"]++;
                $stats["errors"][]="NOTUPDATED: $query<br>".implode("<br>",$rarObj->errors);
            }
            DBi::autocommit(true);
        } else $stats["fileNotFound"]++;
    }
    echo "CAJA CHICA: viewed=$stats[view], noXMLFound=$stats[xmlNotFound], processed=$stats[processed], noFilenameFound=$stats[fileNotFound], mkdir=$stats[mkdir], noMkdir=$stats[noMkdir], updated=$stats[updated], notUpdated=$stats[notUpdated], renamedXML=$stats[xmlRenamed], hasPDF=$stats[hasPDF], renamedPDF=$stats[pdfRenamed], notRenamed=$stats[notRenamed], sameXML=$stats[sameXML]<br>";
    if (isset($stats["errors"])) {
        echo "<ul>";
        for ($i=0; isset($stats["errors"][$i]); $i++) {
            echo "<li>".$stats["errors"][$i]."</li>";
        }
        echo "</ul>";
    }
}
function doTransferFiles() {
    $req_start_time=+$_SERVER["REQUEST_TIME_FLOAT"];
    $start_time=microtime(true);
    $ini_max_time=+ini_get('max_execution_time');
    $before_timeout=$ini_max_time-($start_time-$req_start_time);
    $process_time=0;
    doclog("TRANSFER FILES BEGAN HAVING {$before_timeout}s","ftp");
    global $rccObj,$rviObj,$rarObj,$rvcObj,$gpoObj,$prcObj;
    $tabla=strtolower($_POST["tipo"]??"");
    if (isset($tabla[0])&&$tabla[0]==="v") {
        $tipo="Viáticos";
        $abrv="VT";
        $tabla="reposicionviaticos";
        $tarch="repviaconceptos";
        if (!isset($rviObj)) {
            require_once "clases/ReposicionViaticos.php";
            $rviObj = new ReposicionViaticos();
        }
        $remObj=$rviObj;
        if (!isset($rvcObj)) {
            require_once "clases/RepViaConceptos.php";
            $rvcObj = new RepViaConceptos();
        }
        $rvcObj->rows_per_page=0;
        $arcObj=$rvcObj;
        $arcRegIdName="vid";
    } else if (isset($tabla[0])&&$tabla[0]==="c") {
        $tipo="Caja Chica";
        $abrv="CC";
        $tabla="reposicioncajachica";
        $tarch="reposicionarchivos";
        if (!isset($rccObj)) {
            require_once "clases/ReposicionCajaChica.php";
            $rccObj = new ReposicionCajaChica();
        }
        $remObj=$rccObj;
        if (!isset($rarObj)) {
            require_once "clases/ReposicionArchivos.php";
            $rarObj=new ReposicionArchivos();
        }
        $rarObj->rows_per_page=0;
        $arcObj=$rarObj;
        $arcRegIdName="repid";
    } else {
        echoJSDoc("error", "Falta indicar si se trata de reembolso de Viáticos o Caja Chica", null, $_POST, "cajachica");
        return;
    }
    $ids=$_POST["listaIds"]??"";
    if (!isset($ids[0])) { echoJSDoc("error", "Falta indicar algún folio de reembolso", null, $_POST, "cajachica"); return; }
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $idArr=explode(",", $ids);
    $lastBlock_time=$start_time;
    $lastLapse_time=$start_time;
    foreach ($idArr as $regIdx=>$regId) {
        DBi::autocommit(false);
        $remData=$remObj->getData("id=$regId", 1, "empresaId,solicitante");
        if (isset($remData[0]["empresaId"])) $remData=$remData[0];
        $empresaId=+($remData["empresaId"]??"0");
        if ($empresaId>0) {
            $gpoData=$gpoObj->getData("id=$empresaId", 1, "alias");
            $alias = $gpoData[0]["alias"]??"";
            if (isset($alias[0])) {
                $arcData=$arcObj->getData("$arcRegIdName=$regId and archivoxml is not null", 0, "id,archivoxml,archivopdf");
                try {
                    $filelist=[];
                    $llog=[];
                    $rNum=$regIdx+1;
                    foreach ($arcData as $arcIdx=>$arcRow) {
                        $tmpLog=[];
                        if ($alias==="CASABLANCA") $alias="LAMINADOS";
                        transferInvoice($alias,$arcRow["archivoxml"],$arcRow["archivopdf"],$tmpLog);
                        if (!$arcObj->saveRecord(["id"=>$arcRow["id"],"archivostatus"=>"respaldado"])) {
                            if (DBi::$errno>0) {
                                $errno=DBi::getErrno();
                                $error=DBi::getError();
                                DBi::rollback();
                                DBi::autocommit(true);
                                $block_time=microtime(true);
                                $before_timeout=$ini_max_time-($block_time-$req_start_time);
                                $process_time=$block_time-$lastBlock_time;
                                $lastLapse_time=$block_time;
                                $lastBlock_time=$block_time;
                                doclog("IGNORE TRANSFER BLOCK|Unable to save file data: $abrv {$rNum}[$arcIdx|$alias|$arcRow[archivoxml]|$archRow[archivopdf]] with ERROR '$errno': '$error' AFTER {$process_time}s","ftp");
                                continue 2;
                            } // else // El archivo ya estaba respaldado
                        }
                        if (isset($arcRow["archivoxml"][0])) $filelist[]=$arcRow["archivoxml"];
                        if (isset($arcRow["archivopdf"][0])) $filelist[]=$arcRow["archivopdf"];
                        if (isset($tmpLog[0])) array_push($llog, ...$tmpLog);
                        $lapse_time=microtime(true);
                        $before_timeout=$ini_max_time-($lapse_time-$req_start_time);
                        $process_time=$lapse_time-$lastLapse_time;
                        $lastLapse_time=$lapse_time;
                        doclog("TRANSFERRED $abrv {$rNum}.".($arcIdx+1)." $alias,$arcRow[archivoxml],$arcRow[archivopdf] AFTER {$process_time}s, HAVING {$before_timeout}s","ftp");
                    }
                    if (!$remObj->saveRecord(["id"=>$regId,"ultimoRespaldo"=>date("Y-m-d H:i:s")]) && DBi::$errno>0) {
                        $errno=DBi::getErrno();
                        $error=DBi::getError();
                        DBi::rollback();
                        DBi::autocommit(true);
                        $process_time=microtime(true)-$lastBlock_time;
                        doclog("END TRANSFER BLOCK $abrv {$rNum} with ERROR '$errno': '$error' AFTER {$process_time}s","ftp");
                        echoJSDoc("error", "Falló la actualización del reembolso", null, ["errno"=>$errno, "error"=>$error], "error");
                        return;
                    }
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj = new Proceso();
                    }
                    $prcObj->alta("CajaChica",$regId,"TransferFiles","Empresa:$alias, Id:$regId, Solicita:$remData[solicitante]");
                    echo json_encode(["result"=>"exito","log"=>$llog,"filelist"=>$filelist]);
                    $block_time=microtime(true);
                    $before_timeout=$ini_max_time-($block_time-$req_start_time);
                    $process_time=$block_time-$lastBlock_time;
                    $lastBlock_time=$block_time;
                    doclog("END TRANSFER BLOCK $abrv {$rNum} AFTER {$process_time}s, HAVING {$before_timeout}s","ftp");
                } catch (Exception $e) {
                    $process_time=microtime(true)-$lastBlock_time;
                    doclog("END TRANSFER BLOCK $abrv {$rNum} with ERROR ".$e->getMessage()." AFTER {$process_time}s","ftp");
                    DBi::rollback();
                    DBi::autocommit(true);
                    echoJSDoc("error", "Falló respaldo de archivos", null, ["error"=>getErrorData($e)], "error");
                    return;
                }
            } else {
                $process_time=microtime(true)-$lastBlock_time;
                doclog("END TRANSFER BLOCK $abrv with MISSING DATA:'Alias' (EmpId:$empresaId,Folio:$regId) AFTER {$process_time}s","ftp");
                DBi::rollback();
                DBi::autocommit(true);
                echoJSDoc("error", "No se reconoce la empresa en Reembolso", null, ["empresaId"=>$remData["empresaId"],"tipo"=>$tipo,"regId"=>$regId], "error");
                return;
            }
        } else {
            $process_time=microtime(true)-$lastBlock_time;
            doclog("END TRANSFER BLOCK $abrv with MISSING DATA:'EmpresaId' (Folio:$regId) AFTER {$process_time}s","ftp");
            DBi::rollback();
            DBi::autocommit(true);
            echoJSDoc("error", "Falta empresa en Reembolso", null, ["tipo"=>$tipo,"regId"=>$regId], "error");
            return;
        }
        DBi::commit();
        DBi::autocommit(true);
    }
}
function transferAllFiles() {
    global $query, $ftpObj, $ftp_servidor, $ftp_usuario, $ftp_clave, $ftp_supportPath, $ftp_policyPath, $cajachicaPath;
    echo "<H1>TRANSFER ALL FILES</H1>";
    if (!isset($ftpObj)) {
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceGlama();
        if ($ftpObj==null) {
            echo "<p>ERROR ON FTP creation: ".MIFTP::$lastException->getMessage()."</p>";
        }
    }
    $query="(select g.alias,c.archivoxml,c.archivopdf from repviaconceptos c inner join reposicionviaticos r on c.vid=r.id inner join grupo g on r.empresaId=g.id where c.archivoxml is not null order by c.archivoxml) union (select g.alias,a.archivoxml,a.archivopdf from reposicionarchivos a inner join reposicioncajachica r on a.repid=r.id inner join grupo g on r.empresaId=g.id where a.archivoxml is not null order by a.archivoxml)";
    $result = DBi::query($query) or trigger_error("SQL", E_USER_ERROR);
    if ($result) {
        $arrlst=[];
        while ($row = $result->fetch_assoc()) {
            $arrlst[]=$row;
        }
        $result->close();
        foreach ($arrlst as $idx => $row) {
            try {
                $llog=[];
                transferInvoice($row["alias"],$row["archivoxml"],$row["archivopdf"],$llog);
                foreach ($llog as $key => $value) {
                    echo "<p>$idx.$key - $value</p>";
                }
            } catch (Exception $ex) {
                if (!isset($row["archivopdf"][0])) $row["archivopdf"]="null";
                echo "<p>$idx: ERROR ON TRANSFER $row[alias], $row[archivoxml], $row[archivopdf]: ".$ex->getMessage()."</p>";
            }
        }
    }

}
function transferTestFile() {
    global $ftpObj, $ftp_servidor, $ftp_usuario, $ftp_clave, $ftp_supportPath, $ftp_policyPath, $cajachicaPath;
    if (!isset($ftpObj)) {
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceGlama();
        if ($ftpObj==null) {
            throw MIFTP::$lastException;
        }
    }
    $alias="TEST";
    $xmlPath="202001/CC_FNI970829JR9_1867992.xml";
    $pdfPath="202001/CC_FNI970829JR9_1867992.pdf";
    echo "<H1>TRANSFER ALL FILES</H1>";
    try {
        $llog=[];
        transferInvoice($alias,$xmlPath,$pdfPath,$llog);
        foreach ($llog as $key => $value) {
            echo "<p>$key. $value</p>";
        }
    } catch (Exception $ex) {
        echo "<p>ERROR ON TRANSFER $alias, $xmlPath, $pdfPath: ".$ex->getMessage()."</p>";
    }
}
function transferInvoice($alias, $xmlPath, $pdfPath, &$log) {
    global $ftpObj,$cajachicaPath;
    if (!isset($ftpObj)) {
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceGlama();
        if ($ftpObj==null) {
            throw MIFTP::$lastException;
        }
    }
    $ftpObj->chmkdir($ftpObj->ftpBackupPath.$alias."/");
    $ftpObj->chmkdir($ftpObj->ftpPolicyPath.$alias."/");
    if (isset($xmlPath[0])) {
        if (!file_exists($cajachicaPath.$xmlPath)) throw new Exception("El archivo XML $cajachicaPath$xmlPath no existe");
        $lastSlashIdx=strrpos($xmlPath, "/");
        if ($lastSlashIdx!==false) $xmlName=substr($xmlPath, $lastSlashIdx+1);
        else $xmlName=$xmlPath;
        transferFile(false,$ftpObj->ftpBackupPath.$alias."/PUBLICO/",$xmlName,$cajachicaPath.$xmlPath);
        if (isset($log)) $log[]="SUCCESSFULL TRANSFER ".$ftpObj->ftpBackupPath.$alias."/PUBLICO/".$xmlName;
        transferFile(false,$ftpObj->ftpPolicyPath.$alias."/PUBLICO/",$xmlName,$cajachicaPath.$xmlPath);
        if (isset($log)) $log[]="SUCCESSFULL TRANSFER ".$ftpObj->ftpPolicyPath.$alias."/PUBLICO/".$xmlName;
    } else throw new Exception("El archivo XML es requisito para transferir facturas");
    if (isset($pdfPath[0])) {
        if (!file_exists($cajachicaPath.$pdfPath)) throw new Exception("El archivo PDF $cajachicaPath$pdfPath no existe");
        $lastSlashIdx=strrpos($pdfPath, "/");
        if ($lastSlashIdx!==false) $pdfName=substr($pdfPath, $lastSlashIdx+1);
        else $pdfName=$pdfPath;
        transferFile(true,$ftpObj->ftpBackupPath.$alias."/PUBLICO/", $pdfName, $cajachicaPath.$pdfPath);
        if (isset($log)) $log[]="SUCCESSFULL TRANSFER ".$ftpObj->ftpBackupPath.$alias."/PUBLICO/".$pdfName;
        transferFile(true,$ftpObj->ftpPolicyPath.$alias."/PUBLICO/", $pdfName, $cajachicaPath.$pdfPath);
        if (isset($log)) $log[]="SUCCESSFULL TRANSFER ".$ftpObj->ftpPolicyPath.$alias."/PUBLICO/".$pdfName;
    }
}
function transferFile($isBinary, $remotePath, $remoteName, $localFilepath) {
    global $ftpObj, $prcObj;
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    if ($isBinary) {
        $ftpObj->cargarArchivoBinario($remotePath, $remoteName, $localFilepath);
    } else {
        $ftpObj->cargarArchivoAscii($remotePath, $remoteName, $localFilepath);
    }
    $prcObj->alta("CajaChica",0,"AvanceFTP",$remotePath.$remoteName);
}
function fixDate($dbdate) {
    if (isset($dbdate[9])) return substr($dbdate, 8, 2)."/".substr($dbdate, 5, 2)."/".substr($dbdate, 0, 4);
    return "";
}
function getNombreEmpresa($empresaId) {
    global $gpoObj;
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $gpoObj->rows_per_page=0;
    $gpoData=$gpoObj->getData("id=$empresaId", 1, "alias");
    if (isset($gpoData[0]["alias"])) return $gpoData[0]["alias"];
    return "DESCONOCIDO($empresaId)";
}
function getConceptosViaticos($id) {
    global $rvcObj;
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    $rvcObj->rows_per_page=0;
    $rvcData=$rvcObj->getData("vid='$id'");
    $retval=[];
    foreach ($rvcData as $val) {
        $cfecha=$val["fecha"];
        $cfecha=substr($cfecha,5,2).substr($cfecha,8,2);
        $cnombre=$val["concepto"];
        if (!isset($retval[$cfecha])) $retval[$cfecha]=[];
        if (!isset($retval[$cfecha][$cnombre])) $retval[$cfecha][$cnombre]=[];
        $retval[$cfecha][$cnombre][]=$val;
    }
    return $retval;
}
function getArchivosViaje($id) {
    global $rvcObj;
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    $rvcObj->rows_per_page=0;
    $rvcData=$rvcObj->getData("vid='$id'");
    return $rvcData;
}
function getArchivosCaja($id) {
    global $rarObj;
    if (!isset($rarObj)) {
        require_once "clases/ReposicionArchivos.php";
        $rarObj = new ReposicionArchivos();
    }
    $rarObj->rows_per_page=0;
    $rarData=$rarObj->getData("repid='$id'");
    return $rarData;
}
function doFixStatus() {
    $regId=$_POST["regId"]??"";
    $status=$_POST["status"]??"";
    switch($status) {
        case "pagado":
    /*
    if ($control==="pagado") {
        $fieldarr["pagadoPor"]=getUser()->persona;
        if (isset($_POST["control2"])&&$_POST["control2"]==="autorizar") {
            $fieldarr["autorizadoPor"]=getUser()->persona;
        }
    } else if ($control==="autorizar") {
        $fieldarr["autorizadoPor"]=getUser()->persona;
    } else if ($control==="rechazar") {
        $fieldarr["rechazadoPor"]=getUser()->persona;
    }
    if (!$rccObj->saveRecord($fieldarr)) {
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Error al cambiar registro de caja chica", null, ["errors"=>DBi::$errors], "error");
        return;
    }
    */
            break;
        case "aceptado":
            break;
        case "rechazado":
            break;
        case "pendiente":
            break;
    }
} // END doFixStatus
function doViewRecord() {
    echo "<div class=\"selector centered\"><B>VIEW RECORD</B></div>";
} // END doViewRecord
function doReview() {
    global $query, $esSistemas, $perObj, $ugObj;
    $parameters=[];
    $folio=$_POST["folio"]??"";
    if(isset($folio[0])) $parameters["folio"]=$folio;
    $tipofecha=$_POST["tipofecha"]??"solicitud";
    if ($tipofecha!=="pago") $tipofecha="solicitud";
    $parameters["tipofecha"]=$tipofecha;
    $tipo=$_POST["tipo"]??"";
    if (isset($tipo[0])) $parameters["tipo"]=$tipo;
    $status=strtolower($_POST["status"]??"");
    $sttMod=!isset($_POST["statusModifier"][0]);
    if (isset($status[0])) $parameters["status"]=($sttMod?"":"no ").$status;
    $beneficiario=$_POST["beneficiario"]??"";
    if (isset($beneficiario[0])) $parameters["beneficiario"]=$beneficiario;
    $logmsg="";
    $empresaId=$_POST["empresaId"]??"";
    if (isset($empresaId[0])) {
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $parameters["empresaId"]=$empresaId;
        $parameters["empresa"]=$gpoObj->getValue("id",$empresaId,"alias");
    }
    $unaEmpresaElegida=(isset($empresaId[0]) && $empresaId!=="todas");
    if (!isset($perObj)) {
        require_once "clases/Perfiles.php";
        $perObj=new Perfiles();
    }
    $ccId=$perObj->getIdByName("Caja Reporte");
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $ugObj->rows_per_page=0;
    $refundGroupId=$ugObj->getRefundGroupId(getUser()->id, $ccId, "vista", true);
    //if (!isset($refundGroupId[0])) { echoJSDoc("error", "No tiene empresas válidas", null, $_POST, false); return; }
    $tieneVariasEmpresasValidas=isset($refundGroupId[1]);
    $tieneUnaEmpresaValida=(!$tieneVariasEmpresasValidas&&isset($refundGroupId[0]));
    if (isset($_POST["fechaIni"])) $fechaIni=date("Y/m/d", strtotime(str_replace("/", "-", $_POST["fechaIni"])))." 00:00:00";
    if (isset($_POST["fechaFin"])) $fechaFin=date("Y/m/d", strtotime(str_replace("/", "-", $_POST["fechaFin"])))." 23:59:59";
    $parameters["fechaIni"]=$fechaIni??"";
    $parameters["fechaFin"]=$fechaFin??"";
    $columnas=[];
    switch ($tipo) {
        case "todos":
            $columnas=["folio","tipo","solicitud","pago","beneficiario","descripcion","total","status"];
            if (!$unaEmpresaElegida&&!$tieneUnaEmpresaValida) {
                array_splice($columnas, 4, 0, ["empresa"]);
            }
            $query1="SELECT id, id folio, 'VIATICOS' tipo, fechasolicitud, fechasolicitud solicitud, fechapago, fechapago pago, beneficiario, empresaId, lugaresvisita, '' concepto, concat('VISITA ',lugaresvisita) descripcion, banco, cuentabancaria, cuentaclabe, viaticosrequeridos, diferencialiquidar, montototal, 0 monto, viaticosrequeridos total, observaciones, solicitante, autorizadoPor autorizadopor, rechazadoPor rechazadopor, if(rechazadoPor IS NOT NULL, concat('RECHAZO ',rechazadoPor), if(pagadoPor IS NOT NULL,'PAGADO', if(ultimorespaldo IS NOT NULL,'RESPALDADO', if(autorizadoPor IS NOT NULL, concat('AUTORIZO ',autorizadoPor), 'PENDIENTE')))) status FROM reposicionviaticos";
            $query2="SELECT id, id folio, 'CAJACHICA' tipo, fechasolicitud, fechasolicitud solicitud, fechapago, fechapago pago, beneficiario, empresaId, '' lugaresvisita, concepto, concepto descripcion, banco, cuentabancaria, cuentaclabe, 0 viaticosrequeridos, 0 diferencialiquidar, 0 montototal, monto, monto total, observaciones, solicitante, autorizadopor, rechazadopor, if( rechazadoPor IS NOT NULL, concat('RECHAZO ',rechazadoPor), if(pagadoPor IS NOT NULL,'PAGADO', if(ultimorespaldo IS NOT NULL,'RESPALDADO', if(autorizadoPor IS NOT NULL, concat('AUTORIZO ',autorizadoPor), 'PENDIENTE')))) status FROM reposicioncajachica";
            break;
        case "viaticos":
            $columnas=["folio","solicitud","pago","beneficiario","visita","total","status"];
            if (!$unaEmpresaElegida&&!$tieneUnaEmpresaValida) {
                array_splice($columnas, 3, 0, ["empresa"]);
            }
            $query="SELECT *, id folio, fechasolicitud solicitud, fechapago pago, lugaresvisita visita, viaticosrequeridos total, if( rechazadoPor IS NOT NULL, concat('RECHAZO ',rechazadoPor), if(pagadoPor IS NOT NULL,'PAGADO', if(ultimorespaldo IS NOT NULL,'RESPALDADO', if( autorizadoPor IS NOT NULL, concat('AUTORIZO ',autorizadoPor), 'PENDIENTE')))) status FROM reposicionviaticos";
            break;
        case "cajachica":
            $columnas=["folio","solicitud","pago","beneficiario","concepto","total","status"];
            if (!$unaEmpresaElegida&&!$tieneUnaEmpresaValida) {
                array_splice($columnas, 3, 0, ["empresa"]);
            }
            $query="SELECT *, id folio, fechasolicitud solicitud, fechapago pago, monto total,if( rechazadoPor IS NOT NULL, concat('RECHAZO ',rechazadoPor), if(pagadoPor IS NOT NULL,'PAGADO', if(ultimorespaldo IS NOT NULL,'RESPALDADO', if( autorizadoPor IS NOT NULL, concat('AUTORIZO ',autorizadoPor), 'PENDIENTE')))) status FROM reposicioncajachica";
            break;
        default: { echoJSDoc("error", "Solicitud de reporte no reconocida", null, $_POST, false); return; }
    }
    $where="";
    if (isset($folio[0])) {
        $where.=(isset($where[0])?" AND ":" WHERE ")."id in ($folio)";
    }
    switch($status) {
        case "pagado": $where.=(isset($where[0])?" AND ":" WHERE ")."pagadoPor IS ".($sttMod?"NOT":"")." NULL"; break;
        case "aceptado": $where.=(isset($where[0])?" AND ":" WHERE ")."autorizadoPor IS ".($sttMod?"NOT NULL AND ultimorespaldo IS NULL":"NULL"); break;
        case "rechazado": $where.=(isset($where[0])?" AND ":" WHERE ")."rechazadoPor IS ".($sttMod?"NOT NULL":"NULL"); break;
        case "pendiente": $op=($sttMod?"IS":"IS NOT"); $jn=($sttMod?"AND":"OR");
            $where.=(isset($where[0])?" AND ":" WHERE ")."(autorizadoPor $op NULL $jn rechazadoPor $op NULL $jn pagadoPor $op NULL)"; break;
        case "sinrespaldo": $op=($sttMod?"IS":"IS NOT"); $where.=(isset($where[0])?" AND ":" WHERE ")."autorizadoPor IS NOT NULL AND rechazadoPor IS NULL AND pagadoPor IS NULL AND ultimoRespaldo $op NULL"; break;
        case "respaldado": $op=($sttMod?"IS NOT":"IS"); $where.=(isset($where[0])?" AND ":" WHERE ")."autorizadoPor IS NOT NULL AND rechazadoPor IS NULL AND pagadoPor IS NULL AND ultimoRespaldo $op NULL"; break;
        case "respaldohoy": $op=($sttMod?">":"<"); $fechaHoy=date("Y-m-d")." 00:00:00"; $where.=(isset($where[0])?" AND ":" WHERE ")."autorizadoPor IS NOT NULL AND rechazadoPor IS NULL AND pagadoPor IS NULL AND ultimoRespaldo IS NOT NULL AND ultimoRespaldo $op '$fechaHoy'"; if ($sttMod) {$fechaIni="";$fechaFin="";} break;
        case "todos": if (!$sttMod&&!isset($folio[0])) $where.=(isset($where[0])?" AND ":" WHERE ")."id<0";
    }
    if (isset($beneficiario[0])) {
        $lowName=str_replace(" ", "", strtolower($beneficiario));
        $where.=(isset($where[0])?" AND ":" WHERE ")."lower(replace(beneficiario,' ','')) like '%{$lowName}%'";
    }
    if ($unaEmpresaElegida) {
        $where.=(isset($where[0])?" AND ":" WHERE ")."empresaId='$empresaId'";
    } else if ($tieneVariasEmpresasValidas) {
        $where.=(isset($where[0])?" AND ":" WHERE ")."empresaId in (".implode(",",$refundGroupId).")";
    } else if ($tieneUnaEmpresaValida) {
        $where.=(isset($where[0])?" AND ":" WHERE ")."empresaId=".$refundGroupId[0];
    }
    if (!isset($folio[0])&&isset($fechaIni[0])&&isset($fechaFin[0])) {
        $where.=(isset($where[0])?" AND ":" WHERE ")."fecha{$tipofecha} BETWEEN '$fechaIni' AND '$fechaFin'";
    }
    if (isset($query1)&&isset($query2)) {
        $query1.=$where;
        $query2.=$where;
        $query=$query1." UNION ".$query2;
    } else {
        $query.=$where;
    }
    $sortby=" order by ".($tieneVariasEmpresasValidas||$esSistemas?"empresaId,":"")."fechasolicitud;";
    $query.=$sortby;
    $parameters["query"]=$query;
    $firstQuery=$query;
    $ccrResult = DBi::query($query);
    if (!empty(DBi::$error)) {
        if ($ccrResult) $ccrResult->close();
        echoJSDoc("error", "Ocurrió un error durante la consulta", null, $parameters+["errno"=>DBi::$errno,"error"=>DBi::$error], "error");
        return;
    }
    $data=[];
    $sumTotal=0.0;
    $conceptosMap=["CAJA CHICA SIN FACTURA"=>"CAJA CHICA SIN FACTURA","CAJA CHICA CON FACTURA"=>"CAJA CHICA CON FACTURA"];
    $testing=false;
    $areaDetalleClass=" class=\"";
    if (isset($_REQUEST["area_detalle"][0]))
        $areaDetalleClass.=$_REQUEST["area_detalle"];
    else
        $areaDetalleClass.="scroll-60";
    $areaDetalleClass.=" relative\"";
    if (is_object($ccrResult)) {
        $hasRecord=true;
        $esSolicitante=(getUser()->nombre==="viajero");
        $esNuevoSolicitante=false;
        $groupOptions = "";
        $grupoOptionMap = [];
        $esViaticos=($tipo==="viaticos");
        $esCajaChica=($tipo==="cajachica");
        $esTodos=(!$esViaticos&&!$esCajaChica);
        $puedeAutorizarViaticos=$esSistemas||validaPerfil("Autoriza Viaticos");
        $puedeAutorizarCajaChica=$esSistemas||validaPerfil("Autoriza Caja Chica");
        $puedePagarViaticos = $esSistemas||validaPerfil("Paga Viaticos");
        $puedePagarCajaChica = $esSistemas||validaPerfil("Paga Caja Chica");
        while ($ccrRow = $ccrResult->fetch_assoc()) {
            $sumTotal+=+$ccrRow["total"];
            if ($esTodos) {
                $esViaticos=(isset($ccrRow["tipo"])&&$ccrRow["tipo"]==="VIATICOS");
                $esCajaChica=(isset($ccrRow["tipo"])&&$ccrRow["tipo"]==="CAJACHICA");
            }
            if ($esViaticos||$esCajaChica) {
                $registro=(object)$ccrRow;
                if (isset($registro->pagadopor[0])) $registro->pagadoPor=$registro->pagadopor;
                if (isset($registro->autorizadopor[0])) $registro->autorizadoPor=$registro->autorizadopor;
                if (isset($registro->rechazadopor[0])) $registro->rechazadoPor=$registro->rechazadopor;
                if (isset($registro->pagadoPor[0])) {
                    $statusControl="<B>PAGADO</B>";
                } else if (isset($registro->ultimorespaldo[0])) {
                    $statusControl="<B>RESPALDADO</B>";
                } else if (isset($registro->autorizadoPor[0])) {
                    $statusControl="<B>$registro->autorizadoPor</B>";
                    /*
                    if (($esViaticos&&$puedePagarViaticos)||($esCajaChica&&$puedePagarCajaChica))
                        $statusControl.=" <INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" class=\"noprint\" onclick=\"paidRecord(event);\" auth=\"1\">";
                        */
                } else if (isset($registro->rechazadoPor[0])) {
                    $statusControl="<B>$registro->rechazadoPor</B>";
                    /*
                    if (($esViaticos&&$puedePagarViaticos)||($esCajaChica&&$puedePagarCajaChica))
                        $statusControl.=" <INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" class=\"noprint\" onclick=\"paidRecord(event);\" auth=\"0\">";
                        */
                } else {
                    $statusControl="<B>PENDIENTE</B>";
                    /*
                    if (($esViaticos&&$puedeAutorizarViaticos)||($esCajaChica&&$puedeAutorizarCajaChica)) {
                        $statusControl.=" <INPUT type=\"button\" id=\"acceptButton\" value=\"AUTORIZAR\" class=\"noprint\" onclick=\"acceptRecord(event);\"> <INPUT type=\"button\" id=\"rejectButton\" value=\"RECHAZAR\" class=\"noprint\" onclick=\"rejectRecord(event);\">";
                    }
                    if (($esViaticos&&$puedePagarViaticos)||($esCajaChica&&$puedePagarCajaChica))
                        $statusControl.=" <INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" class=\"noprint\" onclick=\"paidRecord(event);\" auth=\"1\">";
                        */
                }
                $registro->empresa=getNombreEmpresa($registro->empresaId);
                $ccrRow["empresa"]=$registro->empresa;
                if ($esViaticos) {
                    $registro->conceptos=getConceptosViaticos($registro->id);
                    $archData=getArchivosViaje($registro->id);
                } else {
                    $registro->archivos=getArchivosCaja($registro->id);
                    $archData=$registro->archivos; //$ccrRow["archivos"]=getArchivosCaja($registro->id);
                }
                if (isset($archData[0])) {
                    $ccrRow["archivos"]=[];
                    foreach ($archData as $archRow) {
                        $ccrRow["archivos"][]=[$archRow["id"],$archRow["archivoxml"],$archRow["archivopdf"],$archRow["totalfactura"]??$archRow["importe"]??"0",$archRow["tipocomprobante"]??"i"];
                    }
                }
                ob_start();
                include "templates/".($esViaticos?"viajero":"cajachica").".php";
                $ccrRow["html"] = ob_get_clean();
                //$ccrRow["tipo"]
            }
            $registro=null;
            $fixedRow=[];
            foreach ($columnas as $campo) {
                $fixedRow[$campo]=$ccrRow[$campo];
            }
            if (isset($ccrRow["html"])) $fixedRow["html"]=$ccrRow["html"];
            if (isset($ccrRow["archivos"])) $fixedRow["archivos"]=$ccrRow["archivos"];
            $data[]=$fixedRow;
        }
    }
    if (!isset($data[0])) {
        if ($ccrResult) $ccrResult->close();
        echoJSDoc("error", "No se encontraron registros con el criterio indicado", null, $parameters, false);
        return;
    }
    if ($ccrResult) $ccrResult->close();
    echo json_encode(["result"=>"exito","mensaje"=>"Carga de archivos exitosa","columnas"=>$columnas,"datos"=>$data,"sumTotal"=>$sumTotal,"query"=>$firstQuery,"parameters"=>$parameters,"log"=>$logmsg]);
} // END doReview
function trim_value(&$value) {
    $value = trim($value);
}
function doNewPettyCash() {
    global $rccObj,$tmpObj,$prcObj,$gpoObj;
    array_walk($_POST, 'trim_value');
    $beneficiario=$_POST["beneficiario"]??"";
    if (!isset($beneficiario[0])) { echoJSDoc("error", "Debe ingresar el nombre del beneficiario", null, $_POST, false); return; }
    $empresaId=$_POST["empresaId"]??"";
    if (!isset($empresaId[0])) { echoJSDoc("error", "Debe indicar una empresa", null, $_POST, false); return; }
    if (!isset($rccObj)) {
        require_once "clases/ReposicionCajaChica.php";
        $rccObj = new ReposicionCajaChica();
    }
    $concepto=$_POST["concepto"]??"";
    if (!isset($concepto[0])) { echoJSDoc("error", "Debe ingresar un concepto", null, $_POST, false); return; }
    $banco=$_POST["banco"]??"";
    $cuentabancaria=str_replace(" ", "", $_POST["cuentabancaria"]??"");
    if (isset($cuentabancaria[20])) $cuentabancaria=substr($cuentabancaria, 0, 20);
    $cuentaclabe=str_replace(" ", "", $_POST["cuentaclabe"]??"");
    if (isset($cuentaclabe[20])) $cuentaclabe=substr($cuentaclabe, 0, 20);
    $monto=+($_POST["monto"]??"0");
    if ($monto<=0) { echoJSDoc("error", "Debe indicar el monto requerido", null, $_POST, false); return; }
    $observaciones=$_POST["observaciones"]??"";
    DBi::autocommit(false);
    $solicitante = getUser()->persona;
    $fechaAhora=date("Y-m-d H:i:s");
    $fechaHoy=date("Y-m-d");
    $fieldarr=["fechasolicitud"=>$fechaAhora, "fechapago"=>$fechaHoy, "beneficiario"=>$beneficiario,"concepto"=>$concepto,"monto"=>$monto,"solicitante"=>$solicitante];
    if (isset($empresaId[0])) $fieldarr["empresaId"]=$empresaId;
    if (isset($banco[0])) $fieldarr["banco"]=$banco;
    if (isset($cuentabancaria[0])) $fieldarr["cuentabancaria"]=$cuentabancaria;
    if (isset($cuentaclabe[0])) $fieldarr["cuentaclabe"]=$cuentaclabe;
    if (isset($observaciones[0])) $fieldarr["observaciones"]=$observaciones;
    if (!$rccObj->saveRecord($fieldarr)) {
        DBi::rollback();
        DBi::autocommit(true);
        global $query;
        echoJSDoc("error", "Error al crear nuevo registro de reposición de caja chica", null, ["query"=>$query,"errno"=>DBi::$errno,"error"=>DBi::$error], "error");
        return;
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj = new Grupo();
    }
    $alias = $gpoObj->getAliasById($empresaId);
    if (!$alias) $alias="";
    $regId=$rccObj->lastId;
    $_POST["regId"]="$regId";
    $prcObj->alta("CajaChica",$regId,"NewPettyCash","Empresa:$alias, Solicita:$solicitante");
    DBi::commit();
    DBi::autocommit(true);
    doGetPettyCash();
}
function doGetPettyCash($additionalData=null) {
    global $query,$rccObj, $rarObj, $gpoObj, $perObj, $ugObj;
    $regId=$_POST["regId"]??"";
    $beneficiario=$_POST["beneficiario"]??"";
    if (!isset($regId[0])&&!isset($beneficiario[0])) { echoJSDoc("error", "Se necesita el número de registro o nombre del beneficiario", null, $_POST, false); return; }
    if (!isset($rccObj)) {
        require_once "clases/ReposicionCajaChica.php";
        $rccObj = new ReposicionCajaChica();
    }
    $where="";
    if (isset($regId[0])) $where="id='$regId'";
    else {
        $lowName=str_replace(" ", "", strtolower($beneficiario));
        $where="lower(replace(beneficiario,' ','')) like '%{$lowName}%'";
    }
    if (!isset($perObj)) {
        require_once "clases/Perfiles.php";
        $perObj=new Perfiles();
    }
    $ccId=$perObj->getIdByName("Caja Chica");
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $ugObj->rows_per_page=0;
    $refundGroupId=$ugObj->getRefundGroupId(getUser()->id, $ccId, "vista", true);
    if (isset($refundGroupId[1])) $where.=" and empresaId in (".implode(",",$refundGroupId).")";
    else if (isset($refundGroupId[0])) $where.=" and empresaId=".$refundGroupId[0];

    $rccData=$rccObj->getData($where);
    if (!isset($rccData[0])) { echoJSDoc("error", "No se encontró el registro solicitado", null, ["query"=>$query], false); return; }
    if (!isset($rarObj)) {
        require_once "clases/ReposicionArchivos.php";
        $rarObj = new ReposicionArchivos();
    }
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj = new Grupo();
    }
    for ($i=0; isset($rccData[$i]); $i++) {
        $iid=$rccData[$i]["id"];
        $rarData=$rarObj->getData("repid='$iid'");
        if (isset($rarData[0]))
            $rccData[$i]["archivos"]=$rarData;
        $empId=$rccData[$i]["empresaId"];
        $gpoData=$gpoObj->getData("id='$empId'", 1, "alias");
        if (isset($gpoData[0]))
            $rccData[$i]["empresa"]=$gpoData[0]["alias"];
    }
    $result=["result"=>"exito", "message"=>"Carga de archivos exitosa", "datos"=>$rccData];
    if (isset($additionalData)) $result+=$additionalData;
    echo json_encode($result);
}
function doSavePettyCash() {
    $req_start_time=+$_SERVER["REQUEST_TIME_FLOAT"];
    $start_time=microtime(true);
    $ini_max_time=+ini_get('max_execution_time');
    $before_timeout=$ini_max_time-($start_time-$req_start_time);
    $process_time=0;
    global $rccObj, $tmpObj, $gpoObj, $rarObj, $prcObj, $query;
    array_walk($_POST, 'trim_value');
    $regId=$_POST["regId"]??"";
    if (!isset($regId[0])) { echoJSDoc("error", "No se recibió registro a guardar", null, _POST+["action"=>"SavePettyCash"], false); return; }
    $fieldarr=["id"=>$regId];
    if (isset($_POST["beneficiario"])) {
        $beneficiario=$_POST["beneficiario"];
        if (!isset($beneficiario[0])) { echoJSDoc("error", "Debe indicar un beneficiario", null, $_POST+["action"=>"SavePettyCash"], false); return; }
        $fieldarr["beneficiario"]=$beneficiario;
    }
    if (isset($_POST["empresaId"])) {
        $empresaId=$_POST["empresaId"];
        if (!isset($empresaId[0])) { echoJSDoc("error", "Debe indicar una empresa válida", null, $_POST+["action"=>"SavePettyCash"], false); return; }
        $fieldarr["empresaId"]=$empresaId;
    }
    if (isset($_POST["fechapago"])) {
        $fechapago=$_POST["fechapago"];
        if (!isset($fechapago[0])) { echoJSDoc("error", "Debe indicar una fecha de pago", null, $_POST+["action"=>"SavePettyCash"], false); return; }
        $fieldarr["fechapago"]=date("Y-m-d", strtotime(str_replace("/", "-", $fechapago)));
    }
    if (isset($_POST["concepto"])) {
        $concepto=$_POST["concepto"]??"";
        if (!isset($concepto[0])) { echoJSDoc("error", "Debe indicar un concepto válido", null, $_POST+["action"=>"SavePettyCash"], false); return; }
        $fieldarr["concepto"]=$concepto;
    }
    if (isset($_POST["banco"])) {
        $banco=$_POST["banco"];
        if (isset($banco[0]))
            $fieldarr["banco"]=$banco;
        else
            $fieldarr["banco"]=NULL;
    }
    if (isset($_POST["cuentabancaria"])) {
        $cuentabancaria=str_replace(" ", "", $_POST["cuentabancaria"]);
        if (isset($cuentabancaria[20])) $cuentabancaria=substr($cuentabancaria, 0, 20);
    }
    if (isset($_POST["cuentaclabe"])) {
        $cuentaclabe=str_replace(" ", "", $_POST["cuentaclabe"]);
        if (isset($cuentaclabe[20])) $cuentaclabe=substr($cuentaclabe, 0, 20);
    }
    if (isset($cuentabancaria)) {
        if (isset($cuentabancaria[0]))
            $fieldarr["cuentabancaria"]=$cuentabancaria;
        else
            $fieldarr["cuentabancaria"]=NULL;
    }
    if (isset($cuentaclabe)) {
        if (isset($cuentaclabe[0]))
            $fieldarr["cuentaclabe"]=$cuentaclabe;
        else
            $fieldarr["cuentaclabe"]=NULL;
    }
    if (isset($_POST["monto"])) {
        $monto=+($_POST["monto"]??"0");
        if ($monto<=0) { echoJSDoc("error", "Se requiere un monto válido", null, $_POST+["action"=>"SavePettyCash"], "error"); return; }
        $fieldarr["monto"]=$monto;
    }
    if (isset($_POST["observaciones"])) {
        $observaciones=$_POST["observaciones"];
        if (isset($observaciones[0])) $fieldarr["observaciones"]=$observaciones;
        else $fieldarr["observaciones"]=NULL;
    }
    $docspath="C:/InvoiceCheckShare/invoiceDocs/";
    $temppath=$docspath."temporal/";
    $fechasolicitud=$_POST["fechasolicitud"]??"";
    if (isset($fechasolicitud[0])) {
        $dtpath=date("Ym",strtotime(str_replace("/", "-", $fechasolicitud)))."/";
    } else $dtpath=date("Ym")."/";
    $viatpath=$docspath."viajes/".$dtpath;
    if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
    if (!isset($rccObj)) {
        require_once "clases/ReposicionCajaChica.php";
        $rccObj = new ReposicionCajaChica();
    }
    DBi::autocommit(false);
    if (isset($_POST["xmlId"][0])) {
        $xmlId=$_POST["xmlId"];
        if (!isset($tmpObj)) {
            require_once "clases/Temporales.php";
            $tmpObj = new Temporales();
        }
        $xmlName=$tmpObj->procesar($xmlId);
        $fullTmpXML=$temppath.$xmlName;
        if ($xmlName!==false && file_exists($fullTmpXML)) {
            $xmlData=getCFDIData($fullTmpXML, $xmlName,true,"CC".$regId, $concepto??null);
            if (isset($xmlData["filename"][0])) {
                $xmlName=$xmlData["filename"].".xml";
                $fechafactura=new DateTime($xmlData["fecha"]);
                $dtpath=$fechafactura->format("Ym")."/";
                $viatpath=$docspath."viajes/".$dtpath;
                if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
            }
            rename($fullTmpXML, $viatpath.$xmlName);
            $fieldarr["archivoxml"]=$dtpath.$xmlName;
            if (isset($xmlData["uuid"][0])) $fieldarr["uuid"]=$xmlData["uuid"];
        }
    } else if (isset($_POST["xmlPath"]) && !isset($_POST["xmlPath"][0])) { // SOLO PARA BORRAR ARCHIVOS
            $fieldarr["archivoxml"]=NULL;
    }
    if (isset($_POST["pdfId"][0])) {
        $pdfId=$_POST["pdfId"];
        if (!isset($tmpObj)) {
            require_once "clases/Temporales.php";
            $tmpObj=new Temporales();
        }
        $pdfName=$tmpObj->procesar($pdfId);
        $fullTmpPDF=$temppath.$pdfName;
        if ($pdfName!==false && file_exists($fullTmpPDF)) {
            if (isset($xmlData["filename"][0])) {
                $pdfName=$xmlData["filename"].".pdf";
            }
            rename($fullTmpPDF, $viatpath.$pdfName);
            $fieldarr["archivopdf"]=$dtpath.$pdfName;
        }
    } else if (isset($_POST["pdfPath"]) && !isset($_POST["pdfPath"][0])) {
        $fieldarr["archivopdf"]=NULL;
    }
    $control=$_POST["control"]??"";
    if ($control==="pagado") {
        $fieldarr["pagadoPor"]=getUser()->persona;
        if (isset($_POST["control2"])&&$_POST["control2"]==="autorizar") {
            $fieldarr["autorizadoPor"]=getUser()->persona;
        }
    } else if ($control==="autorizar") {
        // validar que todos los archivos tengan XML
        if (!isset($rarObj)) {
            require_once "clases/ReposicionArchivos.php";
            $rarObj=new ReposicionArchivos();
        }
        if (!$rarObj->exists("repid=$regId")) { echoJSDoc("error", "No puede autorizar registros sin comprobantes", null, ["action"=>"SavePettyCash", "query"=>$query], "error"); return; }
        $rarValue=+$rarObj->getValue("repid",$regId,"count(1)","archivoxml IS NULL");
        if ($rarValue!==0) { echoJSDoc("error", "Para autorizar el registro, todos los comprobantes deben incluir XML", null, ["action"=>"SavePettyCash", "query"=>$query], "error"); return; }
        /*else {
            global $esAdmin;
            if ($esAdmin) { echoJSDoc("error", "Registro valido para autorizar. $rarValue conceptos sin XML", null, null, false); return; }
        }*/
        $fieldarr["autorizadoPor"]=getUser()->persona;
    } else if ($control==="rechazar") {
        $fieldarr["rechazadoPor"]=getUser()->persona;
    } else if ($control==="pendiente") {
        $fieldarr["autorizadoPor"]=NULL;
        $fieldarr["rechazadoPor"]=NULL;
    }
    global $query;
    if (!$rccObj->saveRecord($fieldarr)&&DBi::$errno>0) {
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Error al guardar registro de caja chica", null, ["action"=>"SavePettyCash", "errno"=>DBi::$errno, "error"=>DBi::$error, "query"=>$query], "error");
        return;
    }
    $rccData=$rccObj->getData("id=$regId", 1, "empresaId,solicitante");
    if (isset($rccData[0]["empresaId"])) {
        $rccData=$rccData[0];
        $empresaId=$rccData["empresaId"];
    } else {
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "No se encontró empresa relacionada con Reembolso de Caja Chica", null, ["action"=>"SavePettyCash", "folio"=>$regId, "errno"=>DBi::$errno, "error"=>DBi::$error,"query"=>$query], "error");
        return;
    }
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $gpoData=$gpoObj->getData("id=$empresaId", 1, "alias");
    $alias=$gpoData[0]["alias"]??"";
    if (!isset($alias[0])) {
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "No se encontró empresa relacionada con Reembolso de Caja Chica", null, ["action"=>"SavePettyCash", "regId"=>$regId, "control"=>$control, "errno"=>DBi::$errno, "error"=>DBi::$error,"query"=>$query], "error");
        return;
    }

    if ($control==="autorizar") {
        if (!isset($rarObj)) {
            require_once "clases/ReposicionArchivos.php";
            $rarObj=new ReposicionArchivos();
        }
        $rarData=$rarObj->getData("repid=$regId and archivoxml is not null", 0, "archivoxml,archivopdf");
        try {
            $llog=[];
            $block_time=microtime(true);
            $before_timeout=$ini_max_time-($block_time-$req_start_time);
            $process_time=$block_time-$start_time;
            doclog("BEGIN TRANSFER CC AFTER {$process_time}s, HAVING {$before_timeout}s", "ftp");
            $lapse_time=$block_time;
            foreach ($rarData as $rarIdx=>$rarRow) {
                transferInvoice($alias,$rarRow["archivoxml"], $rarRow["archivopdf"], $llog);
                $lapse_duration=microtime(true)-$lapse_time;
                $lapse_time=microtime(true);
                $before_timeout=$ini_max_time-($lapse_time-$req_start_time);
                $process_time=$lapse_time-$start_time;
                $block_time=$lapse_time-$block_time;
                doclog("TRANSFERRED CC ".($rarIdx+1)." $alias, $rarRow[archivoxml], $rarRow[archivopdf] AFTER {$process_time}s, HAVING {$before_timeout}s", "ftp");
            }
        } catch (Exception $e) {
            DBi::rollback();
            DBi::autocommit(true);
            echoJSDoc("error", "Error al respaldar archivos de factura, intente autorizar mas tarde", null, ["error"=>getErrorData($e)], "error");
            return;
        }
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    $prcObj->alta("CajaChica", $regId, "SavePettyCash", "Empresa:$alias, Id:$regId, Solicita:$rccData[solicitante]");
    DBi::commit();
    DBi::autocommit(true);
    doGetPettyCash();
}
function doDeletePettyCashFile() {
    global $rccObj, $rarObj, $prcObj;
    $regId=$_POST["regId"]??"";
    if (!isset($regId[0])) { echoJSDoc("error", "No se recibió registro a borrar", null, $_POST, false); return; }
    $fileId=$_POST["fileId"]??"";
    if (!isset($fileId[0])) { echoJSDoc("error", "No se recibieron los datos del archivo a borrar", null, $_POST, false); return; }
    if (!isset($rarObj)) {
        require_once "clases/ReposicionArchivos.php";
        $rarObj = new ReposicionArchivos();
    }
    global $query;
    if (!$rarObj->deleteRecord(["id"=>$fileId, "repid"=>$regId])) {
        echoJSDoc("error", "Ocurrió un error al eliminar archivo(s)", null, ["errno"=>DBi::$errno, "error"=>DBi::$error, "query"=>$query], "error");
        return;
    }
    if (!isset($rccObj)) {
        require_once "clases/ReposicionCajaChica.php";
        $rccObj = new ReposicionCajaChica();
    }
    $rccData=$rccObj->getData("id=$regId", 1, "empresaId, solicitante");
    if (isset($rccData[0]["empresaId"])) {
        $rccData=$rccData[0];
        $empresaId=$rccData["empresaId"];
    } else {
        echoJSDoc("error", "No se encontró empresa relacionada con Reembolso de Caja Chica", null, ["action"=>"DeletePettyCashFile", "folio"=>$regId, "errno"=>DBi::$errno, "error"=>DBi::$error, "query"=>$query], "error");
        return;
    }
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $gpoData=$gpoObj->getData("id=$empresaId", 1, "alias");
    $alias=$gpoData[0]["alias"]??"";
    if (!isset($alias[0])) {
        echoJSDoc("error", "No se encontró empresa relacionada con Reembolso de Caja Chica", null, $_POST+["action"=>"DeletePettyCashFile", " errno"=>DBi::$errno, "error"=>DBi::$error, "query"=>$query], "error");
        return;
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    $prcObj->alta("CajaChica", $regId, "DeletePettyCashFile", "Empresa:$alias, Id:$regId, Solicita:$rccData[solicitante]");
    doGetPettyCash();
}
function doDeletePettyCashReq() {
    global $rccObj, $rarObj, $query;
    $regId=$_POST["regId"]??"";
    if (!isset($regId[0])) { echoJSDoc("error", "No se recibió registro a borrar", null, $_POST+["action"=>"DeletePettyCashReq"], false); return; }
    if (!isset($rarObj)) {
        require_once "clases/ReposicionArchivos.php";
        $rarObj = new ReposicionArchivos();
    }
    DBi::autocommit(false);
    if ($rarObj->exists("repid=$regId") && !$rarObj->deleteRecord(["repid"=>$regId])) {
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Ocurrió un error al eliminar los archivos del registro", null, ["action"=>"DeletePettyCashReq", "errno"=>DBi::$errno, "error"=>DBi::$error,"query"=>$query], "error");
        return;
    }
    if (!isset($rccObj)) {
        require_once "clases/ReposicionCajaChica.php";
        $rccObj = new ReposicionCajaChica();
    }
    if (!$rccObj->deleteRecord(["id"=>$regId])) {
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Ocurrió un error al eliminar el registro", null, ["action"=>"DeletePettyCashReq", "errno"=>DBi::$errno, "error"=>DBi::$error, "query"=>$query], "error");
        return;
    }
    DBi::commit();
    DBi::autocommit(true);
    $result=["result"=>"exito", "message"=>"Registro eliminado satisfactoriamente"];
    echo json_encode($result);
}
function doNewRecord() {
    global $user, $username, $_now, $rviObj, $query;
    array_walk($_POST, 'trim_value');
    $beneficiario=$_POST["beneficiario"]??"";
    $lugares=$_POST["lugares"]??"";
    $viaticos=+($_POST["reqviaticos"]??"0");
    $empresaId=+($_POST["empresaId"]??0);
    $banco=$_POST["banco"]??"";
    $cuentabancaria=str_replace(" ", "", $_POST["cuentabancaria"]??"");
    if (isset($cuentabancaria[20])) $cuentabancaria=substr($cuentabancaria, 0, 20);
    $cuentaclabe=str_replace(" ", "", $_POST["cuentaclabe"]??"");
    if (isset($cuentaclabe[20])) $cuentaclabe=substr($cuentaclabe, 0, 20);
    $observaciones=$_POST["observaciones"]??"";
    if (!isset($beneficiario[0])) { echoJSDoc("error", "Debe ingresar el nombre del beneficiario", null, $_POST+["action"=>"newRecord"], false); return; }
    if ($empresaId<=0) { echoJSDoc("error", "Se requiere que indique la empresa que pagará sus viáticos", null, $_POST+["action"=>"newRecord"], false); return; }
    if (!isset($lugares[0])) { echoJSDoc("error", "Se requiere el o los lugares a visitar", null, $_POST+["action"=>"newRecord"], false); return; }
    if ($viaticos<=0) { echoJSDoc("error", "Debe indicar el monto total de viáticos requeridos", null, $_POST+["action"=>"newRecord"], false); return; }
    if (!isset($rviObj)) {
        require_once "clases/ReposicionViaticos.php";
        $rviObj = new ReposicionViaticos();
    }
    $solicitante = ($username==="viajero")?$beneficiario:$user->persona;
    $fechaHoy=$_now["now"];
    $fieldarr=["fechasolicitud"=>$fechaHoy, "fechapago"=>$fechaHoy, "beneficiario"=>$beneficiario, "empresaId"=>$empresaId, "lugaresvisita"=>$lugares, "viaticosrequeridos"=>$viaticos, "solicitante"=>$solicitante];
    if (isset($banco[0])) $fieldarr["banco"]=$banco;
    if (isset($cuentabancaria[0])) $fieldarr["cuentabancaria"]=$cuentabancaria;
    if (isset($cuentaclabe[0])) $fieldarr["cuentaclabe"]=$cuentaclabe;
    if (isset($observaciones[0])) $fieldarr["observaciones"]=$observaciones;
    if (!$rviObj->saveRecord($fieldarr)) {
        echoJSDoc("error", "Error al crear nuevo registro de reposición de viáticos", null, ["action"=>"newRecord", "errno"=>DBi::$errno, "error"=>DBi::$error, "query"=>$query], "error");
        return;
    }
    $viaId=$rviObj->lastId;
    $_POST["regid"]="$viaId";
    $_POST["recipient"]=$beneficiario;
    doGetRecord();
}
function doSaveRecord() {
    global $rviObj, $rvcObj, $gpoObj, $query;
    array_walk($_POST, 'trim_value');
    $viaId=$_POST["regid"]??"";
    if (!isset($viaId[0])) { echoJSDoc("error", "No se recibió registro a guardar", null, $_POST+["action"=>"saveRecord"], false); return; }
    $fieldarr=["id"=>$viaId];
    if (isset($_POST["fechapago"])) {
        if (!isset($_POST["fechapago"][0])) { echoJSDoc("error", "Debe indicar una fecha de pago", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["fechapago"]=date("Y-m-d", strtotime(str_replace("/", "-", $_POST["fechapago"])));
    }
    if (isset($_POST["beneficiario"])) {
        if (!isset($_POST["beneficiario"][0])) { echoJSDoc("error", "Debe indicar un beneficiario", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["beneficiario"]=$_POST["beneficiario"];
    }
    if (isset($_POST["empresaId"])) {
        if (!isset($_POST["empresaId"][0])) { echoJSDoc("error", "Debe indicar una empresa", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["empresaId"]=$_POST["empresaId"];
    }
    if (isset($_POST["banco"])) {
        if (!isset($_POST["banco"][0])) { echoJSDoc("error", "Debe indicar un banco", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["banco"]=$_POST["banco"];
    }
    if (isset($_POST["cuentabancaria"])) {
        if (!isset($_POST["cuentabancaria"][0])) { echoJSDoc("error", "Debe indicar una cuenta bancaria", null, $_POST+["action"=>"saveRecord"], false); return; }
        $cuentabancaria=str_replace(" ", "", $_POST["cuentabancaria"]);
        if (isset($cuentabancaria[20])) $cuentabancaria=substr($cuentabancaria, 0, 20);
        $fieldarr["cuentabancaria"]=$cuentabancaria;
    }
    if (isset($_POST["cuentaclabe"])) {
        if (!isset($_POST["cuentaclabe"][0])) { echoJSDoc("error", "Debe indicar una cuenta CLABE", null, $_POST+["action"=>"saveRecord"], false); return; }
        $cuentaclabe=str_replace(" ", "", $_POST["cuentaclabe"]);
        if (isset($cuentaclabe[20])) $cuentaclabe=substr($cuentaclabe, 0, 20);
        $fieldarr["cuentaclabe"]=$cuentaclabe;
    }
    if (isset($_POST["observaciones"])) {
        if (!isset($_POST["observaciones"][0])) { echoJSDoc("error", "Debe indicar sus observaciones", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["observaciones"]=$_POST["observaciones"];
    }
    if (isset($_POST["lugares"])) {
        if (!isset($_POST["lugares"][0])) { echoJSDoc("error", "Debe indicar un lugar de visita", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["lugaresvisita"]=$_POST["lugares"];
    }
    if (isset($_POST["reqviaticos"])) {
        if (!isset($_POST["reqviaticos"][0])) { echoJSDoc("error", "Debe indicar el monto de viáticos requerido", null, $_POST+["action"=>"saveRecord"], false); return; }
        $numReqVia= +($_POST["reqviaticos"]);
        if ($numReqVia <= 0) { echoJSDoc("error", "Debe indicar un monto de viáticos requerido", null, $_POST+["action"=>"saveRecord"], false); return; }
        $fieldarr["viaticosrequeridos"]=$_POST["reqviaticos"];
    }
    $control=$_POST["control"]??"";
    if ($control==="pagado") {
        $fieldarr["pagadoPor"]=getUser()->persona;
        if (isset($_POST["control2"])&&$_POST["control2"]==="autorizar") {
            $fieldarr["autorizadoPor"]=getUser()->persona;
        }
    } else if ($control==="autorizar") {
        // validar que todos los archivos tengan XML
        if (!isset($rvcObj)) {
            require_once "clases/RepViaConceptos.php";
            $rvcObj=new RepViaConceptos();
        }
        $rvcValue=+$rvcObj->getValue("vid",$viaId,"count(1)","archivoxml IS NULL");
        if ($rvcValue!==0) { echoJSDoc("error", "Para autorizar el registro, todos los conceptos deben tener XML.", null, $_POST+["action"=>"saveRecord", "query"=>$query, "result"=>$rvcValue], false); return; }
        $fieldarr["autorizadoPor"]=getUser()->persona;
    } else if ($control==="rechazar") {
        $fieldarr["rechazadoPor"]=getUser()->persona;
    } else if ($control==="pendiente") {
        $fieldarr["autorizadoPor"]=NULL;
        $fieldarr["rechazadoPor"]=NULL;
    }
    if (!isset($rviObj)) {
        require_once "clases/ReposicionViaticos.php";
        $rviObj = new ReposicionViaticos();
    }
    DBi::autocommit(false);
    if (!$rviObj->saveRecord($fieldarr)&&DBi::$errno>0) {
        DBi::rollback();
        DBi::autocommit(true);
        global $query;
        echoJSDoc("error", "Error al guardar registro de reposición de viáticos.", null, $_POST+["action"=>"saveRecord", "errno"=>DBi::$errno,"error"=>DBi::$error, "query"=>$query, "result"=>$rvcValue], "error");
        return;
    }
    if ($control==="autorizar") {
        $rviData=$rviObj->getData("id=$viaId", 1, "empresaId");
        $rviQuery=$query;
        if (isset($rviData[0]["empresaId"])) {
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj=new Grupo();
            }
            $gpoData=$gpoObj->getData("id='".$rviData[0]["empresaId"]."'", 1, "alias");
            $gpoQuery=$query;
            $alias=$gpoData[0]["alias"]??"";
            if (isset($alias[0])) {
                if (!isset($rvcObj)) {
                    require_once "clases/RepViaConceptos.php";
                    $rvcObj=new RepViaConceptos();
                }
                $rvcData=$rvcObj->getData("vid=$viaId and archivoxml is not null", 0, "archivoxml,archivopdf");
                $rvcQuery=$query;
                try {
                    $llog=[];
                    $req_start_time=+$_SERVER["REQUEST_TIME_FLOAT"]; // seconds
                    $start_time=microtime(true); // seconds
                    $ini_max_time=+ini_get('max_execution_time'); // seconds
                    $before_timeout=$ini_max_time-($start_time-$req_start_time);
                    doclog("BEGIN TRANSFER VT HAVING $before_timeout s.","ftp");
                    $lastLapse_time=$start_time;
                    foreach ($rvcData as $rvcRow) {
                        transferInvoice($alias,$rvcRow["archivoxml"],$rvcRow["archivopdf"],$llog);
                        $lapse_time=microtime(true);
                        $lapse_max_time=+ini_get('max_execution_time');
                        $before_timeout=$lapse_max_time-($lapse_time-$req_start_time);
                        $process_time=$lapse_time-$lastLapse_time;
                        doclog("TRANSFERRED VT $alias,$rvcRow[archivoxml],$rvcRow[archivopdf] AFTER $process_time s, HAVING $before_timeout s.","ftp");
                        $lastLapse_time=$lapse_time;
                    }
                } catch (Exception $e) {
                    DBi::rollback();
                    DBi::autocommit(true);
                    echoJSDoc("error", "Error al respaldar archivos de factura.", null, $_POST+["action"=>"saveRecord", "query"=>$rvcQuery, "error"=>getErrorData($e)], "error");
                    return;
                }
            } else {
                DBi::rollback();
                DBi::autocommit(true);
                echoJSDoc("error", "No se encontró empresa relacionada con Reembolso de Viáticos.", null, $_POST+["action"=>"saveRecord", "folio"=>$viaId, "query"=>$gpoQuery], "error");
                return;
            }
        } else {
            DBi::rollback();
            DBi::autocommit(true);
            echoJSDoc("error", "No se encontró empresa relacionada con Reembolso de Viáticos.", null, $_POST+["action"=>"saveRecord", "query"=>$rviQuery], "error");
            return;
        }
    }
    DBi::commit();
    DBi::autocommit(true);
    if (getUser()->nombre==="viajero") {
        $rviData=$rviObj->getData("id=$viaId", 1, "beneficiario,empresaId");
        if (isset($rviData[0])) {
            $_POST["recipient"]=$rviData[0]["beneficiario"];
            $_POST["empresaId"]=$rviData[0]["empresaId"];
        }
    }
    doGetRecord();
}
function doFixPerDiem() {
    global $rviObj, $rvcObj, $tmpObj, $query;
    array_walk($_POST, 'trim_value');
    $conId=$_POST["conId"]??"";
    if (!isset($conId[0])) { echoJSDoc("error", "No se recibió un concepto válido", null, $_POST+["action"=>"fixPerDiem"], "error"); return; }
    $fieldarr=["id"=>$conId];
    $viaId=$_POST["regid"]??"";
    if (!isset($viaId[0])) { echoJSDoc("error", "No se recibió registro a guardar", null, $_POST+["action"=>"fixPerDiem"], "error"); return; }
    $fieldarr["vid"]=$viaId;
    $fecha=$_POST["fecha"]??"";
    $logs=["0-ID_VIATICO"=>$viaId, "0-ID_CONCEPTO"=>$conId];
    if (isset($fecha[0])) {
        $timestamp=strtotime(str_replace("/", "-", $fecha));
        $fieldarr["fecha"]=date("Y-m-d", $timestamp);
        $logs["1-DATE"]="'$fecha'=>'$fieldarr[fecha]'";
    }
    $fechafactura=$_POST["fechafactura"]??"";
    if (isset($fechafactura[0])) {
        $diafactura=substr($fechafactura, 0, 10);
        $horafactura=substr($fechafactura,11);
        $timestamp=strtotime(str_replace("/","-",$diafactura));
        $fieldarr["fechafactura"]=date("Y-m-d", $timestamp);
        if (isset($horafactura[0])) $fieldarr["fechafactura"].=" ".$horafactura;
    }
    $fechaActual=$_POST["fechaActual"]??"";
    if (!isset($timestamp) && isset($fechaActual[0])) {
        $timestamp=strtotime(str_replace("/","-",$fechaActual));
    }
    if (isset($_POST["concepto"])) {
        $concepto=$_POST["concepto"]??"";
        if (!isset($concepto[0])) { echoJSDoc("error", "No se recibió un concepto válido", null, $_POST+["action"=>"fixPerDiem"], "error"); return; }
        $fieldarr["concepto"]=$concepto;
        $logs["2-CONCEPTO"]="$concepto";
    }
    if (isset($_POST["importe"])) {
        $importe = +($_POST["importe"]??"0");
        if ($importe <= 0) { echoJSDoc("error", "Se requiere un importe válido", null, $_POST+["action"=>"fixPerDiem"], "error"); return; }
        $fieldarr["importe"]=$importe;
        $logs["3-IMPORTE"]=$importe;
    }
    if (isset($_POST["folio"])) {
        $folio=$_POST["folio"]??"";
        $logs["4-FOLIO"]="$folio";
        if (isset($folio[0])) {
            $fieldarr["foliofactura"]=$folio;
            $logs["4-FOLIO"].=". added to fieldarr";
        } else {
            $fieldarr["foliofactura"]=NULL;
            $logs["4-FOLIO"].=". IS NULL";
        }
    }
    $docspath="C:/InvoiceCheckShare/invoiceDocs/";
    $temppath=$docspath."temporal/";
    $dtpath=date("Ym",$timestamp)."/";
    $viatpath=$docspath."viajes/".$dtpath;
    if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
    require_once "clases/DBi.php";
    if (!isset(DBi::$tryConnect) || DBi::$tryConnect<=0)
        DBi::connect();
    DBi::autocommit(false);
    $queries=[];
    $results=[];
    if (isset($_POST["xmlId"][0])) {
        $xmlId=$_POST["xmlId"];
        if (!isset($tmpObj)) {
            require_once "clases/Temporales.php";
            $tmpObj = new Temporales();
        }
        $xmlName=$tmpObj->procesar($xmlId);
        $fullTmpXML=$temppath.$xmlName;
        $queries["ProcesarXML"]=$query;
        $results["ProcesarXML"]=$xmlName;
        if ($xmlName!==false && file_exists($fullTmpXML)) {
            $xmlData=getCFDIData($fullTmpXML, $xmlName, true, "CC".$viaId, $concepto??null);
            if (isset($xmlData["filename"][0])) {
                $xmlName=$xmlData["filename"].".xml";
                $fechafactura=new DateTime($xmlData["fecha"]);
                $dtpath=$fechafactura->format("Ym")."/";
                $viatpath=$docspath."viajes/".$dtpath;
                if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
            }
            rename($fullTmpXML, $viatpath.$xmlName);
            $fieldarr["archivoxml"]=$dtpath.$xmlName;
            if (isset($xmlData["uuid"][0])) $fieldarr["uuid"]=$xmlData["uuid"];
            $fieldarr["archivostatus"]="recibido";
            $logs["5-ARCHIVOXML"]=$dtpath.$xmlName;

        }
    } else if (isset($_POST["xmlPath"]) && !isset($_POST["xmlPath"][0])) {
            $fieldarr["archivoxml"]=NULL;
            $fieldarr["archivostatus"]=NULL;
            $logs["5-ARCHIVOXML"]="IS NULL";
    }
    if (isset($_POST["pdfId"][0])) {
        $pdfId=$_POST["pdfId"];
        if (!isset($tmpObj)) {
            require_once "clases/Temporales.php";
            $tmpObj=new Temporales();
        }
        $pdfName=$tmpObj->procesar($pdfId);
        $fullTmpPDF=$temppath.$pdfName;
        $queries["ProcesarPDF"]=$query;
        $results["ProcesarPDF"]=$pdfName;
        if ($pdfName!==false && file_exists($fullTmpPDF)) {
            if (isset($xmlData["filename"][0])) {
                $pdfName=$xmlData["filename"].".pdf";
            }
            rename($fullTmpPDF, $viatpath.$pdfName);
            $fieldarr["archivopdf"]=$dtpath.$pdfName;
            $logs["6-ARCHIVOPDF"]=$dtpath.$pdfName;
        }
    } else if (isset($_POST["pdfPath"]) && !isset($_POST["pdfPath"][0])) {
        $fieldarr["archivopdf"]=NULL;
        $logs["6-ARCHIVOPDF"]="IS NULL";
    }
    if (isset($fieldarr["archivoxml"][0])&&!isset($fieldarr["foliofactura"])) { echoJSDoc("error", "Debe indicar el número de folio de su comprobante", null, $_POST+["action"=>"fixPerDiem", "fieldarr"=>$fieldarr], "error"); return; }
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    if (!$rvcObj->saveRecord($fieldarr)) {
        $errQry=$query;
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Error al modificar registro de reposición de viáticos", null, ["action"=>"fixPerDiem", "errno"=>DBi::$errno,"error"=>DBi::$error, "query"=>$errQry], "error");
        return;
    }
    $logs["7-SAVED"]="DONE";
    $queries["SaveRepViaConcepto"]=$query;
    $results["SaveRepViaConcepto"]="TRUE";
    recalcTotalAtRecord($viaId);

    DBi::commit();
    DBi::autocommit(true);
    if (getUser()->nombre==="viajero") {
        if (!isset($rviObj)) {
            require_once "clases/ReposicionViaticos.php";
            $rviObj = new ReposicionViaticos();
        }
        $rviData=$rviObj->getData("id=$viaId", 1, "beneficiario,empresaId");
        if (isset($rviData[0])) {
            $_POST["recipient"]=$rviData[0]["beneficiario"];
            $_POST["empresaId"]=$rviData[0]["empresaId"];
        }
    }
    doGetRecord(["post"=>$_POST,"queries"=>$queries,"results"=>$results,"logs"=>$logs]);
}
function doAddPerDiem() {
    global $rviObj, $rvcObj, $query, $username;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    array_walk($_POST, 'trim_value');
    $fecha=$_POST["fecha"]??"";
    if (!isset($fecha[0])) { echoJSDoc("error", "Indique la fecha del dia", null, $_POST+["action"=>"addPerDiem"], "error"); return; }
    $fechafactura=$_POST["fechafactura"]??"";
    $concepto=$_POST["concepto"]??"";
    $xmlId=$_POST["xmlId"]??"";
    $pdfId=$_POST["pdfId"]??"";
    $folio=$_POST["folio"]??"";
    $importe=+($_POST["importe"]??"0");
    $viaId=$_POST["regid"]??"";
    if (!isset($viaId[0])) { echoJSDoc("error", "No existe folio de registro de viáticos o su sesión ha expirado", null, $_POST+["action"=>"addPerDiem"], "error"); return; }
    $timestamp=strtotime(str_replace("/", "-", $fecha));
    $fechabd=date("Y-m-d", $timestamp);
    $dtpath=date("Ym",$timestamp)."/"; 
    if (isset($fechafactura[0])) {
        $diafactura=substr($fechafactura, 0, 10);
        $horafactura=substr($fechafactura,11);
        $timestamp=strtotime(str_replace("/","-",$diafactura));
        $fechafacturabd=date("Y-m-d", $timestamp);
        if (isset($horafactura[0])) $fechafacturabd.=" ".$horafactura;
        $dtpath=date("Ym",$timestamp)."/";
    }
    if (!isset($concepto[0])) { echoJSDoc("error", "Es necesario el concepto del viático", null, $_POST+["action"=>"addPerDiem"], "error"); return; }
    if ((isset($xmlId[0])||isset($pdfId[0])) && !isset($folio[0])) { echoJSDoc("error", "Especifique el folio de su comprobante", null, $_POST+["action"=>"addPerDiem"], "error"); return; }
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    if ($importe<=0) { echoJSDoc("error", "Debe indicar el importe del concepto"); return; }
    if (!isset($rviObj)) {
        require_once "clases/ReposicionViaticos.php";
        $rviObj = new ReposicionViaticos();
    }
    
    $rviData=$rviObj->getData("id=$viaId", 1, "empresaId");
    if (!isset($rviData[0]["empresaId"])) { echoJSDoc("error", "No existe el registro de Viáticos indicado", null, $_POST+["action"=>"addPerDiem"], "error"); return; }
    $empresaId=$rviData[0]["empresaId"];
    if (!isset($empresaId)) { echoJSDoc("error", "No se encontró empresa relacionada con su registro de Viáticos", null, $_POST+["action"=>"addPerDiem"], "error"); return; }
    //if (isset($folio[0]))
    DBi::autocommit(false);
    $datosConcepto=["vid"=>$viaId, "fecha"=>$fechabd,"concepto"=>$concepto,"importe"=>$importe];
    if (isset($fechafacturabd[0])) $datosConcepto["fechafactura"]=$fechafacturabd;
    if (isset($folio[0])) {
        if (!isset($xmlId[0]) && !isset($pdfId[0])) {
            if ($rvcObj->exists("rvc.foliofactura='$folio' and rv.empresaId=gpo")) {}
            // toDo: Buscar folios con la misma empresa y mismo folio
// select count(1) from repviaconceptos rvc inner join reposicionviaticos rv on rvc.vid=rv.id where rvc.foliofactura=22304 and rv.empresaId=18;
        }
        $datosConcepto["foliofactura"]=$folio;
        $docspath="C:/InvoiceCheckShare/invoiceDocs/";
        $temppath=$docspath."temporal/";
        $viatpath=$docspath."viajes/".$dtpath;
        if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
        if (isset($xmlId[0])) {
            if (!isset($tmpObj)) {
                require_once "clases/Temporales.php";
                $tmpObj = new Temporales();
            }
            $xmlName=$tmpObj->procesar($xmlId);
            $fullTmpXML=$temppath.$xmlName;
            if ($xmlName!==false && file_exists($fullTmpXML)) {
                $xmlData=getCFDIData($fullTmpXML, $xmlName, true, "CC".$viaId, $concepto);
                if (isset($xmlData["filename"][0])) {
                    $xmlName=$xmlData["filename"].".xml";
                    $fechafactura=new DateTime($xmlData["fecha"]);
                    $dtpath=$fechafactura->format("Ym")."/";
                    $viatpath=$docspath."viajes/".$dtpath;
                    if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
                }
                rename($fullTmpXML, $viatpath.$xmlName);
                $datosConcepto["archivoxml"]=$dtpath.$xmlName;
                if (isset($xmlData["uuid"][0])) $datosConcepto["uuid"]=$xmlData["uuid"];
                $datosConcepto["archivostatus"]="recibido";
            }
        }
        if (isset($pdfId[0])) {
            if (!isset($tmpObj)) {
                require_once "clases/Temporales.php";
                $tmpObj=new Temporales();
            }
            $pdfName=$tmpObj->procesar($pdfId);
            $fullTmpPDF=$temppath.$pdfName;
            if ($pdfName!==false && file_exists($fullTmpPDF)) {
                if (isset($xmlData["filename"][0])) {
                    $pdfName=$xmlData["filename"].".pdf";
                }
                rename($fullTmpPDF, $viatpath.$pdfName);
                $datosConcepto["archivopdf"]=$dtpath.$pdfName;
            }
        }
    }
    if (!$rvcObj->saveRecord($datosConcepto)) {
        $rvcQry=$query;
        DBi::rollback();
        DBi::autocommit(true);
        $errMessage="Información incompleta para nuevo registro de reposición de viáticos";
        switch (DBi::$errno) {
            case 1062: // Duplicate entry '...' for key '???' (uuid_UNIQUE, archivoxml_UNIQUE, archivopdf_UNIQUE)
                if (strpos(DBi::$error, "uuid_UNIQUE")>0) { // siempre va a estar al final, nunca va a estar al principio
                    $errMessage="El comprobante fiscal fue registrado anteriormente, no se permite ingresar nuevamente";
                }
            break;
        }
        echoJSDoc("error", $errMessage, null, $baseData+$_POST+["action"=>"addPerDiem", "line"=>__LINE__, "DBQuery"=>$rvcQry, "DBErrno"=>DBi::$errno, "DBError"=>DBi::$error], "error");
        return;
    }
    recalcTotalAtRecord($viaId);

    DBi::commit();
    DBi::autocommit(true);
    if ($username==="viajero") {
        $rviData=$rviObj->getData("id=$viaId", 1, "beneficiario,empresaId");
        if (isset($rviData[0])) {
            $_POST["recipient"]=$rviData[0]["beneficiario"];
            $_POST["empresaId"]=$rviData[0]["empresaId"];
        }
    }
    doGetRecord();
}
function doGetRecord($additionalData=null) {
    global $rviObj, $rvcObj, $gpoObj, $query, $perObj, $ugObj;
    $viaId=$_POST["regid"]??"";
    $nameId=$_POST["recipient"]??"";
    $firmId=$_POST["empresaId"]??"";
    $esSolicitante = (getUser()->nombre==="viajero");
    if ($esSolicitante) {
        if (!isset($viaId[0])||!isset($nameId[0])||!isset($firmId[0])) { echoJSDoc("error", "Es necesario ingresar todos los datos para realizar la búsqueda", null, $_POST+["action"=>"getRecord"], "error"); return; }
    } else if (!isset($viaId[0])&&!isset($nameId[0])) { echoJSDoc("error", "Se necesita el número de registro o nombre del beneficiario de los viáticos", null, $_POST+["action"=>"getRecord"], "error"); return; }
    if (!isset($rviObj)) {
        require_once "clases/ReposicionViaticos.php";
        $rviObj = new ReposicionViaticos();
    }
    $where="";
    if (isset($viaId[0])) $where.="id='$viaId'";
    if (isset($nameId[0])) {
        $lowName=str_replace(" ", "", mb_strtolower($nameId));
        $where.=(isset($where[0])?" AND ":"")."lower(replace(beneficiario,' ','')) like '%{$lowName}%'";
    }
    if (!isset($perObj)) {
        require_once "clases/Perfiles.php";
        $perObj=new Perfiles();
    }
    $ccId=$perObj->getIdByName("Viaticos"); // Viaticos
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $ugObj->rows_per_page=0;
    $refundGroupId=$ugObj->getRefundGroupId(getUser()->id, $ccId, "vista");
    if (isset($firmId[0])) $where.=(isset($where[0])?" AND ":"")."empresaId=$firmId";
    else if (isset($refundGroupId[1])) $where.=" and empresaId in (".implode(",",$refundGroupId).")";
    else if (isset($refundGroupId[0])) $where.=" and empresaId=".$refundGroupId[0];
    $rviData = $rviObj->getData($where);
    if (!isset($rviData[0])) { echoJSDoc("error", "No se encontró el registro solicitado", null, $_POST+["action"=>"getRecord", "query"=>$query], "error"); return; }
    // armar datos
    for ($i=0; isset($rviData[$i]); $i++) {
        if (!isset($rvcObj)) {
            require_once "clases/RepViaConceptos.php";
            $rvcObj = new RepViaConceptos();
        }
        $rvcObj->clearOrder();
        $rvcObj->addOrder("fecha");
        $rvcObj->addOrder("concepto");
        $rvcData = $rvcObj->getData("vid='".$rviData[$i]["id"]."'");
        $rviData[$i]["conceptos"] = [];
        foreach ($rvcData as $val) {
            $cfecha=$val["fecha"];
            $cfecha=substr($cfecha,5,2).substr($cfecha,8,2);
            $cnombre=$val["concepto"];
            if (!isset($rviData[$i]["conceptos"][$cfecha])) $rviData[$i]["conceptos"][$cfecha]=[];
            if (!isset($rviData[$i]["conceptos"][$cfecha][$cnombre])) $rviData[$i]["conceptos"][$cfecha][$cnombre]=[];
            $rviData[$i]["conceptos"][$cfecha][$cnombre][]=$val;
        }
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $gpoData=$gpoObj->getData("id='".$rviData[$i]["empresaId"]."'", 1, "alias");
        if (isset($gpoData[0])) $rviData[$i]["empresa"]=$gpoData[0]["alias"];
    }
    $result=["result"=>"exito","message"=>"Carga de archivos exitosa","datos"=>$rviData];
    if (isset($additionalData)) $result+=$additionalData;
    if (isset($_POST["viewzone"][0])) $result["viewzone"]=$_POST["viewzone"];
    if (isset($_POST["focuselem"][0])) $result["focuselem"]=$_POST["focuselem"];
    echo json_encode($result);
}
function doDeleteRecord() {
    global $rvcObj, $rviObj, $query;
    $viaId=$_POST["regid"];
    if (!isset($viaId[0])) { echoJSDoc("error", "No se recibió registro a borrar", null, $_POST+["action"=>"deleteRecord"], "error"); return; }
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    DBi::autocommit(false);
    if (!$rvcObj->deleteRecord(["vid"=>$viaId,"id"=>new DBExpression("0",">")]) && !empty(DBi::$errors)) {
        $rvcQuery=$query; $rvcErrno=DBi::$errno; $rvcError=DBi::$error;
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Ocurrió un error al eliminar conceptos", null, $_POST+["action"=>"deleteRecord", "errno"=>$rvcErrno, "error"=>$rvcError, "query"=>$rvcQuery], "error");
        return;
    }
    if (!isset($rviObj)) {
        require_once "clases/ReposicionViaticos.php";
        $rviObj = new ReposicionViaticos();
    }
    if (!$rviObj->deleteRecord(["id"=>$viaId])) {
        $rviQuery=$query; $rviErrno=DBi::$errno; $rviError=DBi::$error;
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Ocurrió un error al eliminar registro", null, $_POST+["action"=>"deleteRecord", "errno"=>$rviErrno, "error"=>$rviError, "query"=>$rviQuery], "error");
        return;
    }
    DBi::commit();
    DBi::autocommit(true);
    $result=["result"=>"exito","message"=>"Registro eliminado satisfactoriamente"];
    echo json_encode($result);
}
function doDelPerDiem() {
    global $rviObj, $rvcObj, $query;
    $conceptoId=$_POST["perDiemId"]??"";
    $viaId=$_POST["regid"]??"";
    if (!isset($conceptoId[0])) { echoJSDoc("error", "No se recibió concepto a borrar", null, $_POST+["action"=>"delPerDiem"], "error"); return; }
    if (!isset($viaId[0])) { echoJSDoc("error", "No se recibió registro a borrar", null, $_POST+["action"=>"delPerDiem"], "error"); return; }
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    DBi::autocommit(false);
    if (!$rvcObj->deleteRecord(["id"=>$conceptoId,"vid"=>$viaId])) {
        $rvcQuery=$query; $rvcErrno=DBi::$errno; $rvcError=DBi::$error;
        DBi::rollback();
        DBi::autocommit(true);
        echoJSDoc("error", "Ocurrió un error al borrar concepto", null, $_POST+["action"=>"delPerDiem", "errno"=>$rvcErrno, "error"=>$rvcError, "query"=>$rvcQuery], "error");
        return;
    }
    recalcTotalAtRecord($viaId);
    DBi::commit();
    DBi::autocommit(true);
    if (getUser()->nombre==="viajero") {
        if (!isset($rviObj)) {
            require_once "clases/ReposicionViaticos.php";
            $rviObj = new ReposicionViaticos();
        }
        $rviData=$rviObj->getData("id=$viaId", 1, "beneficiario,empresaId");
        if (isset($rviData[0])) {
            $_POST["recipient"]=$rviData[0]["beneficiario"];
            $_POST["empresaId"]=$rviData[0]["empresaId"];
        }
    }
    doGetRecord();
}
function recalcTotalAtRecord($id) {
    global $query,$rviObj,$rvcObj;
    if (!isset($rvcObj)) {
        require_once "clases/RepViaConceptos.php";
        $rvcObj = new RepViaConceptos();
    }
    $rvcData=$rvcObj->getData("vid='$id'", 1, "sum(importe) total");
    if (isset($rvcData[0]["total"])) {
        $total = +$rvcData[0]["total"];
        if (!isset($rviObj)) {
            require_once "clases/ReposicionViaticos.php";
            $rviObj=new ReposicionViaticos();
        }
        $rviObj->updateRecord(["id"=>$id, "montototal"=>"$total"]);
    }
}
function doAddFiles() {
    global $hasUser, $username;
    $regId=$_POST["regId"];
    $mdates=$_POST["mdates"]??[];
    $files=getFixedFileArray($_FILES["files"]);
    $messages=$_POST["message"]??[];
    if (!is_array($messages)) $messages=[$messages];
    if (!isset($regId[0])) { echoJSDoc("error", "Debe existir un registro de caja chica válido para ingresar archivos", null, $_POST+["action"=>"addFiles"], "error"); return; }
    global $rarObj, $rccObj;
    if (!isset($rccObj)) {
        require_once "clases/ReposicionCajaChica.php";
        $rccObj = new ReposicionCajaChica();
    }
    $rccData=$rccObj->getData("id=$regId", 1, "empresaId");
    if (!isset($rccData[0]["empresaId"])) { echoJSDoc("error", "No se reconoce la empresa indicada", null, $_POST+["action"=>"addFiles"], "error"); return; }
    $gpoId=$rccData[0]["empresaId"];
    if (!isset($rarObj)) {
        require_once "clases/ReposicionArchivos.php";
        $rarObj = new ReposicionArchivos();
    }
    $funcData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    if ($hasUser) $funcData["usuario"]=$username;
    $dbdata=[];
    $filepath="C:/InvoiceCheckShare/invoiceDocs/viajes/";
    $numReceivedFiles=0;
    $numSavedFiles=0;
    $numXMLFiles=0;
    $numPreparedFields=0;
    $numErrorMessages=0;
    $concepto=$_POST["concepto"]??null;
    $thisTimeStamp=date("ymdB");
    for ($i=0;isset($files[$i]);$i++) {
        $numReceivedFiles++;
        $file=$files[$i];
        $modifDate=$mdates[$i]??(int)(microtime(true) * 1000);
        $isXML=false;$isPDF=false;
        $errorMsg=getInvalidFileMessage($file,"text/xml");
        if (isset($errorMsg[0])) {
            $pdfMsg=getInvalidFileMessage($file,"application/pdf");
            if (isset($pdfMsg[0])) {
                if ($errorMsg!==$pdfMsg) $errorMsg.=" o 'application/pdf'";
            } else {
                $isPDF=true;
                $errorMsg="";
            }
        } else $isXML=true;
        if (isset($errorMsg[0])) {
            $numErrorMessages++;
            $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>$errorMsg];
        } else {
            $filename=$file["name"];
            $tmpname=$file["tmp_name"];
            $dotIdx=strrpos($filename, ".");
            $baseName=substr($filename, 0,$dotIdx);
            if (isset($baseName[96])) {
                $oriBaseName=$baseName;
                $ext=substr($filename, $dotIdx);
                $baseName=substr($baseName, 0, 86)."_".$thisTimeStamp;
                $dotIdx=96;
                $filename=$baseName.$ext;
                if (isset($dbdata[$oriBaseName])) $dbdata[$baseName]=$dbdata[$oriBaseName];
            }
            if (isset($dbdata[$baseName])) {
                $fieldarr=$dbdata[$baseName];
            } else {
                $fieldarr=["repid"=>$regId];
            }
            if ($isXML) {
                $numXMLFiles++;
                $xmlData=getCFDIData($tmpname, $filename, true, "CC".$regId, $concepto);
                $tipoComprobante=strtolower($xmlData["tipocomprobante"][0]??"");
                if (isset($xmlData["gpoId"][0])&&$gpoId!==$xmlData["gpoId"]) {
                    $numErrorMessages++;
                    $messages[]=["eName"=>"P","classname"=>"cancelLabel boldValue bgred", "eText"=>"La factura debe corresponder a la empresa indicada"];
                    doclog("EMPRESA INCORRECTA","cajachica",["regGpoId"=>$gpoId,"invGpoId"=>$xmlData["gpoId"],"post"=>$_POST,"file"=>$file,"xmldata"=>$xmlData]);
                } else if (isset($xmlData["error"])) {
                    $numErrorMessages++;
                    $messages[]=["eName"=>"P","classname"=>"cancelLabel boldValue bgred","eText"=>strip_tags($xmlData["error"]),"stack"=>$xmlData["stack"]];
                } else if ($tipoComprobante!=="i"&&$tipoComprobante!=="e") {
                    $numErrorMessages++;
                    $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>"Error al ingresar un CFDI que no es ingreso ni egreso ($tipoComprobante)"];
                    //ToDo: doclog nombres de archivos originales, xmlData["tipocomprobante"]
                    doclog("TIPO DE COMPROBANTE INVALIDO","cajachica",["original"=>$filename,"tc"=>$tipoComprobante]);
                } else if (isset($xmlData["fecha"][0])) {
                    $filename=$xmlData["filename"].".xml";
                    $fechafactura=new DateTime($xmlData["fecha"]);
                    $dtpath=$fechafactura->format("Ym")."/";
                    $xmlWebName=$dtpath.$filename;
                    $viatpath=$filepath.$dtpath;
                    $xmlFullName=$filepath.$xmlWebName;
                    //doclog("VERIFICACION DE ARCHIVO","cajachica",["original"=>$filename,"xml"=>$xmlWebName,"path"=>$xmlFullName]);
                    if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
                    if (file_exists($xmlFullName)) {
                        // El nombre del archivo incluye el folio por lo que no se puede distinguir si la factura fue ingresada en otra caja chica. 
                        //doclog("SI SE ENCONTRO ARCHIVO","cajachica",["original"=>$filename,"xml"=>$xmlWebName,"path"=>$xmlFullName]);
                        $rarData=$rarObj->getData("archivoxml='{$dtpath}{$filename}'", 0, "repid");
                        global $query;
                        if (isset($rarData[0]["repid"])) {
                            $oldRepId=$rarData[0]["repid"];
                            doclog("RESULTADO DE BUSQUEDA DE XML EN BD por nombre","cajachica",["original"=>$filename, "query"=>$query, "data"=>$rarData]);
                            echoJSDoc("error", "El comprobante fue ingresado previamente", null, $_POST+["action"=>"addFiles", "original"=>$filename], false);
                            return;
                        }
                    }
                    if (isset($xmlData["folio"][0])) {
                        $folio=$xmlData["folio"];
                        $rfcEmisor=$xmlData["rfcemisor"];
                        // toDo: agregar a xmlData el id del receptor
                        // toDo: agregar inner join a reposicioncajachica para validar la empresa
                        // toDo: el error solo debe generarse si se encuentra factura con el mismo folio, rfcEmisor y empresaId
                        $rarData=$rarObj->getData("foliofactura='$folio' and rfcemisor='$rfcEmisor'", 0, "repid,archivoxml");
                        global $query;
                        if (isset($rarData[0]["repid"])) {
                            $oldRepId=$rarData[0]["repid"];
                            $oldXML=$rarData[0]["archivoxml"];
                            // quitar texto de oldXML, todo lo anterior a la diagonal, todo lo posterior al punto: 202106/CC174_BIDASOA_302111373.xml => CC174_BIDASOA_302111373
                            $oldXMLFix=substr($oldXML, 7, -4);
                            $oldYear=+substr($oldXML, 0, 4);
                            $newYear=+substr($dtpath, 0, 4);
                            if ($oldYear==$newYear) {
                                doclog("RESULTADO DE BUSQUEDA DE XML EN BD por folio","cajachica",["original"=>$filename,"query"=>$query,"data"=>$rarData,"newFilePath"=>$xmlWebName]);
                                echoJSDoc("error", "Ya existe un comprobante en el sistema con este folio", null, $_POST+["action"=>"addFiles", "folio"=>$folio, "rfcEmisor"=>$rfcEmisor, "anio"=>$newYear], "error");
                                return;
                            }
                        }
                    } else {
                        $flnm=$xmlData["filename"];
                        $undIdx=strpos($flnm, "_");
                        if ($undIdx!==false) {
                            $mainChunk=substr($flnm, $undIdx+1);
                            $rfcEmisor=$xmlData["rfcemisor"];
                            $rarData=$rarObj->getData("archivoxml like '%{$mainChunk}.xml' and rfcemisor='$rfcEmisor'", 0, "repid,archivoxml");
                            global $query;
                            if (isset($rarData[0]["repid"])) {
                                $oldRepId=$rarData[0]["repid"];
                                $oldXML=$rarData[0]["archivoxml"];
                                doclog("RESULTADO DE BUSQUEDA DE XML EN BD por UUID","cajachica",["original"=>$filename,"query"=>$query,"data"=>$rarData]);
                                echoJSDoc("error", "El comprobante fue ingresado previamente", null, $_POST+["action"=>"addFiles", "idReg"=>$oldRepId, "xmlReg"=>$oldXML, "rfcEmisor"=>$rfcEmisor], "error");
                                return;
                            }
                        }
                        //doclog("NO SE ENCONTRO ARCHIVO","cajachica",["original"=>$filename,"xml"=>$xmlWebName,"path"=>$xmlFullName]);
                    }
                    if (move_uploaded_file($tmpname,$xmlFullName)===false) { $line=__LINE__;
                        $numErrorMessages++;
                        $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>"Error al transferir archivo $file[name]"];
                        doclog("NO SE PUDO MOVER ARCHIVO XML","cajachica",["file"=>$file,"stillExists"=>file_exists($tmpname)?"yes":"no","xmlFullName"=>$xmlFullName,"numErrMsgs"=>$numErrorMessages,"numXMLFiles"=>$numXMLFiles,"numReceivedFiles"=>$numReceivedFiles,"line"=>$line]+$funcData);
                    } else {
                        chmod($xmlFullName,0666);
                        $fieldarr["archivoxml"]=$xmlWebName;
                        if (isset($xmlData["uuid"][0])) $fieldarr["uuid"]=$xmlData["uuid"];
                        if (isset($fieldarr["tmp_pdf"][0])) {
                            $pdffilename=$xmlData["filename"].".pdf";
                            $pdfWebName=$dtpath.$pdffilename;
                            $pdfFullName=$filepath.$pdfWebName;
                            if (move_uploaded_file($fieldarr["tmp_pdf"],$pdfFullName)===false) { $line=__LINE__;
                                $numErrorMessages++;
                                $messages[]=["eName"=>"P","className"=>"cancellabel boldValue bgred","eText"=>"Error al transferir archivo $fieldarr[name_pdf]"];
                                doclog("NO SE PUDO MOVER ARCHIVO PDF","cajachica",["fieldarr"=>$fieldarr,"stillExistsTmpPDF"=>file_exists($fieldarr["tmp_pdf"])?"yes":"no","pdfFullName"=>$pdfFullName,"numErrMsgs"=>$numErrorMessages,"numXMLFiles"=>$numXMLFiles,"numReceivedFiles"=>$numReceivedFiles,"line"=>$line]+$funcData);
                            } else $fieldarr["archivopdf"]=$pdfWebName;
                            unset($fieldarr["tmp_pdf"]);
                            unset($fieldarr["name_pdf"]);
                        } else {
                            $fieldarr["datepath"]=$dtpath;
                            $fieldarr["name_xml"]=$xmlData["filename"];
                        }
                        if (isset($xmlData["folio"][0]))
                            $fieldarr["foliofactura"]=$xmlData["folio"];
                        $fieldarr["fechafactura"]=$fechafactura->format("Y-m-d H:i:s");
                        $fieldarr["totalfactura"]=$xmlData["total"];
                        $fieldarr["tipocomprobante"]=$tipoComprobante;
                        $fieldarr["rfcemisor"]=$xmlData["rfcemisor"];
                    }
                } else {
                    $numErrorMessages++;
                    $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>"Error al obtener datos de $filename"];
                }
            } else if ($isPDF) {
                if (isset($fieldarr["datepath"])) {
                    $filename=$fieldarr["name_xml"].".pdf";
                    $pdfWebName=$fieldarr["datepath"].$filename;
                    $pdfFullName=$filepath.$pdfWebName;
                    if (move_uploaded_file($tmpname,$pdfFullName)===false) { $line=__LINE__;
                        $numErrorMessages++;
                        $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>"Error al transferir archivo $file[name]"];
                        doclog("NO SE PUDO MOVER ARCHIVO PDF","cajachica",["file"=>$file,"stillExistsTmpPDF"=>file_exists($tmpname)?"yes":"no","pdfFullName"=>$pdfFullName,"line"=>$line]+$funcData);
                    } else {
                        chmod($pdfFullName,0777);
                        $fieldarr["archivopdf"]=$pdfWebName;
                    }
                    unset($fieldarr["datepath"]);
                    unset($fieldarr["name_xml"]);
                } else {
                    $fieldarr["tmp_pdf"]=$tmpname;
                    $fieldarr["name_pdf"]=$filename;
                    $fieldarr["modif_pdf"]=$modifDate;
                }
            }
            $dbdata[$baseName]=$fieldarr;
        }
    }
    foreach ($dbdata as $baseName => $fieldarr) {
        $numPreparedFields++;
        if (isset($fieldarr["tmp_pdf"])&&isset($fieldarr["name_pdf"])) {
            $dtpath=date("Ym", $fieldarr["modif_pdf"])."/";
            $pdfWebName=$dtpath.$fieldarr["name_pdf"];
            $viatpath=$filepath.$dtpath;
            $pdfFullName=$filepath.$pdfWebName;
            if (!file_exists($viatpath)) mkdir($viatpath,0777,true);
            if (move_uploaded_file($fieldarr["tmp_pdf"],$pdfFullName)===false) { $line=__LINE__;
                $numErrorMessages++;
                $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>"Error al transferir archivo $fieldarr[name_pdf]"];
                doclog("NO SE PUDO MOVER ARCHIVO PDF(B)","cajachica",["fieldarr"=>$fieldarr,"stillExistsTmpPDF"=>file_exists($fieldarr["tmp_pdf"])?"yes":"no","pdfFullName"=>$pdfFullName,"numErrMsgs"=>$numErrorMessages,"numPrepFlds"=>$numPreparedFields,"line"=>$line]+$funcData);
            } else {
                chmod($pdfFullName,0666);
                $fieldarr["archivopdf"]=$pdfWebName;
            }
            unset($fieldarr["tmp_pdf"]);
            unset($fieldarr["name_pdf"]);
            unset($fieldarr["modif_pdf"]);
        }
        unset($fieldarr["datepath"]);
        unset($fieldarr["name_xml"]);
        if (isset($fieldarr["archivoxml"])||isset($fieldarr["archivopdf"])) {
            if (!$rarObj->saveRecord($fieldarr)) {
                $numErrorMessages++;
                $messages[]=["eName"=>"P","className"=>"cancelLabel boldValue bgred","eText"=>"Error al agregar comprobante '$baseName' a la base de datos. ".json_encode(DBi::$errors)];
            } else $numSavedFiles++;
        }
    }
    doGetPettyCash(["errormessages"=>$messages,"nFiles"=>$numReceivedFiles,"nXML"=>$numXMLFiles,"nFields"=>$numPreparedFields,"nSaved"=>$numSavedFiles,"nErrors"=>$numErrorMessages,"post"=>$_POST,"files"=>$files]);
}
function doTemporales() {
    global $tmpObj;
    if (isset($_FILES["xml"])) {
        $xmlf=$_FILES["xml"];
        if (!isValidFile($xmlf,$invalidMessage,["type"=>"text/xml"])) { echoJSDoc("error", $invalidMessage, null, $_POST+["action"=>"temporales"], "error"); return; }
        //isValidFileCC($xmlf,"text/xml");
    }
    if (isset($_FILES["pdf"])) {
        $pdff=$_FILES["pdf"];
        if (!isValidFile($pdff,$invalidMessage,["type"=>"application/pdf"])) { echoJSDoc("error", $invalidMessage, null, $_POST+["action"=>"temporales"], "error"); return; }
        //isValidFileCC($pdff,"application/pdf");
    }
    if (!isset($xmlf) && !isset($pdff)) { echoJSDoc("error", "No se recibieron archivos", null, $_POST+["action"=>"temporales"], "error"); return; }
    if (!isset($tmpObj)) {
        require_once "clases/Temporales.php";
        $tmpObj=new Temporales();
    }
    $filepath="C:/InvoiceCheckShare/invoiceDocs/temporal/";
    $concepto=$_POST["concepto"]??null;
    if (isset($xmlf)) {
        $xmlName=$xmlf["name"];
        $xmlFullName=$filepath.$xmlName;
        if (move_uploaded_file($xmlf["tmp_name"],$xmlFullName)===false) { echoJSDoc("error", "Error al cargar archivo xml", null, $_POST+["action"=>"temporales", "xml"=>$xmlName], "error"); return; }
        chmod($xmlFullName, 0666);
        $xmlId=$tmpObj->ingresar($xmlName);
        if(!isset($xmlId)) { echoJSDoc("error", "Error al registrar archivo xml", null, $_POST+["action"=>"temporales", "xml"=>$xmlName], "error"); return; }
        $xmlData=getCFDIData($xmlFullName,$xmlName,true,"",$concepto);
        if (isset($xmlData["fecha"][0])) {
            $fechafactura=new DateTime($xmlData["fecha"]);
            $xmlData["fecha"]=$fechafactura->format("d/m/Y H:i:s");
        }
        if (!isset($xmlData["folio"][0])&&isset($xmlData["uuid"][0])) {
            $xmlData["folio"]=substr($xmlData["uuid"], -10);
        } else if (isset($xmlData["folio"][10])) {
            $xmlData["folio"]=substr($xmlData["folio"],-10);
        }
        if (isset($xmlData["total"][0])) {
            $xmlData["total"]=+$xmlData["total"];
        }
    }
    if (isset($pdff)) {
        $pdfName=$pdff["name"];
        $pdfFullName=$filepath.$pdfName;
        if (move_uploaded_file($pdff["tmp_name"], $pdfFullName)===false) { echoJSDoc("error", "Error al cargar archivo pdf", null, $_POST+["action"=>"temporales", "pdf"=>$pdff["name"]], "error"); return; }
        chmod($pdfFullName,0666);
        $pdfId=$tmpObj->ingresar($pdfName);
        if(!isset($pdfId)) { echoJSDoc("error", "Error al registrar archivo pdf", null, $_POST+["action"=>"temporales", "pdf"=>$pdff["name"]], "error"); return; }
    }
    $result=["result"=>"exito","message"=>"Carga de archivos exitosa"];
    if (isset($xmlId)) $result["xmlId"]=$xmlId;
    if (isset($pdfId)) $result["pdfId"]=$pdfId;
    if (isset($xmlData)) {
        if (isset($xmlData["folio"][0])) $result["xmlFolio"]=$xmlData["folio"];
        if (isset($xmlData["serie"][0])) $result["xmlSerie"]=$xmlData["serie"];
        if (isset($xmlData["uuid"][0])) $result["xmlUUID"]=$xmlData["uuid"];
        if (isset($xmlData["rfcemisor"][0])) $result["xmlRFCEmisor"]=$xmlData["rfcemisor"];
        if (isset($xmlData["fecha"][0])) $result["xmlFecha"]=$xmlData["fecha"];
        if (isset($xmlData["total"]) && $xmlData["total"]>0) $result["xmlTotal"]=$xmlData["total"];
        if (isset($xmlData["error"][0])) {
            $result["result"]="error";
            $result["message"]=$xmlData["error"];
            $result["cfdiError"]=$xmlData["error"];
        }
        if (isset($xmlData["stack"][0])) $result["cfdiStack"]=$xmlData["stack"];
        if (isset($xmlData["log"][0])) $result["cfdiLog"]=$xmlData["log"];
    }
    echo json_encode($result);
}
function getCFDIData($absolutePath, $localName, $conValidacion=true, $prefix="",$concepto=null) { // , $prefix="CC"
    $GLOBALS["doNormalizeUtf8Chars"]=1;
    require_once "clases/CFDI.php";
    $cfdistk="";
    $cfdilog="";
    $enough=true;
    $response=["path"=>$absolutePath,"name"=>$localName,"stack"=>"","log"=>""];
    $cfdiObj=CFDI::newInstanceByFileName($absolutePath,$localName,$response["error"],$response["stack"],$enough,$response["log"]);
    if ($cfdiObj!==null) {
        if ($conValidacion) {
            $modifiers=[];
            if (isset($concepto[0])) $modifiers["tipoconcepto"]=$concepto;
            $cfdiObj->validaReposicion($modifiers);
        } else {
            $receptor=$cfdiObj->get("receptor");
            if (isset($receptor["@rfc"])) {
                $receptorNombre=$receptor["@nombre"]??null;
                $err=$cfdiObj->validaCorporativo($receptor);
                if (isset($err[0])) {
                    if (!isset($response["error"][0])) $response["error"]="";
                    $response["error"] .= "<P>$err</P>";
                }
            }
        }
        $response["uuid"]=$cfdiObj->get("uuid");
        $response["folio"]=$cfdiObj->get("folio");
        $response["serie"]=$cfdiObj->get("serie");
        $response["tipocomprobante"]=$cfdiObj->get("tipo_comprobante");
        $response["total"]=$cfdiObj->get("total");
        $emisor=$cfdiObj->get("emisor");
        $response["rfcemisor"]=mb_convert_encoding($emisor["@rfc"], 'UTF-8', mb_list_encodings());
        $response["fecha"]=$cfdiObj->get("fecha");

        $alias=$cfdiObj->cache["aliasGrupo"]??"";
        if (isset($alias[0])) {
            if (empty($response["folio"])) $fileSuffix = $response["uuid"];
            else $fileSuffix = $response["folio"];
            if (isset($fileSuffix[10])) $fileSuffix = substr($fileSuffix, -10);
            // if (!isset($prefix[0])) $prefix="CC";
            if (isset($prefix[0])) $prefix.="_";
            else $prefix=""; // ambiguo pero valida si se recibe valor de null o alguno donde $prefix[0] no exista.
            //$response["filename"]=$prefix."_".$response["rfcemisor"]."_".$fileSuffix;
            $response["gpoId"]=$cfdiObj->cache["idGrupo"];
            $response["filename"]=$prefix.$alias."_".$fileSuffix;
        }
    } else {
        $response["error"]="<p>No fue posible acceder al contenido del archivo XML</p>";
        doclog("No fue posible acceder al contenido del archivo XML","cajachica",$response);
    }
    return $response;
}
function getInvalidFileMessage($file, $type=null) {
    if (empty($file)) return "No existe el archivo";
    if (!isset($file["size"])||!isset($file["type"])||!isset($file["name"])||!isset($file["tmp_name"])) return ("Información de archivo incompleta, intente nuevamente");
    if (empty($file["size"])) return ("Archivo Vacío");
    if (empty($file["tmp_name"])) return ("Archivo sin referencia");
    if (!empty($file["error"]) && $file["error"]!==UPLOAD_ERR_OK) {
        switch($file["error"]) {
            case UPLOAD_ERR_INI_SIZE: return ("El tamaño del archivo no debe exceder ".ini_get('upload_max_filesize')." MiB");
            case UPLOAD_ERR_FORM_SIZE: return ("El archivo excede el tamaño máximo que soporta el navegador");
            case UPLOAD_ERR_PARTIAL: return ("Descarga incompleta del archivo");
            case UPLOAD_ERR_NO_FILE: return ("No se seleccionó ningún archivo");
            case UPLOAD_ERR_NO_TMP_DIR: return ("La descarga de archivos está deshabilitada");
            case UPLOAD_ERR_CANT_WRITE: return ("El archivo no pudo guardarse en el servidor");
            case UPLOAD_ERR_EXTENSION: return ("Temporalmente el portal no soporta la descarga de archivos por extensión");
            default: return ("Error desconocido durante la descarga del archivo");
        }
    }
    if (empty($file["type"])) return ("Formato de archivo desconocido");
    if (!empty($type) && $file["type"]!==$type) return ("Formato de archivo '$file[type]' incorrecto, se espera '$type'");
    return "";
}
