<?php
error_reporting(E_ALL);

function nl() {
    return "<br>\n";
}
$cmd = "ping -n 10 127.0.0.1";
$gestor = popen("$cmd 2>&1","r");
echo "$ $cmd<br>";
echo "'$gestor'; " . gettype($gestor) . ".";
echo "<pre>";
while(!feof($gestor)) {
    $leer = fread($gestor, 4096);
    echo $leer;
}
echo "</pre>";

pclose($gestor);
echo "FIN";
