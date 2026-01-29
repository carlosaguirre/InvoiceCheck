<?php
header('charset=UTF-8');
require_once dirname(__DIR__)."/bootstrap.php";
if (hasUser()) {
    $esAdmin      = validaPerfil("Administrador");
    $esSistemas   = validaPerfil("Sistemas")||$esAdmin;
    $esDesarrollo = hasUser() && getUser()->nombre==="admin";
    $esProveedor  = validaPerfil("Proveedor");
    $isInteractive=!$esProveedor;
} else {
    $esSistemas   =false;
    $esDesarrollo =false;
    $isInteractive=false;
}
?>
<!DOCTYPE html>
<?php
$browser = getBrowser();
$isMSIE = ($browser==="Edge" || $browser==="IE");
require_once "templates/generalScript.php";
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <?= $isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= getBaseURL() ?>" target="_self">
    <title>RESPUESTA DE SOLICITUD DE PAGO</title>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="scripts/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="scripts/bootstrap-3.3.2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-multiselect.css"/>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-3.3.2.min.css"/>
<?php
    if ($isMSIE) echoPolyfillScript();
    echoGeneralScript();
?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>
    <script src="scripts/pdfviewer.php" type="text/javascript"></script>
    <script src="scripts/respGralSolPago.php" type="text/javascript"></script>
  </head>
  <body>
<?php
    $keyMap=["BASEURL"=>getBaseURL(),"HOSTNAME"=>getBaseURL(),"FONDODBOX"=>$waitImgName,"BTNSTY"=>"","ERRCLOSE"=>""];
    $ignoreList=["display"];
    foreach ($_REQUEST as $key => $value) {
        if (in_array($key, $ignoreList)) {
            switch($key) {
                case "display": $keyMap["BTNSTY"]="display:{$value};";
                    break;
            }
            continue;
        }
        $keyMap[$key]=$value;
    }
    if (!isset($keyMap["RESPUESTA"][0])) $keyMap["RESPUESTA"]="";
    ob_start();
    ob_implicit_flush(false);
    include "templates/solforma.php";
    //if ($esDesarrollo) { // ToDo: Mostrar un botón por autorizador, para reenviar solo un correo
?>
    <!-- div><input type="button" value="ENVIAR CORREO" class="noprint" style="margin-bottom:10px;"></div -->
<?php
    //}
    $keyMap["RESPUESTA"].=ob_get_clean();

    if (!isset($keyMap["SOLID"])) $keyMap["SOLID"]="";
    if (!isset($keyMap["SOLFOLIO"])) $keyMap["SOLFOLIO"]="";
    if (!isset($keyMap["ENCABEZADO"])) $keyMap["ENCABEZADO"]="SOLICITUD DE AUTORIZACI&Oacute;N DE PAGO $keyMap[SOLFOLIO]";
    $keyMap["BUTTONS"]=($isInteractive&&$esSistemas)?"<!-- I&S -->":"<!-- 5 -->";
    //$mensaje = file_get_contents(getBasePath()."templates/respuestaSolPago.html");
    $mensaje = file_get_contents(getBasePath()."templates/respGralSolPago.html");
    $mensaje = str_replace(array_map(function($elem){return "%".$elem."%";},array_keys($keyMap)),array_values($keyMap), $mensaje);
    echo $mensaje;
?>
  </body>
</html>
<?php
if ($isInteractive && $esSistemas) {
?>
<!-- 
    -----------------------------
    GET:
    <?= arr2str($_GET); ?>

    -----------------------------
    POST:
    <?= arr2str($_POST); ?>

    -----------------------------
    FILES:
    <?= arr2str($_FILES); ?>
<?php
//    -----------------------------
//    SESSION:
//    < ?= arr2str($_SESSION); ? >
//
//    -----------------------------
//    COOKIES:
//    < ?= arr2str($_COOKIE); ? >
?>
-->
<?php
}
