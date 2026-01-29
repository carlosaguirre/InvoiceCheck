<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.cajachica");
clog1seq(1);
$hayUsuario=hasUser();
$esAdmin=$hayUsuario&&validaPerfil("Administrador");
$esSistemas=$hayUsuario&&validaPerfil("Sistemas")||$esAdmin;
$puedeEditar=$hayUsuario&&validaPerfil("Caja Chica")||$esSistemas;
$puedeAutorizar=$hayUsuario&&validaPerfil("Autoriza Caja Chica")||$esSistemas;
$puedePagar = $hayUsuario&&validaPerfil("Paga Caja Chica")||$esSistemas;
if ($esSistemas) {
?>
function preRecalcAmount() {
    const sumTotal=ebyid("sumFilesTotal");
    if (sumTotal) {
        const suma=sumTotal.textContent;
        overlayConfirmation({eName:"P",eText:"Se actualizará el valor de MONTO con la suma total: "+suma},{eText:"Recalcula Monto"}, recalcAmount);
    }
}
function recalcAmount() {
    console.log("INI function recalcAmount");
    const regIdElem=ebyid("regId");
    const sumTotal=ebyid("sumFilesTotal");
    if (sumTotal) {
        const suma=sumTotal.textContent.replace("$","").trim();
        const parameters = { accion:"savePettyCashReq", regId:regIdElem.bdId, monto:suma };
        postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
    }
}
<?php
}
if($puedeEditar) {
?>
function preNewRecord() {
    const regIdElem=ebyid("regId");
    if (regIdElem.bdId) {
        overlayConfirmation({eName:"P",eText:"Se perderá la información no guardada del registro abierto."}, {eText:"NUEVO REGISTRO"}, newRecord );
    } else newRecord();
}
function newRecord() {
    if (verifyNewData()) {
        const parameters = { accion:"newPettyCashReq",empresaId:ebyid("nempresa").value,beneficiario:ebyid("nbeneficiario").value,concepto:ebyid("nconcepto").value,banco:ebyid("nbanco").value,cuentabancaria:ebyid("ncuentabanco").value,cuentaclabe:ebyid("ncuentaclabe").value,monto:ebyid("nmonto").value,observaciones:ebyid("nobservaciones").value };
        postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
    }
}
function verifyNewData() {
    if (!verifyEmptyField("nbeneficiario","Debe ingresar el nombre del Beneficiario")) return false;
    if (!verifyEmptyField("nempresa","Debe indicar una empresa")) return false;
    if (!verifyEmptyField("nconcepto","Debe ingresar un concepto")) return false;
    //if (!verifyEmptyField("nbanco","Debe ingresar un banco")) return false;
    //const cuenta1=ebyid("ncuentabanco");
    //const cuenta2=ebyid("ncuentaclabe");
    //if (cuenta1.value.length==0 && cuenta2.value.length==0)
    //    return showError("Debe ingresar al menos una cuenta de depósito",cuenta1);
    if (!verifyEmptyField("nmonto","Debe ingresar el monto total solicitado")) return false;
    return true;
}
<?php
} ?>
function preOpenRecord() {
    console.log("INI preOpenRecord");
    const regIdElem=ebyid("regId");
    if (regIdElem.bdId) {
        overlayConfirmation({eName:"P",eText:"Se perderá la información no guardada del registro abierto."}, {eText:"NUEVO REGISTRO"}, openRecord );
    } else openRecord();
}
var openXhr=false;
function openRecord(evt) {
    const tgt=evt?evt.target:false;
    console.log("INI openRecord "+evt.type,tgt);
    if (verifyOpenData(tgt)) {
        const parameters = { accion:"getPettyCashReq" };
        if (tgt.id==="bregId")
            parameters.regId=tgt.value;
        else
            parameters.beneficiario=tgt.value;
        if (openXhr) openXhr.abort();
        openXhr=postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
        openXhr.onabort=()=>{ console.log("ABORT!!"); }
    } else console.log("NO VALID OPEN DATA");
}
function verifyOpenData(tgt) {
    if (!tgt) return false;
    if (tgt.id!=="bregId" && tgt.id!=="bbeneficiario") return false;
    if (tgt.value.length<=0) return false;
    console.log("VALID DATA TO OPEN");
    return true;
}
<?php if ($esSistemas) { ?>
function restoreToPending(event) {
    const tgt=event.target?event.target:false;
    const regIdElem=ebyid("regId");
    if (!regIdElem.bdId||!regIdElem.bdId) return showError("Debe abrir un registro válido", regIdElem);
    if (regIdElem.bdId===regIdElem.value) {
        const parameters = { accion:"savePettyCashReq", regId:regIdElem.bdId, control:"pendiente" };
        postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
    } else console.log("NO COINCIDE ID BD='"+regIdElem.bdId+"' vs VAL='"+regIdElem.value+"'");
}
<?php } ?>
<?php if ($puedePagar) { ?>
function paidRecord() {
    const tgt=event.target?event.target:false;
    const regIdElem=ebyid("regId");
    if (!regIdElem||!regIdElem.bdId) return showError("Debe abrir un registro válido", regIdElem);
    if (regIdElem.bdId===regIdElem.value) {
        const parameters = { accion:"savePettyCashReq", regId:regIdElem.bdId, control:"pagado" };
        if (tgt && tgt.hasAttribute("auth")) {
            const authVal=tgt.getAttribute("auth");
            if (authVal==="0") parameters.control2="autorizar";
        }
        postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
    } else console.log("NO COINCIDE ID BD='"+regIdElem.bdId+"' vs VAL='"+regIdElem.value+"'");
}
<?php } ?>
function saveRecord() {
    console.log("INI function saveRecord");
    if (verifySaveData()) {
        const regIdElem=ebyid("regId");
        const solElem=ebyid("fechaSolicitud");
        const parameters = { accion:"savePettyCashReq", regId:regIdElem.bdId, fechasolicitud:solElem.value };
        const pagoElem=ebyid("fechaPago");
        const changedPago=(pagoElem && pagoElem.value!==pagoElem.bdval);
        if (changedPago) parameters.fechapago=pagoElem.value;
        const benElem=ebyid("beneficiario");
        const changedBenef=(benElem && benElem.value!==benElem.bdval);
        if (changedBenef) parameters.beneficiario=benElem.value;
        const empElem=ebyid("empresa");
        const changedEmpresa=(empElem && empElem.value!==empElem.bdval);
        if (changedEmpresa) parameters.empresaId=empElem.value;
        const conElem=ebyid("concepto");
        const changedConcepto=(conElem && conElem.value!==conElem.bdval);
        if (changedConcepto) parameters.concepto=conElem.value;
        const bcoElem=ebyid("banco");
        const changedBanco=(bcoElem && bcoElem.value!==bcoElem.bdval);
        if (changedBanco) parameters.banco=bcoElem.value;
        const ctaElem=ebyid("cuentabanco");
        const changedCuenta=(ctaElem && ctaElem.value!==ctaElem.bdval);
        if (changedCuenta) parameters.cuentabancaria=ctaElem.value;
        const cbeElem=ebyid("cuentaclabe");
        const changedClabe=(cbeElem && cbeElem.value!==cbeElem.bdval);
        if (changedClabe) parameters.cuentaclabe=cbeElem.value;
        const montoElem = ebyid("monto");
        const changedMonto=(montoElem && montoElem.value!==montoElem.bdval);
        if (changedMonto) parameters.monto=montoElem.value;
        const obsElem=ebyid("observaciones");
        const changedObs=(obsElem && obsElem.value!==obsElem.bdval);
        if (changedObs) parameters.observaciones=obsElem.value;
        const xmlIdElem = ebyid("xmlId");
        const changedXMLId=(xmlIdElem && xmlIdElem.value.length>0);
        if (changedXMLId) parameters.xmlId=xmlIdElem.value;
        const xmlPathElem = ebyid("xmlPath");
        const changedXMLPath=(xmlPathElem && xmlPathElem.value!==xmlPathElem.bdval);
        if (changedXMLPath) parameters.xmlPath=xmlPathElem.value;
        const pdfIdElem = ebyid("pdfId");
        const changedPDFId=(pdfIdElem && pdfIdElem.value.length>0);
        if (changedPDFId) parameters.pdfId=pdfIdElem.value;
        const pdfPathElem = ebyid("pdfPath");
        const changedPDFPath=(pdfPathElem && pdfPathElem.value!==pdfPathElem.bdval);
        if (changedPDFPath) parameters.pdfPath=pdfPathElem.value;
        const ctrlElem = ebyid("control");
        const changedCtrl=(ctrlElem && (ctrlElem.value==="autorizar"||ctrlElem.value==="rechazar"||ctrlElem.value==="pagado"));
        if (changedCtrl) {
            parameters.control=ctrlElem.value;
            if (ctrlElem.value==="pagado") parameters.control2="autorizar";
        }
<?php if ($puedePagar) { ?>
        const paidChk = ebyid("pagadoChk");
        if (paidChk && paidChk.checked) {
            changedCtrl=true;
            parameters.control="pagado";
            parameters.control2="autorizar";
        }
<?php } ?>
        if (changedPago || changedBenef || changedEmpresa || changedConcepto || changedBanco || changedCuenta || changedClabe || changedMonto || changedObs || changedXMLId || changedXMLPath || changedPDFId || changedPDFPath || changedCtrl) {
            console.log("SAVERECORD: GUARDANDO CAMBIOS");
            postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
        } else console.log("SAVERECORD: SIN CAMBIOS");
    }
    console.log("END function saveRecord");
}
function verifySaveData() {
    const regIdElem=ebyid("regId");
    if (!regIdElem||!regIdElem.bdId) return showError("Debe abrir un registro válido para poder modificarlo", regIdElem);
    if (!verifyEmptyField("fechaPago","Debe indicar la fecha de pago")) return false;
    if (!verifyEmptyField("beneficiario","Debe indicar el nombre del Beneficiario")) return false;
    if (!verifyEmptyField("empresa","Debe indicar una empresa")) return false;
    if (!verifyEmptyField("concepto","Debe indicar un concepto")) return false;
    //if (!verifyEmptyField("banco","Debe indicar un banco")) return false;
    //const cuenta1=ebyid("cuentabanco");
    //const cuenta2=ebyid("cuentaclabe");
    //if (cuenta1.value.length==0 && cuenta2.value.length==0)
    //    return showError("Debe indicar al menos una cuenta de depósito",cuenta1);
    if (!verifyEmptyField("monto","Debe indicar el monto total solicitado")) return false;
    return true;
}
function preDeleteRecord() {
    const regIdElem=ebyid("regId");
    if (regIdElem.bdId) {
        overlayConfirmation({eName:"P",eText:"Se borrará el registro abierto."}, {eText:"CONFIRMACION PARA ELIMINAR"}, deleteRecord );
    }
}
function deleteRecord() {
    console.log("INI function deleteRecord");
    if (verifyDeleteData()) {
        const parameters = { accion:"deletePettyCashReq", regId:ebyid("regId").bdId };
        postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
    } else console.log("Failed Verify Delete Data");
}
function verifyDeleteData() {
    const regIdElem=ebyid("regId");
    if (!regIdElem||!regIdElem.bdId) return showError("Debe abrir un registro válido para poder borrarlo", regIdElem);
    return true;
}
var columnViewKeys=["id","fechasolicitud","fechapago","beneficiario","concepto","monto","status"];
var columnViewName={id:"FOLIO",fechasolicitud:"SOLICITUD",fechapago:"PAGO",beneficiario:"BENEFICIARIO",concepto:"CONCEPTO",monto:"TOTAL",status:"STATUS"};
function successPettyCashReq(text,parameters,state,status) {
    //console.log("INI successPettyCashReq STATE: ",state, "\nSTATUS: ", status, "\nPARAMETERS: ", parameters);
    if(state<4&&status<=200) return;
    console.log("INI successPettyCashReq", parameters);
    if(text.length==0) {
        console.log("END successPettyCashReq ERROR: Texto Vacío");
        return;
    }
    try {
        const jobj=JSON.parse(text);
        //console.log("INI function successPettyCashReq Text: "+text);
        if (jobj.result==="error") {
            console.log("END successPettyCashReq: jobj result is error", jobj);
            if (jobj.focuselem) showError(jobj.message, focuselem);
            else showError(jobj.message);
        } else if (jobj.result==="refresh") {
            location.reload(true);
        } else if (jobj.result==="exito") {
            resetView();
            switch (parameters.accion) {
                case "newPettyCashReq":
                case "getPettyCashReq":
                case "savePettyCashReq":
                case "deletePettyCashFile":
                case "addFiles":
                    if (!jobj.datos||jobj.datos.length<=0) {
                        console.log("END successPettyCashReq: SIN DATOS");
                        resetEdit();
                        cladd(ebyid("registro_actual_btn","invisible"));
                        showBlock("buscar_registro","bregId");
                    } else if (jobj.datos.length>1) {
                        console.log("END successPettyCashReq: MULTIPLES RESULTADOS");
                        overlayMessage(
                            [
                                {   eName:"P",className:"marginH5",eText:"Se encontraron varios registros que coinciden. Seleccione el que requiera:"},
                                arrayToHTMLTableObject(
                                    jobj.datos,
                                    columnViewKeys,
                                    {   className:"noApply"},
                                    {   onappend:function(rowO,celO){
                                            if(rowO&&rowO.index&&celO.eText&&rowO.index==="H") {
                                                celO.eText=columnViewName[celO.eText];
                                                if(!celO.className) celO.className="centered";
                                            }
                                        }
                                    },
                                    {   onclick:function(event){
                                            const tgt=event.target?event.target:false;
                                            if (tgt && tgt.index && tgt.index.slice(0,1)==="B") {
                                                const parn=tgt.parentNode;
                                                const ptIdx=tgt.index.indexOf(".",2);
                                                const idx=tgt.index.slice(2,ptIdx);
                                                overlay();
                                                successPettyCashReq(JSON.stringify({result:"exito",message:"Seleccion de registro exitoso",datos:[jobj.datos[+idx]]}),parameters,state,status);
                                            }
                                        },
                                        onbuild:function(tgt) {
                                            if (tgt && tgt.index && tgt.index.slice(0,1)==="B") {
                                                tgt.className="pointer";
                                            }
                                        },
                                        ongetvalue:function(obj,key) {
                                            delete this.className;
                                            switch(key) {
                                                case "fechasolicitud":
                                                case "fechapago":
                                                    const cutText=obj[key].slice(0,10);
                                                    const dateObj=strptime("%Y-%m-%d",cutText);
                                                    const fixedVal=strftime(date_format,dateObj);
                                                    return fixedVal;
                                                case "monto":
                                                    this.className="pointer padr5 righted inputCurrency";
                                                    return parseFloat(obj[key]).toFixed(2);
                                                default: return obj[key];
                                            }
                                            return obj[key];
                                        },
                                        onmissedkey:function(obj,key) {
                                            //console.log("onmissedkey ( obj:",obj,", key:",key," )");
                                            delete this.className;
                                            if (key==="status") {
                                                if (obj.pagadoPor && obj.pagadoPor.length>0)
                                                    return "PAGADO";
                                                if (obj.autorizadoPor && obj.autorizadoPor.length>0)
                                                    return "AUTORIZO "+obj.autorizadoPor;
                                                if (obj.rechazadoPor && obj.rechazadoPor.length>0)
                                                    return "RECHAZO "+obj.rechazadoPor;
                                                return "PENDIENTE";
                                            }
                                            return false;
                                        }
                                    }
                                )
                            ],
                            {eText:"Registros Múltiples"}
                        );
                    } else {
                        console.log("Desplegando informacion");
                        showBlock("registro_actual","beneficiario");
                        const regIdElem=ebyid("regId");
                        const data=jobj.datos[0];
                        regIdElem.value=data.id;
                        regIdElem.bdId=regIdElem.value;
                        const fechaSolElem=ebyid("fechaSolicitud");
                        const cutFechaSol=data.fechasolicitud.slice(0,10);
                        const dateFechaSol=strptime("%Y-%m-%d",cutFechaSol);
                        const fixedFechaSol=strftime(date_format,dateFechaSol);
                        fechaSolElem.value=fixedFechaSol;
                        //console.log("Get Fecha Solicitud: '"+data.fechasolicitud+"' => '"+cutFechaSol+"' => '",dateFechaSol,"' => '"+fixedFechaSol+"'");
                        const fechaPagoElem=ebyid("fechaPago");
                        const cutFechaPago=data.fechapago.slice(0,10);
                        const dateFechaPago=strptime("%Y-%m-%d",cutFechaPago);
                        const fixedFechaPago=strftime(date_format,dateFechaPago);
                        fechaPagoElem.value=fixedFechaPago;
                        fechaPagoElem.bdval=fechaPagoElem.value;
                        //console.log("Get Fecha Pago: '"+data.fechapago+"' => '"+cutFechaPago+"' => '",dateFechaPago,"' => '"+fixedFechaPago+"'");
                        const benElem=ebyid("beneficiario");
                        benElem.value=data.beneficiario;
                        benElem.bdval=benElem.value;
                        const empElem=ebyid("empresa");
                        if (empElem.tagName==="SELECT")
                            empElem.value=data.empresaId;
                        else if (empElem.tagName==="INPUT") {
                            if (data.alias) empElem.value=data.alias;
                            else if (data.empresa) empElem.value=data.empresa;
                            else {
                                empElem.value=data.empresaId;
                                console.log("DATA sin alias ni empresa:\n",data);
                            }
                        }
                        empElem.bdval=empElem.value;
                        const conElem=ebyid("concepto");
                        conElem.value=data.concepto;
                        conElem.bdval=conElem.value;
                        const bcoElem=ebyid("banco");
                        bcoElem.value=data.banco;
                        bcoElem.bdval=bcoElem.value;
                        const ctaElem=ebyid("cuentabanco");
                        ctaElem.value=data.cuentabancaria;
                        ctaElem.bdval=ctaElem.value;
                        const cbeElem=ebyid("cuentaclabe");
                        cbeElem.value=data.cuentaclabe;
                        cbeElem.bdval=cbeElem.value;
                        const mtoElem=ebyid("monto");
                        mtoElem.value=parseFloat(data.monto).toFixed(2);
                        mtoElem.bdval=mtoElem.value;
                        if (!data.observaciones)
                            data.observaciones="";
                        const obsElem=ebyid("observaciones");
                        obsElem.value=data.observaciones;
                        obsElem.bdval=obsElem.value;
                        const solElem=ebyid("solicitante");
                        solElem.value=data.solicitante;
                        solElem.bdval=solElem.value;
                        //if (data.archivos) {
                            refreshFileList(data.archivos);
                        //}
                        const ctrlElem = ebyid("control");
                        while (ctrlElem.nextSibling) ctrlElem.parentNode.removeChild(ctrlElem.nextSibling);
                        if (data.pagadoPor && data.pagadoPor.length>0) {
                            ebyid("controlCap").textContent="SITUACION:";
                            cladd(ctrlElem,"hidden");
                            ctrlElem.parentNode.appendChild(ecrea({eName:"B",eText:"PAGADO"}));
                            setReadOnlyMode(true,true);
                        } else if (data.autorizadoPor && data.autorizadoPor.length>0) {
                            ebyid("controlCap").textContent="AUTORIZADO POR:";
                            cladd(ctrlElem,"hidden");
                            ctrlElem.parentNode.appendChild(ecrea({eName:"B",eText:data.autorizadoPor}));
                            <?php if ($puedePagar) { ?>
                            ctrlElem.parentNode.append(ecrea({eText:". "}), ecrea({eName:"INPUT", type:"button", id:"paidButton", value:"CAMBIAR A PAGADO", onclick:paidRecord, auth:"1"}));
                            <?php } ?>
                            if (data.ultimoRespaldo && data.ultimoRespaldo.length>0) ctrlElem.parentNode.append(ecrea({eText:" RESPALDADO"}));
                            <?php if ($esSistemas) { ?>
                            else ctrlElem.parentNode.append(ecrea({eText:" "}), ecrea({eName:"INPUT", type:"button", id:"resetStatusButton", value:"CAMBIAR A PENDIENTE", onclick:restoreToPending}));
                            <?php } ?>
                            setReadOnlyMode(true,true);
                        } else if (data.rechazadoPor && data.rechazadoPor.length>0) {
                            ebyid("controlCap").textContent="RECHAZADO POR:";
                            cladd(ctrlElem,"hidden");
                            ctrlElem.parentNode.appendChild(ecrea({eName:"B",eText:data.rechazadoPor}));
                            <?php if ($puedePagar) { ?>
                            ctrlElem.parentNode.append(ecrea({eText:". "}), ecrea({eName:"INPUT", type:"button", id:"paidButton", value:"CAMBIAR A PAGADO", onclick:paidRecord, auth:"0"}));
                            <?php }
                                if ($esSistemas) { ?>
                            ctrlElem.parentNode.append(ecrea({eText:" "}), ecrea({eName:"INPUT", type:"button", id:"resetStatusButton", value:"CAMBIAR A PENDIENTE", onclick:restoreToPending}));
                            <?php } ?>
                            setReadOnlyMode(true,true);
                        } else {
                            ebyid("controlCap").textContent="SITUACION:";
                            clrem(ctrlElem,"hidden");
                            if (ctrlElem.tagName==="INPUT") {
                                ctrlElem.value="PENDIENTE";
                            } else if (ctrlElem.tagName==="SELECT") {
                                ctrlElem.selectedIndex=0;
                            }
                            <?php if ($puedePagar) { ?>
                            if (clhas(ctrlElem,"hidden")) {
                                ctrlElem.parentNode.append(ecrea({eText:". "}), ecrea({eName:"LABEL", eChilds:[{eName:"INPUT", type:"checkbox", id:"pagadoChk", value:"1"}, {eText:" Marcar Pagado."}]}));
                            }
                            <?php } ?>
                            setReadOnlyMode(<?= ($puedeEditar?"false":"true").",".($puedeAutorizar?"false":"true") ?>);
                        }
                        if (jobj.errormessages) console.log("END successPettyCashReq: View errormessages ("+jobj.errormessages.length+")");
                        else console.log("END successPettyCashReq: NO errormessages for view");
                        // ToDo: muestra mensaje de error si encuentra jobj.cfdiErrors, pero igual muestra toda la informacion encontrada (asignar contenido de cfdiStack como en viajero.php)
                        // ToDo: para cualquier json.result si encuentra jobj.cfdiLog lo muestra en
                        // ToDo: Limpia y regenera contenido en DIV currentFiles. borra contenido en newFiles
                        if (jobj.errormessages && jobj.errormessages.length>0) showError(jobj.errormessages, false, "AVISOS");
                        else if (parameters.accion==="savePettyCashReq") overlayMessage({eName:"P", className:"boldValue", eText:"El registro fue guardado exitosamente"},"RESULTADO");
                    }
                    break;
                case "deletePettyCashReq":
                    resetEdit();
                    console.log("END successPettyCashReq: deletePettyCashReq");
                    cladd(ebyid("registro_actual_btn","invisible"));
                    showBlock("buscar_registro","bregId");
                    overlayMessage({eName:"P", className:"boldValue", eText:jobj.message},{eText:"EXITO"});
                    break;
            }
        } else {
            console.log("END successPettyCashReq OTHER: ", jobj);
            showError(jobj.message, false, jobj.result);
        }
    } catch(ex) {
        showError(ex.message);
        console.log("END successPettyCashReq Exception caught: ", ex, "\nText: ", text);
    }
}
function failurePettyCashReq(errmsg, parameters, evt) {
    console.log("INI failurePettyCashReq: ERRMSG="+errmsg+", PARAMS=",parameters,", EVENT=",evt);
    showError(errmsg, false, "ERROR "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status);
}
function resetElem(id,defaultValue) {
    if (Array.isArray(id)) {
        id.forEach(function(eId) { resetElem(eId,defaultValue); });
        return;
    }
    const elem=ebyid(id);
    if (elem) {
       //console.log("INI resetElem id='"+id+"'"+(defaultValue?", defaultValue='"+defaultValue+"'":""));
        if (elem.bdId) delete elem.bdId; // elem.bdId=false;
        if (elem.tagName==="INPUT") {
            if (elem.classList.contains("calendarV")&&elem.today) elem.value=elem.today;
            else elem.value=(defaultValue?defaultValue:"");
        } else if (elem.tagName==="SELECT") {
            elem.selectedIndex=(defaultValue?defaultValue:0);
        } else elem.textContent=(defaultValue?defaultValue:"");
    } else console.log("NOT FOUND ELEM WITH ID "+id);
}
function resetView() {
    console.log("INI function resetView");
    resetElem(["bregId","bbeneficiario"]);
    resetElem(["nbeneficiario","nempresa","nbanco","ncuentabanco","ncuentaclabe","nconcepto","nobservaciones","nmonto"]);
    const ncnc=ebyid("nconcepto");
    if (ncnc) ncnc.value="CAJA CHICA CON FACTURA";
}
function resetEdit() {
    console.log("INI function resetEdit");
    setReadOnlyMode(<?= ($puedeEditar?"false":"true").",".($puedeAutorizar?"false":"true") ?>);
    resetElem(["regId","fechaSolicitud","fechaPago","beneficiario","empresa","concepto","banco","cuentabanco","cuentaclabe","monto","observaciones","solicitante","control"]);
    ebyid("concepto").value="CAJA CHICA CON FACTURA";
    resetElem("controlCap","SITUACION:");
    //resetFile("xml");
    //resetFile("pdf");
}
function setReadOnlyMode(isROEdit,isROAuth) {
    isROEdit=!!isROEdit;
    isROAuth=!!isROAuth;
    console.log("INI function setReadOnlyMode toEdit:"+(isROEdit?"true":"false")+", toAuth:"+(isROAuth?"true":"false"));
    const bnf=ebyid("beneficiario");
    if (bnf) bnf.readOnly=isROEdit; // input text
    else console.log("SIN beneficiario");
    const cnc=ebyid("concepto");
    if (cnc) cnc.readOnly=true; //isROEdit; // input text
    else console.log("SIN concepto");
    const bnc=ebyid("banco");
    if (bnc) bnc.readOnly=isROEdit; // input text
    else console.log("SIN banco");
    const empFld=ebyid("empresa"); // select | input text
    if (empFld) {
        empFld.readOnly=isROEdit;
        if (empFld.selectedIndex<0) empFld.selectedIndex=0;
        if (isROEdit) {
            console.log("empresa edit");
            cladd(empFld,"no_selection");
            if (empFld) empFld.onkeydown=ignoreAlpha;
            if (empFld.options) fee(empFld.options,function(opt,idx) { if(empFld.selectedIndex!==idx)cladd(opt,"hidden");}); // option
        } else {
            console.log("empresa noedit");
            clrem(empFld,"no_selection");
            delete empFld.onkeydown;
            if (empFld.options) fee(empFld.options,function(opt) { clrem( opt,"hidden"); }); // option
            else empFld.readOnly=true;
        }
    } else console.log("SIN empresa");
    const ctb=ebyid("cuentabanco");
    if (ctb) ctb.readOnly=isROEdit; // input text
    else console.log("SIN cuentabanco");
    const ccb=ebyid("cuentaclabe");
    if (ccb) ccb.readOnly=isROEdit; // input text
    else console.log("SIN cuentaclabe");
    const mnt=ebyid("monto");
    if (mnt) mnt.readOnly=isROEdit; // input number
    else console.log("SIN monto");
    const obs=ebyid("observaciones");
    if (obs) obs.readOnly=isROEdit; // input text
    else console.log("SIN observaciones");
    const fpg=ebyid("fechaPago");
    if (fpg) fpg.disabled=isROEdit; // input text
    else console.log("SIN fechaPago");
    const ivf=ebyid("invfiles");
    if (ivf) ivf.disabled=isROEdit; // input file
    else console.log("SIN invfiles");
    const srb=ebyid("saveRecordBtn");
    if (srb) srb.disabled=(isROEdit && isROAuth); // button
    else console.log("SIN saveRecordBtn");
    const drb=ebyid("deleteRecordBtn");
    if (drb) drb.disabled=isROEdit; // button
    else console.log("SIN deleteRecordBtn");
    const afb=ebyid("addFilesBtn");
    if (afb) afb.disabled=isROEdit; // button
    else console.log("SIN addFilesBtn");
    if (isROEdit) fee(lbycn("removeIcon"),ekil);
    else console.log("SIN removeIcon's");
}
// // // // // ANEXAR XML Y PDF // // // // //
function addFiles(evt) {
    if (!evt) return;
    const tgt=evt.target?evt.target:false;
    if (!tgt) return;
    console.log("INI function addFiles "+tgt.id);
    const res=verifyFiles(tgt.files);
    if (res.files.length>0) {
        console.log("FILES: ",res.files);
        const parameters = { accion:"addFiles", regId:evl(ebyid("regId")), files:res.files, mdates:res.mdates, message:res.error };
        postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
        ebyid('invfiles').value="";
    } else {
        overlayMessage(res.error,{eText:"ERROR"});
    }
}
function verifyFiles(files) {
    const res={error:[],files:[],mdates:[]};
    let count=0;
    for(let i=0; i<files.length; i++) { // >
        if (!isValidSize(files[i])) {
            if(files[i].size==0) {
                res.error.push({eName:"P",className:"cancelLabel boldValue bgred",eText:"El archivo "+files[i].name+" est\u00E1 vac\u00EDo"});
            } else {
                res.error.push({eName:"P",className:"cancelLabel boldValue bgred",eText:"El archivo "+files[i].name+" excede el tama\u00F1o m\u00E1ximo permitido de 2MB"});
            }
            continue;
        }
        if(isXML(files[i])||isPDF(files[i])) {
            res.files.push(files[i]);
            if (files[i].lastModified) res.mdates.push(files[i].lastModified);
            else res.mdates.push(false);
            count++;
        } else {
            res.error.push({eName:"P",className:"cancelLabel boldValue bgred",eText:"El formato del archivo "+files[i].name+" no es reconocido como XML o PDF ("+files[i].type+")"});
        }
        //res.error.push({eName:"P",eText:"Se ingresaron "+count+" archivo"+(count==1?"":"s")});
    }
    return res;
}
function responseAddFiles(parameters,text,state,status) {
    if(state<4&&status<=200) return;
    if(text.length>0) try {
        const jobj=JSON.parse(text);
        if (jobj.result==="error") {
            ebyid("invfiles").value="";
            overlayMessage({eName:"P",eText:jobj.message});
        } else if (jobj.result==="exito") {
            //if (jobj.files)
                refreshFileList(jobj.files);
            if (jobj.message)
                overlayMessage({eName:"P",eText:jobj.message});
        }
    } catch(ex) {
        console.log(ex,"\n",text);
    }
    ebyid("invfiles").value="";
}
function failedResponseAddFiles(errmsg,state,status) {
    console.log("ERROR "+state+"/"+status+"AL AGREGAR ARCHIVOS: "+errmsg);
    overlayMessage({eName:"P",eText:"Ocurrió un error inesperado al agregar los archivos, revise su conexión y reintente, de continuar el error consulte a su Administrador"},{eText:"ERROR"});
    ebyid("invfiles").value="";
}
function validCountFiles() {
    const numFilesElem=ebyid("numFiles");
    const maxValidFiles=20;
    if (numFilesElem.textContent.length>0) {
        const numFiles=+(numFilesElem.textContent);
        if (numFiles>=maxValidFiles) {
            overlayMessage({eName:"P",eText:"El máximo número de archivos por registro es "+maxValidFiles+"."},{eText:"ERROR"});
            return false;
        }
    }
    return true;
}
function refreshFileList(archivos) {
    console.log("INI refreshFileList: "+(archivos?(archivos.length?archivos.length:"unk|0"):"undef"));
    if (!archivos) archivos=[];
    const filesElem=ebyid("files");
    if (archivos.length>=5) {
        console.log("DESHABILITANDO ADD FILES BTN");
        const afb=ebyid("addFilesBtn");
        if (afb) afb.disabled=true;
        const iff=ebyid("invfiles");
        if (iff) iff.disabled=true;
    } else console.log("5 es mayor que "+archivos.length);
    let filesBody=filesElem?filesElem.firstChild:false;
    while(filesBody && filesBody.tagName!=="TBODY") {
        filesBody=filesBody.nextElementSibling;
    }
    if (filesBody) {
        ekfil(filesBody);
        let suma=0;
        for(let i=0;i<archivos.length;i++) { //>
            const rowObj=makeFileRow(archivos[i]);
            if (rowObj.total) suma+=rowObj.total;
            rowObj.eChilds[0].eText=(i+1);
            filesBody.appendChild(ecrea(rowObj));
        }
        const numF=ebyid("numFiles");
        if (numF) numF.textContent=""+(archivos.length);
        const dscF=ebyid("descFiles");
        if (dscF) dscF.textContent="comprobante"+(archivos.length!=1?"s":"");
        const sft=ebyid("sumFilesTotal");
        if (sft) sft.textContent="$ "+parseFloat(suma).toFixed(2);
        const regactual=ebyid("registro_actual");
        if (regactual&&filesElem) filesElem.style.width=(regactual.offsetWidth)+"px";
        clrem(filesElem,"hidden");
        if (regactual) {
            const widpx=regactual.offsetWidth+"px";
            if (filesElem) filesElem.style.width=widpx;
            setTimeout(function(){
                const widpx2=regactual.offsetWidth+"px";
                if (widpx!==widpx2)
                    if (filesElem) filesElem.style.width=widpx2;
            },100);
        }
    }
}
// // // // // UTILERIAS // // // // //
function getFileFormArray(type,id,path) {
    const form={eName:"FORM", id:type+id+"Form", method:"POST", action:"consultas/docs.php", className:"inlineblock pad2 top relative", target:"doc", onsubmit:function(event){window.open("","doc");return true;}, onmouseenter:function(event){doWipe(type+id,true);}, onmouseleave:function(event){doWipe(type+id,false);},eChilds:[] };
    const rawtype=(type.length>3)?type.slice(-3):type;
    if (rawtype==="xml") form.eChilds.push({eName:"INPUT",type:"hidden",name:"type",value:"text/xml"});
    else if (rawtype==="pdf") form.eChilds.push({eName:"INPUT",type:"hidden",name:"type",value:"application/pdf"});
    else return [];
    const travelPath=(path&&path.length>0&&path!=="temporal")?"viajes/"+path:"";
    form.eChilds.push({eName:"INPUT",type:"hidden",name:"path",id:type+id+"Path",value:travelPath});
    const imgpart=(path&&path.length>0)?"200":"512Missing";
    const imgIcon={eName:"IMG",id:type+id+"Icon",height:"24"};
    if (path&&path.length>0) {
        imgIcon.src="imagenes/icons/"+rawtype+"200.png";
        imgIcon.className="pointer";
    } else {
        imgIcon.src="imagenes/icons/"+rawtype+"512Missing.png";
    }
    imgIcon.onclick=function(event){
        if(evl(ebyid(type+id+"Path")).length>0)
            ebyid(type+id+"Form").submit();
    };
    form.eChilds.push(imgIcon);
    return [form];
}
function preRemoveFileRecord(event) {
    const img=event.target;
    const cell=img.parentNode;
    const row=cell.parentNode;
    const idxCell=row.firstElementChild;
    const xmlCell=idxCell.nextElementSibling;
    const pdfCell=xmlCell.nextElementSibling;
    const nameCell=pdfCell.nextElementSibling;
    const filename=nameCell.textContent;
    overlayConfirmation({eName:"P",eText:"Se eliminará el comprobante "+filename}, {eText:"CONFIRMACION PARA ELIMINAR"}, function() { removeFileRecord(row); } );
}
function removeFileRecord(row) {
    console.log("INI function removeFileRecord ",row);
    const folioElem=ebyid("regId");
    if (!folioElem.value || folioElem.value.length==0) {
        const ovy=ebyid("overlay");
        if (ovy.style.visibility!=="hidden") overlay();
        overlayMessage({eName:"P",eText:"No se encontró registro actual. Verifique que el campo Folio no se encuentre vacío."});
        return;
    }
    if (!row || !row.fileId) {
        const ovy=ebyid("overlay");
        if (ovy.style.visibility!=="hidden") overlay();
        overlayMessage({eName:"P",eText:"Registro de archivo no encontrado. Cargue nuevamente los datos del registro."});
        return;
    }
    const fileId=row.fileId;
    const parameters = { accion:"deletePettyCashFile", regId:folioElem.bdId, fileId:fileId };
    postService("consultas/CajaChica.php", parameters, successPettyCashReq, failurePettyCashReq);
}
function makeFileRow(data) {
    const rowObj={eName:"TR",fileId:data.id,eChilds:[]};
    rowObj.eChilds.push({eName:"TD",className:"shrinkCol"}); // #
    rowObj.eChilds.push({eName:"TD",className:"shrinkCol",eChilds:getFileFormArray("xml",data.id,data.archivoxml)});// XML
    rowObj.eChilds.push({eName:"TD",className:"shrinkCol",eChilds:getFileFormArray("pdf",data.id,data.archivopdf)});// PDF
    let nombre=data.archivoxml;
    if (!nombre) nombre=data.archivopdf;
    if (nombre) {
        let idx1=nombre.lastIndexOf("/");
        let idx2=nombre.lastIndexOf(".");
        if (idx1<0) idx1=0;
        else idx1++;
        if (idx2<0) idx2=nombre.lenth;
        nombre={eName:"TD",eText:nombre.slice(idx1,idx2)};
    } else nombre={eName:"TD"};
    rowObj.eChilds.push(nombre);// Nombre
    let folio=data.foliofactura;
    if (folio) folio={eName:"TD",eText:folio};
    else folio={eName:"TD"};
    rowObj.eChilds.push(folio);// Folio
    let fecha=data.fechafactura;
    if (fecha) {
        const yy=fecha.slice(0,4);
        const mm=fecha.slice(5,7);
        const dd=fecha.slice(8,10);
        const tt=fecha.slice(10);
        fecha={eName:"TD",eText:dd+"/"+mm+"/"+yy+tt};
    } else fecha={eName:"TD"};
    rowObj.eChilds.push(fecha);// Fecha
    let total=data.totalfactura;
    let tc=data.tipocomprobante;
    if (total) {
        rowObj.total=+total;
        let totText="";
        if (tc==="e") {
            totText+="- ";
            rowObj.total*=-1;
        }
        totText+="$ "+(parseFloat(total).toFixed(2));
        total={eName:"TD",eText:totText};
    } else total={eName:"TD"};
    rowObj.eChilds.push(total);// Total
    let delBtn={eName:"TD",className:"shrinkCol",eChilds:[{eName:"IMG",src:"imagenes/icons/deleteIcon12.png",height:"20",className:"btnOp erasePointer removeIcon noprint",onclick:preRemoveFileRecord}]};
    rowObj.eChilds.push(delBtn);
    return rowObj;
}
function showBlock(id,focuselem) {
    clrem(ebyid(id+"_btn"),"invisible");
    fee(lbycn("tabla_caja"), function(elem) { cladd(elem, "hidden"); });
    fee(lbycn("btnTab"), function(elem) { clrem(elem, "selected"); });
    clrem(ebyid(id),"hidden");
    cladd(ebyid(id+"_btn"),"selected");
    if (id==="registro_actual") clrem(ebyid("files"),"hidden");
    if(focuselem) {
        ebyid(focuselem).focus();
        console.log("FOCUS ON ",focuselem," IN SHOWBLOCK");
    }
}
function verifyEmptyField(fieldId,message) {
    const element=ebyid(fieldId);
    let emptyCheck=(element.value.length==0);
    if(!emptyCheck && element.type==="number") emptyCheck=((+element.value)<=0);
    if (emptyCheck) return showError(message, element);
    return true;
}
function showError(message, focusElement, title) {
    if (!title||(typeof title==="string" && title.length==0)) title="ERROR";
    if (typeof title==="string") title={eText:title};
    if (typeof message==="string") message={eName:"P", className:"cancelLabel boldValue bgred2", eText:message};
    overlayMessage(message,title);
    setTimeout(function(elem2Focus) {
        ebyid("closeButton").focus();
        console.log("FOCUS ON CLOSEBUTTON IN SHOWERROR TIMEOUT");
        if (elem2Focus && elem2Focus.focus) ebyid("overlay").callOnClose=function() {
            console.log("CALL ON CLOSE TO FOCUS ON ",elem2Focus);
            elem2Focus.focus();
            console.log("FOCUS ON "+elem2Focus.id+" IN SHOWERROR TIMEOUT");
        }
    }, 250, focusElement); 
    return false;
}
function resetOverlay(message,title) {
    const dra=ebyid("dialog_resultarea");
    const dtt=ebyid("dialog_title");
    if (dra) {
        ekfil(dra);
        appendMessageElement(dra, message);
    }
    if (dtt) {
        ekfil(dtt);
        appendMessageElement(dtt, title);
    }
}
function doWipe(type,way) {
    const path=ebyid(type+"Path");
    const id=ebyid(type+"Id");
    if (path && path.value.length>0 && (path.value!=="temporal" || (id && id.value.length>0)))
    if ((id && id.value.length>0)||(path && path.value.length>0)) {
        if (way) clrem(ebyid(type+"Wipe"),"hidden");
        else cladd(ebyid(type+"Wipe"),"hidden");
    }
}
function isPDF(file) {
    return file.type==="application/pdf";
}
function isXML(file) {
    return file.type==="text/xml";
}
function isValidSize(file) {
    const size=+file.size;
    return size>0 && size<2097000;
}
function chkPaste(evt) {
    const key = evt.key;
    const tgt = evt.target;
    let val = tgt.value;
    console.log("INI chkPaste type='"+evt.type+"' | id='"+tgt.id+"' | key='"+key+"' | ctrl="+(evt.ctrlKey?"true":"false")+" | val='"+val+"'",evt);
    if (evt.type==="paste" || evt.type==="input" || (evt.type==="keyup" && evt.ctrlKey && (key==="v" || key==="V"))) {
        if (evt.type==="paste") {
            evt.preventDefault();
            val = evt.clipboardData.getData('text');
            console.log("CLIPBOARD VAL='"+val+"'");
        }
        val = val.toUpperCase();
        val = val.replace(/[ÁÄÂÀ]/g, "A");
        val = val.replace(/[ÉËÊÈ]/g, "E");
        val = val.replace(/[ÍÏÎÌ]/g, "I");
        val = val.replace(/[ÓÖÔÒ]/g, "O");
        val = val.replace(/[ÚÜÛÙ]/g, "U");
        val = val.replace(/[Ý]/g, "Y");
        val = val.replace(/[Ç]/g, "C");
        tgt.value = val.replace(/[^-A-ZÑ0-9 \.,\(\)&\/]/g, "");
        console.log("NEW VAL='"+tgt.value+"'");
    }
}
function chkDead(evt) {
    if (evt.key==="Dead") evt.target.addEventListener("keyup", chkDead, {once: true});
}
function validaBeneficiario(evt) {
    const tgt=evt.target;
    const key = evt.key;
    if (key.length>1||evt.ctrlKey) {
        if (key==="Dead") {
            console.log("DEAD: '"+tgt.value+"' ("+key+")");
            tgt.si=tgt.selectionStart;
            tgt.sf=tgt.selectionEnd;

            tgt.addEventListener("keyup", chkDead, {once: true});

            tgt.blur();
            setTimeout((t)=>{
                console.log("BACK: '"+t.value+"' OK!");
                t.focus();
                t.setSelectionRange(t.si,t.sf);
            },1,tgt);
            eventCancel(evt);
            return false;
        }
        //if (evt.ctrlKey && (key==="v" || key==="V")) tgt.addEventListener("keyup", chkPaste, {once: true});
        console.log("BIG: "+key);
        return true;
    }
    let regex = new RegExp("[-A-ZÑ0-9 \.,\(\)&\/]");
    if (regex.test(key)) {
        console.log("OK="+key);
        return true;
    }

    let upKey=key.toUpperCase();
    if (["Á","Ä","Â","À"].includes(upKey)) upKey="A";
    else if (["É","Ë","Ê","È"].includes(upKey)) upKey="E";
    else if (["Í","Ï","Î","Ì"].includes(upKey)) upKey="I";
    else if (["Ó","Ö","Ô","Ò"].includes(upKey)) upKey="O";
    else if (["Ú","Ü","Û","Ù"].includes(upKey)) upKey="U";
    else if (["Ý"].includes(upKey)) upKey="Y";
    else if (["Ç"].includes(upKey)) upKey="C";
    if (key!=upKey) {
        const si=tgt.selectionStart;
        const sf=tgt.selectionEnd;
        const val=tgt.value;
        const newVal=val.substring(0,si)+upKey+val.substring(sf)
        tgt.value=newVal;
        if (tgt.value!==newVal) tgt.value=newVal;
        tgt.setSelectionRange(si+upKey.length, si+upKey.length);
        console.log("FIX: '"+tgt.value+"' ("+upKey+")");
        tgt.addEventListener("keyup", chkDead, {once: true});
        // tgt.addEventListener("keyup", chkPaste, {once: true});
    } else console.log("DEL="+key);
    eventCancel(evt);  
    return false;
}
<?php
clog1seq(-1);
clog2end("scripts.cajachica");
