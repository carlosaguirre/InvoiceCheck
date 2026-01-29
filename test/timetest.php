<?php
error_reporting(E_ALL);
//echo "timetest\n";
$mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");

$tzs=["America/Tijuana","America/Hermosillo",
"Mexico/BajaNorte","MST","Mexico/BajaSur","Etc/GMT+6",
"America/Mazatlan","America/Ciudad_Juarez",
"America/Chihuahua","America/Ojinaga","America/Bahia_Banderas",
"America/Monterrey","America/Mexico_City","America/Merida",
"America/Matamoros","America/Cancun"];

$fmtTzs=[];
foreach ($tzs as $key => $value) {
    date_default_timezone_set($value);
    $fmt = (new DateTime())->format("H:i:s");
    if (isset($fmtTzs[$fmt])) $fmtTzs[$fmt][]=$value;
    else $fmtTzs[$fmt]=[$value];
}
echo "<html><head><title>Time Test</title><style>td,th{font-size:32px;font-weight:700;}td{border:1px solid gray;}</style></head><body><table><thead><tr><th>TimeZone</th><th>DateTime</th></tr></thead><tbody>";
foreach ($fmtTzs as $fky => $tzG) {
    echo "<tr><td>".implode("<br>", $tzG)."</td><td>$fky</td></tr>";
}
echo "</tbody></table></body></html>";
