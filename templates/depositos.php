<?php
// ToDo: consultar tabla Menu
// Validar perfil Administrador o Sistemas, sino seguir validaciones
// Query: select permiso from menu where accion="Depositos";
// Debe regresar PCompras, o podría cambiarlo como debía estar antes a ProcesarM
// El texto de permiso hay que separarlo por comas y cada caso validarlo contra un permiso/perfil
// Si empieza con P quitar la P y checar si tiene validaPerfil del resto
// Si termina con C quitar la C y checar si tiene consultaValida
// Si termina con M quitar la M y checar si tiene modificacionValida
if(!hasUser()||(!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Compras"))) {
    if (hasUser()) {
      setcookie("menu_accion", "", time() - 3600);
      setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("templates.depositos");
clog1seq(1);
global $gpoObj;
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$optDefaultValue=$_SESSION['optDefaultValue']; // Definido en Grupo
$gpoFullMapWhere=$gpoObj->setIdOptSessions(["Compras"], $optDefaultValue);
echo "<!-- $gpoFullMapWhere -->\n";
$validList = [];
require_once "configuracion/conPro.php";
if (isset($_SESSION['gpoCodigo2Id'])) {
    $validList = array_keys($_SESSION['gpoCodigo2Id']);
    $validStr=implode(",",$validList);
    echo "<!-- $validStr -->\n";
    if (isset($validStr)) {
        $validStr = str_replace("MARLOT", "TRANSPORTES", $validStr);
        $validStr = str_replace("BIDARENA", "BIDAARENA", $validStr);
        $validStr = str_replace("CAPITALH", "CAPITAL HALL", $validStr);
        $validQry = "?ValidList=$validStr{$_ConProTest}";
    }
}
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">MOVIMIENTOS BANCARIOS</h1>
  <iframe src="<?=$_ConProHost?>/externo/tesoreria/depositos1.aspx<?= $validQry??"" ?>" id="movimientosbancarios1"></iframe>
</div>
<?php
clog1seq(-1);
clog2end("templates.depositos");
