<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Bancos.php";

$bnkObj = new Bancos();
if (isValueService()) getValueService($bnkObj);
else if (isTestService()) getTestService($bnkObj);
else if (isCatalogService()) getCatalogService($bnkObj);
else if (isActionService()) doActionService();
else if (isGetActionService()) doGetActionService();

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isGetActionService() {
    return isset($_GET["action"]);
}
function doGetActionService() {
    global $bnkObj;
    switch($_GET["action"]) {
        case "layout":
            if (isset($_REQUEST["exacto"][0])) $exacto=explode(",", $_REQUEST["exacto"]);
            $pars=[];
            foreach($_REQUEST["param"] as $ky)
                if(isset($_REQUEST[$ky])) $pars[$ky]=$_REQUEST[$ky];
            $where = DBi::params2Where($pars,$exacto,true);
            if (isset($pars["clave"])) $order="(clave+0)";
            else if (isset($pars["razonSocial"])) $order="razonSocial";
            else if (isset($pars["rfc"])) $order="rfc";
            if (!isset($order[0])) $order="(clave+0)";
            $bnkObj->addOrder($order);
            global $query;
            $bnkData = $bnkObj->getData($where);

            $suffix=date("YmdHis");
            header("Content-Type: text/plain");
            header("Content-Disposition: inline; filename:\"bancos{$suffix}.txt\"");
            $text = "CLAVE\tALIAS\tRFC\tCUENTA";
            foreach ($bnkData as $idx => $bnkRow) {
                $text.="\n$bnkRow[clave]\t$bnkRow[alias]\t$bnkRow[rfc]\t$bnkRow[cuenta]";
            }
            header("Content-Length: ".strlen($text));
            header("Expires: Fri, 01 Jan 2010 05:00:00 GMT"); // TODO: generate today date
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            //readfile($file);
            echo $text;
            break;
    }
}
function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    global $bnkObj;
    sessionInit();
    if (!hasUser()) {
        echo "REFRESH";
        return;
    }
    global $query;
    $queries=[];
    switch($_POST["action"]) {
        case "keyAccList": // toDo: regresar lista de bancos por clave y cuenta
            break;
        case "aliAccList": // toDo: regresar lista de bancos por alias y cuenta
            break;
        default: echo "ERROR:Petición inválida ($_POST[action])";
    }
}
