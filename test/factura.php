<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()||!validaPerfil("Administrador")) {
    if (hasUser()) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
require_once "clases/Facturas.php";
$invObj = new Facturas();
$invObj->rows_per_page = 0;
$facData = $invObj->getDataByFieldArray($_GET);
echo "<b>GET:</b>".arr2List($_GET);
if (isset($query)) echo "<hr><b>Query:</b> ".$query;
echo "\n<!-- ".$invObj->log." -->";
echo "<br><b>Num. Registros:</b> ".$invObj->affectedrows;
if(isset($facData[0])) {
    echo "<hr><b>1er. Factura:<b>";
    echo arr2List($facData[0]);
    $statusN = $facData[0]["statusn"];
    echo "<hr><b>Status Desglosado $statusN:</b>";
    echo "<ul>";
    if ($invObj->estaAceptado($statusN)) echo "<li>Aceptado</li>";
    if ($invObj->estaContrarrecibo($statusN)) echo "<li>Contra-recibo</li>";
    if ($invObj->estaExportado($statusN)) echo "<li>Exportado</li>";
    if ($invObj->estaRespaldado($statusN)) echo "<li>Respaldado</li>";
    if ($invObj->estaProgPago($statusN)) echo "<li>Programado para Pago</li>";
    if ($invObj->estaPagado($statusN)) echo "<li>Pagado</li>";
    if ($invObj->estaRecPago($statusN)) echo "<li>Recibido Pago</li>";
    if ($invObj->estaRechazado($statusN)) echo "<li>Rechazado</li>";
    echo "</ul>";
}
