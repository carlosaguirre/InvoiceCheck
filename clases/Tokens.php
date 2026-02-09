<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Tokens extends DBObject {
    const USAGE_REPLACE=0; // el usuario del token siempre reemplaza cualquier usuario existente
    const USAGE_EXISTS=1; // si existe un usuario previo, si es diferente al del token manda error
    const USAGE_TEMPORAL=2; // si existe usuario previo se guarda, se asigna el nuevo usuario, al final del proceso se regresa al usuario guardado. No será posible navegar en el portal con este modo
    const USAGE_VALIDATE=3; // si existe un usuario previo, se valida: debe ser el mismo del token o sistemas
    private static $userUsage=self::USAGE_VALIDATE;
    private $oldSessionId=null;
    private $oldSessionName=null;
    public $data=null;
    public $errorMessage=null;
    function __construct() {
        $this->tablename      = "tokens";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "token", "refId", "usrId", "modulo", "status", "usos", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['token'] = array('skey' => 'y');
        $this->fieldlist['refId'] = array('skey' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Tokens xxxxxxxxxxxxxx //\n";
    }
    public static function nuevo($length) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i=0; $i<$length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, $max)];
        }
        return $token;
    }
    public static function getGrupoModulo($modulo) {
        if ($modulo==="autorizaPago"||$modulo==="rechazaPago") return ["autorizaPago","rechazaPago"];
        if (in_array($modulo, ["transfiereArchivos","procesaCompras","procesaConta","anexaComprobante","procesaPago"])) return [$modulo];
        if ($modulo==="PruebaOn"||$modulo==="PruebaOff") return ["PruebaOn","PruebaOff"];
        if (isset($modulo[0])) return $modulo;
        return null;
    }
    function creaAccionSistemas($refId,$usrId,$modulo,$usos=1) {
        if (empty($refId) || empty($usrId) || empty($modulo)) {
            if (!isset($this->errors)) $this->errors=[];
            $this->errors[]="Tokens.creaAccionSistemas: Parametros vacíos.";
            return false;
        }
        if ($this->exists("refId=$refId and usrId=$usrId and modulo='$modulo'")) {
            $this->errors[]="Tokens.creaAccionSistemas: Ya existe token para Solicitud $refId, modulo '$modulo' y usrId $usrId";
            return false;
        }
        $token=self::nuevo(43);
        $arrFld=["token"=>$token,"refId"=>$refId,"usrId"=>$usrId,"modulo"=>$modulo,"status"=>"activo","usos"=>$usos];
        if ($this->saveRecord($arrFld))
            return $token;
        return false;
    }
    function creaAccion($refId,$usrIds,$modulos,$usos=1,$esNuevo=false) {
        if (empty($refId) || empty($usrIds) || empty($modulos)) {
            if (!isset($this->errors)) $this->errors=[];
            $this->errors[]="Tokens.creaAccion: Parametros vacíos.";
            return false;
        }
        if (!is_array($usrIds)) $usrIds=[$usrIds];
        $usrList="'".implode("','", $usrIds)."'";
        if (!is_array($modulos)) $modulos=[$modulos];
        $modList="'".implode("','", $modulos)."'";
        if (!$esNuevo && $this->exists("refId=$refId and modulo in (".$modList.")")) {
            $this->errors[]="Tokens.creaAccion: El id de referencia $refId, modulo ({$modList}) ya tiene tokens";
            return false;
        } else if ($esNuevo && $this->exists("refId=$refId and modulo in (".$modList.") and usrId in (".$usrList.")")) {
            $this->errors[]="Tokens.creaAccion: El id de referencia $refId, modulo ({$modList}), usuario ({$usrList}) ya tiene tokens";
            return false;
        }
        $arr=[];
        $ret=[];
        foreach ($usrIds as $idx => $usrId) {
            $ret[$usrId]=[];
            foreach ($modulos as $modulo) {
                $token=self::nuevo(43);
                $arr[]=[$token,$refId,$usrId,$modulo,"activo",$usos];
                $ret[$usrId][$modulo]=$token;
            }
        }
        if ($this->insertMultipleRecords(["token","refId","usrId","modulo","status","usos"], $arr))
            return $ret;
        // Agregar errores de BD
        return false;
    }
    public static function eligeUsuario($refId,$modulo) {
        global $tokObj;
        if (!isset($tokObj))
            $tokObj=new Tokens();
        sessionInit();
        if (!hasUser()) return false;
        $usrId=getUser()->id;
        // encuentra tokens con refId y modulo indicados y con usos en null o mayor a cero
        // a todos los tokens encontrados con usrId no activo cambiar status a cancelado y si usos no es null restarle 1
        // si uno de esos tokens tiene el usrId activo, cambiar status a ocupado y si usos no es null restarle 1
        return $tokObj->updateRecord(["status"=>new DBExpression("if(usrId=$usrId,'ocupado','cancelado')"),"usos"=>new DBExpression("if(isnull(usos),null,usos-1)"),"refId"=>$refId,"modulo"=>$modulo],["refId","modulo","usos"=>new DBExpression("(usos is null or usos>0)","REPLACE")]);
    }
    function eligeToken($token,$status="ocupado",$repeatable=false,$keepOldUser=false) {
        doclog("eligeToken INI","token",["token"=>$token,"status"=>$status]);
        $hasResult=false;
        $this->errorMessage=null;
        $hasAutoCommit=DBi::isAutocommit();
        doclog("eligeToken AutoCommit","token",["hasAutoCommit"=>$hasAutoCommit?"TRUE":"FALSE"]);
        if ($hasAutoCommit) DBi::autocommit(false);
        try {
            $this->validaUsuario($token,$repeatable,$keepOldUser);
            if ($this->data["status"]===$status && !$repeatable)
                throw new Exception("SameStatus");
            if ($status==="ocupado" && !$repeatable && isset($this->data["ocupado"]) && $this->data["modulo"]===$this->data["ocupado"]["modulo"])
                throw new Exception("SameModule");
            if (isset($this->data["usos"])) {
                $usos=+$this->data["usos"]-1;
                doclog("eligeToken 1","token",["usos"=>$usos]);
            }

            $listaModulo=Tokens::getGrupoModulo($this->data["modulo"]??null);
            doclog("eligeToken 2","token",["listaModulo"=>$listaModulo]);
            if (!isset($listaModulo)) $modulo=" and modulo is null";
            else if (is_array($listaModulo)) $modulo=" and modulo in ('".implode("','", $listaModulo)."')";
            else if (is_scalar($listaModulo)) $modulo=" and modulo='$modulo'";
            else throw new Exception("BadModule");

            $refData=$this->getData("refId={$this->data["refId"]}{$modulo}");
            $columns=[];
            doclog("eligeToken 3","token",["refCount"=>count($refData)]);
            if (DBi::isAutocommit()) {
                doclog("eligeToken ALERT ISAUTOCOMMIT","token");
            }
            foreach ($refData as $idx => $tokData) {
                $fieldarray=["id"=>$tokData["id"]];
                if (isset($usos)) $fieldarray["usos"]=$usos;
                $isThisToken=($tokData["token"]===$token);
                if ($isThisToken) $fieldarray["status"]=$status;
                else $fieldarray["status"]="cancelado";
                if (DBi::isAutocommit()) {
                    doclog("eligeToken ALERT ISAUTOCOMMIT","token",["tokenIdx"=>$idx,"tokenId"=>$tokData["id"]]);
                }
                if (!$this->updateRecord($fieldarray)) {
                    if (!empty(DBi::$errno)||!empty(DBi::$error))
                        throw new Exception("Error");
                }
                if ($isThisToken) {
                    $hasResult=true; // ["refId"=>$tokData["refId"],"modulo"=>$tokData["modulo"],"usrId"=>$tokData["usrId"],"status"=>$status,"usos"=>$usos];
                    global $prcObj;
                    if (!isset($prcObj)) {
                        require_once "clases/Proceso.php";
                        $prcObj = new Proceso();
                    }
                    $detalle="ref$tokData[refId]|$tokData[modulo]|usr$tokData[usrId]";
                    if ($tokData["usrId"]!==getUser()->id) {
                        $detalle.="=>".getUser()->id;
                    }
                    if ($status!=="ocupado") $detalle.="|$status";
                    if (isset($usos)) $detalle.="|".$usos;
                    $prcObj->cambiaToken($tokData["id"], $detalle);
                }
            }
        } catch (Exception $ex) {
            doclog("eligeToken Exception","token",["exception"=>$ex->getMessage()]);
            switch($ex->getMessage()) {
                case "NoData": $msg="Usuario no identificado"; break;
                case "BadType": $msg="Identificador invalido"; break;
                case "Empty": $msg="Identificador vacio"; break;
                case "BadData": $msg="Correlacion no encontrada";
                    $this->errorMessage="El correo es obsoleto, la acción ya no es válida";
                    break;
                case "Invalid": $msg="Accion invalida"; break;
                case "NoUser": $msg="Usuario desconocido"; break;
                case "BadUser":
                    $msg="Usuario incorrecto";
                    $this->errorMessage="El usuario ".(getUser()->nombre)." no es válido para esta acción";
                    break;
                case "CorruptUser": $msg="Usuario invalido"; break;
                case "BadModule": $msg="Modulo invalido"; break;
                case "SameStatus":
                case "SameModule":
                    $modulo=$this->data["modulo"];
                    if ($modulo==="autorizaPago")
                        $msg="autorizada";
                    else if ($modulo==="rechazaPago")
                        $msg="rechazada";
                    else if ($modulo==="transfiereArchivos")
                        $msg="exportada";
                    else if ($modulo==="procesaCompras"||$modulo==="procesaConta")
                        $msg="procesada";
                    else if ($modulo==="anexaComprobante")
                        $msg="comprobada";
                    else if ($modulo==="procesaPago")
                        $msg="pagada";
                    else $msg="$modulo elegida";
                    $this->errorMessage="La solicitud fue $msg previamente";
                    break;
                case "Error": $msg="Error al guardar"; break;
                default: $msg=$ex->getMessage(); 
            }
            $this->errors[]=$msg;
            $hasResult=false;
        }
        if ($hasAutoCommit) {
            if ($hasResult) {
                doclog("eligeToken AutoCommit","token",["action"=>"commit"]);
                DBi::commit();
            } else {
                doclog("eligeToken AutoCommit","token",["action"=>"rollback"]);
                DBi::rollback();
            }
            DBi::autocommit(true);
        }
        doclog("eligeToken END","token",["result"=>$hasResult?"TRUE":"FALSE"]);
        return $hasResult;
    }
    function validaToken($token,$repeatable=false) {
        doclog("validaToken INI","token",["token"=>$token]);
        $this->data=null;
        if (!isset($token)) throw new Exception("NoData");
        if (!is_string($token)) throw new Exception("BadType");
        if (!isset($token[0])) throw new Exception("Empty");
        $this->data=$this->getData("token='$token'");
        if (!isset($this->data["id"])) {
            if (!isset($this->data[0]["id"])) throw new Exception("BadData");
            $this->data=$this->data[0];
        }
        // ToDo_SOLICITUD: validar status="obsoleto": Es cuando un token deja de servir. Actualmente ocurre cuando una solicitud autorizada, posteriormente es rechazada. Los tokens en los correos enviados previamente dejan de servir. Originalmente se borran los tokens pero se contempla solo cambiar status a 'obsoleto' para reconocerlos y mandar mensaje más apropiado. De preferencia permitiendo validar usuario.
        if ($this->data["status"]==="ocupado" && !$repeatable)
            throw new Exception("SameModule");
        if ($this->data["status"]!=="activo" && $this->data["status"]!=="ocupado") {
            $whr="refId='".$this->data["refId"]."'";
            $listaModulo=Tokens::getGrupoModulo($this->data["modulo"]??null);
            if (!isset($listaModulo)) $whr.=" and modulo is null";
            else if (is_array($listaModulo)) $whr.=" and modulo in ('".implode("','", $listaModulo)."')";
            else if (is_scalar($listaModulo)) $whr.=" and modulo='$modulo'";
            $whr.=" and status='ocupado'";
            $taken=$this->getData($whr);
            if (isset($taken[0]["id"])) $taken=$taken[0];
            if (isset($taken["id"][0])) {
                $this->data["ocupado"]=["id"=>$taken["id"],"token"=>$taken["token"],"usrId"=>$taken["usrId"],"modulo"=>$taken["modulo"]];
            }
            if (isset($this->data["usos"]) && (+$this->data["usos"])<=0) {
                throw new Exception("Invalid");
            }
        }
        doclog("validaToken END","token",$this->data);
    }
    function obtenStatusData($token=null,$refId=null,$modulos=null) {
        doclog("obtenStatusData INI","token",["token"=>$token,"refId"=>$refId,"modulos"=>$modulos]);
        if (isset($token[0])) {
            $this->data=$this->getData("token='$token'","refId,modulo");
            if (isset($this->data[0])) $this->data=$this->data[0];
            if (isset($this->data["refId"])) $refId=$this->data["refId"];
            if (isset($this->data["modulo"])) {
                $modulos=Tokens::getGrupoModulo($this->data["modulo"]);
            }
        }
        $whr="";
        if (isset($refId))
            $whr="refId='".$refId."'";
        if (!isset($modulos)) $whr.=" and modulo is null";
        else if (is_array($modulos)) $whr.=" and modulo in ('".implode("','", $modulos)."')";
        else if (is_scalar($modulos)) $whr.=" and modulo='$modulos'";
        doclog("obtenStatusData END","token",$this->data);
        return $this->getData($whr,0,"id,status,usos");
    }
    function restauraStatusData($statusData) {
        doclog("restauraStatusData INI","token",["statusData"=>$statusData]);
        if (isset($statusData)) {
            foreach ($statusData as $idx => $fldarr) {
                if (isset($fldarr["id"]))
                    $this->updateRecord($fldarr);
            }
        }
        doclog("restauraStatusData END","token");
    }
    function validaUsuario($token,$repeatable=false,$keepOldUser=false) {
        doclog("validaUsuario INI","token",["token"=>$token]);
        $tokenException=null;
        try {
            $this->validaToken($token,$repeatable);
        } catch (Exception $ex) {
            switch($ex->getMessage()) {
               case "SameModule": case "Invalid": break;
               default: throw $ex;
            }
            $tokenException=$ex;
        }
        if (!isset($this->data["usrId"][0])) throw new Exception("NoUser");
        global $usrObj;
        if (!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
        }
        doclog("validaUsuario 1","token",["data"=>$this->data]);
        $usrData=$usrObj->getData("id=".$this->data["usrId"]);
        if (isset($usrData[0]) && !isset($usrData["nombre"])) $usrData=$usrData[0];
        doclog("validaUsuario 2","token",["data"=>$usrData]);
        if (Tokens::$userUsage===self::USAGE_VALIDATE) {
            sessionInit();
            if (hasUser()) {
                doclog("validaUsuario 3","token",["user"=>getUser()]);
                if (!isset(getUser()->id)) {
                    $_SESSION['user']=null;
                    unset($_SESSION['user']);
                    sessionEnds();
                    throw new Exception("CorruptUser");
                }
                if (getUser()->id!==$this->data["usrId"] && !validaPerfil(["Administrador","Sistemas"]) && !$keepOldUser) {
                    throw new Exception("BadUser");
                }
                doclog("validaUsuario hasUser","token",["username"=>getUser()->nombre]);
            } else {
                doclog("validaUsuario 4","token",["usrData"=>$usrData]);
                $usr = (object) $usrData;
                unset($usr->seguro);
                unset($usr->password);
                global $_project_name;
                $usr->project_name = $_project_name;
                if (!isset($usr->cambiaClave)) $usr->cambiaClave=false;
                if (!isset($upObj)) {
                    require_once "clases/Usuarios_Perfiles.php";
                    $upObj = new Usuarios_Perfiles();
                }
                $listaPerfilIds = $upObj->getList("idUsuario",$usr->id,"idPerfil");
                if (!empty($listaPerfilIds)) $arrayPerfilIds = explode("|",$listaPerfilIds);
                if (!empty($arrayPerfilIds)) {
                    if (!isset($perObj)) {
                        require_once "clases/Perfiles.php";
                        $perObj = new Perfiles();
                    }
                    $listaPerfiles = $perObj->getList("id",$arrayPerfilIds,"nombre");
                    if (!empty($listaPerfiles)) $usr->perfiles = explode("|",$listaPerfiles);
                }
                doclog("validaUsuario 5","token",["usr"=>$usr]);
                $_SESSION['user'] = $usr;
                $_SESSION['tmp'] = "tokenized";
                global $prcObj;
                if (!isset($prcObj)) {
                    require_once "clases/Proceso.php";
                    $prcObj = new Proceso();
                }
                $prcObj->cambioSesion($usr->id, "Inicio", $usr->nombre, "Token: ".$usr->persona);
                doclog("validaUsuario newUser","token",["userid"=>$usr->id]);
            }
        } else {
            $this->oldSessionId = session_id();
            if (empty($this->oldSessionId)) {
                session_name($token);
                session_start();
                setcookie("TokenName",$token,0,"/invoice");
            } else if (!hasUser()) {
                sessionEnds();
                clearTokenName();
                $_SESSION=[];
                session_name($token);
                session_start();
                setcookie("TokenName",$token,0,"/invoice");
            } else if (getUser()->id!==$this->data["usrId"]) {
                if (Tokens::$userUsage===self::USAGE_EXISTS) throw new Exception("BadUser");
                session_write_close();
                if (Tokens::$userUsage!==self::USAGE_TEMPORAL) {
                    sessionEnds();
                    clearTokenName();
                    $_SESSION=[];
                }
                session_name($token);
                if(!isset($_COOKIE[$token])) {
                    $_COOKIE[$token] = session_create_id();
                }
                session_id($_COOKIE[$name]);
                session_start();
                setcookie("TokenName",$token,0,"/invoice");
            }
            $usr = (object) $usrData;
            unset($usr->seguro);
            unset($usr->password);
            global $_project_name;
            $usr->project_name = $_project_name;
            if (!isset($usr->cambiaClave)) $usr->cambiaClave=false;
            if (!isset($upObj)) {
                require_once "clases/Usuarios_Perfiles.php";
                $upObj = new Usuarios_Perfiles();
            }
            $listaPerfilIds = $upObj->getList("idUsuario",$usr->id,"idPerfil");
            if (!empty($listaPerfilIds)) $arrayPerfilIds = explode("|",$listaPerfilIds);
            if (!empty($arrayPerfilIds)) {
                if (!isset($perObj)) {
                    require_once "clases/Perfiles.php";
                    $perObj = new Perfiles();
                }
                $listaPerfiles = $perObj->getList("id",$arrayPerfilIds,"nombre");
                if (!empty($listaPerfiles)) $usr->perfiles = explode("|",$listaPerfiles);
            }
            $_SESSION['user'] = $usr;
            $_SESSION['tmp'] = "tokenized";
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj = new Proceso();
            }
            $prcObj->cambioSesion($usr->id, "Inicio", $usr->nombre, "Token: ".$usr->persona);
        }
        if (isset($tokenException)) throw $tokenException;
    }
    function restauraSesion() {
        if (Tokens::$userUsage===self::USAGE_TEMPORAL) {
            session_write_close();
            if (isset($this->oldSessionId)) {
                clearTokenName();
                session_name(getSessionName());
                session_id($this->oldSessionId);
                session_start();
            } else {
                sessionEnds();
                clearTokenName();
                $_SESSION=[];
            }
        }
    }
}
// TODO: generaToken
// TODO: Asigna modulo: PROVEEDOR
// TODO: Asigna datos: [id:idProveedor,doc:opinion/cuenta]
// TODO: procesaToken(valor) : solo si status=activo
