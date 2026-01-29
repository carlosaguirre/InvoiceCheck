<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaEmpl)) $consultaEmpl = consultaValida("Empleados")||$_esSistemas;
if (!isset($modificaEmpl)) $modificaEmpl = modificacionValida("Empleados")||$_esSistemas;
if (!$consultaEmpl) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.empleados");
clog1seq(1);

/*
$soloLectura=(($_POST["view_mode"]??"")==="readonly");
$idEmpleado=$_POST["empl_id"]??"";
$numEmpleado=$_POST["empl_num"]??"";
$nombreEmpleado=strtoupper(trim($_POST["empl_name"]??""));
$cuentaTC=trim($_POST["empl_acccard"]??"");
$cuentaCLABE=trim($_POST["empl_accuniq"]??"");
$empresaAlias=trim($_POST["empl_gpoalias"]??"");
$statusEmpleado=$_POST["empl_status"]??"";
*/
require_once "clases/Grupo.php";
$gpoObj=new Grupo();
$gpoObj->rows_per_page=0;
$gpoObj->clearOrder();
$gpoObj->addOrder("alias");
$gpoData=$gpoObj->getData("status='activo'", 0, "alias,razonSocial");
$grupoArray=[""=>"TODOS"];
foreach ($gpoData as $gpoItem) {
    $grupoArray[$gpoItem["alias"]]=$gpoItem["alias"]." - ".$gpoItem["razonSocial"];
}
$statusArray=[""=>"TODOS","activo"=>"ACTIVO","inactivo"=>"INACTIVO","borrado"=>"BORRADO"];

/*
$beginHidden="beginHidden";
if (isset($_POST["empl_browse"])) {
    $fldarr=[];
    if (isset($numEmpleado[0])) {
        if (strpos($numEmpleado, "*")!==FALSE) $numEmpleado=str_replace("*", "%", $numEmpleado);
        $fldarr["numero"]=$numEmpleado;
    }
    if (isset($nombreEmpleado[0])) {
        if (strpos($nombreEmpleado, "*")!==FALSE) $nombreEmpleado=str_replace("*", "%", $nombreEmpleado);
        else if (strpos($nombreEmpleado, "%")===FALSE) $nombreEmpleado="%".$nombreEmpleado."%";
        $fldarr["nombre"]=$nombreEmpleado;
    }
    if (isset($cuentaTC[0])) {
        if (strpos($cuentaTC, "*")!==FALSE) $cuentaTC=str_replace("*", "%", $cuentaTC);
        else if (strpos($cuentaTC, "%")===FALSE&&!isset($cuentaTC[15])) $cuentaTC="%".$cuentaTC."%";
        $fldarr["cuentaTC"]=$cuentaTC;
    }
    if (isset($cuentaCLABE[0])) {
        if (strpos($cuentaCLABE, "*")!==FALSE) $cuentaCLABE=str_replace("*", "%", $cuentaCLABE);
        else if (strpos($cuentaCLABE, "%")===FALSE&&!isset($cuentaCLABE[17])) $cuentaCLABE="%".$cuentaCLABE."%";
        $fldarr["cuentaCLABE"]=$cuentaCLABE;
    }
    if (isset($empresaAlias)) $fldarr["empresa"]=$empresaAlias;
    if (isset($statusEmpleado)) $fldarr["status"]=$statusEmpleado;
    require_once "clases/Empleados.php";
    $empObj=new Empleados();
    if (empty($_POST["regPerPage"])) $empObj->rows_per_page=100;
    else $empObj->rows_per_page=+$_POST["regPerPage"];
    if (!empty($_POST["pageSwitch"])) {
        $empObj->pageno=+$_POST["pageSwitch"];
    }
    $empData=$empObj->getDataByFieldArray($fldarr);
    if (isset($empData[1])) $urlAction="empleados2";
    else $errorMessage .= "<p class='margin20 centered'>Ning&uacute;n registro encontrado con los criterios indicados.</p>";
} else if (isset($_POST["empl_submit"])) {
    if (!isset($numEmpleado[0])) {
        $errorMessage.="<p class='errorLine'>Es necesario indicar el <b>N&uacute;mero</b> de empleado.</p>";
        if (!isset($focusId)) $focusId="empl_num";
    } else if (!isset($nombreEmpleado[0])) {
        $errorMessage.="<p class='errorLine'>Debe capturar el <b>Nombre</b> del empleado.</p>";
        if (!isset($focusId)) $focusId="empl_name";
    } else if (!isset($empresaAlias[0])) {
        $errorMessage.="<p class='errorLine'>El empleado debe pertenecer a una <b>Empresa</b>.</p>";
        if (!isset($focusId)) $focusId="empl_gpoalias";
    } else if (!isset($statusEmpleado[0])) {
        $statusEmpleado="activo";
    }
    if ($statusEmpleado==="activo" && !isset($cuentaTC[0]) && !isset($cuentaCLABE[0])) {
        $errorMessage.="<p class='errorLine'>Se requiere que especifique al menos una <b>Cuenta bancaria</b>.</p>";
        if (!isset($focusId)) $focusId="empl_accountcard";
    }
    require_once "clases/Empleados.php";
    $empObj=new Empleados();
    DBi::autocommit(FALSE);
    if (empty($idEmpleado)) {
        if (!isset($errorMessage[0])) {
            if ($empObj->exists("numero='$numEmpleado'")) { // " AND empresa='$empresaAlias'"
                $errorMessage.="<p class='errorLine'>El <b>N&uacute;mero</b> de empleado debe ser &uacute;nico.</p>"; // por empresa
                if (!isset($focusId)) $focusId="empl_num";
            }
        }
    } else {
        list ($dbnum,$dbname,$dbBCC,$dbBUK,$dbCom,$dbStt) = explode("|",$empObj->getValue("id",$idEmpleado,"numero,nombre,cuentaTC,cuentaCLABE,empresa,status"));
        if ($dbnum!==$numEmpleado && $empObj->exists("numero='$numEmpleado'")) {
            $errorMessage-="<p class='errorLine'>El <b>N&uacute;mero</b> de empleado debe ser &uacute;nico.</p>";
            if (!isset($focusId)) $focusId="empl_num";
        }
    }
    $doSave=false;
    if (!isset($errorMessage[0])) {
        $fldarr=[];
        if (isset($idEmpleado[0])) $fldarr["id"]=$idEmpleado;
        else $fldarr["numero"]=$numEmpleado;
        if(!isset($dbname[0])||$dbname!==$nombreEmpleado) {
            $doSave=true;
            $fldarr["nombre"]=$nombreEmpleado;
        }
        if(!isset($dbBCC[0])||$dbBCC!==$cuentaTC) {
            $doSave=true;
            $fldarr["cuentaTC"]=$cuentaTC;
        }
        if(!isset($dbBUK[0])||$dbBUK!==$cuentaCLABE) {
            $doSave=true;
            $fldarr["cuentaCLABE"]=$cuentaCLABE;
        }
        if(!isset($dbCom[0])||$dbCom!==$empresaAlias) {
            $doSave=true;
            $fldarr["empresa"]=$empresaAlias;
        }
        if(!isset($dbStt[0])||$dbStt!==$statusEmpleado) {
            $doSave=true;
            $fldarr["status"]=$statusEmpleado;
        }
        if($doSave && $empObj->saveRecord($fldarr)) {
            $empLastId=$empObj->lastId;
        } else if ($doSave) {
            clog1($empObj->log);
            $errorMessage.="<p class='errorLine'>Error al guardar empleado $nombreEmpleado</p>";
        }
    }
    if (!isset($errorMessage[0]) && $doSave) {
        global $prcObj;
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj=new Proceso();
        }
        if ($prcObj->cambioEmpleado($empLastId??$idEmpleado,$statusEmpleado,getUser()->nombre,"Guardar $nombreEmpleado")) {
            $resultMessage.="<p class='successLine'>Empleado $nombreEmpleado guardado satisfactoriamente.</p>";
        } else {
            clog1($prcObj->log);
            $errorMessage.="<p class='errorLine'>Error al guardar proceso de registro.</p>";
        }
    }
    if (!isset($errorMessage[0]) && $doSave) {
        DBi::commit();
    } else {
        DBi::rollback();
    }
    DBi::autocommit(TRUE);
}

$canSaveClass="";
$browseType="submit";
if (isset($idEmpleado[0])||(isset($_POST["empl_submit"])&&isset($numEmpleado[0]))) { // isset($empLastId))
    $browseType="hidden";
} else {
    $canSaveClass=" class=\"hidden\"";
    $beginHidden.=" hidden";
}
*/

if (!isset($onloadScript)) $onloadScript="";
$onloadScript.="doFocusOn('empl_num');";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.empleados");
