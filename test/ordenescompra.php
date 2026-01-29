<?php
require_once dirname(__DIR__)."/bootstrap.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "clases/OrdenesCompra.php";
$ordObj=new OrdenesCompra();

if (isset($_POST["guardar"])) echo "<!-- ".json_encode($_POST)." -->";
else echo "<!-- NOPOST -->";
// toDo: ver si existe POST y guardar todos los que no tengan moneda=MXN

$ordData=$ordObj->getData("moneda is null");
?>
<!DOCTYPE html>
<html>
<head>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title>Test Ordenes Compra sin moneda</title>
</head>
<body>
<?php
if (!isset($ordData[0])) echo "<p>No hay órdenes válidas</p>";
else {
    $monedaOI=" <input type=\"button\" id=\"%ID%\"%SOLID% value=\"MXN\" onclick=\"this.value=(this.value==='MXN'?'USD':(this.value==='USD'?'EUR':'MXN'));this.nextElementSibling.value=this.value;\"><input type=\"hidden\" name=\"%ID%\" value=\"MXN\">";
    // toDo: Agregar atributos a forma para hacer submit a la misma página
    echo "<form method=\"post\" target=\"_self\"><table border=\"1\"><thead><tr><th>ID</th><th>FOLIO</th><th>IDPRV</th><th>FECHA</th><th>PDF</th><th>IMPORTE</th><th>ST</th></tr></thead><tbody>";
    $ordLen=count($ordData);
    $padId=($ordLen<100)?2:3;
    foreach ($ordData as $idx => $row) {
        $monBtn=str_replace(["%ID%","%SOLID%"], ["SOL".str_pad(($idx+1), $padId, "0", STR_PAD_LEFT)," solId=\"$row[id]\""], $monedaOI);
        $archivo="<A href=\"$row[rutaArchivo]$row[nombreArchivo].pdf\" target=\"pdf\"><IMG src=\"imagenes/icons/pdf200.png\" width=\"20\" height=\"20\"></A>";
        echo "<tr><td>$row[id]</td><td>$row[folio]</td><td>$row[idProveedor]</td><td>".substr($row["fecha"],0,10)."</td><td>$archivo</td><td style=\"text-align: right;\">".formatCurrency($row["importe"]).$monBtn."</td><td>$row[status]</td>";
    }
    echo "</tbody></table><button type=\"submit\" value=\"guardar\" name=\"guardar\">GUARDAR</button></form>";
}
?>
</body>
</html>
