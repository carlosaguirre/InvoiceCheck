<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
//echo "<!-- PREPARANDO... -->\n";
$consultaCR = consultaValida("Contrarrecibo");
$autorizaCR = validaPerfil("Autoriza Contra Recibos"); // abre Vencidos
$abreSinAutorizar = $autorizaCR && in_array(getUser()->nombre, ["dmenasse"]);
clog2("PERMISOS: ".json_encode(getUser()->permisos));
//echo "<!-- consultaCR : ".($consultaCR?"true":"false")." -->\n";
if (!$consultaCR&&!$autorizaCR&&!$_esSistemas) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
//echo "<!-- perfiles: ".json_encode(getUser()->perfiles)." -->\n";
//echo "<!-- permisos: ".json_encode(getUser()->permisos)." -->\n";
clog2ini("configuracion.contrarrecibos");
clog1seq(1);

require_once "clases/Grupo.php";
require_once "clases/Proveedores.php";

$tracelog = "";

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
if ($_esComprasB&&!$_esCompras)
    $gpoFullMapWhere= $gpoObj->setOptSessions("Compras Basico",true);
else $gpoFullMapWhere = $gpoObj->setOptSessions(["Autoriza Contra Recibos","Compras"]);
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

$statusOptList="<option value=''>Todos</option><option value='expired'".(($autorizaCR&&!$abreSinAutorizar)?" selected":"").">Vencidos</option><option value='noauth'".($abreSinAutorizar?" selected":"").">Sin Autorizar</option><option value='auth'>Autorizados</option>";

$mes = $_now["n"];
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.contrarrecibos");
