<?php
clog2ini("templates.reportefactura");
clog1seq(1);
?>
          <div id="area_central2" class="centered">
            <h1 class="txtstrk">Reporte de Facturas</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <form name="repfactform" id="repfactform" method="post" target="_self" enctype="multipart/form-data" onsubmit="submitAjax('Buscar'); return false;">
              
                <input type="hidden" name="menu_accion" value="Reporte Facturas">
                <input type="hidden" name="selectortablename" id="selectortablename" value="facturas">
                <input type="hidden" name="selectorname" id="selectorname" value="reportefactura">
                <input type="hidden" name="command" value="Buscar">
                <table class="nohover">
                  <tr class="noApply nohover">
                    <td colspan="2" class="noApply nohover nowrap invisiblePrint">
                      <input type="radio" name="tipolista" id="tipocodigo" value="tcodigo" checked onclick="pickType(this);">Codigo
                      <input type="radio" name="tipolista" id="tiporfc"    value="trfc"            onclick="pickType(this);">RFC
                      <input type="radio" name="tipolista" id="tiporazon"  value="trazon"          onclick="pickType(this);">Razon Social
                    </td>
                    <td class="noApply nohover nowrap">Fecha Ini: </td>
                    <td class="noApply nohover nowrap"><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?> noprint"></div><input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="clfix('reloadGRP', 'invisible');">Empresa: </td>
                    <td class="noApply nohover" id="gpoSelectArea">
                      <select name="grupo" id="gpotcodigo" onchange="selectedItem('gpo');"               ><option value="">Todas</option><?= getHtmlOptions($gpoCodigoOpt, (count($gpoCodigoOpt)==1?key($gpoCodigoOpt):"")) ?></select>
                      
                      <select name="grupo" id="gpotrfc"    onchange="selectedItem('gpo');" class="hidden"><option value="">Todas</option><?= getHtmlOptions($gpoRFCOpt   , (count($gpoRFCOpt)==1?key($gpoRFCOpt):"")) ?></select>
                      
                      <select name="grupo" id="gpotrazon"  onchange="selectedItem('gpo');" class="hidden"><option value="">Todas</option><?= getHtmlOptions($gpoRazSocOpt, (count($gpoRazSocOpt)==1?key($gpoRazSocOpt):"")) ?></select>
                      
<?php
if ($_esSistemas) { //||validaPerfil("Gestor")||$_esSistemas||$_esCompras) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaEmpresas();" title="Recalcular Empresas" id="reloadGRP" class="invisible">
<?php
}
?>
                    </td>
                    <td class="noApply nohover nowrap">Fecha Fin: </td>
                    <td class="noApply nohover nowrap"><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div><input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="clfix('reloadPRV', 'invisible');">Proveedor: </td>
                    
                    <td class="noApply nohover" id="prvSelectArea">
                      <select name="proveedor" id="prvtcodigo" onchange="selectedItem('prv');"               ><option value="">Todos</option><?= getHtmlOptions($prvCodigoOpt, (count($prvCodigoOpt)==1?key($prvCodigoOpt):"")) ?></select>
                      
                      <select name="proveedor" id="prvtrfc"    onchange="selectedItem('prv');" class="hidden"><option value="">Todos</option><?= getHtmlOptions($prvRFCOpt   , (count($prvRFCOpt)==1?key($prvRFCOpt):"")) ?></select>
                      <select name="proveedor" id="prvtrazon"  onchange="selectedItem('prv');" class="hidden"><option value="">Todos</option><?= getHtmlOptions($prvRazSocOpt, (count($prvRazSocOpt)==1?key($prvRazSocOpt):"")) ?></select>
                      
<?php
if ($_esSistemas||$_esCompras) { //||validaPerfil("Gestor")||$_esSistemas||$_esCompras) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaProveedores();" title="Recalcular Proveedores" id="reloadPRV" class="invisible">
<?php
}
?>
                    </td>
                    <td class="noApply nohover">Status: </td>
                    <td class="noApply nohover"><select name="status" id="status" onchange="ajustaStatus(event);"><option value="">Todas</option><?= getHtmlOptions($sttPendientes, $stt) ?></select> &nbsp;&nbsp; <input type="submit" name="Buscar" value="Enviar" onclick="buscaFacturas();"></td>
                  </tr>
                  <tr class="noApply nohover"><td class="noApply nohover" colspan="4"></td></tr>
                </table>
                <input type="file" name="attpdffile" id="attpdffile" accept=".pdf" class="hidden" onchange="addPDFFile();">
                <input type="file" name="eafile" id="eafile" accept=".pdf" class="hidden eafile" onchange="addEAFile();">
              </form>
              <div id="waitRoll" class="centered hidden"><img src="<?=$waitImgName?>"></div>
              <div id="scrolltablediv900" class="scrolldiv datatable hidden">
                <table class="noApply">
                  <thead class="centered semiPrint4">
                    <tr>
                      <th>#</th>
                      <th class="nowrap" title="Fecha de Creación del CFDI">F.Creaci&oacute;n</th>
                      <th class="nowrap" title="Fecha de Captura en el Portal" id="fechaRelevante">F.Captura</th>
                      <th id="encEmpresa">Empresa</th>
                      <th id="encProveedor">Proveedor</th>
                      <th name="Tipo de Comprobante" class="shrinkCol" value="Tipo"></th>
                      <th class="padv03">Folio</th>
                      <th id="encRemision" class="colRemision padv03 hidden" value2="UUID">Remision</th>
                      <th value2="Moneda">Total</th>
                      <th>Status</th>
<?php if($modificaProc) { ?>
                      <th class="noprint">Procesar</th>
<?php } ?>
                      <th class="noprint">&nbsp;Documentos&nbsp;</th>
<?php if($modificaProc && false) { ?>
                      <th class="noprint">Rechazar</th>
<?php } ?>
<?php if($_esSistemas) { ?>
                      <th class="noprint">Eliminar</th>
<?php } ?>
                    </tr>
                  </thead>
                  <tbody id="dialog_tbody" class="centered">

                  </tbody>
                </table><br>
              </div>
              <div id="footer" class="footer20"><img id="stopBtn" src="imagenes/icons/stop20.png" class="btn20 footcore hidden" title="Detener Búsqueda!" onclick="if(this.xhr) this.xhr.abort();"><img id="continueBtn" src="imagenes/icons/refresh.png" class="btn20 footcore hidden" onclick="continueSubmit(event);"><img id="downloadBtn" src="imagenes/icons/csvIcon3.png" class="btn20 footcore hidden" onclick="exportToCSV(event);"><img id="extraBtn" src="imagenes/icons/repair.png" class="btn20 footcore hidden" title="Reparar CFDIs" onclick="repairCFDIs(event);"></div>
            </div>
          </div>
          <!-- FIN BLOQUE USUARIO -->
<?php
require "templates/svgfilterurl.php";
if (($_POST["action"]??"")==="redirect") {
  echo "<script id=\"tmpscrpt\">\n";
  if (isset($_POST["gpotcodigo"]))
    echo "ebyid(\"gpotcodigo\").value=\"$_POST[gpotcodigo]\";\nselectedItem('gpo');\n";
  if (isset($_POST["prvtcodigo"]))
    echo "ebyid(\"prvtcodigo\").value=\"$_POST[prvtcodigo]\";\nselectedItem('prv');\n";
  if (isset($_POST["fechaInicio"]))
    echo "ebyid(\"fechaInicio\").value=\"$_POST[fechaInicio]\";\n";
  if (isset($_POST["fechaFin"]))
    echo "ebyid(\"fechaFin\").value=\"$_POST[fechaFin]\";\n";
  if (isset($_POST["status"]))
    echo "ebyid(\"status\").value=\"$_POST[status]\";\n";
  echo "buscaFacturas();\n";
  //echo "ebyid(\"repfactform\").submit();\n";
  echo "submitAjax('Buscar');";
  echo "ekil(ebyid(\"tmpscrpt\"));";
  echo "</script>";
}
clog1seq(-1);
clog2end("templates.reportefactura");
