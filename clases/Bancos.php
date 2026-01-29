<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Bancos extends DBObject {
    function __construct() {
        $this->tablename      = "bancos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "clave", "alias", "razonSocial", "rfc", "cuenta", "status", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Bancos xxxxxxxxxxxxxx //\n";
    }
}
