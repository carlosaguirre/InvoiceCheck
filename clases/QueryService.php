<?php
function isCatalogService() {
    return isset($_POST["catalogo_admin"]);
}
function getCatalogService($dbobj) {
    switch($_POST["catalogo_admin"]) {
        case "page": getPaginateCatalogService($dbobj); break;
        case "commit":
            require_once "configuracion/inicializacion.php";
            foreach($_POST["modifiedData"] as $classname=>$classdata) {
                require_once "clases/$classname.php";
                $dbobj = new $classname();
                foreach($classdata as $fieldId=>$fielddata) {
                    $fielddata["id"]=$fieldId;

                    if($dbobj->saveRecord($fielddata))
                        echo "{$classname}[id=$fieldId] guardado satisfactoriamente\n";
                    else
                        echo "Error en {$classname}[id=$fieldId]: ".DBi::$error."<br>\n";
                }
            }
            require_once "configuracion/finalizacion.php";
            break;
//            default:
//                echo arr2List($_POST);
    }
}
function getPaginateCatalogService($dbobj) {
    $clsnm = get_class($dbobj);

    $pageVal = $_POST["lastcattablepg"];
    if (isset($pageVal))
        $dbobj->pageno = +$pageVal;

    $sortColVal = $_POST["cattbsortcol"];
    $sortModVal = $_POST["cattbsortmod"];
    if (isset($sortColVal)) {
        if (isset($sortModVal)) $dbobj->addOrder($sortColVal, $sortModVal);
        else $dbobj->addOrder($sortColVal);
    }
    $fieldlist = $dbobj->fieldlist;
    $data = $dbobj->getData();
    echo "                    <input type='hidden' name='currPgVal_$clsnm' id='currPgVal_$clsnm' value='$dbobj->pageno'>\n";
    echo "                    <input type='hidden' name='lastPgVal_$clsnm' id='lastPgVal_$clsnm' value='$dbobj->lastpage'>\n";
    if (isset($sortColVal))
        echo "                    <input type='hidden' name='sortColVal_$clsnm' id='sortColVal_$clsnm' value='$sortColVal'>\n";
    if (isset($sortModVal))
        echo "                    <input type='hidden' name='sortModVal_$clsnm' id='sortModVal_$clsnm' value='$sortModVal'>\n";
    foreach($data as $row) {
        echo "                    <tr id='row{$clsnm}_$row[id]'>\n";
        foreach($fieldlist as $field) {
            if (!is_array($field) && !isset($fieldlist[$field]["pkey"]) && !isset($fieldlist[$field]["auto"])) {
                echo "                      <td id='cell{$clsnm}_$row[id]_{$field}' class='nowrap' ondblclick='changeToEditable(this, \"$clsnm\", \"$row[id]\", \"$field\");'>";
                echo $row[$field]."</td>\n";
            }
        }
        echo "                    </tr>\n";
    }
    echo "<img src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' onload='console.log(\"LOADED!\");this.parentNode.removeChild(this);'>";
}
function isValueService() {
    return isset($_REQUEST["llave"]);
}
function getValueService($dbobj, $excludeFullDataInAdvanced=false, $extraParameters=false) {
    doclog("INI GETVALUE ".get_class($dbobj),"queryservice",["parameters"=>$_REQUEST, "extra"=>$extraParameters]);
    //echo "<!-- VALUE SERVICE -->";
    $isJSON=!empty(getCIKeyVal($_REQUEST,"getJSON"));
    if (isset($_REQUEST["llave"]) && (empty($_REQUEST["llave"]) || (is_array($_REQUEST["llave"])) || isset($_REQUEST[$_REQUEST["llave"]])) && isset($_REQUEST["solicita"]) && !empty($dbobj)) {

        $extraTable = $_REQUEST['extraTable']??"";
        $extraWhere = $_REQUEST["extraWhere"]??"";
        if (is_string($extraWhere) && strpos($extraWhere, ","))
            $extraWhere=explode(",",$extraWhere);
        if (is_array($extraWhere)) {
            $whereStr = "";
            $separator = "=";
            $isName = true;
            foreach ($extraWhere as $idx => $value) {
                if ($isName&&preg_match('/ |=|<|>/i', $value)) 
                    $whereStr.=$value;
                else if ($isName) {
                    $whereStr.=$value;
                    $isName = false;
                } else {
                    $whereStr.=$separator."'".$value."'";
                    $isName = true;
                }
            }
            if (!$isName) $whereStr.=" is null";
            $extraWhere=$whereStr;
        }

        $extraSql = false;
        $fullData = false;
        $llave = $_REQUEST["llave"];
        $llaves="";
        if (empty($llave)) $valllave=false;
        else {
            if (is_array($llave)) {
                if (!isset($extraWhere)) $extraWhere="";
                foreach ($llave as $k) {
                    if (isset($llaves[0])) $llaves.=",";
                    $llaves.=$k;
                    $k2=str_replace(".", "_", $k);
                    if ($k != $llave[0] && !empty($_REQUEST[$k2])) {
                        if (isset($extraWhere[0])) $extraWhere .= " AND ";
                        $extraWhere .= $k."='".$_REQUEST[$k2]."'";
                    }
                }
                $llave = $llave[0];
            } else $llaves = $llave;
            $k2=str_replace(".", "_", $llave);
            $valllave = $_REQUEST[$k2];
        }
        $solicita = $_REQUEST["solicita"];

        if (isset($_REQUEST["status"])) $status = $_REQUEST["status"];
        if (isset($_REQUEST["modo"])) $modo = $_REQUEST["modo"];
        if (isset($_REQUEST["order"])) $order = $_REQUEST["order"];
        if (isset($_REQUEST["log"])) $hasLog = $_REQUEST["log"];

        if (isset($modo) && $modo==="avanzado") { // keyup|keydown con cambio a resultado anterior o siguiente con flechas arriba o abajo
            $valllave .= "%";
            $extraSql = "ORDER BY $llaves";

            if (!$excludeFullDataInAdvanced)
                $fullData = true;
        }
        if (isset($status) && $status==="saliendo") {
            if (!empty($extraWhere)) $extraWhere .= " AND ";
            $extraWhere .= "status='activo' AND fechaSalida is NULL";
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
        global $query;
        if ($isJSON) {
            $dbdata = $dbobj->getData(rtrim($dbobj->getWhereCondition($llave, $valllave).$extraWhere, " AND "), 0, $solicita);
            $result=[];
            if (isset($dbdata[0])) {
                $result["result"]="success";
                $result["numrows"]=$dbobj->numrows;
                if (isset($dbdata[1])) {
                    $result["message"]="Se encontraron $dbobj->numrows registros";
                    $result["data"]=$dbdata;
                } else {
                    $dbdata=$dbdata[0];
                    $result["message"]="Se encontro un registro";
                    if (isset($solicita) && $solicita!=="*") foreach ($dbdata as $key => $value) $result[$key]=$value;
                    else $result["data"]=$dbdata;
                }
            } else if (empty(DBi::$errno)) {
                $result["result"]="empty";
                $result["message"]="No se encontraron coincidencias";
            } else {
                $result["result"]="error";
                $result["message"]="No se pudieron obtener datos";
                $result["errno"]=DBi::$errno;
                $result["error"]=DBi::$error;
                $result["errors"]=DBi::$errors;
                $result["oerrors"]=$dbObj->errors;
            }
            $result["query"]=$query;
            if (isset($hasLog) && !empty($dbobj->log)) $result["log"]=$dbobj->log;
            echo json_encode($result);
        } else {
            $returnValue = $dbobj->getValue($llave, $valllave, $solicita, $extraWhere, $extraSql, $fullData, $extraTable);
            doclog("END GETVALUE ".get_class($dbobj),"queryservice",["query"=>$query,"value"=>$returnValue]);
            echo $returnValue;
            if (isset($hasLog) && !empty($dbobj->log)) {
                if (strlen($returnValue)>0) echo "|";
                clog2($dbobj->log);
            }
        }
        return true;
    } else if ($isJSON) echo json_encode([
        "result"=>"error",
        "message"=>"Datos incompletos",
        "isllave"=>isset($_REQUEST["llave"])?"SI":"NO",
        "emptyllave"=>empty($_REQUEST["llave"])?"SI":"NO",
        "isarrllave"=>is_array($_REQUEST["llave"])?"SI":"NO",
        "isllavevalue"=>isset($_REQUEST[$_REQUEST["llave"]])?"SI":"NO",
        "issolicita"=>isset($_REQUEST["solicita"])?"SI":"NO",
        "hasdbobj"=>(!empty($dbobj))?"SI":"NO"]+$_REQUEST);
/*
    isset($_REQUEST["llave"]) && 
    (   empty($_REQUEST["llave"]) || 
        (is_array($_REQUEST["llave"])) || 
        isset($_REQUEST[$_REQUEST["llave"]])) && 
    isset($_REQUEST["solicita"]) && 
    !empty($dbobj)
*/
    return false;
}
function getFolioService($dbobj) {
    if (isset($_GET["folio"]) && $_GET["folio"] == "siguiente" && isset($_GET["bodega"]) && isset($_GET["ciclo"]) && !empty($dbobj)) {
        echo $dbobj->getSiguienteFolio($_GET["bodega"], $_GET["ciclo"]);
    }
}
function isTestService() {
    return isset($_GET["test"]);
}
function getTestService($dbobj) {
    echo "Test Service: ".get_class($dbobj);
}
