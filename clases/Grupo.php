<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Grupo extends DBObject {
    private static $invalidRefundGroup=["JLA","DANIEL","GYL","FIDEICOMIS","DEMO","FIDEMIFEL","ESMERALDA"];
    function __construct() {
        $this->tablename      = "grupo";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "razonSocial", "alias", "cut", "rfc", "cuentaCargo", "conSitFis", "conSitFisTimes", "status", "filtro", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['rfc'] = array('skey' => 'y');
        $this->fieldlist['alias'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Grupo xxxxxxxxxxxxxx //\n";
        //if (!isset($_SESSION['optDefaultValue'][0]))
        $_SESSION['optDefaultValue']="codigo"; // codigo, rfc, razon
    }
    function getInvalidRefundGroupId($forceRead=false) {
        static $invalidRefundGroupId=[];
        if (!isset($invalidRefundGroupId[0])||$forceRead) {
            $invalidRefundGroupId=array_column($this->getData("alias in ('".implode("','", self::$invalidRefundGroup)."')",0,"id"),"id");
        }
        return $invalidRefundGroupId;
    }
    function getValidRefundGroupId($forceRead=false, $forceReadInvalid=false) {
        static $validRefundGroupId=[];
        if (!isset($validRefundGroupId[0])||$forceRead) {
            $validRefundGroupId=array_column($this->getData("id not in (".implode(",",$this->getInvalidRefundGroupId($forceReadInvalid)).")",0,"id"),"id");
        }
        return $validRefundGroupId;
    }
    function createAlias($name) {
        $alias = trim ( preg_replace(
            "/[^a-z0-9']+([a-z0-9']{1,2}[^a-z0-9']+)*/i",
            " ",
            " $name "
        ) );
        $alias = trim(str_replace(["laminados ", "productos ", "envases ", "papeles ", "acabado ", "productora ", "distribuidora "], "", strtolower($alias)));
        $idx = strpos($alias, " ");
        if ($idx>0) $alias = substr($alias, 0, $idx);
        $alias = strtolower($alias);
        if ($this->exists("alias='$alias'"))
            return "";
        return $alias;
    }
    function getAliasByRFC($rfc, $forceRead=false) {
        static $aliasByRFCCache = [];
        if (!isset($aliasByRFCCache[$rfc]) || $forceRead) {
            $aliasByRFCCache[$rfc] = $this->getValue("rfc", $rfc, "alias");
        }
        return $aliasByRFCCache[$rfc];
    }
    function getRFCByAlias($alias, $forceRead=false) {
        static $rfcByAliasCache = [];
        if (!isset($rfcByAliasCache[$alias]) || $forceRead) {
            $rfcByAliasCache[$alias] = $this->getValue("alias", $alias, "rfc");
        }
        return $rfcByAliasCache[$alias];
    }
    function getIdByRFC($rfc, $forceRead=false) {
        static $idByRFCCache = [];
        if (!isset($idByRFCCache[$rfc]) || $forceRead) {
            $idByRFCCache[$rfc] = $this->getValue("rfc", $rfc, "id");
        }
        return $idByRFCCache[$rfc];
    }
    function getIdByAlias($alias, $forceRead=false) {
        static $idByAliasCache = [];
        if (!isset($idByAliasCache[$alias]) || $forceRead) {
            $idByAliasCache[$alias] = $this->getValue("alias", $alias, "id");
        }
        return $idByAliasCache[$alias];
    }
    function getAliasById($idList) {
        if (is_array($idList)) {
            $gData = $this->getData("id in (".implode(",", $idList).")",0,"alias");
            return array_column($gData, "alias");
        } else {
            $gData = $this->getData("id=$idList",0,"alias");
            if (isset($gData[0]["alias"])) return $gData[0]["alias"];
        }
        return false;
    }
    function getDomainKey($id) {
        $gData = $this->getData("id=$id",0,"alias");
        return $this->getDomainKeyByAlias($gData[0]["alias"]??"");
    }
    function getDomainKeyByAlias($alias) {
        if (isset($alias)) {
            $alias=strtolower($alias);
            if (in_array($alias, ["apsa", "bidasoa", "corepack", "foamymex", "glama", "jyl", "laisa", "melo", "morysan", "rga", "skarton"])) return $alias;
        }
        // APEL, BIDARENA, CAPITALH, CASABLANCA, DANIEL, DEMO, DESA, ENVASES, ESMERALDA, FIDEICOMIS, FIDEMIFEL, GYL, JLA, MARLOT, SERVICIOS
        return "default";
    }
    function getRFCById($idList) {
        if (is_array($idList)) {
            $gData = $this->getData("id in (".implode(",", $idList).")",0,"rfc");
            return array_column($gData, "rfc");
        } else {
            $gData = $this->getData("id=$idList",0,"rfc");
            if (isset($gData[0]["rfc"])) return $gData[0]["rfc"];
        }
        return false;
    }
    function setCodigoOptSession($prfs=["Compras"],$force=false) {
        return $this->setOptSessionsG($prfs,["gpoCodigoOpt"=>["rfc","alias"]],$force);
    }
    function setOptSessions($prfs=["Compras"],$force=false) {
        return $this->setOptSessionsG($prfs,["gpoCodigoOpt"=>["rfc","alias"],"gpoRazSocOpt"=>["rfc","razonSocial"],"gpoRFCOpt"=>["rfc","rfc"]],$force);
    }
    function setIdOptSessions($prfs=["Compras"],$value="codigo",$force=false) {
        if (!hasSession()) return false;
        $gpoFullMapWhere=false;
        if ($value!=="razon" && $value!=="rfc") $value="codigo";
        $sortBy=($value==="razon"?"razonSocial":($value==="rfc"?"rfc":"alias"));
        $gpoOptPrfs=$_SESSION["gpoOptPrfs"]??["Compras"];
        if ($gpoOptPrfs!=$prfs) $force=true;
        $_SESSION["gpoOptPrfs"]=$prfs;
        $gpoOptDefVal=$_SESSION["gpoOptDefVal"]??"codigo";
        if ($gpoOptDefVal!==$value) $force=true;
        $_SESSION["gpoOptDefVal"]=$value;
        if ($force||empty($_SESSION["gpoIdOpt"])) {
            if(!validaPerfil(["Administrador","Sistemas"])) {
                global $ugObj,$user; if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj = new Usuarios_Grupo(); }
                $gpoFullMapWhere=$ugObj->getFullMapWhere($user,$prfs);
            }
            $statusWhere=(isset($gpoFullMapWhere[0])?" AND ":"")."status='activo'";
            $oldRPP=$this->rows_per_page;
            $this->rows_per_page=0;
            $this->clearOrder();
            $this->addOrder($sortBy);
            $gpoData=$this->getData($gpoFullMapWhere.$statusWhere,0,"id,rfc,razonSocial,alias");
            $gpoIdOpt=[]; $gpoRazSoc2Id=[]; $gpoCodigo2Id=[]; $gpoRFC2Id=[];
            foreach ($gpoData as $idx => $gpo) {
                $gpoId=$gpo["id"]; $gpoRfc=$gpo["rfc"]; $gpoRzS=$gpo["razonSocial"]; $gpoAli=$gpo["alias"];
                $pos=strpos($gpoRzS,"&");
                if ($pos!==false && substr($gpoRzS,$pos,5)!=="&amp;") $gpoRzS=str_replace("&", "&amp;", $gpoRzS);
                $gpoIdOpt[$gpoId]=["rfc"=>$gpoRfc,"razon"=>$gpoRzS,"codigo"=>$gpoAli];
                if (isset($gpoIdOpt[$gpoId][$value]))
                    $gpoIdOpt[$gpoId]["value"]=$gpoIdOpt[$gpoId][$value];
                $gpoRazSoc2Id[$gpoRzS]=$gpoId;
                $gpoCodigo2Id[$gpoAli]=$gpoId;
                $gpoRFC2Id[$gpoRfc]=$gpoId;
            }
            $_SESSION['gpoIdOpt']=$gpoIdOpt;
            $_SESSION['gpoRazSoc2Id']=$gpoRazSoc2Id;
            $_SESSION['gpoCodigo2Id']=$gpoCodigo2Id;
            $_SESSION['gpoRFC2Id']=$gpoRFC2Id;
            $this->rows_per_page=$oldRPP;
        }
        return $gpoFullMapWhere;
    }
    function setOptSessionsG($prfs,$sessionData,$force=false) {
        if (!isset($sessionData)) return false;
        $sessionKeys=array_keys($sessionData);
        $missingKey=false;
        if (!hasSession()) return false;
        foreach ($sessionKeys as $key) { if (empty($_SESSION[$key])) { $missingKey=true; break; } }
        $gpoOptPrfs=$_SESSION["gpoOptPrfs"]??["Compras"];
        if ($gpoOptPrfs!=$prfs) $force=true;
        $_SESSION["gpoOptPrfs"]=$prfs;
        $pf=(is_array($prfs)&&isset($prfs[0]))?implode("",$prfs):(is_scalar($prfs)?"$prfs":"");
        $isSys=validaPerfil(["Administrador","Sistemas"]);
        $isPrv=validaPerfil("Proveedor");
        $isRaw=$isSys||$isPrv;
        if (!$missingKey && !$force) {
            if ($isRaw) {
                doclog("SetOptSessionsG","session",["prfs"=>$prfs,"sessionData"=>$sessionData,"force"=>($force?"TRUE":"FALSE"),"log"=>"ignore:isRaw"]);
                return false;
            }
            if (isset($_SESSION["sessOptPrf"])&&$_SESSION["sessOptPrf"]===$pf) {
                doclog("SetOptSessionsG","session",["prfs"=>$prfs,"sessionData"=>$sessionData,"force"=>($force?"TRUE":"FALSE"),"log"=>"ignore:samePrf:$pf"]);
                return false;
            }
        }
        if ((!isset($_SESSION["sessOptPrf"]) || $_SESSION["sessOptPrf"]!==$pf) && !$isRaw) $force=true;
        $_SESSION["sessOptPrf"]=$pf;
        $gpoFullMapWhere=false;
        if (!$isSys) { if ($isPrv) {
                global $invObj,$username; if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj = new Facturas(); }
                $gpoFullMapWhere=$invObj->getFullMapWhere($username);
                doclog("SetOptSessionsG","session",["prfs"=>$prfs,"sessionData"=>$sessionData,"force"=>($force?"TRUE":"FALSE"),"where"=>$gpoFullMapWhere,"perfiles"=>"Proveedor"]);
            } else { //if (validaPerfil("Compras")) {
                global $ugObj,$query,$user; if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj = new Usuarios_Grupo(); }
                $gpoFullMapWhere=$ugObj->getFullMapWhere($user,$prfs);
                doclog("SetOptSessionsG","session",["prfs"=>$prfs,"sessionData"=>$sessionData,"force"=>($force?"TRUE":"FALSE"),"where"=>$gpoFullMapWhere,"perfiles"=>$user->perfiles,"query"=>$query]);
        }   }
        $oldRPP=$this->rows_per_page;
        $this->rows_per_page=0;
        foreach ($sessionKeys as $key) {
            if ($force||empty($_SESSION[$key])) {
                $this->clearFullMap();
                $this->clearOrder();
                $mapKey=$sessionData[$key][0];
                $mapVal=$sessionData[$key][1];
                $this->addOrder($mapVal);
                $_SESSION[$key]=$this->getFullMap($mapKey,$mapVal,$gpoFullMapWhere);
        }   }
        $this->rows_per_page=$oldRPP;
        return $gpoFullMapWhere;
    }
}
