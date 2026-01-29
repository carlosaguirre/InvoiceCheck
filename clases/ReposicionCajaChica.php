<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ReposicionCajaChica extends DBObject {
    function __construct() {
        $this->tablename      = "reposicioncajachica";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "fechasolicitud","fechapago","beneficiario","empresaId","concepto","banco","cuentabancaria","cuentaclabe","monto","observaciones","solicitante","autorizadopor","rechazadopor","pagadoPor","ultimorespaldo","archivoxml","archivopdf", "ultimaedicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['ultimaedicion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Reposicion Caja Chica xxxxxxxxxxxxxx //\n";
    }
}
