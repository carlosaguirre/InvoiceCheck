<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
//$esAdmin = validaPerfil("Administrador");
//$esSistemas = validaPerfil("Sistemas")||$_esAdministrador;
//$esDesarrollo=in_array(getUser()->nombre, ["admin","SISTEMAS"]);
$esAvance = validaPerfil("Avance")||$_esSistemas;
$esCompras = $_esCompras; // validaPerfil("Compras");
$esAltaFacturas = validaPerfil("Alta Facturas");
$esProveedor = $_esProveedor; // validaPerfil("Proveedor");
$esGestor = validaPerfil("Gestor");
$esRevision = validaPerfil("Revision");
$esRechazante = validaPerfil("Rechaza Aceptadas");
$esBorraDoc = validaPerfil("Elimina Documentos")||$_esSistemas;
$esBloqueaEA = (validaPerfil("Bloquea Entrada Almacen")||$_esSistemas);
$esVCFDI = $_esSistemas||$esGestor||$esRevision||validaPerfil("VerificaSAT");
$tieneSolicitudes = validaPerfil("Solicita Pagos")||validaPerfil("Autoriza Pagos")||validaPerfil("Realiza Pagos")||validaPerfil("Gestiona Pagos")||validaPerfil("Consulta Solicitudes")||$_esSistemas;

require_once "clases/Facturas.php";
$prvRazSocOpt = $_SESSION['prvRazSocOpt']??[];
$prvRFCOpt = $_SESSION['prvRFCOpt']??[];
//clog2("prvRazSocOpt:\n".arr2str($prvRazSocOpt));

$consultaProc = consultaValida("Procesar");
$modificaProc = modificacionValida("Procesar");
$delPendiente = modificacionValida("DelPendiente");

if (!isset($invObj)) {
    $invObj = new Facturas();
    $invObj->rows_per_page = 0;
}
$logScript="";
$sysPath=$_SERVER["DOCUMENT_ROOT"];
//clog2("POST: \n".arr2str($_POST));
if (!empty($_POST["command"])) {
    $command = $_POST["command"];
    //clog2("POST[COMMAND] = $command");
}
$title="OK";
if (isset($command) && substr($command, 0, 4)==="KILL") {
    if (!isset($basePath)) {
        $basePath = "";
        if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $basePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
        else if (!empty($_SERVER['DOCUMENT_ROOT'])) $basePath = $_SERVER['DOCUMENT_ROOT'];
    }
    $logMsg=["CMD:KILL. BASE PATH:$basePath"];
    //clog2("CMD:KILL. BASE PATH:$basePath"); // C:/Apache24/htdocs/invoice/
    if (!file_exists($basePath."archivos/borrados/")) mkdir($basePath."archivos/borrados/");
    $invObj->addOrder("codigoProveedor");
    $factId = substr($command, 4);
    $factData = $invObj->getData("id='$factId'",0,"codigoProveedor,ciclo,folio,remision,uuid,tipoComprobante,ubicacion,nombreInterno,nombreInternoPDF,statusn,ea");
    global $query,$prcObj;
    $factQuery = str_replace("'", "", $query);
    if (isset($factData[0])) {
        $factData=$factData[0];
        $logMsg[]="READY TO KILL ID $factId : ".json_encode($factData);
        //clog2("READY TO KILL ID $factId : ".json_encode($factData));
        $folio=$factData["folio"];
        $remision=$factData["remision"];
        $codigo=$factData["codigoProveedor"];
        $tc=strtolower($factData["tipoComprobante"]);
        $sttn=$factData["statusn"];
        $esTemporal=is_null($sttn);
        if ($esTemporal) $sttn=-1;
        else $sttn=+$sttn;
        $estaRechazado=!$esTemporal&&$invObj->estaRechazado($sttn);
        $esPendiente=(!$esTemporal && $sttn===Facturas::STATUS_PENDIENTE);
        if ($esTemporal && !$_esDesarrollo) {
            $errorTitle="Error al Eliminar";
            $message = "No tiene permisos para eliminar esta factura.";
            $errorMessage="<p>$message</p>";
            $title = $errorTitle;
        } else if (!$esTemporal && !$estaRechazado) {
            if ($tc==="p") {
                $errorTitle="Error al Eliminar";
                $message = "No puede eliminar un comprobante de pago que no haya sido Rechazado previamente.";
                $errorMessage="<p>$message</p>";
                $title = $errorTitle;
            } else if ($invObj->estaProgPago($sttn)||$invObj->estaPagado($sttn)||$invObj->estaRecPago($sttn)) {
                // Cuando se detecte status de factura pagada, cancelar borrado y mostrar alerta que no se puede borrar. Al borrar una factura ya respaldada hay que mandar correo a Julieta informando que la factura fue borrada, para reducir el riesgo de que se vaya a pago simultaneamente. Averiguar si hay q mandar ese correo a alguien mas.
                $errorTitle="Error al Eliminar";
                $message = "No puede eliminar una factura pagada.";
                $errorMessage="<p>$message</p>";
                $title = $errorTitle;
            } else if ($invObj->estaContrarrecibo($sttn)) {
                $errorTitle="Error al Eliminar";
                $message = "No puede eliminar una factura que ya est&aacute; en un contra-recibo. Primero elimine la factura del contra-recibo.";
                $errorMessage="<p>$message</p>";
                $title = $errorTitle;
            } else if ($invObj->tieneSolicitud($factId)) {
                $errorTitle="Error al Eliminar";
                $message = "No puede eliminar una factura que ya est&aacute; en una solicitud, debe solicitar la cancelación de la factura";
                $errorMessage="<p>$message</p>";
                $title=$errorTitle;
            }
        }
        if (!isset($errorMessage[0])) {
            if ($_esSistemas || ($esPendiente && getUser()->nombre!=="logcorepack")) {
                // ARCHIVOS
                $xmlBorrado=false;
                $pdfBorrado=false;
                $path=$factData["ubicacion"];
                $xmlf=$factData["nombreInterno"].".xml";
                if (isset($xmlf[0])&&file_exists($path.$xmlf)) {
                    if (rename($basePath.$path.$xmlf, $basePath."archivos/borrados/".$xmlf)) {
                        $xmlBorrado=true;
                        $logMsg[]="DELETED XML FILE: $path$xmlf";
                        //clog2("DELETED XML FILE: $path$xmlf");
                    } else {
                        $logMsg[]="FAILED TO DELETE XML FILE: $path$xmlf";
                        //clog2("FAILED TO DELETE XML FILE: $path$xmlf");
                    }
                } else {
                    $logMsg[]="XML FILE $xmlf DOES NOT EXIST";
                    //clog2("XML FILE $xmlf DOES NOT EXIST");
                }
                $pdff=$factData["nombreInternoPDF"].".pdf";
                if (isset($pdff[0])&&file_exists($path.$pdff)) {
                    if (rename($basePath.$path.$pdff, $basePath."archivos/borrados/".$pdff)) {
                        $pdfBorrado=true;
                        $logMsg[]="DELETED PDF FILE: $path$pdff";
                        //clog2("DELETED PDF FILE: $path$pdff");
                    } else {
                        $logMsg[]="FAILED TO DELETE PDF FILE: $path$pdff";
                        //clog2("FAILED TO DELETE PDF FILE: $path$pdff");
                    }
                } else {
                    $logMsg[]="PDF FILE $pdff DOES NOT EXIST";
                    //clog2("PDF FILE $pdff DOES NOT EXIST");
                }
                DBi::autocommit(FALSE);
                // CONCEPTOS y FACTURA
                $esPago=false; $esNota=false; $esFactura=false;
                switch($tc) {
                    case "i": case "ingreso": $esFactura=true;
                        $resultTitle="Factura";$message="La factura";$gen="a";break;
                    case "e": case "egreso": $esNota=true;
                        $resultTitle="Nota";$message="La nota";$gen="a";break;
                    case "p": $esPago=true;
                        $resultTitle="Pago";$message="El pago";$gen="o";break;
                    default: $resultTitle="Comprobante";$message="El comprobante";$gen="o";
                }
                if ($esFactura || $esNota) {
                    global $query;
                    require_once "clases/Conceptos.php";
                    $cptObj = new Conceptos();
                    if ($cptObj->deleteRecord(["idFactura"=>$factId])&&$invObj->deleteRecord(["id"=>$factId])) {
                        $resultTitle.=" Eliminad$gen";
                        if ($estaRechazado) {
                            $message.=" previamente rechazad$gen, ha sido eliminad$gen definitivamente.";
                        }
                        else if ($invObj->estaExportado($sttn)||$invObj->estaRespaldado($sttn)) {
                            $message.=" ha sido eliminad$gen del portal, pero debe eliminarse tambi&eacute;n en Avance.";
                        } else {
                            $message.=" ha sido eliminad$gen definitivamente.";
                        }
                        $title = $resultTitle;
                        $resultMessage = "<p>$message</p>";
                        if (!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
                        if (!$prcObj->cambioFactura($factId, "ELIMINADO", getUser()->nombre, false, "$message")) {
                            doclog("Falló registro de proceso al eliminar factura","error",["invId"=>$factId,"query"=>$query,"errors"=>DBi::$errors,"log"=>$invObj->log]);
                        }
                        $logData=["invId"=>$factId,"message"=>$message];
                        if ($xmlBorrado) $logData["xml"]="archivos/borrados/".$xmlf;
                        if ($pdfBorrado) $logData["pdf"]="archivos/borrados/".$pdff;
                        if (isset($logMsg[0])) $logData["log"]=$logMsg;
                        doclog("CFDI Eliminado","cfdi",$logData);
                        DBi::commit();
                    } else {
                        // ToDo: Corroborar si aun existe en Conceptos o en Factura.
                        //   - Si ya no existe marcar satisfactorio y que ya habian sido borradas previamente
                        //   - Si aun existe en alguna marcar error y rollback
                        doclog("Error al eliminar factura y/o conceptos","error",["invId"=>$factId,"query"=>$query,"errors"=>DBi::$errors,"log"=>$logMsg]);
                        $logMsg=[]; // Para no guardar doclog(error) dos veces
                        $errorTitle="Error al Eliminar";
                        $message = "Error al eliminar ".strtolower($message).".";
                        $errorMessage="<p>$message</p>";
                        $title = $errorTitle;
                        DBi::rollback();
                    }
                } else if ($esPago) {
                    // Las facturas relacionadas a un pago ya deberían tener statusReciboPago en -1 cuando fue rechazado, al eliminarlo no tiene porque haber mas cambios
                    doclog("Falta agregar codigo para eliminar complementos de pago","error",["invId"=>$factId,"log"=>$logMsg]);
                    $logMsg=[]; // Para no guardar doclog(error) dos veces
                    $errorTitle="Error al Eliminar";
                    $message = "No fue posible eliminar el complemento de pago";
                    $errorMessage="<p>$message</p>";
                    $title=$errorTitle;
                    DBi::rollback();
                }
                //clog2("LOGS:\n".$invObj->log."\n".$cptObj->log);
                DBi::autocommit(TRUE);
            } else {
                $errorTitle="Error al Eliminar";
                if (getUser()->nombre=="logcorepack") {
                    doclog("DELETE FAILED","logcorepack",$factData);
                    doclog("DELETE FAILED","cfdi",$factData);
                   $message = "Se le ha revocado el permiso para borrar facturas";
                }
                else $message = "No tiene permisos para eliminar";
                $errorMessage="<p>$message</p>";
                $title = $errorTitle;
                //$logScript = "console.log('POST: ".str_replace("&"," & ",http_build_query($_POST))."'); console.log('QUERY: $factQuery'); console.log('FACTDATA: ".str_replace("&"," & ",http_build_query($factData))."'); console.log('REVIEW: sttn=$sttn (".gettype($sttn).") & STATUS_PENDIENTE=".Facturas::STATUS_PENDIENTE." (".gettype(Facturas::STATUS_PENDIENTE).") & esPendiente=".($esPendiente?"TRUE":"FALSE")." & strictEquals=".($sttn===Facturas::STATUS_PENDIENTE?"TRUE":"FALSE")." & weakEquals=".($sttn==Facturas::STATUS_PENDIENTE?"TRUE":"FALSE")."');";
            }
            if (isset($errorMessage[0])) {
                $logMsg[]="KILL ERROR $errorTitle : $errorMessage";
                //clog2("KILL ERROR $errorTitle : $errorMessage");
            } else if (isset($resultMessage[0])) {
                $logMsg=[];
                //clog2("KILL SUCCESS $resultTitle : $resultMessage");
            } else {
                $logMsg[]="KILL NOTHING";
                //clog2("KILL NOTHING");
            }
        } else {
            $logMsg[]="TIENE ERROR '$errorTitle' : '$errorMessage'";
            //clog2("TIENE ERROR '$errorTitle' : '$errorMessage'");
        }
    } else {
        $logMsg[]="NOT FOUND TO KILL $factId";
        //clog2("NOT FOUND TO KILL $factId");
    }
    if (isset($logMsg[1])) {
        $txt = array_shift($logMsg);
        doclog($txt,"error",["log"=>$logMsg]);
    }
} // END command KILL
if (isset($command) && substr($command, 0, 3)=="DEL") {
    $factId = substr($command, 3);
    //clog2("# # DEL (INI) ".$factId);
    list($currTC,$currStatus,$currStatusN) = explode("|",$invObj->getValue("id", $factId, "tipoComprobante,status,statusn"));
    $nextStatusN = $currStatusN | Facturas::statusToStatusN("Rechazado");
    $nextStatus = $invObj->statusnToStatus($nextStatusN);
    if ($currStatus == $nextStatus) {
        $message = "No es posible rechazar el comprobante.";
        $title = "ERROR";
    } else if ($currStatus!=="Pendiente" && $currStatus!=="Aceptado" && !$_esSistemas && !$esRechazante) {
        $message = "No tiene permiso para rechazar el comprobante. Consulte a su administrador.";
        $title = "ERROR";
    } else {
        $deletingInvoiceFieldArray = ["id"=>$factId, "status"=>$nextStatus, "statusn"=>$nextStatusN]; // TODO: Confirmar que nombreInterno no se ocupa para distinguir si una factura esta rechazada. // "nombreInterno"=>NULL
        $delResult=$invObj->saveRecord($deletingInvoiceFieldArray);
        if ($delResult) {
            global $cpyObj,$dpyObj,$prcObj;
            if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj = new CPagos(); }
            if (!isset($dpyObj)) { require_once "clases/DPagos.php"; $dpyObj = new DPagos(); }
            $qrys=[];
            $cpyData=$cpyObj->getData("idCPago=$factId");
            $qrys[]=$query;
            $cpyIds=array_column($cpyData, "id");
            $dpyData=$dpyObj->getData("idPPago in (".implode(",", $cpyIds).")");
            $qrys[]=$query;
            $cpyInvIds=array_column($dpyData, "idFactura");
            $invDataDR=$invObj->getData("id".(isset($cpyInvIds[1])?" in (".implode(",",$cpyInvIds).")":"=".$cpyInvIds[0])." and idReciboPago=$factId",0,"id");
            $ddrInvIds=array_column($invDataDR,"id");
            if (isset($ddrInvIds[0])) {
                //$invRes=$invObj->saveRecord(["id"=>$ddrInvIds,"idReciboPago"=>$factId,"fechaReciboPago"=>null,"saldoReciboPago"=>null,"statusReciboPago"=>-1],0,["idReciboPago"]);
                if (!isset($xPymIRPFldArrs)) $xPymIRPFldArrs=[];
                $xPymIRPFldArrs[]=["id"=>$ddrInvIds,"idReciboPago"=>$factId,"fechaReciboPago"=>null,"saldoReciboPago"=>null,"statusReciboPago"=>-1];
            }
            $qrys[]=$query;
            if (!$invRes) doclog("Falló actualizar Facturas con Recibo Pago Rechazado","error",["queries"=>$qrys,"errors"=>DBi::$errors,"log"=>$invObj->log]);
            if (!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
            $prcObj->cambioFactura($factId, $nextStatus, getUser()->nombre, false, "reportefacturaX:$currStatus=>$nextStatus");
            $message = "Comprobante Rechazado";
            $title = "EXITO";
        } else {
            doclog("Falló rechazar Comprobante de Pago","error",["query"=>$query,"errors"=>DBi::$errors,"log"=>$invObj->log]);
            $message = "Comprobante NO Rechazado";
            $title = "ERROR";
            DBi::rollback();
        }
    }
} // END command DEL
if (isset($command) && substr($command, 0, 2)=="GO") {
    $factId = substr($command, 2);
    //clog2("# # GO (INI)".$factId."\nPOST: ".json_encode($_POST)."\nFILES: ".json_encode($_FILES));
    DBi::autocommit(FALSE);
    $invObj->addOrder("codigoProveedor");
    $factData = $invObj->getData("id=$factId",0,"upper(left(tipoComprobante,1)) as tipoComprobante,upper(pedido) as currPedido,upper(remision) as currRemision,status currStatus,statusn currStatusN,nombreInterno archivoXML,nombreInternoPDF archivoPDF,ubicacion,ea,codigoProveedor,date(fechaFactura) fecha");
    //$factPipeList = $invObj->getValue("id", $factId, "upper(left(tipoComprobante,1)),upper(pedido),status,statusn,nombreInterno,nombreInternoPDF,ubicacion");
    if (empty($factData)) { //$factPipeList)) {
        $success = false;
        $errorTxt = "No se pudieron obtener los datos, intente nuevamente.";
    } else {
        extract($factData[0]); // converts array keys into variables
        //flog("TEST: factId:$factId, tipoComprobante=$tipoComprobante, currPedido=$currPedido, currStatus=$currStatus, currStatusN=$currStatusN, archivoXML=$archivoXML, archivoPDF=$archivoPDF, ubicacion=$ubicacion, ea=$ea, codigoProveedor=$codigoProveedor,fecha=$fecha");
        //clog2("DATA: factId:$factId, tipoComprobante=$tipoComprobante, currPedido=$currPedido, currStatus=$currStatus, currStatusN=$currStatusN, archivoXML=$archivoXML, archivoPDF=$archivoPDF, ubicacion=$ubicacion, ea=$ea, codigoProveedor=$codigoProveedor,fecha=$fecha");
        if (isset($_FILES["eafile"]["tmp_name"][0])) {
            $eacp=trim(str_replace("-", "", $codigoProveedor));
            $eafolio=substr($archivoXML, -10);
            $eafecha=substr(trim(str_replace("-","", $fecha)),2,6);
            $eaname="EA_{$eacp}_{$eafolio}_{$eafecha}";
        }
        $currStatusN=+$currStatusN;
        $nextStatusN=$currStatusN|Facturas::actionToStatusN("Aceptado");
        $nextStatus = $invObj->nextStatus($currStatus,"Aceptar");
        if (!empty($_POST["proveedor"])) $prvCode = $_POST["proveedor"];
        else if ($_esProveedor)           $prvCode = getUser()->nombre;
        $fNumPedido = strtoupper($_POST["f_numpedido"]??"");
        $fNumRemision = strtoupper($_POST["f_numremision"]??"");
        if (strlen($currStatus)==0) {
            flog("ERROR: No se reconoce el status actual del comprobante (id $factId)\n".json_encode($factData));//$factPipeList);
            $success = false;
            $errorTxt = "No se reconoce el status actual del comprobante\n";
        } else if (strlen($nextStatus)==0) {
            flog("ERROR: No se reconoce el nuevo status del comprobante (id $factId)\n".json_encode($factData));
            $success = false;
            $errorTxt = "No se reconoce el nuevo status del comprobante\n";
        } else if (preg_match('/\s/',$fNumPedido)) {
            flog("ERROR: El pedido no puede tener espacios (factura id $factId)\n".json_encode($factData));
            $success = false;
            $errorTxt = "El pedido no puede tener espacios\n";
        } else if ($currPedido===$fNumPedido && $currStatusN===$nextStatusN && !isset($eaname[0]) && $currRemision===$fNumRemision) {
            //clog2(" A $nextStatus");
            if ($nextStatus==="Aceptado")
                $success = true;
            else if ($esAvance && $nextStatusN>0 && $nextStatusN<Facturas::actionToStatusN("Rechazado"))
                $success = true;
            else {
                $success = false;
                //NO MOSTRAR MENSAJE DE ERROR
                $errorTxt = "El pedido no puede tener espacios...\n"; 
            }
        } else {
            $processingInvoiceFieldArray = ["id"=>$factId];
            doclog("READY1","test",["arr"=>$processingInvoiceFieldArray]);
            if ($tipoComprobante==="I"||$tipoComprobante==="E") {
                if (isset($fNumPedido) && is_string($fNumPedido) && strlen($fNumPedido)>20) {
                    $fNumPedido = substr($fNumPedido, 20);
                }
                if ($currPedido!==$fNumPedido) {
                    $processingInvoiceFieldArray["pedido"]=$fNumPedido;
                }
                if (isset($fNumRemision) && is_string($fNumRemision) && strlen($fNumRemision)>20) {
                    $fNumRemision = substr($fNumRemision,20);
                }
                if ($currRemision!==$fNumRemision) {
                    $processingInvoiceFieldArray["remision"]=$fNumRemision;
                }
                doclog("READY2","test",["msg"=>"I o E:pedido,remision","arr"=>$processingInvoiceFieldArray]);
            } else if ($tipoComprobante==="P") { // $estaAceptado
                global $cpyObj, $dpyObj;
                if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
                if (!isset($dpyObj)) { require_once "clases/DPagos.php"; $dpyObj=new DPagos(); }
                $qrys=[];
                $cpyData=$cpyObj->getData("idCPago=$factId");
                $qrys[]=$query;
                $cpyIds=array_column($cpyData, "id");
                $dpyData=$dpyObj->getData("idPPago in (".implode(",", $cpyIds).")");
                $qrys[]=$query;
                $cpyInvIds=array_column($dpyData, "idFactura");
                // ToDo: Checar si el complemento está cancelado o si la factura está cancelada o si ya tiene asignado otro complemento de pago para pedir confirmacion
                if (!isset($xPymIRPFldArrs)) $xPymIRPFldArrs=[];
                $xPymIRPFldArrs[]=["id"=>$cpyInvIds,"idReciboPago"=>$factId,"statusReciboPago"=>1];
                doclog("READY3","test",["msg"=>"P","arr"=>$processingInvoiceFieldArray,"arrp"=>$xPymIRPFldArrs]);
            }
            if ($currStatusN!==$nextStatusN) {
                $processingInvoiceFieldArray["fechaAprobacion"]=new DBExpression("NOW()");
                // RESPALDAR //
                if (false) {
                    ini_set("memory_limit","1024M");
                    set_error_handler("ftpErrorHandler");
                    if (!empty($ubicacion)) {
                        if (!isset($ftpObj)) {
                            require_once "clases/FTP.php";
                            $ftpObj = MiFTP::newInstanceGlama();
                        }
                        if (!isset($lookoutFilePath)) {
                            $lookoutFilePath = "";
                            if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
                            else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
                        }
                        list($rutaBase,$alias,$anio,$mes,$nada)=explode("/",$ubicacion);
                        if (!isset($rutaAvance)&&!empty($alias)) {
                            $rutaAvance = $ftp_supportPath.$alias."/".($tipoComprobante==="P"?"T":"")."PUBLICO/";
                        }
                        $pdfUploadSuccess=true;
                        if (!empty($archivoPDF)) {
                            $pdfFileName = $archivoPDF.".pdf";
                            $localFilePath = $lookoutFilePath.$ubicacion.$pdfFileName;
                            try {
                                $ftpObj->cargarArchivoBinario($rutaAvance, $pdfFileName, $localFilePath);
                                $fileStatusn=Facturas::actionToStatusN("Respaldo");
                            } catch (Exception $e) {
                                $pdfUploadSuccess=false;
                            }
                        }
                        if (isset($archivoXML)) {
                            $xmlFileName = $archivoXML.".xml";
                            $localFilePath = $lookoutFilePath.$ubicacion.$xmlFileName;
                            try {
                                $ftpObj->cargarArchivoAscii($rutaAvance, $xmlFileName, $localFilePath);
                                $fileStatusn=Facturas::actionToStatusN("Respaldo");
                                if ($pdfUploadSuccess) {
                                    $nextStatus = $invObj->nextStatus("Aceptado","Respaldar");
                                }
                            } catch (Exception $e) {
                                //clog2("FALLO ENVIO DE FTP. RREMOTA='$rutaAvance', XML='$xmlFileName', NLOCAL='$localFilePath', Exception='".$e->getMessage()."'");
                            }
                        }
                    }
                    restore_error_handler();
                }
                $processingInvoiceFieldArray["status"]=$nextStatus;
                $processingInvoiceFieldArray["statusn"]=Facturas::statusToStatusN($nextStatus);
                doclog("READY4","test",["msg"=>"fecha y status","arr"=>$processingInvoiceFieldArray]);
            }
            if (isset($eaname[0])) {
                $eafullpath=$sysPath.$ubicacion.$eaname.".pdf";
                if (file_exists($eafullpath)) {
                    rename($eafullpath, $sysPath.$ubicacion.$eaname.date("_YmdHis", filemtime($eafullpath)).".pdf");
                    sleep(3);
                }
                if(move_uploaded_file($_FILES["eafile"]["tmp_name"], $eafullpath)!==false)
                    $processingInvoiceFieldArray["ea"]="1";
                doclog("READY5","test",["msg"=>"ea","arr"=>$processingInvoiceFieldArray]);
            }
            if (isset(array_keys($processingInvoiceFieldArray)[1])) {
                $success = ($invObj->saveRecord($processingInvoiceFieldArray)||empty(DBi::$errno));
                global $query;
                if (!$success) {
                    $errorTxt="Error al guardar factura\n";
                    doclog($errorTxt,"error",["query"=>$query,"errors"=>DBi::$errors,"log"=>$invObj->log]);
                } else
                    doclog("READY6","test",["msg"=>"saved","arr"=>$processingInvoiceFieldArray,"query"=>$query]);
            } else {
                $success=False;
                if ($currStatusN>0) {
                    $errorTxt="El comprobante ya estaba aceptado, no necesita aceptarlo de nuevo";

                } else $errorTxt="No se identifica ningún cambio a guardar en el comprobante";
            }
        }
    }
    if ($success&&$tipoComprobante==="P") { // $estaAceptado
        // ToDo: Aqui agregar los cambios en las facturas
        // Los datos del cfdi en las tablas CPagos y DPagos se debieron capturar durante la alta de cfdi 
        // Para cada factura en el complemento de pago:
        // comparar fecha de recibo de pago, si no tiene se agrega, si la fecha de este recibo es posterior a la guardada se cambian id,fecha y saldo
        // 
        /* // Ya se hizo antes, no se necesita repetir
        if (isset($dpyData)) {
            foreach ($dpyData as $dpyIdx => $dpyItem) {
                $cpyItem=$cpyList[$dpyItem["idPPago"]];
                $doctoRelId=$dpyItem["idFactura"];
                $dpyLastOrder=$dpyObj->orderlist;
                $dpyObj->clearOrder();
                $dpyObj->addOrder("numParcialidad","desc");
                $dpyPerInvData=$dpyObj->getData("idFactura=$doctoRelId");
                if (isset($dpyPerInvData[0]["idPPago"]) && $dpyPerInvData[0]["idPPago"]==$dpyItem["idPPago"])
                    $invObj->saveRecord(["id"=>$doctoRelId,"idReciboPago"=>$cpyItem["idCPago"],"fechaReciboPago"=>$cpyItem["fechaPago"],"saldoReciboPago"=>$dpyItem["saldoInsoluto"],"statusReciboPago"=>1]);
            }
        }
        */
    } else if ($success&&($tipoComprobante==="I"||$tipoComprobante==="E")) {
        $arts = $_POST["f_articulo"]??[];
        $artIds = array_keys($arts);
        //clog2("POST: ".print_r($_POST, true));
        require_once "clases/Conceptos.php";
        $cptObj = new Conceptos();
        $ccValArr = [];
        $fldCant = new DBName("cantidad");
        $fldPUni = new DBName("precioUnitario");
        $fldImpo = new DBName("importe");
        global $prvObj;
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj=new Proveedores();
        }
        $prvData=$prvObj->getData("codigo='$codigoProveedor'",0,"esServicio");
        $esServicio=($prvData[0]["esServicio"]??0)>0;
        if ($esServicio) {
            global $srvObj;
            if (!isset($srvObj)) {
                require_once "clases/Servicios.php";
                $srvObj=new Servicios();
            }
        }
        
        foreach ($artIds as $cId) {
            if (preg_match('/\s/',$arts[$cId])) {
                $success = false;
                $errorTxt = "eL c&oacute;digo de concepto no debe incluir espacios\n";
                break;
            }
            $codigoArticulo=strtoupper($arts[$cId]);
            $ccValArr[] = [$cId, $factId, $codigoArticulo, $fldCant, $fldPUni, $fldImpo];
            if ($esServicio) {
                $cptData=$cptObj->getData("id=$cId",0,"claveUnidad,claveProdServ");
                if (isset($cptData[0])) $cptData=$cptData[0];
                $claveUnidad=$cptData["claveUnidad"]??"";
                $claveProdServ=$cptData["claveProdServ"]??"";
                if (isset($claveUnidad[0]) && isset($claveProdServ[0]) && !$srvObj->exists("claveUnidad='$claveUnidad' AND claveProdServ='$claveProdServ' AND codigoProveedor='$codigoProveedor' AND codigoArticulo='$codigoArticulo'")) {
                    // Revisar tabla de Codigos de Servicio, si el codigo no existe agregarlo
                    $srvObj->saveRecord(["claveUnidad"=>"$claveUnidad", "claveProdServ"=>"$claveProdServ", "codigoProveedor"=>"$codigoProveedor", "codigoArticulo"=>"$codigoArticulo"]);
                }
            }
        }
        if ($success) {
            $success &= $cptObj->insertMultipleRecords (["id","idFactura","codigoArticulo","cantidad","precioUnitario","importe"], $ccValArr, "ON DUPLICATE KEY UPDATE codigoArticulo=VALUES(codigoArticulo)");
            if (!$success) {
                if (isset(DBi::$errors[0])) {
                    $errorTxt = "Error al insertar conceptos\n";
                    global $query;
                    doclog($errorTxt,"error",["query"=>$query,"errors"=>DBi::$errors,"log"=>$cptObj->log]);
                } else $success=true; // Si no hay errores, no se insertaron conceptos ni se actualizaron porque son los mismos
                // Con cualquier cambio en la factura se intentan guardar los conceptos y si algunos no cambiaron van a regresar falso porque no hubo cambios y hay que ignorarlos. Para eso se checa primero si hubo errores.
            }
        }
        if ($success && isset($factId) && isset($processingInvoiceFieldArray["statusn"])) {
            if (!isset($solObj)) {
                require_once "clases/SolicitudPago.php";
                $solObj = new SolicitudPago();
            }
            $solObj->updateStatus($factId, $processingInvoiceFieldArray["statusn"]);
        }
    }
    if ($success && $currStatusN!=$nextStatusN) {
        global $prcObj;
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj = new Proceso();
        }
        //if ($esFactura||$esEgreso)
        $success = $prcObj->cambioFactura($factId, "Aceptado", getUser()->nombre, false, "reportefactura:$currStatus=>$nextStatus");
        //else if ($esPago)
        // $success = $prcObj->alta("FechaMontoPago",$factId,"cpUpgrade",$detalle);
        if (!$success) $errorTxt="Error al registrar proceso\n";
    }
    if ($success) {
        global $prcObj;
        // ToDo: Envia xml y pdf al servidor de avance. Si se comprueba q los archivos se enviaron correctamente cambiar status.
        //        Opcion 1: Si el envío es satisfactorio y el tiempo menor a 1 minuto y existen facturas con status Aceptado, Contrarrecibo, RespSinExp, RespSinCX, mandar archivos de 1 o 2 facturas mas (Posiblemente esto requiere crear status temporales para marcar facturas en proceso de envío, y debe detectarse si el proceso falla para regresarlos al status original, vg Aceptado2Exp, Contrarrecibo2Exp, RespSinExp2Exp, RespSinCX2Exp.)
        //        Opcion 2: Cualquier solicitud de index evalua si existen facturas con status Aceptado, Contrarrecibo, RespSinExp, RespSinCX, y manda archivos de 1 factura (Posiblemente esto requiere crear status temporales para marcar facturas en proceso de envío, y debe detectarse si el proceso falla para regresarlos al status original, vg Aceptado2Exp, Contrarrecibo2Exp, RespSinExp2Exp, RespSinCX2Exp)
        //        Opcion 3: Adaptacion de Opcion 2 con status temporales. Cualquier solicitud de index busca facturas con status Aceptado, Contrarrecibo, RespSinExp, RespSinCX y selecciona una (guarda id de factura), luego busca facturas con status Aceptado2Exp, Contrarrecibo2Exp, RespSinExp2Exp o RespSinCX2Exp con fecha de modificacion mayor a 5 minutos atras y restaura status original (sin Exp al final). Posteriormente con el id guardado manda archivos y espera respuesta
        $logTextMessage = "##### LOGS #####\n ----------------\n # FACTURA #\n".$invObj->log.(empty($cptObj)?"":"\n ----------------\n # CONCEPTOS #\n".$cptObj->log).(!isset($prcObj)?"":"\n ----------------\n # PROCESO #\n".$prcObj->log);
        //clog2("EXITO: \n$logTextMessage");
        DBi::commit();
        $message = "El comprobante fue Aceptado";
        $title = "EXITO";
    } else {
        if (!isset($errorTxt)) $errorTxt="Error Desconocido...\n";
        $message = str_replace ( ["\"", "'", "\n"] , ["\\\"", "\\'", "\\n"] , $errorTxt );
        $errorTxt = " ##### LOGS #####\n ----------------\n # FACTURA #\n".$invObj->log.(empty($cptObj)?"":"\n ----------------\n # CONCEPTOS #\n".$cptObj->log).(!isset($prcObj)?"":"\n ----------------\n # PROCESO #\n".$prcObj->log);
        //clog2("ERRORES: \n$errorTxt");
        DBi::rollback();
        // $message = "No fue posible procesar la factura.";
        $title = "ERROR";
    }
    if ($tipoComprobante==="P" && isset($xPymIRPFldArrs)) {
        // Actualizando facturas relacionadas con pagos aunque el pago ya haya sido aceptado previamente. En este arreglo solo existen facturas cuyo comprobante de pago fue validado.
        $numXSaved=0;
        $numXCount=count($xPymIRPFldArrs);
        $invObj->log="\n// xxxxxxxxxxxxxx CPagos xxxxxxxxxxxxxx //\n";
        foreach ($xPymIRPFldArrs as $xIdx => $xVal) {
            $invRes=$invObj->saveRecord($xVal); // $invRes=$invObj->saveRecord($xVal,0,["idReciboPago"]);
            $qrys[]=$query;
            if ($invRes) $numXSaved++;
            else {
                $iipData=$invObj->getData("id in (".implode(",", $xVal["id"]).")",0,"id,idReciboPago,statusReciboPago");
                $qrys[]=$query;
                if (isset($iipData[0])) {
                    $iipData=$iipData[0];
                    if ($iipData["statusReciboPago"]>0) $invRes="ALREADY ACCEPTED";
                } else $invRes="NOT FOUND ".json_encode($xVal);
            }
            $xPymIRPFldArrs[$xIdx]["result"]=($invRes===true?"TRUE":($invRes===false?"FALSE":(is_scalar($invRes)?$invRes:json_encode($invRes))));
        }
        doclog("Actualizacion de facturas pagadas","cpagos",["arrs"=>$xPymIRPFldArrs,"queries"=>$qrys,"count"=>$numXCount,"saved"=>$numXSaved,"logs"=>$invObj->log]);
    }
    DBi::autocommit(TRUE);
    //clog2("# # GO (END)");
} // END command GO
if (isset($_REQUEST["pageno"])) $invObj->pageno = $_REQUEST["pageno"];
if (isset($_REQUEST["limit"]))  $invObj->rows_per_page = $_REQUEST["limit"];
$where = "";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        if ($value = $_REQUEST[$pvalue]) {
            $where .= (isset($where[0])?" AND ":"").$pvalue . " LIKE '%" . $value . "%'";
        }
    }
}
$soloUnaEmpresa=false;
if (!empty($_POST["grupo"])) {
    $where .= (isset($where[0])?" AND ":"")."rfcGrupo='$_POST[grupo]'";
    $soloUnaEmpresa=true;
} else {
    if ($_esProveedor) {
        $rfcGpoList = $invObj->getList("codigoProveedor", getUser()->nombre, "distinct rfcGrupo");
        $soloUnaEmpresa = (substr_count($rfcGpoList,"|")==0);
        $where .= (isset($where[0])?" AND ":"")."rfcGrupo in ('".str_replace("|", "','", $rfcGpoList)."')";
    } else { //  if ($_esCompras)
        $gpoRFCOpt=$_SESSION['gpoRFCOpt']??[];
        if (empty($gpoRFCOpt)) {
            if (!isset($ugObj)) {
                require_once "clases/Usuarios_grupo.php";
                $ugObj=new Usuarios_Grupo();
            }
            $ugObj->rows_per_page=0;
            $rfcEmpresas=$ugObj->getGroupRFC(getUser(), "Compras", "vista");
        } else {
            $rfcEmpresas = array_keys($gpoRFCOpt);
        }
        if (!empty($rfcEmpresas)) {
            $esCompras=true;
            $soloUnaEmpresa = (count($rfcEmpresas)==1);
            $where.=(isset($where[0])?" AND ":"")."rfcGrupo in ('".implode("','",$rfcEmpresas)."')";
        }
    }
}
$soloUnProveedor=false;
if (!empty($_POST["proveedor"])) {
    $where .= (isset($where[0])?" AND ":"")."codigoProveedor='$_POST[proveedor]'";
    $soloUnProveedor=true;
} else if ($_esProveedor) {
    $where .= (isset($where[0])?" AND ":"")."codigoProveedor='".getUser()->nombre."'";
    $soloUnProveedor=true;
}
$ignoraFechas=false;
if (!empty($_POST["fechaInicio"])) {
    list($fDay, $fMon, $fYr) = sscanf($_POST["fechaInicio"], "%2d/%2d/%4d");
    $fechaIni = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
}
if (!empty($_POST["fechaFin"])) {
    list($fDay, $fMon, $fYr) = sscanf($_POST["fechaFin"], "%2d/%2d/%4d");
    $fechaFin = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
}
if (isset($fechaIni)) {
    if (isset($fechaFin)) $rangoFechaWhere=" BETWEEN '$fechaIni 00:00:00' AND '$fechaFin 23:59:59'";
    else $rangoFechaWhere=">'$fechaIni 00:00:00'";
} else if (isset($fechaFin)) {
    $rangoFechaWhere="<'$fechaFin 23:59:59'";
} else $ignoraFechas=true;
$status = $_POST["status"]??"";
//** StatusN=null corresponde a status Temporal: Significa que algun usuario inició Alta de Factura o Comprobante Fiscal, pero el proceso consta de dos pasos (1ro: Ingresar archivos y presionar botón VERIFICAR, 2do:Capturar pedido y codigos de articulos y presionar botón AGREGAR DOCUMENTO), y el usuario nunca presionó el segundo botón (AGREGAR DOCUMENTOS), por lo que la factura nunca fue registrada satisfactoriamente en el sistema. El registro con status temporal es informativo para el administrador del sistema y/o de la base de datos. No hay razón para que otros usuarios puedan verlo, ni siquiera los dueños o gerentes **// 
if (!$_esDesarrollo) $where .= (isset($where[0])?" AND ":"")."statusn is not null";
switch ($status) {
    case "Pendiente":
        $where .= (isset($where[0])?" AND ":"")."statusn&1=0 and statusn<128";
        break;
    case "Procesado": // Sin subir a Avance
        $where .= (isset($where[0])?" AND ":"")."statusn>0 and (statusn<=3 or (lower(left(tipoComprobante,1))='i' and statusn>=9 and statusn<=11))";
        break;
    case "Aceptado":
    case "NoPendiente":
        $where .= (isset($where[0])?" AND ":"")."statusn>0 AND statusn<16";
        break;
    case "CambioRango":
        if (!$ignoraFechas) {
            $ignoraFechas=true;
            $where .= (isset($where[0])?" AND ":"")."modifiedTime$rangoFechaWhere";
        }
        break;
    case "AltaRango":
        if (!$ignoraFechas) {
            $ignoraFechas=true;
            $where .= (isset($where[0])?" AND ":"")."fechaCaptura$rangoFechaWhere";
        }
        break;
    case "AltaAyer":
        $ignoraFechas=true;
        $where .= (isset($where[0])?" AND ":"")."fechaCaptura>=CURDATE()-INTERVAL 1 DAY";
        break;
    case "AltaAhora":
        $ignoraFechas=true;
        $where .= (isset($where[0])?" AND ":"")."fechaCaptura>=NOW()-INTERVAL 4  HOUR";
        break;
    case "AltaHoy":
        $ignoraFechas=true;
        $where .= (isset($where[0])?" AND ":"")."fechaCaptura>=CURDATE()";
        break;
    case "AltaMes":
        $ignoraFechas=true;
        $where .= (isset($where[0])?" AND ":"")."MONTH(fechaCaptura)=MONTH(CURDATE()) AND YEAR(fechaCaptura)=YEAR(CURDATE())";
        break;
    case "Rechazado":
    case "Rechazadas":
        $where .= (isset($where[0])?" AND ":"")."statusn>=128";
        break;
    case "SoloContrarrecibo":
        $where .= (isset($where[0])?" AND ":"")."statusn=3";
        break;
    case "Contrarrecibo":
        $where .= (isset($where[0])?" AND ":"")."statusn&2>0 and statusn<16 AND lower(left(tipoComprobante,1)) in ('i','e')";
        break;
    case "Pagado":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='i' AND statusn>=16 and statusn<128";
        break;
    case "SinPagar":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='i' AND statusn between 2 and 31 AND idReciboPago is NULL";
        break;
    case "SinCPago": // PPD Pagado sin comprobante de pago
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='i' AND statusn between 32 and 127 AND metodoDePago='PPD' AND (idReciboPago is NULL OR (saldoReciboPago IS NOT NULL AND saldoReciboPago>=1))";
        break;
    case "Exportado":
        $where .= (isset($where[0])?" AND ":"")."statusn&4>0 AND statusn<128";
        break;
    case "Avance":
        $where .= (isset($where[0])?" AND ":"")."statusn>=4 AND statusn<128";
        break;
    case "Respaldado":
        if ($_esSistemas) {
            $where .= (isset($where[0])?" AND ":"")."statusn&8>0 AND statusn<128";
        } else $where .= (isset($where[0])?" AND ":"")."statusn=-10";
        break;
    case "Pagos":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='p'";
        if (!$_esSistemas) $where .= " AND statusn>=0 and statusn<128";
        break;
    case "PagosAceptados":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='p' AND statusn&1=1 AND statusn<128";
        break;
    case "PagosIncompletos":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='p' AND statusn&1=1 AND statusn<128 and id not in (select distinct cp.idCPago from CPagos cp inner join DPagos dp on cp.id=dp.idPPago)";
        break;
    case "PagosAMedias":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='p' AND statusn&1=1 AND statusn<128 and id not in (select distinct idCPago from CPagos)";
        break;
    case "PagosPendientes":
        $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='p' AND statusn&1=0 AND statusn<128";
        //if (!$_esSistemas) $where .= " AND statusn>=0";
        break;
    case "VerificaCFDI":
        if (!$ignoraFechas) {
            $ignoraFechas=true;
            $where.=(isset($where[0])?" AND ":"")."((solicitaCFDI IS NOT null AND solicitaCFDI$rangoFechaWhere) OR (consultaCFDI IS NOT null AND consultaCFDI$rangoFechaWhere AND numConsultasCFDI>1))";
        } else $where.=(isset($where[0])?" AND ":"")."(solicitaCFDI IS NOT null OR (consultaCFDI IS NOT null AND numConsultasCFDI>1))";
        break;
    case "VerificaAyer":
        $where .= (isset($where[0])?" AND ":"")."(solicitaCFDI IS NOT NULL OR (consultaCFDI>=(current_date()-1) AND numConsultasCFDI>1))";
        break;
    case "PUE":
        $where .= (isset($where[0])?" AND ":"")."statusn>=0 AND statusn<128 and metododepago='PUE'";
        break;
    case "PUESinPago":
        $where .= (isset($where[0])?" AND ":"")."metododepago='PUE' and statusn between 0 and 31";
        break;
    case "EA":
        $where .= (isset($where[0])?" AND ":"")."statusn>=0 AND statusn<128 and ea='1'";
        break;
    default:
        if (!empty($status))   $where .= (isset($where[0])?" AND ":"")."status = '$status'";
}
if (!$ignoraFechas) {
    $where .= (isset($where[0])?" AND ":"")."fechaFactura$rangoFechaWhere";
}
if (!$_esSistemas && strpos($where, "tipoComprobante")===false) $where .= (isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1)) in ('i','e','p')";
$invObj->addOrder("codigoProveedor");
$invObj->addOrder("fechaFactura");
$prvCodigoOpt = $_SESSION['prvCodigoOpt']??[];
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt']??[];
if (is_null($gpoCodigoOpt)) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
$data = $invObj->getData($where);
//clog2($invObj->log);
global $query;
if ($_esDesarrollo) clog2("Query: ".$query);
if (count(getUser()->perfiles)!=1) $esProveedor=false;
//$esProveedor = $esProveedor && (count(getUser()->perfiles)==1);
//clog2("ProveedorValido: ".($esProveedor?"SI":"NO"));
//clog2("Es Proveedor: ".($esProveedor?"SI":"NO"));
//clog2("Modifica Proc: ".($modificaProc?"SI":"NO"));
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $invObj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $invObj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $invObj->lastpage ?>" />
          <input type="hidden" name="numRegs" id="numRegs" value="<?= count($data) ?>" />
<?php
if ($soloUnProveedor) echo "<input type=\"hidden\" id=\"unEmisor\" value=\"1\"/>";
if ($soloUnaEmpresa)  echo "<input type=\"hidden\" id=\"unReceptor\" value=\"1\"/>";
if (!isset($lookoutFilePath)) {
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
    else $lookoutFilePath = "";
}
$tieneRemision=false;
$remisionScript="fee(lbycn('colRemision'),el=>clrem(el,'hidden'));";
for ($idx=0; $idx<count($data); $idx++) {
    $row=$data[$idx];
    $dataIndex=$idx+1;
    $factId=$row["id"];
    $folio=$row["folio"];
    $remision=$row["remision"]??"";
    if (isset($remision[0])) {
        if ($remision==="S/REMISION") $remision="";
        else $tieneRemision=true;
    }
    $serie=$row["serie"];
    $uuid=$row["uuid"];
    $istatus=$row['status'];
    $lowstatus=strtolower($istatus);
    $istatusn=$row['statusn']??null;
    echo "<!-- ROW idx:$idx, dataIndex:$dataIndex, factId:$factId, folio:$folio, remision:$remision, serie:$serie, uuid:$uuid, istatus:$istatus, lowstatus:$lowstatus, istatusn:$istatusn -->";
    $tieneAceptado=$invObj->estaAceptado($istatusn);
    $tieneContrarrecibo=$invObj->estaContrarrecibo($istatusn);
    if (!is_null($istatusn)) $istatusn=+$istatusn;
    $tipFolio="";
    if (!isset($folio[0])) {
        $fileSuffix=substr($uuid,-10);
        $folio="[".$fileSuffix."]";
        $tipFolio=" title=\"Sin Folio\"";
    } else if (isset($folio[10])) {
        $fileSuffix=substr($folio,-10);
    } else {
        $fileSuffix=$folio;
        if (isset($serie[0])) {
            $fileSuffix2=$serie.$folio;
            if (isset($fileSuffix2[10])) $fileSuffix2=substr($fileSuffix2, -10);
        }
    }
    $tipoComprobante=strtoupper(substr($row['tipoComprobante'],0,1));
    if ($tipoComprobante==="E") {
        $fileSuffix="NC_".$fileSuffix;
        if (isset($fileSuffix2[0])) $fileSuffix2="NC_".$fileSuffix2;
    } else if ($tipoComprobante==="P") {
        $fileSuffix="RP_".$fileSuffix;
        if (isset($fileSuffix2[0])) $fileSuffix2="RP_".$fileSuffix2;
    }
    $tipoComprobante2=$tipoComprobante;
    if ($tipoComprobante2==="I") $tipoComprobante2="F";
    $lenSfx=strlen($fileSuffix);
    if (isset($fileSuffix2[0])) $lenSfx2=strlen($fileSuffix2);
    $codigoProveedor=$row["codigoProveedor"];
    $rfcProveedor=$prvRFCOpt[$codigoProveedor]??"";
    if (!isset($rfcProveedor)) {
        doclog("Proveedor no encontrado","error",["index"=>$idx,"row"=>$row,"prvRFCOpt"=>$prvRFCOpt]);
        continue;
    }
    $xmlName=$row["nombreInterno"];
    $pdfName=$row["nombreInternoPDF"];
    $ea=$row["ea"]??"0";
    $disEA=($ea==="-1");
    $notEA=($ea==="0");
    $hasEA=($ea==="1");
    $yearCycle=$row["ciclo"];
    $ubicacion=$row['ubicacion'];
    $hasXMLReg=isset($xmlName[0]);
    $hasPDFReg=isset($pdfName[0]);
    $xmlClass="centered"; $pdfClass="centered";
    $xmlFileExists=false; $pdfFileExists=false; $eaFileExists=false;
    $veErroresDeArchivos=$esAvance;
    $canFixXML = false;
    $canFixPDF = false;
    $isWrong=false;
    $estaRespaldado = ($istatusn!==NULL&&($istatusn&Facturas::STATUS_RESPALDADO)>0);
    //if ($_esSistemas) {
        $expectedXMLName = $rfcProveedor."_".$fileSuffix;
        $isExpectedXML = ($xmlName===$expectedXMLName);
        $expectedPDFName = $fileSuffix.$rfcProveedor;
        $isExpectedPDF = ($pdfName===$expectedPDFName);
        if (isset($fileSuffix2[0])) {
            $expectedXMLName2 = $rfcProveedor."_".$fileSuffix2;
            if ($xmlName===$expectedXMLName2) {
                $fileSuffix=$fileSuffix2;
                $lenSfx=$lenSfx2;
                $isExpectedXML=true;
                // ToDo: Arreglar que el pdf debe tener la serie tambien, no se está actualizando. Aquí el unico sentido de arreglar esto es para que el pdf no tenga fondo rojo.
            }
            $expectedPDFName2 = $fileSuffix2.$rfcProveedor;
            $isExpectedPDF |= ($pdfName===$expectedPDFName2);
        }
        echo "<!-- CurrentXMLName '$xmlName' and ExpectedXMLName '$expectedXMLName' -->";
        if ($hasXMLReg && !$isExpectedXML && !$estaRespaldado) {
            $invObj->renombraXML($factId, $xmlName, $expectedXMLName, $ubicacion);
            $xmlName=$expectedXMLName;
            echo "<!-- Renamed XML -->";
        } else echo "<!-- Not changed XML -->";
        echo "<!-- CurrentPDFName '$pdfName' and ExpectedPDFName '$expectedPDFName' -->";
        if ($hasPDFReg && $pdfName!==$expectedPDFName && !$estaRespaldado) {
            $invObj->renombraPDF($factId, $pdfName, $expectedPDFName, $ubicacion);
            $pdfName=$expectedPDFName;
            echo "<!-- Renamed PDF -->";
        } else echo "<!-- Not changed PDF -->";
    //}
    $motivo="XMLNAME='$xmlName', PDFNAME='$pdfName', FSFX='$fileSuffix', LENSFX=$lenSfx";
    if (isset($fileSuffix2[0])) $motivo.=", FSFX2='$fileSuffix2', LENSFX2=$lenSfx2";
    $motivo.="\n";
    if ($hasXMLReg) {
        $xmlFilePath = $ubicacion.$xmlName.".xml";
        $xmlFileExists = file_exists($lookoutFilePath.$xmlFilePath);
        if ($veErroresDeArchivos) {
            if (substr($xmlName,-1-$lenSfx)==="_$fileSuffix") $xmlClass.=" right1BG";
            else {
                $xmlClass.=" wrong1BG";
                $motivo.="1- '".substr($xmlName,-1-$lenSfx)."' no es igual a '_$fileSuffix'\n";
            }
        }
    } else if ($veErroresDeArchivos) {
        $xmlClass.=" wrong1BG";
        $motivo.="2- No tiene XML\n";
    }
    if ($hasPDFReg) {
        $pdfFilePath=$ubicacion.$pdfName.".pdf";
        $pdfFileExists = file_exists(($lookoutFilePath.$pdfFilePath));
        if ($veErroresDeArchivos) {
            if (substr($pdfName,0,$lenSfx)===$fileSuffix) {
                $pdfClass.=" right1BG";
            } else if ($pdfFileExists && ($tipoComprobante==="E"||$tipoComprobante==="P") && substr($pdfName,0,$lenSfx-3)===substr($fileSuffix,3)) {
                $pdfClass.=" right1BG";
            } else {
                $pdfClass.=" wrong1BG";
                if (substr($pdfName,0,$lenSfx)!==$fileSuffix) {
                    $motivo.="3- '".substr($pdfName,0,$lenSfx)."' no es igual a '$fileSuffix'\n";
                    if (isset($fileSuffix2[0])) $motivo.="   '".substr($pdfName,0,$lenSfx2)."' ... vs ... '$fileSuffix2'\n";
                }
                if (!$pdfFileExists) {
                    $motivo.="4- No existe el archivo '$pdfFilePath'\n";
                } else if ($tipoComprobante==="E"||$tipoComprobante==="P") {
                    $motivo.="5- TC es $tipoComprobante y '".substr($pdfName,0,$lenSfx-3)."' no es igual a '".substr($fileSuffix,3)."'\n";
                    if (isset($fileSuffix2[0])) $motivo.="                            '".substr($pdfName,0,$lenSfx2-3)."' ... vs ... '".substr($fileSuffix2, 3)."'\n";
                }
                $isWrong=true;
            }
        }
        if($isWrong) {
            $fixPDFName=$fileSuffix.$rfcProveedor;
            $fixPdfFilePath = $ubicacion.$fixPDFName.".pdf";
            if (!file_exists($lookoutFilePath.$fixPdfFilePath)) {
                $fixPDFName=substr($fileSuffix,3).$rfcProveedor;
                $fixPdfFilePath = $ubicacion.$fixPDFName.".pdf";
                if (!file_exists(($lookoutFilePath.$fixPdfFilePath))) {
                    $isWrong=false; // Se mantiene fondo rojo para resaltar y verificar manualmente el posible error
                } else if (!$estaRespaldado) {
                    $invObj->renombraPDF($factId, $pdfName, $fixPDFName, $ubicacion);
                    $pdfName=$fixPDFName;
                    $isWrong=false;
                }
            }
        }
    }
    $xmlAttribs=" class=\"$xmlClass\"";
    $pdfAttribs=" class=\"$pdfClass\"";
    $delMessage = "DEL$factId";
    $esPago=false; $esNota=false; $esFactura=false;
    $esTemporal=($istatusn===null);
    $esPendiente=($istatusn===Facturas::STATUS_PENDIENTE);
    $esAceptado=($istatusn===Facturas::STATUS_ACEPTADO);
    $noAceptado=(($istatusn&Facturas::STATUS_ACEPTADO)===0);
    $noContrarrecibo=(($istatusn&Facturas::STATUS_CONTRA_RECIBO)===0);
    $noRechazado=($istatusn<Facturas::STATUS_RECHAZADO);
    //$conContraAbierto=($istatusn&Facturas::STATUS_CONTRA_RECIBO)>0;
    $estaProgPago=Facturas::estaProgPago($istatusn);
    $estaPagado=Facturas::estaPagado($istatusn)||Facturas::estaRecPago($istatusn);
    $pagadoPendiente = $_esSistemas&&$noAceptado&&($estaProgPago||$estaPagado); // && in_array($lowstatus, ["progpago","pagado","recpago"])
    $esInalterable=$esTemporal||Facturas::estaRechazado($istatusn)||Facturas::estaPagado($istatusn);//$estaPagado;//in_array($lowstatus,["temporal","rechazado","cancelado","pagado","recpago"]);
    $esCasoEspecial = $_esSistemas&&($estaProgPago||$estaPagado);//in_array($lowstatus,["progpago","pagado","recpago"]);
    $esProcesable=((!$esInalterable||$pagadoPendiente)&&($esPendiente || $esAceptado || $_esSistemas || $esAvance));
    $esRechazable=((!$esInalterable||$esCasoEspecial)&&($esPendiente || $_esSistemas || $esRechazante));
    $esActivo=(!$esTemporal && $noRechazado);
    $esEliminable=($esPendiente&&$delPendiente)||$_esSistemas;
    $esModificable=$esBorraDoc || $esPendiente || ($tieneAceptado && $noContrarrecibo && !$esProveedor);
    if ($esCompras && !$esModificable && $tieneContrarrecibo/* && $noRechazado*/ && !$estaPagado) {
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
        }
        $ctfData=$ctfObj->getData("idFactura=$factId",0,"autorizadaPor");
        $autorizadaPor=$ctfData[0]["autorizadaPor"]??"";
        if (!isset($autorizadaPor[0])) $esModificable=true;
    }
    $fechaCrea = $row['fechaFactura'];
    if($hasEA) {
        $eacp=trim(str_replace("-","",$codigoProveedor));
        //if (isset($eacp[0])) clog2(" --- EA CON CP: $eacp --- ");
        //else clog2("--- EA SIN CP --- ");

        //$eafolio=$fileSuffix;
        //if (isset($serie[0]) && isset($folio[0]) && $fileSuffix===($serie.$folio)) $eafolio=$folio;
        $eafolio=$row["folio"];
        if (isset($serie[0])&&isset($eafolio[0])) {
            $eafolio2=$serie.$eafolio;
            if (isset($eafolio2[10])) $eafolio2=substr($eafolio2, -10);
            if ($eafolio==$eafolio2) $eafolio2=null;
        }
        if (!isset($eafolio[0])) $eafolio=$uuid;
        if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
        $eafecha=substr(str_replace("-","",$fechaCrea),2,6);
        if (isset($ubicacion[0])&&isset($eacp[0])&&isset($eafolio[0])) {
            $eaFilePath="{$ubicacion}EA_{$eacp}_{$eafolio}_{$eafecha}.pdf";
            $eaFileExists=file_exists($lookoutFilePath.$eaFilePath);
            if (!$eaFileExists) {
                echo "<!-- EA File1 don't exist: $eaFilePath -->\n";
                if ($tipoComprobante==="I") {
                    if (isset($eafolio2[0])) {
                        $eaFilePath="{$ubicacion}EA_{$eacp}_{$eafolio2}_{$eafecha}.pdf";
                        $eaFileExists=file_exists($lookoutFilePath.$eaFilePath);
                        if (!$eaFileExists) echo "<!-- EA File2 don't exist: $eaFilePath -->\n";
                    }
                    if (!$eaFileExists && isset($expectedXMLName[0])) {
                        $eafolio3=substr($expectedXMLName, -10);
                        if ($eafolio3!==$eafolio && (!isset($eafolio2)||$eafolio3!==$eafolio2)) {
                            $eaFilePath="{$ubicacion}EA_{$eacp}_{$eafolio3}_{$eafecha}.pdf";
                            echo "<!-- Nombre EA3: $eaFilePath -->\n";
                            $eaFileExists=file_exists($lookoutFilePath.$eaFilePath);
                        } //else echo "<!-- NO EXISTE  -->";
                    } //else echo "<!-- ".($eaFileExists?"SI":"NO")." EXISTE, expectedXMLName='$expectedXMLName' -->";
                } else if ($tipoComprobante==="E"||$tipoComprobante==="P") {
                    $eaFilePath="{$ubicacion}EA_{$eacp}_".($tipoComprobante==="E"?"NC":"RP")."_{$eafolio}_{$eafecha}.pdf";
                    echo "<!-- Nombre EA4: $eaFilePath -->\n";
                    $eaFileExists=file_exists($lookoutFilePath.$eaFilePath);
                } //else echo "<!-- TipoComprobante='$tipoComprobante' -->\n";
            }
        }
    } else {
        //clog2(" --- SIN ENTRADA DE ALMACEN --- ");
    }
    switch ($tipoComprobante) {
        case "I": $nombreComprobante="Factura"; $descCompro="la factura"; $esFactura=true; break; // default
        case "E": $nombreComprobante="Nota"; $descCompro="la nota"; $esNota=true; break;
        case "T": $nombreComprobante="Traslado"; $descCompro="el traslado"; $esProcesable=false; $esRechazable=false; break;
        case "P": $nombreComprobante="Complemento Pago"; $descCompro="el recibo"; $esPago=true; /*$esProcesable=false; $esRechazable=false;*/ break;
        default: $nombreComprobante="Desconocido"; $descCompro="el comprobante"; $tipoComprobante2="?";
    }
    require_once "clases/CatLista69B.php";
    if (CatLista69B::estaMarcado($rfcProveedor)) {
        $esProcesable=false;
        $esCat69B=true;
    } else $esCat69B=false;
    $rowRfc=$row["rfcGrupo"];
    $gpoCop=$gpoCodigoOpt;
    $alias=$gpoCodigoOpt[$row['rfcGrupo']];
    $aliasUP=strtoupper($alias);
    $ftpInvPath=$ftp_supportPath.$aliasUP."/".($esPago?"T":"")."PUBLICO/";
    $ftpPolPath=$ftp_policyPath.$aliasUP."/".($esPago?"T":"")."PUBLICO/";
    $ftpPDFFileName="";
    $modStt=($modificaProc?2:($esProveedor?0:1));
    //doclog("DSTATUS","test",["selector"=>"reportefacturas","istatusn"=>$istatusn, "tc"=>$tipoComprobante, "modStt"=>$modStt, "idFactura"=>$factId, "folio"=>$folio, "remision"=>$remision, "codigoProveedor"=>$codigoProveedor]);
    $dstatus=Facturas::statusnToRealStatus($istatusn,$tipoComprobante,$modStt);
    $realStatus=$dstatus;
    if (Facturas::estaRespaldado($istatusn) && $istatusn<Facturas::STATUS_PROGPAGO) {
        if ($esNota) {
            if (($istatusn&Facturas::STATUS_CONTRA_RECIBO)===0)
                $dstatus="Respaldado sin Contra-Recibo";
            else $dstatus="Respaldado";
        } else if ($esPago) $dstatus="Respaldado";
    }
    $pyImg="";
    $idReciboPago=$row["idReciboPago"]??null;
    // Documento comprobante de pago se mostrará dentro de la columna Status, cuando status="con C.Pago"
    // Inicialmente limitado sólo para usuarios de sistemas.
    // Finalmente deshabilitado pues el comprobante de pago se muestra en la columna Documentos
    if (false && $_esSistemas && /* $status==="Pagado" && $dstatus==="Recibo de Pago" &&*/ isset($idReciboPago[0])) {
        $pyData=$invObj->getData("id='$idReciboPago'",0,"folio,ubicacion,nombreInterno,nombreInternoPDF,statusn");
        if(isset($pyData[0])) {
            $pyData=$pyData[0];
            if (isset($pyData["nombreInternoPDF"][0])) {
                $pyPDFPath=$pyData["ubicacion"].$pyData["nombreInternoPDF"].".pdf";
                if (file_exists($lookoutFilePath.$pyPDFPath)) {
                    $pyImg="<a href=\"$pyPDFPath\" target=\"archivo\" class=\"vAlignCenter\"><img src=\"imagenes/icons/pdf200.png\" width=\"28\" height=\"28\" /></a>";
                } else {
                    $pyImg="<!-- ARCHIVO NO EXISTE: idReciboPago=$idReciboPago, folio=$pyData[folio], ubicacion=$pyData[ubicacion], xml=$pyData[nombreInterno], pdf=$pyData[nombreInternoPDF], status=$pyData[statusn] -->";
                }
            } else if (isset($pyData["nombreInterno"][0])) {
                $pyXMLPath=$pyData["ubicacion"].$pyData["nombreInterno"].".xml";
                if (file_exists($lookoutFilePath.$pyXMLPath)) {
                    $pyImg="<a href=\"$pyXMLPath\" target=\"archivo\" class=\"vAlignCenter\"><img src=\"imagenes/icons/xml200.png\" width=\"28\" height=\"28\" class=\"vAlignCenter\" /></a>";
                } else {
                    $pyImg="<!-- ARCHIVO NO EXISTE: idReciboPago=$idReciboPago, folio=$pyData[folio], ubicacion=$pyData[ubicacion], xml=$pyData[nombreInterno], pdf=$pyData[nombreInternoPDF], status=$pyData[statusn] -->";
                }
            } else $pyImg="<!-- NO TIENE XML ni PDF: idReciboPago=$idReciboPago, folio=$pyData[folio], ubicacion=$pyData[ubicacion], xml=$pyData[nombreInterno], pdf=$pyData[nombreInternoPDF], status=$pyData[statusn] -->";
        } else $pyImg="<!-- NO SE ENCONTRO PAGO: idReciboPago=$idReciboPago -->";
    }
    // Documento comprobante de pago identificado por su id. Se mostrará en la columna Documentos

    $prePaymentDetail="";
    if (isset($idReciboPago[0])) {
        $cfdiPData=$invObj->getData("id=$idReciboPago",0,"concat(ubicacion,nombreInterno,'.xml') pXmlPath, concat(ubicacion,nombreInternoPDF,'.pdf') pPdfPath");
        $xmlCompPath=$cfdiPData[0]["pXmlPath"]??"";
        $pdfCompPath=$cfdiPData[0]["pPdfPath"]??"";
        $tieneComplementoPagoPDF=isset($pdfCompPath[0]);
        if (!isset($xmlCompPath[0])) {
            $tieneComplementoPago=false;
            $motivo.="Sin complemento de pago con idReciboPago $idReciboPago\n";
            // toDo: si no hay pdf mostrar página generada automáticamente
        } else {
            $tieneComplementoPago=true;
            // toDo: validar que existe el documento
        }
        if ($status==="SinCPago") {
            $saldoReciboPago=$row["saldoReciboPago"]??null;
            $prePaymentDetail="<P class=\"centered nomargin bgred fontMedium\"><B>DEBE</B>: <span class=\"padv5\">$".number_format($saldoReciboPago,2)."</span></P>";
        }
    } else {
        $tieneComplementoPago=false;
        $tieneComplementoPagoPDF=false;
    }

    $comprobantePago=$row["comprobantePagoPDF"]??null;
    if (isset($comprobantePago[0])) {
        $pdfPaymPath=$ubicacion.$comprobantePago.".pdf";
        $tieneComprobantePago=true;
        // toDo: validar que existe el documento
    } else $tieneComprobantePago=false;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("idFactura=$factId");
    $tieneSolicitud=$tieneSolicitudes && isset($solData[0]["id"]);
    if ($tieneSolicitud) {
        $solId=$solData[0]["id"];
        $solFolio=$solData[0]["folio"];
    } else {
        $solId="";
        $solFolio="";
    }
    if (isset($solData[0]["archivoAntecedentes"][0])) {
        $archivoAntecedentes=$solData[0]["archivoAntecedentes"];
        $bgDocPath=$ubicacion.$archivoAntecedentes.".pdf";
        $tieneAntecedentes=true;
    } else $tieneAntecedentes=false;

    $paymentDetail="";
    if (($istatusn&Facturas::STATUS_PAGADO)>0) {
        if (!isset($pyObj)) {
            require_once "clases/Pagos.php";
            $pyObj=new Pagos();
        }
        $pyObj->clearOrder();
        $pyObj->addOrder("fechaPago");
        $pyData=$pyObj->getData("idFactura=$factId and valido=1");
        if (isset($pyData[0])) {
            $preDate=null;
            $lastPyDate=null;
            $pyTrace=[];
            foreach ($pyData as $pyIdx=>$pyElem) {
                $pyDate=substr($pyElem["fechaPago"],0,10);
                if (!isset($lastPyDate[0])) {
                    $preDate=$pyDate;
                } else if ($lastPyDate!==$pyDate) {
                    if (isset($preDate[0])) {
                        $paymentDetail="<P class=\"centered nomargin fontMedium\"><span class=\"bgdarkbluelt1 padv5\">$preDate</span></P>".$paymentDetail;
                        $preDate="";
                    }
                    $paymentDetail.="<P class=\"centered nomargin fontMedium\"><span class=\"bgdarkbluelt1 padv5\">$pyDate</span></P>";
                }
                $lastPyDate=$pyDate;
                $pyFOp=""; $pyFCl="";
                if (isset($pyElem["archivo"][0])/* && !$esProveedor*/ && $_esAdministrador) {
                    $filename=basename($pyElem["archivo"]);
                    $filenoext=pathinfo($filename, PATHINFO_FILENAME);
                    $pyFOp="<a href=\"http://globaltycloud.com.mx:81/invoice/consultas/docs.php?pymtxt=$filename\" class=\"nodeco bodycolor\" title=\"$filenoext\" download=\"$filename\">"; // ['boldValue','pointer']
                    $pyFCl="</a>";
                }
                $paymentDetail.="<P class=\"fontMedium nomargin righted padv5\">{$pyFOp}$pyElem[referencia]{$pyFCl} x $".number_format($pyElem["total"],2)."</P>";
                $pyTrace[]=$pyDate." | $pyElem[referencia] | $pyElem[total] | ".($pyElem["archivo"]??"");
            }
            if (isset($paymentDetail[0])) {
                $paymentDetail="<P class=\"lefted nomargin fontMedium bgdarkbluelt padv5\"><B>PAGOS</B>: <span class=\"bgdarkbluelt padv5\">{$preDate}</span></P>".$paymentDetail;
            }
            foreach ($pyTrace as $txt) {
                $paymentDetail.="\n<!-- $txt -->";
            }
        }
    }
    $preSttSAT="";
    $posSttSAT="";
    if ($esVCFDI) {
        $sttCFDIid=" id=\"statusCFDI{$factId}\"";
        $sttCFDIsz=" width=\"21\" height=\"21\"";
        $posSttVAlign="vAlignCenter"; // "vbottom";
        $isRfr=$_esSistemas&&$esPago&&$esPendiente;
        $preSttSAT="<img src=\"imagenes/icons/".($isRfr?"refresh":"statusEmpty").".png\"$sttCFDIsz class=\"$posSttVAlign marginV2 noprint".($isRfr?" pad1 pointer":"")."\"".($isRfr?" onclick=\"updatePaymRcpt($factId);\"":"")."/>";
        if (empty($row['solicitaCFDI']) && !empty($row['cancelableCFDI'])) {
            $cancelable=$row['cancelableCFDI'];
            if ($cancelable==="ERR"||$cancelable==="601"||$cancelable==="602") {
                switch($cancelable) {
                    case "ERR": $cancelttl=" title=\"La verificación no pudo realizarse. Consulte al administrador del portal.\""; break;
                    case "601": $cancelttl=" title=\"La verificación no fue reconocida (601). Consulte al administrador del portal.\""; break;
                    case "602": $cancelttl=" title=\"El comprobante no fue encontrado (602). Consulte al administrador del portal.\""; break;
                    default: $cancelttl="";
                }
                $posSttSAT="<img src=\"imagenes/icons/statusErrorUp.png\"$sttCFDIid$sttCFDIsz class=\"$posSttVAlign marginV2 noprint pointer\" onclick=\"verificaCFDI($factId);\" onmouseover=\"this.src='imagenes/icons/statusErrorDn.png';\" onmouseout=\"if (this.src.includes('statusError')) this.src='imagenes/icons/statusErrorUp.png';\"$cancelttl/>";
            } else if ($row["estadoCFDI"]==="Vigente" && empty($row["canceladoCFDI"]) && $istatusn<Facturas::STATUS_RECHAZADO) {
                $posSttSAT="<img src=\"imagenes/icons/statusRightUp.png\"$sttCFDIid$sttCFDIsz class=\"$posSttVAlign marginV2 noprint pointer\" onclick=\"verificaCFDI($factId);\" onmouseover=\"this.src='imagenes/icons/statusRightDn.png';\" onmouseout=\"if (this.src.includes('statusRight')) this.src='imagenes/icons/statusRightUp.png';\" title=\"CFDI Vigente. Ultima consulta: $row[consultaCFDI].\nPresione para verificar nuevamente.\"/>";
            } else {
                if (empty($row["canceladoCFDI"])) $cancelttl="";
                else $cancelttl=" $row[canceladoCFDI]";
                $posSttSAT="<img src=\"imagenes/icons/statusWrongDn.png\"$sttCFDIid$sttCFDIsz class=\"$posSttVAlign marginV2 noprint\" title=\"CFDI $row[estadoCFDI]. $row[cancelableCFDI]$cancelttl\"/>";
            }
        } else $posSttSAT="<img src=\"imagenes/icons/statusWaitDn.png\"$sttCFDIid$sttCFDIsz class=\"$posSttVAlign marginV2 noprint\" title=\"En espera de verificación...\" onload=\"holding($factId,10,'IMGLOAD');\"/>";
    }
    $empresaAttribs  =" class=\"noApply middle".($soloUnaEmpresa?" hidden":"")."\"";
    $proveedorAttribs=" class=\"noApply middle".($soloUnProveedor?" hidden":"")."\"";
    if (isset($prvRazSocOpt[$codigoProveedor])) $proveedorAttribs.=" title='".$prvRazSocOpt[$codigoProveedor]."'";
    $vFechaCrea = "<span class='inblock'>".str_replace("-","&#8209;",substr($fechaCrea,0,10))."</span>";
    $vHoraCrea = "<span class='hidden doprintInline'>&nbsp;".substr($fechaCrea,11)."&nbsp;</span>";
    $xFechaCrea=(new DateTimeImmutable($fechaCrea))->format('d/m/Y H:i:s');

    $fechaAlta = $row['fechaCaptura'];
    if ($_esSistemas && $lowstatus==="pagado" && false) {
        $vFechaAlta=""; // TODO: FALTA AGREGAR FECHA DE PAGO
        $vHoraAlta="";
    } else {
        $vFechaAlta = "<span class='inblock'>".str_replace("-","&#8209;",substr($fechaAlta,0,10))."</span>";
        $vHoraAlta = "<span class='hidden doprintInline'>&nbsp;".substr($fechaAlta,11)."&nbsp;</span>";
    }
    $xFechaAlta=(new DateTimeImmutable($fechaAlta))->format('d/m/Y H:i:s');
    $statusAttribs="value=\"$dstatus\" class=\"noApply middle rowstt".($esVCFDI?" nowrap":"")."\" id=\"rowstt$dataIndex\"";
    $canViewITips=$_esSistemas||$esGestor;
    $substtAttribs=($canViewITips?" class=\"itooltip vAlignCenter\"":"")." statusn=\"$istatusn\" realstatus=\"$realStatus\" modstt=\"{$modStt}\"";
    $sonUltimos2=(count($data)>3 && ($idx+2)>=count($data));
    $itipSttAttribs="class=\"".($canViewITips?"itip".($sonUltimos2?" toTop":" toRight"):"hidden")."\" id=\"rowitp$dataIndex\"";
    $totalAttribs="";
    $totalClass="noApply middle righted shrinkCol";
    $total=$row["total"];
    if ($tipoComprobante==="P"&&!empty($row["saldoReciboPago"])) {
        $totalClass.=" bgblue";
        $totalAttribs="title=\"Importe de Pago\" ";
        $total=$row["saldoReciboPago"];
    }
    $totalAttribs.="class=\"$totalClass\" value=\"$total\"";
    if ($tieneContrarrecibo) {
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
        }
        $ctfData=$ctfObj->getData("idFactura=$factId",0,"idContrarrecibo");
        $idContrarrecibo=$ctfData[0]["idContrarrecibo"]??null;
        if (isset($idContrarrecibo[0])) {
            global $ctrObj;
            if (!isset($ctrObj)) {
                require_once "clases/Contrarrecibos.php";
                $ctrObj=new Contrarrecibos();
            }
            $ctrData=$ctrObj->getData("id=$idContrarrecibo",0,"concat(aliasGrupo,'-',folio) ctrFolio,esCopia,numAutorizadas,numContraRegs");
            $ctrFolio=$ctrData[0]["ctrFolio"]??"";
            $ctrNumAuth=$ctrData[0]["numAutorizadas"];
            $ctrNumCF=$ctrData[0]["numContraRegs"];
            if (!isset($ctrFolio[0])) {
                $tieneContrarrecibo=false;
                $esCopia=false;
                $motivo.="Sin contra-recibo con status 'Con Contra-recibo' y id-CR=$idContrarrecibo\n";
            } else $esCopia=$ctrData[0]["esCopia"];
        } else {
            $tieneContrarrecibo=false;
            $motivo.="Sin contra-recibo con status 'Con Contra-recibo'\n";
        }
    }
    $moneda=$row['moneda'];
    if ($moneda==="XXX") $moneda="";
    $totalAttribs.=" value2=\"$moneda\"";
?>
          <tr idx="<?= $dataIndex ?>">
            <td class="noApply middle"><?= $dataIndex ?><input type="hidden" class="rowidx" value="<?= $dataIndex ?>"><input type="hidden" id="rowid<?= $dataIndex ?>" class="rowid" value="<?= $factId ?>" status="<?= $istatusn ?>"><input type="hidden" id="fI2dI<?= $factId ?>" value="<?= $dataIndex ?>"></td>
            <td class="noApply middle" title="<?= $fechaCrea ?>" value="<?= $xFechaCrea ?>"><?= $vFechaCrea ?><?= $vHoraCrea ?></td>

            <td class="noApply middle" title="<?= $fechaAlta ?>" value="<?= $xFechaAlta ?>"><?= $vFechaAlta ?><?= $vHoraAlta ?></td>

            <td<?= $empresaAttribs ?>><?= ucfirst($alias) ?></td>
            <td<?= $proveedorAttribs ?>><?= str_replace("-","&#8209;",$codigoProveedor) ?></td>
            <td class="noApply middle shrinkCol" title="<?= $nombreComprobante ?>" value="<?= $nombreComprobante ?>"><span class='inblock'><?= $tipoComprobante2 ?></span><input type="hidden" id="ftipocomprob<?= $dataIndex ?>" value="<?= $tipoComprobante ?>"></td>
            <td class="noApply middle nowrap"<?= $tipFolio ?>><?= $folio ?><input type="hidden" id="ffolio<?= $dataIndex ?>" value="<?= $folio ?>"></td>
            <td class="noApply middle nowrap<?= $tieneRemision?"":" colRemision hidden" ?>" value2="<?= $uuid ?>"><?= $remision ?><input type="hidden" id="fremision<?= $dataIndex ?>" value="<?= $remision ?>"><?php if ($tieneRemision&&isset($remisionScript[0])) { echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"{$remisionScript}\">"; $remisionScript=""; } ?></td>
            <td <?= $totalAttribs?>>$<span class="currency vbottom"><?= number_format($total,2) ?></span><span class="curr_codeb"><?= $moneda ?></span></td>
            <td <?= $statusAttribs ?>><?= $preSttSAT ?><span<?= $substtAttribs ?>><?= $dstatus ?><span <?= $itipSttAttribs ?>><table class="noApply fontSmall"><thead class="panalBGDark boldValue whited"><tr><td class="righted">ID:</td><td class="centered larger btnvwTmp" onclick="copyToClipboard(event);"><?= $factId ?></td><td class="righted"><?= $istatusn ?></td></tr><tr><th class="pad2 brdr1d bbtm2d">  Fecha  </th><th class="pad2 brdr1d bbtm2d">Usuario</th><th class="pad2 brdr1d bbtm2d"> Status </th></tr></thead></table></span></span><?= $pyImg.$posSttSAT.$prePaymentDetail.$paymentDetail ?></td>
<?php
    if($modificaProc) { ?>
            <td class="centered noprint">
<?php   if($esProcesable) { 
    $procImg=$_esDesarrollo?"process200i.png":"process96.png";
    ?>
              <div class="pointer" onclick="eliminaDatosPost(); verificaFactura(<?= $factId ?>);">
                <img src="imagenes/icons/<?= $procImg ?>" width="32" height="32" />
              </div>
<?php   } else if ($esCat69B) { ?>
              <div class="pointer" onclick="verificaFactura(<?= $factId ?>,true);" title="Proveedor <?= $rfcProveedor ?> encontrado en Lista 69b">
                <img src="imagenes/icons/reject69b.png" width="32" height="32" />
              </div>
<?php   } else {
            $vwCls="pointer";
            $vwImg="viewer96";
            if ($tipoComprobante==="P" && $esActivo) {
                $hasDocRelated=$invObj->exists("idReciboPago='$factId'");
                if (!$hasDocRelated) {
                    $vwCls.=" relative";
                    if ($xmlFileExists) { // obtener uuid de doctorelacionado's
                        require_once "clases/CFDI.php";
                        $cfdiObj = CFDI::newInstanceByLocalName($lookoutFilePath.$xmlFilePath);
                        if (isset($cfdiObj)) { // toDo: buscar si existen facturas con uuid = cfdi:Complemento|pago10:Pagos|pago10:Pago|pago10:DoctoRelacionado[@iddocumento]
                            $doctos=$cfdiObj->get("pago_doctos");
                            if (isset($doctos["@iddocumento"])) $doctos=[$doctos];
                            $uids=[];
                            $imps=[];
                            foreach ($doctos as $dIdx => $doct) {
                                $idDoc=$doct["@iddocumento"]??"";
                                if (isset($idDoc[0])) {
                                    $uids[]=$idDoc;
                                    $imps[$idDoc]=["ant"=>($doct["@impsaldoant"]??""),"pag"=>($doct["@imppagado"]??""),"ins"=>($doct["@impsaldoinsoluto"]??"")];
                                }
                            }
                            if (isset($uids[0][0])) {
                                $invData=$invObj->getDataByFieldArray(["uuid"=>$uids], 0, "id,uuid,tipoComprobante,ubicacion,nombreInterno,idReciboPago,saldoReciboPago,total,statusn");
                                DBi::autocommit(false);
                                foreach ($invData as $invRow) {
                                    $statusn=+$invRow["statusn"];
                                    if ($invRow["tipoComprobante"]!=="i") {
                                        if ($vwImg==="viewer96") $vwImg.="y";
                                        continue;
                                    }
                                    $idRP="".($invRow["idReciboPago"]??"");
                                    $impSI=+$imps[$invRow["uuid"]]["ins"]??null;
                                    $sldRP=+$invRow["saldoReciboPago"]??null;
                                    if (!is_null($sldRP) && (is_null($impSI)||$impSI>$sldRP)) {
                                        $impSI=$sldRP;
                                    }
                                    if (isset($idRP[0]) && $idRP!=="0"&& $impSI!==$sldRP) {
                                        if($vwImg==="viewer96") $vwImg.="y";
                                        continue;
                                    }
                                    $fieldArray=["id"=>$invRow["id"],"idReciboPago"=>$factId,"fechaReciboPago"=>$fechaCrea,"saldoReciboPago"=>$impSI]; // ,"status"=>"Pagado","statusn"=>new DBExpression("statusn|".Facturas::STATUS_RECPAGO)
                                    if (($statusn&Facturas::STATUS_RECPAGO)<=0) {
                                        $fieldArray["status"]="Pagado";
                                        $fieldArray["statusn"]=$statusn|Facturas::STATUS_RECPAGO;
                                    }
                                    if ($invObj->saveRecord($fieldArray)!==false && $vwImg==="viewer96") {
                                        global $prcObj;
                                        if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
                                        $prcObj->cambioFactura($invRow["id"], "Pagado", getUser()->nombre, false, "update paidComplement invoice");
                                        $vwImg="viewer96z";
                                    }
                                }
                                // ToDo: Verificar que accion realizar si alguna factura no se puede guardar, o si ya tiene asignado otro recibo de pago, podria requerir rollback
                                DBi::commit();
                                DBi::autocommit(true);
                            }
                        }
                    }
                    if ($vwImg==="viewer96") $vwImg.="x";
                }
            }
    ?>
              <div class="<?= $vwCls ?>" onclick="verificaFactura(<?= $factId ?>,true);">
                <img src="imagenes/icons/<?=$vwImg?>.png" width="32" height="32" />
              </div>
<?php   } ?>
            <!-- <?= ($esProcesable?"1":"0").($esCat69B?"1":"0").($esInalterable?"1":"0").($pagadoPendiente?"1":"0").($esPendiente?"1":"0").($esAceptado?"1":"0").$lowstatus.$istatusn ?> --></td>
<?php
    }
    //if (isset($motivo[0])) echo "<!-- MOTIVO: $motivo -->";
?>
            <td class="leftedi nowrap noprint"><?php
    //if ($_esAdministrador) { clog2(json_encode($solData)); }
    if ($tieneSolicitud) { 
              ?><img src="imagenes/icons/solPago200.png" width="32" height="32" class="pointer" title="SOLICITUD <?= $solFolio ?>" onclick="muestraSolicitud(<?=$solId?>,'<?=$solFolio?>');"/><?php
    }
    if($xmlFileExists) { 
              ?><a href="<?= $xmlFilePath ?>" target="archivo" title="CFDI-XML"><img src="imagenes/icons/xml200.png" width="32" height="32" /></a><?php
    }
    if($pdfFileExists) {
        if ($esActivo&&$esModificable) { 
            $fixPDF="['delPDF{$dataIndex}','chgPDF{$dataIndex}']"; // $esBorraDoc?"'delPDF{$dataIndex}'":
              ?><div id="pdfBlk<?=$dataIndex?>" class="inblock" onmouseenter="clrem(<?= $fixPDF ?>,'hidden');" onmouseleave="cladd(<?= $fixPDF ?>,'hidden');"><?php
        }
              ?><a href="<?= $pdfFilePath ?>" target="archivo" title="CFDI-PDF"><img src="imagenes/icons/pdf200.png" width="32" height="32" /></a><?php
        if ($esActivo&&$esModificable) { 
            /*if ($esBorraDoc) {
                $imgPDF="deleteIcon12";
                $fixPDF="delPDF{$dataIndex}";
                $typPDF="Elimina"; // preEliminaDoc
            } else {*/
                $imgPDF="refresh";
                $fixPDF="chgPDF{$dataIndex}";
                $typPDF="Reemplaza"; // preReemplazaDoc
                echo "<img src=\"imagenes/icons/deleteIcon12.png\" id=\"delPDF{$dataIndex}\" width=\"12\" height=\"12\" tipo=\"cfdi\" class=\"delFix12 va8 bgwhite round hidden\" title=\"Elimina CFDI-PDF\" onclick=\"preEliminaDoc(event);\">";
            //}
              ?><img src="imagenes/icons/<?=$imgPDF?>.png" id="<?= $fixPDF ?>" width="12" height="12" tipo="cfdi" class="delFix12 bgwhite round hidden" title="<?=$typPDF?> CFDI-PDF" onclick="pre<?=$typPDF?>Doc(event);"></div><?php
        }
    } else if($esActivo&&$xmlFileExists) {
        if ($_esDesarrollo) {
            $rmPdfList=glob($lookoutFilePath.$ubicacion."RM{$factId}_*.pdf"); // {$pdfName}
            $i=-1;
            foreach ($rmPdfList as $ri => $rmPdfFile) $i=$ri;
            if ($i>=0) {
                $rmPdfLastFile=$rmPdfList[$i];
                $restorePdfBlock="<span id=\"resPDF{$dataIndex}\" class=\"inblock delFix12 round btn12 bgResBSp hidden\"><img src=\"imagenes/icons/prev01_20b.png\" tipo=\"cfdi\" class=\"btn12 txttop\" filename=\"$rmPdfLastFile\" title=\"Recupera PDF\" onclick=\"restoreDoc(event);\"></span>";
            } else $restorePdfBlock="<!-- EMPTY RESTORE PDF BLOCK: ".$lookoutFilePath.$ubicacion."RM{$factId}_*.pdf -->";
            // $ubicacion."RM{$invId}_".$dt->format("ymdHi")."_".$pdfname
            echo "<div class=\"inblock\" onmouseenter=\"clrem('resPDF{$dataIndex}','hidden');\" onmouseleave=\"cladd('resPDF{$dataIndex}','hidden');\">";
        }
              ?><img src="imagenes/icons/invChk200.png" width="32" height="32" class="pointer" title="ANEXAR ARCHIVO PDF" onclick="generaFactura('<?= $xmlName ?>','<?= $yearCycle ?>','archivo');"/><?php
        if ($_esDesarrollo) {
            echo ($restorePdfBlock??"")."</div>";
        }
    }
    echo "<!-- eaFileExists '".($eaFilePath??"no_path")."':".($eaFileExists?"Y":"N").", esActivo:".($esActivo?"Y":"N").", esFactura:".($esFactura?"Y":"N").", hasEA:".($hasEA?"Y":"N").", notEA:".($notEA?"Y":"N").", disEA:".($disEA?"Y":"N").", esBloqueaEA:".($esBloqueaEA?"Y":"N").", esBorraDoc:".($esBorraDoc?"Y":"N").", esModificable:".($esModificable?"Y":"N").", esDesarrollo:".($_esDesarrollo?"Y":"N").", _esSistemas:".($_esSistemas?"Y":"N").", esCompras:".($esCompras?"Y":"N").", esProveedor:".($esProveedor?"Y":"N").", esPendiente:".($esPendiente?"Y":"N").", tieneAceptado:".($tieneAceptado?"Y":"N").", tieneContrarrecibo:".($tieneContrarrecibo?"Y":"N").", noContrarrecibo:".($noContrarrecibo?"Y":"N").", estaPagado:".($estaPagado?"Y":"N").", ISTATUS:".$istatusn.", autorizadaPor: ".(isset($autorizadaPor)?"'$autorizadaPor'":"null")." -->";
    if($eaFileExists) {
        $fixEA=""; $ctrEA="";
        if ($esActivo&&$esModificable) {
            /*if ($esBorraDoc) {
                $fixEA="delEA";
                $fixEACL="'delEA{$dataIndex}'";
                //$ctrEA=" onmouseenter=\"eaSwitch('$dataIndex', true);\" onmouseleave=\"eaSwitch('$dataIndex', false);\" tabindex=\"-1\"";
                // ,'refresh','Reemplaza'
                // ,'deleteIcon12','Elimina'
            } else { */
                $fixEA="chgEA"; $fixEACL="['delEA{$dataIndex}','chgEA{$dataIndex}']";
            //}
            
        ?><div id="eaBlk<?=$dataIndex?>" class="inblock" onmouseenter="clrem(<?= $fixEACL ?>,'hidden');" onmouseleave="cladd(<?= $fixEACL ?>,'hidden');"><?php
        }
              ?><a href="<?= $eaFilePath ?>" target="archivo" title="Entrada de Almacen"><img src="imagenes/icons/pdf200EA.png" width="32" height="32" /></a><?php
        if ($esActivo&&$esModificable) {
            /*if ($esBorraDoc) {
                $imgEA="deleteIcon12";
                //$fixEA="delEA";
                $typEA="Elimina"; // preEliminaDoc
            } else {*/
                $imgEA="refresh";
                //$fixEA="chgEA";
                $typEA="Reemplaza"; // preReemplazaDoc
                echo "<img src=\"imagenes/icons/deleteIcon12.png\" id=\"delEA{$dataIndex}\" width=\"12\" height=\"12\" tipo=\"ea\" class=\"delFix12 va8 bgwhite round hidden\" title=\"Elimina Entrada de Almacén\" onclick=\"preEliminaDoc(event);\">";
            //}
              ?><img src="imagenes/icons/<?=$imgEA?>.png" id="<?= $fixEA.$dataIndex ?>" width="12" height="12" tipo="ea" class="delFix12 bgwhite round hidden" title="<?=$typEA?> Entrada de Almacén" onclick="pre<?=$typEA?>Doc(event);"<?=$ctrEA?>></div><?php
        }
    } else if ($esActivo && $esFactura && ($notEA||($disEA&&$esBloqueaEA)) && ($_esSistemas || $esCompras || $esAltaFacturas || ($esProveedor && $esPendiente))) {
        $mouseScript=$esBloqueaEA?" onmouseenter=\"clrem('disEA{$dataIndex}','hidden');\" onmouseleave=\"cladd('disEA{$dataIndex}','hidden');\"":"";
        $imgFix=$notEA?"Plus1":"x";
        $clsFix=$notEA?" class=\"cellptr\" title=\"Anexar Entrada de Almacén\"":"";
        ?><div id="addEABlk<?= $dataIndex ?>" class="inblock"<?=$mouseScript?>><img src="imagenes/icons/pdf200EA<?= $imgFix ?>.png" id="addEAImg<?= $dataIndex ?>" width="32" height="32"<?= $clsFix ?> onclick="if(clhas(this,'cellptr'))appendNewEA(event);" /><?php
        if ($esBloqueaEA) {
            $imgDis=$notEA?"no":"si";
            $ttlDis=$notEA?"Bloquea":"Habilita";
            $doDis=$notEA?"off":"on";
              ?><img src="imagenes/icons/<?= $imgDis ?>Aplica.png" id="disEA<?= $dataIndex ?>" class="btn12 delFix12 bgwhite round hidden" title="<?= $ttlDis ?> Entrada de Almacén" do="<?= $doDis ?>" onclick="disableEA(event);"><?php
        }
              ?></div><?php
    }
    if ($tieneAntecedentes) {
              ?><a href="<?= $bgDocPath ?>" target="archivo" title="Antecedentes"><img src="imagenes/icons/pdf200Plus.png" width="32" height="32" /></a><?php
    }
    if ($tieneContrarrecibo) {
        $docImgName="crDoc32";
        $fixClick="";
        $crStateImg="";
        $ctrQry="folio=$ctrFolio";
        if (!$esProveedor) {
            if (empty($esCopia)&&(!$tieneSolicitud||$estaPagado||validaPerfil("Realiza Pagos"))&&($tieneSolicitud||isset($autorizadaPor[0]))) {
                global $tokObj; if(!isset($tokObj)){ require_once "clases/Tokens.php"; $tokObj=new Tokens(); }
                $tokData=$tokObj->getData("refId=$idContrarrecibo and modulo='contra_original' and status='activo'",0,"id, token");
                if (!isset($tokData[0])) {
                    $retTok = $tokObj->creaAccion($idContrarrecibo,[0],"contra_original",1,true);
                    $token = $retTok[0]["contra_original"];
                } else {
                    $token = $tokData[0]["token"];
                }
                $ctrQry.="&token=$token";
                $docImgName="cr2Ori32";
                $fixClick=" onclick=\"fixLink(this,'$idContrarrecibo');\"";
            } else {
                if (!$noRechazado) {
                    $symbolImgSrcName="cancelled";
                    $symbolTitle="CANCELADO";
                } else if ($estaPagado) {
                    $symbolText="$";
                    $symbolTitle="PAGADO";
                } else if ($tieneSolicitud) {
                    $symbolText="S";
                    $symbolTitle="CON SOLICITUD";
                } else if (isset($autorizadaPor[0])) {
                    if (isset($ctfData[0])) {
                        $numAuth=0;
                        foreach ($ctfData as $ctfRow) {
                            if (isset($ctfRow["autorizadaPor"][0])) $numAuth++;
                        }
                        if (isset($ctfData[$numAuth])) {
                            $symbolImgSrcName="checkGreen";
                            $symbolTitle="AUTORIZADO PARCIAL";
                            $symbolExtra=" nAuth='$numAuth' nAuthDB='$ctrNumAuth' nCntDB='$ctrNumCF'";
                        } else {
                            $symbolImgSrcName="dblCheckGreen";
                            $symbolTitle="AUTORIZADO";
                        }
                    }
                } else if (empty($esCopia)) {
                    $symbolImgSrcName="crossRed";
                    $symbolTitle="SIN AUTORIZAR";
                //} else {
                }
                $docImgName="cr2Cop32";
            }
        }
              ?><A href="consultas/Contrarrecibos.php?<?=$ctrQry?>"<?=$fixClick?> target="contrarrecibo" title="CONTRA-RECIBO <?=$ctrFolio?>"><IMG src="imagenes/icons/<?=$docImgName?>.png" width="32" height="32" /><?=$crStateImg?></A><?php
        if (isset($symbolImgSrcName[0])) {
            echo "<img src='imagenes/icons/{$symbolImgSrcName}.png' class='size8 marSE2' title='$symbolTitle'".($symbolExtra??"").">";
        } else if (isset($symbolText[0])) {
            echo "<span class='fontSmall inblock size8 marSE2' title='$symbolTitle'>$symbolText</span>";
        }
    }
    if ($tieneComplementoPago) { 
              ?><A href="<?=$tieneComplementoPagoPDF?$pdfCompPath:$xmlCompPath?>" target="archivo" title="COMPLEMENTO PAGO"><IMG src="imagenes/icons/pdf512.png" width="32" height="32" /></A><?php
    }
    if ($tieneComprobantePago) { 
              ?><A href="<?=$pdfPaymPath?>" target="archivo" title="COMPROBANTE PAGO"><IMG src="imagenes/icons/invChk200.png" width="32" height="32" class="grayscale2"/></A><?php
    }
          ?></td>
<?php
    if($modificaProc && false) { ?>
            <td class="centered noprint">
<?php   if($esRechazable) { ?>
              <div class="pointer" onclick="overlayConfirmation('<p>Seguro que desea rechazar <?=$descCompro?>?</p>', title='CONFIRMACI&Oacute;N', function() {submitAjax('<?= $delMessage ?>');})">
                <img src="imagenes/icons/reject96.png" width="32" height="32" />
              </div>
<?php   } ?>
            </td>
<?php
    }
    if($esEliminable) { ?>
            <td class="centered noprint">
                <div class="pointer" onclick="overlayConfirmation('<p>Se eliminar&aacute;n datos del comprobante, archivos y conceptos de este CFDI','CONFIRMACI&Oacute;N',function(){ submitAjax('KILL<?=$factId?>'); });">
                    <img src="imagenes/icons/trash32b.png" width="32" height="32"  onmouseover="this.src='imagenes/icons/trash32.png';" onmouseout="this.src='imagenes/icons/trash32b.png';" />
                </div>
            </td>
<?php
    } ?>
          </tr><!-- END ROW -->
<?php
}
$killScript="ekil(this);";
/*if (empty($message) && $_esDesarrollo) {
    $message = "Mensaje de Prueba";
    $title = "Prueba";
    $killScript="";
}*/
if (!$tieneRemision) $remisionScript="";
$messageScript=empty($message)?"":"overlayMessage({eName:'P',classList:'block clear',eText:'$message'}, '$title');";
echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"{$remisionScript}{$messageScript}pymExpand();isLoaded('$title');{$killScript}{$logScript}\">";
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //
function ftpErrorHandler($errno, $errstr, $errfile, $errline, $errcontext=null) {
    require_once "clases/Trace.php";
    $trcObj = new Trace();
    $trcObj->errorHandler($errno, $errstr, $errfile, $errline, $errcontext);
}
require_once "configuracion/finalizacion.php";
