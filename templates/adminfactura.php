<?php
clog2ini("templates.adminfactura");
clog1seq(1);
?>
          <div class="central">
            <h1 class="txtstrk centered">Administrador de Facturas</h1>
            <div id="admfactura_screen" class="noApply">
              <div id="admfactura_top">
                <table class="noApply fullyExpanded">
                  <tr><th colspan="8" class="titlearea">COMPROBANTE FISCAL DIGITAL POR INTERNET</th></tr>
                  <tr>
                    <th class="hcelr title">ID</th>
                    <td class="fixed77"><input id="adm_facturas_id" type="text" class="admreg facturas widfit2 tableKey" <?= isset($_POST["adm_facturas_id"])?" value='$_POST[adm_facturas_id]'":"" ?>></td>
                    <th class="hcelr title" title="Fecha de Creacion de Factura">Alta</th>
                    <td class="fixed77" title="Fecha de Creacion de Factura"><input id="adm_facturas_fechaFactura" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_fechaFactura"])?" value='$_POST[adm_facturas_fechaFactura]'":"" ?>></td>
                    <th class="hcelr title" title="Tipo de Comprobante">TComp</th>
                    <td class="fixed55" title="Tipo de Comprobante"><select id="adm_facturas_tipoComprobante" class="admreg facturas widfit2 invoiceFixed"><?= getHtmlOptions([""=>"Todos","i"=>"Ingreso","e"=>"Egreso","p"=>"Pago"],isset($_POST["adm_facturas_tipoComprobante"])?$_POST["adm_facturas_tipoComprobante"]:"") ?></select></td>
                    <th class="hcelr title">Vers</th>
                    <td class="fixed77"><select id="adm_facturas_version" class="admreg facturas widfit2 invoiceFixed"><?= getHtmlOptions([""=>"Todas","3.2"=>"3.2","3.3"=>"3.3"],isset($_POST["adm_facturas_version"])?$_POST["adm_facturas_version"]:"") ?></select></td>
                  </tr>
                  <tr>
                    <th class="hcelr title">Pedido</th>
                    <td class="fixed77"><input id="adm_facturas_pedido" type="text" class="admreg facturas widfit2"<?= isset($_POST["adm_facturas_pedido"])?" value='$_POST[adm_facturas_pedido]'":"" ?>></td>
                    <th class="hcelr title" title="Fecha de Captura">Captura</th>
                    <td class="fixed77" title="Fecha de Captura"><input id="adm_facturas_fechaCaptura" type="text" class="admreg facturas widfit2"<?= isset($_POST["adm_facturas_fechaCaptura"])?" value='$_POST[adm_facturas_fechaCaptura]'":"" ?>></td>
                    <th class="hcelr title" title="Tipo de Cambio">TCamb</th>
                    <td class="fixed55" title="Tipo de Cambio"><input id="adm_facturas_tipoCambio" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_tipoCambio"])?" value='$_POST[adm_facturas_tipoCambio]'":"" ?>></td>
                    <th class="hcelr title">Serie</th>
                    <td class="fixed77"><input id="adm_facturas_serie" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_serie"])?" value='$_POST[adm_facturas_serie]'":"" ?>></td>
                  </tr>
                  <tr>
                    <th class="hcelr title">Status</th>
                    <td class="fixed77 relative nowrap"><input id="adm_facturas_status" type="text" class="admreg facturas widfit2 withOp"<?= isset($_POST["adm_facturas_status"])?" value='$_POST[adm_facturas_status]'":"" ?> readOnly="1" onclick="showStatusComponent(event);"><span id="adm_facturas_statusop" class="pro11 btnOp abs top5 lft4 zIdx10" onclick="showStatusOpComponent(event);">&in;</span></td>
                    <th class="hcelr title" title="Fecha de Aprobacion">Aprueba</th>
                    <td class="fixed77" title="Fecha de Aprobacion"><input id="adm_facturas_fechaAprobacion" type="text" class="admreg facturas widfit2"<?= isset($_POST["adm_facturas_fechaAprobacion"])?" value='$_POST[adm_facturas_fechaAprobacion]'":"" ?>></td>
                    <th class="hcelr title" title="Forma de Pago">FormP</th>
                    <td class="fixed55" title="Forma de Pago"><input id="adm_facturas_formaDePago" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_formaDePago"])?" value='$_POST[adm_facturas_formaDePago]'":"" ?>></td>
                    <th class="hcelr title">Folio</th>
                    <td class="fixed77"><input id="adm_facturas_folio" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_folio"])?" value='$_POST[adm_facturas_folio]'":"" ?>></td>
                  </tr>
                  <tr>
                    <th class="hcelr title">Stat#</th>
                    <td class="fixed77 relative nowrap"><input id="adm_facturas_statusn" type="text" class="admreg facturas widfit2 withOp righted"<?= isset($_POST["adm_facturas_statusn"])?" value='$_POST[adm_facturas_statusn]'":"" ?> readOnly="1" onclick="showStatusComponent(event);"><span id="adm_facturas_statusnop" class="pro11 btnOp abs top5 lft4 zIdx10" onclick="showStatusOpComponent(event);">&in;</span></td>
                    <th class="hcelr title" title="Fecha de Vencimiento de Pago">Vence</th>
                    <td class="fixed77" title="Fecha de Vencimiento de Pago"><input id="adm_facturas_fechaVencimiento" type="text" class="admreg facturas widfit2"<?= isset($_POST["adm_facturas_fechaVencimiento"])?" value='$_POST[adm_facturas_fechaVencimiento]'":"" ?>></td>
                    <th class="hcelr title" title="Metodo de Pago">MetdP</th>
                    <td class="fixed55" title="Metodo de Pago"><input id="adm_facturas_metodoDePago" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_metodoDePago"])?" value='$_POST[adm_facturas_metodoDePago]'":"" ?>></td>
                    <th class="hcelr title" title="Fecha de Ultima Modificacion">Modif</th>
                    <td class="fixed77" title="Fecha de Ultima Modificacion"><input id="adm_facturas_modifiedTime" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_modifiedTime"])?" value='$_POST[adm_facturas_modifiedTime]'":"" ?>></td>
                          
                  </tr>
                  <tr>
                    <th colspan="4" class="centered titlearea">EMISOR/PROVEEDOR</th><th colspan="4" class="centered titlearea">RECEPTOR/INTERNO</th></tr>
                  <tr>
                    <th class="hcelr title">ID</th>
                    <td><input id="adm_proveedores_id" type="text" class="admreg proveedores widfit2 tableKey"<?= isset($_POST["adm_proveedores_id"])?" value='$_POST[adm_proveedores_id]'":"" ?>></td>
                    <th class="hcelr title">C&oacute;digo</th>
                    <td><input id="adm_facturas_codigoProveedor" id2="adm_proveedores_codigo" type="text" class="admreg facturas proveedores widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_codigoProveedor"])?" value='$_POST[adm_facturas_codigoProveedor]'":"" ?>></td>
                    <th class="hcelr title">ID</th>
                    <td><input id="adm_grupo_id" type="text" class="admreg grupo widfit2 tableKey"<?= isset($_POST["adm_grupo_id"])?" value='$_POST[adm_grupo_id]'":"" ?>></td>
                    <th class="hcelr title">Alias</th>
                    <td><input id="adm_grupo_alias" type="text" class="admreg grupo widfit2"<?= isset($_POST["adm_grupo_alias"])?" value='$_POST[adm_grupo_alias]'":"" ?>></td>
                  </tr>
                  <tr>
                    <th class="hcelr title">RFC</th>
                    <td colspan="3"><input id="adm_proveedores_rfc" type="text" class="admreg proveedores widfit2"<?= isset($_POST["adm_proveedores_rfc"])?" value='$_POST[adm_proveedores_rfc]'":"" ?>></td>
                    <th class="hcelr title">RFC</th>
                    <td colspan="3"><input id="adm_facturas_rfcGrupo" id2="adm_grupo_rfc" type="text" class="admreg facturas grupo widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_rfcGrupo"])?" value='$_POST[adm_facturas_rfcGrupo]'":"" ?>></td>
                  </tr>
                  <tr>
                    <th class="hcelr title">RazS</th>
                    <td colspan="3"><input id="adm_proveedores_razonSocial" type="text" class="admreg proveedores widfit2"<?= isset($_POST["adm_proveedores_razonSocial"])?" value='$_POST[adm_proveedores_razonSocial]'":"" ?>></td>
                    <th class="hcelr title">RazS</th>
                    <td colspan="3"><input id="adm_grupo_razonSocial" type="text" class="admreg grupo widfit2"<?= isset($_POST["adm_grupo_razonSocial"])?" value='$_POST[adm_grupo_razonSocial]'":"" ?>></td>
                  </tr>
                </table>
              </div>
              <div id="admfactura_scroll">
              </div>
              <div id="admfactura_bottom">
                <table class="noApply fullyExpanded">
                  <tr><th colspan="4" class="titlearea">TIMBRADO</th><th colspan="4" class="titlearea">VALIDACI&Oacute;N SAT</th></tr>
                  <tr><th class="hcelr title">UUID</th>
                    <td colspan="3"><input id="adm_facturas_uuid" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_uuid"])?" value='$_POST[adm_facturas_uuid]'":"" ?>></td>
                    <th class="hcelr title">Mensj</th>
                    <td colspan="3"><input id="adm_facturas_mensajeCFDI" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_mensajeCFDI"])?" value='$_POST[adm_facturas_mensajeCFDI]'":"" ?>></td>
                  </tr>
                  <tr><th class="hcelr title" title="Num. Certificado">#CERT</th>
                    <td colspan="3"><input id="adm_facturas_noCertificado" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_noCertificado"])?" value='$_POST[adm_facturas_noCertificado]'":"" ?>></td>
                    <th class="hcelr title">Estdo</th>
                    <td colspan="3"><input id="adm_facturas_estadoCFDI" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_estadoCFDI"])?" value='$_POST[adm_facturas_estadoCFDI]'":"" ?>></td>
                  </tr>
                  <tr><th colspan="8" class="titlearea">ARCHIVOS</th></tr>
                  <tr>
                    <th class="hcelr title">XMLOri</th>
                    <td colspan="3"><input id="adm_facturas_nombreOriginal" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_nombreOriginal"])?" value='$_POST[adm_facturas_nombreOriginal]'":"" ?>></td>
                    <th class="hcelr title">XML</th>
                    <td colspan="3" class="relative"><input id="adm_facturas_nombreInterno" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_nombreInterno"])?" value='$_POST[adm_facturas_nombreInterno]'":"" ?>><img id="xmlfix" class="adm_fixFileIcon hidden"><?php /* img id="xmlbtn" class="adm_noFileIcon hidden" */ ?></td>
                  </tr>
                  <tr>
                    <th class="hcelr title">Ruta</th>
                    <td colspan="3"><input id="adm_facturas_ubicacion" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_ubicacion"])?" value='$_POST[adm_facturas_ubicacion]'":"" ?>></td>
                    <th class="hcelr title">PDF</th>
                    <td colspan="3" class="relative"><input id="adm_facturas_nombreInternoPDF" type="text" class="admreg facturas widfit2 invoiceFixed"<?= isset($_POST["adm_facturas_nombreInternoPDF"])?" value='$_POST[adm_facturas_nombreInternoPDF]'":"" ?>><?php /* img id="pdfbtn" class="adm_noFileIcon hidden" */ ?></td>
                  </tr>
                  <tr id="admfactura_contrarreciboh"><th colspan="8" class="titlearea">CONTRA-RECIBO</th></tr>
                  <tr id="admfactura_contrarrecibo">
                    <th class="hcelr title">ID</th>
                    <td><input id="adm_contrarrecibos_id" type="text" class="admreg contrarrecibos widfit2 tableKey"<?= isset($_POST["adm_contrarrecibos_id"])?" value='$_POST[adm_contrarrecibos_id]'":"" ?>></td>
                    <th class="hcelr title">Fecha</th>
                    <td><input id="adm_contrarrecibos_fechaRevision" type="text" class="admreg contrarrecibos widfit2"<?= isset($_POST["adm_contrarrecibos_fechaRevision"])?" value='$_POST[adm_contrarrecibos_fechaRevision]'":"" ?>></td>
                    <th class="hcelr title">Folio</th>
                    <td><input id="adm_contrarrecibos_folio" type="text" class="admreg contrarrecibos widfit2"<?= isset($_POST["adm_contrarrecibos_folio"])?" value='$_POST[adm_contrarrecibos_folio]'":"" ?>></td>
                    <th class="hcelr title">Total</th>
                    <td><input id="adm_contrarrecibos_total" type="text" class="admreg contrarrecibos widfit2"<?= isset($_POST["adm_contrarrecibos_total"])?" value='$_POST[adm_contrarrecibos_total]'":"" ?>></td>
                  </tr>
                </table>
              </div>
              <div id="admfactura_extra">
              </div>
            </div>
            <div id="admfactura_foot"><span id="countMessage"></span><span id="admfactura_settings"><img id="adm_clearDataBtn" src="imagenes/icons/deleteIcon12.png" class="btnFX hidden"><img id="adm_saveDataBtn" type="button" class="btnFX hidden"></span>
            </div>
          </div>
          <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const r=lbycn('admreg');for(let i=0;i<r.length;i++){if(r[i].id==='adm_facturas_tipoComprobante')r[i].onchange=function(evt){submitRequest(evt);const isP=(evt.target.value==='p');setClass('admfactura_contrarreciboh','hidden',isP);setClass('admfactura_contrarrecibo','hidden',isP);};else r[i].onchange=submitRequest;}const b=ebyid('adm_clearDataBtn');b.onclick=function(evt){clearForm(true,true);ebyid('adm_facturas_id').focus();};doSubmit();ebyid('adm_facturas_id').focus();const x=ebyid('xmlfix');x.onclick=fixXML;ekil(this);">
    
<?php
clog1seq(-1);
clog2end("templates.adminfactura");
