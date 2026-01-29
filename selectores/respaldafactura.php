<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Facturas.php";

$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas");
$esAvance = validaPerfil("Avance");
$esCompras = validaPerfil("Compras");
$esProveedor = validaPerfil("Proveedor");
$esCalifica = false && $esSistemas;

$modificaProc = modificacionValida("Procesar");

if (!isset($invObj)) $invObj = new Facturas();
$invObj->rows_per_page = 0;
if (isset($_REQUEST["pageno"])) $invObj->pageno = $_REQUEST["pageno"];
if (isset($_REQUEST["limit"]))  $invObj->rows_per_page = $_REQUEST["limit"];

if (!isset($where[0])) $where = "";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        if ($value = $_REQUEST[$pvalue]) {
            $where .= (isset($where[0])?" AND ":"").$pvalue . " LIKE '%" . $value . "%'";
        }
    }
}
clog2("POST:\n".arr2str($_POST));
//clog2("GPO:\n".arr2str($_SESSION['gpoCodigoOpt']));
$soloUnaEmpresa=false;
if (empty($_POST["grupo"])) $_POST["grupo"]="Todos";
$grupoPost = $_POST["grupo"];
if (is_array($grupoPost)) {
    $grupoCount = count($grupoPost);
    if ($grupoCount==1) {
        if ($grupoPost[0]!=="Todos") {
            //clog3("GRUPO ARRAY COUNT 1");
            $grupoOper="=";
            $grupoValue = "'".$grupoPost[0]."'";
            $soloUnaEmpresa=true;
        } else {
            //clog3("GRUPO ARRAY COUNT TODOS");
            if (!empty($_SESSION["                 "])) {
                if ($esCompras) {
                    $gpoKeys=array_keys($_SESSION["gpoCodigoOpt"]);
                    $gpoVals=array_values($_SESSION["gpoCodigoOpt"]);
                    //clog3("COMPRAS VE SOLO ".implode(",",$gpoVals));
                    $grupoOper=" IN ";
                    $grupoValue="('".implode("','",$gpoKeys)."')";
                    $soloUnaEmpresa=(isset($gpoKeys[0])&&!isset($gpoKeys[1]));
                }
            } else {
                $grupoOper="=";
                $grupoValue="'DESCONOCIDO'"; // No debería ocurrir
            }
        }
    } else if (!empty($_SESSION["gpoCodigoOpt"]) && $grupoCount==count($_SESSION["gpoCodigoOpt"])) {
        //clog3("GRUPO ARRAY SESSION COUNT ".$grupoCount);
        $grupoOper=null;
        $grupoValue=null;
    } else {
        //clog3("GRUPO ARRAY COUNT>1 ".$grupoCount);
        $grupoOper=" IN ";
        $grupoValue="('".implode("','",$grupoPost)."')";
    }
} else if ($grupoPost!=="Todos") {
    //clog3("GRUPO ".$grupoPost);
    $grupoOper="=";
    $grupoValue = "'$grupoPost'";
    $soloUnaEmpresa=true;
} //else clog3("GRUPO TODOS");
if (isset($grupoOper) && isset($grupoValue)) {
    $where .= (isset($where[0])?" AND ":"")."rfcGrupo$grupoOper$grupoValue";
}

$soloUnProveedor=false;
if (empty($_POST["proveedor"])) $_POST["proveedor"]="Todos";
$proveedorPost = $_POST["proveedor"];
if ($esProveedor) {
    $proveedorOper="=";
    $proveedorValue="'".getUser()->nombre."'";
    $soloUnProveedor=true;
} else if (empty($_SESSION["prvCodigoOpt"])) {
    $proveedorOper="=";
    $proveedorValue="'DESCONOCIDO'";
} else if (is_array($proveedorPost)) {
    $proveedorCount = count($proveedorPost);
    if (($proveedorCount==1 && $proveedorPost[0]!=="Todos")||
        ($proveedorCount==2 && $proveedorPost[1]==="Todos")) {
        //clog3("PROVEEDOR ARRAY COUNT 1");
        $proveedorOper="=";
        $proveedorValue="'".$proveedorPost[0]."'";
        $soloUnProveedor=true;
    } else if ($proveedorCount==2 && $proveedorPost[0]==="Todos") {
        //clog3("PROVEEDOR ARRAY COUNT 1 b");
        $proveedorOper="=";
        $proveedorValue="'".$proveedorPost[1]."'";
        $soloUnProveedor=true;
    } else if ($proveedorCount!=1 || $proveedorPost[0]!=="Todos") {
        //clog3("PROVEEDOR ARRAY COUNT>1 ".$proveedorCount);
        $proveedorOper=" IN ";
        $proveedorValue="('".implode("','",$proveedorPost)."')";
    }
} else if ($proveedorPost!=="Todos") {
    //clog3("PROVEEDOR ".$proveedorPost);
    $proveedorOper="=";
    $proveedorValue="'$proveedorPost'";
    $soloUnProveedor=true;
} //else clog3("PROVEEDOR TODOS");
if (isset($proveedorOper) && isset($proveedorValue)) {
    $where .= (isset($where[0])?" AND ":"")."codigoProveedor$proveedorOper$proveedorValue";
}
if (empty($errMessage)) {
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
    }
    $status = $_POST["status"]??"";
    if (isset($status[3]) && substr($status,0,4)==="Resp") {
        $where.=(isset($where[0])?" AND ":"")."statusn BETWEEN 8 AND 127";
    }
    switch($status) {
        case "RespaldadasRango":
            if (isset($rangoFechaWhere[0])) {
                $where.=(isset($where[0])?" AND ":"")."id IN (SELECT identif FROM proceso WHERE modulo='Factura' AND detalle like 'Facturas.Resp%' AND fecha$rangoFechaWhere)";
                $rangoFechaWhere="";
            }
            break;
        case "RespaldadasHoy":
            $rangoFechaWhere="";
            $where.=(isset($where[0])?" AND ":"")."id IN (SELECT identif FROM proceso WHERE modulo='Factura' AND detalle like 'Facturas.Resp%' AND fecha>=curdate())";
            break;
        case "RespaldadasAhora":
            $where.=(isset($where[0])?" AND ":"")."id IN (SELECT identif FROM proceso WHERE modulo='Factura' AND detalle like 'Facturas.Resp%' AND fecha>concat(curdate(),' ',hour(now())-1,':00:00'))";
            break;
        case "Respaldadas": break;
        case "Aceptadas":
            $where.=(isset($where[0])?" AND ":"")."statusn BETWEEN ".Facturas::STATUS_ACEPTADO." AND ".(Facturas::STATUS_RESPALDADO-1);
            //"status IN ('Aceptado','Contrarrecibo','ExpSinContra','Exportado')";
            break;
        case "RespPagos":
            $where.=(isset($where[0])?" AND ":"")."lower(left(tipoComprobante,1))='p'"; break;
        default:
            $where.=(isset($where[0])?" AND ":"")."statusn is not null and statusn BETWEEN ".Facturas::STATUS_ACEPTADO." AND ".(Facturas::STATUS_RECHAZADO-1);
            //"status NOT IN ('Temporal','Pendiente','Rechazado')";
    }
    
    if (isset($rangoFechaWhere[0])) {
        $where .= (isset($where[0])?" AND ":"")."fechaFactura$rangoFechaWhere";
    }
    
    // $text .= "Buscar=$_POST[Buscar], Command=$_POST[command]<br>\n";
    $prvRFCOpt       = $_SESSION['prvRFCOpt'];
    $prvRazSocOpt    = $_SESSION['prvRazSocOpt'];
    $gpoCodigoOpt    = $_SESSION['gpoCodigoOpt'];
    $gpoRazSocOpt    = $_SESSION['gpoRazSocOpt'];
    
    clog3("Where: $where");
    $invObj->clearOrder();
    if (isset($_POST["command"]) && substr($_POST["command"], 0, 8)==="BuscaPor") {
        $sortOrderType=substr($_POST["command"], 8);
        if (substr($sortOrderType,-3)==="Asc") {
            $sortOrderValue="asc";
            $sortOrderType=substr($sortOrderType,0,-3);
        } else if (substr($sortOrderType,-4)==="Desc") {
            $sortOrderValue="desc";
            $sortOrderType=substr($sortOrderType,0,-4);
        }
        switch($sortOrderType) {
            case "FechaF": $sortOrderType="fechaFactura"; break;
            case "Proveedor": $sortOrderType="codigoProveedor"; break;
            case "Empresa": $sortOrderType="ubicacion"; break; //"rfcGrupo"; break;
            case "Folio": $sortOrderType="folio+0"; break;
            case "Total": $sortOrderType="total"; break;
            case "Status": $sortOrderType="statusn"; break;
            default: $sortOrderType=null;
        }
        if (!empty($sortOrderType)) $invObj->addOrder($sortOrderType, $sortOrderValue);
    }
    $data = $invObj->getData($where);
    $dataIndex = 0;
    $dataCount = count($data);
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT']."/";
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT']."/";
    else $lookoutFilePath = "";
    //clog3("F#".count($data));
// TODO: guardar tiempo inicial, tambien variable de tiempo de ciclo para llevar la cuenta de ambos
    if ($soloUnProveedor) echo "<input type=\"hidden\" id=\"unEmisor\" value=\"1\"/>";
    if ($soloUnaEmpresa)  echo "<input type=\"hidden\" id=\"unReceptor\" value=\"1\"/>";
    $GLOBALS["ignoreTmpList"]=["data"];
    foreach ($data as $row) {
        $rfcGrupo = $row['rfcGrupo'];
        $codigoProveedor = $row["codigoProveedor"];
        if (!isset($gpoCodigoOpt[$rfcGrupo])) {
            $letter=substr($rfcGrupo, 0, 1);
            $sameStartKeys = array_filter(array_keys($gpoCodigoOpt), function($key) use ($letter) { return strpos($key, $letter) === 0; });
            doclog("INVALID gpoCodigoOpt","error",["rfcGrupo"=>$rfcGrupo, "sameStartKeys"=>$sameStartKeys]);
            $dataCount--;
            continue;
        }
        if (!isset($prvRFCOpt[$codigoProveedor])) {
            $letter=substr($codigoProveedor, 0, 1);
            $sameStartKeys = array_filter(array_keys($prvRFCOpt), function($key) use ($letter) { return strpos($key, $letter) === 0; });
            doclog("INVALID prvRFCOpt","error",["codigoProveedor"=>$codigoProveedor, "sameStartKeys"=>$sameStartKeys]);
            $dataCount--;
            continue;
        }
        if (!isset($prvRazSocOpt[$codigoProveedor])) {
            $letter=substr($codigoProveedor, 0, 1);
            $sameStartKeys = array_filter(array_keys($prvRazSocOpt), function($key) use ($letter) { return strpos($key, $letter) === 0; });
            doclog("INVALID prvRazSocOpt","error",["codigoProveedor"=>$codigoProveedor, "sameStartKeys"=>$sameStartKeys]);
            $dataCount--;
            continue;
        }
        $dataIndex++;
        $xmlClass = "centered";
        $pdfClass = "centered";
        $xmlFileExists = false;
        $pdfFileExists = false;
        $fId=$row["id"];
        $folio=$row["folio"];
        $tipFolio="";
        $rfcProveedor=$prvRFCOpt[$codigoProveedor];
        if (empty($folio)) {
            $fileSuffix=substr($row["uuid"],-10);
            $folio="[".$fileSuffix."]";
            $tipFolio=" title=\"Sin Folio\"";
        } else if (isset($folio[10])) {
            $fileSuffix=substr($folio,-10);
        } else {
            $fileSuffix=$folio;
            if (isset($serie[0])) {
                $fileSuffix2=$serie.$folio;
                if (isset($fileSuffix2[10])) {
                    $fileSuffix2=substr($fileSuffix2, -10);
                    if ($fileSuffix===$fileSuffix2) {
                        $fileSuffix2=substr($row["uuid"],-10);
                    }
                }
            }
        }
        $tipoComprobante=strtoupper($row["tipoComprobante"][0]);
        switch($tipoComprobante) {
            case "E":
                $fileSuffix = "NC_".$fileSuffix;
                if (isset($fileSuffix2[0])) $fileSuffix2="NC_".$fileSuffix2;
                break;
            case "P":
                $fileSuffix = "RP_".$fileSuffix;
                if (isset($fileSuffix2[0])) $fileSuffix2="RP_".$fileSuffix2;
                break;
        }
        $lenSfx=strlen($fileSuffix);
        if (isset($fileSuffix2[0])) $lenSfx2=strlen($fileSuffix2);
        $xmlName = $row["nombreInterno"];
        $pdfName = $row["nombreInternoPDF"];
        $yearCycle=$row["ciclo"];
        $ubicacion = $row["ubicacion"];
        $hasXML = isset($xmlName[0]);
        $hasPDF = isset($pdfName[0]);
        $isWrong = false;
        $canFixXML = false;
        $canFixPDF = false;
        if ($esAdmin) {
            $expectedXMLName = $rfcProveedor."_".$fileSuffix;
            $isExpectedXML = ($xmlName===$expectedXMLName);
            $expectedPDFName = $fileSuffix.$rfcProveedor;
            $isExpectedPDF = ($pdfName===$expectedPDFName);
            if (isset($fileSuffix2[0])) {
                $expectedXMLName2=$rfcProveedor."_".$fileSuffix2;
                if ($xmlName===$expectedXMLName2) {
                    $fileSuffix=$fileSuffix2;
                    $lenSfx=$lenSfx2;
                    $isExpectedXML=true;
                }
                $expectedPDFName2=$fileSuffix2.$rfcProveedor;
                $isExpectedPDF |= ($pdfName===$expectedPDFName2);
            } /* else {
                if ($hasXML && $xmlName!==$expectedXMLName) $canFixXML=true;
                if ($hasPDF && $pdfName!==$expectedPDFName) $canFixPDF=true;
            } */
        }
        $motivo="XML='$xmlName', PDF='$pdfName', FSFX='$fileSuffix', LENSFX=$lenSfx";
        if (isset($fileSuffix2[0]) && $fileSuffix!==$fileSuffix2) $motivo.=", FSFX2='$fileSuffix2', LENSFX2=$lenSfx2";
        $motivo.="\n";
        if ($hasXML) {
            if (substr($xmlName,-1-$lenSfx)==="_$fileSuffix") {
                if ($esCalifica) $xmlClass.=" right1BG";
            } else {
                if ($esCalifica) $xmlClass.=" wrong1BG";
                $motivo.="1- WRONG XML: '".substr($xmlName,-1-$lenSfx)."' no es igual a '_$fileSuffix'\n";
            }
            $xmlFilePath = $ubicacion . $xmlName . ".xml";
            $xmlFileExists = file_exists($lookoutFilePath.$xmlFilePath);
        } else {
            if ($esCalifica) $xmlClass.=" wrong1BG"; // excepto si status es Rechazada.
            $motivo.="2- WRONG XML: No tiene XML\n";
        }
        if ($hasPDF) {
            if (substr($pdfName,0,$lenSfx)===$fileSuffix) {
                if ($esCalifica) $pdfClass.=" right1BG";
            } else {
                if ($esCalifica) $pdfClass.=" wrong1BG";
                $isWrong=true;
                $canFix=true;
                $motivo.="3- WRONG PDF: '".substr($pdfName,0,$lenSfx)."' no es igual a '$fileSuffix'\n";
            }
            $pdfFilePath = $ubicacion . $pdfName . ".pdf";
            $pdfFileExists = file_exists($lookoutFilePath.$pdfFilePath);
            if ($pdfFileExists && $isWrong && ($tipoComprobante==="E" || $tipoComprobante==="P") && substr($pdfName,0,$lenSfx-3)===substr($fileSuffix,3)) {
                $isWrong=false;
                if ($esCalifica) $pdfClass=str_replace("wrong","right",$pdfClass);
                $motivo.="3.1- Aceptado por TC $tipoComprobante\n";
            }
            if ($isWrong) {
                $fixPDFName = $fileSuffix.$rfcProveedor;
                $fixPdfFilePath = $ubicacion.$fixPDFName.".pdf";
                //clog3("Comprobar si existe $lookoutFilePath$fixPdfFilePath con Proveedor $codigoProveedor ".$prvRazSocOpt[$codigoProveedor]);
                if (!file_exists($lookoutFilePath.$fixPdfFilePath)) {

                    $fixPDFName = substr($fileSuffix,3).$rfcProveedor;
                    $fixPdfFilePath = $ubicacion.$fixPDFName.".pdf";
                    //clog3("Comprobar si existe $lookoutFilePath$fixPdfFilePath");
                    if (!file_exists($lookoutFilePath.$fixPdfFilePath)) {
                        $isWrong=false;
                        //No se puede corregir en portal, hay q revisar para corregir de manera externa. Se mantiene el fondo rojo para resaltarlo sin incluir solucion directa.
                    }
                }
            }
        }
        $xmlAttribs = " class=\"$xmlClass\"";
        $pdfAttribs = " class=\"$pdfClass\"";

        if ($hasXML && !$xmlFileExists) $xmlAttribs.=" title=\"$xmlFilePath\"";
        if ($hasPDF && !$pdfFileExists) $pdfAttribs.=" title=\"$pdfFilePath\"";

        $fechaCrea = $row['fechaFactura'];
        $vFechaCrea = str_replace("-","&#8209;",substr($fechaCrea,0,10));

        //clog3("$dataIndex f$fId");
        $total = +$row["total"];
        if ($tipoComprobante==="P" && $total===0) {
            if (!empty($row["saldoReciboPago"])) $total= +$row["saldoReciboPago"];
        }
        $empresaAttribs  =" class=\"noApply middle".($soloUnaEmpresa?" hidden":"")."\"";
        $proveedorAttribs=" class=\"noApply middle".($soloUnProveedor?" hidden":"")."\"";
        if (isset($prvRazSocOpt[$codigoProveedor])) $proveedorAttribs.=" title='".$prvRazSocOpt[$codigoProveedor]."'";
        $esPago=false; $esNota=false; $esFactura=false;
        switch ($tipoComprobante) {
            case "I": $nombreComprobante="Ingreso"; $descCompro="la factura"; $esFactura=true; break; // default
            case "E": $nombreComprobante="Egreso"; $descCompro="la nota"; $esNota=true; break;
            case "T": $nombreComprobante="Traslado"; $descCompro="el traslado"; break;
            case "P": $nombreComprobante="Pago"; $descCompro="el recibo"; $esPago=true; break;
            default: $nombreComprobante="Desconocido"; $descCompro="el comprobante"; $tipoComprobante="?";
        }

        $statusAttribs = " class=\"noApply middle rowstt".($esAdmin?" itooltip":"")."\" id=\"rowstt$dataIndex\"";
        $statusn = $row["statusn"];
        //$status = $row['status'];
        $status=Facturas::statusnToRealStatus($statusn,$tipoComprobante,($modificaProc?2:($esProveedor?0:1)));
        if ($esPago && ($statusn&Facturas::STATUS_RESPALDADO)/*in_array($status,["RespSinCX","RespSinContra","RespSinExp"])*/) $status="Respaldado";
        else if($esNota) {
            if ($statusn&Facturas::STATUS_RESPALDADO) {
                if ($statusn&Facturas::STATUS_CONTRA_RECIBO) $status="Respaldado";
                else $status="RespSinContra";
            }
            //if ($status==="RespSinExp") $status="Respaldado";
            //if ($status==="RespSinCX") $status="RespSinContra";
        } else if ($modificaProc) {
            if (($statusn&Facturas::STATUS_RESPALDADO) && ($statusn&Facturas::STATUS_EXPORTADO) && !($statusn&Facturas::STATUS_CONTRA_RECIBO))
                $status="Respaldado sin Contra-Recibo";
            else if (($statusn&Facturas::STATUS_RESPALDADO) && !($statusn&Facturas::STATUS_EXPORTADO) && !($statusn&Facturas::STATUS_CONTRA_RECIBO))
                $status="Respaldado sin CR ni Exp"; // A && !C && !E = A && !(C||E)
            else if (($statusn&Facturas::STATUS_RESPALDADO) && !($statusn&Facturas::STATUS_EXPORTADO) && ($statusn&Facturas::STATUS_CONTRA_RECIBO))
                $status="Respaldado sin Exportar";
            else if (!($statusn&Facturas::STATUS_RESPALDADO) && ($statusn&Facturas::STATUS_EXPORTADO) && !($statusn&Facturas::STATUS_CONTRA_RECIBO))
                $status="Exportado sin Contra-Recibo";
        }
        /*switch($status) {
            case "RespSinCX": $status="Resp.sin CR ni Exp"; break;
            case "RespSinContra":$status="Respaldado sin Contra-Recibo"; break;
            case "RespSinExp":$status="Respaldado sin Exportar"; break;
            case "ExpSinContra":$status="Exportado sin Contra-Recibo"; break;
        }*/
        if ($esAdmin) {
            $sonUltimos2=($dataCount>3 && ($dataIndex+2)>$dataCount);
            $statusTooltip="<span class=\"itip".($sonUltimos2?" toTop":" toRight")."\" id=\"rowitp$dataIndex\" >...</span>";
        } else $statusTooltip="";
?>
          <tr class="facturasARespaldar" name="fila" idf="<?= $fId ?>">
            <td class="noApply middle"><?= $dataIndex ?></td>
            <td class="noApply middle" title="<?= $fechaCrea ?>"><?= $vFechaCrea ?></td>
            <td<?= $empresaAttribs ?>><?= ucfirst($gpoCodigoOpt[$rfcGrupo]) ?></td>
            <td<?= $proveedorAttribs ?>><?= str_replace("-","&#8209;",$codigoProveedor) ?></td>
            <td class="noApply middle shrinkCol" title="<?= $nombreComprobante ?>"><?= $tipoComprobante ?></td>
            <td class="noApply middle"<?= $tipFolio ?>><?= $folio ?></td>
            <td class="noApply middle">$<span class="currency"><?= number_format($total,2) ?></span></td>
            <td<?= $statusAttribs ?>><?= $status.$statusTooltip ?></td>
<?php
        if (isset($motivo[0])) echo "<!-- $motivo -->";
?>
            <td<?= $xmlAttribs ?>><div class="relative100">
<?php
        if($xmlFileExists) { ?>
                <a href="<?= $xmlFilePath ?>" target="archivo"><img src="imagenes/icons/xml200.png" width="32" height="32" \></a>
<?php       if ($canFixXML) { ?>
                <img src="imagenes/icons/repair.png" width="18" height="18" class="abs_ne btnFX bgdarkblue"\>
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="let x=this.previousElementSibling;let p=x.parentNode.parentNode;p.id='xmlCell<?= $fId ?>';x.title='Cambiar <?= $xmlName ?>.xml por <?= $expectedXMLName ?>.xml';x.onclick=function(){postService('consultas/Facturas.php',{actualiza:'fixxml',id:<?= $fId ?>,oldName:'<?= $xmlName ?>',newName:'<?= $expectedXMLName ?>',filePath:'<?= $ubicacion ?>',target:'xmlCell<?= $fId ?>',extension:'xml'},fixResult);};ekil(this);">
<?php       }
        } ?>
            </div></td>
            <td<?= $pdfAttribs ?>><div class="relative100">
<?php   if($pdfFileExists) { ?>
                <a href="<?= $pdfFilePath ?>" target="archivo"><img src="imagenes/icons/pdf200.png" width="32" height="32" \></a>
<?php   } else if (false && $esAdmin) {
            if ($xmlFileExists) { ?>
                <img src="imagenes/icons/invChk200.png" width="32" height="32" class="pointer" title="Anexar archivo PDF" onclick="generaFactura('<?= $xmlName ?>','<?= $yearCycle ?>','archivo');" \>
<?php       } 
          // ToDo: Reemplazar fixPDF por overlayConfirm preguntando por nuevo nombre y pasar ese nombre a la funcion fixPDF junto con el id de la factura. De lo contrario dentro de fixPDFhay que abrir overlayConfirm, pidiendo el nombre y luego realizar un postService pasando los dos valores a consulta/Facturas.php
?>
                <!-- img src="imagenes/icons/deleteIcon12.png" width="12" height="12" class="abs_ne pointer" title="Borrar Archivo"\ -->
<?php   } ?>
<?php   if ((($esAdmin||$esSistemas||$esAvance)&&$isWrong)||$canFixPDF) { $repairColor=($canFixPDF?"bgdarkblue":"bgwhite"); if ($canFixPDF) $fixPDFName=$expectedPDFName; ?>
                <img src="imagenes/icons/repair.png" width="18" height="18" class="abs_ne btnFX <?=$repairColor?>"\>
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="let x=this.previousElementSibling;let p=x.parentNode.parentNode;p.id='pdfCell<?= $fId ?>';x.title='Cambiar <?= $pdfName ?>.pdf por <?= $fixPDFName ?>.pdf';x.onclick=function(){postService('consultas/Facturas.php',{actualiza:'fixpdf',id:<?= $fId ?>,oldName:'<?= $pdfName ?>',newName:'<?= $fixPDFName ?>',filePath:'<?= $ubicacion ?>',target:'pdfCell<?= $fId ?>',extension:'pdf'},fixResult);};ekil(this);">
<?php   } ?>
            </div></td>
            <td class="seleccionManual"><input type="checkbox" id="invcheck<?= $fId ?>" name="invcheck[]" class="invcheck" value="<?= $fId ?>" checked></td>
          </tr>
<?php
// TODO: Agregar duracion de ciclo y duracion total
    }
}
if (!empty($errMessage)) {
    $errMessage = "<div style=\'float:none;display:block;clear:both;\'><br /><br />$errMessage<br /></div>";
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"overlayMessage('$errMessage','$title');isLoaded('Error');let cch=ebyid('controlCheck');if(cch)cch.onchange=function(){let chlst=lbycn('invcheck');for(let i=0;i<chlst.length;i++)chlst[i].checked=cch.checked;};ekil(this);\">";
} else echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"isLoaded('OK');let cch=ebyid('controlCheck');if(cch)cch.onchange=function(){let chlst=lbycn('invcheck');for(let i=0;i<chlst.length;i++)chlst[i].checked=cch.checked;};ekil(this);\">";
require_once "configuracion/finalizacion.php";
