<?php
require_once dirname(__DIR__)."/bootstrap.php";

$arr0=["archivo"=>"archivos/LAISA/2022/05/ST_4947CAS1401075K0.pdf"];
$txt0=json_encode($arr0);
echo "ARREGLO 0 = ";
echo $arr0;
echo "<br>";
echo "TEXT 0 = $txt0<br>";
echo "ARR0[archivo]=".$arr0["archivo"]."<br>";
echo "<hr>";
try {
    $text="{\"archivo\":\"archivos\/LAISA\/2022\/05\/ST_4947CAS1401075K0.pdf\"}";
    $arr=json_decode($text,true);
    echo "TEXTO = $text<br>";
    if (isset($arr["archivo"])) {
        echo "ARCHIVO = ".$arr["archivo"]."<br>";
    } else echo "NO ARCHIVO<br>";
} catch (Exception $ex) {
    echo "EXCEPTION ".$ex->getMessage()."<BR>";
}
//$text1=str_replace("\/", "\\\\\/", $text);
$text1=str_replace("\\", "", $text);
echo "<hr>";
$arr1=json_decode($text1,true);
echo "TEXT 1 = ".$text1."<br>";
echo "ARCHIVO1 = ".$arr1["archivo"]."<br>";
