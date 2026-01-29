<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__)."/clases/Config.php";
$_pryNm = "invoice";
$_envPth = "C:/PHP/includes/.env.$_pryNm";
Config::init($_envPth);

echo json_encode([
    'success' => true,
    'test' => 1
]);
?>