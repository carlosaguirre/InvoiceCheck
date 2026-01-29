<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";

$cash=1.3579;
//$currValue=;//formatCurrency($cash,"0");
?>
<html>
<body>
    <H1><?= "CURRENCY: ".formatCurrency($cash) ?></H1>
    <H1><?= "TWO FRACTION DIGITS: ".formatTwoFractionDigits($cash) ?></H1>
    <H1><?= "DOLLARS: ".formatCurrency($cash,"USD") ?></H1>
    <H1><?= "EUROS: ".formatCurrency($cash,"EUR") ?></H1>
</body>
</html>
