<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.empleados");
clog1seq(1);
?>
var emplCache={};
function doFocusOn(elem) {
    if (typeof elem==="string") elem=ebyid(elem);
    if (elem) elem.focus();
}
var timeQuery=false;
function doQuery(event) {
    //console.log("INI function doQuery ",event?event.target.value:false);
    clearTimeout(timeQuery);
    const tgt=event.target;
    if(tgt.id==="empl_num") clearResults();
    else {
        clrem(ebyid("clearEmployeeButton"),"hidden");
        const ftelem=ebyid("empl_footer");
        let fttxt=ftelem.textContent;
        let ftidx=-1;
        let ftval="0";
        if (tgt.value!==tgt.oldValue) ftval="1";
        switch(tgt.id) {
            case "empl_name": ftidx=0; break;
            case "empl_acccard": ftidx=1; break;
            case "empl_accuniq": ftidx=2; break;
            case "empl_gpoalias": ftidx=3; break;
            case "empl_status": ftidx=4; break;
        }
        if (ebyid("empl_id").value.length>0) {
            if (ftidx>=0) fttxt=fttxt.substr(0,ftidx)+ftval+fttxt.substr(ftidx+1);
            ekfil(ftelem);
            ftelem.appendChild(ecrea({eText:fttxt}));
        }
        const seb=ebyid("saveEmployeeButton");
        if ((+ftelem.textContent)>0) clrem(seb,"hidden");
        else cladd(seb,"hidden");
        return;
    }
    timeQuery=setTimeout(doDelayedQuery,2000);
}
function doDelayedQuery() {
    //console.log("INI function doDelayedQuery");
    const qdt=getQueryData();
    const ck=JSON.stringify(qdt);
    if (ck in emplCache) {
        console.log("CACHE! "+ck+": ",emplCache[ck]);
        viewResult(emplCache[ck]);
    } else {
        qdt.accion="browse";
        postService("consultas/Empleados.php",qdt,function(text,params,state,status) {
            if(state<4&&status<=200) return;
            if(state>4||status!=200) {
                console.log("ERROR DE CONEXION. STATE="+state+", STATUS="+status+"\n"+text);
                return;
            }
            if (text.length==0) {
                console.log("SIN RESPUESTA");
                return;
            }
            //console.log("PARAMS: ",params);
            try {
                let jobj=JSON.parse(text);
                if (jobj.result) {
                    if (jobj.result==="refresh") {
                        location.reload(true);
                    } else {
                        emplCache[ck]=jobj;
                        viewResult(jobj); //.result);
                    }
                } else console.log("SIN RESULTADO: \n"+text);
            } catch(ex) {
                console.log("ERROR EN RESPUESTA: ",ex,"\n",text);
            }
        });
    }
}
function viewResult(resultData) {
    //console.log("INI function viewResult : ",resultData);
    if (resultData.result.length===0) clearResults();
    else if (resultData.result.length===1 && ebyid("pageNumber").value==="1") {
        clearResults();
        const user=resultData.result[0];
        if(user.id) ebyid("empl_id").value=user.id;
        if(user.numero) { const empNum=ebyid("empl_num");
            empNum.value=user.numero; empNum.oldValue=empNum.value; }
        if(user.nombre) { const empNam=ebyid("empl_name");
            empNam.value=user.nombre; empNam.oldValue=empNam.value; }
        if(user.cuentaTC) { const empAcr=ebyid("empl_acccard");
            empAcr.value=user.cuentaTC; empAcr.oldValue=empAcr.value; }
        if(user.cuentaCLABE) { const empAcq=ebyid("empl_accuniq");
            empAcq.value=user.cuentaCLABE; empAcq.oldValue=empAcq.value; }
        if(user.empresa) { const empGpo=ebyid("empl_gpoalias");
            empGpo.value=user.empresa; empGpo.oldValue=empGpo.value; }
        if(user.status) { const empStt=ebyid("empl_status");
            empStt.value=user.status; empStt.oldValue=empStt.value; }
        clrem(ebyid("saveEmployeeButton"),"hidden");
        clrem(ebyid("clearEmployeeButton"),"hidden");
    } else {
        const empHdr=[{eName:"TR",eChilds:[{eName:"TH",eText:"N\u00FAmero"},{eName:"TH",eText:"Nombre"},{eName:"TH",eText:"Empresa"},{eName:"TH",eText:"Status"}]}];
        const empBdy=[];
        const list=resultData.result;
        for(let i=0;i<list.length;i++) { // >
            empBdy.push({eName:"TR",empId:list[i].id,empTC:list[i
            ].cuentaTC,empUC:list[i].cuentaCLABE,ondblclick:function(event) {
                const emplArea=ebyid("area_empleados");
                const empId=ebyid("empl_id");
                const empNum=ebyid("empl_num");
                const empNam=ebyid("empl_name");
                const empAcr=ebyid("empl_acccard");
                const empAcq=ebyid("empl_accuniq");
                const empGpo=ebyid("empl_gpoalias");
                const empStt=ebyid("empl_status");
                if(empId.value.length==0) {
                    emplArea.browseData={numero:empNum.value};
                    if (empNam.value.length>0) emplArea.browseData.nombre=empNam.value;
                    if (empAcr.value.length>0) emplArea.browseData.cuentaTC=empAcr.value;
                    if (empAcq.value.length>0) emplArea.browseData.cuentaCLABE=empAcq.value;
                    if (empGpo.value.length>0) emplArea.browseData.empresa=empGpo.value;
                    if (empStt.value.length>0) emplArea.browseData.status=empStt.value;
                }
                if (emplArea.browseData) {
                    const pgNum=ebyid("pageNumber");
                    emplArea.browseData.pageNum=pgNum.value;
                    const rwPPg=ebyid("rowsPerPage");
                    emplArea.browseData.rowsPerPage=rwPPg.value;
                }
                let tgt=event.target;
                if (tgt.tagName==="TD") tgt=tgt.parentNode;
                const cells=tgt.children;
                empId.value=tgt.empId;
                empNum.value=cells[0].textContent;
                empNum.oldValue=empNum.value;
                empNam.value=cells[1].textContent;
                empNam.oldValue=empNam.value;
                empAcr.value=tgt.empTC;
                empAcr.oldValue=empAcr.value;
                empAcq.value=tgt.empUC;
                empAcq.oldValue=empAcq.value;
                empGpo.value=cells[2].textContent;
                empGpo.oldValue=empGpo.value;
                empStt.value=cells[3].textContent;
                empStt.oldValue=empStt.value;
                cladd(ebyid("saveEmployeeButton"),"hidden");
                //console.log(tgt);
            },eChilds:[{eName:"TD",eText:list[i].numero},{eName:"TD",eText:list[i].nombre},{eName:"TD",eText:list[i].empresa},{eName:"TD",eText:list[i].status}]});
            //const qdt={numero:empNum.value};
            //const qdr={}
            //const ck=JSON.stringify();
            // TODO: Agregar a cache cada empleado obtenido. Armando bloque pero mejor que solo sea un string: "numero=<numero>" y al comparar cache incluir segunda busqueda con ese string
            // TODO: en template agregar icono nube que borre empleado del cache: si se ingreso el numero q borre el string equivalente de cache tambien.
        }
        const prvPgBtn={eName:"SPAN",className:"btnFX btn20",eText:"-",onclick:function() {
            const pgNum=ebyid("pageNumber");
            pgNum.value=""+(+pgNum.value-1);
            ekfil(ebyid("lista_empleados"));
            const emplArea=ebyid("area_empleados");
            if (emplArea.browseData)
                emplArea.browseData.pageNum=pgNum.value;
            doDelayedQuery();
        }};
        if ((+ebyid("pageNumber").value)<=1) prvPgBtn.className+=" invisible";
        const nxtPgBtn={eName:"SPAN",className:"btnFX btn20",eText:"+",onclick:function() {
            const pgNum=ebyid("pageNumber");
            pgNum.value=""+(+pgNum.value+1);
            ekfil(ebyid("lista_empleados"));
            const emplArea=ebyid("area_empleados");
            if (emplArea.browseData)
                emplArea.browseData.pageNum=pgNum.value;
            doDelayedQuery();
        }};
        if ((+ebyid("pageNumber").value)>=(+resultData.lastPage)) nxtPgBtn.className+=" invisible";
        ebyid("lastPage").value=""+resultData.lastPage;
        const empLst={eName:"TABLE",className:"centered",eChilds:[{eName:"THEAD",eChilds:empHdr},{eName:"TBODY",eChilds:empBdy}]};
        ebyid("lista_empleados").appendChild(ecrea(empLst));
        const navLst={eName:"DIV",className:"centered",eChilds:[prvPgBtn,{eText:" "},nxtPgBtn]};
        ebyid("lista_empleados").appendChild(ecrea(navLst));
        clrem(ebyid("clearEmployeeButton"),"hidden");
    }
}
function clearResults() {
    const empId=ebyid("empl_id");
    empId.value=""; empId.oldValue="";
    const empNam=ebyid("empl_name");
    empNam.value=""; empNam.oldValue="";
    const empAcr=ebyid("empl_acccard");
    empAcr.value=""; empAcr.oldValue="";
    const empAcq=ebyid("empl_accuniq");
    empAcq.value=""; empAcq.oldValue="";
    const empGpo=ebyid("empl_gpoalias");
    empGpo.value=""; empGpo.oldValue="";
    const empStt=ebyid("empl_status");
    empStt.value="activo"; empStt.oldValue="activo";
    cladd(ebyid("saveEmployeeButton"),"hidden");
    cladd(ebyid("clearEmployeeButton"),"hidden");
    ekfil(ebyid("lista_empleados"));
    ebyid("pageNumber").value="1";
    ebyid("rowsPerPage").value="10";
    const emplArea=ebyid("area_empleados");
    if(emplArea.browseData) delete emplArea.browseData;
}
function saveResults() {
    console.log("INI function saveResults");
}
function getQueryData() {
    const emplArea=ebyid("area_empleados");
    if(emplArea.browseData) return emplArea.browseData;
    const key={};
    const idE=ebyid("empl_id");
    if (idE.value.length>0) key.id=idE.value;
    const noE=ebyid("empl_num");
    if (noE.value.length>0) key.numero=noE.value;
    const namE=ebyid("empl_name");
    if (namE.value.length>0) key.nombre=namE.value;
    const tcE=ebyid("empl_acccard");
    if (tcE.value.length>0) key.cuentaTC=tcE.value;
    const clabE=ebyid("empl_accuniq");
    if (clabE.value.length>0) key.cuentaCLABE=clabE.value;
    const gpE=ebyid("empl_gpoalias");
    if (gpE.value.length>0) key.empresa=gpE.value;
    const sttE=ebyid("empl_status");
    if (sttE.value.length>0) key.status=sttE.value;
    const pgNum=ebyid("pageNumber");
    key.pageNum=pgNum.value;
    const rwPPg=ebyid("rowsPerPage");
    key.rowsPerPage=rwPPg.value;
    return key;
}
function csvUpload() {
    //console.log("INI function csvUpload");
    const massFile=ebyid("empl_file");
    const file=massFile.files[0];
    fee(lbycn("deleteOnStart"), function(elem){ekil(elem);});
    if (window.FileReader) {
        const fraElem=ebyid("fileresultArea");
        if (fraElem===null) buildReviewCsvWindow();
        else {
            const encodingElement=ebyid("empl_encoding");
            if (encodingElement.value==="Windows-1252") doFocusOn("WIN_encoding");
            else if (encodingElement.value==="UTF-8") doFocusOn("utf8_encoding");
        }
        getAsCSV(file,csvFileHandler,csvErrorHandler);
    } else {
        console.log("FileReader no disponible en este navegador.");
    }
}
function buildReviewCsvWindow() {
    const headers=[{eName:"TR",eChilds:[{eName:"TH",eText:"Número"},{eName:"TH",eText:"Nombre"},{eName:"TH",eText:"Cuenta TC"},{eName:"TH",eText:"CLABE"},{eName:"TH",eText:"Empresa"},{eName:"TH",eText:"Status"}]}];
    const contents=[{eName:"TR",className:"deleteWhenDone",eChilds:[{eName:"TD",colSpan:"6",eChilds:[{eName:"IMG",src:"imagenes/icons/rollwait2.gif",className:"centered"}]}]}];
    const table={eName:"TABLE",className:"noApply centered brdr1d",eChilds:[{eName:"THEAD",className:"bgblue",eChilds:headers},{eName:"TBODY",id:"fileresultArea",eChilds:contents}]};

    const encodingElement=ebyid("empl_encoding");
/*
    const optionUTF8={eName:"OPTION",value:"UTF-8",eText:"UTF-8"};
    const optionWIN={eName:"OPTION",value:"Windows-1252",eText:"Windows-1252"};
    if(encodingElement.value==="UTF-8") optionUTF8.selected=true;
    else if(encodingElement.value==="Windows-1252") optionWIN.selected=true;
    const encodeList={eName:"SELECT",className:"centered",eChilds:[optionUTF8, optionWIN],onchange:function(event){ebyid('empl_encoding').value=event.target.value;csvUpload();}};
*/
    const radioUTF8={eName:"INPUT",type:"radio",name:"encodingChoice",id:"utf8_encoding",value:"UTF-8",onclick:encodingChoiceClick};
    const radioWIN={eName:"INPUT",type:"radio",name:"encodingChoice",id:"WIN_encoding",value:"Windows-1252",onclick:encodingChoiceClick};
    if (encodingElement.value==="Windows-1252") radioWIN.checked=true;
    else if (encodingElement.value==="UTF-8") radioUTF8.checked=true;

    const structure=[{eName:"DIV",className:"sticky toTop basicBG padh2",eChilds:[]},{eName:"DIV",eChilds:[table]},{eName:"DIV",className:"sticky toBottom basicBG padh2",eChilds:[{eText:"Corregir codificaci\u00F3n de archivo:"}/*,encodeList*/,radioWIN,{eText:"Windows"},radioUTF8,{eText:"UTF-8"},{eName:"BR"},{eName:"INPUT",type:"BUTTON",id:"accept_overlay",value:"AGREGAR EMPLEADOS",onclick:function() {console.log('Agregando Empleados!!!');massUpload();}}]}];

    overlayMessage(structure, "Verificar contenido de archivo..."); //+file.name+" ["+human_filesize(file.size)+"]");
    ebyid("overlay").callOnClose=function() { ebyid("empl_file").value=""; doFocusOn('empl_num'); };
}
function encodingChoiceClick(event) {
    //console.log("INI function encodingChoiceClick "+event.target.value);
    ebyid("empl_encoding").value=event.target.value;
    csvUpload();
}
function getAsCSV(fileToRead, csvCallback, errorCallback) {
    const encodingElement=ebyid("empl_encoding");
    let encoding="UTF-8";
    if (encodingElement && encodingElement.value.length>0)
        encoding=encodingElement.value;
    const reader = new FileReader();
    reader.readAsText(fileToRead,encoding);
    reader.onload = csvCallback;
    reader.onerror = errorCallback;
}
function csvFileHandler(event) {
    const textContent=event.target.result;
    const allTextLines = textContent.split(/\r\n|\n/);
    const fraElem=ebyid("fileresultArea");
    for (var i=0; i<allTextLines.length; i++) { // >
        let data=allTextLines[i].split(",");
        let tarr=[];
        for (var j=0; j<data.length; j++) { //>
            tarr.push({eName:"TD",eText:data[j]});
        }
        fraElem.appendChild(ecrea({eName:"TR",className:"deleteOnStart",eChilds:tarr}));
    }
    fee(lbycn("deleteWhenDone"), function(elem){ekil(elem);}); // Eliminar elementos que contengan class deleteWhenDone
}
function csvErrorHandler(event) {
    const fraElem=ebyid("fileresultArea");
    ekfil(fraElem);
    let errMsg="";
    if (event.target.error.name==="NotReadableError") {
        errMsg="No fue posible leer el archivo.";
    } else {
        errMsg="Error '"+event.target.error.name+"' ("+event.target.error.code+") en FileReader: "+event.target.error.message;
    }
    fraElem.appendChild(ecrea({eName:"TR",className:"deleteOnStart",eChilds:[{eName:"TD",colSpan:"6",eText:errMsg}]}));
    ebyid("empl_file").value="";
}
function massUpload() {
    const massFile=ebyid("empl_file");
    const file=massFile.files[0];
    const encodingElement=ebyid("empl_encoding");
    ekfil("dialog_resultarea");
    ebyid("dialog_resultarea").appendChild(ecrea({eName:"IMG",src:"<?=$waitImgName?>",className:"centered"}));
    postService("consultas/Empleados.php",{"accion":"massUpload",encoding:encodingElement.value,file:file},function(text,params,state,status) {
        if(state<4&&status<=200) return;
        if(state>4||status!=200) {
            ekfil("dialog_resultarea");
            const errmsg={eName:"P",className:"errorLabel",eText:"Ocurri\u00F3 un error de conectividad. Consulte al administrador del Portal."};
            ebyid("dialog_resultarea").appendChild(ecrea(errmsg));
            //console.log("ERROR DE CONEXION. STATE="+state+", STATUS="+status+"\n"+text);
            return;
        }
        if (text.length==0) {
            ekfil("dialog_resultarea");
            const errmsg={eName:"P",className:"errorLabel",eText:"No se obtuvo respuesta del servidor. Consulte al administrador del Portal."};
            ebyid("dialog_resultarea").appendChild(ecrea(errmsg));
            //console.log("SIN RESPUESTA");
            return;
        }
        console.log("PARAMS: ",params);
        try {
            let jobj=JSON.parse(text);
            if (jobj.result) {
                if (jobj.result==="refresh") {
                    location.reload(true);
                } else {
                    ekfil("dialog_resultarea");
                    ebyid("dialog_resultarea").appendChild(ecrea({eName:"P",eText:"Archivo cargado satisfactoriamente."}));
                    ebyid("dialog_resultarea").appendChild(ecrea({eName:"P",eText:jobj.result}));
                    if (jobj.error.length>0) {
                        ebyid("dialog_resultarea").appendChild(ecrea({eName:"P",eText:"Ocurrieron los siguientes errores:"}));
                        const errLst={eName:"UL",eChilds:[]};
                        for(let i=0; i<jobj.error.length; i++) {
                            errLst.eChilds[i]={eName:"LI",eText:jobj.error[i]};
                        }
                        ebyid("dialog_resultarea").appendChild(ecrea(errLst));
                    }
                    //console.log("EXITO: \n"+text);
                }
            } else {
                ekfil("dialog_resultarea");
                const errmsg={eName:"P",className:"errorLabel",eText:"No se obtuvo resultado del servidor. Consulte al administrador del Portal."};
                ebyid("dialog_resultarea").appendChild(ecrea(errmsg));
                //console.log("SIN RESULTADO: \n"+text);
            }
        } catch(ex) {
            ekfil("dialog_resultarea");
            ebyid("dialog_resultarea").appendChild(ecrea({eName:"P",className:"errorLabel",eText:"Ocurri\u00F3 un error al cargar los datos:"}));
            ebyid("dialog_resultarea").appendChild(ecrea({eName:"P",className:"errorLabel",eText:ex.name+": "+ex.message}));
            //console.log("ERROR EN RESPUESTA: ",ex,"\n",text);
        }
    });
}
<?php
clog1seq(-1);
clog2end("scripts.empleados");
