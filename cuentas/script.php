<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("cuentas.script");
?>
console.log("LOADED cuentas/script.php");
function resetForm() {
    var formElem = document.getElementById("forma_admon_prv");
    resetFormElement(formElem);
    fillDataCheck();
    return false;
}
function resetFormElement(element) { //, prefix) {
//    if (!prefix) prefix="";
//    conlog(prefix+"INI resetFormElement "+element);
    var tag = element.tagName?element.tagName.toLowerCase():"nul";
    var typ = element.type?element.type.toLowerCase():"nul";
    var nam = element.id?element.id.toLowerCase():"nul";
    if (tag==="input" && (typ==="text" || typ==="hidden" || typ==="file")) {
        element.value="";
//        conlog(prefix+" "+tag+" "+typ+" "+nam+" = CLEARED");
    } else if (element.children && element.children.length>0) {
        for (var i=0; i<element.children.length; i++) resetFormElement(element.children[i]); //, prefix+"  ");
    }
}
function fillDataCheck() {
    conlog("INI fillDataCheck");
    var blocks = ["proveedor"];
    for (var i=0; i<blocks.length; i++) {
        var block = blocks[i];
        var idVal = getValueTxt(block+"_id");
        var elemDelete = document.getElementById(block+"_delete");
        if (idVal.length==0 || isNaN(idVal) || parseInt(idVal)<=0) {
            if (idVal.length>0) fillValue(block+"_id", "");
            if(elemDelete) elemDelete.style.display="none";
        } else {
            if(elemDelete) elemDelete.style.display="inline";
        }
        var elemRecibo = document.getElementById(block+"_recibo");
        var fileIcon = document.getElementById("abrirArchivo");
        if (elemRecibo && elemRecibo.value && elemRecibo.value.length) {
            if (!fileIcon) {
conlog(elemRecibo.tagName+". new");
                fileIcon = document.createElement("IMG");
                fileIcon.id = "abrirArchivo";
                fileIcon.src = "imagenes/icons/pdf32b.png";
                fileIcon.style.width="20px";
                fileIcon.style.height="20px";
                fileIcon.classList.add("vAlignCenter");
                fileIcon.classList.add("aslink");
                fileIcon.onclick=abrirArchivo;
                elemRecibo.parentNode.classList.add("nowrap");
                elemRecibo.parentNode.insertBefore(fileIcon,elemRecibo.parentNode.firstChild);
            } else if (fileIcon.classList.contains("hidden")) {
conlog(elemRecibo.tagName+". show");
                fileIcon.classList.remove("hidden");
                
            }
        } else if (fileIcon) {
conlog("clean proveedor_nombre_archivo_recibido");
            //fileIcon.parentNode.removeChild(fileIcon);
            fileIcon.classList.add("hidden");
        }
        var fileElem = document.getElementById("proveedor_nombre_archivo_recibo");
        if (fileElem) {
            fileElem.value="";
conlog("clean proveedor_nombre_archivo_recibido");
        }
    }
}
function abrirArchivo() {
    conlog("INI abrirArchivo");
    var reciboElem = document.getElementById("proveedor_recibo");
    if (reciboElem) {
        var url = "cuentas/docs/"+reciboElem.value;
conlog(url);
        var win = window.open(url, "_blank");
        win.focus();
    }
}
function borrarProveedor() {
    conlog("INI borrarProveedor");
    if (getValueTxt("proveedor_id").length==0)
        overlayMessage("<p>Debe seleccionar un proveedor para poder borrarlo</p>", "Error");
    else
        overlayConfirmation("<p>Confirme para borrar el proveedor "+getValueTxt("proveedor_field")+"</p>", "CONFIRMACI&Oacute;N", borrarConfirmado);
}
function guardarProveedor() {
    conlog("INI guardarProveedor");
    var formData = new FormData();
    formData.append("command","Guardar");
    agregaCampos(formData);
    var url = "cuentas/cuentas.php";
    enviarPOST(url, formData, "GUARDADO");
}
function borrarConfirmado() {
    conlog("INI borrarConfirmado");
    var formData = new FormData();
    formData.append("command","Borrar");
    agregaCampos(formData);
    var url = "cuentas/cuentas.php";
    enviarPOST(url, formData, "BORRADO");
}
function procesaCSV(elem) {
    conlog("INI procesaCSV");
    var formData = new FormData();
    formData.append("command","Procesar");
    var csv = document.getElementById("archivo_csv");
    var fil = csv.files[0];
    if (fil) {
        conlog("csv: "+fil.name+", "+fil.size);
        formData.append("archivo_csv", fil);
        var url = "cuentas/cuentas.php";
        enviarPOST(url, formData, "contenidoCSV");
        if (elem) {
            var parent = elem.parentNode;
            var cell = document.createElement("TD");
            //cell.style.width="20px";
            var img = document.createElement("IMG");
            img.src="imagenes/icons/frontArrow.png";
            img.width="10";
            img.height="10";
            img.style="cursor:pointer";
            img.onclick=procesaCSV;
            cell.appendChild(img);
            parent.parentNode.appendChild(cell);
        }
    } else {
        if (!elem) elem = document.getElementById("archivo_csv");
        if (elem) {
// conlog("archivo vacío. borrando elementos previos");
            var imgCell = elem.parentNode.nextElementSibling;
            if (imgCell && imgCell.firstChild)
                imgCell.parentNode.removeChild(imgCell);
            var contenido = document.getElementById("contenidoCSV");
// conlog(":");
            while(contenido.firstChild) {
// conlog(".");
                contenido.removeChild(contenido.firstChild);
            }
        }
    }
}

function overlayResultado(titulo, responseText) {
    conlog("INI overlayResultado");
    var resultado = "";
    var idx = responseText.indexOf("<!-- RESULTADO:");
    if (idx>=0) {
        var idx2 = responseText.indexOf(" -->",idx);
        resultado = responseText.slice(idx+15,idx2);
    }
    if (titulo==="contenidoCSV") {
        var elem = document.getElementById(titulo);
        if (elem) elem.innerHTML = responseText;
        if (resultado==="ERROR") {
            var mensajeError = "Ocurrió un error al cargar el archivo CSV";
            idx = responseText.indexOf("<!-- MENSAJEERROR:");
            if (idx>=0) {
                idx2 = responseText.indexOf(" -->",idx);
                mensajeError = responseText.slice(idx+18,idx2);
            }
            overlayMessage("<p>"+mensajeError+"</p>", resultado);
        } else {
            overlayMessage("<p>Todos los códigos de proveedor, sus cuentas y razón social son correctos</p>", resultado);
        }
    } else {
        if (resultado==="EXITO") {
            var reciboElem = document.getElementById("proveedor_recibo");
            var rfcElem = document.getElementById("proveedor_rfc");
            if (reciboElem && rfcElem) {
                reciboElem.value="prvCta"+rfcElem.value+".pdf";
                fillDataCheck();
            }
        } else titulo=resultado;
        overlayMessage(responseText, titulo);
    }
}
function enviarPOST(url, formData, titulo) {
    var xmlHttpPost = ajaxRequest();
    xmlHttpPost.open("POST", url, true);
    xmlHttpPost.onreadystatechange = function() {
        funclog("TAG", "state: "+xmlHttpPost.readyState+", status: "+xmlHttpPost.status);
        if (xmlHttpPost.readyState==4 && xmlHttpPost.status==200) {
            overlayResultado(titulo, xmlHttpPost.responseText);
        }
    }
    xmlHttpPost.send(formData);
}
function agregaCampos(formData) {
    var ids = ["proveedor_id", "proveedor_field", "proveedor_code", "proveedor_rfc", "proveedor_zona", "proveedor_cuenta", "proveedor_nombre_archivo_recibo"];
    for(var i=0; i<ids.length; i++) appendToFormData(formData, ids[i]);
}
function appendToFormData(fd, id) {
    var elem = document.getElementById(id);
    if (elem) {
        if (elem.type==="hidden"||elem.type==="text") {
            fd.append(elem.id, elem.value);
        } else if (elem.type==="file") {
            fd.append(elem.id, elem.files[0]);
        }
    }
}

<?php
require_once "configuracion/finalizacion.php";
clog2end("cuentas.script");
