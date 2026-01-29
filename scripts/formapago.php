<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.formapago");
clog1seq(1);
?>
console.log("FORMA PAGO SCRIPT READY!!!");
function isChecked(evt) {
    let tgt=evt.target;
    let cel=tgt.parentNode;
    let row=cel.parentNode;
    //console.log("CHECKING: ",row);
    [].forEach.call(row.getElementsByClassName("isdata"), function(x) {
        //console.log("FIXING: ",x);
        clrem(x,"bggreen");
        cladd(x,"bgyellow");
    });
}
function saveData() {
    let pars={action:"fixMDP",add:[],del:[]};
    [].forEach.call(document.querySelectorAll(".isdata.bgyellow>input[type='checkbox']"), function(x) {
        if(x.checked&&!x.classList.contains("isChecked")) pars.add.push(x.value);
        else if (!x.checked&&x.classList.contains("isChecked")) pars.del.push(x.value);
    });
    postService(
        "consultas/MetodosDePago.php",
        pars,
        function(text,params,state,status) {
            if(state<4&&status<=200)return;
            if(state==4&&status==200){
                if(text.length==0) {
                    console.log("RESPUESTA VACIA");
                    return;
                }
                try{
                    let jobj=JSON.parse(text);
                    if (jobj.result==="refresh") {
                        location.reload(true);
                    } else if (jobj.result==="exito") {
                        console.log("EXITO");
                        let btns=document.getElementsByClassName("navSelected");
                        let btn=btns[btns.length-1];
                        if (btn && btn.click) btn.click();
                    } else if (jobj.result==="vacio") {
                        overlayMessage("<p>"+jobj.message+"</p>","ERROR");
                    } else overlayMessage("<pre class=\"lefted\">"+jobj.message+"</pre>",jobj.result);
                } catch(ex) {
                    console.log(ex,text);
                    overlayMessage("<p>Error en servidor, consulte al administrador.</p>","ERROR");
                }
            } else {
                console.log("ERROR WITH STATE="+state+", STATUS="+status,params);
            }
        },function(errmsg, parameters, evt) {
            console.log("Error="+errmsg+", State="+parameters.xmlHttpPost.readyState+", Status="+parameters.xmlHttpPost.status);
        }
    );
}
<?php
clog1seq(-1);
clog2end("scripts.formapago");
