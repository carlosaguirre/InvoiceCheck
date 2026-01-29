<?php
if (!$hasRecord) {
clog2ini("templates.viajero");
clog1seq(1); ?>
<DIV id="area_general" class="central">
  <h1 class="txtstrk relative">Captura de Reembolso de Viáticos<a target="manual" class="abs rgt5 top8 noprint" href="manual/<?= $esSolicitante?"viaticos":"manualReembolso" ?>.pdf"><img src="imagenes/icons/assistance.png" class="top hoverable" title="Descargar Manual de Reembolso de Viáticos y Caja Chica"/></a></H1>
  <DIV id="area_detalle" class="<?= $_REQUEST["area_detalle"]??"scroll-60" ?> relative">
    <DIV class="sticky toTop centered basicBG zIdx1000 noprint">
<?php if ($puedeEditar) { ?>
      <SPAN id="nuevo_registro_btn" class="btnTab<?=$esSolicitante?" selected":""?>" onclick="showBlock('nuevo_registro','nbeneficiario');">NUEVO REGISTRO</SPAN>
<?php } ?>
      <SPAN id="registro_actual_btn" class="btnTab<?=$esSolicitante?"":" selected"?>" onclick="showBlock('registro_actual','regId');">BUSCAR REGISTRO</SPAN>
<?php if ($puedeEditar) { ?>
      <SPAN id="nuevo_concepto_btn" class="btnTab hidden" onclick="if (!this.disabled) showBlock('nuevo_concepto','nconcepto');">NUEVO CONCEPTO</SPAN>
<?php } ?>
      <SPAN id="modifica_concepto_btn" class="btnTab hidden" onclick="showBlock('modifica_concepto','concepto');">CAMBIAR CONCEPTO</SPAN>
    </DIV>
<?php if ($puedeEditar) { ?>
      <TABLE id="nuevo_registro" class="centered screen tabla_viaticos fontMedium noprint<?=$esSolicitante?"":" hidden"?>">
        <TBODY class="lefted">
        <TR><TH class="nowrap">BENEFICIARIO:</TH>
            <TD><INPUT type="text" id="nbeneficiario" class="nombreV padv02"/></TD>
            <TH class="nowrap">EMPRESA:</TH>
            <TD><SELECT id="nempresa" class="empresaV padv02"><OPTION></OPTION><?= $groupOptions ?></SELECT></TR>
        <TR><TH class="nowrap">BANCO:</TH>
            <TD><INPUT type="text" id="nbanco" class="nombreV padv02"/></TD>
            <TH class="nowrap">CUENTA BANCARIA:</TH>
            <TD><INPUT type="text" id="ncuentabanco" class="cuenta padv02" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"/></TD></TR>
        <TR><TH class="nowrap">LUGAR DE VISITA:</TH>
            <TD><INPUT type="text" id="nlugares" class="lugar padv02"/></TD>
            <TH class="nowrap">CUENTA CLABE:</TH>
            <TD><INPUT type="text" id="ncuentaclabe" class="cuenta padv02" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"/></TD></TR>
        <TR><TH>OBSERVACIONES:</TH>
            <TD><INPUT type="text" id="nobservaciones" class="nombreV padv02"/></TD>
            <TH class="nowrap">MONTO REQUERIDO:</TH>
            <TD><SPAN class="inputCurrency"><INPUT type="number" id="nreqviaticos" class="importe" step="any" min="0" placeholder="0.00" onchange="this.value=parseFloat(this.value).toFixed(2);"/></SPAN></TD></TR>
        <TR><TD colspan="4" class="centered"><BUTTON onclick="preNewPerDiem();">CREAR REGISTRO</BUTTON></TD></TR>
        </TBODY>
      </TABLE>
<?php }
}
    ?><TABLE id="registro_actual" class="centered screen tabla_viaticos fontMedium doprintTable noprintBorder<?=$esSolicitante?" hidden":""?>"><?php
      ?><TBODY class="lefted"><?php
      ?><TR><TH class="nowrap">FOLIO:</TH><?php
          ?><TD class="lefted"><INPUT type="text" id="regId" class="folioV2 padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->id\" readonly":" autofocus".($esSolicitante?" onkeyup=\"preOpenPerDiem(event);\"":" onchange=\"preOpenPerDiem(event);\"") ?>/></TD><?php
          ?><TH id="solicitudHCell" class="nowrap viewOnEdit<?= $hasRecord?"":" hidden" ?>">F.SOLICITUD:</TH><?php
          ?><TD id="solicitudDCell" class="lefted viewOnEdit<?= $hasRecord?"":" hidden" ?>"><INPUT type="text" id="fechaSolicitud" class="calendarV padv02 noprintBorder"<?= $hasRecord?" value=\"".fixDate($registro->fechasolicitud)."\"":"" ?> readonly/></TD><?php
          ?><TH class="nowrap viewOnEdit<?= $hasRecord?"":" hidden" ?>">F.PAGO:</TH><?php
          ?><TD class="lefted viewOnEdit<?= $hasRecord?"":" hidden" ?>"><INPUT type="text" id="fechaPago" class="calendarV padv02 noprintBorder"<?= $hasRecord?" value=\"".fixDate($registro->fechapago)."\"":" onclick=\"javascript:show_calendar_widget(this);\"" ?> readonly/></TD><?php
      ?></TR><?php
      ?><TR><TH class="nowrap">BENEFICIARIO:</TH><?php
          ?><TD class="lefted" colspan="3"><INPUT type="text" id="beneficiario" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->beneficiario\" readonly":($esSolicitante?" onkeyup=\"preOpenPerDiem(event);\"":" onchange=\"preOpenPerDiem(event);\"") ?>/></TD><?php
          ?><TH class="nowrap viewOnEdit<?= $hasRecord?"":" hidden" ?>">EMPRESA:</TH><?php
          ?><TD id="empresaDCell" class="lefted viewOnEdit<?= $hasRecord?"":" hidden" ?>"><?= $hasRecord?"<INPUT type=\"text\" id=\"empresa\" class=\"cuenta padv02 noprintBorder\" value=\"$registro->empresa\" readonly>":"<SELECT id=\"empresa\" class=\"empresaV padv02 noprintBorder\">".getHtmlOptions($grupoOptionMap,null)."</SELECT>" ?></TD><?php
      ?></TR><?php
      ?><TR class="viewOnEdit<?= $hasRecord?"":" hidden" ?>"><TH class="nowrap">BANCO:</TH><?php
          ?><TD class="lefted" colspan="3"><INPUT type="text" id="banco" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->banco\" readonly":"" ?>/></TD><?php
          ?><TH class="nowrap">CUENTA:</TH><?php
          ?><TD class="lefted"><INPUT type="text" id="cuentabancaria" class="cuenta padv02 noprintBorder" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"<?= $hasRecord?" value=\"$registro->cuentabancaria\" readonly":"" ?>/></TD></TR><?php
      ?><TR class="viewOnEdit<?= $hasRecord?"":" hidden" ?>"><TH class="nowrap">LUGAR DE VISITA:</TH><?php
          ?><TD class="lefted" colspan="3"><INPUT type="text" id="lugaresV" class="lugar padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->lugaresvisita\" readonly":"" ?>/></TD><?php
          ?><TH class="nowrap">CLABE:</TH><?php
          ?><TD class="lefted"><INPUT type="text" id="cuentaclabe" class="cuenta padv02 noprintBorder" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"<?= $hasRecord?" value=\"$registro->cuentaclabe\" readonly":"" ?>/></TD></TR><?php
      ?><TR class="viewOnEdit<?= $hasRecord?"":" hidden" ?>"><TH class="nowrap">OBSERVACIONES:</TH><?php
          ?><TD class="lefted" colspan="3"><INPUT type="text" id="observaciones" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->observaciones\" readonly":"" ?>/></TD><?php
          ?><TH class="nowrap">MONTO REQ.:</TH><?php
          ?><TD class="lefted"><SPAN class="inputCurrency"><INPUT type="number" id="viaticosReq" class="importe noprintBorder" step="any" min="0" placeholder="0.00" onchange="this.value=parseFloat(this.value).toFixed(2);"<?= $hasRecord?" value=\"".number_format($registro->viaticosrequeridos,2,".","")."\" readonly":"" ?>/></SPAN></TD></TR><?php
      ?><TR class="viewOnEdit<?= $hasRecord?"":" hidden" ?>"><TH>SOLICITANTE:</TH><?php
          ?><TD class="lefted" colspan="3"><INPUT type="text" id="solicitante" class="nombreV padv02 noprintBorder" readonly<?= $hasRecord?" value=\"$registro->solicitante\"":"" ?>/></TD><?php
          ?><TH class="nowrap">TOTAL:</TH><?php
          ?><TD class="lefted"><SPAN class="inputCurrency"><INPUT type="number" id="montoTotal" class="importe noprintBorder" step="any" min="0" placeholder="0.00" onchange="this.value=parseFloat(this.value).toFixed(2);" readonly<?= $hasRecord?" value=\"".number_format($registro->montototal,2,".","")."\"":"" ?>/></SPAN></TD></TR><?php
      ?><TR id="controlRow" class="viewOnEdit<?= $hasRecord?"":" hidden" ?>"><TH class="nowrap"><DIV id="controlCap"><?= $hasRecord?(isset($registro->rechazadoPor[0])?"RECHAZADO POR":(isset($registro->pagadoPor[0])?"SITUACIÓN":(isset($registro->autorizadoPor[0])?"AUTORIZADO POR":"SITUACIÓN"))):"SITUACIÓN" ?>:</DIV></TH><?php
          ?><TD class="lefted" colspan="5"><?= $statusControl ?></TD></TR><?php
if (!$hasRecord) { ?>
        <TR id="browseRecordRow" class="noprint"><TD colspan="6" class="centered"><SPAN id="cleanButton" class="hidden"><BUTTON onclick="preResetRecord('nuevo');">NUEVO REGISTRO</BUTTON> &nbsp; <BUTTON onclick="preResetRecord();">LIMPIAR</BUTTON> &nbsp; </SPAN><BUTTON type="button" id="openButton"<?= $esSolicitante?" onclick=\"preOpenPerDiem(event);\"":"" ?>>ABRIR</BUTTON><SPAN id="saveButtonArea" class="hidden"><BUTTON id="saveRecordBtn" onclick="saveRecord();">GUARDAR</BUTTON> &nbsp; <BUTTON id="deleteRecordBtn" onclick="preDeleteRecord();">ELIMINAR</BUTTON></TD></TR>
<?php
}
      ?></TBODY><?php
    ?></TABLE><?php if ($esNuevoSolicitante) {
    ?><IMG src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const sh=ebyid('solicitudHCell');sh.textContent='EMPRESA:';cladd(ebyid('fechaSolicitud'),'hidden');const sd=ebyid('solicitudDCell');sd.appendChild(ebyid('empresa'));clrem(sh,'hidden');clrem(sd,'hidden');ekil(this);"><?php }
if (!$hasRecord&&$puedeEditar) { ?>
      <TABLE id="nuevo_concepto" class="centered screen tabla_viaticos fontMedium hidden noprint">
        <TBODY class="lefted">
        <TR><TH><DIV>CONCEPTO:</DIV></TH>
            <TD><SELECT id="nconcepto" class="conceptoV">
                <OPTION value="hospedaje">HOSPEDAJE X NOCHE C/IVA</OPTION>
                <OPTION value="desayuno">DESAYUNO</OPTION>
                <OPTION value="comida">COMIDA</OPTION>
                <OPTION value="cena">CENA</OPTION>
                <OPTION value="propina">PROPINAS</OPTION>
                <OPTION value="pasaje">TAXIS-PASAJES</OPTION>
                <OPTION value="uber">UBER</OPTION>
                <OPTION value="avion">AVION-PASAJE</OPTION>
                <OPTION value="hospedaje2">HOSPEDAJE EXTRA</OPTION>
                <OPTION value="casetas">CASETAS</OPTION>
                <OPTION value="auto">RENTA DE CARRO</OPTION>
                <OPTION value="otros">OTROS</OPTION>
                <OPTION value="gasolina">GASOLINA (SOLO FORANEA)</OPTION>
                <OPTION value="flete">PAGO POR FLETE</OPTION>
            </SELECT></TD>
            <TH><DIV>FECHA:</DIV></TH>
            <TD><INPUT type="text" id="nfechaConcepto" value="<?= $fmtDay ?>" class="calendarV" onclick="javascript:show_calendar_widget(this);" readonly/></TD></TR>
        <TR><TH rowspan="2"><DIV>FACTURA:</DIV></TH>
            <TD rowspan="2" class="centered"><FORM id="nxmlForm" method="post" action="consultas/docs.php" class="inlineblock pad2 relative" target="doc" onsubmit="window.open('','doc'); return true;" onmouseenter="doWipe('nxml',true);" onmouseleave="doWipe('nxml',false);"><INPUT type="hidden" name="path" id="nxmlPath" value=""><INPUT type="hidden" name="id" id="nxmlId" value=""><INPUT type="hidden" name="type" value="text/xml"><IMG id="nxmlIcon" src="imagenes/icons/xml512Missing.png" height="36" class="vAlignCenter" onclick="if(ebyid('nxmlId').value.length>0) ebyid('nxmlForm').submit();"/><IMG id="nxmlWipe" src="imagenes/icons/deleteIcon12.png" class="abs_sw btnOp hidden" onclick="preResetFile('nxml');"></FORM><FORM id="npdfForm" method="post" action="consultas/docs.php" class="inlineblock pad2 relative" target="doc" onsubmit="window.open('','doc'); return true;" onmouseenter="doWipe('npdf',true);" onmouseleave="doWipe('npdf',false);"><INPUT type="hidden" name="path" id="npdfPath" value=""><INPUT type="hidden" name="id" id="npdfId" value=""><INPUT type="hidden" name="type" value="application/pdf"><IMG id="npdfIcon" src="imagenes/icons/pdf512Missing.png" height="36" class="vAlignCenter" onclick="if(ebyid('npdfId').value.length>0) ebyid('npdfForm').submit();"/><IMG id="npdfWipe" src="imagenes/icons/deleteIcon12.png" class="abs_sw btnOp hidden" onclick="preResetFile('npdf');"></FORM><SPAN class="inlineblock btnOp mode2 pad3 vAlignCenter" onclick="ebyid('ninvfiles').click();">ANEXAR XML y PDF</SPAN><INPUT type="file" id="ninvfiles" multiple class="hidden" accept=".xml,.pdf" onchange="addFiles(event);"></TD>
            <TH><DIV>FOLIO FAC:</DIV></TH>
            <TD><INPUT type="text" id="nfolio" class="folioV padv02"/></TD></TR>
        <TR><TH><DIV class="ignora">IMPORTE:</DIV></TH>
            <TD><span class="inputCurrency"><INPUT type="number" id="nimporte" class="importe" step="any" min="0" placeholder="0.00" onchange="this.value=parseFloat(this.value).toFixed(2);"/></span></TD></TR>
        <TR><TD colspan="4" class="centered"><BUTTON id="addPerDiemBtn" onclick="addPerDiem();">AGREGAR NUEVO CONCEPTO</BUTTON></TD></TR>
        </TBODY>
      </TABLE>
<?php
}
    ?><TABLE id="modifica_concepto" class="centered screen tabla_viaticos noprint fontMedium hidden" border="1"><?php
      ?><TBODY class="lefted"><?php
      ?><TR><TH><DIV>CONCEPTO:</DIV></TH><?php
          ?><TD><?php if ($hasRecord) { ?><INPUT type="text" id="concepto" class="conceptoV" readonly><?php } else { ?><SELECT id="concepto" class="conceptoV"><?php
              ?><OPTION value="hospedaje">HOSPEDAJE X NOCHE C/IVA</OPTION><?php
              ?><OPTION value="desayuno">DESAYUNO</OPTION><?php
              ?><OPTION value="comida">COMIDA</OPTION><?php
              ?><OPTION value="cena">CENA</OPTION><?php
              ?><OPTION value="propina">PROPINAS</OPTION><?php
              ?><OPTION value="pasaje">TAXIS-PASAJES</OPTION><?php
              ?><OPTION value="uber">UBER</OPTION><?php
              ?><OPTION value="avion">AVION-PASAJE</OPTION><?php
              ?><OPTION value="hospedaje2">HOSPEDAJE EXTRA</OPTION><?php
              ?><OPTION value="casetas">CASETAS</OPTION><?php
              ?><OPTION value="auto">RENTA DE CARRO</OPTION><?php
              ?><OPTION value="otros">OTROS</OPTION><?php
              ?><OPTION value="gasolina">GASOLINA (SOLO FORANEA)</OPTION><?php
              ?><OPTION value="flete">PAGO POR FLETE</OPTION><?php
          ?></SELECT><?php } ?><INPUT type="hidden" id="conceptoId" value=""/></TD><?php
          ?><TH><DIV>FECHA:</DIV></TH><?php
          ?><TD><INPUT type="text" id="fechaConcepto" value="<?= $fmtDay ?>" class="calendarV"<?php if (!$hasRecord) { ?> onclick="javascript:show_calendar_widget(this);"<?php } ?> readonly/></TD></TR><?php
      ?><TR><TH rowspan="2"><DIV>FACTURA:</DIV></TH><?php
          ?><TD rowspan="2" class="centered"><FORM id="xmlForm" method="post" action="consultas/docs.php" class="inlineblock pad2 relative" target="doc" onsubmit="window.open('','doc'); return true;"<?php if (!$hasRecord) { ?> onmouseenter="doWipe('xml',true);" onmouseleave="doWipe('xml',false);"<?php } ?>><INPUT type="hidden" name="path" id="xmlPath" value=""><INPUT type="hidden" name="id" id="xmlId" value=""><INPUT type="hidden" name="type" value="text/xml"><IMG id="xmlIcon" src="imagenes/icons/xml512Missing.png" height="36" class="vAlignCenter" onclick="if(ebyid('xmlPath').value.length>0) ebyid('xmlForm').submit();"/><IMG id="xmlWipe" src="imagenes/icons/deleteIcon12.png" class="abs_sw btnOp hidden" onclick="preResetFile('xml');"></FORM><FORM id="pdfForm" method="post" action="consultas/docs.php" class="inlineblock pad2 relative" target="doc" onsubmit="window.open('','doc'); return true;"<?php if (!$hasRecord) { ?> onmouseenter="doWipe('pdf',true);" onmouseleave="doWipe('pdf',false);"<?php } ?>><INPUT type="hidden" name="path" id="pdfPath" value=""><INPUT type="hidden" name="id" id="pdfId" value=""><INPUT type="hidden" name="type" value="application/pdf"><IMG id="pdfIcon" src="imagenes/icons/pdf512Missing.png" height="36" class="vAlignCenter" onclick="if(ebyid('pdfPath').value.length>0) ebyid('pdfForm').submit();"/><IMG id="pdfWipe" src="imagenes/icons/deleteIcon12.png" class="abs_sw btnOp hidden" onclick="preResetFile('pdf');"></FORM><?php if (!$hasRecord) { ?><SPAN id="addFilesBtn" class="inlineblock btnOp mode2 pad3 vAlignCenter noprint" onclick="const f=ebyid('invfiles'); if (!f.disabled) f.click();">ANEXAR XML y PDF</SPAN><INPUT type="file" id="invfiles" multiple class="hidden" accept=".xml,.pdf" onchange="addFiles(event);"><?php } ?></TD><?php
          ?><TH><DIV>FOLIO FAC:</DIV></TH><?php
          ?><TD><INPUT type="text" id="folio" class="folioV padv02"<?= $hasRecord?" readonly":"" ?>/></TD></TR><?php
      ?><TR><TH><DIV class="ignora">IMPORTE:</DIV></TH><?php
          ?><TD><span class="inputCurrency"><INPUT type="number" id="importe" class="importe" step="any" min="0" placeholder="0.00"<?= $hasRecord?" readonly":" onchange=\"this.value=parseFloat(this.value).toFixed(2);\"" ?>/></span></TD></TR><?php
      ?><TR class="noprint"><TD colspan="4" class="centered"><?php if (!$hasRecord) { ?><BUTTON id="fixPerDiemBtn" onclick="fixPerDiem();">MODIFICAR CONCEPTO</BUTTON> &nbsp; <BUTTON id="delPerDiemBtn" onclick="preDelPerDiem();">ELIMINAR CONCEPTO</BUTTON><?php } else { ?><BUTTON onclick="showBlock('registro_actual');">REGRESAR</BUTTON><?php } ?></TD></TR><?php
      ?></TBODY><?php
    ?></TABLE><?php

$conceptKeys=$hasRecord?array_keys((array)$registro->conceptos):[];
$detailKeys=[];
if (isset($conceptKeys[0])) {
    $conceptCodes=["hospedaje","desayuno","comida","cena","propina","pasaje","uber","avoin","hospedaje2","casetas","auto","otros","gasolina","flete"];
    $conceptNames=["hospedaje"=>"HOSPEDAJE X NOCHE", "desayuno"=>"DESAYUNO", "comida"=>"COMIDA", "cena"=>"CENA", "propina"=>"PROPINAS", "pasaje"=>"TAXIS-PASAJE", "uber"=>"UBER", "avion"=>"AVION-PASAJE", "hospedaje2"=>"HOSPEDAJE EXTRA", "casetas"=>"CASETAS", "auto"=>"RENTA DE CARRO", "otros"=>"OTROS", "gasolina"=>"GASOLINA (FORÁNEA)", "flete"=>"PAGO X FLETE"];
    $conceptSum=["hospedaje"=>0, "desayuno"=>0, "comida"=>0, "cena"=>0, "propina"=>0, "pasaje"=>0, "uber"=>0, "avion"=>0, "hospedaje2"=>0, "casetas"=>0, "auto"=>0, "otros"=>0, "gasolina"=>0, "flete"=>0];
    $columnSum=[];
    $totalSum=0;
    sort($conceptKeys);
} else {
    $conceptNames=[];
    $totalSum=0;
}
  ?><BR/><TABLE id="viaje_acumulado" class="viewWithData centered fontMedium<?= isset($conceptKeys[0])?"":" hidden" ?>"><?php
      ?><THEAD id="block-header"><?php
if (isset($conceptKeys[0])) {
        ?><TR><?php
          ?><TD class="concepto"></TD><?php
    foreach ($conceptKeys as $cKey) {
        echo "<TH colspan=\"2\" class=\"diafecha b3313 bluedbg5 centered\">FECHA</TH>";
    }
          ?><TD class="totalfila"></TD><?php
        ?></TR><?php
        ?><TR><?php
          ?><TD class="concepto"></TD><?php
    foreach ($conceptKeys as $cKey) {
        $bloque=$registro->conceptos[$cKey];
        if (!isset($columnSum[$cKey])) $columnSum[$cKey]=0;
        foreach($bloque as $nombreConcepto => $arregloElemento) {
            if (isset($arregloElemento[0])) {
                if (isset($arregloElemento[0]["fechafactura"][9])) {
                    $concFecha=$arregloElemento[0]["fechafactura"];
                    $concHora=substr($concFecha,11);
                    $concFecha=substr($concFecha,8,2)."/".substr($concFecha,5,2)."/".substr($concFecha,0,4);
                    clog2("Fecha=$concFecha, Hora=$concHora, Original=".$arregloElemento[0]["fechafactura"]);
                    // ToDo: invertir dd/mm/yyyy
                    break;
                }
                if (isset($arregloElemento[0]["fecha"][9])) {
                    $concFecha=$arregloElemento[0]["fecha"];
                    $concFecha=substr($concFecha,8,2)."/".substr($concFecha,5,2)."/".substr($concFecha,0,4);
                    clog2("Fecha=$concFecha, Original=".$arregloElemento[0]["fechafactura"]);
                    break;
                }
            }
        }
        if (!isset($concFecha)) {
            $concFecha=substr($cKey, 2, 2)."/".substr($cKey, 0, 2)."/XXXX";
        }
        echo "<Td colspan=\"2\" class=\"diafecha campofecha b1333\">$concFecha</TH>";
    }
          ?><TD class="totalfila"></TD><?php
        ?></TR><?php
        ?><TR><?php
          ?><TD class="concepto"></TD><?php
    /* LOOP conceptos fechas */
    foreach ($conceptKeys as $cKey) {
        echo "<TH class=\"diafolio b3133 bluedbg5 centered\">FOLIO</TH><TH class=\"diamonto b3331 bluedbg5 centered onprintBR0\">IMPORTE</TH>";
    }
          ?><TH class="totalfila b3333 bluedbg5 centered">TOTAL</TH><?php
        ?></TR><?php
}
      ?></THEAD><?php
      ?><TBODY id="block-body"><?php
foreach ($conceptNames as $cCode=>$cName) {
        ?><TR><?php
          ?><TH class="concepto b3333 bluedbg5"><?= $cName ?></TH><?php
    foreach ($conceptKeys as $cKey) {
        $bloque=$registro->conceptos[$cKey];
          ?><TD class="diafolio b1113 oneLine padv3"><?php
        if(isset($bloque[$cCode])) {
            $items=$bloque[$cCode];
            for ($g=0; isset($items[$g]); $g++) {
                $record=$items[$g];
            //foreach ($items as $record) {
                clog2("Registro[$cKey,$cCode,$g]=",json_encode($record));
                $pdfF=$record["archivopdf"]??"";
                $xmlF=$record["archivoxml"]??"";
                $folioF=$record["foliofactura"]??"";
                $hasPDF=isset($pdfF[0]);
                $hasXML=isset($xmlF[0]);
                $hasFolio=isset($folioF[0]);
                if ($hasPDF||$hasXML) {
                    $ffval=($hasFolio?$folioF:($hasPDF?"PDF":"XML"));
                    $filepath=$hasPDF?$pdfF:$xmlF;
                    if (isset($filepath[0])) $filepath="viajes/".$filepath;
                    $filetype=$hasPDF?"application/pdf":"text/xml";
            ?><DIV><FORM method="POST" action="consultas/docs.php" class="inlineblock noprint" target="doc" onsubmit="window.open('','doc'); return true;"><?php
              ?><INPUT type="hidden" name="path" value="<?= $filepath ?>"/><INPUT type="hidden" name="type" value="<?= $filetype ?>"/><INPUT type="submit" value="<?= $ffval ?>"/><?php
            ?></FORM><SPAN class="hidden doprintBlock"><?= $ffval ?></SPAN></DIV><?php
                } else if($hasFolio) {
            ?><DIV><?= $folioF ?></DIV><?php
                }
            }
        }
          ?></TD><?php
          ?><TD class="diamonto b1311 righted padv3 onprintBR0"><?php
            if(isset($bloque[$cCode])) {
                $items=$bloque[$cCode];
                for ($g=0; isset($items[$g]); $g++) {
                    $record=$items[$g];
                    $importeN=+($record["importe"]??"0");
                    $conceptSum[$cCode]+=$importeN;
                    $columnSum[$cKey]+=$importeN;
                    $totalSum+=$importeN;
                    $importeF=number_format($importeN,2,".","");
            ?><DIV class="righted">$<?= $importeF ?></DIV><?php
            }
        }
          ?></TD><?php
    }
          ?><TD class="totalfila b1313 righted"><?= $conceptSum[$cCode]!==0?"$ ".number_format($conceptSum[$cCode],2,".",""):"" ?></TD><?php
        ?></TR><?php
}
      ?></TBODY><?php
      ?><TFOOT id="block-footer"><?php
        ?><TR><?php
          ?><TH class="concepto b3333 bluedbg5">TOTAL</TH><?php
foreach ($conceptKeys as $cKey) {
          ?><TD class="diafolio b3000"></TD><TD class="diamonto b3333 righted padv3">$ <?= number_format($columnSum[$cKey],2,".","") ?></TD></TD><?php
}
          ?><TH class="totalfila b3333 righted">$ <?= number_format($totalSum,2,".","") ?></TH><?php
        ?></TR><?php
      ?></TFOOT><?php
  ?></TABLE><?php
  ?><DIV class="hg30">&nbsp;</DIV><?php
?></DIV><?php
if (!$hasRecord) { ?>
</DIV>
<?php
    clog1seq(-1);
    clog2end("templates.viajero");
}
