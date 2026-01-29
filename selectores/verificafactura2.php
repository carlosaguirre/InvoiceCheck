<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$modificaProc = modificacionValida("Procesar");
$esRechazante = validaPerfil("Rechaza Aceptadas")||$esSistemas;
$esDesarrollo = in_array(getUser()->nombre, ["admin"]);
$esCompras = validaPerfil("Compras");
$level = 0;
if ($esCompras) $level=1;
if ($modificaProc || $esSistemas) $level=3;
require_once "clases/Facturas.php";
$epsilon = 0.015; //0.000001;
$factId = "".($_REQUEST["facturaId"]??"");
$ctfAuth = ("".($_REQUEST["ctfAuth"]??"0"))==="1";
$soloLectura = isset($_REQUEST["readonly"]);
$invObj = new Facturas();
$invObj->rows_per_page=0;
if (isset($factId[0])) $factData = $invObj->getData("id=$factId");
if (empty($factData)) {
    echo "<H2>Error al buscar la factura $factId</H2>";
    echo "<p>Por favor presione Regresar y consulte a su Administrador</p>";
    return;
}
$factura = $factData[0];
$tc=null;$tcx="COMPROBANTE";$tcd="Comprobante";
$esFactura=false;$esPago=false;$esEgreso=false;$esTraslado=false;
if (!empty($factura["tipoComprobante"])) {
    $tipoComprobante = $factura["tipoComprobante"];
    $tc = strtolower($tipoComprobante[0]);
    switch($tc) {
        case "i": $tcx="INGRESO"; $esFactura=true; $tcd="Factura"; break;
        case "e": $tcx="EGRESO";  $esEgreso=true;    $tcd="Nota";    break;
        case "p": $tcx="PAGO";    $esPago=true;    $tcd="Recibo";  break;
        case "t": $tcx="TRASLADO";$esTraslado=true; $tcd="Traslado"; break;
    }
}
if (isset($factura["rfcGrupo"][0])) {
    require_once "clases/Grupo.php";
    $gpoObj=new Grupo();
    $gpoData=$gpoObj->getData("rfc='$factura[rfcGrupo]'",0,"alias,razonSocial");
    if (isset($gpoData[0])) $gpoData=$gpoData[0];
    else $gpoData=null;
} else $gpoData=null;
$folio=$factura["folio"]??"";
$uuid=$factura["uuid"]??"";
$sysPath=$_SERVER["DOCUMENT_ROOT"];
$ubicacion = $factura["ubicacion"];
$nombreXML = $factura["nombreInterno"];
$hasXML = (isset($nombreXML[0])&&file_exists($sysPath.$ubicacion.$nombreXML.".xml"));
$nombrePDF = $factura["nombreInternoPDF"];
$hasPDF = (isset($nombrePDF[0])&&file_exists($sysPath.$ubicacion.$nombrePDF.".pdf"));
$codigoProveedor=$factura["codigoProveedor"]??"";
if (isset($codigoProveedor[0])) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
    $prvData = explode("|", $prvObj->getValue("codigo", $codigoProveedor, "rfc, razonSocial"));
    if ($esPago) {
        if ($hasXML && (!isset($factura["fechaReciboPago"][0]) || !isset($factura["saldoReciboPago"][0]))) {
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
            if (isset($cfdiObj)) {
                $cpfldarr=["id"=>$factId];
                $detalle="";
                $fechaPagoCP=$cfdiObj->get("pago_fecha");
                if (is_array($fechaPagoCP)) $fechaPagoCP=$fechaPagoCP[0];
                $fechaPagoCP=str_replace("T", " ", $fechaPagoCP);
                $montoPagoCP=$cfdiObj->get("pago_monto_total");
                if (is_array($montoPagoCP)) $montoPagoCP=$montoPagoCP[0];
                if (!isset($factura["fechaReciboPago"][0])) {
                    $factura["fechaReciboPago"]=$fechaPagoCP;
                    $cpfldarr["fechaReciboPago"]=$fechaPagoCP; // checar formato
                    $detalle.="FechaRP=$fechaPagoCP";
                }
                if (!isset($factura["saldoReciboPago"][0])) {
                    $factura["saldoReciboPago"]=$montoPagoCP;
                    $cpfldarr["saldoReciboPago"]=$montoPagoCP;
                    if (isset($detalle[0])) $detalle.=", ";
                    $detalle.="MontoRP=$montoPagoCP";
                }
                $invObj->saveRecord($cpfldarr);
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
                $prcObj->alta("FechaMontoPago",$factId,"cpUpgrade",$detalle);
            }
        }
        $tmpList = explode("|", $invObj->getList("idReciboPago", $factId, "id,serie,folio,UUID,saldoReciboPago,ubicacion,nombreInternoPDF,nombreInterno"));
        //clog3("LISTA DE PAGO: ".json_encode($tmpList)."\n".(isset($tmpList[0])?"1":"0").(isset($tmpList[0][0])?"1":"0").(isset($tmpList[1])?"1":"0"));//$invObj->log);
        $factList = [];
        if (isset($tmpList[1])||isset($tmpList[0][0])) {
            for($i=0; isset($tmpList[$i]); $i++) {
                //clog3("TMPLIST $i = '".$tmpList[$i]."' (".strlen($tmpList[$i]).")");
                $a = floor($i/8);
                $b = $i%8;
                if (!isset($factList[$a])) $factList[$a] = [];
                $factList[$a][$b]=$tmpList[$i];
            }
            $prvData[2] = $factList;
        } else if ($esDesarrollo && $hasXML) {
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
            if (isset($cfdiObj)) {
                $doctos=$cfdiObj->get("pago_doctos");
                if (isset($doctos["@iddocumento"])) $doctos=[$doctos];
                $uids=[];
                foreach($doctos as $dIdx=>$doct) {
                    if (isset($doct["@iddocumento"][0])) $uids[]=$doct["@iddocumento"];
                }
                if (isset($uids[0][0])) {
                    $invData=$invObj->getDataByFieldArray(["uuid"=>$uids],0,"id,folio,serie,uuid,ciclo,ubicacion,nombreInterno,nombreInternoPDF,idReciboPago,statusn,status");
                    foreach ($invData as $invIdx => $invRow) {
                        $idRP="".($invRow["idReciboPago"]??"");
                        if (isset($idRP[0])&&$idRP!=="0") {
                            $rpData=$invObj->getData("id=$idRP","folio,serie,uuid,ciclo,ubicacion,nombreInterno,nombreInternoPDF,statusn,status");
                            if (isset($rpData[0])) $invData[$invIdx]["rpData"]=$rpData[0];
                        }
                    }
                    $prvData[2]=$invData;
                } else $prvData[2]="";
                // toDo: buscar si existen facturas con uuid = cfdi:Complemento|pago10:Pagos|pago10:Pago|pago10:DoctoRelacionado[@iddocumento]
                // toDo: mostrar facturas, validarlas, modificarlas (agregar idReciboPago=$factId) y anexar sus datos en $prvData[2]
                // toDo: poner un indicador de que el recibo de pago y sus facturas fueron modificados, una paloma o algo asi
                // [id,folio,uuid,ubicacion,nombreInternoPDF,idReciboPago,statusn,status,rpData:[folio,uuid,ubicacion]]
            } else $prvData[2]="";
        } else $prvData[2]="";
    //} else if ($esTraslado) {
    //    ;
    } else {
        require_once "clases/Conceptos.php";
        $cptObj = new Conceptos();
        $cptObj->rows_per_page=0;
        $prvData[2] = $cptObj->getData("idFactura='$factId'");
    }
} else $prvData = ["", "",[]];
$hasEA=false;
if(($factura["ea"]??"0")==="1") {
    $eacp=trim(str_replace("-", "", $codigoProveedor));
    $usIdx=strrpos($nombreXML, "_");
    if ($usIdx!==false && $usIdx>0) {
        $eafolio=substr($nombreXML, $usIdx+1);
    } else {
        $eafolio=(isset($folio[0])?$folio:$uuid);
        if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
    }
    if (isset($folio[0]) && isset($serie[0])) $eafolio2=$serie.$folio;
    if (!isset($eafolio2)||$eafolio===$eafolio2) {
        if (isset($folio[0]) && $eafolio!==$folio) $eafolio2=substr($folio, -10);
        else $eafolio2=null;
    }
    $eafecha=substr(str_replace("-","", $factura["fechaFactura"]),2,6);
    $nombreEA = "EA_{$eacp}_{$eafolio}_{$eafecha}";
    //echo "<!-- Nombre EA1: {$ubicacion}$nombreEA -->\n";
    if (file_exists($sysPath.$ubicacion.$nombreEA.".pdf")) $hasEA = true;
    else {
        echo "<!-- No se encontró EA1:'{$nombreEA}' -->\n";
        if ($esFactura) {
            if (isset($eafolio2[0])) {
                $nombreEA = "EA_{$eacp}_{$eafolio2}_{$eafecha}";
                $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
                if (!$hasEA) echo "<!-- No se encontró EA2: '{$nombreEA}' -->\n";
            }
            if (!$hasEA && isset($nombreXML[0])) {
                $eafolio3 = substr($nombreXML, -10);
                $nombreEA = "EA_{$eacp}_{$eafolio3}_{$eafecha}";
                $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
                if (!$hasEA) echo "<!-- No se encontró EA3: '{$nombreEA}' -->\n";
            }
        } else if ($esEgreso||$esPago) {
            $nombreEA = "EA_{$eacp}_".($esEgreso?"NC":"RP")."_{$eafolio}_{$eafecha}";
            $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
            if (!$hasEA) echo "<!-- No se encontró EA".($esEgreso?"E":"P").": '{$nombreEA}' -->\n";
        } else if ($esTraslado) {
            $nombreEA = "EA_{$eacp}_TR_{$eafolio}_{$eafecha}";
            $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
            if (!$hasEA) echo "<!-- No se encontró EAT: '{$nombreEA}' -->\n";
        } else echo "<!-- EA: SIN TIPO COMPROBANTE! -->\n";
    }
}
$statusn=$factura["statusn"]??null;
$esPendiente = isset($statusn) && Facturas::estaPendiente(+$factura["statusn"]);//($factura["status"]==="Pendiente");
if ($esPendiente) {
    $highClass = "highlight";
?>
<H3>Corrobore que <span class="highlight">Num. de Pedido</span> y <span class="highlight">Código</span> de los artículos sean los correctos antes de Confirmar.</H3>
<?php
}
//clog2("\$factura ".json_encode($factura,JSON_PRETTY_PRINT));
?>
<table id="tabla_valida_pedido" class="tableWithScrollableCells">
  <tr style="height: 1px;"><td style="width: 30%; padding_right: 5px; vertical-align: top; height: inherit;">
    <ul class="marginbottom lefted">
      <li>Documentos :
        <?= isset($nombreXML[0])?"<a href=\"$ubicacion$nombreXML.xml\" data-title=\"Archivo XML\" title=\"CFDI XML\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/xml200.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>":"" ?>
        <?= isset($nombrePDF[0])?"<a href=\"$ubicacion$nombrePDF.pdf\" data-title=\"Archivo PDF\" title=\"CFDI PDF\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/pdf200.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>":"" /*"<img src=\"imagenes/icons/invChk200.png\" width=\"20\" height=\"20\" class=\"noBorder2 bgred bxsbrd pointer marginV2 hidBdr\" title=\"Anexar PDF\" onclick=\"const fx=ebyid('attachpdffile');fx.click();\"><span id=\"attachpdfcap\" class=\"redden boldValue padl1\">SIN PDF</span><input type=\"file\" name=\"attachpdffile\" id=\"attachpdffile\" accept=\".pdf\" class=\"hidden\" onchange=\"const c=ebyid('attachpdfcap');const m=ebyid('attachpdfmsg');if(this.files.length==0||this.files[0].name.length==0){c.textContent='SIN PDF';m.textContent='Presione icono azul para anexar CFDI-PDF';}else{c.textContent='';m.textContent='';}\"><br><span id=\"attachpdfmsg\" class=\"smaller bgred\">Presione icono azul para anexar CFDI-PDF.</span>"*/ ?>
        <?= $hasEA?"<a href=\"$ubicacion$nombreEA.pdf\" data-title=\"Entrada de Almacén\" title=\"EA PDF\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/pdf200EA.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>":"" ?>
      </li>
      <?php /* <?= empty($nombrePDF)?"":"<LI>Archivo PDF : <A HREF=\"$ubicacion$nombrePDF.pdf\" target=\"archivopdf\"><B>$nombrePDF.pdf</B></A></LI>" ?>
      <?= empty($nombreXML)?"":"<LI>Archivo XML : <A HREF=\"$ubicacion$nombreXML.xml\" target=\"archivoxml\"><B>$nombreXML.xml</B></A></LI>" */ ?>
      <li>Folio Fiscal(UUID) : <b><?= reduccionMuestraDeCadenaLarga($uuid,8) ?></b></li>
<?php
/*
<li>Emisor : <b><?= $prvData[1] ?></b></li>
<li>R.F.C. : <b><?= $prvData[0] ?></b></li>
<li>Código : <b><?= $codigoProveedor ?></b></li> */
if(isset($codigoProveedor[0])) echo "<li title=\"{$prvData[1]}\">Proveedor : <b>$codigoProveedor</b></li>";
if(isset($gpoData)) echo "<li title=\"$gpoData[razonSocial]\">Empresa : <b>$gpoData[alias]</b></li>";
echo "<li>Tipo : $tcx</li>";
if(isset($factura["usoCFDI"][0])) {
    require_once "clases/catalogoSAT.php";
    $usoDesc=CatalogoSAT::getValue(CatalogoSAT::CAT_USOCFDI, "codigo", $factura["usoCFDI"], "descripcion");
    echo "<li>Uso CFDI : <b>$factura[usoCFDI]</b> ($usoDesc)</li>";
}
if (isset($factura["fechaFactura"][0])) {
    $fechaFactura=date("Y-m-d H:i:s",strtotime($factura["fechaFactura"]));
    echo "<li><span id=\"capFecha\">Fecha</span> : <b>$fechaFactura</b></li>";
}
if (isset($factura["fechaReciboPago"][0])) {
    // TODO: Para cada CFDI de Pago, para cada Pago, guardar campo Pagos:Pago:FechaPago
    $fechaPago=date("Y-m-d",strtotime($factura["fechaReciboPago"]));
    echo "<li><span id=\"capPFecha\">Fecha Pago</span> : <b>$fechaPago</b><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"console.log('Fecha de pago: \'$factura[fechaReciboPago]\'->\'$fechaPago\'');const cf=ebyid('capFecha');cladd(cf, 'inblock');cf.style.width=ebyid('capPFecha').offsetWidth+'px';ekil(this);\"></li>";
}
/* <!-- li>Certificado : <b> $factura["noCertificado"] </b></li -->
<!-- li>Version : <b> $factura["version"] </b></li --> */
if (isset($factura["metodoDePago"][0])) {
    $metodoPago = $factura["metodoDePago"];
    require_once "clases/catalogoSAT.php";
    $metodoDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_METODOPAGO, "codigo", $metodoPago, "descripcion");
    echo "<li>M&eacute;todo de Pago : <b>$metodoPago</b> ($metodoDesc)</li>";
}
if (isset($factura["formaDePago"][0])) {
    $formaPago = $factura["formaDePago"];
    require_once "clases/catalogoSAT.php";
    $formaDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_FORMAPAGO, "codigo", $formaPago, "descripcion");
    echo "<li>Forma de Pago : <b>$formaPago</b> ($formaDesc)</li>";
}
if (isset($factura["serie"][0]))
    echo "<li>Serie : <b>$factura[serie]</b></li>";
if (isset($folio[0]))
    echo "<li>Folio : <b>$folio</b></li>";
if ($esPago && isset($factura["saldoReciboPago"][0])) {
    $saldoPago = $factura["saldoReciboPago"];
    echo "<li>Monto pagado : <b>$".number_format($saldoPago,2)."</b></li>";
}
$realStatus=Facturas::statusnToRealStatus($statusn,$tipoComprobante,$level);
$sttttl=$esAdmin?" title='STT:$statusn, TC:$tipoComprobante, LV:$level'":"";
echo "<li{$sttttl}>Status : <b>$realStatus</b></li>";
?>
      <li class="<?= (strpos($factura["mensajeCFDI"],"satisfactoriamente")===FALSE||$factura["estadoCFDI"]!=="Vigente")?"bgred":"bggreen" ?>">CFDI: <b><?= $factura["mensajeCFDI"] ?><br><?= $factura["estadoCFDI"] ?></b></li>
<?php
if (!$esPago && !$esTraslado) { ?>
      <li><span class="vAlignCenter nowrap<?=isset($highClass)?" $highClass":""?>">Num. de Pedido : <input type="text" name="numpedido" id="numpedido" class="widavailable" value="<?= $factura["pedido"] ?>" <?=$soloLectura?"readonly":"onchange=\"agregaValorPost(this);\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"agregaDatoPost('f_numpedido', '$factura[pedido]');agregaDatoPost('fold_numpedido', '$factura[pedido]');this.parentNode.removeChild(this);\"" ?>></span></li>
<?php
} ?>
    </ul></td>
    <td style="height: inherit;xoverflow-x:auto;"><h3 class="wide">Documentos relacionados</h3><div class="scrollableCell" style="max-width:100%;height:calc(100% - 22px);"><table border="1" id="table_of_concepts">
      <thead><tr style="white-space:nowrap;">
<?php
if ($esPago) { ?>
      <th><span idx="0" arr="id">Folio</span><img src="imagenes/icons/next01_20b.png" onclick="switchHeadCell(this);"></th><th>Fecha</th><th>Documentos</th>
<?php
//} else if ($esTraslado) {
    // Header a mostrar de traslados Carta Porte
} else { ?>
      <th>Cantidad</th><th id="headCode">C&oacute;digo</th><th>Descripci&oacute;n</th><th>P.Unit.</th><th>Importe</th>
<?php
} ?>
      </tr></thead>
      <tbody>
<?php
if ($esPago) {
    if (!isset($prvData[2][0])||!is_array($prvData[2][0])) {
/*        if ($esDesarrollo && $hasXML) {
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
            if (isset($cfdiObj)) {
                echo "<tr><td colspan=\"3\">SI</td></tr>";
            } else echo "<tr><td colspan=\"3\">NO</td></tr>";
        }*/
        echo "<tr><td colspan=\"3\">NO SE IDENTIFICARON FACTURAS RELACIONADAS</td></tr>"; // toDo: cambiar por una imagen de tache rojo
    } else foreach ($prvData[2] as $pago) {
        if (isset($pago["id"]) && isset($pago["idReciboPago"])) {
// [id,folio,uuid,ubicacion,nombreInternoPDF,idReciboPago,statusn,status,rpData:[folio,uuid,ubicacion,nombreInternoPDF,statusn,status]]
            $pgub=$pago["ubicacion"];
            $pgfolio=$pago["folio"];
            $pguuid=$pago["uuid"];
            if (!isset($pgfolio[0])) $pgfolio="[".substr($pguuid, -10)."]";
            if (isset($pago["nombreInternoPDF"][0])) {
                $pghref=$pgub.$pago["nombreInternoPDF"].".pdf";
            //else if (isset($pago["nombreInterno"][0])) $pghref.=$pgub.$pago["nombreInterno"].".xml";
            //else $pghref="";
            //if (isset($pghref[0]))
                $pglink="<A HREF=\"$pghref\" TARGET=\"factura\" tabindex=\"-1\">$pgfolio</A>";
            } else if (isset($pago["nombreInterno"][0])) {
                $pglink="<SPAN class=\"alink btst nobg\" title=\"FACTURA XML\" onclick=\"generaFactura('$pago[nombreInterno]','$pago[ciclo]','factura');\">".$pgfolio."</SPAN>";
            } else $pglink=$pgfolio;
            $pgstts="Asignada a ";
            if (isset($pago["rpData"])) {
                $rpDt=$pago["rpData"];
                $rpub=$rpDt["ubicacion"];
                $rpfolio=$rpDt["folio"];
                $rpuuid=$rpDt["uuid"];
                if (!isset($rpfolio[0])) $rpfolio="[".substr($rpuuid, -10)."]";
                $pgRowAtt=$rpfolio===$folio?"":" class=\"stroke\"";
                if (isset($rpDt["nombreInternoPDF"][0])) {
                    $rphref=$rpub.$rpDt["nombreInternoPDF"].".pdf";
                    $pgstts.="<A HREF=\"$rphref\" TARGET=\"factura\" tabindex=\"-1\">$rpfolio</A>";
                } else if (isset($rpDt["nombreInterno"][0])) {
                    $pgstts.="<SPAN class=\"alink btst nobg\" title=\"FACTURA XML\" onclick=\"generaFactura('$rpDt[nombreInterno]','$rpDt[ciclo]','factura');\">".$rpfolio."</SPAN>";
                } else $pgstts.=$rpfolio;
            } else $pgstts.="<i>$pago[idReciboPago]</i>";
?>
        <tr<?=$pgRowAtt?>><td><?= $pglink ?></td>
            <td><?= $pguuid ?></td>
            <td><?= $pgstts ?></td></tr>
<?php
            continue;
        }
        if (!is_array($pago) || !isset($pago[7])) {
            clog3("PAGO INCOMPLETO: ".json_encode($pago));
            continue;
        }
        $fact_id=$pago[0];
        $fact_serie=$pago[1];
        $fact_folio=$pago[2];
        $fact_uuid=$pago[3];
        $fact_saldo=+$pago[4];
        $fact_ubicacion=$pago[5];
        $fact_pdfname=$pago[6];
        $fact_xmlname=$pago[7];
        $fact_name="";
        $fact_tgt="";
        $hasFSerie=isset($fact_serie[0]);
        $hasFFolio=isset($fact_folio[0]);
        if ($hasFSerie) {
            $fact_name.=$fact_serie;
            $fact_tgt.=$fact_serie;
            if ($hasFFolio) $fact_name.="-";
        }
        if($hasFFolio) {
            $fact_name.=$fact_folio;
            $fact_tgt.=$fact_folio;
        }
        if(!isset($fact_name[0])) {
            $fact_name="SIN FOLIO";
            $fact_tgt="factura";
        }
        $fact_href=$fact_ubicacion;
        if (isset($fact_pdfname[0])) $fact_href.=$fact_pdfname.".pdf";
        else if (isset($fact_xmlname[0])) $fact_href.=$fact_xmlname.".xml";
        else $fact_href="";
        if (isset($fact_href[0])) $fact_link="<A HREF=\"$fact_href\" TARGET=\"$fact_tgt\" tabindex=\"-1\">$fact_name</A>";
        else $fact_link="";
        if ($fact_saldo==0) $fact_stts = "PAGADA";
        else $fact_stts = "PARCIAL";
?>
        <tr><td><?= $fact_link ?></td>
            <td><?= $fact_uuid ?></td>
            <td><?= $fact_stts ?></td></tr>
<?php
    }
//} else if ($esTraslado) {
    // Datos a mostrar de traslados Carta Porte
} else {
    $subtotal = $factura["subtotal"];
    $total = $factura["total"];
    $sumaImportes=0;
    $sumaDescuento=0;
    $sumaTraslado=0;
    $sumaRetenido=0;
    foreach ($prvData[2] as $concepto) {
        $clsUni="";
        $clsDsc="";
        $cantidad = +$concepto["cantidad"];
        $precioUnitario = +$concepto["precioUnitario"];
        $importe = +$concepto["importe"];
        $calculado = $cantidad * $precioUnitario;
        $diferencia = abs($calculado-$importe);
        $sumaImportes+=$importe;

        $sumaDescuento += +$concepto["importeDescuento"];
        $sumaTraslado += +$concepto["impuestoTraslado"];
        $sumaRetenido += +$concepto["impuestoRetenido"];

        if ($diferencia<$epsilon) {
            $claseImporte="bggreen";
            $tipEval="Importe correcto";
            //if ($diferencia!==0) $tipEval.=". Diferencia descartable: ".number_format($diferencia,9).".";
        } else {
            $claseImporte="bgred";
            $tipEval="Importe no corresponde al calculado: $".number_format($calculado,6);
        }
        //$titleUnidad = "";
        $unidad = htmlentities($concepto["unidad"]??"");
        $claveUnidad = $concepto["claveUnidad"]??"";
        if (isset($claveUnidad[0])) {
            require_once "clases/catalogoSAT.php";
            $nombreClaveUnidad = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD, "codigo", $claveUnidad, "nombre");
            if (strcasecmp($unidad, $nombreClaveUnidad)==0||stripos($unidad, $nombreClaveUnidad)!==false) $clsUni=" bggreen2";
            //if (!empty($nombreClaveUnidad))
            //    $titleUnidad = " title=\"ClaveUnidad SAT: $claveUnidad='$nombreClaveUnidad'\"";
            //else
            //    $titleUnidad = " title=\"ClaveUnidad: $claveUnidad (No definida en SAT)\"";
        } else $nombreClaveUnidad="";
        //$titleCodigo = "";
        $descripcion = htmlentities($concepto["descripcion"]??"");
        $claveProdServ = $concepto["claveProdServ"]??"";
        if (isset($claveProdServ[0])) {
            require_once "clases/catalogoSAT.php";
            $nombreClaveProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV, "codigo", $claveProdServ, "descripcion");
            if (isset($nombreClaveProdServ[0])) {
                //$titleCodigo = " title=\"ClaveProdServ SAT: $claveProdServ='$nombreClaveProdServ'\"";
                if (strcasecmp($descripcion, $nombreClaveProdServ)==0||stripos($descripcion, $nombreClaveProdServ)!==false) $clsDsc=" bggreen2";
                if (substr($claveProdServ, -2)==="00") {
                    $rowCPS="<b><span class=\"fixWid inblock\" fixId=\"headCode\">$claveProdServ</span>$nombreClaveProdServ'</b>";
                    $numClaveProdServ=intval($claveProdServ);
                    for ($i=1; $i < 100; $i++) {
                        $nxtClaveProdServ=$numClaveProdServ+$i;
                        $nxtNombreProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo", $nxtClaveProdServ, "descripcion");
                        if (isset($nxtNombreProdServ[0])) {
                            if (strcasecmp($descripcion, $nxtNombreProdServ)==0||stripos($descripcion, $nxtNombreProdServ)!==false) $clsDsc=" bggreen2";
                            $rowCPS.="<br><span class=\"fixWid inblock\" fixId=\"headCode\">$nxtClaveProdServ</span>$nxtNombreProdServ";
                        }
                        else break;
                    }
                } else $rowCPS="<span class=\"fixWid inblock\" fixId=\"headCode\">$claveProdServ</span>$nombreClaveProdServ";
            } else {
                //$titleCodigo=" title=\"ClaveProdServ SAT: $claveProdServ (no definida en SAT)\"";
                $rowCPS="$claveProdServ desconocida";
            }
        } else {
            $rowCPS="Sin Clave de Producto o Servicio";
        }
        if ($esTraslado) $codeElemScr=" &nbsp; ";
        else {
            $fixedCode=mb_strtoupper(html_entity_decode(mb_strtolower($concepto["codigoArticulo"])));
            $conceptoId=$concepto["id"];
            $changeScriptlet=($soloLectura?" readonly":" removeSpaces=\"1\" onchange=\"agregaValorPost(this);\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"agregaDatoPost('f_articulo[$conceptoId]', '$concepto[codigoArticulo]');this.parentNode.removeChild(this);\""); //  onkeydown=\"return event.which!=32;\" // ToDo: Agregar el jueves que puedo verificar que no causa error
            $codeElemScr="<input type='text' id='articulo[$conceptoId]' name='articulo[$conceptoId]' value='$fixedCode' style='width: 100px;'{$changeScriptlet}>";
        }
?>
        <tr>
          <td<?= $titleUnidad??"" ?> class="lefted"><?= $cantidad ?>&nbsp;<?= $unidad ?></td>
          <td<?= $titleCodigo??"" ?> class="lefted shrinkCol<?= isset($highClass[0])?" $highClass":"" ?>"><?=$codeElemScr?></td>
          <!--                                                                                                                                                                                                    onkeydown="return preventKeyCodes(event, [32]);" -->
          <td class="lefted"><?= $descripcion ?></td>
          <td class="shrinkCol righted">$<?= number_format($precioUnitario,2) ?></td>
          <td class="shrinkCol righted <?= $claseImporte ?>" title="<?= $tipEval ?>">$<?= number_format($importe,2) ?></td>
        </tr>
        <tr class="satKeys invoice"><td class="lefted brVanish<?=$clsUni?>"><?= isset($claveUnidad[0])?($claveUnidad.(isset($nombreClaveUnidad[0])?" = '$nombreClaveUnidad'":" desconocida")):"Sin Clave Unidad" ?></td><td class="lefted blVanish nopad<?=$clsDsc?>" colspan="4"><div class="padv5 mxHg50 yFlow minScrBar"><?= $rowCPS??"" ?></div></td></tr>
<?php
    }
    $difSubtotal = abs($sumaImportes-$subtotal);
    if ($difSubtotal<$epsilon) {
        $claseSubtotal="bggreen";
        $tipSubtotal="Subtotal correcto";
    } else {
        $claseSubtotal="bgred";
        $tipSubtotal="Subtotal calculado en $".number_format($sumaImportes,2);
    }
    $descuento=$factura["importeDescuento"];
    $trasladado=$factura["impuestoTraslado"];
    $retenido=$factura["impuestoRetenido"];
    $totalCalculado=$subtotal-$descuento+$trasladado-$retenido;
?>
        <tr><td colspan="4" class="righted">Subtotal : </td><td class="<?= $claseSubtotal ?>" title="<?= $tipSubtotal ?>">$<?= number_format($subtotal,2) ?></td></tr>
<?php
    if ($descuento!=0 || $sumaDescuento!=0) {
        $descClase = "";
        if (abs($descuento-$sumaDescuento)<$epsilon) {
            $descClase="bggreen";
            $tipClase="Descuento correcto";
        } else {
            $descClase="bgred";
            $tipClase="Descuento calculado en $".number_format($sumaDescuento,2);
        }
        echo "<tr><td colspan=\"4\" class=\"righted\">Descuentos : </td><td class=\"$descClase\" title=\"$tipClase\">-$".number_format($descuento,2)."</td></tr>";
    }
    if ($trasladado!=0 || $sumaTraslado!=0) {
        $descClase = "";
        if (abs($trasladado-$sumaTraslado)<$epsilon) {
            $descClase="bggreen";
            $tipClase="Impuesto trasladado correcto";
        } else {
            $descClase="bgred";
            $tipClase="Impuesto trasladado calculado en $".number_format($sumaTraslado,2);
        }
        echo "<tr><td colspan=\"4\" class=\"righted\">Impuestos Trasladados : </td><td class=\"$descClase\" title=\"$tipClase\">$".number_format($trasladado,2)."</td></tr>";
    }
    if ($retenido!=0 || $sumaRetenido!=0) {
        $descClase = "";
        if (abs($retenido-$sumaRetenido)<$epsilon) {
            $descClase="bggreen";
            $tipClase="Impuesto retenido correcto";
        } else {
            $descClase="bgred";
            $tipClase="Impuesto retenido calculado en $".number_format($sumaRetenido,2);
        }
        echo "<tr><td colspan=\"4\" class=\"righted\">Impuestos Retenidos : </td><td class=\"$descClase\" title=\"$tipClase\">-$".number_format($retenido,2)."</td></tr>";
    }
    $difTotal = abs($totalCalculado-$total);
    if ($difTotal<$epsilon) {
        $claseTotal="bggreen";
        $tipTotal="Total correcto";
    } else {
        $claseTotal="bgred";
        $tipTotal="Total calculado en $".number_format($totalCalculado,2);
    } ?>
        <tr><td colspan="4" class="righted">Total : </td><td class="<?= $claseTotal ?>" title="<?= $tipTotal ?>">$<?= number_format($total,2) ?></td></tr>
<?php 
}
$puedeRechazar = $modificaProc && !$ctfAuth && $statusn!=null && (
        $statusn==0 ||
        ($esRechazante && $statusn > 0 && $statusn < 32) ||
        ($esAdmin && $statusn > 0 && $statusn < 128)
    );
if ($ctfAuth) {
    global $ctfObj;if(!isset($ctfObj)){require_once "clases/Contrafacturas.php";$ctfObj=new Contrafacturas();}
    $ctfData=$ctfObj->getData("idFactura=$factId",0,"id");//"idContrarrecibo");
    $idCtf=$ctfData[0]["id"]??"";
}
?>
      </tbody></table><br>
    </div><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fee(lbycn('fixWid'),cl=>{const fxHd=ebyid(cl.getAttribute('fixId'));cl.style.width=fxHd.offsetWidth+'px';});<?= ($puedeRechazar?"addRejectButton($factId);":"").($ctfAuth?"addAuthCFButton($idCtf);":"") ?>ekil(this);">
    </td>
  </tr>
</table>
<?php
require_once "configuracion/finalizacion.php";
