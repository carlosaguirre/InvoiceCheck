<?php
clog2ini("templates.solpago");
clog1seq(1);
$preSp="<img src=\"imagenes/pt.png\" width=\"".($esDesarrollo?"3.8":"15.8")."\">";
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">Solicitud de Pago</h1>
  <div id="area_detalle" class="scrollauto all_space">
    <table class="solpago centered">
      <colgroup><col width="109"><col width="147"><col width="94"><col width="200"></colgroup>
      <tr class="lefted">
        <th><div><?= $preSp ?>FECHA</div></th>
        <td>
          <input type="text" id="fecha" class="date noprintBorder" value="<?= $fmtDay ?>" <?php /* onclick="show_calendar_widget(this);" */ ?> readonly/></td>
        <th><div>PAGO</div></th>
        <td>
          <input type="text" id="fechapago" class="date noprintBorder" value="<?= $fmtDay ?>" onclick="show_calendar_widget(this);" readonly/></td>
      </tr>
      <tr id="gpo_row" class="hidden">
        <th ondblclick="reMap('gpo');"><div><?= $preSp ?>EMPRESA</div></th>
        <td colspan="3">
          <select name="grupo" id="gpo_alias" oninput="populateGroup();"><?= getHtmlOptions($gpoAliasOpt, $gpoAliasDefault) ?></select>
          <div id="gpo_detail" class="detail"><?= $gpoRazSocDefault ?></div></td>
      </tr>
      <tr id="prv_row" class="hidden">
        <th ondblclick="reMap('prv');"><div><?= $preSp ?>PROVEEDOR</div></th>
        <td colspan="3" >
          <select name="proveedor" id="prv_codigo" oninput="populateProvider();"><?= getHtmlOptions([""=>""] + array_combine(array_keys($prvMap), array_column($prvMap, 'codigo'))) ?></select>
          <div id="prv_detail" class="detail"></div></td>
      </tr>
      <tr id="prv_row2" class="hidden">
        <th><div><?= $preSp ?>BANCO</div></th>
        <td><div id="prv_banco"></div>
        </td>
        <th><div>CLABE</div></th>
        <td><div id="prv_clabe"></div>
        </td>
      </tr>
      <tr id="prv_row3" class="hidden">
        <th><div><?= $preSp ?>STATUS</div></th>
        <td colspan="3"><div id="prv_status"></div></td>
      </tr>
<?php
if ($esDesarrollo) {
?>
      <tr class="lefted">
        <th class="top"><div>
          <select id="docChoice" onchange="switchDocument(event)"><option value="factura" class="font14">FACTURA</option><option value="orden" class="font14">ORDEN</option><option value="contra" class="fontCondensed hei19_2">CONTRA RECIBO</option></select></div></th>
        <td id="docMain">
          <div id="invIn" class="docfactura">
            <b>FOLIO</b>&nbsp;<input type="text" id="folio" autofocus oninput="evalInvoice(event);" onkeyup="kup(event);"><img src="imagenes/ledoff.gif" id="delFolioImg" class="grayscale" onclick="clearInvoice('folio');"><script>ebyid("folio").focus();</script>
          </div>
          <div id="invOff" class="docfactura">
            <label for="invfiles" title="Anexe los archivos XML y PDF correspondientes a su factura" class="nobottommargin vAlignCenter nowrap asBtn zNo" style="padding-top: 0px;">Anexar Archivos</label>
          </div>
          <div class="docorden hidden">
            <input type="text" id="ordRef" class="vAlignCenter zNo" placeholder="No. Orden de Compra" style="margin:1.4px 0px;" oninput="evalOrder();" onkeyup="kup(event);">
            <label id="ordDoc" for="ordFile" title="Anexe el archivo PDF correspondiente a su orden de compra" class="nobottommargin vAlignCenter nowrap asBtn zNo" style="padding-top: 0px;">Anexar PDF</label>
            <input type="file" name="ordFile" id="ordFile" accept=".pdf" onchange="fixOrdDoc();" class="masked">
          </div>
          <div class="doccontra lefted relative hidden">
            <b>FOLIO</b>&nbsp;<input type="text" id="foliocr" class="padv02 marbtm2 wid90px" autofocus oninput="evalGroupCounter(event);" onchange="evalCounter(event);" onkeyup="kup(event);"><img src="imagenes/ledoff.gif" id="delFolioCRImg" class="grayscale" onclick="clearCounter();"><span id="messagecr"></span><span id="crReactButtonArea" class="crReact hidden"><button id="mergeButton" class="marR3" onclick="mergeCrBtn();">INTEGRAR</button><input type="checkbox" id="mergeCrAllChk" title="Cambiar Todas" onclick="mergeCrChkSwitch(event);" checked></span><script>ebyid("foliocr").focus();</script>
          </div>
        </td>
        <td id="auxDoc" class="docfactura" colspan="2">
          <input type="file" name="invfiles[]" id="invfiles" multiple accept=".xml,.pdf" onchange="browseCFDI();" class="masked">
          <div id="invSpc"><b>UUID</b>&nbsp;<input type="text" id="uuid" class="padv02 marbtm2 wid225px" oninput="evalInvoice(event);"><img src="imagenes/ledoff.gif" id="delUuidImg" class="grayscale" onclick="clearInvoice('uuid');"></div>
          <div id="invArc" class="pad0i"><p class="lefted marbtm0">Debe seleccionar XML y PDF simultáneamente</p></div>
        </td>
      </tr>
      <tr class="doccontra hidden">
        <th id="capContra" class="topvalign pad2 bgwhite"></th>
        <td id="detContra" class="topvalign pad2 bgwhite2" colspan="3"></td>
      </tr>
<?php
} else {
?>
      <tr class="lefted">
        <th class="top"><div>
          <input type="checkbox" id="invChkbox" checked oninput="switchInvoice();" class="top marginR3i"><span id="invCap" class="vAlignCenter">FACTURA</span></div></th>
        <td id="invDoc" class="top">
            <div id="invIn">
              <b>FOLIO</b>&nbsp;<input type="text" id="folio" class="padv02 marbtm2 wid90px" autofocus oninput="evalInvoice(event);"><img src="imagenes/ledoff.gif" id="delFolioImg" class="grayscale" onclick="clearInvoice('folio');"><script>ebyid("folio").focus();</script>
            </div>
            <div id="invOff">
              <label for="invfiles" title="Anexe los archivos XML y PDF correspondientes a su factura" class="nobottommargin vAlignCenter nowrap asBtn zNo" style="padding-top: 0px;">Anexar Archivos</label>
            </div>
        </td>
        <td id="auxDoc" colspan="2">
          <input type="file" name="invfiles[]" id="invfiles" multiple accept=".xml,.pdf" onchange="browseCFDI();" class="masked">
          <input type="text" id="ordRef" class="vAlignCenter hidden zNo" placeholder="No. Orden de Compra" style="margin:1.4px 0px;" oninput="evalOrder();">
          <label id="ordDoc" for="ordFile" title="Anexe el archivo PDF correspondiente a su orden de compra" class="nobottommargin vAlignCenter nowrap asBtn hidden zNo" style="padding-top: 0px;">Anexar PDF</label>
          <input type="file" name="ordFile" id="ordFile" accept=".pdf" onchange="fixOrdDoc();" class="masked">
          <div id="invSpc"><b>UUID</b>&nbsp;<input type="text" id="uuid" class="padv02 marbtm2 wid225px" oninput="evalInvoice(event);"><img src="imagenes/ledoff.gif" id="delUuidImg" class="grayscale" onclick="clearInvoice('uuid');"></div>
          <div id="invArc" class="pad0i"><p class="lefted marbtm0">Debe seleccionar XML y PDF simultáneamente</p></div>
        </td>
      </tr>
<?php
}
?>
      <tr id="detailI" class="hidden lefted">
        <th class="top"><div><?= $preSp ?><span>CONCEPTOS</span></div></th>
<?php // ToDo_SOLICITUD: Requerir captura de Pedido
?>
        <td>&nbsp;</td>
        <th><div>PEDIDO<br>REMISION</div></th>
        <td><input type="text" id="pedido" class="pedido noprintBorder" placeholder="S/PEDIDO"><br>
            <input type="text" id="remision" class="pedido noprintBorder" placeholder="S/REMISION"></td>
      </tr>
      <tr id="detailI2" class="hidden centered">
        <td colspan="4">
          <table class="fs12a marL16 lytfxd">
            <colgroup><col width="65">
<?php // ToDo_SOLICITUD: ancho columna código articulo ?>
              <col width="72">
              <col width="206"><col width="80"><col width="90"></colgroup>
            <thead>
              <tr>
                <th>Cantidad</th>
<?php // ToDo_SOLICITUD: encabezado código artículo ?>
                <th>Código</th>
                <th>Descripción</th>
                <th class="righted">Costo U.</th>
                <th class="righted">Importe</th>
              </tr>
            </thead>
            <tbody id="artTbd"></tbody>
          </table>
        </td>
      </tr>
      <tr id="detailO" class="hidden lefted">
        <th class="top"><div><?= $preSp ?><span>IMPORTE</span></div></th>
        <td colspan="3"><input type="number" placeholder="0.00" min="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" id="importe" onInput="evalOrder();"><?= $monedaOI??"" ?></td>
      </tr>
      <tr id="sol_obs_row" class="hidden lefted">
        <th class="top"><div class="ellipsis" title="OBSERVACIONES"><?= $preSp ?><span>OBSERVACIONES</span></div><div id="obs_max_len" class="rightedi fontPageFormat">200 caracteres</div></th>
        <td colspan="3"><textarea id="observaciones" style="resize:none;" class="wid95" maxlength="200" oninput="obsSeek()"></textarea></td>
      </tr>
    </table>
    <div id="warningCR" class="hidden centered"></div>
    <div id="btnRow" class="hidden centered sticky toBottom padb3 basicBG"><input type="button" id="cancelInvoiceBtn" value="Cancelar Factura" class="marR3" onclick="preCancelInvoice();"><input type="button" id="btnObj" value="Solicitar Autorización" onclick="preSubmitAuthorization();"></div>
  </div>
  <img src="imagenes/pt.png" onload="doLoaded();ekil(this);">
</div>
<?php
clog1seq(-1);
clog2end("templates.solpago");
