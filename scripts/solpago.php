<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    die();
}
$esAdmin = validaPerfil("Administrador");
$esValidado = true;
$esDesarrollo = in_array(getUser()->nombre, ["admin","sistemas"]);
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$esCompras = validaPerfil("Compras");
$esSolPago=validaPerfil("Solicita Pagos");
if (!$esSistemas && !$esSolPago) {
    die();
}
$test = $esDesarrollo;
header("Content-type: application/javascript; charset: UTF-8");

$gpoMap = $_SESSION['gpoMap'];
$gpoRazSocMap = array_combine(array_keys($gpoMap), array_column($gpoMap, 'razonSocial'));
$prvMap = $_SESSION['prvMap'];
global $ugObj, $perObj;
if (!isset($perObj)) {
    require_once "clases/Perfiles.php";
    $perObj=new Perfiles();
}
if (!isset($ugObj)) {
    require_once "clases/Usuarios_grupo.php";
    $ugObj=new Usuarios_Grupo();
}
$ugObj->rows_per_page=0;
$autorizaPagosId=$perObj->getIdByName("Autoriza Pagos");
$ugObj->clearOrder();
$ugData=$ugObj->getData("ug.idPerfil=$autorizaPagosId and g.status='activo'",0,"ug.idGrupo, group_concat(ug.idUsuario) idAutoriza","ug inner join grupo g on ug.idGrupo=g.id","ug.idGrupo");
$gpoAuthMap=array_combine(array_column($ugData, "idGrupo"),array_map(function($res){return explode(",", $res);},array_column($ugData,"idAutoriza")));
$ugObj->addOrder("ug.idUsuario");
$auData=$ugObj->getData("ug.idPerfil=$autorizaPagosId and g.status='activo'",0,"distinct ug.idUsuario, u.persona","ug inner join grupo g on ug.idGrupo=g.id inner join usuarios u on ug.idUsuario=u.id");
$auDtLen=count($auData);
$authNameMap=array_combine(array_column($auData,"idUsuario"),array_column($auData, "persona"));

$autorizaPagosCRId=$perObj->getIdByName("Autoriza PagoCR");
$ugObj->clearOrder();
$ugCrDt=$ugObj->getData("ug.idPerfil=$autorizaPagosCRId and g.status='activo'",0,"ug.idGrupo, group_concat(ug.idUsuario) idAutoriza","ug inner join grupo g on ug.idGrupo=g.id","ug.idGrupo");
$gpoCrAuthMap=array_combine(array_column($ugCrDt, "idGrupo"),array_map(function($res){return explode(",", $res);},array_column($ugCrDt,"idAutoriza")));
$ugObj->addOrder("ug.idUsuario");
$auCrDt=$ugObj->getData("ug.idPerfil=$autorizaPagosCRId and g.status='activo'",0,"distinct ug.idUsuario, u.persona","ug inner join grupo g on ug.idGrupo=g.id inner join usuarios u on ug.idUsuario=u.id");
$auCrLen=count($auCrDt);
$auCrNameMap=array_combine(array_column($auCrDt,"idUsuario"),array_column($auCrDt, "persona"));
?>
const gpoMap=<?=json_encode($gpoRazSocMap)?>;
const prvMap=<?=json_encode($prvMap)?>;
const gpoAuth=<?=json_encode($gpoAuthMap)?>;
const authName=<?=json_encode($authNameMap)?>;
const gpoCrAuth=<?=json_encode($gpoCrAuthMap)?>;
const authCrName=<?=json_encode($auCrNameMap)?>;
const currency=new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN',
});
const currentLogIdf=2;
const maxCycleDepth=50;
var cycleDepth=0;
var timeOutReq0=0;
var xhpReq0=false;
// 0 : solo mostrar logs de errores
// 1 : mostrar logs de inicio de funciones principales
// 2 : mostrar logs de inicio de otras funciones
// 3 : mostrar otros logs
function iniFunc(logIdf, funcName, ...args) {
    logFunc(logIdf, "INI "+funcName, ...args);
    cycleDepth++;
    if (cycleDepth>=maxCycleDepth) throw new Error("Execution stacked more than "+maxCycleDepth+" calls!");
}
function endFunc(logIdf,funcName, ...args) {
    cycleDepth--;
    logFunc(logIdf, "END "+funcName, ...args);
}
function errFunc(funcName, ...args) { // execution continues
    logFunc(0, "ERROR function "+funcName, ...args);
}
function logFunc(logIdf, mainText, ...args) {
    if (currentLogIdf>=logIdf) console.log("["+cycleDepth+"] "+mainText, ...args);
}
function hide(elem) { return cladd(elem,["hidden","zNo"]); }
function show(elem) { return clrem(elem,["hidden","zNo"]); }
function test(elem,boolVal) { return clset(elem,["hidden","zNo"],boolVal); }
function clearRequest(exceptionList) {
    if (!exceptionList) exceptionList=[];
    iniFunc(2,"clearRequest","exceptionList",exceptionList);
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    const esOrden=((invChkbox && !invChkbox.checked)||(docChoice && docChoice.value==="orden"));
    const invfiles=ebyid("invfiles");
    const folio=ebyid("folio");
    const uuid=ebyid("uuid");
    const invArc=ebyid("invArc");
    const invOff=ebyid("invOff");
    const detailI=ebyid("detailI");
    const detailI2=ebyid("detailI2");
    const detailO=ebyid("detailO");
    const artTbd=ebyid("artTbd");
    const invDoc=ebyid("invDoc");
    const docMain=ebyid("docMain");
    const auxDoc=ebyid("auxDoc");
    const invSpc=ebyid("invSpc");
    if (esFactura) {
        if (!exceptionList.includes("invfiles"))
            invfiles.value="";
        if (!exceptionList.includes("folio"))
            folio.value="";
        else if (folio.value.length>0)
            hide (invOff,invArc);
        if (!exceptionList.includes("uuid"))
            uuid.value="";
        else if (uuid.value.length>0)
            hide (invOff,invArc);
        if (!exceptionList.includes("invArc")) {
            invArc.invid=false;
            invArc.gpoId=false;
            invArc.prvId=false;
            if (invArc.textContent.localeCompare("Debe seleccionar XML y PDF simultáneamente")!==0) {
                ekfil(invArc);
                invArc.appendChild(ecrea({eName:"P",eText:"Debe seleccionar XML y PDF simultáneamente"}));
            }
        }
        if (!exceptionList.includes("invOff"))
            invOff.firstElementChild.textContent="Anexar Archivos";
        if (!exceptionList.includes("detailI"))
            hide(detailI);
        if (!exceptionList.includes("detailI2"))
            hide(detailI2);
        if (!exceptionList.includes("artTbd"))
            ekfil(artTbd);
    } else if (esOrden) {
        if (invDoc) hide(invDoc);
        hide([invSpc,invArc,detailI,detailI2]);
        if (!docMain)
            auxDoc.colSpan="3";
    } else { // esContra
        hide (detailI,detailI2,detailO);
    }

    if (!exceptionList.includes("btnRow"))
        hide(["btnRow","sol_obs_row"]);
    endFunc(2,"clearRequest");
}
function clearCheck(avoidClearance) {
    iniFunc(2,"clearCheck","avoidClearance",
    avoidClearance);
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    const esOrden=((invChkbox && !invChkbox.checked)||(docChoice && docChoice.value==="orden"));
    if (!avoidClearance) {
        if (esFactura) clearRequest(["invDoc","folio","auxDoc","invSpc"]);
        else if (esOrden) clearRequest();
        else { /* esContra */ clearRequest(); evalCounter(); }
    }
    endFunc(2,"clearCheck");
}
function clearInvoice(tgtId) {
    iniFunc(2,"clearInvoice","targetId="+tgtId);
    const tgt=ebyid(tgtId);
    const invArc=ebyid("invArc");
    const folio=ebyid("folio");
    const uuid=ebyid("uuid");
    const invOff=ebyid("invOff");
    const detailI=ebyid("detailI");
    const detailI2=ebyid("detailI2");
    const pedido=ebyid("pedido");
    const remision=ebyid("remision");
    const artTbd=ebyid("artTbd");
    const btnRow=ebyid("btnRow");
    invArc.found=false;
    invArc.invid=false;
    invArc.gpoId=false;
    invArc.prvId=false;
    ekfil(invArc);
    //logFunc(2, "tgt",tgt);
    //if (tgt && tgt.value) tgt.value="";
    folio.value="";
    uuid.value="";
    const delFolioImg=ebyid("delFolioImg");
    const delUuidImg=ebyid("delUuidImg");
    delFolioImg.src="imagenes/ledoff.gif";
    delUuidImg.src="imagenes/ledoff.gif";
    //if (folio.value.length==0 && uuid.value.length==0) {
        //logFunc(2,"invArc(P) show(invOff)");
        invArc.appendChild(ecrea({eName:"P",className:"lefted marbtm0",eText:"Debe seleccionar XML y PDF simultáneamente"}));
        show([invArc,invOff]);
    //} else logFunc(2,"folio("+folio.value.length+"), uuid("+uuid.value.length+")");
    hide([btnRow,detailI,detailI2]);
    if (pedido) {
        pedido.value="";
        pedido.dbval="";
    }
    if (remision) {
        remision.value="";
        remision.dbval="";
    }
    ekfil(artTbd);
    if (tgtId==="uuid") {
        ebyid("gpo_alias").value="";
        ebyid("prv_codigo").value="";
        hide(["prv_row2","prv_row3"]);
        ekfil(ebyid("gpo_detail"));
        ekfil(ebyid("prv_detail"));
    }
    endFunc(2,"clearInvoice");
}
function clearCounter() {
    iniFunc(2,"clearCounter");
    const folioCR=ebyid("foliocr");
    const btnRow=ebyid("btnRow");
    folioCR.value="";
    const delFolioCRImg=ebyid("delFolioCRImg");
    delFolioCRImg.src="imagenes/ledoff.gif";
    //hide([btnRow]);
    ekfil("messagecr");
    folioCR.placeholder="";
    endFunc(2,"clearCounter");
}
function waitInvoice(flag) {
    iniFunc(2,"waitInvoice");
    const delFolioImg=ebyid("delFolioImg");
    const delUuidImg=ebyid("delUuidImg");
    if (flag) {
        delFolioImg.src="imagenes/ledyellow.gif";
        clrem(delFolioImg,"grayscale");
        delUuidImg.src="imagenes/ledyellow.gif";
        clrem(delUuidImg,"grayscale");
    } else {
        delFolioImg.src="imagenes/icons/deleteIcon16.png";
        cladd(delFolioImg,"grayscale");
        delUuidImg.src="imagenes/icons/deleteIcon16.png";
        cladd(delUuidImg,"grayscale");
    }
    endFunc(2,"waitInvoice");
}
function waitCounter(flag) {
    iniFunc(2,"waitCounter: "+flag);
    const delFolioCRImg=ebyid("delFolioCRImg");
    const validFlags=["off","yellow","red","green"];
    if (flag) {
        delFolioCRImg.src="imagenes/led"+flag+".gif";
        if (!validFlags.includes(flag)) flag="off";
        if (flag==="off") cladd(delFolioCRImg,"grayscale");
        else {
            clrem(delFolioCRImg,"grayscale");
            if (flag!=="yellow") window.setTimeout(function(img) {
                img.src="imagenes/ledoff.gif";
                cladd(img,"grayscale");
            }, 3000, delFolioCRImg);
        }
    } else { // imagenes/ledoff.gif
        delFolioCRImg.src="imagenes/icons/deleteIcon16.png";
        cladd(delFolioCRImg,"grayscale");
    }
    endFunc(2,"waitCounter");
}
var choices=["factura","orden","contra"];
function kup(evt) {
    if (!evt) evt=window.event;
    //iniFunc(2,"kup "+evt.keyCode);
    const tgt=evt.currentTarget;
    const docChoice=ebyid("docChoice");
    //console.log("docChoice selIdx="+docChoice.selectedIndex);
    if (evt.keyCode=='38') {
        if (docChoice.selectedIndex==0) docChoice.selectedIndex=(docChoice.options.length-1);
        else docChoice.selectedIndex-=1;
        //endFunc(2,"kup 38 "+docChoice.value);
        switchDocument(docChoice);
        return false; // eventCancel(evt);
    } else if (evt.keyCode=='40') {
        if (docChoice.selectedIndex==(docChoice.options.length-1)) docChoice.selectedIndex=0;
        else docChoice.selectedIndex+=1;
        //endFunc(2,"kup 40 "+docChoice.value);
        switchDocument(docChoice);
        return false; // eventCancel(evt);
    }
    //endFunc(2,"kup "+docChoice.value);
    return true;
}
function evalRequest() {
    iniFunc(2,"evalRequest");
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    const esOrden=((invChkbox && !invChkbox.checked)||(docChoice && docChoice.value==="orden"));
    if (esFactura) evalInvoice();
    else if (esOrden) evalOrder();
    else evalCounter();
    endFunc(2,"evalRequest");
}
function evalInvoice(evt) {
    const tgt=evt?evt.target:null;
    const invArc=ebyid("invArc");
    iniFunc(2,"evalInvoice",tgt?tgt.id:null,invArc);
    if (tgt&&tgt.id==="uuid"&&(ebyid("gpo_alias").value.length>0||ebyid("prv_codigo").value.length>0)) {
        const val=tgt.value;
        clearInvoice('uuid');
        tgt.value=val;
    }
    const btnRow=ebyid("btnRow");
    const obsRow=ebyid("sol_obs_row");
    if (hasFullInvoice(tgt)) show([btnRow,obsRow]);
    else hide([btnRow,obsRow]);
    endFunc(2,"evalInvoice");
}
function evalOrder() {
    iniFunc(2,"evalOrder");
    const btnRow=ebyid("btnRow");
    const obsRow=ebyid("sol_obs_row");
    if (hasFullOrder()) show([btnRow,obsRow]);
    else hide([btnRow,obsRow]);
    endFunc(2,"evalOrder");
}
function evalCounter(evt) {
    const foliocr=ebyid("foliocr");
    iniFunc(2,"evalCounter",foliocr.value);
    //const btnRow=ebyid("btnRow");
    //const obsRow=ebyid("sol_obs_row");
    // toDo: si el campo foliocr tiene letras, guión y numeros: separar las letras, cotejar contra alias de empresa y seleccionar en gpo_alias donde el texto coincida y dejar en el campo foliocr sólo los numeros   
    if (foliocr.value.length>0) {
        const parts=splitCharTypes(foliocr.value,{alphaFirst:1,specialStop:2});
        if (parts.alpha && parts.alpha.length>0)
            populateGroup(true,parts.alpha.toUpperCase());
        foliocr.value=""+parts.number;
    }
    hasFullCounter();//if (hasFullCounter()) show([btnRow,obsRow]);
    //else hide([btnRow,obsRow]);
    endFunc(2,"evalCounter");
}
function evalGroupCounter(evt) {
    const foliocr=ebyid("foliocr");
    iniFunc(2,"evalGroupCounter",foliocr.value);
    ekfil("messagecr");
    foliocr.placeholder="";
    if (timeOutReq0) {
        clearTimeout(timeOutReq0);
        timeOutReq0=0;
        if (xhpReq0) {
            xhpReq0.abort();
            xhpReq0=false;
        }
        logFunc(1, "AJX evalGroupCounter. PREV AJAX THREAD CANCELLED");
    }
    timeOutReq0=window.setTimeout(function() {
        timeOutReq0=0;
        evalCounter();
    }, 2000);
    endFunc(2,"evalGroupCounter");
}
function clearGroup() {
    const gpoDet=ebyid("gpo_detail"); ekfil(gpoDet);
    gpoDet.removeAttribute("title");
    const gpoAlias=ebyid("gpo_alias");
    gpoAlias.isValid=false;
    gpoAlias.value="";
}
function populateGroup(avoidClearance,alias) {
    iniFunc(2,"populateGroup","avoidClearance",avoidClearance);
    clearCheck(avoidClearance);
    const gpoDet=ebyid("gpo_detail"); ekfil(gpoDet);
    gpoDet.removeAttribute("title");
    const gpoAlias=ebyid("gpo_alias");
    gpoAlias.isValid=false;
    if (typeof alias !== "undefined") {
        if (alias.length>0) {
            fee(gpoAlias.options,(opt,idx)=>{if(opt.text===alias.toUpperCase()&&gpoAlias.selectedIndex!=idx){gpoAlias.selectedIndex=idx;return true;}gpoAlias.value="";return false;},"some");
        } else gpoAlias.value="";
    }
    if (gpoAlias.value.length==0) {
    } else if (gpoMap[gpoAlias.value]) {
        gpoAlias.isValid=true;
        gpoDet.appendChild(ecrea({"eText":gpoMap[gpoAlias.value]}));
        if (isTruncatedElement(gpoDet,{display:"inline-block"})) {
            gpoDet.title=gpoDet.textContent;
        }
        if (!avoidClearance) evalRequest();
    } else gpoDet.appendChild(ecrea({"eText": "Desconocido"}));
    endFunc(2,"populateGroup");
}
function clearProvider() {
    const prvDet=ebyid("prv_detail"); ekfil(prvDet);
    prvDet.removeAttribute("title");
    const prvBco=ebyid("prv_banco"); ekfil(prvBco);
    const prvCbe=ebyid("prv_clabe"); ekfil(prvCbe);
    const prvStt=ebyid("prv_status"); ekfil(prvStt);
    const prvCode=ebyid("prv_codigo");
    const prvRow2=ebyid("prv_row2");
    const prvRow3=ebyid("prv_row3");
    const btnObj=ebyid("btnObj"); btnObj.confirma="";
    prvCode.isValid=false;
    prvCode.value="";
    hide([prvRow2,prvRow3]);
}
function populateProvider(avoidClearance,codPrv) {
    iniFunc(2,"populateProvider","avoidClearance", avoidClearance);
    clearCheck(avoidClearance);
    const prvDet=ebyid("prv_detail"); ekfil(prvDet);
    prvDet.removeAttribute("title");
    const prvBco=ebyid("prv_banco"); ekfil(prvBco);
    const prvCbe=ebyid("prv_clabe"); ekfil(prvCbe);
    const prvStt=ebyid("prv_status"); ekfil(prvStt);
    const prvCode=ebyid("prv_codigo");
    const prvRow2=ebyid("prv_row2");
    const prvRow3=ebyid("prv_row3");
    const btnObj=ebyid("btnObj"); btnObj.confirma="";
    prvCode.isValid=false;
    if (typeof codPrv !== "undefined") {
        if (codPrv.length>0) {
            fee(prvCode.options,(opt,idx)=>{if(opt.text===codPrv&&prvCode.selectedIndex!=idx){prvCode.selectedIndex=idx;return true;}prvCode.value="";return false;},"some");
        } else prvCode.value="";
    }
    if (prvCode.value.length==0) {
        hide([prvRow2,prvRow3]);
    } else if (prvMap[prvCode.value]) {
        prvObj=prvMap[prvCode.value];
        prvDet.appendChild(ecrea({"eText":prvObj.razonSocial}));
        if (isTruncatedElement(prvDet,{display:"inline-block"})) {
            prvDet.title=prvDet.textContent;
        }
        if (prvObj.banco) prvBco.appendChild(ecrea({"eText":prvObj.banco}));
        if (prvObj.cuenta) prvCbe.appendChild(ecrea({"eText":prvObj.cuenta}));
        let prvStatus=prvObj.status;
        const prvVerif=prvObj.verificado;
        const prvOpina=prvObj.cumplido;
        if (prvStatus==="actualizar") {
            prvStatus="Actualizar datos";
            btnObj.confirma="el proveedor deba actualizar datos";
        } else if (prvVerif==0) {
            let pl0={s:"",n:""};
            prvStatus="Cuenta";
            btnObj.confirma="el estado de cuenta";
            if (prvOpina<=0) {
                prvStatus+=" y ";
                btnObj.confirma+=" y ";
                pl0={s:"s",n:"n"};
                prvStatus+="Opinión";
                btnObj.confirma+="el documento del SAT de Opinión de Cumplimiento";
            }
            prvStatus+=" Pendiente"+pl0.s;
            btnObj.confirma+=" está"+pl0.n+" PENDIENTE"+pl0.s.toUpperCase()+" por aprobar";
        } else if (prvVerif<0 || prvOpina<-1) {
            let pl1={s:"",n:""};
            btnObj.confirma="";
            prvStatus="";
            if (prvVerif<0) {
                prvStatus="Cuenta";
                btnObj.confirma+="el estado de cuenta";
                if (prvOpina<-1) {
                    prvStatus+=" y ";
                    btnObj.confirma+=" y ";
                    pl1={s:"s",n:"n"};
                }
            }
            if (prvOpina<-1) {
                prvStatus+="Opinión";
                btnObj.confirma+="el documento del SAT de Opinión de Cumplimiento";
            }
            prvStatus+=" Rechazada"+pl1.s;
            btnObj.confirma+=" está"+pl1.n+" RECHAZADO"+pl1.s.toUpperCase();
        } else if (prvOpina<0) {
            prvStatus="Opinión Vencida";
            btnObj.confirma="el documento del SAT de Opinión de Cumplimiento VENCIDO";
        } else if (prvOpina==0) {
            prvStatus="Opinión Pendiente";
            btnObj.confirma+="el documento del SAT de Opinión de Cumplimiento está PENDIENTE por aprobar";
        } else {
            prvStatus=prvStatus.toUpperCase();
            if (prvStatus!=="ACTIVO") btnObj.confirma+="el status de proveedor está en "+prvStatus;
        }
        prvCode.isValid=true;
        if (!avoidClearance) evalRequest();
        prvStt.appendChild(ecrea({"eText":prvStatus.toUpperCase()}));
        show([prvRow2,prvRow3]);
    } else {
        hide([prvRow2,prvRow3]);
        prvDet.appendChild(ecrea({"eText": "Desconocido"}));
    }
    endFunc(2,"populateProvider");
}
<?php
if ($esValidado) {
?>
var docType=["factura","orden","contra"];
function switchDocument(evt) {
    iniFunc(1,"switchDocument");
    clearAllTimeouts();
    let tgt=false;
    if ('currentTarget' in evt) tgt=evt.currentTarget;
    else if ('selectedIndex in evt') tgt=evt;
    //console.log("switchDocument "+tgt.value);
    docType.forEach(d=>{if(d!==tgt.value)fee(lbycn("doc"+d),e=>{cladd(e,"hidden");});});
    fee(lbycn("doc"+tgt.value),e=>{clrem(e,"hidden");});
    const obs=ebyid("observaciones");
    obs.value="";
    hide("warningCR");
    const docMain=ebyid("docMain");
    switch(tgt.value) {
        case "factura": docMain.colSpan="1"; clrem(tgt,"fontCondensed"); ebyid("folio").focus(); switchInvoice(); break;
        case "orden": docMain.colSpan="3"; clrem(tgt,"fontCondensed"); ebyid("ordRef").focus(); switchInvoice(); break;
        case "contra":
            docMain.colSpan="3";
            cladd(tgt,"fontCondensed");
            tgt.blur();
            hide(["cancelInvoiceBtn","detailI","detailI2","detailO"]);
            const gpoAlias=ebyid("gpo_alias");
            if (gpoAlias.value.length==0) {
                const gpoRow=ebyid("gpo_row");
                hide(gpoRow);
            }
            const prvCode=ebyid("prv_codigo");
            if (prvCode.value.length==0) {
                const prvRow=ebyid("prv_row");
                const prvRow2=ebyid("prv_row2");
                const prvRow3=ebyid("prv_row3");
                hide([prvRow,prvRow2,prvRow3]);
            }
            const capContra=ebyid("capContra");
            const folioCR=ebyid("foliocr");
            if (capContra && capContra.lastViewed) {
                viewCounter(capContra.lastViewed);
            }
            folioCR.focus();
        break;
        default: tgt.blur();
    }
    endFunc(1,"switchDocument");
}
<?php
}
?>
function switchInvoice() {
    iniFunc(1,"switchInvoice");
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    const esOrden=((invChkbox && !invChkbox.checked)||(docChoice && docChoice.value==="orden"));
    const invCap=ebyid("invCap");
    const invDoc=ebyid("invDoc");
    const docMain=ebyid("docMain");
    const auxDoc=ebyid("auxDoc");
    const folio=ebyid("folio");
    const uuid=ebyid("uuid");
    const invFil=ebyid("invfiles");
    const invSpc=ebyid("invSpc");
    const invArc=ebyid("invArc");
    const ordRef=ebyid("ordRef");
    const ordDoc=ebyid("ordDoc");
    const gpoAlias=ebyid("gpo_alias");
    const prvCode=ebyid("prv_codigo");
    const gpoRow=ebyid("gpo_row");
    const prvRow=ebyid("prv_row");
    const prvRow2=ebyid("prv_row2");
    const prvRow3=ebyid("prv_row3");
    const detailI=ebyid("detailI");
    const detailI2=ebyid("detailI2");
    const detO=ebyid("detailO");
    const artTbd=ebyid("artTbd");
    const totFld=ebyid("importe");
    const cancelBtn=ebyid("cancelInvoiceBtn");
    const btnRow=ebyid("btnRow");
    const obsRow=ebyid("sol_obs_row");
    if (esFactura) {
        if (invCap) invCap.textContent="FACTURA";
        hide([ordRef,ordDoc,detO]);
        if (!docMain)
            auxDoc.colSpan="2";
        if (invDoc) show(invDoc);
        show(cancelBtn);

        if (invArc.invid && (gpoAlias.value!==invArc.gpoId || prvCode.value!==invArc.prvId)) {
            //if (gpoAlias.value!==invArc.gpoId) gpoAlias.value="";
            //if (prvCode.value!==invArc.prvId) prvCode.value="";
            invArc.invid=false;
            invArc.gpoId=false;
            invArc.prvId=false;
            uuid.value="";
            hide([btnRow,obsRow]);
        }
        if (invArc.invid) {
            // si cambió empresa o proveedor 
            
            show([gpoRow,prvRow,prvRow2,prvRow3,detailI,detailI2,btnRow,obsRow]);
        } else {
            show ([invSpc,invArc]);
            if (folio.value.length>0||uuid.value.length>0) {
                hide(invOff);
                if (artTbd.conceptos && artTbd.children.length>0) {
                    show([detailI,detailI2]);
                }
            } else {
                if (gpoAlias.value.length==0)
                    hide(gpoRow);
                if (prvCode.value.length==0)
                    hide([prvRow,prvRow2,prvRow3]);
                hide([btnRow,obsRow]);
            }
        }
        folio.focus();
    } else if (esOrden) {
        if (invCap) invCap.textContent="ORDEN";
        if (invDoc) hide(invDoc);
        hide([invSpc,invArc,cancelBtn,detailI,detailI2]);
        if (!docMain) auxDoc.colSpan="3";
        show([ordRef,ordDoc,detO,gpoRow,prvRow]);
        if (prvCode.value.length>0) {
            show([prvRow2,prvRow3]);
        }
        /*if (ordRef.value.length==0) {
            hide([ordDoc,detO]);
        } else {
            show([ordDoc,detO]);
        }*/
        evalOrder();
        ordRef.focus();
    } else {
        // toDo: esContra
        hide([cancelBtn,detailI,detailI2,detailO]);
    }
    endFunc(1,"switchInvoice");
}
function fixOrdDoc() {
    iniFunc(1,"fixOrdDoc");
    const elem=ebyid("ordFile");
    const ordDoc=ebyid("ordDoc");
    if (elem.files.length>0) {
        const fil=elem.files[0];
        let filname=fil.name;
        if (filname.length>22) filname=filname.slice(0,16)+"...pdf";
        ordDoc.textContent="Cambiar "+filname;
    } else {
        ordDoc.textContent="Anexar PDF";
    }
    evalOrder();
    endFunc(1,"fixOrdDoc");
}
function browseCFDI() {
    iniFunc(2,"browseCFDI");
    const elem=ebyid("invfiles");
    const invArc=ebyid("invArc");
    ekfil(invArc);
    const btnRow=ebyid("btnRow");
    const obsRow=ebyid("sol_obs_row");
    const invIn=ebyid("invIn");
    const invSpc=ebyid("invSpc");
    hide([invIn,invSpc,btnRow,obsRow]);
    if (elem.files.length>0) {
        const items=[];
        let hasXML=false;
        let hasPDF=false;
        let hasError=false;
        for(var i=0; elem.files.length>i; i++) {
            let fil = elem.files[i];
            let prgArr = [];
            let spnName = {eName:"SPAN", style:"vertical-align: bottom;", className:"ellipsis maxWid140 selectedOnFocus test4title", tabIndex:-1, eText:fil.name, onclick:function(evt){
                copyTextToClipboard(evt.target.textContent);
                evt.target.focus();
                const sel = window.getSelection();
                const range=document.createRange();
                range.selectNodeContents(evt.target);
                sel.removeAllRanges();
                sel.addRange(range);
            }};
            if (fil.type==="text/xml") {
                if (hasXML) prgArr=[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:"Error en archivo '"},spnName,{eText:"', registre sólo un XML"}];
                else hasXML=true;
            } else if (fil.type==="application/pdf") {
                if (hasPDF) prgArr=[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:"Error en archivo '"},spnName,{eText:"', registre sólo un PDF"}];
                else hasPDF=true;
            } else prgArr=[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:"Error en archivo '"},spnName,{eText:"', no es XML ni PDF"}];
            if (prgArr.length==0) {
                if (fil.size<=0) prgArr=[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:"Error en archivo '"},spnName,{eText:"', está vacío"}];
                else if (fil.size>2097000) prgArr=[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:"Error en archivo '"},spnName,{eText:"', excede el tamaño máximo de 2MB"}];
            }
            let classname="msg";
            if (items.length==0) classname+=" btopg";
            if (prgArr.length==0) {
                //console.log("classname(prgArr)='"+classname+"'");
                items.push({eName:"P", className:classname, eChilds:[{eName:"B",style:"vertical-align: middle;",eText:" • "},{eText:"El archivo '"},spnName,{eText:"' de "+human_filesize(fil.size, 1)+" es válido"}]});
            } else {
                classname+=" err";
                //console.log("classname(err)='"+classname+"'");
                items.push({eName:"P", className:classname, eChilds:prgArr});
                hasError=true;
            }
        }
        invArc.append(...ecrea(items));
        if (!hasXML) {
            invArc.appendChild(ecrea({eName:"P",className:"msg err",eChilds:[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:"Falta indicar el archivo XML de su factura"}]}));
            hasError=true;
        }
        fee(lbycn("test4title"),function(elem) {
            const extraStyles={className:""};
            const renderedWidth = getElementWidth(elem);
            const fullWidth = getUnboundWidth(elem,extraStyles);
            if (isTruncatedElement(elem,extraStyles)) {
                elem.title=elem.textContent;
            }
        });
        if (!hasError) {
            const fileArray=[];
            for(let i=0; i<elem.files.length; i++)
                fileArray[i]=elem.files[i];
            invArc.appendChild(ecrea({eName:"P",className:"msg",eChilds:[{eName:"IMG",style:"vertical-align:middle;",src:"imagenes/ledyellow.gif"},{eName:"SPAN",className:"padl3",eText:"Esperando respuesta del servidor..."}]}));
            const invDoc=ebyid("invDoc");
            if (invDoc) hide(invDoc);
            postService("consultas/Facturas.php", {action:"saveFiles", files:fileArray}, function(msg,pars,state,status) {
                if (state==4&&status==200) {
                    iniFunc(2,"browseCFDI:saveFiles");
                    try {
                        const jobj=JSON.parse(msg);
                        if (jobj.existe) {
                            endFunc(2,"browseCFDI:saveFiles");
                            foundInvoice(msg,pars,state,status);
                            return;
                        }
                        if (jobj.result) {
                            if (jobj.allow && jobj.allow==="solpago" && jobj.message && jobj.message.length>0) {
                                logFunc(2,"ALLOW EXIST INVOICE!");
                                if (jobj.uuid) {
                                    const uuid=ebyid("uuid");
                                    uuid.value=jobj.uuid;
                                }
                                if (jobj.folio) {
                                    const folio=ebyid("folio");
                                    folio.value=jobj.folio;
                                } else {
                                    const folio=ebyid("folio");
                                    folio.value="";
                                }
                                if (jobj.idgpo) {
                                    const gpoAlias=ebyid("gpo_alias");
                                    gpoAlias.value=jobj.idgpo;
                                }
                                if (jobj.idprv) {
                                    const prv=ebyid("prv_codigo");
                                    prv.value=jobj.idprv;
                                }
                                invArc.invid=false;
                                invArc.gpoId=false;
                                invArc.prvId=false;
                                evalInvoice();
                                endFunc(2,"browseCFDI:saveFiles");
                                return;
                            }
                            if (jobj.result==="refresh") {
                                logFunc(2,"RESULT=REFRESH");
                                location.reload(true);
                            } else if (jobj.result==="success") {
                                logFunc(2,"RESULT=SUCCESS",jobj);
                                ekfil(invArc);
                                if (jobj.id) {
                                    logFunc(2,"INVOICE ID:'"+jobj.id+"'");
                                    invArc.invid=jobj.id;
                                    if (jobj.idgpo) invArc.gpoId=jobj.idgpo;
                                    if (jobj.idprv) invArc.prvId=jobj.idprv;
                                }
                                if (jobj.xml) {
                                    logFunc(2,"XML:'"+jobj.xml+"'");
                                    invArc.appendChild(ecrea({eName:"A",href:jobj.ruta+jobj.xml+".xml", target:"archivo", eChilds:[{eName:"IMG",src:"imagenes/icons/xml200.png",className:"file24"}]}));
                                    if (invDoc) show(invDoc);
                                    show(invOff);
                                    invOff.firstElementChild.textContent="Cambiar Archivos";
                                }
                                if (jobj.pdf) {
                                    logFunc(2,"PDF:'"+jobj.pdf+"'");
                                    invArc.appendChild(ecrea({eName:"A",href:jobj.ruta+jobj.pdf+".pdf", target:"archivo", eChilds:[{eName:"IMG",src:"imagenes/icons/pdf200.png",className:"file24"}]}));
                                }
                                <?= $test?"invArc.appendChild(ecrea({eName:\"A\",href:jobj.ruta+jobj.xml}));":"" ?>
                            } else if (jobj.result==="error") {
                                logFunc(2,"RESULT=ERROR",jobj);
                                ekfil(invArc);
                                if (jobj.message) {
                                    if (/^<\/?[a-z][\s\S]*>/i.test(jobj.message)) {
                                        invArc.innerHTML+=jobj.message;
                                    } else if (jobj.message.slice(0,6)==="<table") {
                                        invArc.innerHTML+=jobj.message;
                                    } else if (jobj.message.slice(0,2)==="<p") {
                                        invArc.innerHTML+=jobj.message;
                                    } else {
                                        invArc.appendChild(ecrea({eName:"P",className:"msg btopg err",eChilds:[{eName:"B",style:"vertical-align: middle;",eText:" × "},{eText:jobj.message}]}));
                                    }
                                }
                                if (jobj.overlayMessage) {
                                    let title="ERROR";
                                    if (jobj.title) title=jobj.title;
                                    const aDiv=ecrea({eName:"DIV",className:"padhtt"});
                                    aDiv.innerHTML=jobj.overlayMessage;
                                    const isText=(aDiv.textContent===jobj.overlayMessage);
                                    overlayMessage(isText?getParagraphObject(jobj.overlayMessage, "errorLabel"):aDiv,title);
                                }
                                fee(lbycn("cfdiErrorList"),function(el) {
                                    cladd(el,"err");
                                    clrem(el,"mbpi");
                                    let bl=el;
                                    if (bl.firstElementChild) {
                                        if (bl.firstElementChild.tagName==="TBODY") bl=bl.firstElementChild;
                                        if (bl.firstElementChild && bl.firstElementChild.tagName==="TR") fee(bl.children,function(rl) {
                                            const cl=rl.firstElementChild;
                                            if (cl && cl.tagName==="TD") {
                                                if (cl.firstElementChild && cl.firstElementChild.tagName==="B") cl.firstElementChild.innerHTML=" × "+cl.firstElementChild.innerHTML;
                                                else cl.insertBefore(ecrea({eName:"B",style:"vertical-align: middle;",eText:" × "}),cl.firstChild);
                                            }
                                        });
                                    }
                                });
                                elem.value="";
                            } else logFunc(2,"RESULT=INVALID: "+msg);
                            if (jobj.uuid) {
                                logFunc(2,"UUID:'"+jobj.uuid+"'");
                                const uuid=ebyid("uuid");
                                uuid.value=jobj.uuid;
                                uuid.dbval=jobj.uuid;
                                show(invSpc);
                                const delFolioImg=ebyid("delFolioImg");
                                const delUuidImg=ebyid("delUuidImg");
                                delFolioImg.src="imagenes/icons/deleteIcon16.png";
                                cladd(delFolioImg,"grayscale");
                                delUuidImg.src="imagenes/icons/deleteIcon16.png";
                                cladd(delUuidImg,"grayscale");
                            }
                            if (jobj.folio) {
                                logFunc(2,"INVOICE FOLIO:'"+jobj.folio+"'");
                                const folio=ebyid("folio");
                                folio.value=jobj.folio;
                                folio.dbval=jobj.folio;
                                show(invIn);
                            } else {
                                logFunc(2,"INVOICE FOLIO IS EMPTY!");
                                const folio=ebyid("folio");
                                folio.placeholder="Sin folio";
                                folio.value="";
                                folio.dbval="";
                                show(invIn);
                            }
                            if (jobj.idgpo) {
                                logFunc(2,"GROUP ID:'"+jobj.idgpo+"'");
                                const gpoAlias=ebyid("gpo_alias");
                                gpoAlias.value=jobj.idgpo;
                                gpoAlias.dbval=jobj.idgpo;
                                const gpoRow=ebyid("gpo_row");
                                populateGroup(true);
                                show(gpoRow);
                            }
                            if (jobj.idprv) {
                                logFunc(2,"PROVIDER ID:'"+jobj.idprv+"'");
                                const prv=ebyid("prv_codigo");
                                prv.value=jobj.idprv;
                                prv.dbval=jobj.idprv;
                                const prvRow=ebyid("prv_row");
                                populateProvider(true);
                                show(prvRow);
                            }
                            //if (jobj.debug) console.log("DEBUG en "+jobj.debug);
                            displayConceptsTable(jobj,true);
                            const pedido=ebyid("pedido");
                            if (pedido) {
                                pedido.value="";
                                pedido.dbval="";
                                pedido.focus();
                            }
                            const remision=ebyid("remision");
                            if (remision) {
                                remision.value="";
                                remision.dbval="";
                                remision.focus();
                            }
                            if (invDoc) show(invDoc);
                            show(invOff);
                            if (jobj.result==="success")
                                show([btnRow,obsRow]);
                        } else errFunc("browseCFDI:saveFiles", "NO RESULT: "+msg);
                    } catch (ex) {
                        errFunc("browseCFDI:saveFiles",ex,msg);
                        invArc.innerHTML="Error en respuesta del servidor";
                        postService("consultas/Logs.php",{action:"doclog",message:"[SOLPAGO.SAVEFILES] "+ex.message+" | "+msg});
                    }
                    endFunc(2,"browseCFDI:saveFiles");
                } else if (state>4 || status!=200) {
                    errFunc("browseCFDI:saveFiles", "STATE:"+state+", STATUS:"+status+". MESSAGE:"+msg);
                }
            }, function(errmsg, params, evt) {
                errFunc("browseCFDI:saveFiles", "MESSAGE: "+errmsg, "PARAMETERS: ", params, "EVENT: ", evt);
            });
        } else errFunc("browseCFDI", "Errores en Pantalla");
    } else { // no hay archivos seleccionados
        clearRequest();
    }
    endFunc(2,"browseCFDI");
}
function displayConceptsTable(jobj,hasAtSign) {
    const atSign=(hasAtSign?"@":"");
    iniFunc(3,"displayConceptsTable"+(hasAtSign?" "+atSign:""));
    //console.log("jobj: ",jobj);
    const acps=atSign+"claveprodserv";
    if (jobj.conceptos && ((jobj.conceptos.length>0)||(acps in jobj.conceptos))) {
        if (acps in jobj.conceptos) jobj.conceptos=[jobj.conceptos];
        const artList=[];
        const artTbd=ebyid("artTbd");
        ekfil(artTbd);
        artTbd.conceptos=jobj.conceptos;
        let suma=0;
        fee(jobj.conceptos,function(c,idx) {
            //console.log("CONCEPTO "+(idx+1),c);
            const impval = +c[atSign+"importe"];
            suma+=impval;
            const cUnidad=c[atSign+"unidad"];
            const satUnidad=c[atSign+"satunidad"];
            let cPrdSrv=c[atSign+"descripcion"];
            let satPrdSrv=c[atSign+"satprodserv"];
            if (!satPrdSrv) satPrdSrv="";
            if (!cPrdSrv) cPrdSrv=satPrdSrv;
            let codigoBlk={eName:"INPUT", type:"text", className:"codigoArticulo codigo", idx:idx};
            const cId=c[atSign+"id"];
            if (cId && cId.length>0) {
                codigoBlk.idt=cId;
                codigoBlk.className+=" idt";
                //codigoBlk.setAttribute("idt", codigoBlk.idt);
            }
            const cCode=c[atSign+"codigo"];
            if (cCode && cCode.length>0) {
                codigoBlk.value=cCode;
                codigoBlk.oldValue=codigoBlk.value;
                codigoBlk.className+=" oldValue";
                //codigoBlk.setAttribute("oldValue",codigoBlk.value);
            }
            artList.push({eName:"TR",eChilds:[{eName:"TD", className:"padr3 lefted", eChilds:[{eName:"DIV", className:"ellipsis", eText:Number(c[atSign+"cantidad"]).toString()+" "+cUnidad}]}, {eName:"TD", className:"padL2 lefted", eChilds:[codigoBlk]}, {eName:"TD", className:"padv10 lefted", eChilds:[{eName:"DIV", className:"ellipsis", style:"width:182px;", title:cPrdSrv, eText:cPrdSrv}]}, {eName:"TD", eChilds:[{eName:"DIV", className:"righted", eText:currency.format(+c[atSign+"valorunitario"])}]}, {eName:"TD", eChilds:[{eName:"DIV", className:"righted", eText:currency.format(impval)}]}]});
            let esMismoProdServ=((cPrdSrv===satPrdSrv)||cPrdSrv.indexOf(satPrdSrv)>=0);
            let cps=[{eName:"DIV",className:"baseline ellipsis fontSmalli vaBasei",eText:c[acps]}];
            let dps=[{eName:"DIV",className:"baseline ellipsis fontSmalli vaBasei",eText:satPrdSrv}];
            if (c[atSign+"subcveprdsrv"]) {
                const scps=c[atSign+"subcveprdsrv"];
                for(cve in scps) {
                    cps.push({eName:"DIV",className:"baseline ellipsis blocki fontSmalli vaBasei",eText:cve});
                    dps.push({eName:"DIV",className:"baseline ellipsis blocki fontSmalli vaBasei",eText:scps[cve]});
                    if(!esMismoProdServ) esMismoProdServ=(cPrdSrv===scps[cve]||cPrdSrv.indexOf(scps[cve])>=0);
                }
                cps=[{eName:"DIV",id:"cc_"+idx,className:"mxHg50 yFlow minScrBar",onscroll:function(ev){forceScroll(ev.target,ebyid('cd_'+idx));},eChilds:cps}];
                dps=[{eName:"DIV",id:"cd_"+idx,className:"mxHg50 yFlow minScrBar",onscroll:function(ev){forceScroll(ev.target,ebyid('cc_'+idx));},eChilds:dps}];
            }
            artList.push({eName:"TR",className:"satKeys request",eChilds:[{eName:"TD", className:"padr3 lefted fontSmalli"+((cUnidad===satUnidad)?" bggreen2":""), eChilds:[{eName:"DIV",className:"baseline ellipsis fontSmalli vaBasei",eText:c[atSign+"claveunidad"]+"="+satUnidad}]}, {eName:"TD",className:"padL2 lefted fontSmalli"+(esMismoProdServ?" bggreen2":""),eChilds:cps},{eName:"TD",className:"padL10 lefted fontSmalli"+(esMismoProdServ?" bggreen2":""),colSpan:"3",eChilds:dps}]});
        });
        const epsilon=jobj.epsilon?Number(jobj.epsilon):0;
        if(jobj.subtotal) artTbd.subtotal=Number(jobj.subtotal);
        if(jobj.descuento) artTbd.descuento=-Number(jobj.descuento);
        if(jobj.isr) artTbd.isr=-Number(jobj.isr);
        if(jobj.iva) artTbd.iva=Number(jobj.iva);
        if(jobj.total) artTbd.total=Number(jobj.total);
        const baseRow={eName:"TR",eChilds:[{eName:"TD",colSpan:"4",eChilds:[{eName:"DIV",className:"righted padr3 boldValue"}]}, {eName:"TD",eChilds:[{eName:"DIV",className:"righted"}]}]};
        logFunc(3,"LOG displayConceptsTable","BaseRow: ",baseRow);
        if(!artTbd.subtotal||Math.abs(suma-artTbd.subtotal)>epsilon) {
            const sumRow=JSON.parse(JSON.stringify(baseRow));
            sumRow.eChilds[0].eChilds[0].eText="SUMA";
            sumRow.eChilds[1].eChilds[0].eText=currency.format(suma);
            artList.push(sumRow);
            logFunc(3,"LOG displayConceptsTable","SumRow: ",sumRow);
        }
        if (artTbd.subtotal) {
            const sbtRow=JSON.parse(JSON.stringify(baseRow));
            sbtRow.eChilds[0].eChilds[0].eText="SUBTOTAL";
            sbtRow.eChilds[1].eChilds[0].eText=currency.format(artTbd.subtotal);
            artList.push(sbtRow);
            logFunc(3,"LOG displayConceptsTable","SbtRow: ",sbtRow);
        }
        if (artTbd.descuento) {
            const dscRow=JSON.parse(JSON.stringify(baseRow));
            dscRow.eChilds[0].eChilds[0].eText="DESCUENTO";
            dscRow.eChilds[1].eChilds[0].eText=currency.format(artTbd.descuento);
            artList.push(dscRow);
            logFunc(3,"LOG displayConceptsTable","DscRow: ",dscRow);
        }
        if (artTbd.isr) {
            const isrRow=JSON.parse(JSON.stringify(baseRow));
            isrRow.eChilds[0].eChilds[0].eText="ISR";
            isrRow.eChilds[1].eChilds[0].eText=currency.format(artTbd.isr);
            artList.push(isrRow);
            logFunc(3,"LOG displayConceptsTable","ISRRow: ",isrRow);
        }
        if (artTbd.iva) {
            const ivaRow=JSON.parse(JSON.stringify(baseRow));
            ivaRow.eChilds[0].eChilds[0].eText="IVA";
            ivaRow.eChilds[1].eChilds[0].eText=currency.format(artTbd.iva);
            artList.push(ivaRow);
            logFunc(3,"LOG displayConceptsTable","IVARow: ",ivaRow);
        }
        if (artTbd.total) {
            const ttlRow=JSON.parse(JSON.stringify(baseRow));
            ttlRow.eChilds[0].eChilds[0].eText="TOTAL";
            ttlRow.eChilds[1].eChilds[0].eText=currency.format(artTbd.total);
            artList.push(ttlRow);
            logFunc(3,"LOG displayConceptsTable","TotRow: ",ttlRow);
        }
        artTbd.append(...ecrea(artList));
        // ToDo: buscar elementos con class idt, si tienen property (elem.idt) asignar a atributo: elem.setAttribute('idt')=elem.idt;
        // ToDo: buscar elementos con class oldValue, si tienen property (elem.oldValue) asignar a atributo: elem.setAttribute('oldValue')=elem.oldValue;
        show(["detailI","detailI2"]);
    } else {
        logFunc(3,"LOG displayConceptsTable: sin conceptos");
        hide(["detailI","detailI2"]);
    }
    endFunc(3,"displayConceptsTable");
}
function hasFullInvoice(tgt) {
    if (timeOutReq0) {
        clearTimeout(timeOutReq0);
        timeOutReq0=0;
        if (xhpReq0) {
            xhpReq0.abort();
            xhpReq0=false;
        }
        logFunc(1, "AJX hasFullInvoice:fndInv4Req. PREV AJAX THREAD CANCELLED");
    }
    const invArc=ebyid("invArc");
    iniFunc(2,"hasFullInvoice",tgt?tgt.id:null,"invid:"+(invArc.invid?invArc.invid:"FALSE"));
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    if (!esFactura) {
        endFunc(2,"hasFullInvoice","No Invoice");
        return false;
    }
    let retval=false;
    let retmsg="";
    const folio=ebyid("folio");
    const uuid=ebyid("uuid");
    const gpoAlias=ebyid("gpo_alias");
    const prvCode=ebyid("prv_codigo");
    const invIn=ebyid("invIn");
    const invSpc=ebyid("invSpc");
    const invOff=ebyid("invOff");
    const gpoRow=ebyid("gpo_row");
    const prvRow=ebyid("prv_row");
    const prvRow2=ebyid("prv_row2");
    const prvRow3=ebyid("prv_row3");
    const invDoc=ebyid("invDoc");
    const docMain=ebyid("docMain");
    const detailI=ebyid("detailI");
    const detailI2=ebyid("detailI2");
    const pedido=ebyid("pedido");
    const remision=ebyid("remision");
    const artTbd=ebyid("artTbd");
    const obsRow=ebyid("sol_obs_row");
    // show(invDoc]); // determinar si es util o no
    if (invArc.invid) { //  || invArc.found
        if(folio.value===folio.dbval && uuid.value===uuid.dbval && gpoAlias.value===gpoAlias.dbval && prvCode.value===prvCode.dbval) {
            retmsg="DB OK";
            retval=true;
        } else {
            clearInvoice();
            retmsg="NOT FOUND";
        }
    } else {
        const parameters = {action:"findInvoiceForRequest"};
        if (folio.value.length>0 || uuid.value.length>0) {
            if (folio.value.length>0)
                parameters.folio=folio.value;
            if (uuid.value.length>0)
                parameters.uuid=uuid.value;
            hide([invOff,invArc]);
            show([invIn,invSpc]);
            if (folio.value.length>0) show([gpoRow,prvRow]);
            if (prvCode.value.length>0)
                show([prvRow,prvRow2,prvRow3]);
            if (gpoAlias.value.length>0) show(gpoRow);
        } else {
            ekfil(invArc);
            invArc.appendChild(ecrea({eName:"P",className:"lefted marbtm0",eText:"Debe seleccionar XML y PDF simultáneamente"}));
            show([invIn,invSpc,invOff,invArc]);
            if (prvCode.value.length==0)
                hide([prvRow,prvRow2,prvRow3]);
            if (gpoAlias.value.length==0) hide(gpoRow);
        }
        if (gpoAlias.value.length>0)
            parameters.gpoId=gpoAlias.value;
        //else hide(gpoRow);
        if (prvCode.value.length>0)
            parameters.prvId=prvCode.value;
        //else hide([prvRow,prvRow2,prvRow3]);
        if ((folio.value.length>0 && gpoAlias.value.length>0 && prvCode.value.length>0) || uuid.value.length>=6) {
            timeOutReq0=window.setTimeout(function () {
                timeOutReq0=0;
                waitInvoice(true);
                logFunc(2, "READY FOR postService(consultas/Facturas.php)", parameters);
                xhpReq0=postService("consultas/Facturas.php", parameters, foundInvoice);
            }, 1000);
            retmsg="AJAX THREAD";
        } else {
            retmsg="INCOMPLETE";
            hide([detailI,detailI2]);
            if (pedido) {
                pedido.value="";
                pedido.dbval="";
            }
            if (remision) {
                remision.value="";
                remision.dbval="";
            }
            ekfil(artTbd);
        }
    }
    endFunc(2,"hasFullInvoice",retmsg);
    return retval;
}
function foundInvoice(msg,pars,state,status) {
    if (state==4&&status==200) {
        iniFunc(2,"FoundInvoice");
        waitInvoice(false);
        const invArc=ebyid("invArc");
        try {
            const jobj=JSON.parse(msg);
            logFunc(1, "AJX FoundInvoice. MSG: ", msg);
            const folio=ebyid("folio");
            if (jobj.folio) folio.value=jobj.folio;
            else {
                folio.value="";
                folio.placeholder="Sin folio";
            }
            if (jobj.uuid) {
                const uuid=ebyid("uuid");
                uuid.value=jobj.uuid;
            }
            if (jobj.gpoId) {
                const gpoAlias=ebyid("gpo_alias");
                if (!gpoMap[jobj.gpoId]) {
                    throw new Error("No tiene permiso para agregar facturas de "+(jobj.gpoAlias?jobj.gpoAlias:"esa empresa"));
                }
                gpoAlias.value=jobj.gpoId;
                gpoAlias.dbval=jobj.gpoId;
                //console.log("FOUND GROUP ID: "+jobj.gpoId);
                populateGroup(true);
                show("gpo_row");
            }
            if (jobj.prvId) {
                const prvCode=ebyid("prv_codigo");
                prvCode.value=jobj.prvId;
                prvCode.dbval=jobj.prvId;
                populateProvider(true);
                show(["prv_row","prv_row2","prv_row3"]);
            }
            // Mostrar archivos
            if (jobj.conceptos) {
                invArc.found=true;
                const pedido=ebyid("pedido");
                if (pedido) {
                    pedido.value=jobj.pedido;
                    pedido.dbval=jobj.pedido;
                }
                const remision=ebyid("remision");
                if (remision) {
                    remision.value=jobj.remision;
                    remision.dbval=jobj.remision;
                }
                displayConceptsTable(jobj,false);
            }
            ekfil(invArc);
            show(invArc);
            if (jobj.xml && jobj.ruta) {
                invArc.appendChild(ecrea({eName:"A",href:jobj.ruta+jobj.xml+".xml", target:"archivo", eChilds:[{eName:"IMG",src:"imagenes/icons/xml200.png",className:"file24"}]}));
            }
            if (jobj.pdf && jobj.ruta) {
                invArc.appendChild(ecrea({eName:"A",href:jobj.ruta+jobj.pdf+".pdf", target:"archivo", eChilds:[{eName:"IMG",src:"imagenes/icons/pdf200.png",className:"file24"}]}));
            }
            if (jobj.result==="success") {
                invArc.invid=jobj.invId;
                if (jobj.gpoId) invArc.gpoId=jobj.gpoId;
                if (jobj.prvId) invArc.prvId=jobj.prvId;
                show(["btnRow","sol_obs_row"]);
            } else {
                invArc.appendChild(ecrea({eName:"P",className:"lefted marbtm0",eText:jobj.message}));
                hide(["btnRow","sol_obs_row"]);
            }
        } catch (ex) {
            overlayMessage(getParagraphObject(ex.message, "errorLabel"),"ERROR");
            errFunc("FoundInvoice", ex, "MSG("+msg.length+"):'"+msg+"'");
        }
        endFunc(2,"FoundInvoice","invid:"+(invArc.invid?invArc.invid:"FALSE"));
    } else if (state>4 || status!=200) {
        errFunc("FoundInvoice", "STATE:"+state+", STATUS:"+status+", MSG:'"+msg+"'");
        btnObj.value="Solicitar Autorización";
    }
}
function hasFullOrder() {
    iniFunc(2,"hasFullOrder");
    if (timeOutReq0) {
        clearTimeout(timeOutReq0);
        timeOutReq0=0;
        if (xhpReq0) {
            xhpReq0.abort();
            xhpReq0=false;
        }
        logFunc(1, "AJX hasFullOrder. PREV AJAX THREAD CANCELLED");
    }
    const gpoAlias=ebyid("gpo_alias");
    const prvCode=ebyid("prv_codigo");
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    //const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    const esOrden=((invChkbox && !invChkbox.checked)||(docChoice && docChoice.value==="orden"));
    const ordRef=ebyid("ordRef");
    const ordFil=ebyid("ordFile");
    const impFld=ebyid("importe");
    endFunc(2,"hasFullOrder");
    return (gpoAlias.isValid||gpoAlias.options.length==1) && prvCode.isValid && esOrden && ordRef.value && ordFil.files.length>0 && impFld.value; // ToDo impFld numerico
}
function hasFullCounter() {
    iniFunc(2,"hasFullCounter");
    if (timeOutReq0) {
        clearTimeout(timeOutReq0);
        timeOutReq0=0;
        if (xhpReq0) {
            xhpReq0.abort();
            xhpReq0=false;
        }
        logFunc(1, "AJX hasFullCounter. PREV AJAX THREAD CANCELLED");
    }
    //const detailC=ebyid("detailC");
    const esContra=(docChoice && docChoice.value==="contra");
    if (!esContra) {
        endFunc(2,"hasFullCounter","No Contra");
        return false;
    }
    const parameters={action:"findCounterForRequest"};
    const foliocr=ebyid("foliocr");
    const gpoAlias=ebyid("gpo_alias");
    const gpoRow=ebyid("gpo_row");
    const prvCode=ebyid("prv_codigo");
    const prvRow=ebyid("prv_row");
    const prvRow2=ebyid("prv_row2");
    const prvRow3=ebyid("prv_row3");
    if (foliocr && foliocr.value.length>0) { // <aliasGrupo>-<folio>
        parameters.folio=foliocr.value;
        if (gpoAlias.value.length>0) parameters.gpoId=gpoAlias.value;
        else gpoAlias.focus();
        show(gpoRow); // [gpoRow,prvRow]
        if (prvCode.value.length>0) show([prvRow,prvRow2,prvRow3]);
        timeOutReq0=window.setTimeout(function() {
            timeOutReq0=0;
            waitCounter("yellow");
            xhpReq0=readyService("consultas/SolPago.php",parameters,foundCounter,notFoundCounter);
        }, 1000);
        retmsg="AJAX THREAD";
    } else {
        if (gpoAlias.value.length==0) {
            hide(gpoRow);
            prvCode.value="";
        }
        if (prvCode.value.length==0) hide([prvRow,prvRow2,prvRow3]);
    }
    endFunc(2,"hasFullCounter","CONSTRUCTION");
    return false;
}
function notFoundCounter(errmsg, other, extra) {
    iniFunc(2,"notFoundCounter: "+errmsg+", OTHER:", other, "EXTRA:", extra);
    const mcr=ebyid("messagecr");
    if (mcr) {
        const crba=ebyid("crReactButtonArea");
        const maxLen = clhas(crba,"hidden")?40:30;
        if (errmsg>maxLen) {
            mcr.title=errmsg;
            mcr.textContent=errmsg.slice(0,maxLen-3)+"...";
        } else mcr.textContent=errmsg;
    }
    waitCounter("red");
    beep(false);
    const foliocr=ebyid("foliocr");
    foliocr.placeholder="";
    const gpoAlias = ebyid("gpo_alias");
    const prvCode=ebyid("prv_codigo");
    appendCounter({result:"error",message:errmsg,contra:{aliasGrupo:gpoAlias.options[gpoAlias.selectedIndex].text,folio:foliocr.value,codigoProveedor:prvCode.options[prvCode.selectedIndex].text,message:errmsg}});
    foliocr.value="";
    foliocr.focus();
}
function foundCounter(jobj, extra) {
    iniFunc(2,"foundCounter. FOUND:", jobj, "EXTRA:", extra);
    const isSuccess=(jobj.result==="success");
    const isError=(jobj.result==="error");
    const foliocr=ebyid("foliocr");
    if (isError && jobj.code) switch(jobj.code) {
        case 1: break;
        case 0:
        case 2:
        case 3:
        case 4: if (jobj.message) {
            const mcr=ebyid("messagecr");
            if (mcr) mcr.textContent=jobj.message;
            foliocr.placeholder="";
        }
    }
    waitCounter(isSuccess?"green":(isError?"red":"yellow"));
    if (jobj.contra) appendCounter(jobj);
    else if (isError) {
        const gpoAlias = ebyid("gpo_alias");
        const prvCode=ebyid("prv_codigo");
        const errmsg=jobj.message?jobj.message:("Error "+(jobj.code?jobj.code:"no identificado"));
        appendCounter({result:"error",message:errmsg,contra:{aliasGrupo:gpoAlias.options[gpoAlias.selectedIndex].text,folio:foliocr.value,codigoProveedor:prvCode.options[prvCode.selectedIndex].text,message:errmsg}});
    }
    beep(isSuccess);
    if (isSuccess) {
        //show("sol_obs_row");
        const badOnes=lbycn(["bgred0i","bgred2b"]);
        if (!badOnes || badOnes.length==0) show("btnRow"); // [,"sol_obs_row"] // 
    } else hide("btnRow"); // [,"sol_obs_row"]
    foliocr.value="";
    foliocr.focus();
}
function mergeCrBtn() {
    //if (!evt) evt=window.event;
    //const elem=evt?evt.currentTarget:false;
    iniFunc(2,"mergeCrBtn"); // +": ",elem
}
function mergeCrChkSwitch() {
    ;
}
function delCounterBtn(evt) {
    if (!evt) evt=window.event;
    const tgt=evt?evt.currentTarget:false;
    const capContra=ebyid("capContra");
    iniFunc(2,"delCounterBtn: ",tgt,tgt.parentNode.data);
    if (tgt&&tgt.parentNode.data) {
        const currBtn=tgt.parentNode;
        let blk=false;
        if (clhas(currBtn,"bgred2b")) {
            if (capContra.lastViewed==currBtn) capContra.lastViewed=false;
            clswt("detContra","bgred","bgwhite2");
            ekfil("detContra");
            if (!capContra.lastViewed && capContra.children.length>0) capContra.lastViewed=capContra.firstElementChild;
        } else if (clhas(currBtn,"bggold")) ekfil("detContra");
        if (clhas(currBtn,"crBlk")) blk=currBtn.crBlk;
        const cudtcr=currBtn.data.contra;
        const cuinfo=cudtcr?cudtcr.aliasGrupo+"|"+cudtcr.codigoProveedor+"|"+cudtcr.folio+"|"+cudtcr.id:"";
        ekil(currBtn);
        if (blk) {
            const blkLst=lbycn("crBlk"+blk);
            if (blkLst.length<2) {
                //console.log("Removing crBlk id="+blk+"("+blkLst.length+"): "+cuinfo);
                fee(blkLst,b=>{
                    const bdc=b.data.contra;
                    const bi=bdc.aliasGrupo+"|"+bdc.codigoProveedor+"|"+bdc.folio+"|"+bdc.id;
                    //console.log("Clearing Block References idblk="+b.crBlk+": "+bi);
                    delete b.crBlk;
                    clrem(b,["crBlk","crBlk"+blk]);
                    //fee(lbycn("mrgCR",b),it=>cladd(it,"hidden"));
                    cladd(lbycn("crReact"),"hidden");
                });
            } console.log("Keeping crBlk id="+blk+": More than 1",blkLst);
        } console.log("NO BLK ID");
    }
    clearGroup(); clearProvider(); clearCounter();
    const badOnes=lbycn(["bgred0i","bgred2b"]);
    if (badOnes && badOnes.length>0) hide("btnRow"); // [,"sol_obs_row"]
    else if (capContra.children.length>0) {
        show("btnRow"); // [,"sol_obs_row"]
        setInterval(cc=>{viewCounter(cc.firstElementChild);},10,capContra);
    } else hide(["btnRow","sol_obs_row"]);
    endFunc(2,"delCounterBtn");
    return eventCancel(evt);
}
function appendCounter(jobj) {
    iniFunc(2,"appendCounter");
    const capContra=ebyid("capContra");
    let lblBtnCtr=false;
    const isSuccess=(jobj.result==="success");
    if (isSuccess) lblBtnCtr=jobj.contra.aliasGrupo+"-"+jobj.contra.folio;
    else if (jobj.result.length>0) lblBtnCtr=jobj.result.toUpperCase();
    let btnContra=(lblBtnCtr&&lblBtnCtr.length>0)?ebyid("cap"+lblBtnCtr):false;
    let errIdx=-1;
    if (btnContra) {
        if (isSuccess) {
            btnContra.data=jobj; // Actualiza información, posiblemente no habría que actualizar
        } else {
            let dataExists=false;
            if (btnContra.data.length>0) btnContra.data.forEach((j,i)=>{const n=jobj.contra,o=j.contra;if(n.aliasGrupo===o.aliasGrupo && n.folio===o.folio) { dataExists=true; errIdx=i; }});
            if (!dataExists) {
                btnContra.data.unshift(jobj);
                errIdx=0;
            }
        }
    } else {
        btnContra=ecrea({eName:"BUTTON",id:"cap"+lblBtnCtr,className:"block mar1 fontMedium fontNrrwi relative",eChilds:[{eText:lblBtnCtr},{eName:"IMG",src:"imagenes/icons/crossRed.png",title:"Descartar",className:"abs_n rgtm10 size10",onclick:delCounterBtn}/*,{eName:"IMG",src:"imagenes/icons/merge.png",title:"Integrar",className:"abs_s mrgCR rgtm11 size10 hidden",onclick:mergeCrBtn}*/],data:(isSuccess?jobj:[jobj]),obs:"",onclick:evt=>{viewCounter(evt.currentTarget);}});
        if (isSuccess) {
            //console.log("--- appendCounter --- SUCCESS");
            let last=false;
            let curr=capContra.firstElementChild;
            for (;isBigger(btnContra, curr); curr=curr.nextElementSibling);
            const dtcr=btnContra.data.contra;
            const crcr=(curr&&curr.data&&curr.data.contra)?curr.data.contra:{aliasGrupo:"-",codigoProveedor:"-",folio:"-",id:"-"};
            //console.log("INSERT BTNCONTRA "+dtcr.aliasGrupo+"|"+dtcr.codigoProveedor+"|"+dtcr.folio+"|"+dtcr.id+" BEFORE "+crcr.aliasGrupo+"|"+crcr.codigoProveedor+"|"+crcr.folio+"|"+crcr.id);
            capContra.insertBefore(btnContra,curr);
        } else {
            //console.log("--- appendCounter --- FAILURE");
            capContra.appendChild(btnContra);
            errIdx=0;
        }
    }
    viewCounter(btnContra,errIdx);
    endFunc(2,"appendCounter");
}
let currBlkNo=0;
function isBigger(newElem, testElem) {
    if (!testElem || !newElem || !testElem.data || !newElem.data || !testElem.data.contra || !newElem.data.contra) {
        let rsn="no reason";
        if (!testElem) rsn="no old elem";
        else if (!newElem) rsn="no new elem";
        else if (!testElem.data) rsn="no old data";
        else if (!newElem.data) rsn="no new data";
        else if (!testElem.data.contra) rsn="no old contra";
        else if (!newElem.data.contra) rsn="no new contra";
        //console.log("IS BIGGER: FALSE ("+rsn+")");
        return false;
    }
    const nwDtCr=newElem.data.contra;
    const ttDtCr=testElem.data.contra;
    const lcAlias=nwDtCr.aliasGrupo.localeCompare(ttDtCr.aliasGrupo);
    let trace=(lcAlias<0?"":"+")+lcAlias;
    if (lcAlias) {
        //console.log((lcAlias>0?"IS":"NOT")+" BIGGER("+trace+") "+nwDtCr.aliasGrupo+" THAN "+ttDtCr.aliasGrupo);
        return lcAlias>0;
    }
    const lcCdPrv=nwDtCr.codigoProveedor.localeCompare(ttDtCr.codigoProveedor);
    trace+=(lcCdPrv<0?"":"+")+lcCdPrv;
    if (lcCdPrv) {
        //console.log((lcCdPrv>0?"IS":"NOT")+" BIGGER("+trace+") "+nwDtCr.aliasGrupo+"|"+nwDtCr.codigoProveedor+" THAN "+ttDtCr.aliasGrupo+"|"+ttDtCr.codigoProveedor);
        return lcCdPrv>0;
    }
    //console.log("FOUND SAME BLOCK "+trace+": "+ttDtCr.aliasGrupo+"="+nwDtCr.aliasGrupo+" y "+ttDtCr.codigoProveedor+"="+nwDtCr.codigoProveedor);
    if (!testElem.crBlk) {
        currBlkNo++;
        testElem.crBlk=currBlkNo; // ttDtCr.id;
        //console.log("ASSIGNING NEW BLKID="+testElem.crBlk);
        cladd(testElem,["crBlk","crBlk"+testElem.crBlk]);
        //fee(lbycn("mrgCR",testElem),it=>clrem(it,"hidden"));
    } //else console.log("ASSIGNING CURR BLKID="+testElem.crBlk);
    newElem.crBlk=testElem.crBlk;
    cladd(newElem,["crBlk","crBlk"+testElem.crBlk]);
    //fee(lbycn("mrgCR",newElem),it=>clrem(it,"hidden"));
    const result=nwDtCr.id > ttDtCr.id;
    const lcId=result?"1":(nwDtCr.id < ttDtCr.id?"-1":"0");
    trace+=(lcId<0?"":"+")+lcId;
    //if (nwDtCr.id>ttDtCr.id) console.log("IS BIGGER("+trace+") "+nwDtCr.aliasGrupo+"|"+nwDtCr.codigoProveedor+"|"+nwDtCr.folio+" THAN "+ttDtCr.aliasGrupo+"|"+ttDtCr.codigoProveedor+"|"+ttDtCr.folio);
    //else console.log("NOT BIGGER("+trace+") "+nwDtCr.aliasGrupo+"|"+nwDtCr.codigoProveedor+"|"+nwDtCr.folio+" THAN "+ttDtCr.aliasGrupo+"|"+ttDtCr.codigoProveedor+"|"+ttDtCr.folio);
    return result;
}
function viewCounter(elem, errIdx) {
    clearAllTimeouts();
    const capContra=ebyid("capContra");
    capContra.lastViewed=elem;
    const gpoAlias=ebyid("gpo_alias");
    const prvCodigo=ebyid("prv_codigo");
    const msgcr=ebyid("messagecr");
    msgcr.textContent="";
    const foliocr=ebyid("foliocr");
    foliocr.placeholder="";
    const obs=ebyid("observaciones");
    obs.value=elem.obs;
    try {
        iniFunc(2,"viewCounter",elem,elem.data?elem.data:false,errIdx);
        if (!elem) {
            endFunc(2,"viewCounter: not elem");
            gpoAlias.value="";
            prvCodigo.value="";
            foliocr.focus();
            return false;
        }
        if (!elem.data) {
            endFunc(2,"viewCounter: doesnt have elem.data");
            gpoAlias.value="";
            prvCodigo.value="";
            foliocr.focus();
            return false;
        }
        const data=elem.data;
        const detContra=ebyid("detContra");
        if (Array.isArray(data)) {
            if (data.length==0) {
                endFunc(2,"viewCounter: no data");
                foliocr.focus();
                return false;
            }
            const isRed=clhas(elem,"bgred2b");
            if (isRed && data.length==detContra.children.length) {
                if (errIdx>=0) {
                    const dataErr = data[errIdx];
                    const dEContra=dataErr.contra;
                    if (dataErr.message) {
                        msgcr.textContent=dataErr.message;
                        console.log("FOUND ERR MSG");
                    } else if (dEContra.message) {
                        msgcr.textContent=dEContra.message;
                        console.log("FOUND CONTRA ERR MSG");
                    } else console.log("NOT FOUND JOBJ/CONTRA ERR MSG");
                    if (msgcr.textContent.length>0) foliocr.placeholder=dEContra.aliasGrupo+"-"+dEContra.folio;
                }
                endFunc(2,"viewCounter: already viewed");
                foliocr.focus();
                return false;
            } else if (isRed) {
                console.log("IS RED BUT data.length="+data.length+", AND detContra.children.length="+detContra.children.length);
            }
            ekfil(detContra);
            if (!isRed) {
                clswt(detContra,"bgwhite2","bgred");
                clswt(lbycn("bgred2b"),"bgred2b","bgred0i");
                clrem(lbycn("bggold"),"bggold");
                clswt(elem,"bgred0i","bgred2b");
            }
            //console.log("detContra",detContra);
            populateGroup(true,data[0].contra.aliasGrupo);
            populateProvider(true,data[0].contra.codigoProveedor);
            if (data[0].message) data[0].contra.message=data[0].message;
            msgcr.textContent=data[0].contra.message;
            foliocr.placeholder=data[0].contra.aliasGrupo+"-"+data[0].contra.folio;
            // Todo: desglose de errores por contra
            data.forEach(o=>{detContra.appendChild(ecrea({eName:"P",className:"stk2 lefted",eText:o.contra.aliasGrupo+"-"+o.contra.folio+", Prv "+o.contra.codigoProveedor+" : "+o.contra.message,alias:o.contra.aliasGrupo,folio:o.contra.folio,codigo:o.contra.codigoProveedor,mensaje:o.contra.message,onclick:evt=>{const tgt=evt.currentTarget;populateGroup(true,tgt.alias);populateProvider(true,tgt.codigo);ebyid("messagecr").textContent=tgt.mensaje;ebyid("foliocr").placeholder=tgt.alias+"-"+tgt.folio;}}));});
            hide("sol_obs_row");
            endFunc(2,"viewCounter: array message");
            foliocr.focus();
            return false;
        }
        const contra=data.contra;
        const alias=contra.aliasGrupo;
        populateGroup(true,alias);
        const codPrv=contra.codigoProveedor;
        populateProvider(true,codPrv);
        const crFolio=alias+"-"+contra.folio;
        foliocr.placeholder=crFolio;
        if (clhas(elem,"bggold")) {
            endFunc(2,"viewCounter: already viewed");
            foliocr.focus();
            return false;
        }
        ekfil(detContra);
        clswt(detContra,"bgred","bgwhite2");
        clswt(lbycn("bgred2b"),"bgred2b","bgred0i");
        clrem(lbycn("bggold"),"bggold");
        cladd(elem,"bggold");
        const plS1=((+contra.numAutorizadas)==1?"":"s");
        let cpobs="";
        if (contra.esCopia) cpobs+="Es COPIA";
        else cpobs+="Es ORIGINAL";
        if (contra.estaImpreso) {
            cpobs+=", impreso "+contra.estaImpreso+" ve";
            if (contra.estaImpreso==1) cpobs+="z";
            else cpobs+="ces";
        }
        let imgName="crDoc32.png";
        if (contra.esCopia) imgName="cr2Cop32.png";
        else imgName="cr2Ori32.png";
        const crLinkObj={eName:"A",href:"consultas/Contrarrecibos.php?folio="+crFolio,crId:contra.id,tries:5,eChilds:[{eName:"IMG",className:"btn20",src:"imagenes/icons/"+imgName}]};
        if (!contra.esCopia) crLinkObj.onclick=fixCR;
        detContra.appendChild(ecrea({eName:"DIV",className:"m_xW_dFl_w pad2 bgwhite2",eChilds:[{eName:"H3",className:"martop5",eChilds:[{eText:"Contra Recibo: "+crFolio+" "},crLinkObj]},{eName:"INPUT",type:"hidden",id:"currContraId",value:contra.id},{eName:"P",className:"lefted marbtm2",eChilds:[{eName:"B",eText:"REVISION: "},{eText:" "+contra.fechaRevision.slice(0,10)},{eName:"B",eText:" CREDITO: "},{eText:contra.credito+" dias"},{eName:"B",eText:" PAGO: "},{eText:contra.fechaPago}]},]}));
        // ,{eName:"P",className:"lefted marbtm2 relative",eChilds:[{eName:"B",eText:"TOTAL: "},{eText:"$"+(+contra.total).toFixed(2).replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",")},{eName:"SPAN",className:"abs_e",eChilds:[/*{eText:cpobs},*/crLinkObj]}]}
        // ,{eName:"P",className:"marbtm2",eText:"AUTORIZACIONES: "+contra.numAutorizadas+"/"+contra.numContraRegs+" autorizada"+plS1}
        const validBody=[];
        const invalidBody=[];
        let validSum=0;
        let fullSum=0;
        if (data.validas && data.validas.length>0) {
            for(let i=0; i < data.facturas.length; i++) {
                //console.log("DEBUG: dataLoop i="+i);
                let facti=data.facturas[i];
                let tc=facti.tipoComprobante.toLowerCase();
                //console.log("DEBUG: tc(0)="+tc);
                if (tc.length>1) tc=tc.slice(0,1);
                //console.log("DEBUG: tc(1)="+tc);
                let tot= +facti.total;
                //console.log("DEBUG: tot(0)="+tot);
                if (tc==="e") tot*=-1;
                else if (tc!=="i") tot=0;
                //console.log("DEBUG: tot(1)="+tot);
                if (data.validas.includes(facti.id)) { // fechaFactura, fechaCaptura, serie, folio, uuid, pedido, total, statusEnContra
                    const sttn=+facti.statusn;
                    //console.log("DEBUG: sttn="+sttn);
                    if (sttn>=128) invalidBody.push(invObj2TableRow(facti,"CANCELADO"));
                    else if (sttn>=32) invalidBody.push(invObj2TableRow(facti,"PAGADO"));
                    else if (sttn<1) invalidBody.push(invObj2TableRow(facti,"PENDIENTE"));
                    else if (!(sttn&2)) invalidBody.push(invObj2TableRow(facti,"SIN CONTRA RECIBO"));
                    else {
                        validBody.push(invObj2TableRow(facti,"AUTORIZADA"));
                        validSum+=tot;
                    }
                } else invalidBody.push(invObj2TableRow(facti,"NO AUTORIZADA"));
                fullSum+=tot;
                //console.log("DEBUG: fullSum="+fullSum);
            }
        }
        const validHeadr=[{eName:"TR",className:"bgbrown",eChilds:[{eName:"TH",eText:"Creacion"},{eName:"TH",eText:"Captura"},{eName:"TH",eText:"Folio"},{eName:"TH",eText:"TOTAL"},{eName:"TH",eText:"STATUS"},{eName:"TH",eText:"DOCS"}<?=$test?",{eName:\"TH\",eChilds:[{eName:\"INPUT\",type:\"checkbox\",id:\"chkAllInv\"}]}":""?>]}]; // ,{eName:"TH",eText:"Serie"},{eName:"TH",eText:"UUID"},{eName:"TH",eText:"PEDIDO"}
        const validFootr=[{eName:"TR",eChilds:[{eName:"TH",className:"righted",colSpan:3,eText:"TOTAL AUTORIZADO: "},{eName:"TH",className:"righted",eText:"$"+validSum.toFixed(2).replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",")},{eName:"TH",className:"righted",colSpan:3,eText:validBody.length+"/"+(validBody.length+invalidBody.length)}]}];
        if (fullSum>validSum) validFootr.unshift({eName:"TR",eChilds:[{eName:"TH",className:"righted",colSpan:3,eText:"TOTAL GLOBAL: "},{eName:"TH",className:"righted",eText:"$"+fullSum.toFixed(2).replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",")}]});
<?php if ($test) { ?>
        validFootr.push({eName:"TR",id:"sumChkdRow",className:"hidden",eChilds:[{eName:"TH",className:"righted",colSpan:3,eText:"TOTAL SELECCIONADO: "},{eName:"TH",id:"sumChkdInvTotal",className:"righted",eText:"$",sum:0},{eName:"TH",className:"centered",colSpan:2,eChilds:[{eName:"INPUT",type:"button",id:"splitCRBtn",value:"SEPARAR",className:"hidden",onclick:validateSplitCR}]},{eName:"TD",className:"righted smaller",id:"numChkdInv",eText:"0"}]});
<?php } ?>
        if (invalidBody.length>0) validBody.push(...invalidBody);
        detContra.appendChild(ecrea({eName:"DIV",className:"m_xW_dFl_w pad2 bgwhite2",eChilds:[{eName:"TABLE",className:"centered pad2c",eChilds:[{eName:"THEAD",eChilds:validHeadr},{eName:"TBODY",eChilds:validBody},{eName:"TFOOT",eChilds:validFootr}]}]}));
        if (elem.crBlk && elem.crBlk>0) {
            const crBlkLst=lbycn("crBlk"+elem.crBlk);
            const crBlkRws=[];
            //if (crBlkLst.length>0) crBlkRws.push({eName:"P",eText:"Integrar otros contra recibos"});
            fee(crBlkLst,ce=>{
                ;
            });
            detContra.appendChild(ecrea({eName:"DIV",className:"m_xW_dFl_w pad2 bgwhite2",eChilds:crBlkRws}));
        }
        show("sol_obs_row");
<?php if ($test) { ?>
        window.setTimeout(x=>{
            const cai=ebyid("chkAllInv");
            if (!cai) console.log("Lapso de tiempo muy corto para generar casilla para elegir todas las casillas");
            else {
                cai.onclick=function(evt) {
                    let num=0;
                    let sum=0;
                    const chkd=this.checked;
                    fee(lbycn("chkInvRow"),cir=>{
                        cir.checked=chkd;
                        if(chkd) {
                            num++;
                            sum+=+cir.invObj.total;
                        }
                    });
                    ebyid("numChkdInv").setVal(num);
                    ebyid("sumChkdInvTotal").setVal(sum);
                    clset("splitCRBtn","hidden",num<=0||num>=x);
                }.bind(cai);
            }
            const nci=ebyid("numChkdInv");
            if(!nci) console.log("Lapso de tiempo muy corto para generar celda de Numero de Elegidos");
            else {
                nci.getVal=function(){return +(this.textContent);}.bind(nci);
                nci.setVal=function(n){this.textContent=""+n;clset("sumChkdRow","hidden",n<=0);clset("splitCRBtn","hidden",n<=0||n>=x);if(n==0)ebyid("chkAllInv").checked=false;}.bind(nci);
                nci.addVal=function(n){this.setVal(n+this.getVal());}.bind(nci);
            }
            const scit=ebyid("sumChkdInvTotal");
            if (!scit) console.log("Lapso de tiempo muy corto para generar celda de Total de Elegidos");
            else {
                scit.getVal=function(){return this.sum;}.bind(scit);
                scit.setVal=function(a){this.sum=a;this.textContent="$"+a.toFixed(2).replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");}.bind(scit);
                scit.addVal=function(a){this.setVal(this.getVal()+a);}.bind(scit);;
            }
        },300, validBody.length);
<?php } ?>
        endFunc(2,"viewCounter");
        foliocr.focus();
        return true;
    } finally {
        if (gpoAlias.value.length==0) hide("gpo_row");
        else show("gpo_row");
        if (prvCodigo.value.length==0) hide(["prv_row","prv_row2","prv_row3"]);
        else show(["prv_row","prv_row2","prv_row3"]);
    }
}
function validateSplitCR(evt) {
    console.log("INI validateSplitCR");
    const invRows=[];
    const invIds=[];
    fee(lbycn("chkInvRow"),cir=>{
        if (cir.invObj && cir.checked) {
            invRows.push(invObj2TableRow(cir.invObj,false,"Sp"));
            invIds.push(cir.invObj.id);
        }
    });
    overlayMessage([getParagraphObject("Estas facturas se van a asignar a un nuevo contra recibo:","padt20 nomargini boldValue",true),ecrea(
        {eName:"TABLE",id:"inv2SpltTbl",className:"pad2c",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",className:"bgbrown",eChilds:[{eName:"TH",eText:"Creación"},{eName:"TH",eText:"Captura"},{eName:"TH",eText:"Folio"},{eName:"TH",eText:"Total"},{eName:"TH",eText:"Status"},{eName:"TH",eText:"Docs"}]}]},{eName:"TBODY",eChilds:invRows},{eName:"TFOOT",eChilds:[{eName:"TR",eChilds:[{eName:"TD",id:"splitFootArea",colSpan:"6",className:"centered",eChilds:[{eName:"INPUT",type:"button",value:"Confirmar",className:"margin5",invList:invIds,crId:ebyid("currContraId").value,onclick:confirmSplitCR}]}]}]}]})],"Asigna a Nuevo Contra Recibo");
        cladd("closeButtonArea","hidden");
        const cb=ebyid("closeButton");
        cb.restoreOnClose=()=>{ebyid("closeButtonArea").appendChild(ebyid("closeButton"));clrem("closeButtonArea","hidden");};
        ebyid("splitFootArea").appendChild(cb);

    // al presionar botón se hace readyService a consultas/Contrarrecibos.php con el id del contra recibo y los id de facturas a palomear
    // el service genera el nuevo contra pasando las facturas y regresa el folio del nuevo contra
    // la ventana de dialogo sigue abierta y se incluye el nuevo folio y el botón de confirmar se cambia a cerrar, y se eliminan las facturas del recuadro detras del dialogo
    // se ajustan los campos de total
}
var splitLock=false;
var splitTimeout=false;
function confirmSplitCR(evt) {
    const tgt=evt.currentTarget;
    if (splitLock) { console.log("LOCKED confirmSplitCR crId="+tgt.crId+", invLst=",tgt.invList); return; }
    splitLock=true;
    console.log("INI confirmSplitCR crId="+tgt.crId+", invLst=",tgt.invList);
    splitTimeout=setTimeout(()=>{console.log("END confirmSplitCR");splitLock=false;},60000);
    readyService("consultas/Contrarrecibos.php", {action:"splitCR",crId:tgt.crId,invLst:tgt.invList}, (jobj,extra)=>{
        if(jobj.result==="success") {
            let id=false;
            if ("parameters" in extra) id=extra.parameters.id;
            else if ("id" in extra) id=extra.id;
            if (id) {
                console.log("SUCCESS ID="+id);
            } else console.log("NO ID",JSON.stringify(extra,jsonCircularReplacer()));
        } else console.log("NO SUCCESS",JSON.stringify(jobj,jsonCircularReplacer()));
    },(e,t,x)=>{
        iniFunc(2,"ERR FUNCTION");
        console.log("ERROR: "+e+"\nMSG: "+t+"\nEXTRA: ",JSON.stringify(x,jsonCircularReplacer()));
        endFunc(2,"ERR FUNCTION");
    });
}
function checkHit(tgt) { console.log("INI checkHit",tgt);}
var eaCache={};
function invObj2TableRow(invObj,status,sfx) {
    const isAuth=(status==="AUTORIZADA");
    const classname=status?(isAuth?"bggreen":"bgred"):"";
    let fechaFactura=invObj.fechaFactura;
    let horaFactura=fechaFactura.slice(11);
    fechaFactura=fechaFactura.slice(0,10);
    let fechaCaptura=invObj.fechaCaptura;
    let horaCaptura=fechaCaptura.slice(11);
    fechaCaptura=fechaCaptura.slice(0,10);
    const imgDocs=[];
    if (invObj.nombreInterno.length>0) imgDocs.push({eName:"A",href:invObj.ubicacion+invObj.nombreInterno+".xml",target:"archivo",title:"CFDI-XML",eChilds:[{eName:"IMG",className:"btn20",src:"imagenes/icons/xml200.png"}]});
    if (invObj.nombreInternoPDF.length>0) imgDocs.push({eName:"A",href:invObj.ubicacion+invObj.nombreInternoPDF+".pdf",target:"archivo",title:"CFDI-PDF",eChilds:[{eName:"IMG",className:"btn20",src:"imagenes/icons/pdf200.png"}]});
    else imgDocs.push({eName:"IMG",className:"btn20",src:"imagenes/icons/invChk200.png",className:"pointer",title:"ANEXAR ARCHIVO PDF",onclick:evt=>{generaFactura(invObj.nombreInterno,invObj.ciclo);}});
    //console.log("F "+invObj.folio+" ("+invObj.id+") EA="+invObj.ea);
    if (invObj.ea==1) {
        if (invObj.id in eaCache) {
            imgDocs.push({eName:"A",href:eaCache[invObj.id],target:"archivo",title:"EA-PDF",eChilds:[{eName:"IMG",className:"btn20",src:"imagenes/icons/pdf200EA.png"}]});
        } else readyService("consultas/Archivos.php", {action:"getDoc",type:"ea",id:invObj.id}, (jobj,extra)=>{
            if(jobj.result==="success") {
                let id=false;
                if ("parameters" in extra) id=extra.parameters.id;
                else if ("id" in extra) id=extra.id;
                if (id) {
                    eaCache[id]=jobj.eapath+jobj.eaname+".pdf";
                    window.setTimeout(pushEADoc,100,id,1);
                } else console.log("NO ID",JSON.stringify(extra,jsonCircularReplacer()));
            } else console.log("NO SUCCESS",JSON.stringify(jobj,jsonCircularReplacer()));
        });
    } // else console.log("NO EA "+invObj.id+" = "+invObj.ea);
    if (!sfx) sfx="";
    <?= $test?"const chkArea=[];\n    if (isAuth) chkArea.push({eName:\"INPUT\",type:\"checkbox\",id:\"chk\"+sfx+invObj.id,invObj:invObj,className:\"chkInvRow\",onclick:evt=>{const tgt=evt.currentTarget;const chkVal=(tgt.checked?1:-1);ebyid(\"numChkdInv\").addVal(chkVal);ebyid(\"sumChkdInvTotal\").addVal(chkVal*tgt.invObj.total);}});":"";  ?>
    return {eName:"TR",className:classname,eChilds:[{eName:"TD",title:horaFactura,eText:fechaFactura},{eName:"TD",title:horaCaptura,eText:fechaCaptura},{eName:"TD",eText:invObj.folio},{eName:"TD",className:"righted",eText:"$"+(+invObj.total).toFixed(2).replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",")},{eName:"TD",eText:status},{eName:"TD",id:"docs"+sfx+invObj.id,eChilds:imgDocs}<?=$test?",{eName:\"TD\",eChilds:chkArea}":""?>]}; // ,{eName:"TD",eText:invObj.serie},{eName:"TD",eText:invObj.uuid},{eName:"TD",eText:invObj.pedido}
}
function pushEADoc(id,depth) {
    //iniFunc(2,"pushEADoc "+id+" : "+eaCache[id]+" ("+depth+")");
    const eaCell=ebyid("docs"+id);
    if (!eaCell) {
        depth++;
        if (depth<=10) window.setTimeout(pushEADoc,100*depth,id,depth);
        return;
    }
    //console.log("CELL=",eaCell);
    eaCell.appendChild(ecrea({eName:"A",href:eaCache[id],target:"archivo",title:"EA-PDF",eChilds:[{eName:"IMG",className:"btn20",src:"imagenes/icons/pdf200EA.png"}]}));
}
function fixCR(evt,tgt) {
    if (evt) tgt=evt.currentTarget;
    if (!tgt) return; // toDo: log
    window.setTimeout(function(el) {
        postService("consultas/Contrarrecibos.php",{action:"isCopy",id:el.crId},function(msg,params,state,status){
            if (state==4&&status==200) {
                if (msg==="1") {
                    const img=el.firstElementChild;
                    img.src="imagenes/icons/cr2Cop32.png";
                    el.removeAttribute("onclick");
                } else if (el.tries>0) {
                    el.tries--;
                    fixCR(false,el);
                }
            }
        });
    },100,tgt);
}
function generaFactura(factName,yearCycle) {
    let expUrl='templates/factura.php?nombre='+factName;
    if (yearCycle && yearCycle.length>0) expUrl+='&ciclo='+yearCycle;
    //console.log("expUrl: ",expUrl);
    window.open(expUrl,"archivo");
}
function getAuthBlock() {
    const gpoAlias=ebyid("gpo_alias");
    const docChoice=ebyid("docChoice");
    const esContra=(docChoice && docChoice.value==="contra");
    const miAuthName=esContra?authCrName:authName;
    const miGpoAuth=esContra?gpoCrAuth:gpoAuth;
    iniFunc(2,"getAuthBlock", gpoAlias, miAuthName);
    const lines=[getParagraphObject("Solicita autorización a: ","lefted padt7 nomargini wid220pxi boldValue fontBig",true)]; // <!-- "+gpoAlias.value+" -->
    if (gpoAlias && gpoAlias.value.length>0) {
        const gpoId=gpoAlias.value;
        const authDef=miGpoAuth[gpoAlias.value]?miGpoAuth[gpoAlias.value]:[];
        if (authDef.length==0) logService("No hay autorizador para empresa con Id "+gpoAlias.value);
        const divObj={eName:"DIV",className:"wid220pxi marbtm5 centered",eChilds:[]};
        for (let key in miAuthName) {
            divObj.eChilds.push({eName:"DIV",className:"lefted relative", eChilds:[{eText:"• "+miAuthName[key]},{eName:"INPUT",type:"checkbox",id:"auth"+key,className:"authReqList abs_e",checked:authDef.includes(key)}]});
        }
        lines.push(ecrea(divObj));
        return lines;
    }
    return getParagraphObject("Aún no han sido determinados los autorizadores","lefted padt7 nomargini wid300i",true);
}
function preCancelInvoice() {
    iniFunc(2,"preCancelInvoice");
    const btnOff=ebyid("cancelInvoiceBtn");
    if (btnOff.value!=="Cancelar Factura") {
        endFunc(2,"preCancelInvoice off");
        return;
    }
    const invArc=ebyid("invArc");
    if (!invArc.invid) {
        overlayMessage(getParagraphObject("No se puede identificar una factura válida.", "errorLabel"),"Error");
        endFunc(2,"preCancelInvoice NO ID");
        return;
    }
    btnOff.motivo="";
    overlayConfirmation([getParagraphObject("Indique motivo de cancelación de factura:","padt20 nomargini boldValue",true),ecrea({eName:"INPUT",type:"text",id:"cancelReason",className:"wid400px",autofocus:true,oninput:function(event){btnOff.motivo=event.target.value;console.log('input:'+event.key+', motivo:'+btnOff.motivo,event);},onkeyup:function(e){if(e.key==='Enter'){const a=ebyid('accept_overlay');if(a)a.click();}}})],"Cancelar Factura", cancelInvoice);
    endFunc(2,"preCancelInvoice");
}
function cancelInvoice() {
    iniFunc(2,"cancelInvoice : "+ebyid("cancelInvoiceBtn").motivo);
    // ToDo: Cancelar Factura sin crear solicitud.
    //       Usar Proceso para guardar motivo, con modulo "Factura", status "Cancelada", detalle "Solicitud:"+motivo
    const btnOff=ebyid("cancelInvoiceBtn");
    if (btnOff.value!=="Cancelar Factura") {
        endFunc(2,"cancelInvoice off");
        return;
    }
    const invArc=ebyid("invArc");
    if (!invArc.invid) {
        overlayMessage(getParagraphObject("No se puede identificar una factura válida.", "errorLabel"),"Error");
        endFunc(2,"cancelInvoice NO ID");
        return;
    }
    btnOff.value="Cancelando Factura";
    const gpoAlias = ebyid("gpo_alias");
    const gpoIx= gpoAlias.selectedIndex;
    const gpoAl = gpoAlias.options[gpoIx].text;
    const gpoDt = ebyid("gpo_detail");
    const prvEl = ebyid("prv_codigo");
    const prvId = prvEl.value;
    const prvIx = prvEl.selectedIndex;
    const prCod = prvEl.options[prvIx].text;
    const prvDt = ebyid("prv_detail");
    const fecha = ebyid("fecha");
    const folio = ebyid("folio");
    let parameters={action:"cancelInvInReq",invId:invArc.invid,motivo:btnOff.motivo,fecha:fecha.value,gpoId:gpoAlias.value,alias:gpoAl,gpoRS:gpoDt.value,prvId:prvEl.value,prCod:prCod,prvRS:prvDt.value,folio:folio.value};
    postService("consultas/Facturas.php", parameters, function(msg,pars,state,status) {
        if (state==4&&status==200) {
            iniFunc(2, "cancelInvoice:response");
            try {
                const jobj=JSON.parse(msg);
                overlayMessage(getParagraphObject(jobj.message?jobj.message:jobj.mensaje),jobj.action?jobj.action.toUpperCase():jobj.response.toUpperCase());
                //console.log("RESPONSE OK:\n",jobj);
            } catch (ex) {
                overlayMessage(getParagraphObject(ex.message, "errorLabel"),"ERROR");
                console.log("RESPONSE EX:\n"+msg+"\n");
            }
            btnOff.value="Cancelar Factura";
            endFunc(2,"cancelInvoice:reponse");
        } else if (state>4 || status!=200) {
            errFunc("cancelInvoice:response","STATE:"+state+", STATUS:"+status+". MSG:"+msg);
            console.log("RESPONSE STT:\n"+msg+"\n");
        } else console.log("STATE:"+state+", STATUS:"+status);
    });
    endFunc(2,"cancelInvoice");
}
function preSubmitAuthorization() {
    iniFunc(2,"preSubmitAuthorization");
    const btnObj=ebyid("btnObj");
<?php
if (!$test/*$auCrLen==1*/) {
?>
    const docChoice=ebyid("docChoice");
    const esContra=(docChoice && docChoice.value==="contra");
    if (esContra) {
        endFunc(2,"preSubmitAuthorization contra");
        submitAuthorization();
        return;
    }
<?php
}
?>
    if (btnObj.value!=="Solicitar Autorización") {
        endFunc(2,"preSubmitAuthorization off");
        return;
    }
    btnObj.value="Procesando...";
    const artTbd=ebyid("artTbd");
    let hasMissedACode=false;
    fee(lbycn("codigoArticulo"),function(elm,idx){
        if(elm.value.trim().length==0) hasMissedACode=true;
        if (elm.idt)
            artTbd.conceptos[idx]["@cptId"] = elm.idt;
        else if (elm.hasAttribute("idt"))
            artTbd.conceptos[idx]["@cptId"] = elm.getAttribute("idt");
        artTbd.conceptos[idx]["@codigo"]=elm.value.trim();
    });
    if (hasMissedACode) {
        overlayMessage(getParagraphObject("Debe capturar el código de todos los artículos", "errorLabel"),"Error");
        btnObj.value="Solicitar Autorización";
        endFunc(2,"preSubmitAuthorization missCode");
        return;
    }
    if (btnObj.confirma && btnObj.confirma.length>0) {
        overlayConfirmation([getParagraphObject("Confirme responsabilidad en SOLICITUD DE PAGO aunque "+btnObj.confirma+".","padt20 nomargini boldValue",true),ecrea({eName:"HR", className:"marginH5"}), getAuthBlock()], "Confirmación de Riesgo", submitAuthorization);
        btnObj.value="Solicitar Autorización";
    } else {
        overlayConfirmation(getAuthBlock(), "Confirmar Autorizador", submitAuthorization);
        btnObj.value="Solicitar Autorización";
    }
    endFunc(2,"preSubmitAuthorization");
}
function submitAuthorization() {
    iniFunc(2,"submitAuthorization");
    const btnObj=ebyid("btnObj");
    if (btnObj.value!=="Solicitar Autorización") {
        endFunc(2,"submitAuthorization");
        return;
    }
    btnObj.value="Procesando...";
    let url="consultas/Facturas.php";
    let parameters = {action:"requestPaymentAuthorization"};
    parameters.inidate=ebyid("fecha").value;
    parameters.paydate=ebyid("fechapago").value;
    parameters.gpoId=ebyid("gpo_alias").value;
    parameters.prvId=ebyid("prv_codigo").value;
    overlayWheel();
<?php // ToDo_SOLICITUD: Anexar pedido a solicitud
?>
    const pedido=ebyid("pedido");
    if (pedido && (!pedido.dbval || pedido.dbval!==pedido.value))
        parameters.pedido=pedido.value.trim();
    const remision=ebyid("remision");
    if (remision && (!remision.dbval || remision.dbval!==remision.value))
        parameters.remision=remision.value.trim();
    parameters.authList=[];
    fee(lbycn("authReqList"),el=>{
        if (el.checked) parameters.authList.push(el.id.slice(4));
    });
    const invChkbox=ebyid("invChkbox");
    const docChoice=ebyid("docChoice");
    const esFactura=((invChkbox && invChkbox.checked)||(docChoice && docChoice.value==="factura"));
    const esOrden=((invChkbox && !invChkbox.checked)||(docChoice && docChoice.value==="orden"));
    if ((esFactura||<?=$test?"(":""?>esOrden<?=$test?"&&ebyid(\"ordRef\").value.slice(0,6).toLowerCase()!==\"prueba\")":""?>)&&parameters.authList.length==0) {
        overlayMessage(getParagraphObject("Debe seleccionar al menos un autorizador", "errorLabel"),"Error");
        btnObj.value="Solicitar Autorización";
        endFunc(2,"submitAuthorization");
        return;
    }
    const obselem=ebyid("observaciones");
    if (obselem && obselem.value.length>0) {
        parameters.observaciones=obselem.getAttribute("realValue");
    }
    if (esFactura) {
        const invArc=ebyid("invArc");
        if (!invArc.invid) {
            overlayMessage(getParagraphObject("No se puede identificar una factura válida.", "errorLabel"),"Error");
            btnObj.value="Solicitar Autorización";
            endFunc(2,"submitAuthorization");
            return;
        }
        parameters.invId=invArc.invid;
        const artTbd=ebyid("artTbd");
        if (artTbd.conceptos) {
            parameters.conceptos=JSON.parse(JSON.stringify(artTbd.conceptos).replace(/[@]/g, ""));
            /*
            parameters.conceptos = artTbd.conceptos.map(function(obj) {
                // return JSON.stringify(obj);
                return Object.keys(obj).sort().map(function(key) {
                    let ky=key;
                    if (ky.slice(0,1)==="@") ky=ky.slice(1);
                    if (ky==="Impuestos") return ky+"=''"; // toDo: Extraer suma de impuestos
                    let vl=obj[key];
                    if (vl && vl.replace) vl=vl.replace(/["']/g, "-").replace(/,/g, '.');
                    //console.log("PARAM CONC: "+ky+"='"+vl+"'"); // ToDo: Que hacer si val es objeto
                    return ky+"='"+vl+"'";
                    // return obj[key];
                });
            });
            //console.log("PARAMETERS Map-Sort-Map = ",parameters.conceptos);
            */
        }
        if (artTbd.subtotal)
            parameters.subtotal=artTbd.subtotal;
        if (artTbd.descuento)
            parameters.descuento=artTbd.descuento;
        if (artTbd.isr)
            parameters.isr=artTbd.isr;
        if (artTbd.iva)
            parameters.iva=artTbd.iva;
        if (artTbd.total)
            parameters.total=artTbd.total;
        parameters = toPostParameters(parameters);
    } else if (esOrden) {
        const ordRef = ebyid("ordRef");
        if (ordRef.value.length==0) {
            overlayMessage(getParagraphObject("Debe indicar una orden de compra", "errorLabel"),"Error");
            btnObj.value="Solicitar Autorización";
            endFunc(2,"submitAuthorization");
            return;
        }
        parameters.ordRef=ordRef.value;
        const ordFil = ebyid("ordFile");
        if (ordFil.files.length==0) {
            overlayMessage(getParagraphObject("Debe anexar su orden de compra escaneada como PDF", "errorLabel"),"Error");
            btnObj.value="Solicitar Autorización";
            endFunc(2,"submitAuthorization");
            return;
        }
        parameters.ordFile=ordFil.files[0];
        const impFld = ebyid("importe");
        if (impFld.value.length==0) {
            overlayMessage(getParagraphObject("Debe indicar el importe", "errorLabel"),"Error");
            btnObj.value="Solicitar Autorización";
            endFunc(2,"submitAuthorization");
            return;
        }
        parameters.amount=impFld.value;
        const monFld = ebyid("ord_moneda");
        if (monFld) {
            let monVal=monFld.value;
            if (monVal.length==0) monVal="MXN";
            parameters.moneda=monVal;
        }
    } else { // esContra
        /* toDo : ANEXAR TODOS LOS ID DE CONTRA RECIBOS
             X> CREAR TODAS LAS SOLICITUDES LIGADAS A CADA CONTRA RECIBO, DIRECTO PARA PAGO, NO REQUIERE AUTORIZACION
             N> SI REQUIERE AUTORIZACION (AUTORIZACION DE PRONTO PAGO. CREAR PERMISO/EMPRESA PARA ASIGNAR EN CADA UNA QUIEN PUEDE AUTORIZAR PRONTO PAGO) 
             N> VALIDAR CONTRA RECIBOS CON MISMA EMPRESA Y PROVEEDOR, ESOS CONTRA RECIBOS SE VAN A INTEGRAR EN UNO SOLO Y SE LES GENERARÁ UNA SOLA SOLICITUD
             X> ASIGNAR AUTORIZADOR QUIEN HAYA AUTORIZADO EL CONTRA RECIBO
             N> SI SOLO HAY UN AUTORIZADOR DE PRONTO PAGO NO HACE FALTA ASIGNAR AUTORIZADOR. NO SERÁ EL MISMO QUE EL AUTORIZADOR DEL CONTRA RECIBO
             => AL MOSTRAR VISUALIZAR TODAS LAS FACTURAS
             => INCLUIR EN LISTA DE SOLICITUDES PARA PAGO
             => AL SER MARCADAS COMO PAGADAS HAY QUE CAMBIAR STATUS DE TODAS LAS FACTURAS RELACIONADAS AUTORIZADAS A PAGADAS
        */
        parameters.counterList={};
        const capContra=ebyid("capContra");
        fee(capContra.children,btnContra=>{
            if (btnContra.data) {
                if (Array.isArray(btnContra.data)) console.log("IGNORE "+btnContra.id);
                else parameters.counterList[btnContra.data.contra.id]={obs:btnContra.obs};
            } else console.log("NO DATA "+btnContra.id);
        });
        delete parameters.gpoId; delete parameters.prvId; delete parameters.pedido; delete parameters.remision; delete parameters.authList; delete parameters.observaciones;
        url="consultas/SolPago.php";
        parameters.action="requestPaymentDirect";
        console.log("PARAMETERS FOR AUTHORIZATION: ", JSON.stringify(parameters,jsonCircularReplacer()));
    }

    btnObj.value="Esperando Respuesta";
    //console.log("PARAMETERS: ",parameters);
    //parameters = toPostParameters(parameters,"",["ordFile"]);
    //console.log("FIXPARAMETERS: ",parameters);
    logService("[SOLPAGO.REQAUTH]", {filebase:"logs", json:true, url:url, params:parameters});
    readyService(url,parameters,(j,x)=>{
        iniFunc(2,"READY RESPONSE",JSON.stringify(j,jsonCircularReplacer()));
        let msgTitle="AVISO";
        if (j.result==="success") msgTitle="EXITO";
        else if (j.result==="error") msgTitle="ERROR";
        if (j.message) {
            const clrDiv=ecrea({eName:"DIV"});
            clrDiv.innerHTML=j.message;
            overlayMessage(getParagraphObject(clrDiv.textContent, j.result==="error"?"errorLabel":""),msgTitle);
            if (j.result==="success") ebyid("overlay").callOnClose=function() { location.reload(true); }
        }
        // if (j.mail) console.log("RESPUESTA CORREO: "+j.mail); // No hace falta, ya se generó doclog de error y/o mail
        btnObj.value="Solicitar Autorización";
        endFunc(2,"READY RESPONSE");
    },(e,t,x)=>{
        iniFunc(2,"ERROR RESPONSE");
        if (t.slice(0,16)!=="<!-- REFRESH -->") {
            overlayMessage(getParagraphObject(e, "errorLabel"),"ERROR");
        }
        console.log("MSG: "+t+"\nEXTRA: ",JSON.stringify(x,jsonCircularReplacer()));
        btnObj.value="Solicitar Autorización";
        endFunc(2,"ERROR RESPONSE");
    });
    endFunc(2,"submitAuthorization");
}
function reMap(type) {
    //iniFunc(2,"function reload "+type);
    autoSubmit({menu_accion:"SolicitaPago",reMap:type,target:"_self"});
}
function doLoaded() {
    //iniFunc(2,"function doLoaded");
    //show(["sol_obs_row","obsval"]);
    const pedido=ebyid("pedido");
    if (pedido) pedido.onblur=function(evt) {
        const tgt=evt.target;
        if (tgt.value.trim().length==0)
            tgt.value="S/PEDIDO";
    };
    const remision=ebyid("remision");
    if (remision) remision.onblur=function(evt) {
        const tgt=evt.target;
        if (tgt.value.trim().length==0)
            tgt.value="S/REMISION";
    };
}
function clearAllTimeouts() {
    var id=window.setTimeout(function(){},0);
    for(let i=id;i>=0;i--) window.clearTimeout(i); // will do nothing if no timeout with id is present
}
function obsSeek() {
    const obs=ebyid("observaciones");
    const txt=obs.value;
    const len=txt.length;
    const oml=ebyid("obs_max_len");
    if (len>0 && len<200)
        oml.innerHTML=(txt.length)+"/200 caracteres";
    else oml.innerHTML="200 caracteres";
    const enc=txt.replace(/[\u00A0-\u9999<>\&]/g, function(i) {
        return '&#'+i.charCodeAt(0)+';';});
    obs.setAttribute("realValue",enc);
    obs.setAttribute("realLength",enc.length);
    const docChoice=ebyid("docChoice");
    if (docChoice && docChoice.value==="contra") {
        const capContra=ebyid("capContra");
        if (capContra.lastViewed) capContra.lastViewed.obs=txt;
    }
}
console.log("SCRIPT solpago READY!");
<?php
//clog1seq(-1);
//clog2end("scripts.solpago");
