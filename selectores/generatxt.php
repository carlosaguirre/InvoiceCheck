<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas");
$esAvance = validaPerfil("Avance");
$esCompras = validaPerfil("Compras");
$esProveedor = validaPerfil("Proveedor");

require_once "clases/Facturas.php";
if (!isset($invObj)) $invObj = new Facturas();
$invObj->rows_per_page = 0;
if (isset($_REQUEST["pageno"])) $invObj->pageno = $_REQUEST["pageno"];
if (isset($_REQUEST["limit"]))  $invObj->rows_per_page = $_REQUEST["limit"];

$where = "UPPER(tipoComprobante) in ('I','INGRESO')";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        if ($value = $_REQUEST[$pvalue]) {
            $where .= " AND " . $pvalue . " LIKE '%" . $value . "%'";
        }
    }
}
if (!isset($errMessage)) $errMessage="";
if (!empty($_POST["grupo"])) {
    $where .= " AND rfcGrupo='$_POST[grupo]'";
} else $errMessage .= "Debe seleccionar una empresa del grupo.<br>";

$soloUnProveedor=false;
if (!empty($_POST["proveedor"])) {
    $where .= " AND codigoProveedor='$_POST[proveedor]'";
    $soloUnProveedor=true;
}

if (empty($errMessage)) {
    $status = $_POST["status"];
    switch($status) {
        case "ExportadasHoy":
            $where.=" AND statusn&4=4 AND statusn<128 AND id IN (SELECT identif FROM proceso WHERE modulo='Factura' AND detalle like 'Facturas.Exp%' AND fecha>=curdate())";
            break;
        case "ExportadasAhora":
            $where.=" AND statusn&4=4 AND statusn<128 AND id IN (SELECT identif FROM proceso WHERE modulo='Factura' AND detalle like 'Facturas.Exp%' AND fecha>concat(curdate(),' ',hour(now())-1,':00:00'))";
            break;
        case "Exportadas": 
            $where.=" AND statusn&4=4 AND statusn<128";
            break;
        case "Aceptadas":  
            //$where.="status IN ('Aceptado','Contrarrecibo','RespSinCX','RespSinExp')";
            $where.=" AND statusn BETWEEN 1 AND 127 AND statusn&4=0";
            break;
        default: 
            //$where.=" AND status NOT IN ('Temporal','Pendiente','Rechazado')";
            $where.=" AND statusn BETWEEN 1 AND 127";
    }
    
    if (!empty($_POST["fechaInicio"]) && !empty($_POST["fechaFin"])) {
        list($fDay, $fMon, $fYr) = sscanf($_POST["fechaInicio"], "%2d/%2d/%4d");
        $fechaIni = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
        list($fDay, $fMon, $fYr) = sscanf($_POST["fechaFin"], "%2d/%2d/%4d");
        $fechaFin = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
        $where .= " AND fechaFactura BETWEEN '$fechaIni 00:00:00' AND '$fechaFin 23:59:59'";
    }
    
    // $text .= "Buscar=$_POST[Buscar], Command=$_POST[command]<br>\n";
    $prvRFCOpt       = $_SESSION['prvRFCOpt'];
    $prvRazSocOpt    = $_SESSION['prvRazSocOpt'];
    $gpoCodigoOpt    = $_SESSION['gpoCodigoOpt'];
    $gpoRazSocOpt    = $_SESSION['gpoRazSocOpt'];
        
    clog2("Where: $where");
    //$prvChunks = array_chunk($prvRazSocOpt,100,true);
    //$numpch = count($prvChunks);
    //for($px=0; $px<$numpch; $px++)
    //    clog2("PrvRazSocOpt $px:\n".arr2str($prvChunks[$px]));
    $data = $invObj->getData($where);
    //$countData = count($data);
    //clog2("DATA # = ".$countData);
    //if ($countData==0) {
    //    clog2("ZERO DATA. LOG:\n".$invObj->log);
    //}
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT']."/";
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT']."/";
    else $lookoutFilePath = "";
    if ($soloUnProveedor) echo "<input type=\"hidden\" id=\"unEmisor\" value=\"1\"/>";
    $dataIndex = 0;
    foreach ($data as $row) {
        $dataIndex++;
        $fId=$row["id"];
        $folio=$row["folio"];
        $tipFolio="";
        if (empty($folio)) {
            $fileSuffix=substr($row["uuid"],-10);
            $folio="[".$fileSuffix."]";
            $tipFolio=" title=\"Sin Folio\"";
        } else if (isset($folio[10])) {
            $fileSuffix=substr($folio,-10);
        } else {
            $fileSuffix=$folio;
        }
        
        $codigoProveedor = $row["codigoProveedor"];
        $proveedorAttribs=" class=\"noApply middle".($soloUnProveedor?" hidden":"")."\" title='".(isset($prvRazSocOpt[$codigoProveedor])?$prvRazSocOpt[$codigoProveedor]:"...Desconocido...")."'";
        $rfcGrupo = $row['rfcGrupo'];
        if (isset($gpoCodigoOpt[$rfcGrupo]))
            $aliasGrupo = ucfirst($gpoCodigoOpt[$rfcGrupo]);
        else $aliasGrupo=" . . . ";
        
        $xmlClass = "centered";
        $pdfClass = "centered";
        $ubicacion = $row["ubicacion"];
        $xmlName = $row["nombreInterno"];
        $hasXML=isset($xmlName[0]);
        if ($hasXML) {
            $xmlFilePath = $ubicacion . $xmlName . ".xml";
            $xmlFileExists = file_exists($lookoutFilePath.$xmlFilePath);
            if (!$xmlFileExists) $xmlClass.=" panalBGLight";
        } else $xmlFileExists = false;
        $pdfName = $row["nombreInternoPDF"];
        $hasPDF=isset($pdfName[0]);
        if ($hasPDF) {
            $pdfFilePath = $ubicacion . $pdfName . ".pdf";
            $pdfFileExists = file_exists($lookoutFilePath.$pdfFilePath);
            if (!$pdfFileExists) $pdfClass.=" panalBGLight";
        } else $pdfFileExists = false;
        $xmlAttribs = " class=\"$xmlClass\"";
        $pdfAttribs = " class=\"$pdfClass\"";
        if ($hasXML && !$xmlFileExists) $xmlAttribs.=" title=\"$xmlFilePath\"";
        if ($hasPDF && !$pdfFileExists) $pdfAttribs.=" title=\"$pdfFilePath\"";

        $total = +$row["total"];
        $fechaCrea=$row['fechaFactura'];
        $vFechaCrea=str_replace("-","&#8209;",substr($fechaCrea,0,10));

        $statusAttribs = " class=\"noApply middle rowstt".($esAdmin?" itooltip":"")."\" id=\"rowstt$dataIndex\"";
        $status = $row['status'];
        switch($status) {
            case "RespSinCX": $status="Resp.sin CR ni Exp"; break;
            case "RespSinContra":$status="Respaldado sin Contra-Recibo"; break;
            case "RespSinExp":$status="Respaldado sin Exportar"; break;
            case "ExpSinContra":$status="Exportado sin Contra-Recibo"; break;
        }
        if ($esAdmin) {
            $sonUltimos2=(count($data)>3 && ($dataIndex+2)>count($data));
            $statusTooltip="<span class=\"itip".($sonUltimos2?" toTop":" toRight")."\" id=\"rowitp$dataIndex\" >...</span>";
        } else $statusTooltip="";
?>
          <tr class="facturasAExportar" name="fila" idf="<?= $fId ?>">
            <td class="noApply middle"><?= $dataIndex ?></td>
            <td class="noApply middle" title="<?= $fechaCrea ?>"><?= $vFechaCrea ?></td>
            <td<?= $proveedorAttribs ?>><?= str_replace("-","&#8209;",$codigoProveedor) ?></td>
            <td class="noApply middle"<?= $tipFolio ?>><?= $folio ?></td>
            <td class="noApply middle">$<span class="currency"><?= number_format($total,2) ?></span></td>
            <td<?= $statusAttribs ?>><?= $status.$statusTooltip ?></td>
            <td<?= $xmlAttribs ?>>
<?php   if($xmlFileExists) { ?>
                <a href="<?= $xmlFilePath ?>" target="archivo"><img src="imagenes/icons/xml200.png" width="32" height="32" \></a>
<?php   } ?>
            </td>
            <td<?= $pdfAttribs ?>><div class="relative100">
<?php   if($pdfFileExists) { ?>
                <a href="<?= $pdfFilePath ?>" target="archivo"><img src="imagenes/icons/pdf200.png" width="32" height="32" \></a>
<?php   } ?>
            </div></td>
            <td class="seleccionManual"><input type="checkbox" id="invcheck<?= $fId ?>" name="invcheck[]" class="invcheck" value="<?= $fId ?>" checked></td>
          </tr><!-- END ROW -->
<?php
    }
}
if (!empty($errMessage)) {
    $errMessage = "<div style=\'float:none;display:block;clear:both;\'><br /><br />$errMessage<br /></div>";
    $title="ERROR";
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"overlayMessage('$errMessage', '$title');isLoaded('Error');let cch=ebyid('controlCheck');if(cch)cch.onchange=function(){let chlst=lbycn('invcheck');for(let i=0;i<chlst.length;i++)chlst[i].checked=cch.checked;};ekil(this);\">";
} else {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"isLoaded('OK');let cch=ebyid('controlCheck');if(cch)cch.onchange=function(){let chlst=lbycn('invcheck');for(let i=0;i<chlst.length;i++)chlst[i].checked=cch.checked;};ekil(this);\">";
}
require_once "configuracion/finalizacion.php";
