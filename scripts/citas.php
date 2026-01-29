<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.citas");
clog1seq(1);
include "scripts/calendar_tools.php";
if (!isset($calObj)) {
    require_once "clases/Calendar.php";
    $calObj=new Calendar();
}
echo "var fullTime=[\"".implode("\",\"",$calObj->baseDay)."\"];\n";
$esAdmin = validaPerfil("Administrador");
if ($esAdmin) {
?>
function populateCalendar(year) {
    postService("consultas/Citas.php",{action:"populate",year:year},function(textmsg, parameters, readyState, status) {
        if(readyState<4&&status<=200) return;
        if(textmsg.length==0) return;
        location.reload();
    });
}
<?php
}
?>
function load_calendar() {
    const table = ebyid('calendar_widget');
    if (table) {
        table.callbackFunc = function (date,selectedElem) {
            if (!selectedElem || clhas(selectedElem,"weekend") || clhas(selectedElem,"other_month") || clhas(selectedElem,"occupied") || clhas(selectedElem,"pastday") || (selectedElem.availableTime && selectedElem.availableTime.length==0)) return;
            const s = date != '' ? strftime(readable_format,date) : '';
            console.log("Loading Calendar, valid date:",date," and selectedElement:",selectedElem);
            current_day=date;
            let timeList=false;
            if (selectedElem.availableTime) timeList=selectedElem.availableTime;
            else timeList=fullTime;
            const gralBlock=ebyid("cita_generales");
            const lineBlock=ebyid("cita_transporte");
            if (gralBlock) {
                const options=[];
                for (let i=0; i < timeList.length; i++) {
                    const timetxt=timeList[i];
                    options.push({eName:"OPTION",value:timetxt,text:timetxt});
                }
                if (!gralBlock.step) {
                    ekfil(gralBlock);
                    gralBlock.appendChild(ecrea({eName:"TABLE", className:"lefted", eChilds:[{eName:"TBODY", eChilds:[{eName:"TR", eChilds:[{eName:"TH", colSpan:"2", eText:"PROVEEDOR: <?= getUser()->persona ?>"}]},{eName:"TR", eChilds:[{eName:"TH", className:"topvalign", eText:"Fecha de Cita:\u00A0\u00A0\u00A0"}, {eName:"TD", id:"fechaElegida", beginDate:s, beginTime:false, className:"topvalign", eChilds:[{eText:s+"\u00A0\u00A0\u00A0"}, {eName:"SELECT", id:"appointmentList", size:"3", className:"topvalign", eChilds:options}]}]}, {eName:"TR", id:"responseRow", class:"hidden"}, {eName:"TR", eChilds:[{eName:"TD", colSpan:"2", className:"centered", eChilds:[{eName:"BUTTON", id:"btnGo", type:"button", eText:"Continuar", onclick:requestAppt}]}]}]}]}));
                } else {
                    const chosenElem=ebyid("fechaElegida");
                    ekfil(chosenElem);
                    chosenElem.beginDate=s;
                    chosenElem.beginTime=false;
                    chosenElem.appendChild(ecrea({eText:s+"\u00A0\u00A0\u00A0"}));
                    chosenElem.appendChild(ecrea({eName:"SELECT", id:"appointmentList", size:"3", className:"topvalign", eChilds:options}));
                    let respRow=ebyid("responseRow");
                    while (respRow) {
                        cladd(respRow,"hidden");
                        respRow=respRow.nextRow;
                    }
                    const btnGo=ebyid("btnGo");
                    btnGo.textContent="Continuar";
                    clrem(btnGo.parentNode.parentNode,"hidden");
                }
                gralBlock.step=1;
            }
            fee(lbycn("current_selection"), function(elem){clrem(elem, "current_selection");});
            cladd(selectedElem, "current_selection");
            const calBlock=ebyid("cita_calendario");
            clrem(calBlock, "alone");
            clrem(gralBlock, "hidden");
            cladd(lineBlock, "hidden");
        };
    }
}
function requestAppt() {
    const gralBlock=ebyid("cita_generales");
    const lineBlock=ebyid("cita_transporte");
    const chosenElem=ebyid("fechaElegida");
    const apptHrLst=ebyid("appointmentList");
    if (gralBlock.step==1) {
        if (!apptHrLst) {
            console.log("appointmentList not found!");
            return;
        } else if (!apptHrLst.value) {
            chosenElem.beginTime=apptHrLst.options[0].value;
        } else {
            chosenElem.beginTime=apptHrLst.value;
        }
        chosenElem.textContent=chosenElem.beginDate+" "+chosenElem.beginTime;
        let respRow=ebyid("responseRow");
        if (respRow.nextRow) {
            while (respRow) {
                clrem(respRow,"hidden");
                respRow=respRow.nextRow;
            }
        } else {
            clrem(respRow,"hidden");
            respRow.colSpan="2";
            respRow.appendChild(ecrea({eName:"TH",colSpan:"2",eText:"DATOS DE LA PERSONA QUE SOLICITA:"}));
            const btnGo=ebyid("btnGo");
            btnGo.textContent="Avanzar";
            const gralTable=respRow.parentNode;
            const btnRow=btnGo.parentNode.parentNode;
            const respRow2=ecrea({eName:"TR",eChilds:[{eName:"TH",eText:"Nombre(s) *:"},{eName:"TD",eChilds:[{eName:"INPUT",type:"text",className:"wid225px",id:"firstname"}]}]});
            gralTable.insertBefore(respRow2,btnRow);
            respRow.nextRow=respRow2;
            const respRow3=ecrea({eName:"TR",eChilds:[{eName:"TH",eText:"Apellidos *:"},{eName:"TD",eChilds:[{eName:"INPUT",type:"text",className:"wid225px",id:"lastname"}]}]});
            gralTable.insertBefore(respRow3,btnRow);
            respRow2.nextRow=respRow3;
            const respRow4=ecrea({eName:"TR",eChilds:[{eName:"TH",eText:"Teléfono:"},{eName:"TD",eChilds:[{eName:"INPUT",type:"text",className:"wid225px",id:"telephone"}]}]});
            gralTable.insertBefore(respRow4,btnRow);
            respRow3.nextRow=respRow4;
            const respRow5=ecrea({eName:"TR",title:"Correo Electrónico",eChilds:[{eName:"TH",eText:"Correo-E *:"},{eName:"TD",eChilds:[{eName:"INPUT",type:"text",className:"wid225px",id:"email"}]}]});
            gralTable.insertBefore(respRow5,btnRow);
            respRow4.nextRow=respRow5;
            ebyid("firstname").focus();
        }
        gralBlock.step=2;
    } else {
    // aquí iría bloque de factura, previamente dada de alta. Debería caber debajo del bloque general.
    // mover datos de transporte al siguiente bloque.
        const btnGo=ebyid("btnGo");
        cladd(btnGo.parentNode.parentNode,"hidden");
        lineBlock.appendChild(ecrea({eName:"TABLE", className:"lefted", eChilds:[{eName:"TBODY", eChilds:[{eName:"TR", eChilds:[{eName:"TH", colSpan:"2", eText:"DATOS DEL TRANSPORTE A INGRESAR:"}]}, {eName:"TR",eChilds:[{eName:"TH",eText:"Actividad *:"},{eName:"TD",eChilds:[{eName:"SELECT",className:"wid225px",id:"actividad",eChilds:[{eName:"OPTION",value:"CARGA",text:"CARGA"},{eName:"OPTION",value:"DESCARGA",text:"DESCARGA"}]}]}]}, {eName:"TR",eChilds:[{eName:"TH",eText:"Tipo *:"},{eName:"TD",eChilds:[{eName:"SELECT",className:"wid225px",id:"tipo_transporte",eChilds:[{eName:"OPTION",value:"PARTICULAR",text:"PARTICULAR"},{eName:"OPTION",value:"CAMIONETA",text:"CAMIONETA"},{eName:"OPTION",value:"TRAILER",text:"TRAILER"},{eName:"OPTION",value:"TORTON",text:"TORTON"}]}]}]}, {eName:"TR",eChilds:[{eName:"TH", colSpan:"2",eChilds:[{eText:"Requiere Andén:"},{eName:"INPUT",type:"checkbox",id:"con_anden",value:"1"}]}]}, {eName:"TR", eChilds:[{eName:"TD", colSpan:"2", className:"centered", eChilds:[{eName:"BUTTON", id:"btnSend", type:"button", eText:"Solicitar", onclick:requestService}]}]}]}]}));
        clrem(lineBlock, "hidden");
    }
}
function requestService() {
    console.log("INI function requestService");
}
function resultAppt(textmsg, parameters, readyState, status) {
    if(readyState<4&&status<=200) return;
    const respElem=ebyid("responseRow");
    ekfil(respElem);
    if(textmsg.length==0) {
        clrem(respElem,"hidden");
        respElem.appendChild(ecrea({eName:"TD", colSpan:"2", className:"centered redden", eText:"Solicitud sin respuesta. Intente más tarde nuevamente o consulte al administrador."}));
        return;
    }
    try {
        const jobj=JSON.parse(textmsg);
        if (jobj.success) {
            const labelElem=ebyid("fechaElegida");
            labelElem.textContent = labelElem.textContent;
            clrem(respElem,"hidden");
            respElem.appendChild(ecrea({eName:"TD", colSpan:"2", className:"centered", eText:"Solicitud recibida, espere confirmación."}));
            clearTimeout(appointmentTimeout);
            postService("consultas/Citas.php",{action:"refresh",return:"occupied",year:display_day.getFullYear(),month:padDate(display_day.getMonth()+1)},intervalCallback);
        } else {
            clrem(respElem,"hidden");
            respElem.appendChild(ecrea({eName:"TD", colSpan:"2", className:"centered redden", eText:"Solicitud de cita interrumpida, intente nuevamente más tarde o consulte al administrador."}));
            if (jobj.error) console.log("Error: "+jobj.error);
            if (jobj.log) console.log("Log: "+jobj.log);
        }
    } catch(ex) {
        console.log("Exception caught: ", ex, "\nText: ", textmsg);
        return;
    }
}
<?php
clog1seq(-1);
clog2end("scripts.citas");
