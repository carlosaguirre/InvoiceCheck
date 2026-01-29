<?php
require_once dirname(__DIR__)."/bootstrap.php";
$_project_path=$_SERVER["DOCUMENT_ROOT"]; // "C:/inetpub/wwwroot/InvoiceCheck/";
$_project_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_project_path);
$_project_path = rtrim($_project_path, DIRECTORY_SEPARATOR);
$paths = explode(PATH_SEPARATOR, get_include_path());
$paths = array_map('realpath', $paths); // Normaliza rutas
if (!in_array(realpath($_project_path), $paths)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $_project_path);
}
$_SERVER['REMOTE_ADDR'] = "localhost";
require_once "clases/DBObject.php";
require_once "clases/InfoLocal.php";

$infObj=new InfoLocal();

echo "TEST: ".$infObj->getWhereCondition("id", ["25","28","31"]).PHP_EOL;
echo "LOG: ".$infObj->log.PHP_EOL;
