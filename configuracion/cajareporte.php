<?php
if (!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esReporte=validaPerfil("Caja Reporte")||$_esSistemas;
$esRespalda=validaPerfil("Caja Respaldo")||$_esSistemas;
if(!$esReporte&&!$esRespalda) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.cajareporte");
clog1seq(1);

$ccId=dao("per")->getIdByName("Caja Reporte");
$refundGroupId=dao("ug", ["rows_per_page" => 0])->getRefundGroupId($userid, $ccId, "vista");
if (isset($refundGroupId[1])) $gpWhere="id in (".implode(",",$refundGroupId).")";
else if (isset($refundGroupId[0])) $gpWhere="id=".$refundGroupId[0];
else $gpWhere=false;
$grupoOptionMap=dao("gpo", ["rows_per_page" => 0, "orderlist"=>["alias"=>"asc"], "fullmap"=>null])->getFullMap("id","alias",$gpWhere);
$idLst=array_keys($grupoOptionMap);
$repLogoAttribs="";
if (isset($idLst[1])) $groupOptions="<OPTION value=\"todas\">TODAS</OPTION>";
else {
    $groupOptions="";
    if (isset($idLst[0])) $repLogoAttribs=" class=\"doprintBlock\" style=\"background-image: url(imagenes/logos/".mb_strtolower($grupoOptionMap[$idLst[0]]).".png) !important;\"";
}
$groupOptions.=getHtmlOptions($grupoOptionMap,null);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.cajareporte");
