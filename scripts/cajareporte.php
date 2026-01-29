<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.cajareporte");
clog1seq(1);
$esUsuario=hasUser();
$esAdmin=$esUsuario&&validaPerfil("Administrador");
$esSistemas=$esUsuario&&validaPerfil("Sistemas")||$esAdmin;
$esReporte=$esUsuario&&validaPerfil("Caja Reporte")||$esSistemas;
$esRespalda=$esUsuario&&validaPerfil("Caja Respaldo")||$esSistemas;
if ($esRespalda) {
?>
const draDif=132; // piePagina(30)+espacioSuperior(20)+espacioInferior(24)+bordeSuperior(2)+bordeInferior(2)-overlayTitle(24)-overlayButtons(30) = 132
const scrDif=192.4; // draDif(132)+margenSuperiorH1(20)+margenInferiorH1(10)+alturaTextoH1(30.4)
function respaldar(loopAction) {
    const respBtn=ebyid("respaldarBtn");
    if (!respBtn) return;
    const ovy=ebyid("overlay");
    const dbx=ebyid("dialogbox");
    const dra=ebyid("dialog_resultarea");
    let scr=ebyid("scroll_resultarea");
    let rsl=ebyid("resultList");
    let pi1=ebyid("progImg1");
    let pi2=ebyid("progImg2");
    if (respBtn.disabled&&!loopAction) return;
    console.log("INI function respaldar"+(loopAction?" LOOP":""));
    respBtn.disabled=true;
    if (respBtn.idList&&respBtn.idList.length>0) {
        if (!('idListCurrentIndex' in respBtn)) respBtn.idListCurrentIndex=0;
        if (respBtn.idListCurrentIndex >= respBtn.idList.length) {
            // ToDo: Despliega resumen;
            console.log("RESUMEN: "+respBtn.idListCurrentIndex);
            if (scr) {
                scr.appendChild(ecrea({eName:"P",eText:"SE COMPLETÓ EL CICLO DE RESPALDO PARA "+respBtn.idListCurrentIndex+" REGISTRO"+(respBtn.idListCurrentIndex!=1?"S":"")}));
                scr.scrollTop=scr.lastElementChild.offsetTop;
                scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
            }
            if (pi1) pi1.src="imagenes/ledgreen.gif";
            if (pi2) pi2.src="imagenes/ledgreen.gif";
            dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
            if (respBtn.disabled) setTimeout(function(tgt) { tgt.disabled=false; }, 1500, respBtn);
            respBtn.idListCurrentIndex=0;
            return;
        }
        const currentValue=respBtn.idList[respBtn.idListCurrentIndex];
        const currentType=currentValue.slice(0,1).toLowerCase();
        const currentId=currentValue.slice(1);
        const parameters = { accion:"transferFiles", tipo:currentType, listaIds:currentId };
        respBtn.idListCurrentIndex++;
        if (!loopAction) {
            const hd1=ecrea({eName:"H1",eChilds:[{eName:"IMG",id:"progImg1",src:"imagenes/ledyellow.gif"},{eText:"\u00A0 RESPALDO DE ARCHIVOS XML Y PDF \u00A0"},{eName:"IMG",id:"progImg2",src:"imagenes/ledyellow.gif"}]});
            const draHgt=document.body.clientHeight-draDif;
            const scraMxHgt=document.body.clientHeight-scrDif;
            console.log("bodyClientHeight="+document.body.clientHeight+", ScrollResultAreaMaxHeight="+scraMxHgt);
            scr=ecrea({eName:"DIV",id:"scroll_resultarea",style:"max-height:"+scraMxHgt+"px;overflow:hidden auto;",eChilds:[{eName:"OL",id:"resultList"}]});
            overlayMessage([hd1,scr],"MONITOR DE RESPALDOS");
            rsl=ebyid("resultList");
            pi1=ebyid("progImg1");
            pi2=ebyid("progImg2");
            //cladd(dra,"hScroll");
            dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
            scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
        }
        postService("consultas/CajaChica.php",parameters,
            function(text, parameters, state, status){
                if(state<4&&status<=200) return;
                const tipo=(parameters.tipo==="v"?"Viáticos":(parameters.tipo==="c"?"Caja Chica":"DESCONOCIDO"));
                if(text.length==0) {
                    console.log("STATE: ",state,"\nSTATUS: ",status,"\nPARAMETERS: ",parameters,"\nText: VACIO");
                    if(scr) {
                        // scr.appendChild(ecrea({eName:"P",eText:"Error de conexión, reintente respaldar más tarde."}));
                        if (rsl) {
                            lineObj={eName:"LI",eChilds:[{eName:"P",eText:"Reembolso de "+tipo+" folio "+parameters.listaIds},{eName:"P",eText:"Error de conexión, reintente respaldar más tarde."}]};
                            rsl.appendChild(ecrea(lineObj));
                            scr.scrollTop=rsl.lastElementChild.offsetTop;
                        }
                        if (pi1) pi1.src="imagenes/ledred.gif";
                        if (pi2) pi2.src="imagenes/ledred.gif";
                        scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
                    }
                    dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
                    if (respBtn.disabled) setTimeout(function(tgt) { tgt.disabled=false; }, 1500, respBtn);
                    return;
                }
                try {
                    const jobj=JSON.parse(text);
                    if (jobj.result==="refresh") {
                        location.reload(true);
                    } else if (jobj.result==="error") {
                        console.log(parameters,jobj);
                        if (jobj.message) {
                            if (scr) {
                                //scr.appendChild(ecrea({eName:"P",eText:jobj.message}));
                                if (rsl) {
                                    lineObj={eName:"LI",eChilds:[{eName:"P",eText:"Reembolso de "+tipo+" folio "+parameters.listaIds},{eName:"P",eText:jobj.message}]};
                                    rsl.appendChild(ecrea(lineObj));
                                    scr.scrollTop=rsl.lastElementChild.offsetTop;
                                }
                                if (pi1) pi1.src="imagenes/ledorange.gif";
                                if (pi2) pi2.src="imagenes/ledorange.gif";
                                scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
                            }
                            dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
                        }
                    } else if (jobj.result==="exito") {
                        console.log(parameters,jobj);
                        const logArr=[];
                        /* if (jobj.log) {
                            jobj.log.forEach(function(msg) {
                                if (logArr.length>0) logArr.push({eName:"BR"});
                                logArr.push({eText:msg});
                            });
                        } */
                        if (jobj.filelist) {
                            jobj.filelist.forEach(function(msg){
                                if (logArr.length>0) logArr.push({eName:"BR"});
                                const slashIdx=msg.lastIndexOf("/");
                                if (slashIdx>=0) msg=msg.slice(slashIdx+1);
                                logArr.push({eText:"TRANSFERENCIA EXITOSA: "+msg});
                            });
                        }
                        if (scr) {
                            //scr.appendChild(ecrea({eName:"P",eText:msg}));
                            if (rsl) {
                                lineObj={eName:"LI",classList:"lefted",eChilds:[{eName:"P",eText:"Reembolso de "+tipo+" folio "+parameters.listaIds},{eName:"P",eChilds:logArr}]};
                                rsl.appendChild(ecrea(lineObj));
                                scr.scrollTop=rsl.lastElementChild.offsetTop;
                            }
                            if (pi1) pi1.src="imagenes/ledyellow.gif";
                            if (pi2) pi2.src="imagenes/ledyellow.gif";
                            scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
                        }
                        dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
                    }
                    respaldar(true);
                } catch(ex) {
                    console.log(parameters,text,ex);
                    if(scr) {
                        //scr.appendChild(ecrea({eName:"P",eText:"Datos de Transferencia incompletos. Consulte con el administrador."}));
                        if (rsl) {
                            lineObj={eName:"LI",eChilds:[{eName:"P",eText:"Reembolso de "+tipo+" folio "+parameters.listaIds},{eName:"P",eText:"Datos de Transferencia incompletos. Consulte al administrador."}]};
                            rsl.appendChild(ecrea(lineObj));
                            scr.scrollTop=rsl.lastElementChild.offsetTop;
                        }
                        if (pi1) pi1.src="imagenes/ledred.gif";
                        if (pi2) pi2.src="imagenes/ledred.gif";
                        scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
                    }
                    dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
                    if (respBtn.disabled) setTimeout(function(tgt) { tgt.disabled=false; }, 1500, respBtn);
                }
            },function(errmsg, parameters, evt){
                console.log(errmsg, parameters, evt);
                const tipo=(parameters.tipo==="v"?"Viáticos":(parameters.tipo==="c"?"Caja Chica":"DESCONOCIDO"));
                if(scr) {
                    //scr.appendChild(ecrea({eName:"P",eText:"El servidor no responde. Consulte con el administrador."}));
                    if (rsl) { // scr: ebyid("scroll_resultarea"), rsl: ebyid("resultList")
                        lineObj={eName:"LI",eChilds:[{eName:"P",eText:"Reembolso de "+tipo+" folio "+parameters.listaIds},{eName:"P",eText:"El servidor no responde. Consulte al administrador.",ondblclick:appendErrorMessageProperty,errorDetail:{eName:"SPAN",className:"marL4 darkRedLabel",eText:errmsg}}]};
                        rsl.appendChild(ecrea(lineObj));
                        scr.scrollTop=rsl.lastElementChild.offsetTop;
                    }
                    if (pi1) pi1.src="imagenes/ledred.gif";
                    if (pi2) pi2.src="imagenes/ledred.gif";
                    scr.style.maxHeight=(document.body.clientHeight-scrDif)+"px";
                }
                dra.style.maxHeight=(document.body.clientHeight-draDif)+"px";
                if (respBtn.disabled) setTimeout(function(tgt) { tgt.disabled=false; }, 1500, respBtn);
            }
        );
    } else {
        console.log("empty list");
        if (respBtn.disabled) setTimeout(function(tgt) { tgt.disabled=false; }, 1500, respBtn);
    }
}
<?php
} ?>
function doIdEmptyCheck(evt) {
    const tgt=evt.target;
    //console.log("INI function doIdEmptyCheck '"+tgt.value+"'");
    const empresaElem=ebyid("bempresa");
    const statusElem=ebyid("status");
    const istElem=ebyid("inverseStatusTrigger");
    const beneficiarioElem=ebyid("beneficiario");
    const tipofechaElem=ebyid("tipofecha");
    const fechaIniElem=ebyid("fechaIni");
    const fechaFinElem=ebyid("fechaFin");
    if (tgt.value.length==0) {
        if (empresaElem.disabled) {
            empresaElem.disabled=false;
            clrem(empresaElem,"disabled");
        }
        if (statusElem.disabled) {
            statusElem.disabled=false;
            clrem(statusElem,"disabled");
        }
        if (istElem.disabled) {
            istElem.disabled=false;
            clrem(istElem,"disabled");
        }
        if (beneficiarioElem.disabled) {
            beneficiarioElem.disabled=false;
            clrem(beneficiarioElem,"disabled");
        }
        if (tipofechaElem.disabled) {
            tipofechaElem.disabled=false;
            clrem(tipofechaElem,"disabled");
        }
        if (fechaIniElem.disabled) {
            fechaIniElem.disabled=false;
            clrem(fechaIniElem,"disabled");
        }
        if (fechaFinElem.disabled) {
            fechaFinElem.disabled=false;
            clrem(fechaFinElem,"disabled");
        }
    } else {
        empresaElem.disabled=true;
        cladd(empresaElem,"disabled");
        statusElem.disabled=true;
        cladd(statusElem,"disabled");
        istElem.disabled=true;
        cladd(istElem,"disabled");
        beneficiarioElem.disabled=true;
        cladd(beneficiarioElem,"disabled");
        tipofechaElem.disabled=true;
        cladd(tipofechaElem,"disabled");
        fechaIniElem.disabled=true;
        cladd(fechaIniElem,"disabled");
        fechaFinElem.disabled=true;
        cladd(fechaFinElem,"disabled");
    }
}
function buscar(evt) {
    const tgt=evt.target;
    if (tgt.disabled) return;
    tgt.disabled=true;
<?php if ($esRespalda) { ?>
    cladd(ebyid("respaldarBtn"),"hidden");
<?php } ?>
    const folio=ebyid("bfolio").value;
    const empresaElem=ebyid("bempresa");
    const empresaId=empresaElem.disabled?"todas":empresaElem.value;
    const tipofechaElem=ebyid("tipofecha");
    const tipofecha=tipofechaElem.disabled?"solicitud":tipofechaElem.value;
    const tipo=ebyid("tipo").value;
    const statusElem=ebyid("status");
    const status=statusElem.disabled?"todos":statusElem.value;
    const statusModifier=clhas(statusElem,"inverse")&&!statusElem.disabled?"NOT":"";
    const beneficiarioElem=ebyid("beneficiario");
    const beneficiario=beneficiarioElem.disabled?"":beneficiarioElem.value;
    const fechaIniElem=ebyid("fechaIni");
    const fechaFinElem=ebyid("fechaFin");
    const fechaIni=fechaIniElem.disabled?"":fechaIniElem.value;
    const fechaFin=fechaFinElem.disabled?"":fechaFinElem.value;
    const parameters = { accion:"generaReporte", folio:folio, empresaId:empresaId, tipofecha:tipofecha, tipo:tipo, status:status, statusModifier:statusModifier, beneficiario:beneficiario, fechaIni:fechaIni, fechaFin:fechaFin, target:tgt };
    console.log("PARAMETERS: ",parameters);
    postService("consultas/CajaChica.php", parameters, resultado, error);
    const resultadoElem=ebyid("resultado");
    cladd(resultadoElem,"hidden");
    ekfil(resultadoElem);
    resultadoElem.parentNode.appendChild(ecrea({eName:"DIV",id:"waitingImage",className:"centered",eChilds:[{eName:"IMG",src:"imagenes/icons/rollwait2.gif"}]}));
}
function resultado(text, parameters, state, status) {
    if(state<4&&status<=200) return;
    if (parameters && parameters.target && parameters.target.disabled) setTimeout(function(tgt) { tgt.disabled=false; }, 1500, parameters.target);
    ekil(ebyid("waitingImage"));
    if(text.length==0) {
        console.log("STATE: ",state, "\nSTATUS: ", status, "\nPARAMETERS: ", parameters, "\nText: VACIO");
        return;
    }// else console.log("PARAMETERS: ", parameters, "\nTEXT: ",text);
    //console.log("LANGUAGE = '"+navigator.language+"' - ",navigator.languages);
    try {
        const jobj=JSON.parse(text);
        if (jobj.log) console.log("Log: "+jobj.log);
        if (jobj.result==="error") {
            console.log("jobj result is error", jobj);
            if (!parameters.modoImpresion) {
                if (jobj.focuselem) showError(jobj.message, focuselem);
                else showError(jobj.message);
            }
        } else if (jobj.result==="refresh") {
            location.reload(true);
        } else if (jobj.result==="exito") {
            fee(lbycn("resetable"),(elem)=>{
                if (!elem.value) elem.value="";
                elem.oldValue=elem.value;
            });
            //clrem(ebyid("printBtn"),"hidden");
            const resultadoElem=ebyid("resultado");
            if (jobj.query) console.log(jobj.query);
            //console.log("Columnas = ", jobj.columnas);
            let numReg=jobj.datos.length;
            let sumTot=jobj.sumTotal;
            let cellClasses="";
            if (parameters.modoImpresion) {
                cellClasses="nohover";
            }
            const tableProps = {className:"doApply"};
            //if (!parameters.modoImpresion) tableProps.className+=" breakAvoidI";
            const rowProps = {
                onbuild:function(rowO) {
                    if (rowO.index.slice(0,1)==="B") {
                        //console.log("OnBuild+ "+rowO.index); 
                        const idx=rowO.index.slice(2);
                        if (jobj.datos[+idx].html) rowO.html=jobj.datos[+idx].html;
                        else console.log(" - no html");
                        if (jobj.datos[+idx].tipo)
                            rowO.tipo=jobj.datos[+idx].tipo.toUpperCase();
                        else if (parameters.tipo)
                            rowO.tipo=parameters.tipo.toUpperCase();
                        else console.log(" - no tipo");
                    } //else console.log("OnBuild- "+rowO.index);
                },
                onappend:function(rowO,celO) {
                    if (celO) {
                        if (!celO.className) celO.className=cellClasses;
                        if (rowO&&rowO.index) {
                            if (rowO.index==="H" && celO.eText) {
                                celO.eText=celO.eText.toUpperCase();
                                celO.className+=(celO.className.length>0?" ":"")+"centered";
                            }
                            //celO.className+=(celO.className.length>0?" ":"")+"rowidx"+rowO.index;
            }   }   }   };
            rowProps.className="noApply";
            if (parameters.modoImpresion) rowProps.className+=" nohover breakAvoidI";
            const cellProps = {
                onbuild:function(tgt) {
                    if (tgt) {
                        if (!tgt.className) tgt.className="";
                        //appendLog("INI onbuild function "+JSON.stringify(tgt)+"\n");
                        if (this.currentKey) {
                            //appendLog("            currentKey="+this.currentKey+"\n");
                            switch(this.currentKey) {
                                case "total":
                                    tgt.className+=(tgt.className.length>0?" ":"")+"padr5 righted inputCurrency noInput relative";
                                    break;
                            }
                            delete this.currentKey;
                        }
                        if (tgt.index) {
                            //if (tgt.index.slice(0,1)==="B") tgt.className+=(tgt.className.length>0?" ":"")+"pointer";
                            //tgt.className+=" tgtidx"+tgt.index.slice(1);
                }   }   },
                ongetvalue:function(obj,key) {
                    //appendLog("INI ongetvalue function\n");
                    this.currentKey=key;
                    switch(key) {
                        case "fechasolicitud":
                        case "fechapago":
                        case "solicitud":
                        case "pago":
                            const cutText=obj[key].slice(0,10);
                            const dateObj=strptime("%Y-%m-%d",cutText);
                            const fixedVal=strftime(date_format,dateObj);
                            return fixedVal;
                            break;
                        case "monto":
                        case "total":
                            if (!obj[key]) obj[key]=0;
                            const val=obj[key];
                            const flv=parseFloat(val);
                            const lfx=flv.toLocaleString('en-US',{minimumFractionDigits: 2, maximumFractionDigits: 2});
                            //console.log("RECORD["+key+"]="+val+" => "+flv+" => "+lfx);
                            return [{eName:"SPAN",className:"abs lft4",eText:"$"},{eText:lfx}];
                            break;
                        default: return obj[key];
                    }
                },
                onmissedkey:function(obj,key) {
                    //appendLog("INI onmissedkey function\n");
                    this.currentKey=key;
                    if (key==="status") {
                        if ((obj.pagadopor && obj.pagadopor.length>0)||
                            (obj.pagadoPor && obj.pagadoPor.length>0)||
                            obj.status.slice(0,6)==="PAGADO")
                            return "PAGADO";
                        if ((obj.autorizadopor && obj.autorizadopor.length>0)||
                            (obj.autorizadoPor && obj.autorizadoPor.length>0)||
                            obj.status.slice(0,8)==="AUTORIZO")
                            return "AUTORIZO "+obj.autorizadopor;
                        if ((obj.rechazadopor && obj.rechazadopor.length>0)||
                            (obj.rechazadoPor && obj.rechazadoPor.length>0)||
                            obj.status.slice(0,7)==="RECHAZO")
                            return "RECHAZO "+obj.rechazadopor;
                        return "PENDIENTE";
                    }
                    return false;
            }   };
            if (!parameters.modoImpresion) {
                cellProps.onclick=function(event){
                    const tgt=event.target?event.target:false;
                    if (tgt && tgt.index && tgt.index.slice(0,1)==="B") { // es TD
                        const parn=tgt.parentNode; // TR
                        const parch=parn.nextElementSibling; // next row
                        if (clhas(parch,"archivos")) {
                            if (clhas(parch,"hidden")) {
                                fee(lbycn("archivos"),elem=>cladd(elem,"hidden"));
                                clrem(parn,"zoomIn");
                                cladd(parn,"zoomOut");
                            } else {
                                clrem(parn,"zoomOut");
                                cladd(parn,"zoomIn");
                            }
                            clfix(parch,"hidden");
                            //parn
                        } else {
                            let rowObj=tgt;
                            while(rowObj&&rowObj.tagName!=="TR") rowObj=rowObj.parentNode;
                            if (rowObj && rowObj.html) {
                                overlayClose();
                                let titulo="DETALLE";
                                if (rowObj.tipo) titulo+=" "+rowObj.tipo;
                                overlayMessage(rowObj.html,titulo);
                                const ra=ebyid("registro_actual");
                                const ft=ebyid("files");
                                if (ra&&ft) ft.style.width=ra.offsetWidth+"px";
                }   }   }   };
            }
            const tableObj = arrayToHTMLTableObject(
                jobj.datos,
                jobj.columnas,
                tableProps,
                rowProps,
                cellProps
            );
            const tableElem=ecrea(tableObj);
            resultadoElem.appendChild(tableElem);
            const tblHdrElem=tableElem.firstElementChild;
            cladd(tblHdrElem,"asTBody"); // ,"asTHead"); // 
            const tblBdyElem=tblHdrElem.nextElementSibling;
            fee(tblBdyElem.children,(row,rwi) => {
                //row.className="noApply"; // pointer en lugar de zoomIn si no tiene facturas
                //if (parameters.modoImpresion) row.className+=" nohover"; // sin interaccion al imprimir
                // else row.classname+=" breakAvoidI"; // no hace falta, ya se puso en la tabla para cada renglon
                const rwn=(rwi+1);
                //const ptIdx=row.index.indexOf(".",2);
                const idx=row.index.slice(2); //,ptIdx);
                //console.log("RowIdx="+row.index+", Idx="+idx); // PtIdx="+ptIdx+", 
                const jdiArch=jobj.datos[+idx]?jobj.datos[+idx].archivos:null;
                const jdiHtml=jobj.datos[+idx]?jobj.datos[+idx].html:null;
                const jdiTipo=jobj.datos[+idx].tipo?jobj.datos[+idx].tipo.toUpperCase():parameters.tipo?parameters.tipo.toUpperCase():null;
                if (jdiArch && jdiArch.length>0) {
                    //console.log("Record "+rwn+", idx:"+idx+" with "+jdiArch.length+" invoices");
                    let idArchList=[];
                    let xmlArchList=[];
                    let pdfArchList=[];
                    let totArchList=[];
                    if (!parameters.modoImpresion) {
                        clrem(row,"zoomOut");
                        cladd(row,"zoomIn");
                        idArchList.push({eName:"P",className:"righted marbtm2 boldValue bbtm1d noprint",eText:"#"});
                        xmlArchList.push({eName:"P",className:"centered marbtm2 boldValue bbtm1d noprint",eText:"Documento\u00A0XML"});
                        pdfArchList.push({eName:"P",className:"centered marbtm2 boldValue bbtm1d noprint",eText:"Documento\u00A0PDF"});
                        totArchList.push({eName:"P",className:"centered marbtm2 boldValue bbtm1d noprint",eText:"Total Fact."});
                    }
                    for(let i=0;i< jdiArch.length;i++) {
                        const n=(i+1);
                        let prgObj={eName:"P",className:"righted nobottommargin jdi_"+rwn+"_"+n,rowIdx:rwn,pIdx:n,eText:""+n};
                        if (parameters.modoImpresion) {
                            prgObj.className+=" blacked";
                        } else {
                            prgObj.onmouseover=jdilit;
                            prgObj.onmouseout=jdiunlit;
                        }
                        idArchList.push(prgObj);
                        let fname=jdiArch[i][1];
                        if (fname) {
                            let slashIdx=fname.lastIndexOf("/");
                            if (slashIdx>=0) fname=fname.slice(slashIdx+1);
                            if (fname.length>34) {
                                const filebase=fname.slice(0,-4);
                                const fileext=fname.slice(-3);
                                fname=filebase.slice(0,27)+"..."+fileext;
                            }
                        } else fname="\u00A0";
                        prgObj={eName:"P",className:"righted nobottommargin jdi_"+rwn+"_"+n,rowIdx:rwn,pIdx:n,eText:fname};
                        if (parameters.modoImpresion) {
                            prgObj.className+=" blacked";
                        } else {
                            prgObj.onmouseover=jdilit;
                            prgObj.onmouseout=jdiunlit;
                        }
                        xmlArchList.push(prgObj);
                        fname=jdiArch[i][2];
                        if (fname) {
                            let slashIdx=fname.lastIndexOf("/");
                            if (slashIdx>=0) fname=fname.slice(slashIdx+1);
                            if (fname.length>34) {
                                const filebase=fname.slice(0,-4);
                                const fileext=fname.slice(-3);
                                fname=filebase.slice(0,27)+"..."+fileext;
                            }
                        } else fname="\u00A0";
                        prgObj={eName:"P",className:"righted nobottommargin jdi_"+rwn+"_"+n,rowIdx:rwn,pIdx:n,eText:fname};
                        if (parameters.modoImpresion) {
                            prgObj.className+=" blacked";
                        } else {
                            prgObj.onmouseover=jdilit;
                            prgObj.onmouseout=jdiunlit;
                        }
                        pdfArchList.push(prgObj);
                        const flv=parseFloat(jdiArch[i][3]);
                        const ftot=flv.toLocaleString('en-US',{minimumFractionDigits: 2, maximumFractionDigits: 2});
                        prgObj={eName:"P",className:"righted inputCurrency noInput relative nobottommargin jdi_"+rwn+"_"+n,rowIdx:rwn,pIdx:n,eChilds:[{eName:"SPAN",className:"abs lft4",rowIdx:rwn,pIdx:n,eText:"$"},{eName:"P",className:"nobottommargin",rowIdx:rwn,pIdx:n,eText:ftot}]};
                        if (parameters.modoImpresion) {
                            prgObj.className+=" blacked";
                        } else {
                            prgObj.onmouseover=jdilit;
                            prgObj.onmouseout=jdiunlit;
                        }
                        totArchList.push(prgObj);
                    }
                    const numcols=row.children.length;
                    const searchCell={eName:"TD",className:"nohover notbefore notafter"};
                    if (!parameters.modoImpresion)
                        searchCell.eChilds=[{eName:"IMG",src:"imagenes/searchicon18.png",className:"btnTab noprint",html:jdiHtml,tipo:jdiTipo,onclick:function(event){
                            let tgt=event.target?event.target:false;
                            if (tgt && tgt.html) {
                                overlayClose();
                                let titulo="DETALLE";
                                if (tgt.tipo) titulo+=" "+tgt.tipo;
                                overlayMessage(tgt.html,titulo);
                                const ra=ebyid("registro_actual");
                                const ft=ebyid("files");
                                if (ra&&ft) ft.style.width=ra.offsetWidth+"px";
                                eventCancel(event);
                            }
                        },onmouseover:function(event){this.src="imagenes/searchicon18h.png"},onmouseout:function(event){this.src="imagenes/searchicon18.png"}}];
                    const rowObj={eName:"TR",className:"archivos noApply idx"+row.index,eChilds:[{eName:"TD",className:"nohover notbefore notafter",colSpan:(""+(numcols-5)),eText:"\u00A0"},{eName:"TD",className:"nohover notbefore notafter pad3",eChilds:idArchList},{eName:"TD",className:"nohover notbefore notafter pad3 printVBorder printLBorder",eChilds:[{eName:"SPAN",className:"tab centered shrinkCol",eChilds:xmlArchList}]},{eName:"TD",className:"nohover notbefore notafter pad3 printVBorder",eChilds:[{eName:"SPAN",className:"tab centered shrinkCol",eChilds:pdfArchList}]},{eName:"TD",className:"nohover notbefore notafter pad3 printVBorder printRBorder",eChilds:[{eName:"SPAN",className:"tab shrinkCol",eChilds:totArchList}]},searchCell]};
                    if (parameters.modoImpresion) {
                        rowObj.className+=" nohover breakAvoidI";
                    } else {
                        rowObj.className+=" litYellow doPrint";
                        rowObj.onclick=function(event) {
                            console.log("HIDE ",this);
                            cladd(this,"hidden");
                            clrem(this.previousElementSibling,"zoomOut");
                            cladd(this.previousElementSibling,"zoomIn");
                        };
                        rowObj.className="hidden zoomOut "+rowObj.className;
                    }
                    tblBdyElem.insertBefore(ecrea(rowObj), row.nextElementSibling);
                } else if (!parameters.modoImpresion) {
                    console.log("Record "+rwn+", idx:"+idx+" with zero invoices");
                    clrem(row,"zoomOut");
                    clrem(row,"zoomIn");
                    cladd(row,"pointer");
                }
            });
            clrem(resultadoElem,"hidden");
            //console.log("STATUS = "+parameters.status);
            const nre=ebyid("numReg");
            const ste=ebyid("sumTot");
            const pluralSuffix=(numReg!=1?"s":"");
            nre.textContent=""+numReg+" registro"+pluralSuffix+" encontrado"+pluralSuffix;
            ste.textContent="$ "+sumTot.toLocaleString('en-US',{minimumFractionDigits: 2, maximumFractionDigits: 2})+" : SUMA TOTAL";
            clrem(nre,"hidden");
            clrem(ste,"hidden");
<?php if ($esRespalda) { ?>
            const fullStatus=((parameters.statusModifier?parameters.statusModifier+" ":"")+parameters.status).toLowerCase();
            console.log("STATUS: "+fullStatus+(parameters.modoImpresion?" modoImpresion":" modoNormal"));
            if (!parameters.modoImpresion&&fullStatus!=="not todos"&&fullStatus!=="pendiente"&&fullStatus!=="not aceptado"&&fullStatus!=="rechazado") {
                const respBtn=ebyid("respaldarBtn");
                console.log("RESET ID LIST");
                respBtn.idList=[];
                //let count=0;
                jobj.datos.forEach(function(obj) {
                    //count++;
                    if ((obj.autorizadopor && obj.autorizadopor.length>0)||(obj.autorizadoPor && obj.autorizadoPor.length>0)||obj.status.slice(0,8)==="AUTORIZO"||obj.status.slice(0,8)==="RESPALDA") {
                        const respId=(obj.tipo?obj.tipo:parameters.tipo).slice(0,1).toLowerCase()+obj.folio;
                        //console.log("ADDING ID TO LIST "+respId);
                        respBtn.idList.push(respId);
                    } else console.log("NO AÑADIDO: ", obj);
                });
                if (respBtn.idList.length>0) clrem(respBtn,"hidden");
            } else console.log(parameters);
<?php } ?>
        } else {
            console.log("OTHER: ", jobj);
            showError(jobj.message, false, jobj.result);
        }
    } catch(ex) {
        console.log("Exception caught: ", ex, "\nText: '"+text+"'");
        showError(ex);
    }
}
function jdilit(event) {
    if (event) {
        const tgt=event.target;
        if (tgt) {
            const rowIdx=tgt.rowIdx;
            const pIdx=tgt.pIdx;
            if(rowIdx&&pIdx) {
                const jdiclass="jdi_"+rowIdx+"_"+pIdx;
                fee(lbycn(jdiclass),elem=>cladd(elem,"bgyellow"));
            }
        }
    }
}
function jdiunlit(event) {
    if (event) {
        const tgt=event.target;
        if (tgt) {
            const rowIdx=tgt.rowIdx;
            const pIdx=tgt.pIdx;
            if(rowIdx&&pIdx) {
                const jdiclass="jdi_"+rowIdx+"_"+pIdx;
                fee(lbycn(jdiclass),elem=>clrem(elem,"bgyellow"));
            }
        }
    }
}
function doReset(evt) {
    const tgt=evt.target;
    //console.log("INI function doReset "+tgt.id+": '"+tgt.value+"' ('"+tgt.oldValue+"')");
    const isReset=clhas("resultado","hidden");
    let sameValues=true;
    let review="";
    fee(lbycn("resetable"),(elem)=>{
        if (review.length>0) review+=", ";
        review+=elem.id+": ";
        if (elem.value!==null && elem.oldValue!==null) {
            if (elem.value!==elem.oldValue) {
                sameValues=false;
                review+="'"+elem.value+"'<>'"+elem.oldValue+"'";
            } else review+="'"+elem.value+"'=='"+elem.oldValue+"'";
        } else review+="'"+elem.value+"'..'"+elem.oldValue+"'";
    });
    const interactiveList=["resultado","respaldarBtn","numReg","sumTot"]; // ,"printBtn"
    if (sameValues) {
        if (ebyid("resultado").textContent.trim().length>0) {
            //console.log("Same values with previous result, display result again. "+review);
            clrem(interactiveList,"hidden"); 
        } //else console.log("Same values but no result, so do nothing. "+review);
    } else {
        //console.log("different values, hide old results. "+review);
        cladd(interactiveList,"hidden");
    }
    if (tgt.value && !tgt.oldValue) tgt.oldValue=tgt.value;
}
function submitPrint(){
    const formObj={eName:"FORM",action:"templates/cajareportep.php",method:"POST",target:"_blank",className:"hidden",eChilds:[{eName:"INPUT",type:"hidden",name:"accion",value:"generaReporte"},{eName:"INPUT",type:"hidden",name:"folio",value:ebyid("bfolio").value},{eName:"INPUT",type:"hidden",name:"empresaId",value:ebyid("bempresa").value},{eName:"INPUT",type:"hidden",name:"tipofecha",value:ebyid("tipofecha").value},{eName:"INPUT",type:"hidden",name:"tipo",value:ebyid("tipo").value},{eName:"INPUT",type:"hidden",name:"status",value:ebyid("status").value},{eName:"INPUT",type:"hidden",name:"statusModifier",value:clhas(ebyid("status"),'inverse')?"1":""},{eName:"INPUT",type:"hidden",name:"beneficiario",value:ebyid("beneficiario").value},{eName:"INPUT",type:"hidden",name:"fechaIni",value:ebyid("fechaIni").value},{eName:"INPUT",type:"hidden",name:"fechaFin",value:ebyid("fechaFin").value}, {eName:"INPUT",type:"hidden",name:"modoImpresion",value:"1"}]};
    const formElem=ecrea(formObj);
    document.body.appendChild(formElem);
    formElem.submit();
    ekil(formElem);
}
function error(errmsg, parameters, evt) {
    if (parameters && parameters.target && parameters.target.disabled) setTimeout(function(tgt) {
        tgt.disabled=false;
    }, 1500, parameters.target);
    ekil(ebyid("waitingImage"));
    console.log("ERR: "+errmsg+", PARAMS:",parameters,", EVENT:",evt);
    showError(errmsg, false, "ERROR "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status);
}
var noLogo=["todas","apel","marlot","servicios"];
function doRepLogo(event) {
    const tgt=event.target;
    const alias=tgt.options[tgt.selectedIndex].text.toLowerCase();
    //console.log("INI function doRepLogo "+alias);
    const repLogoElem=ebyid("repLogo");
    if (noLogo.includes(alias)) {
        clrem(repLogoElem,"doprintBlock");
        repLogoElem.removeAttribute("style");
    } else {
        cladd(repLogoElem,"doprintBlock");
        repLogoElem.setAttribute("style","background-image:url(imagenes/logos/"+alias+".png) !important");
    }
}
function acceptRecord() {
    const regElem=ebyid("regId");
    console.log("INI function acceptRecord"+(regElem?" "+regElem.value:""));
    fixStatus(regElem.value,"aceptado");
}
function rejectRecord() {
    const regElem=ebyid("regId");
    console.log("INI function rejectRecord"+(regElem?" "+regElem.value:""));
    fixStatus(regElem.value,"rechazado");
}
function paidRecord() {
    const regElem=ebyid("regId");
    console.log("INI function paidRecord"+(regElem?" "+regElem.value:""));
    fixStatus(regElem.value,"pagado");
}
function fixStatus(regId,status) {
    postService("consultas/CajaChica.php", {accion:"ajustaStatus", regId:regId, status:status}, function(text, parameters, state, status){
        if(state<4&&status<=200) return;
        if(text.length==0) {
            console.log("STATE: ",state,"\nSTATUS: ",status,"\nPARAMETERS: ",parameters,"\nText: VACIO");
            return;
        }
        try {
            const jobj=JSON.parse(text);
            if (jobj.result==="error") {
                console.log(parameters,jobj);
                if (jobj.message) {
                }
            } else if (jobj.result==="refresh") {
                location.reload(true);
            } else if (jobj.result==="exito") {
                
            }
        } catch(ex) {
            console.log(parameters,"\n",text,"\n",ex);
        }
    },function(errmsg, parameters, evt){
        console.log(errmsg, parameters, evt);
    });
}
function excludeTrigger(event) {
    console.log("INI function excludeTrigger");
    const tgt=event.target;
    if (tgt.disabled) return;
    const excludeTargetId=tgt.tgtId;
    const singleIcon=tgt.singleIcon;
    const excludeIcon=tgt.excludeIcon;
    const targetElem=ebyid(excludeTargetId);
    if(clhas(targetElem,'inverse')) {
        console.log("Remove Inverse");
        clrem(targetElem,'inverse');
        tgt.setAttribute('src','imagenes/icons/'+singleIcon);
        tgt.setAttribute('title','Elegir status');
        tgt.value="";
    } else {
        console.log("Add Inverse");
        cladd(targetElem,'inverse');
        tgt.setAttribute('src','imagenes/icons/'+excludeIcon);
        tgt.setAttribute('title','Excluir status');
        tgt.value="NO";
    }
    doReset(event);
}
function showError(message, focusElement, title) {
    if (!title||(typeof title==="string" && title.length==0)) title="ERROR";
    if (typeof title==="string") title={eText:title};
    if (message instanceof Error) {
        console.log(message);
        message=getErrorDetail(message,false);
    }
    if (typeof message==="string") message={eName:"P", className:"cancelLabel boldValue bgred2", eText:message};
    console.log("showError: "+JSON.stringify(message));
    overlayMessage(message,title);
    if (focusElement) setTimeout(function(elem2Focus) {
        const closeBtn=ebyid("closeButton");
        if (closeBtn) {
            closeBtn.focus();
            console.log("FOCUS ON CLOSEBUTTON IN SHOWERROR TIMEOUT");
        } else console.log("NOT FOUND closeButton");
        if (elem2Focus && elem2Focus.focus) ebyid("overlay").callOnClose=function() {
            console.log("CALL ON CLOSE TO FOCUS ON ",elem2Focus);
            elem2Focus.focus();
            console.log("FOCUS ON "+elem2Focus.id+" IN SHOWERROR TIMEOUT");
        }
    }, 250, focusElement);
    return false;
}
console.log("SCRIPTS CAJA REPORTE");
<?php
clog1seq(-1);
clog2end("scripts.cajareporte");
