<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
if(!hasUser()) {
    die("Empty File");
}
*/
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.logs");
clog1seq(1);
?>
console.log("LOGS SCRIPT READY!!!");
function selectThis(evt) {
    const tgt=evt.target;
    const path=tgt.getAttribute("name");
    console.log("INI function selectThis ",path);
    clrem(lbycn("selected",tgt.parentNode),"selected");
    cladd(tgt,"selected");
    const rae=ebyid("resultarea");
    rae.textContent="";
    readyService("consultas/Logs.php", {action:"readRequest",path:path}, (j,x)=>{
        if(j.list) {
            const ule=ebyid("userList");
            ekfil(ule);
            j.list.forEach((s,i)=>{
                const lio={eName:"LI",className:"userElem",onclick:selectThis,eText:s.slice(7)};
                if (j.index) {
                    if (i==j.index) lio.className+=" selected";
                } else if (i==0) lio.className+=" selected";
                const lie=ecrea(lio);
                lie.setAttribute("name",s);
                ule.appendChild(lie);
            });
        };
        if(j.message)
            rae.textContent=j.message;console.log(j,x);
    }, (msg,txt,x)=>{
        rae.textContent=msg;
        console.log(txt,x)
    });
}
<?php
clog1seq(-1);
clog2end("scripts.logs");
