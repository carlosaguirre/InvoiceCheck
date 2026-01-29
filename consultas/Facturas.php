<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
global $invObj,$eDrivePath;
if (!isset($invObj)) {
    require_once "clases/Facturas.php";
    $invObj = new Facturas();
}
$invObj->rows_per_page = 0;
$eDrivePath="E:\\FACTURAS\\temp\\";
if (isValueService()) getValueService($invObj);                // is set GET : llave
else if (isTestService()) getTestService2();             // is set GET : test
else if (isCatalogService()) getCatalogService($invObj);       // is set POST : catalogo_admin
else if (isAdminQueryService()) getAdminQueryService(); // is set POST : menu_accion && command && menu_accion=='Admin Factura'
else if (isAdminFacturaService()) getAdminFacturaService();
else if (isAutoUploadService()) doAutoUploadService();
else if (isShowAutoUploadService()) doShowAutoUploadService();
else if (isAppendPDFService()) getAppendPDFService();   // is set POST : AnexarPDF
else if (isAppendInvoiceService()) getAppendInvoiceService(); // (0) CARGA FACTURA ALTA PREVIA //
else if (isSaveFilesService()) getSaveFilesService(); // (1) CARGA DE ARCHIVOS //
else if (isReqPaymAuth()) doReqPaymAuth(); // (2) GENERA SOLICITUD DE PAGO //
else if (isRespPaymAuth()) doRespPaymAuth(); // (3) RESPUESTA DE AUTORIZACION //
else if (isResetBeforeAuth()) doResetBeforeAuth(); // (3B) RESTAURAR PREVIO A AUTORIZAR
else if (isTransferPaymAuth()) doTransferPaymAuth(); // (4) TRANSFERENCIA DE ARCHIVOS A AVANCE //
else if (isProcessPaymReq()) doProcessPaymReq(); // (5) PROCESAMIENTO DE FACTURA //
else if (isRdy2Pay()) doRdy2Pay(); // (6) ASIGNACION A PAGO //
else if (isAttachPaymProof()) doAttachPaymProof(); // (7) ANEXAR COMPROBANTE DE PAGO //
else if (isAttachProofDoc()) doAttachProofDoc(); // (7B) ANEXAR COMPROBANTE DE PAGO //
else if (isPayingRequest()) doPayingRequest(); // (8) PAGO DE SOLICITUD //
else if (isPayingMultiple()) doPayingMultiple();
else if (isGenPaymTextFile()) doGenPaymTextFile();
else if (isResendEmail()) doResendEmail();
else if (isCancelPaymentRequest()) doCancelPaymentRequest();
else if (isCancelInvoiceInRequest()) doCancelInvoiceInRequest();
else if (isSaveRequestObservations()) doSaveRequestObservations();
else if (isSaveReferral()) doSaveReferral();
else if (isESASAPayment()) doESASAPayment();
else if (isQueryCFDI()) getQueryCFDI();
else if (isVerifyCFDI()) getVerifyCFDI();
else if (isVerifyInv4PaymReq()) doVerifyInv4PaymReq();
else if (isSaveInvoiceInPaymReq()) doSaveInvoiceInPaymReq();
else if (isAddWarehouseEntry()) doAddWarehouseEntry();
else if (isDelWarehouseEntry()) doDelWarehouseEntry();
else if (isDisableWarehouseEntry()) doDisableWarehouseEntry();
else if (isAcceptInvoice()) doAcceptInvoice();
else if (isTemporalUpdateReceipt()) doTemporalUpdateReceipt();
else if (isUpdatePaymentReceiptData()) doUpdatePaymentReceiptData();
else if (isFixOldCPagos()) doFixOldCPagos();
else if (isFixEmptyCPagos()) doFixEmptyCPagos();
else if (isTempClrRPInInv()) doTempClrRPInInv();
else if (isRepairCFDI()) doRepairCFDI();
else if (isset($_POST["consultaSAT"])&&isset($_POST["rfcemisor"])&&isset($_POST["rfcreceptor"])&&isset($_POST["total"])&&isset($_POST["uuid"])) {
    $rfcE = str_replace("&", "&amp;", $_POST["rfcemisor"]);
    $rfcR = str_replace("&", "&amp;", $_POST["rfcreceptor"]);
    $total = $_POST["total"];
    $uuid = strtoupper($_POST["uuid"]);
    $result=["expresionImpresa"=>"?re=$rfcE&rr=$rfcR&tt=$total&id=$uuid"];
    if (isset($rfcE[0]) && isset($rfcR[0]) && isset($total[0]) && isset($uuid[0])) {
        require_once "clases/CFDI.php";
        if ($rfcR===CFDI::RFCDEMO) {
            echo json_encode(["resultado"=>"EXITO","cfdi"=>"S - Comprobante obtenido satisfactoriamente.","estado"=>"Vigente","escancelable"=>"No Cancelable"]);
            die();
        }
        require_once "configuracion/altafactura.php";
        use_soap_error_handler(false);
        global $client;
        try {
            $wsdl = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl";
            $client = new SoapClient($wsdl);
            if (empty($client)) {
                $result["resultado"] = "ERROR";
                $result["mensaje"] = "SIN CLIENTE";
            } else {
                $buscar = $client->Consulta($result);
                if (empty($buscar)) {
                    $result["resultado"] = "ERROR";
                    $result["mensaje"] = "SIN RESULTADO";
                } else {
                    $result["resultado"] = "EXITO";
                    $result["cfdi"] = $buscar->ConsultaResult->CodigoEstatus;
                    $result["estado"] = $buscar->ConsultaResult->Estado;
                }
            }
        } catch (Exception $e) {
            $result["resultado"] = "ERROR";
            $result["mensaje"] = $e->getMessage();
        }
    } else {
        $result["resultado"] = "ERROR";
        $result["mensaje"] = "Incompleto";
    }
    echo json_encode($result);
} else if (isset($_GET["consultaSAT"])) {                     // is set GET : consultaSAT
    $rfcE = $_GET["rfcemisor"];
    $rfcR = $_GET["rfcreceptor"];
    $total = $_GET["total"];
    $uuid = strtoupper($_GET["uuid"]);

    $qr = "?re=$rfcE&rr=$rfcR&tt=$total&id=$uuid";
    
    if (isset($rfcE[0]) && isset($rfcR[0]) && isset($total[0]) && isset($uuid[0])) {
        require_once "clases/CFDI.php";
        if ($rfcR===CFDI::RFCDEMO) {
            $result = ["resultado"=>"EXITO","cfdi"=>"S - Comprobante obtenido satisfactoriamente.","estado"=>"Vigente","escancelable"=>"No Cancelable"];
        } else {
            require_once "configuracion/altafactura.php";
            use_soap_error_handler(false);
            global $client;
            try {
                $wsdl = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl";
                $client = new SoapClient($wsdl);
                if (empty($client)) echo "<!-- EMPTY CLIENT -->\n";
                $result = consultaServicio($rfcE, $rfcR, $total, $uuid);
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }
    }

    echo "<table border='1'><tr><th>RFC EMISOR</th><th>RFC RECEPTOR</th><th>TOTAL</th><th>UUID</th></tr>";
    echo "<tr><td>$rfcE</td><td>$rfcR</td><td>$total</td><td>$uuid</td></tr>";
    echo "<tr><th colspan='4'>QR</th></tr>";
    echo "<tr><td colspan='4'>$qr</td></tr>";
    if ($result!==false) {
        echo "<tr><th colspan='4'>RESULTADO</th></tr>";
        echo "<tr><td colspan='4'>";
        arrechoLiteUL($result);
        echo "</td></tr>";
    }
    if (!empty($errorMessage)) {
        echo "<tr><th colspan='4'>ERROR</th></tr>";
        echo "<tr><td colspan='4'>";
        $errorMessage;
        echo "</td></tr>";
    }
    echo "</table>";
    //require_once "configuracion/finalizacion.php";
} else if (isset($_GET["emulaPDF"])) {
    $idFactura = $_GET["emulaPDF"];
    echo "Factura: $idFactura";
} else if (isset($_GET["exportar"])) {
    sessionInit();
    $username=(isset($_SESSION['user'])?$_SESSION['user']->nombre:null);
    $idFactList = $_GET["exportar"];
    $idFArr = explode(",",$idFactList);
    $trace1 = [];
    $statusMap = [];
    $sameStatusMap = [];
    
    if (isset($_GET["modo"])) {
        $modo = $_GET["modo"]; // win,ftp. Default:win
        if ($modo!=="ftp") $modo="win";
    } else $modo="win";
    $esWin = $modo==="win";
    $esFTP = $modo==="ftp";

    if (isset($_GET["salida"])) {
        $salida = $_GET["salida"]; // text,html,htmlp. Default:text
        if ($salida!=="html" && $salida!=="htmlp") $salida="text";
    } else $salida="text";
    $esTexto = $salida==="text";
    $esHtmlParcial = $salida==="htmlp";
    $esHtmlCompleto = $salida==="html";
    $esHtml = $esHtmlParcial||$esHtmlCompleto;
    
    if (isset($_GET["cliente"]))   $cliente = $_GET["cliente"]; // (alias grupo)
    if (isset($_GET["proveedor"])) $proveedor = $_GET["proveedor"]; // (codigo proveedor)
    if (isset($_GET["fini"]))      $fechaInicio = $_GET["fini"]; // dd/mm/yyyy
    if (isset($_GET["ffin"]))      $fechaFin = $_GET["ffin"];    // dd/mm/yyyy
    if (isset($_GET["status"]))    $status = $_GET["status"];    // vacio(todas), Exportadas, Aceptadas. Default vacio
    if (!isset($status) || ($status!=="Exportadas" && $status!=="Aceptadas")) $status="Todas";
    $esStatusTodas = $status==="Todas";
    $esStatusAceptadas = $status==="Aceptadas";
    $esStatusExportadas = $status==="Exportadas";
    /*
    require_once "clases/Avance.php";
    $avanceObj = new Avance();
    $region = $avanceObj->getRegionByClient($cliente);
    $esAPSA = ($region==="APSA");
    $esGLAMA = ($region==="STACLARA");
    */
    $esAPSA = in_array($cliente, ["APSA","JLA","JYL","MARLOT","RGA","COREPACK","SERVICIOS","MORYSAN","BIDARENA"]);
    $esGLAMA = in_array($cliente, ["GLAMA","LAISA","ENVASES","LAMINADOS","APEL","MELO","DANIEL","DESA","BIDASOA","ESMERALDA","FIDEICOMIS","FIDEMIFEL","SKARTON"]);
    $esCorpLobaton = in_array($cliente, ["APSA","JYL","COREPACK","MORYSAN","GLAMA","SKARTON","DESA","LAMINADOS","LAISA","BIDASOA","MELO","DANIEL","JLA","RGA","MARLOT","APEL","FIDEICOMIS","FOAMYMEX","PAPEL","CAPITALH","HALL","FIDEMIFEL","ESMERALDA","JYLSOR","SERVICIOS","APSALU","ENVASES","MORYCE","BIDARENA"]);

    if ($esFTP && (!$esAPSA && !$esGLAMA && !$esCorpLobaton)) {
        if ($esHtmlCompleto) echo "<html><body>";
        if ($esHtml) echo "<H1>Error</H1><p>";
        echo "La empresa $cliente no ha sido habilitada en el portal para exportar";
        if ($esHtml) echo "</p>";
        if ($esHtmlCompleto) echo "</body></html>";
        exit;
    }

    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    require_once "clases/Trace.php";
    $trObj = new Trace();

    if ($esWin && $esTexto) {
        header("Content-Type: text/plain");
        $dt = new DateTime();
        $fmt = $dt->format("ymdHis");
        $filename = "exportar".$fmt.".txt";
        header("Content-Disposition: inline; filename=\"".$filename."\"");
    }

    require_once "clases/Conceptos.php";
    $cptObj = new Conceptos();
    $cptObj->rows_per_page  = 0;
    
    $resultado = "";
    if ($esWin && $esHtmlCompleto) $resultado .= "<html><body>";

    $oldStatusDesc=[];
    eolCheck();
    $fData = $invObj->getData("id in ($idFactList)");
    foreach($fData as $fRow) {
        $fStatus = $fRow["status"];
        $fStatusN = +$fRow["statusn"];
        $fId = $fRow["id"];
        $tc = strtoupper($fRow["tipoComprobante"][0]);
        
        // Ignorar Notas de Credito
        if ($tc==="E") continue;
        
        // Folio de factura
        $xffolio = $fRow["folio"];
        if (!isset($xffolio[0])) {
            $xfuuid = $fRow["uuid"];
            if (isset($xfuuid[9])) $xffolio = substr($xfuuid, -10);
            else if (isset($xfuuid[0])) $xffolio = $xfuuid;
        } else if (isset($xffolio[10])) $xffolio = substr($xffolio, -10);

        // Fecha de generacion de factura
        $ffdt = DateTime::createFromFormat("Y-m-d H:i:s", $fRow["fechaFactura"]);
        $tipoCambio = $fRow["tipoCambio"];
        $intTCambio = intval($tipoCambio);
        //$tasa = $fRow["tasaIva"];
        //if (intval($tasa)==16) $tasa=1;          // Tipo de Tasa. 16% => 1
        //else $tasa=3;                            //              Otro => 3
        $tasa=1;                                   // Calculo deshabilitado, queda siempre en 1
        $cDataArr = $cptObj->getData("idFactura=".$fId);

        $resultado .= str_replace(" ","",$xffolio)." ";   // Folio Factura
        $resultado .= $ffdt->format("ymd")." ";           // Fecha de generacion de factura
        $resultado .= $fRow["pedido"]." ";               // Pedido
        $resultado .= "1 ";                               // Forma de pago. 1=Credito, 2=Contado. Siempre es 1
        $resultado .= $fRow["codigoProveedor"]." ";      // Codigo de proveedor
        $resultado .= "0 0 0 ";                           // %Descuento1, %Descuento2, %Cargo. Siemre son 0 los tres
        if ($intTCambio==1 || $intTCambio==0)    // Moneda. 0 = M.N., MEX, MX, MXN, Pesos, etc. 1 = DLLS, USA, USD, Dolares, etc
            $resultado .= "0 1.0000 ";                    // Factor de cambio = Tipo de cambio. Conversion a pesos MXN. 
        else $resultado .= "1 ".number_format(floatval($tipoCambio),4)." ";  // Si la moneda no son pesos, entonces son dolares.

        foreach($cDataArr as $cData) {
            // Codigo de Artículo del concepto
            $resultado .= $cData["codigoArticulo"]." ";
            // No de IVA. Si tasa es 16% = 1, si es 0% = 3, sino...
            $resultado .= $tasa." ";
            // Cantidad del concepto
            $resultado .= $cData["cantidad"]." ";
            // Importe del concepto
            $resultado .= $cData["precioUnitario"]." ";
        }
        $resultado .= str_pad("@",851);
        if ($esWin && $esHtml) $resultado .= "<BR>";
        $resultado .= PHP_EOL;
        
        //$nextStatus = $invObj->nextStatus($fStatus,"Exportar",$tc);
        $nextStatusN = $fStatusN|Facturas::actionToStatusN("Exportado");
        $nextStatus = Facturas::statusnToStatus($nextStatusN);
        if ($fStatusN!==$nextStatusN) {
            $trace1[] = "$fId:$fStatus($fStatusN)=>$nextStatus($nextStatusN)";
            $statusMap[$nextStatusN][] = $fId;
            $oldStatusDesc[$fId]=$fStatus."($fStatusN=>$nextStatusN)";
            $prcObj->debugStatus($fId, $nextStatus, $username, false, "Facturas.Exp:".$oldStatusDesc[$fId]);
        } else {
            $sameStatusMap[]=$fId;
        }
    }
    if ($esWin && $esHtmlCompleto) $resultado .= "</body></html>";
    
    if ($esFTP) {
        $nombreArchivo="";
        $mensaje="";
        if (isset($cliente)) $nombreArchivo.=$cliente;
        if (isset($proveedor)) $nombreArchivo.=str_replace("-","",$proveedor);
        $meses=["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];
        if ($esStatusExportadas) $suffix = "X"; // Solo para distinguir exportadas que no se encimen con las no exportadas
        else $suffix = ""; // las No exportadas y Todas si se empalman, pues con ambas cambia el status de las no exportadas
        $suffix .= "IChk";
        $numMes = null;
        $numAnio = null;
        if (isset($fechaInicio)) {
            $mes = substr($fechaInicio,3,2);
            $idx = +$mes-1;
            $numMes = $mes;
            $numAnio = substr($fechaInicio,6);
            $mensaje.="<!-- Con Fecha Inicio, mes=$numMes, año=$numAnio -->\n";
            if (isset($meses[$idx])) {
                $mes = $meses[$idx];
            }
        }
        if (isset($fechaFin)) {
            $mesFin = substr($fechaFin,3,2);
            $idx = +$mesFin-1;
            $numMes = $mesFin;
            $numAnio = substr($fechaFin,6);
            $mensaje.="<!-- Con Fecha Fin, mes=$numMes, año=$numAnio -->\n";
            if (isset($meses[$idx])) {
                //if (isset($mes)) {
                //    if ($mes!==$meses[$idx]) $mes.=$meses[$idx];
                //} else
                    $mes=$meses[$idx];
            } else {
                //if (isset($mes)) {
                //    if ($mes!==$mesFin) $mes.=$mesFin;
                //} else
                    $mes=$mesFin;
            }
        }
        if (isset($mes)) $nombreArchivo.=$mes;
        if (!isset($numMes)) { $numMes = date("m"); $mensaje.="<!-- Faltaba mes: $numMes -->\n"; }
        if (!isset($numAnio)) { $numAnio = date("Y"); $mensaje.="<!-- Faltaba año: $numAnio -->\n"; }
        $nombreArchivo.=$suffix.".txt";
        
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceGlama();
        
        require_once "templates/generalScript.php";
        if ($esHtmlCompleto) $mensaje .= "<html><head>".
            "<base href=\"$_SERVER[HTTP_ORIGIN]$_SERVER[WEB_MD_PATH]\">".
            "<meta charset=\"utf-8\">".
            "<link href=\"css/general.php\" rel=\"stylesheet\" type=\"text/css\">".
            getGeneralScript().
            "</head><body>";
        if ($esHtml) {
            $pgrA="<p class=\"marbtm0\">";
            $pgrU="</p>";
            //$mensaje .= "<br>\n";
        } else {
            $pgrA="";
            $pgrU="";
        }
        if ($ftpObj==null) $mensaje .= $pgrA."Error al iniciar conexi&oacute;n FTP.$pgrU\n<!-- ".MIFTP::$lastException." -->\n";
        else if (empty($resultado)) $mensaje .= $pgrA."El archivo a exportar no contiene informaci&oacute;n.$pgrU\n";
        else try {
            $ftpObj->exportarTexto($nombreArchivo, $resultado);
            if ($esGLAMA) {
                $url = $avance_servidor;
                $target = "Glama";
                $usu = $avance_usuario;
                $cla = $avance_clave;
                $reg = "STACLARA";
            } else if ($esAPSA) {
                $url = $avance_servidor;
                $target = "Apsa";
                $usu = $avance_usuario;
                $cla = $avance_clave;
                $reg = "APSA";
            } else if ($esCorpLobaton) {
                $url = $avance_servidor;
                $target = "CorpLobaton";
                $usu = $avance_usuario;
                $cla = $avance_clave;
                $reg = "CORPLOBATON";
            }
            $accessHref = "http://glama.esasacloud.com/avance/cgi-bin/e-sasa/uno";
            $loginAction = "http://$url/avance/cgi-bin/e-sasa/validapassc";
            $exportAction = "http://$url/avance/cgi-bin/e-sasa/CFAexterno2";
            if ($esHtml) {
                $mensaje.="<H1 class=\"marbtm0\">Exportar formato de facturas a AVANCE</H1>";
                //$mensaje.=$pgrA."&nbsp;".$pgrU;
                $mensaje.="{$pgrA}Seleccionar el siguiente cuadro de texto, se copiar&aacute; al <span class=\"tooltipCase\" onmouseenter=\"viewTooltip(event);\" tooltip='{\"eName\":\"DIV\",\"className\":\"maxWid250\",\"eText\":\"Espacio en memoria donde se copia el texto seleccionado para ser pegado en otro lugar.\"}'>PORTAPAPELES</span>:<br><span class=\"invisible\">COPIADO</span>&nbsp;<input type=\"text\" class=\"fullnametext\" value=\"$ftpObj->ftpExportPath$nombreArchivo\" readonly onclick=\"const rr=copyTextToClipboard('$ftpObj->ftpExportPath$nombreArchivo');if(typeof rr==='string') console.log(rr);clrem(ebyid('copyLabel'),'invisible');this.setSelectionRange(0, this.value.length);this.select();clearTimeout(timeOutAux);timeOutAux=setTimeout(function(){cladd(ebyid('copyLabel'),'invisible');clearSelection();}, 3500);\">&nbsp;<span id=\"copyLabel\" class=\"importantLabel invisible\">COPIADO</span>{$pgrU}";
                $mensaje.="{$pgrA}Iniciar sesión en <A href=\"$accessHref\" target=\"avance\" class=\"alink btnlike\" onclick=\"const rs=copyTextToClipboard('$ftpObj->ftpExportPath$nombreArchivo');if(typeof(rs==='string') console.log(rs);\">Avance</A> o abrir ventana donde ya se tenga.<br>Acomodar ventanas para poder ver ambas.{$pgrU}";
                $mensaje.="<hr class=\"marginH5\">";
                $mensaje.="{$pgrA}En la ventana de Avance seguir estos pasos:{$pgrU}<table style=\"margin:auto;\"><tr><td><img src=\"imagenes/TutorialExportar4.gif\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"this.previousElementSibling.src='imagenes/TutorialExportar4.gif?a='+Math.random();const gts=ebyid('genTxtSteps');gts.stp=gts.firstElementChild;gts.idx=1;console.log('Animation Started! '+gts.idx);cladd(gts.stp,'selected');var gtsInterval=setInterval(function(){const gts=ebyid('genTxtSteps');if(gts){clrem(gts.stp,'selected');gts.stp=gts.stp.nextElementSibling;gts.idx++;if(!gts.stp){gts.stp=gts.firstElementChild;gts.idx=1;}console.log('Animation Interval '+gts.idx);cladd(gts.stp,'selected');}else{console.log('Animation Closed!');clearInterval(gtsInterval);}},2100);ekil(this);\"></td><td><ol id=\"genTxtSteps\" class=\"genTxtSteps highSequence\"><li>Ingresar en Compras a Procesos Batch<li>Ingresar a Carga de Facturas/Remisiones<li>Ingresar a Formato con moneda<li>Seleccionar recuadro de texto Nombre del Archivo<li>Pegar el archivo que se encuentra en el Portapapeles (CTRL+V)</ol></td></table>{$pgrA}Necesita obtener permiso en Avance para Carga de Facturas/Remisiones{$pgrU}";
                /*
                $mensaje.="<form method=\"post\" action=\"$exportAction\" id=\"formExport\" name=\"form1\" class=\"inlineblock\" target=\"avance\" onsubmit=\"window.open('','avance'); return true;\">";
                // target=\"$target\" onsubmit=\"console.log('SUBMITTING EXPORT'); openedWindows.$target=window.open('','$target','menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes');\">"; // setTimeout(function() { console.log(openedWindows.$target.body.innerHTML); }, 1000); => Uncaught DOMException: Blocked a frame with origin "$_SERVER[HTTP_ORIGIN]" from accessing a cross-origin frame.
                //$mensaje.="<input type=\"hidden\" name=\"Corp\" value=\"$usu\">";
                //$mensaje.="<input type=\"hidden\" name=\"password\" value=\"$cla\">";
                //$mensaje.="<input type=\"hidden\" name=\"origen\" value=\"1\">";
                //$mensaje.="<input type=\"hidden\" name=\"davrodmar\" value=\"On\">";
                //$mensaje.="<input type=\"hidden\" name=\"escondido\" value=\"1\">";
                //$mensaje.="<input type=\"hidden\" name=\"basura\" value=\"1\">";
                $mensaje.="<input type=\"hidden\" name=\"Cual\" value=\"1\">"; // Facturas
                $mensaje.="<input type=\"hidden\" name=\"CualF\" value=\"15\">"; // Formato con moneda
                $mensaje.="<input type=\"hidden\" name=\"Emp\" value=\"$cliente\">";
                $mensaje.="<input type=\"hidden\" name=\"Idioma\" value=\"1\">";
                $mensaje.="<input type=\"hidden\" name=\"Mes\" value=\"$numMes\">";
                $mensaje.="<input type=\"hidden\" name=\"Ano\" value=\"$numAnio\">";
                $mensaje.="<input type=\"hidden\" name=\"Reg\" value=\"Local$reg\">";
                $mensaje.="<input type=\"hidden\" name=\"Usu\" value=\"Local$usu\">";
                $mensaje.="<input type=\"hidden\" name=\"End\" value=\"1\">";
                $mensaje.="<input type=\"hidden\" name=\"Nombre\" value=\"$ftpObj->ftpExportPath$nombreArchivo\">";
                $mensaje.="<button type=\"submit\" name=\"cmdEnviar\" value=\"Enviar Datos\" onclick=\"copyTextToClipboard('$ftpObj->ftpExportPath$nombreArchivo');\">Cargar Datos</button>";
                $mensaje.="</form>";
                //$mensaje.="<!-- ".getUser()->nombre." -->";
                $mensaje.="<hr class=\"marginHSp\">";
                $mensaje.="S&oacute;lo si AVANCE le genera el error &quot;<b class=\"redden bgred2\">Requiere firmarse</b>&quot; presione ";
                $mensaje.="<form method=\"post\" action=\"$loginAction\" id=\"formLogin\" name=\"form1\" class=\"inlineblock\" target=\"avance\" onsubmit=\"window.open('','avance'); return true;\">";
                // target=\"$target\" onsubmit=\"console.log('SUBMITTING AUTH'); openedWindows.$target=window.open('','$target','menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes');\">";
                $mensaje.="<input type=\"hidden\" name=\"Ancho\" value=\"1024\">";
                $mensaje.="<input type=\"hidden\" name=\"parb\" value=\"IUIUUYNENSINENUU\">";
                $mensaje.="<input type=\"hidden\" name=\"1stusuario\" value=\"$usu\">";
                $mensaje.="<input type=\"hidden\" name=\"Corp\" value=\"Local\">";
                $mensaje.="<input type=\"hidden\" name=\"password\" value=\"$cla\">";
                $mensaje.="<input type=\"hidden\" name=\"1stregion\" value=\"$reg\">";
                $mensaje.="<input type=\"hidden\" name=\"Idioma\" value=\"1\">";
                $mensaje.="<input type=\"hidden\" name=\"origen\" value=\"1\">";
                $mensaje.="<input type=\"hidden\" name=\"escondido\" value=\"1\">";
                $mensaje.="<input type=\"hidden\" name=\"basura\" value=\"On\">";

                //$mensaje.="<input type=\"hidden\" name=\"adelante\" value=\"38078065094098278\">";
                //    26066053082086362
                //    50090077106110386
                //    35075062091095377
                //$mensaje.="<input type=\"hidden\" name=\"davrodmar\" value=\"On\">";
                $mensaje.="<input type=\"submit\" name=\"cmdEnviar\" value=\"Firmar\">";
                $mensaje.="</form><br> y regrese aqui para cargar datos de nuevo.";
                $mensaje.=$pgrA."&nbsp;".$pgrU;
                */
                $mensaje.="<!-- \n$resultado\n -->\n";
                //$mensaje.="<button type=\"button\" onclick=\"copyTextToClipboard('$ftpObj->ftpExportPath$nombreArchivo'); sendData('export', '$exportAction', {Cual:'1', CualF:'15', Emp:'$cliente', Idioma:'1', Mes:'$numMes', Ano:'$numAnio', Reg:'Local$reg', Usu:'Local$usu', End:'1', Nombre:'$ftpObj->ftpExportPath$nombreArchivo'});\">Quick Send</button>";
                
            }
        } catch (Exception $e) {
            $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
            doclog("Error en transferencia de archivos: Falla al exportar texto","solpago",$baseData+["line"=>__LINE__,"invId"=>$fId,"error"=>getErrorData($e)]);
            $mensaje .= $pgrA."Error al cargar resultado por FTP.$pgrU\n<!-- ".$e->getMessage()." -->"."\n";
        }
        //if (!empty($resultado)) {
            //$mensaje .= "<!-- MIFTP LOG:\n".MIFTP::log()." -->\n";
            //$mensaje .= "<input type=\"button\" value=\"Ver Texto\" onclick=\"toggleHiddenArea('exportTextDiv',this,'Ver Texto','Ocultar Texto',116);toggleClass(['copyExportedText','briefLog2'],'hidden');\"> <input id=\"copyExportedText\" type=\"button\" value=\"Copiar Texto\" onclick=\"clearChildrenByClass('briefLog');copyTextAreaIdToClipboard('exportFullText','mylog','briefLog2');\" class=\"hidden\"> <span id=\"briefLog2\"  class=\"briefLog hidden\"></span><textarea id=\"exportFullText\" class=\"softHide\">$resultado</textarea>";
            //$mensaje .= "<div id=\"exportTextDiv\" class=\"hidden screen centered wrap800\"><pre class=\"wrapped6\">" . str_replace(["  ", "\r\n","\n"], [" &nbsp;","<br>","<br>"], $resultado) . "</pre></div><hr>";
        //}
        if ($esHtmlCompleto) $mensaje .= "</body></html>";
        echo $mensaje;
    } else // if ($esWin)
        echo $resultado;
        
    if (!empty($trace1)) {
        $traceText = "EXPORT: ".implode(",",$trace1);
        $trObj->agrega($traceText);
//            clog2($traceText);
    }
    foreach($statusMap as $nuevoStatusN=>$listId) {
        $nuevoStatus = Facturas::statusnToStatus($nuevoStatusN);
        $fieldArray = ["id"=>$listId, "status"=>$nuevoStatus, "statusn"=>$nuevoStatusN];
        $success = $invObj->saveRecord($fieldArray);
        if ($success) {
            $solObj->updateStatus($listId, Facturas::STATUS_EXPORTADO);
            foreach($listId as $fId) {
                $prcObj->cambioFactura($fId, $nuevoStatus, $username, false, "Facturas.Exp:".$oldStatusDesc[$fId]);
            }
        }
    }
    foreach($sameStatusMap as $fId) {
        $prcObj->cambioFactura($fId, "Exportado", $username, false, "Facturas.Exportado otra vez");
    }
} else if (isset($_GET["respaldar"])) {
    sessionInit();
    $username = (isset($_SESSION['user'])?$_SESSION['user']->nombre:null);

    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    
    require_once "clases/Grupo.php";
    if (!isset($gpoObj)) $gpoObj = new Grupo();
    
    $idFactList = $_GET["respaldar"];
    $idFArr = explode(",",$idFactList);

    $lookoutFilePath = "";
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];

    $modo = (isset($_GET["modo"])?strtolower($_GET["modo"]):null);
    if (isset($modo) && $modo==="directo") {
        require_once "clases/FTP.php";
        
        flush_buffers();
        $mensaje = "";
        $briefing = "";
        $idx=0;
        $num=0;
        try {
            $mensaje="";
            $ftpObj=MIFTP::newInstanceGlama();
            if ($ftpObj==null) {
                throw MIFTP::$lastException;
            }
            $mensaje.="<!-- LOG CONN:\n".MIFTP::log()." -->";
            MIFTP::log(false);
            $num=count($idFArr);
            $cuenta = ["AvanceXML"=>0,"AvancePDF"=>0,"ComprobanteI"=>0,"ComprobanteE"=>0,"ComprobanteP"=>0,"ComprobanteT"=>0];
            foreach($idFArr as $idFact) {  
                $briefing = "IDF=$idFact"; 
                $fData = $invObj->getData("id=$idFact");
                //if (!empty($fData) && count($fData)>0) $fData = $fData[0];
                if (isset($fData[0])) $fData = $fData[0];
                if (!empty($fData)) {
                    $idx++;
                    $mensaje.="<LI><!-- id=$idFact -->";
                    $rutaLocal = $fData['ubicacion'];
                    $alias = $gpoObj->getAliasByRFC($fData['rfcGrupo']);
                    if ($alias==="CASABLANCA") $alias="LAMINADOS";
                    $esPago = ($fData['tipoComprobante']==='p');
                    $urlAvance = $ftp_servidor;
                    $rutaAvance = $ftp_supportPath.$alias."/".($esPago?"T":"")."PUBLICO/";
                    $briefing.=", $fData[tipoComprobante] $alias";
                    $mensaje.="Para $alias : ";
                    if (!empty($fData["nombreInterno"])) {
                        $xmlFileName = $fData['nombreInterno'].".xml";
                        $genericXMLFilePath = $lookoutFilePath . $rutaLocal . $xmlFileName;
                        $xmlExists = file_exists($genericXMLFilePath);
                    } else {
                        $xmlFileName = "";
                        $genericXMLFilePath = "";
                        $xmlExists = false;
                    }

                    if (!empty($fData["nombreInternoPDF"])) {
                        $pdfFileName = $fData['nombreInternoPDF'].".pdf";
                        $genericPDFFilePath = $lookoutFilePath . $rutaLocal . $pdfFileName;
                        $pdfExists = file_exists($genericPDFFilePath);
                    } else {
                        $pdfFileName="";
                        $genericPDFFilePath = "";
                        $pdfExists = false;
                    }
                    
                    $uploadFailure=false;
                    $uploadedAny=false;
                    if (isset($xmlFileName[0])) {
                        $briefing.=", $xmlFileName";
                        if ($xmlExists) {
                            $mensaje.=$xmlFileName;
                            if (isset($ftpObj)) {
                                try {
                                    $ftpObj->cargarArchivoAscii($rutaAvance, $xmlFileName, $genericXMLFilePath);
                                    $uploadedAny=true;
                                    $mensaje.="[OK]";
                                    $briefing.=":OK";
                                    if (!isset($cuenta[$alias])) $cuenta[$alias]=0;
                                    $cuenta[$alias]++;
                                    $cuenta["AvanceXML"]++;
                                    switch($fData['tipoComprobante']) {
                                        case "i":
                                        case "ingreso":
                                            $cuenta["ComprobanteI"]++; break;
                                        case "e":
                                        case  "egreso":
                                            $cuenta["ComprobanteE"]++; break;
                                        case "p":
                                        case "pago":
                                            $cuenta["ComprobanteP"]++; break;
                                        case "t":
                                        case "traslado":
                                            $cuenta["ComprobanteT"]++; break;
                                    }
                                } catch (Exception $e) {
                                    $briefing.=":".$e->getMessage();
                                    $uploadFailure=true;
                                    $mensaje.="[".$e->getMessage()."]";
                                }
                            } else $briefing.=":NO FTP";
                            if ($pdfExists) $mensaje.=" y ";
                        } else {
                            $briefing.=":No Existe";
                            MIFTP::log("No existe ruta $genericXMLFilePath");
                            $mensaje.="[No existe ruta $genericXMLFilePath]";
                        }
                    } else {
                        $briefing.=", SIN XML";
                    }
                    if (isset($pdfFileName[0])) {
                        $briefing.=", $pdfFileName";
                        if ($pdfExists) {
                            $mensaje.=$pdfFileName;
                            if (isset($ftpObj)) {
                                try {
                                    $ftpObj->cargarArchivoBinario($rutaAvance, $pdfFileName, $genericPDFFilePath);
                                    $uploadedAny=true;
                                    $mensaje.="[OK]";
                                    $briefing.=":OK";
                                    if (!isset($cuenta[$alias."PDF"])) $cuenta[$alias."PDF"]=0;
                                    $cuenta[$alias."PDF"]++;
                                    $cuenta["AvancePDF"]++;
                                } catch (Exception $e) {
                                    $briefing.=":".$e->getMessage();
                                    $uploadFailure=true;
                                    $mensaje.="[".$e->getMessage()."]";
                                }
                            } else $briefing.=":NO FTP";
                        } else {
                            $briefing.=":No Existe";
                            MIFTP::log("No existe ruta $genericPDFFilePath");
                        }
                    } else {
                        $briefing.=", SIN PDF";
                    }
                    if ($uploadedAny && !$uploadFailure) {
                        $mensaje.="<!-- COMMIT BEGINS.";
                        $oldNumStatus = +$fData["statusn"];
                        $nextNumStatus = $oldNumStatus|Facturas::actionToStatusN("Respaldado");
                        $nextStatus = Facturas::statusnToStatus($nextNumStatus);
                        //$nextStatus = $invObj->nextStatus($fData['status'],"Respaldar",$fData["tipoComprobante"]);
                        //$nextNumStatus = Facturas::statusToStatusN($nextStatus);
                        if ($oldNumStatus!==$nextNumStatus) {
                            $fieldArray = ["id"=>$idFact, "statusn"=>$nextNumStatus];
                            if($fData["status"]!==$nextStatus) $fieldArray["status"]=$nextStatus;
                            $mensaje.=" DIFFERENT STATUS.";
                            if ($invObj->saveRecord($fieldArray)) {
                                $briefing.=". SAVED";
                                $mensaje.=" INVOICE SAVED.";
                                $solObj->updateStatus($idFact, Facturas::STATUS_RESPALDADO);
                                if ($prcObj->cambioFactura($idFact, $nextStatus, $username, false, "Facturas.RespD:$fData[status](".$oldNumStatus.")"))
                                    $mensaje.=" PROCESS SAVED.";
                                else
                                    $mensaje.=" PROCESS WAS NOT SAVED.";
                            } else $mensaje.=" INVOICE WAS NOT SAVED.";
                        } else {
                            $prcObj->cambioFactura($idFact, $nextStatus, $username, false, "Facturas.Respaldado otra vez");
                            $mensaje.=" SAME STATUS: NONE DB CHANGES BY $username.";
                        }
                        $mensaje.=" COMMIT ENDS -->";
                    } else $mensaje.="<!-- FAILED BACKUP -->";
                    $mensaje.="<!-- LOG:\n".MIFTP::log()." -->";
                    MIFTP::log(false);
                    $mensaje.="</LI>";
                }
                $resumen="";
                foreach($cuenta as $key=>$val) {
                    if (substr($key,0,11)!=="Comprobante" && substr($key,0,6)!=="Avance" && substr($key,-3)!=="PDF") {
                        if (substr($key,-3)==="PDF") {
                            $archTyp = "PDF";
                            $fixkey = substr($key, 0, -3);
                        } else $archTyp = "XML";
                        $resumen.="<LI><SPAN class=\"grabbable\" onclick=\"if(copyContentToClipboardByClass('grabbable')) addSibMsgByClass('grabbable', ' &nbsp; COPIADO');\" title=\"Copiar Texto\">$val XML";
                        if (!empty($cuenta[$key."PDF"])) {
                            $resumen.=" y ".$cuenta[$key."PDF"]." PDF";
                        }
                        $resumen.=" de $key</SPAN></LI>";
                    }
                }
                $c_i=$cuenta["ComprobanteI"];
                $c_e=$cuenta["ComprobanteE"];
                $c_t=$cuenta["ComprobanteT"];
                $c_p=$cuenta["ComprobanteP"];
                $hasCI=($c_i>0);
                $hasCE=($c_e>0);
                $hasCT=($c_t>0);
                $hasCP=($c_p>0);
                $c_n=0;
                if ($hasCI) $c_n++;
                if ($hasCE) $c_n++;
                if ($hasCT) $c_n++;
                if ($hasCP) $c_n++;
                if(isset($resumen[0])) {
                    $resumen.="<LI><SPAN class=\"grabbable\" onclick=\"if(copyContentToClipboardByClass('grabbable')) addSibMsgByClass('grabbable', ' &nbsp; COPIADO');\" title=\"Copiar Texto\">";
                    if($hasCI) {
                        $resumen.="$c_i Ingreso".($c_i==1?"":"s");
                        if($c_n==2) $resumen.=" y ";
                        else if($c_n>2) $resumen.=", ";
                    }
                    if($hasCE) {
                        $resumen.="$c_e Egreso".($c_e==1?"":"s");
                        if($hasCT&&$hasCP) $resumen.=", ";
                        else if ($hasCT||$hasCP) $resumen.=" y ";
                    }
                    if($hasCT) {
                        $resumen.="$c_t Traslado".($c_t==1?"":"s");
                        if($hasCP) $resumen.=" y ";
                    }
                    if($hasCP) $resumen.="$c_p Pago".($c_p==1?"":"s");
                    $resumen.="</SPAN></LI>";
                    $missing=$num-$idx;
                    if($missing>0)
                        $resumen.="<LI><SPAN class=\"grabbable\" onclick=\"if(copyContentToClipboardByClass('grabbable')) addSibMsgByClass('grabbable', ' &nbsp; COPIADO');\" title=\"Copiar Texto\">Faltan $missing</SPAN></LI>";
                }
                echo " <!-- START --><!-- $idx/$num:$briefing --><h1 class=\"centered\" id=\"backupDetailTitle\">Respaldo ($idx / $num)</h1><div class=\"centered scrollauto\" id=\"resptestlist\"><table class=\"centered\"><tr><td class=\"lefted\"><OL style=\"margin-top: 17px;\">$mensaje</OL><H3 style=\"margin-top: 10px;\">Resumen</H3><UL>$resumen</UL></td></tr></table></div> <!-- END --> ";
                flush_buffers();
                usleep(200000);
            }
//                $mensaje = "<OL style=\"margin-top: 17px;\">$mensaje</OL>"."<H3 style=\"margin-top: 10px;\">Resumen</H3><UL>$resumen</UL>";
            
        } catch (Exception $e) {
            $exMsg=$e->getMessage();
            $mensaje="<p>Error al respaldar XML y PDF directo a AVANCE</p>";
            doclog("Error al respaldar XML y PDF directo a AVANCE","error",["file"=>getShortPath(__FILE__),"GET"=>$_GET,"idx"=>$idx,"num"=>$num,"briefing"=>$briefing,"exception"=>getErrorData($e),"ftplog"=>MIFTP::log()]);
            echo "<!-- START --><!-- $idx/$num:$briefing:$exMsg --><h1 class=\"centered\" id=\"backupDetailErrorTitle\">Error en Respaldo</h1><div class=\"centered scrollauto\" id=\"resptestlist\"><table class=\"centered\"><tr><td class=\"lefted\">$mensaje\n<!-- ".MIFTP::log()." --></td></tr></table></div><!-- END -->";
        }
    //    echo "<!-- START --><h1 class=\"centered\">Respaldo Completo</h1><div class=\"centered scrollauto\" id=\"resptestlist\"><table class=\"centered\"><tr><td class=\"lefted\">$mensaje</td></tr></table></div><!-- END -->";
        flush_buffers(false);
        die();
    }
    try {
        $tarname = $lookoutFilePath."archivos/respaldo.tar";
        if (file_exists($tarname)) unlink($tarname);
        $rfile = new PharData($tarname);
$logtext = ""; $separator = "\n---------- ---------- ---------- ---------- ----------\n";
$logtext .= "\nTARNAME = $tarname\n";
        foreach($idFArr as $idFact) {
$logtext .= "\nID FACT = $idFact";
            $fData = $invObj->getData("id=$idFact");
            if (!empty($fData) && count($fData>0)) $fData = $fData[0];
            if (!empty($fData)) {
                //$empresas = str_replace("archivos", "empresas", $fData['ubicacion']);
                $esPago = ($fData['tipoComprobante']==='p');
                $empresas = str_replace("archivos", "COMPRASXML", $fData['ubicacion']);
                $empresas = substr($empresas, 0, strlen($empresas)-8).($esPago?"T":"")."PUBLICO/";
                //$empresas .= $fData['nombreInterno'].".xml";
$logtext .= "\nEMPRESAS = $empresas";
                $genericXMLFilePath = $fData['ubicacion'] . $fData['nombreInterno'].".xml";
$logtext .= "\nXMLFILE = $genericXMLFilePath";
                $compressedXMLPath = $empresas . $fData['nombreInterno'].".xml";
$logtext .= "\nXML in TAR = $compressedXMLPath";

                // * TODO: Aqui habria que validar si el archivo existe. Pero es preferible validar antes, al desplegar las facturas en respaldafactura.php, al presionar el boton se valide antes si alguna no mostraba xml. Tambien validar navegador al iniciar sesion.
                if(file_exists($lookoutFilePath.$genericXMLFilePath)) {
                    $rfile->addFile($lookoutFilePath.$genericXMLFilePath, $compressedXMLPath);
                } else {
$logtext .= "\nNO EXISTE XML: $genericXMLFilePath";
                    continue;
                }
                
                $genericPDFFilePath = $fData['ubicacion'] . $fData['nombreInternoPDF'].".pdf";
                if(file_exists($lookoutFilePath.$genericPDFFilePath)) {
$logtext .= "\nPDFFILE = $genericPDFFilePath";
                    $compressedPDFPath = $empresas . $fData['nombreInternoPDF'].".pdf";
$logtext .= "\nPDF in TAR = $compressedPDFPath";
                    $rfile->addFile($lookoutFilePath.$genericPDFFilePath, $compressedPDFPath);
                }
$logtext .= "\nXML CHECKED IN TAR $compressedXMLPath: ".(isset($rfile[$compressedXMLPath])?"OK":"NO")." . ".get_class($rfile[$compressedXMLPath]);
if (isset($compressedPDFPath))
$logtext .= "\nPDF CHECKED IN TAR $compressedPDFPath: ".(isset($rfile[$compressedPDFPath])?"OK":"NO")." . ".get_class($rfile[$compressedPDFPath]);
else $logtext .= "\nNO EXISTE PDF: $genericPDFFilePath";
            }
$logtext .= "\n";
        }
        // $rfile->addFromString($lookoutFilePath."archivos/manifest.txt","Manifesto");

        $dt = new DateTime();
        $fmt = $dt->format("ymdHis");
        $filename = "respaldar".$fmt.".tar";

        //file _put _contents ( $lookoutFilePath."archivos/respaldo.log" , "{$separator}RESPALDO $fmt : $logtext" , FILE_APPEND | LOCK_EX );

        header("Content-Type: archive/tar");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($tarname));
        readfile( $tarname );
//            header("Content-Type: text/plain");
//            header("Content-Disposition: attachment; filename=\"respaldarError.log\"; filename*=utf-8''respaldarError.log");
//            echo $filename;
//            echo PHP_EOL;
//            echo filesize($tarname);
//            echo PHP_EOL;
    } catch (Exception $e) {
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=\"respaldarError.log\"; filename*=utf-8''respaldarError.log");
        echo "EXCEPTION: ".$e->getMessage();
    }

    sort($idFArr, SORT_NUMERIC);
    $invObj->clearOrder();
    $invObj->addOrder("id");
    $statusNArr = explode("|", $invObj->getList("id",$idFArr,"statusn"));
    $invObj->clearOrder();
    // status: Temporal, Pendiente, Aceptado, Rechazado, Contrarrecibo, ExpSinContra, RespSinCX, Exportado, RespSinExp, RespSinContra, Respaldado
    // accion: Procesar, Eliminar, GenerarCR, GenerarTxt, Respaldar
    $statusMap = [];
    $oldStatusDesc = [];

    for($i=0; $i<count($statusNArr); $i++) {
        $factId=$idFArr[$i];
        $oldStatusN = $statusNArr[$i];
        //$nextStatus = $invObj->nextStatus($oldStt,"Respaldar");
        $nextStatusN = $oldStatusN|Facturas::actionToStatusN("Respaldado");
        $statusMap[$nextStatusN][] = $factId;
        $oldStt=Facturas::statusnToStatus($oldStatusN);
        $oldStatusDesc[$factId]=$oldStt."($oldStatusN=>$nextStatusN)";
        $prcObj->debugStatus($factId, Facturas::statusnToStatus($nextStatusN), $username, false, "Facturas.Resp:".$oldStatusDesc[$factId]);
    }

    //$posiblesStatus = ["RespSinCX","RespSinExp","RespSinContra","Respaldado"];

    DBi::autocommit(FALSE);
    $success=true;
    foreach($statusMap as $nuevoStatusN=>$listId) {
        if (!empty($listId)) {
            $nuevoStatus = Facturas::statusnToStatus($nuevoStatus);
            $fieldArray = ["id"=>$listId,"status"=>$nuevoStatus,"statusn"=>$nuevoStatusN];
            $success &= $invObj->saveRecord($fieldArray);
            if ($success) {
                $solObj->updateStatus($listId, Facturas::STATUS_RESPALDADO);
                foreach($listId as $fId) {
                    $prcObj->cambioFactura($fId, $nuevoStatus, $username, false, "Facturas.RespI:".$oldStatusDesc[$fId]);
                }
            }
        }
    }
    if ($success) DBi::commit();
    else          DBi::rollback();
    DBi::autocommit(TRUE);

} else if (isset($_GET["actualiza"])) {
    $actualiza = $_GET["actualiza"];
    switch ($actualiza) {
        case "consultaTipoYTasa" : actualizaTipoYTasa(); break;
        case "guardaTipoYTasa"   : guardaTipoYTasa(); break;
        case "tipoComprobante"   : actualizaTipoComprobante(); break;
    }
} else if (isset($_POST["actualiza"])) {
    $actualiza = $_POST["actualiza"];
    switch ($actualiza) {
        case "rootProcess"         : actualizaProcesoRoot(); break;
        case "fixpdf"            :
            $fixResult=$invObj->renombraPDF($_POST["id"]??0, $_POST["oldName"], $_POST["newName"], $_POST["filePath"]);
            break;
        case "fixxml"            :
            $fixResult=$invObj->renombraXML($_POST["id"]??0, $_POST["oldName"], $_POST["newName"], $_POST["filePath"]);
            break;
        case "xmlSyntax"         :
            $fixResult=$invObj->reparaXML($_POST["filepath"]??"");
            break;
        case "fixstatus"         : cambiaStatus(); break;
    }
    if (isset($fixResult)) {
        if ($fixResult===TRUE) echo json_encode(["result"=>"success"]);
        else if ($fixResult===FALSE) echo json_encode(["result"=>"failure", "message"=>"No se realizaron cambios"]);
        else if (isset($fixResult[0])) echo json_encode(["result"=>"failure", "message"=>$fixResult]);
        else echo json_encode(["result"=>"failure", "message"=>"No se realizaron cambios..."]);
    }
} else {
    echo "<!-- REFRESH -->";
    echo "<p>Consulta Facturas Sin Datos</p>";
    echo "<!-- GET:\n".arr2List($_GET)." -->\n";
    echo "<!-- POST:\n".arr2List($_POST)." -->\n";
    echo "<!-- FILES:\n".arr2List($_FILES)." -->\n";
    echo "<!-- SERVER:\n".arr2List($_SERVER)." -->\n";
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"setTimeout(doReload,3000);ekil(this);\">\n";
}
function flush_buffers($doStart=true) {
    ob_end_flush();
    if (ob_get_level()>0) ob_flush();
    flush();
    if ($doStart) ob_start();
}
function actualizaTipoComprobante() {
    global $invObj;
    //$invObj->rows_per_page = 0;
    header("Content-Type: text/plain");
    $url = $_SERVER['HTTP_ORIGIN'].$_SERVER['WEB_MD_PATH'];
    $path = $_SERVER['DOCUMENT_ROOT'];
    echo "Actualiza Tipo de Comprobante".PHP_EOL;
    $factData = $invObj->getData("tipoComprobante is NULL and ubicacion not like '%16/08/'");
    echo " # ".count($factData).PHP_EOL;
    $idx=0;
    foreach ($factData as $factura) {
        $idx++;
        echo str_pad($idx,3," ",STR_PAD_LEFT).")";
        if (empty($factura["tipoComprobante"])) {
            if (substr($factura["ubicacion"], -3)=="08/") {
                echo " [] ".$factura["ubicacion"].$factura["nombreInterno"].PHP_EOL;
                continue;
            }
            $nombre = $url.$factura["ubicacion"].$factura["nombreInterno"].".xml";
            $xml = new DOMDocument();
            $result = $xml->load($nombre);
            $start = $xml->documentElement;
            if (empty($start)) {
                echo " FALTANTE ".$nombre.PHP_EOL;
                continue;
            }
            $tipoComprobante = $start->getAttribute("tipoDeComprobante");
            echo " ".str_pad($tipoComprobante,5);
            if ($tipoComprobante != $factura["tipoComprobante"])
                echo " [".$factura["tipoComprobante"]."]";
            echo " ".$factura["nombreInterno"];

            if ($tipoComprobante != $factura["tipoComprobante"]) {
                $fieldarr = ["id"=>$factura["id"], "tipoComprobante"=>$tipoComprobante];
                $result = $invObj->saveRecord($fieldarr);
                if ($result) echo " => GUARDADO";
                else echo " => ERROR";
            }
        } else {
            echo " [".$factura["tipoComprobante"]."] ".$factura["nombreInterno"];
        }
        eolCheck();
        echo PHP_EOL;
    }
    echo " - - - - - - - - - - - - - - - - - - - - - - - - - - -".PHP_EOL;
    echo $invObj->log;
}
function actualizaTipoYTasa() {
    global $invObj;
    //$invObj->rows_per_page = 0;
    $factData = $invObj->getData();
    
    header("Content-Type: text/plain");

    $idx=0;
    foreach ($factData as $factura) {
        $idx++;
        echo str_pad($idx,3," ",STR_PAD_LEFT).")";
        $nombre = "$_SERVER[HTTP_ORIGIN]$_SERVER[WEB_MD_PATH]".$factura["ubicacion"].$factura["nombreInterno"].".xml";
        echo " ".str_pad("$nombre",55);
        $xml = new DOMDocument();
        $result = $xml->load($nombre);
        $start = $xml->documentElement;
        $ns = $start->getAttribute("xmlns:cfdi");
        $tipoCambio = $start->getAttribute("TipoCambio");
        echo str_pad($tipoCambio,5);
        $listaTraslado = $xml->getElementsByTagNameNS($ns, "Traslado");
        if ($listaTraslado.length>0) {
            $traslado = $listaTraslado->item(0);
            $tasa = $traslado->getAttribute("tasa");
            echo $tasa;
        } else echo "0";
        eolCheck();
        echo PHP_EOL;
    }
}
function guardaTipoYTasa() {
    global $invObj;
    //$invObj->rows_per_page = 0;
    $factData = $invObj->getData();
    header("Content-Type: text/plain");
    $idx=0;
    foreach ($factData as $factura) {
        $idx++;
        echo str_pad($idx,3," ",STR_PAD_LEFT).")";
        $nombre = "$_SERVER[HTTP_ORIGIN]$_SERVER[WEB_MD_PATH]".$factura["ubicacion"].$factura["nombreInterno"].".xml";
        echo " ID:".str_pad($factura["id"],4," ",STR_PAD_BOTH);
        $xml = new DOMDocument();
        $result = $xml->load($nombre);
        $start = $xml->documentElement;
        $ns = $start->getAttribute("xmlns:cfdi");
        $tipoCambio = $start->getAttribute("TipoCambio");
        if (!empty($tipoCambio)) $tipoCambio = floatVal($tipoCambio);
        else $tipoCambio = floatVal("1.0");
        echo str_pad($tipoCambio,5);
        $trasladoArr = $xml->getElementsByTagNameNS($ns, "Traslado");
        if ($trasladoArr.length>0) {
            $traslado=$trasladoArr->item(0);
            $tasa = $traslado->getAttribute("tasa");
        }
        if (!empty($tasa)) $tasa = floatVal($tasa);
        else $tasa = floatVal("0.0");
        echo $tasa;
        $fieldarr = ["id"=>$factura["id"], "tipoCambio"=>$tipoCambio, "tasaIva"=>$tasa];
        $result = $invObj->saveRecord($fieldarr);
        if ($result) echo " GUARDADO";
        else echo " ERROR";
        eolCheck();
        echo PHP_EOL;
    }
}
function logProcesoRoot($txt, $flag=FILE_APPEND|LOCK_EX) {
    $dt = new DateTime();
    $fmt = $dt->format("ymdHis");
    //file _put_contents("C:/Apache24/logs/invoiceRootProcess.log","[$fmt] $txt\n",$flag);
}
function actualizaProcesoRoot() {
    //file _put_contents ( $lookoutFilePath."archivos/respaldo.log" , "{$separator}RESPALDO $fmt : $logtext" , FILE_APPEND | LOCK_EX );
    logProcesoRoot("Actualiza ProcesoRoot",LOCK_EX);
    
    //testProcesoRoot();
    //actualizaSaldoPago();
    //actualizaFacturasSinFolio();
    //actualizaPDFSinPrefijo()

    //actualizaSolicitudesSinFolio();
    actualizaFacturasSinUsoCFDI();
}
function testProcesoRoot() {
    logProcesoRoot("INI testProcesoRoot");
    flush_buffers();
    echo json_encode([["eName"=>"TABLE","className"=>"centered","eChilds"=>[["eName"=>"THEAD","eChilds"=>["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"#"],["eName"=>"TH","eText"=>"UNO"],["eName"=>"TH","eText"=>"DOS"],["eName"=>"TH","eText"=>"TRES"]]]],["eName"=>"TBODY","id"=>"rootTableBody"]]]])."|*ICHK*|";
    for ($i=0; $i < 6; $i++) { 
        flush_buffers();
        usleep(490000); // sleep(1);
        logProcesoRoot("1");
        echo json_encode(["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>"".(5*$i+1)],["eName"=>"TD","eText"=>"ALPHA"],["eName"=>"TD","eText"=>"BETA"],["eName"=>"TD","eText"=>"THETA"]]])."|*ICHK*|";
        flush_buffers();
        usleep(490000); // sleep(1);
        logProcesoRoot("2");
        echo json_encode(["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>"".(5*$i+2)],["eName"=>"TD","eText"=>"PAPA"],["eName"=>"TD","eText"=>"MAMA"],["eName"=>"TD","eText"=>"HIJO"]]])."|*ICHK*|";
        flush_buffers();
        usleep(490000); // sleep(1);
        logProcesoRoot("3");
        echo json_encode(["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>"".(5*$i+3)],["eName"=>"TD","eText"=>"HOLA"],["eName"=>"TD","eText"=>"MUNDO"],["eName"=>"TD","eText"=>"CRUEL"]]])."|*ICHK*|";
        flush_buffers();
        usleep(490000); // sleep(1);
        logProcesoRoot("4");
        echo json_encode(["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>"".(5*$i+4)],["eName"=>"TD","eText"=>"MARA"],["eName"=>"TD","eText"=>"SALVA"],["eName"=>"TD","eText"=>"TRUCHA"]]])."|*ICHK*|";
        flush_buffers();
        usleep(490000); // sleep(1);
        logProcesoRoot("5");
        echo json_encode(["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>"".(5*$i+5)],["eName"=>"TD","eText"=>"RUMBA"],["eName"=>"TD","eText"=>"ZAMBA"],["eName"=>"TD","eText"=>"MAMBO"]]])."|*ICHK*|";
    }
    flush_buffers(false);
}
function cambiaStatus() {
    sessionInit();
    if(empty(getUser()->perfiles) || (!in_array("Administrador",getUser()->perfiles) && !in_array("Sistemas",getUser()->perfiles))) {
        errNDie("Accion desconocida",["errmsg"=>"Accion desconocida","perfiles"=>getUser()->perfiles]);
    }
    $invId=$_POST["invId"]??"";
    if (empty($invId)) {
        errNDie("Factura no identificada",["errmsg"=>"Factura no identificada"]);
    } else {
        $username = (isset($_SESSION['user'])?$_SESSION['user']->nombre:null);
        $statusn=$_POST["statusn"]??-1;
        $tipocomp=$_POST["tipoComprobante"]??"i";
        if ($statusn<0) {
            errNDie("Status no indicado",["errmsg"=>"Status no indicado"]);
        } else {
            global $invObj;
            //$invObj->rows_per_page = 0;
            $status=Facturas::statusnToDetailStatus($statusn,$tipocomp);
            $fieldArray=["id"=>$invId,"statusn"=>$statusn,"status"=>$status];
            if ($statusn===0) {
                $fieldArray["fechaAprobacion"]=null;
            }
            if ($invObj->saveRecord($fieldArray)) {
                if (!isset($solObj)) {
                    require_once "clases/SolicitudPago.php";
                    $solObj = new SolicitudPago();
                }
                if (!isset($prcObj)) {
                    require_once "clases/Proceso.php";
                    $prcObj = new Proceso();
                }
                $solObj->updateStatus($invId, $statusn);
                $prcObj->cambioFactura($invId, $status, "admin", false, "ADMIN FIX STATUS");
                echo json_encode(["result"=>"exito","message"=>"Status cambiado a $status"]);
            } else {
                echo json_encode(["result"=>"error","errmsg"=>"Error al cambiar status","errors"=>$invObj->errors]);
            }
        }
    }
}
function actualizaSaldoPago() {
    logProcesoRoot("INI actualizaSaldoPago");
    // TODO: Actualizar saldoReciboPago en recibos de pago, sumar monto de Pagos en cada recibo y guardar en el campo saldoReciboPago de dicho comprobante
    global $invObj;
    //$invObj->rows_per_page=0;
    $data = $invObj->getData("tipoComprobante='p' AND saldoReciboPago is NULL and statusn&1",0,"id,ubicacion,nombreInterno"); // 1574
    echo json_encode([["eName"=>"TABLE","className"=>"centered","eChilds"=>[["eName"=>"THEAD","eChilds"=>["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"#".count($data)],["eName"=>"TH","eText"=>"ID"],["eName"=>"TH","eText"=>"UBICACION"],["eName"=>"TH","eText"=>"NOMBREINTERNO"],["eName"=>"TH","eText"=>"SALDORECIBOPAGO"],["eName"=>"TH","eText"=>"STATUS"]]]],["eName"=>"TBODY","id"=>"rootTableBody"]]]])."|*ICHK*|";
    require_once("clases/CFDI.php");
    //require_once("clases/Proceso.php");
    for($i=0; isset($data[$i]); $i++) {
        flush_buffers();
        usleep(100000);
        $id=$data[$i]["id"];
        $ubicacion=$data[$i]["ubicacion"];
        $nombreInterno=$data[$i]["nombreInterno"];
        $status="INICIAL";
        $saldoReciboPago=NULL;
        if (empty($id)) {
            $status="SIN ID";
        } else if (empty($ubicacion)) {
            $status="SIN UBICACION";
        } else if (empty($nombreInterno)) {
            $status="SIN XML";
        } else {
            $cfdiObj = CFDI::newInstanceByLocalName("../".$ubicacion.$nombreInterno.".xml");
            if ($cfdiObj!==null) {
                $montoPagos = $cfdiObj->get("pago_monto");
                if (is_scalar($montoPagos)) $saldoReciboPago = +$montoPagos;
                else {
                    $saldoReciboPago=0;
                    foreach($montoPagos as $monto) $saldoReciboPago += +$monto;
                }
                if (isset($saldoReciboPago)) {
                    $fieldarray = ["id"=>$id, "saldoReciboPago"=>$saldoReciboPago];
                    if ($invObj->saveRecord($fieldarray)) $status="EXITO";
                    else $status="FRACASO";
                } else $status="VACIO";
            } else $status="NULO";
        }
        $arr=["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>($i+1)],["eName"=>"TD","eText"=>$id],["eName"=>"TD","eText"=>$ubicacion],["eName"=>"TD","eText"=>$nombreInterno],["eName"=>"TD","eText"=>$saldoReciboPago],["eName"=>"TD","eText"=>$status]]];
        echo json_encode($arr)."|*ICHK*|";
    }
    flush_buffers(false);
}
function actualizaFacturasSinUsoCFDI() {
    clearstatcache();
    global $invObj;
    $where="version in ('3.3','4.0') AND nombreInterno IS NOT NULL AND usoCFDI IS NULL";
    $totalData=$invObj->getData($where,0,"count(1) n");
    $total=+$totalData[0]["n"];
    $invObj->rows_per_page=3380;
    $invObj->clearOrder();
    $invObj->addOrder("id", "desc");
    $fieldNames=["id","codigoProveedor","tipoComprobante","ubicacion","nombreInterno","usoCFDI","status","statusn"];
    $data = $invObj->getData($where,0,implode(",",$fieldNames));
    $hdrs=[["eName"=>"TH","eText"=>"#".count($data)]];
    foreach($fieldNames as $fld) {
        switch($fld) {
            case "codigoProveedor": $fld="proveedor"; break;
            case "tipoComprobante": $fld="tc"; break;
            case "nombreInterno": $fld="xml"; break;
            case "nombreInternoPDF": $fld="pdf"; break;
            case "statusn": $fld="sn"; break;
        }
        $hdrs[]=["eName"=>"TH","eText"=>strtoupper($fld)];
    }
    $hdrs[]=["eName"=>"TH","eText"=>"STATE"];
    $hdrs[]=["eName"=>"TH","eText"=>"TIME"];
    echo json_encode([["eName"=>"TABLE","className"=>"centered","eChilds"=>[["eName"=>"THEAD","eChilds"=>["eName"=>"TR","eChilds"=>$hdrs]],["eName"=>"TBODY","id"=>"rootTableBody"]]]])."|*ICHK*|";
    flush_buffers();
    usleep(10000);
    require_once("clases/CFDI.php");
    set_time_limit(60*30); // 30 minutos
    $beginTime=lapse(true);
    for($i=0; isset($data[$i]); $i++) {
        $val=[];
        $state=NULL;
        $fieldarray=[];
        $cambioStatus=false;
        $errmsg="";
        foreach($fieldNames as $fld) {
            $val[$fld]=$data[$i][$fld];
            if (empty($val[$fld])) switch($fld) {
                case "id":
                case "ubicacion":
                case "nombreInterno":
                    $state="SIN ".strtoupper($fld);
                    break 2;
            }
        }
        if (empty($state)) {
            DBi::clearErrors();
            $cfdiObj = CFDI::newInstanceByLocalName("../$val[ubicacion]$val[nombreInterno].xml");
            if ($cfdiObj!==null) {
                $receptor=$cfdiObj->get("receptor");
                $val["usoCFDI"]=$receptor["@usocfdi"]??"NUL";
                $fieldarray=["id"=>$val["id"],"usoCFDI"=>$val["usoCFDI"]];
                if ($invObj->saveRecord($fieldarray)) {
                    $state="EXITO";
                } else {
                    $state = "ERROR";
                    $errmsg=json_encode(DBi::$errors);
                }
            } else {
                if(isset(CFDI::getLastError()["exception"])) {
                    $ex=CFDI::getLastError()["exception"];
                    if ($ex->getCode()===CFDI::CFDI_VER32_EXCEPTION) $state="VER32";
                    else $state="CFDI NULO";
                } else $state="CFDI NULO";
                $errmsg=CFDI::getLastError()["log"];
            }
        }
        $cell=[["eName"=>"TD","eText"=>($total-$i)]];
        foreach($val as $vc) {
            $cell[]=["eName"=>"TD","eText"=>$vc];
        }
        $cell[]=["eName"=>"TD","eText"=>$state];
        $cell[]=["eName"=>"TD","eText"=>number_format((float)lapse($beginTime),2,".","")];
        $arr=["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>$cell];
        if (isset($errmsg[0])) $arr["title"]=$errmsg;
        echo json_encode($arr)."|*ICHK*|";
        flush_buffers();
        usleep(10000);
    }
    flush_buffers(false);
}
function actualizaFacturasSinFolio() {
    logProcesoRoot("INI actualizaFacturasSinFolio");
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    // TODO: Actualizar nombre de archivos sin folio que tengan los ultimos 10 caracteres en lugar de los ultimos 4
    clearstatcache();
    global $invObj;
    //$invObj->rows_per_page=0;
    $fieldNames=["id","uuid","codigoProveedor","tipoComprobante","ubicacion","nombreInterno","nombreInternoPDF","status","statusn"];
    $data = $invObj->getData("folio IS NULL AND uuid IS NOT NULL",0,implode(",",$fieldNames)); // 1574
    logProcesoRoot($invObj->log);
    $invObj->log="\n// xxxxxxxxxxxxxx Facturas xxxxxxxxxxxxxx //\n";
    logProcesoRoot("#) "."ID | UUID | CODIGOPROVEEDOR | TIPOCOMPROBANTE | UBICACION | NOMBREXML | NOMBREPDF | STATUS | STATUSN"." | STATE");
    $hdrs=[["eName"=>"TH","eText"=>"#".count($data)]];
    foreach($fieldNames as $fld) {
        switch($fld) {
            case "codigoProveedor": $fld="proveedor"; break;
            case "tipoComprobante": $fld="tc"; break;
            case "nombreInterno": $fld="xml"; break;
            case "nombreInternoPDF": $fld="pdf"; break;
            case "statusn": $fld="sn"; break;
        }
        $hdrs[]=["eName"=>"TH","eText"=>strtoupper($fld)];
    }
    $hdrs[]=["eName"=>"TH","eText"=>"STATE"];
    echo json_encode([["eName"=>"TABLE","className"=>"centered","eChilds"=>[["eName"=>"THEAD","eChilds"=>["eName"=>"TR","eChilds"=>$hdrs]],["eName"=>"TBODY","id"=>"rootTableBody"]]]])."|*ICHK*|";
    flush_buffers();
    usleep(25000);
    require_once("clases/CFDI.php");
    
    //require_once("clases/Proceso.php");
    for($i=0; isset($data[$i]); $i++) {
        //logProcesoRoot($i);
        $val=[];
        $state=NULL;
        $fieldarray=[];
        $cambioStatus=false;
        $errmsg="";
        foreach($fieldNames as $fld) {
            $val[$fld]=$data[$i][$fld];
            if (empty($val[$fld])) switch($fld) {
                case "id":
                case "uuid":
                case "tipoComprobante":
                case "ubicacion":
                case "nombreInterno":
                    $state="SIN ".strtoupper($fld);
                    break 2;
            }
        }
        if (isset($val["codigoProveedor"])) {
            if (!isset($prvObj)) {
                require_once("clases/Proveedores.php");
                $prvObj=new Proveedores();
            }
            $val["codigoProveedor"]=$prvObj->getValue("codigo",$val["codigoProveedor"],"rfc");
        }
        //logProcesoRoot(json_encode($val));
        if (empty($state)) {
            //logProcesoRoot("../$val[ubicacion]$val[nombreInterno].xml");
            $cfdiObj = CFDI::newInstanceByLocalName("../$val[ubicacion]$val[nombreInterno].xml");
            //logProcesoRoot("*_____");
            if ($cfdiObj!==null) {
                //logProcesoRoot("_*____");
                $folio = $cfdiObj->get("folio");
                $uuid = strtoupper($cfdiObj->get("uuid"));
                $tc = strtolower($cfdiObj->get("tipo_comprobante"));
                if ($tc==="egreso"||$tc==="e") $fileSuffix="NC_";
                else if ($tc==="p") $fileSuffix="RP_";
                else if ($tc==="t") $fileSuffix="TR_";
                else $fileSuffix="";
                if (empty($folio)) {
                    //logProcesoRoot("__*___");
                    $fileSuffix=substr($uuid,-10);
                    $emisor=$cfdiObj->get("emisor");
                    $rfcEmisor=utf8_encode($emisor["@rfc"]);
                    $nombreXML=$rfcEmisor."_".$fileSuffix;
                    $nombrePDF=$fileSuffix.$rfcEmisor;
// SHIFT -24 spaces
if ($val["nombreInterno"]!==$nombreXML) {
//logProcesoRoot("___*__");
if (file_exists("../$val[ubicacion]$val[nombreInterno].xml")) {
    if (rename("../$val[ubicacion]$val[nombreInterno].xml","../$val[ubicacion]$nombreXML.xml")) {
        sleep(3);
        if (empty($fieldarray)) $fieldarray["id"]=$val["id"];
        $fieldarray["nombreInterno"]=$nombreXML;
        $sn=+$val["statusn"];
        if (Facturas::estaRespaldado($sn)) {
            $sn-=Facturas::actionToStatusN("Respaldado");
            $status=Facturas::statusnToDetailStatus($sn,$tc);
            $fieldarray["statusn"]=$sn;
            $fieldarray["status"]=$status;
            $cambioStatus=true;
        }
    } else $state="FALLO RENAME XML";
} else if (file_exists("../$val[ubicacion]$nombreXML.xml")) {
    if (empty($fieldarray)) $fieldarray["id"]=$val["id"];
    $fieldarray["nombreInterno"]=$nombreXML;
    $sn=+$val["statusn"];
    if (Facturas::estaRespaldado($sn)) {
        $sn-=Facturas::actionToStatusN("Respaldado");
        $status=Facturas::statusnToDetailStatus($sn,$tc);
        $fieldarray["statusn"]=$sn;
        $fieldarray["status"]=$status;
        $cambioStatus=true;
    }
} else $state = "OTRO XML";
}
if(!empty($val["nombreInternoPDF"])&&$val["nombreInternoPDF"]!==$nombrePDF){
if (file_exists("../$val[ubicacion]$val[nombreInternoPDF].pdf")) {
    if (rename("../$val[ubicacion]$val[nombreInternoPDF].pdf","../$val[ubicacion]$nombrePDF.pdf")) {
        sleep(3);
        if (empty($fieldarray)) $fieldarray["id"]=$val["id"];
        $fieldarray["nombreInternoPDF"]=$nombrePDF;
        doclog("RENOMBRAR PDF", "pdf", $baseData+["line"=>__LINE__,"oldxml"=>$val["nombreInterno"],"newxml"=>$nombreXML,"oldpdf"=>$val["nombreInternoPDF"],"newpdf"=>$nombrePDF]);
        if (!$cambioStatus) {
            $sn=+$val["statusn"];
            if (Facturas::estaRespaldado($sn)) {
                $sn-=Facturas::actionToStatusN("Respaldado");
                $status=Facturas::statusnToDetailStatus($sn,$tc);
                $fieldarray["statusn"]=$sn;
                $fieldarray["status"]=$status;
                $cambioStatus=true;
            }
        }
    } else {
        if ($state==="FALLO RENAME XML") $state.=" Y PDF";
        else {
            if (!empty($state)) $state.=" Y ";
            $state.="FALLO RENAME PDF";
        }
    }
} else if (file_exists("../$val[ubicacion]$nombrePDF.pdf")) {
    if (empty($fieldarray)) $fieldarray["id"]=$val["id"];
    $fieldarray["nombreInternoPDF"]=$nombrePDF;
    doclog("RENOMBRAR PDF", "pdf", $baseData+["line"=>__LINE__,"xml"=>$xmlname,"pdf"=>$pdfname]);
    if (!$cambioStatus) {
        $sn=+$val["statusn"];
        if (Facturas::estaRespaldado($sn)) {
            $sn-=Facturas::actionToStatusN("Respaldado");
            $status=Facturas::statusnToDetailStatus($sn,$tc);
            $fieldarray["statusn"]=$sn;
            $fieldarray["status"]=$status;
            $cambioStatus=true;
        }
    }
} else {
    if ($state==="OTRO XML") $state.=" Y PDF";
    else {
        if (!empty($state)) $state.=" Y ";
        $state.="OTRO PDF";
    }
}
}
                    // SHIFT BACK +24 spaces
                    if (empty($state) && !empty($fieldarray) && $invObj->saveRecord($fieldarray)) {
                        $state="EXITO";
                        if (isset($fieldarray["nombreInterno"])) {
                            $state.=" XML";
                            if (isset($fieldarray["nombreInternoPDF"]))
                                $state.=" Y";
                        }
                        if (isset($fieldarray["nombreInternoPDF"])) $state.=" PDF";
                        if ($cambioStatus) {
                            if (!isset($solObj)) {
                                require_once "clases/SolicitudPago.php";
                                $solObj = new SolicitudPago();
                            }
                            if (!isset($prcObj)) {
                                require_once "clases/Proceso.php";
                                $prcObj = new Proceso();
                            }
                            $solObj->updateStatus($val["id"], -Facturas::STATUS_RESPALDADO);
                            if ($prcObj->cambioFactura($val["id"], $status, "admin", false, "Root Process"))
                                $state .= " Y PROCESO";
                            else
                                $state .= " SIN PROCESO";
                        }
                    } else if (empty($state) && !empty($fieldarray)) {
                        $state = "FALLO SAVE";
                        $errmsg=$invObj->log;
                        $invObj->log="\n// xxxxxxxxxxxxxx Facturas xxxxxxxxxxxxxx //\n";
                    }
                }
            } else {
                //logProcesoRoot("_x____");
                if(isset(CFDI::getLastError()["exception"])) {
                    //logProcesoRoot("__x___");
                    $ex=CFDI::getLastError()["exception"];
                    //logProcesoRoot("___x__");
                    //logProcesoRoot($ex->getCode());
                    if ($ex->getCode()===CFDI::CFDI_VER32_EXCEPTION) $state="VER32";
                    else $state="CFDI NULO";
                    //logProcesoRoot("____x_");
                } else $state="CFDI NULO";
                //logProcesoRoot("x");
                $errmsg=CFDI::getLastError()["log"];
                //logProcesoRoot("y");
            }
        } else {
            if ($state==="SIN NOMBREINTERNO") {

            }
            logProcesoRoot("STATE: ".$state);
        }
        $logText="".($i+1).") ";
        $cell=[["eName"=>"TD","eText"=>($i+1)]];
        foreach($val as $vc) {
            $logText.="$vc | ";
            $cell[]=["eName"=>"TD","eText"=>$vc];
        }
        if (empty($state)) $state="OK";
        $logText.=$state;
        $cell[]=["eName"=>"TD","eText"=>$state];
        $arr=["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>$cell];
        logProcesoRoot($logText);
        if (!empty($fieldarray)) logProcesoRoot("TO SAVE: ".json_encode($fieldarray));
        if (!empty($errmsg)) logProcesoRoot("ERROR: ".$errmsg);
        echo json_encode($arr)."|*ICHK*|";
        flush_buffers();
        usleep(25000);
    }
    flush_buffers(false);
    logProcesoRoot("END actualizaFacturasSinFolio");

}
function actualizaSolicitudesSinFolio() {
    flush_buffers();
    echo json_encode([["eName"=>"TABLE","className"=>"centered pad2c","eChilds"=>[["eName"=>"THEAD","eChilds"=>["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"#"],["eName"=>"TH","eText"=>"ID"],["eName"=>"TH","eText"=>"EMPRESA"],["eName"=>"TH","eText"=>"FECHA"],["eName"=>"TH","eText"=>"FOLIO"]]]],["eName"=>"TBODY","id"=>"rootTableBody"]]]])."|*ICHK*|";
    global $solObj, $query;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj=new SolicitudPago();
    }
    $solObj->rows_per_page=100;
    $solData = $solObj->getData("s.folio IS NULL",0,"s.id solId, g.id gpoId, g.alias, g.cut, s.fechaInicio","s inner join grupo g on s.idEmpresa=g.id");
    foreach ($solData as $idx => $row) {
        $pfx=$row["cut"].substr($row["fechaInicio"],2,2).substr($row["fechaInicio"],5,2);
        $folio=$solObj->getFolio($pfx, $desc);
        $callQuery=$query;
        if (isset($folio)) {
            DBi::clearErrors();
            $folioCell=["eName"=>"TD"];
            if ($solObj->saveRecord(["id"=>$row["solId"],"folio"=>$folio])) {
                $folioCell["eText"]=$folio;
            } else {
                $errors = DBi::$errors;
                $msg=["QRY: $query"];
                if (!isset($errors[0])) $msg[]="NO ERRORS";
                else foreach ($errors as $errIdx => $value) {
                    $msg[]="ERROR $errIdx: $value";
                }
                $folio="X".substr($row["cut"],1)."X".substr($folio, 4);
                if (!isset($msg[0])) $msg=["EMPTY ERRORS"];
                $txtMsg=implode("\n",$msg);
                $folioCell["title"]=$txtMsg;
                $txtMsg.="\n".$callQuery;
                $folioCell["eChilds"]=[["eText"=>$folio],["eComment"=>$txtMsg]];
            }
        } else {
            $msg="\nQRY: ".$callQuery;
            $errors = DBi::$errors;
            if (isset($errors)) foreach ($errors as $errIdx => $errVal) {
                $msg.="\nERR {$errIdx}: $errVal";
            }
            $folioCell=["eName"=>"TD", "title"=>$desc, "eChilds"=>[["eText"=>"NULL"],["eComment"=>$desc.$msg]]];
        }
        $num=$idx+1;
        echo json_encode(["eName"=>"TR","eParId"=>"rootTableBody","eChilds"=>[["eName"=>"TD","eText"=>"$num"],["eName"=>"TD","eText"=>$row["solId"]],["eName"=>"TD","eText"=>$row["alias"]],["eName"=>"TD","eText"=>substr($row["fechaInicio"],0,10)],$folioCell]])."|*ICHK*|";
        if (isset($solData[$num]))
            flush_buffers();
    }
    flush_buffers(false);
}
function actualizaPDFSinPrefijo() {
    //global $invObj;
    //$invObj->rows_per_page = 0;
    // TODO: Actualizar PDFs de egresos y pagos que los archivos no tengan prefijo NC_ o RP_
}
function eolCheck() {
    if (!defined('PHP_EOL')) {
        switch (strtoupper(substr(PHP_OS, 0, 3))) {
            // Windows
            case 'WIN':
                define('PHP_EOL', "\r\n");
                break;
            // Mac
            case 'DAR':
                define('PHP_EOL', "\r");
                break;
            // Unix
            default:
                define('PHP_EOL', "\n");
        }
    }
}
function isESASAPayment() {
    return isset($_POST["command"]) && $_POST["command"]==="eSASAPayment";
}
function doESASAPayment() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    sessionInit();
    if (!hasUser()) {
        echoJsNDie("refresh","Sin sesion");
    }
    if (!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Carga Egresos")) {
        errNDie("No autorizado",$baseData+["line"=>__LINE__]);
    }
    if (!isset($_POST["filename"])) {
        errNDie("Desconocido",$baseData+["line"=>__LINE__]);
    }
    $paymPath="C:\\InvoiceCheckShare\\PAGOS\\";
    $plogPath="C:\\InvoiceCheckShare\\pagos.log";
    if (!file_exists("{$paymPath}$_POST[filename]")) {
        errNDie("Inexistente",$baseData+["line"=>__LINE__]);
    }
    global $invObj;
    //$invObj->rows_per_page = 0;
    $fmt = (new DateTime())->format("yMd H:i:s");
    if (filesize($plogPath)>0) $logPrefix="-----";
    else $logPrefix="";
    //$text = file_get_contents("{$paymPath}$_POST[filename]");
    
    $lines = file("{$paymPath}$_POST[filename]",FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastHead="";
    $currHead="";
    $text="";
    $object=[];
    $section="";
    $level=0;
    $dtRng="";
    $numInv=0;
    $colNames=["Proveedor","Fact/Rem","Fecha","Cantidad","I V A","T O T A L","Tipo","Referencia"];
    $colIdx=[];
    $colRng=[[0,10],[0,0],[0,8],[-8,8],[-11,5],[-7,9],[0,0],[0,0]];
    $colValues=["","","","","","","",""];
    $sum=[];
    $num=[];
    $log="";
    foreach ($lines as $oneline) {
        $trimline=trim($oneline);
        if (!isset($trimline[0])) continue; // Quitar lineas que solo tenian espacios
        if ($trimline==="El Total de ingresos en el periodo comprendido entre:") {
            $section="FOOT";
            $level=0;
        } else if (preg_match('/^(\d+) de (\w+) de (\d+)$/',$trimline,$matches)===1) { // Fecha actual
            //if ($section==="DATA") {
                //$log.="#$level".PHP_EOL;
            //}
            $section="HEAD";
            //if(isset($text[0])) $text.=PHP_EOL;
            //$text.=$matches[1]."/".strtolower(substr($matches[2], 0, 3))."/".$matches[3];
            $currHead=$matches[1].strtolower(substr($matches[2], 0, 3)).substr($matches[3],2,2);
            $level=0;
            //$log.="H{$level}|";
        } else if ($section==="HEAD") {
            $level++;
            switch($level) {
                case 1: { // Pagina
                    //if (preg_match('/^Pagina (\d+)$/',$trimline,$matches)===1) $text.=". Pag ".$matches[1];
                    //else $text.=". ERRPag $trimline";
                    break;
                }
                case 2: { // Nombre de la empresa
                    //$text.=". $trimline";
                    break;
                }
                case 3: { // Direccion de empresa
                    break;
                }
                case 4: { // RFC empresa
                    //$text.=PHP_EOL."$trimline";
                    $currHead.=" $trimline";
                    break;
                }
                case 5: { // Titulo reporte
                    //$text.=". ".$trimline;
                    $currHead.=" $trimline";
                    break;
                }
                case 6: {
                    $ret=preg_match('/Del &#65533;(\d+) de (\w+) de &#65533;(\d+) al &#65533;(\d+) de (\w+) de &#65533;(\d+)/',$trimline,$matches);
                    //if ($ret===false) $text.=". ERROR REGEXP [[$trimline]]";
                    //else if ($ret===0) $text.=". NO ES FECHA [[$trimline]]";
                    //else if ($ret!==1) $text.=". OTRO RESULTADO [[$trimline]]";
                    //else $text.=". ".$matches[1]."/".strtolower(substr($matches[2], 0, 3))."/".$matches[3]." - ".$matches[4]."/".strtolower(substr($matches[5], 0, 3))."/".$matches[6];
                    if($ret===1) $dtRng=$matches[1].strtolower(substr($matches[2], 0, 3)).substr($matches[3],2,2)."-".$matches[4].strtolower(substr($matches[5], 0, 3)).substr($matches[6],2,2);
                    else $dtRng="XXXXXXX-XXXXXXX";
                    $currHead.=" $dtRng";
                    break;
                }
                default: {
                    if ($trimline[0]==="=") break;
                    if (substr($trimline, 0, 9)===$colNames[0]) {
                        for ($i=0; isset($colNames[$i]); $i++) {
                            $tmp=strpos($oneline, $colNames[$i]);
                            if ($i==0 && $tmp===FALSE) break;
                            $colIdx[$i]=$tmp;
                        }
                        $section="DATA";
                        //$log.=PHP_EOL;

                        /*
                        if ($currHead!==$lastHead) {
                            if (isset($text[0])) {
                                $text.=PHP_EOL;
                                $log.=" #{$numInv}";
                                $numInv=0;
                            }
                            $text.=$currHead;
                            $lastHead=$currHead;
                        }
                        */

                        //$text.=PHP_EOL.implode(" | ", $colNames);
                    } //else $text.=PHP_EOL."[ [ $level-$trimline ] ]";
                }
            }
            //$log.=$section[0]."{$level}|";
        } else if ($section==="DATA") {
            if (substr($trimline,0,2)==="--") continue;
            $lineLength=strlen($oneline);
            for ($i=0; isset($colNames[$i]); $i++) {
                if (!isset($colIdx[$i]) || $colIdx[$i]===FALSE) continue;
                $idx = $colIdx[$i];
                $rng = $colRng[$i];
                $len = $rng[1]-$rng[0];
                if ($len==0) {
                    if (!isset($colIdx[$i+1])) $len=$lineLength-$idx; // -$rng[0]
                    else if ($colIdx[$i+1]===FALSE) continue;
                    else $len=$colIdx[$i+1]-$idx; // -$rng[0]
                }
                $celltext=substr($oneline, $idx+$rng[0], $len);
                $trimcell=trim($celltext);
                if (isset($colValues[$i])) {
                    if (isset($trimcell[0])) $colValues[$i]=$trimcell;
                    else if ($i>2) $colValues[$i]=""; // Conservar valor anterior en las primeras 3 celdas
                }
                if (isset($trimcell[0])&&isset($dtRng[0])&&$i>=3&&$i<=5) {
                    if (!isset($sum[$dtRng])) $sum[$dtRng]=[0,0,0];
                    $sum[$dtRng][$i-3] += +str_replace([",","'"], "", $trimcell);
                }
            }
            if (substr($colValues[1],0,2)==="F-") {
                $colValues[1]=substr($colValues[1],2);
                $_folio=$colValues[1];
                $_codprv=$colValues[0];
                $factData = $invObj->getData("folio='$_folio' && codigoProveedor='$_codprv'",0,"id,fechaFactura,rfcGrupo,total,status,statusn,(statusn&32)>0 pagado");
                if (isset($factData[0]["id"])) {
                    $factData=$factData[0];
                    $colValues[8]=$factData["id"];
                    $colValues[9]=$factData["fechaFactura"];
                    $colValues[10]=$factData["total"];
                    $colValues[11]=$factData["statusn"];
                    $colValues[12]=$factData["status"];
                    $colValues[13]=(empty($factData["pagado"])?"NO":"SI");
                    /*
                    if (empty($factData["pagado"])) {
                        // APLICAR PAGO, guardar campos
                        // NO APLICAR PAGO. Esto debe hacerse solo al guardar archivo
                    }
                    */
                }
            }
            if (isset($dtRng[0])) {
                if (!isset($num[$dtRng])) $num[$dtRng]=0;
                $num[$dtRng]++;
            }
            $erConditions=[["/^Pago$/","/^Egreso.+$/","PagoEgreso"],["/^Pago$/",null,"Pagos"],["/Pago anticipo/",null,"PagoAnticipo"],["/Pago/",null,"OtrosPagos"],[null,null,"Otros"]];
            foreach ($erConditions as $erData) {
                if ( (!isset($erData[0]) || preg_match($erData[0], $colValues[6])===1) &&
                     (!isset($erData[1]) || preg_match($erData[1], $colValues[7])===1) ) {
                    if (!isset($num[$erData[2]])) $num[$erData[2]]=0;
                    $num[$erData[2]]++;
                    if (!isset($sum[$erData[2]])) $sum[$erData[2]]=[0,0,0];
                    for($i=0;$i<3;$i++)
                        if(isset($colValues[$i+3][0]))
                            $sum[$erData[2]][$i] += +str_replace([",","'"],"",$colValues[$i+3]);
                    break;
                }
            }
            /*
            $text.=PHP_EOL.implode(" | ", $colValues);
            */
            //$text.="<tr><td>".implode("</td><td>",$colValues)."</td></tr>";
            if (!isset($object[0])) {
                $className="nowrap greyedbg";
                $object[]=["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>$className,"eText"=>"PRV"],["eName"=>"TH","className"=>$className,"eText"=>"FOLIO"],["eName"=>"TH","className"=>$className,"eText"=>"FECHA"],["eName"=>"TH","className"=>$className,"eText"=>"TOTAL"],["eName"=>"TH","className"=>$className,"eText"=>"SALDO"],["eName"=>"TH","className"=>$className,"eText"=>"REFERENCIA"],["eName"=>"TH","className"=>$className,"eText"=>"STATUS"]]];
            }
            $className="nowrap";
            if (substr($colValues[7], 0,6)!=="Egreso") $className.=" reddenbg";
            else if ($colValues[6]!=="Pago") $className.=" yellowedbg";
            //else $className.=" greenHighlight";
            /*
            $cells=[];
            foreach ($colValues as $idx=>$val) {
                $cell=["eName"=>"TD","className"=>$className,"eText"=>$val];
                if ($idx>=3&&$idx<=5) $cell["className"].=" righted";
                $cells[]=$cell;
            }
            $object[]=["eName"=>"TR","eChilds"=>$cells];
            */
            $factTotal=+$colValues[10];
            $pagoTotal=+str_replace(",","",$colValues[5]);
            $saldo=$factTotal-$pagoTotal;
            $object[]=["eName"=>"TR","eChilds"=>[["eName"=>"TD","className"=>$className,"eText"=>$colValues[0]],["eName"=>"TD","id"=>$colValues[8],"className"=>$className,"eText"=>"F ".$colValues[1]],["eName"=>"TD","title"=>"Creacion: ".$colValues[9],"className"=>$className,"eText"=>$colValues[2]],["eName"=>"TD","className"=>$className." righted","title"=>"Total Factura: $".number_format($factTotal,2),"eText"=>"$".$colValues[5]],["eName"=>"TD","className"=>$className." righted","eText"=>"$".number_format($saldo,2)],["eName"=>"TD","className"=>$className,"eText"=>$colValues[6]." ".$colValues[7]],["eName"=>"TD","className"=>$className,"statusn"=>$colValues[11],"eText"=>$colValues[12]]]];

            $numInv++;
        } else if ($section==="FOOT") {
            if (substr($trimline, 0, 8)==="Total de") {
                if (preg_match('/Total de ([^:]+):(?:\s+([\d\',\.]+)){1,3}/',$trimline,$matches)===1) {
                    /*
                    $text.=PHP_EOL."Total ".$matches[1].": ".$matches[2];
                    if (isset($matches[3])) $text.=" | ".$matches[3];
                    if (isset($matches[4])) $text.=" | ".$matches[4];
                    */
                    //$text.="<tr><th colspan=\"3\">Total ".$matches[1]."</th><th>".$matches[2]."</th>";
                    //if (isset($matches[3])) $text.="<th>".$matches[3]."</th>";
                    //if (isset($matches[4])) $text.="<th>".$matches[4]."</th>";
                    //$text.="</tr>";
                    if (!empty($matches[4])) $stotal=$matches[4];
                    else if (!empty($matches[3])) $stotal=$matches[3];
                    else if (!empty($matches[2])) $stotal=$matches[2];
                    else $stotal="0.00";
                    $cells=[["eName"=>"TH","colSpan"=>"3","eText"=>"Total ".$matches[1]],["eName"=>"TH","className"=>"righted","eText"=>"$".$stotal],["eName"=>"TH","colSpan"=>"3","className"=>"righted","eText"=>""]];
                    $object[]=["eName"=>"TR","eChilds"=>$cells];
                } /* else $text.=PHP_EOL."[ ".$trimline." ]"; */
            } else if (preg_match('/El (\d+) de (\w+) de (\d+) al (\d+) de (\w+) de (\d+) fue:(?:\s+([\d\',\.]+)){1,3}/',$trimline,$matches)===1) {
                /*
                $text.=PHP_EOL."Total Ingreso ".$matches[1].strtolower(substr($matches[2], 0, 3)).substr($matches[3],2,2)."-".$matches[4].strtolower(substr($matches[5], 0, 3)).substr($matches[6],2,2).": ".$matches[7];
                if (isset($matches[8])) $text.=" | ".$matches[8];
                if (isset($matches[9])) $text.=" | ".$matches[9];
                */
                //$text.="<tr><th colspan=\"3\">Total ".$matches[1].strtolower(substr($matches[2], 0, 3)).substr($matches[3],2,2)."-".$matches[4].strtolower(substr($matches[5], 0, 3)).substr($matches[6],2,2)."</th><th>".$matches[7]."</th>";
                //if (isset($matches[8])) $text.="<th>".$matches[8]."</th>";
                //if (isset($matches[9])) $text.="<th>".$matches[9]."</th>";
                //$text.="</tr>";
                if (!empty($matches[9])) $stotal=$matches[9];
                else if (!empty($matches[8])) $stotal=$matches[8];
                else if (!empty($matches[7])) $stotal=$matches[7];
                else $stotal="0";
                $cells=[["eName"=>"TH","colSpan"=>"3","eText"=>"Total ".$matches[1].strtolower(substr($matches[2], 0, 3)).substr($matches[3],2,2)."-".$matches[4].strtolower(substr($matches[5], 0, 3)).substr($matches[6],2,2)],["eName"=>"TH","className"=>"righted","eText"=>"$".$stotal],["eName"=>"TH","colSpan"=>"3","className"=>"righted","eText"=>""]];
                $object[]=["eName"=>"TR","eChilds"=>$cells];
            } //else $text.=PHP_EOL."[ ".$trimline." ]";
        }
    // ToDo: usar rango de fecha y particularmente mes para agrupar datos ignorando paginas y generando html de tabla para visualizar en pantalla.
    }
    foreach ($sum as $key => $value) {
        //$text.=PHP_EOL."SUM $key: ".implode(" | ", $value);
        //array_walk($value, function(&$v, $k) {
        //    $v=number_format(+$v,2);
        //});
        //$text.="<tr><th colspan=\"3\">SUMA $key</th><th>".implode("</th><th>",$value)."</th></tr>";
        $cells=[["eName"=>"TH","className"=>"righted","eText"=>"#".$num[$key]],["eName"=>"TH","colSpan"=>"2","eText"=>"$key"]];
        //foreach ($value as $val) $cells[]=["eName"=>"TH","className"=>"righted","eText"=>($val!=0?number_format(+$val,2):"")];
        $totval=0;
        if (!empty($value[2])) $totval=$value[2];
        else if (!empty($value[1])) $totval=$value[1];
        else if (!empty($value[0])) $totval=$value[0];
        $cells[]=["eName"=>"TH","className"=>"nowrap righted","eText"=>"$".number_format($totval,2)];
        $cells[]=["eName"=>"TD","colSpan"=>"3","eText"=>""];
        $object[]=["eName"=>"TR","eChilds"=>$cells];
    }
    $log.=" #{$numInv}";
    if (isset($log[0])) file_put_contents($plogPath,"{$logPrefix}[$fmt] L O G: $log".PHP_EOL, FILE_APPEND | LOCK_EX);
    //echo json_encode(["result"=>"exito","object"=>[["eName"=>"H1","eText"=>$_POST["filename"]],["eName"=>"PRE","className"=>"fixed","eText"=>$text]]]);
    echo json_encode(["result"=>"exito","object"=>[["eName"=>"TABLE","eChilds"=>[["eName"=>"TBODY","eChilds"=>$object]]]]]);
    //echo json_encode(["result"=>"CHECK","message"=>"Archivo Recibido $_POST[filename]"]);
}
function isAppendPDFService() {
    return isset($_FILES["appendpdffile"]);
}
function getAppendPDFService() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    sessionInit();
    global $invObj;
    //$invObj->rows_per_page = 0;
    if (isset($_POST["id"]))         $id         = $_POST["id"];
    if (isset($_POST["nombre"]))     $nombre     = $_POST["nombre"];
    if (isset($_POST["folio"]))      $folio      = $_POST["folio"];
    if (isset($_POST["serie"]))      $serie      = $_POST["serie"];
    if (isset($_POST["rfc_emisor"])) $rfc_emisor = $_POST["rfc_emisor"];
    if (isset($_POST["ubicacion"]))  $ubicacion  = $_POST["ubicacion"];
    if (isset($_FILES["appendpdffile"])) {
        $file_info = $_FILES["appendpdffile"];
        $file_name = $file_info["name"];
        $file_type = $file_info["type"];
        $file_size = $file_info["size"];
        $file_error = $file_info["error"];
        if ($file_error==0 && $file_type==="application/pdf" && $file_size>100) {
            if (!isset($folio[0])) {
                $chunks=explode("_", $nombre);
                if ($chunks[0]===$rfc_emisor) $folio=$chunks[1];
                else {
                    // toDo: Leer CFDI, obtener folio o uuid, asignar valor a $folio
                    $folio="ID{$id}";
                }
            }
            $invData=$invObj->getData("id=$id",0,"nombreInternoPDF,tipoComprobante");
            $dateSuffix="";
            $pdfName=$invData[0]["nombreInternoPDF"]??"";
            $tc=strtolower($invData[0]["tipoComprobante"]??"");
            $document_root = $_SERVER["DOCUMENT_ROOT"];
            $http_origin = $_SERVER["HTTP_ORIGIN"];
            $path=$document_root.$ubicacion;
            if (isset($pdfName[0])) {
                $absName=$path.$pdfName.".pdf";
                if (file_exists($absName)) {
                    $dateSuffix=date("_YmdHis", filemtime($absName));
                    rename($absName, $path.$pdfName.$dateSuffix.".pdf");
                    sleep(3);
                }
            } else { // no hay factura o no tiene nombre de pdf
                $pfx=isset($tc[0])?($tc==="e"?"NC_":($tc==="p"?"CP_":"")):"";
                $pdfName = $pfx.$folio.$rfc_emisor; // toDo: agregar sufijo: segundos desde la fecha de creacion de la factura
                $absName=$path.$pdfName.".pdf";
                if (file_exists($absName)) {
                    $altInvData=$invObj->getData("nombreInternoPDF='$pdfName'",0,"id");
                    if (isset($altInvData[0]["id"]) && $altInvData[0]["id"]!=$id) {
                        if (!isset($serie[0])) {
                            doclog("Ya existe una factura con ese nombre de archivo","error",$baseData+["line"=>__LINE__,"iniInvId"=>$id,"pdfName"=>$pdfName,"ubicacion"=>$ubicacion,"altId"=>$altInvData[0]["id"]]);
                            echo "Error: Archivo ambiguo '$pdfName'";
                            return;
                        }
                        $pdfName = $pfx.$serie.$folio.$rfc_emisor;
                        $absName=$path.$pdfName.".pdf";
                        if (file_exists($absName)) { // no debería existir
                            $altInvData2=$invObj->getData("nombreInternoPDF='$pdfName'",0,"id");
                            if (isset($altInvData2[0]["id"]) && $altInvData2[0]["id"]!=$id) {
                                doclog("Ya existe archivo con serie, pero no se declaro en la factura","error",$baseData+["line"=>__LINE__,"invId"=>$id,"pdfName"=>$pdfName,"ubicacion"=>$ubicacion]);
                                echo "Error: Archivo ambiguo 2 '$pdfName'";
                                return;
                            }
                        }
                    }
                }
            }
            $fieldarray = ["id"=>$id, "nombreInternoPDF"=>$pdfName];
            global $query;
            if (isset($dateSuffix[0]) || $invObj->saveRecord($fieldarray) || empty(DBi::$errno)) {
                if(move_uploaded_file($file_info["tmp_name"], $document_root.$ubicacion.$pdfName.".pdf")) {
                    doclog(isset($invData[0]["nombreInternoPDF"][0])?"REEMPLAZAR PDF":"ASIGNAR PDF", "pdf", $baseData+["line"=>__LINE__,"pdf"=>$pdfName,"dateSuffix"=>$dateSuffix]);
                    echo "$http_origin$ubicacion{$pdfName}.pdf";
                } else {
                    doclog("Error en move_uploaded_file", "error", $baseData+["line"=>__LINE__,"pdf"=>$pdfName,"dateSuffix"=>$dateSuffix,"files"=>$_FILES,"lastError"=>$error_get_last()]);
                    echo "Error al cargar archivo";
                }
            } else {
                doclog("Error al guardar factura", "error", $baseData+["line"=>__LINE__,"pdf"=>$pdfName,"dateSuffix"=>$dateSuffix,"query"=>$query,"DBiErrors"=>DBi::$errors,"InvObjErrors"=>$invObj->errors,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
                echo "Error al guardar archivo";
            }
        } else if ($file_error!=0) {
            doclog("Error en FileUpload", "error", $baseData+["line"=>__LINE__,"files"=>$_FILES,"errno"=>$file_error]);
            echo "Error al cargar archivo: '$file_error'";
        } else if ($file_type!=="application/pdf") {
            doclog("Error en FileType", "error", $baseData+["line"=>__LINE__,"files"=>$_FILES,"filetype"=>$file_type]);
            echo "Error: El tipo de archivo debe ser 'pdf', no '$file_type'";
        } else if ($file_size<=100) {
            doclog("Error en tamaño de archivo", "error", $baseData+["line"=>__LINE__,"files"=>$_FILES,"filesize"=>$file_size]);
            echo "Error: Archivo vacio";
        } else {
            doclog("Error desconocido al cargar archivo", "error", $baseData+["line"=>__LINE__,"files"=>$_FILES]);
            echo "Error al cargar archivo";
        }
    }
}
function isSaveInvoiceInPaymReq() {
    return "saveInvoiceInPaymReq"===($_POST["action"]??"");
}
function doSaveInvoiceInPaymReq() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    sessionInit();
    if (!hasUser()) errNDie("Sin sesion",$baseData+["line"=>__LINE__]);
    global $solObj, $invObj, $query;
    $solId=$_POST["solId"]??"";
    $solFol=$_POST["solFol"]??"";
    $invId=$_POST["invId"]??"";
    $invFol=$_POST["invFol"]??"";
    if (!isset($invId[0])) errNDie("Factura $invFol no identificada",$baseData+["line"=>__LINE__]);
    if (!isset($solId[0])) errNDie("Solicitud $solFol no identificada",$baseData+["line"=>__LINE__]);
    if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
    $invData=$invObj->getData("id=$invId");
    if (!isset($invData[0])) errNDie("Factura $invFol no encontrada",$baseData+["line"=>__LINE__]);
    $invData=$invData[0];
    if (!isset($invFol[0])) $invFol=$invData["folio"];
    else if ($invFol!==$invData["folio"]) errNDie("Folio de factura $invFol no corresponde",$baseData+["line"=>__LINE__, "dbInvFolio"=>$invData["folio"]]);
    if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
    $solData=$solObj->getData("id=$solId",0,"id,folio,idFactura");
    if (!isset($solData[0])) errNDie("Solicitud $solFol no encontrada",$baseData+["line"=>__LINE__]);
    $solData=$solData[0];
    if (!isset($solFol[0])) $solFol=$solData["folio"];
    else if ($solFol!==$solData["folio"]) errNDie("Folio de solicitud $solFol no corresponde",$baseData+["line"=>__LINE__, "dbSolFolio"=>$solData["folio"]]);
    /* // Comentado para permitir anexar una factura en diferentes solicitudes
    $solData=$solObj->getData("idFactura=$invId");
    if (isset($solData[0]["id"])) errNDie("La factura $invFol ya está relacionada con otra solicitud",$baseData+["line"=>__LINE__]);
    */
    // Validar que la factura no esté cancelada, que no tenga contra recibo, (que no esté pagada)
    if (isset($invData["statusn"]) && $invData["statusn"]>=Facturas::STATUS_RECHAZADO) errNDie("Factura $invFol rechazada",$baseData+["line"=>__LINE__]);
    // Validar que la solicitud no tenga idFactura
    DBi::autocommit(false);
    if (!$solObj->saveRecord(["id"=>$solId,"idFactura"=>$invId])) {
        $solQuery=$query;
        $errno=DBi::getErrno();
        $error=DBi::getError();
        if (!$errno) { // No hay cambios, ya se había asignado la factura
            doclog("Factura $invFol previamente asignada a la solicitud $solFol, sin cambios en la base", "solpago", $baseData+["line"=>__LINE__,"query"=>$solQuery]);
        } else {
            if ($errno==1062) $message="La factura $invFol ya está asignada a otra solicitud";
            else $message="Error al guardar solicitud $solFol";
            DBi::rollback();
            DBi::autocommit(true);
            errNDie($message, $baseData+["line"=>__LINE__,"query"=>$solQuery,"errors"=>DBi::$errors]);
        }
    } // else doclog("Factura $invFol guardada en la solicitud $solFol con éxito", "solpago", $baseData+["line"=>__LINE__,"query"=>$query]);
    if ($invData["status"]==="Temporal") $invStatusN=0;
    else $invStatusN=+$invData["statusn"];
    $invStatusN=$invStatusN|Facturas::STATUS_ACEPTADO|Facturas::STATUS_PAGADO;
    $invStatus=Facturas::statusnToDetailStatus($invStatusN);
    if (!$invObj->saveRecord(["id"=>$invId,"status"=>$invStatus,"statusn"=>$invStatusN])) {
        $invQuery=$query;
        $errno=DBi::getErrno();
        $error=DBi::getError();
        if (!$errno) { // No hay cambios, ya se había asignado la factura
            doclog("Factura $invFol ya ha sido marcada como pagada", "solpago", $baseData+["line"=>__LINE__,"query"=>$invQuery]);
        } else {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Error al guardar factura $invFol", $baseData+["line"=>__LINE__,"query"=>$invQuery,"errors"=>DBi::$errors]);
        }
    } // else doclog("Factura $invFol guardada con éxito", "solpago", $baseData+["line"=>__LINE__,"query"=>$query]);
// FACTURASB: ini. Guardar numAutorizadas=1
    global $ctrObj, $ctfObj;
    if (!isset($ctfObj)) { require_once "clases/Contrafacturas.php"; $ctfObj = new Contrafacturas(); }
    if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj = new Contrarrecibos(); }
    $ctfData=$ctfObj->getData("idFactura=$invId");
    if (isset($ctfData[0]["id"])) {
        if (!$ctrObj->saveRecord(["id"=>$ctfData[0]["idContrarrecibo"],"numAutorizadas"=>1])) {
            $ctrQuery=$query;
            $errno=DBi::getErrno();
            $error=DBi::getError();
            if (!$errno) { // No hay cambios, ya se había asignado la factura
                doclog("El contrarrecibo ya ha sido marcado como autorizado", "solpago", $baseData+["line"=>__LINE__,"query"=>$ctrQuery]);
            } else {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("Error al guardar Contra recibo", $baseData+["line"=>__LINE__,"query"=>$ctrQuery,"errors"=>DBi::$errors]);
            }
        }
    } else {
        // toDo: si no tiene contra recibo, crearlo con contra factura y autorizarlo
        // toDo: verificar que el contra recibo aparezca como pagado
        ;
    }
    // toDo: Agregar Proceso de Factura Autorizada // + Contra recibo
    global $prcObj;
    if (!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj=new Proceso(); }
    if (!$prcObj->cambioFactura($invId, $invStatus, getUser()->nombre, false, "Anexada a Solicitud $solFol")) {
        doclog("Error al guardar proceso","solpago",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors]);
    }
    // toDo: respaldar archivos a avance
// FACTURSAB: end
    global $firObj;
    if (!isset($firObj)) { require_once "clases/Firmas.php"; $firObj=new Firmas(); }
    if (!$firObj->saveRecord(["idUsuario"=>getUser()->id,"modulo"=>"solpago","idReferencia"=>$solId,"accion"=>"completa"])) {
        doclog("Error al guardar firma","solpago",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors]);
        //DBi::rollback();
        //DBi::autocommit(true);
        //errNDie("Error al guardar Firma", $baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors]);
    }
    DBi::commit();
    DBi::autocommit(true);
    successNDie("Factura $invFol asignada a Solicitud $solFol satisfactoriamente");
}
function isVerifyInv4PaymReq() {
    return "verifyInvoiceForPaymReq"===($_POST["action"]??"");
}
function doVerifyInv4PaymReq() {
    sessionInit();
    if (!hasUser()) echoJsNDie("refresh","No User");
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre]+$_POST;
    global $solObj, $invObj, $ordObj, $gpoObj, $prvObj, $cptObj, $query;
    $solId=$_POST["solId"]??"0";
    $gpoId=$_POST["gpoId"]??"0";
    $prvId=$_POST["prvId"]??"0";
    $invIdf=$_POST["invIdf"]??"0";
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj=new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId",0,"id,folio,idFactura,idOrden,idEmpresa");
    if (!isset($solData[0]["id"])) { errNDie("No fue posible identificar la solicitud de pago",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$solObj->numrows]+$_POST); }
    $solData=$solData[0];
    if (isset($solData["idFactura"])) {
        $invId="$solData[idFactura]";
        if (isset($invId[0])) { errNDie("La solicitud ya tiene una factura",$baseData+["line"=>__LINE__,"solIdFactura"=>$invId]+$_POST); }
    }
    if ("$gpoId"!=="$solData[idEmpresa]") { errNDie("Empresa incorrecta ($gpoId <> $solData[idEmpresa])",$baseData+["line"=>__LINE__,"solIdEmpresa"=>$solData["idEmpresa"]]+$_POST); }
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $gpoData=$gpoObj->getData("id=$gpoId",0,"rfc");
    if (!isset($gpoData[0]["rfc"])) { errNDie("No fue posible identificar la empresa del corporativo",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$gpoObj->numrows]); }
    $gpoData=$gpoData[0];
    if (!isset($ordObj)) {
        require_once "clases/OrdenesCompra.php";
        $ordObj=new OrdenesCompra();
    }
    $ordData=$ordObj->getData("id=$solData[idOrden]");
    if (!isset($ordData[0]["id"])) { errNDie("No fue posible identificar la orden de compra",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$ordObj->numrows]); }
    $ordData=$ordData[0];
    if ("$gpoId"!=="$ordData[idEmpresa]") { errNDie("Empresa ambigua ($gpoId <> $ordData[idEmpresa])",$baseData+["line"=>__LINE__,"webGpoId"=>$gpoId,"dbOGpoId"=>$ordData["idEmpresa"]]); }
    if ("$prvId"!=="$ordData[idProveedor]") { errNDie("Proveedor ambiguo ($prvId <> $ordData[idProveedor])",$baseData+["line"=>__LINE__,"webPrvId"=>$prvId,"dbOPrvId"=>$ordData["idProveedor"]]); }
    if (!isset($prvObj)) {
        require_once "clases/Proveedores.php";
        $prvObj=new Proveedores();
    }
    $prvData=$prvObj->getData("id=$prvId",0,"codigo");
    if (!isset($prvData[0]["codigo"])) { errNDie("No fue posible identificar al proveedor",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$prvObj->numrows]); }
    $prvData=$prvData[0];

    $ba=unpack("C*", $invIdf);
    doclog("BYTE ARRAY", "test", ["original"=>$invIdf,"byteArray"=>$ba]);

    $invIdf=str_replace("‐", "-", $invIdf);
    //NO SIRVIO//$invIdf=normalize_to_utf8_chars($invIdf);
    $invUIdf=strtoupper($invIdf);
    $invObj->clearOrder();
    $invObj->addOrder("ciclo","desc");
    $invData=$invObj->getData("codigoProveedor='$prvData[codigo]' and rfcGrupo='$gpoData[rfc]' and (folio='$invIdf' or uuid like '%{$invUIdf}%')",0,"id,ciclo,pedido,uuid,serie,folio,subtotal,total,moneda,status,statusn");
    $query = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', normalize_to_utf8_chars($query));
    if (!isset($invData[0]["id"])) { errNDie("Debe realizar Alta de Factura para que se reconozca el folio",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$invObj->numrows]); }
    if (isset($invData[1])) {
        $validIdx=-1;
        $validYr=false;
        foreach($invData as $invIdx=>$invItem) {
            $iisttn=$invItem["statusn"]??null;
            if (!isset($iisttn) || $iisttn>=128) continue;
            if ($validIdx<0) {
                $validIdx=$invIdx;
                $validYr=$invItem["ciclo"];
            } else {
                if ($validYr===$invItem["ciclo"]) $validIdx=-1;
                break;
            }
        }
        // ToDo: Si solo una factura coincide exactamente con el folio elegir esa factura. (si hay más de una factura con el mismo folio: a- elegir la del ciclo mayor(agregar ciclo), b- err:resultado ambiguo)
        if ($validIdx<0) errNDie("Resultado ambiguo, ingrese al menos 6 caracteres del uuid",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$invObj->numrows,"data"=>$invData]);
        else $invData=$invData[$validIdx];
    } else $invData=$invData[0];
    $solData2=$solObj->getData("idFactura=$invData[id]",0,"id,folio,idFactura,idOrden,idEmpresa");
    if (isset($solData2[0]["id"])) errNDie("La factura ya está asignada a la solicitud ".$solData2[0]["folio"],$baseData+["line"=>__LINE__,"invData"=>$invData,"solData"=>$solData,"solData2"=>$solData2]);

    $invStatusN=$invData["statusn"]??null;
    $invStatus=$invData["status"]??"";
    if (!isset($invStatusN) || $invStatus==="Temporal") errNDie("Debe realizar Alta de Factura para que se reconozca el folio",$baseData+["line"=>__LINE__,"invData"=>$invData]);
    if ($invStatusN>=128) errNDie("No puede anexar una factura cancelada",$baseData+["line"=>__LINE__,"invData"=>$invData]);
    /*
    DBi::autocommit(false);
    if (!$solObj->saveRecord(["id"=>$solId,"idFactura"=>$invData["id"]])) { DBi::rollback(); DBi::autocommit(true); errNDie("Ocurrió un error al guardar la solicitud",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$invObj->numrows]); }
    */
    if (!isset($cptObj)) {
        require_once "clases/Conceptos.php";
        $cptObj=new Conceptos();
    }
    $cptData=$cptObj->getData("idFactura=$invData[id]",0,"");
    if (!isset($cptData[0])) {
        errNDie("Debe realizar Alta de Factura para que se reconozca el folio",$baseData+["line"=>__LINE__,"cptData"=>$cptData]);
    }
    //successNDie("La factura es válida para asignar a la solicitud",[]);
    echo json_encode(["result"=>"success","message"=>"La factura es válida para asignar a la solicitud","invId"=>$invData["id"],"tot"=>$invData["total"],"mon"=>$invData["moneda"],"folio"=>$invData["folio"],"uuid"=>$invData["uuid"]]);
}
function isShowAutoUploadService() {
    return "showAutoUpload"===($_POST["action"]??"");
}
function doShowAutoUploadService() {
    sessionInit();
    if (!hasUser()) echoJsNDie("refresh","No User");
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    doclog("SERVICE: BEGINS", "autoupload", ["line"=>__LINE__]+$baseData);
    // Mostrar filtros: rango de fecha, empresa, folio de cfdi, uuid, tipos de error (tipo, metodo, descripcion y/o datos), status (en proceso, ingresado, ya existe archivo, proveedor no registrado, ya existe confirmado en bd, otro, eliminado)
    echoJSDoc("error","Servicio en construcción",null,null,"autoupload");
}
function isAutoUploadService() {
    return "startAutoUpload"===($_POST["action"]??"");
}
function doAutoUploadService() {
    sessionInit();
    if (!hasUser()) echoJsNDie("refresh","No User");
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    doclog("SERVICE: BEGINS", "autoupload", ["line"=>__LINE__]+$baseData);
    global $auiObj;
    if (!isset($auiObj)) {
        require_once "clases/AutoUploadInvoice.php";
        $auiObj=new AutoUploadInvoice();
    }
    AutoUploadInvoice::$inclusiveSeparator=$_POST["inclusiveSeparator"]??"***";
    $invList=$auiObj->getInvList();
    $invN=count($invList);
    $errN=count($auiObj->getErrList());
    if ($invN>0) {
        echoJSDoc("upkeep","SERVICE: HAS VALID LIST",AutoUploadInvoice::$inclusiveSeparator,["invN"=>$invN, "errN"=>$errN],"autoupload");
        $upRes=$auiObj->uploadList();
        if ($upRes["nSvd"]>0) {
            $res="success";
            $msg="SERVICE: UPLOAD SUCCESS";
            if ($upRes["nErr"]>0) $msg.=" con errores";
        } else if ($upRes["nErr"]>0) {
            $res="error";
            $msg="SERVICE: UPLOAD FAILURE";
        }
        echoJSDoc($res,$msg,null,$upRes,"autoupload");
    } else if ($errN>0) {
        echoJSDoc("error","SERVICE: HAS ERROR LIST",null,["invN"=>$invN, "errN"=>$errN],"autoupload");
    } else echoJSDoc("error","SERVICE: EMPTY LIST",null,null,"autoupload");
}
// SOLICITUDES DE PAGO //
// (0) CARGA FACTURA ALTA PREVIA //
// - Cuando los datos coincidan con una factura dada de alta previamente, si es valida y unica se anexa a la solicitud. Aún no se genera la solicitud.
function isAppendInvoiceService() {
    return "findInvoiceForRequest"===($_POST["action"]??"");
}
function getAppendInvoiceService() {
    sessionInit();
    if (!hasUser()) echoJsNDie("refresh","No User");
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre]+$_POST;
    //"folio":"000","uuid":"3789D010D1","gpoId":"5","prvId":"2296"
    global $invObj, $query;
    $whereList=["f.tipoComprobante='i'","f.version in ('3.3','4.0')","f.statusn is not null"]; // ,"f.statusn is not null","f.ciclo='2021'" o este año
    // Por algún motivo se había aceptado buscar facturas en status Temporal, pero no deberían aceptarse pues no han sido dadas de alta. Esperar a ver si resultan quejas...
    $pfx="";
    $gpoId=$_POST["gpoId"]??"";
    $prvId=$_POST["prvId"]??"";
    $folio=$_POST["folio"]??"";
    $uuid=strtoupper($_POST["uuid"]??"");
    if (preg_match("/[^A-Za-z0-9-]/", $uuid)) {
        $uuidHex=bin2hex($uuid);
        $uuidHexFix=str_replace(["c28090","e28090"],"2d",$uuidHex);
        $uuidHexFix2Bin=hex2bin($uuidHexFix);
        if ($uuid!==$uuidHexFix2Bin) {
            $uuidCheck=preg_replace("/[^A-Za-z0-9]/", "", $uuid);
            $uuidCheck2=preg_replace("/[^A-Za-z0-9]/", "", $uuidHexFix2Bin);
            if($uuidCheck===$uuidCheck2) $uuid=$uuidHexFix2Bin;
            else {
                $uuidFix=["original"=>$uuid];
                $uuidFix=["hex"=>$uuidHex];
                $uuidFix=["hexFix"=>$uuidHexFix2Bin];
                $uuidFix=["trans"=>iconv("UTF-8", "ISO-8859-1//TRANSLIT", $uuid)];
                doclog("FIX ENCODING 1","solpago",$uuidFix);
            }
        } else {
            $uuidFix=["original"=>$uuid];
            $uuidFix=["hex"=>$uuidHex];
            $uuidFix=["hexFix"=>$uuidHexFix2Bin];
            $uuidFix=["trans"=>iconv("UTF-8", "ISO-8859-1//TRANSLIT", $uuid)];
            doclog("FIX ENCODING 2","solpago",$uuidFix);
        }
    }
    /*
    $uuidFix=["original"=>$uuid];
    $uuidFix["hex"]=$uuidHex;
    $uuidFix["fixHex2Bin"]=hex2bin($uuidHexFix);
    $uuidFix["dashHardCoded"]=str_replace(["Â€","â€"], "-", $uuid);
    $uuidFix["icp1252"]=iconv('CP1252', 'UTF-8//IGNORE', $uuid);
    $uuidFix["isoTrans"]=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $uuid);
    $uuidFix["isoIgnore"]=iconv("UTF-8", "ISO-8859-1//IGNORE", $uuid);
    //$uuidFix["isoPlain"]=iconv("UTF-8", "ISO-8859-1", $uuid);
    $uuidFix["htmlentities"]=htmlentities($uuid);
    $uuidFix["htmlentQW1252"]=htmlentities($uuid, ENT_QUOTES, "Windows-1252");
    $uuidFix["utf82Win"]=mb_convert_encoding($uuid, "Windows-1252", "UTF-8");
    $uuidFix["win2Utf8"]=mb_convert_encoding($uuid, "UTF-8", "Windows-1252");
    $uuidFix["utf82Iso"]=mb_convert_encoding($uuid, "ISO-8859-1", "UTF-8");
    $uuidFix["iso2Utf8"]=mb_convert_encoding($uuid, "UTF-8", "ISO-8859-1");
    $uuidFix["auto2Utf8"]=mb_convert_encoding($uuid, "UTF-8", "auto");
    $uuidFix["utf8enc"]=utf8_encode($uuid);
    $uuidFix["utf8dec"]=utf8_decode($uuid);
    if (strcmp($uuid, $uuidFix["win2Utf8"])!=0) {
        doclog("FIX ENCODING","solpago",$uuidFix);
    }
    */
    if (isset($gpoId[0])) $whereList[]="g.id=$gpoId";
    if (isset($prvId[0])) $whereList[]="p.id=$prvId";
    if (isset($folio[0])) $whereList[]="f.folio='{$folio}'";
    if (isset($uuid[0])) {
        $uuid=strtoupper($uuid);
        $whereList=["f.uuid like '%{$uuid}%'"];
        $extraSelect=", f.tipoComprobante, f.version";
        $invObj->rows_per_page=1000;
    } else {
        $invObj->rows_per_page=10;
        $extraSelect="";
    }
    $preQryTime=microtime(true);
    $invData=$invObj->getData(implode(" AND ", $whereList),0,"g.id gpoId, g.alias gpoAlias, p.id prvId, f.folio, f.uuid, f.id invId, f.statusn, f.pedido, f.remision, f.subtotal, f.importeDescuento descuento, f.impuestoRetenido isr, f.impuestoTraslado iva, f.total, f.ubicacion ruta, f.nombreInterno xml, f.nombreInternoPDF pdf, f.ea, p.esServicio sv, p.conCodgEnDesc cd{$extraSelect}","f inner join grupo g on f.rfcGrupo=g.rfc inner join proveedores p on f.codigoProveedor=p.codigo");
    $qryTime=microtime(true)-$preQryTime;
    //doclog("Busqueda de factura","solpago",["whereList"=>$whereList,"query"=>$query,"invData"=>$invData,"qryTime"=>$qryTime,"log"=>$invObj->log]);
    // ToDo_SOLICITUD: no contra recibo, no pagada, no cancelada, no solicitud.
    // ToDo_SOLICITUD: agregar conceptos, pedido, subtotal, descuento, isr, iva, total
    if (isset($invData[0]["tipoComprobante"][0]) && isset($invData[0]["version"][0])) { // f.tipoComprobante='i', f.version in ('3.3','4.0')
        $valData=[];
        foreach ($invData as $idx=>$rowData) {
            if ($rowData["tipoComprobante"][0]=="i" && $rowData["version"]=="4.0") //($rowData["version"]=="3.3"||$rowData["version"]=="4.0"))
                $valData[]=$rowData;
        }
        if (!isset($valData[0])) {
            if ($invData[0]["tipoComprobante"][0]!="i") errNDie("El comprobante debe ser de tipo factura",$baseData+["line"=>__LINE__,"tc"=>$invData[0]["tipoComprobante"],"query"=>$query]);
            if (/*$invData[0]["version"]!="3.3" && */$invData[0]["version"]!="4.0") errNDie("La versión de la factura debe ser 4.0",$baseData+["line"=>__LINE__,"tc"=>$invData[0]["version"],"query"=>$query]);
        }
        $invData=$valData;
    }
    if (isset($invData[0])) {
        if (isset($invData[1])) {
            // ToDo_SOLICITUD: Ponderar si tiene contra recibo, pagada, cancelada o con solicitud quitar de la lista, aunque se puede prestar a confusión y que aparezca una factura que no es
            errNDie("Demasiados resultados comunes, afine su busqueda",$baseData+["line"=>__LINE__,"query"=>$query,"numrows"=>$invObj->numrows]);
        }
        $idt=$invData[0];
        if (!isset($idt["pdf"][0])) {
            errNDie("La factura no tiene PDF, debe agregarlo primero",$baseData+["line"=>__LINE__,"invId"=>$idt["invId"]]);
        }
        $cd=$idt["cd"];
        global $cptObj;
        if(!isset($cptObj)) {
            require_once "clases/Conceptos.php";
            $cptObj = new Conceptos();
        }
        $idt["conceptos"]=$cptObj->getData("idFactura=$idt[invId]",0,"id,idFactura,codigoArticulo codigo,cantidad,unidad,claveunidad,claveprodserv,descripcion,precioUnitario valorunitario,importe");
        require_once "clases/catalogoSAT.php";
        foreach ($idt["conceptos"] as $cIdx => $cnc) {
            $idt["conceptos"][$cIdx]["satunidad"]=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD,"codigo",$cnc["claveunidad"],"nombre");
            $idt["conceptos"][$cIdx]["satprodserv"]=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo",$cnc["claveprodserv"],"descripcion");
            $cps=intval($cnc["claveprodserv"]);
            if ($cps%100==0) {
                $idt["conceptos"][$cIdx]["subcveprdsrv"]=[];
                for($i=1;$i<100;$i++) {
                    $ci=$cps+$i;
                    $val=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo",$ci,"descripcion");
                    if (empty($val)) break;
                    $idt["conceptos"][$cIdx]["subcveprdsrv"]["$ci"]=$val;
                }
            }
            /*if ($cd==="1" && !isset($cnc["codigo"][0])) {
                $trimdesc=trim($cnc["descripcion"]);
                $ridx=strrpos($trimdesc," ");
                if ($ridx>=0)
                    $vcd=substr($trimdesc,$ridx+1);
                if (isset($vcd[0])) $idt["conceptos"][$cIdx]["codigo"]=$vcd;
            }*/
        }
        $idt["epsilon"]=Facturas::EPSILON;
        if (isset($idt["statusn"][0])) {
            $statusn=+$idt["statusn"];
            if (($statusn&Facturas::STATUS_RECHAZADO)||($statusn&Facturas::STATUS_CANCELADOSAT)) errNDie("La factura indicada ya está cancelada",$idt+$baseData+["line"=>__LINE__,"query"=>$query]);
            if ($statusn&Facturas::STATUS_PAGADO) errNDie("La factura indicada ya está pagada",$idt+$baseData+["line"=>__LINE__,"query"=>$query]);
            if ($statusn&Facturas::STATUS_CONTRA_RECIBO) errNDie("La factura indicada ya tiene contra recibo",$idt+$baseData+["line"=>__LINE__,"query"=>$query]);
        }
        global $solObj;
        if(!isset($solObj)) {
            require_once "clases/SolicitudPago.php";
            $solObj=new SolicitudPago();
        }
        if ($solObj->exists("idFactura={$idt["invId"]}")) errNDie("La factura indicada ya tiene solicitud",$idt+$baseData+["line"=>__LINE__,"query"=>$query]);
        if (!isset($idt["statusn"][0])) {
            errNDie("No existe una factura con los datos indicados",$baseData+["line"=>__LINE__,"query"=>$query]);
        }
        successNDie("Encontrado!",$idt+$baseData+["line"=>__LINE__,"query"=>$query]);
    }
    $decQry = html_entity_decode($query);
    $encQry = htmlentities($query);
    errNDie("No existe una factura con los datos indicados",$baseData+["line"=>__LINE__,"encquery"=>$encQry,"decquery"=>$decQry,"invData"=>$invData]);
} // FIN (0) CARGA FACTURA ALTA PREVIA
// (1) CARGA DE ARCHIVOS //
// - Guardar archivos de factura y datos iniciales con status Temporal. Aun no se genera la solicitud.
function isSaveFilesService() {
    return "saveFiles"===($_POST["action"]??"");
}
function getSaveFilesService() {
    sessionInit();
    if (!hasUser()) echoJsNDie("refresh","No User");
    $files=getFixedFileArray($_FILES["files"]??null);
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre,"files"=>array_column($files, "name")];
    if (isset($files[2])) errNDie("Demasiados archivos",$baseData+["line"=>__LINE__]);
    if (!isset($files[0])) errNDie("No se recibieron archivos",$baseData+["line"=>__LINE__]);
    for ($i=0; isset($files[$i]); $i++) {
        $file=$files[$i]; $fileType=$file["type"];
        if ($fileType==="application/pdf") {
            if (isset($pdfFileData)) errNDie("Debe indicar tan sólo un archivo PDF",$baseData+["line"=>__LINE__]);
            $pdfFileData=$file;
        } else if ($fileType==="text/xml") {
            if (isset($xmlFileData)) errNDie("Debe indicar tan sólo un archivo XML",$baseData+["line"=>__LINE__]);
            $xmlFileData=$file;
        } else errNDie("Tipo de archivo invalido: ".$fileType,$baseData+["line"=>__LINE__]);
    }
    if (isset($pdfFileData)) {
        global $eDrivePath;
        $tempPath=$eDrivePath.$pdfFileData["name"];
        if (!move_uploaded_file($pdfFileData["tmp_name"], $tempPath)) {
            errNDie("El archivo PDF no se pudo descargar",$baseData+["line"=>__LINE__,"moveError"=>error_get_last()]+$pdfFileData);
        }
        chmod($tempPath, 0777);
        $pdfFileData["tmp_name"]=$tempPath;
        $pdfFileData["fixed"]=true;
        require_once "clases/PDF.php";
        $pdfObj=PDF::getImprovedFile($tempPath);
        if (!isset($pdfObj)) errNDie(isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado",$baseData+["line"=>__LINE__]+PDF::$errdata);
    } else errNDie("Es necesario incluir el archivo PDF de su factura para generar una Solicitud");
    if (isset($xmlFileData)) {
        global $invObj;
        //$invObj->rows_per_page = 0;
        try {
            $result=$invObj->altaTemporal($xmlFileData,$pdfFileData??null,"i");
            if ($result["existe"]??false) {
                $_POST["uuid"]=$result["uuid"];
                $_POST["existe"]=$result["existe"];
                getAppendInvoiceService();
                return;
            }
            $infprv=$result["infprv"]??"";
            if (!isset($infprv[0])) $result["NOINFPRV"]="1";
            if (isset($result["conceptos"]["@claveprodserv"][0]))
                $result["conceptos"]=[$result["conceptos"]];
            if (isset($result["conceptos"][0])) {
                //$result["debug"]="Con ".count($result["conceptos"])." conceptos";
                require_once "clases/catalogoSAT.php";
                foreach ($result["conceptos"] as $idx=>$concepto) {
                    $result["conceptos"][$idx]["@satunidad"]=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD,"codigo",$concepto["@claveunidad"],"nombre");
                    if (!isset($concepto["@unidad"][0])) {
                        $result["conceptos"][$idx]["@unidad"]=$result["conceptos"][$idx]["@satunidad"];
                    }
                    $result["conceptos"][$idx]["@satprodserv"]=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo",$concepto["@claveprodserv"],"descripcion");
                    if (!isset($concepto["@descripcion"][0])) {
                        $result["conceptos"][$idx]["@descripcion"]=$result["conceptos"][$idx]["@satprodserv"];
                    }
                    $cveprdsrv=intval($concepto["@claveprodserv"]);
                    if ($cveprdsrv%100==0) {
                        $result["conceptos"][$idx]["@subcveprdsrv"]=[];
                        for($i=1;$i<100;$i++) {
                            $icps=$cveprdsrv+$i;
                            $val=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo",$icps,"descripcion");
                            if (empty($val)) break;
                            $result["conceptos"][$idx]["@subcveprdsrv"]["$icps"]=$val;
                        }
                    }
                    if (isset($infprv["d"]) && $infprv["d"]==="1") {
                        $trimdesc=trim($concepto["@descripcion"]);
                        $rspIdx=strrpos($trimdesc, " ");
                        if ($rspIdx>=0) {
                            $vcodigo=substr($trimdesc, $rspIdx+1);
                        }
                        if (isset($vcodigo[0])) $result["conceptos"][$idx]["@codigo"]=$vcodigo;
                    }
                }
            }// else $result["debug"]="Sin conceptos";
            if (($result["allow"]??"")==="solpago") {
                $sttn=+($result["statusn"]??"-1");
                if ($sttn>=Facturas::STATUS_RECHAZADO)
                    $result["message"].=" y Cancelada";
                else if ($sttn>=Facturas::STATUS_PAGADO)
                    $result["message"].=" y Pagada";
                else if ($sttn&Facturas::STATUS_CONTRA_RECIBO>0)
                    $result["message"].=" y ya tiene Contra recibo";
                else if ($sttn>=Facturas::STATUS_PENDIENTE && $sttn<Facturas::STATUS_PROGPAGO) {
                    $result["result"]="success"; // ToDo_SOLICITUD: al autorizar buscar la factura cuando exista para asignar el status correctamente.
                }
            }
            echo json_encode($result);
        } catch(Exception $ex) {
            global $query;
            if ($ex->getCode()===0) $baseData["overlayMessage"]=$ex->getMessage();
            errNDie("Error al realizar Alta Temporal",$baseData+["line"=>__LINE__,"query"=>$query,"error"=>getErrorData($ex)]);
        }
    }
} // FIN (1) CARGA DE ARCHIVOS
// (2) GENERA SOLICITUD DE PAGO //
// - Generar Solicitud de Autorización de pago
// - Se genera correo para autorizador(es) correspondiente(s) a la empresa
function isReqPaymAuth() {
    return "requestPaymentAuthorization"===($_POST["action"]??"");
}
function doReqPaymAuth() {
    global $invObj, $ordObj, $solObj, $tokObj, $gpoObj, $prvObj, $ugObj, $usrObj, $perObj, $query;
    //$invObj->rows_per_page = 0;
    sessionInit();
    if (!hasUser()) echoJsNDie("refresh","No User");
    $userId=getUser()->id;
    $userName=getUser()->nombre;
    $esDesarrollo = in_array($userName, ["admin","sistemas","test","test1","test2","test3"]);
    $esPrueba = in_array($userName, ["admin","sistemas"]);
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>$userName,"POST"=>array_intersect_key($_POST, ["inidate"=>1,"paydate"=>1,"gpoId"=>1,"prvId"=>1,"invId"=>1,"ordRef"=>1/*,"conceptos"=>1*/,"subtotal"=>1,"descuento"=>1,"isr"=>1,"iva"=>1,"total"=>1,"amount"=>1,"pedido"=>1])];
    foreach ($_FILES as $key => $file) $baseData+=[$key=>($file["name"]??null)];
    // EXTRAE DATOS
    $inidate=$_POST["inidate"]??"";
    if (!isset($inidate[0]))
        errNDie("Falta la fecha de solicitud",$baseData+["line"=>__LINE__]);
    $dbinidate=reverseDate($inidate,"/","-");
    $paydate=$_POST["paydate"]??"";
    if (!isset($paydate[0]))
        errNDie("Falta la fecha de pago",$baseData+["line"=>__LINE__]);
    $dbpaydate=reverseDate($paydate,"/","-");
    $gpoId=$_POST["gpoId"]??"";
    if (!isset($gpoId[0]))
        errNDie("Falta indicar la empresa",$baseData+["line"=>__LINE__]);
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $gpoData=$gpoObj->getData("id=$gpoId",0,"cut,alias,razonSocial");
    if (isset($gpoData[0])) $gpoData=$gpoData[0];
    $prvId=$_POST["prvId"];
    if (!isset($prvId[0]))
        errNDie("Falta indicar el proveedor",$baseData+["line"=>__LINE__]);
    if (!isset($prvObj)) {
        require_once "clases/Proveedores.php";
        $prvObj=new Proveedores();
    }
    $proveedor=$prvObj->getData("id=$prvId",0,"razonSocial,status,verificado,cumplido");
    $rowStatusProveedor="";
    if (isset($proveedor[0])) $proveedor=$proveedor[0];
    $prvVerif=+$proveedor["verificado"];
    $prvOpina=+$proveedor["cumplido"];
    $prvStatus=$proveedor["status"];
    if ($prvStatus==="actualizar") {
        $rowStatusProveedor="Los datos bancarios requieren ser actualizados";
    } else if ($prvVerif==0) {
        $pl0=["",""]; // 0=S,1=n
        $rowStatusProveedor="El estado de cuenta";
        if ($prvOpina<=0) {
            $rowStatusProveedor.=" y ";
            $pl0=["S","n"];
            $rowStatusProveedor.=" la opini&oacute;n de cumplimiento";
        }
        $rowStatusProveedor.=" no est&aacute;".$pl0[1]." APROBADO".$pl0[0];
    } else if ($prvVerif<0 || $prvOpina<-1) {
        $pl1=["",""];
        $e="e";
        if ($prvVerif<0) {
            $rowStatusProveedor="El estado de cuenta";
            if ($prvOpina<-1) {
                $rowStatusProveedor.=" y ";
                $pl1=["S","n"];
            }
        } else $e="E";
        if ($prvOpina<-1) {
            $rowStatusProveedor.=$e."l documento del SAT de Opini&oacute;n de Cumplimiento";
        }
        $rowStatusProveedor.=" est&aacute;".$pl1[1]." RECHAZADO".$pl1[0];
    } else if ($prvOpina<0) {
        $rowStatusProveedor="El documento del SAT de Opini&oacute;n de Cumplimiento est&aacute; VENCIDO";
    } else if ($prvOpina==0) {
        $rowStatusProveedor="El documento del SAT de Opini&oacute;n de Cumplimiento no est&aacute; APROBADO";
    } else if ($prvStatus!=="activo") {
        $rowStatusProveedor="El proveedor est&aacute; ".strtoupper($prvStatus);
    }
    if (isset($rowStatusProveedor[0])) $rowStatusProveedor="<tr><th style='text-align:left;'>Status:</th><td colspan='3' style='text-align:left;background-color:rgba(255,0,0,0.1);color:darkred;font-weight:bold;'>".$rowStatusProveedor."</td></tr>";
    if(!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj=new SolicitudPago();
    }
    DBi::autocommit(false);
    $solData=["fechaPago"=>$dbpaydate,"idUsuario"=>$userId,"idEmpresa"=>$gpoId];
    $observaciones=$_POST["observaciones"]??"";
    if (isset($observaciones[0])) $solData["observaciones"]=$observaciones;
    $invId=$_POST["invId"]??"";
    $ordRef=$_POST["ordRef"]??"";
    if (isset($invId[0])) {
        // VALIDA SOLICITUD DE PAGO
        if ($solObj->exists("idFactura='$invId'")) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("La factura se ingresó en una solicitud previa",$baseData+["line"=>__LINE__]);
        }
        $invData=$invObj->getData("id='$invId'");
        if (!isset($invData[0])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Alta de factura interrumpida, debe iniciar el proceso nuevamente.",$baseData+["line"=>__LINE__]);
        }
        $invData=$invData[0];
        if (!isset($invData["id"])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Alta de factura corrupta, consulte al administrador.",$baseData+["line"=>__LINE__]);
        }
        if (isset($invData["statusn"])) {
            $statusn=+$invData["statusn"];
            if ($statusn>=Facturas::STATUS_RECHAZADO) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("La factura ya está rechazada.",$baseData+["line"=>__LINE__]);
            }
            if ($statusn>=Facturas::STATUS_PAGADO) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("La factura ya está pagada.",$baseData+["line"=>__LINE__]);
            }
            // ToDo_SOLICITUD: ignora factura con contra recibo
            if (($statusn&Facturas::STATUS_CONTRA_RECIBO)>0) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("La factura ya tiene contra recibo.",$baseData+["line"=>__LINE__]);
            }
        } else $statusn=0;
        $folio=$invData["folio"]??"";
        if (!isset($folio[0])) $folio=substr($invData["uuid"],-10);

        if (isset($_POST["pedido"][0])) $pedido=$_POST["pedido"];
        else $pedido="S/PEDIDO";
        if (isset($_POST["remision"][0])) $remision=$_POST["remision"];
        else $remision="S/REMISION";
        $invFieldArray=["id"=>$invId,"pedido"=>$pedido,"remision"=>$remision];
        $invObj->saveRecord($invFieldArray);

        global $cptObj;
        if(!isset($cptObj)) {
            require_once "clases/Conceptos.php";
            $cptObj = new Conceptos();
        }
        $conceptos=$_POST["conceptos"]??[];
        foreach ($conceptos as $idx=>$concepto) {
            $cantidad=+($concepto["cantidad"]??"0");
            $unidad=$concepto["unidad"]??"";
            if (!isset($unidad[0])) $unidad=$concepto["claveunidad"];
            $codigo=$concepto["codigo"]??"";
            if (strpos($codigo, ' ')!==false) { // ToDo: Validar otros tipos de espacios
                // ToDo: Encontrar primer y ultima palabra sin espacios y poner dos botones con las palabras validas respectivamente, al presionar alguno que cambie el valor del campo codigo correspondiente
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("El código de concepto no debe incluir espacios",$baseData+["line"=>__LINE__,"concepto"=>$concepto]); // Aqui viene en concepto[codigo]
            }
            if (isset($concepto["cptId"])) {
                $cptFieldArray=["id"=>$concepto["cptId"],"codigoArticulo"=>$codigo];
                $cptObj->updateRecord($cptFieldArray);
            } else {
                if (isset($concepto["descripcion"][299]))
                    $concepto["descripcion"]=substr($concepto["descripcion"], 0, 296)."...";
                $cptFieldArray=["idFactura"=>$invId,"codigoArticulo"=>$codigo,"cantidad"=>$cantidad,"unidad"=>$unidad,"claveUnidad"=>$concepto["claveunidad"],"claveProdServ"=>$concepto["claveprodserv"],"descripcion"=>$concepto["descripcion"],"precioUnitario"=>$concepto["valorunitario"],"importe"=>$concepto["importe"]]; // ,"version"=>"3.3","status"=>"activo"
                $cptObj->insertRecord($cptFieldArray);
            }
        }
        $vistaFactura="table-row";
        $ubicacion=$invData["ubicacion"];
        $xml=$invData["nombreInterno"].".xml";
        if (isset($invData["nombreInternoPDF"][0])) $pdf=$invData["nombreInternoPDF"].".pdf";
        $newStatusN=$statusn|Facturas::STATUS_PENDIENTE;
        $newStatus=Facturas::statusnToDetailStatus($newStatusN);
        $invFieldArray=["id"=>$invId,"statusn"=>$newStatusN,"status"=>$newStatus];
        if ($invObj->saveRecord($invFieldArray)) {
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioFactura($invId, $newStatus, $userName, false, "Genera Solicitud");
            $prcId=$prcObj->lastId;
        }
        $solData["idFactura"]=$invId;
        $solData["status"]=SolicitudPago::STATUS_CON_FACTURA|$solObj->getStatus($statusn??0);
    } else if (isset($ordRef[0])) {
        // VALIDA ORDEN DE COMPRA
        if (strpos($ordRef, " ")) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("El número de orden no debe tener espacios.",$baseData+["line"=>__LINE__,"ordRef"=>$ordRef]);
        }
        if (isset($ordRef[45])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("El número de orden es demasiado largo.",$baseData+["line"=>__LINE__,"ordRef"=>$ordRef]);
        }
        $ordFile=$_FILES["ordFile"]??null;
        if (!isset($ordFile["name"])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Falta anexar la orden de compra en PDF.",$baseData+["line"=>__LINE__,"FILES"=>$_FILES]);
        } else {
            require_once "clases/Archivos.php";
            $errMsg=Archivos::getUploadError($ordFile, "application/pdf");
            if (isset($errMsg[0])) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie($errMsg,$baseData+["line"=>__LINE__]);
            }
            if ($esPrueba) doclog("ORDFILE 1 uploadCheck","pruebas",["file"=>$ordFile]);
            global $eDrivePath;
            $tempPath=$eDrivePath.$ordFile["name"];
            if (!move_uploaded_file($ordFile["tmp_name"], $tempPath)) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("El archivo PDF no se pudo descargar",$baseData+["line"=>__LINE__,"moveError"=>error_get_last()]+$ordFile);
            }
            if ($esPrueba) doclog("ORDFILE 2 moved","pruebas",["newPath"=>$tempPath]);
            chmod($tempPath, 0777);
            require_once "clases/PDF.php";
            $pdfObj=PDF::getImprovedFile($tempPath);
            if ($esPrueba) doclog("ORDFILE 3 improved","pruebas",["newPath"=>$tempPath]);
            if (!isset($pdfObj)) {
                $errData=PDF::$errdata;
                $errObj=$errData["error"]??["code"=>-1];
                if ($errObj["code"]===268)
                    errNDie("El archivo PDF está encriptado",$baseData+["line"=>__LINE__]+$errData);
                // 267 = CrossReferenceException: El parser gratuito de FPDI sólo soporta técnicas de compresión básicas, se puede ignorar si no se requiere modificar el archivo.
                doclog("Archivo PDF falló para modificar pero sólo se valida no encripción","pdferr",$baseData+["line"=>__LINE__]+$errData);
                //errNDie(isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado",$baseData+["line"=>__LINE__]+PDF::$errdata);
            }
            try {
                $ubicacion=$invObj->getUbicacion($dbinidate."T".date("h:i:s"),$gpoData["alias"],"i");
            } catch (Exception $ex) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("Error al generar ubicacion de archivo",$baseData+["line"=>__LINE__,"alias"=>$gpoData["alias"],"error"=>getErrorData($ex)]);
            }

            $document_root = $_SERVER["DOCUMENT_ROOT"];
            $http_origin = $_SERVER["HTTP_ORIGIN"];
            $ordName="ord".replaceAccents(str_replace(['!','*',"'","(",")",";",":","@","&","=","+","$",",","?","%","#","[","]"," "], "", str_replace(["/","\\\\"],"-",$ordRef)))."_{$gpoId}_{$prvId}";
            $pdf=$ordName.".pdf";
            $ordAbsName=$document_root.$ubicacion.$pdf;
            if(rename($tempPath, $ordAbsName)===false) {
                $pdf=null;
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("No se pudo cargar el archivo $ordFile[name]",$baseData+["line"=>__LINE__,"absname"=>$ordAbsName,"tmpname"=>$ordFile["tmp_name"],"moveError"=>error_get_last()]);
            }
        }
        $importe=$_POST["amount"]??"";
        if (!isset($importe[0])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Falta indicar el monto de compra",$baseData+["line"=>__LINE__]);
        }
        $importe = preg_replace("/[^0-9.]/", "", $importe);
        $moneda=$_POST["moneda"]??"MXN";
        if (!isset($moneda[0])) $moneda="MXN";
        if (!isset($ordObj)) {
            require_once "clases/OrdenesCompra.php";
            $ordObj=new OrdenesCompra();
        }
        if ($ordObj->exists("folio='$ordRef'")) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Ya existe una orden de compra con ese número de registro",$baseData+["line"=>__LINE__]);
        }
        $ordId=$ordObj->saveRecord(["folio"=>$ordRef,"idEmpresa"=>$gpoId,"idProveedor"=>$prvId,"fecha"=>$dbinidate,"rutaArchivo"=>$ubicacion,"nombreArchivo"=>$ordName,"importe"=>$importe,"moneda"=>$moneda,"status"=>OrdenesCompra::STATUS_INGRESADO]);
        if ($ordId===false) {
            $errData=$baseData;
            if(!empty($ordObj->errors)) {
                $errData["error"]=[];
                foreach($ordObj->errors as $error) $errData["error"][]=$error;
            } else if (!empty(DBi::$errors)) {
                $errData["error"]=DBi::$errors;
            } else {
                $errData["error"]=[DBi::getErrno()=>DBi::getError()];
            }
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Falló el registro de orden de compra",$errData+["line"=>__LINE__,"query"=>$query]);
        }
        if (!is_numeric($ordId)) {
            DBi::rollback();
            DBi::autocommit(true);
            $ordStr="";
            if (is_bool($ordId)) $ordStr="(boolean) ".($ordId?"TRUE":"FALSE");
            else if (is_scalar($ordId)) $ordStr="(".gettype($ordId).") ".$ordId;
            else $ordStr="(".gettype($ordId).")";
            errNDie("Respuesta no esperada en registro de orden de compra '$ordStr'",$baseData+["line"=>__LINE__]);
        }
        $vistaOrden="table-row";
        $solData["idOrden"]=$ordId;
        $solData["status"]=SolicitudPago::STATUS_SIN_FACTURA;
    } else {
        DBi::rollback();
        DBi::autocommit(true);
        errNDie("Falta indicar orden de compra o factura",$baseData+["line"=>__LINE__]);
    }
    // GENERA FOLIO POR EMPRESA
    $fechaInicio=date("Y-m");
    $solFolPfx=$gpoData["cut"].substr($fechaInicio,2,2).substr($fechaInicio,5,2);
    $solFolio=false;
    $solTimes=10;
    while($solFolio===false) {
        $solFolio=$solObj->getFolio($solFolPfx, $descResult);
        $callQuery=$query;
        if (!isset($solFolio)||$solFolio===false) {
            $solFolio=false;
            if ($solTimes<=0) {
                doclog("Solicitud generada sin folio y sin reintentos","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"query"=>$callQuery]);
                break;
            }
            $solTimes--;
            doclog("Solicitud sin folio. Reintento $solTimes","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"query"=>$callQuery]);
            continue;
        }
        usleep(rand(5000, 995000));
        if ($solObj->exists("folio='$solFolio'")) {
            $callQuery=$query;
            $solFolio=false;
            if ($solTimes<=0) {
                doclog("Solicitud generada sin folio y sin reintentos","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"query"=>$callQuery]);
                break;
            }
            $solTimes--;
            doclog("Folio de solicitud ya ocupado. Reintento $solTimes","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"query"=>$callQuery]);
            continue;
        }
    }
    if (isset($solFolio)&&$solFolio!==false)
        $solData["folio"]=$solFolio;
    if (isset($prcObj) && isset($prcId)) {
        $prcObj->saveRecord(["id"=>$prcId, "detalle"=>"Genera Solicitud $solFolio"]);
    }

    // IDENTIFICA USUARIOS AUTORIZADORES
    $authList=$_POST["authList"]??"";
    $test=empty($authList)&&$esPrueba&&isset($ordRef[5])&&strtolower(substr($ordRef, 0, 6))==="prueba";
    if (!$test) {
        if (is_array($authList)) $authList=array_values(array_filter($authList)); // trim, strlen
        if (isset($authList[0])) {
            if (is_string($authList))
                $authList=explode(",", $authList);
        } else {
            if (!isset($perObj)) {
                require_once "clases/Perfiles.php";
                $perObj=new Perfiles();
            }
            $perData=$perObj->getData("nombre='Autoriza Pagos'",0,"id");
            if (!isset($perData[0])||!isset($perData[0]["id"])) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("Falló en encontrar perfil de autorización",$baseData+["line"=>__LINE__]);
            }
            $perfilId=$perData[0]["id"];
            if(!isset($ugObj)) {
                require_once "clases/Usuarios_Grupo.php";
                $ugObj=new Usuarios_Grupo();
            }
            global $query;
            $ugData=$ugObj->getData("idPerfil=$perfilId and idGrupo=$gpoId",0,"idUsuario");
            if(!isset($ugData[0])||!isset($ugData[0]["idUsuario"])) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("Falló en encontrar permiso Autoriza Pagos",$baseData+["line"=>__LINE__,"query"=>$query]);
            }
            $authList=[];
            foreach ($ugData as $ugIdx => $ugRow) $authList[]=$ugRow["idUsuario"];
        }
        if (!isset($authList[0])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Falló en obtener autorizador",$baseData+["line"=>__LINE__]);
        }
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        // Jaime Lobatón podrá autorizar cualquier solicitud 
        $bossData=$usrObj->getData("nombre='jlobaton'",0,"id,persona,email");
        if (isset($bossData[0]["id"])) $bossData=$bossData[0];
        if (isset($authList[1]) && isset($bossData["id"]) && ($bossIdx=array_search($bossData["id"], $authList))!==false) {
            unset($authList[$bossIdx]);
            $authList = array_values($authList);
        }
        // quitar jlobaton si hay al menos otro autorizador, para que no le lleguen los correos a el.
        $solData["authList"]=implode(",", $authList);
        // si no es autorizador unico, agregar a jlobaton para autorizar todas las solicitudes
        if (isset($bossData["id"]) && $authList[0]!==$bossData["id"]) $authList[]=$bossData["id"];
    } // !$test

    // GUARDA SOLICITUD DE PAGO
    $solId=$solObj->saveRecord($solData);
    $solQuery=$query;
    if ($solId===false) {
        $errData=$baseData;
        if(!empty($solObj->errors)) {
            $errData["error"]=[];
            foreach($solObj->errors as $error) $errData["error"][]=$error;
        } else if (!empty(DBi::$errors)) {
            $errData["error"]=DBi::$errors;
        } else {
            $errData["error"]=[DBi::getErrno()=>DBi::getError()];
        }
        DBi::rollback();
        DBi::autocommit(true);
        errNDie("Falló el registro de solicitud de pago",$errData+["line"=>__LINE__,"query"=>$solQuery]);
    }
    if (!isset($solFolio)||$solFolio===false) {
        $solFolio=$solId;
        $solObj->saveRecord(["id"=>$solId,"folio"=>$solId]);
    }
    $solObj->firma($solId,"solicita");
    
    if (!$test) {
        // PREPARA USUARIOS PARA ENVIO DE CORREO Y ASIGNAR TOKENS
        $usrWhere="id";
        if (isset($authList[1])) $usrWhere.=" in ($solData[authList])";
        else $usrWhere.="=".$authList[0];
        $usrMailData=$usrObj->getData($usrWhere,0,"id,persona,email");
        if (isset($usrMailData["id"])) {
            $usrMailData=[$usrMailData];
        }
        if(!isset($usrMailData[0])||!isset($usrMailData[0]["email"][0])) {
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("Falló en encontrar usuario autorizador",$baseData+["line"=>__LINE__]);
        }
        $userIdList=array_column($usrMailData, "id"); // generar tokens antes de quitar jlobaton, para permitir que pueda autorizar o rechazar cualquier solicitud de pago
        $baseKeyMap=["BASEURL"=>getBaseURL(),"SOLID"=>$solId,"SOLFOLIO"=>$solFolio,"FECHA"=>$inidate,"PAGO"=>$paydate,"EMPRESA"=>$gpoData["razonSocial"],"PROVEEDOR"=>$proveedor["razonSocial"],"VISTAFACTURA"=>$vistaFactura??"none","VISTAORDEN"=>$vistaOrden??"none","VISTACORREO"=>"table-row","VISTARESPUESTA"=>"none","STATUSPROVEEDOR"=>$rowStatusProveedor,"HOSTNAME"=>$_SERVER["HTTP_ORIGIN"]];
        if (isset($folio)) $baseKeyMap["FOLIO"]=$folio;
        if (isset($ordRef[0])) $baseKeyMap["NUMORDEN"]=$ordRef;
        if (isset($importe)) {
            $baseKeyMap["IMPORTE"]=formatCurrency(+$importe, $moneda);
            $baseKeyMap["MONEDA"]=$moneda;
        }
        if (isset($xml[0])) {
            $baseKeyMap["XMLDISPLAY"]="inline-block";
            $baseKeyMap["XML"]=$ubicacion.$xml;
        } else $baseKeyMap["XMLDISPLAY"]="none";
        if (isset($pdf[0])) {
            $baseKeyMap["PDFDISPLAY"]="inline-block";
            $baseKeyMap["PDF"]=$ubicacion.$pdf;
        } else $baseKeyMap["PDFDISPLAY"]="none";
        $baseKeyMap["isInteractive"]="0";
        $isInteractive=false;
        ob_start();
        ob_implicit_flush(false);
        include "templates/solforma.php";
        $baseKeyMap["RESPUESTA"]=ob_get_clean();

        $rutaToken="consultas/Facturas.php?action=responsePaymentAuthorization&token=";
        if(!isset($tokObj)) {
            require_once "clases/Tokens.php";
            $tokObj = new Tokens();
        }
        $mensajeBase=file_get_contents(getBasePath()."templates/correoSolPago2.html");

        $moduleList=["autorizaPago","rechazaPago"];
        $usageKey=null; // usos infinitos
        doclog("Nueva version de generacion de tokens","solpago",["solId"=>$solId,"solFolio"=>$solFolio,"usrIds"=>$userIdList,"modules"=>$moduleList]);
        // GUARDA TOKEN
        $tokList=$tokObj->creaAccion($solId,$userIdList,$moduleList,$usageKey);
        $sent=false;
        $mailSettings=["gpoId"=>$gpoId, "domain"=>$gpoObj->getDomainKey($gpoId), "solFolio"=>$solFolio];
        foreach ($usrMailData as $idx=>$usrInfo) {
            if (!isset($tokList[$usrInfo["id"]])) {
                DBi::rollback();
                DBi::autocommit(true);
                errNDie("Generación de token de autorización fallido",$baseData+["line"=>__LINE__,"tokens"=>$tokList,"usrInfo"=>$usrInfo]);
            }
            $tokenAutorizar=$tokList[$usrInfo["id"]]["autorizaPago"];
            $tokenRechazar=$tokList[$usrInfo["id"]]["rechazaPago"];
            // CONSTRUYE MENSAJE DE AUTORIZACION
            $keyMap=array_merge($baseKeyMap,["AUTORIZAR"=>$rutaToken.$tokenAutorizar,"RECHAZAR"=>$rutaToken.$tokenRechazar]);
            $mensaje = preg_replace("/%\w+%/","",str_replace(array_map(function($elem){return "%".$elem."%";},array_keys($keyMap)),array_values($keyMap), $mensajeBase));
            $from=["address"=>getUser()->email,"name"=>replaceAccents(getUser()->persona)];
            $to=["address"=>$usrInfo["email"],"name"=>replaceAccents($usrInfo["persona"])];

            $subject="Solicitud de Autorizacion de Pago $solFolio";
            // Si hay más de un autorizador y alguno es jlobaton, no enviarle correo
            // toDo: Agregar variable estatica a clases/Facturas mailExceptionIds, inicializar clase buscando el id de jlobaton y agregandolo a este arreglo. Y reemplazar todas las menciones de 2639 por el contenido de esta variable.
            if (!isset($usrMailData[1]) || $usrInfo["id"]!="2639") {
                $sent=sendMail($subject,$mensaje,$from,$to,null,null,$mailSettings); // (2) GENERA SOLICITUD DE PAGO. Solicitante a Autorizadores
                if (!$sent) doclog("Falló envío de correo, revisar mail.log","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"solFolio"=>$solFolio,"subject"=>$subject,"to"=>$to]);
            } // else : No pasa nada, significa que hay más de un usuario y el usuario actual es Jaime Lobaton y no se le enviará correo.
        // toDo: Enviar correo a solicitante: "Solicitud de Autorización enviada"
        }
        // ENVIA CORREO CON MENSAJE
            // regresa resultado de éxito
    } // !$test
    DBi::commit();
    DBi::autocommit(true);
    successNDie("<b>Solicitud de Autorización $solFolio enviada</b>"/*,["mail"=>($test?"Solicitud de prueba, no se envió correo":($sent?"Correo enviado satisfactoriamente":"Falló el envío de correo"))]*/);
} // FIN (2) GENERA SOLICITUD DE PAGO
//  (3) RESPUESTA DE AUTORIZACION //
// - Marcar solicitud como autorizada o rechazada
// - Si se autoriza ajustar status de solicitud al correspondiente status de factura
// - Si se autoriza y la factura tiene status temporal, agregar conceptos en base de datos y cambiar a status pendiente X La factura ya tiene que tener conceptos
// - Si hay mas de un autorizador, mandar correo de respuesta a los otros autorizadores
// - Si se autoriza y no tiene factura mandar correo de respuesta a Pagos
// - Si se autoriza y si tiene factura mandar correo de respuesta al solicitante y a Gestion
// - Se puede autorizar/rechazar por token via correo o ingresando usuario autorizador en el portal
function isRespPaymAuth() {
    return "responsePaymentAuthorization"===($_REQUEST["action"]??"");
}
function doRespPaymAuth() {
    global $tokObj,$solObj,$invObj,$ordObj,$prcObj,$query;
    //$invObj->rows_per_page = 0;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    if(!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    if (isset($_GET["token"])) {
        $token=$_GET["token"];
        $beInteractive=true;
    } else {
        sessionInit();
        if (!hasUser()) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
            doclog("Autorización de Solicitud de Pago sin sesión","solpago",$baseData+["line"=>__LINE__]);
            die();
        }
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esAuthPago=validaPerfil("Autoriza Pagos")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        $esDesarrollo = in_array($username, ["admin","sistemas","test","test1","test2","test3"]);
        if (!$esAuthPago) {
            echo json_encode(["action"=>"redirect","mensaje"=>"No tiene permiso para autorizar la solicitud de pago","errorMessage"=>"No tiene permiso para autorizar la solicitud de pago"]);
            doclog("Intento de autorización de Solicitud de Pago fallida por $username ($usrId)","solpago",$baseData+["line"=>__LINE__]);
            die();
        }
        $solId=$_POST["solId"]??null;
        $modulo=$_POST["module"]??null;
        if (!isset($solId[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Solicitud no identificada","errorMessage"=>"Solicitud no identificada"]);
            doclog("Respuesta de autorización en portal incompleta. No hay solicitud","error",$baseData+["line"=>__LINE__]);
            die();
        }
        if (!isset($modulo[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo no identificado","errorMessage"=>"Módulo no identificado"]);
            doclog("Respuesta de autorización en portal incompleta. No hay módulo","error",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            die();
        }
        $tokSrch=$tokObj->getData("refId=$solId and usrId=$usrId and modulo='$modulo'",0,"token");
        $tokQry=$query;
        if (isset($tokSrch[0]["token"][0])) $token=$tokSrch[0]["token"];
        else if (isset($tokSrch["token"][0])) $token=$tokSrch["token"];
        /*else if ($esSistemas) {
            //$tokData=$tokObj->getData("refId=$solId and modulo='$modulo'");
            $token=$tokObj->creaAccionSistemas($solId,$usrId,$modulo);
        }*/
        if (!isset($token[0])) {
            $accion=substr($modulo, 0, -4)."r";
            echo getPaymNoteView("El usuario $username no puede $accion la solicitud","","",true,true,false);
            doclog("Respuesta de autorización en portal incompleta: Usuario no valido","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"query"=>$tokQry]);
            die();
        }
        $beInteractive=false;
    }
    doclog("Antes de elegir Token","token",["token"=>$token]);
    $prevTokenData=$tokObj->obtenStatusData($token);
    DBi::autocommit(false);
    if ($tokObj->eligeToken($token)) {
        doclog("Token Elegido","token");
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!validaPerfil("Administrador") && !validaPerfil("Sistemas") && !validaPerfil("Autoriza Pagos")) {
            echo getPaymNoteView("No tiene permiso para autorizar solicitudes de pago","","",true,true,false);
            doclog("Intento de autorización de Solicitud de Pago fallida","error",$baseData+["line"=>__LINE__,"usr"=>["id"=>$usrId,"name"=>$username],"token"=>$token]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $solId=$tokObj->data["refId"];
        $status=$tokObj->data["status"];
        $modulo=$tokObj->data["modulo"];
        $usos=$tokObj->data["usos"];
    } else {
        doclog("Token NO Elegido","token",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrno"=>DBi::$errno,"dberror"=>DBi::$error,"query"=>$query]);
        if (isset($tokObj->data["refId"])) {
            $solId=$tokObj->data["refId"];
            if (isset($tokObj->errorMessage)) {
                // toDo: $tokObj->data["modifiedTime"] si es menor a 2 segundos ignorar envío de mensaje de error
                echo getPaymNoteView($tokObj->errorMessage,$solId,"",true,true,false);
                doclog("Envío de autorización de pago fallida","error",$baseData+["line"=>__LINE__,"errorMessage"=>$tokObj->errorMessage]+$tokObj->data);
            } else {
                $tokErrMsg0=$tokObj->errors[0]??"";
                if(isset($tokErrMsg0[0]) && !isset($tokObj->errors[1]) && substr($tokErrMsg0,-11)==="previamente") {
                    echo getPaymNoteView("Solicitud $tokErrMsg0",$solId,"",true,true,false);
                    doclog("Se ignora solicitud de autorización","error",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                } else if (isset($tokObj->data["usos"]) && (+$tokObj->data["usos"])<=0) {
                    if (isset($tokObj->data["ocupado"])) {
                        $msg=substr($tokObj->data["ocupado"]["modulo"],0,-4)."da"; // autoriza-Pago, rechaza-Pago
                    } else $msg="cancelada";
                    echo getPaymNoteView("La solicitud ya fue $msg",$solId,"",true,true,false);
                    doclog("Se ignora solicitud de autorización","solpago",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                } else {
                    echo getPaymNoteView("Respuesta de autorización fallida",$solId,"",true,true,false);
                    doclog("Envío de autorización de pago fallida","error",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                }
            }
        } else if (isset($tokObj->errorMessage)) {
            echo getPaymNoteView($tokObj->errorMessage,"","",true,true,false);
            doclog("Envío de autorización de pago fallida","error",$baseData+["line"=>__LINE__,"token"=>$token,"errorMessage"=>$tokObj->errorMessage]);
        } else {
            echo getPaymNoteView("Respuesta de autorización fallida","","",true,true,false);
            doclog("Envío de autorización de pago fallida","error",$baseData+["line"=>__LINE__,"token"=>$token,"errors"=>$tokObj->errors]);
        }
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        echo getPaymNoteView("La solicitud ya no existe",$solId,"",true,true,false);
        doclog("Autorizacion de pago cancelada, la solicitud ya no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solStatus=+$solData["status"];
    $solIdUsuario=$solData["idUsuario"];
    // ToDo_SOLICITUD: Validar si la accion es cancelar y ya está cancelada, salir indicando que ya se realizó con anterioridad
    // ToDo_SOLICITUD: Validar si la accion es autorizar y ya está autorizada, salir indicando que ya se realizó con anterioridad
    /* Remover este codigo cuando las validaciones indicadas se hayan realizado 
    if ($solStatus>=SolicitudPago::STATUS_CANCELADA) {
        echo getPaymNoteView("La solicitud ya fue cancelada",$solId,"",true,true,false);
        doclog("Solicitud de pago $solId cancelada previamente sin actualizar tokens","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    // ToDo_SOLICITUD: Ver si aqui se valida por STATUS_CANCELADA_SAT
    */
    if ($solStatus&SolicitudPago::STATUS_PAGADA) {
        echo getPaymNoteView("La solicitud ya fue pagada",$solId,$solFolio,true,true,$beInteractive);
        doclog("Solicitud de pago $solId pagada previamente sin actualizar tokens","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $conFactura=(isset($solData["idFactura"]) && !empty($solData["idFactura"]));
    $conOrden=(isset($solData["idOrden"]) && !empty($solData["idOrden"]));
    if ($conFactura) {
        $idFactura=$solData["idFactura"];
        $invData=$invObj->getData("id=$idFactura");// and statusn is not null");
        if (!isset($invData[0])) {
            echo getPaymNoteView("La factura en la solicitud se ha eliminado",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Autorizacion de pago interrumpida. No se encontró la factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $invData=$invData[0];
        if (!isset($invData["status"][0])) {
            echo getPaymNoteView("La factura en la solicitud ha sido alterada",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Autorizacion de pago interrumpida. La factura no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!isset($invData["version"])||!in_array($invData["version"], ["4.0"])) {
            echo getPaymNoteView("La versión de factura $invData[version] no es válida",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Autorizacion de pago interrumpida. Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]??"N/A"]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!isset($invData["tipoComprobante"])||$invData["tipoComprobante"]!=="i") {
            echo getPaymNoteView("Se ingresó un comprobante que no es factura",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Autorizacion de pago interrumpida. El tipo comprobante no es ingreso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]??"N/A"]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if ($invData["status"]==="Temporal")
            $invStatusN=0;
        else {
            $invStatusN=+$invData["statusn"];
            if ($invStatusN>=Facturas::STATUS_CANCELADOSAT) {
                echo getPaymNoteView("La factura esta cancelada ante el SAT",$solId,$solFolio,true,true,$beInteractive);
                doclog("Solicitud de pago $solId invalida, la factura ya está cancelada ante el SAT","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
            if ($invStatusN>=Facturas::STATUS_RECHAZADO) {
                echo getPaymNoteView("La factura ya esta cancelada",$solId,$solFolio,true,true,$beInteractive);
                doclog("Solicitud de pago $solId invalida, la factura ya está cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
            if ($invStatusN>=Facturas::STATUS_PAGADO) {
                // ToDo: Cambiar solicitud a status pagada
                echo getPaymNoteView("La factura ya esta pagada",$solId,$solFolio,true,true,$beInteractive);
                doclog("Solicitud de pago $solId invalida, la factura ya está pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
        }
        if (!isset($invData["estadoCFDI"])||$invData["estadoCFDI"]!=="Vigente") {
            echo getPaymNoteView("La factura ya no es vigente ante el SAT",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Autorizacion de pago interrumpida. La factura ya no es vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invData["estadoCFDI"]??"N/A"]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
    }
    global $usrObj;
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj=new Proceso();
    }
    $autorizada=($modulo==="autorizaPago");
    if (!$autorizada) {
        $solProceso=+$solData["proceso"];
        if ($solProceso>0) {
            if ($solProceso>=4) $procMsg="La factura ya fue pagada";
            else if ($solProceso>=2) $procMsg="La solicitud está en finanzas para pago";
            else $procMsg="La solicitud está en proceso contable";
            echo getPaymNoteView($procMsg,$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Cancelación de solicitud interrumpida. $procMsg","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId, "invId"=>$idFactura??null,"modulo"=>$modulo,"proceso"=>$solProceso]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!$solObj->saveRecord(["id"=>$solId,"idAutoriza"=>$usrId,"status"=>new DBExpression("status|".SolicitudPago::STATUS_CANCELADA)])) {
            echo getPaymNoteView("No fue posible cancelar la solicitud",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Cancelación de solicitud fallida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId, "invId"=>$idFactura??null,"modulo"=>$modulo,"proceso"=>$solProceso]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        // ToDo: Validar si es falso para mandar error;
        if ($conFactura) {
            if ($invObj->saveRecord(["id"=>$idFactura,"status"=>"Rechazado","statusn"=>($invStatusN|Facturas::STATUS_RECHAZADO)])) {
                $prcObj->cambioFactura($idFactura, "Rechazado", $username, false, "Solicitud no autorizada");
            }
            doclog("Solicitud con Factura Rechazada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            global $ctrObj,$ctfObj;
            if (!isset($ctfObj)) {
                require_once "clases/Contrafacturas.php";
                $ctfObj = new Contrafacturas();
            }
            $ctfData=$ctfObj->getData("idFactura=$idFactura",0,"idContrarrecibo");
            if (isset($ctfData[0]["idContrarrecibo"])) {
                $ctrId=$ctfData[0]["idContrarrecibo"];
                $numCF=+array_column($ctfObj->getData("idContrarrecibo=$ctrId",0,"count(1) n"), "n")[0];
                $ctfObj->deleteRecord(["idContrarrecibo"=>$ctrId]);
                doclog("Contrafacturas eliminadas","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
                if (!isset($ctrObj)) {
                    require_once "clases/Contrarrecibos.php";
                    $ctrObj = new Contrarrecibos();
                }
                $ctrObj->deleteRecord(["id"=>$ctrId]);
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                if (!$firObj->saveRecord(["modulo"=>"contrarrecibo","idReferencia"=>$ctrId,"idUsuario"=>$usrId,"accion"=>"elimina","motivo"=>"Completo: $numCF facturas"])) {
                    
                    doclog("No fue posible agregar firma de eliminacion de contra recibo",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>$firObj->errors,"dberrors"=>DBi::$errors,"log"=>$firObj->log]);
                }
                doclog("Contra recibo eliminado","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            }
            // ToDo_SOLICITUD: En lugar de borrar cambiar status a 'obsoleto'. Validar cuando status de tokens sea 'obsoleto'
            $tokObj->deleteRecord(["refId"=>$solId,"modulo"=>["transfiereArchivos","procesaCompras"]]);
            $isErrResp=false;
        } else if ($conOrden) {
            if (!isset($ordObj)) {
                require_once "clases/OrdenesCompra.php";
                $ordObj=new OrdenesCompra();
            }
            $ordObj->saveRecord(["id"=>$solData["idOrden"],"status"=>-1]);
            doclog("Solicitud con Orden Rechazada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$solData["idOrden"]]);
            // ToDo: Validar si es falso para mandar error;
            $isErrResp=false;
        } else {
            doclog("Solicitud de pago rechazada sin factura ni orden de compra","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            $isErrResp=true;
        }
        $solObj->firma($solId,"rechaza");
        $prcObj->cambiaSolicitud($solId,"RECHAZADA",isset($_GET["token"])?"Via Correo":(isset($_POST["solId"])?"Via Portal":"Desconocido").($conOrden?". Con orden":($conFactura?". Con Factura":". Sin orden ni factura")));
        DBi::commit();
        DBi::autocommit(true);

        echo getPaymNoteView("Se ha rechazado la solicitud de pago",$solId,$solFolio,true,$isErrResp,$beInteractive);
        sendRejectPaymMail($solId, $solFolio, $solIdUsuario, $usrId, $solData["idEmpresa"]);
        die();
    }

    $newSolStatus=SolicitudPago::STATUS_AUTORIZADA;
    $authData=$usrObj->getData("id=$usrId",0,"persona,email");
    if (!isset($authData[0]["persona"][0])) {
        echo getPaymNoteView("Los datos del autorizador no están completos",$solId,$solFolio,true,true,$beInteractive);
        doclog("ERROR: Autorizacion de pago interrumpida. Los datos del usuario autorizador no están completos","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"idAutoriza"=>$usrId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $authData=$authData[0];
    $fromObj=["address"=>$authData["email"], "name"=>replaceAccents($authData["persona"])];
    $respuesta="<h2>La solicitud de pago $solFolio ha sido autorizada por ".$fromObj["name"]."</h2>";
    if ($conFactura) {
        if (!$invObj->exists("id=$idFactura")) {
            echo getPaymNoteView("La factura indicada en la solicitud ha sido eliminada",$solId,$solFolio,true,true,$beInteractive);
            doclog("ERROR: Autorizacion de pago interrumpida. La factura no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj = new Grupo();
        }
        $gpoData=$gpoObj->getData("rfc='$invData[rfcGrupo]'");
        if (!isset($gpoData[0]["rfc"])) {
            echo getPaymNoteView("No se encuentra el registro de la empresa cliente",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, la empresa cliente no está registrada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"rfcGrupo"=>$invData["rfcGrupo"]]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        //$invStatusN=$invData["statusn"]|Facturas::STATUS_ACEPTADO;
        //$invStatus=Facturas::statusnToDetailStatus($invStatusN);
        $gpoData=$gpoData[0];
        $currTokenData=$tokObj->obtenStatusData($token);
        $tokObj->restauraStatusData($prevTokenData);
        global $ctrObj;
        if (!isset($ctrObj)) {
            require_once "clases/Contrarrecibos.php";
            $ctrObj = new Contrarrecibos();
        }
        $idCr=false;
        $times=10;
        $alias=$gpoData["alias"];
        while($idCr===false) {
            $idCr=$ctrObj->getNextFolio($alias);
            if ($idCr===false) {
                if ($times>0) {
                    $times--;
                    doclog("Error en Solicitud Autorizada. Generación de folio de contra recibo fallido","contrarrecibo",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"try"=>$times,"time"=>date("H:i:s",microtime(true)),"query"=>$query]);
                } else {
                    echo getPaymNoteView("Falló la generación de contra recibo",$solId,$solFolio,true,true,$beInteractive);
                    doclog("Solicitud de pago interrumpida, la generación de contra recibo falló","contrarrecibo",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"try"=>$times,"time"=>date("H:i:s",microtime(true)),"query"=>$query]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
                }
            }
            usleep(rand(5000, 995000));
            if ($ctrObj->exists("aliasGrupo='$alias' and folio='$idCr'")) {
                if ($times>0) {
                    $times--;
                    doclog("Error en Solicitud Autorizada. Generación de folio de contra recibo repetido", "contrarrecibo", $baseData+["line"=>__LINE__, "solId"=>$solId, "invId"=>$idFactura, "aliasGpo"=>$alias, "folioCR"=>$idCr, "try"=>$times, "time"=>date("H:i:s",microtime(true)), "query"=>$query]);
                    $idCr=false;
                } else {
                    echo getPaymNoteView("Falló la generación de contra recibo",$solId,$solFolio,true,true,$beInteractive);
                    doclog("Solicitud de pago interrumpida, la generación de contra recibo falló", "contrarrecibo", $baseData+["line"=>__LINE__, "solId"=>$solId, "invId"=>$idFactura, "aliasGpo"=>$alias, "folioCR"=>$idCr, "try"=>$times, "time"=>date("H:i:s",microtime(true)), "query"=>$query]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
                }
            }
        }
        doclog("Generación de folio de contra recibo aceptado","solpago",["solId"=>$solId,"invId"=>$idFactura, "aliasGpo"=>$alias, "folioCR"=>$idCr]);
        DBi::commit(); // SE GUARDA CR ID
        //DBi::autocommit(false);
        $tokObj->restauraStatusData($currTokenData);
        if ($invStatusN>=Facturas::STATUS_RECHAZADO) {
            global $firObj;
            if (!isset($firObj)) {
                require_once "clases/Firmas.php";
                $firObj=new Firmas();
            }
            if ($firObj->exists("modulo='solpago' and accion='cancela' and idReferencia=$solId")) {
                echo getPaymNoteView("La solicitud ha sido cancelada por solicitante",$solId,$solFolio,true,true,$beInteractive);
                doclog("Solicitud de pago invalida, la factura ha sido cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
            $invStatusN=$invStatusN&~Facturas::STATUS_RECHAZADO;
            $invNewStatus=Facturas::statusnToDetailStatus($invStatusN);
            if ($invObj->saveRecord(["id"=>$idFactura,"status"=>$invNewStatus,"statusn"=>$invStatusN])) {
                $prcObj->cambioFactura($idFactura, $invNewStatus, $username, false, "Autoriza solicitud cancelando Rechazo");
            }
        }

        $fechaRevision=date("Y-m-d H:i:s");
        $prvCode = $invData["codigoProveedor"];
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj = new Proveedores();
        }
        $prvData=$prvObj->getData("codigo='$prvCode'");
        if (!isset($prvData[0]["codigo"])) {
            echo getPaymNoteView("No se encuentra el registro del proveedor",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, el proveedor no está registrado","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"codigoProveedor"=>$prvCode]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $prvData=$prvData[0];
        $credito=+$prvData["credito"]??0;
        //$fechaVencimiento = Contrarrecibos::getFechaVencimiento($fechaRevision, $credito);
        $fechaPago=substr($solData["fechaPago"],0,10);
        $fieldArray = ["folio"=>$idCr,
                       "codigoProveedor"=>$prvCode,
                       "razonProveedor"=>$prvData["razonSocial"],
                       "rfcGrupo"=>$gpoData["rfc"],
                       "razonGrupo"=>$gpoData["razonSocial"],
                       "aliasGrupo"=>$alias,
                       "fechaRevision"=>$fechaRevision,
                       "credito"=>$credito,
                       "fechaPago"=>$fechaPago, //$fechaVencimiento,
                       "total"=>$invData["total"],
                       "numAutorizadas"=>1,
                       "numContraRegs"=>1];
        if (!$ctrObj->saveRecord($fieldArray)) {
            echo getPaymNoteView("Creación de contra recibo incompleta", $solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, falló creación de contra recibo","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"folioCR"=>$idCr,"query"=>$query,"dberrors"=>DBi::$errors]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $idCtr=$ctrObj->lastId;
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj = new Contrafacturas();
        }
        $fieldArray = [ "idContrarrecibo"=>$idCtr,
                        "idFactura"=>$idFactura,
                        "pedido"=>$invData["pedido"],
                        "folioFactura"=>isset($invData["folio"][0])?$invData["folio"]:substr($invData["uuid"],-10),
                        "serieFactura"=>$invData["serie"],
                        "fechaFactura"=>$invData["fechaFactura"],
                        "fechaCaptura"=>$invData["fechaCaptura"],
                        "metodoDePago"=>$invData["metodoDePago"],
                        "tipoComprobante"=>$invData["tipoComprobante"],
                        "nombreInterno"=>$invData["nombreInterno"],
                        "ubicacion"=>$invData["ubicacion"],
                        "subtotal"=>$invData["subtotal"],
                        "total"=>$invData["total"],
                        "retencion"=>$invData["impuestoRetenido"],
                        "moneda"=>$invData["moneda"],
                        "autorizadaPor"=>$usrId];
        if (!$ctfObj->saveRecord($fieldArray)) {
            echo getPaymNoteView("Creación de contra recibo incompleta", $solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, falló creación de contra factura","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"folioCR"=>$idCr,"query"=>$query,"dberrors"=>DBi::$errors]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $invStatusN=$invStatusN|Facturas::STATUS_ACEPTADO|Facturas::STATUS_CONTRA_RECIBO;
        $invNewStatus=Facturas::statusnToDetailStatus($invStatusN);
        if ($invObj->saveRecord(["id"=>$idFactura,"fechaVencimiento"=>$fechaPago,"status"=>$invNewStatus,"statusn"=>$invStatusN])) {
            $prcObj->cambioFactura($idFactura, $invNewStatus, $username, false, "Aceptado y Genera contra recibo automático al autorizar");
        }
        // Actualizar status de Solicitud
        $newSolStatus|=$solObj->getStatus($invStatusN);
        $usrData=$usrObj->getData("id=$solIdUsuario",0,"id,nombre,persona,email");
        if (isset($usrData["id"])) $usrData=[$usrData];
        if (!isset($usrData[0]["nombre"])) {
            echo getPaymNoteView("No se encuentra el registro del solicitante",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, ya no está el registro del solicitante","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"usrId"=>$solIdUsuario]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $template="correoSolPago3.html";
        $tokenExportar=$tokObj->creaAccion($solId,$solIdUsuario,"transfiereArchivos",null);
        $tokenProcesar=$tokObj->creaAccion($solId,$solIdUsuario,"procesaCompras",1);
        if ($tokenExportar===false || $tokenProcesar===false) {
            echo getPaymNoteView("La solicitud ha sido procesada previamente",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago fuera de tiempo","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"usrId"=>$solIdUsuario,"errors"=>$tokObj->errors]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $usrData[0]["keyMap"]=["%EXPORTAR%"=>$tokenExportar[$solIdUsuario]["transfiereArchivos"], "%PROCESAR%"=>$tokenProcesar[$solIdUsuario]["procesaCompras"]];

        //$baseKeyMap=["%EXPORTAR%"=>$tokenExportar[$solIdUsuario]["transfiereArchivos"], "%PROCESAR%"=>$tokenProcesar[$solIdUsuario]["procesaCompras"], "%RESPUESTA%"=>$respuesta];
        //$toObj=[["address"=>$usrData["email"], "name"=>replaceAccents($usrData["persona"])]];
        $usrObj->saveRecord(["id"=>$solIdUsuario,"banderas"=>new DBExpression("banderas|2")]);
        doclog("Solicitud de Pago AUTORIZADA con Factura en Proceso de Compras","solpago",["solId"=>$solId, "invId"=>$idFactura, "usrId"=>$solIdUsuario]);
    } else if ($conOrden) {
        $idOrden=$solData["idOrden"];
        if (!isset($ordObj)) {
            require_once "clases/OrdenesCompra.php";
            $ordObj=new OrdenesCompra();
        }
        if ($solStatus&SolicitudPago::STATUS_CANCELADA) $newOrdStatus="2";
        else $newOrdStatus=new DBExpression("status|2");
        $ordObj->saveRecord(["id"=>$idOrden,"status"=>$newOrdStatus]);
        $ordData=$ordObj->getData("id='$idOrden'");
        if (!isset($ordData[0]["folio"])) {
            echo getPaymNoteView("No se encuentra el registro de la orden de compra",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, la orden de compra no está registrada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $ordData=$ordData[0];
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj = new Grupo();
        }
        $gpoData=$gpoObj->getData("id='$ordData[idEmpresa]'");
        if (!isset($gpoData[0]["rfc"])) {
            echo getPaymNoteView("No se encuentra el registro de la empresa cliente",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, la empresa cliente no está registrada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden,"idEmpresa"=>$ordData["idEmpresa"]]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $gpoData=$gpoData[0];
        $alias=$gpoData["alias"];
        //ToDo_SOLICITUD: Obtener prvData por idProveedor
        global $prvObj;
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj = new Proveedores();
        }
        $prvData=$prvObj->getData("id='$ordData[idProveedor]'");
        if (!isset($prvData[0]["rfc"])) {
            echo getPaymNoteView("No se encuentra el registro del proveedor",$solId,$solFolio,true,true,$beInteractive);
            doclog("Solicitud de pago interrumpida, el proveedor no está registrado","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden,"idProveedor"=>$ordData["idProveedor"]]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $prvData=$prvData[0];

        if (isset($ordData["nombreArchivo"][0])) {
            $usrData=$usrObj->getData("id=$solIdUsuario",0,"persona");
            require_once "clases/PDF.php";
            $invoicePath=$_SERVER['DOCUMENT_ROOT'];
            $pdfpath=$ordData["rutaArchivo"];
            $pdfname=$ordData["nombreArchivo"].".pdf";
            setlocale(LC_TIME,"es_MX.UTF-8","es_MX","esl");
            //$formattedDate=Facturas::$stmpFmt->format(time());
            $formattedDate=strftime("%e %b, %Y");
            try {
                $fullname=$invoicePath.$pdfpath.$pdfname;
                $stampname=$invoicePath.$pdfpath."ST_".$pdfname;
                $pdfObj=PDF::getImprovedFile($fullname);
                if (!isset($pdfObj)) {
                    $errmsg=isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado";
                    doclog($errmsg,"error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]+PDF::$errdata);
                    throw new Exception($errmsg);
                }
                $stampMsg=$pdfObj->setStampFile($invoicePath."imagenes/icons/sello1.png");
                if (isset($stampMsg[0])) {
                    echo getPaymNoteView("Sello no estampado, intente nuevamente",$solId,$solFolio,true,true,$beInteractive);
                    doclog("Autorización de Pago: Error al marcar orden sellada 1","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden,"msg"=>$stampMsg]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
                }
                $basePageCount=$pdfObj->pageCount;
                $pdfObj->addStamp($formattedDate, $usrData[0]["persona"]);
                for($pageNo=1; $pageNo<=$basePageCount; $pageNo++) {
                    $tmplIdx=$pdfObj->importPage($pageNo);
                    $pdfObj->AddPage();
                    $pdfObj->useTemplate($tmplIdx);
                    $pdfObj->SetFont("Helvetica","B",30);
                    $pdfObj->SetTextColor(180,180,180);
                    $pdfObj->SetXY(8,0);
                    $pdfObj->Write(30,"X");
                }
                $pdfObj->pageCount=2*$basePageCount;
                $pdfObj->saveFile($stampname);
                if (!$ordObj->saveRecord(["id"=>$idOrden,"tieneSello"=>1])&&!empty(DBi::$errno)) {
                    $lastQuery=$query;
                    echo getPaymNoteView("Sello no estampado, intente nuevamente",$solId,$solFolio,true,true,$beInteractive);
                    doclog("Autorización de Pago: Error al marcar orden sellada 2","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden,"query"=>$lastQuery,"dberrors"=>DBi::$errors]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
                }
            } catch (Exception $ex) {
                    echo getPaymNoteView("Sello no estampado, intente nuevamente",$solId,$solFolio,true,true,$beInteractive);
                    doclog("Autorización de Pago: Error al marcar factura sellada 3","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden,"error"=>getErrorData($ex)]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
            }
        }

        $template="correoSolPago5.html";
        // ToDo_SOLICITUD: Reemplazar en query usuarios_perfiles por usuarios_grupo y agregar condicion ug.idGrupo=$ordData['idEmpresa'];
        $idGrupo=$ordData['idEmpresa'];
        $usrData=$usrObj->getData("ug.idGrupo=$idGrupo AND ug.idPerfil=".SolicitudPago::PERFIL_PAGA,0,"u.id,u.nombre,u.persona,u.email","u inner join usuarios_grupo ug on u.id=ug.idUsuario");
        if (isset($usrData["id"])) $usrData=[$usrData];
        if (!isset($usrData[0])) {
            $usrData=[["id"=>"1","nombre"=>"admin","persona"=>"Administrador","email"=>"desarrollo@glama.com.mx"]];
        }
        $usrIdList=array_column($usrData, "id");
        $tokenAnexar=$tokObj->creaAccion($solId,$usrIdList,"anexaComprobante",null);
        $tokenPagar=$tokObj->creaAccion($solId,$usrIdList,"procesaPago",1);
        foreach ($usrData as $idx => $usrItem) {
            if (!isset($usrItem["nombre"][0])) {
                echo getPaymNoteView("No se encuentra usuario de Finanzas",$solId,$solFolio,true,true,$beInteractive);
                doclog("Error en Proceso Contable: Proceso Pago sin usuario","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"query"=>$query]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
            $usrData[$idx]["keyMap"]=["%ANEXAR%"=>$tokenAnexar[$usrItem["id"]]["anexaComprobante"],"%PAGAR%"=>$tokenPagar[$usrItem["id"]]["procesaPago"]];
        }
        //$baseKeyMap=["%ANEXAR%"=>$tokenAnexar[$solIdUsuario]["anexaComprobante"],"%PAGAR%"=>$tokenPagar[$solIdUsuario]["procesaPago"],"%RESPUESTA%"=>$respuesta];
        
        // ToDo: Preparada para pago, generar alerta para Julieta, crear listado para pago y mostrar ordenes autorizadas
        doclog("Solicitud de Pago AUTORIZADA sin Factura","solpago",["solId"=>$solId, "ordId"=>$idOrden,"usrIds"=>$usrIdList]);
    } else {
        echo getPaymNoteView("Autorizacion de pago interrumpida sin factura ni orden de compra",$solId,$solFolio,true,true,$beInteractive);
        errlog("Autorizacion de pago interrumpida, no se encuentra factura ni orden de compra relacionada");
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solObj->saveRecord(["id"=>$solId,"idAutoriza"=>$usrId,"status"=>new DBExpression("(status|".$newSolStatus.")&~".SolicitudPago::STATUS_CANCELADA)]);
    $solObj->firma($solId,"autoriza");
    $prcObj->cambiaSolicitud($solId,"AUTORIZADA",isset($_GET["token"])?"Via Correo":(isset($_POST["solId"])?"Via Portal":"Desconocido").($conOrden?". Con orden":($conFactura?". Con Factura":". Sin orden ni factura")));
    DBi::commit();
    DBi::autocommit(true);
    $asunto="Respuesta de Autorizacion de Pago $solFolio";
    $mailSettings=["domain"=>$gpoObj->getDomainKey($gpoData["id"]??"")];
    foreach ($usrData as $idx => $usrItem) {
        $baseKeyMap=["%RESPUESTA%"=>$respuesta,"isInteractive"=>"0"]+$usrItem["keyMap"];
        $toObj=["address"=>$usrItem["email"],"name"=>replaceAccents($usrItem["persona"])];
        $mensaje = getSolFormaView($template,$solId,$solFolio,$baseKeyMap);
        sendMail($asunto,$mensaje,$fromObj,$toObj,null,null,$mailSettings); // (3) RESPUESTA DE AUTORIZACION. Autorizador a F(Solicitante)/O(Finanzas)
    }

    echo getPaymNoteView("La solicitud ha sido autorizada",$solId,$solFolio,true,false,$beInteractive);
    // Otros autorizadores
    $tokData = $tokObj->getData("refId=$solId and usrId!=$usrId and modulo in ('autorizaPago','rechazaPago')",0,"distinct usrId");
    $toObj=[];
    if (isset($tokData)) foreach($tokData as $idx=>$otherAuth) {
        if (isset($otherAuth["usrId"][0]) && $otherAuth["usrId"]!="2639") { // no mandar a jlobaton
            $othData=$usrObj->getData("id=$otherAuth[usrId]",0,"persona,email");
            if (isset($othData[0]["email"][0]))
                $toObj[]=["address"=>$othData[0]["email"],"name"=>replaceAccents($othData[0]["persona"])];
        }
    }
    if (isset($toObj[0])) {
        $template="respGralSolPago.html";
        $baseKeyMap=["%ENCABEZADO%"=>"SOLICITUD DE AUTORIZACI&Oacute;N DE PAGO $solFolio","%RESPUESTA%"=>$respuesta,"%BUTTONS%"=>"<!-- 1 -->","isInteractive"=>"0"];
        $mensaje = getSolFormaView($template,$solId,$solFolio,$baseKeyMap);
        sendMail($asunto,$mensaje,$fromObj,$toObj,null,null,$mailSettings); // (3) Copia. Autorizador a otros autorizadores
    }
} // FIN (3) RESPUESTA DE AUTORIZACION
// (3B) RESTAURAR PREVIO A AUTORIZAR
function isResetBeforeAuth() {
    return "restorePaymentBeforeAuthorization"===($_REQUEST["action"]??"");
}
function doResetBeforeAuth() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"categoria"=>"SOLICITUD"];
    sessionInit();
    if (!hasUser()) {
        echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
        //doclog("Restauración de Solicitud de Pago sin sesión","solpago",$baseData+["line"=>__LINE__]);
        die();
    }
    // ToDo: a solicitudpago.status restarle 128 y en tokens.status cambiarlo a activo para modulo in (autorizaPago o rechazaPago) (necesario para reenviar correo) y en facturas.statusn restarle 128 y ajustar facturas.status al que corresponda
    //json_encode(value);
    
    $solId=$_POST["solId"]??"";
    if (!isset($solId[0])) {
        errNDie("Debe indicar la solicitud a restaurar",$baseData+["line"=>__LINE__]+$_POST);
    }
    $module=$_POST["module"]??"";
    $qryList=[];
    if ($module==="rechazaPago") {
        global $solObj,$query; if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj = new SolicitudPago(); }
        $solData=$solObj->getData("id=$solId",0,"idFactura, idOrden, status");
        if (!isset($solData[0])) {
            errNDie("Solicitud no encontrada",$baseData+["line"=>__LINE__,"query"=>$query]);
        }
        $solData=$solData[0];
        $idOrden=$solData["idOrden"]??"";
        $idFactura=$solData["idFactura"]??"";
        $solStatus=+$solData["status"]??"0";
        if ($solStatus<128) errNDie("La solicitud no está cancelada",$baseData+["line"=>__LINE__,"query"=>$query,"data"=>$solData]);
        $qryList[]=["query"=>$query]+$solData;
        DBi::autocommit(false);
        if (isset($idOrden[0])) {
            global $ordObj; if (!isset($ordObj)) { require_once "clases/OrdenesCompra.php"; $ordObj = new OrdenesCompra(); }
            if (!$ordObj->saveRecord(["id"=>$idOrden,"status"=>isset($idFactura[0])?"1":"0"])&&!empty(DBi::$errno)) {
                $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
                errNDie("No fue posible actualizar Orden de Compra",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$ordObj->errors,"dberrors"=>DBi::$errors,"log"=>$ordObj->log]);
            }
            $qryList[]=["query"=>$query];
        } else if (isset($idFactura[0])) {
            global $invObj;
            $invData=$invObj->getData("id=$idFactura",0,"statusn,status,tipoComprobante");
            if (!isset($invData[0])) {
                $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
                errNDie("Factura no encontrada",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$invObj->errors,"dberrors"=>DBi::$errors,"log"=>$invObj->log]);
            }
            $invData=$invData[0];
            $qryList[]=["query"=>$query]+$invData;
            $statusn=+$invData["statusn"];
            if ($statusn<128) {
                DBi::rollback(); DBi::autocommit(true);
                errNDie("La factura no está cancelada",$baseData+["line"=>__LINE__,"idSolicitud"=>$solId,"idFactura"=>$idFactura,"statusn"=>$statusn,"status"=>$status]);
            }
            $statusn-=128;
            $invStatus=Facturas::statusnToDetailStatus($statusn,$invData["tipoComprobante"]);
            if (!$invObj->saveRecord(["id"=>$idFactura,"statusn"=>$statusn,"status"=>$invStatus])) {
                $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
                errNDie("No fue posible actualizar Factura",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$invObj->errors,"dberrors"=>DBi::$errors,"log"=>$invObj->log]);
            }
            $lastQuery=$query;
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioFactura($idFactura, $invStatus, getUser()->nombre, false, "Restaura solicitud rechazada");
            $qryList[]=["query"=>$lastQuery];
        }
        global $tokObj; if (!isset($tokObj)) { require_once "clases/Tokens.php"; $tokObj = new Tokens(); }
        $tokObj->clearOrder();
        $tokObj->addOrder("id","desc");
        $tokData=$tokObj->getData("refId=$solId and modulo in ('autorizaPago','rechazaPago','transfiereArchivos','procesaCompras','procesaConta','anexaComprobante','procesaPago')");
        if (!isset($tokData[0])) {
            $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
            errNDie("Token no encontrado",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors,"log"=>$tokObj->log]);
        }
        $qryList[]=["query"=>$query,"result"=>$tokData];
        $tokModule=$tokData[0]["modulo"]??"";
        if ($tokModule==="rechazaPago"||$tokModule==="autorizaPago") {
            if (!$tokObj->saveRecord(["refId"=>$solId,"modulo"=>["autorizaPago","rechazaPago"],"status"=>"activo","usos"=>null])) {
                $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
                errNDie("No fue posible actualizar los tokens de autorizacion",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors,"log"=>$tokObj->log]);
            }
        //} else if ($tokModule==="transfiereArchivos") { DBi::rollback(); DBi::autocommit(true);
        //    errNDie("Cambiando TOKENS1 $tokModule",$baseData+["line"=>__LINE__]);
        //} else if ($tokModule==="procesa") { DBi::rollback(); DBi::autocommit(true);
        //    errNDie("Cambiando TOKENS2 $tokModule",$baseData+["line"=>__LINE__]);
            global $firObj; if (!isset($firObj)) { require_once "clases/Firmas.php"; $firObj=new Firmas(); }
            $usrId=getUser()->id;
            if ($usrId===1) {
                global $usrObj; if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
                $usrId=$usrObj->getValue("nombre","SISTEMAS","id");
            }
            if (!$firObj->saveRecord(["idReferencia"=>$solId,"idUsuario"=>$usrId,"accion"=>"restaura","motivo"=>"Falta indicar quien solicita"])) {
                $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
                errNDie("No fue posible agregar firma de restauración",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$firObj->errors,"dberrors"=>DBi::$errors,"log"=>$firObj->log]);
            }
            $qryList[]=["query"=>$query];
            if (!$solObj->saveRecord(["id"=>$solId,"status"=>($solStatus-128)])) {
                $lastQuery=$query; DBi::rollback(); DBi::autocommit(true);
                errNDie("No fue posible modificar la solicitud",$baseData+["line"=>__LINE__,"query"=>$lastQuery,"errors"=>$solObj->errors,"dberrors"=>DBi::$errors,"log"=>$solObj->log]);
            }
            $qryList[]=["query"=>$query];
            // ToDo: Contemplar si se requiere envío de correo al proveedor y al solicitante
            //DBi::commit();
            //successNDie("Solicitud restaurada");
        } else {
            DBi::rollback(); DBi::autocommit(true);
            errNDie("No válido para restaurar: ".implode(",",array_column($tokData, "modulo")),$baseData+["line"=>__LINE__]);
        }
        DBi::rollback(); DBi::autocommit(true);
        errNDie("Restauración en Construcción",$baseData+["line"=>__LINE__,"queries"=>$qryList],"solpago");
    } else {
        errNDie("Módulo inválido",$baseData+["line"=>__LINE__]);
        /*echo "<h3>Módulo inconcluso, realizar procedimiento en base de datos:</h3>".
            "<ul class='restaura'>".
                "<li><span>SolicitudPago.folio :</span>Obtener id=>solId, idFactura, idOrden</li>".
                "<li><span>OrdenesCompra.status :</span>Para id=idOrden, cambiar a 0</li>".
                "<li><span>Facturas.statusn :</span>Para id=idFactura, restarle 128</li>".
                "<li><span>Facturas.status :</span>Y calcular con nuevo statusn</li>".
                "<li><span>Tokens.status :</span>Para refId=solId y modulo in (autorizaPago o rechazaPago), cambiar a activo</li>".
                "<li><span>Firmas :</span>Para idReferencia=solId, insertar registro idReferencia=solId, idUsuario=1038 (SISTEMAS),accion=restaura,motivo=quien solicita</li>".
                "<li><span>SolicitudPago.status :</span>Para id=solId, restarle 128</li>".
            "</ul>".
            "<p>".json_encode($_POST)."</p>";*/
    }
    //errNDie("NO CONTEMPLADO",$baseData+["line"=>__LINE__]+$_POST,"solpago");
}// FIN (3B) RESTAURAR PREVIO A AUTORIZAR
// (4) TRANSFERENCIA DE ARCHIVOS A AVANCE //
function isTransferPaymAuth() {
    return "requestTransferInvoiceFiles"===($_REQUEST["action"]??"");
}
function doTransferPaymAuth() {
    global $tokObj, $query;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    $encabezado="EXPORTAR FACTURA EN SOLICITUD";
    if(!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    if (isset($_GET["token"])) {
        $token=$_GET["token"];
        $beInteractive=true;
    } else {
        sessionInit();
        if (!hasUser()) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
            doclog("Transferencia de archivos sin sesión","solpago",$baseData+["line"=>__LINE__]);
            die();
        }
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esAvance=validaPerfil("Avance")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esAvance) {
            echo json_encode(["action"=>"redirect","mensaje"=>"No tiene permiso para para transferir archivos a Avance","errorMessage"=>"No tiene permiso para para transferir archivos a Avance"]);
            doclog("Transferencia de Archivos fallida. Sin perfil Avance.","solpago",$baseData+["line"=>__LINE__,"username"=>$username,"usrId"=>$usrId]);
            die();
        }
        $solId=$_POST["solId"]??null;
        $modulo=$_POST["module"]??null;
        if (!isset($solId[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Solicitud no identificada","errorMessage"=>"Solicitud no identificada"]);
            doclog("Error en transferencia de archivos: No hay solicitud","error",$baseData+["line"=>__LINE__]);
            die();
        }
        if (!isset($modulo[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            doclog("Error en transferencia de archivos: No hay módulo","error",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            die();
        }
        if ($modulo!=="transfiereArchivos") {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            doclog("Error en transferencia de archivos: El módulo debe ser 'transfiereArchivos'","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo]);
            die();
        }
        $tokSrch=$tokObj->getData("refId=$solId and modulo='$modulo'",0,"token,usrId,status");
        if (isset($tokSrch["token"][0])) $tokSrch=[$tokSrch];
        $tokQry=$query;
        if (isset($tokSrch[0]["token"][0])) {
            $validTokenIdx=-1;
            $otherStatus=[];
            $logtxt="";
            $usrIds=[];
            foreach ($tokSrch as $idx => $tokItem) {
                $tokStatus=$tokItem["status"];
                if ($tokStatus!=="activo" && $tokStatus!=="ocupado") {
                    $otherStatus=$tokItem;
                    $logtxt.="IDX$idx)STATUS=".$tokStatus." NO ACEPTADO! ";
                    continue;
                }
                $tokUsrId=$tokItem["usrId"];
                if (isset($tokUsrId[0])) $tokUsrId=+$tokUsrId;
                else {
                    $logtxt.="IDX$idx)USRID VACIO! ";
                    continue;
                }
                if($tokUsrId===$usrId) {
                    $validTokenIdx=$idx;
                    $logtxt.="IDX$idx)USRID=".$tokUsrId." ES VALIDO! ";
                    break;
                } else if ($esSistemas) {
                    $validTokenIdx=$idx;
                    $logtxt.="IDX$idx)USR ES ADMIN! ";
                    break;
                } else if ($esAvance) {
                    global $solObj;
                    if (!isset($solObj)) {
                        require_once "clases/SolicitudPago.php";
                        $solObj = new SolicitudPago();
                    }
                    $solData=$solObj->getData("id=$solId");
                    if (isset($solData[0]["idEmpresa"])) {
                        $idEmpresa=$solData[0]["idEmpresa"];
                    }
                    global $perObj;
                    if (!isset($perObj)) {
                        require_once "clases/Perfiles.php";
                        $perObj=new Perfiles();
                    }
                    $perData=$perObj->getData("nombre='Compras' and estado=1",0,"id");
                    if (isset($perData[0]["id"][0])) $idPerfil=$perData[0]["id"];
                    if (isset($idEmpresa)&&isset($idPerfil)) {
                        global $ugObj;
                        if(!isset($ugObj)) {
                            require_once "clases/Usuarios_Grupo.php";
                            $ugObj=new Usuarios_Grupo();
                        }
                        $ugData=$ugObj->getData("idUsuario=$usrId and idGrupo=$idEmpresa and idPerfil=$idPerfil",0,"idUsuario");
                        if (isset($ugData[0]["idUsuario"])) {
                            $validTokenIdx=$idx;
                            $logtxt.="IDX$idx)USR ES AVANCE VALIDO! ";
                            $break;
                        } else {
                            $logtxt.="IDX$idx)USR ES AVANCE SIN PERMISO! ";
                        }
                    } else {
                        $logtxt.="IDX$idx)USR ES AVANCE SIN SOLICITUD O SIN PERFIL! ";
                    }
                    $usrIds[]=$tokUsrId;
                } else {
                    $usrIds[]=$tokUsrId;
                }
                $logtxt.="IDX$idx)STATUS=".$tokStatus." and USRID=".$tokUsrId."!==$usrId"."! ";
            }
            if ($validTokenIdx>=0)
                $token=$tokSrch[$validTokenIdx]["token"];
            else {
                echo getRespGralView($encabezado,"Sólo el usuario que inició la solicitud puede responder",$solId,"",true,false,true);
                doclog("Error en transferencia de archivos: Usuario incorrecto","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"usuario"=>$username,"usrId"=>$usrId,"tokUsrIds"=>$usrIds,"query"=>$tokQry,"otherStatus"=>$otherStatus,"log"=>$logtxt]);
                die();
            }
        } else {
            echo getRespGralView($encabezado,"Token no identificado",$solId,"",true,false,true);
            doclog("Error en transferencia de archivos: No hay token","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"query"=>$tokQry]);
            die();
        }
        $beInteractive=false;
    }
    doclog("Antes de elegir Token","token",["token"=>$token]);
    $prevTokenData=$tokObj->obtenStatusData($token);
    DBi::autocommit(false);
    if ($tokObj->eligeToken($token,"ocupado",true,true)) {
        doclog("Token Elegido","token");
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esAvance=validaPerfil("Avance")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esAvance) {
            echo getRespGralView($encabezado,"No tiene permiso para exportar facturas a Avance","","",true,false,true);
            doclog("Intento de exportar a Avance fallida. Sin perfil Avance","error",$baseData+["line"=>__LINE__,"usr"=>["id"=>$usrId,"name"=>$username],"token"=>$token]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $solId=$tokObj->data["refId"];
        $status=$tokObj->data["status"];
        $modulo=$tokObj->data["modulo"];
        $usos=$tokObj->data["usos"];
    } else {
        doclog("Token NO Elegido","token",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrno"=>DBi::$errno,"dberror"=>DBi::$error,"query"=>$query]);
        if (isset($tokObj->data["refId"])) {
            $solId=$tokObj->data["refId"];
            if (isset($tokObj->errorMessage)) {
                echo getRespGralView($encabezado,$tokObj->errorMessage,$solId,"",true,false,true);
                doclog("Error en transferencia de archivos","error",$baseData+["line"=>__LINE__,"errorMessage"=>$tokObj->errorMessage]+$tokObj->data);
            } else {
                $tokErrMsg0=$tokObj->errors[0]??"";
                if (isset($tokObj->data["usos"]) && (+$tokObj->data["usos"])<=0) {
                    if (isset($tokObj->data["ocupado"])) {
                        $msg="realizada previamente";
                    } else $msg="cancelada";
                    echo getRespGralView($encabezado,"Transferencia $msg",$solId,"",true,false,true);
                    doclog("Error en transferencia de archivos","solpago",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                } else {
                    echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
                    doclog("Error en transferencia de archivos","error",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                }
            }
        } else if (isset($tokObj->errorMessage)) {
            echo getRespGralView($encabezado,$tokObj->errorMessage,"","",true,false,true);
            doclog("Error en transferencia de archivos","error",$baseData+["line"=>__LINE__,"token"=>$token,"errorMessage"=>$tokObj->errorMessage]);
        } else {
            echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
            doclog("Error en transferencia de archivos","error",$baseData+["line"=>__LINE__,"token"=>$token,"errors"=>$tokObj->errors]);
        }
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        echo getRespGralView($encabezado,"La solicitud ya no existe",$solId,"",true,false,true);
        doclog("Error en transferencia de archivos: la solicitud no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solStatus=+$solData["status"];
    $encabezado="EXPORTAR FACTURA EN SOLICITUD $solFolio";
    if ($solStatus&SolicitudPago::STATUS_CANCELADA) {
        echo getRespGralView($encabezado,"La solicitud esta cancelada",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: la solicitud esta cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $conFactura=(isset($solData["idFactura"]) && !empty($solData["idFactura"]));
    $conOrden=(isset($solData["idOrden"]) && !empty($solData["idOrden"]));
    if (!$conFactura && $conOrden) {
        echo getRespGralView($encabezado,"La solicitud no tiene factura",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: la solicitud tiene orden de compra","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $idFactura=$solData["idFactura"];
    global $invObj;
    $invData=$invObj->getData("id=$idFactura");
    if (!isset($invData[0])) {
        echo getRespGralView($encabezado,"La factura en la solicitud se ha eliminado",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: No se encontró la factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $invData=$invData[0];
    if (!isset($invData["status"][0])) {
        echo getRespGralView($encabezado,"La factura en la solicitud ha sido alterada",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: La factura no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["version"])||!in_array($invData["version"], ["3.3","4.0"])) {
        echo getRespGralView($encabezado,"La versión de factura $invData[version] no es válida",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["tipoComprobante"])||$invData["tipoComprobante"]!=="i") {
        echo getRespGralView($encabezado,"Se ingresó un comprobante que no es factura",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: El tipo comprobante no es ingreso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($invData["status"]==="Temporal" || !isset($invData["statusn"])) {
        echo getRespGralView($encabezado,"La solicitud no se registró correctamente",$solId,$solFolio,true,false);
        if ($invData["status"]==="Temporal") $mensaje="El status es Temporal";
        else $mensaje="El status numérico no esta definido";
        doclog("Error en transferencia de archivos: $mensaje","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $invStatusN=+$invData["statusn"];
    if ($invStatusN>=Facturas::STATUS_RECHAZADO) {
        echo getRespGralView($encabezado,"La factura ya está Cancelada",$solId,$solFolio,true,$beInteractive);
        doclog("Error en transferencia de archivos: Factura Cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
        echo getRespGralView($encabezado,"La factura no se acepto correctamente",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: Factura No Aceptada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["estadoCFDI"])||$invData["estadoCFDI"]!=="Vigente") {
        echo getRespGralView($encabezado,"La factura ya no es vigente ante el SAT",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: La factura no esta Vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invData["estadoCFDI"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $gpoObj;
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj = new Grupo();
    }
    $gpoData=$gpoObj->getData("rfc='$invData[rfcGrupo]'");
    if (!isset($gpoData[0]["alias"][0])) {
        echo getRespGralView($encabezado,"No se encuentra el registro de la empresa cliente",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: La empresa cliente no está registrada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"rfcGrupo"=>$invData["rfcGrupo"]]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $gpoData=$gpoData[0];
    $gpoAlias=$gpoData["alias"];
    if ($gpoAlias==="CASABLANCA") $gpoAlias="LAMINADOS";
    $empresasValidas=["APSA","JYL","COREPACK","MORYSAN","GLAMA","SKARTON","DESA","LAMINADOS","LAISA","BIDASOA","MELO","DANIEL","JLA","RGA","MARLOT","APEL","FIDEICOMIS","FOAMYMEX","PAPEL","CAPITALH","HALL","FIDEMIFEL","ESMERALDA","JYLSOR","SERVICIOS","APSALU","ENVASES","MORYCE","BIDARENA"];
    if (!in_array($gpoAlias, $empresasValidas)) {
        echo getRespGralView($encabezado,"No se ha habilitado la empresa $gpoAlias para exportar",$solId,$solFolio,true,false);
        doclog("Error en transferencia de archivos: Empresa no habilitada para Exportar","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"alias"=>$gpoAlias]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }

    $textoAExportar="";
    $invFolio=$invData["folio"];
    if (!isset($invFolio[0]))
        $invFolio=$invData["uuid"];
    if (isset($invFolio[0]))
        $invFolio=str_replace(" ", "", $invFolio);
    if (isset($invFolio[10]))
        $invFolio=substr($invFolio, -10);
    $fechaFactura = DateTime::createFromFormat("Y-m-d H:i:s",$invData["fechaFactura"]);
    $fechaYMD=$fechaFactura->format("ymd");
    $invPedido=$invData["pedido"];
    if (isset($invPedido[0]))
        $invPedido=str_replace(" ", "", $invPedido);
    $codProv=$invData["codigoProveedor"];
    if (isset($codProv[0]))
        $codProv=str_replace(" ", "", $codProv);
    $tipoCambio=$invData["tipoCambio"];
    $intTCambio=intval($tipoCambio);
    if ($intTCambio==1||$intTCambio==0)
        $tipoCambio="0 1.0000";
    else $tipoCambio="1 ".number_format(floatval($tipoCambio),4);
    $tasa="1";

    $textoAExportar.="$invFolio $fechaYMD $invPedido 1 $codProv 0 0 0 $tipoCambio ";

    global $cptObj;
    if (!isset($cptObj)) {
        require_once "clases/Conceptos.php";
        $cptObj = new Conceptos();
    }
    $cptObj->rows_per_page=0;
    $cptData=$cptObj->getData("idFactura=".$idFactura);
    foreach ($cptData as $idx => $cptRow) {
        $textoAExportar.="$cptRow[codigoArticulo] $tasa $cptRow[cantidad] $cptRow[precioUnitario] ";
    }
    $textoAExportar.=str_pad("@",851).PHP_EOL;
    $nombreArchivo=$gpoAlias.str_replace("-","",$codProv)."Sol".$solId."Chk.txt";
    
    $emulate=FALSE;
    if ($emulate) {
        doclog("TRANSFERENCIA DE ARCHIVO DE TEXTO SIMULADA","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"texto"=>$textoAExportar]);
    } else {
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceGlama();
        if ($ftpObj==null) {
            echo getRespGralView($encabezado,"No se pudo iniciar conexi&oacute;n a Avance",$solId,$solFolio,true,$beInteractive);
            doclog("Error en transferencia de archivos: Falla al iniciar FTP","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"error"=>getErrorData(MIFTP::$lastException)]); // ,"log"=>MIFTP::$log
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        try {
            $ftpObj->exportarTexto($nombreArchivo, $textoAExportar);
        } catch (Exception $e) {
            echo getRespGralView($encabezado,"Error al exportar datos a Avance",$solId,$solFolio,true,$beInteractive);
            doclog("Error en transferencia de archivos: Falla al exportar texto","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"archivo"=>$nombreArchivo,"error"=>getErrorData($e)]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
    }
    $invStatusN|=Facturas::STATUS_EXPORTADO;
    $solStatus|=SolicitudPago::STATUS_EXPORTADA;
    $rutaLocal = $invData["ubicacion"];
    $xmlFileName = $invData["nombreInterno"].".xml";
    $lookoutFilePath="";
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
    global $ftp_supportPath;
    $rutaAvance = $ftp_supportPath.$gpoAlias."/PUBLICO/";
    $genericXMLFilePath = $lookoutFilePath.$rutaLocal.$xmlFileName;
    if (file_exists($genericXMLFilePath)) {
        if ($emulate) {
            doclog("TRANSFERENCIA DE XML SIMULADA","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"XML"=>$genericXMLFilePath]);
        } else {
            try {
                $ftpObj->cargarArchivoAscii($rutaAvance, $xmlFileName, $genericXMLFilePath);
            } catch (Exception $e) {
                echo getRespGralView($encabezado,"Error al respaldar XML a Avance",$solId,$solFolio,true,$beInteractive);
                doclog("Error en transferencia de archivos: Falla al respaldar XML","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"error"=>getErrorData($e)]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
        }
    } else {
        echo getRespGralView($encabezado,"Error al respaldar XML a Avance",$solId,$solFolio,true,$beInteractive);
        doclog("Error en transferencia de archivos: No existe el archivo XML","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"XML"=>$genericXMLFilePath]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (isset($invData["nombreInternoPDF"][0])) {
        $pdfFileName=$invData["nombreInternoPDF"].".pdf";
        $genericPDFFilePath=$lookoutFilePath.$rutaLocal.$pdfFileName;
        if (file_exists($genericPDFFilePath)) {
            if ($emulate) {
                doclog("TRANSFERENCIA DE PDF SIMULADA","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"PDF"=>$genericPDFFilePath]);
            } else {
                try {
                    $ftpObj->cargarArchivoBinario($rutaAvance,$pdfFileName,$genericPDFFilePath);
                } catch (Exception $e) {
                    echo getRespGralView($encabezado,"Error al respaldar PDF a Avance",$solId,$solFolio,true,$beInteractive);
                    doclog("Error en transferencia de archivos: Falla al respaldar PDF","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"error"=>getErrorData($e)]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
                }
            }
        }
    }
    // ToDo_SOLICITUD: crear $newInvStatusN y $newSolStatus para conservar status actual y nuevo, para poder compararlos y saber cuando no hace falta guardar nuevamente.
    $invStatusN|=Facturas::STATUS_RESPALDADO;
    if ($invObj->saveRecord(["id"=>$idFactura,"status"=>"Respaldado","statusn"=>$invStatusN])) {
        global $prcObj;
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj=new Proceso();
        }
        $prcObj->cambioFactura($idFactura, "Respaldado", getUser()->nombre, false, "Solicitud iniciando proceso compras");
    }
    $solStatus|=SolicitudPago::STATUS_RESPALDADA;
    $solObj->saveRecord(["id"=>$solId,"status"=>$solStatus]);
    $solObj->firma($solId,"exporta");
    DBi::commit();
    DBi::autocommit(true);
    $pgrA="<p class=\"mb0\">";
    $pgrU="</p>";
    $hostname=$_SERVER["HTTP_ORIGIN"];
    $basepath=$_SERVER["WEB_MD_PATH"];
    $invoiceHref="$hostname$basepath";
    $accessHref="http://glama.esasacloud.com/avance/cgi-bin/e-sasa/uno";
    $rutaArchivo=$ftpObj->ftpExportPath.$nombreArchivo;
    $htmlMessage="<SCRIPT type=\"text/javascript\">var timeOutAux=0;function vtt(evt){const tgt=evt.target;if(tgt){if(tgt.ttp)return;const ttt=tgt.getAttribute('ttp');let tto=false;try{tto=JSON.parse(ttt);if('eName' in tto||'eText' in tto){tgt.ttp=ecrea(tto);cladd(tgt.ttp,'ttp');}}catch(ex){}if(!tgt.ttp)tgt.ttp=ecrea({eName:'DIV',className:'ttp',eText:ttt});tgt.appendChild(tgt.ttp);tgt.onmousemove=dtt;tgt.onmouseleave=htt;}}function htt(evt){const tgt=evt.target;if(tgt&&tgt.ttp){tgt.removeChild(tgt.ttp);tgt.ttp=false;delete tgt.ttp;tgt.onmousemove=false;delete tgt.onmousemove;}}function dtt(evt){const tgt=evt.target;if(tgt&&tgt.ttp){tgt.ttp.style.top=(evt.clientY+10)+'px';tgt.ttp.style.left=(evt.clientX-10)+'px';}}function isEl(o){return(typeof HTMLElement==='object'?o instanceof HTMLElement:o&&typeof o==='object'&&o!==null&&o.nodeType===1&&typeof o.nodeName==='string');}function ecrea(pp){if(isEl(pp))return pp;if(Array.isArray(pp)){const ar=[];pp.forEach(function(el){ar.push(ecrea(el));});return ar;}let ppn=Object.keys(pp);if(pp.eName){let idx=ppn.indexOf('eName');if(idx>=0)ppn.splice(idx,1);idx=ppn.indexOf('eText');if(idx>=0)ppn.splice(idx,1);idx=ppn.indexOf('eChilds');if(idx>=0)ppn.splice(idx,1);let nwo=document.createElement(pp.eName);for(let i=0;i<ppn.length;i++){nwo[ppn[i]]=pp[ppn[i]];}if(pp.eChilds){if(Array.isArray(pp.eChilds))for(let i=0;i<pp.eChilds.length;i++){let ch=ecrea(pp.eChilds[i]);if(ch)nwo.appendChild(ch);}else{let ch=ecrea(pp.eChilds);if(ch)nwo.appendChild(ch);}}else if(pp.eText)nwo.appendChild(document.createTextNode(pp.eText));return nwo;}else if(pp.eText){let nwo=document.createTextNode(pp.eText);return nwo;}return null;}function ebyid(id){return document.getElementById(id);}function cladd(el,cn){if(cn&&el){if(Array.isArray(el)){let n=0;el.forEach(function(se){n+=cladd(se,cn);});return n;}else if(el instanceof NodeList||el instanceof HTMLCollection){let n=0;for(let n=0;n<el.length;n++){n+=cladd(el[n],cn);}return n;}if(typeof el==='string'){return cladd(ebyid(el),cn);}if(el.classList){if(Array.isArray(cn)){let ct=cn.length;fee(cn,function(class1){if(el.classList.contains(class1))ct--;});if(ct>0)el.classList.add(...cn);return ct;}else{if(el.classList.contains(cn))return 0;el.classList.add(cn);return 1;}}}return 0;}function clrem(el,cn){if(cn&&el){if(Array.isArray(el)){let n=0;el.forEach(function(se){n+=clrem(se,cn);});return n;}else if(el instanceof NodeList||el instanceof HTMLCollection){let n=0;for(let n=0;n<el.length;n++){n+=clrem(el[n],cn);}return n;}if(typeof el==='string'){return clrem(ebyid(el),cn);}if(el.classList){if(Array.isArray(cn)){let count=0;fee(cn,function(class1){if(el.classList.contains(class1))count++;});if(count>0)el.classList.remove(...cn);return count;}else{if(!el.classList.contains(cn))return 0;el.classList.remove(cn);return 1;}}}return 0;}function ekil(el){if(el){if(el.parentNode)el.parentNode.removeChild(el);else delete el;}}function ta2cb(ta,le,be){let lt='';let bl='';let ss=false;if(ta&&ta.value.length>0){try{ta.select();let sf=document.execCommand('copy');lt=(sf?'La ruta fue copiada satisfactoriamente':'No se pudo copiar la ruta');bl=(sf?'COPIADO':'NO COPIADO');ss=true;}catch(err){lt='Error al copiar texto: '+err.message;bl='ERROR AL COPIAR';}}else{lt='No hay texto que copiar';bl='VACIO';}if(le){while(le.firstChild)le.removeChild(le.firstChild);le.appendChild(document.createTextNode(lt));}if(be){while(be.firstChild)be.removeChild(be.firstChild);be.appendChild(document.createTextNode(bl));}return ss;}function tx2cb(tx,lId,bId){const tao=ecrea({eName:'TEXTAREA',style:{position:'fixed',top:0,left:0,width:'2em',height:'2em',padding:0,border:'none',outline:'none',boxShadow:'none',background:'transparent'},value:tx});document.body.appendChild(tao);const le=(lId?ebyid(lId):null);const be=(bId?ebyid(bId):null);const rs=ta2cb(tao,le,be);document.body.removeChild(tao);return rs;}function clearSelection(){if(window.getSelection){if(window.getSelection().empty){window.getSelection().empty();}else if(window.getSelection().removeAllRanges){window.getSelection().removeAllRanges();}}else if(document.selection){document.selection.empty();}}</SCRIPT>".
    "<STYLE>.ttc{text-decoration:underline;text-decoration-style:dashed;}.ttc:hover{cursor:help;}.ttc>.ttp{border:#a0a0a0 2px dotted;display:block;z-index:1050;background-color:rgba(255,255,255,0.9);position:absolute;text-decoration:none;opacity:1;filter:alpha(opacity=10);}ol.gts{width:310px;margin:auto;text-align:left;}ol.hsq{list-style:none;counter-reset:hhsq;padding-inline-start:35px;}ol.hsq li{counter-increment:hhsq;font-size:14px;color:#008;}ol.hsq li::before{content:counter(hhsq) '. ';font-weight:bold;}ol.hsq li.selected{font-size:13px;font-weight:bold;background-color:rgba(255,127,0,0.1);}ol.hsq li.selected::before{background-color:rgba(255,0,0,0.2);color:darkred;}.invis{visibility:hidden;}.mxw250{max-width:250px;}.fpf{width:350px;background-color:rgba(255,255,255,0.7);color:#008;border-width:1px;}.vipLbl{color:rebeccapurple;font-weight:bold;}.albk{color:#008;text-decoration:underline;font-weight:bold;background-color:rgba(100,100,255,0.2);border:outset lightgrey 2px;padding:2px;}.albk:hover,.albk:focus,.albk:visited{color:currentColor;text-decoration:underline;cursor:pointer;}.mh5{margin-top;5px;margin-bottom:5px;}.mb0{margin-bottom:0px;}</STYLE>".
    "{$pgrA}Seleccionar el siguiente cuadro de texto, se copiar&aacute; al <span class=\"ttc\" onmouseenter=\"vtt(event);\" ttp='{\"eName\":\"DIV\",\"className\":\"mxw250\",\"eText\":\"Espacio en memoria donde se copia el texto seleccionado para ser pegado en otro lugar.\"}'>PORTAPAPELES</span>:<br><span class=\"invis\">COPIADO</span>&nbsp;<input type=\"text\" class=\"fpf\" value=\"$rutaArchivo\" readonly onclick=\"tx2cb('$rutaArchivo');clrem('copyLabel','invis');this.setSelectionRange(0,this.value.length);this.select();clearTimeout(timeOutAux);timeOutAux=setTimeout(function(){cladd('copyLabel','invis');clearSelection();},3500);\">&nbsp;<span id=\"copyLabel\" class=\"vipLbl invis\">COPIADO</span>{$pgrU}".
    "{$pgrA}Realizar los siguientes pasos en otra ventana donde ingrese a <A href=\"$accessHref\" target=\"avance\" class=\"albk\" onclick=\"tx2cb('$rutaArchivo');\">Avance</A>:{$pgrU}".
    "<table style=\"margin:auto;\"><tr><td><img src=\"{$invoiceHref}imagenes/TutorialExportar4.gif\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"this.previousElementSibling.src='{$invoiceHref}imagenes/TutorialExportar4.gif?a='+Math.random();let gts=ebyid('gts');if(!gts)gts=ebyid('Rgts');gts.stp=gts.firstElementChild;gts.idx=1;cladd(gts.stp,'selected');var gtsI=setInterval(function(){let gts=ebyid('gts');if(!gts)gts=ebyid('Rgts');if(gts){clrem(gts.stp,'selected');gts.stp=gts.stp.nextElementSibling;gts.idx++;if(!gts.stp){gts.stp=gts.firstElementChild;gts.idx=1;}cladd(gts.stp,'selected');}else{clearInterval(gtsI);}},2100);ekil(this);\"></td><td><ol id=\"gts\" class=\"gts hsq\"><li>Ingresar en Compras a Procesos Batch<li>Ingresar a Carga de Facturas/Remisiones<li>Ingresar a Formato con moneda<li>Seleccionar recuadro de texto Nombre del Archivo<li>Pegar el archivo que se encuentra en el Portapapeles (CTRL+V)</ol></td></table>{$pgrA}Si en Avance no puede realizar algún paso, necesita solicitar permiso para su usuario.{$pgrU}<HR>";

    echo getRespGralView($encabezado,$htmlMessage,$solId,$solFolio,false,false,false);
} // FIN (4) TRANSFERENCIA DE ARCHIVOS A AVANCE //
// (5) PROCESAMIENTO DE FACTURA //
function isProcessPaymReq() {
    return "responseSetValidRequest"===($_REQUEST["action"]??"");
}
function doProcessPaymReq() {
    global $tokObj, $query;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    $encabezado="SOLICITUD EN PROCESO COMPRAS";
    if(!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    if (isset($_GET["token"])) {
        $token=$_GET["token"];
        $beInteractive=true;
    } else {
        sessionInit();
        if (!hasUser()) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
            doclog("Proceso Compras sin sesión","solpago",$baseData+["line"=>__LINE__]);
            die();
        }
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esCompras=validaPerfil("Compras")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esCompras) {
            echo json_encode(["action"=>"redirect","mensaje"=>"No tiene permiso para para transferir archivos a Avance","errorMessage"=>"No tiene permiso para para transferir archivos a Avance"]);
            doclog("Error en Proceso Compras: Sin perfil Compras.","solpago",$baseData+["line"=>__LINE__,"username"=>$username,"usrId"=>$usrId]);
            die();
        }
        $solId=$_POST["solId"]??null;
        $modulo=$_POST["module"]??null;
        if (!isset($solId[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Solicitud no identificada","errorMessage"=>"Solicitud no identificada"]);
            doclog("Error en Proceso Compras: No hay solicitud","error",$baseData+["line"=>__LINE__]);
            die();
        }
        if (!isset($modulo[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            doclog("Error en Proceso Compras: No hay módulo","error",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            die();
        }
        if ($modulo!=="procesaCompras") {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            doclog("Error en Proceso compras: El módulo debe ser 'procesaCompras'","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo]);
            die();
        }
        $tokSrch=$tokObj->getData("refId=$solId and modulo='$modulo'",0,"token,usrId,status");
        if (isset($tokSrch["token"][0])) $tokSrch=[$tokSrch];
        $tokQry=$query;
        if (isset($tokSrch[0]["token"][0])) {
            $validTokenIdx=-1;
            $otherStatus=[];
            $logtxt="";
            $usrIds=[];
            foreach ($tokSrch as $idx => $tokItem) {
                $tokStatus=$tokItem["status"];
                if ($tokStatus!=="activo" && $tokStatus!=="ocupado") {
                    $otherStatus=$tokItem;
                    $logtxt.="IDX$idx)STATUS=".$tokStatus." NO ACEPTADO! ";
                    continue;
                }
                $tokUsrId=$tokItem["usrId"];
                if (isset($tokUsrId[0])) $tokUsrId=+$tokUsrId;
                else {
                    $logtxt.="IDX$idx)USRID VACIO! ";
                    continue;
                }
                if($tokUsrId===$usrId) {
                    $validTokenIdx=$idx;
                    $logtxt.="IDX$idx)USRID=".$tokUsrId." ES VALIDO! ";
                    break;
                } else if ($esSistemas) {
                    $validTokenIdx=$idx;
                    $logtxt.="IDX$idx)USR ES ADMIN! ";
                    break;
                } else if ($esCompras) {
                    global $solObj;
                    if (!isset($solObj)) {
                        require_once "clases/SolicitudPago.php";
                        $solObj = new SolicitudPago();
                    }
                    $solData=$solObj->getData("id=$solId");
                    if (isset($solData[0]["idEmpresa"])) {
                        $idEmpresa=$solData[0]["idEmpresa"];
                    }
                    global $perObj;
                    if (!isset($perObj)) {
                        require_once "clases/Perfiles.php";
                        $perObj=new Perfiles();
                    }
                    $perData=$perObj->getData("nombre='Compras' and estado=1",0,"id");
                    if (isset($perData[0]["id"][0])) $idPerfil=$perData[0]["id"];
                    if (isset($idEmpresa)&&isset($idPerfil)) {
                        global $ugObj;
                        if(!isset($ugObj)) {
                            require_once "clases/Usuarios_Grupo.php";
                            $ugObj=new Usuarios_Grupo();
                        }
                        $ugData=$ugObj->getData("idUsuario=$usrId and idGrupo=$idEmpresa and idPerfil=$idPerfil",0,"idUsuario");
                        if (isset($ugData[0]["idUsuario"])) {
                            $validTokenIdx=$idx;
                            $logtxt.="IDX$idx)USR ES COMPRAS VALIDO! ";
                            $break;
                        } else {
                            $logtxt.="IDX$idx)USR ES COMPRAS SIN PERMISO!";
                        }
                    } else {
                        $logtxt.="IDX$idx)USR ES COMPRAS SIN SOLICITUD O SIN PERFIL! ";
                    }
                    $usrIds[]=$tokUsrId;
                } else {
                    $usrIds[]=$tokUsrId;
                }
                $logtxt.="IDX$idx)STATUS=".$tokStatus." and USRID=".$tokUsrId."!==$usrId"."! ";
            }
            if ($validTokenIdx>=0)
                $token=$tokSrch[$validTokenIdx]["token"];
            else {
                echo getRespGralView($encabezado,"Sólo el usuario que inició la solicitud puede responder",$solId,"",true,false,true);
                doclog("Error en Proceso Compras: Usuario incorrecto","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"usuario"=>$username,"usrId"=>$usrId,"tokUsrIds"=>$usrIds,"query"=>$tokQry,"otherStatus"=>$otherStatus,"log"=>$logtxt]);
                die();
            }
        } else {
            echo getRespGralView($encabezado,"Token no identificado",$solId,"",true,false,true);
            doclog("Error en Proceso Compras: No hay token","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo]);
            die();
        }
        $beInteractive=false;
    }
    doclog("Antes de elegir Token","token",["token"=>$token]);
    $prevTokenData=$tokObj->obtenStatusData($token);
    DBi::autocommit(false);
    if ($tokObj->eligeToken($token,"ocupado",false,true)) {
        doclog("Token Elegido","token");
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esCompras=validaPerfil("Compras")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esCompras) {
            echo getRespGralView($encabezado,"No tiene permiso para procesar solicitud","","",true,false,true);
            doclog("Error en Proceso Compras. Sin perfil Compras","error",$baseData+["line"=>__LINE__,"usr"=>["id"=>$usrId,"name"=>$username],"token"=>$token]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $solId=$tokObj->data["refId"];
        $status=$tokObj->data["status"];
        $modulo=$tokObj->data["modulo"];
        $usos=$tokObj->data["usos"];
    } else {
        doclog("Token NO Elegido","token",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrno"=>DBi::$errno,"dberror"=>DBi::$error,"query"=>$query]);
        if (isset($tokObj->data["refId"])) {
            $solId=$tokObj->data["refId"];
            if (isset($tokObj->errorMessage)) {
                echo getRespGralView($encabezado,$tokObj->errorMessage,$solId,"",true,false,true);
                doclog("Error en Proceso Compras","error",$baseData+["line"=>__LINE__,"errorMessage"=>$tokObj->errorMessage]+$tokObj->data);
            } else {
                $tokErrMsg0=$tokObj->errors[0]??"";
                if (isset($tokObj->data["usos"]) && (+$tokObj->data["usos"])<=0) {
                    if (isset($tokObj->data["ocupado"])) {
                        $msg="procesada previamente";
                    } else $msg="cancelada";
                    echo getRespGralView($encabezado,"Solicitud $msg",$solId,"",true,false,true);
                    doclog("Error en Proceso Compras","error",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                } else {
                    echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
                    doclog("Error en Proceso Compras","error",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                }
            }
        } else if (isset($tokObj->errorMessage)) {
            echo getRespGralView($encabezado,$tokObj->errorMessage,"","",true,false,true);
            doclog("Error en Proceso Compras","error",$baseData+["line"=>__LINE__,"token"=>$token,"errorMessage"=>$tokObj->errorMessage]);
        } else {
            echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
            doclog("Error en Proceso Compras","error",$baseData+["line"=>__LINE__,"token"=>$token,"errors"=>$tokObj->errors]);
        }
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        echo getRespGralView($encabezado,"La solicitud ya no existe",$solId,"",true,false,true);
        doclog("Error en Proceso Compras: la solicitud no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solStatus=+$solData["status"];
    $solIdEmpresa=$solData["idEmpresa"];
    $encabezado="SOLICITUD $solFolio EN PROCESO COMPRAS";
    if ($solStatus&SolicitudPago::STATUS_CANCELADA) {
        echo getRespGralView($encabezado,"La solicitud esta cancelada",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: la solicitud esta cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solProceso=+$solData["proceso"];
    if ($solProceso>=SolicitudPago::PROCESO_COMPRAS) {
        echo getRespGralView($encabezado,"La solicitud ya fue procesada",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: la solicitud ya fue procesada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $conFactura=(isset($solData["idFactura"]) && !empty($solData["idFactura"]));
    $conOrden=(isset($solData["idOrden"]) && !empty($solData["idOrden"]));
    if (!$conFactura && $conOrden) {
        echo getRespGralView($encabezado,"La solicitud no tiene factura",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: la solicitud no tiene factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $idFactura=$solData["idFactura"];
    global $invObj;
    $invData=$invObj->getData("id=$idFactura");
    if (!isset($invData[0])) {
        echo getRespGralView($encabezado,"La factura en la solicitud se ha eliminado",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: No se encontró la factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $invData=$invData[0];
    if (!isset($invData["status"][0])) {
        echo getRespGralView($encabezado,"La factura en la solicitud ha sido alterada",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: La factura no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["version"])||!in_array($invData["version"], ["3.3","4.0"])) {
        echo getRespGralView($encabezado,"La versión de factura $invData[version] no es válida",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["tipoComprobante"])||$invData["tipoComprobante"]!=="i") {
        echo getRespGralView($encabezado,"Se ingresó un comprobante que no es factura",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: El tipo comprobante no es ingreso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($invData["status"]==="Temporal" || !isset($invData["statusn"])) {
        echo getRespGralView($encabezado,"La solicitud no se registró correctamente",$solId,$solFolio,true,false);
        if ($invData["status"]==="Temporal") $mensaje="El status es Temporal";
        else $mensaje="El Status numerico no esta definido";
        doclog("Error en Proceso Compras: $mensaje","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $invStatusN=+$invData["statusn"];
    if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
        echo getRespGralView($encabezado,"La solicitud no se acepto correctamente",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: Factura No Aceptada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["estadoCFDI"])||$invData["estadoCFDI"]!=="Vigente") {
        echo getRespGralView($encabezado,"La factura ya no es vigente ante el SAT",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: La factura no esta Vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invData["estadoCFDI"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $usrObj;
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $fromObj=["address"=>getUser()->email,"name"=>replaceAccents(getUser()->persona)];
    $respuesta="<h2>La solicitud $solFolio ha sido procesada por ".$fromObj["name"]."</h2>";
    $usrData=$usrObj->getData("ug.idPerfil=".SolicitudPago::PERFIL_GESTIONA." and ug.idGrupo={$solIdEmpresa}",0,"u.id,u.nombre,u.persona,u.email","u inner join usuarios_grupo ug on u.id=ug.idUsuario");
    if (!isset($usrData[0]) && !isset($usrData["id"])) $usrData=$usrObj->getData("up.idPerfil=".SolicitudPago::PERFIL_GESTIONA,0,"u.id,u.nombre,u.persona,u.email","u inner join usuarios_perfiles up on u.id=up.idUsuario");
    if (isset($usrData["id"])) $usrData=[$usrData];
    if (!isset($usrData[0]["nombre"])) {
        echo getRespGralView($encabezado,"No se encuentra usuario para Proceso Contable",$solId,$solFolio,true,false);
        doclog("Error en Proceso Compras: Proceso Contable sin usuario","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"query"=>$query]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $usrIdList=array_column($usrData, "id");
    $tokList=$tokObj->creaAccion($solId,$usrIdList,"procesaConta",1);
    $template="correoSolPago4.html";
    $asunto="Solicitud $solFolio procesada por Compras";
    $baseKeyMap=["%RESPUESTA%"=>$respuesta,"isInteractive"=>"0"];
    $solObj->saveRecord(["id"=>$solId,"proceso"=>SolicitudPago::PROCESO_COMPRAS]);
    $solObj->firma($solId,"procesa");
    foreach ($usrData as $idx=>$usrElem) {
        if (!isset($tokList[$usrElem["id"]])) {
            echo getRespGralView($encabezado,"Usuario Contable Invalido",$solId,$solFolio,true,false);
            doclog("Error en Proceso Compras: Usuario Contable sin Token","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"usuario"=>$usrElem["nombre"]]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
    }
    DBi::commit();
    DBi::autocommit(true);
    global $gpoObj;
    if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
    $mailSettings=["domain"=>$gpoObj->getDomainKey($solData["idEmpresa"]??"")];
    foreach ($usrData as $idx=>$usrElem) {
        $tokContable=$tokList[$usrElem["id"]]["procesaConta"];
        $keyMap=["%PROCESAR%"=>$tokContable];
        $toObj=["address"=>$usrElem["email"],"name"=>replaceAccents($usrElem["persona"])];
        $usrObj->saveRecord(["id"=>$usrElem["id"],"banderas"=>new DBExpression("banderas|2")]);
        $mensaje=getSolFormaView($template,$solId,$solFolio,$baseKeyMap+$keyMap);
        sendMail($asunto,$mensaje,$fromObj,$toObj,null,null,$mailSettings); // (5) PROCESAMIENTO DE FACTURA. Compras a Contabilidad
    }
    echo getRespGralView($encabezado,"La solicitud ha sido procesada",$solId,$solFolio,false,$beInteractive);
} // FIN (5) PROCESAMIENTO DE FACTURA //
// (6) ASIGNACION A PAGO //
function isRdy2Pay() {
    return "readyToPayRequest"===($_REQUEST["action"]??"");
}
function doRdy2Pay() {
    global $tokObj, $query;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    $encabezado="SOLICITUD EN PROCESO CONTABLE";
    if(!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    if (isset($_GET["token"])) {
        $token=$_GET["token"];
        $beInteractive=true;
    } else {
        sessionInit();
        if (!hasUser()) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
            doclog("Proceso Contable sin sesión","solpago",$baseData+["line"=>__LINE__]);
            die();
        }
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esGestion=validaPerfil("Gestiona Pagos")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esGestion) {
            echo json_encode(["action"=>"redirect","mensaje"=>"No tiene permiso para procesar solicitud","errorMessage"=>"No tiene permiso para procesar solicitud"]);
            doclog("Error en Proceso Contable: Sin perfil Gestiona Pagos.","solpago",$baseData+["line"=>__LINE__,"username"=>$username,"usrId"=>$usrId]);
            die();
        }
        $solId=$_POST["solId"]??null;
        $modulo=$_POST["module"]??null;
        if (!isset($solId[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Solicitud no identificada","errorMessage"=>"Solicitud no identificada"]);
            doclog("Error en Proceso Contable: No hay solicitud","error",$baseData+["line"=>__LINE__]);
            die();
        }
        if (!isset($modulo[0])) {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            doclog("Error en Proceso Contable: No hay módulo","error",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            die();
        }
        if ($modulo!=="procesaConta") {
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            doclog("Error en Proceso Contable: El módulo debe ser 'procesaConta'","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo]);
            die();
        }
        $tokSrch=$tokObj->getData("refId=$solId and usrId=$usrId and modulo='$modulo'",0,"token");
        if (isset($tokSrch[0]["token"][0])) $token=$tokSrch[0]["token"];
        else if (isset($tokSrch["token"][0])) $token=$tokSrch["token"];
        else {
            echo getRespGralView($encabezado,"Usuario no autorizado para realizar proceso contable",$solId,"",true,false,true);
            doclog("Error en Proceso Contable: No hay token","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"usrId"=>$usrId]);
            die();
        }
        $beInteractive=false;
    }
    doclog("Antes de elegir Token","token",["token"=>$token]);
    $prevTokenData=$tokObj->obtenStatusData($token);
    DBi::autocommit(false);
    if ($tokObj->eligeToken($token)) {
        doclog("Token Elegido","token");
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esGestion=validaPerfil("Gestiona Pagos")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esGestion) {
            echo getRespGralView($encabezado,"No tiene permiso para procesar solicitud","","",true,false,true);
            doclog("Error en Proceso Contable. Sin perfil Gestiona Pagos","error",$baseData+["line"=>__LINE__,"usr"=>["id"=>$usrId,"name"=>$username],"token"=>$token]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $solId=$tokObj->data["refId"];
        $status=$tokObj->data["status"];
        $modulo=$tokObj->data["modulo"];
        $usos=$tokObj->data["usos"];
    } else {
        doclog("Token NO Elegido","token",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrno"=>DBi::$errno,"dberror"=>DBi::$error,"query"=>$query]);
        if (isset($tokObj->data["refId"])) {
            $solId=$tokObj->data["refId"];
            if (isset($tokObj->errorMessage)) {
                echo getRespGralView($encabezado,$tokObj->errorMessage,$solId,"",true,false,true);
                doclog("Error en Proceso Contable","error",$baseData+["line"=>__LINE__,"errorMessage"=>$tokObj->errorMessage]+$tokObj->data);
            } else {
                $tokErrMsg0=$tokObj->errors[0]??"";
                if (isset($tokObj->data["usos"]) && (+$tokObj->data["usos"])<=0) {
                    if (isset($tokObj->data["ocupado"])) {
                        $msg="procesada previamente";
                    } else $msg="cancelada";
                    echo getRespGralView($encabezado,"Solicitud $msg",$solId,"",true,false,true);
                    doclog("Error en Proceso Contable","solpago",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                } else {
                    echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
                    doclog("Error en Proceso Contable","error",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                }
            }
        } else if (isset($tokObj->errorMessage)) {
            echo getRespGralView($encabezado,$tokObj->errorMessage,"","",true,false,true);
            doclog("Error en Proceso Contable","error",$baseData+["line"=>__LINE__,"token"=>$token,"errorMessage"=>$tokObj->errorMessage]);
        } else {
            echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
            doclog("Error en Proceso Contable","error",$baseData+["line"=>__LINE__,"token"=>$token,"errors"=>$tokObj->errors]);
        }
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        echo getRespGralView($encabezado,"La solicitud ya no existe",$solId,"",true,false,true);
        doclog("Error en Proceso Contable: la solicitud no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solStatus=+$solData["status"];
    $encabezado="SOLICITUD $solFolio EN PROCESO CONTABLE";
    if ($solStatus&SolicitudPago::STATUS_CANCELADA) {
        echo getRespGralView($encabezado,"La solicitud esta cancelada",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: la solicitud esta cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solProceso=+$solData["proceso"];
    if ($solProceso<SolicitudPago::PROCESO_COMPRAS) {
        echo getRespGralView($encabezado,"La solicitud no fue procesada por Compras",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: la solicitud no fue procesada por Compras","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($solProceso>=SolicitudPago::PROCESO_CONTABLE) {
        echo getRespGralView($encabezado,"La solicitud ya fue procesada por Contabilidad",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: la solicitud ya fue procesada por Contabilidad","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $conFactura=(isset($solData["idFactura"]) && !empty($solData["idFactura"]));
    $conOrden=(isset($solData["idOrden"]) && !empty($solData["idOrden"]));
    if (!$conFactura && $conOrden) {
        echo getRespGralView($encabezado,"La solicitud no tiene factura",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: la solicitud no tiene factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $idFactura=$solData["idFactura"];
    global $invObj;
    $invData=$invObj->getData("id=$idFactura");
    if (!isset($invData[0])) {
        echo getRespGralView($encabezado,"La factura en la solicitud se ha eliminado",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: No se encontró la factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $invData=$invData[0];
    if (!isset($invData["status"][0])) {
        echo getRespGralView($encabezado,"La factura en la solicitud ha sido alterada",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: La factura no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["version"])||!in_array($invData["version"], ["3.3","4.0"])) {
        echo getRespGralView($encabezado,"La versión de factura $invData[version] no es válida",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["tipoComprobante"])||$invData["tipoComprobante"]!=="i") {
        echo getRespGralView($encabezado,"Se ingresó un comprobante que no es factura",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: El tipo comprobante no es ingreso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($invData["status"]==="Temporal" || !isset($invData["statusn"])) {
        echo getRespGralView($encabezado,"La solicitud no se registró correctamente",$solId,$solFolio,true,false);
        if ($invData["status"]==="Temporal") $mensaje="El status es Temporal";
        else $mensaje="El Status numerico no esta definido";
        doclog("Error en Proceso Contable: $mensaje","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $invStatusN=+$invData["statusn"];
    if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
        echo getRespGralView($encabezado,"La solicitud no se acepto correctamente",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: Factura No Aceptada","error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!isset($invData["estadoCFDI"])||$invData["estadoCFDI"]!=="Vigente") {
        echo getRespGralView($encabezado,"La factura ya no es vigente ante el SAT",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: La factura no esta Vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invData["estadoCFDI"]??"N/A"]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $ffolio=$invData["folio"];
    $fuuid=substr($invData["uuid"]??"", -10);
    global $usrObj;
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    if (isset($invData["nombreInternoPDF"][0])) {
        $usrData=$usrObj->getData("id=$solData[idUsuario]",0,"persona");
        require_once "clases/PDF.php";
        $invoicePath=$_SERVER['DOCUMENT_ROOT'];
        $pdfpath=$invData["ubicacion"];
        $pdfname=$invData["nombreInternoPDF"].".pdf";
        setlocale(LC_TIME,"es_MX.UTF-8","es_MX","esl");
        $formattedDate=strftime("%e %b, %Y");
        try {
            $fullname=$invoicePath.$pdfpath.$pdfname;
            $stampname=$invoicePath.$pdfpath."ST_".$pdfname;
            if (!file_exists($fullname)) {
                throw new Exception("No existe el archivo");
            }
            $pdfObj=PDF::getImprovedFile($fullname);
            if (!isset($pdfObj)) {
                $errmsg=isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado";
                doclog($errmsg,"error",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]+PDF::$errdata);
                throw new Exception($errmsg);
            }
            $stampMsg=$pdfObj->setStampFile($invoicePath."imagenes/icons/sello1.png");
            if (isset($stampMsg[0])) {
                echo getRespGralView($encabezado,"Sello no estampado, intente nuevamente",$solId,$solFolio,true,$beInteractive);
                doclog("Error en Proceso Contable: Error al marcar factura sellada 4","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"codProv"=>$invData["codigoProveedor"]??"","fullname"=>$fullname,"folio"=>$ffolio,"uuid"=>$fuuid,"msg"=>$stampMsg]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
            $pdfObj->addStamp($formattedDate, $usrData[0]["persona"], false);
            $pdfObj->saveFile($stampname);
            if (!$invObj->saveRecord(["id"=>$idFactura,"tieneSello"=>1])&&!empty(DBi::$errno)) {
                global $query;
                $lastQuery=$query;
                echo getRespGralView($encabezado,"Sello no estampado, consulte al administrador",$solId,$solFolio,true,$beInteractive);
                doclog("Error en Proceso Contable: Error al marcar factura sellada 5","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"codProv"=>$invData["codigoProveedor"]??"","fullname"=>$fullname,"folio"=>$ffolio,"uuid"=>$fuuid,"query"=>$lastQuery,"dberrors"=>DBi::$errors]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
        } catch (Exception $ex) {
                echo getRespGralView($encabezado,"Sello no estampado, consulte al administrador",$solId,$solFolio,true,$beInteractive);
                doclog("Error en Proceso Contable: Error al marcar factura sellada 6","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"codProv"=>$invData["codigoProveedor"]??"","fullname"=>$fullname,"folio"=>$ffolio,"uuid"=>$fuuid,"error"=>getErrorData($ex)]);
                DBi::rollback();
                DBi::autocommit(true);
                die();
        }
    }
    $fromObj=["address"=>getUser()->email,"name"=>replaceAccents(getUser()->persona)];
    $respuesta="<h2>La solicitud $solFolio ha sido procesada por ".$fromObj["name"]."</h2>";
    $idGrupo=$solData["idEmpresa"];
    $usrData=$usrObj->getData("ug.idGrupo=$idGrupo AND ug.idPerfil=".SolicitudPago::PERFIL_PAGA,0,"u.id,u.nombre,u.persona,u.email","u inner join usuarios_grupo ug on u.id=ug.idUsuario");
    if (isset($usrData["id"])) $usrData=[$usrData];
    if (!isset($usrData[0])) {
        doclog("No se encontró usuario con perfil para pagar solicitudes, se asigna admin","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"query"=>$query,"usrData"=>$usrData]);
        $usrData=[["id"=>"1","nombre"=>"admin","persona"=>"Administrador","email"=>"desarrollo@glama.com.mx"]];
    }
    if (!isset($usrData[0]["nombre"])) {
        echo getRespGralView($encabezado,"No se encuentra usuario de Finanzas",$solId,$solFolio,true,false);
        doclog("Error en Proceso Contable: Proceso Pago sin usuario","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"query"=>$query]);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $usrIdList=array_column($usrData, "id");
    $tokAList=$tokObj->creaAccion($solId,$usrIdList,"anexaComprobante",null);
    $tokPList=$tokObj->creaAccion($solId,$usrIdList,"procesaPago",1);
    $template="correoSolPago5.html";
    $asunto="Solicitud $solFolio procesada por Contabilidad";
    $baseKeyMap=["%RESPUESTA%"=>$respuesta,"isInteractive"=>"0"];
    $solObj->saveRecord(["id"=>$solId,"proceso"=>SolicitudPago::PROCESO_CONTABLE]);
    $solObj->firma($solId,"contable");
    foreach ($usrData as $idx=>$usrElem) {
        if (!isset($tokAList[$usrElem["id"]])) {
            echo getRespGralView($encabezado,"Usuario Finanzas Invalido",$solId,$solFolio,true,false);
            doclog("Error en Proceso Contable: Usuario Finanzas sin Token Anexar","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"usuario"=>$usrElem["nombre"],"usrList"=>$usrIdList,"query"=>$query]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!isset($tokPList[$usrElem["id"]])) {
            echo getRespGralView($encabezado,"Usuario Finanzas Invalido",$solId,$solFolio,true,false);
            doclog("Error en Proceso Contable: Usuario Finanzas sin Token Pagar","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"usuario"=>$usrElem["nombre"],"usrList"=>$usrIdList,"query"=>$query]);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
    }
    DBi::commit();
    DBi::autocommit(true);
    global $gpoObj;
    if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
    $mailSettings=["domain"=>$gpoObj->getDomainKey($idGrupo)];
    foreach ($usrData as $idx=>$usrElem) {
        $tokenAnexar=$tokAList[$usrElem["id"]]["anexaComprobante"];
        $tokenPagar=$tokPList[$usrElem["id"]]["procesaPago"];
        $keyMap=["%ANEXAR%"=>$tokenAnexar,"%PAGAR%"=>$tokenPagar];
        $toObj=["address"=>$usrElem["email"],"name"=>replaceAccents($usrElem["persona"])];
        $usrObj->saveRecord(["id"=>$usrElem["id"],"banderas"=>new DBExpression("banderas|2")]);
        $mensaje=getSolFormaView($template,$solId,$solFolio,$baseKeyMap+$keyMap);
        sendMail($asunto,$mensaje,$fromObj,$toObj,null,null,$mailSettings); // (6) ASIGNACION A PAGO. Contabilidad a Finanzas
    }
    echo getRespGralView($encabezado,"La solicitud ha sido procesada",$solId,$solFolio,false,$beInteractive);
} // FIN (6) ASIGNACION A PAGO //
// (7) ANEXAR COMPROBANTE DE PAGO //
function isAttachPaymProof() {
    return "requestProofOfPayment"===($_REQUEST["action"]??"");
}
function doAttachPaymProof() {
    global $tokObj, $query;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    $encabezado="SOLICITUD EN COMPROBACION DE PAGO";
    if(!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    if (isset($_GET["token"])) {
        $token=$_GET["token"];
        $beInteractive=true;
    } else {
        sessionInit();
        if (!hasUser()) {
            doclog("Error al Anexar Comprobante: Sin sesión","solpago",$baseData+["line"=>__LINE__]);
            echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
            die();
        }
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esFinanzas=validaPerfil("Realiza Pagos")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esFinanzas) {
            doclog("Error al Anexar Comprobante: Sin perfil Realiza Pagos.","solpago",$baseData+["line"=>__LINE__,"username"=>$username,"usrId"=>$usrId]);
            echo json_encode(["action"=>"redirect","mensaje"=>"No tiene permiso para procesar solicitud","errorMessage"=>"No tiene permiso para procesar solicitud"]);
            die();
        }
        $solId=$_POST["solId"]??null;
        $modulo=$_POST["module"]??null;
        if (!isset($solId[0])) {
            doclog("Error al Anexar Comprobante: No hay solicitud","solpago",$baseData+["line"=>__LINE__]);
            echo json_encode(["action"=>"redirect","mensaje"=>"Solicitud no identificada","errorMessage"=>"Solicitud no identificada"]);
            die();
        }
        if (!isset($modulo[0])) {
            doclog("Error al Anexar Comprobante: No hay módulo","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            die();
        }
        $tokSrch=$tokObj->getData("refId=$solId and usrId=$usrId and modulo='$modulo'",0,"token");
        if (isset($tokSrch[0]["token"][0])) $token=$tokSrch[0]["token"];
        else if (isset($tokSrch["token"][0])) $token=$tokSrch["token"];
        else {
            doclog("Error al Anexar Comprobante: No hay token","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"usrId"=>$usrId]);
            echo getRespGralView($encabezado,"Usuario no autorizado para anexar comprobante de pago",$solId,"",true,false,true);
            die();
        }
        $beInteractive=false;
    }
    doclog("Anexar Comprobante: Antes de elegir Token","token",["token"=>$token]);
    $prevTokenData=$tokObj->obtenStatusData($token);
    DBi::autocommit(false);
    if ($tokObj->eligeToken($token,"ocupado",true)) {
        doclog("Anexar Comprobante: Token Elegido","token",$baseData+["line"=>__LINE__]);
        if (!isset($esFinanzas)) {
            $esAdmin=validaPerfil("Administrador");
            $esSistemas=validaPerfil("Sistemas")||$esAdmin;
            $esFinanzas=validaPerfil("Realiza Pagos")||$esSistemas;
            $usrId=getUser()->id;
            $username=getUser()->nombre;
            if (!$esFinanzas) {
                doclog("Error al Anexar Comprobante: Sin perfil Realiza Pagos","error",$baseData+["line"=>__LINE__,"usr"=>["id"=>$usrId,"name"=>$username],"token"=>$token]);
                echo getRespGralView($encabezado,"No tiene permiso para procesar solicitud","","",true,false,true);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
        }
        $solId=$tokObj->data["refId"];
        $status=$tokObj->data["status"];
        $modulo=$tokObj->data["modulo"];
        $usos=$tokObj->data["usos"];
    } else {
        doclog("Error al Anexar Comprobante: Token NO Elegido","token",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrno"=>DBi::$errno,"dberror"=>DBi::$error,"query"=>$query]);
        if (isset($tokObj->data["refId"])) {
            $solId=$tokObj->data["refId"];
            if (isset($tokObj->errorMessage)) {
                doclog("Error al Anexar Comprobante: Token no elegido con error","solpago",$baseData+["line"=>__LINE__,"errorMessage"=>$tokObj->errorMessage]+$tokObj->data);
                echo getRespGralView($encabezado,$tokObj->errorMessage,$solId,"",true,false,true);
            } else {
                $tokErrMsg0=$tokObj->errors[0]??"";
                if (isset($tokObj->data["usos"]) && (+$tokObj->data["usos"])<=0) {
                    if (isset($tokObj->data["ocupado"])) {
                        $msg="Comprobante anexado previamente";
                    } else $msg="Comprobación de pago cancelada";
                    doclog("Error al Anexar Comprobante: Token no elegido sin usos","solpago",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                    echo getRespGralView($encabezado,$msg,$solId,"",true,false,true);
                } else {
                    doclog("Error al Anexar Comprobante: Token no elegido con refId sin razon","solpago",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                    echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
                }
            }
        } else if (isset($tokObj->errorMessage)) {
            doclog("Error al Anexar Comprobante: Token no elegido sin refId","solpago",$baseData+["line"=>__LINE__,"token"=>$token,"errorMessage"=>$tokObj->errorMessage]);
            echo getRespGralView($encabezado,$tokObj->errorMessage,"","",true,false,true);
        } else {
            doclog("Error al Anexar Comprobante: Token no elegido sin refId y sin razon","solpago",$baseData+["line"=>__LINE__,"token"=>$token,"errors"=>$tokObj->errors]);
            echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
        }
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        doclog("Error al Anexar Comprobante: la solicitud no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud ya no existe",$solId,"",true,false,true);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solStatus=+$solData["status"];
    $encabezado="SOLICITUD $solFolio EN COMPROBACION DE PAGO";
    if ($solStatus&SolicitudPago::STATUS_CANCELADA) {
        doclog("Error al Anexar Comprobante: la solicitud esta cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud esta cancelada",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solProceso=+$solData["proceso"];
    if ($solProceso>=SolicitudPago::PROCESO_PAGADA) {
        doclog("Error al Anexar Comprobante: solicitud ya pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud ya esta pagada",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $conFactura=(isset($solData["idFactura"]) && !empty($solData["idFactura"]));
    $conOrden=(isset($solData["idOrden"]) && !empty($solData["idOrden"]));
    $conContra=(isset($solData["idContrarrecibo"]) && !empty($solData["idContrarrecibo"]));
    if ($conFactura) {
        $idFactura=$solData["idFactura"];
        if ($solProceso<SolicitudPago::PROCESO_CONTABLE) {
            doclog("Error al Anexar Comprobante: la solicitud no está lista para Pago","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            echo getRespGralView($encabezado,"La solicitud no está lista para Pago",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        global $invObj; $invData=$invObj->getData("id=$idFactura");
        if (!isset($invData[0])) {
            doclog("Error al Anexar Comprobante: No se encontró la factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La factura en la solicitud se ha eliminado",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $invData=$invData[0];
        if (!isset($invData["status"][0])) {
            doclog("Error al Anexar Comprobante: La factura no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La factura en la solicitud ha sido alterada",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        if (!isset($invData["version"])||!in_array($invData["version"], ["3.3","4.0"])) {
            doclog("Error al Anexar Comprobante: Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]??"N/A"]);
            echo getRespGralView($encabezado,"La versión de factura $invData[version] no es válida",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        if (!isset($invData["tipoComprobante"])||$invData["tipoComprobante"]!=="i") {
            doclog("Error al Anexar Comprobante: El tipo comprobante no es ingreso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]??"N/A"]);
            echo getRespGralView($encabezado,"Se ingresó un comprobante que no es factura",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        if ($invData["status"]==="Temporal" || !isset($invData["statusn"])) {
            if ($invData["status"]==="Temporal") $mensaje="El status es Temporal";
            else $mensaje="El Status numerico no esta definido";
            doclog("Error al Anexar Comprobante: $mensaje","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La solicitud no se registró correctamente",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $invStatusN=+$invData["statusn"];
        if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
            doclog("Error al Anexar Comprobante: Factura No Aceptada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La solicitud no se acepto correctamente",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        if (!isset($invData["estadoCFDI"])||$invData["estadoCFDI"]!=="Vigente") {
            doclog("Error al Anexar Comprobante: La factura no esta Vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invData["estadoCFDI"]??"N/A"]);
            echo getRespGralView($encabezado,"La factura ya no es vigente ante el SAT",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $cpId=$idFactura; $cpPlace="f"; $tmpName=$invData["nombreInternoPDF"];
        if (!isset($tmpName[0])) $tmpName=$invData["nombreInterno"];
        $cpName="CP_".$tmpName;
    } else if ($conOrden) {
        $ordId=$solData["idOrden"];
        if (!isset($ordObj)) { require_once "clases/OrdenesCompra.php"; $ordObj=new OrdenesCompra(); }
        $ordData = $ordObj->getData("id=$ordId");
        if (!isset($ordData[0])) {
            doclog("Error al Anexar Comprobante: La orden de compra no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra no existe",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $ordData=$ordData[0];
        $stt=$ordData["status"]??"";
        if (!isset($stt[0])) {
            doclog("Error al Anexar Comprobante: La orden no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra no está disponible para pago",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $stt=+$stt;
        if ($stt<0) {
            doclog("Error al Anexar Comprobante: La orden está cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra está cancelada",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        if ($stt<2) {
            doclog("Error al Anexar Comprobante: La orden no está autorizada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra no se ha autorizado",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        if ($stt>=4) {
            doclog("Error al Anexar Comprobante: La orden ya está pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra ya está pagada",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $cpId=$ordId; $cpPlace="o"; $cpName="CP_".$ordData["nombreArchivo"];
    } else if ($conContra) {
        $ctrId=$solData["idContrarrecibo"];
        if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj=new Contrarrecibos(); }
        $ctrData = $ctrObj->getData("id=$ctrId");
        if (!isset($ctrData[0])) {
            doclog("Error al Anexar Comprobante: El contra recibo no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId]);
            echo getRespGralView($encabezado,"El contra recibo no existe",$solId,$solFolio,true,false);
            DBi::rollback(); DBi::autocommit(true); die(); }
        $ctrData=$ctrData[0];
        $alias=$ctrData["aliasGrupo"];
        $fRev=$ctrData["fechaRevision"];
        $yrmon=substr($fRev, 0, 4)."/".substr($fRev, 5, 2);
        $cpId=$ctrId; $cpPlace="c"; $cpPath="archivos/{$alias}/{$yrmon}/"; $cpName="CP_CTR_".$ctrData["folio"];
    } else {
        doclog("Error al Anexar Comprobante: La solicitud no tiene factura ni orden","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud no tiene factura ni orden",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    // ToDo_SOLICITUD: No cambiar status aqui. Hay que habilitar captura de archivo pdf y ya que se tenga se cambia el status y se firma la solicitud como anexa:
    $cpPathHtml=isset($cpPath[0])?"<input type='hidden' id='cpPath' value='$cpPath'>":"";
    $cpPathJS=isset($cpPath[0])?"fd.append('path','$cpPath');":"";
    $hostName=$_SERVER["HTTP_ORIGIN"];
    $htmlMessage="<SCRIPT type=\"text/javascript\">function ebyid(id){return document.getElementById(id);}function ekil(el){el.parentNode.removeChild(el);}function ewip(el,cn){if(cn){const lc=el.getElementsByClassName(cn);if(Array.from)Array.from(lc).forEach(se=>ekil(se));else[].forEach.call(lc,se=>ekil(se));}else while(el.lastChild)el.removeChild(el.lastChild);}function ajx(){if(window.XMLHttpRequest)try{return new XMLHttpRequest;}catch(e){}if(window.ActiveXObject){const axm=['Microsoft.XMLHTTP','Msxml2.XMLHTTP'];axm.forEach(function(ai){try{return new ActiveXObject(ai);}catch(e){}});}return false;}function nwPg(par,text,cn=null,id=null){const np=document.createElement('P');if(cn)np.className=cn;if(id)np.id=id;np.textContent=text;par.appendChild(np);}
function checkChange(){
console.log('INI function consultas.Facturas.checkChange');
const msj=ebyid('msj');
ewip(msj);
const cp=ebyid('cp');
if(cp.files.length==1){
    let hasError=false;
    let fd=cp.files[0];
    if(fd.type!=='application/pdf'){nwPg(msj,'El archivo \''+fd.name+'\' no tiene el formato requerido (PDF)');hasError=true;}
    if(fd.size>2097000){nwPg(msj,'El archivo \''+fd.name+'\' excede el tamaño máximo permitido (2MB)');hasError=true;}
    //if (hasError) setTimeout(overlayClose,1000);
    //else snd();

}
}function snd(){
console.log('INI function consultas.Facturas.snd');
const msj=ebyid('msj');
ewip(msj,'snd');
const cp=ebyid('cp');
if(cp.files.length==0)nwPg(msj,'No ha seleccionado un archivo','snd');
else if(msj.firstChild)nwPg(msj,'Solo se puede enviar un archivo válido','snd');
else{
    console.log('PREPARING DATA');
    nwPg(msj,'Enviando...','snd');
    const fl=cp.files[0];
    const xhp=ajx();
    const fd=new FormData();
    fd.append('action','docProofOfPayment');
    fd.append('id',$cpId);
    fd.append('solId',$solId);
    fd.append('type','$cpPlace');
    fd.append('name','$cpName');{$cpPathJS}
    fd.append('attach',fl,fl.name);
    const url='/invoice/consultas/Facturas.php'; // postService
    console.log('OPENING PORT');
    xhp.open('POST',url,true);
    xhp.onload=()=>{
        try{
            jobj=JSON.parse(xhp.responseText);
            console.log('SUCCESS: '+xhp.responseText);
            ewip(msj,'snd');
            nwPg(msj,'ARCHIVO RECIBIDO!','snd');
            const divDocs=ebyid(jobj.divname);
            if (divDocs) {
                ewip(divDocs,'cpDoc');
                const cD=document.createElement('A');
                cD.className='cpDoc';
                cD.href='{$hostName}'+jobj.path+jobj.name;
                cD.target='archivo';
                const iD=document.createElement('IMG');
                iD.src='{$hostName}/invoice/imagenes/icons/invChk200.png';
                iD.width='20';
                iD.height='20';
                iD.title='COMPROBANTE PAGO';
                iD.style.filter='grayscale(1) brightness(0.8) contrast(2.5)';
                cD.appendChild(iD);
                divDocs.appendChild(cD);
            }
        }catch(ex){
            console.log(ex,xhp.responseText);
            ewip(msj,'snd');
            nwPg(msj,'ERROR EN RESPUESTA DEL SERVIDOR','snd');
        }
    };
    xhp.send(fd);
    xhp.onerror=()=>{console.log('ERROR: '+xhp.responseText,xhp);};
    xhp.onabort=()=>{console.log('ABORT!',xhp);};
    xhp.ontimeout=()=>{console.log('TIMEOUT',xhp);};
    console.log('REQUEST SENT');
}
}</SCRIPT><H2>Anexar Comprobante de Pago</H2><DIV><input type='file' name='cp' id='cp' accept='.pdf' onchange='checkChange();'><input type='hidden' id='cpSolId' value='$solId'><input type='hidden' id='cpId' value='$cpId'><input type='hidden' id='cpPlace' value='$cpPlace'><input type='hidden' id='cpName' value='$cpName'>{$cpPathHtml}&nbsp;<input type='button' onclick='snd();' value='Enviar'></DIV><DIV id='msj'></DIV><HR>"; // <img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const cpEl=ebyid('cp');console.log('PDF File Selection');cladd('dialogbox', 'invisible');cpEl.click();cpEl.value='1';setTimeout(()=>{clrem('dialogbox','invisible');console.log('CP value='+ebyid('cp').value);}, 3000);ekil(this);\">
    //$solObj->saveRecord(["id"=>$solId,"proceso"=>SolicitudPago::PROCESO_ANEXADA]);
    //$solObj->firma($solId,"anexa");
    DBi::commit();
    DBi::autocommit(true);
    echo getRespGralView($encabezado,$htmlMessage,$solId,$solFolio,false,$beInteractive,false);
}
// FIN (7) ANEXAR COMPROBANTE DE PAGO //
// (7B) ANEXAR COMPROBANTE DE PAGO //
function isAttachProofDoc() {
    return "docProofOfPayment"===($_REQUEST["action"]??"");
}
function doAttachProofDoc() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    sessionInit();
    if(!isset($_FILES["attach"])) {
        doclog("AttachProofDoc: Sin archivo","solpago",$baseData+["line"=>__LINE__]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: NO FILE', 'code'=>1337)));
    }
    $type=$_POST["type"]??"";
    if (!isset($type[0])) {
        doclog("AttachProofDoc: Sin tipo de cfdi","solpago",$baseData+["line"=>__LINE__]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: NO TYPE', 'code'=>1338)));
    }
    $log="type='$type'";
    if ($type[0]==="f") { global $invObj; $log.=",table='Facturas'";
        $tabObj=$invObj; $fileField="comprobantePagoPDF";
        $pathField="ubicacion"; $divName="invDocs";
    } else if ($type[0]==="o") { global $ordObj; if (!isset($ordObj)) { require_once "clases/OrdenesCompra.php"; $ordObj=new OrdenesCompra(); } $log.=",table='OrdenesCompra'";
        $tabObj=$ordObj; $fileField="comprobantePago";
        $pathField="rutaArchivo"; $divName="ordDocs";
    } else if ($type[0]==="c") { global $ctrObj; if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj=new Contrarrecibos(); } $log.=",table='Contrarrecibos'";
        $tabObj=$ctrObj; $fileField="comprobantePago";
        $pathField=""; $divName="ctrDocs"; $pathName=$_POST["path"]??"";
    } else {
        doclog("AttachProofDoc: Tipo de cfdi invalido","solpago",$baseData+["line"=>__LINE__,"log"=>$log]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: WRONG TYPE', 'code'=>1339, 'log'=>$log)));
    }
    $solId=$_POST["solId"]??"";
    if (!isset($solId[0])) {
        doclog("AttachProofDoc: Sin solId","solpago",$baseData+["line"=>__LINE__,"log"=>$log]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: NO REQUEST', 'code'=>1340, 'log'=>$log)));
    }
    $id=$_POST["id"]??"";
    if (!isset($id[0])) {
        doclog("AttachProofDoc: Sin id de cfdi","solpago",$baseData+["line"=>__LINE__,"log"=>$log]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: NO ID', 'code'=>1341, 'log'=>$log)));
    }
    global $query;
    if (isset($pathField[0])) {
        $tabData=$tabObj->getData("id=$id",0,$pathField);
        if (!isset($tabData[0])) {
            doclog("AttachProofDoc: Sin Datos de tabla","solpago",$baseData+["line"=>__LINE__,"log"=>$log,"query"=>$query]+$_POST+$_FILES);
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array('message'=>'ERROR: WRONG ID', 'code'=>1342, 'log'=>$log, 'query'=>$query)));
        }
        $pathName=$tabData[0][$pathField];
    }
    $newName=$_POST["name"]??"";
    if (!isset($newName[0])) {
        doclog("AttachProofDoc: Sin nuevo nombre","solpago",$baseData+["line"=>__LINE__,"log"=>$log]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: NO NAME', 'code'=>1343, 'log'=>$log)));
    }
    $file=$_FILES["attach"]; $fileName=$file["name"]; $tempName=$file["tmp_name"];
    $fileType=$file["type"]; $fileSize=$file["size"]; $fileError=$file["error"];
    if ($fileError) {
        doclog("AttachProofDoc: Error de archivo","solpago",$baseData+["line"=>__LINE__,"log"=>$log]+$_POST+$_FILES);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message'=>'ERROR: FILE ERROR', 'code'=>1344, 'error'=>$fileError)));
    }
    chmod($tempName, 0777);
    $fieldarray = ["id"=>$id, $fileField=>$newName];
    global $query; $query="";
    if ($tabObj->saveRecord($fieldarray)||empty(DBi::$errno)) {
        doclog("AttachProofDoc: CFDI guardado o sin cambios","solpago",$baseData+["line"=>__LINE__,"log"=>$log,"query"=>$query]+$_POST+$_FILES);
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        $http_origin = $_SERVER["HTTP_ORIGIN"];
        $fullName=($pathName??"").$newName.".pdf";
        $log="upload new $document_root.$fullName";
        if(file_exists($document_root.$fullName)) {
            $log.=":EXISTS!";
            if(chmod($document_root.$fullName,0755))
                $log.=",CHMOD";
            //Change the file permissions if allowed
            if(unlink($document_root.$fullName))
                $log.=",UNLINK";
            //remove the file
        }else $log.=":NOTFOUND!";
        if(move_uploaded_file($tempName, $document_root.$fullName)===false) {
            $log.=",NOTMOVED!";
            $lastError=error_get_last();
            doclog("AttachProofDoc: ERROR: File NOT move-uploaded","solpago",$baseData+["line"=>__LINE__,"log"=>$log,"lastError"=>$lastError,"fullName"=>$fullName]+$_POST+$_FILES);
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array('message'=>'ERROR: LOAD ERROR', 'code'=>1345, 'error'=>$lastError, 'fullName'=>$fullName)));
            //echo $http_origin.$fullName;
        } else {
            $log.=",MOVED!";
            global $solObj;
            if (!isset($solObj)) {
                require_once "clases/SolicitudPago.php";
                $solObj = new SolicitudPago();
            }
            if (!$solObj->saveRecord(["id"=>$solId,"proceso"=>SolicitudPago::PROCESO_ANEXADA]) && !empty(DBi::$errno)) {
                $log.=",NOTSAVEDSOL! QRY: $query, ERR ".DBi::$errno.": ".DBi::$error;
                doclog("AttachProofDoc: ERROR: Solicitud no guardada","solpago",$baseData+["line"=>__LINE__,"log"=>$log,"DBOErrors"=>$tabObj->errors,"DBiErrors"=>DBi::$errors]+$_POST+$_FILES);
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(array('message'=>'ERROR: NOSAVE REQUEST', 'code'=>1346, 'errno'=>DBi::$errno, 'error'=>DBi::$error, 'fullName'=>$fullName, 'query'=>$query)));
                //echo $http_origin.$fullName;
            }

            $solObj->firma($solId,"anexa");
        }
    } else {
        $log="QRY: $query, ERR ".DBi::$errno.": ".DBi::$error;
        doclog("AttachProofDoc: ERROR: CFDI no guardado","solpago",$baseData+["line"=>__LINE__,"log"=>$log,"DBOErrors"=>$tabObj->errors,"DBiErrors"=>DBi::$errors]+$_POST+$_FILES);
    }
    doclog("AttachProofDoc: Comprobante de pago de solicitud asignado y guardado","solpago",$baseData+["line"=>__LINE__,"log"=>$log]+$_POST+$_FILES);
    header('Content-Type: application/json');
    echo json_encode(["name"=>$newName.".pdf","path"=>$pathName,"type"=>$fileType,"size"=>$fileSize,"divname"=>$divName,'log'=>$log]);
}
// FIN (7B) ANEXAR COMPROBANTE DE PAGO //
// (8) PAGO DE SOLICITUD //
function isPayingRequest() {
    return "responseSetPaidRequest"===($_REQUEST["action"]??"");
}
function doPayingRequest() {
    global $tokObj, $query;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    $encabezado="SOLICITUD PROGRAMADA PARA PAGO";
    if (isset($_POST["ignore"])/*&&$_POST["ignore"]===true*/) {
        if (isset($_POST["solId"][0])) {
            $solId=$_POST["solId"];
            $solFolio="ID_".$solId;
        } else {
            $solId="NO_ID";
            $solFolio="NO_FOLIO";
        }
        doclog("PAYINGREQUEST Error: Ignorada","solpago",$baseData+["line"=>__LINE__]+$_POST);
        echo getRespGralView($encabezado,"La solicitud ha sido ignorada",$solId,$solFolio,false,false,false);
        die();
    }
    if(!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    if (isset($_GET["token"])) {
        $token=$_GET["token"];
        $beInteractive=true;
    } else {
        sessionInit();
        if (!hasUser()) {
            doclog("PAYINGREQUEST Error: Sesión caducada","solpago",$baseData+["line"=>__LINE__]);
            echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente","errorMessage"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
            die();
        }
        $esAdmin=validaPerfil("Administrador");
        $esSistemas=validaPerfil("Sistemas")||$esAdmin;
        $esFinanzas=validaPerfil("Realiza Pagos")||$esSistemas;
        $usrId=getUser()->id;
        $username=getUser()->nombre;
        if (!$esFinanzas) {
            doclog("PAYINGREQUEST Error: Sin perfil Realiza Pagos.","solpago",$baseData+["line"=>__LINE__,"username"=>$username,"usrId"=>$usrId]);
            echo json_encode(["action"=>"redirect","mensaje"=>"No tiene permiso para procesar solicitud","errorMessage"=>"No tiene permiso para procesar solicitud"]);
            die();
        }
        $solId=$_POST["solId"]??null;
        $modulo=$_POST["module"]??null;
        if (!isset($solId[0])) {
            doclog("PAYINGREQUEST Error: No hay solicitud","solpago",$baseData+["line"=>__LINE__]);
            echo json_encode(["action"=>"redirect","mensaje"=>"Solicitud no identificada","errorMessage"=>"Solicitud no identificada"]);
            die();
        }
        if (!isset($modulo[0])) {
            doclog("PAYINGREQUEST Error: No hay módulo","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            echo json_encode(["action"=>"redirect","mensaje"=>"Módulo de solicitud no identificado","errorMessage"=>"Módulo de solicitud no identificado"]);
            die();
        }
        $tokSrch=$tokObj->getData("refId=$solId and usrId=$usrId and modulo='$modulo'",0,"token");
        if (isset($tokSrch[0]["token"][0])) $token=$tokSrch[0]["token"];
        else if (isset($tokSrch["token"][0])) $token=$tokSrch["token"];
        else {
            doclog("PAYINGREQUEST Error: No hay token","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"modulo"=>$modulo,"usrId"=>$usrId]);
            echo getRespGralView($encabezado,"Usuario no autorizado para realizar pagos",$solId,"",true,false,true);
            die();
        }
        $beInteractive=false;
    }
    doclog("PAYINGREQUEST: Antes de elegir Token","token",["token"=>$token]);
    $prevTokenData=$tokObj->obtenStatusData($token);
    DBi::autocommit(false);
    if ($tokObj->eligeToken($token)) {
        doclog("PAYINGREQUEST: Token Elegido","token");
        if (!isset($esFinanzas)) {
            $esAdmin=validaPerfil("Administrador");
            $esSistemas=validaPerfil("Sistemas")||$esAdmin;
            $esFinanzas=validaPerfil("Realiza Pagos")||$esSistemas;
            $usrId=getUser()->id;
            $username=getUser()->nombre;
            if (!$esFinanzas) {
                doclog("PAYINGREQUEST Error: Sin perfil Realiza Pagos","error",$baseData+["line"=>__LINE__,"usr"=>["id"=>$usrId,"name"=>$username],"token"=>$token]);
                echo getRespGralView($encabezado,"No tiene permiso para realizar pagos","","",true,false,true);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            }
        }
        $solId=$tokObj->data["refId"];
        $status=$tokObj->data["status"];
        $modulo=$tokObj->data["modulo"];
        $usos=$tokObj->data["usos"];
    } else {
        doclog("PAYINGREQUEST Error: Token NO Elegido","token",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrno"=>DBi::$errno,"dberror"=>DBi::$error,"query"=>$query]);
        if (isset($tokObj->data["refId"])) {
            $solId=$tokObj->data["refId"];
            if (isset($tokObj->errorMessage)) {
                doclog("PAYINGREQUEST Error: Token no elegido con error","solpago",$baseData+["line"=>__LINE__,"errorMessage"=>$tokObj->errorMessage]+$tokObj->data);
                echo getRespGralView($encabezado,$tokObj->errorMessage,$solId,"",true,false,true);
            } else {
                $tokErrMsg0=$tokObj->errors[0]??"";
                if (isset($tokObj->data["usos"]) && (+$tokObj->data["usos"])<=0) {
                    if (isset($tokObj->data["ocupado"])) {
                        $msg="La Solicitud ya está pagada";
                    } else $msg="Solicitud cancelada";
                    doclog("PAYINGREQUEST Error: Token no elegido por USADO","solpago",$baseData+["line"=>__LINE__,"msg"=>$msg,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                    echo getRespGralView($encabezado,$msg,$solId,"",true,false,true);
                } else {
                    doclog("PAYINGREQUEST Error: Token no elegido, sin razón","solpago",$baseData+["line"=>__LINE__,"errors"=>$tokObj->errors,"dberrors"=>DBi::$errors]+$tokObj->data);
                    echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
                }
            }
        } else if (isset($tokObj->errorMessage)) {
            doclog("PAYINGREQUEST Error: Token sin id de solicitud con error","solpago",$baseData+["line"=>__LINE__,"token"=>$token,"errorMessage"=>$tokObj->errorMessage]);
            echo getRespGralView($encabezado,$tokObj->errorMessage,"","",true,false,true);
        } else {
            doclog("PAYINGREQUEST Error: Token sin id de solicitud sin error","solpago",$baseData+["line"=>__LINE__,"token"=>$token,"errors"=>$tokObj->errors]);
            echo getRespGralView($encabezado,"Accion no permitida","","",true,false,true);
        }
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        doclog("PAYINGREQUEST Error: la solicitud no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud ya no existe",$solId,"",true,false,true);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solStatus=+$solData["status"];
    $solIdUsuario=$solData["idUsuario"];
    $encabezado="SOLICITUD $solFolio PROGRAMADA PARA PAGO";
    if ($solStatus&SolicitudPago::STATUS_CANCELADA) {
        doclog("PAYINGREQUEST Error: la solicitud esta cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud esta cancelada",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($solStatus&SolicitudPago::STATUS_PAGADA) {
        doclog("PAYINGREQUEST Error: la solicitud ya esta pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud ya esta pagada",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    $conFactura=(isset($solData["idFactura"]) && !empty($solData["idFactura"]));
    $conOrden=(isset($solData["idOrden"]) && !empty($solData["idOrden"]));
    $conContra=(isset($solData["idContrarrecibo"]) && !empty($solData["idContrarrecibo"]));
    $solProceso=+$solData["proceso"];
    if ($conFactura && $solProceso<SolicitudPago::PROCESO_CONTABLE) {
        doclog("PAYINGREQUEST Error: la solicitud no esta lista para Pago","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"proceso"=>$solProceso]);
        echo getRespGralView($encabezado,"La solicitud no esta lista para Pago",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($solProceso<SolicitudPago::PROCESO_ANEXADA) {
        doclog("PAYINGREQUEST Error: solicitud sin Comprobante de Pago","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"proceso"=>$solProceso]);
        echo getRespGralView($encabezado,"La solicitud no tiene Comprobante de Pago",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($solProceso>=SolicitudPago::PROCESO_PAGADA) {
        doclog("PAYINGREQUEST Error: solicitud ya pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud ya esta pagada",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if ($conFactura) {
        $idFactura=$solData["idFactura"];
        global $invObj;
        $invData=$invObj->getData("id=$idFactura");
        if (!isset($invData[0])) {
            doclog("PAYINGREQUEST Error: No se encontró la factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La factura en la solicitud se ha eliminado",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $invData=$invData[0];
        if (!isset($invData["status"][0])) {
            doclog("PAYINGREQUEST Error: La factura no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La factura en la solicitud ha sido alterada",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!isset($invData["version"])||!in_array($invData["version"], ["3.3","4.0"])) {
            doclog("PAYINGREQUEST Error: Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]??"N/A"]);
            echo getRespGralView($encabezado,"La versión de factura $invData[version] no es válida",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!isset($invData["tipoComprobante"])||$invData["tipoComprobante"]!=="i") {
            doclog("PAYINGREQUEST Error: El tipo comprobante no es ingreso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]??"N/A"]);
            echo getRespGralView($encabezado,"Se ingresó un comprobante que no es factura",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if ($invData["status"]==="Temporal" || !isset($invData["statusn"])) {
            if ($invData["status"]==="Temporal") $mensaje="El status es Temporal";
            else $mensaje="El Status numerico no esta definido";
            doclog("PAYINGREQUEST Error: $mensaje","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La solicitud no se registró correctamente",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $invStatusN=+$invData["statusn"];
        if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
            doclog("PAYINGREQUEST Error: Factura No Aceptada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
            echo getRespGralView($encabezado,"La solicitud no se acepto correctamente",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if (!isset($invData["estadoCFDI"])||$invData["estadoCFDI"]!=="Vigente") {
            doclog("PAYINGREQUEST Error: La factura no esta Vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invData["estadoCFDI"]??"N/A"]);
            echo getRespGralView($encabezado,"La factura ya no es vigente ante el SAT",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $codigoProveedor=$invData["codigoProveedor"];
        if (!$invObj->saveRecord(["id"=>$idFactura,"statusn"=>new DBExpression("statusn|".Facturas::STATUS_PAGADO),"status"=>"Pagado"])) {
            $svQry=$query; $errn=DBi::getErrno();
            if ($errn>0) {
                $errs=DBi::$errors;
                doclog("PAYINGREQUEST Error: La factura no pudo guardarse","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"query"=>$svQry,"dberrors"=>$errs]);
                echo getRespGralView($encabezado,"Error al guardar la factura",$solId,$solFolio,true,$beInteractive);
                DBi::rollback();
                DBi::autocommit(true);
                die();
            } // else // LA FACTURA YA ESTA MARCADA COMO PAGADA
        } else {
            global $prcObj;
            if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
            $prcObj->cambioFactura($idFactura, "Pagado", $username, false, "Solicitud $solFolio con Factura");
        }
    } else if ($conOrden) {
        $ordId=$solData["idOrden"];
        global $ordObj;
        if (!isset($ordObj)) {
            require_once "clases/OrdenesCompra.php";
            $ordObj=new OrdenesCompra();
        }
        $ordData = $ordObj->getData("id=$ordId");
        if (!isset($ordData[0])) {
            doclog("PAYINGREQUEST Error: La orden de compra no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra no existe",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $ordData=$ordData[0];
        $stt=$ordData["status"]??"";
        if (!isset($stt[0])) {
            doclog("PAYINGREQUEST Error: La orden no tiene status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra no está disponible para pago",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $stt=+$stt;
        if ($stt<0) {
            doclog("PAYINGREQUEST Error: La orden está cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra está cancelada",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if ($stt<2) {
            doclog("PAYINGREQUEST Error: La orden no está autorizada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra no se ha autorizado",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        if ($stt>=4) {
            doclog("PAYINGREQUEST Error: La orden ya está pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId]);
            echo getRespGralView($encabezado,"La orden de compra ya está pagada",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $idProveedor=$ordData["idProveedor"];
        global $prvObj;
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj = new Proveedores();
        }
        $prvData=$prvObj->getData("id=$idProveedor",0,"codigo");
        $codigoProveedor=$prvData[0]["codigo"]??"";
        if (!$ordObj->saveRecord(["id"=>$ordId,"status"=>new DBExpression("status|4")])) {
            doclog("PAYINGREQUEST Error: La orden no pudo guardarse","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$ordId,"query"=>$query,"dberrors"=>DBi::$errors]);
            //errlog($ordObj->log,"log");
            echo getRespGralView($encabezado,"Error al guardar orden de compra",$solId,$solFolio,true,$beInteractive);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
    } else if ($conContra) {
        $ctrId=$solData["idContrarrecibo"];
        global $ctrObj;
        if (!isset($ctrObj)) {
            require_once "clases/Contrarrecibos.php";
            $ctrObj=new Contrarrecibos();
        }
        $ctrData=$ctrObj->getData("id=$ctrId");
        if (!isset($ctrData[0])) {
            doclog("PAYINGREQUEST Error: El contra recibo no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId]);
            echo getRespGralView($encabezado,"El contra recibo no existe",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $ctrData=$ctrData[0];
        $numAuth=$ctrData["numAutorizadas"]??0;
        if ($numAuth<=0) {
            doclog("PAYINGREQUEST Error: El contra recibo no tiene facturas autorizadas","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId]);
            echo getRespGralView($encabezado,"El contra recibo no tiene facturas autorizadas",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
        }
        $ctfObj->rows_per_page=0;
        $ctfData=$ctfObj->getData("idContrarrecibo=$ctrId and autorizadaPor is not null");
        if (!isset($ctfData[0])) {
            global $query;
            doclog("PAYINGREQUEST Error: El contra recibo no tiene contra facturas autorizadas","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId,"query"=>$query]);
            echo getRespGralView($encabezado,"El contra recibo no tiene facturas autorizadas",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        $ctfidFs=array_column($ctfData, "idFactura");
        $invWhere=isset($ctfidFs[1])?"id in (".implode(",", $ctfidFs).")":(isset($ctfidFs[0])?"id=".$ctfidFs[0]:"");
        if (!isset($invWhere[0])) {
            global $query;
            doclog("PAYINGREQUEST Error: El contra recibo no tiene idFactura's autorizadas","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId,"contrafacturas"=>$ctfData]);
            echo getRespGralView($encabezado,"El contra recibo no tiene facturas autorizadas",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
        global $invObj;
        $invData=$invObj->getData($invWhere,0,"id,statusn");
        $fixedInvoice=0;
        foreach ($invData as $idx => $invRow) {
            $invId=$invRow["id"];
            $invStatusN=(+$invRow["statusn"]);
            if ($invStatusN<16) {
                if ($invObj->saveRecord(["id"=>$invId,"status"=>"Pagado","statusn"=>$invStatusN+32])) {
                    $fixedInvoice++;
                    global $prcObj;
                    if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
                    $prcObj->cambioFactura($invId, "Pagado", $username, false, "Sol $solFolio x Contra $ctrData[aliasGrupo]-$ctrData[folio] Marcado");
                } else {
                    global $query;
                    doclog("PAYINGREQUEST Error: No fue posible actualizar una factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId,"idx"=>$idx,"statusn"=>$invStatusN,"factura"=>$invRow]);
                    echo getRespGralView($encabezado,"No fue posible actualizar una factura",$solId,$solFolio,true,false);
                    DBi::rollback();
                    DBi::autocommit(true);
                    die();
                }
            }
        }
        if ($fixedInvoice<=0) {
            global $query;
            doclog("PAYINGREQUEST Error: El contra recibo no tiene idFactura's autorizadas","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ctrId"=>$ctrId,"contrafacturas"=>$ctfData]);
            echo getRespGralView($encabezado,"El contra recibo no tiene facturas por pagar",$solId,$solFolio,true,false);
            DBi::rollback();
            DBi::autocommit(true);
            die();
        }
    } else {
        doclog("PAYINGREQUEST Error: La solicitud no tiene factura ni orden","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
        echo getRespGralView($encabezado,"La solicitud no tiene factura ni orden",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (!$solObj->saveRecord(["id"=>$solId,"status"=>new DBExpression("status|".SolicitudPago::STATUS_PAGADA),"proceso"=>SolicitudPago::PROCESO_PAGADA])) {
        if(!empty($solObj->errors)) {
            $errData=[];
            foreach($solObj->errors as $error) $errData[]=$error;
        } else if (!empty(DBi::$errors)) {
            $errData=DBi::$errors;
        } else {
            $errData=[DBi::getErrno()=>DBi::getError()];
        }
        global $query;
        doclog("PAYINGREQUEST Error: La solicitud no pudo guardarse","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"query"=>$query,"errors"=>$errData]);
        echo getRespGralView($encabezado,"Error al guardar solicitud",$solId,$solFolio,true,$beInteractive);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    global $usrObj;
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $fromObj=["address"=>getUser()->email,"name"=>replaceAccents(getUser()->persona)];
    $respuesta="<h2>La solicitud ".($solFolio??$solId)." ha sido pagada</h2>";
    $usrData=$usrObj->getData("id=$solIdUsuario",0,"nombre,persona,email");
    if (!isset($usrData[0]["nombre"])) {
        doclog("PAYINGREQUEST Error: Solicitud sin solicitante","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"usrId"=>$solIdUsuario]);
        echo getRespGralView($encabezado,"No se encuentra usuario Solicitante",$solId,$solFolio,true,false);
        DBi::rollback();
        DBi::autocommit(true);
        die();
    }
    if (isset($codigoProveedor[0])) {
        $uprData=$usrObj->getData("nombre='$codigoProveedor'",0,"persona,email");
        if (isset($uprData[0]["email"][0])) {
            $uprData=$uprData[0];
        } else $uprData=null;
    }
    $solObj->firma($solId,"paga");
    DBi::commit();
    DBi::autocommit(true);
    $usrData=$usrData[0];
    $template="respGralSolPago.html";
    $asunto="Solicitud $solFolio Pagada";
    $baseKeyMap=["%ENCABEZADO%"=>$asunto,"%RESPUESTA%"=>$respuesta,"%BUTTONS%"=>"<!-- 2 -->","isInteractive"=>"0"];
    $toObj=["address"=>$usrData["email"],"name"=>replaceAccents($usrData["persona"])];
    if (isset($uprData["email"])) {
        $toObj=[$toObj,["address"=>$uprData["email"],"name"=>$uprData["persona"]]];
    }
    $usrObj->saveRecord(["id"=>$solIdUsuario,"banderas"=>new DBExpression("banderas|2")]);
    $mensaje=getSolFormaView($template,$solId,$solFolio,$baseKeyMap);
    global $gpoObj;
    if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
    $mailSettings=["gpoId"=>$solData["idEmpresa"], "domain"=>$gpoObj->getDomainKey($solData["idEmpresa"])];
    sendMail($asunto,$mensaje,$fromObj,$toObj,null,null,$mailSettings); // (8) PAGO DE SOLICITUD. Finanzas a Solicitante
    echo getRespGralView($asunto,"La solicitud ha sido pagada",$solId,$solFolio,false,$beInteractive,false);
} // FIN (8) PAGO DE SOLICITUD //
function isResendEmail() {
    return "resendEmail"===($_REQUEST["action"]??"");
}
function doResendEmail() {
    sessionInit();
    if (!hasUser()) reloadNDie("Su sesión ha caducado, ingrese con su usuario nuevamente");
    if (!validaPerfil(["Administrador","Sistemas","Gestor"])) reloadNDie("No tiene permiso para reenviar correos");
    $solId=$_POST["solId"]??"";
    if (!isset($solId[0])) {
        errNDie("No se especifica id de solicitud",$_POST,"mail");
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        errNDie("La solicitud no existe",$_POST,"mail");
    }
    doclog("RESEND EMAIL: ","mail",$_POST);
    $solData=$solData[0];
    $solFolio=$solData["folio"];
    $solAuthList=$solData["authList"];
    $gpoId=$solData["idEmpresa"]??"";
    if (!isset($gpoId[0])) {
        errNDie("No se pudo obtener la empresa de la solicitud",$solData,"mail");
    }
    if (isset($_POST["authId"])) {
        global $perObj;
        if (!isset($perObj)) {
            require_once "clases/Perfiles.php";
            $perObj = new Perfiles();
        }
        $perData = $perObj->getData("nombre=\"Autoriza Pagos\"", 0, "id");
        if (!isset($perData[0])||!isset($perData[0]["id"])) {
            errNDie("No se encontró perfil de autorización",null,"mail");
        }
        $perId=$perData[0]["id"];
        $authId=$_POST["authId"];
        global $upObj;
        if (!isset($upObj)) {
            require_once "clases/Usuarios_Perfiles.php";
            $upObj = new Usuarios_Perfiles();
        }
        $upData = $upObj->getData("idUsuario=$authId and idPerfil=$perId",0,"count(1) n");
        if (!isset($upData[0])||!isset($upData[0]["n"])) {
            errNDie("No tiene permiso para autorizar solicitudes",null,"mail");
        }
        $authList=[$authId];
        global $tokObj;
        if (!isset($tokObj)) {
            require_once "clases/Tokens.php";
            $tokObj = new Tokens();
        }
        $tokData=$tokObj->getData("refId=$solId and modulo in (\"autorizaPago\",\"rechazaPago\") and status=\"activo\" and usrId=$authId",0,"token,modulo");
        if (!isset($tokData[0])) {
            $moduleList=["autorizaPago","rechazaPago"];
            $usageKey=null;
            $tokList=$tokObj->creaAccion($solId,$authList,$moduleList,$usageKey,true);
            if(!in_array("2639", $authList) && ($solAuthList=="2639"||(is_array($solAuthList) && in_array("2639", $solAuthList)))) $authList[]="2639";
            $solObj->saveRecord(["id"=>$solId,"authList"=>implode(",", $authList)]);
            $solAuthList=$authList;
        }
    } else if (isset($solAuthList[0])) {
        $authList=explode(",", $solAuthList);
        if (isset($authList[1]) && $authList[0]==="2639") array_shift($authList);
        $authId=$authList[0];
        // ToDo: Modificar para que se reciba solo un authId y solo a este se reenviará el correo. Hay que validar que exista token para ese usuario. Ya si no se recibe por alguna razón se tomará el primero de solData.authList (evitando 2639, como se implementó)
    } else {
        global $perObj;
        if (!isset($perObj)) {
            require_once "clases/Perfiles.php";
            $perObj=new Perfiles();
        }
        $perData=$perObj->getData("nombre='Autoriza Pagos'",0,"id");
        if (!isset($perData[0])||!isset($perData[0]["id"])) {
            errNDie("No se encontró perfil de autorización",null,"mail");
        }
        $perfilId=$perData[0]["id"];
        global $ugObj;
        if (!isset($ugObj)) {
            require_once "clases/Usuarios_Grupo.php";
            $ugObj=new Usuarios_Grupo();
        }
        $ugQuery="idPerfil=$perfilId and idGrupo=$gpoId";
        $authId=$_POST["authId"]??"";
        if (isset($authId[0])) {
            $ugQuery.=" and idUsuario=$authId";
        }
        $ugData=$ugObj->getData($ugQuery,0,"idUsuario");
        if (!isset($ugData[0]["idUsuario"])) {
            errNDie("No se encontró permiso Autoriza Pagos",$_POST,"mail");
        }
        if (isset($ugData[1])) {
            // Remover jlobaton, seleccionar fgarabana
            if ($ugData[0]["idUsuario"]==="2639") array_shift($ugData);
            else if (isset($ugData[1]) && !isset($ugData[2]) && $ugData[1]["idUsuario"]==="2639") array_pop($ugData);
            if (isset($ugData[1])) errNDie("Se encontraron varios autorizadores",$ugData,"mail");
        }
        $authId=$ugData["idUsuario"];
        $authList=[$authId];
    }
    $baseKeyMap=["SOLID"=>$solId,"SOLFOLIO"=>$solFolio,"HOSTNAME"=>$_SERVER["HTTP_ORIGIN"],"isInteractive"=>"0"];
    $isInteractive=false;
    ob_start();
    ob_implicit_flush(false);
    include "templates/solforma.php";
    $baseKeyMap["RESPUESTA"]=ob_get_clean();
    $rutaToken="consultas/Facturas.php?action=responsePaymentAuthorization&token=";
    global $tokObj;
    if (!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    $mensajeBase=file_get_contents(getBasePath()."templates/correoSolPago2.html");
    $tokData=$tokObj->getData("refId=$solId and modulo in (\"autorizaPago\",\"rechazaPago\") and status=\"activo\" and usrId=$authId",0,"token,modulo");
    foreach ($tokData as $idx=>$tokInfo) {
        if ($tokInfo["modulo"]==="autorizaPago")
            $tokenAutorizar=$tokInfo["token"];
        else if ($tokInfo["modulo"]==="rechazaPago")
            $tokenRechazar=$tokInfo["token"];
    }
    if (!isset($tokenAutorizar[0]) || !isset($tokenRechazar[0])) {
        errNDie("No se encontraron los tokens para autorizar",$tokData+$_POST,"mail");
    } else {
        //doclog("ENLACES DE TOKENS: ","mail",["AUTORIZAR"=>$rutaToken.$tokenAutorizar,"RECHAZAR"=>$rutaToken.$tokenRechazar]);
    }
    $keyMap=array_merge($baseKeyMap,["AUTORIZAR"=>$rutaToken.$tokenAutorizar,"RECHAZAR"=>$rutaToken.$tokenRechazar]);
    $mensaje = preg_replace("/%\w+%/","",str_replace(array_map(function($elem){return "%".$elem."%";},array_keys($keyMap)),array_values($keyMap), $mensajeBase));
    $solUserId=$solData["idUsuario"];
    $usrFromData=$usrObj->getData("id=$solUserId",0,"persona,email");
    if (!isset($usrFromData[0]["email"]))
        errNDie("No se encontro usuario solicitante",$solData,"mail");
    $usrFromData=$usrFromData[0];
    $from=["address"=>$usrFromData["email"],"name"=>replaceAccents($usrFromData["persona"])];
    //$fromString="{$from['name']} <{$from['address']}>";
    $usrToData=$usrObj->getData("id=$authId",0,"persona,email");
    if (!isset($usrToData[0]["email"])) {
        errNDie("No se encontró usuario autorizador",$authList,"mail");
    }
    $usrToData=$usrToData[0];
    $to=["address"=>$usrToData["email"],"name"=>replaceAccents($usrToData["persona"])];
    //$to=["address"=>"aguirrehidalgoca@gmail.com","name"=>"Carlos Aguirre Test"];
    //$toString="{$to['name']} <{$to['address']}>";
    $subject="Solicitud de Autorizacion de Pago $solFolio";
    //$mailDesc="'$subject' de $fromString para $toString";
    global $gpoObj;
    if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
    $mailSettings=["gpoId"=>$gpoId, "domain"=>$gpoObj->getDomainKey($gpoId),"solId"=>$solId,"solfolio"=>$solFolio];
    //doclog("Enviando correo $mailDesc","mail");
    sendMail($subject,$mensaje,$from,$to,null,null,$mailSettings); // RESEND. Solicitante a Ultimo Autorizador
    // toDo: Enviar correo a solicitante: "Solicitud de Autorización reenviada"
    //require_once "clases/Correo.php";
    //echo "Resend Mail in Construction: ".htmlspecialchars($mailDesc);
    $sssRsndCnt=+($_SESSION["resendCounter{$solId}"]??"0")+1;
    $_SESSION["resendCounter{$solId}"]="$sssRsndCnt";
    $rsndtms="$sssRsndCnt ve".($sssRsndCnt==1?"z":"ces");
    echo "<p class=\"boldValue reloadOnClose\">Solicitud de Autorización $solFolio reenviada a $to[name]<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const blk=ebyid('rsndBlk{$solId}');if(blk){blk.setAttribute('rsndTms','$rsndtms');const bdg=ebyid('rsndBdg{$solId}');if(bdg){bdg.title='Reenviado $rsndtms';}else{cladd(blk,'relative');blk.appendChild(ecrea({eName:'DIV',id:'rsndBdg{$solId}',className:'abs_se badge',title:'Reenviado {$rsndtms}',eText:'+'}));}}else console.log('No pudo encontrarse rsndBlk{$solId}');ekil(this);\"></p>";
    //                         padh1em
}
function isPayingMultiple() {
    return "payingMultipleRequest"===($_REQUEST["action"]??"");
}
function doPayingMultiple() {
    global $query, $invObj, $ordObj, $prvObj, $usrObj;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    sessionInit();
    if (!hasUser()) {
        echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
        die();
    }
    $ids=$_POST["ids"]??[]; // solId, [folId,] proc, stt, tipoFO
    $files=getFixedFileArray($_FILES["file"]??null);
    $info=[];
    $document_root = $_SERVER["DOCUMENT_ROOT"];
    $http_origin = $_SERVER["HTTP_ORIGIN"];
    if (isset($ids[0])) foreach ($ids as $idx => $data) {
        if (!isset($data) || !is_string($data) || substr_count($data, ",")<3) {
            if (!isset($info["error"])) $info["error"]=[];
            $info["error"][]="Datos incompletos (# ".($idx+1).")";
            doclog("Error al anexar y pagar: incompleto","solpago",$baseData+["line"=>__LINE__,"data"=>$data]);
            continue;
        }
        if (isset($data) && $data[0]==="[") {
            $data=json_decode($data);
            list($solId,$solFolio,$solProceso,$solStatus,$solTipoFO)=$data;
        } else list($solId,$solFolio,$solProceso,$solStatus,$solTipoFO)=explode(",", $data, 5);
        doclog("SOLTIPO","solpago",$baseData+["line"=>__LINE__,"data"=>$data,"tipo"=>$solTipoFO]);
        if (!isset($solId[0])) {
            $info["error"][]="Sin id solicitud (# ".($idx+1).")";
            doclog("Error al anexar y pagar: sin id solicitud","solpago",$baseData+["line"=>__LINE__,"data"=>$data]);
            continue;
        }
        if (!isset($solFolio[0])) {
            doclog("No se encontró folio, se mostrará Id","solpago",$baseData+["line"=>__LINE__,"data"=>$data]);
            $solFolio=$solId;
        }
        $info[$solId]=["error"=>[]];
        if (!isset($solProceso[0])) {
            $info[$solId]["error"][]="Sin proceso";
            doclog("Error al anexar y pagar: sin proceso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"data"=>$data]);
            continue;
        }
        //$solProceso=+$solProceso;
        if (!isset($solStatus[0])) {
            $info[$solId]["error"][]="Sin status";
            doclog("Error al anexar y pagar: sin status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"data"=>$data]);
            continue;
        }
        //$solStatus=+$solStatus;
        if (!isset($solTipoFO[0])) {
            $info[$solId]["error"][]="Sin tipo F|O";
            doclog("Error al anexar y pagar: sin tipo F|O","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"data"=>$data]);
            continue;
        }
        $extraData=isset($solTipoFO[1])?substr($solTipoFO, 2):"";
        $solTipoFO=strtoupper($solTipoFO[0]);
        $info[$solId]=["solFolio"=>$solFolio,"solProceso"=>$solProceso,"solStatus"=>$solStatus,"solTipo"=>$solTipoFO,"extraData"=>$extraData,"error"=>[]];
        $forceNoFile=isset($extraData[0])&&$extraData[0]==="1";
        doclog("EXTRADATA","solpago",$baseData+["line"=>__LINE__,"extradata"=>$extraData,"forceNoFile"=>$forceNoFile?"TRUE":"FALSE"]);
        if (isset($extraData[1])) {
            $extraData=substr($extraData,2);
        } else if (isset($extraData[0])) $extraData="";
        if (!isset($files[$solId]) && !$forceNoFile && $solProceso<3) {
            $info[$solId]["error"][]="No se cargó archivo en solicitud $solId";
            doclog("Error al anexar y pagar: sin archivo","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            continue;
        }
        if (!isset($solObj)) {
            require_once "clases/SolicitudPago.php";
            $solObj=new SolicitudPago();
        }
        $solData=$solObj->getData("id=$solId");
        if (!isset($solData)) {
            $info[$solId]["error"][]="La solicitud $solId ya no existe";
            doclog("Error al anexar y pagar: no existe","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            continue;
        }
        $solData=$solData[0];
        if (!isset($solFolio[0])||$solFolio===$solId) $solFolio=$solData["folio"];
        if ($solStatus!==$solData["status"]) {
            doclog("Aviso al anexar y pagar: Cambió status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"oldStatus"=>$solStatus,"newStatus"=>$solData["status"]]);
        }
        $solStatus=+$solData["status"];
        if ($solStatus&SolicitudPago::STATUS_CANCELADA) {
            $info[$solId]["error"][]="La solicitud $solId se ha cancelado";
            doclog("Error al anexar y pagar: cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            continue;
        }
        $info[$solId]["solStatus"]=$solStatus;
        if ($solProceso!==$solData["proceso"]) {
            doclog("Aviso al anexar y pagar: Cambió proceso","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"oldProceso"=>$solProceso,"newProceso"=>$solData["proceso"]]);
        }
        $solProceso=+$solData["proceso"];
        if ($solProceso>=SolicitudPago::PROCESO_PAGADA) {
            $info[$solId]["error"][]="La solicitud $solId ya está Pagada";
            doclog("Error al anexar y pagar: pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            continue;
        }
        $info[$solId]["solProceso"]=$solProceso;
        if (isset($files[$solId])) {
            $file=$files[$solId];
            if (!empty($file["error"])) {
                $info[$solId]["error"][]="Archivo con error en solicitud $solId";
                doclog("Error al anexar y pagar: error en archivo","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"file"=>$file,"fileError"=>fileCodeToMessage($file["error"],["filename"=>$file["name"],"lang"=>"en"])]);
                continue;
            }
            if (!chmod($file["tmp_name"], 0777))
                doclog("Aviso al anexar y pagar: Falló cambio de permiso de archivo temporal","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"file"=>$file]);
        }
        $idFactura=$solData["idFactura"]??null;
        $idOrden=$solData["idOrden"]??null;
        if (isset($idFactura)) {
            if ($solTipoFO!=="F") {
                doclog("Aviso al anexar y pagar: Cambió tipo a Factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"oldTipo"=>$solTipoFO,"newTipo"=>"F"]);
                $solTipoFO="F";
            }
            if ($solProceso<SolicitudPago::PROCESO_CONTABLE) {
                $info[$solId]["error"][]="La solicitud $solId no está lista para pago";
                doclog("Error al anexar y pagar: factura no contable","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
                continue;
            }
            $invData=$invObj->getData("id=$idFactura");
            if (!isset($invData[0])) {
                $info[$solId]["error"][]="La factura en la solicitud $solId ha sido eliminada";
                doclog("Error al anexar y pagar: sin factura","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura]);
                continue;
            }
            $invData=$invData[0];
            if (!isset($invData["status"][0])) {
                $info[$solId]["error"][]="La factura en la solicitud $solId ha sido alterada";
                doclog("Error al anexar y pagar: factura alterada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"invStatusN"=>$invData["statusn"]]);
                continue;
            }
            if (!isset($invData["version"])||!in_array($invData["version"], ["3.3","4.0"])) {
                $info[$solId]["error"][]="La versión de factura $invData[version] no es válida";
                doclog("Error al anexar y pagar: Versión CFDI inválida","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"version"=>$invData["version"]]);
                continue;
            }
            if (!isset($invData["tipoComprobante"])||strtolower($invData["tipoComprobante"])!=="i") {
                $info[$solId]["error"][]="El comprobante en la solicitud $solId no es ingreso";
                doclog("Error al anexar y pagar: tipo de comprobante","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"tipoComprobante"=>$invData["tipoComprobante"]]);
                continue;
            }
            if ($invData["status"]==="Temporal" || !isset($invData["statusn"])) {
                $info[$solId]["error"][]="La solicitud $solId no se registró correctamente";
                doclog("Error al anexar y pagar: status invalido","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"status"=>$invData["status"],"statusn"=>$invData["statusn"]]);
                continue;
            }
            $invStatusN=+$invData["statusn"];
            if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
                $info[$solId]["error"][]="La factura en la solicitud $solId no fue aceptada";
                doclog("Error al anexar y pagar: factura no aceptada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"status"=>$invData["status"],"statusn"=>$invData["statusn"]]);
                continue;
            }
            $invEstadoCFDI=$invData["estadoCFDI"]??"N/A";
            if (!isset($invEstadoCFDI) || $invEstadoCFDI!=="Vigente") {
                $info[$solId]["error"][]="La factura en la solicitud $solId no está vigente ante el SAT";
                doclog("Error al anexar y pagar: factura no vigente","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"invId"=>$idFactura,"estadoCFDI"=>$invEstadoCFDI]);
                continue;
            }
            $codigoProveedor=$invData["codigoProveedor"];
            $tabDesc="Factura";
            $tabObj=$invObj;
            $cpId=$idFactura;
            if (isset($file)) {
                $tmpName=$invData["nombreInternoPDF"];
                if (!isset($tmpName[0])) $tmpName=$invData["nombreInterno"];
                $fileFieldName="comprobantePagoPDF";
                $cpName="CP_".$tmpName;
                $cpPath=$invData["ubicacion"];
            }
            $divName="invDocs";
            $tabStatusFieldNames=["statusn","status"];
            $tabStatusValues=[new DBExpression("statusn|".Facturas::STATUS_PAGADO),"Pagado"];
        } else if (isset($idOrden)) {
            if ($solTipoFO!=="O") {
                doclog("Aviso al anexar y pagar: Cambió tipo a Orden de Compra","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"oldTipo"=>$solTipoFO,"newTipo"=>"O"]);
                $solTipoFO="O";
            }
            if (!isset($ordObj)) {
                require_once "clases/OrdenesCompra.php";
                $ordObj=new OrdenesCompra();
            }
            $ordData=$ordObj->getData("id=$idOrden");
            if (!isset($ordData[0])) {
                $info[$solId]["error"][]="La orden en la solicitud $solId ha sido eliminada";
                doclog("Error al anexar y pagar: sin orden","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]);
                continue;
            }
            $ordData=$ordData[0];
            $stt=$ordData["status"]??"";
            if (!isset($stt[0])) {
                $info[$solId]["error"][]="La orden de compra no está disponible para pago";
                doclog("Error al anexar y pagar: orden sin status","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]);
                continue;
            }
            $stt=+$stt;
            if ($stt<0) {
                $info[$solId]["error"][]="La orden de compra está cancelada";
                doclog("Error al anexar y pagar: orden cancelada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]);
                continue;
            }
            if ($stt<2) {
                $info[$solId]["error"][]="La orden de compra no se ha autorizado";
                doclog("Error al anexar y pagar: orden no autorizada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]);
                continue;
            }
            if ($stt>=4) {
                $info[$solId]["error"][]="La orden de compra ya está pagada";
                doclog("Error al anexar y pagar: orden pagada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"ordId"=>$idOrden]);
                continue;
            }
            $idProveedor=$ordData["idProveedor"];
            if (!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj = new Proveedores();
            }
            $prvData=$prvObj->getData("id=$idProveedor");
            $codigoProveedor=$prvData[0]["codigo"]??"";
            $tabDesc="Orden de compra";
            $tabObj=$ordObj;
            $cpId=$idOrden;
            if (isset($file)) {
                $fileFieldName="comprobantePago";
                $cpName="CP_".$ordData["nombreArchivo"];
                $cpPath=$ordData["rutaArchivo"];
            }
            $divName="ordDocs";
            $tabStatusFieldNames=["status"];
            $tabStatusValues=[new DBExpression("status|4"),"Pagado"];
        } else {
            $info[$solId]["error"][]="La solicitud no tiene factura ni orden de compra";
            doclog("Error al anexar y pagar: solicitud sin factura ni orden","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId]);
            continue;
        }
        $fieldArray=["id"=>$cpId];
        if (isset($file)) {
            $fieldArray[$fileFieldName]=$cpName;
        }
        foreach ($tabStatusFieldNames as $idx => $fldnm) {
            $fieldArray[$fldnm]=$tabStatusValues[$idx];
        }
        DBi::autocommit(false);
        if (!$tabObj->saveRecord($fieldArray)&&!empty(DBi::$errno)) {
            $info[$solId]["error"][]="Error al guardar $tabDesc";
            doclog("Error al anexar y pagar: $tabDesc no guardada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId, "query"=>$query, 'errno'=>DBi::$errno, 'error'=>DBi::$error]);
            DBi::rollback();
            DBi::autocommit(true);
            continue;
        }
        if (!$solObj->saveRecord(["id"=>$solId,"status"=>new DBExpression("status|".SolicitudPago::STATUS_PAGADA),"proceso"=>SolicitudPago::PROCESO_PAGADA]) && !empty(DBi::$errno)) {
            $info[$solId]["error"][]="Error al guardar solicitud";
            doclog("Error al anexar y pagar: solicitud no guardada","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId, "query"=>$query, 'errno'=>DBi::$errno, 'error'=>DBi::$error]);
            DBi::rollback();
            DBi::autocommit(true);
            continue;
        }
        require_once "clases/Tokens.php";
        $tokenResultA = Tokens::eligeUsuario($solId,"anexaComprobante");
        $tokenResultP = Tokens::eligeUsuario($solId,"procesaPago");
        if (isset($file)) {
            $fullName=$cpPath.$cpName.".pdf";
            $absName=$document_root.$fullName;
            if(file_exists($absName)) {
                if (!chmod($absName, 0777))
                    doclog("Aviso al anexar y pagar: Falló cambio de permiso para borrar archivo anterior","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"fullName"=>$fullName]);
                if (!unlink($absName))
                    doclog("Aviso al anexar y pagar: Falló borrado de archivo anterior","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"fullName"=>$fullName]);
            }
            if (move_uploaded_file($file["tmp_name"], $absName)===false) {
                $info[$solId]["error"][]="Error al cargar archivo";
                doclog("Error al anexar y pagar: archivo no anexado","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId, "fullName"=>$fullName,"moveError"=>error_get_last()]);
                DBi::rollback();
                DBi::autocommit(true);
                continue;
            }
            $solObj->firma($solId,"anexa");
        }
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $fromObj=["address"=>getUser()->email, "name"=>replaceAccents(getUser()->persona)];
        $idUsuario=$solData["idUsuario"];
        /* TEST */$idUsuario=2146;
        $usrData=$usrObj->getData("id=$idUsuario",0,"nombre,persona,email");
        if (!isset($usrData[0]["nombre"])) {
            $info[$solId]["error"][]="Error en envío de correo al solicitante";
            doclog("Error al anexar y pagar: solicitante no identificado","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId, "idUsuario"=>$idUsuario]);
            DBi::rollback();
            DBi::autocommit(true);
            continue;
        }
        $usrData=$usrData[0];
        if (isset($codigoProveedor[0])) {
            $uprData=$usrObj->getData("nombre='$codigoProveedor'",0,"persona,email");
            if (isset($uprData[0]["email"][0])) {
                $uprData=$uprData[0];
                /* TEST */$uprData["email"] = "aguirrehidalgoca@gmail.com";
            } else $uprData=null;
        }
        $solObj->firma($solId,"paga");
        DBi::commit();
        DBi::autocommit(true);
        $asunto="Solicitud $solFolio Pagada";
        $respuesta="<h2>La solicitud $solFolio ha sido pagada</h2>";
        //$baseKeyMap=["%ENCABEZADO%"=>$asunto,"%RESPUESTA%"=>$respuesta,"isInteractive"=>"0"];
        //$template="respGralSolPago.html";
        //$mensaje=getSolFormaView($template,$solId,$baseKeyMap);
        $mensaje=getRespGralView($asunto,$respuesta,$solId,$solFolio,false,true,false);
        $toObj=["address"=>$usrData["email"],"name"=>replaceAccents($usrData["persona"])];
        if (isset($uprData["email"])) {
            $toObj=[$toObj,["address"=>$uprData["email"],"name"=>$uprData["persona"]]];
        }
        $usrObj->saveRecord(["id"=>$idUsuario,"banderas"=>new DBExpression("banderas|2")]);
        global $gpoObj;
        if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
        $mailSettings=["domain"=>$gpoObj->getDomainKey($solData["idEmpresa"])];
        sendMail($asunto,$mensaje,$fromObj,$toObj,null,null,$mailSettings); // Pagos Múltiples. Finanzas a Solicitantes
        $info[$solId]["log"]="Solicitud Marcada Pagada";
    }
    successNDie("Solicitudes procesadas",["post"=>$_POST,"files"=>$files,"info"=>$info]);
}
// Cancelar factura sin solicitud
function isCancelInvoiceInRequest() {
    return "cancelInvInReq"===($_POST["action"]??"");
}
function doCancelInvoiceInRequest() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    sessionInit();
    if (!hasUser()) {
        echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
        die();
    }
    $accion=$_POST["accion"]??"Cancela";
    $accionlw=strtolower($accion);
    $invId=$_POST["invId"]??"";
    if (!isset($invId[0])) {
        errNDie("No se especificó CFDI a {$accionlw}r",$baseData+$_POST);
    }
    $motivo=trim($_POST["motivo"]??"");
    if (!isset($motivo[0])) {
        errNDie("No se especificó motivo para {$accionlw}r",$baseData+$_POST);
    }
    $folio=$_POST["folio"]??"";
    $alias=$_POST["alias"]??"";
    $empresa=$_POST["gpoRS"]??"";
    $codigo=$_POST["prCod"]??"";
    $proveedor=$_POST["prvRS"]??"";
    global $usrObj, $invObj, $ctrObj, $ctfObj, $prcObj, $solObj, $query;
    /*if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj = new Facturas(); }*/
    $invData=$invObj->getData("id=$invId");
    if (!isset($invData[0]["uuid"][0])) {
        $em="";
        if (isset($alias[0])) {
            $em.="empresa $alias";
            global $gpoObj;if(!isset($gpoObj)){require_once "clases/Grupo.php";$gpoObj=new Grupo();}
            $gpoData=$gpoObj->getData("alias='{$alias}'",0,"id");
            //$gpoId=array_column(, "id")[0];
            if (isset($gpoData[0]["id"])) $gpoId=$gpoData[0]["id"];
        }
        if (isset($codigo[0])) {
            $em.=(isset($em[0])?", ":"")."proveedor $codigo";
        }
        if (isset($folio[0])) $em.=(isset($em[0])?", ":"")."folio $folio";
        if (!isset($em[0])) $em="id $invId";
        errNDie("CFDI no encontrado ($em)",$baseData+$_POST);
    }
    $invData=$invData[0];
    $tc=$invData["tipoComprobante"][0];
    $rfcGpo=$invData["rfcGrupo"];
    if (!isset($codigo[0])) $codigo=$invData["codigoProveedor"];
    if (!isset($folio[0])) {
        if (isset($invData["folio"][0])) {
            $folio=$invData["folio"];
            if (isset($invData["folio"][10])) $folio=substr($folio,-10);
        } else $folio="[".substr($invData["uuid"], -10)."]";
    }
    if(!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj = new Usuarios(); }
    $uprData=$usrObj->getData("nombre='$codigo'",0,"persona,email");
    if (!isset($uprData[0]["email"][0])) {
        doclog("Proveedor de CFDI no reconocido","cancelinv",$_POST);
        $uprData=null;
    }
    $invStatusN=+$invData["statusn"];
    if ($invStatusN>=Facturas::STATUS_RECHAZADO) {
        errNDie("El CFDI ya estaba {$accionlw}do",$baseData+$_POST);
    }
    $esAdmin=validaPerfil("Administrador");
    $esSistemas=validaPerfil("Sistemas")||$esAdmin;
    if ($invStatusN>=Facturas::STATUS_PAGADO && !$esSistemas) { // ToDo: ignorar si es administrador o sistemas. Permitir rechazar facturas pagadas
        errNDie("No puede cancelar una factura pagada, solicite cancelacion a Sistemas.",$baseData+$_POST);
    }
    if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
    $solData=$solObj->getData("idFactura=$invId");
    if (isset($solData[0]["id"])) {
        errNDie("No puede cancelar una factura integrada a una solicitud",$baseData+$_POST);
    }
    DBi::autocommit(FALSE);
    $bccObj=[]; $mmsg="";
    if ($invStatusN&Facturas::STATUS_CONTRA_RECIBO) {
        $plan=$_POST["plan"]??"a";
        switch($plan) {
            case "a": // Si tiene contra recibo marcar error: primero debe eliminar la factura del contra recibo
                DBi::rollback();
                DBi::autocommit(TRUE);
                errNDie("No puede cancelar una factura con contra recibo, debe separarla primero.",$baseData+$_POST);
                break;
            case "b"; // Eliminar factura de su contra recibo, si el contra recibo no tiene otras facturas eliminarlo tambien
                if (!isset($ctfObj)) { require_once "clases/Contrafacturas.php"; $ctfObj = new Contrafacturas(); }
                $ctfData=$ctfObj->getData("idFactura=$invId");
                if (isset($ctfData[0]["id"])) {
                    $ctfData=$ctfData[0];
                    $ctfId=$ctfData["id"];
                    $ctfTot=+$ctfData["total"];
                    $crId=$ctfData["idContrarrecibo"];
                    if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj = new Contrarrecibos(); }
                    $ctrData=$ctrObj->getData("id=$crId");
                    if (isset($ctrData[0])) {
                        $aliasG=$ctrData[0]["aliasGrupo"];
                        $ctrFolio=$aliasG."-".$ctrData[0]["folio"];
                        global $gpoObj;if(!isset($gpoObj)){require_once "clases/Grupo.php";$gpoObj=new Grupo();}
                        $gpoData=$gpoObj->getData("alias='{$aliasG}'",0,"id");
                        //$gpoId=array_column(, "id")[0];
                        if (isset($gpoData[0]["id"])) $gpoId=$gpoData[0]["id"];
                    }
                    if (!$ctfObj->deleteRecord(["id"=>$ctfId])) {
                        $ctfQry=$query;
                        $ern=DBi::$errno;
                        $err=DBi::$error;
                        DBi::rollback();
                        DBi::autocommit(TRUE);
                        errNDie("No se pudo eliminar la factura del contra recibo",$baseData+["query"=>$ctfQry,"errno"=>$ern,"error"=>$err]+$_POST);
                    }
                    $cfsData=$ctfObj->getData("idContrarrecibo=$crId");
                    if (!isset($firObj)) { require_once "clases/Firmas.php"; $firObj=new Firmas(); }
                    if (isset($cfsData[0])) {
                        $ctfOp="-";
                        if ($ctfTot<0) {
                            $ctfOp="+";
                            $ctfTot*=-1;
                        }
                        $ctrFldArr=["id"=>$crId,"total"=>new DBExpression("total{$ctfOp}$ctfTot"),"numContraRegs"=>new DBExpression("numContraRegs-1")];
                        if (isset($ctfData["autorizadaPor"][0])) $ctrFldArr["numAutorizadas"]=new DBExpression("numAutorizadas-1");
                        if (!$ctrObj->saveRecord($ctrFldArr) && DBi::$errno) {
                            $ctrQry=$query;
                            $ern=DBi::$errno;
                            $err=DBi::$error;
                            DBi::rollback();
                            DBi::autocommit(TRUE);
                            errNDie("Error al actualizar contra recibo",$baseData+["query"=>$ctrQry,"errno"=>$ern,"error"=>$err]+$_POST);
                        }
                        if (isset($ctrFolio)) $mmsg.="<br>El contra-recibo $ctrFolio fue modificado.";
                        if (!$firObj->saveRecord(["idUsuario"=>getUser()->id,"modulo"=>"contrarrecibo","idReferencia"=>$crId,"accion"=>"elimina","motivo"=>"Elimina $ctfData[folioFactura] en contrarrecibo $ctrFolio"])) doclog("Error al agregar Firma de Contra recibo modificado","cancelinv",$baseData+["query"=>$query,"errno"=>DBi::$errno,"error"=>DBi::$error]+$_POST);
                        // toDo: agregar firma de facturas eliminadas del contra recibo
                    } else {
                        if (!$ctrObj->deleteRecord(["id"=>$crId])) {
                            $ctrQry=$query;
                            $ern=DBi::$errno;
                            $err=DBi::$error;
                            DBi::rollback();
                            DBi::autocommit(TRUE);
                            errNDie("Error al eliminar contra recibo",$baseData+["query"=>$ctrQry,"errno"=>$ern,"error"=>$err]+$_POST);
                        }
                        if (isset($ctrFolio)) $mmsg.="<br>El contra-recibo $ctrFolio fue eliminado.";
                        // toDo: agregar firma de todas las facturas eliminadas del contra recibo
                        if (!$firObj->saveRecord(["idUsuario"=>getUser()->id,"modulo"=>"contrarrecibo","idReferencia"=>$crId,"accion"=>"elimina","motivo"=>"Elimina $ctfData[folioFactura] y contrarrecibo $ctrFolio"])) doclog("Error al agregar Firma de Contra recibo eliminado","cancelinv",$baseData+["query"=>$query,"errno"=>DBi::$errno,"error"=>DBi::$error]+$_POST);
                    }
                    // Enviar copia del correo a contabilidad, indicando el contra recibo y si fue modificado o eliminado
                    //$upgData=$usrObj->getData("up.idPerfil=".SolicitudPago::PERFIL_GESTIONA,0,"u.id,u.nombre,u.persona,u.email","u inner join usuarios_perfiles up on u.id=up.idUsuario");
                    $hasGId=(isset($gpoId));
                    $upgData=$usrObj->getData("ug.idPerfil=".SolicitudPago::PERFIL_GESTIONA.($hasGId?" and ug.idGrupo=".$gpoId:""),0,($hasGId?"":"distinct ")."u.id,u.nombre,u.persona,u.email","u inner join usuarios_grupo ug on u.id=ug.idUsuario".($hasGId?" inner join grupo g on ug.idGrupo=g.id":""));
                    //if (isset($upgData["id"])) $upgData=[$upgData];
                    foreach ($upgData as $idx=>$usrElem) {
                        $bccObj[]=["address"=>$usrElem["email"],"name"=>replaceAccents($usrElem["persona"])];
                    }
                } else doclog("No se encontró contrafactura","cancelinv",$_POST);
                break;
            default: // Si tiene contra recibo marcar error: primero debe eliminar la factura del contra recibo
                DBi::rollback();
                DBi::autocommit(TRUE);
                errNDie("La factura indicada tiene contra recibo.",$_POST,"cancelinv");
                break;
        }
    }
    doclog("Ready to cancel","read",["post"=>$_POST,"data"=>$invData]);
    if (!$invObj->saveRecord(["id"=>$invId,"status"=>"{$accion}do","statusn"=>new DBExpression("statusn|".Facturas::STATUS_RECHAZADO)])) {
        global $query;
        $invQuery=$query;
        $invErrors=DBi::$errors;
        DBi::rollback();
        DBi::autocommit(TRUE);
        errNDie("No fue posible {$accionlw}r el CFDI",$_POST+["query"=>$invQuery,"errors"=>$invErrors],"cancelinv");
    }
    if (getUser()->nombre==="admin") {
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $usrData=null;
        $usrname=$_POST["username"]??"SISTEMAS";
        if ($usrname==="nomail") {
            $uprData=null;
            $usrname=null;
        }
        if (isset($usrname)) {
            $usrData=$usrObj->getData("nombre='$usrname'",0,"id,persona,email");
            if (!isset($usrData[0]["email"][0])) {
                if (!isset($usrData[0]["id"])) errNDie("El usuario $usrname no existe",$_POST,"cancelinv");
                errNDie("El usuario $usrname no tiene correo",$_POST,"cancelinv");
            }
            $usrId=$usrData[0]["id"];
            $usrfull=replaceAccents($usrData[0]["persona"]);
            $usrmail=$usrData[0]["email"];
        }
    } else {
        $usrId=getUser()->id;
        $usrname=getUser()->nombre;
        $usrfull=replaceAccents(getUser()->persona);
        $usrmail=getUser()->email;
    }
    // Proceso con motivo
    if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
    $prcObj->cambioFactura($invId, "{$accion}do", $usrname, false, $detalle="$motivo");
    $mensaje="El CFDI ha sido {$accionlw}do: $motivo";
    // Correo al proveedor
    if (isset($usrmail)) {
        //$fromObj=["address"=>$usrmail, "name"=>$usrfull];
        $toObj=[];
        if (isset($uprData)) {
            $toObj[]=["address"=>$uprData[0]["email"],"name"=>replaceAccents($uprData[0]["persona"])];
            $bccObj[]=["address"=>$usrmail, "name"=>$usrfull];
        } else {
            $toObj[]=["address"=>$usrmail, "name"=>$usrfull];
            $mensaje.=", pero <u>sin correo al proveedor</u>";
            $mmsg.="<br>Proveedor sin correo: $codigo $proveedor";
        }
        global $gpoObj;
        if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
        $mailSettings=["domain"=>$gpoObj->getDomainKeyByAlias($gpoObj->getAliasByRFC($rfcGpo))];
        sendMail("CFDI $folio {$accion}do","El CFDI con folio '$folio' fue {$accionlw}do:<br><b>$motivo</b>{$mmsg}", false, $toObj, $ccObj??null, $bccObj??null, $mailSettings);
    }
    DBi::commit();
    DBi::autocommit(TRUE);
    successNDie($mensaje.$mmsg,["idUsuario"=>$usrId,"idFactura"=>$invId],"cancelinv");
}
function isCancelPaymentRequest() {
    return "cancelPaymRequest"===($_REQUEST["action"]??"");
}
function doCancelPaymentRequest() {
    sessionInit();
    if (!hasUser()) {
        echo json_encode(["action"=>"redirect","mensaje"=>"Su sesión ha caducado, ingrese con su usuario nuevamente"]);
        die();
    }
    $solId=$_POST["solId"]??"";
    if (!isset($solId[0])) {
        errNDie("No se especifica id de solicitud",$_POST);
    }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        errNDie("La solicitud no existe",$_POST);
    }
    $solData=$solData[0];
    DBi::autocommit(FALSE);
    if (!$solObj->saveRecord(["id"=>$solId,"status"=>new DBExpression("status|".SolicitudPago::STATUS_CANCELADA)])) {
        DBi::rollback();
        DBi::autocommit(TRUE);
        errNDie("No fue posible cancelar la solicitud",$_POST);
    }
    $solUsrId=$solData["idUsuario"];
    $solFolio=$solData["folio"]??"ID$solId";
    $invPrvId=null;
    $invFolio=null;
    $invId=$solData["idFactura"]??null;

    $usrId = getUser()->id;
    $usrname = getUser()->nombre;
    $usrFullName=getUser()->persona;
    $usrEMail=getUser()->email;
    /*
    global $tokObj;
    if (!isset($tokObj)) {
        require_once "clases/Tokens.php";
        $tokObj = new Tokens();
    }
    $tokObj->saveRecord(["refId"=>$solId,"status"=>"activo"]);
    */
    DBi::query("UPDATE tokens SET status=\"cancelado\", usos=0 WHERE refId=$solId AND status=\"activo\" AND id>0");
    DBi::query("UPDATE tokens SET usos=0 WHERE refId=$solId AND modulo=\"autorizaPago\" AND id>0");
    if (isset($invId)) {
        global $invObj;
        /*if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj = new Facturas();
        }*/
        if (!$invObj->saveRecord(["id"=>$invId,"status"=>"Cancelado","statusn"=>new DBExpression("statusn|".Facturas::STATUS_RECHAZADO)])) {
            DBi::rollback();
            DBi::autocommit(TRUE);
            errNDie("No fue posible cancelar la factura",$_POST);
        }
        $invData=$invObj->getData("f.id=$invId",0,"f.folio,right(f.uuid,10) uuid,p.id","f inner join proveedores p on f.codigoProveedor=p.codigo");
        if (isset($invData[0])) $invData=$invData[0];
        if (isset($invData["id"])) $invPrvId=$invData["id"];
        if (isset($invData["folio"][0]))
            $invFolio=$invData["folio"];
        else if (isset($invData["uuid"][0]))
            $invFolio=$invData["uuid"];
        else $invFolio="(sin folio)";
        global $prcObj;
        if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
        $prcObj->cambioFactura($invId, "Cancelado", $usrname, false, $detalle="Sol $solFolio cancelada");
    } else if (isset($solData["idOrden"])) {
        global $ordObj;
        if (!isset($ordObj)) {
            require_once "clases/OrdenesCompra.php";
            $ordObj = new OrdenesCompra();
        }
        if (!$ordObj->saveRecord(["id"=>$solData["idOrden"],"status"=>OrdenesCompra::STATUS_CANCELADO])) {
            DBi::rollback();
            DBi::autocommit(TRUE);
            errNDie("No fue posible cancelar la orden de compra",$_POST);
        }
    } else {
        DBi::rollback();
        DBi::autocommit(TRUE);
        errNDie("No fue posible cancelar la factura u orden de compra",$_POST);
    }
    global $firObj;
    if (!isset($firObj)) {
        require_once "clases/Firmas.php";
        $firObj = new Firmas();
    }
    if (getUser()->nombre==="admin") {
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $usrData = $usrObj->getData("nombre='SISTEMAS'",0,"id,persona,email");
        if (isset($usrData[0])) $usrData=$usrData[0];
        if (isset($usrData["id"])) {
            $usrId=$usrData["id"];
            $usrFullName=$usrData["persona"];
            $usrEMail=$usrData["email"];
        }
    }
    $motivo=$_POST["motivo"]??"No especificado";
    $firObj->saveRecord(["idUsuario"=>$usrId,"modulo"=>"solpago","idReferencia"=>$solId,"accion"=>"cancela","motivo"=>$motivo]);
    DBi::commit();
    DBi::autocommit(TRUE);

    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $usrData=$usrObj->getData("id=$solUsrId",0,"persona,email");
    $solUsrAddr=null;
    global $gpoObj;
    if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
    $mailSettings=["domain"=>$gpoObj->getDomainKey($solData["idEmpresa"])];
    if (isset($usrData[0])) $usrData=$usrData[0];
    if (isset($usrData["persona"][0]) && isset($usrData["email"][0])) {
        $asunto="Solicitud $solFolio Cancelada";
        $mensaje="La Solicitud $solFolio ha sido cancelada con motivo: <b>$motivo</b>";
        $usrAddr=["address"=>$usrEMail,"name"=>replaceAccents($usrFullName)];
        $solUsrAddr=["address"=>$usrData["email"],"name"=>replaceAccents($usrData["persona"])];
        sendMail($asunto, $mensaje, $usrAddr, $solUsrAddr, null, null, $mailSettings);
        if (isset($invPrvId) && isset($invFolio)) {
            $usrData=$usrObj->getData("p.id=$invPrvId",0,"u.persona,u.email","u inner join proveedores p on u.nombre=p.codigo");
            // select u.persona,u.email from usuarios u inner join proveedores p on u.nombre=p.codigo where p.id=3098;
            if (isset($usrData[0])) $usrData=$usrData[0];
            if (isset($usrData["persona"][0]) && isset($usrData["email"][0])) {
                $asunto="Factura $invFolio Cancelada";
                $mensaje="La Factura $invFolio ha sido cancelada con motivo: <b>$motivo</b>";
                $prvAddr=["address"=>$usrData["email"],"name"=>replaceAccents($usrData["persona"])];
                sendMail($asunto, $mensaje, $solUsrAddr, $prvAddr, null, null, $mailSettings);
            }
        }
    }
    successNDie("Cancelación de Solicitud exitosa",["idUsuario"=>$usrId,"idSolicitud"=>$solId],"solpago");
    //echo("<div class='padt20 boldValue font14'></div>");
}
function isSaveReferral() {
    return "saveReferral"===($_REQUEST["action"]??"");
}
function doSaveReferral() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    sessionInit();
    if (!hasUser()) {
        errNDie("Error al Guardar Remision: Usuario desconocido",$baseData+["line"=>__LINE__]);
    }
    $invId=$_POST["invId"];
    if (!isset($invId[0])) {
        errNDie("Error al Guardar Remision: Factura no identificada",$baseData+["line"=>__LINE__]);
    }
    if (!isset($_POST["text"])) {
        errNDie("Error al Guardar Remision: No hay texto de remision",$baseData+["line"=>__LINE__]);
    }
    $text=$_POST["text"];
    global $invObj, $query;
    if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
    if (!$invObj->saveRecord(["id"=>$invId,"remision"=>$text])) {
        if (DBi::$errno>0)
            errNDie("Error al Guardar Remision: No se guardaron los datos",$baseData+["line"=>__LINE__,"errno"=>DBi::$errno,"error"=>DBi::$error,"query"=>$query,"log"=>$invObj->log]);
        else doclog("REMISION NO MODIFICADA","factura",$baseData+["line"=>__LINE__,"invId"=>$invId,"remision"=>$text,"query"=>$query,"log"=>$invObj->log]);
    }
    //doclog("REMISION MODIFICADA","factura",$baseData+["line"=>__LINE__,"invId"=>$invId,"remision"=>$text,"query"=>$query]);
    $usrId=getUser()->id;
    successNDie("REMISION MODIFICADA",["idUsuario"=>$usrId,"idFactura"=>$invId,"remision"=>$text],"factura");
}
function isSaveRequestObservations() {
    return "saveSolObs"===($_REQUEST["action"]??"");
}
function doSaveRequestObservations() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
    sessionInit();
    if (!hasUser()) {
        errNDie("Error al guardar Observaciones: Usuario desconocido",$baseData+["line"=>__LINE__]+$_POST);
    }
    $solId=$_POST["solid"]??"";
    if (!isset($solId[0])) {
        errNDie("Error al Guardar Observaciones: Solicitud no identificada",$baseData+["line"=>__LINE__]+$_POST);
    }
    if (!isset($_POST["text"])) {
        errNDie("Error al Guardar Observaciones: No hay observaciones",$baseData+["line"=>__LINE__]+$_POST);
    }
    $text=$_POST["text"]??"";
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj = new SolicitudPago();
    }
    /*
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        errNDie("La solicitud no existe",$_POST);
    }
    $solData=$solData[0];
    */
    DBi::autocommit(FALSE);
    global $query;
    $text=str_replace(["\r","\n"], ["\\r","\\n"], $text);
    if (!$solObj->saveRecord(["id"=>$solId,"observaciones"=>$text])) {
        $solQry=$query;
        DBi::rollback();
        DBi::autocommit(TRUE);
        errNDie("Error al Guardar Observaciones: No se guardaron los datos",$baseData+["line"=>__LINE__]+$_POST+["errno"=>DBi::$errno??0,"error"=>DBi::$error??"","query"=>$solQry,"log"=>$solObj->log]);
    }
    // toDo: Crear tabla observaciones, guardar usuario, solId, fecha(modifiedTime) y texto
    // toDo: En formato de solicitud de pago, en fila de observaciones, debajo de la nube poner otro botón que represente historico, tal vez que sean 3 rayas o algun icono para representar todos los cambios en observaciones. Al picarle que se vea una tabla con todos los cambios, con fecha y el usuario que lo hizo.
    doclog("OBSERVACIONES","solpago",$baseData+["line"=>__LINE__,"solId"=>$solId,"observaciones"=>$text]);
    $postStr=json_encode($_POST);
    echo("<div class='padt20 boldValue font14'>Solicitud guardada exitosamente</div><!-- POST = $postStr, QUERY = $query -->");
    DBi::commit();
    DBi::autocommit(TRUE);
}
function getSolFormaView($templateName,$solId,$solFolio,$bsKyMp=[]) {
    // quitar $templateName
    $base = file_get_contents(getBasePath()."templates/".$templateName);
    // respGralSolPago.html
    if (!isset($bsKyMp["%HOSTNAME%"][0])) $bsKyMp["%HOSTNAME%"]=$_SERVER["HTTP_ORIGIN"];
    if (!isset($bsKyMp["%RESPUESTA%"])) $bsKyMp["%RESPUESTA%"]="";
    //if (!isset($bsKyMp["%BUTTONS%"])) $bsKyMp["%BUTTONS%"]="";
    $isInteractive = (isset($bsKyMp["isInteractive"])&&$bsKyMp["isInteractive"]==="1");
    if (!isset($bsKyMp["%BTNSTY%"])) $bsKyMp["%BTNSTY%"]=(!$isInteractive)?"display:none;":"";
    if (!isset($bsKyMp["%ERRCLOSE%"])) $bsKyMp["%ERRCLOSE%"]="";
    ob_start();
    ob_implicit_flush(false);
    include "templates/solforma.php";
    $bsKyMp["%RESPUESTA%"].=ob_get_clean();
    $bsKyMp["%SOLID%"]=$solId;
    $bsKyMp["%SOLFOLIO%"]=$solFolio;
    return str_replace(array_keys($bsKyMp),array_values($bsKyMp),$base);
}
function getRespGralView($encabezado,$mensaje,$solId="",$solFolio="",$isErr=true,$isInteractive=true,$isNotice=true) {
    // $buttons="<input type=\"button\" value=\"CERRAR\" style=\"margin:2px;\" onclick=\"window.close();\">";
    $data = ["%ENCABEZADO%"=>$encabezado,"%RESPUESTA%"=>$mensaje,"%BTNSTY%"=>"","%ERRCLOSE%"=>""];
    $data["isInteractive"]=$isInteractive?"1":"0";
    $data["%BUTTONS%"]="<!-- 3 -->";
    if (!$isInteractive) {
        // $buttons="";
        $data["%BTNSTY%"]="display:none;";
        $data["%ERRCLOSE%"]=$isErr?"":" reloadOnClose";
    } else if ($isErr) {
        $style=" style='background-color:rgba(255,0,0,0.01);color:darkred;'"; // class='reloadOnClose'
    } else {
        $style=" class=\"reloadOnClose\"";
        $data["%BUTTONS%"]="<!-- AUTORIZAR, RECHAZAR, ANTERIOR, SIGUIENTE -->";
    }
    if ($isInteractive) {
        $browser = getBrowser();
        $isMSIE = ($browser==="Edge" || $browser==="IE");
        require_once "templates/generalScript.php";
        $data["<!-- PRE -->"]="<!DOCTYPE html><html xmlns=\"http://www.w3.org/1999/xhtml\"><head><meta charset=\"utf-8\">".($isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"")."<base href=\"{$_SERVER['HTTP_ORIGIN']}{$_SERVER['WEB_MD_PATH']}\" target=\"_self\"><title>RESPUESTA DE SOLICITUD DE PAGO</title>".($isMSIE?getPolyfillScript():"").getGeneralScript()."<link href=\"css/general.php\" rel=\"stylesheet\" type=\"text/css\"/><script src=\"https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js\"></script><script src=\"scripts/pdfviewer.php\" type=\"text/javascript\"></script><script src=\"scripts/respGralSolPago.php\" type=\"text/javascript\"></script></head><body>";
        $data["<!-- POS -->"]="</body></html>";
    }
    if ($isNotice)
        $data["%RESPUESTA%"]="<h2".($style??"").">$mensaje</h2>";
    return getSolFormaView("respGralSolPago.html", $solId, $solFolio, $data); // "%BUTTONS%"=>$buttons
}
function getPaymNoteView($msg,$solId="",$solFolio="",$isAuth=false,$isErr=true,$isInteractive=true) {
    return getRespGralView("SOLICITUD".($isAuth?" DE AUTORIZACI&Oacute;N":"")." DE PAGO $solFolio",$msg,$solId,$solFolio,$isErr,$isInteractive);
}
function sendRejectPaymMail($solId,$solFolio,$idUsuario,$idAutoriza,$idEmpresa) {
    global $tokObj,$usrObj;
    $msg="La solicitud de pago $solFolio ha sido rechazada";
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $asunto="Respuesta de Autorizacion de Pago $solFolio";
    $authUsrData=$usrObj->getData("id=$idAutoriza",0,"persona,email");
    if (isset($authUsrData[0])&&isset($authUsrData[0]["persona"][0])) {
        $msg.=" por ".replaceAccents($authUsrData[0]["persona"]);
        $msg=getPaymNoteView($msg,$solId,$solFolio,true,false,false);
        global $gpoObj;
        if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
        $mailSettings=["domain"=>$gpoObj->getDomainKey($idEmpresa)];
        if(!isset($tokObj)) {
            require_once "clases/Tokens.php";
            $tokObj=new Tokens();
        }
        $tokData=$tokObj->getData("refId=$solId and usrId!=$idAutoriza",0,"distinct usrId");
        foreach ($tokData as $idx => $otherAuth) {
            if (isset($otherAuth["usrId"][0])) {
                $otherAuthUsrData=$usrObj->getData("id=".$otherAuth["usrId"],0,"persona,email");
                if (isset($otherAuthUsrData[0])&&isset($otherAuthUsrData[0]["email"][0])) {
                    sendMail($asunto,$msg,
                        ["address"=>$authUsrData[0]["email"],"name"=>replaceAccents($authUsrData[0]["persona"])],
                        ["address"=>$otherAuthUsrData[0]["email"],"name"=>replaceAccents($otherAuthUsrData[0]["persona"])],
                        $cc??null,$bcc??null,$mailSettings
                    );
                }
            }
        }
        $usrData=$usrObj->getData("id=$idUsuario",0,"id,persona,email");
        if(isset($usrData[0])&&isset($usrData[0]["email"][0])) {
            sendMail($asunto,$msg,
                ["address"=>$authUsrData[0]["email"],"name"=>replaceAccents($authUsrData[0]["persona"])],
                ["address"=>$usrData[0]["email"],"name"=>replaceAccents($usrData[0]["persona"])],
                $cc??null,$bcc??null,$mailSettings);
        }
    }
}
function isGenPaymTextFile() {
    return "genPaymTextFile"===($_REQUEST["action"]??"");
}
function doGenPaymTextFile() {
    $solIdList=$_POST["ids"];
    $textResult="";
    $textError="";
    require_once "clases/SolicitudPago.php";
    //require_once "clases/Facturas.php";
    require_once "clases/OrdenesCompra.php";
    require_once "clases/Contrarrecibos.php";
    require_once "clases/Proveedores.php";
    require_once "clases/Usuarios.php";
    require_once "clases/Grupo.php";
    $solObj=new SolicitudPago();
    $invObj=new Facturas();
    $ordObj=new OrdenesCompra();
    $ctrObj=new Contrarrecibos();
    $prvObj=new Proveedores();
    $gpoObj=new Grupo();
    $usrObj=new Usuarios();
    $inicio="PAY485";
    $miBanco="BANAMEX";
    $codigoBanamex="000"; // 3 caracteres, sino rellenar con ceros a la izquierda
    $tipoPagoBanamex="072";
    $tipoPagoOtroBanco="001";
    $inicioCuentaBanamex="002";
    $codigoFijo="0501";
    $otroCodigoFijo="NN01"; // 4 caracteres
    $antepenultimoFijo="001";
    $penultimoFijo="00000";
    $ultimoFijo=str_repeat(9, 15);
    $resumenFijo="TRL";
    $monedaDefault="MXN";
    $pais="MEXICO"; // 6 caracteres
    $consecutive=0;
    $sumaMonto=0;
    $today=date("ymd");
    $pgI="<p class=\"marblk5\">"; //marblk5_1
    $pgF="</p>";
    foreach ($solIdList as $idx => $solId) {
        $solData=$solObj->getData("id=$solId");
        if (!isset($solData[0])) { $textError.="{$pgI}No se encontró en sistema la solicitud $solId{$pgF}";continue; }
        $solData=$solData[0];
        $solIdf=substr($solData["folio"], -3);
        if (isset($solData["idFactura"])) {
            $invData=$invObj->getData("id=$solData[idFactura]");
            if (!isset($invData[0])) { $textError.="{$pgI}No se encontró en sistema la factura de la solicitud $solId{$pgF}";continue; }
            $invData=$invData[0];
            $prvData=$prvObj->getData("codigo='$invData[codigoProveedor]'");
            $moneda=$invData["moneda"];
            $monto=$invData["total"];
            $folioFactura=$invData["folio"];
            if (!isset($folioFactura[0])) $folioFactura="[".substr($invData["uuid"],-8)."]";
            else if (isset($folioFactura[10])) $folioFactura=substr($folioFactura,-10);
            $folioReferencia2="FACT $folioFactura"; // "FACTURA $folioFactura";
        } else if (isset($solData["idOrden"])) {
            $ordData=$ordObj->getData("id=$solData[idOrden]");
            if (!isset($ordData[0])) { $textError.="{$pgI}No se encontró en sistema la orden de compra de la solicitud $solId{$pgF}";continue; }
            $ordData=$ordData[0];
            $prvData=$prvObj->getData("id=$ordData[idProveedor]");
            $moneda=$ordData["moneda"];
            $monto=$ordData["importe"];
            $folioOrden=$ordData["folio"];
            if (isset($folioOrden[10])) $folioOrden=substr($folioOrden, -10);
            $folioReferencia2="ORDC $folioOrden"; // "ORDEN C ".$ordData["folio"];
        } else if (isset($solData["idContrarrecibo"])) {
            $ctrData=$ctrObj->getData("id=$solData[idContrarrecibo]",0,"folio,codigoProveedor,total");
            if (!isset($ctrData[0])) { $textError.="{$pgI}No se encontró en sistema el contra recibo de la solicitud $solId{$pgF}";continue; }
            $ctrData=$ctrData[0];
            $prvData=$prvObj->getData("codigo='$ctrData[codigoProveedor]'");
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
            $ctfData=$ctfObj->getData("idContrarrecibo=$solData[idContrarrecibo]",0,"moneda");
            if (!isset($ctfData[0])) { $textError.="{$pgI}No se encontró en sistema el contra recibo de la solicitud $solId{$pgF}";continue; }
            $moneda=$ctfData[0]["moneda"];
            $monto=$ctrData["total"];
            $folioCR="$ctrData[folio]";
            if (isset($folioCR[10])) $folioCR=substr($folioCR, -10);
            $folioReferencia2="CTRC $folioCR";
            $solIdf=substr($folioCR, -3);
        } else { $textError.="{$pgI}La solicitud $solId no tiene ligada una factura o una orden de compra{$pgF}";continue; }
        if (!isset($prvData[0])) { $textError.="{$pgI}No se encontró en sistema al proveedor de la solicitud $solId{$pgF}";continue; }
        $prvData=$prvData[0];
        $banco=$prvData["banco"];
        $cuenta=$prvData["cuenta"];
        $lenCuenta=strlen($cuenta);
        $plCta= ($lenCuenta==1)?"":"s";
        if (!in_array($lenCuenta, [10,11,16,18])) {
            $ctatxt="";
            if ($lenCuenta>0) $ctatxt="válida ($cuenta). La cuenta tiene $lenCuenta dígito{$plCta}.";
            else $ctatxt="bancaria.";
            $textError.="{$pgI}Solicitud $solId : El proveedor $prvData[codigo] no tiene una cuenta {$ctatxt}{$pgF}";
            continue;
        }
        $tipoCuenta=($lenCuenta==18)?5:(($lenCuenta==16)?3:1);
        // cuentas bancomer tienen 10 digitos
        // cuentas banamex tienen 11 digitos
        // clabe banamex tiene 18 digitos
        // tarjeta tiene 16 digitos
        $esBanamex=(strpos(strtoupper($banco), $miBanco)!==FALSE)||(strlen($cuenta)==18 && substr($cuenta, 0,3)===$inicioCuentaBanamex);
        $usrData=$usrObj->getData("nombre='$prvData[codigo]'");
        if (!isset($usrData[0])) {
            $textError.="{$pgI}Solicitud $solId : El proveedor $prvData[codigo] no tiene cuenta de usuario en el portal InvoiceCheck{$pgF}";
            /*continue;*/
            $correo="";
        } else {
            $usrData=$usrData[0];
            $correo=$usrData["email"]??"";
        }
        $gpoData=$gpoObj->getData("id=$solData[idEmpresa]");
        if (!isset($gpoData[0])) { $textError.="{$pgI}No se encontró en el sistema la empresa receptora de la solicitud $solId{$pgF}";continue; }
        $gpoData=$gpoData[0];
        $referencia=str_pad("SOL".$today.$gpoData["cut"].$solIdf,6+15," ");
        $consecutive++;
        $sumaMonto+=$monto;
        // EMPEZANDO A CAPTURAR LINEA DE DATOS
        $textLine=$inicio;
        $textLine.=str_repeat(" ", 10);
        $textLine.=$today;
        $textLine.=$esBanamex?$tipoPagoBanamex:$tipoPagoOtroBanco;
        $textLine.=$referencia;
        $textLine.=str_pad($consecutive, 2, "0", STR_PAD_LEFT);
        $textLine.=str_pad($prvData["rfc"], 20);
        if (!isset($moneda[0])) $moneda=$monedaDefault;
        $textLine.=str_pad($moneda, 3);
        $textLine.=str_pad($prvData["codigo"], 20);
        $importe=str_replace([",",".","$"], "", formatCurrency($monto, $moneda));
        $textLine.=str_pad($importe,15,"0",STR_PAD_LEFT).str_repeat(" ",6);
        $referenciaFija=substr($gpoData["alias"],0,6)." A ".str_replace("-","",$prvData["codigo"])." ".$folioReferencia2;
        if (isset($referenciaFija[35])) $referenciaFija=substr($referenciaFija,0,35);
        $referencia1=$prvData["referencia1"]??"";
        if (isset($referencia1[35])) $referencia1=substr($referencia1,0,35);
        else if (!isset($referencia1[0]))
            $referencia1=$referenciaFija; // substr($gpoData["alias"],0,6)." A ".substr($prvData["razonSocial"],0,24);
        $textLine.=str_pad($referencia1,35);
        $referencia2=$prvData["referencia2"]??"";
        if (isset($referencia2[35])) $referencia2=substr($referencia2, 0, 35);
        else if (!isset($referencia2[0]))
            $referencia2=$referenciaFija; // $folioReferencia2;
        $textLine.=str_pad($referencia2,35);
        $referencia3=$prvData["referencia3"]??"";
        if (isset($referencia3[35])) $referencia3=substr($referencia3, 0, 35);
        else if (!isset($referencia3[0]))
            $referencia3=$referenciaFija;
        $textLine.=str_pad($referencia3,35);
        $referencia4=$prvData["referencia4"]??"";
        if (isset($referencia4[35])) $referencia4=substr($referencia4, 0, 35);
        $textLine.=str_pad($referencia4,35);
        $textLine.=$codigoFijo;
        $textLine.=str_pad(substr($prvData["razonSocial"],0,35),35);
        $textLine.=str_repeat(" ", 45);
        $textLine.=str_pad(isset($prvData["calle"][0])?substr($prvData["calle"],0,35):"X",35);
        $textLine.=str_pad(isset($prvData["colonia"][0])?substr($prvData["colonia"],0,35):"X",35);
        $textLine.=str_pad(isset($prvData["ciudad"][0])?substr($prvData["ciudad"],0,15):"X",15);
        $textLine.=str_pad(isset($prvData["estado"][0])?substr($prvData["estado"],0,2):"X",2," ",STR_PAD_LEFT);
        $textLine.=str_pad(isset($prvData["codigoPostal"][0])?substr($prvData["codigoPostal"],0,12):"X",12);
        $textLine.=str_pad(isset($prvData["telefono"][0])?substr($prvData["telefono"],0,16):"X",16);
        $textLine.=$esBanamex?$codigoBanamex:substr($cuenta,0,3);
        $textLine.=str_repeat(" ", 8);
        $textLine.=str_pad($cuenta,35);
        $textLine.=str_pad($tipoCuenta,2,0,STR_PAD_LEFT);
        $textLine.=str_pad($pais,6+41);
        $textLine.=$otroCodigoFijo;
        $textLine.=str_pad($gpoData["cuentaCargo"]??"",11," ",STR_PAD_LEFT);
        $textLine.=str_pad($antepenultimoFijo,75," ",STR_PAD_LEFT);
        $textLine.=str_pad($penultimoFijo,55," ",STR_PAD_LEFT);
        $textLine=str_replace([".",",",";",":","<",">","-","_","¬","°","|","!","\"","#","$","%","&","/","(",")","=","?","'","¡","¿","´","¨","+","*","[","{","]","}","~","^","`","\\"]," ",replaceAccents($textLine));
        // preg_replace("/[^a-zA-Z0-9\-@]/", " ", $textLine);
        $textLine.=str_pad($correo,50);
        $textLine.=str_pad($ultimoFijo,16);
        $textLine.=str_pad(isset($correo[0])?"E-Mail":"None",267);
        $textResult.="$textLine\n";
    }
    if ($consecutive>0) {
        $sumaFormato=formatCurrency($sumaMonto);
        $textResult.=$resumenFijo.str_pad($consecutive,15,0,STR_PAD_LEFT).str_pad(str_replace([",",".","$"], "", $sumaFormato),15,"0",STR_PAD_LEFT).str_pad($consecutive,30,0,STR_PAD_LEFT).str_repeat(" ", 37)."\n";
    }
    if (isset($textError[0])) {
        if (!isset($textResult[0]))
            $textError.="{$pgI}<B>No hay solicitudes válidas para generar el documento para pago.</B>{$pgF}";
        else {
            $totSol=count($solIdList);
            $plOK=($consecutive==1)?"":"es";
            $totErr=$totSol-$consecutive;
            $plErr1=($totErr==1)?"ó":"aron";
            $plErr2=($totErr==1)?"":"es";
            $textError.="{$pgI}<B>Se detect{$plErr1} $totErr solicitud{$plErr2} con error. Se generó documento para pago con $consecutive solicitud{$plOK} de {$totSol}.</B>{$pgF}";
        }
        $textResult.="|";
    }
    echo $textResult.$textError;
}
// CREA FUNCION QUE RECIBE TOKEN
function isAdminFacturaService() {
    return isset($_POST["adminfactura"]);
}
function getAdminFacturaService() {
    global $invObj;
    //$invObj->rows_per_page = 0;
    $result = [];
    if (isset($_POST["proveedores"])) {
        $prv = $_POST["proveedores"];
        $prvfieldarray=[];
        foreach ($prv as $key=>$value) $prvfieldarray[$key]=$value;
        if (count($prvfieldarray)>0) {
            if(!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj = new Proveedores();
            }
            $prvObj->pageno=1;
            $prvObj->rows_per_page=100;
            $prvData = $prvObj->getDataByFieldArray($prvfieldarray);
            if ($prvObj->numrows==1) $result["proveedores"]=$prvData[0];
            $codigoProveedor = [];
            foreach ($prvData as $prvItem) $codigoProveedor[]=$prvItem["codigo"];
        }
    }
    if (isset($_POST["grupo"])) {
        $gpo = $_POST["grupo"];
        $gpofieldarray=[];
        foreach ($gpo as $key=>$value) $gpofieldarray[$key]=$value;
        if (count($gpofieldarray)>0) {
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj = new Grupo();
            }
            $gpoObj->pageno=1;
            $gpoObj->rows_per_page=100;
            $gpoData = $gpoObj->getDataByFieldArray($gpofieldarray);
            if ($gpoObj->numrows==1) $result["grupo"]=$gpoData[0];
            $rfcGrupo = [];
            foreach ($gpoData as $gpoItem) $rfcGrupo[]=$gpoItem["rfc"];
        }
    }
    if (isset($_POST["contrarrecibos"])) {
        $ctr = $_POST["contrarrecibos"];
        $ctrfieldarray=[];
        foreach ($ctr as $key=>$value) $ctrfieldarray[$key]=$value;
        if (count($ctrfieldarray)>0) {
            if (!isset($ctrObj)) {
                require_once "clases/Contrarrecibos.php";
                $ctrObj = new Contrarrecibos();
            }
            $ctrObj->pageno=1;
            $ctrObj->rows_per_page=100;
            $ctrData = $ctrObj->getDataByFieldArray($ctrfieldarray);
            if ($ctrObj->numrows==1) $result["contrarrecibos"]=$ctrData[0];
            $idCtr = [];
            foreach ($ctrData as $ctrItem) $idCtr[]=$ctrItem["id"];
            if (isset($idCtr[0])) {
                if (!isset($ctfObj)) {
                    require_once "clases/Contrafacturas.php";
                    $ctfObj = new Contrafacturas();
                }
                $ctfObj->pageno=1;
                $ctfObj->rows_per_page=1000;
                $ctfData = $ctfObj->getDataByFieldArray(["idContrarrecibo"=>$idCtr],0,"idContrarrecibo,idFactura");
                $idCtf = [];
                $ctrMap = [];
                $ctfMap = [];
                foreach ($ctfData as $ctfItem) {
                    $idCtf[]=$ctfItem["idFactura"];
                    if (!isset($ctrMap[$ctfItem["idContrarrecibo"]])) {
                        $ctrMap[$ctfItem["idContrarrecibo"]]=[];
                    }
                    $ctrMap[$ctfItem["idContrarrecibo"]][]=$ctfItem["idFactura"];
                    $ctfMap[$ctfItem["idFactura"]]=$ctfItem["idContrarrecibo"];
                }
            }
        }
    }
    $hasFacturas=isset($_POST["facturas"]);
    if ($hasFacturas||!empty($codigoProveedor)||!empty($rfcGrupo)||!empty($idCtf)) {
        $fieldarray=[];
        if ($hasFacturas) {
            $fac = $_POST["facturas"];
            foreach ($fac as $key=>$value) $fieldarray[$key]=$value;
            if (isset($fieldarray["id"]) && !isset($ctfMap[$fieldarray["id"]])) {
                unset($result["contrarrecibos"]); // No coincide
            }
        } else if (!empty($idCtf)) {
            if (isset($idCtf[0]) && !isset($idCtf[1])) $fieldarray["id"]=$idCtf[0];
            else $fieldarray["id"]=$idCtf;
        }
        if (!empty($codigoProveedor)&&!isset($fieldarray["id"])) $fieldarray["codigoProveedor"]=$codigoProveedor;
        if (!empty($rfcGrupo)&&!isset($fieldarray["id"])) $fieldarray["rfcGrupo"]=$rfcGrupo;
        $invObj->pageno=isset($_POST["pageno"])?+$_POST["pageno"]:1;
        $invObj->rows_per_page=isset($_POST["rowsperpage"])?+$_POST["rowsperpage"]:10;
        $facData = $invObj->getDataByFieldArray($fieldarray);
        $result["count"]=$invObj->numrows;
        $result["log"]=$invObj->log;
        if ($result["count"]==1) {
            $fact=$facData[0];
            $fId=$fact["id"];
            $fTC=$fact["tipoComprobante"];
            $result["facturas"]=$fact;
            if (empty($result["proveedores"])) {
                if(!isset($prvObj)) {
                    require_once "clases/Proveedores.php";
                    $prvObj = new Proveedores();
                }
                $prvObj->rows_per_page = 0;
                $result["proveedores"] = $prvObj->getData("codigo='".$fact["codigoProveedor"]."'")[0];
            }
            if (empty($result["grupo"])) {
                if(!isset($gpoObj)) {
                    require_once "clases/Grupo.php";
                    $gpoObj = new Grupo();
                }
                $gpoObj->rows_per_page = 0;
                $result["grupo"] = $gpoObj->getData("rfc='".$fact["rfcGrupo"]."'")[0];
            }
            if(!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
            }
            $prcObj->rows_per_page = 0;
            $result["proceso"] = $prcObj->getData("identif='$fId'");
            if (isset($result["proceso"][0])) {
                $users=[];
                foreach ($result["proceso"] as &$prcItem) {
                    $users[]=$prcItem["usuario"];
                    $procFecha = $prcItem['fecha'];
                    $soloFecha = str_replace("-","‑",substr($procFecha,0,10));
                    $soloHora = substr($procFecha,11);
                    $prcItem['fecha']=$soloFecha;
                    $prcItem['hora']=$soloHora;
                    $prcItem['fullfecha']=$procFecha;
                    unset($prcItem);
                }
                if (isset($users[0])) {
                    if(!isset($usrObj)) {
                        require_once "clases/Usuarios.php";
                        $usrObj = new Usuarios();
                    }
                    $usrObj->rows_per_page = 0;
                    $usrData = $usrObj->getDataByFieldArray(["nombre"=>$users],0,"id,nombre,persona,email,observaciones");
                    $usrList=[];
                    foreach($usrData as $usrItem) {
                        $nombre = $usrItem["nombre"];
                        unset($usrItem["nombre"]);
                        $usrList[$nombre]=$usrItem;
                    }
                    if (isset($usrData[0])) $result["usuarios"]=$usrList;
                }
            }
            if (isset($fTC[1])) {
                $fTC=$fTC[0];
                $fact["tipoComprobante"]=$fTC;
            }
            switch($fTC) {
                case "i":
                    //if (!empty($fact["idReciboPago"]))
                case "e":
                    if(!isset($cptObj)) {
                        require_once "clases/Conceptos.php";
                        $cptObj = new Conceptos();
                    }
                    $cptObj->rows_per_page = 0;
                    $result["conceptos"] = $cptObj->getData("idFactura=$fId");
                    if(!isset($ctfObj)) {
                        require_once "clases/Contrafacturas.php";
                        $ctfObj = new Contrafacturas();
                    }
                    $contraReciboId = $ctfObj->getValue("idFactura",$fId,"idContrarrecibo");
                    if (!empty($contraReciboId)) {
                        if (isset($result["contrarrecibos"]) && $result["contrarrecibos"]["id"]===$contraReciboId) break; // Ya 
                        if(!isset($ctrObj)) {
                            require_once "clases/Contrarrecibos.php";
                            $ctrObj = new Contrarrecibos();
                        }
                        $ctrObj->rows_per_page = 0;
                        $result["contrarrecibos"] = $ctrObj->getData("id=$contraReciboId")[0];
                    }
                    break;
                case "p":
                    $invObj->rows_per_page = 0;
                    $result["pagadas"] = $invObj->getData("idReciboPago=$fId",0,"id,fechaFactura,rfcGrupo,codigoProveedor,folio,total");
                    break;
                case "t":
            }
        } else if ($result["count"]>1) {
            if ($invObj->numrows>$invObj->rows_per_page) $result["limit"]=$invObj->rows_per_page;
            $result["currPage"]=$invObj->pageno;
            $result["lastPage"]=$invObj->lastpage;
            $result["preview"]=[];
            sessionInit();
            foreach ($facData as $facItem) {
                $previewItem=[];
                foreach(["id","rfcGrupo","codigoProveedor","folio","fechaFactura","total"] as $facReg) {
                    $previewItem[$facReg]=$facItem[$facReg];
                }
                if (isset($_SESSION["gpoCodigoOpt"][$previewItem["rfcGrupo"]]))
                    $previewItem["empresa"]=$_SESSION["gpoCodigoOpt"][$previewItem["rfcGrupo"]];
                else
                    $previewItem["empresa"]=$previewItem["rfcGrupo"];
                unset($previewItem["rfcGrupo"]);
                $previewItem["proveedor"]=$previewItem["codigoProveedor"];
                unset($previewItem["codigoProveedor"]);
                $previewItem["fecha"]=$previewItem["fechaFactura"];
                unset($previewItem["fechaFactura"]);
                $result["preview"][]=$previewItem;
            }
        }
    }
    if (empty($fac)&&empty($prv)&&empty($gpo)) {
        $count=$invObj->getValue("","","count(1)");
        $result["count"]=$count;
        $result["log"]=$invObj->log;
    }
    echo json_encode($result);
}
function isAdminQueryService() {
    return isset($_POST["menu_accion"]) && isset($_POST["command"]) && $_POST["menu_accion"]==="Admin Factura";
}
function getAdminQueryService() {
    switch ($_POST["command"]) {
        case "Buscar": getAdminFindQuery(); break;
        case "Test": echo "Prueba de Admin Find Query Service"; break;
    }
}
function isQueryCFDI() {
    return isset($_POST["accion"]) && $_POST["accion"]==="consultaCFDI";
}
function getQueryCFDI() {
    if (empty($_POST["id"])) {
        errNDie("Solicitud incompleta, falta el id de la factura");
    }
    global $invObj;
    //$invObj->rows_per_page = 0;
    $invId=$_POST["id"];
    $data = $invObj->getData("id=$invId",0,"mensajeCFDI,estadoCFDI,cancelableCFDI,canceladoCFDI,solicitaCFDI,consultaCFDI");
    if (isset($data[0])) {
        $row=$data[0];
        $mensaje=$row["mensajeCFDI"];
        $estado=$row["estadoCFDI"];
        $solicita=$row["solicitaCFDI"];
        $cancelable=$row["cancelableCFDI"];
        $cancelado=$row["canceladoCFDI"];
        $consulta=$row["consultaCFDI"];
        $result=["result"=>"success","id"=>$invId,"mensaje"=>$mensaje,"estado"=>$estado,"solicita"=>$solicita,"cancelable"=>$cancelable,"cancelado"=>$cancelado,"consulta"=>$consulta];
        if (empty($solicita) && !empty($cancelable)) {
            if ($cancelable==="ERR"||$cancelable==="601"||$cancelable==="602") {
                switch($cancelable) {
                    case "ERR": $result["title"]="La verificación no pudo realizarse. Consulte al administrador del portal."; break;
                    case "601": $result["title"]="La verificación no fue reconocida (601). Consulte al administrador del portal."; break;
                    case "602": $result["title"]="El comprobante no fue encontrado (602). Consulte al administrador del portal."; break;
                }
                $result["src"]="imagenes/icons/statusErrorUp.png";
                $result["onclick"]=1;
                //$result["className"]="vbottom";
            } else if ($estado==="Vigente" && empty($cancelado)) {
                $result["src"]="imagenes/icons/statusRightUp.png";
                //$result["className"]="vbottom pointer";
                $result["onclick"]=1;
                $result["title"]="CFDI Vigente. Ultima consulta: $consulta.\nPresione para verificar nuevamente.";
            } else {
                $result["src"]="imagenes/icons/statusWrongDn.png";
                //$result["className"]="vbottom";
                $result["title"]="CFDI $estado. $cancelable";
                if (!empty($cancelado)) {
                    $result["title"].=" $cancelado";
                    $result["status"]=Facturas::statusnToRealStatus(Facturas::STATUS_CANCELADOSAT);
                }
            }
        } else {
            $result["src"]="imagenes/icons/statusWaitDn.png";
            //$result["className"]="vbottom";
            $result["loop"]=1;
        }
    } else $result=["result"=>"error","message"=>"No se encontraron datos de la factura $invId"];
    echo json_encode($result);
}
function isValidateCFDI() {
    return isset($_POST["accion"]) && $_POST["accion"]==="validaCFDI";
}
function doValidateCFDI() {
    echo json_encode(["result"=>"check", "message"=>"Solicitud para validar CFDI recibida"]);
}
function isVerifyCFDI() {
    return isset($_POST["accion"]) && $_POST["accion"]==="verificaCFDI";
}
function getVerifyCFDI() {
    if (empty($_POST["id"])) {
        errNDie("Solicitud incompleta, falta el id de la factura");
    }
    global $invObj;
    //$invObj->rows_per_page = 0;
    // ToDo: update solicitaCFDI=current_timestamp where id=POST[id]
    //require_once "clases/Facturas.php";
    $invId=$_POST["id"];
    $fieldArray = ["id"=>$invId, "solicitaCFDI"=>new DBExpression("current_timestamp()"), "cancelableCFDI"=>NULL];
    if ($invObj->saveRecord($fieldArray)) {
        global $prcObj;
        if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
        sessionInit();
        $prcObj->cambioFactura($invId, "SATCheck", hasUser()?getUser()->nombre:"nouser", false, "VerificaSAT");
        echo json_encode(["result"=>"success","affectedRows"=>DBi::$affected_rows??0,"info"=>DBi::$query_info??"","warnings"=>DBi::$warnings??"","errno"=>DBi::$errno??0,"error"=>DBi::$error??""]);
    } else echo json_encode(["result"=>"error","message"=>"No se actualizó factura $invId","affectedRows"=>DBi::$affected_rows??0,"info"=>DBi::$query_info??"","warnings"=>DBi::$warnings??"","errno"=>DBi::$errno??0,"error"=>DBi::$error??""]);
}
function isAddWarehouseEntry() {
    return ($_POST["accion"]??"")==="addWHEntry";
}
function doAddWarehouseEntry() {
    sessionInit();
    if (!hasUser()) reloadNDie("Su sesión ha caducado, ingrese con su usuario nuevamente");
    $invId=$_POST["id"]??"";
    if (!isset($invId[0])) errNDie("No se identifica la factura a modificar");
    global $invObj;
    $invData=$invObj->getData("id=$invId",0,"ea,codigoProveedor prv,folio,right(uuid,10) uuid,date(fechaFactura) fecha,ubicacion");
    if (!isset($invData[0])) errNDie("No existe la factura indicada");
    $invData=$invData[0];
    $ea=$invData["ea"]??"";
    if ($ea==="-1") errNDie("No está permitido Anexar Entrada de Almacén",["ea"=>"-1"]);
    if (!isset($_FILES['file'])) errNDie("No se recibió archivo",["post"=>$_POST,"files"=>$_FILES]);
    $file=$_FILES["file"];
    $ftmpn = $file["tmp_name"];
    $fname = $file["name"];
    $ftype = $file["type"];
    $fsize = $file["size"];
    $ferror = $file["error"];
    if ($ferror!=0) {
        $msg=fileCodeToMessage($ferror,["filename"=>$fname]);
        errNDie("Ocurrió un error en la descarga del archivo",["file"=>$file,"errmsg"=>isset($msg[0])?$msg:"Codigo de error desconocido"]);
    }
    if ($ftype!=="application/pdf") errNDie("El archivo debe tener formato PDF",["file"=>$file]);
    if ($fsize<100) errNDie("El archivo está vacío",["file"=>$file]);
    $cp=trim(str_replace("-", "", $invData["prv"]));
    $folio=trim($invData["folio"]??"");
    if (!isset($folio[0])) $folio=trim($invData["uuid"]??"");
    if (isset($folio[10])) $folio=substr($folio, -10);
    $fecha=substr(trim(str_replace("-","", $invData["fecha"])),2,6);
    $sysPath=$_SERVER["DOCUMENT_ROOT"];
    $webPath=$_SERVER["HTTP_ORIGIN"];
    $appPath=$invData["ubicacion"];
    $newPath=$appPath."EA_{$cp}_{$folio}_{$fecha}";
    $relName=$newPath.".pdf";
    $absName=$sysPath.$relName;
    if (!is_dir($sysPath.$appPath)) {
        if(mkdir($sysPath.$appPath,0777,true)) chmod($sysPath.$appPath, 0777);
        else errNDie("La ruta para guardar el archivo no existe",["abspath"=>$sysPath.$appPath,"file"=>$file,"data"=>$invData]);
    }
    if (file_exists($absName)) {
        rename($absName, $sysPath.$newPath.date("_YmdHis", filemtime($absName)).".pdf");
        sleep(3);
    }
    if (!move_uploaded_file($ftmpn, $absName)) errNDie("No se pudo agregar Entrada de Almacen",["absname"=>$absName,"file"=>$file,"data"=>$invData,"moveError"=>error_get_last()]);
    if (!$invObj->saveRecord(["id"=>$invId, "ea"=>"1"])) {
        global $query;
        errNDie("No se pudo guardar Entrada de Almacen",["errors"=>DBi::$errors,"query"=>$query,"absname"=>$absName,"file"=>$file,"data"=>$invData]);
    }
    global $firObj;
    if (!isset($firObj)) {
        require_once "clases/Firmas.php";
        $firObj=new Firmas();
    }
    $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"ea", "idReferencia"=>$invId, "accion"=>"agrega", "motivo"=>$relName]);
    successNDie("Entrada de Almacén agregada satisfactoriamente",["url"=>$relName]);
}
function isDelWarehouseEntry() {
    return ($_POST["accion"]??"")==="delWHEntry";
}
function doDelWarehouseEntry() {
    sessionInit();
    if (!hasUser()) reloadNDie("Su sesión ha caducado, ingrese con su usuario nuevamente");
    if (!validaPerfil(["Administrador","Sistemas","Elimina Documentos"])) reloadNDie("No tiene permiso para eliminar documentos");
    $invId=$_POST["id"]??"";
    if (!isset($invId[0])) errNDie("No se recibió factura a modificar");
    global $invObj;
    $invData=$invObj->getData("id=$invId",0,"ea,codigoProveedor prv,folio,right(uuid,10) uuid,date(fechaFactura) fecha,ubicacion");
    if (!isset($invData[0])) errNDie("No existe la factura indicada");
    $invData=$invData[0];
    $ea=$invData["ea"];
    if ($ea!=="1") errNDie("No existe Entrada de Almacen",["ea"=>$ea]);
    $cp=trim(str_replace("-", "", $invData["prv"]));
    $folio=trim($invData["folio"]??"");
    if (!isset($folio[0])) $folio=trim($invData["uuid"]??"");
    $fecha=substr(trim(str_replace("-","", $invData["fecha"])),2);
    $sysPath=$_SERVER["DOCUMENT_ROOT"];
    $webPath = $_SERVER["HTTP_ORIGIN"];
    $newPath=$invData["ubicacion"]."EA_{$cp}_{$folio}_{$fecha}";
    $absName=$sysPath.$newPath.".pdf";
    if (!file_exists($absName)) errNDie("No existe el archivo a eliminar",["filename"=>$absName,"exists"=>false]);
    //if (!unlink($absName)) errNDie("No se pudo eliminar la Entrada de Almacen",["filename"=>$absName]);
    if (!rename($absName, $sysPath.$newPath.date("_YmdHis", filemtime($absName)).".pdf")) errNDie("No se pudo eliminar la Entrada de Almacen",["filename"=>$absName,"exists"=>true,"failkey"=>"rename","moveError"=>error_get_last()]);
    sleep(3);
    if (!$invObj->saveRecord(["id"=>$invId,"ea"=>0])) {
        global $query;
        errNDie("No se pudo eliminar la Entrada de Almacen",["errors"=>DBi::$errors,"query"=>$query,"file"=>$file,"data"=>$invData,"absname"=>$absName,"failkey"=>"query"]);
    }
    global $firObj;
    if (!isset($firObj)) {
        require_once "clases/Firmas.php";
        $firObj=new Firmas();
    }
    $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"ea", "idReferencia"=>$invId, "accion"=>"elimina", "motivo"=>$_POST["motivo"]??""]);
    successNDie("Entrada de Almacén eliminada satisfactoriamente");
}
function isDisableWarehouseEntry() {
    return ($_POST["accion"]??"")==="disWHEntry";
}
function doDisableWarehouseEntry() {
    sessionInit();
    if (!hasUser()) reloadNDie("Su sesión ha caducado, ingrese con su usuario nuevamente");
    if (!validaPerfil(["Administrador","Sistemas","Bloquea Entrada Almacen"])) reloadNDie("No tiene permiso para deshabilitar entrada de almacén");
    $invId=$_POST["id"]??"";
    if (!isset($invId[0])) errNDie("No se recibió factura a modificar");
    $currEA=$_POST["ea"]??"";
    global $invObj;
    $invData=$invObj->getData("id=$invId",0,"ea");
    if (!isset($invData[0])) errNDie("No existe la factura indicada");
    $invData=$invData[0];
    $ea0=$invData["ea"];
    if ($ea0==Facturas::EA_CON) errNDie("La factura ya cuenta con Entrada de Almacen");
    if (isset($currEA[0]) && $ea0==$currEA) {
        $accion=($ea0==Facturas::EA_NA?"des":"")."habilita";
        successNDie("Entrada de Almacén ya {$accion}da",["ea"=>$ea0]);
    }
    $ea=($ea0==Facturas::EA_SIN)?Facturas::EA_NA:Facturas::EA_SIN;
    $motivo=$_POST["motivo"]??"";
    if ($ea===Facturas::EA_NA && !isset($motivo[0])) errNDie("Debe indicar un motivo para deshabilitar");
    $accion=($ea==Facturas::EA_NA?"des":"")."habilita";
    if (!$invObj->saveRecord(["id"=>$invId,"ea"=>$ea])) {
        global $query;
        errNDie("No se pudo {$accion}r Entrada de Almacen",["error"=>DBi::$errors,"query"=>$query,"ea"=>$ea]);
    }
    global $firObj;
    if (!isset($firObj)) {
        require_once "clases/Firmas.php";
        $firObj=new Firmas();
    }
    $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"ea", "idReferencia"=>$invId, "accion"=>$accion, "motivo"=>$motivo]);
    successNDie("Entrada de Almacén {$accion}da",["ea"=>$ea]);
}
function isAcceptInvoice() {
    return ($_POST["accion"]??"")==="acceptInvoice";
}
function doAcceptInvoice() {
    sessionInit();
    if (!hasUser()) {
        echoJsNDie("refresh","Sin sesion");
    }
    $fId=$_POST["id"]??"";
    if (!isset($fId[0])) errNDie("No se recibió identificador de factura");
    $logData=["id"=>$fId];
    global $invObj,$query;
    $invData=$invObj->getData(appendPostIntegerToQuery("id", "id", false),0,"tipoComprobante,statusn,status");
    if (!isset($invData[0]["tipoComprobante"])) errNDie("No existe la factura indicada",$_POST+["query"=>$query,"errors"=>DBi::$errors]);
    $invData=$invData[0];
    if (!isset($invData["statusn"][0])) errNDie("Factura inválida",$_POST+["query"=>$query,"log"=>$logData]);
    $oldStatusn=+$invData["statusn"];
    $newStatusn=$oldStatusn|Facturas::STATUS_ACEPTADO;
    $fieldArray=[];
    if ($oldStatusn!==$newStatusn) {
        $fieldArray["statusn"]=$newStatusn;
        $logData["old_statusn"]=$oldStatusn;
        $logData["new_statusn"]=$newStatusn;
        $status=Facturas::statusnToDetailStatus($newStatusn,$invData["tipoComprobante"]);
        $fieldArray["status"]=$status;
        $logData["old_status"]=$invData["status"];
        $logData["new_status"]=$status;
    } else $status=$invData["status"];
    $pedido=$_POST["f_numpedido"]??"";
    $oPedido=$_POST["fold_numpedido"]??"";
    if ($pedido!==$oPedido) {
        $fieldArray["pedido"]=$pedido;
        $logData["old_pedido"]=$oPedido;
        $logData["new_pedido"]=$pedido;
    }
    $remision=$_POST["f_numremision"]??"";
    $oRemision=$_POST["fold_numremision"]??"";
    if ($remision!==$oRemision) {
        $fieldArray["remision"]=$remision;
        $logData["old_remision"]=$oRemision;
        $logData["new_remision"]=$remision;
    }
    $articulos=$_POST["f_articulo"]??[];
    $oArticulos=$_POST["fold_articulo"]??[];
    DBi::autocommit(false);
    if (!empty($articulos)) {
        global $cptObj;
        if (!isset($cptObj)) {
            require_once "clases/Conceptos.php";
            $cptObj = new Conceptos();
        }
        $cptObj->rows_per_page  = 0;
        foreach($articulos as $cptId=>$cptCode) {
            $oldCode=$oArticulos[$cptId]??null;
            if (!isset($oldCode)) $logData["concepto[$cptId]"]=$cptCode;
            else if ($cptCode!==$oldCode) {
                if (strpos($cptCode, ' ')!==false) {
                    $logData["concepto[$cptId]"]=$cptCode;
                    DBi::rollback();
                    DBi::autocommit(true);
                    errNDie("El código de concepto no debe incluir espacios",$_POST+["log"=>$logData]);
                }
                $logData["old_concepto[$cptId]"]=$oldCode;
                $logData["new_concepto[$cptId]"]=$cptCode;
                DBi::clearErrors();
                if ($cptObj->saveRecord(["id"=>$cptId,"codigoArticulo"=>$cptCode])) {
                    $logData["save_concepto[$cptId]"]="TRUE";
                    $saved=true;
                } else if (isset(DBi::$errors[0])) {
                    $logData["save_concepto[$cptId]"]=json_encode(DBi::$errors);
                    DBi::rollback();
                    DBi::autocommit(true);
                    errNDie("No fue posible actualizar la factura",$_POST+["query"=>$query,"errors"=>DBi::$errors,"log"=>$logData]);
                } else $logData["save_concepto[$cptId]"]="FALSE";
            }
        }
    }
    if (isset(array_keys($fieldArray)[0])) {
        $fieldArray["id"]=$fId;
        DBi::clearErrors();
        if ($invObj->saveRecord($fieldArray)) {
            $logData["save_factura[$fId]"]="TRUE";
            $saved=true;
            if (isset($fieldArray["statusn"])) {
                global $prcObj;
                if(!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
                $prcObj->cambioFactura($fId, $status, getUser()->nombre, false, "acceptInvoice");
            }
        } else if (isset(DBi::$errors[0])) {
            $logData["save_factura[$fId]"]=json_encode(DBi::$errors);
            DBi::rollback();
            DBi::autocommit(true);
            errNDie("No fue posible actualizar la factura",$_POST+["query"=>$query,"errors"=>DBi::$errors,"log"=>$logData]);
        } else $logData["save_factura[$fId]"]="FALSE";
    }
    if ($saved??false) {
        DBi::commit();
        DBi::autocommit(true);
        successNDie("Factura aceptada satisfactoriamente",["statusn"=>$newStatusn,"status"=>$status,"pedido"=>$pedido,"remision"=>$remision,"log"=>$logData],"factura");
    }
    DBi::rollback();
    DBi::autocommit(true);
    echoJsNDie("ignore","Factura sin cambios",["log"=>$logData],"factura");
}
function isTempClrRPInInv() {
    return ($_POST["action"]??"")==="clrRPInInv";
}
function doTempClrRPInInv() {
    if (!isset($_POST["fId"])) errNDie("Falta indicar factura",$_POST);
    $factId=$_POST["fId"];
    $query="UPDATE facturas set idReciboPago=null, fechaReciboPago=null, saldoReciboPago=null, statusReciboPago=null where id=$factId";
    $res = DBi::query($query);
    if ($res) successNDie("Datos de comprobante de pago reseteados",["num"=>DBi::$affected_rows],"factura");
    if(!(DBi::$errno)) echoJsNDie("empty","Sin cambios",["query"=>$query]);
    errNDie("Error al resetear",["errno"=>DBi::$errno,"error"=>DBi::$error]);
}
function isTemporalUpdateReceipt() {
    return ($_POST["action"]??"")==="tempUpdtPymRcpt";
}
function doTemporalUpdateReceipt() {
    $query="UPDATE facturas f INNER JOIN facturas p ON f.idReciboPago=p.id INNER JOIN dpagos d ON f.id=d.idFactura INNER JOIN cpagos c ON p.id=c.idCPago SET f.statusReciboPago=1 WHERE f.tipoComprobante='i' AND p.tipoComprobante='p' AND f.idReciboPago IS NOT NULL AND f.statusReciboPago IS NULL AND f.statusn BETWEEN 32 AND 127 AND p.statusn&1>0 AND p.statusn<128";
    $res = DBi::query($query);
    if ($res) successNDie("Actualización satisfactoria",["num"=>DBi::$affected_rows],"factura");
    if(!(DBi::$errno)) echoJsNDie("empty","Sin cambios",["query"=>$query]);
    errNDie("Error al actualizar",["errno"=>DBi::$errno,"error"=>DBi::$error]);
}
function isUpdatePaymentReceiptData() {
    return ($_POST["action"]??"")==="updtPymRcptDt";
}
function doUpdatePaymentReceiptData() {
    $id=$_POST["id"]??null;
    if (isset($id[0])) {
        global $cpyObj;
        if (!$cpyObj) { require_once "clases/CPagos.php"; $cpyObj = new CPagos(); }
        $beginTime=microtime(true);
        $res=$cpyObj->fixCP($id);
        $endTime=microtime(true)-$beginTime;
    }
    successNDie("CHECK",["trace"=>$cpyObj->fixedIdList,"data"=>$cpyObj->data,"time"=>$endTime]);
}
function isFixOldCPagos() {
    return ($_POST["action"]??"")==="fixOldCPagos";
}
function doFixOldCPagos() {
    global $cpyObj;
    if (!$cpyObj) { require_once "clases/CPagos.php"; $cpyObj = new CPagos(); }
    $beginTime=microtime(true);
    $cpyObj->fixOldCPagos();
    $endTime=microtime(true)-$beginTime;
    successNDie("CHECK",["trace"=>$cpyObj->fixedIdList,"data"=>$cpyObj->data,"time"=>$endTime]);
}
function isFixEmptyCPagos() {
    return ($_POST["action"]??"")==="fixEmptyCPagos";
}
function doFixEmptyCPagos() {
    global $cpyObj;
    if (!$cpyObj) { require_once "clases/CPagos.php"; $cpyObj = new CPagos(); }
    sessionInit();
    $beginTime=microtime(true);
    $cpyObj->fixEmptyCPagos();
    $endTime=microtime(true)-$beginTime;
    successNDie("CHECK",["trace"=>$cpyObj->fixedIdList,"data"=>$cpyObj->data,"time"=>$endTime]);
}
function isRepairCFDI() {
    return ($_POST["action"]??"")==="repairCFDIs";
}
function doRepairCFDI() {
    global $cpyObj;
    $idList=$_POST["idList"];
    if (!$cpyObj) { require_once "clases/CPagos.php"; $cpyObj = new CPagos(); }
    $beginTime=microtime(true);
    $genList=$cpyObj->getCPIds($idList, 3);
    if (isset($genList[0])) {
        $result=$cpyObj->updateCPData($genList);
        $endTime=microtime(true)-$beginTime;
        if ($result===true) successNDie("Complementos actualizados",["genList"=>$genList,"trace"=>$cpyObj->fixedIdList,"time"=>$endTime]);
        errNDie($result,["genList"=>$genList,"trace"=>$cpyObj->fixedIdList,"time"=>$endTime]);
    }
    echoJsNDie("empty", "Nada para actualizar");
}
function getAdminFindQuery() {
    global $invObj;
    $query = "";
    if (!empty($_POST["idFactura"]))       $query .= appendPostIntegerToQuery("idFactura", "id", isset($query[0]));
    if (!empty($_POST["rfcGrupo"]))        $query .= appendPostStringToQuery("rfcGrupo", "rfcGrupo", isset($query[0]));
    if (!empty($_POST["codigoProveedor"])) $query .= appendPostStringToQuery("codigoProveedor", "codigoProveedor", isset($query[0]));
    if (!empty($_POST["folio"]))           $query .= appendPostStringToQuery("folio", "folio", isset($query[0]));
    if (!empty($_POST["serie"]))           $query .= appendPostStringToQuery("serie", "serie", isset($query[0]));
    if (!empty($_POST["uuid"]))            $query .= appendPostUUIDToQuery("uuid", "uuid", isset($query[0]));
    if (!empty($_POST["pedido"]))          $query .= appendPostStringToQuery("pedido", "pedido", isset($query[0]));
    if (!empty($_POST["status"]))          $query .= appendPostStatusToQuery("status", "status", isset($query[0]));
    if (!empty($_POST["subtotal"]))        $query .= appendPostIntegerToQuery("subtotal", "subtotal", isset($query[0]));
    if (!empty($_POST["total"]))           $query .= appendPostIntegerToQuery("total", "total", isset($query[0]));
    if (!empty($_POST["fechaFactura"]))    $query .= appendPostDateToQuery("fechaFactura", "fechaFactura", isset($query[0]));
    if (!empty($_POST["fechaCaptura"]))    $query .= appendPostDateToQuery("fechaCaptura", "fechaCaptura", isset($query[0]));
    if (!empty($_POST["modifiedTime"]))    $query .= appendPostDateToQuery("modifiedTime", "modifiedTime", isset($query[0]));
    $invObj->rows_per_page = 100;
    $invData = $invObj->getData($query);
    if (isset($invData[0])) echo("#|ID|F.FACT|PROVEEDOR|CLIENTE|SERIE|FOLIO|PEDIDO|STATUS|EOH|");
    $idx=0;
    foreach ($invData as $factura) {
        $idx++;
        echo ("$idx|$factura[id]|$factura[fechaFactura]|$factura[codigoProveedor]|$factura[rfcGrupo]|$factura[serie]|$factura[folio]|$factura[pedido]|$factura[status]|EOL|");
    }
    $numrows = $invObj->numrows;
    
    echo "EOT|".($numrows<=100?"$numrows":"100/$numrows")." registro".($numrows==1?"":"s");
}
function appendPostIntegerToQuery($postKey, $columnName, $hasValue) {
    return ($hasValue?" AND ":"").$columnName."=".$_POST[$postKey];
}
function appendPostStringToQuery($postKey, $columnName, $hasValue) {
    return ($hasValue?" AND ":"").$columnName."='".$_POST[$postKey]."'";
}
function appendPostStatusToQuery($postKey, $columnName, $hasValue) {
    switch ($_POST[$postKey]) {
        case "": 
            return "";
        case "Aceptado": 
            return ($hasValue?" AND ":"").$columnName." NOT IN ('Temporal','Pendiente','Rechazado')";
        case "Contrarrecibo": 
            return ($hasValue?" AND ":"").$columnName." IN ('Contrarrecibo','Exportado','RespSinExp','Respaldado')";
        case "SinContrarrecibo": 
            return ($hasValue?" AND ":"").$columnName." IN ('Aceptado','ExpSinContra','RespSinCX','RespSinContra')";
        case "Exportado": 
            return ($hasValue?" AND ":"").$columnName." IN ('Exportado','ExpSinContra','RespSinContra','Respaldado')";
        case "SinExportar": 
            return ($hasValue?" AND ":"").$columnName." IN ('Aceptado','Contrarrecibo','RespSinCX','RespSinExp')";
        case "Respaldado": 
            return ($hasValue?" AND ":"").$columnName." IN ('RespSinCX','RespSinContra','RespSinExp','Respaldado')";
        case "SinRespaldar": 
            return ($hasValue?" AND ":"").$columnName." IN ('Aceptado','Contrarrecibo','Exportado','ExpSinContra')";
        default: 
            return ($hasValue?" AND ":"").$columnName."='".$_POST[$postKey]."'";
    }
} // Aceptado, Contrarrecibo, ExpSinContra, RespSinCX, Exportado, RespSinExp, RespSinContra, Respaldado
function appendPostUUIDToQuery($postKey, $columnName, $hasValue) {
    //return ($hasValue?" AND ":"")."UPPER($columnName) LIKE UPPER('%".$_POST[$postKey]."%')";
    return ($hasValue?" AND ":"")."$columnName LIKE '%".strtoupper($_POST[$postKey])."%'";
}
function appendPostDateToQuery($postKey, $columnName, $hasValue) {
    $dateElems = explode("/",$_POST[$postKey]);
    if (isset($dateElems[2])) {
        list($fDay, $fMon, $fYr) = sscanf($_POST[$postKey], "%2d/%2d/%4d");
        $fechaDia = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
        return ($hasValue?" AND ":"").$columnName." BETWEEN '$fechaDia 00:00:00' AND '$fechaDia 23:59:59'";
    }
    if (isset($dateElems[1])) {
        list($fMon, $fYr) = sscanf($_POST[$postKey], "%2d/%4d");
        $fechaIni = sprintf("%4d-%02d-01 00:00:00", $fYr, $fMon);
        if ($fMon>=12) $fechaFin = sprintf("%4d-%02d-01 00:00:00", 1+$fYr, 1);
        else              $fechaFin = sprintf("%4d-%02d-01 00:00:00", $fYr, 1+$fMon);
        return ($hasValue?" AND ":"").$columnName." BETWEEN '$fechaIni' AND '$fechaFin'";
    }
    if (isset($dateElems[0])) {
        $fYr = $_POST[$postKey];
        $fechaIni = sprintf("%4d-01-01 00:00:00", $fYr);
        $fechaFin = sprintf("%4d-01-01 00:00:00", 1+$fYr);
        return ($hasValue?" AND ":"").$columnName." BETWEEN '$fechaIni' AND '$fechaFin'";
    }
    return "";
}
function getAdminChangeQuery() {
    $fieldarr = [];
    if (!empty($_POST["idFactura"])) $fieldarr["id"] = $_POST["idFactura"];
    if (!empty($_POST["rfcGrupo"])) $fieldarr["rfcGrupo"] = $_POST["rfcGrupo"];
    if (!empty($_POST["codigoProveedor"])) $fieldarr["codigoProveedor"] = $_POST["codigoProveedor"];
    if (!empty($_POST["folio"])) $fieldarr["folio"] = $_POST["folio"];
    if (!empty($_POST["serie"])) $fieldarr["serie"] = $_POST["serie"];
    if (!empty($_POST["uuid"])) $fieldarr["uuid"] = strtoupper($_POST["uuid"]);
    if (!empty($_POST["pedido"])) $fieldarr["pedido"] = $_POST["pedido"];
    if (!empty($_POST["status"])) $fieldarr["status"] = $_POST["status"];
    if (!empty($_POST["subtotal"])) $fieldarr["subtotal"] = $_POST["subtotal"];
    if (!empty($_POST["total"])) $fieldarr["total"] = $_POST["total"];
    if (!empty($_POST["fechaFactura"])) $fieldarr["fechaFactura"] = $_POST["fechaFactura"];
    if (!empty($_POST["fechaCaptura"])) $fieldarr["fechaCaptura"] = $_POST["fechaCaptura"];
    if (!empty($_POST["modifiedTime"])) $fieldarr["modifiedTime"] = $_POST["modifiedTime"];

    echo(arr2List($fieldarr));
}
function getTestService2() {
    global $invObj;
    //$invObj->rows_per_page = 0;
    echo "Test Service 2: ".get_class($invObj);
    $arr = ["AdminQuery"=>getTestAdminQuery(), "AppendPDF"=>getTestAppendPDF(), "Test"=>$_GET["test"], "GET"=>$_GET, "POST"=>$_POST, "FILES"=>$_FILES];

    echo arr2List($arr);
}
function getTestAdminQuery() {
    $result = "<form method=\"POST\" action=\"Facturas.php\" target=\"_blank\"><input type=\"hidden\" name=\"menu_accion\" value=\"Admin Factura\"><input type=\"hidden\" name=\"command\" value=\"Buscar\"><input type=\"hidden\" name=\"idFactura\" value=\"347\"><input type=\"submit\" name=\"send\" value=\"Test\"></form>";
    return $result;
}
function getTestAppendPDF() {
    return isAppendPDFService()?"SI":"NO";
    // $result = "<form method=\"POST\" action=\"Facturas.php\" target=\"_blank\"><input type=\"hidden\" name=\"menu_accion\" value=\"Admin Factura\"><input type=\"hidden\" name=\"command\" value=\"Buscar\"><input type=\"hidden\" name=\"idFactura\" value=\"347\"><input type=\"submit\" name=\"send\" value=\"Test\"></form>";
}
