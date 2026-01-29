<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Perfiles extends DBObject {
    function __construct() {
        $this->tablename      = "perfiles";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "nombre", "detalle", "estado", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['nombre'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Perfiles xxxxxxxxxxxxxx //\n";
    }
    function getIdByName($name, $forceRead=false) {
        static $idByNameCache = [];
        if (is_array($name)) {
            $arrayResult=[];
            foreach ($name as $idx => $value) {
                $result=$this->getIdByName($value, $forceRead);
                if (isset($result)) $arrayResult[]=$result;
            }
            if (isset($arrayResult[0])) return $arrayResult;
            return null;
        }
        if (!isset($idByNameCache[$name]) || $forceRead) {
            $valueId = $this->getValue("nombre", $name, "id");
            if (!empty($valueId))
                $idByNameCache[$name] = $valueId;
        }
        return $idByNameCache[$name]??null;
    }
}
