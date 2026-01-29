<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Proceso.php";

$obj = new Proceso();
if (isValueService()) getValueService($obj);
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
else if (isRecordService()) getRecordService($obj);
else if (isListService()) getListService($obj);
else echo "<!-- NO PROCESO -->";

function isRecordService() {
    return isset($_POST["action"])&&$_POST["action"]==="record";
} // action=record,module=covid,case=infected,zone=MEXICO
function getRecordService($prcObj) {
    global $query;
    if (isset($_POST["module"])) switch($_POST["module"]) {
        case "covid": $result=$prcObj->insertRecord(["modulo"=>"COVID","identif"=>"0","status"=>"Vista","detalle"=>($_POST["case"]??"DESCONOCIDO")." ".($_POST["zone"]??"DESCONOCIDO"),"fecha"=>date("Y-m-d H:i:s"),"usuario"=>hasUser()?getUser()->nombre:"no user", "region"=>getIP()]);
            echo json_encode(["result"=>($result?"success":"failure"),"query"=>$query,"error"=>($prcObj->errors??[])]);
            break;
        default: echo "WRONG CASE: $_POST[module]";
    } else echo "NO MODULE";
}
function isListService() {
    return isset($_POST["action"])&&$_POST["action"]==="list";
}
function getListService($prcObj) {
    global $query;
    if (isset($_POST["module"])) switch($_POST["module"]) {
        case "covid":
            $query="SELECT coalesce(u.persona,\"PUBLICO\") usuario, ".
                "coalesce(p2.hoy,0) hoy, ".
                "coalesce(p3.ayer,0) ayer, ".
                //"coalesce(p4.semana,0) semana, ".
                //"coalesce(p5.mes,0) mes, ".
                //"coalesce(p6.anio,0) anio, ".
                //"coalesce(p7.siempre,0) siempre, ".
                "coalesce(p8.casos,0) casos, ".
                "coalesce(p9.muertos,0) muertos, ".
                "coalesce(pa.enfermos,0) enfermos, ".
                "coalesce(pb.curados,0) curados ".
                "from (select distinct usuario usr from proceso ".
                    "where modulo=\"COVID\" order by usuario) p1 ".
                "left join usuarios u on p1.usr=u.nombre ".
                "left join (select usuario usr,count(1) hoy ".
                    "from proceso ".
                    "where modulo=\"COVID\" and fecha>=curdate() ".
                    "group by usuario) p2 on p1.usr=p2.usr ".
                "left join (select usuario usr,count(1) ayer ".
                    "from proceso ".
                    "where modulo=\"COVID\" ".
                    "and fecha between (curdate() - INTERVAL 1 DAY) ".
                    "and curdate() group by usuario) p3 ".
                    "on p1.usr=p3.usr ".
                /*"left join (select usuario usr,count(1) semana ".
                    "from proceso ".
                    "where modulo=\"COVID\" and fecha>(curdate() - INTERVAL 7 DAY) group by usuario) p4 ".
                    "on p1.usr=p4.usr ".
                "left join (select usuario usr,count(1) mes ".
                    "from proceso ".
                    "where modulo=\"COVID\" and fecha>(curdate() - INTERVAL 30 DAY) group by usuario) p5 ".
                    "on p1.usr=p5.usr ".
                "left join (select usuario usr,count(1) anio ".
                    "from proceso ".
                    "where modulo=\"COVID\" and fecha>(curdate() - INTERVAL 365 DAY) group by usuario) p6 ".
                    "on p1.usr=p6.usr ".
                "left join (select usuario usr,count(1) siempre ".
                    "from proceso ".
                    "where modulo=\"COVID\" group by usuario) p7 ".
                    "on p1.usr=p7.usr ".*/
                "left join (select usuario usr,count(1) casos ".
                    "from proceso ".
                    "where modulo=\"COVID\" and detalle like \"Casos%\" group by usuario) p8 ".
                    "on p1.usr=p8.usr ".
                "left join (select usuario usr,count(1) muertos ".
                    "from proceso ".
                    "where modulo=\"COVID\" and detalle like \"Muertos%\" group by usuario) p9 ".
                    "on p1.usr=p9.usr ".
                "left join (select usuario usr,count(1) enfermos ".
                    "from proceso ".
                    "where modulo=\"COVID\" and detalle like \"Enfermos%\" group by usuario) pa ".
                    "on p1.usr=pa.usr ".
                "left join (select usuario usr,count(1) curados ".
                    "from proceso ".
                    "where modulo=\"COVID\" and detalle like \"Recuperados%\" group by usuario) pb ".
                    "on p1.usr=pb.usr";
            $result = DBi::query($query);
            $errno=DBi::getErrno();
            $error=DBi::getError();
            if (isset($errno) && isset($error)) {
                $error=$errno." : ".$error;
            } else $error="";
            $prcData=[];
            while ($row = $result->fetch_assoc()) {
                $prcData[] = $row;
            }
            if ($result) $result->close();
            /*
            if (isset($_POST["sortby"])) {
                $prcObj->clearOrder();
                if (isset($_POST["sortdir"])) $prcObj->addOrder($_POST["sortby"],$_POST["sortdir"]);
                else $prcObj->addOrder($_POST["sortby"]);
            }
            $prcData=$prcObj->getData("modulo=\"COVID\"",0,"fecha,usuario,detalle");
            */
            if (isset($prcData[0])) {
                echo json_encode(["result"=>"success","list"=>$prcData,"query"=>$query,"error"=>$error]);
            } else echo json_encode(["result"=>"failure","query"=>$query,"error"=>$error]);
            break;
        default: echo json_encode(["result"=>"failure","query"=>"","error"=>["WRONG CASE: $_POST[module]"]]);
    } else echo json_encode(["result"=>"failure","query"=>"","error"=>["NO MODULE"]]);
}
