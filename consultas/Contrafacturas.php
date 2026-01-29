<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
if (!isset($ctfObj)) {
    require_once "clases/Contrafacturas.php";
    $ctfObj = new Contrafacturas();
}
$ctfObj->rows_per_page=0;
if (isValueService()) getValueService($ctfObj);
else if (isTestService()) getTestService($ctfObj);
else if (isCatalogService()) getCatalogService($ctfObj);
else if (isActionService()) doActionService();
else if (isset($_GET["eraseList"]) && isset($_GET["counterId"]) && isset($_GET["newTotal"])) {
    $baseData=["file"=>getShortPath(__FILE__),"action"=>"eraseList","get"=>$_GET];
    $idList = $_GET["eraseList"];
    $counterId = $_GET["counterId"];
    $newTotal = $_GET["newTotal"];
    $ids = explode(",",$idList);
    if(!empty($ids)) {
        if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj=new Contrarrecibos(); }
        $ctrObj->rows_per_page=0;
        $ctrData=$ctrObj->getData("id=$counterId", 1); //"id", "folio", "codigoProveedor", "razonProveedor", "rfcGrupo", "razonGrupo", "aliasGrupo", "fechaRevision", "fechaPago", "total", "modifiedTime"
        if (isset($ctrData[0])) {
            $ctrData=$ctrData[0];
            $counterCode = "$ctrData[aliasGrupo]-$ctrData[folio]";
            $prvCode = $ctrData["codigoProveedor"];
            $oldTotal = $ctrData["total"];
            $det="Contra recibo eliminado $counterCode";
        } else {
            $det="Contra recibo no encontrado $counterId";
            doclog("Contra recibo marcado para eliminar pero no encontrado","error",$baseData+["line"=>__LINE__,"errors"=>DBi::$errors]);
        }
        $invIdList = $ctfObj->getList("id", $ids, "idFactura", "idContrarrecibo=$counterId");
        // se incluye validar idContrarrecibo por si se transfiere el contra recibo a otro y deja de coincidir, o contra intentos de hackeo
        if (isset($invIdList[0])) {
            $invIds = explode("|",$invIdList);
            foreach($invIds as $iid) {
                if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj = new Facturas(); }
                list($status,$statusn) = explode("|",$invObj->getValue("id",$iid,"status,statusn"));
                //$prevStatus = $invObj->prevStatus($status,"Contrarrecibo");
                $actionStatusN = Facturas::actionToStatusN("Contrarrecibo");
                if (Facturas::estaContrarrecibo($statusn))
                    $prevStatusN = $statusn - $actionStatusN;
                else $prevStatusN = $statusn;
                $prevStatus = Facturas::statusnToDetailStatus($prevStatusN);
                if ($statusn!==$prevStatusN) {
                    $fieldarray = ["id"=>$iid, "statusn"=>$prevStatusN];
                    if ($status!==$prevStatusN) $fieldarray["status"]=$prevStatus;
                    if ($invObj->saveRecord($fieldarray)) {
                        if (!isset($solObj)) {
                            require_once "clases/SolicitudPago.php";
                            $solObj = new SolicitudPago();
                        }
                        if (!isset($prcObj)) {
                            require_once "clases/Proceso.php";
                            $prcObj = new Proceso();
                        }
                        $solObj->updateStatus($iid, -Facturas::STATUS_CONTRA_RECIBO);
                        $prcObj->cambioFactura($iid, $prevStatus, getUser()->nombre, null, "Contrafacturas.eraseList:$status($statusn-$actionStatusN=$prevStatusN)");
                    }
                }
            }
            if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
            $invObj->rows_per_page=0;
            $folFacs = array_column($invObj->getData("id in (".implode(",",$invIds).")", 0, "folio"), "folio");
            if (isset($folFacs[0])) $det.=(isset($folFacs[1])?" con ".count($folFacs)." facturas":" con factura ".$folFacs[0]);
            else {
                $det.=" sin facturas";
                doclog("Contra recibo marcado para eliminar pero no tiene facturas","error",$baseData+["line"=>__LINE__,"errors"=>DBi::$errors]);
            }
        } else {
            doclog("Contra recibo marcado para eliminar pero sin facturas","error",$baseData+["line"=>__LINE__,"errors"=>DBi::$errors]);
            $det.=" sin facturas";
        }
        $fieldarray = ["id"=>$ids];
        if ($ctfObj->deleteRecord($fieldarray)) {
            // toDo: Calcular newTotal y newRegNum en lugar de obtenerlo por GET
            // select idContrarrecibo, count(autorizadaPor) auths, count(1) num , sum(total) sumTot from contrafacturas
            $ctfData = $ctfObj->getData("idContrarrecibo=$counterId", 0, "count(autorizadaPor) auths, count(1) num, sum(total) sumtot","","idContrarrecibo");
            $calcNewTotal=array_column($ctfObj->getData("idContrarrecibo=$counterId", 0, "sum(total) sumtot","","idContrarrecibo"),"sumtot")[0];
            doclog("Verificacion de nuevo total de contra recibo","contrarecibo",["idContrarrecibo"=>$counterId,"folioCR"=>$counterCode??"","newTotal"=>$newTotal,"calcNewTotal"=>$calcNewTotal]);
            $fieldarray = ["id"=>$counterId, "total"=>$newTotal];
            if ($ctrObj->saveRecord($fieldarray)) {
                if (!isset($prcObj)) {
                    require_once "clases/Proceso.php";
                    $prcObj = new Proceso();
                }
                $detalle = ""; // facturas que contenia, total original, folio de contrarrecibo
                if (isset($counterCode)) $detalle .= $counterCode;
                if (isset($prvCode)) {
                    if (isset($detalle[0])) $detalle.= " ";
                    $detalle .= $prvCode;
                }
                if (isset($oldTotal)) {
                    if (isset($detalle[0])) $detalle.= " ";
                    $detalle .= $oldTotal;
                }
                $detalle .= ". BORRADO PARCIAL";
                if (isset($invIds)) {
                    $detalle .= " (IDS:".implode(",",$invIds).")";
                }
                $detalle .= " => TOT=$ {$newTotal}";
                if ($newTotal!=$calcNewTotal) $detalle.=" / CALC=$ {$calcNewTotal}";
                $prcObj->cambioContrarrecibo($counterId, "Borrado", getUser()->nombre, $detalle);
                echo "OK";
            } else clog2($ctrObj->log);
        } else clog2($ctfObj->log);
    } else clog2("Lista de Ids vacía");
} else if (isset($_GET["eraseCounter"])) {
    $counterId = $_GET["eraseCounter"];
    $invIdList = $ctfObj->getList("idContrarrecibo",$counterId,"idFactura");
    if (isset($invIdList[0])) {
        $invIds = explode("|",$invIdList);
        foreach($invIds as $iid) {
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj = new Facturas();
            }
            list($status,$statusn) = explode("|",$invObj->getValue("id",$iid,"status,statusn"));
            //$prevStatus = $invObj->prevStatus($status,"Contrarrecibo");
            $actionStatusN = Facturas::actionToStatusN("Contrarrecibo");
            if (Facturas::estaContrarrecibo($statusn))
                $prevStatusN = $statusn - $actionStatusN;
            else $prevStatusN = $statusn;
            $prevStatus = Facturas::statusnToDetailStatus($prevStatusN);
            if ($statusn!==$prevStatusN) {
                $fieldarray = ["id"=>$iid, "statusn"=>$prevStatusN];
                if ($status!==$prevStatusN) $fieldarray["status"]=$prevStatus;
                if ($invObj->saveRecord($fieldarray)) {
                    if (!isset($solObj)) {
                        require_once "clases/SolicitudPago.php";
                        $solObj = new SolicitudPago();
                    }
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj = new Proceso();
                    }
                    $solObj->updateStatus($iid, -Facturas::STATUS_CONTRA_RECIBO);
                    $prcObj->cambioFactura($iid, $prevStatus, getUser()->nombre, null, "Contrafacturas.eraseCounter:$status($statusn-$actionStatusN=$prevStatusN)");
                }
            }
        }
    }
    
    $fieldarray = ["idContrarrecibo"=>$counterId];
    $res=$ctfObj->deleteRecord($fieldarray);
    if (!$res) clog2($ctfObj->log);
    if (!isset($ctrObj)) {
        require_once "clases/Contrarrecibos.php";
        $ctrObj = new Contrarrecibos();
    }
    $data = $ctrObj->getData("id=$counterId", 1);
    if (!empty($data) && !empty($data[0])) {
        //"id", "folio", "codigoProveedor", "razonProveedor", "rfcGrupo", "razonGrupo", "aliasGrupo", "fechaRevision", "fechaPago", "total", "modifiedTime"
        $counterCode = $data[0]["aliasGrupo"].$data[0]["folio"];
        $prvCode = $data[0]["codigoProveedor"];
        $oldTotal = $data[0]["total"];
    }
    $fieldarray = ["id"=>$counterId];
    if ($ctrObj->deleteRecord($fieldarray)) {
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj = new Proceso();
        }
        $detalle = ""; // parcial, id facturas eliminadas, total original, folio de contrarrecibo
        if (isset($counterCode)) $detalle .= $counterCode;
        if (isset($prvCode)) {
            if (isset($detalle[0])) $detalle.= " ";
            $detalle .= $prvCode;
        }
        if (isset($oldTotal)) {
            if (isset($detalle[0])) $detalle.= " ";
            $detalle .= $oldTotal;
        }
        $detalle .= ". BORRADO TOTAL";
        if (isset($invIds)) {
            $detalle .= " (".implode(",",$invIds).")";
        }
        $prcObj->cambioContrarrecibo($counterId, "Borrado", getUser()->nombre, $detalle);
        echo "OK";
    } else clog2($ctrObj->log);
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"post"=>$_POST];
    if (hasUser()) $baseData["usuario"]=getUser()->nombre;
    switch($_POST["action"]) {
        case "auth":
            if (!hasUser()) { echoJSDoc("refresh", "Sin sesion", null, $baseData+["line"=>__LINE__], "access"); return; }
            $esAdmin=validaPerfil("Administrador");
            $esSistemas=$esAdmin||validaPerfil("Sistemas");
            $esAutorizaContraRecibos = $esSistemas||validaPerfil("Autoriza Contra Recibos");
            if (!$esAutorizaContraRecibos) { echoJSDoc("refresh", "No autorizado", null, $baseData+["line"=>__LINE__], "access"); return; }
            if (!isset($_POST["list"][0])) { echoJSDoc("error", "No se registraron facturas para autorizar", null, $baseData+["line"=>__LINE__], "authorized"); return; }
            $list=$_POST["list"];
            global $ctrObj, $ctfObj;
            if (!isset($ctrObj)) {
                require_once "clases/Contrarrecibos.php";
                $ctrObj = new Contrarrecibos();
            }
            $ctrObj->rows_per_page=0;
            $ctfData=$ctfObj->getData("id in (".implode(",", $list).")", 0, "id,idContrarrecibo,folioFactura,autorizadaPor");
            $validList=[];
            $numAuth=[];
            $fLst=[];
            foreach ($ctfData as $idx => $row) {
                $idCR=$row["idContrarrecibo"];
                if (((+$row["autorizadaPor"]??0)>0)) {
                    doclog("Previamente Autorizada","authorized",$row);
                    continue;
                }
                if (!isset($numAuth[$idCR])) {
                    $ctrData=$ctrObj->getData("id=$idCR", 1, "id");
                    $numAuth[$idCR]=0;
                }
                $validList[]=$row["id"];
                $numAuth[$idCR]++;
                if (!isset($fLst[$idCR])) $fLst[$idCR]=[$row["folioFactura"]];
                else $fLst[$idCR][]=$row["folioFactura"];
            }
            if (!isset($validList[0])) {
                $is1=isset($ctfData[0]["id"])&&!isset($ctfData[1]["id"]); $pl_n=$is1?"":"n"; $pl_s=$is1?"":"s";
                echoJSDoc("error", "La{$pl_s} factura{$pl_s} ya estaba{$pl_n} autorizada{$pl_s}", null, $baseData+["line"=>__LINE__], "authorized");
                return;
            }
            global $query;
            $ymd = (new DateTime())->format("Ymd");
            DBi::autocommit(FALSE);
            if (!$ctfObj->saveRecord(["id"=>$validList,"autorizadaPor"=>getUser()->id,"fechaAutorizada"=>$ymd])) {
                DBi::rollback();
                DBi::autocommit(TRUE);
                echoJSDoc("error", "No fue posible autorizar las facturas, intente de nuevo más tarde", null, $baseData+["line"=>__LINE__,"cfList"=>$validList,"crList"=>$numAuth,"query"=>$query,"errors"=>DBi::$errors], "authorized");
                return;
            }
            $qrs=[$query];
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
            }
            if (!isset($firObj)) {
                require_once "clases/Firmas.php";
                $firObj = new Firmas();
            }
            $usrName=getUser()->nombre;
            foreach ($numAuth as $idCr=>$sumAuth) {
                if(!$ctrObj->saveRecord(["id"=>$idCr,"numAutorizadas"=>new DBExpression("numAutorizadas+{$sumAuth}")])) {
                    DBi::rollback();
                    DBi::autocommit(TRUE);
                    echoJSDoc("error", "No fue posible autorizar el contra recibo, intente de nuevo más tarde", null, $baseData+["line"=>__LINE__,"factFolios"=>$fLst[$idCr],"query"=>$query,"errors"=>DBi::$errors], "authorized");
                    return;
                }
                $qrs[]=$query;
                if ($sumAuth=="1") {
                    $det="autoriza ".$fLst[$idCr][0];
                } else {
                    $det="$sumAuth autorizadas: ".implode(",", $fLst[$idCr]);
                }
                if (!$prcObj->cambioContrarrecibo($idCr, "Autorizado", $usrName, $det)) {
                    doclog("Error al guardar proceso de autorización en contra recibo","error",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors]);
                }
                $qrs[]=$query;
                $firDataArray=["idUsuario"=>getUser()->id,"modulo"=>"contrarrecibo","idReferencia"=>$idCr,"accion"=>"autoriza","motivo"=>$det];
                if (!$firObj->saveRecord($firDataArray)) {
                    doclog("Error al guardar Firma de autorización en contra recibo","error",$baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors]);
                } else $qrs[]=$query;
            }
            DBi::commit();
            DBi::autocommit(TRUE);
            echoJSDoc("success", "Autorizacion Exitosa", null, ["cfList"=>$validList,"crList"=>$numAuth,"queries"=>$qrs], "authorized");
            break;
        case "check": doclog("CHECKBOX CAMBIADO","authorized",$_POST); break;
        case "next": doclog("CHECKBOX SIGUIENTE","authorized",$_POST); break;
        default: echoJSDoc("error", "Accion no definida", null, $baseData+["line"=>__LINE__], "authorized");
    }
}
