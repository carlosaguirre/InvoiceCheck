<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Articulos extends DBObject {
    function __construct() {
        $this->tablename      = "articulos";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "codigoProveedor", "aliasGrupo", "codigoProdServ", "codigoUnidad", "codigoFormaPago", "codigoArticulo", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Articulos xxxxxxxxxxxxxx //\n";
    }
}
