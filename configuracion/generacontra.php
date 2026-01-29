<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$modificaConR=($_esSistemas?true:modificacionValida("Contrarrecibo"));
if (!$modificaConR) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.generacontra");
clog1seq(1);

$_SESSION["gpoIdOpt"]=null;
$_SESSION['prvIdOpt']=null;

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$optDefaultValue=$_SESSION['optDefaultValue']; // Definido en Grupo
if ($_esComprasB&&!$_esCompras)
    $gpoFullMapWhere= $gpoObj->setIdOptSessions(["Compras Basico"],$optDefaultValue);
else $gpoFullMapWhere = $gpoObj->setIdOptSessions(["Compras"],$optDefaultValue);
$gpoIdOpt = $_SESSION['gpoIdOpt'];
$gpoRazSoc2Id=$_SESSION['gpoRazSoc2Id'];
$gpoCodigo2Id=$_SESSION['gpoCodigo2Id'];
$gpoRFC2Id=$_SESSION['gpoRFC2Id'];
$esCompras=isset($gpoFullMapWhere[0]);
$defaultGpoId="";

global $prvObj;
if (!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}
$prvObj->setIdOptSessions(0,$optDefaultValue);
$prvIdOpt = $_SESSION['prvIdOpt'];
$prvRazSoc2Id=$_SESSION['prvRazSoc2Id'];
$prvCodigo2Id=$_SESSION['prvCodigo2Id'];
$prvRFC2Id=$_SESSION['prvRFC2Id'];
$defaultPrvId="";

reset($gpoCodigo2Id);
reset($gpoRFC2Id);
reset($gpoRazSoc2Id);
reset($prvCodigo2Id);
reset($prvRFC2Id);
reset($prvRazSoc2Id);

$mes = $_now["n"];
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);

// ToDo_SOLICITUD: Aviso al ingresar a genera contra
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.generacontra");
