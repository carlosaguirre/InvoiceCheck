<?php
clog2ini("templates.consitfis");
clog1seq(1);
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">CONSTANCIAS DE SITUACIÓN FISCAL</h1>
<?php if ($gpoSize<1) {
    echo "<b>Debe dar de alta al menos una factura.</b></div>";
    return;
}
?>
  <div id="area_detalle" class="top padt5 padr10">
    <B>EMPRESAS:</B><br>
    <select id="empresa" name="empresa" size="<?= $gpoSize ?>" onchange="loadPDF(this.value);"><?php 
foreach ($gpoData as $idx => $row) {
    $alias=$row["alias"];
    $ruta="{$row["conSitFis"]}{$alias}";
    $csft=$row["conSitFisTimes"];
    if (!empty($csft)) $ruta.="_{$csft}";
    if ($alias==="SERVICIOS") $alias="SAC";
    //$ruta=str_replace("/", "|", $ruta);
    echo "<option value=\"$ruta\" id=\"gpo{$row["id"]}\">$alias</option>";
}
?></select>
  <input type="hidden" id="bgdocPath" value=""><input type="hidden" id="bgdocName" value="">
  <p class="wrap100 prewrap1 fontCondensed">Incluye Opinión de Cumplimiento</p>
<?php
if ($esConSitFis) { ?>
        <input type="file" class="wid0 hidden" id="updPkFl" onchange="updateFile(this);" accept=".pdf"<?= $esDesarrollo?" multiple":"" ?>><input type="button" value="Actualizar" class="<?= $esDesarrollo?"":"hidden"?>" id="updateButton" onclick="ebyid('updPkFl').click();"><br>
<?php
} else echo "<!-- NoEsConSitFis user='".getUser()->nombre."' ".($esDesarrollo?"":"No")."EsDesarrollo -->"; ?>
  </div>
  <div id="pdfviewer_containerb" class="inlineblock allWidBut150i">
    <div id="pdfv_canvas_container">
        <div id="pdfv_info" class="boldValue fontLarge padt5">Seleccione una empresa en el listado a la izquierda.<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="setTimeout(hideInfo,4000);ekil(this);">
        </div>
        <canvas id="pdfv_renderer"></canvas>
    </div>
    <div id="pdfv_controls" class="fullWidHigh invisible">
        <div id="pdfv_zoom_controls" class="righted">
            <img id="pdfv_nwwin" src="imagenes/icons/nwwin.png" title="Abrir en otra pestaña" onclick="openNewWin()" class="pdfvBtn">
            <img id="pdfv_print" src="imagenes/prntricon32a.png" title="Imprimir" onclick="submitPrint();" class="pdfvBtn">
            <img id="pdfv_zoom_in" src="imagenes/icons/zoomIn.png" title="120%" onclick="changeZoom(0.2);" class="pdfvBtn"/>
            <img id="pdfv_zoom_out" src="imagenes/icons/zoomOut.png" title="80%" onclick="changeZoom(-0.2);" class="pdfvBtn"/>
        </div>
        <div id="pdfv_navigation_controls">
            <img id="pdfv_go_previous" src="imagenes/icons/prevPageE20.png" onclick="changePage(-1);" class="pdfvBtn"/>
            <input id="pdfv_current_page" value="1" min="1" type="number" onkeypress="setPage(event);"/>
            <img id="pdfv_go_next" src="imagenes/icons/nextPageE20.png" onclick="changePage(+1);" class="pdfvBtn">
        </div>
        <div id="pdfv_clear_controls"></div>
    </div>
</div>
<?php
clog1seq(-1);
clog2end("templates.consitfis");
