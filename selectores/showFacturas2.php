<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Facturas.php";
require_once "clases/Grupo.php";
require_once "clases/Proveedores.php";
clog2ini("showFacturas2");
clog1seq();

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

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
    <style type="text/css">
      div.calendar_widget { position: absolute; float: left; top: 0px; left: 0px; width:170px; height: 200px; display: none; }
    </style>
    <script src="scripts/general.js"></script>
    <script src="date-picker.js"></script>
    <script>
      window.onload = fillPaginationIndexes;
      var entidadTipos = ["razon", "codigo", "rfc"];
      var showGpo = false, showPrv = false;
      function pickType(elem) {
          appendLog("function pickType");
          if (!elem || !elem.value) { appendLog(" empty\n"); return; }
          appendLog("("+elem.value+")\n");
          for (var i=0; i<entidadTipos.length; i++) {
              var tipo = entidadTipos[i];
              var selGpo = document.getElementById("gpot"+tipo);
              var selPrv = document.getElementById("prvt"+tipo);
              if (elem.value == "t"+tipo) {
                  showGpo = selGpo;
                  showPrv = selPrv;
              } else {
                  appendLog("hide "+selGpo.id+", "+selPrv.id+"\n");
                  selGpo.classList.add("hidden");
                  selPrv.classList.add("hidden");
              }
          }
          appendLog("show ");
          if (showGpo) { appendLog(showGpo.id+(showPrv?", ":"")); showGpo.classList.remove("hidden"); }
          if (showPrv) { appendLog(showPrv.id); showPrv.classList.remove("hidden"); }
          appendLog("\n");
      }

    </script>
  </head>
  <body>
    <div id="dialog_resultarea2" class="resultarea nocenter">
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
$gpoObj = new Grupo();
$gpoRazSocOpt = $gpoObj->getFullMap("id","razonSocial");
$gpoObj->clearFullMap();
$gpoCodigoOpt = $gpoObj->getFullMap("id","alias");
$gpoObj->clearFullMap();
$gpoRFCOpt = $gpoObj->getFullMap("id","rfc");
$prvObj = new Proveedores();
$prvRazSocOpt = $prvObj->getFullMap("id","razonSocial");
$prvObj->clearFullMap();
$prvCodigoDOpt = $prvObj->getFullMap("id","codigo");
$prvObj->clearFullMap();
$prvRFCOpt = $prvObj->getFullMap("id","rfc");
$dia = date('j');
$mes = date('n');
$anio = date('Y');
$maxdia = date('t');
$stt = "";
$mesNombres = ["1"=>"Enero","2"=>"Febrero","3"=>"Marzo","4"=>"Abril","5"=>"Mayo","6"=>"Junio","7"=>"Julio","8"=>"Agosto","9"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre"];
$sttNombres = ["Temporal"=>"Temporal", "Pendiente"=>"Pendiente", "Aceptado"=>"Aceptado", "Contrarrecibo"=>"Contrarrecibo", "Exportado"=>"Exportado", "Respaldado"=>"Respaldado"];
clog("Mes: $mes", 3);

$data = $invObj->getData($where);
// if ($invObj->numrows > 0) {
    if (!isset($_GET["datos"])) {
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $invObj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showFacturas2">
      <table class="nohover">
        <tr class="noApply nohover">
          <td class="noApply nohover">Empresa: </td>
          <td class="noApply nohover"><select name="gpotrazon" id="gpotrazon"><option value="">Todas</option><?= getHtmlOptions($gpoRazSocOpt, "") ?></select>
                                      <select name="gpotcodigo" id="gpotcodigo" class="hidden"><option value="">Todas</option><?= getHtmlOptions($gpoCodigoOpt, "") ?></select>
                                      <select name="gpotrfc" id="gpotrfc" class="hidden"><option value="">Todas</option><?= getHtmlOptions($gpoRFCOpt, "") ?></select></td>
          <td class="noApply nohover nowrap">Fecha Ini: </td>
          <td class="noApply nohover"><input id='fechaInicio' name="fechaInicio" value="<?= str_pad($dia,2,"0",STR_PAD_LEFT) ?>/<?= str_pad($mes,2,"0",STR_PAD_LEFT) ?>/<?= $anio ?>" class="calendar" onclick="javascript:show_calendar_widget(this);"></td>
        </tr>
        <tr class="noApply nohover">
          <td class="noApply nohover">Proveedores: </td>
          <td class="noApply nohover"><select name="prvtrazon" id="prvtrazon"><option value="">Todos</option><?= getHtmlOptions($prvRazSocOpt, "") ?></select>
                                      <select name="prvtcodigo" id="prvtcodigo" class="hidden"><option value="">Todas</option><?= getHtmlOptions($prvCodigoDOpt, "") ?></select>
                                      <select name="prvtrfc" id="prvtrfc" class="hidden"><option value="">Todas</option><?= getHtmlOptions($prvRFCOpt, "") ?></td>
          <td class="noApply nohover nowrap">Fecha Fin: </td>
          <td class="noApply nohover"><input id='fechaFin' name="fechaFin" value="<?= str_pad($dia,2,"0",STR_PAD_LEFT) ?>/<?= str_pad($mes,2,"0",STR_PAD_LEFT) ?>/<?= $anio ?>" class="calendar" onclick="javascript:show_calendar_widget(this);"></td>
        </tr>
        <tr class="noApply nohover">
          <td class="noApply nohover" colspan="2"><input type="radio" name="tipolista" id="tiporazon" value="trazon" checked onclick="pickType(this);">Razon Social <input type="radio" name="tipolista" id="tipocodigo" value="tcodigo" onclick="pickType(this);">Codigo <input type="radio" name="tipolista" id="tiporfc" value="trfc" onclick="pickType(this);">RFC</td>
          <td class="noApply nohover">Status: </td>
          <td class="noApply nohover"><select id="status"><option value="">Todas</option><?= getHtmlOptions($sttNombres, $stt) ?></select> &nbsp; <input type="submit" name="Buscar" value="Enviar"></td>
        </tr>
        <tr class="noApply nohover"><td class="noApply nohover" colspan="4"></td></tr>
      </table>
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
          // $dblclickEvent = fillData('transporte', $row);
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
    <div class="calendar_widget" id="calendar_widget"><iframe id='calendar_widget_iframe' name='calendar_widget_iframe' style="border: none;" width=100% height=100% src="calendar_widget.html"></iframe></div>
  </body>
</html>
<?php
}

include_once ("configuracion/finalizacion.php");
clog1seq(-1);
clog2end("showFacturas2");
