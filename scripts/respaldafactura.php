<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.respaldafactura");
clog1seq(1);
$gpoRazSocOpt = $_SESSION['gpoRazSocOpt'];
$gpoCodigoOpt = $_SESSION['gpoCodigoOpt'];
$gpoRFCOpt = $_SESSION['gpoRFCOpt'];
$prvRazSocOpt = $_SESSION['prvRazSocOpt'];
$prvCodigoOpt = $_SESSION['prvCodigoOpt'];
$prvRFCOpt = $_SESSION['prvRFCOpt'];
function arr2JsonProps($array) {
    $script = "";
    $isFirst=true;
    foreach($array as $key=>$val) {
        if ($isFirst) $isFirst=false;
        else $script.=", ";
        $script .= "'$key': \"".$val."\"";
    }
    return $script;
}
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas");
$esAvance = validaPerfil("Avance");
?>
<?php if ($esAdmin) { ?>
window.onkeyup=function(event) {
    var ulists = $('ul.multiselect-container:visible');
    if (ulists.length) {
        var ulist=ulists[0];
        var btnGpDiv=ulist.parentNode;
        var selElem=btnGpDiv.previousElementSibling;
        var kyUp = event.key.toUpperCase();
        var kyTrCod = selElem.getAttribute("keyTraceCode");
        selElem.removeAttribute("keyTraceCode");
        
        var msg = false;
        
        var llabels=$("ul.multiselect-container:visible li a label");
        var llabel = llabels[0];
        var llink = llabel.parentNode;
        var llistline = llink.parentNode;
        if (event.which==189 || (event.which>=48 && event.which<=57) || (event.which>=65 && event.which<=90)) { // - dash, 0 - 9, A - Z
            var currActive = document.activeElement;
            var wlabels=false;
            if (kyTrCod) {
                kyTrCod = kyTrCod+kyUp;
                wlabels=$("ul.multiselect-container:visible li a label[title^='"+kyTrCod+"']");
                //console.log("kyTrCod "+kyTrCod);
            }
            if (!wlabels || wlabels.length<1) {
                kyTrCod = kyUp;
                selElem.setAttribute("keyTraceCode", kyUp);
                wlabels=$("ul.multiselect-container:visible li a label[title^='"+kyUp+"']");
                //console.log("Failed. kyTrCod "+kyTrCod);
            } else {
                selElem.setAttribute("keyTraceCode", kyTrCod);
                //console.log("Accepted in len="+wlabels.length);
            }
            if (wlabels.length<1) {
                //console.log("NO1 "+kyUp);
                selElem.removeAttribute("keyTraceCode");
                $("ul.multiselect-container:visible li:first a").focus();
                return false;
            }
            var wlabel = wlabels[0];
            if ($("ul.multiselect-container:visible li:first.active").length) {
                $('#'+selElem.id).multiselect('deselectAll', false);
                //console.log("deselectAll");
            }
            if (kyTrCod.length==1) {
                for (var l=0; l<wlabels.length; l++) {
                    var thislabel = wlabels[l];
                    if (currActive===thislabel.parentNode) {
                        if (event.shiftKey) {
                            if (l==0) wlabel=wlabels[wlabels.length-1];
                            else wlabel=wlabels[l-1];
                            //console.log("Moving to prev label "+wlabel.firstElementChild.value);
                        } else {
                            if ((l+1)==wlabels.length) wlabel=wlabels[0];
                            else wlabel=wlabels[l+1];
                            //console.log("Moving to next label "+wlabel.firstElementChild.value);
                        }
                        break;
                    }
                }
            }
            if (wlabel && wlabel.children.length<1) {
                //console.log("NO2 "+kyUp);
                selElem.removeAttribute("keyTraceCode");
                $("ul.multiselect-container:visible li:first a").focus();
                return false;
            }
            var wchkbx = wlabel.children[0];
            var wvalue = wchkbx.value;
            
            //ulists.multiselect('refresh');
            var wbtn = btnGpDiv.firstChild;
            // TODO: Cambiar texto del boton.
            
            var wlink = wlabel.parentNode;
            var wlistline = wlink.parentNode;
            ulist.scrollTop = (wlistline.offsetTop-50);
            // console.log(wlabel, ulist.scrollTop);
            wlabel.focus();
            wlink.focus();
            wlistline.focus();
            // $('#'+selElem.id).multiselect('select', wvalue); // No select, use space or enter
        } else {
            selElem.removeAttribute("keyTraceCode");
            switch(event.which) {
                case 27: // ESCape
                    $(document).click();
                    break;
                case 33: // RePag Anterior
                    var currHgtRP = ulist.clientHeight;
                    if (ulist.scrollTop>currHgtRP) ulist.scrollTop-=currHgtRP;
                    else ulist.scrollTop=0;
                    msg = "RePag: -"+currHgtRP+" = "+ulist.scrollTop;
                    break;
                case 34: // AvPag Siguiente
                    var ulist = $("ul.multiselect-container:visible")[0];
                    var currHgtAP = ulist.clientHeight;
                    if (ulist.scrollTop<(ulist.scrollHeight)) ulist.scrollTop+=currHgtAP;
                    msg = "AvPag: +"+currHgtAP+" = "+ulist.scrollTop;
                    break;
                case 35: // Fin
                    var lastLi = $("ul.multiselect-container:visible li:last")[0];
                    lastLi.focus();
                    ulist.scrollTop = lastLi.offsetTop-50;
                    msg = "Last: "+ulist.scrollTop;
                    break;
                case 36: // Inicio
                    var firstLi = $("ul.multiselect-container:visible li:first")[0];
                    firstLi.focus();
                    ulist.scrollTop = firstLi.offsetTop;
                    msg = "First: "+ulist.scrollTop;
                    break;
                default:  
                    msg = "KeyUp : ";
                    if (event.shiftKey) msg+="(shf)";
                    if (event.altKey) msg+="(alt)";
                    if (event.ctrlKey) msg+="(ctr)";
                    if (event.metaKey) msg+="(met)";
                    if (event.repeat) msg+="(rep)";
                    if (event.isComposing) msg+="[Cmps]";
                    msg+="ChCd="+event.charCode;
                    msg+=", code="+event.code;
                    msg+=", key="+kyUp;
                    msg+=", which="+event.which;
                    msg+=", kyCd="+event.keyCode;
                    msg+=", loc="+event.location;
            }
            if (msg) console.log(msg);
        }
    } else console.log("...mute...");
};
function generaFactura(factName,yearCycle,winname) {
    if (!winname) winname='_blank';
    var expUrl='templates/factura.php?nombre='+factName;
    if (yearCycle && yearCycle.length>0) expUrl+='&ciclo='+yearCycle;
    console.log(expUrl);
    window.open(expUrl,winname);
}
<?php } ?>
var xmlhttp=false;
<?php if ($esAdmin||$esSistemas||$esAvance) { ?>
function fixResult(respText,params,state,status) {
    if (state<4) return;
    if (state>4||status!=200) {
        console.log("State Error "+state+"|"+status+": ",params,respText,ebyid(params.target));
        return;
    }
    try {
        let response=JSON.parse(respText);
        if (response.result==="success") {
            let tgt=ebyid(params.target);
            tgt.classList.remove("wrong1BG");
            tgt.classList.add("right1BG");
            tgt.title="";
            ekfil(tgt.firstElementChild);
            let lnk=ecrea({eName:"A",href:params.filePath+params.newName+"."+params.extension,target:"archivo",eChilds:{eName:"IMG",src:"imagenes/icons/"+params.extension+"200.png",width : "32",height: "32"}});
            tgt.firstElementChild.appendChild(lnk);
        } else console.log(params,respText,ebyid(params.target));
    } catch (ex) { console.log("fixResult Exception: ",ex,", Text:"+respText); }
}
<?php } ?>
window.onload = onloadPack;
//if (window.onresize) {
//    var oldOnresize = window.onresize;
//    window.onresize = function (event) {
//        oldOnresize && oldOnresize();
//        backupOverlayAdjust();
//    }
//} else window.onresize=backupOverlayAdjust;
var entidadTipos = ["codigo", "rfc", "razon"];
var showGpo= false, showPrv=false;
var timeoutDownload=0;
var timeoutMaxRetries=10;
var timeoutRetries=0;
var timeOutTipping=0;

var gpoCod = { <?= arr2JsonProps($gpoCodigoOpt) ?> };
var gpoRzs = { <?= arr2JsonProps($gpoRazSocOpt) ?> };
var gpoRfc = { <?= arr2JsonProps($gpoRFCOpt) ?> };
var prvCod = { <?= arr2JsonProps($prvCodigoOpt) ?> };
var prvRzs = { <?= arr2JsonProps($prvRazSocOpt) ?> };
var prvRfc = { <?= arr2JsonProps($prvRFCOpt) ?> };

function onloadPack() {
    fillPaginationIndexes();
    // inputFileSetup();
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
    let encEmp =  document.getElementById("encEmpresa");
    if (encEmp) {
        //console.log("encabezado Empresa");
        let unRec = document.getElementById("unReceptor");
        if (unRec) {
            //console.log("Un Receptor: Oculta encabezado Empresa");
            encEmp.classList.add("hidden");
        } else {
            //console.log("No encontrado rec: Muestra encabezado Empresa");
            encEmp.classList.remove("hidden");
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
        if (xmlhttp) xmlhttp.abort();
        xmlhttp=ajaxRequest();
        xmlhttp.onreadystatechange = function() {
            //console.log("State:"+xmlhttp.readyState+", Status:"+xmlhttp.status);
            if (xmlhttp.readyState!=4||xmlhttp.status!=200) return;
            let respArr = xmlhttp.responseText.split("|");
            if (respArr.length>0) {
                ekfil(tipElem);
                let tbl = ecrea({eName:"TABLE",className:"noApply fontSmall"});
                let thd = ecrea({eName:"THEAD",className:"panalBG"});
                let thtr = ecrea({eName:"TR"});
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
                    tbtr.append(ecrea({eName:"TD",className:"pad2 brdr1d",title:respArr[i+5],eText:respArr[i+4]}));
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
<?php if (false) { ?>
function readTARFile(event) {
    console.log("INI function readTARFile( event = "+event+" )");
    var fileToLoad = event.srcElement.files[0];
    if (fileToLoad != null) {
        var fileName = fileToLoad.name;
        var fileExt = fileName.split( '\.' ).pop().toLowerCase();
        if (fileExt === "tar") {
            overlayMessage("<p>... Procesando archivo "+fileName+"...</p>", "Verificación de Respaldo de Facturas"); // TODO: Considerar si vale la pena crear una funcion overlayElement, equivalente a overlayMessage pero recibe un elemento DOM como mensaje
            FileHelper.loadFileAsBinaryString(fileToLoad, null, processTARFile);
        } else {
            overlayMessage("<p>El archivo debe tener formato TAR y contener facturas en formato XML</p>", "ERROR: Archivo inválido");
        }
    } else {
        overlayMessage("<p>Debe seleccionar un archivo de respaldo de facturas con formato TAR</p>", "ERROR: Sin archivo");
    }
}
function processTARFile(fileToLoad, fileAsBinaryString) {
    console.log("INI function processTARFile(fileToLoad = "+fileToLoad+", fileAsBinaryString, this="+this+")");
    var fileName = fileToLoad.name;
    var fileAsBytes = ByteHelper.stringUTF8ToBytes(fileAsBinaryString);
    var tarFile = TarFile.fromBytes(fileName, fileAsBytes);
    Globals.Instance.tarFile = tarFile;

    var displayElement = buildDOM4TARFile(tarFile);
    if ('title' in displayElement) {
        var dtdiv = document.getElementById("dialog_title");
        if (dtdiv) {
            dtdiv.innerHTML = displayElement.title;
        }
        // cachar area de titulo
    }
    { // TODO: Verificar si fuera necesario meter este bloque en una funcion con settimeout para dar tiempo a overlaymessage de crear la ventana de dialogo 
        var dra = document.getElementById("dialog_resultarea");
        if (dra) {
            if (dra.style && dra.style.height) dra.style.height=null;
            dra.innerHTML = "";
            dra.appendChild(displayElement);
            console.log("Resultado del proceso anexado al area de dialogo");
        } else {
            console.log("El area de dialogo ya no está a la vista");
        }
    }
}
function makeErrorElement(message) {
    var returnValue = document.createElement("DIV");
    returnValue.classList.add("centered");
    var prgrph = document.createElement("P");
    prgrph.appendChild(document.createTextNode(message));
    returnValue.appendChild(prgrph);
    returnValue.title = "ERROR";
    return returnValue;
}
function buildDOM4TARFile(tarFile) {
    var returnValue = document.createElement("DIV");
    returnValue.classList.add("centered");
    var prgrph = document.createElement("P");
//    prgrph.appendChild(document.createTextNode("XML/PDF VERIFIED CONTENT"));
//    returnValue.appendChild(prgrph);
//    prgrph = document.createElement("P");
    var boldElem = document.createElement("B");
    boldElem.appendChild(document.createTextNode(tarFile.fileName));
    prgrph.appendChild(boldElem);
    returnValue.appendChild(prgrph);
    prgrph = document.createElement("P");
//    prgrph.appendChild(document.createTextNode("Contiene "+tarFile.entries.length+" archivos"));
//    returnValue.appendChild(prgrph);
//    var block = document.createElement("DIV");
//    block.classList.add("top_area");
//    var list = document.createElement("OL");
//    list.classList.add("lefted");
    var numTot = 0;
    var numXML = 0;
    var numPDF = 0;
    var perSite = {};
    var perSiteLen = 0;
    for (var i = 0; i < tarFile.entries.length; i++) {
        var entry = tarFile.entries[i];
        
        var entryHeader = false;
        if ('header' in entry && entry.header) entryHeader = entry.header;
        else return makeErrorElement("El archivo esta corrupto, dañado o no tiene formato válido. (Error en entry.header)");
        
        var filename = false;
        if ('fileName' in entryHeader && entryHeader.fileName) filename = entryHeader.fileName;
        else return makeErrorElement("El archivo esta corrupto, dañado o no tiene formato válido. (Error en entryHeader.fileName)");
        
        var splitname = filename.split(/[\/\.]/);
        var splitlen = splitname.length;
        
        var filetype = false;
        if ('typeFlag' in entryHeader && entryHeader.typeFlag) {
            if ('name' in entryHeader.typeFlag && entryHeader.typeFlag.name) filetype = entryHeader.typeFlag.name;
            else return makeErrorElement("El archivo esta corrupto, dañado o no tiene formato válido. (Error en entryHeader.typeFlag.name)");
        } else return makeErrorElement("El archivo esta corrupto, dañado o no tiene formato válido. (Error en entryHeader.typeFlag)");
        
        var filesize = false;
        if ('fileSizeInBytes' in entryHeader && entryHeader.fileSizeInBytes) filesize = entryHeader.fileSizeInBytes;
        else return makeErrorElement("El archivo esta corrupto, dañado o no tiene formato válido. (Error en entryHeader.fileSizeInBytes)");
//        var line = document.createElement("LI");
//        line.appendChild(document.createTextNode(splitname[1]+": "+splitname[2]+" ["+splitname[3]+"] "+human_filesize(filesize)));
//        list.appendChild(line);
        numTot++;
        var fileext = splitname[splitlen-1].toLowerCase();
        if (fileext==="xml") {
            numXML++;
            if (splitname[1] in perSite) perSite[splitname[1]]++;
            else {
                perSite[splitname[1]] = 1;
                perSiteLen++;
            }
        } else if (fileext==="pdf") numPDF++;
    }
    if (numXML==0) {
        prgrph.appendChild(document.createTextNode("El documento no contiene facturas en formato XML"));
        returnValue.appendChild(prgrph);
    } else {
        prgrph.appendChild(document.createTextNode(numXML+" factura"+(numXML==1?"":"s")+" (XML) en "+perSiteLen+" empresa"+(perSiteLen==="1"?"":"s")+"."));
        returnValue.appendChild(prgrph);
        for (var prop in perSite) {
            if (perSite.hasOwnProperty(prop)) {
                prgrph = document.createElement("P");
                prgrph.appendChild(document.createTextNode(" • "+prop+" : "+perSite[prop]+" factura"+(perSite[prop]==1?"":"s")+".")); // &bull; o &bullet;
                returnValue.appendChild(prgrph);
            }
        }
    }
//    block.appendChild(list);
//    returnValue.appendChild(block);
    return returnValue;
}
function inputFileSetup() {
    console.log("INI function inputFileSetup()");
    var inputs = document.querySelectorAll( '.inputfile' );
    Array.prototype.forEach.call( inputs, function( input ) {
        var label     = input.nextElementSibling,
        labelVal = label.innerHTML;
        if (false) {
        input.addEventListener( 'change', readTARFile);

        input.addEventListener( 'change', function( e ) {
            var fileName = '';
            if( this.files && this.files.length > 1 )  fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
            else                                       fileName = e.target.value.split( '\\' ).pop();
            if( fileName ) {
                label.querySelector( 'strong' ).innerHTML = fileName;
                var tarMessage = "";
                //TODO: Obtener lista de ids actualmente en busqueda
                var idList = getFacturas();
                //TODO: Validar que el archivo sea tar y que tenga la estructura y archivos generados al respaldar
                var tgt = e.target;
                var tgtVal = tgt.value;
                if ('files' in tgt) {
                    if (tgt.files.length == 0) tarMessage += "<p>Error: Falta seleccionar un archivo</p>";
                    else {
                        tarMessage += "Lista de archivos seleccionados: <table class='noApply shrinkCol centered'><thead><th>fullname</th><th>name</th><th>type</th><th>size</th></thead><tbody>";
                        var fullnames = tgtVal.split(",");
                        for (var i=0; i<tgt.files.length; i++) {
                            var file = tgt.files[i];
                            tarMessage += "<tr><td>"+fullnames[i]+"</td><td>";
                            if ('name' in file) tarMessage += file.name;
                            else tarMessage += "ERR: No name";
                            tarMessage += "</td><td>";
                            var typeInfo = false;
                            if ('type' in file) {
                                tarMessage += file.type;
                                if (file.type !== "application/x-tar" && file.type !== "application/tar")
                                    typeInfo = "El archivo no es un respaldo de facturas";
                            } else {
                                typeInfo = "No fue posible obtener el tipo de archivo de "+file.name;
                                tarMessage += "ERR: No type";
                            }
                            if (typeInfo) {
                                overlayMessage("<p>"+typeInfo+"</p>", "Verificador de archivo de respaldo TAR");
                                return;
                            }
                            tarMessage += "</td><td>";
                            if ('size' in file) tarMessage += file.size;
                            else tarMessage += "ERR: No size";
                            tarMessage += "</td></tr>";
                        }
                        tarMessage += "</tbody></table>";
                    }
                }
                //TODO: Descomprimir tar, de ser posible sin guardar los archivos, solo para analizarlos
                //TODO: Para cada archivo, aumentar contador de extension (Inicializar un contador de XML y otro de PDF en cero) y aumentar contador de extension por empresa. Con el nombre del archivo obtener id de la tabla factura y descartar de la lista de ids si se tiene.
                //TODO: Desplegar información: Total de XMLs, Total de XMLs desglosados por empresa, Total de PDFs, Total de PDFs desglosados por empresa, Si existe lista y tiene id's no descartados mostrar lista de ids no descartados (opt. obtener empresa, proveedor y folio de la base de datos, si es que existe o indicar si no existiera)
                if (idList) {
                    var idArr = idList.split(",");
                    tarMessage += "Lista de Ids de facturas procesadas: <div class='shrinkCol'><ul class='lefted'><li>"+idArr.join("</li><li>")+"</li></ul></div>";
                } else tarMessage += "Sin lista de Ids.";
                overlayMessage("<p>"+tarMessage+"</p>","Verificador de archivo de respaldo TAR");
            } else
                label.innerHTML = labelVal;
        });
        }
    });
}
<?php } ?>
function pickType(elem) {
    console.log("INI function pickType ",elem);
    if (!elem || !elem.value) { return; }
    var gpoValues = $('#gpotcodigo').val() || []; // SAVE LIST OF SELECTED VALUES
    var prvValues = $('#prvtcodigo').val() || [];
    var gpoView = $('#gpotcodigo').attr("view"); // CUSTOM ATTRIB, TO AVOID UNNECESSARY CHANGES FOR DUMB CLICKS
    var prvView = $('#prvtcodigo').attr("view");
    if (gpoView===elem.value || prvView===elem.value) return; // SAME RADIO BUTTON CLICKED TWICE
    $('#gpotcodigo').attr("view",elem.value); // SET NEW CUSTOM ATTRIB VALUE
    $('#prvtcodigo').attr("view",elem.value);
    $('#gpotcodigo')[0].options.length=0; // DELETE ALL OPTIONS
    $('#prvtcodigo')[0].options.length=0;
    var gpoList = false; // INITIAL OPTION LIST ASSIGNMENT
    var prvList = false;
    switch(elem.value) { // ASSIGN SELECTED OPTION LIST
        case "tcodigo": gpoList=gpoCod; prvList=prvCod; break; // SELECTED TO VIEW LIST OF ALIASES
        case "trfc": gpoList=gpoRfc; prvList=prvRfc; break;    // SELECTED TO VIEW LIST OF RFC'S
        case "trazon": gpoList=gpoRzs; prvList=prvRzs; break;  // SELECTED TO VIEW LIST OF BUSINESS NAMES
    } // SHOULD NOT EXIST OTHER POSSIBLE VALUES, IF MANUPULATED IF FAILS TO NO DATA
    for(var p in gpoList) {
        if (gpoList.hasOwnProperty(p)) {
            $('#gpotcodigo').append($('<option>', { value: p, text: gpoList[p] })); // CREATE EACH UPDATED OPTION
        }
    }
    for(var p in prvList) {
        if (prvList.hasOwnProperty(p)) {
            $('#prvtcodigo').append($('<option>', { value: p, text: prvList[p] })); // CREATE EACH UPDATED OPTION
        }
    }
    var fixedOptions = { gpo:{ numberDisplayed: 3, buttonWidth: '220px' },
                         prv:{ numberDisplayed: 4, buttonWidth: '220px' }};
    switch(elem.value) {
        case "trfc":
            fixedOptions["gpo"]["numberDisplayed"] = fixedOptions["prv"]["numberDisplayed"] = 2;
            break;
        case "trazon":
            fixedOptions["gpo"]["numberDisplayed"] = fixedOptions["prv"]["numberDisplayed"] = 1;
            fixedOptions["gpo"]["buttonWidth"]     = fixedOptions["prv"]["buttonWidth"]     = "300px";
            break;
    }
<?php if ($esAdmin) { ?>
   	$('#gpotcodigo').multiselect('setOptions', fixedOptions["gpo"]);
   	$('#prvtcodigo').multiselect('setOptions', fixedOptions["prv"]);
<?php } ?>
    // SET OPTIONS PREVIOUSLY SELECTED 
    for(var i=0; i<gpoValues.length; i++) $('#gpotcodigo option[value="'+gpoValues[i]+'"]').attr('selected','selected');
    for(var i=0; i<prvValues.length; i++) $('#prvtcodigo option[value="'+prvValues[i]+'"]').attr('selected','selected');
<?php if ($esAdmin) { ?>
    $('#gpotcodigo').multiselect('rebuild'); // REDRAW UPDATED HTML SELECT
   	$('#prvtcodigo').multiselect('rebuild'); // REDRAW UPDATED HTML SELECT
<?php } ?>
}

<?php if ($esAdmin) { ?>
// JQUERY USING SECTION
    $(function() {
        $('#gpotcodigo').multiselect({
            selectAllText: 'Todas',
            nonSelectedText: 'Selecciona una...',
            nSelectedText: ' seleccionadas',
            includeSelectAllOption: true,
            numberDisplayed: 3,
            allSelectedText: 'Todas',
            selectAllNumber: false,
            buttonClass: 'multiselect-custom-btn btn btn-default',
            maxHeight: 200,
            onChange: resetForm,
            onSelectAll: resetForm,
            buttonWidth: '220px',
            buttonTitle: function(options, select) {
                if (options.length==$(select).find('option').length)
                    return this.selectAllText;
                else if (options.length === 0) {
                    return this.nonSelectedText;
                } else {
                    var selected = '';
                    var delimiter = this.delimiterText;

                    options.each(function () {
                        var label = ($(this).attr('label') !== undefined) ? $(this).attr('label') : $(this).text();
                        selected += label + delimiter;
                    });
                    return selected.substr(0, selected.length - this.delimiterText.length);
                }
            }
/*
            ,
            buttonText: function(options, select) {
                var maxLen = 36;
                var retVal = '';
                 console.log("options: "+options.length+" select.options: "+$(select).find('option').length);
                if (options.length === 0) {
                    retVal = 'Selecciona una...';
                } else if (options.length > 3) {
                    if (options.length == $(select).find('option').length)
                        retVal = "Todas";
                    else retVal = options.length+' seleccionadas';
                } else {
                     options.each(function() {
                         if (options.length==1||retVal.length<=(maxLen-6)) {
                             if (retVal.length>0) retVal+=', ';
                             if ($(this).attr('label') !== undefined) {
                                 retVal += ''+$(this).attr('label');
                             } else {
                                 retVal += ''+$(this).html();
                             }
                             if (retVal.length>maxLen) {
                                 retVal = retVal.slice(0,(maxLen-3))+"...";
                             }
                         } else {
                             if (options.length == $(select).find('option').length)
                                 retVal = "Todas";
                             else retVal = options.length+' seleccionadas';
                             return false; // si no es la última pero ya llegó al tamaño máximo
                         }
                     });
                 }
                 return retVal;
            }
*/
        });
        $('#prvtcodigo').multiselect({
            selectAllText: 'Todos',
            nonSelectedText: 'Selecciona uno...',
            nSelectedText: ' seleccionados',
            includeSelectAllOption: true,
            numberDisplayed: 4,
            allSelectedText: 'Todos',
            selectAllNumber: false,
            buttonClass: 'multiselect-custom-btn btn btn-default',
            maxHeight: 200,
            onChange: function($option, $checked) { resetForm(); },
            onSelectAll: resetForm,
            buttonWidth: '220px',
            buttonTitle: function(options, select) {
                if (options.length==$(select).find('option').length)
                    return this.selectAllText;
                else if (options.length === 0) {
                    return this.nonSelectedText;
                } else {
                    var selected = '';
                    var delimiter = this.delimiterText;
                    var maxCount=100;
                    var count=0;
                    options.each(function () {
                        var label = ($(this).attr('label') !== undefined) ? $(this).attr('label') : $(this).text();
                        selected += label + delimiter;
                        count++;
                        if (count>=maxCount) {
                            // TODO: agregar ... al final, solo si hay mas de maxCount elementos
                            return false;
                        }
                    });
                    return selected.substr(0, selected.length - this.delimiterText.length); // TODO: o checar aqui si options.length es mayor agregar .... Tambien checar q valor tiene delimiterText y para que sirve
                }
            }
 /*
            ,
            buttonText: function(options, select) {
                var maxLen = 36;
                var retVal = '';
                 console.log("options: "+options.length+" select.options: "+$(select).find('option').length);
                if (options.length === 0) {
                    retVal = 'Selecciona uno...';
                } else if (options.length > 3) {
                    if (options.length == $(select).find('option').length)
                        retVal = "Todos";
                    else retVal = options.length+' seleccionados';
                } else {
                     options.each(function() {
                         if (options.length==1||retVal.length<=(maxLen-6)) {
                             if (retVal.length>0) retVal+=', ';
                             if ($(this).attr('label') !== undefined) {
                                 retVal += ''+$(this).attr('label');
                             } else {
                                 retVal += ''+$(this).html();
                             }
                             if (retVal.length>maxLen) {
                                 retVal = retVal.slice(0,(maxLen-3))+"...";
                             }
                         } else {
                             if (options.length == $(select).find('option').length)
                                 retVal = "Todos";
                             else retVal = options.length+' seleccionados';
                             return false; // si no es la última pero ya llegó al tamaño máximo
                         }
                     });
                 }
                 return retVal;
            }
*/
        });
    });
// ENDS JQUERY USING SECTION
<?php } ?>
function keytracker(event) {
    var msg = "";
    if (event.shiftKey) msg+="(shf)";
    if (event.altKey) msg+="(alt)";
    if (event.ctrlKey) msg+="(ctr)";
    if (event.metaKey) msg+="(met)";
    if (event.repeat) msg+="(rep)";
    if (event.isComposing) msg+="[Cmps]";
    msg+="ChCd="+event.charCode;
    msg+=", code="+event.code;
    msg+=", key="+event.key;
    msg+=", which="+event.which;
    msg+=", kyCd="+event.keyCode;
    msg+=", loc="+event.location;
    console.log("keyMsg : "+msg);
}
function buscaFacturas() {
    document.forms["repfactform"].command.value="Buscar";
}

var _forma_submitted = false;
function resetForm() {
    var rvw = document.getElementById("countBackup");
    if (rvw) while(rvw.firstChild) rvw.removeChild(rvw.firstChild);
    rvw = document.getElementById("unCountBackup");
    if (rvw) while(rvw.firstChild) rvw.removeChild(rvw.firstChild);
    var initHidden = document.getElementsByClassName("initHidden");
    for (var i=0; i<initHidden.length; i++) initHidden[i].classList.add("hidden");
    var initVisible = document.getElementsByClassName("initVisible");
    for (var i=0; i<initVisible.length; i++) initVisible[i].classList.remove("hidden");
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
    console.log("Success Callback"+(isPartialResponse?" Partial":"")+"! "+(xmlHttpPost&&xmlHttpPost.length?xmlHttpPost.length:0));
    var formName = 'repfactform';
    var formElem = document.getElementById(formName);
    if  (isPartialResponse) {
        //fee(lbycn("successHidden"),function(elem){cladd(elem,"hidden")});
        //console.log("");
    } else if (formElem.command.value.substr(0,5)==="Busca") {
        let ctrlCheck = ebyid("controlCheck");
        if (ctrlCheck) ctrlCheck.checked=true;
        let aRespaldar = document.getElementsByClassName("facturasARespaldar");
        let num = (aRespaldar?aRespaldar.length:0);
        console.log("Command Encontradas : "+num);
        if (!isPartialResponse) {
            var rvw = document.getElementById("countBackup");
            if (rvw) {
                ekfil(rvw);
                rvw.appendChild(document.createTextNode(num+" encontrada"+(num==1?"":"s")));
                rvw.appendChild(document.createTextNode(" \u00A0"));
            }
            rvw = document.getElementById("unCountBackup");
            if (rvw) {
                ekfil(rvw);
                rvw.appendChild(document.createTextNode(num+" encontrada"+(num==1?"":"s")));
                rvw.appendChild(document.createTextNode(" \u00A0"));
            }
        }
        if (num>0 || (!xmlHttpPost && isPartialResponse)) {
            var elements = document.getElementsByClassName("datatable");
            for (var i=0; i<elements.length; i++) {
                elements[i].classList.remove("hidden");
            }
            var elements = document.getElementsByClassName("undatatable");
            for (var i=0; i<elements.length; i++) {
                elements[i].classList.add("hidden");
            }
            fee(lbycn("successHidden"),function(elem){cladd(elem,"hidden")});
        } else if (xmlHttpPost && !isPartialResponse) {
            var initHidden = document.getElementsByClassName("initHidden");
            for (var i=0; i<initHidden.length; i++) initHidden[i].classList.add("hidden");
            var initVisible = document.getElementsByClassName("initVisible");
            for (var i=0; i<initVisible.length; i++) initVisible[i].classList.remove("hidden");
        }
    }
    console.log("End Success Callback");
}
function getFacturas() {
    let idList = false;
    let aRespaldar = document.getElementsByClassName("facturasARespaldar");
    if (aRespaldar && aRespaldar.length>0) {
        idList="";
        let added=0;
        for (let i=0; i<aRespaldar.length; i++) {
            let idf=aRespaldar[i].getAttribute("idf");
            let ichk=ebyid("invcheck"+idf);
            if(ichk && !ichk.checked) continue;
            if(added>0) idList+=",";
            idList+=aRespaldar[i].getAttribute("idf");
            added++;
        }
    }
    return idList;
}
function getSortOrder(name) {
    var sortOrderType = document.getElementById("sortOrderType");
    var sortOrderValue = document.getElementById("sortOrderValue");
    if (!sortOrderType) {
        sortOrderType = document.createElement("INPUT");
        sortOrderType.type="hidden";
        sortOrderType.id="sortOrderType";
        sortOrderType.name="sortOrderType";
        sortOrderType.value=name;
        sortOrderValue = document.createElement("INPUT");
        sortOrderValue.type="hidden";
        sortOrderValue.id="sortOrderValue";
        sortOrderValue.name="sortOrderValue";
        sortOrderValue.value="Asc";
        var form = document.forms['repfactform'];
        form.appendChild(sortOrderType);
        form.appendChild(sortOrderValue);
        return "Asc";
    } else {
        if (sortOrderType.value===name && sortOrderValue.value==="Asc")
            sortOrderValue.value="Desc";
        else {
            sortOrderType.value=name;
            sortOrderValue.value="Asc";
        }
        return sortOrderValue.value;
    }
}
function submitAjax(submitValue, callbackfunc) {
    if (!callbackfunc) callbackfunc=submitCallbackSuccessFunc;
    console.log("submitAjax: "+submitValue);
    resetForm();
    var postURL = 'selectores/respaldafactura.php';
    var formName = 'repfactform';
    var resultDiv = 'dialog_tbody';
    var waitingHtml = false;// '<tr><td colspan=\'12\' class=\'centered\'><img src=\'<?=$waitImgName?>\' width=\'360\' height=\'360\'></td></tr>';
    clrem(ebyid("waitingRoll"),"hidden");
    var formElem = document.forms[formName];
    formElem.command.value = submitValue;
    console.log("to ajaxPost: "+postURL+", "+formName+", "+resultDiv+". Command="+formElem.command.value);
    const prvList = ebyid('prvtcodigo');
    const options = prvList.options;
    let selcount = 0;
    for (let i=0; i < options.length; i++) {
        if (options[i].selected) selcount++;
    }
    if (selcount==options.length) {
        prvList.selectedIndex=-1;
        // prvList.value="";
    }
    document.getElementById(resultDiv).innerHTML = ""; // waitingHtml;
    try {
        if (xmlhttp) xmlhttp.abort();
        xmlhttp=ajaxPost(postURL, formName, resultDiv, waitingHtml, callbackfunc);
    } catch (ex) {
        console.log("EXCEPCION= ",ex);
    }
    console.log("submitAjax DONE!");
    return false;
}
function respaldoDirecto() {
    console.log("function respaldoDirecto"); //appendLog("INI | respaldoDirecto\n");
    clearTimeout(timeOutTipping);
    var idFacturas = getFacturas();
    if (!idFacturas||idFacturas.length==0) {
        overlayMessage("Debe marcar al menos una factura para Respaldar","Error");
        return;
    }
    var rspUrl='consultas/Facturas.php?modo=directo&respaldar='+idFacturas;
    console.log(rspUrl);
    overlayMessage("<br><div id=\"xmlHttpStatus\"><img src=\"imagenes/ledoff.gif\"></div><div id=\"waitWheel\" class=\"centered\"><img src=\"<?=$waitImgName?>\"></div>", "Detalle");
    var dra = document.getElementById("dialog_resultarea");
    resetForm();
    if (xmlhttp) xmlhttp.abort();
    xmlhttp = ajaxRequest();
    xmlhttp.partOffset = 0;
    xmlhttp.sequenceCalls = 0;
    xmlhttp.onerror = function(error) { console.log("#RD# ERROR EN AJAX REQUEST (state="+xmlhttp.readyState+",status="+xmlhttp.status+"):",error); }
    xmlhttp.onreadystatechange = function() {
        console.log("AJAX | respaldoDirecto.onreadystatechange state:"+xmlhttp.readyState+", status:"+xmlhttp.status+".\n");
        var statusDiv = document.getElementById("xmlHttpStatus");
        if (statusDiv && statusDiv.firstElementChild.src) { // readyState == 0 : UNSENT
            if (xmlhttp.readyState==1) statusDiv.firstElementChild.src = "imagenes/ledred.gif"; // OPENED
            else if (xmlhttp.readyState==2) statusDiv.firstElementChild.src = "imagenes/ledorange.gif"; // HEADERS_RECEIVED
            else if (xmlhttp.readyState==3) statusDiv.firstElementChild.src = "imagenes/ledyellow.gif"; // LOADING
            else if (xmlhttp.readyState==4) statusDiv.firstElementChild.src = "imagenes/ledgreen.gif"; // DONE
        }
        var numChld = 0;
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            xmlhttp.sequenceCalls++;
            var result = xmlhttp.responseText.slice(xmlhttp.partOffset);
            var iniOff = result.lastIndexOf("<!-- START -->");
            if (iniOff<0) { iniOff=0; result="";
            } else result = result.slice(iniOff);
            //appendLog("AJAX4 | SEQ:"+xmlhttp.sequenceCalls+", PARTOFFSET:"+xmlhttp.partOffset+"\nRESULT:"+result.replace(/</g,"&lt;").replace(/>/g,"&gt;")+"\n");
            submitCallbackSuccessFunc(false);
            console.log("#RD# Respuesta de Respaldo Directo Recibida "+xmlhttp.sequenceCalls+" ("+result.length+")");
            if (result && result.length>0 && dra) {
                console.log("#RD# Has Result.");
                dra.innerHTML = result+getLoadableImgScript("backupOverlayAdjust();var rtl=document.getElementById('resptestlist');if (rtl) { console.log('#RD# Has Elem'); fixDetailTitleOnFinishedReception(false); rtl.scrollTop=rtl.scrollHeight-rtl.clientHeight; } else { console.log('#RD# Not found element with id=resptestlist!'); }");
            } else {
                console.log("#RD# No Result!");
                // ToDo. Marcar error unicamente si la lectura anterior parcial contenía un error. Aquí puede terminar satisfactoriamente sin texto.
                //dra.innerHTML = "<p>Ocurri&oacute; un error durante el respaldo de archivos. Por favor contacte a su administrador.</p>";
                // TODO: Leer titulo. Si dice "Respaldo en Progreso (/d "
                fixDetailTitleOnFinishedReception(false);
            }
            timeOutTipping=setTimeout(addStatusTooltip,10);
        } else if (xmlhttp.readyState == 3) {
            xmlhttp.sequenceCalls++;
            let part = xmlhttp.responseText.slice(xmlhttp.partOffset);
            let endOff = part.lastIndexOf("<!-- END -->");
            let iniOff = part.lastIndexOf("<!-- START -->",endOff);
            endOff+=12;

            let logMsg="SEQ="+xmlhttp.sequenceCalls+", RespLen="+xmlhttp.responseText.length+", PartOffset="+xmlhttp.partOffset+", PartLen="+part.length+", IniOffset="+iniOff+", EndOffset="+endOff;
            if (iniOff<0 || endOff<=iniOff) {
                logMsg+=", INVALID PART=";
                if (part.length<=500) logMsg+="'"+part+"'";
                else logMsg+="'"+part.slice(0,350)+"..."+part.slice(-150)+"'";
                part="";
                xmlhttp.partOffset += part.length;
            } else {
                xmlhttp.partOffset += endOff;
                if (iniOff>0) {
                    let cut = part.slice(0,iniOff).trim();
                    if (cut.length>0) {
                        logMsg+=", Cut=";
                        if (cut.length<=500) logMsg+="'"+cut+"'";
                        else logMsg+="'"+cut.slice(0,350)+"..."+cut.slice(-150)+"'";
                    }
                }
                part = part.slice(iniOff+14,endOff-12);
                logMsg+=", NewPartLen="+part.length+", Part=";
                if (part.length<=500) logMsg+="'"+part+"'";
                else logMsg+="'"+part.slice(0,350)+"..."+part.slice(-150)+"'";
            }
            console.log("#RD# Respuesta de Respaldo Directo Parcial "+logMsg);
            if (part && part.length>0 && dra) dra.innerHTML = part+getLoadableImgScript("backupOverlayAdjust();fixDetailTitleOnFinishedReception(true);");
        }
    };
    xmlhttp.open("GET", rspUrl, true);
    xmlhttp.send();
}
function fixDetailTitleOnFinishedReception(isPart) {
    let titleElem = document.getElementById("backupDetailTitle");
    if (titleElem) {
        let ttl = titleElem.textContent;
        if (ttl!=="Respaldo Completo" && ttl!=="Respaldo No definido" && ttl.slice(0,19)!=="Respaldo Incompleto" && (!isPart || ttl.slice(0,20)!=="Respaldo en Progreso")) {
            if (ttl.slice(0,8)==="Respaldo") {
                let reg = /Respaldo[A-Za-z ]+\((\d+) \/ (\d+)\)/;
                let result = ttl.match(reg);
                let resultOfResult = (result && result[1] && result[2]);
                if (resultOfResult) {
                    if (result[1]===result[2]) { resultOfResult="Respaldo Completo"; }
                    else { resultOfResult="Respaldo "+(isPart?"en Progreso":"Incompleto")+" ("+result[1]+" / "+result[2]+")"; }
                } else {
                    console.log("Title doesn't correspond to expected '"+ttl+"'");
                    resultOfResult="Respaldo No definido";
                }
                while(titleElem.firstChild) titleElem.removeChild(titleElem.firstChild);
                titleElem.appendChild(document.createTextNode(resultOfResult));
            } else console.log("Already fixed backupDetailTitle content: "+ttl);
        } else console.log("Not known backupDetailTitle content: "+ttl);
    } else console.log("Not found 'backupDetailTitle' HtmlElement");
}
function backupOverlayAdjust() {
    var ovr = document.getElementById("overlay");
    var dra = document.getElementById("dialog_resultarea");
    var rtl = document.getElementById("resptestlist");
    
    if (dra && rtl) {
        var invLines = rtl.querySelectorAll("table>tbody>tr>td>ol>li");
        var chkLines = rtl.querySelectorAll("table>tbody>tr>td>ul>li");
    
        // heights:
        var ovr_dra_dif = 102; // ovr-dbx=44, dbx-dra=54, dra.borderTop=2, dra.borderBottom=2
        var dra_rtl_dif = 30; // dra-rtl=30
        var rtl_row_hgt = 17; // for each element in invLines or chkLines
        var invMargins = 27; // invLines.marginTop=17, invLines.marginBottom=10
        var chkMargins = 50; // chkTitle=30, chkTitle.marginBottom=10, chkLines.marginBottom=10
    
        var calcHeight = invMargins + (rtl_row_hgt * invLines.length);
        if (chkLines && chkLines.length>0) calcHeight += chkMargins + (rtl_row_hgt * chkLines.length);
        var maxDraHgt = ovr.offsetHeight-ovr_dra_dif;
        var maxRtlHgt = maxDraHgt-dra_rtl_dif;
    
        if (calcHeight>maxRtlHgt) {
            rtl.style.height = maxRtlHgt+"px";
            dra.style.height = maxDraHgt+"px";
        } else {
            rtl.style.height = null;
            dra.style.height = null;
        }
    }
}
function getOuterHeight(elem) {
    var offHgt = elem.offsetHeight;
    var elmHgt = 0;
    var elMrgn = 0;
    if (document.all) {// IE
        elmHgt = parseInt(elem.currentStyle.height, 10);
        elMrgn = parseInt(elem.currentStyle.marginTop, 10) + parseInt(elem.currentStyle.marginBottom, 10);
    } else { // Mozilla
        elmHgt = parseInt(document.defaultView.getComputedStyle(elem, '').getPropertyValue('height'),10);
        elMrgn = parseInt(document.defaultView.getComputedStyle(elem, '').getPropertyValue('margin-top')) + parseInt(document.defaultView.getComputedStyle(elem, '').getPropertyValue('margin-bottom'));
    }
    if (offHgt>elmHgt) elmHgt=offHgt;
    return elmHgt+elMrgn;
}
function notificarDescarga(xmlHttpPost, isPartialResponse) {
    console.log("function notificarDescarga"+(isPartialResponse?" Parcial":""));
    if(isPartialResponse) {
        return;
    }
    submitCallbackSuccessFunc(false);
    timeoutDownload=0;
    overlayMessage("<p>Se descarg&oacute; el respaldo de facturas satisfactoriamente.</p>", "Descarga Realizada!");
}
function recalculaEmpresas() {
    if (xmlhttp) xmlhttp.abort();
    xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("gpoSelectArea");
            elem.innerHTML=respTxt;
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Grupo.php?selectorhtml=1&tipolista="+tipolista,true);
    xmlhttp.send();
}
function recalculaProveedores() {
    if (xmlhttp) xmlhttp.abort();
    xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("prvSelectArea");
            elem.innerHTML=respTxt;
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Proveedores.php?selectorhtml=1&tipolista="+tipolista,true);
    xmlhttp.send();
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
function dateIniSet() {
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(prev_month(day));
}
function dateEndSet() {
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
function adjustCalMonImgs(tgtWdgt) { adjust_calendar(tgtWdgt,false,{freeRange:true}); }
/*function adjustCalMonImgs(tgtWdgt) {
    const iniDateElem = document.getElementById("fechaInicio");
    const endDateElem = document.getElementById("fechaFin");

    const iniday = strptime(date_format, iniDateElem.value);
    const endday = strptime(date_format, endDateElem.value);
    const inimon = iniday.getMonth()+1;
    const endmon = endday.getMonth()+1;
    const sameyr = (iniday.getYear()===endday.getYear());

    let curday = iniday;
    let curmon = inimon;
    if (tgtWdgt===iniDateElem) {
        if (inimon!==endmon||!sameyr) {
            const lastDay=day_before(first_of_month(next_month(iniday)));
            endDateElem.value=strftime(date_format,lastDay);
        } else if (iniday>endday)
            endDateElem.value=iniDateElem.value;
    } else if (tgtWdgt===endDateElem) {
        curday=endday;
        curmon=endmon;
        if (inimon!==endmon||!sameyr) {
            const firstDay=first_of_month(endday);
            iniDateElem.value=strftime(date_format,firstDay);
        } else if(iniday>endday)
            iniDateElem.value=endDateElem.value;
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
}*/
<?php
clog1seq(-1);
clog2end("scripts.respaldafactura");
