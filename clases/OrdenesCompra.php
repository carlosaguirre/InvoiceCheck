<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class OrdenesCompra extends DBObject {
    const STATUS_INGRESADO=0;
    const STATUS_CON_FACTURA=1;
    const STATUS_AUTORIZADO=2;
    const STATUS_PAGADO=4;
    const STATUS_CANCELADO=-1;
    function __construct() {
        $this->tablename      = "ordenescompra";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "folio", "idEmpresa", "idProveedor", "fecha", "rutaArchivo", "nombreArchivo", "comprobantePago", "tieneAntecedentes", "tieneSello", "selloImpreso", "importe", "moneda", "status", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['folio'] = array('skey' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx OrdenesCompra xxxxxxxxxxxxxx //\n";
    }
    static function describeStatus($status) { // ToDo: Distinguir cuando se recibe una factura y se incluye la orden de compra pero todo el proceso se realiza con la factura... el status sería Orden de Compra anexada... podria ser status 8
        if (is_string($status) && isset($status[0])) $status=+$status;
        if ($status<0) return "Orden de Compra cancelada";
        if ($status>=4) {//($status&4>0) {
            if (($status&1)>0) return "Orden Pagada con Factura";
            return "Orden de Compra pagada";
        }
        if (($status&2)>0) {
            if (($status&1)>0) return "Orden Autorizada con Factura";
            return "Orden de Compra autorizada";
        }
        if ($status==1) {
            return "Orden de Compra Anexada";
        }
        if ($status==0) return "Orden de Compra sin autorizaci&oacute;n";
        return "Status '$status' no contemplado";
    }
}
