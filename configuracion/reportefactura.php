<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$consultaProc = consultaValida("Procesar");
$modificaProc = modificacionValida("Procesar");
if (!$consultaProc) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.reportefactura");
clog1seq(1);

require_once "clases/Grupo.php";
require_once "clases/Proveedores.php";

/* 
unset($_SESSION['gpoRazSocOpt']);
unset($_SESSION['gpoCodigoOpt']);
unset($_SESSION['gpoRFCOpt']);
unset($_SESSION['prvRazSocOpt']);
unset($_SESSION['prvCodigoOpt']);
unset($_SESSION['prvRFCOpt']);
 */

$tracelog = "";
$esAvance = validaPerfil("Avance");
$esGestor = validaPerfil("Gestor");
$esRevision = validaPerfil("Revision");
$esAltaFactura = validaPerfil("Alta Factura");

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
//$gpoFullMapWhere = $gpoObj->setOptSessions($_esCompras?"Compras":($_esComprasB?"Compras Basico":""),true);
$gpoFullMapWhere = $gpoObj->setOptSessions($_esComprasB&&!$_esCompras?"Compras Basico":"Compras",true);
clog2("GPO WHERE: $gpoFullMapWhere");
$gpoRazSocOpt = $_SESSION['gpoRazSocOpt']??[];
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt']??[];
$gpoRFCOpt = $_SESSION['gpoRFCOpt']??[];
$esCompras=isset($gpoFullMapWhere[0])&&!$_esProveedor;

global $prvObj;
if(!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}
$prvFullMapWhere = $prvObj->setOptSessions(true);
clog2("PRV WHERE: $prvFullMapWhere");
$prvRazSocOpt = $_SESSION['prvRazSocOpt']??[];
$prvCodigoOpt = $_SESSION['prvCodigoOpt']??[];
$prvRFCOpt = $_SESSION['prvRFCOpt']??[];

$mes = $_now["n"];
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);

$stt = "Pendiente";
if ($_esSistemas) {
    $sttPendientes = ["Temporal"=>"Temporales", "Pendiente"=>"Pendientes", "Procesado"=>"Aceptadas", "AltaRango"=>"Por F.Captura", "CambioRango"=>"Por F.Ajuste", "VerificaCFDI"=>"Verifica CFDI", "SinCPago"=>"Sin C.Pago", "Pagado"=>"Pagadas", "Pagos"=>"Pagos","PagosPendientes" => "Pagos Pendientes",
//    ...($_esDesarrollo ? ["PagosAceptados" => "Pagos Aceptados"] : []),
//    ...($_esDesarrollo ? ["PagosIncompletos" => "Pagos Incompletos"] : []),
//    ...($_esDesarrollo ? ["PagosAMedias" => "Pagos A Medias"] : []),
    "PUE"=>"PUE", "PUESinPago"=>"PUE sin Pago", "EA"=>"EA", "Rechazado"=>"Rechazadas"];
} else if ($_esCompras||$esRevision) {
    $sttPendientes = ["Pendiente"=>"Pendientes", "Procesado"=>"Por Subir a Avance", "Contrarrecibo"=>"Con Contrarrecibo", "Avance"=>"En Avance"];
    if ($esGestor||$esRevision) $sttPendientes["VerificaCFDI"]="Verifica CFDI";
    $sttPendientes["Pagado"]="Pagadas";
    $sttPendientes["Pagos"]="Pagos";
    $sttPendientes["PagosPendientes"]="Pagos Pendientes";
    $sttPendientes["SinCPago"]="Sin C.Pago";
    $sttPendientes["PUE"]="PUE";
    $sttPendientes["PUESinPago"]="PUE sin Pago";
    $sttPendientes["Rechazadas"]="Rechazadas";
} else
    $sttPendientes = ["Pendiente"=>"Pendientes", "NoPendiente"=>"Aceptadas", "Pagos"=>"Pagos", "SinCPago"=>"Sin C.Pago", "Rechazadas"=>"Rechazadas"];
if (isset($_POST)) clog2("POST: ".json_encode($_POST));
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.reportefactura");
