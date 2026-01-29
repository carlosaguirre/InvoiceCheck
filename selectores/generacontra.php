<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
$esAdmin=validaPerfil("Administrador");
$esSistemas=$esAdmin||validaPerfil("Sistemas");
$esCompras=validaPerfil("Compras");
$esComprasBasico = validaPerfil("Compras Basico");
$esDesarrollo = in_array(getUser()->nombre, ["admin","SISTEMAS","test"]);
$modificaConR=($esSistemas?true:modificacionValida("Contrarrecibo"));
$modificaProc=modificacionValida("Procesar"); // && $esPruebas;
if (!$modificaConR) {
    require_once "configuracion/finalizacion.php";
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
if (!$esSistemas) { // && $esCompras) {
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $ugObj->rows_per_page=0;

    $idEmpresas=$ugObj->getIdGroupByNames(getUser(), (!$esCompras&&$esComprasBasico)?"Compras Basico":"Compras", "vista");
    if (!empty($idEmpresas)) $esCompras=true;
    if (!empty($_POST["grupo"]) && !in_array($_POST['grupo'], $idEmpresas)) $_POST["grupo"]=null;
}
$esRechazante = validaPerfil("Rechaza Aceptadas");
$esBorraDoc = (validaPerfil("Elimina Documentos")||$esSistemas);
$esBloqueaEA = (validaPerfil("Bloquea Entrada Almacen")||$esSistemas);
$command = $_POST["command"]??"";
$baseData=["file"=>getShortPath(__FILE__)];
$gpoIdOpt = $_SESSION['gpoIdOpt']??[];
$gpoRFC2Id=$_SESSION['gpoRFC2Id']??[];
$prvIdOpt = $_SESSION['prvIdOpt']??[];
$prvCodigo2Id=$_SESSION['prvCodigo2Id']??[];
$cellClass="noApply middle";
if ($command==="Buscar") {
    global $invObj;
    if (!isset($invObj)) {
        require_once "clases/Facturas.php";
        $invObj = new Facturas();
    }
    $invObj->rows_per_page = 0;
    $invObj->clearOrder();
    $invObj->addOrder("rfcGrupo");
    $invObj->addOrder("codigoProveedor");
    $invObj->addOrder("fechaFactura");
    $where = "LOWER(tipoComprobante) in ('i','ingreso','e','egreso')";
    if (!isset($errMessage)) $errMessage = "";
    if (!empty($_POST["grupo"])) {
        if (isset($where[0])) $where .= " AND ";
        $gpoId=$_POST["grupo"];
        $gpoRfc=$gpoIdOpt[$gpoId]["rfc"]??"";
        $where .= "rfcGrupo='$gpoRfc'";
    } else if (isset($idEmpresas[0])) {
        $rfcEmpresas=[];
        foreach ($idEmpresas as $idx => $idGpo) {
            if (isset($gpoIdOpt[$idGpo]))
                $rfcEmpresas[$idx]=$gpoIdOpt[$idGpo]["rfc"]??"";
        }
        if (isset($where[0])) $where .= " AND ";
        if (count($rfcEmpresas)>0) {
            $where .="rfcGrupo in ('".implode("','", $rfcEmpresas)."')";
        } else {
            $where .="rfcGrupo=''";
        }
    }
    if (!empty($_POST["proveedor"])) {
        if (isset($where[0])) $where .= " AND ";
        $prvId=$_POST["proveedor"];
        $prvCode=$prvIdOpt[$prvId]["codigo"]??"";
        $where .= "codigoProveedor='$prvCode'";
    }
    if (!empty($_POST["fechaInicio"]) && !empty($_POST["fechaFin"])) {
        list($fDay,$fMon,$fYr) = sscanf($_POST["fechaInicio"], "%2d/%2d/%4d");
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
    if (empty($errMessage)) {
        if (isset($where[0])) $where .= " AND ";
        $where .= "fechaFactura BETWEEN '$fechaIni 00:00:00' AND '$fechaFin 23:59:59'";
        if (isset($where[0])) $where .= " AND ";
        //if($esPruebas)
            $where.="statusn is not NULL";
        //else $where .= "statusn&1=1";
        $where .= " AND statusn&2=0 AND statusn<128";
        $where .= " AND id not in (select distinct s.idFactura from solicitudpago s where s.idFactura is not null and s.idOrden is null and s.status!=3 and s.modifiedTime>fechaFactura)";
        clog2("Where: $where");
        $invData = $invObj->getData($where);
        $dataIndex = 0;
        $oldIdGpo=null;
        $oldIdPrv=null;
        $partCount=0;
        $chkdCount=0;
        $blockCount=0;
        //$partSum=[];
        $chkdSum=[];
        $allSum=[];
        $invNum=count($invData);
        $invPre=0;
        require_once "clases/CatLista69B.php";
        $invIds=[];
        foreach ($invData as $idx=>$row) {
            $invId=$row["id"];
            $invIds[]=$invId;
            $rfcGrupo=$row["rfcGrupo"];
            $codProv=$row["codigoProveedor"];
            if (!isset($gpoRFC2Id[$rfcGrupo])) {
                doclog("Factura en empresa deshabilitada. Habilite empresa (rfc='$rfcGrupo')");
                $invNum--;
                continue;
            }
            $idGpo=$gpoRFC2Id[$rfcGrupo]??-1;
            if ($idGpo<0 || !isset($gpoIdOpt[$idGpo])) {
                doclog("Factura en empresa deshabilitada. Habilite empresa (".($idGpo>=0?"id='$idGpo', ":"")."rfc='$rfcGrupo')");
                $invNum--;
                continue;
            }
            $gpoOpt=$idGpo<0?["codigo"=>"NO ENCONTRADO","razon"=>""]:($gpoIdOpt[$idGpo]??["codigo"=>"NO ENCONTRADO","razon"=>""]);

            $idPrv=$prvCodigo2Id[$codProv]??-1; $prvOpt=$idPrv<0?["codigo"=>"NO ENCONTRADO","razon"=>""]:($prvIdOpt[$idPrv]??["codigo"=>"NO ENCONTRADO","razon"=>""]);
            if (isset($prvOpt["rfc"]) && CatLista69B::estaMarcado($prvOpt["rfc"])) {
                doclog("Proveedor $codProv encontrado en Lista 69B");
                $invNum--;
                continue;
            }

            $statusn=+$row["statusn"];
            $isS1=!!($statusn&1);
            $procImg="viewer96";
            $verChng="";
            if ($modificaProc&&($statusn<Facturas::STATUS_PROGPAGO||$esSistemas)) {
                $procImg=$esDesarrollo?"process200h":"process96";
                $verChng=",true";
            } else if (!$isS1) {
                doclog("Usuario sin permiso de generar contrarrecibo en facturas pendientes");
                $invNum--;
                continue;
            }

            $dataIndex++;
            $xmlfilepath = $row['ubicacion'].$row['nombreInterno'];
            $pdffilepath = str_replace("xml", "pdf", $xmlfilepath);
            $paddedDataIndex = str_pad($dataIndex, 3, "0", STR_PAD_LEFT);
            //clog2("GPO: $rfcGrupo, ID: $idGpo");
            //clog2("PRV: $codProv, ID: $idPrv");
            $moneda=$row["moneda"]; if (!isset($moneda)) $moneda="MXN";
            $viewMoneda="<span class=\"curr_codeb\">$moneda</span>";
            if (!isset($oldIdGpo)) $oldIdGpo=$idGpo;
            if (!isset($oldIdPrv)) $oldIdPrv=$idPrv;
            $total = +$row['total']; $viewTotal = number_format($row['total'],2);
            $comprobante = $row['tipoComprobante'];
            $tc = strtoupper($comprobante[0]);
            if ($tc==="I") $tc="F";
            else if ($tc==="E") $tc="NC";
            $folio = trim($row['folio']??"");
            $uuid = trim($row['uuid']??"");
            $uuidx = substr($row["uuid"], -10);
            $isFolio0 = isset($folio[0]);
            $isFolio10 = isset($folio[10]);
            $folio10 = $isFolio10?substr($folio,-10):$folio;
            $folioTtl = $isFolio10?" title=\"$folio\"":($isFolio0?"":" title=\"Sin folio\"");
            $folioTxC = $isFolio10?"…".$folio10:($isFolio0?$folio:"[{$uuidx}]");
            $ccpCell="<img src='imagenes/pt.png' width='22px' height='20px'><img src='imagenes/icons/{$procImg}.png' width='20px' height='20px' class='pointer wid20px hei20 lightOver abs_e vAlignCenter' onclick='verificaFactura($invId{$verChng});'>";
            $chkd=$isS1?" checked":"";
            $chkcn="checkInvoice nomargini".($isS1?"":" pnd");
            $cnred=$isS1?"redden":"reddish";
            $colclass = "";
            if ($tc==="NC") {
                $viewTotal = "(".$viewTotal.")";
                $total = -$total;
                $colclass = " $cnred";
            } $viewTotal .= $viewMoneda;
            if (!isset($allSum[$moneda])) $allSum[$moneda]=0;
            $allSum[$moneda]+=$total;
            $rowClass="facturasAceptadas";
            $divClass0="inblock lnh12 ellipsis";
            $divClass1="inblock lnh12";
            $fecha=substr($row["fechaFactura"], 0, 10);
            if ($idGpo!==$oldIdGpo || $idPrv!==$oldIdPrv) {
                $blockCount++;
                if ($partCount>1) {
                    $oldGpoOpt=$gpoIdOpt[$oldIdGpo]??["codigo"=>"NO ENCONTRADO","razon"=>""]; $oldPrvOpt=$prvIdOpt[$oldIdPrv]??["codigo"=>"NO ENCONTRADO","razon"=>""];
                    $chkdSumTxt="";
                    if (!empty($chkdSum)) {
                        $viewChkdSum=""; ksort($chkdSum);
                        foreach ($chkdSum as $mon => $monto) {
                            if (isset($chkdSumTxt[0])) $chkdSumTxt.=",";
                            $chkdSumTxt.=$mon.":".$monto;
                            if ($monto<0) { $oldclass=" redden"; $oPre="("; $oPos=")"; $monto*=-1;
                            } else { $oldclass=""; $oPre=""; $oPos=""; }
                            $viewChkdSum .= "<p class='nomargin{$oldclass}'>$".$oPre.number_format($monto,2).$oPos.$mon."</p>";
                        }
                    } else $viewChkdSum = " - ";
                    $blkSumClass="btop2blu bgbrown1 blockSummary".($chkdCount>0?"":" hidden");
                    $allBlkChkd=($chkdCount==$partCount); ?>
          <tr class="<?=$blkSumClass?>">
            <td class="noApply righted partNo shrinkCol" id="block<?=$blockCount?>" all="<?=$partCount?>" num="<?=$chkdCount?>">#<?=$chkdCount?></td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?> maxWid140 ellipsisCel" title="<?="$oldPrvOpt[codigo] : $oldPrvOpt[razon]"?>"><?="$oldPrvOpt[razon]"?></td>
            <td class="<?=$cellClass?> shrinkCol"><?=ucfirst($oldGpoOpt["codigo"])?></td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?>">&nbsp;</td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?> blkSum shrinkCol righted" id="blockSum<?=$blockCount?>" sum="<?=$chkdSumTxt?>" mon="<?=$moneda?>"><?= $viewChkdSum ?></td>
            <td class="noApply" colspan="2"></td>
            <td class="<?=$cellClass?> shrinkCol"><input type="radio" id="blkchk<?= $blockCount ?>" name="blkchk[<?= $blockCount ?>]" value="1" onclick="toggleChk(this);" block="<?=$blockCount?>" class="nomargini" tabindex="-1"<?=$allBlkChkd?" checked=\"true\" previous=\"true\"":""?>></td>
          </tr><!-- END ROW -->
<?php
                }
                $rowClass.=" btop2dblu";
                $oldIdGpo=$idGpo;
                $oldIdPrv=$idPrv;
              //$partSum = [];
                $chkdSum = [];
                $partCount=0;
                $chkdCount=0;
            }
            if ($partCount==0 && (!isset($invData[$idx+1]) || $rfcGrupo!==$invData[$idx+1]["rfcGrupo"] || $codProv!==$invData[$idx+1]["codigoProveedor"])) {
                $rowClass.=" partial singlePart ".($isS1?"bggreenish":"grayed bgblack");
            } else $rowClass.=" partial fractionPart ".($isS1?"bggreenlt":"grayed bglightgray1");
          //if (!isset($partSum[$moneda])) $partSum[$moneda]=0;
          //$partSum[$moneda]+=$total;
            if ($invNum!==$invPre) {
                $loadImg="<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"ebyid('dialog_tbody').inv={$invNum};ekil(this);\">";
                $invPre=$invNum;
            }
?>
          <tr class="<?=$rowClass?>" name="fila">
            <!-- fila de factura aceptada -->
            <td class="<?=$cellClass?> shrinkCol"><?= $dataIndex ?><?=$loadImg??""?></td>
            <td class="<?=$cellClass?> shrinkCol maxWid80" title="<?=$row['fechaFactura']?>"><?= $fecha ?></td>
            <td class="<?=$cellClass?> maxWid140 ellipsisCel" title="<?="$prvOpt[codigo] : $prvOpt[razon]"?>"><?= "$prvOpt[razon]" ?></td>
            <td class="<?=$cellClass?> shrinkCol"><?= ucfirst($gpoOpt["codigo"]) ?></td>
            <td class="<?=$cellClass?> shrinkCol centered"><?= $tc ?></td>
            <td class="<?=$cellClass?> shrinkCol"><div class="relative"><span class="vAlignCenter"<?=$folioTtl?>><?=$folioTxC?></span><?=$ccpCell?></div></td>
            <td class="<?=$cellClass?> shrinkCol" id="pdd<?=$invId?>"><?= $row['pedido'] ?></td>
            <td class="<?=$cellClass?> shrinkCol righted<?= $colclass ?>">$<?= $viewTotal ?></td>
<?php
            $ea=$row["ea"];
            $eafolio=$isFolio0?$folio10:$uuidx;
            $modEA=false;
            if ($ea==1) {
                $eaIdBtn1="openEA";
                $eaNmImg1="pdfadobeicon2.png";
                $eaTitle1="Abrir Entrada de Almacén";
                $eaExtra1=" pointer";
                if ($esBorraDoc) {
                    $modEA=true;
                    $eaIdBtn2="delEA";
                    $eaNmImg2="deleteIcon20.png";
                    $eaTitle2="Eliminar Entrada de Almacén";
                }
                $eaUrl=" url=\"".$row["ubicacion"]."EA_".trim(str_replace("-","",$codProv))."_";
                $eaUrl.=$eafolio."_".substr(trim(str_replace("-","",$fecha)),2,6).".pdf\"";
            } else {
                $eaIdBtn1="addEA";
                $eaNmImg1="upRed.png";
                $eaTitle1="Anexar Entrada de Almacén";
                $deshabilitado=($ea==-1);
                $eaExtra1=$deshabilitado?" disabled grayscale":" pointer";
                if ($esBloqueaEA) {
                    $modEA=true;
                    $eaIdBtn2="disEA";
                    $eaNmImg2=($deshabilitado)?"siAplica.png":"noAplica.png";
                    $eaTitle2=(($deshabilitado)?"H":"Desh")."abilitar Entrada de Almacén";
                }
                $eaUrl="";
            }
?>
            <td class="<?=$cellClass?> shrinkCol" ea="<?=$ea?>"><img src="imagenes/icons/<?=$eaNmImg1?>" id="<?=$eaIdBtn1.$invId?>" class="btnLt btn20<?=$eaExtra1??""?>" title="<?=$eaTitle1?>"<?= $eaUrl ?> onclick="<?=$eaIdBtn1."f($invId,'$eafolio');"?>"><?php if ($modEA) { ?><img src="imagenes/icons/<?=$eaNmImg2?>" id="<?=$eaIdBtn2.$invId?>" class="btnLt btn20 marL4 pointer" title="<?=$eaTitle2?>" onclick="<?=$eaIdBtn2."f($invId,'$eafolio');"?>"><?php } ?></td>
            <td class="<?=$cellClass?> shrinkCol" id="stt<?=$invId?>"><?= $row['status'] ?></td>
            <td class="<?=$cellClass?> shrinkCol"><input type="checkbox" name="check[<?= $blockCount ?>][<?= $partCount ?>]" value="<?= $invId ?>" id="chk<?=$invId?>" onclick="toggleChk(this);" total="<?=$total?>" mon="<?=$moneda?>" block="<?=$blockCount?>" sttn="<?=$statusn?>" class="<?=$chkcn?> blkchk<?=$blockCount+1?>" tabindex="-1"<?=$chkd?>>
            </td>
          </tr><!-- END ROW -->
<?php
            $partCount++;
            if ($isS1) {
                $chkdCount++;
                if (!isset($chkdSum[$moneda])) $chkdSum[$moneda]=0;
                $chkdSum[$moneda]+=$total;
            }
        }
        $blockCount++;
        global $query;
        doclog("GeneraContra browse result","read",["query"=>$query,"resultIds"=>$invIds]); // CASO: LUISM quiere cancelar una factura pero se cancela otra. Se ajusta un campo en consultas/verificaFactura que dejaba el primer Id consultado cuando se volvia a abrir
        if ($partCount>1) {
            $oldGpoOpt=$gpoIdOpt[$oldIdGpo]; $oldPrvOpt=$prvIdOpt[$oldIdPrv];
          //$partSumTxt="";
          //if (!empty($partSum)) {
          //    $viewPartSum=""; ksort($partSum);
          //    foreach ($partSum as $mon => $monto) {
          //        if (isset($partSumTxt[0])) $partSumTxt.=",";
          //        $partSumTxt.=$mon.":".$monto;
          //        if ($monto<0) { $oldclass=" redden"; $oPre="("; $oPos=")"; $monto*=-1;
          //        } else { $oldclass=""; $oPre=""; $oPos=""; }
          //        $viewPartSum .= "<p class='nomargin{$oldclass}'>$".$oPre.number_format($monto,2).$oPos.$mon."</p>";
          //    }
          //} else $viewPartSum = " - ";
            $chkdSumTxt="";
            if (!empty($chkdSum)) {
                $viewChkdSum=""; ksort($chkdSum);
                foreach ($chkdSum as $mon => $monto) {
                if (isset($chkdSumTxt[0])) $chkdSumTxt.=",";
                    $chkdSumTxt.=$mon.":".$monto;
                    if ($monto<0) { $oldclass=" redden"; $oPre="("; $oPos=")"; $monto*=-1;
                    } else { $oldclass=""; $oPre=""; $oPos=""; }
                    $viewChkdSum .= "<p class='nomargin{$oldclass}'>$".$oPre.number_format($monto,2).$oPos.$mon."</p>";
                }
            } else $viewChkdSum = " - ";
            $blkSumClass="btop2blu bgbrown1 blockSummary".($chkdCount>0?"":" hidden");
            $allBlkChkd=($chkdCount==$partCount); ?>
          <tr class="<?=$blkSumClass?>">
            <td class="noApply righted partNo shrinkCol" id="block<?=$blockCount?>" all="<?=$partCount?>" num="<?=$chkdCount?>">#<?=$chkdCount?></td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?> maxWid140 ellipsisCel" title="<?="$oldPrvOpt[codigo] : $oldPrvOpt[razon]"?>"><?="$oldPrvOpt[razon]"?></td>
            <td class="<?=$cellClass?> shrinkCol"><?=ucfirst($oldGpoOpt["codigo"])?></td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?>">&nbsp;</td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?> blkSum shrinkCol righted" id="blockSum<?=$blockCount?>" sum="<?=$chkdSumTxt?>" mon="<?=$moneda?>"><?= $viewChkdSum ?></td>
            <td class="noApply" colspan="2"></td>
            <td class="<?=$cellClass?> shrinkCol"><input type="radio" id="blkchk<?= $blockCount ?>" name="blkchk[<?= $blockCount ?>]" value="1" onclick="toggleChk(this);" block="<?=$blockCount?>" class="nomargini" tabindex="-1"<?=$allBlkChkd?" checked=\"true\" previous=\"true\"":" previous=\"false\""?>></td>
          </tr><!-- END ROW -->
<?php
        }
        if (!empty($allSum)) {
            $viewAllSum=""; ksort($allSum);
            foreach ($allSum as $mon => $monto) { // moneda => monto
                if ($monto<0) { $allclass=" redden"; $aPre="("; $aPos=")"; $monto*=-1;
                } else { $allclass=""; $aPre=""; $aPos=""; }
                $viewAllSum .= "<p class='nomargin{$allclass}'>$".$aPre.number_format($monto,2).$aPos.$mon."</p>";
            }
        } else $viewAllSum = " - "; $bcP=($blockCount!=1); $bcPs=($bcP?"s":""); $bcPes=($bcP?"es":"");
?>
          <tr class="btop2dblu bgblue fullSummary">
            <td class="noApply lefted" colspan="4"><?= "" ?></td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?>"><?= "" ?> <?php /*'#'.$dataIndex*/ ?></td>
            <td class="noApply"></td>
            <td class="<?=$cellClass?> shrinkCol righted"><?= "" ?></td>
            <td class="noApply" colspan="3"><input type="hidden" name="blockCount" id="blockCount" value="<?=$blockCount?>" all="<?=$dataIndex?>"></td>
          </tr><!-- END ROW -->
<?php
    }
} else if ($command==="Generar") { // solicitudpago
    global $query, $invObj;
    if (!isset($invObj)) {
        require_once "clases/Facturas.php";
        $invObj = new Facturas();
    }
    $invObj->rows_per_page = 0;
    $contraReciboStatus=Facturas::actionToStatusN("Contrarrecibo");
    $check=$_POST["check"]??[];
    if (!isset($errMessage)) $errMessage = "";
    $errList=[];
    $suma=[];
    foreach ($check as $crIdx => $cfList) {
        $invIdLst=array_values($cfList);
        if (!isset($invIdLst[0])) {
            if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
            $errList[$crIdx].="Lista de facturas vacía";
            doclog("Lista de facturas vacía","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"cfList"=>$cfList]);
            continue;
        }
        $isSingleInvoice=!isset($invIdLst[1]);
        $invIdLstStr=implode(",", $invIdLst);
        $invData=$invObj->getData("id in ({$invIdLstStr})");
        $invQuery=$query;
        $rfcGrp="";
        $codPrv="";
        $monCR="";
        $crNum=false;
        $idCR=null;
        $times=5;
        $countCF=0;
        DBi::autocommit(false);
        foreach ($invData as $invIdx=>$row) {
            $rowId=$row["id"];
            $rowFolio=$row["folio"];
            if (!isset($rowFolio[0])) $rowFolio="[".substr($row["uuid"],-10)."]";
            if (!isset($rfcGrp[0])) {
                $rfcGrp=$row["rfcGrupo"];
                $idGpo=$gpoRFC2Id[$rfcGrp]??-1;
                $gpoOpt=$idGpo<0?["codigo"=>"NO ENCONTRADO","razon"=>""]:($gpoIdOpt[$idGpo]??["codigo"=>"NO ENCONTRADO","razon"=>""]);
                $alias=$gpoOpt["codigo"];
            } else if ($rfcGrp!==$row["rfcGrupo"]) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="La factura $rowFolio tiene empresa diferente";
                doclog("Factura con empresa diferente","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"query"=>$invQuery,"rowId"=>$rowId,"rfcGrp"=>$rfcGrp,"rowRfcGrupo"=>$row["rfcGrupo"]]);
                break;
            }
            if (!isset($codPrv[0])) {
                $codPrv=$row["codigoProveedor"];
                $idPrv=$prvCodigo2Id[$codPrv]??-1;
                $prvOpt=$idPrv<0?["codigo"=>"NO ENCONTRADO","razon"=>""]:($prvIdOpt[$idPrv]??["codigo"=>"NO ENCONTRADO","razon"=>""]);
                $fechaRevision=date("Y-m-d H:i:s");
                if (!isset($prvObj)) {
                    global $prvObj;
                    if (!isset($prvObj)) {
                        require_once "clases/Proveedores.php";
                        $prvObj = new Proveedores();
                    }
                    $prvObj->rows_per_page = 0;
                }
                $prvData=$prvObj->getData("id=$idPrv",0,"credito");
                $credito=+$prvData[0]["credito"]??0;
                require_once "clases/Contrarrecibos.php";
                $fechaVencimiento=Contrarrecibos::getFechaVencimiento($fechaRevision, $credito);
            } else if ($codPrv!==$row["codigoProveedor"]) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="La factura $rowFolio tiene proveedor diferente";
                doclog("Factura con proveedor diferente","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"query"=>$invQuery,"rowId"=>$rowId,"codPrv"=>$codPrv,"rowCodigoProveedor"=>$row["codigoProveedor"]]);
                break;
            }
            $moneda=$row["moneda"]; if (!isset($moneda)) $moneda="MXN";
            if (!isset($monCR[0])) {
                $monCR=$moneda;
            } else if ($monCR!==$moneda) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="La factura $rowFolio tiene diferente moneda a la anterior";
                doclog("Factura con diferente moneda","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"query"=>$invQuery,"rowId"=>$rowId,"monCR"=>$monCR,"rowMoneda"=>$moneda]);
                break;
            }
            if (!isset($solObj)) {
                global $solObj;
                if (!isset($solObj)) {
                    require_once "clases/SolicitudPago.php";
                    $solObj = new SolicitudPago();
                }
                $solObj->rows_per_page = 0;
            }
            $solData=$solObj->getData("idFactura=$rowId",0,"id,idOrden,idAutoriza,proceso");
            $proceso=-1;
            $idSolAutoriza=-1;
            //if ($solObj->exists("idFactura=$rowId and idOrden is null")) {
            if (isset($solData[0]["id"])) {
                $proceso=+$solData[0]["proceso"];
                $idSolAutoriza=+($solData[0]["idAutoriza"]??0);
                $idOrden=$solData[0]["idOrden"]??"";
                if (!isset($idOrden[0])||$proceso<4) {
                    if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                    $errList[$crIdx].="La factura $rowFolio ya está en una solicitud de pago";
                    doclog("Factura con solicitud","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"query"=>$query,"rowId"=>$rowId]);
                    break;
                }
                if (isset($invData[1])) {
                    // La factura no se incluye en el contra recibo, pero no se genera error para no afectar la generacion del contra recibo con las demás facturas
                    //if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                    //$errList[$crIdx].="La factura $rowFolio no fue incluida en contra recibo por haber más facturas relacionadas";
                    doclog("Factura con solicitud pagada no es única","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"query"=>$query,"rowId"=>$rowId]);
                    continue;
                }
            }
            $numStatus = +$row["statusn"];
            if (Facturas::estaContrarrecibo($numStatus)) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="La factura $rowFolio ya tiene contra recibo";
                doclog("Generación de contra recibo fallida. La factura tiene status contra recibo.","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"statusn"=>$numStatus,"query"=>$query,"rowId"=>$rowId]);
                break;
            }
            if (!isset($ctfObj)) {
                global $ctfObj;
                if (!isset($ctfObj)) {
                    require_once "clases/Contrafacturas.php";
                    $ctfObj=new Contrafacturas();
                }
                $ctfObj->rows_per_page = 0;
            }
            if ($ctfObj->exists("idFactura=$rowId")) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="La factura $rowFolio ya tiene contra recibo";
                doclog("Generación de contra recibo fallida. La factura está en contrafacturas","contrarrecibo",$baseData+["line"=>__LINE__,"crIdx"=>$crIdx,"query"=>$query,"rowId"=>$rowId]);
                break;
            }
            while($crNum===false) {
                if (!isset($ctrObj)) {
                    global $ctrObj;
                    if (!isset($ctrObj)) {
                        require_once "clases/Contrarrecibos.php";
                        $ctrObj=new Contrarrecibos();
                    }
                    $ctrObj->rows_per_page = 0;
                }
                $crNum = $ctrObj->getNextFolio($alias);
                if ($crNum===false) {
                    if ($times>0) {
                        $times--;
                        doclog("Generación de folio de contra recibo fallido con reintento","contrarrecibo",$baseData+["line"=>__LINE__,"invId"=>$rowId,"try"=>$times,"time"=>date("H:i:s",microtime(true)),"query"=>$query]);
                    } else {
                        if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                        $errList[$crIdx].="Falló la generación de contra recibo";
                        doclog("Generación de folio de contra recibo fallido","contrarrecibo",$baseData+["line"=>__LINE__,"invId"=>$rowId,"try"=>$times,"time"=>date("H:i:s",microtime(true)),"query"=>$query]);
                        break 2;
                    }
                }
                usleep(rand(250, 248750));
                if ($ctrObj->exists("aliasGrupo='$alias' and folio='$crNum'")) {
                    if ($times>0) {
                        $times--;
                        doclog("Generación de folio de contra recibo fallido con reintento", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$rowId, "aliasGpo"=>$alias, "folioCR"=>$crNum, "try"=>$times, "time"=>date("H:i:s",microtime(true)), "query"=>$query]);
                        $crNum=false;
                    } else {
                        if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                        $errList[$crIdx].="Falló la generación de contra recibo";
                        doclog("Generación de folio de contra recibo fallido", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$rowId, "aliasGpo"=>$alias, "folioCR"=>$crNum, "try"=>$times, "time"=>date("H:i:s",microtime(true)), "query"=>$query]);
                        break 2;
                    }
                }
            }
            if ($idCR===null) {
                if ($crNum===false) {
                    if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                    $errList[$crIdx].="Falló la generación de contra recibo";
                    doclog("Generación de contra recibo invalidada", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$rowId, "aliasGpo"=>$alias]);
                    break;
                }
                if (isset($errList[$crIdx][0])) {
                    doclog("Generación de contra recibo invalidada", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$rowId, "aliasGpo"=>$alias, "errlist"=>$errList[$crIdx]]);
                    break;
                }
                $ctrFldArr=[
                    "folio"=>$crNum,
                    "codigoProveedor"=>$codPrv,
                    "razonProveedor"=>$prvOpt["razon"],
                    "rfcGrupo"=>$rfcGrp,
                    "razonGrupo"=>$gpoOpt["razon"],
                    "aliasGrupo"=>$alias,
                    "fechaRevision"=>$fechaRevision,
                    "credito"=>$credito,
                    "fechaPago"=>$fechaVencimiento,
                    "total"=>0
                ];
                if (!$ctrObj->saveRecord($ctrFldArr)) {
                    if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                    $errList[$crIdx].="Error al guardar contrarrecibo $alias-$crNum";
                    doclog("Error al guardar contra recibo", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$cfList, "aliasGpo"=>$alias, "folioCR"=>$crNum, "query"=>$query, "errno"=>DBi::getErrno(), "error"=>DBi::getError()]);
                    break;
                }
                $idCR=$ctrObj->lastId;
            }
            $retencion = +$row['impuestoRetenido'];
            $total = +$row['total'];
            $subtotal = +$row['subtotal'];
            $comprobante = $row['tipoComprobante'];
            $tc=strtoupper($comprobante[0]);
            if ($tc==="E") {
                $total = -$total;
                $subtotal = -$subtotal;
            }
            if (!isset($suma[$crIdx])) $suma[$crIdx]=[];
            if (!isset($suma[$crIdx][$monCR])) $suma[$crIdx][$monCR]=0;
            $suma[$crIdx][$monCR]+=$total;
            if (!isset($suma["total"])) $suma["total"]=[];
            if (!isset($suma["total"][$monCR])) $suma["total"][$monCR]=0;
            $suma["total"][$monCR]+=$total;
            $venceFecha=$ctrObj->getFechaVencimiento($row["fechaCaptura"],$credito,1);
            $ctfFldArr=[
                "idContrarrecibo"=>$idCR,
                "idFactura"=>$rowId,
                "pedido"=>$row["pedido"],
                "folioFactura"=>$rowFolio,
                "serieFactura"=>$row["serie"],
                "fechaFactura"=>$row["fechaFactura"],
                "fechaCaptura"=>$row["fechaCaptura"],
                "metodoDePago"=>$row["metodoDePago"],
                "tipoComprobante"=>$comprobante,
                "nombreInterno"=>$row["nombreInterno"],
                "ubicacion"=>$row["ubicacion"],
                "subtotal"=>$subtotal,
                "total"=>$total,
                "retencion"=>$retencion,
                "moneda"=>$moneda,
                "fechaVencimiento"=>$venceFecha
            ];
            if (isset($idOrden) && $idOrden>0 && $proceso==4) {
                $ctfFldArr["autorizadaPor"]=$idSolAutoriza;
                //$ctrFldArr["numAutorizadas"]=1;
                //$ctrFldArr["numContraRegs"]=1;
            }
            if (!$ctfObj->saveRecord($ctfFldArr)) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="Error al guardar factura $rowFolio en contrarrecibo $alias-$crNum";
                doclog("Error al guardar contra recibo", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$rowId, "aliasGpo"=>$alias, "folioCR"=>$crNum, "query"=>$query, "errno"=>DBi::getErrno(), "error"=>DBi::getError()]);
                break;
            }
            $newNumStatus=$numStatus|$contraReciboStatus;
            $newStatus = Facturas::statusnToStatus($numStatus);
            $invFldArr=["id"=>$rowId,"fechaVencimiento"=>$venceFecha,"statusn"=>$newNumStatus,"status"=>$newStatus];
            //OBS: No corresponde aquí. tieneOrden debe asignarse cuando se liga una factura a una solicitud creada por orden de compra
            //if (isset($idOrden) && $idOrden>0 && $proceso==4) {
            //    $invFldArr["tieneOrden"]=1;
            //}
            if (!$invObj->saveRecord($invFldArr)) {
                if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                $errList[$crIdx].="Error al actualizar factura";
                doclog("Error al guardar factura con status y fecha vencimiento", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$rowId, "aliasGpo"=>$alias, "query"=>$query, "errno"=>DBi::getErrno(), "error"=>DBi::getError()]);
                break;
            }
            $countCF++;
            if ($newNumStatus!=$numStatus) {
                if (!isset($prcObj)) {
                    global $prcObj;
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj=new Proceso();
                    }
                    $prcObj->rows_per_page = 0;
                }
                $prcObj->cambioFactura($rowId,$newStatus,getUser()->nombre,false,"generacontra ({$numStatus}+{$contraReciboStatus}=$newNumStatus)");
            }
        }
        if (isset($errList[$crIdx][0])) DBi::rollback();
        else {
            $sumCR=$suma[$crIdx][$monCR];
            if ($sumCR != 0) {
                $ctrFldArr = [ "id"=>$idCR, "total"=>$sumCR, "numContraRegs"=>$countCF ];
                if (isset($idOrden) && $idOrden>0 && $proceso==4) {
                    $ctrFldArr["numAutorizadas"]=1;
                }
                if (!$ctrObj->saveRecord($ctrFldArr)) {
                    if (isset($errList[$crIdx][0])) $errList[$crIdx].="<br>"; else $errList[$crIdx]="";
                    $errList[$crIdx].="Error al guardar contra recibo $alias-$crNum";
                    doclog("Error al guardar contra recibo", "contrarrecibo", $baseData+["line"=>__LINE__, "invId"=>$cfList, "crIdx"=>$crIdx, "moneda"=>$monCR, "aliasGpo"=>$alias, "folioCR"=>$crNum, "query"=>$query, "errno"=>DBi::getErrno(), "error"=>DBi::getError(),"suma"=>$suma,"log"=>$ctrObj->log]);
                    DBi::rollback();
                    DBi::autocommit(true);
                    continue;
                }
            }
            DBi::commit();
            $fecha=substr($fechaRevision, 0, 10);
            if ($sumCR<0) {
                $sumCR=-$sumCR;
                $colclass=" redden";
                $viewTotal = "(".number_format($sumCR,2).")";
            } else $viewTotal = number_format($sumCR,2);
            $viewTotal.="<span class=\"curr_codeb\">$monCR</span>";
            // no se calcula token pues se acaba de crear y no tiene autorizadas, ni solicitud
?>
          <tr class="contragenerado" name="fila">
            <td class="noApply middle"><?= ($crIdx+1) ?></td>
            <td class="<?=$cellClass?> nowrap maxWid80" title="<?=$fechaRevision?>"><?= $fecha ?></td>
            <td class="<?=$cellClass?> maxWid140 ellipsisCel" title="<?="$prvOpt[codigo] : $prvOpt[razon]"?>"><?= "$prvOpt[razon]" ?></td>
            <td class="<?=$cellClass?>"><?= ucfirst($gpoOpt["codigo"]) ?></td>
            <td class="<?=$cellClass?> nowrap"><?= $crNum ?></td>
            <td class="<?=$cellClass?> nowrap righted<?= $colclass??"" ?>">$<?= $viewTotal ?></td>
            <td class="noApply middle"><a href="consultas/Contrarrecibos.php?folio=<?= "$alias-$crNum" ?>"><img src="imagenes/icons/crDoc32.png"></a></td>
          </tr><!-- END ROW -->
<?php
            flush_buffers();
        }
        DBi::autocommit(true);
    }
    DBi::rollback();
    DBi::autocommit(true);
    if (!empty($errList)) {
        $successNum=(isset($suma["total"])?count($suma)-1:0);
        foreach ($errList as $idx => $msg) {
            $errMessage.="<LI>$msg</LI>";
        }
        if (!empty($errMessage)) {
            echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"ulErr('$errMessage',$successNum); this.parentNode.removeChild(this);\">";
        }
    }
}
function flush_buffers($doStart=true) {
    ob_end_flush();
    if (ob_get_level()>0) ob_flush();
    flush();
    if ($doStart) ob_start();
}
require_once "configuracion/finalizacion.php";
