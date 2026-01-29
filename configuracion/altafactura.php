<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaAlta)) $consultaAlta = consultaValida("Alta");
if (!$_esSistemas&&!$consultaAlta) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$deshabilitarAlta=false;
if ($deshabilitarAlta) {
    $errorMessage="<P class='noticia margin20 centered'>El Servicio de Alta de Facturas se encuentra temporalmente deshabilitado.<br>Se mandar&aacute; aviso cuando se habilite nuevamente.</P>";
}
clog2ini("configuracion.altafactura");
clog1seq(1);
require_once "configuracion/cfdi.php";
$tracelog = "";
if (isset($_POST["debugField"])) clog2("DEBUG: ".$_POST["debugField"]);

if (isset($_POST["submitxml"])||($_esDesarrollo&&isset($_GET["submitxml"]))) {
    include "configuracion/altafactura01_submitxml.php";
} else if (isset($_POST["insertxml"])||($_esDesarrollo&&isset($_GET["insertxml"]))) {
    $resultMessage="";
    include "configuracion/altafactura02_insertxml.php";
} else {
    $resultMessage="";
    require_once "clases/CFDI.php";
    require_once "clases/Proveedores.php";
    if (!isset($prvObj)) $prvObj = new Proveedores();
    if (!$deshabilitarAlta && $_esProveedor && !Proveedores::esCorporativo($username) && (!isset(CFDI::PRV_MONTHLIMIT_EXCEPTION[0]) || !in_array($username, CFDI::PRV_MONTHLIMIT_EXCEPTION))) {
        $today=new DateTime();
        $lastWorkingDay=getLastInvoicingDay();
        if (!$lastWorkingDay) $errorMessage="<P class='noticia margin20 centered'>El Servicio de Alta de Facturas y Pagos se encuentra deshabilitado</P>";
        else {
            $lastWorkingDateTime=new DateTime($lastWorkingDay." 16:00:00");
            $esHoyEsHoy=($today->format('Y-m-d')===$lastWorkingDay);
            if ($today>$lastWorkingDateTime) {
                //$deshabilitarAlta=true;
                $errorMessage="<P class='noticia margin20 centered'>El Servicio de Alta de Facturas se encuentra deshabilitado".($esHoyEsHoy?" el día de hoy desde las 16 hrs":"")." por cierre de mes.<br>Le solicitamos refacturar para el próximo mes.</P>";
            } else if ($esHoyEsHoy) {
                if (mt_rand(1,10)<2) $resultMessage="<P class='noticia margin20 centered'>Les recordamos que por cierre de mes el día de hoy a las 16 hrs, se deshabilitará".($_esProveedor?"":" a proveedores")." el Servicio de Alta de Facturas.</P>";
            }
        }
    }
    $postFix = $_POST; //clone($_POST);
    unset($postFix["factura"][0]["concepto"]); // quitar conceptos de la primer factura
    clog2("NO SUBMIT/INSERT\nPOST:\n".arr2str($postFix));
}
// - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - //
function getUbicacionFactura($factura) {
    if (!isset($factura) || !is_array($factura) || !isset($factura["xml"])) return false;
    if ($factura["xml"]->has("fecha"))
        $ffecha = $factura["xml"]->get("fecha");
    if (isset($factura["xml"]->cache["aliasGrupo"]))
        $fcoder = $factura["xml"]->cache["aliasGrupo"];
    if (!isset($ffecha) || !isset($fcoder)) return false;
    $invDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $ffecha);
    if ($invDate === false) return false;
    $dtErrors = DateTime::getLastErrors();
    if (!empty($dtErrors) && ($dtErrors["warning_count"]>0 || $dtErrors["error_count"]>0))
        return false;
    $anio = $invDate->format("Y");
    if (!empty($anio)) $anio .= "/";
    $mes = $invDate->format("m");
    if (!empty($mes)) $mes .= "/";
    $tipoCompro = $factura["xml"]->get("tipo_comprobante");
    if ($tipoCompro[0]==="P") return "recibos/".$fcoder."/".$anio.$mes;
    return "archivos/".$fcoder."/".$anio.$mes;
}
function getRutaReal($uuid) {
    global $invObj;
    if (!isset($invObj)) {
        require_once "clases/Facturas.php";
        $invObj = new Facturas();
    }
    $uuid=strtoupper($uuid);
    $result = $invObj->getValue ("uuid", $uuid, "ubicacion,nombreInterno,nombreInternoPDF");
    if (!$result) return null;
    list($ubicacion,$nombreXML,$nombrePDF) = explode("|",$result);
    if (empty($nombrePDF)) return $ubicacion.$nombreXML.".xml";
    return $ubicacion.$nombrePDF.".pdf";
}
function validaUsuarioProveedor($rfcEmisor) {
    global $tracelog, $prvObj;
    $tracelog = "validaUsuarioProveedor($rfcEmisor)\n";
    require_once "clases/Proveedores.php";
    if (!isset($prvObj)) $prvObj = new Proveedores();
    $usrnm = getUser()->nombre;
    $tracelog.= "username=$usrnm\n";
    if (empty(getUser()->rfc)) {
        $usrRfc = $prvObj->getValue("codigo",$usrnm,"rfc");
        if (!empty($usrRfc)) {
            $prvObj->savedValues["userRFC"] = $usrRfc;
            getUser()->rfc = $usrRfc;
        }
    } else $usrRfc = getUser()->rfc;
    $tracelog.="LOG\n".$prvObj->log;
    $tracelog.="\nUserRFC=$usrRfc\n";
    $tracelog.="validaUsuarioProveedor result=".($rfcEmisor==$usrRfc);
    return $rfcEmisor==$usrRfc;
}
function validaUsuarioCompras($rfcReceptor) {
    global $ugObj;
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_Grupo.php";
        $ugObj = new Usuarios_Grupo();
    }
    return $ugObj->isRelatedByRFC(getUser(), $rfcReceptor, "Compras", "vista");
}
function buscaTraceLog($searchword) {
    global $tracelog;
    $tracearr = explode("\n", $tracelog);
    $matches = array_filter($tracearr, function($var) use ($searchword) { return preg_match("/$searchword/i", $var); });
    return array_values($matches);
}
function consultaServicio($rfcE, $rfcR, $total, $uuid) {
    if (true) {
        global $invObj;
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        }
        $uuid=strtoupper($uuid);
        return $invObj->consultaServicio($rfcE, $rfcR, $total, $uuid);
    }
    //$rfcE = utf8_encode($rfcE); //str_replace("&", "&amp;", $rfcE);
    $rfcE = str_replace("&","&amp;",$rfcE);
    //$rfcR = utf8_encode($rfcR); //str_replace("&", "&amp;", $rfcR);
    $rfcR = str_replace("&","&amp;",$rfcR);
    // maximo 6 digitos para decimales
    $total = sprintf("%.6f", (double)$total);
    // 18 posiciones para los enteros, uno para punto
    $total = str_pad($total, 17, "0", STR_PAD_LEFT); // 25
    $ttDotIdx = strpos($total, ".");
    if ($ttDotIdx!==false) {
        $totIntStr = ltrim(substr($total, 0, $ttDotIdx+1),"0");
        if ($totIntStr===".") $totIntStr="0.";
        $totDecStr = rtrim(substr($total, $ttDotIdx+1),"0");
        if ($totDecStr==="") $totDecStr="0";
        $total = $totIntStr.$totDecStr;
    }
    $qr="?re=$rfcE&rr=$rfcR&tt=$total&id=$uuid";
    clog3("QR: $qr");
    require_once "clases/CFDI.php";
    if ($rfcR===CFDI::RFCDEMO) {
        return ["expresionImpresa"=>$qr, "cfdi"=>"S - Comprobante obtenido satisfactoriamente.", "estado"=>"Vigente", "escancelable"=>"No Cancelable"];
    }
    $factura = valida_en_sat($qr);
    return $factura;
}
function consultaBase($uuid) {
    global $tracelog, $invObj;
    if (!isset($invObj)) {
        require_once "clases/Facturas.php";
        $invObj = new Facturas();
    }
    $uuid=strtoupper($uuid);
    $invData=$invObj->getData("uuid='$uuid'",0,"id, mensajeCFDI cfdi, estadoCFDI estado, status");
    //$result = explode("|",$invObj->getValue("uuid", $uuid, "id, mensajeCFDI cfdi, estadoCFDI estado, status"));
    if (isset($invData[0]["cfdi"])) $result=$invData[0];
    //$tracelog = "consultaBase($uuid)->result = ".str_replace("\"", "\\\"", htmlentities(json_encode($result)));
    //$tracelog .= "<br>isEmpty:".(empty($result)?"TRUE":"FALSE").", isFirstEmpty:".(empty($result[0])?"TRUE":"FALSE").", Count:".count($result);
    if (isset($result["id"])) {
        doclog("AltaFactura:ConsultaBase:","altafac",["uuid"=>$uuid,"result"=>$result]);
        return $result;
    }
    /*if (!empty($result) && !empty($result[0]) && !empty($result[1]) && count($result)>3) {
        return ["id"=>$result[0], "cfdi"=>$result[1], "estado"=>$result[2], "status"=>$result[3]];
    }*/
    //clog2("# CFDI FAIL # consultaBase. UUID=$uuid");
    return false;
}
function obtenerSerie($ubicacion, $nombreInterno) {
    global $invObj;
    if (!isset($invObj)) {
        require_once "clases/Facturas.php";
        $invObj = new Facturas();
    }
    return $invObj->getValue("nombreInterno",$nombreInterno,"serie","ubicacion='$ubicacion'");
}
function ajustarRegistroFactura(&$arr) {
    if (isset($arr["pedido"]) && is_string($arr["pedido"]) && strlen($arr["pedido"])>20) {
        $arr["pedido"] = substr($arr["pedido"], 0, 20);
    }
    if (isset($arr["remision"]) && is_string($arr["remision"]) && strlen($arr["remision"])>20) {
        $arr["remision"] = substr($arr["remision"], 0, 20);
    }
    if (isset($arr["uuid"]) && is_string($arr["uuid"]) && strlen($arr["uuid"])>50) {
        $arr["uuid"] = strtoupper(substr($arr["uuid"], -50));
    }
    if (isset($arr["serie"]) && is_string($arr["serie"]) && strlen($arr["serie"])>50) {
        $arr["serie"] = substr($arr["serie"], -50);
    }
    if (isset($arr["folio"]) && is_string($arr["folio"]) && strlen($arr["folio"])>50) {
        $arr["folio"] = substr($arr["folio"], -50);
    }
    if (isset($arr["noCertificado"]) && is_string($arr["noCertificado"]) && strlen($arr["noCertificado"])>50) {
        $arr["noCertificado"] = substr($arr["noCertificado"], -50);
    }
    if (isset($arr["metodoDePago"]) && is_string($arr["metodoDePago"]) && strlen($arr["metodoDePago"])>50) {
        $arr["metodoDePago"] = substr($arr["metodoDePago"], 0, 50);
    }
    if (isset($arr["nombreOriginal"]) && is_string($arr["nombreOriginal"]) && strlen($arr["nombreOriginal"])>100) {
        $arr["nombreOriginal"] = substr($arr["nombreOriginal"], 0, 100);
    }
    if (isset($arr["nombreInterno"]) && is_string($arr["nombreInterno"]) && strlen($arr["nombreInterno"])>50) {
        $arr["nombreInterno"] = substr($arr["nombreInterno"], 0, 50);
    }
    if (isset($arr["ubicacion"]) && is_string($arr["ubicacion"]) && strlen($arr["ubicacion"])>100) {
        $arr["ubicacion"] = substr($arr["ubicacion"], 0, 100);
    }
    if (isset($arr["mensajeCFDI"]) && is_string($arr["mensajeCFDI"]) && strlen($arr["mensajeCFDI"])>80) {
        $arr["mensajeCFDI"] = substr($arr["mensajeCFDI"], 0, 80);
    }
    if (isset($arr["estadoCFDI"]) && is_string($arr["estadoCFDI"]) && strlen($arr["estadoCFDI"])>30) {
        $arr["estadoCFDI"] = substr($arr["estadoCFDI"], 0, 30);
    }
}
function anexaError($mensajeError,$attributes=null,$data=null) {
    global $file, $errorMessage;
    doclog($mensajeError,$data["logname"]??"error",$data);
    if (is_null($attributes)||is_bool($attributes)) $attributes=""; // ToDo: Si es bool Agregar comentario: 'true' o 'false'
    if (is_array($attributes)||is_object($attributes)) $attributes=json_encode($attributes); // ToDo: Cambiar a vacío y agregar jsonString en comentario
    $errorMessage .= "<P class='fontMedium margin20 centered'>".$mensajeError."</P>";
    if (!isset($file["errmsg"])) $file["errmsg"] = "<div $attributes>$mensajeError</div>";
    else $file["errmsg"] .= "<div $attributes>$mensajeError</div>";
    $file["enough"] = false;
    return;
}
function dblog($txt) {
    inlineLog("DBLOG", $txt);
}
function aflog($txt) {
    inlineLog("ALTAF", $txt);
}
function inlineLog($area, $txt) {
    global $file;
    if(!isset($file["dblog"])) $file["dblog"] = "";
    $file["dblog"] .= "// # $area # $txt\n";
}
function hasValues($values, $specs, &$nodelist) {
    $retval = false;
    foreach ($specs as $key=>$value) {
        if (isset($nodelist[0])) $nodelist.=",";
        $nodelist .= $key;
        if (isset($values[$key])) $retval = true;
    }
    return $retval;
}
function countRows($values, $specs, $key) {
    $retval = 0;
    $type = $specs["type"];
    $tag = $specs["tag"];
    if ($type==="array") {
        foreach ($values as $value) {
            $retval += countRows($value, $specs, $key);
        }
    }
}
function implodeAssoc($array) {
    return str_replace("+", " ", str_replace("&","\", ", str_replace("=","=\"", http_build_query($array))));
}
function procesaDetalle($file, $idx="") {
    $xmlArray = $file["xml"]->toArray();
    $classlist = null;
    if (isset($file["new_name"])) $classlist=$file["new_name"];
    else if (isset($file["uuid"])) {
        $classlist=strtoupper($file["uuid"]);
        if (isset($classlist[8])) $classlist = substr($classlist,-8);
    }
    else if (isset($file["xml"])) {
        if ($file["xml"]->has("folio")) $classlist=$file["xml"]->get("folio");
        else if ($file["xml"]->has("uuid")) {
            $classlist=strtoupper($file["xml"]->get("uuid"));
            if (isset($classlist[8])) $classlist = substr($classlist,-8);
        }
    }
    $filefill = recursiveArray2InputHidden($xmlArray, "XSD$idx", "XSD", $classlist);
    if (isset($file["xml"]->cache["codigoProveedor"])) {
        $filefill.="<input type=\"hidden\" id=\"XSD{$idx}_CDPRV\" name=\"XSD{$idx}_CDPRV\" desc=\"código de proveedor\" value=\"".$file["xml"]->cache["codigoProveedor"]."\" disabled>";
    }
    if (isset($file["xml"]->cache["aliasGrupo"])) {
        $filefill.="<input type=\"hidden\" id=\"XSD{$idx}_ALIGP\" name=\"XSD{$idx}_ALIGP\" desc=\"alias de empresa\" value=\"".$file["xml"]->cache["aliasGrupo"]."\" disabled>";
    }
    $conceptos = $file["xml"]->get("conceptos");
    $numConceptos = count($conceptos);
    if ($numConceptos>0 && isset($conceptos["@cantidad"])) $numConceptos = "1 concepto";
    else $numConceptos = "$numConceptos conceptos";
    $filefill.="<input type=\"hidden\" id=\"XSD{$idx}_NCCPT\" name=\"XSD{$idx}_NCCPT\" desc=\"número de conceptos\" value=\"$numConceptos\" disabled>";
    return $filefill;
}
function recursiveArray2InputHidden($arr, $id, $name=null, $cssclass=null) {
    if ($name===null) $name=$id;
    $retval = "";
    $idx=0;
    foreach ($arr as $key=>$value) {
        if ($key==="dblog") continue;
        $idx++;
        if (is_array($value)) {
            if ($key==="Comprobante") {
                if (isset($cssclass[0])) $cssclass2 = $cssclass." cfdivalue";
                else $cssclass2="cfdivalue";
            } else if (in_array($key,["@attributes"])) $cssclass2=null;
            else if (isset($cssclass)) {
                $cssclass2 = $cssclass;
                if (isset($cssclass[0])) $cssclass2.=" ";
                if (!is_numeric($key)) {
                    if ($key[0]==="@") $cssclass2.=strtolower(substr($key,1));
                    else $cssclass2.=strtolower($key);
                }
            }
            $retval .= recursiveArray2InputHidden($value, $id."_".$idx, $name."[".$key."]", $cssclass2??$cssclass);
        } else if (is_scalar($value)) {
            if (isset($cssclass) && $key==="value") $cssclass2=" class=\"$cssclass\"";
            else $cssclass2="";
            $vartype = gettype($value);
            $retval .= "<input type=\"hidden\" id=\"".$id."_".$idx."\" name=\"".$name."[".$key."]\" vartype=\"".$vartype."\"$cssclass2 value=\"".($vartype==="boolean"?($value?"true":"false"):$value)."\" disabled>\n";
        } else if (is_object($value)) {
            $objclass = get_class($value);
            if ($objclass==="CFDI")
                $retval .= recursiveArray2InputHidden($value->toArray(), $id."_".$idx, $name."[".$key."]", $cssclass);
            else
                $retval .= "<input type=\"hidden\" id=\"".$id."_".$idx."\" name=\"".$name."[".$key."]\" value=\"{".$objclass."}\" disabled>\n";
        } else
            $retval .= "<input type=\"hidden\" id=\"".$id."_".$idx."\" name=\"".$name."[".$key."]\" vartype=\"".gettype($value)."\" value=\"".gettype($value)."\" disabled>\n";
    }
    return $retval;
}
function stripComments($html) {
    return preg_replace('/<!--.*-->/Us', '', $html);
}
clog1seq(-1);
clog2end("configuracion.altafactura");
