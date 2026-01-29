<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Facturas.php";
clog2ini("noCCPList");
clog1seq(1);
$invObj=new Facturas();
if (isset($_POST["pageno"])) $invObj->pageno=$_POST["pageno"];
if (isset($_POST["limit"])) $invObj->rows_per_page=$_POST["limit"];
else $invObj->rows_per_page=100;
$where=""; $order=""; $exacto=[];
if (isset($_POST["exacto"])) $exacto=explode(",", $_POST["exacto"]);
if (isset($_POST["param"])) {
    $pars=[];
    foreach ($_POST["param"] as $ky) {
        if (isset($_POST[$ky])) $pars[$ky]=$_POST[$ky];
    }
    $where=DBi::params2Where($pars,$exacto,true);
}
if (!isset($order[0])) $order="id";
$invObj->addOrder($order);
global $query;
$invData=$invObj->getData($where);
$progressSeparator=$_POST["progressSeparator"]??"***";
echo json_encode(["data"=>$invData,"pageno"=>$invObj->pageno+1]).$progressSeparator;
clog1seq(-1);
clog2end("noCCPList");
