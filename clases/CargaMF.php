<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class CargaMF extends DBObject {
    const STATUS_PROCESO=0;     // En proceso
    const STATUS_INGRESADO=1;   // Alta satisfactoria
    const STATUS_YAEXISTE=2;      // Ya existe en sistema
    const STATUS_NOPROVEEDOR=3; // Proveedor no registrado
    const STATUS_BDEXISTE=4; // Ya existe, verificado en BD
    const STATUS_OTRO=10;       // Otro
    const STATUS_ELIMINADO=78; // ELIMINADO
    function __construct() {
        $this->tablename      = "cargamf";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "nombreArchivo", "rutaArchivo", "idFactura", "status", "fechaCarga", "descripcion", "tipo", "metodo", "datos", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx CargaMF xxxxxxxxxxxxxx //\n";
    }
}
