<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Empleados extends DBObject {
    function __construct() {
        $this->tablename      = "empleados";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "numero", "nombre", "cuentaTC", "cuentaCLABE", "empresa", "status", "ultimaEdicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['numero'] = array('skey' => 'y');
        $this->fieldlist['ultimaEdicion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Empleados xxxxxxxxxxxxxx //\n";
    }
}
