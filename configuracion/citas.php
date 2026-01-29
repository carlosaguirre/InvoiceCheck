<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$clogMsgs=[];
if (!isset($generaCitas)) $generaCitas=($_esProveedor&&getUser()->nombre==="I-025");
if (!isset($consultaCitas)) $consultaCitas=validaPerfil("Consulta Citas");
if (!isset($modificaCitas)) $modificaCitas=validaPerfil("Modifica Citas");
if($_esAdministrador) $clogMsgs[]="ADMIN";
if($_esSistemas) $clogMsgs[]="SISTEMAS";
if($_esProveedor) $clogMsgs[]="PROVEEDOR";
if($generaCitas) $clogMsgs[]="GENERA";
if($consultaCitas) $clogMsgs[]="CONSULTA";
if($modificaCitas) $clogMsgs[]="MODIFICA";

if (!$_esAdministrador&&!$_esSistemas&&!$generaCitas&&!$consultaCitas&&!$modificaCitas) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.citas");
clog1seq(1);

if (isset($clogMsgs[0])) clog2(implode(", ", $clogMsgs));

if($generaCitas) {
    $titulo="SOLICITUD DE CITAS";
    $intro="Aparte horario de entrega o recolección de material";
} else if ($modificaCitas||$_esAdministrador||$_esSistemas) {
    $titulo="LISTADO DE CITAS";
    $intro="Consulte y valide las citas solicitadas";
} else if ($consultaCitas) {
    $titulo="LISTADO DE CITAS";
    $intro="Consulte y atienda las citas acordadas";
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.citas");
