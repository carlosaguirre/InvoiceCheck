<?php
clog2ini("templates.empleados");
clog1seq(1);
?>
<div id="area_general" class="central">
  <h1><span class="relative"> &nbsp;&nbsp; EMPLEADOS &nbsp;&nbsp; <img src="imagenes/icons/upload1.png" class="btnOp abs_e top8" onclick="ebyid('empl_file').click();"><input id="empl_file" type="file" class="hidden" onchange="csvUpload();" accept=".csv"><input id="empl_encoding" type="hidden" value="Windows-1252"></span></h1>
  <div id="area_empleados">
<?php /* if ($modificaEmpl) { */ ?>
    <!-- form method="post" name="forma_reg_empl" id="forma_reg_empl" target="_self" enctype="multipart/form-data" -->
<?php /* }  */ ?>
        <input type="hidden" name="menu_accion" value="Empleados">
        <table id="tabla_empleado">
            <tr><td>N&uacute;mero</td><td>: <input id="empl_id" type="hidden"><input id="empl_num" type="text" class="numero"></td><td>Nombre</td><td>: <input id="empl_name" type="text" class="nombre"></td><td>CuentaTC</td><td>: <input id="empl_acccard" type="text" class="cuenta"></td></tr>
            <tr></tr>
            <tr><td>Status</td><td>: <select id="empl_status" class="status"><?=getHtmlOptions($statusArray,"activo") ?></select></td><td>Empresa</td><td>: <select id="empl_gpoalias" class="empresa"><?= getHtmlOptions($grupoArray,false) ?></select></td><td>CLABE</td><td>: <input id="empl_accuniq" type="text" class="cuenta"></td></tr>
        </table>
<?php /* if ($modificaEmpl) { */ ?>
    <!-- /form -->
<?php /* }  */?>
    <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="ebyid('empl_num').oninput=doQuery;ebyid('empl_name').oninput=doQuery;ebyid('empl_acccard').oninput=doQuery;ebyid('empl_status').onchange=doQuery;ebyid('empl_gpoalias').onchange=doQuery;ebyid('empl_accuniq').oninput=doQuery;ekil(this);">
  </div>
  <div id="area_control1">
    <button type="button" id="clearEmployeeButton" class="hidden" onclick="let e=ebyid('empl_num');e.value='';clearResults();e.focus();">Borrar</button>
    <button type="button" id="saveEmployeeButton" class="hidden" onclick="saveResults();">Guardar</button>
    <input type="hidden" id="pageNumber" value="1">
    <input type="hidden" id="rowsPerPage" value="10">
    <input type="hidden" id="lastPage" value="0">
  </div>
  <div id="lista_empleados">
  </div>
  <div id="empl_footer" style="position:absolute;bottom:30px;width:calc(100% - 200px);text-align: center;">00000</div>
</div>
<?php
clog1seq(-1);
clog2end("templates.empleados");
