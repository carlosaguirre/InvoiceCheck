<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.reportes");
clog1seq(1);
?>
window.addEventListener("click",unmarkAll);
window.addEventListener("blur",unmarkAll);
var _forma_submitted = false;
function submitAjax() {
  console.log("INI function submitAjax");
  try {
    var postURL = "selectores/reportes.php";
    var formName = "formaReportes";
    var resultDiv = "reporte_resultado";
    overlayWheel();
    var formElem = document.forms[formName];
    var commandElem = document.getElementById("command");
    ekfil(ebyid(resultDiv));
    //document.getElementById(resultDiv).innerHTML = waitingHtml;
    const ovy=ebyid("overlay");
    ovy.xhr=ajaxPost(postURL, formName, resultDiv, false, sentFunc);
    ebyid("overlay").callOnClose=()=>{
        console.log("CLOSING OVERLAY!!! Hopefully only FLYING image...",{parameters:JSON.stringify(ovy.xhr.parameters), respLen:ovy.xhr.responseLength, readyState:ovy.xhr.readyState, status:ovy.xhr.status, statusText:ovy.xhr.statusText});
        if (ovy.xhr.readyState<4) ovy.xhr.abort();
        ovy.xhr=null; delete ovy.xhr;
    };
    ovy.xhr.onabort=function(evt) {
        const xhr=evt.target;
        console.log("ABORT ",{parameters:JSON.stringify(xhr.parameters), respLen:xhr.responseLength, readyState:xhr.readyState, status:xhr.status, statusText:xhr.statusText});
    };
  } catch (err) {
      console.error(err);
  }
  return false;
}
function downloadFile() {
    var empresaElem = document.getElementById("empresa"); // empresa : (resumen, desglose, rfc-de-empresa)
    var fechaIniElem = document.getElementById("fechaIni");   // fecha   : dd/mm/aaaa => aaaa-mm-dd
    var fechaFinElem = document.getElementById("fechaFin");   // fecha   : dd/mm/aaaa => aaaa-mm-dd
    var importeElem = document.getElementById("importe"); // importe : total, subtotal
    var monedaElem = document.getElementById("moneda");  // moneda  : todas, pesos, dolares
    var filename = printTitle();
    var url = "selectores/reportes.php?command=Exportar&empresa=" + empresaElem.value + "&fechaIni=" + fechaIniElem.value + "&fechaFin=" + fechaFinElem.value + "&importe=" + importeElem.value + "&moneda=" + monedaElem.value + "&filename=" + filename;
    window.open(url, "_blank");
}
function sentFunc(xmlHttpPost) {
    console.log("INI function sentFunc",{parameters:JSON.stringify(xmlHttpPost.parameters), respLen:xmlHttpPost.responseLength, readyState:xmlHttpPost.readyState, status:xmlHttpPost.status, statusText:xmlHttpPost.statusText});
    if (xmlHttpPost.readyState>=4 && xmlHttpPost.status>=200) overlayClose();
    var contentDiv = "reporte_contenido";
    //var resultDiv = "reporte_resultado";
    var contentElem = document.getElementById(contentDiv);
    if (contentElem) contentElem.classList.remove("hidden");
    // var respText = xmlHttpPost.responseText;
    // console.log("INI sentFunc:\n - - - - - - - - - -\n"+respText+"\n - - - - - - - - - -\n");
}
function setCommand(commandValue) {
    var commandElem = document.getElementById("command");
    if (commandElem) commandElem.value = commandValue;
}
function mark(elem) {
    elem.classList.add("mark");
}
function unmarkAll() {
    var markElems = document.getElementsByClassName("mark");
    for(var i=0; i<markElems.length; i++) markElems[i].classList.remove("mark");
}
function printElem(elemId) {
    var mywindow = window.open("", "PRINT", "height=400,width=600");
    var title = printTitle().split("|");
    
    mywindow.document.write("<html><head><title>"+title[0]+" "+title[1]+"</title>");
    mywindow.document.write("</head><body >");
    mywindow.document.write("<h1 style=\"text-align:center;\">"+title[0]+"<br>"+title[1]+"</h1>");
    mywindow.document.write(document.getElementById(elemId).innerHTML);
    mywindow.document.write("</body></html>");

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
}
function printTitle() {
    var empElem = document.getElementById("empresa"); // empresa : (resumen, desglose, rfc-de-empresa)
    var iniElem = document.getElementById("fechaIni");   // fecha   : dd/mm/aaaa => aaaa-mm-dd
    var finElem = document.getElementById("fechaFin");   // fecha   : dd/mm/aaaa => aaaa-mm-dd
    var impElem = document.getElementById("importe"); // importe : total, subtotal
    var monElem = document.getElementById("moneda");  // moneda  : todas, pesos, dolares
    var fechaVal = iniElem.value;
    if (fechaVal !== finElem.value) fechaVal += "-"+finElem.value;
    var title = "";
    var impValue = impElem.value;
    impValue = impValue[0].toUpperCase()+impValue.slice(1);
    var monValue = monElem.value;
    if (monValue==="todas") monValue="";
    else if (monValue==="pesos") monValue=" MXN";
    else monValue=" USD";
    if (empElem.value==="desglose") title = "Reporte Desglosado "+impValue+monValue+" por Facturas|"+fechaVal;
    else if (empElem.value==="resumen") title = "Resumen "+impValue+monValue+" por Empresa|"+fechaVal;
    else title+="Reporte "+impValue+monValue+" "+empElem.options[empElem.selectedIndex].text+"|"+fechaVal;
    return title;
}
<?php
clog1seq(-1);
clog2end("scripts.reportes");
