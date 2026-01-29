<?php
error_reporting(E_ALL);
date_default_timezone_set("Etc/GMT+6");
$mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
$basePath = dirname(dirname(__FILE__))."\\";
function flog($txt) {
    global $basePath;
    $fmt = (new DateTime())->format("y-m-d H:i:s");
    file_put_contents($basePath."LOGS\\respaldobd.log","[$fmt] $txt\r\n", FILE_APPEND | LOCK_EX);
}
function errlog($txt) {
    global $basePath;
    $fmt = (new DateTime())->format("y-m-d H:i:s");
    file_put_contents($basePath."LOGS\\respaldobd.err.log","[$fmt]$txt\r\n", FILE_APPEND | LOCK_EX);
}
$directory = "file://FTPSERVER/sistemas";// $basePath."/archivos"; //'/path/to/my/directory';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
echo $directory."\n";
print_r($scanned_directory);
