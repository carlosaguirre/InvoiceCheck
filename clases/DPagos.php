<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class DPagos extends DBObject {
    function __construct() {
        $this->tablename      = "dpagos";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id","idPPago","idFactura", "numParcialidad","saldoAnterior","impPagado","saldoInsoluto","moneda","equivalencia","modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx DPagos xxxxxxxxxxxxxx //\n";
    }
    function insertIntoProceso($idf,$user) {
        $ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
        // ToDo: Mover este codigo en una funcion dentro del objeto PROCESO
        $query="INSERT INTO proceso (modulo,identif,status,detalle,fecha,usuario,region) (SELECT 'DPago',$idf,if(saldoInsoluto=0,'Pagado','Parcial'),concat('$',format(saldoAnterior,2),' - $',format(impPagado,2),' = $',format(saldoInsoluto,2)),fechaPago,'$user->nombre','$ip' from dpagos where id=$idf)";
        DBi::query($query); // proceso no entra para replicar insert, no hace falta pasar el objeto
    }
    function updateTables($codigoProveedor, $numChanges=100) {
        global $invObj, $cpyObj;
        if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
        if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
        $invObj->rows_per_page=$numChanges;
        $invData = $invObj->getData("codigoProveedor='$codigoProveedor' and tipoComprobante='p' and id not in (select distinct cp.idCPago from CPagos cp inner join DPagos dp on cp.id=dp.idPPago)");
        require_once "clases/CFDI.php";
        $sysPath=$_SERVER["DOCUMENT_ROOT"];
        foreach ($invData as $iidx => $iitem) {
            $invId=$iitem["id"];
            $ubicacion = $iitem["ubicacion"];
            $nombreXML = $iitem["nombreInterno"];
            $cfdiObj = CFDI::newInstanceByLocalName($sysPath.$ubicacion.$nombreXML.".xml");
            if (isset($cfdiObj)) {
            }
        }
    }
}
