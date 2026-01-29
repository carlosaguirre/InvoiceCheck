<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Grupo.php";

$obj = new Grupo();
if (isValueService()) getValueService($obj);
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
else if (isSelectorHTML()) getSelectorHTML($obj);
else if (isCreateAliasCmd()) getCreateAliasCmd($obj);
//    else header("Location: /".$_project_name."/index.php");


function isSelectorHTML() {
    sessionInit();
    return isset($_SESSION['user']) && !empty($_REQUEST["selectorhtml"]);
}
function getSelectorHTML($obj) {
    if (isset($_REQUEST["tipolista"])) {
        $tipoLista = $_REQUEST["tipolista"];
        $esCodigo = $tipoLista==="tcodigo";
        $esRFC = $tipoLista==="trfc";
        $esRazon = $tipoLista==="trazon";
        if (!$esCodigo && !$esRFC && !$esRazon) $esCodigo=true;
    } else {
        $esCodigo=true; $esRFC=false; $esRazon=false;
    }
    if (isset($_REQUEST["defaultText"])) {
        $defaultText = $_REQUEST["defaultText"];
    } else {
        $defaultText = "Todas";
    }
    
    $gpoFullMapWhere=$obj->setOptSessions([],true);
    $gCod=$_SESSION['gpoCodigoOpt'];
    $gRFC=$_SESSION['gpoRFCOpt'];
    $gRzS=$_SESSION['gpoRazSocOpt'];
        

    $optionsCode = getHtmlOptions($gCod, (count($gCod)==1?key($gCod):""));
    $optionsRfc = getHtmlOptions($gRFC, (count($gRFC)==1?key($gRFC):""));
    $optionsRefer = getHtmlOptions($gRzS, (count($gRzS)==1?key($gRzS):""));
        
    echo "<select name=\"grupo\" id=\"gpotcodigo\" onchange=\"selectedItem('gpo');\"";
    if (!$esCodigo) echo " class=\"hidden\"";
    echo "><option value=\"\">$defaultText</option>";
    echo $optionsCode;
    echo "</select><select name=\"grupo\" id=\"gpotrfc\" onchange=\"selectedItem('gpo');\"";
    if (!$esRFC) echo " class=\"hidden\"";
    echo "><option value=\"\">$defaultText</option>";
    echo $optionsRfc;
    echo "</select><select name=\"grupo\" id=\"gpotrazon\" onchange=\"selectedItem('gpo');\"";
    if (!$esRazon) echo " class=\"hidden\"";
    echo "><option value=\"\">$defaultText</option>";
    echo $optionsRefer;
    echo "</select> ";
    echo "<img src=\"imagenes/icons/statusRight.png\" id=\"reloadGRP\" onLoad=\"setTimeout(function(){const me=ebyid('reloadGRP');me.onclick=recalculaEmpresas;me.title='Recalcular Empresas';cladd(me,'invisible');me.src='imagenes/icons/descarga6.png';me.onload=null;},3000);\">";
}
function isCreateAliasCmd() {
    return !empty($_GET["createAlias"]);
}
function getCreateAliasCmd($obj) {
    echo $obj->createAlias($_GET["createAlias"]);
}
