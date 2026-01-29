<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Doctos extends DBObject {
    function __construct() {
        $this->tablename     = "doctos";
        $this->rows_per_page = 0;
        $this->fieldlist     = array("id","idCPago","idFactura","idDocumento","serie",
            "folio","moneda","equivalencia","parcialidad","saldoAnterior",
            "importePagado","saldoInsoluto","objetoImpuesto","status","modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx DOCTOS xxxxxxxxxxxxxx //\n";
    }
}
