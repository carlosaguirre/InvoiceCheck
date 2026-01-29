<?php
if (!isset($menu_accion)) $menu_accion="";
?>
<!doctype html>
<html style="height:100%;">
  <head>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8">
    <title><?= $systemTitle ?></title>
    <link href="css/general.php" rel="stylesheet" type="text/css">
<?php
require_once "templates/generalScript.php";
echoGeneralScript();
?>
    <script>
      var now = new Date(<?php echo time() * 1000; ?>);
      startInterval(); //start it right away
    </script>
  </head>
  <body>
    <div id="contenedor">
<?php
$navIdx=0;
include "templates/encabezado.php";
?>
      <div id="bloque_central">
<?php include "templates/barraLateral.php"; ?>

        <div id="principal">
<?php
if ($errorDetail)
    echo $errorDetail;
if ($errorTrace)
    clog1($errorTrace);
?>
        </div>
<br><br>
      </div>
<?php
include ("templates/piePagina.php");
//    clog1seq();
?>
    </div>
    <div id="mylog" class="hidden">
    </div>
  </body>
</html>
