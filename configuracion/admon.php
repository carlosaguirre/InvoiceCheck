<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esComExt     = validaPerfil("ComercioExterior Admin")||
                validaPerfil("ComercioExterior Control")||
                $_esSistemas;
if (!isset($consultaUsrs)) $consultaUsrs = consultaValida("Usuarios")||$_esSistemas;
if (!isset($consultaGrpo)) $consultaGrpo = consultaValida("Grupo")||$_esSistemas;
if (!isset($consultaProv)) $consultaProv = consultaValida("Proveedor")||$_esSistemas;
if (!isset($consultaPerm)) $consultaPerm = consultaValida("Permisos")||$_esSistemas;
$consultaBancos = $_esSistemas;
if (!isset($modificaUsrs)) $modificaUsrs = modificacionValida("Usuarios")||$_esSistemas;
if (!isset($modificaGrpo)) $modificaGrpo = modificacionValida("Grupo")||$_esSistemas;
if (!isset($modificaProv)) $modificaProv = modificacionValida("Proveedor")||$_esSistemas;
if (!isset($modificaPerm)) $modificaPerm = modificacionValida("Permisos")||$_esSistemas;

$modificaBancos = $_esSistemas;
if (!$consultaGrpo && !$consultaProv && !$consultaPerm && !$modificaUsrs && !$consultaBancos && !$_esSistemas) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}

clog2ini("configuracion.admon");
clog1seq(1);

if (!isset($prcObj)) {
    require_once "clases/Proceso.php";
    $prcObj = new Proceso();
}
if (!isset($bnkObj)) {
    require_once "clases/Bancos.php";
    $bnkObj = new Bancos();
}
if (isset($_POST["proveedor_code"])) $codigoProveedor = $_POST["proveedor_code"];
if (isset($_POST["proveedor_field"])) $razonProveedor = $_POST["proveedor_field"];
if (isset($_POST["proveedor_rfc"])) $rfcProveedor = $_POST["proveedor_rfc"];
if (isset($_POST["proveedor_zona"])) $zonaProveedor = $_POST["proveedor_zona"];
if (isset($_POST["proveedor_status"])) $statusProveedor = $_POST["proveedor_status"];
if (isset($_POST["proveedor_id"])) $idProveedor = $_POST["proveedor_id"];
if (isset($_POST["grupo_field"])) $razonEmpresa = $_POST["grupo_field"];
if (isset($_POST["grupo_alias"])) $aliasEmpresa = $_POST["grupo_alias"];
if (isset($_POST["grupo_rfc"])) $rfcEmpresa = $_POST["grupo_rfc"];
if (isset($_POST["grupo_cut"])) $cutEmpresa = strtoupper($_POST["grupo_cut"]??"");
if (isset($_POST["grupo_filtro"])) $filtroEmpresa = $_POST["grupo_filtro"]??[];
if (isset($_POST["grupo_id"])) $idEmpresa = $_POST["grupo_id"];
if (isset($_POST["user_field"])) $nombreUsuario = $_POST["user_field"];
if (isset($_POST["user_realname"])) $personaUsuario = $_POST["user_realname"];
if (isset($_POST["user_email"])) $emailUsuario = $_POST["user_email"];
if (isset($_POST["user_obs"])) $obsUsuario = $_POST["user_obs"];
if (isset($_POST["user_id"])) $idUsuario = $_POST["user_id"];
if (isset($_POST["banco_field"])) $razonBanco = trim($_POST["banco_field"]??"");
if (isset($_POST["banco_alias"])) $aliasBanco = trim($_POST["banco_alias"]??"");
if (isset($_POST["banco_clave"])) $claveBanco = trim($_POST["banco_clave"]??"");
if (isset($_POST["banco_rfc"])) $rfcBanco = trim($_POST["banco_rfc"]??"");
if (isset($_POST["banco_status"])) $statusBanco = trim($_POST["banco_status"]??"");
if (isset($_POST["banco_cuenta"])) $cuentaBanco = trim($_POST["banco_cuenta"]??"");
if (isset($_POST["banco_id"])) $idBanco = $_POST["banco_id"]??"";
if (isset($_POST["proveedor_submit"])) {
    if ($modificaProv) {
        $fldarr = [];
        if (empty($rfcProveedor)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar el RFC del proveedor</P>";
        } else $fldarr["rfc"] = $rfcProveedor;
        if (empty($razonProveedor)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar la raz&oacute;n social del proveedor</P>";
        } else $fldarr["razonSocial"] = $razonProveedor;
        if (empty($codigoProveedor)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar un c&oacute;digo para el proveedor</P>";
        } else $fldarr["codigo"] = $codigoProveedor;
        if (empty($statusProveedor)) $statusProveedor="activo";
        $fldarr["status"] = $statusProveedor;
        require_once "clases/CatLista69B.php";
        if (!empty($rfcProveedor) && CatLista69B::estaMarcado($rfcProveedor)) {
            $errorMessage="<P class='fontMedium margin20 centered'>El proveedor se encuentra en el listado 69-B por lo que no se autoriza su registro al portal.</P>";
        }
        if (empty($errorMessage)) {
            if (!empty($idProveedor))
                $fldarr["id"] = $idProveedor;
            if (!empty($zonaProveedor))
                $fldarr["zona"] = $zonaProveedor;
            if (!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj = new Proveedores();
            }
            if ($prvObj->saveRecord($fldarr)||empty(DBi::$errno)) {
                unset($_SESSION['prvRazSocOpt']);
                unset($_SESSION['prvCodigoOpt']);
                unset($_SESSION['prvRFCOpt']);
                $prvLastId = $prvObj->lastId;
                $resultMessage .= "<P class='fontMedium margin20 centered'>Proveedor $razonProveedor guardado satisfactoriamente</P>";
                if (empty($fldarr["id"])) { $procIdentif = $prvLastId;    $procStatus = "Alta";   }
                else                      { $procIdentif = $fldarr["id"]; $procStatus = "Cambio"; }
                $procDetalle = "ADMON Prv "+json_encode($fldarr);
                $prcObj->cambioProveedor($procIdentif, $procStatus, getUser()->nombre, $procDetalle);
                clog2("SUCCESS SAVING PRV LOG:\n".$prvObj->log);
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al guardar proveedor $razonProveedor</P>";
                global $query;
                doclog("Error(ADMON) al guardar proveedor","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"error"=>DBi::$error,"errno"=>DBi::$errno]);
            }
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para modificar proveedores</P>";
        doclog("Acceso invalido (ADMON) para modificar proveedores","error",["post"=>$_POST]);
    }
} else if (isset($_POST["proveedor_delete"])) {
    if ($modificaProv) {
        if (empty($idProveedor)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario seleccionar un proveedor para poder borrarlo</P>";
        } else {
            if (!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj = new Proveedores();
            }
            if ($prvObj->deleteRecord(["id"=>$idProveedor])/* && $prvObj->affectedrows*/) {
                unset($_SESSION['prvRazSocOpt']);
                unset($_SESSION['prvCodigoOpt']);
                unset($_SESSION['prvRFCOpt']);
                $resultMessage .= "<P class='fontMedium margin20 centered'>Proveedor $razonProveedor eliminado satisfactoriamente</P>";
                $procDetalle = "Proveedor";
                if (!empty($codigoProveedor)) $procDetalle .= " $codigoProveedor";
                if (!empty($razonProveedor)) $procDetalle .= " $razonProveedor";
                $prcObj->cambioProveedor($idProveedor, "Baja", getUser()->nombre, $procDetalle);
                clog2("SUCCESS DELETING PRV LOG:\n".$prvObj->log);
                $prvLastId = "";
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al eliminar proveedor $razonProveedor</P>";
                global $query;
                doclog("Error(ADMON) al eliminar proveedor","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"error"=>DBi::$error,"errno"=>DBi::$errno]);
            }
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para eliminar proveedores</P>";
        doclog("Acceso inválido (ADMON) para eliminar proveedores","error",["post"=>$_POST]);
    }
} else if (isset($_POST["grupo_submit"])) {
    if ($modificaGrpo) {
        $fldarr = [];
        if (empty($razonEmpresa)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar la raz&oacute;n social de la empresa del corporativo</P>";
        } else $fldarr["razonSocial"] = $razonEmpresa;
        if (empty($aliasEmpresa)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar un alias para la empresa del corporativo</P>";
        } else $fldarr["alias"] = $aliasEmpresa;
        if (empty($rfcEmpresa)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar el RFC de la empresa del corporativo</P>";
        } else $fldarr["rfc"] = $rfcEmpresa;
        if (!isset($cutEmpresa[2])) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Debe indicar un prefijo de 3 caracteres para las solicitudes de pago</P>";
            //$cutEmpresa=strtoupper(substr($aliasEmpresa, 0, 3));
            //if (!isset($cutEmpresa[2])) $cutEmpresa=strtoupper(substr($rfcEmpresa, 0, 3));
        } else {
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj = new Grupo();
            }
            $gpoData=$gpoObj->getData("cut='$cutEmpresa'");
            if (isset($gpoData[0]["id"])) $errorMessage .= "<P class='fontMedium margin20 centered'>El prefijo para solicitudes de pago debe ser único</P>";
            else $fldarr["cut"] = $cutEmpresa;
        }
        $filtroEmpresaSum=0;
        foreach (["1","2","4"] as $f) {
            if(in_array($f, $filtroEmpresa))
                $filtroEmpresaSum += +$f;
        }
        $fldarr["filtro"] = $filtroEmpresaSum;
        
        /*if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj = new Grupo();
        }
        while(!isset($cutEmpresa[3])) {
            $gpoData=$gpoObj->getData("cut='$cutEmpresa'");
            if (isset($gpoData[0]["id"])) $cutEmpresa++;
            else break;
        }
        if (!isset($cutEmpresa[3])) $fldarr["cut"]=$cutEmpresa;*/
        $fldarr["status"] = "activo";

//            if (empty($statusEmpresa)) {
//                $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar el Status de la empresa del corporativo</P>";
//            } else $fldarr["status"] = $statusEmpresa;
        if (empty($errorMessage)) {
            if (!empty($idEmpresa))
                $fldarr["id"] = $idEmpresa;
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj = new Grupo();
            }
            if ($gpoObj->saveRecord($fldarr)||empty(DBi::$errno)) {
                unset($_SESSION['gpoRazSocOpt']);
                unset($_SESSION['gpoCodigoOpt']);
                unset($_SESSION['gpoRFCOpt']);
                $gpoLastId = $gpoObj->lastId;
                $resultMessage .= "<P class='fontMedium margin20 centered'>Empresa $razonEmpresa guardada satisfactoriamente</P>";
                if (empty($fldarr["id"])) { $procIdentif = $gpoLastId;    $procStatus = "Alta";   }
                else                      { $procIdentif = $fldarr["id"]; $procStatus = "Cambio"; }
                $procDetalle = "Corporativo "+$fldarr["codigo"]+" "+$fldarr["razonSocial"];
                $prcObj->cambioAdmin($procIdentif, $procStatus, getUser()->nombre, $procDetalle);
                clog2("SUCCESS SAVING GRP LOG:\n".$gpoObj->log);
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al guardar empresa $razonEmpresa</P>";
                doclog("Error(ADMON) al guardar empresa del corporativo","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"error"=>DBi::$error,"errno"=>DBi::$errno]);
            }
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para modificar empresas del grupo</P>";
        doclog("Acceso inválido (ADMON) al guardar empresa del corporativo","error",["post"=>$_POST]);
    }
} else if (isset($_POST["grupo_delete"])) {
    if ($modificaGrpo) {
        if (empty($idEmpresa)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario seleccionar una empresa del corporativo para poder borrarla</P>";
        } else {
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj = new Grupo();
            }
            if ($gpoObj->deleteRecord(["id"=>$idEmpresa])/* && $gpoObj->affectedrows*/) {
                unset($_SESSION['gpoRazSocOpt']);
                unset($_SESSION['gpoCodigoOpt']);
                unset($_SESSION['gpoRFCOpt']);
                $resultMessage .= "<P class='fontMedium margin20 centered'>Empresa $razonEmpresa eliminada satisfactoriamente</P>";
                $procDetalle = "Corporativo";
                if (!empty($aliasEmpresa)) $procDetalle .= " ".$aliasEmpresa;
                if (!empty($razonEmpresa)) $procDetalle .= " ".$razonEmpresa;
                $prcObj->cambioAdmin($idEmpresa, "Baja", getUser()->nombre, $procDetalle);
                clog2("SUCCESS DELETING GRP LOG:\n".$gpoObj->log);
                $gpoLastId = "";
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al eliminar empresa $razonEmpresa</P>";
                doclog("Error(ADMON) al eliminar empresa del corporativo","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"error"=>DBi::$error,"errno"=>DBi::$errno]);
            }
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para borrar empresas del corporativo</P>";
        doclog("Acceso inválido (ADMON) para eliminar empresa del corporativo","error",["post"=>$_POST]);
    }
} else if (isset($_POST["user_submit"])) {
    if ($modificaUsrs) {
        $fldarr = [];
        if (empty($nombreUsuario)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar el usuario</P>";
        } else $fldarr["nombre"] = $nombreUsuario;
        if (empty($personaUsuario)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar el nombre de la persona</P>";
        } else $fldarr["persona"] = $personaUsuario;
        if (empty($emailUsuario)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar el correo electr&oacute;nico del usuario</P>";
        } else $fldarr["email"] = $emailUsuario;
        if (!empty($obsUsuario)) $fldarr["observaciones"] = $obsUsuario;
        if (empty($errorMessage)) {
            if (!empty($idUsuario))
                $fldarr["id"] = $idUsuario;
            if (!empty($_POST["user_password"])) {
                $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
                $password = hash("sha256", $_POST["user_password"] . $salt);
                for($round=0; $round<65536; $round++) {
                    $password=hash('sha256', $password . $salt);
                }
                $fldarr["password"] = $password;
                $fldarr["seguro"] = $salt;
                unset($salt);
                unset($password);
            }
            $kval=$_POST["user_updval"]??"0";
            $kupd=$_POST["user_updkey"]??"0";
            if ($kval!==$kupd) {
                $fldarr["banderas"] = $kupd;//getUser()->banderas^1;
            }
            if (!isset($usrObj)) {
                require_once "clases/Usuarios.php";
                $usrObj = new Usuarios();
            }
            echo "<!-- TEST SISTEMAS -->\n";
            if ($esSistemas) {
                echo "<!-- SI ES SISTEMAS -->\n";
                $sval=$_POST["user_sysval"]??"0";
                $skey=$_POST["user_syskey"]??"0";
                echo "<!-- sval=$sval, $skey=$skey -->\n";
                if ($sval!==$skey) {
                    $sysId="0";
                    if ($skey=="1") {
                        //$usrData=$usrObj->getData("nombre='SISTEMAS'",0,"id");
                        $sysId=getUser()->id; //$usrData[0]["id"]??"0";
                    }
                    $fldarr["unoComo"]=$sysId;
                    echo "<!-- set unoComo $sysId -->\n";
                }
            }
            global $query;
            DBi::autocommit(FALSE);
            $saveResult=$usrObj->saveRecord($fldarr);
            unset($fldarr["password"]);
            unset($fldarr["seguro"]);
            if ($saveResult) doclog("DATOS DE USUARIO GUARDADOS","usuarios",["query"=>$query,"fldarr"=>$fldarr,"sval"=>$sval,"skey"=>$skey,"sysId"=>$sysId??""]);
            else doclog("DATOS DE USUARIO NO GUARDADOS","usuarios",["query"=>$query,"fldarr"=>$fldarr,"sval"=>$sval,"skey"=>$skey,"sysId"=>$sysId??"","errors"=>DBi::$errors]);
            if (empty(DBi::$errors) && !empty($idUsuario)) {
                $usrObj->lastId=$idUsuario;
                $keepGoing=true; // sin errores, aceptar q no se haya guardado nada.
            } else $keepGoing=$saveResult;
            if ($keepGoing) {
                $usrLastId = $usrObj->lastId;
                $resultMessage .= "<P class='fontMedium margin20 centered'>Usuario $nombreUsuario guardado satisfactoriamente</P>";
                if ($saveResult) {
                    if (empty($fldarr["id"])) { $procIdentif = $usrLastId;    $procStatus = "Alta";   }
                    else                      { $procIdentif = $fldarr["id"]; $procStatus = "Cambio"; }
                    $procDetalle = "Usuario ".$fldarr["nombre"]." ".$fldarr["persona"];
                    $prcObj->cambioAdmin($procIdentif, $procStatus, getUser()->nombre, $procDetalle);
                    doclog("DATOS DE PROCESO GUARDADOS","usuarios",["query"=>$query]);
                }
                // Actualizar TABLA usuarios_perfiles
                $userPerfil = $_POST["user_perfil"]??[]; // obtener lista nueva de perfiles
                $userPerfilOld = $_POST["user_perfilOld"]??[]; // obtener lista vieja de perfiles
                // extraer perfiles a borrar (existen en la lista vieja pero no en la nueva)
                $delProfileList=array_values(array_diff($userPerfilOld, $userPerfil));
                // extraer perfiles a agregar (existen en la lista nueva pero no en la vieja)
                $newProfileList=array_values(array_diff($userPerfil, $userPerfilOld));
                if (isset($delProfileList[0])) { // eliminar perfiles encontrados
                    if (!isset($upObj)) { require_once "clases/Usuarios_Perfiles.php"; $upObj = new Usuarios_Perfiles(); }
                    $delRes = $upObj->deleteRecord(["idUsuario"=>$usrLastId,"idPerfil"=>$delProfileList]);
                    if (!empty(DBi::$errors)) {
                        $keepGoing=false;
                        $errorMessage.="<P class='fontMedium margin20 centered'>Error al remover perfiles de usuario</P>";
                        doclog("Error(ADMON) al remover perfiles de usuario","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors]);
                    } else if ($delRes) doclog("DATOS DE PERFIL ELIMINADOS","usuarios",["query"=>$query]);
                }
                if ($keepGoing && isset($newProfileList[0])) { // agregar los nuevos nada más
                    $valuesArray=[];
                    foreach ($newProfileList as $idx => $value) {
                        $valuesArray[]=[$usrLastId,$value];
                    }
                    // insertar perfiles encontrados
                    if (!isset($upObj)) { require_once "clases/Usuarios_Perfiles.php"; $upObj = new Usuarios_Perfiles(); }
                    $newRes = $upObj->insertMultipleRecords(["idUsuario","idPerfil"], $valuesArray);
                    if (!empty(DBi::$errors)) {
                        $keepGoing=false;
                        $errorMessage.="<P class='fontMedium margin20 centered'>Error al agregar perfiles de usuario</P>";
                        doclog("Error(ADMON) al agregar perfiles de usuario","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors]);
                    } else if ($newRes) {
                        doclog("DATOS DE PERFIL INSERTADOS","usuarios",["query"=>$query]);
                    }
                }
                if ($keepGoing) { // Actualizar TABLA usuarios_grupo
                    $usrPrfGpo = $_POST["uxg"]??[]; // obtener lista nueva de perfiles por grupo
                    $usrPrfGpoOld=$_POST["uxgOld"]??[]; // obtener lista vieja de perfiles por grupo
                    if (!isset($ugObj)) {
                        require_once "clases/Usuarios_Grupo.php"; $ugObj = new Usuarios_Grupo(); }
                    // extraer perfiles por grupo a borrar (existen en la lista vieja pero no en la nueva), se incluyen los que existen en ambas pero modificados
                    $delGroupList=array_diff_assoc($usrPrfGpoOld, $usrPrfGpo); // extraer perfiles por grupo a agregar (existen en la lista nueva pero no en la vieja), se incluyen los que existen en ambas pero modificados
                    $newGroupList=array_diff_assoc($usrPrfGpo, $usrPrfGpoOld); // extraer los perfiles del grupo a borrar.
                    $delGroupKeys=array_keys($delGroupList); // extraer los perfiles del grupo a agregar.
                    $addGroupKeys=array_keys($newGroupList); // identificar los perfiles que se encuentran en ambos grupos.
                    $sameGroupKeys=array_intersect($delGroupKeys,$addGroupKeys); // identificar los perfiles que solo se encuentran en el grupo a borrar
                    $realDelKeys=array_diff($delGroupKeys,$addGroupKeys); // identificar los perfiles que solo se encuentran en el grupo a agregar
                    $realAddKeys=array_diff($addGroupKeys,$delGroupKeys); // construir arreglo de datos a agregar, los datos a borrar se eliminan directamente
                    $addArray=[]; // los datos a agregar se incluyen en una lista para un solo insert multiple
                    // incluir datos de los perfiles que solo estan para borrar, con todos los grupos en el bloque
                    //doclog("REALKEYS",null,["realAddKeys"=>$realAddKeys,"newGroupList"=>$newGroupList,"realDelKeys"=>$realDelKeys,"delGroupList"=>$delGroupList,"usrLastId"=>$usrLastId]);
                    if (isset($realDelKeys[0])) foreach($realDelKeys as $key) {
                        $gpIds=explode(";",$delGroupList[$key]);
                        if ($ugObj->deleteRecord(["idUsuario"=>$usrLastId,"idPerfil"=>$key]))
                            doclog("DATOS DE PERFIL/GRUPO ELIMINADOS","usuarios",["query"=>$query]);
                        else if (!empty(DBi::$errors)) {
                            $keepGoing=false;
                            $errorMessage.="<P class='fontMedium margin20 centered'>Error al eliminar perfil/grupo de usuario</P>";
                            doclog("Error(ADMON) al remover perfil/grupo de usuario","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"realDelKeys"=>$realDelKeys,"delGroupList"=>$delGroupList,"usrLastId"=>$usrLastId]);
                        }
                    }
                    if ($keepGoing && isset($realAddKeys[0])) foreach($realAddKeys as $key) {
                        $gpIds=explode(";",$newGroupList[$key]);
                        $tipo="vista";
                        if ($key=="61") $tipo="auth";
                        else if ($key=="62") $tipo="";
                        foreach($gpIds as $gpId)
                            $addArray[]=[$usrLastId,$key,$gpId,$tipo]; //"'".implode("','",[$usrLastId,$key,$gpId,$tipo])."'";
                    }
                    //doclog("SAME GROUPS",null,["sameGroupKeys"=>$sameGroupKeys,"delGroupList"=>$delGroupList,"newGroupList"=>$newGroupList]);
                    if ($keepGoing) foreach($sameGroupKeys as $idx=>$ky) {
                        $delGps=explode(";",$delGroupList[$ky]);
                        $addGps=explode(";",$newGroupList[$ky]);
                        if (isset($delGps[0])||isset($addGps[0])) {
                            $tipo="vista";
                            if ($ky=="61") $tipo="auth";
                            else if ($ky=="62") $tipo="";
                            $delFix=array_diff($delGps,$addGps);
                            $addFix=array_diff($addGps,$delGps);
                            foreach($delFix as $gky) {
                                if($ugObj->deleteRecord(["idUsuario"=>$usrLastId,"idPerfil"=>$ky,"idGrupo"=>$gky,"tipo"=>$tipo])) {
                                    doclog("DATOS DE PERFIL/GRUPO ELIMINADOS","usuarios",["query"=>$query]);
                                } else if (!empty(DBi::$errors)) {
                                    $keepGoing=false;
                                    $errorMessage.="<P class='fontMedium margin20 centered'>Error al remover perfil/grupo de usuario</P>";
                                    doclog("Error(ADMON) al remover perfil/grupo de usuario","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"realAddKeys"=>$realAddKeys,"newGroupList"=>$newGroupList,"realDelKeys"=>$realDelKeys,"delGroupList"=>$delGroupList,"usrLastId"=>$usrLastId]);
                                }
                            }
                            foreach($addFix as $gky)
                                $addArray[]=[$usrLastId,$ky,$gky,$tipo]; // "'".implode("','",[$usrLastId,$ky,$gky,$tipo])."'";
                        }
                    }
                    if ($keepGoing && isset($addArray[0])) {
                        if ($ugObj->insertMultipleRecords(["idUsuario","idPerfil","idGrupo","tipo"], $addArray)) {
                            doclog("DATOS DE PERFIL/GRUPO GUARDADOS","usuarios",["query"=>$query]);
                        } else if (!empty(DBi::$errors)) {
                            $keepGoing=false;
                            $errorMessage.="<P class='fontMedium margin20 centered'>Error al agregar perfil/grupo de usuario</P>";
                            doclog("Error(ADMON) al agregar perfil/grupo de usuario","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"realAddKeys"=>$realAddKeys,"newGroupList"=>$newGroupList,"realDelKeys"=>$realDelKeys,"delGroupList"=>$delGroupList,"usrLastId"=>$usrLastId]);
                        }
                    }
                }
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al guardar usuario $nombreUsuario</P>";
                clog2(" ### ERROR SAVING USUARIOS LOG:\n".$usrObj->log);
                doclog("Error(ADMON) al guardar usuario","error",["post"=>$_POST,"query"=>$query,"errors"=>DBi::$errors,"error"=>DBi::$error,"errno"=>DBi::$errno]);
            }
            if (empty($errorMessage)) {
                DBi::commit();
            } else {
                DBi::rollback();
            }
            DBi::autocommit(TRUE);
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para modificar usuarios</P>";
    }
    unset($_POST["user_password"]);
} else if (isset($_POST["user_delete"])) {
    if ($modificaUsrs) {
        if (empty($idUsuario)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario seleccionar un usuario para poder borrarlo</P>";
        } else {
            DBi::autocommit(FALSE);
            if (!isset($upObj)) {
                require_once "clases/Usuarios_Perfiles.php";
                $upObj = new Usuarios_Perfiles();
            }
            if (!isset($ugObj)) {
                require_once "clases/Usuarios_Grupo.php";
                $ugObj = new Usuarios_Grupo();
            }
            if (!$upObj->deleteRecord (["idUsuario"=>$idUsuario])&&!empty(DBi::$errno)) {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al borrar perfiles del usuario $nombreUsuario</P>";
            } else if ($ugObj->exists("idUsuario=$idUsuario") && !$ugObj->deleteRecord (["idUsuario"=>$idUsuario])) {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al borrar grupos del usuario $nombreUsuario</P>";
            } else {
                require_once "clases/Usuarios.php";
                $usrObj = new Usuarios();
                if ($usrObj->deleteRecord(["id"=>$idUsuario]) && $usrObj->affectedrows) {
                    $resultMessage .= "<P class='fontMedium margin20 centered'>Usuario $nombreUsuario eliminado satisfactoriamente</P>";
                    $procDetalle = "Usuario";
                    if (!empty($nombreUsuario)) $procDetalle .= " ".$nombreUsuario;
                    if (!empty($personaUsuario)) $procDetalle .= " ".$personaUsuario;
                    $prcObj->cambioAdmin($idUsuario, "Baja", getUser()->nombre, $procDetalle);
                    clog2("SUCCESS DELETING USR LOG:\n".$usrObj->log);
                    $usrLastId = "";
                } else {
                    $errorMessage .= "<P class='fontMedium margin20 centered'>Error al borrar usuario $nombreUsuario</P>";
                }
            }
            if (empty($errorMessage)) {
                DBi::commit();
            } else {
                DBi::rollback();
            }
            DBi::autocommit(TRUE);
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para borrar usuarios</P>";
    }
    unset($_POST["user_password"]);
} else if (isset($_POST["banco_submit"])) {
    if ($modificaBancos) {
        $fldarr=[];
        $procDetalle="Banco ";
        $procColon=false;
        if (empty($idBanco)) {
            $procDetalle.="Nuevo ";
        } else {
            $bnkData=$bnkObj->getData("id=$idBanco",0,"id,clave,coalesce(alias,'') alias,razonSocial,coalesce(rfc,'') rfc,coalesce(cuenta,'') cuenta,status");
            $fldarr["id"] = $idBanco;
            if (isset($bnkData[0]["id"])) $bnkData=$bnkData[0];
            else $procDetalle.="Reingreso ";
        }
        $tieneData=isset($bnkData);
        $tieneAlias=isset($aliasBanco[0]);
        if ((!$tieneData && $tieneAlias) || ($tieneData && $bnkData["alias"]!==$aliasBanco)) {
            $fldarr["alias"] = $tieneAlias?$aliasBanco:null;
            if (isset($bnkData["alias"][0])) $procDetalle.="$bnkData[alias]=>";
        } else $procDetalle.="#";
        if ($tieneAlias) $procDetalle.=$aliasBanco;
        if (!isset($claveBanco[0])) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario indicar la clave del banco</P>";
        } else if (!$tieneData || $bnkData["clave"]!==$claveBanco) {
            $fldarr["clave"] = $claveBanco;
            $procDetalle.=($tieneAlias?" : ":"").(isset($bnkData["clave"][0])?"$bnkData[clave]=>":"").$claveBanco;
            $procColon=$tieneAlias;
        } else if (!$tieneAlias) $procDetalle.=$claveBanco;
        if (!isset($razonBanco[0])) {
            $errorMessage.="<P class='fontMedium margin20 centered'>Es necesario indicar la raz&oacute;n social del banco</P>";
        } else if (!$tieneData || $bnkData["razonSocial"]!==$razonBanco) {
            $fldarr["razonSocial"] = $razonBanco;
            $procDetalle.=($procColon?",":" :").(isset($bnkData["razonSocial"][0])?" CAMBIA":"")." $razonBanco";
            $procColon=true;
        }
        $tieneRfc=isset($rfcBanco[0]);
        if ((!$tieneData && $tieneRfc) || ($tieneData && $bnkData["rfc"]!==$rfcBanco)) {
            $fldarr["rfc"] = $tieneRfc?$rfcBanco:null;
            $procDetalle.=($procColon?", ":" : ").(isset($bnkData["rfc"][0])?"$bnkData[rfc]=>":"").$rfcBanco;
            $procColon=true;
        }
        if (!$tieneData || $bnkData["cuenta"]!==$cuentaBanco) {
            $fldarr["cuenta"] = isset($cuentaBanco[0])?$cuentaBanco:null;
            $procDetalle.=($procColon?", ":" : ")."CUENTA ".(isset($bnkData["cuenta"][0])?"$bnkData[cuenta]=>":"").(isset($cuentaBanco[0])?$cuentaBanco:"-");
            $procColon=true;
        }
        $statusBanco=($statusBanco==="1")?"activo":"inactivo";
        if (!$tieneData || $bnkData["status"]!==$statusBanco) {
            $procDetalle.=($procColon?", ":" : ").$statusBanco;
            $procColon=true;
            $fldarr["status"] = $statusBanco;
        }
        if (empty($errorMessage)) {
            if ($bnkObj->saveRecord($fldarr)||empty(DBi::$errno)) {
                //unset($_SESSION['bnkRazSocOpt']);
                //unset($_SESSION['bnkCodigoOpt']);
                //unset($_SESSION['bnkRFCOpt']);
                $bnkLastId = $bnkObj->lastId;
                $resultMessage .= "<P class='fontMedium margin20 centered'>Banco $razonBanco guardado satisfactoriamente</P>";
                if (empty($fldarr["id"])) {
                    $procIdentif=$bnkLastId;
                    $procStatus="Alta";
                } else {
                    $procIdentif=$fldarr["id"];
                    $procStatus="Cambio";
                }
                $prcObj->cambioAdmin($procIdentif, $procStatus, getUser()->nombre, $procDetalle);
                clog2("SUCCESS SAVING BNK LOG:\n".$bnkObj->log);
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al guardar banco $razonBanco</P>";
            }
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para modificar bancos</P>";
    }
} else if (isset($_POST["banco_delete"])) {
    if ($modificaBancos) {
        if (empty($idBanco)) {
            $errorMessage .= "<P class='fontMedium margin20 centered'>Es necesario seleccionar un banco para poder borrarlo</P>";
        } else {
            if ($bnkObj->deleteRecord(["id"=>$idBanco])/* && $bnkObj->affectedrows*/) {
                //unset($_SESSION['bnkRazSocOpt']);
                //unset($_SESSION['bnkCodigoOpt']);
                //unset($_SESSION['bnkRFCOpt']);
                $resultMessage .= "<P class='fontMedium margin20 centered'>Banco $razonBanco eliminada satisfactoriamente</P>";
                $procDetalle = "Banco";
                if (!empty($aliasBanco))
                    $procDetalle .= " ".$aliasBanco;
                if (!empty($razonBanco))
                    $procDetalle .= " ".$razonBanco;
                $prcObj->cambioAdmin($idBanco, "Baja", getUser()->nombre, $procDetalle);
                clog2("SUCCESS DELETING BNK LOG:\n".$bnkObj->log);
                $bnkLastId = "";
            } else {
                $errorMessage .= "<P class='fontMedium margin20 centered'>Error al borrar banco $razonBanco</P>";
            }
        }
    } else {
        $errorMessage .= "<P class='fontMedium margin20 centered'>No tiene permiso para borrar bancos</P>";
    }
}

if (!empty($errorMessage)) {
    if (isset($prvObj)) {
        clog2("LOGPRV: ".$prvObj->log);
        clog2("ERRLOG: ".arr2str($prvObj->errors));
    } else if (isset($gpoObj)) {
        clog2("LOGGPO: ".$gpoObj->log);
        clog2("ERRLOG: ".arr2str($gpoObj->errors));
    } else if (isset($usrObj)) {
        clog2("LOGUSR: ".$usrObj->log);
        clog2("ERRLOG: ".arr2str($usrObj->errors));
    }
}

clog2("POST: ".arr2str($_POST));
// ---------------------------- Variables de Layout ---------------------------- //
$prvIdVal = ""; $prvRzSocVal = ""; $prvCodVal = ""; $prvRfcVal = ""; $prvZonaVal = ""; $prvSttVal = ""; $prvDelBtnDisp = "none";
$gpoIdVal = ""; $gpoRzSocVal = ""; $gpoBrfVal = ""; $gpoRfcVal = ""; $gpoCutVal = ""; $gpoSttVal = ""; $gpoFlt1Val = ""; $gpoFlt2Val = ""; $gpoFlt4Val = ""; $gpoDelBtnDisp = "none";
$usrIdVal = ""; $usernameVal = ""; $userRNameVal = ""; $keyUpdCheck=""; $keySysCheck=""; $userEmailVal = ""; $userObsVal = ""; $usrDelBtnDisp = "none";
$bnkIdVal = ""; $bnkRzSocVal = ""; $bnkBrfVal = ""; $bnkKeyVal = ""; $bnkRfcVal = ""; $bnkSttVal = ""; $bnkCtaConta = ""; $bnkDelBtnDisp = "none";
$aliasListaComprasGrupo = "";

$defaultCheckMetodoPago = "";
require_once "clases/InfoLocal.php";
if (!isset($infObj)) $infObj = new InfoLocal();
$availableCheckMetodoPago = $infObj->available();
$retIL = $infObj->obtener("validaMetodoPago");
if (empty($retIL) || $retIL!="NO") $defaultCheckMetodoPago = " checked";

if (isset($errorMessage[0]) || isset($resultMessage[0])) {
    if (!empty($prvLastId)) {
        $prvIdVal = " value=\"$prvLastId\"";
        $prvDelBtnDisp = "inline";
    }
    if (!empty($razonProveedor))  $prvRzSocVal = " value=\"$razonProveedor\"";
    if (!empty($codigoProveedor)) $prvCodVal   = " value=\"$codigoProveedor\"";
    if (!empty($rfcProveedor))    $prvRfcVal   = " value=\"$rfcProveedor\"";
    if (!empty($zonaProveedor))    $prvZonaVal   = " value=\"$zonaProveedor\"";
    if ($esSistemas) {
        if (!empty($statusProveedor)) $prvSttVal = $statusProveedor;
    }
    if (!empty($gpoLastId)) {
        $gpoIdVal = " value=\"$gpoLastId\"";
        $gpoDelBtnDisp = "inline";
    }
    if (!empty($razonEmpresa)) $gpoRzSocVal = " value=\"$razonEmpresa\"";
    if (!empty($aliasEmpresa)) $gpoBrfVal = " value=\"$aliasEmpresa\"";
    if (!empty($rfcEmpresa)) $gpoRfcVal = " value=\"$rfcEmpresa\"";
    if (!empty($cutEmpresa)) $gpoCutVal = " value=\"$cutEmpresa\"";
    if (!empty($filtroEmpresa)) {
        if(in_array("1", $filtroEmpresa)) $gpoFlt1Val=" checked";
        if(in_array("2", $filtroEmpresa)) $gpoFlt2Val=" checked";
        if(in_array("4", $filtroEmpresa)) $gpoFlt4Val=" checked";
    }
    if (!empty($usrLastId)) {
        $usrIdVal = " value=\"$usrLastId\"";
        $usrDelBtnDisp = "inline";
        if (!isset($ugObj)) {
            require_once "clases/Usuarios_Grupo.php";
            $ugObj = new Usuarios_Grupo();
        }
        $ugObj->rows_per_page=0;
        $aliasEmpresas = $ugObj->getGroupAliases((object)["id"=>$usrLastId], "Compras", "vista");
        $aliasListaComprasGrupo=implode(",", $aliasEmpresas);
    } else if (isset($idUsuario[0])) {
        $usrIdVal = " value=\"$idUsuario\"";
        $usrDelBtnDisp = "inline";
    }
    if (!empty($nombreUsuario)) $usernameVal = " value=\"$nombreUsuario\"";
    if (!empty($personaUsuario)) $userRNameVal = " value=\"$personaUsuario\"";
    if (!empty($emailUsuario)) $userEmailVal = " value=\"$emailUsuario\"";
    if (!empty($kupd)) $keyUpdCheck=" checked";
    if (!empty($skey)) $keySysCheck=" checked";
    if (!empty($obsUsuario)) $userObsVal = " value=\"$obsUsuario\"";

    if (!empty($bnkLastId)) {
        $bnkIdVal=" value=\"$bnkLastId\"";
        $bnkDelBtnDisp="inline";
    }
    if (!empty($razonBanco)) $bnkRzSocVal = " value=\"$razonBanco\"";
    if (!empty($aliasBanco)) $bnkBrfVal = " value=\"$aliasBanco\"";
    if (!empty($claveBanco)) $bnkKeyVal = " value=\"$claveBanco\"";
    if (!empty($rfcBanco)) $bnkRfcVal = " value=\"$rfcBanco\"";
    if ("activo"===($statusBanco??"")) $bnkSttVal = " checked";
    if (!empty($cuentaBanco)) $bnkCtaConta = " value=\"$cuentaBanco\"";
}
$statusOptions=getHtmlOptions(["activo"=>"activo","actualizar"=>"actualizar","inactivo"=>"inactivo"], $prvSttVal);
if ($consultaPerm) {
    require_once "clases/Acciones.php";
    $actObj = new Acciones();
    $actObj->rows_per_page=0;
    $actObj->clearOrder();
    $actObj->addOrder("id");
    $actData=$actObj->getData();
    $actOpts=[""=>"-- NUEVA --"];
    $actOpts1=[];
    foreach($actData as $actIdx=>$actRow) {
        $actOpts[$actRow["id"]]=["value"=>$actRow["nombre"],"desc"=>$actRow["descripcion"]];
        $actOpts1[$actRow["id"]]=$actOpts[$actRow["id"]];
    }
    $actOptions=getHtmlOptions($actOpts,"");
    $actOptions1=getHtmlOptions($actOpts1,"");

    require_once "clases/Perfiles.php";
    $prfObj = new Perfiles();
    $prfObj->rows_per_page=0;
    $prfObj->clearOrder();
    $prfObj->addOrder("id");
    $prfData=$prfObj->getData();
    $prfOpts=[""=>"-- NUEVO --"];
    require_once "clases/Permisos.php";
    $prmObj = new Permisos();
    $prmObj->rows_per_page=0;
    $prmObj->clearOrder();
    $prmObj->addOrder("idAccion");
    foreach ($prfData as $prfIdx => $prfRow) {
        $prmReadData=$prmObj->getData("idPerfil=$prfRow[id] and consulta=1",0,"idAccion");
        $readTxt="";
        foreach ($prmReadData as $idx => $prmReadRow) {
            if (isset($readTxt[0])) $readTxt.=",";
            $readTxt.=$prmReadRow["idAccion"];
        }
        $prmWriteData=$prmObj->getData("idPerfil=$prfRow[id] and modificacion=1",0,"idAccion");
        $writeTxt="";
        foreach ($prmWriteData as $idx => $prmWriteRow) {
            if (isset($writeTxt[0])) $writeTxt.=",";
            $writeTxt.=$prmWriteRow["idAccion"];
        }
        $prfOpts[$prfRow["id"]]=["value"=>$prfRow["nombre"],"desc"=>$prfRow["detalle"],"stat"=>$prfRow["estado"]];
        if (isset($readTxt[0])) $prfOpts[$prfRow["id"]]["read"]=$readTxt;
        if (isset($writeTxt[0])) $prfOpts[$prfRow["id"]]["write"]=$writeTxt;
    }
    $prfOptions=getHtmlOptions($prfOpts,"");
}
if ($esSistemas) {
    if (isset($_POST["cuenta_submit"])) {
        $_SESSION["accounts"]=null;
    }
    if (isset($_POST["cuenta_delete"])) {
        $_SESSION["accounts"]=null;
    }
    require_once "clases/Cuentas.php"; $accObj = new Cuentas(); $accObj->rows_per_page=0;
    $accObj->clearOrder(); $accObj->addOrder("nombre"); $accData=$accObj->getData(false,0,"id,nombre,tipo,cuenta");
    $accOpts=[""=>"-- NUEVA --"];
    if (!isset($_SESSION["accounts"])) $_SESSION["accounts"]=$accData;
    foreach ($accData as $accIdx => $accData) {
        $accOpts[$accData["id"]]=["value"=>$accData["nombre"],"tipo"=>$accData["tipo"],"cuenta"=>$accData["cuenta"]];
    }
    $accOptions=getHtmlOptions($accOpts,"");

    require_once "clases/ProveedorTipos.php"; $prtObj = new ProveedorTipos(); $prtObj->rows_per_page=0;
    $prtObj->clearOrder(); $prtObj->addOrder("nombre"); $prtData=$prtObj->getData();
    $tprvOpts=[""=>"-- NUEVO --"];
    require_once "clases/ProveedorTipoCuentas.php"; $ptcObj = new ProveedorTipoCuentas(); $ptcObj->rows_per_page=0;
    foreach ($prtData as $prtIdx => $prtRow) {
        $ptcData=$ptcObj->getData("idProveedor=$prtRow[id]");
        $ctaTxt="";
        foreach ($ptcData as $idx => $ptcRow) {
            if (isset($ctaTxt[0])) $ctaTxt.=",";
            $ctaTxt.="$ptcRow[idCuenta]";
        }
        $tprvOpts[$prtRow["id"]]=["value"=>$prtRow["nombre"]];
        if (isset($ctaTxt[0])) $tprvOpts[$prtRow["id"]]["cuentas"]=$ctaTxt;
    }
    $tprvOptions=getHtmlOptions($tprvOpts,"");
}

clog1seq(-1);
clog2end("configuracion.admon");
