window.onload = function() {
    // Check if the current window has an opener
    if (window.opener || window.name) {
        // If the window was opened from another one or through a link's 'target', close it
        if (window.opener) window.opener.location.reload();
        window.close();
    }
};
<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    die("Empty File");
}
require_once "clases/SolicitudPago.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.listasolp");
clog1seq(1);
$listaFiltros=$_SESSION["solLstFltr"]??[];
$baseUrl=getBaseURL();
$baseUrlLen=strlen($baseUrl);
$esAdmin=validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
if ($esSistemas) $_SESSION['solAuthList']=null;
$esDesarrollo = hasUser() && in_array(getUser()->nombre, ["admin","sistemas"]);

$authList=$_SESSION["solAuthList"]??[];
if (empty($authList)) {
    global $perObj, $ugObj;
    if (!isset($perObj)) {
        require_once "clases/Perfiles.php";
        $perObj=new Perfiles();
    }
    $perData=$perObj->getData("nombre='Autoriza Pagos'",0,"id");
    if (isset($perData[0])&&isset($perData[0]["id"])) {
        $perfilId=$perData[0]["id"];
        if(!isset($ugObj)) {
            require_once "clases/Usuarios_Grupo.php";
            $ugObj=new Usuarios_Grupo();
        }
        $ugObj->rows_per_page=0;
        $ugObj->clearOrder();
        $ugObj->addOrder("idGrupo");
        $ugObj->addOrder("idUsuario");
        $ugData=$ugObj->getData("ug.idPerfil=$perfilId and g.status='activo'",0,"ug.idUsuario,ug.idGrupo","ug inner join grupo g on ug.idGrupo=g.id");
        $authList=[];
        $allAuth=[];
        foreach ($ugData as $ugIdx => $ugRow) {
            //if (!isset($authList[0])) $authList[0]=[];
            if (!isset($authList[$ugRow["idGrupo"]])) $authList[$ugRow["idGrupo"]]=[];
            $authList[$ugRow["idGrupo"]][]=$ugRow["idUsuario"];
            if (!in_array($ugRow["idUsuario"], $allAuth)) $allAuth[]=$ugRow["idUsuario"];
        }
        sort($allAuth);
        $authList=["0"=>$allAuth]+$authList;
        $_SESSION["solAuthList"]=$authList;
    }
}
$authNameMap=[];
$authKeys=$authList["0"]??[];
if (isset($authKeys[0])) {
    global $usrObj;
    if(!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $usrData=$usrObj->getData("id in (".implode(",",$authKeys).")",0,"id,nombre,persona,email");
    foreach ($usrData as $idx => $usrRow) {
        $authNameMap[$usrRow["id"]]=$usrRow["persona"];
    }
}
if ($esSistemas) { ?>
doShowFuncLogs=false;
console.log("AUTHLIST0: <?=str_replace("\"", "\\\"", json_encode($authList)) ?>");
console.log("AUTHNAMEMAP: <?=str_replace("\"", "\\\"", json_encode($authNameMap))?>");
console.log("AUTHKEYS: <?=str_replace("\"", "\\\"", json_encode($authKeys))?>");
<?php
}
if ($esAdmin) { ?>
var blockActions=false; //true;
<?php
}
?>
var testVar=1;
var delayTimeout=0;
var massTimeout=0;
const currency=new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN',
});
var authList=<?=json_encode($authList)?>;
var refreshTimeout=false;
function doPaymAction(action, solId, extraParams) {
    console.log("INI function doPaymAction "+action+" id="+solId, extraParams);
    clearTimeout(refreshTimeout);
    refreshTimeout=null;
    let srvAction=false;
    let module=false;
    let solFolio=solId;
    if (extraParams && extraParams.folio) solFolio=extraParams.folio;
    switch(action) {
        case "PAGADA":
            viewForm(solId,solFolio,"SOLICITUD PAGADA "+solFolio);
            return;
        case "AUTORIZAR":
            srvAction="responsePaymentAuthorization";
            module="autorizaPago";
            break;
        case "RECHAZAR":
            srvAction="responsePaymentAuthorization";
            module="rechazaPago";
            break;
        case "REENVIAR":
<?php 
if ($esSistemas) { ?>
            const row=ebyid("row"+solId);
            const gpoId=row.getAttribute("gpoid");
            const auIs=row.getAttribute("auis");
            console.log("auIs: "+auIs);
            const myAuthList=(auIs&&auIs.length>0)?auIs.split(","):authList[gpoId];
            console.log("myAuthList("+myAuthList.length+"): ", myAuthList);
            let myAuthId=-1;
            if (myAuthList.length==1) myAuthId=myAuthList[0];
            else if (myAuthList.length>1) {
                const noIdx=myAuthList.indexOf("2639");
                if (noIdx!==-1) myAuthList.splice(noIdx,1);
                myAuthId=myAuthList[0];
            }
            console.log("myAuthId: "+myAuthId);
            const optLst=[];
<?php
    foreach ($authNameMap as $usrId => $usrRealName) {
        echo "optLst.push({eName:\"OPTION\",value:\"$usrId\",eText:\"$usrRealName\",selected:(myAuthId==\"$usrId\")});\n";
    }
?>
            console.log("OPTLST: ",optLst);
            const adminList=[getParagraphObject(["Reenviar correo a:",{eName:"SELECT",eChilds:optLst,onchange:e=>{const t=e.target;const o=ebyid('overlay');o.authId=t.value;}}],["lefted padt7 nomargini wid220pxi","lefted nomargini"
            ],true)];
            overlayConfirmation(adminList,"Reenviar correo",function(){const o=ebyid('overlay');if(o.authId){console.log('O_AUTHID: '+o.authId);extraParams.authId=o.authId;delete o.authId;doPaymAction("REENVIAROK",solId,extraParams);}else{console.log('NOAUTHID');}});
            const ais=ebyid("newAuthId");
            const ovy=ebyid("overlay");
            if (ais){ovy.authId=ais.value;}
            else if (myAuthId>0) {ovy.authId=myAuthId;}
            return;
<?php 
} ?>
        case "REENVIAROK":
            srvAction="resendEmail";
            module="reenviarCorreo";
            break;
        case "RECUPERAR":
            srvAction="restorePaymentBeforeAuthorization";
            module="rechazaPago";
            break;
        case "EXPORTAR":
            srvAction="requestTransferInvoiceFiles";
            module="transfiereArchivos";
            break;
        case "PROCESAR":
            srvAction="responseSetValidRequest";
            module="procesaCompras";
            break;
        case "PASAR A PAGO":
            srvAction="readyToPayRequest";
            module="procesaConta";
            break;
        case "ANEXAR COMPROBANTE":
            srvAction="requestProofOfPayment";
            module="anexaComprobante";
            break;
        case "MARCAR PAGADA":
            srvAction="responseSetPaidRequest";
            module="procesaPago";
            break;
        case "ANEXAR FACTURA":
            displayInvoiceUploadDialog(solId, extraParams);
            return;
        case "AGREGAR FACTURA":
            srvAction="appendInvoice2PaidReq";
            break;
        case "CANCELAR":
            srvAction="cancelPaymRequest";
            module="MOTIVO PENDIENTE";
            if (!extraParams) extraParams={};
            if (!extraParams.motivo || extraParams.motivo.length==0) {
                overlayConfirmation({eName:"P",className:"mbp",eChilds:[{eName:"LABEL",eChilds:[{eText:"Motivo de Cancelación: "},{eName:"INPUT",type:"text",id:"myReasonToCancel",autofocus:true,maxLength:"100",size:"50"}]}]},"Cancelar Solicitud "+solFolio,function(){ const ovy=ebyid('overlay'); extraParams.motivo=ovy.myReasonToCancel; delete ovy.myReasonToCancel; doPaymAction("CANCELAR", solId, extraParams); });
                const mrtc=ebyid("myReasonToCancel");
                const ovy=ebyid("overlay");
                if (mrtc) {
                    ovy.myReasonToCancel=mrtc.value;
                    mrtc.oninput=function(event) {
                        ovy.myReasonToCancel=mrtc.value;
                    };
                }
                return;
            }
            break;
        default :
            overlayMessage(getParagraphObject("Acción inválida: "+action, "errorLabel"),"ERROR");
            return;
    }
    overlayWheel();
    const parameters = {action:srvAction, module:module, solId:solId};
    if (extraParams) Object.assign(parameters,extraParams);
<?php
if ($esSistemas) { ?>
    if (typeof blockActions!=='undefined' && blockActions===true)
        parameters.ignore=true;
<?php
} ?>
    console.log("POSTSERVICE consultas/Facturas.php",parameters);
    clearTimeout(delayTimeout);
    postService("consultas/Facturas.php", parameters, function(msg,pars,state,status) {
        //console.log("RESPONSE "+state+","+status);
        if (state==4&&status==200) {
            if (msg.slice(0,10)==="{\"action\":" || msg.slice(0,10)==="{\"result\":") {
                try {
                    const jobj=JSON.parse(msg);
                    if (jobj.action && jobj.action==="redirect") {
                        //console.log("ACTION=REDIRECT!",pars,msg);
                        overlayClose();
                        backdropCloseable=false;
                        cleanBackdrop();
                        viewBackdrop();
                        const formObj={eName:"FORM",target:"_self",method:"POST",eChilds:[]};
                        for (let key in jobj) {
                            //console.log("NAME="+key+", VALUE="+jobj[key]);
                            formObj.eChilds.push({eName:"INPUT",type:"hidden",name:key,value:jobj[key]});
                        }
                        const formElem=ecrea(formObj);
                        document.body.appendChild(formElem);
                        formElem.submit();
                    } else if (jobj.result) {
                        const reloadResult=["redirect","refresh","reload"];
                        if (reloadResult.includes(jobj.result)) {
                            overlayClose();
                            viewWaitBackdrop();
                            location.reload(true);
                            return;
                        }
                        if (jobj.result==="success") {
                            if (pars&&pars.action==="resendEmail") {
                                const blk=ebyid("rsndBlk"+pars.solId);
                                if (blk) {
                                    const bdg=ebyid("rsndBdg"+pars.solId);
                                    blk.setAttribute("rsndTms",jobj.rsndtms);
                                    if (bdg) {
                                        bdg.title="Reenviado "+jobj.rsndtms;
                                    } else {
                                        cladd(blk,'relative');;
                                        prn.appendChild(ecrea({eName:'DIV',id:'rsndBdg'+$solId,className:'abs_se badge',title:'Reenviado '+jobj.rsndtms,text:'+'}));
                                    }
                                } else console.log("No pudo encontrarse 'rsndBlk"+pars.solId+"'");
                            }
                            refreshTimeout=setTimeout(function() {
                                overlayClose();
                                viewWaitBackdrop();
                                location.reload(true);
                            },3000);
                        }
                        if (jobj.message) {
                            if (isTextContent(jobj.message))
                                overlayMessage(getParagraphObject(jobj.message),jobj.result.toUpperCase());
                            else
                                overlayMessage(jobj.message,jobj.result.toUpperCase());
                            if (refreshTimeout) {
                                clearTimeout(refreshTimeout);
                                const ovy=ebyid("overlay");
                                ovy.callOnClose=function() {
                                    refreshTimeout=setTimeout(function() {
                                        overlayClose();
                                        viewWaitBackdrop();
                                        location.reload(true);
                                    },2000);
                                }
                            }
                        }
                    } else overlayMessage(getParagraphObject("Error de significado, avise al administrador", "errorLabel"),"ERROR");
                } catch (ex) {
                    overlayMessage(getParagraphObject("Error de interpretación, avise al administrador", "errorLabel"),"ERROR");
                }
            } else {
                msg = msg.replaceAll('id="','id="R').replace('CERRAR" style="','CERRAR" style="display:none;').replace('encabezado" style="','encabezado" style="display:none;');
                console.log("RESPUESTA HTML");
                fee(lbycn("fixScript"),el=>ekil(el));
                const fixScript=ecrea({eName:"SCRIPT",className:"fixScript",eText:"function vtt(evt){const tgt=evt.target;if(tgt&&tgt.hasAttribute('ttp'))tgt.setAttribute('tooltip',tgt.getAttribute('ttp'));viewTooltip(evt);}"});
                const sss=document.getElementsByTagName("SCRIPT");
                sss[0].parentNode.appendChild(fixScript);
                overlayMessage(msg,"RESPUESTA");
                const toReloadList = lbycn("reloadOnClose",ebyid("overlay"));
                if (toReloadList && toReloadList.length>0) {
                    console.log("RELOAD READY: ", toReloadList);
                    ebyid("overlay").callOnClose=function() {
                        refreshTimeout=setTimeout(function() {
                            overlayClose();
                            viewWaitBackdrop();
                            location.reload(true);
                        },2000);
                    }
                } else console.log("NOTHING TO RELOAD");
                setTimeout(onresizeScripts,100);
            }
        } else if (state>4 || status!=200) {
            overlayMessage(getParagraphObject("Error de comunicación, reintente más tarde.", "errorLabel"),"ERROR");
            logService("Error de comunicación, reintente más tarde",{url:"consultas/Facturas.php", state:state, status:status, parameters:pars, msg:msg});
        }
    });
}
function descargaFolios(elem) {
    //console.log("INI descargaFolios "+elem.tagName);
    const tbl=elem.parentNode.nextElementSibling;
    const rows=tbl.getElementsByTagName("TR");
    const folios=["SOLICITUD\tFACTURA"];
    fee(rows,rw=>{
        resultLine="";
        if (rw.hasAttribute("folId")) resultLine+=rw.getAttribute("folId");
        if (rw.children.length>3) {
            resultLine+="\t";
            if (rw.children.item(3).firstElementChild.title)
                resultLine+=rw.children.item(3).firstElementChild.title;
            else resultLine+=rw.children.item(3).textContent;
        }
        folios.push(resultLine);
    });
    if (folios.length>1) {
        let text=folios.join("\r\n");
        const footTxt=tbl.parentNode.nextElementSibling.firstChild;
        text+="\r\n"+footTxt.nodeValue.trim();
        const anchorObj={eName:"A",download:"solfolios.txt",style:{display:"none"},href:"data:text/plain;charset=utf-8,"+encodeURIComponent(text)};
        const aElem=ecrea(anchorObj);
        document.body.appendChild(aElem);
        aElem.click();
        document.body.removeChild(aElem);
    }
}
function displayInvoiceUploadDialog(solId, extraParams) {
    const row=ebyid("row"+solId);
    const cellFolio=row.firstElementChild;

    const cellEmpresa=cellFolio.nextElementSibling;
    const gpoRazSoc=cellEmpresa.title;
    const divEmpresa=cellEmpresa.firstElementChild;
    const gpoAlias=divEmpresa.textContent;
    const blockList=[];
    blockList.push({eName:"DIV", title:gpoAlias, className:"nomargin inblock wid500px lefted", eChilds:[{eName:"B", className:"inblock wid135px padL50", eText:"EMPRESA"}, {eName:"SPAN", eText:gpoRazSoc}]});

    const cellProveedor=cellEmpresa.nextElementSibling;
    let prvRazSoc=cellProveedor.title;
    const divProveedor=cellProveedor.firstElementChild;
    const prvCodigo=divProveedor.textContent;
    const esProveedor=(prvCodigo!=="I-998" && prvCodigo!=="I-999");
    if (!esProveedor) prvRazSoc+=" (sin empresa)";
    blockList.push({eName:"DIV", title:prvCodigo, className:"nomargin inblock wid500px lefted", eChilds:[{eName:"B", className:"inblock wid135px padL50", eText:"PROVEEDOR"}, {eName:"SPAN", eText:prvRazSoc}]});
    if (esProveedor) {
        blockList.push({eName:"DIV", id:"folioBlock", className:"nomargin inblock wid500px lefted", eChilds:[{eName:"B", id:"folioBlkCap", className:"inblock wid135px padL50", eText:"FOLIO/UUID"}, {eName:"INPUT", type:"text", id:"folioFactura", isl:solId, fsl:extraParams.folio, className:"padv02 marbtm2 wid225px", autofocus:true, oninput:browseInvoice}, {eName:"IMG", src:"imagenes/ledoff.gif", className:"grayscale pro12", id:"browseLed", onclick:stopBrowsing}]});
        blockList.push({eName:"DIV", id:"uuidBlock", className:"nomargin inblock wid500px lefted"});
        blockList.push({eName:"DIV", id:"messageBlock", className:"bodycolor boldValue alignCenter"});
    } else blockList.push({eName:"DIV", id:"messageBlock", className:"errorLabel", textContent:"No es un proveedor válido"});

    overlayMessage({eName:"DIV", id:"cfdiSelector", className:"martop5 marbtm5 alignCenter",eChilds:blockList},"Relacionar Factura con Solicitud "+extraParams.folio);
<?php 
if ($esDesarrollo) { ?>
    console.log("TESTING setPaidButton functionality");
    const pbn=ebyid("setPaidButton");
    if (pbn) {
        console.log("Found setPaidButton reassigned from "+pbn.solId+" to "+solId);
        pbn.solId=solId;
    } else setTimeout(function(sid) {
        console.log("Create setPaidButton for "+sid);
        const cbn=ebyid("closeButton");
        const pbn=ecrea({eName:"INPUT", type:"button", value:"Marcar Pagada", solId:sid, onclick:(evt)=>{const tgt=evt.target;readyService("consultas/SolPago.php", {action:"setCleanPaid",solId:tgt.solId}, (j,e)=>{
            if (j.result==="success") window.location.reload();
            else {
                console.log("RESULT: ",j,e);
                overlayMessage(getParagraphObject(j.message, "errorLabel"),"ERROR");
                logService(j.message,{url:"consultas/SolPago.php", json:j, extra:e});
            }
        }, (m,r,x)=>{
            console.log("ERROR: ",r,x);
            overlayMessage(getParagraphObject(m, "errorLabel"),"ERROR");
            logService(m,{url:"consultas/SolPago.php", response:r, extra:x});
        }); console.log('En Proceso de Pago. SolId='+tgt.solId+'!!');}});
        cbn.parentNode.insertBefore(pbn,cbn);
        console.log("ELEM PROPERTY TEST: "+pbn.solId);
    }, 10, solId);
<?php 
} else { ?>
    console.log("¡¡¡¡ NORMAL !!!!");
<?php 
} ?>
    focusOnAutoFocus();
}
var browseTimeout;
var xqr;
function browseInvoice(evt) {
    const tgt=evt.target;
    const solId=tgt.isl;
    const solFolio=tgt.fsl;
    const row=ebyid("row"+solId);
    const gpoId=row?(row.gpoid?row.gpoid:(row.hasAttribute("gpoid")?row.getAttribute("gpoid"):false)):false;
    const prvId=row?(row.prvid?row.prvid:(row.hasAttribute("prvid")?row.getAttribute("prvid"):false)):false;
    console.log("INI browseInvoice@"+tgt.id+" "+evt.inputType+": "+evt.data+" => "+tgt.value+" (sol:"+solId+",gpo:"+gpoId+",prv:"+prvId);
    stopBrowsing(evt);
    const browseLed=ebyid("browseLed");
    browseLed.src="imagenes/ledyellow.gif";
    clrem(browseLed,"grayscale");
    const ff=ebyid("folioFactura");
    cladd(ff,"lightBlurred");
    const cbn=ebyid("closeButton");
    while (cbn && cbn.nextSibling) cbn.parentNode.removeChild(cbn.nextSibling);
    browseTimeout=setTimeout(function(solId, gpoId, prvId, invIdf, solF){
        console.log("BROWSING sol "+solId+":"+solF+", gpo "+gpoId+", prv "+prvId+", inv "+invIdf);
        const messageBlock=ebyid("messageBlock");
        xqr=postService("/invoice/consultas/Facturas.php", {action:"verifyInvoiceForPaymReq",solId:solId,solF:solF,gpoId:gpoId,prvId:prvId,invIdf:invIdf}, function(retmsg, params, rdyState, status) {
            //console.log("AJAX RESPONSE FUNCTION "+rdyState+"/"+status);
            if(rdyState==4&&status==200) {
                try {
                    if (!retmsg || retmsg.length==0) {
                        clrem(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
                        cladd(messageBlock,"errorLabel");
                        browseLed.src="imagenes/ledred.gif";
                        clrem(ff,"lightBlurred");
                        cladd(ff,["darkRedLabel","reddenbg"]);
                        messageBlock.textContent="No se localizó el folio o uuid indicado";
                        console.log("ERROR: "+errmsg,params,evt);
                        delete params.xmlHttpPost;
                        logService("scripts.listasolp.browseInvoice EMPTY RESPONSE",{retmsg:retmsg,parameters:params});
                        return;
                    }
                    jobj=JSON.parse(retmsg);
                    if (jobj.result) {
                        if (jobj.result==="refresh") {
                            location.reload(true);
                        } else if (jobj.result==="error") {
                            clrem(messageBlock,["bodycolor", "boldValue","alignCenter"]);
                            cladd(messageBlock,"errorLabel");
                            browseLed.src="imagenes/ledred.gif";
                            clrem(ff,"lightBlurred");
                            cladd(ff,["darkRedLabel","reddenbgi"]);
                            messageBlock.textContent=(jobj.message?jobj.message:"No fue posible validar la factura, consulte al administrador");
                            console.log("JOBJ: "+JSON.stringify(jobj,jsonCircularReplacer()));
                        } else if (jobj.result==="success") {
                            params.row=ebyid("row"+params.solId);
                            params.gpoId=params.row?(params.row.gpoid?params.row.gpoid:(params.row.hasAttribute("gpoid")?params.row.getAttribute("gpoid"):false)):false;
                            params.prvId=params.row?(params.row.prvid?params.row.prvid:(params.row.hasAttribute("prvid")?params.row.getAttribute("prvid"):false)):false;
                            params.tot=params.row?(params.row.tot?params.row.tot:(params.row.hasAttribute("tot")?params.row.getAttribute("tot"):false)):false;
                            params.mon=params.row?(params.row.mon?params.row.mon:(params.row.hasAttribute("mon")?params.row.getAttribute("mon"):false)):false;
                            clrem(messageBlock,"errorLabel");
                            cladd(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
                            browseLed.src="imagenes/ledgreen.gif";
                            clrem(ff,"lightBlurred");
                            cladd(ff,["greenHighlight","greenbgi"]);
                            fillContent("folioBlkCap","FOLIO");
                            fillValue("folioFactura",jobj.folio);
                            const uuidBlock=ebyid("uuidBlock");
                            uuidBlock.appendChild(ecrea({eName:"B", className:"inblock wid135px padL50", eText:"UUID"}));
                            uuidBlock.appendChild(ecrea({eText:jobj.uuid}));
                            const solTotal=currency.format(params.tot);
                            const facTotal=currency.format(jobj.tot);
                            const totalColor=(solTotal===facTotal)?"greenbg":"reddenbg";
                            messageBlock.parentNode.insertBefore(ecrea({eName:"DIV", className:"nomargin inblock wid500px lefted", eChilds:[{eName:"B", className:"inblock wid135px padL50", eText:"TOTAL SOL."}, {eName:"SPAN",className:totalColor,eText:currency.format(params.tot)+" "+params.mon}]}),messageBlock);
                            messageBlock.parentNode.insertBefore(ecrea({eName:"DIV", className:"nomargin inblock wid500px lefted", eChilds:[{eName:"B", className:"inblock wid135px padL50", eText:"TOTAL FAC."}, {eName:"SPAN",className:totalColor,eText:currency.format(jobj.tot)+" "+jobj.mon}]}),messageBlock);
                            messageBlock.textContent=(jobj.message?jobj.message:"");
                            // ... agregar texto con informacion
                            const cba=ebyid("closeButtonArea");
                            const saveBtn=ecrea({eName:"INPUT",type:"button",value:"Confirmar",onclick:saveInvoiceInPaymReq});
                            saveBtn.solId=params.solId;
                            saveBtn.solFol=params.solF;
                            saveBtn.invId=jobj.invId;
                            cba.appendChild(ecrea({eText:" "}));
                            cba.appendChild(saveBtn);
                            //viewBackdrop(); // todo: poner WaitRoll
                        } else {
                            clrem(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
                            cladd(messageBlock,"errorLabel");
                            browseLed.src="imagenes/ledred.gif";
                            clrem(ff,"lightBlurred");
                            cladd(ff,["darkRedLabel","reddenbg"]);
                            messageBlock.textContent="Resultado inválido, consulte al administrador";
                            console.log("INVALID RESULT: ",jobj);
                        }
                    } else if (jobj.action) {
                        if (jobj.action==="refresh") {
                            location.reload(true);
                        }
                    } else {
                        clrem(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
                        cladd(messageBlock,"errorLabel");
                        browseLed.src="imagenes/ledred.gif";
                        clrem(ff,"lightBlurred");
                        cladd(ff,["darkRedLabel","reddenbg"]);
                        messageBlock.textContent="Datos inválidos, consulte al administrador";
                        console.log("INVALID DATA: ",jobj);
                    }
                } catch (ex) {
                    clrem(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
                    cladd(messageBlock,"errorLabel");
                    browseLed.src="imagenes/ledred.gif";
                    clrem(ff,"lightBlurred");
                    cladd(ff,["darkRedLabel","reddenbg"]);
                    messageBlock.textContent="Proceso en actualización, consulte al administrador";
                    console.log("EXCEPTION: ",ex,params);
                    console.log("MESSAGE ("+retmsg.length+"):\n",retmsg);
                    delete params.xmlHttpPost;
                    postService("consultas/Logs.php",{
                        action:"doclog",
                        message:"scripts.listasolp.browseInvoice Exception",
                        filebase:"error",
                        data:JSON.stringify({exception:ex.toString(),parameters:params,resMsg:retmsg})});
                }
            } else if (rdyState>4 || status!=200) {
                clrem(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
                cladd(messageBlock,"errorLabel");
                browseLed.src="imagenes/ledred.gif";
                clrem(ff,"lightBlurred");
                cladd(ff,["darkRedLabel","reddenbg"]);
                messageBlock.textContent="Consulta incompleta, consulte al administrador.";
                console.log("ERROR RDY"+rdyState+", STT"+status+": "+retmsg,params);
            }
        }, function(errmsg, params, evt) {
            clrem(messageBlock,["bodycolor", "boldValue", "alignCenter"]);
            cladd(messageBlock,"errorLabel");
            browseLed.src="imagenes/ledred.gif";
            clrem(ff,"lightBlurred");
            cladd(ff,["darkRedLabel","reddenbg"]);
            messageBlock.textContent="Servidor temporalmente sin conexión, intente nuevamente más tarde.";
            console.log("ERROR: "+errmsg,params,evt);
        });
    },1500,solId,gpoId,prvId,tgt.value,solFolio);
}
function saveInvoiceInPaymReq(evt) {
    const tgt=evt.target;
    console.log("INI saveInvoiceInPaymReq s"+tgt.solId+":"+tgt.solFol+", i"+tgt.invId);
    readyService("/invoice/consultas/Facturas.php", 
        { action:"saveInvoiceInPaymReq", solId:tgt.solId, solFol:tgt.solFol, invId:tgt.invId },
        (j,e)=>{
            if (j.result==="success") {
                refreshTimeout=setTimeout(function() {
                    location.reload(true);
                },100);
            } else {
                overlayMessage(getParagraphObject(j.message, "errorLabel"),j.result.toUpperCase());
                console.log("OVYMESSAGE "+rdyState+"/"+status+": '", retmsg, "', PARAMS: ", params);
                refreshTimeout=setTimeout(function() {
                    location.reload(true);
                },10000);
                ebyid("overlay").callOnClose=function() {
                    clearTimeout(refreshTimeout);
                    refreshTimeout=setTimeout(function() {
                        location.reload(true);
                    },100);
                }
            }
        }, 
        (m,r,x)=> {
            if (m.length>0) {
                overlayMessage(getParagraphObject(m, "errorLabel"),"ERROR");
                console.log("OVYERRMESSAGE: '", m, "', EVT: ", evt, ", TEXT: ", r, ", EXTRA: ",x);
                refreshTimeout=setTimeout(function() {
                    location.reload(true);
                },10000);
                ebyid("overlay").callOnClose=function() {
                    clearTimeout(refreshTimeout);
                    refreshTimeout=setTimeout(function() {
                        location.reload(true);
                    },100);
                }
            } else {
                console.log("UNKNOWN ERROR, EVT: ", evt, ", TEXT: ", r, ", EXTRA: ",x);
                refreshTimeout=setTimeout(function() {
                    location.reload(true);
                },100);
            }
        });
}
function stopBrowsing(evt) {
    clearTimeout(browseTimeout);
    browseTimeout=0;
    if (xqr) xqr.abort();
    const browseLed=ebyid("browseLed");
    browseLed.src="imagenes/ledoff.gif";
    cladd(browseLed,"grayscale");
    const ff=ebyid("folioFactura");
    clrem(ff,["greenHighlight","greenbgi","darkRedLabel","reddenbgi","lightBlurred"]);
    fillContent("folioBlkCap","FOLIO/UUID");
    const uuidBlock=ebyid("uuidBlock");
    if (uuidBlock) {
        ekfil(uuidBlock);
        while(uuidBlock.nextElementSibling.id!=="messageBlock") ekil(uuidBlock.nextElementSibling);
    }
    ekfil("messageBlock");
}
function showErrorCFDI(msgEs) {
    //console.log("INI showErrorCFDI: ",msgEs);
    ekil(ebyid("cfdiContent"));
    const blk=ebyid("cfdiSelector");
    //console.log("cfdiSelector: ",blk);
    //console.log("parentNode: ",blk.parentNode);
    blk.parentNode.appendChild(ecrea({eName:"DIV", id:"cfdiContent", className:"martop5 marbtm5 errorLabel",eChilds:msgEs}));
    return true;
}
function redirect2InvoiceReport() {
    // ToDo: Considerar posibilidades de accion de esta funcion:
    //   A.- Redirigir a menu accion 'Reporte Facturas' con empresa, proveedor, fecha, status y búsqueda realizada, mostrando renglón con la factura seleccionada
    //   B.- Abrir overlay mostrando nuevo formato de reporte de factura, incluyendo todas las acciones que se pueden hacer en esa sección:
    //       * Listado de datos: Fecha de creacion, fecha de captura, Empresa, Proveedor, Tipo de Comprobante, Folio, Total, Status, Documentos
    //       * Mostrar listado de proceso (al pasar el mouse sobre el status)
    //       * Reiniciar verificacion del CFDI ante el SAT
    //       * Procesar factura: Captura de pedido, códigos de articulos y Confirmar Aceptación
    //       * Enlace a vista de cada documento relacionado: XML y PDF del CFDI, Vista de PDF Construido con Carga de PDF faltante, Contra recibo, Complemento de Pago, Comprobante de Pago
    //       * Rechazar facturas
    //       * Eliminar facturas
    console.log("INI redirect2InvoiceReport");
}
//function viewForm(solId,solFolio,encabezado) {
//    const inputList=[{eName:"INPUT",type:"hidden",name:"SOLID",value:solId},{eName:"INPUT",type:"hidden",name:"SOLFOLIO",value:solFolio}];
//    if (encabezado) inputList.push({eName:"INPUT",type:"hidden",name:"ENCABEZADO",value:encabezado});
function viewForm() { // solId, solFolio, encabezado, extraData
    const inputList=[];
    let scaleArgs=0;
    for(let i=0; i< arguments.length; i++) {
        let arg=arguments[i];
        if (typeof arg === "object") for(pp in arg)
            inputList.push({eName:"INPUT",type:"hidden",name:pp,value:arg[pp]});
        else switch(scaleArgs++) {
            case 0: inputList.push({eName:"INPUT",type:"hidden",name:"SOLID",value:arg}); break;
            case 1: inputList.push({eName:"INPUT",type:"hidden",name:"SOLFOLIO",value:arg}); break;
            case 2: inputList.push({eName:"INPUT",type:"hidden",name:"ENCABEZADO",value:arg}); break;
        }
    }
    const formObj={eName:"FORM",target:"solpago",method:"POST",action:"templates/respuestaSolPago.php",eChilds:inputList};
    const formElem=ecrea(formObj);
    document.body.appendChild(formElem);
    window.open("","solpago");
    formElem.submit();
}
function checkChange() {
    //console.log("INI function listasolp.chechChange");
    const msj=ebyid("msj");
    ekfil(msj);
    const cp=ebyid("cp");
    if (cp.files.length==1) {
        const fd=cp.files[0];
        if (fd.type!=="application/pdf")
            msj.appendChild(ecrea({eName:"P",eText:"El archivo '"+fd.name+"' no tiene el formato requerido (PDF)"}));
        if (fd.size>2097000)
            msj.appendChild(ecrea({eName:"P",eText:"El archivo '"+fd.name+"' excede el tamaño máximo permitido (2MB)"}));
    }
}
function snd() {
    //console.log("INI function listasolp.snd");
    const msj=ebyid('msj');
    fee(lbycn("snd",msj),el=>ekil(el));
    const cp=ebyid("cp");
    if (cp.files.length==0)
        msj.appendChild(ecrea({eName:"P", className:"snd", eText:"No ha seleccionado un archivo"}));
    else if (msj.firstChild)
        msj.appendChild(ecrea({eName:"P", className:"snd", eText:"Solo se puede enviar un archivo válido"}));
    else {
        //console.log("PREPARING DATA");
        msj.appendChild(ecrea({eName:"P", className:"snd", eText:"Enviando..."}));
        const parameters={action:"docProofOfPayment", id:ebyid("cpId").value, solId:ebyid("cpSolId").value, type:ebyid("cpPlace").value, name:ebyid("cpName").value, attach:cp.files[0]};
        const url="/invoice/consultas/Facturas.php";
        postService(url, parameters, function(retmsg, params, rdyState, status) {
            if(rdyState==4&&status==200) {
                try {
                    jobj=JSON.parse(retmsg);
                    //console.log('SUCCESS: '+retmsg);
                    fee(lbycn("snd",msj),el=>ekil(el));
                    msj.appendChild(ecrea({eName:"P", className:"snd", eText:"ARCHIVO RECIBIDO!"}));
                    const divDocs=ebyid(jobj.divname);
                    if (divDocs) {
                        fee(lbycn("cpDoc",divDocs),el=>ekil(el));
                        divDocs.appendChild(ecrea({eName:"A",className:"cpDoc",href:jobj.path+jobj.name,target:"archivo",eChilds:[{eName:"IMG",src:"imagenes/icons/invChk200.png",width:"20",height:"20",title:"COMPROBANTE PAGO",style:{filter:"grayscale(1) brightness(0.8) contrast(2.5)"}}]}));
                    }
                } catch (ex) {
                    fee(lbycn("snd",msj),el=>ekil(el));
                    msj.appendChild(ecrea({eName:"P", className:"snd", eText:"Proceso en actualización, consulte al administrador."}));
                    console.log("EXCEPCION: ",ex,params);
                    delete params.xmlHttpPost;
                    postService("consultas/Logs.php",{
                        action:"doclog",
                        message:"scripts.listasolp.snd Exception",
                        filebase:"error",
                        data:JSON.stringify({exception:ex.toString(),parameters:params,resMsg:retmsg})});
                }
            } else if (rdyState>4 || status!=200) {
                fee(lbycn("snd",msj),el=>ekil(el));
                msj.appendChild(ecrea({eName:"P", className:"snd", eText:"Consulta incompleta, consulte al administrador."}));
                console.log("ERROR RDY"+rdyState+", STT"+status+": "+retmsg,params);
            }
        }, function(errmsg, params, evt) {
            fee(lbycn("snd",msj),el=>ekil(el));
            msj.appendChild(ecrea({eName:"P", className:"snd", eText:"Servidor temporalmente sin conexión, intente nuevamente más tarde."}));
            console.log("ERROR: "+errmsg,params,evt);
        });
        //console.log("REQUEST SENT");
    }
}
var filterList=<?=json_encode($listaFiltros);?>;
function showFilterList() {
    if (!ebyid("filterBlock") && backdropObj.eChilds.length>0) clearBackdrop();
    //if (backdropObj.eChilds.length==0) return;
    
    console.log("INI showFilterList: "+backdropObj.eChilds.length);
    let canView=false;
    if (backdropObj.eChilds.length==0) {
        const filterButton=ebyid("pickFilter");
        if (filterButton) {
            const rect=filterButton.getBoundingClientRect();
            let divWid=130; //rect.right-rect.left;
            let divHei=26; // size*(rect.bottom-rect.top);
            let divTop=rect.bottom;
            let divLft=rect.left;
            let shfDwn=0;
            for (const filterId in filterList) {
                const filterBlock=ebyid(filterId+"block");
                if (filterBlock) continue;
                canView=true;
                const filterData=filterList[filterId];
                addBackdropChild(filterId,divTop+shfDwn,divLft,divWid,divHei,{className:"pad0 hoverDarkF5 pointer basicBG br1_8 filterTab",eChilds:[{eName:"DIV",onclick:showFilterOptions,filterId:filterId,className:"filterDiv expandAll flexCenter",eText:filterData.texto}]});
                shfDwn+=divHei;
            }
        }
    }
    if (canView) {
        viewBackdrop();
        if (!backdropCloseCallback) backdropCloseCallback=function() {
            const cw=ebyid("calendar_widget");
            if (cw && (!cw.style.display || cw.style.display!=="none")) cw.style.display="none";
        };
    } else {
        // toDo: Mostrar mensaje: Todos los filtros están ocupados, puede editarlos o eliminarlos individualmente.
        if (backdropObj.eChilds.length>0 || ebyid("backdrop") || backdropResizeFunc || backdropCloseCallback || backdropTimeout) clearBackdrop();
    }
}
function adjustLstSolPCal() {
    let tgtWg=this.target_widget||false;
    let ids=tgtWg.ids||false;
    if (ids) {
        const iniDtE=ebyid(ids[0]);
        const endDtE=ebyid(ids[1]);
        const iniDay=strptime(date_format, iniDtE.value);
        const endDay=strptime(date_format, endDtE.value);
        if (iniDay>endDay) {
            if (tgtWg===iniDtE) {
                if (same_day(iniDay,first_of_month(actual_day)))
                    endDtE.value=strftime(date_format,actual_day);
                else if (iniDay.getDate()==1)
                    endDtE.value=strftime(date_format,last_of_month(iniDay));
                else
                    endDtE.value=iniDtE.value;
            } else if (tgtWg===endDtE) {
                const nextDay=day_after(endDay);
                if (nextDay.getDate()==1 || same_day(endDay,actual_day))
                    iniDtE.value=strftime(date_format,first_of_month(endDay));
                else
                    iniDtE.value=endDtE.value;
            }
        }
    }
}
function showFilterOptions(evt) {
    if (!evt || !evt.target) {
        //console.log("INI function showFilterOptions: No event or target. ",evt);
        return;
    }
    const tgt=evt.target;
    let tgtId=tgt.filterId?tgt.filterId:tgt.id;
    //console.log("INI function showFilterOptions "+tgtId, tgt);
    if (clhas(tgt,"filterDiv")) {
        const clst=["bgyellow2","boldValue"];
        clrem(lbycn("filterDiv"),clst);
        cladd(tgt,clst);
    }
    const filterBlock=ebyid(tgtId+"block");
    const filterItem=filterList[tgtId];
    if (!filterItem) {
        console.log("INI function showFilterOptions: Invalid filterId "+tgtId,filterList);
        return;
    }
    const filterSection=ebyid(tgtId);
    let filterContainer=ebyid("filterContainer");
    if (filterContainer) {
        fee(backdropObj.eChilds, function(item,idx){if(item.id==="filterContainer") backdropObj.eChilds.splice(idx,1);});
        ekil(filterContainer);
    }
    const backdrop=ebyid("backdrop");
    const rowHgt=25;
    let divTop=0;
    let divLft=0;
    let divWid=filterItem.width?filterItem.width:350;
    let divHei=filterItem.height?filterItem.height:100;
    if (filterBlock) {
        //console.log("Has filterBlock!");
        const rect=filterBlock.getBoundingClientRect();
        divTop=rect.bottom;
        divLft=rect.left;
        viewBackdrop();
        if (!backdropCloseCallback) backdropCloseCallback=function() {
            //console.log("backdropCloseCallback!");
            const cw=ebyid("calendar_widget");
            if (cw && (!cw.style.display || cw.style.display!=="none")) cw.style.display="none";
        };
    } else {
        //console.log("Has Backdrop!");
        const rowSz=backdrop?backdrop.children.length:0;
        const rowLowLimit=rowSz*rowHgt;
        const filterButton=ebyid("pickFilter");
        const rect=filterButton.getBoundingClientRect();
        const frect=filterSection.getBoundingClientRect();
        if (!filterItem.height) divHei=rowHgt*3;
        let baseLstTop=rect.bottom;
        let baseLstBtm=baseLstTop+rowLowLimit;
        let baseDivBtm=baseLstTop+divHei;
        divTop=baseLstTop;
        //console.log("BLT="+baseLstTop+",BLB="+baseLstBtm+",BDB="+baseDivBtm+",DT="+divTop);
        if (baseLstBtm>baseDivBtm) {
            divTop=frect.top;
            //console.log("DT="+divTop);
            let extraSpace=baseLstBtm-divTop-divHei;
            //console.log("EXS="+extraSpace);
            if (extraSpace < 0) {
                let topSpace=divTop+rowHgt-baseLstTop-divHei;
                //console.log("TS="+topSpace);
                if (topSpace < 0) {
                    if (extraSpace >= topSpace)
                        divTop=baseLstBtm-divHei;
                    else divTop=baseLstTop;
                } else divTop=baseLstTop+topSpace
                //console.log("DT="+divTop);
            }
        }
        divLft=frect.right;
    }
    //console.log("FilterContainer: "+JSON.stringify(filterItem.contenido));
    addBackdropChild("filterContainer",divTop,divLft,divWid,divHei,{className:"pad5 basicBG br1_8",filterId:tgtId,eChilds:[{eName:"DIV",eChilds:filterItem.contenido}]});
    const filterFields=[];
    //console.log("FilterItem.ids = ",filterItem.ids);
    if (filterItem.ids) {
        const ff=ebyid("filterForm");
        for (let i=0; i < filterItem.ids.length; i++) {
            const fItm=ebyid(filterItem.ids[i]);
            if (fItm) {
                fItm.ids=filterItem.ids;
                if (i==0) fItm.focus();
                filterFields.push(fItm);
                switch (filterItem.type) {
                    case "fecha":
                        //console.log("IS CALENDAR",fItm,ff.elements.namedItem(fItm.id));
                        if (fItm.value.length==0) {
                            const fnve=(ff?ff.elements.namedItem(fItm.id):false);
                            if (fnve) fItm.value=fnve.value;
                            else {
                                if (i==0) fItm.value=getCookie("defaultIniDate");
                                else fItm.value=getCookie("defaultEndDate");
                            }
                        }
                        fItm.onclick=function(ev){show_calendar_widget(fItm,'adjustLstSolPCal');ebyid('calendar_widget').style.zIndex=9000;};
                        fItm.onkeypress=function(ev){const cw=ebyid("calendar_widget");if (ev&&(ev.code==="Enter"||ev.code==="Space"||(ev.code==="Escape"&&cw&&cw.style.display!=="none")))fItm.onclick(ev);};
                        //fItm.onkeydown=function(ev){console.log(ev);};
                        break;
                    case "lista":
                        if (fItm.multiple) {
                            //console.log("HAS MULTIPLE");
                            const fnve=(ff?ff.elements.namedItem(fItm.id+"[]"):false);
                            const optLen=fItm.options.length
                            if (fnve) for(let i=0;i < optLen; i++) {
                                for (let j=0;j < fnve.length; j++) {
                                    if (fnve[j].value===fItm.options[i].value) {
                                        //console.log("OPTION VALUE SELECTED="+fnve[j].value);
                                        fItm.options[i].selected=true;
                                        break;
                                    }
                                }
                            } else console.log("No named values");
                        } else {
                            const fnve=(ff?ff.elements.namedItem(fItm.id):false);
                            if (fnve) fItm.value=fnve.value;
                        }
                        break;
                    case "rango":
                    case "texto":
                    default:
                        const fnve=(ff?ff.elements.namedItem(fItm.id):false);
                        if (fnve) fItm.value=fnve.value;
                }
            }
        }
    }
    filterContainer=ebyid("filterContainer");
    if (filterContainer) {
        const filterContainerDiv=filterContainer.firstElementChild;
        if (filterContainerDiv && filterContainerDiv.tagName==="DIV") {
            const filterContainerTable=filterContainerDiv.firstElementChild;
            if (filterContainerTable) {
                const filterContainerWidth=filterContainerTable.clientWidth;//+12;
                //console.log("FIXING FILTERCONTAINER WIDTH TO: "+filterContainerWidth);
                filterContainer.style.width=(filterContainerWidth+12)+"px";
            } else console.log("No existe filterContainerTable");
        } else console.log("NO DIV"+(filterContainerDiv?" ("+filterContainerDiv.tagName+")":""),filterContainerDiv);
    } else console.log("No existe filterContainer");
    //console.log("filterFields=",filterFields);

    fee(lbycn("hasEnterId"),el=>{
        //console.log("Elem with enterId: ",el);
        if (el.hasAttribute("enterId"))
            el.enterId=el.getAttribute("enterId");
        if (el.enterId) {
            const eid=el.enterId;
            //console.log("enterId= "+eid);
            const ee=ebyid(eid);
            if (ee) {
                //console.log("EnterElem: ",ee);
                el.onkeyup=function(ev) {
                    if (ev.key==="Enter")
                        ee.click();
                }
            } else console.log("No EnterElem");
        } else console.log("No EnterId. "+el.enterId);
    });
    const appFBtn=ebyid("appendFilterButton");
    if (appFBtn) appFBtn.onkeyup=function(ev) {
        if (ev.key==="Enter" || ev.key===" ")
            ev.target.click();
    }
    if (appFBtn) appFBtn.onclick=function() {
        //console.log("appendFilterButton click!");
        const filterValues=[];
        for (let i=0; i < filterFields.length; i++) {
            if (filterFields[i].value.length==0) {
                //console.log("Missing Filter Field Value");
                return;
            }
            if (filterFields[i].options && filterFields[i].multiple) {
                for (let o=0, oLen=filterFields[i].options.length; o < oLen; o++) {
                    const opt = filterFields[i].options[o];
                    if (opt.selected) filterValues.push(opt.value||opt.text);
                }
            } else filterValues.push(filterFields[i].value);
        }
        closeBackdrop();
        const fSp=ebyid("filterSpace");
        if (fSp) {
            clearBackdrop();
            const tb=ebyid(tgtId+"block");
            if (tb) {
                const firstSpan=tb.firstElementChild;
                const firstTitle=firstSpan?firstSpan.firstElementChild:null;
                while(firstTitle&&firstTitle.nextSibling) ekil(firstTitle.nextSibling);
                firstSpan.appendChild(ecrea({eText:": "+filterValues.join(" - ")}));
            } else {
                fSp.appendChild(ecrea({eName:"DIV", id:tgtId+"block", className:"fltL pad3 inblock br1_8", eChilds:[{eName:"SPAN", className:"inblock vAlignCenter", eChilds:[{eName:"B", eText:filterItem.texto}, {eText:": "+filterValues.join(" - ")}]}, {eName:"SPAN", className:"inblock relative vAlignCenter padl2 marL2 v24_12", eChilds:[{eName:"IMG", className:"abs_nw btn12 btnOI", id:tgtId+"ren", filterId:tgtId, src:"imagenes/icons/rename12.png", onclick:filterAction}, {eName:"IMG", className:"abs_sw btn12 btnOI", id:tgtId+"del", src:"imagenes/icons/deleteIcon12.png", onclick:filterAction}]}]}));
            }
            let fCkStr=getCookie("filtroListaSolP");
            const oldfCkStr=fCkStr;
            if (fCkStr.length>0) {
                const fCk=JSON.parse(fCkStr);
                console.log("FilterCookie=",fCk);
                fCk[tgtId]=filterValues;
                fCkStr=JSON.stringify(fCk);
                console.log("FilterCookie="+fCkStr);
                const cookieValue=addCookie("filtroListaSolP",fCkStr,7);
                console.log("CookieValue="+cookieValue);
                console.log("FilterCookie="+getCookie("filtroListaSolP"));
                postService("consultas/Logs.php",{action:"doclog",message:"scripts.listasolp:showFilterOptions: COOKIE LOG",filebase:"action",data:JSON.stringify({oldFiltroListaSolP:oldfCkStr,tgtIdName:tgtId,tgtIdValue:filterValues,filtroListaSolP:fCkStr})});
            }
            if (fCkStr!==oldfCkStr) {
                console.log(ebyid(tgtId+"ren").filterId);
                //filterCalcHeight();
                backdropCloseable=false;
                viewWaitBackdrop();
                //showBackdropChild({eName:"IMG",src:"<?=$waitImgName?>",id:"waitCentered",style:true,onclick:true});
                ebyid("filterForm").submit();
            } else {
                //console.log("filtroListaSolP=",fCkStr);
            }
        }
    }
}
function filterAction(evt) {
    const tgt=evt.target;
    //console.log("INI function filterAction ",tgt);
    const id=tgt.id;
    if (!tgt.filterId && tgt.hasAttribute("filterId"))
        tgt.filterId=tgt.getAttribute("filterId");
    if (!tgt.action && tgt.hasAttribute("action"))
        tgt.action=tgt.getAttribute("action");
    const action=tgt.action?tgt.action:id.slice(-3);
    const filterId=tgt.filterId?tgt.filterId:id.slice(0,-3);
    console.log("INI function filterAction action="+action+", "+filterId);
    switch(action) {
        case "ren":
            clearBackdrop(); // debería limpiar solo si no se abre el mismo
            showFilterOptions(evt);
            break;
        case "del":
            clearBackdrop();
            const blockId=filterId+"block";
            ekil(ebyid(blockId));
            let fCkStr=getCookie("filtroListaSolP");
            const oldfCkStr=fCkStr;
            if (fCkStr.length>0) {
                const fCk=JSON.parse(fCkStr);
                //console.log("FilterCookie=",fCk);
                const delName=filterId;
                const delValue=fCk[filterId];
                delete fCk[filterId];
                fCkStr=JSON.stringify(fCk);
                //console.log("FilterCookie="+fCkStr);
                addCookie("filtroListaSolP",fCkStr,7);
                postService("consultas/Logs.php",{action:"doclog",message:"scripts.listasolp:filterAction: COOKIE LOG",filebase:"action",data:JSON.stringify({oldFiltroListaSolP:oldfCkStr,delName:delName,delValue:delValue,filtroListaSolP:fCkStr})});
            }
            //filterCalcHeight();
            backdropCloseable=false;
            viewWaitBackdrop();
            //showBackdropChild({eName:"IMG",src:"<?=$waitImgName?>",id:"waitCentered",style:true,onclick:true});
            ebyid("filterForm").submit();
            break;
    }
}
function filterCalcHeight(evt) {
    //console.log("INI function filterCalcHeight");
    const hDet=ebyid("header_detalle");
    const fSpc=ebyid("filterSpace");
    const fSpCh=fSpc.children;
    let blockHgt=0;
    for(let i=0; i < fSpCh.length; i++) {
        //console.log(i,fSpCh[i]);
        const hgt=fSpCh[i].offsetTop+fSpCh[i].offsetHeight+3;
        if (hgt>blockHgt) blockHgt=hgt;
    }
    if (blockHgt<35) blockHgt=35;
    fSpc.style.height=blockHgt+"px";
}
function checkAll(evt,cls) {
    const tgt=evt.target;
    fee(lbycn(cls), el=>el.checked=tgt.checked);
    //console.log(tgt.checked?"CHECKED":"UNCHECKED");
}
function pymWidFix(evt) {
    const tgt=evt.target;
    const pardiv=tgt.parentNode;
    const partab=pardiv.previousElementSibling;
    //console.log("INI pymWidFix ",pardiv.clientWidth,partab.clientWidth);
    //pardiv.style.display="inline-block";
    pardiv.style.width=(partab.clientWidth+"px");
}
function rompeSelloCR(elem, idx) {
    console.log("INI rompeSelloCR ",elem,idx);
    elem.onclick=null;
    elem.firstElementChild.src='imagenes/icons/crDoc32.png';
    window.setTimeout((e,i)=>{
        e.href=e.href.slice(0,i);
    },300,elem,idx);
}
function rompeSello(solId) {
    const parameters={action:"rompeSello",solId:solId};
    postService("consultas/Archivos.php",parameters,function(msg,pars,state,status){
        if (state==4&&status==200&&msg.length>0) {
            try {
                const jobj=JSON.parse(msg);
                console.log("RESPUESTA ROMPE SELLO: ",jobj);
                if (jobj.action) {
                    if (jobj.action==="refresh") {
                        location.reload(true);
                    } else if (jobj.action==="delay") {
                        //const currTime=ebyid("pie_clock").value;
                        delayTimeout=window.setTimeout(function(){location.reload(true);},5*60*1000);
                    }
                }
                //if (jobj.message) console.log(jobj.message);
            } catch (ex) {
                console.log(msg, ex);
            }
        }
    });
}
function pagoMultiple() {
    console.log("INI pagoMultiple");
    const solIdList=[];
    let misn=0;
    let errn=0;
    fee(lbycn("pymchk"),el=>{
        if(el.checked) {
            const solId=el.getAttribute("solid");
            const rowEl=ebyid("row"+solId);
            const folId=rowEl.getAttribute("folId");
            const prcId=rowEl.getAttribute("prcId");
            const sttId=rowEl.getAttribute("sttId");
            const typId=rowEl.getAttribute("typId");
            if (solIdList.length>0) {
                if (solId < solIdList[0][0]) solIdList.unshift([solId,folId,prcId,sttId,typId]);
                else if (solId >= solIdList[solIdList.length-1][0]) solIdList.push([solId,folId,prcId,sttId,typId]);
                else for(let i=1; i < solIdList.length; i++) {
                    if (solId < solIdList[i][0]) solIdList.splice(i,0,[solId,folId,prcId,sttId,typId]);
                }
            } else solIdList.push([solId,folId,prcId,sttId,typId]);
            if (prcId<3) misn++;
            else if (prcId>3) errn++;
        }
    });
    if (solIdList.length<=0) {
        overlayMessage(getParagraphObject("Debe seleccionar las casillas de las solicitudes a modificar", "errorLabel"),"ERROR");
        const dra=ebyid("dialog_resultarea");
        cladd(dra,["flexCenter","flexColumn"]);
    } else if (errn<=0 && misn<=0) {
        // ToDo: Si todas las solicitudes seleccionadas tienen proceso=3, se realiza postservice para marcar todas como pagadas=>(proceso=4) (ahi se validará nuevamente que las solicitudes ya tengan comprobante de pago (proceso=3))
        //console.log(solIdList);
        clearTimeout(delayTimeout);
        postService("consultas/Facturas.php",{action:"payingMultipleRequest",ids:solIdList}, payingMultipleResponse);
    } else {
        //console.log("LEN:"+solIdList.length+", MISSING:"+misn+", ERROR:"+errn+", LIST: ",solIdList);
        ekil("appendButton");
        const title="ANEXAR COMPROBANTES";
        const rows=[];
        // blk. 0:solId, 1:folId, 2:prcId, 3:sttId, 4:typId
        fee(solIdList,blk=>{
            if (blk[2]==3) {
                //console.log("YA TIENE ANEXO! "+blk[0]);
                rows.push({eName:"TR",style:"height: 20px;",eChilds:[{eName:"TD",style:"height:100%;width:100px;",eChilds:[{eName:"SPAN",style:"display:block;min-height:100%;height:auto !important;height:100%;margin:0 auto;width:100%;font-weight:400;",eText:blk[1]}]},{eName:"TD",style:"height:100%;width:100px;",eChilds:[{eName:"SPAN",style:"display:block;min-height:100%;height:auto !important;height:100%;margin:0 auto;width:100%;text-align:left;",eText:"Ya tiene comprobante de pago"}]}]});
            } else {
                //console.log("SIN COMPROBANTE: "+blk[0]+" ("+blk[2]+")");
                fileId="file["+blk[0]+"]";
                rows.push({eName:"TR",style:"height:20px;",eChilds:[{eName:"TD",style:"height:100%;width:100px;",eChilds:[{eName:"LABEL",htmlFor:fileId,style:"display:block;min-height:100%;height:auto !important;height:100%;margin:0 auto;width:100%;font-weight:400;",eText:blk[1]}]},{eName:"TD",eChilds:[{eName:"INPUT",type:"file",accept:".pdf",solid:blk[0],solfolio:blk[1],id:fileId,className:"paymfile fullWidHigh" }]}]});
            }
        });
        overlayMessage({eName:"TABLE",className:"noApply centered marginH5 wid350px",eChilds:[{eName:"THEAD",className:"centered",eChilds:[{eName:"TR",eChilds:[{eName:"TH",eText:"Solicitud"},{eName:"TH",eText:"Anexar Comprobante de Pago"}]}]},{eName:"TBODY",eChilds:rows},{eName:"TFOOT",eChilds:[{eName:"TR",eChilds:[{eName:"TH",colSpan:"2",id:"message",className:"centered",eChilds:[{eText:"Seleccionar archivos y confirmar."}]}]}]}]},title);
        const canOmitFile=<?=$esSistemas?"true":"false"?>;
        const cba=ebyid("closeButtonArea");
        cba.appendChild(ecrea({eName:"INPUT", type:"button", id:"appendButton", value:"Confirmar", className:"marL4", onclick:function(){
            let canSubmit=true;
            let mE=ebyid("message");
            mE.hasMessage=false;
            const parameters={action:"payingMultipleRequest",ids:solIdList};
            clrem(mE,"errorLabel");
            fee(lbycn("paymfile"),el=>{
                if (el.value.length==0) {
                    if (!canOmitFile && !mE.hasMessage) {
                        mE.textContent="Falta el comprobante de la solicitud "+el.solfolio;
                        cladd(mE,"errorLabel");
                        mE.hasMessage=true;
                        canSubmit=false;
                    }
                } else parameters[el.id]=el.files[0];
            });
            if (!canSubmit) return false;
            const dbx=ebyid("dialogbox");
            const wbx=ebyid("wheelbox");
            cladd(dbx,"hidden");
            clrem(wbx,"hidden");
            ekil(ebyid("appendButton"));
            console.log("PostService: consultas/Facturas.php, Parameters: ",parameters);
            clearTimeout(delayTimeout);
            postService("consultas/Facturas.php",parameters, payingMultipleResponse);
        }}));
    }
}
function payingMultipleResponse(msg, pars, state, status) {
    if (state==4&&status==200) {
        console.log("INI payingMultipleResponse");
        if (msg.length==0) {
            overlayMessage({eName:"H3",eText:"SIN RESPUESTA"},"VACIO");
        } else {
            try {
                const jobj=JSON.parse(msg);
                const ovArr=[];
                if (jobj.message)
                    ovArr.push({eName:"H3",eText:jobj.message});
                if (jobj.info) {
                    let errNum=0;
                    let logNum=0;
                    const resultRows=[];
                    for (let key in jobj.info) {
                        const val=jobj.info[key];
                        const cellData=[];
                        if (key==="error") {
                            val.forEach(txt=>{
                                errNum++;
                                cellData.push({eName:"P",className:"stk cancelLabel",eText:errNum+") "+txt});
                            });
                        } else {
                            if (val.error) {
                                val.error.forEach(txt=>{
                                    errNum++;
                                    cellData.push({eName:"P",className:"stk cancelLabel",eText:"ERROR "+errNum+": "+txt});
                                });
                            }
                            if (val.log) {
                                logNum++;
                                cellData.push({eName:"P",className:"stk",eText:val.log});
                            }
                        }
                        resultRows.push({eName:"TR",eChilds:[{eName:"TD",className:"top",eChilds:[{eName:"P",className:"stk",eText:key}]},{eName:"TD",className:"top",eChilds:cellData}]});
                    }
                    let resultReview="";
                    if (errNum>0) resultReview+="Errores: "+errNum;
                    if (logNum>0) {
                        if (errNum>0) resultReview+=". ";
                        resultReview+="Solicitudes procesadas: "+logNum;
                    } else if (logNum==0&&errNum==0)
                        resultReview="Sin resultados";
                    const resTHead={eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",eChilds:[{eName:"DIV",className:"nowrap pad5 centered",eText:"SOL ID"}]},{eName:"TH",eChilds:[{eName:"DIV",className:"nowrap pad5 centered",eText:"RESULTADO"}]}]}]};
                    const resTBody={eName:"TBODY",eChilds:resultRows};
                    const resTFoot={eName:"TFOOT",eChilds:[{eName:"TR",eChilds:[{eName:"TH",colSpan:"2",eChilds:[{eName:"DIV",eText:resultReview}]}]}]};
                    ovArr.push({eName:"TABLE",className:"widfit noApply",eChilds:[resTHead,resTBody,resTFoot]});
                }
                overlayMessage(ovArr,"RESULTADO");
                ebyid("overlay").callOnClose=function() {
                    overlayClose();
                    viewWaitBackdrop();
                    location.reload(true);
                }
            } catch(ex) {
                overlayMessage(getParagraphObject("Respuesta del servidor inválida. Consulte al administrador", "errorLabel"),"ERROR");
                delete pars.xmlHttpPost;
                postService("consultas/Logs.php",{
                    action:"doclog",
                    message:"scripts.listasolp.payingMultipleResponse Exception",
                    filebase:"error",
                    data:JSON.stringify({exception:ex.toString(),parameters:pars,resMsg:msg})});
            }
        }
        const dra=ebyid("dialog_resultarea");
        cladd(dra,["flexCenter","flexColumn"]);
    } else if (state>4||status!=200) {
        overlayMessage(getParagraphObject("No se pudo obtener respuesta válida del servidor. Consulte al administrador", "errorLabel"),"ERROR");
        delete pars.xmlHttpPost;
        postService("consultas/Logs.php",{
            action:"doclog",
            message:"scripts.listasolp.payingMultipleResponse STATERROR",
            filebase:"error",
            data:JSON.stringify({state:state,status:status,parameters:pars,resMsg:msg})});
        const dra=ebyid("dialog_resultarea");
        cladd(dra,["flexCenter","flexColumn"]);
    }
    setTimeout(onresizeScripts,100);
}
function massAction(chkClass, actionName, readyFunc, exceptionFunc, emptyMessage) {
    console.log("INI massAction "+chkClass);
    let solIdList=[];
    fee(lbycn(chkClass),el=>el.checked?solIdList.push(el.getAttribute("solid")):null);
    console.log("SOL ID LIST: ",solIdList);
    if (solIdList.length>0) {
        clearTimeout(massTimeout);
        const parameters={action:actionName, ids:solIdList};
        postService("consultas/SolPago.php",parameters,getPostRetFunc(readyFunc,exceptionFunc),getPostErrFunc(exceptionFunc));
    } else {
        overlayMessage(getParagraphObject(emptyMessage, "errorLabel"),"ERROR");
    }
    const dra=ebyid("dialog_resultarea");
    cladd(dra,["flexCenter","flexColumn"]);
}
function resendEmail() {
    console.log("INI resendEmail");
    massAction("rsmchk", "massMail", jobj=>console.log("result=",jobj), (errMsg,respTxt)=>console.log("err="+errMsg+", resp="+respTxt), "Debe seleccionar las casillas de las solicitudes a reenviar por correo");
}
function massAuth() {
    console.log("INI massAuth");
    massAction("rsmchk", "massAuth", jobj=>console.log("result=",jobj), (errMsg,respTxt)=>console.log("err="+errMsg+", resp="+respTxt), "Debe seleccionar las casillas de las solicitudes a autorizar");
}
var infClicked=false;
function massPayment(evt) {
    const solData=[];
    fee(lbycn("payBlock"),el=>{
        const solId=el.id.slice(3);
        const chkel=ebyid('chk'+solId);
        if(chkel&&chkel.checked)solData.push({id:solId,folio:el.getAttribute("folid"),type:el.getAttribute("typid")});
    });
    if (solData.length==0) {
        console.log("Ningún checkbox marcado para pagar");
        overlayMessage(getParagraphObject("Debe indicar las solicitudes que va a procesar"),"ERROR");
        return;
    }
    const tgt=evt.target;
    const par=tgt.parentNode;
    const inf=ecrea({eName:"INPUT",id:"massPaymentFileInput",type:"file",accept:".pdf",className:"paymbnk camouflage",onclick:(evt)=>{console.log('CLICK!!');infClicked=true;},onfocus:(evt)=>{console.log("FOCUS!");if(infClicked)setTimeout(ev=>{massPaymentWithFile(ev);},250,evt);},onblur:(evt)=>{console.log("BLUR");},onchange:(evt)=>{console.log("CHANGE");}});
    inf.solData=solData;
    par.insertBefore(inf,tgt);
    inf.focus();
    inf.click();
}
var logSent=false;
var logSentTimeout=false;
var logTimes=0;
var logMaxTimes=10;
function massPaymentWithFile(evt) {
    infClicked=false;
    console.log("INI massPaymentWithFile");
    const tgt=evt.target;
    const files=tgt.files;
    const solData=tgt.solData;
    readyService("consultas/ArchivosMul.php",{action:"massReqPaym",data:solData,files:files},(j,e)=>{
        delete j.params;
        console.log("JOBJ: "+JSON.stringify(j,jsonCircularReplacer()));
        delete e.action;
        delete e.data;
        delete e.files;
        console.log("EXTRA: "+JSON.stringify(e,jsonCircularReplacer()));
        overlayMessage(getParagraphObject(j.message),j.result==="success"?"EXITO":j.result.toUpperCase());
        readyToReload(true);
    }, (m,r,x)=>{
        console.log("ERROR: "+m);
        console.log("TEXT: "+r);
        console.log("EXTRA: "+JSON.stringify(x,jsonCircularReplacer()));
        readyService("consultas/Logs.php",{action:"doclog",message:"Error inesperado",filebase:"error",data:{error:m,text:r,extra:x}},(jj,ee)=>{
            delete jj.params;
            console.log("Error enviado a log: "+JSON.stringify(jj,jsonCircularReplacer()));
            readyToReload(true);
        },(mm,rr,xx)=>{
            console.log("Ocurrió un error al mandar log: "+mm);
            console.log("Texto: "+rr);
            readyToReload(true);
        });
        overlayMessage(getParagraphObject("Ocurrió un error inesperado","errorLabel"),"ERROR");
        readyToReload();
    });
}
function readyToReload(forceSent=false) {
    logTimes++;
    console.log("INI readyToReload "+logTimes);
    if (forceSent) logSent=true;
    if (logSentTimeout) clearTimeout(logSentTimeout);
    if (!logSent) {
        if (logTimes>=logMaxTimes) logSent=true;
        logSentTimeout=setTimeout(readyToReload, 500);
        return;
    }
    logTimes=0;
    const ov=ebyid("overlay");
    if (ov) ov.callOnClose=function() {
        clearTimeout(refreshTimeout);
        refreshTimeout=setTimeout(function() {
            location.reload(true);
        },100);
    };
    else location.reload(true);
}
function generaDoc() {
    //console.log("INI generaDoc");
    let solIdList=[];
    fee(lbycn("pymchk"),el=>el.checked?solIdList.push(el.getAttribute("solid")):null);
    //console.log(solIdList);
    const parameters={action:"genPaymTextFile",ids:solIdList};
    if (solIdList.length>0) {
        clearTimeout(delayTimeout);
        postService("consultas/Facturas.php",parameters, function(msg,pars,state,status) {
            if (state==4&&status==200) {
                if (msg.length>0) {
                    const messages=msg.split("|");
                    if (messages[0].length>0) {
                        const anchorObj={eName:"A",download:"pago.txt",style:{display:"none"},href:"data:text/plain;charset=utf-8,"+encodeURIComponent(messages[0])};
                        const element=ecrea(anchorObj);
                        document.body.appendChild(element);
                        element.click();
                        document.body.removeChild(element);
                    }
                    if (messages.length>1 && messages[1].length>0) {
                        overlayMessage(getParagraphObject(messages[1], "errorLabel"),"ERROR");
                        const dra=ebyid("dialog_resultarea");
                        cladd(dra,["flexCenter","flexColumn"]);
                    }
                } else {
                    overlayMessage(getParagraphObject("Documento vacío", "errorLabel"),"ERROR");
                    const dra=ebyid("dialog_resultarea");
                    cladd(dra,["flexCenter","flexColumn"]);
                }
            } else if (state>4 || status!=200) {
                overlayMessage(getParagraphObject("Servicio no disponible, intente nuevamente más tarde.", "errorLabel"),"ERROR");
                console.log("State="+state+", Status="+status);
                const dra=ebyid("dialog_resultarea");
                cladd(dra,["flexCenter","flexColumn"]);
            }
        });
    } else {
        overlayMessage(getParagraphObject("Debe seleccionar las casillas de las solicitudes a incluir en el documento", "errorLabel"),"ERROR");
        const dra=ebyid("dialog_resultarea");
        cladd(dra,["flexCenter","flexColumn"]);
    }
}
var numericIndex=[6];
var currencyIndex=["$","usd","eur"];
function sortTable(evt) {
    let tgt = evt.target;
    let tbl = tgt.closest('table');
    let tbd = tbl.querySelector('tbody');
    let img = tgt.querySelector('img.srt');
    fee(document.querySelectorAll('img.srt'),el=>{if(el!==img)el.src="imagenes/pt.png";});
    let columnIndex = ""+tgt.cellIndex;
    if (!tbl.coln || tbl.coln!==columnIndex) {
        tbl.coln=""+columnIndex;
        tbl.drct="1";
        if (img) img.src="imagenes/icons/downArrow.png";
    } else {
        if (tbl.drct==="0") {
            tbl.drct="1";
            if (img) img.src="imagenes/icons/downArrow.png";
        } else if (tbl.drct==="1") {
            tbl.drct="-1";
            if (img) img.src="imagenes/icons/upArrow.png";
        } else {
            tbl.drct="0";
            if (img) img.src="imagenes/pt.png";
        }
    }
    let rows = Array.from(tbd.rows); // Omitir la fila de encabezado
    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].textContent.trim().toLowerCase();
        let cellB = rowB.cells[columnIndex].textContent.trim().toLowerCase();
        if (tbl.drct==="1") { // ascending
            if (numericIndex.includes(+tbl.coln)) {
                let currA=cellA.slice(0,3);
                if (cellA.slice(0,1)==="$") currA="$";
                else if (!currencyIndex.includes(currA)) currA=false;
                let currB=cellB.slice(0,3);
                if (cellB.slice(0,1)==="$") currB="$";
                else if (!currencyIndex.includes(currB)) currB=false;
                if ((currA===false&&currB!==false) || currA<currB) return -1;
                if ((currA!==false&&currB===false) || currA>currB) return 1;
                cellA=+cellA.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
                cellB=+cellB.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
                if (cellA<cellB) return -1;
                if (cellA>cellB) return 1;
                return 0;
            }
            return cellA.localeCompare(cellB);
        } else if (tbl.drct==="-1") { // descending
            if (numericIndex.includes(+tbl.coln)) {
                let currA=cellA.slice(0,3);
                if (cellA.slice(0,1)==="$") currA="$";
                else if (!currencyIndex.includes(currA)) currA=false;
                let currB=cellB.slice(0,3);
                if (cellB.slice(0,1)==="$") currB="$";
                else if (!currencyIndex.includes(currB)) currB=false;
                if ((currA!==false&&currB===false) || currA>currB) return -1;
                if ((currA===false&&currB!==false) || currA<currB) return 1;
                cellA=+cellA.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
                cellB=+cellB.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
                if (cellA<cellB) return 1;
                if (cellA>cellB) return -1;
                return 0;
            }
            return cellB.localeCompare(cellA);
        } else {
            cellA = +rowA.getAttribute("idx");
            cellB = +rowB.getAttribute("idx");
            if (cellA<cellB) return -1;
            if (cellA>cellB) return 1;
            return 0;
        }
    });
    rows.forEach(row => tbd.appendChild(row));
}
document.addEventListener('DOMContentLoaded', ()=>{
    let tbl=false;
    fee(lbycn("sortH"), th=>{
        tbl=th.closest('table');
        if (!('coln' in tbl)) { tbl.coln=false; tbl.drct="0"; }
        th.prepend(ecrea({eName:"IMG",src:"imagenes/pt.png",className:"btn10"}));
        th.append(ecrea({eName:"IMG",src:"imagenes/pt.png",className:"btn10 srt"}));
        th.addEventListener('click', sortTable);
        cladd(th,'pointer'); });
});

addEvent(window,"resize",filterCalcHeight);
//let otherOnresizeScript=false;
//if (window.onresize) otherOnresizeScript=window.onresize;
//window.onresize=function(event) {otherOnresizeScript&&otherOnresizeScript();filterCalcHeight();}

addEvent(document, "keydown", function(e) {
    e=e||window.event;
    if (e.repeat) return;
    const aet=document.activeElement.tagName;
    //console.log("KeyCode="+e.keyCode+", Which="+e.which+", Code="+e.code+", Key="+e.key);
    //console.log((e.metaKey?"META ":"")+(e.ctrlKey?"CTRL ":"")+(e.altKey?"ALT ":"")+(e.shiftKey?"SHIFT ":"")+e.code+" => "+aet);
    const bkd=ebyid("backdrop");
    const fcn=ebyid("filterContainer");
    let filterId=false;
    let filterTab=bkd?bkd.firstElementChild:false;
    if (e.code==="KeyF") {
        //console.log(e);
        if (!e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey && aet!=="INPUT" && aet!=="SELECT") {
            if (bkd) {
                if (!fcn) {
                    clrem(lbycn("filterTab"), "selectedTab");
                    bkd.click();
                }
                //else if (!ebyid("folioOrdFld")&&!ebyid("folioInvFld")&&!ebyid("folioSolFld")&&!ebyid("proveedorFld")&&!ebyid("empresaFld")) bkd.click();
            } else ebyid("pickFilter").click();
        }
    } else if (e.code==="Escape") {
        if (fcn) {
            if (fcn.filterId) {
                filterId=fcn.filterId;
                filterTab=ebyid(filterId);
                clrem(lbycn("filterTab"), "selectedTab");
                cladd(filterTab,"selectedTab");
            }
            fee(backdropObj.eChilds, function(item,idx){if(item.id==="filterContainer") backdropObj.eChilds.splice(idx,1);});
            ekil(filterContainer);
            if (bkd && !bkd.firstElementChild)
                bkd.click();
        } else if (bkd) {
            clrem(lbycn("filterTab"),"selectedTab");
            bkd.click();
        }
    } else if (e.code==="ArrowDown"||e.code==="ArrowUp") {
        if (bkd) {
            let nTabs=bkd.children.length;
            if (fcn && nTabs>1) {
                if (document.activeElement) {
                    const daeId=document.activeElement.id;
                    if (daeId==="proveedorFld" || daeId==="empresaFld" || daeId==="seccionFld") return true;
                }
                if (fcn.filterId) filterId=fcn.filterId;
                fee(backdropObj.eChilds, function(item,idx){if(item.id==="filterContainer") backdropObj.eChilds.splice(idx,1);});
                ekil(filterContainer);
                nTabs--;
            }
            const st=lbycn("selectedTab");
            if (filterId||st.length>0) {
                filterTab=filterId?ebyid(filterId):st[0];
                if (e.code==="ArrowDown") {
                    if (filterTab && filterTab.nextElementSibling)
                        filterTab=filterTab.nextElementSibling;
                    else filterTab=bkd.firstElementChild;
                } else {
                    if (filterTab && filterTab.previousElementSibling)
                        filterTab=filterTab.previousElementSibling;
                    else filterTab=bkd.lastElementChild;
                }
            }
            if (filterTab) {
                if (e.code==="ArrowDown")
                    filterTab.firstElementChild.click();
                else
                    filterTab.lastElementChild.click();
            }
        }
    } else if (e.code==="Enter") {
        console.log("ActiveElement=",document.activeElement);
        console.log("HasFocus=",document.hasFocus());
    }
});

let filtroListaSolP = getCookie("filtroListaSolP");
if (filtroListaSolP==="[]")
    addCookie("filtroListaSolP","{\"filter01\":[\"01/05/2021\",\"31/05/2021\"]}",7);
//console.log("filtroListaSolP='"+filtroListaSolP+"'");
<?php
clog1seq(-1);
clog2end("scripts.listasolp");
