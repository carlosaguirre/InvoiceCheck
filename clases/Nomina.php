<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Nomina extends DBObject {
    function __construct() {
        $this->tablename      = "nomina";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "idempleado", "numero", "periodoInicio", "periodoFin", "cuenta", "monto", "ultimaEdicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['numero'] = array('skey' => 'y');
        $this->fieldlist['ultimaEdicion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Nomina xxxxxxxxxxxxxx //\n";
    }
}
