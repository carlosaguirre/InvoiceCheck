<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.correos");
clog1seq(1);
?>
var browser = "<?= getBrowser() ?>";
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
    if (colsElem && colsElem.value) waitingHtml = '<tr><td colspan=\''+colsElem.value+'\' class=\'centered\'><img src=\'<?=$waitImgName?>\' width=\'360\' height=\'360\'></td></tr>';
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
clog2end("scripts.correos");
