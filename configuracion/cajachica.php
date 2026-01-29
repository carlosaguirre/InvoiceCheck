<?php
$esCajaChica=validaPerfil("Caja Chica");
$esAutorizador=validaPerfil("Autoriza Caja Chica");
$puedeEditar=$_esSistemas||$esCajaChica;
$puedeAutorizar=$_esSistemas||$esAutorizador;
if(!$puedeEditar && !$puedeAutorizar) {
    if ($hasUser) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.cajachica");
clog1seq(1);
$puedePagar = validaPerfil("Paga Caja Chica")||$_esSistemas;
$editAttrib=$puedeEditar?"":" readonly";
$testing=false;
$areaDetalleClass=" class=\"";
if (isset($_REQUEST["area_detalle"][0]))
    $areaDetalleClass.=$_REQUEST["area_detalle"];
else
    $areaDetalleClass.="scroll-60";
$areaDetalleClass.=" relative\"";

if (!isset($perObj)) {
    require_once "clases/Perfiles.php";
    $perObj=new Perfiles();
}
$ccId=$perObj->getIdByName("Caja Chica");
if (!isset($ugObj)) {
    require_once "clases/Usuarios_grupo.php";
    $ugObj=new Usuarios_Grupo();
}
$ugObj->rows_per_page=0;
$refundGroupId=$ugObj->getRefundGroupId(getUser()->id, $ccId, "vista");
if (isset($refundGroupId[1])) $gpWhere="id in (".implode(",",$refundGroupId).")";
else if (isset($refundGroupId[0])) $gpWhere="id=".$refundGroupId[0];
else $gpWhere=false;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj=new Grupo();
}
$gpoObj->rows_per_page=0;
$gpoObj->clearFullMap();
$gpoObj->clearOrder();
$gpoObj->addOrder("alias");
$grupoOptionMap=$gpoObj->getFullMap("id","alias",$gpWhere);
$groupOptions=getHtmlOptions($grupoOptionMap,null);

$conceptosMap=["CAJA CHICA SIN FACTURA"=>"CAJA CHICA SIN FACTURA","CAJA CHICA CON FACTURA"=>"CAJA CHICA CON FACTURA"];

if (isset($_REQUEST["datos"])) {
    $constants = get_defined_constants(true);
    $json_errors = array();
    foreach ($constants["json"] as $name => $value) {
        if (!strncmp($name, "JSON_ERROR_", 11)) {
            $json_errors[$value] = $name;
        }
    }
    $registro=json_decode($_REQUEST["datos"]);
    if (isset($registro)) {
        //clog2("REGISTRO: ".$_REQUEST["datos"]);
        if (isset($registro->pagadoPor[0])) $statusControl="<B>PAGADO</B>";
        else if (isset($registro->autorizadoPor[0])) $statusControl="<B>$registro->autorizadoPor</B>".($puedePagar?"<INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" onclick=\"paidRecord(event);\" auth=\"1\">":"");
        else if (isset($registro->rechazadoPor[0])) $statusControl="<B>$registro->rechazadoPor</B>".($puedePagar?"<INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" onclick=\"paidRecord(event);\" auth=\"0\">":"");
        else if (!$puedeAutorizar&&!$puedePagar) $statusControl="<B>PENDIENTE</B>";
    } else {
        $errno=json_last_error();
        $error=json_last_error_msg();
        clog2("JSON ERROR $errno".'['.$json_errors[$errno].']'.": $error\nJSON: ".$_REQUEST["datos"]);
    }
}
if (!isset($statusControl)) {
    if ($puedeAutorizar||$puedePagar) {
        $statusControl = "<SELECT id=\"control\" class=\"noprintBorder\"><OPTION value=\"\">PENDIENTE</OPTION><OPTION value=\"autorizar\">AUTORIZAR</OPTION><OPTION value=\"rechazar\">RECHAZAR</OPTION>".($puedePagar?"<OPTION value=\"pagado\">PAGADO</OPTION>":"")."</SELECT>";
    } else {
        $statusControl = "<INPUT type=\"text\" id=\"control\" class=\"nombreV padv02 noprintBorder\" readonly/>";
    }
}
$hasRecord=isset($registro);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //
function fixDate($dbdate) { // YYYY-MM-DD HH:II:SS => DD/MM/YYYY
    if (isset($dbdate[9])) return substr($dbdate, 8, 2)."/".substr($dbdate, 5, 2)."/".substr($dbdate, 0, 4);
    return "";
}

clog1seq(-1);
clog2end("configuracion.cajachica");
