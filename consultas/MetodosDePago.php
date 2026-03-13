<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
$mdpObj = dao("mdp");
if (isValueService()) getValueService($mdpObj);
else if (isFixMDP()) doFixMDP();
else if (isset($_GET["existe"])) {
    try {
      echo $mdpObj->exists("descripcion='$_GET[existe]'")?"SI":"NO";
    } catch (Exception $e) {
      echo "Error ".$e->getMessage();
    }
}
else if (isTestService()) getTestService($mdpObj);
else if (isCatalogService()) getCatalogService($mdpObj);
else if (isset($_GET["valida"])) {
    if ($mdpObj->esValido($_GET["valida"])) echo "Aceptado";
    else echo "Rechazado";
} else {
    echo "Metodos De Pago";
}
function isFixMDP() {
    return isset($_POST["action"])&&$_POST["action"]==="fixMDP";
}
function doFixMDP() {
    sessionInit();
    if (!validaPerfil("Administrador") && !validaPerfil("Sistemas")) die();
    DBi::autocommit(FALSE);
    $message="";
    $result=true;
    $hasChanges=false;
    $mdpObj=dao("mdp");
    if (isset($_POST["add"][0])) {
        require_once "clases/catalogoSAT.php";
        $where=rtrim(CatalogoSAT::getWhereCondition(CatalogoSAT::CAT_FORMAPAGO,"codigo",$_POST["add"]), " AND ");
        $map=CatalogoSAT::getFullMap(CatalogoSAT::CAT_FORMAPAGO, "codigo", "descripcion", $where);
        $valuesArray=[];
        foreach ($_POST["add"] as $value) $valuesArray[]=[$value,$map[$value]];
        $result=$mdpObj->insertMultipleRecords (["clave","descripcion"], $valuesArray);
        if ($result) $hasChanges=true;
    }
    if ($result&&isset($_POST["del"][0])) {
        $valuesArray=[];
        foreach ($_POST["del"] as $value) $valuesArray[]=[$value];
        $result=$mdpObj->deleteMultipleRecords (["clave"], $valuesArray);
        if ($result) $hasChanges=true;
    }
    if($result) {
        if($hasChanges) {
            DBi::commit();
            echo json_encode(["result"=>"exito"]);
        } else {
            DBi::rollback();
            echo json_encode(["result"=>"vacio","message"=>"No se registraron cambios."]);
        }
    } else {
        $message=$mdpObj->log;
        DBi::rollback();
        echo json_encode(["result"=>"RECIBIDO","message"=>$message]); // json_encode($_POST)
    }
    DBi::autocommit(TRUE);
}
