<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Conceptos extends DBObject {
    function __construct() {
        $this->tablename      = "conceptos";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "idFactura", "codigoArticulo", "cantidad", "unidad", "claveUnidad", "claveProdServ", "descripcion", "precioUnitario", "importe", "importeDescuento", "impuestoTraslado", "impuestoRetenido", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['idFactura'] = array('skey' => 'y');
        $this->fieldlist['codigoArticulo'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Conceptos xxxxxxxxxxxxxx //\n";
    }
}
