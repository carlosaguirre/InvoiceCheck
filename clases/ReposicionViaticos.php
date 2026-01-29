<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ReposicionViaticos extends DBObject {
    function __construct() {
        $this->tablename      = "reposicionviaticos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id","fechasolicitud","fechapago","beneficiario","empresaId","lugaresvisita","banco","cuentabancaria","cuentaclabe","viaticosrequeridos","diferencialiquidar","montototal","observaciones","solicitante","autorizadoPor","rechazadoPor","pagadoPor","ultimorespaldo","ultimaedicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['ultimaedicion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Reposicion Viaticos xxxxxxxxxxxxxx //\n";
    }
}
