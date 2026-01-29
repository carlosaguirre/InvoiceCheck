<?php
clog2ini("templates.comparasat");
clog1seq(1);
?>
            <div id="areaTemplate">
            <h1>Comparar Comprobantes Avance y SAT</h1>
<?php if ($autorizado) {?>
            <p><b>Alta de Archivo CSV</b>: &nbsp; <input type="file" id="loadCSVClientes" class="hidden" onchange="loadCSV('clientes');"><input type="button" value="CLIENTES" onclick="console.log(' - - - - - - - - - C L I E N T E S - - - - - - - - - ');ebyid('loadCSVClientes').click();"><a href="formatoClientes.csv" download="formatoClientes.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a> &nbsp; <input type="file" id="loadCSVAvance" class="hidden" onchange="loadCSV('comparaavance');"><input type="button" value="AVANCE" onclick="console.log(' - - - - - - - - - - A V A N C E - - - - - - - - - - ');ebyid('loadCSVAvance').click();"><a href="formatoAvance.csv" download="formatoAvance.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a> &nbsp; <input type="file" id="loadCSVSAT" class="hidden" onchange="loadCSV('comparasat');"><input type="button" value="SAT" onclick="console.log(' - - - - - - - - - - -  S A T   - - - - - - - - - - - ');ebyid('loadCSVSAT').click();"><a href="formatoSAT.csv" download="formatoSAT.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a></p><hr>
<?php } ?>
            <p><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div><b>&#171;</b>&nbsp;Fecha Ini: <span><input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></span> Fecha Fin: <span><input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></span>&nbsp;<b>&#187;</b><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div> &nbsp; <button type="button" value="Comparar" onclick="comparaDatos();"><b>Comparar</b></button></p><hr>
            <p><b>Consulta</b>: <label><input id="client_trigger" name="basedatos" type="radio" onchange="chooseByClass('bdResult',function(e){return e.id==='client_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});"><u>Clientes</u></label> &nbsp; <label><input id="avance_trigger" name="basedatos" type="radio" onchange="chooseByClass('bdResult',function(e){return e.id==='avance_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});"><u>Avance</u></label> &nbsp; <label><input id="sat_trigger" name="basedatos" type="radio" onchange="chooseByClass('bdResult',function(e){return e.id==='sat_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});"><u>SAT</u></label></p>
                <div id="tables_wrapper" class="width100 scrollauto">
                    <table id="client_table" name="clientes" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="codigo" class="asLinkH bgblue" onclick="ordenaPor(this);">C&Oacute;DIGO</th>
                                <th name="nombre" class="asLinkH bgblue" onclick="ordenaPor(this);">NOMBRE</th>
                                <th name="rfc" class="asLinkH bgblue" onclick="ordenaPor(this);">RFC</th>
                                <th name="contacto" class="asLinkH bgblue" onclick="ordenaPor(this);">CONTACTO</th>
                                <th name="calle" class="asLinkH bgblue" onclick="ordenaPor(this);">CALLE</th>
                                <th name="colonia" class="asLinkH bgblue" onclick="ordenaPor(this);">COLONIA</th>
                                <th name="cp" class="asLinkH bgblue" onclick="ordenaPor(this);">CP</th>
                                <th name="ciudad" class="asLinkH bgblue" onclick="ordenaPor(this);">CIUDAD</th>
                                <th name="estado" class="asLinkH bgblue" onclick="ordenaPor(this);">ESTADO</th>
                                <th name="correo" class="asLinkH bgblue" onclick="ordenaPor(this);">CORREO</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="avance_table" name="comparaavance" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="uuid" class="asLinkH bgblue" onclick="ordenaPor(this);">UUID</th>
                                <th name="fecha" class="asLinkH bgblue" onclick="ordenaPor(this);">FECHA</th>
                                <th name="remision" class="asLinkH bgblue" onclick="ordenaPor(this);">REMISION</th>
                                <th name="status" class="asLinkH bgblue" onclick="ordenaPor(this);">STATUS</th>
                                <th name="codigoReceptor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Codigo del Cliente">RECEPTOR</th>
                                <th name="tipoComprobante" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Tipo de Comprobante">TIPO</th>
                                <th name="subtotal" class="asLinkH bgblue" onclick="ordenaPor(this);">SUBTOTAL</th>
                                <th name="impuestos" class="asLinkH bgblue" onclick="ordenaPor(this);">IMPUESTOS</th>
                                <th name="total" class="asLinkH bgblue" onclick="ordenaPor(this);">TOTAL</th>
                                <th name="referencia" class="asLinkH bgblue" onclick="ordenaPor(this);">REFERENCIA</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="sat_table" name="comparasat" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="uuid" class="asLinkH bgblue" onclick="ordenaPor(this);">UUID</th>
                                <th name="fecha" class="asLinkH bgblue" onclick="ordenaPor(this);">FECHA</th>
                                <th name="serie" class="asLinkH bgblue" onclick="ordenaPor(this);">SERIE</th>
                                <th name="folio" class="asLinkH bgblue" onclick="ordenaPor(this);">FOLIO</th>
                                <th name="rfcEmisor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="RFC del Emisor">EMISOR</th>
                                <th name="rfcReceptor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="RFC del Receptor">RECEPTOR</th>
                                <th name="tipoComprobante" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Tipo de Comprobante">TIPO</th>
                                <th name="subtotal" class="asLinkH bgblue" onclick="ordenaPor(this);">SUBTOTAL</th>
                                <th name="descuento" class="asLinkH bgblue" onclick="ordenaPor(this);">DESCUENTO</th>
                                <th name="trasladoIVA" class="asLinkH bgblue" onclick="ordenaPor(this);">TRASL.IVA</th>
                                <th name="retencionISR" class="asLinkH bgblue" onclick="ordenaPor(this);">RETEN.ISR</th>
                                <th name="retencionIVA" class="asLinkH bgblue" onclick="ordenaPor(this);">RETEN.IVA</th>
                                <th name="total" class="asLinkH bgblue" onclick="ordenaPor(this);">TOTAL</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="tables_footer" class="centered relative100 hei22 margintop hidden"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fixHeight();ekil(this);"><span id="navigation" class="centered inlineblock margintop2"><img id="firstNav" src="imagenes/icons/firstPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><img id="prevNav" src="imagenes/icons/prevPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><span id="pagina" class="inlineblock wid40 righted"></span>/<span id="ultimapag" class="inlineblock wid40 lefted"></span><img id="nextNav" src="imagenes/icons/nextPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><img id="lastNav" src="imagenes/icons/lastPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"></span><select id="registrosPorPagina" class="abs_e" onchange="muestraDatos(false,true);"><option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option></select>
                </div>
<?php
clog1seq(-1);
clog2end("templates.comparasat");
