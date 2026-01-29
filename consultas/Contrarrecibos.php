<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Contrarrecibos.php";
$isAdmin=validaPerfil("Administrador");
$isSystem=$isAdmin||validaPerfil("Sistemas");
$isManager=validaPerfil("Gestor")||validaPerfil("Origen Contra Recibos");
$isPayer=validaPerfil("Realiza Pagos");
$isProvider=!hasUser()||validaPerfil("Proveedor");
$uid=hasUser()?"".getUser()->id:"0";
$uname=hasUser()?getUser()->nombre:"nouser";
$isDeveloper = in_array($uname, ["admin"]);
$isAuthorizer=validaPerfil("Autoriza Contra Recibos");
$seekAuth=true; //$isDeveloper||$isAuthorizer||validaPerfil("Requiere Contra Autorizado");
$ctrObj = new Contrarrecibos();
global $query;
if (isValueService()) getValueService($ctrObj);
//else if (isTestService()) getTestService($ctrObj);
else if (isCatalogService()) getCatalogService($ctrObj);
else if (isActionService()) getActionService($ctrObj);
else if (isset($_REQUEST["nextFolio"])) {
    $nextFolio=$ctrObj->getNextFolio($_REQUEST["nextFolio"]);
    if ($nextFolio) echo $nextFolio;
} else if (isset($_REQUEST["multiprint"])) {
    header("Content-language: es");
    date_default_timezone_set("Etc/GMT+6");
    $mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
    $idList=$_REQUEST["multiprint"]; // lista de id's
    if (empty($idList)) {
        doclog("Sin impresión de varios contra recibos. Lista vacía!","contrarrecibo",["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__]);
        echo "ERROR: No se indicaron contra recibos a imprimir";
        die();
    }
    $ctrData = $ctrObj->getData("id in (".implode(",", $idList).")");
    if (!isset($ctrData[0])) {
        doclog("Sin impresión de varios contra recibos. Resultado vacío!","contrarrecibo",["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__,"idList"=>$idList]);
        echo "ERROR: No se encontraron contra recibos válidos";
        die();
    }
    $formato = file("../templates/contraWrapper.html", FILE_IGNORE_NEW_LINES);
    $formato = str_replace("%BASE%", "{$_SERVER['HTTP_ORIGIN']}{$_SERVER['WEB_MD_PATH']}", $formato);
    $contenido = "";
    $iniciaVencimiento=strtotime("4 October 2020"); // A partir de esta fecha los contra recibos muestran fecha de vencimiento
    foreach ($ctrData as $ctrIdx => $ctrRow) {
        $aliasGrupo=urldecode($ctrRow["aliasGrupo"]);
        $seekAuthByCompany=$seekAuth; //&&!in_array($aliasGrupo, [/*"MELO","DANIEL",*/"FOAMYMEX"]);
        $folioCompleto=$aliasGrupo."-".$ctrRow["folio"];
        $razonSocial=urldecode(str_replace("%D1","&Ntilde;",$ctrRow["razonGrupo"]));
        $codigoProveedor=urldecode($ctrRow["codigoProveedor"]);
        $proveedor=urldecode(str_replace("%D1","&Ntilde;",$ctrRow["razonProveedor"]));
        if (!isset($prvObj)) {
            global $prvObj;
            if (!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj=new Proveedores();
            }
        }
        $prvData=$prvObj->getData("codigo='$codigoProveedor'", 1, "banco,cuenta");
        if (isset($prvData[0])) {
            $banco=urldecode($prvData[0]["banco"]??"");
            $clabe=urldecode($prvData[0]["cuenta"]??"");
        } else { $banco=""; $clabe=""; }
        $timeRevision=strtotime(urldecode($ctrRow["fechaRevision"]));
        $fecha=date("d/m/Y",$timeRevision);
        $diasCredito=+$ctrRow["credito"];
        $fechaPago=$ctrRow["fechaPago"];
        if (isset($fechaPago[0])) {
            $timeVencimiento=strtotime(urldecode($fechaPago));
            $fechaPago=date("dm",$timeVencimiento);
        } else $fechaPago=date("dm",$timeRevision);
        if (!$isProvider && $timeRevision>$iniciaVencimiento) { // $conVencimiento=true
            $diasCredito="$diasCredito";
        } else {
            $diasCredito="";
            if (!$isProvider) $fechaPago="";
        }
        $metpago0="";
        $metpago="";
        $htmlFacturas="";
        if (!isset($ctfObj)) {
            global $ctfObj;
            if (!isset($ctfObj)) {
                require_once "clases/Contrafacturas.php";
                $ctf=new Contrafacturas();
            }
            $ctfObj->rows_per_page=0;
            $ctfObj->clearOrder();
            $ctfObj->addOrder("fechaFactura");
        }
        $ctfData=$ctfObj->getData("idContrarrecibo=$ctrRow[id]");
        $realSum=0;
        $hasStrikeout=false;
        $totMon="";
        $isPaymReq=false;
        $firmas=[];
        $firQry="";
        $hasAuth=false;
        $creator="";
        foreach($ctfData as $ctfIdx=>$ctfRow) {
            if (!isset($invObj)) {
                global $invObj;
                if (!isset($invObj)) {
                    require_once "clases/Facturas.php";
                    $invObj=new Facturas();
                }
            }
            $invData=$invObj->getData("id=$ctfRow[idFactura]", 1, "statusn");
            $statusn=$invData[0]["statusn"]??null;
            if (!isset($solObj)) {
                global $solObj;
                if (!isset($solObj)) {
                    require_once "clases/SolicitudPago.php";
                    $solObj=new SolicitudPago();
                }
            }
            $solData=$solObj->getData("idFactura=$ctfRow[idFactura]", 1, "id,folio,proceso");
            $isPaymReq=isset($solData[0]["id"]);
            $isPaid=$isPaymReq&&$solData[0]["proceso"]>=4;
            $trace.="\n/* MULTI: isPaymReq=".($isPaymReq?"true":"false").", isPaid=".($isPaid?"true":"false")." */";
            $rowClass="counterInvoiceRow";
            $isAuth=!$seekAuthByCompany||((+$ctfRow["autorizadaPor"]??0)>0);
            if ($isAuth) $hasAuth=true;
            // toDo: Si ya está pagado hasAuth=true
            $preRow=$seekAuthByCompany?"<td class='noApply invisible'>&nbsp;</td>":"";
            $posRow=$seekAuthByCompany?"<td class='noApply invisible'>&nbsp;</td>":"";
            if (isset($statusn)&&$statusn<Facturas::STATUS_PAGADO&&!$isPaid&&($isAuth||$isPaymReq||$isProvider)) {
                $realSum+=+$ctfRow["total"];
            } else {
                $hasStrikeout=true;
                $rowClass.=" unauthorized";
                if ($seekAuthByCompany) {
                    $imgName="";
                    if (!isset($status)||$statusn>=Facturas::STATUS_RECHAZADO) $imgName="deleteIcon20";
                    else if ($status>=Facturas::STATUS_PAGADO) $imgName="dolarChkd";
                    else if (!$isAuth) $imgName="dots";
                    $imgTag=isset($imgName[0])?"<img src='imagenes/icons/{$imgName}.png' width='20px' height='20px'>":"%nbsp;";
                    $posRow="<td class='noApply'>$imgTag</td>";
                }
            }
            $moneda=$ctfRow["moneda"];
            $htmlFacturas.="<tr id=\"counterInvoiceRow_$ctfRow[id]\" total=\"$ctfRow[total]\" class=\"{$rowClass}\">{$preRow}<td>".date("Y-m-d", strtotime($ctfRow["fechaFactura"]))."</td><td>".date("Y-m-d", strtotime($ctfRow["fechaCaptura"]))."</td><td>$ctfRow[folioFactura]</td><td>$ctfRow[pedido]</td><td class=\"righted\">$ ".number_format((float)$ctfRow["total"], 2, '.', ',')."$mon</td>{$posRow}</tr>";
            if ($ctfRow["metodoDePago"]==="PUE" && $ctfRow["tipoComprobante"][0]==="i") {
                $metpago0="PUE";
                $metpago="&nbsp; &nbsp; F&nbsp;A&nbsp;C&nbsp;T&nbsp;U&nbsp;R&nbsp;A &nbsp; P&nbsp;U&nbsp;E";
            }
            if ($ctfIdx===0) {
                global $prcObj;
                if (!isset($prcObj)) {
                    require_once "clases/Proceso.php";
                    $prcObj = new Proceso();
                }
                $prcData=$prcObj->getData("modulo='Factura' and identif=$ctfRow[idFactura] and detalle like 'generacontra%'", 0, "usuario");
                if (!$isProvider)
                    $creator=$prcData[0]["usuario"]??"";
            }
        }
        if (isset($htmlFacturas[0])) {
            $preRow=$seekAuthByCompany?"<th>&nbsp;</th>":"";
            $posRow=$seekAuthByCompany?"<th>&nbsp;</th>":"";
            $htmlFacturas="<table class=\"contrarrecibo centered transparent\"><thead><tr>{$preRow}<th class=\"centered\">F.Emisión</th><th class=\"centered\">F.Captura</th><th class=\"centered\">Folio</th><th class=\"centered\">Pedido</th><th class=\"centered\">Total</th>{$posRow}</tr></thead><tbody>$htmlFacturas</tbody></table>";
        }
        if ($isPaymReq&&!$isProvider) { // Los contra-recibos por solicitud solo tienen una factura
            if (!isset($firObj)) {
                global $firObj;
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                $firObj->rows_per_page = 0;
            }
            $firmas=$firObj->getData("f.idReferencia=".$solData[0]["id"]." and f.accion in ('solicita','autoriza','contable','paga')", 0, "upper(f.accion) accion, u.persona nombre, date(f.fecha) fecha","f inner join usuarios u on f.idUsuario=u.id");
            $firQry=$query;
            $firCode="<p class='hg26 nomarblkend'><B>SOLICITUD</B>: ".$solData[0]["folio"]."</p><table class='lefted noApply bpad0 collapse all_space'>";
            foreach($firmas as $firIdx=>$firRow) {
                $firCode.="<tr><td class='wid100px noApply pad0'><B>$firma[accion]</B>:</td><td class='wid270px noApply'><div class='ellipsis wid270px'>$firma[nombre]</div></td><td class='wid70px noApply'><B>FECHA</B>:</td><td class='noApply'>$firma[fecha]</td></tr>";
            }
            $firCode.="</table>";
        } else $firCode="";
        if ($isProvider || ($isPaymReq && !$isPaid && !$isPayer) || (!$isPaymReq&&!$hasAuth)) {
            $ctrRow["esCopia"]="1";
            $trace.="\n/* MULTI: esCopia=1 */";
        }
        $ctrRow["esCopia"]="1"; // En multiprint no se muestran originales
        $total=number_format((float)urldecode($ctrRow["total"]),2,'.',',');
        if ($hasStrikeout) {
            $totalFmt="$ ".number_format($realSum, 2, '.', ',').$totMon;
            $totaloStr="<strike class='strikeout darkRedLabel'><span class='blacked'>$ {$total}{$totMon}</span></strike>";
            $totalStr="$totaloStr <span>{$totalFmt}</span>";
            $totaloStr.="<br><span>{$totalFmt}</span>";
        } else {
            $totalFmt="$ {$total}{$totMon}";
            $totaloStr="<span>{$totalFmt}</span>";
            $totalStr=$totaloStr;
        }
        $timestamp0="IMPR ".date("Y-m-d H:i.s");
        $timestamp="<span class='font8N'>{$timestamp0}</span>";
        $watermarkClass=$isProvider?"PROVEEDOR":(empty($ctrRow["esCopia"])?"ORIGINAL":"COPIA");
        $chunk = file("../templates/contraBlock.html", FILE_IGNORE_NEW_LINES);
        $chunk = str_replace("%alias%",$aliasGrupo,$chunk);
        $chunk = str_replace("%banco%",$banco,$chunk);
        $chunk = str_replace("%clabe%",$clabe,$chunk);
        $chunk = str_replace("%codigo%",$codigoProveedor,$chunk);
        $chunk = str_replace("%credito%",$diasCredito,$chunk);
        $chunk = str_replace("%facturas%",$htmlFacturas,$chunk);
        $chunk = str_replace("%fecha%",$fecha,$chunk);
        $chunk = str_replace("%fechaPago%",$fechaPago,$chunk);
        $chunk = str_replace("%folio%",$folioCompleto,$chunk);
        $chunk = str_replace("%metodopago%",$metpago,$chunk);
        $chunk = str_replace("%metodopago0%",$metpago0,$chunk);
        $chunk = str_replace("%proveedor%",$proveedor,$chunk);
        $chunk = str_replace("%razonSocial%",$razonSocial,$chunk);
        $chunk = str_replace("%timestamp%",$timestamp,$chunk);
        $chunk = str_replace("%timestamp0%",$timestamp0,$chunk);
        $chunk = str_replace("%creator%","<br><span class='font8N'>{$creator}</span>",$chunk);
        $chunk = str_replace("%total%",$totalStr,$chunk);
        $chunk = str_replace("%totalo%",$totaloStr,$chunk);
        $chunk = str_replace("%total0%",$totalFmt,$chunk);
        $chunk = str_replace("%watermark%",$watermarkClass,$chunk);
        //if (!$isProvider) $chunk = str_replace("%barcodescript%","<script src=\"scripts/JsBarcode.all.min.js?ver=1.0\"></script><script>function addBarCode(elem,txt){console.log('INI addBarCode1',elem,txt);const tgt=document.getElementById('barcode');if(tgt&&tgt.parentNode)tgt.parentNode.removeChild(tgt);if (elem&&elem.parentNode)elem.parentNode.insertBefore(Object.assign(document.createElement('svg'),{id:'barcode'}),elem);JsBarcode('#barcode', txt);}</script>",$chunk);
        $chunk = str_replace("%FIRMAS%",$firCode,$chunk);
        $contenido.=$chunk;
    }
    $formato = str_replace("%CONTENIDO%", "<!-- CONTENIDO -->".$contenido, $formato);
    doclog("Impresión de varios contra recibos","contrarrecibo",["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__,"idList"=>$idList]);
} else if (isset($_REQUEST["folio"])) {
    $asJson = isset($_REQUEST["asJson"]);
    $isInteractive = isset($_REQUEST["interactive"]); // Allow data manipulation in counter and related invoices, currently only interaction is to delete invoices or the counter completely, but this functionality is restricted to admin and system users.
    $isPaymReq=false; // si es solicitud de pago
    $isPaid=false; // si es solicitud de pago y está pagada
    $jsonArr = [];
    $crFolio=$_REQUEST["folio"];
    $crInput = explode("-",$crFolio);
    $trace="\n/* REQ[FOLIO]=$crFolio */";
    if(!$isAdmin) doclog("Requerimiento de Contra recibo ".($asJson?"JSON":"HTML").($isInteractive?" interactivo":"")." $crFolio","read");
    if (!empty($crInput) && count($crInput)==2) {
        require_once "clases/Contrafacturas.php";
        $ctrData = $ctrObj->getData("aliasGrupo='".$crInput[0]."' and folio='".$crInput[1]."'"); // .($isAdmin?"":" and status=1")
        $trace.="\n/* CTR-QUERY: $query */";
    }
    if (empty($ctrData)) {
        if ($asJson) {
            $jsonArr["result"] = "error";
            $jsonArr["errorMessage"]="Contrarrecibo $crFolio no v&aacute;lido";
        } else echo "Contrarrecibo $crFolio no v&aacute;lido.<br>";
    } else {
        $ctrData = $ctrData[0];
        $trace.="\n/* CTR-RESULT: ".json_encode($ctrData)." */";
        $canErase=$isInteractive&&($isSystem||$isManager); // Mostrar elementos de borrado solo en modo interactivo y solo para administradores y de sistemas
        $ctrId = urldecode($ctrData["id"]);
        $aliasGrupo = urldecode($ctrData["aliasGrupo"]);
        $seekAuthByCompany=$seekAuth; //&&!in_array($aliasGrupo, [/*"MELO","DANIEL",*/"FOAMYMEX"]);
        $razonSocial = urldecode(str_replace("%D1","&Ntilde;",$ctrData["razonGrupo"]));
        $codigoProveedor = urldecode($ctrData["codigoProveedor"]);
        $proveedor = urldecode(str_replace("%D1","&Ntilde;",$ctrData["razonProveedor"]));
        global $prvObj;
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj=new Proveedores();
        }
        $prvData=$prvObj->getData("codigo='$codigoProveedor'", 1, "banco,cuenta");
        if (isset($prvData[0])) {
            $banco = urldecode($prvData[0]["banco"]??"");
            $clabe = urldecode($prvData[0]["cuenta"]??"");
        }
        $timeRevision=strtotime(urldecode($ctrData["fechaRevision"]));
        $fecha = date("d/m/Y", $timeRevision);
        $diasCredito=+$ctrData["credito"];
        $fechaPago=$ctrData["fechaPago"];
        if (isset($fechaPago[0])) {
            $timeVencimiento=strtotime(urldecode($fechaPago));
            $fechaPago = date("dm", $timeVencimiento);
        } else $fechaPago=date("dm", $timeRevision);
        $iniciaVencimiento=strtotime("4 October 2020");
        $conVencimiento = $timeRevision>$iniciaVencimiento;
        $metpago0="";
        $metpago="";
        $eraseCol = $canErase?"<th id=\"eraseHeader\">Eliminar</th>":"";
        $htmlFacturas = "";
        $ctfObj = new Contrafacturas();
        $ctfObj->rows_per_page = 0;
        $ctfObj->clearOrder();
        $ctfObj->addOrder("fechaFactura");
        $ctfData = $ctfObj->getData("idContrarrecibo='{$ctrId}'");
        $retSum=+$ctfObj->getValue("idContrarrecibo", $ctrId, "sum(retencion)"); // select sum(retencion) from contrafacturas where idContrarrecibo=69008;
        if(!$isAdmin) doclog("Información obtenida de contra recibo $ctrId","read");
        $totMon="";
        $firmas=[];
        $firQry="";
        $hasStrikeout=false;
        $invIdLst=[];
        if (!empty($ctfData)) {
            $htmlFacturas .= "<tbody>";
            if (!isset($solObj)) {
                require_once "clases/SolicitudPago.php";
                $solObj = new SolicitudPago();
            }
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj = new Facturas();
            }
            $realSum=0;
            $authData=[];
            $hasAuth=false;
            $creator="";
            $lastAuth=0;
            $invInfo=[];
            $facturasAutorizables=0;
            $solId=null;
            foreach($ctfData as $cfIdx=>$cfRow) {
                $cfId=$cfRow["id"];
                $tc=$cfRow["tipoComprobante"];
                $mon=(isset($cfRow["moneda"][0])?" $cfRow[moneda]":"");
                $auth=((+$cfRow["autorizadaPor"]??0)>0);
                $invData=$invObj->getData("id=$cfRow[idFactura]", 1, "statusn");
                $stt=$invData[0]["statusn"]??null;
                $solData=$solObj->getData("idFactura=$cfRow[idFactura]", 1, "id,folio,proceso");
                if (isset($solData[0]["id"])) {
                    $isPaymReq=true;
                    $isPaid=$solData[0]["proceso"]>=4;
                    $solId=$solData[0]["id"];
                }
                $invInfo[$cfId]=["stt"=>$stt,"auth"=>$auth,"tc"=>$tc,"mon"=>$mon ];
                if ($tc=="i" && !$auth && !$isPaymReq) $facturasAutorizables++;

            }
            foreach($ctfData as $cfIdx=>$cfRow) {
                $eraseData = $canErase?"<td class=\"noApply counterCell\"><input type=\"checkbox\" class=\"counter_removal\" id=\"counter_del_$cfRow[id]\" checked></td>":"";
                $icInfo=$invInfo[$cfRow["id"]];
                $mon=$icInfo["mon"];
                if (isset($mon[0])) {
                    if (!isset($totMon[0])) $totMon=$mon;
                    else if ($totMon!==$mon) $totMon=" &nbsp;";
                }
                $statusn=$icInfo["stt"];
                $trace.="\n/* cfIdx=$cfIdx, isPaymReq=".($isPaymReq?"true":"false").", isPaid=".($isPaid?"true":"false")." */";
                $isAuth=!$seekAuthByCompany||$icInfo["auth"];
                if ($isAuth) {
                    $hasAuth=true;
                    if ($lastAuth<($cfRow["fechaAutorizada"]??0))
                        $lastAuth=$cfRow["fechaAutorizada"];
                }
                // toDo: Si ya está pagado hasAuth=true
                $preRow=$seekAuthByCompany?"<td class='noApply wid26px invisible'>&nbsp;</td>":"";
                $posRow=$seekAuthByCompany?"<td class='noApply invisible'>&nbsp;</td>":"";
                //$hasStrikeout=false;
                $strikeout="";
//echo "<!-- STATUSN:".$statusn.", STRIKEOUT:".($hasStrikeout?"Y":"N")." -->";
                if (isset($statusn)&&$statusn<Facturas::STATUS_PAGADO&&!$isPaid&&($isAuth||$isPaymReq||$isProvider)) {
                    $realSum+=+$cfRow["total"];
//echo "<!-- FIRST, STRIKEOUT:".($hasStrikeout?"Y":"N").", SUM=$realSum -->";
                } else {
                    $hasStrikeout=true;
                    $strikeout=" unauthorized";
                    if ($seekAuthByCompany) {
                        //$imgName="";
                        $strkTxt="";
                        if (!isset($statusn)||$statusn>=Facturas::STATUS_RECHAZADO) { // AUTHLEGEND
                            //$imgName="deleteIcon20";
                            $strkTxt="CANCELADO";
                        } else if ($statusn>=Facturas::STATUS_PAGADO) {
                            //$imgName="dolarChkd";
                            $strkTxt="PAGADO";
                        } else if (!$isAuth) {
                            //$imgName="dots";
                            $strkTxt="SIN AUTORIZAR";
                        }
                        $strkTxt.="<!-- seekAuth=$seekAuthByCompany, contrafactura:".json_encode($cfRow)." -->";
                        //$imgTag=isset($imgName[0])?"<img src='imagenes/icons/{$imgName}.png' width='20px' height='20px'>":"%nbsp;";
                        $imgTag=isset($strkTxt[0])?"<span class='hgtBtn'>$strkTxt</span>":"&nbsp;";
                        $posRow="<td class='noApply'>$imgTag</td>";
                    }
//echo "<!-- SECOND, STRIKEOUT:".($hasStrikeout?"Y":"N").", SUM=$realSum -->";
                }

                $htmlFacturas .= "<tr id=\"counterInvoiceRow_$cfRow[id]\" total=\"$cfRow[total]\" ret=\"$cfRow[retencion]\" class=\"counterInvoiceRow{$strikeout}\">{$preRow}<td>".date("Y-m-d", strtotime($cfRow["fechaFactura"]))."</td><td>".date("Y-m-d", strtotime($cfRow["fechaCaptura"]))."</td>";
                // ToDo_SOLICITUD: Detecta si existe solicitud
                $htmlFacturas .= "<td>$cfRow[folioFactura]</td><td>$cfRow[pedido]</td>";
                $totFmt=number_format((float)$cfRow["total"], 2, '.', ',');
                if ($retSum>0) {
                    if (+$cfRow["retencion"]==0) {
                        $htmlFacturas.="<td class=\"righted\">$ {$totFmt}$mon</td><td class=\"righted\">$ 0.00{$mon}</td>";
                    } else {
                        $totYRet=+$cfRow["total"]+$cfRow["retencion"];
                        $htmlFacturas.="<td class=\"righted\">$ ".number_format($totYRet, 2, '.', ',')."$mon</td><td class=\"righted\">$ ".number_format((float)$cfRow["retencion"], 2, '.', ',')."$mon</td>";
                    }
                }
                $htmlFacturas .= "<td class=\"righted\">$ {$totFmt}$mon</td>$eraseData{$posRow}</tr>";
                $invIdLst[]=$cfRow["idFactura"];
                if ($cfRow["metodoDePago"]==="PUE" && $cfRow["tipoComprobante"][0]==="i") {
                    $metpago0="PUE";
                    $metpago="&nbsp; &nbsp; F&nbsp;A&nbsp;C&nbsp;T&nbsp;U&nbsp;R&nbsp;A &nbsp; P&nbsp;U&nbsp;E";
                }
                if ($cfIdx===0) {
                    global $prcObj;
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj = new Proceso();
                    }
                    $prcData=$prcObj->getData("modulo='Factura' and identif=$cfRow[idFactura] and detalle like 'generacontra%'", 0, "usuario");
                    if (!$isProvider)
                        $creator=$prcData[0]["usuario"]??"";
                }
            }
            $htmlFacturas .= "</tbody>";
            if ($isPaymReq) {
                global $firObj;
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                $firObj->addOrder("f.fecha");
                $firmas=$firObj->getData(
                    "f.modulo='solpago' and f.idReferencia=$solId and f.accion in ('solicita','autoriza','contable','paga')",
                    1,
                    "upper(f.accion) accion,u.persona nombre,date(f.fecha) fecha",
                    "f inner join usuarios u on f.idUsuario=u.id");
                $firQry=$query;
            } else if ($seekAuthByCompany && isset($invIdLst[0])) {
                global $firObj;
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                $qry="f.modulo='contrarrecibo' and f.idReferencia={$ctrId} and f.accion in ('cancela','paga','autoriza','original')";
                $firObj->addOrder("f.fecha");
                $firmas=$firObj->getData($qry, 0,
                    "upper(f.accion) accion,u.persona nombre,date(f.fecha) fecha,f.motivo texto",
                    "f inner join usuarios u on f.idUsuario=u.id");
                $firQry=$query;
            }
            if(!$isAdmin) doclog("Lista de facturas generada ".implode(",",$invIdLst??[]),"read");
        }
        // ToDo_SOLICITUD: Mostrar copia si no es Finanzas o si es Proveedor
        if ($isProvider || ($isPaymReq && !$isPaid && !$isPayer) || (!$isPaymReq&&!$hasAuth)) {
            $ctrData["esCopia"]="1";
            $trace.="\n/* => esCopia=1 */";
        }
        
        $sCrData=$solObj->getData("s.idContrarrecibo=$ctrId", 0, "u.persona nombre, date(s.fechaInicio) fecha, s.folio","s inner join usuarios u on s.idUsuario=u.id");
        if (isset($sCrData[0]["nombre"][0])) {
            $nombreUsuario=$sCrData[0]["nombre"];
            if (($isDeveloper || $isPayer) && isset($_GET["st"]) && $_GET["st"]==="S3" && $ctrData["selloImpreso"]=="0") {
                if (!isset($nombreUsuario[10])) {
                    $nombre1=$nombreUsuario;
                    $nombre2="";
                } else {
                    $spIdx=strpos($nombreUsuario, " ");
                    if ($spIdx!==false && $spIdx<10) {
                        $nombre1=substr($nombreUsuario, 0, $spIdx);
                        $nombre2=substr($nombreUsuario, $spIdx+1);
                    } else {
                        $nombre1=substr($nombreUsuario, 0, 9)."-";
                        $nombre2=substr($nombreUsuario,9);
                    }
                }
                if (isset($nombre1[0])) $nombre1="<div style=\"position: absolute; left: 120px; width: 120px; top:77px; height: 20px; font-weight: bold;\">$nombre1</div>";
                if (isset($nombre2[0])) $nombre2="<div style=\"position: absolute; left: 20px; width: 220px; top:109px; height: 20px; font-weight: bold;\">$nombre2</div>";
                $payStamp="<div style=\"position: absolute; left: 100px; width: 140px; top: 45px; height: 20px; font-weight: bold;\">$fecha</div>{$nombre1}{$nombre2}<img src=\"imagenes/icons/sello1.png\" style=\"position: absolute;top: 0;left: 0;width: 242px;height: 154px;\">";
            }
            if (isset($firmas[0])) {
                $solFecha=$sCrData[0]["fecha"];
                $solSgnt=["accion"=>"SOLICITUD","nombre"=>$nombreUsuario,"fecha"=>$solFecha,"texto"=>$sCrData[0]["folio"]];
                $isSolIns=false;
                foreach ($firmas as $fidx => $frow) {
                    if($frow["fecha"]>$solFecha) {
                        array_splice($firmas, $fidx, 0, [$solSgnt]);
                        $isSolIns=true;
                        break;
                    }
                }
                if(!$isSolIns) $firmas[]=$solSgnt;
            }
        } //else $payStamp="<!-- not solpago.nombre -->";
        //$colorClass = (empty($ctrData["esCopia"])?"ORIGINAL":"COPIA").$metpago0;
        $colorClass = "transparent";
        $preRow=$seekAuthByCompany?"<th>&nbsp;</th>":"";
        $posRow=$seekAuthByCompany?"<th>&nbsp;</th>":"";
        $preHtmlFacturas = "<table class=\"contrarrecibo centered $colorClass\"><thead><tr>{$preRow}<th class=\"centered\">F.Emisión</th><th class=\"centered\">F.Captura</th>";
        //if ($conVencimiento) $preHtmlFacturas .= "<th class=\"centered\">Vence Pago</th>";
        $preHtmlFacturas .= "<th class=\"centered\">Folio</th><th class=\"centered\">Pedido</th><th class=\"centered\">Total</th>";
        if ($retSum>0) $preHtmlFacturas.="<th class=\"centered\">Retención</th><th class=\"centered\">Total a Pagar</th>";
        $htmlFacturas = $preHtmlFacturas."$eraseCol{$posRow}</tr></thead>$htmlFacturas</table>";
        if ($canErase) $htmlFacturas .= "<span id=\"eraseFooter\"><b>Eliminar Todas:</b> <input type=\"checkbox\" checked onclick=\"triggerAllChecksByClass(this.checked, 'counter_removal');\"></span>";
        
        $total = number_format((float)urldecode($ctrData["total"]), 2, '.', ',');
        if ($asJson) {
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
            }
            $prcQry="modulo='Contrarrecibo' and identif=$ctrId";
            if (isset($invIdLst[0])) {
                $invIdLstStr=implode(",", $invIdLst);
                $prcQry="($prcQry) or (modulo='Factura' and detalle like 'generacontra%' and identif in ($invIdLstStr))";
            }
            $prcObj->addOrder("p.id");
            $prcData=$prcObj->getData($prcQry, 0, "f.folio,f.uuid,p.status,p.detalle,date(p.fecha) fecha,u.persona nombre", "p inner join usuarios u on p.usuario=u.nombre left join facturas f on p.modulo='Factura' and p.identif=f.id");
            global $firObj;
            if (!isset($firObj)) {
                require_once "clases/Firmas.php";
                $firObj=new Firmas();
            }
            $qry="f.modulo='contrarrecibo' and f.idReferencia={$ctrId}";
            $firData=$firObj->getData($qry, 0,
                "upper(f.accion) accion,u.persona nombre,date(f.fecha) fecha,f.motivo texto",
                "f inner join usuarios u on f.idUsuario=u.id");
            $jsonArr["result"] = "success";
            $jsonArr["id"] = $ctrId;
            $jsonArr["folio"] = $crFolio;
            $jsonArr["alias"] = $aliasGrupo;
            $jsonArr["razonSocial"] = $razonSocial;
            $jsonArr["codigo"] = $codigoProveedor;
            $jsonArr["proveedor"] = $proveedor;
            if (isset($banco)) $jsonArr["banco"] = $banco;
            if (isset($clabe)) $jsonArr["clabe"] = $clabe;
            $jsonArr["fecha"] = $fecha;
            $jsonArr["metodopago"] = $metpago0;
            $jsonArr["facturas"] = $htmlFacturas;
            $jsonArr["total"] = $total.$totMon;
            $jsonArr["esCopia"] = !empty($ctrData["esCopia"]);
            $jsonArr["estaImpreso"] = $ctrData["estaImpreso"];
            $jsonArr["proceso"] = $prcData;
            $jsonArr["firmas"] = $firData;
        } else {
            if (empty($ctrData["esCopia"])) {
                $token = $_REQUEST["token"]??"";
                if (!isset($token[0])) {
                    $ctrData["esCopia"]="1";
                    $trace.="\n/* NO TOKEN REQUEST => esCopia=1 */";
                } else {
                    global $tokObj; if(!isset($tokObj)){ require_once "clases/Tokens.php"; $tokObj=new Tokens(); }
                    $tokData=$tokObj->getData("refId=$ctrId and modulo='contra_original' and status='activo'", 0, "id, token");
                    if (!isset($tokData[0])) {
                        $ctrData["esCopia"]="1";
                        $trace.="\n/* NO TOKEN DB => esCopia=1 */";
                    } else {
                        $tokData=$tokData[0];
                        if (!isset($tokData["token"][0])) {
                            $ctrData["esCopia"]="1";
                            $trace.="\n/* EMPTY TOKEN DB => esCopia=1 */";
                        } else if (strcmp($token, $tokData["token"])!==0) {
                            $ctrData["esCopia"]="1";
                            $trace.="\n/* INVALID TOKEN => esCopia=1 */";
                        }
                    }
                }
            }
            $formato = file("../templates/contrarrecibo.html", FILE_IGNORE_NEW_LINES);
            $formato = str_replace("%base%", "{$_SERVER['HTTP_ORIGIN']}{$_SERVER['WEB_MD_PATH']}", $formato);
            if(isset($systemTitle)) $formato = str_replace("%title%", $systemTitle, $formato);
            $formato = str_replace("%folio%", $crFolio, $formato);
            $formato = str_replace("%alias%", $aliasGrupo, $formato);
            $formato = str_replace("%razonSocial%", $razonSocial, $formato);
            $formato = str_replace("%codigo%", $codigoProveedor, $formato);
            $formato = str_replace("%proveedor%", $proveedor, $formato);
            if (isset($banco)) $formato = str_replace("%banco%", $banco, $formato);
            if (isset($clabe)) $formato = str_replace("%clabe%", $clabe, $formato);
            $formato = str_replace("%fecha%", $fecha, $formato);
            $formato = str_replace("%fecharv%", "REV ".$fecha, $formato);
            if ($conVencimiento) {
                if ($isProvider) $diasCredito="";
                $formato = str_replace("%credito%", $diasCredito, $formato);
                $formato = str_replace("%fechaPago%", $fechaPago, $formato);
            } else {
                $formato = str_replace("%credito%", "", $formato);
                $formato = str_replace("%fechaPago%", "", $formato);
            }
            $formato = str_replace("%metodopago0%", $metpago0, $formato);
            $formato = str_replace("%metodopago%", $metpago, $formato);
            $formato = str_replace("%facturas%", $htmlFacturas, $formato);
//echo "<!-- STRIKEOUT: ".($hasStrikeout?"1":"0")." -->";
            if ($hasStrikeout) {
                $totalFormatted="$ ".number_format($realSum, 2, '.', ',').$totMon;
                $totaloStr="<strike class='strikeout darkRedLabel'><span class='blacked'>$ {$total}{$totMon}</span></strike>";
                $totalStr="$totaloStr <span>{$totalFormatted}</span>";
                $totaloStr.="<br><span>{$totalFormatted}</span>";
            } else {
                $totalFormatted="$ {$total}{$totMon}";
                $totaloStr="<span>{$totalFormatted}</span>";
                $totalStr=$totaloStr;
            }
            $formato = str_replace("%total0%", $totalFormatted, $formato);
            $formato = str_replace("%total%", $totalStr, $formato);
            $formato = str_replace("%totalo%", $totaloStr, $formato);
            $formato = str_replace("%id%", $ctrId, $formato);
            $formato = str_replace("%uid%", $uid, $formato);
            header("Content-language: es");
            date_default_timezone_set("Etc/GMT+6");
            $mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
            $timestamp="IMPR ".date("Y-m-d H:i:s");
            $formato = str_replace("%timestamp0%", $timestamp, $formato);
            $formato = str_replace("%timestamp%", "<span class='font8N'>{$timestamp}</span>", $formato);
            $formato = str_replace("%creator%", "<br><span class='font8N'>{$creator}</span>", $formato);
            $watermarkClass = $isProvider?"PROVEEDOR":(empty($ctrData["esCopia"])?"ORIGINAL":"COPIA");
            $formato = str_replace("%watermark%",$watermarkClass,$formato); //  COPIA //  ORIGINAL
            $formato = str_replace("%paystamp%",$payStamp??"",$formato);
            if (!$isProvider) {
                $formato = str_replace("%barcodescript%","<script src=\"scripts/JsBarcode.code128.min.js?ver=v3.11.5\"></script><script>function setVal(txt){JsBarcode('#bcod',txt,{width:1,height:50,displayValue:false,background:'transparent'});}$trace</script>",$formato);
                $lastAuth=$lastAuth>0?"$lastAuth":"";
            } else $lastAuth = "";
            $formato = str_replace("%loadFunc%",$isAuthorizer?"if(typeof setVal==='function') setVal('{$crFolio}-{$lastAuth}');":"load('$_REQUEST[folio]','$lastAuth','$uname');",$formato);
            if (isset($firmas[0])&&!$isProvider) {
                if ($isPaymReq) {
                    if (isset($solData[0])) {// ($solId))
                        $firClass=""; $firCode="<p class='hg26 nomarblkend'><B>SOLICITUD</B>: ".$solData[0]["folio"]."</p>";
                    } else if (isset($sCrData[0])) {
                        $firClass=""; $firCode="<p class='hg26 nomarblkend'><B>SOLICITUD</B>: ".$sCrData[0]["folio"]."</p>";
                    } else {
                        $firClass=" all_space"; $firCode="";
                        doclog("ALERTA: TIENE FIRMAS SIN SOLICITUD","error",["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__,"firmas"=>$firmas,"query"=>$firQry??"","usuario"=>$uname]);
                    }
                } else {
                    $firClass=" all_space"; $firCode="";
                    // Puede tener firma sin solicitud
                    // También hay firma por contra recibo
                    //doclog("ALERTA: TIENE FIRMAS SIN SOLICITUD NI CONTRA RECIBO","error",["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__, "folio"=>isset($solData[0])?$solData[0]["folio"]:(isset($sCrData[0])?$sCrData[0]["folio"]:"SinFolio"),"firmas"=>$firmas,"query"=>$firQry??"","usuario"=>$uname]);
                }
                $firCode.="<table class='lefted noApply bpad0 firmasCtr collapse{$firClass}'>"; // <p class='hg26 nomarblkend'><B>CONTRARRECIBO</B>: </p>
                foreach ($firmas as $idx => $firma) {
                    $firAcc=$firma["accion"];
                    if ($firAcc==="authpago") $firAcc="autoriza";
                    $texto=$firma["texto"]??"";
                    if (isset($texto[100])) {
                        $cc=strpos($texto, ":");
                        if ($cc && $cc>0) $texto=substr($texto, 0,$cc);
                        else $texto=substr($texto, 0, 97)."...";
                    }
                    $firCode.="<tr><td class='wid100px noApply pad0 nowrap'><B>$firma[accion]</B>:</td><td class='wid270px noApply nowrap'><div class='ellipsis wid270px'>$firma[nombre]</div></td><td class='wid70px noApply'><B>FECHA</B>:</td><td class='noApply nowrap'>$firma[fecha]</td></tr>";
                    if (isset($texto[0])) $firCode.="<tr><td class='noApply pad0'></td><td class='noApply' colspan='3'>$texto</td></tr>";
                }
                $firCode.="</table>";
                $formato = str_replace("%FIRMAS%",$firCode,$formato);
            } //else $formato = str_replace("%FIRMAS%","",$formato); // SIN FIRMAS
            // ToDo_SOLICITUD: cambiar a copia si no es proveedor y no tiene solicitud o si el usuario si es de finanzas
            doclog("TIPO DE DOCUMENTO","contrarrecibo",["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__,"watermark"=>$watermarkClass,"esCopia"=>$ctrData["esCopia"]??"0","usuario"=>$uname,"id"=>$ctrId]);
            // No es Proveedor
            //             y (no es Solicitud o es Finanzas)
            //                                             y tiene alguna factura autorizada
            //                                                        y no es autorizador
            // (!$isProvider && (!$isPaymReq || $isPaid || $isPayer) && ($isPaymReq||$hasAuth)) 
            $esOriginal=($watermarkClass==="ORIGINAL");
            if (!$isProvider && (!$isPaymReq || $isPaid || $isPayer) && ($isPaymReq||$hasAuth) && !$isAuthorizer) {
                $fieldarray=["id"=>$ctrId];
                if ($esOriginal) {
                    $trace.="\n/* in timed ORI 2 COP */";
                    $fieldarray["esCopia"]="1";
                    $fieldarray["originalTimeout"]=new DBExpression("(now() + INTERVAL 5 MINUTE)");
                }
                if (isset($payStamp[0])) $fieldarray["selloImpreso"]="1";
                $fieldkeys=array_keys($fieldarray);
                if (isset($fieldkeys[1]) && $ctrObj->saveRecord($fieldarray)) {
                    global $prcObj;
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj = new Proceso();
                    }
                    $prcMsg="$watermarkClass";
                    if (isset($fieldarray["selloImpreso"])) $prcMsg.=" CON SELLO";
                    $prcObj->cambioContrarrecibo($ctrId, "Consulta", $uname, $prcMsg);
                }
            }
            global $firObj;
            if (!isset($firObj)) {
                require_once "clases/Firmas.php";
                $firObj=new Firmas();
            }
            $firObj->saveRecord(["idUsuario"=>$uid,"modulo"=>"contrarrecibo","idReferencia"=>$ctrId,"accion"=>strtolower($watermarkClass),"motivo"=>"consulta ".substr($timestamp,16)]);
//                $formato = str_replace(["&aacute;","&Aacute;","&eacute;","&Eacute;","&iacute;","&Iacute;","&oacute;","&Oacute;","&uacute;","&Uacute;","&ntilde;","&Ntilde;"],["a","A","e","E","i","I","o","O","u","U","n","N"],$formato);
            $formato = array_filter($formato, function($val) {
                return preg_match("/%\w+%/", $val)?false:true;
            });

            echo implode('
',$formato);
            if(!$isAdmin) doclog("Publicación de Contra recibo HTML desplegada","read");
        }
    }
    if ($asJson && !empty($jsonArr)) {
        header('Content-Type: application/json');
        header("Content-language: es");
        echo json_encode($jsonArr);
        if(!$isAdmin) doclog("Publicación de Contra recibo JSON desplegada","read");
    }
} else {
    header('HTTP/1.0 404 Not Found');
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isActionService() {
    return isset($_POST["action"]);
}
function getActionService($ctrObj) {
    global $uname;
    $guid=hasUser()?"".getUser()->id:"0";
    switch($_POST["action"]) {
        case "isCopy":
            $ctrId=$_POST["id"]??"";
            if (isset($ctrId[0]))
                $ctrData=$ctrObj->getData("id=$ctrId", 1, "esCopia");
                echo $ctrData[0]["esCopia"]??"";
            break;
        case "isPrinted":
            $uid=$_POST["uid"]??"0";
            $cid=$_POST["id"]??"";
            $wmk=$_POST["wmk"]??"";
            $mdp=$_POST["mdp"]??"";
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
            }
            if(empty($cid)) {
                echo "UNSPECIFIED ID";
                doclog("Contra recibo ID no especificado","print",["post"=>$_POST, "user"=>["id"=>$guid,"nombre"=>$uname], "ip"=>getIP()]);
            } else if (empty($wmk)) {
                echo "INVALID MARK";
                doclog("Marca de agua no especificada","print",["post"=>$_POST, "user"=>["id"=>$guid,"nombre"=>$uname], "ip"=>getIP()]);
            } else {
                $fieldarray=["id"=>$cid,"estaImpreso"=>new DBExpression("estaImpreso+1")];
                $esOriginal=($wmk==="ORIGINAL");
                if ($esOriginal) $fieldarray["esCopia"]=1;
                global $query;
                $qrys=[];
                if ($ctrObj->saveRecord($fieldarray)) {
                    $qrys["ctr"]=$query;
                    $det="Impresión $wmk";
                    $prcObj->cambioContrarrecibo($cid, "Accion", $uname, $det);
                    $qrys["prc"]=$query;
                    if ($esOriginal) {
                        global $firObj;
                        if (!isset($firObj)) {
                            require_once "clases/Firmas.php";
                            $firObj=new Firmas();
                        }
                        $firObj->saveRecord(["idUsuario"=>$guid,"modulo"=>"contrarrecibo","idReferencia"=>$cid,"accion"=>"original","motivo"=>"impresión"]);
                        $qrys["fir"]=$query;
                    }
                    doclog("Contra recibo Impreso","print",["post"=>$_POST, "user"=>["id"=>$guid,"nombre"=>$uname], "detalle"=>$det, "queries"=>$qrys, "ip"=>getIP()]);
                    echo "REGISTERED $cid";
                } else {
                    $qrys["ctr"]=$query;
                    doclog("Error: Contra recibo impreso no guardado","print",["post"=>$_POST, "user"=>["id"=>$guid,"nombre"=>$uname], "detalle"=>$det, "queries"=>$qrys, "errno"=>DBi::$errno, "error"=>DBi::$error, "ip"=>getIP()]);
                    echo "DBERROR";
                }
            }
            break;
        case "fixCopy":
            if(!isset($_POST["id"][0])) echo "UNSPECIFIED ID";
            else if (!isset($_POST["value"][0])) echo "UNSPECIFIED VALUE";
            else {
                $id=$_POST["id"];
                $val=$_POST["value"];
                switch($val) {
                    case "COPIA":
                        $fieldarray=["id"=>$id,"esCopia"=>1];
                        break;
                    case "ORIGINAL":
                        $fieldarray=["id"=>$id,"esCopia"=>NULL];
                        break;
                    default:
                        echo "INVALID VALUE";
                        break 2;
                }
                if (isset($fieldarray)) {
                    if ($ctrObj->saveRecord($fieldarray)) {
                        global $prcObj;
                        if (!isset($prcObj)) {
                            require_once "clases/Proceso.php";
                            $prcObj = new Proceso();
                        }
                        $det="Cambia a $val";
                        $prcObj->cambioContrarrecibo($id, "FIXCOPY", $uname, $det);
                        echo "SUCCESSFUL $val";
                    } else echo "ERROR";
                } else echo "WRONG DATA";
            }
            break;
        case "resetPrintCount":
            if(empty($_POST["id"])) echo "UNSPECIFIED ID";
            else {
                $fieldarray=["id"=>$_POST["id"], "estaImpreso"=>0];
                if ($ctrObj->saveRecord($fieldarray)) echo "SUCCESSFUL";
                else echo "ERROR";
            }
            break;
        default: echo "INVALID ACTION";
    }
}
