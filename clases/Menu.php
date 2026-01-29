<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Menu extends DBObject {
    function __construct() {
        $this->tablename      = "menu";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "indice", "accion", "descripcion", "estilo", "titulo", "permiso", "regla", "status", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Menu xxxxxxxxxxxxxx //\n";
    }
    function getDMenu($rules) {
        $this->log.="// INI getDMenu ".json_encode($rules)." //\n";
        if (is_array($rules)) {
            $permiso=[];
            $regla=[];
            foreach ($rules as $idx=>$rule) {
                $x=strpos($rule, "=");
                if ($x===false) $permiso[]=$rule;
                else $regla[]=$rule;
            }
            $this->log.="// PERMISOS: ".json_encode($permiso)." //\n";
            $this->log.="// REGLAS: ".json_encode($regla)." //\n";
            $hasPerm=isset($permiso[0]);
            $hasRule=isset($regla[0]);
            //$where=($hasRule?"((":"").($hasPerm?"(":"")."permiso IS null".($hasPerm?" OR permiso".(isset($permiso[1])?" REGEXP '".implode("|",$permiso)."'":"='".$permiso[0]."'").")":"")." AND regla IS null".($hasRule?") OR regla".(isset($regla[1])?" IN ('".implode("','",$regla)."')":"='".$regla[0]."'").")":"");
            //El numero de perfiles del usuario no influye en el numero de perfiles permitidos en el menu. No debe usarse isset($permiso[0 o 1])
            $where=($hasRule?"((":"").($hasPerm?"(":"")."permiso IS null".($hasPerm?" OR permiso"." REGEXP '".implode("|",$permiso)."'".")":"")." AND regla IS null".($hasRule?") OR regla".(isset($regla[1])?" IN ('".implode("','",$regla)."')":"='".$regla[0]."'").")":"");
            $this->log.="// ARRAY: WHERE = $where //\n";
        } else if ($rules==="user.name=admin") $where=""; else { // ToDo: Eliminar condicion admin y perfil Diseño en producción
            $isAdm=($rules==="PAdministrador");
            $isSis=($isAdm || $rules==="PSistemas");
            $isPrm=(strpos($rules, "=")===false);
            $where=($isSis?"(permiso IS null OR permiso".($isAdm?"!='PDiseño'":" NOT IN ('PAdministrador','PDiseño')").") AND (regla IS null OR regla!='user.name=admin')":($isPrm?"(":"")."permiso IS null".($isPrm?" OR permiso='$rules')":"")." AND ".($isPrm?"":"(")."regla IS null".($isPrm?"":" OR regla='$rules')"));
            $this->log.="// STRING: WHERE = $where //\n";
        }
        $where.=(isset($where[0])?" AND ":"")."status=1";
        if ($this->hasOrder()) {
            $ol=$this->orderlist;
            $this->clearOrder();
        }
        $this->addOrder("indice*1");
        $result = $this->getData($where, 0, "indice,accion,descripcion,estilo,titulo");
        if (isset($ol)) $this->orderlist=$ol;
        else $this->clearOrder();
        return $result;
    }
}
