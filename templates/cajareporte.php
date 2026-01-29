<?php
clog2ini("templates.cajareporte");
clog1seq(1);
?>
<div id="area_general" class="central">
  <div id="repLogo"<?= $repLogoAttribs ?>></div>
  <h1 class="txtstrk relative printStatic">Reporte de Reembolso de Caja Chica y Viáticos<a target="manual" class="abs rgt5 top8 noprint" href="manual/manualReembolso.pdf"><img src="imagenes/icons/assistance.png" class="top hoverable" title="Descargar Manual de Reembolso de Viáticos y Caja Chica"/></a></h1>
  <div id="area_detalle" class="scroll-60 relative printStatic">
    <div class="sticky printStatic toTop basicBG zIdx1000">
        <table class="centered screen tabla_caja fontMedium">
            <tbody class="lefted">
                <tr>
                    <th>FOLIO:</th>
                    <td colspan="2"><input type="text" id="bfolio" class="padv02 noprintBorder resetable" placeholder="TODOS" onchange="doReset(event);"></td>
                    <th>EMPRESA:</th>
                    <td class="vAlignCenter"><select id="bempresa" class="padv02 noprintBorder resetable" onchange="doRepLogo(event);doReset(event);"><?= $groupOptions ?></select></td>
                    <th>FECHA TIPO:</th>
                    <td class="alignCenter"><select id="tipofecha" class="padv02 noprintBorder resetable" onchange="doReset(event);"><OPTION value="solicitud">SOLICITUD</OPTION><OPTION value="pago">PAGO</OPTION></select></td>
                </tr>
                <tr>
                    <th>TIPO:</th>
                    <td colspan="2"><select id="tipo" class="padv02 noprintBorder resetable" onchange="doReset(event);"><option value="todos">TODOS</option><option value="cajachica" selected>CAJA CHICA</option><option value="viaticos">VIÁTICOS</option></select></td>
                    <th>STATUS:</th>
                    <td class="vAlignCenter"><select id="status" class="padv02 noprintBorder vAlignCenter resetable" onchange="doReset(event);"><option value="todos">TODOS</option><option value="pendiente" selected>PENDIENTE</option><option value="aceptado">AUTORIZADO</option><option value="pagado">PAGADO</option><option value="rechazado">RECHAZADO</option><?php if($esRespalda) { ?><option value="respaldohoy">RESPALDADO HOY</option><?php } ?><option value="respaldado">RESPALDADO</option></select>
                    <IMG id="inverseStatusTrigger" src="imagenes/icons/selectSingle15.png" class="pointer vAlignCenter<?= $esAdmin?"":" hidden" ?> noprint resetable" title="Elegir status">
                    </td>
                    <th>FECHA INI:</th>
                    <td><input type="text" id="fechaIni" readonly class="calendarV padv02 noprintBorder resetable" value="<?= $fmtDay0 ?>" onclick="javascript:show_calendar_widget(this, 'fixRange');" onchange="doReset(event);"/></td>
                </tr>
                <tr>
                    <th colspan="2">BENEFICIARIO:</th>
                    <td colspan="3"><input type="text" id="beneficiario" class="nombreV padv02 noprintBorder resetable" placeholder="TODOS" onchange="doReset(event);"/></td>
                    <th>FECHA FIN:</th>
                    <td><input type="text" id="fechaFin" readonly class="calendarV padv02 noprintBorder resetable" value="<?= $fmtDay ?>" onclick="javascript:show_calendar_widget(this, 'fixRange');" onchange="doReset(event);"/></td>
                </tr>
                <tr class="noprint">
                    <td colspan="7" class="centered"><input type="button" value="BUSCAR" onclick="buscar(event);">
                </tr>
            </tbody>
        </table>
    </div>
    <div id="resultado" class="resultarea centered fontMedium hidden">
    </div>
    <div class="sticky printStatic toBottom relative subfoot basicBG zIdx1000 centered outoff1d noprintBorder">
<?php if ($esRespalda) { ?>
        <input type="button" id="respaldarBtn" value="RESPALDAR" class="hgtBtn hidden noprint marginV7" onclick="respaldar();">
<?php } ?>
        <!-- input type="button" id="printBtn" value="IMPRIMIR" class="hgtBtn hidden noprint marginV7" onclick="submitPrint();" -->
        <span id="numReg" class="hidden abs_w padL20"></span>
        <span id="sumTot" class="hidden abs_e padR20"></span>
    </div>
    <IMG src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const bf=ebyid('bfolio');bf.oninput=doIdEmptyCheck;const ist=ebyid('inverseStatusTrigger');ist.tgtId='status';ist.singleIcon='selectSingle15.png';ist.excludeIcon='selectExclude15.png';ist.onclick=excludeTrigger;ekil(this);">
  </div>
</div>
<script type="text/javascript">
    document.onkeydown=checkKey;
    document.onkeyup=checkKey;
    function checkKey(evt) {
        if (evt.ctrlKey && evt.keyCode==80) {
            //const printBtn=ebyid("printBtn");
            const resultArea=ebyid("resultado");
            //if (!clhas(printBtn,"hidden"))
            if (!clhas(resultArea,"hidden") && resultArea.textContent.trim().length>0)
                submitPrint();
            return eventCancel(evt);
        }
        return true;
    }
</script>
<?php
clog1seq(-1);
clog2end("templates.cajareporte");
