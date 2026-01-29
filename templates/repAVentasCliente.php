<?php
clog2ini("templates.repAVentasCliente");
clog1seq(1);
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">REPORTE ANALITICO DE VENTAS POR CLIENTE</h1>
  <div id="area_detalle" onclick="const af=ebyid('avanceFrame');if(af.style.pointerEvents==='none') af.style.pointerEvents='auto'; else af.style.pointerEvents='none';">
    <iframe id="avanceFrame" name="<?= $avnObj->target ?>" class="centered" width="730" height="446" border="0" style="pointer-events: none;" onclick="if(this.style.pointerEvents==='none') this.style.pointerEvents='auto'; else this.style.pointerEvents='none';"></iframe>
    <form class="hidden" id="loginForm" method="POST" action="<?= $avnObj->url ?>dos" name="form1" target="<?= $avnObj->target ?>">
        <input type="hidden" name="Corp" value="<?= $avnObj->user ?>">
        <input type="hidden" name="password" value="<?= $avnObj->pswd ?>">
        <input type="hidden" name="Idioma" value="1">
        <input type="hidden" name="origen" value="1">
        <input type="hidden" name="davrodmar" value="On">
        <input type="hidden" name="escondido" value="1">
        <input type="hidden" name="basura" value="1">
        <input type="hidden" name="cmdEnviar" value="Autenticar">
    </form>
    <form class="hidden" id="requestForm" method="POST" action="<?= $avnObj->url ?>FArepanaa" name="form1" target="<?= $avnObj->target ?>">
        <input type="hidden" name="Opcion" value="6">
        <input type="hidden" name="Cual" value="1">
        <input type="hidden" name="Formato" value="*">
        <input type="hidden" name="formcli" value="*">
        <input type="hidden" name="VAR" value="-1">
        <input type="hidden" name="VALVAR" value="*">
        <input type="hidden" name="Linea" value="">
        <input type="hidden" name="Conj" value="0">
        <input type="hidden" name="IniDay" value="1">
        <input type="hidden" name="IniMon" value="<?= $mes ?>">
        <input type="hidden" name="IniYear" value="<?= $anio ?>">
        <input type="hidden" name="EndDay" value="<?= $dia ?>">
        <input type="hidden" name="EndMon" value="<?= $mes ?>">
        <input type="hidden" name="EndYear" value="<?= $anio ?>">
        <input type="hidden" name="Dev" value="1">
        <!-- input type="hidden" name="CONRFC" value="1" -->
        <!-- input type="hidden" name="CONDESC" value="1" -->
        <!-- input type="hidden" name="CONPED" value="1" -->
        <input type="hidden" name="Tipimp" value="E">
        <input type="hidden" name="Renimp" value="">
        <input type="hidden" id="avanceEmpresa" name="Emp" value="">
        <input type="hidden" name="Idioma" value="1">
        <input type="hidden" name="Ano" value="<?= $anio ?>">
        <input type="hidden" name="Mes" value="<?= $mes ?>">
        <input type="hidden" id="avanceRegion" name="Reg" value="">
        <input type="hidden" name="Usu" value="Local<?= $avnObj->user ?>">
        <input type="hidden" name="End" value="1">
        <input type="hidden" name="cmdEnviar" value="Enviar Datos">
    </form>
    <div id="statusSummary" class="showOnlyLastChild"></div>
  </div>
</div>
<div id="stepArea" class="hidden backdrop bgbrown" onclick="go();">
    <img src="imagenes/icons/deleteIcon12.png" class="abs_ne" onclick="ekil(this.parentNode);window.location.href=window.location.href;return eventCancel(event);">
</div>
<?php
$x=344;
$y=360;
if (!$isLocal) {
    $x+=337;
    $y+=82;
}
$w=16;
$h=16;
$x-=($w/2);
$y-=($h/2);
?>
<img id="pointImg" class="abs" style="top: <?=$y?>px; left: <?=$x?>px;width: <?=$w?>px; height: <?=$h?>px;" src="imagenes/ledred.gif">
<img id="cursorImg" class="abs" style="top: 0px; left: 0px;width: <?=$w?>px; height: <?=$h?>px;pointer-events: none;" src="imagenes/ledgreen.gif">
<?php
/* full screen top: 442px, left 681px. */
/* no left menu, no header: 360px, left 344px */
?>
<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="autoProcess(1);ekil(this);">
<?php
clog1seq(-1);
clog2end("templates.repAVentasCliente");
