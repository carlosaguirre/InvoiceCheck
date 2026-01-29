<?php
require_once "C:/Apache24/htdocs/invoice/configuracion/conPro.php";

$tesoreria="/externo/tesoreria/";
$otros="/externo/otros/";
$depositosScript="{$_ConProHost}{$tesoreria}depositos.aspx";
$contraExisteScript="{$_ConProHost}{$otros}contra_existe.aspx";
$contrarreciboScript="{$_ConProHost}{$otros}Contrarrecibo.aspx";

$empGlama="&Emp=GLAMA";
$empSkarton="&empresa=SKARTON";
$fDeDMY="&FDe=26_09_2025";
$fADMY="&FA=26_09_2025";
$cant01="&Cant=6780.78";
$fechaYMD="&fecha=2025-09-09";
$total02="&total=3528.00";
$factura02="&factura=SKARTON-8119";
$folio02="&folio=24386";

$urlQuery01="?{$empGlama}{$fDeDMY}{$fADMY}{$cant01}{$_ConProTest}";
$urlQuery02="?{$fechaYMD}{$empSkarton}{$total02}{$factura02}{$folio02}{$_ConProTest}";

$deposito="{$depositosScript}{$urlQuery01}";
$contraExiste="{$contraExisteScript}{$urlQuery02}";
$contraRecibo="{$contrarreciboScript}{$urlQuery02}";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<h1>DEPOSITO: <?=$fileURL?></h1>
<iframe src="<?=$deposito?>" style="width: 100%; height: 90px;"></iframe>
<h1>CONTRA EXISTE:</h1>
<iframe src="<?=$contraExiste?>" style="width: 100%; height: 30px;"></iframe>
<h1>CONTRARRECIBO IFRAME:</h1>
<iframe src="<?=$contraRecibo?>" style="width: 100%; height: 400px;"></iframe>
<h1>FIN</h1>
</body>
</html>
