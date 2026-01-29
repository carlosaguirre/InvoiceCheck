<?php
global $solObj, $prvObj;
if (!isset($solId)) {
    //if(!isset($_REQUEST["SOLID"])) die();
    $solId=$_REQUEST["SOLID"]??"";
}
if (!isset($solObj)) {
    require_once "clases/SolicitudPago.php";
    $solObj=new SolicitudPago();
}
if (!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj=new Proveedores();
}
$hostname=getBaseURL();
$esDesarrollo = hasUser() && getUser()->nombre==="admin"; //in_array(getUser()->nombre, ["admin","test","test1","test2","test3"]);
if (!isset($isInteractive)) {
//    doclog("No existe variable de no correo","error");
    $isInteractive=false;
}
$solData=$solObj->getData("id=$solId");
if (isset($solData[0])) {
    $solData=$solData[0];
    //echo "<!-- SOLICITUD: ".json_encode($solData)." -->";
    $solFolio=$solData["folio"];
    if (isset($solData["idFactura"][0])) {
        global $invObj;
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        }
        $invData=$invObj->getData("id=$solData[idFactura]");
        // id, pedido, codigoProveedor, rfcGrupo, fechaFactura, fechaCaptura, fechaAprobacion, fechaVencimiento, uuid, serie, folio, noCertificado, formaDePago, metodoDePago, importeDescuento, impuestoTraslado, impuestoRetenido, subtotal, total, tipoComprobante, tipoCambio, moneda, tasaIva, nombreOriginal, ciclo, nombreInterno, nombreInternoPDF, ubicacion, tieneSello, tieneOrden, mensajeCFDI, estadoCFDI, cancelableCFDI, canceladoCFDI, solicitaCFDI, consultaCFDI, numConsultasCFDI, fechaPago, totalPago, referenciaPago, idReciboPago, fechaReciboPago, saldoReciboPago, version, status, statusn, modifiedTime
        if (isset($invData[0])) {
            $invData=$invData[0];
        }
        $cp=$invData["codigoProveedor"]??"";
        if (isset($cp[0])) {
            $prvData=$prvObj->getData("codigo='$cp'");
            if (isset($prvData[0])) {
                $prvData=$prvData[0];
            }
        }
        global $cptObj;
        if (!isset($cptObj)) {
            require_once "clases/Conceptos.php";
            $cptObj=new Conceptos();
            $cptObj->rows_per_page=0;
        }
        $cptData=$cptObj->getData("idFactura=$solData[idFactura]");
        $moneda=$invData["moneda"]??"MXN";
    }
    if (isset($solData["idOrden"][0])) {
        global $ordObj;
        if (!isset($ordObj)) {
            require_once "clases/OrdenesCompra.php";
            $ordObj=new OrdenesCompra();
        }
        $ordData=$ordObj->getData("id=$solData[idOrden]"); // id,folio,idEmpresa,idProveedor,fecha,rutaArchivo,nombreArchivo,importe,status,modifiedTime
        if (isset($ordData[0])) {
            $ordData=$ordData[0];
        }
        if (!isset($prvData["id"]) && isset($ordData["idProveedor"][0])) {
            $prvData=$prvObj->getData("id=$ordData[idProveedor]");
            if (isset($prvData[0])) {
                $prvData=$prvData[0];
            }
        }
        $moneda=$ordData["moneda"]??"MXN";
    }
    if (isset($solData["idContrarrecibo"][0])) {
        global $ctrObj;
        if (!isset($ctrObj)) {
            require_once "clases/Contrarrecibos.php";
            $ctrObj=new Contrarrecibos();
        }
        $ctrData=$ctrObj->getData("id=$solData[idContrarrecibo]");
        if (isset($ctrData[0])) {
            $ctrData=$ctrData[0];
        }
        if (!isset($prvData["id"]) && isset($ctrData["codigoProveedor"][0])) {
            $prvData=$prvObj->getData("codigo='$ctrData[codigoProveedor]'");
            if (isset($prvData[0])) {
                $prvData=$prvData[0];
            }
        }
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
            $ctfObj->rows_per_page=0;
        }
        $ctfData=$ctfObj->getData("idContrarrecibo=$solData[idContrarrecibo]");
        if (isset($ctfData[0])) {
            $moneda=$ctfData[0]["moneda"]??"MXN";
        }
    }
    if (!isset($moneda[0])) $moneda="MXN";

    if (isset($solData["idEmpresa"][0])) {
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $gpoData=$gpoObj->getData("id=$solData[idEmpresa]");
        if (isset($gpoData[0])) {
            $gpoData=$gpoData[0];
            //echo "<!-- EMPRESA: ".json_encode($gpoData)." -->";
        }
    }
    if (isset($solData["idUsuario"][0])) {
        global $usrObj;
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $usrData=$usrObj->getData("id=$solData[idUsuario]");
        if (isset($usrData[0])) $usrData=$usrData[0];
    }
    if (isset($solData["idAutoriza"][0])) {
        global $usrObj;
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $autData=$usrObj->getData("id=$solData[idAutoriza]");
        if (isset($autData[0])) $autData=$autData[0];
    }
    $esAdmin = validaPerfil("Administrador");
    $esSistemas = $esAdmin||validaPerfil("Sistemas");
    $esRealizaPago = validaPerfil("Realiza Pagos")||$esSistemas;
    $puedeVerMotivo=validaPerfil(["Gestiona Pagos","Autoriza Pagos"])||$esSistemas;
    if (isset($solData["status"][0])) {
        $sttList=SolicitudPago::getStatusList($solData["status"],true);
    }
    $solStatus=+$solData["status"];
    $solStatusFlags=getBinFlags($solStatus);
    $solProceso=+$solData["proceso"];
    clog2("STATUS: $solStatus");
    clog2("FLAGS: ".implode(",", $solStatusFlags));
    clog2("PROCS: $solProceso");

//function getBinFlags($num, $inverse=false) {
    $binStr=decbin($solStatus);
    clog2("BIN STATUS: $binStr");
    $flags=[]; $inverse=[];
    $binSz=strlen($binStr);
    for ($i=0; $i<$binSz; $i++) {
        clog2("BINLOOP: ".($i+1).". BIN INDEX: ".($binSz-$i-1).". BIN VALUE: ".$binStr[$binSz-$i-1]);
        if ($binStr[$binSz-$i-1]==="1") {
            //if ($inverse)
                //array_unshift($flags, 2**$i);
                array_unshift($inverse, 2**$i);
            //else
                $flags[]=2**$i;
        }
    }
    echo "<!-- BIN FLAGS: \n";
    print_r($flags);
    echo "\n     BIN INVERSE: \n";
    print_r($inverse);
    echo "\n -->\n";
    //return $flags;
//}

    if (hasUser()) clog2("NOMBRE: ".getUser()->nombre.($esDesarrollo?"(DESARROLLO)":""));
    $esParaPago=!in_array(SolicitudPago::STATUS_PAGADA, $solStatusFlags)&&!in_array(SolicitudPago::STATUS_CANCELADA, $solStatusFlags)&&$solProceso===SolicitudPago::PROCESO_CONTABLE;
    $esOrdenParaPago=!in_array(SolicitudPago::STATUS_PAGADA, $solStatusFlags)&&!in_array(SolicitudPago::STATUS_CANCELADA, $solStatusFlags)&&$solProceso===SolicitudPago::PROCESO_AUTORIZADA;
    $esPagada=in_array(SolicitudPago::STATUS_PAGADA, $solStatusFlags)&&!in_array(SolicitudPago::STATUS_CANCELADA, $solStatusFlags);
    $celHigh="";//"background-color:cyan;outline:1px dotted blue;outline-offset:-1px;";
    $divHigh="";//"background-color:yellow;outline:1px dotted red;outline-offset:-1px;";
    $clrBlue="color:#008;";
    $valHigh="background-color:rgba(255,255,255,0.3);"; // no en correo
    $noSelect="-webkit-user-select:none;";
    $pad2="padding:2px;";
    $pad2h="padding:0px 2px;";
    $pad5h="padding:0px 5px;";
    $pad5r="padding-right:5px;";
    $mar5r="margin-right:5px;";
    $font0="font-size:0;";
    $font12="font-size:12px;";
    $font14="font-size:14px;";
    $fbold="font-weight:bold;";
    $inblock="display:inline-block;";
    $noflow="overflow:hidden;";
    $autoflow="overflow:auto;";
    $nowrap="white-space:nowrap;";
    $spwrap="white-space:break-spaces;";
    $lefted="text-align:left;";
    $righted="text-align:right;";
    $centered="text-align:centered;";
    $vaTop="vertical-align:top;";
    $vaMid="vertical-align:middle;";
    $ellipsis="text-overflow:ellipsis;";
    $out="outline:1px solid rgba(255,255,255,0.4);outline-offset:-1px;";
    $wmax="width:100%;";
    $w91p="width:91%;";
    $w096="width:96px;";
    $w066="width:67.2px;";
    $w100="width:100px;";
    $w146="width:146px;";
    $w346="width:343px;"; // no en correo
    $h100="height:100px;";
    $cellMain=$pad2.$font0.$nowrap.$vaTop.$celHigh;
    $cellLeft=$cellMain.$lefted;
    $cellCapt=$noSelect.$cellMain.$lefted;
    $blkMain=$inblock.$noflow.$nowrap.$font14.$clrBlue;
    $blkMain2=$inblock.$nowrap.$font14.$clrBlue;
    $blkMain3=$inblock.$noflow.$spwrap.$font14.$clrBlue;
    $blkCapt=$w096.$blkMain.$divHigh;
    $blkCap2=$w066.$blkMain.$divHigh;
    $blkBase=$blkMain.($isInteractive?$valHigh:"");
    $blkWrap=$blkMain3.($isInteractive?$valHigh:"");
    $blkElps=$blkBase.$ellipsis;
    $blkBLft=$blkBase.$lefted;
    $blkWLft=$blkWrap.$lefted;
    $blkELft=$blkElps.$lefted;
    $blkBRgt=$blkBase.$righted;
    $blkBR91p=$w91p.$blkBRgt;
    $blkELMax=$wmax.$blkELft;
    $blkELMax2=$wmax.$blkMain2.$valHigh.$ellipsis.$lefted;
    $blkBL100=$w100.$mar5r.$blkBLft;
    $blkEL346=($isInteractive?$w346:"").$blkELft;
    $blkTData=$wmax.$h100.$autoflow.$inblock.$vaTop.$font14.$valHigh;
    $blkSmr=$inblock.$vaMid.$font12.$fbold.$pad5r.$clrBlue; // $righted
    $blkCpt=$nowrap.$noflow.$ellipsis.$inblock.$vaMid.$font12.$clrBlue; // $lefted
    $blkAmt=$nowrap.$inblock.$vaMid.$font12.$clrBlue; // $righted
?>
    <table style="width: 550px;margin: 0 auto;">
      <colgroup><col width="100"><col width="200"><col width="70"><col width="180"></colgroup>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">FECHA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?= substr($solData["fechaInicio"]??"", 0, 10) ?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">PAGO</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?= substr($solData["fechaPago"]??"", 0, 10) ?></div></td>
      </tr>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">EMPRESA</div></th>
        <td colspan="3" style="<?=$cellLeft?>"><div style="<?=$blkBL100.$out.$pad2h?>"><?= $gpoData["alias"]??"" ?></div><div id="gpo_detail" style="<?=$blkEL346.$out.$pad2h?>"><?= $gpoData["razonSocial"]??"" ?></div></td>
      </tr>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">PROVEEDOR</div></th>
        <td colspan="3" style="<?=$cellLeft?>"><div style="<?=$blkBL100.$out.$pad2h?>"><?= $prvData["codigo"]??"" ?></div><div id="prv_detail" style="<?=$blkEL346.$out.$pad2h?>"><?= $prvData["razonSocial"]??"" ?></div></td>
      </tr>
<?php
    if (isset($prvData)) {
        $prvStatus=Proveedores::describeBankStatus($prvData["status"],$prvData["verificado"],$prvData["cumplido"],2);
        $prvData["warning"]=Proveedores::$warning;
        if (isset($prvStatus[0])) {
?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt.$out.$pad2h?>">&nbsp;</div></th>
        <td colspan="3" style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$prvStatus?></div></td>
      </tr>
<?php
        }
    }
?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">BANCO</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$prvData["banco"]??"&nbsp;"?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">CLABE</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$prvData["cuenta"]??"&nbsp;"?></div></td>
      </tr>
<?php
    $invId="".(isset($invData["id"])?$invData["id"]:"");
    $ordId="".(isset($ordData["id"])?$ordData["id"]:"");
    $ctrId="".(isset($ctrData["id"])?$ctrData["id"]:"");
    $tieneFactura=isset($invId[0]);
    $tieneOrden=isset($ordId[0]);
    $tieneContra=isset($ctrId[0]);
    if ($tieneOrden) {
        $tieneSello=($ordData["tieneSello"]??"0")!=="0";
        //echo "<!-- ".($tieneSello?"SI":"NO")." TIENE SELLO -->";
        $selloImpreso=($ordData["selloImpreso"]??"0")!=="0";
        //echo "<!-- ".($selloImpreso?"SI":"NO")." SELLO IMPRESO -->";
        //echo "<!-- ".($esRealizaPago?"SI":"NO")." REALIZA PAGO -->";
        //echo "<!-- ".($esSistemas?"SI":"NO")." ES SISTEMAS -->";
        //echo "<!-- ".($esOrdenParaPago?"SI":"NO")." ES ORDEN PARA PAGO -->";
        //echo "<!-- ".($isInteractive?"SI":"NO")." ES INTERACTIVO -->";
        $mostrarSello=!$tieneFactura&&$tieneSello&&!$selloImpreso&&$esRealizaPago&&$esOrdenParaPago&$isInteractive;
        //echo "<!-- ".($mostrarSello?"SI":"NO")." MOSTRAR SELLO -->";
        $recuperarSello=!$tieneFactura&&$tieneSello&&$selloImpreso&&$esSistemas&&$esOrdenParaPago&$isInteractive;
        //echo "<!-- ".($recuperarSello?"SI":"NO")." RECUPERAR SELLO -->";
        $bgPath=$ordData["rutaArchivo"]??"";
        $bgExists=isset($solData["archivoAntecedentes"][0]);
        $bgName=$bgExists?$solData["archivoAntecedentes"]:"sol{$solId}BGO1";
        if (hasUser()&&$isInteractive) $bga=["","ANEXAR ANTECEDENTES"," class=\"pointer".($bgExists?" inhghlght10":"")."\" onclick=\"viewBackgroundDocs();\"","<input type=\"hidden\" id=\"bgdocId\" value=\"$solId\"><input type=\"hidden\" id=\"bgdocPath\" value=\"$bgPath\"><input type=\"hidden\" id=\"bgdocName\" value=\"$bgName\"><input type=\"hidden\" id=\"bgdocExists\" value=\"".($bgExists?"1":"0")."\">"];
        else if ($bgExists) $bga=["<A href=\"{$hostname}{$bgPath}{$bgName}.pdf\" target=\"archivo\">","ANTECEDENTES","","</A>"];
        $addBGDocLink=isset($bga)?"&nbsp;<!-- [BGDLo -->{$bga[0]}<img src=\"{$hostname}imagenes/icons/pdf200Plus.png\" id=\"bgdocImg\" alt=\"{$bga[1]}\" title=\"{$bga[1]}\" width=\"20\" height=\"20\"{$bga[2]}>{$bga[3]}<!-- BGDLo] -->":"";
        if ($esSistemas&&$isInteractive) {
            $replaceTagIni="<div class=\"inblock\" onmouseenter=\"clrem(['retOPDF','retOPLst'],'hidden');\" onmouseleave=\"cladd(['retOPDF','retOPLst'],'hidden');\">";
            $olgp=$ordData["rutaArchivo"].$ordData["nombreArchivo"]."*.pdf";
            $opdfs=isset($ordData["nombreArchivo"])?glob("../".$olgp):[];
            $opdfs=array_reverse($opdfs);
            $dpdfs=array_pop($opdfs);
            $opdfs=str_replace("../", "",$opdfs);
            $spdf="";
            foreach ($opdfs as $pthnm) {
                if (isset($spdf[0])) $spdf.=",";
                $fnm=basename($pthnm);
                $fts=substr($fnm, -16, -14)."/".substr($fnm, -14, -12)."/".substr($fnm, -12, -10)." ".substr($fnm, -10, -8).":".substr($fnm, -8, -6).":".substr($fnm, -6, -4);
                $spdf.="{\"eName\":\"LI\",\"eChilds\":[{\"eName\":\"SPAN\",\"eChilds\":[{\"eName\":\"A\",\"href\":\"{$pthnm}\",\"target\":\"archivo\",\"eText\":\"{$fts}\"}]}]}";
            }
            $mpdfs=array_map('basename',$opdfs);
            $replaceTagList=(isset($opdfs[0])&&false)?"<div id=\"retOPLst\" class=\"inblock posNE8d retFix size8 tooltipCase nodecoration hidden\" tooltip='{\"eName\":\"DIV\",\"className\":\"simple\",\"eChilds\":[{\"eName\":\"P\",\"eText\":\"ORDEN-PDF Previos\"},{\"eName\":\"UL\",\"eChilds\":[$spdf]}]}' onmouseenter=\"viewTooltip(event);\"><img src=\"imagenes/icons/menu7.png\" class=\"size8 top\"></div>":"";
            $replaceTagEnd="$replaceTagList<img src=\"imagenes/icons/backArrow.png\" id=\"retOPDF\" tipo=\"cfdi\" class=\"retFix size8 posSE8 hidden\" title=\"Reemplaza ORDEN-PDF\" name=\"$ordData[nombreArchivo]\" onload=\"this.path='$ordData[rutaArchivo]';\" onclick=\"replaceDoc(event);\"></div>";
        }
?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">ORDEN</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$ordData["folio"]??"&nbsp;"?></div></td>
        <th style="<?=$cellCapt?>"><div class="noprint" style="<?=$blkCap2?>">DOCS</div></th>
        <td style="<?=$cellLeft?>"><div id="ordDocs" val="<?=$ordId?>" class="noprint" style="<?=$blkELMax.$out.$pad2h?>"><!-- [ORDD --><?=$replaceTagIni??""?><A href="<?=$hostname?><?=$ordData["rutaArchivo"].$ordData["nombreArchivo"]?>.pdf" target="archivo"><img src="<?=$hostname?>imagenes/icons/pdf200.png" alt="PDF" title="ORDEN-PDF" width="20" height="20"></A><?=$replaceTagEnd??""?><?= $addBGDocLink ?><?php if($mostrarSello){ ?>&nbsp;<A href="<?=$hostname?><?=$ordData["rutaArchivo"]."ST_".$ordData["nombreArchivo"]?>.pdf" target="archivo" id="stampO" onclick="rompeSello($solId);return true;"><img src="<?=$hostname?>imagenes/icons/pdf200S3.png" alt="PDFSellado" title="SELLO-PDF" width="20" height="20"></A><?php } else if ($recuperarSello) { ?>&nbsp;<div class="inblock outoff26 round" style="width:17.6px;height:20px;" id="restampO"><img src="<?=$hostname?>imagenes/icons/sello1.png" alt="PDFResella" title="Recuperar SELLO-PDF" class="rot30" width="17" height="12" onclick="recuperaSello(<?=$solId?>);"></div><?php } /*else echo "<!-- ES_RESPALDADA=".(in_array(SolicitudPago::STATUS_RESPALDADA, $solStatusFlags)?"SI":"NO").", ES_PAGADA=".(in_array(SolicitudPago::STATUS_PAGADA, $solStatusFlags)?"SI":"NO").", ES_CANCELADA=".(in_array(SolicitudPago::STATUS_CANCELADA, $solStatusFlags)?"SI":"NO").", ES_CONTABLE=".(($solProceso===SolicitudPago::PROCESO_CONTABLE)?"SI":"NO").", TIENE_SELLO=".($tieneSello?"SI":"NO")." ".($ordData["tieneSello"]??"0").", SELLO_IMPRESO=".($selloImpreso?"SI":"NO")." ".($ordData["selloImpreso"]??"0")." -->";*/ ?><?php if(isset($ordData["comprobantePago"][0])){ ?>&nbsp;<A href="<?=$hostname?><?=$ordData["rutaArchivo"].$ordData["comprobantePago"]?>.pdf" class="cpDoc" target="archivo"><img src="<?=$hostname?>imagenes/icons/invChk200.png" alt="ReciboPago" title="COMPROBANTE PAGO" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></A><?php } ?><!-- ORDD] --></div></td>
      </tr>
<?php   if (!$tieneFactura) { ?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">IMPORTE</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkBLft.$out.$pad2h?>"><?=formatCurrency(+$ordData["importe"], $moneda??'MXN')?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">STATUS</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?= OrdenesCompra::describeStatus($ordData["status"]) ?></div></td>
      </tr>
<?php
        }
    }
    if ($tieneContra) {
        $ctrAlias=$ctrData["aliasGrupo"];
        $ctrFolioNum=$ctrData["folio"];
        $ctrFolio="{$ctrAlias}-{$ctrFolioNum}";
        $selloImpreso=$ctrData["selloImpreso"];
        $mostrarSello=($selloImpreso==="0")&&$esRealizaPago&&$esParaPago&&$isInteractive;
        $recuperarSello=($selloImpreso==="1")&&$esSistemas&&$esParaPago&&$isInteractive;
        $ctrQuery=($mostrarSello?"&st=S3":"");
        $ctrClick=($mostrarSello?" onclick=\"rompeSelloCR(this,-6);\"":"");
        $ctrSrc="imagenes/icons/crDoc32".($mostrarSello?"S3":"").".png";
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
            $ctfObj->rows_per_page=0;
        }
        $ctfData=$ctfObj->getData("idContrarrecibo=$solData[idContrarrecibo]",0,"moneda");
        if (isset($ctfData[0]["moneda"][0]))
            $moneda=$ctfData[0]["moneda"]??"MXN";
        if (in_array(SolicitudPago::STATUS_CANCELADA, $solStatusFlags)) $status="CANCELADA";
        else if (in_array(SolicitudPago::STATUS_PAGADA, $solStatusFlags)) $status="PAGADA";
        else if (in_array(SolicitudPago::STATUS_CONTRARRECIBO, $solStatusFlags)) $status="AUTORIZADA";
        else $status="NO AUTORIZADA";
        $comprobantePago=$ctrData["comprobantePago"];
        $fRev=$ctrData["fechaRevision"];
        $yr =substr($fRev, 0, 4);
        $mon=substr($fRev, 5, 2);
        $ruta="archivos/$ctrAlias/$yr/$mon/";
        $docRoot=$_SERVER["DOCUMENT_ROOT"];
        $rutaCompleta=$docRoot.$ruta;
        $comprobanteCRPCompleto=$rutaCompleta.$comprobantePago.".pdf";
        $hrefCRP="{$hostname}{$ruta}{$comprobantePago}.pdf";
        $srcImgCRP="{$hostname}imagenes/icons/invChk200.png";
?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">CONTRA REC.</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$ctrFolio?></div></td>
        <th style="<?=$cellCapt?>"><div class="noprint" style="<?=$blkCap2?>">DOCS</div></th>
        <td style="<?=$cellLeft?>"><div id="ctrDocs" val="<?=$ctrId?>" class="noprint" style="<?=$blkELMax.$out.$pad2h?>"><A href="consultas/Contrarrecibos.php?folio=<?=$ctrFolio.$ctrQuery?>" target="archivo" title="CONTRA-RECIBO <?=$ctrFolio?>"<?=$ctrClick?>><img src="<?=$ctrSrc?>" alt="ContraRecibo" width="20" height="20"></A><?= file_exists($comprobanteCRPCompleto)?"<A href=\"$hrefCRP\" class=\"cpDoc\" target=\"archivo\"><img src=\"$srcImgCRP\" alt=\"ComprobantePago\" title=\"COMPROBANTE DE PAGO\" width=\"20\" height=\"20\" style=\"filter:grayscale(1) brightness(0.8) contrast(2.5)\"></A>":"noCtrCp<!-- '$ruta' | '$comprobantePago' | '.pdf' -->" ?></div></td>
      </tr>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">IMPORTE</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=formatCurrency(+$ctrData["total"], $moneda??'MXN')?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">STATUS</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?= $status ?></div></td>
      </tr>
<?php
    }
    if ($tieneFactura) {
        $invFolio=$invData["folio"]??"";
        $invUuid=$invData["uuid"]??"";
        $viewUuid=substr($invUuid,-10);
        $invSerie=$invData["serie"]??"";
        if (isset($invFolio[0])) $viewFolio=substr($invFolio,-10);
        else if (isset($invUuid[0])) $viewFolio="[$viewUuid]";
        $xml=$invData["nombreInterno"]??"";
        $pdf=$invData["nombreInternoPDF"]??"";
        $ubicacion=$invData["ubicacion"]??"";
        
        global $ctfObj;
        if (!isset($ctfObj)) {
            require_once "clases/Contrafacturas.php";
            $ctfObj=new Contrafacturas();
        }
        global $query;
        $ctfData=$ctfObj->getData("idFactura=$invId",0,"idContrarrecibo");
        $queryList="\n".$query;
        $idContrarrecibo=$ctfData[0]["idContrarrecibo"]??null;
        $ctrQry="";
        if (isset($idContrarrecibo[0])) {
            global $ctrObj;
            if (!isset($ctrObj)) {
                require_once "clases/Contrarrecibos.php";
                $ctrObj=new Contrarrecibos();
            }
            $ctrData=$ctrObj->getData("id=$idContrarrecibo",0,"concat(aliasGrupo,'-',folio) ctrFolio, esCopia");
            $queryList.="\n".$query;
            $ctrFolio=$ctrData[0]["ctrFolio"]??"";
            $ctrImg="crDoc32";
            $ctrQry="folio=$ctrFolio";
            if ($esRealizaPago) { 
                if (empty($ctrData[0]["esCopia"])) {
                    global $tokObj; if(!isset($tokObj)){ require_once "clases/Tokens.php"; $tokObj=new Tokens(); }
                    $tokData=$tokObj->getData("refId=$idContrarrecibo and modulo='contra_original' and status='activo'",0,"id, token");
                    if (!isset($tokData[0])) {
                        $retTok = $tokObj->creaAccion($idContrarrecibo,[0],"contra_original",1,true);
                        $token = $retTok[0]["contra_original"];
                    } else {
                        $token = $tokData[0]["token"];
                    }
                    $ctrQry.="&token=$token";
                    $ctrImg="cr2Ori32";
                } else { $ctrImg="cr2Cop32"; }
            }
        }
        echo "<!-- $queryList -->";
        $idReciboPago=$invData["idReciboPago"]??"";
        if (isset($idReciboPago[0])) {
            $paymData=$invObj->getData("id=$idReciboPago",0,"concat(ubicacion,nombreInternoPDF,'.pdf') paymPdfPath");
            $paymPath=$paymData[0]["paymPdfPath"]??"";
            if (!isset($paymPath[0])) {
                $tieneComplementoPago=false;
            } else {
                $tieneComplementoPago=true;
            }
        } else $tieneComplementoPago=false;
        $tieneSello=($invData["tieneSello"]??"0")!=="0";
        $selloImpreso=($invData["selloImpreso"]??"0")!=="0";
        $mostrarSello=$tieneSello&&!$selloImpreso&&$esRealizaPago&&$esParaPago&&$isInteractive;
        $recuperarSello=$tieneSello&&$selloImpreso&&$esSistemas&&$esParaPago&&$isInteractive;
        if (!$tieneOrden) {
            $bgExists=isset($solData["archivoAntecedentes"][0]);
            $bgName=$bgExists?$solData["archivoAntecedentes"]:"sol{$solId}BGF1";
            if (hasUser()&&$isInteractive) $bga=["","ANEXAR ORDEN DE COMPRA Y ANTECEDENTES"," class=\"pointer".($bgExists?" inhghlght10":"")."\" onclick=\"viewBackgroundDocs();\"","<input type=\"hidden\" id=\"bgdocId\" value=\"$solId\"><input type=\"hidden\" id=\"bgdocPath\" value=\"$ubicacion\"><input type=\"hidden\" id=\"bgdocName\" value=\"$bgName\"><input type=\"hidden\" id=\"bgdocExists\" value=\"".($bgExists?"1":"0")."\">"];
            else if ($bgExists) $bga=["<A href=\"{$hostname}{$ubicacion}{$bgName}.pdf\" target=\"archivo\">","ANTECEDENTES","","</A>"];
            $addBGDocLink=isset($bga)?"&nbsp<!-- [BGDLf -->{$bga[0]}<img src=\"{$hostname}imagenes/icons/pdf200Plus.png\" id=\"bgdocImg\" alt=\"{$bga[1]}\" title=\"{$bga[1]}\" width=\"20\" height=\"20\"{$bga[2]}>{$bga[3]}<!-- BGDLf] -->":"";
        }
        if ($esSistemas&&$isInteractive) {
            $replaceTagIni="<!-- [RpTgI --><div class=\"inblock\" onmouseenter=\"clrem(['retFPDF','retFPLst'],'hidden');\" onmouseleave=\"cladd(['retFPDF','retFPLst'],'hidden');\"><!-- RpTgI] -->";
            $fpdfs=isset($pdf[0])?glob("../".$ubicacion.$pdf."*.pdf"):[];
            $fpdfs=array_reverse($fpdfs);
            $cpdfs=array_pop($fpdfs);
            $fpdfs=str_replace("../", "", $fpdfs);
            $tpdf="";
            foreach ($fpdfs as $pthnm) {
                if (isset($tpdf[0])) $tpdf.=",";
                $fnm=basename($pthnm);
                $fts=substr($fnm, -16, -14)."/".substr($fnm, -14, -12)."/".substr($fnm, -12, -10)." ".substr($fnm, -10, -8).":".substr($fnm, -8, -6).":".substr($fnm, -6, -4);
                $tpdf.="{\"eName\":\"LI\",\"eChilds\":[{\"eName\":\"SPAN\",\"eChilds\":[{\"eName\":\"A\",\"href\":\"{$pthnm}\",\"target\":\"archivo\",\"eText\":\"{$fts}\"}]}]}";
            }
            $npdfs = array_map('basename', $fpdfs);
            $replaceTagList=(isset($fpdfs[0])&&false)?"<!-- [RpTgL --><div id=\"retFPLst\" class=\"inblock posNE8d retFix size8 tooltipCase nodecoration hidden\" tooltip='{\"eName\":\"DIV\",\"className\":\"simple\",\"eChilds\":[{\"eName\":\"P\",\"eText\":\"CFDI-PDF Previos\"},{\"eName\":\"UL\",\"eChilds\":[$tpdf]}]}' onmouseenter=\"viewTooltip(event);\"><img src=\"imagenes/icons/menu7.png\" class=\"size8 top\"></div><!-- RpTgL] -->":"";
            $replaceTagEnd="$replaceTagList<!-- [RpTgE --><img src=\"imagenes/icons/backArrow.png\" id=\"retFPDF\" tipo=\"cfdi\" class=\"retFix size8 posSE8 hidden\" title=\"Reemplaza CFDI-PDF\" name=\"$pdf\" onload=\"this.path='$ubicacion';\" onclick=\"replaceDoc(event);\"></div><!-- RpTgE] -->";
        }
        $sysPath=$_SERVER["DOCUMENT_ROOT"];
        $eaExists=false;
        if (!empty($invData["ea"])) {
            $fechaCrea=$invData["fechaFactura"];
            $tc=$invData["tipoComprobante"];
            $eatc=($tc==="e"?"NC_":($tc==="p"?"RP_":""));
            $eafolio=isset($invFolio[0])?substr($invFolio, -10):$viewUuid;
            $eacp=trim(str_replace("-", "", $cp));
            $eafecha=substr(trim(str_replace("-","",$fechaCrea)),2,6);
            $eafolios=[substr($xml, -10), isset($invFolio[0])?substr($invFolio,-10):$viewUuid,substr($invSerie.$invFolio,-10)];
            $eafolios[]=$eatc.$eafolios[1]; //if (!isset($eafolios[1][9])) $eafolios[]=substr($eatc.$eafolios[1], -10);
            $eafolios[]=$eatc.$eafolios[2]; //if (!isset($eafolios[2][9])) $eafolios[]=substr($eatc.$eafolios[2], -10);
            $eaIdx=-1; 
            do {
                $eaIdx++;
                $eaname="EA_{$eacp}_".$eafolios[$eaIdx]."_{$eafecha}";
                $eawebpath=$ubicacion.$eaname.".pdf";
                $eafullpath=$sysPath.$eawebpath;
                $eaExists=file_exists($eafullpath);
            } while(!$eaExists&&isset($eafolios[$eaIdx+1]));
        }
?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">FACTURA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$viewFolio?></div></td>
        <th style="<?=$cellCapt?>"><div class="noprint" style="<?=$blkCap2?>">DOCS</div></th>
        <td style="<?=$cellLeft?>">
            <div id="invDocs" val="<?=$invId?>" class="noprint" style="<?=$blkELMax2.$out.$pad2h?>"><A href="<?=$hostname?><?=$ubicacion.$xml?>.xml" target="archivo"><img src="<?=$hostname?>imagenes/icons/xml200.png" alt="XML" title="CFDI-XML" width="20" height="20"></A><?php 
                if(isset($pdf[0])) { ?>&nbsp;<?=$replaceTagIni??""?><A href="<?=$hostname?><?=$ubicacion.$pdf?>.pdf" target="archivo"><img src="<?=$hostname?>imagenes/icons/pdf200.png" alt="PDF" title="CFDI-PDF" width="20" height="20"></A><?=$replaceTagEnd??""?><?php }
                if($eaExists) { ?>&nbsp;<A href="<?=$hostname?><?=$eawebpath?>" target="archivo"><img src="<?=$hostname?>imagenes/icons/pdf200EA.png" alt="PDF" title="CFDI-PDF" width="20" height="20"></A><?php }
                if($mostrarSello) { ?>&nbsp;<A href="<?=$hostname?><?=$ubicacion."ST_".$pdf?>.pdf" target="archivo" id="stampF" onclick="rompeSello($solId);return true;"><img src="<?=$hostname?>imagenes/icons/pdf200S3.png" alt="PDFSellado" title="SELLO-PDF" width="20" height="20"></A><?php } else
                if ($recuperarSello) { ?>&nbsp;<div class="inblock outoff26 round" style="width:17.6px;height:20px;" id="restampF"><img src="<?=$hostname?>imagenes/icons/sello1.png" alt="PDFResella" title="Recuperar SELLO-PDF" class="rot30" width="17" height="12" onclick="recuperaSello(<?=$solId?>);"></div><?php } ?>
                <?= $addBGDocLink??"" ?>
                <?php if(isset($ctrQry[0])) { ?>&nbsp;<A href="<?=$hostname?>consultas/Contrarrecibos.php?<?=$ctrQry?>" target="archivo"><img src="<?=$hostname?>imagenes/icons/<?=$ctrImg?>.png" alt="ContraRecibo" title="CONTRA-RECIBO" width="20" height="20"></A><?php }
                if ($tieneComplementoPago) { ?><A href="<?=$hostname?><?=$paymPath?>" target="archivo"><IMG src="<?=$hostname?>imagenes/icons/pdf512.png" width="20" height="20" alt="CFDIPago" title="COMPLEMENTO PAGO" /></A><?php }
                if(isset($invData["comprobantePagoPDF"][0])) { ?>&nbsp;<A href="<?=$hostname?><?=$ubicacion.$invData["comprobantePagoPDF"]?>.pdf" class="cpDoc" target="archivo"><img src="<?=$hostname?>imagenes/icons/invChk200.png" alt="ReciboPago" title="COMPROBANTE PAGO" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></A><?php } ?>
            </div></td>
      </tr>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">CONCEPTOS</div></th>
        <td colspan="3" style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>">
          <table>
            <colgroup><col width="65"><col width="72"><col width="206"><col width="80"><col width="90"></colgroup>
            <thead>
              <tr>
                <th style="<?=$pad5r.$clrBlue?>">Cantidad</th>
                <th style="<?=$clrBlue?>">Código</th>
                <th style="<?=$pad5h.$centered.$clrBlue?>">Descripción</th>
                <th style="<?=$pad5r.$righted.$clrBlue?>">Unidad</th>
                <th style="<?=$righted.$clrBlue?>">Importe</th>
              </tr>
            </thead>
            <tbody id="artTbd">
<?php
        if (isset($cptData[0])) {
            require_once "clases/catalogoSAT.php";
            $cptSuma=0;
            foreach ($cptData as $idx => $cptItem) {
                $cptUnidad=$cptItem["unidad"];
                if (!isset($cptUnidad[0])) {
                    $cptUnidad=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD,"codigo",$cptItem["claveUnidad"],"nombre");
                }
                $cptDescripcion=$cptItem["descripcion"];
                if (!isset($cptDescripcion[0])) {
                    $cptDescripcion=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo",$cptItem["claveProdServ"],"descripcion");
                }
                $cptCantidad=+($cptItem["cantidad"]??"0");
                $cptCodigo=$cptItem["codigoArticulo"]??"";
                $cptImporte=+$cptItem["importe"];
                $cptSuma+=$cptImporte;
?>
              <tr>
                <td style="<?=$pad5r.$lefted?>"><div style="<?=$blkCpt?>"><?=$cptCantidad." ".$cptUnidad?></div></td>
                <td style="<?=$lefted?>"><div style="<?=$blkCpt?>"><?=$cptCodigo?></div></td>
                <td style="<?=$pad5h.$lefted?>"><div class="printWrapped" style="width:182px;<?=$blkCpt?>" title="<?=$cptDescripcion?>"><?=$cptDescripcion?></div></td>
                <td style="<?=$pad5r.$righted?>"><div style="<?=$blkAmt?>"><?=formatCurrency($cptItem["precioUnitario"], $moneda??'MXN')?></div></td>
                <td style="<?=$righted?>"><div style="<?=$blkAmt?>"><?=formatCurrency($cptImporte, $moneda??'MXN')?></div>
                </td>
              </tr>
<?php
            }
            $cptExtraRows=["subtotal"=>$invData["subtotal"]];
            if (!empty($invData["importeDescuento"])) {
                $descuento=+$invData["importeDescuento"];
                if ($descuento>0) $cptExtraRows["descuento"]=-$descuento;
            }
            if (!empty($invData["impuestoRetenido"])) {
                $isr=+$invData["impuestoRetenido"];
                if ($isr>0) $cptExtraRows["isr+iva ret"]=-$isr;
            }
            if (!empty($invData["impuestoTraslado"])) {
                $iva=+$invData["impuestoTraslado"];
                if ($iva>0) $cptExtraRows["iva"]=$iva;
            }
            $cptExtraRows["total"]=+$invData["total"];
            foreach ($cptExtraRows as $key => $value) { ?>
              <tr>
                <td colspan="4" style="<?=$righted?>"><div style="<?=$blkSmr?>"><?=strtoupper($key)?></div></td>
                <td style="<?=$righted?>"><div style="<?=$blkAmt?>"><?=formatCurrency($value, $moneda??'MXN')?></div>
                </td>
              </tr>
<?php
            }
        } else if (isset($baseKeyMap["CONCEPTOS"])) echo $baseKeyMap["CONCEPTOS"];
        else if (isset($invData["status"])&&$invData["status"]==="Temporal") {
            // LEER CONCEPTOS DEL XML
        }
?>
            </tbody>
          </table></div>
        </td>
      </tr>
<?php
    }
?>
<?php
    //global $tokObj;
    //if (!isset($tokObj)) {
    //    require_once "clases/Tokens.php";
    //    $tokObj=new Tokens();
    //}
    //$tokData=$tokObj->getData("refId=$solId and modulo in ('autorizaPago','rechazaPago') and status='ocupado'");
    $observaciones=$solData["observaciones"]??"";
    $oldObs="$observaciones";
    $observaciones=str_replace(["\\r","\\n"], ["\r","\n"], $observaciones);
    //if ($oldObs!==$observaciones) {
    //    $observaciones.="*";
    //}
    if ($isInteractive||isset($observaciones[0])) {
?>
    <tr<?= isset($observaciones[0])?"":" class=\"noprint\"" ?>>
        <th style="<?=$cellCapt?>" title="OBSERVACIONES"><div style="<?=$blkCapt?>">OBSERVAC.</div><?php if ($isInteractive&&hasUser()) { ?><div id="obs_count" class="rightedi fontPageFormat noprint">200 char</div><?php } ?></th>
        <td colspan="3" style="<?=$cellLeft?>"><div style="<?=($isInteractive?$blkBLft:$blkWLft).$wmax.$out.$pad2h?>"><?php if (!$isInteractive||!hasUser()) { echo "<span id=\"observaciones\" int=\"".($isInteractive?"1":"0")."\" usr=\"".(hasUser()?"1":"0")."\">".str_replace("\n", "<br>", $observaciones)."</span>"; } else { ?><textarea id="observaciones" sid="<?=$solId?>" class="noresize wid95 vAlignCenter noprintBorder noprintFlow nooverfix" maxlength="200" onkeyup="if (countObs) countObs();"><?=$observaciones?></textarea><img src="<?=$hostname?>imagenes/icons/carga8_20.png" alt="Guardar" title="GUARDAR" class="btnLt btn20 vAlignCenter marL2 noprint" onclick="if (saveObs) saveObs(); else logObs();" onload="if (initObs) initObs();"><?php } ?></div>
        </td>
    </tr>
<?php
    } else echo "<!-- int=".($isInteractive?"1":"0")." obs=".(isset($observaciones[0])?"1":"0")."-->";
    global $firObj;
    if (!isset($firObj)) {
        require_once "clases/Firmas.php";
        $firObj=new Firmas();
    }
    $firObj->rows_per_page=0;
    $firData=$firObj->getData("idReferencia=$solId and modulo='solpago'");
    if (isset($firData[0])) {
        $firMap=[];
        foreach ($firData as $idx=>$firRow) {
            $firUsrData=$usrObj->getData("id=".$firRow["idUsuario"]);
            if (isset($firUsrData[0]["id"][0])) $firUsrData=$firUsrData[0];
            if (isset($firUsrData["id"][0])) {
                unset($firUsrData["password"]);
                unset($firUsrData["seguro"]);
                $firRow["usuario"]=$firUsrData;
                $firData[$idx]=$firRow;
            }
            $firMap[$firRow["accion"]]=$firRow;
        }
        if (!isset($firMap["solicita"]) && isset($usrData)) { ?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">SOLICITANTE</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$usrData["nombre"]?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">FECHA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=substr($solData["fechaInicio"],0,10)?></div></td>
      </tr>
<?php
        }
        if (!isset($firMap["autoriza"]) && !isset($firMap["rechaza"]) && isset($autData)) { ?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">AUTORIZADOR</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$autData["nombre"]?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">FECHA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=substr($solData["modifiedTime"],0,10)?></div></td>
      </tr>
<?php
        }
        foreach ($firData as $idx=>$firRow) {
            $accion=strtoupper($firRow["accion"]);
            $motivo=$firRow["motivo"]??"";
            if ($isInteractive&&isset($motivo[0])) {
                $accTtl=" title=\"MOTIVO: $motivo\"";
                $accion.=" <img src=\"imagenes/icons/hoverIconC.png\" class=\"size12 abs_se marginH2 noprint\"{$accTtl}>";
                $accCapt="position: relative;";
            } else { $accTtl=""; $accCapt=""; }
            ?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt.$accCapt?>"<?=$accTtl?>><?= $accion ?></div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>" title="<?=$firRow["usuario"]["nombre"]??$firRow["idUsuario"] ?>"><?=$firRow["usuario"]["persona"]??$firRow["idUsuario"] ?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">FECHA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=substr($firRow["fecha"],0,16)?></div></td>
      </tr>
<?php
            if ($accion==="CANCELA" && isset($motivo[0]) && $puedeVerMotivo) { ?>
      <tr>
        <th style="<?=$cellCapt?>">&nbsp;</th>
        <td style="<?=$cellLeft?>" colspan="3"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$motivo ?></div></td>
      </tr>
<?php
            }
        }
    } else {
        if (isset($usrData)) { ?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">SOLICITANTE.</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$usrData["nombre"]?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">FECHA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=substr($solData["fechaInicio"],0,10)?></div></td>
      </tr>
<?php
        }
        if (isset($autData)) { ?>
      <tr>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCapt?>">AUTORIZADOR.</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=$autData["nombre"]?></div></td>
        <th style="<?=$cellCapt?>"><div style="<?=$blkCap2?>">FECHA</div></th>
        <td style="<?=$cellLeft?>"><div style="<?=$blkELMax.$out.$pad2h?>"><?=substr($solData["modifiedTime"],0,10)?></div></td>
      </tr>
<?php
        } // else if orden
    }
?>
    </table>
<?php
    if (false && $esDesarrollo) {
        echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"addAuthReqButton($solId);ekil(this);\">";
    }
    if (hasUser() && $isInteractive) {
        require "templates/svgfilterurl.php";
        include "templates/overlay.php";
    }
}
