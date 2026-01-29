<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Usuarios_Perfiles.php";

$obj = new Usuarios_Perfiles();
if (isValueService()) getValueService($obj);
else if (isset($_GET["clase"]) && (isset($_GET["usuario"]) || isset($_GET["perfil"]))) {
    DBi::connect();
    if (isset($_GET["usuario"])) { $keyElement="idUsuario"; $keyValue=$_GET["usuario"]; $searchElement="idPerfil"; }
    else                         { $keyElement="idPerfil"; $keyValue=$_GET["perfil"]; $searchElement="idUsuario"; }
    echo $obj->getList($keyElement, $keyValue, $searchElement);
    DBi::close();
}
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
