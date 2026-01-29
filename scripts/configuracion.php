<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.configuracion");
clog1seq(1);
require_once "scripts/ecreaShortcuts.php";
$dia = date('j');
$mes = date('n');
$anio = date('Y');
$maxdia = date('t');
$mesPasado = $mes>1?$mes-1:12;
$mesProximo = $mes<12?$mes+1:1;
$mesPasado = str_pad($mesPasado,2,"0",STR_PAD_LEFT);
$mesProximo = str_pad($mesProximo,2,"0",STR_PAD_LEFT);
$fmtDay0 = "01/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
$fmtDay = str_pad($dia,2,"0",STR_PAD_LEFT)."/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
?>
console.log("CONFIGURACION SCRIPT READY!!!");
function showAutoUploadRecords(evt) {
    console.log("INI function showAutoUploadRecords");
    overlayMessage(tabFnc(false,[trFnc([thFnc("Fecha Ini:"),tdFnc([objFnc("DIV",{className:"calendar_month_wrapper",onclick:dateIniSet},[{eName:"SPAN",eText:"<TMP>"}])])])]),"Alta Automática de Facturas"); // {eName:"TABLE",eChilds:[{eName:"TBODY",eChilds:[{eName:"TR",eChilds:[{eName:"TH",eText:"Fecha Ini:"},{eName:"TD",eChilds:[{eName:"DIV",className:"calendar_month_wrapper",onclick:dateIniSet,eChilds:[{eName:"IMG",src:"imagenes/icons/calmes.png", id:"calendar_month_prev", title:"Mes Anterior", className:"calendar_month_<?= $mesPasado ?>"}]},{eName:"INPUT", type:"text", id:"fechaInicio", name:"fechaInicio"}]}]}]}]} // {eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",eText:""}]}]},
    const parameters = {action:"showAutoUpload", inclusiveSeparator:"***"};
    progressService("consultas/Facturas.php",parameters,showAutoUploadSuccess,showAutoUploadFailure);
}
function showAutoUploadSuccess(jobj,extra) {
    console.log("INI ShowAutoUploadSuccess. JOBJ: ",jobj);
    console.log("Extra: ",extra);
}
function showAutoUploadFailure(errmsg,respText,extra) {
    console.log("INI showAutoUploadFailure. MSG: "+errmsg);
    console.log("RESPTEXT: "+respText);
    console.log("Extra: ",extra);
}
var lastMinuteReloaded=false;
function reloadMailHourCount() {
    const dt = new Date();
    const hr = dt.getHours();
    const min = dt.getMinutes()+(60*hr);
    if (lastMinuteReloaded!==false && lastMinuteReloaded==min) {
        console.log("INI reloadMailHourCount "+hr+". No need to repeat same minute "+min);
        return;
    }
    console.log("INI reloadMailHourCount "+hr);
    lastMinuteReloaded=min;
    readyService("consultas/InfoLocal.php",{"accion":"reloadHourMailCount"}, (j, x, n)=> {
        if (j) console.log("Result "+JSON.stringify(j, jsonCircularReplacer()));
        if (x) console.log("EXTRA: "+JSON.stringify(x, jsonCircularReplacer()));
        switch(j.result) {
            case "success":
                title="EXITO";
                const mhc=ebyid("mailHourCount");
                ekfil(mhc);
                const domRows=[]; let block;
                for(domKey in j.data) {
                    if (domKey=="key"||domKey=="prueba") continue;
                    block=[];
                    for(hourKey in j.data[domKey]) {
                        padHour=(hourKey<10?"0":"")+hourKey+":00";
                        block.push({eName:"P",className:"mar2",eText:" "+padHour+" #"+j.data[domKey][hourKey]});
                    }
                    domRows.push({eName:"TR",eChilds:[{eName:"TD",className:"top",eChilds:[{eName:"P",className:"mar2",eText:domKey.toUpperCase()}]},{eName:"TD",className:"top",eChilds:block}]});
                }
                mhc.appendChild(ecrea({eName:"TABLE",className:"separate1 layout pad2c marginL15",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",className:"bglightgray",eText:"DOMINIO"},{eName:"TH",className:"bglightgray",eText:"POR HORA"}]}]},{eName:"TBODY",eChilds:domRows}]}))
                //mhc.appendChild(ecrea({eText:JSON.stringify(j.data)}));
                break;
            case "empty":
                title="SIN CAMBIOS";
                message="No se actualizo ningún dato";
                break;
            case "error":
                title="ERROR";
                message="Error de Datos "+j.errno+": "+j.error;
                break;
            default:
                title=j.result.toUpperCase();
                message="Is "+j.result.toLowerCase();
        }
        if (j.result!=="success" && j.result!=="empty") overlayMessage(getParagraphObject(message), title);
    }, (e, t, x)=> {
        showOnError(e, t, x);
    });
}
function clogcfg(msg,jobj,extra) {
    console.log(msg+(jobj?"\nJOBJ: "+JSON.stringify(jobj,jsonCircularReplacer()):"")+(extra?"\nEXTRA: "+JSON.stringify(extra,jsonCircularReplacer()):""));
    return false;
}
function setDRAMessage(msg,data,doTable=false) {
    console.log("INI setDRAMessage MSG: "+msg+".\nDATA:",data,"\nDOTABLE:",doTable);
    cn="lefted marginV7";
    if (!data) data={type:0};
    else {
        if (data.id && ebyid(data.id)) {
            console.log("ERR: Already exists.");
            return false;
        }
        if (!data.type) data.type=0;
    }
    switch(data.type) {
        case 1: cn+=" bggreenlt greener"; break;
        case 2: cn+=" lightBlurred"; break;
        case 3: cn+=" bgred0i"; break;
        case 4: cn+=" bgred0i boldValue"; break;
    }
    const dv=document.createElement("div");
    dv.innerHTML=msg;
    msg=dv.textContent||dv.innerText||"";
    const dra=ebyid("dialog_resultarea");
    if (!clhas(dra,"hScroll")) cladd(dra,"hScroll");
    if (doTable) {
        let tableElem=false;
        if (doTable.id) tableElem=ebyid(doTable.id);
        if (!tableElem) {
            console.log("... creating table ...");
            const tableObj={eName:"TABLE"};
            if (doTable.id) tableObj.id=doTable.id;
            if (doTable.headNames) {
                const headLst=[];
                for(const nm of doTable.headNames) headLst.push({eName:"TH",eText:nm.toUpperCase()});
                tableObj.eChilds=[{eName:"THEAD",eChilds:[{eName:"TR", eChilds:headLst}]}];
            }
            dra.appendChild(ecrea(tableObj));
            tableElem=ebyid(doTable.id);
        }
        let tbodyElem=tableElem.querySelector("tbody");
        if (!tbodyElem) {
            console.log("... creating tbody ...");
            tbodyElem=ecrea({eName:"TBODY"});
            tableElem.appendChild(tbodyElem);
        }
        if (data.colSpan) {
            const rowCels=[{eName:"TD",colSpan:data.colSpan,eText:msg}];
            const rowObj={eName:"TR",className:cn,eChilds:rowCels};
            if (data.id) rowObj.id=data.id;
            tbodyElem.appendChild(ecrea(rowObj));
        } else if (doTable.headNames) {
            const rowCels=[];
            for (const nm of doTable.headNames) {
                if (data[nm]) rowCels.push({eName:"TD",eText:data[nm]});
                else if (nm.toUpperCase()==="MSG"||nm.toUpperCase()==="MESSAGE"||nm.toUpperCase()==="MENSAJE"||nm.toUpperCase()==="TEXT"||nm.toUpperCase()==="TEXTO"||nm.toUpperCase()==="DESC"||nm.toUpperCase()==="DESCRIPCION") rowCels.push({eName:"TD",eText:msg});
                else rowCels.push({eName:"TD",eText:"."});
            }
            const rowObj={eName:"TR",className:cn,eChilds:rowCels};
            if (data.id) rowObj.id=data.id;
            tbodyElem.appendChild(ecrea(rowObj));
        }
    } else {
        const elObj={eName:"P",eText:msg,className:cn};
        if (data.id) elObj.id=data.id;
        dra.appendChild(ecrea(elObj));
    }
}
function saveCheck(key, value, successFunc, failureFunc) {
    console.log("INI saveCheck "+key+", "+value);
    postService("consultas/InfoLocal.php",{accion:"definir",nombre:key,valor:value},function(text,pars,state,status) {
        if (state!=4||status!=200) { console.log(state+"/"+status); return; }
        if (text.length==0) { console.log("EMPTY",pars); if (failureFunc) failureFunc("Sin respuesta", 1); return; }
        try {
            let response=JSON.parse(text);
            if (response.result && response.result==="success") {
                console.log("SUCCESS!");
                if (successFunc) successFunc(response);
            } else {
                console.log("FAILURE: "+text);
                if (failureFunc) {
                    if (response.message) failureFunc(response.message, 2, text);
                    else failureFunc("Respuesta no exitosa", 3, text);
                }
            }
        } catch (ex) {
            console.log("EXCEPTION",ex,text);
            if (failureFunc) failureFunc("Error en respuesta", 4, text, ex);
        }
    }, function(errmsg, parameters, evt) {
        if (errmsg==="Request Timed Out") {
            console.log(errmsg, parameters, evt);
        } else {
            console.log(errmsg, parameters, evt);
        }
        if (failureFunc) failureFunc(errmsg, 5, false, parameters);
    });
}
function addList(jobj,sfxname,liname) {
    const ule=ebyid("ul"+sfxname);
    const lim={eName:"IMG", src:"imagenes/icons/deleteIcon12.png", className:"pointer abs rgt4 top4", onclick:function(ev){delUser(ev);}};
    const lio={eName:"LI", className:"user relative"};
    let lit=" ";
    if (jobj.id) { lio.idf=jobj.id; lim.name=liname+jobj.id; }
    if (jobj.nombre) { lio.nom=jobj.nombre; lit+=jobj.nombre; }
    if (jobj.persona) { lio.per=jobj.persona; lit+=" - "+jobj.persona; }
    if (jobj.email) { lio.ema=jobj.email; }
    lio.eChilds=[lim,{eText:lit}];
    const list=ule.children;
    let appended=false;
    if (list.length>0 && lio.nom) for(l=0; l< list.length; l++) {
        let currLi=list[l];
        let currNm=currLi.nom?currLi.nom:currLi.getAttribute("nom");
        let cmpR=lio.nom.localeCompare(currNm);
        if (cmpR==0) {
            console.log("Ya existe "+currNm);
            appended=true;
            overlayMessage(getParagraphObject("El usuario ya está anexado en la lista de excepciones permitidas"));
        } else if (cmpR<0) {
            appended=true;
            ule.insertBefore(ecrea(lio),currLi);
        }
    }
    if (!appended) ule.appendChild(ecrea(lio));
    clrem("lst"+sfxname,"hidden");
}
function addError(text, code, textResponse, extraData) {
    overlayMessage(getParagraphObject(text));
    if (textResponse && textResponse.length>0) console.log("Response: ", textResponse);
    if (extraData) console.log("ExtraData: ", extraData);
}
function addUser(evt,code) {
    let tgt=evt.target;
    while(tgt && tgt.tagName!=="INPUT") tgt=tgt.previousElementSibling;
    console.log("INI addUser",tgt);
    if (tgt) saveCheck("CFDI_ALLOW"+code+"_",tgt.value,function(jobj){
        addList(jobj,code,"CFDI_ALLOW"+code+"_");
        tgt.value="";
    },addError);
}
function delUser(evt) {
    let tgt=evt.target;
    toggleInfo(event,(tg)=>{
        while(tg && tg.tagName!=="LI") tg=tg.parentNode;
        if (tg) {
            const blk=tg.parentNode;
            ekil(tg);
            if (blk.children.length==0) cladd(blk.parentNode,"hidden");
        }
    });
}
function toggleInfo(evt,successFunc) {
    const tgt=evt.target;
    const name=tgt.name;
    console.log("INI function toggleInfo "+name+(tgt.checked?" checked":" not checked"));
    saveCheck(name,tgt.checked?"1":"0",function(){
        if(tgt.id==="allowLastMonth") {
            if (tgt.checked) {
                cladd(ebyid("editButton"),"hidden");
                cladd(ebyid("clearanceList"),"hidden");
            } else {
                clrem(ebyid("editButton"),"hidden");
                clrem(ebyid("clearanceList"),"hidden");
            }
        }
        if (successFunc) successFunc(tgt);
    },function(text, code, textResponse, extraData){
        addError(text, code, textResponse, extraData);
        tgt.checked=!tgt.checked;
    });
}
function removeUser(evt) {
    const tgt=evt.target;
    const name=tgt.parentNode.textContent.trim();
    console.log("INI function removeUser "+name);
    if (tgt.tagName==="IMG" && tgt.parentNode.tagName==="LI") {
        let liE=tgt.parentNode;
        let list="";
        fee(lbycn("user"),function(elem){if(elem!==liE){if(list.length>0)list+=",";list+=elem.id.slice(1);}});
        saveCheck("CFDI_IGNOREMONTHLIMIT",list,function(){
            liE.parentNode.removeChild(liE);
        });
    } else console.log(tgt.tagName+" not IMG or "+tgt.parentNode.tagName+" not LI");
}
function showFilterLine(evt) {
    const tgt=evt.target;
    const prg=tgt.parentNode;
    const backDrop={htmlObj:{eName:"DIV",className:"backdrop",onclick:function(evt){ekil(this);return eventCancel(evt);}}};
    const rect=prg.getBoundingClientRect();
    const size=1;
    let divWidth = rect.right-rect.left;
    let divHeight = rect.bottom-rect.top;
    let divTop = rect.top+divHeight;
    let divLeft = rect.left;
    backDrop.htmlObj.eChilds=[{
        eName:"DIV",id:"filterLine",style:"position:fixed;z-index:8898;width:"+divWidth+"px;top:"+divTop+"px;left:"+divLeft+"px;box-shadow:0 1px 3px 0 rgba(0,0,0,.25);border-radius: 6px;padding:5px;",className:"basicBG",eChilds:[{eText:"Usuario o Nombre: "},{eName:"INPUT",type:"text",id:"filterInput",oninput:function(evt){if(this.value.length>=4) browseName(event);}}],
            onclick:function(evt){return eventCancel(evt);}
    }];
    document.body.appendChild(ecrea(backDrop.htmlObj));
    ebyid("filterInput").focus();
}
function appendUser(evt) {
    const tgt=evt.target;
    console.log("SELECTED "+this.userId+" | "+this.userName);
    // ToDo: agregarlo a BD y luego agregarlo a clearanceList
    if (!ebyid("u"+tgt.userId)) {
        let list="";
        fee(lbycn("user"),function(elem){if(list.length>0)list+=",";list+=elem.id.slice(1);});
        if(list.lenght>0)list+=",";
        list+=tgt.userId;
        saveCheck("CFDI_IGNOREMONTHLIMIT",list,function(){
            const cle=ebyid("clearanceList");
            cle.appendChild(ecrea({eName:"LI",id:"u"+tgt.userId,name:tgt.userName,className:"user relative",eChilds:[{eName:"IMG",src:"imagenes/icons/deleteIcon12.png",className:"pointer abs rgt4 top4",onclick:removeUser},{eText:" "+tgt.textContent}]}));
            fee(lbycn("backdrop"),ekil);
        });
    }    
}
function browseName(evt) {
    const tgt=event.target;
    console.log("INI function browseName: "+tgt.value);
    if (tgt.xhp) tgt.xhp.abort();
<?php
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $usrData=$usrObj->getData("nombre in ('admin','sistemas')",0,"id");
    $idList=array_column($usrData,"id");
?>
    let list="<?=implode(",",$idList)?>"; // inicial
    fee(lbycn("user"),function(elem){if(list.length>0)list+=",";list+=elem.id.slice(1);});
    console.log("Exception List = "+list);
    tgt.xhp=postService("consultas/Usuarios.php", {accion:"browseUserName",nombre:tgt.value,exceptions:list}, function(text,pars,state,status){
        if (state<4||status<200) {
            //console.log(state+"/"+status);
            return;
        }
        if (state!=4||status!=200) {
            console.log(state+"/"+status);
            return;
        }
        if (text.length==0) {
            console.log("empty. ",pars);
            return;
        }
        try {
            let response=JSON.parse(text);
            if (response.result && response.result==="success") {
                const filterLineElem=ebyid("filterLine");
                if (!filterLineElem) {
                    console.log("Backdrop closed!");
                    return;
                }
                if (!response.data) {
                    console.log("Corrupt Response: "+text);
                    return;
                }
                if (response.message) console.log(response.message);
                if (response.query) console.log(response.query);
                console.log(response.data);
                const rect=filterLineElem.getBoundingClientRect();
                const size=Math.min(5,response.data.length);
                let divWidth = rect.right-rect.left;
                let difHeight = (rect.bottom-rect.top);
                let divTop = rect.top+difHeight+1;
                let divLeft = rect.left;
                let divHeight=size*difHeight;
                const optionsArr=[];
                fee(response.data, function(elem) {
                    optionsArr.push({eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:5px;cursor:pointer;",userId:elem.id,userName:elem.nombre,eText:elem.persona,className:"hoverDark2",
                    onclick:appendUser});
                });
                const resultListObj={eName:"DIV",id:"resultList",style:"position:fixed;z-index:8898;width:"+divWidth+"px;height:"+divHeight+"px;top:"+divTop+"px;left:"+divLeft+"px;box-shadow:0 1px 3px 0 rgba(0,0,0,.25);overflow-x: hidden;overflow-y: auto;border-radius: 6px;padding:5px;",className:"basicBG",eChilds:optionsArr,onclick:function(evt){return eventCancel(evt);}};
                ekil(ebyid("resultList"));
                filterLineElem.parentNode.appendChild(ecrea(resultListObj));
            } else {
                console.log("FAILURE: "+text);
                ekil(ebyid("resultList"));
            }
        } catch (ex) {
            console.log("error: ",ex,pars.xmlHttpPost.responseText);
        }
    });
}
function updatePaymentReceiptStatus(evt) {
    const tgt=event.target;
    console.log("INI function updatePaymentReceiptStatus: "+tgt.value);
    if (tgt.xhp) tgt.xhp.abort();
    readyService("consultas/Facturas.php", {action: "tempUpdtPymRcpt"}, (j, x, n)=> {
        if (j) console.log("Result "+JSON.stringify(j, jsonCircularReplacer()));
        if (x) console.log("EXTRA: "+JSON.stringify(x, jsonCircularReplacer()));
        overlayClose();
        let title="RESULTADO";
        let message="DETALLE";
        switch(j.result) {
            case "success":
                title="EXITO";
                message=j.message+". "+j.num+" facturas actualizadas.";
                break;
            case "empty":
                title="SIN CAMBIOS";
                message="No se actualizo ninguna factura";
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
<?php
clog1seq(-1);
clog2end("scripts.configuracion");
