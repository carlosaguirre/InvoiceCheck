<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    die("Empty File");
}
$esDesarrollo =  /* put or remove space after next asterisc to switch code option */ false; /*/ in_array(getUser()->nombre, ["admin","sistemas"], true); /**/
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$esConSitFis=validaPerfil("Constancias Corporativo")||$esSistemas;
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.consitfis");
clog1seq(1);
?>
document.onkeydown=bgKeyCheck;
// ToDo: Show wait airplane while loading pdf, and after pushing print. Remove after closing print dialog
function loadPDF(name) {
    console.log("INI loadPDF "+name);
    hideInfo();
    const pathEl=ebyid("bgdocPath");
    const nameEl=ebyid("bgdocName");
    pdfViewerState.renderElementId="pdfv_renderer";
    pdfViewerState.currentPageId="pdfv_current_page";
    pdfViewerState.zoomInId="pdfv_zoom_in";
    pdfViewerState.zoomOutId="pdfv_zoom_out";
    pdfViewerState.prevPgId="pdfv_go_previous";
    pdfViewerState.nextPgId="pdfv_go_next";
    pdfViewerState.tries=1;
    pdfViewerState.timeoutDelay=3000;
    const pdfRenderEl=ebyid("pdfv_renderer");
    pdfRenderEl.onrender=function() {
        console.log("In onrender function");
        clrem(["updateButton","pdfv_controls"],["hidden","invisible"]);
        adjustDialogBoxHeight();
        //pdfRenderEl.onrender=null;
        console.log("End onrender");
        overlayClose();
    }
    pdfRenderEl.onfailrender=function(msg) {
        overlayClose();
        overlayMessage(msg, "ERROR");
        //pdfRenderEl.onrender=null;
        //pdfRenderEl.onfailrender=null;
    }
    const patharr=name.split("/");
    nameEl.value=patharr.pop();
    pathEl.value=patharr.join("/")+"/";
    overlayWheel();
    reloadPDF();
}
function bgKeyCheck(evt) {
    //console.log("INI bgKeyCheck");
    if (evt.ctrlKey && evt.keyCode==80) {
        submitPrint();
        return eventCancel(evt);
    }
    return true;
}
function reloadPDF() {
    console.log("INI reloadPDF");
    const pathEl=ebyid("bgdocPath");
    const nameEl=ebyid("bgdocName");
    console.log("Path="+pathEl.value);
    console.log("Name="+nameEl.value);
    const url=window.location.origin+"/docs/csf/"+pathEl.value+nameEl.value+".pdf";
    console.log("Full Web Path = "+url);
    viewPDF(url);
    //overlayClose();
}
<?php
    if ($esConSitFis) { ?>
function updateFile(elem) {
    console.log("INI updateFile ",elem.files);
    const empEl=ebyid("empresa");
    const empSl=(empEl.selectedIndex&&empEl.selectedIndex>=0)?empEl.selectedIndex:0;
    const optEl=(empEl.options&&empEl.options.length>empSl)?empEl.options[empEl.selectedIndex]:false;
    console.log("Empresa="+empEl.value+"="+(optEl?optEl.text:"Nothing"));
    const pathEl=ebyid("bgdocPath");
    const nameEl=ebyid("bgdocName");
    const parameters = {action:"updateTaxStatusProof", inclusiveSeparator:"***", alias:nameEl.value,file:elem.files,gpoId:(optEl?optEl.id.slice(3):-1)};
    console.log("Parameters: ",parameters);
    overlayWheel();
    progressService("consultas/Archivos<?= $esDesarrollo?"Mul":"" ?>.php",parameters,updatedFile,notUpdatedFile);
}
<?php
    } ?>
function updatedFile(jobj,extra) {
    console.log("INI updatedFile: \nRESULT: "+jobj.result+(jobj.message?"\nMESSAGE: "+jobj.message:"")+"\nEXTRA: "+JSON.stringify(extra,jsonCircularReplacer()));
    if (jobj.data) console.log("DATA: ",jobj.data);
    else console.log("JOBJ: "+JSON.stringify(jobj,jsonCircularReplacer()));
    if (jobj.result==="success") {
        console.log("...result is success..."); //" jobj.state='"+jobj.state+"', jobj.params=<"+JSON.stringify(jobj.params,jsonCircularReplacer())+">, extra.state='"+extra.state+"'");
        if ((!jobj.state||jobj.state!=4) && (!jobj.params||!jobj.params.state||jobj.params.state!=4) && (!extra.state||extra.state!=4)) {
            console.log("INVALID STATE... jobj("+jobj.state+") - params("+jobj.params.state+") - extra("+extra.state+")");
            return; // esperar al final
        }
        console.log("..state is 4...");
        // toDo: si jobj.result=success: obtener nuevo path, cambiar bgdocPath.value y empresa.options[empresa.selectedIndex].value=bgdocPath.value+bgdocName.value, reloadPDF()
        const dataId=(jobj.data&&jobj.data.id?jobj.data.id:(jobj.id?jobj.id:false));
        if (!dataId) {
            console.log("...no hay dataId...");
            throw new Error("No hay ID");
        }
        const dataPath=(jobj.data&&jobj.data.path?jobj.data.path:(jobj.path?jobj.path:false));
        const dataAlias=(jobj.data&&jobj.data.alias?jobj.data.alias:(jobj.alias?jobj.alias:false));
        console.log("ID='"+dataId+"', PATH='"+dataPath+"', ALIAS='"+dataAlias+"'");
        const optEl=ebyid("gpo"+dataId);
        optEl.value=dataPath+dataAlias;
        const empEl=ebyid("empresa");
        empEl.value=optEl.value;
        const pathEl=ebyid("bgdocPath");
        const nameEl=ebyid("bgdocName");
        nameEl.value=dataAlias;
        pathEl.value=dataPath;
        reloadPDF();
        //overlayClose();
    } else if (jobj.result==="upkeep") {
        console.log("UPKEEP: "+jobj.message+"\n",jobj);
    } else if ((extra.state&&extra.state==4) || (extra.parameters&&extra.parameters.state&&extra.parameters.state==4)) {
        // toDo: overlayError jobj.message
        console.log("FINAL RESULT '"+jobj.result+"':'"+jobj.message+"'");
        for(const pp in jobj) if (pp!="result" && pp!="message") console.log(" * "+pp+" = ",jobj[pp]);
        ebyid("empresa").value="";
        overlayClose();
    } else {
        //console.log("UNEXPECTED result='"+jobj.result+"': '"+jobj.message+"'");
        console.log("UNEXPECTED '"+jobj.result+"':'"+jobj.message+"'");
        for(const pp in jobj) if (pp!="result" && pp!="message") console.log(" * "+pp+" = ",jobj[pp]);
        ebyid("empresa").value="";
        overlayClose();
    }
    // toDo: si no notUpdatedFile(jobj.message,jobj.log,extra)
}
function notUpdatedFile(errmsg,respText,extra) {
    // toDo: log;
    console.log("INI notUpdatedFile: ERRMSG: '"+errmsg+"'\nRESPTEXT: \n'"+respText+"'\nEXTRA: ",extra);
    if (extra.state>=4||extra.status>=200) {
        ebyid("empresa").value="";
        overlayClose();
    }
}
function submitPrint() {
    console.log("INI submitPrint");
    let bgdFrame=ebyid("bgdFrame");
    const pathEl=ebyid("bgdocPath");
    const nameEl=ebyid("bgdocName");
    if(pathEl.value.length==0||nameEl.value.length==0) return;
    const url=window.location.origin+"/docs/csf/"+pathEl.value+nameEl.value+".pdf";
    ekil(bgdFrame);
    //if (!bgdFrame) {
        bgdFrame=ecrea({eName:"IFRAME",src:url,className:"hidden",id:"bgdFrame"});
        document.body.appendChild(bgdFrame);
        console.log("append iframe. src= "+url);
    //} else {
    //    console.log("refresh iframe. src= "+url);
    //    bgdFrame.setAttribute("src",url);
    //}
    bgdFrame.contentWindow.print();
    bgdFrame.onbeforeprint=function() {
        console.log("bgdFrame before print");
    }
    bgdFrame.onafterprint=function() {
        console.log("bgdFrame after print");
    }
    bgdFrame.contentWindow.onbeforeprint=function() {
        console.log("iframe before print");
    }
    bgdFrame.contentWindow.onafterprint=function() {
        console.log("iframe after print");
    }
    console.log("Sent to print");
}
var newTab=false;
var newTabInterval=false;
function openNewWin() {
    console.log("INI openNewWin");
    let bgdFrame=ebyid("bgdFrame");
    const pathEl=ebyid("bgdocPath");
    const nameEl=ebyid("bgdocName");
    if(pathEl.value.length==0||nameEl.value.length==0) return;
    const url=window.location.origin+"/docs/csf/"+pathEl.value+nameEl.value+".pdf";
    clearInterval(newTabInterval);
    newTab=window.open(url,"archivo");
    newTabInterval=setInterval(function() {
        if (!newTab || newTab.closed) {
            clearInterval(newTabInterval);
            return;
        }
        try {
            if (newTab.location.href!==url) {
                // Estrategia 1: Cerrar pestaña y activar esta
                console.log("User tries to navigate at New Tab");
                clearInterval(newTabInterval);
                newTab.close();
                try {
                    window.focus();
                } catch (e) {
                    console.log("Focus failed (1): ", e);
                }
                newTab=false;
                // Estrategia 2: Forzar a que no naveguen
                //console.log("REDIRECTING NewTab to "+url);
                //newTab.location.href=url;
            }
        } catch (e) {
            // Handle cross-origin error by closing the window. Not necessary if newTab is already closing
            //console.log("Closing NewTab on cross-origin error", e);
            //newTab.close();
            console.log("Cross-origin error when closing newTab: ", e);
        }
    }, 500);
    setTimeout(()=>{
        if (newTab) {
            if (newTab.name!=="archivo") {
                console.log("NewTab name was not 'archivo'");
                newTab.name="archivo";
            } else console.log("NewTab Name CHECKED! It is 'archivo'");
        } else console.log("Cannot find newTab!!!");
    },100);
    // Estrategia 1: Cerrar newTab cuando esta ventana se cierra
    /*window.addEventListener("beforeunload", ()=> {
        if (newTab && !newTab.closed) newTab.close();
    });*/
    window.addEventListener("message", (e) => {
        console.log("Triggered Message Event from '"+e.origin+"'", e);
        if (e.data === "forceCloseChild" && newTab && !newTab.closed) {
            clearInterval(newTabInterval);
            newTab.close();
            try {
                window.focus();
            } catch (e) {
                console.log("Focus failed (2): ", e);
            }
            newTab=false;
        } else if (e.data === "requestFocus") {
            try {
                window.focus();
                // Optional: Bring to front in Electron/WebView scenarios
                if (window.blur) window.blur();
                window.focus();
            } catch (e) {
                console.log("Focus failed (3): ", e);
            }
        }
    });
    // Estrategia 2: No restringir newTab, permitir que siga abierta si cierran esta ventana, pero al navegar en ella se deberá redirigir al home /invoice
}
function test(txt) {
    console.log(txt);
}
function hideInfo() {
    console.log("INI hideInfo");
    cladd("pdfv_info","hidden");
<?php
    if ($esConSitFis) { ?>
    ebyid("updPkFl").value=null;
<?php
    } ?>
}
window.onbeforeprint=function() {
    console.log("window before print");
};
window.onafterprint=function() {
    console.log("window after print");
}
<?php
clog1seq(-1);
clog2end("scripts.consitfis");
