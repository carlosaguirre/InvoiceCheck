<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class OpcionesBloqueo extends DBObject {
    function __construct() {
        $this->tablename      = "opcionesbloqueo";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "texto", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx OpcionesBloqueo xxxxxxxxxxxxxx //\n";
    }
}
