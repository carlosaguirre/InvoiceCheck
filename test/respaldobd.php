<?php
error_reporting(E_ALL);
date_default_timezone_set("America/Mexico_City");
$mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
$basePath = dirname(dirname(__FILE__))."\\";
$_project_name="invoice";
require_once dirname(__DIR__)."/bootstrap.php";
require_once $basePath."clases\\FTP.php";
global $ftpsrv_clave,$ftpsrv_servidor;
if ($ftpsrv_servidor==="192.168.2.57:2300") {
    $ftpsrv_servidor="192.168.2.5:2300";
    MIFTP::log("FIXING ftpsrv_servidor: $ftpsrv_servidor");
}
if ($ftpsrv_clave==="ADMINPG16") {
    $ftpsrv_clave="ADMINPG";
    MIFTP::log("FIXING ftpsrv_clave: $ftpsrv_clave");
}
$ftpObj = MIFTP::newInstanceFacturas(); // newInstanceFtpServ(); // 
if (isset($ftpObj)) {
    $filename = "ftpTest.txt";
    $content = "Prueba de Objeto MIFTP\nLa existencia de este archivo en el servidor remoto implica el éxito de esta prueba.\nRevisar log para corroborar que la conexión haya cerrado correctamente.";
    try {
        $ftpObj->exportarTexto($filename, $content);
        doclog("SUCESS!","respaldobd",["username"=>"SISTEMA","log"=>MIFTP::log()]);
        echo "EXPORT SUCCESSFUL";
    } catch (Exception $e) {
        doclog("Error al exportar texto","respaldobd.err", ["username"=>"SISTEMA","exception"=>$e,"log"=>MIFTP::log()]);
        echo "EXPORT ERROR1";
    }
} else {
    doclog("Error en creación de Objeto FTP","respaldobderr",["username"=>"SISTEMA","log"=>MIFTP::log()]);
        echo "EXPORT ERROR2";
}
