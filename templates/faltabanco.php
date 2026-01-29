<?php
clog2ini("templates.faltabanco");
clog1seq(1);
/*if ($user->proveedor->status==="activo") {
  unset($_SESSION['MENSAJE_INICIAL']);
  include "templates/login.php";
} else {*/
?>
          <div id="area_actualizabanco" class="central">
            <h1 class="txtstrk">Actualizar Datos</h1>
            <div id="resultarea_base" class="resultarea">
              <P class="width80 centered lefted">Estimado <?= $userName ?>, le solicitamos verificar los siguientes datos y capturar los faltantes, son muy importantes para realizar los pagos sin inconvenientes.</P>
              <form method="post" name="forma_bco_miss" id="forma_bco_miss" target="_self" enctype="multipart/form-data">
                <br><table class="width80i centered nowrap"><tbody>
                  <tr class="noApply nohover"><th class="lefted">Correo electrónico: </th><td class="lefted nohover"><input type="email" id="user_email" name="user_email" size="35" value="<?= $userEmail ?>"></td></tr>
                  <tr class="noApply nohover"><th class="lefted">Banco: </th><td class="lefted nohover"><input type="text" id="prov_bank" name="prov_bank" size="35" value="<?= $provBank ?>"></td></tr>
                  <tr class="noApply nohover"><th class="lefted">RFC del Banco: </th><td class="lefted nohover"><input type="text" id="prov_bankrfc" name="prov_bankrfc" size="12" value="<?= $bankRFC ?>"></td></tr>
                  <tr class="noApply nohover"><th class="lefted">Cuenta CLABE: </th><td class="lefted nohover"><input type="text" id="prov_account" name="prov_account" size="35" value="<?= $provAccount ?>" title="La cuenta debe tener solamente números y debe estar visible en la carátula del estado de cuenta"></td></tr>
                  <tr class="noApply nohover"><th class="lefted" title="Seleccione un archivo en formato PDF que contenga la carátula de un estado de cuenta reciente con CLABE visible">Carátula Edo.Cta.:<img src="imagenes/icons/assistance.png" class="vAlignCenter btnFX" id="sampleBtn" width="20" height="20" title="Ejemplo de Edo.Cta."> </th><td class="lefted nohover"><input type="file" id="prov_receipt" name="prov_receipt" size="35" accept=".pdf" title="Presione para agregar Documento"<?= $prvRcptClss ?>><?= $prvRcptElem ?></td></tr>
                  <tr class="noApply nohover"><th class="lefted top" title="Seleccione un archivo en formato PDF que contenga la Opinión del Cumplimiento de Obligaciones Fiscales Vigente generado por el SAT">Opini&oacute;n Cumplim.: <a href="https://www.sat.gob.mx/consultas/20777/consulta-tu-opinion-de-cumplimiento-de-obligaciones-fiscales" target="SAT" class="noborder"><img src="imagenes/icons/sat.gif" class="vAlignCenter btnFX" id="satBtn" width="20" height="20" title="Enlace al SAT para Generar Documento de Opinión del Cumplimiento"></a></th><td class="lefted nohover"><input type="file" id="prov_opinion" name="prov_opinion" size="35" accept=".pdf" title="Presione para agregar Documento"<?= $prvOpiClss ?>><?= $prvOpiElem ?><?php /*input type="hidden" id="nuevaFechaVencimiento" name="nuevaFechaVencimiento" value="< ? = $dueDay ? >">*/ ?></td></tr>
                  <?php /* tr class="noApply nohover"><th class="lefted">Emisión de Opinión: </th><td class="lefted nohover"><input type="text" id='fechaOpinion' name="fechaOpinion" value="< ? = $ fmtDay ? >" class="calendar vAlignCenter" onclick="javascript:show_calendar_widget(this,'verifyDate');" readonly> <span class="grayed italic fontSmall vAlignCenter">( dd / mm / yy )</span></td></tr*/ ?>
                  <?php /*tr class="noApply nohover"><th class="lefted">Vencimiento de Opinión: </th><td class="lefted nohover"><input type="text" id="fechaVencimiento" name="fechaVencimiento" value="< ? = $ dueDay ? >" class="calendar vAlignCenter" readonly> <span class="grayed italic fontSmall vAlignCenter">( dd / mm / yy )</span></td></tr*/ ?>
                  <tr class="noApply nohover"><td colspan="2" class="centered nohover"><button type="submit" id="actualizaProveedor" name="actualizaProveedor" value="faltabanco">Confirmar</button></td></tr>
                </tbody></table>
              </form>

              <?php // ELIMINAR ESTA SECCION, PUES SOLO DESPLIEGA DATOS RECIBIDOS POR METODO POST Y SOLO ES UTIL PARA EL DESARROLLADOR
              /*
              $first=true;
              foreach ($_POST as $key => $value) {
                if ($first) { echo "<HR><H2>POST:</H2><TABLE class=\"width80i centered\">"; $first=false; }
                echo "<TR class=\"noApply nohover\"><TH class=\"shrinkCol lefted\">$key = </TH><TD class=\"lefted nohover\">'$value'</TD></TR>";
              }
              if (!$first) echo "</TABLE>";
              */
              ?>
              <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="let a=ebyid('prov_account');a.oninput=accountCheck;a.onpaste=accountCheck;let e=ebyid('prov_receipt');e.onchange=function(){checkFile(e);};let o=ebyid('prov_opinion');o.onchange=function(){checkFile(o);};let i=ebyid('sampleBtn');i.onclick=function(){viewImageSample('Ejemplo de Car&aacute;tula');};let f=ebyid('forma_bco_miss');f.onsubmit=validaDatos;this.parentNode.removeChild(this);">
            </div>
          </div>

<?php
/*}*/
clog1seq(-1);
clog2end("templates.faltabanco");
