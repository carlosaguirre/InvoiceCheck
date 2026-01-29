<?php
//clog2ini("templates.cuentas");
//clog1seq(1);
?><div id="area_cuentas"><?php
  ?><h2 class="mb20px nowrap">Validación de Cuentas Bancarias de Proveedores</h2><?php
  ?><span class="lefted">Por Archivo TXT de Avance: <input type="file" id="archivo_txt" onchange="procesaTXT();" accept=".txt" multiple></span><?php
  ?><div id="result_account" class="prv_section centered"><?php
    ?><h3 id="error_line" class="errorLabel hidden"></h3><?php
    ?><table id="result_table" class="hidden widfit allcellpad3 allcellborder-light centered"><?php
      ?><thead><tr><th title="Código de Proveedor">Código</th><th title="Cuenta Bancaria">Cuenta Archivo</th><th>Razón Social</th><th>Referencia</th><th>Respuesta</th></tr></thead><?php
      ?><tbody id="result_body"></tbody><?php
    ?></table><?php
  ?></div><?php
  ?><div id="summary_account" class="hidden"><span id="summary_good"></span><button id="button_good" onclick="generaTxt(true);">A PAGAR</button><span id="summary_error"></span><button id="button_error" onclick="generaTxt(false);">A ESPERAR</button></div><?php
?></div><?php
//clog1seq(-1);
//clog2end("templates.cuentas");
