<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Usuarios.php";

doclog("Consultas Usuarios","usuarios",["get"=>$_GET,"post"=>$_POST,"request"=>$_REQUEST,"isValue"=>(isset($_REQUEST["llave"])?"SI":"NO")]);
global $usrObj;
$usrObj = new Usuarios();
if (isValueService()) {
    if (!empty(getCIKeyVal($_REQUEST,"getJSON"))) getValueService($usrObj);
    else getValueService2();
} else if (isNewKeyService()) getNewKeyService();
else if (isTestService()) getTestService($usrObj);
else if (isCatalogService()) getCatalogService($usrObj);
else if (isReloadUser()) doReloadUser();
else if (isItchlet()) getItchlet();
else if (isSessionService()) doSessionService();
else if (isset($_POST["accion"])) {
    switch($_POST["accion"]) {
        case "browseUserName": doBrowseUserName(); break;
    }
} else echo "unknown request";
die();
function doBrowseUserName() {
    global $query, $usrObj;
    sessionInit();
    $name=$_POST["nombre"]??"";
    $exceptionList=$_POST["exceptions"]??"";
    $onlyName=isset($_POST["onlyName"])?true:false;
    if (!isset($name[0])) {
        echo json_encode(["result"=>"failure","message"=>"Debe indicar nombre de usuario o persona"]);
        die();
    }
    if (isset($_POST["sortList"])) {
        $sortList=explode(",", $_POST["sortList"]);
        $usrObj->clearOrder();
        foreach ($sortList as $idx => $sortItem) {
            $sortWords=explode(" ",$sortItem);
            if (isset($sortWords[0][0])) {
                if (isset($sortWords[1]) && $sortWords[1]==="desc") $usrObj->addOrder($sortWords[0], "desc");
                else $usrObj->addOrder($sortWords[0]);
            }
        }
    }
    $usrObj->rows_per_page=0;
    $dbdata=$usrObj->browseByUserName($name,$exceptionList,$onlyName);
    if (isset($dbdata[0])) echo json_encode(["result"=>"success","message"=>"Se encontraron $usrObj->numrows registros","data"=>$dbdata,"query"=>$query]);
    else echo json_encode(["result"=>"failure","message"=>"No se encontraron registros relacionados.","query"=>$query,"oerrors"=>$usrObj->errors,"ierrors"=>DBi::$errors]);
}
function isNewKeyService() {
    return isset($_GET["clase"]) && isset($_GET["opcion"]) && $_GET["opcion"]=="generaClave";
}
function getNewKeyService() {
    DBi::connect();
    if (isset($_GET["largo"]) && ctype_digit($_GET["largo"])) $length = $_GET["largo"];
    else $length = 8;
    echo getToken($length);
    DBi::close();
}
function isSessionService() {
    return isset($_POST["sessionKey"][0]);
}
function doSessionService() {
    doclog("SETTING VALUE","session",$_POST);
    $sessionKey=$_POST["sessionKey"];
    $sessionValue=$_POST["sessionValue"]??null;
    if (isset($sessionValue)) {
        $_SESSION[$sessionKey]=$sessionValue;
        echo "SET '$sessionKey'='$sessionValue'";
    } else {
        $_SESSION[$sessionKey]=null;
        unset($_SESSION[$sessionKey]);
        echo "DEL '$sessionKey'";
    }
    doclog("VALUE SET","session",[$sessionKey=>$_SESSION[$sessionKey]??"null"]);
}
function isReloadUser() {
    return ($_POST["action"]??"")==="reloadUser";
}
function doReloadUser() {
    // 1 = usuario.id, usuario.fechaRegistro, project_name
    // 2 = usuario.nombre, usuario.persona, usuario.email, usuario.observaciones, usuario.banderas
    // 4 = usuarios_perfiles, usuario->permisos
    $flag=+($_POST["flag"]??"6");
    $trace="Flag=$flag";
    sessionInit();
    if (!hasUser()) reloadNDie("NO USER",$_POST,"usuario");
    global $usrObj;
    if ($flag>0) {
        $trace.="|GETUSER";
        $usr=getUser();
        $username=$usr->nombre;
        $usrData = $usrObj->getData("nombre='$username'");
        if (isset($usrData[0]["nombre"])) $usrData = (object) $usrData[0];
    }
    if (isset($usr)) {
        if ($flag&1) {
            global $_project_name;
            $usr->id=$usrData->id;
            $usr->fechaRegistro=$usrData->fechaRegistro;
            $usr->project_name=$_project_name;
            $trace.="|F1";
        }
        if ($flag&2) {
            $usr->nombre=$usrData->nombre;
            $usr->persona=$usrData->persona;
            $usr->email=$usrData->email;
            $usr->observaciones=$usrData->observaciones;
            $usr->banderas=$usrData->banderas;
            $trace.="|F2";
        }
    }
    if ($flag&4) {
        global $upObj;
        if (!isset($upObj)) {
            require_once "clases/Usuarios_Perfiles.php";
            $upObj = new Usuarios_Perfiles();
        }
        $listaPerfilIds = $upObj->getList("idUsuario",$usr->id,"idPerfil");
        $trace.="|F4";
        if (!empty($listaPerfilIds)) $arrayPerfilIds = explode("|",$listaPerfilIds);
        if (!empty($arrayPerfilIds)) {
            global $perObj;
            if (!isset($perObj)) {
                require_once "clases/Perfiles.php";
                $perObj = new Perfiles();
            }
            $listaPerfiles = $perObj->getList("id",$arrayPerfilIds,"nombre");
            if (!empty($listaPerfiles)) {
                $usr->perfiles = explode("|",$listaPerfiles);
                $trace.="|Perfiles";
            }
        }
    }
    unset($GLOBALS["consultaAlta"],$GLOBALS["consultaProc"],$GLOBALS["consultaConR"],$GLOBALS["consultaExpr"],$GLOBALS["consultaResp"],$GLOBALS["consultaGrpo"],$GLOBALS["consultaProv"],$GLOBALS["consultaUsrs"],$GLOBALS["consultaCata"],$GLOBALS["consultaData"],$GLOBALS["consultaPerm"],$GLOBALS["consultaRepo"],$GLOBALS["consultaUsrs"],$GLOBALS["modificaUsrs"],$GLOBALS["modificaGrpo"],$GLOBALS["modificaProv"],$GLOBALS["modificaPerm"],$GLOBALS["modificaCata"],$GLOBALS["modificaData"],$GLOBALS["modificaRepo"],$GLOBALS["consultaEmpl"],$GLOBALS["modificaEmpl"],$GLOBALS["consultaNomi"],$GLOBALS["modificaNomi"],$GLOBALS["generaCitas"],$GLOBALS["consultaCitas"],$GLOBALS["modificaCitas"]);
    if (isset($usr)) {
        $oldUsr=$_SESSION['user'];
        $_SESSION['user']=$usr;
        unset($_SESSION['user']->permisos);
        $_COOKIE['sessionRestart'] = "2";
        setcookie('sessionRestart',"2",0,'/invoice');
        successNDie("Usuario actualizado",["usr"=>(array)$usr,"old"=>$oldUsr,"trace"=>$trace],"usuario");
    }
    doclog("Actualizacion no realizada","usuario",["sesusr"=>$_SESSION['user'],"trace"=>$trace]+$_POST);
    errNDie("Actualización no realizada",["trace"=>$trace]);
}
function isItchlet() {
    return isset($_POST["accion"])&&$_POST["accion"]==="itch";
}
function getItchlet() {
    global $usr, $usrObj, $_project_name;
    if (hasUser()&&validaPerfil("Administrador")&&!isset($_POST["original"])) {
        if (empty($_POST["uid"])) {
            echo json_encode(["result"=>"error","message"=>"Usuario indefinido."]);
            die();
        }
        if (empty($_POST["pid"])) {
            echo json_encode(["result"=>"error","message"=>"Proveedor indefinido."]);
            die();
        }
        $itchData = $usrObj->getData("id='$_POST[uid]'");
        if (empty($itchData)) {
            echo json_encode(["result"=>"error","message"=>"Usuario desconocido."]);
            die();
        }
        $itchUsr = (object) $itchData[0];
        unset($itchUsr->seguro);
        unset($itchUsr->password);
        $itchUsr->project_name = $_project_name;

        require_once "clases/Proveedores.php";
        $prvObj=new Proveedores();
        $prvData = $prvObj->getData("id='$_POST[pid]'",0,"id,codigo,razonSocial,rfc,zona,cuenta,edocta,credito,banco,rfcbanco,codigoFormaPago,status,verificado,opinion,cumplido,venceopinion, date(venceopinion)<date(now()) vencido");
        if (empty($prvData)) {
            echo json_encode(["result"=>"error","message"=>"Proveedor desconocido."]);
            die();
        }
        $itchUsr->proveedor=(object)$prvData[0];
        if ($itchUsr->proveedor->cumplido>0&&!empty($itchUsr->proveedor->vencido)) {
            $prvObj->updateRecord(["id"=>$_POST["pid"],"cumplido"=>"-1"]);
            $itchUsr->proveedor->cumplido="-1";
        }

        require_once "clases/Usuarios_Perfiles.php";
        if (!isset($upObj)) $upObj = new Usuarios_Perfiles();
        $listaPerfilIds = $upObj->getList("idUsuario",$_POST["uid"],"idPerfil");
        if (!empty($listaPerfilIds)) $arrayPerfilIds = explode("|",$listaPerfilIds);
        if (!empty($arrayPerfilIds)) {
            require_once "clases/Perfiles.php";
            if (!isset($perObj)) $perObj = new Perfiles();
            $listaPerfiles = $perObj->getList("id",$arrayPerfilIds,"nombre");
            if (!empty($listaPerfiles)) $itchUsr->perfiles = explode("|",$listaPerfiles);
        }

        $itchUsr->itch=getUser();
        $_SESSION['user']=$itchUsr;
        $usr=$itchUsr;
        echo json_encode(["message"=>"UID=".($_POST["uid"]??"NULL").",PID=".($_POST["pid"]??"NULL"),"original"=>getUser()]);
    } else if (hasUser()&&isset($_POST["original"])) {
        global $usr;
        if(!isset($usr)) $usr=getUser();
        if (isset($usr->itch)) {
            $_SESSION['user']=$usr->itch;
            $usr=$usr->itch;
            echo json_encode(["original"=>getUser()]);
        }
        echo json_encode(["original"=>getUser()]);
    } else {
        $key="".(hasUser()?"1":"0").(validaPerfil("Administrador")?"1":"0");
        echo json_encode(["error"=>"$key","user"=>getUser()]);
    }
}
function getValueService2() {
    // global $usrObj;
    DBi::connect();
    // GET[llave] debe ser el codigo/nombre del usuario y se regresa el campo nombre/persona en Usuario
    // En scripts/bitacora.php usar directamente ajaxRequest para solicitar por GET el nombre de la persona
    //  usando solo el parametro llave para mandar el codigo de usuario(Usuarios.nombre)
    // Este servicio debe devolver unicamente el nombre de la persona(Usuarios.persona)
    DBi::close();
}
