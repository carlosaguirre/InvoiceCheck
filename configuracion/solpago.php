<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esSolPago=$_esCompras&&validaPerfil("Solicita Pagos");
//$esAuthPago=validaPerfil("Autoriza Pagos");
$consultaSolPago=$esSolPago||validaPerfil("Consulta Solicitudes");//||$esAuthPago;
if (!$_esSistemas && !$consultaSolPago) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}

clog2ini("configuracion.solpago");
clog1seq(1);
if (isset($_POST["reMap"][0])) {
    unset($_SESSION[$_POST["reMap"]."Map"]);
}
$monedaOI=" <input type=\"button\" id=\"ord_moneda\" class=\"wid40px\" value=\"MXN\" onclick=\"this.value=(this.value==='MXN'?'USD':(this.value==='USD'?'EUR':'MXN'));\">";
global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$optDefaultValue=$_SESSION['optDefaultValue']; // Definido en Grupo
$gpoFullMapWhere=$gpoObj->setIdOptSessions($_esCompras?["Compras"]:($_esComprasB?["Compras Basico"]:["Autoriza Pagos"]),$optDefaultValue);
$gpoMap=[];
$gpoAliasOpt=[];
foreach ($_SESSION['gpoIdOpt'] as $gpoId => $gv) {
    $gpoMap[$gpoId]=["alias"=>$gv["codigo"],"rfc"=>$gv["rfc"],"razonSocial"=>$gv["razon"]];
    $gpoAliasOpt[$gpoId]=$gv["codigo"];
}
$_SESSION['gpoMap'] = $gpoMap;
if (count($gpoAliasOpt)==1) {
    $gpoAliasDefault = key($gpoAliasOpt);
    $gpoRazSocDefault = $gpoMap[$gpoAliasDefault]["razonSocial"];
} else {
    $gpoAliasDefault = "";
    $gpoRazSocDefault = "";
    $gpoAliasOpt = [""=>""] + $gpoAliasOpt;
}
global $prvObj;
if (!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}
$prvObj->setIdMap();
$prvMap = $_SESSION['prvMap'];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.solpago");
