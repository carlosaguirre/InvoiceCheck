<?php
clog2ini("templates.generatxt");
clog1seq(1);

//clog(arr2str($_POST), 3);
//reset($gpoCodigoOpt);
//reset($gpoRFCOpt);
//reset($gpoRazSocOpt);
//reset($prvCodigoOpt);
//reset($prvRFCOpt);
//reset($prvRazSocOpt);
?>
          <div id="area_central3" class="centered">
            <h1 class="txtstrk">Generar TXT</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <form name="repfactform" id="repfactform" method="post" target="_self" enctype="multipart/form-data" onsubmit="submitAjax('Buscar'); return false;">
              
                <input type="hidden" name="menu_accion" value="Generar TXT">
                <input type="hidden" name="selectortablename" id="selectortablename" value="facturas">
                <input type="hidden" name="selectorname" id="selectorname" value="generatxt">
                <input type="hidden" name="command" value="Buscar">
                <table class="nohover">
                  <tr class="noApply nohover">
                    <td colspan="2" class="noApply nohover nowrap">
                      <input type="radio" name="tipolista" id="tipocodigo" value="tcodigo" checked onclick="pickType(this);">Codigo
                      <input type="radio" name="tipolista" id="tiporfc"    value="trfc"            onclick="pickType(this);">RFC
                      <input type="radio" name="tipolista" id="tiporazon"  value="trazon"          onclick="pickType(this);">Razon Social
                    </td>
                    <td class="noApply nohover nowrap">Fecha Ini: </td>
                    <td class="noApply nohover"><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div><input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" onchange="resetForm();" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="toggleClassByClass('refresher', 'hidden');">Empresa: </td>
                    <td class="noApply nohover" id="gpoSelectArea">
                      <select name="grupo" id="gpotcodigo" onchange="selectedItem('gpo');"               ><option value="">Selecciona una...</option><?= getHtmlOptions($gpoCodigoOpt, (count($gpoCodigoOpt)==1?key($gpoCodigoOpt):"")) ?></select>
                      <select name="grupo" id="gpotrfc"    onchange="selectedItem('gpo');" class="hidden"><option value="">Selecciona una...</option><?= getHtmlOptions($gpoRFCOpt   , (count($gpoRFCOpt)==1?key($gpoRFCOpt):"")) ?></select>
                      
                      <select name="grupo" id="gpotrazon"  onchange="selectedItem('gpo');" class="hidden"><option value="">Selecciona una...</option><?= getHtmlOptions($gpoRazSocOpt, (count($gpoRazSocOpt)==1?key($gpoRazSocOpt):"")) ?></select>
<?php
if ($esAdmin) { //||validaPerfil("Gestor")||validaPerfil("Sistemas")||validaPerfil("Compras")) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaEmpresas();" title="Recalcular Empresas" class="hidden refresher">
<?php
}
?>
                    </td>
                    <td class="noApply nohover nowrap">Fecha Fin: </td>
                    <td class="noApply nohover"><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div><input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" onchange="resetForm();" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="toggleClassByClass('refresher', 'hidden');">Proveedor: </td>
                    
                    <td class="noApply nohover" id="prvSelectArea">
                      <select name="proveedor" id="prvtcodigo" onchange="selectedItem('prv');"               ><option value="">Todos</option><?= getHtmlOptions($prvCodigoOpt, (count($prvCodigoOpt)==1?key($prvCodigoOpt):"")) ?></select>
                      
                      <select name="proveedor" id="prvtrfc"    onchange="selectedItem('prv');" class="hidden"><option value="">Todos</option><?= getHtmlOptions($prvRFCOpt   , (count($prvRFCOpt)==1?key($prvRFCOpt):"")) ?></select>
                      <select name="proveedor" id="prvtrazon"  onchange="selectedItem('prv');" class="hidden"><option value="">Todos</option><?= getHtmlOptions($prvRazSocOpt, (count($prvRazSocOpt)==1?key($prvRazSocOpt):"")) ?></select>
                      
<?php
if ($esAdmin) { //||validaPerfil("Gestor")||validaPerfil("Sistemas")||validaPerfil("Compras")) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaProveedores();" title="Recalcular Proveedores" class="hidden refresher">
<?php
}
?>
                    </td>
                    <td class="noApply nohover">Status: </td>
                    <td class="noApply nohover nowrap"><select name="status" id="status" onchange="resetForm();"><option value="">Todas</option><?= getHtmlOptions($sttExportadas, $stt) ?></select> &nbsp;&nbsp; <input type="submit" name="Buscar" value="Enviar" onclick="buscaFacturas();"></td>
                  </tr>
                  <tr class="noApply nohover"><td class="noApply nohover" colspan="4"></td></tr>
                </table>
              </form>
              <div id="scrolltablediv900" class="scrolldiv">
                <table class="datatable noApply hidden initHidden">
                  <thead class="centered">
                    <tr>
                      <th>#</th>
                      <th class="nowrap" title="Fecha de Creación del CFDI">F.Creaci&oacute;n</th>
                      <th id="encProveedor">Proveedor</th>
                      <th>Folio</th>
                      <th>Total</th>
                      <th>Status</th>
                      <th>XML</th>
                      <th>PDF</th>
                      <th name="Seleccion Directa"><input type="checkbox" id="controlCheck" checked></th>
                    </tr>
                  </thead> 
                  <tbody id="dialog_tbody" class="centered">

                  </tbody>
                </table>
              </div>
              <div id="exportbuttondiv">
                <table class="datatable fullwidth noApply">
                  <tr class="noApply">
                    <td class="centered noApply">
<?php
/*if ($esAdmin||$esSistemas) { 
  require_once "clases/Avance.php";
  $avnObj = new Avance();
  echo $avnObj->getLoginHtmlForm();
}*/ ?>
                      <input type="button" name="exportarDirecto" id="exportarDirecto" value="Exportar" class="datatable hidden initHidden" onclick="exportarDirecto();">
                      <span id="countBackup" class="abs_e"></span>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>  <!-- FIN BLOQUE USUARIO -->
<?php
clog1seq(-1);
clog2end("templates.generatxt");
