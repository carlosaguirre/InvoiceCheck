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
    clog2("$filterId = ".json_encode($filterData)." #".$filterNum);
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
$reenvioCorreoMasivo=$esPruebas; //$esSistemas; // false;
foreach ($showList as $key => $data) {
    if (isset($data[0])) {
        $hasAny=true;
        if ($reenvioCorreoMasivo && $key==="NoAutorizadas") {
            $headerCheck=" <input type=\"checkbox\" class=\"topvalign noprint\" onclick=\"checkAll(event,'rsmchk')\">";
            $statusCheck="<input type=\"checkbox\" %KEY% class=\"rsmchk noprint vAlignCenter\">";
        } else if (($esRealizaPago||$esSistemas) && $key==="ParaPago") {
            //$folioCheck="<input type=\"checkbox\" class=\"vAlignCenter\">";
            $headerCheck=" <input type=\"checkbox\" class=\"topvalign noprint\" onclick=\"checkAll(event,'pymchk')\">";
            $statusCheck="<input type=\"checkbox\" %KEY% class=\"pymchk noprint vAlignCenter\">";
        } else {
            $headerCheck="";
            $statusCheck="";
        }

        if ($key==="NoAutorizadas") {
            $assistanceKey=" <img src=\"imagenes/icons/refresh.png\" title=\"Recargar Lista\" width=\"24\" height=\"24\" class=\"pointer btnLt vATTop\" onclick=\"overlayClose();viewWaitBackdrop();location.reload(true);\">";
        } else if ($key==="SinFactura") {
            $assistanceKey=" <img src=\"imagenes/icons/descarga3.png\" title=\"Descargar Lista de Folios\" width=\"24\" height=\"24\" class=\"pointer btnLt vATTop\" onclick=\"descargaFolios(this);\">";
        } else $assistanceKey="";
        $bg0=trim($showClass[$key]??"");
        if (isset($bg0[0])) $bg0=" {$bg0}0";
        else $bg0=" basicBG";
        $brc=0; // block requests count: número de solicitudes en bloque actual: Sin importar las empresas, los autorizadores sólo verán las solicitudes que pueden autorizar.
        
?>
        <fieldset id="<?=$key?>">
            <legend class="relative"><?= ($titles[$key]??$key).$assistanceKey ?></legend>
            <!-- <?= $qryList["$key"]??"" ?> -->
            <table class="lstpago separate0 cellborder1<?=$showClass[$key]??""?>">
                <thead><tr><th class="sticky toLeft zIdx4<?=$bg0?>">Folio</th><th>Empresa</th><th>Prov.</th><th>Orden o Factura</th><th><?=$tipoFechaCap?></th><th>Pago</th><th>Importe</th><th>Usuario</th>
<?php   if ($key==="NoAutorizadas") { ?>
                    <th colspan="2" class="sticky toRight zIdx3 bxslft<?=$bg0?>">Status<?= $headerCheck ?></th>
<?php   } else { ?>
                    <th>Autoriza</th><th class="sticky toRight zIdx3 bxslft<?=$bg0?> vAlignCenter">Status<?= $headerCheck ?></th>
<?php   } ?>
                </tr></thead>
                <tbody>
<?php   if (isset($data[0])) foreach ($data as $row) {
            $rowId=$row["id"];
            $authList=$row["authList"]??"";
            if ($esAuthPago && $key==="NoAutorizadas") { // si no es autorizador no mostrar
                // si authlist esta vacio revisar tabla tokens con esta solicitud y este autorizador, si no tiene token continue
                $usrId=getUser()->id;
                if (!isset($authList[0])) {
                    global $tokObj;
                    if (!isset($tokObj)) {
                        require_once "clases/Tokens.php";
                        $tokObj = new Tokens();
                    }
                    $tokData = $tokObj->getData("refId=$rowId and usrId=$usrId and modulo='autorizaPago' and status='activo'",0,"id");
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
            $brc++; $reg=null; $pdfExt2=""; $breakId="";
            $rowFolio=$row["sol_folio"];
            $gpoId=$row["sol_igpo"]??"";
            $alias=$row["alias"]??"";
            if (isset($row["fac_folio"][0])||isset($row["fac_uuid"][0])) {
                $prvId=$row["fac_iprv"]??"";
                $ruta=$row["fac_ruta"]??""; $xml=$row["fac_xml"]??""; $pdf=$row["fac_pdf"]??"";
                if (isset($pdf[0])&&$row["fac_stp"]=="1"&&$row["fac_sim"]=="0"&&($esRealizaPago||$esSistemas)&&$key==="ParaPago") {
                    $pdf="ST_".$pdf; $pdfExt2="S3"; $breakId="$rowId";
                }
                $xmlLink=getLink2Doc($ruta.$xml,"xml");
                $pdfLink=(isset($pdf[0])?getLink2Doc($ruta.$pdf,"pdf",$pdfExt2,$breakId):"");
                $reg=["tipo"=>"F", "folio"=>$row["fac_folio"]??"[$row[fac_uuid]]", "cprv"=>$row["fac_cprv"], "rzsc"=>$row["fac_razsoc"], "fecha"=>$row["fac_fecha"]??"", "xml"=>$xmlLink, "pdf"=>$pdfLink, "total"=>$row["fac_total"]??"", "moneda"=>$row["fac_mon"]??""];
            } else if (isset($row["ord_folio"][0])) {
                $prvId=$row["ord_iprv"]??"";
                $ruta=$row["ord_ruta"]??""; $pdf=$row["ord_pdf"]??"";
                if (isset($pdf[0])&&$row["ord_stp"]=="1"&&$row["ord_sim"]=="0"&&($esRealizaPago||$esSistemas)&&$key==="ParaPago") {
                    $pdf="ST_".$pdf; $pdfExt2="S3"; $breakId="$rowId";
                }
                $pdfLink=(isset($pdf[0])?getLink2Doc($ruta.$pdf,"pdf",$pdfExt2,$breakId):"");
                $reg=["tipo"=>"O", "folio"=>$row["ord_folio"], "cprv"=>$row["ord_cprv"], "rzsc"=>$row["ord_razsoc"], "fecha"=>$row["ord_fecha"]??"", "xml"=>"", "pdf"=>$pdfLink, "total"=>$row["ord_total"]??"", "moneda"=>$row["ord_mon"]??""];
            } else if (isset($row["ctr_folio"][0])) {
                $ctrFolio=$row["ctr_folio"];
                $prvId=$row["ctr_iprv"]??"";
                $ctrQry="";
                if ($row["ctr_sim"]==="0"&&($esRealizaPago||$esSistemas)&&$key==="ParaPago") {
                    $pdfExt2="S3";
                    $ctrQry="&st=S3";
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
            $encabezado=($key==="ConFactura")?",'SOLICITUD PAGADA $rowFolio'":"";
            $viewForm=($hasButtonToForm?"onclick=\"viewForm($rowId,'$rowFolio'{$encabezado});\"":"");
            if ($tipoFecha==="solicitud") $fechaInicio=substr($row["inicio"],0,10);
            else if ($tipoFecha==="factura") $fechaInicio=substr($reg["fecha"],0,10);
            $prc=+$row["proceso"];
            $stt=+$row["status"];
            $typ=$reg["tipo"];
            $nFl=$esSistemas?".1":""; // no file required
            $bgExists=isset($row["archivoAntecedentes"][0]);
            $bgBadge=($bgExists?"<div class='abs_se badge' title='Tiene Antecedentes'>+</div>":"");
            $authUso=$row["authUso"];
            $rechUso=$row["rechUso"];
            if ($stt>=SolicitudPago::STATUS_CANCELADA) {
                if (!isset($row["autoriza"][0]) || $anioMesInicio<$anioMesActual ) {
                    $authUso=0; $rechUso=0;
                }
            }
            if ($esSistemas) {
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
            if ($fechaInicio===$hoy) $rowClass=" class=\"bgwhite7\"";
?>
                <tr id="row<?=$rowId?>"<?=$rowClass?> folId="<?=$rowFolio?>" prcId="<?=$prc?>" sttId="<?=$stt?>" typId="<?=$typ.$nFl?>" gpoId="<?=$gpoId?>" prvId="<?=$prvId?>" auIs="<?=$authList?>" tot="<?= $reg["total"] ?>" mon="<?= $currMoneda ?>"><td class="sticky toLeft zIdx2<?=$bg0?>"><div class="wrap100 vAlignCenter<?= $hasButtonToForm?" btnLt bRad2 pointer":"" ?>" <?=$viewForm?>><?= $rowFolio.$bgBadge ?></div> <?= $folioCheck??"" ?></td>
                    <td title="<?= $row['empresa'] ?>"><div class="wid70px"><?= $alias ?></div></td>
                    <td title="<?= $reg['rzsc'] ?>"><div class="wid48px"><?= $reg["cprv"] ?></div></td>
                    <td><div class="wid100px lefted" title="<?= $reg["folio"] ?>"><span ondblclick="redirect2InvoiceReport();" class="pre"><?= "$typ $cutFolio" ?></span></div><div class="wid48px noprint"><?= "$reg[xml] $reg[pdf]" ?></div></td>
                    <td><div class="wid77px"><?= $fechaInicio ?></div></td>
                    <td><div class="wid77px"><?= substr($row["pago"],0,10) ?></div></td>
                    <td><div class="wid135px righted"><?= formatCurrency($reg["total"],$currMoneda).$viewMoneda ?></div></td>
                    <td><div class="wid100px" title="<?= $row["usuario_nombre"]??"" ?>"><?= $row["usuario"] ?></div></td>
<?php       
            echo "<!-- YMIni = $anioMesInicio, YMCur = $anioMesActual -->";
            $myStatus=getStatusName($stt,$prc,$key,$rowId,$rowFolio,$usos);
            if (isset($statusCheck[0])) {
                if (isset($myStatus[0])) $myStatus.=" ";
                $myStatus.=str_replace("%KEY%", "solid=\"$rowId\"", $statusCheck);
            }
            $fixBG=isset($myStatus[0])?$bg0:" basicBG";
            if ($key==="NoAutorizadas") {
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
                    <td colspan="2" class="sticky toRight zIdx1 bxslft<?=$bg0?>"><div class="wid240px centered"><?= $myStatus ?></div></td>
<?php       } else { 
                if (isset($motivo[0])) {
                    $statusTitle=" title=\"$motivo\"";
                    $fixBG=" bgblue0";
                    global $query;
                    clog2("QUERY: $query");
                } else $fixBG=$bg0;
?>
                    <td><div class="wid100px" title="<?= $row["autoriza_nombre"]??"" ?>"><?= $row["autoriza"] ?></div></td>
                    <td class="sticky toRight zIdx1 bxslft<?=$fixBG?>"<?=$statusTitle??""?>><div class="wid135px centered vAlignCenter"><?= $myStatus ?></div></td>
<?php       }
            $motivo=null; $statusTitle=null; ?>
                </tr>
<?php   } ?>
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
<?php   }
        if ($reenvioCorreoMasivo && $key==="NoAutorizadas") {
            $z=true; ?>
            <div class="abs_e righted pad1 inblock btnfsa hidden"><input type="button" class="massauth" value="Autoriza Elegidas" onclick="massAuth()"> <input type="button" class="remail" value="Reenviar Email" onclick="resendEmail()"></div>
<?php   } else if (($esRealizaPago||$esSistemas) && $key==="ParaPago") {
            $z=true; ?>
            <div class="abs_e righted pad1 inblock btnfsa hidden"><input type="button" class="boots" value="Genera Doc Pago" onclick="generaDoc()"><input type="button" class="boots marL4 marR1" value="Marcar Pagadas" onclick="pagoMultiple()"></div>
<?php   }
        if ($z) { ?>
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const dv=this.parentNode;dv.style.maxWidth=(1+dv.previousElementSibling.clientWidth)+'px';ekil(this);">
<?php   } ?>
        </div>
<?php
    }
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
