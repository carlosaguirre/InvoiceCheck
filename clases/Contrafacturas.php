<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Contrafacturas extends DBObject {
    function __construct() {
        $this->tablename      = "contrafacturas";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "idContrarrecibo", "idFactura", "pedido", "folioFactura", "serieFactura", "fechaFactura", "fechaCaptura","fechaVencimiento", "metodoDePago", "tipoComprobante", "nombreInterno", "ubicacion", "subtotal", "total", "retencion", "moneda", "autorizadaPor","fechaAutorizada", "primeraImpresion", "ea", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Contrafacturas xxxxxxxxxxxxxx //\n";
    }
    function esAutorizado($ctrId) {
        // select count(1) from contrafacturas
        // c inner join facturas f on c.idFactura=f.id
        // where c.idContrarrecibo=144220 and c.autorizadaPor is not null and f.statusn between 1 and 31;
        $extraFrom = "c inner join facturas f on c.idFactura=f.id";
        $where = "c.idContrarrecibo=$ctrId and c.autorizadaPor is not null and f.statusn between 1 and 31";
        return $this->exists($where, $extraFrom);
    }
}
