<?php
  clog2ini("templates.reportes");
  clog1seq(1);
?>
          <div id="area_central_reportes" class="central">
            <h1 class="txtstrk">Reportes Acumulados</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <div id="reporte_filtros">
                <form name="formaReportes" id="formaReportes" method="post" target="_self" enctype="multipart/form-data" onsubmit="submitAjax();  return false;">
                  <input type="hidden" name="menu_accion" value="Reportes">
                  <input type="hidden" name="command" id="command" value="">
                  <table class="noApply nohover">
                    <tr class="noApply nohover">
                      <td class="noApply nohover">Empresa: </td>
                      <td class="noApply nohover"><select class="noprintBorder" name="empresa" id="empresa"><option value="resumen">Resumen</option><option value="desglose">Desglose</option><?= getHtmlOptions($gpoCodigoOpt, (count($gpoCodigoOpt)==1?key($gpoCodigoOpt):"")) ?></select></td>
                      <td class="noApply nohover shrinkCol">Fecha Ini: </td>
                      <td class="noApply nohover nowrap"><input id="fechaIni" name="fechaIni" value="<?= $fmtDay0 ?>" class="calendar noprintBorder" onclick="javascript:show_calendar_widget(this, 'fixRange');" readonly></td>
                      <td class="noApply nohover shrinkCol noprintBorder">Moneda: </td>
                      <td class="noApply nohover nowrap"><select name="moneda" id="moneda" class="noprintBorder"><option value="todas">Ambas</option><option value="pesos">Pesos</option><option value="dolares">Dólares</option></select></td>
                    </tr>
                    <tr class="noApply nohover">
                      <td class="noApply nohover">Importe: </td>
                      <td class="noApply nohover"><select name="importe" id="importe" class="noprintBorder"><option value="total">Total</option><option value="subtotal">SubTotal</option></select></td>
                      <td class="noApply nohover shrinkCol">Fecha Fin: </td>
                      <td class="noApply nohover nowrap"><input id="fechaFin" name="fechaFin" value="<?= $fmtDay ?>" class="calendar noprintBorder" onclick="javascript:show_calendar_widget(this, 'fixRange');" readonly></td>
                      <td class="noApply nohover noprint">&nbsp;</td>
                      <td class="noApply nohover lefted noprint" colspan="2"><input type="submit" name="Buscar" value="Enviar" onclick="setCommand('Buscar');"></td>
                    </tr>
                  </table>
                </form>
              </div>
              <div id="reporte_contenido" class="hidden onprintTopMargin10">
                <div id="reporte_accionesH" class="noprint"><img src="imagenes/prntricon32.png" class="pointer unmarked" onclick="mark(this); printElem('reporte_resultado');"/><img src="imagenes/excelicon32.png" class="pointer unmarked" onclick="downloadFile();"/></div>
                <div id="reporte_resultado"></div>
                <div id="reporte_accionesF" class="noprint"><img src="imagenes/prntricon32.png" class="pointer unmarked" onclick="mark(this); printElem('reporte_resultado');"/><img src="imagenes/excelicon32.png" class="pointer unmarked" onclick="downloadFile();"/></div>
              </div>
            </div>
          </div>
<?php
clog1seq(-1);
clog2end("templates.reportes");
