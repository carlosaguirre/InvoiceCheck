<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ProveedorGrupo extends DBObject {
    function __construct() {
        $this->tablename      = "proveedor_grupo";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "idProveedor", "idGrupo", "cuentaContable", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx ProveedorGrupo xxxxxxxxxxxxxx //\n";
    }
}
