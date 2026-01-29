<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
sessionInit();
if(!hasUser()) {
    die("Empty File");
}
*/
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.eventos");
clog1seq(1);
?>
console.log("EVENTOS SCRIPT READY!!!");
<?php
clog1seq(-1);
clog2end("scripts.eventos");
