<?php
echo "<meta charset='utf-8'>\n";

//echo "<p>".date()."</p>";

//$code=["es_MX.UTF-8","es_MX","esl"];
//setlocale(LC_TIME,$code[0],$code[1],$code[2]);
//$time=strftime("%d de %B de %Y, a las %H:%M horas");
//$text="<p>Revisión practicada el día ".iconv('ISO-8859-2', 'UTF-8',$time)."</p>";
//echo $text;
//if (preg_match("(\d+) de (\w+) de (\d+), a las (\d+):(\d+)",$op,$matches)===1) {}
//echo "<p>".strftime("%e %b, %Y")."</p>";

$today=strftime("%Y-%m-%d");
$todayISO=str_replace("-",".",$today);
$todayTS=strtotime($today);
$today3D=date('Y-m-d',strtotime('+90 days',$todayTS));
$today3D2=date('Y-m-d',strtotime($today.' +90 days'));
$generaopinion="2021-03-24";
$opinionISO=str_replace("-",".",$generaopinion);
$opinionTS=strtotime($generaopinion);
$opinion3D=date('Y-m-d',strtotime('+90 days',$opinionTS));
$opinion3D2=date('Y-m-d',strtotime($generaopinion.' +90 days'));

$test="2021-03-03";
$testISO=str_replace("-", ".", $test);
$testTS=strtotime($test);
$test3D=date('Y-m-d',strtotime('+90 days',$testTS));
$test3D2=date('Y-m-d',strtotime($test.' +90 days'));

echo "<table><thead><tr><th>TIPO</th><th>Y-M-D</th><th>Y.M.D</th><th>StrToTime</th><th>+90dias</th><th>+90dias2</th></tr></thead>";
echo "<tbody><tr><td>TODAY</td><td>$today</td><td>$todayISO</td><td>$todayTS</td><td>$today3D</td><td>$today3D2</td></tr>";
echo "<tr><td>OPINION</td><td>$generaopinion</td><td>$opinionISO</td><td>$opinionTS</td><td>$opinion3D</td><td>$opinion3D2</td></tr>";
echo "<tr><td>TEST</td><td>$test</td><td>$testISO</td><td>$testTS</td><td>$test3D</td><td>$test3D2</td></tr></tbody></table>";
/*
echo "11.12.10 = ".date("jS F, Y", strtotime("11.12.10"))."<br>";
echo "11/12/10 = ".date("jS F, Y", strtotime("11/12/10"))."<br>";
echo "11-12-10 = ".date("jS F, Y", strtotime("11-12-10"))."<br>";
echo date("jS F, Y", $opinionTS)."<br>";
echo date("jS F, Y", $todayTS)."<br>";
*/
