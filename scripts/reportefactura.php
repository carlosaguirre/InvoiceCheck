<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "configuracion/conPro.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.reportefactura");
clog1seq(1);
$esUsuario = hasUser();
//$esSuperAdmin = $esUsuario && getUser()->nombre==="admin";
//$esAdmin = validaPerfil("Administrador");
//$esSistemas = validaPerfil("Sistemas")||$_esAdministrador;
$esAvance = validaPerfil("Avance")||$_esSistemas;
//$esCompras = validaPerfil("Compras");
//$esProveedor = validaPerfil("Proveedor");
//$esPruebas = $esUsuario && in_array(getUser()->nombre, ["admin","sistemas","test"]);
$modificaProc = modificacionValida("Procesar");
$esRechazante = validaPerfil("Rechaza Aceptadas")||$_esSistemas;
$userId=$esUsuario?getUser()->id:0;
?>
window.onload = loadingScripts;
window.onresize = resizingScripts;
var entidadTipos = ["codigo", "rfc", "razon"];
var showGpo= false, showPrv=false;
var timeOutLoading = 0;
var timeoutCFDI = 0;
var timeOutBrowseUser = 0;
function loadingScripts() {
//    appendLog("START | loadingScripts\n");
//    fillPaginationIndexes();
<?php if ($_esPruebas) { ?>
    logService("MENSAJE DE PRUEBA", {filebase:"logs",otro:"OTRO dato",num:1});
<?php } ?>
}
function resizingScripts() {
//    appendLog("START | resizingScripts\n");
    let ov = ebyid("overlay");
    if (ov) {
//        appendLog("Overlay Visibility: "+ov.style.visibility+"\n");
        ajustaTablaConceptos();
    }
}
function pymExpand() {
    console.log("INI pymExpand");
    const leftSide=lbycn("pymLeftSide");
    const rightSide=lbycn("pymRightSide");
    const sideLength=leftSide.length;
    let leftWidth=0;
    let rightWidth=0;
    for (let i=0; i < sideLength; i++) {
        let lwid=leftSide[i].clientWidth;
        if (leftWidth < lwid) leftWidth=lwid;
        let rwid=rightSide[i].clientWidth;
        if (rightWidth < rwid) rightWidth=rwid;
    }
    const totWidth=leftWidth+rightWidth;
    fee(lbycn("widthToExpand"),function(el) {
        el.style.width=totWidth+"px;";
    });
}
function ajustaStatus(evt) {
    let statusElem=ebyid("status");
//    let hiDateElem=ebyid("fechaRelevante");
<?php if ($_esSistemas && false) { /* Agregar campos en Facturas: Fecha */ ?>
    if (statusElem.value==="Pagado") {
        hiDateElem.textContent="F.Pago";
        hiDateElem.title=null;
    } else {
        hiDateElem.textContent="F.Captura";
        hiDateElem.title="Fecha de Captura en el Portal";
    }
<?php } ?>
}
function ajustaTablaConceptos() {
    let ov = ebyid("overlay");
    if (ov.style.visibility==="visible" && ov.firstElementChild && ov.firstElementChild.id==="dialogbox") {
        //appendLog("Overlay             Width="+ov.offsetWidth+", Height="+ov.offsetHeight+"\n");
        let dbx = ov.firstElementChild;
        let dra = false;
        if (dbx && dbx.firstElementChild && dbx.firstElementChild.id==="close_row") {
            //appendLog("DialogBox           Width="+dbx.offsetWidth+", Height="+dbx.offsetHeight+"\n");
            let xrw = dbx.firstElementChild;
            if (xrw && xrw.nextElementSibling && xrw.nextElementSibling.id==="dialog_resultarea") {
                dra = xrw.nextElementSibling; 
            }
        }
        if (!dra) { 
            dra = ebyid("dialog_resultarea"); 
            //appendLog((dra?"":"Not ")+"Found By Id: dialog_resultarea.\n"); 
        }
        if (dra && dra.firstElementChild && dra.firstElementChild.id==="tabla_valida_pedido") {
            //appendLog("ResultArea          Width="+dra.offsetWidth+", Height="+dra.offsetHeight+"\n");
            let tab = dra.firstElementChild;
            let drw = false;
            if (tab && tab.firstElementChild) {
                //appendLog("Table ValidaPedido  Width="+tab.offsetWidth+", Height="+tab.offsetHeight+"\n");
                if (tab.firstElementChild.tagName==="TBODY") {
                    let tbd = tab.firstElementChild;
                    if (tbd && tbd.firstElementChild && tbd.firstElementChild.tagName==="TR") {
                        drw = tbd.firstElementChild;
                    }
                } else if (tab.firstElementChild.tagName==="TR") {
                    drw = tab.firstElementChild;
                }
            }
            let cel1 = false;
            let cel2 = false;
            if (drw && drw.firstElementChild) {
                cel1 = drw.firstElementChild;
                if (cel1 && cel1.nextElementSibling) {
                    //appendLog("Cell 1 Data         Width="+cel1.offsetWidth+", Height="+cel1.offsetHeight+"\n");
                    cel2 = cel1.nextElementSibling;
                }
            }
            if (cel2 && cel2.firstElementChild) {
                //appendLog("Cell 2 Concepts     Width="+cel2.offsetWidth+", Height="+cel2.offsetHeight+"\n");
                let divScr = cel2.firstElementChild;
                if (divScr && divScr.firstElementChild) {
                    //appendLog("Scrollable Div      Width="+divScr.offsetWidth+", Height="+divScr.offsetHeight+"\n");
                    let tcc = divScr.firstElementChild;
                    //appendLog("Scrollable Table    Width="+tcc.offsetWidth+", Height="+tcc.offsetHeight+"\n");
                    
                    let ovWid = ov.offsetWidth;
                    let dbxWidOld = dbx.offsetWidth; // -40-4
                    let draWidOld = dra.offsetWidth;
                    let tabWidOld = tab.offsetWidth;
                    let c1WidOld = cel1.offsetWidth; // -6
                    let bxWid = ovWid-44;
                    let c1Wid = c1WidOld-6;
                    let c2Wid = bxWid-c1WidOld-6-6;
                    dbx.style.width=bxWid+"px";
                    dra.style.width=bxWid+"px";
                    tab.style.width=bxWid+"px";
                    cel1.style.width=c1Wid+"px";
                    cel2.style.width=c2Wid+"px";
                    divScr.style.width=c2Wid+"px";
                }
            }
        }
    }
}
function pickType(elem) {
          if (!elem || !elem.value) { return; }
          for (let i=0; i< entidadTipos.length; i++) {
              let tipo = entidadTipos[i];
              let selGpo = ebyid("gpot"+tipo);
              let selPrv = ebyid("prvt"+tipo);
              if (elem.value == "t"+tipo) {
                  showGpo = selGpo;
                  showPrv = selPrv;
                  localStorage.setItem("initTipoLista",JSON.stringify(<?=$userId?>));
              } else {
                  selGpo.classList.add("hidden");
                  selPrv.classList.add("hidden");
              }
          }
          if (showGpo) { showGpo.classList.remove("hidden"); }
          if (showPrv) { showPrv.classList.remove("hidden"); }
}
function buscaFacturas() {
    document.forms["repfactform"].command.value="Buscar";
}
function doAlert(idelem) {
    let elem = ebyid(idelem);
    alert(elem);
}
function agregaDatoPost(name, value) {
    let formName = 'repfactform';
    let formElem = ebyid(formName);
    let elem = ebyid(name);
    if (!elem) {
        elem = document.createElement("INPUT");
        elem.type="hidden";
        elem.name=name;
        elem.id=name;
        elem.className="datoPost";
        formElem.appendChild(elem);
    }
    elem.value=value;
}
function agregaValorPost(verifElem) {
    if (verifElem.removeSpaces||verifElem.getAttribute("removeSpaces")) verifElem.value=verifElem.value.replace(/\s/g, "");
    let f_elem = ebyid("f_"+verifElem.name);
    if (f_elem) f_elem.value = verifElem.value;
}
function eliminaDatosPost() {
    // let edpElems = document.getElementsByClassName("datoPost");
    // console.log(edpElems);
    removeElementsByClass("datoPost");
    // let edpElems = document.getElementsByClassName("datoPost");
    // console.log(edpElems);
}
//doShowFuncLogs=true;
function cancelingInvoice(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    ekil("cancelUserList");
    if (!tgt.fId) {
        const blk=ebyid("cancelReasonBlk");
        const cap=ebyid("cancelReasonCap");
        cladd("cancelReasonSnd","hidden");
        cladd("cancelReasonTxt","hidden");
        cladd(blk,"bgredvip2");
        cap.textContent="No es posible rechazar un CFDI sin ID";
        cap.style.width="auto";
        blk.style.textAlign="center";
        return;
    }
    const crt=ebyid("cancelReasonTxt");
    const motivo=crt.value;
    if (motivo.length>0) {
        console.log("INI cancelingInvoice: '"+motivo+"'");
        let parameters={action:"cancelInvInReq",script:"reportefactura",tgtId:tgt.id,invId:tgt.fId,motivo:motivo,accion:"Rechaza"<?= $_esSistemas?",plan:\"b\"":"" ?>};
        const usr=ebyid("cancelUserName");
        if (usr) parameters.username=usr.value;
        // ,fecha:fecha.value
        // ,gpoId:gpoEl.value
        // ,alias:gpoAl
        // ,gpoRS:gpoDt.value
        // ,prvId:prvEl.value
        // ,prCod:prCod
        // ,prvRS:prvDt.value
        // ,folio:folio.value
        postService("consultas/Facturas.php", parameters, getPostRetFunc(cancelledInvoice,notCancelledInvoice), getPostErrFunc(notCancelledInvoice));
        //clfix("cancelReasonBlk","hidden");
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
function cancelledInvoice(jobj,extra) { // docAnexado
    if (jobj.params.xmlHttpPost.readyState!==4) return;
    if (jobj && jobj.result && jobj.result==="success") {
        cladd('cancelReasonBlk','hidden');
        ekil("cancelInvoice");
        cladd("waitRoll","hidden");
        overlayClose();
        overlayMessage(getParagraphObject(jobj.message),"CFDI Rechazado");
        setTimeout(()=>{
            console.log("Preparing CallOnClose");
            const ovy=ebyid("overlay");
            ovy.callOnClose=()=>{
                submitAjax("Buscar",function(ajaxObj1,flag) {
                    console.log("CALLBACK submitAjax Buscar"+(ajaxObj1?". cancelledInvoice:"+ajaxObj1.responseText.length+". "+(flag?" PARTIAL.":" FULL."):""));
                    if (!flag) {
                        cladd("waitRoll","hidden");
                        overlayClose();
                    }
                });
            };
        },30);
    } else {
        if (!jobj) console.log("CANCELLEDINVOICE.RESULT=SIN JOBJ");
        else if (!jobj.result) console.log("CANCELLEDINVOICE.RESULT=SIN RESULT");
        else console.log("CANCELLEDINVOICE.RESULT="+jobj.result);
        console.log("CANCELLEDINVOICE.EXTRA: ",extra);
        notCancelledInvoice((jobj&&jobj.result)?jobj.result:"SIN RESPUESTA",(jobj&&jobj.message)?jobj.message:"SIN MENSAJE:\n"+JSON.stringify(jobj,jsonCircularReplacer()));
    }
}
function notCancelledInvoice(errmsg,respText,extra) {
    cladd("waitRoll","hidden");
    overlayClose();
    if (errmsg==="error") {
        overlayMessage(getParagraphObject(respText),"ERROR");
        //console.log("ERROR: "+respText);
    } else {
        overlayMessage(getParagraphObject("No fue posible cancelar el CFDI"),"ERROR");
        console.log("ERROR: ",errmsg);
        console.log("RESPONSE: ",respText);
        console.log("EXTRA: ",extra);
    }
}
function addRejectButton(fId,times) {
    if (times && times>10) {
        console.log("IGNORE addRejectButton "+fId+": too many tries");
        return;
    }
    console.log("INI addRejectButton "+fId+(times?" (Retry "+times+")":""));
    const ci=ebyid("cancelInvoice");
    if (ci) {
        const crt = ebyid("cancelReasonTxt");
        const crs = ebyid("cancelReasonSnd");
        if (crt.fId && crt.fId==fId && crs.fId && crt.fId==fId) {
            console.log("END addRejectButton: cancelInvoice exists");
            return;
        }
        ekil("cancelInvoice");
    }
<?php if ($_esSistemas) { ?>
    let ao=ebyid("accept_overlay");
    if (!ao) ao=ebyid("closeButton");
<?php } else { ?>
    const ao=ebyid("accept_overlay");
<?php } ?>
    if (ao) {
        ao.parentNode.insertBefore(ecrea({eName:"DIV",id:"cancelInvoice",eChilds:[{eName:"input",type:"button",value:"Rechazar",className:"marginV1",onclick:function(event){
            console.log("Pedir Motivo, Enviar Correo al proveedor. ",event);
            clrem("cancelReasonBlk","hidden");
            setTimeout(()=>{ebyid("<?= $_esSistemas?"cancelUserName":"cancelReasonTxt" ?>").focus();},10);
        }},{eName:"DIV",id:"cancelReasonBlk",className:"hidden",eChilds:[<?= $_esSistemas?"{eName:\"SPAN\",id:\"cancelUserCap\",eText:\"USUARIO:\"},{eName:\"INPUT\",type:\"hidden\",id:\"cancelUserId\"},{eName:\"INPUT\",type:\"text\",id:\"cancelUserName\",onkeyup:browseCancelUser},{eName:\"SPAN\",id:\"cancelUserFullName\"},{eName:\"BR\"},":"" ?>{eName:"DIV",id:"cancelReasonCap",eText:"MOTIVO DE RECHAZO:"},{eName:"INPUT",type:"text",id:"cancelReasonTxt",fId:fId,placeholder:"Se requiere motivo para rechazar",onkeypress:(event)=>{enterExit(event,cancelingInvoice);}},{eName:"BR"},{eName:"INPUT",type:"button",id:"cancelReasonBkw",value:"Cancelar",onclick:()=>{cladd('cancelReasonBlk','hidden');ekil('cancelUserList');}},{eName:"INPUT",type:"button",id:"cancelReasonSnd",value:"Enviar",fId:fId,onclick:cancelingInvoice},{eName:"IMG",src:"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7",onload:evt=>{console.log('Cancel Reason PT LOADED!');ekil(evt.target);}}]}]}),ao);
<?php if ($_esSistemas) { ?>
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
            // ver si puedo poner usuario predefinido en base a datos de la factura
            // agregar dropdown de posibles usuarios a elegir
            // ver si puedo mostrar de alguna forma el nombre de la persona al elegir el nombre de usuario
            // quitar texto si no existe el usuario
            // con un texto parcial mostrar usuarios que empiecen asi
            // ver si debajo puedo mostrar usuarios que contengan el texto parcial que no inicien así
        },10);
<?php } ?>
        console.log("END addRejectButton: cancelInvoice added");
    } else {
        console.log("END addRejectButton: accept_overlay doesnt exist");
        if (times) times++;
        else times=1;
        setTimeout(addRejectButton,15,fId,times);
    }
}
<?php if ($_esSistemas) { ?>
function browseCancelUser(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    if (timeOutBrowseUser) {
        console.log("INI function browseCancelUser "+tgt.id+": cancel action");
        browseCancelUserAbort(tgt);
        clearTimeout(timeOutBrowseUser);
        timeOutBrowseUser=0;
    }
    if (!tgt.previousValue) tgt.previousValue="";
    if (tgt.previousValue!==tgt.value) {
        tgt.previousValue=tgt.value;
        timeOutBrowseUser = setTimeout(elem=>{
            timeOutBrowseUser=0;
            console.log("INI function browseCancelUser "+elem.id+": "+elem.value);
            elem.xhr=readyService("consultas/Usuarios.php", {accion:"browseUserName",nombre:elem.value+"*",sortList:"nombre",exceptions:"'admin','test','test1','test2','test3'"}, browseCancelUserFound, browseCancelUserNotFound);
            elem.xhr.onabort=browseCancelUserAbort;
        },100,tgt);
    } else console.log("INI function browseCancelUser "+tgt.id+": nothing changed");
}
function browseCancelUserAbort(evt) {
    if (!evt) evt = window.event;
    const tgt = isElement(evt)?evt:(evt.target || evt.srcElement);
    const xhr = tgt.xhr?tgt.xhr:false;
    console.log("INI function browseCancelUserAbort state="+(tgt.xhr?tgt.xhr.readyState:'unknown')+", status="+(tgt.xhr?tgt.xhr.status:'unknown')+", elem.id="+tgt.id,tgt);
    ekil("cancelUserList");
}
function browseCancelUserFound(jobj,extra) {
    console.log("SUCCESS browseCancelUserFound "+(jobj?"JOBJ-STATE="+jobj.params.xmlHttpPost.readyState:(extra&&extra.lastJObj?"LASTJSTT="+extra.lastJObj.params.xmlHttpPost.readyState:"NO JOBJ")));
    const prefix=jobj?"J":(extra&&extra.lastJObj?"X":false);
    if (prefix==="X") jobj=extra.lastJObj;
    if (jobj && jobj.result && jobj.result==="success") {
        console.log(prefix+": "+jobj.message,jobj);
        const data=jobj.data;
        if (data.length==0) ekil("cancelUserList");
        else if (data.length==1) {
            ekil("cancelUserList");
            ebyid("cancelUserId").value=data[0].id;
            const cun=ebyid("cancelUserName");
            cun.blur();
            cun.value=data[0].nombre;
            const cufn=ebyid("cancelUserFullName");
            cufn.textContent=data[0].persona;
            cufn.focus();
        } else if (data.length>1) {
            const cun=ebyid("cancelUserName");
            const rct=cun.getBoundingClientRect();
            const cTp = rct.top + (cun.offsetHeight?+cun.offsetHeight:+cun.clientHeight);
            const cLf=rct.left;
            const cWd=rct.right-rct.left;
            let sz=data.length;
            if (sz>3) sz=3;
            const cHg=sz*(rct.bottom-rct.top)+4; // 4 del padding q agrega basicBG
            let cul=ebyid("cancelUserList");
            if (cul) {
                ekfil(cul);
                cul.style.height=cHg+"px";
            } else {
                cul=ecrea({eName:"DIV",id:"cancelUserList",className:"abs block zIdx3k basicBG pointer br1_0 yFlow",style:"top:"+cTp+"px;left:"+cLf+"px;width:"+cWd+"px;height:"+cHg+"px;"});
                document.body.appendChild(cul);
            }
            data.forEach(u=>{cul.appendChild(ecrea({eName:"DIV",eText:u.nombre,title:u.persona,className:"invertHoverBG",usrId:u.id,usrFlNm:u.persona,onclick:evt=>{
                if (!evt) evt = window.event;
                const tgt = evt.target || evt.srcElement;
                ebyid("cancelUserId").value=tgt.usrId;
                ebyid("cancelUserName").value=tgt.textContent;
                ebyid("cancelUserFullName").textContent=tgt.usrFlNm;
                ekil("cancelUserList");
            }}));});
        }
        return true;
    }
    if (prefix==="X") console.log("EMPTY LAST UNSUCCESSFUL RESULT: ",extra);
    browseCancelUserNotFound((jobj&&jobj.result)?jobj.result:"SIN RESPUESTA",(jobj&&jobj.message)?jobj.message:"SIN MENSAJE",(prefix==="J")?JSON.stringify(jobj,jsonCircularReplacer()):extra);
    return false;
}
function browseCancelUserNotFound(errmsg,respText,extra) {
    console.log("BROWSECANCELUSERNOTFOUND ERROR:",errmsg);
    console.log("BROWSECANCELUSERNOTFOUND RESPONSE:",respText);
    console.log("BROWSECANCELUSERNOTFOUND EXTRA:",extra);
}
<?php } ?>
function verificaFactura(factId,isReadonly=false) {
    console.log("INI function verificaFactura "+factId+", "+(isReadonly?"SOLO LECTURA":"LIBRE"));
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            if (isReadonly) overlayMessage(xmlhttp.responseText, 'VALIDACI&Oacute;N DE CFDI');
            else {
                overlayConfirmation(xmlhttp.responseText, 'VALIDACI&Oacute;N DE CFDI', function() {
                    submitAjax("GO"+factId, function (ajaxObj2,flag) {
                        console.log("CALLBACK submitAjax GO"+factId+(ajaxObj2?". verificaFactura():"+ajaxObj2.responseText.length+". "+(flag?" PARTIAL.":" FULL."):""));
                    });
                    console.log('SUBMITTED GO');
                }, false, function() {
                    const err="";
                    const pedidoFld=ebyid("numpedido");
                    if (pedidoFld && pedidoFld.value.length==0) pedidoFld.value="S/PEDIDO";
                    const remisionFld=ebyid("numremision");
                    if (remisionFld && remisionFld.value.length==0) remisionFld.value="S/REMISION";
                    let artFlds=[];
                    if (document.querySelectorAll) {
                        artFlds = document.querySelectorAll('INPUT[id^="articulo"]')
                    } else {
                        const inputFlds=document.getElementsByTagName("INPUT");
                        const artRE=/^articulo/;
                        for (let i=inputFlds.length;i--;) if(artRE.test(inputFlds[i].id)) artFlds.push(inputFlds[i]);
                    }
                    for (let i=0; i < artFlds.length; i++) {
                        let artVal=artFlds[i].value.trim();
                        artFlds[i].value=artVal;
                        if (artVal.length==0) {
                            artFlds[i].focus();
                            const errLbl=ebyid("validationErrorLabel");
                            if (errLbl) {
                                errLbl.textContent="Debe especificar todos los códigos de conceptos";
                                return false;
                            }
                            const cncTab=ebyid("table_of_concepts");
                            if (!cncTab) {
                                alert("Debe especificar todos los códigos de conceptos");
                                return false;
                            }
                            cncTab.parentNode.appendChild(ecrea({eName:"P",id:"validationErrorLabel",className:"errorLabel",eText:"Debe especificar todos los códigos de conceptos"}));
                            return false;
                        }
                        if (/\s/.test(artVal)) { // artVal.indexOf(" ")>0
                            artFlds[i].focus();
                            const errLbl=ebyid("validationErrorLabel");
                            if (errLbl) {
                                errLbl.textContent="El código de concepto no debe incluir espacios";
                                return false;
                            }
                            const cncTab=ebyid("table_of_concepts");
                            if (!cncTab) {
                                alert("El código de concepto no debe incluir espacios");
                                return false;
                            }
                            cncTab.parentNode.appendChild(ecrea({eName:"P",id:"validationErrorLabel",className:"errorLabel",eText:"El código de concepto no debe incluir espacios"}));
                            return false;
                        }
                    }
                    const errLbl=ebyid("validationErrorLabel");
                    if (errLbl) errLbl.textContent="";
                    //console.log("CHECKING INVOICE: AUTO FAIL!");
                    return true;
                });
            }
            const f2d=ebyid("fI2dI"+factId);
            const dI=f2d?f2d.value:false;
            const tcE=dI?ebyid("ftipocomprob"+dI):false;
            const tc=tcE?tcE.value:false;
            console.log("VERIFICANDO CFDI con TC='"+tc+"'");
            if (!tc || tc=="I" || tc=="E") ajustaTablaConceptos();
            const ovy=ebyid("overlay");
            ovy.callOnClose=()=>{
                console.log("INI verificaFactura->overlay.callOnClose");
                const eda=ebyid('eafile');
                if(eda&&!clearFileInput(eda)) console.log("No fue posible limpiar contenido: '"+eda.value+"'",eda.files);
                ekil("cancelInvoice");
                console.log("END verificaFactura->overlay.callOnClose");
            };
        }
    };
    xmlhttp.open("GET","selectores/verificafactura.php?facturaId="+factId+(isReadonly?"&readonly":""), true);
    xmlhttp.send();
    // let content = "<b>Verificaci&oacute;n de factura con ID <u>"+factId+"</u></b><br>\n";
    // let numpedidoNom = "numpedido";
    // let numpedidoVal = "A74";
    // content += "Pedido <input type='text' name='"+numpedidoNom+"' id='"+numpedidoNom+"' value='"+numpedidoVal+"' onchange='agregaValorPost(this);'>";
    // agregaDatoPost("f_"+numpedidoNom, numpedidoVal);
}
function generaFactura(factName,yearCycle,winname) {
    if (!winname) winname='_blank';
    let expUrl='templates/factura.php?nombre='+factName;
    if (yearCycle && yearCycle.length>0) expUrl+='&ciclo='+yearCycle;
    console.log("expUrl: ",expUrl);
    window.open(expUrl,winname);
}
function muestraSolicitud(solId,solFolio) {
    const formElem=ecrea({eName:"FORM",target:"solpago",method:"POST",action:"templates/respuestaSolPago.php",eChilds:[{eName:"INPUT",type:"hidden",name:"SOLID",value:solId},{eName:"INPUT",type:"hidden",name:"SOLFOLIO",value:solFolio},{eName:"INPUT",type:"hidden",name:"ENCABEZADO",value:"SOLICITUD DE PAGO "+solFolio}]});
    document.body.appendChild(formElem);
    window.open("","solpago");
    formElem.submit();
}
var _forma_submitted = false;
var resultDiv = 'dialog_tbody';
var time_begin=0;
var time_lapse=0;
function continueSubmit() { // Continuar descarga a partir del registro donde se quedó.
    console.log("INI continueSubmit");
    // buscaFacturas() => document.forms["repfactform"].command.value="Buscar";
    // Hacer submit de busqueda agregando num de registros descargados
    // No se borrará la tabla, el num de registros seguirá incrementando
}
function submitAjax(submitValue, callbackfunc) {
    try {
    console.log("submitAjax: "+submitValue);
    if (_forma_submitted) return;
    _forma_submitted=true;
    if (submitValue==="Buscar") {
        preLoad();
        if (!callbackfunc) {
            console.log("callback set to search")
            callbackfunc=searchCallbackFunc;
        } else console.log("callback set in parameter");
    }
    let postURL = 'selectores/reportefactura.php';
    let formName = 'repfactform';
    let formElem = document.forms[formName];
    formElem.command.value = submitValue;
    console.log("to ajaxPost: "+postURL+", "+formName+", "+resultDiv+". Command="+formElem.command.value);
    ebyid(resultDiv).innerHTML = "";
    fee(lbycn('colRemision'),el=>cladd(el,'hidden'));
    time_begin=performance.now();
    time_lapse=0;
    const xhr=ajaxPost(postURL, formName, resultDiv, false, callbackfunc);
    xhr.isProgressEnabled=true;
    xhr.inclusiveSeparator="</tr><!-- END ROW -->";
    xhr.hasExternalWait=true;
    xhr.timeoutCallbackFunc=function(evt) {
        const new_lapse=performance.now();
        if (time_lapse>0) console.log("TIMEOUT Lapse duration "+Math.round((new_lapse-time_lapse)/1000)+" seconds.");
        console.log("Stopped by timeout. Total duration "+Math.round((new_lapse-time_begin)/1000)+" seconds.");
        cladd("waitRoll","hidden");
        const stopBtn=ebyid("stopBtn");
        let txt="";
        if (stopBtn) {
            if (stopBtn.xhr) { stopBtn.xhr=null; delete stopBtn.xhr; }
            cladd(stopBtn,"hidden");
            for (let testElem=stopBtn.nextSibling; testElem; testElem=testElem.nextSibling) {
                if(clhas(testElem,"footcore")) continue;
                testElem=testElem.previousSibling;
                testElem.parentNode.removeChild(testElem.nextSibling);
            }
            txt=". Tiempo de Búsqueda Excedido!";
            stopBtn.parentNode.appendChild(document.createTextNode(txt));
            let count=0;
            if (document.querySelectorAll) count=document.querySelectorAll("#"+resultDiv+">TR").length;
            else fee(ebyid(resultDiv).children, function(el){if(el.tagName==="TR")count++;});
            clset("downloadBtn","hidden",count<=0);
            <?php if ($_esSistemas) { ?>
            const nr=ebyid("numRegs");
            const num=+(nr?nr.value:-1);
            clset("continueBtn","hidden",count<=0||num<=0||count>=num);
            clset("extraBtn","hidden",count<=0);
            <?php } ?>
        }
        reLoad(txt);
    };
    xhr.onabort=function(evt) {
        console.log("ABORT state "+xhr.readyState+", status "+xhr.status);
        const new_lapse=performance.now();
        if (time_lapse>0) console.log("ABORT Lapse duration "+Math.round((new_lapse-time_lapse)/1000)+" seconds.");
        console.log("Stopped by abort. Total duration "+Math.round((new_lapse-time_begin)/1000)+" seconds.");
        cladd("waitRoll","hidden");
        const stopBtn=ebyid("stopBtn");
        let txt="";
        if (stopBtn) {
            if (stopBtn.xhr) { stopBtn.xhr=null; delete stopBtn.xhr; }
            cladd(stopBtn,"hidden");
            for (let testElem=stopBtn.nextSibling; testElem; testElem=testElem.nextSibling) {
                if(clhas(testElem,"footcore")) continue;
                testElem=testElem.previousSibling;
                testElem.parentNode.removeChild(testElem.nextSibling);
            }
            txt=". Búsqueda Interrumpida!";
            stopBtn.parentNode.appendChild(document.createTextNode(txt));
            let count=0;
            if (document.querySelectorAll) count=document.querySelectorAll("#"+resultDiv+">TR").length;
            else fee(ebyid(resultDiv).children, function(el){if(el.tagName==="TR")count++;});
            clset("downloadBtn","hidden",count<=0);
            <?php if ($_esSistemas) { ?>
            const nr=ebyid("numRegs");
            const num=+(nr?nr.value:-1);
            clset("continueBtn","hidden",count<=0||num<=0||count>=num);
            clset("extraBtn","hidden",count<=0);
            <?php } ?>
        }
        reLoad(txt);
    };
    console.log("submitAjax DONE!");
    } catch (ex) {
        console.log("EXCEPTION IN submitAjax: ",ex);
    }
    return false;
}
function searchCallbackFunc(xmlHttpPost, isPartialResponse) {
    let count=0;
    try {
    console.log("searchCallbackFunc "+(isPartialResponse?"Partial":"Ended"));
    const new_lapse=performance.now();
    if (time_lapse>0) console.log("SEARCH Lapse duration "+Math.round((new_lapse-time_lapse)/1000)+" seconds.");
    if (isPartialResponse) {
        if (time_lapse==0) console.log("PARTIAL Lapse duration "+Math.round((new_lapse-time_begin)/1000)+" seconds.");
        time_lapse = new_lapse;
    } else {
        console.log("Completed. Total duration "+Math.round((new_lapse-time_begin)/1000)+" seconds.");
    }
    // Agregar modificaciones al recibir respuesta de selector (buscar) reporte facturas
    /*
    const respDiv = ebyid(resultDiv);
    if (respDiv && isPartialResponse && xmlHttpPost.status==200) {
        let respTxt = xmlHttpPost.responseText;
        respDiv.innerHTML = respTxt;
    }
    */
    // ToDo: Reemplazar ajaxPost por postService
    if (xmlHttpPost.responseLength) {
        cladd("waitRoll","hidden");
        fee(lbycn("datatable"), function(elem) { clrem(elem,"hidden"); });
    }
    count=footLoad();
    if (xmlHttpPost.readyState==3 && xmlHttpPost.status==200) {
        if (stopBtn) {
            if (!stopBtn.xhr) stopBtn.xhr=xmlHttpPost;
            clrem(stopBtn,"hidden");
        }
    } else if (xmlHttpPost.readyState>=4) {
        if (stopBtn) {
            stopBtn.xhr=null;
            delete stopBtn.xhr;
            cladd(stopBtn,"hidden");
            clset("downloadBtn","hidden",count<=0);
            <?php if ($_esSistemas) { ?>
            clset("extraBtn","hidden",count<=0);
            <?php } ?>
        }
        if (xmlHttpPost.status >= 200) {
            console.log("submitAjax COMPLETELY FINISHED!");
            _forma_submitted=false;
        }
    }
    } catch (ex) {
        console.log("EXCEPTION IN searchCallbackFunc: ",ex);
    }
    const nr=ebyid("numRegs");
    const num=+(nr?nr.value:-1);
    if (num>=0 && count>num) {
        console.log("COUNT EXCEEDS TOTAL COUNT!!! "+count+" > "+num);
        xmlHttpPost.abort();
    }
}
function preLoad() {
    console.log("previous to Load...");
    cladd(["continueBtn","downloadBtn","extraBtn"],"hidden");
    fee(lbycn("datatable"), function(elem) { elem.classList.add("hidden"); });
    clrem("waitRoll","hidden");
    ekil("toCsvLink");
    ekfil("footer",["stopBtn","downloadBtn"<?= $_esSistemas?",\"continueBtn\",\"extraBtn\"":"" ?>]);
    const tipos=["codigo","rfc","razon"];
    let i=0;
    for (; i < tipos.length; i++) if (ebyid("tipo"+tipos[i]).checked) break;
    if (i < tipos.length) {
        const encEmpr =  ebyid("encEmpresa");
        if (encEmpr) {
            const gpot=ebyid("gpot"+tipos[i]);
            let unaEmpr=(gpot.value.length>0) && !gpot.multiple;
            if (!unaEmpr) {
                const gpoOpts=gpot.options;
                let gpoNum=gpot.length;
                let gpoSelN=0;
                for (let o=0; o < gpoOpts.length; o++) {
                    if (gpoOpts[o].value.length==0) gpoNum--;
                    else if (gpoOpts[o].selected) gpoSelN++;
                }
                if (gpoNum==1||gpoSelN==1) unaEmpr=true;
            }
            clset(encEmpr,"hidden",unaEmpr);
        }
        const encProv=ebyid("encProveedor");
        if (encProv) {
            const prvt=ebyid("prvt"+tipos[i]);
            let unProveedor=(prvt.value.length>0) && !prvt.multiple;
            if (!unProveedor) {
                const prvOpts=prvt.options;
                let prvNum=prvt.length;
                let prvSelN=0;
                for (let p=0; p < prvOpts.length; p++) {
                    if (prvOpts[p].value.length==0) prvNum--;
                    else if (prvOpts[p].selected) prvSelN++;
                }
                if (prvNum==1||prvSelN==1) unProveedor=true;
            }
            clset(encProv,"hidden",unProveedor);
        }
    }
}
function isLoaded(txt) {
    console.log("isLoaded "+txt);
    let encProv = ebyid("encProveedor");
    if (encProv) {
        console.log("encabezado Proveedor");
        let unEmi = ebyid("unEmisor");
        if (unEmi) {
            console.log("Un Emisor: Oculta encabezado Proveedor");
            cladd(encProv,"hidden");
        } else {
            console.log("No encontrado emi: Muestra encabezado Proveedor");
            clrem(encProv,"hidden");
        }
    }
    let encEmp =  ebyid("encEmpresa");
    if (encEmp) {
        console.log("encabezado Empresa");
        let unRec = ebyid("unReceptor");
        if (unRec) {
            console.log("Un Receptor: Oculta encabezado Empresa");
            cladd(encEmp,"hidden");
        } else {
            console.log("No encontrado rec: Muestra encabezado Empresa");
            clrem(encEmp,"hidden");
        }
    }
    reLoad();
}
function reLoad(txt) {
    if (footLoad(txt)>0) {
        fee(lbycn("datatable"),elem=>clrem(elem,"hidden"));
    }
    let fwElems=document.getElementsByClassName("currency");
    let maxWidth=0;
    for(let i=0;i< fwElems.length;i++) maxWidth=Math.max(maxWidth,fwElems[i].offsetWidth);
    for(let i=0;i< fwElems.length;i++) fwElems[i].style.width=maxWidth+"px";

    let rowIndexBlock = document.getElementsByClassName("rowidx");
<?php if ($_esSistemas) { ?>
    /*fee(lbycn("pie_quickSys"),ekil);
    if (rowIndexBlock.length>0) {
        const pEsp=ebyid("pie_space");
        if (pEsp) {
            pEsp.parentNode.insertBefore(ecrea({eName:"DIV", className:"pie_element pie_quickSys", eChilds:[{eName:"IMG", src:"imagenes/icons/process200.png", title:"Repara Pagos Viejos", className:"btn16 aslink", onclick:fixOldPagos}]}),pEsp);
            pEsp.parentNode.insertBefore(ecrea({eName:"DIV", className:"pie_element pie_quickSys", eChilds:[{eName:"IMG", src:"imagenes/icons/process200.png", title:"Repara Pagos Incompletos", className:"btn16 aslink", onclick:fixEmptyPagos}]}),pEsp);
        }
    }*/
<?php } ?>
    for (let i=0;i< rowIndexBlock.length;i++) {
        rowIndexBlock[i].classList.add("rowidx1");
    }
    clearTimeout(timeOutLoading);
    timeOutLoading = setTimeout(addStatusTooltip,10,1);

    _forma_submitted=false;
}
function footLoad(txt) {
    console.log("INI footLoad");
    let count=0;
    if (document.querySelectorAll) {
        count=document.querySelectorAll("#"+resultDiv+">TR").length;
    } else {
        fee(ebyid(resultDiv).children, function(el){if(el.tagName==="TR")count++;});
    }
    const nr=ebyid("numRegs");
    const num=+(nr?nr.value:-1);
    const ftr=ebyid("footer");
    console.log("REGISTROS: "+count+"/"+num);
    ekfil(ftr,["stopBtn","downloadBtn"<?= $_esSistemas?",\"continueBtn\",\"extraBtn\"":"" ?>]);
    if (!txt) txt="";
    ftr.insertBefore(ecrea({eText:"Registros encontrados: "+(count< num?count+"/":"")+num+txt}),ebyid("extraBtn"));
    return count;
}
function doLoad(idxElem,loop) {
    //console.log("INI doLoad "+idxElem.value+" | "+(loop?loop:-1));
    clearTimeout(timeOutLoading);
    let idxVal=idxElem.value;
    let idElem=ebyid("rowid"+idxVal);
    if (idElem) {
        let xmlhttp = ajaxRequest();
        let url="consultas/Proceso.php?llave[]=p.identif&llave[]=p.modulo&fulldata&solicita=p.id,p.fecha,p.usuario,p.region,p.status,p.detalle,u.persona&extraTable=p inner join Usuarios u on p.usuario=u.nombre&p.modulo=Factura&p.identif="+idElem.value;
        //console.log("AJXURL='"+url+"'");
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState!=4||xmlhttp.status!=200) {
                if (xmlhttp.readyState>=4) console.log("Proceso no encontrado o fallido "+xmlhttp.readyState+"/"+xmlhttp.status+": "+xmlhttp.responseText);
                return;
            }
            let respArr = xmlhttp.responseText.split("|");
            let itpElem=ebyid("rowitp"+idxVal);
            if (!itpElem) {
                console.log("Elemento no encontrado: 'rowitp"+idxVal+"'");
            } else if (respArr.length>0) {
                //console.log("Consulta exitosa para idf:"+idElem.value);
                while(itpElem.firstChild) itpElem.removeChild(itpElem.firstChild);

                const thtrObj = {eName:"TR",eChilds:[]};
                let colnames = ["Fecha","Usuario","Status"];
                for(let c=0; c<3; c++) {
                    thtrObj.eChilds.push({eName:"TH",className:"pad2 brdr1d bbtm2d",eText:colnames[c]});
                }

                const tbdObj={eName:"TBODY",className:"panalBGLight",eChilds:[]};
                let folioElem=ebyid("ffolio"+idxVal);
                let tcElem=ebyid("ftipocomprob"+idxVal);
                let isFixP=false;
                console.log("RespArr: [ "+respArr.join(" | ")+" ] LEN="+respArr.length);
                //console.log(respArr);
                for(let i=0; i < respArr.length; i+=7) {
                    let fecha=respArr[i+1];
                    let vfecha=fecha.slice(0,10);
                    let td3={eName:"TD",className:"pad2 brdr1d",title:respArr[i+5],eText:respArr[i+4]};
<?php if ($_esSistemas) { ?>
                    if(respArr[i+4]==="Pendiente" && !isFixP) {
                        isFixP=true;
                        td3.className+=" btnOp pointer";
                        td3.fId=idElem.value;
                        td3.title="Regresar a Pendiente";
                        td3.stt="Pendiente";
                        td3.sttn="0";
                        if (folioElem) td3.folio=folioElem.value;
                        if (tcElem) td3.tc=tcElem.value.toLowerCase();
                        td3.onclick=fixStatus;
                    }
<?php } ?>
                    tbdObj.eChilds.push({eName:"TR",eChilds:[{eName:"TD",className:"pad2 brdr1d", title:fecha,eText:vfecha},{eName:"TD",className:"pad2 brdr1d usrNdNm", title:respArr[i+6]+"\n"+respArr[i+3],eText:respArr[i+2]},td3]});
                }
                const tblObj={eName:"TABLE",className:"noApply fontSmall",eChilds:[{eName:"THEAD",className:"panalBGDark boldValue whited",eChilds:[{eName:"TR",eChilds:[{eName:"TD",className:"righted",eText:"ID:"},{eName:"TD",className:"centered larger btnvwTmp",onclick:copyToClipboard,eText:idElem.value},{eName:"TD",className:"righted",eText:idElem.getAttribute("status")}]},thtrObj]},tbdObj]};
                itpElem.appendChild(ecrea(tblObj));
                idxElem.classList.remove("rowidx"+loop);
                let nextLoop = loop+1;
                idxElem.classList.add("rowidx"+nextLoop);
            } else console.log("Datos vacios='"+xmlhttp.responseText+"'");
            timeOutLoading = setTimeout(addStatusTooltip,10,loop);
        };
        xmlhttp.open("GET",url,true);
        xmlhttp.send();
    }
}
function exportToCSV(evt) {
    console.log("INI exportToCSV");
    let link=ebyid("toCsvLink");
    if (link) {
        link.click();
        console.log("ENLACE REUTILIZADO");
    } else {
        const dtb=ebyid("dialog_tbody");
        const arr=htmlTableToArray(dtb.parentNode, true, ",", [0,1,2,3,4,5,6,7,8,9]); // 0=#, 1=F.Creación, 2=F.Captura, 3=Empresa, 4=Proveedor, 5=TipoComprobante, 6=Folio, 7=Remision, 7_value2=UUID, 8=Total, 8_value2=Moneda, 9=Status
        link=arrayToCSVFile(arr,false,true,true,false);
        if (link===false)
            console.log("DATOS NO RECONOCIDOS");
        else {
            link.id="toCsvLink";
            cladd(link,"hidden");
            console.log("ARCHIVO GENERADO");
        }
    }
}
function saveReferral(invId) {
    const rem = ebyid("numremision");
    let deft = rem.defaultValue; if (deft.length>20) { deft = deft.slice(0, 20); rem.defaultValue=deft; }
    let text = rem.value; if (text.length>20) { text = text.slice(0,20); rem.value=text; }
    console.log("INI saveReferral: newValue='"+text+"' vs defaultValue='"+deft+"'");
    if (rem.isSaving) { console.log("IS SAVING"); return; }
    if (deft===text) { console.log("NOTHING TO SAVE"); return; }
    rem.isSaving=true;

    // Decodifica entidades HTML numéricas si existen
    const txt = text.replace(/&#\d+;/gm, function(s) { return String.fromCharCode(s.match(/\d+/gm)[0]); });

    // Codifica caracteres especiales como entidades HTML numéricas
    const enc = txt.replace(/[\u00A0-\u9999<>\&]/g, function(i) { return '&#' + i.charCodeAt(0) + ';'; });

    // Si el texto original es igual al codificado en longitud, se usa el codificado
    if (text.length > 0 && enc.length >= text.length) { text = enc; }

    // Envía los datos al servidor usando postService
    rem.style.backgroundColor="palegoldenrod";
    // No es el momento, en caso de error quiero regresar al anterior // rem.oldvalue=rem.value; // text
    readyService("consultas/Facturas.php", {action: "saveReferral", invId: invId, text: text}, function(jobj,extra) {
        if (jobj.result && jobj.result==="success") {
            console.log("Successful referral saving", jobj);
            rem.style.backgroundColor="springgreen";
            setTimeout(function(){rem.style.backgroundColor="white";rem.defaultValue=rem.value;rem.isSaving=false;},2000);
            // Ajuste en pantalla:
            const fI2Elem=ebyid("fI2dI"+invId);
            if (fI2Elem) {
                const dtIdx=fI2Elem.value;
                const fElem=ebyid("fremision"+dtIdx);
                if (fElem) {
                    fElem.value=rem.value;
                    const txtElem = fElem.previousSibling;
                    if (txtElem && txtElem.nodeType==3) txtElem.nodeValue=rem.value;
                }
            }
        } else {
            console.log("Failed to save Referral, resetting value to default", jobj);
            rem.style.backgroundColor="salmon";
            setTimeout(function(){rem.style.backgroundColor="white";rem.value=rem.defaultValue;rem.isSaving=false;},2000);
        }
    }, function(errmsg,respText,extra) {
        console.log("ERROR saveReferral process: "+errmsg);
        console.log("Response Text: ", respText);
        console.log("Extra: ", extra);
        rem.style.backgroundColor="salmon";
        setTimeout(function(){rem.style.backgroundColor="white";rem.value=rem.defaultValue;rem.isSaving=false;},2000);
    });
    console.log("END saveReferral: ENVIADO");
}

<?php if ($_esSistemas) { ?>
function fixEmptyPagos() {
    console.log("fixEmptyPagos");
    overlayWheel();
    readyService("consultas/Facturas.php", {action:"fixEmptyCPagos"}, fixOPDone, fixOPFail);
}
function fixOldPagos() {
    console.log("fixOldPagos");
    overlayWheel();
    readyService("consultas/Facturas.php", {action:"fixOldCPagos"}, fixOPDone, fixOPFail);
}
function fixOPDone(jobj,extra) {
    overlayClose();
    if (jobj.time) console.log("DURACION: "+jobj.time);
    if (jobj.data) console.log("DATOS: "+jobj.data);
    const viewResult=[{eName:"H2",eText:"ERRORES"}];
    jobj.trace.Errores.forEach((x,i)=>{viewResult.push(getLines(x,i));});
    viewResult.push({eName:"H2",eText:"QUERIES"});
    jobj.trace.Queries.forEach((x,i)=>{viewResult.push(getLines(x,i));});
    viewResult.push({eName:"H2",eText:"PASOS"});
    jobj.trace.Pasos.forEach((x,i)=>{viewResult.push(getLines(x,i));});
    overlayMessage(viewResult,jobj.result);
    // cambiar height
}
function getLines(text,idx) {
    if (idx==0) return [{eText:text}];
    return [{eName:"BR"},{eText:text}];
}
function fixOPFail(errmsg,respText,extra) {
    overlayClose();
    console.log("ERROR: "+errmsg);
    console.log(respText);
    console.log("EXTRA: "+JSON.stringify(extra, jsonCircularReplacer()));
    overlayMessage("Proceso interrumpido","ERRORES");
}
function updatePaymRcpt(fId) {
    console.log("updatePaymRcpt "+fId);
    readyService("consultas/Facturas.php", {action:"updtPymRcptDt",id:fId}, upPyRcDone, upPyRcFail); 
}
function upPyRcDone(jobj,extra) {
    console.log("JOBJ: ",JSON.stringify(jobj, jsonCircularReplacer()));
    console.log("EXTRA: ",JSON.stringify(extra, jsonCircularReplacer()));
    console.log("overlayClose");
    overlayClose();
    submitAjax("Buscar",function(ajaxObj3,flag){
        console.log("CALLBACK submitAjax Buscar"+(ajaxObj3?". upPyRcDone:"+ajaxObj3.responseText.length+". "+(flag?" PARTIAL.":" FULL."):""));
        if (!flag) {
            cladd("waitRoll","hidden");
            overlayMessage(getParagraphObject("El documento fue actualizado satisfactoriamente"),"Documento Actualizado");
        }
    });
}
function upPyRcFail(errmsg,respText,extra) {
    console.log("FAIL: "+errmsg);
}
function repairCFDIs(evt) {
    console.log("Repair CFDIs");
    const dtb=ebyid("dialog_tbody");
    const els=dtb.querySelectorAll(".rowid");
    const ids=[];
    els.forEach(e=>ids.push(e.value));
    console.log("Ids Factura found:",ids);
    overlayWheel();
    readyService("consultas/Facturas.php",{action:"repairCFDIs",idList:ids},repaired,notRepaired);
}
function repaired(jobj,extra) {
    const pars=jobj.params;
    const xhp=pars.xmlHttpPost;
    console.log("INI repaired j("+pars.state+"/"+pars.status+" | "+xhp.readyState+"/"+xhp.status+":"+xhp.statusText+"), e("+extra.state+"/"+extra.status+")");
    if (xhp.readyState!==4) return;
    if (jobj && jobj.result==="error") {
        extra.trace=jobj.trace;
        notRepaired(jobj.message,false,extra);
    } else if (jobj && jobj.result==="empty") {
        console.log("jobj: ", jobj,"\nextra: ", extra);
        cladd("waitRoll","hidden");
        overlayClose();
    } else {
        submitAjax("Buscar",function(ajaxObj4,flag){
            console.log("CALLBACK submitAjax Buscar"+(ajaxObj4?". repaired:"+ajaxObj4.responseText.length+". "+(flag?" PARTIAL.":" FULL."):""));
            if (!jobj) console.log("EMPTY:",extra.lastJObj);
            else console.log("jobj: ", jobj,"\nextra: ", extra);
            if (!flag) {
                cladd("waitRoll","hidden");
                overlayClose();
                overlayMessage(getParagraphObject(["Los documentos fueron reparados satisfactoriamente",{eName:"HR"},getTrace2Table(jobj.trace)]),"Documento Reparado");
            }
        });
        return true;
    }
}
function notRepaired(errmsg,respText,extra) {
    overlayClose();
    console.log("ERROR: ", errmsg, "\nTEXT: ",respText, "\nEXTRA: ",extra);
    overlayMessage(getParagraphObject(extra.trace?[errmsg,{eName:"HR"},getTrace2Table(extra.trace)]:errmsg), title="ERROR");
}
function getTrace2Table(trc) {
    const rws=[];
    let aux=false;
    const pgClass=[];
    let cmd=false;
    let arg=false;
    for (let key in trc) {
        if (trc.hasOwnProperty(key)) {
            aux=[];
            if (key==="CPagos"||key==="DPagos"||key==="Facturas") {
                if (trc[key].deleted.length>0) {
                    aux.push(getParagraphObject("DELETED:"));
                    trc[key].deleted.forEach(l=>aux.push(getParagraphObject(JSON.stringify(l))));
                }
                if (trc[key].saved.length>0) {
                    aux.push(getParagraphObject("SAVED:"));
                    trc[key].saved.forEach(e=>aux.push(getParagraphObject(JSON.stringify(e))));
                }
            } else {
                trc[key].forEach(v=>{
                    cmd=v.slice(0,8);
                    arg=v.slice(8).trim();
                    if (cmd==="addclass") {
                        if (arg.length>0) pgClass.push(arg);
                    } else if (cmd==="delclass") {
                        if (pgClass.length>0) {
                            if (!arg) pgClass.pop();
                            else {
                                arg=+arg;
                                while (arg>0 && pgClass.length>0) {
                                    pgClass.pop();
                                    arg--;
                                }
                            }
                        }
                    } else {
                        const pgClassStr = pgClass.length>0?pgClass.slice(-1):false;
                        const repCls = pgClassStr===false?false:true;
                        aux.push(getParagraphObject(v,pgClassStr,repCls));
                    }
                });
            }
            if (aux.length>0)
                rws.push({eName:"TR",eChilds:[{eName:"TH",className:"nowrap p3bb1d",eText:key},{eName:"TD",className:"p3bb1d",eChilds:aux}]});
        }
    }
    return {eName:"DIV",className:"maxFlowMsg",eChilds:[{eName:"TABLE",className:"all_space izquierdo bcollapse",eChilds:rws}]};
}
function fixStatus(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    console.log("INI function fixStatus: ("+tgt.fId+","+tgt.stt+","+tgt.folio+","+tgt.tc+")");
    overlayConfirmation("<p>Confirmar cambio de status de comprobante '"+tgt.tc.toUpperCase()+"' "+tgt.folio+" a "+tgt.stt+".</p>","Confirmar",function(){
        postService(
            "consultas/Facturas.php",
            {
                actualiza:"fixstatus",
                invId:tgt.fId,
                statusn:tgt.sttn,
                tipoComprobante:tgt.tc
            },function(responseText,params,readyState,hStatus) {
                if (readyState==4 && hStatus==200) {
                    try {
                        let jobj=JSON.parse(responseText);
                        if (jobj.result==="refresh") {
                            location.reload(true);
                        } else if (jobj.result==="exito")
                            overlayMessage("<p>"+jobj.message+"</p>", title="CAMBIO EXITOSO");
                        else {
                            overlayMessage("<p>"+jobj.message+"</p>", title="RESULTADO CON ERROR");
                            console.log("JOBJ ERRORS: ",jobj.errors);
                        }
                    } catch(err) {
                        overlayMessage("<p>Error</p>", title="RESPUESTA CON ERROR");
                        console.log("ERROR: ", err, responseText);
                    }
                }
            }
        );
    });
}
<?php } ?>
function preReemplazaDoc(evt) {
    ekil("replaceFile");
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    const typ=tgt.getAttribute("tipo");
    let row=tgt;
    while(row&&row.tagName!=="TR") row=row.parentNode;
    const idx=row.getAttribute("idx");
    const fIdElem=ebyid("rowid"+idx);
    console.log("INI preReemplazaDoc tipo='"+typ+"', factId='"+fIdElem.value+"'",tgt);
    const fileElem=ecrea({eName:"INPUT",type:"file",className:"wid0",id:"replaceFile",idx:idx,accept:".pdf",img:tgt,link:tgt.previousElementSibling,onchange:function(evt){const et=evt.target;console.log("ONCHANGE!",et);reemplazaDoc(evt);},onblur:function(evt){const et=evt.target;console.log("BLUR!",et);}})
    fileElem.factId=fIdElem.value;
    fileElem.idx=idx;
    tgt.parentNode.appendChild(fileElem);
    fileElem.focus();
    fileElem.click();
}
function reemplazaDoc(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    console.log("INI reemplazaDoc: ", tgt);
    toNKil("wrongTip");
    const file = tgt.files[0];
    const fchk = isValidFile(file,"application/pdf");
    if (fchk===false) {
        console.log("Falta indicar un archivo");
    } else if (fchk!==true) {
        console.log("ERROR: "+fchk);
        const wrongTip=ecrea({eName:"DIV",id:"wrongTip",className:"errorLabel bgpink abs_se13 font10 padv03 br1sdr round",eText:"ERR:"+fchk});
        wrongTip.onclick=function(){toNKil("wrongTip");};
        wrongTip.timeoutVar=setTimeout(()=>{toNKil("wrongTip");},10000);
        cladd(tgt.parentNode,"relative");
        tgt.parentNode.appendChild(wrongTip);

    }
    else {
        const tipo = tgt.img.getAttribute("tipo");
        console.log("FNC reemplazaDoc "+tipo+" FactId="+tgt.factId+", LINK="+(tgt.link?(tgt.link.href?tgt.link.href:tgt.link):"noLink"));
        postService("consultas/Archivos.php",{action:"repDoc",file:file,type:tipo,fId:tgt.factId,idx:tgt.idx},getPostRetFunc(docSent,docNotSent),getPostErrFunc(docNotSent));
    }
}
function docSent(jobj,extra) {
    if (jobj.params.xmlHttpPost.readyState!==4) return;
    if (jobj.result==="error") {
        extra.error={message:(jobj.message?jobj.message:"Error Indefinido")};
        if (jobj.file) extra.error.file=jobj.file;
        if (jobj.function) extra.error.function=jobj.function;
        if (jobj.line) extra.error.line=jobj.line;
        if (jobj.trace) extra.error.trace=jobj.trace;
        if (jobj.log) extra.log=jobj.log;
        if (jobj.accion) extra.accion=jobj.accion;
        if (jobj.ubicacion) extra.ubicacion=jobj.ubicacion;
        if (jobj.nombre) extra.nombre=jobj.nombre;
        docNotSent(jobj.message,"",extra);
        return;
    }
    const idx=jobj.params.idx;
    let tipo=jobj.params.type;
    if (tipo==="cfdi") tipo="pdf";
    if (idx) {
        const blk=ebyid(tipo+"Blk"+idx);
        if (blk) {
            clrem(blk,"redFilter");
            cladd(blk,"greenFilter");
            blk.timeoutVar=setTimeout(toNClr,5000,blk);
            blk.onclick=function(event){toNCls(blk);};
        }
        else console.log("SUCCESS! NO BLK!");
    } else console.log("SUCCESS! NO IDX!");
    console.log("INI docSent!",JSON.stringify(jobj, jsonCircularReplacer()), JSON.stringify(extra, jsonCircularReplacer()));
}
function docNotSent(errmsg,respTxt,extra) {
    const idx=extra.parameters.idx;
    let tipo=extra.parameters.type;
    if (tipo==="cfdi") tipo="pdf";
    if (idx) {
        const blk=ebyid(tipo+"Blk"+idx);
        if (blk) {
            clrem(blk,"greenFilter");
            cladd(blk,"redFilter");
            blk.timeoutVar=setTimeout(toNClr,5000,blk);
            blk.onclick=function(event){toNCls(blk);};
        }
        else console.log("ERROR. NO BLK!");
    } else console.log("ERROR! NO IDX!");
    console.log("INI docNotSent! ERR:'"+errmsg+"', TXT:'"+respTxt+"'",JSON.stringify(extra, jsonCircularReplacer()));
}
function toNKil(id) {
    const wt=ebyid(id);
    if (wt) {
        const wpn=wt.parentNode;
        if (wt.timeoutVar)
            clearTimeout(wt.timeoutVar);
        ekil(wt);
        clrem(wpn,"relative");
    }
}
function toNClr(el) {
    if (el) {
        el.onclick=null;
        el.timeoutVar=null;
        clrem(el,"greenFilter");
        clrem(el,"redFilter");
    }
}
function toNCls(el) {
    // if (el es event) el=el.tgt;
    // if (el es string) el=ebyid(el);
    if (el) {
        if (el.timeoutVar) {
            clearTimeout(el.timeoutVar);
            delete el.timeoutVar;
        }
        clrem(el,"greenFilter");
        clrem(el,"redFilter");
    }
}
function preEliminaDoc(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    const tipo=tgt.getAttribute("tipo");
    let rwe = tgt;
    while (rwe && rwe.tagName!=="TR") rwe=rwe.parentNode;
    const idx=rwe.getAttribute("idx");
    console.log("INI preEliminaDoc "+tipo+", "+idx, tgt);
    const tipodoc=(tipo==="cfdi"?"PDF de Factura":(tipo==="ea"?"Entrada de Almacén":""));
    if (tipodoc.length>0) {
        const riE=ebyid("rowid"+idx);
        const fId=riE?riE.value:"";
        const ffE=ebyid("ffolio"+idx);
        const desc=ffE?ffE.value:"";
        console.log("ID='"+fId+"', FOLIO='"+desc+"', TIPO='"+tipo+"', IDX='"+idx+"'");
        overlayConfirmation(getParagraphObject(["Va a eliminar el documento "+tipodoc+" de la factura "+desc,"Está seguro de querer eliminarlo?"],"marhtt",true), "ELIMINAR DOCUMENTO", ()=>{overlayWheel();readyService("consultas/Archivos.php",{action:"delDoc",id:fId,type:tipo},docEliminado,docNoEliminado);});
    } else console.log("Tipo inválido: "+tipo);
}
function docEliminado(jobj,extra) {
    console.log("SUCCESS docEliminado"+((jobj&&jobj.params&&jobj.params.xmlHttpPost)?" STATE="+jobj.params.xmlHttpPost.readyState:((extra&&extra.lastJObj&&extra.lastJObj.params&&extra.lastJObj.params.xmlHttpPost)?" LASTSTATE="+extra.lastJObj.params.xmlHttpPost.readyState:"")));
    if ((!jobj && extra && extra.lastJObj && extra.lastJObj.result==="success")||(jobj && jobj.result && jobj.result==="success")) {
        submitAjax("Buscar",function(ajaxObj5,flag){
            console.log("CALLBACK submitAjax Buscar"+(ajaxObj5?". docEliminado:"+ajaxObj5.responseText.length+". "+(flag?" PARTIAL.":" FULL."):""));
            if (!jobj) console.log("EMPTY:",extra.lastJObj);
            if (!flag) {
                cladd("waitRoll","hidden");
                overlayClose();
                overlayMessage(getParagraphObject("El documento fue eliminado satisfactoriamente"),"Documento Eliminado");
            }
        });
        return true;
    }
    if (!jobj && extra && extra.lastJObj) console.log("EMPTY LAST UNSUCCESSFUL RESULT: ",extra);
    docNoEliminado((jobj&&jobj.result)?jobj.result:"SIN RESPUESTA",(jobj&&jobj.message)?jobj.message:"SIN MENSAJE",jobj?JSON.stringify(jobj,jsonCircularReplacer()):extra);
    return false;
}
function docNoEliminado(errmsg,respText,extra) {
    cladd("waitRoll","hidden");
    overlayClose();
    if (errmsg==="error") {
        overlayMessage(getParagraphObject(respText),"ERROR");
    } else {
        overlayMessage(getParagraphObject("El documento no se pudo eliminar"),"ERROR");
    }
    console.log("ERROR:",errmsg);
    console.log("RESPONSE:",respText);
    console.log("EXTRA:",extra);
}
function restoreDoc(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    console.log("INI function restoreDoc: '"+tgt.getAttribute("filename")+"'",tgt);
    //readyService("consultas/Archivos.php",{action:"resDoc",id:fId,path:tgt.getAttribute("filename")},docRestaurado,docNoRestaurado);});
}
function docRestaurado(jobj,extra) {
    ;
}
function docNoRestaurado(errmsg,respText,extra) {
    ;
}
function eaSwitch(idx,val) {
    const elId="delEA"+idx;
    const el=ebyid(elId);
    //console.log("INI eaSwitch ( id="+elId+"("+(el?"true":"false")+"), val="+(val?"true":"false")+" )");
    if (val) {
        el.focus();
        el.onkeydown=function(e) {
            if (e.key === 'Control') {
                //console.log('Ctrl key is pressed down.',this);
                this.src="imagenes/icons/refresh.png";
                this.onclick=window["preReemplazaDoc"];
            }
        };
        el.onkeyup=function(e) {
            if (e.key === 'Control') {
                //console.log('Ctrl key is pressed up.',this);
                this.src="imagenes/icons/deleteIcon12.png";
                this.onclick=window["preEliminaDoc"];
            }
        }
    } else {
        el.blur();
        el.onkeydown=null;
        el.onkeyup=null;
        el.src="imagenes/icons/deleteIcon12.png";
        el.onclick=window["preEliminaDoc"];
    }
    //el.src="imagenes/icons/"+img+".png";
    //el.title=typ+" Entrada de Almacén";
    //el.onclick=window["pre"+typ+"Doc"]; // evt

}
function adjustPymWidths() {
    const swhpAreaNames=["Pym","Ins"];
    swhpAreaNames.forEach(n=>{
        const currs=document.querySelectorAll(".swhp"+n+">span.curr");
        let maxWid=0;
        fee(currs,el=>{const tw=getTextWidth(el.textContent);if (tw>maxWid) maxWid=tw;});
        if (maxWid>0) {
            const spans=document.querySelectorAll(".swhp"+n+">span");
            fee(spans,el=>{if(clhas(el,"cap")) { el.style.width="calc(100% - "+maxWid+"px)";} else if (clhas(el,"curr")) { el.style.width=maxWid+"px";}});
        }
    });
}
function switchPymCurrency(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    const mon=tgt.getAttribute("mon");
    const isMon=(tgt.textContent.slice(-3)===mon);
    const lmn=mon.toLowerCase();
    fee(document.querySelectorAll((clhas(tgt.parentNode,"swhpPym")?".swhpPym":".swhpIns")+">span.curr"),el=>el.textContent=el.getAttribute(isMon?"mxn":lmn));
}
function addStatusTooltip(loop) {
    //console.log("addStatusTooltip ... looking for classname rowidx"+loop);
    let rowIndexBlock = document.getElementsByClassName("rowidx"+loop);
    //console.log(rowIndexBlock);
    let blockLen = rowIndexBlock.length;
    if (blockLen>0) {
        //console.log("blockLen="+blockLen);
        let elem = rowIndexBlock[0];
        doLoad(elem,loop);
        //elem.classList.remove("rowidx"+loop);
        //elem.classList.
    }
}
var holdingList={};
function holding(id,secs,pfx,depth) {
    console.log("INI holding: id="+id+", secs="+secs+", pfx='"+pfx+"'"+(depth?", depth="+depth:""));
    if(!isHolding(id)) {
        if (!depth) depth=1;
        const milis=secs*1000;

        if (!pfx) pfx="HOLDING";
        let msg=pfx+" "+id+": consultaCFDI "+secs;
        if (depth>1) msg+=" x"+depth;
        console.log(msg);

        setHolding(id,setTimeout(consultaCFDI,milis,id,depth));
    } else console.log("NOT HOLDING "+id+" ("+pfx+")");
}
function isHolding(id) {
    const idS=id.toString();
    //return holdingList.hasOwnProperty(id.toString());
    return idS in holdingList;
}
function getHolding(id) {
    if (isHolding(id)) {
        const idS=id.toString();
        return holdingList[idS];
    } else console.log("NOT GETHOLDING "+id);
    return false;
}
function setHolding(id,timeoutId) {
    console.log("INI setHolding: id="+id);
    const idS=id.toString();
    if (timeoutId) holdingList[idS]=timeoutId;
    else console.log("NOT SETHOLDING "+id+", "+timeoutId);
}
function clearHolding(id) {
    console.log("INI clearHolding: id="+id);
    if (isHolding(id)) {
        const idS=id.toString();
        clearTimeout(holdingList[idS]);
        delete holdingList[idS];
    } else console.log("NOT CLEARHOLDING "+id);
}
function preVerificaCFDI(invId) {
    overlayConfirmation("Confirmar solicitud de verificacion de factura.","Confirmar",function() { verificaCFDI(invId); });
}
function verificaCFDI(invId) {
    console.log("INI function verificaCFDI "+invId);
    let cfdImg=ebyid("statusCFDI"+invId);
    cfdImg.onclick=null;
    cfdImg.classList.remove("pointer");
    postService("consultas/Facturas.php",{accion:"verificaCFDI",id:invId},function(responseText,params,readyState,hStatus) {
        if (readyState==4 && hStatus==200) {
            try {
                let jobj=JSON.parse(responseText);
                //console.log("Respuesta = "+responseText);
                if (jobj.result) {
                    if (jobj.result==="refresh") {
                        location.reload(true);
                    } else if (jobj.result==="error") {
                        cfdImg.src="imagenes/icons/statusErrorDn.png";
                        cfdImg.setAttribute("title","Resultado Error "+jobj.message);
                    } else if (jobj.result==="success") {
                        if (cfdImg) {
                            cfdImg.src="imagenes/icons/statusWaitDn.png";
                            cfdImg.setAttribute("title","En espera de verificación...");
                            holding(invId,10,'VERIFIC');
                        }
                    }
                }
            } catch(err) {
                let text="verificaCFDI. ";
                if (err instanceof SyntaxError)
                    text+=err.name+":"+err.message+" ("+err.fileName+"#"+err.lineNumber+"#"+err.columnNumber+")";
                else if (err.name || err.message) {
                    if (err.name) text+=err.name;
                    if (err.message) {
                        if (err.name) text+=" ";
                        text+=err.message;
                    }
                } else text+=err.toString();
                cfdImg.src="imagenes/icons/statusErrorDn.png";
                cfdImg.setAttribute("title","Error en respuesta "+text);
                console.log("Exception: ", err);
                console.log("JSON: "+responseText);
                postService("consultas/Errores.php",{accion:"savelog",nombre:"reportefactura",texto:text});
            }
        }
    });
}
function consultaCFDI(invId,num) {
    console.log("INI consultaCFDI: id="+invId+", num="+num);
    clearHolding(invId);
    let cfdImg=ebyid("statusCFDI"+invId);
    postService("consultas/Facturas.php",{accion:"consultaCFDI",id:invId},function(responseText,params,readyState,hStatus) {
        if (readyState==4 && hStatus==200) {
            try {
                let jobj=JSON.parse(responseText);
    // ToDo: postService consultas/Facturas campos CFDI para actualizar icono
    // ToDo: incrementar num y si no cambia a Verde,Rojo o Gris, hacer timeout de num minutos para ejecutar consultaCFDI(invId,num)
                if (jobj.result) {
                    if (jobj.result==="refresh") {
                        location.reload(true);
                    } else if (jobj.result==="error") {
                        if (jobj.message)
                            cfdImg.setAttribute("title","Resultado Error "+jobj.message);
                        else {
                            cfdImg.setAttribute("title","Resultado Error no especificado");
                            console.log("Resultado Error no especificado:\n"+responseText);
                        }
                        cfdImg.src="imagenes/icons/statusErrorDn.png";
                        cfdImg.onclick=null;
                        cfdImg.classList.remove("pointer");
                    } else if (jobj.result==="success") {
                        console.log("TXT: "+responseText);
                        if (cfdImg) {
                            if (jobj.src) cfdImg.src=jobj.src;
                            if (jobj.title) cfdImg.setAttribute("title",jobj.title);
                            if (jobj.className) cfdImg.className=jobj.className;
                            if (jobj.onclick) cfdImg.onclick=function(){verificaCFDI(invId);};
                            else cfdImg.onclick=null;
                            clset(cfdImg,"pointer",cfdImg.onclick!==null);
                            if (jobj.loop) {
                                cfdImg.setAttribute("title","En espera de verificación... Intento "+num+".");
                                holding(invId,10+(20*num),"CONSULT",num+1);
                            }
                            if (jobj.status) { // ToDo: Arreglar Status consultaCFDI
                                const prvE=cfdImg.previousElementSibling;
                                const txtE=prvE.firstChild;
                                if (txtE.nodeType==3) txtE.nodeValue=jobj.status; // CanceladoSAT
                                else console.log("NODO DE STATUS: ",txtE);
                            }
                        }
                    } else {
                        cfdImg.setAttribute("title","'"+jobj.result+"'");
                        cfdImg.src="imagenes/icons/statusErrorDn.png";
                        cfdImg.onclick=null;
                        cfdImg.classList.remove("pointer");
                        console.log("Resultado Desconocido:\n"+responseText);
                    }
                } else {
                    cfdImg.setAttribute("title","Sin Resultado");
                    cfdImg.src="imagenes/icons/statusErrorDn.png";
                    cfdImg.onclick=null;
                    cfdImg.classList.remove("pointer");
                    console.log("Sin Resultado:\n"+responseText);
                }
                
            } catch(err) {
                let text="consultaCFDI. ";
                if (err instanceof SyntaxError)
                    text+=err.name+":"+err.message+" ("+err.fileName+"#"+err.lineNumber+"#"+err.columnNumber+")";
                else if (err.name || err.message) {
                    if (err.name) text+=err.name;
                    if (err.message) {
                        if (err.name) text+=" ";
                        text+=err.message;
                    }
                } else text+=err.toString();
                cfdImg.setAttribute("title","Error en respuesta "+text);
                cfdImg.src="imagenes/icons/statusErrorDn.png";
                cfdImg.onclick=null;
                cfdImg.classList.remove("pointer");
                console.log("Exception: ", err);
                console.log("JSON: "+responseText);
                postService("consultas/Errores.php",{accion:"savelog",nombre:"reportefactura",texto:text});
            }
        }
    });
}
function selectedItem(prefix) {
    let forma = document.repfactform;
    if (forma) {
        let tipoListaElem = forma.tipolista;
        if (tipoListaElem) {
            let aux = Object.prototype.toString.call(tipoListaElem); // IE=[object HtmlCollection], FF/Ch=[object RadioNodeList]
            if (aux == "[object HTMLCollection]") {
                for (let i=0; i< tipoListaElem.length; i++) {
                    if (tipoListaElem[i].checked)
                        tipoListaVal = tipoListaElem[i].value;
                }
            } else tipoListaVal = tipoListaElem.value;
        }
    }
    let itemVal = getValueTxt(prefix+tipoListaVal);
    console.log(prefix+" "+tipoListaVal+" = "+itemVal);
    fillValue(prefix+"tcodigo", itemVal);
    fillValue(prefix+"trfc", itemVal);
    fillValue(prefix+"trazon", itemVal);
    
    let gpotc = ebyid(prefix+"tcodigo");
    let opts = gpotc.options;
    for (let i=0; i< opts.length; i++) {
        if (opts[i].value===itemVal) {
            console.log("Seleccionado: "+opts[i].text);
            break;
        }
    }
}
function recalculaEmpresas() {
    const me=ebyid("reloadGRP");
    if(me.locked) return;
    conlog("INI recalculaEmpresas");
    me.locked=true;
    me.src="imagenes/icons/statusWait2.gif";
    //me.onclick=null;
    //me.classList.remove("pointer");
    me.style.cursor="default";
    setTimeout(function() {
        let xmlhttp = ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                const respTxt = xmlhttp.responseText;
                const elem = ebyid("gpoSelectArea");
                if (respTxt.length==0) {
                    console.log("Respuesta vacía. Se perdió la sesión.");
                    me.src="imagenes/icons/statusWrong.png";
                    me.onload=function(){setTimeout(function(){const me1=ebyid("reloadGRP");me1.locked=false;
                        cladd(me1,"invisible");me1.src="imagenes/icons/descarga6.png";me1.onload=null;},3000);};
                } else elem.innerHTML=respTxt;
            } else if (xmlhttp.readyState>4||xmlhttp.status>200) {
                console.log("Error en conexión. State="+xmlhttp.readyState+", Status="+xmlhttp.status);
                me.src="imagenes/icons/statusWrong.png";
                me.onload=function(){setTimeout(function(){const me1=ebyid("reloadGRP");me1.locked=false;
                    cladd(me1,"invisible");me1.src="imagenes/icons/descarga6.png";me1.onload=null;},3000);};
            }
        };
        let tipocodigo = ebyid("tipocodigo");
        let tiporfc = ebyid("tiporfc");
        let tiporazon = ebyid("tiporazon");
        let tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
        xmlhttp.open("GET","consultas/Grupo.php?selectorhtml=1&tipolista="+tipolista,true);
        xmlhttp.send();
    },1000);
}
function recalculaProveedores() {
    const me=ebyid("reloadPRV");
    if(me.locked) return;
    conlog("INI recalculaProveedores");
    me.locked=true;
    me.src="imagenes/icons/statusWait2.gif";
    me.style.cursor="default";
    setTimeout(function() {
        let xmlhttp = ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                const respTxt = xmlhttp.responseText;
                const elem = ebyid("prvSelectArea");
                if (respTxt.length==0) {
                    console.log("Respuesta vacía. Se perdió la sesión.");
                    me.src="imagenes/icons/statusWrong.png";
                    me.onload=function(){setTimeout(function(){const me2=ebyid("reloadPRV");me2.locked=false;
                        cladd(me2,"invisible");me2.src="imagenes/icons/descarga6.png";me2.onload=null;},3000);};
                } else elem.innerHTML=respTxt;
            } else if (xmlhttp.readyState>4||xmlhttp.status>200) {
                console.log("Error en conexión. State="+xmlhttp.readyState+", Status="+xmlhttp.status);
                me.src="imagenes/icons/statusWrong.png";
                me.onload=function(){setTimeout(function(){const me2=ebyid("reloadPRV");me2.locked=false;
                        cladd(me2,"invisible");me2.src="imagenes/icons/descarga6.png";me2.onload=null;},3000);};
            }
        };
        let tipocodigo = ebyid("tipocodigo");
        let tiporfc = ebyid("tiporfc");
        let tiporazon = ebyid("tiporazon");
        let tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
        xmlhttp.open("GET","consultas/Proveedores.php?selectorhtml=1&tipolista="+tipolista,true);
        xmlhttp.send();
    },1000);
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
function dateIniSet() {
    console.log("function dateIniSet");
    let iniDateElem = ebyid("fechaInicio");
    let day = strptime(date_format, iniDateElem.value);
    setFullMonth(prev_month(day));
}
function dateEndSet() {
    console.log("function dateEndSet");
    let iniDateElem = ebyid("fechaInicio");
    let day = strptime(date_format, iniDateElem.value);
    setFullMonth(next_month(day));
}
function setFullMonth(date) {
    let firstDay = first_of_month(date);
    let lastDay = day_before(first_of_month(next_month(date)));
    let iniDateElem = ebyid("fechaInicio");
    let endDateElem = ebyid("fechaFin");
    iniDateElem.value = strftime(date_format, firstDay);
    endDateElem.value = strftime(date_format, lastDay);
    adjust_calendar();
}
function adjustCalMonImgs(tgtWdgt) { adjust_calendar(tgtWdgt,false,{freeRange:true}); }
function addPDFFile() {
    const c=ebyid('attachpdfcap');
    const m=ebyid('attachpdfmsg');
    const a=ebyid('attpdffile');
    if(a.files.length==0||a.files[0].name.length==0){
        c.textContent='SIN PDF';
        m.textContent='Presione icono azul para anexar CFDI-PDF';
    }else{
        c.textContent='';
        m.textContent='';
    }
}
function appendNewEA(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    if (tgt.disabled||clhas(tgt,"disabled")) { console.log("disabled"); return false; }

    let rwe = tgt;
    while (rwe && rwe.tagName!=="TR") {
        rwe=rwe.parentNode;
    }
    const idx=rwe?rwe.getAttribute("idx"):false;
    console.log("INI appendNewEA "+idx, tgt);
    const eafl=ebyid("eafile");
    if (eafl && idx) {
        eafl.idx=idx;
        eafl.onchange=submitEAFile;
        eafl.click();
    }
}
function submitEAFile(evt) {
    console.log("INI submitEAFile ",evt);
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    console.log("TGT submitEAFile ",tgt);
    if (!tgt.files || !tgt.files[0]) return false;
    console.log("FLS submitEAFile ",tgt.files);
    const file=tgt.files[0];
    const valFMsg=isValidFile(file,"application/pdf");
    overlayWheel();
    console.log("FIL submitEAFile ",file);
    if (valFMsg!==true) {
        if (valFMsg===false) {
            valFMsg="El archivo '"+file.name+"' está " + (file.size==0?"vacío":"incompleto");
        }
        console.log("ERR submitEAFile: "+valFMsg,file);
        overlayMessage(getParagraphObject(valFMsg),"ERROR");
    } else {
        const idx=tgt.idx?tgt.idx:"";
        const riE=ebyid("rowid"+idx);
        const fId=riE?riE.value:"";
        const tipo="ea";
        readyService("consultas/Archivos.php", {action:"addDoc",id:fId,type:tipo,file:tgt.files[0]}, docAnexado, docNoAnexado);
        tgt.idx=false;
        tgt.onchange=addEAFile;
    }
}
function docAnexado(jobj,extra) {
    console.log("SUCCESS docAnexado "+(jobj?"JOBJ-STATE="+jobj.params.xmlHttpPost.readyState:(extra&&extra.lastJObj?"LASTJSTT="+extra.lastJObj.params.xmlHttpPost.readyState:"NO JOBJ")));
    if ((!jobj && extra && extra.lastJObj && extra.lastJObj.result==="success")||(jobj && jobj.result && jobj.result==="success")) {
        submitAjax("Buscar",function(ajaxObj6,flag) {
            console.log("CALLBACK submitAjax Buscar"+(ajaxObj6?". docAnexado:"+ajaxObj6.responseText.length+". "+(flag?" PARTIAL.":" FULL."):""));
            if (!jobj) console.log("EMPTY:",extra.lastJObj);
            if (!flag) {
                cladd("waitRoll","hidden");
                overlayClose();
                overlayMessage(getParagraphObject("El documento fue agregado satisfactoriamente"),"Documento Anexado");
            }
        });
        return true;
    }
    if (!jobj && extra && extra.lastJObj) console.log("EMPTY LAST UNSUCCESSFUL RESULT: ",extra);
    docNoAnexado((jobj&&jobj.result)?jobj.result:"SIN RESPUESTA",(jobj&&jobj.message)?jobj.message:"SIN MENSAJE",jobj?JSON.stringify(jobj,jsonCircularReplacer()):extra);
    return false;
}
function docNoAnexado(errmsg,respText,extra) {
    cladd("waitRoll","hidden");
    overlayClose();
    if (errmsg==="error") {
        overlayMessage(getParagraphObject(respText),"ERROR");
    } else {
        overlayMessage(getParagraphObject("El documento no se pudo anexar"),"ERROR");
    }
    console.log("ERROR:",errmsg);
    console.log("RESPONSE:",respText);
    console.log("EXTRA:",extra);
}
function addEAFile() {
    console.log("INI addEAFile");
    const ifl=ebyid("eafile");
    let msgtxt="";
    let btntxt="Anexar PDF";
    if (ifl.files && ifl.files.length>0) {
        const fl=ifl.files[0];
        console.log("FILE: ",fl);
        msgtxt=isValidFile(fl,"application/pdf");
        if (msgtxt===true) {
            btntxt=fl.name;
            if(btntxt.length>17)
                btntxt=btntxt.slice(0,5)+"..."+btntxt.slice(-9);
            msgtxt="";
        } else if (msgtxt===false) {
            msgtxt="";
        }
    }
    if (msgtxt.length>0 && !clearFileInput(ifl))
        console.log("No fue posible limpiar contenido: '"+ifl.value+"'",ifl.files);
    const msg=ebyid("eamsg");
    if (msg) msg.textContent=msgtxt;
    const btn=ebyid("eafilebtn");
    if (btn) btn.textContent=btntxt;
}
function disableEA(evt) {
    if (!evt) evt = window.event;
    const tgt = evt.target || evt.srcElement;
    if (tgt.busy) { console.log("INI disableEA BUSY!"); return; }
    tgt.busy=true;
    let rwe = tgt;
    while (rwe && rwe.tagName!=="TR") rwe=rwe.parentNode;
    const idx=rwe?rwe.getAttribute("idx"):false;
    const riE=ebyid("rowid"+idx);
    const fId=riE?riE.value:"";
    const a_do=tgt.getAttribute("do");
    console.log("INI disableEA "+fId+" "+a_do+" "+idx,evt);
    postService("consultas/Facturas.php", {accion:"disWHEntry",id:fId,idx:idx,ea:a_do==="off"?-1:0,motivo:"sin motivo"}, getPostRetFunc(disabledInvoice,notDisabledInvoice,{tgt:tgt}), getPostErrFunc(notDisabledInvoice,{tgt:tgt}));
}
function disabledInvoice(jobj,extra) {
    if (jobj.params.xmlHttpPost.readyState!==4) return;
    if (jobj) {
        const kys=Object.keys(jobj);
        //if (jobj.params && jobj.params.xmlHttpPost && jobj.params===jobj.params.xmlHttpPost.parameters) jobj.params.xmlHttpPost.parameters='params';
        let msg="";
        kys.forEach(ky=>{msg+=" | "+ky+"=";if(typeof jobj[ky]==="object")msg+=JSON.stringify(jobj[ky],jsonCircularReplacer());else msg+="'"+jobj[ky]+"'";});
        if (jobj.result && jobj.result==="success") {
            console.log("INI disabledInvoice: "+msg);
            // toDo: modificar elementos visuales
            const ea=jobj.ea;
            const idx=jobj.params.idx;
            const eaImg1=ebyid("addEAImg"+idx);
            if (eaImg1) {
                if (ea==0) {
                    eaImg1.src="imagenes/icons/pdf200EAPlus1.png";
                    eaImg1.className="cellptr";
                    eaImg1.title="Anexar Entrada de Almacén";
                } else if (ea==-1) {
                    eaImg1.src="imagenes/icons/pdf200EAx.png";
                    eaImg1.className="";
                    eaImg1.title="";
                } else console.log("NOT VALID addEA"+idx+"="+ea);
            } else console.log("NOT FOUND addEAImg"+idx);
            const eaImg2=ebyid("disEA"+idx);
            if (eaImg2) {
                if (ea==0) {
                    eaImg2.src="imagenes/icons/noAplica.png";
                    eaImg2.title="Bloquea Entrada de Almacén";
                    eaImg2.setAttribute("do","off");
                } else if (ea==-1) {
                    eaImg2.src="imagenes/icons/siAplica.png";
                    eaImg2.title="Habilita Entrada de Almacén"
                    eaImg2.setAttribute("do","on");
                } else console.log("NOT VALID disEA"+idx+"="+ea);
            } else console.log("NOT FOUND disEA"+idx);
            if (extra.tgt) extra.tgt.busy=false;
        } else notCancelledInvoice(jobj.result?jobj.result:"SIN RESPUESTA",jobj.message?jobj.message:"SIN MENSAJE: "+msg);
    } else notCancelledInvoice("SIN RESPUESTA","SIN MENSAJE NI JOBJ");
}
function notDisabledInvoice(errmsg,respText,extra) {
    if (errmsg==="error") {
        console.log("INI notDisabledInvoice ERROR: "+respText);
    } else {
        console.log("INI notDisabledInvoice ERRMSG:",errmsg);
        console.log("RESPONSE:",respText);
    }
    if (extra.tgt) extra.tgt.busy=false;
}
<?php if ($_esSistemas) { ?>
function resetRP(factId) {
    console.log("INI function RESETRP "+factId);
    readyService("consultas/Facturas.php", {action: "clrRPInInv",fId: factId}, (j, x, n)=> {
        if (j) console.log("Result "+JSON.stringify(j, jsonCircularReplacer()));
        if (x) console.log("EXTRA: "+JSON.stringify(x, jsonCircularReplacer()));
        overlayClose();
        let title="RESULTADO";
        let message="DETALLE";
        switch(j.result) {
            case "success":
                title="EXITO";
                message=j.message;
                break;
            case "empty":
                title="SIN CAMBIOS";
                message="La factura ya no tiene datos de comprobante de pago ligados";
                break;
            case "error":
                title="ERROR";
                message="Error de Datos "+j.errno+": "+j.error;
                break;
            default:
        }
        overlayMessage(getParagraphObject(message), title);
    }, (e, t, x)=> {
        showOnError(e, t, x);
    });
}
<?php } ?>
<?php if ($esAvance) { ?>
function fixResult(respText,params,state,status) {
    console.log("FixResult "+state+"/"+status+". Txt="+respText);
    if (state!=4||status!=200) return;
    let response=JSON.parse(respText);
    if (response.result==="success") {
        try {
            //console.log("fixResult Success");
            let tgt=ebyid(params.target);
            tgt.classList.remove("wrong1BG");
            tgt.classList.add("right1BG");
            tgt.title="";
            ekfil(tgt.firstElementChild);
            let lnk=ecrea({eName:"A",href:params.filePath+params.newName+"."+params.extension,target:"archivo",eChilds:{eName:"IMG",src:"imagenes/icons/"+params.extension+"200.png",width : "32",height: "32"}});
            tgt.firstElementChild.appendChild(lnk);
        } catch (ex) { console.log("fixResult Exception: ",ex); }
    } else console.log("No Success: ",params,ebyid(params.target));
}
var switchableHeaders={id:[{key:"folio",text:"Folio"},{key:"uuid",text:"UUID"}],fecha:[{key:"par",text:"Parcialidad"},{key:"egreso",text:"Egreso"},{key:"crea",text:"Fecha Creación"},{key:"alta",text:"Fecha Captura"}]}; // ,{key:"pago",text:"Fecha Complemento"}
function switchHeadCell(elem) {
    console.log("INI function switchHeadCell");
    if (!elem) { console.log("Error: Missing Elem"); return; }
    let capEl=elem.previousElementSibling;
    let isNext = capEl && clhas(capEl,"paymFixableCaption");
    if (!isNext) {
        capEl=elem.nextElementSibling;
        if (!capEl||!clhas(capEl,"paymFixableCaption")) { console.log("Error: Missing Caption Element"); return; }
    }
    if (!capEl) { console.log("Error: Missing Caption Element"); return; }
    if (!capEl.hasAttribute("arr")) { console.log("Error: Missing arr Attribute"); return; }
    const arrId=capEl.getAttribute("arr");
    if (!switchableHeaders[arrId]) { console.log("Error: Missing switchable property '"+arrId+"'"); return; }
    const arr=switchableHeaders[arrId];
    if (!capEl.hasAttribute("idx")) { console.log("Error: Missing idx Attribute"); return; }
    let idx=capEl.getAttribute("idx");
    if (isNext) {
        idx++;
        if (idx>=arr.length) idx=0;
    } else {
        idx--;
        if (idx<0) idx=arr.length-1;
    }
    capEl.setAttribute("idx", idx);
    const ao=arr[idx];
    capEl.textContent=ao.text;
    console.log("Hiding swh"+arrId);
    cladd(lbycn("swh"+arrId),"hidden");
    console.log("Showing swh"+arrId+ao.key);
    clrem(lbycn("swh"+arrId+ao.key),"hidden");
}
function paymCellSettings() {
    if (!additionalResizeScript) {
        cladd("dialog_resultarea","hScroll");
        additionalResizeScript = function() {
            fee(lbycn("paymFixableCaption"), function(el) {
                console.log("Resizing paymFixableCaption",el);
                if (!el.initWidth) {
                    el.initWidth=el.offsetWidth;
                    console.log("Set initWidth='"+el.initWidth+"'");
                }
                const parEl = el.parentNode;
                let newWidth = parEl.offsetWidth-64; // 7+5+20+20+5+7
                if (newWidth < el.initWidth) newWidth=el.initWidth;
                console.log("Changing Element Width from '"+el.offsetWidth+"' to '"+newWidth+"' (Cell had "+parEl.offsetWidth+"px)");
                el.style.width=""+newWidth+"px";
            });
        };
    }
    additionalResizeScript();
}
var bankData={};
function setBankData(btn,emp,dt,id1,id2,id3,cant) {
    console.log("INI function setBankData");
    if (emp) bankData.emp=emp;
    if (dt) bankData.date=dt;
    if (cant) {
        btn.typ="c";
        bankData.cant=cant;
        if (!id1) cladd(btn,"hidden");
    }
    if (id1) {
        bankData.desc=[id1];
        if (!cant) btn.typ="i";
        btn.idx=-1;
        if (id2 && id2.length>0) {
            bankData.desc.push(id2);
            if (id3 && id3.length>0) bankData.desc.push(id3);
        } else if (!cant) cladd(btn,"hidden");
    }
    console.log("Set Bank Data: ", bankData);
    switchBankData(btn);
}
function switchBankData(btn) {
    let urlqry="";
    if (bankData.emp) urlqry+="&Emp="+bankData.emp;
    if (bankData.date) {
        urlqry+="&FDe="+bankData.date+"&FA="+bankData.date;
    }
    if (bankData.cant && btn.typ==="c") {
        if (bankData.desc) {
            btn.typ="i";
            btn.idx=-1;
        }
        urlqry+="&Cant="+bankData.cant;
        btn.title="Cantidad: "+bankData.cant;
    } else if (bankData.desc && btn.typ==="i") {
        btn.idx++;
        if ((btn.idx + 1) >= bankData.desc.length && bankData.cant) btn.typ="c";
        if (btn.idx >= bankData.desc.length) btn.idx=0;
        if (btn.idx < bankData.desc.length) {
            urlqry+="&Desc="+bankData.desc[btn.idx];
            btn.title="Descripción: "+bankData.desc[btn.idx];
        }
    }
    if (urlqry.length>0) {
        console.log("URLQRY='"+urlqry+"'");
        const mb=ebyid("movimientosbancarios");
        mb.src="<?=$_ConProHost?>/externo/tesoreria/depositos.aspx?<?=$_ConProTest?>"+urlqry;
        const mbDoc = mb.contentWindow.document;
        const mbTbl = mbDoc.getElementById('GridView1');
        if (mbTbl) {
            console.log("A: Rows="+mbTbl.rows.length);
            console.log("B: Rows="+mbTbl.tBodies.rows.length);
        } else console.log("GridView Table not found!");
    } else console.log("NO URLQRY");
}
<?php } ?>
<?php
clog1seq(-1);
clog2end("scripts.reportefactura");