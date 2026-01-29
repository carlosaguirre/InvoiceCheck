<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    die("Empty File");
}
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.admon");
clog1seq(1);
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas");
$modificaUsrs = modificacionValida("Usuarios")||$esSistemas;
$modificaGrpo = modificacionValida("Grupo")||$esSistemas;
$modificaProv = modificacionValida("Proveedor")||$esSistemas;
$consultaPerm = consultaValida("Permisos")||$esSistemas;
$modificaPerm = modificacionValida("Permisos")||$esSistemas;
$isRoot = (getUser()->nombre==="admin");
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$gpoObj->rows_per_page=0;
$gpoObj->clearOrder();
$gpoObj->addOrder("alias");
global $query;
$gpoData=$gpoObj->getData("status='activo'",0,"id,alias");
//echo "// ".$query."\n";
//echo "// ".json_encode($gpoData)."\n";
$gi=""; $gj="";
foreach ($gpoData as $gpoIdx => $gpoRow) {
    if ($gpoIdx>0) { $gi.=", "; $gj.=", "; }
    $gId=$gpoRow["id"]; $gAlias=$gpoRow["alias"];
    $gi.="\"$gId\":\"$gAlias\"";
    $gj.="\"$gAlias\":\"$gId\"";
}
echo "var gpoMap={".$gi."};\nvar gmFlip={".$gj."};\n";
// ? > doShowFuncLogs=true;
//doShowLogs=true;
?>
function resetForm(formElem) {
    var elements = formElem.elements;
    var len = elements.length;
    //var txt = "";
    for (var i=0; i < len; i++) {
        var tag = elements[i].tagName.toLowerCase();
        var typ = elements[i].type.toLowerCase();
        //var nam = elements[i].name.toLowerCase();
        if (tag=="input" && typ=="text") elements[i].value="";
        //txt += tag+" "+typ+" "+nam+"\n";
    }
    //console.log(txt);
    return false;
}
function chkToggle(evt) {
    const tgt=evt.target;
    console.log("toggle check: ", tgt);
    tgt.checked=!tgt.checked;
}
function fillDataCheck() {
    console.log("INI function fillDataCheck");
    var blocks = ["grupo", "banco", "user"]; // "proveedor", 
    for (var i=0; i < blocks.length; i++) {
        var block = blocks[i];
        var idVal = getValueTxt(block+"_id");
        var elemDelete = document.getElementById(block+"_delete");
        if (idVal.length==0 || isNaN(idVal) || parseInt(idVal)<=0) {
            if (idVal.length>0) fillValue(block+"_id", "");
            if(elemDelete) elemDelete.style.display="none";
        } else {
            if(elemDelete) elemDelete.style.display="inline";
            if (block=="user") {
                const sysValElem=ebyid("user_sysval");
                const sysNameElem=ebyid("user_sysname");
                const sysLblElem=sysNameElem.parentNode;
                const sysChkElem=ebyid("user_syskey");
                sysChkElem.removeEventListener('onchange',chkToggle);
                sysChkElem.onchange=null;
                delete sysChkElem.onchange;
                if (sysValElem.value>0 && sysValElem.value!=="<?=getUser()->id?>") {
                    console.log("SysValElem ("+sysValElem.value+") is not <?=getUser()->id?>");
                    sysChkElem.onchange=chkToggle;
                    readyService("consultas/Usuarios.php",{llave:"id",id:sysValElem.value,solicita:"nombre",getjson:1},(j,e)=>{
                        console.log(" !!! READY RESULT !!!");
                        if (j.result && j.result==="success") {
                            console.log("JOBJ: "+JSON.stringify(j,jsonCircularReplacer()));
                            sysNameElem.textContent=j.nombre;
                            console.log("QUERY="+j.query);
                        } else {
                            sysNameElem.textContent="SISTEMAS";
                            console.log("JOBJ: "+JSON.stringify(j,jsonCircularReplacer()));
                            console.log("EXTRA: "+JSON.stringify(e,jsonCircularReplacer()));
                        }
                        sysLblElem.title="Permite ingresar una vez con contraseña de '"+sysNameElem.textContent+"'";
                    },(m,r,x)=>{
                        sysNameElem.textContent="SISTEMAS";
                        sysLblElem.title="Permite ingresar una vez con contraseña de 'SISTEMAS'";
                        console.log("ERROR: "+m);
                        console.log("TEXT: "+r.substring(0,500)+"...");
                        console.log("EXTRA: "+JSON.stringify(x,jsonCircularReplacer()));
                    });
                } else {
                    console.log("SysValElem ("+sysValElem.value+") is <?=getUser()->id?>");
                    sysNameElem.textContent="<?=getUser()->nombre?>";
                    sysLblElem.title="Permite ingresar una vez con contraseña de '"+sysNameElem.textContent+"'";
                }
                //changeAttribute("generaPassword", "disabled", getValueTxt("user_field").length==0);
                //fillValue("generaPassword", "Generar");
            }
        }
        const elemId = ebyid(block+"_id");
        if (elemId && elemId.value && (!elemId.lastValue || elemId.lastValue!==elemId.value)) {
            callChange(elemId);
        } // else if (!elemId) console.log("not found block "+block+"_id");
        // else if (!elemId.value) console.log("not found block "+block+"_id's value");
        // else console.log("not changed block "+block+"_id's value");
    }
}
function callChange(elem) {
    if (elem.id) switch(elem.id) {
        case "user_id": clset(ebyid("userByGroupRow"),"hidden",!elem.value.length);
    }
    elem.lastValue=elem.value;
}

<?php if ($modificaGrpo || $modificaProv || $modificaUsrs) { ?>
var guardaValorUsed=false;
function guardaValor(elem) {
    if (guardaValorUsed) return;
    guardaValorUsed=true;
    console.log("guardaValor: "+elem);
    if (elem && elem.tagName.toUpperCase()=="INPUT" && elem.getAttribute("type")=="checkbox") {
        var checked = (elem.checked?true:false);
        elem.disabled=true;
        var xmlhttp = ajaxRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var responseText = xmlhttp.responseText;
                var responseArray = responseText.split(": ");
                if (responseArray[0]=="Error") alert(responseArray[1]);
                elem.disabled=false;
                guardaValorUsed=false;
            }
        };
        params = "accion=definir&nombre=validaMetodoPago&valor=";
        if (checked) params += "SI";
        else         params += "NO";
        xmlhttp.open("GET", "consultas/InfoLocal.php?"+params,true);
        xmlhttp.send();
    }
    else guardaValorUsed=false;
}
function checkChange(elemname) {
    var elemId = document.getElementById(elemname+"_id");
    var elemSubmit = document.getElementById(elemname+"_submit");
    var elemReset = document.getElementById(elemname+"_reset");

    if (elemId && elemId.value && elemId.value.length>0) {
        if (elemSubmit) elemSubmit.style.display="inline";
        if (elemReset) elemReset.style.display="inline";
    } else {
        var msg = "";
        if (!elemId) msg = "Not found "+elemname+"_id\n";
        if (elemSubmit) elemSubmit.style.display="none";
        else msg += "Not found "+elemname+"_submit\n";
        if (elemReset) elemReset.style.display="none";
        else msg += "Not found "+elemname+"_reset\n";
        if (msg.length>0) alert(msg);
    }
}
function checkCode(prefix) {
    console.log("checkCode("+prefix+")");
    var rsElem = document.getElementById(prefix+"_field");
    var cdElem = document.getElementById(prefix+"_code");
    
    if (cdElem.value.length==0 || cdElem.value == cdElem.oldValue) {
        xmlhttp = ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                cdElem.value = xmlhttp.responseText;
                cdElem.oldValue = cdElem.value;
                console.log("checkCode RECEIVED "+cdElem.value);
            }
        };
        var consulta = false;
        switch(prefix) {
            case "proveedor": consulta = "Proveedores"; break;
        }
        if (consulta) {
            var chkUrl = "consultas/"+consulta+".php?nextCode="+rsElem.value;
            console.log("checkCode SENDING "+chkUrl);
            xmlhttp.open("GET", chkUrl, true);
            xmlhttp.send();
        }
    }
}
function setStatus(selectId, selectedOption) {
    var stElem = document.getElementById(selectId);
    var currSelectedElem=false, toSelectElem=false;
    for (var i=0; i<stElem.options.length; i++) {
        if (stElem.options[i].selected) currSelectedElem=stElem.options[i];
        if (stElem.options[i].value==selectedOption) toSelectElem=stElem.options[i];
    }
    // if (currSelectedElem!=toSelectElem)
    if (!currSelectedElem || currSelectedElem.value.length==0)
        toSelectElem.selected=true;
}
function deleteDataElement(dataelem) {
    var elemId=dataelem+"_id", elemFld=dataelem+"_field", elemUnit=dataelem, elemArt1="el", elemArt2="un", elemGenderSfx="o";
    switch(dataelem) {
        case "proveedor": break; // default values ok
        case "grupo": elemUnit="empresa"; elemArt1="la"; elemArt2="una"; elemGenderSfx="a"; break;
        case "user": elemUnit="usuario"; break;
    }
    if (getValueTxt(elemId).length==0)
        overlayMessage("<p>Debe seleccionar "+elemArt2+" "+elemUnit+" para poder borrarl"+elemGenderSfx+"</p>", "Error");
    else
        overlayConfirmation("<p>Confirme para borrar "+elemArt1+" "+elemUnit+" "+getValueTxt(elemFld)+"</p>", "CONFIRMACI&Oacute;N", function() { deleteConfirmed(dataelem); });
}
function deleteConfirmed(dataelem) {
    var formName = "forma_admon", submitName = dataelem+"_delete";
    switch(dataelem) {
        case "proveedor": formName += "_prv"; break;
        case "grupo": formName += "_gpo"; break;
        case "user": formName += "_user"; break;
    }

    var formElem = document.getElementById(formName);
    var hElem = document.createElement("INPUT");
    hElem.type = "hidden";
    hElem.name = submitName;
    hElem.value = "1";
    formElem.appendChild(hElem);
    formElem.submit();
}
function getGroupTitle(grpIds) {
    console.log("INI function getGroupTitle: "+grpIds);
    const grpIdLst=grpIds.split(";");
    const grpNmLst=[];
    grpIdLst.forEach(gId=>{if(gpoMap[gId]) grpNmLst.push(gpoMap[gId])});
    grpNmLst.sort();
    let title=grpNmLst.join("\r\n");
    return title;
}
function cleanProfilesPerUser() {
    fee(lbycn("user_perfil"),itm=>{itm.checked=false; clrem(itm.parentNode,'bgbeige2');});
    fee(lbycn("usrprfold"),itm=>{itm.disabled=true;});
    fee(lbycn("uxg"),itm=>{itm.value="";itm.disabled=true;});
    fee(lbycn("uxgold"),itm=>{itm.value="";itm.disabled=true;});
    fee(lbycn("upxgBtn"), im=>{
        im.src="imagenes/icons/group.png";
        im.removeAttribute("title");
        clrem(im,"hasPxG");
        im.onmouseenter=null;
        im.onmouseleave=null;
        im.onmousedown=null;
        im.onmouseup=null;
        im.onclick=null;
    });
}
function enableUPXGLt(img) {
    console.log("enableUPXGLt: "+img.id);
    const pfId=img.id.slice(5);
    const uxghd=ebyid("uxg_"+pfId);
    if (pfId!=="1" && pfId!=="4") {
        if (uxghd && !uxghd.disabled && uxghd.value.length>0) {
            img.src="imagenes/icons/group1.png";
            cladd(img,"hasPxG");
            img.title=getGroupTitle(uxghd.value);
            uxghd.disabled=false;
        } else {
            img.src="imagenes/icons/group.png";
            clrem(img,"hasPxG");
            img.removeAttribute("title");
            uxghd.disabled=true;
        }
    }
}
function enableUPXGBtn(img) {
    //console.log("enableUPXGBtn: "+img.id);
    const pfId=img.id.slice(5);
    const upchk=ebyid("user_perfil_"+pfId);
    if (upchk.checked) cladd(upchk.parentNode,"bgbeige2");
    const uxghd=ebyid("uxg_"+pfId);
    if (pfId!=="1" && pfId!=="4") {
        if (uxghd && !uxghd.disabled && uxghd.value.length>0) {
            img.src="imagenes/icons/group1.png";
            cladd(img,"hasPxG");
            img.title=getGroupTitle(uxghd.value);
        }
        img.onmouseenter=e=>{cladd(e.target,"ptOver");clrem(e.target,"ptOut");};
        img.onmouseleave=e=>{cladd(e.target,"ptOut");clrem(e.target,["ptOver","ptUp","ptDown"]);};
        img.onmousedown=e=>{cladd(e.target,"ptDown");};
        img.onmouseup=e=>{clrem(e.target,"ptDown");};
        img.onclick=e=>{overlayMessage(showEmpresasPerfil(e),'Permisos por Empresa');fee(lbycn("gxpchk"),gi=>{gi.onchange=e=>{clset(gi.parentNode,"bgbeige2",gi.checked);fixGpoIds(pfId);};});const dbx=ebyid('dialogbox');cladd(dbx,'widfit');dbx.style.height='calc(100% - 44px)';};
    }
}
function enableProfilesPerUser() {
    const usrIdElem=ebyid("user_id");
    console.log("INI function enableProfilesPerUser. "+(usrIdElem&&usrIdElem.value.length>0?"UsrId='"+usrIdElem.value+"'":"No UsrId"));
    if (usrIdElem && usrIdElem.value.length>0) fee(lbycn("upxgBtn"), im => { enableUPXGBtn(im); });
}
function updateProfilesPerUser(prfIds,pxgIds) {
    console.log("INI function updateProfilesPerUser prfIds="+prfIds+", pxgIds="+pxgIds);
    fee(lbycn("user_perfil"),itm=>{itm.checked=false; clrem(itm.parentNode,'bgbeige2');});
    fee(lbycn("usrprfold"),itm=>{itm.disabled=true;}); // Value es fijo, no quitar
    fee(lbycn("uxg"),itm=>{itm.value="";itm.disabled=true;});
    fee(lbycn("uxgold"),itm=>{itm.value="";itm.disabled=true;});
    fee(lbycn("upxgBtn"), im => {
        im.src="imagenes/icons/group.png";
        clrem(im,"hasPxG");
        if (im.id!=="gpxpr1" && im.id!=="gpxpr4") { // No incluir perfiles Administrador y Sistemas
            im.onmouseenter=e=>{cladd(e.target,"ptOver");clrem(e.target,"ptOut");};
            im.onmouseleave=e=>{cladd(e.target,"ptOut");clrem(e.target,["ptOver","ptUp","ptDown"]);};
            im.onmousedown=e=>{cladd(e.target,"ptDown");};
            im.onmouseup=e=>{clrem(e.target,"ptDown");};
            im.onclick=e=>{overlayMessage(showEmpresasPerfil(e),'Permisos por Empresa');fee(lbycn("gxpchk"),gi=>{gi.onchange=e=>{clset(gi.parentNode,"bgbeige2",gi.checked);fixGpoIds(im.id.slice(5));};});const dbx=ebyid('dialogbox');cladd(dbx,'widfit');dbx.style.height='calc(100% - 44px)';};
        }
    });
    if (prfIds.length>0) {
        prfIds = prfIds.split(",");
        for (let i=0; i < prfIds.length; i++) {
            const item=ebyid("user_perfil_"+prfIds[i]);
            if (item) {
                item.checked=true; // item.setAttribute("checked",true);
                cladd(item.parentNode,'bgbeige2');
            }
            const htem=ebyid("user_perfil_old_"+prfIds[i]);
            if (htem) {
                htem.disabled=false;
            }
        }
    }
    if (pxgIds.length>0) {
        pxgIds = pxgIds.split(",");
        for (let i=0; i < pxgIds.length; i++) {
            const parts=pxgIds[i].split(":");
            const pfId=parts[0];
            if (pfId!=="1"&&pfId!=="4") {
                const img=ebyid("gpxpr"+pfId);
                if (img) {
                    img.src="imagenes/icons/group1.png";
                    cladd(img,"hasPxG");
                }
                if (parts[1].length>0) {
                    const uxohd=ebyid("uxg_old_"+pfId);
                    if (uxohd) {
                        uxohd.value=parts[1];
                        uxohd.disabled=false;
                    }
                    const uxghd=ebyid("uxg_"+pfId);
                    if (uxghd) {
                        uxghd.value=parts[1];
                        uxghd.disabled=false;
                    }
                    if (img) img.title=getGroupTitle(parts[1]);
                }
            }
        }
    }
}
function fixGpoIds(pfId) {
    //console.log("INI function fixGpoIds "+pfId);
    const uxghd=ebyid("uxg_"+pfId);
    uxghd.value="";
    fee(lbycn("gxpchk"),chki=>{
        if(chki.checked) {
            //console.log("IS CHECKED: ",chki);
            if (uxghd.value.length>0)
                uxghd.value+=";";
            uxghd.value+=chki.id.split("_")[1];
        }
    });
    uxghd.disabled=(uxghd.value.length==0);
    const uxohd=ebyid("uxg_old_"+pfId);
    console.log("fixGpoIds "+pfId+" ["+uxohd.value+"] => ["+uxghd.value+"]");
    const img=ebyid("gpxpr"+pfId);
    if (uxghd.value.length>0) {
        if (!uxohd || uxohd.value.length==0) {
            uxghd.disabled=false;
            img.src="imagenes/icons/group1.png";
            cladd(img,"hasPxG");
        }
        img.title=getGroupTitle(uxghd.value);
    } else if (uxohd && uxohd.value.length>0) {
            uxghd.disabled=true;
            img.src="imagenes/icons/group.png";
            clrem(img,"hasPxG");
            img.removeAttribute("title");
    }
}
function showEmpresasPerfil(ev) {
    const tgt=ev.target;
    if ("gpxpr"!==tgt.id.slice(0,5)) return;
    const perId=tgt.id.slice(5);
    const perNm=tgt.getAttribute("nm");
    const uxghd=ebyid("uxg_"+perId);
    console.log("INI function showEmpresasPerfil pId="+perId+", gpoLst="+uxghd.value);
    const gpoLst=uxghd.value.split(";");//tgt.gpoIds.split(";");
    const lineLst=[{eName:"DIV", className:"admon marginV7 righted bgblack", title:"Todas", eChilds:[{eName:"input", type:"checkbox", onclick:(ev)=>{console.log(ev);const tg=ev?ev.currentTarget:false;console.log(tg);const ch=tg?tg.checked:false;console.log(ch?"true":"false");fee(lbycn("gxpchk"), gi=>{gi.checked=ch;});}}]}];

    for (let gpoAlias in gmFlip) {
        const gpoId=gmFlip[gpoAlias];
        const chkgp=gpoLst.includes(gpoId);
        lineLst.push({eName:"DIV", className:"admon marginV7"+(chkgp?" bgbeige2":""), eChilds:[{eName:"LABEL", className:"inblock boldValue lefted wid220px", htmlFor:"chk"+perId+"_"+gpoId, eText:gpoAlias}, {eName:"INPUT", id:"chk"+perId+"_"+gpoId, type:"checkbox", className:"gxpchk", checked:chkgp}]});
    }
    return {eName:"DIV",classList:"inblock centered marginT7",eChilds:lineLst};
}
<?php } ?>
<?php if ($isRoot) { ?>
var doRootProcess_blocked=false;
var doRootProcess_calledOverlay=false;
var doRootProcess_stack=[];
var doRootProcess_timeOut=false;
var doRootProcess_fullTimeOut=false;
var doRootProcess_filling=false;
var doRootProcess_overlayLoadingTimes=30;
function doRootProcess() {
    if (doRootProcess_blocked) {
        console.log("doRootProcess Blocked");
        return;
    }
    //console.log("doRootProcess Core Begins");
    doRootProcess_blocked=true;
    doRootProcess_calledOverlay=false;
    doRootProcess_stack=[];
    clearTimeout(doRootProcess_timeOut);
    overlayWheel();
    postService("consultas/Facturas.php", {actualiza:"rootProcess", responseLength:0}, function(responseText,params,readyState,hStatus) {
        //console.log("State="+readyState+", STATUS="+hStatus);
        if (readyState>2) {
            let endIdx = responseText.lastIndexOf("|*ICHK*|");
            let newText = "";
            if (endIdx>params.responseLength) {
                let newText = responseText.substring(params.responseLength, endIdx);
                params.responseLength=endIdx+8;
                if (newText.length>0) {
                    console.log("PUSH "+readyState+"/"+newText.length);
                    doRootProcess_stack.push({state:readyState,status:hStatus,text:newText,pars:params});
                    clearTimeout(doRootProcess_timeOut);
                    doRootProcess_timeOut = setTimeout(doRootOverlayFilling,1);
                }
            }
            if(readyState==4&&hStatus==200) {
                doRootProcess_blocked=false;
                if (doRootProcess_stack.length==0) {
                    clearTimeout(doRootProcess_fullTimeOut);
                    //console.log("doRootProcess Ajax Ended. State 4 and empty stack");
                    ebyid("rootProcessLed").src="imagenes/ledgreen.gif";
                //} else {
                    //console.log("doRootProcess Ajax Ended. State 4, still emptying stack ("+doRootProcess_stack.length+")");
                }
            } else {
                const rpl = ebyid("rootProcessLed");
                if (readyState>4 || hStatus>200) {
                    let errMsg=params.xmlHttpPost.statusText;
                    if (endIdx>0) {
                        errMsg=responseText.substring(params.responseLength, endIdx);
                        params.responseLength=endIdx+8;
                    } else if (!errMsg || !errMsg.length || errMsg.length==0) errMsg=responseText;
                    console.log("AJAX ERROR. readyState="+readyState+", status="+hStatus+", message="+errMsg);
                    if (rpl) rpl.src="imagenes/ledred.gif";
                } else if (rpl) rpl.src="imagenes/ledyellow.gif";
            }
        }
    });
    doRootProcess_fullTimeOut = setTimeout(function() {
        if (doRootProcess_blocked) {
            doRootProcess_blocked=false;
            let missing=doRootProcess_stack.length;
            doRootProcess_stack=[];
            clearTimeout(doRootProcess_timeOut);
            console.log("doRootProcess Core Ended by Timeout");
            const rpl = ebyid("rootProcessLed");
            if (rpl) rpl.src="imagenes/ledorange.gif";
        }
    },60000);
}
function doRootOverlayFilling()  {
    if (doRootProcess_filling) {
        console.log("doRootOverlayFilling Interrupted: Blocked!");
        return;
    }
    if (doRootProcess_stack.length==0) {
        console.log("doRootOverlayFilling Interrupted: ERROR: Empty Stack!");
        return;
    }
    doRootProcess_filling=true;
    //console.log("doRootOverlayFilling Begins");
    if (!doRootProcess_calledOverlay) {
        let ove = ebyid("overlay");
        if (ove.style.visibility!=="hidden") {
            console.log("doRootOverlayFilling Interrupted. Closing old Overlay");
            toggleVisibility("overlay");
            clearTimeout(doRootProcess_timeOut);
            doRootProcess_filling=false;
            doRootProcess_timeOut = setTimeout(doRootOverlayFilling,1);
            return;
        }
        console.log("doRootOverlayFilling Opening new Overlay");
        doRootProcess_calledOverlay=true;
        //let rawdata = doRootProcess_stack.shift();
        //let jsndata = JSON.parse(data.text);
        //if (data)
        overlayMessage("<div id='rootProcessArea'></div>","<div id='rootProcessTitle'>Respuesta <img id='rootProcessLed' src='imagenes/ledyellow.gif'></div>");
        const dra=ebyid("dialog_resultarea");
        //cladd(dra,"reverse");
        cladd(dra.firstElementChild,["reverse","yFlow"]);
        //console.log("scrTop="+dra.scrollTop+", scrHgt="+dra.scrollHeight);
        if (doRootProcess_stack.length>0) {
            //console.log("doRootOverlayFilling Interrupted. Craving for more after first ("+doRootProcess_stack.length+")");
            clearTimeout(doRootProcess_timeOut);
            doRootProcess_filling=false;
            doRootProcess_timeOut = setTimeout(doRootOverlayFilling,50);
            return;
        }
    } else {
        let rootPA = ebyid("rootProcessArea");
        if (!rootPA) {
            console.log("doRootOverlayFilling Interrupted. Overlay Elements are still loading...("+doRootProcess_overlayLoadingTimes+")");
            clearTimeout(doRootProcess_timeOut);
            doRootProcess_filling=false;
            if (doRootProcess_overlayLoadingTimes>0) {
                doRootProcess_overlayLoadingTimes--;
                doRootProcess_timeOut = setTimeout(doRootOverlayFilling,1);
            }
            return;
        }
        let rawData = doRootProcess_stack.shift();
        if (rawData) {
            try {
                let blocks=rawData.text.split("|*ICHK*|");
                console.log("doRootOverlayFilling RAW PROCESS LEN="+rawData.text.length+", BLOCKS="+blocks.length);
                const dra=ebyid("dialog_resultarea");
                if (dra.scrollTop>0 && dra.scrollTop<1) dra.scrollTop=0;
                for (let i=0; i<blocks.length; i++) {
                    if(blocks[i].length>0) {
                        let jsonObj=JSON.parse(blocks[i]);
                        //console.log("JSON:", jsonObj);
                        if (!Array.isArray(jsonObj)) jsonObj=[jsonObj];
                        for(let j=0; j<jsonObj.length; j++) {
                            let parentElem = rootPA;
                            if (jsonObj[j].eParId) {
                                let tempElem = ebyid(jsonObj[j].eParId);
                                if (tempElem) parentElem=tempElem;
                            }
                            let element = ecrea(jsonObj[j]);
                            if (element && parentElem) {
                                parentElem.appendChild(element);
                                adjustDialogBoxHeight(true);
                                const draDiff=dra.scrollHeight-dra.scrollTop;
                                if (draDiff<1000) {
                                    //console.log("scrTop="+dra.scrollTop+", scrHgt="+dra.scrollHeight+", dif="+draDiff+", TOBOTTOM");
                                    dra.scrollTop=dra.scrollHeight;
                                } // else console.log("scrTop="+dra.scrollTop+", scrHgt="+dra.scrollHeight+", dif="+draDiff+", NOTHING");
                            }
                        }
                    }
                }
            } catch (exc) {
                console.log("doRootOverlayFilling Exception Found: ",exc);
            }
        } else console.log("doRootOverlayFilling bug. Somehow there was no data!!!");
        if (doRootProcess_stack.length>0) {
            console.log("doRootOverlayFilling Interrupted. Craving for more ("+doRootProcess_stack.length+")");
            clearTimeout(doRootProcess_timeOut);
            doRootProcess_filling=false;
            doRootProcess_timeOut = setTimeout(doRootOverlayFilling,1);
            return;
        }
    }
    if (doRootProcess_stack.length==0) {
        clearTimeout(doRootProcess_fullTimeOut);
        clearTimeout(doRootProcess_timeOut);
        if (!doRootProcess_blocked)
            ebyid("rootProcessLed").src="imagenes/ledgreen.gif";
    }
    doRootProcess_filling=false;
}
function doTest(idx) {
    console.log("INI DOTEST "+idx);
    if (!idx) idx=0;
    let ovy=ebyid("overlay");
    if (ovy) overlayClose();
    const msg=getParagraphObject("Realizando Prueba "+(idx+1), idx);
    if (msg) {
        setTimeout(function() {
            overlayMessage(msg,"TEST");
            idx++;
            if (true) {
                const cIdx=idx;
                ovy.callOnClose=()=>doTest(cIdx);
            }
        },300);
    } else console.log("END DOTEST "+idx);
}
<?php } ?>
<?php if ($consultaPerm) { ?>
function setSelectedByAttributeList(selectId, element, attribute) {
    const selectElem=ebyid(selectId);
    const listArr = (element.hasAttribute(attribute)?element.getAttribute(attribute).split(","):false);
    for (let i=0; i < selectElem.options.length; i++) {
        const optionElem=selectElem.options[i];
        optionElem.selected = listArr?listArr.includes(optionElem.value):false;
    }
    return listArr;
}
function getSelectedValues(selectId) {
    const selectElem=ebyid(selectId);
    let listArr = [];
    for (let i=0; i < selectElem.options.length; i++) {
        const optionElem=selectElem.options[i];
        if (optionElem.selected) listArr.push(optionElem.value);
    }
    return listArr;
}
function removeOptionsByValue(selectId,values) {
    if (Array.isArray(selectId)) {
        selectId.forEach(selId=>{removeOptionsByValue(selId,values);});
    } else {
        if (!values) values="";
        if (!Array.isArray(values)) values=[values];
        const selectElem=ebyid(selectId);
        if (selectElem) for (let i=0; i < selectElem.options.length; i++) {
            const optionElem=selectElem.options[i];
            if (values.includes(optionElem.value)) {
                selectElem.remove(i);
                break;
            }
        }
    }
}
function attachOption(selectId, value, text, extra) {
    if (Array.isArray(selectId)) {
        let resultArray=[];
        selectId.forEach(selId=>{ resultArray.push(attachOption(selId, value, text, extra)); });
        return resultArray;
    }
    const selectElem = ebyid(selectId);
    const selectedIndex = selectElem.selectedIndex;
    for (let i=0; i < selectElem.options.length; i++) {
        const optionElem=selectElem.options[i];
        if (optionElem.value===value) {
            optionElem.text=text;
            if (extra) for(let prop in extra) {
                optionElem[prop]=extra[prop];
                optionElem.setAttribute(prop,extra[prop]);
            }
            doScrollTo(optionElem);
            return false;
        }
    }
    const optionElem = ecrea({eName:"OPTION", value:value, text:text});
    if (extra) for(let prop in extra) {
        optionElem[prop]=extra[prop];
        optionElem.setAttribute(prop,extra[prop]);
    }
    selectElem.appendChild(optionElem);
    doScrollTo(optionElem);
    return true;
}
function doScrollTo(elem) {
    if (elem) {
        const elemTop=elem.offsetTop;
        const parentTop=elem.parentNode.offsetTop;
        const scrollTop=elem.parentNode.scrollTop;
        const fixScrollTop=(elemTop-parentTop); // scrollTop+
        elem.parentNode.scrollTop=fixScrollTop;
    }
}
function pick(elem) {
    if (!elem.hasAttribute("prefixId") || !elem.hasAttribute("minPrefixId")) return;
    const prefix=elem.getAttribute("prefixId");
    const minPfx=elem.getAttribute("minPrefixId");
    const nameElem=ebyid(prefix+"Name");
    const descElem=ebyid(prefix+"Desc");
    const statOn=ebyid(prefix+"On");
    const statOff=ebyid(prefix+"Off");
    const readList=ebyid(prefix+"Read");
    const writeList=ebyid(prefix+"Write");
    const opt=elem.options[elem.selectedIndex];
    if (opt && opt.value.length>0) {
        console.log("PICK ELEM id='"+prefix+"Name', "+minPfx+"Id = '"+opt.value+"', value = '"+opt.text+"'");
        if (opt.value) nameElem[minPfx+"Id"]=opt.value;
        nameElem.value=opt.text;
        nameElem.oldValue=nameElem.value;
        if (opt.hasAttribute("desc")) {
            descElem.value=opt.getAttribute("desc");
            descElem.oldValue=descElem.value;
            console.log("DESC ELEM id='"+prefix+"Desc', value='"+descElem.value+"'");
        } else {
            descElem.value="";
            descElem.oldValue="";
        }
        if (statOn && statOff) {
            if (opt.hasAttribute("stat")&&opt.getAttribute("stat")==="1") {
                statOn.checked=true;
                nameElem.stat="1";
            } else {
                statOff.checked=true;
                nameElem.stat="0";
            }
            const readList=setSelectedByAttributeList(prefix+"Read",opt,"read");
            if (readList && readList.length>0) nameElem.readList=readList;
            else nameElem.readList=false;
            const writeList=setSelectedByAttributeList(prefix+"Write",opt,"write");
            if (writeList && writeList.length>0) nameElem.writeList=writeList;
            else nameElem.writeList=false;
        }
    } else {
        nameElem[minPfx+"Id"]="";
        nameElem.value="";
        nameElem.oldValue="";
        descElem.value="";
        descElem.oldValue="";
        if (statOff) statOff.checked=true;
        if (readList) readList.selectedIndex=-1;
        if (writeList) writeList.selectedIndex=-1;
    }
    nameElem.focus();
}
function save(selectId) {
    if (Array.isArray(selectId)) {
        selectId.forEach(selId=>{save(selId);});
        return false;
    }
    const selectElem=ebyid(selectId);
    if (!selectElem||!selectElem.hasAttribute("nombre")||!selectElem.hasAttribute("genero")||!selectElem.hasAttribute("clase")||!selectElem.hasAttribute("prefixId")||!selectElem.hasAttribute("minPrefixId")) return false;
    const nameEs=selectElem.getAttribute("nombre");
    const nameGen=selectElem.getAttribute("genero");
    const isFem=(nameGen==="f");
    const gndf=(isFem?"a":"o");
    const gndA=(isFem?"La":"El");
    const gnde=(isFem?" la":"l");
    const prefix=selectElem.getAttribute("prefixId");
    const minPfx=selectElem.getAttribute("minPrefixId");
    const nameElem=ebyid(prefix+"Name");
    const elemId=nameElem[minPfx+"Id"];
    const isNew=(!elemId||elemId.length==0);
    const pgI="<p class='marT10i'>";
    const pgE="</p>";
    if (nameElem.value.length==0) {
        overlayMessage(pgI+"Debe especificar "+(isNew?"un nuevo nombre de":"el nombre de"+gnde)+" "+nameEs+pgE,"ERROR");
        return false;
    }
    let hasError=false;
    if (isNew) {
        [].some.call(selectElem.options,opt=>{
            if (opt.text===nameElem.value) {
                overlayMessage(pgI+gndA+" "+nameEs+" "+opt.text+" ya existe!<br>Debe seleccionarl"+gndf+" primero si desea modificarl"+gndf+pgE,"ERROR");
                hasError=true;
            }
        });
        if (!hasError) {
            const message="Confirme para crear nuev";
            const title="CONFIRMAR NUEV";
            switch(selectId) {
                case "actions": overlayValidation(pgI+message+"a Acción <b>"+nameElem.value+"</b>"+pgE,title+"A ACCIÓN",confirmSaveAction); return true;
                case "profiles": overlayValidation(pgI+message+"o Perfil <b>"+nameElem.value+"</b>"+pgE,title+"O PERFIL",confirmSaveProfile); return true;
            }
        }
        return false;
    }
    const descElem=ebyid(prefix+"Desc");
    if (nameElem.value!==nameElem.oldValue || descElem.value!==descElem.oldValue) {
        [].some.call(selectElem.options,opt=>{
            if (opt.text===nameElem.value && opt.value!==elemId) {
                overlayMessage(pgI+gndA+" "+nameEs+" "+opt.text+" ya existe!<br>Seleccione "+gndA.toLowerCase()+" "+nameEs.toLowerCase()+" que desea modificar"+pgE,"ERROR");
                hasError=true;
            }
        });
        const message="Confirme para modificar ";
        const title="CONFIRMAR CORRECCIÓN";
        if (!hasError) {
            switch(selectId) {
                case "actions": overlayValidation(pgI+message+"Acción <b>"+nameElem.oldValue+"</b>"+pgE,title,confirmSaveAction); return true;
                case "profiles": overlayValidation(pgI+message+"Perfil <b>"+nameElem.oldValue+"</b>"+pgE,title,confirmSaveProfile); return true;
            }
        }
    }
    switch (selectId) {
        //case "actions": return false;
        case "profiles": {
            const message="Confirme para modificar Perfil <b>"+nameElem.oldValue+"</b>";
            const title="CONFIRMAR CORRECCIÓN";
            const onElem=ebyid("profileOn");
            const offElem=ebyid("profileOff");
            const hasOff=(!nameElem.stat || nameElem.stat==="0");
            if ((hasOff && onElem.checked)||(!hasOff && offElem.checked)) {
                overlayValidation(pgI+message+pgE,title,confirmSaveProfile); return true;
            }
            const readElem=ebyid("profileRead");
            const readList=getSelectedValues(prefix+"Read").join(",");
            const oldReadList=nameElem.readList?nameElem.readList:"";
            const writElem=ebyid("profileWrite");
            const writeList=getSelectedValues(prefix+"Write").join(",");
            const oldWriteList=nameElem.writeList?nameElem.writeList:"";
            if (readList!==oldReadList || writeList!==oldWriteList) {
                overlayValidation(pgI+message+pgE,title,confirmSavePermission); return true;
            }
        }
    }
    return false;
}
function confirmSaveAction() {
    const nameElem=ebyid("actionName");
    const descElem=ebyid("actionDesc");
    overlayClose();
    overlayWheel();
    postService("consultas/Acciones.php", {action:"adminSave",id:nameElem.actId,name:nameElem.value,desc:descElem.value}, function(responseText,params,readyState,hStatus) {
        if (readyState==4 && hStatus==200) {
            console.log("SAVEACTION RESPONSE: \n'"+responseText+"'\nPARAMS: ",params);
            overlayClose();
            if (responseText.length>0) {
                if (responseText==="REFRESH") {
                    console.log("REFRESH");
                    //location.reload(true);
                } else if (responseText.indexOf("ERROR:")==0) {
                    let message=responseText.slice(6).trim();
                    overlayMessage("<p class='marT10i'>"+message+"</p>","ERROR");
                } else if (responseText.match(/\D/)!==null) { // se encuentra una letra (caracteres no numericos)
                    let message=responseText;
                    overlayMessage("<p class='marT10i pre'>"+message+"</p>","ERROR FATAL");
                    console.log("ERROR: '"+responseText+"' ",params);
                } else {
                    const isNew=attachOption("actions", responseText, nameElem.value, {desc:descElem.value});
                    attachOption(["profileRead","profileWrite"], responseText, nameElem.value);
                    if (isNew) {
                        nameElem.actId=responseText;
                        ebyid("actions").value=responseText;
                    }
                    nameElem.oldValue=nameElem.value;
                    descElem.oldValue=descElem.value;
                    overlayMessage("<p class='marT10i'>Se ha "+(isNew?"creado nueva":"modificado")+" acción "+nameElem.value+"</p>");
                }
            }
        } else if (readyState>=4 && hStatus>=200) {
            overlayClose();
            overlayMessage("<p class='marT10i'>Error de conexión. Intente más tarde.</p>");
        }
    },function(errmsg,params,evt){
        overlayClose();
        if (errmsg && errmsg.length>0) errmsg=errmsg.replace(/'/g,'\\\'');
        else errmsg="EMPTY ERRMSG";
        overlayMessage("<p class='marT10i' title='"+errmsg+"'>Error de conectividad 1. Intente más tarde.</p>");
        conlog("SUBMIT FAILURE: "+errmsg,params,evt);
    });
}
function confirmSaveProfile() {
    overlayClose();
    overlayWheel();
    const nameElem=ebyid("profileName");
    const descElem=ebyid("profileDesc");
    const statValue=ebyid("profileOn").checked?"1":"0";
    const readList=getSelectedValues("profileRead").join(",");
    const oldReadList=nameElem.readList?nameElem.readList:"";
    const writeList=getSelectedValues("profileWrite").join(",");
    const oldWriteList=nameElem.writeList?nameElem.writeList:"";
    const params={action:"adminSave"};
    if (nameElem.prfId) params.id=nameElem.prfId;
    if (nameElem.value!==nameElem.oldValue) params.name=nameElem.value;
    if (descElem.value!==descElem.oldValue) params.desc=descElem.value;
    if (nameElem.stat!==statValue) params.stat=statValue;
    if (readList!==oldReadList) params.readList=readList;
    if (writeList!==oldWriteList) params.writeList=writeList;
    postService("consultas/Perfiles.php", params, function(responseText,params,readyState,hStatus) {
        if (readyState==4 && hStatus==200) {
            overlayClose();
            if (responseText.length>0) {
                if (responseText==="REFRESH") {
                    console.log("REFRESH");
                    //location.reload(true);
                } else if (responseText.indexOf("ERROR:")==0) {
                    let message=responseText.slice(6).trim();
                    overlayMessage("<p class='marT10i'>"+message+"</p>","ERROR");
                } else if (responseText.match(/\D/)!==null) { // se encuentra una letra (caracteres no numericos)
                    overlayMessage("<p class='marT10i pre'>"+responseText+"</p>","ERROR FATAL");
                    console.log("ERROR FATAL: '"+responseText+"'");
                } else {
                    const isNew=attachOption("profiles", responseText, nameElem.value, {desc:descElem.value});
                    if (isNew) {
                        nameElem.prfId=responseText;
                        ebyid("profiles").value=responseText;
                    }
                    nameElem.oldValue=nameElem.value;
                    descElem.oldValue=descElem.value;
                    overlayMessage("<p class='marT10i'>Se ha "+(isNew?"creado nuevo":"modificado")+" perfil "+nameElem.value+"</p>");
                }
            }
        } else if (readyState>=4 && hStatus>=200) {
            overlayClose();
            overlayMessage("<p class='marT10i'>Error de conexión. Intente más tarde.</p>");
        }
    }, function(errmsg,params,evt) {
        overlayClose();
        if (errmsg && errmsg.length>0) errmsg=errmsg.replace(/'/g,'\\\'');
        else errmsg="EMPTY ERRMSG";
        overlayMessage("<p class='marT10i' title='"+errmsg+"'>Error de conectividad 2. Intente más tarde.</p>");
        conlog("SUBMIT FAILURE: "+errmsg,params,evt);
    });
}
function confirmSavePermission() {
    overlayClose();
    overlayWheel();
    const nameElem=ebyid("profileName");
    const readList=getSelectedValues("profileRead").join(",");
    const oldReadList=nameElem.readList?nameElem.readList:"";
    const writeList=getSelectedValues("profileWrite").join(",");
    const oldWriteList=nameElem.writeList?nameElem.writeList:"";
    const params={action:"adminSave"};
    if (nameElem.prfId) params.id=nameElem.prfId;
    if (readList!==oldReadList) params.readList=readList;
    if (writeList!==oldWriteList) params.writeList=writeList;
    postService("consultas/Permisos.php", params, function(responseText,params,readyState,hStatus) {
        if (readyState==4 && hStatus==200) {
            overlayClose();
            if (responseText.length>0) {
                console.log("RESPONSE="+responseText);
                if (responseText==="REFRESH") {
                    console.log("REFRESH");
                    //location.reload(true);
                } else if (responseText.indexOf("ERROR:")==0) {
                    let message=responseText.slice(6).trim();
                    overlayMessage("<p class='marT10i'>"+message+"</p>","ERROR");
                } else if (responseText.match(/\D/)!==null) { // se encuentra una letra (caracteres no numericos)
                    let message=responseText;
                    overlayMessage("<p class='marT10i pre'>"+message+"</p>","ERROR FATAL");
                } else {
                    overlayMessage("<p class='marT10i'>Se ha modificado perfil "+nameElem.value+"</p>");
                }
            }
        } else if (readyState>=4 && hStatus>=200) {
            console.log(responseText,params,readyState,hStatus);
            overlayClose();
            overlayMessage("<p class='marT10i'>Error de conexión. Intente más tarde.</p>");
        }
    }, function(errmsg,params,evt) {
        overlayClose();
        if (errmsg && errmsg.length>0) errmsg=errmsg.replace(/'/g,'\\\'');
        else errmsg="EMPTY ERRMSG";
        overlayMessage("<p class='marT10i' title='"+errmsg+"'>Error de conectividad 3. Intente más tarde.</p>");
        conlog("SUBMIT FAILURE: "+errmsg,params,evt);
    });
}
function remove(selectId) {
    if (Array.isArray(selectId)) {
        selectId.forEach(selId=>{remove(selId);});
        return false;
    }
    const selectElem=ebyid(selectId);
    if (!selectElem||!selectElem.hasAttribute("nombre")||!selectElem.hasAttribute("genero")||!selectElem.hasAttribute("clase")||!selectElem.hasAttribute("prefixId")||!selectElem.hasAttribute("minPrefixId")) return false;
    const nameEs=selectElem.getAttribute("nombre");
    const nameGen=selectElem.getAttribute("genero");
    const nameCls=selectElem.getAttribute("clase");
    const isFem=(nameGen==="f");
    const gndf=(isFem?"a":"o");
    const gndA=(isFem?"La":"El");
    const prefix=selectElem.getAttribute("prefixId");
    const minPfx=selectElem.getAttribute("minPrefixId");
    const nameElem=ebyid(prefix+"Name");
    const elemId=nameElem[minPfx+"Id"];
    if (selectElem.selectedIndex<1) {
        overlayMessage("<p class='marT10i'>Debe elegir un"+gndf+" "+nameEs+" a eliminar</p>","ERROR");
    } else {
        const opt=selectElem.options[selectElem.selectedIndex];
        overlayValidation("<p class='marT10i'>Confirme para eliminar "+gndA.toLowerCase()+" "+nameEs+" <b>"+opt.text+"</b></p>","CONFIRMAR ELIMINACIÓN",()=>{
            overlayClose();
            overlayWheel();
            postService("consultas/"+nameCls+".php",{action:"adminDelete",id:opt.value},function(responseText,params,readyState,hStatus) {
                if (readyState==4 && hStatus==200) {
                    overlayClose();
                    if (responseText.length>0) {
                        if (responseText==="REFRESH") {
                            console.log("REFRESH");
                            //location.reload(true);
                        } else if (responseText.indexOf("ERROR:")==0) {
                            let message=responseText.slice(6).trim();
                            overlayMessage("<p class='marT10i'>"+message+"</p>","ERROR");
                        } else if (responseText.match(/\D/)!==null) { // se encuentra una letra (caracteres no numericos)
                            let message=responseText;
                            overlayMessage("<p class='marT10i pre'>"+message+"</p>","ERROR FATAL");
                        } else {
                            const selList=[selectId];
                            if (selectId==="actions") {
                                selList.push("profileRead");
                                selList.push("profileWrite");
                            }
                            removeOptionsByValue(selList,responseText);
                            selectElem.selectedIndex=-1;
                            const nameElem=ebyid(prefix+"Name");
                            const descElem=ebyid(prefix+"Desc");
                            nameElem[minPfx+"Id"]="";
                            nameElem.oldValue="";
                            descElem.oldValue="";
                            if (selectId==="profiles") {
                                ebyid("profileOff").checked=true;
                                ebyid("profileRead").selectedIndex=-1;
                                ebyid("profileWrite").selectedIndex=-1;
                            }
                            overlayMessage("<p class='marT10i'>"+gndA+" "+nameEs+" "+nameElem.value+" ha sido eliminad"+gndf+"</p>");
                        }
                    }
                } else if (readyState>=4 && hStatus>=200) {
                    overlayClose();
                    overlayMessage("<p class='marT10i'>Error de conexión. Intente más tarde.</p>");
                }
            },function(errmsg,params,evt) {
                overlayClose();
                if (errmsg && errmsg.length>0) errmsg=errmsg.replace(/'/g,'\\\'');
                else errmsg="EMPTY ERRMSG";
                overlayMessage("<p class='marT10i' title='"+errmsg+"'>Error de conectividad 4. Intente más tarde.</p>");
                conlog("SUBMIT FAILURE: "+errmsg,params,evt);
            });
        });
    }
}
<?php } ?>
function setUploadBankList() {
    console.log("INI setUploadBankList");
    const pnb=ebyid("pageNavBlock");
    if (pnb) {
        let filterBox = lbycn("filter_box");
        let params="";
        let exacto = "";
        for(let i=0; i < filterBox.length; i++) {
            if (filterBox[i].value) {
                params+='&param[]='+filterBox[i].name;
                let paramVal = filterBox[i].value;
                let filtro = filterBox[i].getAttribute("filter");
                if (filtro == "exacto") {
                    if (exacto.length>0) exacto += ",";
                    exacto += filterBox[i].name;
                }
                params+="&"+filterBox[i].name+"="+paramVal;
            }
        }
        const d=new Date(); // "YmdHis"
        const filename="bancos"+d.getFullYear()+("0"+(d.getMonth()+1)).slice(-2)+("0"+d.getDate()).slice(-2)+("0"+d.getHours()).slice(-2)+("0"+d.getMinutes()).slice(-2)+("0"+d.getSeconds()).slice(-2)+".txt";
        if (exacto.length>0) params="&exacto="+exacto+params;
        pnb.appendChild(ecrea({eName:"A",download:filename,className:"abs_e buttonLike marR3",href:"consultas/Bancos.php?action=layout"+params,eChilds:[{eName:"IMG",src:"imagenes/icons/dwnload1.png",title:"Descargar Layout de Carga Masiva"}]}));
        console.log("Download List Set! action=layout"+params);
    } else {
        setTimeout(setUploadBankList,45);
        console.log("Not Found pageNavBlock! Retry");
    }
}
function downloadBankList() {
    console.log("INI downloadBankList"); // Clave, Alias, Rfc, Cuenta
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
function traceActiveElement() {
    //console.log("ActiveElement: ",document.activeElement);
    addEvent(document, "keyup", function(e) {
        e=e||window.event;
        if (e.repeat) return;
        if (e.code==="Tab") {
            //console.log("ActiveElement: ",document.activeElement);
        }
    });
}
<?php
clog1seq(-1);
clog2end("scripts.admon");
