<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.repAVentasCliente");
clog1seq(1);
$regiones=[
    "APSA"=>["APSA","MARLOT","RGA","JLA","JYL","COREPACK","SERVICIOS","SKARTON","MORYSAN"],
    "STACLARA"=>["GLAMA","LAISA","ENVASES","LAMINADOS","APEL","MELO","DANIEL","DESA","DEMO","GLAMASTA","PAPEL","BIDASOA","FIDEICOMIS","HALL","FIDEMIFEL","ESMERALDA","SKARTON","JYLSOR","APSALU"]];
if (isset($_SERVER["SERVER_NAME"])&&$_SERVER["SERVER_NAME"]==="localhost") {
?>
var timeStep=false;
var autoRun=true;
var cancelReason="";
console.log("REPAVENTASCLIENTE SCRIPT READY!!!");
function repAVentasClienteInit() {
    if (window.Event) {
            document.captureEvents(Event.MOUSEMOVE);
    }
    document.onmousemove=getCursorXY;
}
function getCursorXY(e) {
    const cImg=ebyid("cursorImg");
    const wid=cImg.clientWidth;
    const hei=cImg.clientHeight;
    cImg.style.left=((window.Event?e.pageX:(event.clientX+(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft)))-(wid/2))+"px";
    cImg.style.top=((window.Event?e.pageY:(event.clientY+(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop)))-(hei/2))+"px";
}
function autoProcess(step) {
    console.log("INI function autoProcess. step="+step+". Auto mode.");
    const sttElem=ebyid("statusSummary");
    let formId=false;
    let stepCount=0;
    let textStatus=false;
    switch(step) {
        case 1: formId="loginForm"; stepCount=25; textStatus="TIEMPO DE REGISTRO"; break;
        case 2: formId="requestForm"; stepCount=5; textStatus="TIEMPO DE DESCARGAR REPORTE "; break;
        case 3: ebyid("avanceFrame").src=""; sttElem.appendChild(ecrea({eName:"P",eText:"TERMINÓ EL PROCESO"}));break;
    }
    if (!autoRun) {
        console.log("AUTO RUN DISABLED");
        if (timeStep) clearTimeout(timeStep);
        sttElem.appendChild(ecrea({eName:"P",eText:"CICLO CANCELADO"+(cancelReason.length>0?": "+cancelReason:"")}));
        return;
    }
    if (formId) {
        if (!sttElem.hasOwnProperty("count")) {
            ebyid(formId).submit();
            sttElem.count=stepCount;
        }
        if (sttElem.count>0) {
            if (step==1&&sttElem.count==(stepCount-1)) {
                postService("consultas/terminal.php",{accion:"esasaSubmit1",x:window.screenLeft,y:window.screenTop},function(text,pars,state,status){
                    if (state!=4||status!=200) {
                        console.log("terminal "+state+"/"+status);
                        if (state>4||status>200) {
                            autoRun=false;
                            cancelReason="Consulta al servidor fallida.";
                        }
                        return;
                    }
                    if (text.length==0) {
                        console.log("terminal empty. parameters: ",pars);
                        autoRun=false;
                        cancelReason="Respuesta del servidor vacía.";
                        return;
                    }
                    try {
                        let response=JSON.parse(text);
                        console.log("terminal result: "+text);
                        if (response.result && response.result==="refresh") {
                            location.reload(true);
                        } else if (response.result && response.result=="success") {
                            console.log("step="+step+", count="+sttElem.count);
                            if (response.message) {
                                sttElem.appendChild(ecrea({eName:"P",eText:"SUCCESS: "+response.message}));
                            } else {
                                sttElem.appendChild(ecrea({eName:"P",eText:"SUCCESS!"}));
                            }
                        } else {
                            console.log("FAILURE!");
                            if (response.message) {
                                sttElem.appendChild(ecrea({eName:"P",eText:"FAILURE: "+response.message}));
                            } else {
                                sttElem.appendChild(ecrea({eName:"P",eText:"FAILURE!"}));
                            }
                            autoRun=false;
                            cancelReason="Fallo en Respuesta del servidor.";
                            return;
                        }
                    } catch (ex) {
                        console.log("terminal error: ",ex);
                        autoRun=false;
                        cancelReason="Error en Respuesta del servidor.";
                        return;
                    }
                });
            } else if (step==2&&sttElem.count==(stepCount-3)) {
                clearTimeout(timeStep);
                timeStep=setTimeout(function() { const linkElem=ecrea({eName:"A",href:"http://glama.esasacloud.com/reportes/FAR6187.188.83.18LocalFACTURAS.csv",target:"_blank",download:"reporteAPSA2004.csv"}); linkElem.dispatchEvent(new MouseEvent('click')); /* rename file in server setTimeout(function(){},1000); */ },1000);
            }
            sttElem.appendChild(ecrea({eName:"P",eText:textStatus+": "+sttElem.count}));
            sttElem.count--;
            setTimeout(function() { autoProcess(step); },998);
        } else {
            delete sttElem.count;
            autoProcess(step+1);
        }
    }
}
<?php
} else if(hasUser()&&validaPerfil("Administrador")) { ?>
function repAVentasClienteInit() {
    if (window.Event) {
            document.captureEvents(Event.MOUSEMOVE);
    }
    document.onmousemove=getCursorXY;
}
function getCursorXY(e) {
    const cImg=ebyid("cursorImg");
    const wid=cImg.clientWidth;
    const hei=cImg.clientHeight;
    cImg.style.left=((window.Event?e.pageX:(event.clientX+(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft)))-(wid/2))+"px";
    cImg.style.top=((window.Event?e.pageY:(event.clientY+(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop)))-(hei/2))+"px";
}
function autoProcess(step) {
    console.log("INI function autoProcess. step="+step+". Manual mode.");
    const sttElem=ebyid("statusSummary");
    let formId=false;
    switch(step) {
        case 1: formId="loginForm"; sttElem.appendChild(ecrea({eName:"P",eChilds:[{eText:"PRESIONE BOTON ENVIAR DATOS. LUEGO "},{eName:"BUTTON",type:"button",onclick:function(){autoProcess(2);},eText:"INICIE DESCARGA"}]})); break;
        case 2: formId="requestForm"; sttElem.appendChild(ecrea({eName:"P",eText:"TIEMPO DE DESCARGAR REPORTE APSA"})); break;
        case 3: ebyid("avanceFrame").src=""; sttElem.appendChild(ecrea({eName:"P",eText:"TERMINÓ EL PROCESO"}));break;
        default: console.log("Unknown step: '"+step+"'");
    }
    if (formId) {
        ebyid(formId).submit();
    }
}
<?php
} else { ?>
function autoProcess(step) {
    console.log("INI function autoProcess. step="+step+". No action.");
}
<?php
} ?>
function go() {
    console.log("INI FUNCTION GO!!!");
}
<?php
clog1seq(-1);
clog2end("scripts.repAVentasCliente");
