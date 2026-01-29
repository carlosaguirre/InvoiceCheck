<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";

if (isset($_POST["send"][0])||(isset($_GET["id"][0])&&isset($_GET["re"][0])&&isset($_GET["rr"][0])&&isset($_GET["tt"][0]))) {
    //$qr=$_POST["qr"]??"";
    $id=$_REQUEST["id"]??""; // UUID
    $re=$_REQUEST["re"]??""; // RFC Emisor
    $rr=$_REQUEST["rr"]??""; // RFC Receptor
    $tt=$_REQUEST["tt"]??""; // Total
    $fe=$_REQUEST["fe"]??""; // Ultimos 8 caracteres del sello digital del emisor
    if (isset($fe[8])) $fe=substr($fe, -8);
    //if (isset($qr[0])) {
    //    $id=""; $re=""; $rr=""; $tt="";
    //} else 
    if (isset($id[0])&&isset($re[0])&&isset($rr[0])&&isset($tt[0])) {
        $qr="?re=$re&rr=$rr&tt=$tt&id=$id";
        if (isset($fe[0])) $qr.="&fe=$fe";
    //}
    //if (isset($qr[0])) {
        require_once "configuracion/cfdi.php";
        $response=valida_en_sat($qr);
    }// else $error="NO HAY QR";
}
?>
<html>
<body>
<form method="POST">
<?php /* QR: <input type="text" name="qr" autofocus<?= isset($qr[0])?" value=\"$qr\"":"" ?>><br> */ ?>
UUID: <input type="text" name="id"<?= isset($id[0])?" value=\"$id\"":"" ?>><br>
RFC E: <input type="text" name="re"<?= isset($re[0])?" value=\"$re\"":"" ?>><br>
RFC R: <input type="text" name="rr"<?= isset($rr[0])?" value=\"$rr\"":"" ?>><br>
TOTAL: <input type="text" name="tt"<?= isset($tt[0])?" value=\"$tt\"":"" ?>><br>
<?php /* SELLO: <input type="text" name="fe"<?= isset($fe[0])?" value=\"$fe\"":"" ?>><br> */ ?>
<input type="submit" name="send" value="Enviar">
</form>
<?php
if (isset($response)) {
    echo "<HR>";
    foreach ($response as $key => $value) { // expresionImpresa, cfdi, estado, escancelable, estatuscancelacion
        $value=str_replace(["<",">"],["&lt;","&gt;"],$value);
        echo "<PRE>$key : '$value'</PRE><BR>";
    }
}
if (isset($error[0])) {
    echo "<HR>ERROR: $error";
}
?>
</body>
</html>
