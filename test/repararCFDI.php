<?php
// Para ejecutar en consola de texto
// TODO: para cada xml dentro de la carpeta archivos y recursivamente en todas las carpetas, ejecutar CFDI->reparaXML
// Ignorar archivo y no mostrar mensaje si el resultado es "No se realizaron cambios";
// Mostrar mensaje si el resultado es un string no vacío
// Mostrar mensaje Archivo corregido si el resultado es true
$_project_name=false;
$logmsg="";
function errlog($msg,$key) {
    global $logmsg;
    if (isset($logmsg[0])) $logmsg.="\n";
    $logmsg.=$key.") ".$msg;
}
$sitePath='C:/Apache24/htdocs/invoice/';
$basePath=$sitePath.'archivos';
$baseLen=strlen($basePath);
$countFile=0;
$countFix=0;
$countFail=0;
$countIgnore=0;
require_once $sitePath."clases/CFDI.php";
function repararPath($dir='C:/Apache24/htdocs/invoice/archivos') {
    global $basePath, $baseLen, $countFile, $countFix, $countFail, $countIgnore;
    if (substr($dir,0,$baseLen)!==$basePath) return;
    $tree = glob(rtrim($dir, '/') . '/*');
    if (is_array($tree)) {
        foreach($tree as $file) {
            if (is_dir($file)) {
                repararPath($file);
            } elseif (is_file($file) && substr($file, -4)===".xml") {
                $countFile++;
                $result=CFDI::reparaXML($file);
                if ($result===true) {
                    $countFix++;
                    echo $file." CORREGIDO!\n";
                } else if ($result!=="No se realizaron cambios") {
                    $countFail++;
                    echo $file." ".$result."\n";
                } else $countIgnore++;
            }
        }
    }
}
repararPath($_POST["filePath"]??$basePath);
echo "Se evaluaron $countFile archivos. Reparados $countFix. Erroneos $countFail. Ignorados $countIgnore.\n";
if (isset($logmsg[0])) {
    echo "LOGS:\n".$logmsg."\n";
}
