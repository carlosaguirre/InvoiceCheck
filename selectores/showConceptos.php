<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Conceptos.php";
clog2ini("showConceptos");
clog1seq();

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

function fillData($table, $row) {
    if (!isset($_GET["tabla"]) && !isset($_GET["datos"]))
        return "alert('".$table."[".$row["id"]."] = ".$row["pedido"]." | ".$row["codigoArticulo"]." | ".$row["cantidad"]." | ".$row["unidad"]." | ".$row["descripcion"]." | ".$row["precioUnitario"]." | ".$row["importe"]."')";
    $linea = NULL;
    $retVal = "fillMappedValue({concepto_id: '".$row["id"]."'";
    $retVal .=            ", concepto_pedido: '".$row["pedido"]."'";
    $retVal .=            ", concepto_code: '".$row["codigoArticulo"]."'";
    $retVal .=            ", concepto_cantidad: '".$row["cantidad"]."'";
    $retVal .=            ", concepto_unidad: '".$row["unidad"]."'";
    $retVal .=            ", concepto_descripcion: '".$row["descripcion"]."'";
    $retVal .=            ", concepto_unitario: '".$row["unitario"]."'";
    $retVal .=            ", concepto_importe: '".$row["importe"]."'";
    $retVal .=            "});";
    $retVal .=            " overlay();";
    return $retVal;
}

$cptObj = new Conceptos();
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) {
?>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8" />
    <title><?= $systemTitle ?></title>
    <link href="css/general.php" rel="stylesheet" type="text/css" />
<?php
    require_once "templates/generalScript.php";
    echoGeneralScript();
?>
    <script>
      window.onload = fillPaginationIndexes;
    </script>
  </head>
  <body>
    <div id="dialog_resultarea">
<?php
}
if (isset($_REQUEST["pageno"])) {
    $cptObj->pageno = $_REQUEST["pageno"];
}
if (isset($_REQUEST["limit"])) {
    $cptObj->rows_per_page = $_REQUEST["limit"];
}
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        if ($value = $_REQUEST[$pvalue]) {
            if (strlen($where)>0) $where .= " AND ";
            $where .= $pvalue . " LIKE '%" . $value . "%'";
        }
    }
}
$data = $cptObj->getData($where);
// if ($cptObj->numrows > 0) {
    if (!isset($_GET["datos"])) {
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $cptObj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showConceptos">
      <table>
        <thead>
          <tr>
            <th>Pedido</th><th>Art&iacute;culo</th><th>Cantidad</th><th>Unidad</th><th>Descripci&oacute;n</th><th>$ Uni</th><th>Importe</th>
          </tr>
          <tr>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="pedido" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="codigoArticulo" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="cantidad" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="unidad" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="descripcion" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="precioUnitario" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="importe" class="longtext filter_box"></th>
          </tr>
        </thead>
        <tbody id="dialog_tbody">
<?php
    }
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $cptObj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $cptObj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $cptObj->lastpage ?>" />
<?php
    foreach ($data as $row) {
        $dblclickEvent = fillData('transporte', $row);
?>
          <tr>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['pedido'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['codigoArticulo'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['cantidad'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['unidad'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['descripcion'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['precioUnitario'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['importe'] ?></td>
          </tr>
<?php
    }
?>
          <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
<?php
    if (!isset($_GET["datos"])) {
?>
        </tbody>
        <tfoot id="dialog_tfoot">
          <tr>
            <th colspan="7">
              <input type="button" id="navToFirst"    class="navOverlayButton" value="<<"  onclick="fillSelectorContents('first')">
              <input type="button" id="navToPrevious" class="navOverlayButton" value=" < " onclick="fillSelectorContents('prev')">
              <span id="paginationIndexes" class="fontPageFormat"> <?= $cptObj->pageno ?>/<?= $cptObj->lastpage ?> </span>
              <input type="button" id="navToNext"     class="navOverlayButton" value=" > " onclick="fillSelectorContents('next')">
              <input type="button" id="navToLast"     class="navOverlayButton" value=">>"  onclick="fillSelectorContents('last')">
            </th>
          </tr>
        </tfoot>
      </table>
<?php
    }
// }
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) {
?>
    </div><div id="mylog" class="hidden"></div>
  </body>
</html>
<?php
}

include_once ("configuracion/finalizacion.php");
clog1seq(-1);
clog2end("showFacturas");
