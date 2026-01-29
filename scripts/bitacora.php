<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.bitacora");
clog1seq(1);
?>
var browser = "<?= getBrowser() ?>";
function buscaUsuario(nombre) {
    var getURL = 'consultas/Usuarios.php?llave='+nombre;
    // ajaxRequest...  ver scripts\general.js, funcion fillReporte() como ejemplo
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
function checkEnter(evt) {
    return checkKeyUp(evt, '13');
}
function checkEscape(evt) {
    return checkKeyUp(evt, '27');
}
function checkKeyUp(evt, keyCode) {
    if (evt && evt.type=="keyup") {
        if (evt.keyCode == keyCode) return true;
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
function submitCallbackSuccessFunc(xmlHttpPost) {
    conlog("Success Callback!");
}
function submitAjax(nombre, callbackfunc) {
    if (!callbackfunc) callbackfunc=submitCallbackSuccessFunc;
    
    conlog("submitAjax: "+nombre);
    var postURL = 'consultas/'+nombre+'.php';
    var formName = 'formaCatalogo';
    var resultDiv = 'tbody_'+nombre;
    var waitingHtml = '';
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
<?php
clog1seq(-1);
clog2end("scripts.bitacora");
