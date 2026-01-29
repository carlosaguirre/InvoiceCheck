<?php
clog2ini("templates.comparasat_b");
clog1seq(1);
?>
            <div id="areaTemplate">
            <h1>Comparar Comprobantes Avance y SAT</h1>
<?php if ($autorizado) {?>
            <p><b>Alta de Archivo CSV</b>: &nbsp; <input type="file" id="loadCSVAvance" class="hidden" onchange="loadCSV('comparaavance');"><input type="button" value="AVANCE" onclick="console.log(' - - - - - - - - - - A V A N C E - - - - - - - - - - ');ebyid('loadCSVAvance').click();"><a href="formatoAvance_b.csv" download="formatoAvance_b.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a> &nbsp; <input type="file" id="loadCSVSAT" class="hidden" onchange="loadCSV('comparasat');"><input type="button" value="SAT" onclick="console.log(' - - - - - - - - - - -  S A T   - - - - - - - - - - - ');ebyid('loadCSVSAT').click();"><a href="formatoSAT_b.csv" download="formatoSAT_b.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a></p><hr>
<?php } ?>
            <p><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div><b>&#171;</b>&nbsp;Fecha Ini: <span><input type="text" id='fechaInicio' name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></span> Fecha Fin: <span><input type="text" id='fechaFin' name="fechaFin" value="<?= $fmtDay ?>" class="calendar" onclick="javascript:show_calendar_widget(this,'adjustCalMonImgs');" readonly></span>&nbsp;<b>&#187;</b><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div> &nbsp; <button type="button" value="Comparar" onclick="comparaDatos();"><b>Comparar</b></button></p><hr>

            <p><b>Consulta</b>: <label><input id="avance_trigger" name="basedatos" type="radio" onchange="chooseByClass('bdResult',function(e){return e.id==='avance_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});"><u>Avance</u></label> &nbsp; <label><input id="sat_trigger" name="basedatos" type="radio" onchange="chooseByClass('bdResult',function(e){return e.id==='sat_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});"><u>SAT</u></label></p>
                <div id="tables_wrapper" class="width100 scrollauto">
                    <table id="avance_table" name="comparaavance" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="uuid" class="asLinkH bgblue" onclick="ordenaPor(this);">UUID</th>
                                <th name="fecha" class="asLinkH bgblue" onclick="ordenaPor(this);">FECHA</th>
                                <th name="tipoComprobante" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Tipo de Comprobante">TIPO</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="sat_table" name="comparasat" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="uuid" class="asLinkH bgblue" onclick="ordenaPor(this);">UUID</th>
                                <th name="fecha" class="asLinkH bgblue" onclick="ordenaPor(this);">FECHA</th>
                                <th name="tipoComprobante" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Tipo de Comprobante">TIPO</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="tables_footer" class="centered relative100 hei22 margintop hidden"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fixHeight();ekil(this);"><span id="navigation" class="centered inlineblock margintop2"><img id="firstNav" src="imagenes/icons/firstPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><img id="prevNav" src="imagenes/icons/prevPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><span id="pagina" class="inlineblock wid40 righted"></span>/<span id="ultimapag" class="inlineblock wid40 lefted"></span><img id="nextNav" src="imagenes/icons/nextPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><img id="lastNav" src="imagenes/icons/lastPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"></span><select id="registrosPorPagina" class="abs_e" onchange="muestraDatos(false,true);"><option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option></select>
                </div>
<?php
clog1seq(-1);
clog2end("templates.comparasat_b");
