<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.catalogo");
clog1seq(1);
?>
var browser = "<?= getBrowser() ?>";
var timeoutCatalog = 0;
function changeToEditable(cellElem, tablename, fieldid, fieldname) {
    var children = cellElem.childNodes;
    var elem = children[0];
    var desc = "";
    for (i=0; i<children.length; i++) {
        if(i>0) desc+=",";
        desc+=children[i].nodeName+"("+children[i].nodeType+")";
        if(children[i].nodeType==3) desc+=children[i].nodeValue;
        else desc+=children[i].value;
    }
    conlog("changeToEditable("+cellElem.parentNode.id+", "+tablename+", "+fieldid+", "+fieldname+") => "+desc);
    if (elem.nodeType!=3) return; // 1:HtmlElement, 3:#text
    var value = cellElem.innerHTML;
    var code = "<input type='text' id='txt"+tablename+"_"+fieldid+"_"+fieldname+"' value='"+value+"' oldvalue='"+value+"'>";
    code+="<img src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' onload='setFocusOn(\"txt"+tablename+"_"+fieldid+"_"+fieldname+"\"); this.parentNode.removeChild(this);'>";
    cellElem.innerHTML = code;
    cellElem.oldvalue = value;
    
    var forma = document.getElementById("formaCatalogo");
    var originalData = getOrMakeElementAt(forma, "INPUT", {type:"hidden", name:"originalData["+tablename+"]["+fieldid+"]["+fieldname+"]", id:tablename+"_"+fieldid+"_"+fieldname});
    if (!originalData.value) originalData.value = value;
    
}
function setFocusOn(elemId) {
    conlog("setFocusOn("+elemId+")");
    var elem = document.getElementById(elemId);
    if (elem) {
        conlog("Element "+elemId+" Found");
        elem.focus();
        elem.addEventListener("blur", storeElement, false);
        elem.addEventListener("keyup", canStoreElement, false);
    }
}
function storeElement(evt) {
    conlog("storeElement()");
    if (!evt) evt = window.event;
    var elem = evt.target;
    var elemParent = elem.parentNode;
    var elemData = elem.id.split("_");
    var tablename = elemData[0].substr(3);
    var fieldname = elemData[2];
    var fieldid = elemData[1];
    conlog("storeElement( table="+tablename+", id="+fieldid+", column="+fieldname+", value="+elem.value+" oldvalue="+elem.getAttribute("oldvalue")+" )");
    
    elem.removeEventListener("blur", storeElement, false);
    elem.removeEventListener("keyup", canStoreElement, false);
    elemParent.innerHTML = elem.value;
    var forma = document.getElementById("formaCatalogo");
    var originalData = document.getElementById(tablename+"_"+fieldid+"_"+fieldname);
    var hasModifiedData = false;
    if (!originalData) conlog("originalData MISSING!");
    if (!elem) conlog("elem MISSING!");
    if (originalData.value==elem.value) {
        forma.removeChild(originalData);
        var modifiedData = document.getElementById(tablename+"_"+fieldid+"_"+fieldname+"_new");
        if (modifiedData) forma.removeChild(modifiedData);
        if (forma.elements) {
            for (var i=0; i<forma.elements.length; i++) { // >
                if(forma.elements[i].name.substr(0,12)=="modifiedData") {
                    hasModifiedData = true;
                    break;
                }
            }
        }
    } else {
        var modifiedData = getOrMakeElementAt(forma, "INPUT", {type:"hidden", name:"modifiedData["+tablename+"]["+fieldid+"]["+fieldname+"]", id:tablename+"_"+fieldid+"_"+fieldname+"_new"});
        modifiedData.value = elem.value;
        hasModifiedData = true;
    }
    var fxBtns = document.getElementsByClassName("btnFX");
    for (var i=0; i<fxBtns.length; i++) { // >
        if (hasModifiedData && fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.remove("hidden");
        else if (!hasModifiedData && !fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.add("hidden");
    }
}
function doUploadData(evt) {
    if (!evt) evt = window.event;
    let tgt = evt.target;
    if (tgt.classList.contains("disabled")) return;
    console.log("upload data");
}
function doDownloadData(evt) {
    if (!evt) evt = window.event;
    let tgt = evt.target;
    if (tgt.classList.contains("disabled")) return;
    console.log("download data");
}
function doRollBack(evt) {
    if (!evt) evt = window.event;
    var tgt = evt.target;
    if (tgt.classList.contains("disabled")) return;
    overlayConfirmation("<p>Confirme que desea eliminar los cambios y restaurar los valores originales.</p>", "Restaurar Originales", confirmRollBack);
}
function confirmRollBack() {
    var forma = document.getElementById("formaCatalogo");
    if (forma.elements) {
        for (var i=0; i<forma.elements.length; i++) { // >
            if(forma.elements[i].name.substr(0,12)=="originalData") {
                var elemArr = forma.elements[i].name.replace(/\[/g, "|").replace(/\]/g, "").split("|");
                var name = "cell"+elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3];
                var fixElem = document.getElementById(name);
                var value = forma.elements[i].value;
                if (fixElem) fixElem.innerHTML = value;
                var originalData = document.getElementById(elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3]);
                if (originalData) forma.removeChild(originalData);
                var modifiedData = document.getElementById(elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3]+"_new");
                if (modifiedData) forma.removeChild(modifiedData);
            }
        }
    }

    var fxBtns = document.getElementsByClassName("btnFX");
    for (var i=0; i<fxBtns.length; i++) { // >
        if (!fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.add("hidden");
    }
}
function doCommit(evt) {
    if (!evt) evt = window.event;
    var tgt = evt.target;
    if (tgt.classList.contains("disabled")) return;
    var forma = document.getElementById("formaCatalogo");
    var txt = "<p>Confirme que desea guardar los cambios:<br>";
    if (forma.elements) {
        for (var i=0; i<forma.elements.length; i++) { // >
            if(forma.elements[i].name.substr(0,12)=="modifiedData") {
                var elemArr = forma.elements[i].name.replace(/\[/g, "|").replace(/\]/g, "").split("|");
                var value = forma.elements[i].value;
                txt += elemArr[1]+"[id="+elemArr[2]+"]."+elemArr[3]+" = "+value+"<br>";
            }
        }
    }
    txt += "</p>";
    overlayConfirmation(txt, "Guardar Cambios", confirmCommit);
}
function confirmCommit() {
    var catalogo_admin = document.getElementById("catalogo_admin");
    if(catalogo_admin) catalogo_admin.value="commit";

    var postURL = 'consultas/Facturas.php';
    var formName = 'formaCatalogo';
    var resultDiv = 'dialog_resultarea';
    overlayMessage("<p>Guardando...</p>");
    ajaxPost(postURL, formName, resultDiv, '', successSentChanges);
}
function successSentChanges(xmlHttpPost) {
    var forma = document.getElementById("formaCatalogo");
    if (forma.elements) {
        for (var i=0; i<forma.elements.length; i++) { // >
            if(forma.elements[i].name.substr(0,12)=="originalData") {
                var elemArr = forma.elements[i].name.replace(/\[/g, "|").replace(/\]/g, "").split("|");
                var name = "cell"+elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3];
                var fixElem = document.getElementById(name);
//                var value = forma.elements[i].value;
//                if (fixElem) fixElem.innerHTML = value;
                var originalData = document.getElementById(elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3]);
                if (originalData) forma.removeChild(originalData);
                var modifiedData = document.getElementById(elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3]+"_new");
                if (modifiedData) forma.removeChild(modifiedData);
            }
        }
    }
    var fxBtns = document.getElementsByClassName("btnFX");
    for (var i=0; i<fxBtns.length; i++) { // >
        if (!fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.add("hidden");
    }
}
function canStoreElement(evt) {
    if (!evt) evt = window.event;
    var elem = evt.target;
    if(checkEscape(evt)===true) {
        var elemParent = elem.parentNode;
        conlog("Escape "+elem+" "+elem.value+"|"+elemParent["oldvalue"]);
        elem.removeEventListener("blur", storeElement, false);
        elem.removeEventListener("keyup", canStoreElement, false);
        elemParent.innerHTML = elemParent.oldvalue;
    }
    if(checkEnter(evt)===false) return;
    conlog("BLUR");
    elem.blur();
}
function checkEnter(evt) {
//    conlog("checkEnter() "+(evt?evt.type+" "+evt.keyCode:""));
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


function submitChangePage(nombre, pagina) {
    conlog("submitChangePage("+nombre+", "+pagina+")");
    var forma = document.getElementById("formaCatalogo");

    var lastcattable = getOrMakeElementAt(forma, "INPUT", {type:"hidden", name:"lastcattable", id:"lastcattable"});
    lastcattable.value = nombre;

    var lastcattablepg = getOrMakeElementAt(forma, "INPUT", {type:"hidden", name:"lastcattablepg", id:"lastcattablepg"});
    lastcattablepg.value = pagina;

    var sortColElem = document.getElementById("sortColVal_"+nombre);
    var sortModElem = document.getElementById("sortModVal_"+nombre);
    if (sortColElem) { // && sortColElem.value.length>0) {
        var cattbsortcol = getOrMakeElementAt(forma, "INPUT", {type:"hidden", name:"cattbsortcol", id:"cattbsortcol"});
        cattbsortcol.value = sortColElem.value;
        conlog("Defined sortCol "+sortColElem.value);
    }
    if (sortModElem) { // && sortModElem.value.length>0) {
        var cattbsortmod = getOrMakeElementAt(forma, "INPUT", {type:"hidden", name:"cattbsortmod", id:"cattbsortmod"});
        cattbsortmod.value = sortModElem.value;
        conlog("Defined sortMod "+sortModElem.value);
    }
    
    submitAjax(nombre, function(xmlHttpPost) { successChangedPage(nombre); });
}
function successChangedPage(nombre) {
    conlog("successChangedPage("+nombre+")");

    var cPageElem = document.getElementById("currPgVal_"+nombre);
    var lPageElem = document.getElementById("lastPgVal_"+nombre);
    var pagina = cPageElem.value;
    var ultima = lPageElem.value;

    var btnFrst = document.getElementById("btnFrst_"+nombre);
    var btnBack = document.getElementById("btnBack_"+nombre);
    var btnFwrd = document.getElementById("btnFwrd_"+nombre);
    var btnLast = document.getElementById("btnLast_"+nombre);
    var pagNum = document.getElementById("pagNum_"+nombre);
    var lastPag = document.getElementById("lastPag_"+nombre);

    if (pagNum) pagNum.innerHTML = pagina;
    if (lastPag) lastPag.innerHTML = ultima;
    conlog("Pagina: "+pagina+", Ultima: "+ultima);

    if ((+pagina)<=1) btnBack.classList.add('invisible'); // >
    else btnBack.classList.remove('invisible');

    if ((+pagina)<=2) btnFrst.classList.add('invisible'); // >
    else btnFrst.classList.remove('invisible');

    if ((+pagina)>=(+ultima)) btnFwrd.classList.add('invisible');
    else btnFwrd.classList.remove('invisible');

    if ((+pagina+1)>=(+ultima)) btnLast.classList.add('invisible');
    else btnLast.classList.remove('invisible');

    var forma = document.getElementById("formaCatalogo");
    if (forma.elements) {
        for (var i=0; i<forma.elements.length; i++) { // >
            if(forma.elements[i].name.substr(0,12)=="modifiedData") {
                var elemArr = forma.elements[i].name.replace(/\[/g, "|").replace(/\]/g, "").split("|");
                var name = "cell"+elemArr[1]+"_"+elemArr[2]+"_"+elemArr[3];
                var elem = document.getElementById(name);
                var value = forma.elements[i].value;
                if (elem) elem.innerHTML = value;
            }
        }
    }
}
function submitCallbackSuccessFunc(xmlHttpPost) {
    conlog("Success Callback!");
}
function submitAjax(nombre, callbackfunc) {
    if (!callbackfunc) callbackfunc=submitCallbackSuccessFunc;
    var colsElem = document.getElementById("noColsVal_"+nombre);
    
    conlog("submitAjax: "+nombre);
    var postURL = 'consultas/'+nombre+'.php';
    var formName = 'formaCatalogo';
    var resultDiv = 'tbody_'+nombre;
    var waitingHtml = '';
    if (colsElem && colsElem.value) waitingHtml = '<tr><td colspan=\''+colsElem.value+'\' class=\'centered\'><img src=\'imagenes/icons/flying.gif\' width=\'360\' height=\'360\'></td></tr>';
    conlog("to ajaxPost: "+postURL+", "+formName+", "+resultDiv+".");
    document.getElementById(resultDiv).innerHTML = "";
    ajaxPost(postURL, formName, resultDiv, waitingHtml, callbackfunc);
    conlog("submitAjax DONE!");
    return false;
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
function getParentTable(elem) {
    if (!elem) return false;
    var parentNode = elem.parentNode;
    if (parentNode && parentNode.tagName.toLowerCase() === 'table') return parentNode;
    return getParentTable(parentNode);
}
function doPageChange(accion) {
    var process=false;
    var currPg = document.getElementById("catalog_currPg");
    if (currPg) {
        var num = +currPg.value;
        var lastPg = document.getElementById("catalog_lastPg");
        var lnum = +lastPg.value;
        switch(accion) {
            case "frst": if (num>1) { currPg.value="1"; process=true; } break;
            case "prev": if (num>1) { currPg.value=(num-1); process=true; } break;
            case "next": if (num<lnum) { currPg.value=(num+1); process=true; } break; // >
            case "last": if (num<lnum) { currPg.value=lastPg.value; process=true; } break; // >
        }
    }
    if (process) viewTable(false,false,"page");
}
function sortPageBy(elem) {
    var elemName = elem.innerHTML;
    //conlog("INI sortPageBy "+elemName);
    var process=false;
    var orderElem = false;
    if (elem.nextElementSibling && elem.nextElementSibling.tagName==="SPAN" && elem.nextElementSibling.classList.contains("catColumnOrderSpan")) orderElem = elem.nextElementSibling;
    var sortByElem = document.getElementById("catalog_sortBy");
    var sortByQuery = sortByElem.value;
    var idx = sortByQuery.indexOf(elemName+" asc");
    if (idx>=0) { // asc to desc
        sortByQuery = sortByQuery.slice(0,idx+elemName.length+1)+"de"+sortByQuery.slice(idx+elemName.length+2);
        if (orderElem) {
            while(orderElem.firstChild) orderElem.removeChild(orderElem.firstChild);
            orderElem.appendChild(document.createTextNode(" \u00AB "));
        }
        process=true;
    } else {
        idx = sortByQuery.indexOf(elemName+" desc");
        if (idx>=0) {
            if (idx===0) {
                if (sortByQuery.length===(elemName.length+5)) { sortByQuery=""; process=true; }
                else { sortByQuery = sortByQuery.slice(elemName.length+6); process=true; }
            } else if (sortByQuery.length===(idx+elemName.length+5)) {
                sortByQuery = sortByQuery.slice(0, idx-1);
                process=true;
            } else {
                sortByQuery = sortByQuery.slice(0, idx-1)+sortByQuery.slice(idx+elemName.length+5);
                process=true;
            }
            if (orderElem) {
                while(orderElem.firstChild) orderElem.removeChild(orderElem.firstChild);
                orderElem.appendChild(document.createTextNode("   "));
            }
        } else {
            if (sortByQuery.length>0) sortByQuery+="|";
            sortByQuery+=elemName+" asc";
            if (orderElem) {
                while(orderElem.firstChild) orderElem.removeChild(orderElem.firstChild);
                orderElem.appendChild(document.createTextNode(" \u00BB "));
            }
            process=true;
        }
    }
    // TODO: @consultas/catalogo :  query .= ORDER BY POST[sortBy]
    if (process) {
        //conlog(sortByQuery);
        sortByElem.value=sortByQuery;
        viewTable(false,false,"sort");
    }
}
function filterPageBy(event) {
    //conlog("INI filterPageBy");
//    var process=false;
    // TODO: loop in filterFields (if (cell not hidden) catalog.filterBy+=AND cell.name (=|like|in) cell.filterField.value
    const filterItem=document.getElementById("catalog_filterItem");
    let theFilterField=false;
    if (this.classList&&this.classList.contains("filterField")) theFilterField=this;
    else if (event && event.target && event.target.classList && event.target.classList.contains("filterField")) theFilterField=event.target;
    var filterFields = document.getElementsByClassName("filterField");
    var filterBy="";
    for (var i=0; i<filterFields.length; i++) { // >
        if (!filterFields[i].classList.contains("hidden") && filterFields[i].value.length>0) {
            if (filterBy.length>0) filterBy+="|";
            var ffval = filterFields[i].value;
            if (filterFields[i].tagName!=="SELECT") {
                if (ffval.charAt(0)==='%' || ffval.charAt(ffval.length-1)==='%') { ffval = ffval.replace("%","*"); filterFields[i].value=ffval; }
                if (ffval.charAt(0)!=='%' && ffval.charAt(0)!=='*' && ffval.charAt(ffval.length-1)!=='%' && ffval.charAt(ffval.length-1)!=='*') ffval = "*"+ffval+"*";
            }
            filterBy+=filterFields[i].colname+"="+ffval;
            if (filterFields[i]===theFilterField) filterItem.value=theFilterField.colname;
//            process=true;
        }
    }
//    if (process) {
        //conlog(filterBy);
        var filterElem = document.getElementById("catalog_filterBy");
        filterElem.value=filterBy;
        viewTable(false,false,"filter");
//    }
}
function transmuteToTextField(evt) {
    var target = evt.target || evt.srcElement;
    conlog("INI transmuteToTextField "+target.tagName+" : "+target.value);
}
function viewTable(tabname, tabtitle, lastAction) {
    //conlog("INI viewTable "+tabname+" "+tabtitle+" "+lastAction);
    var msg="";
    var tname = document.getElementById("catalog_tablename");
    if (tname) {
        if (tabname) tname.value=tabname;
        msg+=" Table Name="+tname.value;
    }
    var tvname = document.getElementById("catalog_tableviewname");
    if (tvname) {
        if (tabtitle) tvname.value=tabtitle;
        msg+=" Table View Name="+tvname.value;
    }
    var ncols = document.getElementById("catalog_noCols");
    if (ncols) {
        if (!(ncols.value)) ncols.value="10";
        msg+=" Num Cols="+ncols.value;
    }
    var curPg = document.getElementById("catalog_currPg");
    if (curPg) {
        if (!(curPg.value)) curPg.value="0";
        msg+=" Curr Page="+curPg.value;
    }
    var lstPg = document.getElementById("catalog_lastPg");
    if (lstPg) {
        if (!(lstPg.value)) lstPg.value="0";
        msg+=" Last Page="+lstPg.value;
    }
    var lstActElem = document.getElementById("catalog_lastAction");
    if (lstActElem) {
        if (lastAction) lstActElem.value=lastAction;
        else if (tabname && tabtitle) lstActElem.value="table";
    }
    var colDataElem = document.getElementById("catalog_column_table");
    colDataElem.disabled=true;
    cladd(colDataElem,"disabled");
    ajaxPost('consultas/catalogo.php', 'formaCatalogo', false, '', fillData);
    //conlog("END viewTable "+msg);
}
function fillData(xmlHttpPost) {
    var colDataElem = document.getElementById("catalog_column_table");
    colDataElem.disabled=false;
    clrem(colDataElem,"disabled");
    let textData = xmlHttpPost.responseText;
    //console.log("INI fillData\n"+textData);
    const lstActElem = ebyid("catalog_lastAction");
    var doWidthFixes = true; // lstActElem && lstActElem.value!=="sort" && lstActElem.value!=="filter";
    //console.log("Last Action: "+lstActElem.value);
    let isCatalog=(lstActElem.value==="catalog");
    let isTable=(lstActElem.value==="table");
    var hiddenIndexes = [];
    var jsonObj = false;
    try {
        jsonObj = JSON.parse(textData);
    } catch (e) {
        var phpErrors = "";
        for (var comIdx=textData.indexOf("<!--"), endIdx=textData.indexOf("-->",comIdx); comIdx>=0 && endIdx>0; comIdx=textData.indexOf("<!--"), endIdx=textData.indexOf("-->",comIdx)) {
            //conlog("DEBUG: comIdx="+comIdx+", endIdx="+endIdx+", data="+textData.substring(comIdx+4,endIdx).trim());
            phpErrors += "PHP TRACE: "+textData.substring(comIdx+4,endIdx).trim()+"\n";
            if (comIdx==0) textData = textData.substring(endIdx+3).trim();
            else textData = textData.substring(0,comIdx).trim()+textData.substring(endIdx+3).trim();
        }
        if (textData.length>0) {
            try {
                jsonObj = JSON.parse(textData);
                if (!jsonObj.log) jsonObj.log="";
                jsonObj.log += phpErrors;
            } catch (e2) {
                jsonObj = {"columnNames":["descripcion"],"columnGrant":["r"],"columnTypes":["s"],"resultData":[[textData]],"tablename":"error","tableviewname":"ERROR","noCols":10,"currPG":1,"lastPG":1,"totReg":1,"log":"EXCEPTION 2: "+e2.message+"\nEXCEPTION 1: "+e.message+"\n"+phpErrors};
            }
        } else
            jsonObj = {"columnNames":["descripcion"],"columnGrant":["r"],"columnTypes":["s"],"resultData":"","tablename":"error","tableviewname":"ERROR","noCols":10,"currPG":1,"lastPG":1,"totReg":1,"log":"EMPTY DATA"};
    }
    var catColWrap = document.getElementById("catalog_column_wrapper");
    if (catColWrap && catColWrap.classList && catColWrap.classList.contains("nofilter")) {
        catColWrap.classList.remove("nofilter");
        if (catColWrap.className == "") catColWrap.removeAttribute('class');
    }
    var catColSect = document.getElementById("catalog_column_section");
    if (catColSect && catColSect.classList && catColSect.classList.contains("nofilter")) {
        catColSect.classList.remove("nofilter");
        if (catColSect.className == "") catColSect.removeAttribute('class');
    }
    var catConSect = document.getElementById("catalog_content_section");
    if (catConSect && catConSect.classList && catConSect.classList.contains("nofilter")) {
        catConSect.classList.remove("nofilter");
        if (catConSect.className == "") catConSect.removeAttribute('class');
    }
    var catPagSect = document.getElementById("catalog_page_section");
    if (catPagSect && catPagSect.classList && catPagSect.classList.contains("hidden")) {
        catPagSect.classList.remove("hidden");
        if (catPagSect.className == "") catPagSect.removeAttribute('class');
    }
    if (jsonObj) {
        if (jsonObj.tablename) {
            if (doWidthFixes) {
                var legendElem = document.getElementById("catalog_legend");
                if (legendElem) {
                    while(legendElem.firstChild) legendElem.removeChild(legendElem.firstChild);
                    if (jsonObj.tableviewname)
                        legendElem.appendChild(document.createTextNode(jsonObj.tableviewname));
                    else if (jsonObj.tablename)
                        legendElem.appendChild(document.createTextNode(jsonObj.tablename));
                }
                var colDataElem = document.getElementById("catalog_column_table");
                if (colDataElem && colDataElem.firstElementChild && colDataElem.firstElementChild.tagName==="THEAD") {
                    colDataElem.classList.remove("fit");
                    colDataElem=colDataElem.firstElementChild;
                }
                var filterRowElem = false;
                if (colDataElem && colDataElem.firstElementChild && colDataElem.firstElementChild.tagName==="TR") {
                    colDataElem=colDataElem.firstElementChild;
                    if (colDataElem.nextElementSibling && colDataElem.nextElementSibling.tagName==="TR") {
                        filterRowElem = colDataElem.nextElementSibling;
                        while(filterRowElem.firstChild) filterRowElem.removeChild(filterRowElem.firstChild);
                    } else {
                        filterRowElem = document.createElement("TR");
                        colDataElem.parentNode.appendChild(filterRowElem);
                    }
                }
                var sortByElem = document.getElementById("catalog_sortBy");
                var sortArr = {};
                if (sortByElem) {
                    var tmpArr = sortByElem.value.split("|");
                    for (var i=0; i<tmpArr.length; i++) { // >
                        var tuple = tmpArr[i].split(" ");
                        if (tuple[1]==="asc") sortArr[tuple[0]]=" \u00BB ";
                        else if (tuple[1]==="desc") sortArr[tuple[0]]=" \u00AB ";
                    }
                }
                var filterByElem = document.getElementById("catalog_filterBy");
                if (jsonObj.filterBy) filterByElem.value=jsonObj.filterBy;
                var filterArr = [];
                if (filterByElem) {
                    var tmpArr = filterByElem.value.split("|");
                    for (var i=0; i<tmpArr.length; i++) { // >
                        var tuple = tmpArr[i].split("=");
                        if (tuple[1]) {
                            tuple[1] = tuple[1].replace("%","*");
                            var t1len = tuple[1].length;
                            if (tuple[1]==="*") tuple[1]="";
                            else if (t1len>1 && tuple[1].charAt(0)==="*" && tuple[1].charAt(t1len-1)==="*") tuple[1]=tuple[1].slice(1,-1);
                            filterArr[tuple[0]]=tuple[1];
                        }
                    }
                }
                const hasHideColumnBtn = (!jsonObj.tableOptions || !jsonObj.tableOptions.includes("noHideColumnBtn"));
                const hasShowColumnBtn = (!jsonObj.tableOptions || !jsonObj.tableOptions.includes("noShowColumnBtn"));
                if (colDataElem && colDataElem.firstElementChild && colDataElem.firstElementChild.tagName==="TH") {
                    while(colDataElem.firstChild) colDataElem.removeChild(colDataElem.firstChild);
                    if (jsonObj.columnNames) {
                        var columnNames = jsonObj.columnNames;
                        for (var i=0; i<columnNames.length; i++) { // >
                            var cellDiv = document.createElement("DIV");
                            cellDiv.classList.add("relative100");
                            var txtSpan = document.createElement("SPAN");
                            txtSpan.classList.add("catColumnNameSpan");
                            txtSpan.classList.add("asLinkH");
                            txtSpan.onclick=function() { sortPageBy(this); };
                            txtSpan.appendChild(document.createTextNode(columnNames[i]));
                            cellDiv.appendChild(txtSpan);
                            var spSpan = document.createElement("SPAN");
                            spSpan.classList.add("catColumnOrderSpan");
                            spSpan.appendChild(document.createTextNode(sortArr[columnNames[i]]?sortArr[columnNames[i]]:" \u00A0\u00A0"));
                            cellDiv.appendChild(spSpan);
                            if (hasHideColumnBtn) {
                                var cmdXImg = document.createElement("IMG");
                                cmdXImg.src="imagenes/hideColumnBtn.png";
                                cmdXImg.classList.add("catalog_column_hide_button");
                                cmdXImg.idx=(i+1);
                                cmdXImg.colname=columnNames[i];
                                cellDiv.appendChild(cmdXImg);
                            }
                            if (hasShowColumnBtn) {
                                var cmdOImg = document.createElement("IMG");
                                cmdOImg.src="imagenes/showColumnBtn.png";
                                cmdOImg.classList.add("catalog_column_show_button");
                                cmdOImg.idx=(i+1);
                                cmdOImg.colname=columnNames[i];
                                cellDiv.appendChild(cmdOImg);
                            }
                            var cell = document.createElement("TH");
                            cell.appendChild(cellDiv);
                            cell.style.width="1%";
                            cell.style.whiteSpace="nowrap";
                            cell.classList.add("cell_"+(i+1));
                            cell.classList.add("cell_header");
                            cell.id="col_"+columnNames[i];
                            cell.name=columnNames[i];
                            if (hasCookie("hidden_cell_"+columnNames[i])) {
                                cell.classList.add("hidden");
                                hiddenIndexes.push(i);
                            }
                            colDataElem.appendChild(cell);
                            
                            cell = document.createElement("TD");
                            var filterElem = false;
                            if (jsonObj.columnComment && jsonObj.columnComment[columnNames[i]]) {
                                filterElem = document.createElement("SELECT");
                                filterElem.classList.add("adminSelect");
                                filterElem.addEventListener('dblclick',transmuteToTextField.bind(filterElem),false);
                                var list = jsonObj.columnComment[columnNames[i]];
                                var opt = document.createElement("OPTION");
                                opt.value = "";
                                opt.text = "";
                                filterElem.appendChild(opt);
                                for (var o=0; o<list.length; o++) { // >
                                    opt = document.createElement("OPTION");
                                    opt.value = list[o];
                                    opt.text = list[o];
                                    filterElem.appendChild(opt);
                                }
                                if (columnNames[i]!=="FECHA" && list.length>0) {
                                    const currName="CURR_"+columnNames[i];
                                    let currElem=ebyid(currName);
                                    if (!currElem) {
                                        const forma = document.getElementById("formaCatalogo");
                                        currElem = document.createElement("INPUT");
                                        currElem.type="hidden";
                                        currElem.name=currName;
                                        forma.appendChild(currElem);
                                    }
                                    currElem.value=list.join();
                                }
                            } else {
                                filterElem = document.createElement("INPUT");
                                filterElem.type="text";
                                filterElem.classList.add("longtext");
                            }
                            if (jsonObj.columnTypes && jsonObj.columnTypes[i]) filterElem.setAttribute("dbtype", jsonObj.columnTypes[i]);
                            filterElem.colname=columnNames[i];
                            filterElem.classList.add("filterField");
                            filterElem.classList.add("hidden");
                            filterElem.onchange=filterPageBy;
                            if (filterArr[columnNames[i]]) filterElem.value = filterArr[columnNames[i]];
                            cell.appendChild(filterElem);
                            cell.classList.add("cell_"+(i+1));
                            if (hasCookie("hidden_cell_"+columnNames[i])) cell.classList.add("hidden");
                            filterRowElem.appendChild(cell);
                    
                            if (hasHideColumnBtn) cmdXImg.addEventListener('click', function() { hideCatColumn(this.idx, this.colname); });
                            if (hasShowColumnBtn) cmdOImg.addEventListener('click', function() { showCatColumn(this.idx, this.colname); });
                        }
                    }
                
                    var scrollCell = document.createElement("TD");
                    scrollCell.id="scrollCell";
                    scrollCell.classList.add("scrollCell");
                    scrollCell.style.width="9px"; // ToDo: Checar ancho de scrollbar de catalog_content_table
                    scrollCell.style.minWidth="9px";
                    scrollCell.style.maxWidth="9px";
                    scrollCell.style.borderTop="0";
                    scrollCell.style.borderRight="0";
                    scrollCell.style.borderBottom="0";
                    colDataElem.appendChild(scrollCell);
                }
            }
            var dataElem = document.getElementById("catalog_content_table");
            if (dataElem && dataElem.firstElementChild && dataElem.firstElementChild.tagName==="TBODY") {
                if (doWidthFixes) dataElem.classList.remove("fit");
                dataElem=dataElem.firstElementChild;
            }
            if (dataElem && dataElem.firstElementChild && dataElem.firstElementChild.tagName==="TR") {
                if (doWidthFixes) while(dataElem.firstChild) dataElem.removeChild(dataElem.firstChild);
                var resultData = jsonObj.resultData?jsonObj.resultData:[];
                var columnGrant = jsonObj.columnGrant?jsonObj.columnGrant:[];
                var columnTypes = jsonObj.columnTypes?jsonObj.columnTypes:[];
                var columnSpan = jsonObj.columnSpan?jsonObj.columnSpan:[];
                var columnClass = jsonObj.columnClass?jsonObj.columnClass:[];
                for (var i=0; i<resultData.length; i++) { // >
                    var rowData = resultData[i];
                    var row = null;
                    if (doWidthFixes) row = document.createElement("TR");
                    else row = dataElem.children[i];
                    //console.log("ROW "+i+(doWidthFixes?"(dWF)":"(x)")+" = ",row);
                    if (row) {
                        for (var j=0; j<rowData.length; j++) { // >
//conlog("CELL idx "+j);
                            var cell = null;
                            if (doWidthFixes) { // ???
                                cell = document.createElement("TD");
                                cell.style.width="1%";
                                cell.style.whiteSpace="nowrap";
                                cell.classList.add("cell_"+(j+1));
                                if (hiddenIndexes.indexOf(j)>=0) cell.classList.add("hidden");
                                row.appendChild(cell);
                            } else {
                                cell = row.children[j];
                                while(cell.firstChild) cell.removeChild(cell.firstChild);
                                cell.style.whiteSpace="";
                            }
                            cell.appendChild(document.createTextNode(rowData[j]));
                            if (columnGrant[j]) cell.setAttribute("grant", columnGrant[j]);
                            if (columnTypes[j]) cell.setAttribute("type", columnTypes[j]);
                            if (columnSpan[j]) cell.colSpan = ""+columnSpan[j];
                            if (columnClass[j]) {
                                if (!cell.className || cell.className.length==0) cell.className=columnClass[j];
                                else cell.className+=" "+columnClass[j];
                            }
                        }
                        if (doWidthFixes) dataElem.appendChild(row);
                        else {
                            row.classList.remove("hidden");
                            row.classList.remove("invisible");
                        }
                    }
                }
                if (jsonObj.noCols && resultData.length<+jsonObj.noCols) { // >
                    var maxNum = +jsonObj.noCols;
                    //console.log("HIDDEN ROWS rdL="+resultData.length+", jOnC="+maxNum);
                    for (var i=resultData.length; i<maxNum; i++) { // >
                        var row = dataElem.children[i];
                        //console.log("i="+i+", hasRow="+(row?"Y":"F"));
                        if (!row) {
                            row = document.createElement("TR");
                            if (i==0)
                                row.classList.add("invisible");
                            else
                                row.classList.add("hidden");
                            
                            for (var j=0; j<jsonObj.columnNames.length; j++) { // >
                                cell = document.createElement("TD");
                                cell.classList.add("cell_"+(j+1));
                                if (hiddenIndexes.indexOf(j)>=0) cell.classList.add("hidden");
                                row.appendChild(cell);
                            }
                            dataElem.appendChild(row);
                        } else {
                            if (i==0)
                                row.classList.add("invisible");
                            else
                                row.classList.add("hidden");
                            for (var j=0; j<row.children.length; j++) { // >
                                var cell = row.children[j];
                                while(cell.firstChild) cell.removeChild(cell.firstChild);
                                cell.removeAttribute("grant");
                                cell.removeAttribute("type");
                            }
                        }
                    }
                }
            }
            
            if (jsonObj.log) console.log("LOG: "+jsonObj.log);
            var btnFrst = document.getElementById("btnFrst");
            var btnBack = document.getElementById("btnBack");
            var btnFwrd = document.getElementById("btnFwrd");
            var btnLast = document.getElementById("btnLast");
            var numRegs = document.getElementById("numRegs");
            if (numRegs) {
                for (var i=0; i<numRegs.options.length; i++) { // >
                    //console.log("NumRegs OPTION "+i+" value="+numRegs.options[i].value+", text="+numRegs.options[i].text);
                    if (numRegs.options[i].value == jsonObj.noCols)
                        numRegs.options[i].selected=true;
                    else numRegs.options[i].selected=false;
                }
            }
            var pagNum = document.getElementById("pagNum");
            if (pagNum) {
                while(pagNum.firstChild) pagNum.removeChild(pagNum.firstChild);
                if (jsonObj.currPG) pagNum.appendChild(document.createTextNode(jsonObj.currPG));
            }
            var currPG = document.getElementById("catalog_currPg");
            if (currPG && jsonObj.currPG) currPG.value=jsonObj.currPG;
            var lastPag = document.getElementById("lastPag");
            if (lastPag) {
                while(lastPag.firstChild) lastPag.removeChild(lastPag.firstChild);
                if (jsonObj.lastPG) lastPag.appendChild(document.createTextNode(jsonObj.lastPG));
            }
            var lastPG = document.getElementById("catalog_lastPg");
            if (lastPG && jsonObj.lastPG) lastPG.value=jsonObj.lastPG;
            var mylog = document.getElementById("mylog");
            if (mylog && jsonObj.log) {
                mylog.appendChild(document.createTextNode("CAT LOG:\n"+jsonObj.log+"\n"));
            }
            var catContentElem = document.getElementById("catalog_content");
            if (catContentElem) catContentElem.classList.remove("hidden");
            if (jsonObj.currPG && 1==(+jsonObj.currPG)) {
                btnFrst.disabled=true;
                btnBack.disabled=true;
            } else {
                btnFrst.disabled=false;
                btnBack.disabled=false;
            }
            if (jsonObj.currPG && jsonObj.lastPG && (+jsonObj.currPG)==(+jsonObj.lastPG)) {
                btnFwrd.disabled=true;
                btnLast.disabled=true;
            } else {
                btnFwrd.disabled=false;
                btnLast.disabled=false;
            }
            if (jsonObj.lastPG && jsonObj.totReg && 1==(+jsonObj.lastPG) && 10>=(+jsonObj.totReg)) {
                numRegs.disabled=true;
            } else {
                numRegs.disabled=false;
            }
            if (isTable || isCatalog) {
                let upBtn = document.getElementById("uploadCatalogIcon");
                let dwBtn = document.getElementById("downloadCatalogIcon");
                if (upBtn) {
                    if (isTable) upBtn.classList.add("disabled");
                    else upBtn.classList.remove("disabled");
                }
                if (dwBtn) {
                    if (isTable) dwBtn.classList.add("disabled");
                    else dwBtn.classList.remove("disabled");
                }
            }

            if (doWidthFixes) {
                clearTimeout(timeoutCatalog);
                timeoutCatalog = setTimeout(resetTableWidths, 10);
            }
        }
    }
}

function hideCatColumn(idx, colname) {
    var headerCells = document.getElementsByClassName("cell_header");
    var visibleCount = 0;
    for (var i=0; i<headerCells.length; i++) { // >
        if (!headerCells[i].classList.contains("hidden")) visibleCount++;
    }
    if (visibleCount>1) {
        var cellsInColumn = document.getElementsByClassName("cell_"+idx);
        if (!hasCookie("hidden_cell_"+colname)) addCookie("hidden_cell_"+colname, true);
        for (var i=0; i<cellsInColumn.length; i++) { // >
            cellsInColumn[i].classList.add("hidden");
        }
        visualAdjustmentsOnColumnVisibilityChanges();
    }
}
function showCatColumn(idx, colname) {
    var cellHeaders = document.getElementsByClassName("cell_header cell_"+idx);
    if (cellHeaders.length>0) {
        ch = cellHeaders[0];
        var realIndex = false;
        if (ch.nextElementSibling && ch.nextElementSibling.tagName==="TH" && ch.nextElementSibling.classList.contains("hidden")) {
            realIndex = (+idx+1);
            colname = ch.nextElementSibling.name;
        } else if (ch.previousElementSibling && ch.previousElementSibling.tagName==="TH" && ch.previousElementSibling.classList.contains("hidden")) {
            realIndex = (+idx-1);
            colname = ch.previousElementSibling.name;
        }
        if (realIndex!==false) {
            var cellsInColumn = document.getElementsByClassName("cell_"+realIndex);
            var cookieName = "hidden_cell_"+colname;
            if (hasCookie(cookieName)) delCookie(cookieName);
            for (var i=0; i<cellsInColumn.length; i++) { // >
                cellsInColumn[i].classList.remove("hidden");
            }
            resetTableWidths();
        }
    }
}
function resetTableWidths() {
    var cellHeaders = document.getElementsByClassName("cell_header");
    for (var i=0; i<cellHeaders.length; i++) { // >
        var cell = cellHeaders[i];
        cell.style.width="1%";
        cell.style.minWidth=null;
    }
    var dataTable = document.getElementById("catalog_content_table");
    if (dataTable.firstElementChild && dataTable.firstElementChild.tagName==="TBODY") {
        var tbodyElem = dataTable.firstElementChild;
        if (tbodyElem && tbodyElem.firstElementChild && tbodyElem.firstElementChild.tagName==="TR") {
            var firstRowElem = tbodyElem.firstElementChild;
            var cells = firstRowElem.children;
            for (var i=0; i<cells.length; i++) { // >
                var cell = cells[i];
                cell.style.width="1%";
                cell.style.minWidth=null;
            }
        }
    }
    var fittedTables = document.getElementsByClassName("catalog_table");
    for (var i=0; i<fittedTables.length; i++) { // >
        fittedTables[i].classList.remove("fit");
    }
    var filterFields = document.getElementsByClassName("filterField");
    for (var i=0; i<filterFields.length; i++) filterFields[i].classList.add("hidden"); // >
    clearTimeout(timeoutCatalog);
    timeoutCatalog = setTimeout(adjustTableWidths, 10);
}
function adjustTableWidths() {
    var colDataTable = document.getElementById("catalog_column_table");
    var colDataElem = colDataTable;
    var dataTable = false;
    if (colDataElem && colDataElem.firstElementChild && colDataElem.firstElementChild.tagName==="THEAD")
        colDataElem=colDataElem.firstElementChild;
    if (colDataElem && colDataElem.firstElementChild && colDataElem.firstElementChild.tagName==="TR") {
        colDataElem=colDataElem.firstElementChild;
        var colWdt = [];
        if (colDataElem && colDataElem.firstElementChild && colDataElem.firstElementChild.tagName==="TH") {
            var cell = colDataElem.firstElementChild;
            do {
                var offWdt = cell.clientWidth; //offsetWidth
                colWdt.push(offWdt);
                //console.log("COL WDT "+colWdt.length+" = "+offWdt);
                cell = cell.nextElementSibling;
            } while(cell && cell.tagName==="TH");
            dataTable = document.getElementById("catalog_content_table");
            var dataElem = dataTable;
            if (dataElem && dataElem.firstElementChild && dataElem.firstElementChild.tagName==="TBODY")
                dataElem=dataElem.firstElementChild;
            if (dataElem && dataElem.firstElementChild && dataElem.firstElementChild.tagName==="TR") {
                dataElem=dataElem.firstElementChild;
                if (dataElem && dataElem.firstElementChild && dataElem.firstElementChild.tagName==="TD") {
                    cell = dataElem.firstElementChild;
                    var i=0;
                    do {
                        const offWdt = cell.clientWidth; //offsetWidth
                        //console.log("CEL WDT "+(i+1)+" = "+offWdt);
                        let colSpn = +(cell.colSpan?cell.colSpan:1);
                        const celWdt=[...colWdt];
                        for (let x=1; x<colSpn; x++) { // >
                            celWdt[i]+=celWdt[i+x]; // sumar las siguientes celdas a la actual
                            celWdt[i+x]=0; // poner cero a las celdas dentro del span
                        }
                        if (offWdt>celWdt[i]) {
                            let diff=offWdt-celWdt[i];
                            celWdt[i]=offWdt;
                            //if (colSpn>1) console.log("CEL ADJ "+(i+1)+" + SPAN "+colSpn+" = "+celWdt[i])
                            // Asignar diferencia a la primer columna en el span
                            //colWdt[i]+=diff;
                            // Repartir diferencia por igual entre columnas dentro del span
                            const part=Math.ceil(diff/colSpn);
                            for (let x=0; x<colSpn; x++) { // >
                                if (diff>part) {
                                    colWdt[i+x]+=part;
                                    diff-=part;
                                } else if (diff>0) {
                                    colWdt[i+x]+=diff;
                                    diff=0;
                                }
                                //console.log("COL ADJ "+(i+x+1)+" = "+colWdt[i+x]);
                            }
                        }
                        cell.style.width=celWdt[i]+"px";
                        cell.style.minWidth=celWdt[i]+"px";
                        i+=colSpn; // i++;
                        cell = cell.nextElementSibling;
                    } while(cell);
                    for (i=0, cell = colDataElem.firstElementChild; cell && cell.tagName==="TH"; cell = cell.nextElementSibling, i++) {
                        cell.style.width=colWdt[i]+"px";
                        cell.style.minWidth=colWdt[i]+"px";
                    }
                }
            }
            colDataTable.classList.add("fit");
            dataTable.classList.add("fit");
        }
    }
    setTimeout(widthCheck, 10);
}
function widthCheck() {
    var colSectionElem = document.getElementById("catalog_column_section");
    var dataSectionElem = document.getElementById("catalog_content_section");
    var colTableElem = document.getElementById("catalog_column_table");
    var dataTableElem = document.getElementById("catalog_content_table");
    var filterFields = document.getElementsByClassName("filterField");
    for (var i=0; i<filterFields.length; i++) filterFields[i].classList.remove("hidden");
    visualAdjustmentsOnColumnVisibilityChanges();
}
function visualAdjustmentsOnColumnVisibilityChanges() {
    var cellHeaders = document.getElementsByClassName("cell_header");
    var visHideBtns = [];
    for (var i=0; i<cellHeaders.length; i++) {
        var imgBtns = cellHeaders[i].getElementsByTagName("IMG");
        if (imgBtns.length>1) {
            if ((i>0 && cellHeaders[i-1].classList.contains("hidden"))||((i+1)<cellHeaders.length && cellHeaders[i+1].classList.contains("hidden"))) imgBtns[1].classList.remove("disabled2");
            else imgBtns[1].classList.add("disabled2");
        }
        if (imgBtns.length>0) {
            imgBtns[0].classList.remove("disabled2");
            if (!cellHeaders[i].classList.contains("hidden")) visHideBtns.push(imgBtns[0]);
        }
    }
    if (visHideBtns.length==1) visHideBtns[0].classList.add("disabled2");
}
<?php
clog1seq(-1);
clog2end("scripts.catalogo");
