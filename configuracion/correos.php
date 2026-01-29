<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaProc)) $consultaProc = consultaValida("Procesar");
if (!isset($modificaProc)) $modificaProc = modificacionValida("Procesar");
if (!$consultaProc) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.correos");
clog1seq(1);

require_once "clases/Grupo.php";
require_once "clases/Proveedores.php";

$tracelog = "";

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$gpoFullMapWhere = $gpoObj->setOptSessions();
$gpoRazSocOpt = $_SESSION['gpoRazSocOpt'];
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt'];
$gpoRFCOpt = $_SESSION['gpoRFCOpt'];

global $prvObj;
if(!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}
$prvFullMapWhere = $prvObj->setOptSessions();
$prvRazSocOpt = $_SESSION['prvRazSocOpt'];
$prvCodigoOpt = $_SESSION['prvCodigoOpt'];
$prvRFCOpt = $_SESSION['prvRFCOpt'];

$stt = "Pendiente";
$sttNombres = ["Temporal"=>"Temporal", "Pendiente"=>"Pendiente", "Aceptado"=>"Aceptado", "Contrarrecibo"=>"Contrarrecibo", "Exportado"=>"Exportado", "Respaldado"=>"Respaldado"];
$sttPendientes = ["Pendiente"=>"Pendientes", "NoPendiente"=>"Aceptadas", "Rechazadas"=>"Rechazadas"];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.correos");
