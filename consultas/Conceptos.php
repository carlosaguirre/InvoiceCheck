<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Conceptos.php";

$cptObj = new Conceptos();
if (isValueService()) getValueService($cptObj);
else if (isTestService()) getTestService($cptObj);
else if (isCatalogService()) getCatalogService($cptObj);

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";

