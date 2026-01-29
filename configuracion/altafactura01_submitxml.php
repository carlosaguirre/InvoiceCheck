<?php
clog2ini("configuracion.altafactura01_submitxml");
clog1seq(1);
$time_start=lapse(true);
$pfx="|".$username;
$files = getFixedFileArray($_FILES["xmlfiles"]??null);
if ($_esDesarrollo&&!isset($files[0])) {
    require_once "clases/Config.php";
    $sharePath=(Config::get("project","sharePath")??"..\\");
    $files=[["type"=>"text/xml","name"=>"cfdiPrueba.xml","tmp_name"=>$sharePath."fallidos\\SIGN_A06473.xml","error"=>UPLOAD_ERR_OK],["type"=>"application/pdf","name"=>"cfdiPrueba.pdf","tmp_name"=>$sharePath."fallidos\\SIGN_A06473.pdf","error"=>UPLOAD_ERR_OK]];
}
$pdfs = []; // Usar el nombre del pdf, sin extension pdf, como llave
$hayPagos=false;
$hayFacturas=false;
$hayNotas=false;
$hayTraslados=false;
$faltanPDF=false;
if (count($files)>0) {
    // Separar PDFs de XMLs
    $uploadedFilenames=[];
    $xmls = [];
    foreach ($files as $fidx=>$file) {
        $filetext="$file[name] ".sizeFix($file["size"]);
        if (empty($file["error"])) {
            if ($file["type"]==="application/pdf") {
                $pdfname = substr($file["name"],0,-4);
                $pdfs[$pdfname]=$file;
            } else if ($file["type"]==="text/xml") {
                $xmls[]=$file;
            } else {
                //if (!isset($logObj)) {
                //    require_once "clases/Logs.php";
                //    $logObj=new Logs();
                //}
                //$logObj->agrega(getUser()->id, "ALTA FACTURA", "WRONG FILEINFO:".json_encode($file));
                $filetext.=" WRONGTYPE $file[type]";
            }
        } else $filetext.=" ERROR $file[error]";
        $uploadedFilenames[]=$filetext;
    }
    $validPDFIdx=[];
    $files=$xmls;
    $processed=0;
    $approved=0;
    $numFacturas=count($files);
    if (!isset($errorMessage[0])) {
        $seconds = 120*$numFacturas;
        set_time_limit($seconds);
    }
    $beginArray=["STL"=>0,"lapse"=>round(lapse(),3,PHP_ROUND_HALF_DOWN),"numXML"=>$numFacturas,"timeLimit"=>$seconds??0,"files"=>$uploadedFilenames];
    if (isset($errorMessage[0])) $beginArray["error"]=$errorMessage;
    doclog("SUBMITXML BEGIN","altafac",$beginArray);
    global $file;
    $file=null;
    $factuuids = [];
    $factnwnms = [];
    $loopTime=$time_start;
    if (!isset($errorMessage[0])) for ($idx=0; $idx < $numFacturas; $idx++) {
        $processed++;
        $file = $files[$idx];
        $conArr = [];
        $file["enough"]=TRUE;
        $file["errmsg"]="";
        $file["success"]=FALSE;
        $tgtFullPath = "";
        $tgtPath = "";
        $fileSuffix = "";
        $fileSuffix2 = "";
        $pdfName1 = "";
        $originalName=null;
        $uuid = "";
        // Validacion de datos de carga de archivos
        // Validacion de XML y extraccion de elementos importantes
        if (isFileUploadDataValid()) {
            $GLOBALS["doNormalizeUtf8Chars"]=1;
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByFileName($file["tmp_name"], $file["name"], $errorMessage, $file["errmsg"], $file["enough"], $file["dblog"]);
            if ($cfdiObj!==null) {
                $file["xml"] = $cfdiObj;
                $seconds = 10*60;
                set_time_limit($seconds);
                doclog("SUBMITXML CFDI","altafac",["STL"=>1,"loopTime"=>lapse($loopTime),"idx"=>$idx,"filename"=>$file["name"],"timeLimit"=>$seconds]);
                $loopTime=lapse(true);
                $file["xml"]->validar();
                $loopTime2=lapse($loopTime);
                doclog("SUBMITXML VALIDATED","altafac",["loopTimeVal"=>$loopTime2,"lastError"=>CFDI::getLastError(),"errorMessage"=>$errorMessage,"errorList"=>$file["errmsg"],"errorLog"=>$file["dblog"]]);
            } else {
                $file["xml"]=null;
                if (!isset($errorMessage[0])) $errorMessage="Extraccion de datos de XML fallida: $file[name]";
                $yr=$_now["Y"];
                if (empty($yr)) $yr=""; else $yr.="/";
                $mn=$_now["m"];
                if (empty($mn)) $mn=""; else $mn.="/";
                $errPath="archivos/error/{$yr}{$mn}";
                if(!is_dir($errPath) && mkdir($errPath, 0777, true))
                    copy("archivos/index.php", "{$errPath}index.php");
                if (is_dir($errPath)) {
                    $errFilePath=$errPath.$file["name"];
                    move_uploaded_file($file["tmp_name"], $errFilePath);
                    chmod($errFilePath,0666);
                    if (isset(CFDI::$lastException)) {
                        anexaError("No fue posible acceder al archivo XML de la factura.",null,["logname"=>"altafac","errPath"=>$errPath,"filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username,"lastException"=>getErrorData(CFDI::$lastException),"cfdiLog"=>$file["dblog"]]);
                    } else doclog("SUBMITXML Extraccion de datos de XML fallida","altafac",["filename"=>$file["name"],"errPath"=>$errPath,"cfdiLog"=>$file["dblog"]]);
                } else anexaError("No pudo crearse la ruta para guardar los archivos CFDI.",null,["logname"=>"altafac","errPath"=>$errPath,"filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);

            }
        } else {
            // No debería ser verdadero
            if (isEnough()) anexaError("Error en validación de factura.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
            $file["xml"]=null;
        }
        // Verificacion de existencia y vigencia.
        //  - El emisor de la factura debe ser un proveedor activo.
        //  - El receptor de la factura debe ser una empresa del corporativo activa.
        //  - La factura a dar de alta debe ser nueva o con status Temporal
        doclog("SUBMITXML POPULATE","altafac",["filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
        //clog2ini("CFDISetup.populate-file.$idx");
        // Generacion de ruta para guardar archivos.
        // Formato archivo XML: {RFC}_{folio}.xml o {RFC}_{serie}{folio}.xml o {RFC}_{uuid}.xml
        //                    o {RFC}_{TC}_{folio}.xml o {RFC}_{TC}_{serie}{folio}.xml
        // Formato archivo PDF: {folio}{RFC}.pdf o {serie}{folio}{RFC}.pdf o {uuid}{RFC}.pdf
        //                    o {TC}_{folio}{RFC}.pdf o {TC}_{serie}{folio}{RFC}.pdf
        if (isset($file["xml"])) { //isEnough()) {
            $tipoComprobante = strtolower($file["xml"]->get("tipo_comprobante"));
            $esFactura =  ($tipoComprobante==="i" || $tipoComprobante==="ingreso");
            $esNota =     ($tipoComprobante==="e" || $tipoComprobante==="egreso");
            $esPago =     ($tipoComprobante==="p" || $tipoComprobante==="pago");
            $esTraslado = ($tipoComprobante==="t" || $tipoComprobante==="traslado");
            $comprob=$esFactura?"factura":($esTraslado?"traslado":($esNota?"nota":"comprobante"));
            $ca=($esFactura||$esNota)?"La":"El";
            $cdea=($esFactura||$esNota)?"de la":"del";
            $cal=strtolower($ca);
            $ca_pl=($esFactura||$esNota)?"Las":"Los";
            //clog2ini("CFDISetup.file-path-setup.$idx");
            $uuid=strtoupper($file["xml"]->get("uuid"));
            $folioFactura = trim($file["xml"]->get("folio"));
            $serieFactura = trim($file["xml"]->get("serie"));
            if (empty($folioFactura)) {
                $uuidLen = strlen($uuid);
                if ($uuidLen >= 10) {
                    $fileSuffix = substr($uuid, -10);
                } else if ($uuidLen > 0) {
                    $fileSuffix = $uuid;
                } else if (isEnough()) anexaError("$ca $comprob no tiene folio único ni UUID.",null,["logname"=>"altafac","filename"=>$file["name"],"usrid"=>$userid,"usrname"=>$username]);
            } else if (preg_match('/[^a-zA-Z0-9\-\_\.]/', $folioFactura)) {
                if (isEnough()) anexaError("El folio $cdea $comprob solo puede contener letras, numeros, guiones y puntos.",null,["logname"=>"altafac","filename"=>$file["name"],"folio"=>$folioFactura,"usrid"=>$userid,"usrname"=>$username]);
            } else {
                $fileSuffix = $folioFactura;
                if (isset($fileSuffix[10])) $fileSuffix = substr($fileSuffix, -10);
                if (isset($serieFactura[0])) $fileSuffix2 = $serieFactura.$folioFactura;
                if (isset($fileSuffix2[10])) $fileSuffix2 = substr($fileSuffix2, -10);
            }
            //clog2("File Suffix : $fileSuffix");
            //clog2end("CFDISetup.file-path-setup.$idx");
        }
        if (isset($file["xml"])) { //isEnough()) {
            //clog2ini("CFDISetup.typedoc.$idx");
            if ($tipoComprobante==="egreso" || $tipoComprobante==="e") {
                $hayNotas=true;
                if (isset($fileSuffix[0])) $fileSuffix = "NC_".$fileSuffix;
                if (isset($fileSuffix2[0])) $fileSuffix2 = "NC_".$fileSuffix2;
            } else if ($tipoComprobante==="p") {
                $hayPagos=true;
                if (isset($fileSuffix[0])) $fileSuffix = "RP_".$fileSuffix;
                if (isset($fileSuffix2[0])) $fileSuffix2 = "RP_".$fileSuffix2;
            } else if ($tipoComprobante==="ingreso" || $tipoComprobante==="i") {
                $hayFacturas=true;
            } else if ($tipoComprobante==="traslado" || $tipoComprobante==="t") {
                $hayTraslados=true;
            }
            //clog2("Type Doc : $tipoComprobante");
            //clog2end("CFDISetup.typedoc.$idx");
        }
        if (isset($file["xml"])) {
            $emisor = $file["xml"]->get("emisor");
            $receptor = $file["xml"]->get("receptor");
            $rfcEmisor = $emisor["@rfc"]; //utf8_encode($emisor["@rfc"]);
            //clog3("RFC Emisor = $rfcEmisor"); // | ".$emisor["@rfc"]);
            $rfcReceptor = $receptor["@rfc"]; //utf8_encode($receptor["@rfc"]);
            //clog3("RFC Receptor = $rfcReceptor"); // | ".$receptor["@rfc"]);
            $usoCFDI=mb_strtoupper($receptor["@usocfdi"]??"");
            if (isset($fileSuffix[0])) {
                $xmlName1=$rfcEmisor."_".$fileSuffix; // .".xml";
                if (isset($fileSuffix2[0])) $xmlName2=$rfcEmisor."_".$fileSuffix2;
                $file["new_name"] = $xmlName1;
                doclog("SUBMITXML FILE PREP","altafac",["newname"=>$xmlName1,"usrid"=>$userid,"usrname"=>$username]);
            }
            $tgtPath = getUbicacionFactura($file);
            $ciclo = null;
            if (!$tgtPath) { // $ca/$cal/$ca_pl $comprob
                if (!$file["xml"]->has("fecha")) anexaError("No se identifica la fecha $cdea $comprob en el XML.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
                else if (!isset($factura["xml"]->cache["aliasGrupo"])) anexaError("La empresa receptora no puede ser identificada.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
                else anexaError("No pudo definirse la ubicacion $cdea {$comprob}.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
            } else if(!is_dir("./".$tgtPath)) {
                if (mkdir("./".$tgtPath, 0777, true)) {
                    copy("./archivos/index.php", "./{$tgtPath}/index.php");
                    //$pathParts = explode("/",$tgtPath);
                } else anexaError("No pudo crearse la ruta para guardar el xml.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
            }
            if (isEnough()) $ciclo = explode("/",$tgtPath)[2];//substr($tgtPath,0,-4);
            if (isset($fileSuffix[0])) {
                $pdfName1=$fileSuffix.$rfcEmisor;
                $pdfName2=mb_substr($file["name"],0,-4);
                if ($tipoComprobante==="p"||$tipoComprobante==="e"||$tipoComprobante==="egreso") $pdfName3=mb_substr($fileSuffix,3).$rfcEmisor;
                if (isset($fileSuffix2[0])) $pdfName4=$fileSuffix2.$rfcEmisor;
                if  (isset($pdfs[$pdfName1])) $originalName=$pdfName1;
                else if (isset($pdfs[$pdfName2])) $originalName=$pdfName2;
                else if (isset($pdfName3[0])&&isset($pdfs[$pdfName3])) $originalName=$pdfName3;
                if (isset($originalName[0])) $file["pdf_name"]=$pdfName1;
                else $faltanPDF=true;
            }
            //clog3("PDF1=$pdfName1");
            //clog3("PDF2=$pdfName2");
            //if (isset($pdfName3[0])) clog3("PDF3=$pdfName3");
            //clog3("ORIGINAL PDF = $originalName");
        } else {
            doclog("SUBMITXML FILE PREP NOXML","altafac");
        }
        // Verificar no exista la misma factura (mismo nombre de archivo) en las anteriores
        if (isset($tgtPath[0]) && isset($fileSuffix[0])) { // if (isEnough()) 
            $tgtFullPath = $tgtPath.$xmlName1.".xml";
            if (isset($xmlName2[0])) {
                $tgtFullPath2 = $tgtPath.$xmlName2.".xml";
                $chkFullPath=$tgtFullPath2;
            } else $chkFullPath=$tgtFullPath;
            if (isset($factnwnms[$chkFullPath])) {
                anexaError("No puede dar de alta dos comprobantes del mismo proveedor con el mismo folio: ".$xmlName1,null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>getUser()->id,"usrname"=>getUser()->nombre]);
            } else {
                $factnwnms[$chkFullPath]=1;
            }
        }
        // Obtencion del certificado y vigencia del SAT
        // Si la factura ya estaba registrada (Status temporal) se verifica que ya hubiera validado
        if (isset($file["xml"])) {
            $total = $file["xml"]->get("total");
        }
        if (isEnough()) {
            doclog("SUBMITXML BASE PREP","altafac",["tgtFullPath"=>$tgtFullPath,"uuid"=>$uuid]);
            $respuesta = false;
            if (file_exists($tgtFullPath)) {
                $respuesta = consultaBase($uuid);
                doclog("SUBMITXML ConsultaBase","altafac",["uuid"=>$uuid,"respuesta"=>$respuesta]);
                if (isset($respuesta) && isset($respuesta["status"])) {
                    $sujeto=null; $pronombre=null;
                    $esTemporal=($respuesta["status"] === "Temporal");
                    $tc=$tipoComprobante[0];
                    switch($tc) {
                        case "i": $sujeto="La factura"; $pronombre="a"; break;
                        case "e": $sujeto="La nota"; $pronombre="a"; break;
                        case "p": $sujeto="El pago"; $pronombre="o"; break;
                        case "t": $sujeto="El traslado"; $pronombre="o"; break;
                        default: $tc=null;
                    } 
                    if ($esTemporal) {
                        $file["registroexiste"] = TRUE;
                        if ($tc==="i") {
                            if (!isset($solObj)) {
                                require_once "clases/SolicitudPago.php";
                                $solObj = new SolicitudPago();
                            }
                            if ($solObj->exists("idFactura='$respuesta[id]'")) {
                                $file["registroexiste"] = FALSE;
                                anexaError("Ya existe una solicitud de pago para esta factura",null,["logname"=>"altafac","filename"=>$file["name"],"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                            }
                        }
                    } else if (!is_null($tc)) {
                        anexaError("$sujeto ya est&aacute; registrad$pronombre en el sistema.",null,["logname"=>"altafac","filename"=>$file["name"],"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                    }
                }
                $serieBD=obtenerSerie($tgtPath,$xmlName1);
                if (isset($xmlName2[0]) && $serieFactura!==$serieBD) {
                    $file["new_name"]=$xmlName2;
                    if (isset($file["pdf_name"][0])) $file["pdf_name"]=$pdfName4;
                    $fileSuffix=$fileSuffix2;
                    $tgtFullPath=$tgtFullPath2;
                    // ToDo: Arreglar que el pdf debe tener la serie tambien, no se está actualizando. Posiblemente es por la variable originalName. Cotejar ejemplo en reporte facturas proporcionado por laurai: APSA - A-053, Fecha septiembre, Status Todas
                }
            }
            if (isset($fileSuffix[0])) $file["file_suffix"]=$fileSuffix;
            doclog("XML NAME","altafac",["newname"=>$file["new_name"]]);
            if (isset($file["pdf_name"][0])) doclog("PDF NAME","altafac",["pdf"=>$file["pdf_name"]]);
            try {
                if (empty($respuesta)||$respuesta["cfdi"][0]!=="S") {
                    $respuesta = consultaServicio($rfcEmisor, $rfcReceptor, $total, $uuid);
                    doclog("ConsultaServicio","altafac",["respuesta"=>$respuesta]);
                }
                if ($respuesta) {
                    if(isset($respuesta["error"])) {
                        anexaError($respuesta["mensaje"],null,["logname"=>"altafac","filename"=>$file["name"],"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                    } else if (!$_esPruebas) {
                        $file["cfdi"] = $respuesta["cfdi"];
                        $file["vigencia"] = $respuesta["estado"];
                        $file["cancelable"] = $respuesta["escancelable"]??"";
                        $file["cancelado"] = $respuesta["estatuscancelacion"]??"";
                        if (!isset($file["cfdi"][0])) {
                            anexaError("No se obtuvo respuesta del SAT. Intente de nuevo más tarde.",null,["logname"=>"altafac","filename"=>$file["name"],"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                        } else if ($file["cfdi"][0]!="S") {
                            if ($file["cfdi"]==="N - 602: Comprobante no encontrado.") {
                                anexaError("<p class=\"marginV7 wordkeep\">El comprobante no se localizó en los registros del SAT.</p><p class=\"marginV7 wordkeep\">Puede <a href=\"https://verificacfdi.facturaelectronica.sat.gob.mx/\" target=\"verificacfdisat\"><span class=\"btnFX highlight semifit\">confirmar su registro al SAT</span></a> ingresando los datos <span class=\"highlight semifit\">resaltados</span>.</p>","class=\"topvalign\"",["logname"=>"altafac","filename"=>$file["name"],"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                            } else {
                                anexaError("Comprobante del SAT no satisfactorio: $file[cfdi]",null,["logname"=>"altafac","filename"=>$file["name"],"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                            }
                        } else if ($file["vigencia"]!="Vigente") anexaError("Status del SAT no vigente: $file[vigencia]",null,["logname"=>"altafac","filename"=>$file["name"],"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"respuesta"=>$respuesta,"usrid"=>$userid,"usrname"=>$username]);
                        //else doclog("");
                    }
                } else anexaError("No se obtuvo respuesta del SAT. Reintente más tarde.",null,["logname"=>"altafac","filename"=>$file["name"],"uuid"=>$uuid,"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"usrid"=>$userid,"usrname"=>$username]);
            } catch (Exception $e) {
                anexaError("No se obtuvo respuesta del SAT. Intente nuevamente más tarde.",null,["logname"=>"altafac","filename"=>$file["name"],"uuid"=>$uuid,"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"usrid"=>$userid,"usrname"=>$username,"exception"=>getErrorData($e)]);
            }
        } else doclog("SUBMITXML NO BASE PREP","altafac");
        if (isset($file["xml"])) { //isEnough()) {
            $subtotal = +($file["xml"]->get("subtotal"));
            $descuento = 0;
            if ($file["xml"]->has("descuento")) $descuento = +($file["xml"]->get("descuento"));
            $impRetenido = 0;
            if ($file["xml"]->has("totalimpuestosretenidos")) $impRetenido = +($file["xml"]->get("totalimpuestosretenidos"));
            $impTraslado = 0;
            if ($file["xml"]->has("totalimpuestostrasladados")) $impTraslado = +($file["xml"]->get("totalimpuestostrasladados"));
            $impLRetenido = 0;
            if ($file["xml"]->has("implocal_totalretenciones")) $impLRetenido = +($file["xml"]->get("implocal_totalretenciones"));
            $impLTraslado = 0;
            if ($file["xml"]->has("implocal_totaltraslados")) $impLTraslado = +($file["xml"]->get("implocal_totaltraslados"));
            doclog("SUBMITXML AMOUNT PREP","altafac",["tgtFullPath"=>$tgtFullPath,"uuid"=>$uuid]);
            //if ($file["xml"]->get("version")==="3.3") {
                if (!isset($epsilon)) $epsilon=0.015; //0.000001;
                $sum = $subtotal-$descuento-$impRetenido-$impLRetenido+$impTraslado+$impLTraslado;
                $total=+$total;
                if ($total>$sum) $dif = $total-$sum;
                else $dif = $sum-$total;
                /* DESHABILITAR TEMPORALMENTE para subir facturas con ISH (Impuestos Locales Complementarios) */
                if ($dif > $epsilon) {
                    $errMsg="El monto total $".number_format($total,2)." no coincide con subtotal $".number_format($subtotal,2);
                    if ($descuento!==0) $errMsg.=" - descuento $".number_format($descuento,2);
                    if ($impTraslado!==0) $errMsg.=" + impuestos trasladados $".number_format($impTraslado,2);
                    if ($impRetenido!==0) $errMsg.=" - impuestos retenidos $".number_format($impRetenido,2);
                    if ($impLTraslado!==0) $errMsg.=" + impuesto local trasladado $".number_format($impLTraslado,2);
                    if ($impLRetenido!==0) $errMsg.=" - impuesto local retenido $".number_format($impLRetenido,2);
                    $errMsg.=" = $".number_format($sum,2)." (dif. $".number_format($dif,2).")";
                    anexaError($errMsg,null,["logname"=>"altafac","filename"=>$file["name"],"total"=>$total,"sum"=>$sum,"dif"=>$dif,"uuid"=>$uuid,"usrid"=>$userid,"usrname"=>$username]);
                }
                /* */
            //}
        }
        $fechaReciboPago="";
        $saldoReciboPago=0;
        if (isset($file["xml"]) && $tipoComprobante==="p") { // isEnough() // Validar que las facturas que contiene existan en invoice check
            $pdoctos = $file["xml"]->get("pago_doctos");
            if (isset($pdoctos["@iddocumento"])) $pdoctos = [$pdoctos];
            $numPagos = $file["xml"]->cache["numPagos"]??count($pdoctos);
            //$diffLapse=lapse();
            //$seconds -= floor($diffLapse);
            //$seconds += 10*$numPagos;
            $seconds = 30*$numPagos;
            if ($seconds<90) $seconds=90;
            else if ($seconds>1000) $seconds=1000;
            set_time_limit($seconds);
            doclog("SUBMITXML CFDIP","altafac",["STL"=>2,"lapse"=>lapse(),"numPagos"=>$numPagos,"timeLimit"=>$seconds]);
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj = new Facturas();
            }
            $doctIds=[];
            $folioFacturaFaltanteEnRecibo=[];
            $monedas=[];
            //require_once "clases/PagoSoloUUID.php";
            foreach ($pdoctos as $pago) {
                $pagoMon=strtoupper($pago["@monedadr"]);
                if (isset($monedas[$pagoMon])) $monedas[$pagoMon]++;
                else $monedas[$pagoMon]=1;
                $uuiddoc=strtoupper($pago["@iddocumento"]);
                $where = $invObj->getWhereCondition("uuid", $uuiddoc)."statusn is not null"; //  and statusn>0
                //clog3("WHERE: $where");
                $invUidData=$invObj->getData($where,false,"id,codigoProveedor,rfcGrupo");

                if (isset($invUidData[0]["id"])) $invUidData=$invUidData[0];
                if (isset($invUidData["id"])) {
                    $doctIds[$uuiddoc]=$invUidData["id"];
                } else {
                    $folioFacturaFaltanteEnRecibo[] = $uuiddoc;
                    continue;
                    //clog3("Factura en Recibo de Pago no existe en portal: uuid=".$pago["@iddocumento"].(isset($pago["@serie"])?", serie=".$pago["@serie"]:"").(isset($pago["@folio"])?", folio=".$pago["@folio"]:""));
                    //clog3($invObj->log);
                }

                if ($invUidData["codigoProveedor"]!==$file["xml"]->cache["codigoProveedor"]) {
                    anexaError("El proveedor en las facturas del complemento de pago deben coincidir con el mismo",null,["logname"=>"altafac","filename"=>$file["name"],"usrid"=>$userid,"usrname"=>$username,"cPagoCodProv"=>$file["xml"]->cache["codigoProveedor"],"invId"=>$invUidData["id"],"invCodProv"=>$invUidData["codigoProveedor"]]);
                    continue;
                }
                if ($invUidData["rfcGrupo"]!==$rfcReceptor) {
                    anexaError("La empresa receptora en las facturas del complemento de pago deben coincidir con el mismo",null,["logname"=>"altafac","filename"=>$file["name"],"usrid"=>$userid,"usrname"=>$username,"cPagoRfcCorp"=>$rfcReceptor,"invId"=>$invUidData["id"],"invRfcCorp"=>$invUidData["rfcGrupo"]]);
                    continue;
                }

            }
            if (isset($folioFacturaFaltanteEnRecibo[0][0])) {
                $uuidFaltantesTot=[];
                $folioFaltantesTot=[];
                foreach($folioFacturaFaltanteEnRecibo as $unfolio) {
                    if (isset($unfolio[30])) $uuidFaltantesTot[]=substr($unfolio,-10);
                    else $folioFaltantesTot[]=$unfolio;
                }
                $len = count($folioFacturaFaltanteEnRecibo);
                $pl_s = $len!=1?"s":"";
                $pl_n = $len!=1?"n":"";
                $flen = count($folioFaltantesTot);
                $fpl_s = $flen!=1?"s":"";
                $ulen = count($uuidFaltantesTot);
                $ftxt = $flen>0?"folio$fpl_s '".implode("','",$folioFaltantesTot)."'":"";
                $utxt = $ulen>0?"uuid '...".implode("','...",$uuidFaltantesTot)."'":"";
                if (isset($ftxt[0])&&isset($utxt[0])) $utxt = ", ".$utxt;

                anexaError("Es necesario que la$pl_s factura$pl_s ($ftxt$utxt) en el Recibo de Pago, este$pl_n dada$pl_s de alta en el portal.",null,["logname"=>"altafac","filename"=>$file["name"],"usrid"=>$userid,"usrname"=>$username]); //  y hayan sido aceptadas
            }
            if (isEnough()) {
                $file["xml"]->cache["monDR"]=$monedas;
                $fechaPagos = $file["xml"]->get("pago_fecha");
                if (is_string($fechaPagos)) $fechaReciboPago=$fechaPagos;
                else if (is_array($fechaPagos)) {
                    rsort($fechaPagos);
                    $fechaReciboPago=$fechaPagos[0];
                }
                $montoPagos = $file["xml"]->get("pago_monto_total");
                if (is_scalar($montoPagos)) $saldoReciboPago += +$montoPagos;
                else foreach($montoPagos as $monto) $saldoReciboPago += +$monto;
            }
        }
        // Se  agrega la factura al sistema
        // Se genera error en caso de detectarse error al guardar la factura en base y archivo 
        if (isEnough()) {
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj = new Facturas();
            }
            if (($file["registroexiste"]??FALSE) && $invObj->exists("id='$respuesta[id]' && status='Temporal'")) {
                $delfldarray = ["id"=>$respuesta["id"], "status"=>"Temporal"];
                $invObj->deleteRecord($delfldarray);
            }
            $invData=$invObj->getData("nombreInterno='$file[new_name]' && status='Temporal'",false,"id");
            if (isset($invData[0]["id"])) {
                if (!isset($solObj)) {
                    require_once "clases/SolicitudPago.php";
                    $solObj = new SolicitudPago();
                }
                if ($solObj->exists("idFactura='".$invData[0]["id"]."'")) anexaError("Ya existe una solicitud de pago para esta factura");
                else {
                    $delfldarray = ["id"=>$invData[0]["id"], "status"=>"Temporal"];
                    $invObj->deleteRecord($delfldarray);
                }
            }
            //clog3("Guardando");
            $fecha = $_now["now"];
            $traslado_tasa = $file["xml"]->get("traslado_tasa");
            if (is_array($traslado_tasa) && isset($traslado_tasa[0])) $traslado_tasa = $traslado_tasa[0]; // Se copia el primer valor tasa (del primer impuesto) De todas formas la tasa debería de guardarse por concepto en lugar de por factura. Aunque no es relevante pues no se solicita en Avance la tasa por concepto.
            $tipoCambio = $file["xml"]->get("tipocambio");
            if (empty($tipoCambio)) $tipoCambio="0";
            $fieldarray = [
                "codigoProveedor"=>$file["xml"]->cache["codigoProveedor"],
                "rfcGrupo"=>$rfcReceptor,
                "fechaFactura"=>$file["xml"]->get("fecha"),
                "fechaCaptura"=>"$fecha",
                "uuid"=>$uuid,
                "noCertificado"=>$file["xml"]->get("certificado"),
                "importeDescuento"=>"$descuento",
                "impuestoTraslado"=>"$impTraslado",
                "impuestoRetenido"=>"$impRetenido",
                "subtotal"=>"$subtotal",
                "total"=>"$total",
                "tipoComprobante"=>$tipoComprobante,
                "tipoCambio"=>$tipoCambio,
                "moneda"=>$file["xml"]->get("moneda"),
                "nombreOriginal"=>$file["name"],
                "nombreInterno"=>$file["new_name"],
                "ubicacion"=>$tgtPath,
                "ciclo"=>$ciclo,
                "version"=>$file["xml"]->get("version"),
                "status"=>"Temporal"];
            if (isset($usoCFDI[0])) $fieldarray["usoCFDI"]=$usoCFDI;
            if ($file["registroexiste"]??FALSE)
                $fieldarray["id"]=$respuesta["id"];
            if (!empty($traslado_tasa))
                $fieldarray["tasaIva"] = $traslado_tasa;
            if (!empty($folioFactura))
                $fieldarray["folio"] = $folioFactura;
            if (isset($serieFactura[0])) // $file["xml"]->has("serie")
                $fieldarray["serie"] = $serieFactura; //$file["xml"]->get("serie");
            if ($file["xml"]->has("metodo_pago"))
                $fieldarray["metodoDePago"] = $file["xml"]->get("metodo_pago");
            if ($file["xml"]->has("forma_pago"))
                $fieldarray["formaDePago"] = $file["xml"]->get("forma_pago");
            if (!empty($file["cfdi"])) {
                $fieldarray["mensajeCFDI"] = $file["cfdi"];
                $fieldarray["consultaCFDI"] = $fecha;
            }
            if (!empty($file["vigencia"]))
                $fieldarray["estadoCFDI"] = $file["vigencia"];
            if (!empty($file["cancelable"]))
                $fieldarray["cancelableCFDI"] = $file["cancelable"];
            if (!empty($file["cancelado"]))
                $fieldarray["canceladoCFDI"] = $file["cancelado"];
            $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
            if (isset($originalName[0])) {
                $fieldarray["nombreInternoPDF"] = $file["pdf_name"];
                doclog("NOMBRAR PDF", "pdf", $baseData+["line"=>__LINE__,"xml"=>$file["new_name"],"pdf"=>$file["pdf_name"]]);
                //clog3("Saving fieldArray[nombreInternoPDF]=$file[pdf_name]");
            }
            if (!empty($fechaReciboPago))
                $fieldarray["fechaReciboPago"] = $fechaReciboPago;
            if (!empty($saldoReciboPago))
                $fieldarray["saldoReciboPago"] = $saldoReciboPago;
            ajustarRegistroFactura($fieldarray);
            $file["success"] = $invObj->saveRecord($fieldarray);
            if ($file["success"]) {
                $file["facturaId"] = $invObj->lastId;
                doclog("SUBMITXML SAVE","altafac",["STL"=>3,"lapse"=>lapse(),"id"=>$file["facturaId"]]);
                if (isset($file["xml"]) && $tipoComprobante==="p") /* $file["xml"]->savePagos($file["facturaId"],$doctIds); */ {
                    global $cpyObj, $query;
                    if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
                    //$cpyObj->
                    // CPagos: id,idCPago, (idFactura,numParcialidad,saldoAnterior,impPagado,saldoInsoluto,moneda,equivalencia,) idEPago,fechaPago,montoPago,monedaPago,tipocambioPago
                    // DPagos: id,idPPago,idFactura,numParcialidad,saldoAnterior,impPagado,saldoInsoluto,moneda,equivalencia
                    $pagos = $file["xml"]->get("pagos");
                    if (isset($pagos["@fechapago"])) $pagos=[$pagos];
                    foreach ($pagos as $pgIdx => $pgItem) {
                        $cpgArr=["idCPago"=>$file["facturaId"],"fechaPago"=>$pgItem["@fechapago"],"montoPago"=>$pgItem["@monto"],"monedaPago"=>$pgItem["@monedap"],"tipocambioPago"=>$pgItem["tipocambiop"]??1];
                        $docsRel=$pgItem["DoctoRelacionado"]??null;
                        if (isset($docsRel["@iddocumento"])) $docsRel=[$docsRel];
                        $cpgArrNeedUpdate=false;
                        foreach ($docsRel as $drIdx => $drItem) {
                            $pgUUID=strtoupper($drItem["@iddocumento"]);
                            $pgFId=$doctIds[$pgUUID];
                            $dpgArr=["idFactura"=>$pgFId,"numParcialidad"=>$drItem["@numparcialidad"],"saldoAnterior"=>$drItem["@impsaldoant"],"impPagado"=>$drItem["@imppagado"],"saldoInsoluto"=>$drItem["@impsaldoinsoluto"],"moneda"=>$drItem["@monedadr"],"equivalencia"=>$drItem["@equivalenciadr"]??1];
                            if (!isset($cpgArr["id"])) {
                                $cpgArr=array_merge($cpgArr,$dpgArr);
                                if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
                                if ($cpyObj->saveRecord($cpgArr)) {
                                    $cpgArr["id"]=$cpyObj->lastId;
                                } else {
                                    errlog("Error al guardar Pagos/Pago en CPago","error",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>$cpyObj->errors]);
                                }
                            }
                            if ((+$cpgArr["numParcialidad"])<(+$dpgArr["numParcialidad"])) {
                                $cpgArr=array_merge($cpgArr,$dpgArr);
                                $cpgArrNeedUpdate=true;
                            }
                            $dpgArr["idPPago"]=$cpgArr["id"];
                            if (!isset($dpyObj)) { require_once "clases/DPagos.php"; $dpyObj=new DPagos(); }
                            if (!$dpyObj->saveRecord($dpgArr)) {
                                errlog("Error al guardar Pagos/Pago/DoctoRelacionado en DPago","error",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>$dpyObj->errors]);
                            }
                        }
                        if ($cpgArrNeedUpdate) {
                            if (!$cpyObj->saveRecord($cpgArr)) {
                                errlog("Error al actualizar Pagos/Pago en CPago","error",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>$cpyObj->errors]);
                            }
                        }
                    }
                }

                if (validaPerfil("Proveedor")) { // Permite recalcular listas de Grupo
                    if(isset($_SESSION['gpoRazSocOpt'])) unset($_SESSION['gpoRazSocOpt']);
                    if(isset($_SESSION['gpoCodigoOpt'])) unset($_SESSION['gpoCodigoOpt']);
                    if(isset($_SESSION['gpoRFCOpt']))    unset($_SESSION['gpoRFCOpt']);
                }
                //clog2("# CFDI TEST # SUCCESS! ID = ".$invObj->lastId);
                global $prcObj;
                if (!isset($prcObj)) {
                    require_once "clases/Proceso.php";
                    $prcObj = new Proceso();
                }
                $prcObj->cambioFactura($invObj->lastId, "Temporal", $username, $fecha, "altafactura01");
            } else {
                $errMasked="# CFDI TEST # FAILURE!";
                $errParsed="";
                if (!empty(DBi::$errors)) foreach(DBi::$errors as $sErn=>$sErr) {
                    $errMasked.="\n# ".$sErn." : ".$sErr;
                    $fixerror = DBi::getErrorTranslated($sErn, $sErr);
                    if (!empty($fixerror)) {
                        $fixerror.="<!-- ".$sErn." : ".$sErr." -->";
                        if (empty($errParsed)) $errParsed=$fixerror;
                        else {
                            if (substr($errParsed,0,4)!=="<li>") $errParsed="<li>$errParsed</li>";
                            $errParsed.="<li>$fixerror</li>";
                        }
                    }
                }
                if (empty($errParsed)) $errParsed="El comprobante no pudo guardarse.";
                else if (substr($errParsed,0,4)==="<li>") $errParsed="<ul>$errParsed</ul>";
                clog2($errMasked);
                anexaError($errParsed);
            }
        }
        if (isset($tgtFullPath[0]) && (!file_exists($tgtFullPath)||isEnough())) {
            move_uploaded_file($file["tmp_name"], $tgtFullPath);
            chmod($tgtFullPath,0666);
        }
        if (isset($originalName[0]) && isset($tgtPath[0]) && !empty($pdfs[$originalName]["tmp_name"]) && (!file_exists($tgtPath.$file["pdf_name"].".pdf")||isEnough())) {
            $pdfFullPath=$tgtPath.$file["pdf_name"].".pdf";
            move_uploaded_file($pdfs[$originalName]["tmp_name"], $pdfFullPath);
            chmod($pdfFullPath,0666);
        }
        /* ToDo: Comentado para probar si se puede eliminar, todo indica que si, pero hay que esperar a ver si no provoca ningun error
        if (isset($file["xml"])) { // isEnough()) {
            $file["conceptos"] = $file["xml"]->get("conceptos");
            // TODO: Verificar atributos (empiezan con @) Factura AAA1...0415.xml tiene errores al desplegar conceptos
            //doclog(DBi::real_escape_string(json_encode($file["conceptos"])),"_cnc_");
            $file["pagadas"] = $file["xml"]->get("pago_doctos");
            if (empty($file["errmsg"])) $approved++;
            $file["pagos"] = $file["xml"]->get("pagos");
        }*/
        if (!empty($file["errmsg"])) {
            if (!empty($file["dblog"]))
                $file["errmsg"] .= "\n<!-- \n".$file["dblog"]." -->\n";
        }
        $files[$idx] = $file;
        clog2end("CFDISetup.populate-file.$idx");
    }
    if (isset($errorMessage[0])) {
        doclog("SUBMITXML END","altafac",["STL"=>4,"duration"=>lapse($time_start)]);
        $pl_s=($numFacturas>1?"s":"");
        $pl_c=($numFacturas>1?"los comprobantes":"el comprobante");
        if ($processed>1) {
            if($approved==0) $errorMessage = "<p class='fontRelevant margin20 centered'>Se encontraron errores en {$pl_c}.</p>";
            else {
                $errorTitle = "HAY ERRORES";
                $errorMessage = "<p class='margin20 importantValue centered'>Algunos de sus comprobantes no fueron aceptados por tener error.</p><p class='margin20 centered'>Puede continuar ingresando <u>Número de Pedido</u> y <u>Código de Artículos</u> de sus comprobantes sin errores.</p><p class='margin20 centered'>Presione bot&oacute;n <b class=\"importantValue\">Agregar Documentos</b> para registrar los comprobantes v&aacute;lidos.</p>";
            }
        }
    }
} else {
    anexaError("Ning&uacute;n comprobante se recibi&oacute; para dar de alta.");
}
if (empty($errorMessage)) {
    $resultMessage = "";
    $pl_s=($numFacturas>1?"s":"");
    if ($faltanPDF) $resultMessage="<u>Archivo{$pl_s} PDF</u>";
    if ($hayFacturas||$hayNotas) {
        if (isset($resultMessage[0])) $resultMessage.=", ";
        $resultMessage.="<u>Número de Pedido</u> y <u>Código de Artículos</u> para su{$pl_s} ";
        if ($hayFacturas) $resultMessage.="factura{$pl_s}";
        if ($hayFacturas&&$hayNotas) $resultMessage.=" y ";
        if ($hayNotas) $resultMessage.="nota{$pl_s}";
    }
    if (isset($resultMessage[0])) {
        $resultMessage="<p class='margin20 centered'>Ingrese $resultMessage.</p><p>Por último presione botón <b class=\"importantValue\">Agregar Documento{$pl_s}</b> para enviar su información.</p>";
    } else if($hayPagos) {
        $resultMessage="<p class='margin20 centered'>Corrobore que la información es correcta y presione el botón <b class=\"importantValue\">Agregar Documento{$pl_s}</b> para registrar su información.</p>";
    }
    if ($numFacturas==1) $resultMessage.="<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"ebyid('insertxml').value='Agregar Documento';\">";
}
// - - - - - - - - - - - - - - - S U B M I T   M E T H O D S - - - - - - - - - - - - - - - //
function isFileUploadDataValid() {
    global $file;
    switch($file["error"]) {
        case UPLOAD_ERR_OK: 
            if (empty($file["name"])) anexaError("No se identifica el nombre de archivo.",null,["logname"=>"altafac","tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]);
            else if (empty($file["type"])) anexaError("No se identifica el tipo de archivo.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"filetype"=>$file["type"],"usrid"=>$userid,"usrname"=>$username]);
            else if ($file["type"]!="text/xml" && $file["type"]!="application/xml") anexaError("El archivo debe tener extensi&oacute;n XML, no $file[type].",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"filetype"=>$file["type"],"usrid"=>$userid,"usrname"=>$username]);
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
                    anexaError("El archivo no tiene formato XML reconocible: $tmpType.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"tmptype"=>$tmpType,"usrid"=>$userid,"usrname"=>$username]);
                }
            }
            break;
        case UPLOAD_ERR_NO_FILE: anexaError("No se envi&oacute; el archivo.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]); break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: anexaError("El archivo es demasiado grande.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"usrid"=>$userid,"usrname"=>$username]); break;
        default: anexaError("Error de carga de archivo desconocido.",null,["logname"=>"altafac","filename"=>$file["name"],"tmpname"=>$file["tmp_name"],"fileerror"=>$file["error"],"usrid"=>$userid,"usrname"=>$username]);
    }
    return isEnough();
}
function isEnough() {
    global $file;
    return isset($file["enough"]) && $file["enough"];
}
clog1seq(-1);
clog2end("configuracion.altafactura01_submitxml");
