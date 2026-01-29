<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ReposicionArchivos extends DBObject {
    function __construct() {
        $this->tablename      = "reposicionarchivos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "tipo","repid","conid","archivoxml","archivopdf","originalxml","foliofactura","uuid","fechafactura","totalfactura", "tipocomprobante", "rfcemisor", "archivostatus", "ultimaedicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['tipo'] = array('auto'=>'y'); // deshabilita uso para viaticos
        $this->fieldlist['conid'] = array('auto'=>'y'); // deshabilita uso para viaticos
        $this->fieldlist['ultimaedicion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Reposicion Caja Chica xxxxxxxxxxxxxx //\n";
    }
}
