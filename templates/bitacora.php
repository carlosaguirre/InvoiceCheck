<?php
clog2ini("templates.bitacora");
clog1seq(1);
?>
          <div id="area_central_bitacora" class="central">
            <h1 class="txtstrk">Bitácora</h1>
            <div id="resultarea_base" class="resultarea nocenter">
              <form name="formaBitacora" id="formaBitacora" method="post" target="_self" enctype="multipart/form-data" onsubmit="submitAjax('Buscar'); return false;">
                <input type="hidden" name="menu_accion" value="Bitacora">
                <input type="hidden" name="selectortablename" id="selectortablename" value="proceso">
                <input type="hidden" name="selectorname" id="selectorname" value="bitacora">
                <input type="hidden" name="command" value="Buscar">
                <table class="nohover">
                  <tr class="noApply nohover">
                    <td class="noApply nohover">Código Usuario: </td>
                    <td class="noApply nohover"><input type="text" name="codigoUsuario" id="codigoUsuario" onchange="buscaUsuario(this.value);"></td>
                    <td class="noApply nohover">Fecha Ini: </td>
                    <td class="noApply nohover"><input id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this);" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" name="nombreUsuario" id="nombreUsuario" colspan="2">&nbsp;</td>
                    <td class="noApply nohover">Fecha Fin: </td>
                    <td class="noApply nohover"><input id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this);" readonly></td>
                  </tr>
                  <tr class="noApply nohover">
                    <td class="noApply nohover" colspan="4" class="centered"><input type="submit" name="Buscar" value="Enviar" onclick="buscaProcesos();"></td>
                  </tr>
                </table>
              </form>
            </div>
          </div>
<?php
clog1seq(-1);
clog2end("templates.bitacora");
