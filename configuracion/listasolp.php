<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esGestor = validaPerfil("Gestor");
$esSolPago=$_esCompras&&validaPerfil("Solicita Pagos");
$veSolPago=validaPerfil("Consulta Solicitudes");
$esAuthPago=validaPerfil("Autoriza Pagos");
$esRealizaPago=validaPerfil("Realiza Pagos");
$esGestionaPago=validaPerfil("Gestiona Pagos");
if(!$_esSistemas && !$esSolPago && !$esAuthPago && !$esRealizaPago && !$esGestionaPago && !$veSolPago) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    doclog("configuracion.listasolp: NO AUTORIZADO","error",["menu_accion"=>""]);
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.listasolp");
clog1seq(1);
global $solObj; if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); } $solObj->rows_per_page=0;
$showList=[];
$qryList=[];
$statusList=[];
$tipoFecha=$_POST["tipoFecha"]??"solicitud";
$tipoFechaCap="Ini.".strtoupper($tipoFecha[0]).substr($tipoFecha, 1,2).".";
$lookoutFilePath = "";
if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];

$anio = $_now["y"];
$maxdia = $_now["t"];
$diaStr = $_now["d"];
$maxStr = $_now["t"];
$mesStr = $_now["m"];
$mesAnioStr = "/{$mesStr}/$anio";
$anioMesActual=intval($anio.$mesStr);
$updateTimeWeek = time()+3600*24*5;
$timeWeek = time()+3600*24*7;
$firstDayYr = "01/01/$anio";
$firstDayNoPay = ""; // obtener día de la primer solicitud sin pagar
$firstDay = "01$mesAnioStr";
$toDay = $diaStr.$mesAnioStr;
$lastDay = $maxStr.$mesAnioStr;
$defaultIniDate=$firstDay;
if ($esRealizaPago) {
    global $ugObj; if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj=new Usuarios_Grupo(); } //$ugObj->clearOrder(); //$ugObj->addOrder("alias");
    $ugObj->rows_per_page=0;
    $seccionId=["ParaPago","Pagadas","Procesando","Rechazadas"];
    $empresas=$ugObj->getIdGroupByNames(getUser(),"Realiza Pagos");
    $solData=$solObj->getData("status<64 and idEmpresa in (".implode(",", $empresas).") and ((idOrden is null and proceso between 2 and 3) or (idFactura is null and status=2 and proceso=0))",0,"date(min(fechaInicio)) fecha");
    $fecha=$solData[0]["fecha"]??"";
    if (isset($fecha[9]))
        $defaultIniDate=substr($fecha, -2)."/".substr($fecha, 5, 2)."/".substr($fecha, 0, 4);
    else $defaultIniDate=$firstDayYr;
}
$defaultEndDate=$lastDay;
$filtroStr=$_COOKIE["filtroListaSolP"]??"";
$oldFiltroStr=$filtroStr;
$flspDone=false;
if (empty($filtroStr)||$filtroStr==="[]"||$filtroStr==="()"||$filtroStr==="null") {
    $filtroStr=json_encode(["filter01"=>[$defaultIniDate,$defaultEndDate]]);
    doclog("FILTRO FIX 1","cookies",["oldFiltroStr"=>$oldFiltroStr,"filtroStr"=>$filtroStr]);
    setcookie("filtroListaSolP",$filtroStr,$timeWeek, "/invoice");
    setcookie("timeWeek",$timeWeek,$timeWeek,"/invoice");
    $oldFiltroStr=$filtroStr;
    $flspDone=true;
}
//if ($_esDesarrollo || getUser()->nombre==="dianan") doclog("1- filtros: $filtroStr","action");
$defaultIniDateCookie = $_COOKIE["defaultIniDate"]??null;
$defaultEndDateCookie = $_COOKIE["defaultEndDate"]??null;
$todayCookie=$_COOKIE["todayCookie"]??null;
$lastAccess=$_COOKIE["lastAccess"]??null;
try {
    $filtros=json_decode($filtroStr,true);
} catch (Exception $ex) {
    doclog("configuracion.listasolp: FILTER ERROR","error",["filtros"=>$filtroStr]);
}
$ckLogData=[];
if (!isset($lastAccess[0])||$lastAccess!==$toDay) {
    setcookie("lastAccess",$toDay,$timeWeek,"/invoice");
    $ckLogData["lastAccess"]=$toDay;
}
if (!isset($defaultIniDateCookie)||$defaultIniDate!==$defaultIniDateCookie) {
    if (isset($defaultIniDateCookie[0])||!isset($filtros["filter01"])) {
        $filtros["filter01"] = [$defaultIniDate,$defaultEndDate];
        $filtroStr=json_encode($filtros);
        if ($filtroStr!==$oldFiltroStr) {
            doclog("FILTRO FIX 2","cookies",["oldFiltroStr"=>$oldFiltroStr,"filtroStr"=>$filtroStr,"iniDateCookie"=>$defaultIniDateCookie,"iniDate"=>$defaultIniDate]);
            setcookie("filtroListaSolP",$filtroStr,$timeWeek, "/invoice");
            setcookie("timeWeek",$timeWeek,$timeWeek,"/invoice");
            $oldFiltroStr=$filtroStr;
            $flspDone=true;
        }
        $ckLogData["filtroListaSolP"]=$filtroStr;
        //if ($_esDesarrollo || getUser()->nombre==="dianan") doclog("2- filtros: $filtroStr","action");
    }
    setcookie("defaultIniDate",$defaultIniDate,$timeWeek, "/invoice");
    $ckLogData["defaultIniDate"]=$defaultIniDate;
    setcookie("defaultEndDate",$defaultEndDate,$timeWeek,"/invoice");
    $ckLogData["defaultEndDate"]=$defaultEndDate;
    setcookie("todayCookie",$toDay,$timeWeek, "/invoice");
    $ckLogData["todayCookie"]=$toDay;
} else if (!isset($todayCookie)||$todayCookie!==$toDay) {
    if (isset($todayCookie[0])) {
        for ($i=1; $i <= 3; $i++) {
            $filterKey="filter0".$i;
            if (isset($filtros[$filterKey]))
                updateFilterDate($filtros[$filterKey],$todayCookie, $toDay);
        }
        if (isset($filtros["filter14"]))
            updateFilterDate($filtros["filter14"],$todayCookie, $toDay);
        $filtroStr=json_encode($filtros);
        if ($filtroStr!==$oldFiltroStr) {
            doclog("FILTRO FIX 3","cookies",["oldFiltroStr"=>$oldFiltroStr,"filtroStr"=>$filtroStr,"todayCookie"=>$todayCookie,"toDay"=>$toDay]);
            setcookie("filtroListaSolP",$filtroStr,$timeWeek, "/invoice");
            setcookie("timeWeek",$timeWeek,$timeWeek,"/invoice");
            $oldFiltroStr=$filtroStr;
            $flspDone=true;
        }
        $ckLogData["filtroListaSolP"]=$filtroStr;
        //if ($_esDesarrollo || getUser()->nombre==="dianan") doclog("3- filtros: $filtroStr","action");
    }
    setcookie("todayCookie",$toDay,$timeWeek, "/invoice");
    $ckLogData["todayCookie"]=$toDay;
}
if (!$flspDone) {
    $timeWeekCookie=$_COOKIE["timeWeek"]??null;
    if (!isset($timeWeekCookie)||$updateTimeWeek>$timeWeekCookie) {
        setcookie("filtroListaSolP",$filtroStr,$timeWeek, "/invoice");
        doclog("FILTRO FIX 4","cookies",["oldTimeWeek"=>$timeWeekCookie,"newTimeWeek"=>$timeWeek,"filtroStr"=>$filtroStr]);
        setcookie("timeWeek",$timeWeek,$timeWeek,"/invoice");
    }
}

//if (!empty($ckLogData) && ($_esDesarrollo || getUser()->nombre==="dianan")) doclog("configuracion.listasolp: COOKIE LOG","action",$ckLogData);
$todasSecciones=[
    "NoAutorizadas"=>[ // sistemas,autoriza,solicita
        "title"=>"Solicitudes sin Autorizar",
        "status"=>"AUTORIZA"],
    "Autorizadas"=>[ // Para procesar por Compras // sistemas,solicita
        "title"=>"Facturas Autorizadas",
        "classname"=>" bgyellow",
        "status"=>"EXPORTA"],
    "SinFactura"=>[ // Pendientes
        "title"=>"Solicitudes Pagadas sin Factura",
        "classname"=>" bgorange",
        "status"=>"AGREGA"],
    "EnProceso"=>[ // Para procesar por Contabilidad
        "title"=>"Facturas en Proceso de Validación",
        "status"=>"PROCESA"],
    "EnProcConta"=>[ // Para procesar por Contabilidad
        "title"=>"Facturas en Proceso Contable",
        "classname"=>" bgyellow",
        "status"=>"PROCESA"],
    "Procesando"=>[ // En proceso Compras o Contable
        "title"=>"Facturas en Proceso de Validación",
        "status"=>"PROCESO"],
    "ParaPago"=>[ // Para pagar
        "title"=>"Solicitudes a Pagar",
        "status"=>"PAGA"],
    "EnProcPago"=>[ // Para pagar
        "title"=>"Facturas en Proceso de Pago",
        "status"=>"PROCESO"],
    "SinPagar"=>[ // Autorizador
        "title"=>"Solicitudes Autorizadas Sin Pagar",
        "status"=>"PROCESO"],
    "ImpuestoImportacion"=>[ // Pagadas que nunca tendrán factura
        "title"=>"Impuestos e Importaciones Pagadas",
        "classname"=>" bggreen",
        "status"=>"PAGADA"],
    "ConFactura"=>[ // Informativo
        "title"=>"Facturas Pagadas",
        "classname"=>" bggreen",
        "status"=>"PAGADA"],
    "Pagadas"=> [ // Autorizador
        "title"=>"Solicitudes Pagadas",
        "classname"=>" bggreen",
        "status"=>"PAGADA"],
    "RechazadasHoy"=>[ // Informativo Importante
        "title"=>"Solicitudes Rechazadas Hoy",
        "classname"=>" bgred",
        "status"=>"CANCELADA"],
    "RechazadasAntes"=>[ // Informativo
        "title"=>"Solicitudes Rechazadas Dias Anteriores",
        "status"=>"CANCELADA"],
    "Rechazadas"=>[ // Autorizador
        "title"=>"Solicitudes Rechazadas",
        "status"=>"CANCELADA"]
];

global $query;
if ($_esSistemas) {
    $seccionId=["NoAutorizadas","SinFactura","Autorizadas","EnProceso","ParaPago","ImpuestoImportacion","ConFactura","RechazadasHoy","RechazadasAntes"];
    $empresas=null;
} else if ($esAuthPago) {
    $seccionId=["NoAutorizadas", "Autorizadas", "EnProceso", "ParaPago", "Pagadas", "Rechazadas"];
    global $ugObj; if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj=new Usuarios_Grupo(); } //$ugObj->clearOrder(); //$ugObj->addOrder("alias");
    $ugObj->rows_per_page=0;
    $empresas=$ugObj->getIdGroupByNames(getUser(),"Autoriza Pagos","auth");
// } else if ($esRealizaPago) { // definido más arriba
} else if ($esGestionaPago) {
    $seccionId=["SinFactura","EnProceso","NoAutorizadas","Autorizadas","ParaPago","ImpuestoImportacion","ConFactura","Rechazadas"];
    global $ugObj; if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj=new Usuarios_Grupo(); } //$ugObj->clearOrder(); //$ugObj->addOrder("alias");
    $ugObj->rows_per_page=0;
    $empresas=$ugObj->getIdGroupByNames(getUser(),"Gestiona Pagos");
} else if ($esSolPago||$veSolPago) {
    $seccionId=["SinFactura","Autorizadas","RechazadasHoy","NoAutorizadas","EnProcPago","ImpuestoImportacion","ConFactura","RechazadasAntes"];
    global $ugObj; if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj=new Usuarios_Grupo(); } //$ugObj->clearOrder(); //$ugObj->addOrder("alias");
    $ugObj->rows_per_page=0;
    if ($veSolPago) $empresas=$ubObj->getIdGroupByNames(getUser(),"Consulta Solicitudes","vista");
    if (!isset($empresas[0]) && $esSolPago)
        $empresas=$ugObj->getIdGroupByNames(getUser(),"Solicita Pagos","vista");
    if (!isset($empresas[0]) && $_esCompras)
        $empresas=$ugObj->getIdGroupByNames(getUser(),"Compras","vista");
    if (!isset($empresas[0]) && !is_null($empresas))
        $empresas=null;
}
if (isset($empresas)) {
    if ($_esCompras && !isset($empresas[0])) $empresas=null;
    else clog2("Empresas: ".json_encode($empresas));
}
$secciones=[];
foreach ($seccionId as $idx => $key) {
    $secciones[$key]=$todasSecciones[$key];
}
if ($esRealizaPago) {
    $secciones["ParaPago"]["classname"]=" bgyellow";
} else if ($esGestionaPago) {
    $secciones["EnProceso"]["title"]=$todasSecciones["EnProcConta"]["title"];
    $secciones["ParaPago"]["status"]=$todasSecciones["EnProcPago"]["status"];
    $secciones["Autorizadas"]["title"]=$todasSecciones["EnProceso"]["title"];
    $secciones["Autorizadas"]["status"]=$todasSecciones["EnProcPago"]["status"];
    $secciones["NoAutorizadas"]["classname"]=" bgyellow";
} else if ($esAuthPago) {
    $secciones["NoAutorizadas"]["classname"]=" bgyellow";
    $secciones["NoAutorizadas"]["empresas"]="todas"; // en esta seccion no se restringe por empresa
} else if ($esSolPago||$veSolPago) {
    $secciones["Autorizadas"]["classname"]=" bgyellow";
    $secciones["SinFactura"]["classname"]=" bgyellow";
}
global $prvObj; if (!isset($prvObj)) { require_once "clases/Proveedores.php"; $prvObj=new Proveedores(); }
$prvObj->rows_per_page=0;
//$noInvPrvIds=array_column($prvObj->getData("codigo in ('I-999','I-998')",0,"id"), "id");
SolicitudPago::init($empresas,$secciones);
$listaFiltros=SolicitudPago::$listaFiltros;
$numEmpresas=isset($empresas)?(is_array($empresas)?count($empresas):(empty($empresas)?0:1)):0;
if ($numEmpresas==1) unset($listaFiltros["filter04"]);
unset($listaFiltros["filter06"]);unset($listaFiltros["filter07"]);unset($listaFiltros["filter08"]);
//if (!$_esDesarrollo)
    unset($listaFiltros["filter11"],$listaFiltros["filter12"]);
$_SESSION["solLstFltr"]=$listaFiltros;
$maxResults=1500;
$numResults=0;
if (is_array($filtros)) {
    $fSeccion=$filtros["filter10"]??[];
    foreach ($secciones as $key => $data) {
        if (isset($fSeccion[0])&&!in_array($key, $fSeccion)) continue;
        $funcname="getNum{$key}F";
        $fixFiltros=$filtros;
        if ($key==="SinFactura") {
            if (!isset($fixFiltros["filter05"][0])) $fixFiltros["filter05"]=[];
            $fixFiltros["filter05"][]="!I-998";
            $fixFiltros["filter05"][]="!I-999";
        } else if ($key==="ImpuestoImportacion") {
            $hasF05=isset($fixFiltros["filter05"][0]);
            $hasI998=$hasF05&&in_array("I-998", $fixFiltros["filter05"]);
            $hasI999=$hasF05&&in_array("I-999", $fixFiltros["filter05"]);
            if ($hasF05 && !$hasI998 && !$hasI999) continue;
            $fixFiltros["filter05"]=[];
            if (!$hasF05||$hasI998) $fixFiltros["filter05"][]="I-998";
            if (!$hasF05||$hasI999) $fixFiltros["filter05"][]="I-999";
            $funcname="getNumSinFacturaF";
        }
        $num=$solObj->$funcname($fixFiltros,(($data["empresas"]??"")==="todas")?null:$empresas);
        if (!is_null($num)) {
            if (is_numeric($num))
                $numResults+= (+$num);
            else doclog("ConfigListaSolP countResults","solpago",["key"=>$key,"funcname"=>$funcname,"num"=>$num]);
        }
    }
    doclog("ConfigListaSolP secciones","solpago",["filtros"=>array_keys($filtros),"seccionesKeys"=>array_keys($secciones),"numResults"=>$numResults]);
    if ($numResults<=$maxResults) foreach ($secciones as $key => $data) {
        if (isset($fSeccion[0])&&!in_array($key, $fSeccion)) continue;
        $funcname="get{$key}F";
        $fixFiltros=$filtros;
        if ($key==="SinFactura") {
            if (!isset($fixFiltros["filter05"][0])) $fixFiltros["filter05"]=[];
            $fixFiltros["filter05"][]="!I-998";
            $fixFiltros["filter05"][]="!I-999";
        } else if ($key==="ImpuestoImportacion") {
            $hasF05=isset($fixFiltros["filter05"][0]);
            $hasI998=$hasF05&&in_array("I-998", $fixFiltros["filter05"]);
            $hasI999=$hasF05&&in_array("I-999", $fixFiltros["filter05"]);
            if ($hasF05 && !$hasI998 && !$hasI999) continue;
            $fixFiltros["filter05"]=[];
            if (!$hasF05||$hasI998) $fixFiltros["filter05"][]="I-998";
            if (!$hasF05||$hasI999) $fixFiltros["filter05"][]="I-999";
            $funcname="getSinFacturaF";
        }
        $showList[$key]=$solObj->$funcname($fixFiltros,(($data["empresas"]??"")==="todas")?null:$empresas);
        $qryList[$key]=$query."|".count($showList[$key]);
        if (isset($data["classname"]))
            $showClass[$key]=$data["classname"];
        $statusList[$key]=$data["status"];
    }
} //else clog2("Tipo=".gettype($filtros));
$titles=array_combine($seccionId, array_column($secciones, "title"));
//$titles=[""=>"TODAS","Autorizadas"=>"Facturas Autorizadas","RechazadasHoy"=>"Solicitudes Rechazadas Hoy","NoAutorizadas"=>"Solicitudes sin Autorizar","SinPagar"=>"Solicitudes Autorizadas sin Pago","SinFactura"=>"Solicitudes Pagadas sin Factura","EnProceso"=>"Facturas en Proceso de Validación","EnProcConta"=>"Facturas en Proceso Contable","EnProcPago"=>"Facturas en Proceso de Pago","ParaPago"=>"Solicitudes a Pagar","ConFactura"=>"Facturas Pagadas","Pagadas"=>"Solicitudes Pagadas","Rechazadas"=>"Solicitudes Rechazadas","RechazadasAntes"=>"Solicitudes Rechazadas Dias Anteriores"];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //
function updateFilterDate(&$dateItem,$oldDay,$curDay) {
    if (isset($dateItem[0])&&$dateItem[0]===$oldDay) {
        $dateItem[0]=$curDay;
    }
    if (isset($dateItem[1])&&$dateItem[1]===$oldDay) {
        $dateItem[1]=$curDay;
    }
}
function getLink2Doc($href,$ext,$ext2="",$breakId="") {
    if (!isset($href[0])||!isset($ext[0])) return "";
    if (isset($breakId[0])) {
        $linkAttribs=" onclick=\"rompeSello($breakId);\"";
    } else $linkAttribs="";
    if ($href==="consultas/Contrarrecibos" && substr($ext, 0, 10)==="php?folio=") {
        $ampIdx=strpos($ext, "&", 10);
        if ($ampIdx===false || $ampIdx<0) $ampIdx=0;
        $linkAttribs.=" title=\"CONTRA-RECIBO ".substr($ext, 10, $ampIdx)."\"";
        if (isset($ext2[0])) {
            $linkAttribs.="onclick=\"rompeSelloCR(this,".($ampIdx-strlen($ext)).");\"";
        }
        $ext2="crDoc32".$ext2;
        $ext0="php";
    } else {
        $ext2=$ext."200".$ext2;
    }
    $rootPath="";
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $rootPath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $docPath=$href.".".$ext;
    $docPath0=$href.".".($ext0??$ext);
    if(file_exists($rootPath.$docPath0))
        return "<A href=\"{$docPath}\" target=\"archivo\"{$linkAttribs}><IMG src=\"imagenes/icons/{$ext2}.png\" width=\"20\" height=\"20\" /></A>";
    else if (in_array($ext, ["xml","pdf"])) {
        doclog("El archivo no existe","solpago",["filepath"=>$docPath0,"href"=>$href,"ext"=>$ext,"ext2"=>$ext2,"breakId"=>$breakId]);
        return "<IMG src=\"imagenes/icons/{$ext}512Missing.png\" width=\"20\" height=\"20\" title=\"{$docPath}\" />";
    } else return "<IMG src=\"\" width=\"20\" height=\"20\" title:\"{$docPath}\" />";
    // CTR:
    // <a href="consultas/Contrarrecibos.php?folio=APSA-10270" target="archivo" title="CONTRA-RECIBO APSA-10270"><img src="imagenes/icons/crDoc32.png" width="32" height="32"></a>
}
function getStatusName($status, $proceso, $key, $solId, $solFolio, $usos=null) {
    global $esSolPago, $veSolPago, $esAuthPago, $esRealizaPago, $esGestionaPago, $_esSistemas, $esGestor, $statusList;
    $suffix="";//" en $key";
    $prefix="STATUS='$status', PROCESO='$proceso', KEY='$key', SOLID='$solId', SOLFOLIO='$solFolio'";
    $extraParams="class=\"solBtn\"";
    //$kex=preg_replace("/\s+/", "", $key);
    //$extraParams=" key=\"$kex\" solid=\"$solId\" status=\"$status\" proceso=\"$proceso\"";
    if ($usos!==null) {
        if (is_array($usos)) {
            $prefix.=", USOS=".json_encode($usos);
            //foreach ($usos as $uky => $uvl) {
            //    $uvx=preg_replace("/\s+/", "", $uvl);
            //    $extraParams.=" uso{$uky}=\"$uvx\"";
            //}
        } else {
            $prefix.=", USOS='$usos'";
            //$usox=str_replace(['\'','"','='],'',preg_replace("/\s/", "", $usos));
            //$extraParams.=" uso=\"$usox\"";
        }
    } else {
        $usos=["auth"=>true,"rech"=>true];
        //$extraParams.=" usoauth=\"true\" usorech=\"true\"";
    }
    if (isset($statusList[$key])) {
        $prefix = ""; // "<!-- $prefix, STATKEY='".$statusList[$key]."' -->"; // 
        //$extraParams.=" statkey=\"{$statusList[$key]}\"";
        //doclog("ConfigListaSolP getStatusName","solpago",["status"=>$status, "proceso"=>$proceso, "key"=>$key, "solId"=>$solId, "solFolio"=>$solFolio, "usos"=>$usos, "statKey"=>$statusList[$key]]);
        //echo console.log("TEST getStatusName key=");
        switch($statusList[$key]) {
            case "AUTORIZA":
                if ($esGestor) return $prefix."No Autorizada ".getActionHtml("REENVIAR",$solId,$solFolio,$extraParams);
                if ($_esSistemas) $suffix=getActionHtml("REENVIAR",$solId,$solFolio,$extraParams).getActionHtml("CANCELAR",$solId,$solFolio,$extraParams);
                if ($esAuthPago||$_esSistemas) {
                    $retval=($usos["auth"]?getActionHtml("AUTORIZAR", $solId,$solFolio,$extraParams):"").($usos["rech"]?getActionHtml("RECHAZAR", $solId,$solFolio,$extraParams):"");
                    if (!isset($retval[0])) $retval=$key;
                    return $prefix.$retval.($suffix??"");
                }
                if ($esSolPago||$esGestionaPago||$veSolPago) return $prefix."ESPERANDO AUTORIZACIÓN";
                return $prefix.$statusList[$key];
            case "EXPORTA":
                $suffix=$_esSistemas?getActionHtml("CANCELAR",$solId,$solFolio,$extraParams):"";
                return $prefix.getActionHtml("EXPORTAR",$solId,$solFolio,$extraParams).getActionHtml("PROCESAR",$solId,$solFolio,$extraParams).$suffix;
            case "PROCESA":
                $suffix=$_esSistemas?getActionHtml("CANCELAR",$solId,$solFolio,$extraParams):"";
                return $prefix.getActionHtml("PASAR A PAGO",$solId,$solFolio,$extraParams).$suffix;
            case "PAGA":
                $suffix=$_esSistemas?getActionHtml("CANCELAR",$solId,$solFolio,$extraParams):"";
                return $prefix.getActionHtml("ANEXAR COMPROBANTE",$solId,$solFolio,$extraParams).getActionHtml("MARCAR PAGADA",$solId,$solFolio,$extraParams).$suffix;
            case "AGREGA":
                $oldXP=$extraParams;
                $extraParams="class=\"solBtn%20pad1\"";
                $suffix=getActionHtml("ANEXAR FACTURA",$solId,$solFolio,$extraParams);
                $extraParams=$oldXP;
                $suffix.=$_esSistemas?getActionHtml("CANCELAR",$solId,$solFolio,$extraParams):"";
                return $prefix.$suffix;
            case "PAGADA":
                $suffix=$_esSistemas?getActionHtml("CANCELAR",$solId,$solFolio,$extraParams):"";
                return $prefix.getActionHtml("PAGADA",$solId,$solFolio).$suffix; // 
            case "CANCELADA":
                if ($esAuthPago) {
                    $retval=($usos["auth"]?getActionHtml("AUTORIZAR",$solId,$solFolio,$extraParams):"");
                    if (isset($retval[0])) return $prefix.$retval;
                } else if ($_esSistemas) {
                    // verificar si la cancelación fue por sistemas o por sat para no mostrar botón
                    $retval=($usos["auth"]?getActionHtml("RECUPERAR",$solId,$solFolio,$extraParams):"");
                    if (isset($retval[0])) return $prefix.$retval;
                }
                return $prefix."CANCELADA";
            case "PENDIENTE": return $prefix."EN ESPERA";
            case "PROCESO":
                $suffix=$_esSistemas?getActionHtml("CANCELAR",$solId, $solFolio,$extraParams):"";
                if ($proceso==0) {
                    if ($_esSistemas/*||$esGestor*/) $suffix=getActionHtml("REENVIAR",$solId, $solFolio,$extraParams).$suffix;
                    if ($esAuthPago||$_esSistemas) {
                        $retval=($usos["rech"]?getActionHtml("RECHAZAR", $solId, $solFolio,$extraParams):"");
                        if (!isset($retval[0])) $retval=$key;
                        return $prefix.$retval.$suffix;
                    }
                    if ($esRealizaPago) return $prefix."COMPRAS".$suffix;
                    if ($status==2||$status==3) return $prefix."AUTORIZADA".$suffix;
                    if ($status>=4&&$status<8) return $prefix."ACEPTADA".$suffix;
                    if ($status>=8&&$status<16) return $prefix."CONTRA RECIBO".$suffix;
                    if ($status>=16&&$status<32) return $prefix."EXPORTADA".$suffix;
                    if ($status>=32&&$status<64) return $prefix."RESPALDADA".$suffix;
                } else if ($proceso==1) {
                    if ($esRealizaPago) return $prefix."CONTABILIDAD".$suffix;
                    return $prefix."CONTABLE".$suffix;
                } else if ($proceso<4) return $prefix."PARA PAGO".$suffix;
                else return $prefix.getActionHtml("PAGADO",$solId,$solFolio).$suffix;
            default: return $prefix.$statusList[$key];
        }
    } else return /*"<!-- $prefix, NO STATKEY -->".*/$key;
}
function getActionHtml($action, $solId, $solFolio, $extraParams="") {
    switch($action) {
        case "PAGADA": $child="<IMG src=\"imagenes/icons/crPaid32.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        case "AUTORIZAR": $child="<IMG src=\"imagenes/icons/statusRight.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        case "RECHAZAR": $child="<IMG src=\"imagenes/icons/statusWrong.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        case "REENVIAR": $sssRsndCnt=+($_SESSION["resendCounter{$solId}"]??"0")+0;
            $rsndtms="$sssRsndCnt ve".($sssRsndCnt==1?"z":"ces");
            $child="<IMG src=\"imagenes/icons/resendEmail.png\" width=\"20\" height=\"20\" onload=\"const prn=this.parentNode;prn.id='rsndBlk$solId';cladd(prn,'relative');".($sssRsndCnt>0?"prn.setAttribute('rsndTms','$rsndtms');const bdg=ebyid('rsndBdg{$solId}');if(bdg){bdg.title='Reenviado $rsndtms';}else{cladd(prn,'relative');prn.appendChild(ecrea({eName:'DIV',id:'rsndBdg{$solId}',className:'abs_se badge',title:'Reenviado {$rsndtms}',eText:'+'}));}":"if(prn.hasAttribute('rsndTms')){prn.appendChild(ecrea({eName:'DIV',id:'rsndBdg$solId',className:'abs_se badge',title:'Reenviado '+prn.getAttribute('rsndTms'),text:'+'}));}")."\">"; $title=$action; break; // ToDo: Encimar microbotón/imagen(lapiz) al presionarlo permite elegir nuevos autorizadores, en la lista de autorizadores por default estarán palomeados los autorizadores que actualmente se indican en campo solicitudpago.authList. Los ids de los autorizadores elegidos se guardan en campo solicitudpago.authList. Si en Tokens no existen los registros con refId de la solicitud, usrId de cada autorizador elegido y modulos autorizaPago y rechazaPago, insertar nuevos tokens con esos datos. Después de estos cambios en la base de datos se ejecuta el código para reenviar correo.
//        $sssRsndCnt=+($_SESSION["resendCounter{$solId}"]??"0")+1;
//        $_SESSION["resendCounter{$solId}"]="$sssRsndCnt";
//        $rsndtms="$sssRsndCnt ve".($sssRsndCnt==1?"z":"ces");
//        echo "<p class=\"boldValue padhtt reloadOnClose\">Solicitud de Autorización $solFolio reenviada a $to[name]<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const blk=ebyid('rsndBlk{$solId}');if(blk){blk.setAttribute('rsndTms','$rsndtms');const bdg=ebyid('rsndBdg{$solId}');if(bdg){bdg.title='Reenviado $rsndtms';}else{cladd(blk,'relative');blk.appendChild(ecrea({eName:'DIV',id:'rsndBdg{$solId}',className:'abs_se badge',title:'Reenviado {$rsndtms}',eText:'+'}));}}else console.log('No pudo encontrarse rsndBlk{$solId}');ekil(this);\"></p>";

        // <div class='abs_se badge' title='Reenviado $rsndCnt $rsndCap'>+</div>
        case "RECUPERAR":/* "DESRECHAZAR": /*case "DESAUTORIZAR": */$child="<IMG src=\"imagenes/icons/prevPageE20.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        // Las facturas rechazadas que se pueden autorizar también deben poderse recuperar sin autorizar..
        // ToDo: Si está rechazado a solicitudpago.status restarle 128 y en tokens.status cambiarlo a activo para modulo in (autorizaPago o rechazaPago) (necesario para reenviar correo) y en facturas.statusn restarle 128 y ajustar facturas.status al que corresponda
        // ToDo: Por lo pronto sólo implementar desrechazar. Si está autorizado a solicitudpago.status restarle 2 y en tokens.status cambiarlo a activo para modulo in (autorizaPago o rechazaPago) - falta definir que se hace en factura pues al autorizar se genera el contra recibo. Debería eliminarse por completo ese contra recibo.
        case "EXPORTAR": $child="<IMG src=\"imagenes/icons/carga8.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        case "PROCESAR": case "PASAR A PAGO": $child="<IMG src=\"imagenes/icons/nextPageE20.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        case "ANEXAR COMPROBANTE": $child="<IMG src=\"imagenes/icons/invChk200.png\" width=\"20\" height=\"20\" style=\"filter:grayscale(1) brightness(0.8) contrast(2.5)\">"; $title=$action; break;
        case "MARCAR PAGADA": $child="<IMG src=\"imagenes/icons/invoiceIcon.png\" width=\"20\" height=\"20\" style=\"filter:grayscale(1) brightness(0.8) contrast(2.5)\">"; $title=$action; break;
        case "ANEXAR FACTURA": 
        case "AGREGAR FACTURA": $child="<IMG src=\"imagenes/icons/cfdi2.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        case "CANCELAR": $child="<IMG src=\"imagenes/icons/deleteIcon20.png\" width=\"20\" height=\"20\">"; $title=$action; break;
        default: $child=$action;
    }
    $parParams="";
    if (isset($solFolio[0])) $parParams.="folio:'$solFolio'";
    if (isset($extraParams[0])) {
        $listParams=explode(" ", trim($extraParams));
        $extraParams=str_replace("%20", " ", $extraParams);
        foreach ($listParams as $idx => $expr) {
            list($key,$value)=explode("=",str_replace("%20"," ",str_replace('"','',$expr)));
            if ($key==="class") continue; //$key="className";
            if (isset($parParams[0])) $parParams.=",";
            $parParams.="{$key}:'{$value}'";
        }
    }
    if (isset($parParams[0])) $parParams=",{{$parParams}}";
    if (isset($title[0])) $extraParams.=" title=\"".$title."\"";
    return "<button type=\"button\" class=\"bgbtnIO\" onclick=\"doPaymAction('$action','$solId'{$parParams});\" onmousedown=\"cladd(this,'pressed');\" onmouseup=\"clrem(this,'pressed');\" onmouseleave=\"clrem(this,'pressed');\" $extraParams>$child</button>";
}

if (getMetaLogLevel()<=getMetaLogLevel("meta_logl_errors")) {
    //echo "\n<!-- MetaLogLevel=".getMetaLogLevel()."(".getMetaLogLevel(getMetaLogLevel()).") < ".getMetaLogLevel("meta_logl_errors")."(meta_logl_errors) -->";
    // $_esAdministrador, $_esSistemas, $_esSistemasX, $_esDesarrollo, $_esPruebas, $_esCompras, $_esComprasB, $_esProveedor;
    echo "\n<!-- PERFILES: ".($_esAdministrador?"1":"0").($_esSistemas?"1":"0").($_esSistemasX?"1":"0").($_esDesarrollo?"1":"0").($_esPruebas?"1":"0").($_esCompras?"1":"0").($_esComprasB?"1":"0").($_esProveedor?"1":"0")." ";
    $hsPrf=false;
    if ($_esDesarrollo) { echo "DESARROLLO"; $hsPrf=true; }
    else if ($_esAdministrador) { echo "ADMIN"; $hsPrf=true; }
    else if ($_esSistemas) { echo "SISTEMAS"; $hsPrf=true; }

    if ($esSolPago) { echo ($hsPrf?",":"")."SOLICITA"; $hsPrf=true; }
    if ($esAuthPago) { echo ($hsPrf?",":"")."AUTORIZA"; $hsPrf=true; }
    if ($esRealizaPago) { echo ($hsPrf?",":"")."PAGA"; $hsPrf=true; }
    if ($esGestionaPago) { echo ($hsPrf?",":"")."GESTIONA"; $hsPrf=true; }
    if ($veSolPago) { echo ($hsPrf?",":"")."CONSULTA"; $hsPrf=true; }
    echo " -->\n";
    //echo "<!-- GET:\n".arr2str($_GET)."\n -->\n";
    //echo "<!-- POST:\n".arr2str($_POST)."\n -->\n";
    //echo "<!-- FILES:\n".arr2str($_FILES)."\n -->\n";
    //echo "<!-- SESSION:\n".arr2str($_SESSION)."\n -->\n";
    echo "<!-- COOKIES:\n".arr2str($_COOKIE)."\n -->\n";
    foreach ($qryList as $key => $value) {
        $whrIdx=strpos($value, " WHERE ");
        if ($whrIdx>=0) $where=substr($value, $whrIdx+7);
        else $where=$value;
        $pppIdx=strpos($where, "|");
        if ($pppIdx>=0) {
            $num=substr($where, $pppIdx+1);
            $where=substr($where,0,$pppIdx);
        } else {
            $num=-1;
        }
        echo "<!-- QUERY $key($num) = $where -->\n";
    }
//        echo "\n<!-- ";
//        echo "\n    QUERYS:\n".arr2str($qryList);
//        echo "\n-->";
//        echo "\n<!-- ";
//        echo "\n    LOGS:".$solObj->log;
//        echo " -->\n";
} else if (getMetaLogLevel()<=getMetaLogLevel("meta_logl_files")) {
    echo "\n<!-- POST:\n".arr2str($_POST)."\n -->\n";
    echo "<!-- COOKIES:\n".arr2str($_COOKIE)."\n -->\n";
}
clog1seq(-1);
clog2end("configuracion.listasolp");
