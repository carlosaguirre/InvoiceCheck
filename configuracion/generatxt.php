<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaExpr)) $consultaExpr = consultaValida("Exportar");
$esAvance = validaPerfil("Avance");
if ($_esCompras && getUser()->nombre==="compras1") {
    $consultaExpr=true;
}
if (!$consultaExpr) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.generatxt");
clog1seq(1);

$tracelog = "";

if ($_esComprasB&&!$_esCompras)
    $gpoFullMapWhere= dao("gpo")->setOptSessions("Compras Basico",true);
else $gpoFullMapWhere = dao("gpo")->setOptSessions();
$gpoRazSocOpt = $_SESSION['gpoRazSocOpt'];
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt'];
$gpoRFCOpt = $_SESSION['gpoRFCOpt'];

$prvFullMapWhere = dao("prv")->setOptSessions();
$prvRazSocOpt = $_SESSION['prvRazSocOpt'];
$prvCodigoOpt = $_SESSION['prvCodigoOpt'];
$prvRFCOpt = $_SESSION['prvRFCOpt'];

$mes = $_now["n"];
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);

$stt = "Aceptadas";
$sttNombres = ["Temporal"=>"Temporal", "Pendiente"=>"Pendiente", "Aceptado"=>"Aceptado", "Contrarrecibo"=>"Contrarrecibo", "Exportado"=>"Exportado", "Respaldado"=>"Respaldado"];
$sttExportadas = ["Aceptadas"=>"No Exportadas","Exportadas"=>"Exportadas", "ExportadasHoy"=>"Exportadas Hoy", "ExportadasAhora"=>"Exportadas Ahora"];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.generatxt");
