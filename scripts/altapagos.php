<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
if(!hasUser()||(!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Alta Pagos"))) {
    die("Redirecting to /".$_project_name."/");
}
clog2ini("scripts.altapagos");
clog1seq(1);
?>
function cleanLogFile() {
    let logContents=ebyid("log_contents");
    if(!logContents.firstChild) { console.log("Log File is empty"); return; }
    postService("consultas/Logs.php", {command:"eliminar",key:"autoCFDIP"},
        function(text,params,state,status){
            if(state<4&&status<=200)return;
            if(state==4&&status==200){
                if (text.length>0) {
                    try {
                        let jobj=JSON.parse(text);
                        if (jobj.result==="refresh") {
                            location.reload(true);
                            // window.location.href = "/< ?= $ _project _name ?>";
                        } else if (jobj.result==="exito") ekfil(logContents);
                        if (jobj.message) console.log("MSG: "+jobj.message);
                    } catch(ex) { console.log("EXC: "+ex); }
                } else { console.log("ERR: SIN RESPUESTA"); }
            } else { console.log("ERR: STATE="+state+", STATUS="+status+", or TEXTLEN="+text.length,params); }
        }
    );
}
//var colorBlocksLength=0;
var separatorsLength=0;
var refreshTry=true;
function readLogFile() {
    let logContents=ebyid("log_contents");
    postService("consultas/Logs.php", {command:"lectura",key:"autoCFDIP"},
        function(text,params,state,status) {
            if(state<4&&status<=200)return;
            if(state==4&&status==200){
                if(text.length>0) { try {
                    let jobj=JSON.parse(text);
                    if (jobj.result==="refresh") {
                        location.reload(true);
                        // window.location.href = "/< ?= $ _project _name ?>";
                    } else if (jobj.result==="exito") {
                        let currentSize=logContents.innerHTML.length-separatorsLength; // colorBlocksLength;
                        if (refreshTry||jobj.message.length!==currentSize) {
                            refreshTry=false;
                            logContents.textContent=jobj.message;
                            //console.log("Contenido actualizado "+currentSize+" => "+jobj.message.length);
                            separateBlocks();
                            if (jobj.filelist) {
                                //console.log(text);
                                const buttonSelected=lbycn("tabBtn selected");
                                const buttonText=buttonSelected[0].textContent.toLowerCase()||false;
                                const selectedSection=buttonText==="en proceso"?"proceso":buttonText;
                                //console.log("Selected Section is "+selectedSection.toUpperCase());
                                const flb=ebyid("fileListBody");
                                ekfil(flb);
                                const flr=ebyid("fileListReview");
                                ekfil(flr);
                                for (const subpath in jobj.filelist) {
                                    const section = subpath==="CFDIs"?"proceso":subpath;
                                    const sectionClassName="archivo "+section+(section===selectedSection?"":" hidden");
                                    fee(jobj.filelist[subpath], function(fdata) {
                                        //console.log("File Data: "+fdata.fname+"|"+fdata.fsize);
                                        flb.appendChild(ecrea({eName:"TR",className:sectionClassName,eChilds:[{eName:"TD",className:"top shrinkCol",eText:"\u00A0\u2022\u00A0"},{eName:"TD",className:"lefted",eText:fdata.fname},{eName:"TD",className:"righted vATBtm",eText:fdata.fsize}]}));
                                    });
                                    const listSize=jobj.filelist[subpath].length;
                                    const plSfx=(listSize==1?"":"s");
                                    flr.appendChild(ecrea({eName:"SPAN",className:sectionClassName,eText:""+listSize+" archivo"+plSfx+" "+(section==="proceso"?"en ":"")+section+"."}));
                                }
                            }
                        } // else console.log("Sin cambios "+currentSize);
                    } else console.log("LECTURA DE LOG FALLIDA: "+text);
                } catch(ex) {
                    console.log(ex,text);
                } }
            } else {
                console.log("ERROR WITH STATE="+state+", STATUS="+status,params);
            }
        }
    );
}
function separateBlocks() {
    let logContents=ebyid("log_contents");
    var reH = new RegExp("-----" , "g");
    var fxH = "<hr>";
    let startLen = logContents.innerHTML.length;
    logContents.innerHTML = logContents.innerHTML.replace(reH, fxH);
    separatorsLength=logContents.innerHTML.length-startLen;
}
function doColorBlocks() {
    let logContents=ebyid("log_contents");
    var reI = new RegExp("(.*INITIATE PROCESS.*)" , "g");
    var fxI = "<span class=\"inlineblock all_space btop2d padtop2\">$1</span>";
    var reT = new RegExp("(.*TERMINATE PROCESS.*)" , "g");
    var fxT = "<span class=\"inlineblock all_space bbtm2d padbtm2\">$1</span>";
    let startLen = logContents.innerHTML.length;
    logContents.innerHTML = logContents.innerHTML.replace(reI, fxI).replace(reT, fxT);
    colorBlocksLength=logContents.innerHTML.length-startLen;
}
setInterval(readLogFile,5*60*1000-15);
<?php
clog1seq(-1);
clog2end("scripts.altapagos");
