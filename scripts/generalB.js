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
var doShowLogs=true;
var doShowFuncLogs=false;
var doShowElemLogs=false;
var _isMouseDown = false;
var _startX = 0;
var _startY = 0;
var _offsetX = 0;
var _offsetY = 0;
var _dragElement;
var myLog=false;
// var _oldZIndex = 0;

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
        }
    }
    console.log("INI onresizeScripts"+msg);
}
function getReadableFileSizeString(fileSizeInBytes) {
    var i = -1;
    var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
    do {
        fileSizeInBytes = fileSizeInBytes / 1024;
        i++;
    } while (fileSizeInBytes > 1024);

    return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
}
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
    funclog("INI", "getQueryString("+formname+")");
    let form = document.forms[formname];
    let qstr = "";

    function GetElemValue(name, value) {
        qstr += (qstr.length > 0 ? "&" : "")
            + encodeURIComponent(name) + "="
            + encodeURIComponent(value);
        funclog("SUBINI","GetElemValue("+name+", "+value+") => qstr="+qstr);
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
    funclog("INI","getQueryData("+formname+")");
    let form = document.forms[formname];
    let formData = new FormData(form);
    function GetTextValue(name, value) {
        let escaped_name = encodeURIComponent(name).replace(/%5B/g, '[').replace(/%5D/g, ']');
        let escaped_value = encodeURIComponent(value ? value : "");
        formData.append(escaped_name, escaped_value);
        funclog("SUBINI", "GetTextValue("+name+", "+value+") => '"+escaped_name+"' : '"+escaped_value+"'");
    }
    function GetFileValue(name, file, filename) {
        let escaped_name = encodeURIComponent(name).replace(/%5B/g, '[').replace(/%5D/g, ']');
        formData.append(escaped_name, file, filename);
        funclog("SUBINI", "GetFileValue("+name+", <file>, "+filename+") => '"+filename+"' APPENDED with variable name '"+escaped_name+"'");
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
                funclog("SUBTAG", "FOUND FILE TYPE");
                let files = element.files;
                for (let f=0; f<files.length; f++) {
                    let file = files[f];
                    funclog("SUBTAG", "TRYING TO UPLOAD "+file.name+"|"+file.type+"~="+element.filetype);
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
function postService(url, parameters, retFunc, errFunc, progressFunc) {
    funclog("INI","postService("+url+", "+JSON.stringify(parameters)+")");
    let xmlHttpPost = ajaxRequest();
    let fd = new FormData();
    if (parameters) for (let key in parameters)
        if (parameters.hasOwnProperty(key) && typeof parameters[key] !== "function") {
            if (Array.isArray(parameters[key])) {
                let pArr=parameters[key];
                let pKey=key+"[]";
                for (let i=0;i<pArr.length;i++) fd.append(pKey,pArr[i]);
            } else switch(key) {
                case "timeout": xmlhttpPost.timeout=parameters[key]; break;
                default: fd.append(key, parameters[key]);
            }
        }
    parameters.xmlHttpPost=xmlHttpPost;
    xmlHttpPost.parameters=parameters;
    xmlHttpPost.open("POST", url, true);
    xmlHttpPost.onreadystatechange = function() {
        //if (xmlHttpPost.readyState==4 && xmlHttpPost.status==200) {
            //funclog("postService "+url+"?"+JSON.stringify(parameters)+": "+xmlHttpPost.responseText);
        //}
        //funclog("RDY","postService("+url+") "+xmlHttpPost.readyState+"/"+xmlHttpPost.status);
        if (retFunc) {
            let retmsg=xmlHttpPost.responseText;
            if (xmlHttpPost.status!=200&&retmsg.length==0) retmsg=xmlHttpPost.statusText;
            retFunc(retmsg, parameters, xmlHttpPost.readyState, xmlHttpPost.status);
        }
    };
    if (errFunc) {
        xmlHttpPost.onerror = function(evt) {
            //funclog("ERR","postService("+url+") "+xmlHttpPost.readyState+"/"+xmlHttpPost.status);
            let errmsg=xmlHttpPost.responseText;
            if (errmsg.length==0) errmsg=xmlHttpPost.statusText;
            errFunc(errmsg, xmlHttpPost.parameters, evt); // xmlHttpPost.readyState, xmlHttpPost.status
        };
        xmlHttpPost.ontimeout = function(evt) {
            errFunc("Request Timed Out", xmlHttpPost.parameters, evt);
        };
    } else {
        xmlHttpPost.onerror = function(evt) {
            console.log("ERR: "+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        };
        xmlHttpPost.ontimeout = function(evt) {
            console.log("ERR: Request Timed Out, PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        };
    }
    if (progressFunc) {
        xmlHttpPost.onprogress = function (evt) {
            progressFunc(xmlHttpPost.responseText, xmlHttpPost.parameters, evt);
        };
    }
    xmlHttpPost.send(fd);
    //funclog("END","postService");
    return xmlHttpPost; // para manipular solicitud, vg llamar metodo abort()
}
function ajaxPost(url, formname, responsediv, responsemsg, callbacksentfunc, timeOutSec) {
    console.log("INI", "ajaxPost(url: "+url+", form: "+formname+")");
    let xmlHttpPost = ajaxRequest();
    xmlHttpPost.open("POST", url, true);
    if (timeOutSec) xmlHttpPost.timeout=1000*timeOutSec;
    else xmlHttpPost.timeout=30000;
    let form = document.forms[formname];
    let respDiv = false;
    if (responsediv) respDiv = ebyid(responsediv);
    if (responsemsg) respDiv.innerHTML = responsemsg;
    if (form.enctype!="multipart/form-data")
        xmlHttpPost.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpPost.responseLength=0;
    xmlHttpPost.onreadystatechange = function(evt) {
        gralEventHandler(evt);
        console.log("TAG", "state: "+xmlHttpPost.readyState+", status: "+xmlHttpPost.status);
        if (xmlHttpPost.readyState==4 && xmlHttpPost.status==200) {
            //updatepage
            console.log("TAG", "AjaxPost READY! => "+responsediv);
            if (respDiv) {
                let respTxt = xmlHttpPost.responseText;
             // if isProgressEnabled {
                    if (xmlHttpPost.responseLength==0) respDiv.innerHTML="";
                    respTxt = respTxt.slice(xmlHttpPost.responseLength);
                    if (respTxt.length>0) respDiv.innerHTML += respTxt;
             // } end progress enabled
                xmlHttpPost.responseLength=xmlHttpPost.responseText.length;
                console.log("TAG", "AjaxPost SENT TO "+respDiv.id+": "+(respTxt?respTxt.length+"/"+xmlHttpPost.responseText.length:-1));
            }
            if (callbacksentfunc) callbacksentfunc(xmlHttpPost);
            else console.log("TAG", "No callback func.");
            console.log("TAG","END READY");
        } else if (respDiv && xmlHttpPost.readyState==3) {
            let respTxt = xmlHttpPost.responseText.slice(xmlHttpPost.responseLength);
         // if isProgressEnabled {
                // respTxt already sliced
                if (xmlHttpPost.responseLength==0) respDiv.innerHTML="";
                if (xmlHttpPost.inclusiveSeparator) {
                    const incSepLstIdx=respTxt.lastIndexOf(xmlHttpPost.inclusiveSeparator);
                    if (incSepLstIdx>-1) {
                        const endIndex=incSepLstIdx+xmlHttpPost.inclusiveSeparator.length;
                        respDiv.innerHTML += respTxt.slice(0,endIndex);
                        xmlHttpPost.responseLength+=endIndex;
                    } else {
                        //respDiv.innerHTML += respTxt;
                        //xmlHttpPost.responseLength=xmlHttpPost.responseText.length;
                    }
                } else {
                    respDiv.innerHTML += respTxt;
                    xmlHttpPost.responseLength=xmlHttpPost.responseText.length;
                }
         // } end progress enabled
            if (xmlHttpPost.extraTimeout) xmlHttpPost.timeout+=xmlHttpPost.extraTimeout;
            console.log("TAG", "AjaxPost SENT PARTIAL TO "+respDiv.id+": "+(respTxt?respTxt.length+"/"+xmlHttpPost.responseText.length:-1));
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
            console.log("TAG","END PARTIAL");
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
        parameters[pair[0]]=pair[1];
    }
    xmlHttpPost.parameters=parameters;
    const gralEventHandler=function(evt) {
        console.log(" [ "+evt.type+(evt.loaded?": "+evt.loaded+" bytes transferred":(evt.target&&evt.target.responseText?": "+evt.target.responseText.length+" text length":""))+" ]");
    }
    xmlHttpPost.onloadstart=gralEventHandler;
    xmlHttpPost.onload=gralEventHandler;
    xmlHttpPost.onloadend=gralEventHandler;
    //xmlHttpPost.lastLength=0;
    //xmlHttpPost.onprogress = function (evt) {
    //    gralEventHandler(evt);
    //    console.log("PROGRESS state "+xmlHttpPost.readyState+", status "+xmlHttpPost.status+", length "+xmlHttpPost.responseText.length+", added "+(xmlHttpPost.responseText.length-xmlHttpPost.lastLength));
    //    xmlHttpPost.lastLength = xmlHttpPost.responseText.length;
    //};
    xmlHttpPost.onerror = function(evt) {
        gralEventHandler(evt);
        console.log("ERR: "+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        if (respDiv) respDiv.innerHTML="";
        else console.log("NO RESPONSE DIV");
        overlayMessage([{eName:"P",className:"errorLabel",eText:xmlHttpPost.responseText},{eName:"P",eText:"Por favor, consulte al administrador."}],"ERROR");
    };
    xmlHttpPost.onabort = gralEventHandler;
    xmlHttpPost.ontimeout = function(evt) {
        gralEventHandler(evt);
        console.log("ERR: Request Timed Out: "+xmlHttpPost.responseText+", PARAMS:",xmlHttpPost.parameters,", EVENT:",evt);
        if (respDiv) respDiv.innerHTML="";
        else console.log("NO RESPONSE DIV");
        overlayMessage({eName:"P",className:"errorLabel",eText:"El servidor está tardando mucho tiempo, por favor consulte al administrador."},"ERROR");
    };

    xmlHttpPost.send(query);
    console.log("END", "ajaxPost sent");
    return xmlHttpPost;
}
function getValueTxt(idElem, emptyVal, objVal, falseVal) {
    if (!emptyVal) emptyVal="";
    if (!objVal) objVal="";
    if (!falseVal) falseVal="";
    let obj = ebyid(idElem);
    if (obj) {
        if (obj.value) {
            if (obj.value.length > 0) return obj.value;
            else return emptyVal;
        } else return objVal
    } else return falseVal;
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
    funclog("END", "fillReporte");
}
function printOverlay() {
    console.log("INI function printOverlay");
    let dra=ebyid("dialog_resultarea");
    let baseurl=false;
    let bases=document.getElementsByTagName("BASE");
    if (bases[0]) baseurl=bases[0].href;
    console.log("BASE URL = '"+baseurl+"'");
    let pw=window.open();
    pw.document.open();
    pw.document.write('<html><head><meta charset="utf-8"><base href="'+baseurl+'" target="_blank"><link href="css/general.php" rel="stylesheet" type="text/css"></head><body class="blank fontMedium marginV7">'+dra.innerHTML+'</body></html>');
    pw.document.close();
    //pw.print();
    pw.onload=function(e) { console.log("PRINT PAGE LOADED. READY TO PRINT AND CLOSE."); pw.print(); pw.close(); };
    //let scrp=pw.document.createElement('script');
    //scrp.type='text/javascript';
    //scrp.text='window.close();';
    //pw.document.body.appendChild(scrp);
}
function fillSelector(selectorname, params, title, dialogId) {
    if (!dialogId) dialogId="dialog";
    funclog("INI", "fillSelector('"+selectorname+"', '"+params+"', '"+title+"', '"+dialogId+"')");
    if (!empty(params)) params="&"+params;
    ebyid(dialogId+"_title").innerHTML = title;
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let element = ebyid(dialogId+"_resultarea");
            element.innerHTML = xmlhttp.responseText;
            if (element.style && element.style.height) element.style.height=null;
            fillPaginationIndexes();
        }
    };
    
    xmlhttp.open("GET","selectores/"+selectorname+".php?tabla=1"+params,true);
    xmlhttp.send();
    funclog("END", "fillSelector");
}
function fillSelectorContents(action, callbackSuccessFunc) {
    if (!action) action="basic";
    funclog("INI", "fillSelectorContents('"+action+"','"+callbackSuccessFunc+"')");
    let selectorname = ebyid("selectorname");
    let rowsperpage = ebyid("limit");
    let currpage = ebyid("pageno");
    let lastpage = ebyid("lastpg");
    let params="";
    if (rowsperpage) params = "&limit=" + rowsperpage.value;
    let filterBox = lbycn("filter_box");
    let exacto = "";
    appendLog("PARAMS1 | "+params+"\n");
    for(let i=0; i<filterBox.length; i++) {
        /* if (filterBox[i].selectedOptions) {
            for (opt in filterBox[i].selectedOptions) {
            }
        } else */
        if (filterBox[i].value) {
            params+='&param[]='+filterBox[i].name;
            let paramVal = filterBox[i].value;
            let filtro = filterBox[i].getAttribute("filter");
            let fbSfx = ""; // Sufijo (se agregan corchetes [] para valores multiples
            let fbAdd = ""; // Adicional (se agregan los valores multiples adicionales)
            
            appendLog(" ###1"+filtro+" "+filterBox[i].name+" = "+filterBox[i].value+"\n");
            if (filtro == "folio" && filterBox[i]) {
                paramVal = ""+parseInt(paramVal.replace(/\D/g,''), 10);
            } else if (filtro == "exacto") {
                if (exacto.length>0) exacto += ",";
                exacto += filterBox[i].name;
            } else if (filtro == "fixSaldo") {
                let text = filterBox[i].options[filterBox[i].selectedIndex].text;
                appendLog(" ### "+text+": "+filterBox[i].name+" = "+filterBox[i].value+"\n");
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
            appendLog(" ###X "+params.replace(/\&/g, "&amp;")+"\n");
        } else {
            appendLog(" ###0"+" "+filterBox[i].name+"\n");
        }
    }
    params4log = params.replace(/\&/g, "&amp;");
    // params4log = params4log.replace(/¶/g, "&amp;para");
    // params4log = params4log.replace("&", "#");
    appendLog("PARAMS2 | "+params4log+"\n");
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
            appendLog("RESULT RECEIVED\n");
            const dtb=ebyid("dialog_tbody");
            if (dtb) {
                dtb.innerHTML = xmlhttp.responseText;
                fillPaginationIndexes();
                if (callbackSuccessFunc) callbackSuccessFunc();
            } else console.log("NOT FOUND DIALOG TBODY FOR RESPONSE:\n"+xmlhttp.responseText);
        }
    };
    appendLog("AJAX | "+"selectores/"+selectorname.value+".php?datos=1&exacto="+exacto+params.replace(/\&/g, "&amp;")+"\n");
    xmlhttp.open("GET","selectores/"+selectorname.value+".php?datos=1&exacto="+exacto+params,true);
    xmlhttp.send();
    funclog("END", "fillSelectorContents");
}
function refreshFooter() {
    appendLog("INI refreshFooter\n");
    let piePagina = ebyid("pie_pagina");
    if (piePagina) {
        let xmlhttp = ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                piePagina.innerHTML = xmlhttp.responseText;
                appendLog("SUCCESS refreshFooter\n");
            }
        };
        let rqst = "bloques/piePagina.php?refreshFooter=true";
        xmlhttp.open("GET",rqst,true);
        appendLog("SEND refreshFooter: "+rqst+"\n");
        xmlhttp.send();
    }
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
        closeBtn.value="Cerrar";
    }
    if (saveBtn) {
        let spc = saveBtn.previousSibling;
        if (spc.nodeType==3) saveBtn.parentNode.removeChild(spc);
        ekil(saveBtn);
    }
}
function appendMessageElement(element, message) {
    //funclog("INI","appendMessageElement");
    if (!element||!message) {
        //funclog("END","appendMessageElement invalid args");
        return false;
    }
    if (Array.isArray(message)) {
        //funclog("Array");
        message.forEach(function(submessage) { appendMessageElement(element,submessage); });
    } else if (typeof message==="string") {
        let strdiv=ecrea({eName:"DIV"});
        strdiv.innerHTML=message;             // texto y html
        // let strdiv=ecrea({eText:message}); // solo texto
        element.appendChild(strdiv);
    } else if (message!==null && typeof message==="object" && message.nodeName && message.nodeType) {
        element.appendChild(message);
        //funclog("Element",message);
    } else if (message!==null && typeof message==="object" && (message.eName || message.eText)) {
        element.appendChild(ecrea(message));
    }//else funclog("Unknown "+(typeof message),message);
}
function overlayConfirmation(msg, title, callbackConfirmFunc, dialogId) {
    if (!title) title="CONFIRMACI&Oacute;N"; if (!dialogId) dialogId="dialog";
    funclog("INI", "overlayConfirmation(<msg>,'"+title+"',<callbackConfirmFunc>,'"+dialogId+"')");
    const element=ebyid(dialogId+"_resultarea");
    ekfil(element);
    if (element && element.style && element.style.height) element.style.height=null;
    const ttElement = ebyid(dialogId+"_title");
    ekfil(ttElement);
    appendMessageElement(element, msg);
    appendMessageElement(ttElement, title);
    setTimeout(function(theCallbackFunc) {
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (_dragElement) {
            _dragElement.style.top='0px';
            _dragElement.style.left='0px';
            _dragElement.focus();
            _dragElement.blur();
            funclog("TAG", "DRAG CONFIRMATION ELEMENT FOCUSED AND BLURRED");
        }
        const buttonBox = ebyid("closeButtonArea");
        const closeBtn = ebyid("closeButton");
        let saveBtn = ebyid("accept_overlay");
        closeBtn.value="Cancelar";
        if (!saveBtn) {
            saveBtn = ecrea({eName:"INPUT",type:"button",id:"accept_overlay",value:"Confirmar",onclick:function() {
                overlay(); theCallbackFunc(); }});
            const spc = document.createTextNode(" ");
            try { buttonBox.insertBefore(saveBtn,closeBtn.nextSibling);
                  buttonBox.insertBefore(spc,closeBtn.nextSibling);
            } catch (e) { appendLog(e.message); }
        }
        applyVisibility("overlay");
    }, 50, callbackConfirmFunc);
    funclog("END", "overlayConfirmation");
}
function overlayMessage(msg, title, dialogId) {
    if (!title) title="AVISO"; if (!dialogId) dialogId="dialog";
    funclog("INI", "overlayMessage(<msg>,'"+title+"','"+dialogId+"')");
    const element = ebyid(dialogId+"_resultarea");
    ekfil(element);
    if (element && element.style && element.style.height) element.style.height=null;
    const ttlElem = ebyid(dialogId+"_title");
    ekfil(ttlElem);
    appendMessageElement(element,msg);
    appendMessageElement(ttlElem,title);
    setTimeout(function() {
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (_dragElement) {
            _dragElement.style.top='0px';
            _dragElement.style.left='0px';
            _dragElement.focus();
            _dragElement.blur();
            funclog("TAG", "DRAG MESSAGE ELEMENT FOCUSED AND BLURRED");
        }
        applyVisibility("overlay"); 
    }, 50);
    funclog("END", "overlayMessage");
}
function overlayWheel() {
    console.log("INI overlayWheel");
    let element = ebyid("overlay");
    if (element) console.log("VISIBILITY = "+element.style.visibility);
    else console.log("UNKNOWN");
    if (element && element.style.visibility === "hidden") {
        console.log("Found and hidden");
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
    } else 
        console.log("NOT FOUND!!!");
}
function overlay(showSelector, title, referenceId, params, dialogId) {
    if (!params) params="";
    if (!title) title="AVISO";
    if (!dialogId) dialogId="dialog";
    funclog("INI", "overlay('"+showSelector+"','"+title+"','"+referenceId+"','"+params+"','"+dialogId+"')");
    let element = ebyid(dialogId+"_resultarea");
    while(element.firstChild) element.removeChild(element.firstChild);
    let closeArea = ebyid("closeButtonArea");
    for(let elemCA=closeArea.firstChild;elemCA;) {
        if (elemCA.id==="closeButton") elemCA=elemCA.nextElementSibling;
        else {
            closeArea.removeChild(elemCA);
            elemCA=closeArea.firstChild;
        }
    }
    let dbox = ebyid("dialogbox");
    dbox.style.removeProperty("height");
    if (element.style && element.style.height) element.style.height=null;
    if (typeof showSelector === 'undefined') {
    } else {
        fillSelector(showSelector, params, title, dialogId);
        if (!_dragElement) _dragElement = ebyid("dialogbox");
        if (_dragElement) {
            _dragElement.style.top='0px';
            _dragElement.style.left='0px';
            _dragElement.focus();
            _dragElement.blur();
            funclog("TAG", "DRAG OVERLAY ELEMENT FOCUSED AND BLURRED");
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
    funclog("END", "overlay");
}
function overlayClose() {
    let element = ebyid ("overlay");
    if (element && element.style.visibility !== "hidden") toggleVisibility("overlay");
}
function applyVisibility(elementId, depth) {
    funclog("INI", "applyVisibility('"+elementId+"','"+depth+"')");
    let element = ebyid (elementId);
    if (element) {
        if (element.style.visibility === "hidden") toggleVisibility(elementId);
        else {
            let dbox = ebyid("dialogbox");
            let wbox = ebyid("wheelbox");
            if (dbox && dbox.classList.contains("hidden")) clrem(dbox,"hidden");
            if (wbox && !wbox.classList.contains("hidden")) cladd(wbox,"hidden");
            //if (dbox.style.visibility === "hidden") dbox.style.visibility = "visible";
            //if (wbox.style.visibility !== "hidden") wbox.style.visibility = "hidden";
            let drar = ebyid("dialog_resultarea");
            let ovlen = element.offsetHeight;
            let dblen = dbox.offsetHeight;
            let drlen = drar.offsetHeight;
            let calcRLen = dblen - 55;
            if (drlen>0 && calcRLen < drlen) {
                dbox.style.height="calc(100% - 40px)";
                funclog("DbLen=dialogbox.offsetHeigh="+dblen+"\nCalcRLen=dblen-55="+calcRLen+"\nDrLen=dialog_resultarea.offsetHeight="+drlen+"\novlen=overlay.offsetHeight="+ovlen);
                funclog("FIX HEIGHT: "+dbox.style.height);
            } else funclog("NOT FIXED HEIGHT: "+ovlen+" | "+dblen+" | "+drlen);
        }
    }
    funclog("END", "applyVisibility");
}
function toggleVisibility(elementId, depth) {
    funclog("INI", "toggleVisibility('"+elementId+"','"+depth+"')");
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
                        funclog("TAG", "FOCUS on USER FIELD!");
                    }, 200);
                } else {
                    console.log("STEP.callOnClose toggleVisibility");
                    setTimeout(function() {
                        console.log("TIME.callOnClose toggleVisibility");
                        if (element.callOnClose) {
                            element.callOnClose();
                            if (!element.keepCallOnClose) delete element.callOnClose;
                        }
                    }, 200);
                }
            } else {
                clearTimeout(timeOutOverlay);
                cladd(element,"no_selection");
                element.myDblClickEvent = function(event) {
                    if(!event) event = window.event;
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
                let acceptBtn = ebyid('accept_overlay');
                if (acceptBtn) {
                    acceptBtn.focus();
                    funclog("TAG", "Focus on Accept button");
                } else {
                    let closeBtn = ebyid('closeButton');
                    if (closeBtn) {
                        closeBtn.focus();
                        funclog("TAG", "Focus on Close button");
                    } else {
                        funclog("TAG", "Accept or Close button NOT found!");
                    }
                }
                let dbox = ebyid("dialogbox");
                let drar = ebyid("dialog_resultarea");
                let ovlen = element.offsetHeight;
                let dblen = dbox.offsetHeight;
                let drlen = drar.offsetHeight;
                let calcRLen = dblen - 55;
                if (drlen>0 && calcRLen < drlen) {
                    dbox.style.height="calc(100% - 40px)";
                    funclog("DbLen=dialogbox.offsetHeight="+dblen+"\nCalcRLen=dblen-55="+calcRLen+"\nDrLen=dialog_resultarea.offsetHeight="+drlen+"\novlen=overlay.offsetHeight="+ovlen);
                    funclog("FIX HEIGHT: "+dbox.style.height);
                } else funclog("NOT FIXED HEIGHT: "+ovlen+" | "+dblen+" | "+drlen);
            }
        }
    } else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                toggleVisibility(elementId, depth+1);
            }, 100);
        }
    }
    funclog("END", "toggleVisibility");
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
    } else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                changeAttribute(elementId, elementAttribute, attributeValue, depth+1);
            }, 20);
        }
    }
    return false;
}
function changeAttributeByClass(classname, elementAttribute, attributeValue, depth) {
    if (typeof depth === 'undefined') depth=0;
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
    } else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                backupAttribute(elementId, sourceAttribute, targetAttribute, depth+1);
            }, 20);
        }
    }
    return false;
}
function backupAttributeByClass(classname, sourceAttribute, targetAttribute, depth) {
    if (typeof depth === 'undefined') depth=0;
    let elements = lbycn(classname);
    for (let i=0; i<elements.length; i++) {
        elements[i][targetAttribute]=elements[i][sourceAttribute];
    }
}
function removeElementsByClass(classname, depth) {
    if (typeof depth === 'undefined') depth=0;
    let elements = lbycn(classname);
    while(elements.length > 0) {
        ekil(elements[0]);
    }
}
function fillValue(elementId, elementValue, depth) {
    if (typeof depth === 'undefined') depth=0;
    appendLog("INI fillValue "+elementId+", "+elementValue+", "+depth+"\n");
    let element = ebyid(elementId);
    if (element) {
        if (element.value!=elementValue) {
            element.value=elementValue;
//            if (element.type=="hidden")
//                element.onchange();
        }
        return element;
    } else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                fillValue(elementId, elementValue, depth+1);
            }, 20);
        } else {
            // document.write("<!-- GENERAL.JS ERROR: fillValue. "+elementId+" not found! -->\n");
        }
    }
    return false;
}
function addContent(elementId, contentElem, depth, maxdepth) {
    if (typeof depth === 'undefined') depth=0;
    if (typeof maxdepth === 'undefined') maxdepth=10;
    appendLog("INI fillContent "+elementId+", ",contentElem,", "+depth+", "+maxdepth+"\n");
    let element = ebyid(elementId);
    if (element && contentElem) {
        element.appendChild(contentElem);
        return element;
    } else {
        if (depth<maxdepth) {
            setTimeout(function() {
                fillContent(elementId, contentElem, depth+1, maxdepth);
            }, 20);
        } else {
            // document.write("<!-- GENERAL.JS ERROR: fillContent. "+elementId+" not found! -->\n");
        }
    }
    return false;
}
function fillInnerHtmlWithValue(elementId, elementIdWithValue, depth) {
    if (typeof depth === 'undefined') depth=0;
    appendLog("INI fillInnerHtmlWithValue "+elementId+", "+elementIdWithValue+", "+depth+"\n");
    let elementWithValue = ebyid (elementIdWithValue);
    if (elementWithValue)
        fillInnerHtml(elementId, elementWithValue.value, depth);
    else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                fillInnerHtmlWithValue(elementId, elementIdWithValue, depth+1);
            }, 20);
        } else {
            // document.write("<!-- GENERAL.JS ERROR: fillInnerHtmlWithValue. "+elementIdWithValue+" not found! -->\n");
        }
    }
}
function fillInnerHtml(elementId, elementText, depth) {
    if (typeof depth === 'undefined') depth=0;
    let element = ebyid (elementId);
    if (element) {
        element.innerHTML=elementText;
    } else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                fillInnerHtml(elementId, elementText, depth+1);
            }, 20);
        }
    }
}
function appendInnerHtml(elementId, elementText, depth) {
    if (typeof depth === 'undefined') depth=0;
    let element = ebyid (elementId);
    if (element) {
        element.innerHTML+=elementText;
    } else {
        if (typeof depth === 'undefined') depth=0;
        if (depth<10) {
            setTimeout(function() {
                appendInnerHtml(elementId, elementText, depth+1);
            }, elementId=="mylog"?51:20);
        } else {
            if (elementId!="mylog")
                appendLog("XXX appendInnerHtml failed for "+elementId+"\n");
        }
    }
}
function fillLog(text) {
    if (doShowLogs) fillInnerHtml("mylog", text,10);
}
function appendLog(text) {
    if (doShowLogs) appendInnerHtml("mylog", text,10);
}
function addLog(text) {
    if (!myLog) myLog=ebyid("mylog");
    //textObj={eText:text};
    //textElem=ecrea(textObj);
    //myLog.appendChild(textElem);
    myLog.appendChild(document.createTextNode(text));
    console.log(myLog);
}
function conlog(txt) {
  if (doShowLogs) console.log(txt);
}
function funclog(pref, txt) {
  if (doShowFuncLogs) console.log(pref+" | "+txt);
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
                appendLog("XXX fillMappedValue "+keyv+" : "+assocArrayToValue[keyv]+"\n");
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
    appendLog("START | fillPaginationIndexes "+depth+".\n");

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
        appendLog("ACTION | fillPaginationIndexes "+lastPage.value+" lastPage, "+pageNum.value+" pageNum, "+pageRows.value+" pageRows, vis:"+visStatus+".\n");
        let numPages = parseInt(lastPage.value);
        let plim = parseInt(pageRows.value);
        let curPage = parseInt(pageNum.value);
        navToFirst.style.visibility = (curPage > 2?visStatus:"hidden");
        navToPrevious.style.visibility = (curPage > 1?visStatus:"hidden");
        navToNext.style.visibility = (curPage < numPages?visStatus:"hidden");
        navToLast.style.visibility = (curPage < (numPages - 1)?visStatus:"hidden");
        paginationIndexes.innerHTML=" "+curPage+"/"+numPages+" ";
    } else if (depth < 10) {
        setTimeout(function() {
            fillPaginationIndexes(depth+1);
        }, 25);
    } else {
        appendLog("ERROR | fillPaginationIndexes "+depth+" "+(lastPage?"":"lastPage missing")+(pageNum?"":(lastPage?"":", ")+"pageNum missing")+(pageRows?"":(lastPage&&pageNum?"":", ")+"pageRows missing")+(paginationIndexes?"":(lastPage&&pageNum&&pageRows?"":", ")+"paginationIndexes missing")+".\n");
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
        setTimeout(function() {
            hidePaginationButtons(depth+1);
        }, 50);
    } else {
        appendLog("ERROR | hidePaginationButtons().\n");
    }
}
function initializeSubtreeModifiedListener(elementId, functionName, times) {
    let container = ebyid (elementId);
    if (container && container.addEventListener) {
        container.addEventListener ('DOMSubtreeModified', functionName, false);
    } else {
        if (typeof times === 'undefined') times=0;
        if (times < 10) {
            setTimeout(function() {
                initializeSubtreeModifiedListener(elementId, functionName, times+1);
            }, 20);
        } else {
            appendLog("ERROR | initializeSubtreeModifiedListener ( " + elementId + /* ", "+functionName.toString() + */ " ): Not found Element by Id or it doesn't have addEventListener.\n");
        }
    }
}
function resetBlocks() {
}
function loopFunc(arr, funcVal) {
    if (typeof funcVal === 'undefined') funcVal=false;
    appendLog("loopFunc()\n");
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
function delayKeyUp(event) {
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

// Este método provee busqueda predictiva en campos de texto.
// Se requiere asignar un idAccion a cada campo deseado y añadir el codigo respectivo en las secciones condicionadas por idAccion
function calculoAvanzadoDelay(evento, idAccion, idElemento, backupFunc) {
    if (typeof backupFunc === 'undefined') backupFunc=false;
    if (!evento) evento = window.event;
    appendLog(" # ## # ## # ## # ## # ## # ## # ## # ## # ## # ## #\n");
    appendLog(" # ## # INI calculoAvanzadoDelay: "+(evento?evento.type:"sin evento")+", "+idAccion+(idElemento?", "+idElemento:"")+"\n");
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
    appendLog(" # ## # INI calculaRangoSeleccionado: "+elemento.value+", "+inilen+", "+endlen+"\n");
    if("selectionStart" in elemento) {
        elemento.selectionStart = inilen;
        elemento.selectionEnd = endlen;
        elemento.focus();
        funclog("TAG", "FOCUS ON ELEMENTO");
    } else {
        let nRange = elemento.createTextRange();
        nRange.moveStart("character", inilen);
        nRange.collapse();
        nRange.moveEnd("character", (endlen-inilen));
        nRange.select();
        funclog("TAG","RANGE SELECTION ON ELEMENTO");
    }
}
// En este metodo se hace una peticion al servidor, especifica por idAccion, el resultado se procesa en $idUsuario y despues se acondiciona el fragmento predicho del mismo para permitir correcciones.
function calculoAvanzado(evento, idAccion, elemento, backupFunc) {
    if (typeof backupFunc === 'undefined') backupFunc=false;
    appendLog(" # ## # INI calculoAvanzado: "+(evento?evento.type:"sin evento")+", "+idAccion+(elemento?", "+elemento.value:"")+"\n");
    let keyup = ( (evento && evento.type=="keyup" && elemento.type=="text") ? true : false );
    let keydown = ( (evento && evento.type=="keydown" && elemento.type=="text") ? true : false );
    if (!elemento || !(elemento.value) || elemento.value.length==0) {
        $idUsuario(evento, idAccion, false);
        return;
    }
    let inilen = 0;
    if (elemento.type=="text") inilen = elemento.selectionStart;
    appendLog(" # ## # inilen="+inilen+"\n");
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let resultado = xmlhttp.responseText;
            // Procesamos el resultado de la peticion al servidor. Con el evento keyup ademas seleccionamos el texto agregado por la busqueda predictiva para facilitar modificaciones.
            let shft = $idUsuario(evento, idAccion, resultado, backupFunc)
            if (shft!==false && (keyup||keydown)) {
                if (shft===true) shft=0;
                if (shft>0) inilen=shft;
                appendLog(" # ## # OK  calculoAvanzado: "+inilen+", "+elemento.id+", "+elemento.value+"\n");
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
        appendLog(" # ## # SNT calculoAvanzado: "+rqst+"\n");
        xmlhttp.send();
    }
}

// En este metodo se procesa el resultado y se decide el curso de accion posterior
// Los modulos que actualmente reciben las peticiones en el servidor regresan datos separados por pipes '|'
// Y las condiciones por idAccion asignan los diferentes valores a campos de texto en la interfaz
// La segunda evaluacion de condicion de idAccion sirve para borrar los campos de texto cuando no se encuentran resultados
function resultadoAvanzado(evento, idAccion, resultado, backupFunc) {
    if (typeof backupFunc === 'undefined') backupFunc=false;
    if (resultado && resultado.length>0) { // && resultado.indexOf("|")>=0) {
        appendLog(" # ## # INI resultadoAvanzado: "+(evento?evento.type:"sin evento")+", "+idAccion+", "+resultado.length+"\n");
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
            appendLog(" # ## # resultados: "+rarr[0]+", "+rarr[1]+", "+rarr[2]+", "+rarr[3]+", "+rarr[4]+", "+rarr[5]+"\n");
            let idUsrElem = fillValue("user_id", rarr[0]);
            let nomUsrElem = fillValue("user_field", rarr[1]);
            fillValue("user_email", rarr[2]);
            fillValue("user_realname", rarr[3]);
            let kchk=ebyid("user_updkey");
            let kval=ebyid("user_updval");
            if (kchk&&kval) {
                if (rarr[9]) {
                    kval.value=rarr[9];
                    if (rarr[9]==="1")kchk.checked=true;
                    else kchk.checked=false;
                }
            }
            if (rarr[8]) fillValue("user_obs", rarr[8]);
            else fillValue("user_obs", "");
            let perfilesIds = rarr[5].split(",");
            changeAttributeByClass("user_perfil", "checked", false);
            let esPrv=false;
            for (let i=0; i<perfilesIds.length; i++) {
                changeAttribute("user_perfil_"+perfilesIds[i], "checked", true);
                if (perfilesIds[i]==3) esPrv=true;
            }
            if (esPrv) {
                // ToDo: Deshabilitar todos los permisos.
            }
            if (rarr[4].indexOf("Compras")>=0) {
                validaCompras(true, "Compras");
                // check empresas
                fillValue("listaComprasGrupoId", rarr[7]);
                changeAttribute("aliasListaComprasGrupoCell", "title", rarr[7], 10);
            } else {
                validaCompras(false, "Compras");
                changeAttribute("aliasListaComprasGrupoCell", "title", "", 10);
            }
            return false;
        }
    } else {
        appendLog(" # ## # INI resultadoAvanzado: "+(evento?evento.type:"sin evento")+", "+idAccion+", resultado vacio\n");
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
            changeAttributeByClass("user_perfil", "checked", false);
            validaCompras(false, "Compras");
            changeAttribute("aliasListaComprasGrupoCell", "title", "", 10);
        } else if (idAccion=="usuario-perfil") {
            changeAttributeByClass("u_checkbox u_perfil", "checked", false);
            changeAttributeByClass("u_checkbox u_accion", "checked", false);
            changeAttribute("borrarUsuario", "style", "display: none;");
        }
    }
    return false;
}
function findEqualIndexInRows(rarr,elemsPerRow,elemIdxToEval) {
    appendLog(" # ## # INI findEqualIndexInRows (arr,"+elemsPerRow+","+elemIdxToEval+")\n");
    let i=0;
    let len = rarr.length;
    appendLog("Len "+len);
    len -= (len%elemsPerRow);
    appendLog(" -> "+len+"\n");
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
    appendLog("i="+i+"\n");
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
    appendLog(" # ## # INI findFollowedSectionInRows (arr("+rarr.length+"),"+elemsPerSection+","+lookOutIdx+","+lookOutElemId+","+keyCode+")\n");
    if (rarr.length>=(2*elemsPerSection)) { // minimo dos secciones
        let elemVal = getValueTxt(lookOutElemId, false, false, false);
        appendLog("   #  #     "+lookOutElemId+"='"+elemVal+"'\n");
        if (elemVal!==false && elemVal!=="0") {
            let sectionIdx = findSectionInRows(rarr, elemsPerSection, lookOutIdx, elemVal);
            appendLog("   #  #     sectionIdx="+sectionIdx+"\n");
            if (sectionIdx!==false) {
                if (keyCode=='38') sectionIdx--;
                else sectionIdx++; // keyCode=='40'
                let len=rarr.length;
                len-=len%elemsPerSection;
                let maxSec=len/elemsPerSection;
                if(sectionIdx<0) sectionIdx=maxSec-1;
                else if(sectionIdx>=maxSec) sectionIdx=0;
                appendLog("   #  #     new sectionIdx="+sectionIdx+"\n");
                return sectionIdx;
            }
        }
    }
    appendLog(" # ## # X\n");
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
    appendLog("forzarSoloNumeros eventtype="+event.type+". target="+event.target+". keyCode="+event.keyCode+". ctrl="+event.ctrlKey+"\n");
    if (!event.ctrlKey && !event.metaKey && !event.altKey) {
        let charCode = (typeof event.which == "undefined") ? event.keyCode : event.which;
        if (charCode && !/\d/.test(String.fromCharCode(charCode))) return false;
    }
    return true;
}
function validaElementoNumerico(elemId) {
    let element = ebyid(elemId);
    if(element.value.length>0 && !isNaN(element.value)) {
        appendLog("validaElementoNumerico '"+element.value+"' ("+element.value.length+") = true.\n");
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

    appendLog("### ### "+oldStart+", "+sStart+", "+oldEnd+", "+sEnd+", "+oldPtIdx+", "+value.indexOf(".")+", "+event.keyCode+", "+String.fromCharCode(event.keyCode)+", "+oldValue+", "+oldValue.length+", "+event.target.value+", "+event.target.value.length+", "+value+", "+value.length+"\n");

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
function daysInMonth(humanMonth, year) {
    return new Date(year || new Date().getFullYear(), humanMonth, 0).getDate();
}

function generaToken(elementId1, elementId2, length, totalLength2) {
    if (typeof length === 'undefined') length=8;
    if (typeof totalLength2 === 'undefined') totalLength2=8;
    appendLog("INI generaToken ("+length+")\n");
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let resultado = xmlhttp.responseText;
            appendLog("RESULTADO generaToken: '"+resultado+"'\n");

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
    appendLog("SEND generaToken: "+rqst+"\n");
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
function ignoreAlpha(event) {
  if (!event) {
    if (window.event) event = window.event;
    else return false;
  }
  if ((event.keyCode >= 65 && event.keyCode <= 90)||(event.keyCode>=48 && event.keyCode<=57)) // A to Z || 0 to 9
    eventCancel(event);
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
    // If the value is not a number or the exp is not an integer...
    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
        return NaN;
    }
    // Shift
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
//    return num;
}
function ExtractNumber(value) {
    let n = parseInt(value);
    return n == null || isNaN(n) ? 0 : n;
}
function addCookie(name, value, expireDate) {
    let expires = "";
    if (expireDate) {
        let date = new Date(expireDate);
        expires="; expires="+date.toUTCString();
    }
    document.cookie = name+"="+value+expires;
}
function hasCookie(name) {
    let value = getCookie(name);
    if (value) return true;
    return false;
}
function getCookie(name) {
//    let cookieValue = document.cookie.replace(           /(?:(?:^|.*;\s*)test2\s*\=\s*([^;]*).*$)|^.*$/, "$1");
    let cookieValue = decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(name).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1"));
    return cookieValue;
}
function delCookie(name) {
    document.cookie = name+"=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
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
        funclog("TAG", "FOCUS ON BODY DURING MOUSE_IS_DOWN");
        document.onselectstart = function () { return false; };
        _dragElement.ondragstart = function() { return false; };
        _dragElement.style.margin = null;
        _dragElement.style.verticalAlign = null;
        _dragElement.style.marginTop = null;
        _dragElement.style.position = 'relative';
        appendLog(" ### Start Dragging "+_dragElement.id+": ("+_offsetX+", "+_offsetY+")\n");
        return false;
    }
    return true;
}
function OnMouseUp(evt) {
    if (!evt) evt = window.event;
    if (_dragElement) {
        appendLog(" ### Mouse Up: Dragging stopped ("+ExtractNumber(_dragElement.style.left)+", "+ExtractNumber(_dragElement.style.top)+")\n");
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
    console.log("Removed addSibMsg");
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
    console.log("COPIED:\n"+logText);
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
//Returns true if it is a DOM node
function isNode(o){
  return (
    typeof Node === "object" ? o instanceof Node : 
    o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName==="string"
  );
}

//Returns true if it is a DOM element    
function isElement(o){
  return (
    typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
    o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName==="string"
);
}
// SHORTCUTS
function fee(arrayLike, elemCallback) { // for each class element: callback(elem)
    if (Array.from) Array.from(arrayLike).forEach(elemCallback);
    else [].forEach.call(arrayLike, elemCallback);
}
function ebyid(id) { // element by id
    return document.getElementById(id);
}
function lbycn(classname,baseElement,index) { // list by class name
    if (!baseElement) baseElement=document;
    let result=baseElement.getElementsByClassName(classname);
    if (typeof index === 'undefined') return result;
    if (result.length>index) return result[index];
    else return false;
}
function clhas(elem,classname) {
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let retval=(elem.length>0);
            elem.forEach(function(subelem) {
                retval&=clhas(subelem,classname);
            });
            return retval;
        } else if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let retval=(elem.length>0);
            for (let n=0; n<elem.length; n++) {
                retval&=clhas(elem[n],classname);
            }
            return retval;
        }
        if (elem.classList) return elem.classList.contains(classname);
    }
    return false;
}
function clfix(elem,classname) {
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let retval=(elem.length>0);
            elem.forEach(function(subelem) {
                clfix(subelem,classname);
            });
            return retval;
        } else if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let retval=(elem.length>0);
            for (let n=0; n<elem.length; n++) {
                clfix(elem[n],classname);
            }
            return retval;
        }
        if (elem.classList) return elem.classList.toggle(classname);
    }
    return false;
}
function cladd(elem,classname) {
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let num=0;
            elem.forEach(function(subelem) {
                num += cladd(subelem,classname); });
            return num;
        } else if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let num=0;
            for (let n=0; n<elem.length; n++) {
                num += cladd(elem[n],classname);
            }
            return num;
        }
        if (elem.classList) {
            if (elem.classList.contains(classname)) return 0;
            elem.classList.add(classname);
            return 1;
        }
    }
    return 0;
}
function clrem(elem,classname) {
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let num=0;
            elem.forEach(function(subelem) {
                num += clrem(subelem,classname); });
            return num;
        } else if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let num=0;
            for (let n=0; n<elem.length; n++) {
                num += clrem(elem[n],classname);
            }
            return num;
        }
        if (elem.classList) {
            if (!elem.classList.contains(classname)) return 0;
            elem.classList.remove(classname);
            return 1;
        }
    }
    return 0;
}
function clset(elem,classname,boolval) {
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let num=0;
            elem.forEach(function(subelem) {
                num += clset(subelem,classname,boolval); });
            return num;
        } else if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let num=0;
            for (let n=0; n<elem.length; n++) {
                num += clset(elem[n],classname,boolval);
            }
            return num;
        }
        if (elem.classList) {
            if (boolval===elem.classList.contains(classname)) return 0;
            if (boolval) elem.classList.add(classname);
            else elem.classList.remove(classname);
            return 1;
        }
    }
    return 0;
}
function epar(elem,depth) {
    if (elem) {
        if (depth && depth>1) return epar(elem.parentNode,depth-1);
        return elem.parentNode;
    }
    return false;
}
function ekil(elem) {
    if(elem) {
        if(elem.parentNode) elem.parentNode.removeChild(elem);
        else delete elem;
    }
}
function ekfil(elem) {
    if (elem) while (elem.firstChild) elem.removeChild(elem.firstChild);
}
function evl(elem,dfvl) {
    if (!dfvl) dfvl="";
    if (elem && elem.value) return elem.value;
    return dfvl;
}
function ecrea(props) {
    if (isElement(props)) return props;
    let propNames=Object.keys(props);
    if (props.eName) {
        let idx=propNames.indexOf("eName");
        if (idx>=0) propNames.splice(idx,1);
        idx=propNames.indexOf("eText");
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
        return newObj;
    } else if (props.eText) {
        let newObj=document.createTextNode(props.eText);
        return newObj;
    }
    return null;
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
    //addLog("INI function addElemToCellObj "+(cellObj.eName??cellObj.eText??"cellObj")+(cellObj.id?"#"+cellObj.id:cellObj.name?"[name='"+cellObj.name+"']":"")+" <= "+(elem.eName??elem.eText??"elem")+(elem.id?"#"+elem.id:elem.name?"[name='"+elem.name+"']":"")+"\n");
    if (Array.isArray(elem)) {
        if (!cellObj.eChilds) cellObj.eChilds=[];
        if(isArrayAsTable) cellObj.eChilds.push(arrayToHTMLTableObject(elem, keys, tableProperties, rowProperties, cellProperties));
        else elem.forEach(function(item) { addElemToCellObj(cellObj, item, key); });
    } else if (typeof elem === 'object') {
        //console.log("addElemToCellObj ",elem,", "+key);
        if (elem.eName || elem.eText) {
            if (!cellObj.eChilds) cellObj.eChilds=[];
            cellObj.eChilds.push(elem);
        } else addProps(cellObj, elem, ["eName","eChilds","eText"]);
    } else {
        //console.log("addElemToCellObj ",elem,", "+key);
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
    //addLog("INI function addElemToRowObj "+(rowObj.eName??rowObj.eText??"rowObj")+" <= "+(elem.eName??elem.eText??"elem")+"\n");
    if (typeof elem === 'object' && elem !== null && elem.eName && (elem.eName==="TH"||elem.eName==="TD")) {
        addProps(elem, cellProperties, restrictedKeys);
        rowObj.eChilds.push(elem);
        if(rowObj.onappend) {
            //addLog("addElemToRowObj calls function onappend1\n");
            rowObj.onappend(rowObj,elem);
        }
    } else {
        if (cellType!=="TH") cellType="TD";
        const cellObj={eName:cellType, index:nextIndex(rowObj)};
        addProps(cellObj, cellProperties, restrictedKeys);
        addElemToCellObj(cellObj, elem, key, false);
        rowObj.eChilds.push(cellObj);
        if(rowObj.onappend) {
            //addLog("addElemToRowObj calls function onappend2\n");
            rowObj.onappend(rowObj,cellObj);
        }
    }
}
function arrayToHTMLTableObject(array, keys, tableProperties, rowProperties, cellProperties, idSuffix) {
    //addLog("INI function arrayToHTMLTableObject"+(keys?" ["+keys.toString()+"]":"")+"\n");
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
                        //if (cellProperties.ongetvalue) addLog("arrayToHTMLTableObject calls function ongetvalue {"+key+":"+obj[key]+"}\n");
                        const val=(cellProperties.ongetvalue?cellProperties.ongetvalue(obj,key):obj[key]);
                        addElemToRowObj(rowObj, val, key, "TD", cellProperties, restrictedKeys);
                    } else if (cellProperties.onmissedkey) {
                        //addLog("arrayToHTMLTableObject calls function onmissedkey {"+key+":"+obj[key]+"}\n");
                        const val=cellProperties.onmissedkey(obj,key);
                        if (val) { addElemToRowObj(rowObj, val, key, "TD", cellProperties, restrictedKeys); }
                        else { addElemToRowObj(rowObj, {eName:"TD", index:nextIndex(rowObj)}, key, false, cellProperties, restrictedKeys); }
                    } else { addElemToRowObj(rowObj, {eName:"TD", index:nextIndex(rowObj)}, key, false, cellProperties, restrictedKeys); }
                });
            } else {
                //addLog("arrayToHTMLTableObject no keys\n");
                if (colNames.length>0) colNames.forEach(function (colk) {
                    if (colk in obj && obj.hasOwnProperty(colk) && typeof obj[colk] !== "function") {
                        if (obj[colk].eName && obj[colk].eName==="TD") {
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
var backdropObj={eName:"DIV",/*className*/id:"backdrop",eChilds:[],onclick:function(evt){ekil(this);return eventCancel(evt);}};
var backdropTimeout=0;
function clearBackdrop() {
    console.log("INI function clearBackdrop");
    backdropObj.eChilds=[];
    ekfil(ebyid("backdrop"));
    clearTimeout(backdropTimeout);
    console.log("END function clearBackdrop");
}
function addBackdropChild(elemId, elemTop, elemLeft, elemWidth, elemHeight, extraParameters, extraClassList) {
    console.log("INI function addBackdropChild");
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
    console.log("END function appendBackdropChild");
}
function appendBackdropChild(childObj) {
    console.log("INI function appendBackdropChild");
    backdropObj.eChilds.push(childObj);
    const backdrop = ebyid("backdrop");
    if (backdrop) backdrop.appendChild(ecrea(childObj));
    console.log("END function appendBackdropChild");
}
function viewBackdrop() {
    console.log("INI function viewBackdrop");
    const backdrop = ebyid("backdrop");
    if (backdrop) {
        console.log("BACKDROP TO VISIBLE: ", backdrop);
        clrem(backdrop,"hidden");
        clrem(backdrop,"invisible");
    } else if (backdropObj.eChilds.length>0) {
        console.log("BACKDROP CREATING");
        document.body.appendChild(ecrea(backdropObj));
        console.log("BACKDROP CREATED");
    }
    console.log("END function viewBackdrop");
}
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
    console.log("INI function showBackdropChild");
    appendBackdropChild(childObj);
    console.log(backdropObj);
    viewBackdrop();
    console.log("END function showBackdropChild");
}
function closeBackdrop() {
    console.log("INI function closeBackdrop");
    //fee(lbycn("backdrop"),ekil);
    ekil(ebyid("backdrop"));
    clearTimeout(backdropTimeout);
    //backdropObj.eChilds=[];
    console.log("END function closeBackdrop");
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
var chartCodeNames={infected:"Casos totales", sick:"Enfermos", recovered:"Recuperados", dead:"Muertos"};
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
    console.log("INI function viewChartOptions ( "+chartId+" )");
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
            optionsArr.push({eName:"DIV",class:"deleteWithData",style:"display:flex;justify-content:flex-start;align-items:center;padding:10px 20px;",
                eChilds:[{eName:"IMG",src:"imagenes/icons/rollwait2.gif"}]
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
                            console.log("Proceso->list(COVID) => SUCCESS");
                            const chartList=ebyid("chartSelectElement");
                            if (jobj.list&&chartList&&chartList.chartId==3) {
                                console.log("Proceso->list(COVID) => READING "+jobj.list.length+" ITEMS");
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
                            } else console.log("Proceso->list(COVID) => MISSING ELEMENTS");
                        } else if (jobj.result==="failure") {
                            console.log("Proceso->list(COVID) => FAILURE\nQUERY="+jobj.query+"\nERRORS="+JSON.stringify(jobj.error));
                        } else console.log("Proceso->list(COVID) => UNKNOWN RESULT IS "+jobj.result);
                    } else console.log("Proceso->list(COVID) => NO RESULT");
                } catch(ex) {
                    console.log("Exception caught: ", ex, "\nText: ", msg);
                }
            } else console.log("STATE="+state+", STATUS="+status+", LENGTH="+msg.length);
        });
    }
}
function viewChart(optionCode,zoneCode) {
    console.log("INI function viewChart ( "+optionCode+" , "+zoneCode+" )");
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
    if (frame1New) frame1New.src="https://coronavirus.app/chart/"+zoneCode+"/"+optionCode+"/new?embed=true";
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
                            console.log("Proceso->record(COVID) => SUCCESS");
                        } else if (jobj.result==="failure") {
                            console.log("Proceso->record(COVID) => FAILURE\nQUERY="+jobj.query+"\nERRORS="+JSON.stringify(jobj.error));
                        } else console.log("Proceso->record(COVID) => UNKNOWN RESULT IS "+jobj.result);
                    } else console.log("Proceso->record(COVID) => NO RESULT");
                } catch(ex) {
                    console.log("Exception caught: ", ex, "\nText: ", msg);
                }
            } else console.log("STATE="+state+", STATUS="+status+", LENGTH="+msg.length);
        }
    );
    console.log("SERVICE Record Proceso COVID ("+optionCode+","+zoneNames[zoneCode]+") SENT");
    console.log("END function viewChart");
}
