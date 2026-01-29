<?php
clog2ini("templates.respaldosis");
clog1seq(1);
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">RESPALDO DE SISTEMA</h1>
  <div id="area_respaldos" class="subHeadFoot">
    <div id="area_datos" class="column2 vhalf scrollauto">
      <div class="sticky toTop basicBG"><button value="Respaldo" onclick="hacerDatos();" class="mar2">RESPALDAR DATOS</button></div>
      <div class="inlineblock"><UL id="resultado" class="lefted"><?php showList($datalist,"data") ?></UL></div>
    </div>
    <div id="area_codigo" class="column2 vhalf scrollauto">
      <div class="sticky toTop basicBG"><button value="Respaldo" onclick="hacerCodigo();" class="mar2">RESPALDAR CÓDIGO</button></div>
      <div class="inlineblock"><UL id="versiones" class="lefted"><?php showList($codelist,"code") ?></UL></div>
    </div>
    <div id="area_logs" class="column2 vhalf scrollauto">
      <div class="sticky toTop basicBG"><button value="Respaldo" onclick="hacerLOGS();" class="mar2">RESPALDAR LOGS</button></div>
      <div class="inlineblock"><UL id="loglist" class="lefted"><?php showList($logslist,"logs"); ?></UL></div>
    </div>
    <div id="area_cfdi" class="column2 vhalf scrollauto">
      <div class="sticky toTop basicBG"><button value="Respaldo" onclick="hacerCFDI();" class="mar2">RESPALDAR CFDI</button></div>
      <div class="inlineblock"><UL id="cfdilist" class="lefted"><?php showList($cfdilist,"cfdi"); ?></UL></div>
    </div>
  </div>
  <div id="foot_data" class="footOneLine righted"><button class="marT1 marR2" onclick="hacer(event);" value="Carga de Datos Total">FullDataLoad</button><button class="marT1 marR2" onclick="hacer(event);" value="Respaldo de Datos Total">FullDataDump</button></div>
</div>
<?php
clog1seq(-1);
clog2end("templates.respaldosis");
