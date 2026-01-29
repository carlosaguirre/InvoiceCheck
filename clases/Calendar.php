<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Calendar extends DBObject {
    function __construct() {
        $this->tablelist = [ "appointment", "corporate", "master" ];
        $this->tablename      = "calendar_appointment";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "type", "value", "beginDate", "endDate", "beginTime", "endTime", "description", "editedTS");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['editedTS'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Calendar Appointment xxxxxxxxxxxxxx //\n";
        $this->appointment = ["step"=>15,"begin"=>9,"end"=>18];
        $this->baseDay = [];
        for ($h=$this->appointment["begin"]; $h<$this->appointment["end"]; $h++) {
            for ($m=0; $m<60; $m+=$this->appointment["step"])
                $this->baseDay[]=str_pad($h,2,"0",STR_PAD_LEFT).":".str_pad($m,2,"0",STR_PAD_LEFT);
        }
    }
    function toAppointment() {
        $this->tablename = "calendar_appointment";
    }
    function toCorporate() {
        $this->tablename = "calendar_corporate";
    }
    function toMaster() {
        $this->tablename = "calendar_master";
    }
    function populate($year) {
        $this->toMaster();
        $data = $this->getData("type=\"yearly\"",0,"value,description"); // type,beginDate,endDate,beginTime,endTime, // in (\"daily\",\"weekly\", )
        $this->toAppointment();
        $this->deleteRecord(["type"=>"official","value"=>$year]);
        $valuesArray=[];
        foreach ($data as $row) {
            $date = $year."-".date("m-d",strtotime($row["value"]));
            foreach($this->baseDay as $timeStep) {
                $endTime=date("H:i",900+strtotime($timeStep));
                $valuesArray[]=["official",$year,$date,$date,$timeStep,$endTime,$row["description"]];
            }
            // todos son yearly
            /*switch($row["type"]) {
                case "daily": break;
                case "weekly": break;
                case "yearly": break;
            }*/
        }
        $this->insertMultipleRecords(["type","value","beginDate","endDate","beginTime","endTime","description"], $valuesArray);
    }
    function getOccupied($year, $month) {
        $data = $this->getData("type in (\"temporal\",\"appointment\",\"special\",\"official\") and beginDate>=\"$year-$month-01\" and endDate<=last_day(\"$year-$month-01\")", 0, "type,beginDate, beginTime, endTime, description");
        $occupied=[]; // occupiedDay=>availableHours
        $details=[];
        $availableTime=[];
        foreach ($data as $row) {
            $bdt=$row["beginDate"];
            $btm=$row["beginTime"];
            if (isset($btm[5])) $btm=substr($btm, 0, 5);
            /*
            if (!isset($occupied[$bdt])) $occupied[$bdt]=[$btm];
            else if (isset($occupied[$bdt][0])) $occupied[$bdt][]=$btm;
            if (!isset($availableTime[$bdt])) $availableTime[$bdt]=$this->baseDay;
            $key=array_search($btm, $availableTime[$bdt]);
            if ($key!==false) {
                array_splice($availableTime[$bdt],$key,1);
            }
            if (!isset($availableTime[$bdt][0])) {
                $occupied[$bdt]=[];
                if ($row["type"]==="official" || $row["type"]==="special")
                    $details[$bdt]=$row["description"];
                else
                    $details[$bdt]="Saturado";
            }
            */
            if (!isset($occupied[$bdt])) $occupied[$bdt]=$this->baseDay;
            $key=array_search($btm, $occupied[$bdt]);
            if ($key!==false) array_splice($occupied[$bdt],$key,1);
            if (!isset($occupied[$bdt][0])) {
                if ($row["type"]==="official" || $row["type"]==="special")
                    $details[$bdt]=$row["description"];
                else
                    $details[$bdt]="Saturado";
            }
        }
        $currentDate=date("Y-m-d");
        $currentTime=date("H:i");
        $currentTS=strtotime("$currentDate $currentTime");
        $beginTS=strtotime("$currentDate 09:00");
        $hasStarted=($beginTS<$currentTS);
        if ($hasStarted) {
            $todayNeedFix=false;
            if (!isset($occupied[$currentDate])) $occupied[$currentDate]=$this->baseDay;
            foreach($occupied[$currentDate] as $i=>$timeStep) {
                $stepTS=strtotime("$currentDate $timeStep");
                $hasStarted=($stepTS<$currentTS);
                if ($hasStarted) {
                    unset($occupied[$currentDate][$i]);
                    $todayNeedFix=true;
                }
            }
            if ($todayNeedFix) $occupied[$currentDate]=array_values($occupied[$currentDate]);
        }
        return ["occupied"=>$occupied,"details"=>$details,"log"=>"currDt=$currentDate,currTm=$currentTime,currTS=$currentTS,beginTS=$beginTS,started=".($hasStarted?"YES":"NO")];
    }
}
/*
if ("calendar"===strtolower($_GET["test"]??"")) {
    echo json_encode($_GET);
    echo "<br>\n";
    $calObj=new Calendar();
    switch($_GET["action"]??"") {
        case "populate": 
            $year=$_GET["year"]??date("Y");
            $calObj->toMaster();
            $data = $calObj->getData("type=\"yearly\"",0,"value,description");
            $calObj->toAppointment();
            $valuesArray=[];
            foreach ($data as $row) {
                $date = $year."-".date("m-d",strtotime($row["value"]." ".$year));
                foreach($calObj->baseDay as $timeStep) {
                    $endTime=date("H:i",900+strtotime($timeStep));
                    echo "OFFICIAL V='$row[value]', Y='$year', D='$date', T='$timeStep', E='$endTime', X='$row[description]' <br>\n";
                }
            }
            break;
        case "occupied":
            $x=$calObj->getOccupied($y, $m);
            if (!isset($x)) echo "unset; ";
            else if (!$x) echo "falsy; ";
            else if (!is_array($x)) echo gettype($x).":".$x."; ";
            else {
                $j=json_encode($x);
                if ($j===false) {
                    echo json_last_error().":".json_last_error_msg();
                    echo "<br>\n";
                    if (isset($x["occupied"])) echo "occupied='".json_encode($x["occupied"])."', <br>\n";
                    if (isset($x["details"])) {
                        echo "details[";
                        echo gettype($x["details"])." ".count($x["details"])."]={";
                        $frst=true;
                        foreach ($x["details"] as $key => $value) {
                            if ($frst) $frst=false;
                            else echo", ";
                            echo $key="'".$value."'";
                        }
                        echo "}, <br>\n";
                    }
                    if (isset($x["log"])) echo "log='$x[log]'";
                } else echo $j;
            }
            break;
    }
}
*/
