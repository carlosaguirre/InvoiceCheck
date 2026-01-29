<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.respaldosis");
clog1seq(1);
?>
function hacerRespaldo(resultadoId, tipoRespaldo) {
    const resElem=ebyid(resultadoId); ekfil(resElem); cladd(resElem,"hidden");
    resElem.parentNode.appendChild(ecrea({eName:"IMG",id:"waiting",src:"imagenes/icons/rollwait2.gif",width:"160",height:"40",className:"centered"}));
    const postURL="consultas/Respaldo.php"; const parameters={tipo:tipoRespaldo,timeout:179900}; // 2m, 59.9s
    const xHP=postService(postURL, parameters, function(response, pars, state, status) {
        console.log("STATE: "+state+", STATUS: "+status+", RESPONSE: "+response);
        if (state<4 && status<=200) return;
        if (state==4 && status==200) {
            console.log(response); clrem(resElem,"hidden"); ekil(ebyid("waiting"));
            const jobj=JSON.parse(response);
            //const respArr=response.split(/\r?\n/);
            //respArr.forEach(line => (line.trim().length>0?resElem.appendChild(ecrea({eName:"LI",eChilds:JSON.parse(line)})):0));
            if (jobj.result && jobj.result==="refresh") {
                location.reload(true);
            } else if (jobj.result && jobj.result==="success" && jobj.data) {
                jobj.data.forEach(line => resElem.appendChild(ecrea(line)));
            }
        } else console.log("STATE: "+state+", STATUS: "+status+", RESPONSE: "+response);
    }, function (errmsg, parameters, evt) {
        console.log("STATE: "+parameters.xmlHttpPost.readyState+", STATUS: "+parameters.xmlHttpPost.status+", ERRMSG: "+errmsg);
    });

}
function hacerDatos() { hacerRespaldo("resultado","datos"); }
function hacerCodigo() { hacerRespaldo("versiones","codigo"); }
function hacerLOGS() { hacerRespaldo("loglist", "logs"); }
function hacerCFDI() { hacerRespaldo("cfdilist", "cfdi"); }
function viewFile(evt) {
    let tgt=evt.target;
    if (tgt.tagName!=="LI") tgt=tgt.parentNode;
    const atp=tgt.getAttribute("ftype");
    const anm=tgt.getAttribute("title");
    console.log("INI function viewFile ",tgt);
    const filepath=atp+"/"+anm;
    overlayMessage({eName:"B",eText:filepath}, "Archivo");
}
function hacer(evt) {
    if (!evt) evt=windows.event;
    if (evt) {
        const tgt=evt.target;
        console.log("Hacer "+tgt.value+". ("+tgt.textContent+")");
    }
}
<?php
clog1seq(-1);
clog2end("scripts.respaldosis");
