<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ProveedorTipos extends DBObject {
    function __construct() {
        $this->tablename      = "proveedortipos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "nombre", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx ProveedorTipos xxxxxxxxxxxxxx //\n";
    }
}
