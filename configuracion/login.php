<?php
$submitted_username = "";
if ($habilitado) {
    global $prcObj;
    if (!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj = new Proceso(); }
}
if (!isValidBrowser()) {
    //clog3("BROWSER: ".getBrowser());
    //clog3("BROWSER USERAGENT: ".getBrowser("useragent"));
    //clog3("BROWSER VERSION: ".getBrowser("version"));
    //clog3("BROWSER DEBUG: ".getBrowser("debug"));
    $errorTitle = "ERROR";
    $errorMessage = "<p class='margin20 centered'>Actualmente esta aplicación sólo es compatible con <b><a href='https://www.google.com/chrome/'>Chrome</a></b> y <b><a href='https://www.microsoft.com/es-es/edge'>Edge</a></b>.</p>"."<i><small>"./*getBrowser("ua")."<br>".*/getBrowser("browser")."</small></i>";
    //global $ualog;
    //doclog("WRONG BROWSER","error",["browser"=>getBrowser("browser"),"useragent"=>getBrowser("ua")/*,"ualog"=>$ualog*/]);
} else if (isset($_REQUEST["logout"]) && $hasUser) {
    echo "<!-- BEGIN LOGOUT -->\n";
    if ($habilitado) $prcObj->cambioSesion($userid, "Cierre", $username, "Logout: ".$user->persona);
    sessionEnds();
    clearTokenName();
    $_SESSION=[];
    cleanUser();
    $resultTitle = "LOGOUT";
    $resultMessage = "<p class='margin20 centered'>Ha salido del sistema.</p>";
    $errorTitle = false;
} else if (isset($_POST["username"][0])) {
    $login_ok = false;
    //echo "<!-- USERNAME -->";
    $postUsername = htmlentities($_POST['username'], ENT_QUOTES, "UTF-8");
    if ($habilitado && isset($postUsername[0])) {
        global $usrObj;
        if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj = new Usuarios(); }
        $usrData = $usrObj->getData("nombre='$postUsername'", 1);
        $postPassword = htmlentities($_POST["password"], ENT_QUOTES, "UTF-8");
        if ($usrData) {
            $user = (object) $usrData[0];
            $user->isSystem=false;
            if (empty($user->password) && empty($postPassword)) {
                $login_ok=true;
                $user->cambiaClave=true;
            } else {
                $salt = $user->seguro;
                $check_password = hash('sha256', $postPassword.$salt);
                for($round=0; $round<65536; $round++) {
                    $check_password = hash('sha256', $check_password.$salt);
                }
                if($check_password === $user->password) {
                    $login_ok = true;
                } else if (!empty($user->unoComo)) { // ingresar como SISTEMAS
                    $syD = $usrObj->getData("id=".$user->unoComo, 1, "password,seguro");
                    if (isset($syD[0]["password"][0])) {
                        $syD=$syD[0];
                        $check_password = hash('sha256', $postPassword.$syD["seguro"]);
                        for($round2=0; $round2<65536; $round2++) {
                            $check_password = hash('sha256', $check_password.$syD["seguro"]);
                        }
                        if ($check_password === $syD["password"]) {
                            $login_ok = true;
                            $usrObj->saveRecord(["id"=>$user->id, "unoComo"=>null]);
                            unset($user->unoComo);
                            $user->isSystem=true;
                            $user->cambiaClave=false; // No puede cambiar contraseña de usuario si ingresó con contraseña de administrador
                        }
                    }
                }
            }
            unset($user->seguro);
            unset($user->password);
            unset($usrData);
        }
    }
    unset($_POST["password"]);
    if($login_ok && isset($user)) { // si $login_ok => $habilitado=true
        //echo "<!-- login ok -->";
        $user->project_name = $_project_name;
        if (!isset($user->cambiaClave)) $user->cambiaClave=false;
        global $upObj;
        if (!isset($upObj)) { require_once "clases/Usuarios_Perfiles.php"; $upObj = new Usuarios_Perfiles(); }
        $listaPerfilIds = $upObj->getList("idUsuario",$user->id,"idPerfil");
        if (!empty($listaPerfilIds)) $arrayPerfilIds = explode("|",$listaPerfilIds);
        if (!empty($arrayPerfilIds)) {
            global $perObj;
            if (!isset($perObj)) { require_once "clases/Perfiles.php"; $perObj = new Perfiles(); }
            $listaPerfiles = $perObj->getList("id",$arrayPerfilIds,"nombre");
            if (!empty($listaPerfiles)) $user->perfiles = explode("|",$listaPerfiles);
        }
        if (isset($_SESSION['user']) && $_SESSION['user']->id!==$user->id && !$user->isSystem) {
            //sessionEnds();
            //clearTokenName();
            //$_SESSION=[];
            session_destroy();
            $_SESSION=[];
        }
        $_SESSION['user'] = $user;
        $_SESSION['tmp'] = "loggedin2";
        setUser();
        $prcObj->cambioSesion($userid, "Inicio", $username, "Login: ".$user->persona);
        include_once "configuracion/loggedInCheck.php";
        if ($_esProveedor) {
            require_once "clases/Proveedores.php";
            /* HABILITAR PARA MANDAR MENSAJE A PROVEEDORES */
            //$_SESSION['MENSAJE_INICIAL'] = MENSAJE_NAVIDAD2023;//MENSAJE_LIMITE_MES;
            //
            // $_SESSION["MENSAJE_INICIAL"]=MENSAJE_FINV33;
            global $prvObj;
            if (!isset($prvObj)) { require_once "clases/Proveedores.php"; $prvObj = new Proveedores(); }
            $prvData = $prvObj->getData("codigo='$username'", 1, "id,codigo,razonSocial,rfc,zona,cuenta,edocta,credito,banco,rfcbanco,codigoFormaPago,status,verificado,opinion,cumplido,venceopinion, date(venceopinion)<date(now()) vencido");
            if (isset($prvData[0])) {
                $prvData=$prvData[0];
                // $_SESSION['MENSAJE_INICIAL'] = isset($prvData["rfc"][12])?MENSAJE_NAVIDAD2024PF:MENSAJE_NAVIDAD2024PM;
                if ($prvData["status"]==="eliminado") {
                    $errorMessage = "<p class='fontRelevant margin20 centered'>El usuario no está habilitado en el sistema</p>";
                    $submitted_username = $postUsername;
                } else {
                    $user->proveedor=(object)$prvData;
                    if ($user->proveedor->cumplido>0&&!empty($user->proveedor->vencido)) {
                        $prvObj->updateRecord(["id"=>$user->proveedor->id,"cumplido"=>"-1"]);
                        $user->proveedor->cumplido="-1";
                    }
                }
            } else {
                $errorMessage = "<p class='fontRelevant margin20 centered'>El usuario y/o la clave no son correctos</p>";
                $submitted_username = $postUsername;
            }
        }
        if ($_esCompras) {
            // HABILITAR PARA MANDAR MENSAJE A USUARIOS COMPRAS 
            //$_SESSION["MENSAJE_INICIAL_COMPRAS"]=MENSAJE_NAVIDAD2023;//MENSAJE_LIMITE_MES_INTERNO;//MENSAJE_FINV33;
            // Definido en configuracion.inicializacion y asignado en templates.login (line:46)
        }
        if ($_esAdministrador||$_esSistemas) {
            // HABILITAR PARA MANDAR MENSAJE A SISTEMAS 
            //$_SESSION["MENSAJE_INICIAL_COMPRAS"]=MENSAJE_NAVIDAD2023;//MENSAJE_LIMITE_MES_INTERNO;//MENSAJE_FINV33;
            // Definido en configuracion.inicializacion y asignado en templates.login (line:46)
        }
        if (($_esAdministrador || $_esSistemas || $_esCompras) && isset($_SESSION['MENSAJE_INICIAL_COMPRAS'][0])) $_SESSION['MENSAJE_NOTICIA'] = $_SESSION['MENSAJE_INICIAL_COMPRAS'];
        else if (isset($_SESSION['MENSAJE_INICIAL'][0])) $_SESSION['MENSAJE_NOTICIA'] = $_SESSION['MENSAJE_INICIAL'];

        if (!isset($errorMessage[0])) {
            if (empty($rediurl))
                $rediurl = "/".$_project_name."/";

            header("Location: $rediurl");
            die("Redirecting to: $rediurl");
        }
    } else {
        $errorMessage = "<p class='fontRelevant margin20 centered'>El usuario y/o la clave no son correctos</p>";
        $submitted_username = $postUsername;
        doclog("SIN LOGIN","error",["post"=>$_POST, "session"=>$_SESSION, "postUsername"=>$postUsername]);
        sessionEnds();
        clearTokenName();
        $_SESSION=[];
        cleanUser();
    }
} else if ($hasUser && $habilitado) {
    include_once "configuracion/loggedInCheck.php";
    if (!empty($user->cambiaClave) && isset($_POST["password"][0]) && isset($_POST["password2"][0])) {
        if ($_POST["password"]!==$_POST["password2"]) {
            $errorMessage="<p>No coinciden los campos de clave y confirmaci&oacute;n</p>";
        } else {
            $postPassword = htmlentities($_POST["password"], ENT_QUOTES, "UTF-8");
            $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
            $chkPwd=hash('sha256',$postPassword.$salt);
            for($round=0;$round<65536; $round++) {
                $chkPwd=hash('sha256',$chkPwd.$salt);
            }
            DBi::autocommit(FALSE);
            $fldarr=["id"=>$userid, "password"=>$chkPwd, "seguro"=>$salt, "banderas"=>($user->banderas^1)];
            global $usrObj;
            if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj = new Usuarios(); }
            if($usrObj->saveRecord($fldarr)) {
                $prcObj->cambioUsuario($userid,"Clave",$username,"Cambia Clave");
                $user->cambiaClave=false;
                $user->banderas=$user->banderas^1;
                DBi::commit();
                $resultMessage="<p>Su contraseña se ha actualizado.</p>";
            } else {
                DBi::rollback();
                $errorMessage="<p>Error al guardar usuario ".$username.".</p>";
            }
            DBi::autocommit(TRUE);
            unset($salt);
            unset($chkPwd);
            unset($fldarr["password"]);
            unset($fldarr["seguro"]);
        }
    } else if ($_esProveedor) {
        global $prvObj;
        if (!isset($prvObj)) { require_once "clases/Proveedores.php"; $prvObj = new Proveedores(); }
        $prvData=$prvObj->getData("codigo='".$username."'", 1, "status");

        if (isset($prvData[0])) {
            //echo "<!-- STATUS=".$prvData[0]["status"]." -->";
            $user->proveedor->status=$prvData[0]["status"];
        //} else {
            //global $query;
            //echo "<!-- NO STATUS. query: $query -->";
        }
    } else {
        global $usrObj;
        if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj = new Usuarios(); }
        $usrData = $usrObj->getData("id=".$userid, 1, "banderas");
        if (isset($usrData[0])) {
            $user->cambiaClave=(($usrData[0]["banderas"]&1)>0);
        }
    }
    unset($_POST["password"]);
    unset($_POST["password2"]);
} //else echo "<!-- notin -->";
//if ($_esAdministrador||$_esSistemas) {
    //clog3("BROWSER: ".getBrowser());
    //clog3("BROWSER USERAGENT: ".getBrowser("useragent"));
    //clog3("BROWSER VERSION: ".getBrowser("version"));
    //clog3("BROWSER DEBUG: ".getBrowser("debug"));
//}
