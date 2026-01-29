<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/SolicitudPago.php";

$solObj = new SolicitudPago();
if (isValueService()) getValueService($solObj);
else if (isTestService()) getTestService($solObj);
else if (isCatalogService()) getCatalogService($solObj);
else if (isActionService()) doActionService();
die();
function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    global $actObj,$prcObj,$solObj;
    sessionInit();
    if (!hasUser()) {
        echoJsNDie("refresh","No User");
        //echo "REFRESH";
        //die();
    }
    global $query;
    $queries=[];
    switch($_POST["action"]??"") {
        case "findCounterForRequest":
            $folio=$_POST["folio"]??"";
            $gpoId=$_POST["gpoId"]??0;
            if ($gpoId>0) {
                require_once "clases/Grupo.php";
                $gpoObj=new Grupo();
                $gpoData=$gpoObj->getData("id=$gpoId",0,"alias");
                if (isset($gpoData[0]["alias"][0])) $alias=$gpoData[0]["alias"];
            }
            if (isset($folio[0])) {
                require_once "clases/Contrarrecibos.php";
                $ctrObj=new Contrarrecibos();
                $ctrObj->rows_per_page=0;
                $ctrWhr="folio='$folio'";
                if (isset($alias[0])) $ctrWhr.=" and aliasGrupo='$alias'";
                $ctrData=$ctrObj->getData($ctrWhr);
                global $query;
                if (!isset($ctrData[0])) errNDie("No se encontró contra recibo con ese folio",["code"=>0,"query"=>$query]);
                if (isset($ctrData[1])) errNDie("Varios contra recibos coinciden con ese folio",["code"=>1,"query"=>$query,"aliases"=>array_column($ctrData, "aliasGrupo")]);
                $ctrData=$ctrData[0];
                if ($ctrData["numAutorizadas"]==0) errNDie("El contra recibo debe tener facturas autorizadas por pagar.",["code"=>2,"contra"=>$ctrData,"query"=>$query]);
                if ($solObj->exists("idContrarrecibo=$ctrData[id]")) errNDie("El contra recibo ya ha sido asignado a una Solicitud de Pago",["code"=>3,"query"=>$query,"idContrarrecibo"=>$ctrData["id"]]);
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
                $invObj->rows_per_page=0;
                $invData=$invObj->getData("id in (select idFactura from contrafacturas where idContrarrecibo={$ctrData['id']} and autorizadaPor is not null)",0,"id,fechaFactura,fechaCaptura,folio,serie,uuid,pedido,total,tipoComprobante,moneda,ciclo,ubicacion,nombreInterno,nombreInternoPDF,ea,status,statusn");
                $idList=implode(",",array_column($invData, "id"));
                if ($solObj->exists("idFactura in ($idList)")) errNDie("Ya existe una solicitud de pago relacionada",["code"=>4,"query"=>$query,"idContrarrecibo"=>$ctrData["id"],"idFactura"=>$idList]);
                $npaid=0;
                $ncanceled=0;
                $validId=[];
                foreach ($invData as $idx => $row) {
                    if (!isset($row["statusn"])) continue;
                    $sttn=$row["statusn"];
                    if ($sttn<Facturas::STATUS_ACEPTADO) continue;
                    if ($sttn>=Facturas::STATUS_RECHAZADO) $ncanceled++;
                    else if ($sttn>=Facturas::STATUS_PAGADO) $npaid++;
                    $validId[]=$row["id"];
                }
                if (!isset($validId[0])) {
                    if ($npaid>0) errNDie("Las facturas del contra recibo ya están pagadas",["code"=>3,"contra"=>$ctrData,"facturas"=>$invData]);
                    if ($ncanceled>0) errNDie("Las facturas del contra recibo están canceladas",["code"=>4,"contra"=>$ctrData,"facturas"=>$invData]);
                }
            }
            //errNDie("Resultado Funcional interrumpido",["contra"=>$ctrData,"validas"=>$validId,"facturas"=>$invData]);
            // toDo: Se debe generar una solicitud con el contra recibo. Se incluirá el folio de la solicitud
            // toDo: En tabla contrafacturas agregar campo solId null, al asignar contra recibo a la nueva solicitud, guardar id de la solicitud y posteriormente asignar el id de la nueva solicitud en este campo de cada contrafactura valida
            successNDie("Contra recibo aceptado",["contra"=>$ctrData,"validas"=>$validId,"facturas"=>$invData]);
        break;
        case "requestPaymentDirect": doReqPaymDirect(); break;
        case "setCleanPaid": doSetAlreadyPaid(); break;
        default: echo "ERROR:Petición inválida ($_POST[action])";
    }
}
function doSetAlreadyPaid() {
    global $query,$solObj,$prcObj;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>getUser()->nombre,"POST"=>$_POST];
    $solId=$_POST["solId"];
    // status between 64 and 127
    // proceso <= 4
    // idFactura is null
    // f.codigoProveedor is null or not in (I-998,I-999)
    $solData=$solObj->getData("id=$solId and idFactura is null",0,"id,status,proceso");
    if (!isset($solData[0]["id"])) errNDie("No se localiza la solicitud $solId sin factura",$baseData+["line"=>__LINE__,"query"=>$query],"solpago");
    $solData=$solData[0];
    $status=+$solData["status"];
    if ($status<64) errNDie("La factura debe estar pagada",$baseData+["line"=>__LINE__,"query"=>$query]+$solData,"solpago");
    if ($status>127) errNDie("La factura no debe estar cancelada",$baseData+["line"=>__LINE__,"query"=>$query]+$solData,"solpago");
    $proceso=+$solData["proceso"];
    if ($proceso>4) errNDie("El proceso no es adecuado para marcar como pagada sin necesidad de factura",$baseData+["line"=>__LINE__,"query"=>$query]+$solData,"solpago");
    if (!$solObj->saveRecord(["id"=>$solId,"proceso"=>5])) errNDie("No se pudo guardar la solicitud",$baseData+["line"=>__LINE__,"query"=>$query,"error"=>$solObj->errors],"solpago");
    if (!isset($firObj)) {
        require_once "clases/Firmas.php";
        $firObj = new Firmas();
    }
    if (!$firObj->saveRecord(["idUsuario"=>getUser()->id,"modulo"=>"solpago","idReferencia"=>$solId,"accion"=>"completa","motivo"=>$solFolio])) {
        doclog("Falló la firma para marcar pagada la solicitud","error",$baseData+["line"=>__LINE__,"query"=>$query,"error"=>$firObj->errors]);
    }
    if (!isset($prcObj)) {
        require_once "clases/Proceso.php";
        $prcObj = new Proceso();
    }
    if (!$prcObj->saveRecord()) {
        doclog("Falló el registro de proceso para marcar pagada la solicitud","error",$baseData+["line"=>__LINE__,"query"=>$query,"error"=>$prcObj->errors]);
    }
    successNDie("Solicitud marcada como pagada sin necesidad de factura");
}
function doReqPaymDirect() {
    $userId=getUser()->id;
    $userName=getUser()->nombre;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"usuario"=>$userName,"POST"=>$_POST];
    // EXTRAE DATOS
    $inidate=$_POST["inidate"]??"";
    if (!isset($inidate[0]))
        errNDie("Falta la fecha de solicitud",$baseData+["line"=>__LINE__],"solpago");
    $dbinidate=reverseDate($inidate,"/","-");
    $paydate=$_POST["paydate"]??"";
    if (!isset($paydate[0]))
        errNDie("Falta la fecha de pago",$baseData+["line"=>__LINE__],"solpago");
    $dbpaydate=reverseDate($paydate,"/","-");
    $counterList=$_POST["counterList"]??[];
    doclog("counterList","solfolio",$baseData+["line"=>__LINE__,"list"=>$counterList]);
    global $ctrObj,$query,$solObj,$firObj;
    if (!isset($ctrObj)) {
        require_once "clases/Contrarrecibos.php";
        $ctrObj=new Contrarrecibos();
    }
    $ctrObj->rows_per_page=0;
    $errList=[];
    $solList=[];
    $fechaInicio=date("Y-m");
    global $tokObj, $usrObj;
    foreach ($counterList as $ctrId => $ctrVal) {
        $solData=$solObj->getData("idContrarrecibo=$ctrId",0,"id,folio");
        if (isset($solData[0]["id"])) {
            doclog("Ya existe una solicitud de pago","solfolio",$baseData+["line"=>__LINE__,"ctrId"=>$ctrId,"solData"=>$solData[0]]);
            $errList["$ctrId"]="Ya asignado a ".$solData[0]["folio"];
            continue;
        }
        $ctrData=$ctrObj->getData("c.id=$ctrId",0,"g.id gId,g.cut","c inner join grupo g on c.rfcGrupo=g.rfc");
        if (!isset($ctrData[0])) {
            doclog("Contrarrecibo sin empresa","solfolio",$baseData+["line"=>__LINE__,"ctrId"=>$ctrId,"query"=>$query,"errno"=>DBi::getErrno(),"error"=>DBi::getError(),"resultData"=>$ctrData]);
            $errList["$ctrId"]="No se encontró empresa";
            continue;
        }
        $ctrData=$ctrData[0];
        $idGpo=$ctrData["gId"];
        $solData=["fechaPago"=>$dbpaydate,"idContrarrecibo"=>$ctrId,"idUsuario"=>$userId,"idEmpresa"=>$idGpo,"status"=>SolicitudPago::STATUS_CONTRARRECIBO,"proceso"=>SolicitudPago::PROCESO_AUTORIZADA];
        if (isset($ctrVal["obs"][0])) $solData["observaciones"]=$ctrVal["obs"];
        // GENERA FOLIO POR EMPRESA
        $solFolPfx=$ctrData["cut"].substr($fechaInicio,2,2).substr($fechaInicio,5,2);
        $solFolio=false;
        $solTimes=10;
        while($solFolio===false) {
            $solFolio=$solObj->getFolio($solFolPfx, $descResult);
            $callQuery=$query;
            if (!isset($solFolio)||$solFolio===false) {
                $solFolio=false;
                if ($solTimes<=0) {
                    //doclog("Solicitud no generada por falta de folio y reintentos","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"query"=>$callQuery]);
                    $errList["$ctrId"]="Folio no generado";
                    break;
                }
                $solTimes--;
                doclog("Sin folio de solicitud. Reintento $solTimes","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"query"=>$callQuery]);
                continue;
            }
            usleep(rand(5000, 995000));
            if ($solObj->exists("folio='$solFolio'")) {
                $callQuery=$query;
                $solFolio=false;
                if ($solTimes<=0) {
                    //doclog("Solicitudes no generadas por folio ocupado","solfolio",$baseData+["line"=>__LINE__,"solFolio"=>$solFolio,"query"=>$callQuery]);
                    $errList["$ctrId"]="Folio ocupado";
                    break;
                }
                $solTimes--;
                doclog("Folio de solicitud ocupado. Reintento $solTimes","solfolio",$baseData+["line"=>__LINE__,"solFolio"=>$solFolio,"query"=>$callQuery]);
                continue;
            }
        }
        if (isset($solFolio)&&$solFolio!==false)
            $solData["folio"]=$solFolio;
        else {
            $error=$errList["$ctrId"]??"Error no definido";
            doclog("Solicitud no generada por falta de folio","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"solData"=>$solData,"contraId"=>$ctrId,"error"=>$error]);
            continue;
        }
        if (!isset($firObj)) {
            require_once "clases/Firmas.php";
            $firObj = new Firmas();
        }
        $firData=$firObj->getData("modulo='contrarrecibo' and accion='autoriza' and idReferencia=$ctrId",0,"idUsuario");
        if (isset($firData[0]["idUsuario"]))
            $solData["authList"]=$firData[0]["idUsuario"];
        $solId=$solObj->saveRecord($solData);
        $solQuery=$query;
        if ($solId===false) {
            doclog("error","solfolio",$baseData+["line"=>__LINE__,"solPrefix"=>$solFolPfx,"solData"=>$solData,"contraId"=>$ctrId,"query"=>$solQuery,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
            $errList["$ctrId"]="error";
            continue;
        }
        $solList["$ctrId"]=$solFolio;
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $usrData=$usrObj->getData("ug.idGrupo=$idGpo AND ug.idPerfil=".SolicitudPago::PERFIL_PAGA,0,"u.id,u.nombre,u.persona,u.email","u inner join usuarios_grupo ug on u.id=ug.idUsuario");
        $usrIdList=array_column($usrData, "id");
        if(!isset($tokObj)) {
            require_once "clases/Tokens.php";
            $tokObj = new Tokens();
        }
        $tokAList=$tokObj->creaAccion($solId,$usrIdList,"anexaComprobante",null);
        $tokPList=$tokObj->creaAccion($solId,$usrIdList,"procesaPago",1);
        $firObj->saveRecord(["idUsuario"=>$userId,"modulo"=>"contrarrecibo","idReferencia"=>$ctrId,"accion"=>"solicitaPago","motivo"=>$solFolio]);
    }
    $solKeys=array_keys($solList);
    $errKeys=array_keys($errList);
    $s_pl="";
    $es_pl="";
    if (isset($solKeys[0])) {
        if(isset($solKeys[1])) { $s_pl="s"; $es_pl="es"; }
        successNDie("Solicitud{$es_pl} generada{$s_pl} satisfactoriamente",["solList"=>$solList,"errList"=>$errList]);
    }
    if (isset($errKeys[1])) { $s_pl="s"; $es_pl="es"; }
    errNDie("Solicitud{$es_pl} con error",$baseData+["line"=>__LINE__,"errList"=>$errList]);
}
