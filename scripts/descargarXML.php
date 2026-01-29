<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!validaPerfil("Administrador")) {
    exit;
}
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.descargarXML");
clog1seq(1);
?>
var forma = false;
function miforma() {
    if (!forma) forma = document.getElementById("formaDwn");
    return forma;
}
function descargaZips() {
    descargas("zip");
}
function descargaTars() {
    descargas("tar");
}
function unzip() {
    solicitaAjax("unzip", "listaSAT");
}
function untar() {
    solicitaAjax("untar", "listaIS");
}
function organizaXMLs() {
    solicitaAjax("ordenaSAT", "listaSAT");
}
function organizaEmpresas() {
    solicitaAjax("ordenaIS", "listaIS");
}
function actualizaSAT() {
    actualiza("SAT");
}
function actualizaIS() {
    actualiza("IS");
}
function actualizaArch() {
    actualiza("Arch");
}
function actualizaDif() {
    actualiza("Dif");
}
function limpiaSAT() {
    document.getElementById("listaSAT").innerHTML="";
}
function limpiaIS() {
    document.getElementById("listaIS").innerHTML="";
}
function limpiaArch() {
    document.getElementById("listaArch").innerHTML="";
}
function limpiaDif() {
    document.getElementById("listaDif").innerHTML="";
}
function confirmaBorrarArchivo(ruta, jkey) {
    overlayConfirmation("<p>Seguro que deseas borrar el archivo "+ruta+" ("+jkey+")</p>", "Confirmar", function() {  borraArchivo(ruta, jkey); })
}
function borraArchivo(filepath, jkey) {
    creaValorOculto("archivoABorrar",filepath);
    creaValorOculto("nombreZona",jkey);
    solicitaAjax("borraArchivo", "lista"+jkey);
}

function actualiza(jkey) {
    var empElem = document.getElementById("empresa"+jkey);
    var mesElem = document.getElementById("fechaMes"+jkey);
    var yrElem = document.getElementById("fechaAnio"+jkey);
    creaValorOculto("empresa",empElem.value);
    creaValorOculto("mes",mesElem.value);
    creaValorOculto("anio",yrElem.value);
    solicitaAjax("actualiza"+jkey, "lista"+jkey);
}
function descargas(tipo) {
    var accion = "descarga"+tipo[0].toUpperCase()+tipo.slice(1).toLowerCase();
    creaValorOculto("accion",accion);
    var arch = document.getElementById("dwnfiles");
    arch.filetype=tipo.toLowerCase();
    arch.click();
}
function solicitaAjax(accion, targetDiv) {
    creaValorOculto("accion",accion);
    document.getElementById(targetDiv).innerHTML="";
    submitAjax(targetDiv);
}


function creaValorOculto(nombre, valor) {
    var accion = document.getElementById(nombre);
    if (!accion) {
        accion = document.createElement("INPUT");
        accion.type="hidden";
        accion.name=nombre;
        accion.id=nombre;
        miforma().appendChild(accion);
    }
    if (accion.type=="hidden") accion.value=valor;
}
function sendFiles(evt) {
    conlog("sendFiles()");
    if(!evt) evt = window.event;
    var targetDiv = "listaSAT";
    if (evt && evt.target) {
        var tgt = evt.target;
        if (tgt.filetype && tgt.filetype=="tar")
            targetDiv = "listaIS";
        var tgtfiles = tgt.files;
        var numfiles = 0;
        if (tgtfiles) {
            numfiles = tgtfiles.length;
            if(numfiles==0 || (numfiles==1 && tgtfiles[0].type.length==0)) {
                conlog("CANCELLED!");
                return;
            }
            for(var i=0; i<numfiles; i++) {
                conlog("SIZE["+(i+1)+"]="+tgtfiles[i].size);
                if (tgtfiles[i].size>2000000) {
                    conlog("EXCEEDED!");
                    document.getElementById(targetDiv).innerHTML='A file is too big';
                    return;
                }
            }
            if(numfiles>20) {
                document.getElementById(targetDiv).innerHTML='Only allowed up to 20 files';
                conlog("TOO MANY!");
                return;
            }
        }
        conlog("num files: '"+numfiles+"'");
    }
    document.getElementById(targetDiv).innerHTML="";
    submitAjax(targetDiv);
}
function submitCallbackSuccessFunc(xmlHttpPost) {
    var result = xmlHttpPost.responseText;
    conlog("Success Callback! "+(result?(result.length<=50?result:result.substr(0,47)+"..."):-1));
    var files = document.getElementById("dwnfiles");
    if (files && files.files) files.value="";
}
function submitAjax(resultDivId, callbackfunc) {
//    conlog("submitAjax("+resultDivId+")");
    if (!callbackfunc) callbackfunc=submitCallbackSuccessFunc;
    var postURL = 'consultas/Archivos.php';
    var waitingHtml = false;
    var resultDiv = document.getElementById(resultDivId);
    if (resultDiv) resultDiv.innerHTML = "<img src=\"imagenes/ledred.gif\"/>";
    ajaxPost(postURL, miforma().id, resultDivId, waitingHtml, callbackfunc);
    return false;
}
<?php
clog1seq(-1);
clog2end("scripts.descargarXML");
