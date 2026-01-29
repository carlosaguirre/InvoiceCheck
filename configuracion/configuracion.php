<?php
if (!$_esSistemas) {
    if ($hasUser) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.configuracion");
clog1seq(1);

clog2("testVar: $testVar");
clog2("testEnv: $testEnv");
clog2("_project_name: $_project_name");

if (!isset($infObj)) {
    require_once "clases/InfoLocal.php";
    $infObj = new InfoLocal();
}
$infObj->rows_per_page=0;

$infData = $infObj->getData("nombre LIKE 'CFDI_ALLOWP01%' and valor='1'",0,"nombre");
$allowP01=[];
foreach($infData as $idx=>$val) $allowP01[]=substr($val["nombre"],14);

$infData = $infObj->getData("nombre LIKE 'CFDI_ALLOW33%' and valor='1'",0,"nombre");
$allow33=[];
foreach ($infData as $idx => $val) $allow33[]=substr($val["nombre"], 13);

$infData = $infObj->getData("nombre LIKE 'CFDI_ALLOWPRTV%' and valor='1'",0,"nombre");
$allowPRTV=[];
foreach ($infData as $idx => $val) $allowPRTV[]=substr($val["nombre"],15);

$infData = $infObj->getData("nombre LIKE 'CFDI_ALLOW01x4%' and valor='1'",0,"nombre");
$allow01x4=[];
foreach ($infData as $idx => $val) $allow01x4[]=substr($val["nombre"], 15);

$infData = $infObj->getData("nombre='CFDI_IGNORE2020LIMIT'",0,"valor");
$allow2019=($infData[0]["valor"]??"0")==="1";
$infData = $infObj->getData("nombre='CFDI_IGNOREMONTHLIMIT'",0,"valor");
$allowLastMonth=($infData[0]["valor"]??"0")==="1";
$lastMonthList="";

if (isset($_SESSION["viewMyLog"]))
    clog2("VIEWMYLOG SESSION=".json_encode($_SESSION["viewMyLog"]));
if (isset($_POST["viewMyLog"]))
    clog2("VIEWMYLOG POST=".json_encode($_POST["viewMyLog"]));
if (isset($_POST["viewMyLog0"]))
    clog2("VIEWMYLOG0 POST=".json_encode($_POST["viewMyLog0"]));
$_SESSION['viewMyLog'] = $viewMyLog = $_POST["viewMyLog"]??($_POST['viewMyLog0']??($_SESSION['viewMyLog']??"0"));
clog2("VIEWMYLOG RESULT".($viewMyLog?"(true)":"(false)")."=".json_encode($viewMyLog));
$editButtonClass="hidden"; // ToDo: Debería estar vacío, pero para evitar equivocaciones se mantendrá oculto, así como el checkbox con disabled.
// Actualmente la lista contiene a todos los usuarios que no son proveedores, y la busqueda editable siempre estará vacía pues solo se despliegan los usuarios no proveedores que no estén en la lista.
$lastMonthClass="";
if (!$allowLastMonth) {
    $editButton=" <img id=\"editButton\" src=\"imagenes/icons/rename12.png\" class=\"pointer\" onclick=\"showFilterLine(event);\">";
    if ($infData[0]["valor"]!=="0") {
        $usrIdList=explode(",", $infData[0]["valor"]);
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        $usrObj->rows_per_page=0;
        $usrData=$usrObj->getData("id in (".$infData[0]["valor"].")",0,"id,nombre,persona");
        clog2("USRDATA LENGTH = ".count($usrData));
        if (isset($usrData[0])) foreach ($usrData as $idx => $usrItem) {
            $lastMonthList.="<li id=\"u$usrItem[id]\" name=\"$usrItem[nombre]\" class=\"user relative\"><img src=\"imagenes/icons/deleteIcon12.png\" class=\"pointer abs rgt4 top4\" onclick=\"removeUser(event);\"> $usrItem[persona]</li>";
        } else $lastMonthClass=" hidden";
    } else $lastMonthClass="hidden";
} else {
    $editButtonClass=" hidden";
    $lastMonthClass=" hidden";
}
global $usrObj;
if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
$usrObj->rows_per_page=0;
$usrObj->addOrder("nombre");

$lstP01="";
$clsP01=" hidden";
if (isset($allowP01[0])) {
    $usrData=$usrObj->getData("id in (".implode(",",$allowP01).")",0,"id,nombre,persona,email");
    foreach ($usrData as $idx => $urw) {
        $lstP01.="<li idf=\"$urw[id]\" nom=\"$urw[nombre]\" per=\"$urw[persona]\" eml=\"$urw[email]\" class=\"user relative\"><img src=\"imagenes/icons/deleteIcon12.png\" name=\"CFDI_ALLOWP01_$urw[id]\" class=\"pointer abs rgt4 top4\" onclick=\"delUser(event)\"> $urw[nombre] - $urw[persona]</li>";
    }
    if (isset($lstP01[0])) $clsP01="";
}

$lst33="";
$cls33=" hidden";
if (isset($allow33[0])) {
    $usrData=$usrObj->getData("id in (".implode(",",$allow33).")",0,"id,nombre,persona,email");
    foreach ($usrData as $idx => $urw) {
        $lst33.="<li idf=\"$urw[id]\" nom=\"$urw[nombre]\" per=\"$urw[persona]\" eml=\"$urw[email]\" class=\"user relative\"><img src=\"imagenes/icons/deleteIcon12.png\" name=\"CFDI_ALLOW33_$urw[id]\" class=\"pointer abs rgt4 top4\" onclick=\"delUser(event)\"> $urw[nombre] - $urw[persona]</li>";
    }
    if (isset($lst33[0])) $cls33="";
}

$lstPRTV="";
$clsPRTV=" hidden";
if (isset($allowPRTV[0])) {
    $usrData=$usrObj->getData("id in (".implode(",",$allowPRTV).")",0,"id,nombre,persona,email");
    foreach ($usrData as $idx => $urw) {
        $lstPRTV.="<li idf=\"$urw[id]\" nom=\"$urw[nombre]\" per=\"$urw[persona]\" eml=\"$urw[email]\" class=\"user relative\"><img src=\"imagenes/icons/deleteIcon12.png\" name=\"CFDI_ALLOWPRTV_$urw[id]\" class=\"pointer abs rgt4 top4\" onclick=\"delUser(event)\"> $urw[nombre] - $urw[persona]</li>";
    }
    if (isset($lstPRTV[0])) $clsPRTV="";
}

$lst01x4="";
$cls01x4=" hidden";
if (isset($allow01x4[0])) {
    $usrData=$usrObj->getData("id in (".implode(",", $allow01x4).")",0,"id,nombre,persona,email");
    foreach ($usrData as $idx => $udt) {
        $lst01x4.="<li idf=\"$udt[id]\" nom=\"$udt[nombre]\" per=\"$udt[persona]\" ema=\"$udt[email]\" class=\"user relative\"><img src=\"imagenes/icons/deleteIcon12.png\" name=\"CFDI_ALLOW01x4_$udt[id]\" class=\"pointer abs rgt4 top4\" onclick=\"delUser(event)\"> $udt[nombre] - $udt[persona]</li>";
    }
    if (isset($lst01x4[0])) $cls01x4="";
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.configuracion");
