<?php

$autorizado=$hasUser&&($_esAdministrador||$_esSistemas||validaPerfil("Gestor"));
if (!$autorizado) {
    if ($hasUser) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}

clog2ini("configuracion.comparasat");
clog1seq(1);

$mes = $_now["n"];
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);

clog1seq(-1);
clog2end("configuracion.comparasat");
