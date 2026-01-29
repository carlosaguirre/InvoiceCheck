<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.catalogo");
clog1seq(1);
?>
var browser = "<?= getBrowser() ?>";
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
            for(var i=0; i<forma.elements.length; i++) {
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
    for(var i=0; i<fxBtns.length; i++) {
        if (hasModifiedData && fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.remove("hidden");
        else if (!hasModifiedData && !fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.add("hidden");
    }
}
function doRollBack() {
    overlayConfirmation("<p>Confirme que desea eliminar los cambios y restaurar los valores originales.</p>", "Restaurar Originales", confirmRollBack);
}
function confirmRollBack() {
    var forma = document.getElementById("formaCatalogo");
    if (forma.elements) {
        for(var i=0; i<forma.elements.length; i++) {
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
    for(var i=0; i<fxBtns.length; i++) {
        if (!fxBtns[i].classList.contains("hidden"))
            fxBtns[i].classList.add("hidden");
    }
}
function doCommit() {
    var forma = document.getElementById("formaCatalogo");
    var txt = "<p>Confirme que desea guardar los cambios:<br>";
    if (forma.elements) {
        for(var i=0; i<forma.elements.length; i++) {
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
function successSentChanges() {
    var forma = document.getElementById("formaCatalogo");
    if (forma.elements) {
        for(var i=0; i<forma.elements.length; i++) {
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
    for(var i=0; i<fxBtns.length; i++) {
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
function doPageChange(nombre,accion) {
    conlog("doPageChange("+nombre+", "+accion+")");
    var pagElem = document.getElementById("currPgVal_"+nombre);
    var lpgElem = document.getElementById("lastPgVal_"+nombre);
    if (pagElem && lpgElem) {
        var catalogo_admin = document.getElementById("catalogo_admin");
        if(catalogo_admin) catalogo_admin.value="page";

        var pagVal = pagElem.value;
        var lpgVal = lpgElem.value;
        var pagina = -1;
        var ultima = -1;
        if (/^\d+$/.test(pagVal)) pagina = +pagVal;
        if (/^\d+$/.test(lpgVal)) ultima = +lpgVal;
        switch(accion) {
            case "frst": pagina=1; break;
            case "prev": pagina--; break;
            case "next": pagina++; break;
            case "last": pagina=ultima; break;
        }
        if (pagina >=0) submitChangePage(nombre, pagina);
    } else conlog("No encontro elementos");
}

function sortPageBy(nombre,colName, hCellElem) {
    conlog("sortPageBy("+nombre+", "+colName+")");

    var catalogo_admin = document.getElementById("catalogo_admin");
    if(catalogo_admin) catalogo_admin.value="sort";

    var lpgElem = document.getElementById("lastPgVal_"+nombre);
    var sortColElem = document.getElementById("sortColVal_"+nombre);
    var sortModElem = document.getElementById("sortModVal_"+nombre);

    var catalogo_admin = document.getElementById("catalogo_admin");
    if(catalogo_admin) catalogo_admin.value="page";

    if (sortColElem && sortModElem && sortColElem.value==colName) {
        if (sortModElem.value=="asc") {
            sortModElem.value="desc";
            conlog("sort "+sortColElem.value+" by "+sortModElem.value);
            hCellElem.classList.remove("asc");
            hCellElem.classList.add("desc");
        } else {
            // sortColElem.value="";
            // sortModElem.value="";
            sortModElem.parentNode.removeChild(sortModElem);
            sortColElem.parentNode.removeChild(sortColElem);
            var forma = document.getElementById("formaCatalogo");
            var cattbsortcol = document.getElementById("cattbsortcol");
            if (cattbsortcol) forma.removeChild(cattbsortcol);
            var cattbsortmod = document.getElementById("cattbsortmod");
            if (cattbsortmod) forma.removeChild(cattbsortmod);
            conlog("Removing sort data");
            hCellElem.classList.remove("desc");
        }
    } else {
        sortColElem = getOrMakeElementAt(lpgElem.parentNode, "INPUT", {type:"hidden", name:"sortColVal_"+nombre, id:"sortColVal_"+nombre});
        sortModElem = getOrMakeElementAt(lpgElem.parentNode, "INPUT", {type:"hidden", name:"sortModVal_"+nombre, id:"sortModVal_"+nombre});
        sortColElem.value=colName;
        sortModElem.value="asc";
        conlog("sort "+sortColElem.value+" by "+sortModElem.value);
        for(var hCell = hCellElem.parentNode.firstElementChild; hCell!=null; hCell = hCell.nextElementSibling) {
            if (hCell!=hCellElem) {
                if (hCell.classList.contains("asc")) {
                    hCell.classList.remove("asc");
                    break;
                } else if (hCell.classList.contains("desc")) {
                    hCell.classList.remove("desc");
                    break;
                }
            }
        }
        hCellElem.classList.add("asc");
    }

    submitChangePage(nombre, "1");
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
    
    submitAjax(nombre, function() {successChangedPage(nombre);});
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

    if ((+pagina)<=1) btnBack.classList.add('invisible');
    else btnBack.classList.remove('invisible');

    if ((+pagina)<=2) btnFrst.classList.add('invisible');
    else btnFrst.classList.remove('invisible');

    if ((+pagina)>=(+ultima)) btnFwrd.classList.add('invisible');
    else btnFwrd.classList.remove('invisible');

    if ((+pagina+1)>=(+ultima)) btnLast.classList.add('invisible');
    else btnLast.classList.remove('invisible');

    var forma = document.getElementById("formaCatalogo");
    if (forma.elements) {
        for(var i=0; i<forma.elements.length; i++) {
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
function submitCallbackSuccessFunc() {
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
function scrollToSection(elemId) {
    var milog = "INI scrollToSection("+elemId+")";
    var elem = document.getElementById(elemId);
    if (elem) {
        var topPos = elem.offsetTop;
        milog += " offTop:"+topPos;
        var scrollDiv = document.getElementById('catalog_scroll');
        if (scrollDiv) {
            var scrollTopTo = topPos - 1 - scrollDiv.offsetTop;
            scrollDiv.scrollTop = scrollTopTo;
            milog += " scrTop:"+scrollTopTo;
        } else milog += " NO ScrollDiv";
    } else milog += " NO Elem";
    conlog(milog);
}
<?php
clog1seq(-1);
clog2end("scripts.catalogo");
