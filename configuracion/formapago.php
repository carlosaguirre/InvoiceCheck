<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!$_esSistemas) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.formapago");
clog1seq(1);

require_once "clases/catalogoSAT.php";
require_once "clases/MetodosDePago.php";
CatalogoSAT::setOrder(CatalogoSAT::CAT_FORMAPAGO, [["codigo"]]);
$catData = CatalogoSAT::getData(CatalogoSAT::CAT_FORMAPAGO);
$mdpObj = new MetodosDePago();
$mdpObj->rows_per_page=0;
$mdpObj->clearFullMap();
$mdpMap = $mdpObj->getFullMap("clave", "descripcion");
//$mdpData = $mdpObj->getData();
//$mdpHeaders = $mdpObj->fetch_headers;
//clog2("MDP HEADERS: ".json_encode($mdpHeaders));

// ToDo: Obtener mdpMap de clave y descripcion.
//       Usar codigo de catsat para el id y name de cada checkbox
//       Usar mdpMap para determinar checked al comparar clave de mdp y codigo de catsat.

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.formapago");
