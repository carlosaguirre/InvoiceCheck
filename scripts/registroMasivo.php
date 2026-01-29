<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.registroMasivo");
clog1seq(1);
$esPruebas = in_array(getUser()->nombre, ["admin"]);
$esAdmin=validaPerfil("Administrador");
$esSistemas=$esAdmin||validaPerfil("Sistemas");
$esCuentas=$esSistemas||validaPerfil("Cuentas Bancarias");
$consultaProv=$esSistemas||consultaValida("Proveedor");
$modificaProv=$esSistemas||modificacionValida("Proveedor");
$validaBanco=$esSistemas||validaPerfil("Valida Bancarias");
$validaOpinion=$esSistemas||validaPerfil("Valida Opinion");
?>
var errTimeout=false;
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
        errmsg = "<p class='centered vAlignCenter marginH5'>"+textName+" debe proporcionarse en archivo PDF</p>";
    }
    if (size>2097000) {
        if (!errmsg) errmsg="";
        errmsg+="<p class='centered vAlignCenter marginH5'>El archivo excede el tamaño máximo permitido de 2MB</p>";
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
    if(attributes.id) element = document.getElementById(attributes.id);
    if(!element) {
        element = document.createElement(tagname);
        for (var key in attributes)
            element[key] = attributes[key];
        parentElem.appendChild(element);
    }
    return element;
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
<?php if ($modificaProv || $validaBanco ) { ?>
function setErrorField(text) {
    let errElm=ebyid("errFld");
    ekfil(errElm);
    if(errElm) {
        errElm.appendChild(ecrea({eText:text}));
        errTimeout=setTimeout(function() {
            ebyid("errFld").click();
        }, 4000);
        errElm.onclick=function(event) {
            console.log("removing error message");
            if(errTimeout) clearTimeout(errTimeout);
            fillValue("fileNameFld","");
            clrem(ebyid("fileNameFld"),"hidden");
            let tgt=event.target;
            tgt.onclick=null;
            cladd(tgt,"hidden");
        }
    }
    clrem(errElm,"hidden");
    cladd(ebyid("rolWaitImg"),"hidden");
}
function revisaCuenta(evt,id,rfcprov) {
    let tgt=evt.target;
    let banco=tgt.getAttribute("bank");
    let rfcbanco=tgt.getAttribute("bankrfc");
    let cuenta=tgt.getAttribute("account");
    let archivo=tgt.getAttribute("filename");
    let estado=tgt.getAttribute("verify");
    let estadoStr=tgt.value;
    console.log("INI function revisaCuenta "+id+", "+rfcprov+", "+banco+", "+rfcbanco+", "+cuenta+", "+archivo+", "+estado+", "+estadoStr,tgt);
    let codeId="";
    let codPrvElem=ebyid("code_"+id);
    if(codPrvElem) codeId=codPrvElem.textContent+": ";
<?php if ($modificaProv) {
          $elemValidar=($validaBanco?"{eName:\"SELECT\",id:\"verifyFld\",eChilds:[{eName:\"OPTION\",value:\"0\",text:\"PENDIENTE\"},{eName:\"OPTION\",value:\"1\",text:\"ACEPTADO\"},{eName:\"OPTION\",value:\"-1\",text:\"RECHAZADO\"}]}":"{eName:\"SPAN\",id:\"verifyArea\",defaultText:estadoStr,eText:estadoStr}");
?>
    overlayMessage({eName:"TABLE",id:"ovTable",className:"centered noApply",provId:id,provRfc:rfcprov,eChilds:[{eName:"TR",eChilds:[{eName:"TH",className:"lefted",eText:"BANCO: "},{eName:"TD",className:"lefted",eChilds:[{eName:"INPUT",type:"TEXT",id:"bankFld",className:"width240px",defaultValue:banco,value:banco}]},{eName:"TH",className:"lefted",eText:"RFC BANCO: "},{eName:"TD",className:"lefted",eChilds:[{eName:"INPUT",type:"TEXT",id:"bankRegFld",className:"width240px",defaultValue:rfcbanco,value:rfcbanco}]}]},{eName:"TR",className:"lefted",eChilds:[{eName:"TH",className:"lefted",eText:"CUENTA: "},{eName:"TD",className:"lefted",eChilds:[{eName:"INPUT",type:"TEXT",id:"accountFld",className:"width240px",defaultValue:cuenta,value:cuenta}]},{eName:"TH",className:"lefted",eText:"CARATULA: "},{eName:"TD",className:"lefted",eChilds:[{eName:"INPUT",type:"text",id:"docNameFld",className:"width240px",readOnly:true,defaultValue:archivo,value:archivo},{eName:"IMG",id:"ovDelFileBtn",src:"imagenes/icons/delDoc32.png",className:"wid20px vAlignCenter hidden",onclick:function(){
        //console.log("Eliminar Archivo");
        fillValue("docNameFld","");
        cladd(ebyid("ovPdfVwrRow"),"hidden");
        fillValue("fileNameFld","");
        clrem(ebyid("fileNameFld"),"hidden");
        cladd(ebyid("ovDelFileBtn"),"hidden");
        fillValue("verifyFld","0");
        ekfil(ebyid("verifyArea"));
        addContent("verifyArea","PENDIENTE",0,0);
    }}]}]},{eName:"TR",id:"ovPdfVwrRow",className:"hidden",eChilds:[{eName:"TD",id:"pdfcell",colSpan:"4"}]},{eName:"TR",eChilds:[{eName:"TH",className:"lefted",eText:"ESTADO: "},{eName:"TD",className:"lefted", eChilds:[<?= $elemValidar ?>]},{eName:"TD",className:"lefted",colSpan:"2",eChilds:[{eName:"INPUT",type:"hidden",id:"ovProvRfc",value:rfcprov},{eName:"INPUT",type:"FILE",id:"fileNameFld",className:"vAlignCenter hidden",onchange:function(){
        cladd(ebyid("fileNameFld"),"hidden");
        clrem(ebyid("rolWaitImg"),"hidden");
        let ovtab=ebyid("ovTable");
        let remoteFileName=ovtab.provRfc+"-edocta";
        postService(
            "consultas/Proveedores.php", { command:"loadFile", fileKey:"fkey",remoteName:remoteFileName, fkey:this.files[0]},
            function(text,params,state,status){
                if(state<4&&status<=200)return;
                cladd(ebyid("rolWaitImg"),"hidden");
                if(state==4&&status==200&&text.length>0){
                    try{
                        let jobj=JSON.parse(text);
                        if (jobj.result==="refresh") {
                            location.reload(true);
                        } else if (jobj.result==="error") {
                            setErrorField(jobj.message);
                            console.log("ERROR RESULT: "+jobj.message,params);
                        } else if (jobj.result==="success"){
                            fillValue("docNameFld",jobj.message);
                            let fp=ebyid("pdfcell");
                            ekfil(fp);
                            fp.appendChild(ecrea({eName:"OBJECT",data:"cuentas/docs/tmp-"+jobj.message,type:"application/pdf",width:"95%",height:"300"}));
                            clrem(ebyid("ovPdfVwrRow"),"hidden");
                            clrem(ebyid("ovDelFileBtn"),"hidden");
                        } else {
                            setErrorField("Error en recepción de datos");
                            console.log("NO VALID RESULT: "+text,params);
                        }
                    } catch(ex) {
                        setErrorField("Error de verificación de datos");
                        console.log(ex);
                    }
                } else {
                    console.log("ERROR WITH STATE="+state+", STATUS="+status+", or TEXTLEN="+text.length,params);
                    setErrorField("Error de comunicación al servidor");
                }
            },function(errmsg, parameters, evt) {
                cladd(ebyid("rolWaitImg"),"hidden");
                setErrorField("Error en envío de archivo");
                console.log("Error="+errmsg+", State="+parameters.xmlHttpPost.readyState+", Status="+parameters.xmlHttpPost.status);
            }
        );}},{eName:"IMG",id:"rolWaitImg",src:"imagenes/icons/rollwait2.gif",className:"hei18 hidden"},{eName:"SPAN",id:"errFld",className:"cancelLabel boldValue bgred qblink hidden"}]}]}]},codeId+"EDITAR DATOS BANCARIOS");
    if(archivo&&archivo.length>0) {
        let fp=ebyid("pdfcell");
        ekfil(fp);
        fp.appendChild(ecrea({eName:"OBJECT",data:"cuentas/docs/"+archivo,type:"application/pdf",width:"95%",height:"300"}));
        clrem(ebyid("ovPdfVwrRow"),"hidden");
        clrem(ebyid("ovDelFileBtn"),"hidden");
    }
<?php     if($validaBanco) { ?>
    let valFld=ebyid("verifyFld");
    if(valFld&&estado) {
        valFld.value=estado;
        valFld.defaultValue=estado;
    }
<?php     } ?>
    let closeButton=ebyid("closeButton");
    let closeArea=ebyid("closeButtonArea");
    cladd(closeButton,"hidden");
    while (closeButton.nextSibling) closeArea.removeChild(closeButton.nextSibling);
    closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"CANCELAR",onclick:function(){closeArea.action2=this.value;overlay();}}));
    closeArea.appendChild(ecrea({eText:" "}));
    closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"GUARDAR",onclick:function(){doSave( "edocta");}}));
<?php } else { ?>
    if(archivo&&archivo.length>0) {
        let elem=ebyid("cuenta_"+id);
        if (elem.classList.contains("bgbeige")||elem.classList.contains("bgred")||elem.classList.contains("bggreen")) return;
        overlayMessage([{eName:"H1",eText:banco+". "+rfcbanco},{eName:"H1",eText:cuenta},{eName:"OBJECT",data:"cuentas/docs/"+archivo,type:"application/pdf",width:"95%",height:"300",eChilds:[{eName:"A",href:"cuentas/docs/"+archivo,eText:"Abrir PDF"}]},{eName:"P",classList:"centered vAlignCenter marginH5",eText:"Verificar que los datos coincidan:"}],codeId+"VERIFICAR CUENTA");
        let closeArea=ebyid("closeButtonArea");
        let closeButton=ebyid("closeButton");
        cladd(closeButton,"hidden");
        while (closeButton.nextSibling) closeArea.removeChild(closeButton.nextSibling);
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"CANCELAR",onclick:function(){closeArea.action1=this.value;overlay();}}));
        closeArea.appendChild(ecrea({eText:" "}));
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"RECHAZAR",onclick:function(){closeArea.action1=this.value;overlay();}}));
        closeArea.appendChild(ecrea({eText:" "}));
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"ACEPTAR",onclick:function(){closeArea.action1=this.value;overlay();}}));
    } else {
        overlayMessage({eName:"P",eText:"El usuario no ha capturado sus datos bancarios."},codeId+"SIN DATOS BANCARIOS");
    }
<?php } ?>
    ebyid("overlay").callOnClose=function() {
        let closeArea=ebyid("closeButtonArea");
        console.log("INI callOnClose function"+(closeArea.action1?" Action1="+closeArea.action1:"")+(closeArea.action2?" Action2="+closeArea.action2:""));
        clrem(ebyid("closeButton"),"hidden"); 
        if (closeArea.action1) {
            let verificado=false;
            if (closeArea.action1==="ACEPTAR") verificado="1";
            else if (closeArea.action1==="RECHAZAR") verificado="-1";
            delete closeArea.action1;
            if (verificado!==false) {
                let elem=ebyid("cuenta_"+id);
                elem.classList.add("bgbeige");
                postService("consultas/Proveedores.php",{"command":"verificarProveedor","id":id,"verificado":verificado},function(text,params,state,status) {
                    if (state<4||status<200) {
                        //console.log("State="+state+", Status="+status+" : IGNORED");
                        return;
                    }
                    if (state>4||status>200) {
                        elem.classList.remove("bgbeige");
                        elem.classList.add("bgred");
                        console.log("State="+state+", Status="+status+" : TERMINATED");
                    } else {
                        elem.classList.remove("bgbeige");
                        if(text&&text.length>0) {
                            try {
                                let jobj=JSON.parse(text);
                                let esExito=(jobj.result==="success");
                                if (jobj.result==="refresh") {
                                    location.reload(true);
                                } else if (esExito) {
                                    elem.classList.add("bggreen");
                                    if (verificado==="1") {
                                        elem.value="ACEPTADO";
                                        console.log("REGISTRO ACEPTADO SATISFACTORIAMENTE");
                                    } else if (verificado==="-1") {
                                        elem.value="RECHAZADO"
                                        console.log("REGISTRO RECHAZADO SATISFACTORIAMENTE");
                                    }
                                    elem.setAttribute("bank",banco);
                                    elem.setAttribute("bankrfc",rfcbanco);
                                    elem.setAttribute("account",cuenta);
                                    elem.setAttribute("filename",archivo);
                                    elem.setAttribute("verify",verificado);
                                } else {
                                    elem.classList.add("bgred");
                                    console.log("ERROR (SIN EXITO): "+jobj.message);
                                }
                            } catch (e) {
                                elem.classList.add("bgred");
                                console.log("ERROR (JSON PARSE): "+e.message);
                            }
                        } else {
                            elem.classList.add("bgred");
                            console.log("ERROR. EMPTY RESULT");
                        }
                    }
                    setTimeout(function() {
                        elem.classList.remove("bgred");
                        elem.classList.remove("bggreen");
                    }, 3000);
                }, function(text, parameters, evt) {
                    elem.classList.remove("bgbeige");
                    elem.classList.add("bgred");
                    console.log("POST SERVICE ERROR (STE "+parameters.xmlHttpPost.readyState+", STA "+parameters.xmlHttpPost.status+"): "+text);
                    setTimeout(function() {
                        elem.classList.remove("bgred");
                    }, 3000);
                });
            }
        } else if(closeArea.action2) {
            if (closeArea.action2==="GUARDAR") {
                let elem=ebyid("cuenta_"+id);
                if (closeArea.bank) {
                    elem.setAttribute("bank",closeArea.bank);
                    delete closeArea.bank;
                }
                if (closeArea.bankrfc) {
                    elem.setAttribute("bankrfc",closeArea.bankrfc);
                    delete closeArea.bankrfc;
                }
                if (closeArea.account) {
                    elem.setAttribute("account",closeArea.account);
                    delete closeArea.account;
                }
                if (closeArea.filename) {
                    elem.setAttribute("filename",closeArea.filename);
                    delete closeArea.filename;
                }
                if (closeArea.verify) {
                    elem.setAttribute("verify",closeArea.verify);
                    if (closeArea.verify==="-1") elem.value="RECHAZADO";
                    else if (closeArea.verify==="0") elem.value="PENDIENTE";
                    else if (closeArea.verify==="1") elem.value="ACEPTADO";
                    delete closeArea.verify;
                }
            }
            delete closeArea.action2;
        }
    };
}
function doSave(type) {
    console.log("INI function doSave "+type);
    if (isValidSave(type)) {
        let canSave=false;
        let ovtab=ebyid("ovTable");
        let parameters={command:"saveProvider",id:ovtab.provId,rfc:ovtab.provRfc,type:type};
        let bnkElm=ebyid("bankFld");
        if(bnkElm.value!==bnkElm.defaultValue) {
            parameters.bank=bnkElm.value;
            canSave=true;
        }
        let brfElm=ebyid("bankRegFld");
        if(brfElm.value!==brfElm.defaultValue) {
            parameters.bankrfc=brfElm.value;
            canSave=true;
        }
        let accElm=ebyid("accountFld");
        if(accElm.value!==accElm.defaultValue) {
            parameters.account=accElm.value;
            canSave=true;
        }
        let filElm=ebyid("fileNameFld");
        if(filElm.files.length>0) {
            parameters.file=filElm.files[0];
            canSave=true;
        }
        let verElm=ebyid("verifyFld");
        let vraElm=ebyid("verifyArea");
        if(verElm&&verElm.value!==verElm.defaultValue) {
            parameters.verify=verElm.value;
            canSave=true;
        } else if(vraElm&&vraElm.textContent!==vraElm.defaultText) {
            parameters.verify="0";
            canSave=true;
        }
        //console.log("errFld='"+ebyid("errFld").textContent+"'");
        if (canSave) {
            postService("consultas/Proveedores.php",parameters,function(text,pars,state,status){
                if (state<4&&status<=200) {
                    console.log("SaveProvider Progress State="+state+", Status="+status+", Text="+text);
                    return;
                }
                if (state>4||status>200) {
                    console.log("SaveProvider Error State="+state+", Status="+status+", Text="+text);
                    setOvError("Error de conectividad. ["+state+","+status+"]");
                } else if (text.length==0) {
                    setOvError("Respuesta vac&iacute;a del servidor.");
                } else {
                    //console.log("SaveProvider Complete State="+state+", Status="+status+", Text="+text, parameters);
                    try {
                        let jobj=JSON.parse(text);
                        let esExito=(jobj.result==="success");
                        if (jobj.result==="refresh") {
                            location.reload(true);
                        } else if (esExito) {
                            let closeArea=ebyid("closeButtonArea");
                            closeArea.action2="GUARDAR";
                            if (parameters.bank) closeArea.bank=parameters.bank;
                            if (parameters.bankrfc) closeArea.bankrfc=parameters.bankrfc;
                            if (parameters.account) closeArea.account=parameters.account;
                            if (parameters.verify) closeArea.verify=parameters.verify;
                            if (jobj.filename) closeArea.filename=jobj.filename;
                            overlay();
                        } else {
                            setOvError(jobj.message);
                            console.log(text);
                        }
                    } catch(ex) {
                        setOvError("Error en los datos recibidos.");
                        console.log(ex,text);
                    }
                }
            },function(errmsg, parameters, evt){
                setOvError("Error de conexión: "+errmsg);
                console.log("Saving Provider, connection error. State="+parameters.xmlHttpPost.readyState+", Status="+parameters.xmlHttpPost.status+". Parameters=",parameters);
            });
        } else {
            setOvError("Sin cambios");
        }
    }
}
function isValidSave(type) {
    let errmsg=false;
    switch(type) {
        case "edocta": {
            let bnkFld=ebyid("bankFld");
            if (bnkFld.value.length==0) {
                errmsg="Falta indicar el Banco";
                break;
            }
            let regFld=ebyid("bankRegFld");
            if (regFld.value.length==0) {
                errmsg="Falta indicar el RFC del banco";
                break;
            }
            let accFld=ebyid("accountFld");
            if (accFld.value.length==0) {
                errmsg="Falta indicar la cuenta CLABE";
                break;
            }
            let docFld=ebyid("docNameFld");
            if (docFld.value.length==0) {
                errmsg="Falta indicar carátula de estado de cuenta";
                break;
            }
            break;
        }
        case "opisat": {
            let genFld=ebyid("opinionInicio");
            if (genFld.value.length==0) {
                errmsg="Falta indicar la fecha de revisión";
            }
            break;
        }
    }
    if (errmsg) {
        setOvError(errmsg);
        return false;
    }
    return true;
}
function setOvError(errmsg) {
    let errElem=ebyid("ovErrLine");
    if (!errElem) {
        let dra=ebyid("dialog_resultarea");
        if (dra) dra.insertBefore(ecrea({eName:"H2",id:"ovErrLine",className:"cancelLabel boldValue bgred",eText:errmsg}),dra.firstElementChild);
    } else {
        ekfil(errElem);
        addContent("ovErrLine",ecrea({eText:errmsg}),0,0);
    }
}
<?php } ?>
<?php if ($modificaProv || $validaOpinion ) { ?>
function revisaOpinion(evt,id,rfcprov) {
    let tgt=evt.target;
    let archivo=tgt.getAttribute("filename");
    let estado=tgt.getAttribute("verify");
    let fechagenera=tgt.getAttribute("revision");
    let estadoStr=tgt.value;
    console.log("INI function revisaOpinion "+id+", "+rfcprov+", "+archivo+", "+estado+", "+estadoStr+", "+fechagenera,tgt);
    let codeId="";
    let codPrvElem=ebyid("code_"+id);
    if (codPrvElem) codeId=codPrvElem.textContent+": ";
<?php if ($modificaProv) {
      /* Solo valida opinion */
      } ?>
    if(archivo&&archivo.length>0) {
        let elem=ebyid("opinion_"+id);
        if (elem.classList.contains("bgbeige")||elem.classList.contains("bgred")||elem.classList.contains("bggreen")) return; // En proceso de guardado en la base.
        overlayMessage([{eName:"OBJECT",data:"cuentas/docs/"+archivo+"?v=1",type:"application/pdf",classList:"margintop10",width:"95%",height:"300",eChilds:[{eName:"A",href:"cuentas/docs/"+archivo,eText:"Abrir PDF"}]},{eName:"TABLE",classList:"layauto noApply centered vAlignCenter marginH5",eChilds:[{eName:"TBODY",eChilds:[{eName:"TR",eChilds:[{eName:"TH",eText:"Fecha Revisión: "},{eName:"TD",eChilds:[{eName:"INPUT",type:"text",id:"opinionInicio",name:"opinionInicio",value:fechagenera,className:"calendar centered",readOnly:true,onclick:function(){show_calendar_widget(ebyid('opinionInicio'),'fixExpiredDate');let cw=ebyid('calendar_widget');if(cw)cw.style.zIndex='2501';}}]},{eName:"TH",classList:"padL20",eText:"Vence en 90 días: "},{eName:"INPUT",type:"text",id:"opinionVence",name:"opinionVence",value:"",className:"calendar centered",readOnly:true}]}]}]}],codeId+"VERIFICAR DOCUMENTO");
        fixExpiredDate(null);
        let dtOb=strptime(date_format,ebyid('opinionVence').value);
        let today=new Date();
        today.setHours(0,0,0,0);
        let thirdLeyend="ACEPTAR";
        if(dtOb.getTime()<=today.getTime()) // >
            thirdLeyend="VENCIDO";
        let closeArea=ebyid("closeButtonArea");
        let closeButton=ebyid("closeButton");
        cladd(closeButton,"hidden");
        while (closeButton.nextSibling) closeArea.removeChild(closeButton.nextSibling);
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",classList:"marginV2",value:"CANCELAR",onclick:function(){closeArea.actionO=this.value;closeArea.fechaInicio=ebyid("opinionInicio").value;closeArea.fechaVence=ebyid("opinionVence").value;overlay();}}));
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",classList:"marginV2",value:"RECHAZAR",onclick:function(){closeArea.actionO=this.value;closeArea.fechaInicio=ebyid("opinionInicio").value;closeArea.fechaVence=ebyid("opinionVence").value;overlay();}}));
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",id:"thirdButton",classList:"marginV2",value:thirdLeyend,onclick:function(){closeArea.actionO=this.value;closeArea.fechaInicio=ebyid("opinionInicio").value;closeArea.fechaVence=ebyid("opinionVence").value;overlay();}}));
    } else {
        overlayMessage({eName:"P",eText:"El usuario no ha capturado su documento de Opinión de Cumplimiento."},codeId+"SIN OPINION");
    }
    ebyid("overlay").callOnClose=function() {
        let closeArea=ebyid("closeButtonArea");
        console.log("INI callOnClose"+(closeArea.actionO?" "+closeArea.actionO+" "+id:""));
        let cw=ebyid('calendar_widget');
        if (cw&&cw.style.display==="block") {
            cw.style.display="none";
        }
        clrem(ebyid("closeButton"),"hidden");
        if(closeArea.actionO) {
            let cumplido=false;
            if (closeArea.actionO==="ACEPTAR") cumplido="1";
            else if (closeArea.actionO==="VENCIDO") cumplido="-1";
            else if (closeArea.actionO==="RECHAZAR") cumplido="-2";
            let generaOpinion=closeArea.fechaInicio;
            let venceOpinion=closeArea.fechaVence;
            delete closeArea.actionO;
            delete closeArea.fechaInicio;
            delete closeArea.fechaVence;
            if (cumplido!==false) {
                let elem=ebyid("opinion_"+id);
                elem.classList.add("bgbeige");
                postService("consultas/Proveedores.php",{"command":"opinionProveedor","id":id,"cumplido":cumplido,"generaopinion":generaOpinion,"venceopinion":venceOpinion},function(text,params,state,status) {
                    if (state<4||status<200) {
                        //console.log("State="+state+", Status="+status+" : IGNORED");
                        return;
                    }
                    if (state>4||status>200) {
                        elem.classList.remove("bgbeige");
                        elem.classList.add("bgred");
                        console.log("State="+state+", Status="+status+" : TERMINATED");
                    } else {
                        elem.classList.remove("bgbeige");
                        if(text&&text.length>0) {
                            try {
                                let jobj=JSON.parse(text);
                                let esExito=(jobj.result==="success");
                                if (jobj.result==="refresh") {
                                    location.reload(true);
                                } else if (esExito) {
                                    elem.classList.add("bggreen");
                                    if (cumplido==="1") {
                                        elem.value="ACEPTADO";
                                        console.log("REGISTRO ACEPTADO SATISFACTORIAMENTE");
                                    } else if (cumplido==="-1") {
                                        elem.value="VENCIDO"
                                        console.log("REGISTRO VENCIDO SATISFACTORIAMENTE");
                                    } else if (cumplido==="-2") {
                                        elem.value="RECHAZADO"
                                        console.log("REGISTRO RECHAZADO SATISFACTORIAMENTE");
                                    }
                                    elem.setAttribute("verify",cumplido);
                                    elem.setAttribute("revision",generaOpinion);
                                } else {
                                    elem.classList.add("bgred");
                                    console.log("ERROR (SIN EXITO): "+text); // jobj.message);
                                }
                            } catch (ex) {
                                elem.classList.add("bgred");
                                console.log("ERROR (JSON PARSE): "+text,ex); // e.message);
                            }
                        } else {
                            elem.classList.add("bgred");
                            console.log("ERROR. EMPTY RESULT");
                        }
                    }
                    setTimeout(function() {
                        elem.classList.remove("bgred");
                        elem.classList.remove("bggreen");
                    }, 3000);
                }, function(text, parameters, evt) {
                    elem.classList.remove("bgbeige");
                    elem.classList.add("bgred");
                    console.log("POST SERVICE ERROR (STE "+parameters.xmlHttpPost.readyState+", STA "+parameters.xmlHttpPost.status+"): "+text);
                    setTimeout(function() {
                        elem.classList.remove("bgred");
                    }, 3000);
                });
            }
        }
    }
}
<?php } ?>
function fixExpiredDate(tgt) {
    console.log("INI function fixExpiredDate ",tgt);
    let genDateElem=ebyid("opinionInicio");
    let expDateElem=ebyid("opinionVence");
    if (genDateElem&&expDateElem) {
        let dateObj=strptime(date_format,genDateElem.value);
        dateObj.setDate(dateObj.getDate()+90);
        let btn3=ebyid("thirdButton");
        if (btn3) {
            let todayObj=new Date();
            todayObj=new Date(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate());
            if (dateObj < todayObj)
                btn3.value="VENCIDO";
            else
                btn3.value="ACEPTAR";
        }
        expDateElem.value=strftime(date_format,dateObj);
    }
}
<?php if ($modificaProv) { ?>
function cambiaEstado(id,estado) {
    console.log("INI function cambiaEstado "+id+", "+estado);
    let elem=ebyid("status_"+id);
    if (elem) {
        elem.classList.remove("bgbtn");
        elem.classList.add("bgbeige");
        let optlist=elem.children;
        for(let i=0;i<optlist.length/*>*/;i++) if (optlist[i].value!==estado) optlist[i].classList.add("hidden");
        postService("consultas/Proveedores.php", {command:"fixStatus",id:id,status:estado}, function(text,params,state,status) {
            if (state<4||status<200) return;
            if (state>4||status>200) {
                elem.classList.remove("bgbeige");
                elem.classList.add("bgred");
            } else {
                elem.classList.remove("bgbeige");
                if(text&&text.length>0) {
                    try {
                        let respObj = JSON.parse(text);
                        if (respObj.result && respObj.result==="refresh") {
                            location.reload(true);
                        } else if (respObj.result && respObj.result==="success") {
                            elem.classList.add("bggreen");
                        } else elem.classList.add("bgred");
                    } catch(ex) {
                        elem.classList.add("bgred");
                    }
                } else {
                    elem.classList.add("bgred");
                }
            }
            setTimeout(function() {
                let optlist=elem.children;
                for(let i=0;i<optlist.length/*>*/;i++) optlist[i].classList.remove("hidden");
                if (elem.classList.contains("bgred")) {
                    elem.value=elem.getAttribute("baseValue");
                    elem.classList.remove("bgred");
                } else if (elem.classList.contains("bggreen")) {
                    elem.setAttribute("baseValue",elem.value);
                    elem.classList.remove("bggreen");
                }
                elem.classList.add("bgbtn"); 
            }, 3000);
        });
    }
}
<?php } ?>
var locked=false;
function goto(key,val) {
    if (locked) return;
    locked=true;
    let frp=ebyid("forma_reg_prv");
    if (!frp) return;
    let hidObj={eName:"INPUT",type:"hidden"};
    switch(key) {
        case "pageSwitch":
            frp.appendChild(ecrea({...hidObj, ...{name:key,value:val}}));
            let rpp=ebyid("regPerPage");
            if(rpp)
                frp.appendChild(ecrea({...hidObj, ...{name:"regPerPage",value:rpp.value}}));
            frp.appendChild(ecrea({...hidObj, ...{name:"prov_browse",value:key}}));
            frp.elements["prov_return"].value="";
            //console.log("prov_return",frp.prov_return);
            if (validateForm(frp))
                frp.submit();
            break;
        case "regPerPage":
            frp.appendChild(ecrea({...hidObj, ...{name:key,value:val}}));
            let psw=ebyid("pageSwitch");
            if(psw)
                frp.appendChild(ecrea({...hidObj, ...{name:"pageSwitch",value:psw.value}}));
            frp.appendChild(ecrea({...hidObj, ...{name:"prov_browse",value:key}}));
            frp.elements["prov_return"].value="";
            if (validateForm(frp))
                frp.submit();
            break;
    }
}
function validateForm(ev) {
<?php /*if ($esPruebas) { ?>
    if (!(ev instanceof Event) && !(ev instanceof HTMLFormElement)) {
        console.log("INVALID ARG",ev);
        return eventCancel();
    }
    const f=new FormData(ev instanceof Event?ev.target:ev);
    let o={};
    for(let e of f.entries()){
        o[e[0]]=e[1];
    }
    if (ev.submitter) o[ev.submitter.name]=ev.submitter.value;
    //console.dir(o);
    return eventCancel(ev);
<?php } else*/ echo "return true;\n";?>
}
function getHiddenObject(properties) {
    let newObj={eName:'INPUT',type:'hidden'};
}
<?php
clog1seq(-1);
clog2end("scripts.registroMasivo");
