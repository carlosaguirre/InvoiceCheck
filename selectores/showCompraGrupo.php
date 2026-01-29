<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
require_once "clases/Grupo.php";
clog2ini("showCompraGrupo");
clog1seq(1);

$obj = new Grupo();
$obj->rows_per_page = 0;
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
      function recalculaCompraGrupo(checado, alias) {
          var lista = document.getElementById('listaComprasGrupoId');
          if (lista) {
              var listarr;
              if (lista.value.length==0) listarr=[];
              else listarr = lista.value.split(","); // JSON.parse('['+lista.value+']'); //
              if (checado) listarr.push(alias);
              else {
                  var idx = listarr.indexOf(alias);
                  if (idx>=0) listarr.splice(idx, 1);
              }
              lista.value = listarr.join();
          }
      }
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

if (isset($_REQUEST["lista"])) {
    $listaEmpresas = explode(",",$_REQUEST["lista"]);
    clog2("LISTA EMPRESAS : $_REQUEST[lista]");
}

$where = "";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        if ($value = $_REQUEST[$pvalue]) {
            if (strlen($where)>0) $where .= " AND ";
            $where .= $pvalue . " LIKE '%" . $value . "%'";
        }
    }
}
clog2("WHERE = $where");
$data = $obj->getData($where);
if ($obj->numrows > 0) {
    if (!isset($_GET["datos"])) {
        $gpoSttArr = array( ""=>"", "registrado"=>"registrado", "activo"=>"activo", "vencido"=>"vencido" );
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $obj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showCompraGrupo">
      <table>
        <thead>
          <tr>
            <th>Alias</th><th>Raz&oacute;n Social</th><th>RFC</th><th>Permiso</th>
          </tr>
        </thead>
        <tbody id="dialog_tbody">
<?php
    }
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $obj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $obj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $obj->lastpage ?>" />
<?php
    foreach ($data as $row) {
        $alias = $row['alias'];
        $checkedValue = "";
        if (in_array($alias, $listaEmpresas)) $checkedValue=" checked";
?>
          <tr>
            <td><?= $alias ?></td>
            <td><?= $row['razonSocial'] ?></td>
            <td class="shrinkCol"><?= $row['rfc'] ?></td>
            <td class="shrinkCol"><input type="checkbox"<?= $checkedValue ?> onclick="recalculaCompraGrupo(this.checked, '<?= $alias ?>');"></td>
          </tr>
<?php
    }
?>
<?php
    if (!isset($_GET["datos"])) {
?>
        </tbody>
      </table>
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
clog2end("showCompraGrupo");
