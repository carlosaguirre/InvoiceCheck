<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class RepViaConceptos extends DBObject {
    function __construct() {
        $this->tablename      = "repviaconceptos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "vid", "fecha","concepto","foliofactura","uuid","fechafactura","importe","archivoxml","archivopdf","originalxml","archivostatus", "ultimaedicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['ultimaedicion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Rep-Via Conceptos xxxxxxxxxxxxxx //\n";
    }
}
