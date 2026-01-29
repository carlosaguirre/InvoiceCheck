<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
$alertUid=["2146","1083"];
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj=new Usuarios();
}
$result = $usrObj->saveRecord(["id"=>$alertUid,"banderas"=>new DBExpression("banderas|2")]);
if (is_bool($result)) {
    if ($result) echo "Usuarios modificados satisfactoriamente!";
    else echo "Usuarios no modificados";
} else echo "Resultado recibido: ".json_encode($result);
*/
echo " NN & 2 = ?? - NN | 2 = ??";
for ($i=0; $i<32; $i++) {
    $strI="".$i;
    if (!isset($strI[1])) $strI="0".$strI;
    echo "<br> $strI & 2 = ".($i&2)." | $strI | 2 = ".($i|2);
}
echo "<br>";