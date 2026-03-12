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

$tracelog = "";

$gpoFullMapWhere = dao("gpo")->setOptSessions();
$gpoRazSocOpt = $_SESSION['gpoRazSocOpt'];
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt'];
$gpoRFCOpt = $_SESSION['gpoRFCOpt'];

$prvFullMapWhere = dao("prv")->setOptSessions();
$prvRazSocOpt = $_SESSION['prvRazSocOpt'];
$prvCodigoOpt = $_SESSION['prvCodigoOpt'];
$prvRFCOpt = $_SESSION['prvRFCOpt'];

$stt = "Pendiente";
$sttNombres = ["Temporal"=>"Temporal", "Pendiente"=>"Pendiente", "Aceptado"=>"Aceptado", "Contrarrecibo"=>"Contrarrecibo", "Exportado"=>"Exportado", "Respaldado"=>"Respaldado"];
$sttPendientes = ["Pendiente"=>"Pendientes", "NoPendiente"=>"Aceptadas", "Rechazadas"=>"Rechazadas"];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.correos");
