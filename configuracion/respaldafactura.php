<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaResp)) $consultaResp = consultaValida("Respaldar");
$esAvance = validaPerfil("Avance");
if ($_esCompras && $username==="compras1") {
    $consultaResp=true;
}
if (!$consultaResp) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.respaldafactura");
clog1seq(1);

$tracelog = "";

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
if ($_esComprasB&&!$_esCompras)
    $gpoFullMapWhere= $gpoObj->setOptSessions("Compras Basico",true);
else $gpoFullMapWhere = $gpoObj->setOptSessions();
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
if ($_esSistemas) {
    $gpoVal=array_keys($gpoCodigoOpt);
    $prvVal=array_keys($prvCodigoOpt);
} else {
    $gpoVal=$_POST["grupo"]??(count($gpoCodigoOpt)==1?key($gpoCodigoOpt):[]);
    $gpoCodigoOpt=["Todos"=>"Todas"]+$gpoCodigoOpt;
    $prvVal=$_POST["proveedor"]??(count($prvCodigoOpt)==1?key($prvCodigoOpt):[]);
    $prvCodigoOpt=["Todos"=>"Todas"]+$prvCodigoOpt;
}

$mes = $_now["n"];
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);

$stt = "Aceptadas";
$sttNombres = ["Temporal"=>"Temporal", "Pendiente"=>"Pendiente", "Aceptado"=>"Aceptado", "Contrarrecibo"=>"Contrarrecibo", "Respaldado"=>"Respaldado"];
$sttRespaldadas = ["Aceptadas"=>"No Respaldadas", "Respaldadas"=>"Respaldadas", "RespaldadasHoy"=>"Respaldadas Hoy", "RespaldadasAhora"=>"Respaldadas Ahora"];
if ($_esSistemas) {
    $sttRespaldadas["RespaldadasRango"]="Respaldadas en Rango";
}
$sttRespaldadas["RespPagos"]="Complementos de Pago";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.respaldafactura");
