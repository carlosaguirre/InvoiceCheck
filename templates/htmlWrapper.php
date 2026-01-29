<?php
require_once dirname(__DIR__)."/bootstrap.php";

$template=$_REQUEST["template"]??null;
$templateh=$_REQUEST["templateh"]??null;
if (isset($_REQUEST["TITLE"][0])) $systemTitle=$_REQUEST["TITLE"];
//switch ($template) {
//    case "solforma":
//}

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
    <base href="<?= getBaseURL() ?>" target="_blank">
    <title><?= $systemTitle??"TITULO" ?></title>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="scripts/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="scripts/bootstrap-3.3.2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-multiselect.css"/>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-3.3.2.min.css"/>
<?php
    if ($isMSIE) echoPolyfillScript();
    echoGeneralScript();
?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/><?= $styleActionLine??"" ?>
    <script src="scripts/date-picker.js?ver=1.31"></script>
    <?= $scriptActionLine??"" ?><script src="scripts/calendar_conf.js?ver=1.23"></script>
    <script src="scripts/barraLateral.php" type="text/javascript"></script>
    <script>
      var now = new Date(<?php echo time() * 1000; ?>);
      startInterval(); //start it right away
    </script>
  </head>
  <body>
    <div id="contenedor" class="centered">
<?php
    include "templates/encabezado.php";
    if (isset($template[0])) include "templates/{$template}.php";
    if (isset($templateh[0])) {
        $keyMap=["BASEURL"=>getBaseURL(),"HOSTNAME"=>getBaseURL(),"BUTTONS"=>"<!-- 4 -->"];
        foreach ($_REQUEST as $key => $value) $keyMap[$key]=$value;
        $mensaje=file_get_contents(getBasePath()."templates/{$templateh}.html");
        //$mensaje = preg_replace("/%\w+%/","",str_replace(array_map(function($elem){return "%".$elem."%";},array_keys($keyMap)),array_values($keyMap), $mensaje));
        $mensaje = str_replace(array_map(function($elem){return "%".$elem."%";},array_keys($keyMap)),array_values($keyMap), $mensaje);
        echo $mensaje;
    }
    include "templates/piePagina.php";
?>
    </div>
    </div>
  </body>
</html>
<!-- 
    -----------------------------
    GET:
    <?= arr2str($_GET); ?>

    -----------------------------
    POST:
    <?= arr2str($_POST); ?>

    -----------------------------
    FILES:
    <?= arr2str($_FILES); ?>

    -----------------------------
    SESSION:
    <?= arr2str($_SESSION); ?>

    -----------------------------
    COOKIES:
    <?= arr2str($_COOKIE); ?>
-->
<?php
