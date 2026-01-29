<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Usuarios_Grupo extends DBObject {
    function __construct() {
        $this->tablename      = "usuarios_grupo";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "idUsuario", "idPerfil", "idGrupo", "tipo");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['idUsuario'] = array('skey' => 'y');
        $this->fieldlist['idGrupo'] = array('skey' => 'y');
        $this->fieldlist['idPerfil'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Usuarios_Grupo xxxxxxxxxxxxxx //\n";
    }
    function isRelated($idUsuario, $idGrupo, $idPerfil, $tipo=null) {
        if (is_null($idUsuario) || (is_string($idUsuario) && !isset($idUsuario[0]))) $usrCond="";
        else {
            $usrCond=$this->getQueryExpression("idUsuario", $idUsuario);
        }
        if (is_null($idGrupo) || (is_string($idGrupo) && !isset($idGrupo[0]))) $gpoCond="";
        else {
            $gpoCond=$this->getQueryExpression("idGrupo", $idGrupo);
            if(isset($usrCond[0])&&isset($gpoCond[0])) $gpoCond=" AND $gpoCond";
        }
        if (is_null($idPerfil) || (is_string($idPerfil) && !isset($idPerfil[0]))) $perfCond="";
        else {
            $perfCond=$this->getQueryExpression("idPerfil", $idPerfil);
            if(isset($perfCond[0])&&(isset($usrCond[0])||isset($gpoCond[0]))) $perfCond=" AND $perfCond";
        }
        $tipoCond=(isset($tipo[0])?$this->getQueryExpression("tipo",$tipo):"");
        if (isset($tipoCond[0])&&(isset($usrCond[0])||isset($gpoCond[0])||isset($perfCond[0]))) $tipoCond=" AND $tipoCond";
        return $this->exists($usrCond.$gpoCond.$perfCond.$tipoCond);
    }
    function isRelatedByRFC($usr, $rfcEmpresa, $perfil, $tipo=null, $forceRead=false) {
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $idEmpresa = $gpoObj->getIdByRFC($rfcEmpresa, $forceRead);
        if (!empty($idEmpresa)) {
            global $perObj;
            if (!isset($perObj)) {
                require_once "clases/Perfiles.php";
                $perObj=new Perfiles();
            }
            $perfilId=$perObj->getIdByName($perfil);
            return $this->isRelated($usr->id, $idEmpresa, $perfilId, $tipo);
        }
        return false;
    }
    function isRelatedByPerfil($usr, $idEmpresa, $perfil, $tipo=null){
        global $perObj;
        if (!isset($perObj)) {
            require_once "clases/Perfiles.php";
            $perObj=new Perfiles();
        }
        $perfilId=$perObj->getIdByName($perfil);
        return $this->isRelated($usr->id, $idEmpresa, $perfilId, $tipo);
    }
    function getRefundGroupId($idUsuario, $idPerfil=0, $tipo="vista",$esReporte=false) {
        $refundGroupId=$this->getIdGroup($idUsuario, $idPerfil, $tipo);
        if (!isset($refundGroupId[0])) {
            global $gpoObj;
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj=new Grupo();
            }
            $gpoObj->rows_per_page=0;
            $refundGroupId=$esReporte?[]:$gpoObj->getValidRefundGroupId();

        }
        return $refundGroupId;
    }
    function getIdGrupo($idUsuario, $idPerfil, $tipo=null) {
        return getIdGroup($idUsuario, $idPerfil, $tipo);
    }
    function getIdGroup($idUsuario, $idPerfil, $tipo=null) {
        if (is_null($idUsuario) || (is_string($idUsuario) && !isset($idUsuario[0]))) $usrCond="";
        else {
            $usrCond=$this->getQueryExpression("idUsuario", $idUsuario);
        }
        if (is_null($idPerfil) || (is_string($idPerfil) && !isset($idPerfil[0]))) $perfCond="";
        else {
            $perfCond=$this->getQueryExpression("idPerfil", $idPerfil);
            if(isset($usrCond[0])&&isset($perfCond[0])) $perfCond=" AND $perfCond";
        }
        $tipoCond=(isset($tipo[0])?$this->getQueryExpression("tipo",$tipo):"");
        if (isset($tipoCond[0])&&(isset($usrCond[0])||isset($perfCond[0]))) $tipoCond=" AND $tipoCond";
        $ugData=$this->getData($usrCond.$perfCond.$tipoCond,0,"idGrupo");
        return array_column($ugData, "idGrupo");
    }
    function getIdGroupByNames($usr,$perfil,$tipo=null) {
        global $perObj;
        if (!isset($perObj)) {
            require_once "clases/Perfiles.php";
            $perObj=new Perfiles();
        }
        $perfilId=$perObj->getIdByName($perfil);
        if (!empty($perfilId))
            $idEmpresas=$this->getIdGroup($usr->id, $perfilId, $tipo);
        return $idEmpresas??[];
    }
    function getIdUsers($idPerfil,$idGrupo,$tipo=null) {
        if (is_null($idPerfil) || (is_string($idPerfil) && !isset($idPerfil[0]))) $perfCond="";
        else {
            $perfCond=$this->getQueryExpression("idPerfil", $idPerfil);
        }
        if (is_null($idGrupo) || (is_string($idGrupo) && !isset($idGrupo[0]))) $gpoCond="";
        else {
            $gpoCond=$this->getQueryExpression("idGrupo", $idGrupo);
            if(isset($perfCond[0])&&isset($gpoCond[0])) $gpoCond=" AND $gpoCond";
        }
        if (!isset($gpoCond[0])) return [];
        $tipoCond=(isset($tipo[0])?$this->getQueryExpression("tipo",$tipo):"");
        if (isset($tipoCond[0])&&(isset($perfCond[0])||isset($gpoCond[0]))) $tipoCond=" AND $tipoCond";
        $ugData=$this->getData($perfCond.$gpoCond.$tipoCond,0,"idUsuario");
        return array_column($ugData, "idUsuario");
    }
    function getGroupAliases($usr, $perfil, $tipo=null) {
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $gpoObj->rows_per_page=0;
        $idEmpresas=$this->getIdGroupByNames($usr, $perfil, $tipo);
        if (isset($idEmpresas[0]))
            $aliasEmpresas=$gpoObj->getAliasById($idEmpresas);
        return $aliasEmpresas??[];
    }
    function getFullMapWhere($usr,$perfiles=["Compras"]) {
        $oldRPP=$this->rows_per_page;
        $this->rows_per_page=0;
        if (!is_array($perfiles)) {
            if (!isset($perfiles)||!$perfiles||!isset($perfiles[0])) $perfiles=[];
            else $perfiles=[$perfiles];
        }
        doclog("getFullMapWhere","session",["usr"=>$usr,"perfiles"=>$perfiles]);
        $aliasEmpresas = $this->getGroupAliases($usr, $perfiles,"vista");
        if (empty($aliasEmpresas)) $fmw="status='activo'";
        else $fmw="alias in ('".implode("','",$aliasEmpresas)."')";
        $this->rows_per_page=$oldRPP;
        return $fmw??false;
    }
    function getGroupRFC($usr,$perfil,$tipo=null) {
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $gpoObj->rows_per_page=0;
        $idEmpresas=$this->getIdGroupByNames($usr, $perfil, $tipo);
        if (isset($idEmpresas[0]))
            $rfcEmpresas=$gpoObj->getRFCById($idEmpresas);
        return $rfcEmpresas??[];
    }
}
