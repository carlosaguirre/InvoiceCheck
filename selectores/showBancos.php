<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Bancos.php";
clog2ini("showBancos");
clog1seq(1);

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

function fillData($table, $row) {
    if (!isset($_GET["tabla"]) && !isset($_GET["datos"]))
        return "alert('".$table."[".$row["id"]."] = ".$row["razonSocial"]." | ".$row["alias"]." | ".$row["clave"]." | ".$row["rfc"]."')";
    return "console.log(' # * # * # FILLDATA BANCO # * # * #');fillValue('banco_id','".$row["id"]."');fillValue('banco_field','".$row["razonSocial"]."');fillValue('banco_alias','".$row["alias"]."');fillValue('banco_clave','".$row["clave"]."');fillValue('banco_rfc','".$row["rfc"]."');ebyid('banco_status').checked=".($row["status"]==="activo"?"true":"false").";fillValue('banco_cuenta','".$row["cuenta"]."');fillDataCheck();overlay();";
}

$obj = new Bancos();
$obj->rows_per_page  = 10;
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
$order = "";
$exacto=[];
if (isset($_REQUEST["exacto"])) $exacto=explode(",", $_REQUEST["exacto"]);
if (!isset($_REQUEST["param"])) {
    if (!isset($_GET["datos"])) $where="LOWER(clave) LIKE '40%' AND LOWER(status)='activo'"; // !isset($_GET["datos"]) => al abrir dialogo
} else {
    $pars=[];
    foreach($_REQUEST["param"] as $ky)
        if(isset($_REQUEST[$ky])) $pars[$ky]=$_REQUEST[$ky];
    $where = DBi::params2Where($pars,$exacto,true);
    if (isset($pars["clave"])) $order="(clave+0)";
    else if (isset($pars["razonSocial"])) $order="razonSocial";
    else if (isset($pars["rfc"])) $order="rfc";
}
if (!isset($order[0])) $order="(clave+0)";
$obj->addOrder($order);
global $query;
$data = $obj->getData($where);
//clog2("\n".$obj->log."\n");
clog2("QUERY:\n$query");
if ($obj->numrows > 0) {
    if (!isset($_GET["datos"])) {
        $bnkSttArr = array( ""=>"", "activo"=>"activo", "inactivo"=>"inactivo" );
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $obj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showBancos">
      <table onwheel="wheelPaginate(event)">
        <thead>
          <tr>
            <th>Clave</th><th>Raz&oacute;n Social</th><th>Alias</th><th>RFC</th><th>CUENTA</th><th>Status</th>
          </tr>
          <tr>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="clave" class="longtext filter_box" value="<?= $iniClave??"40%" ?>"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="razonSocial" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="alias" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="rfc" class="longtext filter_box"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="cuenta" class="longtext filter_box"></th>
            <th><select onchange="fillSelectorContents('exacto')" name="status" filter="exacto" class="filter_box">
<?= getHtmlOptions($bnkSttArr, "activo", 20) ?>
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
            <td ondblclick="<?= fillData('banco', $row); ?>" class="shrinkCol"><?= $row['clave'] ?></td>
            <td ondblclick="<?= fillData('banco', $row); ?>"><?= $row['razonSocial'] ?></td>
            <td ondblclick="<?= fillData('banco', $row); ?>" class="shrinkCol"><?= $row['alias'] ?></td>
            <td ondblclick="<?= fillData('banco', $row); ?>" class="shrinkCol"><?= $row['rfc'] ?></td>
            <td ondblclick="<?= fillData('banco', $row); ?>" class="shrinkCol"><?= $row['cuenta'] ?></td>
            <td ondblclick="<?= fillData('banco', $row); ?>" class="shrinkCol"><?= $row['status'] ?></td>
          </tr>
<?php
    }
?>
          <tr><th></th><th></th><th></th><th></th><th></th></tr>
<?php
    if (!isset($_GET["datos"])) {
?>
        </tbody>
        <?php /* <tfoot id="dialog_tfoot">
          <tr>
            <th colspan="6" class="centered">
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
clog2end("showBancos");
