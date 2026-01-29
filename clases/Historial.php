<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Historial extends DBObject {
    function __construct() {
        $this->tables         = ["historialint","historialchar"];
        $this->tabINT         = 0;
        $this->tabCHR         = 1;
        $this->tablename      = "historialchar";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "tabla", "campo", "valor", "tiempo");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Historial xxxxxxxxxxxxxx //\n";
    }
    function toInt() {
        $this->tablename="historialint";
    }
    function toChar() {
        $this->tablename="historialchar";
    }
}
