<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
if (isset($_GET["facturaId"])) doclog("GET: verificafactura","action",$_GET);
else if (isset($_POST["facturaId"])) doclog("POST: verificafactura","action",$_POST);
else if (isset($_REQUEST["facturaId"])) doclog("REQUEST: verificafactura","action",$_REQUEST);
$canFixReferral = $_esSistemas||$_esCompras||$_esProveedor;
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$modificaProc = modificacionValida("Procesar");
$esRechazante = validaPerfil("Rechaza Aceptadas")||$esSistemas;
$esDesarrollo = in_array(getUser()->nombre, ["admin"]);
$esCompras = validaPerfil("Compras");
$esProveedor = validaPerfil("Proveedor");
$level = 0;
if ($esCompras) $level=1;
if ($modificaProc || $esSistemas) $level=3;
$epsilon = 0.015; //0.000001;
$baseData=["file"=>getShortPath(__FILE__)];
$factId = "".($_REQUEST["facturaId"]??"");
$ctfAuth = ("".($_REQUEST["ctfAuth"]??"0"))==="1";
$soloLectura = isset($_REQUEST["readonly"]);
global $invObj;
if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
$invObj->rows_per_page=0;
if (isset($factId[0])) $factData = $invObj->getData("id=$factId");
if (empty($factData)) {
    echo "<H2>Error al buscar CFDI $factId</H2>";
    echo "<p>Por favor presione Regresar y consulte a su Administrador</p>";
    return;
}
$factura = $factData[0];
$tc=null;$tcx="COMPROBANTE";$tcd="Comprobante";
$esFactura=false;$esPago=false;$esEgreso=false;$esTraslado=false;
if (!empty($factura["tipoComprobante"])) {
    $tipoComprobante = $factura["tipoComprobante"];
    $tc = strtolower($tipoComprobante[0]);
    switch($tc) {
        case "i": $tcx="INGRESO"; $esFactura=true; $tcd="Factura";  break;
        case "e": $tcx="EGRESO";  $esEgreso=true;    $tcd="Nota";    break;
        case "p": $tcx="PAGO";    $esPago=true;    $tcd="Recibo";  break;
        case "t": $tcx="TRASLADO";$esTraslado=true; $tcd="Traslado"; break;
    }
}
if ($ctfAuth && (!$esFactura&&!$esEgreso)) {
    echo "<H2>No es posible autorizar contra recibo</H2>";
    doclog("Se intenta autorizar contra recibo para un $tcd","error",["cfdi"=>$factura]);
    return;
}
if (isset($factura["rfcGrupo"][0])) {
    require_once "clases/Grupo.php";
    $gpoObj=new Grupo();
    $gpoData=$gpoObj->getData("rfc='$factura[rfcGrupo]'",0,"alias,razonSocial");
    if (isset($gpoData[0])) $gpoData=$gpoData[0];
    else $gpoData=null;
} else $gpoData=null;
$folio=$factura["folio"]??"";
$uuid=$factura["uuid"]??"";
$statusn=$factura["statusn"]??null;
$estaRechazado=isset($statusn) && $statusn>=128;
$estaRegistrado=isset($statusn) && !$estaRechazado && $statusn>=0;
$estaAceptado=isset($statusn) && !$estaRechazado && $statusn>=1;
$validarCancelado=true&&$esAdmin; // Bandera para desligar complementos rechazados
$sysPath=$_SERVER["DOCUMENT_ROOT"];
$ubicacion = $factura["ubicacion"];
$nombreXML = $factura["nombreInterno"];
$hasXML = (isset($nombreXML[0])&&file_exists($sysPath.$ubicacion.$nombreXML.".xml"));
$nombrePDF = $factura["nombreInternoPDF"];
$hasPDF = (isset($nombrePDF[0])&&file_exists($sysPath.$ubicacion.$nombrePDF.".pdf"));
$codigoProveedor=$factura["codigoProveedor"]??"";
$GLOBALS["ignoreTmpList"]=["cfdi","dbFieldNames","factData","factList","factura","trace"];
$dbFieldNames=["id","serie","folio","uuid", // identificadores
    "saldoReciboPago","totalPago",      // Total de pago en CP y Egreso
    "ubicacion","nombreInterno","nombreInternoPDF", // ruta y nombre de archivos
    "ciclo","fechaFactura","fechaCaptura", // fechas
    "fechaReciboPago","fechaPago", // fechas de pago CP y Egreso
    "idReciboPago","referenciaPago","archivoEgreso", // id de CP, No de Egreso y arch Eg.
    "statusReciboPago","statusn","status" // estado actual del comprobante fiscal
];

if (isset($codigoProveedor[0])) {
    global $query, $prvObj;
    if (!isset($prvObj)) {
        require_once "clases/Proveedores.php";
        $prvObj = new Proveedores();
    }
    $prvData = explode("|", $prvObj->getValue("codigo", $codigoProveedor, "rfc, razonSocial"));
    if ($esPago && !$estaRegistrado) {
        $prvData[2]=($estaRechazado?"El pago esta rechazado":"No se ha registrado el pago");
        if ($validarCancelado && $estaRechazado) {
            $invDataDR = $invObj->getData("idReciboPago=$factId and (statusReciboPago is null or statusReciboPago>=0)",0,"id");
            if (isset($invDataDR[0]["id"])) {
                $invIds=array_column($invDataDR, "id");
                if ($invObj->updateRecord(["idReciboPago"=>$factId,"fechaReciboPago"=>null,"saldoReciboPago"=>null,"statusReciboPago"=>-1],["idReciboPago"])) {
                    global $prcObj;
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj = new Proceso();
                    }
                    foreach ($invIds as $iid) {
                        $prcObj->alta("Facturas",$iid,"cpago","Complemento de pago Rechazado $factId");
                    }
                } else errlog("Error al actualizar facturas de complemento rechazado","error",$baseData+["line"=>__LINE__,"query"=>$query,"idList"=>$invIds,"rpId"=>$factId,"oErrors"=>$invObj->errors,"iErrors"=>DBi::$errors]);
            }
        }
    } else if ($esPago) {
        global $cpyObj,$dpyObj,$pyObj;
        if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
        if (!isset($dpyObj)) { require_once "clases/DPagos.php"; $dpyObj=new DPagos(); }
        $cpyData = $cpyObj->getData("idCPago=$factId");
        echo "<!-- CPagos[idCPago=$factId] = ".json_encode($cpyData)." -->";
        if (isset($cpyData[0])) {
            $cpyIds=array_column($cpyData,"id");
            $cpyMap=array_combine($cpyIds, $cpyData);
            $cpyLStr=implode(",", $cpyIds);
            $dpyData=$dpyObj->getData("idPPago in ($cpyLStr)");
            echo "<!-- DPagos[idPPago=($cpyLStr)] = ".json_encode($dpyData)." -->";
            if (isset($dpyData[0]["idFactura"])) {
                $fIdList=array_column($dpyData, "idFactura");
                $dpyMap=array_combine($fIdList, $dpyData);
                $ordlst=$invObj->orderlist;
                $invObj->clearOrder();;
                $invObj->addOrder("folio");
                $factList=$invObj->getData("id in (".implode(",", $fIdList).")",0,$dbFieldNames);
                $invObj->orderlist=$ordlst;
                $otherCpStt=[];
                foreach ($factList as $iidx => $cfdi) {
                    $fDocId=$cfdi["id"];
                    $dpyDoc=$dpyMap[$fDocId];
                    $cpyDoc=$cpyMap[$dpyDoc["idPPago"]];
                    $debe=$dpyDoc["saldoInsoluto"];
                    $idRP=$cfdi["idReciboPago"]??0;
                    $slRP=+($cfdi["saldoReciboPago"]??-1);
                    $origSRP=$slRP;
                    if ($idRP>0 && $idRP!=$factId) {
                        if (!isset($otherCPStt[$idRP])) {
                            $ocpData=$invObj->getData("id=$idRP",0,"statusn");
                            if (isset($ocpData[0]["statusn"])) $otherCpStt[$idRP]=$ocpData[0]["statusn"];
                            if ($otherCpStt[$idRP]>=Facturas::STATUS_RECHAZADO) {
                                // ToDo: Debería buscar todos los DPagos con idFactura=$cfdi[id], ordenarlos por numParcialidad(desc) y obtener el primer saldoInsoluto del complemento que no esté rechazado, y asignarlo a slRP y a la factura directamente 
                                $slRP=-1; // con esto se ignora el cp asignado q esta cancelado pero se asigna cualquier otro cp que se consulte aunque sea antiguo, claro que al consultar uno reciente se tendría que actualizar de nuevo
                            }
                        }
                    }
                    echo "<!-- FactList[$iidx] DPagos[saldoInsoluto]=$debe, cfdi[$iidx]=[idReciboPago='$idRP', saldoReciboPago='$slRP'".($slRP!==$origSRP?"|original='$origSRP'":"")."] -->";
                    if ($estaAceptado && ($slRP<0 || (+$debe)<$slRP)) {
                        $result=$invObj->saveRecord(["id"=>$cfdi["id"],"idReciboPago"=>$factId,"fechaReciboPago"=>$cpyDoc["fechaPago"],"saldoReciboPago"=>$debe,"statusReciboPago"=>1]);
                        if ($result) {
                            global $prcObj;
                            if (!isset($prcObj)) {
                                require_once "clases/Proceso.php";
                                $prcObj = new Proceso();
                            }
                            $prcObj->alta("Facturas",$cfdi["id"],"cpago","$factId: $cpyDoc[fechaPago], "."$"."$debe, NumPar$dpyDoc[numParcialidad]");
                        } else errlog("Error al actualizar factura","error",$baseData+["line"=>__LINE__,"query"=>$query,"cfdi"=>$cfdi,"dpyItem"=>$dpyDoc,"cpyItem"=>$cpyMap[$dpyDoc["idPPago"]],"oErrors"=>$invObj->errors,"iErrors"=>DBi::$errors]);
                    }
                }
                if (isset($factList[0])) $prvData[2]=$factList;
            }
        }
        if (!isset($factList[0])) {
            if ($hasXML) {
                require_once "clases/CFDI.php";
                $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
                if (isset($cfdiObj)) {
                    echo "<!-- Cargando datos del XML -->";
                    $fechaPagoCP=$cfdiObj->get("pago_fecha");
                    if (is_array($fechaPagoCP)) {
                        //sort($fechaPagoCP); // ordenar fechas en orden ascendente
                        rsort($fechaPagoCP); // ordenar fechas en orden descendente
                        $fechaPagoCP=$fechaPagoCP[0];
                    }
                    if (is_string($fechaPagoCP)) $fechaPagoCP=str_replace("T"," ",$fechaPagoCP);
                    if (!isset($factura["fechaReciboPago"][0])||$factura["fechaReciboPago"]<$fechaPagoCP) {
                        $montoPagoCP=$cfdiObj->get("pago_monto_total");
                        $factura["fechaReciboPago"]=$fechaPagoCP;
                        $factura["saldoReciboPago"]=$montoPagoCP;
                        $cpFldArr=["id"=>$factId, "fechaReciboPago"=>$fechaPagoCP, "saldoReciboPago"=>$montoPagoCP];
                        if($estaAceptado) { // ToDo: quitar condicion y agregar statusReciboPago
                            $saveResult=$invObj->saveRecord($cpFldArr);
                            if ($saveResult) {
                                global $prcObj;
                                if (!isset($prcObj)) {
                                    require_once "clases/Proceso.php";
                                    $prcObj = new Proceso();
                                }
                                $prcObj->alta("CPago",$factId,"actualiza","$fechaPagoCP, "."$"."$montoPagoCP");
                            }
                        }
                    }
                    if (isset($cpyData[0])) {
                        errlog("Removing obsolete data",getUser()->nombre,$baseData+["line"=>__LINE__,"cpyData"=>$cpyData,"dpyData"=>$dpyData,"CPId"=>$factId,"cpyIds"=>$cpyIds]);
                        $cpyObj->deleteRecord(["idCPago"=>$factId]);
                        if (isset($dpyData[0])) $dpyObj->deleteRecord(["idPPago"=>$cpyIds]);
                    }
                    $pagos=$cfdiObj->get("pagos");
                    if (isset($pagos["@fechapago"])) $pagos=[$pagos];
                    $factList=[];
                    foreach ($pagos as $pgIdx => $pgItem) {
                        $cpgArr=["idCPago"=>$factId,"fechaPago"=>$pgItem["@fechapago"],"montoPago"=>$pgItem["@monto"],"monedaPago"=>$pgItem["@monedap"],"tipocambioPago"=>$pgItem["tipocambiop"]??1];
                        $docsRel=$pgItem["DoctoRelacionado"]??null;
                        if (isset($docsRel["@iddocumento"])) $docsRel=[$docsRel];
                        $cpgArrNeedUpdate=false;
                        foreach ($docsRel as $drIdx => $drItem) {
                            $pgUUID=strtoupper($drItem["@iddocumento"]);
                            $pgFact=$invObj->getData("uuid='$pgUUID'",0,$dbFieldNames);
                            if (isset($pgFact[0]["id"])) $pgFact=$pgFact[0];
                            $pgFId=$pgFact["id"];
                            $drSalIns=$drItem["@impsaldoinsoluto"];
                            $dpgArr=["idFactura"=>$pgFId,"numParcialidad"=>$drItem["@numparcialidad"],"saldoAnterior"=>$drItem["@impsaldoant"],"impPagado"=>$drItem["@imppagado"],"saldoInsoluto"=>$drSalIns,"moneda"=>$drItem["@monedadr"],"equivalencia"=>$drItem["@equivalenciadr"]??1];
                            if (isset($cpgArr["id"])) {
                                if ((+$cpgArr["numParcialidad"])<(+$dpgArr["numParcialidad"])) {
                                    $cpgArr=array_merge($cpgArr,$dpgArr);
                                    $cpgArrNeedUpdate=true;
                                }
                            } else {
                                $cpgArr=array_merge($cpgArr,$dpgArr);
                                if ($cpyObj->saveRecord($cpgArr)) {
                                    $cpgArr["id"]=$cpyObj->lastId;
                                } else {
                                    errlog("Error al guardar Pagos/Pago en CPago","error",$baseData+["line"=>__LINE__,"query"=>$query,"oErrors"=>$cpyObj->errors,"iErrors"=>DBi::$errors]);
                                }
                            }
                            if (isset($cpgArr["id"])) {
                                $dpgArr["idPPago"]=$cpgArr["id"];
                                if (!$dpyObj->saveRecord($dpgArr)) {
                                    errlog("Error al guardar Pagos/Pago/DoctoRelacionado en DPago","error",$baseData+["line"=>__LINE__,"query"=>$query,"oErrors"=>$dpyObj->errors,"iErrors"=>DBi::$errors]);
                                }
                            }
                            $fcSalIns=+$pgFact["saldoReciboPago"]??0;
                            if ($estaAceptado && (+$drSalIns)<$fcSalIns) {
                                $result=$invObj->saveRecord(["id"=>$pgFId,"idReciboPago"=>$factId,"fechaReciboPago"=>$cpgArr["fechaPago"],"saldoReciboPago"=>$drSalIns,"statusReciboPago"=>1]);
                                if ($result) {
                                    global $prcObj;
                                    if (!isset($prcObj)) {
                                        require_once "clases/Proceso.php";
                                        $prcObj = new Proceso();
                                    }
                                    $pgFact["idReciboPago"]=$factId;
                                    $pgFact["fechaReciboPago"]=$cpgArr["fechaPago"];
                                    $pgFact["saldoReciboPago"]=$drSalIns;
                                    $pgFact["statusReciboPago"]=1;
                                    $prcObj->alta("Facturas",$pgFId,"cpago","$factId: $cpgArr[fechaPago], "."$"."$drSalIns, NumPar$dpgArr[numParcialidad]");
                                } else errlog("Error al actualizar factura","error",$baseData+["line"=>__LINE__,"query"=>$query,"cfdi"=>$pgFact,"dpyItem"=>$dpgArr,"cpyItem"=>$cpgArr,"oErrors"=>$invObj->errors,"iErrors"=>DBi::$errors]);
                            }
                            $factList[]=$pgFact;
                        }
                        if ($cpgArrNeedUpdate) {
                            if (!$cpyObj->saveRecord($cpgArr)) {
                                errlog("Error al actualizar Pagos/Pago en CPago","error",$baseData+["line"=>__LINE__,"query"=>$query,"oErrors"=>$cpyObj->errors,"iErrors"=>DBi::$errors]);
                            }
                        }
                    }
                    if (isset($factList[0])) $prvData[2]=$factList;
                    $cpyData = $cpyObj->getData("idCPago=$factId");
                    $cpyIds=array_column($cpyData,"id");
                    $cpyMap=array_combine($cpyIds, $cpyData);
                    $dpyData=$dpyObj->getData("idPPago in (".implode(",", $cpyIds).")");
                    $fIdList=array_column($dpyData, "idFactura");
                    $dpyMap=array_combine($fIdList, $dpyData);
                } else {
                    errlog("Error al abrir XML del CFDI","error",$baseData+["line"=>__LINE__,"sysPath"=>$sysPath,"ubicacion"=>$ubicacion,"xml"=>$nombreXML.".xml"]);
                    $prvData[2]="No se pudo reconocer el XML";
                }
            } else {
                errlog("Error no se encuentra archivo XML del CFDI","error",$baseData+["line"=>__LINE__,"sysPath"=>$sysPath,"ubicacion"=>$ubicacion,"xml"=>$nombreXML.".xml"]);
                $prvData[2]="Complemento sin XML";
            }
        }
        if (isset($factList[0])) foreach ($factList as $iidx => $cfdi) {
            $ordlst=$dpyObj->orderlist;
            $dpyObj->clearOrder();
            $dpyObj->addOrder("numParcialidad");
            $dpyData=$dpyObj->getData("idFactura=$cfdi[id]");
            $dpyObj->orderlist=$ordlst;
            $ppIds=array_column($dpyData, "idPPago");
            $ordlst=$cpyObj->orderlist;
            $cpyObj->clearOrder();
            $cpyObj->addOrder("idCPago");
            $cpyData=$cpyObj->getData("id in (".implode(",", $ppIds).")");
            $cpyQry=$query;
            $cpyObj->orderlist=$ordlst;
            $cpIds=array_column($cpyData,"idCPago");
            $icpStDt=$invObj->getData("id in (".implode(",", $cpIds).")",0,"id,statusn");
            $icpQry=$query;
            $icSDIds=array_column($icpStDt, "id");
            $icSDSts=array_column($icpStDt, "statusn");
            $icSDMap=array_combine($icSDIds, $icSDSts);
            echo "<!-- Lectura de Status: ".json_encode($icSDMap)." -->";
            for ($cpyIdx=count($cpyData)-1; $cpyIdx>=0; $cpyIdx--) {
                $cpyValue=$cpyData[$cpyIdx];
                if (!isset($icSDMap[$cpyValue["idCPago"]])) {
                    array_splice($cpyData, $cpyIdx, 1); // this reindexes array, so it was not working with foreach
                    //unset($cpyData[$cpyIdx]);
                    //$cpyData[$cpyIdx]=null; // false;
                } else
                    $cpyData[$cpyIdx]["statusn"]=$icSDMap[$cpyValue["idCPago"]];
            }
            /*foreach ($cpyData as $cpyIdx => $cpyValue) {
                if (!isset($icSDMap[$cpyValue["idCPago"]])) {
                    array_splice($cpyData, $cpyIdx, 1);
                    //unset($cpyData[$cpyIdx]);
                    //$cpyData[$cpyIdx]=null; // false;
                } else
                    $cpyData[$cpyIdx]["statusn"]=$icSDMap[$cpyValue["idCPago"]];
            }*/
            $cppIds=array_column($cpyData, "id");
            $cpPMap=array_combine($cppIds, $cpyData);
            if (isset($dpyData[0])) {
                foreach ($dpyData as $vidx => $vitem) {
                    $viiPP=$vitem["idPPago"];
                    if (isset($cpPMap[$viiPP]))
                        $dpyData[$vidx]["pp"]=$cpPMap[$viiPP];
                    else doclog("COMPROBANTE NO EXISTE EN FACTURAS","verificafactura",["cpyQry"=>$cpyQry,"cpyIds"=>$cpIds,"icpQry"=>$icpQry,"icSDMap"=>$icSDMap]);
                    // ToDo: Si ya no existe el comprobante en facturas, se debería eliminar de $dpyData
                }
                $prvData[2][$iidx]["cpData"]=$dpyData;
            }
            if (!isset($pyObj)) { require_once "clases/Pagos.php"; $pyObj=new Pagos(); }
            $ordlst=$pyObj->orderlist;
            $pyObj->clearOrder();
            $pyObj->addOrder("fechaPago");
            $pyData=$pyObj->getData("idFactura=$cfdi[id]");
            $dpyObj->orderlist=$ordlst;
            if (isset($pyData)) $prvData[2][$iidx]["epData"]=$pyData;
        } else if (!isset($prvData[2][0])) {
            $prvData[2]="No se encontraron documentos relacionados";
        }
    /* CPAGOS: id,idCPago,idFactura,idEPago,numParcialidad,saldoAnterior,impPagado,saldoInsoluto */
                // toDo: buscar si existen facturas con uuid = cfdi:Complemento|pago10:Pagos|pago10:Pago|pago10:DoctoRelacionado[@iddocumento]
                // toDo: mostrar facturas, validarlas, modificarlas (agregar idReciboPago=$factId) y anexar sus datos en $prvData[2]
                // toDo: poner un indicador de que el recibo de pago y sus facturas fueron modificados, una paloma o algo asi
                // [id,folio,uuid,ubicacion,nombreInternoPDF,idReciboPago,statusn,status,rpData:[folio,uuid,ubicacion]]
    //} else if ($esTraslado) {
    //    ;
    } else {
        require_once "clases/Conceptos.php";
        $cptObj = new Conceptos();
        $cptObj->rows_per_page=0;
        $prvData[2] = $cptObj->getData("idFactura='$factId'");
    }
} else {
    $prvData = ["", "",[]];
}
$hasEA=false;
if(($factura["ea"]??"0")==="1") {
    $eacp=trim(str_replace("-", "", $codigoProveedor));
    $usIdx=strrpos($nombreXML, "_");
    if ($usIdx!==false && $usIdx>0) {
        $eafolio=substr($nombreXML, $usIdx+1);
    } else {
        $eafolio=(isset($folio[0])?$folio:$uuid);
        if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
    }
    if (isset($folio[0]) && isset($serie[0])) $eafolio2=$serie.$folio;
    if (!isset($eafolio2)||$eafolio===$eafolio2) {
        if (isset($folio[0]) && $eafolio!==$folio) $eafolio2=substr($folio, -10);
        else $eafolio2=null;
    }
    $eafecha=substr(str_replace("-","", $factura["fechaFactura"]),2,6);
    $nombreEA = "EA_{$eacp}_{$eafolio}_{$eafecha}";
    //echo "<!-- Nombre EA1: {$ubicacion}$nombreEA -->\n";
    if (file_exists($sysPath.$ubicacion.$nombreEA.".pdf")) $hasEA = true;
    else {
        echo "<!-- No se encontró EA1:'{$nombreEA}' -->\n";
        if ($esFactura) {
            if (isset($eafolio2[0])) {
                $nombreEA = "EA_{$eacp}_{$eafolio2}_{$eafecha}";
                $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
                if (!$hasEA) echo "<!-- No se encontró EA2: '{$nombreEA}' -->\n";
            }
            if (!$hasEA && isset($nombreXML[0])) {
                $eafolio3 = substr($nombreXML, -10);
                $nombreEA = "EA_{$eacp}_{$eafolio3}_{$eafecha}";
                $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
                if (!$hasEA) echo "<!-- No se encontró EA3: '{$nombreEA}' -->\n";
            }
        } else if ($esEgreso||$esPago) {
            $nombreEA = "EA_{$eacp}_".($esEgreso?"NC":"RP")."_{$eafolio}_{$eafecha}";
            $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
            if (!$hasEA) echo "<!-- No se encontró EA".($esEgreso?"E":"P").": '{$nombreEA}' -->\n";
        } else if ($esTraslado) {
            $nombreEA = "EA_{$eacp}_TR_{$eafolio}_{$eafecha}";
            $hasEA=file_exists($sysPath.$ubicacion.$nombreEA.".pdf");
            if (!$hasEA) echo "<!-- No se encontró EAT: '{$nombreEA}' -->\n";
        } else echo "<!-- EA: SIN TIPO COMPROBANTE! -->\n";
    }
}
$esPendiente = isset($statusn) && Facturas::estaPendiente(+$factura["statusn"]);//($factura["status"]==="Pendiente");
if ($esPendiente) {
    $highClass = " highlight";
    if ($esPago) {
        $highClassP = " class=\"highlight\"";
?>
<H4 class="marginH3">Corrobore que <span class="highlight">Fecha Pago</span>, <span class="highlight">Folio</span> y <span class="highlight">Monto pagado</span>  coincidan con un movimiento bancario.<!-- br>La fecha de Egreso debería coincidir si estuviera registrado.--></H4>
<?php
    } else {
?>
<H3>Corrobore que <span class="highlight">Num. de Pedido</span> y <span class="highlight">Código</span> de los artículos sean los correctos antes de Confirmar.</H3>
<?php
    }
}
//clog2("\$factura ".json_encode($factura,JSON_PRETTY_PRINT));
?>
<table id="tabla_valida_pedido" class="tableWithScrollableCells">
  <tr style="height: 1px;"><td style="width: 30%; padding_right: 5px; vertical-align: top; height: inherit;">
    <ul class="marginbottom lefted">
      <li>Documentos :
        <?= isset($nombreXML[0])?"<a href=\"$ubicacion$nombreXML.xml\" data-title=\"Archivo XML\" title=\"CFDI XML\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/xml200.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>":"" ?>
        <?= isset($nombrePDF[0])?"<a href=\"$ubicacion$nombrePDF.pdf\" data-title=\"Archivo PDF\" title=\"CFDI PDF\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/pdf200.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>":"" /*"<img src=\"imagenes/icons/invChk200.png\" width=\"20\" height=\"20\" class=\"noBorder2 bgred bxsbrd pointer marginV2 hidBdr\" title=\"Anexar PDF\" onclick=\"const fx=ebyid('attachpdffile');fx.click();\"><span id=\"attachpdfcap\" class=\"redden boldValue padl1\">SIN PDF</span><input type=\"file\" name=\"attachpdffile\" id=\"attachpdffile\" accept=\".pdf\" class=\"hidden\" onchange=\"const c=ebyid('attachpdfcap');const m=ebyid('attachpdfmsg');if(this.files.length==0||this.files[0].name.length==0){c.textContent='SIN PDF';m.textContent='Presione icono azul para anexar CFDI-PDF';}else{c.textContent='';m.textContent='';}\"><br><span id=\"attachpdfmsg\" class=\"smaller bgred\">Presione icono azul para anexar CFDI-PDF.</span>"*/ ?>
        <?= $hasEA?"<a href=\"$ubicacion$nombreEA.pdf\" data-title=\"Entrada de Almacén\" title=\"EA PDF\" target=\"archivo\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/pdf200EA.png\" width=\"20\" height=\"20\" class=\"noBorder2\"></a>":"" ?>
      </li>
      <?php /* <?= empty($nombrePDF)?"":"<LI>Archivo PDF : <A HREF=\"$ubicacion$nombrePDF.pdf\" target=\"archivopdf\"><B>$nombrePDF.pdf</B></A></LI>" ?>
      <?= empty($nombreXML)?"":"<LI>Archivo XML : <A HREF=\"$ubicacion$nombreXML.xml\" target=\"archivoxml\"><B>$nombreXML.xml</B></A></LI>" */ ?>
      <li>Folio Fiscal(UUID) : <b class="asLinkH" title="Click para copiar:
<?=$uuid?>" onclick="copyTextToClipboard('<?=$uuid?>');"><?= reduccionMuestraDeCadenaLarga($uuid,6) ?></b></li>
<?php
/*
<span class=\"swhid swhiduuid asLinkH hidden\" title=\"Click para copiar:\n$pguuid\" onclick=\"copyTextToClipboard('$pguuid');\">$pguuidShort</span>

<li>Emisor : <b><?= $prvData[1] ?></b></li>
<li>R.F.C. : <b><?= $prvData[0] ?></b></li>
<li>Código : <b><?= $codigoProveedor ?></b></li> */
if(isset($codigoProveedor[0])) echo "<li title=\"{$prvData[1]}\">Proveedor : <b>$codigoProveedor</b></li>";
if(isset($gpoData)) echo "<li title=\"$gpoData[razonSocial]\">Empresa : <b>$gpoData[alias]</b></li>";
echo "<li>Tipo : $tcx</li>";
if(isset($factura["usoCFDI"][0])) {
    require_once "clases/catalogoSAT.php";
    $usoDesc=CatalogoSAT::getValue(CatalogoSAT::CAT_USOCFDI, "codigo", $factura["usoCFDI"], "descripcion");
    echo "<li>Uso CFDI : <b>$factura[usoCFDI]</b> ($usoDesc)</li>";
}
if (isset($factura["fechaFactura"][0])) {
    $fechaFactura=date("Y-m-d H:i:s",strtotime($factura["fechaFactura"]));
    echo "<li><span id=\"capFecha\">Fecha</span> : <b>$fechaFactura</b></li>";
}
if (isset($factura["fechaReciboPago"][0])) {
    // TODO: Para cada CFDI de Pago, para cada Pago, guardar campo Pagos:Pago:FechaPago
    $fechaCPago=date("Y-m-d",strtotime($factura["fechaReciboPago"]));
    echo "<li><span id=\"capPFecha\"".($highClassP??"").">Fecha Pago</span> : <b>$fechaCPago</b><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const cf=ebyid('capFecha');cladd(cf, 'inblock');cf.style.width=ebyid('capPFecha').offsetWidth+'px';ekil(this);\"></li>";
} else $fechaCPago="";
/* <!-- li>Certificado : <b> $factura["noCertificado"] </b></li -->
<!-- li>Version : <b> $factura["version"] </b></li --> */
if (isset($factura["metodoDePago"][0])) {
    $metodoPago = $factura["metodoDePago"];
    require_once "clases/catalogoSAT.php";
    $metodoDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_METODOPAGO, "codigo", $metodoPago, "descripcion");
    echo "<li>M&eacute;todo de Pago : <b>$metodoPago</b> ($metodoDesc)</li>";
}
if (isset($factura["formaDePago"][0])) {
    $formaPago = $factura["formaDePago"];
    require_once "clases/catalogoSAT.php";
    $formaDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_FORMAPAGO, "codigo", $formaPago, "descripcion");
    echo "<li>Forma de Pago : <b>$formaPago</b> ($formaDesc)</li>";
}
if (isset($factura["serie"][0]))
    echo "<li>Serie : <b>$factura[serie]</b></li>";
if (isset($folio[0]))
    echo "<li><span".($highClassP??"").">Folio</span> : <b>$folio</b></li>";
if ($esPago && isset($factura["saldoReciboPago"][0])) {
    $saldoPago = $factura["saldoReciboPago"];
    echo "<li><span".($highClassP??"").">Monto pagado</span> : <b>$".number_format($saldoPago,2)."</b></li>";
}
$realStatus=Facturas::statusnToRealStatus($statusn,$tipoComprobante,$level);
$sttttl=$esAdmin?" title='STT:$statusn, TC:$tipoComprobante, LV:$level'":"";
echo "<li{$sttttl}>Status : <b>$realStatus</b></li>";
if ($estaRechazado) {
    global $prcObj;
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    // select detalle from proceso where modulo="Factura" and identif=446366 and status="Rechazado";
    $prcData=$prcObj->getData("modulo='Factura' and identif=$factId and status='Rechazado'", 0, "detalle");
    if (isset($prcData[0]["detalle"][0])) echo "<li>Motivo: <b>".$prcData[0]["detalle"]."</b></li>";
}
?>
      <li class="<?= (strpos($factura["mensajeCFDI"],"satisfactoriamente")===FALSE||$factura["estadoCFDI"]!=="Vigente")?"bgred":"bggreen" ?>">CFDI: <b><?= $factura["mensajeCFDI"] ?><br><?= $factura["estadoCFDI"] ?></b></li>
<?php
if (!$esPago && !$esTraslado) { ?>
      <li><span class="vAlignCenter nowrap<?=$highClass??""?>">Num. de Pedido : <input type="text" name="numpedido" id="numpedido" class="widavailable" value="<?= $factura["pedido"] ?>" <?=$soloLectura?"readonly":"onchange=\"agregaValorPost(this);\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"agregaDatoPost('f_numpedido', '$factura[pedido]');agregaDatoPost('fold_numpedido', '$factura[pedido]');this.parentNode.removeChild(this);\"" ?>></span></li>
      <li><span class="vAlignCenter nowrap">Num. de Remision : <input type="text" name="numremision" id="numremision" maxlength="20" class="widavailable" value="<?= $factura["remision"] ?>" <?=$soloLectura?($canFixReferral?"":"readonly"):"onchange=\"agregaValorPost(this);\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"agregaDatoPost('f_numremision', '$factura[remision]');agregaDatoPost('fold_numremision', '$factura[remision]');this.parentNode.removeChild(this);\"" ?>><?php if($soloLectura&&$canFixReferral) { ?><img src="<?=$_SERVER["HTTP_ORIGIN"].$_SERVER["WEB_MD_PATH"]?>imagenes/icons/carga8_20.png" alt="Guardar" title="GUARDAR" class="btnLt btn20 vAlignCenter marL2 noprint" onclick="saveReferral(<?=$factId?>);"><?php } ?><?= $_esSistemas?"<!-- SISTEMAS -->":"<!-- X -->" ?><?= $_esCompras?"<!-- COMPRAS -->":"<!-- Y -->" ?><?= $_esProveedor?"<!-- PROVEEDOR -->":"<!-- Z -->" ?></span></li>
<?php
} ?>
    </ul></td>
<?php
if ($esPago) { 
    $refreshIcon=$esSistemas?"<img src=\"imagenes/icons/refresh.png\" class=\"btn12 mar2 vaBase pointer\" onclick=\"updatePaymRcpt($factId);\">":"";
    ?>
    <td id="paymDocsSection"><h3>Documentos relacionados<?=$refreshIcon?></h3><table border="1" id="table_of_invoices">
      <thead><tr class="nowrap centered">
      <th style="width: 112px;"><img src="imagenes/icons/prev01_20b.png" onclick="switchHeadCell(this);"><span idx="0" arr="id" class="paymFixableCaption inblock">Folio</span><img src="imagenes/icons/next01_20b.png" onclick="switchHeadCell(this);"></th><th><img src="imagenes/icons/prev01_20b.png" onclick="switchHeadCell(this);"><span idx="0" arr="fecha" class="paymFixableCaption inblock">Parcialidad</span><img src="imagenes/icons/next01_20b.png" onclick="switchHeadCell(this);"></th><th style="width: 86px;">Documentos</th>
<?php
//} else if ($esTraslado) {
    // Header a mostrar de traslados Carta Porte
} else { ?>
    <td id="conceptsSection"><h3 class="wide">Artículos</h3><table border="1" id="table_of_concepts">
      <thead><tr class="nowrap centered">
      <th>Cantidad</th><th id="headCode">C&oacute;digo</th><th>Descripci&oacute;n</th><th>P.Unit.</th><th>Importe</th>
<?php
} ?>
      </tr></thead>
      <tbody>
<?php
if ($esPago) {
    $ffolios=[];
    $ffolios2=[];
    $ffolios3=[];
    if (!isset($prvData[2][0])||!is_array($prvData[2][0])) {
/*        if ($esDesarrollo && $hasXML) {
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
            if (isset($cfdiObj)) {
                echo "<tr><td colspan=\"3\">SI</td></tr>";
            } else echo "<tr><td colspan=\"3\">NO</td></tr>";
        }*/
        echo "<tr><td colspan=\"3\">".(isset($prvData[2][0])?$prvData[2]:"NO SE IDENTIFICARON FACTURAS RELACIONADAS")."</td></tr>"; // toDo: cambiar por una imagen de tache rojo
    } else foreach ($prvData[2] as $pidx=>$pago) {
        // echo "<-- PAGO $pidx: ".json_encode($pago)." -->";
        if (is_array($pago) && isset($pago["id"])) { //  && isset($pago["idReciboPago"])
// [id,folio,uuid,ubicacion,nombreInternoPDF,idReciboPago,statusn,status,rpData:[folio,uuid,ubicacion,nombreInternoPDF,statusn,status]]
            $pgub=$pago["ubicacion"]??"";
            $pguuid=$pago["uuid"]??"";
            $pgfolio=$pago["folio"]??"";
            $pgReciboId=$pago["idReciboPago"]??"";
            $pgCPs=$pago["cpData"]??[];
            $pgEPs=$pago["epData"]??[];
            $pgStn=$pago["statusn"]??0;
            $pgTc=$pago["tipoComprobante"]??"i";
            $pgStt=Facturas::statusnToRealStatus($pgStn,$pgTc,($modificaProc?2:($esProveedor?0:1)));
            $invTtl=" title=\"$pgStt\"";
            if (isset($pgfolio[0])) {
                if (!in_array($pgfolio, $ffolios)) {
                    $ffolios[]=$pgfolio;
                    if (isset($pgfolio[2])) {
                        $ffolios2[]=substr($pgfolio, 1);
                        $ffolios2[]=substr($pgfolio, 0, -1);
                    }
                    if (isset($pgfolio[3])) {
                        $ffolios3[]=substr($pgfolio, 2);
                        $ffolios3[]=substr($pgfolio, 0, -2);
                    }
                }
            } else if (isset($pguuid[0])) {
                $pgfolio="[".substr($pguuid, -10)."]";
                $ffolios[]=substr($pguuid, 0, 6);
                $ffolios[]=substr($pguuid, 2, 6);
                $ffolios[]=substr($pguuid, -6);
                $ffolios[]=substr($pguuid, -12, 6);

            }
            $pgLinks="";
            if (isset($pago["nombreInterno"][0])) {
                $pghref=$pgub.$pago["nombreInterno"].".xml";
                if (isset($pgub[0])) {
                    $pgLinks.="<a href=\"$pghref\" data-title=\"Archivo XML\" title=\"CFDI XML\" target=\"factura\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/xml200.png\" width=\"16\" height=\"16\" class=\"noBorder2\"></a>";
                } else $pgLinks.="<img src=\"imagenes/icons/xml200.png\" width=\"16\" height=\"16\" class=\"noBorder2\" style=\"cursor: no-drop;\" title=\"Sin archivo: $pghref\">";
            }
            if (isset($pago["nombreInternoPDF"][0])) {
                $pghref=$pgub.$pago["nombreInternoPDF"].".pdf";
                if (isset($pgub[0])) {
                    $pgLinks.="<a href=\"$pghref\" data-title=\"Archivo PDF\" title=\"CFDI PDF\" target=\"factura\" tabindex=\"-1\" onfocus=\"this.blur();\" class=\"pointer marginV2 hidBdr\"><img src=\"imagenes/icons/pdf200.png\" width=\"16\" height=\"16\" class=\"noBorder2\"></a>";
                } else $pgLinks.="<img src=\"imagenes/icons/pdf200.png\" width=\"16\" height=\"16\" class=\"noBorder2\" style=\"cursor: no-drop;\" title=\"Sin archivo: $pghref\">";
            } else if (isset($pago["nombreInterno"][0]) && isset($pago["ciclo"][0])) {
                $pgLinks.="<img src=\"imagenes/icons/invChk200.png\" width=\"16\" height=\"16\" class=\"pointer\" title=\"ANEXAR ARCHIVO PDF\" onclick=\"generaFactura('$pago[nombreInterno]','$pago[ciclo]','factura');\">";
            }
            if (isset($pgLinks[0])) $pgLinks="<span class=\"swhdocs\">$pgLinks</span>";
            $pguuidShort=isset($pguuid[0])?reduccionMuestraDeCadenaLarga($pguuid,6):""; //substr($pguuid, 0, 4)."...".substr($pguuid, -4);
            $pguuidAtts=isset($pguuid[0])?" title=\"Click para copiar:\n$pguuid\" onclick=\"copyTextToClipboard('$pguuid');\"":"";
            $pgId="<span class=\"swhid swhidfolio\"$invTtl>$pgfolio".($esSistemas?"<img src=\"imagenes/icons/backArrow.png\" class=\"btn10 pointer\" onclick=\"resetRP($pago[id]);\">":"")."</span><span class=\"swhid swhiduuid asLinkH hidden\"$pguuidAtts>$pguuidShort</span>"; //  // crea alta pago egreso
            $pgParcialidad="";
            // CPAGOS: id, idCPago, idFactura, idEPago, numParcialidad, saldoAnterior, impPagado, saldoInsoluto
            if (isset($pgCPs[0])) {
                $cdpIdx=-1;
                foreach ($pgCPs as $dpIdx => $dpItem) {
                    $cpItem=$dpItem["pp"]??null;
                    if(isset($cpItem) && isset($cpItem["idCPago"]) && $cpItem["idCPago"]==$factId) {
                        $cdpIdx=$dpIdx;
                        break;
                    }
                }
                if ($cdpIdx>=0) {
                    $ppd=$pgCPs[$cdpIdx];
                    $numPar="Num.$ppd[numParcialidad]";
                    if (!isset($pgReciboId[0])) $numPar.=" *";
                    $impPagado=$ppd["impPagado"];
                    $pagado="&dollar;".number_format($impPagado,2);
                    if (isset($ppd["moneda"][0]) && $ppd["moneda"]!=="MXN") {
                        $pgCurr=$ppd["moneda"];
                        $pgEquiv=+$ppd["equivalencia"];
                        $pagado.=$pgCurr;
                        $impPagadoMx=$impPagado/$pgEquiv;
                        $pagadoMx="&dollar;".number_format($impPagadoMx,2);
                        $omon=strtolower($ppd["moneda"]);
                        $pgCurrAtt=" mon=\"$ppd[moneda]\" $omon=\"$pagado\" mxn=\"$pagadoMx\" onclick=\"switchPymCurrency(event);\"";
                    } else $pgCurrAtt="";
                    if ($ppd["saldoInsoluto"]>=1)
                        $insoluto="<span class=\"cap\">Debe:</span><span class=\"curr\">&dollar;".number_format($ppd["saldoInsoluto"],2).($pgCurr??"")."</span>";
                    else $insoluto="Liquidado";
                } else {
                    $numPar="";
                    $pagado="";
                    $pgCurrAtt="";
                    $insoluto="";
                }
                $parTtl="";
                if (isset($pgCPs[1])) {
                    $parTtl="";
                    foreach ($pgCPs as $dpIdx => $dpItem) {
                        if (isset($parTtl[0])) $parTtl.="\n";
                        $cpItem=$dpItem["pp"]??null;
                        if (isset($cpItem)) {
                            if (isset($cpItem["statusn"]) && (+$cpItem["statusn"])>=Facturas::STATUS_RECHAZADO) continue;
                            $cpFechaPago=substr($cpItem["fechaPago"], 0,  10);
                        }
                        if (!isset($cpFechaPago)) {
                            if (isset($fechaCPago)) $cpFechaPago=$fechaCPago;
                            else $cpFechaPago="";
                        }
                        $cpSaldoPago=number_format(+$dpItem["saldoInsoluto"],2);
                        // ToDo: FolioCP FechaPago NumPar SaldoIns Moneda
                        $parTtl.="Num.$dpItem[numParcialidad]) $cpFechaPago "."$"."$cpSaldoPago$dpItem[moneda]";
                    }
                    if (isset($parTtl[0])) $parTtl.=" title=\"$parTtl\"";
                }
                $pgParcialidad="<span class=\"swhpNum\"{$parTtl}>$numPar</span><span class=\"swhpPym\" title=\"Importe Pagado\"><span class=\"cap\">Pago:</span><span class=\"curr\"$pgCurrAtt>$pagado</span></span><span class=\"swhpIns\" title=\"Saldo Insoluto\">$insoluto</span>";
            } else {
                $pgParcialidad="...";
                foreach ($pago as $key => $value) {
                    $pgParcialidad.="\n<!-- $key : ";
                    if (is_array($value)) $pgParcialidad.=json_encode($value);
                    else $pgParcialidad.="\"$value\"";
                    $pgParcialidad.="-->";
                }
            }
            // $pgRPago=str_replace(" 00:00:00", "", $pago["fechaReciboPago"]);
            $fechaEPago=$pago["fechaPago"]??"";
            $egresoClass="swheDate";
            $egresoWarning="";
            $pgEgreso="";
            if (!isset($fechaEPago[0]) && isset($pgEPs[0])) {
                if (isset($cdpIdx) && $cdpIdx>=0 && isset($pgEPs[$cdpIdx])) {
                    $fechaEPago=$pgEPs[$cdpIdx]["fechaPago"];
                } else if (isset($pgEPs[0])) {
                    $fechaEPago=end($pgEPs)["fechaPago"];
                }
            }
            if (isset($fechaEPago[0])) {
                // id, archivo, codigoProveedor, idFactura, fechaPago, cantidad, iva, total, tipo, referencia, valido, modifiedTime
                $hep="";
                if (isset($pgEPs[0])) foreach ($pgEPs as $epId => $epRow) {
                    $fep=substr($epRow["fechaPago"], 0, 10);
                    //if ($fep)
                    // $hep.=
                }
                $horaEPago=isset($fechaEPago[10])?substr($fechaEPago, 11, 8):"";
                if (isset($hep[0])) {
                    if (isset($horaEPago[0])) $horaEPago.="\n";
                    $horaEPago.=$hep;
                }
                if (isset($horaEPago[0])) {
                    $horaEPago=" title=\"$horaEPago\"";
                }
                $fechaEPago=substr($fechaEPago, 0, 10);
                if ($fechaCPago===$fechaEPago) {
                    $egresoClass.=" bggreen";
                } else {
                    $egresoClass.=" stroke bgred2 darkRedLabel";
                    $egresoWarning="<br>No coinciden fecha de complemento y egreso";
                }
                $pgEgreso="<span class=\"$egresoClass\"$horaEPago>$fechaEPago</span>";
            }
            if (isset($pago["referenciaPago"][0])) {
                $pgTitle=($esDesarrollo&&isset($pago["archivoEgreso"][0]))?" title=\"$pago[archivoEgreso]\"":"";
                if ($esDesarrollo) $pgEgreso.="<!-- ".json_encode($pago)." -->";
                $pgEgreso.="<span class=\"swheNum\"$pgTitle>".trim(str_replace("Egreso", "", $pago["referenciaPago"]))."</span>";
            }
            if (isset($pago["totalPago"][0])) $pgEgreso.="<span class=\"swheCurr\">&dollar;".number_format($pago["totalPago"],2)."</span>";
            if (!isset($pgEgreso[0])) $pgEgreso="No registrado";
            $pagoFechaCrea=$pago["fechaFactura"]??"";
            $pagoFechaAlta=$pago["fechaCaptura"]??"";
            $pgDates="<span class=\"swhfecha swhfechapar\">$pgParcialidad</span><span class=\"swhfecha swhfechaegreso hidden\">$pgEgreso</span><span class=\"swhfecha swhfechacrea hidden\">$pagoFechaCrea</span><span class=\"swhfecha swhfechaalta hidden\">$pagoFechaAlta</span>";
            //$pgstts="Asignada a ";
            if (isset($pago["rpData"])) {
                //$rpDt=$pago["rpData"];
                //$rpub=$rpDt["ubicacion"];
                $rpfolio=$pago["rpData"]["folio"];
                //$rpuuid=$rpDt["uuid"];
                //if (!isset($rpfolio[0])) $rpfolio="[".substr($rpuuid, -10)."]";
                $pgRowAtt=$rpfolio==$folio?"":" class=\"stroke\"";
                //if (isset($rpDt["nombreInternoPDF"][0])) {
                //    $rphref=$rpub.$rpDt["nombreInternoPDF"].".pdf";
                //    $pgstts.="<A HREF=\"$rphref\" TARGET=\"factura\" tabindex=\"-1\">$rpfolio</A>";
                //} else if (isset($rpDt["nombreInterno"][0])) {
                //    $pgstts.="<SPAN class=\"alink btst nobg\" title=\"FACTURA XML\" onclick=\"generaFactura('$rpDt[nombreInterno]','$rpDt[ciclo]','factura');\">".$rpfolio."</SPAN>";
                //} else $pgstts.=$rpfolio;
            } //else $pgstts.="<i>$pago[idReciboPago]</i>";
            // pglink => folio  | pguuid => uuid | pgstts => estado
            // pgId => Folio/UUID | pgDates => FCreacion/FCaptura/FCPago/FEgreso | pglink => documentos xml y pdf
?>
        <tr<?=$pgRowAtt??""?>><td><?= $pgId ?></td>
            <td><?= $pgDates ?></td>
            <td><?= $pgLinks ?></td></tr>
<?php
        } else if (is_array($pago) && isset($pago[0]) && isset($pago[7])) {
            $fact_id=$pago[0];
            $fact_serie=$pago[1];
            $fact_folio=$pago[2];
            $fact_uuid=$pago[3];
            $fact_saldo=+$pago[4];
            $fact_ubicacion=$pago[5];
            $fact_pdfname=$pago[6];
            $fact_xmlname=$pago[7];
            $fact_name="";
            $fact_tgt="";
            $hasFSerie=isset($fact_serie[0]);
            $hasFFolio=isset($fact_folio[0]);
            if ($hasFSerie) {
                $fact_name.=$fact_serie;
                $fact_tgt.=$fact_serie;
                if ($hasFFolio) $fact_name.="-";
            }
            if($hasFFolio) {
                $fact_name.=$fact_folio;
                $fact_tgt.=$fact_folio;
            }
            if(!isset($fact_name[0])) {
                $fact_name="SIN FOLIO";
                $fact_tgt="factura";
            }
            $fact_href=$fact_ubicacion;
            if (isset($fact_pdfname[0])) $fact_href.=$fact_pdfname.".pdf";
            else if (isset($fact_xmlname[0])) $fact_href.=$fact_xmlname.".xml";
            else $fact_href="";
            if (isset($fact_href[0])) $fact_link="<A HREF=\"$fact_href\" TARGET=\"$fact_tgt\" tabindex=\"-1\">$fact_name</A>";
            else $fact_link="";
            if ($fact_saldo==0) $fact_stts = "PAGADA";
            else $fact_stts = "PARCIAL";
?>
        <tr><td><?= $fact_link ?></td>
            <td><?= $fact_uuid ?></td>
            <td><?= $fact_stts ?></td></tr>
<?php
        } else {
            if (gettype($pago)==="string" && isset($pago[0])) clog3("Mensaje de error: $pago");
            else {
                clog3("PAGO INCOMPLETO: ".json_encode($pago));
                $pago="Documento Relacionado incompleto";
            }
?>
        <tr><td colspan="3">$pago</td></tr>
<?php
        }
    }
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"adjustPymWidths();this.parentNode.removeChild(this);\">";
//} else if ($esTraslado) {
    // Datos a mostrar de traslados Carta Porte
} else {
    $subtotal = $factura["subtotal"];
    $total = $factura["total"];
    $sumaImportes=0;
    $sumaDescuento=0;
    $sumaTraslado=0;
    $sumaRetenido=0;
    foreach ($prvData[2] as $concepto) {
        $clsUni="";
        $clsDsc="";
        $cantidad = +$concepto["cantidad"];
        $precioUnitario = +$concepto["precioUnitario"];
        $importe = +$concepto["importe"];
        $calculado = $cantidad * $precioUnitario;
        $diferencia = abs($calculado-$importe);
        $sumaImportes+=$importe;

        $sumaDescuento += +$concepto["importeDescuento"];
        $sumaTraslado += +$concepto["impuestoTraslado"];
        $sumaRetenido += +$concepto["impuestoRetenido"];

        if ($diferencia<$epsilon) {
            $claseImporte="bggreen";
            $tipEval="Importe correcto";
            //if ($diferencia!==0) $tipEval.=". Diferencia descartable: ".number_format($diferencia,9).".";
        } else {
            $claseImporte="bgred";
            $tipEval="Importe no corresponde al calculado: $".number_format($calculado,6);
        }
        //$titleUnidad = "";
        $unidad = htmlentities($concepto["unidad"]??"");
        $claveUnidad = $concepto["claveUnidad"]??"";
        if (isset($claveUnidad[0])) {
            require_once "clases/catalogoSAT.php";
            $nombreClaveUnidad = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD, "codigo", $claveUnidad, "nombre");
            if (strcasecmp($unidad, $nombreClaveUnidad)==0||stripos($unidad, $nombreClaveUnidad)!==false) $clsUni=" bggreen2";
            //if (!empty($nombreClaveUnidad))
            //    $titleUnidad = " title=\"ClaveUnidad SAT: $claveUnidad='$nombreClaveUnidad'\"";
            //else
            //    $titleUnidad = " title=\"ClaveUnidad: $claveUnidad (No definida en SAT)\"";
        } else $nombreClaveUnidad="";
        //$titleCodigo = "";
        $descripcion = htmlentities($concepto["descripcion"]??"");
        $claveProdServ = $concepto["claveProdServ"]??"";
        if (isset($claveProdServ[0])) {
            require_once "clases/catalogoSAT.php";
            $nombreClaveProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV, "codigo", $claveProdServ, "descripcion");
            if (isset($nombreClaveProdServ[0])) {
                //$titleCodigo = " title=\"ClaveProdServ SAT: $claveProdServ='$nombreClaveProdServ'\"";
                if (strcasecmp($descripcion, $nombreClaveProdServ)==0||stripos($descripcion, $nombreClaveProdServ)!==false) $clsDsc=" bggreen2";
                if (substr($claveProdServ, -2)==="00") {
                    $rowCPS="<b><span class=\"fixWid inblock\" fixId=\"headCode\">$claveProdServ</span>$nombreClaveProdServ'</b>";
                    $numClaveProdServ=intval($claveProdServ);
                    for ($i=1; $i < 100; $i++) {
                        $nxtClaveProdServ=$numClaveProdServ+$i;
                        $nxtNombreProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo", $nxtClaveProdServ, "descripcion");
                        if (isset($nxtNombreProdServ[0])) {
                            if (strcasecmp($descripcion, $nxtNombreProdServ)==0||stripos($descripcion, $nxtNombreProdServ)!==false) $clsDsc=" bggreen2";
                            $rowCPS.="<br><span class=\"fixWid inblock\" fixId=\"headCode\">$nxtClaveProdServ</span>$nxtNombreProdServ";
                        }
                        else break;
                    }
                } else $rowCPS="<span class=\"fixWid inblock\" fixId=\"headCode\">$claveProdServ</span>$nombreClaveProdServ";
            } else {
                //$titleCodigo=" title=\"ClaveProdServ SAT: $claveProdServ (no definida en SAT)\"";
                $rowCPS="$claveProdServ desconocida";
            }
        } else {
            $rowCPS="Sin Clave de Producto o Servicio";
        }
        if ($esTraslado) $codeElemScr=" &nbsp; ";
        else {
            $fixedCode=mb_strtoupper(html_entity_decode(mb_strtolower($concepto["codigoArticulo"])));
            $conceptoId=$concepto["id"];
            $changeScriptlet=($soloLectura?" readonly":" removeSpaces=\"1\" onchange=\"agregaValorPost(this);\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"console.log('changeScriptlet');agregaDatoPost('f_articulo[$conceptoId]', '$concepto[codigoArticulo]');this.parentNode.removeChild(this);\""); //  onkeydown=\"return event.which!=32;\" // ToDo: Agregar el jueves que puedo verificar que no causa error
            $codeElemScr="<input type='text' id='articulo[$conceptoId]' name='articulo[$conceptoId]' value='$fixedCode' style='width: 100px;'{$changeScriptlet}>";
        }
?>
        <tr>
          <td<?= $titleUnidad??"" ?> class="lefted"><?= $cantidad ?>&nbsp;<?= $unidad ?></td>
          <td<?= $titleCodigo??"" ?> class="lefted shrinkCol<?= $highClass??"" ?>"><?=$codeElemScr?></td>
          <!--                                                                                                                                                                                                    onkeydown="return preventKeyCodes(event, [32]);" -->
          <td class="lefted"><?= $descripcion ?></td>
          <td class="shrinkCol righted">$<?= number_format($precioUnitario,2) ?></td>
          <td class="shrinkCol righted <?= $claseImporte ?>" title="<?= $tipEval ?>">$<?= number_format($importe,2) ?></td>
        </tr>
        <tr class="satKeys invoice"><td class="lefted brVanish<?=$clsUni?>"><?= isset($claveUnidad[0])?($claveUnidad.(isset($nombreClaveUnidad[0])?" = '$nombreClaveUnidad'":" desconocida")):"Sin Clave Unidad" ?></td><td class="lefted blVanish nopad<?=$clsDsc?>" colspan="4"><div class="padv5 mxHg50 yFlow minScrBar"><?= $rowCPS??"" ?></div></td></tr>
<?php
    }
    $difSubtotal = abs($sumaImportes-$subtotal);
    if ($difSubtotal<$epsilon) {
        $claseSubtotal="bggreen";
        $tipSubtotal="Subtotal correcto";
    } else {
        $claseSubtotal="bgred";
        $tipSubtotal="Subtotal calculado en $".number_format($sumaImportes,2);
    }
    $descuento=$factura["importeDescuento"];
    $trasladado=$factura["impuestoTraslado"];
    $retenido=$factura["impuestoRetenido"];
    $totalCalculado=$subtotal-$descuento+$trasladado-$retenido;
?>
        <tr><td colspan="4" class="righted">Subtotal : </td><td class="<?= $claseSubtotal ?>" title="<?= $tipSubtotal ?>">$<?= number_format($subtotal,2) ?></td></tr>
<?php
    if ($descuento!=0 || $sumaDescuento!=0) {
        $descClase = "";
        if (abs($descuento-$sumaDescuento)<$epsilon) {
            $descClase="bggreen";
            $tipClase="Descuento correcto";
        } else {
            $descClase="bgred";
            $tipClase="Descuento calculado en $".number_format($sumaDescuento,2);
        }
        echo "<tr><td colspan=\"4\" class=\"righted\">Descuentos : </td><td class=\"$descClase\" title=\"$tipClase\">-$".number_format($descuento,2)."</td></tr>";
    }
    if ($trasladado!=0 || $sumaTraslado!=0) {
        $descClase = "";
        if (abs($trasladado-$sumaTraslado)<$epsilon) {
            $descClase="bggreen";
            $tipClase="Impuesto trasladado correcto";
        } else {
            $descClase="bgred";
            $tipClase="Impuesto trasladado calculado en $".number_format($sumaTraslado,2);
        }
        echo "<tr><td colspan=\"4\" class=\"righted\">Impuestos Trasladados : </td><td class=\"$descClase\" title=\"$tipClase\">$".number_format($trasladado,2)."</td></tr>";
    }
    if ($retenido!=0 || $sumaRetenido!=0) {
        $descClase = "";
        if (abs($retenido-$sumaRetenido)<$epsilon) {
            $descClase="bggreen";
            $tipClase="Impuesto retenido correcto";
        } else {
            $descClase="bgred";
            $tipClase="Impuesto retenido calculado en $".number_format($sumaRetenido,2);
        }
        echo "<tr><td colspan=\"4\" class=\"righted\">Impuestos Retenidos : </td><td class=\"$descClase\" title=\"$tipClase\">-$".number_format($retenido,2)."</td></tr>";
    }
    $difTotal = abs($totalCalculado-$total);
    if ($difTotal<$epsilon) {
        $claseTotal="bggreen";
        $tipTotal="Total correcto";
    } else {
        $claseTotal="bgred";
        $tipTotal="Total calculado en $".number_format($totalCalculado,2);
    } ?>
        <tr><td colspan="4" class="righted">Total : </td><td class="<?= $claseTotal ?>" title="<?= $tipTotal ?>">$<?= number_format($total,2) ?></td></tr>
<?php 
}
$puedeRechazar = $modificaProc && !$ctfAuth && $statusn!=null && (
        $statusn==0 ||
        ($esRechazante && $statusn > 0 && $statusn < 32) ||
        ($esAdmin && $statusn > 0 && $statusn < 128)
    );
if ($ctfAuth) {
    global $ctfObj;if(!isset($ctfObj)){require_once "clases/Contrafacturas.php";$ctfObj=new Contrafacturas();}
    $ctfData=$ctfObj->getData("idFactura=$factId",0,"id");//"idContrarrecibo");
    $idCtf=$ctfData[0]["id"]??"";
}
?>
      </tbody></table><br>
<?php if ($esPago) {
    $urlQry=[];
    if (isset($gpoData)) {
        $valEmp=$gpoData["alias"];
        $valEmp=ucfirst(mb_strtolower($valEmp));
        switch ($valEmp) {
            case "Corepack": $valEmp="CorePack"; break;
            case "Marlot": $valEmp="Transportes"; break;
        }
        $urlQry["Emp"]=$valEmp;
        $valEmp="'$valEmp'";
    } else $valEmp="false";
    if (isset($fechaCPago[9])) {
        $valF="'".substr($fechaCPago, 8, 2)."_".substr($fechaCPago, 5, 2)."_".substr($fechaCPago, 0, 4)."'";
    } else $valF="false";
    if (isset($ffolios[0])) {
        $valDesc = "'".implode(",", $ffolios)."'";
    } else $valDesc = "false";
    if (isset($ffolios2[0])) {
        $valDesc .= ",'".implode(",", $ffolios2)."'";
    } else $valDesc .= ",false";
    if (isset($ffolios3[0])) {
        $valDesc .= ",'".implode(",",$ffolios3)."'";
    } else $valDesc .= ",false";
    if (isset($saldoPago)) {
        $valCant = $saldoPago;
        if (isset($pgCurr[0])) {
            $valCant="'".($saldoPago-0.01).";".($saldoPago+0.00).";".($saldoPago+0.01)."'";
            //$valCant="|".($saldoPago-0.01)."|".($saldoPago+0.01);
        }
    } else $valCant = "false";
    ?>
    <h3>Movimientos bancarios relacionados <img src="imagenes/icons/add.png" width="16" onload="setBankData(this,<?=$valEmp?>,<?=$valF?>,<?=$valDesc?>,<?=$valCant?>);" onclick="switchBankData(this);"></h3><iframe id="movimientosbancarios"></iframe><br>
<?php } ?>
    <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fee(lbycn('fixWid'),cl=>{const fxHd=ebyid(cl.getAttribute('fixId'));cl.style.width=fxHd.offsetWidth+'px';});<?= ($puedeRechazar?"addRejectButton($factId);":"").($ctfAuth?"addAuthCFButton($idCtf);":"") ?><?= $esPago?"paymCellSettings();":"" ?>ekil(this);"><!-- LAST CELL verificafactura <?= ($puedeRechazar?"Can":"No")." Reject, ".($modificaProc?"Can":"No")." ModifyProc, ".($ctfAuth?"Can":"No")." CTFAuth, statusn=$statusn" ?> -->
    </td>
  </tr>
</table>
<?php
require_once "configuracion/finalizacion.php";
