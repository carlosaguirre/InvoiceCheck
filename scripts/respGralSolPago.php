<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*    if(!hasUser()) {
    die("Empty File");
}*/
$esUsuario = hasUser();
$esDesarrollo = $esUsuario && getUser()->nombre==="admin";
$esAdmin = validaPerfil("Administrador");
$esSistemas = $esAdmin||validaPerfil("Sistemas");
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.respGralSolPago");
clog1seq(1);
?>
var invoiceOrigin=window.location.origin+"/invoice/";
function initObs() {
    const oo=ebyid("observaciones");
    oo.oldvalue=oo.value;
    oo.sid=oo.getAttribute("sid");
    oo.cansave=true;
    countObs();
}
function countObs() {
    const oo=ebyid("observaciones");
    const txt=oo.value.replace(/&#\d+;/gm, function(s) {
        return String.fromCharCode(s.match(/\d+/gm)[0]);
    });
    const len=txt.length;
    const oc=ebyid("obs_count");
    if (len>0 && len<200)
        oc.innerHTML=""+len+"/200 char";
    else oc.innerHTML="200 char";
    const enc=txt.replace(/[\u00A0-\u9999<>\&]/g, function(i) {
        return '&#'+i.charCodeAt(0)+';';});
    oo.setAttribute("realValue",enc);
    oo.setAttribute("realLength",enc.length);
}
function logObs() {
    ;
}
<?php if ($esUsuario) { ?>
function saveObs() {
    const oo=ebyid("observaciones");
    if (oo.cansave) oo.cansave=false;
    else return;
    console.log("INI function saveObs()");
    if (oo.oldvalue===oo.value) {
        oo.cansave=true;
        console.log("Nada que guardar");
        return;
    }
    let text=oo.value;
    if (text.length>0 && oo.hasAttribute("realValue") && oo.hasAttribute("realLength") && text.length<=(+oo.getAttribute("realLength"))) {
        text=oo.getAttribute("realValue");
    }
    oo.style.backgroundColor="palegoldenrod";
    postService("consultas/Facturas.php",{action:"saveSolObs",solid:oo.sid,text:text},function(msg,pars,state,status) {
        if (state==4&&status==200) {
            // check msg is successful
            // button green for 3 seconds;
            console.log("RESPUESTA RECIBIDA: '"+msg+"'");
            try {
                const jobj=JSON.parse(msg);
                console.log("JSON obj: ",jobj);
                if (jobj.result==="error") {
                    oo.value=oo.oldvalue;
                    oo.style.backgroundColor="salmon";
                }
            } catch (ex) {
                console.log("NO valid JSON: ", ex);
            }
            if (oo.value!==oo.oldvalue) {
                oo.oldvalue=oo.value;
                oo.cansave=true;
                oo.style.backgroundColor="springgreen";
            }
            setTimeout(function(){oo.style.backgroundColor="white";},2000);
            //oo.background=somegreen; timeout:remove background
        } else if (state>4 || status!=200) {
            // button red for 3 seconds;
            console.log("ESTADO DE ERROR EN SERVIDOR: '"+msg+"'");
            oo.value=oo.oldvalue;
            oo.cansave=true;
            oo.style.backgroundColor="salmon";
            setTimeout(function(){oo.style.backgroundColor="white";},2000);
        }
    }, function(errmsg, params, evt) {
        // button red for 3 seconds;
        console.log("RESPUESTA DE ERROR: '"+errmsg+"'");
        oo.value=oo.oldvalue;
        oo.cansave=true;
        oo.style.backgroundColor="salmon";
        setTimeout(function(){oo.style.backgroundColor="white";},2000);
    });
    console.log("INI function guardarObservaciones: ENVIADO");
}
<?php } ?>
function viewBackgroundDocs() {
    console.log("INI function viewBackgroundDocs");
    const id=ebyid("bgdocId");
    const img=ebyid("bgdocImg");
    const path=ebyid("bgdocPath");
    const name=ebyid("bgdocName");
    const exst=ebyid("bgdocExists");
    const title=img.title;//.slice(8);
    overlayMessage("<DIV id=\"pdfviewer_container\"><DIV id=\"pdfv_canvas_container\"><CANVAS id=\"pdfv_renderer\"></CANVAS></DIV><DIV id=\"pdfv_controls\"><DIV id=\"pdfv_zoom_controls\"><IMG id=\"pdfv_refresh\" src=\"imagenes/icons/refresh.png\" onclick=\"reloadPDF();\" class=\"pdfvBtn\"/><IMG id=\"pdfv_zoom_in\" src=\"imagenes/icons/zoomIn.png\" title=\"120%\" onclick=\"changeZoom(0.2);\" class=\"pdfvBtn\"/><IMG id=\"pdfv_zoom_out\" src=\"imagenes/icons/zoomOut.png\" title=\"80%\" onclick=\"changeZoom(-0.2);\" class=\"pdfvBtn\"/></DIV><DIV id=\"pdfv_navigation_controls\"><IMG id=\"pdfv_go_previous\" src=\"imagenes/icons/prevPageE20.png\" onclick=\"changePage(-1);\" class=\"pdfvBtn\"/><INPUT id=\"pdfv_current_page\" value=\"1\" min=\"1\" type=\"number\" onkeypress=\"setPage(event);\"/><IMG id=\"pdfv_go_next\" src=\"imagenes/icons/nextPageE20.png\" onclick=\"changePage(+1);\" class=\"pdfvBtn\"></DIV><DIV id=\"pdfv_clear_controls\"></DIV></DIV></DIV><DIV id=\"pdfv_alter_container\"><DIV id=\"pdfv_append_container\"><B>Agregar PDF: </B><LABEL class=\"btnEdg\" for=\"pdfv_new_file\" id=\"pdfv_new_label\">Elegir archivo</LABEL><INPUT id=\"pdfv_new_file\" type=\"file\" accept=\".pdf\" onchange=\"checkBGDocs();\"/> <BUTTON onclick=\"saveBGDocs();\">Enviar</BUTTON></DIV><DIV id=\"pdfv_remove_container\"><B id=\"pdfv_label_remcont\">Eliminar Hoja: </B><SELECT id=\"pdfv_remove_list\" onchange=\"listRemoveBGDocs();\" style=\"height:20.8px;margin-top:-1px;\"><OPTION>Actual</OPTION><OPTION>Hasta</OPTION><OPTION>Todas</OPTION></SELECT> <INPUT id=\"pdfv_to_page\" value=\"1\" min=\"1\" type=\"number\" class=\"hidden\"/> <BUTTON onclick=\"removeBGDocs();\">Enviar</BUTTON></DIV></DIV><DIV id=\"pdfv_message\"></DIV>",title);
    pdfViewerState.renderElementId="pdfv_renderer";
    pdfViewerState.currentPageId="pdfv_current_page";
    pdfViewerState.zoomInId="pdfv_zoom_in";
    pdfViewerState.zoomOutId="pdfv_zoom_out";
    pdfViewerState.prevPgId="pdfv_go_previous";
    pdfViewerState.nextPgId="pdfv_go_next";
    pdfViewerState.tries=1;
    pdfViewerState.timeoutDelay=3000;
    if (exst.value==="1") {
        const pdfRenderEl=ebyid("pdfv_renderer");
        pdfRenderEl.onrender=function() {
            console.log("In onrender function");
            clrem(ebyid("pdfviewer_container"),"hidden");
            clrem(ebyid("pdfv_remove_container"),"hidden");
            adjustDialogBoxHeight();
            pdfRenderEl.onrender=null;
            const cba=ebyid("closeButtonArea");
            const cb=ebyid("closeButton");
            ekfil(cba,[cb]);
            cba.insertBefore(ecrea({eName:"INPUT",type:"button",id:"bgdPrintButton",value:"Imprimir",onclick:function(event){submitPrint();}}), cb);
            cba.insertBefore(ecrea({eText:" "}), cb);
            document.onkeydown=bgKeyCheck;
            console.log("End onrender");
        }
        pdfRenderEl.onfailrender=function(msg) {
            //location.reload(true);
            closeBackdrop(true);
            const msgEl=ebyid("pdfv_message");
            msgEl.appendChild(ecrea({eName:"P","eText":msg}));
            msgEl.canClear=true;
            //pdfRenderEl.onrender=null;
            //pdfRenderEl.onfailrender=null;
        }
        console.log("InvoiceOrigin="+invoiceOrigin);
        console.log("Path="+path.value);
        console.log("Name="+name.value);
        viewPDF(invoiceOrigin+path.value+name.value+".pdf");
    } else {
        clearCanvas();
        cladd(ebyid("pdfviewer_container"),"hidden");
        cladd(ebyid("pdfv_remove_container"),"hidden");
    }
}
function reloadPDF() {
    const path=ebyid("bgdocPath");
    const name=ebyid("bgdocName");
    const exst=ebyid("bgdocExists");
    console.log("InvoiceOrigin="+invoiceOrigin);
    console.log("Path="+path.value);
    console.log("Name="+name.value);
    if (exst.value==="1") {
        viewPDF(invoiceOrigin+path.value+name.value+".pdf");
    }
}
function checkBGDocs() {
    const pdfEl=ebyid('pdfv_new_file');
    const msgEl=ebyid('pdfv_message');
    const pdfLb=ebyid("pdfv_new_label");
    msgEl.textContent="";
    pdfLb.title="";
    clrem(msgEl,"errorLabel");
    let msg="";
    if (pdfEl&&pdfEl.files) fee(pdfEl.files, (f,idx) => {
        if (f.type!=="application/pdf") {
            msg="El archivo '"+f.name+"' no tiene el formato requerido (PDF)";
            console.log(""+idx+": "+msg);
            msgEl.appendChild(ecrea({"eName":"P","eText":msg}));
            cladd(msgEl,"errorLabel");
        } else if (f.size>2097000) {
            msg="El archivo '"+f.name+"' excede el tamaño máximo permitido (2MB)";
            console.log(""+idx+": "+msg);
            msgEl.appendChild(ecrea({"eName":"P","eText":msg}));
            cladd(msgEl,"errorLabel");
        }
        if (clhas(msgEl,"errorLabel")) {
            pdfEl.value="";
            console.log("Error: "+msgEl.textContent);
        } else {
            console.log("Ready to send");
            pdfLb.textContent=f.name;
            pdfLb.title=f.name;
        }
    });
}
function saveBGDocs() {
    // console.log("INI function saveBGDocs");
    const pdfEl=ebyid('pdfv_new_file');
    const msgEl=ebyid('pdfv_message');
    if (pdfEl&&pdfEl.files&&pdfEl.files.length>0) {
        if (msgEl.textContent.length==0||msgEl.canClear) {
            msgEl.textContent="";
            msgEl.canClear=false;
            clrem(msgEl,"errorLabel");
            const num=pdfEl.files.length;
            const plu=(num==1?"":"S");
            console.log("SENDING "+num+" FILE"+plu+"...");
            const url="/invoice/consultas/Archivos.php";
            const parameters={action:"appendBackgroundDocs",prevPage:pdfViewerState.currentPage,file:pdfEl.files[0]};
            const bgDocIdEl=ebyid("bgdocId");
            const bgDocPathEl=ebyid("bgdocPath");
            const bgDocNameEl=ebyid("bgdocName");
            const bgDocExistsEl=ebyid("bgdocExists");
            let readyToSubmit=false;
            if (bgDocIdEl && bgDocIdEl.value.length>0) {
                parameters.solid=bgDocIdEl.value;
                readyToSubmit=true;
            }
            if (readyToSubmit) {
                backdropCloseable=false;
                showBackdropChild({eName:"IMG",src:"imagenes/icons/rollwait2.gif",id:"waitCentered",style:true,onclick:true});
                postService(url,parameters,refreshBGDocs,failedRefreshBGDocs);
            } else {
                msgEl.canClear=true;
                msgEl.textContent="Datos incompletos";
            }
        } else console.log(msgEl.textContent);
    } else {
        msgEl.textContent="Falta elegir un archivo PDF";
        cladd(msgEl,"errorLabel");
        console.log(msgEl.textContent);
    }
    // console.log("END function saveBGDocs");
}
function listRemoveBGDocs() {
    const prl=ebyid("pdfv_remove_list");
    //console.log("INI function listRemoveBGDocs "+prl.value);
    const ptp=ebyid("pdfv_to_page");
    const lr=ebyid("pdfv_label_remcont");
    if (prl.value==="Actual")
        lr.textContent="Eliminar Hoja: ";
    else lr.textContent="Eliminar Hojas: ";
    clset(ptp, "hidden", prl.value!=="Hasta");
    if (!clhas(ptp,"hidden")) {
        ptp.max=pdfViewerState.pdf._pdfInfo.numPages;
    }
    // console.log("END function listRemoveBGDocs");
}
function removeBGDocs(confirm) {
    console.log("INI function removeBGDocs "+(confirm?"CONFIRMED":""));
    const prl=ebyid("pdfv_remove_list");
    const pcp=ebyid("pdfv_current_page");
    const ptp=ebyid("pdfv_to_page");
    if (!confirm) {
        let msg="";
        switch(prl.value) {
            case "Actual": msg="Se eliminará la página "+pcp.value; break;
            case "Hasta":
                const hcp=pcp.valueAsNumber;
                const htp=ptp.valueAsNumber;
                msg="Se quitará";
                if (hcp==htp) msg+=" la página "+hcp;
                else msg+="n las páginas "+Math.min(hcp,htp)+" a "+Math.max(hcp,htp);
                break;
            case "Todas": msg="Se eliminará todo el documento";
        }
        backdropConfirm(msg,null,200,85,function(){removeBGDocs(true);closeBackdrop(true);});
    } else {
        const bgDocIdEl=ebyid("bgdocId");
        const url="/invoice/consultas/Archivos.php";
        const parameters={action:"removeBackgroundPages", type:prl.value,solid:bgDocIdEl.value,currPage:pdfViewerState.currentPage,lastPage:pdfViewerState.pdf._pdfInfo.numPages};
        switch(prl.value) {
            case "Actual":
            parameters.iniPage=pcp.valueAsNumber;
            parameters.endPage=pcp.valueAsNumber;
            break;
            case "Hasta":
            const vcp=pcp.valueAsNumber;
            const vtp=ptp.valueAsNumber;
            parameters.iniPage=Math.min(vcp,vtp);
            parameters.endPage=Math.max(vcp,vtp);
            break;
            case "Todas":
            parameters.iniPage=1;
            parameters.endPage=pdfViewerState.pdf._pdfInfo.numPages;
        }
        if (parameters.iniPage) {
            postService(url,parameters,refreshBGDocs,failedRefreshBGDocs);
        }
        console.log("READY TO DELETE: ",parameters);
        closeBackdrop(true);
    }
    // console.log("END function removeBGDocs");
}
function refreshBGDocs(msg,pars,state,status) {
    const pdfEl=ebyid('pdfv_new_file');
    const msgEl=ebyid('pdfv_message');
    if (state==4 && status==200) {
        if (msg.length>0) try {
            const jobj=JSON.parse(msg);
            if (jobj.result) {
                if (jobj.result==="refresh") {
                    location.reload(true);
                } else if (jobj.result==="success") {
                    console.log("SUCCESS!",jobj);
                    if (jobj.path && jobj.name) {
                        if (jobj.currentPage) pdfViewerState.currentPage=jobj.currentPage;
                        const cpg=pdfViewerState.currentPage;
                        const pdfRenderEl=ebyid("pdfv_renderer");
                        pdfRenderEl.onrender=function() {
                            console.log("In refreshBGDocs.onrender function");
                            closeBackdrop(true);
                            pdfRenderEl.onrender=null;
                            const ptp=ebyid("pdfv_to_page");
                            if (!clhas(ptp,"hidden")) {
                                ptp.max=pdfViewerState.pdf._pdfInfo.numPages;
                            }
                            console.log("End refreshBGDocs.onrender");
                        }
                        viewPDF(invoiceOrigin+jobj.path+jobj.name+".pdf", cpg); // toDo: Agregar argumento exception callback para tratar las excepciones, por ejemplo para esperar un tiempo y volver a intentar
                        const path=ebyid("bgdocPath");
                        const name=ebyid("bgdocName");
                        const exst=ebyid("bgdocExists");
                        path.value=jobj.path;
                        name.value=jobj.name;
                        exst.value="1";
                        clrem(ebyid("pdfviewer_container"),"hidden");
                        clrem(ebyid("pdfv_remove_container"),"hidden");
                    } else {
                        closeBackdrop(true);
                        // msgEl.textContent="No se recibió documento.";
                        clearCanvas();
                        cladd(ebyid("pdfviewer_container"),"hidden");
                        cladd(ebyid("pdfv_remove_container"),"hidden");
                        adjustDialogBoxHeight();
                        console.log("PARAMETERS:",pars,"MESSAGE:",msg);
                    }
                } else {
                    if (jobj.action && jobj.action==="reloadpdf") {
                        const path=ebyid("bgdocPath");
                        const name=ebyid("bgdocName");
                        const exst=ebyid("bgdocExists");
                        if (jobj.name) name.value=jobj.name;
                        if (jobj.path) path.value=jobj.path;
                        pdfViewerState.tries=1;
                        pdfViewerState.timeoutDelay=3000;
                        pdfViewerState.currentPage=1;
                        const pdfRenderEl=ebyid("pdfv_renderer");
                        pdfRenderEl.onrender=function() {
                            console.log("In onrender function");
                            adjustDialogBoxHeight();
                            pdfRenderEl.onrender=null;
                            console.log("End onrender");
                        }
                        pdfRenderEl.onfailrender=function(msg) {
                            closeBackdrop(true);
                            const msgEl=ebyid("pdfv_message");
                            msgEl.appendChild(ecrea({eName:"P","eText":msg}));
                            msgEl.canClear=true;
                        }
                        viewPDF(invoiceOrigin+path.value+name.value+".pdf");
                    } else if (jobj.action && jobj.action==="clearpdf") {
                        clearCanvas();
                        cladd(ebyid("pdfviewer_container"),"hidden");
                        cladd(ebyid("pdfv_remove_container"),"hidden");
                        adjustDialogBoxHeight();
                    }
                    closeBackdrop(true);
                    msgEl.textContent=jobj.result.toUpperCase()+(jobj.message?": "+jobj.message:".");
                    console.log("PARAMETERS:",pars,"RESULT:",jobj);
                }
            } else {
                closeBackdrop(true);
                pdfEl.value=""; // msgEl.canClear=true;
                msgEl.textContent="No fue posible mostrar el documento. Consulte a su administrador.";
                console.log("PARAMETERS:",pars,"MESSAGE:",msg);
            }
        } catch (ex) {
            closeBackdrop(true);
            pdfEl.value=""; // msgEl.canClear=true;
            msgEl.textContent="No fue posible mostrar el documento. Consulte a su administrador: ",(ex.getMessage?ex.getMessage():(ex.message?ex.message:ex));
            console.log("PARAMETERS:",pars,"MESSAGE:",msg,"ERROR:",ex);
        }
        backdropCloseable=true;
    } else if (state>4 || status!=200) {
        closeBackdrop(true);
        backdropCloseable=true;
        pdfEl.value=""; // msgEl.canClear=true;
        msgEl.textContent="No fue posible mostrar el documento. Consulte a su administrador. State: "+state+", Status: "+status;
        console.log("PARAMETERS:",pars,"MESSAGE:",msg);
    }
}
function failedRefreshBGDocs(errmsg, pars, evt) {
    const pdfEl=ebyid('pdfv_new_file');
    const msgEl=ebyid('pdfv_message');
    closeBackdrop(true);
    backdropCloseable=true;
    pdfEl.value=""; // msgEl.canClear=true;
    msgEl.textContent="No fue posible anexar el documento. Consulte a su administrador.";
    console.log("PARAMETERS:",pars,"MESSAGE:",errmsg,"EVENT:",evt);
}
function bgKeyCheck(evt) {
    //console.log("INI bgKeyCheck");
    const exst=ebyid("bgdocExists");
    const bpd=ebyid("bgdPrintButton");
    if (exst.value==="1" && bpd && window.getComputedStyle(bpd).display!=="none" && evt.ctrlKey && evt.keyCode==80) {
        submitPrint();
        return eventCancel(evt);
    }
    return true;
}
function rompeSelloCR(elem, idx) {
    console.log("INI rompeSelloCR ",elem,idx);
    elem.onclick=null;
    elem.firstElementChild.src='imagenes/icons/crDoc32.png';
    window.setTimeout((e,i)=>{
        e.href=e.href.slice(0,i);
    },300,elem,idx);
}
function rompeSello(solId) { console.log("INI rompeSello "+solId);
    const stO=ebyid("stampO");
    if (stO) {
        if (stO.breaking) { console.log("END rompeSello O"+stO.breaking); return; }
        stO.breaking=true;
    }
    const stF=ebyid("stampF");
    if (stF) {
        if (stF.breaking) { console.log("END rompeSello F"+stF.breaking); return; }
        stF.breaking=true;
    }
    postService("consultas/Archivos.php",{action:"rompeSello",solId:solId},getPostRetFunc(function(jobj,extra) {
        console.log("JOBJ: "+JSON.stringify(jobj, jsonCircularReplacer()));
        if (jobj.result && jobj.result==="success") {
            const delDt=AddMinutesToDate(new Date(),5); const delHr=("0"+delDt.getHours()).slice(-2);
            const delMn=("0"+delDt.getMinutes()).slice(-2); const delTm=delHr+":"+delMn;
            const stmO=ebyid("stampO"); if (stmO) stmO.breaking=delTm;
            const stmF=ebyid("stampF"); if (stmF) stmF.breaking=delTm;
            overlayMessage(getParagraphObject("El archivo sellado será eliminado en 5 minutos ("+delTm+")"),"Romper Sello");
            setTimeout(function (solId2) {
                console.log("TIEMPO TRANSCURRIDO. SE ELIMINA ARCHIVO SELLADO");
                const stampO=ebyid("stampO");
                if (stampO) {
                    const prevSO=stampO.previousElementSibling;
                    while(stampO.previousSibling!==prevSO) ekil(stampO.previousSibling);
                    ekil(stampO);
<?php
if ($esSistemas) { ?>
                    const nextSO=prevSO.nextSibling;
                    prevSO.parentNode.insertBefore(ecrea({eText:"\u00a0"}),nextSO);
                    prevSO.parentNode.insertBefore(ecrea({eName:"DIV",classlist:"inblock outoff26 round",style:{width:"17.6px",height:"20px"},id:"restampO",eChilds:[{eName:"IMG",src:"imagenes/icons/sello1.png",title:"Recuperar SELLO-PDF",classlist:"rot30",width:"17",height:"12",onclick:function(){recuperaSello(solId2);}}]}),nextSO);
<?php
} ?>
                }
                const stampF=ebyid("stampF");
                if (stampF) {
                    const prevSF=stampF.previousElementSibling;
                    while(stampF.previousSibling!==prevSF) ekil(stampF.previousSibling);
                    ekil(stampF);
<?php
if ($esSistemas) { ?>
                    const nextSF=prevSF.nextSibling;
                    prevSF.parentNode.insertBefore(ecrea({eText:"\u00a0"}),nextSF);
                    prevSF.parentNode.insertBefore(ecrea({eName:"DIV",classlist:"inblock outoff26 round",style:{width:"17.6px",height:"20px"},id:"restampF",eChilds:[{eName:"IMG",src:"imagenes/icons/sello1.png",title:"Recuperar SELLO-PDF",classlist:"rot30",width:"17",height:"12",onclick:function(){recuperaSello(solId2);}}]}),nextSF);
<?php
} ?>
                }
                console.log("END rompeSello SUCCESS");
            }, 5*60*1000, jobj.params.solId);
        } else noBrkFunc("NO SUCCESS",JSON.stringify(jobj, jsonCircularReplacer()),extra);
    },noBrkFunc), getPostErrFunc(noBrkFunc));
}
function noBrkFunc(message,response,extra) {
    console.log("INI noBrkFunc "+(message?"message["+message.length+"]":"")+(response?"response["+response.length+"]":"")+(extra?JSON.stringify(extra, jsonCircularReplacer()):""));
    const stO=ebyid("stampO");
    if (stO && stO.breaking) {
        stO.breaking=false;
        delete stO.breaking;
    }
    const stF=ebyid("stampF");
    if (stF && stF.breaking) {
        stF.breaking=false;
        delete stF.breaking;
    }
    console.log("END noBrkFunc\nEND rompeSello FAIL");
}
<?php
if ($esSistemas) { ?>
function recuperaSello(solId) { console.log("INI recuperaSello "+solId);
// Con result=success, se regenera la imagen y link de archivo sellado
// Agregar paso intermedio para mostrar ventana de confirmación indicando que el sello ya fue activado y eliminado posteriormente despues de 5 minutos. Es importante asegurarse de que es necesario y requerido por Julieta (debería ser previamente autorizado por Jaime Lobaton pero es posible que se le haya pasado a Julieta la impresion y que tenga la seguridad de que no lo había impreso antes para evitar que se traspapele)
    const rsO=ebyid("restampO");
    if (rsO) {
        if (rsO.restoring) { console.log("END recuperaSello O"+rsO.restoring); return; }
        rsO.restoring=true;
    }
    const rsF=ebyid("restampF");
    if (rsF) {
        if (rsF.restoring) { console.log("END recuperaSello F"+rsF.restoring); return; }
        rsF.restoring=true;
    }
    overlayConfirmation(getParagraphObject("Se va a reestablecer el PDF con Sello"),"Recuperar Sello",function(){
        postService("consultas/Archivos.php",{action:"recuperaSello",solId:solId},getPostRetFunc(function(jobj,extra){
            console.log("JOBJ: "+JSON.stringify(jobj, jsonCircularReplacer()));
            // toDo: si jobj.result==="success" reconstruir tags (link e imagen), borrar div/imagen para recuperar sello
            if (jobj.result && jobj.result==="success") {
                console.log("END recuperaSello SUCCESS");
                const restampO=ebyid("restampO");
                if (restampO) {
                    const prevRO=restampO.previousElementSibling;
                    while(restampO.previousSibling!==prevRO) ekil(restampO.previousSibling);
                    ekil(restampO);
                    const nextRO=prevRO.nextSibling;
                    prevRO.parentNode.insertBefore(ecrea({eText:"\u00a0"}),nextRO);
                    prevRO.parentNode.insertBefore(ecrea({eName:"A",href:jobj.href,target:"archivo",id:"stampO",onclick:function(){rompeSello(jobj.params.solId);return true;},eChilds:[{eName:"IMG",src:"imagenes/icons/pdf200S3.png",title:"SELLO-PDF",width:"20",height:"20"}]}),nextRO);
                }
                const restampF=ebyid("restampF");
                if (restampF) {
                    const prevRF=restampF.previousElementSibling;
                    while(restampF.previousSibling!==prevRF) ekil(restampF.previousSibling);
                    ekil(restampF);
                    const nextRF=prevRF.nextSibling;
                    prevRF.parentNode.insertBefore(ecrea({eText:"\u00a0"}),nextRF);
                    prevRF.parentNode.insertBefore(ecrea({eName:"A",href:jobj.href,target:"archivo",id:"stampF",onclick:function(){rompeSello(jobj.params.solId);return true;},eChilds:[{eName:"IMG",src:"imagenes/icons/pdf200S3.png",title:"SELLO-PDF",width:"20",height:"20"}]}),nextRF);
                }
            } else noRecFunc("NO SUCCESS",JSON.stringify(jobj, jsonCircularReplacer()),extra);
        },noRecFunc), getPostErrFunc(noRecFunc));
    });
}
function noRecFunc(message,response,extra) {
    console.log("INI noRecFunc "+(message?"message["+message.length+"]":"")+(response?"response["+response.length+"]":"")+(extra?JSON.stringify(extra, jsonCircularReplacer()):""));
    const rsO=ebyid("restampO");
    if (rsO && rsO.restoring) {
        rsO.restoring=false;
        delete rsO.restoring;
    }
    const rsF=ebyid("restampF");
    if (rsF && rsF.restoring) {
        rsF.restoring=false;
        delete rsF.restoring;
    }
    console.log("END noRecFunc\nEND recuperaSello FAIL");
}
function replaceDoc(ev) {
    const tgt=ev.target;
    console.log("INI replaceDoc PATH='"+tgt.path+"', NAME='"+tgt.name+"', ID='"+tgt.id+"'");
    const fileElem=ecrea({
        eName:"INPUT",type:"file",className:"wid0",id:"replaceFile",iid:tgt.id,accept:".pdf",tgt:tgt,onchange:sendReplaceDoc,onblur:function(evt){
            console.log('BLURRED!KILLED!');
            ekil(evt.target);
        }
    });
    fileElem.path=tgt.path;
    fileElem.name=tgt.name;
    tgt.parentNode.appendChild(fileElem);
    fileElem.focus();
    fileElem.click();
}
function sendReplaceDoc(ev) {
    const tgt=ev.target;
    tgt.changed=true;
    const file=tgt.files[0];
    const params={action:"repDoc",file:file,type:"cfdi",path:tgt.path,name:tgt.name};
    if (tgt.iid==="retFPDF") params.fId=ebyid("invDocs").getAttribute("val");
    else if (tgt.iid==="retOPDF") params.oId=ebyid("ordDocs").getAttribute("val");
    else {
        console.log("ERROR: Cambio de archivo PDF no contemplado '"+tgt.iid+"'");
        return;
    }
    console.log("INI sendReplaceDoc: postService: PATH='"+tgt.path+"', NAME='"+tgt.name+"'",file);
    const fileCheck = isValidFile(file,"application/pdf");
    if (fileCheck===false) console.log("Falta indicar un archivo");
    else if (fileCheck!==true) console.log("ERROR: "+fileCheck);
    else {
        console.log("Target=",tgt.tgt);
        const prt=tgt.tgt.parentNode;
        console.log("Parent=",prt);
        clrem(prt,["redFilter","greenFilter"]);
        cladd(prt,"yellowFilter");
        prt.timeoutVar=setInterval((me)=>{clfix(me,"yellowFilter");},500,prt);
        params.target=prt;

        console.log("POST SERVICE consultas/Archivos.php",params);
        postService("consultas/Archivos.php",params,getPostRetFunc(docSent,docNotSent),getPostErrFunc(docNotSent));
    }
}
function docSent(jobj,extra) {
    console.log("INI docSent!",JSON.stringify(jobj, jsonCircularReplacer()), JSON.stringify(extra, jsonCircularReplacer()));
    const tgt=extra.parameters.target;
    if (tgt) {
        clearInterval(tgt.timeoutVar);
        delete tgt.timeoutVar;
        clrem(tgt,"yellowFilter");
        cladd(tgt,"greenFilter");
        tgt.timeoutVar=setInterval((me)=>{clfix(me,"greenFilter");},500,tgt);
        //tgt.timeoutVar=setTimeout((me)=>{delete me.timeoutVar;clrem(me,"greenFilter");},5000,tgt);
        tgt.onmouseover=(evt=>{let t=evt.target;while(t&&t.tagName!=="DIV")t=t.parentNode;console.log("TARGET: ",t);clearInterval(t.timeoutVar);delete t.timeoutVar; delete t.onmouseover;clrem(t,"greenFilter");});
    }
    // alert: Al abrir el archivo presionar F5 para que se actualice
    // agregar fondo verde parpadeante 3 segundos // o filtro verde
    // green
}
function docNotSent(errmsg,respTxt,extra) {
    console.log("INI docNotSent! ERR:'"+errmsg+"', TXT:'"+respTxt+"'",JSON.stringify(extra, jsonCircularReplacer()));
    // agregar fondo rojo parpadeante 3 segundos
    const tgt=extra.parameters.target;
    if (tgt) {
        clearInterval(tgt.timeoutVar);
        delete tgt.timeoutVar;
        clrem(tgt,"yellowFilter");
        cladd(tgt,"redFilter");
        tgt.timeoutVar=setInterval((me)=>{clfix(me,"redFilter");},500,tgt);
        //tgt.timeoutVar=setTimeout((me)=>{delete me.timeoutVar;clrem(me,"redFilter");},5000,tgt);
        tgt.onmouseover=(evt=>{let t=evt.target;while(t&&t.tagName!=="DIV")t=t.parentNode;console.log("TARGET: ",t);clearInterval(t.timeoutVar);delete t.timeoutVar; delete t.onmouseover;clrem(t,"redFilter");});
    }
}
<?php
}
if ($esDesarrollo) { ?>
function addAuthReqButton(idSol,times) {
    if (!times) times=1;
    console.log("INI function addAuthReqButton isSol="+idSol+", times="+times);
    let arb=ebyid("authReqBtn");
    if(arb) console.log("FNC addAuthReqButton "+times+": Button exists!");
    else {
        const ca=ebyid("closeArea");
        if (ca) {
            ca.appendChild(ecrea({eName:"INPUT",type:"button",id:"authReqBtn",value:"AUTORIZAR",className:"marL4",solid:idSol,onclick:ev=>{
                setTimeout(function(tgt) {
                    if (window.opener) {
                        // toDo: if (auth checkbox exists unchecked, check it)
                        window.opener.console.log("AUTHORIZE FROM CHILD "+tgt.solid);
                        window.opener.focus();
                    }
                    window.close();
                }, 50, ev.currentTarget);
            }}));
            console.log("FNC button created authReqBtn");
        } else {
            if (times<=5) {
                console.log("FNC addAuthReqButton "+times+": Close Area Not Found!");
                setTimeout(function(arg1,arg2){addAuthReqButton(arg1,arg2);},50,idSol,++times);
            } else console.log("FNC addAuthReqButton exhausted times. Button will not be created!");
        }
    }
    console.log("END function addAuthReqButton "+times);
}
<?php
} ?>
function submitPrint() {
    console.log("INI submitPrint");
    let bgdFrame=ebyid("bgdFrame");
    const path=ebyid("bgdocPath");
    const name=ebyid("bgdocName");
    const url=invoiceOrigin+path.value+name.value+".pdf";
    ekil(bgdFrame);
    //if (!bgdFrame||!bgdFrame.contentWindow) {
        bgdFrame=ecrea({eName:"IFRAME",src:url,className:"hidden",id:"bgdFrame"});
        document.body.appendChild(bgdFrame);
        console.log("append iframe. src= "+url);
    //} else {
    //    console.log("refresh iframe. src= "+url);
    //    bgdFrame.setAttribute("src",url);
    //}
    bgdFrame.contentWindow.onbeforeprint=function() {
        console.log("iframe before print");
    }
    bgdFrame.contentWindow.print();
    console.log("Sent to print");
    bgdFrame.contentWindow.onbeforeprint=function() {
        console.log("iframe before print");
    }
    bgdFrame.contentWindow.onafterprint=function() {
        console.log("iframe after print");
    }
    bgdFrame.onbeforeprint=function() {
        console.log("bgdFrame before print");
    }
    bgdFrame.onafterprint=function() {
        console.log("bgdFrame after print");
    }
/*
    var el = ebyid("contenedor");
    cladd(el,"extraNoPrint");
    document.body.
    window.print();
    console.log("PRINTING...");
    window.onafterprint=function() {
        console.log("AFTER PRINT");
        var el = document.querySelectorAll('*');
        for (var i=0; i < el.length; i++) {
            if (el[i].tagName!=="CANVAS") {
                clrem(el[i],"extraNoPrint");
            } else clrem(el[i],"printStatic");
        }
    }
*/
/*
    const path=ebyid("bgdocPath");
    const name=ebyid("bgdocName");
    const url=invoiceOrigin+path.value+name.value+".pdf";
    console.log("SUBMIT PRINT! "+url);

    var mywindow = window.open(url);
    //mywindow.print();
    //mywindow.close();
*/
    /*
    setTimeout(function () { console.log("STARTS PRINT DIALOG"); mywindow.print(); }, 500);
    mywindow.onfocus = function () { setTimeout(function () { console.log("WINDOW FOCUS ALLOWS CLOSING"); mywindow.close(); }, 700); }
    */
/*
    mywindow.onafterprint=function() { console.log("AFTER PRINT PDF"); mywindow.close(); };
    window.onafterprint=function() { console.log("AFTER PRINT"); mywindow.close(); };
    mywindow.onfocus = function() { console.log("PDF GOT FOCUS"); };
    mywindow.onblur = function() { console.log("PDF LOST FOCUS"); };
    mywindow.print();
*/
}
//window.onbeforeprint=function() {
//    console.log("window before print");
//};
//window.onafterprint=function() {
//    console.log("window after print");
//}
//if (window.matchMedia) {
//    window.matchMedia("print").addListener(function (mql) {
//        console.log("Media Match Print Listener active!");
//        if (mql.matches) {
            //submitPrint();
//            console.log("match");
//            return false;
//        } else {
//            console.log("nomatch");
//            return false;
//        }
//    });
//}
<?php
clog1seq(-1);
clog2end("scripts.respGralSolPago");
