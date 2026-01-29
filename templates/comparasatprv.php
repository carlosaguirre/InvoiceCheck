<?php
$autorizado=hasUser()&&(validaPerfil("Administrador")||validaPerfil("Sistemas")||validaPerfil("Compara Proveedores"));
if (!$autorizado) {
    if (hasUser()) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("templates.comparasatprv");
clog1seq(1);

//require_once "clases/Grupo.php";
//$gpoObj=new Grupo();
//$gpoObj->rows_per_page=0;
//$gpoData=$gpoObj->getData(false,0,"rfc,alias");
$query="SELECT DISTINCT g.rfc,g.alias FROM grupo g INNER JOIN comparaavanceprv a ON g.rfc=a.rfcEmisor OR g.rfc=a.rfcReceptor UNION SELECT DISTINCT g.rfc,g.alias FROM grupo g INNER JOIN comparasatprv s ON g.rfc=s.rfcEmisor OR g.rfc=s.rfcReceptor";
$result = DBi::query($query);
$gpoMap=[""=>"TODAS"];
while ($row = $result->fetch_assoc()) {
    $gpoMap[$row["rfc"]] = $row["alias"];
}
$result->close();

$query="SELECT DISTINCT tipoComprobante tipo FROM comparasatprv UNION SELECT DISTINCT tipoComprobante tipo FROM comparaavanceprv";
$result = DBi::query($query);
$tipoMap=[""=>"TODOS"];
while ($row = $result->fetch_assoc()) {
    $tipoMap[$row["tipo"]] = $row["tipo"];
}
$tipoMap["otros"]="otros";
$result->close();

$dia = date("j");
$mes = date("n");
$anio = date("Y");
$maxdia = date("t");
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);
$fmtDay0 = "01/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
$fmtDay = str_pad($dia,2,"0",STR_PAD_LEFT)."/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
?>
            <div id="areaTemplate">
            <h1 class="nowrap">Comparar CFDI de Proveedores</h1>
            <p class="marblk5 nowrap"><b>Alta de Archivo CSV</b>: &nbsp; <input type="file" id="loadCSVAvance" class="hidden" onchange="loadCSV('comparaavanceprv');"><input type="button" value="AVANCE" onclick="console.log(' - - - - - - - - - - A V A N C E - - - - - - - - - - ');ebyid('loadCSVAvance').click();"><a href="formatoCompara.csv" download="formatoCompara.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a> &nbsp; <input type="file" id="loadCSVSAT" class="hidden" onchange="loadCSV('comparasatprv');"><input type="button" value="SAT" onclick="console.log(' - - - - - - - - - - -  S A T   - - - - - - - - - - - ');ebyid('loadCSVSAT').click();"><a href="formatoCompara.csv" download="formatoCompara.csv"><img title="Formato CSV" src="imagenes/icons/descarga6.png" class="btnFX btn16 vbottom"></a></p><hr class="centered marblk5">
            <table class="centered">
              <!-- tr>
                <td colspan="2" class="pad0"></td><td colspan="2" class="righted pad0 padrgt2"><div class="calendar_month_wrapper" onclick="dateIniSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_prev" title="Mes Anterior" class="calendar_month_<?= $mesPasado ?>"></div><b>&#171;</b>&nbsp;<img src="imagenes/icons/hoy.png" id="calendar_hoy" title="Sólo Hoy" class="calendarFX vbottom" onclick="soloHoy();">&nbsp;<b>&#187;</b><div class="calendar_month_wrapper" onclick="dateEndSet();"><img src="imagenes/icons/calmes.png" id="calendar_month_next" title="Mes Siguiente" class="calendar_month_<?= $mesProximo ?>"></div></td>
              </tr -->
              <tr>
                <th class="righted">Corporativo:</th>
                <td class="lefted">
                  <select id="empresa" onchange="resetTablas();"><?= getHtmlOptions($gpoMap, false) ?></select>
                </td>
                <th class="righted nowrap">Fecha Ini:</th>
                <td class="lefted">
                  <input type="text" id="fechaInicio" name="fechaInicio" value="<?= $fmtDay0 ?>" class="calendar" canPickMonthOrYear="1" onclick="javascript:show_calendar_widget(this,'muestraFechaIni');" onchange="resetTablas();" readonly>
                </td>
              </tr>
              <tr>
                <th class="righted">Tipo:</th>
                <td class="lefted">
                  <select id="tipo" onchange="resetTablas();"><?= getHtmlOptions($tipoMap, false) ?></select>
                </td>
                <th class="righted nowrap">Fecha Fin:</th>
                <td class="lefted">
                  <input type="text" id="fechaFin" name="fechaFin" value="<?= $fmtDay ?>" class="calendar" canPickMonthOrYear="1" onclick="javascript:show_calendar_widget(this,'muestraFechaFin');" onchange="resetTablas();" readonly>
                </td>
              </tr>
            </table>
            <p class="marblk5 centered nowrap"><span class="consultaSAT"><button type="button" value="Avance" class="boldValue" onclick="chooseByClass('bdResult',function(e){return e.id==='avance_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});">Consultar Avance</button><button type="button" value="SAT" class="boldValue" onclick="chooseByClass('bdResult',function(e){return e.id==='sat_table'},function(e){muestraDatos(e);},function(e){cladd(e,'hidden');});">Consultar SAT</button></span><span id="eliminaSAT" class="eliminaSAT hidden"><button type="button" value="Borrar" class="boldValue" onclick="preEliminaDatos();">Elimina CFDIs</button></span><span class="comparaSAT"><button type="button" value="Comparar" class="boldValue" onclick="comparaDatos();">Comparar Ambas</button></span></p>
            <hr class="centered marblk7">
                <div id="tables_wrapper" class="width100 scrollauto">
                    <table id="avance_table" name="comparaavanceprv" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="uuid" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Folio Fiscal">UUID</th>
                                <th name="fecha" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Fecha de creación">FECHA</th>
                                <th name="tipoComprobante" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Tipo de Comprobante">TIPO</th>
                                <th name="rfcEmisor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="RFC del Emisor">RFCEMISOR</th>
                                <th name="rfcReceptor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="RFC del Receptor">RFCRECEPTOR</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="sat_table" name="comparasatprv" class="bdResult hidden centered catalog_table nice">
                        <thead>
                            <tr>
                                <th name="uuid" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Folio Fiscal">UUID</th>
                                <th name="fecha" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Fecha de creación">FECHA</th>
                                <th name="tipoComprobante" class="asLinkH bgblue" onclick="ordenaPor(this);" title="Tipo de Comprobante">TIPO</th>
                                <th name="rfcEmisor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="RFC del Emisor">RFCEMISOR</th>
                                <th name="rfcReceptor" class="asLinkH bgblue" onclick="ordenaPor(this);" title="RFC del Receptor">RFCRECEPTOR</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="tables_footer" class="centered relative100 hei22 margintop hidden"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fixHeight();ekil(this);"><span id="navigation" class="centered inlineblock margintop2"><img id="firstNav" src="imagenes/icons/firstPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><img id="prevNav" src="imagenes/icons/prevPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><span id="pagina" class="inlineblock wid40 righted"></span>/<span id="ultimapag" class="inlineblock wid40 lefted"></span><img id="nextNav" src="imagenes/icons/nextPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"><img id="lastNav" src="imagenes/icons/lastPageE12.png" class="btnFX vbottom disabled" onclick="page(event);"></span><select id="registrosPorPagina" class="abs_e marginR2" onchange="muestraDatos(false,true);"><option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option></select>
                </div>
<?php
clog1seq(-1);
clog2end("templates.comparasatprv");
