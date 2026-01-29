<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$esDesarrollo = getUser()->nombre==="admin";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.altafactura");
clog1seq(1);

$browser = getBrowser();
if ($browser==="Chrome") $maxXML=10;
else $maxXML=3;
?>
doShowLogs=true;
var _forma_submitted = false;
var collapseTimer;
var collapseBlockFlag=false;
var submitBlockade=false;
var postQueue = [];
var timeOutPostQueue=0;
var dataImgCheck = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAMAAAD04JH5AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3ODkwNzY1QzM1QUVFMDExOUI0MDg3MzJEODFDNjBGNyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5RkQzQUQ5NEI2RkQxMUUwQThEQkRGODVEMjUyRDgzMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5RkQzQUQ5M0I2RkQxMUUwQThEQkRGODVEMjUyRDgzMyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo2RjgzN0JERjVGQjNFMDExQTZCQ0JDNUUxM0I0M0YxNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3ODkwNzY1QzM1QUVFMDExOUI0MDg3MzJEODFDNjBGNyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PpeaIb0AAADVUExURUK0Sf///0K0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0SUK0STDxEMgAAABGdFJOUwAAAwYJDA8VGBseISQnKi0zNjk8P0JFS05RVFpdYGNpb3J4e36Bh4qQk5aZoqWosbS3vcDDyczP0tXY297h5Ort8PP2+fyCn80zAAADTUlEQVR42u2bYVsSQRSFJ9igVRQpKQoNNUEhEVPILRJElvP/f1IftPKxZXbu3Ttzv3A+y57zwL5zd8+M5pWyzCbAJsBGgdS5nP4YttXsd8YAAHyLdfzjOZ40q2n4RwmgmmAEqCboAaoJ2oBqgkYK1QT/ANBJ8BwAlQQjQDVBD1BN0AZUE2QAEDRBJgABE6wBIFiC0ghQTdCDg2bb3vwP4KRrX/5vU7cA6Pjx35o7+iPx4v86cfXHSguAv1ID4El3egA8qq8IAAAstxQBAICPmgAAONEFAENdADAu6wIwreoCsNjVBWD1XhmAjjIA9hWw1j7Y9QvAVclypWYCANNPHgFIKpYrHf/5q1NvAMxtj4InrPuEBEDacPPH6oMfANqO/sCi7gOArrM/MIvlAbgk+APfI2kAJhHFHxiVZAGYVWn+wLkoAA91qn8ujCQAVi26v/VDVACOGf45MJIAGLD8rTCSALgu8/yB20gCgGmF678WRhIA8xrff83zAwmAtFnEPxNGGgCHa/07XIJJAPTW+u+mzDWMBIBlRb9wXsVjPgC3lgnkfiMnz6+yTQHAOtV532OFAsByz7aYp5yllAaAvQQYc15nzgRLgM+Uaz3BeEj5zEXeA+UN5WrLujHmHeVnu8ktAapT0G5oEgAuJUCNVCslMQUAtxJgn/KVYkG5Z5rFN5cKyfnl7sSPP6EG/erD/6rkHqB8Le9vLQH+E2lxd3sEI9bA8UzW31oCZGpvKRqAcWSntRL073KKto6cP7OGPpPyn0S8ALTnDF4JkLPhPJHwd62Wis9mzvt8nnYWhQMcFeucm2lB/4HPgx/FSoAgs/lnxRTXsMAEEjkTwZ/N6b7M5gd7Nh9Kbb/Ec5Z/T24DqMGZzSPJLSjGbL6NJAPQZ/NM+shun+ZvLwECzOaWERdpNsufBDDGVH+JlQBM1V1n803ZTwDX2ezhJABpNi92jD915UoAb7O549U/fzafG8/Kmc2jku8A9hIrqRj/sszm+ZYJobWzedkwYXQkVwIwNcj0Pw3mnz2bhyagMmbzJAoZwLx5WWLdVU1YvZjNRUoAidm8apnwOpArAZjqyZUA3AXp4fEd9EtJKYDZ7o/vJ4O62UhQm/873gTQDvAbH4UlvgihgyEAAAAASUVORK5CYII=";
var dataImgCross = "data:image/gif;base64,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";
var changeMessage=false;
additionalResizeScript=ajustaTablasConcepto;
function fixInvoiceDetailValue(text, type, elem) {
    switch(type) {
        case "xs:string":
            if (text.length>77) return text.slice(0,36)+" ... "+text.slice(-36);
            return text;
        case "tdCFDI:t_Importe":
            return "$"+parseInt(text).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        case "tdCFDI:t_FechaH":
            var dt = new Date(text);
            var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return dt.toLocaleDateString('es-MX',options)
        case "catCFDI:c_CodigoPostal":
            postQueue.push({"catalogo":"CodigoPostal", "llave":"codigo", "codigo":text, "solicita":"estado,municipio,localidad", "_parameter_element":elem});
            break;
        case "catCFDI:c_TipoDeComprobante":
            if (text.length>1)
                return text.charAt(0).toUpperCase()+text.slice(1);
            else {
                postQueue.push({"catalogo":"TipoDeComprobante", "llave":"codigo", "codigo":text, "solicita":"descripcion", "_parameter_element":elem});
                elem.title=text;
            }
            break;
        case "catCFDI:c_ClaveUnidad":
            postQueue.push({"catalogo":"ClaveUnidad", "llave":"codigo", "codigo":text, "solicita":"nombre,descripcion", "_parameter_element":elem});
            elem.title=text;
            break;
        case "catCFDI:c_ClaveProdServ":
        case "catCFDI:c_FormaPago":
        case "catCFDI:c_Impuesto":
        case "catCFDI:c_MetodoPago":
        case "catCFDI:c_Moneda":
        case "catCFDI:c_Pais":
        case "catCFDI:c_RegimenFiscal":
        case "catCFDI:c_TipoRelacion":
        case "catCFDI:c_UsoCFDI":
            postQueue.push({"catalogo":type.slice(10), "llave":"codigo", "codigo":text, "solicita":"descripcion", "_parameter_element":elem});
            elem.title=text;
            break;
        case "catCFDI:c_TasaOCuota":
            if (!isNaN(text)) {
                var tasaOCuotaNum = parseFloat(text);
                if (tasaOCuotaNum<1 && tasaOCuotaNum>0) tasaOCuotaNum *= 100;
                return tasaOCuotaNum.toFixed(2)+"%";
            }
            return "{"+text+"}";
            break;
    }
    clearTimeout(timeOutPostQueue);
    if (postQueue.length>0) timeOutPostQueue = setTimeout(function() { postService("consultas/CatalogoSAT.php", postQueue.shift(), retFunc); }, 10);
    return text;
}
function retFunc(responseText, parameters, state, status) {
    if (state!=4||status!=200) return;
    if (responseText.length>0 && "_parameter_element" in parameters) {
        var node = parameters["_parameter_element"];
        var parentNode = node.parentNode;
        switch (parameters["catalogo"]) {
            case "CodigoPostal":
                var cpArr = responseText.split("|");
                var cEdo = cpArr[0];
                var cMun = cpArr[1];
                var cLoc = cpArr[2];
                var edoNode = document.createTextNode(cEdo);
                var munNode = document.createTextNode(cMun);
                var locNode = document.createTextNode(cLoc);
                node.appendChild(document.createTextNode(", "));
                node.appendChild(munNode);
                node.appendChild(document.createTextNode(", "));
                node.appendChild(locNode);
                node.appendChild(document.createTextNode(", "));
                node.appendChild(edoNode);
                postQueue.push(
                    {   "catalogo":"Municipio", 
                        "llave":"codigo",
                        "codigo":cMun, 
                        "solicita":"descripcion", 
                        "extraWhere":"codigoEstado='"+cEdo+"'", 
                        "_parameter_element":munNode, 
                        "_parameter_origin":"CodigoPostal", 
                        "_parameter_edoCode":cEdo, 
                        "_parameter_edoNode":edoNode, 
                        "_parameter_locCode":cLoc, 
                        "_parameter_locNode":locNode
                    });
                break;
            case "Municipio":
                node.nodeValue = responseText;
                if (parameters["_parameter_origin"]==="CodigoPostal") {
                    postQueue.push(
                        {   "catalogo":"Localidad", 
                            "llave":"codigo",
                            "codigo":parameters["_parameter_locCode"], 
                            "solicita":"descripcion", 
                            "extraWhere":"codigoEstado='"+parameters["_parameter_edoCode"]+"'", 
                            "_parameter_element":parameters["_parameter_locNode"], 
                            "_parameter_origin":"CodigoPostal", 
                            "_parameter_edoCode":parameters["_parameter_edoCode"], 
                            "_parameter_edoNode":parameters["_parameter_edoNode"]
                        });
                }
                break;
            case "Localidad":
                node.nodeValue = responseText;
                var lCommaNode = node.previousSibling;
                var lMunNode = lCommaNode.previousSibling;
                if (node.nodeValue===lMunNode.nodeValue) {
                    parentNode.removeChild(lCommaNode);
                    parentNode.removeChild(node);
                }
                if (parameters["_parameter_origin"]==="CodigoPostal") {
                    postQueue.push(
                        {   "catalogo":"Estado", 
                            "llave":"codigo",
                            "codigo":parameters["_parameter_edoCode"], 
                            "solicita":"descripcion,codigoPais", 
                            "_parameter_element":parameters["_parameter_edoNode"]
                        });
                }
                break;
            case "Estado":
                var edoArr = responseText.split("|");
                var dEdo = edoArr[0];
                var cPais = edoArr[1];
                var paisNode = document.createTextNode(cPais);
                node.nodeValue = dEdo;
                var eCommaNode = node.previousSibling;
                var eLocNode = eCommaNode.previousSibling;
                if (node.nodeValue===eLocNode.nodeValue) {
                    parentNode.removeChild(eCommaNode);
                    parentNode.removeChild(node);
                }
                parentNode.appendChild(document.createTextNode(", "));
                parentNode.appendChild(paisNode);
                postQueue.push(
                    {   "catalogo":"Pais", 
                        "llave":"codigo", 
                        "codigo":cPais, 
                        "solicita":"descripcion", 
                        "_parameter_element":paisNode
                    });
                break;
            case "Pais":
                node.nodeValue = responseText;
                break;
            case "ClaveUnidad":
                while (node.hasChildNodes()) node.removeChild(node.childNodes[0]);
                node.appendChild(document.createTextNode(responseText.split("|").join(": ")));
                break;
            default:
                while (node.hasChildNodes()) node.removeChild(node.childNodes[0]);
                node.appendChild(document.createTextNode(responseText));
        }
    }
    clearTimeout(timeOutPostQueue);
    if (postQueue.length>0) timeOutPostQueue = setTimeout(function() { postService("consultas/CatalogoSAT.php", postQueue.shift(), retFunc); }, 10);
}
function displayFollowingDetail(elem) {
    console.log("INI function displayFollowingDetail");
    collapseBlockFlag=false;
    var detalle = "<UL id='DetalleCFDI' class='dtl'><LI>"+
                  "<TABLE><TR><TH>DATOS</TH><TH class='val'>VALOR</TH><TH class='chk'>CHK</TH></TR></TABLE>"+
                  "</LI></UL>";
    overlayMessage(detalle, "Detalle");
    setTimeout(setOverlayInvoiceDetailListItems, 100, elem.nextElementSibling.children);
    console.log("END function displayFollowingDetail");
}
function setOverlayInvoiceDetailListItems(elemlist) {
    console.log("INI function setOverlayInvoiceDetailListItems");
    var listElem = document.getElementById("DetalleCFDI");
    var lastPath = [];
    var bgColorFlag=true;
    var lastSplitLen=0;
    var splitCountFlag=false;
    var isRootFlag=false;
    var isSubSeqFlag=false;
    var hasBullFlag=false;
    var hasIdentFlag=false;
    for (var i=0; i<elemlist.length; i++) {
        var elemItem = elemlist[i];
        var elemName = elemItem.getAttribute("name");
        if (elemName.startsWith("XSD[Comprobante]") && elemName.indexOf("[xml]")>0) {
            var xmlIdx = elemName.indexOf("[xml]");
            var xmlLen = elemName.length;
            var localname = elemName.slice(17,-1).replace("][xml","");
            var splitarr = localname.split("][");
            var splitlen = splitarr.length;
            isRootFlag=false; isSubSeqFlag=false; hasBullFlag=false; hasIdentFlag=false;
            console.log("localname = "+localname+", splitarr = ["+splitarr.join(",")+"], splitlen="+splitlen+", lastPath = ["+lastPath.join(",")+"], lastlen="+lastPath.length);
            if (splitarr[splitlen-1].charAt(0)==="@") splitarr[splitlen-1] = splitarr[splitlen-1].slice(1);
            var isvalid = true;
            for (var x=0; x<splitlen; x++) {
                if (isvalid && lastPath.length>x && lastPath[x]===splitarr[x]) {
                    splitarr[x] = "";
                    isSubSeqFlag=true;
                } else {
                    isvalid=false;
                    for (var y=0; y<x; y++) {
                        if (y==0) {
                            splitarr[x] = "&nbsp;&bull;&nbsp;"+splitarr[x];
                            hasBullFlag=true;
                        } else {
                            splitarr[x] = "&nbsp; &nbsp;"+splitarr[x];
                            hasIdentFlag=true;
                        }
                    }
                    var myClassList = "";
                    if (hasIdentFlag) myClassList+=" if";
                    if(splitlen>1) {
                     if (x==0) {
                         myClassList+=" root";
                         isRootFlag=true;
                         splitarr[0]+="<span class=\"leaf expanded\"> &oplus;</span>";
                     } else if (isRootFlag) myClassList+=" leaf";
                     //else myClassList+=" leaf"; // " expanded";
                     //if (isSubSeqFlag) myClassList+=" subs";
                    }
                    if (myClassList.length>0) myClassList = " class=\""+myClassList.slice(1)+"\"";
                    splitarr[x] = "<p"+myClassList+">"+splitarr[x]+"</p>";
                }
            }
            if (splitarr[0].length>0) bgColorFlag=!bgColorFlag;
            lastPath = localname.split("][");
            var itemElem = document.createElement("LI");
            if (isRootFlag) {
                itemElem.onclick = function() { toggleBranches(this); };
                itemElem.classList.add("root");
            } else if (isSubSeqFlag) {
                itemElem.classList.add("leaf");
                //itemElem.classList.add("expanded");
            }
            if (splitlen===lastSplitLen) {
                splitCountFlag=!splitCountFlag;
                if (splitCountFlag) {
                    if (bgColorFlag) itemElem.classList.add("oddB");
                    else itemElem.classList.add("oddC");
                } else if (bgColorFlag) itemElem.classList.add("oddA");
            } else {
                splitCountFlag=false;
                if (bgColorFlag) itemElem.classList.add("oddA");
            }
            var tabElem = document.createElement("TABLE");
            var rowElem = document.createElement("TR");
            var cell1Elem = document.createElement("TD");
            cell1Elem.innerHTML = splitarr.join("");
            rowElem.appendChild(cell1Elem);
            var elemValue = false;
            var cell2Elem = document.createElement("TD");
            cell2Elem.classList.add("val");
            var cell3Elem = document.createElement("TD");
            cell3Elem.classList.add("chk");
            var checkImage = document.createElement("IMG");
            checkImage.width="15";
            checkImage.height="15";
            var entityIdentif = false;
            
            if (elemItem.value) {
                var pgrph = document.createElement("P");
                if (isRootFlag) {
                    pgrph.classList.add("leaf");
                }
                var retVal = elemItem.value; // htmlentities
                pgrph.appendChild(document.createTextNode(retVal));
                cell2Elem.appendChild(pgrph);
                checkImage.src = "imagenes/icons/qmark.png";
            }
            if (isRootFlag) {
                checkImage.classList.add("leaf");
            }
            cell3Elem.appendChild(checkImage);
            rowElem.appendChild(cell2Elem);
            rowElem.appendChild(cell3Elem);
            tabElem.appendChild(rowElem);
            itemElem.appendChild(tabElem);
            listElem.appendChild(itemElem);
            lastSplitLen = splitlen;
            
        } else if (elemName.startsWith("XSD[Comprobante]") && elemName.endsWith("[use]")) {
            var elemNext = elemItem.nextElementSibling;
            var localname = elemName.slice(17,-6);
            var splitarr = localname.split("][");
            var splitlen = splitarr.length;
            isRootFlag=false; isSubSeqFlag=false; hasBullFlag=false; hasIdentFlag=false;
            console.log("localname = "+localname+", splitarr = ["+splitarr.join(",")+"], splitlen="+splitlen+", lastPath = ["+lastPath.join(",")+"], lastlen="+lastPath.length);
            if (splitarr[splitlen-1].charAt(0)==="@") splitarr[splitlen-1] = splitarr[splitlen-1].slice(1);
            var isvalid = true;
            for (var x=0; x<splitlen; x++) {
                if (isvalid && lastPath.length>x && lastPath[x]===splitarr[x]) {
                    splitarr[x] = "";
                    isSubSeqFlag=true;
                } else {
                    isvalid=false;
                    for (var y=0; y<x; y++) {
                        if (y==0) {
                            splitarr[x] = "&nbsp;&bull;&nbsp;"+splitarr[x];
                            hasBullFlag=true;
                        } else {
                            splitarr[x] = "&nbsp; &nbsp;"+splitarr[x];
                            hasIdentFlag=true;
                        }
                    }
                    var myClassList = "";
                    if (hasIdentFlag) myClassList+=" if";
                    if(splitlen>1) {
                     if (x==0) {
                         myClassList+=" root";
                         isRootFlag=true;
                         splitarr[0]+="<span class=\"leaf expanded\"> &oplus;</span>";
                     } else if (isRootFlag) myClassList+=" leaf";
                     //else myClassList+=" leaf"; // " expanded";
                     //if (isSubSeqFlag) myClassList+=" subs";
                    }
                    if (myClassList.length>0) myClassList = " class=\""+myClassList.slice(1)+"\"";
                    splitarr[x] = "<p"+myClassList+">"+splitarr[x]+"</p>";
                }
            }
            
            var isEmisorRFC = ( splitarr.length==2 && splitarr[0].indexOf(">Emisor<")>0 && splitarr[1].indexOf(">&nbsp;&bull;&nbsp;Rfc</p>")>0);
            var isReceptorRFC = ( splitarr.length==2 && splitarr[0].indexOf(">Receptor<")>0 && splitarr[1].indexOf(">&nbsp;&bull;&nbsp;Rfc</p>")>0);
            var isConceptos1CPS = ( splitarr.length==3 && splitarr[0].indexOf(">Conceptos<")>0 && splitarr[1].indexOf("Concepto1")>0 && splitarr[2].indexOf("ClaveProdServ")>0);
            var isImpuestosTIR = false; //( splitarr.length==2 && splitarr[0].indexOf(">Impuestos<")>0 && splitarr[1].indexOf(">&nbsp;&bull;&nbsp;TotalImpuestosRetenidos</p>")>0);
            if (splitarr[0].length>0) bgColorFlag=!bgColorFlag;
            lastPath = localname.split("][");
            var itemElem = document.createElement("LI");
            if (isRootFlag) {
                itemElem.onclick = function() { toggleBranches(this); };
                itemElem.classList.add("root");
            } else if (isSubSeqFlag) {
                itemElem.classList.add("leaf");
                //itemElem.classList.add("expanded");
            }
            if (splitlen===lastSplitLen) {
                splitCountFlag=!splitCountFlag;
                if (splitCountFlag) {
                    if (bgColorFlag) itemElem.classList.add("oddB");
                    else itemElem.classList.add("oddC");
                } else if (bgColorFlag) itemElem.classList.add("oddA");
            } else {
                splitCountFlag=false;
                if (bgColorFlag) itemElem.classList.add("oddA");
            }
            var tabElem = document.createElement("TABLE");
            var rowElem = document.createElement("TR");
            var cell1Elem = document.createElement("TD");
            cell1Elem.innerHTML = splitarr.join("");
            rowElem.appendChild(cell1Elem);
            var elemValue = false;
            var cell2Elem = document.createElement("TD");
            cell2Elem.classList.add("val");
            var cell3Elem = document.createElement("TD");
            cell3Elem.classList.add("chk");
            var checkImage = document.createElement("IMG");
            checkImage.width="15";
            checkImage.height="15";
            var entityIdentif = false;
            if (elemNext.getAttribute("name").endsWith("[value]")) {
                var elemType = elemNext.nextElementSibling;
                if (isEmisorRFC||isReceptorRFC||isConceptos1CPS||isImpuestosTIR) {
                    var elemId = elemNext.id;
                    var undscrIdx = elemId.indexOf("_");
                    if (undscrIdx>=0) {
                        var xsdIdf = elemId.slice(0,undscrIdx);
                        var viewCheckImage=true;
                        var unknownMsg="desconocido";
                        if (isEmisorRFC) {
                            var codPrvElem = document.getElementById(xsdIdf+"_CDPRV");
                            if (codPrvElem) entityIdentif=codPrvElem.value;
                        } else if (isReceptorRFC) {
                            var aliGpoElem = document.getElementById(xsdIdf+"_ALIGP");
                            if (aliGpoElem) entityIdentif=aliGpoElem.value;
                        } else if (isConceptos1CPS) {
                            var numConcElem = document.getElementById(xsdIdf+"_NCCPT");
                            if (numConcElem) {
                                entityIdentif=numConcElem.value;
                                if ((+entityIdentif)<=0) entityIdentif=false;
                                else viewCheckImage=false;
                            }
                            if(viewCheckImage) unknownMsg="0";
                        } else if (isImpuestosTIR) {
                            viewCheckImage=false;
                        }
                        var pgrph0 = document.createElement("P");
                        if (entityIdentif) {
                            pgrph0.appendChild(document.createTextNode(entityIdentif));
                        } else {
                            pgrph0.appendChild(document.createTextNode(unknownMsg));
                            pgrph0.classList.add("redden");
                        }
                        cell2Elem.appendChild(pgrph0);
                        if (viewCheckImage) {
                            var checkImage0 = document.createElement("IMG");
                            checkImage0.width="15";
                            checkImage0.height="15";
                            checkImage0.src = entityIdentif?dataImgCheck:dataImgCross;
                            cell3Elem.appendChild(checkImage0);
                        }
                    }
                }
                var pgrph = document.createElement("P");
                if (isRootFlag) {
                    pgrph.classList.add("leaf");
                    //pgrph.classList.add("expanded");
                }
                var retVal = fixInvoiceDetailValue(elemNext.value, elemType.value, pgrph);
                pgrph.appendChild(document.createTextNode(retVal));
                cell2Elem.appendChild(pgrph);
                checkImage.src = dataImgCheck;
            } else if (elemItem.value==="required") {
                checkImage.src=dataImgCross;
            } else if (elemItem.value==="required2") {
                checkImage.src=dataImgCross;
                checkImage.title="Requerimiento interno";
            } else checkImage=false;
            if (checkImage) {
                if (isRootFlag) {
                    checkImage.classList.add("leaf");
                    //checkImage.classList.add("expanded");
                }
                cell3Elem.appendChild(checkImage);
            }
            rowElem.appendChild(cell2Elem);
            rowElem.appendChild(cell3Elem);
            tabElem.appendChild(rowElem);
            itemElem.appendChild(tabElem);
            listElem.appendChild(itemElem);
            lastSplitLen = splitlen;
        }
    }
    adjustOverlay();
    console.log("END function setOverlayInvoiceDetailListItems");
}
function adjustOverlay() {
    var listElem = document.getElementById("DetalleCFDI");
    var ovy = document.getElementById("overlay");
    var winHg = ovy.offsetHeight;
    var maxRaHg = winHg-95;
    var maxDtHg = maxRaHg-20;
    var dra = document.getElementById("dialog_resultarea");
    var detHg = listElem.offsetHeight;
    if (detHg>maxDtHg) {
        dra.classList.add("hScroll");
        dra.style.height = maxRaHg+"px";
    } else {
        dra.classList.remove("hScroll");
        dra.removeAttribute("style");
    }
}
function toggleBranches(elem) {
    console.log("INI function toggleBranches "+elem.tagName);
    toggleLeaves(elem);
    while(elem.nextElementSibling && elem.nextElementSibling.classList.contains("leaf")) {
        toggleLeaf(elem.nextElementSibling);
        elem=elem.nextElementSibling;
    }
    adjustOverlay();
}
function toggleLeaves(elem) {
    for (var i=0; i<elem.children.length; i++) {
        if (!toggleLeaf(elem.children[i]))
            toggleLeaves(elem.children[i]);
    }
}
function toggleLeaf(elem) {
    if (elem.classList.contains("leaf")) {
        if (elem.classList.contains("expanded")) elem.classList.remove("expanded");
        else elem.classList.add("expanded");
        return true;
    }
    return false;
}
function getOverlayInvoiceDetailListItems(elemlist) {
    var texto = "";
    for(var i=0; i<elemlist.length; i++) {
        var elemItem = elemlist[i]; var elemName = elemItem.getAttribute("name");
        if (elemName.startsWith("XSD[Comprobante]") && elemName.endsWith("[use]")) {
            var elemNext = elemItem.nextElementSibling; var localname = elemName.slice(17,-6);
            { // TODO: Como conservar los elementos previos la primera vez y quitarlos en los siguientes solamente. Contemplar usar <br> para el primer elemento nada mas, para que la sangria no crezca tanto
              // TODO: Evaluar como modificar el borde inferior para que abarque todos los elementos internos o en su defecto quitar todos los bordes inferiores, mantener solo el externo de la tabla
                var splitarr = localname.split("]["); var len = splitarr.length; var thisPath = "";
                for (var i=0; i<(len-1); i++) { thisPath += splitarr[i]; splitarr[i] = " &nbsp; &nbsp; "; }
                if (splitarr[len-1].charAt(0)==="@") { splitarr[len-1] = splitarr[len-1].slice(1); }
                localname = splitarr.join("");
            } texto += "<LI><TABLE><TR><TD>"+localname+"</TD><TD class='val'>";
            var elemValue = false;
            if(elemNext.getAttribute("name").endsWith("[value]")) {
                elemType=elemNext.nextElementSibling; console.log("tamaño texto 1: "+texto.length);
                texto += fixInvoiceDetailValue(elemNext.value, elemType.value); console.log("tamaño texto 2: "+texto.length);
                texto += "</TD><TD class='chk'><IMG SRC='' WIDTH='15' HEIGHT='15'>"; console.log("txt3: "+texto.length);
            } else if (elemItem.value==="required") {
                texto += "</TD><TD class='chk'><IMG SRC='' WIDTH='15' HEIGHT='15'>";
            } else texto += "</TD><TD class='chk'>";
            texto += "</TD></TR></TABLE></LI>";
        }
    }
    return texto;
}
function displayFollowingDetailOld(elem) {
    appendLog("INI displayFollowingDetailOld\n");
    var sibling = elem.nextElementSibling;
    if (sibling) {
        if (sibling.tagName==="DIV" && sibling.classList.contains("hidden") && sibling.classList.contains("detail")) {
            var find = '_B_A_S_E';
            var re = new RegExp(find, 'g');
            collapseBlockFlag=false;
            overlayMessage(sibling.innerHTML.replace(re, ''), "Detalle");
            var dra = document.getElementById("dialog_resultarea");
            dra.classList.add("hScroll");
            var hgt = dra.offsetHeight;
            dra.style.height = hgt+"px";
        } else displayFollowingDetailOld(sibling);
    }
    appendLog("END displayFollowingDetailOld\n");
}
function habilitaRegistro(elem) {
    console.log("INI function habilitaRegistro ( elem )");
    if (elem.form) elem.form.submited=elem.id;
    else {
        document.forma_alta.submited=elem.id;
        if (checkSubmittedForm()) {
            var prvSub = document.createElement("INPUT");
            prvSub.type='hidden';
            prvSub.name='proveedor_submit';
            prvSub.value='proveedor_submit';
            document.forma_alta.appendChild(prvSub);
            document.forma_alta.submit();
        }
    }
}

function fixPerson(razsoc, rfc) {
    console.log("INI function fixPerson. Razon Social = '"+razsoc+"'. RFC = '"+rfc+"'");
    var cleanRazSoc = razsoc.replace(" del "," ").replace(" las "," ").replace(" de "," ").replace(" la "," ").replace(" y "," ").replace(" a "," ");
    var moralRE = /^([A-Z]{3})[0-9]{6}/;
    var fisicRE = /^([A-Z]{4})[0-9]{6}/;
    var result = rfc.match(moralRE);
    var esMoral=false;
    var esFisica=false;
    if (result) {
        esMoral=true;
        console.log(" ES PERSONA MORAL!!! "+result[1]);
    } else {
        result = rfc.match(fisicRE);
        if (result) {
            esFisica=true;
            console.log(" ES PERSONA FISICA!! "+result[1]);
            var narr = cleanRazSoc.split(" ");
            var nlen = narr.length;
            // NOMBRES PATERNO MATERNO => PATERNO MATERNO NOMBRES
            if( nlen>=3 &&
                narr[nlen-2].charAt(0) === result[1].charAt(0) && // 1er letra de 1er palabra de RazSoc coincide con 1er letra del RFC ~= Es el Apellido Paterno
                narr[nlen-1].charAt(0) === result[1].charAt(2) && // 1er letra de 2da palabra de RazSoc coincide con 3er letra del RFC ~= Es el Apellido Materno
                narr[0].charAt(0) === result[1].charAt(3) // 1er letra de 3er palabra de RazSoc coincide con 4ta letra del RFC ~= Es el Nombre
                ) {
                var patIdx = razsoc.indexOf(narr[nlen-2]);
                return razsoc.slice(patIdx)+" "+razsoc.slice(0,patIdx-1);
            }
        }
    }
    return razsoc;
}
function checkSubmittedForm() {
    console.log("INI function checkSubmittedForm");
    var dbgElem = document.getElementById("debugField");
    if (submitBlockade) {
        console.log("END checkSubmittedForm: Submit Blockade");
        return false;
    }
    console.log("");
    if (changeMessage && changeMessage.length && changeMessage.length>0) {
        overlayMessage(changeMessage,"Error");
        console.log("END checkSubmittedForm: Submit Error");
        return false;
    }
    console.log("B");
    submitBlockade=true;
    try {
    appendLog("INI checkSubmittedForm() \n");
    appendLog("SUBMITED: "+document.forma_alta.submited+"\n");
    var accepted = true;
    if (dbgElem) {
        appendLog("DEBUGGING MODE");
        var elements = document.forms["forma_alta"].elements;
        for (var i=0; i<elements.length; i++) {
            appendLog(" - TAG="+elements[i].tagName+". NAME="+elements[i].name+". VALUE="+elements[i].value);
        }
        appendLog(" - TOTAL OF "+elements.length+" ELEMENTS");
        console.log(" - TOTAL OF "+elements.length+" ELEMENTS");
    }
    console.log("C "+document.forma_alta.submited);

    if (document.forma_alta.submited=="proveedor_submit") {
        if (dbgElem) console.log("<<"+document.forma_alta.submited+">>");
        appendLog("submited = PROVEEDOR_SUBMIT");
        var menuActBtn = document.getElementById("menu_accion");
        menuActBtn.value="Registro";
        var submitBtn = document.getElementById("proveedor_submit");
        var rfc = submitBtn.getAttribute("rfc");
        var razsoc = fixPerson(submitBtn.getAttribute("razsoc"),rfc);
        if (dbgElem) console.log("A2");
        if (!razsoc || razsoc.length==0) {
            console.log("END checkSubmittedForm: No RazSoc");
            return false;
        }
        console.log("D");
        var prvFld = document.createElement("INPUT");
        prvFld.type="hidden";
        prvFld.name="proveedor_field";
        prvFld.value=razsoc;
        var prvRfc = document.createElement("INPUT");
        prvRfc.type="hidden";
        prvRfc.name="proveedor_rfc";
        prvRfc.value=rfc;
        var prvCod = document.createElement("INPUT");
        prvCod.type="hidden";
        prvCod.name="proveedor_code";
        prvCod.value=razsoc.charAt(0)+"-";
        var retAct = document.createElement("INPUT");
        retAct.type="hidden";
        if (dbgElem) console.log("A3");
        retAct.name="return_menu_action";
        retAct.value="Alta Facturas";
        menuActBtn.parentNode.appendChild(prvCod);
        menuActBtn.parentNode.appendChild(prvFld);
        menuActBtn.parentNode.appendChild(prvRfc);
        console.log("Registro Proveedor");
        if (dbgElem) console.log("A4");
<?php
    if ($esDesarrollo) {
?>
        if (accepted) {
            overlayMessage(getParagraphObject("ALTA INTERRUMPIDA 0"), "ERROR");
            console.log("END checkSubmittedForm: ADMIN INTERRUPTED");
            return false;
        }
<?php
    }
?>
        console.log("END checkSubmittedForm: ACCEPTED IS "+(accepted?"TRUE":"FALSE"));
        return accepted;
    } else if (document.forma_alta.submited=="submitxml") {
        if (dbgElem) console.log("<<"+document.forma_alta.submited+">>");
        appendLog("submited = SUBMIT XML");
        var xf = document.getElementById("xmlfiles");
        if (xf.files.length<1) {
            appendLog("END. No files detected\n");
            if (dbgElem) console.log("B2");
            console.log("END checkSubmittedForm: NO FILES");
            return false;
        }
        console.log("E");
        var xmls = [];
        var pdfs = [];
        var invxml = [];
        for(var i=0; i<xf.files.length; i++) {
            var fil = xf.files[i];
            var nam = fil.name;
            var typ = fil.type
            if (typ==="text/xml") {
                var newname = nam.slice(0,-4);
                xmls.push(newname);
                var idx = newname.indexOf("_");
                if (idx>0) {
                    invxml.push(newname.slice(idx+1)+newname.slice(0,idx));
                }
            } else if (typ==="application/pdf") {
                pdfs.push(nam.slice(0,-4));
            } else {
                overlayMessage(getParagraphObject("Sólo pueden procesarse archivos XML y PDF representativos de comprobante de factura digital."), "ERROR");
                console.log("END checkSubmittedForm: Tipo archivo "+typ+" no aceptado");
                return false;
            }
        }
        console.log("F");
        if (xmls.length><?= $maxXML ?>) {
            appendLog("END. Too many files\n");
            overlayMessage(getParagraphObject("Puede verificar m&aacute;ximo <?= $maxXML ?> archivos XML de forma simult&aacute;nea."), "ERROR");
            if (dbgElem) console.log("B3");
            console.log("END checkSubmittedForm: Demasiados XMLs: "+xmls.length);
            return false;
        } else if (xmls.length==0) {
            appendLog("END. XML files required\n");
            overlayMessage(getParagraphObject("Es requerido al menos un archivo de tipo XML"),"ERROR");
            console.log("END checkSubmittedForm: NO XML FILES");
            return false;
        }
        console.log("G");
        for (var p=0; p<pdfs.length; p++) {
            var found=false;
            for (var x=0; x<xmls.length; x++) {
                if (pdfs[p]===xmls[x]) { found=true; break; }
            }
            if (!found) for (var y=0; y<invxml.length; y++) {
                if (pdfs[p]===invxml[y] || pdfs[p].indexOf(invxml[y])>=0) { found=true; break; }
            }
            if (!found) {
                console.log("PDF sin XML:\n'"+pdfs[p]+"'");
                console.log("XMLS:\n'"+xmls.join("'\n'")+"'");
                console.log("INVXMLS:\n'"+invxml.join("'\n'")+"'");
                const xmlSum=xmls+invxml;
                logService("Alta Invalida porque archivo PDF '"+pdfs[p]+".pdf' no corresponde a un xml: '"+xmlSum.join(".xml','")+".xml'");
                overlayMessage(getParagraphObject("El nombre del archivo "+pdfs[p]+".pdf debe ser el mismo de alguno de los archivos XML."), "ERROR");
                console.log("END checkSubmittedForm: PDF NO COINCIDE");
                return false;
            }
        }
        console.log("H");
        if (_forma_submitted == false) {
            _forma_submitted = true;
            toggleClass('waiting-roll', 'hidden');
            if (accepted) toggleClass('help-screen', 'hidden');
            setClass('area_scrollable', 'hidden');
            setClass('xml_insert', 'hidden');
            appendLog("END. SUBMIT. ONCE!\n");
            if (dbgElem) console.log("B4");
            console.log("END checkSubmittedForm: ACCEPTED[B] IS "+(accepted?"TRUE":"FALSE"));
            return accepted;
        }
        toggleClassRoll('waiting-roll', ['bgwhite','bgred','bgyellow','bgblue','bgcyan','bgmagenta','bggreen','bgblack']);
        appendLog("END. ALREADY SUBMITED! Nothing happens.");
        if (dbgElem) console.log("B5");
        console.log("END checkSubmittedForm: ALREADY SUBMITTED... ignored A");
        return false;
    } else if (document.forma_alta.submited=="insertxml") {
        if (dbgElem) console.log("<<"+document.forma_alta.submited+">>");
        appendLog("submited = INSERT XML");
        const table = document.getElementById("load_invoice_structure");
        const tbody = table.firstElementChild;
        const uploadDataContainer = tbody.getElementsByClassName("uploadData");
        let errormessage = [];
        let esCritico = false;
        const pdfRE = /pdffile\[(\d+)\]/;
        const invRE = /factura\[(\d+)\]\[(\w+)\]/;
        const artRE = /factura\[(\d+)\]\[concepto\]\[(\d+)\]\[(\w+)\]/;
        console.log("I-J");
        for (let i=0; i<uploadDataContainer.length; i++) {
            const uploadInputDataElems = uploadDataContainer[i].getElementsByTagName("input");
            let hasMoreMissingData = (errormessage.length>0);
            let pdffilename = "";
            let xmlfilename = "";
            let eafilename = "";
            let uuidcode = "";
            let rfcfolio = "";
            var dataId = "";
            var pedido = "";
            var remision = "";
            var logconceptos = "";
            var errorconcepto = [];
            var idxerrcon = "";
            var tipoCompro = "";
            for (var j=0; j<uploadInputDataElems.length; j++) {
                console.log("I="+i+",J="+j);
                var elemobj = uploadInputDataElems[j];
                var elemname = elemobj.name;
                var elemvalue = elemobj.value;
                console.log("(i:'"+i+"'. j:'"+j+"'. elemname:'"+elemname+"'. elemvalue:'"+elemvalue+"'.");
                if (elemname.startsWith("eafile")) {
                    eafilename=elemvalue;
                } else if (elemname.startsWith("pdffile")) {
                    var m = elemname.match(pdfRE);
                    var tmpId = (m && m.length>1)?m[1]:false;
                    if (tmpId!==false) {
                        if (dataId.length==0) dataId = tmpId;
                        else if (dataId!==tmpId) {
                            errormessage.push({eName:"P",eChilds:[{eText:"Los datos no se cargaron correctamente. Seleccione nuevamente el botón "},{eName:"B",eText:"Alta Facturas y Pagos"},{eText:" para refrescar la pantalla."}]});
                            esCritico=true;
                            break;
                        }
                    } else {
                        errormessage.push({eName:"P",eChilds:[{eText:"Los datos no se cargaron adecuadamente. Seleccione nuevamente el botón "},{eName:"B",eText:"Alta Facturas y Pagos"},{eText:" para refrescar la pantalla."}]});
                        esCritico=true;
                        break;
                    }
                    pdffilename = elemvalue;
                } else if (elemname.startsWith("factura")) {
                    var m = elemname.match(invRE);
                    var tmpId = (m && m.length>1)?m[1]:false;
                    if (tmpId!==false) {
                        if (dataId.length==0) dataId = tmpId;
                        else if (dataId!==tmpId) {
                            errormessage.push({eName:"P",eChilds:[{eText:"Los datos no se cargaron correctamente. Seleccione el botón "},{eName:"B",eText:"Alta Facturas y Pagos"},{eText:" nuevamente para refrescar la pantalla."}]});
                            esCritico=true;
                            break;
                        }
                    } else {
                        errormessage.push({eName:"P",eChilds:[{eText:"Los datos no se cargaron adecuadamente. Seleccione el botón "},{eName:"B",eText:"Alta Facturas y Pagos"},{eText:" nuevamente para refrescar la pantalla."}]});
                        esCritico=true;
                        break;
                    }
                    var tmptype = m[2];
                    switch (tmptype) {
                        case "oname": xmlfilename = elemvalue; idxerrcon=""; break;
                        case "nname": rfcfolio = elemvalue; idxerrcon=""; break;
                        case "uuid": uuidcode = elemvalue; idxerrcon=""; break;
                        case "pedido": pedido = elemvalue.trim(); elemobj.value=pedido; idxerrcon=""; break;
                        case "remision": remision = elemvalue.trim(); elemobj.value=remision; idxerrcon=""; break;
                        case "tipoComprobante": tipoCompro = elemvalue; idxerrcon=""; break;
                        case "concepto":
                            var n = elemname.match(artRE);
                            var artcol = (n && n.length>1)?n[3]:false;
                            if (artcol===false) {
                                errormessage.push({eName:"P",eChilds:[{eText:"Los datos de artículos no se cargaron adecuadamente. Seleccione el botón "},{eName:"B",eText:"Alta Facturas y Pagos"},{eText:" para refrescar la pantalla."}]});
                                esCritico=true;
                                break;
                            }
                            var artId = n[2];
                            if (artId===false) {
                                errormessage.push({eName:"P",eChilds:[{eText:"Los datos de artículos no se cargaron adecuadamente. Seleccione el botón "},{eName:"B",eText:"Alta Facturas y Pagos"},{eText:" para refrescar la pantalla."}]});
                                esCritico=true;
                                break;
                            }
                            if (artcol==="codigo") {
                                if(elemvalue.length==0) {
                                    errorconcepto.push({eName:"P",eText:"Falta capturar el código del concepto "}) += "";
                                    idxerrcon=artId;
                                } else {
                                    if (logconceptos.length>0) logconceptos+=", ";
                                    logconceptos+="'"+elemvalue+"'";
                                }
                            } else if (artcol==="descripcion" && idxerrcon===artId) {
                                idxerrcon="";
                                lastElemObjAppend(errorconcepto, "'"+elemvalue+"'");
                                //let lastElem=errorconcepto.pop();
                                //if (!lastElem) lastElem={eText:""};
                                //lastElem.eText+="'"+elemvalue+"'";
                                //errorconcepto.push(lastElem);
                            }
                            break;
                        default: idxerrcon="";
                    }
                }
                if (esCritico) break;
            }
            if (esCritico) break;
            if (pdffilename.length==0) {
                // se permite que no se incluya pdf
                // toDo: Revisar en el flujo del proceso que no sea requerido, en ese caso hay que generar un pdf nuevo
            } else if (!esPDF(pdffilename)) {
                const errorText="El archivo debe tener formato PDF de la factura "+xmlfilename+". ";
                if (hasMoreMissingData) errormessage.push({eName:"P",eText:errorText});
                else {
                    lastElemObjAppend(errormessage, errorText);
                    //let lastElem=errormessage.pop();
                    //if (!lastElem) lastElem={eText:""};
                    //lastElem.eText+="El archivo debe tener formato PDF de la factura "+xmlfilename;
                    //errormessage.push(lastElem);
                    hasMoreMissingData=true;
                }
            }
            if (eafilename.length>0) {
                if(!esPDF(eafilename)) {
                    const errorText="El archivo entrada de almacén debe tener formato PDF. ";
                    if (hasMoreMissingData) errormessage.push({eName:"P",eText:errorText});
                    else {
                        lastElemObjAppend(errormessage, errorText);
                        //let lastElem=errormessage.pop();
                        //if (!lastElem) lastElem={eText:""};
                        //lastElem.eText+="El archivo entrada de almacén debe tener formato PDF";
                        //errormessage.push(lastElem);
                        hasMoreMissingData=true;
                    }
                }
            }
            if (tipoCompro!=="PAGO" && pedido.length==0) {
                const errorText="Falta capturar el pedido de la factura "+xmlfilename+". ";
                if (hasMoreMissingData) errormessage.push({eName:"P", eText:errorText});
                else {
                    lastElemObjAppend(errormessage, errorText);
                    //let lastElem=errormessage.pop();
                    //if (!lastElem) lastElem={eText:""};
                    //lastElem.eText+="Falta capturar el pedido de la factura "+xmlfilename;
                    //errormessage.push(lastElem);
                    hasMoreMissingData=true;
                }
            }
            if (errorconcepto.length>0) {
                if (hasMoreMissingData) errormessage.push(...errorconcepto);
                else { errormessage = errorconcepto; hasMoreMissingData=true; }
            }
        }
        console.log("K");
        if (errormessage.length>1) {
            overlayMessage(getParagraphObject(errormessage), "ERROR");
            if (dbgElem) console.log("C2");
            console.log("END checkSubmittedForm: ERROR");
            return false;
        }
<?php
    if ($esDesarrollo) {
?>
        if (false) {
            overlayMessage(getParagraphObject("ALTA INTERRUMPIDA (INSERT)"), "ERROR");
            console.log("END checkSubmittedForm: FALSE");
            return false;
        }
<?php
    }
?>
        if (_forma_submitted == false) {
            _forma_submitted = true;
            toggleClass('waiting-roll', 'hidden');
            setClass('area_scrollable', 'hidden');
            setClass('xml_insert', 'hidden');
            appendLog("END. INSERT. ONCE!\n");
            if (dbgElem) console.log("C3");
            console.log("END checkSubmittedForm: ACCEPTED[C] IS "+(accepted?"TRUE":"FALSE"));
            return accepted;
        }
        console.log("L");
        toggleClassRoll('waiting-roll', ['bgwhite','bgred','bgyellow','bgblue','bgcyan','bgmagenta','bggreen','bgblack']);
        appendLog("END. ALREADY SUBMITED! Nothing happens.");
        if (dbgElem) console.log("C4");
        console.log("END checkSubmittedForm: ALREADY SUBMITTED... ignored B");
        return false;
    }
    appendLog("END. NO VALID SUBMIT BUTTON\n");
    if (dbgElem) console.log("D1");
    console.log("END checkSubmittedForm: INVALID SUBMIT ");
    return false;
    } catch (err) {
        console.log("END checkSubmittedForm: CAUGHT ERROR:",err);
        if (dbgElem) console.log("E1");
        return false;
    } finally {
        console.log("END checkSubmittedForm: Finally submitBlockade removed");
        submitBlockade=false;
    }
}
function addEAFile(idx) {
    const ifl=ebyid("eafile"+idx);
    const btn=ebyid("eafilebtn"+idx);
    const msg=ebyid("eamsg"+idx);
    if (ifl.files && ifl.files.length>0) {
        const fl=ifl.files[0];
        console.log("FILE: ",fl);
        const name=fl.name;
        let vwname=name;
        if(vwname.length>14)
            vwname=vwname.slice(0,5)+"..."+vwname.slice(-9);
        const size=+fl.size;
        const type=fl.type;
        if (type!=="application/pdf") {
            msg.textContent="El archivo '"+vwname+"' no tiene el formato requerido (PDF)";
            btn.textContent="Anexar PDF";
            if (clearFileInput(ifl)) console.log("No fue posible limpiar contenido: '"+ifl.value+"'",ifl.files);
        } else if (size>2097000) {
            msg.textContent="El archivo '"+vwname+"' excede el tamaño máximo permitido de 2MB";
            btn.textContent="Anexar PDF";
            if (clearFileInput(ifl)) console.log("No fue posible limpiar contenido: '"+ifl.value+"'",ifl.files);
        } else {
            msg.textContent="";
            btn.textContent=vwname;
        }
    } else {
        msg.textContent="";
        btn.textContent="Anexar PDF";
    }
}
var fillables = [];
function setFillables(fillArr) {
    fillables = fillArr;
}
function checkFillables() {
    var ready=true;
    for (var i=0; i<fillables.length; i++) {
        var box = document.getElementById(fillables[i]);
        if (box.value.length>0) box.classList.remove("highlight");
        else {
            ready=false;
            if (!box.classList.contains("highlight")) box.classList.add("highlight");
        }
    }
    var loadbtn = document.getElementById("insertxml");
    if (ready) {
        loadbtn.classList.add("highlight");
        loadbtn.focus();
    } else if (loadbtn.classList.contains("highlight"))
        loadbtn.classList.remove("highlight");
}
function esPDF(filename) {
    var parts = filename.split('.');
    var extension = parts[parts.length - 1];
    return (extension.toLowerCase() === "pdf");
}
function setTabTitle(evt) {
    console.log("setTabTitle");
    if (true) return true;
    // La función modifica el titulo de la ventana actual en lugar de la que se abre.
    // Por eso se omite el codigo siguiente
    if (!evt) evt = window.event;
    if (evt) console.log("has event");
    var tgt = evt.target || evt.srcElement;
    if (tgt.nodeType==3) tgt = tgt.parentNode;
    if (tgt) console.log("has target "+tgt);
    if(tgt.hasAttribute("data-title")) {
        document.title=tgt.getAttribute("data-title");
        console.log("set tab title as "+tgt.getAttribute("data-title"));
    }
}
function checkChange() {
    changeMessage = "";
    var xf = document.getElementById("xmlfiles");
    if (xf.files.length>0) {
        for(var i=0; i<xf.files.length; i++) {
            var fileData = xf.files[i];
            var name = fileData.name;
            var size = +fileData.size;
            var type = fileData.type;
            var prfx = "";
            var sufx = "";
            if (type!=="application/pdf" && type!=="text/xml") {
                changeMessage += "<p>El archivo '"+name+"' no tiene el formato requerido (XML o PDF)</p>";
                prfx = "ERROR ";
                sufx += " | type";
            }
            if (size>2097000) {
                changeMessage += "<p>El archivo '"+name+"' excede el tamaño máximo permitido de 2MB</p>";
                prfx = "ERROR ";
                sufx += " | size";
            }
            console.log(prfx+"File "+name+" "+type+" "+size+"bytes"+sufx);
        }
        
        if (changeMessage.length>0) overlayMessage(changeMessage,"Error");
        else {
            xf.classList.remove("highlight");
            var sx = document.getElementById("submitxml");
            if (sx) {
                sx.classList.add("highlight");
                sx.focus();
            }
        }
    }
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
var currentAction=null;
function doCurrentAction(triggerElem, onlyOnce) {
    if (currentAction!=null && currentAction instanceof Function) {
        currentAction(triggerElem);
        if (onlyOnce) currentAction=null;
    }
}
function setCurrentAction(newAction) {
    currentAction=newAction;
}
function removeCurrentAction() {
    currentAction=null;
}
function toggleHiddenError(triggerElem) {
    var elem = triggerElem;
    while(elem.nextElementSibling) {
        elem = elem.nextElementSibling;
        if (elem.classList.contains("hidden")) elem.classList.remove("hidden");
        else elem.classList.add("hidden");
    }
}
function toggleCollapse(source, nodeId, goSingle, isRecursive, prompt) {
    if (!prompt) prompt="";
    
    console.log(prompt+"INI function toggleCollapse( source='" + source + "', nodeId='" + nodeId + "', goSingle=" + (goSingle?"TRUE":"FALSE") + ", isRecursive=" + (isRecursive?"TRUE":"FALSE") + " ) collapseBlockFlag=" + (collapseBlockFlag?"TRUE":"FALSE") + ", collapseTimer=>" + (collapseTimer?"YES":"NO"));
    var isFinisher=false;
    if (!goSingle && !isRecursive) {
        if (collapseBlockFlag) { console.log(prompt+"END function toggleCollapse"); return; }
        if (collapseTimer) {
            collapseBlockFlag=true; isFinisher=true; isRecursive=true; clearTimeout(collapseTimer); collapseTimer=false;
            console.log(prompt+"   START RECURSIVE");
        } else {
            collapseTimer = setTimeout(function() { toggleCollapse(source, nodeId, true); }, 250);
            console.log(prompt+"END function toggleCollapse TRY SINGLE");
            return;
        }
    } else {
        if (collapseTimer) { clearTimeout(collapseTimer); collapseTimer=false; }
        if (goSingle) {
            if (collapseBlockFlag || isRecursive) { console.log(prompt+"END function toggleCollapse"); return; }
            collapseBlockFlag=true;
            isFinisher=true;
        }
    }
    var effDv = ( source && source.firstElementChild && source.firstElementChild.tagName === "DIV" && source.firstElementChild.classList.contains("ulmenu") ) ? source.firstElementChild : false;
    var elem = document.getElementById(nodeId);
    console.log(prompt+" - Elem id:"+nodeId+", class:'"+elem.getAttribute('class')+"'");
    if (elem.classList.contains("node") || elem.classList.contains("leaf")) {
        if (nodeId!=="nodeTOP") {
            if (elem.classList.contains("expanded")) {
                elem.classList.remove("expanded");
                if (effDv) effDv.classList.remove("expanded");
            } else {
                elem.classList.add("expanded");
                if (effDv) effDv.classList.add("expanded");
            }
        } else console.log("   IS TOP NODE");
        if (isRecursive && elem.children) {
            var subnode = elem.children;
            var sublen = subnode.length;
            for (var i=0; i<sublen; i++) {
                var sbnd = subnode[i];
                if (sbnd.tagName==="LI") {
                    var frst = sbnd.firstElementChild;
                    if (frst===null) {
                        console.log(prompt+"NUL FIRST CHILd OF '"+nodeId+"'.'"+sbnd.tagName+"'#'"+sbnd.id+"'");
                    } else if (frst.tagName==="SPAN" && frst.classList.contains("link")) {
                        var scnd = frst.nextElementSibling;
                        if (scnd.classList.contains("node") || scnd.classList.contains("leaf")) {
                            toggleCollapse(frst, scnd.id, false, true, prompt+"   ");
                        }
                    }
                }
            }
        }
    }
    if (!isRecursive || isFinisher) collapseBlockFlag=false;
    console.log(prompt+"END function toggleCollapse");
}
function ajustaTablasConcepto() { // resize
    let fixes=0;
    const fixList=lbycn("fixWid");
    console.log("INI function ajustaTablasConcepto. FOUND="+(fixList.length));
    fee(fixList,cl=>{const fxHd=ebyid(cl.getAttribute("fixId"));let ow=fxHd.offsetWidth+"px";if(cl.style.width!=ow){cl.style.width=fxHd.offsetWidth+"px";fixes++;}});
    console.log("END function ajustaTablasConcepto FIXED="+fixes);
}
function doClick(elemId) {
    document.getElementById(elemId).click();
}
function readFile(evt) {
    var files = evt.target.files;
    var file = files[0];
    var reader = new FileReader();
    reader.onload = function(event) {
        console.log((('name' in file)?file.name:"?")+" ("+(('size' in file)?file.size:"?")+") "+(('type' in file)?file.type:"?")+"\n"+event.target.result);
    }
    reader.readAsText(file);
}
function helper(key) {
    if (key==='uploadinv')
        overlayMessage("<H2>Importar Num.Pedido y Código de Conceptos</H2><p></p><ul class=\"lefted\"><li> Crear archivo de texto. La primer línea debe contener el Num.Pedido, las siguientes los códigos de los conceptos en líneas consecutivas<br><img class=\"altafacturacell\" src=\"imagenes/uploadDataTextSample.png\"></li><li> Presionar botón <img class=\"vAlignCenter\" src=\"imagenes/icons/upload1.png\"> y seleccionar el archivo de texto</li><li>Se copiará la primer línea del archivo al pedido y las siguientes en los códigos de conceptos consecutivamente<br><img class=\"altafacturacell\" src=\"imagenes/uploadDataFormSample.png\"></li></ul>","Asistencia");
}
function exportInputTextToFile(evt, idx) {
}
function importInputTextFromFile(evt, idx) {
    var files = evt.target.files;
    var file = files[0];
    var reader = new FileReader();
    reader.onload = function(event) {
        if (file.type!=="text/plain") { alert('El archivo debe ser de texto plano (extensión txt)'); console.log('ImportText-Error: Filetype is '+file.type); return; }
        if (file.size>50000) { alert('El contenido del archivo es demasiado grande'); return; }
        var text = event.target.result;
        var lines = text.split(/[\r\n]+/g);
        var ln = 0;
        while(lines[ln].length<1) ln++;
        var pedidoElem = document.getElementById("pedido"+idx);
        if (pedidoElem) {
            lines[ln]=lines[ln].trim();
            if (lines[ln].length>20) pedidoElem.value = lines[ln].slice(0,20);
            else pedidoElem.value = lines[ln];
            var cn = 0;
            console.log("Lines="+lines.length);
            for (var i=ln+1; i<lines.length; i++) {
                if (lines[i].length<1) continue;
                var conceptoElem = document.getElementById("concepto"+idx+"_"+cn);
                if (conceptoElem) {
                    if (lines[i].length>45) conceptoElem.value = lines[i].slice(0,45);
                    else conceptoElem.value = lines[i];
                    cn++;
                } else {
                    console.log("LOOP ENDED IN LINE "+i);
                    break;
                }
            }
            console.log("LAST CONSECUTIVE IS "+cn);
        }
    }
    reader.readAsText(file);
}
<?php
clog1seq(-1);
clog2end("scripts.altafactura");
