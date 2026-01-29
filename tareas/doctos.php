<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    if (isset($_POST["action"])) {
        echo json_encode(["result"=>"reload"]);
        return;
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
global $rootPath;
if (!isset($rootPath)) {
    $rootPath = "";
    if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $rootPath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
    else if (!empty($_SERVER['DOCUMENT_ROOT'])) $rootPath = $_SERVER['DOCUMENT_ROOT'];
}
$hasAction=isset($_POST["action"]);
$isAdmin=validaPerfil("Administrador");
if ($hasAction) {
    if (!$isAdmin) {
        echo json_encode(["result"=>"error","message"=>"Usuario inválido"]);
    } else if ($_POST["action"]!=="retrieve") {
        echo json_encode(["result"=>"error","message"=>"Acción inválida"]);
    } else {
        require_once "clases/CFDI.php";
        $lastIndex=$_POST["lastIndex"]??0;
        $page=$_POST["page"]??0;
        global $invObj, $pymObj, $query;
        if (!isset($pymObj)) {
            require_once "clases/Doctos.php";
            $pymObj = new Doctos();
        }
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj = new Facturas();
        }
        $invObj->rows_per_page=20;
        $invObj->pageno=$page;
        $whereStr="tipoComprobante='p'";
        $fieldNames="*";
        $extraFrom="";
        $groupStr="";
        $numExists=0;
        $numErrors=0;
        $numOk=0;
        $numSaved=0;
        $numNoCObj=0;
        $numNoDocs=0;
        $numHasDocs=0;
        $numTryPaym=0;
        $sumData=[];
        $invData = $invObj->getData($whereStr, 0, $fieldNames, $extraFrom, $groupStr);
        $sumData[]="QUERY PAGOS: $query";
        if (isset($invData["id"])) $invData=[$invData];
        $numrows=$invObj->numrows;
        $lastpage=$invObj->lastpage;
        $page=$invObj->pageno;
        $lastId=0; $lastFoil=""; $lastEval="";
        $invLen=count($invData);
        foreach ($invData as $invIdx => $invRow) {
            $numTryPaym++;
            $lastIndex++;
            $lastId=$invRow["id"];
            $lastFoil=$invRow["folio"];
            $sumData[]="COMPLEMENTO ".($invIdx+1)."/{$invLen}: {$rootPath}{$invRow['ubicacion']}{$invRow['nombreInterno']}.xml";
            $cfdiObj = CFDI::newInstanceByLocalName($rootPath.$invRow["ubicacion"].$invRow["nombreInterno"].".xml");
            if (isset($cfdiObj)) {
                $doctos=$cfdiObj->get("pago_doctos");
                if (isset($doctos["@iddocumento"])) $doctos=[$doctos];
                $numDoctos=count($doctos);
                if ($pymObj->exists("idCPago=$lastId")) {
                    if ($pymObj->numrows==$numDoctos) {
                        $sumData[]="REGISTRADO CORRECTAMENTE CON FOLIO $lastFoil Y ".$pymObj->numrows." FACTURAS";
                        $numOk++;
                        continue;
                    } else $sumData[]="REGISTRADO CON DISTINTO NUM.DOCTOS, CON FOLIO $lastFoil, REGISTRADAS: $pymObj->numrows, EN_XML: $numDoctos";
                }
                if ($numDoctos>0) {
                    $numHasDocs++;
                    $docLen=count($doctos);
                    foreach ($doctos as $pymIdx => $pymRow) {
                        $pymUUID=$pymRow["@iddocumento"];
                        $pymData=$pymObj->getData("idCPago=$lastId and idDocumento='$pymUUID'");
                        $sumData[]="QUERY DOCTOS ".($pymIdx+1)."/{$docLen}: $query";
                        if (!isset($pymData[0])) {
                            $fieldarray=["idCPago"=>$lastId,"idDocumento"=>$pymUUID];
                            $pymInvData=$invObj->getData("uuid='$pymUUID'");
                            $sumData[]="QUERY UUID: $query";
                            if (isset($pymInvData[0]["id"])) $pymInvData=$pymInvData[0];
                            if (isset($pymInvData["id"])) $fieldarray["idFactura"]=$pymInvData["id"];
                            if (isset($pymRow["@serie"])) $fieldarray["serie"]=$pymRow["@serie"];
                            if (isset($pymRow["@folio"])) $fieldarray["folio"]=$pymRow["@folio"];
                            if (isset($pymRow["@monedadr"])) $fieldarray["moneda"]=$pymRow["@monedadr"];
                            if (isset($pymRow["@equivalenciadr"])) $fieldarray["equivalencia"]=$pymRow["@equivalenciadr"];
                            if (isset($pymRow["@numparcialidad"])) $fieldarray["parcialidad"]=$pymRow["@numparcialidad"];
                            if (isset($pymRow["@impsaldoant"])) $fieldarray["saldoAnterior"]=$pymRow["@impsaldoant"];
                            if (isset($pymRow["@imppagado"])) $fieldarray["importePagado"]=$pymRow["@imppagado"];
                            if (isset($pymRow["@impsaldoinsoluto"])) $fieldarray["saldoInsoluto"]=$pymRow["@impsaldoinsoluto"];
                            if (isset($pymRow["@objetoimpdr"])) $fieldarray["objetoImpuesto"]=$pymRow["@objetoimpdr"];
                            if (isset($invRow["statusn"])&&$invRow["statusn"]>=128) $fieldarray["status"]=-1;
                            else if (isset($pymInvData["statusn"])) {
                                if($pymInvData["statusn"]<128) $fieldarray["status"]=1;
                                else $fieldarray["status"]=-2;
                            }
                            if ($pymObj->saveRecord($fieldarray)) {
                                $sumData[]="QUERY SAVE: $query";
                                $numSaved++;
                                $lastEval="Saved";
                            } else {
                                $sumData[]="QUERY SAVEERR: $query";
                                $sumData[]="DBi::Errors: ".json_encode(DBi::$errors);
                                $sumData[]="DoctoErrors: ".json_encode($pymObj->errors);
                                $lastErrno=DBi::getErrno();
                                $lastError=DBi::getError();
                                $lastEval="NoSave";
                                $numErrors++;
                                $sumData[]="($lastId) Can't save  [$lastErrno]='$lastError'";
                            }
                        } else {
                            $numExists++;
                            $lastEval="Exists";
                        }
                    }
                } else {
                    $numNoDocs++;
                    $lastEval="NoDocto";
                    $sumData[]="($lastId) CP SIN DOCTOS";
                }
            } else {
                $numNoCObj++;
                $lastEval="NoCP";
                $docErrors=CFDI::getLastError(); // errorMessage,errorStack,enough,log
                $errData="";
                if (isset($docErrors["errorMessage"][0]))
                    $errData.=(isset($errData[0])?", ":"")."ERRMSG=".$docErrors["errorMessage"];
                if (isset($docErrors["texto"][0]))
                    $errData.=(isset($errData[0])?", ":"")."TXT=".$docErrors["texto"];
                if (isset($docErrors["code"]) && $docErrors["code"]===CFDI::EXCEPTION_UNREGISTERED_PROVIDER && isset($docErrors["proveedor"][0]))
                    $errData.=(isset($errData[0])?", ":"")."PRV=".$docErrors["proveedor"];
                if (isset($docErrors["validar"][0]))
                    $errData.=(isset($errData[0])?", ":"")."LOG=".$docErrors["validar"];
                if (isset($docErrors["exception"])) {
                    $errData.=(isset($errData[0])?", ":"")."EXCEPTION=".json_encode(getErrorData($docErrors["exception"]));
                }
                if (!isset($errData[0])) $errData="ERROR NO REGISTRADO";
                $sumData[]="($lastId) ".$errData;
            }
        }
        $resultArray=[];
        if (($numOk+$numSaved+$numExists)>0) $resultArray["result"]="success";
        else $resultArray["result"]="error";
        if (isset($sumData)) $resultArray["summaryData"]=$sumData;
        $resultArray["message"]="";
        if ($numTryPaym>0) {
            $invMsg="";
            $resultArray["message"]="$page) De $numTryPaym Complementos de Pago revisados:";
            if ($numOk>0) {
                $resultArray["message"].=" $numOk tienen registo previo completo";
                if (($numHasDocs+$numNoDocs+$numNoCObj)>0) $resultArray["message"].=",";
            }
            if ($numHasDocs>0) {
                $resultArray["message"].=" $numHasDocs reconocidos con facturas";
                $numInvoices=$numExists+$numErrors+$numSaved;
                $invMsg.=" De $numInvoices facturas en CPs:";
                if ($numExists>0) {
                    $invMsg.=" $numExists ya estaban registradas";
                    if (($numErrors+$numSaved)>0) $invMsg.=",";
                }
                if ($numErrors>0) {
                    $invMsg.=" $numErrors generaron error al intentar guardarlas";
                    if ($numSaved>0) $invMsg.=",";
                }
                if ($numSaved>0) $invMsg.=" $numSaved se guardaron exitosamente";
                if (($numNoDocs+$numNoCObj)>0) $resultArray["message"].=",";
            }
            if ($numNoDocs>0) {
                $resultArray["message"].=" $numNoDocs no tenían facturas";
                if ($numNoCObj>0) $resultArray["message"].=",";
            }
            if ($numNoCObj>0) $resultArray["message"].=" $numNoCObj fallaron al abrir";
            $resultArray["message"].=". {$invMsg}";
        } else $resultArray["message"]="Ningun complemento encontrado";
        if ($lastId>0) {
            $resultArray["lastId"]=$lastId;
            //$resultArray["message"].=". Last $lastEval $lastFoil ($lastId)";
            if (isset($lastErrno)) {
                $resultArray["message"].=". ERROR $lastErrno";
                if (isset($lastError)) $resultArray["message"].=": $lastError";
                if (isset($lastQuery)) $resultArray["message"].=". $lastQuery";
            }

        }
        //$resultArray["message"].=".";
        $resultArray["lastIndex"]=$lastIndex;
        $resultArray["numrows"]=$numrows;
        $resultArray["lastPage"]=$lastpage;
        $resultArray["page"]=1+$page;
        echo json_encode($resultArray);
    }
    return;
}
clog2ini("tareas.doctos");
clog1seq(1);
require_once "templates/generalScript.php";
?>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title>Documentos Relacionados</title>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
<?php
echoGeneralScript();
?>
    <script>
        var xhp;
        function startPopulation(isAutomatic) {
            if (xhp) xhp.abort();
            xhp=readyService("tareas/doctos.php",{action:"retrieve",lastIndex:ebyid("lastIndex").value,page:ebyid("page").value},successResult,failureResult);
            setTimeout((isAuto)=>{
                if (!isAuto) {
                    cladd("genBtn","hidden");
                    clrem("stopBtn","hidden");
                }
            },10,isAutomatic);
        }
        function stopPopulation(isAutomatic) {
            if (xhp) xhp.abort();
            setTimeout((isAuto)=>{
                xhp=null;
                if (!isAuto) {
                    clrem("genBtn","hidden");
                    cladd("stopBtn","hidden");
                }
            },10,isAutomatic);
        }
        function addMessage(message,isVisLine,extraClass) {
            const prg=document.createElement("P");
            prg.textContent=message;
            if (extraClass) cladd(prg,extraClass);
            if (isVisLine) cladd(prg,"visLine");
            const container=ebyid("area_usuario2");
            const numVL=(isVisLine?1:0)+lbycn("visLine",container).length;
            if (numVL>0) {
                const prgId="vl_"+numVL;
                if (isVisLine) {
                    prg.id=prgId;
                    prg.onclick=function(event){if(event&&event.target){clfix(lbycn(event.target.id),"hidden");}};
                } else {
                    cladd(prg,[prgId,"hidden"]);
                    prg.num=numVL;
                    prg.onclick=function(event){if(event&&event.target&&event.target.num)cladd(lbycn("vl_"+event.target.num),"hidden");};
                }
            }
            container.appendChild(prg);
            return prg;
        }
        function setSummaryData(summaryData,extraClass) {
            if (summaryData) {
                summaryData.forEach(sd=>{
                    if (!extraClass) extraClass=[];
                    else if (!Array.isArray(extraClass)) extraClass=[extraClass];
                    extraClass.push("padL10");
                    addMessage(sd,false,extraClass);
                });
            }
        }
        function addHBar() {
            const hb=document.createElement("HR");
            cladd(hb,["hei1","nomarginblock","martop3i"]);
            ebyid("area_usuario2").appendChild(hb);
        }
        function failureResult(messageError,responseText,extra) {
            console.log("INI failureResult: "+messageError, extra);
            const prg=addMessage(messageError,true,"bgred2b");
            setSummaryData(extra.summaryData, "bgred2b");
            addHBar();
        }
        function lastSetup(jobj) {
            if (jobj.page) ebyid("page").value=jobj.page;
            if (jobj.lastIndex) ebyid("lastIndex").value=jobj.lastIndex;
            if (jobj.lastPage&&jobj.lastPage>jobj.page) {
                console.log("Start next page "+jobj.page+"/"+jobj.lastPage);
                if (xhp) {
                    setTimeout(startPopulation,10,true);
                } else console.log("NO XHP");
            }
        }
        function successResult(jobj,extra) {
            const hasSummaryData = (jobj.summaryData && jobj.summaryData.length>0);
            if (jobj.result==="success") {
                const prg=addMessage(jobj.message,true);
                setSummaryData(jobj.summaryData);
                addHBar();
                lastSetup(jobj);
            } else if (jobj.result==="error") {
                if (hasSummaryData) extra.summaryData=jobj.summaryData;
                failureResult(jobj.message?jobj.message:"Falla indefinida","",extra);
                lastSetup(jobj);
            } else {
                console.log("RESULT '"+jobj.result+"':'"+jobj.message+"' "+extra.state+"/"+extra.status);
                stopPopulation();
            }
        }
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>Documentos Relacionados</h1>
      <div id="area_setup" class="bbtm1_8 marbtm1 padb4">PAG:<input type="text" id="page" value="0" class="folioV2" autofocus>
        <input type="button" id="genBtn" value="GENERAR" onclick="startPopulation();">
        <input type="button" id="stopBtn" value="DETENER" onclick="stopPopulation();" class="hidden">
        <input type="hidden" id="lastIndex" value="0">
        <input type="hidden" id="lastId" value="0">
      </div>
      <div id="area_usuario2" class="nocenter">
      </div>
    </div>
  </body>
</html>
<?php
clog1seq(-1);
clog2end("tareas.doctos");
