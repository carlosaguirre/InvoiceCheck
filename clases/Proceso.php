<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Proceso extends DBObject {
    function __construct() {
        $this->tablename      = "proceso";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "modulo", "identif", "status", "detalle", "fecha", "usuario", "region", "zona");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['identif'] = array('skey' => 'y');
        $this->fieldlist['status'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Proceso xxxxxxxxxxxxxx //\n";
    }
    // Posibles valores de cambio de status de factura:
    // Temporal, Pendiente, Aceptado, Contrarrecibo, Exportado, Respaldado, RespSinExp, Borrado
    function cambioFactura($id, $status, $usuario, $fecha=false, $detalle="Cambio de Status") {
        return $this->alta("Factura",$id,$status,$detalle,$usuario,$fecha);
    }
    function debugStatus($id,$status,$usuario,$fecha=false,$detalle="Debug de Status") {
        return $this->alta("DebugStatus",$id,$status,$detalle,$usuario,$fecha);
    }

    // Posibles valores de cambio de status de sesion:
    // Inicio, Cierre
    function cambioSesion($id, $status, $usuario, $detalle, $fecha=false) {
        return $this->alta("Sesion",$id,$status,$detalle,$usuario,$fecha);
    }
    // Posibles valores de cambio de status de administracion: Alta, Baja, Cambio
    // Estructura de detalle: (Proveedor|Corporativo|Usuario) (codigo|alias|usuario) (razon social|nombre completo)
    function cambioAdmin($id,$status,$usuario,$detalle,$fecha=false) {
        return $this->alta("Admin",$id,$status,$detalle,$usuario,$fecha);
    }
    function cambioContrarrecibo($id,$status,$usuario,$detalle,$fecha=false) {
        return $this->alta("Contrarrecibo",$id,$status,$detalle,$usuario,$fecha);
    }
    function cambioUsuario($id,$status,$usuario,$detalle,$fecha=false) {
        return $this->alta("Usuario",$id,$status,$detalle,$usuario,$fecha);
    }
    function cambioProveedor($idPrv,$sttPrv,$usuario,$detalle,$datos=null) {
        return $this->alta("Proveedor",$idPrv,$sttPrv,$detalle,$usuario);
    }
    function cambiaToken($idToken,$detalle,$status="ocupado",$usuario=null,$fecha=false) {
        return $this->alta("Token",$idToken,$status,$detalle,$usuario,$fecha);
    }
    function cambiaSolicitud($idSol,$status,$detalle,$usuario=null) {
        return $this->alta("SolPago",$idSol,$status,$detalle,$usuario);
    }
    function anotaAltaMasiva($idFactura, $status, $detalle, $usuario=null) {
        return $this->alta("AltaMasiva", $idFactura, $status, $detalle, $usuario);
    }
    function alta($modulo,$identif,$status,$detalle,$usuario=null,$fecha=null) {
        if (!isset($usuario[0]) && hasUser()) $usuario=getUser()->nombre;
        if (!isset($fecha[0])) $fecha = date("Y-m-d H:i:s");
        return $this->insertRecord(["modulo"=>$modulo,"identif"=>$identif,"status"=>$status,"detalle"=>$detalle,"fecha"=>$fecha,"usuario"=>$usuario,"region"=>getIP()]);
    }
}
