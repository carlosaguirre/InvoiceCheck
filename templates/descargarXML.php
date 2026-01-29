<?php
clog2ini("templates.descargarXML");
clog1seq(1);

function creaCarpetaDescargas($dirname) {
    if(!is_dir("./descargas/$dirname/")) {
        $cmdResult = mkdir("./descargas/$dirname/", 0777, true);
        if (!$cmdResult) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>No pudo crearse la carpeta de descargas $dirname.</p>";
        }
    }
}
creaCarpetaDescargas("recibidos");
creaCarpetaDescargas("emitidos");
creaCarpetaDescargas("invoicesafe");

$today = new DateTime();
$thisMonth = +$today->format("m");
$thisYear = +$today->format("Y");
// // // // // // // // // // // // //
$numYears = $thisYear-2016;
// // // // // // // // // // // // //
$months = []; $years = [];
for($i=0;$i<12;$i++) $months[$i+1]=strftime('%B',mktime(0,0,0,$i+1));
for($i=$numYears;$i>=0;$i--) $years[$thisYear-$i]=($thisYear-$i);
$monthOptions = getHtmlOptions($months, $thisMonth);
$yearOptions = getHtmlOptions($years, $thisYear);
$empresaOptions = getHtmlOptions(["APSA"=>"APSA"],"APSA");
?>
          <div id="area_central_xml" class="central base clear">
            <div class="scrollauto">
                <h1>Descargar XML</h1>
            </div>
            <div id="descarga_area" class="scrolldiv">
              <fieldset id="wrapper1" class="nobottompadding">
                  <legend align='left'><b>Facturas no encontradas</b></legend>
                  <div class="lefted">
                    Facturas obtenidas del SAT no dadas de alta en Invoice Check ni respaldadas en Invoice Safe.<br>
                    <select id="empresaDif"><?= $empresaOptions ?></select>
                    <select id="fechaMesDif"><?= $monthOptions ?></select>
                    <select id="fechaAnioDif"><?= $yearOptions ?></select>
                    <input type="button" name="actualizaDif" value="Mostrar" onclick="actualizaDif();">
                    <input type="button" name="limpiaDif" value="Limpiar" onclick="limpiaDif();">
                  </div><hr class="nobottommargin"><div id="listaDif" class="lefted">
                  </div>
              </fieldset>
              <fieldset id="wrapper2" class="nobottompadding">
                  <legend align='left'><b>Archivos</b></legend>
                  <div class="lefted">
                    Facturas dadas de alta en el Portal de Invoice Check.<br>
                    <select id="empresaArch"><?= $empresaOptions ?></select>
                    <select name="fechaMesArch" id="fechaMesArch"><?= $monthOptions ?></select>
                    <select name="fechaAnioArch" id="fechaAnioArch"><?= $yearOptions ?></select>
                    <input type="button" name="actualizaarch" value="Mostrar" onclick="actualizaArch();">
                    <input type="button" name="limpiaarch" value="Limpiar" onclick="limpiaArch();">
                  </div><hr class="nobottommargin"><div id="listaArch" class="lefted">
                  </div>
              </fieldset>
              <fieldset id="wrapper3" class="nobottompadding">
                  <legend align='left'><b>CFDI Recibidos</b></legend>
                  <div class="lefted">
                    Env&iacute;o de Facturas Recibidas en formato ZIP obtenidos del <a href="https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon">Portal de Contribuyentes del SAT</a><br>
                    <input type="button" name="dtar" value="Descarga ZIP" onclick="descargaZips();">
                    <input type="button" name="untar" value="Descomprimir" onclick="unzip();">
                    <input type="button" name="organiza" value="Organiza XML" onclick="organizaXMLs();">
                    <select id="empresaSAT"><?= $empresaOptions ?></select>
                    <select id="fechaMesSAT"><?= $monthOptions ?></select>
                    <select id="fechaAnioSAT"><?= $yearOptions ?></select>
                    <input type="button" name="actualizasat" value="Mostrar" onclick="actualizaSAT();">
                    <input type="button" name="limpiasat" value="Limpiar" onclick="limpiaSAT();">
                  </div><hr class="nobottommargin"><div id="listaSAT" class="lefted">
                  </div>
              </fieldset>
              <fieldset id="wrapper4" class="nobottompadding">
                  <legend align='left'><b>INVOICE SAFE</b></legend>
                  <div class="lefted">
                    Env&iacute;o de Facturas Respaldadas en formato TAR del <a href="http://invoicesafemx.com.mx">Portal de Invoice Safe</a><br>
                    <input type="button" name="dtar" value="Descarga TAR" onclick="descargaTars();">
                    <input type="button" name="untar" value="DescompacTAR" onclick="untar();">
                    <input type="button" name="organiza" value="Organiza XML" onclick="organizaEmpresas();">
                    <select id="empresaIS"><?= $empresaOptions ?></select>
                    <select id="fechaMesIS"><?= $monthOptions ?></select>
                    <select id="fechaAnioIS"><?= $yearOptions ?></select>
                    <input type="button" name="actualizais" value="Mostrar" onclick="actualizaIS();">
                    <input type="button" name="limpiais" value="Limpiar" onclick="limpiaIS();">
                  </div><hr class="nobottommargin"><div id="listaIS" class="lefted">
                  </div>
              </fieldset>
              <form name="formaDwn" id="formaDwn" style="display: inline;" enctype="multipart/form-data">
                <input type="file" name="dwnfiles[]" id="dwnfiles" multiple style="opacity: 0;" onchange="sendFiles();">
              </form>
            </div>
          </div>
<?php
clog1seq(-1);
clog2end("templates.descargarXML");
