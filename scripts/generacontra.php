<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.generacontra");
clog1seq(1);
if(!hasUser()) die();
//clog2("user=".json_encode(getUser()));
$esAdmin=validaPerfil("Administrador");
$esSistemas=$esAdmin||validaPerfil("Sistemas");
$esPruebas = in_array(getUser()->nombre, ["admin","test","nuevo"]);
$esRechazante = (validaPerfil("Rechaza Aceptadas")||$esSistemas);
$esBorraDoc = (validaPerfil("Elimina Documentos")||$esSistemas);
$esBloqueaEA = (validaPerfil("Bloquea Entrada Almacen")||$esSistemas);
$modificaProc=modificacionValida("Procesar");
?>
window.onload = fillPaginationIndexes;
function fixSelectorBy(sel,type) {
    if (sel.getAttribute('type')===type) return;
    //console.log("fixSelector "+sel.name+" By "+type);
    const val=sel.value;
    const tmp=[...sel.options];
    tmp.forEach(item=>item.text=item.getAttribute(type));
    tmp.sort((a,b) => {
        if(a.value.length==0 || (b.value.length>0 && a.text <= b.text)) return -1; // se queda igual: a, b
        return 1; // se cambia el orden: b, a
    });
    ekfil(sel);
    sel.append(...tmp);
    sel.setAttribute('type',type);
    sel.value=val;
}
function pickType(elem) {
    if (!elem || !elem.value) { return; }
    //console.log("pickType "+elem.value);
    fixSelectorBy(document.getElementById("gpoOpt"), elem.value);
    fixSelectorBy(document.getElementById("prvOpt"), elem.value);
}
function submitCallbackSentFunc(xhr) {
    //console.log("Success Callback! "+xhr.readyState+"/"+xhr.status+" "+xhr.responseText.length);
    const command=ebyid("command").value;
    if (xhr.isProgressEnabled && xhr.readyState==3 && xhr.status==200) {
        //console.timeLog("Callback Time");
        cladd(ebyid("waitRoll"),"hidden"); // ocultar rollo de espera
        if (command==="Buscar") {
            const aceptadas = lbycn("facturasAceptadas");
            const alen=(aceptadas?aceptadas.length:0);
            if (alen>0) {
                cladd(ebyid("seleccionar"),"hidden");
                const stopBtn=ebyid("stopBtn");
                stopBtn.xhr=xhr;
                clrem(stopBtn,"hidden");
                clrem(ebyid("acceptbuttondiv").firstElementChild,"hidden");
                const dtbd=ebyid("dialog_tbody");
                clrem(dtbd.parentNode,"hidden");
                const invN=(dtbd.inv?dtbd.inv:alen);
                const countText=alen+"/"+invN+" encontrada"+(alen==1?"":"s");
                fee(lbycn("countDisplay"),el=>{ekfil(el);el.appendChild(ecrea({eText:countText}));});
            }
        } else if (command==="Generar") {
            cladd(ebyid("gencontraform"),"gencr");
            clrem(ebyid("scrolltablediv_gencr"),"hidden");
            clrem(ebyid("result_tbody").parentNode,"hidden");
            const generadas = lbycn("contragenerado");
            if (generadas && generadas.length>0) {
                const emptySection = ebyid("emptySection");
                ekfil(emptySection);
                emptySection.appendChild(ecrea({eText:'Contra-recibos Generados: '+generadas.length+" / "+xhr.chkLen}));
                clrem(emptySection,"hidden");
            }
        }
    } else if (xhr.readyState==4 && xhr.status==200) {
        //console.timeEnd("Callback Time");
        cladd(ebyid("waitRoll"),"hidden"); // ocultar rollo de espera
        if (command==="Buscar") {
            const countDisplay = lbycn("countDisplay");
            const stopBtn=ebyid("stopBtn");
            if (stopBtn.xhr) { stopBtn.xhr=null; delete stopBtn.xhr; }
            cladd(stopBtn,"hidden");
            clrem(ebyid("acceptbuttondiv").firstElementChild,"hidden");
            clrem(ebyid("seleccionar"),"hidden");
            const checkAllElem = document.getElementById("checkall");
            if (checkAllElem) {
                checkAllElem.checked=true;
                doAllChecks(true);
            }
            const aceptadas = lbycn("facturasAceptadas");
            const anum = (aceptadas?aceptadas.length:0);
            if (anum>0) {
                clrem(ebyid("dialog_tbody").parentNode,"hidden");
                const countText=anum+" encontrada"+(anum==1?"":"s");
                fee(countDisplay,el=>{ekfil(el);el.appendChild(ecrea({eText:countText}))});
                ebyid("checkall").focus();
                fee(lbycn("checkInvoice"),chk=>{const pN=chk.parentNode;pN.removeChild(chk);pN.appendChild(chk);});
            } else {
                fee(lbycn("datatable"),el=>cladd(el,"hidden"));
                const emptySection = ebyid("emptySection");
                ekfil(emptySection);
                emptySection.appendChild(ecrea({eText:'0 encontradas'}));
                clrem(emptySection,"hidden");
            }
        } else if (command==="Generar") {
            cladd(ebyid("gencontraform"),"gencr");
            clrem(ebyid("scrolltablediv_gencr"),"hidden");
            clrem(ebyid("result_tbody").parentNode,"hidden");
            const generadas = lbycn("contragenerado");
            if (generadas && generadas.length>0) {
                const emptySection = ebyid("emptySection");
                ekfil(emptySection);
                emptySection.appendChild(ecrea({eText:'Contra-recibos Generados: '+generadas.length}));
                clrem(emptySection,"hidden");
            }
        //} else {
            //fee(lbycn("datatable"),el=>cladd(el,"hidden"));
        }
    } else if (xhr.readyState>4 || xhr.status>200) {
        //console.timeEnd("Callback Time");
        console.log("ERROR bad state "+xhr.readyState+" or status "+xhr.status);
        cladd(ebyid("waitRoll"),"hidden");
        const stopBtn=ebyid("stopBtn");
        if (stopBtn && stopBtn.xhr) { stopBtn.xhr=null; delete stopBtn.xhr; }
        cladd(stopBtn,"hidden");
        clrem(ebyid("seleccionar"),"hidden");
        //fee(lbycn("datatable"),el=>cladd(el,"hidden"));
    }
}
function submitAjax(submitValue) {
    fee(lbycn("datatable"), function(elem) { cladd(elem,"hidden"); });
    cladd(ebyid("emptySection"),"hidden");
    clrem(ebyid("gencontraform"),"gencr");
    clrem(ebyid("waitRoll"),"hidden");
    const stopBtn = ebyid("stopBtn");
    while(stopBtn && stopBtn.nextSibling) stopBtn.parentNode.removeChild(stopBtn.nextSibling);
    const postURL = 'selectores/generacontra.php';
    const formName = 'gencontraform';
    let resultDiv = 'dialog_tbody';
    if (submitValue==='Generar') resultDiv='result_tbody';
    else cladd(ebyid("scrolltablediv_gencr"),"hidden");
    const waitingHtml = '';
    const formElem = document.forms[formName];
    formElem.command.value = submitValue;
    //console.time("Callback Time");
    const xhr=ajaxPost(postURL, formName, resultDiv, waitingHtml, submitCallbackSentFunc);
    if (xhr.parameters.check) {
        xhr.chkLen=xhr.parameters.check.length;
        //console.log("FOUND PARAMETERS CHECK. CHKLEN="+xhr.chkLen);
    } else if (formElem && formElem.check) {
        xhr.chkLen=formElem.check.length;
        //console.log("FOUND FORM CHECK. CHKLEN="+xhr.chkLen);
    } else {
        const blkSmy=lbycn("blockSummary");
        const single=lbycn("singlePart");
        const crCount=blkSmy.length+single.length;
        if (crCount>0) {
            xhr.chkLen=crCount;
            //console.log("FOUND CR BLOCKS = "+crCount);
        } //else console.log("NOT FOUND CHECK IN PARAMETERS NOR FORM NOR BLOCKS", xhr.parameters);
    }
    ebyid(resultDiv).innerHTML = ""; // debe ir después de ajaxPost, para no borrar información antes de enviarla
    xhr.inclusiveSeparator="</tr><!-- END ROW -->";
    xhr.isProgressEnabled=true;
    xhr.extraTimeout=3000;
    xhr.timeoutCallbackFunc=function(evt) {
        //console.timeEnd("Callback Time");
        cladd(ebyid("waitRoll"),"hidden");
    };
    xhr.onabort=(evt)=>{
        console.log("ABORT state "+xhr.readyState+", status "+xhr.status);
        cladd(ebyid("waitRoll"),"hidden");
        const stopBtn=ebyid("stopBtn");
        if (stopBtn && stopBtn.xhr) { stopBtn.xhr=null; delete stopBtn.xhr; }
        cladd(stopBtn,"hidden");
        if (submitValue==="Buscar") {
            const aceptadas = lbycn("facturasAceptadas");
            const alen=(aceptadas?aceptadas.length:0);
            if (alen>0) {
                const dtbd=ebyid("dialog_tbody");
                clrem(dtbd.parentNode,"hidden");
                const invN=(dtbd.inv?dtbd.inv:alen);
                const countText=alen+((alen!=invN)?"/"+invN:"")+" encontrada"+(alen==1?"":"s");
                fee(lbycn("countDisplay"),el=>{ekfil(el);el.appendChild(ecrea({eText:countText}));});
            }
            while(stopBtn && stopBtn.nextSibling) stopBtn.parentNode.removeChild(stopBtn.nextSibling);
            stopBtn.parentNode.appendChild(document.createTextNode("Búsqueda Interrumpida!"));
        }
    };
    return true; // usado solo en boton Generar. Si regresa true, oculta boton para generar de nuevo
}
function doAllChecks(chkd) { // console.log("INI doAllChecks ( chkd="+(chkd?"true":"false")+" )");
    const curr={blk:false,num:0,sum:{}}; fee(lbycn("checkInvoice"),(elem,i)=>{ autoSwitch(elem,i,chkd,curr); });
    if (curr.blk!==false) fixBlock(curr.blk, curr.num, curr.sum);
}
function autoSwitch(el,idx,chkd,ref) { // ref.blk,ref.num,ref.sum
    //console.log("INI autoSwitch el=["+el.name+","+(el.checked?"true":"false")+"], idx="+idx+", chkd="+(chkd?"true":"false")+", ref="+JSON.stringify(ref));
    if (clhas(el,"pnd")) { if (!ref.pnd) ref.pnd=1; else ref.pnd++; if (chkd) return; }
    //else if (chkd) { if(!ref.chkd) ref.chkd=1; else ref.chkd++; }
    if(el.checked===chkd) { /*if(!ref.same)ref.same=1;else ref.same++;*/ return; }
    const r=tpar(el,"TR"); el.checked=chkd; if (!ref.tggl) ref.tggl=1; else ref.tggl++;

    if(clhas(r,"partial")) {
        if(clhas(r,"singlePart")){clset(r,["grayed","bgblack"],!chkd);clset(r,"bggreenish",chkd);}
        else if(clhas(r,"fractionPart")){clset(r,["grayed","bglightgray1"],!chkd);clset(r,"bggreenlt",chkd);}
        const etot=+el.getAttribute("total"); const emon=el.getAttribute("mon"); const blkn=(+el.getAttribute("block"))+1;

        if(etot<0)fee(lbycn(chkd?"reddish":"redden",r),c=>{clrem(c,chkd?"reddish":"redden");cladd(c,chkd?"redden":"reddish");});
        if (ref.blk===false) ref.blk=blkn;
        else if (ref.blk!=blkn) { fixBlock(ref.blk, ref.num, ref.sum); ref.blk=blkn; ref.num=0; ref.sum={}; }
        if (!(emon in ref.sum)) ref.sum[emon]=0;
        if (chkd) { ref.num++; ref.sum[emon]+=etot; } else { ref.num--; ref.sum[emon]-=etot; }
        // console.log("FNC autoSwitch["+(idx+1)+"]|"+(chkd?"true":"false")+"->(tot="+etot+",sum="+JSON.stringify(ref.sum)+")");
    }
}
function toggleChk(chkElm) {
    let blkn=(+chkElm.getAttribute("block")); const r=tpar(chkElm,"TR");
    if (chkElm.type==="radio") {
        const rchkd=!(chkElm.getAttribute("previous")==="true");
        //const rchkd=!(chkElm.hasAttribute("checked")&&chkElm.getAttribute("checked")==="true");
        //const rchkd=!chkElm.checked;
        //console.log("INI toggleChk "+chkElm.type+" "+chkElm.id+" ("+blkn+"): "+(rchkd?"checked":"unchecked"));
        let curr={blk:false,num:0,sum:{}}; fee(lbycn(chkElm.id),(elem,i)=>autoSwitch(elem,i,rchkd,curr)); //console.log("DATA0: ",curr);
        if(rchkd&&curr.pnd&&!curr.tggl){curr={blk:false,num:0,sum:{}};fee(lbycn(chkElm.id),(elem,i)=>autoSwitch(elem,i,false,curr)); /*console.log("DATA1: ",curr);*/ }
        if (curr.blk!==false) fixBlock(curr.blk, curr.num, curr.sum);
        else {
            fixBlock(blkn,0,0);
        }
        return;
    } blkn+=1; const chkd=chkElm.checked;
    if(chkd&&clhas(chkElm,"pnd")) {
        chkElm.checked=false; //console.log("INI toggleChk "+chkElm.type+" "+chkElm.name+": unchecked pnd");
<?php if($modificaProc) { ?>
        xmlhttp=verificaFactura(chkElm.value,true);
        xmlhttp.checkbox=chkElm;
        //console.log("CHECK IS ADDED TO XMLHTTP: "+JSON.stringify(xmlhttp,jsonCircularReplacer()),chkElm);
<?php } ?>
        return;
    } //console.log("INI toggleChk "+chkElm.type+" "+chkElm.name+": "+(chkElm.checked?"checked":"unchecked"));
    if(clhas(r,"partial")) {
        if(clhas(r,"singlePart")){clset(r,["grayed","bgblack"],!chkd);clset(r,"bggreenish",chkd);}
        else if(clhas(r,"fractionPart")){clset(r,["grayed","bglightgray1"],!chkd);clset(r,"bggreenlt",chkd);}
        const etot=+chkElm.getAttribute("total"); const emon=chkElm.getAttribute("mon");
        if (!chkd){const blkchk=ebyid("blkchk"+blkn);if(blkchk&&blkchk.hasAttribute("checked")&&blkchk.getAttribute("checked")==="true"){blkchk.removeAttribute("checked");blkchk.checked=false;}}
        if(etot<0)fee(lbycn(chkd?"reddish":"redden",r),c=>{clrem(c,chkd?"reddish":"redden");cladd(c,chkd?"redden":"reddish");});
        const esum={};esum[emon]=etot;let enom=1; if(!chkd){esum[emon]*=-1;enom=-1;} // console.log("FNC toggleChk->fixBlock",esum);
        fixBlock(blkn, enom, esum);
    }
}
function fixBlock(blkNum,count,sum) { //console.log("INI fixBlock bN="+blkNum+", cnt="+count+", sum="+JSON.stringify(sum)+"("+(typeof sum)+")");
    const sumType=(typeof sum);
    const sumElm=ebyid("blockSum"+blkNum);
    if (!sumElm) return;
    let sumTxt=sumElm.getAttribute("sum");
    const sumLst=sumTxt.split(",");
    sumLst.forEach(txt=>{ const pair=txt.split(":");if(sumType==="object"&&pair.length>1&&pair[0].length>0){if(!(pair[0] in sum)) sum[pair[0]]=0; sum[pair[0]]+=+pair[1];}});
    if (sumType==="object") {
        ekfil(sumElm); sumTxt="";
        for (pMon in sum) {
            let pTot=sum[pMon]; if (sumTxt.length>0) sumTxt+=","; sumTxt+=pMon+":"+pTot; let pClass="nomargin";
            if (pTot<0) { pTot*=-1; pTot="("+pTot.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")+")"; pClass+=" redden"; } else pTot=pTot.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            sumElm.appendChild(ecrea({eName:"P",className:pClass,eText:"$"+pTot+pMon}));
        }
        sumElm.setAttribute("sum",sumTxt);
    } //else console.log("SUM NOT OBJECT: "+sum);
    const blkId="block"+blkNum;
    const blkElm=ebyid(blkId);
    //console.log("Get NumCel "+blkId,blkElm);
    //if (count!=0) {
        const blkCnt=+blkElm.getAttribute("num")+count;
        if (count!=0) blkElm.setAttribute("num",blkCnt);
        const allNum=+blkElm.getAttribute("all");
        const fullBlk=(allNum==blkCnt);
        //console.log("FULLBLK="+(fullBlk?"true":"false")+". allNum="+allNum+" vs blkCnt="+blkCnt);
        const blkchk=ebyid("blkchk"+blkNum);
        const blkoff=ebyid("blkoff"+blkNum);
        const prevchkd=blkchk.getAttribute("previous")==="true";
        //console.log("Get Block Check "+blkNum+"="+(prevchkd?"true":"false"),blkchk);
        if (prevchkd!==fullBlk) {
            blkchk.setAttribute("checked",fullBlk?"true":"false");
            blkchk.setAttribute("previous",fullBlk?"true":"false");
            //if (fullBlk) blkchk.checked=true; else blkoff.checked=true;
            blkchk.checked=fullBlk;
            //console.log("SWITCH CHECKED "+(fullBlk?"true":"false"));
        } //else console.log("SAME CHECKED "+(fullBlk?"true":"false"));
        if (count!=0) blkElm.textContent="#"+blkCnt; // clset(tpar(blkElm,"TR"),"hidden",blkCnt==0);
    //}
}
function ulErr(msg,successNum) {
    if (msg.length>0) {
        msg = "<UL style='width: fit-content;margin: 0 auto;padding-top: 8px;'>"+msg+"</UL>";
    }
    if (successNum==0) {
        msg = "<H3>No se pudo generar ninguno de los contra recibos</H3>"+msg;
    }
    overlayMessage("<div style='float:none;display:block;clear:both;' class='maxFlowMsg'>"+msg+"</div>","ERRORES");
}
function recalculaEmpresas() {
    conlog("INI recalculaEmpresas");
    xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("gpoSelectArea");
            elem.innerHTML=respTxt;
            conlog("REFRESH complete "+respTxt.length);
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Grupo.php?selectorhtml=1&tipolista="+tipolista+"&defaultText=Selecciona una...",true);
    xmlhttp.send();
}
function recalculaProveedores() {
    conlog("INI recalculaProveedores");
    xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("prvSelectArea");
            elem.innerHTML=respTxt;
            conlog("REFRESH complete "+respTxt.length);
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Proveedores.php?selectorhtml=1&tipolista="+tipolista+"&defaultText=Selecciona uno...",true);
    xmlhttp.send();
}
function cancelingInvoice(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    if (!tgt.fId) {
        const blk=ebyid("cancelReasonBlk");
        const cap=ebyid("cancelReasonCap");
        cladd("cancelReasonSnd","hidden");
        cladd("cancelReasonTxt","hidden");
        cladd(blk,"bgredvip2");
        cap.textContent="No es posible rechazar una factura sin ID";
        cap.style.width="auto";
        blk.style.textAlign="center";
        return;
    }
    const crt=ebyid("cancelReasonTxt");
    const motivo=crt.value;
    if (motivo.length>0) {
        let parameters={action:"cancelInvInReq",script:"generacontra",tgtId:tgt.id,invId:tgt.fId,motivo:motivo,accion:"Rechaza"<?= $esSistemas?",plan:\"b\"":"" ?>};
        const usr=ebyid("cancelUserName");
        if (usr) parameters.username=usr.value;
        console.log("INI cancelingInvoice service: action='"+parameters.action+"', invId='"+tgt.fId+"', motivo='"+motivo+"', plan='b', accion='Rechaza', url='consultas/Facturas.php'");
        postService("consultas/Facturas.php", parameters, getPostRetFunc(cancelledInvoice,notCancelledInvoice), getPostErrFunc(notCancelledInvoice,{file:"scripts/generacontra.php",function:"cancelingInvoice",factId:tgt.fId,motivo:motivo}));
    } else {
        console.log("ERR cancelingInvoice: Debe indicar un motivo");
        crt.focus();
        crt.times=6;
        if (crt.intid) {
            clearInterval(crt.intid);
            clrem(crt,"bgredvip2");
            crt.intid=false;
        }
        crt.intid=setInterval((e)=>{
            console.log("I "+e.times);
            if(e.times&&e.times>0){
                clfix(e,"bgredvip2");
                e.times--;
            } else {
                clearInterval(e.intid);
                clrem(e,"bgredvip2");
                e.intid=false;
            }
        },200,crt);
    }
}
function cancelledInvoice(jobj,extra) {
    if (jobj && jobj.result && jobj.result==="success") {
        cladd("waitRoll","hidden");
        overlayClose();
        overlayMessage(getParagraphObject(jobj.message),"Factura Rechazada");
        setTimeout(()=>{
            console.log("Preparing CallOnClose");
            const ovy=ebyid("overlay");
            ovy.callOnClose=()=>{
                console.log("CALLONCLOSE CANCELLED INVOICE");
                ekil(ebyid("cancelInvoice"));
                const fId=jobj.idFactura;
                const chk=ebyid("chk"+fId);
                const row=tpar(chk,"TR");
                const blkn=+chk.getAttribute("block")+1;
                const blk=ebyid("block"+blkn);
                if (blk) {
                    const allnum=+blk.getAttribute("all")-1;
                    blk.setAttribute("all",allnum);
                    if (chk.checked) { //  && !clhas(chk,"pnd")
                        const sum={};
                        const tot=+chk.getAttribute("total");
                        const mon=chk.getAttribute("mon");
                        sum[mon]=-tot;
                        fixBlock(blkn,-1,sum);
                    }
                }
                ekil(row);
                blkcnt=ebyid("blockCount");
                const allinv=+blkcnt.getAttribute("all")-1;
                blkcnt.setAttribute("all",allinv);
                const dispTxt=""+allinv+" encontrada"+(allinv!=1?"s":"");
                fee(lbycn("countDisplay"),c=>{c.textContent=dispTxt;});
            };
        },30);
    } else notCancelledInvoice((jobj&&jobj.result)?jobj.result:"SIN RESPUESTA",(jobj&&jobj.message)?jobj.message:"SIN MENSAJE:\n"+JSON.stringify(jobj,jsonCircularReplacer()));
}
function notCancelledInvoice(errmsg,respText,extra) {
    console.log("INI notCancelledInvoice "+errmsg+": "+respText+"\nEXTRA: "+JSON.stringify(extra,jsonCircularReplacer()));
    cladd("waitRoll","hidden");
    overlayClose();
    if (errmsg==="error") {
        overlayMessage(getParagraphObject(respText),"ERROR");
    } else {
        overlayMessage(getParagraphObject("No fue posible cancelar la factura"),"ERROR");
    }
}
function addRejectButton(fId) {
    //console.log("INI addRejectButton "+fId);
    const ci=ebyid("cancelInvoice");
    if (ci) {
        const crt = ebyid("cancelReasonTxt");
        const crs = ebyid("cancelReasonSnd");
        if (crt.fId && crt.fId==fId && crs.fId && crt.fId==fId) {
            //console.log("END addRejectButton: cancelInvoice exists");
            return;
        }
        ekil("cancelInvoice");
    }
<?php if ($esSistemas) { ?>
    let ao=ebyid("accept_overlay");
    if (!ao) ao=ebyid("closeButton");
<?php } else { ?>
    const ao=ebyid("accept_overlay");
<?php } ?>
    if (ao) {
        ao.parentNode.insertBefore(ecrea({eName:"DIV",id:"cancelInvoice",eChilds:[{eName:"input",type:"button",value:"Rechazar",className:"marginV1",onclick:function(event){
            clrem("cancelReasonBlk","hidden");
            setTimeout(()=>{ebyid("<?= $esSistemas?"cancelUserName":"cancelReasonTxt" ?>").focus();},10);
        }},{eName:"DIV",id:"cancelReasonBlk",className:"hidden",eChilds:[<?= $esSistemas?"{eName:\"SPAN\",id:\"cancelUserCap\",eText:\"USUARIO:\"},{eName:\"INPUT\",type:\"text\",id:\"cancelUserName\"},{eName:\"BR\"},":"" ?>{eName:"DIV",id:"cancelReasonCap",eText:"MOTIVO DE RECHAZO:"},{eName:"INPUT",type:"text",id:"cancelReasonTxt",fId:fId,placeholder:"Se requiere motivo para rechazar",onkeypress:(event)=>{enterExit(event,cancelingInvoice);}},{eName:"BR"},{eName:"INPUT",type:"button",id:"cancelReasonBkw",value:"Cancelar",onclick:()=>{cladd('cancelReasonBlk','hidden');}},{eName:"INPUT",type:"button",id:"cancelReasonSnd",value:"Enviar",fId:fId,onclick:cancelingInvoice}]}]}),ao);
<?php if ($esSistemas) { /* toDo: Habilitar para Administradores. En lugar de campo de texto mostrar lista de usuarios validos, usuario actual preseleccionado, en log se registra usuario real y usuario seleccionado */ ?>
        setTimeout(()=>{
            const nci=ebyid("cancelInvoice");
            const ncrt=ebyid("cancelReasonTxt");
            const ncrs=ebyid("cancelReasonSnd");
            if (ncrt.fId!==ncrs.fId) {
                alert("Registro a verificar inconsistente, seleccionar nuevamente");
                overlayClose();
                return;
            }
            nci.setAttribute("fId",ncrs.fId);
        },10);
<?php } ?>
    } else {
        setTimeout(addRejectButton,50,fId);
    }
}
function agregaValorPost(verifElem) {
    const ovy=ebyid("overlay");
    if (!ovy.data) ovy.data={};
    const name="f_"+verifElem.name;
    const oldn="fold_"+verifElem.name;
    if(!(oldn in ovy.data)) ovy.data[oldn]=verifElem.defaultValue;
    if (verifElem.removeSpaces||verifElem.getAttribute("removeSpaces")) verifElem.value=verifElem.value.replace(/\s/g, "");
    ovy.data[name]=verifElem.value;
    //console.log("INI agregaValorPost => "+(ovy.data?JSON.stringify(ovy.data,jsonCircularReplacer()):"NULL"));
}
function agregaDatoPost(name, value) {
    const ovy=ebyid("overlay");
    if (!ovy.data) ovy.data={};
    ovy.data[name]=value;
}
function clearCheckbox(errmsg,respTxt,extra) {
    //console.log("INI clearCheckbox:"+errmsg+"\nRSP clearCheckbox:"+respTxt+"\nXTR clearCheckbox:"+JSON.stringify(extra,jsonCircularReplacer()));
    const xh=(extra&&extra.parameters&&extra.parameters.xmlHttpPost)?extra.parameters.xmlHttpPost:false;
    if (xh&&xh.checkbox) { xh.checkbox=false; delete xh.checkbox; }
    // ToDo: Agregar a Dialogbox position:relative, anexar subDiv semitransparente del 100% ancho y alto que lo tape completamente. Poner mensaje de error centrado v/h y un tache para cerrar, pero cualquier click sobre el subDiv lo elimina y dialogbox regresa a position:static. Mejor crear metodo en general.js que realice todo esto: function overlayError(message)
}
function verificaFactura(factId<?=$modificaProc?",esEditable=false":""?>) {
    //console.log("INI function verificaFactura "+factId<?=$modificaProc?"+(esEditable?\" EDITABLE\":\" SOLO LECTURA\")":""?>);
    const xmlhttp = ajaxRequest();
    const ovy=ebyid("overlay");
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            const vTitle="VALIDACI&Oacute;N DE FACTURA";
            const chk=xmlhttp.checkbox;
            if (chk) { xmlhttp.checkbox=false; delete xmlhttp.checkbox; }
<?php if($modificaProc) { ?>
            if (esEditable) {
                overlayValidation(xmlhttp.responseText, vTitle, function() {
                    if(!ovy.data) ovy.data={};
                    ovy.data.accion="acceptInvoice";
                    ovy.data.id=factId;
                    postService("consultas/Facturas.php", ovy.data, getPostRetFunc(function(jobj,extra){
                        //console.log("CONFIRMAR. JOBJ="+JSON.stringify(jobj,jsonCircularReplacer())+", EXTRA="+JSON.stringify(extra,jsonCircularReplacer()));
                        if (jobj.result==="success") {
                            ebyid("stt"+factId).textContent=jobj.status;
                            ebyid("pdd"+factId).textContent=jobj.pedido;
                            if (chk) {
                                clrem(chk,"pnd");
                                chk.setAttribute("sttn",jobj.statusn);
                                //const chkall=ebyid("checkall");
                                if (!chk.checked) { // && chkall && chkall.checked
                                    chk.checked=true;
                                    toggleChk(chk);
                                }
                            } else {
                                clrem("chk"+factId,"pnd");
                                ebyid("chk"+factId).setAttribute("sttn",jobj.statusn);
                            }
                            overlayClose();
                        }
                        console.log("VALIDACION NO REALIZADA: "+JSON.stringify(jobj,jsonCircularReplacer())); // Mientras solo se muestra en log
                        //if (jobj.result!=="ignore") {
                            // overlayMessage(getParagraphObject(jobj.message),jobj.result.toUpperCase());
                            // ToDo: En lugar de overlayMessage hacer lo mismo que en clearCheckbox
                        //}
                    },clearCheckbox), getPostErrFunc(clearCheckbox,{file:"scripts/generacontra.php",function:"verificaFactura",factId:factId,accion:"acceptInvoice"}));
                    return false;
                });
                const accBtn=ebyid("accept_overlay");
                if (accBtn) {
                    accBtn.value="Confirmar";
                } else {
                    var accIId=setInterval(()=>{
                        const ao=ebyid("accept_overlay");
                        if (ao) {
                            ao.value="Confirmar";
                            clearInterval(accIId);
                        }
                    },50);
                }
            } else
<?php } ?>

            overlayMessage(xmlhttp.responseText, vTitle);
            setTimeout(function(){
                const divScr=ebyid("table_of_concepts").parentNode;
                const cel2=divScr.parentNode;
                const cel1=cel2.previousElementSibling;
                const ovWid=ebyid("overlay").offsetWidth;
                const bxWid = ovWid-44;
                let c1Wid = cel1.offsetWidth;
                const c2Wid = bxWid-c1Wid-6-6;
                c1Wid-=6;
                ebyid("dialogbox").style.width=bxWid+"px";
                ebyid("dialog_resultarea").style.width=bxWid+"px";
                ebyid("tabla_valida_pedido").style.width=bxWid+"px";
                cel1.style.width=c1Wid+"px";
                cel2.style.width=c2Wid+"px";
                divScr.style.width=c2Wid+"px";
                const cb=ebyid("closeButton");
                if (cb) cb.restoreOnClose=()=>{
                    ekil(ebyid("cancelInvoice"));
                    ovy.data=null; delete ovy.data;
                    console.log("VERIFICAFACTURA.closeButton.restoreOnClose ovy.data ERASED!");
                };
            },10);
        }
    };
    xmlhttp.open("GET","selectores/verificafactura.php?facturaId="+factId+<?=$modificaProc?"(esEditable?\"\":\"&readonly\")":"\"&readonly\""?>, true);
    xmlhttp.onerror=function(evt) {
        console.log("ERROR: "+xmlhttp.responseText.length+" | "+xmlhttp.statusText+", EVENT:",evt);
        if (xmlhttp.checkbox) { xmlhttp.checkbox=null; delete xmlhttp.checkbox; }
        //if (ovy./data) { ovy./data=null; delete ovy./data; }
    };
    xmlhttp.ontimeout = function(evt) {
        console.log("TIMEOUT: "+xmlhttp.responseText.length+" | "+xmlhttp.statusText+", EVENT:",evt);
        if (xmlhttp.checkbox) { xmlhttp.checkbox=null; delete xmlhttp.checkbox; }
        //if (ovy./data) { ovy./data=null; delete ovy./data; }
    };
    xmlhttp.onprogress = function(evt) {
        console.log("PROGRESS: "+xmlhttp.responseText.length+" | "+xmlhttp.statusText+", EVENT:",evt);
    }
    xmlhttp.send();
    return xmlhttp;
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
function dateIniSet() {
    //console.log("function dateIniSet");
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(prev_month(day));
}
function dateEndSet() {
    //console.log("function dateEndSet");
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(next_month(day));
}
function setFullMonth(date) {
    var firstDay = first_of_month(date);
    var lastDay = day_before(first_of_month(next_month(date)));
    var iniDateElem = document.getElementById("fechaInicio");
    var endDateElem = document.getElementById("fechaFin");
    iniDateElem.value = strftime(date_format, firstDay);
    endDateElem.value = strftime(date_format, lastDay);
    //adjustCalMonImgs();
    adjust_calendar();
}
function adjustCalMonImgs(tgtWdgt) { adjust_calendar(tgtWdgt,false,{freeRange:true}); }
/*function adjustCalMonImgs(tW) { // target widget
    //console.log("INI adjustCalMonImgs",tW);
    const iDE=ebyid("fechaInicio");const eDE=ebyid("fechaFin"); // ini/end date elem
    const isIT=(tW===iDE); const isET=(tW===eDE); // is ini/end target
    const idy=strptime(date_format, iDE.value); // ini day
    const edy=strptime(date_format, eDE.value); // end day
    if (isIT||isET) {
        if (idy>edy){
            const oW=isIT?eDE:iDE; // other widget
            if (isIT && idy.getDate()==1) {
                oW.value=strftime(date_format,last_of_month(idy));
            } else if (isET) {
                const lom=last_of_month(edy);
                if (same_day(lom,edy)) {
                    oW.value=strftime(date_format,first_of_month(edy));
                } else {
                    oW.value=tW.value;
                }
            } else {
                oW.value=tW.value;
            }
        } else if (isET) {
            clrem(tW,"clearable");
            return;
        }
        clrem(tW,"clearable");
        let prevMon=isIT?idy.getMonth():edy.getMonth();
        if(prevMon<1) prevMon+=12;
        let nextMon=prevMon+2; if(prevMon>12) prevMon-=12;
        ebyid("calendar_month_prev").className= "calendar_month_"+padDate(prevMon);
        ebyid("calendar_month_next").className= "calendar_month_"+padDate(nextMon);
    }
}*/
function openEAf(id,foil) {
    const ol=ebyid("openEA"+id);
    if (ol.url) {
        //console.log("INI openEA id="+id+",folio='"+foil+"': url(prop)="+ol.url);
        window.open(ol.url,"doc");
    } else if (ol.hasAttribute("url")) {
        const tmpUrl=ol.getAttribute("url"); ol.removeAttribute("url"); ol.url=tmpUrl;
        //console.log("INI openEA id="+id+",folio='"+foil+"': url(attr)="+ol.url);
        window.open(ol.url,"doc");
    } //else console.log("INI openEA id="+id+",folio='"+foil+"': Not found URL!");
}
function addEAf(id,foil) {
    const el=ebyid("addEA"+id);
    if (clhas(el,"disabled")) {
        //console.log("INI addEA id="+id+",folio='"+foil+"': DISABLED!");
        return;
    }
    let fl=ebyid("fileEA"+id); if (!fl) {
        fl=ecrea({eName:"INPUT",type:"file",id:"fileEA"+id,folio:foil,className:"hidden",accept:".pdf",onchange:sendEAFile});
        //console.log("INI addEAf id="+id+",folio='"+foil+"': NEW fileEA");
        el.parentNode.appendChild(fl);
    } else {
        //console.log("INI addEAf id="+id+",folio='"+foil+"': CLEAN fileEA");
        fl.value="";
    }
    fl.click();
}
function sendEAFile(evt) {
    const tgt=evt.target;const par=tgt.parentNode;const id=tgt.id.slice(6);const foil=tgt.folio;
    //console.log("INI sendEAFile: ",tgt.files);
    const el=ebyid("addEA"+id); cladd(el,"disabled");
<?php
if ($esBloqueaEA) { ?>
    const dl=ebyid("disEA"+id);cladd(dl,"disabled");
<?php
} ?>
    fixEA({id:id,file:tgt.files[0]},"add",(jobj)=>{
        //console.log("sendEAFile->fixEA->result (jobj='"+JSON.stringify(jobj,jsonCircularReplacer())+"')");
        if (jobj.result==="success") { ekfil(par);
            par.appendChild(ecrea({eName:"IMG",src:"imagenes/icons/pdfadobeicon2.png",id:"openEA"+id,className:"btnLt btn20 pointer",title:"Abrir Entrada de Almacén",url:jobj.url,onclick:function(){openEAf(id,foil);}}));
<?php
if ($esBorraDoc) { ?>
            par.appendChild(ecrea({eName:"IMG",src:"imagenes/icons/deleteIcon20.png",id:"delEA"+id,className:"btnLt btn20 marL4 pointer",title:"Eliminar Entrada de Almacén",onclick:function(){delEAf(id,foil);}}));
<?php
} ?>
        } else {
<?php
if ($esBloqueaEA) { ?>
            clrem(dl,"disabled");
<?php
} ?>
            if (typeof jobj.ea !== "undefined") {
                if (jobj.ea==="-1") {
                    cladd(el,"grayscale");
                    clrem(el,"pointer");
<?php
if ($esBloqueaEA) { ?>
                    dl.src="imagenes/icons/siAplica.png";
                    dl.title="Habilitar Entrada de Almacén";
<?php
} ?>
                } else clrem(el,"disabled");
            } else clrem(el,"disabled");
            overlayMessage({eName:"P",className:"padhtt",eText:jobj.message},jobj.result.toUpperCase());
        }
    },(errmsg,respTxt)=>{
        //console.log("sendEAFile->fixEA->error (errmsg='"+errmsg+"', respTxt='"+respTxt+"')");
        overlayMessage({eName:"P",className:"padhtt",eText:errmsg},"Error");
        clrem("addEA"+id,"disabled");
<?php
if ($esBloqueaEA) { ?>
        clrem("disEA"+id,"disabled");
<?php
} ?>
    });
}
<?php
if ($esBorraDoc) { ?>
function delEAf(id,foil) {
    const tgt=ebyid("delEA"+id); const par=tgt.parentNode;
    if (!tgt.motivo) {
        //console.log("INI delEAf id="+id+",folio='"+foil+"': CONFIRMAR...");
        overlayConfirmation(getParagraphObject(["Va a eliminar el documento Entrada de Almacén de la factura "+foil,"Está seguro de querer eliminarlo?"],"marhtt",true),"ELIMINAR DOCUMENTO", ()=>{tgt.motivo="sin motivo";delEAf(id,foil);});
        return;
    }
    const motivo=tgt.motivo; tgt.motivo=false;
    //console.log("INI delEAf id="+id+",folio='"+foil+"',motivo='"+motivo+"'");
    cladd("openEA"+id,"disabled"); cladd("delEA"+id,"disabled");
    fixEA({id:id,motivo:motivo},"del",(jobj)=>{
        //console.log("delEAf->fixEA->result (jobj='"+JSON.stringify(jobj,jsonCircularReplacer())+"')");
        if (jobj.result==="success") { ekfil(par);
            par.appendChild(ecrea({eName:"IMG",src:"imagenes/icons/upRed.png",id:"addEA"+id,className:"btnLt btn20",title:"Anexar Entrada de Almacén",onclick:function(){addEAf(id,foil);}}));
<?php
    if ($esBloqueaEA) { ?>
            par.appendChild(ecrea({eName:"IMG",src:"imagenes/icons/noAplica.png",id:"disEA"+id,className:"btnLt btn20 marL4",title:"Deshabilitar Entrada de Almacén",onclick:function(){disEAf(id,foil);}}));
<?php
    } ?>
        } else {
            overlayMessage({eName:"P",className:"padhtt",eText:jobj.message},jobj.result.toUpperCase());
            clrem("openEA"+id,"disabled"); clrem("delEA"+id,"disabled");
        }
    },(errmsg,respTxt)=>{
        //console.log("delEAf->fixEA->error (errmsg='"+errmsg+"', respTxt='"+respTxt+"')");
        overlayMessage({eName:"P",className:"padhtt",eText:errmsg},"Error");
        clrem("openEA"+id,"disabled"); clrem("delEA"+id,"disabled");
    });
}
<?php
}
if ($esBloqueaEA) { ?>
function disEAf(id,foil) {
    //console.log("INI disEAf id="+id+",folio='"+foil+"'");
    const el=ebyid("addEA"+id); const dl=ebyid("disEA"+id); cladd(el,"disabled"); cladd(dl,"disabled");
    fixEA({id:id,motivo:"sin motivo"},"dis",(jobj)=>{
        //console.log("disEAf->fixEA->result (jobj='"+JSON.stringify(jobj,jsonCircularReplacer())+"')");
        clrem(dl,"disabled");
        if (jobj.result==="success") {
            const beDis=(jobj.ea<0);
            clfix(el,["disabled","grayscale"],beDis);
            clfix(el,"pointer",!beDis)
            dl.src="imagenes/icons/"+(beDis?"si":"no")+"Aplica.png";
            dl.title=(beDis?"H":"Desh")+"abilitar Entrada de Almacén";
        } else {
            if (typeof jobj.ea !== "undefined") {
                if (jobj.ea==="-1") { cladd(el,["disabled","grayscale"]); clrem(el,"pointer"); dl.src="imagenes/icons/siAplica.png"; dl.title="Habilitar Entrada de Almacén"; }
            } else clrem(el,"disabled");
            overlayMessage({eName:"P",className:"padhtt",eText:jobj.message},jobj.result.toUpperCase());
        }
    },(errmsg,respTxt)=>{
        //console.log("disEAf->fixEA->error (errmsg='"+errmsg+"', respTxt='"+respTxt+"')");
        clrem(el,"disabled");
        clrem(dl,"disabled");
    });
}
<?php
} ?>
function fixEA(props,act,func,nofunc) {
    if (act==="open") {
        //console.log("Falta especificar archivo a abrir");
    } else {
        const xhp=postService("consultas/Facturas.php", Object.assign(props,{accion:act+"WHEntry"}), getPostRetFunc(function(jobj){
            if (func) func(jobj);
            //else console.log(jobj.result.toUpperCase()+": "+jobj.message);
        },function(errmsg,respTxt){
            if (nofunc) nofunc(errmsg,respTxt);
            //else console.log("EXCEP:"+errmsg,"RESP:"+respTxt);
        }), getPostErrFunc(function(errmsg,respTxt){
            if (nofunc) nofunc(errmsg,respTxt);
            //else console.log("ERROR:"+errmsg,"RESP:"+respTxt);
        },{file:"scripts/generacontra.php",function:"fixEA",props:props,act:act}));
    }
}
<?php
clog1seq(-1);
clog2end("scripts.generacontra");
