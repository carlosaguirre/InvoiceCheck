// JavaScript Document
var timeOutFolio = 0;
var timeOutCli = 0;
var timeOutPrv = 0;
var timeOutRango=0;
var timeOutAdvanced=0;
var timeOutOverlay=0;
var timeOutPersona=0;
var timeOutPerfiles=0;
var timeOutAcciones=0;
var pressedKeys=0;
var numLog=0;
var doShowLogs=false;
var doShowFuncLogs=false;
var doShowElemLogs=false;
var _isMouseDown = false;
var _startX = 0;
var _startY = 0;
var _offsetX = 0;
var _offsetY = 0;
var _dragElement;
var myLog=false;
var additionalResizeScript=false;

// Clock Functionality
function startInterval() {
  setInterval('updateTime();', 1000);  
}
function updateTime() {
  let nowMS = now.getTime();
  nowMS += 1000;
  now.setTime(nowMS);
  let clock = ebyid('pie_clock');
  if(clock) {
    clock.innerHTML = ("0" + now.getHours()).slice(-2) + ":" + ("0" + now.getMinutes()).slice(-2) + ":" + ("0" + now.getSeconds()).slice(-2);//adjust to suit
  }
}
function onresizeScripts() {
    funclog("INI", "onresizeScripts");
    let msg = "";
    let ovy = ebyid("overlay");
    let ovyHgt = 0;
    if (ovy) {
        msg+=" overlay";
        if (ovy.classList.contains("hidden") || ovy.style.visibility==="hidden")
            msg+=" hidden";
        else {
            ovyHgt = ovy.offsetHeight;
            msg+=" height="+ovyHgt;
            let wbox = ebyid("wheelbox");
            let wboxHgt = 0;
            if (wbox) {
                msg+=". wheelbox";
                if (wbox.classList.contains("hidden") || wbox.style.visibility==="hidden")
                    msg+=" hidden";
                else {
                    wboxHgt = wbox.offsetHeight;
                    msg+=" height="+wboxHgt;
                    let ima = wbox.firstElementChild;
                    if (ima.tagName==="IMG") {
                        if (ima.offsetHeight>ovyHgt || ima.naturalHeight>=ovyHgt) {
                            ima.height = ovyHgt;
                            wbox.style.paddingTop="0px";
                        } else {
                            ima.height = ima.naturalHeight;
                            let newTop = (ovyHgt-ima.height)/2;
                            if (newTop>0) wbox.style.paddingTop=newTop+"px";
                            else          wbox.style.paddingTop="0px";
                        }
                        msg+=" image offheight="+ima.offsetHeight+" height="+ima.height+" natheight="+ima.naturalHeight;
                        // ima.style.width=ovyHgt+"px";
                        // ima.style.height=ovyHgt+"px";
                    }
                }
            }
            clearDialogBoxHeight();
            adjustDialogBoxHeight();
        }
    }
    if (additionalResizeScript) additionalResizeScript();
    if (backdropResizeFunc) backdropResizeFunc();
    funclog("END", "onresizeScripts"+msg);
}
/*
function getReadableFileSizeString(fileSizeInBytes) {
    var i = -1;
    var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
    do {
        fileSizeInBytes = fileSizeInBytes / 1024;
        i++;
    } while (fileSizeInBytes > 1024);

    return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
}
*/
function ajaxRequest() {
    if (window.XMLHttpRequest) try { // if Mozilla, Safari etc
        return new XMLHttpRequest;
    } catch (e) {}
    if (window.ActiveXObject) { //Test for support for ActiveXObject in IE (ignore that XMLHttpRequest in IE7 is broken)
        const activexmodes=["Microsoft.XMLHTTP", "Msxml2.XMLHTTP"] //activeX versions to check for in IE
        for (let i=0; i<activexmodes.length; i++) try {
            return new ActiveXObject(activexmodes[i]);
        } catch (e) {}
    }
    return false;
}
function ajaxRequestOld() {
    if (window.ActiveXObject) { //Test for support for ActiveXObject in IE first (as XMLHttpRequest in IE7 is broken)
        for (let i=0; i<activexmodes.length; i++) {
            try {
                return new ActiveXObject(activexmodes[i])
            } catch(e) {
            //suppress error
            }
        }
    } else if (window.XMLHttpRequest) // if Mozilla, Safari etc
        return new XMLHttpRequest()
    else
        return false
}
function getQueryString(formname) {
    //funclog("INI", "getQueryString("+formname+")");
    let form = document.forms[formname];
    let qstr = "";

    function GetElemValue(name, value) {
        qstr += (qstr.length > 0 ? "&" : "")
            + encodeURIComponent(name) + "="
            + encodeURIComponent(value);
        //funclog("SUBINI","GetElemValue("+name+", "+value+") => qstr="+qstr);
    }
	
    let elemArray = form.elements;
    for (let i = 0; i < elemArray.length; i++) {
        let element = elemArray[i];
        let elemType = element.type.toUpperCase();
        let elemName = element.name;
        if (elemName) {
            if  (elemType == "TEXT"
              || elemType == "TEXTAREA"
              || elemType == "PASSWORD"
              || elemType == "BUTTON"
              || elemType == "RESET"
              || elemType == "SUBMIT"
              || elemType == "FILE"
              || elemType == "IMAGE"
              || elemType == "HIDDEN")
                GetElemValue(elemName, element.value);
            else if (elemType == "CHECKBOX" && element.checked)
                GetElemValue(elemName, element.value ? element.value : "On");
            else if (elemType == "RADIO" && element.checked)
                GetElemValue(elemName, element.value);
            else if (elemType.indexOf("SELECT") != -1)
                for (let j = 0; j < element.options.length; j++) {
                    let option = element.options[j];
                    if (option.selected)
                        GetElemValue(elemName, (typeof option.value !== "undefined")? option.value : option.text);
                }
        }
    }
    return qstr;
}
function getQueryData(formname) {
    //funclog("INI","getQueryData("+formname+")");
    let form = document.forms[formname];
    let formData = new FormData(form);
    function GetTextValue(name, value) {
        let escaped_name = encodeURIComponent(name).replace(/%5B/g, '[').replace(/%5D/g, ']');
        let escaped_value = encodeURIComponent(value ? value : "");
        formData.append(escaped_name, escaped_value);
        //funclog("SUBINI", "GetTextValue("+name+", "+value+") => '"+escaped_name+"' : '"+escaped_value+"'");
    }
    function GetFileValue(name, file, filename) {
        let escaped_name = encodeURIComponent(name).replace(/%5B/g, '[').replace(/%5D/g, ']');
        formData.append(escaped_name, file, filename);
        //funclog("SUBINI", "GetFileValue("+name+", <file>, "+filename+") => '"+filename+"' APPENDED with variable name '"+escaped_name+"'");
    }
	
    let elemArray = form.elements;
    for (let i = 0; i < elemArray.length; i++) {
        let element = elemArray[i];
        let elemType = element.type.toUpperCase();
        let elemName = element.name;
        if (elemName) {
            if  (elemType == "TEXT"
              || elemType == "TEXTAREA"
              || elemType == "PASSWORD"
              || elemType == "BUTTON"
              || elemType == "RESET"
              || elemType == "SUBMIT"
              || elemType == "IMAGE"
              || elemType == "HIDDEN")
                GetTextValue(elemName, element.value);
            else if (elemType == "FILE") {
                //funclog("SUBTAG", "FOUND FILE TYPE");
                let files = element.files;
                for (let f=0; f<files.length; f++) {
                    let file = files[f];
                    //funclog("SUBTAG", "TRYING TO UPLOAD "+file.name+"|"+file.type+"~="+element.filetype);
                    if(element.filetype)
                        if (!file.type.match(element.filetype)) continue;
                    GetFileValue(elemName, file, file.name);
                }
            } else if (elemType == "CHECKBOX" && element.checked)
                GetTextValue(elemName, element.value ? element.value : "On");
            else if (elemType == "RADIO" && element.checked)
                GetTextValue(elemName, element.value);
            else if (elemType.indexOf("SELECT") != -1)
                for (let j = 0; j < element.options.length; j++) {
                    let option = element.options[j];
                    if (option.selected)
                        GetTextValue(elemName, (typeof option.value !== "undefined")? option.value : option.text);
                }
        }
    }
    return formData;
}
function toPostQueryString(item,prefix="") {
    let retval="";
    if (item===null) {
        if (prefix.length>0) retval=prefix+"=null";
    } else if(Array.isArray(item)) {
        item.forEach(function(elem,idx) {
            if(retval.length>0) retval+="&";
            retval+=toPostQueryString(elem,prefix+"["+idx+"]");
        });
    } else if (typeof item==="object") {
        for (let key in item) {
            if (retval.length>0) retval+="&";
            retval+=toPostQueryString(item[key],prefix+"["+key+"]");
        }
    } else if (prefix.length>0) {
        retval=prefix+"="+item;
    } else retval=""+item;
    return retval;
}
function toPostParameters(item,prefix="",exceptKeys=[]) {
    const fixParameters={};
    if (item===null) return fixParameters;
    else if (Array.isArray(item)) {
        item.forEach(function(elem,idx) {
            Object.assign(fixParameters,toPostParameters(elem,prefix+"["+idx+"]"));
        });
    } else if (typeof item==="object") {
        for (let key in item) {
            if (exceptKeys && exceptKeys.includes && exceptKeys.includes(key)) fixParameters[key]=item;
            else Object.assign(fixParameters,toPostParameters(item[key],prefix.length==0?key:prefix+"["+key+"]"));
        }
    } else if (prefix.length>0) fixParameters[prefix]=item;
    return fixParameters;
}
const jsonCircularReplacer = () => {
  const seen = new WeakSet();
  return (key, value) => {
    if (typeof value === "object" && value !== null) {
      if (seen.has(value)) {
        return;
      }
      seen.add(value);
    }
    return value;
  };
};
function postService(url, parameters, retFunc, errFunc, progressFunc) {
    funclog("INI","postService("+url+", "+JSON.stringify(parameters,jsonCircularReplacer())+")");
    let xmlHttpPost = ajaxRequest();
    let fd = new FormData();
    if (parameters) {
        if (typeof parameters==="object") {
            for (let key in parameters) if (parameters.hasOwnProperty(key) && typeof parameters[key] !== "function") {
                let pk=parameters[key];
                if (pk instanceof File) {
                    funclog("PARAM",key+"=file("+pk.name+")");
                    fd.append(key, pk, pk.name);
                } else if (Array.isArray(pk) || pk instanceof FileList) {
                    for (let i=0;i<pk.length;i++) {
                        if (pk[i] instanceof File) {
                            funclog("PARAM",key+"["+i+"]=file("+pk[i].name+")");
                            fd.append(key+"["+i+"]",pk[i],pk[i].name);
                        } else {
                            if (typeof pk[i]==="object") pk[i]=JSON.stringify(pk[i],jsonCircularReplacer());
                            funclog("PARAM",key+"["+i+"]="+pk[i]);
                            fd.append(key+"["+i+"]",pk[i]);
                        }
                    }
                } else if (typeof pk==="object") {
                    for (let pp in pk) {
                        let ppVal=pk[pp];
                        if (ppVal instanceof File) {
                            funclog("PARAM",key+"["+pp+"]=file("+ppVal.name+")");
                            fd.append(key+"["+pp+"]",ppVal,ppVal.name);
                        } else {
                            if (typeof ppVal==="object") ppVal=JSON.stringify(ppVal,jsonCircularReplacer());
                            funclog("PARAM",key+"["+pp+"]="+ppVal);
                            fd.append(key+"["+pp+"]",ppVal);
                        }
                    }
                } else if(key==="timeout") {
                    funclog("PARAM","TIMEOUT="+pk);
                    xmlHttpPost.timeout=pk;
                } else {
                    funclog("PARAM",key+"="+pk);
                    fd.append(key, pk);
                }
            }
        } else {
            funclog("PARAM","parameter="+parameters);
            fd.append("parameter",parameters);
            parameters={parameter:parameters};
        }
    } else parameters={};
    parameters.xmlHttpPost=xmlHttpPost;
    xmlHttpPost.parameters=parameters;
    xmlHttpPost.progressIndex=0;
    // OPEN wait
    xmlHttpPost.open("POST", url, true);
    xmlHttpPost.onreadystatechange = function() {
        xmlHttpPost.parameters.state=xmlHttpPost.readyState;
        xmlHttpPost.parameters.status=xmlHttpPost.status;
        //if (xmlHttpPost.readyState==4 && xmlHttpPost.status==200) {
            //funclog("postService "+url+"?"+JSON.stringify(parameters,jsonCircularReplacer())+": "+xmlHttpPost.responseText);
        //}
        //funclog("RDY","postService("+url+") "+xmlHttpPost.readyState+"/"+xmlHttpPost.status);
        if (retFunc) {
            if (typeof retFunc==="function") {
                let retmsg=xmlHttpPost.responseText;
                //console.log("CHK | responseText = "+retmsg);
                if (xmlHttpPost.status!=200&&retmsg.length==0) {
                    retmsg=xmlHttpPost.statusText;
                    console.log("CHK | Empty responseText, statusText = "+retmsg);
                }
                retFunc(retmsg, xmlHttpPost.parameters, xmlHttpPost.readyState, xmlHttpPost.status);
            } else console.log("CHK | retFunc not Function: "+(typeof retFunc),retFunc);
        }
    };
    if (errFunc && typeof errFunc==="function") {
        xmlHttpPost.onerror = function(evt) {
            let errmsg=xmlHttpPost.responseText;
            if (errmsg.length==0) errmsg=xmlHttpPost.statusText;
            errFunc(errmsg, xmlHttpPost.parameters, evt); // xmlHttpPost.readyState, xmlHttpPost.status
        };
        xmlHttpPost.ontimeout = function(evt) {
            errFunc("Request Timed Out", xmlHttpPost.parameters, evt);
        };
    } else {
        errType="ERR"+(errFunc?" (errFunc is "+(typeof errFunc)+")":"")+": ";
        xmlHttpPost.onerror = function(evt) {
            console.log(errType+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        };
        xmlHttpPost.ontimeout = function(evt) {
            console.log(errType+"Request Timed Out, PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        };
    }
    if (progressFunc && typeof progressFunc==="function") {
        xmlHttpPost.onprogress = function (evt) {
            progressFunc(xmlHttpPost.responseText, xmlHttpPost.parameters, evt);
        };
    } else xmlHttpPost.onprogress = function(evt) {
        //console.log("PROGRESS"+(progressFunc?" (progressFunc is "+(typeof progressFunc)+")":"")+": "+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        let txt=xmlHttpPost.responseText;
        if (txt.length>100) txt=txt.slice(0,100)+"...";
        //console.log("PROGRESS"+(progressFunc?(" ("+(typeof progressFunc)+")"):"")+": "+txt);
    }
    xmlHttpPost.send(fd);
    //funclog("END","postService");
    return xmlHttpPost; // para manipular solicitud, vg llamar metodo abort()
}
function progressService(url, parameters, rdyFunc, errFunc, prgFunc) {
    if (typeof parameters === "string") {
        if ((/^{/.test(parameters) && /}$/.test(parameters))||
            (/^\[/.test(parameters) && /\]$/.test(parameters)))
            parameters=parameters.slice(1, -1);
        parameters=parameters.split(/\s?[,;]\s?/)
                            .map(pair => pair.split(/[\:=]/))
                            .map(([key, value]) => [key, isNaN(value)?value:+value]);
    }
    return prePostService(url, {...parameters, hasProgress: true, validEmptyClosure: true}, rdyFunc, errFunc, prgFunc);
}
function readyService(url, parameters, rdyFunc, errFunc) {
    if (parameters) parameters.hasProgress=false;
    else parameters={hasProgress: false, validEmptyClosure: false};
    return prePostService(url, parameters, rdyFunc, errFunc);
}
function prePostService(url, parameters, rdyFunc, errFunc, prgFunc) {
    const postErrFunc=getPostErrFunc(errFunc,parameters);
    let postRetFunc=null;
    let postPrgFunc=null;
    if (parameters.hasProgress && prgFunc) {
        postRetFunc=getPostRetFunc(rdyFunc, errFunc, {...parameters, notProgressFunc: true});
        postPrgFunc=getPostRetFunc(prgFunc, errFunc, {...parameters, notReadyFunc: true, validEmptyClosure: false});
    } else
        postRetFunc=getPostRetFunc(rdyFunc, errFunc, parameters);
    const xmlHttpPost=postService(url,parameters,postRetFunc,postErrFunc,postPrgFunc);
    if(parameters.hasProgress && parameters.inclusiveSeparator) xmlHttpPost.inclusiveSeparator=parameters.inclusiveSeparator;
    return xmlHttpPost;
}
function getPostRetFunc(rdyFunc,errFunc,extra) {
    //console.log("INI function getPostRetFunc extra:",extra);
    return (responseText,params,readyState,hStatus)=>{
        //console.log("INI retfunc responseText("+responseText.length+"), params, state="+readyState+", status="+hStatus);
        if (!extra) extra={};
        if (!extra.parameters) {
            if (extra.length===params.length) {
                for(let p in params) {
                    if (params.hasOwnProperty(p)) {
                        if (extra[p]!==params[p]) {
                            extra.parameters=params;
                            break;
                        }
                    }
                }
            } else extra.parameters=params;
        }
        if (!extra.state) extra.state=readyState;
        if (!extra.status) extra.status=hStatus;
        const xobj=params.xmlHttpPost;
        //console.log("start progressIndex="+xobj.progressIndex+", xobj=",xobj);
        try {
            if (readyState>4 || hStatus>200) throw new Error("SERVICIO NO DISPONIBLE "+readyState+"/"+hStatus);
            if (readyState<3 || hStatus<200) return;
            if (readyState===3 && !params.hasProgress && !extra.hasProgress) {
                console.log("PROGRESS NOT VALID");
                return false;
            }
            if (readyState===3 && extra.notProgressFunc) {
                console.log("THIS IS NOT THE PROGRESS FUNC");
                return false;
            }
            if (readyState===4 && extra.notReadyFunc) {
                console.log("THIS IS NOT THE READY FUNC");
                return false;
            }
            if (responseText==="REFRESH") {
                location.reload(true);
                return false;
            }
            //if ((readyState==3||readyState==4) && hStatus==200) {
                let responseList=false;
                if (xobj && xobj.progressIndex!==undefined && xobj.progressIndex!==false) {
                    if (xobj.progressIndex<0) xobj.progressIndex=0;
                    if (xobj.inclusiveSeparator && xobj.inclusiveSeparator.length>0) {
                        //console.log("has inclusiveSeparator='"+xobj.inclusiveSeparator+"'");
                        const lastSeparatorIndex=responseText.lastIndexOf(xobj.inclusiveSeparator);
                        if (lastSeparatorIndex>xobj.progressIndex) {
                            responseText=responseText.slice(xobj.progressIndex,lastSeparatorIndex);
                            xobj.progressIndex=lastSeparatorIndex+xobj.inclusiveSeparator.length;
                            responseList=responseText.split(xobj.inclusiveSeparator);
                        } else if (readyState==4 && responseText.length>xobj.progressIndex) {
                            responseText=responseText.slice(xobj.progressIndex);
                            xobj.progressIndex=responseText.length;
                            responseList=responseText.split(xobj.inclusiveSeparator);
                        } else {
                            responseText="";
                        }
                    } else {
                        if (xobj.progressIndex>=responseText.length) {
                            responseText="";
                        } else if (xobj.progressIndex>0) {
                            responseText=responseText.slice(xobj.progressIndex);
                            xobj.progressIndex+=responseText.length;
                        } else {
                            xobj.progressIndex=responseText.length; //responseText="";
                        }
                        if (responseText.length>0) {
                            console.log("READY IN ONCE");
                            responseList=[responseText];
                        }
                    }
                    if (responseText.length==0) {
                        if (readyState==3) return false;
                        if (xobj.progressIndex==0) throw new Error("VACIO.");
                    }
                } else if (readyState==3) return false;
                else if (responseText.length==0) throw new Error("VACIO!");

                //postService("consultas/Errores.php",{accion:"savelog",nombre:"getPostRetFunc",texto:"TEST RESULT (STATE="+readyState+", STATUS="+hStatus+")\nTEXT: "+responseText+"\nPARAMS: "+JSON.stringify(params,jsonCircularReplacer())});
                if (responseList) responseList.forEach((txt,idx,arr)=>{
                    if (txt.length>0) {
                        const jobj=JSON.parse(txt);
                        if (readyState==3) xobj.lastJObj=jobj;
                        if (jobj.action) {
                            if (jobj.action==="refresh"||jobj.action==="reload") location.reload(true);
                            else if (jobj.action==="delay") {
                                const clockElem=ebyid("pie_clock");
                                if (clockElem) {
                                    const currTime=clockElem.value;
                                    console.log(currTime+" Delayed Relocation in 5 minutes");
                                }
                                delayTimeout=setTimeout(function(){location.reload(true);},5*60*1000);
                            }
                        }
                        let nextStepFnc=null;
                        if (params.url && params.rdyFnc) {
                            nextStepFnc=function(x) {
                                console.log("INI anonymous nextStepFunction");
                                let thisXService=readyService;
                                if (x.xServiceName) thisXService=x.xServiceName;
                                if (x.url && x.rdyFnc) {
                                    if (x.errFnc) thisXService(x.url,x,x.rdyFnc,x.errFnc);
                                    else thisXService(x.url,x,x.rdyFnc);
                                }
                            };
                        }
                        if (jobj.result) {
                            const result=jobj.result.toLowerCase();
                            if (result==="refresh"||result==="reload") location.reload(true);
                            else if (rdyFunc) {
                                jobj.params=params;
                                if (typeof rdyFunc==="function") rdyFunc(jobj,extra,nextStepFnc);
                                else console.log("INI getPostRetFunc.rdyFunc is not function: ",rdyFunc);
                            } else console.log("INI getPostRetFunc.result.responseBlock="+txt);
                        } else throw new Error("SIN RESULTADO");
                    } else console.log("EMPTY JSON OBJECT");
                });
                else if (readyState==4 && rdyFunc && typeof rdyFunc==="function" && extra.validEmptyClosure) {
                    //console.log("EMPTY LAST BLOCK");
                    if (xobj.lastJObj) extra.lastJObj=xobj.lastJObj;
                    rdyFunc(null,extra);
                }
            //}
        } catch (err) {
            let messageError="";
            // toDo: Agregar clase de objeto err
            if (err.name) {
                messageError+=err.name;
                if (err.message) messageError+=": "+err.message;
                if (err.fileName) {
                    messageError+="("+err.fileName;
                    if (err.lineNumber) messageError+="#"+err.lineNumber;
                    if (err.colNumber) messageError+="X"+err.colNumber;
                    messageError+=")";
                }
            } else messageError=err.toString();
            if (errFunc) {
                if (typeof errFunc==="function") errFunc(messageError,responseText,extra);
                else console.log("INI getPostRetFunc.errFunc is not function: ",errFunc);
            } else console.log("INI getPostRetFunc.exception: message="+messageError+" | response="+responseText);
            postService("consultas/Errores.php",{accion:"savelog",nombre:"getPostRetFunc",texto:messageError+"\nTEXT: "+responseText+"\nPARAMS: "+JSON.stringify(params,jsonCircularReplacer())+"\nSTATE="+readyState+", STATUS="+hStatus});
        }
    };
}
function getPostErrFunc(errFunc,extra) {
    //console.log("INI function getPostErrFunc");
    return (errmsg, params, evt)=>{
        //console.log("INI errfunc errmsg("+errmsg.length+"), params, event=",evt);
        if (!extra) extra={};
        if (!extra.parameters) extra.parameters=params;
        if (!extra.event) extra.event=evt;
        if (errFunc) errFunc(errmsg,"",extra);
        postService("consultas/Errores.php",{accion:"savelog",nombre:"getPostErrFunc",texto:errmsg});
    };
}
function showOnReady(jobj, extra, nextStepFnc) {
    console.log("INI showOnReady jobj",jobj,"extra",extra);
    if (jobj.url) extra.url=jobj.url;
    if (jobj.dataFields) extra.dataFields=jobj.dataFields;
    if ((extra.msgId||jobj.msgId) && jobj.message) {
        const msgElem=ebyid(extra.msgId?extra.msgId:jobj.msgId);
        if (msgElem && (extra.msgAs||jobj.msgAs)) {
            if (extra.msgAs==="value"||jobj.msgAs==="value") msgElem.value=jobj.message;
            else if (extra.msgAs==="innerHTML"||jobj.msgAs==="innerHTML") msgElem.innerHTML=jobj.message;
            else /*if (extra.msgAs==="textContent")*/ msgElem.textContent=jobj.message;
        }
    }
    const dtEl=((extra.dataId||jobj.dataId)&&jobj.data)?ebyid(extra.dataId?extra.dataId:jobj.dataId):false;
    const ftEl=((extra.footId||jobj.footId)&&jobj.data)?ebyid(extra.footId?extra.footId:jobj.footId):false;
    const scrbDtEl=dtEl?getScrollParent(dtEl):false;
    const scrbFtEl=ftEl?getScrollParent(ftEl):false;
    const scrd2Btm=((scrbDtEl===scrbFtEl)&&scrbFtEl)?(Math.abs(scrbFtEl.scrollHeight-scrbFtEl.scrollTop-scrbFtEl.offsetHeight)<10):false;
    if (dtEl && (extra.dataAs||jobj.dataAs)) {
        if (extra.dataAs==="tbrow"||jobj.dataAs==="tbrow") fee(jobj.data,dtLine=>{
            const row={eName:"TR"};
            if (extra.rowClass) row.className=extra.rowClass;
            else if (jobj.rowClass) row.className=jobj.rowClass;
            if (extra.dataFields) {
                row.eChilds=[];
                const numRow=dtEl.children.length+1;
                fee(extra.dataFields,fld=>{
                    if (fld==="#" && !dtLine[fld]) row.eChilds.push({eName:"TD",id:"#fld",eText:""+numRow});
                    else row.eChilds.push({eName:"TD",id:"fld_"+fld,eText:dtLine[fld]});
                });
            } else {
                row.eChilds=[];
                for (let pk in dtLine) {
                    if (Object.hasOwn(dtLine, pk)) {
                        row.eChilds.push({eName:"TD",id:"pk_"+pk,eText:dtLine[pk]});
                    }
                }
            }
            dtEl.appendChild(ecrea(row));
        });
    }
    if (ftEl) {
        let npn=(jobj.pageno?jobj.pageno:0); // next page number
        if(npn>1) npn-=2; // remove next page and remove current page (current page fields are added from data length)
        else if (npn>0) npn--;
        const fpp=(jobj.rowsPerPage?jobj.rowsPerPage:jobj.data.length); // fields per page
        const cdf=fpp*npn+jobj.data.length; // currently downloaded fields
        const tfd=(jobj.totRows?jobj.totRows:extra.totRows);
        const pl_s=(tfd==1?"":"s");
        ftEl.textContent=cdf+(tfd==cdf?"":"/"+tfd)+" registro"+pl_s+" encontrado"+pl_s;
        if (scrd2Btm) ftEl.scrollIntoView();
        else {
            console.log("DataElement: "+(dtEl?"TRUE":"FALSE")+", FootElement: "+(ftEl?"TRUE":"FALSE"));
            console.log("ScrollDataE: "+(scrbDtEl?"TRUE":"FALSE")+", ScrollFootE: "+(scrbFtEl?"TRUE":"FALSE"));
            if (scrbFtEl) console.log("ScrollHeight="+scrbFtEl.scrollHeight+", ScrollTop="+scrbFtEl.scrollTop+", OffsetHeight="+scrbFtEl.offsetHeight+", DIFF="+Math.abs(scrbFtEl.scrollHeight-scrbFtEl.scrollTop-scrbFtEl.offsetHeight));
        }
    }
    if (nextStepFnc && jobj.pageno && jobj.lastPage && jobj.pageno<=jobj.lastPage && extra.url && extra.rdyFnc) {
        extra.pageno=jobj.pageno;
        setTimeout(function(xtra){nextStepFnc(xtra);},95,extra);
    } else {
        if (!nextStepFnc) console.log("No Next Step Fnc");
        else if (!jobj.pageno) console.log("No JObj PageNo");
        else if (!jobj.lastPage) console.log("No JObj LastPage");
        else if (jobj.pageno>jobj.lastPage) console.log("LastPage "+jobj.lastPage+" reached ("+jobj.pageno+")");
        else if (!extra) console.log("No Extra");
        else if (!extra.url) console.log("No Extra URL");
        else if (!extra.rdyFnc) console.log("No RdyFnc");
        else console.log("No real reason is weird: jobj=",jobj,", extra=",extra);
    }
}
function showOnError(messageError,responseText,extra) {
    console.log("INI showOnError msg='"+messageError+"', txt=",responseText,", extra=",extra);
    overlayClose();
    overlayMessage(messageError, "ERROR");
}
function ajaxPost(url, formname, responsediv, responsemsg, callbacksentfunc, timeOutSec) {
    //conlog("INI", "ajaxPost(url: "+url+", form: "+formname+")");
    let xmlHttpPost = ajaxRequest();
    xmlHttpPost.open("POST", url, true);
    if (timeOutSec) xmlHttpPost.timeout=1000*timeOutSec;
    else xmlHttpPost.timeout=180000;
    let form = document.forms[formname];
    let respDiv = false;
    if (responsediv) respDiv = ebyid(responsediv);
    if (responsemsg) respDiv.innerHTML = responsemsg;
    if (form.enctype!="multipart/form-data")
        xmlHttpPost.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpPost.responseLength=0;
    xmlHttpPost.onreadystatechange = function(evt) {
        gralEventHandler(evt);
        //console.log("TAG", "state: "+xmlHttpPost.readyState+", status: "+xmlHttpPost.status);
        if (xmlHttpPost.readyState==4 && xmlHttpPost.status==200) {
            //updatepage
            //conlog("TAG", "AjaxPost READY! => "+responsediv);
            if (respDiv) {
                let respTxt = xmlHttpPost.responseText;
                if (xmlHttpPost.isProgressEnabled) {
                    if (xmlHttpPost.responseLength==0) respDiv.innerHTML="";
                    else respTxt = respTxt.slice(xmlHttpPost.responseLength);
                    if (respTxt.length>0) respDiv.innerHTML += respTxt;
                } else {
                    respDiv.innerHTML = respTxt;
                    //conlog("TAG", "Progress disabled!");
                }
                xmlHttpPost.responseLength=xmlHttpPost.responseText.length;
                //conlog("TAG", "AjaxPost SENT TO "+respDiv.id+": "+xmlHttpPost.responseLength+"/"+xmlHttpPost.responseText.length+" ("+xmlHttpPost.responseLength+")");
            } else console.log("ERR", "NO responsediv "+responsediv)
            if (callbacksentfunc) callbacksentfunc(xmlHttpPost);
            else console.log("TAG", "No callback func.");
            //conlog("TAG","END READY");
        } else if (respDiv && xmlHttpPost.readyState==3 && xmlHttpPost.status==200) {
            let respTxt = xmlHttpPost.responseText;
            if (xmlHttpPost.isProgressEnabled) {
                if (xmlHttpPost.responseLength==0) respDiv.innerHTML="";
                //console.log("SLICE","FROM "+xmlHttpPost.responseLength+" TO "+respTxt.length);
                respTxt = respTxt.slice(xmlHttpPost.responseLength);
                //console.log("CHUNK","LENGTH="+respTxt.length);
                if (xmlHttpPost.inclusiveSeparator) {
                    const incSepLstIdx=respTxt.lastIndexOf(xmlHttpPost.inclusiveSeparator);
                    if (incSepLstIdx>-1) {
                        const partLen=incSepLstIdx+(xmlHttpPost.inclusiveSeparator.length);
                        const partTxt = respTxt.slice(0,partLen);
                        respDiv.innerHTML += partTxt;
                        xmlHttpPost.responseLength+=partLen;
                        //console.log("REAL CHUNK","LENGTH="+partTxt.length+"="+partLen+", NEW FULL LENGTH="+xmlHttpPost.responseLength);
                    } else {
                        //respDiv.innerHTML += respTxt;
                        //xmlHttpPost.responseLength=xmlHttpPost.responseText.length;
                        console.log("FALSE CHUNK","LENGTH=0, NEW FULL LENGTH="+xmlHttpPost.responseLength);
                    }
                } else {
                    if (respTxt.length>0) respDiv.innerHTML += respTxt;
                    xmlHttpPost.responseLength=xmlHttpPost.responseText.length;
                    //console.log("FULL CHUNK","LENGTH="+(respTxt.length)+", NEW FULL LENGTH="+xmlHttpPost.responseLength);
                }
            //} else {
                //console.log("TAG", "Progress disabled!");
            }
            //if (xmlHttpPost.extraTimeout) xmlHttpPost.timeout+=xmlHttpPost.extraTimeout;
            //conlog("TAG", "AjaxPost SENT PARTIAL TO "+respDiv.id+": "+(respTxt?respTxt.length+"/"+xmlHttpPost.responseText.length:-1)+" ("+xmlHttpPost.responseLength+")");
            //if (!responsemsg) {
            //    responsemsg = respDiv.innerHTML;
/*
                if (xmlHttpPost.status!=200) responsemsg += "<img src=\"imagenes/ledred.gif\"/>";
                else switch(xmlHttpPost.readyState) {
                    case 2: responsemsg += "<img src=\"imagenes/ledyellow.gif\"/>";
                    case 3: responsemsg += "<img src=\"imagenes/ledgreen.gif\"/>";
                    default: responsemsg += "<img src=\"imagenes/ledorange.gif\"/>";
                }
*/
            //} //else funclog("DBG", "RESPONSEMSG LEN:"+responsemsg.length);
            //respDiv.innerHTML = responsemsg;
            if (callbacksentfunc) callbacksentfunc(xmlHttpPost,true);
            else console.log("TAG", "No callback func.");
            //conlog("TAG","END PARTIAL");
        } else if (xmlHttpPost.readyState>4 && callbacksentfunc) callbacksentfunc(xmlHttpPost);
    }
    let query = false;
//    if (form.enctype=="multipart/form-data")
//        query = getQueryData(formname);
//    else
//        query = getQueryString(formname);
//    let keys = "";
//    let qkys = query.keys();
//    for (let idx=0; idx<qkys.length; idx++) keys += " "+qkys[idx];
//    funclog("TAG", "AjaxPost sending:"+keys);
    query = new FormData(form);
    const parameters = {};
    for (let pair of query.entries()) {
        let typeName = (typeof pair[1]);
        if (pair[1] instanceof File) typeName="file";
        else if (typeName==="object") typeName=pair[1].constructor.name;
        //console.log("Entry '"+pair[0]+"' = '"+pair[1]+"' ("+typeName+")");
        parameters[pair[0]]=pair[1];
    }
    /*
    if (parameters) for (let key in parameters)
        if (parameters.hasOwnProperty(key) && typeof parameters[key] !== "function") {
            if (Array.isArray(parameters[key])) {
                let pArr=parameters[key];
                for (let i=0;i<pArr.length;i++) fd.append(key+"["+i+"]",pArr[i]);
            } else if(key==="timeout") {
                xmlHttpPost.timeout=parameters[key];
            } else if (parameters[key] instanceof File) {
                fd.append(key, parameters[key], parameters[key].name);
            } else {
                fd.append(key, parameters[key]);
            }
        }
    */
    xmlHttpPost.parameters=parameters;
    const gralEventHandler=function(evt) {
        console.log(" [ "+evt.type+": state "+evt.target.readyState+", status "+evt.target.status+(evt.loaded?", "+evt.loaded+" bytes transferred":(evt.target.responseText?", "+evt.target.responseText.length+" text length":""))+" ]");
    }
    xmlHttpPost.onloadstart=gralEventHandler;
    //xmlHttpPost.onload=gralEventHandler;
    xmlHttpPost.onloadend=gralEventHandler;
    //xmlHttpPost.lastLength=0;
    //xmlHttpPost.onprogress = function (evt) {
    //    gralEventHandler(evt);
    //    conlog("PROGRESS state "+xmlHttpPost.readyState+", status "+xmlHttpPost.status+", length "+xmlHttpPost.responseText.length+", added "+(xmlHttpPost.responseText.length-xmlHttpPost.lastLength));
    //    xmlHttpPost.lastLength = xmlHttpPost.responseText.length;
    //};
    xmlHttpPost.onerror = function(evt) {
        gralEventHandler(evt);
        console.log("ERR: "+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        if (respDiv&&!xmlHttpPost.hasExternalWait) respDiv.innerHTML="";
        overlayMessage([{eName:"P",className:"errorLabel",eText:xmlHttpPost.responseText},{eName:"P",id:"onErrorMessage",eText:"Error de conexión al servidor"}],"ERROR");
        postService(
            "consultas/Logs.php",
            {action:"doclog",filebase:"error",message:"AjaxPost ERROR ["+e.target.readyState+"/"+e.target.status+"] '"+e.target.responseText+"'"+", PARAMS: "+JSON.stringify(xmlHttpPost.parameters,jsonCircularReplacer())},
            function(text,params,state,status) {
                if (state==4&&status==200) {
                    console.log("OnError Log: '"+text+"'");
                    const errPgr=ebyid("onErrorMessage");
                    if(errPgr) errPgr.textContent+=", intente nuevamente.";
                } else if (state>4||status>200) {
                    const errPgr=ebyid("onErrorMessage");
                    if(errPgr) errPgr.textContent+=", intente nuevamente más tarde.";
                }
            },
            function(errmsg, params, evt) {
                const errPgr=ebyid("onErrorMessage");
                if (errPgr) {
                    if (errmsg.length>0) errPgr.textContent+=": "+errmsg;
                    else errPgr.textContent+=", intente nuevamente más tarde.";
                }
            }
        );
        // todo, mandar mensaje de error al servidor
    };
    xmlHttpPost.onabort = gralEventHandler;
    xmlHttpPost.ontimeout = function(evt) {
        gralEventHandler(evt);
        console.log("ERR: Request Timed Out: "+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        if (respDiv&&!xmlHttpPost.hasExternalWait) respDiv.innerHTML="";
        if (xmlHttpPost.timeoutCallbackFunc) xmlHttpPost.timeoutCallbackFunc(evt);
        overlayMessage({eName:"P",className:"cancelLabel bgredvip01 textcenter mbpi boldValue",eText:"Se excedió el tiempo de consulta al servidor."},"ERROR");
    };

    xmlHttpPost.send(query);
    //conlog("END", "ajaxPost sent");
    return xmlHttpPost;
}
function fillMessage(key) {
    funclog("INI", "fillMessage");
    postService("consultas/Mensajes.php", {msgkey:key}, function(text,params,state,status) { if (state!=4||status!=200) return; if(text.length>0) overlayMessage(text,params.title?params.title:"AVISO"); });
}
function fillReporte(nombrereporte) {
    funclog("INI", "fillReporte('"+nombrereporte+"')");
    let inidia = ebyid("inidia");
    let inimes = ebyid("inimes");
    let inianio = ebyid("inianio");
    let findia = ebyid("findia");
    let finmes = ebyid("finmes");
    let finanio = ebyid("finanio");
    let params = "";
    if (inidia.value.length>0 && inimes.value.length>0 && inianio.value.length>0) {
        params += "&datemin="+inianio.value+"-"+inimes.value+"-"+inidia.value;
    }
    if (findia.value.length>0 && finmes.value.length>0 && finanio.value.length>0) {
        params += "&datemax="+finanio.value+"-"+finmes.value+"-"+findia.value;
    }
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let element = ebyid("reportes_resultarea");
            element.innerHTML = xmlhttp.responseText;
            if (element.style && element.style.height) element.style.height=null;
            let export_link = ebyid("export_button");
            export_link.href = "selectores/"+nombrereporte+".php?export=excel"+params;
            export_link.style.display = "inline-block";

            let print_link = ebyid("print_button");
            print_link.href = "javascript: (function(){ let pw=window.open(); pw.document.open(); pw.document.write('<html><body>'+xmlhttp.responseText+'</body></html>'); pw.document.close(); pw.print(); pw.onload = function(e){ pw.close(); }; let scrp = pw.document.createElement('script'); scrp.type='text/javascript'; scrp.text='window.close();'; pw.document.body.appendChild(scrp); })();";
            print_link.style.display = "inline-block";
        }
    };
    xmlhttp.open("GET","selectores/"+nombrereporte+".php?tabla=1"+params,true);
    xmlhttp.send();
    //funclog("END", "fillReporte");
}
function printOverlay() {
    //funclog("INI","function printOverlay");
    let dra=ebyid("dialog_resultarea");
    let baseurl=false;
    let bases=document.getElementsByTagName("BASE");
    if (bases[0]) baseurl=bases[0].href;
    //funclog("BASE URL","'"+baseurl+"'");
    let pw=window.open();
    pw.document.open();
    pw.document.write('<html><head><meta charset="utf-8"><base href="'+baseurl+'" target="_blank"><link href="css/general.php" rel="stylesheet" type="text/css"></head><body class="blank fontMedium marginV7">'+dra.innerHTML+'</body></html>');
    pw.document.close();
    //pw.print();
    pw.onload=function(e) { funclog("ONLOAD","PRINT PAGE LOADED. READY TO PRINT AND CLOSE."); pw.print(); pw.close(); };
    //let scrp=pw.document.createElement('script');
    //scrp.type='text/javascript';
    //scrp.text='window.close();';
    //pw.document.body.appendChild(scrp);
    //funclog("END","function printOverlay");
}
function printContainer(id) {
    //funclog("INI", "function printContainer");
    let container=ebyid(id);
    if (container) {
        let baseurl=false;
        let bases=document.getElementsByTagName("BASE");
        if (bases[0]) baseurl=bases[0].href;
        //funclog("BASE URL","'"+baseurl+"'");
        let pw=window.open();
        pw.document.open();
        pw.document.write('<html><head><meta charset="utf-8"><base href="'+baseurl+'" target="_blank"><link href="css/general.php" rel="stylesheet" type="text/css"></head><body class="blank fontMedium marginV7">'+container.innerHTML+'</body></html>');
        pw.document.close();
        pw.onload=function(e) { funclog("ONLOAD","PRINT PAGE LOADED. READY TO PRINT AND CLOSE."); pw.print(); pw.close(); };
        //funclog("END","function printContainer");
    }
}
function printURL(url) {
    console.log("INI printURL: "+url);
    let pw=window.open();
    pw.document.open(url);
    pw.document.close();
    pw.onload=function(e) { console.log("ONLOAD PRINT PAGE LOADED. READY TO PRINT AND CLOSE."); pw.print(); pw.close(); };
}
function printPDF(url) {
    var iframe = this._printIframe;
    if (!this._printIframe) {
        iframe = this._printIframe = document.createElement("IFRAME");
        document.body.appendChild(iframe);
        iframe.style.display="none";
        iframe.onload=function() {
            setTimeout(function() {
                iframe.focus();
                iframe.contentWindow.print();
            },10);
        };
    }
    iframe.src=url;
}
function fillSelector(selectorname, params, title, dialogId) {
    if (!dialogId) dialogId="dialog";
    //funclog("INI", "fillSelector('"+selectorname+"', '"+params+"', '"+title+"', '"+dialogId+"')");
    if (!empty(params)) params="&"+params;
    let element = ebyid(dialogId+"_resultarea");
    let waitImage="icons/rollwait2";
    if (_win_) waitImage=_win_;
    element.innerHTML = "<div id='waitRoll' class='centered'><img src='imagenes/"+waitImage+".gif' class='hg100 padhtt'></div>";
    ebyid(dialogId+"_title").innerHTML = title;
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            element.innerHTML = xmlhttp.responseText;
            if (element.style && element.style.height) element.style.height=null;
            fillPaginationIndexes();
        }
    };
    
    xmlhttp.open("GET","selectores/"+selectorname+".php?tabla=1"+params,true);
    xmlhttp.send();
    //funclog("END", "fillSelector");
}
var preventOutScrolling=false;
var outScrollTimes=2;
function isScrolling(elem,isGoingDown) {
    if (elem&&elem.tagName) {
        let data=" ";
        data+=elem.tagName+"[";
        if (elem.id) {
            data+="id='"+elem.id+"',";
        } else if (elem.className) {
            data+="class='"+elem.className+"',";
        }
        data+="scrollTop="+elem.scrollTop+", ";
        data+="offsetHeight="+elem.offsetHeight+", ";
        data+="scrollHeight="+elem.scrollHeight+", ";
        const scrollBottom=elem.offsetHeight+elem.scrollTop+1;
        data+="scrollBottom="+scrollBottom+"]";
        const toTop = (elem.scrollTop<=0);
        const toBottom = (scrollBottom>=elem.scrollHeight);
        if (toTop && toBottom) {
            if (preventOutScrolling && preventOutScrolling.times<=0)
                preventOutScrolling=false;
            return isScrolling(elem.parentNode,isGoingDown);
        }
        if (isGoingDown && toBottom) {
            if (preventOutScrolling && preventOutScrolling.gotTo==="bottom") {
                preventOutScrolling.times--;
                //console.log("INI isScrollingDown"+data+"=toBottom "+preventOutScrolling.times);
                if (preventOutScrolling.times<=0) {
                    preventOutScrolling=false;
                    return isScrolling(elem.parentNode,isGoingDown);
                }
            } else {
                preventOutScrolling={gotTo:"bottom",times:outScrollTimes};
                //console.log("INI isScrollingDown"+data+"=toBottom "+preventOutScrolling.times);
            }
        } else if (!isGoingDown && toTop) {
            if (preventOutScrolling && preventOutScrolling.gotTo==="top") {
                preventOutScrolling.times--;
                //console.log("INI isScrollingUp"+data+"=toTop "+preventOutScrolling.times);
                if (preventOutScrolling.times<=0) {
                    preventOutScrolling=false;
                    return isScrolling(elem.parentNode,isGoingDown);
                }
            } else {
                preventOutScrolling={gotTo:"top",times:outScrollTimes+1};
                //console.log("INI isScrollingUp"+data+"=toTop "+preventOutScrolling.times);
            }
        } else {
            preventOutScrolling=false;
            //console.log("INI isScrolling"+(isGoingDown?"Down":"Up")+data+"=onTheWay");
        }
        return true;
    }
    return false;
}
function forceScroll(src,tgt) {
    tgt.scrollTop=src.scrollTop;
}
function getScrollParent(node) {
  if (node == null) return null;
  if (node.scrollHeight > node.clientHeight) return node;
  else return getScrollParent(node.parentNode);
}
var wheelLock=false;
function wheelPaginate(evt) {
    if (wheelLock) return;
    const currPage = +ebyid("pageno").value;
    const lastPage = +ebyid("lastpg").value;
    if (evt.deltaY>0 && currPage < lastPage) {
        if (!isScrolling(evt.target,true)) {
            evt.preventDefault();
            wheelLock=true;
            fillSelectorContents("next");
        }
    } else if (evt.deltaY<0 && currPage > 1) {
        if (!isScrolling(evt.target,false)) {
            evt.preventDefault();
            wheelLock=true;
            fillSelectorContents("prev");
        }
    }
}
function fillSelectorContents(action, callbackSuccessFunc) {
    if (!action) action="basic";
    //funclog("INI", "fillSelectorContents('"+action+"','"+callbackSuccessFunc+"')");
    let selectorname = ebyid("selectorname");
    let rowsperpage = ebyid("limit");
    let currpage = ebyid("pageno");
    let lastpage = ebyid("lastpg");
    let params="";
    if (rowsperpage) params = "&limit=" + rowsperpage.value;
    let filterBox = lbycn("filter_box");
    let exacto = "";
    //appendLog("PARAMS1 | "+params);
    //funclog("PARAMS",params);
    for(let i=0; i<filterBox.length; i++) {
        /* if (filterBox[i].selectedOptions) {
            for (opt in filterBox[i].selectedOptions) {
            }
        } else */
        if (filterBox[i].value) {
            params+='&param[]='+filterBox[i].name;
            let paramVal = filterBox[i].value;
            let filtro = filterBox[i].getAttribute("filter");
            if (!filtro) {
                if (action==="exacto") filtro="exacto";
            }
            let fbSfx = ""; // Sufijo (se agregan corchetes [] para valores multiples
            let fbAdd = ""; // Adicional (se agregan los valores multiples adicionales)
            
            //appendLog(" ###1"+filtro+" "+filterBox[i].name+" = "+filterBox[i].value);
            //funclog("FILTRO["+i+"]",filterBox[i].name+" = "+filterBox[i].value);
            if (filtro == "folio") { //  && filterBox[i]
                paramVal = ""+parseInt(paramVal.replace(/\D/g,''), 10);
            } else if (filtro == "exacto") {
                if (exacto.length>0) exacto += ",";
                exacto += filterBox[i].name;
            } else if (filtro == "fixSaldo") {
                let text = filterBox[i].options[filterBox[i].selectedIndex].text;
                //funclog("SALDO",text);
                if (text == "Saldo") {
                    paramVal="1";
                    fbSfx = "[]";
                    fbAdd = "&"+filterBox[i].name+"[]=2&"+filterBox[i].name+"[]=3&"+filterBox[i].name+"[]=4&"+filterBox[i].name+"[]=8&multiple="+filterBox[i].name;
                }
            } else if (filtro == "unidades") {
                if (exacto.length>0) exacto += ",";
                exacto += filterBox[i].name;
                if (paramVal.length>0 && paramVal!="10") {
                    paramVal = "!10";
                }
            }
            params+="&"+filterBox[i].name+fbSfx+"="+paramVal+fbAdd;
        } else {
            //appendLog(" ###0"+" "+filterBox[i].name);
            //funclog("FILTRO["+i+"]",filterBox[i].name+" (SIN VALOR)");
        }
    }
    // params4log = params.replace(/\&/g, "&amp;");
    // params4log = params4log.replace(/¶/g, "&amp;para");
    // params4log = params4log.replace("&", "#");
    // appendLog("PARAMS2 | "+params4log);
    if (action == "first") {
        params+="&pageno=1";
    } else if (action == "prev") {
        let prevpg = parseInt(currpage.value) - 1;
        params+="&pageno="+prevpg;
    } else if (action == "next") {
        let nextpg = parseInt(currpage.value) + 1;
        params+="&pageno="+nextpg;
    } else if (action == "last") {
        params+="&pageno="+parseInt(lastpage.value);
    }
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            //funclog("ONREADYSTATECHANGE","RESULT RECEIVED");
            const dtb=ebyid("dialog_tbody");
            if (dtb) {
                dtb.innerHTML = xmlhttp.responseText;
                clearDialogBoxHeight();
                adjustDialogBoxHeight();
                fillPaginationIndexes();
                if (callbackSuccessFunc) callbackSuccessFunc();
                adjustDialogBoxHeight(true);
            } else funclog("ERROR","fillSelectorContents NOT FOUND DIALOG TBODY FOR RESPONSE:\n"+xmlhttp.responseText);
        }
    };
    //appendLog("AJAX | "+"selectores/"+selectorname.value+".php?datos=1&exacto="+exacto+params.replace(/\&/g, "&amp;"));
    params="&exacto="+exacto+params;
    xmlhttp.open("GET","selectores/"+selectorname.value+".php?datos=1"+params,true);
    xmlhttp.send();
    //funclog("END", "fillSelectorContents. PARAMS="+params.replace(/\&/g, "&amp;"));
}
function setPageNavBlock(pgNo,lsPg) {
    let pnb=ebyid("pageNavBlock");
    if (pnb) {
        ebyid("paginationIndexes").textContent=" "+pgNo+"/"+lsPg+" ";
    } else {
        pnb=ecrea({eName:"DIV",id:"pageNavBlock",className:"centered marbtm5 relative",eChilds:[
            {eName:"INPUT",type:"button",id:"navToFirst",className:"navOverlayButton",value:"<<",
                onclick:function(){fillSelectorContents("first");}},
            {eName:"INPUT",type:"button",id:"navToPrevious",className:"navOverlayButton",value:" < ",
                onclick:function(){fillSelectorContents("prev");}},
            {eName:"SPAN",id:"paginationIndexes",className:"fontPageFormat",eText:" "+pgNo+"/"+lsPg+" "},
            {eName:"INPUT",type:"button",id:"navToNext",className:"navOverlayButton",value:" > ",
                onclick:function(){fillSelectorContents("next");}},
            {eName:"INPUT",type:"button",id:"navToLast",className:"navOverlayButton",value:">>",
                onclick:function(){fillSelectorContents("last");}}]});
        const dra=ebyid("dialog_resultarea");
        const cba=ebyid("closeButtonArea");
        cba.insertBefore(pnb,ebyid("closeButton"));
        cladd(dra,"twoCloseRows");
        cladd(cba,"twoCloseRows");
        const ovy=ebyid("overlay");
        if (ovy.callOnClose) ovy.oldCallOnClose=ovy.callOnClose;
        ovy.callOnClose=function() {
            clrem(dra,"twoCloseRows");
            clrem(cba,"twoCloseRows");
            if (ovy.oldCallOnClose) {
                ovy.oldCallOnClose();
                delete ovy.oldCallOnClose;
            }
            delete ovy.callOnClose;
        };
    }
}
function refreshFooter() {
    //funclog("INI","refreshFooter");
    let piePagina = ebyid("pie_pagina");
    if (piePagina) {
        let xmlhttp = ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                piePagina.innerHTML = xmlhttp.responseText;
                //funclog("SUCCESS","refreshFooter "+(xmlhttp.responseText.length));
            }
        };
        let rqst = "bloques/piePagina.php?refreshFooter=true";
        xmlhttp.open("GET",rqst,true);
        xmlhttp.send();
        //funclog("SENT","refreshFooter: "+rqst+"\n");
    }
}
function sendRefresh(callbackFunc) {
    //funclog("INI","sendRefresh");
    //document.cookie=cookieText;
    let xmlhttp=ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState==4) {
            if (callbackFunc) callbackFunc();
            else {
                //console.log(xmlhttp.status);
                //console.log(xmlhttp.responseText);
                location.reload(true);
            }
        }
    };
    let url="consultas/terminal.php";
    xmlhttp.open("GET",url);
    //xmlhttp.setRequestHeader("Cookie", cookieText);
    xmlhttp.send();
}
function basicDialogString(message) {
    return message;
}
function restoreOverlayElements() {
    let dbox = ebyid("dialogbox");
    let wbox = ebyid("wheelbox");
    if (dbox && dbox.classList.contains("hidden")) clrem(dbox,"hidden");
    if (wbox && !wbox.classList.contains("hidden")) cladd(wbox,"hidden");
//    if (dbox.style.visibility === "hidden") dbox.style.visibility = "visible";
//    if (wbox.style.visibility !== "hidden") wbox.style.visibility = "hidden";
    let cldra = ebyid("dialog_resultarea").classList;
    for (let i=cldra.length; i>0; i--) cldra.remove(cldra[0]); // Delete each classList item
    let closeBtn = ebyid("closeButton");
    let saveBtn = ebyid("accept_overlay");
    if (closeBtn) {
        closeBtn.nofocus=false;
        closeBtn.value="Cerrar";
        if (closeBtn.restoreOnClose) {
            closeBtn.restoreOnClose();
            delete closeBtn.restoreOnClose;
        }
    }
    ekil(saveBtn);
}
function focusOnAutoFocus(parentElement) {
    console.log("INI function focusOnAutoFocus");
    if (!parentElement) parentElement=document;
    elemList=parentElement.querySelectorAll("[autofocus]");
    if (elemList && elemList.length>0) {
        const elem = elemList[elemList.length-1];
        repeatFocus(elem,10);
        console.log("END function focusOnAutoFocus: ",elem);
        return true;
    }
    console.log("END function focusOnAutoFocus: NONE");
    return false;
}
function repeatFocus(elem,times) {
    if (times<=0) return;
    elem.focus();
    console.log("Repeat Focus "+times, document.activeElement, elem);
    if (elem!==document.activeElement) setTimeout(repeatFocus, 20, elem, times-1);
    else console.log("Active Elem = ",document.activeElement);
}
/* **
 * Se muestra ventana con titulo <title> y contenido <msg> y se solicita presionar botón Validar
 * Si se presiona Validar, se ejecuta callback y se evalua mensaje de respuesta
 * Si devuelve FALSO se mantiene abierta ventana de Validacion
 * Si devuelve VERDADERO se cierra ventana de Validacion
 * Si devuelve texto se cierra ventana de Validacion y se muestra ventana de Error con <texto>
*/
function overlayValidation(msg,title,callbackValidFunc,dialogId,preCheckFunc) {
    if (!preCheckFunc || preCheckFunc()) {
        _overlayCheck("valid",msg,title,dialogId,()=>{
            const retCF=callbackValidFunc();
            if(retCF) {
                overlayClose(ebyid("accept_overlay"));
                // ["string","array","object"].includes(typeof retCF)
                if (((typeof retCF==="string"||typeof retCF==="array") && retCF.length>0)||isElemObj(retCF)) overlayMessage(retCF,"ERROR");
            }
        });
    }
}
/* **
 * Se muestra ventana con titulo <title> y contenido <msg> y se solicita presionar botón Confirmar
 * Si se presiona Confirmar, se cierra la ventana de confirmacion y se ejecuta callback
*/
function overlayConfirmation(msg, title, callbackConfirmFunc, dialogId, preCheckFunc) {
    _overlayCheck("confirm",msg,title,dialogId,()=>{if(!preCheckFunc||preCheckFunc()){overlayClose(ebyid("accept_overlay"));callbackConfirmFunc();}});
}
function _overlayCheck(name,msg,title,dialogId,saveFunc) {
    if (!title) title=(name.toUpperCase()+"ACI&Oacute;N"); if (!dialogId) dialogId="dialog";
    const capName=name.charAt(0).toUpperCase()+name.slice(1);
    //funclog("INI","overlay"+capName+"ation(<msg>,'"+title+"',<callback>,'"+dialogId+"')");
    const element=ebyid(dialogId+"_resultarea"); ekfil(element);
    if (element && element.style && element.style.height) element.style.height=null;
    const ttElement=ebyid(dialogId+"_title"); ekfil(ttElement);
    appendMessageElement(element, msg); appendMessageElement(ttElement, title);
    setTimeout(function(theName, theSaveFunc) {
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (_dragElement) {
            if (_dragElement.style.removeProperty) _dragElement.style.removeProperty("height");
            else _dragElement.style.removeAttribute("height");
            _dragElement.style.top='0px'; _dragElement.style.left='0px';
            _dragElement.focus(); _dragElement.blur();
            //funclog("TAG", "DRAG "+theName.toUpperCase()+"ACTION ELEMENT FOCUSED AND BLURRED");
        }
        const buttonBox = ebyid("closeButtonArea");
        let saveBtn = ebyid("accept_overlay");
        const closeBtn = ebyid("closeButton");
        if (closeBtn) closeBtn.value="Cancelar";
        if (!saveBtn) {
            saveBtn = ecrea({eName:"INPUT",type:"button",id:"accept_overlay",value:theName+"ar",className:"marginV1",onclick:theSaveFunc});
            try { buttonBox.insertBefore(saveBtn,closeBtn.nextSibling);
            } catch (e) { appendLog("ERROR overlay"+theName+"ation insertBefore: "+e.message); }
        }
        applyVisibility("overlay");
    }, 50, capName, saveFunc);
    //funclog("END", "overlay"+capName);
}
function overlayMessage(msg, title, dialogId) {
    if (!title) title="AVISO"; if (!dialogId) dialogId="dialog";
    //funclog("INI", "overlayMessage(<msg>,'"+title+"','"+dialogId+"')");
    // ini close if open
    const ovy = ebyid ("overlay");
    if (ovy && ovy.style && ovy.style.visibility!=="hidden") { // overlayClose(); 
        ovy.style.visibility = "hidden";   //
        hidePaginationButtons();           //
        restoreOverlayElements();          //
        clearTimeout(timeOutOverlay);      //
        if (ovy.myDblClickEvent)           //
            ovy.removeEventListener('dblclick', ovy.myDblClickEvent); //
    }                                      //
    // end close if open
    const element = ebyid(dialogId+"_resultarea");
    ekfil(element);
    if (element && element.style && element.style.height) element.style.height=null;
    const ttlElem = ebyid(dialogId+"_title");
    ekfil(ttlElem);
    appendMessageElement(element,msg);
    appendMessageElement(ttlElem,title);
    setTimeout(function() {
        funclog("BEGIN","OVERLAY TIMEOUT BLOCK");
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (_dragElement) {
            if (_dragElement.style.removeProperty) _dragElement.style.removeProperty("height");
            else _dragElement.style.removeAttribute("height");
            _dragElement.style.top='0px';
            _dragElement.style.left='0px';
            _dragElement.focus();
            _dragElement.blur();
            //funclog("TAG", "DRAG MESSAGE ELEMENT FOCUSED AND BLURRED");
        }
        funclog("TAG","OVERLAY TIMEOUT BLOCK");
        applyVisibility("overlay");
        funclog("FINISH","OVERLAY TIMEOUT BLOCK");
    }, 50);
    //funclog("END", "overlayMessage");
}
function getParagraphObject(text, extraClassName, replaceClassName) {
    if (Array.isArray(text)) {
        const retarr=[];
        text.forEach(function(subtxt,idx) {
            let subECN="";
            if (Array.isArray(extraClassName)) {
                if (extraClassName.length>idx) subECN=extraClassName[idx];
                else subECN=false;
            } else subECN=extraClassName;
            let subRCN="";
            if (Array.isArray(replaceClassName)) {
                if (replaceClassName.length>idx) subRCN=replaceClassName[idx];
                else subRCN=false;
            } else subRCN=replaceClassName;
            retarr.push(getParagraphObject(subtxt, subECN, subRCN));
        });
        return retarr;
    }
    let pgObj=null;
    if (isElemObj(text)) pgObj=text;
    else pgObj = {eName:"P",eText:text};
    if (replaceClassName) {
        //if (extraClassName && extraClassName.length>0)
            pgObj.className=extraClassName;
    } else {
        pgObj.className="padhtt";
        if (extraClassName && extraClassName.length>0) pgObj.className+=" "+extraClassName;
    }
    return pgObj;
/*
"boldValue"     = "font-weight: bold;"
"cancelLabel"   = "color: darkred;"
"bgred2"        = "background-color: rgba(255, 100, 100, 0.2);"
"bgredvip01"    = "background-color: rgba(255,   0,   0, 0.01) !important;"
"errorLabel"    = "background-color: rgba(255,0,0,0.01) !important; color: darkred; font-weight: bold; \
                   margin: 0 auto; text-align: center; align: center;"
"centered"      = "margin: 0 auto; text-align: center; align: center;"
"textcenter"    = "textalign: center;"
"vAlignCenter"  = "vertical-align: middle;"
"marginH5"      = "margin-top: 5px; margin-bottom: 5px;"
"marT10i"       = "margin-top: 10px !important;"
"padhtt"        = "padding-top: 20px; padding-bottom: 10px;"
"mbpi"          = "margin-top: 12px !important; margin-bottom: 12px !important;"
*/
}

function overlayWheel() {
    //funclog("INI","overlayWheel");
    let element = ebyid("overlay");
    //if (element) funclog("VISIBILITY",element.style.visibility);
    //else funclog("ERROR", "UNKNOWN overlay");
    if (element && element.style.visibility === "hidden") {
        let dbox = ebyid("dialogbox");
        let wbox = ebyid("wheelbox");
        //if (dbox && dbox.style) dbox.removeAttribute("style");
        //if (wbox && wbox.style) wbox.removeAttribute("style");
        if (dbox && !dbox.classList.contains("hidden")) cladd(dbox,"hidden");
        if (wbox && wbox.classList.contains("hidden")) clrem(wbox,"hidden");
        let ovyHgt = element.offsetHeight;
        let wboxHgt = wbox.offsetHeight;
        let newTop = (ovyHgt-wboxHgt)/2;
        if (newTop>0) {
            wbox.style.paddingTop=newTop+"px";
        } else {
            wbox.style.paddingTop="0px";
            let imag = wbox.firstElementChild;
            if (imag.tagName==="IMG") {
                imag.style.width=ovyHgt+"px";
                imag.style.height=ovyHgt+"px";
            }
        }
        toggleVisibility("overlay");
    }
    //funclog("END","overlayWheel");
}
function isShownWheelbox() {
    const ovy = ebyid ("overlay");
    if (ovy && (ovy.style.visibility==="hidden" || clhas(ovy,"hidden"))) return false;
    let wbox = ebyid("wheelbox");
    return wbox && wbox.style.visibility!=="hidden" && !clhas(wbox,"hidden");
}
function overlay(showSelector, title, referenceId, params, dialogId) {
    if (!params) params="";
    if (!title) title="AVISO";
    if (!dialogId) dialogId="dialog";
    //funclog("INI", "overlay('"+showSelector+"','"+title+"','"+referenceId+"','"+params+"','"+dialogId+"')");
    let element = ebyid(dialogId+"_resultarea");
    while(element.firstChild) element.removeChild(element.firstChild);
    let closeArea = ebyid("closeButtonArea");
    ekfil(closeArea,[ebyid("closeButton")]);
//    for(let elemCA=closeArea.firstChild;elemCA;elemCA=elemCA.nextElementSibling) {
        //if (elemCA.id==="closeButton") //elemCA=elemCA.nextElementSibling;
        //else {
//        if (elemCA.id!=="closeButton") {
//            let prvElem=false;
//            if (elemCA.previousElementSibling) prvElem=elemCA.previousElementSibling;
//            closeArea.removeChild(elemCA);
//            if (prvElem) elemCA=prvElem;
//            else elemCA=closeArea.firstChild;
//        }
//    }
    let dbox = ebyid("dialogbox");
    dbox.style.removeProperty("height");
    if (element.style && element.style.height) element.style.height=null;
    if (typeof showSelector === 'undefined') {
    } else {
        fillSelector(showSelector, params, title, dialogId);
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (_dragElement) {
            if (_dragElement.style.removeProperty) _dragElement.style.removeProperty("height");
            else _dragElement.style.removeAttribute("height");
            _dragElement.style.top='0px';
            _dragElement.style.left='0px';
            _dragElement.focus();
            _dragElement.blur();
            //funclog("TAG", "DRAG OVERLAY ELEMENT FOCUSED AND BLURRED");
        }
    }
    if (typeof referenceId !== 'undefined' && !empty(referenceId)) {
        let reference = ebyid(referenceId);
        if (dbox) dbox.style.marginTop = "20px";
        if (reference && dbox) {
            let bodyRect = document.body.getBoundingClientRect(),
            refRect = reference.getBoundingClientRect(),
            offset   = refRect.top - bodyRect.top;
            let mtop = offset + "px";
            dbox.style.marginTop = mtop;
        }
    }
    toggleVisibility("overlay");
    //funclog("END", "overlay");
}
function overlayClose(btn) {
    let element = ebyid ("overlay");
    funclog("INI", "overlayClose() "+(element?element.style.visibility:"NOT FOUND"));
    if (element && element.style.visibility !== "hidden") {
        if (btn && btn.callOnClose) {
            funclog("FOUND",btn.id+".callOnClose overlayClose");
            btn.callOnClose();
            if (!btn.keepCallOnClose) delete btn.callOnClose;
        }
        toggleVisibility("overlay");
    }
    funclog("END", "overlayClose()");
}
function applyVisibility(elementId, depth) {
    let element = ebyid (elementId);
    if (element) {
        //funclog("INI", "applyVisibility('"+elementId+"','"+depth+"') "+element.style.visibility);
        if (element.style.visibility === "hidden") toggleVisibility(elementId);
        else {
            let dbox = ebyid("dialogbox");
            let wbox = ebyid("wheelbox");
            if (dbox && dbox.classList.contains("hidden")) clrem(dbox,"hidden");
            if (wbox && !wbox.classList.contains("hidden")) cladd(wbox,"hidden");
        }
        //funclog("END", "applyVisibility");
    } else {
        //funclog("ONE", "applyVisibility('"+elementId+"','"+depth+"') NOT FOUND!");
    }
}
function adjustDialogBoxHeight(onlySet) {
    //funclog("INI", "adjustDialogBoxHeight");
    const dbox = ebyid("dialogbox");
    const drar = ebyid("dialog_resultarea");
    cladd(drar,"calc");
    const dblen = dbox.offsetHeight;
    const drlen = drar.offsetHeight;
    const calcRLen = dblen - 55;
    let msg="";
    if (drlen>0) {
        if (calcRLen < drlen) {
            dbox.style.height="calc(100% - 44px)";
            msg=" DBOX H="+dblen+" (calc="+calcRLen+"), DRSA H="+drlen+": FIX HEIGHT";
            clrem(drar,"calc");
        } else if (!onlySet) {
            clearDialogBoxHeight();
            msg=" DBOX H="+dblen+" (calc="+calcRLen+"), DRSA H="+drlen+": FREE HEIGHT";
        }
    }
    //funclog("END","adjustDialogBoxHeight"+msg);
}
function clearDialogBoxHeight() {
    const dbox = ebyid("dialogbox");
    if (dbox.style.removeProperty) dbox.style.removeProperty("height");
    else dbox.style.removeAttribute("height");
    const drar = ebyid("dialog_resultarea");
    cladd(drar,"calc");
}
function toggleVisibility(elementId, depth) {
    if (typeof depth === 'undefined') depth=0;
    //funclog("INI", "toggleVisibility('"+elementId+"','"+depth+"')");
    let element = ebyid (elementId);
    if (element) {
        element.style.visibility = (element.style.visibility !== "hidden") ? "hidden" : "visible";
        if (elementId === "overlay") {
            if (element.style.visibility === "hidden") {
                hidePaginationButtons();
                restoreOverlayElements();
                clearTimeout(timeOutOverlay);
                if (element.myDblClickEvent)
                    element.removeEventListener('dblclick', element.myDblClickEvent);
                if (ebyid("area_acceso")) {
                    let usrfld = ebyid("username");
                    if(usrfld) setTimeout(function() { 
                        usrfld.focus();
                        //funclog("TAG", "FOCUS on USER FIELD!");
                    }, 180);
                } else if (element.callOnClose) {
                    //funclog("STEP","callOnClose toggleVisibility");
                    setTimeout(function() {
                        //funclog("TIME","callOnClose toggleVisibility");
                        if (element.callOnClose) {
                            //funclog("FOUND","callOnClose toggleVisibility");
                            element.callOnClose();
                            if (!element.keepCallOnClose) delete element.callOnClose;
                        }
                        //funclog("OUT","callOnClose toggleVisibility");
                    }, 180);
                }
            } else {
                clearTimeout(timeOutOverlay);
                cladd(element,"no_selection");
                element.myDblClickEvent = function(event) {
                    if(!event) event = window.event;
                    console.log("dblclk",event?event.target:"");
                    if(event && event.target.id=='overlay') overlay();
                    if (element.removeAllRanges)
                        element.removeAllRanges();
                    return false;
                };
                element.myDblClickEventHandler = function () {
                    if (element.addEventListener) {
                        element.addEventListener('dblclick', element.myDblClickEvent);
                    } else {
                        element.attachEvent('ondblclick', element.myDblClickEvent);
                    }
                };
                timeOutOverlay = setTimeout(element.myDblClickEventHandler, 500);
                const dbox = ebyid("dialogbox");
                if (dbox && !dbox.classList.contains("hidden") && !focusOnAutoFocus(element)) {
                    setTimeout(function() {
                        //funclog("TIME","AutomaticFocus toggleVisibility");
                        // ToDo: Verificar si se realiza un focus previo
                        // Idea1: Usar Autofocus en otros componentes, buscar si existe autofocus
                        // Idea2: Donde se haga focus en un componente, marcar estos botones:
                        //        acceptBtn.nofocus=true; closeBtn.nofocus=true
                        let acceptBtn = ebyid('accept_overlay');
                        if (acceptBtn && !acceptBtn.nofocus) {
                            repeatFocus(acceptBtn,3);
                            //acceptBtn.focus();
                            //funclog("TAG", "Focus on Accept button");
                        } else {
                            let closeBtn = ebyid('closeButton');
                            if (closeBtn && !closeBtn.nofocus) {
                                repeatFocus(closeBtn,3);
                                //closeBtn.focus();
                                //funclog("TAG", "Focus on Close button");
                            } else {
                                //funclog("TAG", "Accept or Close button NOT found!");
                            }
                        }
                        //funclog("OUT","AutomaticFocus toggleVisibility");
                    }, 180);
                }
                adjustDialogBoxHeight();
            }
        }
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | toggleVisibility "+newDepth);
        setTimeout(function() {
            toggleVisibility(elementId, newDepth);
        }, 100);
    } else {
        appendLog("ERROR | toggleVisibility missing: element");
    }
    //funclog("END", "toggleVisibility");
}
//function setClass(elementId, classname, activate=true) {
function setClass(elementId, classname, activate) {
    if (typeof activate === 'undefined') activate=true;
    let element = ebyid(elementId);
    if (element) {
        let contained = element.classList.contains(classname);
        if (contained && !activate)
            clrem(element,classname);
        else if (activate && !contained)
            cladd(element,classname);
    }
}
function toggleClass(elementId, classname) {
    if (elementId.constructor === Array) {
        for (let i=0; i<elementId.length; i++) toggleClass(elementId[i], classname);
        return;
    }
    if (elementId.classList) {
        elementId.classList.toggle(classname);
    } else {
        let element = ebyid(elementId);
        if (element) {
            element.classList.toggle(classname);
        } else { }
    }
}
function toggleClassRoll(elementId, classArray) {
    let element = ebyid(elementId);
    if (element) {
        for (let i=0; i<classArray.length; i++) {
            if (element.classList.contains(classArray[i])) {
                clrem(element,classArray[i]);
                if ((i+1)<classArray.length)
                    cladd(element,classArray[i+1]);
                return;
            }
        }
        cladd(element,classArray[0]);
    }
}
function toggleClassByClass(browseClassname, toggleClassname) {
    if (browseClassname.constructor !== Array) browseClassname = [browseClassname];
    let elements = [];
    for (let i=0; i<browseClassname.length; i++) {
        let classnameElems = lbycn(browseClassname[i]);
        for (n=0; n<classnameElems.length; n++) {
            if (elements.indexOf(classnameElems[n]) === -1) elements.push(classnameElems[n]);
        }
    }
    if (toggleClassname.constructor !== Array)  toggleClassname = [toggleClassname];
    for (let i=0; i<elements.length; i++) {
        let found=false;
        let classRollLen = toggleClassname.length;
        for (let j=0; j<classRollLen; j++) {
            if (elements[i].classList.contains(toggleClassname[j])) {
                clrem(elements[i],toggleClassname[j]);
                if ((j+1)<classRollLen)
                    cladd(elements[i],toggleClassname[j+1]);
                else if (classRollLen>1)
                    cladd(elements[i],toggleClassname[0]);
                found=true;
                break;
            }
        }
        if (!found) cladd(elements[i],toggleClassname[0]);
    }
}
function callByClass(classname, callbackFunc) {
    [].forEach.call(lbycn(classname),callbackFunc);
}
function chooseByClass(classname, successFunc, onTrueFunc, onFalseFunc) {
    [].forEach.call(lbycn(classname),function(element,index,array) {
        if (successFunc) {
            if (successFunc(element,index,array)) {
                if (onTrueFunc) onTrueFunc(element,index,array);
            } else if (onFalseFunc) onFalseFunc(element,index,array);
        }
    });
}
function getAttribute(elementId, elementAttribute) {
    let element = ebyid ( elementId);
    if (element && element[elementAttribute]) return element[elementAttribute];
    return false;
}
function changeAttribute(elementId, elementAttribute, attributeValue, depth) {
    if (typeof depth === 'undefined') depth=0;
    let element = ebyid ( elementId);
    if (element) {
        if (element[elementAttribute]!=attributeValue)
            element[elementAttribute]=attributeValue;
        return element;
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | changeAttribute "+newDepth);
        setTimeout(function() {
            changeAttribute(elementId, elementAttribute, attributeValue, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | changeAttribute missing: element");
    }
    return false;
}
function changeAttributeByClass(classname, elementAttribute, attributeValue, depth) {
    //if (typeof depth === 'undefined') depth=0;
    let elements = lbycn(classname);
    for (let i=0; i<elements.length; i++) {
        elements[i][elementAttribute]=attributeValue;
    }
}
function backupAttribute(elementId, sourceAttribute, targetAttribute, depth) {
    if (typeof depth === 'undefined') depth=0;
    let element = ebyid ( elementId);
    if (element) {
        element[targetAttribute]=element[sourceAttribute];
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | backupAttribute "+newDepth);
        setTimeout(function() {
            backupAttribute(elementId, sourceAttribute, targetAttribute, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | backupAttribute missing: element");
    }
    return false;
}
function backupAttributeByClass(classname, sourceAttribute, targetAttribute, depth) {
    //if (typeof depth === 'undefined') depth=0;
    let elements = lbycn(classname);
    for (let i=0; i<elements.length; i++) {
        elements[i][targetAttribute]=elements[i][sourceAttribute];
    }
}
function removeElementsByClass(classname, depth) {
    //if (typeof depth === 'undefined') depth=0;
    let elements = lbycn(classname);
    while(elements.length > 0) {
        ekil(elements[0]);
    }
}
function getValueTxt(idElem, emptyVal, objVal, falseVal) {
    if (!emptyVal) emptyVal="";
    if (!objVal) objVal=emptyVal; // "";
    if (!falseVal) falseVal=objVal; // "";
    let obj = ebyid(idElem);
    if (obj) {
        if (obj.value) {
            if (obj.value.length > 0) return obj.value;
            else return emptyVal;
        } else return objVal
    } else return falseVal;
}
function fillValue(elementId, elementValue, depth) {
    if (typeof depth === 'undefined') depth=0;
    if (Array.isArray(elementId)) {
        elementId.forEach(function(subelemId) { fillValue(subelemId, elementValue, depth); });
        return false;
    }
    //funclog("INI","fillValue "+elementId+", "+elementValue+", "+depth+"\n");
    let element = ebyid(elementId);
    if (element) {
        if (element.value!=elementValue) {
            element.value=elementValue;
//            if (element.type=="hidden")
//                element.onchange();
        }
        return element;
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | fillValue "+newDepth);
        setTimeout(function() {
            fillValue(elementId, elementValue, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | fillValue missing: element");
    }
    return false;
}
function toggleValue(elementId, elementValues, depth) {
    if (typeof depth === 'undefined') depth=0;
    if (!Array.isArray(elementValues)) elementValues = [elementValues];
    else if (elementValues.length==0) return null;
    if (Array.isArray(elementId)) {
        let elemArr=[];
        elementId.forEach(function(subelemId) { elemArr.push(toggleValue(subelemId, elementValues, depth)); });
        return elemArr;
    }
    let element=isElement(elementId)?elementId:ebyid(elementId);
    if (element) {
        if (elementValues.length==1) {
            if (element.value===elementValues[0]) element.value="";
            else element.value=elementValues[0];
        } else {
            let idx=elementValues.indexOf(element.value)+1;
            if (idx>=elementValues.length) idx=0;
            element.value=elementValues[idx];
        }
    }
    return element;
}
function appendMessageElement(element, message) {
    funclog("INI","appendMessageElement");
    if (!element||!message) {
        //funclog("END","appendMessageElement invalid args");
        return false;
    }
    if (message.length>400) funclog("MSG: \'"+message.substr(0,200)+"...\n"+"..."+message.substr(-200)+"\'");
    else funclog("MSG: \'"+message+"\'");
    if (Array.isArray(message)) {
        //funclog("Array");
        message.forEach(function(submessage) { appendMessageElement(element,submessage); });
    } else if (typeof message==="string") {
        const strdiv=ecrea({eName:"DIV",eText:"."});
        //console.log("DIV: ",strdiv,"\nMESSAGE: ",message);
        // let strdiv=ecrea({eText:message}); // solo texto
        element.appendChild(strdiv);
        try {
            strdiv.innerHTML=message;             // texto y html
            console.log("DONE: ",strdiv);
        } catch (exc) {
            strdiv.textContent=message;
            console.log("EXCEPTION: ", exc);
        }
    } else if (isElement(message)) {
        element.appendChild(message);
        //funclog("Element",message);
    } else if (isElemObj(message)) {
        element.appendChild(ecrea(message));
    }//else funclog("Unknown "+(typeof message),message);
}
function isTextContent(str) {
    const cont=ecrea({eName:"DIV"});
    cont.innerHTML=str;
    return cont.textContent===str;
}
function addContent(elementId, contentElem, depth, maxdepth) {
    if (typeof depth === 'undefined') depth=0;
    if (typeof maxdepth === 'undefined') maxdepth=10;
    funclog("INI addContent "+elementId+", ",contentElem,", "+depth+", "+maxdepth+"\n");
    let element = ebyid(elementId);
    if (element && contentElem) {
        if (typeof contentElem==="string") element.textContent+=contentElem;
        else if (isElemObj(contentElem)) element.appendChild(ecrea(contentElem));
        else if (isElement(contentElem)) element.appendChild(contentElem);
        //else return false;
        return element;
    } else if (depth < maxdepth) {
        let newDepth=depth+1;
        appendLog("RETRY | addContent "+newDepth);
        setTimeout(function() {
            addContent(elementId, contentElem, newDepth, maxdepth);
        }, 20);
    } else {
        appendLog("ERROR | addContent missing:"+(element?"":" element")+(contentElem?"":" contentElem"));
    }
    return false;
}
function fillContent(elementId, contentElem, depth, maxdepth) {
    if (typeof depth === 'undefined') depth=0;
    if (typeof maxdepth === 'undefined') maxdepth=10;
    funclog("INI fillContent "+elementId+", ",contentElem,", "+depth+", "+maxdepth+"\n");
    let element = ebyid(elementId);
    if (element && contentElem) {
        if (typeof contentElem==="string") element.textContent=contentElem;
        else if (isElemObj(contentElem)) {
            ekfil(element);
            element.appendChild(ecrea(contentElem));
        } else if (isElement(contentElem)) {
            ekfil(element);
            element.appendChild(contentElem);
        } // else if isArray(contentElem) fillContent(first item) loop:addContent(next items)
        // else return false;
        return element;
    } else if (depth < maxdepth) {
        let newDepth=depth+1;
        appendLog("RETRY | fillContent "+newDepth);
        setTimeout(function() {
            fillContent(elementId, contentElem, newDepth, maxdepth);
        }, 20);
    } else {
        appendLog("ERROR | fillContent missing:"+(element?"":" element")+(contentElem?"":" contentElem"));
    }
    return false;
}
function fillInnerHtmlWithValue(elementId, elementIdWithValue, depth) {
    if (typeof depth === 'undefined') depth=0;
    funclog("INI fillInnerHtmlWithValue "+elementId+", "+elementIdWithValue+", "+depth+"\n");
    let elementWithValue = ebyid (elementIdWithValue);
    if (elementWithValue)
        fillInnerHtml(elementId, elementWithValue.value, depth);
    else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | fillInnerHtmlWithValue "+newDepth);
        setTimeout(function() {
            fillInnerHtmlWithValue(elementId, elementIdWithValue, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | fillInnerHtmlWithValue missing: elementWithValue");
    }
}
function fillInnerHtml(elementId, elementText, depth) {
    if (typeof depth === 'undefined') depth=0;
    let element = ebyid (elementId);
    if (element) {
        element.innerHTML=elementText;
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | fillInnerHtml "+newDepth);
        setTimeout(function() {
            fillInnerHtml(elementId, elementText, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | fillInnerHtml missing: element");
    }
}
function appendInnerHtml(elementId, elementText, depth) {
    if (typeof depth === 'undefined') depth=0;
    let element = ebyid (elementId);
    if (element) {
        element.innerHTML+=elementText;
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | appendInnerHtml "+newDepth);
        setTimeout(function() {
            appendInnerHtml(elementId, elementText, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | appendInnerHtml missing: element");
    }
}
function fillLog(text,lvl) {
    if (doShowLogs) { // doShowLogs=true|>0
        if (arguments.length>1) {
            if(!lvl) return; // falsy argument not undefined
            if (typeof lvl === 'number' && typeof doShowLogs === 'number' && doShowLogs<lvl) return; // number out of range
            // all other cases are valid
        }
        addLog(text,true,true);
    }
}
function appendLog(text,lvl) {
    if (doShowLogs) { // doShowLogs=true|>0
        if (arguments.length>1) {
            if(!lvl) return; // falsy argument not undefined
            if (typeof lvl === 'number' && typeof doShowLogs === 'number' && doShowLogs<lvl) return; // number out of range
            // all other cases are valid
        }
        addLog(text,true,false);
    }
}
function addLog(text,nwNoLn,cleanLog) {
    if (!myLog) myLog=ebyid("mylog");
    //textObj={eText:text};
    //textElem=ecrea(textObj);
    //myLog.appendChild(textElem);
    if (cleanLog) ekfil(myLog);
    if (nwNoLn) numLog++;
    myLog.appendChild(document.createTextNode((nwNoLn?numLog+") ":"")+text+(nwNoLn?"\n":"")));
    //conlog(myLog);
}
function conlog(...myArgs) { // txt,lvl
    if (doShowLogs && myArgs.length>0) { // doShowLogs=true|>0
        if (myArgs.length>1) {
            const lvl=myArgs[myArgs.length -1];
            if(!lvl) return; // falsy argument not undefined
            if (typeof lvl === 'number') {
                if (typeof doShowLogs === 'number' && doShowLogs<lvl) return; // number out of range
                myArgs.pop(); // 
            }
            // all other cases are valid
        }
        console.log(...myArgs);
    }
}
function funclog(pref, txt, lvl) {
    if (doShowFuncLogs) { // doShowLogs=true|>0
        if (arguments.length>2) {
            if(!lvl) return; // falsy argument not undefined
            if (typeof value === 'number' && typeof doShowFuncLogs === 'number' && doShowFuncLogs<lvl) return; // number out of range
            // all other cases are valid
        }
        if (arguments.length==1) {
            txt=pref;
            pref="FNCL";
        }
        try {
            console.log(pref+" | "+txt);
        } catch (se) {
            console.log("PREF: ",pref);
            console.log("TEXT: ",txt);
            console.log("EXCEPTION: ",se);
        }
    }
}
function elemlog(element, showVarnames, isRecursive, ident) {
    if (!ident) ident="";
    if (doShowElemLogs) {
        let varText = "";
        for(let i=0; i<showVarnames.length; i++) {
            let value = "";
            if (element[showVarnames[i]]) value = element[showVarnames[i]];
            else if (element.hasAttribute(showVarnames[i])) value = element.getAttribute(showVarnames[i]);
//            if (value && value.length>0) {
                if (varText.length>0) varText += ", ";
                varText += showVarnames[i]+": '"+value+"'";
  //          }
        }
        console.log(ident+" ELEM "+varText);
        if (isRecursive && element.children && element.children.length>0)
            for(let i=0; i<element.children.length;i++)
                elemlog(element.children[i],showVarnames, isRecursive, ident+"  ");
    }
}
function fillData(elementIdentifier, dataCode, dataDesc, dataId) {
    if (typeof elementIdentifier !== 'undefined') {
        let eid = ebyid (elementIdentifier+"_id");
        let fld = ebyid (elementIdentifier+"_field");
        
        if (eid || fld) {
            if (typeof dataCode !== 'undefined') fillValue(elementIdentifier+"_field",dataCode);
            if (typeof dataDesc !== 'undefined') fillInnerHtml(elementIdentifier+"_descripcion",dataDesc);
            if (typeof dataId !== 'undefined')   fillValue(elementIdentifier+"_id",dataId);
            return true;
        } else {
            alert(elementIdentifier+"["+dataId+"] = "+dataCode+" | "+dataDesc);
            return false;
        }
    }
}
function fillMappedValue(assocArrayToValue) {
    if (typeof assocArrayToValue !== 'undefined' && assocArrayToValue) {
        for (let keyv in assocArrayToValue) {
            if (assocArrayToValue.hasOwnProperty(keyv) && typeof assocArrayToValue[keyv] !== "function") {
                let elementv = ebyid ( keyv );
                if (elementv) elementv.value = assocArrayToValue[keyv];
                else fillValue(keyv, assocArrayToValue[keyv]);
            }
        }
    }
}
function fillMappedInnerHtml(assocArrayToInnerHtml) {
    if (typeof assocArrayToInnerHtml !== 'undefined' && assocArrayToInnerHtml) {
        for (let keyh in assocArrayToInnerHtml) {
            if (assocArrayToInnerHtml.hasOwnProperty(keyh) && typeof assocArrayToInnerHtml[keyh] !== "function") {
                let elementh = ebyid ( keyh );
                if (elementh) elementh.innerHTML=assocArrayToInnerHtml[keyh];
                else fillInnerHtml(keyh, assocArrayToInnerHtml[keyh]);
            }
        }
    }
}
function doAlert() {
    alert("DONE ALERT!");
}
function fillPaginationIndexes(depth, force) {
    if (typeof depth === 'undefined') depth=0;
    if (typeof force === 'undefined') force=false;

    let lastPage = ebyid("lastpg");
    let pageNum = ebyid("pageno");
    let pageRows = ebyid("limit");
    let paginationIndexes = ebyid("paginationIndexes");
    let navToFirst = ebyid("navToFirst");
    let navToPrevious = ebyid("navToPrevious");
    let navToNext = ebyid("navToNext");
    let navToLast = ebyid("navToLast");
    let ovl = ebyid("overlay");
    let force2 = (ebyid("forcePaginationVisibility") && ebyid("forcePaginationVisibility").value=="true");
    let visStatus = "visible";
    if (ovl && !force && !force2) visStatus = ovl.style.visibility;

    if (lastPage && pageNum && pageRows && paginationIndexes) {
        let numPages = parseInt(lastPage.value);
        let plim = parseInt(pageRows.value);
        let curPage = parseInt(pageNum.value);
        navToFirst.style.visibility = (curPage > 2?visStatus:"hidden");
        navToPrevious.style.visibility = (curPage > 1?visStatus:"hidden");
        navToNext.style.visibility = (curPage < numPages?visStatus:"hidden");
        navToLast.style.visibility = (curPage < (numPages - 1)?visStatus:"hidden");
        paginationIndexes.innerHTML=" "+curPage+"/"+numPages+" ";
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | fillPaginationIndexes "+newDepth);
        setTimeout(function() {
            fillPaginationIndexes(newDepth);
        }, 25);
    } else {
        appendLog("ERROR | fillPaginationIndexes missing:"+(lastPage?"":" lastPage")+(pageNum?"":" pageNum")+(pageRows?"":" pageRows")+(paginationIndexes?"":" paginationIndexes"));
    }
}
function hidePaginationButtons(depth) {
    if (typeof depth === 'undefined') depth = 0;
    let navToFirst = ebyid("navToFirst");
    let navToPrevious = ebyid("navToPrevious");
    let navToNext = ebyid("navToNext");
    let navToLast = ebyid("navToLast");
    if (navToFirst && navToPrevious && navToNext && navToLast) {
        navToFirst.style.visibility = "hidden";
        navToPrevious.style.visibility = "hidden";
        navToNext.style.visibility = "hidden";
        navToLast.style.visibility = "hidden";
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | hidePaginationButtons "+newDepth);
        setTimeout(function() {
            hidePaginationButtons(newDepth);
        }, 50);
    } else {
        appendLog("ERROR | hidePaginationButtons missing:"+(navToFirst?"":" navToFirst")+(navToPrevious?"":" navToPrevious")+(navToNext?"":" navToNext")+(navToLast?"":" navToLast"));
    }
}
function initializeSubtreeModifiedListener(elementId, functionName, depth) {
    if (typeof depth === 'undefined') depth=0;
    let container = ebyid (elementId);
    if (container && container.addEventListener) {
        container.addEventListener ('DOMSubtreeModified', functionName, false);
    } else if (depth < 10) {
        let newDepth=depth+1;
        appendLog("RETRY | initializeSubtreeModifiedListener "+newDepth);
        setTimeout(function() {
            initializeSubtreeModifiedListener(elementId, functionName, newDepth);
        }, 20);
    } else {
        appendLog("ERROR | initializeSubtreeModifiedListener missing:"+(container?"":" container")+(!container||container.addEventListener?"":" container.addEventListener"));
    }
}
function resetBlocks() {
}
function loopFunc(arr, funcVal) {
    if (typeof funcVal === 'undefined') funcVal=false;
    for (let i=0; i<arr.length; i++) {
        let element = arr[i];
        if (funcVal) funcVal(element);
    }
}
function ajustaCerosEnPeso(elemId) {
    event = window.event;
    if (existeElemento(elemId)) {
        let element = ebyid(elemId);
        if (validaElementoNumerico(elemId)) {
            let val = parseInt(element.value, 10);
            let txt = "" + val;
            if (txt.length < 4) txt = ("0000"+txt).substr(txt.length);
            element.value = txt;
        } else element.value = "0000";
    }
}
function isEnterKey(event) {
    event = event || window.event;
    if (event.code !== undefined) return event.code==="Enter";
    if (event.key !== undefined) return event.key==="Enter";
    if (event.keyCode !== undefined) return event.key===13;
    if (event.which !== undefined) return event.which===13;
    if (event.charCode !== undefined) return event.charCode===13;
    if (event.keyIdentifier !== undefined) return event.keyIdentifier==="Enter";
}
function enterExit(event,callbackFunc) {
    if (isEnterKey(event)) {
        if (callbackFunc) callbackFunc(event);
        eventCancel(event);
        return false;
    }
    return true;
}
function delayKeyUp(event) {
    event = event || window.event;
    if (event && event.type=="keyup") {
        if (event.target.value.length<1) return false;
        // No calcular si se presionan las flechas para permitir movilidad en el texto.
        if (event.keyCode == '37' || event.keyCode == '38' || event.keyCode == '39' || event.keyCode == '40') {
            return false;
        }
        // No calcular si se presionan shift, control, alt, BloqMayus
        if (event.keyCode == '16' || event.keyCode == '17' || event.keyCode == '18' || event.keyCode == '20') return false;
        // No calcular teclas pause/break, escape, tab, enter
        if (event.keyCode == '19' || event.keyCode == '27' || event.keyCode == '9' || event.keyCode == '13') return false;
        // No calcular Insert, Supr, Inicio, Fin, RePag, AvPag
        if (event.keyCode == '45' || event.keyCode == '46' || event.keyCode == '36' || event.keyCode == '35' || event.keyCode == '33' || event.keyCode == '34') return false;
        // No calcular Left Window Key, Right Window Key, Select Key, Num Lock, Scroll Lock
        if (event.keyCode == '91' || event.keyCode == '92' || event.keyCode == '93' || event.keyCode == '144' || event.keyCode == '145') return false;
        // No calcular teclas F1 a F12
        if (event.keyCode == '112' || event.keyCode == '113' || event.keyCode == '114' || event.keyCode == '115' || event.keyCode == '116' || event.keyCode == '117' || event.keyCode == '118' || event.keyCode == '119' || event.keyCode == '120' || event.keyCode == '121' || event.keyCode == '122' || event.keyCode == '123') return false;
        // backspace se acepta, pero aumenta el delay
        if (event.keyCode == '8') {
            if (event.target.value.length<2) return false;
            return 500;
        }
    } else if (event && event.type=="keydown") {
        if (event.keyCode == '38' || event.keyCode == '40') { event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true); event.preventDefault(); }
        else return false;
    }
    return 100;
}
function isActionKey(event) {
    if (!event) event=window.event;
    if (event.keyCode == '8') return true; // backspace
    if (event.keyCode == '19' || event.keyCode == '27' || event.keyCode == '9' || event.keyCode == '13') return true; // pause/break, escape, tab, enter
    if (event.keyCode == '16' || event.keyCode == '17' || event.keyCode == '18' || event.keyCode == '20') return true; // shift, control, alt, mayus
    if (event.keyCode=='37' || event.keyCode == '38' || event.keyCode == '39' || event.keyCode == '40') return true; // Es Flecha
    if (event.keyCode == '45' || event.keyCode == '46' || event.keyCode == '36' || event.keyCode == '35' || event.keyCode == '33' || event.keyCode == '34') return true; // Insert, Supr, Inicio, Fin, RePag, AvPag
    if (event.keyCode == '91' || event.keyCode == '92' || event.keyCode == '93' || event.keyCode == '144' || event.keyCode == '145') return true; // Left Window Key, Right Window Key, Select Key, Num Lock, Scroll Lock
    if (event.keyCode == '112' || event.keyCode == '113' || event.keyCode == '114' || event.keyCode == '115' || event.keyCode == '116' || event.keyCode == '117' || event.keyCode == '118' || event.keyCode == '119' || event.keyCode == '120' || event.keyCode == '121' || event.keyCode == '122' || event.keyCode == '123') return true; // teclas F1 a F12
    return false;
}

// Este método provee busqueda predictiva en campos de texto.
// Se requiere asignar un idAccion a cada campo deseado y añadir el codigo respectivo en las secciones condicionadas por idAccion
function calculoAvanzadoDelay(evento, idAccion, idElemento, backupFunc) {
    if (typeof backupFunc === 'undefined') backupFunc=false;
    if (!evento) evento = window.event;
    //funclog("INI", "calculoAvanzadoDelay: "+(evento?evento.type:"sin evento")+", "+idAccion+(idElemento?", "+idElemento:""));
    // El objeto evento esta ligado al elemento que lo acciona, aunque tambien puede llamarse este metodo sin un evento ligado

    // El elemento a validar debe existir, ya sea obtenido del evento o por un id proporcionado
    let elemento = false;
    if (idElemento) {
        elemento = ebyid(idElemento);
    } else if (evento) {
        elemento = evento.target;
    }
    if (!elemento || !(elemento.value) || elemento.value.length==0) {
        $idUsuario(evento, idAccion, false);
        return;
    }

    // En caso del evento keyup evitamos actuar con caracteres o teclas especiales
    let delay = delayKeyUp(evento);
    if (delay === false) return;

    // Con el evento keyup usamos ejecucion retardada y cancelamos acciones en espera para continuar solo despues de la ultima tecla presionada dentro del rango de tiempo establecido, generalmente 1/10 de segundo.
    clearTimeout(timeOutAdvanced);
    if (evento && (evento.type=="keyup"||evento.type=="keydown")) {
        timeOutAdvanced = setTimeout(function() {
            calculoAvanzado(evento, idAccion, elemento, backupFunc);
        }, delay);
    } else {
        calculoAvanzado(evento, idAccion, elemento, backupFunc);
    }
}

function calculaRangoSeleccionado(elemento, inilen) {
    let endlen= elemento.value.length;
    //funclog("INI", "calculaRangoSeleccionado: "+elemento.value+", "+inilen+", "+endlen);
    if("selectionStart" in elemento) {
        elemento.selectionStart = inilen;
        elemento.selectionEnd = endlen;
        elemento.focus();
        //funclog("TAG", "FOCUS ON ELEMENTO");
    } else {
        let nRange = elemento.createTextRange();
        nRange.moveStart("character", inilen);
        nRange.collapse();
        nRange.moveEnd("character", (endlen-inilen));
        nRange.select();
        //funclog("TAG","RANGE SELECTION ON ELEMENTO");
    }
}
function seleccionaElemento(elemento,forzaSeleccion) {
    const selection = window.getSelection();
    if(forzaSeleccion) {
        selection.removeAllRanges(); // toDo: falta contemplar empty//chrome
    } else if (selection.rangeCount>0) {
        console.log("Se encontraron "+selection.rangeCount+" rangos en selección");
        return false; // si hay algo seleccionado no hace nada
    } else {
        const seltxt=selection.toString();
        if (seltxt.length>0) {
            console.log("Se encontró texto seleccionado");
            return false; // si hay algo seleccionado no hace nada
        }
    }
    const range = document.createRange();
    range.selectNodeContents(elemento);
    selection.addRange(range);
    console.log("SELECCION COMPLETA");
    return true;
}
function clearSelection() {
    if (window.getSelection) {
        const selection=window.getSelection();
        if (selection.empty) {  // Chrome
            selection.empty();
        } else if (selection.removeAllRanges) {  // Firefox
            selection.removeAllRanges();
        }
    } else if (document.selection) {  // IE?
        document.selection.empty();
    }
}
// En este metodo se hace una peticion al servidor, especifica por idAccion, el resultado se procesa en $idUsuario y despues se acondiciona el fragmento predicho del mismo para permitir correcciones.
function calculoAvanzado(evento, idAccion, elemento, backupFunc) {
    if (typeof backupFunc === 'undefined') backupFunc=false;
    //funclog("INI", "calculoAvanzado: "+(evento?evento.type:"sin evento")+", "+idAccion+(elemento?", "+elemento.value:""));
    let keyup = ( (evento && evento.type=="keyup" && elemento.type=="text") ? true : false );
    let keydown = ( (evento && evento.type=="keydown" && elemento.type=="text") ? true : false );
    if (!elemento || !(elemento.value) || elemento.value.length==0) {
        $idUsuario(evento, idAccion, false);
        return;
    }
    let inilen = 0;
    if (elemento.type=="text") inilen = elemento.selectionStart;
    //funclog("inilen",inilen);
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let resultado = xmlhttp.responseText;
            // Procesamos el resultado de la peticion al servidor. Con el evento keyup ademas seleccionamos el texto agregado por la busqueda predictiva para facilitar modificaciones.
            let shft = $idUsuario(evento, idAccion, resultado, backupFunc)
            if (shft!==false && (keyup||keydown)) {
                if (shft===true) shft=0;
                if (shft>0) inilen=shft;
                //funclog("isReady","calculoAvanzado: "+inilen+", "+elemento.id+", "+elemento.value);
                timeOutRango = setTimeout(function() {
                    calculaRangoSeleccionado(elemento, inilen);
                }, 0);
            }
        }
    };
    // Las peticiones al servidor son individuales y decididas por la accion indicada desde el principio. Esto permite extender rapidamente la funcionalidad de busqueda predictiva a nuevos campos de texto.
    clearTimeout(timeOutRango);
    let rqst="";
    let elemValue = elemento.value;
    if (keydown && inilen<elemValue.length) {
        elemValue = elemValue.substr(0, inilen);
    }
    if (idAccion=="grupo") {
        rqst = "consultas/Grupo.php?llave=rfc&rfc="+elemValue+"&solicita=id,razonSocial,alias,rfc";
    } else if (idAccion=="proveedor") {
        rqst = "consultas/Proveedores.php?llave=nombre&nombre="+elemValue+"&solicita=id,nombre,codigo";
    } else if (idAccion=="usuario-perfil") {
        rqst = "consultas/Usuarios_Perfiles.php?clase=Usuarios_Perfiles&usuario="+elemValue;
    }
    if (rqst.length > 0) {
        if (keyup||keydown) rqst+="&modo=avanzado";
        xmlhttp.open("GET",rqst,true);
        xmlhttp.send();
        //funclog("SENT", "calculoAvanzado: "+rqst+"\n");
    }
}

// En este metodo se procesa el resultado y se decide el curso de accion posterior
// Los modulos que actualmente reciben las peticiones en el servidor regresan datos separados por pipes '|'
// Y las condiciones por idAccion asignan los diferentes valores a campos de texto en la interfaz
// La segunda evaluacion de condicion de idAccion sirve para borrar los campos de texto cuando no se encuentran resultados
function resultadoAvanzado(evento, idAccion, resultado, backupFunc) {
    if (typeof backupFunc === 'undefined') backupFunc=false;
    if (resultado && resultado.length>0) { // && resultado.indexOf("|")>=0) {
        //funclog("INI+", "resultadoAvanzado: "+(evento?evento.type:"sin evento")+", "+idAccion+", "+resultado.length);
        let rarr = resultado.split("|");
        let keyup = ( (evento && evento.type=="keyup") ? true : false );
        let keydown = ( (evento && evento.type=="keydown") ? true : false );

        if (idAccion=="grupo") {
            if (keydown) {
                let sIdx = findFollowedSectionInRows(rarr,3,0,"grupo_id", evento.keyCode);
                if (sIdx !== false) {
                    fillValue("grupo_id", rarr[3*sIdx]);
                    fillValue("grupo_field", rarr[3*sIdx+1]);
                    fillValue("grupo_code", rarr[3*sIdx+2]);
                    fillInnerHtml("grupo_descripcion", rarr[3*sIdx+2]);
                    return true;
                }
                return false;
            } else {
                fillValue("grupo_id", rarr[0]);
                fillValue("grupo_field", rarr[1]);
                fillValue("grupo_code", rarr[2]);
                fillInnerHtml("grupo_descripcion", rarr[2]);
                return findEqualIndexInRows(rarr,3,1);
            }
        } else if (idAccion=="proveedor") {
            if (keydown) {
                let sIdx = findFollowedSectionInRows(rarr,3,0,"proveedor_id", evento.keyCode);
                if (sIdx !== false) {
                    fillValue("proveedor_id", rarr[3*sIdx]);
                    fillValue("proveedor_field", rarr[3*sIdx+1]);
                    fillValue("proveedor_code", rarr[3*sIdx+2]);
                    fillInnerHtml("proveedor_descripcion", rarr[3*sIdx+2]);
                    return true;
                }
                return false;
            } else {
                fillValue("proveedor_id", rarr[0]);
                fillValue("proveedor_field", rarr[1]);
                fillValue("proveedor_code", rarr[2]);
                fillInnerHtml("proveedor_descripcion", rarr[2]);
                return findEqualIndexInRows(rarr,3,1);
            }
        } else if (idAccion=="usuario") {
            //funclog("Resultados","resultadoAvanzado: "+rarr[0]+", "+rarr[1]+", "+rarr[2]+", "+rarr[3]+", "+rarr[5]+"\n");
            // IDX 0=id, 1=nombre, 2=persona, 3=email, 4=cambiaClave, 5=observaciones, 6=perfiles, 7=perfilxgrupo
            let idUsrElem = fillValue("user_id", rarr[0]);
            let nomUsrElem = fillValue("user_field", rarr[1]);
            fillValue("user_realname", rarr[2]);
            fillValue("user_email", rarr[3]);
            let kchk=ebyid("user_updkey");
            let kval=ebyid("user_updval");
            if (kchk&&kval) {
                kval.value=rarr[4];
                kchk.checked=(kval.value==="1");
            }
            fillValue("user_obs", rarr[5]);
            updateProfilesPerUser(rarr[6],rarr[7]);
            return false;
        }
    } else {
        //funclog("INI-", "resultadoAvanzado: "+(evento?evento.type:"sin evento")+", "+idAccion+", resultado vacio");
        if (idAccion=="grupo") {
            // fillValue("grupo_id", "");
            // fillValue("grupo_code", "");
            // fillInnerHtml("grupo_descripcion", "");
            fillValue("grupo_id", "");
            fillValue("grupo_field", "");
            fillValue("grupo_code", "");
            fillInnerHtml("grupo_descripcion", "");
        } else if (idAccion=="proveedor") {
            fillValue("proveedor_id", "");
            fillValue("proveedor_field", "");
            fillValue("proveedor_code", "");
            fillInnerHtml("proveedor_descripcion", "");
        } else if (idAccion=="usuario") {
            //let idUsrElem = fillValue("idUsuario", "");
            //fillValue("correoUsuario", "");
            //changeAttribute("borrarUsuario", "style", "display: none;");
            //if (ebyid("generaPassword")) resetPassword();
            //clearTimeout(timeOutPerfiles);
            //clearTimeout(timeOutAcciones);
            //resultadoAvanzado(evento, "usuario-perfil", false);
            fillValue("user_id", "");
            fillValue("user_field", "");
            fillValue("user_password", "");
            fillValue("user_email", "");
            fillValue("user_realname", "");
            fillValue("user_obs", "");
            cleanProfilesPerUser();
        } else if (idAccion=="usuario-perfil") {
            changeAttributeByClass("u_checkbox u_perfil", "checked", false);
            changeAttributeByClass("u_checkbox u_accion", "checked", false);
            changeAttribute("borrarUsuario", "style", "display: none;");
        }
    }
    return false;
}
function findEqualIndexInRows(rarr,elemsPerRow,elemIdxToEval) {
    let baseLen = rarr.length;
    let len = baseLen - (baseLen%elemsPerRow);
    //funclog("INI", "findEqualIndexInRows (arr,"+elemsPerRow+","+elemIdxToEval+") Len "+baseLen+"->"+len);
    let i=0;
    if (len<=elemsPerRow) return 0;
    let baseName = rarr[elemIdxToEval];
    top: {
        for (i=0; i<baseName.length; i++) {
            let nextIdx=elemIdxToEval+elemsPerRow;
            for (let j=nextIdx; j<len; j+=elemsPerRow) {
                if(baseName.charAt(i)!=rarr[j].charAt(i))
                    break top;
            }
        }
    }
    //funclog("END","findEqualIndexInRows i="+i);
    return i;
}
function findSectionInRows(rarr, elemsPerSection, lookOutIdx, lookOutVal) {
    let len = rarr.length;
    len -= (len%elemsPerSection);
    for (let idx=0, i=lookOutIdx; i<len; i+=elemsPerSection, idx++)
        if (rarr[i]==lookOutVal) return idx;
    return false;
}
function findFollowedSectionInRows(rarr, elemsPerSection, lookOutIdx, lookOutElemId, keyCode) {
    //funclog("INI", "findFollowedSectionInRows (arr("+rarr.length+"),"+elemsPerSection+","+lookOutIdx+","+lookOutElemId+","+keyCode+")");
    if (rarr.length>=(2*elemsPerSection)) { // minimo dos secciones
        let elemVal = getValueTxt(lookOutElemId, false, false, false);
        //funclog("VALUE",lookOutElemId+"='"+elemVal+"'");
        if (elemVal!==false && elemVal!=="0") {
            let sectionIdx = findSectionInRows(rarr, elemsPerSection, lookOutIdx, elemVal);
            //funclog("SECTION", "sectionIdx="+sectionIdx);
            if (sectionIdx!==false) {
                if (keyCode=='38') sectionIdx--;
                else sectionIdx++; // keyCode=='40'
                let len=rarr.length;
                len-=len%elemsPerSection;
                let maxSec=len/elemsPerSection;
                if(sectionIdx<0) sectionIdx=maxSec-1;
                else if(sectionIdx>=maxSec) sectionIdx=0;
                //funclog("NEW", "sectionIdx="+sectionIdx+"\n");
                return sectionIdx;
            }
        }
    }
    //funclog("END","findFollowedSectionInRows");
    return false;
}
function existeElemento(elemId) {
    let element = ebyid(elemId);
    if (!empty(element)) {
        return true;
    }
    return false;
}
function forzarSoloNumeros(event) {
    if (!event) event = window.event;
    //funclog("INI" ,"forzarSoloNumeros eventtype="+event.type+". target="+event.target+". keyCode="+event.keyCode+". ctrl="+event.ctrlKey);
    if (!event.ctrlKey && !event.metaKey && !event.altKey) {
        let charCode = (typeof event.which == "undefined") ? event.keyCode : event.which;
        if (charCode && !/\d/.test(String.fromCharCode(charCode))) return false;
    }
    return true;
}
function validaElementoNumerico(elemId) {
    let element = ebyid(elemId);
    if(element.value.length>0 && !isNaN(element.value)) {
        //funclog("INI", "validaElementoNumerico '"+element.value+"' ("+element.value.length+") = true.");
        return true;
    }
    return false;
}
function validaElementoPorcentajeMayorA(elemId, limite) {
    let element = ebyid(elemId);
    if (Math.round(parseFloat(element.value), 2) > limite) {
        return true;
    }
    return false;
}
function validateIntegerDown(event) {
    return ( event.ctrlKey || event.altKey
            || (47<event.keyCode && event.keyCode<58 && event.shiftKey==false)
            || (95<event.keyCode && event.keyCode<106)
            || (event.keyCode==8) || (event.keyCode==9)
            || (event.keyCode>34 && event.keyCode<40)
            || (event.keyCode==46))
}
/*
        // No calcular si se presionan shift, control, alt, BloqMayus
        if (event.keyCode == '16' || event.keyCode == '17' || event.keyCode == '18' || event.keyCode == '20') return false;
        // No calcular teclas pause/break, escape, tab, enter
        if (event.keyCode == '19' || event.keyCode == '27' || event.keyCode == '9' || event.keyCode == '13') return false;
        if (event.keyCode == '37' || event.keyCode == '38' || event.keyCode == '39' || event.keyCode == '40') return false;
        // No calcular Insert, Supr, Inicio, Fin, RePag, AvPag
        if (event.keyCode == '45' || event.keyCode == '46' || event.keyCode == '36' || event.keyCode == '35' || event.keyCode == '33' || event.keyCode == '34') return false;

        // No calcular Left Window Key, Right Window Key, Select Key, Num Lock, Scroll Lock
        if (event.keyCode == '91' || event.keyCode == '92' || event.keyCode == '93' || event.keyCode == '144' || event.keyCode == '145') return false;
        // No calcular teclas F1 a F12
        if (event.keyCode == '112' || event.keyCode == '113' || event.keyCode == '114' || event.keyCode == '115' || event.keyCode == '116' || event.keyCode == '117' || event.keyCode == '118' || event.keyCode == '119' || event.keyCode == '120' || event.keyCode == '121' || event.keyCode == '122' || event.keyCode == '123') return false;

        // backspace se acepta, pero aumenta el delay
        if (event.keyCode == '8') {
            if (event.target.value.length<2) return false;
            return 500;
        }
 */
function validateIntegerUp(event) {
    let sStart = event.target.selectionStart;
    let sEnd = event.target.selectionEnd;
    let value = event.target.value;
    
    if (value.length == 0) {
        event.target.value = "0";
        value = event.target.value;
    }
    
    event.target.value=value.replace(/[^\d]/, '');
    value = event.target.value;
    
    if ((!event.ctrlKey && !event.altKey && !event.shiftKey) &&
        ((event.keyCode>47 && event.keyCode<58) ||
         (event.keyCode>95 && event.keyCode<106)) &&
        sStart==sEnd) {
        let prevVal = value.substr(0,sStart-1)+value.substr(sStart);
        let keyVal = value.substr(sStart-1, 1);
        let iVal = +prevVal;
        if (iVal == 0) {
            event.target.value = keyVal;
            value = event.target.value;
        }
    }
    
    event.target.setSelectionRange(sStart,sEnd);
}

// event.type must be keypress
function getChar(event) {
  if (event.which == null) {
    return String.fromCharCode(event.keyCode) // IE
  } else if (event.which!=0 && event.charCode!=0) {
    return String.fromCharCode(event.which)   // the rest
  } else {
    return null // special key
  }
}
function validateCurrencyPress(event) {
    event.target["haskeypressed"]=true;
}
function validateCurrencyDown(event) {
    event.target["haskeypressed"]=false;
    event.target["oldValue"]=event.target.value;
    event.target["oldStart"]=event.target.selectionStart;
    event.target["oldEnd"]=event.target.selectionEnd;
    event.target["oldPtIdx"]=event.target.value.indexOf(".");
    if ( event.ctrlKey || event.altKey
                    || (47<event.keyCode && event.keyCode<58 && event.shiftKey==false)
                    || (95<event.keyCode && event.keyCode<106)
                    || (event.keyCode==8) || (event.keyCode==9) 
                    || (event.keyCode>34 && event.keyCode<40) 
                    || (event.keyCode==46) 
                    || (event.keyCode==110)
                    || (event.keyCode==190)) {
        pressedKeys++;
        return true;
    } else return false;
}
function validateCurrencyUp(event) {
    let sStart = event.target.selectionStart;
    let sEnd = event.target.selectionEnd;
    let value = event.target.value;
    let ptIdx = value.indexOf(".");
    let len = value.length;

    let oldStart = parseInt(event.target["oldStart"]);
    let oldEnd = parseInt(event.target["oldEnd"]);
    let oldValue = event.target["oldValue"];
    let oldPtIdx = parseInt(event.target["oldPtIdx"]);
    let oldLen = oldValue?oldValue.length:0;

    let c = String.fromCharCode(event.keyCode);
    if (event.keyCode!=110 && event.keyCode!=190 && (event.keyCode<48 || event.keyCode>57) && (event.keyCode<96 || event.keyCode>105)) {
        if (event.target["haskeypressed"]) {
            event.target.value = oldValue;
            event.target.selectionStart = oldStart;
            event.target.selectionEnd = oldEnd;
            event.preventDefault();
            pressedKeys--;
            return false;
        }

        if (event.keyCode==8||event.keyCode==46) {
        } else {
            pressedKeys--;
            return true;
        }
    }
    
    if (value.length == 0) value = "0.00";
    else value = value.replace(/[^\d\.]/, '');
    ptIdx = value.indexOf("."); len = value.length;

    if (ptIdx < 0) { value = value+".00"; ptIdx = value.indexOf("."); len = value.length;
    } else {
        let pt2Idx = value.lastIndexOf(".");
        if (ptIdx != pt2Idx) {
            if (pt2Idx-ptIdx == 1) value = value.substring(0, ptIdx)+"."+value.substring(pt2Idx+1);
            else value = value.substring(0, pt2Idx);
            ptIdx = value.indexOf("."); len = value.length;
            if (event.keyCode==110 || event.keyCode==190) sStart = sEnd = ptIdx+1;
        }
    }
    
    if ((!event.ctrlKey && !event.altKey && !event.shiftKey) &&
        ((event.keyCode>47 && event.keyCode<58) ||
         (event.keyCode>95 && event.keyCode<106)) &&
        sStart==sEnd) {
        let prevVal = value.substr(0,sStart-1)+value.substr(sStart);
        let keyVal = value.substr(sStart-1, 1);
        if (prevVal == "0.00" && sStart<=ptIdx) {
          value = keyVal+".00";
          ptIdx = value.indexOf("."); len = value.length;
        }
    }
    
    if (ptIdx == 0) {
        value = "0"+value; ptIdx = value.indexOf("."); len = value.length;
        if (event.keyCode==110 || event.keyCode==190) sStart = sEnd = ptIdx+1;
        if (sStart==0) sStart=1;
        if (sEnd==0) sEnd=1;
    }

    if (value.length - ptIdx > 3) {  // Elimina tercer decimal en adelante
        value = value.substring(0, ptIdx+3);
        len = value.length;
    }
    if (value.length - ptIdx == 2) {
        value = value+"0";
        len = value.length;
    }

    if (oldEnd==1 && oldPtIdx==1 && ptIdx==1 && sEnd==2 && event.keyCode!=110 && event.keyCode!=190) sEnd--;

    if (oldPtIdx!=ptIdx) {
        if (value!=oldValue) {
        } else {
            sEnd--;
        }
    }
    
    value = parseFloat(value).toFixed(2);
    if (value.indexOf(".")<ptIdx) sEnd--;
    ptIdx = value.indexOf("."); len = value.length;

    //funclog("INI","validateCurrencyUp: "+oldStart+", "+sStart+", "+oldEnd+", "+sEnd+", "+oldPtIdx+", "+value.indexOf(".")+", "+event.keyCode+", "+String.fromCharCode(event.keyCode)+", "+oldValue+", "+oldValue.length+", "+event.target.value+", "+event.target.value.length+", "+value+", "+value.length);

    event.target.value = value;
    if ("selectionStart" in event.target) {
        event.target.selectionStart = sEnd;
        event.target.selectionEnd = sEnd;
    } else {
        let nRange = event.target.createTextRange();
        nRange.moveStart("character", sEnd);
        nRange.collapse();
    }
    event.target["positionHadBeenFixed"]=true;
    pressedKeys--;
}

function validaFecha(prefix) {
    // Fecha Inicial
    iniDia = ebyid("inidia");
    iniMes = ebyid("inimes");
    iniAnio = ebyid("inianio");
    iniFecha = new Date(iniAnio.value, iniMes.value-1, iniDia.value);
    iniDia.max = daysInMonth(iniMes.value, iniAnio.value);
    if (iniDia.value > iniDia.max) iniDia.value = iniDia.max;
    // Fecha Final
    finDia = ebyid("findia");
    finMes = ebyid("finmes");
    finAnio = ebyid("finanio");
    finFecha = new Date(finAnio.value, finMes.value-1, finDia.value);
    finDia.max = daysInMonth(finMes.value, finAnio.value);
    if (finDia.value > finDia.max) finDia.value = finDia.max;

    if (iniFecha.getTime() > finFecha.getTime()) {
        if (prefix == 'ini') {
            iniAnio.value = finAnio.value;
            iniMes.selectedIndex = finMes.selectedIndex;
            iniDia.max = finDia.max;
            iniDia.value = finDia.value;
        } else {
            finAnio.value = finAnio.value;
            finMes.selectedIndex = finMes.selectedIndex;
            finDia.max = finDia.max;
            finDia.value = finDia.value;
        }
        return false;
    }
    return true;
}

function generaToken(elementId1, elementId2, length, totalLength2) {
    if (typeof length === 'undefined') length=8;
    if (typeof totalLength2 === 'undefined') totalLength2=8;
    //funclog("INI", "generaToken ("+length+")");
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let resultado = xmlhttp.responseText;
            //funclog("RESULTADO", "generaToken: '"+resultado+"'");
            if(elementId1) fillValue(elementId1,resultado);
            if(elementId2) {
                if (totalLength2>length) {
                    let rpad = Math.ceil((padlen = totalLength2 - resultado.length) / 2);
                    let lpad = padlen - rpad;
                    fillValue(elementId2, Array(lpad+1).join(' ')+resultado+Array(rpad+1).join(' '));
                } else
                    fillValue(elementId2, resultado);
            }
        }
    };
    
    let rqst = "consultas/Usuarios.php?clase=Usuarios&opcion=generaClave&largo="+length;
    xmlhttp.open("GET",rqst,true);
    xmlhttp.send();
    //funclog("SENT", "generaToken: "+rqst);
}
function removeUnprintable() {
    let unprintableElements = document.querySelectorAll(".unprintable");
    for (let i=0; i<unprintableElements.length; i++) {
        unprintableElements[i].style.display = "none";
    }
}

function displayUnprintable() {
    let unprintableElements = document.querySelectorAll(".unprintable");
    for (let i=0; i<unprintableElements.length; i++) {
        if (unprintableElements[i].tagName=="TABLE")
            unprintableElements[i].style.display = "table";
        else
            unprintableElements[i].style.display = "block";
    }
}
function empty(mixed_var) {
  let undef, key, i, len;
  let emptyValues = [undef, null, false, 0, '', '0'];
  for (i = 0, len = emptyValues.length; i < len; i++) {
    if (mixed_var === emptyValues[i]) {
      return true;
    }
  }
  if (typeof mixed_var === 'object') {
    for (key in mixed_var) {
      //if (mixed_var.hasOwnProperty(key)) {
      return false;
      //}
    }
    return true;
  }
  return false;
}
function eventCancel(event) {
  if (!event) {
    if (window.event) event = window.event;
    else return false;
  }
  if (event.cancelBubble != null) event.cancelBubble = true;
  if (event.stopPropagation) event.stopPropagation();
  if (event.preventDefault) event.preventDefault();
  if (window.event) event.returnValue = false;
  if (event.cancel != null) event.cancel = true;
  return false;
}
function eventStop(event) {
    if (!event) {
        if (window.event) event=window.event;
        else return false;
    }
  if (event.cancelBubble != null) event.cancelBubble = true;
  if (event.stopPropagation) event.stopPropagation();
}
function ignoreAlpha(event) {
  if (!event) {
    if (window.event) event = window.event;
    else return false;
  }
  if ((event.keyCode >= 65 && event.keyCode <= 90)||(event.keyCode>=48 && event.keyCode<=57)) // A to Z || 0 to 9
    eventCancel(event);
}
function addEvent(element, eventName, callback) {
    if (element.addEventListener) {
        element.addEventListener(eventName, callback, false);
    } else if (element.attachEvent) {
        element.attachEvent("on" + eventName, callback);
    } else {
        const eventName="on"+eventName;
        let oldScript=false;
        if (element[eventName]) {
            oldScript=element[eventName];
            if (callback) element[eventName] = function(event) {
                oldScript(event);
                callback(event);
            }
        } else element[eventName] = callback;
    }
}
function addCookie(name, value, days) {
    let expires = "";
    if (!days) days=1;
    let date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    let expireValue=date.toUTCString();
    expires="; expires="+expireValue;
    if (days<0) {
        document.cookie = name+"="+expires;
        document.cookie = name+"="+expires+"; path=/";
    }
    const cookieValue=name+"="+encodeURIComponent(value)+expires+"; path=/invoice";
    //conlog("addCookie: "+cookieValue);
    document.cookie = cookieValue;
    return cookieValue;
}
function hasCookie(name) {
    let value = getCookie(name);
    if (value) return true;
    return false;
}
function getCookie(name) {
    const expr="(?:(?:^|.*;)\\s*" +
        encodeURIComponent(name).replace(/[\-\.\+\*]/g, "\\$&") +
        "\\s*\\=\\s*([^;]*).*$)|^.*$";
    const re=new RegExp(expr);
    const cookie=document.cookie.replace(re, "$1");
    let cookieValue = decodeURIComponent(cookie);
    //conlog("getCookie: "+name+" = "+cookieValue);
    return cookieValue;
}
function delCookie(name) {
    addCookie(name,"",-1);
}
function mouseIsDown(evt) {
    if (!evt) evt = window.event;
    let tgt = evt.target!=null ? evt.target: evt.srcElement;
    if (evt.which) { if (evt.which==1) _isMouseDown = true; }
    else if (evt.buttons) { if (evt.buttons==1) _isMouseDown = true; }
    else if (evt.button) { if (evt.button==0) _isMouseDown = true; }
    if (_isMouseDown) {
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (!_dragElement) return false;
        document.onmouseup = OnMouseUp;
        _startX = evt.clientX;
        _startY = evt.clientY;
        _offsetX = ExtractNumber(_dragElement.style.left);
        _offsetY = ExtractNumber(_dragElement.style.top);
        document.onmousemove = OnMouseMove;
        document.body.focus();
        //funclog("TAG", "mouseIsDown: FOCUS ON BODY DURING MOUSE_IS_DOWN");
        document.onselectstart = function () { return false; };
        _dragElement.ondragstart = function() { return false; };
        _dragElement.style.margin = null;
        _dragElement.style.verticalAlign = null;
        _dragElement.style.marginTop = null;
        _dragElement.style.position = 'relative';
        //funclog("TAG", "mouseIsDown: Start Dragging "+_dragElement.id+": ("+_offsetX+", "+_offsetY+")");
        return false;
    }
    return true;
}
function OnMouseUp(evt) {
    if (!evt) evt = window.event;
    if (_dragElement) {
        //funclog("TAG", "OnMouseUp: Dragging stopped ("+ExtractNumber(_dragElement.style.left)+", "+ExtractNumber(_dragElement.style.top)+")\n");
        document.onmousemove = null;
        document.onselectstart = null;
        _dragElement.ondragstart = null;
        _dragElement = null;
    }
    _isMouseDown = false;
}
function OnMouseMove(evt) {
    if (!evt) evt = window.event;
    if (_dragElement) {
        _dragElement.style.left = (_offsetX + evt.clientX - _startX) + 'px';
        _dragElement.style.top = (_offsetY + evt.clientY - _startY) + 'px';
    }
}
function preventKeyCodes(evento, lista) {
    let keydown = ( (evento && evento.type=="keydown") ? true : false );
    if (lista.indexOf(evento.keyCode)>=0) return eventCancel(evento);
    // if (evento.keyCode == '32') return eventCancel(evento);
    return true;
}
function clearChildrenByClass(classname) {
    let elements = lbycn(classname);
    for (let i=0; i<elements.length; i++) {
        while(elements[i].firstChild) elements[i].removeChild(elements[i].firstChild);
    }
}
function addSibMsg(elem, message) {
    conlog("Removed addSibMsg");
}
function addSibMsgByClass(classname, message) {
    removeElementsByClass("siblingMessage");
    let elems = lbycn(classname);
    if (elems.length>0) {
        for(let i=0; i<elems.length; i++) {
            let msgElem = document.createElement("SPAN");
            cladd(msgElem,"siblingMessage");
            msgElem.appendChild(document.createTextNode(message));
            if (elems[i].nextElementSibling) elems[i].parentNode.insertBefore(msgElem, elems[i].nextSiblingElement);
            else elems[i].parentNode.appendChild(msgElem);
        }
    }
}
function getLoadableImgScript(callbackFuncString) {
    return "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\""+callbackFuncString+";ekil(this);\">";
}
function copyContentToClipboardByClass(classname) {
    let elems = lbycn(classname);
    let message = getAllTextIn(elems, " ", 10, "\n");
    return copyTextToClipboard(message);
}
function getAllTextIn(elem, separator, depth, onlyFirstSeparator) {
    if (!separator) separator="";
    if (!depth && depth!==0) depth=10;
    if (isNode(elem)) {
        if (elem.nodeType==3) return elem.nodeValue;
        if (elem.value) return elem.value;
        if (depth===0||depth<0) return "";
        let retval = "";
        let first=true;
        for (elem=elem.firstChild; elem; elem=elem.nextSibling) {
            if (first) first=false;
            else if (onlyFirstSeparator) retval+=onlyFirstSeparator;
            else retval += separator;
            retval += getAllTextIn(elem, separator, depth-1);
        }
        return retval;
    } else if (!elem) {
        return "";
    } else if (Array.isArray(elem)) {
        if (depth===0||depth<0) return "";
        let retval = "";
        for (let i=0; i<elem.length; i++) {
            if (i>0) {
                if (onlyFirstSeparator) retval+=onlyFirstSeparator;
                else retval += separator;
            }
            retval += getAllTextIn(elem[i], separator, depth-1);
        }
    } else if( (typeof elem === "object") && (elem !== null) ) {
        let retval = "";
        
        if ((typeof elem.length === 'number') && elem.length>0 && typeof elem[0] === "object") {
            for (let i=0; i<elem.length; i++) {
                if (i>0) {
                    if (onlyFirstSeparator) retval+=onlyFirstSeparator;
                    else retval += separator;
                }
                retval += getAllTextIn(elem[i], separator, depth-1);
            }
        } else if (elem.toString) retval = elem.toString();
        else retval = Object.prototype.toString.call(elem);

        return retval;
    }
    return ""+elem;
}
function copyTextAreaIdToClipboard(textAreaId, logId, briefLogId) {
    let textAreaElem = ebyid(textAreaId);
    let logElem = ebyid(logId);
    let briefLogElem = ebyid(briefLogId);
    return copyTextAreaElemToClipboard(textAreaElem, logElem, briefLogElem);
}
function copyTextAreaElemToClipboard(textAreaElem, logDivElem, briefLogDivElem) {
    let logText = "";
    let briefLog = "";
    let success = false;
    if (textAreaElem && textAreaElem.value.length>0) {
        try {
            textAreaElem.select();
            let successful = document.execCommand('copy');
            logText = (successful ? 'La ruta fue copiada satisfactoriamente' : 'No se pudo copiar la ruta');
            briefLog = (successful ? 'COPIADO' : 'NO COPIADO');
            success = true;
        } catch (err) {
            logText = 'Error al copiar texto: '+err.message;
            briefLog = 'ERROR AL COPIAR';
        }
    } else {
        logText = 'No hay texto que copiar';
        briefLog = 'VACIO';
    }
    //conlog("COPIED:\n"+logText);
    if (logDivElem) {
        while(logDivElem.firstChild) logDivElem.removeChild(logDivElem.firstChild);
        logDivElem.appendChild(document.createTextNode(logText));
    }
    if (briefLogDivElem) {
        while(briefLogDivElem.firstChild) briefLogDivElem.removeChild(briefLogDivElem.firstChild);
        briefLogDivElem.appendChild(document.createTextNode(briefLog));
    }
    return success;
}
function copyTextToClipboard(text,logId,briefLogId) {
    let textArea = document.createElement("textarea");
    // Positioned at top-left corner of screen
    textArea.style.position='fixed';
    textArea.style.top=0;
    textArea.style.left=0;
    // Small width and height. 1px or 1em doesn't work.
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    // Remove padding
    textArea.style.padding = 0;
    // Remove borders
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    // Avoid background rendering
    textArea.style.background = 'transparent';
    // Assign, display and select
    textArea.value = text;
    document.body.appendChild(textArea);

    let logElem = null;
    let briefLogElem = null;
    if (logId) logElem = ebyid(logId);
    if (briefLogId) briefLogElem = ebyid(briefLogId);

    let result = copyTextAreaElemToClipboard(textArea, logElem, briefLogElem);
    document.body.removeChild(textArea);
    return result;
}

function getErrorDetail(exception,showStack) {
    if (exception instanceof Error) {
        let text=exception.name+": "+exception.message;
        if (exception.fileName || exception.lineNumber) {
            text+=" (";
            if (exception.fileName) text+=exception.fileName;
            if (exception.lineNumber) {
                text+="#"+exception.lineNumber;
                if (exception.columnNumber) text+="#"+exception.columnNumber;
            }
            text+=")";
        }
        if (exception.stack && showStack) {
            text+="\n"+exception.stack;
        }
        return text;
    } else return "["+exception.toString()+"]";
}
function clearFileInput(fileInputElement) {
    try {
        fileInputElement.value = null;
    } catch(ex) { }
    if (fileInputElement.value) {
        fileInputElement.parentNode.replaceChild(fileInputElement.cloneNode(true), fileInputElement);
    }
    if (fileInputElement.value) {
        const f=document.createElement("FORM");
        const p=fileInputElement.parentNode;
        const r=fileInputElement.nextSibling;
        f.appendChild(fileInputElement);
        f.reset();
        p.insertBefore(fileInputElement,r);
    }
    if (fileInputElement.value) return false;
    return true;
}
function isValidFile(file,validType) {
    if (!file.name || !file.size || !file.type) return false;
    let name=file.name;
    if(name.length>17) name=name.slice(0,5)+"..."+name.slice(-9);
    const type=file.type;
    if (validType &&((Array.isArray(validType) && !validType.includes(type))||type!==validType))
        return "El archivo '"+name+"' no tiene el formato requerido";
    const size=+file.size;
    if (size>2097000)
        return "El archivo '"+name+"' excede el tamaño máximo permitido de 2MB";
    return true;
}
//Returns true if it is a DOM node
function isNode(o){
    return o && o!==null && (typeof Node==="object" ? o instanceof Node : 
        (typeof o==="object" && typeof o.nodeType==="number" && typeof o.nodeName==="string"));
}

//Returns true if it is a DOM element    
function isElement(o){
    return o && o!==null && (typeof HTMLElement==="object" ? o instanceof HTMLElement : //DOM2
        (typeof o==="object" && o.nodeType===1 && typeof o.nodeName==="string"));
}
function isElemObj(o) {
    return o && o!==null && typeof o==="object" && (o.eName || o.eText || o.eComment);
}
// SPECIFIC TOOLS
function randNum(maxNum) {
    return Math.floor(Math.random()*maxNum);
}
function randChar(maxVal) {
    if (!maxVal || maxVal<0 || maxVal>65535) maxVal=65535;
    return String.fromCharCode(randNum(maxVal));
}
function randHexStr(length) {
    const result=[""], chars="0123456789abcdef";
    if (!length || Number.isNaN(length) || isNaN(length) || length<1) return "";
    for (let i=0; i<length; i+=Math.random()) 
        result[~~i]=chars[randNum(chars.length)];
    return result.join("");
}
function daysInMonth(humanMonth, year) {
    return new Date(year || new Date().getFullYear(), humanMonth, 0).getDate();
}
function toFixed(num, precision) {
    return round10(num, -precision).toFixed(precision);
}
function round10(value, exp) {
    if (typeof exp === 'undefined' || +exp === 0) {
        return Math.round(value);
    }
    value = +value;
    exp = +exp;
    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
        return NaN;
    }
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
}
function ExtractNumber(value) {
    let n = parseInt(value);
    return n == null || isNaN(n) ? 0 : n;
}
function getSortedIndex(array, value) {
    let low=0;
    let high=array.length;
    let mid=0;
    while (low<high) {
        mid=(low+high)>>>1;
        if(array[mid]<value) low=mid+1;
        else high=mid;
    }
    return low;
}
function human_filesize(bytes, decimals) {
    if (decimals === false || decimals === undefined || typeof decimals !== 'number' || isNaN(decimals)) decimals=2;
    else if (decimals<0) decimals=0;
    const sz = 'BKMGTPEZY';
    const lenbytes=(""+bytes).length;
    const factor = Math.floor((lenbytes - 1) / 3);
    if (factor>0) {
        const szu = sz.charAt(factor);
        const num = bytes/Math.pow(1024,factor);
        return num.toFixed(decimals)+szu+"B";
    } else return bytes+"B";
}
function splitCharTypes(str,opts) {
    let alp = "";
    let num = "";
    let spc = "";
    for (let i=0; i<str.length; i++) {
        if (!isNaN(String(str[i]) * 1)) {
            if (opts&&opts.numFirst) alp="";
            num+=str[i];
        } else if((str[i] >= 'A' && str[i] <= 'Z') ||
         (str[i] >= 'a' && str[i] <= 'z')) {
            if (opts&&opts.alphaFirst) num="";
            alp+=str[i];
        } else {
            spc+=str[i];
            if (opts&&opts.specialStop&&spc.length>=opts.specialStop) break;
        }
    }
    return {alpha:alp,number:num,special:spc};
}
var okBeep = new Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");
var tskBeep = new Audio("data:audio/wav;base64,UklGRhwMAABXQVZFZm10IBAAAAABAAEAgD4AAIA+AAABAAgAZGF0Ya4LAACAgICAgICAgICAgICAgICAgICAgICAgICAf3hxeH+AfXZ1eHx6dnR5fYGFgoOKi42aloubq6GOjI2Op7ythXJ0eYF5aV1AOFFib32HmZSHhpCalIiYi4SRkZaLfnhxaWptb21qaWBea2BRYmZTVmFgWFNXVVVhaGdbYGhZbXh1gXZ1goeIlot1k6yxtKaOkaWhq7KonKCZoaCjoKWuqqmurK6ztrO7tbTAvru/vb68vbW6vLGqsLOfm5yal5KKhoyBeHt2dXBnbmljVlJWUEBBPDw9Mi4zKRwhIBYaGRQcHBURGB0XFxwhGxocJSstMjg6PTc6PUxVV1lWV2JqaXN0coCHhIyPjpOenqWppK6xu72yxMu9us7Pw83Wy9nY29ve6OPr6uvs6ezu6ejk6erm3uPj3dbT1sjBzdDFuMHAt7m1r7W6qaCupJOTkpWPgHqAd3JrbGlnY1peX1hTUk9PTFRKR0RFQkRBRUVEQkdBPjs9Pzo6NT04Njs+PTxAPzo/Ojk6PEA5PUJAQD04PkRCREZLUk1KT1BRUVdXU1VRV1tZV1xgXltcXF9hXl9eY2VmZmlna3J0b3F3eHyBfX+JgIWJiouTlZCTmpybnqSgnqyrqrO3srK2uL2/u7jAwMLFxsfEv8XLzcrIy83JzcrP0s3M0dTP0drY1dPR1dzc19za19XX2dnU1NjU0dXPzdHQy8rMysfGxMLBvLu3ta+sraeioJ2YlI+MioeFfX55cnJsaWVjXVlbVE5RTktHRUVAPDw3NC8uLyknKSIiJiUdHiEeGx4eHRwZHB8cHiAfHh8eHSEhISMoJyMnKisrLCszNy8yOTg9QEJFRUVITVFOTlJVWltaXmNfX2ZqZ21xb3R3eHqAhoeJkZKTlZmhpJ6kqKeur6yxtLW1trW4t6+us7axrbK2tLa6ury7u7u9u7vCwb+/vr7Ev7y9v8G8vby6vru4uLq+tri8ubi5t7W4uLW5uLKxs7G0tLGwt7Wvs7avr7O0tLW4trS4uLO1trW1trm1tLm0r7Kyr66wramsqaKlp52bmpeWl5KQkImEhIB8fXh3eHJrbW5mYGNcWFhUUE1LRENDQUI9ODcxLy8vMCsqLCgoKCgpKScoKCYoKygpKyssLi0sLi0uMDIwMTIuLzQ0Njg4Njc8ODlBQ0A/RUdGSU5RUVFUV1pdXWFjZGdpbG1vcXJ2eXh6fICAgIWIio2OkJGSlJWanJqbnZ2cn6Kkp6enq62srbCysrO1uLy4uL+/vL7CwMHAvb/Cvbq9vLm5uba2t7Sysq+urqyqqaalpqShoJ+enZuamZqXlZWTkpGSkpCNjpCMioqLioiHhoeGhYSGg4GDhoKDg4GBg4GBgoGBgoOChISChISChIWDg4WEgoSEgYODgYGCgYGAgICAgX99f398fX18e3p6e3t7enp7fHx4e3x6e3x7fHx9fX59fn1+fX19fH19fnx9fn19fX18fHx7fHx6fH18fXx8fHx7fH1+fXx+f319fn19fn1+gH9+f4B/fn+AgICAgH+AgICAgIGAgICAgH9+f4B+f35+fn58e3t8e3p5eXh4d3Z1dHRzcXBvb21sbmxqaWhlZmVjYmFfX2BfXV1cXFxaWVlaWVlYV1hYV1hYWVhZWFlaWllbXFpbXV5fX15fYWJhYmNiYWJhYWJjZGVmZ2hqbG1ub3Fxc3V3dnd6e3t8e3x+f3+AgICAgoGBgoKDhISFh4aHiYqKi4uMjYyOj4+QkZKUlZWXmJmbm52enqCioqSlpqeoqaqrrK2ur7CxsrGys7O0tbW2tba3t7i3uLe4t7a3t7i3tre2tba1tLSzsrKysbCvrq2sq6qop6alo6OioJ+dnJqZmJeWlJKSkI+OjoyLioiIh4WEg4GBgH9+fXt6eXh3d3V0c3JxcG9ubWxsamppaWhnZmVlZGRjYmNiYWBhYGBfYF9fXl5fXl1dXVxdXF1dXF1cXF1cXF1dXV5dXV5fXl9eX19gYGFgYWJhYmFiY2NiY2RjZGNkZWRlZGVmZmVmZmVmZ2dmZ2hnaGhnaGloZ2hpaWhpamlqaWpqa2pra2xtbGxtbm1ubm5vcG9wcXBxcnFycnN0c3N0dXV2d3d4eHh5ent6e3x9fn5/f4CAgIGCg4SEhYaGh4iIiYqLi4uMjY2Oj5CQkZGSk5OUlJWWlpeYl5iZmZqbm5ybnJ2cnZ6en56fn6ChoKChoqGio6KjpKOko6SjpKWkpaSkpKSlpKWkpaSlpKSlpKOkpKOko6KioaKhoaCfoJ+enp2dnJybmpmZmJeXlpWUk5STkZGQj4+OjYyLioqJh4eGhYSEgoKBgIB/fn59fHt7enl5eHd3dnZ1dHRzc3JycXBxcG9vbm5tbWxrbGxraWppaWhpaGdnZ2dmZ2ZlZmVmZWRlZGVkY2RjZGNkZGRkZGRkZGRkZGRjZGRkY2RjZGNkZWRlZGVmZWZmZ2ZnZ2doaWhpaWpra2xsbW5tbm9ub29wcXFycnNzdHV1dXZ2d3d4eXl6enp7fHx9fX5+f4CAgIGAgYGCgoOEhISFhoWGhoeIh4iJiImKiYqLiouLjI2MjI2OjY6Pj46PkI+QkZCRkJGQkZGSkZKRkpGSkZGRkZKRkpKRkpGSkZKRkpGSkZKRkpGSkZCRkZCRkI+Qj5CPkI+Pjo+OjY6Njo2MjYyLjIuMi4qLioqJiomJiImIh4iHh4aHhoaFhoWFhIWEg4SDg4KDgoKBgoGAgYCBgICAgICAf4CAf39+f35/fn1+fX59fHx9fH18e3x7fHt6e3p7ent6e3p5enl6enl6eXp5eXl4eXh5eHl4eXh5eHl4eXh5eHh3eHh4d3h4d3h3d3h4d3l4eHd4d3h3eHd4d3h3eHh4eXh5eHl4eHl4eXh5enl6eXp5enl6eXp5ent6ent6e3x7fHx9fH18fX19fn1+fX5/fn9+f4B/gH+Af4CAgICAgIGAgYCBgoGCgYKCgoKDgoOEg4OEg4SFhIWEhYSFhoWGhYaHhoeHhoeGh4iHiIiHiImIiImKiYqJiYqJiouKi4qLiouKi4qLiouKi4qLiouKi4qLi4qLiouKi4qLiomJiomIiYiJiImIh4iIh4iHhoeGhYWGhYaFhIWEg4OEg4KDgoOCgYKBgIGAgICAgH+Af39+f359fn18fX19fHx8e3t6e3p7enl6eXp5enl6enl5eXh5eHh5eHl4eXh5eHl4eHd5eHd3eHl4d3h3eHd4d3h3eHh4d3h4d3h3d3h5eHl4eXh5eHl5eXp5enl6eXp7ent6e3p7e3t7fHt8e3x8fHx9fH1+fX59fn9+f35/gH+AgICAgICAgYGAgYKBgoGCgoKDgoOEg4SEhIWFhIWFhoWGhYaGhoaHhoeGh4aHhoeIh4iHiIeHiIeIh4iHiIeIiIiHiIeIh4iHiIiHiIeIh4iHiIeIh4eIh4eIh4aHh4aHhoeGh4aHhoWGhYaFhoWFhIWEhYSFhIWEhISDhIOEg4OCg4OCg4KDgYKCgYKCgYCBgIGAgYCBgICAgICAgICAf4B/f4B/gH+Af35/fn9+f35/fn1+fn19fn1+fX59fn19fX19fH18fXx9fH18fXx9fH18fXx8fHt8e3x7fHt8e3x7fHt8e3x7fHt8e3x7fHt8e3x7fHt8e3x8e3x7fHt8e3x7fHx8fXx9fH18fX5+fX59fn9+f35+f35/gH+Af4B/gICAgICAgICAgICAgYCBgIGAgIGAgYGBgoGCgYKBgoGCgYKBgoGCgoKDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KCgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGBgYCBgIGAgYCBgIGAgYCBgIGAgYCBgIGAgYCBgIGAgYCAgICBgIGAgYCBgIGAgYCBgIGAgYCBgExJU1RCAAAASU5GT0lDUkQMAAAAMjAwOC0wOS0yMQAASUVORwMAAAAgAAABSVNGVBYAAABTb255IFNvdW5kIEZvcmdlIDguMAAA");
var noBeep = new Audio("data:audio/mpeg;base64,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jDrDxMEsJox2wvDFHP/7YGQRDNM+Skqb2lJoIsOZIxgliQtFKSpvbKXgJ5AlTACKHBNMUgE04q4SyKTMdTMgYECESmIehnYoAzrdB4SyfCimX5ryGap4xfuyS9lR4K48Lj0VSFWOPdOR//////////////wY8PQJwAQD0nKPOTljdixBhVtivQi7JYM35dBJgGy4V//////+oGcAAAwKASDATC4MJB3UxgTrTNUFRMNcJ40VlPDij4Wc7wzET2CkloQ6ZEoekSJKKthhoG0GlfsRgmE5eu2Y7KcDhFzuLrkQlikp/UZ8b////////////+N8aFuM//v1+n+D+CoAJ34ATBgmALmECGMZoSaRg8k0GP/7YGQKDNKwSku72hFoF8AZkwQAAQplKSpvcEPgHRAlTACLBKKBkYOwChlsIPmHbkm6BmOLjwOcEItIZowsFaI1WfxflR2c51hmDPHqIe4CBAQ6ArFAbUbT/////////////9Qvg3eAAwGFQ4EXWtR/MXp2JOoU7//////+RC3ABIMBoBswABlTX3GpMXYfE4MLTD5FT+NREUx+VRISjA6HgVBRdxHFMiDWNKwNn27TXJPnkCY4tjFZwRsyDUigF0qf3jeP////////////+C8GBeDo5F+P41UAAAktwESA4HAwchLTXuQNMEUKY8M8MZJgoVCJJMRPW5mBiDJMFGYPUWVrhv/7YGQWCALOSsrT2xD4IeQJQwQCkwulKyjvaEPow4qlNBAWRJMCe3EX+1fxCIEOo6Zi4VXVl77eX8b///////X09Komn/1eyHZ0UGO4d1qFbbwpwCSPn+X/P20/f//H///9tewUYv63jmDxU2lvYLMXQ2xQW/pESA4HgwNRNDUFWwMGUKY73cbOiEUEWgDPZeWtTxpVUb8qYlEmCXtTEEy3mw05odkudoJxpvjT+ay9KdN/6fv/TevVHb3To3fvSnayLRAZVaw/dK7721sju+9oAlCIMP2yIgWxEkM3s+XmXm3erBpXT4+pdV3Zp6r2Rc3jKnXXMZcMaKlKbc3Wq2xEwgAADf/7cGQBiYL1SsnT2ij6LSAZjQAAAQr0eyVO6EPgw41kaBAILHHbIAMCQBIwcSSzLoYnMNQUcH4B1G4gu8NK5THHTTe32Oz2ahMFKaWOS9/bePiQsxYm24PxoaPGBX/1jxv//7f87el/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");
//var toneBeep = new Audio('data:audio/ogg;base64,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');
function beep(isGood) {
    if (isGood===undefined || isGood===true)
        okBeep.play();
    else noBeep.play();
}
function addPipes(elem) {
    if (elem.tagName==="TABLE") {
        fee(elem.getElementsByTagName("th"),cell=>{
            cell.appendChild(ecrea({eName:"SPAN",className:"pipe",eText:cell.nextElementSibling?"|":"\n"}));
        });
        fee(elem.getElementsByTagName("td"),cell=>{
            cell.appendChild(ecrea({eName:"SPAN",className:"pipe",eText:cell.nextElementSibling?"|":"\n"}));
        });
    } else {
        ;
    }
}
function delPipes(elem) {
    fee(lbycn("pipe",elem),ekil);
}
function htmlTableToArray(elem, hasHeaders, separator) {
    conlog("INI | htmlTableToArray ",elem,(hasHeaders?", hasHeaders":"")+", separator='"+separator+"'");
    let csv=[];
    if (elem) {
        if (typeof elem==="string") {
            elem=ebyid(elem);
            if (!elem) return false;
        }
        if (!["TABLE","TBODY","THEAD"].includes(elem.tagName)) return false;
        if (!separator||(typeof separator!=="string")) separator=",";
        for (let child of elem.children) {
            //console.log(elem.tagName+">"+child.tagName);
            if (child.tagName==="THEAD" && hasHeaders) {
                if (csv.length>0)
                    csv=[ ...csv, ...htmlTableToArray(child, true, separator)];
                else csv=htmlTableToArray(child, true, separator);
            }
            if (child.tagName==="TBODY") {
                if (csv.length>0)
                    csv=[ ...csv, ...htmlTableToArray(child, false, separator)];
                else csv=htmlTableToArray(child, false, separator);
            }
            if (child.tagName==="TR") {
                const row=[];
                for (let cell of child.children) {
                    if (["TD","TH"].includes(cell.tagName)) {
                        let data="";
                        if (cell.value) data=cell.value;
                        else if (cell.hasAttribute("value")) data=cell.getAttribute("value");
                        else data=cell.textContent;
                        if (data.length>0) data='"'+data.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ').replace(/"/g, '""')+'"';
                        row.push(data);
                    }
                }
                csv.push(row.join(separator));
            }
        }
    }
    //if (elem.tagName!=="TABLE") console.log("LIST",csv);
    return csv;
}
function arrayToCSVFile(arr,filename,addDate,nocheck) {
    if (!filename) filename = "reporteDatos";
    conlog("INI | arrayToCSVFile arr("+arr.length+"), filename='"+filename+"'"+(addDate?", addDate":"")+(nocheck?", nocheck":""));
    if (arr.length>0) {
        let csv_string="";
        if (nocheck) csv_string=arr.join('\n');
        else {
            const fix=[];
            for(let i=0; i<arr.length; i++) {
                const item=arr[i];
                const itype=(typeof item);
                if (itype === "string" || item instanceof String)
                    fix.push(arr[i]);
                else if (Array.isArray(arr[i]))
                    fix.push(arr[i].join(separator));
                else if (itype === "object") {
                    if (item.hasOwnProperty("toString")) fix.push(item);
                    else fix.push(JSON.stringify(item));
                } else if (itype === "boolean") fix.push(item?"true":"false");
                else if (["number","bigint"].includes(itype)) fix.push(""+item);
                else if (itype === "symbol") fix.push("[symbol]");
                else if (itype === "function") fix.push("[function]");
                else fix.push("");
            }
            csv_string=fix.join('\n');
        }
        // Download it
        if (addDate) filename+=strftime("%T"); //new Date().toLocaleDateString();
        filename += ".csv";
        const link = document.createElement('a');
        link.style.display = 'none';
        link.setAttribute('target', '_blank');
        link.setAttribute('href', 'data:text/csv;charset=utf-8,%EF%BB%BF' + encodeURIComponent(csv_string));
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        return true;
    }
    return false;
}
function sortHtmlTable(elem,columnNumbers,sortDown) {
    if (elem) {
        if (typeof elem==="string") {
            elem=ebyid(elem);
            if (!elem) return false;
        }
        if (elem.tagName==="TABLE") {
            const aux=elem.getElementsByTagName("TBODY");
            if (aux.length>0) return sortHtmlTable(aux[0],columnNumbers);
            if (elem.firstElementChild.tagName!=="TR") return false;
        } else if (elem.tagName!=="TBODY") return false;
        if (!columnNumbers) columnNumbers=[0];
        else if (!Array.isArray(columnNumbers)) columnNumbers=[columnNumbers];
        const rows=elem.rows;//getElementsByTagName("TR");
        var sortMod=(sortDown?-1:1);
        [].slice.call(rows).sort(function(a,b){
            let atxt=a.cells[columnNumbers[0]-1].textContent;
            let btxt=b.cells[columnNumbers[0]-1].textContent;
            if (!isNaN(atxt)) atxt=+atxt;
            if (!isNaN(btxt)) btxt=+btxt;
            return (
                atxt<btxt?-1*sortMod:
                atxt>btxt?sortMod:0
                ); // toDo: if 0 compare second columnNumber...etc
        }).forEach((v,i)=>elem.appendChild(v));
    }
}
// SHORTCUTS
function fee(arrayLike, elemCallback, doSome) { // for each class element: callback(elem[,index,array])
    if (Array.from) {
        if (doSome) Array.from(arrayLike).some(elemCallback);
        else Array.from(arrayLike).forEach(elemCallback);
    } else if (doSome) [].some.call(arrayLike, elemCallback);
    else [].forEach.call(arrayLike, elemCallback);
}
function ebyid(id) { // element by id
    return document.getElementById(id);
}
function lbycn(classname,baseElement,index) { // list by class name
    if (!baseElement) baseElement=document;
    if (Array.isArray(classname)) {
        const resPack=[];
        fee(classname,oneclass=>{let res=lbycn(oneclass,baseElement,index);if(res)fee(res,ares=>{if(!resPack.includes(ares))resPack.push(ares);})});
        return resPack;
    }
    const result=baseElement.getElementsByClassName(classname);
    if (typeof index === 'undefined') return result;
    if (result.length>index) return result[index];
    else return false;
}
function lastElemObjAppend(elemobj, text) {
    if (elemobj && text) {
        let lastElem=elemobj.pop();
        if (!lastElem) lastElem={eText:""};
        else if (!lastElem.eText) lastElem.eText="";
        lastElem.eText+=text;
        elemobj.push(lastElem);

        //let len=elemobj.size();
        //elemobj[len-1].eText+=text;
    }
}
function clfunc(elem,classname,funcname,params) {
    if (typeof elem==="string") elem=ebyid(elem);
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let count=0; elem.forEach(subelem=>{ count+=clfunc(subelem,classname,funcname,params); }); return count;
        } if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let count=0; for (let n=0; n<elem.length; n++) { count+=clfunc(elem[n],classname,funcname,params); } return count;
        }
        if (elem.classList) {
            let neg=false;
            let sFuncname=false;
            if (funcname==="add") { funcname="contains"; sFuncname="add"; neg=true; }
            else if (funcname==="remove") { funcname="contains"; sFuncname="remove"; }
            else if (funcname==="set") {
                if (typeof params==="boolean") funcname="toggle";
                else return 0;
            } else if (funcname==="replace") {
                let retval=0;
                let baseLen=elem.classList.length;
                if (Array.isArray(classname)) elem.classList.remove(...classname);
                else elem.classList.remove(classname);
                retval=baseLen-elem.classList.length;
                baseLen=elem.classList.length;
                if (Array.isArray(params)) elem.classList.add(...params);
                else elem.classList.add(params);
                retval+=elem.classList.length-baseLen;
                return retval;
            }
            if (Array.isArray(classname)) {
                let count=0; fee(classname,cn=>{
                    const args=[cn]; if (typeof params!=="undefined") { if (Array.isArray(params)) args.push(...params); else args.push(params); }
                    if (!elem.classList[funcname](...args) != !neg) {
                        if (sFuncname) {
                            elem.classList[sFuncname](...args);
                            //console.log("+*",sFuncname,"(",...args,")",elem);
                        } //else console.log("+|",funcname,"(",...args,")",elem);
                        count++;
                    }
                }); return count;
            }
            const args=[classname]; if (typeof params!=="undefined") { if (Array.isArray(params)) args.push(...params); else args.push(params); }
            let retval=elem.classList[funcname](...args);
            if (neg) retval=!retval;
            if (retval && sFuncname) {
                elem.classList[sFuncname](...args);
                //console.log(".*",sFuncname,"(",...args,")",elem);
            } //else if (retval) console.log(".|",funcname,"(",...args,")",elem);
            return retval?1:0;
        }
    }
    return 0;
}
function clhas(elem,classname, ...params) { return clfunc(elem,classname,"contains",params); }
function clfix(elem,classname, ...params) { return clfunc(elem,classname,"toggle",params); }
function cladd(elem,classname, ...params) { return clfunc(elem,classname,"add",params); }
function clrem(elem,classname, ...params) { return clfunc(elem,classname,"remove",params); }
function clset(elem,classname, boolval) { return clfunc(elem,classname,"toggle",boolval); }
function clswt(elem,classname1, classname2) { return clfunc(elem,classname1,"replace",classname2); }
function epar(elem, depth) {
    if (elem) {
        if (depth && depth>1) return epar(elem.parentNode,depth-1);
        return elem.parentNode;
    }
    return false;
}
function tpar(elem,tagName) {
    if (elem) {
        if (elem.tagName===tagName) return elem;
        return tpar(elem.parentNode,tagName);
    }
    return false;
}
function ekil(elem) {
    //conlog("INI function ekil",elem);
    if(elem) {
        if (typeof elem==="string") {
            elem=ebyid(elem);
            if (!elem) return;
        }
        conlog("ELEM exists");
        if(elem.parentNode) {
            conlog("PARENT NODE removeChild");
            elem.parentNode.removeChild(elem);
        } else {
            conlog("delete elem");
            delete elem;
        }
    }
    //conlog("END function ekil",elem);
}
function ekfil(elem, exceptionList) {
    //console.log("INI function ekfil",elem,exceptionList);
    if (elem) {
        if (typeof elem==="string") {
            elem=ebyid(elem);
            if (!elem) return;
        }
        let tgtChild=elem.firstChild;
        while (tgtChild) {
            let nextChild=tgtChild.nextSibling;
            if (!exceptionList || (!exceptionList.includes(tgtChild) && !exceptionList.includes(tgtChild.id)))
                elem.removeChild(tgtChild);
            tgtChild=nextChild;
        }
    }
}
function evl(elem,dfvl) {
    if (!dfvl) dfvl="";
    if (elem && elem.value) return elem.value;
    return dfvl;
}
function ecrea(props) {
    if (isElement(props)) return props;
    if (Array.isArray(props)) {
        const arr=[];
        props.forEach(function(elem) {arr.push(ecrea(elem));});
        return arr;
    }
    let propNames=Object.keys(props);
    if (props.eName) {
        let idx=propNames.indexOf("eName");
        if (idx>=0) propNames.splice(idx,1);
        idx=propNames.indexOf("eText");
        if (idx>=0) propNames.splice(idx,1);
        idx=propNames.indexOf("eComment");
        if (idx>=0) propNames.splice(idx,1);
        idx=propNames.indexOf("eChilds");
        if (idx>=0) propNames.splice(idx,1);
        let newObj=document.createElement(props.eName);
        for(let i=0;i<propNames.length;i++) {
            newObj[propNames[i]]=props[propNames[i]];
        }
        if (props.eChilds) {
            if (Array.isArray(props.eChilds)) for (let i=0; i<props.eChilds.length; i++) {
                let child = ecrea(props.eChilds[i]);
                if (child) newObj.appendChild(child);
            } else {
                let child = ecrea(props.eChilds);
                if (child) newObj.appendChild(child);
            }
        } else if (props.eText) newObj.appendChild(document.createTextNode(props.eText));
        if (props.eComment) newObj.appendChild(document.createComment(props.eComment));
        return newObj;
    } else if (props.eText) {
        let newObj=document.createTextNode(props.eText);
        return newObj;
    } else if (props.eComment) {
        let newObj=document.createComment(props.eComment);
        return newObj;
    }
    return null;
}
function switchAHref(elem, newHref) {
    //funclog("INI","function switchAHref "+(elem.tagName?"<"+elem.tagName+">":"invalid!")+" => "+newHref);
    if (elem.tagName!=="A") return;
    let oldHref=elem.href;
    elem.href=newHref;
    setTimeout(function(a,href) {a.href=href;},100,elem,oldHref);
}
function setAHref(elem,href) {
    //funclog("INI","function setAHref "+(elem.tagName?"<"+elem.tagName+">":"invalid!")+" => "+href);
    if (elem.tagName!=="A") return;
    elem.href=href;
    setTimeout(function(a) {delete a.href;a.removeAttribute("href");},100,elem);
}
function addProps(nodeObj, properties, exceptions) {
    if (nodeObj===null) return;
    if (exceptions === undefined || exceptions===false || exceptions===null) exceptions=[];
    if (typeof properties === "object" && properties!==null)
        for (let key in properties) {
            if (properties.hasOwnProperty(key) && !exceptions.includes[key]) {
                if (typeof properties[key]==="function") {
                    if (key==="onbuild") {
                        properties[key](nodeObj);
                    } else if (key.slice(0,2)==="on") {
                        nodeObj[key]=properties[key];
                    } else {
                        const retval=properties[key](nodeObj);
                        if (retval !== undefined && retval !== null) {
                            const typf = typeof retval;
                            if (typf!=="undefined"&&typf!=="boolean"&&typf!=="object") // &&typf!=="function"
                                nodeObj[key]=retval;
                        }
                    }
                } else {
                    nodeObj[key]=properties[key];
                }
            }
        }
}
function addElemToCellObj(cellObj, elem, key, isArrayAsTable, keys, tableProperties, rowProperties, cellProperties) {
    if (elem === undefined || elem === null || typeof elem === "undefined" || typeof elem === "boolean" || typeof elem === 'function') return;
    //appendLog("INI function addElemToCellObj "+(cellObj.eName??cellObj.eText??"cellObj")+(cellObj.id?"#"+cellObj.id:cellObj.name?"[name='"+cellObj.name+"']":"")+" <= "+(elem.eName??elem.eText??"elem")+(elem.id?"#"+elem.id:elem.name?"[name='"+elem.name+"']":""));
    if (Array.isArray(elem)) {
        if (!cellObj.eChilds) cellObj.eChilds=[];
        if(isArrayAsTable) cellObj.eChilds.push(arrayToHTMLTableObject(elem, keys, tableProperties, rowProperties, cellProperties));
        else elem.forEach(function(item) { addElemToCellObj(cellObj, item, key); });
    } else if (typeof elem === 'object') {
        //conlog("addElemToCellObj ",elem,", "+key);
        if (isElemObj(elem)) {
            if (!cellObj.eChilds) cellObj.eChilds=[];
            cellObj.eChilds.push(elem);
        } else addProps(cellObj, elem, ["eName","eChilds","eText"]);
    } else {
        //conlog("addElemToCellObj ",elem,", "+key);
        cellObj.eText=""+elem;
    }
}
function nextIndex(parentNode) {
    let idx="";
    if (parentNode.index && parentNode.index.length>0)
        idx=parentNode.index+".";
    if (parentNode.eChilds)
        idx+=parentNode.eChilds.length;
    return idx;
}
function addElemToRowObj(rowObj, elem, key, cellType, cellProperties, restrictedKeys) {
    //appendLog("INI function addElemToRowObj "+(rowObj.eName??rowObj.eText??"rowObj")+" <= "+(elem.eName??elem.eText??"elem"));
    if (isElemObj(elem) && (elem.eName==="TH"||elem.eName==="TD")) {
        addProps(elem, cellProperties, restrictedKeys);
        rowObj.eChilds.push(elem);
        if(rowObj.onappend) {
            //appendLog("addElemToRowObj calls function onappend1");
            rowObj.onappend(rowObj,elem);
        }
    } else {
        if (cellType!=="TH") cellType="TD";
        const cellObj={eName:cellType, index:nextIndex(rowObj)};
        addProps(cellObj, cellProperties, restrictedKeys);
        addElemToCellObj(cellObj, elem, key, false);
        rowObj.eChilds.push(cellObj);
        if(rowObj.onappend) {
            //appendLog("addElemToRowObj calls function onappend2");
            rowObj.onappend(rowObj,cellObj);
        }
    }
}
function arrayToHTMLTableObject(array, keys, tableProperties, rowProperties, cellProperties, idSuffix) {
    //appendLog("INI function arrayToHTMLTableObject"+(keys?" ["+keys.toString()+"]":""));
    if (array) {
        const restrictedKeys=["eName","eChilds","eText"];
        const tabObj={eName:"TABLE"};
        addProps(tabObj, tableProperties, restrictedKeys);
        const hRowObj={eName:"TR", index:"H", eChilds:[]};
        addProps(hRowObj, rowProperties, restrictedKeys);
        const colNames=keys?keys:[];
        colNames.forEach(function(cname) { addElemToRowObj(hRowObj,{eName:"TH", index:nextIndex(hRowObj), eText:cname},cname,false,cellProperties,restrictedKeys); });
        const headObj={eName:"THEAD",eChilds:[hRowObj]};
        const bodyObj={eName:"TBODY",index:"B",eChilds:[]};
        //const footObj={eName:"TFOOT"};
        array.forEach(function(obj) {
            const rowObj={eName:"TR", index:nextIndex(bodyObj), eChilds:[]};
            addProps(rowObj, rowProperties, restrictedKeys);
            if (keys && keys.length>0) {
                keys.forEach(function (key) {
                    if (key in obj && obj.hasOwnProperty(key) && typeof obj[key] !== "function") {
                        //if (cellProperties.ongetvalue) appendLog("arrayToHTMLTableObject calls function ongetvalue {"+key+":"+obj[key]+"}");
                        const val=(cellProperties.ongetvalue?cellProperties.ongetvalue(obj,key):obj[key]);
                        addElemToRowObj(rowObj, val, key, "TD", cellProperties, restrictedKeys);
                    } else if (cellProperties.onmissedkey) {
                        //appendLog("arrayToHTMLTableObject calls function onmissedkey {"+key+":"+obj[key]+"}");
                        const val=cellProperties.onmissedkey(obj,key);
                        if (val) { addElemToRowObj(rowObj, val, key, "TD", cellProperties, restrictedKeys); }
                        else { addElemToRowObj(rowObj, {eName:"TD", index:nextIndex(rowObj)}, key, false, cellProperties, restrictedKeys); }
                    } else { addElemToRowObj(rowObj, {eName:"TD", index:nextIndex(rowObj)}, key, false, cellProperties, restrictedKeys); }
                });
            } else {
                //appendLog("arrayToHTMLTableObject no keys");
                if (colNames.length>0) colNames.forEach(function (colk) {
                    if (colk in obj && obj.hasOwnProperty(colk) && typeof obj[colk] !== "function") {
                        if (obj[colk].eName && obj[colk].eName==="TD") { // isElemObj(obj[colk])
                            rowObj.eChilds.push(obj[colk]);
                        } else {
                            const cellObj={eName:"TD", index:nextIndex(rowObj)};
                            addElemToCellObj(cellObj, obj[colk], colk, false);
                            rowObj.eChilds.push(cellObj);
                        }
                    } else rowObj.eChilds.push({eName:"TD", index:nextIndex(rowObj)});
                });
                for (let key in obj) {
                    if (obj.hasOwnProperty(key) && typeof obj[key] !== "function" && !colNames.includes(key)) {
                        colNames.push(key);
                        addElemToRowObj(hRowObj, {eName:"TH", index:nextIndex(hRowObj), eText:key}, key, false, cellProperties, restrictedKeys);
                        bodyObj.eChilds.forEach(function (prevRowObj) { prevRowObj.eChilds.push({eName:"TD", index:nextIndex(prevRowObj)}); });
                        addElemToRowObj(rowObj, obj[key], key, "TD", cellProperties, restrictedKeys);
                    }
                }
            }
            bodyObj.eChilds.push(rowObj);
        });
        tabObj["eChilds"]=[headObj, bodyObj];
        return tabObj;
    }
    return null;
}
// STORAGE
if (!Storage.prototype.get) {
    Storage.prototype.get = function(k) {
        const val=this.getItem(k);
        return new Promise((resolve,reject)=>{
            setTimeout((v)=>resolve(v),8,val);
        });
    };
}
if (!Storage.prototype.set) {
    Storage.prototype.set = function(k, v) {
            this.setItem(k,v);
    };
}
function addToStoredArray(key, item, storage) {
    storage.get(key).then(val => {
        const arr = val?JSON.parse(val):[];
        arr.push(item);
        storage.set(key, JSON.stringify(arr));
    });
}
function getFromStoredArray(key, storage, startIdx, num) {
    let resultVal="";
    return storage.get(key).then(val => {
        const arr = val?JSON.parse(val):[];
        if (!startIdx && startIdx!==0) startIdx=-1;
        if (!num && num!==0) num=1;
        if (num===0) resultVal=arr.slice(startIdx, startIdx+1);
        else {
            resultVal=arr.splice(startIdx, num);
            storage.set(key, JSON.stringify(arr));
        }
        if (resultVal.length==1) resultVal=resultVal[0];
        return new Promise((resolve,reject)=>{setTimeout((v)=>resolve(v),8,resultVal);});
    });
}
// TOOLTIP
var tooltipTimeout=null;
function viewTooltip(evt) {
    const tgt=evt.target;
    conlog("INI function viewTooltip");
    if (tgt) {
        conlog("Target: ",tgt);
        clearTimeout(tooltipTimeout);
        if (tgt.tooltip) {
            clrem(tgt.tooltip,"enterTooltip");
            conlog("END function viewTooltip: Found tooltip");
            return;
        }
        let tooltipText=tgt.getAttribute("tooltip");
        let tooltipObj=false;
        try {
            tooltipText = tooltipText.replace(/\\n/g, "\\n")  
               .replace(/\\'/g, "\\'")
               .replace(/\\"/g, '\\"')
               .replace(/\\&/g, "\\&")
               .replace(/\\r/g, "\\r")
               .replace(/\\t/g, "\\t")
               .replace(/\\b/g, "\\b")
               .replace(/\\f/g, "\\f")
               .replace(/[\u0000-\u0019]+/g,"");
            conlog("TooltipText='"+tooltipText+"'");
            tooltipObj=JSON.parse(tooltipText);
            if ("eName" in tooltipObj || "eText" in tooltipObj) {
                tgt.tooltip=ecrea(tooltipObj);
                cladd(tgt.tooltip,"tooltip");
                conlog("Found and added tooltip object");
            } else conlog("Tooltip without eName or eText");
        } catch(ex) {
            conlog("Tooltip not JSON: ",ex);
        }
        if (!tgt.tooltip) {
            conlog("Not found, added tooltip text");
            tgt.tooltip=ecrea({eName:"DIV",className:"tooltip",eText:tooltipText});
        }
        tgt.tooltip.onmouseenter=function(event){cladd(event.target,"enterTooltip");};
        tgt.tooltip.onmouseleave=function(event){hideTooltip(event);};
        tgt.appendChild(tgt.tooltip);
        tgt.onmousemove=function(event){dragTooltip(event);};
        tgt.onmouseleave=function(event){hideTooltip(event);};
    }
    conlog("END function viewTooltip");
}
function hideTooltip(evt) { // eliminar si no esta sobre el tooltip y agregar onmouseleave al tooltip
    conlog("INI function hideTooltip: ",evt.target);
    tooltipTimeout=setTimeout(function(tgt){
        if (tgt) {
            conlog("INI timeout hideTooltip has target");
            if (clhas(tgt,"enterTooltip")&&tgt===tgt.parentNode.tooltip) {
                clrem(tgt,"enterTooltip");
                tgt=tgt.parentNode;
            }
            if (tgt.tooltip && !clhas(tgt.tooltip,"enterTooltip")) {
                tgt.removeChild(tgt.tooltip);
                tgt.tooltip=false;
                delete tgt.tooltip;
                tgt.onmousemove=false; // null;
                delete tgt.onmousemove;
            }
            conlog("END timeout hideTooltip with target");
        } else conlog("SET timeout hideTooltip no target");
        tooltipTimeout=null;
    },100,evt.target);
}
function dragTooltip(evt) {
    conlog("INI function dragTooltip: "+evt.clientX+","+evt.clientY);
    const tgt=evt.target;
    if (tgt && tgt.tooltip) {
        tgt.tooltip.style.top=(evt.clientY+10)+"px";
        tgt.tooltip.style.left=(evt.clientX-10)+"px";
        conlog("END function dragTooltip: "+tgt.tooltip.style.left+","+tgt.tooltip.style.top);
    } else conlog("END function dragTooltip: No tgt or tooltip",tgt);
}
var backdropCloseable=true;
var backdropContinueAfterClose=false;
var backdropObj={eName:"DIV",/*className*/id:"backdrop",eChilds:[],onclick:function(evt){if (backdropCloseable) closeBackdrop();return backdropContinueAfterClose||eventCancel(evt);}};
var backdropTimeout=0;
var backdropCloseCallback=null;
var backdropResizeFunc=null;
/*
function setBackdropContinueAfterClose(val) {
    backdropContinueAfterClose=val;
    let classList=(backdropObj.className&&backdropObj.className.length>0?backdropObj.className.split(" "):[]);
    let fix=false;
    let isTransparent=classList.includes("transparent");
    if (backdropContinueAfterClose) {
        if (!isTransparent) {
            classList.push("transparent");
            fix=true;
        }
    } else if (isTransparent) {
        classList = classList.filter(value=>value!=="transparent");
        fix=true;
    }
    if (fix) backdropObj.className=classList.join(" ");
}
*/
// remove backdrop childs, backdrop object also removed by default
function clearBackdrop(keepObj) {
    //conlog("INI function clearBackdrop");
    cleanBackdrop();
    ekfil(ebyid("backdrop"));
    if (!keepObj) backdropObj.eChilds=[];
    //conlog("END function clearBackdrop");
}
// remove backdrop element, backdrop object kept by default
function closeBackdrop(removeObj) {
    //conlog("INI function closeBackdrop");
    cleanBackdrop();
    ekil(ebyid("backdrop"));
    if (removeObj) backdropObj.eChilds=[];
    //conlog("END function closeBackdrop");
}
function cleanBackdrop() {
    if (backdropResizeFunc) {
        backdropResizeFunc=null;
    }
    if (backdropCloseCallback) {
        backdropCloseCallback();
        backdropCloseCallback=null;
    }
    clearTimeout(backdropTimeout);
    backdropTimeout=0;
}
// create and append sub-container(div) to backdrop
// traceable by declared id, initially positioned, styled and parameterized
function addBackdropChild(elemId, elemTop, elemLeft, elemWidth, elemHeight, extraParameters, extraClassList) {
    //conlog("INI function addBackdropChild");
    const childObj={eName:"DIV",style:"position:fixed;z-index:8898;",onclick:function(evt){ return eventCancel(evt); }};
    if (elemId) childObj.id=elemId;
    if (elemWidth) childObj.style+="width:"+elemWidth+"px;";
    if (elemHeight) childObj.style+="height:"+elemHeight+"px;";
    if (elemTop) childObj.style+="top:"+elemTop+"px;";
    if (elemLeft) childObj.style+="left:"+elemLeft+"px;";
    if (extraClassList) childObj.style+=extraClassList;
    const exceptions=["eName","style","id"];
    if (extraParameters) for (let key in extraParameters) {
        if (extraParameters.hasOwnProperty(key) && !exceptions.includes[key]) {
            childObj[key]=extraParameters[key];    
        }
    }
    appendBackdropChild(childObj);
    //conlog("END function appendBackdropChild");
    return childObj;
}
// append element to backdrop object and element, if it exists, comparer used for keeping unicity
function appendBackdropChild(childObj, comparer) {
    //conlog("INI function appendBackdropChild");
    if (!comparer) comparer=function(el) {
        if (childObj===el) return true;
        if (childObj.id && el.id && childObj.id===el.id) return true;
        if (JSON.stringify(childObj,jsonCircularReplacer())===JSON.stringify(el,jsonCircularReplacer())) return true;
        return false;
    }
    let isContained=false;
    fee(backdropObj.eChilds,function(ch) {
        if (comparer(ch)) isContained=true;
    });
    const backdrop = ebyid("backdrop");
    let childElem = null;
    if (isContained) {
        if (childObj.id) childElem=ebyid(childObj.id);
        else {
            // sin id es mas complicado encontrar un elemento
            // agregar aquí propiedades que sirvan para identificar un elemento
            // o validar si al convertirlo a JSON.stringify se puede localizar
            // evaluar posibilidad de reingenieria para obtener objeto de un elemento y si valdrá la pena
        }
    } else {
        backdropObj.eChilds.push(childObj);
        childElem=ecrea(childObj);
        if (backdrop) backdrop.appendChild(childElem);
    }
    //conlog("END function appendBackdropChild");
    return childElem;
}
// if backdrop element exists, then show it
// else create it from backdrop object and append it to body
// in the later classes are not changed to show it if it is hidden
function viewBackdrop() {
    //conlog("INI function viewBackdrop");
    const backdrop = ebyid("backdrop");
    if (backdrop) {
        //conlog("BACKDROP TO VISIBLE: ", backdrop);
        clrem(backdrop,"hidden");
        clrem(backdrop,"invisible");
    } else { //if (backdropObj.eChilds.length>0) {
        //conlog("BACKDROP CREATING");
        document.body.appendChild(ecrea(backdropObj));
        //conlog("BACKDROP CREATED");
    }
    //conlog("END function viewBackdrop");
}
function resizeWaitBackdrop() {
    //console.log("INI resizeWaitBackdrop");
    const img=ebyid("waitBackdropImage");
    if (!img) return;
    img.style.display="block";
    var rect = img.getBoundingClientRect();
    const bdWid=document.documentElement.clientWidth;
    const bdHei=document.documentElement.clientHeight;
    if (img.classList.contains("waitExpanded")) {
        //console.log("docWid="+bdWid+", docHei"+bdHei);
        //console.log("imgWid="+rect.width+", imgHei="+rect.height+", top="+rect.top+", left="+rect.left);
        if (bdHei>(rect.height+4)) {
            img.style.top=((bdHei-rect.height)/2)+"px";
            img.style.left="2px";
        } else if (bdHei<(rect.height+4)) {
            const factor=bdHei/(rect.height+4);
            const newWid=rect.width*factor;
            img.style.width=newWid+"px";
            img.style.heigth=(bdHei-2)+"px";
            img.style.top="2px";
            img.style.left=((bdWid-newWid)/2)+"px";
        }
    } else {
        //console.log("calculates size from 316x315");
        const imgWid=316;
        const imgHei=315;
        const fixWid=2*bdWid/3;
        const fixHei=2*bdHei/3;
        const clcWid=imgWid*fixHei/imgHei;
        const clcHei=imgHei*fixWid/imgWid;
        let imgWidR=(clcWid>fixWid)?fixWid:clcWid;
        let imgHeiR=(clcWid>fixWid)?clcHei:fixHei;
        let elemTop=(bdHei-imgHeiR)/2;
        let elemLeft=(bdWid-imgWidR)/2;
        img.style.width=imgWidR+"px";
        img.style.height=imgHeiR+"px";
        img.style.top=elemTop+"px";
        img.style.left=elemLeft+"px";
    }
}
function viewWaitBackdrop() {
    //console.log("INI viewWaitBackdrop");
    if (isShownWheelbox()) return false;
    backdropCloseable=false;
    clearBackdrop();
    const whlbxdv=ebyid("wheelbox");

    let waitImgSrc="imagenes/icons/flying.gif";
    if (_win_) waitImageSrc="imagenes/"+_win_+".gif";
    let waitClass="";
    let waitStyle="position:fixed;z-index:8898;top:0px;left:0px;display:none;";
    if (whlbxdv) {
      for (let e=whlbxdv.firstElementChild; e; e=e.nextElementSibling) {
        if (e.tagName==="IMG") {
            waitImgSrc=e.src;
            waitClass=e.className;
            //waitStyle+="outline:3px solid #a6a;outline-offset:-3px;";
            break;
        }
      }
    }
    //console.log(" waitImage src=\""+waitImgSrc+"\"");
    const waitBDO={eName:"IMG",id:"waitBackdropImage",src:waitImgSrc,style:waitStyle};
    if (waitClass) {
        waitBDO.className=waitClass;
        //console.log(" waitImage className=\""+waitClass+"\" style=\""+waitStyle+"\"");
    }
    showBackdropChild(waitBDO);
    resizeWaitBackdrop();
    backdropResizeFunc=resizeWaitBackdrop;
}
// display backdrop including childObj if it had not beed added before
function showBackdropChild(childObj) {
    if (childObj) {
        if (!childObj.style) {
            let elemTop=0;
            let elemLeft=0;
            childObj.style="position:fixed;z-index:8898;top:"+elemTop+"px;left:"+elemLeft+"px;";
        }
        if (!childObj.onclick) {
            childObj.onclick=function(evt){ return eventCancel(evt); };
        }
    }
    //conlog("INI function showBackdropChild");
    appendBackdropChild(childObj);
    //conlog(backdropObj);
    viewBackdrop();
    //conlog("END function showBackdropChild");
}
function backdropConfirm(message,buttons,wid,hei,confirmCallback) {
    if (!wid) wid=400;
    if (!hei) hei=300;
    if (!confirmCallback) confirmCallback=(function() { conlog('CONFIRMED!');alert("CONFIRMED");clearBackdrop(); });
    if (!buttons) {
        buttons={eName:"DIV",className:"centered",eChilds:[{eName:"INPUT",type:"button",value:"Cancelar",className:"marginV1",onclick:(function() {closeBackdrop(true); })},{eName:"INPUT",type:"button",value:"Aceptar",className:"marginV1",onclick:confirmCallback}]};
    }
    clearBackdrop();
    //const dualScreenLeft = window.screenLeft !==  undefined ? window.screenLeft : window.screenX;
    //const dualScreenTop = window.screenTop !==  undefined   ? window.screenTop  : window.screenY;
    //conlog("dualScreenLeft = "+dualScreenLeft);
    //conlog("dualScreenTop = "+dualScreenTop);

    const winWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    const winHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    conlog("winWidth = "+winWidth);
    conlog("winHeight = "+winHeight);

    const systemZoom = winWidth / window.screen.availWidth;
    conlog("systemZoom = "+systemZoom);
    const left = (winWidth - wid) / 2; // / systemZoom; // + dualScreenLeft;
    const top = (winHeight - hei) / 2; // / systemZoom; // + dualScreenTop;
    conlog("wid="+wid+", hei="+hei+", left="+left+", top="+top);
    addBackdropChild(
        "backdropConfirmBodyElement",
        top,
        left,
        wid,// /systemZoom,
        hei,// /systemZoom,
        {eChilds:[{eName:"DIV",eChilds:[{eName:"DIV",className:"boldValue centered margin5 bgbrown bbtm2d",eText:"CONFIRMAR DATOS"},{eName:"DIV",className:"centered marbtm5",eText:message},buttons]}]},
        "background:white;box-shadow:0 1px 3px 0 rgba(0,0,0,.25);overflow-x: hidden;overflow-y: auto;border-radius: 4px;"
    );
    viewBackdrop();
    backdropObj.eChilds=[];
}
var autoCloseTimeout=false;
function showAutoCloseLine(message, seconds) {
    showAutoCloseObj({eName:"P",id:"autoCloseWindow",eText:message}, seconds);
}
function showAutoCloseBlock(title, elements, seconds) {
    const childs=[{eName:"H2",eText:title}, ...elements];
    showAutoCloseObj({eName:"DIV",id:"autoCloseWindow",eChilds:childs}, seconds);
}
function showAutoCloseObj(hObj, seconds) {
    if (autoCloseTimeout) return;
    if (!seconds) seconds=3000;
    document.body.appendChild(ecrea(hObj));
    clearTimeout(autoCloseTimeout);autoCloseTimeout=setTimeout(function(){ekil(ebyid("autoCloseWindow"));autoCloseTimeout=false;},seconds);
}
var chartCodeNames={infected:"Casos totales", recovered:"Recuperados", dead:"Muertos"}; // , sick:"Enfermos"
var zoneNames={
    "MX":"MEXICO",
    "ISRKMzEDojyrZ9iyWwex":"Aguascalientes",
    "Jp9ePErcc8i1aOozEAMf":"Baja California Norte",
    "xPnSSf9uB9oCJMjypB6v":"Baja California Sur",
    "9DAvxOObTqIvMOU93nlY":"Campeche",
    "rzT7Y44WR7w6IhAjgL3H":"Chiapas",
    "IhhiYI6T0TX4CYVPcnmH":"Chihuahua",
    "61E7TGjUwAo9xu6Y6gAH":"Ciudad de México",
    "CCUFp4urvgXPo5unfMGy":"Coahuila",
    "tWYn4PqXYP2x4QsFfjsB":"Colima",
    "yeCnIs7n45mkXCYykOnY":"Durango",
    "WpCXuQjYFLbozSpEhbrZ":"Estado de México",
    "M3wdiueFtwrUHiQ2rOXA":"Guanajuato",
    "mLBP8zJHPTL7Bauu7tMB":"Guerrero",
    "0XmCRqiK2ZaehoEJvfer":"Hidalgo",
    "Qkho455pBwx88Gohs3hL":"Jalisco",
    "pp7txXROhgKQDjCDQMty":"Morelos",
    "ZQSkzcEvJtBLgGbrf9gH":"Michoacán",
    "rAFoXOP1VxHaUrVoycie":"Nayarit",
    "IdKbgzgKLpjBFh4UPOwu":"Nuevo León",
    "YidRsOGDERjtbaIBGCdu":"Oaxaca",
    "t2PKsYPSPt315Hi5fyfb":"Puebla",
    "Wt97UDm6jfYef6WBrS2w":"Querétaro",
    "ySHBI4EN9xTHupDBvr6R":"Quintana Roo",
    "6vjIVg1gSANFZghV7xtS":"San Luis Potosí",
    "3kFCzJguabtBvXaHxgiu":"Sinaloa",
    "LYCBHrA9ms6GcxpLGPCT":"Sonora",
    "YP3nlz6AKyrnCWYb5mHA":"Tabasco",
    "54cfsdsRF76XVHWtZs19":"Tamaulipas",
    "fOKJ5UT1eN2NZKQb1t7j":"Tlaxcala",
    "jaePeuTZlZsfGeI4zvIZ":"Veracruz",
    "TQCWbhIpJQBQxBjhCGVJ":"Yucatán",
    "UflxTAb4FnAwve7c5MbY":"Zacatecas"
};
function viewChartOptions(chartId) {
    //conlog("INI function viewChartOptions ( "+chartId+" )");
    let switchElem=false;
    const optionsArr=[];
    let size=0;
    switch(chartId) {
        case 1:
            switchElem=ebyid("chartSwitch1");
            size=4;
            for (const code in chartCodeNames) {
                optionsArr.push({
                    eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px 10px 20px;cursor:pointer;",
                    eChilds:[{eName:"DIV",className:"switchColor "+code},{eName:"DIV",className:"switchName",eText:chartCodeNames[code]}],
                    onclick:function(evt) { viewChart(code); }
                });
            }
            break;
        case 2:
            switchElem=ebyid("zoneSwitch1");
            size=5;
            for (const zone in zoneNames) {
                optionsArr.push({
                    eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px 10px 20px;cursor:pointer;",
                    eChilds:[{eName:"DIV",className:"switchName",eText:zoneNames[zone]}],
                    onclick:function(evt) { viewChart(false,zone); }
                });
            }
            break;
        case 3:
            switchElem=ebyid("procSwitch");
            size=3;
            optionsArr.push({eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px;",
                eChilds:[{eName:"DIV",className:"switchName switchHeader",eText:"CONSULTAS AL PORTAL"}]
            });
            optionsArr.push({
                eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px 10px 20px;",
                eChilds:[
                    {eName:"DIV",className:"switchUser switchHeader",eText:"USUARIO"},
                    {eName:"DIV",className:"switchDetail switchHeader",eText:"HOY"},
                    {eName:"DIV",className:"switchDetail switchHeader",eText:"AYER"},
                    //{eName:"DIV",className:"switchDetail switchHeader",eText:"SEMANA"},
                    //{eName:"DIV",className:"switchDetail switchHeader",eText:"MES"},
                    //{eName:"DIV",className:"switchDetail switchHeader",eText:"AÑO"},
                    //{eName:"DIV",className:"switchDetail switchHeader",eText:"TODOS"},
                    {eName:"DIV",className:"switchDetail switchHeader",eText:"CASOS"},
                    {eName:"DIV",className:"switchDetail switchHeader",eText:"MUERTOS"},
                    {eName:"DIV",className:"switchDetail switchHeader",eText:"ENFERMOS"},
                    {eName:"DIV",className:"switchDetail switchHeader",eText:"CURADOS"}]
            });
            let waitImgSrc="imagenes/icons/rollwait2.gif";
            if (_win_) waitImageSrc="imagenes/"+_win_+".gif";
            optionsArr.push({eName:"DIV",class:"deleteWithData",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px;",
                eChilds:[{eName:"IMG",src:waitImgSrc}]
            });
            break;
        default:
    }
    if (switchElem && optionsArr.length>0) {
        const rect=switchElem.getBoundingClientRect();
        let divWidth = rect.right-rect.left;
        let divHeight = size*(rect.bottom-rect.top);
        let divTop = rect.top;
        let divLeft = rect.left;
        if (chartId==3) {
            divWidth += 400;
            divLeft -= 400;
        }
        clearBackdrop();
        addBackdropChild(
            "chartSelectElement",
            divTop,
            divLeft,
            divWidth,
            divHeight,
            {chartId:chartId,eChilds:optionsArr},
            "background:white;box-shadow:0 1px 3px 0 rgba(0,0,0,.25);overflow-x: hidden;overflow-y: auto;border-radius: 20px;"
        );
        viewBackdrop();
        if (chartId==3) postService("consultas/Proceso.php",{action:"list",module:"covid"},function (msg,params,state,status){
            if (state==4&&status==200&&msg.length>0) {
                fee(lbycn("deleteWithData"),function(elem){ekil(elem);});
                try {
                    const jobj=JSON.parse(msg);
                    if (jobj.result) {
                        if (jobj.result==="refresh") {
                            location.reload(true);
                        } else if (jobj.result==="success") {
                            //conlog("Proceso->list(COVID) => SUCCESS");
                            const chartList=ebyid("chartSelectElement");
                            if (jobj.list&&chartList&&chartList.chartId==3) {
                                //conlog("Proceso->list(COVID) => READING "+jobj.list.length+" ITEMS");
                                for (let i=0; i<jobj.list.length; i++) {
                                    const item=jobj.list[i];
                                    const optElem=ecrea({
                                        eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px 10px 20px;",
                                        eChilds:[
                                            {eName:"DIV",className:"switchUser",eText:item.usuario},
                                            {eName:"DIV",className:"switchDetail",eText:item.hoy},
                                            {eName:"DIV",className:"switchDetail",eText:item.ayer},
                                            //{eName:"DIV",className:"switchDetail",eText:item.semana},
                                            //{eName:"DIV",className:"switchDetail",eText:item.mes},
                                            //{eName:"DIV",className:"switchDetail",eText:item.anio},
                                            //{eName:"DIV",className:"switchDetail",eText:item.siempre},
                                            {eName:"DIV",className:"switchDetail",eText:item.casos},
                                            {eName:"DIV",className:"switchDetail",eText:item.muertos},
                                            {eName:"DIV",className:"switchDetail",eText:item.enfermos},
                                            {eName:"DIV",className:"switchDetail",eText:item.curados}]
                                    });
                                    chartList.appendChild(optElem);
                                }
                            } else conlog("Proceso->list(COVID) => MISSING ELEMENTS");
                        } else if (jobj.result==="failure") {
                            conlog("Proceso->list(COVID) => FAILURE\nQUERY="+jobj.query+"\nERRORS="+JSON.stringify(jobj.error,jsonCircularReplacer()));
                        } else conlog("Proceso->list(COVID) => UNKNOWN RESULT IS "+jobj.result);
                    } else conlog("Proceso->list(COVID) => NO RESULT");
                } catch(ex) {
                    conlog("Exception caught: ", ex, "\nText: ", msg);
                }
            } else conlog("STATE="+state+", STATUS="+status+", LENGTH="+msg.length);
        });
    }
}
function viewChart(optionCode,zoneCode) {
    //conlog("INI function viewChart ( "+optionCode+" , "+zoneCode+" )");
    const chartSwitch1=ebyid("chartSwitch1");
    const zoneSwitch1=ebyid("zoneSwitch1");
    if (optionCode && chartCodeNames[optionCode]) chartSwitch1.lastCode=optionCode;
    else if (chartSwitch1.lastCode) optionCode=chartSwitch1.lastCode;
    else { optionCode="infected"; chartSwitch1.lastCode=optionCode; }
    if (zoneCode && zoneNames[zoneCode]) zoneSwitch1.lastCode=zoneCode;
    else if (zoneSwitch1.lastCode) zoneCode=zoneSwitch1.lastCode;
    else { zoneCode="MX"; zoneSwitch1.lastCode=zoneCode; }
    const switchColor1=ebyid("switchColor1");
    const switchName1=ebyid("switchName1");
    const zoneName1=ebyid("zoneName1");
    if (switchColor1) switchColor1.className="switchColor "+optionCode;
    if (switchName1) switchName1.textContent=chartCodeNames[optionCode];
    if (zoneName1) zoneName1.textContent=zoneNames[zoneCode];
    closeBackdrop();
    const frame1All=ebyid("frame1All");
    const frame1New=ebyid("frame1New");
    if (frame1All) frame1All.src="https://coronavirus.app/chart/"+zoneCode+"/"+optionCode+"?embed=true";
    //console.log(frame1All.src);
    if (frame1New) frame1New.src="https://coronavirus.app/chart/"+zoneCode+"/"+optionCode+"/new?embed=true";
    //console.log(frame1New.src);
    postService(
        "consultas/Proceso.php",
        {action:"record",module:"covid",case:chartCodeNames[optionCode],zone:zoneNames[zoneCode]},
        function (msg, parameters, state, status) {
            if (state==4&&status==200&&msg.length>0) {
                try {
                    const jobj=JSON.parse(msg);
                    if (jobj.result) {
                        if (jobj.result==="refresh") {
                            location.reload(true);
                        } else if (jobj.result==="success") {
                            conlog("Proceso->record(COVID) => SUCCESS");
                        } else if (jobj.result==="failure") {
                            conlog("Proceso->record(COVID) => FAILURE\nQUERY="+jobj.query+"\nERRORS="+JSON.stringify(jobj.error,jsonCircularReplacer()));
                        } else conlog("Proceso->record(COVID) => UNKNOWN RESULT IS "+jobj.result);
                    } else conlog("Proceso->record(COVID) => NO RESULT");
                } catch(ex) {
                    conlog("Exception caught: ", ex, "\nText: ", msg);
                }
            } else conlog("STATE="+state+", STATUS="+status+", LENGTH="+msg.length);
        }
    );
    //conlog("SERVICE Record Proceso COVID ("+optionCode+","+zoneNames[zoneCode]+") SENT");
    //conlog("END function viewChart");
}
function autoSubmit(parameters) {
    var form = ecrea({eName:"FORM",method:"POST"});
    if (parameters) for (let key in parameters) if (parameters.hasOwnProperty(key) && typeof parameters[key] !== "function") {
        if (Array.isArray(parameters[key])) {
            let pArr=parameters[key];
            let pKey=key+"[]";
            for (let i=0;i<pArr.length;i++) form.appendChild(ecrea({eName:"INPUT",name:pKey,value:pArr[i]}));
        } else {
            const formAttributes=["method","action","target","enctype"];
            const keyLow=key.toLowerCase();
            if (formAttributes.includes(keyLow)) form[keyLow]=parameters[key];
            else form.appendChild(ecrea({eName:"INPUT",type:"hidden",name:key,value:parameters[key]}));
        }
    }
    document.body.appendChild(form);
    form.submit();
}
function getElementWidth(element) {
    return (element && element.getBoundingClientRect) ? element.getBoundingClientRect().width : 0;
}
function getUnboundWidth(element,extraStyles) {
    var clonedElement = element.cloneNode(true);
    // Add inline styles to make the element extend to the width it needs
    clonedElement.style.width = 'auto';
    clonedElement.style.visibility = 'hidden';
    if (extraStyles) for (let name in extraStyles) if (extraStyles.hasOwnProperty(name) && typeof extraStyles[name]!=="function") {
        if (name==="className") clonedElement.className=extraStyles[name];
        else if (name==="class") cladd(clonedElement,extraStyles[name]);
        else clonedElement.style[name]=extraStyles[name];
    }
    // Append the element to the body to be rendered
    document.body.append(clonedElement);
    var elementWidth = getElementWidth(clonedElement);
    // Remove the cloned element from the DOM
    clonedElement.parentNode.removeChild(clonedElement);
    return elementWidth;
}
function isTruncatedElement(element,extraStyles) {
    var renderedWidth = getElementWidth(element);
    var fullWidth = getUnboundWidth(element,extraStyles);
    return (renderedWidth < fullWidth);
}
