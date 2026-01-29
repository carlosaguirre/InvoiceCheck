<?php
if(!hasUser()) {
    $scriptName=$_SERVER["SCRIPT_NAME"];
    $lastSlashIdx=strrpos($scriptName, "/");
    $scriptName=substr($scriptName, $lastSlashIdx+1);
    if (isset($scriptName[0]) && $scriptName!=="index.php"  && $scriptName!=="error.php" && $scriptName!=="inicio.php" && $scriptName!=="docs.php" && $scriptName!=="invoice") {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
        header("Location: /".$_project_name."/");
        die();
    }
}
clog2ini("encabezado");
clog1seq(1);

$configClass="noprint";
if(isset($_REQUEST["encabezado"][0])) $configClass.=" $_REQUEST[encabezado]";
if (isset($configClass[0])) $configClass=" class=\"".$configClass."\"";
?>
      <div id="encabezado"<?=$configClass?>>
        <div id="head_logo" class="centered"><a href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_self"><img src="imagenes/logos/invoiceCheck.png" alt="Invoice Check" longdesc="Logo InvoiceCheck"></a></div>
        <div id="head_main">
          <h1 class="txtstrk"><?= $systemTitle??"ENCABEZADO" ?></h1>
        </div>
        <br class="clear"/>
      </div>
<?php
clog1seq(-1);
clog2end("encabezado");
