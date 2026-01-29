<?php
clog2ini("templates.comext");
clog1seq(1);

$esAdmin      = validaPerfil("Administrador");
$esSistemas   = validaPerfil("Sistemas")||$esAdmin;
$esCExControl=validaPerfil("ComercioExterior Control")||$esSistemas;
$esCExMonitor=validaPerfil("ComercioExterior Monitor");
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">COMERCIO EXTERIOR</h1>
  <div id="area_detalle">
    <div id="comext_menu_close"><button id="comext_menu_down"></button></div>
    <div id="comext_menu">
        <button id="comext_menu_up"></button>
        <UL>
<?php if ($esCExControl) { ?>
            <LI><button type="button" id="prvBtn" class="comextMenuBtn" onclick="comextOpt2('prv')">Proveedor Extranjero</button></LI>
<?php } ?>
            <LI><button type="button" id="nwoBtn" class="comextMenuBtn" onclick="comextOpt2('nwo')">Registro de Operación</button></LI>
            <LI><button type="button" id="rdoBtn" class="comextMenuBtn" onclick="comextOpt2('rdo')">Consultar Registro</button></LI>
            <LI><button type="button" id="auoBtn" class="comextMenuBtn" onclick="comextOpt('auo')">Auditar Operaciones</button></LI>
            <LI><button type="button" id="rpoBtn" class="comextMenuBtn" onclick="comextOpt('rpo')">Reporte de Operación</button></LI>
        </UL>
    </div>
    <div id="comext_page">
        <iframe id="comext_frame"></iframe>
        <h2 id="comext_title"></h2>
        <div id="comext_content"></div>
    </div>
  </div>
</div>
<?php
clog1seq(-1);
clog2end("templates.comext");
