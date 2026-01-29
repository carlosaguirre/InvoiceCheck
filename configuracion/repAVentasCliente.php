<?php
$isLocal=(isset($_SERVER["SERVER_NAME"])&&$_SERVER["SERVER_NAME"]==="localhost");
if (!$isLocal&&!$_esSistemas) {
    if(hasUser()) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.repAVentasCliente");
clog1seq(1);

require_once "clases/Avance.php";
$avnObj = new Avance();
$avnObj->target="avanceFrame";

if (empty($onloadScript)) $onloadScript="";
$onloadScript .= "repAVentasClienteInit();";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //
function getLoginForm() {
    global $avnObj;
    
}

clog1seq(-1);
clog2end("configuracion.repAVentasCliente");
