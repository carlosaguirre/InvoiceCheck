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

clog2ini("configuracion.eventos");
clog1seq(1);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.eventos");
