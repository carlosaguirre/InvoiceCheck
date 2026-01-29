<?php
if (!$hasUser) {
    header("Location: /$_project_name/");
    die("Redirecting to: $_project_name");
}
$esCuentas=validaPerfil("Cuentas Bancarias");
if (!$_esSistemas && !$esCuentas) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /$_project_name/");
    die("Redirecting to /".$_project_name."/");
}
/*
clog2ini("configuracion.cuentas");
clog1seq(1);

clog1seq(-1);
clog2end("configuracion.cuentas");
*/