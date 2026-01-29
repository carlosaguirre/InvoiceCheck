<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Servicios extends DBObject {
    function __construct() {
        $this->tablename      = "servicios";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "claveUnidad", "claveProdServ", "codigoProveedor", "codigoArticulo", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['claveUnidad'] = array('skey' => 'y');
        $this->fieldlist['claveProdServ'] = array('skey' => 'y');
        $this->fieldlist['codigoProveedor'] = array('skey' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Servicios xxxxxxxxxxxxxx //\n";
    }
}
