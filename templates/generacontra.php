<?php
clog2ini("templates.generacontra");
clog1seq(1);
?>
          <div id="area_central_gencr" class="centered">
            <h1 class="txtstrk">Generar Contra Recibos</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <form name="gencontraform" id="gencontraform" method="post" target="_self" enctype="multipart/form-data" onsubmit="submitAjax('Buscar'); return false;">
                <input type="hidden" name="menu_accion" value="Generar Contra Recibos">
                <input type="hidden" name="selectortablename" id="selectortablename" value="facturas">
                <input type="hidden" name="selectorname" id="selectorname" value="generacontra">
                <input type="hidden" name="command" id="command" value="Buscar">
                <table class="nohover noApply centered widmin">
                  <tr class="noApply nohover">
                    <td class="noApply nohover centered fontSmall" colspan="2">
                      <input type="radio" name="tipolista" id="tipocodigo" value="codigo" class="wid8px marT0i vAlignCenter" checked onclick="pickType(this);"> Codigo
                      <input type="radio" name="tipolista" id="tiporfc"    value="rfc"    class="wid8px marT0i vAlignCenter"         onclick="pickType(this);"> RFC
                      <input type="radio" name="tipolista" id="tiporazon"  value="razon"  class="wid8px marT0i vAlignCenter"         onclick="pickType(this);"> Razon Social
                    </td>
                    <td class="noApply nohover" colspan="2"></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover nowrap lefted" ondblclick="toggleClassByClass('refresher', 'hidden');">Empresa: &nbsp;</td>
                    <td class="noApply nohover nowrap lefted" id="gpoSelectArea">
                      <select name="grupo" id="gpoOpt" class="fixedSelect" type="codigo"><option value="" codigo="Todas" rfc="Todas" razon="Todas">Todas</option><?= getHtmlOptions($gpoIdOpt, (count($gpoIdOpt)==1?key($gpoIdOpt):$defaultGpoId)) ?></select>
<?php
if (validaPerfil("Administrador")) { //||validaPerfil("Gestor")||validaPerfil("Sistemas")||validaPerfil("Compras")) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaEmpresas();" title="Recalcular Empresas" class="hidden refresher">
<?php
}
?>
                      &nbsp;
                    </td>
                    <td class="noApply nohover nowrap lefted">Fecha Ini: &nbsp;</td>
                    <td class="noApply nohover nowrap lefted"><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div>&nbsp;<input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover nowrap lefted" ondblclick="toggleClassByClass('refresher', 'hidden');">Proveedor: &nbsp;</td>

                    <td class="noApply nohover nowrap lefted" id="prvSelectArea">
                      <select name="proveedor" id="prvOpt" class="fixedSelect" type="codigo"><option value="" codigo="Todas" rfc="Todas" razon="Todas">Todos</option><?= getHtmlOptions($prvIdOpt, (count($prvIdOpt)==1?key($prvIdOpt):$defaultPrvId)) ?></select>
<?php
if (validaPerfil("Administrador")) { //||validaPerfil("Gestor")||validaPerfil("Sistemas")||validaPerfil("Compras")) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaProveedores();" title="Recalcular Proveedores" class="hidden refresher">
<?php
}
?>
                      &nbsp;
                    </td>
                    <td class="noApply nohover nowrap lefted">Fecha Fin: &nbsp;</td>
                    <td class="noApply nohover nowrap lefted"><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div>&nbsp;<input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <?php /* <td class="noApply nohover" colspan="2">Status: </td>
                    <td class="noApply nohover">
                      <select name="status" id="status" class="fixedSelect">< ? = getHtmlOptions($statusList, $selectedStatus) ? ></select></td> */ ?>
                    <td colspan="4" class="noApply nohover centered"><input type="submit" name="Buscar" value="Enviar" onclick="ebyid('command').value='Buscar';" autofocus></td>
                  </tr>
                  <tr class="noApply nohover"><td class="noApply nohover" colspan="4"></td></tr>
                </table>
              <div id="waitRoll" class="gencr centered hidden"><img src="<?=$waitImgName?>"></div>
              <div id="scrolltablediv900" class="scrolldiv"><table class="datatable generacontra hidden noApply">
                <thead>
                  <tr class="padv3"><th>#</th><th>Fecha</th><th class="maxWid140">Proveedor</th><th>Empresa</th><th class="shrinkCol">&nbsp;</th><th>Folio</th><th>Pedido</th><th class="centered">Total</th><th class="centered" title="Entrada de Almacen">EA</th><th>Status</th><th><input type="checkbox" id="checkall" name="checkall" checked onclick="doAllChecks(this.checked)"></th></tr>
                </thead>
                <tbody id="dialog_tbody">

                </tbody>
              </table></div>
              </form>
              <div id="scrolltablediv_gencr" class="scrolldiv hidden"><table class="datatable generacontra hidden noApply">
                <thead>
                  <tr><th>#</th><th>Fecha</th><th class="maxWid140">Proveedor</th><th>Empresa</th><th>Folio</th><th>Total</th><th>Documento</th></tr>
                </thead>
                <tbody id="result_tbody">

                </tbody>
              </table></div>
              <div id="acceptbuttondiv">
                <table class="datatable hidden noApply">
                  <!-- tr>
                    <td class="centered">
                      Fecha programada de Pago : <input type="text" name="fechapago" id="fechapago" value="" class="calendar" onclick="javascript:show_calendar_widget(this);" readonly>
                    </td>
                  </tr -->
                  <tr>
                    <td class="invisible countDisplay padding5 shrinkCol lefted"></td>
                    <td class="centered">
                      <div class="relative hei24">
                        <input type="button" name="seleccionar" id="seleccionar" value="Generar" onclick="if(submitAjax('Generar')) getParentTable(this).classList.add('hidden'); ">
                        <img id="stopBtn" src="imagenes/icons/stop20.png" class="abs_e btn20 marginH2 hidden" onclick="if(this.xhr) this.xhr.abort();">
                      </div>
                    </td>
                    <td class="countDisplay padding5 shrinkCol righted"></td>
                  </tr>
                </table>
                <div id="emptySection" class="righted hidden padding5"></div>
              </div>
            </div>
         </div>  <!-- FIN BLOQUE USUARIO -->
<?php
clog1seq(-1);
clog2end("templates.generacontra");
