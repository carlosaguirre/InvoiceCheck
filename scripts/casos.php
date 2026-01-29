<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
sessionInit();
if(!hasUser()) {
    die("Empty File");
}
*/
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.casos");
clog1seq(1);
?>
function isValidEmailRT(evt) {
    const tgt=evt.target;
    const email=tgt.value.trim();
    const blk=document.querySelector("#resultDiv>div");
    if (email.length>0 && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        clswt(blk,["bggreenish","greener"],["bgred05","darkRedLabel"]);
    } else if (email.length>0) {
        clswt(blk,["bgred05","darkRedLabel"],["bggreenish","greener"])
    } else {
        clrem(tgt,["greener","bggreenish","bgred05","darkRedLabel"]);
    }
}
function sendMail(evt) {
    const aGralDiv=ebyid("area_general");
    readyService("consultas/Casos.php", { action:"email", domain:ebyid("domain").value, email:ebyid("email").value, subject:ebyid("subject").value, message:ebyid("message").value}, (j,e)=>{
        console.log("Ready SendMail",j);
        messageBlock(j.message);
        const blk=document.querySelector("#resultDiv>div");
        if (j.result==="error") clswt(blk,["bggreenish","greener"],["bgred05","darkRedLabel"]);
        else if (j.result==="success") clswt(blk,["bgred05","darkRedLabel"],["bggreenish","greener"]);
        else clrem(blk,["bggreenish","greener","bgred05","darkRedLabel"]);
        if (j.autofocus) {
            const af=ebyid(j.autofocus);
            if (af) {
                console.log("Adding CallOnClose",af);
                af.autofocus="1";
                const resultDiv=ebyid("resultDiv");
                resultDiv.callOnClose = (evt)=>{
                    const tgt=evt.currentTarget.parentNode;
                    console.log("CallOnClose",tgt.querySelectorAll("[autofocus]"));
                    focusOnAutoFocus(tgt);
                };
            } else console.log("NO AUTOFOCUS");
        } else console.log("JSON RESULT: ", j, blk);
    }, (m,t,x)=>{
        messageBlock("ERROR: "+m);
        const blk=document.querySelector("#resultDiv>div");
        clswt(blk,["bggreenish","greener"],["bgred05","darkRedLabel"]);
        console.log("TEXT RESULT: ", t, blk);
    });
}
var messageBlockTimeout=false;
function messageBlock(message, isAppend) {
    const resultDiv = ebyid("resultDiv");
    const alertBlock = resultDiv.firstElementChild;
    if (!isAppend) ekfil(alertBlock);
    if (!message) return true;
    if (typeof message==="string" && message.includes("\n")) {
        message=message.split("\n");
        message=message.flatMap((itm, idx, arr) => idx < arr.length-1 ? [itm, {eName:"BR"}] : [itm]);
    }
    if (Array.isArray(message)) {
        let addSome=false;
        message.forEach(submsg=>{ if (messageBlock(submsg, true)) addSome=true; });
        return addSome;
    }
    if (typeof message==="string") message={eText:message};
    if (isElemObj(message)) message=ecrea(message);
    if (isNode(message)) {
        alertBlock.appendChild(message);
        if (messageBlockTimeout) {
            clearTimeout(messageBlockTimeout);
            messageBlockTimeout=false;
        }
        messageBlockTimeout=setTimeout((el,msg)=>{
            clrem(el,"hidden");
            const blk=el.firstElementChild;
            const hM=(el.clientHeight-blk.clientHeight-6)/3;
            blk.style.marginTop=""+hM+"px";
            clearTimeout(messageBlockTimeout);
            messageBlockTimeout=false;
        },100,resultDiv,message);
        return true;
    }
    return false;
}
function hideDiv(evt) {
    const tgt = evt.currentTarget;
    console.log("hideDiv",tgt);
    cladd(tgt,"hidden");
    if (tgt.callOnClose) {
        tgt.callOnClose(evt);
        if (!tgt.keepCallOnClose) delete tgt.callOnClose;
    } else console.log("Undefined callOnClose",tgt);
}
//function testAlert(evt) {
//    const tgt = evt.target;
//    messageBlock("Esto\nes\nun\nmensaje\nde\nprueba");
//}
//document.addEventListener('DOMContentLoaded', function() {
//    const rtv=messageBlock("Probando funciones \ncladd y \nclrem");
//});

<?php
clog1seq(-1);
clog2end("scripts.casos");
