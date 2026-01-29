<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.registro");
clog1seq(1);
$esDesarrollo = in_array(getUser()->nombre, ["admin"]);
$esAdmin=validaPerfil("Administrador");
$esSistemas=$esAdmin||validaPerfil("Sistemas");
$esCuentas=$esSistemas||validaPerfil("Cuentas Bancarias");
$contaProv = $esSistemas||validaPerfil("ContaProv");
$consultaProv = $esSistemas||consultaValida("Proveedor");
$modificaProv = $esSistemas||modificacionValida("Proveedor");
$bloqueaProv = $esSistemas||validaPerfil("BloquearPrv");
$validaBanco = $esSistemas||validaPerfil("Valida Bancarias");
$validaOpinion = $esSistemas||validaPerfil("Valida Opinion");
$consultaMasiva = $esSistemas||validaPerfil("Consulta Masiva Prv");
$consultaObjImp = $esSistemas||consultaValida("Objeto Impuesto");
$modificaObjImp = $esSistemas||modificacionValida("Objeto Impuesto");
$consultaCPyImp = $esSistemas||consultaValida("CPago Impuestos");
$modificaCPyImp = $esSistemas||modificacionValida("CPago Impuestos");
$consultaCCPSDf = $esSistemas||consultaValida("ConceptoSindefinir");
$modificaCCPSDf = $esSistemas||modificacionValida("ConceptoSindefinir");
$consultaTiposProveedor = $esSistemas||validaPerfil(["TiposProveedor","ComercioExterior Monitor","ComercioExterior Control","ComercioExterior Admin","Direccion"]);
?>
var browser = "<?= getBrowser() ?>";
<?php 
/*function setFocusOn(elemId) {
    var elem = ebyid(elemId);
    if (elem) {
        elem.focus();
        elem.addEventListener("blur", storeElement, false);
        elem.addEventListener("keyup", canStoreElement, false);
    }
}*/
if ($esDesarrollo) echo "doShowLogs=true;\n"; ?>
function checkEnter(evt) {
    if (evt && evt.type=="keyup") {
        if (evt.keyCode == '13') return true; // ENTER
    }
    return false;
}
function checkEscape(evt) {
    if (evt && evt.type=="keyup") {
        if (evt.keyCode == '27') return true; // ESCAPE
    }
    return false;
}
function checkFile(elem) {
    if (!elem) return;
    let fileData = elem.files[0];
    let size = +fileData.size;
    let type = fileData.type;
    let errmsg = false;
    let textName=false;
    if (elem.hasAttribute("textname")) textName=elem.getAttribute("textname");
    if (!textName||textName.length==0) textName="El documento";
    if (type!=="application/pdf") {
        errmsg = "<p>"+textName+" debe proporcionarse en archivo PDF</p>";
    }
    if (size>2097000) {
        if (!errmsg) errmsg="";
        errmsg+="<p>El archivo excede el tamaño máximo permitido de 2MB</p>";
    }
    if (errmsg) {
        overlayMessage(errmsg,"Error");
        var newElem = document.createElement("input");
        newElem.type = "file";
        newElem.id = elem.id;
        newElem.name = elem.name;
        newElem.size = "35";
        newElem.setAttribute("textName",textName);
        newElem.onchange = function() { checkFile(newElem); };
        elem.parentNode.replaceChild(newElem, elem);
    }
}
function getOrMakeElementAt(parentElem, tagname, attributes) {
    var element = false;
    if(attributes.id) element = ebyid(attributes.id);
    if(!element) {
        element = document.createElement(tagname);
        for (var key in attributes)
            element[key] = attributes[key];
        parentElem.appendChild(element);
    }
    return element;
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
function getParentTable(elem) {
    if (!elem) return false;
    var parentNode = elem.parentNode;
    if (parentNode && parentNode.tagName.toLowerCase() === 'table') return parentNode;
    return getParentTable(parentNode);
}
function maxNEV(e,v) { // Numeric Element Value
    if(+e.value>v) e.value=v;
    else           e.value=+e.value;
}
function fixedUpper(c) {
    let len=c.value.length;
    if (len>0 && c.value.match(/[a-záéíóúü]/)) c.value=c.value.toUpperCase();
}
function doFocusOn(elem) {
    if (typeof elem==="string") elem=ebyid(elem);
    if (elem) elem.focus();
}
function submitting(event) {
    let submitForm=ebyid("forma_reg_prv");
    console.log("INI submitting "+submitForm.submitValue);
    <?php /*if ($esDesarrollo) echo "if(true){const f=new FormData(event.target);let o={};for(let e of f.entries()){o[e[0]]=e[1];}o[event.submitter.name]=event.submitter.value;console.dir(o);return eventCancel(event);}";*/ ?>
    if (submitForm.submitValue==="browse") return true;
    let provCode=ebyid("prov_code");
    if (provCode.value.length==0) {
        overlayMessage("<p>Es necesario indicar el <b>C&oacute;digo</b> de proveedor.</p>","Error");
        ebyid("overlay").callOnClose=function() { doFocusOn("prov_code"); };
        return eventCancel(event);
    }
    if (provCode.value.length<5) {
        overlayMessage("<p>El <b>C&oacute;digo</b> de proveedor no está completo.</p>")
        ebyid("overlay").callOnClose=function() { doFocusOn("prov_code"); };
        return eventCancel(event);
    }
    let provField=ebyid("prov_field");
    if (provField.value.length==0) {
        overlayMessage("<p>Debe capturar la <b>Raz&oacute;n Social</b>.</p>","Error");
        ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_field"); };
        return eventCancel(event);
    }
    let provRFC=ebyid("prov_rfc");
    if (provRFC.value.length==0) {
        overlayMessage("<p>Se requiere que especifique su <b>RFC</b>.</p>","Error");
        ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_rfc"); };
        return eventCancel(event);
    }
    /* TODO: AJAX para obtener si el proveedor está marcado en el listado69b.
             No urge pues esto ya se hace en servidor (configuracion) */
    let provStatus=ebyid("prov_status");
    let userEmail=ebyid("user_email");
    if (userEmail.value.length==0) {
        if (provStatus.value.length==0) provStatus.value="actualizar";
        else if (provStatus.value==="activo") {
            overlayMessage("<p>El <b>Correo Electr&oacute;nico</b> es requisito para env&iacute;o de notificaciones. En su defecto cambie status a <b>ACTUALIZAR</b></p>","Error");
            ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("user_email"); };
            return eventCancel(event);
        }
    }
    let provBank=ebyid("prov_bank");
    if (provBank.value.length==0) {
        if (provStatus.value.length==0) provStatus.value="actualizar";
        else if (provStatus.value==="activo") {
            overlayMessage("<p>Debe indicar el nombre del <b>Banco</b> relacionado con el pago. En su defecto cambie status a <b>ACTUALIZAR</b></p>","Error");
            ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_bank"); };
            return eventCancel(event);
        }
    }
    let provBankRFC=ebyid("prov_bankrfc");
    if (provBankRFC.value.length==0) {
        if (provStatus.value.length==0) provStatus.value="actualizar";
        else if (provStatus.value==="activo") {
            overlayMessage("<p>Debe indicar el <b>RFC del Banco</b> relacionado con el pago. En su defecto cambie status a <b>ACTUALIZAR</b></p>","Error");
            ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_bankrfc"); };
            return eventCancel(event);
        }
    }
    let provAccount=ebyid("prov_account");
    if (provAccount.value.length==0) {
        if (provStatus.value.length==0) provStatus.value="actualizar";
        else if (provStatus.value==="activo") {
            overlayMessage("<p>Debe indicar la <b>Cuenta Bancaria</b> del proveedor. En su defecto cambie status a <b>ACTUALIZAR</b></p>","Error");
            ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_account"); };
            return eventCancel(event);
        }
    }
    let provReceipt=ebyid("prov_receipt");
    if (!provReceipt || provReceipt.value.length==0) {
        let provReceiptElem=ebyid("prov_receipt_name");
        if (!provReceiptElem || provReceiptElem.value.length==0) {
            if (provStatus.value.length==0) provStatus.value="actualizar";
            else if (provStatus.value==="activo") {
                overlayMessage("<p>Debe incluir un documento pdf con la car&aacute;tula de un <b>Estado de cuenta</b> escaneado. En su defecto cambie status a <b>ACTUALIZAR</b></p>","Error");
                ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_receipt"); };
                return eventCancel(event);
            }
        }
    }
    let provOpinion=ebyid("prov_opinion");
    if (!provOpinion || provOpinion.value.length==0) {
        let provOpinionElem=ebyid("prov_opinion_name");
        if (!provOpinionElem || provOpinionElem.value.length==0) {
            if (provStatus.value.length==0) provStatus.value="actualizar";
            else if (provStatus.value==="activo") {
                overlayMessage("<p>Debe incluir un documento pdf de la <b>Opini&oacute;n de Cumplimiento</b>. En su defecto cambie status a <b>ACTUALIZAR</b></p>","Error");
                ebyid("overlay").callOnClose=function() { /*console.log("INI afunc callOnClose");*/ doFocusOn("prov_opinion"); };
                return eventCancel(event);
            }
        }
    }
    return true;
}
function prvCodFix(c) {
    let cvl=c.value;
    console.log("INI prvCodFix CVL (1) = "+cvl);
    let len=cvl.length;
    let refElem = ebyid("refreshImage");
    if (len==0) {
        clearProv();
        if (refElem) refElem.classList.add("hidden");
        return;
    }
    if ((len==5||len==7)&&cvl.match(/[A-Z]-[0-9]{3}(?:-[0-9])?/)) {
        populateProv(cvl);
        return;
    }
    let code=cvl.slice(0,1);
    if (code.match(/[A-Z]/)) { cvl=cvl.slice(1); }
    else if (code.match(/[a-z]/)) { code=code.toUpperCase(); cvl=cvl.slice(1); }
    else code="";
    //console.log("CVL (2) = "+cvl);
    if(cvl.length>0) {
        cvl=cvl.replace(/[^\d-]/g,"");
        //console.log("CVL (3) = "+cvl);
    }
    if(cvl.length>0) {
        if (cvl.slice(0,1)==="-") {
            cvl=cvl.slice(1);
            //console.log("CVL (4) = "+cvl);
        }
        code+="-";
        let num="";
        while (cvl.length>0 && num.length<3) {
            let n=cvl.slice(0,1);
            if (n.match(/[\d]/)) num+=n;
            cvl=cvl.slice(1);
            //console.log("CVL (5) = "+cvl);
        }
        if (num.length>0) code+=num;
        if (cvl.length>0) {
            cvl=cvl.replace(/\D/g,"").slice(0,1);
            //console.log("CVL (6) = "+cvl);
            if (cvl.length>0 && cvl!=="0") {
                code+="-"+cvl;
            }
        }
    }
    c.value=code;
    len=code.length;
    if ((len==5||len==7)&&code.match(/[A-Z]-[0-9]{3}(?:-[0-9])?/)) {
        populateProv(code);
    } else {
        clearProv();
        if (refElem) refElem.classList.add("hidden");
    }
}
var prvCache={};
<?php 
if ($esDesarrollo) { ?>
function prvIdChng(id, force=false) {
    if (!force && prvCache[id] && prvCache[prvCache[id]]) fillData(prvCache[id]);
    else {
        postService("consultas/Proveedores.php", {accion:'regdata',nacional:1,id:id}, function(text,params,state,status) {
            if (state<4&&status<=200) return;
            console.log("RESPONSE prvIdChng|regdata"+state+"/"+status);
            if (state>4||status>200) {
                console.log("RESP: ERROR DE CONEXION. STATE="+state+", STATUS="+status);
                clearProv();
            }
            if(text&&text.length>0) {
                try {
                    let respObj = JSON.parse(text);
                    if (respObj.error) {
                        console.log("Resp Error",respObj);
                        if (respObj.errno && respObj.errno==1 && prvCache[params.id]) {
                            if (prvCache[prvCache[params.id]]) delete prvCache[prvCache[params.id]];
                            delete prvCache[params.id];
                        }
                        if (respObj.errno && respObj.errno==1) clearProv(true);
                        else clearProv();
                        if (respObj.errno && respObj.errno>2) {
                            console.log(respObj);
                            overlayMessage(respObj.error,"Error");
                            ebyid("prov_code").value="";
                            ebyid("overlay").callOnClose=function() { doFocusOn("prov_code"); };
                        }
                    } else if (respObj.code) {
                        console.log("REGDATA RESULT: "+text);
                        prvCache[params.id]=respObj.code;
                        prvCache[respObj.code]=respObj;
                        fillData(respObj.code);
                    }
                } catch(ex) {
                    console.log("RESP EXCEPTION");
                    console.log("TEXT1: ",text);
                    console.log("ERR1: ",ex);
                    clearProv();
                    overlayMessage("Ocurri&oacute; un error al mostrar datos del proveedor\n<!-- "+ex+" -->\n"+text,"Error");
                }
            } else {
                console.log("RESP EMPTY");
                clearProv();
            }
        }, function(errmsg, parameters, evt) {
            console.log("RESP ERROR: '"+errmsg+"', STATE="+parameters.xmlHttpPost.readyState+", STATUS="+parameters.xmlHttpPost.status);
            clearProv();
        });
    }
}
<?php 
} ?>
function fixRef() {
    const rr0=ebyid("refRow");
    const rr1=ebyid("refRow1");
    const rr2=ebyid("refRow2");
    if (clhas(rr0,"hidden")) {
        if (!clhas(rr1,"hidden")) cladd(rr1,["wasVis","hidden"]);
        if (!clhas(rr2,"hidden")) cladd(rr2,["wasVis","hidden"]);
    } else {
        if (clhas(rr1,"wasVis")) clrem(rr1,["wasVis","hidden"]);
        if (clhas(rr2,"wasVis")) clrem(rr2,["wasVis","hidden"]);
    }
}
function checkRef(event) {
    console.log("INI checkRef "+event.target.id+" "+(event.target.checked?"ON":"OFF"));
    let ref=null;
    switch(event.target.id) {
        case "doRef1": ref=ebyid("referencia1"); break;
        case "doRef2": ref=ebyid("referencia2"); break;
    }
    if (ref) {
        clset(ref.parentNode.parentNode,"hidden",!event.target.checked);
        if (event.target.checked) {
            ref.value=ref.defaultValue;
        } else {
            ref.defaultValue=ref.value;
            ref.value="";
        }
    }
}
<?= ""/*isset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"])?json_encode($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"]):"{}"*/ ?>
function populateProv(code,force) {
    let refElem = ebyid("refreshImage");
    var len = code.length;
    if  (len==5 || len==7) { // Mientras no existan 10 zonas con un mismo rfc (actualmente solo hay un codigo de 7 caracteres)
        if (force || !prvCache[code]) {
            postService("consultas/Proveedores.php", {accion:'regdata',nacional:1,code:code}, function(text,params,state,status) {
                if (state<4&&status<=200) return;
                console.log("RESPONSE populateProv|regdata"+state+"/"+status);
                if (state>4||status>200) {
                    console.log("RESP: ERROR DE CONEXION. STATE="+state+", STATUS="+status);
                    clearProv();
                    return;
                }
                if(text&&text.length>0) {
                    try {
                        let respObj = JSON.parse(text);
                        if (respObj.error) {
                            console.log("RESP ERROR"+(respObj.errno?" "+respObj.errno:"")+": "+respObj.error);
                            //if (respObj.log) console.log("LOG: "+respObj.log);
                            if (respObj.errno && respObj.errno==1 && prvCache[params.code]) { // Borrar porque el registro no existe en la base de datos
                                delete prvCache[params.code];
                            }
                            if (respObj.errno && respObj.errno==1) clearProv(true);
                            else clearProv();
                            if (respObj.errno && respObj.errno>2) {
                                console.log(respObj);
                                overlayMessage(respObj.error,"Error");
                                ebyid("prov_code").value="";
                                ebyid("overlay").callOnClose=function() { doFocusOn("prov_code"); };
                            }
                        } else {
                        	console.log("REGDATA RESULT: "+text);
                            prvCache[params.code]=respObj;
                            fillData(params.code);
                        }
                    } catch(ex) {
                        console.log("TEXT2: ",text);
                        console.log("ERR2: ",ex);
                        clearProv();
                        overlayMessage("Ocurri&oacute; un error al mostrar datos del proveedor\n<!-- "+ex+" -->\n"+text,"Error");
                    }
                } else {
                    clearProv();
                    // Error en comunicación al servidor pues siempre debe existir información
                }
            }, function(errmsg, parameters, evt) {
                clearProv();
                console.log("ERROR EN RESPUESTA: '"+errmsg+"', STATE="+parameters.xmlHttpPost.readyState+", STATUS="+parameters.xmlHttpPost.status);
            });
        } else {
            fillData(code);
        }
        if (refElem) refElem.classList.remove("hidden");
    } else {
        clearProv();
        if (refElem) refElem.classList.add("hidden");
    }
}
function refreshCode() {
    let codElem = ebyid("prov_code");
    populateProv(codElem.value,true);
}
function viewImageSample(title) {
    //console.log("INI function viewImageSample");
    overlayMessage("<br><img src='imagenes/caratulaEdoCta.jpg' width='600' height='300'><br>",title);
}
function fillData(code) {
    let data=prvCache[code];
    console.log("INI func fillData "+code,data);
    if (data["error"]) {
        delete prvCache[code];
        clearProv();
        return;
    }
    let numDisplayed=clrem(lbycn("beginHidden"),"hidden");
    //console.log("Showing non-browsable elements: "+numDisplayed);
    let idpElem = ebyid("prov_id");
    let rzsElem = ebyid("prov_field");
    let rfcElem = ebyid("prov_rfc");
    let emlElem = ebyid("user_email");
    let iduElem = ebyid("user_id");
    let crdElem = ebyid("prov_credit");
    let pymElem = ebyid("prov_paym");
    let bnkElem = ebyid("prov_bank");
    let brfElem = ebyid("prov_bankrfc");
    let accElem = ebyid("prov_account");
    let rf1ChkE = ebyid("doRef1");
    let rf1RowE = ebyid("refRow1");
    let rf1Elem = ebyid("referencia1");
    let rf2ChkE = ebyid("doRef2");
    let rf2RowE = ebyid("refRow2");
    let rf2Elem = ebyid("referencia2");
    let ecrElem = ebyid("prov_receipt");
    let opiElem = ebyid("prov_opinion");
    let zonElem = ebyid("prov_zone");
    let sttElem = ebyid("prov_status");

<?php if ($esAdmin) {?>
    cladd(ebyid("itchdot"),"bglightgray1");
<?php } ?>

    const prvId=String(data["id"]).trim();
    prvCache[prvId]=code;
    idpElem.value=prvId;
    rzsElem.value=String(data["razonSocial"]).trim();
    rfcElem.value=String(data["rfc"]).trim();
    if (data["email"]) emlElem.value=String(data["email"]).trim();
    else emlElem.value="";
    if (data["userId"]) iduElem.value=String(data["userId"]).trim();
    else iduElem.value="";
    crdElem.value=String(data["credito"]).trim();
    pymElem.value=String(data["codigoFormaPago"]).trim();
    bnkElem.value=String(data["banco"]).trim();
    brfElem.value=String(data["rfcbanco"]).trim();
    accElem.value=String(data["cuenta"]).trim();
    rf1Elem.value=String(data["referencia1"]).trim();
    rf2Elem.value=String(data["referencia2"]).trim();

    doCheck(ebyid("conCodgEnDesc"),ebyid("conCEDImg"),String(data["conCodgEnDesc"]).trim()==="1");

    doCheck(ebyid("esServicio"),ebyid("esSrvImg"),String(data["esServicio"]).trim()==="1");

<?php 
if ($consultaObjImp) { ?>
    doCheck(ebyid("reqObjImp"),ebyid("objImpImg"),String(data["reqObjImp"]).trim()==="1");
<?php 
} ?>

<?php 
if ($consultaCPyImp) { ?>
    doCheck(ebyid("reqPayTaxChk"),ebyid("pyTxChkImg"),String(data["reqPayTaxChk"]).trim()==="1");
<?php 
} ?>

<?php 
if ($consultaCCPSDf) { ?>
    doCheck(ebyid("reqDefCvPrdSrv"),ebyid("dfCvPSImg"),String(data["reqDefCvPrdSrv"]).trim()==="1");
<?php 
}
require_once "clases/Proveedores.php";
?>
    const tipo=String(data["tipo"]);
    doCheck(ebyid("esTComer"),ebyid("esTComImg"),tipo & <?= Proveedores::TIPO_COMERCIAL ?>);
    doCheck(ebyid("esTAduan"),ebyid("esTAdnImg"),tipo & <?= Proveedores::TIPO_ADUANA ?>);
    doCheck(ebyid("esTTrasl"),ebyid("esTTraImg"),tipo & <?= Proveedores::TIPO_FLETE ?>);
    doCheck(ebyid("esTLogis"),ebyid("esTLogImg"),tipo & <?= Proveedores::TIPO_LOGISTICA ?>);

    if (rf1Elem.value.length>0) {
        rf1ChkE.checked=true;
        clrem(rf1RowE,["hidden","wasVis"]);
    } else {
        rf1ChkE.checked=false;
        clrem(rf1RowE,"wasVis");
        cladd(rf1RowE,"hidden");
    }
    if (rf2Elem.value.length>0) {
        rf2ChkE.checked=true;
        clrem(rf2RowE,["hidden","wasVis"]);
    } else {
        rf2ChkE.checked=false;
        clrem(rf2RowE,"wasVis");
        cladd(rf2RowE,"hidden");
    }
<?php 
if ($modificaProv||$validaBanco||$validaOpinion||$bloqueaProv) { ?>
    clrem(ebyid("prov_submit"),"hidden");
<?php 
}
if ($modificaProv||$validaBanco||$validaOpinion) { ?>
    clrem(ebyid("prov_reset"),"hidden");
<?php 
} ?>
    while(ecrElem.nextSibling) ecrElem.parentNode.removeChild(ecrElem.nextSibling);
    if (ecrElem.value.length>0) {
        ecrElem.value="";
        if (!/safari/i.test(navigator.userAgent)) {
            ecrElem.type="";
            ecrElem.type="file";
        }
    }
    while(opiElem.nextSibling) opiElem.parentNode.removeChild(opiElem.nextSibling);
    if (opiElem.value.length>0) {
        opiElem.value="";
        if (!/safari/i.test(navigator.userAgent)) {
            opiElem.type="";
            opiElem.type="file";
        }
    }
    if(data["edocta"]) {
        let ehidElem = ecrea({eName:"INPUT", type:"hidden", id:"prov_receipt_name", name:"prov_receipt_name", value:data["edocta"]});
        let lnkElem = ecrea({eName:"A", href:"cuentas/docs/"+data["edocta"], target:"archivopdf", className:"vAlignCenter", eChilds:[{eName:"IMG", src:"imagenes/icons/pdf200.png", width:"20", height:"20", className:"vAlignCenter"}]});
        ecrElem.classList.add("hidden");
        let ecrPar = ecrElem.parentNode;
        ecrPar.appendChild(ehidElem);
        ecrPar.appendChild(lnkElem);
        let verifCap=false;
        switch(data["verificado"]) {
            case "-1": verifCap="RECHAZADO"; break;
            case "0": verifCap="PENDIENTE"; break;
            case "1": verifCap="ACEPTADO"; break;
        }
        <?php 
if($modificaProv) { ?>let imgDelElem = ecrea({eName:"IMG", src:"imagenes/icons/deleteIcon16.png",title:"Descartar Documento", onclick:function(evt){let tgt=evt.target;let dci=tgt.previousElementSibling;if(clhas(ecrElem,"hidden")){clrem(ecrElem,"hidden");tgt.src="imagenes/icons/backArrow.png";tgt.title="Restaurar Documento Anterior";cladd(dci,"hidden");}else{cladd(ecrElem,"hidden");tgt.src="imagenes/icons/deleteIcon16.png";tgt.title="Descartar Documento";clrem(dci,"hidden");}}, className:"vAlignCenter pointer marginV2 noprint"});
        ecrPar.appendChild(imgDelElem);
<?php
} ?>
        if (verifCap!==false) {
            //ecrPar.appendChild(ecrea({eName:"SPAN",className:"marginV7 vAlignCenter",eText:"VERIF:"}));
            <?php 
if($validaBanco) { ?>ecrPar.appendChild(ecrea({eName:"SELECT", id:"acc_verified", name:"acc_verified", className:"pad3 vAlignCenter noprintBorder", eChilds:[{eName:"OPTION",value:"0",text:"PENDIENTE"},{eName:"OPTION",value:"1",text:"ACEPTADO"},{eName:"OPTION",value:"-1",text:"RECHAZADO"}]})); ebyid("acc_verified").value=data["verificado"];
            <?php 
} else { ?>if(verifCap==="PENDIENTE")verifCap="EN REVISI\u00D3N";ecrPar.appendChild(ecrea({eName:"SPAN",className:"pad3 vAlignCenter",eChilds:[{eName:"INPUT",type:"hidden", id:"acc_verified", name:"acc_verified", value:data["verificado"]},{eText:verifCap}]}));
<?php
} ?>
        }
    } else {
        <?php if($modificaProv){ ?>ecrElem.classList.remove("hidden");
        <?php } else { ?>ecrElem.parentNode.appendChild(ecrea({eName:"IMG", src:"imagenes/icons/statusWrong.png", width:"20", height:"20"}));
<?php         } ?>
    }
    if(data["opinion"]) {
        let ohidElem = ecrea({eName:"INPUT", type:"hidden", id:"prov_opinion_name", name:"prov_opinion_name", value:data["opinion"]});
        let ocpElem = ecrea({eName:"A", href:"cuentas/docs/"+data["opinion"], target:"archivopdf", className:"vAlignCenter", eChilds:[{eName:"IMG", src:"imagenes/icons/pdf200.png", width:"20", height:"20", className:"vAlignCenter"}]});
        opiElem.classList.add("hidden");
        let opiPar = opiElem.parentNode;
        opiPar.appendChild(ohidElem);
        opiPar.appendChild(ocpElem);
        let opiCap=false;
        switch(data["cumplido"]) {
            case "-2": opiCap="RECHAZADO"; break;
            case "-1": opiCap="VENCIDO"; break;
            case "0": opiCap="PENDIENTE"; break;
            case "1": opiCap="ACEPTADO"; break;
        }
        <?php if ($modificaProv) { ?>let imgDelOlem = ecrea({eName:"IMG", src:"imagenes/icons/deleteIcon16.png", title:"Descartar Documento", onclick:function(evt){let tgt=evt.target;let dci=tgt.previousElementSibling;if(clhas(opiElem,"hidden")){clrem(opiElem,"hidden");tgt.src="imagenes/icons/backArrow.png";tgt.title="Restaurar Documento Anterior";cladd(dci,"hidden");}else{cladd(opiElem,"hidden");tgt.src="imagenes/icons/deleteIcon16.png";tgt.title="Descartar Documento";clrem(dci,"hidden");}}, className:"vAlignCenter pointer marginV2 noprint"});
        opiPar.appendChild(imgDelOlem);
<?php         } ?>
        if (opiCap!==false) {
            //opiPar.appendChild(ecrea({eName:"SPAN",className:"marginV7 vAlignCenter",eText:"VERIF:"}));
            <?php if($validaOpinion){ ?>opiPar.appendChild(ecrea({eName:"SELECT", id:"opinion_fulfilled", name:"opinion_fulfilled", className:"pad3 vAlignCenter noprintBorder", eChilds:[{eName:"OPTION",value:"-1",text:"VENCIDO"}, {eName:"OPTION",value:"0",text:"PENDIENTE"}, {eName:"OPTION",value:"1",text:"ACEPTADO"}, {eName:"OPTION",value:"-2",text:"RECHAZADO"}]})); ebyid("opinion_fulfilled").value=data["cumplido"];opiPar.appendChild(ecrea({eName:"BR"}));opiPar.appendChild(ecrea({eText:"Vigencia:"}));opiPar.appendChild(ecrea({eName:"SPAN",className:"pad3 vAlignCenter fontSmall",eChilds:[{eName:"INPUT",type:"text",id:"opinion_created",name:"opinion_created",value:data["generaopinion"],className:"calendar",readOnly:true,onclick:function(){show_calendar_widget(ebyid('opinion_created'),'fixExpiredDate');}}]}));opiPar.appendChild(ecrea({eText:"- "}));opiPar.appendChild(ecrea({eName:"INPUT",type:"text",id:"opinion_expired",name:"opinion_expired",value:data["venceopinion"],className:"calendar",readOnly:true}));
            <?php }else{ ?>if(opiCap==="PENDIENTE")opiCap="EN REVISI\u00D3N";opiPar.appendChild(ecrea({eName:"SPAN",className:"pad3 vAlignCenter",eChilds:[{eName:"INPUT",type:"hidden", id:"opinion_fulfilled", name:"opinion_fulfilled", value:data["cumplido"]},{eText:opiCap}]}));opiPar.appendChild(ecrea({eName:"BR"}));opiPar.appendChild(ecrea({eText:"Vigencia:"}));opiPar.appendChild(ecrea({eName:"SPAN",className:"pad3 vAlignCenter fontSmall",eChilds:[{eName:"INPUT",type:"hidden",id:"opinion_created",name:"opinion_created",value:data["generaopinion"]},{eName:"INPUT",type:"hidden",id:"opinion_expired",name:"opinion_expired",value:data["venceopinion"]},{eText:"["+data["generaopinion"]+" - "+data["venceopinion"]+"]"}]}));
<?php             } ?>
        }
    } else {
        <?php if ($modificaProv) { ?>opiElem.classList.remove("hidden");
        <?php }else{ ?>opiElem.parentNode.appendChild(ecrea({eName:"IMG", src:"imagenes/icons/statusWrong.png", width:"20", height:"20"}));
<?php         } ?>
    }
    zonElem.value=String(data["zona"]).trim();
<?php if ($esAdmin) { ?>
    let txtElem = ebyid("prov_text");
    txtElem.value=data["comentarios"];
<?php } ?>
    const sttVal=String(data["status"]).trim();
<?php if (!$bloqueaProv) {
        $optionArray=[""=>"TODOS","activo"=>"ACTIVO","actualizar"=>"ACTUALIZAR"];
 ?>
    if (sttVal==="bloqueado"||sttVal==="inactivo") {
        if (sttElem.tagName==="SELECT") {
            const sttCell=sttElem.parentNode;
            //console.log("KILLING "+sttElem.tagName);
            ekil(sttElem);
            //console.log("CELL FIRSTELEM AFTER KILLING: "+sttCell.firstElementChild.tagName);
            sttCell.insertBefore(ecrea({eName:"INPUT",type:"text", name:"prov_status", id:"prov_status", readOnly:true,value:sttVal,className:"uppercase",size:"12"}),sttCell.firstElementChild);
            sttElem = ebyid("prov_status");
        }
    } else if (sttElem.tagName==="INPUT") {
        const sttCell=sttElem.parentNode;
        //console.log("KILLING "+sttElem.tagName);
        ekil(sttElem);
        //console.log("CELL FIRSTELEM AFTER KILLING: "+sttCell.firstElementChild.tagName);
        const optLst=[];
        for(let v of ["activo","actualizar"]) {
            //console.log("v="+v);
            optLst.push(ecrea({eName:"OPTION",value:v,eText:v.toUpperCase()}));
        }
        sttCell.insertBefore(ecrea({eName:"SELECT",name:"prov_status",id:"prov_status",eChilds:optLst}),sttCell.firstElementChild);
        sttElem = ebyid("prov_status");
    }
<?php } ?>
    if (sttElem.children&&sttElem.children.length>0&&sttElem.children[0].value.length==0)
        sttElem.children[0].classList.add("hidden");
    sttElem.value=sttVal;

    // flag block ... done already
    //const ssdt=(data["esServicio"]==="1");
    //doCheck(ebyid("esServicio"),ebyid("esSrvImg"),ssdt);

    //const ccdt=(data["conCodgEnDesc"]==="1");
    //doCheck(ebyid("conCodgEnDesc"),ebyid("conCEDImg"),ccdt);

<?php if ($consultaObjImp) { ?>
    //const oidt=(data["reqObjImp"]==="1");
    //doCheck(ebyid("reqObjImp"),ebyid("objImpImg"),oidt);
<?php } ?>

<?php if ($consultaCPyImp) { ?>
    //const ptcdt=(data["reqPayTaxChk"]==="1");
    //doCheck(ebyid("reqPayTaxChk"),ebyid("pyTxChkImg"),ptcdt);
<?php } ?>

<?php if ($consultaCCPSDf) { ?>
    //const dpsdt=(data["reqDefCvPrdSrv"]==="1");
    //doCheck(ebyid("reqDefCvPrdSrv"),ebyid("dfCvPSImg"),dpsdt);
<?php }
?>
    //console.log("Tipo = '"+tipo+"' 1="+(tipo&1)+":"+(tipo&1?"true":"false")+", 2="+(tipo&2)+":"+(tipo&2?"true":"false")+", 4="+(tipo&4)+":"+(tipo&4?"true":"false")+", 8="+(tipo&8)+":"+(tipo&8?"true":"false"));
    //console.log("TIPO COMERCIAL = "+<?= Proveedores::TIPO_COMERCIAL ?>);
    //console.log("TIPO ADUANA = "+<?= Proveedores::TIPO_ADUANA ?>);
    //console.log("TIPO FLETE = "+<?= Proveedores::TIPO_FLETE ?>);
    //console.log("TIPO LOGISTICA = "+<?= Proveedores::TIPO_LOGISTICA ?>);
    //doCheck(ebyid("esTComer"),ebyid("esTComImg"),tipo & <?= Proveedores::TIPO_COMERCIAL ?>);
    //doCheck(ebyid("esTAduan"),ebyid("esTAdnImg"),tipo & <?= Proveedores::TIPO_ADUANA ?>);
    //doCheck(ebyid("esTTrasl"),ebyid("esTTraImg"),tipo & <?= Proveedores::TIPO_FLETE ?>);
    //doCheck(ebyid("esTLogis"),ebyid("esTLogImg"),tipo & <?= Proveedores::TIPO_LOGISTICA ?>);

    clrem(ebyid('flagBlock'),'hidden');
<?php if ($consultaMasiva) { ?>
    //cladd(ebyid("prov_browse"),"hidden");
    ebyid("prov_browse").disabled=true;
    ebyid("prov_browse").type="hidden";
<?php } ?>
    console.log("END func fillData");
}
function doCheck(chkElem,chkImg,checked) {
    console.log("INI doCheck"+(chkElem?" "+chkElem.id:" noChkElem")+(chkImg?" ["+chkImg.id+"]":" noChkImg")+" = "+"'"+checked+"' "+(checked?"checked":"notChecked"));
    if (checked) {
        if (chkElem) {
            chkElem.value="1";
            chkElem.checked=true;
        }
        if (chkImg) chkImg.src="imagenes/icons/chkd24.png";
    } else {
        if (chkElem) {
            if (chkElem.type==="checkbox") {
                chkElem.value="1";
                chkElem.checked=false;
            } else chkElem.value="0";
        }
        if (chkImg) chkImg.src="imagenes/icons/deleteIcon12.png";
    }
    clset(chkImg,"hidden",chkElem&&chkElem.type==="checkbox");
}
function fixExpiredDate(tgt) {
    let genDateElem=ebyid("opinion_created");
    let expDateElem=ebyid("opinion_expired");
    if (genDateElem&&expDateElem) {
        let dateObj=strptime(date_format,genDateElem.value);
        dateObj.setDate(dateObj.getDate()+90);
        expDateElem.value=strftime(date_format,dateObj);
    }
}
function clearProv(esNuevo) {
    console.log("INI clearProv "+(esNuevo?"(nuevo)":""));
    let idpElem = ebyid("prov_id");
    let rzsElem = ebyid("prov_field");
    let rfcElem = ebyid("prov_rfc");
    let emlElem = ebyid("user_email");
    let iduElem = ebyid("user_id");
    let crdElem = ebyid("prov_credit");
    let pymElem = ebyid("prov_paym");
    let bnkElem = ebyid("prov_bank");
    let brfElem = ebyid("prov_bankrfc");
    let accElem = ebyid("prov_account");
    let rf1ChkE = ebyid("doRef1");
    let rf1RowE = ebyid("refRow1");
    let rf1Elem = ebyid("referencia1");
    let rf2ChkE = ebyid("doRef2");
    let rf2RowE = ebyid("refRow2");
    let rf2Elem = ebyid("referencia2");
    let ecrElem = ebyid("prov_receipt");
    let opiElem = ebyid("prov_opinion");
    let zonElem = ebyid("prov_zone");
    let sttElem = ebyid("prov_status");
    idpElem.value="";
    rzsElem.value="";
    rfcElem.value="";
    emlElem.value="";
    iduElem.value="";
    crdElem.value="0";
    pymElem.value="03";
    bnkElem.value="";
    brfElem.value="";
    accElem.value="";
    ecrElem.value="";
    rf1ChkE.checked=false;
    clrem(rf1RowE,"wasVis");
    cladd(rf1RowE,"hidden");
    rf1Elem.value="";
    rf2ChkE.checked=false;
    clrem(rf2RowE,"wasVis");
    cladd(rf2RowE,"hidden");
    rf2Elem.value="";

    doCheck(ebyid("esServicio"),ebyid("esSrvImg"),false);

    doCheck(ebyid("conCodgEnDesc"),ebyid("conCEDImg"),false);

<?php if ($consultaObjImp) { ?>
    doCheck(ebyid("reqObjImp"),ebyid("objImpImg"),false);
<?php } ?>

<?php if ($consultaCPyImp) { ?>
    doCheck(ebyid("reqPayTaxChk"),ebyid("pyTxChkImg"),false);
<?php } ?>

<?php if ($consultaCCPSDf) { ?>
    doCheck(ebyid("reqDefCvPrdSrv"),ebyid("dfCvPSImg"),false);
<?php }
?>
doCheck(ebyid("esTComer"),ebyid("esTComImg"),false);
doCheck(ebyid("esTAduan"),ebyid("esTAdnImg"),false);
doCheck(ebyid("esTTrasl"),ebyid("esTTraImg"),false);
doCheck(ebyid("esTLogis"),ebyid("esTLogImg"),false);

<?php if ($esAdmin) { ?>
    clrem(ebyid("itchdot"),"bglightgray1");
<?php } ?>
    while(ecrElem.nextSibling) ecrElem.parentNode.removeChild(ecrElem.nextSibling);
    opiElem.value="";
    while(opiElem.nextSibling) opiElem.parentNode.removeChild(opiElem.nextSibling);
    zonElem.value="";
<?php if ($esAdmin) { ?>
    let txtElem = ebyid("prov_text");
    txtElem.value="";
<?php } ?>
    sttElem.value="";

<?php if ($modificaProv||$validaBanco||$validaOpinion) { ?>
    if (esNuevo) {
        let numDisplayed=clrem(lbycn("beginHidden"),"hidden");
        //console.log("Showing non-browsable elements: "+numDisplayed);
        clrem(ebyid("prov_submit"),"hidden");
        clrem(ebyid("prov_reset"),"hidden");
        if (!/safari/i.test(navigator.userAgent)) {
            ecrElem.type="";
            ecrElem.type="file";
            opiElem.type="";
            opiElem.type="file";
        }
        ecrElem.classList.remove("hidden");
        opiElem.classList.remove("hidden");
        if (sttElem.children&&sttElem.children.length>0)
            sttElem.children[0].classList.add("hidden");
        sttElem.value="actualizar";
<?php if ($consultaMasiva) { ?>
        //cladd(ebyid("prov_browse"),"hidden");
        ebyid("prov_browse").disabled=true;
        ebyid("prov_browse").type="hidden";
<?php } ?>
    } else {
<?php } ?>
        let numHidden=cladd(lbycn("beginHidden"),"hidden");
        fixRef();
        //console.log("Hiding non-browsable elements: "+numHidden);
        cladd(ebyid("prov_submit"),"hidden");
        cladd(ebyid("prov_reset"),"hidden");
        ecrElem.classList.add("hidden");
        ecrElem.parentNode.appendChild(ecrea({eName:"SELECT",id:"acc_verified",name:"acc_verified",className:"pad3 vAlignCenter noprintBorder",eChilds:[{eName:"OPTION",value:"",text:"TODOS"},{eName:"OPTION",value:"0",text:"PENDIENTE"},{eName:"OPTION",value:"1",text:"ACEPTADO"},{eName:"OPTION",value:"-1",text:"RECHAZADO"}]}));
        opiElem.classList.add("hidden");
        opiElem.parentNode.appendChild(ecrea({eName:"SELECT",id:"opinion_fulfilled",name:"opinion_fulfilled",className:"pad3 vAlignCenter noprintBorder",eChilds:[{eName:"OPTION",value:"",text:"TODOS"},{eName:"OPTION",value:"-1",text:"VENCIDO"},{eName:"OPTION",value:"0",text:"PENDIENTE"},{eName:"OPTION",value:"1",text:"ACEPTADO"},{eName:"OPTION",value:"-2",text:"RECHAZADO"}]}));
        if (sttElem.children&&sttElem.children.length>0)
            sttElem.children[0].classList.remove("hidden");
<?php if ($consultaMasiva) { ?>
        //clrem(ebyid("prov_browse"),"hidden");
        ebyid("prov_browse").disabled=false;
        ebyid("prov_browse").type="submit";
<?php } ?>
<?php if ($modificaProv||$validaBanco||$validaOpinion) { ?>
    }
<?php } ?>
<?php if (!$consultaMasiva) { ?>
    cladd(ebyid('flagBlock'),'hidden');
<?php } ?>
}
<?php if ($esAdmin) {?>
function itchlet(evt) {
    //console.log("INI function itchlet "+ebyid("prov_id").value);
    let pid=ebyid("prov_id").value.trim();
    let uid=ebyid("user_id").value.trim();
    if (pid.length>0) {
        postService("consultas/Usuarios.php",{accion:'itch',uid:uid,pid:pid},function(text,params,state,status){
            if(state<4&&status<=200) return;
            if(state>4||status>200) {
                console.log("ERROR DE CONEXION. STATE="+state+", STATUS="+status+"\n"+text);
                return;
            }
            if(text.length>0) {
                try {
                    let jobj=JSON.parse(text);
                    if (jobj.message) {
                        console.log(jobj.message,jobj.original);
                    } else console.log("RESPUESTA SIN MENSAJE:\n",text);
                } catch(ex) {
                    console.log("ERROR EN RESPUESTA:\n",ex,"\n",text);
                }
            } else {
                console.log("SIN RESPUESTA");
            }
        });
    }
}
<?php } ?>
<?php
clog1seq(-1);
clog2end("scripts.registro");
