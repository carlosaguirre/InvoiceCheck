<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser()) {
    die();
}
$esAdmin=validaPerfil("Administrador");
if (!isset($esSuperAdmin)) $esSuperAdmin = $esAdmin && (getUser()->nombre==="admin"||getUser()->nombre==="arturo");
header("Content-type: application/javascript; charset: UTF-8");
if ($esSuperAdmin) {
?>
function isValidSubmit(evt) {
    console.log("INI function isValidSubmit");
    const tgt=evt.target;
    const els=tgt.elements;
    clearPackMessage();
    if (els.filepack) return isValidFile(els.filepack.files[0],ebyid("filepackMessage"));
    else if (els.scriptpack) isValidFile(els.scriptpack.files[0],ebyid("scriptpackMessage"));
    return false;
}
function isValidFile(file,msgElem) {
    console.log("INI function isValidFile ",file);
    if (file.size>=2097152) {
        msgElem.textContent="Error. El archivo es demasiado grande: "+human_filesize(file.size)+"\nEl tamaño no debe exceder de 2MB";
        return false;
    }
    return true;
}
function clearPackMessage() {
    ekfil(ebyid("filepackMessage"));
    ekfil(ebyid("scriptpackMessage"));
}
function sendQuery(qry) {
    console.log("INI function sendQuery: "+qry);
}
function changeStatement(evt) {
    const tgt=evt.target;
    console.log("INI function changeStatement: "+tgt.value);
    switch(tgt.value) {
        case "SELECT": cladd(["insertBlock","updateBlock"],"hidden"); clrem("selectBlock","hidden"); break;
        case "INSERT": cladd(["selectBlock","updateBlock"],"hidden"); clrem("insertBlock","hidden"); break;
        case "UPDATE": cladd(["selectBlock","insertBlock"],"hidden"); clrem("updateBlock","hidden"); break;
    }
}
function validateText(evt) {
    ;
}
<?php
}
