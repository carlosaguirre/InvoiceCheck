<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Doctos.php";

$docObj = new Doctos();
if (isValueService()) getValueService($docObj);
else if (isTestService()) getTestService($docObj);
else if (isCatalogService()) getCatalogService($docObj);
else if (isActionService()) doActionService();
die();
function isActionService() {
    return isset($_POST["action"]);
}
function doActionService() {
    global $docObj;
    sessionInit();
    if (!hasUser()) {
        echo json_encode(["result"=>"refresh","action"=>"refresh"]);
        die();
    }
    switch($_POST["action"]) {
        case "paylist": sendPaymList();
            break;
        default: echo "ERROR:Petición inválida ($_POST[action])";
    }
}
function sendPaymList() {
    global $invObj;
    if (!isset($invObj)) {
        require_once "clases/Facturas.php";
        $invObj=new Facturas();
    }
    $invObj->rows_per_page=100;
    if (isset($_POST["pageno"])) $invObj->pageno=+$_POST["pageno"];
    $proveedor=$_POST["codprov"]??"";
    do {
        flush_buffers();
        usleep(1000);
        $noCCPQuery="codigoProveedor=\"$proveedor\" and tipoComprobante=\"i\" and metodoDePago=\"PPD\" and idReciboPago is null and statusn between 32 and 127 and (fechaPago is null or fechaPago>\"2018-09-01 00:00:00\")";
        $noCCPData=$invObj->getData($noCCPQuery, 0, "id,ubicacion,uuid,serie,folio,fechaFactura,total,moneda,nombreInterno,nombreInternoPDF,ea,version,statusn");
        if (isset($noCCPData[0])) {
            echo json_encode(["data"=>$noCCPData,"page"=>$invObj->pageno])."|*CCP*|";
            $invObj->pageno++;
        }
    } while (isset($noCCPData[0]));
    flush_buffers(false);
}
