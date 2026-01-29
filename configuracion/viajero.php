<?php
if (!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esSolicitante=getUser()->nombre==="viajero";
$esViaticos=validaPerfil("Viaticos");
$esAutorizador=validaPerfil("Autoriza Viaticos");
$puedeEditar=$_esSistemas||$esViaticos||$esSolicitante;
$puedeAutorizar=$_esSistemas||$esAutorizador;
if(!$puedeEditar && !$puedeAutorizar) {
    //console.log("ES SOLICITANTE? ".($esSolicitante?"SI":"NO"));
    //console.log("ES ADMIN? ".($_esAdministrador?"SI":"NO"));
    //console.log("ES SISTEMAS? ".($_esSistemas?"SI":"NO"));
    //console.log("ES VIATICOS? ".($esViaticos?"SI":"NO"));
    //console.log("ES AUTORIZADOR? ".($esAutorizador?"SI":"NO"));
    //console.log("ES COMPRAS? ".($_esCompras?"SI":"NO"));
    //console.log("PUEDE EDITAR? ".($puedeEditar?"SI":"NO"));
    //console.log("PUEDE AUTORIZAR? ".($puedeAutorizar?"SI":"NO"));
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.viajero");
clog1seq(1);

$puedePagar = validaPerfil("Paga Viaticos")||$_esSistemas;
$statusControl = ($puedeAutorizar||$puedePagar)?"<SELECT id=\"control\" class=\"noprintBorder\" onchange=\"console.log('CONTROL CHANGED TO '+this.value);\"><OPTION value=\"\">PENDIENTE</OPTION><OPTION value=\"autorizar\">AUTORIZAR</OPTION><OPTION value=\"rechazar\">RECHAZAR</OPTION>".($puedePagar?"<OPTION value=\"pagado\">PAGADO</OPTION>":"")."</SELECT>":"<INPUT type=\"text\" id=\"control\" class=\"nombreV padv02 noprintBorder\" readonly/>";

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

if (isset($_REQUEST["datos"])) {
    $constants = get_defined_constants(true);
    $json_errors = array();
    foreach ($constants["json"] as $name => $value) {
        if (!strncmp($name, "JSON_ERROR_", 11)) {
            $json_errors[$value] = $name;
        }
    }
    $registro=json_decode($_REQUEST["datos"]);
    if (!$registro) {
        $errno=json_last_error();
        $error=json_last_error_msg();
        clog2("JSON ERROR $errno: ".'['.$json_errors[$errno].']'.": $error\nJSON: ".$_REQUEST["datos"]);
    } else {
        clog2("REGISTRO: ".$_REQUEST["datos"]);
        if (isset($registro->pagadoPor[0])) $statusControl="<B>PAGADO</B>";
        else if (isset($registro->autorizadoPor[0])) $statusControl="<B>$registro->autorizadoPor</B>".($puedePagar?"<INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" onclick=\"paidRecord(event);\" auth=\"1\">":"").($esSistemas?" <INPUT type=\"button\" id=\"resetStatusButton\" value=\"CAMBIAR A PENDIENTE\" onclick=\"restoreToPending(event);\">":"");
        else if (isset($registro->rechazadoPor[0])) $statusControl="<B>$registro->rechazadoPor</B>".($puedePagar?"<INPUT type=\"button\" id=\"paidButton\" value=\"CAMBIAR A PAGADO\" onclick=\"paidRecord(event);\" auth=\"0\">":"").($esSistemas?" <INPUT type=\"button\" id=\"resetStatusButton\" value=\"CAMBIAR A PENDIENTE\" onclick=\"restoreToPending(event);\">":"");
        else if ($esSolicitante||(!$puedeAutorizar&&!$puedePagar)) $statusControl="<B>PENDIENTE</B>";
    }
}
$hasRecord=isset($registro);
$esNuevoSolicitante = (!$hasRecord&&$esSolicitante);

function fixDate($dbdate) { // YYYY-MM-DD HH:II:SS => DD/MM/YYYY
    if (isset($dbdate[9])) return substr($dbdate, 8, 2)."/".substr($dbdate, 5, 2)."/".substr($dbdate, 0, 4);
    return "";
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.viajero");
