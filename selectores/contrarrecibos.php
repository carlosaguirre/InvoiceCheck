<?php
$beginExecutionTime = microtime(true);
$maxExecutionTime = ini_get('max_execution_time');
require_once dirname(__DIR__)."/bootstrap.php";
clog2ini("selectores.contrarrecibos");
clog1seq(1);
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    clog1seq(-1);
    clog2end("selectores.contrarrecibos");
    die();
}
global $ctrObj, $ctfObj, $solObj;
if (!isset($ctrObj)) {
    require_once "clases/Contrarrecibos.php";
    $ctrObj = new Contrarrecibos();
}
$ctrObj->rows_per_page = 0;
if (isset($_REQUEST["pageno"])) $ctrObj->pageno = $_REQUEST["pageno"];
if (isset($_REQUEST["limit"]))  $ctrObj->rows_per_page = $_REQUEST["limit"];
$ctrObj->addOrder("aliasGrupo");
$ctrObj->addOrder("folio");

$where = "";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        if ($value = $_REQUEST[$pvalue]) {
            if (strlen($where)>0) $where .= " AND ";
            //if (strpos($pvalue, ".")===false) $pvalue="c.".$pvalue;
            $where .= $pvalue . " LIKE '%" . $value . "%'";
        }
    }
}
//$_esAdministrador, $_esSistemas, $_esSistemasX, $_esDesarrollo, $_esPruebas, $_esCompras, $_esComprasB, $_esProveedor
//    $_esCompras      = validaPerfil("Compras");
$esContrarrecibo= validaPerfil("Contrarrecibos");
$esRealizaPagos = validaPerfil("Realiza Pagos");
//$consultaCR = consultaValida("Contrarrecibo");
$autorizaCR = validaPerfil("Autoriza Contra Recibos") || $_esSistemas || in_array("Autoriza Contra Recibos",getUser()->perfiles);
$abreStatusSinAutorizar = $autorizaCR && in_array(getUser()->nombre, ["dmenasse"]);
//$esDesarrollo = in_array(getUser()->nombre, ["admin","arturo"]);
$esVistaAutorizaContra = true; // $esDesarrollo || $autorizaCR || validaPerfil("Requiere Contra Autorizado");
$esOrigenContraRecibos = $_esSistemas||in_array("Origen Contra Recibos",getUser()->perfiles); // original, impreso
$esEliminaContraRecibos = $_esSistemas||in_array("Elimina Contra Recibos", getUser()->perfiles);
$esRefoliaContraRecibos = $_esSistemas||in_array("Refolia Contra Recibos", getUser()->perfiles);
if ($autorizaCR) {
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $ugObj->rows_per_page=0;
    //$authCRGrpId=$ugObj->getIdGroupByNames(getUser(), "Autoriza Contra Recibos");
    $authCRAliases = $ugObj->getGroupAliases(getUser(), "Autoriza Contra Recibos");
}
clog2("POST: ".json_encode($_POST));
$gpoCodigoOpt    = $_SESSION['gpoCodigoOpt'];
$gpoRazSocOpt    = $_SESSION['gpoRazSocOpt'];
//clog2("GPO BY POST");
if (!empty($_POST["grupo"])) {
    if (strlen($where)>0) $where .= " AND ";
    $where .= "rfcGrupo='$_POST[grupo]'";
} else if ($_esProveedor) { //clog2("GPO BY Proveedor");
    $rfcGpoList = $ctrObj->getList("codigoProveedor", getUser()->nombre, "rfcGrupo");
    //clog2("GPO LIST = $rfcGpoList");
    if (strlen($where)>0) $where .= " AND ";
    $where = "rfcGrupo in ('".str_replace("|", "','", $rfcGpoList)."')";
} else if ($_esSistemas) {
} else { //if ($_esCompras) {
    if (empty($_SESSION['gpoRFCOpt'])) {
        if (!isset($ugObj)) {
            require_once "clases/Usuarios_grupo.php";
            $ugObj=new Usuarios_Grupo();
        }
        $ugObj->rows_per_page=0;
        $rfcEmpresas=$ugObj->getGroupRFC(getUser(), ["Autoriza Contra Recibos","Compras"], "vista");
    } else { //clog2("GPO BY Compras & SESSION");
        $rfcEmpresas = array_keys($_SESSION['gpoRFCOpt']);
    }
    if (!empty($rfcEmpresas)) {
        //$_esCompras=true;
        if (strlen($where)>0) $where .= " AND ";
        $where = "rfcGrupo in ('".implode("','",$rfcEmpresas)."')";
    }
}
if (isset($_POST["folio"][0])) {
    $folioValue = preg_replace('/[^\d,]+/', '', $_POST["folio"]);
    if (isset($where[0])) $where .= " AND ";
    if (strpos($folioValue,",")===false) $where .= "folio=$folioValue";
    else $where.="folio in ($folioValue)";
} else {
    if (!empty($_POST["fechaInicio"]) && !empty($_POST["fechaFin"])) {
        list($fDay, $fMon, $fYr) = sscanf($_POST["fechaInicio"], "%2d/%2d/%4d");
        $fechaIni = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
        list($fDay, $fMon, $fYr) = sscanf($_POST["fechaFin"], "%2d/%2d/%4d");
        $fechaFin = sprintf("%4d-%02d-%02d", $fYr, $fMon ,$fDay);
    } else {
        $dia = date('j');
        $mes = date('n');
        $anio = date('Y');
        $maxdia = date('t');
        $fechaIni = $anio."-".str_pad($mes,2,"0",STR_PAD_LEFT)."-"."01";
        $fechaFin = $anio."-".str_pad($mes,2,"0",STR_PAD_LEFT)."-".str_pad($dia,2,"0",STR_PAD_LEFT);
    }
    $status=$_POST["status"]??"";
    if (!isset($_POST["status"])&&$autorizaCR) {
        $status=$abreStatusSinAutorizar?"noauth":"expired";
    }
    switch($status) {
        case "noauth":
            if (strlen($where)>0) $where .= " AND ";
            $where.="numAutorizadas<numContraRegs";
            //$fields="c.*, count(1) num";
            break;
        case "auth":
            if (strlen($where)>0) $where .= " AND ";
            $where.="numAutorizadas>0";
            break;
        case "expired":
            if (strlen($where)>0) $where .= " AND ";
            $where.="fechaPago<current_date() and numAutorizadas<numContraRegs";
            $ctrObj->clearOrder();
            $ctrObj->addOrder("fechaPago");
            $ctrObj->addOrder("aliasGrupo");
            $ctrObj->addOrder("folio");
            break;
    }
    if (isset($fechaIni) && isset($fechaFin)) {
        if (strlen($where)>0) $where .= " AND ";
        $where .= "fechaRevision BETWEEN '$fechaIni 00:00:00' AND '$fechaFin 23:59:59'";
    }
}

$prvCodigoOpt = $_SESSION['prvCodigoOpt'];
$prvRFCOpt       = $_SESSION['prvRFCOpt'];
$prvRazSocOpt    = $_SESSION['prvRazSocOpt'];

if (!empty($_POST["proveedor"])) {
    if (strlen($where)>0) $where .= " AND ";
    $where .= "codigoProveedor='$_POST[proveedor]'";
} else if ($_esProveedor) {
    if (strlen($where)>0) $where .= " AND ";
    $where .= "codigoProveedor='".getUser()->nombre."'";
//} else { // toDo: reemplazar por inner join proveedores y status!='inactivo'
    //$where .= "codigoProveedor in ('".implode("','", array_values($prvCodigoOpt);)."')";
}

//clog2("Where: $where");
global $query;
$ctrData = $ctrObj->getData($where); //, 0, $fields, $extraFrom, $groupBy);
// getData ($where_str=false, $_num_rows_preset=0, $fieldNames="*", $extraFrom="", $group_str="")
clog2("QUERY: $query");
$regNum=count($ctrData);
$totSum=0;
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $ctrObj->pageno ?>" class="need2Bkup"/>
          <input type="hidden" name="limit" id="limit" value="<?= $ctrObj->rows_per_page ?>" class="need2Bkup"/>
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $ctrObj->lastpage ?>" class="need2Bkup"/>
<?php

//clog2("DATA:");
//clog2(arr2str($ctrData));
//clog2("LOG:");
//clog2($ctrObj->log);

$dataIndex = 0;
$authShow=false;
$incNum=0;
$cfChkIdx=0;
$logAuthList=[];
$puedeAutorizarEmpresas=false;
$GLOBALS["ignoreTmpList"]=["ctrData"];
$urlIgnoreList=["_POST","abreStatusSinAutorizar","authCell","authHeadCell","authShow","authSymbol","authSymbolB","authTrClass","autorizaCR","autorizables","autorizadas","bkgdImgNameD","canceladas","ccpCell","ccpHeadCell","cfChkIdx","cfCls","cfFecha","cfFolio","cfId","cfMon","cfMPago","cfPedido","cfRow","cfTotal","cfTC","crIdx","ctfData","ctfIdx","ctfRow","ctrData","ctrEmpresa","ctrFechaRev","ctrId","ctrNumAuth","ctrNumCF","ctrRow","ctrTotal","cuentaAutorizable","dataIndex","docImageBlock","docImgName","estaPagado","esContrarrecibo","esCopia","esEliminaContraRecibos","esRealizaPagos","esRefoliaContraRecibos","esSinAuth","esVistaAutorizaContra","fechaIni","fechaFin","fixClick","folioCR","fDay","fId","fMon","fYr","gpoAliasEmpresa","http_response_header","idx","incNum","invId","invStt","lastIdx","logAuthList","logCtfData","logCtrData","numInvalidRegs","pagadas","posPt","puedeAutorizarEmpresa","puedeAutorizarEmpresas","query_b","regNum","rfcEmpresas","rfcGpoList","rowCls","rowEvt","sideList","solData","status","tieneVistaAutorizaContra","totSum","urlIgnoreList","where"];
$maxTimeOutCR=10;
foreach ($ctrData as $ctrIdx => $ctrRow) {
    $dataIndex++;
    $ctrId=$ctrRow["id"];
    if (!isset($gpoCodigoOpt[$ctrRow['rfcGrupo']])) {
        doclog("INVALID gpoCodigoOpt","error",["row"=>$ctrRow]);
        continue;
    }
    if (!isset($prvRazSocOpt[$ctrRow['codigoProveedor']])) {
        doclog("INVALID prvRazSocOpt","error",["row"=>$ctrRow]);
        continue;
    }
    $gpoAliasEmpresa = ucfirst($gpoCodigoOpt[$ctrRow['rfcGrupo']]);
    $puedeAutorizarEmpresa = (isset($authCRAliases[0])&&in_array($gpoAliasEmpresa, $authCRAliases))||$_esSistemas;
    $folioCR = $gpoAliasEmpresa."-".$ctrRow['folio'];
    $crIdx="cr".str_pad("$dataIndex", 3,"0",STR_PAD_LEFT);
    $ctrNumAuth=$ctrRow["numAutorizadas"];
    $ctrNumCF=$ctrRow["numContraRegs"];
    if (!isset($ctfObj)) {
        require_once "clases/Contrafacturas.php";
        $ctfObj = new Contrafacturas();
    }
    $ctfObj->rows_per_page = 0;
    $ctfObj->clearOrder();
    $ctfObj->addOrder("fechaFactura");
    $ctfData = $ctfObj->getData("idContrarrecibo=$ctrId");
    $esSolPago=false;
    $estaPagado=false;
    $canceladas=[];
    $pagadas=[];
    $autorizadas=[];
    $autorizables=0;
    $esSinAuth=$_esProveedor; //||in_array($gpoAliasEmpresa, [/*"DANIEL","MELO",*/"FOAMYMEX"]);
    $tieneVistaAutorizaContra=$esVistaAutorizaContra&&!$esSinAuth;
    $authSymbol="";
    $authSymbolB="";
    $numInvalidRegs=0;
    $logCtrData=["ctrId"=>$ctrId, "folio"=>$folioCR, "ctrIdx"=>$crIdx, "ctfNum"=>$ctrNumCF, "ctfAuth"=>$ctrNumAuth];
    //$facturasAutorizables=0;
    $ffolio1=null; // folio de la primer factura
    foreach ($ctfData as $ctfIdx => $ctfRow) {
        $invId=$ctfRow["idFactura"];
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        }
        $invData=$invObj->getData("id=$invId",0,"folio,statusn");
        if (!isset($invData[0])) continue;
        $invData=$invData[0];
        $invStt=$invData["statusn"]??-1;
        if (!isset($ffolio1)) $ffolio1=$invData["folio"]??null;
        $cuentaAutorizable=false;
        if ($invStt>=Facturas::STATUS_RECHAZADO) {
            $numInvalidRegs++;
            $canceladas[]=$invId;
            if (!$esSinAuth && !isset($authSymbol[0])) {
                $authSymbol=" <img src='imagenes/icons/cancelled.png' class='size8 marSE4' title='CANCELADO'>";
                $authSymbolB="<img src='imagenes/icons/cancelled.png' class='size8 marSE4' title='CANCELADO'>";
            }
        } else if ($invStt>=Facturas::STATUS_PAGADO) {
            $numInvalidRegs++;
            $pagadas[]=$invId;
            if (!$esSinAuth && !isset($authSymbol[0])) {
                $authSymbol=" <span class='fontSmall inblock size8 greenHighlight marSE7' title='PAGADO'>$</span>";
                $authSymbolB=$authSymbol;
            }
        } else if ((+($ctfRow["autorizadaPor"][0]??0))>0 || !$tieneVistaAutorizaContra) {
            $autorizadas[]=$invId;
            if (!$esSinAuth) {
                $authSymbol=" <img src='imagenes/icons/checkGreen.png' class='size8 marSE7' title='AUTORIZADO PARCIAL' nAuth='".count($autorizadas)."' nAuthDB='".$ctrNumAuth."' nCntDB='".$ctrNumCF."'>";
                $authSymbolB=$authSymbol;
            }
        } else {
            $autorizables++;
            $cuentaAutorizable=true;
            //if ($ctfRow["tipoComprobante"]=="i") $facturasAutorizables++;
            if (!$esSinAuth) {
                $authSymbol=" <img src='imagenes/icons/crossRed.png' class='size8 marSE2' title='SIN AUTORIZAR' >";
                $authSymbolB="<img src='imagenes/icons/crossRed.png' class='size8 marSE2' title='SIN AUTORIZAR' symbol='B'>";
            }
        }
        if (!isset($solObj)) {
            require_once "clases/SolicitudPago.php";
            $solObj = new SolicitudPago();
        }
        $solData=$solObj->getData("idFactura=$invId",0,"folio,proceso");
        if (isset($solData[0]["folio"])) {
            $esSolPago=true;
            $estaPagado=($solData[0]["proceso"]>=4);
            if ($cuentaAutorizable) $autorizables--;
            break;
        }
    }
    /*if($facturasAutorizables==0&&$autorizables>0) {
        $autorizables=0;
        if(!$esSinAuth) {
            $authSymbol=" <img src='imagenes/icons/cancelled.png' class='size8 marSE2' title='IGNORADA' >";
            $authSymbolB="<img src='imagenes/icons/cancelled.png' class='size8 marSE2' title='IGNORADA' symbol='B'>";
        }
    }*/
    // Se ignoran los contra recibos dependiendo del status
    // Invalidas: Mostrar contra recibos que tengan al menos una factura pagada o rechazada
    // NO Invalidas: Mostrar contra recibos que tengan al menos una factura no pagada y no rechazada
    if ((isset($status[0])&&($status==="invalid")&&($numInvalidRegs==0)) || (isset($status[0])&&($status!=="invalid")&&($numInvalidRegs==$ctrNumCF))) {
        continue;
    }
    $totSum+=$ctrRow['total'];
    if ($esSolPago && isset($status[0])) continue; // $status==="auth"
    if ($esSolPago && !$_esProveedor) {
        $authSymbol=" <span class='fontSmall inblock size8 marSE7' title='CON SOLICITUD'>S</span>";
        $authSymbolB="";
    } else if (!isset($authSymbol[0])) $authSymbol=" <span class='fontSmall inblock size8 marSE5'>&nbsp;</span>";
    else if (!$esSinAuth && isset($autorizadas[0])) {
        if (isset($autorizadas[$ctrNumCF-1])) {
            $authSymbol=" <img src='imagenes/icons/dblCheckGreen.png' class='size8 marSE6' title='AUTORIZADO'>";
            $authSymbolB=$authSymbol;
        } else {
            $authSymbol=" <img src='imagenes/icons/checkGreen.png' class='size8 marSE7' title='AUTORIZADO PARCIAL' nAuth='".count($autorizadas)."' nAuthDB='".$ctrNumAuth."' nCntDB='".$ctrNumCF."'>";
            $authSymbolB=$authSymbol;
        }
    }
    $esCopia=$ctrRow["esCopia"];
    if ($_esProveedor || ($esSolPago ? (!$estaPagado && !$esRealizaPagos) : !isset($autorizadas[0]))) $esCopia="1";
//    if ($_esProveedor || ($esSolPago && !$estaPagado && !$esRealizaPagos) || (!$esSolPago&&!isset($autorizadas[0]))) $esCopia="1";

    $docImgName="crDoc32.png";
    $ctrQry="folio=$folioCR";
    $fixClick="";
    if (empty($esCopia)) { // ToDo: Integrar imagen authSymbol con docImgName sin bloquear accion del botón
        global $tokObj; if(!isset($tokObj)){ require_once "clases/Tokens.php"; $tokObj=new Tokens(); }
        $tokData=$tokObj->getData("refId=$ctrId and modulo='contra_original' and status='activo'",0,"id, token");
        if (!isset($tokData[0])) {
            $retTok = $tokObj->creaAccion($ctrId,[0],"contra_original",1,true);
            $token = $retTok[0]["contra_original"];
        } else {
            $token = $tokData[0]["token"];
        }
        $ctrQry.="&token=$token";
        $docImgName="cr2Ori32.png";
        $fixClick=" onclick=\"fixLink(this,'$ctrId');\"";
    } else if (!$_esProveedor)  {
        $docImgName="cr2Cop32.png";
    }
    $rowEvt=$_esProveedor?"":" ondblclick=\"clfix('$crIdx','hidden');clfix(this,'bbtmdblu');\"";
    $rowCls=isset($ctfData[0])&&$puedeAutorizarEmpresa&&$autorizables>0?"":" class=\"bbtmdblu\"";

    $docImageBlock="<a href=\"consultas/Contrarrecibos.php?{$ctrQry}\"{$fixClick} target=\"contrarrecibo\"><img src=\"imagenes/icons/{$docImgName}\" /></a>";
    if (isset($authSymbolB[0])) {
        $docImageBlock="<div class=\"inblock widfit relative\">{$docImageBlock}{$authSymbolB}</div>";
    } else $docImageBlock.=$authSymbol;

    $ctrFechaRev=$ctrRow["fechaRevision"]??"";
    if (isset($ctrFechaRev[10])) $ctrFechaRev=substr($ctrFechaRev, 0, 10);
    $ctrEmpresa=$ctrRow["aliasGrupo"]??"";
    $ctrTotal=$ctrRow["total"]??"0.00";
    $posPt=strpos($ctrTotal, ".");
    if ($posPt!==false && isset($ctrTotal[$posPt+3])) $ctrTotal=substr($ctrTotal, 0, $posPt+3);
    require_once "configuracion/conPro.php";
    $url1="{$_ConProHost}/externo/otros/invoice/contrarrecibos/contra_existe.aspx?&fecha={$ctrFechaRev}&empresa={$ctrEmpresa}&total={$ctrTotal}&factura={$folioCR}{$_ConProTest}";
    $url2="{$_ConProHost}/externo/otros/invoice/contrarrecibos/contrarrecibo.aspx?&fecha={$ctrFechaRev}&empresa={$ctrEmpresa}&total={$ctrTotal}&factura={$folioCR}{$_ConProTest}";

// http://globaltycloud.com.mx:4013/externo/otros/invoice/contrarecibos/contra_existe.aspx
// http://globaltycloud.com.mx:4013/externo/otros/invoice/contrarecibos/contrarrecibo.aspx
   
    if (isset($ffolio1)) {
        $url1.="&folio={$ffolio1}";
        $url2.="&folio={$ffolio1}";
    }
    $sideList=$GLOBALS["ignoreTmpList"];
    $GLOBALS["ignoreTmpList"]=$urlIgnoreList;
    $hasUrl=false;
    echo "<!-- URL1 = $url1 -->\n";
    echo "<!-- URL2 = $url2 -->\n";
    $options = [ "http" => [ "timeout" => 5 /* seconds */ ] ];
    $context = stream_context_create($options);
    if ($maxTimeOutCR>0) {
         try {
            //set_time_limit(5);
            //max_exe
            $startTime = microtime(true);
            //$elapsedTime = $startTime-$beginExecutionTime+6; // Tiempo transcurrido desde el principio más 5 segundos de timeout más 1 segundo de colchón
            //if ($elapsedTime>$maxExecutionTime) { // contar ocasiones que file_get_contents falla por timeout, si falla 10 veces se deja de consultar
            //    ;
            //}
            $_resUrl=@file_get_contents($url1, false, $context);
            $duration = microtime(true) - $startTime;
            if ($_resUrl!==false && isset($_resUrl[0])) {
                $resUrl=trim($_resUrl);
                if (isset($resUrl[0])) {
                    if ($resUrl[0]==="0") { $_resUrl="0"; $hasUrl=false; }
                    else $hasUrl=true;
                } else $hasUrl=false;
            }
            if (!$hasUrl) {
                if ($duration>=5) $maxTimeOutCR--;
                if ($_resUrl!=="0") doclog("NOConProCR","contrarrecibo",["idx"=>$ctrIdx, "url"=>$url1, "existe"=>"false", "duration"=>$duration, "result"=>($_resUrl===false?"false":$_resUrl)]);
            } // else doclog("ConProCR","contrarrecibo",["idx"=>$ctrIdx, "url"=>$url1, "existe"=>"true", "duration"=>$duration]);
        } catch (Exception $exc) {
            $duration = microtime(true) - $startTime;
            if ($duration>=5) $maxTimeOutCR--;
            doclog("ERROR","contrarrecibo",["idx"=>$ctrIdx, "url"=>$url1, "duration"=>$duration, "exception"=>getErrorData($exc)]);
        }
        if ($maxTimeOutCR<=0) doclog("TIMEOUT COUNT ENDS", ["idx"=>$ctrIdx, "url"=>$url1]);
    }
    $GLOBALS["ignoreTmpList"]=$sideList;
    if ($hasUrl) $docImageBlock.="<a target=\"crConPro\" href=\"$url2\"><img src=\"imagenes/icons/crConPro.png\" title=\"Contra Recibo ConPro\"/></a>";
    $url3="https://laisa.com.mx/DL/doc/contra_recibos.php?id={$folioCR}";

    clog2("CREXTERNO: $ctrRow[crExterno]");

    if ($ctrRow["crExterno"]=="1") {
        clog2("SI");
        $docImageBlock.="<a target=\"crDexaLai\" href=\"$url3\"><img src=\"imagenes/icons/crDexaLai.png\" title=\"Contra Recibo DexaLai\"/></a>";
    } else clog2("NO");
    
    $incNum++;
?>
          <tr<?=$rowEvt.$rowCls?>>
            <td class="noApply middle nowrap rowNum"><?= $incNum ?></td>
            <td class="noApply middle nowrap centered rowFoil"><?= $folioCR ?></td>
            <td class="noApply middle ellipsisCel maxWid250 colPrv" title="<?= $prvRazSocOpt[$ctrRow['codigoProveedor']] ?>" altval="<?= $ctrRow['codigoProveedor'] ?>"><?= $prvRazSocOpt[$ctrRow['codigoProveedor']] ?></td>
            <td class="noApply middle centered nowrap rowDate" title="<?= $ctrRow['fechaRevision'] ?>"><?= substr($ctrRow['fechaRevision'],0,10) ?></td>
            <!-- td class="noApply middle"><?= $ctrRow['fechaPago'] ?></td -->
            <td class="noApply middle righted padr10 rowTotal">$<?= number_format($ctrRow['total'],2) ?></td>
            <td class="noApply middle centered rowDoc"><?=$docImageBlock?>
<?php
    if ($esOrigenContraRecibos||$esEliminaContraRecibos) { ?>
            <img class="aslink" onclick="overlayCR2Config('<?=$folioCR?>');" src="imagenes/icons/cfgDoc32.png" />
<?php
    } ?>
            </td>
          </tr><!-- END ROW -->
<?php   
    if (isset($ctfData[0])&&!$_esProveedor) {
        if ($puedeAutorizarEmpresa) {
            //$authNum=intval($ctrRow["numAutorizadas"]??"0");
            //$chkNum=count($ctfData);
            //$chkMiss=$chkNum-$authNum;
            $authHeadCell="<th cell=\"Auth\" title=\"Autorizar Facturas\"><span>Auth</span>";
            if ($autorizables>1)
                $authHeadCell.="<input id=\"chkAll_{$crIdx}\" type=\"checkbox\" class=\"marT0i\" onclick=\"checkAll('$crIdx',this.checked);\" na=\"$autorizables\" title=\"Autorizar Todas\">";
            //else $authHeadCell.="";
            $authHeadCell.="</th>";
            if ($autorizables>0) {
                $authShow=true;
                $authTrClass="";
            } else {
                $authTrClass=" hidden";
            }
        } else {
            $authHeadCell="";
            $authTrClass=" hidden";
        }
        $ccpHeadCell="<th cell=\"Peek\"></th>";
        $logCtfData=[];
    ?>
          <tr id="<?=$crIdx?>" class="cfdetail bbtmdblu nohover<?=$authTrClass?>">
            <td class="bbtm1d nohover">&nbsp;</td>
            <td colspan="4" class="centered bbtm1d nohover"><table class="contrafacturas"><thead><tr><th cell="Folio">Folio</th><th cell="Fecha">Fecha</th><th cell="MP" title="Método de Pago">MP</th><th cell="TC" title="Tipo de Comprobante">TC</th><th cell="Pedido">Pedido</th><th cell="Total">Total</th><?=$ccpHeadCell.$authHeadCell?></tr></thead><tbody>
<?php   foreach ($ctfData as $idx => $cfRow) {
            $cfId=$cfRow["id"];
            $fId=$cfRow["idFactura"];
            $cfTotal=$cfRow["total"];
            $cfFecha=$cfRow["fechaFactura"];
            $cfFolio=$cfRow["folioFactura"];
            if (isset($cfFolio[10])) $cfFolioFix="...".substr($cfFolio, -10);
            $cfPedido=$cfRow["pedido"];
            $cfMPago=$cfRow["metodoDePago"];
            $cfTC=$cfRow["tipoComprobante"][0];
            $cfMon=$cfRow["moneda"]??"";
            $cfCls="ctfRow";
            if ($puedeAutorizarEmpresa) {
                $authCell="<td cell=\"Auth\">";
                $cfAuth=+$cfRow["autorizadaPor"]??0;
                $dtAuth=$cfRow["primeraImpresion"]??"";
                $authTip="";
                if (in_array($fId, $canceladas)) {
                    $ttl=" title='Cancelada'";
                    $authCell.="<img src='imagenes/icons/deleteIcon20.png' width='20px' height='20px' id='chk_$cfId'{$ttl}>";
                    $cfCls.=" stroke";
                } else if (in_array($fId, $pagadas)) {
                    $ttl=" title='Pagada'";
                    if ($cfAuth>0) {
                        $usr=getUserInfo($cfAuth);
                        if (isset($usr)) $ttl=" title='Pagada'";
                    }
                    $authCell.="<img src='imagenes/icons/dolarChkd.png' width='20px' height='20px' id='chk_$cfId'{$ttl}>";
                    $cfCls.=" stroke";
                } else if ($cfAuth>0 && !$esSolPago) {
                    $usr=getUserInfo($cfAuth);
                    if (isset($usr))
                        $ttl=" title='Autorizada por $usr[persona]'";
                    else $ttl="Autorizada por $cfAuth";
                    $authCell.="<img src='imagenes/icons/chkd24.png' width='20' height='20' id='chk_$cfId'{$ttl}".($_esAdministrador?" onclick='muestraRemoverAutorizacion(event);'":"").">"; // TODO: si el usuario actual es autorizador, al hacer un click mostrar botón: Quitar Autorizacion. Al presionar ese botón abrir dialogo: Esta seguro que quiere cancelar, poner renglon motivo y campo de texto pero no necesario. Al confirmar, quitar paloma y poner checkbox, en firmas agregar accion desautoriza, detalle: Retira autorizacion: <motivo>.
                    if (isset($dtAuth[0])) $authCell.=" ".substr($dtAuth, 0, 8);
                    //$cfCls.=" stroke";
                }/* else if ($cfTC!="i" && $facturasAutorizables==0) {
                    switch($cfTC) {
                        case "e": $tcn="Egreso"; break;
                        case "p": $tcn="Pago"; break;
                        case "t": $tcn="Traslado"; break;
                        case "c": $tcn="CartaPorte"; break;
                        default: $tcn="Comprobante";
                    }
                    $authCell.="<img src='imagenes/icons/crossRed.png' title='$tcn Ignorado' width='20' height='20' id='chk_$cfId'>";
                    $cfCls.=" stroke";
                }*/ else if (!$esSolPago) {
                    $logCtfData[$idx]=["ctfId"=>$cfId,"ctfChkIdx"=>$cfChkIdx,"invId"=>$fId,"invFl"=>$cfFolio,"invTC"=>$cfTC,"invMon"=>$cfMon];
                    $authCell.="<input type='checkbox' class='$crIdx cfchk' onclick='check(this);' fId='$fId' crId='$ctrId' crIdx='$crIdx' cfIdx='$cfChkIdx' id='chk_$cfId'>";
                    $cfChkIdx++;
                }
                $authCell.="</td>";
            } else $authCell="";
            $ccpCell="<td cell=\"Peek\">"; // "PREV"+(cfIdx-1)+":"+prvChkId+"|"
            $ccpCell.="<img src='imagenes/icons/viewer96.png' width='20px' height='20px' id='peek_$cfId' class='pointer wid20px hei20 lightOver' onclick='const ctft=ebyid(\"contrafooter\");if(ctft)ctft.crlog=\"INI{$cfChkIdx}:chk_{$cfId}\";verificaFactura($fId);'>";
            $ccpCell.="</td>";
            if (isset($cfMon[0])) $cfMon=" $cfMon"; ?>
            <tr id="ctf_<?=$cfId?>" total="<?=$cfTotal?>" class="<?=$cfCls?>"><td cell="Folio"><?= $cfFolioFix??$cfFolio ?></td><td cell="Fecha"><?=date("Y-m-d",strtotime($cfFecha))?></td><td cell="MP" class="shrinkCol"><?=$cfMPago?></td><td cell="TC" class="shrinkCol"><?=$cfTC?></td><td cell="Pedido"><?=$cfPedido?></td><td cell="Total" id="tot_<?=$cfId?>">$ <?=number_format((float)$cfTotal,2,'.',',').$cfMon ?></td><?=$ccpCell.$authCell?></tr>
<?php   }
        if ($puedeAutorizarEmpresa && !empty($logCtfData)) {
            $logCtrData["ctfList"]=$logCtfData;
            $logAuthList[]=$logCtrData;
        }
?>
            </tbody></table></td>
            <td class="bbtm1d nohover">&nbsp;</td>
          </tr>
<?php
    }
    if ($puedeAutorizarEmpresa) $puedeAutorizarEmpresas=true;
}
if ($puedeAutorizarEmpresas) doclog("LISTA PARA AUTORIZAR","authorized",["lista"=>$logAuthList]);
$regTxt="$incNum registro".($incNum==1?"":"s"); // reemplazar regNum por dataIndex, ahora por incNum
$totTxt = "$".number_format($totSum,2);
$authTxt = "";//$authShow?"ebyid('contraViewBtn').textContent='-';":"";
?>
          <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const regNum=ebyid('regNum');regNum.textContent='<?=$regTxt?>';const sumTot=ebyid('sumTot');sumTot.textContent='<?= $totTxt ?>';<?= $authTxt ?>adjustFooter();ekil(this);">
<?php
function getUserInfo($id) {
    global $usrObj,$userList;
    if (!isset($userList)) $userList=[];
    if (!isset($userList[$id])) {
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $usrData=$usrObj->getData("id=$id",0,"nombre,persona,email");
        if (!isset($usrData[0])) return null;
        $usrData=$usrData[0];
        $usrList[$id]=["nombre"=>$usrData["nombre"],"persona"=>$usrData["persona"],"email"=>$usrData["email"]];
    }
    return $usrList[$id];
}
clog1seq(-1);
clog2end("selectores.contrarrecibos");
require_once "configuracion/finalizacion.php";
