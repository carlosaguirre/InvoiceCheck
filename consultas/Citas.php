<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Calendar.php";
$calObj = new Calendar();
if (isValueService()) getValueService($calObj);
else if (isTestService()) getTestService($calObj);
else if (isCatalogService()) getCatalogService($calObj);
else if ( isset($_REQUEST["action"]) ) {
    switch($_REQUEST["action"]) {
        case "refresh": refreshCalendar(); break;
        case "populate": populateCalendar(); break;
        case "request": requestAppointment(); break;
    }
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function refreshCalendar() {
    global $calObj;
    $year=$_REQUEST["year"]??date("Y");
    $month=$_REQUEST["month"]??date("m"); // m:00
    //$date=$_REQUEST["date"]??date("d"); // d:00
    //if (isset($_REQUEST["return"])) {
        switch($_REQUEST["return"]??"") {
            case "occupied":
                echo json_encode($calObj->getOccupied($year,$month));
        }
    //}
}
function populateCalendar() {
    global $calObj;
    if (isset($_REQUEST["year"])) $year=$_REQUEST["year"];
    else $year=date("Y");
    $calObj->populate($year);
    $data=$calObj->getData("type='official' and value='$year'");
    foreach ($data as $row) {
        echo json_encode($row)."<br>";
    }
}
function requestAppointment() {
    global $calObj;
    $apptDate=$_POST["apptDate"]??"";
    $beginTime=$_POST["apptTime"]??"";
    if (!isset($apptDate[0])||!isset($beginTime[0])) {
        echo json_encode(["error"=>"1"]);
        return;
    }
    if (!hasUser()) {
        echo json_encode(["error"=>"2"]);
        return;
    }
    $endTime=date("H:i",900+strtotime($beginTime));
    if ($calObj->saveRecord(["type"=>"temporal","value"=>"1","beginDate"=>$apptDate,"endDate"=>$apptDate,"beginTime"=>$beginTime,"endTime"=>$endTime,"description"=>"user:".getUser()->nombre])) {
        echo json_encode(["success"=>"1"]);
    } else {
        echo json_encode(["error"=>$calObj->errors,"log"=>$calObj->log]);
    }
}
