<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$consultaRepo = consultaValida("Reportes")||$_esSistemas;
if (!$consultaRepo) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.reportes");
clog1seq(1);

$tracelog = "";

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$gpoFullMapWhere = $gpoObj->setCodigoOptSession();
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt'];

$prvFullMapWhere = ($_esProveedor?"codigo='".$username."'":false);

$rango = isset($_POST["rango"])?$_POST["rango"]:"diario";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.reportes");
