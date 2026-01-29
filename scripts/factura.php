<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.factura");
clog1seq(1);
?>
var browser = "<?= getBrowser() ?>";

function ajaxRequest() {
    var activexmodes=["Msxml2.XMLHTTP", "Microsoft.XMLHTTP"] //activeX versions to check for in IE
    if (window.ActiveXObject) { //Test for support for ActiveXObject in IE first (as XMLHttpRequest in IE7 is broken)
        for (var i=0; i<activexmodes.length; i++) {
            try {
                return new ActiveXObject(activexmodes[i])
            } catch(e) {
            //suppress error
            }
        }
    } else if (window.XMLHttpRequest) // if Mozilla, Safari etc
        return new XMLHttpRequest();
    else
        return false;
}
// toDo: verificar que esta funcion se incluya con los permisos adecuados.
function checkChange() {
    console.log("INI function checkChange");
    changeMessage = "";
    var xf = document.getElementById("xmlfiles");
    if (xf.files.length>0) {
        var fileData = xf.files[0];
        var name = fileData.name;
        var size = +fileData.size;
        var type = fileData.type;
        var prfx = "";
        var sufx = "";
        if (type!=="text/xml") {
            changeMessage += "<p>El archivo '"+name+"' no tiene formato XML</p>";
            prfx = "ERROR ";
            sufx += " | type";
        }
        if (size>2097000) {
            changeMessage += "<p>El archivo '"+name+"' excede el tamaño máximo permitido de 2MB</p>";
            prfx = "ERROR ";
            sufx += " | size";
        }
        console.log(prfx+"File "+name+" "+type+" "+size+"bytes"+sufx);
    } else console.log("No files");
    if (changeMessage.length>0) overlayMessage(changeMessage,"Error");
    else {
        console.log("No message");
        xf.classList.remove("highlight");
        var sx = document.getElementById("submitxml");
        if (sx) {
            sx.classList.add("highlight");
            sx.focus();
        }
    }
}
function isSubmit() {
    
}

function selectedPDF() {
    console.log("FUNC selectedPDF()");
    document.getElementById("appendpdffile").classList.remove("highlight");
    document.getElementById("AnexarPDF").classList.add("highlight");
}
function agregaPDF(event) {
    console.log("FUNC agregaPDF()");
    if (!event && window.event) event=window.event;
    if (!event) return false;
    event.preventDefault();
    var oFormElement = event.target;

    var postURL = "consultas/Facturas.php";
    var resultDiv = document.getElementById("resultSubmit");
    resultDiv.innerHTML = "";
    
    var xmlHttpPost = ajaxRequest();
    xmlHttpPost.open("POST", postURL, true);
    var fileSelect = document.getElementById("appendpdffile");
    var fileItem = false;
    if (fileSelect.files && fileSelect.files.length) {
        var fileList = fileSelect.files;
        fileItem = fileList[0];
        if (fileItem.type) {
            if (!fileItem.type.match('.*pdf')) {
console.log("Formato de archivo inválido : "+fileItem.type+". "+fileItem.name);
                alert("El formato del archivo no es válido: "+fileItem.type);
                fileItem = false;
            }
        } else {
console.log("filename: "+fileItem.name);
        }
    }
    if (!fileItem) {
console.log("Sin archivo");
        return false;
    }
    var fdata = new FormData();
    var oSubElements = oFormElement.elements;
    for(var i=0; i<oSubElements.length; i++) {
        if (oSubElements[i].value) {
            if(fileItem && oSubElements[i].name==="appendpdffile") {
                fdata.append(oSubElements[i].name, fileItem, fileItem.name);
                console.log("FORM FILE ELEM "+oSubElements[i].tagName+") "+oSubElements[i].name);
            } else {
                fdata.append(oSubElements[i].name, oSubElements[i].value);
                console.log("FORM ELEMENT "+oSubElements[i].tagName+") "+oSubElements[i].name+" = "+oSubElements[i].value);
            }
        }
    }
    xmlHttpPost.onreadystatechange = function() {
        console.log("TAG state: "+xmlHttpPost.readyState+", status: "+xmlHttpPost.status);
        if (xmlHttpPost.readyState==4 && xmlHttpPost.status==200) {
            var respTxt = xmlHttpPost.responseText;
            if (respTxt.slice(0,5)==="Error") {
                console.log(respTxt);
                submitCallbackSuccessFunc("");
            } else {
                resultDiv.innerHTML = respTxt;
                submitCallbackSuccessFunc(respTxt);
            }
        }
    }
    xmlHttpPost.send(fdata);
    return false;
}
function submitCallbackSuccessFunc(resultText) {
    if (resultText.length>0) {
        window.opener.buscaFacturas();
        window.opener.submitAjax('Buscar');
        if (document.location) document.location.replace(resultText);
        else                   window.location.replace(resultText);
    } else {
        console.log("FUNC submitCallbackSuccessFunc");
        document.getElementById("appendpdffile").value="";
        document.getElementById("appendpdffile").classList.add("highlight");
        document.getElementById("AnexarPDF").classList.remove("highlight");
        alert("No pudo descargarse el archivo");
    }
}

<?php
clog1seq(-1);
clog2end("scripts.factura");
