<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.viajero");
clog1seq(1);
if (!hasUser()) die("empty file");
$esSolicitante=(getUser()->nombre==="viajero");
$esAdmin=validaPerfil("Administrador");
$esSistemas=validaPerfil("Sistemas")||$esAdmin;
$esCompras=validaPerfil("Compras");
$esViaticos=validaPerfil("Viaticos");
$puedeEditar=$esSistemas||$esViaticos||$esSolicitante;
$puedeAutorizar=validaPerfil("Autoriza Viaticos")||$esSistemas;
$puedePagar = validaPerfil("Paga Viaticos")||$esSistemas;
if(!$puedeEditar && !$puedeAutorizar) {
    die("empty file");
}
?>
var formData={};
var tableData={};
var addedFiles=[];

// // // // // ANEXAR XML Y PDF // // // // //
var addFileOpt={};
function addFiles(evt) {
    if (!evt) return;
    const tgt=evt.target?evt.target:false;
    if (!tgt) return;
    console.log("INI function addFiles "+tgt.id);
    if (tgt.id!=="ninvfiles" && tgt.id!=="invfiles") return;
    const prefix=(tgt.id==="ninvfiles")?"n":"";
    addFileOpt={prefix:prefix, target:tgt};
    const vr=verifyFiles(tgt.files);
    vr.title="CONFIRMAR ENVIO DE DOCUMENTOS";
    if (!vr.xml && !vr.pdf) {
        if (addFileOpt.errxml) failedFile(prefix+"xml","01: "+addFileOpt.errxml);
        if (addFileOpt.errpdf) failedFile(prefix+"pdf","01: "+addFileOpt.errpdf);
        addFileOpt={};
    } else {
        const parameters = { accion:"temporales", xml:vr.xml, pdf:vr.pdf, concepto:ebyid("nconcepto").value };
        if (vr.xml) {
            parameters.xml=vr.xml;
            addFileOpt.xml=vr.xml;
            if (prefix.length==0) {
                const folioElem=ebyid("folio");
                if (folioElem && folioElem.bdval)
                    delete folioElem.bdval;
            }
        }
        if (vr.pdf) { parameters.pdf=vr.pdf; addFileOpt.pdf=vr.pdf; }
        /* const fechac=ebyid(prefix+"fechaConcepto");
        parameters.fecha=fechac.value;
        if (fechac.date) {
            parameters.fechafactura=fechac.date;
            if (fechac.time) parameters.fechafactura+=" "+fechac.time;
        } */
        console.log("Parameters:",parameters);
        postService("consultas/CajaChica.php", parameters, responseFiles, failedResponseFiles);
    }
}
function verifyFiles(files) {
    const res={msg:[],xml:false,pdf:false};
    let hasXMLMsg=false;
    let hasPDFMsg=false;
    let pdfIdx=0;
    for(let i=0; i<files.length; i++) { // >
        if (!isValidSize(files[i])) {
            if(files[i].size==0) {
                res.msg.push({eName:"P",className:"cancelLabel boldValue bgred",eText:"El archivo "+files[i].name+" est\u00E1 vac\u00EDo"});
                if(isXML(files[i])) addFileOpt.errxml="Archivo vac\u00EDo";
                else if(isPDF(files[i])) addFileOpt.errpdf="Archivo vac\u00EDo";
            } else {
                res.msg.push({eName:"P",className:"cancelLabel boldValue bgred",eText:"El archivo "+files[i].name+" excede el tama\u00F1o m\u00E1ximo permitido de 2MB"});
                if(isXML(files[i])) addFileOpt.errxml="Archivo demasiado grande";
                else if(isPDF(files[i])) addFileOpt.errpdf="Archivo demasiado grande";
            }
            continue;
        }
        if(isXML(files[i])) {
            if (res.xml) {
                if (!hasXMLMsg) {
                    res.msg.splice(1,0,{eName:"P",className:"cancelLabel boldValue bgred",eText:"S\u00F3lo se registrar\u00E1 el primer archivo XML de su selecci\u00F3n"});
                    pdfIdx++;
                    hasXMLMsg=true;
                }
                continue;
            }
            res.msg.splice(0,0,{eName:"P",eText:"Archivo XML ingresado: "+files[i].name});
            pdfIdx++;
            res.xml=files[i];
        } else if(isPDF(files[i])) {
            if (res.pdf) {
                if (!hasPDFMsg) {
                    res.msg.splice(pdfIdx+1,0,{eName:"P",className:"cancelLabel boldValue bgred",eText:"S\u00F3lo se registrar\u00E1 el primer archivo PDF de su selecci\u00F3n"});
                    hasPDFMsg=true;
                }
                continue;
            }
            res.msg.splice(pdfIdx,0,{eName:"P",eText:"Archivo PDF ingresado: "+files[i].name});
            res.pdf=files[i];
        }
    }
    return res;
}
function responseFiles(text,parameters,state,status) {
    if(state<4&&status<=200) return;
    const isTmp=(parameters.accion==="temporales");
    const prefix=isTmp?addFileOpt.prefix:"";
    // ToDo: Incluir isTmp donde se consulte addFileOpt
    if(text.length==0) {
        if (isTmp) {
            if (addFileOpt.xml) failedFile(prefix+"xml","02: Sin respuesta");
            if (addFileOpt.pdf) failedFile(prefix+"pdf","02: Sin respuesta");
            addFileOpt={};
        }
        return;
    }
    try {
        const jobj=JSON.parse(text);
        if (jobj.result && jobj.result==="refresh") {
            location.reload(true);
        } else if (jobj.result==="error") {
            if (isTmp) {
                if (addFileOpt.xml) failedFile(prefix+"xml","03: Error en datos");
                if (addFileOpt.pdf) failedFile(prefix+"pdf","03: Error en datos");
                addFileOpt={};
            }
            if (jobj.cfdiError) {
                showError("", null, "ERROR");
                if (jobj.cfdiStack) {
                    const dra=ebyid("dialog_resultarea");
                    if (dra) dra.innerHTML=jobj.cfdiStack;
                }
                if (jobj.cfdiLog) console.log("CFDILOG:\n"+jobj.cfdiLog);
            }
        } else if (jobj.result==="exito") {
            //console.log(text);
            if (jobj.cfdiError) {
                showError("", null, "ERROR");
                if (jobj.cfdiStack) {
                    const dra=ebyid("dialog_resultarea");
                    if (dra) dra.innerHTML=jobj.cfdiStack;
                }
                if (jobj.cfdiLog) console.log(jobj.cfdiLog);
            } else {
                //if (jobj.cfdiLog) console.log(jobj.cfdiLog);
                console.log(jobj);
                if(jobj.xmlId) successFile(prefix+"xml",jobj.xmlId);
                if (jobj.xmlFolio) {
                    const folioElem=ebyid(prefix+"folio");
                    if (folioElem) folioElem.value=jobj.xmlFolio;
                }
                if (jobj.xmlTotal) {
                    const montoElem=ebyid(prefix+"importe");
                    if (montoElem) montoElem.value=parseFloat(jobj.xmlTotal).toFixed(2);
                }
                if (jobj.xmlFecha) {
                    const fechaElem=ebyid(prefix+"fechaConcepto");
                    if (fechaElem) {
                        fechaElem.value=jobj.xmlFecha.substr(0,10);
                        fechaElem.date=jobj.xmlFecha.substr(0,10);
                        fechaElem.time=jobj.xmlFecha.substr(10);
                    }
                }
            }
            if(jobj.pdfId) successFile(prefix+"pdf",jobj.pdfId);
            if (isTmp) addFileOpt={};
        }
    } catch(ex) {
        console.log(ex,"\n",text);
        if (isTmp) {
            if (addFileOpt.xml) failedFile(prefix+"xml","04: Error en respuesta");
            if (addFileOpt.pdf) failedFile(prefix+"pdf","04: Error en respuesta");
            addFileOpt={};
        }
    }
}
function failedResponseFiles(errmsg, parameters, evt) {
    console.log("ERROR "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status+"AL AGREGAR ARCHIVOS: "+errmsg);
    if (parameters.accion==="temporales") { // isTmp
        const prefix=addFileOpt.prefix;
        if (addFileOpt.xml) failedFile(prefix+"xml","05: Error en servidor");
        if (addFileOpt.pdf) failedFile(prefix+"pdf","05: Error en servidor");
        addFileOpt={};
    }
}
function failedFile(type,errmsg) {
    const felm=_resetFileElement(type,"512Error");
    if (felm) {
        if (felm.icon) felm.icon.title=errmsg;
    }
}
function successFile(type,id) {
    const felm=_resetFileElement(type,"200");
    if (felm) {
        if (felm.id) felm.id.value=String(id);
        if (felm.path) felm.path.value="temporal";
        if (felm.icon) felm.icon.classList.add("pointer");
    }
}
function resetFile(type) {
    const felm=_resetFileElement(type,"512Missing");
}
function readyFile(type,name) {
    const felm=_resetFileElement(type,"200");
    if (felm) {
        if (felm.path) {
            felm.path.value="viajes/"+name;
            felm.path.bdval=felm.path.value;
        }
        if (felm.icon) felm.icon.classList.add("pointer");
    }
}
function _resetFileElement(type,imgpart) {
    if (!["xml","nxml","pdf","npdf"].includes(type)) return false;
    const elems={id:ebyid(type+"Id"),icon:ebyid(type+"Icon"),path:ebyid(type+"Path")};
    if (elems.id) elems.id.value="";
    if (elems.path) elems.path.value="";
    if (elems.icon) {
        const rawtype=(type.length>3)?type.slice(-3):type;
        elems.icon.src="imagenes/icons/"+rawtype+imgpart+".png";
        elems.icon.classList.remove("pointer");
        delete elems.icon.title;
    }
    return elems;
}
function preResetFile(type) {
    const rawtype=(type.length>3)?type.slice(-3):type;
    overlayConfirmation({eName:"P",eText:"Se eliminará el archivo "+rawtype.toUpperCase()}, {eText:"CONFIRMACION PARA ELIMINAR"}, function() { resetFile(type); } );
}

// // // // // CREAR VIATICOS // // // // //
function preNewPerDiem() {
    const regIdElem=ebyid("regId");
    if (regIdElem.bdId) {
        overlayConfirmation({eName:"P",eText:"Se perderá la información no guardada del registro abierto."}, {eText:"NUEVO REGISTRO"}, newPerDiem );
    } else newPerDiem();
}
function newPerDiem() {
    if (verifyNewData()) {
        const parameters = { accion:"newPerDiem", beneficiario:ebyid("nbeneficiario").value, empresaId:ebyid("nempresa").value, lugares:ebyid("nlugares").value, reqviaticos:ebyid("nreqviaticos").value,
        viewzone:"nuevo_concepto",
        focuselem:"nconcepto" };
        const bancoElem=ebyid("nbanco");
        if(bancoElem&&bancoElem.value.length>0) parameters.banco=bancoElem.value;
        const cuentaElem=ebyid("ncuentabanco");
        if (cuentaElem&&cuentaElem.value.length>0) parameters.cuentabancaria=cuentaElem.value;
        const clabeElem=ebyid("ncuentaclabe");
        if (clabeElem&&clabeElem.value.length>0) parameters.cuentaclabe=clabeElem.value;
        const obsElem=ebyid("nobservaciones");
        if (obsElem&&obsElem.value.length>0) parameters.observaciones=obsElem.value;
        postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
    }
}
function verifyNewData() {
    if (!verifyEmptyField("nbeneficiario","Debe ingresar el nombre del Beneficiario")) return false;
    if (!verifyEmptyField("nempresa","Debe ingresar la empresa del Beneficiario")) return false;
    if (!verifyEmptyField("nlugares","Debe ingresar los lugares a visitar")) return false;
    if (!verifyEmptyField("nreqviaticos","Debe ingresar el monto total de los viáticos requeridos")) return false;
    return true;
}

// // // // // ABRIR VIATICOS EXISTENTES // // // // //
var opdLock=false;
function preOpenPerDiem(evt) {
    if (opdLock) return;
    const tgt=evt.target;
    console.log("INI function preOpenPerDiem ",tgt);
<?php if ($esSolicitante) { ?>
    if (ebyid("registro_actual_btn").textContent==="BUSCAR REGISTRO") {

        if (tgt.id==="regId" || tgt.id==="beneficiario") {
            if (evt.keyCode===13) {
                if (tgt.value.length==0) return false;
                const eElem=ebyid("empresa");
                if (tgt.id==="regId") {
                    const bElem=ebyid("beneficiario");
                    if (eElem.value.length==0) eElem.focus();
                    else if (bElem.value.length==0) bElem.focus();
                    else {
                        opdLock=true;
                        postService("consultas/CajaChica.php", {accion:"getPerDiem",regid:tgt.value,recipient:bElem.value,empresaId:eElem.value}, viewResponsePD, failPD);
                    }
                } else {
                    const rElem=ebyid("regId");
                    if (rElem.value.length==0) rElem.focus();
                    else if (eElem.value.length==0) eElem.focus();
                    else {
                        opdLock=true;
                        console.log("Parameters: ",{accion:"getPerDiem",regid:rElem.value,recipient:tgt.value,empresaId:eElem.value});
                        postService("consultas/CajaChica.php", {accion:"getPerDiem",regid:rElem.value,recipient:tgt.value,empresaId:eElem.value}, viewResponsePD, failPD);
                    }
                }
            }
        } else if (tgt.id==="openButton") {
            const regIdElem=ebyid("regId");
            const beneficiarioElem=ebyid("beneficiario");
            const empresaElem=ebyid("empresa");
            if (regIdElem.value.length==0) {
                overlayMessage({eName:"P",eText:"Se requiere que indique el folio del registro a buscar."}, {eText:"Campo Requerido"});
                ebyid("overlay").callOnClose=function() { regIdElem.focus(); }
            } else if (beneficiarioElem.value.length==0) {
                overlayMessage({eName:"P",eText:"Se requiere que indique el nombre y/o apellido del beneficiario."}, {eText:"Campo Requerido"});
                ebyid("overlay").callOnClose=function() { beneficiarioElem.focus(); }
            } else if (empresaElem.value.length==0) {
                overlayMessage({eName:"P",eText:"Se requiere que seleccione la empresa que financía sus viáticos."}, {eText:"Campo Requerido"});
                ebyid("overlay").callOnClose=function() { empresaElem.focus(); }
            } else {
                opdLock=true;
                postService("consultas/CajaChica.php", {accion:"getPerDiem",regid:regIdElem.value,recipient:beneficiarioElem.value,empresaId:empresaElem.value}, viewResponsePD, failPD);
            }
        }
    }
<?php } else { ?>
    if (tgt.id==="regId") {
        if (tgt.bdId) {
            if (tgt.value.length==0) {
                overlayConfirmation({eName:"P",eText:"Se limpiaran los datos y se perderá la información no guardada."}, {eText:"NUEVA BÚSQUEDA"}, function() { resetRecord(true); });
                ebyid("overlay").callOnClose=function() {
                    if (tgt.bdId) tgt.value=tgt.bdId;
                    tgt.focus();
                    console.log("FOCUS ON REGID on CLEAN OPEN");
                }
            } else if (tgt.value!==tgt.bdId) {
                const recordId=tgt.value;
                overlayConfirmation({eName:"P",eText:"Se cargarán los datos de otro registro y se perderá la información no guardada."}, {eText:"NUEVA BÚSQUEDA"}, function() {
                    resetRecord(false);
                    tgt.value=recordId;
                    opdLock=true;
                    postService("consultas/CajaChica.php", {accion:"getPerDiem",regid:recordId }, viewResponsePD, failPD);
                });
                ebyid("overlay").callOnClose=function() {
                    if (tgt.bdId) tgt.value=tgt.bdId;
                    tgt.focus();
                    console.log("FOCUS ON REGID ON ANOTHER OPEN");
                }
            }
        } else if (tgt.value.length==0) {
            resetRecord(false);
        } else {
            tgt.bdId=false;
            console.log("FIRST REQUEST: ",tgt);
            opdLock=true;
            postService("consultas/CajaChica.php", {accion:"getPerDiem",regid:tgt.value }, viewResponsePD, failPD);
        }
    } else if (tgt.id==="beneficiario") {
        const regIdElem=ebyid("regId");
        if (regIdElem.bdId) return;
        if (tgt.value.length==0) resetRecord(false);
        else {
            opdLock=true;
            postService("consultas/CajaChica.php", {accion:"getPerDiem",recipient:tgt.value}, viewResponsePD, failPD);
        }
    }
<?php } ?>
}

// // // // // AGREGAR VIATICOS // // // // //
function addPerDiem() {
    if (verifyAddData()) {
        const nfecha=ebyid("nfechaConcepto");
        const parameters = { accion:"addPerDiem", fecha:nfecha.value, concepto:ebyid("nconcepto").value, xmlId:ebyid("nxmlId").value, pdfId:ebyid("npdfId").value, folio:ebyid("nfolio").value, importe:ebyid("nimporte").value, regid:ebyid("regId").value };
        if (nfecha.date) {
            parameters.fechafactura=nfecha.date;
            delete nfecha.date;
        }
        if (nfecha.time) {
            if (parameters.fechafactura) parameters.fechafactura+=" "+nfecha.time;
            delete nfecha.time;
        }
        cladd(ebyid("modifica_concepto_btn"),"hidden");
        ebyid("conceptoId").value="";
        ebyid("concepto").selectedIndex=0;
        const fechaElem=ebyid("fechaConcepto");
        fechaElem.value="";
        if (fechaElem.date) delete fechaElem.date;
        if (fechaElem.time) delete fechaElem.time;
        ebyid("folio").value="";
        ebyid("importe").value="0.00";
        resetFile("xml");
        resetFile("pdf");
        postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
    }
}
function verifyAddData() {
    if (!verifyEmptyField("nconcepto","Debe seleccionar un concepto válido")) return false;
    if (!verifyEmptyField("nfechaConcepto","Debe indicar una fecha válida")) return false;
    const xmlId=ebyid("nxmlId").value;
    const pdfId=ebyid("npdfId").value;
    if ((xmlId.length>0||pdfId.length>0)&&!verifyEmptyField("nfolio","Debe ingresar el folio de su comprobante")) return false;
    if (!verifyEmptyField("nimporte","Debe ingresar el importe requerido")) return false;
    const ridElem=ebyid("regId");
    if (ridElem.bdId) {
        if (ridElem.value.length==0) return showError("Presione LIMPIAR para iniciar uno nuevo!", ridElem);
        if (ridElem.value!==ridElem.bdId) return showError("Presione ABRIR primero!", ridElem);
    } else if (ridElem.value.length>0) {
        return showError("Presione ABRIR primero o borre REGISTRO", ridElem);
    }
    return true;
}

// // // // // MOSTRAR REGISTRO COMPLETO // // // // //
var columnViewKeys=["id","fechasolicitud","beneficiario","lugaresvisita","montototal","status"];
var columnViewName={id:"FOLIO",fechasolicitud:"FECHA",beneficiario:"BENEFICIARIO",lugaresvisita:"VISITA",montototal:"TOTAL",status:"STATUS"};
function viewResponsePD(text,parameters,state,status) {
    if(state<4&&status<=200) return;
    opdLock=false;
    if(text.length==0) {
        console.log("STATE: ",state, "\nSTATUS: ", status, "\nPARAMETERS: ", parameters, "\nText: VACIO"); // , text
        return; // showError("SIN RESPUESTA");
    }
    clrem(ebyid("cleanButton"),"hidden");
    try {
        const jobj=JSON.parse(text);
        //console.log("INI function viewResponsePD: "+text);
        if (jobj.result && jobj.result==="refresh") {
            location.reload(true);
        } else if (jobj.result==="error") {
            console.log("jobj result is error", jobj, parameters);
            if (parameters.accion==="getPerDiem" && parameters.regid)
                showError(jobj.message,ebyid("regId"));
            else showError(jobj.message);
        } else if (jobj.result==="exito") {
            if (jobj.viewzone) {
                showBlock(jobj.viewzone,jobj.focuselem);
                if (jobj.viewzone!=="modifica_concepto") {
                    cladd(ebyid("modifica_concepto_btn"),"hidden");
                }
            }
            const bhd=ebyid("block-header");
            const bbd=ebyid("block-body");
            const bft=ebyid("block-footer");
            ekfil(bhd);
            ekfil(bbd);
            ekfil(bft);
            if (!jobj.datos||jobj.datos.length<=0) {
                resetRecord(true);
                if (parameters.accion==="deleteRecord" && jobj.message) {
                    overlayMessage({eName:"P", className:"boldValue", eText:jobj.message},{eText:"EXITO"});
                }
            } else if (jobj.datos.length>1) {
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
                                        if (!celO.className) celO.className="centered";
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
                                        viewResponsePD(JSON.stringify({result:"exito",message:"Seleccion de registro exitoso",datos:[jobj.datos[+idx]]}),parameters,state,status);
                                    }
                                },
                                onbuild:function(tgt){
                                    if (tgt && tgt.index && tgt.index.slice(0,1)==="B") {
                                        tgt.className="pointer";
                                    }
                                },
                                ongetvalue:function(obj,key) {
                                    delete this.className;
                                    switch(key) {
                                        case "fechasolicitud":
                                            const cutText=obj[key].slice(0,10);
                                            const dateObj=strptime("%Y-%m-%d",cutText);
                                            const fixedVal=strftime(date_format,dateObj);
                                            return fixedVal;
                                        case "montototal":
                                            this.className="pointer padr5 righted inputCurrency";
                                            if(obj[key]) return parseFloat(obj[key]).toFixed(2);
                                            else return "0.00";
                                        default: return obj[key];
                                    }
                                    return obj[key];
                                },
                                onmissedkey:function(obj,key) {
                                    console.log("onmissedkey ( obj:",obj,", key:",key," )");
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
<?php if ($esSolicitante) { ?>
                ebyid('solicitudHCell').textContent='F.SOLICITUD:';
                ebyid('empresaDCell').appendChild(ebyid('empresa'));
                clrem(ebyid('fechaSolicitud'),'hidden');
<?php } ?>
                ebyid("registro_actual_btn").textContent="REGISTRO ACTUAL";
                clrem(ebyid("nuevo_concepto_btn"),"hidden");
                cladd(ebyid("openButton"),"hidden");
                clrem(ebyid("saveButtonArea"),"hidden");
                // Limpiar Nuevo Registro si se recibe Registro Existente
                ebyid("nbeneficiario").value="";
                ebyid("nlugares").value="";
                ebyid("nempresa").value="";
                ebyid("nbanco").value="";
                ebyid("ncuentabanco").value="";
                ebyid("ncuentaclabe").value="";
                ebyid("nobservaciones").value="";
                ebyid("nreqviaticos").value="";
                ebyid("nfechaConcepto").value="";
                ebyid("nconcepto").selectedIndex=0;
                resetFile("nxml");
                resetFile("npdf");
                ebyid("nfolio").value="";
                ebyid("nimporte").value="";
                // Agregar datos del Registro encontrado
                const data=jobj.datos[0];
                const regIdElem=ebyid("regId");
                regIdElem.value=data.id;
                if (data.id.length>0) {
                    regIdElem.bdId=data.id;
                    cladd(ebyid("nuevo_registro_btn"),"hidden");
                }
                let soloFecha=data.fechasolicitud.slice(0,10);
                let fechaObj=strptime("%Y-%m-%d",soloFecha);
                let fechaValor=strftime(date_format,fechaObj);
                let fechaElem=ebyid("fechaSolicitud");
                fechaElem.value=fechaValor;
                if (data.fechapago && data.fechapago.length>0) {
                    soloFecha=data.fechapago.slice(0,10);
                    fechaObj=strptime("%Y-%m-%d",soloFecha);
                    fechaValor=strftime(date_format,fechaObj);
                    fechaElem=ebyid("fechaPago");
                    fechaElem.value=fechaValor;
                    fechaElem.bdval=fechaValor;
                }
                const benElem=ebyid("beneficiario");
                benElem.value=data.beneficiario;
                benElem.bdval=benElem.value;
                const empElem=ebyid("empresa");
                empElem.value=data.empresaId;
                empElem.bdval=empElem.value;
                const bcoElem=ebyid("banco");
                bcoElem.value=data.banco;
                bcoElem.bdval=bcoElem.value;
                const ctaElem=ebyid("cuentabancaria");
                ctaElem.value=data.cuentabancaria;
                ctaElem.bdval=ctaElem.value;
                const cbeElem=ebyid("cuentaclabe");
                cbeElem.value=data.cuentaclabe;
                cbeElem.bdval=cbeElem.value;
                //clrem(ebyid("fechaSolicitudRow"),"hidden");
                //clrem(ebyid("lugaresVRow"),"hidden");
                //clrem(ebyid("viaticosReqRow"),"hidden");
                //clrem(ebyid("montoTotalRow"),"hidden");
                //clrem(ebyid("solicitanteRow"),"hidden");
                //clrem(ebyid("controlRow"),"hidden");
                const lugarElem=ebyid("lugaresV");
                lugarElem.value=data.lugaresvisita;
                lugarElem.bdval=lugarElem.value;
                const obsElem=ebyid("observaciones");
                obsElem.value=data.observaciones;
                obsElem.bdval=obsElem.value;
                const rqvElem=ebyid("viaticosReq");
                rqvElem.value=parseFloat(data.viaticosrequeridos).toFixed(2);
                rqvElem.bdval=rqvElem.value;
                const totalElem=ebyid("montoTotal");
                totalElem.value=parseFloat(data.montototal).toFixed(2);
                totalElem.bdval=totalElem.value;
                const solElem=ebyid("solicitante");
                solElem.value=data.solicitante;
                solElem.bdval=solElem.value;
                const ctrlElem = ebyid("control");
                while (ctrlElem.nextSibling) ctrlElem.parentNode.removeChild(ctrlElem.nextSibling);
                
                if (data.rechazadoPor && data.rechazadoPor.length>0) {
                    ebyid("controlCap").textContent="RECHAZADO POR:";
                    cladd(ctrlElem,"hidden");
                    ctrlElem.parentNode.appendChild(ecrea({eName:"B",eText:data.rechazadoPor}));
                    <?php if ($puedePagar) { ?>
                    ctrlElem.parentNode.append(ecrea({eText:". "}), ecrea({eName:"INPUT", type:"button", id:"paidButton", value:"CAMBIAR A PAGADO", onclick:paidRecord, auth:"0"}));
                    <?php }
                        if ($esSistemas) { ?>
                    ctrlElem.parentNode.append(ecrea({eText:" "}), ecrea({eName:"INPUT", type:"button", id:"resetStatusButton", value:"CAMBIAR A PENDIENTE", onclick:restoreToPending}));
                    <?php } ?>
                    setReadOnlyMode(true);
                } else if (data.pagadoPor && data.pagadoPor.length>0) {
                    ebyid("controlCap").textContent="SITUACION:";
                    cladd(ctrlElem,"hidden");
                    ctrlElem.parentNode.appendChild(ecrea({eName:"B",eText:"PAGADO"}));
                    setReadOnlyMode(true);
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
                    setReadOnlyMode(true);
                } else {
                    ebyid("controlCap").textContent="SITUACION:";
                    clrem(ctrlElem,"hidden");
                    if (ctrlElem.tagName==="INPUT") {
                        ctrlElem.value="PENDIENTE";
                    } else if (ctrlElem.tagName==="SELECT") {
                        ctrlElem.selectedIndex=0;
                    }
                    <?php if ($puedePagar) { ?>
                    ctrlElem.parentNode.append(ecrea({eText:". "}), ecrea({eName:"LABEL", eChilds:[{eName:"INPUT", type:"checkbox", id:"pagadoChk", value:"1"}, {eText:" Marcar Pagado."}]}));
                    <?php } ?>
                    setReadOnlyMode(false);
                }
                fee(lbycn("viewOnEdit"),function(elem){clrem(elem,"hidden");});
                const conceptos=jobj.datos[0].conceptos;
                const conceptoIdElem=ebyid("conceptoId");
                if (Object.keys(conceptos).length>0) {
                    // GENERANDO COLUMNA 1: ENCABEZADOS DE CONCEPTO
                    const rows={
                        head1:[{eName:"TD",className:"concepto"}],
                        head2:[{eName:"TD",className:"concepto"}],
                        head3:[{eName:"TD",className:"concepto"}]
                    };
                    const listaConceptosK=["hospedaje","desayuno","comida","cena","propina","pasaje","uber","avion","hospedaje2","casetas","auto","otros","gasolina","flete"];
                    const mapaConceptos={hospedaje:"HOSPEDAJE X NOCHE", desayuno:"DESAYUNO", comida:"COMIDA", cena:"CENA", propina:"PROPINAS", pasaje:"TAXIS-PASAJE", uber:"UBER", avion:"AVION-PASAJE", hospedaje2:"HOSPEDAJE EXTRA", casetas:"CASETAS", auto:"RENTA DE CARRO", otros:"OTROS", gasolina:"GASOLINA (FORÁNEA)", flete:"PAGO X FLETE"};
                    const sumaConceptos={hospedaje:0, desayuno:0, comida:0, cena:0, propina:0, pasaje:0, uber:0, avion:0, hospedaje2:0, casetas:0, auto:0, otros:0, gasolina:0, flete:0};
                    for (let i=0; i<listaConceptosK.length; i++) { // >
                        const keyCon=listaConceptosK[i];
                        rows[keyCon]=[{eName:"TH",className:"concepto b3333 bluedbg5",eText:mapaConceptos[keyCon]}];
                    }
                    rows.foot=[{eName:"TH",className:"concepto b3333 bluedbg5",eText:"TOTAL"}];
                    // GENERANDO COLUMNAS CONTENIDO
                    for (let mmdd in conceptos) {
                        console.log("LOOP conceptos. mmdd="+mmdd);
                        const diaBloque=conceptos[mmdd];
                        rows.head1.push({eName:"TH",colSpan:"3",className:"diafecha b3313 bluedbg5 centered",eText:"FECHA"});
                        rows.head3.push({eName:"TH",className:"diafolio b3133 bluedbg5 centered",eText:"FOLIO"});
                        rows.head3.push({eName:"TH",className:"diamonto b3131 bluedbg5 centered onprintBR0",eText:"IMPORTE"});
                        rows.head3.push({eName:"TH",className:"b3330 bluedbg5"});
                        let sumaCol=0;
                        let firstCnc=true;
                        for (let c=0; c<listaConceptosK.length; c++) { // >
                            const keyCon=listaConceptosK[c];
                            if (diaBloque[keyCon]) {
                                const items=diaBloque[keyCon];
                                const folioCell={eName:"TD",className:"diafolio b1113 subh21 padv3 oneLine",eChilds:[]};
                                const montoCell={eName:"TD",className:"diamonto b1111 subh21 padv3 righted onprintBR0",eChilds:[]};
                                const editCell={eName:"TD",className:"b1310 subh21",eChilds:[]};
                                for (let i=0; i<items.length; i++) {
                                    const item=items[i];
                                    if (firstCnc) {
                                        firstCnc=false;
                                        rows.head2.push({eName:"TD",colSpan:"3",className:"diafecha campofecha b1333",eText:strftime(date_format,strptime("%Y-%m-%d",item.fecha.slice(0,10)))});
                                    }
                                    if (conceptoIdElem&&(""+conceptoIdElem.value)===(""+item.id)) {
                                        if (item.archivoxml && item.archivoxml.length>0) readyFile("xml",item.archivoxml);
                                        else resetFile("xml");
                                        if (item.archivopdf && item.archivopdf.length>0) readyFile("pdf",item.archivopdf);
                                        else resetFile("pdf");
                                    }
                                    const hasPDF = (item.archivopdf && item.archivopdf.length>0);
                                    const hasXML = (item.archivoxml && item.archivoxml.length>0);
                                    const hasFolio = (item.foliofactura && item.foliofactura.length>0);
                                    if (hasPDF||hasXML) {
                                        const ffval=(hasFolio?item.foliofactura:(hasPDF?"PDF":"XML"));
                                        const filepath=hasPDF?item.archivopdf:item.archivoxml;
                                        const filetype=hasPDF?"application/pdf":"text/xml";
                                        const travelPath=(filepath.length>0&&filepath!=="temporal")?"viajes/"+filepath:"";
                                        folioCell.eChilds.push({eName:"DIV", eChilds:[{eName:"FORM", method:"POST", action:"consultas/docs.php", className:"inlineblock noprint", target:"doc", onsubmit:"window.open('','doc'); return true;",eChilds:[{eName:"INPUT",type:"hidden",name:"path",value:travelPath},{eName:"INPUT",type:"hidden",name:"type",value:filetype},{eName:"INPUT",type:"submit",value:ffval}]},{eName:"SPAN",className:"hidden doprintBlock",eText:ffval}]});
                                    } else if (hasFolio) {
                                        folioCell.eChilds.push({eName:"DIV",eText:item.foliofactura});
                                    } else {
                                        folioCell.eChilds.push({eName:"DIV",eText:""});
                                    }
                                    montoCell.eChilds.push({eName:"DIV",className:"righted",eText:"$"+parseFloat(item.importe).toFixed(2)});
                                    editCell.eChilds.push({eName:"DIV",eChilds:[{eName:"IMG",src:"imagenes/icons/rename12.png",className:"btnLt noprint pointer modifyButton",item:item,onclick:editPerDiem}]});
                                    sumaCol+=+item.importe;
                                    sumaConceptos[keyCon]+=+item.importe;
                                }
                                rows[keyCon].push(folioCell);
                                rows[keyCon].push(montoCell);
                                rows[keyCon].push(editCell);
                            } else {
                                rows[keyCon].push({eName:"TD",className:"diafolio b1113 oneLine padv3"});
                                rows[keyCon].push({eName:"TD",className:"diamonto b1111 padv3 onprintBR0"});
                                rows[keyCon].push({eName:"TD",className:"b1310"});
                            }
                        }
                        rows.foot.push({eName:"TD",className:"diafolio b3000"});
                        rows.foot.push({eName:"TH",className:"diamonto b3333 righted padv3",eText:"$ "+parseFloat(sumaCol).toFixed(2)});
                        rows.foot.push({eName:"TD",className:"b3000"});
                    }
                    // GENERANDO COLUMNA TOTALES
                    rows.head1.push({eName:"TD",className:"totalfila"});
                    rows.head2.push({eName:"TD",className:"totalfila"});
                    rows.head3.push({eName:"TH",className:"totalfila b3333 bluedbg5 centered",eText:"TOTAL"});
                    let sumaTodo=0;
                    for (let i=0; i<listaConceptosK.length; i++) { // >
                        const keyCon=listaConceptosK[i];
                        if (sumaConceptos[keyCon]!=0) {
                            rows[keyCon].push({eName:"TD",className:"totalfila b1313 righted",eText:"$ "+parseFloat(sumaConceptos[keyCon]).toFixed(2)}); // vbottom
                            sumaTodo+=sumaConceptos[keyCon];
                        } else {
                            rows[keyCon].push({eName:"TD",className:"totalfila b1313"});
                        }
                    }
                    rows.foot.push({eName:"TH",className:"totalfila b3333 righted",eText:"$ "+parseFloat(sumaTodo).toFixed(2)});
                    bhd.appendChild(ecrea({eName:"TR",eChilds:rows.head1}));
                    bhd.appendChild(ecrea({eName:"TR",eChilds:rows.head2}));
                    bhd.appendChild(ecrea({eName:"TR",eChilds:rows.head3}));
                    for (let i=0; i<listaConceptosK.length; i++) { // >
                        bbd.appendChild(ecrea({eName:"TR",eChilds:rows[listaConceptosK[i]]}));
                    }
                    bft.appendChild(ecrea({eName:"TR",eChilds:rows.foot}));
                    fee(lbycn("viewWithData"),function(elem){clrem(elem,"hidden");});
                }
            }
        } else {
            console.log("OTHER: ", jobj);
            showError(jobj.message, false, jobj.result);
        }
    } catch(ex) {
        showError(ex.message);
        console.log("Exception caught: ", ex, "\nText: ", text);
    }
}
function failPD(errmsg, parameters, evt) {
    opdLock=false;
    showError(errmsg, false, "ERROR "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status);
}
function setReadOnlyMode(isRO) {
    ebyid("fechaPago").disabled=isRO; // input text
    ebyid("beneficiario").readOnly=isRO; // input text
    ebyid("banco").readOnly=isRO;
    ebyid("cuentabancaria").readOnly=isRO;
    ebyid("cuentaclabe").readOnly=isRO;
    ebyid("lugaresV").readOnly=isRO; // input text
    ebyid("observaciones").readOnly=isRO;
    ebyid("viaticosReq").readOnly=isRO; // input number
    ebyid("saveRecordBtn").disabled=isRO; // button
    ebyid("deleteRecordBtn").disabled=isRO; // button
    ebyid("addFilesBtn").disabled=isRO; // span
    ebyid("nuevo_concepto_btn").disabled=isRO; // span
    ebyid("addPerDiemBtn").disabled=isRO; // button
    const conSel=ebyid("concepto"); // select
    if (conSel.selectedIndex<0) conSel.selectedIndex=0;
    const empSel=ebyid("empresa"); // select | input text
    empSel.readOnly=isRO;
    //empSel.disabled=isRO;
    if (empSel.selectedIndex<0) empSel.selectedIndex=0;
    if (isRO) {
        cladd(ebyid("addFilesBtn"),"disabled"); // span
        cladd(ebyid("nuevo_concepto_btn"),"disabled"); // span
        cladd(ebyid("concepto"),"disabled"); // select
        fee(conSel.options,function(opt) { cladd(opt,"hidden"); }); // option
        cladd(empSel,"no_selection");
        empSel.onkeydown=ignoreAlpha;
        fee(empSel.options,function(opt,idx) { if(empSel.selectedIndex!==idx)cladd(opt,"hidden"); }); // option
    } else {
        clrem(ebyid("addFilesBtn"),"disabled"); // span
        clrem(ebyid("nuevo_concepto_btn"),"disabled"); // span
        clrem(ebyid("concepto"),"disabled"); // select
        fee(conSel.options,function(opt) { clrem(opt,"hidden"); }); // option
        clrem(empSel,"no_selection");
        delete empSel.onkeydown;
        fee(empSel.options,function(opt) { clrem( opt,"hidden"); }); // option
    }
    ebyid("fechaConcepto").disabled=isRO; // input text
    ebyid("invfiles").disabled=isRO; // input file
    ebyid("folio").readOnly=isRO; // input text
    ebyid("importe").readOnly=isRO; // input number
    ebyid("fixPerDiemBtn").disabled=isRO; // button
    ebyid("delPerDiemBtn").disabled=isRO; // button
}

// // // // // LIMPIAR VISTA // // // // //
function preResetRecord(bloque) {
    if (ebyid("regId").bdId) {
        overlayConfirmation({eName:"P",eText:"Se limpiarán los datos y se perderá la información no guardada."}, {eText:"NUEVA BÚSQUEDA"}, function() {
            resetRecord(false);
            if (bloque==="nuevo") showBlock('nuevo_registro','nbeneficiario');
        });
        ebyid("overlay").callOnClose=function() {
            const tgt=ebyid("regId");
            if (tgt.bdId) tgt.value=tgt.bdId;
            tgt.focus();
            console.log("FOCUS ON REGID IN PRERESET");
        }
    } else resetRecord(false);
}
function resetRecord(toNew) {
    clrem(ebyid("nuevo_registro_btn"),"hidden");
    ebyid("nbeneficiario").value="";
    ebyid("nlugares").value="";
    ebyid("nreqviaticos").value="0.00";
    if (toNew) {
        showBlock("nuevo_registro","nbeneficiario");
    }
    setReadOnlyMode(false);
    ebyid("registro_actual_btn").textContent="BUSCAR REGISTRO";
    const regIdElem=ebyid("regId");
    regIdElem.value="";
    regIdElem.bdId=false;
    ebyid("beneficiario").value="";
    ebyid("fechaSolicitud").value="";
    ebyid("fechaPago").value="";
    ebyid("empresa").value="";
    ebyid("banco").value="";
    ebyid("cuentabancaria").value="";
    ebyid("cuentaclabe").value="";
    ebyid("observaciones").value="";

    fee(lbycn("viewOnEdit"),function(elem){cladd(elem,"hidden");});
    //cladd(ebyid("fechaSolicitudRow"),"hidden");
    //cladd(ebyid("lugaresVRow"),"hidden");
    //cladd(ebyid("viaticosReqRow"),"hidden");
    //cladd(ebyid("montoTotalRow"),"hidden");
    //cladd(ebyid("solicitanteRow"),"hidden");
    //cladd(ebyid("controlRow"),"hidden");

<?php if ($esSolicitante) { ?>
    const sh=ebyid('solicitudHCell');
    sh.textContent='EMPRESA:';
    cladd(ebyid('fechaSolicitud'),'hidden');
    const sd=ebyid('solicitudDCell');
    sd.appendChild(ebyid('empresa'));
    clrem(sh,'hidden');
    clrem(sd,'hidden');
<?php } ?>

    ebyid("lugaresV").value="";
    ebyid("viaticosReq").value="0.00";
    ebyid("montoTotal").value="0.00";
    ebyid("solicitante").value="";
    const ctrlElem=ebyid("control");
    if (ctrlElem.tagName==="INPUT") ctrlElem.value="";
    else if (ctrlElem.tagName==="SELECT") ctrlElem.selectedIndex=0;
    ebyid("controlCap").textContent="SITUACION:";
    //cladd(ebyid("saveRecordRow"),"hidden");
    //clrem(ebyid("browseRecordRow"),"hidden");
    clrem(ebyid("openButton"),"hidden");
    cladd(ebyid("cleanButton"),"hidden");
    cladd(ebyid("saveButtonArea"),"hidden");
    fee(lbycn("viewWithData"),function(elem){cladd(elem,"hidden");});
    ekfil(ebyid("block-header"));
    ekfil(ebyid("block-body"));
    ekfil(ebyid("block-footer"));
    cladd(ebyid("nuevo_concepto_btn"),"hidden");
    ebyid("nconcepto").selectedIndex=0;
    const nfechaElem=ebyid("nfechaConcepto");
    nfechaElem.value="";
    if (nfechaElem.date) delete nfechaElem.date;
    if (nfechaElem.time) delete nfechaElem.time;
    ebyid("nfolio").value="";
    ebyid("nimporte").value="0.00";
    resetFile("nxml");
    resetFile("npdf");
    cladd(ebyid("modifica_concepto_btn"),"hidden");
    ebyid("conceptoId").value="";
    ebyid("concepto").selectedIndex=0;
    const fechaElem=ebyid("fechaConcepto");
    fechaElem.value="";
    if (fechaElem.date) delete fechaElem.date;
    if (fechaElem.time) delete fechaElem.time;
    ebyid("folio").value="";
    ebyid("importe").value="0.00";
    resetFile("xml");
    resetFile("pdf");
}

// // // // // CAMBIO DE SITUACION (CONTROL) // // // // //
<?php if ($esSistemas) { ?>
function restoreToPending(event) {
    const tgt=event.target?event.target:false;
    const regIdElem=ebyid("regId");
    if (!regIdElem.bdId||!regIdElem.bdId) return showError("Debe abrir un registro válido", regIdElem);
    if (regIdElem.bdId===regIdElem.value) {
        const parameters = { accion:"saveRecord", regid:regIdElem.bdId, control:"pendiente" };
        postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
    } else console.log("NO COINCIDE ID BD='"+regIdElem.bdId+"' vs VAL='"+regIdElem.value+"'");
}
<?php } ?>
<?php if ($puedePagar) { ?>
function paidRecord(event) {
    const tgt=event.target?event.target:false;
    const regIdElem=ebyid("regId");
    if (!regIdElem.bdId||!regIdElem.bdId) return showError("Debe abrir un registro válido", regIdElem);
    if (regIdElem.bdId===regIdElem.value) {
        const parameters = { accion:"saveRecord", regid:regIdElem.bdId, control:"pagado" };
        if (tgt && tgt.hasAttribute("auth")) {
            const authVal=tgt.getAttribute("auth");
            if (authVal==="0") parameters.control2="autorizar";
        }
        postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
    } else console.log("NO COINCIDE ID BD='"+regIdElem.bdId+"' vs VAL='"+regIdElem.value+"'");
}
<?php } ?>

// // // // // GUARDAR VIATICOS // // // // //
function saveRecord() {
    console.log("INI function saveRecord");
    const regIdElem=ebyid("regId");
    if (!regIdElem.bdId) showError("Debe abrir un registro válido para poder guardarlo", regIdElem);
    else if (regIdElem.bdId===regIdElem.value) {
        const parameters = { accion:"saveRecord", regid:regIdElem.bdId };
        const fpgElem=ebyid("fechaPago");
        const changedFPago=(fpgElem && fpgElem.value!==fpgElem.dbval);
        if (changedFPago) parameters.fechapago=fpgElem.value;
        const benElem=ebyid("beneficiario");
        const changedBenef=(benElem && benElem.value!==benElem.bdval);
        if (changedBenef) parameters.beneficiario=benElem.value;
        const empElem=ebyid("empresa");
        const changedEmpresa=(empElem && empElem.value!==empElem.bdval);
        if (changedEmpresa) parameters.empresaId=empElem.value;
        const bcoElem=ebyid("banco");
        const changedBanco=(bcoElem && bcoElem.value!==bcoElem.bdval);
        if (changedBanco) parameters.banco=bcoElem.value;
        const ctaElem=ebyid("cuentabancaria");
        const changedCuenta=(ctaElem && ctaElem.value!==ctaElem.bdval);
        if (changedCuenta) parameters.cuentabancaria=ctaElem.value;
        const cbeElem=ebyid("cuentaclabe");
        const changedClabe=(cbeElem && cbeElem.value!==cbeElem.bdval);
        if (changedClabe) parameters.cuentaclabe=cbeElem.value;
        const placeElem=ebyid("lugaresV");
        const changedPlace=(placeElem && placeElem.value!==placeElem.bdval);
        if (changedPlace) parameters.lugares=placeElem.value;
        const obsElem=ebyid("observaciones");
        const changedObs=(obsElem && obsElem.value!==obsElem.bdval);
        if (changedObs) parameters.observaciones=obsElem.value;
        const rqvElem=ebyid("viaticosReq");
        const changedRQV=(rqvElem && rqvElem.value!==rqvElem.bdval);
        if (changedRQV) parameters.reqviaticos=rqvElem.value;
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
        if (changedFPago || changedBenef || changedEmpresa || changedBanco || changedCuenta || changedClabe || changedPlace || changedObs || changedRQV || changedCtrl) {
            console.log("GUARDANDO CAMBIOS");
            postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
        } else console.log("SIN CAMBIOS"); // ToDo: No hacer nada o mandar mensaje de que no hay cambios...
    } else console.log("NO COINCIDE ID BD='"+regIdElem.bdId+"' vs VAL='"+regIdElem.value+"'");
}

// // // // // EDITAR CONCEPTO // // // // //
function editPerDiem(event) {
    const tgt=event.target?event.target:false;
    const item=tgt&&tgt.item?tgt.item:false;
    console.log("INI function editPerDiem "+(item?item.id:" NULL"));
    fee(lbycn("modifyButton"),element=>cladd(element,"noprint"));
    clrem(tgt,"noprint");
    const cIdElem=ebyid("conceptoId");
    cIdElem.value=item.id;
    cIdElem.bdval=cIdElem.value;
    const conElem=ebyid("concepto");
    conElem.value=item.concepto;
    conElem.bdval=conElem.value;
    const fechaValor=strftime(date_format,strptime("%Y-%m-%d",item.fecha.slice(0,10)));
    const fcElem=ebyid("fechaConcepto");
    fcElem.value=fechaValor;
    fcElem.bdval=fcElem.value;
    if (item.fecha.length>10)
        fcElem.time=item.fecha.slice(11);
    if (item.fechafactura) {
        fcElem.date=strftime(date_format,strptime("%Y-%m-%d",item.fechafactura.slice(0,10)));
        if (item.fechafactura.length>10)
            fcElem.time=item.fechafactura.slice(11);
    }
    const folioElem=ebyid("folio");
    folioElem.value=item.foliofactura;
    folioElem.bdval=folioElem.value;
    const importeElem=ebyid("importe");
    importeElem.value=parseFloat(item.importe).toFixed(2);
    importeElem.bdval=importeElem.value;
    if (item.archivoxml && item.archivoxml.length>0)
        readyFile("xml",item.archivoxml);
    else resetFile("xml");
    if (item.archivopdf && item.archivopdf.length>0)
        readyFile("pdf",item.archivopdf);
    else resetFile("pdf");
    showBlock("modifica_concepto","concepto");
}
// // // // // MODIFICAR CONCEPTO // // // // //
function fixPerDiem() {
    console.log("INI function fixPerDiem");
    if (verifyFixData()) {
        const regIdElem=ebyid("regId");
        const cIdElem=ebyid("conceptoId");
        const parameters = { accion:"fixPerDiem", regid:regIdElem.bdId, conId:cIdElem.value };
        const conElem=ebyid("concepto");
        const changedConcepto=(conElem && conElem.value!==conElem.bdval);
        if (changedConcepto) parameters.concepto=conElem.value;
        const fechaElem=ebyid("fechaConcepto");
        const changedFecha=(fechaElem && fechaElem.value!==fechaElem.bdval);
        if (changedFecha) {
            parameters.fecha=fechaElem.value;
        } else {
            parameters.fechaActual=fechaElem.value;
        }
        const changedFechaFactura=(fechaElem && fechaElem.date && fechaElem.date!==fechaElem.bdval);
        if (changedFechaFactura) {
            parameters.fechafactura=fechaElem.date;
            if (fechaElem.time) parameters.fechafactura+=" "+fechaElem.time;
        }
        const folioElem=ebyid("folio");
        const changedFolio=(folioElem && (!folioElem.bdval || folioElem.value!==folioElem.bdval));
        if (changedFolio) parameters.folio=folioElem.value;
        const importeElem = ebyid("importe");
        const changedImporte=(importeElem && importeElem.value!==importeElem.bdval);
        if (changedImporte) parameters.importe=importeElem.value;
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
        if (changedConcepto || changedFecha || changedFechaFactura || changedFolio || changedImporte || changedXMLId || changedXMLPath || changedPDFId || changedPDFPath) {
            console.log("GUARDANDO CAMBIOS: "+JSON.stringify(parameters));
            postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
        } else console.log("SIN CAMBIOS"); // ToDo: No hacer nada o mandar mensaje de que no hay cambios...
    } else console.log("NO COINCIDE ID BD='"+regIdElem.bdId+"' vs VAL='"+regIdElem.value+"'");
}
function verifyFixData() {
    const regIdElem=ebyid("regId");
    if (!regIdElem||!regIdElem.bdId) { showError("Debe abrir un registro válido para poder modificarlo", regIdElem); return false; }
    if (!verifyEmptyField("concepto","Debe seleccionar un concepto válido")) return false;
    if (!verifyEmptyField("fechaConcepto","Debe ingresar la fecha del concepto")) return false;
    const xmlId=ebyid("xmlId").value;
    const pdfId=ebyid("pdfId").value;
    if (xmlId.length>0&&!verifyEmptyField("folio","Debe ingresar el folio de su comprobante")) return false;
    if (!verifyEmptyField("importe","Debe ingresar el importe requerido")) return false;
    return true;
}

// // // // // ELIMINAR REGISTRO // // // // //
function preDeleteRecord() {
    const regIdElem=ebyid("regId");
    if (regIdElem.bdId) {
        overlayConfirmation({eName:"P",eText:"Se borrará el registro completamente."}, {eText:"CONFIRMACION PARA ELIMINAR"}, deleteRecord );
    }
}
function deleteRecord() {
    console.log("INI function deleteRecord");
    const regIdElem=ebyid("regId");
    if (!regIdElem.bdId) showError("Debe abrir un registro válido para poder borrarlo", regIdElem);
    else {
        const parameters = {
            accion:"deleteRecord",
            regid:regIdElem.bdId
        };
        postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
    }
}

// // // // // ELIMINAR CONCEPTO // // // // //
function preDelPerDiem() {
    const cIdElem=ebyid("conceptoId");
    if (cIdElem.value.length>0) {
        overlayConfirmation({eName:"P",eText:"Se borrará el concepto completamente."}, {eText:"CONFIRMACION PARA ELIMINAR"}, delPerDiem );
    }
}
function delPerDiem() {
    console.log("INI function delPerDiem");
    const regIdElem=ebyid("regId");
    const cIdElem=ebyid("conceptoId");
    if (regIdElem.bdId && cIdElem.value.length>0) {
        const parameters = {
            accion:"delPerDiem",
            perDiemId:cIdElem.value,
            regid:regIdElem.bdId,
            viewzone:"registro_actual"
        };
        postService("consultas/CajaChica.php", parameters, viewResponsePD, failPD);
    }
}

// // // // // UTILERIAS // // // // //
function showBlock(id,focuselem) {
    clrem(ebyid(id+"_btn"),"hidden");
    fee(lbycn("tabla_viaticos"), function(elem) { cladd(elem, "hidden"); });
    fee(lbycn("btnTab"), function(elem) { clrem(elem, "selected"); });
    clrem(ebyid(id),"hidden");
    cladd(ebyid(id+"_btn"),"selected");
    if(focuselem) {
        ebyid(focuselem).focus();
        console.log("FOCUS ON "+focuselem.id+" IN SHOWBLOCK");
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
<?php
clog1seq(-1);
clog2end("scripts.viajero");
