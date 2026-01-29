<?php
// cliTest.php
$cfFecha="2023-03-31 16:58:38";
$cfTime=strtotime($cfFecha);
echo "strtotime: ".$cfTime."\n";
//echo "strftime: ".strftime("%Y-%m-%d",strtotime($cfFecha))."\n";
date_default_timezone_set('UTC');
date_default_timezone_set("Etc/GMT+6");
setlocale(LC_TIME, 'spanish');
//echo "strftime: ".strftime("%#d de %B del %Y", $cfTime)."\n";
////echo "    date: ".date("d \d\e\e F \d\e\l Y",$cfTime)."\n";
$formatter = new IntlDateFormatter('spanish', IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Etc/GMT+6", IntlDateFormatter::GREGORIAN, "d MMM., Y");
//echo "formattr: ".$formatter->format($cfTime);
//echo "strftime: ".strftime("%Y-%m-%d",$cfTime)."\n";
//echo "    date: ".date("Y-m-d",$cfTime);
echo "strftime: ".strftime("%e %b, %Y")."\n";
echo "    date: ".$formatter->format(time());