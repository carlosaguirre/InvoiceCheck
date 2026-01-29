<?php
clog2ini("configuracion.altafactura02_insertxml");

$baseData=["file"=>getShortPath(__FILE__)];
$sysPath=$_SERVER["DOCUMENT_ROOT"];
$webPath = $_SERVER["HTTP_ORIGIN"];
$facturas = $_POST["factura"]??[];
try {
    $numFacturas=count($facturas);
} catch (TypeError $e) {
    $numFacturas=0;
}
if ($numFacturas>0) doclog("INSERTXML BEGIN","altafac",$baseData+["line"=>__LINE__,"numFacturas"=>$numFacturas,"countPosts"=>count($_POST),"countPostRecursive"=>count($_POST,COUNT_RECURSIVE),"countPostLeafs"=>count_terminals($_POST)]);
if (isset($_FILES["pdffile"])) {
    $pdffiles = getFixedFileArray($_FILES["pdffile"]);
    $uploadedFilenames=[];
    foreach ($pdffiles as $file) {
        $uploadedFilenames[]="$file[name] ($file[type]) $file[error]";
    }
    doclog("INSERTXML PDFs","altafac",$baseData+["line"=>__LINE__,"files"=>$uploadedFilenames]);
}
if (isset($_FILES["eafile"])) {
    $eafiles = getFixedFileArray($_FILES["eafile"]);
    $uploadEAFilenames=[];
    foreach ($eafiles as $ifile) {
        $uploadEAFilenames[]="$ifile[name] ($ifile[type]) $ifile[error]";
    }
    doclog("INSERTXML EAs","altafac",$baseData+["line"=>__LINE__,"eafiles"=>$uploadEAFilenames]);
}
$errorLog = "";
require_once "clases/FTP.php";
$ftpObj = MiFTP::newInstanceGlama();

DBi::autocommit(FALSE);
//clog2("DBI autocommit = FALSE");
//if (isset($pdffiles)) clog2("PDFFILES:\n".arr2str($pdffiles));
for ($idx = 0; $idx < $numFacturas; $idx++) {
    $factura = $facturas[$idx];
//clog2("FACTURA $idx:\n".arr2str($factura));

    $uuid = ""; if (isset($factura['uuid'])) $uuid=strtoupper($factura['uuid']);
    $folio = ""; if (isset($factura['ffolio'])) $folio = $factura["ffolio"];
    $fileSuffix=""; if (isset($factura['ffsfx'])) $fileSuffix=$factura["ffsfx"];
    $ffecha = ""; if (isset($factura['ffecha'])) $ffecha = $factura["ffecha"];
    $frfc = ""; if (isset($factura['frfc'])) $frfc = $factura["frfc"];
    $fcodigo=""; if (isset($factura['fcodigo'])) $fcodigo=$factura["fcodigo"];
    $pedido = ""; if (isset($factura['pedido'])) $pedido = $factura["pedido"];
    $remision = ""; if (isset($factura['remision'])) $remision = str_replace(' ', '', $factura["remision"]);
    $fpath = ""; if (isset($factura['fpath'])) $fpath = $factura["fpath"];
    $tipoComprobante = ""; if (isset($factura['tipoComprobante'])) $tipoComprobante = $factura["tipoComprobante"];
    $esPago = ($tipoComprobante==="PAGO");
    $esNota = ($tipoComprobante==="EGRESO");
    $esTraslado = ($tipoComprobante==="TRASLADO");
//if ($esPago) clog2("ES PAGO");
//if ($esNota) clog2("ES NOTA");
    require_once "clases/Facturas.php";
    $invObj = new Facturas();
    $invData=$invObj->getData("uuid='$uuid'");
    if (isset($invData[0])) $invData=$invData[0];
    $newPdfName=null;
    if (isset($invData["statusn"])) {
        // Se ignora lo siguiente, si statusn no es null, es muy probable que hayan refrescado la página y el proceso se ejecuta de nuevo lo que provoca que se inserten nuevamente los conceptos.
        if (!is_null($invData["statusn"])) {
            // toDo: Se podría verificar que no existan conceptos relacionados, si es factura o nota, o si es pago, que ya existan los pagos
            // Por lo pronto nada más se ignora
            continue;
        }
        // toDo: Posiblemente habría que revisar el proceso, que la última accion fuera altafactura01
        $fstn=+$invData["statusn"];
        $fid=$invData["id"];
        if (!$esPago&&!$esTraslado) {
            if ($fstn!==0) {
                $mensajeLocal = "Su sesión está fuera de tiempo, el proceso ya está en progreso.";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                if (isset($errorMessage[0])) $errorMessage .= ", ";
                $errorMessage .= $uuid;
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                clog2("ERROR0 en Factura o Nota. uuid=$uuid: $mensajeLocal");
                doclog("INSERTXML ERROR: Factura o Nota con Status Avanzado","error",$baseData+["line"=>__LINE__,"idx"=>$idx, "uuid"=>$uuid, "statusn"=>$fstn, "factId"=>$fid]);
                continue;
            }
        } else {
            if ($fstn!==1) {
                $mensajeLocal = "Su sesión está fuera de tiempo, el proceso ya está en progreso.";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                if (isset($errorMessage[0])) $errorMessage .= ", ";
                $errorMessage .= $uuid;
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                clog2("ERROR0 en Pago o Traslado. uuid=$uuid: $mensajeLocal");
                doclog("INSERTXML ERROR: Pago o Traslado con Status Avanzado","error",$baseData+["line"=>__LINE__,"idx"=>$idx, "uuid"=>$uuid, "statusn"=>$fstn, "factId"=>$fid]);
                continue;
            }
        }
    } else {
        // toDo: debería existir, por el momento no se valida
    }
    $comprob=$esTraslado?"Traslado":($esPago?"Recibo":($esNota?"Nota":"Factura"));
// TODO: Corroborar $idx. Confirmar si se ingresan unas facturas con pdf y otras sin pdf pueden faltar $idx y se produce error de offset
// ver LOGS/210128/error.log facturas tiene 3 registros pero pdffiles solo 2, aunque ambos registros tienen error 4: UPLOAD_ERR_NO_FILE
    if (isset($pdffiles[$idx])) $pdffile = $pdffiles[$idx];
    if (isset($eafiles[$idx])) $eafile=$eafiles[$idx];
    else $eafile=null;
    if (isset($fileSuffix[0])) $folioFix=$fileSuffix;
    else {
        if (empty($folio)) $folioFix = substr($uuid,-10);
        else {
            $folioFix = $folio;
            if (isset($folioFix[10])) $folioFix = substr($folioFix, -10);
        }
        if ($esNota) $folioFix="NC_".$folioFix;
        else if ($esPago) $folioFix="RP_".$folioFix;
    }
    if (!isset($factura["errmsg"])) $factura["errmsg"]="";
    if (!$esPago && empty($pedido)) {
        $mensajeLocal = "No se indic&oacute; pedido";
        $factura["errmsg"] .= "<div>$mensajeLocal</div>";
        /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
        else */
        if (isset($errorMessage[0])) $errorMessage .= ", ";
        $errorMessage .= "$folioFix";
        $factura["success"]=false;
        $facturas[$idx] = $factura;
        //DBi::rollback(); // aun no hay transacciones
        clog2("ERROR1 con rollback en $comprob folio $folioFix: $mensajeLocal");
        doclog("INSERTXML ERROR: $comprob sin Pedido","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid]);
        continue;
    } else if (!$esPago && preg_match('/\s/',$pedido)) {
        $mensajeLocal = "El pedido no puede tener espacios.";
        $factura["errmsg"] .= "<div>$mensajeLocal</div>";
        /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
        else */
        if (isset($errorMessage[0])) $errorMessage .= ", ";
        $errorMessage .= "$folioFix";
        $factura["success"]=false;
        $facturas[$idx] = $factura;
        //DBi::rollback();
        clog2("ERROR2 con rollback en $comprob folio $folioFix: $mensajeLocal");
        doclog("INSERTXML ERROR: $comprob con espacios en Pedido","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"pedido"=>$pedido]);
        continue;
    }
    if (isset($pedido[0])) {
        $oldPedido=$pedido;
        //$pedido=preg_replace('/\x921854/u', "-", $pedido);
        //$pedido=preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $pedido);
        $pedido=preg_replace('/\xe2\x88\x92/',"-",$pedido);
        $pedido=preg_replace('/[\x00-\x1f]/', '?', $pedido);
        if ($oldPedido!==$pedido) doclog("INSERTXML: REEMPLAZO DE CARACTERES EN PEDIDO","altafac",$baseData+["line"=>__LINE__,"uuid"=>$uuid,"oldPedido"=>$oldPedido,"newPedido"=>$pedido]);
        //$pedido=preg_replace('/[\xF0-\xF7].../s', '', $pedido);
    }
    if (isset($remision[0])) {
        $oldRemision=$remision;
        $remision=preg_replace('/\xe2\x88\x92/',"-",$remision);
        $remision=preg_replace('/[\x00-\x1f]/', '?', $remision);
        if (in_array($remision, ["S/N","SIN REMISION"])) $remision="S/REMISION";
        if ($oldRemision!==$remision) doclog("INSERTXML: REEMPLAZO DE CARACTERES EN REMISION","altafac",$baseData+["line"=>__LINE__,"uuid"=>$uuid,"oldRemision"=>$oldRemision,"newRemision"=>$remision]);
        $codPrv=$invData["codigoProveedor"]??"";
        if (isset($codPrv[0]) && $remision!=="S/REMISION") {
            $invChkData=$invObj->getData("codigoProveedor='$codPrv' and remision='$remision'",0,"folio");
            // NINGUNA REMISION PUEDE ASIGNARSE A DOS FACTURAS
            if (isset($invChkData[0]["folio"][0])) {
                $mensajeLocal = "La remisión ya está asignada a la factura '".$invChkData[0]["folio"]."'";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                clog2("ERROR2 con rollback en $comprob folio $folioFix: $mensajeLocal");
                doclog("INSERTXML ERROR: $comprob con remision repetida","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"remision"=>$remision,"facturaActual"=>$invData,"facturaRemision"=>$invChkData]);
                continue;
            }
        }
    } else $remision="S/REMISION";
    
    $newStatusN=Facturas::STATUS_PENDIENTE;
    if (empty($pdffile)) {                         // El PDF es opcional normalmente
        if ($esPago && empty($factura["pname"])) { // Excepto cuando el tipo de comprobante es pago. Contemplar si fue agregado en la primer pantalla
            $mensajeLocal = "Es necesario incluir el archivo PDF correspondiente al Recibo de Pago. Por favor realice el alta de este recibo nuevamente.";
            $factura["errmsg"] .= "<div>$mensajeLocal</div>";
            /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
            else */
            if (isset($errorMessage[0])) $errorMessage.=", ";
            $errorMessage.="$folioFix";
            $factura["success"]=false;
            $facturas[$idx]=$factura;
            //DBi::rollback();
            doclog("INSERTXML ERROR: Pago sin PDF","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid]);
            continue;
        }
    } else {
        if (isset($pdffile["error"])) $ferr = $pdffile["error"];
        else $ferr = UPLOAD_ERR_OK; // 0
        if ( $ferr!==UPLOAD_ERR_OK && $ferr!==UPLOAD_ERR_NO_FILE ) { // 4
            require_once "clases/Archivos.php";
            $mensajeLocal=Archivos::getUploadError($pdffile);
            $factura["errmsg"] .= "<div>$mensajeLocal</div>";
            /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
            else */
            if (isset($errorMessage[0])) $errorMessage .= ", ";
            $errorMessage .= "$folioFix";
            $factura["success"]=false;
            $facturas[$idx] = $factura;
            //DBi::rollback();
            clog2("ERROR3 con rollback en $comprob folio $folioFix: $mensajeLocal");
            doclog("INSERTXML ERROR: PDF en $comprob: $mensajeLocal","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid]);
            continue;
        }
        if ($ferr===UPLOAD_ERR_OK) {
            if (!isset($pdffile["name"])) {
                $mensajeLocal = "No se reconoce el archivo PDF";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
                else */
                if (isset($errorMessage[0])) $errorMessage .= ", ";
                $errorMessage .= "$folioFix";
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                //DBi::rollback();
                clog2("ERROR4 con rollback en $comprob folio $folioFix: $mensajeLocal");
                doclog("INSERTXML ERROR: PDF en $comprob sin nombre","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"file"=>["name"=>$pdffile["name"]??"","type"=>$pdffile["type"]??"","error"=>$pdffile["error"]??""]]);
                continue;
            }
            if (!isset($pdffile["type"])) {
                $mensajeLocal = "No se reconoce el formato del archivo PDF";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
                else */
                if (isset($errorMessage[0])) $errorMessage .= ", ";
                $errorMessage .= "$folioFix";
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                //DBi::rollback();
                clog2("ERROR5 con rollback en $comprob folio $folioFix: $mensajeLocal");
                doclog("INSERTXML ERROR: PDF en $comprob sin tipo","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"file"=>["name"=>$pdffile["name"]??"","type"=>$pdffile["type"]??"","error"=>$pdffile["error"]??""]]);
                continue;
            }
            if ($pdffile["type"]!=="application/pdf") {
                $mensajeLocal = "El archivo de la factura no tiene formato PDF";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
                else */
                if (isset($errorMessage[0])) $errorMessage .= ", ";
                $errorMessage .= "$folioFix";
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                //DBi::rollback();
                clog2("ERROR6 con rollback en $comprob folio $folioFix: $mensajeLocal");
                doclog("INSERTXML ERROR: Archivo en $comprob no es PDF","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"file"=>["name"=>$pdffile["name"]??"","type"=>$pdffile["type"]??"","error"=>$pdffile["error"]??""]]);
                continue;
            }
            if (!isset($pdffile["tmp_name"])) {
                $mensajeLocal = "No se cargó el archivo PDF";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
                else */
                if (isset($errorMessage[0])) $errorMessage .= ", ";
                $errorMessage .= "$folioFix";
                $factura["success"]=false;
                $facturas[$idx] = $factura;
                //DBi::rollback();
                clog2("ERROR7 con rollback en $comprob folio $folioFix: $mensajeLocal");
                doclog("INSERTXML ERROR: Archivo en $comprob sin TMP","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"file"=>["name"=>$pdffile["name"]??"","type"=>$pdffile["type"]??"","error"=>$pdffile["error"]??""]]);
                continue;
            }
            $newPdfName = $folioFix.$frfc;
            $pdfFullPath = $sysPath.$fpath.$newPdfName.".pdf";
            if (move_uploaded_file($pdffile["tmp_name"], $pdfFullPath)) {
                chmod($pdfFullPath,0777);
                $factura["pname"]=$newPdfName;
//clog2("MOVED uploaded file from '$pdffile[tmp_name]' to '$fpath.$newPdfName.pdf'");
            } else $newPdfName = null;
        } else $newPdfName = null;
    }
    if ($esPago) {
        $pagos = $factura["pagodocto"];
        // Búsqueda de movimiento bancario en mssql
        try {
            require_once "clases/DBPDO.php";
            if (DBPDO::validaAceptacion($invData["rfcGrupo"], $invData["fechaReciboPago"], $invData["saldoReciboPago"])) {
                $newStatusN|=Facturas::STATUS_ACEPTADO;
            } // else // Casos para rechazar automaticamente
        } catch (Exception $e) {
            doclog("FALLA ACEPTACION DE PAGO","error",["rfcReceptor"=>$invData["rfcGrupo"], "fechaReciboPago"=>$invData["fechaReciboPago"], "saldoReciboPago"=>$invData["saldoReciboPago"],"error"=>getErrorData($e)]);
        }
        $newStatus=Facturas::statusnToDetailStatus($newStatusN,"P");
        $fieldarray = [
            "uuid"=>$uuid,
            "status"=>$newStatus,
            "statusn"=>$newStatusN
        ];
        doclog("VALIDA COMPLEMENTO DE PAGO","pagos",["invData"=>$invData,"fieldarray"=>$fieldarray]);
    } else {
        $conceptos = $factura["concepto"];
        $numConceptos = count($conceptos);
        $newStatus=Facturas::statusnToDetailStatus($newStatusN,$tipoComprobante);
        $fieldarray = [
//                "id"=>$factura["id"],
            "uuid"=>$uuid,
            "pedido"=>strtoupper($pedido),
            "remision"=>strtoupper($remision),
            "status"=>$newStatus,
            "statusn"=>$newStatusN
        ];
        $fieldarray["fechaCaptura"]=$_now["now"];
        if (!$esNota && !$esTraslado) {
            if (!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj = new Proveedores();
            }
            $prvData=$prvObj->getData("rfc='$frfc' and status not in (\"inactivo\",\"eliminado\")",0,"credito");
            if (!isset($prvData[0])) {
                global $query;
                clog2("ERROR8 con rollback en rfc '$frfc' no encontrado");
                doclog("No se encontró el proveedor con rfc '$frfc'","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"query"=>$query, "errors"=>DBi::$errors]);
                continue;

            }
            $credito=+$prvData[0]["credito"]??0;
            if ($credito<=0) $venceFecha=$_now["now"];
            else {
                $wkd=+date("w");
                if ($wkd<2) $wkd+=6; else $wkd--;
                $creditoFix=$credito-($credito%7)+7-$wkd; // Se aumenta el numero de dias de credito mas los dias q faltan para ser lunes
                $venceTS=strtotime($_now["now"]."+ $creditoFix days");
                $venceFecha=date("Y-m-d",$venceTS);
            }
            $fieldarray["fechaVencimiento"]=$venceFecha;
        }
    }
    
    if (empty($newPdfName)) {
        if (!empty($factura["pname"])) $newPdfName=$factura["pname"];
    } else {
        doclog("REASIGNAR PDF", "pdf", $baseData+["line"=>__LINE__,"pdf"=>$newPdfName]);
        $fieldarray["nombreInternoPDF"] = $newPdfName;
        $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    }
    if (!empty($factura["oname"])) {
        $xmlFileName=$factura["oname"];
    }
    
    ajustarRegistroFactura($fieldarray);
    if ($esPago) {
        if (!isset($lookoutFilePath)) {
            $lookoutFilePath = "";
            if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
            else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
        }
        if (!isset($rutaAvance)) {
            $rutaAvance = $ftp_supportPath.$factura["falias"]."/TPUBLICO/";
        }
        $pdfUploadSuccess=true;
    } else {
        $factura["pedido"] = $fieldarray["pedido"];
        $factura["remision"] = $fieldarray["remision"];
    }
    if (isset($eafile["name"][0])) {
        require_once "clases/Archivos.php";
        $eaerr=Archivos::getUploadError($eafile,"application/pdf");
        if (isset($eaerr[0])) {
            doclog("EAFile ERROR1: Upload Error","error",["msg"=>$eaerr,"file"=>$eafile]);
        } else {
            $eacp=trim(str_replace("-","",$fcodigo));
            $eafecha=substr(trim(str_replace("-", "", $ffecha)), 2, 6);
            $eafolio=$folioFix;
            if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
            $newEAFileName = "EA_{$eacp}_{$eafolio}_{$eafecha}";
            $eaFullName = $sysPath.$fpath.$newEAFileName.".pdf";
            $eaok=true;
            if (!is_dir($sysPath.$fpath)) {
                if(mkdir($sysPath.$fpath,0777,true)) chmod($sysPath.$appPath,0777);
                else {
                    $eaok=false;
                    doclog("EAFile ERROR2: CANT CREATE PATH","error",["abspath"=>$sysPath.$fpath,"newName"=>$newEAFileName,"file"=>$eafile]);
                }
            }
            if ($eaok) {
                if (file_exists($eaFullName)) {
                    rename($eaFullName, $sysPath.$fpath.$newEAFileName.date("_YmdHis",filemtime($eaFullName)).".pdf");
                    sleep(3);
                }
                if (move_uploaded_file($eafile["tmp_name"], $eaFullName)) {
                    chmod($eaFullName,0666);
                    $fieldarray["ea"]="1";
                } else doclog("EAFile ERROR3: CANT MOVE UPLOADED FILE","error",["absname"=>$eaFullName,"","file"=>$eafile]);
            }
        }
    }
//clog2("SAVE RECORD $comprob $folioFix");
    $fsuccess = $invObj->saveRecord($fieldarray);
    $factura["dblog"] = $invObj->log;
    if ($fsuccess) {
        doclog("INSERTXML SAVE","altafac",$baseData+["line"=>__LINE__,"idx"=>$idx, "query"=>$query, "lastId"=>$invObj->lastId]);
        $factura["id"] = $invObj->lastId;
        if ($esPago) {
            global $cpyObj;
            if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
            $cpyObj->fixInvStats($factura["id"]);
            doclog("Actualiza Pagos","pagos",["id"=>$factura["id"],"fieldarray"=>$fieldarray,"pasos"=>$cpyObj->fixedIdList["Pasos"]]);
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
            }
            $prcObj->cambioFactura($factura["id"], $fieldarray["status"], $username, $now["now"], "altafactura02");
        } else {
            $columns = ["idFactura", "codigoArticulo", "cantidad", "unidad", "claveUnidad", "claveProdServ", "descripcion", "precioUnitario", "importe", "importeDescuento", "impuestoTraslado", "impuestoRetenido"]; // "version" (default 3.3), "status" (default activo)
            $conceptArrays = [];
            for ($cix=0; $cix < $numConceptos; $cix++) {
                if (empty($conceptos[$cix]) || empty($conceptos[$cix]['codigo'])) {
                    //clog2("// EMPTY CONCEPT\n");
                    $mensajeLocal = "No se indic&oacute; un c&oacute;digo de art&iacute;culo. Debe elegir el archivo nuevamente";
                    $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                    /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
                    else */
                    if (isset($errorMessage[0])) $errorMessage .= ", ";
                    $errorMessage .= "$folioFix";
                    $factura["success"]=false;
                    $facturas[$idx] = $factura;
                    DBi::rollback();
                    clog2("ERROR9 con rollback en $comprob folio $folioFix: $mensajeLocal");
                    doclog("INSERTXML ERROR: Concepto sin código","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"cix"=>$cix,"numConceptos"=>$numConceptos,"concepto"=>$conceptos[$cix]]);
                    continue 2;
                } else if (preg_match('/\s/',$conceptos[$cix]['codigo'])) {
                    //clog2("// SPACES IN CONCEPT\n");
                    $mensajeLocal = "El c&oacute;digo de concepto no debe incluir espacios. Tiene que elegir el archivo nuevamente.";
                    $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                    /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
                    else */
                    if (isset($errorMessage[0])) $errorMessage .= ", ";
                    $errorMessage .= "$folioFix";
                    $factura["success"]=false;
                    $facturas[$idx] = $factura;
                    DBi::rollback();
                    clog2("ERROR10 con rollback en $comprob folio $folioFix: $mensajeLocal");
                    doclog("INSERTXML ERROR: Código en concepto invalido (tiene espacios)","error",$baseData+["line"=>__LINE__,"idx"=>$idx,"folio"=>$folioFix,"uuid"=>$uuid,"cix"=>$cix,"concepto"=>$conceptos[$cix]]);
                    continue 2;
                } else {
                    $ccodigo=$conceptos[$cix]['codigo'];
                    $cdescripcion=strtok(trim($conceptos[$cix]['descripcion']),"\r\n");
                    if (!isset($cdescripcion[0])) $cdescripcion=strtok("\r\n");
                    if ($cdescripcion===FALSE) $cdescripcion="";
                    else {
                        if (isset($cdescripcion[299])) {
                            $cdescripcion = substr($cdescripcion, 0, 296)."...";
                        }
                        //$cdescripcion=filter_var($cdescripcion, FILTER_SANITIZE_ADD_SLASHES);
                        //
                        $cdescripcion=str_replace("\\'","'",DBi::real_escape_string($cdescripcion));
                        $cdescripcion=preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $cdescripcion);
                    }
                    //Los apostrofes se reparan dentro de insertMultipleRecords
//INSERT INTO conceptos
//(idFactura,codigoArticulo,cantidad,unidad,claveUnidad,claveProdServ,descripcion,precioUnitario,importe,importeDescuento,impuestoTraslado,impuestoRetenido) VALUES
//('118363','2440153','6','pz','H87','40171600','TUBO P/ NIVEL  BCO.  5/8\"   X   3   MTS.','1641.94','9851.64','1970.328','1261.01','0')
                    //clog3("Descripcion: $cdescripcion");
                    if (strlen($ccodigo)>=45) {
                        $ccodigo = substr($ccodigo, 0, 45);
                    }
                    if (isset($ccodigo[0]) && (mb_strpos($ccodigo, "−")!==FALSE||mb_strpos($ccodigo, "&MINUS;")!==FALSE)) {
                        $ccodigo = str_replace(["−","&MINUS;"], "-", $ccodigo);
                    }
                    $cdescuento=0;
                    $ctraslado=0;
                    $cretencion=0;
                    if (isset($conceptos[$cix]['descuento'])) $cdescuento = round(+$conceptos[$cix]['descuento'],3);
                    if (isset($conceptos[$cix]['traslado'])) $ctraslado = round(+$conceptos[$cix]['traslado'],3);
                    if (isset($conceptos[$cix]['retencion'])) $cretencion = round(+$conceptos[$cix]['retencion'],3);
                    //$epsilon=0.015; //0.000001;
                    $cimporte = round(+$conceptos[$cix]['importe'],3);
                    //$ccTot = +$conceptos[$cix]['calcTotal'];
                    //if (abs($ccTot - $cimporte + $cdescuento + $cretencion - $ctraslado) > $epsilon) {  } // El total es calculado previamente, debe de coincidir siempre

                    $conceptArrays[] = [$factura["id"], strtoupper(htmlentities($ccodigo)), $conceptos[$cix]['cantidad'], $conceptos[$cix]['unidad'], $conceptos[$cix]['claveUnidad'], $conceptos[$cix]['claveProdServ'], $cdescripcion, $conceptos[$cix]['valorUnitario'], $cimporte, $cdescuento, $ctraslado, $cretencion];
                }
            }
            //clog2("Id: $factura[id], Folio: $folioFix, Pedido: $pedido\nColumns: ".json_encode($columns)."\nValues: ".json_encode($conceptArrays));
            require_once "clases/Conceptos.php";
            $cptObj = new Conceptos();
//clog2("INSERT MULTIPLE conceptos");
            if ($cptObj->insertMultipleRecords($columns, $conceptArrays)) {
                global $prcObj;
                $factura["dblog"] .= "\n".$cptObj->log;
                if (!isset($prcObj)) {
                    require_once "clases/Proceso.php";
                    $prcObj = new Proceso();
                }
                doclog("INSERTXML Conceptos","altafac",$baseData+["line"=>__LINE__,"numConceptos"=>$numConceptos,"numGuardados"=>count($conceptArrays)]);
                $prcObj->cambioFactura($factura["id"], "Pendiente", $username, $_now["now"], "altafactura02");
                $factura["dblog"] .= "\n".$prcObj->log;
            } else {
                $mensajeLocal = "Error al guardar los conceptos de la factura.";
                $factura["errmsg"] .= "<div>$mensajeLocal</div>";
                $factura["dblog"] .= "\n".$cptObj->log;
                clog2("ERROR11 con rollback. ".DBi::$errno.": ".DBi::$error."\n$query");
                doclog("INSERTXML ERROR al insertar Conceptos","error",$baseData+["line"=>__LINE__,"idx"=>$idx, "folio"=>$folioFix, "uuid"=>$uuid, "query"=>$query, "errors"=>DBi::$errors]);
            }
        }
    } else {
        doclog("INSERTXML SAVE ERROR","error",$baseData+["line"=>__LINE__,"idx"=>$idx, "folio"=>$folioFix, "uuid"=>$uuid, "query"=>$query, "errors"=>DBi::$errors, "ERRNO"=>DBi::$errno, "ERROR"=>DBi::$error]);
        if (empty(DBi::$errors)) $mensajeLocal="No hubo cambios en la factura";
        else $mensajeLocal = "No pudo guardarse la factura.";
        //$errArr = $invObj->errors;
        //foreach($errArr as $err) {
        //    $mensajeLocal .= " $err.";
        //}
        $factura["errmsg"] .= "<div>$mensajeLocal</div>";
        clog2("// ERROR12 GUARDANDO $comprob: ".DBi::$errno.": ".DBi::$error."\n$query");
    }
    if (!empty($factura["errmsg"])) {
//            $factura["errmsg"] .= "<!-- \n$factura[dblog] -->";
        /* if (empty($errorMessage)) $errorMessage="<p class='fontRelevant margin20 centered'>";
        else */
        if (isset($errorMessage[0])) $errorMessage .= ", ";
        if (empty($folio)) $errorMessage .= "uuid-$folioFix";
        else $errorMessage .= "$folio";
        // appendLog("Error en factura $factura[id]) UUID $uuid: $factura[errmsg]\nLOG:\n$factura[dblog]");
        
        $errorLog .= "<tr class='noApply nohover'><td class='noApply nohover'>Pedido $pedido<td>$factura[errmsg]</td></tr>";

        //$errorLog .= "<!-- \\n".str_replace(["\n", "\""], ["\\n", "\\\""], $factura["dblog"])."\\n -->\\n";
//clog2($factura["dblog"]);
        // \\nErrLog: $factura["dblog"]\\n
        $factura["success"]=false;
        DBi::rollback();
clog2("Rollback en $comprob folio $folioFix: $factura[errmsg]");
    } else {
        /* if (empty($resultMessage)) $resultMessage = "<p class='margin20 centered'>";
        else */
        if (isset($resultMessage[0])) $resultMessage .= ", ";
        $resultMessage .= "$folioFix";
        $factura["success"]=true;
        DBi::commit();
//clog2("COMMIT en $comprob folio $folioFix.\nDB LOG:\n".$factura["dblog"]."\n");
    }
    $facturas[$idx] = $factura;
}
DBi::autocommit(TRUE);
//clog2("DBI autocommit TRUE");

if (!empty($errorMessage)) {
    $isSingleError = (strpos($errorMessage,",")===false);
    if ($isSingleError) $errorMessage = ($esPago||$esTraslado?"El":"La")." $comprob con folio $errorMessage no fue agregad".($esPago?"o":"a");
    else $errorMessage = "Los comprobantes con folio $errorMessage no fueron agregados";
    $errorMessage ="<P class='fontRelevant margin20 centered'>$errorMessage.</P>";
    if ($isSingleError) $errorMessage.="<SPAN class='centered'>$mensajeLocal</SPAN><BR><BR>";
    else $errorMessage.="<table class='noApply width80 centered'>$errorLog</table><BR>";
    //clog("ERROR EN AL MENOS UN PEDIDO!", 3);
    //clog("ERRMSG: $factura[errmsg]", 3);
    //clog("ERRORMESSAGE: $errorMessage", 3);
} else if (!empty($resultMessage)) {
    $isSingleResult = (strpos($errorMessage,",")===false);
    if ($isSingleResult) $resultMessage = ($esPago||$esTraslado?"El":"La")." $comprob con folio $resultMessage fue agregad".($esPago?"o":"a");
    else $resultMessage = "Los comprobantes con folio $resultMessage fueron agregados";
    $resultMessage = "<P class='margin20 centered'>$resultMessage satisfactoriamente</P>";
    //clog("PEDIDOS GENERADOS SATISFACTORIAMENTE!", 3);
} else {
    clog("NOTHING HAPPENED!", 3);
}
clog2end("configuracion.altafactura02_insertxml");
