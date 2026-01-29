<?php
if (!$hasRecord) {
    clog2ini("templates.cajachica");
    clog1seq(1); ?>
<div id="area_general" class="central">
  <h1 class="txtstrk relative">Captura de Reembolso de Caja Chica<a target="manual" class="abs rgt5 top8 noprint" href="manual/manualReembolso.pdf"><img src="imagenes/icons/assistance.png" class="top hoverable" title="Descargar Manual de Reembolso de Viáticos y Caja Chica"/></a></h1>
  <div id="area_detalle"<?= $areaDetalleClass ?>>
    <div class="sticky toTop centered basicBG zIdx1000 noprint">
<?php 
    if ($puedeEditar) { ?>
      <span id="nuevo_registro_btn" class="btnTab" onclick="showBlock('nuevo_registro','nbeneficiario');">NUEVO REGISTRO</span>
<?php 
    } ?>
      <span id="buscar_registro_btn" class="btnTab selected" onclick="showBlock('buscar_registro','bregId');">BUSCAR REGISTRO</span>
      <span id="registro_actual_btn" class="btnTab<?= $testing?"":" invisible" ?>" onclick="showBlock('registro_actual');">REGISTRO ACTUAL</span>
    </div>
    <table id="buscar_registro" class="centered screen tabla_caja fontMedium noprint">
      <tbody class="lefted">
        <tr><th class="nowrap">FOLIO:</th>
          <td class="lefted"><input type="text" id="bregId" class="folioV2 padv02" autofocus onchange="openRecord(event);"/></td></tr>
        <tr><th class="nowrap">BENEFICIARIO:</th>
          <td class="lefted"><input type="text" id="bbeneficiario" class="nombreV padv02" pattern="[A-ZÑ0-9 \.,\(\)\&\/\-]+" onpaste="chkPaste(event)" onkeydown="validaBeneficiario(event)" onchange="openRecord(event)" onblur="openRecord(event)"/></td></tr>
        <tr id="browseRecordRow" class="noprint"><td colspan="2" class="centered"><button type="button" id="openButton" onclick="openRecord(event)">ABRIR</button></td></tr>
      </tbody>
    </table>
<?php 
    if ($puedeEditar) { ?>
    <table id="nuevo_registro" class="centered screen tabla_caja fontMedium noprint hidden">
      <tbody class="lefted">
        <tr><th class="nowrap">BENEFICIARIO:</th>
          <td class="lefted"><input type="text" id="nbeneficiario" class="nombreV padv02" pattern="[A-Z0-9 ]+" onpaste="chkPaste(event)" onkeydown="validaBeneficiario(event)"/></td>
          <th class="nowrap">EMPRESA:</th>
          <td><SELECT id="nempresa" class="empresaV padv02"><OPTION></OPTION><?= $groupOptions ?></SELECT></td></tr>
        <tr class="bgblack"><th class="nowrap">BANCO:</th>
          <td class="lefted"><input type="text" id="nbanco" class="nombreV padv02 bgwhite"/></td>
          <th class="nowrap">CUENTA BANCARIA:</th>
          <td><input type="text" id="ncuentabanco" class="cuenta padv02 bgwhite" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"/></td></tr>
        <tr class="bgblack"><th class="nowrap">CONCEPTO:</th>
          <td class="lefted relative">
            <input type="text" id="nconcepto" class="nombreV padv02 bgwhite"<?= " value=\"CAJA CHICA CON FACTURA\" readonly" ?>/>
          </td>
          <th class="nowrap">CUENTA CLABE:</th>
          <td><input type="text" id="ncuentaclabe" class="cuenta padv02 bgwhite" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"/></td></tr>
        <tr><th class="nowrap">OBSERVACIONES:</th>
          <td class="lefted"><input type="text" id="nobservaciones" class="nombreV padv02"/></td>
          <th class="nowrap">MONTO:</th>
          <td class="lefted"><span class="inputCurrency"><input type="number" id="nmonto" class="importe" step="any" min="0" placeholder="0.00" onchange="this.value=parseFloat(this.value).toFixed(2);"/></span></td></tr>
        <tr><td colspan="4" class="centered"><button onclick="newRecord();">CREAR REGISTRO</button></td></tr>
      </tbody>
    </table>
<?php
    }
}
if ($hasRecord) clog2(json_encode(array_keys((array)$registro)));
  ?><table id="registro_actual" class="centered screen tabla_caja fontMedium doprintTable noprintBorder<?= $hasRecord?"":" hidden" ?>"><?php
    ?><tbody class="lefted"><?php
      ?><tr><th>FOLIO:</th><?php
        ?><td class="lefted"><input type="text" id="regId" class="folioV2 padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->id\"":"" ?> readonly/></td><?php
        ?><th class="nowrap">F.SOLICITUD:</th><?php
        ?><td class="lefted"><input type="text" id="fechaSolicitud" class="calendarV padv02 noprintBorder"<?= $hasRecord?" value=\"".fixDate($registro->fechasolicitud)."\"":"" ?> readonly/></td><?php
        ?><th class="nowrap">F.PAGO:</th><?php
        ?><td class="lefted"><input type="text" id="fechaPago" class="calendarV padv02 noprintBorder"<?= $hasRecord?" value=\"".fixDate($registro->fechapago)."\"":($puedeEditar?" onclick=\"javascript:show_calendar_widget(this);\"":"") ?> readonly/></td></tr><?php
      ?><tr><th class="nowrap">BENEFICIARIO:</th><?php
        ?><td class="lefted" colspan="3"><input type="text" id="beneficiario" class="nombreV padv02 noprintBorder" pattern="[A-ZÑ0-9 \.,\(\)\&\/\-]+" onpaste="chkPaste(event)" onkeydown="validaBeneficiario(event)"<?= $hasRecord?" value=\"$registro->beneficiario\" readonly":$editAttrib ?>/></td><?php
        ?><th class="nowrap">EMPRESA:</th><?php
        ?><td class="lefted"><?= $hasRecord?"<INPUT type=\"text\" id=\"empresa\" class=\"cuenta padv02 noprintBorder\" value=\"$registro->empresa\" readonly>":($puedeEditar?"<SELECT id=\"empresa\" class=\"empresaV padv02 noprintBorder\">$groupOptions</SELECT>":"<INPUT id=\"empresa\" class=\"empresaV padv02 noprintBorder\" readonly>") ?></td></tr><?php
      ?><tr><th class="nowrap">BANCO:</th><?php
        ?><td class="lefted" colspan="3"><input type="text" id="banco" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->banco\" readonly":$editAttrib ?>/></td><?php
        ?><th class="nowrap">CUENTA:</th><?php
        ?><td class="lefted"><input type="text" id="cuentabanco" class="cuenta padv02 noprintBorder" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"<?= $hasRecord?" value=\"$registro->cuentabancaria\" readonly":$editAttrib ?>/></td></tr><?php
      ?><tr><th class="nowrap">CONCEPTO:</th><?php
        ?><td class="lefted relative" colspan="3">
          <input type="text" id="concepto" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->concepto\" readonly":" value=\"CAJA CHICA CON FACTURA\" readonly"/*" onclick=\"if(!this.readOnly)clfix(ebyid('conceptoList'),'hidden');\" oninput=\"if(!this.readOnly){let cl=ebyid('conceptoList');cl.value='';cladd(cl,'hidden');}\""*/ ?>/><?php if (false) { echo $hasRecord?"":"<select id=\"conceptoList\" class=\"nombreV concept_dropdown_list hidden\" size=\"2\" onclick=\"cladd(this,'hidden');\" onchange=\"ebyid('concepto').value=this.options[this.selectedIndex].text;\">".getHtmlOptions($conceptosMap,null)."</select><img src=\"imagenes/icons/downArrow.png\" width=\"12\" class=\"concept_dropdown_button\" onclick=\"if(!ebyid('concepto').readOnly)clfix(ebyid('conceptoList'),'hidden');\"/>"; } ?></td><?php
        ?><th class="nowrap">CLABE:</th><?php
        ?><td class="lefted"><input type="text" id="cuentaclabe" class="cuenta padv02 noprintBorder" maxlength="20" onkeydown="return ignoreSpaces(event);" onchange="removeSpaces(event);"<?= $hasRecord?" value=\"$registro->cuentaclabe\" readonly":$editAttrib ?>/></td></tr><?php
      ?><tr><th class="nowrap">OBSERVACIONES:</th><?php
        ?><td class="lefted" colspan="3"><input type="text" id="observaciones" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->observaciones\" readonly":$editAttrib ?>/></td><?php
        ?><th class="nowrap"<?= $esSistemas?" ondblclick=\"preRecalcAmount();\"":"" ?>>MONTO:</th><?php
        ?><td class="lefted"><span class="inputCurrency"><input type="number" id="monto" class="importe noprintBorder" step="any" min="0" placeholder="0.00"<?= $hasRecord?" value=\"".number_format($registro->monto,2,".","")."\" readonly":($puedeEditar?" onchange=\"this.value=parseFloat(this.value).toFixed(2);\"":" readonly") ?>/></span></td></tr><?php
      ?><tr><th class="nowrap">SOLICITANTE:</th><?php
        ?><td class="lefted" colspan="3"><input type="text" id="solicitante" class="nombreV padv02 noprintBorder"<?= $hasRecord?" value=\"$registro->solicitante\"":$editAttrib ?> readonly/></td><?php
        ?><td class="centered vAlignCenter" colspan="2" rowspan="2"></td></tr><?php
      ?><tr><th class="nowrap" id="controlCap"><?= $hasRecord?(isset($registro->autorizadoPor[0])?"AUTORIZADO POR":(isset($registro->rechazadoPor[0])?"RECHAZADO POR":"SITUACION:")):"SITUACION:" ?></th><?php
        ?><td class="lefted" colspan="5" id="controlVal"><?= $statusControl ?></td></tr><?php
if (!$hasRecord) { ?>
        <tr id="editRecordRow" class="noprint">
            <td colspan="6" class="centered"><?php if($puedeEditar) { ?><button id="addFilesBtn" type="button" onclick="const f=ebyid('invfiles'); if (!f.disabled&&validCountFiles()) f.click();">ANEXAR ARCHIVOS</button><input type="file" id="invfiles" multiple class="hidden" accept=".xml,.pdf" onchange="addFiles(event);"/> &nbsp; <?php } ?><button id="saveRecordBtn" onclick="saveRecord();">GUARDAR DATOS</button><?php if($puedeEditar) { ?> &nbsp; <button id="deleteRecordBtn" onclick="preDeleteRecord();">ELIMINAR REGISTRO</button><?php } ?></td></tr>
<?php
} 
    ?></tbody><?php
  ?></table><?php
  ?><table id="files" class="centered screen tabla_caja fontMedium noprintBorder<?= ($hasRecord&&isset($registro->archivos[0]))?"":" hidden" ?>"><?php
    ?><thead class="darker2 sprut"><?php
      ?><tr><?php
        ?><th class="shrinkCol">#</th><?php
        ?><th class="shrinkCol">XML</th><?php
        ?><th class="shrinkCol">PDF</th><?php
        ?><th>Nombre</th><?php
        ?><th>Folio</th><?php
        ?><th>Fecha</th><?php
        ?><th class="nosprut lsprut">Total</th><?php
        ?><th class="nosprut rsprut shrinkCol"></th><?php
      ?></tr><?php
    ?></thead><?php
    ?><tbody tipo="archivos-contenido"><?php 
if ($hasRecord) {
    $sum=0;
    $num=0;
    for ($i=0; isset($registro->archivos[$i]); $i++) {
        $arch=$registro->archivos[$i];
        $archId=$arch["id"]??"";
        $archXML=$arch["archivoxml"]??"";
        $pathXML=((isset($archXML[0])&&$archXML!=="temporal")?"viajes/":"").$archXML;
        $archPDF=$arch["archivopdf"]??"";
        $pathPDF=((isset($archPDF[0])&&$archPDF!=="temporal")?"viajes/":"").$archPDF;
        $archTC=$arch["tipocomprobante"]??"i";
        if (!isset($archTC[0])||($archTC[0]!=="i"&&$archTC[0]!=="e")) continue; // no permitir anexar pagos
        $num++;
        echo "<TR><TD col=\"num\" class=\"shrinkCol\">$num</TD>";
        echo "<TD col=\"xml\" class=\"shrinkCol\"><FORM id=\"xml{$archId}Form_$i\" method=\"POST\" action=\"consultas/docs.php\" class=\"inlineblock pad2 top relative\" target=\"doc\" onsubmit=\"window.open('','doc');\"><INPUT type=\"hidden\" name=\"type\" value=\"text/xml\"><INPUT type=\"hidden\" name=\"path\" id=\"xml{$archId}Path_$i\" value=\"$pathXML\">";
        echo "<IMG id=\"xml{$archId}Icon_$i\" height=\"24\"";
        if (isset($archXML[0])) echo " src=\"imagenes/icons/xml200.png\" class=\"pointer\" onclick=\"this.parentNode.submit();\"";
        else echo " src=\"imagenes/icons/xml512Missing.png\"";
        echo "\>";
        echo "</FORM>";
        echo "</TD>";
        echo "<TD col=\"pdf\" class=\"shrinkCol\"><FORM id=\"pdf{$archId}Form_$i\" method=\"POST\" action=\"consultas/docs.php\" class=\"inlineblock pad2 top relative\" target=\"doc\" onsubmit=\"window.open('','doc');\"><INPUT type=\"hidden\" name=\"type\" value=\"application/pdf\"><INPUT type=\"hidden\" name=\"path\" id=\"pdf{$archId}Path_$i\" value=\"$pathPDF\">";
        echo "<IMG id=\"pdf{$archId}Icon_$i\" height=\"24\"";
        if (isset($archPDF[0])) echo " src=\"imagenes/icons/pdf200.png\" class=\"pointer\" onclick=\"this.parentNode.submit();\"";
        else echo " src=\"imagenes/icons/pdf512Missing.png\"";
        echo "\>";
        echo "</FORM>";
        echo "</TD>";
        if (isset($archXML[0]))
            $nombre=$archXML;
        else if (isset($archPDF[0]))
            $nombre=$archPDF;
        echo "<TD col=\"nombre\">";
        if (isset($nombre[0])) {
            $idx1=strrpos($nombre,"/");
            $idx2=strrpos($nombre,".");
            if ($idx1===false) {
                if ($idx2===false) echo $nombre;
                else echo substr($nombre, 0, $idx2);
            } else {
                if ($idx2===false) echo substr($nombre, $idx1+1);
                else echo substr($nombre, $idx1+1, $idx2-$idx1-1);
            }
        }
        echo "</TD>";
        echo "<TD col=\"folio\">";
        $archFolio=$arch["foliofactura"]??"";
        if (isset($archFolio[0])) echo $archFolio;
        echo "</TD>";
        echo "<TD col=\"fecha\">";
        $archFecha=$arch["fechafactura"]??"";
        if (isset($archFecha[0])) echo substr($archFecha,0,10);
        echo "</TD>";
        echo "<TD col=\"importe\" tc=\"$archTC\">";
        $archTotal=$arch["totalfactura"]??"";
        if (isset($archTotal[0])) {
            $archTotN=(+$archTotal);
            if ($archTC==="e") {
                echo "- ";
                $archTotN *= -1;
            }
            echo "$ {$archTotN}";
            $sum+=$archTotN;
        }
        echo "</TD>";
        echo "<TD col=\"boton\">";
        echo "</TD>";
        echo "</TR>";
    }
}
    ?></tbody><?php
    ?><tfoot class="darker total test"><?php
      ?><tr><?php
        ?><th id="numFiles" class="centered shrinkCol"><?= ($hasRecord&&isset($num))?"$num":""  ?></th><?php
        ?><th id="descFiles" colspan="4" class="lefted"><?= ($hasRecord&&isset($num))?"comprobante".($num==1?"":"s"):"" ?></th><?php
        ?><th class="righted">SUMA:</th><?php
        ?><th id="sumFilesTotal" class="centered nowrap"><?= ($hasRecord&&isset($sum))?"$ ".$sum:"" ?></th><?php
        ?><th class="shrinkCol"></th><?php
      ?></tr><?php
    ?></tfoot><?php
  ?></table><?php
if (!$hasRecord) { ?>
  </div>
</div>
<?php
    clog1seq(-1);
    clog2end("templates.cajachica");
}
