<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Pagos extends DBObject {
    function __construct() {
        $this->tablename      = "pagos";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id","archivo","codigoProveedor", "idFactura","fechaPago","cantidad","iva","total","tipo","referencia","valido","modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Pagos xxxxxxxxxxxxxx //\n";
    }
    function insertIntoProceso($maxId,$user) {
        //$data = $this->getData ("id>$maxId", 0, "idFactura,referencia,total,fechaPago");
        //require_once "clases/Proceso.php";
        $ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
        // ToDo: Mover este codigo en una funcion dentro del objeto PROCESO
        $query="INSERT INTO proceso (modulo,identif,status,detalle,fecha,usuario,region) (SELECT 'Factura',idFactura,'Pagado',concat(referencia,' x $',format(total,2)),fechaPago,'$user->nombre','$ip' from pagos where id>$maxId and valido=1)";
        DBi::query($query); // proceso no entra para replicar insert, no hace falta pasar el objeto
    }
}
