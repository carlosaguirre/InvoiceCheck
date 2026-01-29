<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Permisos extends DBObject {
    public static $CONSULTA = 1;
    public static $MODIFICA = 2;
    public static $NIVEL_BASICO = 1;
    public static $NIVEL_TOTAL = 9;
    function __construct() {
        $this->tablename      = "permisos";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "idPerfil", "idAccion", "consulta", "modificacion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['idPerfil'] = array('skey' => 'y');
        $this->fieldlist['idAccion'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Permisos xxxxxxxxxxxxxx //\n";
    }
    function consultaValida($usuario, $accion, $nivel=1) {
        return $this->validarPermisoGral($usuario, $accion, Permisos::$CONSULTA, $nivel);
    }
    function modificacionValida($usuario, $accion, $nivel=1) {
        return $this->validarPermisoGral($usuario, $accion, Permisos::$MODIFICA, $nivel);
    }
    function resetPermisos($usuario) {
        if (empty($usuario) || !is_object($usuario) || !isset($usuario->id) || empty($accion))
            validarPermiso(0,0,0,true);
        else
            validarPermiso($usuario->id,0,0,true);
    }
    private function validarPermisoGral ($usuario, $accion, $tipo, $nivel=1) {
        static $actObj=null;
        static $act2Id=[];
        if (empty($usuario) || !is_object($usuario) || !isset($usuario->id) || empty($accion)) return false;
        if(!isset($act2Id[$accion])) {
            if ($actObj==null) {
                require_once "clases/Acciones.php";
                $actObj=new Acciones();
            }
            $act2Id[$accion]=$actObj->getValue("nombre",$accion,"id");
        }
        if ($tipo==Permisos::$CONSULTA) return $this->validarPermiso($usuario->id, $act2Id[$accion], $nivel)->consulta;
        if ($tipo==Permisos::$MODIFICA) return $this->validarPermiso($usuario->id, $act2Id[$accion], $nivel)->modificacion;
        return false;
    }
    function validarPermiso ($idUsuario = 0, $idAccion=0, $nivel=1, $resetPermisos=false) {
        static $upObj=null;
        static $perfiles=[];
        static $values=[];

        if ($resetPermisos) {
            if (empty($idUsuario)) {
                $upObj=null;
                $perfiles=[];
                $values=[];
            } else {
                unset($perfiles[$idUsuario]);
                unset($values[$idUsuario]);
            }
        }
        if ($idUsuario<=0) return false;
        if (empty($values[$idUsuario])) $values[$idUsuario]=[];
        if ($idAccion==0) return false;
        if (empty($values[$idUsuario][$idAccion])) {
            if (empty($perfiles[$idUsuario])) {
                if($upObj==null) {
                    require_once "clases/Usuarios_Perfiles.php";
                    $upObj = new Usuarios_Perfiles();
                    $upObj->rows_per_page=0;
                }
                $upData = $upObj->getData("idUsuario='$idUsuario'");
                if (empty($upData)) return (object) array("consulta" => false, "modificacion" => false);
                $perfiles[$idUsuario] = "";
                foreach($upData as $upRow) {
                    if (strlen($perfiles[$idUsuario])>0) $perfiles[$idUsuario] .= ",";
                    $perfiles[$idUsuario] .= $upRow["idPerfil"];
                }
                if (empty($perfiles[$idUsuario])) return (object) array("consulta" => false, "modificacion" => false);
            }
            $values[$idUsuario][$idAccion] = $this->getValue("idAccion",$idAccion,"IF(MAX(consulta)>=$nivel,1,0) as consulta, IF(MAX(modificacion)>=$nivel,1,0) as modificacion","idPerfil IN (".$perfiles[$idUsuario].") GROUP BY idAccion");
            $this->log .= "Usuario: $idUsuario\nPerfiles: ".$perfiles[$idUsuario]."\nValues:".$values[$idUsuario][$idAccion]."\n";
        }
        $valUsrAcc = $values[$idUsuario][$idAccion];
        if (isset($valUsrAcc[0])) {
            if(strpos($valUsrAcc,"|")!==false)
                list($perCon, $perMod) = explode("|",$valUsrAcc);
            else {
                $perCon=$valUsrAcc;
                $perMod="0";
            }
        } else {
            $perCon="0";
            $perMod="0";
        }
        return (object) array("consulta" => ($perCon=="1"), "modificacion" => ($perMod=="1"));
    }
}
