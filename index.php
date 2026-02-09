<?php
require_once "bootstrap.php";
$menu_accion = "";
//echo "<!-- INDEX BEGIN -->\n";
if ($hasUser) {
    //echo "<!-- HAS USER : TEST SET | NEW KEY SET -->\n";
    if ($_esPruebas) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    if (!$habilitado || $modoActualizacion || ($modoPruebas && !$_esPruebas)) $menu_accion="Actualizacion";
    else if($user->cambiaClave) $menu_accion="CambiaClave";
}
if (!isset($menu_accion[0])) {
    //echo "<!-- NO MENU ACCION. GETTING IT -->\n";
    if (isset($_POST["menu_accion"][0])) {
        $menu_accion = $_POST["menu_accion"];
        if ($hasUser) {
            //echo "<!-- MENU ACCION : POST & USER -->\n";
            setcookie("menu_accion", $menu_accion, 0, "/invoice");
            doclog("POST: $menu_accion","action");
        } else {
            //echo "<!-- MENU ACCION : POST & NOT USER -->\n";
            $_SESSION["lastMenuAction"]=$menu_accion;
            doclog("POST noUser (last=$menu_accion)");
            $menu_accion="";
        }
    } else if (isset($_GET["menu_accion"][0])) {
        $menu_accion = $_GET["menu_accion"];
        if ($hasUser) {
            //echo "<!-- GET & USER -->\n";
            setcookie("menu_accion", $menu_accion, 0, "/invoice");
        } //else echo "<!-- GET & NOT USER -->\n";
        doclog("GET: $menu_accion","action");
    } else if(isset($_REQUEST["menu_accion"][0])) {
        $menu_accion = $_REQUEST["menu_accion"];
        if ($hasUser) {
            //echo "<!-- REQUEST & USER -->\n";
            setcookie("menu_accion",$menu_accion, 0, "/invoice");
        } //else echo "<!-- REQUEST & NOT USER -->\n";
        doclog("REQUEST: $menu_accion","action");
    } else if ($hasUser && isset($_SESSION["lastMenuAction"][0])) {
        //echo "<!-- USER && SESSION[lastMenuAction] -->\n";
        $menu_accion=$_SESSION["lastMenuAction"];
        unset($_SESSION["lastMenuAction"]);
        doclog("SESSION lastMenuAction: $menu_accion","action");
    } else if ($hasUser && isset($_COOKIE["menu_accion"])) {
        //echo "<!-- USER && COOKIE[menu_accion] -->\n";
        $menu_accion = $_COOKIE["menu_accion"];
        doclog("COOKIE: $menu_accion","action");
    } else {
        //echo "<!-- OTHER, NO MENU ACCION -->\n";
        try {
            if (!isset($menu_accion)) {
                //echo "<!-- no menu accion -->\n";
                doclog("OTHER: no menu action","action");
            } else if (!isset($menu_accion[0])) {
                //echo "<!-- empty menu accion -->\n";
                doclog("OTHER: empty menu action","action");
            } else {
                //echo "<!-- other menu accion -->\n";
                doclog("OTHER: no menu action: '$menu_accion'","action");
            }
        } catch (Exception $e) {
            echo "<!-- DocLog Exception: ".json_encode(getErrorData($e))." -->\n";
        }
    }
    //echo "<!-- SESSION[tmp] -->\n";
    if (isset($_SESSION["tmp"])) {
        if ($_SESSION["tmp"]==="loggedin") {
            //echo "<!-- SESSION[tmp]=loggedin -->\n";
            if(($user->banderas & 2)>0) { // Tiene bandera 2
                //echo "<!-- USER && BANDERA 2 -->\n";
                $resultTitle="Solicitud de Pago";
                $resultMessage="<P class='fontRelevant margin20 centered'>Se ha generado un pendiente en <b class=\"alink\" onclick=\"autoSubmit({menu_accion:'ListaSolPago',target:'_self'});\">Solicitud de Pago</b></P>";
                if (!isset($usrObj)) {
                    require_once "clases/Usuarios.php";
                    $usrObj=new Usuarios();
                }
                $usrObj->saveRecord(["id"=>$userid,"banderas"=>new DBExpression("banderas&253")]); // Borrar bandera 2
            }
        }
        unset($_SESSION["tmp"]);
    }
    //echo "<!-- INV INIT  -->\n";
    if ((($_esSistemas??false)||($_esAdministrador??false)) && (!isset($_POST["menu_accion"])||$_POST["menu_accion"]==="Inicio")) {
        global $invObj, $query;
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        }
        $invObj->rows_per_page=100;
        $invObj->clearOrder();
        $invObj->addOrder("version","desc");
        $invObj->addOrder("ciclo","desc");
        $invObj->addOrder("right(ubicacion,3)","desc");
        $invObj->addOrder("ubicacion");
    }
    if (isset($user->proveedor) && !isset($_REQUEST["logout"])) {
        if ($user->proveedor->status==="inactivo") $menu_accion="Inactivo";
        else if (!isset($_POST["menu_accion"])||$_POST["menu_accion"]==="Inicio"||$_POST["menu_accion"]==="ActualizaCuentaBancaria") {
            global $invObj, $query;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            // Actualizar pagos en tablas CPagos y DPagos
            // Si el status es aceptado
            $invObj->rows_per_page=200;
            $invObj->clearOrder();
            $invObj->addOrder("version","desc");
            $invObj->addOrder("year(fechaFactura)","desc");
            $invObj->addOrder("right(ubicacion,3)","desc");
            $invObj->addOrder("folio","desc");
            $invObj->addOrder("ubicacion");
            //$invObj->addOrder("d.numParcialidad","desc");
            /*$noCCPData=$invObj->getData("f.codigoProveedor=\"".$user->proveedor->codigo."\" and f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and (f.idReciboPago is null or f.statusReciboPago is null or c.idCPago is null or f.statusReciboPago<1 or f.saldoReciboPago>0) and f.statusn between 32 and 127 and (p.statusn is null or p.statusn<127) and (g.statusn is null or g.statusn<127) and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\")", 0,
                "f.id,f.ubicacion,f.uuid,f.serie,f.folio,f.fechaFactura,f.total,f.moneda,f.nombreInterno,f.nombreInternoPDF,f.ea,f.version,f.idReciboPago,f.statusReciboPago,f.saldoReciboPago,f.statusn,c.idCPago,c.fechaPago,d.numParcialidad,d.saldoInsoluto,p.statusn cpStatusN,g.statusn rpStatusN,p.folio cpFolio,p.nombreInterno cpNombreInterno,p.nombreInternoPDF cpNombreInternoPDF,p.ubicacion cpUbicacion",
                "f left join dpagos d on f.id=d.idFactura left join cpagos c on d.idppago=c.id left join facturas p on c.idCPago=p.id left join facturas g on f.idReciboPago=g.id", 1000);
            */
            $noCCPData=$invObj->getDataFromTemp("f.codigoProveedor=\"".$user->proveedor->codigo."\" and f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and f.statusn between 32 and 127 and (g.id is null or g.statusn between 1 and 127) and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\")", 
                "f.id, f.ubicacion, f.uuid, f.serie, f.folio, f.tipoComprobante, f.fechaFactura, f.total, f.moneda, f.ea, f.version, f.nombreInterno, f.nombreInternoPDF, f.statusn, f.idReciboPago, f.fechaReciboPago, f.saldoReciboPago, f.statusReciboPago, x.saldoInsoluto, x.numParcialidads, x.saldoAnteriors, x.impPagados, x.idCPagos, x.fechaPagos, g.ubicacion rpUbicacion, g.folio rpFolio, g.tipoComprobante rpTipoComprobante, g.nombreInterno rpNombreInterno, g.nombreInternoPDF rpNombreInternoPDF, g.statusn rpStatusN, g.idReciboPago rpIdReciboPago, g.fechaReciboPago rpFechaReciboPago, g.saldoReciboPago rpSaldoReciboPago, g.statusReciboPago rpStatusReciboPago",
                "f LEFT JOIN facturas g ON f.idReciboPago=g.id LEFT JOIN (SELECT d.idFactura, d.saldoInsoluto, group_concat(d.numParcialidad order by d.idFactura desc, c.idCPago desc) numParcialidads, group_concat(d.saldoAnterior order by d.idFactura desc, c.idCPago desc) saldoAnteriors, group_concat(d.impPagado order by d.idFactura desc, c.idCPago desc) impPagados, group_concat(c.idCPago order by d.idFactura desc, c.idCPago desc) idCPagos, group_concat(date(c.fechaPago) order by d.idFactura desc, c.idCPago desc) fechaPagos FROM dpagos d INNER JOIN cpagos c on d.idPPago=c.id INNER JOIN facturas h on c.idCPago=h.id INNER JOIN (SELECT e.idFactura, min(e.saldoInsoluto) minSaldo FROM dpagos e GROUP BY e.idFactura) b ON d.idFactura=b.idFactura and d.saldoInsoluto=b.minSaldo WHERE h.statusn between 1 AND 127 GROUP BY d.idFactura, d.saldoInsoluto ORDER BY d.idFactura) x on f.id=x.idFactura",
                "f.id", 
                "f.idReciboPago is null or f.statusReciboPago is null or x.idCPagos is null or x.idCPagos like \"%,%\" or f.statusReciboPago<1 or f.saldoReciboPago>0");
// group_concat([<c|d>pagoField] order by d.saldoInsoluto asc) => group_concat([<c|d>pagoField] order by d.idFactura desc, c.idCPago desc)
            //echo "<!-- $query -->\n";
            $ttn=$invObj->numrows;
            $d2d=0; // discarded to display
            $cnt=count($noCCPData);
            // Si son 2 o más:
            if (isset($noCCPData[0])) {
                $noCCPTable="<!-- TOTNUM: $ttn, COUNT: $cnt, QRY-BLK: $query --><table class=\"pad2c screenBG centered\"><thead><tr class=\"bbtmdblu\"><th>#</th><th>FECHA</th><th>EMPRESA</th><th>FOLIO</th><TH>STATUS</TH><th>DOCS</th></tr></thead><tbody>";
                $eacp=trim(str_replace("-","",$user->proveedor->codigo));
                $nln=0;
                $fxRP=[]; $fxI=[];
                $frcFx=false;
                foreach ($noCCPData as $idx => $crow) {
                    $idFactura="".$crow["id"]??"";
                    if (isset($fxI[$idFactura])) {
                        $d2d++;
                        doclog("Index noCCPData","pagosDup",["usuario"=>$username,"codigo"=>$user->proveedor->codigo,"idx"=>$idx,"idFactura"=>$idFactura,"invNoCPData"=>$crow,"saved"=>$fxI[$idFactura]]);
                        continue;
                    }
                    $pfolio=$crow["folio"]??"";
                    $idReciboPago="".$crow["idReciboPago"]??"";
                    $hasRP=isset($idReciboPago[0]);
                    $saldoReciboPago=$crow["saldoReciboPago"]??"";
                    $statusReciboPago="".$crow["statusReciboPago"]??"";
                    $saldoInsoluto=$crow["saldoInsoluto"];
                    if (isset($saldoInsoluto[0])) $saldoInsoluto=+$saldoInsoluto;
                    $cpFechaPago=$crow["fechaPagos"];
                    $tieneComaFP=isset($cpFechaPago[0])?strpos($cpFechaPago, ","):false;
                    if ($tieneComaFP!==false) $cpFechaPago=substr($cpFechaPago, 0, $tieneComaFP);
                    $idCPago=$crow["idCPagos"]??"";
                    $hasCP=isset($idCPago[0]);
                    $tieneComaIP=isset($idCPago[0])?strpos($idCPago, ","):false;
                    if ($tieneComaIP!==false) $idCPago=substr($idCPago, 0, $tieneComaIP);
                    if (($hasRP&&!$hasCP)||($hasCP&&!$hasRP)||$tieneComaFP!==false||$tieneComaIP!==false) doclog("CORREGIR".($hasRP?"":" sin idReciboPago").($hasCP?"":"sin idCPagos").(($tieneComaFP==false&&$tieneComaIP==false)?"":" pago ambiguo"),"recibo",$crow);
                    $cpTest="";
                    if (isset($idCPago[0])) {
                        $cpStatusN=$crow["rpStatusN"]??"";
                        $cpTest="1".(isset($cpStatusN[0])?"1".($cpStatusN&1):"00");
                        $cpStatusN=(isset($cpStatusN[0])?+$cpStatusN:-1);
                        if ($cpStatusN>=Facturas::STATUS_RECHAZADO) $fixSttRP=-1;
                        else if ($cpStatusN&1==0) $fixSttRP=0;
                        else $fixSttRP=1;
                    } else {
                        $cpTest="000";
                        $cpStatusN="*";
                        if (isset($statusReciboPago[0]))
                            $fixSttRP=+$statusReciboPago;
                        else $fixSttRP="";
                    }
                    if (!isset($idReciboPago[0])) {
                        $sttRP="FALTA";
                        if (isset($idCPago[0])) {
                            if ($fixSttRP<0) $sttRP="RECHAZADO";
                            else if ($fixSttRP==0) $sttRP="SIN VERIFICAR";
                            else if ($saldoInsoluto>=1) $sttRP="DEBE";
                            else $sttRP="LIQUIDADO";
                            $fxArrFld=["id"=>$idFactura,"idReciboPago"=>$idCPago,"fechaReciboPago"=>$cpFechaPago,"saldoReciboPago"=>$saldoInsoluto,"statusReciboPago"=>$fixSttRP];
                            if ($invObj->saveRecord($fxArrFld)) {
                                $idReciboPago=$idCPago;
                                $statusReciboPago=$fixSttRP;
                                $saldoReciboPago=$saldoInsoluto;
                                if ($statusReciboPago>=0) $fxI[$idFactura]=$fxArrFld;
                                if ($sttRP==="LIQUIDADO") {
                                    $d2d++;
                                    doclog("Index noCCPData FIXED 1","pagos",["codigo"=>$user->proveedor->codigo,"idx"=>$idx,"idFactura"=>$idFactura,"invNoCPData"=>$crow,"saved"=>$fxArrFld]);
                                    continue;
                                }
                            } else if (empty(DBi::getErrno()) && $sttRP==="LIQUIDADO") {
                                continue;
                            } else {
                                doclog("Index noCCPData FAIL SAVE 1","pagosErr",["codigo"=>$user->proveedor->codigo,"idFactura"=>$idFactura,"preSaved"=>($fxI[$idFactura]??""),"invNoCPData"=>$crow,"query"=>$query,"errcode"=>DBi::getErrno(),"errmsg"=>DBi::getError(),"dbiErrors"=>DBi::$errors,"invErrors"=>$invObj->errors]);
                            }
                        }
                    } else if ($idReciboPago==$idCPago&&(empty($statusReciboPago)&&$fixSttRP==1)) {
                        if ($saldoInsoluto>=1) $sttRP="DEBE";
                        else $sttRP="LIQUIDADO";
                        $fxArrFld=["id"=>$idFactura,"fechaReciboPago"=>$cpFechaPago,"saldoReciboPago"=>$saldoInsoluto,"statusReciboPago"=>$fixSttRP];
                        if ($invObj->saveRecord($fxArrFld)) {
                            $statusReciboPago=$fixSttRP;
                            $saldoReciboPago=$saldoInsoluto;
                            $fxI[$idFactura]=$fxArrFld;
                            if ($sttRP==="LIQUIDADO") {
                                $d2d++;
                                doclog("Index noCCPData FIXED 2","pagos",["codigo"=>$user->proveedor->codigo,"idx"=>$idx,"idFactura"=>$idFactura,"invNoCPData"=>$crow,"saved"=>$fxArrFld]);
                                continue;
                            }
                        } else if (empty(DBi::getErrno()) && $sttRP==="LIQUIDADO") {
                            continue;
                        } else doclog("Index noCCPData FAIL SAVE 2","pagosErr",["codigo"=>$user->proveedor->codigo,"idFactura"=>$idFactura,"preSaved"=>$fxI[$idFactura],"foundInvNoCPData"=>$crow,"query"=>$query,"errcode"=>DBi::getErrno(),"errmsg"=>DBi::getError(),"dbiErrors"=>DBi::$errors,"invErrors"=>$invObj->errors]);
                    } else if ($idReciboPago!=$idCPago) {
                        /*if ($fixSttRP<0) $sttRP="RECHAZADO";
                        else if ($fixSttRP==0) $sttRP="SIN VERIFICAR";
                        else if ($saldoInsoluto>0) $sttRP="DEBE";
                        else $sttRP="LIQUIDADO";*/
                        $sttRP="REVISION";
                    } else {
                        if ($fixSttRP==="") $sttRP="";
                        else if ($fixSttRP<0) $sttRP=" RECHAZADO ";
                        else if ($fixSttRP==0) $sttRP=" SIN VERIFICAR ";
                        else if ($saldoInsoluto>=1) $sttRP=" DEBE: $".number_format($saldoInsoluto,2);
                        else {
                            $sttRP=" LIQUIDADO ";
                            continue;
                        }
                        $sttRP.="<!-- $idx -->";
                    }

                    $nln++;
                    if ($nln>100) break;
                    $num=$nln;
                    $fechahora=$crow["fechaFactura"];
                    $fecha=substr($fechahora, 0, 10);
                    $eafecha=substr(str_replace("-","",$fecha),2);
                    $eafolio=$crow["folio"]??"";
                    if (!isset($eafolio[0])) {
                        $eafolio=substr($crow["uuid"],-10);
                        $folio="[".$eafolio."]";
                    } else if (isset($eafolio[10])) {
                        $folio="...".substr($eafolio,-10);
                    } else $folio=$eafolio;
                    $ubicacion=$crow["ubicacion"]??"";
                    $alias=substr($ubicacion, 9, -9);
                    $noCCPTable.="<tr class=\"bbtm1d\"><td idx=\"$idx\">$num</td><td title=\"$fechahora\">$fecha</td><td>$alias</td><td>$folio</td><td>$sttRP</td><td>";

                    $xml=$crow["nombreInterno"]??"";
                    $pdf=$crow["nombreInternoPDF"]??"";
                    $cpPath=$crow["rpUbicacion"]??"";
                    $cpFol=$crow["rpFolio"]??"";
                    $cpXml=$crow["rpNombreInterno"]??"";
                    $cpPdf=$crow["rpNombreInternoPDF"]??"";
                    $comentario="";
                    if (isset($xml[0])) $noCCPTable.="<A href=\"$ubicacion{$xml}.xml\" target=\"archivo\" cfdi=\"$idFactura\"><IMG src=\"imagenes/icons/xml200.png\" width=\"20\" height=\"20\"></A>";
                    if (isset($pdf[0])) $noCCPTable.="<A href=\"$ubicacion{$pdf}.pdf\" target=\"archivo\"><IMG src=\"imagenes/icons/pdf200.png\" width=\"20\" height=\"20\"></A>";
                    if ($crow["ea"]==="1") $noCCPTable.="<A href=\"{$ubicacion}EA_{$eacp}_{$eafolio}_{$eafecha}.pdf\" target=\"archivo\"><IMG src=\"imagenes/icons/pdf200EA.png\" width=\"20\" height=\"20\"></A>";
                    if (!empty($idReciboPago)) {
                        if (isset($cpXml[0]))
                            $noCCPTable.="<A href=\"$cpPath{$cpXml}.xml\" target=\"archivo\" cfdi=\"$idReciboPago\"".($idReciboPago!=$idCPago?" cpago=\"$idCPago\"":"")." title=\"Complemento de Pago $cpFol\"><IMG src=\"imagenes/icons/xml200P.png\" width=\"20\" height=\"20\"></A>";
                        if (isset($cpPdf[0]))
                            $noCCPTable.="<A href=\"$cpPath{$cpPdf}.pdf\" target=\"archivo\" title=\"Complemento de Pago $cpFol\"><IMG src=\"imagenes/icons/pdf200P.png\" width=\"20\" height=\"20\"></A>";
                    }
                    $noCCPTable.="</td><tr>";
                }
                $dfn=$ttn-$d2d-$nln;
                if ($dfn>0) $noCCPTable.="<tr><th colspan=\"5\">.../+{$dfn}</th></tr>";
                $noCCPTable.="</tbody></table><br>";
            }
            $prefixOnce=isset($errorMessage[0])?"":"<P class='fontRelevant margin20 centered'>Estimado Proveedor:</P>$lstPfx";
            if (isset($mensajeImportante[0])) {
                $errorMessage.=$prefixOnce.$mensajeImportante;
                $prefixOnce="";
            }
            if ($user->proveedor->status==="actualizar") {
                $menu_accion="ActualizaCuentaBancaria";
                $errorTitle="Actualizar Datos";
                $errorMessage.=$prefixOnce.MENSAJE_ACTUALIZAR1;
                $prefixOnce="";
            } else if ($user->proveedor->verificado<0) {
                $menu_accion="ActualizaCuentaBancaria";
                $errorTitle="Actualizar Datos";
                $errorMessage.=$prefixOnce.MENSAJE_ACTUALIZAR2;
                $prefixOnce="";
            } else if ($user->proveedor->cumplido<0) {
                $menu_accion="ActualizaCuentaBancaria";
                if ($user->proveedor->cumplido<-1) {
                    $errorTitle="Actualizar Datos";
                    $errorMessage.=$prefixOnce.MENSAJE_ACTUALIZAR3;
                    $prefixOnce="";
                } else if (empty($user->proveedor->opinion)) {
                    $errorTitle="Actualizar Datos";
                    $errorMessage.=$prefixOnce.MENSAJE_ACTUALIZAR4;
                    $prefixOnce="";
                } else {
                    $errorTitle="Actualizar Datos";
                    $errorMessage.=$prefixOnce.MENSAJE_ACTUALIZAR5;
                    $prefixOnce="";
                }
            }
            if (isset($noCCPData[1])) { // separado para mantener cambio en menu_accion
                // toDo: modificar para integrar pero respetando los cambios para actualizar datos
                $errorTitle="Entregar Complementos";
                $errorMessage.=$prefixOnce.MENSAJE_COMPLEMENTOS.$noCCPTable;
                $prefixOnce="";
            }

            if ($user->proveedor->status==="bloqueado"/* && isset($_SESSION['MENSAJE_INICIAL'])*/) {
                $errorTitle="Pagos Bloqueados";
                $noticia="Sus pagos se encuentran bloqueados porque no ha dado de alta sus Recibos de Pago.<BR>Favor de darlos de alta en este portal para restaurar sus pagos.";
                $errorMessage.=$prefixOnce."<P class='fontRelevant margin20 centered'>$noticia</P>";
                $_SESSION["MENSAJE_NOTICIA"]="<p class='fontRelevant margin20 centered'>Estimado proveedor:<BR><BR>$noticia</P><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"this.parentNode.style='display: inline-block;width: 100%;text-align: center;'; this.parentNode.removeChild(this);\">";
                unset($_SESSION['MENSAJE_INICIAL']);
            }

            /* * *
            $noticia=[];
            if ($user->proveedor->status==="bloqueado" && isset($_SESSION['MENSAJE_INICIAL'])) {
                $noticia[]="Sus pagos se encuentran bloqueados porque no ha dado de alta sus Recibos de Pago.<BR>Favor de darlos de alta en este portal para restaurar sus pagos.";
            }
            if (isset(ALARMA["PROVEEDOR"][0])) { // SIEMPRE
                $noticia[]=ALARMA["PROVEEDOR"];
            }
            if (isset(AVISO["PROVEEDOR"][0])) { // TRES VECES
                $hasFirstWarning=($user->banderas & 8)>0;
                $hasSecondWarning=($user->banderas & 16)>0;
                $hasThirdWarning=($user->banderas & 32)>0;
                if (!$hasThirdWarning && !$hasSecondWarning && !$hasFirstWarning) {
                    $noticia[]=AVISO["PROVEEDOR"];
                    if (!isset($usrObj)) {
                        require_once "clases/Usuarios.php";
                        $usrObj=new Usuarios();
                    }
                    $usrObj->saveRecord(["id"=>$userid,"banderas"=>new DBExpression("banderas&253")]);
                }
            }
            if (isset(MENSAJE["PROVEEDOR"][0])) { // UNA VEZ
                $hasBeenNoticed=($user->banderas & 4)>0;
                if (!$hasBeenNoticed) {
                    $noticia[]=MENSAJE["PROVEEDOR"];
                    if (!isset($usrObj)) {
                        require_once "clases/Usuarios.php";
                        $usrObj=new Usuarios();
                    }
                    $usrObj->saveRecord(["id"=>$userid,"banderas"=>new DBExpression("banderas&253")]);
                }
            }
            if (isset($noticia[0])) {
                if(isset($errorMessage[0]))
                    $errorMessage.="<P class='fontRelevant margin20 centered'>$noticia</P>";
                else {
                    $errorMessage.="<p class='fontRelevant margin20 centered'>Estimado proveedor:<BR><BR>$noticia</p>";
                    $errorTitle="Pagos Bloqueados";
                }
                $_SESSION["MENSAJE_NOTICIA"]="<p class='fontRelevant margin20 centered'>Estimado proveedor:<BR><BR>$noticia</P><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"this.parentNode.style='display: inline-block;width: 100%;text-align: center;'; this.parentNode.removeChild(this);\">";
                unset($_SESSION['MENSAJE_INICIAL']);
            }
            * * */
        }
    }
}
if ($username==="viajero"&&(!isset($menu_accion[0])||$menu_accion==="Inicio"))
    $menu_accion="Viajero";

$hasStyleAction = false;
$otherStyleHref=[];
$hasScriptAction = false;
$otherScriptSrc=[];
$hasConfigAction = false;
    
doclog("PRESWITCH","action",["menu_action"=>$menu_accion,"urlAction"=>($urlAction??"null"),"post"=>$_POST]);
switch ($menu_accion) {
    case "Actualizacion":
        $urlAction = "actualizacion";
        break;
    case "ActualizaCuentaBancaria":
        $urlAction = "faltabanco";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Admin Factura":
        $urlAction = "adminfactura";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Administracion":
        $urlAction = "admon";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Alta Facturas":
        $urlAction = "altafactura";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Alta Facturas 0":
        $urlAction = "altafactura0";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Alta Pagos":
        $urlAction = "altapagos";
        $hasScriptAction = true;
        break;
    case "Animaciones":
        $urlAction = "animaciones";
        break;
    case "Bitacora":
        $urlAction = "bitacora";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Caja Chica":
        $urlAction = "cajachica";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Caja Reporte":
        $urlAction = "cajareporte";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Carga Pagos":
        $urlAction = "cargapagos";
        $hasConfigAction = true;
        $hasScriptAction = true;
        break;
    case "Casos":
        $urlAction = "casos";
        $hasScriptAction = true;
        $hasStyleAction = true;
        break;
    case "Catalogo":
        $urlAction = "catalogo";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Configuracion":
        $urlAction = "configuracion";
        $hasConfigAction = true;
        $hasScriptAction = true;
        break;
    case "Citas":
        $urlAction = "citas";
        $hasConfigAction=true;
        $hasScriptAction=true;
        $hasStyleAction=true;
        //$otherStyleHref[]="css/calendar-style.css?v=1.5";
        break;
    case "Comercio Exterior":
        $urlAction = "comext";
        $hasConfigAction=true;
        $hasScriptAction=true;
        $hasStyleAction=true;
        break;
    case "ComparaSAT": // Filtros validos para Comparar y Consultar. Checkbox en resultado de consulta y botón borrar solo los marcados. Mantener checkbox marcado en otras paginas.
        $urlAction="comparasat_d";
        $hasScriptAction=true;
        break;
    case "ComparaSAT_A": // UUID, fecha, emisor, receptor, subtotal, impuestos, total
        $urlAction="comparasat";
        $hasScriptAction=true;
        $hasConfigAction=true;
        break;
    case "ComparaSAT_B": // Solo UUID, Fecha y tipo de comprobante
        $urlAction="comparasat_b";
        $hasScriptAction=true;
        $hasConfigAction=true;
        break;
    case "ComparaSAT_C": // UUID, fecha, tipo de comprobante, rfc emisor y rfc receptor
        $urlAction="comparasat_c";
        $hasScriptAction=true;
        break;
    case "ComparaSATPrv":
        $urlAction="comparasatprv";
        $hasScriptAction=true;
        break;
    case "Contra Recibos":
        $urlAction = "contrarrecibos";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Correos":
        $urlAction = "correos";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "CR Diario":
        $urlAction = "crdiario";
        break;
    case "CuentasBancarias":
        $urlAction="cuentas";
        $hasScriptAction=true;
        $hasConfigAction=true;
        break;
    case "Depositos":
        $urlAction = "depositos";
        break;
    case "Descargar XML":
        $urlAction = "descargarXML";
        $hasScriptAction = true;
        break;
    case "Empleados":
        $urlAction = "empleados";
        $hasScriptAction=true;
        $hasConfigAction=true;
        break;
    case "Eventos":
        $urlAction = "eventos";
        $hasScriptAction=true;
        $hasConfigAction=true;
        break;
    case "ExtraeDatos":
        $urlAction = "extraedatos";
        $hasScriptAction = true;
        break;
    case "Factura":
        $urlAction = "factura";
        $hasScriptAction=true;
        break;
    case "Forma Pago":
        $urlAction = "formapago";
        $hasConfigAction=true;
        $hasScriptAction=true;
        break;
    case "Generar Contra Recibos":
        $urlAction = "generacontra";
        $hasConfigAction = true;
        $hasScriptAction = true;
        break;
    case "Generar TXT":
        $urlAction = "generatxt";
        $hasConfigAction = true;
        $hasScriptAction = true;
        break;
    case "Inactivo":
        $urlAction = "inactivo";
        break;
    case "ListaSolPago":
        $urlAction ="listasolp";
        $hasConfigAction = true;
        $hasStyleAction  = true;
        $hasScriptAction = true;
        //$templateSuffix = ($_esPruebas||$username==="jlobaton")?"_nxt":"";
        break;
    case "Logs":
        $urlAction = "logs";
        $hasScriptAction = true;
        break;
    case "Menu":
        $urlAction = "menu";
        $hasConfigAction=true;
        $hasScriptAction=true;
        break;
    case "Nomina":
        $urlAction = "nomina";
        $hasScriptAction=true;
        $hasConfigAction=true;
        break;
    case "Pruebas":
        $urlAction = "pruebas";
        $hasScriptAction=true;
        break;
    case "Registro":
        $urlAction = "registro";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Reporte Facturas":
        $urlAction = "reportefactura";
        $hasScriptAction = true;
        $hasStyleAction = true;
        $hasConfigAction = true;
        break;
    case "Reportes":
        $urlAction = "reportes";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Respalda Facturas":
        $urlAction = "respaldafactura";
        $otherScriptSrc[]="scripts/helper.php";
        $otherScriptSrc[]="scripts/respaldafactura.php"; // Para q helper vaya primero hay q dejar action en false y anexar el default al other en el orden deseado
        $hasConfigAction = true;
        break;
    case "Respaldo":
        $urlAction = "respaldosis";
        $hasConfigAction = true;
        $hasScriptAction = true;
        break;
    case "SituacionFiscal":
        $urlAction = "consitfis";
        $hasConfigAction = true;
        $hasScriptAction = true;
        $otherScriptSrc[]="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js";
        $otherScriptSrc[]="scripts/pdfviewer.php";
        break;
    case "SolicitaPago":
        $urlAction = "solpago";
        $hasConfigAction = true;
        $hasStyleAction  = true;
        $hasScriptAction = true;
        if ($_esDesarrollo) $scriptSuffix="2";
        break;
    case "Upgrade":
        $urlAction = "upgrade";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "VentasCliente":
        $urlAction = "repAVentasCliente";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    case "Viajero":
        $urlAction = "viajero";
        $hasScriptAction = true;
        $hasConfigAction = true;
        break;
    default:
        $real_menu_accion = $menu_accion;
        $urlAction = "login";
}
doclog("POSTSWITCH","action",["menu_action"=>$menu_accion,"urlAction"=>($urlAction??"null")]);
//    echo "<!-- POSTSWITCH: menu_action='$menu_accion', urlAction='".($urlAction??"null")."' -->";

if (!isset($resultMessage[0]) && isset($_POST["resultMessage"][0])) $resultMessage=$_POST["resultMessage"];
if (!isset($errorMessage[0]) && isset($_POST["errorMessage"][0])) $errorMessage=$_POST["errorMessage"];
$shownGeneralTries=+($_SESSION["NUMERO_DE_AVISOS_GENERALES"]??"0");
if ($hasUser&&(!isset($menu_accion[0])||$menu_accion==="Inicio"||$menu_accion==="ActualizaCuentaBancaria"||(!isset($_POST["menu_accion"])&&$menu_accion!=="Actualizacion"&&$menu_accion!=="CambiaClave"&&$menu_accion!=="SituacionFiscal"&&$shownGeneralTries<3))&&!isset($_REQUEST["logout"])) {
    $anyGeneralView=false;
    $avisoGeneralProveedor=AVISO_GENERAL_PROVEEDOR??"";
    if (isset($user->proveedor)) {
        $prvRfc=$user->proveedor->rfc??"";
        if (isset($prvRfc[11]) && isset($errorMessage[0])) { // solo se agrega si hay errores
            if (isset($prvRfc[12])) {
                if (isset(AVISO_GENERAL_PF[0])) $avisoGeneralProveedor.=AVISO_GENERAL_PF;
            } else if (isset(AVISO_GENERAL_PM[0])) $avisoGeneralProveedor.=AVISO_GENERAL_PM;
        }
        if (isset($avisoGeneralProveedor[0])) {
            if (isset($errorMessage[0])) {
                $fmc=strpos($errorMessage, $lstPfx);
                if ($fmc!==false) $errorMessage=substr_replace($errorMessage, $avisoGeneralProveedor, $fmc+$lpLen, 0);
                else $errorMessage.=$avisoGeneralProveedor;
                $errorTitle="AVISOS";
            } else if (isset($resultMessage[0])) {
                $fmc=strpos($resultMessage, $lstPfx);
                if ($fmc!==false) $resultMessage=substr_replace($resultMessage, $avisoGeneralProveedor, $fmc+$lpLen, 0);
                else $resultMessage.=$avisoGeneralProveedor;
                $resultTitle="RECORDATORIOS";
            } else $resultMessage=$avisoGeneralProveedor;
            $anyGeneralView=true;
        }
    }
    if (isset(AVISO_GENERAL[0])) {
        if (isset($errorMessage[0])) {
            $fmc=strpos($errorMessage, $lstPfx);
            if ($fmc!==false) $errorMessage=substr_replace($errorMessage, AVISO_GENERAL, $fmc+$lpLen, 0);
            else $errorMessage.=AVISO_GENERAL;
            $errorTitle="AVISOS";
        } else if (isset($resultMessage[0])) {
            $fmc=strpos($resultMessage, $lstPfx);
            if ($fmc!==false) $resultMessage=substr_replace($resultMessage, AVISO_GENERAL_PROVEEDOR, $fmc+$lpLen, 0);
            else $resultMessage.=AVISO_GENERAL;
            $resultTitle="RECORDATORIOS";
        } else $resultMessage=AVISO_GENERAL;
        $anyGeneralView=true;
    }
    if ($anyGeneralView) {
        $shownGeneralTries++;
        $_SESSION["NUMERO_DE_AVISOS_GENERALES"]="$shownGeneralTries";
    }
}

//echo "      <!-- PRE MENU ACCION ".($_esPruebas?"STILL TESTING":"NOT ANYMORE TEST")." -->\n";
include "menu_accion.php";
//echo "      <!-- POS MENU ACCION ".($_esPruebas?"STILL TESTING":"NOT ANYMORE TEST")." -->\n";

//echo "      <!-- HABILITADO:".($habilitado?"TRUE":"FALSE")." -->\n";
if ($habilitado) include "configuracion/finalizacion.php";
