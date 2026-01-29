<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
if(!hasUser()||(!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Carga Egresos"))) {
    die("Redirecting C to /".$_project_name."/");
}
clog2ini("scripts.cargapagos");
clog1seq(1);
?>
var winLog=false;
function showWaitRoll() {
    console.log("INI function showWaitRoll");
    cladd(ebyid("resultView"),"cartroll");
}
function openLog(dtf) {
    if (ebyid("logchk").checked) {
        console.log("INI openLog "+dtf);
        const pyms=ebyid("pagos");
        if (pyms.files && pyms.files.length>0 && pyms.files[0]["name"].length>0) {
            winLog=window.open("consultas/docs.php?pymlog="+dtf+"&encabezado=hidden&pie_pagina=hidden", "winlog", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=350,height=250");
        } else console.log("NO FILE");
    } else console.log("LOG DISABLED");
}
function submitting() {
    ekfil('resultView');
    showWaitRoll();
    if (winLog) winLog.close();
    console.log(' ... submitting ...');
    return true;
}
function fileTest() {
    console.log("INI fileTest");
    const pyBtn=ebyid("pagos");
    if (pyBtn.files.length>0) {
        clrem(pyBtn,"highlight");
        cladd(ebyid("sndbtn"),"highlight");
    } else {
        clrem(ebyid("sndbtn"),"highlight");
        cladd(pyBtn,"highlight");
    }
}
<?php
clog1seq(-1);
clog2end("scripts.cargapagos");
