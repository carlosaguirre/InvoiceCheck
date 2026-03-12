<?php
/*
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaX)) $consultaX = consultaValida("X");
if(!$_esSistemas && !$consultaX) {
    setcookie("menu_accion", "", time() - 3600);
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
*/

clog2ini("configuracion.comext");
clog1seq(1);

$optDefaultValue=$_SESSION['optDefaultValue'];
$gpoFullMapWhere = dao("gpo")->setIdOptSessions(["Compras"],$optDefaultValue);
$gpoIdOpt = $_SESSION['gpoIdOpt'];
//$esCompras=isset($gpoFullMapWhere[0]);

$prvObj=dao("prv");
$prvObj->setIdOptSessions(1000,$optDefaultValue);
$_SESSION['extIdOpt']=$_SESSION['prvIdOpt'];
$prvObj->setIdOptSessions(4,$optDefaultValue);
$_SESSION['agtIdOpt']=$_SESSION['prvIdOpt'];

if (isset($_POST["comextChoice"][0])) $_SESSION["comextChoice"]=$_POST["comextChoice"];
else $_SESSION["comextChoice"]=null;

$testStr="BEGIN";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.comext");
