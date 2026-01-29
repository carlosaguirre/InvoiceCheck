<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Contrarrecibos.php";
$ctrObj=new Contrarrecibos();
$ctrObj->rows_per_page = 0;
$crNum = $ctrObj->getNextFolio("PRUEBAS");
echo $crNum;
