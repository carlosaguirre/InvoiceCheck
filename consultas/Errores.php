<?php
require_once dirname(__DIR__)."/bootstrap.php";
$basePath = dirname(dirname(__FILE__))."\\";
if (isSaveLog()) doSaveLog();
function isSaveLog() {
    return isset($_POST["accion"])&&$_POST["accion"]==="savelog"&&isset($_POST["nombre"])&&isset($_POST["texto"]);
}
function doSaveLog() {
    global $basePath;
    $nm=preg_replace("/[^a-zA-Z0-9_]/", "",$_POST["nombre"]??"");
    $txt=$_POST["texto"]??"";
    try {
        if (isset($nm[0]) && isset($txt[0])) {
            //doclog($txt, $nm, $_POST);
            $dt=new DateTime();
            $dtFmt=$dt->format("ymd");
            $tmFmt = (new DateTime())->format("H:i:s");
            if (hasUser()) {
                $usr=getUser();
                $unm=$usr->nombre;
            } else $unm="nouser";
            // todo: crear directorio LOGS\\$dtFmt si no existe
            file_put_contents($basePath."LOGS\\$dtFmt\\$nm.log","[$unm $tmFmt] $txt\r\n", FILE_APPEND | LOCK_EX);
        } else doclog("ERROR EN ERRORES","error",$_POST);
    } catch(Exception $ex) {
        doclog("EXCEPCION EN ERRORES","error",$_POST+getErrorData($ex));
    }
}
