<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Grupo.php";
clog2ini("showGrupo");
clog1seq(1);

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

function fillData($table, $row) {
    if (!isset($_GET["tabla"]) && !isset($_GET["datos"]))
        return "alert('".$table."[".$row["id"]."] = ".$row["razonSocial"]." | ".$row["alias"]." | ".$row["rfc"]." | ".$row["cut"]." | ".$row["filtro"]."')";
    return "fillValue('grupo_id','".$row["id"]."');fillValue('grupo_field','".$row["razonSocial"]."');fillValue('grupo_alias','".$row["alias"]."');fillValue('grupo_rfc','".$row["rfc"]."');fillValue('grupo_cut','".$row["cut"]."');fillValue('grupo_status','".$row["status"]."');toggleCheck('grupo_filtro1',".(!!($row["filtro"]&&1)).");toggleCheck('grupo_filtro2',".(!!($row["filtro"]&&2)).");toggleCheck('grupo_filtro4',".(!!($row["filtro"]&&4)).");fillDataCheck();overlay();";
}

$obj = new Grupo();
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
    $obj->pageno = $_REQUEST["pageno"];
}
if (isset($_REQUEST["limit"])) {
    $obj->rows_per_page = $_REQUEST["limit"];
}
$where = "";
$order = "alias";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        $value = $_REQUEST[$pvalue];
        if (isset($value) && $value!==null && $value!==false && isset($value[0])) {
            if ($value[0]!="%" && $value[strlen($value)-1]!="%") $value = "%".$value."%";
            if (isset($value[1])) {
                if (strlen($where)>0) $where .= " AND ";
                $where .= "LOWER(".$pvalue.") LIKE LOWER('" . $value . "')";
                if ($pvalue=="razonSocial") $order="razonSocial";
                if ($pvalue=="rfc") $order="rfc";
            }
        }
    }
}
$obj->addOrder($order);
$data = $obj->getData($where);
clog2("\n".$obj->log."\n");
if ($obj->numrows > 0) {
    if (!isset($_GET["datos"])) {
        $gpoSttArr = array( ""=>"", "registrado"=>"registrado", "activo"=>"activo", "vencido"=>"vencido" );
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $obj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showGrupo">
      <table onwheel="wheelPaginate(event)">
        <thead>
          <tr>
            <th>Alias</th><th>Raz&oacute;n Social</th><th>RFC</th><th>Status</th>
          </tr>
          <tr>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="alias" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="razonSocial" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="rfc" class="longtext filter_box"></th>
            <th><select onchange="fillSelectorContents('exacto')" name="status" class="filter_box">
<?= getHtmlOptions($gpoSttArr, "", 20) ?>
            </select></th>
          </tr>
        </thead>
        <tbody id="dialog_tbody">
<?php
    }
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $obj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $obj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $obj->lastpage ?>" />
          <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="wheelLock=false;ekil(this);">
<?php
    foreach ($data as $row) {
?>
          <tr>
            <td ondblclick="<?= fillData('grupo', $row); ?>"><?= $row['alias'] ?></td>
            <td ondblclick="<?= fillData('grupo', $row); ?>"><?= $row['razonSocial'] ?></td>
            <td ondblclick="<?= fillData('grupo', $row); ?>" class="shrinkCol"><?= $row['rfc'] ?></td>
            <td ondblclick="<?= fillData('grupo', $row); ?>" class="shrinkCol"><?= $row['status'] ?></td>
          </tr>
<?php
    }
?>
          <tr><th></th><th></th><th></th><th></th></tr>
<?php
    if (!isset($_GET["datos"])) {
?>
        </tbody>
        <?php /* <tfoot id="dialog_tfoot">
          <tr>
            <th colspan="4" class="centered">
              <input type="button" id="navToFirst"    class="navOverlayButton" value="<<"  onclick="fillSelectorContents('first')">
              <input type="button" id="navToPrevious" class="navOverlayButton" value=" < " onclick="fillSelectorContents('prev')">
              <span id="paginationIndexes" class="fontPageFormat"> <?= $obj->pageno ?>/<?= $obj->lastpage ?> </span>
              <input type="button" id="navToNext"     class="navOverlayButton" value=" > " onclick="fillSelectorContents('next')">
              <input type="button" id="navToLast"     class="navOverlayButton" value=">>"  onclick="fillSelectorContents('last')">
            </th>
          </tr>
        </tfoot> */ ?>
      </table><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="setPageNavBlock(<?= $obj->pageno.",".$obj->lastpage ?>);ekil(this);">
<?php
    }
}
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) { 
?>
    </div><div id="mylog" class="hidden"></div>
  </body>
</html>
<?php
}

include_once ("configuracion/finalizacion.php");
clog1seq(-1);
clog2end("showGrupo");
