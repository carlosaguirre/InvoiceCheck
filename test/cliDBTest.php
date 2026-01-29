<?php
require_once dirname(__DIR__)."/bootstrap.php";
$_noDie=true;
require_once "consultas/CajaChica.php";
require_once "clases/RepViaConceptos.php";
global $query;
$query="INI";
$rvcObj=new RepViaConceptos();
$rvcObj->rows_per_page=1000;
$rvcData=$rvcObj->getData("uuid is null and archivoxml is not null and fechafactura>\"2022-10-01 00:00:00\"");
$filepath="X:/invoiceDocs/viajes/";
$maxRows=$rvcObj->rows_per_page;
$numMaxDigits=strlen("$maxRows");
foreach ($rvcData as $idx => $row) {
    $abspath=$filepath.$row["archivoxml"];
    $num=$idx+1;
    $padNum=str_pad("$num", $numMaxDigits, " ", STR_PAD_LEFT);
    echo "$padNum - $row[id]) ";
    if (file_exists($abspath)) {
        $xmlData=getCFDIData($abspath, $row["archivoxml"], false);
        if (isset($xmlData["uuid"][0])) {
            if ($rvcObj->saveRecord(["id"=>$row["id"],"uuid"=>$xmlData["uuid"]])===false) {
                echo "No se guardó el cambio '$abspath'. id=$row[id], uuid=$xmlData[uuid]: QUERY=$query, ERRORS=".json_encode(DBi::$errors)," ERRNO=".DBi::$errno.", ERROR=".DBi::$error;
            } else {
                echo "UUID $xmlData[uuid] guardado satisfactoriamente!";
            }
        } else if (isset($xmlData)) {
            echo "No tiene UUID '$abspath' => ".json_encode($xmlData);
            echo ". XMLDATA:";
            foreach ($xmlData as $key => $value) {
                echo "\n - $key : ";
                if (is_array($value) || is_object($value)) echo json_encode($value);
                else echo $value;
            }
        } else echo "No se generó información del archivo '$abspath";
    } else {
        echo "No existe archivo '$abspath'";
    }
    echo "\n";
}
