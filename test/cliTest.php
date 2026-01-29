<?php
// cliTest.php
$_SERVER["include_path"]=get_include_path();
$_SERVER["__FILE__"]=__FILE__;
$_SERVER["__DIR__"]=__DIR__;
function aoscmp($a, $b) {
    if (is_array($a)||is_object($a)) $a=json_encode($a);
    if (is_array($b)||is_object($b)) $b=json_encode($b);
    return strnatcasecmp($a, $b);
}
uasort($_SERVER,"aoscmp");
foreach ($_SERVER as $key => $value) {
    if (is_array($value)||is_object($value))
        echo "$key => ".json_encode($value)."\n";
    else
        echo "$key => '$value'\n";
}
