<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class _blueprint extends DBObject {
    function __construct() {
        $this->tablename      = "_blueprint";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "_field", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx _blueprint xxxxxxxxxxxxxx //\n";
    }
}
