<?php
require_once dirname(__DIR__)."/bootstrap.php";
//clog2ini("generico");
//clog1seq(1);
if (!hasUser()) reloadNDie("Sin usuario");
$result=[];
if (isset($_POST["module"][0])) {
    switch($_POST["module"]) {
        case "NOCCP": // readyService(\"selectores/generico.php\", {module:\"NOCCP\",totRows:-1, rdyFnc:showOnReady, errFnc:showOnError}, showOnReady, showOnError);

                // $invObj->getData(                                           "f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and f.idReciboPago is null and f.statusn between 32 and 127 and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\") and p.status!='inactivo'",0,
                // "COUNT(DISTINCT f.codigoProveedor,f.version,f.ciclo) AS totRows",                  "f inner join proveedores p on f.codigoProveedor=p.codigo");
                //                                                             "f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and f.idReciboPago is null and f.statusn between 32 and 127 and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\") and p.status!='inactivo'" .$groupWhere, 0, 
                // "f.codigoProveedor, p.razonSocial, p.rfc, f.version, f.ciclo, count(1) n",         "f inner join proveedores p on f.codigoProveedor=p.codigo",           "f.codigoProveedor, f.version, f.ciclo"
            $result=["clase"=>"Facturas","pageno"=>"1","limit"=>"100","where"=>"f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and (f.idReciboPago is null or f.statusReciboPago>0) and f.statusn between 32 and 127 and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\") and p.status!=\"inactivo\"","countFields"=>"COUNT(DISTINCT f.codigoProveedor,f.version,f.ciclo) AS totRows","fields"=>"g.alias, f.codigoProveedor, p.razonSocial, p.rfc, f.version, f.ciclo, f.statusReciboPago, f.saldoReciboPago>0 part, right(f.ubicacion, 3) mes, count(1) n","extra"=>"f inner join proveedores p on f.codigoProveedor=p.codigo inner join grupo g on f.rfcGrupo=g.rfc","groupBy"=>"f.version,f.ciclo, g.alias, f.codigoProveedor, right(f.ubicacion, 3), f.statusReciboPago, f.saldoReciboPago>0, f.ubicacion","msgId"=>"msg","msgAs"=>"textContent","dataId"=>"noccpbody","dataAs"=>"tbrow","rowClass"=>"bbtm1d","dataFields"=>["#","codigoProveedor","razonSocial","rfc","version","ciclo","n"],"footId"=>"noccpfoot","url"=>"selectores/generico.php"];
            $_POST+=$result;
        break;
    }
}
if (!isset($_POST["clase"])) errNDie("Debe indicar la clase de datos a obtener"); 
if (isset($_POST["clase"][0])) {
    $className=$_POST["clase"];
    $classPath="clases/".$className.".php";
    require_once $classPath;
    if (class_exists($className)) $obj=new $className();
}
if (!isset($obj)) errNDie("No se pudo crear el objeto de datos '$className'");
if (isset($obj) && isset($_POST["pageno"])) $obj->pageno=$_POST["pageno"];
if (isset($obj) && isset($_POST["limit"])) $obj->rows_per_page=$_POST["limit"];
else $obj->rows_per_page=100;
$exacto=[];
$where=$_POST["where"]??"";
if (isset($_POST["exacto"])) $exacto=explode(",", $_POST["exacto"]);
if (isset($_POST["param"])) {
    $pars=[];
    foreach ($_POST["param"] as $ky) {
        if (isset($_POST[$ky])) $pars[$ky]=$_POST[$ky];
    }
    if (isset($where[0])) $where.=" and ";
    $where.=DBi::params2Where($pars,$exacto,true);
}
$fields=$_POST["fields"]??"*";
$extra=$_POST["extra"]??"";
$grpBy=$_POST["groupBy"]??"";
if (isset($_POST["totRows"])) $totrows=$_POST["totRows"];
else {
    $countFields=$_POST["countFields"]??"";
    if (isset($countFields[0])) {
//   $invObj->getData($where,0,$countFields,$extra);
        $data=$obj->getData($where,0,$countFields,$extra);
        if (isset($data[0]["totRows"])) {
            $totrows=$data[0]["totRows"];
        }
    }
}
if (!isset($totrows)) {
    $obj->exists($where,$extra,$grpBy);
    $totrows=$obj->numrows;
}
if (isset($_POST["order"])) {
    $obj->clearOrder();
    foreach ($_POST["order"] as $val) {
        $fix="asc";
        if ($val[0]==="-") {
            $val=substr($val, 1);
            $fix="desc";
        }
        $obj->addOrder($val,$fix);
    }
}
global $query;
$data=$obj->getData($where,0,$fields,$extra,$grpBy);
if (!isset($data[0])) errNDie("No se encontraron más datos",["query"=>$query]);
$result=["result"=>"success","rowsPerPage"=>$obj->rows_per_page,"lastPage"=>$obj->lastpage,"data"=>$data,"pageno"=>$obj->pageno+1,"totRows"=>$totrows,"query"=>$query];
$inclusiveSeparator=$_POST["inclusiveSeparator"]??"";
if (isset($inclusiveSeparator[0])) $result["inclusiveSeparator"]=$inclusiveSeparator;
echo json_encode($result).$inclusiveSeparator;
//clog1seq(-1);
//clog2end("generico");
