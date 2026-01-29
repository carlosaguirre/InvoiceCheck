<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.adminfactura");
clog1seq(1);
?>
var sequence=0;
function callbackFactura(responseText, parameters, readyState, status) {
    if (readyState!=4 || status!=200) return;
    if (sequence>parameters.sequence) {
        //console.log("CANCELLED OLD SEQUENCE "+parameters.sequence);
        return;
    }
    let cm = ebyid("countMessage");
    if (cm) ekfil(cm);
    if (responseText && responseText.length>0) {
        let jsonObj = JSON.parse(responseText);
        //console.log("INI function callbackCargaFactura. Parameters:",Object.keys(parameters),"JSON Object:",Object.keys(jsonObj));
        if (jsonObj.result && jsonObj.result==="refresh") {
            location.reload(true);
            // window.location.href = "/< ?= $ _project _name ?>";
        } else if (jsonObj.count) {
            if (cm) {
                let msg="";
                if (jsonObj.limit && jsonObj.limit>0 && jsonObj.limit<jsonObj.count) msg+=jsonObj.limit+" de ";
                msg+=jsonObj.count+" registro"+((+jsonObj.count)==1?"":"s");
                cm.appendChild(ecrea({eText:msg}));
            }
            let aidTable = ebyid("aidblock");
            let clsBtn = ebyid("adm_clearDataBtn");
            if (jsonObj.preview) {
                let aidBody=ebyid("aid_body");
                let aidTitle = "Vista Previa";
                if (jsonObj.currPage && jsonObj.lastPage) aidTitle+=" ["+jsonObj.currPage+"/"+jsonObj.lastPage+"]";
                else { jsonObj.currPage=1; jsonObj.lastPage=1; }
                if (!aidTable) {
                    let ft = ebyid("admfactura_foot");
                    let aidTable = ecrea({eName:"TABLE",id:"aidblock",border:"1",className:"previewTip",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",className:"centered",eText:"Fecha",style:"width:132px;"},{eName:"TH",className:"centered",eText:"Empresa",style:"width:87px;"},{eName:"TH",className:"centered",eText:"Prov",style:"width:54px;"},{eName:"TH",className:"centered",eText:"Folio",style:"width:112px;"},{eName:"TH",className:"centered",eText:"Total",style:"width:81px;"}]}]},{eName:"TFOOT",eChilds:[{eName:"TR",eChilds:[{eName:"TH",colSpan:"5",className:"centered",eChilds:[{eName:"DIV",className:"relative100",eChilds:[{eName:"SPAN",id:"aid_title",currPage:jsonObj.currPage,lastPage:jsonObj.lastPage,onclick:function(evt){clearForm();fillParams(parameters);},eText:aidTitle},{eName:"SPAN",className:"abs_w",eChilds:[{eName:"IMG",id:"firstPage",src:"imagenes/icons/firstPage.png",className:"btnFX invisible",onclick:function(evt){doTurnPage(parameters,1-1*(+ebyid('aid_title').currPage));}},{eName:"IMG",id:"prevPage",src:"imagenes/icons/prevPage.png",className:"btnFX invisible",onclick:function(evt){doTurnPage(parameters,-1);}}]},{eName:"SPAN",className:"abs_e",eChilds:[{eName:"IMG",id:"nextPage",src:"imagenes/icons/nextPage.png",className:"btnFX invisible",onclick:function(evt){doTurnPage(parameters,1);}},{eName:"IMG",id:"lastPage",src:"imagenes/icons/lastPage.png",className:"btnFX invisible",onclick:function(evt){doTurnPage(parameters,(+ebyid('aid_title').lastPage)-(+ebyid('aid_title').currPage));}},{eName:"IMG",src:"imagenes/icons/deleteIcon12.png",className:"btnFX",onclick:function(evt){ekil(ebyid("aidblock"));}}]}]}]}]}]},{eName:"TBODY",id:"aid_body"}]});
                    ft.appendChild(aidTable);
                    aidBody=ebyid("aid_body");
                    if (jsonObj.currPage>1) ebyid("prevPage").classList.remove("invisible");
                    if (jsonObj.currPage>2) ebyid("firstPage").classList.remove("invisible");
                    if (jsonObj.currPage<jsonObj.lastPage) ebyid("nextPage").classList.remove("invisible");
                    if ((jsonObj.currPage+1)<jsonObj.lastPage) ebyid("lastPage").classList.remove("invisible");
                } else {
                    ekfil(aidBody);
                    let at=ebyid("aid_title");
                    at.innerHTML=aidTitle;
                    at.currPage=jsonObj.currPage; //at.setAttribute("currPage",jsonObj.currPage);
                    at.lastPage=jsonObj.lastPage; //at.setAttribute("lastPage",jsonObj.lastPage);
                    if (jsonObj.currPage>1) ebyid("prevPage").classList.remove("invisible");
                    else ebyid("prevPage").classList.add("invisible");
                    if (jsonObj.currPage>2) ebyid("firstPage").classList.remove("invisible");
                    else ebyid("firstPage").classList.add("invisible");
                    if (jsonObj.currPage<jsonObj.lastPage) ebyid("nextPage").classList.remove("invisible");
                    else ebyid("nextPage").classList.add("invisible");
                    if ((jsonObj.currPage+1)<jsonObj.lastPage) ebyid("lastPage").classList.remove("invisible");
                    else ebyid("lastPage").classList.add("invisible");
                }
                //console.log("PREVIEW Length="+jsonObj.preview.length);
                let prevKeys=["fecha","empresa","proveedor","folio","total"];
                for(let i=0; i<jsonObj.preview.length; i++) {
                    let previewItem=jsonObj.preview[i];
                    let row = ecrea({eName:"TR",className:"selectable",onclick:function(evt) { clearForm();ebyid("adm_facturas_id").value=previewItem.id;clsBtn.classList.remove("hidden");doSubmit({aidRequest:1},true);}});
                    for(let c=0; c<prevKeys.length; c++) {
                        let cellParams = {eName:"TD",className:"centered",eText:previewItem[prevKeys[c]]};
                        row.appendChild(ecrea(cellParams));
                    }
                    aidBody.appendChild(row);
                }
                if (aidTable && aidTable.classList) aidTable.classList.remove("hidden");
            }
            if (jsonObj.facturas) {
                for(let objKey in jsonObj.facturas) {
                    let objItem = ebyid("adm_facturas_"+objKey);
                    if (objItem) {
                        let newVal=jsonObj.facturas[objKey];
                        switch(objKey) {
                            case "tipoComprobante":
                                if(newVal.length>1) newVal = newVal.slice(0,1);
                                let esPago=(newVal==='p');
                                setClass('admfactura_contrarreciboh','hidden',esPago);
                                setClass('admfactura_contrarrecibo','hidden',esPago);
                                break;
                        }
                        objItem.value=newVal;
                        objItem.originalValue=newVal;
                    }
                }
                clsBtn.classList.remove("hidden");
                let items = lbycn("admreg");
                for(let i=0;i<items.length;i++) {
                    let itm = items[i];
                    let itmcl=itm.classList;
                    if (itmcl.contains("invoiceFixed")||!(itmcl.contains("facturas")||itmcl.contains("tableKey"))) {
                        itmcl.add("bgbrown");
                        itm.readOnly=true;
                    }
                }
            }
            if (jsonObj.proveedores) {
                for(let objKey in jsonObj.proveedores) {
                    let objItem = ebyid("adm_proveedores_"+objKey);
                    if (objItem) {
                        let newVal=jsonObj.proveedores[objKey];
                        objItem.value=newVal;
                        objItem.originalValue=newVal;
                    }
                }
                let alt = ebyid("adm_facturas_codigoProveedor");
                if (alt.value.length==0) {
                    alt.value=jsonObj.proveedores.codigo;
                    alt.originalValue=alt.value;
                }
                clsBtn.classList.remove("hidden");
            }
            if (jsonObj.grupo) {
                for(let objKey in jsonObj.grupo) {
                    let objItem = ebyid("adm_grupo_"+objKey);
                    if (objItem) {
                        let newVal=jsonObj.grupo[objKey];
                        objItem.value=newVal;
                        objItem.originalValue=newVal;
                    }
                }
                let alt = ebyid("adm_facturas_rfcGrupo");
                if (alt.value.length==0) {
                    alt.value=jsonObj.grupo.rfc;
                    alt.originalValue=alt.value;
                }
                clsBtn.classList.remove("hidden");
            }
            let ctr=ebyid("admfactura_scroll");
            if (ctr) ekfil(ctr);
            if (jsonObj.conceptos) {
                let conRows = [];
                let sumIDes = 0;
                let sumITra = 0;
                let sumIRet = 0;
                let sumCImp = 0;
                for (let c=0; c<jsonObj.conceptos.length;c++) {
                    if (jsonObj.conceptos[c].importeDescuento>0) sumIDes += +jsonObj.conceptos[c].importeDescuento;
                    if (jsonObj.conceptos[c].impuestoTraslado>0) sumITra += +jsonObj.conceptos[c].impuestoTraslado;
                    if (jsonObj.conceptos[c].impuestoRetenido>0) sumIRet += +jsonObj.conceptos[c].impuestoRetenido;
                    sumCImp += +jsonObj.conceptos[c].importe;
                    let conPUni=(+jsonObj.conceptos[c].precioUnitario).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    let conImpo=(+jsonObj.conceptos[c].importe).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    let conImpoCN="currency";
                    conRows.push({eName:"TR",eChilds:[
                        {eName:"TD",className:"screen",eText:jsonObj.conceptos[c].claveProdServ},
                        {eName:"TD",className:"screen",eText:jsonObj.conceptos[c].cantidad},
                        {eName:"TD",className:"screen",eText:jsonObj.conceptos[c].claveUnidad},
                        {eName:"TD",className:"screen",eText:jsonObj.conceptos[c].descripcion},
                        {eName:"TD",className:"screen centered nowrap",eChilds:[{eText:"$"},
                            {eName:"SPAN",className:"currency",eText:conPUni}]},
                        {eName:"TD",className:"screen centered nowrap",eChilds:[{eText:"$"},
                            {eName:"SPAN",className:conImpoCN,eText:conImpo}]}
                    ]});
                }
                let sumITot=sumCImp-sumIDes+sumITra-sumIRet;
                let facSubt = (+jsonObj.facturas.subtotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                sumCImp = sumCImp.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                let facSubtCN = "screen centered nowrap "+(facSubt===sumCImp?"bggreen":"bgred"); // TODO: Repetir esto para descuento, impuestos y total
                let currencySubSpan = {eName:"SPAN",className:"currency",eText:facSubt};
                if (facSubt!==sumCImp) currencySubSpan.title=sumCImp;
                let conFootRows = [{eName:"TR",eChilds:[
                    {eName:"TH",className:"righted padrgt2",colSpan:"5",eText:"Subtotal"},
                    {eName:"TD",className:facSubtCN,eChilds:[{eText:"$"},currencySubSpan]}
                ]}];
                if (jsonObj.facturas.importeDescuento>0 || sumIDes>0) {
                    let facIDes = empty(jsonObj.facturas.importeDescuento)?"0.00":(+jsonObj.facturas.importeDescuento).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    sumIDes = sumIDes.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    let facIDesCN = "screen centered nowrap "+(facIDes===sumIDes?"bggreen":"bgred");
                    let currencyDesSpan = {eName:"SPAN",className:"currency",eText:"-"+facIDes};
                    if (facIDes!==sumIDes) currencyDesSpan.title=sumIDes;
                    conFootRows.push({eName:"TR",eChilds:[
                        {eName:"TH",className:"righted padrgt2",colSpan:"5",eText:"Descuento"},
                        {eName:"TD",className:facIDesCN,eChilds:[{eText:"$"},currencyDesSpan]}
                    ]});
                }
                if (jsonObj.facturas.impuestoTraslado>0 || sumITra>0) {
                    let facITra = empty(jsonObj.facturas.impuestoTraslado)?"0.00":(+jsonObj.facturas.impuestoTraslado).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    sumITra = sumITra.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    let facITraCN = "screen centered nowrap "+(facITra===sumITra?"bggreen":"bgred");
                    let currencyTraSpan = {eName:"SPAN",className:"currency",eText:facITra};
                    if (facITra!==sumITra) currencyTraSpan.title=sumITra;
                    conFootRows.push({eName:"TR",eChilds:[
                        {eName:"TH",className:"righted padrgt2",colSpan:"5",eText:"Impuesto Trasladado"},
                        {eName:"TD",className:facITraCN,eChilds:[{eText:"$"},currencyTraSpan]}
                    ]});
                }
                if (jsonObj.facturas.impuestoRetenido>0 || sumIRet>0) {
                    let facIRet = empty(jsonObj.facturas.impuestoRetenido)?"0.00":(+jsonObj.facturas.impuestoRetenido).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    sumIRet = sumIRet.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    let facIRetCN = "screen centered nowrap "+(facIRet===sumIRet?"bggreen":"bgred");
                    let currencyRetSpan = {eName:"SPAN",className:"currency",eText:"-"+facIRet};
                    if (facIRet!==sumIRet) currencyRetSpan.title=sumITot;
                    conFootRows.push({eName:"TR",eChilds:[
                        {eName:"TH",className:"righted padrgt2",colSpan:"5",eText:"Impuesto Retenido"},
                        {eName:"TD",className:facIRetCN,eChilds:[{eText:"$"},currencyRetSpan]}
                    ]});
                }
                let facITot = (+jsonObj.facturas.total).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                sumITot = sumITot.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                let facITotCN = "screen centered nowrap "+(facITot===sumITot?"bggreen":"bgred");
                let currencyTotalSpan = {eName:"SPAN",className:"currency",eText:facITot}
                if (facITot!==sumITot) currencyTotalSpan.title=sumITot;
                conFootRows.push({eName:"TR",className:"btop1d",eChilds:[
                    {eName:"TH",className:"righted padrgt2",colSpan:"5",eText:"Total"},
                    {eName:"TD",className:facITotCN,eChilds:[{eText:"$"},currencyTotalSpan]}
                ]});
                let cncTab = ecrea({
                    eName:"TABLE",
                    className:"width100",
                    eChilds:[
                        {eName:"THEAD",eChilds:[
                            {eName:"TR",eChilds:[{eName:"TH",colSpan:"6",className:"titlearea",eText:"CONCEPTOS"}]},
                            {eName:"TR",eChilds:[
                                {eName:"TH",className:"title",eText:"PrdSrv"},
                                {eName:"TH",className:"title",eText:"Cant"},
                                {eName:"TH",className:"title",eText:"Unid"},
                                {eName:"TH",className:"title",eText:"Descripción"},
                                {eName:"TH",className:"title",eText:"P.Uni"},
                                {eName:"TH",className:"title",eText:"Importe"}
                            ]}
                        ]},
                        {eName:"TBODY",className:"bbtm2d",eChilds:conRows},
                        {eName:"TFOOT",eChilds:conFootRows}
                    ]
                });
                ctr.appendChild(cncTab);
                ctr.appendChild(ecrea({eName:"IMG",onload:function(evt) {
                    let lcur=lbycn('currency');
                    let mxw=0;
                    for(let l=0;l<lcur.length;l++) if(lcur[l].offsetWidth>mxw) mxw=lcur[l].offsetWidth;
                    for(let l=0;l<lcur.length;l++) lcur[l].style.width=mxw+'px';
                    //console.log('fixed currency widths');
                    ekil(evt.target);
                },src:"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"}));
            }
            if (jsonObj.contrarrecibos) {
                for(let objKey in jsonObj.contrarrecibos) {
                    let objItem = ebyid("adm_contrarrecibos_"+objKey);
                    if (objItem) {
                        let newVal=jsonObj.contrarrecibos[objKey];
                        objItem.value=newVal;
                        objItem.originalValue=newVal;
                    }
                }
                clsBtn.classList.remove("hidden");
            }
            let ext=ebyid("admfactura_extra");
            if (ext) ekfil(ext);
            if (jsonObj.proceso) {
                let prcRows = [];
                for (let p=0;p<jsonObj.proceso.length;p++) {
                    let usr=jsonObj.proceso[p].usuario;
                    let usrCel={eName:"TD",className:"screen",eText:jsonObj.proceso[p].usuario};
                    if (jsonObj.usuarios[usr]) usrCel.title=jsonObj.usuarios[usr].persona;
                    prcRows.push({eName:"TR",eChilds:[
                        {eName:"TD",className:"screen",eText:jsonObj.proceso[p].id},
                        {eName:"TD",className:"screen",title:jsonObj.proceso[p].hora,eText:jsonObj.proceso[p].fecha},
                        {eName:"TD",className:"screen",eText:jsonObj.proceso[p].modulo},
                        {eName:"TD",className:"screen",eText:jsonObj.proceso[p].status},
                        {eName:"TD",className:"screen",eText:jsonObj.proceso[p].detalle},
                        {eName:"TD",className:"screen",eText:jsonObj.proceso[p].region},
                        usrCel//,{eName:"TD",eText:jsonObj.proceso[p].zona}
                    ]});
                }
                let prcTab = ecrea({
                    eName:"TABLE",
                    className:"width100",
                    eChilds:[
                        {eName:"THEAD",eChilds:[
                            {eName:"TR",eChilds:[{eName:"TH",colSpan:"7",className:"titlearea",eText:"PROCESO"}]}, //8
                            {eName:"TR",eChilds:[
                                {eName:"TH",className:"title",eText:"ID"},
                                {eName:"TH",className:"title",eText:"Fecha"},
                                {eName:"TH",className:"title",eText:"Modulo"},
                                {eName:"TH",className:"title",eText:"Status"},
                                {eName:"TH",className:"title",eText:"Detalle"},
                                {eName:"TH",className:"title",eText:"Region"},
                                {eName:"TH",className:"title",eText:"Usuario"}//,{eName:"TH",className:"title",eText:"Zona"}
                            ]}
                        ]},{eName:"TBODY",eChilds:prcRows}
                    ]
                });
                ext.appendChild(prcTab);
            }
            if (jsonObj.pagadas) {
                //
            }
            let fpath=ebyid("adm_facturas_ubicacion");
            let fxml=ebyid("adm_facturas_nombreInterno");
            let xmlbtn = ebyid("xmlbtn");
            let xmlfix = ebyid("xmlfix");
            if (fxml && fxml.value.length>0) {
                // Crear class xmlfile, agregar class. agregar onclick para abrir fpath.value+fxml.value+".xml"
                clrem(xmlbtn,"hidden");
                clrem(xmlfix,"hidden");
            } else {
                cladd(xmlbtn,"hidden");
                cladd(xmlfix,"hidden");
            }
            let fpdf=ebyid("adm_facturas_nombreInternoPDF");
            let pdfbtn = ebyid("pdfbtn");
            if (fpdf && fpdf.value.length>0) {
                clrem(pdfbtn,"hidden");
            } else {
                cladd(pdfbtn,"hidden");
            }
        } else if (cm) cm.appendChild(ecrea({eText:"No se encontraron registros."}));
        else console.log("SIN REGISTROS Y SIN AREA DE MENSAJES!!");
    } else if (cm) cm.appendChild(ecrea({eText:"Sin respuesta del servidor."}));
    else console.log("SIN RESPUESTA Y SIN AREA DE MENSAJES!!");
}
function fixXML(evt) {
    const ubicacion=ebyid("adm_facturas_ubicacion").value;
    const archivo=ebyid("adm_facturas_nombreInterno").value+".xml";
    //console.log("INI function fixXML "+ubicacion+archivo);
    postService('consultas/Facturas.php',{actualiza:'xmlSyntax',filepath:ubicacion+archivo},function(txt,pars,ste,sts) {
        //console.log("Fix Corrupt XML "+ste+"/"+sts+": "+txt);
        if (ste<4||sts<200) return;
        if (ste==4&&sts==200) {
            try {
                const res=JSON.parse(txt);
                if (res.result==="success") {
                    //console.log("FILE SYNTAXIS FIXED!");
                } else console.log("NO SUCCESS: "+res.message);
            } catch (ex) { console.log("ERROR: "+ex); }
        } else console.log("FAILURE: "+txt);
    });
}
function doTurnPage(params,modif) {
    let at=ebyid("aid_title");
    let currPage=+at.currPage; //getAttribute("currPage");
    let lastPage=+at.lastPage; //getAttribute("lastPage");
    let modPg=currPage+modif;
    if(modPg>=1 && modPg<=lastPage) {
        sequence++;
        let nwPars={pageno:modPg,sequence:sequence};
        at.currPage=modPg;
        if (params) for(let atNm in params) if (atNm!=="pageno"&&atNm!=="sequence") nwPars[atNm]=params[atNm];
        setWaitBar('aid_title',52);
        doSubmit(nwPars);
    } else console.log("Function doTurnPage "+currPage+" to "+modPg+" Failed. LastPage is "+lastPage);
}
function buildParameters(extraParameters=null) {
    sequence++;
    let parameters={adminfactura:"json",sequence:sequence};
    if (extraParameters)
        for(let attrName in extraParameters)
            if (attrName!=="sequence")
                parameters[attrName]=extraParameters[attrName];
    let tables=["facturas","proveedores","grupo","contrarrecibos"];
    for(let t=0; t<tables.length; t++) {
        let elements=document.getElementsByClassName(tables[t]);
        for (let e=0; e<elements.length; e++) {
            let elem=elements[e];
            let elemname=elem.id;
            let prefix="adm_"+tables[t]+"_";
            let elemPrefix=elemname.slice(0,prefix.length);
            let regname=false;
            if (elemPrefix===prefix) regname=elemname.slice(prefix.length);
            if (elemPrefix!==prefix && elem.hasAttribute("id2")) {
                let elemId2=elem.getAttribute("id2");
                elemPrefix=elemId2.slice(0,prefix.length);
                if (elemPrefix===prefix) regname=elemId2.slice(prefix.length);
            }
            let value=elem.value;
            if (regname && value) {
                if (value.length>0) parameters[tables[t]+"["+regname+"]"]=value;
                if (!elem.classList.contains("tableKey")) {
                    let originalValue=elem.originalValue;
                    if (originalValue && originalValue!==value) {
                        if (!elem.classList.contains("invoiceFixed"))
                            parameters[tables[t]+"_edit["+regname+"]"]=value;
                        parameters[tables[t]+"["+regname+"]"]=originalValue;
                    }
                }
            }
        }
    }
    return parameters;
}
var timeOutAdmSubmitRequest=0;
function getRequestOption() {
    //console.log("INI function getRequestOption");
    let items=lbycn("admreg");
    isKeyChange=false; isAnyChange=false; isRequest=false;
    for(let i=0;i<items.length;i++) {
        if(items[i].originalValue && items[i].originalValue!==items[i].value) {
            if (items[i].classList.contains("tableKey")) isKeyChange=true;
            else isAnyChange=true;
        }
        if(items[i].value.length!=0) {
//            if (items[i].classList.contains("tableKey")) isKeyChange=true;
//            else 
            isRequest=true;
        }
    }
    if (isKeyChange) {
        if (isAnyChange) return "keywiping";
        return "keysetting";
    }
    if (isAnyChange) return "editing";
    if (isRequest) return "requesting";
    return false;
}
function countEditedFields() {
    let items=lbycn("admreg");
    let edited=0;
    for(let i=0;i<items.length;i++) {
        let itm=items[i];
        if (!itm.readOnly&&!itm.classList.contains("tableKey")&&itm.originalValue&&itm.value!==itm.originalValue) edited++;
    }
    return edited;
}
function submitRequest(evt) {
    if (evt) {
        let tgt=evt.target;
        let tgtVal=tgt.value;
        if (tgt.readOnly) {
            if (tgt.originalValue) tgt.value = tgt.originalValue;
            else tgt.value = tgt.defaultValue;
            return;
        }
        //console.log("INI function submitRequest target:",tgt);
        if (tgtVal.length>0) ebyid("adm_clearDataBtn").classList.remove("hidden");
        let tgtCL=tgt.classList;
        // TODO: Redefinir opcion: Solo considerar item actual (evt.target)
        if (tgtCL.contains("tableKey")) { // Si contiene class tableKey:
            // Buscar si existen otros campos editados (sin clase tableKey), donde fld.value!==fld.originalValue
            // Si hay campos editados:
            if(countEditedFields()>0) {
            //     asignar newValue=tgt.value
            //     reasignar tgt.value=tgt.originalValue
                if (tgt.originalValue) tgt.value=tgt.originalValue;
                else tgt.value=tgt.defaultValue;
            //     solicitar confirmacion para "Ignorar cambios y repopular display"
            //     si se confirma realizar { clearForm(false,true);tgt.value=newValue;doSubmit(); }
                overlayConfirmation("Desea ignorar los cambios?","Confirmación Requerida",function() {
                    clearForm(false,true);
                    tgt.value=tgtVal;
                    doSubmit();
                });
            // Si no: realizar { clearForm(false,true,[tgt.id]);doSubmit(); }
            } else {
                clearForm(false,true,[tgt.id]);
                doSubmit();
            }
        // Si no Si existe tgt.originalValue: // si el valor se obtuvo al cargar datos de una factura
        } else if (tgt.originalValue) {
        //     Si tgt.value!==tgt.originalValue:
            if (tgtVal!==tgt.originalValue) {
        //         Mostrar boton Guardar,cambiar bgcolor,return // No hacer doSubmit, hasta q presionen boton Guardar
            }
        // si no (si no existe tgt.originalValue):
        //     clearTimeout(timeOutAdmSubmitRequest);
        //     timeOutAdmSubmitRequest = setTimeout(doSubmit,500);
        //
        }
        // Si se presiona el boton Guardar:
        //     Solicitar confirmacion para "Guardar cambios"
        //     Si se confirma realizar
        let reqOpt = getRequestOption(); 
        //console.log("Request Option = "+reqOpt+"; target Id="+tgt.id+", Val='"+tgtVal+"'");
        switch(reqOpt) {
            case "keywiping":
                clearTimeout(timeOutAdmSubmitRequest);
                timeOutAdmSubmitRequest = setTimeout(function() { overlayConfirmation("Desea ignorar los cambios?","Confirmación  Requerida", function(){clearForm(false,true,[tgt.id]);doSubmit();}); },700);
                break;
            case "keysetting":
                clearForm(false,true,[tgt.id]);
                doSubmit();
                break;
            case "editing":
                clearTimeout(timeOutAdmSubmitRequest);
                timeOutAdmSubmitRequest = setTimeout(function() { overlayConfirmation("Desea guardar los cambios?", "Confirmación Requerida", function() {ekil(ebyid('aidblock'));doSubmit();}); },700);
                break;
            case "requesting":
                if (tgt.classList.contains("tableKey")) clearForm(false,true,[tgt.id]);
                else ekil(ebyid('aidblock'));
                doSubmit();
                break;
        }


        //if(tgt.id==='adm_facturas_tipoComprobante') { let isP=(tgtVal==='p'); setClass('admfactura_contrarreciboh','hidden',isP); setClass('admfactura_contrarrecibo','hidden',isP); }
    }
}
function doSubmit(params,hasExtraParams) {
    if (!params) {
        params=buildParameters();
    } else if (hasExtraParams) {
        params=buildParameters(params);
    }
    //console.log("INI function doSubmit. params:",Object.keys(params),", hasExtraParams:"+(hasExtraParams?"YES":"NO"));
    setWaitBar('countMessage');
    postService('consultas/Facturas.php', params, callbackFactura);
}
function fillParams(parameters) {
    let items = lbycn("admreg");
    for(let i=0;i<items.length;i++) {
        let item=items[i];
        let tags=item.id.split("_");
        let pnm=tags[1]+"["+tags[2]+"]";
        /*
        let edit_pnm=tags[1]+"_edit["+tags[2]+"]";
        if (parameters[edit_pnm]) {
            item.value=parameters[edit_pnm];
            if (parameters[pnm]) item.originalValue=parameters[pnm];
        } else 
        */
        if (parameters[pnm]) item.value=parameters[pnm];
        delete item.originalValue;
        if (item.id==='adm_facturas_tipoComprobante') {
            let esPago=(item.value==='p');
            setClass('admfactura_contrarreciboh','hidden',esPago);
            setClass('admfactura_contrarrecibo','hidden',esPago);
        }
    }
    ebyid("adm_clearDataBtn").classList.remove("hidden");
}
function clearForm(cancelRequests,clearAidBlock,exceptionIds) {
    if (cancelRequests) {
        clearTimeout(timeOutAdmSubmitRequest);
        sequence++;
    }
    if (clearAidBlock) {
        ekil(ebyid('aidblock'));
    }
    let items = lbycn("admreg");
    for(let i=0;i<items.length;i++) {
        if (exceptionIds&&exceptionIds.includes(items[i].id)) continue;
        items[i].value="";
        items[i].classList.remove("bgbrown");
        delete items[i].originalValue;
        items[i].readOnly=false;
    }
    let esPago = (ebyid("adm_facturas_tipoComprobante").value==="p");
    setClass('admfactura_contrarreciboh','hidden',esPago);
    setClass('admfactura_contrarrecibo','hidden',esPago);
    ekfil(ebyid("countMessage"));
    ebyid("adm_clearDataBtn").classList.add("hidden");
    ekfil(ebyid("admfactura_scroll"));
    ekfil(ebyid("admfactura_extra"));
    hideStatusComponent();
}
function setWaitBar(elemId,wid,hei) {
    if (!wid) wid=76;
    if (!hei) hei=wid/4;
    let elem=ebyid(elemId);
    if (elem) {
        ekfil(elem);
        elem.appendChild(ecrea({eName:"IMG",src:"imagenes/icons/rollwait.gif",width:wid,height:hei}));
    }
}
function showStatusComponent(evt) {
    //console.log("INI function showStatusComponent");
    hideStatusComponent();
    let sc = ecrea({eName:"DIV",id:"admfacturas_statusComponent",className:"abs_ne zIdx100",eChilds:[{eName:"UL",classList:"mbmenu",eChilds:[{eName:"LI",eText:"\u00A0",onclick:clearStatus},{eName:"LI",eText:"Temporal",onclick:setStatusTemporal},{eName:"LI",eText:"Pendiente",onclick:setStatusPendiente},{eName:"LI",eText:"Aceptado",onclick:setStatusAceptado},{eName:"LI",eText:"Contra-Recibo",onclick:setStatusContraRecibo},{eName:"LI",eText:"Exportado",onclick:setStatusExportado},{eName:"LI",eText:"Respaldado",onclick:setStatusRespaldado},{eName:"LI",eText:"Pagado",onclick:setStatusPagado},{eName:"LI",eText:"RecPago",onclick:setStatusRecPago},{eName:"LI",eText:"Rechazado",onclick:setStatusRechazado}]}]});
    evt.target.parentNode.appendChild(sc);
}
function clearStatus(event) { setStatusComponent(""); ekil(ebyid("aidblock")); }
function setStatusTemporal(event) { setStatusComponent("NULL");  submitRequest(event); }
function setStatusPendiente(event) { setStatusComponent("0");  submitRequest(event); }
function setStatusAceptado(event) { setStatusComponent("1"); submitRequest(event); }
function setStatusContraRecibo(event) { setStatusComponent("2"); submitRequest(event); }
function setStatusExportado(event) { setStatusComponent("4"); submitRequest(event); }
function setStatusRespaldado(event) { setStatusComponent("8"); submitRequest(event); }
function setStatusPagado(event) { setStatusComponent("32"); submitRequest(event); }
function setStatusRecPago(event) { setStatusComponent("64"); submitRequest(event); }
function setStatusRechazado(event) { setStatusComponent("128"); submitRequest(event); }
function setStatusComponent(statusNVal) {
    switch(statusNVal) {
        case "":
            ebyid("adm_facturas_status").value="";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="";
            break;
        case "NULL": 
            ebyid("adm_facturas_status").value="Temporal";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="";
            break;
        case "0":
            ebyid("adm_facturas_status").value="Pendiente";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="0";
            break;
        case "1":
            ebyid("adm_facturas_status").value="Aceptado";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="1";
            break;
        case "2":
            ebyid("adm_facturas_status").value="Contra-Rec";
            ebyid("adm_facturas_status").title="Contra-Recibo";
            ebyid("adm_facturas_statusn").value="2";
            break;
        case "4":
            ebyid("adm_facturas_status").value="Exportado";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="4";
            break;
        case "8":
            ebyid("adm_facturas_status").value="Respaldado";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="8";
            break;
        case "32":
            ebyid("adm_facturas_status").value="Pagado";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="32";
            break;
        case "64":
            ebyid("adm_facturas_status").value="RecPago";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="64";
            break;
        case "128":
            ebyid("adm_facturas_status").value="Rechazado";
            ebyid("adm_facturas_status").removeAttribute("title");
            ebyid("adm_facturas_statusn").value="128";
            break;
    }
    hideStatusComponent();
}
function hideStatusComponent() {
    //console.log("INI function hideStatusComponent");
    ekil(ebyid("admfacturas_statusComponent"));
    ekil(ebyid("admfacturas_statusOpComponent"));
}
const statusOpList=["\u2208","\u2A01","\u229D"];
const statusOpCmds=[setStatusOpRelevant,setStatusOpIncluded,setStatusOpExcluded];
function showStatusOpComponent(evt) {
    //console.log("INI function showStatusOpComponent");
    hideStatusComponent();
    const lis=[];
    for(let i=0;i<statusOpList.length;i++) lis[i]={eName:"LI",eText:statusOpList[i],onclick:statusOpCmds[i]};
    const soc = ecrea({eName:"DIV",id:"admfacturas_statusOpComponent",className:"abs top5 lft4 zIdx100",eChilds:[{eName:"UL",classList:"mbmenu",eChilds:lis}]});
    evt.target.parentNode.appendChild(soc);
    //console.log("END function showStatusOpComponent")
}
function setStatusOpRelevant(event) {
    setStatusOp(event, 0);
}
function setStatusOpIncluded(event) {
    setStatusOp(event, 1);
}
function setStatusOpExcluded(event) {
    setStatusOp(event, 2);
}
function setStatusOp(event,n) {
    //console.log("INI function setStatusOp");
    const sop=ebyid("adm_facturas_statusop");
    const nop=ebyid("adm_facturas_statusnop")
    ekfil(sop);
    sop.appendChild(ecrea({eText:statusOpList[n]}));
    ekfil(nop);
    nop.appendChild(ecrea({eText:statusOpList[n]}));
    hideStatusComponent();
    submitRequest(event);
}
window.onclick=function() {
    if (!document.activeElement.id || "adm_facturas_status"!==document.activeElement.id.slice(0,19)) {
        //console.log("ACTIVE = ",document.activeElement);
        hideStatusComponent();
        //console.log("ACTIVE END");
    }
}
<?php
clog1seq(-1);
clog2end("scripts.adminfactura");
