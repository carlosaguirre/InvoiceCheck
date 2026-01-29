<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.nomina");
clog1seq(1);
?>
console.log("NOMINA SCRIPT READY!!!");
<?php
clog1seq(-1);
clog2end("scripts.nomina");
