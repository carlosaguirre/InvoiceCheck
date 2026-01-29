<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ProveedorTipoCuentas extends DBObject {
    function __construct() {
        $this->tablename      = "proveedortipo_cuentas";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "idProveedorTipo", "idCuenta", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx ProveedorTipoCuentas xxxxxxxxxxxxxx //\n";
    }
}
