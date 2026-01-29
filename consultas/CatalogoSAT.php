<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/catalogoSAT.php";
if (isValueService()) {
    getValueService();
} else if (isHeaderService()) {
    getHeaderService();
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isHeaderService() {
    return isset($_REQUEST["action"]) && $_REQUEST["action"]==="getHeaders" && isset($_REQUEST["catalogo"]);
}
function getHeaderService() {
    $catalogo = $_REQUEST["catalogo"];
    $headArr = CatalogoSAT::getHeaders($catalogo);
    echo implode("|",$headArr);
    return true;
}
function isValueService() {
    return isset($_REQUEST["llave"]) && isset($_REQUEST["catalogo"]);
}
function getValueService($excludeFullDataInAdvanced=false, $extraParameters=false) {
    if (isset($_REQUEST["llave"]) && (empty($_REQUEST["llave"]) || (is_array($_REQUEST["llave"])) || isset($_REQUEST[$_REQUEST["llave"]])) && isset($_REQUEST["solicita"])) {
        $catalogo = $_REQUEST["catalogo"];
        if (isset($_REQUEST["extraWhere"])) {
            $extraWhere = $_REQUEST["extraWhere"];
            if (is_string($extraWhere) && strpos($extraWhere, ","))
                $extraWhere=explode(",",$extraWhere);
            if (is_array($extraWhere) && count($extraWhere)%2==0) {
                $whereStr = "";
                $arrlen = count($extraWhere);
                for($x=0; $x<$arrlen; $x+=2) {
                    if (strlen($whereStr)>0) $whereStr.=" AND ";
                    $whereStr.=$extraWhere[$x]."='".$extraWhere[$x+1]."'";
                }
                $extraWhere = $whereStr;
            }
        } else $extraWhere=false;
        
        $extraSql = false;
        $fullData = false;
        $llave = $_REQUEST["llave"];
        if (empty($llave)) $valllave=false;
        else {
            if (is_array($llave)) {
                if (empty($extraWhere)) $extraWhere="";
                foreach ($llave as $k) {
                    if ($k != $llave[0] && !empty($_REQUEST[$k])) {
                        if ($extraWhere!=="") $extraWhere .= " AND ";
                        $extraWhere .= $k."='".$_REQUEST[$k]."'";
                    }
                }
                $llave = $llave[0];
            }
            $valllave = $_REQUEST[$llave];
        }
        $solicita = $_REQUEST["solicita"];
        $selems = explode(",",$solicita);
        $helems = CatalogoSAT::getHeaders($catalogo);
        $fix=false;
        foreach($selems as $s) {
            if (!in_array($s, $helems)) {
                $solicita = implode("''",explode($s,$solicita));
            }
        }

        if (isset($_REQUEST["modo"])) $modo = $_REQUEST["modo"];
        if (isset($_REQUEST["order"])) $order = $_REQUEST["order"];

        if (isset($modo) && $modo==="avanzado") { // keyup|keydown con cambio a resultado anterior o siguiente con flechas arriba o abajo
            $valllave .= "%";
            $extraSql = "ORDER BY ".$llave;

            if (!$excludeFullDataInAdvanced)
                $fullData = true;
        }
        if (isset($order)) {
            $extraSql = "ORDER BY ".$order;
        }
        if (isset($_REQUEST["fulldata"])) {
            $fullData = true;
        }
        if (!empty($extraParameters)) {
            if (!empty($extraParameters["extraWhere"])) {
                if (!empty($extraWhere)) $extraWhere.=" AND ";
                $extraWhere .= $extraParameters["extraWhere"];
            }
        }
        $returnValue = CatalogoSAT::getValue($catalogo, $llave, $valllave, $solicita, $extraWhere, $extraSql, $fullData);
        echo $returnValue;
        return true;
    }
    return false;
}
