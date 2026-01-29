<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
if(!hasUser()) {
    die("Empty File");
}
*/
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.menu");
clog1seq(1);
?>
console.log("MENU SCRIPT READY!!!");
<?php
clog1seq(-1);
clog2end("scripts.menu");
