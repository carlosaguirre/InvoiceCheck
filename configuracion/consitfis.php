<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
/*
if (!isset($consultaX)) $consultaX = consultaValida("X");
if(!$_esSistemas && !$consultaX) {
    setcookie("menu_accion", "", time() - 3600);
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
*/

//clog2ini("configuracion.consitfis");
//clog1seq(1);

//clog2("TEST!!!");
// ToDo: if !hasUser redirect to login
$esConSitFis=validaPerfil("Constancias Corporativo")||$_esSistemas;
$gpoSize=10;
$gpoWhere="status='activo' and conSitFis is not null";
if ($_esProveedor) {
    global $query;
    $invData = dao("inv", ["rows_per_page" => 0])->getData("codigoProveedor='$username'", 0, "distinct rfcGrupo");
    $rfcGpoList = array_column($invData,"rfcGrupo");
    if (!isset($rfcGpoList[$gpoSize])) {
        $gpoSize=count($rfcGpoList);
        clog2("GPOLIST= $gpoSize");
    }
    $gpoWhere .= " and rfc in ('".implode("','", $rfcGpoList)."')";
    clog2("GPOWHERE= '$gpoWhere'");
} //else clog2("NO ES PROVEEDOR. QUERY: $query");

$gpoData=dao("gpo", ["rows_per_page"=>0, "orderlist"=>["alias"=>"asc"]])->getData($gpoWhere,0,"id,conSitFis,alias,conSitFisTimes");
global $query;
//clog2("QUERY: $query");
if (!isset($gpoData[$gpoSize])) {
    $gpoSize=count($gpoData);
    clog2("GPOLIST active not null= $gpoSize");
}
//clog2("DATA: ".json_encode($gpoData));

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

//clog1seq(-1);
//clog2end("configuracion.consitfis");
