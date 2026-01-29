<?php
clog2ini("templates.extraedatos");
clog1seq(1);
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">EXTRAER DATOS</h1>
  <div id="area_detalle" class="all_space">
    <div id="filter_area" class="all_space oneLine centered">
      <input id="filelist" type="file" accept=".xml" multiple onchange="add();">
    </div>
    <div id="data_area" class="all_space lessOneLine centered"></div>
  </div>
</div>
<?php
clog1seq(-1);
clog2end("templates.extraedatos");
