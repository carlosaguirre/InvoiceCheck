<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
clog2ini("Selector");
clog1seq();
// Este script es un selector generico, funciona como componente dentro de otro php.
// Solo importa este php, crea la clase con un objeto heredado de DBObject y ejecuta show
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación
// "id", "tipo", "folio", "codigoBodega", "ciclo", "fecha", "descripcion", "idRecurso", "monto"

class Selector {
    var $dbObj;
    var $title;
    var $skipHeaders;
    var $showAuto=false;
    var $resultArea = "selector_resultarea";
    var $selectorName;

    var $_isInitialized=true;
    var $_headers;
    function __construct($_dbObj) {
        if (is_subclass_of($_dbObj, 'DBObject')) {
            $this->dbObj = $_dbObj;
            $this->title = get_class($_dbObj);
            $this->selectorName = basename(__FILE__, '.php');
            clog2("Selector Name: ".$this->selectorName);
        }
    }
    function toNumericArray($arr) {
        $keys = array_keys($arr);
        $retArr = [];
        foreach ($keys as $k) {
            if (gettype($k)=="integer")
                $retArr[$k] = $arr[$k];
        }
        return $retArr;
    }
    function show() {
        if (!isset($this->dbObj)) return;
        $this->_initialize_show();

        if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) $this->_beginHtmlBlock();
        if (isset($_REQUEST["pageno"])) {
            $this->dbObj->pageno = $_REQUEST["pageno"];
        }
        if (isset($_REQUEST["limit"])) {
            $this->dbObj->rows_per_page = $_REQUEST["limit"];
        }
        $this->dbObj->getData($this->_where_construct());
        if (isset($this->skipHeaders) && !empty($this->skipHeaders) && is_array($this->skipHeaders)) {
            $this->_headers = [];
            foreach($this->dbObj->fetch_headers as $headname) {
                if (!in_array($headname, $this->skipHeaders)) $this->_headers[] = $headname;
            }
        } else $this->_headers = $this->dbObj->fetch_headers;
        clog2(" ---------- HEADERS ----------\n".arr2str($this->headers)." ----------         ----------");
        if (!isset($_GET["datos"])) $this->_beginTableBlock();
        $this->_dataBlock();
        if (!isset($_GET["datos"])) $this->_endTableBlock();
        if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) $this->_endHtmlBlock();

        $this->_finalize_show();
    }
    function _where_construct() {
        $whereStr = "";
        $exactVars = NULL;
        if (isset($_REQUEST["exacto"])) {
            $exactVars = explode(",", $_REQUEST["exacto"]);
        }
        if (isset($_REQUEST["param"])) {
            $param = $_REQUEST["param"];
            foreach ($param as $pvalue) {
                if ($value = $_REQUEST[$pvalue]) {
                    if (strlen($whereStr)>0) $whereStr .= " AND ";
                    if (in_array($pvalue, $exactVars))
                        $whereStr .= $pvalue . "='".$value."'";
                    else
                        $whereStr .= $pvalue . " LIKE '%" . $value . "%'";
                }
            }
        }
        return $whereStr;
    }
    function _initialize_show() {
        $this->_isInitialized = include_once "configuracion/inicializacion.php";
        if ($this->_isInitialized!==true)
            clog2("### ### Selector INICIALIZADO. (include_once configuracion/inicializacion.php)");
    }
    function _finalize_show() {
        if ($this->_isInitialized!==true) {
            include_once "configuracion/finalizacion.php";
            clog2("### ### Selector FINALIZADO. (include_once configuracion/finalizacion.php)");
        }
    }
    function _beginHtmlBlock() {
?>
<!DOCTYPE html>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8" />
    <title>Compra-Venta Desperdicios Melo</title>
    <link href="css/general.php" rel="stylesheet" type="text/css" />
<?php
        require_once "templates/generalScript.php";
        echoScript("General");
?>
    <script>
      window.onload = fillPaginationIndexes;
    </script>
  </head>
  <body>
    <div id="<?= $this->resultArea ?>" class="resultarea">
      <H2><?= $this->title ?></H2>
<?php
    }
    function _beginTableBlock() {
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $this->dbObj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="<?= $this->selectorName ?>">
      <table style="height: 350px;">
        <thead>
          <tr>
<?php
        echo "            ";
        foreach ($this->_headers as $colname)
            echo "<th>".ucfirst($colname)."</th>";
        echo "\n";
?>
          </tr>
        </thead>
        <tbody id="dialog_tbody">
<?php
    }
    function _endTableBlock() {
?>
        </tbody>
        <tfoot id="dialog_tfoot">
          <tr>
            <th colspan="10">
              <input type="button" id="navToFirst"    class="navOverlayButton" value="<<"  onclick="fillSelectorContents('first')">
              <input type="button" id="navToPrevious" class="navOverlayButton" value=" < " onclick="fillSelectorContents('prev')">
              <span id="paginationIndexes" class="fontPageFormat"> <?= $this->dbObj->pageno ?>/<?= $this->dbObj->lastpage ?> </span>
              <input type="button" id="navToNext"     class="navOverlayButton" value=" > " onclick="fillSelectorContents('next')">
              <input type="button" id="navToLast"     class="navOverlayButton" value=">>"  onclick="fillSelectorContents('last')">
            </th>
          </tr>
        </tfoot>
      </table>
<?php
    }
    function _endHtmlBlock() {
?>
    </div><div id="mylog" class="hidden"></div>
  </body>
</html>
<?php
    }
    function _dataBlock() {
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $this->dbObj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $this->dbObj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $this->dbObj->lastpage ?>" />
<?php
        foreach ($this->dbObj->data_array as $row) {
            $this->formatRow($row);
        }
        echo "          <tr>";
        foreach ($this->_headers as $fieldname)
            echo "<th></th>";
        echo "</tr>\n";
    }
    function formatRow($row) {
        echo "          <tr>\n";
        foreach ($this->_headers as $fieldname) {
            $this->formatField($row, $fieldname);
        }
        echo "          </tr>\n";
    }
    function formatField($row, $fieldname) {
        echo "            <td>".$row[$fieldname]."</td>\n";
    }
}
if (!isset($ciclo)) {
    $ciclo = date('Y');
    if (isset($_REQUEST["ciclo"])) $ciclo = $_REQUEST["ciclo"];
}
clog1seq(-1);
clog2end("Selector");
