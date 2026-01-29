<?php
clog2ini("templates.configuracion");
clog1seq(1);
?>
<div id="area_general" class="central scrollauto">
  <div id="area_top" class="basicBG sticky toTop padhtt zIdx1"><h1 class="txtstrk nomargin">CONFIGURACI&Oacute;N</h1></div>
  <div id="area_detalle">
    <!-- p class="boldValue"><input type="button" value="Actualizar Status Complementos" onclick="updatePaymentReceiptStatus(event);"></p -->
    <!-- p class="boldValue"><input type="button" value="Verificar Carga Automática de CFDIs" onclick="showAutoUploadRecords(event);"></p -->
    <p><img src="imagenes/pt.png" width="11" height="11" class="vAlignCenter" style="margin: 1px;border: 1px solid gray;border-radius: 2px;"> <b class="vAlignCenter">Correos por hora</b> <img src="imagenes/icons/refresh.png" class="btnOp round pad1 btn16 marL4 vAlignCenter" onclick="reloadMailHourCount();">
    <div id="mailHourCount"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="reloadMailHourCount();ekil(this);"></div></p>
    <p><img src="imagenes/pt.png" width="11" height="11" style="margin: 1px;border: 1px solid gray;border-radius: 2px;"> <b>Permitir Alta de Facturas con USO CFDI P01.</b> <input type="text" name="CFDI_ALLOWP01" id="usrP01" class="wid120px marL4" placeholder="usuario" onkeypress="if(isEnterKey(event))addUser(event,'P01');"><img src="imagenes/icons/add.png" id="btnP01" class="btn20 btnTab marNeg20" onclick="addUser(event,'P01');"></p>
    <div class="scrollauto screen bts<?=$clsP01?>" id="lstP01" style="max-height:200px;"><ul id="ulP01" class="inline_block lefted nomargin alternate"><?=$lstP01?></ul></div>
    <p><img src="imagenes/pt.png" width="11" height="11" style="margin: 1px;border: 1px solid gray;border-radius: 2px;"> <b>Permitir Alta de Facturas versión CFDI 3.3.</b> <input type="text" name="CFDI_ALLOW33" id="usr33" class="wid120px marL4" placeholder="usuario" onkeypress="if(isEnterKey(event))addUser(event,'33');"><img src="imagenes/icons/add.png" id="btn33" class="btn20 btnTab marNeg20" onclick="addUser(event,'33');"></p>
    <div class="scrollauto screen bts<?=$cls33?>" id="lst33" style="max-height:200px;"><ul id="ul33" class="inline_block lefted nomargin alternate"><?=$lst33?></ul></div>
    <p><img src="imagenes/pt.png" width="11" height="11" style="margin: 1px;border: 1px solid gray;border-radius: 2px;"> <b>Permitir Alta de Complementos de Pago sin validar IMPUESTOS.</b> <input type="text" name="CFDI_ALLOWPRTV" id="usrPRTV" class="wid120px marL4" placeholder="usuario" onkeypress="if(isEnterKey(event))addUser(event,'PRTV');"><img src="imagenes/icons/add.png" id="btnPRTV" class="btn20 btnTab marNeg20" onclick="addUser(event,'PRTV');"></p>
    <div class="scrollauto screen bts<?=$clsPRTV?>" id="lstPRTV" style="max-height:200px;"><ul id="ulPRTV" class="inline_block lefted nomargin alternate"><?=$lstPRTV?></ul></div>
    <p><img src="imagenes/pt.png" width="11" height="11" style="margin: 1px;border: 1px solid gray;border-radius: 2px;"> <b>Permitir Alta de Facturas con ClaveProdServ 01010101.</b> <input type="text" name="CFDI_ALLOW01x4" id="usr01x4" class="wid120px marL4" placeholder="usuario" onkeypress="if(isEnterKey(event))addUser(event,'01x4');"><img src="imagenes/icons/add.png" id="btn01x4" class="btn20 btnTab marNeg20" onclick="addUser(event,'01x4');"></p>
    <div class="scrollauto screen bts<?=$cls01x4?>" id="lst01x4" style="max-height:200px;"><ul id="ul01x4" class="inline_block lefted nomargin alternate"><?=$lst01x4?></ul></div>
    <p><label><input type="checkbox" id="allow2019"<?=$allow2019?" checked":"" ?> name="CFDI_IGNORE2020LIMIT" onclick="toggleInfo(event);"> Permitir Alta de CFDI anteriores al año 2020.</label></p>
    <p class="nomargin"><img src="imagenes/pt.png" width="11" height="11" style="margin: 1px;border: 1px solid gray;border-radius: 2px;"> <b>Permitir Alta de CFDI del mes pasado.</b> <img id="editButton" src="imagenes/icons/rename12.png" class="pointer<?= $editButtonClass ?>" onclick="showFilterLine(event);"></p><div class="scroll-74 screen bts" style="max-height:200px;"><ul id="clearanceList" class="inline_block lefted nomargin alternate<?=$lastMonthClass?>"><?=$lastMonthList?></ul></div>
<?php if ($esSuperAdmin) { ?>
    <form id="myLogForm" method="POST" target="_self">
      <p><label><input type="checkbox" id="viewMyLog"<?=$viewMyLog?"checked":"" ?> name="viewMyLog" value="1" onclick="ebyid('myLogForm').submit();"> Mostrar MyLog</label><input type="hidden" name="viewMyLog0" value="0"><input type="hidden" name="menu_accion" value="Configuracion"></p>
    </form>
<?php } ?>
  </div>
</div>

<?php
clog1seq(-1);
clog2end("templates.configuracion");
