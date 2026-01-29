<?php
if (!$_esSistemas) {
    if ($hasUser) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600,"/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.adminfactura");
clog1seq(1);
global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$gpoFullMapWhere = $gpoObj->setCodigoOptSession();
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt'];

clog1seq(-1);
clog2end("configuracion.adminfactura");
