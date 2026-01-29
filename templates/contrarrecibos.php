<?php
if(!hasUser()) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("templates.contrarrecibos");
clog1seq(1);
clog(arr2str($_POST),3);
reset($gpoCodigoOpt);
reset($gpoRFCOpt);
reset($gpoRazSocOpt);
reset($prvCodigoOpt);
reset($prvRFCOpt);
reset($prvRazSocOpt);
?>
          <div id="area_central4" class="centered">
            <h1 class="txtstrk">Consulta de Contra Recibos</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <form name="ackfactform" id="ackfactform" method="post" target="_self" enctype="multipart/form-data" onsubmit="submitAjax('Buscar'); return false;">
                <input type="hidden" name="menu_accion" value="Contra Recibos">
                <input type="hidden" name="selectortablename" id="selectortablename" value="contrarrecibos">
                <input type="hidden" name="selectorname" id="selectorname" value="contrarrecibos">
                <input type="hidden" name="command" value="Buscar">
                <table class="nohover">
                  <tr class="noApply nohover">
                    <td colspan="2" class="noApply nohover nowrap">
                      <input type="radio" name="tipolista" id="tipocodigo" value="tcodigo" checked onclick="pickType(this);">Codigo
                      <input type="radio" name="tipolista" id="tiporfc"    value="trfc"            onclick="pickType(this);">RFC
                      <input type="radio" name="tipolista" id="tiporazon"  value="trazon"          onclick="pickType(this);">Razon Social
                    </td>
                    <td class="noApply nohover nowrap">Fecha Ini: </td>
                    <td class="noApply nohover folioRelated"><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div><input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="toggleClassByClass('refresher', 'hidden');">Empresa: </td>
                    <td class="noApply nohover" id="gpoSelectArea">
                      <select name="grupo" id="gpotcodigo" onchange="selectedItem('gpo');"               ><option value="">Todas</option><?= getHtmlOptions($gpoCodigoOpt, (count($gpoCodigoOpt)==1?key($gpoCodigoOpt):"")) ?></select>
                      
                      <select name="grupo" id="gpotrfc"    onchange="selectedItem('gpo');" class="hidden"><option value="">Todas</option><?= getHtmlOptions($gpoRFCOpt   , (count($gpoRFCOpt)==1?key($gpoRFCOpt):"")) ?></select>
                      
                      <select name="grupo" id="gpotrazon"  onchange="selectedItem('gpo');" class="hidden"><option value="">Todas</option><?= getHtmlOptions($gpoRazSocOpt, (count($gpoRazSocOpt)==1?key($gpoRazSocOpt):"")) ?></select>
                      
<?php
if ($esAdmin) { //||validaPerfil("Gestor")||$esSistemas||$esCompras) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaEmpresas();" title="Recalcular Empresas" class="hidden refresher">
<?php
}
?>
                    </td>
                    <td class="noApply nohover nowrap">Fecha Fin: </td>
                    <td class="noApply nohover folioRelated"><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div><input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="toggleClassByClass('refresher', 'hidden');">Proveedor: </td>
                    
                    <td class="noApply nohover" id="prvSelectArea">
                      <select name="proveedor" id="prvtcodigo" onchange="selectedItem('prv');"               ><option value="">Todos</option><?= getHtmlOptions($prvCodigoOpt, (count($prvCodigoOpt)==1?key($prvCodigoOpt):"")) ?></select>
                      
                      <select name="proveedor" id="prvtrfc"    onchange="selectedItem('prv');" class="hidden"><option value="">Todos</option><?= getHtmlOptions($prvRFCOpt   , (count($prvRFCOpt)==1?key($prvRFCOpt):"")) ?></select>
                      <select name="proveedor" id="prvtrazon"  onchange="selectedItem('prv');" class="hidden"><option value="">Todos</option><?= getHtmlOptions($prvRazSocOpt, (count($prvRazSocOpt)==1?key($prvRazSocOpt):"")) ?></select>
                      
<?php
if ($esAdmin) { //||validaPerfil("Gestor")||$esSistemas||$esCompras) {
?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaProveedores();" title="Recalcular Proveedores" class="hidden refresher">
<?php
}
?>
                    </td>
                    <td class="noApply nohover nowrap">Status: </td>
                    <td class="noApply nohover folioRelated"><select name="status" id="status"><?= $statusOptList ?></select></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover nowrap">Folio: </td>
                    <td class="noApply nohover nowrap"><input type="text" id="folioCR" name="folio" class="folioV" onkeypress="return validFolios(event);" onchange="folioFix(this.value);"></td>
                    <td colspan="2" class="noApply nohover"><input type="submit" name="Buscar" value="Enviar" onclick="buscaFacturas();"></td>
                  </tr>
                </table>
              </form>
              <div id="scrolltablediv900" class="scrolldiv"><table class="datatable noApply">
                <thead class="vAlignMiddle">
                  <tr><th class="centered"><span>#</span></th><th class="centered"><div class="relative"><span>Folio</span>&nbsp;<button type="button" id="contraViewBtn" class="hidden" onclick="switchCFView();">+</button></div></th><th><div class="relative"><span>Proveedor</span>&nbsp;<button type="button" id="contraRscpBtn" class="hidden colPrv" altval="RS" onclick="switchPrvVal();">CP</button></div></th><th class="centered hcEF ellipsisCel fixed77">Fecha Revisi&oacute;n</th><!-- th>Fecha Pago</th --><th class="righted padr10 hcEF ellipsisCel fixed77">Monto Total</th><th id="docsCol" class="centered">Contrarrecibo</th></tr>
                </thead>
                <tbody id="dialog_tbody">
<?php
if ($esProveedor||$autorizaCR) include "selectores/contrarrecibos.php";
?>
                </tbody>
              </table><div id="inner" class="all_space"></div></div>
              <div id="contrafooter" class="footer20">
                <TABLE id="contrafoottb" class="noApply">
                  <TR>
                    <TD class="noShrink lefted boldValue"><div id="regNum" class="lefted"></div></TD>
                    <TD><div id="lftSide">&nbsp;</div></TD>
                    <TD class="centered nowrap">
                      <BUTTON id="authBtn" class="invisible highlight" type="button" onclick="sendAuth();" onmouseover="clrem(this,'highlight');">AUTORIZAR</BUTTON> <label id="sumChkLbl"></label>
                    </TD>
                    <TD class="righted"><div id="sumTot" class="righted boldValue"></div></TD>
                    <TD><div id="rgtSide">&nbsp;</div></TD>
                  </TR>
                </TABLE>
            </div>
         </div>  <!-- FIN BLOQUE USUARIO -->
<?php
clog1seq(-1);
clog2end("templates.contrarrecibos");
