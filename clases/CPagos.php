<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class CPagos extends DBObject {
    public $fixedIdList=["Queries"=>[],"Pasos"=>[],"Errores"=>[]];
    public $data=["inv"=>[],"icp"=>[],"cpy"=>[],"cpyc"=>[],"dpy"=>[],"dpyf"=>[],"dpyp"=>[]];
    public $dataLog=[];
    function __construct() {
        $this->tablename      = "cpagos";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id","idCPago","idFactura","numParcialidad","saldoAnterior","impPagado","saldoInsoluto","moneda","equivalencia", "idEPago","fechaPago","montoPago","monedaPago","tipocambioPago","modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx CPagos xxxxxxxxxxxxxx //\n";
    }
    function insertIntoProceso($idf,$user) {
        $ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
        // ToDo: Mover este codigo en una funcion dentro del objeto PROCESO
        $query="INSERT INTO proceso (modulo,identif,status,detalle,fecha,usuario,region) (SELECT 'CPago',$idf,if(saldoInsoluto=0,'Pagado','Parcial'),concat('$',format(saldoAnterior,2),' - $',format(impPagado,2),' = $',format(saldoInsoluto,2)),fechaPago,'$user->nombre','$ip' from cpagos where id=$idf)";
        DBi::query($query); // proceso no entra para replicar insert, no hace falta pasar el objeto
    }
    private function resetAllData() {
        $this->fixedIdList=["Queries"=>[],"Pasos"=>[],"Errores"=>[]];
        $this->data=["inv"=>[],"icp"=>[],"cpy"=>[],"cpyc"=>[],"dpy"=>[],"dpyf"=>[],"dpyp"=>[]];
        $this->dataLog=[];
    }
    private function fixedIdReview() {
        $invMin=$this->data["inv"]["min"]??".";
        $invMax=$this->data["inv"]["max"]??".";
        $icpMin=$this->data["icp"]["min"]??".";
        $icpMax=$this->data["icp"]["max"]??".";
        $this->fixedIdList["Pasos"][]="LIST INV: ".implode(",",array_keys($this->data["inv"]));
        $this->fixedIdList["Pasos"][]="LIST ICP: ".implode(",",array_keys($this->data["icp"]));
        $this->fixedIdList["Pasos"][]="REVIEW INV: $invMin - $invMax";
        $this->fixedIdList["Pasos"][]="REVIEW ICP: $icpMin - $icpMax";
    }
    private function errObjData($type) {
        global $query, $invObj, $dpyObj;
        switch($type) {
            case "INV": if (isset($invObj)) return $invObj->errors;
            case "CPY": return $this->errors;
            case "DPY": if (isset($dpyObj)) return $dpyObj->errors;
        }
        return null;
    }
    private function setObjData($action,$type,$params,$prefix=null) {
        global $query, $invObj, $dpyObj;
        $tmstmp = date("His");
        switch($type) {
            case "INV": if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); } $obj=$invObj; break;
            case "CPY": $obj=$this; break;
            case "DPY": if (!isset($dpyObj)) { require_once "clases/DPagos.php"; $dpyObj=new DPagos(); } $obj=$dpyObj; break;
        }
        if (!isset($obj)) {
            $this->fixedIdList["Errores"][]="[$tmstmp] SetObjData Tipo incorrecto ($type|$action)";
            return null;
        }
        if (isset($prefix)) $prefix="{$action}-{$prefix}-{$type}";
        else $prefix="{$action}-{$type}";
        if (isset($params["id"])) $identif="Id=$params[id]";
        else {
            $k1=array_key_first($params);
            if ($k1==0) $identif=$params[$k1];
            else $identif="$k1=".$params[$k1];
        }
        $res=null; $resTxt="";
        try {
            $this->fixedIdList["Pasos"][]="[$tmstmp] $prefix";
            switch ($action) {
                case "GET":
                    $res=$obj->getData(...$params);
                    $resTxt="| COUNT($identif): ".count($res);
                    break;
                case "DEL": case "DELETE":
                    $res=$obj->deleteRecord($params);
                    $resTxt="| ".($res===TRUE?"SUCCESS":($res===FALSE?"FAILURE":$res))."($identif)";
                    break;
                case "SAV": case "SAVE":
                    $res=$obj->saveRecord($params);
                    if ($res===FALSE) {
                        if ($obj->affectedrows==0 && DBi::$errno==0) $resTxt="| NO CHANGE";
                        else $resTxt="| FAILURE";
                    } else {
                        $resTxt="| SUCCESS";
                        if (isset($params["id"])) {
                            $thisId=$params["id"];
                            $dataRef = isset($this->data["inv"][$thisId])?"inv":(isset($this->data["icp"][$thisId])?"icp":"");
                            if (isset($dataRef[0])) {
                                if (isset($this->data[$dataRef]["min"])) {
                                    if ($this->data[$dataRef]["min"]>$thisId);
                                } else $this->data[$dataRef]["min"]=$thisId;

                                if (!isset($this->data[$dataRef]["max"])) {
                                    if ($this->data[$dataRef]["max"]<$thisId);
                                } else $this->data[$dataRef]["max"]=$thisId;
                            } else $resTxt.=" NOREF";
                        } else $resTxt.=" NEW";
                    }
                    $resTxt.=($res===TRUE?"":" ".$res)." ($identif)";
                    if ($res===TRUE) $resTxt.=" LastId=".DBi::getLastId();
                    break;
                default:
                    $this->fixedIdList["Errores"][]="[$tmstmp] ($identif) SetObjData Acción incorrecta";
                    return null;
            }
            $this->fixedIdList["Queries"][]="[$tmstmp] $query";
            $currentErrno = DBi::getErrno();
            $currentError = DBi::getError();
            if (isset($currentErrno) && $currentErrno!==0) {
                if (!isset($currentError)) $currentError="...";
                $this->fixedIdList["Errores"][]="[$tmstmp] ($identif) {$currentErrno}: {$currentError}";
            }
            if (isset($resTxt[0])) $this->fixedIdList["Pasos"][array_key_last($this->fixedIdList["Pasos"])].=$resTxt;
        } catch (Error $e) {
            $extraData=["time"=>$tmstmp];
            if (isset($params["id"])) $extraData["Id"]=$params["id"];
            else {
                $k1=array_key_first($params);
                $extraData[$k1]=$params[$k1];
            }
            $this->fixedIdList["Errores"][]=array_merge($extraData,getErrorData($e));
            $this->fixedIdList["Pasos"][array_key_last($this->fixedIdList["Pasos"])].="| ERROR";
        }
        return $res;
    }
    private function getObjData($type,$params,$prefix=null) { return $this->setObjData("GET",$type,$params,$prefix); }
    private function saveObjData($type,$params,$prefix=null) { return $this->setObjData("SAV",$type,$params,$prefix); }
    private function delObjData($type,$params,$prefix=null) { return $this->setObjData("DEL",$type,$params,$prefix); }
    private function hasData($dataRef) {
        if (isset($this->data[$dataRef])) return count($this->data[$dataRef]);
        return false;
    }
    private function getDataIds($dataRef) {
        if ($this->hasData($dataRef))
            return array_keys($this->data[$dataRef]);
        return [];
    }
    private function getMiniMap0($map, $numKeys=3, $prefKeys=['id','folio','codigoProveedor','ubicacion','idReciboPago','statusn','idCPago','idPPago','idFactura','numParcialidad'], $ignoreKeys=[]) {
        $miniMap=[];
        foreach ($map as $mapId => $mapItem) {
            $num=0;
            $miniMap[$mapId]=[];
            foreach($mapItem as $key => $val) {
                if (in_array($key, $prefKeys) && !in_array($key, $ignoreKeys)) {
                    $miniMap[$mapId][$key]=$val;
                    if (($num++)>$numKeys) break;
                }
            }
        }
        return $miniMap;
    }
    private function getMiniMap($map, $numKeys=3, $prefKeys=['id','folio','codigoProveedor','ubicacion','idReciboPago','statusn','idCPago','idPPago','idFactura','numParcialidad'], $ignoreKeys=[]) {
        $miniMap=[];
        foreach ($map as $mapId => $mapArray) {
            $miniMap[$mapId]=[];
            foreach($mapArray as $idx => $mapItem) {
                $num=0;
                $miniMap[$mapId][$idx]=[];                
                foreach($mapItem as $key => $val) {
                    if (in_array($key, $prefKeys) && !in_array($key, $ignoreKeys)) {
                        $miniMap[$mapId][$idx][$key]=$val;
                        if (($num++)>$numKeys) break;
                    }
                }
            }
        }
        return $miniMap;
    }
    public function logData($action,$dataRef,$map,$ignoreKeys=[]) {
        $prefKeys=['id','folio','codigoProveedor','ubicacion','idReciboPago','statusn','idCPago','idPPago','idFactura','numParcialidad'];
        $this->dataLog[]=strtoupper($action)." $dataRef\n    map: ".json_encode($this->getMiniMap0($map,4,$prefKeys,$ignoreKeys))."\n    data: ".json_encode($this->getMiniMap0($this->data[$dataRef],4,$prefKeys,$ignoreKeys));
    }
    public function logData1($action,$dataRef,$map,$ignoreKeys=[]) {
        $prefKeys=['id','folio','codigoProveedor','ubicacion','idReciboPago','statusn','idCPago','idPPago','idFactura','numParcialidad'];
        $this->dataLog[]=strtoupper($action)." $dataRef\n    map: ".json_encode($this->getMiniMap($map,4,$prefKeys,$ignoreKeys))."\n    data: ".json_encode($this->getMiniMap($this->data[$dataRef],4,$prefKeys,$ignoreKeys));
    }
    private function addData($data, $dataKey, $dataRef, $forceReplace=false) {
        if (isset($data[0][$dataKey])) {
            $added=0;
            $dataIds=array_column($data,$dataKey);
            if ($dataIds===array_unique($dataIds)) {
                $map=array_combine($dataIds, $data);
            } else {
                $map=[];
                foreach($data as $av) {
                    $ai=$av["id"];
                    $af=$av[$dataKey];
                    if(isset($map[$af])) $map[$af][$ai]=$av;
                    else $map[$af]=[$ai=>$av];
                }
            }
            $this->data[$dataRef]=("array_".($forceReplace?"replace":"merge"))($this->data[$dataRef]??[], $map);
            $this->logData("addData",$dataRef,$map,[$dataKey]);
            return count($data);
        }
        return false;
    }
    private function delData($dataRef,$dataValues=null) { // dataRef: inv,icp,cpy,cpyc,dpy,dpyf,dpyp
        $num=0;
        if (!isset($dataValues[0])) {
            $num=count(array_keys($this->data[$dataRef]));
            $this->data[$dataRef]=[];
        } else foreach ($dataValues as $dataIdx => $dataVal) if (isset($this->data[$dataRef][$dataVal])) {
            $num++;
            unset($this->data[$dataRef][$dataVal]);
        }
        $this->logData("delData",$dataRef,$dataValues);
        return $num;
    }


    private function getPagosEnFacturas($ids, $forceReplace=false) {
        $idValues=isset($ids[1])?" in (".implode(", ", $ids).")":"=".$ids[0];
        $invData=$this->getObjData("INV",["id{$idValues} and tipoComprobante!='p'"]);
        if (isset($invData[0]["id"])) {
            $this->addData($invData,"id","inv",$forceReplace);
            $cpIds=array_unique(array_filter(array_column($invData, "idRecibo")));
            $cpIdV=isset($cpIds[1])?" in (".implode(", ", $cpIds).")":"=".$cpIds[0];
            $this->addData($this->getObjData("INV",["id{$cpIdV} and tipoComprobante='p'"]),"id","icp",$forceReplace);
        }
        $this->addData($this->getObjData("INV",["id{$idValues} and tipoComprobante='p'"]),"id","icp",$forceReplace);
    }
    private function getCPagos($ids,$byId=false,$forceReplace=false) {
        if (isset($ids[0])) {
            $fldName=is_bool($byId)?($byId?"id":"idCPago"):$byId;
            $idValues=isset($ids[1])?" in (".implode(", ", $ids).")":"=".$ids[0];
            $cpyData=$this->getObjData("CPY",["{$fldName}{$idValues}"]);
            $this->addData($cpyData,"idCPago","cpyc",$forceReplace);
            $this->addData($cpyData,"id","cpy",$forceReplace);
            return $cpyData;
        } return [];
    }
    private function getDPagos($ids,$byId=false,$forceReplace=false) {
        if (isset($ids[0])) {
            $fldName=is_bool($byId)?($byId?"id":"idFactura"):$byId;
            $idValues=isset($ids[1])?" in (".implode(", ", $ids).")":"=".$ids[0];
            $dpyData=$this->getObjData("DPY",["{$fldName}{$idValues}"]);
            $this->addData($dpyData,"idFactura","dpyf",$forceReplace);
            $this->addData($dpyData,"idPPago","dpyp",$forceReplace);
            $this->addData($dpyData,"id","dpy",$forceReplace);
            return $dpyData;
        } return [];
    }


    public function fixEmptyCPagos() {
        $this->resetAllData();
        try {
            $this->fixedIdList["Pasos"][]="INI fixEmptyCPagos";
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $rpp=$invObj->rows_per_page;
            $ordlist=$invObj->orderlist;
            $invObj->rows_per_page=100;
            $invObj->clearOrder();
            $invObj->addOrder("ciclo","desc");
            $icpData=$this->getObjData("INV",["f.tipoComprobante='p' and (f.statusn is null or f.statusn<128) and f.status!='Temporal' and c.idCPago is null",0,"f.*","f left join cpagos c on f.id=c.idCPago"]);
            $icpRows=$this->numrows;
            $this->addData($icpData,"id","icp");
            $idRPs=array_column($icpData, "id");
            $rpIdS=isset($idRPs[1])?" in (".implode(", ", $idRPs).")":"=".$idRPs[0];
            $invData=$this->getObjData("INV",["idReciboPago$rpIdS and tipoComprobante!='p'"]);
            $invRows=$this->numrows;
            $this->addData($invData,"id","inv");
            DBi::autocommit(false);
            $this->reloadCPData($icpData, $cpyData, $dpyData);
            $this->setInvoicesStatus($icpData, $cpyData, $dpyData);
            DBi::commit();
            $this->fixedIdList["Pasos"][]="Commit!";
        } catch (CPException $cpex) {
            DBi::rollback();
            $nm=$cpex->getName();
            $isPym = (substr($nm, 0, 5)==="pagos");
            $isErr = ($nm==="error");
            $cpex->doLog($isPym?$cpex->getMessage():"fixEmptyCPagos ".($isErr?"Failed":"Error"));
            $this->fixedIdList["Pasos"][]="Rollback! CPException ".$cpex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($cpex));
            return $cpex->getUserMessage();
        } catch (Error $ex) {
            DBi::rollback();
            doclog("fixEmptyCPagos Unexpected Failure","error",getErrorData($ex));
            $this->fixedIdList["Pasos"][]="Rollback! Exception ".$ex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return "Falló la actualización de datos de pago";
        } finally {
            DBi::autocommit(true);
            $this->fixedIdReview();
            $this->fixedIdList["Pasos"][]="END fixEmptyCPagos";
        }
    }
    public function fixListCPagos($idList) {
        $this->resetAllData();
        try {
            $this->fixedIdList["Pasos"][]="INI fixListCPagos";
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $icpData=$this->getObjData("INV",["tipoComprobante='p' and id in $values",0,"*"]);
            $this->addData($icpData,"id","icp");
            $invData=$this->getObjData("INV",["tipoComprobante!='p' and id in $values",0,"*"]);
            $this->addData($icpData,"id","inv");
            if ($this->hasData("icp")) {
                $this->getCPagos($this->getDataIds("icp"));
                $this->getDPagos($this->getDataIds("cpy"),true);
            }
            if ($this->hasData("inv")) {
                $this->getDPagos($this->getDataIds("inv"));
                $this->getCPagos($this->getDataIds("dpyp"),true);
            }

            DBi::autocommit(false);
            $this->reloadData();
            DBi::commit();
            $this->fixedIdList["Pasos"][]="Commit!";
        } catch (CPException $cpex) {
            DBi::rollback();
            $nm=$cpex->getName();
            $isPym = (substr($nm, 0, 5)==="pagos");
            $isErr = ($nm==="error");
            $cpex->doLog($isPym?$cpex->getMessage():"fixListCPagos ".($isErr?"Failed":"Error"));
            $this->fixedIdList["Pasos"][]="Rollback! CPException ".$cpex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($cpex));
            return $cpex->getUserMessage();
        } catch (Error $ex) {
            DBi::rollback();
            doclog("fixListCPagos Unexpected Failure","error",getErrorData($ex));
            $this->fixedIdList["Pasos"][]="Rollback! Exception ".$ex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return "Falló la actualización de datos de pago";
        } finally {
            DBi::autocommit(true);
            $this->fixedIdReview();
            $this->fixedIdList["Pasos"][]="END fixListCPagos";
        }
    }
    public function fixOldCPagos() {
        $this->resetAllData();
        try {
            $this->fixedIdList["Pasos"][]="INI fixOldCPagos";
            $rpp=$this->rows_per_page;
            $ordlist=$this->orderlist;
            $this->rows_per_page=100;
            $this->clearOrder();
            $this->addOrder("idCPago","desc");
            $cpyData=$this->getObjData("CPY",["fechapago is null",0,"distinct idCPago"]);
            $idCPs=array_column($cpyData, "idCPago");
            $this->rows_per_page=1000;
            $cpyData=$this->getObjData("CPY",["idCPago in (".implode(",", $idCPs).")",0,"id"]);
            $idPPs=array_column($cpyData, "id");
            $this->setOrderList($ordlist);
            $this->rows_per_page=$rpp;

            //$this->getPagosEnFacturas($idCPs);
            // probar si está incluido en update CPData

            $this->delObjData("DPY",["idPPago"=>$idPPs]);
            $this->delObjData("CPY",["id"=>$idPPs]);
            $icpData=$this->getObjData("INV",["id{$idCPs}"]);
            $this->addData($icpData,"id","icp");
            $idRPs=array_column($icpData, "id");
            $rpIdS=isset($idRPs[1])?" in (".implode(", ", $idRPs).")":"=".$idRPs[0];
            $invData=$this->getObjData("INV",["idReciboPago$rpIdS and tipoComprobante!='p'"]);
            $this->addData($invData,"id","inv");
            DBi::autocommit(false);
            $this->reloadCPData($icpData, $cpyData, $dpyData);
            $this->setInvoicesStatus($icpData, $cpyData, $dpyData);
            DBi::commit();
            $this->fixedIdList["Pasos"][]="Commit!";
        } catch (CPException $cpex) {
            DBi::rollback();
            $nm=$cpex->getName();
            $isPym = (substr($nm, 0, 5)==="pagos");
            $isErr = ($nm==="error");
            $cpex->doLog($isPym?$cpex->getMessage():"fixOldCPagos ".($isErr?"Failed":"Error"));
            $this->fixedIdList["Pasos"][]="Rollback! CPException ".$cpex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return $cpex->getUserMessage();
        } catch (Error $ex) {
            DBi::rollback();
            doclog("fixOldCPagos Unexpected Failure","error",getErrorData($ex));
            $this->fixedIdList["Pasos"][]="Rollback! Exception ".$ex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return "Falló la actualización de datos de pago";
        } finally {
            DBi::autocommit(true);
            $this->fixedIdReview();
            $this->fixedIdList["Pasos"][]="END fixOldCPagos";
        }
    }
    function fixCP($id) {
        $this->resetAllData();
        try {
            $this->fixedIdList["Pasos"][]="INI fixCP $id";
            $idPPs=array_column($this->getObjData("CPY",["idCPago=$id",0,"id"]), "id");
            $this->delObjData("DPY",["idPPago"=>$idPPs]);
            $this->delObjData("CPY",["id"=>$idPPs]);

            $icpData=$this->getObjData("INV",["id=$id and tipoComprobante='p'"]);
            $this->addData($icpData,"id","icp");
            $invData=$this->getObjData("INV",["idReciboPago=$id and tipoComprobante!='p'"]);
            $this->addData($invData,"id","inv");

            DBi::autocommit(false);
            $this->reloadCPData($icpData, $cpyData, $dpyData);
            $this->setInvoicesStatus($icpData, $cpyData, $dpyData);
            DBi::commit();
            $this->fixedIdList["Pasos"][]="Commit!";
        } catch (CPException $cpex) {
            DBi::rollback();
            $nm=$cpex->getName();
            $isPym = (substr($nm, 0, 5)==="pagos");
            $isErr = ($nm==="error");
            $cpex->doLog($isPym?$cpex->getMessage():"fixCP ".($isErr?"Failed":"Error"));
            $this->fixedIdList["Pasos"][]="Rollback! CPException ".$cpex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return $cpex->getUserMessage();
        } catch (Error $ex) {
            DBi::rollback();
            doclog("fixCP Unexpected Failure","error",getErrorData($ex));
            $this->fixedIdList["Pasos"][]="Rollback! Exception ".$ex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return "Falló la actualización de datos de pago";
        } finally {
            DBi::autocommit(true);
            $this->fixedIdReview();
            $this->fixedIdList["Pasos"][]="END fixCP";
        }
    }
    function fixInvStats($cpId) {
        $this->resetAllData();
        try {
            $this->fixedIdList["Pasos"][]="INI fixInvStats $cpId";
            $icpData=$this->getObjData("INV",["id=$cpId and tipoComprobante='p'"]);
            $this->addData($icpData,"id","icp");
            $cpyData=$this->getCPagos([$cpId]);
            $dpyData=$this->getDPagos(array_column($cpyData, "id"),"idPPago");
            DBi::autocommit(false);
            $this->setInvoicesStatus($icpData, $cpyData, $dpyData);
            DBi::commit();
            $this->fixedIdList["Pasos"][]="Commit!";
        } catch (CPException $cpex) {
            DBi::rollback();
            $nm=$cpex->getName();
            $isPym = (substr($nm, 0, 5)==="pagos");
            $isErr = ($nm==="error");
            $cpex->doLog($isPym?$cpex->getMessage():"fixInvStats ".($isErr?"Failed":"Error"));
            $this->fixedIdList["Pasos"][]="Rollback! CPException ".$cpex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return $cpex->getUserMessage();
        } catch (Error $ex) {
            DBi::rollback();
            doclog("fixInvStats Unexpected Failure","error",getErrorData($ex));
            $this->fixedIdList["Pasos"][]="Rollback! Exception ".$ex->getMessage();
            $this->fixedIdList["Errores"][]=json_encode(getErrorData($ex));
            return "Falló la actualización de datos de pago";
        } finally {
            DBi::autocommit(true);
            $this->fixedIdReview();
            $this->fixedIdList["Pasos"][]="END fixCP";
        }
    }


    public function setInvoiceData() { // ToDo: Usar $this->data en lugar de argumentos de la función icp|cpy|dpy
        ;
    }

    function updateCPData($idCFDI) {
        try {
            $this->fixedIdList["Pasos"][]="INI updateCPData";
            if (!isset($idCFDI)) return "Falta indicar elementos a actualizar";
            DBi::autocommit(false);
            $this->resetCPData($idCFDI,/*&*/$icpData,/*&*/$cpyData,/*&*/$dpyData);
            $this->setInvoicesStatus($icpData, $cpyData, $dpyData);
            DBi::commit();
            $this->fixedIDList["Pasos"][]="Commit! Success!!";
            return true;
        } catch (CPException $cpex) {
            DBi::rollback();
            $nm=$cpex->getName();
            $isPym = (substr($nm, 0, 5)==="pagos");
            $isErr = ($nm==="error");
            $cpex->doLog($isPym?$cpex->getMessage():"UpdateCPData ".($isErr?"Failed":"Error"));
            $this->fixedIdList["Pasos"][]="Rollback! CPException ".$cpex->getMessage();
            return $cpex->getUserMessage();
        } catch (Exception $ex) {
            DBi::rollback();
            doclog("UpdateCPData Unexpected Failure","error",getErrorData($ex));
            $this->fixedIdList["Pasos"][]="Rollback! Exception ".$ex->getMessage();
            return "Falló la actualización de datos de pago";
        } finally {
            DBi::autocommit(true);
        }
    }
    private function resetCPData($idCFDI, &$icpData,&$cpyData,&$dpyData) {
        try {
            $this->fixedIdList["Pasos"][]="INI resetCPData";
            if (!isset($idCFDI)) throw new CPException("No hay ID", 10000001, null, ["id"=>$idCFDI],"pagosErr","Debe especificar un identificador de complemento de pago");
            $idCFDIs="";
            if(is_array($idCFDI)&&isset($idCFDI[1])) $idCFDIs=" in (".implode(", ", $idCFDI).")";
            else {
                $idCFDIs="=";
                if (is_array($idCFDI)) $idCFDIs.=$idCFDI[0];
                else $idCFDIs.=$idCFDI;
            }
            $icpData=$this->getObjData("INV",["id{$idCFDIs}"]);
            if (!isset($icpData[0])) {
                global $invObj;
                throw new CPException("No hay datos", 10000002, null, ["id"=>$idCFDI,"query"=>$query,"data"=>$icpData,"log"=>$invObj->log],"pagosErr","No se encontro registro de CFDI");
            }
            if (isset($icpData["id"])) $icpData=[$icpData];
            $rjPys=[];
            foreach ($icpData as $icpIdx => $icpValue) {
                $tc=strtolower($icpValue["tipoComprobante"]);
                $folio=$icpValue["folio"];
                $sttN=$icpValue["statusn"]??0;
                if (!isset($folio[0])) $folio="[".substr($icpValue["uuid"], -10)."]";
                if ($tc!=="p") throw new CPException("No es complemento de pago ($folio)", 10000003, null,["id"=>$idCFDI,"tc"=>$tc,"idx"=>$icpIdx,"data"=>$icpValue],"pagosErr","El comprobante $folio no fue registrado como pago ($tc)");
                if ($sttN>=Facturas::STATUS_RECHAZADO) {
                    $rjPys[]=$icpValue["id"];
                }
            }
            if (isset($rjPys[0])) $this->fixedIdList["Pasos"][]="Rejected CPs: ".implode(", ", $rjPys);

            $nonData=$this->getObjData("INV",["f.id{$idCFDIs} and c.idCPago is null",0,"*","f left join cpagos c on f.id=c.idCPago"],"NON");
            if (isset($nonData[0]["id"])) {
                $nonIdList=array_column($nonData, "id");
                if (isset($nonIdList[0])) $this->fixedIdList["Pasos"][]="Not in CPagos: ".implode(", ", $nonIdList);
                $nonIdList=array_unique(array_merge(
                    $nonIdList,
                    $rjPys), SORT_REGULAR);
                $nonArrFld=["id"=>$nonIdList,"idReciboPago"=>null,"fechaReciboPago"=>null,"saldoReciboPago"=>null,"statusReciboPago"=>null];
                $invRes=$this->saveObjData("INV",$nonArrFld,"NON");
            }
            $cpIdFldArr=["idCPago"=>$idCFDI];
            $cpIdWhere="idCPago{$idCFDIs}";
            $cpyData=$this->getObjData("CPY",[$cpIdWhere]);
            if (!isset($cpyData[0])) {
                doclog("EMPTY", "pagos", ["idFactura"=>$idCFDI, "invData"=>$icpData, "query"=>$query, "cpyWhere"=>$cpIdWhere, "cpyData"=>$cpyData]);
                $cpyData=null;
                $dpyData=null;
            } else {
                // toDo: Reemplazar emptyList por elementos en icpData y cpyData que correspondan a fechaPago vacía: Son los que se guardaron cuando no existia dpyData
                // toDo: Estos hay que eliminarlos de cpyData y pasar sus facturas a idReciboPago=null
                $emptyCpyIds=[];
                $emptyCPIds=[];
                $ppIds=[];
                foreach ($cpyData as $cpyIdx => $cpyRow) {
                    if (empty($cpyRow["fechaPago"])) {
                        $emptyCpyIds[]=$cpyRow["id"];
                        $emptyCPIds[]=$cpyRow["idCPago"];
                    }
                }
                $emptyList=array_filter(array_column($cpyData, "fechaPago"), fn($v)=>empty($v));
                $this->fixedIdList["Pasos"][]="EMPTY LIST=".count($emptyList);
                $ppWhere=""; $ppFldArr=[];
                $ppIds=array_column($cpyData, "id");
                if (isset($ppIds[1])) {
                    $ppFldArr=["idPPago"=>$ppIds];
                    $ppWhere="idPPago in (".implode(", ", $ppIds).")";
                } else if (isset($ppIds[0])) {
                    $ppFldArr=["idPPago"=>$ppIds[0]];
                    $ppWhere="idPPago=".$ppIds[0];
                } else {
                    $cpyRes=$this->delObjData("CPY",$cpIdFldArr);
                    doclog("El registro del complemento de pago está incompleto","pagos",["idFactura"=>$idCFDI,"invData"=>$icpData,"cpyData"=>$cpyData,"idPs"=>$ppIds,"deleteCP"=>["fldarr"=>$cpIdFldArr,"res"=>(is_bool($cpyRes)?($cpyRes?"TRUE":"FALSE"):$cpyRes)]]);
                    // El proceso continua para volver a generar CPagos y DPagos
                    $cpyData=null;
                    $dpyData=null;
                }
                if (isset($ppWhere[0])) {
                    $dpyData=$this->getObjData("DPY",[$ppWhere]);
                    if (!isset($dpyData[0])) {
                        $cpyRes=$this->delObjData("CPY",$cpIdFldArr);
                        doclog("DELETEP1","pagos",["idFactura"=>$idCFDI,"invData"=>$icpData,"cpyData"=>$cpyData,"deleteCP"=>["fldarr"=>$cpIdFldArr,"query"=>$query,"res"=>(is_bool($cpyRes)?($cpyRes?"TRUE":"FALSE"):$cpyRes)]]);
                        $cpyData=null;
                        $dpyData=null;
                    } else {
                        if (isset($emptyList[0])) { // toDo: en lugar de borrar todo cpyData y dpyData, solo eliminar los que esten en emptyList
                            $dpyRes=$this->delObjData("DPY",$ppFldArr);
                            $cpyRes=$this->delObjData("CPY",$cpIdFldArr);
                            doclog("DELETEP2","pagos",["idFactura"=>$idCFDI,"invData"=>$icpData,"dpyData"=>$dpyData,"cpyData"=>$cpyData,"deleteDP"=>["fldarr"=>$ppFldArr,"query"=>$dQry,"res"=>(is_bool($dpyRes)?($dpyRes?"TRUE":"FALSE"):$dpyRes)],"deleteCP"=>["fldarr"=>$cpIdFldArr,"query"=>$cQry,"res"=>(is_bool($cpyRes)?($cpyRes?"TRUE":"FALSE"):$cpyRes)]]);
                            $cpyData=null;
                            $dpyData=null;
                        }
                    }
                }
            }
            if (!isset($cpyData)) $cpyData=null;
            if (!isset($dpyData)) $dpyData=null;
            if ((!isset($cpyData) && !isset($dpyData))||isset($nonData[0]["id"])) {
                $this->reloadCPData($icpData, $cpyData, $dpyData);
            }
        } finally {
        }
    }
    private function getCfdiObj($filePath) {
        try {
            global $sysPath;
            if (!isset($sysPath[0])) {
                $sysPath = $_SERVER["DOCUMENT_ROOT"];
            }
            $this->fixedIdList["Pasos"][]="INI getCfdiObj";
            require_once "clases/CFDI.php";
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$filePath);
            if (!isset($cfdiObj)) throw new CPException("CFDI inválido", 10000004, CFDI::$lastException, ["sysPath"=>$sysPath,"filePath"=>$filePath,"lastError"=>CFDI::getLastError()], "pagosErr","CFDI inválido $sysPath.$filePath");
            if (isset(CFDI::$lastException)) throw new CPException("CFDI incorrecto", 10000005, CFDI::$lastException, ["sysPath"=>$sysPath,"filePath"=>$filePath,"cfdiObj"=>$cfdiObj], "pagosErr","CFDI incorrecto $sysPath.$filePath");
            return $cfdiObj;
        } finally {
        }
    }
    //public $data=["inv"=>[],"icp"=>[],"cpy"=>[],"dpy"=>[],"cpyc"=>[],"dpyf"=>[]];
    private function loadCfdiData($icpData, &$cpyData, &$dpyData) {
        global $query;
        $this->fixedIdList["Pasos"][]="INI loadCfdiData";
        $cpyData=[];
        $dpyData=[];
        foreach ($icpData as $icpIdx => $icpValue) {
            $cfdiObj=$this->getCfdiObj($icpValue["ubicacion"].$icpValue["nombreInterno"].".xml");
            $pagos = $cfdiObj->get("pagos");
            if (isset($pagos["@fechapago"])) $pagos=[$pagos];
            $idCFDI=$icpValue["id"];
            $docList=[];
            $fechaReciboPago=null;
            $saldoReciboPago=$cfdiObj->get("pago_monto_total");
            foreach ($pagos as $pgIdx => $pgItem) {
                // ToDo: Agregar: else if (esMenor) o if (esMayor)... definir si sería mejor guardar la fecha mayor o la menor... Aunque primero habría que encontrar cuantos casos tienen más de una fecha de pago por comprobante de pago....
                if (is_null($fechaReciboPago)) $fechaReciboPago=$pgItem["@fechapago"];
                if (is_null($saldoReciboPago)) $saldoReciboPago=$pgItem["@monto"];
                $cpgArr=["idCPago"=>$idCFDI,"fechaPago"=>$pgItem["@fechapago"],"montoPago"=>$pgItem["@monto"],"monedaPago"=>$pgItem["@monedap"],"tipocambioPago"=>$pgItem["tipocambiop"]??1];
                if (!$this->saveObjData("CPY",$cpgArr)) {
                    if (DBi::getErrno()>0) {
                        $errData=["idCP"=>$idCFDI,"cpData"=>$icpValue,"query"=>$query];
                        $errObj=$this->errObjData("CPY");
                        if (isset($errObj)) $errData["oerrors"]=$errObj;
                        $errObj=DBi::$errors??null;
                        if (isset($errObj)) $errData["ierrors"]=$errObj;
                        throw new CPException("Error al guardar Pagos/Pago en CPago", 10000006, null, $errData, "pagosErr", "Error al actualizar pagos");
                    } else {
                        doclog("NOTHING TO SAVECP","pagos",["cpgArr"=>$cpgArr,"query"=>$query,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx]);
                    }
                }
                $cpgArr["id"]=$this->lastId;
                doclog("SAVEDCP","pagos",["cpgArr"=>$cpgArr,"query"=>$query,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx]);
                $this->addData([$cpgArr],"idCPago","cpyc");
                $this->addData([$cpgArr],"id","cpy");
                $cpyData[]=$cpgArr;

                $docsRel=$pgItem["DoctoRelacionado"]??null;
                if (isset($docsRel["@iddocumento"])) $docsRel=[$docsRel];
                foreach ($docsRel as $drIdx => $drItem) {
                    $pgUUID=strtoupper($drItem["@iddocumento"]);
                    if (!isset($docList[$pgUUID])) {
                        $docData=$this->getObjData("INV",["uuid='$pgUUID'"]);
                        if (isset($docData[0]["id"])) $docData=$docData[0];
                        if (!isset($docData["id"])) throw new CPException("Falta factura en complemento de pago", 10000007, null,["idCP"=>$idCFDI,"cpData"=>$icpValue,"doctoRelacionado"=>$drItem,"query"=>$query,"docData"=>$docData],"pagosErr","Falta dar de alta factura con uuid '$pgUUID'");
                        $docList[$pgUUID]=$docData;
                    } else $docData=$docList[$pgUUID];
                    $docId=$docData["id"];
                    $dpgArr=["idPPago"=>$cpgArr["id"],"idFactura"=>$docId,"numParcialidad"=>$drItem["@numparcialidad"],"saldoAnterior"=>$drItem["@impsaldoant"],"impPagado"=>$drItem["@imppagado"],"saldoInsoluto"=>$drItem["@impsaldoinsoluto"],"moneda"=>$drItem["@monedadr"],"equivalencia"=>$drItem["@equivalenciadr"]??1];
                    if (!$this->saveObjData("DPY",$dpgArr)) {
                        if (DBi::getErrno()>0) {
                            $errData=["idCP"=>$idCFDI,"cpData"=>$icpValue,"query"=>$query];
                            $errObj=$this->errObjData("DPY");
                            if (isset($errObj)) $errData["oerrors"]=$errObj;
                            $errObj=DBi::$errors??null;
                            if (isset($errObj)) $errData["ierrors"]=$errObj;
                            throw new CPException("Error al guardar Pagos/Pago/DoctoRelacionado en DPago", 10000008, null, $errData, "pagosErr", "Error al actualizar pagos");
                        } else {
                            doclog("NOTHING TO SAVEDDP","pagos",["query"=>$query,"dpgArr"=>$dpgArr,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx,"drIdx"=>$drIdx]);
                        }
                    }
                    global $dpyObj;
                    $dpgArr["id"]=$dpyObj->lastId;
                    doclog("SAVEDDP","pagos",["dpgArr"=>$dpgArr,"query"=>$query,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx,"drIdx"=>$drIdx]);
                    $this->addData([$dpgArr],"idFactura","dpyf");
                    $this->addData([$dpgArr],"idPPago","dpyp");
                    $this->addData([$dpgArr],"id","dpy");
                    $dpyData[]=$dpgArr;
                }
            }
        }
    }
    private function adjustCPData($icpData, $cpyData, $dpyData) {
        foreach ($icpData as $icpIdx => $icpValue) {
            $invFldArr=["id"=>$idCFDI, "fechaReciboPago"=>$fechaReciboPago, "saldoReciboPago"=>$saldoReciboPago];
            require_once "clases/DBPDO.php";
            if (DBPDO::validaAceptacion($icpValue["rfcGrupo"], $fechaReciboPago, $saldoReciboPago)) {
                $statusn=$icpValue["statusn"]??0;
                if (empty($statusn)) $statusn=0;
                $statusn|=Facturas::STATUS_ACEPTADO;
                if ($statusn!==$icpValue["statusn"]) {
                    $invFldArr["statusn"]=$statusn;
                    $invFldArr["status"]=Facturas::statusnToDetailStatus($statusn,"P");
                }
            } // else // Casos para rechazar automaticamente
            if (!$this->saveObjData("INV",$invFldArr)) {
                if (DBi::getErrno()>0) {
                    $errData=["idCP"=>$idCFDI,"cpData"=>$icpValue,"query"=>$query];
                    $errObj=$this->errObjData("INV");
                    if (isset($errObj)) $errData["oerrors"]=$errObj;
                    $errObj=DBi::$errors??null;
                    if (isset($errObj)) $errData["ierrors"]=$errObj;
                    throw new CPException("Error al guardar Complemento de Pago en Facturas", 10000009, null, $errData, "pagosErr", "Error al actualizar pagos");
                } else {
                    doclog("NOTHING TO SAVE InvCP","pagos",["query"=>$query,"icpIdx"=>$icpIdx]);
                }
            } else
                doclog("SAVED InvCP","pagos",["query"=>$query,"icpIdx"=>$icpIdx]);
        }
    }
    public function reloadData() { // ToDo: Usar $this->data en lugar de argumentos de la función icp|cpy|dpy
        global $query;
        try {
            $this->fixedIdList["Pasos"][]="INI reloadData";
            if ($this->hasData("icp")) {
                foreach ($this->data["icp"] as $icpIdx=>$icpValue) {
                    $cfdiObj=$this->getCfdiObj($icpValue["ubicacion"].$icpValue["nombreInterno"].".xml");
                    $pagos = $cfdiObj->get("pagos");
                    if (isset($pagos["@fechapago"])) $pagos=[$pagos];
                    $idCFDI=$icpValue["id"];
                    $docList=[];
                    $fechaReciboPago=null;
                    $saldoReciboPago=$cfdiObj->get("pago_monto_total");
                    foreach ($pagos as $pgIdx => $pgItem) {
                        if (is_null($fechaReciboPago)) $fechaReciboPago=$pgItem["@fechapago"];
                        if (is_null($saldoReciboPago)) $saldoReciboPago=$pgItem["@monto"];
                        $cpgArr=["idCPago"=>$idCFDI,"fechaPago"=>$pgItem["@fechapago"],"montoPago"=>$pgItem["@monto"],"monedaPago"=>$pgItem["@monedap"],"tipocambioPago"=>$pgItem["tipocambiop"]??1];
                        if (isset($this->data["cpyc"][$idCFDI]))
                        if ($this->saveObjData("CPY",$cpgArr)) {
                            $cpgArr["id"]=$this->lastId;
                            $this->addData([$cpgArr],"idCPago","cpyc");
                            $this->addData([$cpgArr],"id","cpy");
                        }
                    }
                }
            }
        } finally {
        }
        //$this->setInvoicesStatus($icpData, $cpyData, $dpyData);
        //$this->setInvoiceData();
    }
    private function reloadCPData($icpData, &$cpyData, &$dpyData) {
        global $query;
        $this->fixedIdList["Pasos"][]="INI reloadCPData";
        if (is_null($cpyData)) $cpyData=[];
        if (is_null($dpyData)) $dpyData=[];
        $currIdx=-1;
        foreach ($icpData as $icpIdx => $icpValue) {
            $currIdx=$icpIdx;
            $icpId=$icpValue["id"];
            $icpXml="$icpValue[ubicacion]$icpValue[nombreInterno].xml";
            $icpPfx="#$icpIdx id:$icpId";
            $icpExtra="tc:$icpValue[tipoComprobante] folio:$icpValue[folio] codPrv:$icpValue[codigoProveedor]";
            try {
                $tmstmp = date("His");
                $this->fixedIdList["Pasos"][]="[$tmstmp] ICP $icpPfx $icpExtra $icpXml";
                $cfdiObj=$this->getCfdiObj($icpXml);
                $pagos = $cfdiObj->get("pagos");
                if (isset($pagos["@fechapago"])) $pagos=[$pagos];
                
                $docList=[];
                $fechaReciboPago=null;
                $saldoReciboPago=$cfdiObj->get("pago_monto_total");
                foreach ($pagos as $pgIdx => $pgItem) {
                    // ToDo: Agregar: else if (esMenor) o if (esMayor)... definir si sería mejor guardar la fecha mayor o la menor... Aunque primero habría que encontrar cuantos casos tienen más de una fecha de pago por comprobante de pago....
                    if (is_null($fechaReciboPago)) $fechaReciboPago=$pgItem["@fechapago"];
                    if (is_null($saldoReciboPago)) $saldoReciboPago=$pgItem["@monto"];
                    $cpgArr=["idCPago"=>$icpId,"fechaPago"=>$pgItem["@fechapago"],"montoPago"=>$pgItem["@monto"],"monedaPago"=>$pgItem["@monedap"],"tipocambioPago"=>$pgItem["tipocambiop"]??1];
                    if (!$this->saveObjData("CPY",$cpgArr)) {
                        if (DBi::getErrno()>0) {
                            $errData=["idCP"=>$icpId,"cpData"=>$icpValue,"query"=>$query];
                            $errObj=$this->errObjData("CPY");
                            if (isset($errObj)) $errData["oerrors"]=$errObj;
                            $errObj=DBi::$errors??null;
                            if (isset($errObj)) $errData["ierrors"]=$errObj;
                            throw new CPException("Error al guardar Pagos/Pago en CPago", 10000006, null, $errData, "pagosErr", "Error al actualizar pagos");
                        } else {
                            doclog("NOTHING TO SAVECP","pagos",["cpgArr"=>$cpgArr,"query"=>$query,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx]);
                        }
                    }
                    $cpgArr["id"]=$this->lastId;
                    doclog("SAVEDCP","pagos",["cpgArr"=>$cpgArr,"query"=>$query,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx]);
                    $this->addData([$cpgArr],"idCPago","cpyc");
                    $this->addData([$cpgArr],"id","cpy");
                    $cpyData[]=$cpgArr;

                    $docsRel=$pgItem["DoctoRelacionado"]??null;
                    if (isset($docsRel["@iddocumento"])) $docsRel=[$docsRel];
                    foreach ($docsRel as $drIdx => $drItem) {
                        $pgUUID=strtoupper($drItem["@iddocumento"]);
                        if (!isset($docList[$pgUUID])) {
                            $docData=$this->getObjData("INV",["uuid='$pgUUID'"]);
                            if (isset($docData[0]["id"])) $docData=$docData[0];
                            if (!isset($docData["id"])) throw new CPException("Falta factura en complemento de pago", 10000007, null,["idCP"=>$icpId,"cpData"=>$icpValue,"doctoRelacionado"=>$drItem,"query"=>$query,"docData"=>$docData],"pagosErr","Falta dar de alta factura con uuid '$pgUUID'");
                            $docList[$pgUUID]=$docData;
                        } else $docData=$docList[$pgUUID];
                        $docId=$docData["id"];
                        $dpgArr=["idPPago"=>$cpgArr["id"],"idFactura"=>$docId,"numParcialidad"=>$drItem["@numparcialidad"],"saldoAnterior"=>$drItem["@impsaldoant"],"impPagado"=>$drItem["@imppagado"],"saldoInsoluto"=>$drItem["@impsaldoinsoluto"],"moneda"=>$drItem["@monedadr"],"equivalencia"=>$drItem["@equivalenciadr"]??1];
                        if (!$this->saveObjData("DPY",$dpgArr)) {
                            if (DBi::getErrno()>0) {
                                $errData=["idCP"=>$icpId,"cpData"=>$icpValue,"query"=>$query];
                                $errObj=$this->errObjData("DPY");
                                if (isset($errObj)) $errData["oerrors"]=$errObj;
                                $errObj=DBi::$errors??null;
                                if (isset($errObj)) $errData["ierrors"]=$errObj;
                                throw new CPException("Error al guardar Pagos/Pago/DoctoRelacionado en DPago", 10000008, null, $errData, "pagosErr", "Error al actualizar pagos");
                            } else {
                                doclog("NOTHING TO SAVEDDP","pagos",["query"=>$query,"dpgArr"=>$dpgArr,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx,"drIdx"=>$drIdx]);
                            }
                        }
                        global $dpyObj;
                        $dpgArr["id"]=$dpyObj->lastId;
                        doclog("SAVEDDP","pagos",["dpgArr"=>$dpgArr,"query"=>$query,"icpIdx"=>$icpIdx,"pgIdx"=>$pgIdx,"drIdx"=>$drIdx]);
                        $this->addData([$dpgArr],"idFactura","dpyf");
                        $this->addData([$dpgArr],"idPPago","dpyp");
                        $this->addData([$dpgArr],"id","dpy");
                        $dpyData[]=$dpgArr;
                    }
                }
                $this->fixedIdList["Pasos"][array_key_last($this->fixedIdList["Pasos"])].=": OK!";
            } catch (CPException $cpex) {
                $this->fixedIdList["Pasos"][array_key_last($this->fixedIdList["Pasos"])].=": ERROR ".$cpex->getCode();
                if ($cpex->getCode()===10000004 || $cpex->getCode()===10000005) {
                    $nm=$cpex->getName();
                    $isPym = (substr($nm, 0, 5)==="pagos");
                    $isErr = ($nm==="error");
                    $cpex->addData(["icpIdx"=>$icpIdx, "icpData"=>$icpValue]);
                    $cpex->doLog($isPym?$cpex->getMessage():"reloadCPData ".($isErr?"Failed":"Error"));
                    $tmstmp = date("His");
                    $this->fixedIdList["Errores"][]="[$tmstmp] Failed CFDI Load ".$cpex->getCode().": $icpPfx ".$cpex->getMessage();
                    continue;
                } else throw $cpex;
            }
            $invFldArr=["id"=>$icpId, "fechaReciboPago"=>$fechaReciboPago, "saldoReciboPago"=>$saldoReciboPago];
            require_once "clases/DBPDO.php";
            if (DBPDO::validaAceptacion($icpValue["rfcGrupo"], $fechaReciboPago, $saldoReciboPago)) {
                $statusn=$icpValue["statusn"]??0;
                if (empty($statusn)) $statusn=0;
                $statusn|=Facturas::STATUS_ACEPTADO;
                if ($statusn!==$icpValue["statusn"]) {
                    $invFldArr["statusn"]=$statusn;
                    $invFldArr["status"]=Facturas::statusnToDetailStatus($statusn,"P");
                }
            } // else // Casos para rechazar automaticamente
            if (!$this->saveObjData("INV",$invFldArr)) {
                if (DBi::getErrno()>0) {
                    $errData=["idCP"=>$icpId,"cpData"=>$icpValue,"query"=>$query];
                    $errObj=$this->errObjData("INV");
                    if (isset($errObj)) $errData["oerrors"]=$errObj;
                    $errObj=DBi::$errors??null;
                    if (isset($errObj)) $errData["ierrors"]=$errObj;
                    throw new CPException("Error al guardar Complemento de Pago en Facturas", 10000009, null, $errData, "pagosErr", "Error al actualizar pagos");
                } else {
                    doclog("NOTHING TO SAVE InvCP","pagos",["query"=>$query,"icpIdx"=>$icpIdx]);
                }
            } else
                doclog("SAVED InvCP","pagos",["query"=>$query,"icpIdx"=>$icpIdx]);
        }
    }
    private function setInvoicesStatusNew() {
        global $query;
        // ToDo: Obten inv que no estén en dpyf: guardar idReciboPago=null, saldoReciboPago=null, fechaReciboPago=null, statusReciboPago=null
        foreach ($this->data["inv"] as $key => $value) {
            if (!isset($this->data["dpyf"][$key])) {
                if (!$this->saveObjData("INV",["id"=>$key,"idReciboPago"=>null,"fechaReciboPago"=>null,"saldoReciboPago"=>null,"statusReciboPago"=>null])) {
                    if (DBi::getErrno()>0) {
                        $errData=["invId"=>$key,"invIdList"=>array_keys($this->data["inv"]),"dpyfIdList"=>array_keys($this->data["dpyf"]),"query"=>$query];
                        $errObj=$this->errObjData("INV");
                        if (isset($errObj)) $errData["oerrors"]=$errObj;
                        $errObj=DBi::$errors??null;
                        if (isset($errObj)) $errData["ierrors"]=$errObj;
                        doclog("ERROR AL DESANEXAR FACTURA DEL COMPLEMENTO DE PAGO","pagosErr",$errData);
                    } else {
                        doclog("NADA QUE GUARDAR YA DEBE ESTAR DESANEXADA","pagos",["query"=>$query,"invId"=>$key,"invIdList"=>array_keys($this->data["inv"]),"dpyfIdList"=>array_keys($this->data["dpyf"])]);
                    }
                } else {
                    doclog("FACTURA DESANEXADA DEL COMPLEMENTO DE PAGO","pagos",["invId"=>$key,"invIdList"=>array_keys($this->data["inv"]),"dpyfIdList"=>array_keys($this->data["dpyf"])]);
                }
            }
        }
        // ToDo: Obten dpyf que no estén en inv: No debería ocurrir, no hay registro de las facturas, no deberían existir en dpyf. Marcar error: Falta dar de alta las facturas
        // ToDo: Para las facturas que si existan en ambos ejecutar ciclo que inicia en linea 617
    }
    private function setInvoicesStatus($icpData, $cpyData, $dpyData) {
        global $query;
        try {
            $this->fixedIdList["Pasos"][]="INI setInvoicesStatus";
            $icpIds=array_column($icpData, "id");
            $icpMap=array_combine($icpIds, $icpData);
            $idCFDIs="";
            if(isset($icpIds[1])) $idCFDIs=" in (".implode(", ", $icpIds).")";
            else if (isset($icpIds[0])) $idCFDIs="=".$icpIds[0];
            else throw new CPException("No hay datos", 10000010, null, ["icpIds"=>$icpIds,"data"=>$icpData],"pagosErr","No se indicaron comprobantes de pago");

            $invData=$this->getObjData("INV",["idReciboPago{$idCFDIs}"]);
            $invWRPIds=[]; // id list from Facturas With matching idReciboPago value
            $invWRPMap=[]; // relation by id of invoice data With matching idReciboPago value
            $invRPIds=[];  // idReciboPago list from Facturas data (invData)
            $invRPIMap=[]; // relation by idReciboPago of ids from invoice data with that value
            foreach ($invData as $invKey => $invValue) {
                $invValId=$invValue["id"];
                $invRPId=$invValue["idReciboPago"];
                $invWRPIds[]=$invValId;
                $invWRPMap[$invValId]=$invValue;
                if (in_array($invRPId, $invRPIds)) {
                    $invRPIMap[$invRPId][]=$invValId;
                } else {
                    $invRPIds[]=$invRPId;
                    $invRPIMap[$invRPId]=[$invValId];
                }
            }
            $ippCpyIds=array_column($cpyData, "id"); // id list from CPagos rows
            $ippCpyMap=array_combine($ippCpyIds, $cpyData); // relation by id of CPagos data
            $icpCpyMap=[]; // relation by idCPago of CPagos data
            foreach ($cpyData as $cpyIdx =>$cpyValue) {
                $idCPago=$cpyValue["idCPago"];
                if (!isset($icpMap[$idCPago])) {
                    $idPPago=$cpyValue["id"]; $ippCpyIds = array_diff($ippCpyIds, [$idPPago]);
                    $ippCpyItm=$ippCpyMap[$idPPago]; unset($ippCpyMap[$idPPago]);
                    doclog("Removed CP","pagos",["cpyIdx"=>$cpyIdx,"cpyValue"=>$cpyValue,"idCPago"=>$idCPago,"idPPago"=>$idPPago,"removed"=>$ippCpyItm]); continue;
                }
                if (!isset($icpCpyMap[$idCPago])) $icpCpyMap[$idCPago]=[$cpyIdx];
                else $icpCpyMap[$idCPago][]=$cpyIdx;
            }
            $invIds=[]; // idFactura list from DPagos data (dpyData)
            $invDpyMap=[]; // relation by idFactura of DPagos data
            $ippDpyMap=[]; // relation by idPPagos of DPagos data
            foreach ($dpyData as $dpyIdx => $dpyValue) {
                $idPPago=$dpyValue["idPPago"];
                if (!isset($ippCpyMap[$idPPago])) {
                    doclog("Not found DP","pagos",["dpyIdx"=>$dpyIdx,"dpyValue"=>$dpyValue]); continue;
                }
                $idFactura=$dpyValue["idFactura"];
                if (!isset($invDpyMap[$idFactura])) {
                    $invIds[]=$idFactura; $invDpyMap[$idFactura]=[$dpyIdx];
                } else $invDpyMap[$idFactura][]=$dpyIdx;
                if (!isset($ippDpyMap[$idPPago])) $ippDpyMap[$idPPago]=[$dpyIdx];
                else $ippDpyMap[$idPPago][]=$dpyIdx;
            }
            // Using all relations generated above to update Facturas fields
            // idReciboPago: On each invIds found, from dpyData get idPPago value, use it to get related idCPago. Get only those with statusn >=1(ACEPTADO) and <128(RECHAZADO). If retrieved none, filter again but get only those with statusn=0(PENDIENTE). If retrieved none, filter again but get only those with statusn>=128(RECHAZADO). If retrieved none set this field and all the others to null. In this field save the highest value from those found, but keep the found list to set value on next fields.
            // fechaReciboPago: From found list, set the lastest one value.
            // statusReciboPago: If found list have statusn ACEPTADO set this to 1, if statusn is PENDIENTE set this to 0, if statusn is RECHAZADO set this to -1, else set this to null
            // saldoReciboPago: If statusReciboPago=1, sum all paid amounts. From current invoice data get total and substract previous sum, set here the result obtained. Else set here the total value.
            foreach ($invIds as $invIdKy => $invIdVal) {
                if (isset($invWRPMap[$invIdVal])) $invItem=$invWRPMap[$invIdVal];
                else {
                    //$this->fixedIdList["Pasos"][]="GET-INV: id=$invIdVal";
                    $invItem=$this->getObjData("INV",["id=$invIdVal"]);
                    if ($invItem[0]["id"]) {
                        $invItem=$invItem[0];
                        doclog("Checking NonRegistered Invoice","pagos",["invIds"=>$invIds,"invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invWRPIds"=>$invWRPIds,"invItem"=>$invItem]);
                    } else {
                        $invItem=null;
                        doclog("Not found NonRegistered Invoice","pagosErr",["invIds"=>$invIds,"invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal]);
                        continue;
                    }
                }

                if (isset($invDpyMap[$invIdVal])) {
                    $iiStn=$invItem["statusn"]??-1; // -1 para no incluir status temporal
                    $dpyIdxs=$invDpyMap[$invIdVal];
                    if ($iiStn<0) continue; // ignorar
                    if ($iiStn>=Facturas::STATUS_RECHAZADO) {
                        doclog("Invoice cancelled!","pagosErr",["invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invItem"=>$invItem,"dpyRelated"=>$dpyIdxs]);
                        // ToDo: Como debe tratarse el complemento de pago, debe cancelarse o se ignora la factura, debería ajustarse pues el total cambia, pero debe haber un proceso intermedio...
                    } else if (($iiStn&1)==0) doclog("Invoice not accepted".($iiStn>=Facturas::STATUS_PAGADO?" but Paid":""),"pagosErr",["invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invItem"=>$invItem,"dpyRelated"=>$dpyIdxs]);
                    else {
                        $invTot=+$invItem["total"];
                        $dpySum=0; $lastDP=null; $lastCP=null; $chkCP=null; $badCP=null;
                        foreach ($dpyIdxs as $diKey => $diVal) {
                            $dpyItem=$dpyData[$diVal];
                            $dpyIdPP=$dpyItem["idPPago"];
                            $cpyItem=$ippCpyMap[$dpyIdPP];
                            $cpyIdCP=$cpyItem["idCPago"];
                            $cpfItem=$icpMap[$cpyIdCP];
                            $cpfStt=+($cpfItem["statusn"]??0);
                            if ($cpfStt<0) continue;
                            if ($cpfStt>=Facturas::STATUS_RECHAZADO) {
                                if (!isset($badCP)) $badCP=$cpyItem;
                            } else if (($cpfStt&1)==0){
                                if (!isset($chkCP)) $chkCP=$cpyItem;
                            } else {
                                $cpfStt=+$cpfStt;
                                $dpyNumPar=+$dpyItem["numParcialidad"];
                                $cpyPyDt=$cpyItem["fechaPago"];
                                $lastNumPar=isset($lastDP)?(+$lastDP["numParcialidad"]):null;
                                $lastPyDt=isset($lastCP)?$lastCP["fechaPago"]:null;
                                if (!isset($lastNumPar) || $dpyNumPar>$lastNumPar || ($dpyNumPar==$lastNumPar && (!isset($lastPyDt) || $cpyPyDt>$lastPyDt))) {
                                    $lastCP=$cpyItem;
                                    $lastDP=$dpyItem;
                                }
                                $dpySum+=$dpyItem["impPagado"];
                            }
                        }
                        $saldoRP=$invTot-$dpySum;
                        if ($saldoRP>0) {
                            if ($dpySum>0) {
                                if (isset($chkCP)) $lastCP=$chkCP;
                            } else {
                                if (isset($badCP)) $lastCP=$badCP;
                            }
                        }
                        if ($dpySum==0 && isset($badCP)) $lastCP=$badCP;
                        if (isset($lastCP)) { // ToDo: Por ahora solo cambiar los que tienen complementos de pago validos
                            doclog(($saldoRP==0)?"!!PAGADA!!":($saldoRP<0?"!!!EXCEDIDO!!!":($dpySum>0?"PARCIAL!!":"CERO|IGNORADA")),"pagos",["invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invItem"=>$invItem,"dpyRelated"=>$dpyIdxs,"dpySum"=>$dpySum,"invTot"=>$invTot,"saldoRP"=>$saldoRP,"lastCP"=>$lastCP,"statusRP"=>($lastCP===$badCP?-1:($lastCP===$chkCP?0:1))]);
                            $invFldArr=["id"=>$invIdVal,"idReciboPago"=>$lastCP["idCPago"],"fechaReciboPago"=>$lastCP["fechaPago"],"saldoReciboPago"=>$saldoRP>0?$saldoRP:0,"statusReciboPago"=>($lastCP===$badCP?-1:($lastCP===$chkCP?0:1))];
                            if ($saldoRP<=0) {
                                $invFldArr["statusn"]=$iiStn|Facturas::STATUS_ACEPTADO|Facturas::STATUS_PAGADO;
                                $invFldArr["status"]=Facturas::statusnToDetailStatus($invFldArr["statusn"]);
                            }
                            if (!$this->saveObjData("INV",$invFldArr)) {
                                if (DBi::getErrno()>0) {
                                    $errData=["idCP"=>$idCFDI,"cpData"=>$icpValue,"query"=>$query];
                                    $errObj=$this->errObjData("INV");
                                    if (isset($errObj)) $errData["oerrors"]=$errObj;
                                    $errObj=DBi::$errors??null;
                                    if (isset($errObj)) $errData["ierrors"]=$errObj;
                                    doclog("ERROR AL ACTUALIZAR FACTURA","pagosErr",$errData);
                                } else {
                                    doclog("NADA QUE ACTUALIZAR","pagos",["query"=>$query,"invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invItem"=>$invItem,"dpyRelated"=>$dpyIdxs,"dpySum"=>$dpySum,"invTot"=>$invTot,"saldoRP"=>$saldoRP]);
                                }
                            }
                        } else doclog("BORRADA o DESAPARECIDA!!","pagos",["invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invItem"=>$invItem,"dpyRelated"=>$dpyIdxs,"dpySum"=>$dpySum,"invTot"=>$invTot,"saldoRP"=>$saldoRP]);
                    }
                } else doclog("Inv Not Found","pagosErr",["invIdKy"=>$invIdKy,"invIdVal"=>$invIdVal,"invItem"=>$invItem]);
            }
        } finally {
        }
    }
}
class CPException extends Exception {
    private $data;
    private $name;
    private $userMessage;
    public function __construct($message, $code=0, Exception $previous=null, $data=null, $name="error",$description=null) {
        parent::__construct($message, $code, $previous);
        if (empty($data)) $this->data=[];
        else if (!is_array($data)||!array_filter(array_keys($data), 'is_string')) $this->data=["data"=>$data];
        else $this->data=$data;
        if (empty($name)) $name="error";
        $this->name=$name;
        if (!empty($description)) $this->userMessage=$description;
    }
    public function getData() {
        return $this->data;
    }
    public function addData($extraData) {
        $this->data=$this->data+$extraData;
    }
    public function getName() {
        return $this->name;
    }
    public function getUserMessage() {
        if (empty($this->userMessage)) return $this->getMessage();
        return $this->userMessage;
    }
    public function __toString() {
        return __CLASS__.": [{$this->code}];: {$this->message}\n";
    }
    public function doLog($message=null) {
        $this->data["error"]=getErrorData($this);
        doclog($message,$this->getName(),$this->getData());
    }
}
