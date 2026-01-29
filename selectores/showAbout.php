<?php
require_once dirname(__DIR__)."/bootstrap.php";
clog2ini("showAbout");
clog1seq(1);

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

//include ("configuracion/inicializacion.php");
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) {
?>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8" />
    <title>Gesti&oacute;n de Facturas Electr&oacute;nicas del Grupo</title>
    <link href="css/general.php" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div id="dialog_resultarea">
<?php
}
if (!isset($_GET["datos"])) {
?>
      <table>
        <tbody id="dialog_tbody">
<?php
}
?>
          <tr class="nohover">
            <td class="nohover"><img id="img_ayuda" src="imagenes/logos/glama.gif" /></td>
            <td class="nohover bodyfont izquierdo">Gesti&oacute;n de Facturas<br><br>Versión 1.0<br><br>Copyright &copy; 2016<br><br>Facturaci&oacute;n del Grupo<br><br><textarea id="textarea_ayuda" readonly>Sistema diseñado para gestionar las facturas electr&oacute;nicas recibidas de los proveedores.</textarea></td>
          </tr>
<?php
if (!isset($_GET["datos"])) {
?>
        </tbody>
      </table>
<?php
}
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) {
?>
    </div><div id="mylog" class="hidden"></div>
  </body>
</html>
<?php
}
//include_once ("configuracion/finalizacion.php");
clog1seq(-1);
clog2end("showAbout");
