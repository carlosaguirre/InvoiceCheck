<?php
echo "\n<!-- $controlAction -->\n";
clog2ini("templates.listasolp");
clog1seq(1);
$hasShownAny=false;
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">Listado de Solicitudes de Pago</h1>
  <div id="area_detalle" class="noboots scrollauto width100 noOutline" tabindex="-1" autofocus>
    <div id="header_detalle" class="sticky toTop toLeft zIdx100 basicBG relative">
        <form id="filterForm" target="_self" method="POST" class="marbtm5">
            <input type="hidden" name="menu_accion" value="ListaSolPago">
            <div class="screen pad3 abs toLeft toTop zIdx200" style="width: 76px;"><button type="button" id="pickFilter" onclick="showFilterList();">FILTROS</button></div>
            <div id="filterSpace" class="pad3 all_space fs12" style="margin-left:76px;width:calc(100% - 77px);transform:translate(0, -2px);"><div id="emptyblock" class="fltL inblock" style="height:32px;"></div>
<?php
foreach ($filtros as $filterId => $filterData) {
    $filterNum=count($filterData);
    clog2("FILTRO $filterId = ".json_encode($filterData)." #".$filterNum);
?>
                <div id="<?=$filterId?>block" class="fltL pad3 inblock br1_8"><span class="inblock vAlignCenter" style="max-width: calc(100% - 14px);"><b><?=$listaFiltros[$filterId]["texto"]??""?></b>: <?php
    foreach ($filterData as $idx => $value) {
        if ($idx>0) echo " - ";echo $value;
    }
    if (isset($listaFiltros[$filterId]["ids"])) {
        $lstFIdNum=count($listaFiltros[$filterId]["ids"]);
        clog2("IDS:".json_encode($listaFiltros[$filterId]["ids"])." #".$lstFIdNum);
        $sameNum=($filterNum==$lstFIdNum);
        foreach($listaFiltros[$filterId]["ids"] as $filterIdx=>$filterName) {
            if ($sameNum&&isset($filterData[$filterIdx]))
                echo "<input type=\"hidden\" name=\"$filterName\" value=\"{$filterData[$filterIdx]}\">";
            else if ($lstFIdNum==1) {
                foreach ($filterData as $idx => $value) {
                    echo "<input type=\"hidden\" name=\"{$filterName}[]\" value=\"{$filterData[$idx]}\">";
                }
            }
        }
    }
                ?></span><span class="inblock relative topvalign padl2 marL2 v24_12"><img class="abs_nw btn12 btnOI" id="<?=$filterId?>ren" filterId="<?=$filterId?>" src="imagenes/icons/rename12.png" onclick="filterAction(event);"><img class="abs_sw btn12 btnOI" id="<?=$filterId?>del" src="imagenes/icons/deleteIcon12.png" onclick="filterAction(event);"></span></div>
<?php
}
?>
            </div>
            <img src="imagenes/pt.png" onload="filterCalcHeight();ekil(this);">
        </form>
    </div>
    <hr class="marginH3 clear"/>
<?php
$hasAny=false;
$reenvioCorreoMasivo=$esPruebas; //$_esSistemas; // false;
$usrId=getUser()->id;
foreach ($showList as $key => $data) {
    $num=count($data);
    // echo "<!-- SHOWLIST $key($num) -->\n";
    $blkNoAuth=($key==="NoAutorizadas");
    $blkRdy4Py=($key==="ParaPago");
    $blkPaidNoInv=($key==="SinFactura");
    $blkPaidWtInv=($key==="ConFactura");
    $blkPaidAuth=($key==="Pagadas");
    if ($num>0) {
        // echo "<!-- SHOWLIST has data".($num==1?": ".json_encode($data):"")." -->\n";
        $hasAny=true;
        $assistanceKey="";
        $headerCheck="";
        $statusCheck="";
        $bg0=trim($showClass[$key]??"");
        if (isset($bg0[0])) $bg0=" {$bg0}0";
        else $bg0=" basicBG";
        $va0=" vAlignCenter";
        $brc=0; // block requests count: número de solicitudes en bloque actual: Sin importar las empresas, los autorizadores sólo verán las solicitudes que pueden autorizar.
        switch($key) {
            case "NoAutorizadas": // $blkNoAuth:
                $assistanceKey=" <img src=\"imagenes/icons/refresh.png\" title=\"Recargar Lista\" width=\"24\" height=\"24\" class=\"pointer btnLt vATTop\" onclick=\"overlayClose();viewWaitBackdrop();location.reload(true);\">";
                $va0="";
                if ($reenvioCorreoMasivo) {
                    $headerCheck=" <input type=\"checkbox\" class=\"topvalign noprint\" onclick=\"checkAll(event,'rsmchk')\">";
                    $statusCheck="<input type=\"checkbox\" %KEY% class=\"rsmchk noprint vAlignCenter\">";
                }
                break;
            case "ParaPago": // $blkRdy4Py:
                if ($esRealizaPago||$_esSistemas) {
                    //$folioCheck="<input type=\"checkbox\" class=\"vAlignCenter\">";
                    $headerCheck=" <input type=\"checkbox\" class=\"topvalign noprint\" onclick=\"checkAll(event,'pymchk')\">";
                    $statusCheck="<input type=\"checkbox\" %KEY% class=\"pymchk noprint vAlignCenter\">";
                }
                break;
            case "SinFactura": // $blkPaidNoInv:
                $assistanceKey=" <img src=\"imagenes/icons/descarga3.png\" title=\"Descargar Lista de Folios\" width=\"24\" height=\"24\" class=\"pointer btnLt vATTop\" onclick=\"descargaFolios(this);\">";
                break;
        }
        $hdCls="sortH";//$_esDesarrollo?"sortH":"";
        $hdCls0=isset($hdCls[0])?" class=\"$hdCls\"":"";
        $hdClsEmp=$hdCls0;
        if (isset($filtros["filter04"])&&count($filtros["filter04"])==1) $hdClsEmp="";
        $hdClsPrv=$hdCls0;
        if (isset($filtros["filter05"])&&count($filtros["filter05"])==1) $hdClsPrv="";
        $hdCls1=isset($hdCls[0])?" $hdCls":"";
?>
        <fieldset id="<?=$key?>" num="<?=$num?>">
            <legend class="relative"><?= ($titles[$key]??$key).$assistanceKey ?></legend>
            <table class="lstpago separate0 cellborder1<?=$showClass[$key]??""?>">
                <thead><tr idx="0"><th class="sticky toLeft zIdx4<?=$bg0.$hdCls1?>">Folio</th><th<?=$hdClsEmp?>>Empresa</th><th<?=$hdClsPrv?>>Prov.</th><th<?=$hdCls0?>>Orden o Factura</th><th<?=$hdCls0?>><?=$tipoFechaCap?></th><th<?=$hdCls0?>>Pago</th><th<?=$hdCls0?>>Importe</th><th<?=$hdCls0.($blkNoAuth?" colspan=\"2\"":"")?>>Usuario</th>
                    <?= $blkNoAuth?"":"<th".$hdCls0.">Autoriza</th>" ?><th class="sticky toRight zIdx3 bxslft<?=$bg0.$va0?>">Status<?= $headerCheck ?></th>
                </tr></thead>
                <tbody>
<?php   foreach ($data as $rowIdx=>$row) {
            $rowId=$row["id"];
            $authList=$row["authList"]??"";
            $prc=+$row["proceso"];
            $bg1=""; $bg2=$bg0;
            $statusIconNotPaid=false;
            if ($esAuthPago) {
                if ($blkNoAuth) { // si no es autorizador no mostrar
                    // si authlist esta vacio revisar tabla tokens con esta solicitud y este autorizador, si no tiene token continue
                    if (!isset($authList[0])) {
                        global $tokObj; if(!isset($tokObj)){ require_once "clases/Tokens.php"; $tokObj=new Tokens(); }
                        $tokData=$tokObj->getData("refId=$rowId and usrId=$usrId and modulo='autorizaPago' and status='activo'",0,"id");
                        if (!isset($tokData[0])) {
                            $rowJs=json_encode($row);
                            echo "<!-- USER $usrId WITHOUT TOKEN\n{$rowJs} -->";
                            continue;
                        }
                    } else if (strpos($authList, $usrId)===false) {
                        $rowJs=json_encode($row);
                        echo "<!-- USER $usrId NOT IN AUTHLIST\n{$rowJs} -->";
                        continue;
                    }
                }
                if ($blkPaidAuth && !isset($row["fac_cprv"][0]) && $prc<SolicitudPago::PROCESO_NOREQ_FACTURA) {
                    $bg1="bgorangea"; $bg2=" bgorangex"; $statusIconNotPaid=true;
                }
                // ToDo: $blkPaidAuth && $row["proceso"]<SolicitudPago::PROCESO_NOREQ_FACTURA && !isset($row["fac_cprv"])
                //       then row background color = orange
            }
            $brc++; $reg=null; $pdfExt2=""; $breakId="";
            $rowFolio=$row["sol_folio"];
            $gpoId=$row["sol_igpo"]??"";
            $alias=$row["alias"]??"";
            if (isset($row["fac_folio"][0])||isset($row["fac_uuid"][0])) {
                $prvId=$row["fac_iprv"]??"";
                $ruta=$row["fac_ruta"]??""; $xml=$row["fac_xml"]??""; $pdf=$row["fac_pdf"]??"";
                if (isset($pdf[0])&&$row["fac_stp"]=="1"&&$row["fac_sim"]=="0"&&($esRealizaPago||$_esSistemas)&&$blkRdy4Py) {
                    $pdf="ST_".$pdf; $pdfExt2="S3"; $breakId="$rowId";
                }
                $xmlLink=getLink2Doc($ruta.$xml,"xml");
                $pdfLink=(isset($pdf[0])?getLink2Doc($ruta.$pdf,"pdf",$pdfExt2,$breakId):"");
                $reg=["tipo"=>"F", "folio"=>$row["fac_folio"]??"[$row[fac_uuid]]", "cprv"=>$row["fac_cprv"], "rzsc"=>$row["fac_razsoc"], "fecha"=>$row["fac_fecha"]??"", "xml"=>$xmlLink, "pdf"=>$pdfLink, "total"=>$row["fac_total"]??"", "moneda"=>$row["fac_mon"]??""];
            } else if (isset($row["ord_folio"][0])) {
                $prvId=$row["ord_iprv"]??"";
                $ruta=$row["ord_ruta"]??""; $pdf=$row["ord_pdf"]??"";
                if (isset($pdf[0])&&$row["ord_stp"]=="1"&&$row["ord_sim"]=="0"&&($esRealizaPago||$_esSistemas)&&$blkRdy4Py) {
                    $pdf="ST_".$pdf; $pdfExt2="S3"; $breakId="$rowId";
                }
                $pdfLink=(isset($pdf[0])?getLink2Doc($ruta.$pdf,"pdf",$pdfExt2,$breakId):"");
                $reg=["tipo"=>"O", "folio"=>$row["ord_folio"], "cprv"=>$row["ord_cprv"], "rzsc"=>$row["ord_razsoc"], "fecha"=>$row["ord_fecha"]??"", "xml"=>"", "pdf"=>$pdfLink, "total"=>$row["ord_total"]??"", "moneda"=>$row["ord_mon"]??""];
            } else if (isset($row["ctr_folio"][0])) {
                $ctrFolio=$row["ctr_folio"];
                $prvId=$row["ctr_iprv"]??"";
                $ctrQry="";
                //$esSolicitud=true;
                $estaPagado=$prc>=4; /* $esSolicitud&& */
                $esOriginal = empty($row["ctr_copia"]) && hasUser() && !$_esProveedor && ($estaPagado||$esRealizaPago); // $esSolicitud?(:Contrafacturas->esAutorizado($ctrId))
                if ($esOriginal) {
                    global $tokObj; if(!isset($tokObj)){ require_once "clases/Tokens.php"; $tokObj=new Tokens(); }
                    $tokData=$tokObj->getData("refId=$ctrId and modulo='contra_original' and status='activo'",0,"id, token");
                    if (!isset($tokData[0])) {
                        $retTok = $tokObj->creaAccion($ctrId,[0],"contra_original",1,true);
                        $token = $retTok[0]["contra_original"];
                    } else {
                        $token = $tokData[0]["token"];
                    }
                    $ctrQry.="&token=$token";
                }
                if ($row["ctr_sim"]==="0"&&($esRealizaPago||$_esSistemas)&&$blkRdy4Py) {
                    $pdfExt2="S3";
                    $ctrQry.="&st=S3";
                }
                $ctrLink=getLink2Doc("consultas/Contrarrecibos","php?folio={$alias}-{$ctrFolio}{$ctrQry}",$pdfExt2);
                $ctrId="".($row["ctr_id"]??"");
                $ctrMon="";
                if (isset($ctrId[0])) {
                    global $ctfObj;
                    if (!isset($ctfObj)) {
                        require_once "clases/Contrafacturas.php";
                        $ctfObj = new Contrafacturas();
                    }
                    $ctfData = $ctfObj->getData("idContrarrecibo=$ctrId",0,"distinct moneda");
                    if (isset($ctfData[0]["moneda"][0]) && !isset($ctfData[1])) $ctrMon=$ctfData[0]["moneda"];
                }
                // Contrafacturas select distinct moneda
                $reg=["tipo"=>"C", "folio"=>$ctrFolio, "cprv"=>$row["ctr_cprv"], "rzsc"=>$row["ctr_razsoc"], "fecha"=>$row["ctr_fecha"]??"", "xml"=>"", "pdf"=>$ctrLink, "total"=>$row["ctr_total"]??"", "moneda"=>$ctrMon];
            } else {
                $prvId="";
                $pdfLink="";
                $reg=["tipo"=>"F", "folio"=>"", "cprv"=>$row["fac_cprv"]??"", "rzsc"=>$row["row_razsoc"]??"", "fecha"=>$row["fac_fecha"]??"", "xml"=>"", "pdf"=>$pdfLink, "total"=>0];
            }
            $anioMesInicio=intval(substr($row["inicio"], 0,4).substr($row["inicio"],5,2));
            $currMoneda=isset($reg['moneda'][0])?$reg['moneda']:"MXN";
            $viewMoneda=/*$reg["moneda"]??*/"";
            $cutFolio=$reg["folio"];
            if (isset($cutFolio[10])) {
                if ($cutFolio[0]==="[") $cutFolio="[".substr($cutFolio,-9);
                else {
                    // ToDo: cada caracter tiene un valor (1/n), de acuerdo a los comentarios mas abajo, sumar caracteres de atras para adelante mientras la suma sea menor a 1
                    $cutFolio=substr($cutFolio, -11);
                    if ($cutFolio!==" Incorrecto") $cutFolio=substr($cutFolio, -9);
                    $cutFolio="...".$cutFolio;
                }
                // 23: il
                // 19: j
                // 17: . <space>
                // 16: ft
                // 14: rI-
                // 12: szJ
                // 10: acekvxyFL
                //  9: bdghnopquABEKPSTVXYZ1234567890
                //  8: CGNRU
                //  7: wDHMOQ
                //  6: m
                //  5: W
                // Incorrecto = 1/16 + 3/14 + 3/9 + 3/10 = 0.91
            }
            $hasButtonToForm=true;
            $encabezado=($blkPaidWtInv)?",'SOLICITUD PAGADA $rowFolio'":"";
            $viewForm=($hasButtonToForm?"onclick=\"viewForm($rowId,'$rowFolio'{$encabezado});\"":"");
            if ($tipoFecha==="solicitud") $fechaInicio=substr($row["inicio"],0,10);
            else if ($tipoFecha==="factura") $fechaInicio=substr($reg["fecha"],0,10);
            $stt=+$row["status"];
            $typ=$reg["tipo"];
            $nFl=$_esSistemas?".1":""; // no file required
            $bgExists=isset($row["archivoAntecedentes"][0]);
            $bgBadge=($bgExists?"<div class='abs_se badge' title='Tiene Antecedentes'>+</div>":"");
            $authUso=$row["authUso"];
            $rechUso=$row["rechUso"];
            if ($stt>=SolicitudPago::STATUS_CANCELADA) {
                if (!isset($row["autoriza"][0]) || $anioMesInicio<$anioMesActual ) {
                    $authUso=0; $rechUso=0;
                }
            }
            if ($_esSistemas) {
                global $firObj;
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj = new Firmas();
                }
                $firData = $firObj->getData("modulo='solpago' and idReferencia=$rowId and motivo is not null and length(motivo)>0",0,"accion,motivo");
                foreach ($firData as $idx => $firRow) {
                    if (isset($motivo[0])) $motivo.="\n";
                    else if (!isset($motivo)) $motivo="";
                    $motivo.=strtoupper($firRow["accion"]).": $firRow[motivo]";
                }
            }
            $authPrc=$prc<2;
            $usos=["auth"=>$authPrc&&($authUso===null||$authUso>0),"rech"=>$authPrc&&($rechUso===null||$rechUso>0)];
            $rowClass="";
            $hoy=date("Y-m-d");
            //$bg2=$bg0;
            if ($fechaInicio===$hoy) {
                /*if (isset($bg1[0])) {
                    $bg1.="1"; //$bg2=" $bg1";
                } else */$bg1="bgwhite7";
            }// else if (isset($bg1[0])) { $bg1.="3"; }
            if ($blkRdy4Py) $rowClass="payBlock";
            if (isset($bg1[0])) {
                if (isset($rowClass[0])) $rowClass.=" ";
                $rowClass.=$bg1;
            }
            if (isset($rowClass[0])) $rowClass=" class=\"$rowClass\"";
?>
                <tr id="row<?=$rowId?>" idx="<?=$rowIdx+1?>"<?=$rowClass?> folId="<?=$rowFolio?>" prcId="<?=$prc?>" sttId="<?=$stt?>" typId="<?=$typ.$nFl?>" gpoId="<?=$gpoId?>" prvId="<?=$prvId?>" auIs="<?=$authList?>" tot="<?= $reg["total"] ?>" mon="<?= $currMoneda ?>"><td class="sticky toLeft zIdx2<?=$bg2?>"><div class="listfolioButton vAlignCenter<?= $hasButtonToForm?" btnLt bRad2 pointer":"" ?>" <?=$viewForm?>><?= $rowFolio.$bgBadge ?></div> <?= $folioCheck??"" ?></td>
                    <td title="<?= $row['empresa'] ?>"><div class="wid70px"><?= $alias ?></div></td>
                    <td title="<?= $reg['rzsc'] ?>"><div class="wid48px"><?= $reg["cprv"] ?></div></td>
                    <td><div class="wid100px lefted" title="<?= $reg["folio"] ?>"><span ondblclick="redirect2InvoiceReport();" class="pre"><?= "$typ $cutFolio" ?></span></div><div class="wid48px noprint"><?= "$reg[xml] $reg[pdf]" ?></div></td>
                    <td><div class="wid77px"><?= $fechaInicio ?></div></td>
                    <td><div class="wid77px"><?= substr($row["pago"],0,10) ?></div></td>
                    <td><div class="wid135px righted"><?= formatCurrency($reg["total"],$currMoneda).$viewMoneda ?></div></td>
<?php       
            echo "<!-- YMIni = $anioMesInicio, YMCur = $anioMesActual -->";
            $myStatus=getStatusName($stt,$prc,$key,$rowId,$rowFolio,$usos);
            if (isset($statusCheck)) {
                if (isset($myStatus[0])) $myStatus.=" ";
                $myStatus.=str_replace("%KEY%", "id=\"chk$rowId\" solid=\"$rowId\"", $statusCheck);
            }
            $fixBG=isset($myStatus[0])?$bg2:" basicBG";
            $rowSettings="<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"ekil(this);\">";
            if ($blkNoAuth) {
                global $usrObj;
                if (!isset($usrObj)) {
                    require_once "clases/Usuarios.php";
                    $usrObj=new Usuarios();
                }
                $usrObj->clearOrder();
                $usrObj->addOrder("FIELD(id, {$authList})","");
                $authData=$usrObj->getData("id in ($authList)",0,"persona");
                $authNames="";
                foreach ($authData as $idx => $authRow) {
                    if (isset($authRow["persona"][0])) {
                        if (isset($authNames[0])) $authNames.=", ";
                        $authNames.=$authRow["persona"];
                    }
                }
                if (isset($authNames[0])) {
                    $myStatus=str_replace("title=\"REENVIAR\"", "title=\"REENVIAR $authNames\"", $myStatus);
                    $ttlIdx=strpos($myStatus, "title=\"REENVIAR");
                    if ($ttlIdx>0) {
                        $rsndCnt=+($_SESSION['resendCounter{$rowId}']??"0");
                        $rsndCap="ve".($rsndCnt==1?"z":"ces");
                        //$resendBadge=($rsndCnt>0?"<div class='abs_se badge' title='Reenviado $rsndCnt $rsndCap'>+</div>":"");
                        if ($rsndCnt>0) {
                            $myStatus=substr($myStatus, 0, $ttlIdx)."rsndTms=\"$rsndCnt $rsndCap\" ".substr($myStatus, $ttlIdx);
                        }
                    }
                }
                ?>
                    <td colspan="2"><div class="wid205px" title="<?= $row["usuario_nombre"]??"" ?>"><?= $row["usuario"] ?></div></td>
                    <td class="sticky toRight zIdx1 bxslft<?=$bg2?>"><div class="listStatusButton1col centered vAlignCenter"><?= $myStatus ?></div></td>
<?php       } else { 
                if (isset($motivo[0])) {
                    $statusTitle=" title=\"$motivo\"";
                    $fixBG=" bgblue0";
                    global $query;
                    clog2("QUERY: $query");
                } else $fixBG=$bg2;
                if ($statusIconNotPaid) $myStatus=str_replace(["title=\"PAGADA\"","crPaid32"], ["title=\"PAGADA SIN FACTURA\"","crPaidNoInv32"], $myStatus);
?>
                    <td><div class="wid100px" title="<?= $row["usuario_nombre"]??"" ?>"><?= $row["usuario"] ?></div></td>
                    <td><div class="wid100px" title="<?= $row["autoriza_nombre"]??"" ?>"><?= $row["autoriza"] ?></div></td>
                    <td class="sticky toRight zIdx1 bxslft<?=$fixBG?>"<?=$statusTitle??""?>><div class="listStatusButton1col centered vAlignCenter"><?= $myStatus ?></div></td>
<?php       }
            $motivo=null; $statusTitle=null; ?>
                </tr>
<?php   }
        //$reqIds=array_column($data, "id");
        //$reqReg=array_column($data, "sol_folio");
        //$reqList=array_combine($reqIds, $reqReg); ?>
                </tbody>
            </table>
<?php
        ?>
        </fieldset>
        <div class="lefted pad1 padb25 relative sticky toLeft">
<?php   $z=false;
        if ($brc>1) {
            $z=true; ?>
            <?= $brc." ".mb_strtolower($titles[$key]??$key) ?>
<?php   } else if ($brc==0) { ?>
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const dv=this.parentNode;const fl=dv.previousElementSibling; console.log(fl); ekil(fl); ekil(dv);">
<?php   }
        if ($reenvioCorreoMasivo && $blkNoAuth) {
            $z=true; ?>
            <div class="abs_e righted pad1 inblock btnfsa hidden"><input type="button" class="massauth" value="Autoriza Elegidas" onclick="massAuth()"> <input type="button" class="remail" value="Reenviar Email" onclick="resendEmail()"></div>
<?php   } else if (($esRealizaPago||$_esSistemas) && $blkRdy4Py) {
            $massPayBtn=($esRealizaPago||$_esSistemas)?"<input type=\"button\" class=\"boots\" value=\"Pago Masivo\" onclick=\"massPayment(event);\">":"";
            $genMassCls=($esRealizaPago||$_esSistemas)?" marL4 marR1":"";
            $z=true; ?>
            <div class="abs_e righted pad1 inblock btnfsa hidden"><?=$massPayBtn?><input type="button" class="boots<?=$genMassCls?>" value="Genera Doc Pago" onclick="generaDoc()"><input type="button" class="boots marL4 marR1" value="Marcar Pagadas" onclick="pagoMultiple()"></div>
<?php   }
        if ($z) { ?>
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const dv=this.parentNode;dv.style.maxWidth=(1+dv.previousElementSibling.clientWidth)+'px';ekil(this);">
<?php   } ?>
        </div>
<?php
    } //else echo "<!-- SHOWLIST NO DATA -->";
}
if (!$hasAny) {
    if ($numResults>$maxResults) echo "<H3>Demasiados resultados ($numResults), especifique mas detalle en filtros para reducir la búsqueda</H3><H4>El máximo número de resultados a desplegar en este momento son $maxResults";
    else echo "<H3>Ningún registro encontrado</H3>";
}
?>
  </div>
</div>
<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fee(lbycn('btnfsa'),el=>clrem(el,'hidden'));ekil(this);">
<?php
clog1seq(-1);
clog2end("templates.listasolp");
