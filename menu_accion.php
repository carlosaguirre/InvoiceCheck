<?php
//    echo "<!-- INI MENU ACCION ".($esPruebas?"IS":"NOT")." TESTING -->\n";
    $ciclo = date('Y');
    $clearForm = true;

    if ($hasConfigAction) {
        $controlAction = "configuracion/".$urlAction.($configSuffix??"").".php";
        //echo "<!-- controlAction: $controlAction -->";
//        echo "<!-- MENU ACCION 1A ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
        include $controlAction;
//        echo "<!-- MENU ACCION 1B ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
    }
    $scriptActionLine = $hasScriptAction?"<script src=\"scripts/".$urlAction.($scriptSuffix??"").".php\" type=\"text/javascript\"></script>":"";
    foreach ($otherScriptSrc as $ossi) {
        $scriptActionLine .= (isset($scriptActionLine[0])?"\n    ":"")."<script src=\"$ossi\" type=\"text/javascript\"></script>";
    }
    $styleActionLine = $hasStyleAction?"<link href=\"css/".$urlAction.($styleSuffix??"").".php\" rel=\"stylesheet\" type=\"text/css\">":"";
    foreach ($otherStyleHref as $oshi) {
        $styleActionLine .= (isset($styleActionLine[0])?"\n    ":"")."<link href=\"$oshi\" rel=\"stylesheet\" type=\"text/css\"></script>";
    }
    $templateAction = "templates/".$urlAction.($templateSuffix??"").".php";
//    echo "<!-- MENU ACCION 2 ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
?>
<!DOCTYPE html>
<?php
//clog2("Browser: $_browser");
//clog2("HTTP_ORIGIN : '$_SERVER[HTTP_ORIGIN]'");
//clog2("WEB_PATH : '$_SERVER[WEB_MD_PATH]'");
$isMSIE = ($_browser==="Edge" || $_browser==="IE");
require_once "templates/generalScript.php";
//echo "<!-- MENU ACCION 3 ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
echo "<!-- WAIT IMG: $waitImgName -->\n";
echo "<!-- BKGD IMG: $bkgdImgName -->\n";

?>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8" />
    <?= $isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <meta name="viewport" content="width=device-width" />
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank" />
    <title><?= $systemTitle ?></title>
    <link rel="icon" href="favicon.ico">
    <?php /* script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script */ ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script type="text/javascript" src="scripts/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="scripts/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="scripts/bootstrap-3.3.2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-multiselect.css"/>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-3.3.2.min.css?ver=1.0B"/>
<?php
//echo "<!-- MENU ACCION 4 ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
    if ($isMSIE) echoPolyfillScript();
    echoScript("General");
?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
    <?= $styleActionLine ?>
<?php
//echo "<!-- MENU ACCION 5 ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
    echoScript("DatePicker");
    echoScript("Calendar");
//echo "<!-- MENU ACCION 6 ".($esPruebas?"STILL":"NOT")." TESTING -->\n";
    $clockInitLine=($_esPruebas?"      doShowFuncLogs=false;\n      let clock = ebyid('pie_clock');\n      if (clock) {\n        clock.onmouseenter=clockTimerDisplay;\n      }\n":"<!-- NOT TESTING -->\n");
?>
    <?= $scriptActionLine ?>
    <script src="scripts/barraLateral.php" type="text/javascript"></script>
    <script>
      var now = new Date(<?php echo time() * 1000; ?>);
<?= $clockInitLine ?>
      startInterval(); //start it right away
<?php
    if ($urlAction=="login" && !$hasUser && empty($resultMessage) && empty($errorMessage)) {
        if (empty($onloadScript)) $onloadScript="";
        $onloadScript .= "const usrnmEl=ebyid('username'); if (usrnmEl) usrnmEl.focus(); else console.log('NO USERNAME');";
    }
    include "templates/onLoad.php";
?>
    </script>
  </head>
  <body>
    <div id="contenedor" class="centered">
<?php
    $navIdx=0;
    include "templates/encabezado.php";
    $configClass="";
    if(isset($_REQUEST["bloque_central"][0])) $configClass=" class=\"$_REQUEST[bloque_central]\"";
?>
      <div id="bloque_central"<?=$configClass?>>

<?php include "templates/barraLateral.php";
    $configClass="";
    if(isset($_REQUEST["principal"][0])) $configClass=" class=\"$_REQUEST[principal]\"";
?>
        <div id="principal"<?=$configClass?>><!-- TEMPLATE ACTION = '<?=$templateAction?>' -->
<?php
    include $templateAction;
?>
        </div><br class="clear"/>
      </div>
<?php
    include ("templates/piePagina.php");
?>
    </div>
<?php
    include ("templates/overlay.php");
?>
    <div id="mylog" class="<?= ($_SESSION["viewMyLog"]??false)?"isAdmin":"" ?>">
    </div>
  </body>
</html>
<?php
if ($_esPruebas) {
    echo "<!--\n";
    if (count($_GET)>0) {
        echo "    -----------------------------\n    GET:\n";
        echo arr2str($_GET, " * ", "", 1, ["maxvallen"=>50,"separator"=>", ","maxdepth"=>3,"showvaluetype"=>true,"showvaluelength"=>true])."\n";
    }
    if (count($_POST)>0) {
        echo "    -----------------------------\n    POST:\n";
        echo arr2str($_POST, " * ", "", 1, ["maxvallen"=>50,"separator"=>", ","maxdepth"=>3,"showvaluetype"=>true,"showvaluelength"=>true])."\n";
    }
    if (count($_FILES)>0) {
        echo "    -----------------------------\n    FILES:\n";
        echo arr2str($_FILES, " * ", "", 1, ["maxvallen"=>0,"separator"=>", ","maxdepth"=>3,"showvaluetype"=>true,"showvaluelength"=>true])."\n";
    }
    if (count($_SESSION)>0) {
        echo "    -----------------------------\n    SESSION:\n";
        echo arr2str($_SESSION, " * ", "", 1, ["maxvallen"=>50,"separator"=>", ","maxdepth"=>3,"showvaluetype"=>true,"showvaluelength"=>true])."\n";
        if (isset($_SESSION["user"])) echo "     user session:\n".json_encode($_SESSION["user"])."\n";
    }
    if (count($_COOKIE)>0) {
        echo "    -----------------------------\n    COOKIES:\n";
        echo arr2str($_COOKIE, " * ", "", 1, ["maxvallen"=>50,"separator"=>", ","maxdepth"=>3,"showvaluetype"=>true,"showvaluelength"=>true])."\n-->\n";
    }
}