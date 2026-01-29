<?php
clog2ini("templates.citas");
clog1seq(1);
$esDesarrollo = hasUser() && getUser()->nombre==="admin";
?>
<div id="area_general" class="central">
  <h1 class="txtstrk"><?= $titulo ?></h1>
  <p><?= $intro ?></p>
  <div id="cita_contenido" class="centered">
    <div class="cita_bloque alone" id="cita_calendario">
        <?php include "templates/calendarBlock.php"; ?>
    </div>
    <div class="cita_bloque hidden" id="cita_generales"><span id="fechaElegida"></span></div>
    <div class="cita_bloque hidden" id="cita_transporte"></div>
    <div class="cita_bloque hidden" id="cita_carga"></div>
    <div class="clear"></div>
  </div>
  <div id="calendarCheck"<?= $esDesarrollo?"":" class=\"hidden\"" ?>></div>
</div>
<?php
clog1seq(-1);
clog2end("templates.citas");
