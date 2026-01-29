<?php
require_once dirname(__DIR__)."/bootstrap.php";
clog2ini("templates.inicio");
clog1seq(1);
?>
<!DOCTYPE html>
<?php
$browser = getBrowser();
$isMSIE = ($browser==="Edge" || $browser==="IE");
require_once "templates/generalScript.php";
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <?= $isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title><?= $systemTitle ?></title>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <?php /* link rel="stylesheet" type="text/css" href="http://davidstutz.de/bootstrap-multiselect/dist/css/bootstrap-multiselect.css"/ */ ?>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-3.3.2.min.css"/>
<?php
    if (isset($extraHeadSettings[0])) echo $extraHeadSettings;
    if ($isMSIE) echoPolyfillScript();
    echoGeneralScript();
?>
    <link href="css/general.php" rel="stylesheet" type="text/css">
    <script>
      var now = new Date(<?php echo time() * 1000; ?>);
      startInterval(); //start it right away
<?php
    include "templates/onLoad.php";
?>
    </script>
  </head>
  <body>
    <div id="contenedor" class="centered">
<?php
    $navIdx=0;
    include "templates/encabezado.php";
?>
      <div id="bloque_central">
<?= $contenido??"" ?>
      </div>
<?php
    include ("templates/piePagina.php");
?>
    </div>
  </body>
</html>
<?php
clog1seq(-1);
clog2end("templates.inicio");
