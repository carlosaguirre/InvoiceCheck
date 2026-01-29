<?php
clog2ini("templates.altafactura");
clog1seq(1);
$epsilon=0.015; //0.000001;
$browser = getBrowser();
if ($browser==="Chrome") $maxXML=10;
else $maxXML=3;
?> 
          <div id="area_central" class="centered">
            <h1 class="txtstrk">Alta de Facturas y Recibos de Pago</h1>
            <form method="post" name="forma_alta" target="_self" enctype="multipart/form-data" onsubmit="return checkSubmittedForm();">
              <input type="hidden" id="menu_accion" name="menu_accion" value="Alta Facturas">
              <div id="xml_selector" class="marginbottom nowrap" title="Seleccione hasta <?= $maxXML ?> archivos XML. Puede agregar los archivos PDF con el mismo nombre que el correspondiente archivo XML.">XML y PDF (mismo nombre): <input type="file" name="xmlfiles[]" id="xmlfiles" multiple<?=  empty($files)?" class=\"highlight\" autofocus":"" ?> accept=".xml,.pdf" onchange="checkChange()"> <?= $deshabilitarAlta&&!$esAdmin?"":"<input type=\"submit\" name=\"submitxml\" id=\"submitxml\" value=\"Verificar\" onclick=\"document.forma_alta.submited=this.id;this.classList.remove('highlight');\">" ?></div>
              <div id="waiting-roll" class="hidden"><img src="<?=$waitImgName?>" width="360" height="360"></div>
<?php
$GLOBALS["ignoreTmpList"]=["files","facturas"];
if (empty($files) && empty($facturas)) {
// PRIMER PAGINA
    echo "              <div id=\"help-screen\" class=\"screen help\"><table class=\"centered\"><tr><td class=\"lefted\"><p>Puede agregar indistintamente facturas, notas de crédito y recibos de pago, simultáneamente hasta $maxXML archivos XML y opcionalmente los archivos PDF correspondientes.</p><p>Es necesario que cada archivo PDF tenga el mismo nombre que el XML respectivo.</p><table style=\"max-width:470px;\" class=\"centered\"><tr><td class=\"lefted fontMedium requiredCfdi\">Por ejemplo:<br>Si el xml es <u class=\"importantValue\">cfdi123.xml</u>, el pdf deberá ser <u class=\"importantValue\">cfdi123.pdf</u> para que el validador lo reconozca.</td></tr></table><p>Alternativamente el archivo PDF puede tener como nombre el folio del comprobante y el rfc del proveedor en mayúsculas.</p><table style=\"max-width:470px;\" class=\"centered\"><tr><td class=\"lefted fontMedium requiredCfdi\">Por ejemplo:<br>Si el xml es <u class=\"importantValue\">cfdi123.xml</u>, con folio <u class=\"importantValue\">123</u> y el rfc del proveedor <u class=\"importantValue\">ABC123456XYZ</u>, el pdf deberá ser <u class=\"importantValue\">123ABC123456XYZ.pdf</u> para que el validador lo reconozca.</td></tr></table></td></tr></table></div>\n";
} else {
    $fillables = "";
    $unfillables = "";
    $xmlIdx = 0;
    $fullIdx = 0;
    echo "              <div id=\"area_scrollable\" class=\"lefted scrolly".(isset($_POST["submitxml"])||isset($_POST["insertxml"])?" screen":"")."\">\n";
    $lisClass = "";
    if (isset($facturas)) $lisClass=" class=\"facturas\"";
    else if (isset($files)) $lisClass=" class=\"archivos\"";
    echo "                <table id=\"load_invoice_structure\"$lisClass>\n";
    if (isset($facturas)) {
    // TERCER PAGINA
      clog3(" ##### ##### ##### ##### #####\n F A C T U R A S\n ##### ##### ##### ##### #####\n".arr2str($facturas));
      foreach ($facturas as $fact) {
        $fullIdx++;
        $success = $fact["success"]??false;
        $errmsg = $fact["errmsg"]??"";
        if (!isset($fact["success"]) && !isset($errmsg[0])) $errmsg="Sin validar, debe reiniciar alta.";
        echo "                  <tr><td".($success?"":" colspan=\"2\"").">\n";
        echo "                    <ul class=\"".(isset($errmsg[0])?"marginbottom0":"marginbottom")."\">\n";

        $ffecha=$fact["ffecha"];
        $nname = $fact["nname"];
        $xml="<a href='$fact[fpath]$nname.xml' data-title='Archivo XML' title='CFDI XML' target='archivo' tabindex='-1' onfocus='this.blur();' class='pointer marginV2 hidBdr'><img src='imagenes/icons/xml200.png' width='20' height='20' class='noBorder2'></a>";
        if (isset($fact["pname"])) {
            $pname = $fact["pname"];
            $pdf="<a href='$fact[fpath]$pname.pdf' data-title='Archivo PDF' title='CFDI-PDF' target='archivo' tabindex='-1' onfocus='this.blur();' class='pointer marginV2 hidBdr'><img src='imagenes/icons/pdf200.png' width='20' height='20' class='noBorder2'></a>";
        } else $pdf="";
        if (($fact["ea"]??"0")==="1") {
            $eapath=$fact["fpath"]??"";
            $eacp=trim(str_replace("-","",$fact["fcodigo"]??""));
            $eafolio=$fact["ffsfx"]??"";
            $eafecha=substr(trim(str_replace("-", "", $ffecha)), 2, 6);
            if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
            if (isset($eapath[0])&&isset($eacp[0])&&$isset($eafolio[0])) {
                $sysPath=$_SERVER["DOCUMENT_ROOT"];
                $eaname="{eapath}EA_{$eacp}_{$eafolio}_{$eafecha}.pdf";
                if (file_exists($sysPath.$eaname)) {
                    $eaf="<a href='$eaname' data-title='Entrada Almacen' title='EA-PDF' target='archivo' tabindex='-1' onfocus='this.blur();' class='pointer marginV2 hidBdr'><img src='imagenes/icons/pdf200EA.png' width='20' height='20' class='noBorder2'></a>";
                } else if ($esPruebas) {
                    $eaf="<img src='imagenes/icons/pdf512Error.png' width='20' height='20' title='NO EXISTE $eaname'>";
                }
            } else $eaf="";
        } else $eaf="";
        if (!empty($xml)||!empty($pdf)) {
            echo "<li>Documentos : ";
            if (isset($xml[0])) echo $xml;
            if (isset($pdf[0])) echo $pdf;
            if (isset($eaf[0])) echo $eaf;
            echo "</li>";
        }
        $uuid = strtoupper($fact["uuid"]);
        if (!empty($uuid))
        echo "                        <li>UUID : <b>".$uuid."</b></li>\n";
        $codigo=$fact["fcodigo"]??"";
        if (isset($codigo[0])) {
            echo "<li";
            $rsprov=$fact["fprov"]??"";
            if (isset($rsprov[0])) {
                if (strpos($rsprov, "'")!==false) $rsprov=str_replace("'", "", $rsprov);
                echo " title='$rsprov'";
            }
            echo ">Proveedor : <b>$codigo</b></li>";
        }
        $falias=$fact["falias"];
        if (isset($falias[0])) {
            echo "<li";
            $rsemp=$fact["fempresa"]??"";
            if (isset($rsemp[0])) {
                if (strpos($rsemp, "'")!==false) $rsemp=str_replace("'", "", $rsemp);
                echo " title='$rsemp'";
            }
            echo ">Empresa : <b>$falias</b></li>";
        }
        if (isset($ffecha[0])) {
            echo "<li>Fecha : <b>$ffecha</b></li>";
        }
        $ffolio = $fact["ffolio"];
        if (isset($ffolio[0]))
            echo "                        <li>Folio : <b>".$ffolio."</b></li>\n";
        if (isset($fact["pedido"])) $pedido = $fact["pedido"];
        if (!empty($pedido))
        echo "                        <li>Pedido : <b>".$pedido."</b></li>\n";
        if (isset($fact["remision"])) $remision = $fact["remision"];
        if (!empty($remision))
        echo "                        <li>Remision : <b>".$remision."</b></li>\n";
        if (!empty($success))
        echo "                        <li class=\"bggreen\">Resultado : <b>Satisfactorio</b></li>\n";
        if (!empty($errmsg))
        echo "                        <li class=\"bgred\">Error : <b>".$errmsg."</b></li>\n";
        if (isset($fact["pagodocto"])) $pagos = $fact["pagodocto"];
        if (empty($success))
            echo "                        <li>Status : <b>No Registrado</b></li>\n";
        //else if (!empty($pagos))
        //    echo "                        <li>Status : <b>Aceptado</b></li>\n";
        else
            echo "                        <li>Status : <b>Pendiente</b></li>\n";
        $dblog = $fact["dblog"]??"";
        if (isset($dblog[0])) clog2($dblog);
        echo "                    </ul>\n";
        echo "                  </td>\n";
        echo "                  <td>\n";
        if (isset($fact["concepto"])) $conceptos = $fact["concepto"];
        if (isset($conceptos)) {
            echo "                    <table id=\"table_of_concepts\" border=\"1\">\n";
            echo "                        <thead>\n";
            echo "                            <tr><th>Cantidad</th><th>Unidad</th><th>C&oacute;digo</th><th>Descripci&oacute;n</th><th>Precio Unitario</th><th>Importe</th></tr>\n";
            echo "                        </thead>\n";
            echo "                        <tbody>\n";
            $subtotal = 0;
            foreach ($conceptos as $concepto) {
                $cantidad=+$concepto["cantidad"];
                $valoruni=+$concepto["valorUnitario"];
                $importe=+$concepto["importe"];
                $resultado=$cantidad*$valoruni;
                $subtotal+=$importe;
                if (abs($importe-$resultado)<$epsilon) $claseImporte="bggreen";
                else $claseImporte="bgred\" calc=\"".number_format($subtotal,2);
                echo "                            <tr><td class=\"centered concepto cantidad\">$cantidad</td>";
                $row2="<tr class=\"satKeys invoice\"><td class=\"righted brVanish\" colspan=\"2\">";
                if (isset($concepto["claveUnidad"])) {
                    $titleUnidad=" title=\"ClaveUnidad SAT: $concepto[claveUnidad]";
                    if (isset($concepto["nombreClaveUnidad"])) $titleUnidad.="='$concepto[nombreClaveUnidad]'";
                    $titleUnidad.="\"";
                    $row2.="$concepto[claveUnidad]='$concepto[nombreClaveUnidad]'";
                } else $titleUnidad="";
                $row2.="</td>";
                echo "<td class=\"concepto unidad\"$titleUnidad>".htmlentities($concepto["unidad"])."</td>";
                $row2.="<td class=\"bhVanish\">";
                if (isset($concepto["claveProdServ"])) {
                    $titleCodigo=" title=\"ClaveProdServ SAT: $concepto[claveProdServ]";
                    if (isset($concepto["nombreClaveProdServ"])) $titleCodigo.="='$concepto[nombreClaveProdServ]'";
                    $titleCodigo.="\"";
                    $row2.="$concepto[claveProdServ]</td><td class=\"blVanish\" colspan=\"3\">$concepto[nombreClaveProdServ]";
                } else $titleCodigo="";
                $row2.="</td></tr>";
                echo "<td class=\"concepto codigo\"$titleCodigo>".htmlentities($concepto["codigo"])."</td>";
                echo "<td class=\"concepto descripcion\">".htmlentities($concepto["descripcion"])."</td>";
                echo "<td class=\"righted concepto unitario\">$".number_format($valoruni,2)."</td>";
                echo "<td class=\"righted concepto importe $claseImporte\">$".number_format($importe,2)."</td></tr>{$row2}\n";
            }
            echo " <tr><td colspan=\"5\" class=\"righted subtotal\">Subtotal: &nbsp; </td><td class=\"righted\">$".number_format($subtotal,2)."</td></tr>\n";
            echo "                        </tbody>\n";
            echo "                    </table>\n";
        }
        if (isset($pagos)) {
            echo "                    <table id=\"table_of_paid_invoices\" border=\"1\">\n";
            echo "                        <thead>\n";
            echo "                            <tr><th>Factura</th><th>UUID</th><th>Status</th></tr>\n";
            echo "                        </thead>\n";
            echo "                        <tbody>\n";
            foreach($pagos as $pago) {
                $pagoSerie=$pago["serie"];
                $pagoFolio=strtoupper(ltrim($pago["folio"],'0'));
                $pagoUUID=strtoupper($pago["UUID"]);
                if (!isset($pago["saldo"][0])) $pagoSaldo=0;
                else $pagoSaldo=+trim($pago["saldo"]);
                if ($pagoSaldo==0) $pagoStatus="PAGADA";
                else $pagoStatus="PARCIAL";
                $fileLink=getRutaReal($pagoUUID);
                $target="$pagoSerie$pagoFolio";
                $pagoFolio2="";
                if (!empty($pagoSerie)) {
                    $pagoFolio2=$pagoSerie;
                    if (!empty($pagoFolio)) $pagoFolio2.="-";
                }
                if (!empty($pagoFolio)) $pagoFolio2.=$pagoFolio;
                if(empty($pagoFolio2)&&!empty($pagoUUID)) $pagoFolio2=substr($pagoUUID,-10);
                if (isset($fileLink)) $fileLink="<A HREF=\"$fileLink\" target=\"$target\" tabindex=\"-1\">$pagoFolio2</A>";
                else $fileLink=$target;
                echo "<tr><td>$fileLink</td>";
                echo "<td data-max-width=\"140px\" class=\"isUUIDCell\" title=\"$pagoUUID\">$pagoUUID</td>";
                echo "<td>$pagoStatus</td>";
                echo "</tr>\n";
            }
            echo "                        </tbody>\n";
            echo "                    </table>\n";
        }
        echo "                  </td>\n";
        echo "                  </tr>\n";
      }
    }
    if(isset($files)) {
    // SEGUNDA PAGINA
      clog3(" ##### ##### ##### ##### #####\n F I L E S\n ##### ##### ##### ##### #####");
      //clog3(arr2str($files));
      foreach ($files as $file) {
        $fullIdx++;
        $filename = basename(strtolower($file["name"]), ".xml");
        $esPago=false;
        $esNota=false;
        $esTraslado=false;
        $esFactura=false;
        if (isset($file["xml"]) && $file["xml"]->has("tipo_comprobante")) {
            $tipoComprobante = strtoupper($file["xml"]->get("tipo_comprobante"));
            $tipoDoc = "Documento";
            switch($tipoComprobante[0]) {
                case "I": $tipoComprobante="INGRESO"; $esFactura=true; $tipoDoc="Factura"; break;
                case "E": $tipoComprobante="EGRESO";  $esNota=true;    $tipoDoc="Nota";    break;
                case "P": $tipoComprobante="PAGO";    $esPago=true;    $tipoDoc="Recibo";  break;
                case "T": $tipoComprobante="TRASLADO";$esTraslado=true; $tipoDoc="Traslado"; break;
            }
        } else {
            $tipoComprobante="Comprobante"; $tipoDoc="Documento";
        }
        if (!isset($file["enough"])) $file["enough"]=true;
        if (!isset($file["errmsg"][0])) $file["errmsg"]="";
        echo "                  <tr class=\"".($file["enough"]?"uploadData":"uploadErrorData")."\"><td".($file["enough"]?"":" colspan=\"2\"").">\n";
        echo "                    <ul class=\"".(empty($file["errmsg"])?"marginbottom":"marginbottom0")."\">\n";
        
        $filePath = getUbicacionFactura($file);
        //if (!empty($filePath) && !(empty($file["new_name"])&&empty($file["pdf_name"]))) {
        if (isset($filePath[0]) && isset($file["new_name"][0])) {
            echo "<li>Documentos : ";
            if (isset($file["new_name"][0])) {
                echo "<a href=\"$filePath$file[new_name].xml\" data-title=\"Archivo XML\" title=\"CFDI XML\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/xml200.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>";
            }
            if (isset($file["pdf_name"][0])) {
                echo "<a href=\"$filePath$file[pdf_name].pdf\" data-title=\"Archivo PDF\" title=\"CFDI PDF\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/pdf200.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>";
            } else if ($file["enough"]) {
                //$ciclo = explode("/",$filePath)[2];
                //echo "<a href=\"templates/factura.php?nombre=$file[new_name]&ciclo=$ciclo\" data-title=\"Sin PDF\" title=\"Anexar PDF\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/invChk200.png\" width=\"20\" height=\"20\" class=\"noBorder2 bgred bxsbrd\"></a><span class=\"redden boldValue padl1\">SIN PDF</span><br><span class=\"smaller bgred\">Presione icono azul para anexar CFDI-PDF.</span>";
                echo "<img src=\"imagenes/icons/invChk200.png\" width=\"20\" height=\"20\" class=\"noBorder2 bgred bxsbrd pointer marginV2 hidBdr\" title=\"Anexar PDF\" onclick=\"const fx=ebyid('pdffile$xmlIdx');fx.click();\"><span id=\"pdfcap$xmlIdx\" class=\"redden boldValue padl1\">SIN PDF</span><input type=\"file\" name=\"pdffile[$xmlIdx]\" id=\"pdffile$xmlIdx\" accept=\".pdf\" class=\"hidden\" onchange=\"const c=ebyid('pdfcap$xmlIdx');const m=ebyid('pdfmsg$xmlIdx');if(this.files.length==0||this.files[0].name.length==0){c.textContent='SIN PDF';m.textContent='Presione icono azul para anexar CFDI-PDF';}else{c.textContent='';m.textContent='';}\"><br><span id=\"pdfmsg$xmlIdx\" class=\"smaller bgred\">Presione icono azul para anexar CFDI-PDF.</span>";

            }
// if (isset($eafile[0])) echo "<a href></a>";
            echo "</li>";
        }
        /*if (!empty($filePath) && !empty($file["pdf_name"]))
            echo "                        <li>Archivo PDF: <a href=\"$filePath$file[pdf_name].pdf\" data-title=\"Archivo PDF\" target=\"archivo\" onclick=\"setTabTitle(event);\" tabindex=\"-1\"><b>$file[pdf_name].pdf</b></a></li>\n";
        else if ($file["enough"])
                echo "                        <li>Ingrese $tipoDoc (Archivo PDF) : <input type=\"file\" name=\"pdffile[$xmlIdx]\" id=\"pdffile$xmlIdx\" class=\"highlight\"></li>\n";
        if (!empty($filePath) && !empty($file["name"]) && isset($file["new_name"])) {
            echo "                        <li>Archivo XML: <a href=\"$filePath$file[new_name].xml\" data-title=\"Archivo XML\" target=\"archivo\" onclick=\"setTabTitle(event);\" tabindex=\"-1\"><b>$file[new_name].xml</b></a></li>\n";
        }*/

        if (!isset($file["xml"])) {
            echo "<li>Archivo <b>".basename($file["name"])."</b>";
            echo "</li></ul></td></tr>";
            echo "<tr class=\"uploadErrorMessage\"><td colspan=\"2\" class=\"vexpand padding0 top xmlerrorcell\">";
            echo "<ul class=\"margintop0 marginbottom0\"><li class=\"wordwrap bgred vexpand\">Errores : ";
            echo "<table class=\"cfdiErrorSetup\"><tbody><tr><td class=\"lefted wordwrap\">";
            if (isset($file["errmsg"][0])) echo $file["errmsg"];
            else "NO EXISTE XML";
            echo "</td></tr></tbody></table></li></ul></td></tr>";
            echo "<tr class=\"uploadBottomLine\"><td colspan=\"2\" class=\"centered\">&nbsp;</td></tr>";
            continue;
        }
        $comprobanteNoEncontradoEnSAT = (isset($file["cfdi"])&&strpos($file["cfdi"],"N - 602")!==FALSE);
        if ($file["xml"]->has("uuid")) {
            $uuid=strtoupper($file["xml"]->get("uuid"));
            if ($comprobanteNoEncontradoEnSAT)
                echo "<li>Folio Fiscal(UUID) : <b class=\"bgmagenta highlight\">$uuid</b></li>\n";
            else echo "<li>Folio Fiscal(UUID) : <b>".reduccionMuestraDeCadenaLarga($uuid)."</b></li>\n";
        }
        if ($file["xml"]->has("emisor")) {
            $emisor=$file["xml"]->get("emisor");
            $provRazSoc=htmlentities($emisor["@nombre"]);
            $codProv=$file["xml"]->cache["codigoProveedor"]??null;
            if (isset($codProv[0])) {
                $prvNombre=$file["xml"]->cache["nombreProveedor"]??null;
                if (isset($prvNombre[0])) $prvRazSoc=$prvNombre;
                echo "<li title=\"$provRazSoc\">Proveedor : <b>$codProv"./*"<br>".reduccionMuestraDeCadenaLarga($provRazSoc,15,"...").*/"</b></li>";
            } else {
                echo "<li>Emisor : <b>".reduccionMuestraDeCadenaLarga($provRazSoc,20,"...")."</b></li>\n";
                echo "<li>RFC Emisor : <b".($comprobanteNoEncontradoEnSAT?" class=\"bgmagenta highlight\"":"").">".$emisor["@rfc"]."</b></li>\n";
            }
        }
        $hasReceptor = $file["xml"]->has("receptor");
        if ($hasReceptor) {
            $receptor=$file["xml"]->get("receptor");
            $receptorJson=json_encode($receptor);
            $receptorKeys=array_keys($receptor);
            $gpoRazSoc=htmlentities($receptor["@nombre"]);
            $gpoAlias=$file["xml"]->cache["aliasGrupo"]??null;
            echo "<!-- ".json_encode($receptor)." -->";
            if (isset($gpoAlias[0])) {
                $gpoNombre=$file["xml"]->cache["nombreGrupo"]??null;
                if (isset($gpoNombre[0])) $gpoRazSoc=$gpoNombre;
                echo "<li title=\"$gpoRazSoc\">Empresa : <b>$gpoAlias"./*"<br>".reduccionMuestraDeCadenaLarga($gpoRazSoc,15,"...").*/"</b></li>";
            } else {
                echo "<li>Receptor : <b>".reduccionMuestraDeCadenaLarga($gpoRazSoc, 20, "...") . "</b></li>\n";
                if ($comprobanteNoEncontradoEnSAT)
                    echo "<li>RFC Receptor : <b class=\"bgmagenta highlight\">".$receptor["@rfc"]."</b></li>\n";
            }
            $usoCfdi=$receptor["@usocfdi"]??null;
        }
        echo "<li>Tipo : <b>$tipoComprobante</b></li>";
        if ($hasReceptor && isset($usoCfdi[0])) {
            require_once "clases/catalogoSAT.php";
            $usoDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_USOCFDI, "codigo", $usoCfdi, "descripcion");
            echo "<li>Uso CFDI : <b>$usoCfdi</b> ($usoDesc)</li>";
        }
        /* */
        if ($file["xml"]->has("fecha")) {
            $fecha=date("Y-m-d H:i:s",strtotime($file["xml"]->get("fecha")));
            echo "                        <li><span id=\"capFecha\">Fecha</span> : <b>$fecha</b></li>\n";
        }
        if ($file["xml"]->has("pago_fecha")) {
            $fechaPagoArr=$file["xml"]->get("pago_fecha");
            // buscar como se guarda en tabla Pagos para posteriormente extraer de ahi el campo fechaPago
            if (!is_array($fechaPagoArr)) $fechaPagoArr = [$fechaPagoArr];
            $uniqDatLst=[];
            foreach ($fechaPagoArr as $idx => $fpVal) {
                $fpT=strtotime($fpVal);
                if (!isset($uniqDatLst[$fpT])) $uniqDatLst[$fpT]=date("Y-m-d H:i:s",$fpT);
            }
            $unqDatKys=array_keys($uniqDatLst);
            sort($unqDatKys);
            foreach ($unqDatKys as $idx => $key) {
                $dtp=$uniqDatLst[$key];
                echo "                        <li><span id=\"capPFecha\">Fecha de Pago</span> : <b>$dtp</b><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const cf=ebyid('capFecha');cladd(cf, 'inblock');cf.style.width=ebyid('capPFecha').offsetWidth+'px';ekil(this);\"></li>\n";
            }
        }
        /* */
        /*if ($file["xml"]->has("certificado")) {
            $certificado = $file["xml"]->get("certificado");
            echo "                        <li title='$certificado'>Certificado : <b>".reduccionMuestraDeCadenaLarga($certificado,10," ... ")."</b></li>\n";
        }*/
        /*if ($file["xml"]->has("version")) {
            $version = $file["xml"]->get("version");
            echo "                        <li>Versi&oacute;n : <b>$version</b></li>\n";
        }*/
        if ($file["xml"]->has("metodo_pago")) {
            $metodoPago = $file["xml"]->get("metodo_pago");
            require_once "clases/catalogoSAT.php";
            $metodoDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_METODOPAGO, "codigo", $metodoPago, "descripcion");
            //echo "                        <li title=\"$metodoDesc\">M&eacute;todo de Pago : <b>$metodoPago</b></li>\n";
            echo "<li>M&eacute;todo de Pago : <b>$metodoPago</b> ($metodoDesc)</li>\n";
        }
        if ($file["xml"]->has("forma_pago")) {
            $formaPago = $file["xml"]->get("forma_pago");
            require_once "clases/catalogoSAT.php";
            $formaDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_FORMAPAGO, "codigo", $formaPago, "descripcion");
            //echo "                        <li title=\"$formaDesc\">Forma de Pago : <b>$formaPago</b></li>\n";
            echo "<li>Forma de Pago : <b>$formaPago</b> ($formaDesc)</li>\n";
        }
        if ($file["xml"]->has("serie"))
            echo "                        <li>Serie : <b>".$file["xml"]->get("serie")."</b></li>\n";
        if ($file["xml"]->has("folio"))
            echo "                        <li>Folio : <b>".$file["xml"]->get("folio")."</b></li>\n";
        if ($file["xml"]->has("subtotal")) {
            $subtotal = +$file["xml"]->get("subtotal");
            if (!$esPago || $subtotal!==0)
                echo "<li>Subtotal : <b>$".number_format($subtotal, 2)."</b></li>";
        }
        if ($file["xml"]->has("total")) {
            $total = +$file["xml"]->get("total");
            if (!$esPago || $total!==0)
                echo "<li>Total : <b>$".number_format($total, 2)."</b></li>";
        }
        if (!empty($file["cfdi"]) && !$comprobanteNoEncontradoEnSAT) {
            echo "<li class=\"".(strpos($file["cfdi"],"satisfactoriamente")===FALSE?"bgred":"bggreen")."\">Respuesta SAT : <b>".$file["cfdi"]."</b></li>";
            if (!empty($file["vigencia"]))
                echo "<li class=\"".($file["vigencia"]==="Vigente"?"bggreen":"bgred")."\">Vigencia : <b>".$file["vigencia"]."</b></li>";
        }
        echo "</ul>";
        echo "</td>";
        if ($file["enough"]) {
            echo "<td>";
            if (!$esPago&&!$esTraslado) {
                echo "Ingrese n&uacute;mero de pedido y c&oacute;digo de art&iacute;culos.<br>\n";
                $fillables.="\"pedido$xmlIdx\",";
            }
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][oname]\" value=\"$file[name]\">\n";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][nname]\" value=\"$file[new_name]\">";
            if (isset($file["pdf_name"][0]))
                echo "<input type=\"hidden\" name=\"factura[$xmlIdx][pname]\" value=\"$file[pdf_name]\">";
            if (isset($file["file_suffix"][0]))
                echo "<input type=\"hidden\" name=\"factura[$xmlIdx][ffsfx]\" value=\"$file[file_suffix]\">";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][ffolio]\" value=\"".$file["xml"]->get("folio")."\">";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][ffecha]\" value=\"".date("Y-m-d H:i:s",strtotime($file["xml"]->get("fecha")))."\">";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][frfc]\" value=\"".$file["xml"]->get("emisor")["@rfc"]."\">";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][fpath]\" value=\"$filePath\">\n";
            // TODO: Hay q cambiar RFC a ALIAS, O buscarlo en el cache del xml. O utilizar el path que ya está calculado. Preveer que el path debe ser para guardar en Invoice Check y se requiere generer el path para guardar en avance... o ambos...
            $alias=$file["xml"]->cache["aliasGrupo"];
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][falias]\" value=\"$alias\">";
            $empresa=$file["xml"]->cache["nombreGrupo"]??"";
            if (isset($empresa[0])) echo "<input type=\"hidden\" name=\"factura[$xmlIdx][fempresa]\" value=\"$empresa\">";
            $codigo=$file["xml"]->cache["codigoProveedor"]??"";
            if (isset($codigo[0])) echo "<input type=\"hidden\" name=\"factura[$xmlIdx][fcodigo]\" value=\"$codigo\">";
            $proveedor=$file["xml"]->cache["nombreProveedor"]??"";
            if (isset($proveedor[0])) echo "<input type=\"hidden\" name=\"factura[$xmlIdx][fprov]\" value=\"$proveedor\">";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][uuid]\" value=\"".strtoupper($file["xml"]->get("uuid"))."\">";
            echo "<input type=\"hidden\" name=\"factura[$xmlIdx][tipoComprobante]\" value=\"$tipoComprobante\">";
            if ($esTraslado) {
                echo "<input type='hidden' id='pedido$xmlIdx' name='factura[$xmlIdx][pedido]' value='S/PEDIDO'/>";
                echo "<input type='hidden' id='remision$xmlIdx' name='factura[$xmlIdx][remision]' value='S/REMISION'/>";
            } else if (!$esPago) {
                echo "Num. de Pedido: <input type=\"text\" id=\"pedido$xmlIdx\" name=\"factura[$xmlIdx][pedido]\" class=\"highlight uppercase pedido marginH2\" onchange=\"checkFillables()\" onkeydown=\"return preventKeyCodes(event, [32]);\" value=\"S/PEDIDO\"/>\n";
                echo "<img class=\"btnFX vbottom\" src=\"imagenes/icons/upload1.png\" onclick=\"doClick('uptxtfile$xmlIdx');\"><input type=\"file\" id=\"uptxtfile$xmlIdx\" enctype=\"multipart/form-data\" class=\"hidden\" onchange=\"importInputTextFromFile(event, $xmlIdx);\">";
                echo "<img class=\"btnFX vbottom\" src=\"imagenes/icons/assistance.png\" onclick=\"helper('uploadinv');\" onload=\"console.log('ASSISTANCE LOADED!');\">";
                echo "<br>Num. de Remision: <input type=\"text\" id=\"remision$xmlIdx\" name=\"factura[$xmlIdx][remision]\" class=\"highlight uppercase remision marginH2\" onchange=\"checkFillables()\" onkeydown=\"return preventKeyCodes(event, [32]);\" value=\"S/REMISION\"/>&nbsp;<i style=\"vertical-align: super;font-size: smaller;\">opcional</i>\n";
            }
            if (!$esPago || !empty($total)) {
                $infoPrv=$file["xml"]->cache["infoProveedor"]??[];
                $conceptos = $file["xml"]->get("conceptos");
                if (isset($conceptos["@cantidad"])) $conceptos = [$conceptos];
                if (isset($conceptos[50])) echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"overlayWheel();ekil(this);\">";
                echo "<table id=\"table_of_concepts\" border=\"1\">";
                echo "<thead>";
                echo "<tr><th>Cantidad</th><th>Unidad</th>";
                if (!$esTraslado) echo "<th id=\"headCellCode_{$xmlIdx}\">C&oacute;digo</th>";
                echo "<th>Descripci&oacute;n</th><th>Precio Unitario</th><th>Importe</th></tr>";
                echo "</thead>";
                echo "<tbody>";
                $conceptIdx=0;
                $sumtotal = 0;
                $sumsubtotal = 0;
                $sumdescuento = 0;
                $sumtraslados = 0;
                $sumretenciones = 0;
                foreach ($conceptos as $cncIdx=>$concepto) {
                    //clog2("Concepto ".gettype($concepto)." Data: ".json_encode($concepto));
                    $conceptIdf = "{$xmlIdx}_{$conceptIdx}";
                    $conceptName = "concepto$conceptIdf";
                    $fillables.="\"$conceptName\",";
                    $cantidad = +$concepto["@cantidad"];
                    $cncNum=$cncIdx+1;
                    echo "<tr id='row{$cncNum}' index='{$conceptIdx}'><td title='{$cncNum}'>$cantidad<input type=\"hidden\" id=\"concCant$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][cantidad]\" value=\"$cantidad\"></td>";
                    $row2="<tr class=\"satKeys invoice\"><td class=\"righted brVanish\" colspan=\"2\">";
                    if (isset($concepto["@claveunidad"])) {
                        $claveUnidad = $concepto["@claveunidad"];
                        require_once "clases/catalogoSAT.php";
                        $nombreClaveUnidad = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD, "codigo", $claveUnidad, "nombre");
                        if (!empty($nombreClaveUnidad)) {
                            $titleUnidad = " title=\"ClaveUnidad SAT: $claveUnidad='$nombreClaveUnidad'\"";
                            $row2.="$claveUnidad = '$nombreClaveUnidad'";
                        } else {
                            $titleUnidad = " title=\"ClaveUnidad: $claveUnidad (No definida en SAT)\"";
                            $row2.="$claveUnidad desconocida";
                        }
                    } else {
                        $titleUnidad = "";
                        $row2.="Sin Clave Unidad";
                    }
                    $row2.="</td>";
                    if (isset($concepto["@unidad"])) {
                        $unidad = htmlentities($concepto["@unidad"]);
                    } else if (isset($claveUnidad)) {
                        $unidad = htmlentities($nombreClaveUnidad);
                    } else {
                        $unidad = "N/D";
                        $titleUnidad = " title=\"Unidad No Definida\"";
                    }
                    echo "<td$titleUnidad>$unidad<input type=\"hidden\" id=\"concUMsr$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][unidad]\" value=\"$unidad\">";
                    if (isset($claveUnidad)) echo "<input type=\"hidden\" id=\"concCveU$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][claveUnidad]\" value=\"$claveUnidad\">";
                    if (isset($nombreClaveUnidad)) echo "<input type=\"hidden\" id=\"concNCveU$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][nombreClaveUnidad]\" value=\"$nombreClaveUnidad\">";
                    $colSpan=($esTraslado?3:4);
                    $row2.="<td class=\"lefted blVanish nopad\" colspan=\"$colSpan\"><div class=\"padv5 mxHg50 yFlow\">";
                    if (isset($concepto["@claveprodserv"])) {
                        $claveProdServ = $concepto["@claveprodserv"];
                        require_once "clases/catalogoSAT.php";
                        $nombreClaveProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV, "codigo", $claveProdServ, "descripcion");
                        if (!empty($nombreClaveProdServ)) {
                            $titleCodigo = " title=\"ClaveProdServ SAT: $claveProdServ='$nombreClaveProdServ'\"";
                            if (substr($claveProdServ, -2)==="00") {
                                $row2.="<b><span class=\"fixWid inblock marR3\" fixId=\"headCellCode_{$xmlIdx}\">$claveProdServ</span>$nombreClaveProdServ</b>";
                                $numClaveProdServ=intval($claveProdServ);
                                for ($i=1; $i < 100; $i++) {
                                    $nxtClaveProdServ=$numClaveProdServ+$i;
                                    $nxtNombreProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo", $nxtClaveProdServ, "descripcion");
                                    if (isset($nxtNombreProdServ[0]))
                                        $row2.="<br><span class=\"fixWid inblock\" fixId=\"headCellCode_{$xmlIdx}\">$nxtClaveProdServ</span>$nxtNombreProdServ";
                                    else break; // Se asume que son números consecutivos, sin saltos
                                }
                            } else $row2.="<span class=\"fixWid inblock\" fixId=\"headCellCode_{$xmlIdx}\">$claveProdServ</span>$nombreClaveProdServ";
                        } else {
                            $titleCodigo = " title=\"ClaveProdServ SAT: $claveProdServ (no definida en SAT)\"";
                            $row2.="Clave $claveProdServ desconocida";
                        }
                    } else $titleCodigo = "";
                    $row2.="</div></td></tr>";
                    $descripcion = htmlentities($concepto["@descripcion"],ENT_QUOTES);
                    $valueDescripcion = $descripcion; //htmlentities($descripcion,ENT_QUOTES);
                    $cdescripcion=DBi::real_escape_string($valueDescripcion);
                    if (isset($infoPrv["d"]) && $infoPrv["d"]==="1") {
                        $trimdesc=trim($descripcion);
                        $rspIdx=strrpos($trimdesc, " ");
                        if ($rspIdx>=0) {
                            $vcodigo=substr($trimdesc, $rspIdx+1);
                            //$descripcion=trim(substr($trimdesc, 0, $rspIdx));
                            //$valueDescripcion=$trimdesc;
                        }
                        if (isset($vcodigo[0])) $vcodigo=trim($vcodigo);
                        if (isset($vcodigo[0])) $vcodigo=" value=\"$vcodigo\"";
                        else $vcodigo="";
                    } else $vcodigo="";
                    if ($esTraslado)
                        echo "<input type='hidden' id='$conceptName' name='factura[$xmlIdx][concepto][$conceptIdx][codigo]' value='S/CODIGO'/>";
                    else {
                        echo "</td><td$titleCodigo><input type=\"text\" id=\"$conceptName\" name=\"factura[$xmlIdx][concepto][$conceptIdx][codigo]\" class=\"highlight uppercase concepto\"{$vcodigo} onchange=\"checkFillables()\" onkeydown=\"return preventKeyCodes(event, [32]);\"/>";
                        if (isset($claveProdServ)) echo "<input type=\"hidden\" id=\"concCvePrdSrv$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][claveProdServ]\" value=\"$claveProdServ\">";
                        if (isset($nombreClaveProdServ)) echo "<input type=\"hidden\" id=\"concNCvePrdSrv$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][nombreClaveProdServ]\" value=\"$nombreClaveProdServ\">";
                    }
                    echo "</td>";
                    echo "<td>$descripcion<input type=\"hidden\" id=\"concDesc$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][descripcion]\" value=\"$valueDescripcion\"><!-- $cdescripcion --></td>";
                    $valorUnitario = +$concepto["@valorunitario"];
                    echo "<td class=\"righted nowrap\">$".number_format($valorUnitario,2)."<input type=\"hidden\" id=\"concUVal$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][valorUnitario]\" value=\"$valorUnitario\"></td>";
                    $importe = +$concepto["@importe"];
                    $resultado=$cantidad*$valorUnitario;
                    $sumsubtotal+=$importe;
                    if (abs($importe-$resultado)<$epsilon) $claseImporte="bggreen";
                    else $claseImporte="bgred\" calculado=\"".number_format($resultado,2);
                    echo "<td class=\"righted nowrap $claseImporte\">$".number_format($importe,2)."<input type=\"hidden\" id=\"concMonto$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][importe]\" value=\"$importe\">";
                    $descuento=0;
                    if (isset($concepto["@descuento"])) {
                        $descuento = +trim($concepto["@descuento"]);
                        echo "<input type=\"hidden\" id=\"concDscnto$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][descuento]\" value=\"$descuento\">";
                    }
                    $sumaTrasladosConcepto=0;
                    $sumaRetencionesConcepto=0;
                    if (isset($concepto["Impuestos"])) {
                        $ccImps=$concepto["Impuestos"];
                        if (isset($ccImps["Traslados"])) {
                            foreach ($ccImps["Traslados"] as $traslado) {
                                if (isset($traslado["@importe"]))
                                    $sumaTrasladosConcepto += +$traslado["@importe"];
                            }
                            echo "<input type=\"hidden\" id=\"concITras$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][traslado]\" value=\"$sumaTrasladosConcepto\">";
                        }
                        if (isset($ccImps["Retenciones"])) {
                            foreach ($ccImps["Retenciones"] as $retencion) {
                                if (isset($retencion["@importe"]))
                                    $sumaRetencionesConcepto += +$retencion["@importe"];
                            }
                            echo "<input type=\"hidden\" id=\"concIRete$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][retencion]\" value=\"$sumaRetencionesConcepto\">";
                        }
                    }
                    $sumdescuento += $descuento;
                    $sumtraslados += $sumaTrasladosConcepto;
                    $sumretenciones += $sumaRetencionesConcepto;
                    $cTotal = $importe-$descuento+$sumaTrasladosConcepto-$sumaRetencionesConcepto;
                    echo "<input type=\"hidden\" id=\"concTotal$conceptIdf\" name=\"factura[$xmlIdx][concepto][$conceptIdx][calcTotal]\" value=\"$cTotal\">";
                    $sumtotal += $cTotal;
                    echo "</td></tr>{$row2}\n";
                    $conceptIdx++;
                }
                if (isset($subtotal) && abs($subtotal-$sumsubtotal)<$epsilon) $claseImporte="bggreen";
                else $claseImporte="bgred\" calculado=\"".number_format($sumsubtotal,2);
                $colSpan=($esTraslado?4:5);
                echo "<tr><td colspan=\"$colSpan\" class=\"righted subtotal\">Subtotal : </td><td class=\"righted nowrap $claseImporte\">$".number_format($subtotal,2)."</td></tr>";
                if ($sumdescuento>0) {
                    if ($file["xml"]->has("descuento")) {
                        $invDescuento=+$file["xml"]->get("descuento");
                        if (abs($invDescuento-$sumdescuento)<$epsilon) $claseImporte="bggreen";
                        else $claseImporte="bgred\" calculado=\"".number_format($sumdescuento,2);
                    } else {
                        $claseImporte="bgred\" enfactura=\"inexistente";
                        $invDescuento=$sumdescuento;
                    }
                    echo "<tr><td colspan=\"$colSpan\" class=\"righted descuento\">Descuento : </td><td class=\"righted nowrap $claseImporte\"><span class=\"sign\">-</span> $".number_format($invDescuento,2)."</td></tr>";
                } else if ($file["xml"]->has("descuento")) {
                        $invDescuento=+$file["xml"]->get("descuento");
                    if (!empty($invDescuento)) {
                        $sumtotal-=$invDescuento;
                        $claseImporte="bggreen";
                        echo "<tr><td colspan=\"$colSpan\" class=\"righted descuento\">Descuento : </td><td class=\"righted nowrap $claseImporte\"><span class=\"sign\">-</span> $".number_format($invDescuento,2)."</td></tr>";
                    }
                }
                if ($sumtraslados>0) {
                    if ($file["xml"]->has("totalimpuestostrasladados")) {
                        $invTotImpTras=+$file["xml"]->get("totalimpuestostrasladados");
                        if (abs($invTotImpTras-$sumtraslados)<$epsilon) $claseImporte="bggreen";
                        else $claseImporte="bgred\" calculado=\"".number_format($sumtraslados,2);
                    } else {
                        $invTotImpTras=$sumtraslados;
                        $claseImporte="bgred\" enfactura=\"inexistente";
                    }
                    echo "<tr><td colspan=\"$colSpan\" class=\"righted traslados\">Impuestos Trasladados : </td><td class=\"righted nowrap $claseImporte\"><span class=\"sign\">+</span> $".number_format($invTotImpTras,2)."</td></tr>";
                } else if ($file["xml"]->has("totalimpuestostrasladados")) {
                    $invTotImpTras=+$file["xml"]->get("totalimpuestostrasladados");
                    if (!empty($invTotImpTras)) {
                        $sumtotal+=$invTotImpTras;
                        $claseImporte="bggreen";
                        echo "<tr><td colspan=\"$colSpan\" class=\"righted traslados\">Impuestos Trasladados : </td><td class=\"righted nowrap $claseImporte\"><span class=\"sign\">+</span> $".number_format($invTotImpTras,2)."</td></tr>";
                    }
                }
                if ($sumretenciones>0) {
                    if ($file["xml"]->has("totalimpuestosretenidos")) {
                        $invTotImpRete=+$file["xml"]->get("totalimpuestosretenidos");
                        if (abs($invTotImpRete-$sumretenciones)<$epsilon) $claseImporte="bggreen";
                        else $claseImporte="bgred\" calculado=\"".number_format($sumretenciones,2);
                    } else {
                        $invTotImpRete=$sumretenciones;
                        $claseImporte="bgred\" enfactura=\"inexistente";
                    }
                    echo "<tr><td colspan=\"$colSpan\" class=\"righted retenciones\">Impuestos Retenidos : </td><td class=\"righted nowrap $claseImporte\"><span class=\"sign\">-</span> $".number_format($invTotImpRete,2)."</td></tr>";
                } else if ($file["xml"]->has("totalimpuestosretenidos")) {
                    $invTotImpRete=+$file["xml"]->get("totalimpuestosretenidos");
                    if (!empty($invTotImpRete)) {
                        $sumtotal-=$invTotImpRete;
                        $claseImporte="bggreen";
                        echo "<tr><td colspan=\"$colSpan\" class=\"righted retenciones\">Impuestos Retenidos : </td><td class=\"righted nowrap $claseImporte\"><span class=\"sign\">-</span> $".number_format($invTotImpRete,2)."</td></tr>";
                    }
                }
                if (isset($total) && abs($total-$sumtotal)<$epsilon) $claseImporte="bggreen";
                else $claseImporte="bgred\" calculado=\"".number_format($sumtotal,2);
                echo "<tr><td colspan=\"$colSpan\" class=\"righted total\">Total : </td><td class=\"righted nowrap  $claseImporte\">$".number_format($total,2)."</td></tr>";
                echo "</tbody></table>";
            }
            if ($esPago){
                echo "<p><b>Facturas Pagadas</b></p>";
                echo "<table id=\"table_of_paid_invoices\" border=\"1\">";
                echo "<thead>";
                echo "<tr><th>Factura</th><th>UUID</th><th>Status</th></tr>";
                echo "</thead>";
                echo "<tbody>";
                $paidIdx=0;
                $pagos = $file["xml"]->get("pago_doctos");
                clog3("PAGOS: \n".arr2str($pagos));
                if (isset($pagos["@iddocumento"])) $pagos = [$pagos];
                foreach ($pagos as $pago) {
                    $paidIdf = "{$xmlIdx}_{$paidIdx}";
                    $paidName = "pago$paidIdf";
                    $unfillables.="\"$paidName\",";
                    $pagoUUID=strtoupper($pago["@iddocumento"]); // required
                    $pagoSerie=$pago["@serie"]??""; // optional
                    $pagoFolio=isset($pago["@folio"])?strtoupper(ltrim($pago["@folio"],'0')):""; // optional
                    $pagoMoneda=$pago["@monedadr"];
                    //$pagoTipoCambio=$pago["@tipocambiodr"]??"";  // optional. Doesnt exist in 2.0
                    //$pagoMetodoPago=$pago["@metododepagodr"]??""; // required. Doesnt exist in 2.0
                    clog3("paidIdf=$paidIdf");
                    $pagoParcialidad=$pago["@numparcialidad"]??""; // optional
                    $pagoSaldoAnt=$pago["@impsaldoant"]??""; // optional
                    $pagoPagado=$pago["@imppagado"]??""; // optional
                    $pagoSaldo=$pago["@impsaldoinsoluto"]??""; // optional
                    $pagoMoneda=$pago["@monedadr"]??"";
                    $pagoEquiv=$pago["@equivalenciadr"];
                    if (isset($pagoSaldo[0])) {
                        if ((+$pagoSaldo)==0) $pagoStatus="PAGADA";
                        else $pagoStatus="PARCIAL";
                    } else  $pagoStatus="DESCONOCIDO";

                    $fileLink=getRutaReal($pagoUUID);
                    clog3("fileLink = $fileLink");
                    $target="$pagoSerie$pagoFolio";
                    clog3("target=$target");
                    $pagoFolio2="";
                    if (!empty($pagoSerie)) {
                        $pagoFolio2=$pagoSerie;
                        if (!empty($pagoFolio)) $pagoFolio2.="-";
                    }
                    if (!empty($pagoFolio)) $pagoFolio2.=$pagoFolio;
                    if(empty($pagoFolio2)&&!empty($pagoUUID)) $pagoFolio2=substr($pagoUUID,-10);
                    if (isset($fileLink)) $fileLink="<A HREF=\"$fileLink\" target=\"$target\" tabindex=\"-1\">$pagoFolio2</A>";
                    else $fileLink=$target;
                    echo "<tr><td>$fileLink<input type=\"hidden\" id=\"pagoSerie$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][serie]\" value=\"$pagoSerie\"><input type=\"hidden\" id=\"pagoFolio$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][folio]\" value=\"$pagoFolio\"></td>";
                    echo "<td data-max-width=\"140px\" class=\"isUUIDCell\" title=\"$pagoUUID\">$pagoUUID<input type=\"hidden\" id=\"pagoUUID$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][UUID]\" value=\"$pagoUUID\"></td>";
                    echo "<td>$pagoStatus<input type=\"hidden\" id=\"pagoParc$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][parc]\" value=\"$pagoParcialidad\"><input type=\"hidden\" id=\"pagoAnt$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][anterior]\" value=\"$pagoSaldoAnt\"><input type=\"hidden\" id=\"pagoPagado$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][pagado]\" value=\"$pagoPagado\"><input type=\"hidden\" id=\"pagoSaldo$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][saldo]\" value=\"$pagoSaldo\"><input type=\"hidden\" id=\"pagoMoneda$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][moneda]\" value=\"$pagoMoneda\"><input type=\"hidden\" id=\"pagoEquiv$paidIdf\" name=\"factura[$xmlIdx][pagodocto][$paidIdx][equiv]\" value=\"$pagoEquiv\"></td>";
                    echo "</tr>\n";
                    $paidIdx++;
                }
                echo "</tbody></table>";
                echo "<p class=\"blink\">Recuerde <b class=\"importantValue\">Agregar Documentos</b> para confirmar y procesar la información.</p>";
            }
            if ($esFactura) echo "<div class=\"padt10\">Agregue Entrada de Almacén:<button type=\"button\" id=\"eafilebtn$xmlIdx\" class=\"marL4\" onclick=\"ebyid('eafile$xmlIdx').click();\">Anexar PDF</button><input type=\"file\" name=\"eafile[$xmlIdx]\" id=\"eafile$xmlIdx\" accept=\".pdf\" class=\"hidden eafile\" onchange=\"addEAFile($xmlIdx);\"><div id=\"eamsg$xmlIdx\" class=\"redden\"></div></div>";
            echo "</td>\n";
            $xmlIdx++;
        }
        echo "                  </tr>\n";
        if (!empty($file["errmsg"])) {
              echo "                        <tr class=\"uploadErrorMessage\"><td class=\"vexpand padding0 top xmlerrorcell\" colspan=\"2\"><ul class=\"margintop0 marginbottom0\"><li class=\"wordwrap bgred vexpand\">Errores : $file[errmsg]</li></ul></td></tr>\n";
        }
        echo "                        <tr class=\"uploadBottomLine\"><td colspan=\"2\" class=\"centered\">";
        echo "<input type=\"button\" name=\"detalle\" value=\"Detalle\" onclick=\"displayFollowingDetail(this);\" tabindex=\"".(1000+$fullIdx)."\"><div class=\"detail hidden\">".procesaDetalle($file,strval($fullIdx))."</div>";
        echo "</td></tr>\n";
      }
    }
    echo "                </table>\n";
    echo "              </div>\n";
    if (!empty($fillables)||!empty($unfillables)) {
        $buttonValue="Agregar Documentos";
        $debugField="";
        if ($esAdmin) $debugField="<input type=\"hidden\" name=\"debugField\" id=\"debugField\" value=\"1\">";
        echo "              <div id=\"xml_insert\" class=\"margintop\">$debugField<input type=\"submit\" name=\"insertxml\" id=\"insertxml\" value=\"$buttonValue\" class=\"importantValue highlight\" onclick=\"document.forma_alta.submited=this.id;\"></div>\n";
    }
}
?>
            </form>
          </div>  <!-- FIN BLOQUE USUARIO -->
<?php
if (!empty($fillables)) {
    echo "          <script>setFillables([ $fillables ]);</script>\n";
}
if (isset($facturas)) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"ajustaTablasConcepto();ekil(this);\">";
} else if (isset($files)) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"ajustaTablasConcepto();overlayClose();ekil(this);\">";
}

clog1seq(-1);
clog2end("templates.altafactura");
