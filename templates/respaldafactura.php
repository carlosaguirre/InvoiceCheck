<?php
clog2ini("templates.respaldafactura");
clog1seq(1);
echo "<!-- Meta Log Level:".getMetaLogLevel()." -->\n";
?>
          <div id="area_central3" class="centered">
            <h1 class="txtstrk">Respaldo de Facturas</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <form name="repfactform" id="repfactform" method="post" target="_self" enctype="multipart/form-data" onsubmit="funclog('FORM', 'INI SUBMIT');submitAjax('Buscar');funclog('FORM', 'END SUBMIT'); return false;">
              
                <input type="hidden" name="menu_accion" value="Respalda Facturas">
                <input type="hidden" name="selectortablename" id="selectortablename" value="facturas">
                <input type="hidden" name="selectorname" id="selectorname" value="respaldafactura">
                <input type="hidden" name="command" value="Buscar">
<?php
if(isset($_POST["command"]) && substr($_POST["command"],0,8)==="BuscaPor") {
      $cmdOrder = substr($_POST["command"],8);
      if (substr($cmdOrder,-3)==="Asc") {
          $cmdOrder = substr($cmdOrder, 0, -3);
          echo "<input type=\"hidden\" name=\"sortOrderValue\" value=\"Asc\">";
      } else if (substr($cmdOrder,-4)==="Desc") {
          $cmdOrder = substr($cmdOrder, 0, -4);
          echo "<input type=\"hidden\" name=\"sortOrderValue\" value=\"Desc\">";
      }
      echo "<input type=\"hidden\" name=\"sortOrderType\" value=\"$cmdOrder\">";
} ?>
                <table class="nohover">
                  <tr class="noApply nohover">
                    <td colspan="2" class="noApply nohover nowrap">
                      <input type="radio" name="tipolista" id="tipocodigo" value="tcodigo" checked onclick="pickType(this);">Codigo
                      <input type="radio" name="tipolista" id="tiporfc"    value="trfc"            onclick="pickType(this);">RFC
                      <input type="radio" name="tipolista" id="tiporazon"  value="trazon"          onclick="pickType(this);">Razon Social
                    </td>
                    <td class="noApply nohover nowrap">Fecha Ini: </td>
                    <td class="noApply nohover nowrap"><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div><input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" onchange="resetForm();" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="toggleClassByClass('refresher', 'hidden');">Empresa: </td>
                    <td class="noApply nohover" id="gpoSelectArea"><div>
                      <select name="grupo[]" id="gpotcodigo"<?= $esAdmin?" class=\"hidden\" multiple=\"multiple\"":"" ?> view="tcodigo"><?= getHtmlOptions($gpoCodigoOpt,$gpoVal) ?></select>
<?php
if ($esAdmin) { ?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaEmpresas();" title="Recalcular Empresas" class="hidden refresher">
<?php
} ?>
                    </div></td>
                    <td class="noApply nohover nowrap">Fecha Fin: </td>
                    <td class="noApply nohover nowrap"><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div><input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" onchange="resetForm();" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" ondblclick="toggleClassByClass('refresher', 'hidden');">Proveedor: </td>
                    
                    <td class="noApply nohover" id="prvSelectArea">
                      <select name="proveedor[]" id="prvtcodigo"<?= $esAdmin?" class=\"hidden\" multiple=\"multiple\"":"" ?> view="tcodigo"><?= getHtmlOptions($prvCodigoOpt, $prvVal) ?></select>
<?php
if ($esAdmin) { ?>
                      <img src="imagenes/icons/descarga6.png" onclick="recalculaProveedores();" title="Recalcular Proveedores" class="hidden refresher">
<?php
} ?>
                    </td>
                    <td class="noApply nohover">Status: </td>
                    <td class="noApply nohover nowrap"><select name="status" id="status" onchange="resetForm();"><option value="">Todas</option><?= getHtmlOptions($sttRespaldadas, $stt) ?></select> &nbsp;&nbsp; <input type="submit" name="Buscar" value="Enviar" onclick="buscaFacturas();"></td>
                  </tr>
                  <tr class="noApply nohover"><td class="noApply nohover" colspan="4"></td></tr>
                </table>
              </form>
              <div id="waitingRoll" class="centered hidden initHidden successHidden">
                <img src="<?=$waitImgName?>" width="360" height="360">
              </div>
              <div id="scrolltablediv900" class="scrolldiv datatable hidden initHidden">
                <table class="noApply">
                  <thead class="centered">
                    <tr>
                      <th>#</th>
                      <th class="nowrap" title="Fecha de Creación del CFDI"><button id="BuscaPorFecha" class="likeTH" onclick="submitAjax('BuscaPorFecha'+getSortOrder('Fecha'));">F.Creaci&oacute;n</button></th>
                      <th id="encEmpresa"><button id="BuscaPorEmpresa" class="likeTH" onclick="submitAjax('BuscaPorEmpresa'+getSortOrder('Empresa'));">Empresa</button></th>
                      <th id="encProveedor"><button id="BuscaPorProveedor" class="likeTH" onclick="submitAjax('BuscaPorProveedor'+getSortOrder('Proveedor'));">Proveedor</button></th>
                      <th name="Tipo de Comprobante" class="shrinkCol"></th>
                      <th><button id="BuscaPorFolio" class="likeTH" onclick="submitAjax('BuscaPorFolio'+getSortOrder('Folio'));">Folio</button></th>
                      <th><button id="BuscaPorTotal" class="likeTH" onclick="submitAjax('BuscaPorTotal'+getSortOrder('Total'));">Total</button></th>
                      <th><button id="BuscaPorStatus" class="likeTH" onclick="submitAjax('BuscaPorStatus'+getSortOrder('Status'));">Status</button></th>
                      <th>XML</th>
                      <th>PDF</th>
                      <th name="Seleccion Directa"><input type="checkbox" id="controlCheck" checked></th>
                    </tr>
                  </thead>
                  <tbody id="dialog_tbody" class="centered">
                  </tbody>
                </table>
              </div>
              <div id="respaldaFooter">
                <table class="datatable hidden initHidden noApply">
                  <tr>
                    <td class="centered">
                      <input type="button" name="respaldarAvance" id="respaldarAvance" value="Respaldo" onclick="respaldoDirecto();">
                      <span id="countBackup" class="abs_e lnHgt26"></span>
                    </td>
                  </tr>
                </table>
                <table class="undatatable initVisible noApply">
                  <tr>
                    <td class="centered">
                      <span id="unCountBackup" class="abs_e lnHgt26"></span>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
         </div>  <!-- FIN BLOQUE USUARIO -->
<?php
clog1seq(-1);
clog2end("templates.respaldafactura");
