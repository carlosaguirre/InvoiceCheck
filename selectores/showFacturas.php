<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Facturas.php";
clog2ini("showFacturas");
clog1seq();

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

function fillData($table, $row) {
    if (!isset($_GET["tabla"]) && !isset($_GET["datos"]))
        return "alert('".$table."[".$row["id"]."] = ".$row["pedido"]." | ".$row["codigoProveedor"]." | ".$row["rfcGrupo"]."')";
    $linea = NULL;
    $retVal = "fillMappedValue({factura_id: '".$row["id"]."'";
    $retVal .=            ", factura_field: '".$row["pedido"]."'";
    $retVal .=            ", factura_codePrv: '".$row["codigoProveedor"]."'";
    $retVal .=            ", factura_rfcGpo: '".$row["rfcGrupo"]."'";
    $retVal .=            ", factura_status: '".$row["status"]."'";
    $retVal .=            "});";
    $retVal .=            " overlay();";
    return $retVal;
}

$invObj = new Facturas();
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
    $invObj->pageno = $_REQUEST["pageno"];
}
if (isset($_REQUEST["limit"])) {
    $invObj->rows_per_page = $_REQUEST["limit"];
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
$data = $invObj->getData($where);
// if ($invObj->numrows > 0) {
    if (!isset($_GET["datos"])) {
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $invObj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showFacturas">
      <table>
        <thead>
          <tr>
            <th>Pedido</th><th>C&oacute;digo Prv</th><th>RFC Gpo</th><th>UUID</th><th>Status</th>
          </tr>
          <tr>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="pedido" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="codigoProveedor" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="rfcGrupo" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="uuid" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="status" class="longtext filter_box"></th>
          </tr>
        </thead>
        <tbody id="dialog_tbody">
<?php
    }
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $invObj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $invObj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $invObj->lastpage ?>" />
<?php
    foreach ($data as $row) {
        $dblclickEvent = fillData('transporte', $row);
?>
          <tr>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['pedido'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['codigoProveedor'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['rfcGrupo'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['uuid'] ?></td>
            <td ondblclick="<?= $dblclickEvent ?>"><?= $row['status'] ?></td>
          </tr>
<?php
    }
?>
          <tr><th></th><th></th><th></th><th></th><th></th></tr>
<?php
    if (!isset($_GET["datos"])) {
?>
        </tbody>
        <tfoot id="dialog_tfoot">
          <tr>
            <th colspan="5">
              <input type="button" id="navToFirst"    class="navOverlayButton" value="<<"  onclick="fillSelectorContents('first')">
              <input type="button" id="navToPrevious" class="navOverlayButton" value=" < " onclick="fillSelectorContents('prev')">
              <span id="paginationIndexes" class="fontPageFormat"> <?= $invObj->pageno ?>/<?= $invObj->lastpage ?> </span>
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
