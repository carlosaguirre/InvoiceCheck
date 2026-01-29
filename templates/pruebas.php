<?php
clog2ini("templates.pruebas");
clog1seq(1);
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">PRUEBAS</h1>
  <div id="area_detalle" class="scrollauto all_space">
    <h2>Mass Req Test</h2>
    <table class="pad2c cellborder1"><thead><tr><TH>#</TH><th>
    DOCUMENTS</th><th>DATA</th></tr></thead>
        <tbody id="result"></tbody>
        <tfoot id="summary"></tfoot>
    </table>
    <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="massTest();ekil(this);">
  </div>
</div>
<?php
clog1seq(-1);
clog2end("templates.pruebas");
