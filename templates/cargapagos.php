<?php
clog2ini("templates.cargapagos");
clog1seq(1);
$logchk=true;
if (isset($_POST["sndbtn"]) && !isset($_POST["logchk"])) $logchk=false;
?>
  <div id="area_alta" class="central">
    <h1 class="txtstrk">Carga Reportes de Egresos de Avance</h1>
    <div id="area_alta_contenido" class="contenido">
      <form method="post" name="forma_alta" target="_self" enctype="multipart/form-data" class="oneLine" onsubmit="return submitting();">
        <input type="hidden" name="menu_accion" value="Carga Pagos">
        <input type="file" id="pagos" name="pagos[]" multiple class="highlight" onchange="fileTest();">
        <input type="submit" id="sndbtn" name="sndbtn" value="Enviar" onclick="openLog('<?=getCurrLog()?>');">
        <label><input type="checkbox" id="logchk" name="logchk" value="1"<?= $logchk?" checked":""?> title="Abrir Log">Log</label>
      </form>
      <div id="resultView" class="lessOneLine scrollauto">
<?=$resultView?>
      </div>
    </div>
  </div>
<?php
clog1seq(-1);
clog2end("templates.cargapagos");
