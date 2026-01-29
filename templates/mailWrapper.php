<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/SolicitudPago.php";
$val=+$_REQUEST["flag"]??0;
$flags=SolicitudPago::getStatusList($val,true);
?>
<p>TEST: INSPECT SolicitudPago</p>
<ol>
<?php foreach ($flags as $value) { ?>
    <LI><?=$value?></LI>
<?php } ?>
</ol>
    <!-- 
        const STATUS_ SIN_FACTURA=0;
        const STATUS_ CON_FACTURA=1;
        const STATUS_ AUTORIZADA=2;
        const STATUS_ ACEPTADA=4;
        const STATUS_ CONTRARRECIBO=8;
        const STATUS_ EXPORTADA=16;
        const STATUS_ RESPALDADA=32;
        const STATUS_ PAGADA=64;
        const STATUS_ CANCELADA=128;
        -----------------------------
        BINFLAGS <?= $val ?>:
<?= arr2str($flags) ?>
        -----------------------------
        POST:
<?= arr2str($_POST); ?>

        -----------------------------
        FILES:
<?= arr2str($_FILES); ?>

        -----------------------------
        SESSION:
<?= arr2str($_SESSION); ?>

        -----------------------------
        COOKIES:
<?= arr2str($_COOKIE); ?>
    -->
