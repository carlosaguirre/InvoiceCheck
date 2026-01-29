<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}

$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$modificaProc = modificacionValida("Procesar");
$esRechazante = validaPerfil("Rechaza Aceptadas")||$esSistemas;
$esDesarrollo = in_array(getUser()->nombre, ["admin"]);
$esCompras = validaPerfil("Compras");
$level = 0;
if ($esCompras) $level=1;
if ($modificaProc || $esSistemas) $level=3;
require_once "clases/Facturas.php";
$epsilon = 0.015; //0.000001;
$factId = "".($_REQUEST["facturaId"]??"");
$ctfAuth = ("".($_REQUEST["ctfAuth"]??"0"))==="1";
$soloLectura = isset($_REQUEST["readonly"]);

global $query;
$baseData=["file"=>getShortPath(__FILE__),"usrid"=>getUser()->id,"REQUEST"=>$_REQUEST];
$trace=["00"=>"POST: ".http_build_query($_REQUEST, "", ", ")];
$invObj = new Facturas();
$invObj->rows_per_page=0;
if (isset($factId[0])) $factData = $invObj->getData("id=$factId");
if (!isset($factData[0]["id"])) {
    errlog("Error CFDI no encontrado","error",$baseData+["query"=>$query,"errors"=>$invObj->errors]);
    echo "<H2>Error CFDI no encontrado</H2>";
    die();
}

$factData = $factData[0];
$tc=null;$tcx="COMPROBANTE";$tcd="Comprobante";
$esFactura=false;$esPago=false;$esEgreso=false;$esTraslado=false;
if (!isset($factData["tipoComprobante"][0])) {
    errlog("Error al obtener CFDI sin tipo de comprobante","error",$baseData+["factData"=>$factData]);
    echo "<H2>Error al obtener CFDI sin tipo de comprobante</H2>";
    die();
}

$tc = strtolower($factData["tipoComprobante"][0]);
switch($tc) {
    case "i": $tcx="INGRESO"; $esFactura=true; $tcd="Factura"; break;
    case "e": $tcx="EGRESO";  $esEgreso=true;    $tcd="Nota";    break;
    case "p": $tcx="PAGO";    $esPago=true;    $tcd="Recibo";  break;
    case "t": $tcx="TRASLADO";$esTraslado=true; $tcd="Traslado"; break;
    default: {
        errlog("Tipo de comprobante desconocido","error",$baseData+["tc"=>$tc,"factData"=>$factData]);
        echo "<H2>Tipo de comprobante desconocido</H2>";
        die();
    }
}
$trace["01"]="Tipo de Comprobante : $tc | $tcx | $tcd";

global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj=new Grupo();
}
$gpoData=$gpoObj->getData("rfc='$factData[rfcGrupo]'",0,"alias,razonSocial");
if (!isset($gpoData[0])) {
    errlog("Error al obtener empresa del corporativo","error",$baseData+["query"=>$query,"errors"=>$gpoObj->errors]);
    echo "<H2>Error al obtener empresa del corporativo</H2>";
    die();
}
$gpoData=$gpoData[0];
$trace["02"]="Empresa: $gpoData[alias] = $gpoData[razonSocial] = $factData[rfcGrupo]";

$folio=$factData["folio"]??"";
$uuid=$factData["uuid"]??"";
$statusn=$factData["statusn"]??null;
$estaRechazado=isset($statusn) && $statusn>=128;
$sysPath=$_SERVER["DOCUMENT_ROOT"];
$ubicacion = $factData["ubicacion"];
$nombreXML = $factData["nombreInterno"];
$hasXML = (isset($nombreXML[0])&&file_exists($sysPath.$ubicacion.$nombreXML.".xml"));
$nombrePDF = $factData["nombreInternoPDF"];
$hasPDF = (isset($nombrePDF[0])&&file_exists($sysPath.$ubicacion.$nombrePDF.".pdf"));
$codigoProveedor=$factData["codigoProveedor"];
$GLOBALS["ignoreTmpList"]=["cfdi","dbFieldNames","factData","factList","factura","trace"];

global $prvObj;
if (!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}
$prvData=$prvObj->getData("codigo='$codigoProveedor'",0,"rfc,razonSocial");
if (!isset($prvData[0])) {
    errlog("Error al obtener proveedor","error",$baseData+["query"=>$query,"errors"=>$prvObj->errors]);
    echo "<H2>Error al obtener proveedor</H2>";
    die();
}
$prvData=$prvData[0];
$trace["03"]="Proveedor: $codigoProveedor = $prvData[razonSocial] = $prvData[rfc]";

if ($esPago) {
    $trace["04"]="Verificando Pago";
    $missingFctData = false;
    $factDataFP=$factData["fechaReciboPago"]??"";
    if (isset($factDataFP[0])) $trace["04"].=" | fechaReciboPago=$factDataFP";
    else $missingFctData=true;
    $factDataSP=$factData["saldoReciboPago"]??"";
    if (isset($factDataSP[0])) $trace["04"].=" | saldoReciboPago=$factDataSP";
    else $missingFctData=true;
    global $cpyObj,$pyObj;
    if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
    $cpyData = $cpyObj->getData("idCPago=$factId");
    if (isset($cpyData["id"])) $cpyData=[$cpyData];
    $missingCpyData = !isset($cpyData[0]["id"]);
    if (!$missingCpyData) {
        $fIdList=array_column($cpyData, "idFactura");
        $cpyMap=array_combine($fIdList, $cpyData);
        foreach ($cpyData as $cpyIdx => $cpyElem)
            if (!isset($cpyElem["fechaPago"][0])) {
                $missingCpyData=true;
                break;
            }
    }
    // 36 = 8+1+4+1+4+1+4+1+12 : ([a-f0-9A-F]{8}-[a-f0-9A-F]{4}-[a-f0-9A-F]{4}-[a-f0-9A-F]{4}-[a-f0-9A-F]{12})
    // 16 = 3+1+2+1+9 : ([0-9]{3}-[0-9]{2}-[0-9]{9})

    if ($hasXML && ($missingFctData || $missingCpyData)) {
        require_once "clases/CFDI.php";
        $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
        if (isset($cfdiObj)) {
            $trace["04"].=" | CFDI(id=$factId): $ubicacion{$nombreXML}.xml";
            if ($missingCpyData) $cpyFldArr=[];
            if ($missingFctData) $cpFldArr=["id"=>$factId];
            //cfdi:Comprobante/cfdi:Complemento/pago20:Pagos
                // Totales // @totalretencionesiva, @totalretencionesisr, @totalretencionesieps, @totaltrasladosbaseiva16, @totaltrasladosimpuestoiva16, @totaltrasladosbaseiva8, @totaltrasladosimpuestoiva8, @totaltrasladosbaseiva0, @totaltrasladosimpuestoiva0, @totaltrasladosbaseivaexento, @montototalpagos*
                // Pago[:unbounded]
                    // DoctoRelacionado[:unbounded]
                        // ImpuestosDR[0:]
                            // RetencionesDR[0:] // RetencionDR[:unbounded] // @basedr*, @impuestodr*, @tipofactordr*, @tasaocuotadr*, @importedr*
                            // TrasladosDR[0:] // TrasladoDR[:unbounded] // @basedr*, @impuestodr*, @tipofactordr*, @tasaocuotadr, @importedr
                        // @iddocumento*, @serie, @folio, @monedadr*, @equivalenciadr, @numparcialidad*, @impsaldoant*, @imppagado*, @impsaldoinsoluto*, @objetoimpdr*
                    // ImpuestosP[0:]
                        // RetencionesP[0:] //RetencionP[:unbounded] // @impuestop*, @importep*
                        // TrasladosP[0:] //TrasladoP[:unbounded] // @basep*, @impuestop*, @tipofactorp*, @tasaocuotap, @importep
                    // @fechapago*, @formadepagop*, @monedap*, @tipocambiop, @monto*, @numoperacion, @rfcemisorctaord, @nombancoordext, @ctaordenante, @rfcemisorctaben, @ctabeneficiario, @tipocadpago, @certpago, @cadpago, @sellopago
                // @version*='2.0'

            // CPagos [ id, idCPago, idFactura, numParcialidad, saldoAnterior, impPagado, saldoInsoluto, moneda, equivalencia, idEPago, fechaPago,montoPago,monedaPago,tipocambioPago, modifiedTime ]
            // DPagos [ id, idPPago, idFactura, numParcialidad, saldoAnterior, impPagado, saldoInsoluto, moneda, equivalencia ]
            // Pagos/Pago[:unbounded]
                // DoctoRelacionado[:unbounded]
                    // @iddocumento*, @serie, @folio, @monedadr*, @equivalenciadr, @numparcialidad*, @impsaldoant*, @imppagado*, @impsaldoinsoluto*, @objetoimpdr*
                // @fechapago*, @formadepagop*, @monedap*, @tipocambiop, @monto*, @numoperacion, @rfcemisorctaord, @nombancoordext, @ctaordenante, @rfcemisorctaben, @ctabeneficiario, @tipocadpago, @certpago, @cadpago, @sellopago
            $pagoElems=$cfdiObj->get("pagos");
            // /cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago
            // 
            // todo: ver si pago existe en cpagos por el num de parcialidad, luego ver si la fecha coincide y luego los datos faltantes (checar tabla CPagos y estructura xml)
            if (isset($pagoElems["@fechapago"])) $pagoElems=[$pagoElems];
            $pgKys=[];
            // checar si la fecha de pago esta guardada o requiere correccion ...
            foreach ($pagoElems as $pagoIdx => $pagoElm) { // datos del archivo xml
                $pgElKys=array_keys($pagoElm);
                foreach ($pgElKys as $kyIdx => $kyNm) {
                    if (isset($pgKys[$kyNm])) $pgKys[$kyNm]++;
                    else $pgKys[$kyNm]=1;
                }
                $pagoElmFP=$pagoElm["@fechapago"];
                if (!isset($factDataFP[0]) || strcmp($factDataFP,$pagoElmFP)<0) {
                    $cpFldArr["fechaReciboPago"]=$pagoElmFP;
                }
                $dcsRl=$pagoElm["DoctoRelacionado"];
                if (isset($dcsRl["@iddocumento"])) $dcsRl=[$dcsRl];
                // encontrar elemento en $cpyData relacionado con $pagoElem
                foreach ($dcsRl as $dcrIdx => $dcrElm) {
                    $cpySelIdx=-1;
                    $cpyFld=[];
                    if (isset($cpyData[0])) {
                        foreach ($cpyData as $cpyIdx => $cpyElm) {
                            if ($cpyElm["numParcialidad"]===$dcrElm["@numparcialidad"] && $cpyElm["saldoAnterior"]===$dcrElm["@impsaldoant"] && $cpyElm["impPagado"]===$dcrElm["@imppagado"] && $cpyElm["saldoInsoluto"]===$dcrElm["@impsaldoinsoluto"]) {
                                $cpySelIdx=$cpyIdx;
                                break;
                            }
                        }
                        if (!$estaRechazado) {
                            if ($cpySelIdx<0) {
                                $cpyObj->deleteRecord(["idCPago"=>$factId]);
                                $cpyData=null;
                                foreach ($cpyFldArr as $cfId => $cfVal) if (isset($cfVal["id"])) unset($cfVal["id"]);
                            } else $cpyFld["id"]=$cpySelIdx;
                        }
                    }
                    $cpyFld["idCPago"]=$factId;

                    if ($cpySelIdx<0) {
                        $invData=$invObj->getData("uuid='".strtoupper($dcrElm["@iddocumento"])."'", 0, "id,statusn");
                        if (!isset($invData[0]["id"])) {
                            errlog("No puede registrar un complemento si no se han registrado previamente todas las facturas relacionadas","error",$baseData+["query"=>$query]);
                            echo "<H2>Faltan facturas por facturar</H2>";
                            die();
                        }
                        $cpyFld["idFactura"]=$invData[0]["id"];
                    }
                    $cpyFld["fechaPago"]=$pagoElmFP;
                    $cpyFld["numParcialidad"]=$dcrElm["@numparcialidad"];
                    $cpyFld["saldoAnterior"]=$dcrElm["@impsaldoant"];
                    $cpyFld["impPagado"]=$dcrElm["@imppagado"];
                    $cpyFld["saldoInsoluto"]=$dcrElm["@impsaldoinsoluto"];
                    $cpyFld["moneda"]=$dcrElm["@monedadr"];
                    $cpyFld["equivalencia"]=$dcrElm["@equivalenciadr"]??1;
                }
            }
            
            $trace["04"].=" | PagoKeys=['".implode("','", $pagoKeys)."']";
            if (isset($pagoElems["@fechapago"])) {

            }
            if (!$hasCpyElems || $cpyDoesntHaveFP) {
                $cpyFldArr=[];
            }
            if ($missingFctData) {
                $cpFldArr=["id"=>$factId];

            }
            $detalle="";
            $fechaPagoCP=$cfdiObj->get("pago_fecha");
            $trace["05"]="Has pago_fecha: ";
            if (is_array($fechaPagoCP)) {
                rsort($fechaPagoCP); // ordenar fechas en orden descendente
                $trace["05"].="ARR[ ".implode(" | ", $fechaPagoCP)." ]";
                $fechaPagoCP=$fechaPagoCP[0];
            } else $trace["05"].=$fechaPagoCP;
            $fechaPagoCP=str_replace("T", " ", $fechaPagoCP);
            $montoPagoCP=$cfdiObj->get("pago_monto_total");
            $trace["06"]="Has pago_monto_total: ";
            /*if (is_array($montoPagoCP)) {
                $trace["06"].="SUM[ ".implode(" + ", $montoPagoCP)." ]";
                $sumMontoPagoCP=0;
                foreach ($montoPagoCP as $idxCP => $valCP) {
                    $sumMontoPagoCP+=(+$valCP);
                }
                $montoPagoCP=$sumMontoPagoCP;
                $trace["06"].=" =´$montoPagoCP";
            } else*/
                $trace["06"].=$montoPagoCP;
            if (!isset($factData["fechaReciboPago"][0])) {
                $factData["fechaReciboPago"]=$fechaPagoCP;
                $cpFldArr["fechaReciboPago"]=$fechaPagoCP; // checar formato
                $detalle.="FechaRP=$fechaPagoCP";
            }
            if (!isset($factData["saldoReciboPago"][0])) {
                $factData["saldoReciboPago"]=$montoPagoCP;
                $cpFldArr["saldoReciboPago"]=$montoPagoCP;
                if (isset($detalle[0])) $detalle.=", ";
                $detalle.="MontoRP=$montoPagoCP";
            }
            $saveResult=$invObj->saveRecord($cpFldArr);
            $trace["07"]="SAVE QUERY: $query\nRESULT: ".($saveResult?"TRUE":"FALSE");
            if ($saveResult) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
                $prcObj->alta("FechaMontoPago",$factId,"cpUpgrade",$detalle);
            }
        }
    }
    $dbFieldNames=["id","serie","folio","uuid", // identificadores
        "saldoReciboPago","totalPago",      // Total de pago en CP y Egreso
        "ubicacion","nombreInterno","nombreInternoPDF", // ruta y nombre de archivos
        "ciclo","fechaFactura","fechaCaptura", // fechas
        "fechaReciboPago","fechaPago", // fechas de pago CP y Egreso
        "idReciboPago","referenciaPago","archivoEgreso", // id de CP, No de Egreso y arch Eg.
        "statusn","status" // estado actual del comprobante fiscal
    ];
    if (isset($cpyData[0])) {
        $trace["08"]="SI HAY CPAGOS: ";
        $trace["08"].="\nfIdList = ".json_encode($fIdList);
        $trace["08"].="\ncpyData = ".json_encode($cpyData);
        $trace["08"].="\ncpyMap = ".json_encode($cpyMap);
        $ordlst=$invObj->orderlist;
        $invObj->orderlist = array();
        $invObj->addOrder("folio");
        $factList=$invObj->getData("id in (".implode(",", $fIdList).")",0,$dbFieldNames);
        $invObj->orderlist=$ordlst;
        foreach ($factList as $idx => $cfdi) {
            $fDocId=$cfdi["id"];
            $pyDoc=$cpyMap[$fDocId];
            $debe=+$pyDoc["saldoInsoluto"];
            $idRP=$cfdi["idReciboPago"]??0;
            $slRP=$cfdi["saldoReciboPago"]??0;
            $trIdx="08.$idx";
            $trace[$trIdx]="CP[$factId]F[$fDocId](debe $debe) VS. F-CP[$idRP](debe $slRP)";
            if (!$estaRechazado && $debe<=$slRP && $factId!=$idRP) {
                $result=$invObj->saveRecord(["id"=>$cfdi["id"],"idReciboPago"=>$factId,"saldoReciboPago"=>$debe,"fechaReciboPago"=>$factData["fechaReciboPago"]]);
                $trace[$trIdx].="\nFacturas SAVE QUERY: $query\nRESULT: ";
                if (is_bool($result)) {
                    if ($result) $trace[$trIdx].="OK";
                    else {
                        $trace[$trIdx].="ERROR ".DBi::getErrno()." : ".DBi::getError();
                    }
                } else if (is_scalar($result)) $trace[$trIdx].=$result;
                else if (is_array($result)) $trace[$trIdx].="[".implode(",", $result)."]";
                else if (is_object($result)) $trace[$trIdx].="(".get_class($result).") {".json_encode($result)."}";
                else $trace[$trIdx].="<".gettype($result).">";
            }
            $cpyVal=$cpyMap[$cfdi["id"]]??null;
            if (isset($cpyVal)) $factList[$idx]["cpData"]=$cpyVal;
        }
    } else {
        $trace["08"]="NO HAY CPAGOS";
        $factList=[];
    }
            //$factList = $invObj->getData("idReciboPago=$factId",0,$dbFieldNames);
    $trace["10"]="FACT QUERY: $query\nRESULT IDs: [".implode(", ",array_column($factList, "id"))."]";
    //if (isset($tmpList[1])||isset($tmpList[0][0])) {
    //    for($i=0; isset($tmpList[$i]); $i++) {
            //clog3("TMPLIST $i = '".$tmpList[$i]."' (".strlen($tmpList[$i]).")");
    //        $a = floor($i/8);
    //        $b = $i%8;
    //        if (!isset($factList[$a])) $factList[$a] = [];
    //        $factList[$a][$b]=$tmpList[$i];
    //    }
    if (isset($factList["id"])) $factList = [$factList];
    if (isset($factList[0]["id"])) {
        foreach ($factList as $fctIdx => $facti) {
            if (isset($facti["cpData"]) && !isset($facti["cpData"]["moneda"][0])) {
                $facti_uuid=$facti["uuid"];
                if (!isset($cfdiObj)) {
                    require_once "clases/CFDI.php";
                    $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
                }
                if (isset($cfdiObj)) {
                    $doctos=$cfdiObj->get("pago_doctos");
                    if (isset($doctos["@iddocumento"])) $doctos=[$doctos];
                    foreach($doctos as $dIdx=>$doct) {
                        if (isset($doct["@iddocumento"][0]) && strtoupper($doct["@iddocumento"])===$facti_uuid) {
                            $cpyFldArr=["id"=>$facti["cpData"]["id"],"moneda"=>$doct["@monedadr"]??"","equivalencia"=>$doct["@equivalenciadr"]??1];
                            if (!$estaRechazado && $cpyObj->saveRecord($cpyFldArr)) {
                                $factList[$fctIdx]["cpData"]["moneda"]=$cpyFldArr["moneda"];
                                $factList[$fctIdx]["cpData"]["equivalencia"]=$cpyFldArr["equivalencia"];
                                $trace["11"]="CPagos Moneda Saved";
                            } else {
                                $trace["11"]="Cant Save CPagos Moneda";
                            }
                        }
                    }
                }
            }

        }
        $prvData[2] = $factList;
        $trace["12"]="List Found!";
    } else if ($hasXML&&!isset($cpyData[0])) {
        if (!isset($cfdiObj)) {
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
        }
        if (isset($cfdiObj)) {
            $doctos=$cfdiObj->get("pago_doctos");
            $trace["13"]="DOCTOS: ".json_encode($doctos);
            if (isset($doctos["@iddocumento"])) $doctos=[$doctos];
            $uids=[];
            $udocs=[];
            foreach($doctos as $dIdx=>$doct) {
                if (isset($doct["@iddocumento"][0])) {
                    $uid=strtoupper($doct["@iddocumento"]);
                    $uids[]=$uid;
                    $udocs[$uid]=$doct;
                }
            }
            $trace["14"]="UUIDs: ".implode(", ", $uids);
            if (isset($uids[0][0])) {
                $ordlst=$invObj->orderlist;
                $invObj->orderlist = array();
                $invObj->addOrder("folio");
                $invData=$invObj->getDataByFieldArray(["uuid"=>$uids],0,$dbFieldNames);
                $invObj->orderlist=$ordlst;
                $trace["15"]="DOCTOS QUERY: $query";
                $rpData=[];
                foreach ($invData as $invIdx => $invRow) {
                    $idRP="".($invRow["idReciboPago"]??"");
                    if (!empty($idRP)&&$idRP!=$factId&&!isset($rpData[$idRP])) {
                        $tmpData=$invObj->getData("id=$idRP","id,serie,folio,uuid,saldoReciboPago,fechaReciboPago,ciclo,ubicacion,nombreInterno,nombreInternoPDF,statusn,status");
                        $trace["16.$invIdx"]="INV QUERY: $query";
                        if (isset($tmpData[0]["id"])) $rpData[$idRP]=$tmpData[0];
                        else if (isset($tmpData["id"])) $rpData[$idRP]=$tmpData;
                    }
                    if (isset($rpData[$idRP]) && $rpData[$idRP]["folio"]!=$folio)
                        $invData[$invIdx]["rpData"]=$rpData[$idRP];
                    $pyDoc=$udocs[$invRow["uuid"]];
                    if (!$estaRechazado && (+$pyDoc["@impsaldoinsoluto"])==0&&(empty($idRP)||$idRP!=$factId)) {
                        $pyEqv=$pyDoc["@equivalenciadr"]??1;
                        if (empty($pyEqv)) $pyEqv=1; else $pyEqv=+$pyEqv;
                        // ToDo: Ya no tiene caso conservar estos campos: fechaReciboPago y saldoReciboPago, es preferible consultar CPagos. Buscar donde se ocupan para sustituirlos. Sólo se ocupa para complementos de pago. Ver si mejor se ocupa la tabla CPagos para también guardar el complemento de pago poniendo el mismo dato en idCPago y  idFactura, numParcialidad la última, numParcialidad=null,saldoAnterior=null,importePagado=montoTotalPAgado, saldoInsoluto=null... o empezar a ocupar campo total para guardar montototalpagado, solo hay que asegurarse de validar cada que se obtiene total.
                        $result=$invObj->saveRecord(["id"=>$invRow["id"],"idReciboPago"=>$factId,"fechaReciboPago"=>$factData["fechaReciboPago"],"saldoReciboPago"=>($pyDoc["@imppagado"]/$pyEqv)]);
                        $trIdx="17.$invIdx";
                        $trace[$trIdx]="Facturas SAVE QUERY: $query\nRESULT: ";
                        if (is_bool($result)) {
                            if ($result) $trace[$trIdx].="OK";
                            else {
                                $trace[$trIdx].="ERROR ".DBi::getErrno()." : ".DBi::getError();
                            }
                        } else if (is_scalar($result)) $trace[$trIdx].=$result;
                        else if (is_array($result)) $trace[$trIdx].="[".implode(",", $result)."]";
                        else if (is_object($result)) $trace[$trIdx].="(".get_class($result).") {".json_encode($result)."}";
                        else $trace[$trIdx].="<".gettype($result).">";
                    }
                    $cpyFldArr=["idCPago"=>$factId, "idFactura"=>$invRow["id"]/*,"idEPago"=>$idEPago*/, "numParcialidad"=>$pyDoc["@numparcialidad"]??"", "saldoAnterior"=>$pyDoc["@impsaldoant"]??-1, "impPagado"=>$pyDoc["@imppagado"]??-1, "saldoInsoluto"=>$pyDoc["@impsaldoinsoluto"]??-1, "moneda"=>$pyDoc["@monedadr"]??"", "equivalencia"=>$pyDoc["@equivalenciadr"]??1];
                    if (!$estaRechazado) {
                        $result=$cpyObj->saveRecord($cpyFldArr);
                        $trIdx="18.$invIdx";
                        $trace[$trIdx]="CPagos SAVE QUERY: $query\nRESULT: ";
                        if (is_bool($result)) {
                            if ($result) {
                                $trace[$trIdx].="OK";
                                $invData[$invIdx]["cpData"]=$cpyFldArr;
                            } else if (DBi::getErrno()==0) {
                                $trace[$trIdx].="NO CHANGES";
                                $invData[$invIdx]["cpData"]=$cpyFldArr;
                            } else $trace[$trIdx].="ERROR ".DBi::getErrno()." : ".DBi::getError();
                        } else if (is_scalar($result)) { // lastId
                            $trace[$trIdx].=$result;
                            $cpyFldArr["id"]=$result;
                            $invData[$invIdx]["cpData"]=$cpyFldArr;
    /* CPAGOS: id,idCPago,idFactura,idEPago,numParcialidad,saldoAnterior,impPagado,saldoInsoluto
    */
                        } else if (is_array($result)) $trace[$trIdx].="[".implode(",", $result)."]";
                        else if (is_object($result)) $trace[$trIdx].="(".get_class($result).") {".json_encode($result)."}";
                        else $trace[$trIdx].="<".gettype($result).">";
                    } else $invData[$invIdx]["cpData"]=$cpyFldArr;
                }
                $prvData[2]=$invData;
            } else $prvData[2]=[];
            // toDo: buscar si existen facturas con uuid = cfdi:Complemento|pago10:Pagos|pago10:Pago|pago10:DoctoRelacionado[@iddocumento]
            // toDo: mostrar facturas, validarlas, modificarlas (agregar idReciboPago=$factId) y anexar sus datos en $prvData[2]
            // toDo: poner un indicador de que el recibo de pago y sus facturas fueron modificados, una paloma o algo asi
            // [id,folio,uuid,ubicacion,nombreInternoPDF,idReciboPago,statusn,status,rpData:[folio,uuid,ubicacion]]
        } else $prvData[2]=[];
    } else $prvData[2]=[];
    foreach ($prvData[2] as $iidx => $iitm) {
        if (!isset($pyObj)) { require_once "clases/Pagos.php"; $pyObj=new Pagos(); }
        $pyObj->clearOrder();
        $pyObj->addOrder("fechaPago");
        $pyData=$pyObj->getData("idFactura=$iitm[id]",0);
        if (isset($pyData)) $prvData[2][$iidx]["epData"]=$pyData;
    }
//} else if ($esTraslado) {
//    ;
} else {
    require_once "clases/Conceptos.php";
    $cptObj = new Conceptos();
    $cptObj->rows_per_page=0;
    $prvData[2] = $cptObj->getData("idFactura='$factId'");
}
$hasEA=false;
if(($factData["ea"]??"0")==="1") {
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
    $eafecha=substr(str_replace("-","", $factData["fechaFactura"]),2,6);
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
$esPendiente = isset($statusn) && Facturas::estaPendiente(+$factData["statusn"]);//($factData["status"]==="Pendiente");
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
//clog2("\$factData ".json_encode($factData,JSON_PRETTY_PRINT));
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
if(isset($factData["usoCFDI"][0])) {
    require_once "clases/catalogoSAT.php";
    $usoDesc=CatalogoSAT::getValue(CatalogoSAT::CAT_USOCFDI, "codigo", $factData["usoCFDI"], "descripcion");
    echo "<li>Uso CFDI : <b>$factData[usoCFDI]</b> ($usoDesc)</li>";
}
if (isset($factData["fechaFactura"][0])) {
    $fechaFactura=date("Y-m-d H:i:s",strtotime($factData["fechaFactura"]));
    echo "<li><span id=\"capFecha\">Fecha</span> : <b>$fechaFactura</b></li>";
}
if (isset($factData["fechaReciboPago"][0])) {
    // TODO: Para cada CFDI de Pago, para cada Pago, guardar campo Pagos:Pago:FechaPago
    $fechaCPago=date("Y-m-d",strtotime($factData["fechaReciboPago"]));
    echo "<li><span id=\"capPFecha\"".($highClassP??"").">Fecha Pago</span> : <b>$fechaCPago</b><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const cf=ebyid('capFecha');cladd(cf, 'inblock');cf.style.width=ebyid('capPFecha').offsetWidth+'px';ekil(this);\"></li>";
} else $fechaCPago="";
/* <!-- li>Certificado : <b> $factData$factData["noCertificado"] </b></li -->
<!-- li>Version : <b> $factData["version"] </b></li --> */
if (isset($factData["metodoDePago"][0])) {
    $metodoPago = $factData["metodoDePago"];
    require_once "clases/catalogoSAT.php";
    $metodoDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_METODOPAGO, "codigo", $metodoPago, "descripcion");
    echo "<li>M&eacute;todo de Pago : <b>$metodoPago</b> ($metodoDesc)</li>";
}
if (isset($factData["formaDePago"][0])) {
    $formaPago = $factData["formaDePago"];
    require_once "clases/catalogoSAT.php";
    $formaDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_FORMAPAGO, "codigo", $formaPago, "descripcion");
    echo "<li>Forma de Pago : <b>$formaPago</b> ($formaDesc)</li>";
}
if (isset($factData["serie"][0]))
    echo "<li>Serie : <b>$factData[serie]</b></li>";
if (isset($folio[0]))
    echo "<li><span".($highClassP??"").">Folio</span> : <b>$folio</b></li>";
if ($esPago && isset($factData["saldoReciboPago"][0])) {
    $saldoPago = $factData["saldoReciboPago"];
    echo "<li><span".($highClassP??"").">Monto pagado</span> : <b>$".number_format($saldoPago,2)."</b></li>";
}
$realStatus=Facturas::statusnToRealStatus($statusn,$tc,$level);
$sttttl=$esAdmin?" title='STT:$statusn, TC:$tc, LV:$level'":"";
echo "<li{$sttttl}>Status : <b>$realStatus</b></li>";
?>
      <li class="<?= (strpos($factData["mensajeCFDI"],"satisfactoriamente")===FALSE||$factData["estadoCFDI"]!=="Vigente")?"bgred":"bggreen" ?>">CFDI: <b><?= $factData["mensajeCFDI"] ?><br><?= $factData["estadoCFDI"] ?></b></li>
<?php
if (!$esPago && !$esTraslado) { ?>
      <li><span class="vAlignCenter nowrap<?=$highClass??""?>">Num. de Pedido : <input type="text" name="numpedido" id="numpedido" class="widavailable" value="<?= $factData["pedido"] ?>" <?=$soloLectura?"readonly":"onchange=\"agregaValorPost(this);\"><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"agregaDatoPost('f_numpedido', '$factData[pedido]');agregaDatoPost('fold_numpedido', '$factData[pedido]');this.parentNode.removeChild(this);\"" ?>></span></li>
<?php
} ?>
    </ul></td>
<?php
if ($esPago) { ?>
    <td id="paymDocsSection"><h3>Documentos relacionados</h3><table border="1" id="table_of_invoices">
      <thead><tr class="nowrap centered">
      <th style="width: 112px;"><img src="imagenes/icons/prev01_20b.png" onclick="switchHeadCell(this);"><span idx="0" arr="id" class="paymFixableCaption inblock">Folio</span><img src="imagenes/icons/next01_20b.png" onclick="switchHeadCell(this);"></th><th><img src="imagenes/icons/prev01_20b.png" onclick="switchHeadCell(this);"><span idx="0" arr="fecha" class="paymFixableCaption inblock">Parcialidad</span><img src="imagenes/icons/next01_20b.png" onclick="switchHeadCell(this);"></th><th style="width: 86px;">Documentos</th>
<?php
//} else if ($esTraslado) {
    // Header a mostrar de traslados Carta Porte
} else { ?>
    <td id="conceptsSection"><h3 class="wide">Documentos relacionados</h3><table border="1" id="table_of_concepts">
      <thead><tr class="nowrap centered">
      <th>Cantidad</th><th id="headCode">C&oacute;digo</th><th>Descripci&oacute;n</th><th>P.Unit.</th><th>Importe</th>
<?php
} ?>
      </tr></thead>
      <tbody>
<?php
if (isset($trace)) {
    foreach ($trace as $key => $value) {
        echo "<!-- '$key' => $value -->\n";
    }
}
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
        echo "<tr><td colspan=\"3\">NO SE IDENTIFICARON FACTURAS RELACIONADAS</td></tr>"; // toDo: cambiar por una imagen de tache rojo
    } else foreach ($prvData[2] as $pidx=>$pago) {
        if (is_array($pago) && isset($pago["id"])) { //  && isset($pago["idReciboPago"])
// [id,folio,uuid,ubicacion,nombreInternoPDF,idReciboPago,statusn,status,rpData:[folio,uuid,ubicacion,nombreInternoPDF,statusn,status]]
            $pgub=$pago["ubicacion"]??"";
            $pguuid=$pago["uuid"]??"";
            $pgfolio=$pago["folio"]??"";
            $pgReciboId=$pago["idReciboPago"]??"";
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
            $pgId="<span class=\"swhid swhidfolio\">$pgfolio</span><span class=\"swhid swhiduuid asLinkH hidden\"$pguuidAtts>$pguuidShort</span>"; //  // crea alta pago egreso
            $pgParcialidad="";
            // CPAGOS: id, idCPago, idFactura, idEPago, numParcialidad, saldoAnterior, impPagado, saldoInsoluto
            if (isset($pago["cpData"])) {
                $ppd=$pago["cpData"];
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
                if ($ppd["saldoInsoluto"]>0)
                    $insoluto="<span class=\"cap\">Debe:</span><span class=\"curr\">&dollar;".number_format($ppd["saldoInsoluto"],2).($pgCurr??"")."</span>";
                else $insoluto="Liquidado";
                $pgParcialidad="<span class=\"swhpNum\">$numPar</span><span class=\"swhpPym\" title=\"Importe Pagado\"><span class=\"cap\">Pago:</span><span class=\"curr\"$pgCurrAtt>$pagado</span></span><span class=\"swhpIns\" title=\"Saldo Insoluto\">$insoluto</span>";
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
            if (isset($fechaEPago[0])) {
                // id, archivo, codigoProveedor, idFactura, fechaPago, cantidad, iva, total, tipo, referencia, valido, modifiedTime
                $hep="";
                if (isset($pago["epData"])) foreach ($pago["epData"] as $epId => $epRow) {
                    $fep=substr($epRow["fechaPago"], 0, 10);
                    //if ($fep)
                }
                $horaEPago=isset($fechaEPago[10])?substr($fechaEPago, 11, 8):"";
                if (isset($horaEPago[0])) $horaEPago=" title=\"$horaEPago\"";
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
            clog3("PAGO INCOMPLETO: ".json_encode($pago));
?>
        <tr><td colspan="3">Documento Relacionado incompleto</td></tr>
<?php
        }
    }
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"adjustPymWidths();this.parentNode.removeChild(this);\">";
//} else if ($esTraslado) {
    // Datos a mostrar de traslados Carta Porte
} else {
    $subtotal = $factData["subtotal"];
    $total = $factData["total"];
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
    $descuento=$factData["importeDescuento"];
    $trasladado=$factData["impuestoTraslado"];
    $retenido=$factData["impuestoRetenido"];
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
    <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="fee(lbycn('fixWid'),cl=>{const fxHd=ebyid(cl.getAttribute('fixId'));cl.style.width=fxHd.offsetWidth+'px';});<?= ($puedeRechazar?"addRejectButton($factId);":"").($ctfAuth?"addAuthCFButton($idCtf);":"") ?><?= $esPago?"paymCellSettings();":"" ?>ekil(this);">
    </td>
  </tr>
</table>
<?php
require_once "configuracion/finalizacion.php";
