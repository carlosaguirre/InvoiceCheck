<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.generatxt");
clog1seq(1);
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas");
$esAvance = validaPerfil("Avance");
?>
window.onload = fillPaginationIndexes;
var entidadTipos = ["codigo", "rfc", "razon"];
var showGpo= false, showPrv=false;
var timeOutTipping=0;
var timeOutAux=0;
function pickType(elem) {
          if (!elem || !elem.value) { return; }
          for (var i=0; i<entidadTipos.length; i++) {
              var tipo = entidadTipos[i];
              var selGpo = document.getElementById("gpot"+tipo);
              var selPrv = document.getElementById("prvt"+tipo);
              if (elem.value == "t"+tipo) {
                  showGpo = selGpo;
                  showPrv = selPrv;
              } else {
                  selGpo.classList.add("hidden");
                  selPrv.classList.add("hidden");
              }
          }
          if (showGpo) { showGpo.classList.remove("hidden"); }
          if (showPrv) { showPrv.classList.remove("hidden"); }
}
function buscaFacturas() {
    document.forms["repfactform"].command.value="Buscar";
}

var _forma_submitted = false;
function resetForm() {
    var rvw = document.getElementById("countBackup");
    if (rvw) while(rvw.firstChild) rvw.removeChild(rvw.firstChild);
    var initHidden = document.getElementsByClassName("initHidden");
    for (var i=0; i<initHidden.length; i++) initHidden[i].classList.add("hidden");
}
function waitingBlock(index) {
    console.log("WAITING BLOCK");
    if (!index) {
    console.log(" - - - XYZ - - -");
        var tbd = document.getElementById("dialog_tbody");
        while(tbd.firstChild) tbd.removeChild(tbd.firstChild);
        var row = document.createElement("TR");
        var cell = document.createElement("TD");
        var img = document.createElement("IMG");
        img.src = "imagenes/ledoff.gif";
        cell.colspan="5";
        cell.classList.add("centered");
        cell.appendChild(img);
        row.appendChild(cell);
        tbd.appendChild(row);
        var tbDT = tbd.parentNode;
        if (tbDT.classList.contains("hidden")) tbDT.classList.remove("hidden");
    }
}
function submitCallbackSuccessFunc(xmlHttpPost, isPartialResponse) {
 console.log("Success Callback"+(isPartialResponse?" Partial":"")+"!");
    var formName = 'repfactform';
    var formElem = document.getElementById(formName);
 console.log("FormElem Command Value = "+formElem.command.value);
    if (formElem.command.value==="Buscar") {
        let ctrlCheck = ebyid("controlCheck");
        if (ctrlCheck) ctrlCheck.checked=true;
        var aExportar = document.getElementsByClassName("facturasAExportar");
        var num = (aExportar?aExportar.length:0);
        console.log("Command Encontradas : "+num);
        if (num>0 || (!xmlHttpPost && isPartialResponse)) {
            var elements = document.getElementsByClassName("datatable");
            for (var i=0; i<elements.length; i++) {
                elements[i].classList.remove("hidden");
            }
        } else if (xmlHttpPost && !isPartialResponse) {
            var initHidden = document.getElementsByClassName("initHidden");
            for (var i=0; i<initHidden.length; i++) initHidden[i].classList.add("hidden");
        }
        if (!isPartialResponse) {
            var rvw = document.getElementById("countBackup");
            if (rvw) {
                rvw.appendChild(document.createTextNode(num+" encontrada"+(num==1?"":"s")));
                rvw.appendChild(document.createTextNode(" \u00A0"));
            }
        }
    }
}
function exportarDirecto() {
    var params = "";
    var grupoElem = document.getElementById("gpotcodigo");
    if (!grupoElem || grupoElem.value.length==0) {
        overlayMessage("Debe especificar una Empresa","Error");
        return;
    } else {
        var cliente = grupoElem.options[grupoElem.selectedIndex].text;
        switch(cliente) {
            case "CASABLANCA": params+="&cliente=LAMINADOS"; break;
            case "GYL": params+="&cliente=PRODUCTORA"; break;
            default: params+="&cliente="+cliente;
        }
    }
    var proveedorElem = document.getElementById("prvtcodigo");
    if (proveedorElem) params+="&proveedor="+proveedorElem.value;
    var fechaIniElem = document.getElementById("fechaInicio");
    if (fechaIniElem) params+="&fini="+fechaIniElem.value;
    var fechaFinElem = document.getElementById("fechaFin");
    if (fechaFinElem) params+="&ffin="+fechaFinElem.value;
    var statusElem = document.getElementById("status");
    if (statusElem) params+="&status="+statusElem.value;
    var facturas = getFacturas();
    if (!facturas||facturas.length==0) {
        overlayMessage("Debe marcar al menos una factura para exportar","Error");
        return;
    }
    var expUrl='consultas/Facturas.php?modo=ftp&salida=htmlp&exportar='+facturas+params;
    var xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            console.log("Respuesta de Exportación Directa Recibida ("+xmlhttp.responseText.length+")");
            overlayMessage(xmlhttp.responseText, "Detalle");
        }
    };
    xmlhttp.open("GET",expUrl,true);
    xmlhttp.send();
    console.log("Direct Export sent: "+expUrl);
    overlayWheel();
}
function getFacturas() {
    let idList = false;
    let aExportar = document.getElementsByClassName("facturasAExportar");
    if (aExportar && aExportar.length>0) {
        idList="";
        let added=0;
        for (let i=0; i<aExportar.length; i++) {
            let idf=aExportar[i].getAttribute("idf");
            let ichk=ebyid("invcheck"+idf);
            if (ichk && !ichk.checked) continue;
            if(added>0) idList+=",";
            idList+=idf;
            added++;
        }
    }
    return idList;
}
function submitAjax(submitValue) {
    console.log("submitAjax: "+submitValue);
    resetForm();
    waitingBlock();
    var postURL = 'selectores/generatxt.php';
    var formName = 'repfactform';
    var resultDiv = 'dialog_tbody';
    var waitingHtml = "<tr><td colspan=\"12\" class=\"centered\"><img src=\"<?=$waitImgName?>\" width=\"360\" height=\"360\"></td></tr>";
    var formElem = document.forms[formName];
    formElem.command.value = submitValue;
    console.log("to ajaxPost: "+postURL+", "+formName+", "+resultDiv+". Command="+formElem.command.value);
    console.log(waitingHtml);
    document.getElementById(resultDiv).innerHTML = "";
    ajaxPost(postURL, formName, resultDiv, waitingHtml, submitCallbackSuccessFunc);
    console.log("submitAjax DONE!");
    return false;
}
function selectedItem(prefix) {
    var forma = document.repfactform;
    if (forma) {
        var tipoListaElem = forma.tipolista;
        if (tipoListaElem) {
            var aux = Object.prototype.toString.call(tipoListaElem); // IE=[object HtmlCollection], FF/Ch=[object RadioNodeList]
            if (aux == "[object HTMLCollection]") {
                for (var i=0; i<tipoListaElem.length; i++) {
                    if (tipoListaElem[i].checked)
                        tipoListaVal = tipoListaElem[i].value;
                }
            } else tipoListaVal = tipoListaElem.value;
        }
    }
    var itemVal = getValueTxt(prefix+tipoListaVal);
    console.log(prefix+" "+tipoListaVal+" = "+itemVal);
    fillValue(prefix+"tcodigo", itemVal);
    fillValue(prefix+"trfc", itemVal);
    fillValue(prefix+"trazon", itemVal);
    
    var gpotc = document.getElementById(prefix+"tcodigo");
    var opts = gpotc.options;
    for (var i=0; i<opts.length; i++) {
        if (opts[i].value===itemVal) {
            console.log("Seleccionado: "+opts[i].text);
            break;
        }
    }
    resetForm();
}
function recalculaEmpresas() {
    conlog("INI recalculaEmpresas");
    xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("gpoSelectArea");
            elem.innerHTML=respTxt;
            conlog("REFRESH complete "+respTxt.length);
            // TODO: Encontrar nombres de proveedores faltantes para acompletarlos
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Grupo.php?selectorhtml=1&tipolista="+tipolista+"&defaultText=Selecciona una...",true);
    xmlhttp.send();
}
function recalculaProveedores() {
    conlog("INI recalculaProveedores");
    xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("prvSelectArea");
            elem.innerHTML=respTxt;
            conlog("REFRESH complete "+respTxt.length);
            // TODO: Encontrar nombres de proveedores faltantes para acompletarlos
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Proveedores.php?selectorhtml=1&tipolista="+tipolista,true);
    xmlhttp.send();
}
function ingresoAvance() {
    console.log("Ingreso Avance");
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
function checkAllChecks() {
    // document.getElementsByTag(input[checkbox]);
}
function getParentTable(elem) {
    if (!elem) return false;
    var parentNode = elem.parentNode;
    if (parentNode && parentNode.tagName.toLowerCase() === 'table') return parentNode;
    return getParentTable(parentNode);
}
function toggleHiddenArea(elemDivId,buttonElem,btnToShowVal,btnToHideVal,heightDif) {
    var elem = document.getElementById(elemDivId);
    if (elem) {
        var added = elem.classList.toggle("hidden");
        if (buttonElem) {
            if (added) buttonElem.value=btnToShowVal;
            else buttonElem.value=btnToHideVal;
        }
        if (added) {
            console.log("Hiding "+elemDivId);
            elem.style.height=null;
            elem.style.overflow=null;
            var curNode = elem;
            for (var parent=curNode.parentNode; parent && parent.nodeType==1; curNode=parent, parent=parent.parentNode) {
                console.log("Parent node tag="+parent.tagName+", type="+parent.nodeType);
                if (parent.hasAttribute("noHeight")) {
                    parent.style.height=null;
                    parent.removeAttribute("noHeight");
                }
            }
        } else {
            console.log("Showing "+elemDivId);
            if (heightDif && heightDif>0) elem.style.height="calc(100% - "+heightDif+"px)";
            else heightDif=0;
            elem.style.overflow="auto";
            
            var parent = elem.parentNode;
            var curNode = elem;
            for (var parent=curNode.parentNode; parent && parent.nodeType==1; curNode=parent, parent=parent.parentNode) {
                console.log("Parent node tag="+parent.tagName+(parent.id?", id="+parent.id:"")+", type="+parent.nodeType);
                if (!parent.style.height && curNode.offsetHeight > parent.offsetHeight) {
                    console.log("Not Parent Style Height ("+parent.style.height+") to 100%");
                    parent.style.height="100%";
                    parent.setAttribute("noHeight","1");
                } else console.log("Parent Style Height = '"+parent.style.height+"'");
            }
        }
    }
}
function isLoaded(txt) {
    //console.log("INI function isLoaded "+txt);
    let encProv = document.getElementById("encProveedor");
    if (encProv) {
        //console.log("encabezado Proveedor");
        let unEmi = document.getElementById("unEmisor");
        if (unEmi) {
            //console.log("Un Emisor: Oculta encabezado Proveedor");
            encProv.classList.add("hidden");
        } else {
            //console.log("No encontrado emi: Muestra encabezado Proveedor");
            encProv.classList.remove("hidden");
        }
    }
    let fwElems=document.getElementsByClassName("currency");
    let maxWidth=0;
    for(let i=0;i<fwElems.length;i++) maxWidth=Math.max(maxWidth,fwElems[i].offsetWidth);
    for(let i=0;i<fwElems.length;i++) fwElems[i].style.width=maxWidth+"px";

    tipReload();
}
function tipReload() {
    //console.log("INI function tipReload");
    let tipBlock = document.getElementsByClassName("itip");
    for (let i=0; i<tipBlock.length; i++) tipBlock[i].classList.add("tipHolding");
    let holdingElems = document.getElementsByClassName("tipHolding");
    //console.log("Num .tipHolding = "+holdingElems.length);
    clearTimeout(timeOutTipping);
    timeOutTipping=setTimeout(addStatusTooltip,10);
}
function addStatusTooltip() {
    //console.log("INI function addStatusTooltip");
    let holdingElems = document.getElementsByClassName("tipHolding");
    if (holdingElems.length>0) {
        clearTimeout(timeOutTipping);
        let tipElem=holdingElems[0];
        let rowElem=tipElem.parentNode.parentNode;
        let factId=rowElem.getAttribute("idf");
        //console.log("Procesando status de "+factId);
        let xmlhttp=ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            //console.log("State:"+xmlhttp.readyState+", Status:"+xmlhttp.status);
            if (xmlhttp.readyState!=4||xmlhttp.status!=200) return;
            //console.log("Status Tooltip: "+xmlhttp.responseText);
            let respArr = xmlhttp.responseText.split("|");
            if (respArr.length>0) {
                ekfil(tipElem);
                let tbl = ecrea({eName:"TABLE",className:"noApply fontSmall"});
                let thd = ecrea({eName:"THEAD",className:"panalBG"});
                let thtr = ecrea({eName:"TR"});
                thtr.appendChild(ecrea({eName:"TD",colspan:"3",className:"centered",eText:"ID : "+factId}));
                thd.appendChild(thtr);
                thtr = ecrea({eName:"TR"});
                let colnames = ["Fecha","Usuario","Status"];
                for(let c=0; c<3; c++)
                    thtr.appendChild(ecrea({eName:"TH",className:"pad2 brdr1d bbtm2d",eText:colnames[c]}));
                thd.appendChild(thtr);
                tbl.appendChild(thd);
                let tbd = ecrea({eName:"TBODY",className:"panalBGLight"});
                for(let i=0; i<respArr.length; i+=6) {
                    let tbtr = ecrea({eName:"TR"});
                    let fecha=respArr[i+1];
                    let vfecha=fecha.slice(0,10);
                    tbtr.append(ecrea({eName:"TD",className:"pad2 brdr1d",title:fecha,eText:vfecha}));
                    tbtr.append(ecrea({eName:"TD",className:"pad2 brdr1d usrNdNm",title:respArr[i+3],eText:respArr[i+2]}));
                    tbtr.append(ecrea({eName:"TD",className:"pad2 brdr1d",title:decodeURI(respArr[i+5]),eText:respArr[i+4]}));
                    tbd.appendChild(tbtr);
                }
                tbl.appendChild(tbd);
                tipElem.appendChild(tbl);
            }
            tipElem.classList.remove("tipHolding");
            timeOutTipping=setTimeout(addStatusTooltip,10);
        };
        xmlhttp.open("GET","consultas/Proceso.php?llave[]=identif&llave[]=modulo&fulldata&solicita=id,fecha,usuario,region,status,detalle&modulo=Factura&identif="+factId, true);
        xmlhttp.send();
    }
}
function dateIniSet() {
    console.log("function dateIniSet");
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(prev_month(day));
}
function dateEndSet() {
    console.log("function dateEndSet");
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(next_month(day));
}
function setFullMonth(date) {
    var firstDay = first_of_month(date);
    var lastDay = day_before(first_of_month(next_month(date)));
    var iniDateElem = document.getElementById("fechaInicio");
    var endDateElem = document.getElementById("fechaFin");
    iniDateElem.value = strftime(date_format, firstDay);
    endDateElem.value = strftime(date_format, lastDay);

    adjustCalMonImgs();
}
function adjustCalMonImgs(tgtWdgt) { // tgtWdgt always fechaInicio or undefined
    //console.log("INI function adjustCalMonImgs");
    const iniDateElem = ebyid("fechaInicio");
    const endDateElem = ebyid("fechaFin");

    const iniday = strptime(date_format, iniDateElem.value);
    const endday = strptime(date_format, endDateElem.value);
    const inimon = iniday.getMonth()+1;
    const endmon = endday.getMonth()+1;
    const sameyr = (iniday.getYear()===endday.getYear());

    let curday = iniday;
    let curmon = inimon;
    if (tgtWdgt===iniDateElem) {
        if (inimon!==endmon||!sameyr) {
            const lastDay = day_before(first_of_month(next_month(iniday)));
            endDateElem.value = strftime(date_format, lastDay);
        } else if (iniday>endday) {
            endDateElem.value = iniDateElem.value;
        }
    } else if (tgtWdgt===endDateElem) {
        //console.log("FECHA FIN RECIBIDA");
        curday = endday;
        curmon = endmon;
        if (inimon!==endmon||!sameyr) {
            const firstDay = first_of_month(endday);
            iniDateElem.value = strftime(date_format, firstDay);
        } else if (iniday>endday) {
            iniDateElem.value = endDateElem.value;
        }
    } else {
        //console.log("CALCULO DEL MES COMPLETO");
    }

    let prevMon = curmon-1;
    while(prevMon<1) prevMon+=12;
    let nextMon = curmon+1;
    while(nextMon>12) nextMon-=12;

    const prevClass = "calendar_month_"+padDate(prevMon);
    const nextClass = "calendar_month_"+padDate(nextMon);
    const calMonPrev = document.getElementById("calendar_month_prev");
    const calMonNext = document.getElementById("calendar_month_next");
    calMonPrev.className = prevClass;
    calMonNext.className = nextClass;
    //console.log("END function adjustCalMonImgs");
}
<?php
clog1seq(-1);
clog2end("scripts.generatxt");