<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/CargaMF.php";
$cmfObj=new CargaMF();
// select distinct u.nombre from usuarios u inner join usuarios_perfiles up on u.id=up.idUsuario where up.idPerfil!=3 order by u.nombre;
$cmfObj->rows_per_page=2000;
$cmfData=$cmfObj->getData("status=2");
?>
<html>
  <head>
    <title>CargaMF Fix</title>
    <script>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>CargaMF Fix ...<?= count($cmfData) ?></h1>
      <div id="area_detalle">
<?php
if (isset($cmfObj->errors[0])) {
?>
        <h2>ERRORES (objeto)</h2>
        <ul>
<?php
  foreach ($cmfObj->errors as $cmfErr) {
?>
        <li><?= $cmfErr ?></li>
<?php 
  }
?>
        </ul>
<?php 
}
if (!empty(DBi::$errors)) {
?>
        <h2>ERRORES (clase)</h2>
        <ul>
<?php
  foreach (DBi::$errors as $cmfErrno=>$cmfError) {
?>
        <li><b><?= $cmfErrno ?></b> - <?= $cmfError ?></li>
<?php 
  }
?>
        </ul>
<?php 
}
$confirmed=0;
if (isset($cmfData[0])) { ?>
        <table style="white-space: nowrap;"><thead><tr><th>#</th><th>ID</th><th>FECHA</th><th>PATH</th><th>ARCHIVO</th><th>UUID</th><th>STATUS</th><th>TIPO</th><th>METODO</th><th>DESCRIPCION</th><th>DATOS</th></tr></thead><tbody>
<?php 
  require_once "clases/Facturas.php";
  $invObj=new Facturas();
  require_once "clases/AutoUploadInvoice.php";
  global $autoUploadPath, $autoUploadErrPath;
  $idx=0;
  foreach ($cmfData as $cmfItem) {
    $id=$cmfItem["id"];
    $na=strtoupper($cmfItem["nombreArchivo"]);
    $iv=$cmfItem["idFactura"];
    $invData=$invObj->getData("id=$iv");
    if (isset($invData[0])) $invData=$invData[0];
    $ui=strtoupper($invData["uuid"]??$iv);
    if (strcmp($na, $ui)==0) {
      $cmfObj->saveRecord(["id"=>$id,"status"=>CargaMF::STATUS_BDEXISTE]);
      $confirmed++;
      continue;
    }
    $ra=$cmfItem["rutaArchivo"];
    $dt=$cmfItem["fechaCarga"];
    $ds=$cmfItem["descripcion"];
    $st=$cmfItem["status"];
    $tp=$cmfItem["tipo"];
    $mt=$cmfItem["metodo"];
    $da=str_replace(["<",">"], ["&lt;","&gt;"], $cmfItem["datos"]);
    $idx++;
    //$cmfObj->saveRecord(["id"=>$id,"status"=>CargaMF::STATUS_ELIMINADO]);
    //AutoUploadInvoice::moveTo($autoUploadErrPath."yaExiste/".$ra, $autoUploadPath."REINTENTAR", $na);
?>
          <tr><td><?= $idx ?></td><td><?= $id ?></td><td><?= $dt ?></td><td><?= $ra ?></td><td><?= $na ?></td><td><?= $ui ?></td><td><?= $st ?></td><td><?= $tp ?></td><td><?= $mt ?></td><td><?= $ds ?></td><td><?= $da ?></td></tr>
<?php
  }
?>
        </tbody><tfoot><tr><td colspan="10"><?=$confirmed?> facturas confirmadas</td></tr></tfoot></table>
<?php
}
?>
      </div>
    </div>
  </body>
</html>
