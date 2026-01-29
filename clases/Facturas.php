<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Facturas extends DBObject {
    const CAMPO_ID = 6133001; // "id", 61330[01-34], 61331[01-18], 61332[01]
    const CAMPO_PEDIDO = 6133002; // "pedido", 
    const CAMPO_REMISION = 6133201; // "remision",
    const CAMPO_CODIGO_PROVEEDOR = 6133003; // "codigoProveedor", 
    const CAMPO_REGIMEN_FISCAL = 6133101; // "regimenFiscal", 
    const CAMPO_RFC_GRUPO = 6133004; // "rfcGrupo", 
    const CAMPO_USO_CFDI = 6133102; // "usoCFDI", 
    const CAMPO_FECHA_FACTURA = 6133005; // "fechaFactura", 
    const CAMPO_FECHA_CAPTURA = 6133006; // "fechaCaptura", 
    const CAMPO_FECHA_APROBACION = 6133007; // "fechaAprobacion", 
    const CAMPO_FECHA_VENCIMIENTO = 6133008; // "fechaVencimiento", 
    const CAMPO_UUID = 6133009; // "uuid", 
    const CAMPO_SERIE = 6133010; // "serie", 
    const CAMPO_FOLIO = 6133011; // "folio", 
    const CAMPO_NO_CERTIFICADO = 6133012; // "noCertificado", 
    const CAMPO_FORMA_DE_PAGO = 6133013; // "formaDePago", 
    const CAMPO_METODO_DE_PAGO = 6133014; // "metodoDePago", 
    const CAMPO_IMPORTE_DESCUENTO = 6133015; // "importeDescuento", 
    const CAMPO_IMPUESTO_TRASLADO = 6133016; // "impuestoTraslado",
    const CAMPO_IMPUESTO_RETENIDO = 6133017; // "impuestoRetenido", 
    const CAMPO_SUBTOTAL = 6133018; // "subtotal", 
    const CAMPO_TOTAL = 6133019; // "total", 
    const CAMPO_TIPO_COMPROBANTE = 6133020; // "tipoComprobante", 
    const CAMPO_TIPO_CAMBIO = 6133021; // "tipoCambio", 
    const CAMPO_MONEDA = 6133103; // "moneda", 
    const CAMPO_TASA_IVA = 6133022; // "tasaIva", 
    const CAMPO_NOMBRE_ORIGINAL = 6133023; // "nombreOriginal", 
    const CAMPO_CICLO = 6133104; // "ciclo", 
    const CAMPO_NOMBRE_XML = 6133024; // "nombreInterno", 
    const CAMPO_NOMBRE_PDF = 6133025; // "nombreInternoPDF", 
    const CAMPO_NOMBRE_PAGO = 6133105; // "comprobantePagoPDF", 
    const CAMPO_UBICACION = 6133026; // "ubicacion", 
    const CAMPO_TIENE_SELLO = 6133106; // "tieneSello", 
    const CAMPO_SELLO_IMPRESO = 6133107; // "selloImpreso",  
    const CAMPO_TIENE_ORDEN = 6133108; // "tieneOrden", 
    const CAMPO_MENSAJE_CFDI = 6133027; // "mensajeCFDI", 
    const CAMPO_ESTADO_CFDI = 6133028; // "estadoCFDI", 
    const CAMPO_CANCELABLE_CFDI = 6133109; // "cancelableCFDI", 
    const CAMPO_CANCELADO_CFDI = 6133110; // "canceladoCFDI", 
    const CAMPO_SOLICITA_CFDI = 6133111; // "solicitaCFDI", 
    const CAMPO_CONSULTA_CFDI = 6133112; // "consultaCFDI", 
    const CAMPO_NUM_CONSULTAS_CFDI = 6133113; // "numConsultasCFDI", 
    const CAMPO_FECHA_PAGO = 6133114; // "fechaPago", 
    const CAMPO_TOTAL_PAGO = 6133115; // "totalPago", 
    const CAMPO_REFERENCIA_PAGO = 6133116; // "referenciaPago", 
    const CAMPO_ID_PAGO = 6133029; // "idReciboPago", 
    const CAMPO_FECHA_RECIBO_PAGO = 6133117; // "fechaReciboPago", 
    const CAMPO_SALDO_PAGO = 6133030; // "saldoReciboPago", 
    const CAMPO_VERSION = 6133031; // "version", 
    const CAMPO_STATUS = 6133032; // "status", 
    const CAMPO_STATUS_N = 6133033; // "statusn", 
    const CAMPO_EA = 6133118; // "ea", 
    const CAMPO_MODIFIED_TIME = 6133034; // "modifiedTime"
    const STATUS_PENDIENTE=0;
    const STATUS_ACEPTADO=1;
    const STATUS_CONTRA_RECIBO=2;
    const STATUS_EXPORTADO=4;
    const STATUS_RESPALDADO=8;
    const STATUS_PROGPAGO=16; // Este estado no se ocupa
    const STATUS_PAGADO=32;
    const STATUS_RECPAGO=64;
    const STATUS_RECHAZADO=128;
    const STATUS_CANCELADOSAT=256;
    const ERROR_AUTO_NOXML=1000001;
    const ERROR_AUTO_EMPTY=1000002;
    const ERROR_AUTO_NOCFDI=1000003;
    const ERROR_AUTO_NOCFDI2=1000004;
    const ERROR_AUTO_ERRCFDI=1000005;
    const ERROR_AUTO_ERRCFDI2=1000006;
    const ERROR_AUTO_BADTC=1000007;
    const ERROR_AUTO_NOCLI=1000008;
    const ERROR_AUTO_NOPRV=1000009;
    const ERROR_AUTO_NOPRV2=1000010;
    const ERROR_AUTO_NOSRV=1000011;
    const ERROR_AUTO_NOUUID=1000012;
    const ERROR_AUTO_HASSOL=1000013;
    const ERROR_AUTO_BADFOIL=1000014;
    const ERROR_AUTO_NOFOIL=1000015;
    const ERROR_AUTO_SAMEFOIL=1000016;
    const ERROR_AUTO_SAMEFOIL2=1000017;
    const ERROR_AUTO_HASSOL2=1000018;
    const ERROR_AUTO_BADSTT=1000019;
    const ERROR_AUTO_NOSAT=1000020;
    const ERROR_AUTO_NOSAT2=1000021;
    const ERROR_AUTO_ERRSAT=1000022;
    const ERROR_AUTO_SATN602=1000023;
    const ERROR_AUTO_ERRSAT2=1000024;
    const ERROR_AUTO_SATOUT=1000025;
    const ERROR_AUTO_BADCALC=1000026;
    const ERROR_AUTO_DB=1000027;
    const ERROR_AUTO_ERRSAT1=1000028;
    const ERROR_AUTO_UNKNOWN=1010000;
    const EA_SIN=0;
    const EA_CON=1;
    const EA_NA=-1;
    const EPSILON=0.015;
    //public static $stmpFmt=null;

    function __construct() {
        $this->tablename      = "facturas";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "pedido", "remision", "codigoProveedor", "regimenFiscal", "rfcGrupo", "usoCFDI", "fechaFactura", "fechaCaptura", "fechaAprobacion", "fechaVencimiento", "uuid", "serie", "folio", "noCertificado", "formaDePago", "metodoDePago", "importeDescuento", "impuestoTraslado","impuestoRetenido", "subtotal", "total", "tipoComprobante", "tipoCambio", "moneda", "tasaIva", "nombreOriginal", "ciclo", "nombreInterno", "nombreInternoPDF", "comprobantePagoPDF", "ubicacion", "tieneSello","selloImpreso", "tieneOrden", "mensajeCFDI", "estadoCFDI", "cancelableCFDI", "canceladoCFDI", "solicitaCFDI", "consultaCFDI", "numConsultasCFDI", "fechaPago", "totalPago", "referenciaPago", "idReciboPago", "fechaReciboPago", "saldoReciboPago", "statusReciboPago", "version", "status", "statusn", "ea", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        // $this->fieldlist['pedido'] = array('skey' => 'y');
        $this->fieldlist['codigoProveedor'] = array('skey' => 'y');
        $this->fieldlist['rfcGrupo'] = array('skey' => 'y');
        $this->fieldlist['uuid'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Facturas xxxxxxxxxxxxxx //\n";
        //if (Facturas::$stmpFmt===null) {
        //    Facturas::$stmpFmt=new IntlDateFormatter('spanish', IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Etc/GMT+6", IntlDateFormatter::GREGORIAN, "d MMM., Y");
        //}
        //doclog("Facturas::construct","test",["tablename"=>$this->tablename]);
    }
    public static function actionToStatusN($status) {
        switch(strtolower($status)) {
            case "pendiente": return 0;
            case "aceptado": return 1;
            case "contrarrecibo": return 2;
            case "exportado": return 4;
            case "respaldado": return 8;
            case "progpago": return 16;
            case "pagado": return 32;
            case "recpago": return 64;
            case "rechazado": return 128;
        }
        return null;
    }
    public static function statusToStatusN($status) {
        switch(strtolower($status)) {
            case "pendiente": return 0;
            case "aceptado": return 1;
            case "contrarrecibo": return 3; // aceptado(1)+contrarrecibo(2)
            case "expsincontra": return 5; // aceptado(1)+exportado(4)
            case "exportado": return 7; // aceptado(1)+contrarrecibo(2)+exportado(4)
            case "respsincx": return 9; // aceptado(1)+respaldado(8)
            case "respsinexp": return 11; // aceptado(1)+contrarrecibo(2)+respaldado(8)
            case "respsincontra": return 13; // aceptado(1)+exportado(4)+respaldado(8)
            case "respaldado": return 15; // aceptado(1)+contrarrecibo(2)+exportado(4)+respaldado(8)
            case "progpago": return 31; // aceptado(1)+contrarrecibo(2)+exportado(4)+respaldado(8)+pagado(16)
            case "pagado": return 63; // aceptado(1)+contrarrecibo(2)+exportado(4)+respaldado(8)+pagado(16)
            case "recpago": return 127; // aceptado(1)+contrarrecibo(2)+exportado(4)+respaldado(8)+pagado(16)
            case "rechazado": return 128; // entre 128 y 255. Desconociendo historico, queda directo 128.
        }
        return null;
    }
    public static function statusnToStatus($statusN=null) {
        if (isset($statusN)&&$statusN>=0) {
            if ($statusN<1) return "Pendiente";
            if ($statusN<2) return "Aceptado";
            if ($statusN<4) return "Contrarrecibo";
            if ($statusN<8) return "Exportado";
            if ($statusN<16) return "Respaldado";
            if ($statusN<32) return "ProgPago";
            if ($statusN<64) return "Pagado";
            if ($statusN<128) return "RecPago";
            return "Rechazado";
        }
        return "Temporal";
    }
    public static function statusnToRealStatus($statusN=null, $tipoComprobante="i", $level=0) { // 0=proveedor,1=compras,2=modificaProcesar,3=detalle
        //doclog("INI FUNC","test",["class"=>"Facturas","function"=>"statusnToRealStatus","statusn"=>$statusN, "tc"=>$tipoComprobante, "level"=>$level]);
        if (!isset($statusN)) return "Temporal";
        if ($statusN<0) return "Error";
        if ($statusN>255) return "CanceladoSAT";
        $esProveedor=($level==0);
        $esCompras=($level==1);
        $esAdmin=($level==2); // o con permiso de modificar Procesar
        $det=($level==3); // admin o modifica procesar pero mas detallado
        //doclog("ASSIGN1","test",["class"=>"Facturas","function"=>"statusnToRealStatus","esProveedor"=>$esProveedor?"SI":"NO", "esCompras"=>$esCompras?"SI":"NO", "esAdmin"=>$esAdmin?"SI":"NO", "det"=>$det?"SI":"NO", "level"=>$level]);
        if (empty($tipoComprobante)) $tipoComprobante="i";
        if (isset($tipoComprobante[1])) $tipoComprobante=$tipoComprobante[0];
        $tipoComprobante=strtolower($tipoComprobante);
        $esEgreso=($tipoComprobante==="e");
        $esPago=($tipoComprobante==="p");
        $esTraslado=($tipoComprobante==="t");
        $esIngreso=($tipoComprobante==="i"); // !($esEgreso||$esPago);
        if ($statusN>=128) {
            if ($det && $statusN&2) return "Rechazado con Contrarrecibo";
            return "Rechazado";
        }
        $detalle="";
        if ($det) {
            if ($statusN>1 && ($statusN&1)==0) $detalle.=" sin Aceptar";
            if ($statusN>2 && ($statusN&2)==0 && !$esPago && !$esTraslado) $detalle.=" sin Contra-Recibo";
            if ($statusN>4 && ($statusN&4)==0 && $esIngreso) $detalle.=" sin Exportar";
            if ($statusN>8 && ($statusN&8)==0) $detalle.=" sin Respaldar";
            $esAdmin=true;
        }
        //doclog("detalle","test",["class"=>"Facturas","function"=>"statusnToRealStatus","detalle"=>$detalle,"esAdmin"=>$esAdmin?"SI":"NO"]);
        if (!$esProveedor && $statusN>=64) {
            if ($det) return "Con Complemento de Pago".$detalle; // (Compras|Admin)&statusn>64
            return "con C.Pago";
        }
        if ($statusN>=32) return "Pagado".$detalle; //Todos&statusn>32
        $isAdmPP=false;
        $PPP="";
        if ($statusN>=16) {
            $statusN-=16;
            if($esAdmin) {
                $isAdmPP=true;
                $PPP="Prog Pago ";
            }
        }
        //doclog("ProgPago","test",["class"=>"Facturas","function"=>"statusnToRealStatus","PPP"=>$PPP,"isAdmPP"=>$isAdmPP?"SI":"NO"]);
        if ($statusN==0 || ($esProveedor && ($statusN&1==0)))
            return $PPP."Pendiente";
        if ($statusN==1 || ($esProveedor && ($statusN&2==0)))
            return $PPP."Aceptado";
        if ($statusN<=3) $retval="Contra-Recibo";
        else if ($statusN<=7) $retval="Exportado";
        else $retval="Respaldado";
        //doclog("TC & RETVAL","test",["class"=>"Facturas","function"=>"statusnToRealStatus","tc"=>$tipoComprobante,"esEgreso"=>$esEgreso?"SI":"NO","esPago"=>$esPago?"SI":"NO","esTraslado"=>$esTraslado?"SI":"NO","esIngreso"=>$esIngreso?"SI":"NO","retval"=>$retval]);
        if (($statusN&1)==0) return $PPP.$retval." no Aceptado";
        if ($esProveedor) return "Contra-Recibo"; // 3,7,11,15
        if (($statusN&2)!=0) $retval="Contra-Recibo";
        if (($statusN&4)!=0) $retval="Exportado";
        if (($statusN&8)!=0) $retval="Respaldado";
        if ($det) return $PPP.$retval.$detalle;
        //doclog("RETVAL FIX1","test",["class"=>"Facturas","function"=>"statusnToRealStatus","statusn"=>$statusN,"ANDCHECK"=>["2"=>($statusN&2),"4"=>($statusN&4),"8"=>($statusN&8)],"TESTS"=>["2"=>($statusN&2!=0)?"YES":"NO","4"=>($statusN&4!=0)?"YES":"NO","8"=>($statusN&8!=0)?"YES":"NO"],"TESTSB"=>["2"=>(($statusN&2)!=0)?"YES":"NO","4"=>(($statusN&4)!=0)?"YES":"NO","8"=>(($statusN&8)!=0)?"YES":"NO"],"retval"=>$retval]);
        switch ($statusN) {
            case 3: if (!$esAdmin||!$esPago) return $PPP.$retval;
                return $PPP."$retval en Pago!";
            case 5: if ($esPago||$esTraslado) return $PPP.$retval;
                if ($isAdmPP) return $PPP."Exp s/CR";
                return $retval." sin Contra-Recibo";
            case 7: if (!$esAdmin||!$esPago) return $PPP.$retval;
                if ($isAdmPP) return $PPP."Exp en Pago c/CR!";
                return $retval." en Pago c/CR!";
            case 9: if ($esPago||$esTraslado) return $PPP.$retval;
                if ($isAdmPP) {
                    if ($esEgreso) return $PPP."Resp sin CR";
                    return $PPP."Resp sin CR ni Exp";
                }
                if ($esEgreso) "$retval sin Contra-Recibo";
                return $retval." sin CR ni Exp";
            case 11: if ($esTraslado||$esEgreso||($esCompras&&$esPago))
                    return $PPP.$retval;
                if ($esPago) return $PPP."Resp en Pago c/CR s/Exp!";
                return $PPP.$retval." sin Exportar";
            case 13: if ($esTraslado||($esPago&&$esCompras)) return $PPP.$retval;
                if ($esIngreso||$esCompras) {
                    if ($isAdmPP) return $PPP."Resp sin CR";
                    return $retval." sin Contra-Recibo";
                }
                if ($esPago) return $PPP."Resp en Pago c/Exp!";
                return $PPP."Resp en Egreso s/CR c/Exp!";
            case 15: return $PPP."Respaldado";
        }
        //doclog("END FUNC","test",["class"=>"Facturas","function"=>"statusnToRealStatus","statusn"=>$statusN, "tc"=>$tipoComprobante, "level"=>$level,"retval"=>$retval]);
    }
    public static function statusnToDetailStatus(&$statusN=null,$tipoComprobante="I") {
        if (!isset($statusN)||$statusN<0) return "Temporal";
        if ($statusN==0) return "Pendiente";
        if (in_array($statusN,[  1, 17],true)) return "Aceptado";
        if ($statusN%2==0) { $statusN=0; return "Pendiente"; }

        if (!isset($tipoComprobante[0])) $tipoComprobante="I";
        $tipoComprobante=strtoupper($tipoComprobante[0]);
        if (!in_array($tipoComprobante,["I","E","P","T"])) $tipoComprobante="I";
        
        if (in_array($statusN,[  3, 19],true)) return (($tipoComprobante==="T"||$tipoComprobante==="P")?"Aceptado":"Contrarrecibo");
        if (in_array($statusN,[  5, 21],true)) return ($tipoComprobante==="I"?"ExpSinContra":"Aceptado");
        if (in_array($statusN,[  7, 23],true)) return ($tipoComprobante==="I"?"Exportado":(($tipoComprobante==="P"||$tipoComprobante==="T")?"Aceptado":"Contrarrecibo"));
        if (in_array($statusN,[  9, 25],true)) return ($tipoComprobante==="I"?"RespSinCX":(($tipoComprobante==="P"||$tipoComprobante==="T")?"Respaldado":"RespSinContra"));
        if (in_array($statusN,[ 11, 27],true)) return ($tipoComprobante==="I"?"RespSinExp":"Respaldado");
        if (in_array($statusN,[ 13, 29],true)) return (($tipoComprobante==="P"||$tipoComprobante==="T")?"Respaldado":"RespSinContra");
        if (in_array($statusN,[ 15, 31],true)) return "Respaldado";
        if ($statusN<128) return "Pagado";
        return "Rechazado";
    }
    public static function estaPendiente($statusN) { return ($statusN<128) && !($statusN & 1); } // !estaAceptado && !estaRechazado
    public static function estaAceptado($statusN) { return ($statusN<128) && ($statusN & 1); }
    public static function estaContrarrecibo($statusN) { return ($statusN<128) && ($statusN & 2); }
    public static function estaExportado($statusN) { return ($statusN<128) && ($statusN & 4); }
    public static function estaRespaldado($statusN) { return ($statusN<128) && ($statusN & 8); }
    public static function estaProgPago($statusN) { return ($statusN<128) && ($statusN & 16); }
    public static function estaPagado($statusN) { return ($statusN<128) && ($statusN & 32); }
    public static function estaRecPago($statusN) { return ($statusN<128) && ($statusN & 64); }
    public static function estaRechazado($statusN) { return ($statusN>=128); /*($statusN & 128);*/ }
    public static function tieneSolicitud($factId) {
        global $solObj;
        if (!isset($solObj)) {
            require_once "clases/SolicitudPago.php";
            $solObj=new SolicitudPago();
        }
        return $solObj->exists("idFactura=$factId");
    }


    // status: Temporal, Pendiente, Aceptado, Rechazado, Contrarrecibo, ExpSinContra, RespSinCX, Exportado, RespSinExp, RespSinContra, Respaldado
    // accion: Procesar, Eliminar, GenerarCR, GenerarTxt, Respaldar
    function nextStatus($status,$accion,$tipoComprobante="I") {
        $tipoComprobante=strtoupper($tipoComprobante[0]);
        $esEgreso=($tipoComprobante==="E");
        $esPago=($tipoComprobante==="P");
        $esTraslado=($tipoComprobante==="T");
        if ($status==="Temporal") {
            if ($esPago||$esTraslado) return "Aceptado";
            return "Pendiente"; // null + 0 = 0
        }
        if ($status==="Pendiente") {
            if ($accion==="Aceptar") return "Aceptado"; // 0 + 1 = 1
            if ($accion==="Rechazar") return "Rechazado"; // 0 + 128 = 128
        } else if($status==="Aceptado") {
            if ($accion==="Contrarrecibo") return "Contrarrecibo"; // 1 + 2 = 3
            if ($accion==="Exportar") return "ExpSinContra"; // 1 + 4 = 5
            if ($accion==="Respaldar") {
                if ($esPago||$esTraslado) return "Respaldado";
                if ($esEgreso) return "RespSinContra";
                return "RespSinCX"; // 1 + 8 = 9
            }
            if($accion==="Rechazar") return "Rechazado"; // 1 + 128 = 129
        } else if($status==="Contrarrecibo") {
            if ($accion==="Exportar") return "Exportado"; // 3 + 4 = 7
            if ($accion==="Respaldar") {
                if ($esEgreso) return "Respaldado";
                return "RespSinExp"; // 3 + 8 = 11
            }
            if($accion==="Rechazar") return "Rechazado"; // 3 + 128 = 131
        } else if($status==="ExpSinContra") {
            if ($accion==="Contrarrecibo") return "Exportado"; // 5 + 2 = 7
            if ($accion==="Respaldar") return "RespSinContra"; // 5 + 8 = 13
            if($accion==="Rechazar") return "Rechazado"; // 5 + 128 = 133
        } else if($status==="RespSinCX") {
            if ($accion==="Contrarrecibo") return "RespSinExp"; // 9 + 2 = 11
            if ($accion==="Exportar") return "RespSinContra"; // 9 + 4 = 13
            if($accion==="Rechazar") return "Rechazado"; // 9 + 128 = 137
        } else if($status==="Exportado") {
            if ($accion==="Respaldar") return "Respaldado"; // 7 + 8 = 15
            if($accion==="Rechazar") return "Rechazado"; // 7 + 128 = 135
        } else if($status==="RespSinExp") {
            if ($accion==="Exportar") return "Respaldado"; // 11 + 4 = 15
            if($accion==="Rechazar") return "Rechazado"; // 11 + 128 = 139
        } else if($status==="RespSinContra") {
            if ($accion==="Contrarrecibo") return "Respaldado"; // 13 + 2 = 15
            if($accion==="Rechazar") return "Rechazado"; // 13 + 128 = 141
        }
        return $status;
    }
    function prevStatus($status,$accion,$tipoComprobante="I") {
        $tipoComprobante=strtoupper($tipoComprobante[0]);
        $esEgreso=($tipoComprobante==="E");
        $esPago=($tipoComprobante==="P");
        $esTraslado=($tipoComprobante==="T");
        if ($status=="Pendiente") return "Temporal"; // 0 - null = null
        else if($status=="Aceptado" && $accion==="Aceptar") {
            if ($esPago||$esTraslado) return "Temporal";
            return "Pendiente"; // 1 - 1 = 0
        } else if($status==="Contrarrecibo" && $accion==="Contrarrecibo") return "Aceptado"; // 3 - 2 = 1
        else if ($status==="ExpSinContra" && $accion==="Exportar") return "Aceptado"; // 5 - 4 = 1
        else if ($status==="RespSinCX" && $accion==="Respaldar") return "Aceptado"; // 9 - 8 = 1
        else if ($status==="Exportado" && $accion==="Exportar") return "Contrarrecibo"; // 7 - 4 = 3
        else if ($status==="RespSinExp" && $accion==="Respaldar") return "Contrarrecibo"; // 11 - 8 = 3
        else if ($status==="Exportado" && $accion==="Contrarrecibo") return "ExpSinContra"; // 7 - 2 = 5
        else if ($status==="RespSinContra" && $accion==="Respaldar") {
            if ($esEgreso) return "Aceptado";
            return "ExpSinContra"; // 13 - 8 = 5
        }
        else if ($status==="RespSinExp" && $accion==="Contrarrecibo") return "RespSinCX"; // 11 - 2 = 9
        else if ($status==="RespSinContra" && $accion==="Exportar") return "RespSinCX"; // 13 - 4 = 9
        else if ($status==="Respaldado" && $accion==="Respaldar") {
            if ($esEgreso) return "Contrarrecibo";
            if ($esPago||$esTraslado) return "Aceptado";
            return "Exportado"; // 15 - 8 = 7
        }
        else if ($status==="Respaldado" && $accion==="Exportar") return "RespSinExp"; // 15 - 4 = 11
        else if ($status==="Respaldado" && $accion==="Contrarrecibo") return "RespSinContra"; // 15 - 2 = 13
            
        return $status;
    }
    function getExportContent($ids) { // List of invoice IDs, comma separated
        $arr = export(",",$ids);
        $num = count($arr);
        if ($num<=0) return "";
        if ($num==1) $where = "id=$ids";
        else $where = "id in ($ids)";
        
        $this->rows_per_page=0;
        // require_once "clases/Conceptos.php";
        // $cptObj = new Conceptos();
        // $cptObj->rows_per_page  = 0;
        
        $fData = $this->getData($where);
        foreach($fData as $fRow) {
            if (strtoupper($fRow["tipoComprobante"][0])==="E") continue; // Ignorar Notas de Credito
            $fStatus = $fRow["status"];
            $fId = $fRow["id"];
            $xffolio = $fRow["folio"];
            if (!isset($xffolio[0])) {
                $xfuuid = $fRow["uuid"];
                if (isset($xfuuid[9])) $xffolio = substr($xfuuid, -10);
                else if (isset($xfuuid[0])) $xffolio = $xfuuid;
            } else if (isset($xffolio[10])) $xffolio = substr($xffolio, -10);
            $ffdt = DateTime::createFromFormat("Y-m-d H:i:s", $fRow["fechaFactura"]);
            $tipoCambio = $fRow["tipoCambio"];
            $intTCambio = intval($tipoCambio);
            //$tasa = $fRow["tasaIva"];
            //if (intval($tasa)==16) $tasa=1;          // Tipo de Tasa. 16% => 1
            //else $tasa=3;                            //              Otro => 3
            $tasa=1;                                   // Calculo deshabilitado, queda siempre en 1
        }
    }
    function recalcDueDate($prvCod, $creditDays) {
        $this->log.="// INI function recalcDueDate codigoProveedor=$prvCod credito=$creditDays\n";
        // busca todas las facturas con el codigo de proveedor y con status 
        // update facturas set fechaVencimiento=date(date_add(fechaAprobacion, interval 1 day))
        // where codigoproveedor="E-039" and tipocomprobante="i" and statusn>0 and statusn<16;
        if ($creditDays<=0) $dueDateScript="fechaCaptura";
        else {
            $baseCreditCount=$creditDays-($creditDays%7)+7;
            $dueDateScript = "date_add(fechaCaptura, interval ($baseCreditCount-case weekday(fechaCaptura) when 0 then 7 else weekday(fechaCaptura) end) DAY)";
        }
        $this->updateValue("fechaVencimiento", null, new DBExpression("date($dueDateScript)"), "codigoproveedor='$prvCod' and tipocomprobante='i' and statusn>0 and statusn<16");
        //$this->updateValue("fechaVencimiento", null, new DBExpression("date(date_add(fechaAprobacion, interval $creditDays day))"), "codigoproveedor='$prvCod' and tipocomprobante='i' and statusn>0 and statusn<16");
    }
    function renombraPDF($factId, $oldName, $newName, $ubicacion) {
        return $this->renombraArchivo(Facturas::CAMPO_NOMBRE_PDF, $factId, $oldName, $newName, $ubicacion);
    }
    function renombraXML($factId, $oldName, $newName, $ubicacion) {
        return $this->renombraArchivo(Facturas::CAMPO_NOMBRE_XML, $factId, $oldName, $newName, $ubicacion);
    }
    // TODO: Modificar metodo que reciba solo $factId, que consulte la base y modifique ambos archivos sólo en caso de ser necesario
    function renombraArchivo($fieldConstant, $factId, $oldName, $newName, $ubicacion) {
        global $query;
        if($fieldConstant===Facturas::CAMPO_NOMBRE_XML) { $fieldName="nombreInterno"; $ext="xml"; }
        else if($fieldConstant===Facturas::CAMPO_NOMBRE_PDF) { $fieldName="nombreInternoPDF"; $ext="pdf"; }
        $errMsg="";
        if (!isset($ext)) $errMsg="El tipo de archivo no es válido";
        else if ($factId==0) $errMsg="No se proporcionó un id de factura";
        else if (!isset($oldName[0])) $errMsg="No se proporcionó nombre de archivo actual";
        else if (!isset($newName[0])) $errMsg="No se proporcionó nuevo nombre de archivo";
        else if (!isset($ubicacion[0])) $errMsg="No se proporcionó ubicacion de archivos";
        else if (file_exists("../$ubicacion$oldName.$ext") && !file_exists("../$ubicacion$newName.$ext")) {
            if (!rename("../$ubicacion$oldName.$ext","../$ubicacion$newName.$ext")) $errMsg="No se pudo renombrar archivo $oldName.$ext a $newName.$ext";
        } else if (!file_exists("../$ubicacion$oldName.$ext") && !file_exists("../$ubicacion$newName.$ext")) $errMsg="No existen archivos $oldName.$ext ni $newName.$ext";
        //sessionInit();
        $fieldarray = ["id"=>$factId, $fieldName=>$newName];
        sessionInit();
        if (isset($errMsg[0])) {
            doclog("Ocurrió un error al auto renombrar facturas (id:{$factId}, {$ext}:{$oldName}->{$newName}): $errMsg","error");
            return $errMsg;
        }
        if ($this->saveRecord($fieldarray)) return TRUE;
        $errors="";
        foreach (DBi::$errors as $code => $text) {
            if (isset($errors[0])) $errors.=", ";
            $errors.="$code: $text";
        }
        if (!isset($errors[0])) return FALSE;
        //if (!isset($errors[0])) $errors="No se  ningun registro"; // No habia nada que modificar...
        doclog("Ocurrió un error al auto renombrar facturas (id:{$factId}, {$ext}:{$oldName}->{$newName}): $errors","error");
        return $errors;
    }
    function reparaXML($filename) {
        require_once "clases/CFDI.php";
        return CFDI::reparaXML(getBasePath().$filename);
    }
    function altaTempBlock($result,$message=null,$ubicacion=null,$xmlname=null,$pdfname=null,$id=null,$cfdiObj=null,$extra=[]) {
        $block=["result"=>$result];
        if (isset($message[0])) $block["message"]=$message;
        if (isset($ubicacion[0])) $block["ruta"]=$ubicacion; // ruta, xml, pdf
        if (isset($xmlname[0])) $block["xml"]=$xmlname;
        if (isset($pdfname[0])) $block["pdf"]=$pdfname;
        if (!empty($id)) $block["id"]=$id;
        if (isset($cfdiObj)) {
            $cacheFields=["idGrupo"=>"idgpo","aliasGrupo"=>"alias","idProveedor"=>"idprv","codigoProveedor"=>"codprv","infoProveedor"=>"infprv"];
            foreach ($cacheFields as $key => $blKey) {
                $cacheVal=$cfdiObj->cache[$key]??null;
                if (isset($cacheVal[0]) || (is_array($cacheVal) && isset(array_keys($cacheVal)[0]))) $block[$blKey]=$cacheVal;
                else {
                    if (!isset($block["missCache"]))
                        $block["missCache"]=[];
                    $block["missCache"][$key]=$cfdiObj->cache[$key];
                }
            }
            $block["cacheInfPrv"]=$cfdiObj->cache["infoProveedor"];
            $fields=["fecha">="fecha","version"=>"version","serie"=>"serie","folio"=>"folio","ea"=>"ea","forma_pago"=>"formaPago","subtotal"=>"subtotal","descuento"=>"descuento","tipocambio"=>"tipoCambio","moneda"=>"moneda","total"=>"total","tipo_comprobante"=>"tc","metodo_pago"=>"metodoPago","uuid"=>"uuid","totalimpuestosretenidos"=>"isr","totalimpuestostrasladados"=>"iva","conceptos"=>"conceptos"];
            foreach ($fields as $key=>$blKey) {
                if ($cfdiObj->has($key)) $block[$blKey]=$cfdiObj->get($key);
            }
            $block["epsilon"]=Facturas::EPSILON; //0.000001;
            // if (($total - $subtotal + $descuento + $impRetenido - $impTraslado) > $epsilon) anexaError("El monto total no coincide con subtotal - descuento + impuestos trasladados - impuestos retenidos");
        }
        if (!empty($extra)) foreach ($extra as $key => $value) {
            $block[$key]=$value;
        }
        return $block;
    }
    function altaTempError($errStk,$ubicacion,$xmlname,$pdfname,$cfdiObj,$extra=[]) {
        return $this->altaTempBlock("error",$errStk,$ubicacion,$xmlname,$pdfname,null,$cfdiObj,$extra);
    }
    function cargaConceptos($invId, $cfdiObj=null) {
        require_once("clases/Conceptos.php");
        $cptObj=new Conceptos();
        if ($cptObj->exists("idFactura=$invId")) throw new Exception("La factura ya tiene conceptos");
        $invData=$this->getData("id=$invId");
        if (!isset($invData[0])) throw new Exception("No se encontró la factura con id $invId");
        $invData=$invData[0];
        if ($cfdiObj==null) {
            $xmlName=$invData["nombreInterno"].".xml";
            $xmlPath=$invData["ubicacion"];
            $basepath = $_SERVER['HTTP_ORIGIN'].$_SERVER['WEB_MD_PATH'];
            $xmlFullName=$basepath.$xmlPath.$xmlName;
            require_once("clases/CFDI.php");
            $cfdiObj=CFDI::newInstanceByLocalName($xmlFullName);
            if ($cfdiObj==null) {
                $lastError=CFDI::getLastError();
                errlog("Error Message: ".$lastError["errorMessage"],"cfdi");
                errlog("Error Stack: ".$lastError["errorStack"],"cfdi");
                errlog("CFDI Log: ".$lastError["log"],"cfdi");
                throw new Exception($lastError["errorMessage"]);
            }
        }
        //$infprv=$cfdiObj->cache["infoProveedor"];
        $conceptos=$cfdiObj->get("conceptos");
        if (isset($conceptos["@claveprodserv"])) $conceptos=[$conceptos];
        foreach($conceptos as $cc) {
            if (isset($cc["@descripcion"][299])) $cc["@descripcion"]=substr($cc["@descripcion"], 0, 296)."...";
            doclog(json_encode($cc),"concepto");
            $ccA=["idFactura"=>$invId,"codigoArticulo"=>"","cantidad"=>$cc["@cantidad"],"descripcion"=>$cc["@descripcion"],"precioUnitario"=>$cc["@valorunitario"],"importe"=>$cc["@importe"],"version"=>"3.3","status"=>"activo"];
            $cu=$cc["@claveunidad"]??"";
            if (isset($cu[0])) $ccA["claveUnidad"]=$cu;
            $u=$cc["@unidad"]??"";
            if (isset($u[0])) $ccA["unidad"]=$u;
            else if (isset($cu[0])) {
                require_once "clases/catalogoSAT.php";
                $ccA["unidad"]=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD,"codigo",$cu,"nombre");
            }
            $cp=$cc["@claveprodserv"];
            if (isset($cp[0])) $ccA["claveProdServ"]=$cp;
            $d=$cc["@descuento"]??"";
            if (isset($d[0])) $ccA["importeDescuento"]=$d;
            $ccIs=$cc["Impuestos"]??null;
            if (isset($ccIs)) {
                $ccTs=$ccIs["Traslados"]??null;
                if (isset($ccTs)) {
                    $sumT=0;
                    foreach($ccTs as $cct) {
                        $sumT+=($cct["@importe"]??"0");
                    }
                    $ccA["impuestoTraslado"]=$sumT;
                }
                $ccRs=$ccIs["Retenciones"]??null;
                if (isset($ccRs)) {
                    $sumR=0;
                    foreach ($ccRs as $ccr) {
                        $sumR+=($ccr["@importe"]??"0");
                    }
                    $ccA["impuestoRetenido"]=$sumR;
                }
            }
            // TODO: si el proveedor tiene bandera "Tiene código en descripción" poner ultima palabra de la descripcion en codigoArticulo
            // TODO: si el proveedor tiene bandera "Es Servicio" consultar tabla de servicios para obtener el codigoArticulo indicando proveedor y claveprodserv
            // TODO: buscar archivo(s) donde se pasa status de Pendiente a Aceptado, si el proveedor tiene bandera de "Es Servicio" buscar por proveedor y claveprodserv: Si no la encuentra agregar instancia con el codigo capturado. Si la encuentra ignorar, pero mantener posibilidad de que si pueda reemplazar, por ejemplo, si termina con signo de exclamación '!'
            $cptObj->insertRecord($ccA);
        }
    }
    private function validaUsuario($data) {
        if (!validaPerfil(["Administrador","Sistemas"])) {
            if (!validaPerfil(["Compras","Alta Facturas"])) throw new Exception("No tiene permiso para dar de alta Facturas");
            global $ugObj,$perObj;
            if (!isset($ugObj)) {
                require_once "clases/Usuarios_grupo.php";
                $ugObj=new Usuarios_Grupo();
            }
            $idGrupo=$data["idGrupo"];
            $alias=$data["aliasGrupo"]??"Empresa $idGrupo";
            if(!$ugObj->isRelatedByPerfil(getUser(),$idGrupo,"Compras","vista")) throw new Exception("No tiene permiso para dar de alta facturas de $alias");
        }
    }
    function transferTries($filename, $filesize, $tries=10, $docname="altaMasiva") {
        for ($fsz=filesize($filename);$fsz<$filesize; $fsz=filesize($filename)) {
            $tries--;
            if ($tries<0) {
                if (file_exists($filename)) @unlink($filename);
                throw new Exception("La descarga ftp de '$filename' tardó demasiado");
            }
            doclog("TRANSFERENCIA LENTA",$docname,["ftpSize"=>$filesize, "localPath"=>$filename, "localSize"=>$fsz]);
            sleep(1);
        }
    }
    function resumenRecursivo($ftpObj, $path, $depth=0) {
        $list=$ftpObj->list($path, 1, false);
        $result="{$path} - ";
        if ($list===false) $result.="VACIO";
        else {
            $directories=[]; $files=[];
            foreach ($list as $line) {
                list ($date, $time, $size, $name) = preg_split("/[\s]+/", $line, 4);
                if ($size==="<DIR>") $directories[]=$name;
                else {
                    $date=str_replace("-", "/", $date);
                    $ext=substr($name, -3);
                    $tmstmp=strtotime("$date $time");
                    if (!isset($files[$ext])) {
                        $files[$ext]=["count"=>1];
                        if ($tmstmp!==false) {
                            $files[$ext]["mindate"]=$tmstmp;
                            $files[$ext]["maxdate"]=$tmstmp;
                        } else $files[$ext]["error"]="strtotime failed for '$date $time'";
                    } else {
                        $files[$ext]["count"]++;
                        if ($tmstmp!==false) {
                            if ($tmstmp<$files[$ext]["mindate"]) $files[$ext]["mindate"]=$tmstmp;
                            if ($tmstmp>$files[$ext]["maxdate"]) $files[$ext]["maxdate"]=$tmstmp;
                        }
                    }
                }
            }
            if (isset($directories[0])) $result.=count($directories)." carpeta".(isset($directories[1])?"s":"");
            if (isset(array_keys($files)[0])) {
                foreach ($files as $key => $block) {
                    $result.=" $block[count] $key";
                    if ($block["count"]!==1) $result.="s";
                    $hasMinDate=isset($block["mindate"]);
                    $hasMaxDate=isset($block["maxdate"]);
                    if ($hasMinDate) $result.=($hasMaxDate?" [":" | ").date("Y-m-d H:i",$block["mindate"]);
                    if ($hasMaxDate) $result.=($hasMinDate?" - ":" | ").date("Y-m-d H:i",$block["maxdate"]);
                    if ($hasMinDate && $hasMaxDate) $result.="]";
                    if (isset($block["error"])) $result.=" $block[error]";
                }
            } else if (!isset($directories[0])) $result.="VACIO";
            $maxDepth=3;
            if ($depth<$maxDepth && isset($directories[0])) {
                $result.="<OL>";
                foreach ($directories as $subdir) {
                    $result.="<LI>".$this->resumenRecursivo($ftpObj, "{$path}/{$subdir}", $depth+1)."</LI>";
                }
                $result.="</OL>";
            }
        }
        return $result;
    }
    function resumenDeAltaMasiva() {
        require_once "clases/Grupo.php";
        $gpoObj = new Grupo();
        $gpoObj->rows_per_page=0;
        $validRfcList=array_column($gpoObj->getData(false,0,"rfc"), "rfc");

        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceFacturas();
        if (!isset($ftpObj)) {
            doclog("FTP de 'facturas' no iniciado","error",["class"=>"Facturas","function"=>"resumenDeAltaMasiva","log"=>MIFTP::log(),"error"=>MIFTP::$lastException]);
            return ["title"=>"Error al intentar iniciar FTP", "data"=>[MIFTP::log()]];
            //throw new Exception("FTP no iniciado: ".MIFTP::log());
        }
        $fileList=$ftpObj->list("/", 1, false);
        if ($fileList===false) {
            doclog("Lista de archivos por FTP vacía","error",["class"=>"Facturas","function"=>"resumenDeAltaMasiva","log"=>MIFTP::log(),"error"=>MIFTP::$lastException]);
            return ["title"=>"La ruta base por FTP no tiene archivos", "data"=>[MIFTP::log()]];
            //throw new Exception("FTP no iniciado: ".MIFTP::log());
        }
        $data=[]; //["<B>SYSTYPE</B>: ".$ftpObj->systype(),"<B>VALID RFC</B>: ".implode(", ", $validRfcList)];
        $directories=[]; $files=[];
        foreach($fileList as $singleFile) {
            list($date, $time, $size, $name)=preg_split("/[\s]+/", $singleFile, 4);
            $name=trim($name);
            if ($name[0]==="/") $name=substr($name, 1);
            if ($size==="<DIR>") {
                if (in_array($name, $validRfcList)) {
                    $data[]=$this->resumenRecursivo($ftpObj,"/{$name}");
                } else {
                    $directories[]=$name;
                }
            } else {
                $ext=substr($name, -3);
                if (!isset($files[$ext])) $files[$ext]=1;
                else $files[$ext]++;
            }
        }
        $line="<p>/ ";
        if (isset($directories[0])) $line.=count($directories)." carpeta".(isset($directories[1])?"s":"").": ".implode(", ", $directories);
        if (isset(array_keys($files)[0])) {
            foreach ($files as $key => $value) {
                $line.=" $value $key";
                if ($value!==1) $line.="s";
            }
        }
        $line.="</p>";
        $data[]=$line;
        return ["title"=>"Elementos en Raíz", "data"=>$data];
    }
    function listaDeAltaMasiva($datePath=null) {
        $data=[];
        if (isset($datePath[0])) $datePath=trim($datePath);
        if (isset($datePath[0])) {
            if ($datePath[0]!=="/") $datePath="/".$datePath;
        } else $datePath="";
        require_once "clases/Grupo.php";
        $gpoObj = new Grupo();
        $gpoObj->rows_per_page=0;
        $validRfcList=array_column($gpoObj->getData(false,0,"concat('/',rfc) drfc"), "drfc");
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceFacturas();
        if (!isset($ftpObj)) {
            doclog("FTP de 'facturas' no iniciado","error",["class"=>"Facturas","function"=>"listaDeAltaMasiva","datePath"=>$datePath,"log"=>MIFTP::log(),"error"=>MIFTP::$lastException]);
            throw new Exception("FTP no iniciado: ".MIFTP::log());
        }
        $fileList=$ftpObj->list("/");
        $realRfcList = array_values(array_filter($fileList,function($el)use($validRfcList){return in_array($el, $validRfcList);}));
        doclog("INI listaDeAltaMasiva","altaMasiva",["datePath"=>$datePath, "rfcList"=>$realRfcList]);
        foreach ($realRfcList as $dirname) {
            $data=array_merge($data,$this->listaRecursiva($ftpObj, $dirname.$datePath));
        }
        return $data;
    }
    function listaRecursiva($ftpObj, $xmlPath) {
        $result=[];
        if (!isset($xmlPath[0])) return $result;
        $rawfiles=$ftpObj->list($xmlPath, 1, false);
        $badExt=[];
        if ($rawfiles!==false) foreach($rawfiles as $rawfile) {
            $info=preg_split("/[\s]+/", $rawfile, 4);
            $filename=$info[3];
            $extension=strtolower(substr($filename,-4));
            if ($info[2]==="<DIR>") {
                if ($filename!=="emitidas" && $filename!=="nomina") {
                    $subRes=$this->listaRecursiva($ftpObj, $xmlPath."/".$filename);
                    if (isset($subRes[0]))
                        $result=array_merge($result,$subRes);
                }
            } else if ($extension===".xml"||$extension===".pdf") {
                $info[]=$xmlPath;
                $result[]=$info;
            } else $badExt[]=$extension;
        } // else doclog();
        doclog("RES listaRecursiva","altaMasiva",["xmlPath"=>$xmlPath,"badext"=>$badExt, "rawLen"=>($rawfiles===false?-1:count($rawfiles)), "resLen"=>count($result)]);
        return $result;
    }
    function altaMasiva($datePath=null) {
        //doclog("INI altaMasiva","altaMasiva");
        if (isset($datePath[0])) $datePath=trim($datePath);
        if (isset($datePath[0])) {
            if ($datePath[0]!=="/") $datePath="/".$datePath;
        } else $datePath="";
        require_once "clases/Grupo.php";
        $gpoObj = new Grupo();
        $gpoObj->rows_per_page=0;
        $validRfcList=array_column($gpoObj->getData(false,0,"concat('/',rfc) drfc"), "drfc");
        require_once "clases/FTP.php";
        $ftpObj = MIFTP::newInstanceFacturas();
        if (!isset($ftpObj)) {
            doclog("FTP de 'facturas' no iniciado","error",["class"=>"Facturas","function"=>"altaMasiva","datePath"=>$datePath,"log"=>MIFTP::log(),"error"=>MIFTP::$lastException]);
            throw new Exception("FTP no iniciado: ".MIFTP::log());
        }
        $fileList=$ftpObj->list("/");
        $realRfcList = array_values(array_filter($fileList,function($el)use($validRfcList){return in_array($el, $validRfcList);}));
        $num=0;
        $dateTag=str_replace("/", "", $datePath);
        foreach ($realRfcList as $dirname) {
            $num+=$this->altaRecursiva($ftpObj, $dirname.$datePath, $dateTag);
        }
        doclog("FACTURAS DADAS DE ALTA SATISFACTORIAMENTE","altaMasiva",["num"=>$num, "list"=>$realRfcList, "dateTag"=>$dateTag]);
        return $num;
    }
    function altaRecursiva($ftpObj, $xmlPath, $dateTag="") {
        if (!isset($xmlPath[0])) {
            doclog("END altaRecursiva: sin XmlPath","altaMasiva",["dateTag"=>$dateTag]);
            return 0;
        }
        $rawfiles = $ftpObj->list($xmlPath,1,false);
        $sum=0;
        global $prcObj;
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj=new Proceso();
        }
        if (isset($rawfiles[0])) foreach($rawfiles as $rawfile) {
            $info = preg_split("/[\s]+/", $rawfile, 4);
            $filename=$info[3];
            if ($info[2]==="<DIR>") {
                if ($filename!=="emitidas" && $filename!=="nomina") {
                    $sum+=$this->altaRecursiva($ftpObj, $xmlPath."/".$filename, $dateTag);
                } else {
                    doclog("Carpeta ignorada","altaMasiva",["path"=>$rawfile]);
                }
            } else if (preg_match("/\.xml$/", $filename)) {
                $xmlFile=$xmlPath."/".$filename;
                $onlyName=substr($filename, 0, -4);
                $pdfFile=$xmlPath."/".$onlyName.".pdf";
                $usIdx=strpos($filename, "_");
                if ($usIdx!==false) $pdfFile2=$xmlPath."/".substr($filename, $usIdx+1, -4).substr($filename, 0, $usIdx).".pdf";
                //altaAutomatica($ftpObj, $xmlFile);
                // obtener del ftp el pdf si existe, con el mismo nombre que el xml
                // si el xml tiene cfdi, y si existe una factura en la base de datos, obtener el pdf con el texto guardado en el campo nombreInternoPDF
                try {
                    $block=$this->altaAutomatica($ftpObj, $xmlFile, $pdfFile, $pdfFile2??null);
                    if (isset($block["result"])) {
                        if ($block["result"]==="success") {
                            $prcObj->anotaAltaMasiva($block["id"], "ACCEPTED", "{$dateTag}|{$onlyName}", "SISTEMAS");
                            $sum++;
                        } else if ($block["result"]==="error") {
                            doclog("ALTA TEMPORAL CON ERROR","altaMasivaError",$block);
                            if (!isset($block["message"])) $block["message"]="SIN MENSAJE. CODIGO $block[code]";
                            global $query;
                            $prcObj->anotaAltaMasiva($block["id"]??null, "REJECTED", "{$dateTag}|{$onlyName}|$block[message]", "SISTEMAS");
                            doclog("ANOTA ALTA TEMPORAL EN PROCESO","altaMasivaError",["id"=>$block["id"]??null, "dateTag"=>$dateTag, "onlyName"=>$onlyName, "message"=>$block["message"], "query"=>$query, "error"=>DBi::getError(), "errno"=>DBi::getErrno()]);
                            if (!isset($block["code"]) || !in_array($block["code"], [static::ERROR_AUTO_NOCFDI, static::ERROR_AUTO_NOCFDI2, static::ERROR_AUTO_ERRCFDI, static::ERROR_AUTO_ERRCFDI2, static::ERROR_AUTO_NOCLI, static::ERROR_AUTO_NOPRV, static::ERROR_AUTO_NOPRV2, static::ERROR_AUTO_NOSRV, static::ERROR_AUTO_NOSAT, static::ERROR_AUTO_NOSAT2, static::ERROR_AUTO_SATN602, static::ERROR_AUTO_DB, static::ERROR_AUTO_UNKNOWN])) {
                                doclog("MOVER A REJECTED","altaMasivaError",["xml"=>$xmlFile,"pdf"=>$pdfFile??null,"code"=>$block["code"]??null]);
                                $xmlName=basename($xmlFile);
                                $ftpObj->moverArchivo($xmlFile, "/REJECTED/{$xmlName}", true);
                                $pdfSize=$ftpObj->size($pdfFile);
                                if ($pdfSize>0) {
                                    $pdfName=basename($pdfFile);
                                    $ftpObj->moverArchivo($xmlFile, "/REJECTED/{$pdfName}", true);
                                }
                                doclog("DONE","altaMasivaError");
                            } else {
                                $errcode=$block["code"];
                                $errtext="UNDEFINED";
                                switch($errcode) {
                                    case static::ERROR_AUTO_NOCFDI: $errtext="NOCFDI"; break;
                                    case static::ERROR_AUTO_NOCFDI2: $errtext="NOCFDI2"; break;
                                    case static::ERROR_AUTO_ERRCFDI: $errtext="ERRCFDI"; break;
                                    case static::ERROR_AUTO_ERRCFDI2: $errtext="ERRCFDI2"; break;
                                    case static::ERROR_AUTO_NOCLI: $errtext="NOCLI"; break;
                                    case static::ERROR_AUTO_NOPRV: $errtext="NOPRV"; break;
                                    case static::ERROR_AUTO_NOPRV2: $errtext="NOPRV2"; break;
                                    case static::ERROR_AUTO_NOSRV: $errtext="El proveedor no está habilitado como Servicio"; break;
                                    case static::ERROR_AUTO_NOSAT: $errtext="NOSAT"; break;
                                    case static::ERROR_AUTO_SATN602: $errtext="SATN602"; break;
                                    case static::ERROR_AUTO_DB: $errtext="DB"; break;
                                    case static::ERROR_AUTO_UNKNOWN: $errtext="UNKNOWN"; break;
                                }
                                doclog("CODIGO PARA REINTENTAR","altaMasivaError",["xml"=>$xmlFile,"pdf"=>$pdfFile??null,"errcode"=>$errcode,"errtxt"=>$errtext]);
                            }
                            // ToDo: Revisar si se prefiere que no se muevan los archivos cuando el proveedor no ha sido dado de alta o si no es servicio, para corregirlos y volver a subirlos
                        } else {
                            doclog("BLOQUE CON RESULTADO NO CONTEMPLADO","altaMasivaError",$block);
                            $prcObj->anotaAltaMasiva($block["id"]??null, "IGNORED", "{$dateTag}|{$onlyName}|$block[message]", "SISTEMAS");
                        }
                    } else {
                        doclog("ALTA TEMPORAL DESCONOCIDA","altaMasivaError",$block);
                        $prcObj->anotaAltaMasiva(null, "IGNORED", "{$dateTag}|{$onlyName}|".($block["message"]??"SIN PROCESAR"), "SISTEMAS");
                    }
                } catch (Exception $exR) {
                    $errblk=getErrorData($exR);
                    $errCode=$errblk["code"]??"";
                    if ($errCode==CFDI::EXCEPTION_VAL_UUID_EXISTS) {
                        //doclog("ALTA AUTOMATICA DESCARTADA","altaMasivaError",["ftpPath"=>$xmlPath,"name"=>$filename, "error"=>$errblk]);
                        $prcObj->anotaAltaMasiva(CFDI::getLastError()["id"], "DISCARDED", "{$dateTag}|{$onlyName}|$errblk[message]", "SISTEMAS");
                    } else {
                        doclog("ALTA AUTOMATICA FALLIDA","altaMasivaError",["ftpPath"=>$xmlPath,"name"=>$filename, "error"=>$errblk]);
                        if (!is_string($errCode)) $errCode="$errCode";
                        if ($errCode=="0") $errCode="";
                        else if (isset($errCode[0])) $errCode=" $errCode";
                        $errmsg=$errblk["message"];
                        $iptIdx=strpos($errmsg, "<input type");
                        if ($iptIdx!==false) {
                            $endIdx=strpos($errmsg, ">", $iptIdx);
                            if ($endIdx!==false) {
                                $errmsg=substr($errmsg, 0, $iptIdx).substr($errmsg, $endIdx+1);
                            }
                        }
                        $ptIdx=strpos($errmsg, ". .");
                        if ($ptIdx!==false) {
                            $errmsg=substr($errmsg, 0, $ptIdx).substr($errmsg, $ptIdx+3);
                        }
                        $prcObj->anotaAltaMasiva(null, "FAILED", "{$dateTag}|{$onlyName}|ERROR {$errCode}: $errmsg", "SISTEMAS");
                    }
                }
            }
        } else doclog("END altaRecursiva: sin rawfiles","altaMasiva",["xmlPath"=>$xmlPath,"dateTag"=>$dateTag]);
        return $sum;
    }
    function altaAutomatica($ftpObj, $xmlFile, $pdfFile, $pdfFile2=null) {
        $xmlSize=$ftpObj->size($xmlFile);
        if ($xmlSize<0) throw new Exception("El archivo XML no existe", static::ERROR_AUTO_NOXML); // Al parecer puede ser directorio, pero por código ya se está garantizando que no sea directorio
        $emptySize=0; // ToDo: Aumentar para contemplar archivos que solo tienen BOM y algunos espacios vacíos. ´También se puede construir un xml básico sin datos, con componentes requeridos y usarlo como tamaño base
        //@unlink($xmlLocalPath);
        if ($xmlSize<=$emptySize) {
            throw new Exception("El archivo XML no tiene información", static::ERROR_AUTO_EMPTY); // está vacío
        }
        $pdfSize=0;
        require_once "clases/Config.php";
        $tmpPath=(Config::get("project","sharePath")??"..\\")."tmp\\autoget\\";
        $xmlName=basename($xmlFile);
        $xmlLocalPath=$tmpPath.$xmlName;
        $pdfName=null;
        $pdfLocalPath=null;
        if (file_exists($xmlLocalPath)) {
            @unlink($xmlLocalPath);
            doclog("ELIMINA PREVIO ARCHIVO LOCAL","files",["local"=>$xmlLocalPath]);
        }
        $returnBlock=null;
        try {
            $ftpObj->obtenerArchivo($xmlFile,$xmlLocalPath);
            $this->transferTries($xmlLocalPath, $xmlSize);
            doclog("DESCARGA ARCHIVO XML","files",["remoto"=>$xmlFile, "local"=>$xmlLocalPath]);
            $pdfLocalPath=null;
            if (isset($pdfFile[0])) {
                $pdfSize=$ftpObj->size($pdfFile);
                if ($pdfSize>0) {
                    $pdfName=substr($xmlName, 0, -4).".pdf";
                    $pdfLocalPath=$tmpPath.$pdfName;
                    if (file_exists($pdfLocalPath)) {
                        @unlink($pdfLocalPath);
                        doclog("ELIMINA PREVIO ARCHIVO PDF LOCAL","files",["local"=>$pdfLocalPath]);
                    }
                    try {
                        $ftpObj->obtenerArchivo($pdfFile, $pdfLocalPath);
                        $this->transferTries($pdfLocalPath, $pdfSize);
                        doclog("DESCARGA ARCHIVO PDF","files",["remoto"=>$pdfFile, "local"=>$pdfLocalPath]);
                    } catch (Exception $ttex) {
                        doclog("FALLÓ LA DESCARGA DE PDF","altaMasivaError",["ftpPath"=>$pdfFile,"localPath"=>$pdfLocalPath,"error"=>getErrorData($ttex)]);
                        $pdfLocalPath=null;
                    }
                }
            }
            if (isset($pdfFile2[0])) {
                $pdfSize=$ftpObj->size($pdfFile2);
                if ($pdfSize>0) {
                    $pdfName=substr($xmlName, 0, -4).".pdf";
                    $pdfLocalPath=$tmpPath.$pdfName;
                    if (file_exists($pdfLocalPath)) {
                        @unlink($pdfLocalPath);
                        doclog("ELIMINA PREVIO ARCHIVO PDF LOCAL","files",["local"=>$pdfLocalPath,"step"=>"2"]);
                    }
                    try {
                        $ftpObj->obtenerArchivo($pdfFile2, $pdfLocalPath);
                        $this->transferTries($pdfLocalPath, $pdfSize);
                        doclog("DESCARGA ARCHIVO PDF2","files",["remoto"=>$pdfFile2, "local"=>$pdfLocalPath]);
                    } catch (Exception $ttex) {
                        doclog("FALLÓ LA DESCARGA DE PDF2","altaMasivaError",["ftpPath"=>$pdfFile2,"localPath"=>$pdfLocalPath,"error"=>getErrorData($ttex)]);
                        $pdfLocalPath=null;
                    }
                }
            }
            require_once "clases/CFDI.php";
            if (isset(CFDI::getLastError()["texto"][0])||isset(CFDI::getLastError()["exception"])) CFDI::clearLastError();
            $cfdiObj=CFDI::newInstanceByLocalName($xmlLocalPath);
            $cfdiLastError=CFDI::getLastError();
            if ($cfdiObj===null) {
                if (isset($cfdiLastError["texto"][0])) throw new Exception($cfdiLastError["texto"],$cfdiLastError["code"]??static::ERROR_AUTO_NOCFDI);
                if (isset($cfdiLastError["exception"])) throw $cfdiLastError["exception"];
                throw new Exception("NULL: ".json_encode($cfdiLastError),static::ERROR_AUTO_NOCFDI2);
            }
            $cfdiObj->cache["valmode"]="auto";
            $cfdiObj->validar();
            $cfdiLastError=CFDI::getLastError();
            if (isset($cfdiLastError["texto"][0])) throw new Exception($cfdiLastError["texto"],$cfdiLastError["code"]??static::ERROR_AUTO_ERRCFDI);
            if (isset($cfdiLastError["enough"]) && !$cfdiLastError["enough"]) {
                doclog("ERROR EN CFDI INDEFINIDO", "altaMasivaError",["lastError"=>$cfdiLastError]);
                throw new Exception($cfdiLastError["errorStack"]??"ERROR EN CFDI NO DEFINIDO",static::ERROR_AUTO_ERRCFDI2);
            }

            $tc=strtolower($cfdiObj->get("tipo_comprobante"));
            if ($tc!=="i") {
                switch($tc) {
                    case "e": $tc="Egreso"; break;
                    case "p": $tc="Complemento de Pago"; break;
                    case "t": $tc="Traslado"; break;
                }
                $returnBlock=$this->altaTempError("El tipo de comprobante '$tc' no es válido para alta masiva",null,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_BADTC]);
                return $returnBlock;
            }
            
            //$this->validaUsuario($cfdiObj->cache);

            $receptor=$cfdiObj->get("receptor");
            $rfcReceptor=$receptor["@rfc"];
            $usoCFDI=mb_strtoupper($receptor["@usocfdi"]??"");
            $idGrupo=$cfdiObj->cache["idGrupo"]??"";
            $alias=$cfdiObj->cache["aliasGrupo"]??"";
            if (!isset($alias[0])) {
                if (!CFDI::getLastError()["enough"]) throw new Exception(CFDI::getLastError()["errorStack"]);
                $returnBlock=$this->altaTempError("La empresa con RFC $rfcReceptor no está registrada",null,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_NOCLI]);
                return $returnBlock;
            }

            $fecha=$cfdiObj->get("fecha");
            $ubicacion=$this->getUbicacion($fecha, $alias, $tc);
            $emisor=$cfdiObj->get("emisor");
            $rfcEmisor=$emisor["@rfc"];
            $idProveedor=$cfdiObj->cache["idProveedor"]??"";
            $codigoProveedor=$cfdiObj->cache["codigoProveedor"]??"";
            $razonProveedor=strtoupper($cfdiObj->cache["razonProveedor"]??$cfdiObj->cache["nombreProveedor"]??$emisor["@nombre"]);
            if (!isset($codigoProveedor[0])) {
                if (!CFDI::getLastError()["enough"]) throw new Exception(CFDI::getLastError()["errorStack"],static::ERROR_AUTO_NOPRV);
                $returnBlock=$this->altaTempError("El proveedor $razonProveedor ($rfcEmisor) no está registrado",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_NOPRV2]);
                return $returnBlock;
            }
            $infoProveedor=$cfdiObj->cache["infoProveedor"]??["s"=>0];
            if (!($infoProveedor["s"]??0)) {
                $returnBlock=$this->altaTempError("El proveedor $codigoProveedor $razonProveedor ($rfcEmisor) no es Servicio",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_NOSRV]);
                return $returnBlock;
            }

            $ciclo=explode("/",$ubicacion)[2];
            $uuid=strtoupper($cfdiObj->get("uuid"));
            if (!isset($uuid[0])) {
                $returnBlock=$this->altaTempError("La factura no está timbrada",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_NOUUID]);
                return $returnBlock;
            }
            $rptaBD=$this->consultaBase($uuid);
            $rutaBase=$_SERVER['DOCUMENT_ROOT'];
            if ($rptaBD!==false && $rptaBD["status"]==="Temporal") { $id=$rptaBD["id"]; $statusn=$rptaBD["statusn"]??"";
                //if (isset($statusn[0])){$returnBlock=$this->altaTempError("La factura ya está registrada",$ubicacion,null,null,$cfdiObj); return $returnBlock;}
                global $solObj; if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
                $solData=$solObj->getData("idFactura=".$rptaBD["id"]);
                if (isset($solData[0])&&$solData[0]["status"]<SolicitudPago::STATUS_CANCELADA) {
                    $returnBlock=$this->altaTempError("Ya existe una solicitud con esta factura",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_HASSOL]);
                    return $returnBlock;
                }
                //if ($status==="Temporal") {
                    $this->deleteRecord(["id"=>$rptaBD["id"]]);
                    $xmlFullName=$rutaBase.$rptaBD["ubicacion"].$rptaBD["nombreInterno"].".xml";
                    $pdfFullName=isset($rptaBD["nombreInternoPDF"][0])?$rutaBase.$rptaBD["ubicacion"].$rptaBD["nombreInternoPDF"].".pdf":"";
                    if (isset($xmlFullName[0]) && file_exists($xmlFullName)) {
                        @unlink($xmlFullName);
                        doclog("ELIMINA XML LOCAL","files",["local"=>$xmlFullName]);
                    }
                    if (isset($pdfFullName[0]) && file_exists($pdfFullName)) {
                        @unlink($pdfFullName);
                        doclog("ELIMINA PDF LOCAL","files",["local"=>$pdfFullName]);
                    }
                    $rptaBD=false;
                    $xmlFullName=false;
                    $pdfFullName=false;
                //}
            }

            $folio=$cfdiObj->get("folio"); $serie=$cfdiObj->get("serie");
            if (isset($folio[0])) {
                if (isset($folio[50])) $folio=substr($folio,-50);
                if (preg_match('/[^a-zA-Z0-9\-\_]/', $folio)) {
                    $returnBlock=$this->altaTempError("El folio solo puede tener letras, numeros y guiones",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_BADFOIL]);
                    return $returnBlock;
                }
                if (isset($folio[10])) $fileid=substr($folio,-10);
                else $fileid=$folio;
            } else if (isset($uuid[9])) $fileid=substr($uuid,-10);
            else $fileid=$uuid;
            if (!isset($fileid[0])) throw new Exception("Factura sin folio ni uuid",static::ERROR_AUTO_NOFOIL);
            if (isset($fileid[10])) $fileid=substr($fileid, 10);
            if (isset($serie[50])) $serie=substr($serie,-50);

            $emisor=$cfdiObj->get("emisor");
            $rfcEmisor=$emisor["@rfc"];

            $xmlbdname=$rfcEmisor."_".$fileid;
            $xmlFullName=$rutaBase.$ubicacion.$xmlbdname.".xml";
            if (isset($pdfLocalPath[0])) {
                $pdfbdname=$fileid.$rfcEmisor;
                $pdfFullName=$rutaBase.$ubicacion.$pdfbdname.".pdf";
            }

            if ($rptaBD===false && file_exists($xmlFullName)) {
                $serieData=$this->getData("nombreInterno='$xmlbdname' and ubicacion='$ubicacion'",0,"id,serie,statusn");
                if (isset($serieData[0])) {
                    $serieData=$serieData[0];
                    $serieBD=$serieData["serie"];
                    $sst=$serieData["status"]??"";
                    $sstnn=$serieData["statusn"]??"";
                    if (isset($sstnn[0])) {
                        if (isset($folio[9])) {
                            $returnBlock=$this->altaTempError("Ya existe una factura con los mismos últimos 10 dígitos de folio",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_SAMEFOIL]);
                            return $returnBlock;
                        }
                        if ($serie===$serieBD) {
                            $returnBlock=$this->altaTempError("Ya existe una factura con los mismos folio, serie y proveedor",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_SAMEFOIL2]);
                            return $returnBlock;
                        }
                        $fileid=$serie.$folio;
                        if (isset($fileid[10])) $fileid=substr($fileid, -10);

                        $xmlbdname=$rfcEmisor."_".$fileid;
                        $xmlFullName=$rutaBase.$ubicacion.$xmlbdname.".xml";
                        if (isset($pdfLocalPath[0])) {
                            $pdfbdname=$fileid.$rfcEmisor;
                            $pdfFullName=$rutaBase.$ubicacion.$pdfbdname.".pdf";
                        }

                        $serieData=$this->getData("nombreInterno='$xmlbdname' and ubicacion='$ubicacion'",0,"id,status,statusn");
                        if (isset($serieData[0])) {
                            $serieData=$serieData[0];
                            $sst=$serieData["status"]??"";
                            $sstnn=$serieData["statusn"]??"";
                            if (isset($sstnn[0])) $this->altaTempError("Ya existe una factura con los mismos últimos 10 dígitos de serie-folio",$ubicacion,null,null,$cfdiObj);
                            global $solObj; if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
                            $solData=$solObj->getData("idFactura=".$rptaBD["id"]);
                            if (isset($solData[0])&&$solData[0]["status"]<SolicitudPago::STATUS_CANCELADA) {
                                $returnBlock=$this->altaTempError("Ya existe una solicitud para esta factura",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_HASSOL2]);
                                return $returnBlock;
                            }
                            if ($sst!=="Temporal") throw new Exception("Factura con status inválido",static::ERROR_AUTO_BADSTT);
                            $this->deleteRecord(["id"=>$rptaBD["id"]]);
                        }
                    }
                }
            }

            $total = $cfdiObj->get("total");
            if ($rptaBD===false||!isset($rptaBD["cfdi"])||$rptaBD["cfdi"][0]!=="S") {
                try {
                    $rptaSAT=$this->consultaServicio($rfcEmisor, $rfcReceptor, $total, $uuid);
                } catch (Exception $e) {
                    $returnBlock=$this->altaTempError("No se obtuvo respuesta del SAT",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_NOSAT]);
                    doclog("Error en consulta SAT","altaMasivaError",["data"=>$returnBlock,"error"=>getErrorData($e)]);
                    return $returnBlock;
                }
                if (!isset($rptaSAT)||!$rptaSAT) {
                    $returnBlock=$this->altaTempError("No se obtuvo respuesta del SAT",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_NOSAT2]);
                    return $returnBlock;
                }
                if (isset($rptaSAT["error"])) return $this->altaTempError($rptaSAT["mensaje"].": ".(isset($rptaSAT["errno"])?"(".$rptaSAT["errno"].") ":"").$rptaSAT["error"],$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_ERRSAT]);
            } else $rptaSAT=$rptaBD;
            $rptaCFDI=$rptaSAT["cfdi"];
            $rptaVigencia=$rptaSAT["estado"];
            $rptaCancelable=$rptaSAT["escancelable"]??"";
            $rptaCancelado=$rptaSAT["estatuscancelacion"]??"";
            if (!isset($rptaCFDI[0])) {
                $returnBlock=$this->altaTempError("No se obtuvo respuesta del SAT, reintente más tarde",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_ERRSAT1]);
                return $returnBlock;
            }
            if ($rptaCFDI==="N - 602: Comprobante no encontrado.") {
                $returnBlock=$this->altaTempError("El comprobante no se encuentra registrado en los controles del SAT",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_SATN602]);
                return $returnBlock;
            }
            if ($rptaCFDI[0]!=="S") {
                $returnBlock=$this->altaTempError("Comprobante del SAT no satisfactorio: $rptaCFDI",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_ERRSAT2]);
                return $returnBlock;
            }
            if ($rptaVigencia!=="Vigente") {
                $returnBlock=$this->altaTempError("Status del SAT no vigente: $rptaVigencia",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_SATOUT]);
                return $returnBlock;
            }
            $subtotal=$cfdiObj->get("subtotal");
            if ($cfdiObj->has("descuento"))
                $descuento=$cfdiObj->get("descuento");
            else $descuento=0;
            if ($cfdiObj->has("totalimpuestosretenidos"))
                $impRetenido=$cfdiObj->get("totalimpuestosretenidos");
            else $impRetenido=0;
            if ($cfdiObj->has("totalimpuestostrasladados"))
                $impTraslado=$cfdiObj->get("totalimpuestostrasladados");
            else $impTraslado=0;
            $version=$cfdiObj->get("version");
            if ($version==="3.3"||$version==="4.0") {
                if (!isset($epsilon)) $epsilon=Facturas::EPSILON; // 0.000001
                if (($total-$subtotal+$descuento+$impRetenido-$impTraslado)>$epsilon) {
                    $returnBlock=$this->altaTempError("El monto total no coincide con subtotal-descuento+impuestoTraslado-impuestoRetenido",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_BADCALC]);
                    return $returnBlock;
                }
            }

            $certificado=$cfdiObj->get("certificado");
            if (isset($certificado[50])) $certificado=substr($certificado,-50);
            if (isset($uuid[50])) $uuid=substr($uuid,-50);
            $traslado_tasa=$cfdiObj->get("traslado_tasa");
            if (is_array($traslado_tasa)&&isset($traslado_tasa[0])) $traslado_tasa=$traslado_tasa[0];
            $tipoCambio=$cfdiObj->get("tipocambio");
            if (empty($tipoCambio)) $tipoCambio="0";
            $ahora=date("Y-m-d H:i:s");
            $fieldarray = [
                "codigoProveedor"=>$codigoProveedor,
                "rfcGrupo"=>$rfcReceptor,
                "fechaFactura"=>$fecha,
                "fechaCaptura"=>$ahora,
                "uuid"=>$uuid,
                "noCertificado"=>$certificado,
                "importeDescuento"=>"$descuento",
                "impuestoTraslado"=>"$impTraslado",
                "impuestoRetenido"=>"$impRetenido",
                "subtotal"=>"$subtotal",
                "total"=>"$total",
                "tipoComprobante"=>$tc,
                "tipoCambio"=>$tipoCambio,
                "moneda"=>$cfdiObj->get("moneda"),
                "nombreOriginal"=>$xmlName,
                "nombreInterno"=>$xmlbdname,
                "ubicacion"=>$ubicacion,
                "ciclo"=>$ciclo,
                "version"=>$version,
                "status"=>"Pendiente",
                "statusn"=>0
            ];
            if (isset($usoCFDI[0])) $fieldarray["usoCFDI"]=$usoCFDI;
            if (isset($traslado_tasa[0])) $fieldarray["tasaIva"]=$traslado_tasa;
            if (isset($folio[0])) $fieldarray["folio"]=$folio;
            if (isset($serie[0])) $fieldarray["serie"]=$serie;
            if ($cfdiObj->has("metodo_pago")) {
                $mp=$cfdiObj->get("metodo_pago");
                if (isset($mp[50])) $mp=substr($mp,0,50);
                $fieldarray["metodoDePago"]=$mp;
            }
            if ($cfdiObj->has("forma_pago")) {
                $fp=$cfdiObj->get("forma_pago");
                if (isset($fp[50])) $fp=substr($fp,0,50);
                $fieldarray["formaDePago"]=$fp;
            }
            if (isset($rptaCFDI[0])) {
                if (isset($rptaCFDI[80])) $rptaCFDI=substr($rptaCFDI, 0, 80);
                $fieldarray["mensajeCFDI"] = $rptaCFDI;
                $fieldarray["consultaCFDI"] = $ahora;
            }
            if (isset($rptaVigencia[0])) {
                if (isset($rptaVigencia[30])) $rptaVigencia=substr($rptaVigencia, 0, 30);
                $fieldarray["estadoCFDI"] = $rptaVigencia;
            }
            if (isset($rptaCancelable[0]))
                $fieldarray["cancelableCFDI"] = $rptaCancelable;
            if (isset($rptaCancelado[0]))
                $fieldarray["canceladoCFDI"] = $rptaCancelado;
            if (isset($pdfbdname[0])) {
                $fieldarray["nombreInternoPDF"]=$pdfbdname;
                $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
                doclog("RENOMBRAR PDF", "pdf", $baseData+["line"=>__LINE__,"xml"=>$xmlbdname,"pdf"=>$pdfbdname]);
            }

            $saveResult=$this->saveRecord($fieldarray);
            if (!$saveResult) {
                $errParsed="";
                if (isset(DBi::$errors)) foreach(DBi::$errors as $sErn=>$sErr) {
                    $fixerror=DBi::getErrorTranslated($sErn, $sErr);
                    if (isset($fixerror[0])) {
                        if (isset($errParsed[0])) $errParsed.=", ";
                        $errParsed.=$sErn.":".$fixerror;
                    } else $errParsed.=$sErn.":".$sErr;
                }
                if (isset($errParsed[0])) {
                    $errStatus="REJECTED";
                    // ToDo: En base al error determinar si algun archivo debe ir a una carpeta diferente
                    $ftpObj->moverArchivo($xmlFile, "/{$errStatus}/{$xmlName}", true);
                    doclog("ALTA AUTOMATICA $errStatus XML","files",["initial"=>$xmlFile, "final"=>"/{$errStatus}/{$xmlName}"]);
                    if (isset($pdfFile2[0])) $pdfFile=$pdfFile2;
                    if (isset($pdfName[0]) && isset($pdfFile[0])) {
                        $ftpObj->moverArchivo($pdfFile, "/{$errStatus}/{$pdfName}", true);
                        doclog("ALTA AUTOMATICA $errStatus PDF","files",["initial"=>$pdfFile, "final"=>"/{$errStatus}/{$pdfName}"]);
                    }
                    $returnBlock=$this->altaTempError($errParsed,$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_DB]);
                    return $returnBlock;
                }
                $ftpObj->moverArchivo($xmlFile, "/REJECTED/{$xmlName}", true);
                doclog("ALTA AUTOMATICA REJECTED XML","files",["initial"=>$xmlFile, "final"=>"/REJECTED/{$xmlName}"]);
                if (isset($pdfFile2[0])) $pdfFile=$pdfFile2;
                if (isset($pdfName[0]) && isset($pdfFile[0])) {
                    $ftpObj->moverArchivo($pdfFile, "/REJECTED/{$pdfName}", true);
                    doclog("ALTA AUTOMATICA REJECTED PDF","files",["initial"=>$pdfFile, "final"=>"/REJECTED/{$pdfName}"]);
                }
                $returnBlock= $this->altaTempError("El comprobante no pudo guardarse",$ubicacion,null,null,$cfdiObj,["code"=>static::ERROR_AUTO_UNKNOWN]);
                return $returnBlock;
            }
            $invId = $this->lastId;
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioFactura($invId, "Pendiente", getUser()->nombre, $ahora, "AltaAutomatica");
            
            rename($xmlLocalPath, $xmlFullName);
            chmod($xmlFullName,0764);
            doclog("RENOMBRA XML LOCAL","files",["initial"=>$xmlLocalPath,"final"=>$xmlFullName]);
            if (isset($pdfbdname[0])) {
                rename($pdfLocalPath,$pdfFullName);
                chmod($pdfFullName,0764);
                doclog("RENOMBRA PDF LOCAL","files",["initial"=>$pdfLocalPath,"final"=>$pdfFullName]);
            }
            $this->cargaConceptos($invId, $cfdiObj);
            $ftpObj->borrarArchivo($xmlFile);
            if (isset($pdfFile2[0])) $pdfFile=$pdfFile2;
            if (isset($pdfName[0]) && isset($pdfFile[0])) {
                $ftpObj->borrarArchivo($pdfFile);
            }
            $returnBlock=$this->altaTempBlock("success",null,$ubicacion,$xmlbdname,$pdfbdname??null,$invId,$cfdiObj);
            return $returnBlock;
        } catch (Exception $x) {
            $xblk=getErrorData($x);
            doclog("ALTA AUTOMATICA FALLIDA","error",["xml"=>$xmlFile, "pdf"=>$pdfFile, "pdf2"=>$pdfFile2, "error"=>$xblk]);
            $xCod=$xblk["code"]??"";
            try {
                if ($xCod==CFDI::EXCEPTION_VAL_UUID_EXISTS) {
                    $ftpObj->borrarArchivo($xmlFile);
                    if (isset($pdfFile2[0])) {
                        $pdfFile=$pdfFile2;
                    }
                    if (isset($pdfName[0]) && isset($pdfFile[0])) {
                        $ftpObj->borrarArchivo($pdfFile);
                    }
                    $returnBlock= $this->altaTempError(CFDI::getExceptionMessage(CFDI::EXCEPTION_VAL_UUID_EXISTS),$ubicacion,null,null,$cfdiObj,["code"=>CFDI::EXCEPTION_VAL_UUID_EXISTS]);
                    return $returnBlock;
                } 
                $ftpObj->moverArchivo($xmlFile, "/FAILED/{$xmlName}", true);
                doclog("ALTA AUTOMATICA FAILED XML","files",["initial"=>$xmlFile, "final"=>"/FAILED/{$xmlName}"]);
                if (isset($pdfFile2[0])) $pdfFile=$pdfFile2;
                if (isset($pdfName[0]) && isset($pdfFile[0])) {
                    $ftpObj->moverArchivo($pdfFile, "/FAILED/{$pdfName}", true);
                    doclog("ALTA AUTOMATICA FAILED PDF","files",["initial"=>$pdfFile, "final"=>"/FAILED/{$pdfName}"]);
                }
            } catch (Exception $xx) {
                $raw = $ftpObj->list($xmlFile,1,false);
                doclog("ERROR DURANTE ACCIONES FALLIDAS","error",["xml"=>$xmlFile, "pdf"=>$pdfFile, "pdf2"=>$pdfFile2, "raw"=>$raw, "error2"=>getErrorData($xx)]);
                if ($xCod==CFDI::EXCEPTION_UNREGISTERED_PROVIDER) {
                    $returnBlock = $this->altaTempError(CFDI::getExceptionMessage(CFDI::EXCEPTION_UNREGISTERED_PROVIDER),null,null,null,$cfdiObj,["code"=>CFDI::EXCEPTION_UNREGISTERED_PROVIDER]);
                    return $returnBlock;
                }
            }
            throw $x;
        } finally {
            if (isset($xmlLocalPath[0]) && file_exists($xmlLocalPath)) {
                @unlink($xmlLocalPath);
                //doclog("FINALLY ELIMINA XML LOCAL","files",["initial"=>$xmlLocalPath]);
            }
            if (isset($pdfLocalPath[0]) && file_exists($pdfLocalPath)) {
                @unlink($pdfLocalPath);
                //doclog("FINALLY ELIMINA PDF LOCAL","files",["initial"=>$pdfLocalPath]);
            }
        }
    }
    function altaTemporal($xmlUpFile,$pdfUpFile=null,$vtc=null) {
        if (!isset($xmlUpFile)) throw new Exception("Debe indicar un archivo XML");
        require_once "clases/Archivos.php";
        $errMsg=Archivos::getUploadError($xmlUpFile);
        if (isset($errMsg[0])) throw new Exception($errMsg);
        require_once "clases/CFDI.php";
        $errMsg=""; $errStk=""; $isEnough=TRUE; $myLog="";
        $cfdiObj=CFDI::newInstanceByFileName($xmlUpFile["tmp_name"],$xmlUpFile["name"], $errMsg, $errStk, $isEnough, $myLog);
        if ($cfdiObj===null) {
            if (!isset($errMsg[0])) throw new Exception("Extracción de datos fallida");
            $stripErrMsg=trim(strip_tags($errMsg));
            doclog(CFDI::$lastException->getMessage()??"ERROR: $stripErrMsg","cfdi",["stack"=>$errStk,"log"=>$myLog]);
            if ($isEnough) throw new Exception("Documento XML no reconocido");
            if (!isset($stripErrMsg[0])) throw new Exception("El Comprobante Fiscal no se pudo validar");
            throw new Exception($stripErrMsg); // $errStk
        }
        $cfdiObj->validar($vtc);
        $stripErrMsg=isset($errMsg[0])?trim(strip_tags($errMsg)):"";
        $cfdiLastError=CFDI::getLastError();
        if (!$isEnough) {
            if (isset($cfdiLastError["code"]) && $cfdiLastError["code"]==CFDI::EXCEPTION_UNREGISTERED_PROVIDER)
                doclog("INVALIDO: $stripErrMsg","cfdi",["errMsg"=>$errMsg, "stack"=>$errStk, "lastError"=>$cfdiLastError]);
            else doclog("INVALIDO: $stripErrMsg","cfdi",["errMsg"=>$errMsg, "stack"=>$errStk, "lastError"=>$cfdiLastError, "log"=>$myLog]);
            if (isset($cfdiObj->cache["errors"][0])) {
                foreach ($cfdiObj->cache["errors"] as $idx => $err) {
                    //["message"=>"Ya está dado de alta en el sistema","name"=>"TimbreFiscalDigital.UUID","value"=>$uuid,"codigoProveedor"=>$invCodProv]
                    if (isset($err["message"]) && $err["message"]==="Ya está dado de alta en el sistema" && isset($err["name"]) && $err["name"]==="TimbreFiscalDigital.UUID" && isset($err["value"][0]))  return $this->altaTempError($err["message"],null,null,null,null,["existe"=>true,"uuid"=>$err["value"]]);
                }
                // ToDo: Cotejar contra error en CFDI(linea 1088)
                // Si existe y es porque ya existe el uuid, arrojar excepcion que incluya estos datos.
                // En Solicitud de Pago, si la excepcion tiene mensaje de error porque el uuid ya existe, usar el uuid para rastrear la factura.
            }
            if (!isset($stripErrMsg[0])) throw new Exception("Comprobante Fiscal inválido");
            throw new Exception($stripErrMsg);
        }
        $esDesarrollo = hasUser() && getUser()->nombre==="admin";
        if ($esDesarrollo && isset($cfdiLastError["validar"][0])) {
            doclog($cfdiLastError["validar"],"read");
            doclog($cfdiLastError["validar"],"cfdi");
        }
        $idGrupo=$cfdiObj->cache["idGrupo"]??"";
        $alias=$cfdiObj->cache["aliasGrupo"]??"";
        $idProveedor=$cfdiObj->cache["idProveedor"]??"";
        $codigoProveedor=$cfdiObj->cache["codigoProveedor"]??"";
        $uuid=strtoupper($cfdiObj->get("uuid"));

        try {
            $this->validaUsuario($cfdiObj->cache);
        } catch (Exception $ex) {
            doclog("Error en Alta Temporal","altaTemporal",["idPrv"=>$idProveedor,"codPrv"=>$codigoProveedor,"idGpo"=>$idGrupo,"alias"=>$alias,"uuid"=>$uuid]);
            throw $ex;
        }

        $tc=strtolower($cfdiObj->get("tipo_comprobante"));
        $tcNameList=["i"=>"Ingreso","e"=>"Egreso","p"=>"Pago","t"=>"Traslado"];
        $tcName=$tcNameList[$tc]??"Otro ($tc)";
        $tcAltNameList=["i"=>"La factura","e"=>"La nota","p"=>"El complemento de pago","t"=>"El traslado"];
        $tcAltName=$tcAltNameList[$tc]??"El comprobante fiscal($tc)";
        if ($tc=="i"||$tc="e") $tcSfx="a";
        else $tcSfx="o";
        if (isset($vtc[0])) {
            if (!is_array($vtc)) $vtc=[$vtc];
            if (!in_array($tc, $vtc)) {
                throw new Exception("El tipo de comprobante '$tcName' no es válido");
            }
        }
        $nombreOriginal=$xmlUpFile["name"];
        if (isset($nombreOriginal[100])) $nombreOriginal=substr($nombreOriginal,0,100);

        $fecha=$cfdiObj->get("fecha");
        $certificado=$cfdiObj->get("certificado");
        if (isset($certificado[50])) $certificado=substr($certificado, -50);
        $emisor=$cfdiObj->get("emisor");
        $receptor=$cfdiObj->get("receptor");
        $rfcEmisor=$emisor["@rfc"];
        $rfcReceptor=$receptor["@rfc"];
        $usoCFDI=mb_strtoupper($receptor["@usocfdi"]??"");

        if (!isset($alias[0])) {
            if (!$isEnough) throw new Exception($errStk);
            else return $this->altaTempError("La empresa con RFC $rfcReceptor no está registrada",null,null,null,$cfdiObj);
        }
        //DBi::query("SELECT 'Ubicacion = $fecha $idGrupo:$alias $tc' as test");
        $ubicacion=$this->getUbicacion($fecha, $alias, $tc);
        $ciclo=explode("/",$ubicacion)[2];
        if (!isset($uuid[0])) return $this->altaTempError($tcAltName." no está timbrad".$tcSfx,$ubicacion,null,null,$cfdiObj); // throw new Exception("El comprobante no está timbrado");
        if (isset($uuid[50])) $uuid=substr($uuid,-50);
        $folio=$cfdiObj->get("folio");
        $serie=$cfdiObj->get("serie");
        if (isset($folio[0])) {
            if (isset($folio[50])) $folio=substr($folio,-50);
            if (preg_match('/[^a-zA-Z0-9\-\_]/', $folio)) return $this->altaTempError("El folio solo puede tener letras, numeros y guiones",$ubicacion,null,null,$cfdiObj); // throw new Exception("El folio solo puede tener letras, numeros y guiones");
            if (isset($folio[10])) $fileid=substr($folio,-10);
            else $fileid=$folio;
        } else if (isset($uuid[9])) $fileid=substr($uuid,-10);
        else $fileid=$uuid;
        if (isset($serie[50])) $serie=substr($serie, -50);
        //DBi::query("SELECT 'EMISOR $rfcEmisor ".$emisor["@nombre"]."' as test");
        //DBi::query("SELECT 'RECEPTOR $rfcReceptor ".$receptor["@nombre"]."' as test");
        if (!isset($codigoProveedor[0])) {
            if (!$isEnough) throw new Exception($errStk);
            else return $this->altaTempError("El proveedor con RFC $rfcEmisor no está registrado",$ubicacion,null,null,$cfdiObj);
        }

        // REPLICANDO EN ALTA AUTOMATICA
        $xmlname=$rfcEmisor."_".$fileid;
        if (isset($pdfUpFile["tmp_name"])) {
            // Verificar que existe el archivo
            if (!file_exists($pdfUpFile["tmp_name"])) {
                // Registro de errores
                doclog("Error en Alta Temporal PDF","altaTemporal",["error"=>"El archivo temporal ya no existe","file"=>$pdfUpFile,"wouldBePdfName"=>$fileid.$rfcEmisor]);
                // Manejo del error
                return $this->altaTempError("El archivo PDF no es válido",$ubicacion, null, null, $cfdiObj);
            }
            $pdfname=$fileid.$rfcEmisor;
        }

        // Validar existencia
        $rutaBase=$_SERVER['DOCUMENT_ROOT'];
        //DBi::query("SELECT 'EXISTCHK $rutaBase$ubicacion{$xmlname}.xml' as test");
        if (file_exists($rutaBase.$ubicacion.$xmlname.".xml")) {
            //DBi::query("SELECT 'EXISTS1' as test");
            $rptaBD = $this->consultaBase($uuid);
            //DBi::query("SELECT 'RPTACHK1' as test");
            if ($rptaBD) {
                global $solObj;
                if (!isset($solObj)) {
                    require_once "clases/SolicitudPago.php";
                    $solObj=new SolicitudPago();
                }
                $solData=$solObj->getData("idFactura=".$rptaBD["id"]);
                if (isset($solData[0])) return $this->altaTempError("Ya existe una solicitud para esta factura",$ubicacion,null,null,$cfdiObj);
                if ($rptaBD["status"]==="Temporal") {
                    $this->deleteRecord(["id"=>$rptaBD["id"]]);
                } else {
                    return $this->altaTempError("$tcAltName ya está Registrad$tcSfx",$ubicacion,$xmlname,$pdfname??null,$cfdiObj,["allow"=>"solpago","status"=>$rptaBD["status"],"statusn"=>$rptaBD["statusn"],"id"=>$rptaBD["id"]]); // throw new Exception("$tcn ya está registrad$g en el sistema");
                }
            }
            //DBi::query("SELECT 'DONE1' as test");
            $serieData=$this->getData("nombreInterno='$xmlname' and ubicacion='$ubicacion'",0,"id,serie,status");
            if (isset($serieData[0])) {
                //DBi::query("SELECT 'SERIEDATA1' as test");
                if ($serieData[0]["status"]==="Temporal") {
                    // Checar si falta eliminar de SolicitudPago
                    $this->deleteRecord(["id"=>$serieData[0]["id"]]);
                    // ToDo: Marcar error si no se pudo borrar
                } else {
                    $serieBD=$serieData[0]["serie"];
                    if ($serie===$serieBD || !isset($serieBD[0])) return $this->altaTempError("Ya existe una factura con ese folio y proveedor",$ubicacion,null,null,$cfdiObj); // throw new Exception("Ya existe una factura con ese folio y proveedor");
                    if (isset($folio[9])) return $this->altaTempError("Ya existe una factura con los mismos últimos 10 dígitos de folio",$ubicacion,null,null,$cfdiObj); // throw new Exception("Ya existe una factura con los mismos últimos 10 dígitos de folio");
                    $fileid=$serie.$folio;
                    if (isset($fileid[10]))
                        $fileid=substr($fileid, -10);
                    $xmlname=$rfcEmisor."_".$fileid;
                    if (isset($pdfname[0])) $pdfname=$fileid.$rfcEmisor;
                    $serieData=$this->getData("nombreInterno='$xmlname' and ubicacion='$ubicacion'",0,"id,status");
                    if (isset($serieData[0])) {
                        if ($serieData[0]["status"]==="Temporal") {
                            // Checar si falta eliminar de SolicitudPago
                            $this->deleteRecord(["id"=>$serieData[0]["id"]]);
                            // ToDo: Marcar error si no se pudo borrar
                        } else return $this->altaTempError("Ya existe una factura con los mismos últimos 10 dígitos de serie-folio",$ubicacion,null,null,$cfdiObj); // throw new Exception("Ya existe una factura con los mismos últimos 10 dígitos de serie-folio");
                    }
                }
                //DBi::query("SELECT 'DONE2' as test");
            }
        } //else DBi::query("SELECT 'NO!' as test");
        if (!$isEnough) return $this->altaTempError($errStk,$ubicacion,$xmlname,$pdfname,$cfdiObj); // $fecha,$idGrupo,$alias,$idProveedor,$codigoProveedor,$tc
        if (isset($vtc[0])) {
            if (!is_array($vtc)) $vtc=[$vtc];
            if (!in_array($tc, $vtc)) return $this->altaTempError("El tipo de comprobante no es válido",$ubicacion,$xmlname,$pdfname,$cfdiObj);
        }
        //DBi::query("SELECT 'DONE3' as test");
        $total = $cfdiObj->get("total");
        if (!isset($rptaBD)||!$rptaBD||$rptaBD["cfdi"][0]!=="S") {
            try {
                $rptaSAT=$this->consultaServicio($rfcEmisor, $rfcReceptor, $total, $uuid);
            } catch (Exception $e) {
                return $this->altaTempError("No se obtuvo respuesta del SAT",$ubicacion,null,null,$cfdiObj); // throw new Exception("No se obtuvo respuesta del SAT");
            }
            if (!isset($rptaSAT)||!$rptaSAT) return $this->altaTempError("No se obtuvo respuesta del SAT",$ubicacion,null,null,$cfdiObj); // throw new Exception("No se obtuvo respuesta del SAT");
            if (isset($rptaSAT["error"])) return $this->altaTempError($rptaSAT["mensaje"].": ".(isset($rptaSAT["errno"])?"(".$rptaSAT["errno"].") ":"").$rptaSAT["error"],$ubicacion,null,null,$cfdiObj); // throw new Exception($rptaSAT["mensaje"].": ".$rptaSAT["error"]);
        } else $rptaSAT=$rptaBD;
        //DBi::query("SELECT 'DONE4' as test");
        $rptaCFDI=$rptaSAT["cfdi"];
        $rptaVigencia=$rptaSAT["estado"];
        $rptaCancelable=$rptaSAT["escancelable"]??"";
        $rptaCancelado=$rptaSAT["estatuscancelacion"]??"";
        if ($rptaCFDI==="N - 602: Comprobante no encontrado.") return $this->altaTempError("El comprobante no se encuentra registrado en los controles del SAT",$ubicacion,null,null,$cfdiObj); // throw new Exception("El comprobante no se encuentra registrado en los controles del SAT");
        if (!isset($rptaCFDI[0]) || $rptaCFDI[0]!=="S") return $this->altaTempError("Comprobante del SAT no satisfactorio: $rptaCFDI",$ubicacion,null,null,$cfdiObj); // throw new Exception("Comprobante del SAT no satisfactorio: $rptaCFDI");
        if ($rptaVigencia!=="Vigente") return $this->altaTempError("Status del SAT no vigente: $rptaVigencia",$ubicacion,null,null,$cfdiObj); // throw new Exception("Status del SAT no vigente: $rptaVigencia");

        $subtotal=$cfdiObj->get("subtotal");
        if ($cfdiObj->has("descuento"))
            $descuento=$cfdiObj->get("descuento");
        else $descuento=0;
        if ($cfdiObj->has("totalimpuestosretenidos"))
            $impRetenido=$cfdiObj->get("totalimpuestosretenidos");
        else $impRetenido=0;
        if ($cfdiObj->has("totalimpuestostrasladados"))
            $impTraslado=$cfdiObj->get("totalimpuestostrasladados");
        else $impTraslado=0;
        $version=$cfdiObj->get("version");
        if ($version==="3.3") {
            if (!isset($epsilon)) $epsilon=Facturas::EPSILON; // 0.000001
            if (($total-$subtotal+$descuento+$impRetenido-$impTraslado)>$epsilon) return $this->altaTempError("El monto total no coincide con subtotal-descuento+impuestoTraslado-impuestoRetenido",$ubicacion,null,null,$cfdiObj); // throw new Exception("El monto total no coincide con subtotal-descuento+impuestoTraslado-impuestoRetenido");
        }
        if ($tc==="p") {
            // verifica pago_doctos
        }
        $traslado_tasa=$cfdiObj->get("traslado_tasa");
        if (is_array($traslado_tasa)&&isset($traslado_tasa[0])) $traslado_tasa=$traslado_tasa[0];
        $tipoCambio=$cfdiObj->get("tipocambio");
        if (empty($tipoCambio)) $tipoCambio="0";
        $ahora=date("Y-m-d H:i:s");
        //DBi::query("SELECT 'DONE5' as test");
        $fieldarray = [
            "codigoProveedor"=>$codigoProveedor,
            "rfcGrupo"=>$rfcReceptor,
            "fechaFactura"=>$fecha,
            "fechaCaptura"=>$ahora,
            "uuid"=>$uuid,
            "noCertificado"=>$certificado,
            "importeDescuento"=>"$descuento",
            "impuestoTraslado"=>"$impTraslado",
            "impuestoRetenido"=>"$impRetenido",
            "subtotal"=>"$subtotal",
            "total"=>"$total",
            "tipoComprobante"=>$tc,
            "tipoCambio"=>$tipoCambio,
            "moneda"=>$cfdiObj->get("moneda"),
            "nombreOriginal"=>$nombreOriginal,
            "nombreInterno"=>$xmlname,
            "ubicacion"=>$ubicacion,
            "ciclo"=>$ciclo,
            "version"=>$version,
            "status"=>"Temporal"
        ];
        if (isset($usoCFDI[0])) $fieldarray["usoCFDI"]=$usoCFDI;
        if (isset($traslado_tasa[0])) $fieldarray["tasaIva"]=$traslado_tasa;
        if (isset($folio[0])) $fieldarray["folio"]=$folio;
        if (isset($serie[0])) $fieldarray["serie"]=$serie;
        if ($cfdiObj->has("metodo_pago")) {
            $mp=$cfdiObj->get("metodo_pago");
            if (isset($mp[50])) $mp=substr($mp,0,50);
            $fieldarray["metodoDePago"]=$mp;
        }
        if ($cfdiObj->has("forma_pago")) {
            $fp=$cfdiObj->get("forma_pago");
            if (isset($fp[50])) $fp=substr($fp,0,50);
            $fieldarray["formaDePago"]=$fp;
        }
        if (isset($rptaCFDI[0])) {
            if (isset($rptaCFDI[80])) $rptaCFDI=substr($rptaCFDI, 0, 80);
            $fieldarray["mensajeCFDI"] = $rptaCFDI;
            $fieldarray["consultaCFDI"] = $ahora;
        }
        if (isset($rptaVigencia[0])) {
            if (isset($rptaVigencia[30])) $rptaVigencia=substr($rptaVigencia, 0, 30);
            $fieldarray["estadoCFDI"] = $rptaVigencia;
        }
        if (isset($rptaCancelable[0]))
            $fieldarray["cancelableCFDI"] = $rptaCancelable;
        if (isset($rptaCancelado[0]))
            $fieldarray["canceladoCFDI"] = $rptaCancelado;
        if (isset($pdfname[0])) {
            $fieldarray["nombreInternoPDF"]=$pdfname;
            $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
            doclog("RENOMBRAR PDF", "pdf", $baseData+["line"=>__LINE__,"xml"=>$xmlname,"pdf"=>$pdfname]);
        }
        //DBi::query("SELECT 'DONE6' as test");
        $saveResult=$this->saveRecord($fieldarray);
        if (!$saveResult) {
            $errParsed="";
            if (isset(DBi::$errors)) foreach(DBi::$errors as $sErn=>$sErr) {
                $fixerror=DBi::getErrorTranslated($sErn, $sErr);
                if (isset($fixerror[0])) {
                    if (isset($errParsed[0])) $errParsed.=", ";
                    $errParsed.=$sErn.":".$fixerror;
                } else $errParsed.=$sErn.":".$sErr;
            }
            if (isset($errParsed[0])) return $this->altaTempError($errParsed,$ubicacion,null,null,$cfdiObj); // throw new Exception($errParsed);
            return $this->altaTempError("El comprobante no pudo guardarse",$ubicacion,null,null,$cfdiObj); // throw new Exception("El comprobante no pudo guardarse");
        }
        //DBi::query("SELECT 'DONE7' as test");
        global $prcObj;
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj=new Proceso();
        }
        $prcObj->cambioFactura($this->lastId, "Temporal", getUser()->nombre, $ahora, "altafacturaSP");

        // Verificar y establecer permisos de directorio
        $rutaDeCarga=$rutaBase.$ubicacion;
        $directoryPermissions = 0777; // Ajusta los permisos según tus necesidades
        if (!file_exists($rutaDeCarga)) {
            if (!mkdir($rutaDeCarga, $directoryPermissions, true)) {
                // Registro de errores
                doclog("Alerta en Alta Temporal","altaTemporal",["invId"=>$this->lastId,"uploadPath"=>$rutaDeCarga,"error"=>"Falló la creación de la ruta de carga de archivos"]);
            }
        } else {
            if (!chmod($rutaDeCarga, $directoryPermissions)) {
                // Registro de errores
                doclog("Alerta en Alta Temporal","altaTemporal",["invId"=>$this->lastId,"uploadPath"=>$rutaDeCarga,"error"=>"Falló la asignación de permisos en la ruta de carga de archivos"]);
            }
        }

        // Mover archivo XML
        $newXmlFullName=$rutaDeCarga.$xmlname.".xml";
        if(!move_uploaded_file($xmlUpFile["tmp_name"],$newXmlFullName)) {
            $lastError=error_get_last();
            if (is_null($lastError)) $lastError="null";
            // Registro de errores
            doclog("Error en Alta Temporal XML","altaTemporal",["invId"=>$this->lastId,"xmlname"=>$newXmlFullName,"error"=>$lastError,"file"=>$xmlUpFile]);
            // Manejo del error
            return $this->altaTempError("No fue posible guardar el documento XML",$ubicacion, null, null, $cfdiObj);
        }
        // Establecer permisos del archivo
        if (!chmod($newXmlFullName,0666)) {
            // Registro de errores
            doclog("Alerta en Alta Temporal XML","altaTemporal",["invId"=>$this->lastId,"xmlname"=>$newXmlFullName,"error"=>"Falló la asignación de permisos del archivo XML"]);
        }

        if (isset($pdfname[0])) {
            // Nuevo Nombre de archivo PDF determinado por datos, se asigna extensión PDF directamente
            $newPdfFullName=$rutaDeCarga.$pdfname.".pdf";

            // Verificar tamaño máximo del archivo
            $maxPDFFileSize = 2097152; // 2 MB
            if ($pdfUpFile["size"]>$maxPDFFileSize) {
                // Registro de errores
                doclog("Error en Alta Temporal PDF","altaTemporal",["invId"=>$this->lastId,"pdfname"=>$newPdfFullName,"error"=>"El tamaño del archivo excede el límite permitido","file"=>$pdfUpFile]);
                // Manejo del error
                return $this->altaTempError("El tamaño del archivo excede el límite permitido",$ubicacion, null, null, $cfdiObj);
            }

            // Verificar que el tipo de archivo sea PDF
            if ($pdfUpFile["type"]!=="application/pdf") {
                // Registro de errores
                doclog("Error en Alta Temporal PDF","altaTemporal",["invId"=>$this->lastId,"pdfname"=>$newPdfFullName,"error"=>"El tipo de archivo no es válido","file"=>$pdfUpFile]);
                // Manejo del error
                return $this->altaTempError("El tipo de archivo no es válido",$ubicacion, null, null, $cfdiObj);
            }

            // Verificar que existe el archivo
            if (!file_exists($pdfUpFile["tmp_name"])) {
                // Registro de errores
                doclog("Error en Alta Temporal PDF","altaTemporal",["invId"=>$this->lastId,"pdfname"=>$newPdfFullName,"error"=>"El archivo temporal ya no existe","file"=>$pdfUpFile]);
                // Manejo del error
                return $this->altaTempError("El archivo PDF no es válido",$ubicacion, null, null, $cfdiObj);
            }

            // Mover archivo PDF
            if (isset($pdfUpFile["fixed"])) {
                if (!rename($pdfUpFile["tmp_name"],$newPdfFullName)) {
                    // Registro de errores
                    $err=error_get_last();
                    if (is_null($err)) $err="null";
                    doclog("No se pudo renombrar archivo PDF","altaTemporal",["invId"=>$this->lastId,"pdfname"=>$newPdfFullName,"error"=>$err,"file"=>$pdfUpFile]);

                    // Manejo del error
                    return $this->altaTempError("No fue posible guardar el documento PDF",$ubicacion, null, null, $cfdiObj);
                }
            } else if(!move_uploaded_file($pdfUpFile["tmp_name"],$newPdfFullName)) {
                $err=error_get_last();
                if (is_null($err)) $err="null";
                // Registro de errores
                doclog("Error en Alta Temporal PDF (move_uploaded_file)","altaTemporal",["invId"=>$this->lastId,"pdfname"=>$newPdfFullName,"error"=>$err,"file"=>$pdfUpFile]);

                // Manejo del error
                return $this->altaTempError("No fue posible guardar el documento PDF",$ubicacion, null, null, $cfdiObj);
            }
            // Establecer permisos del archivo PDF
            if (!chmod($newPdfFullName,0666)) {
                doclog("Alerta en Alta Temporal PDF","altaTemporal",["invId"=>$this->lastId,"pdfname"=>$newPdfFullName,"error"=>"Falló la asignación de permisos al archivo PDF"]);
            }
        }
        return $this->altaTempBlock("success",null,$ubicacion,$xmlname,$pdfname??null,$this->lastId,$cfdiObj); // return $retval;
    }
    function consultaServicio($rfcE, $rfcR, $total, $uuid) {
        //$rfcE = utf8_encode($rfcE); //str_replace("&", "&amp;", $rfcE);
        $rfcE = str_replace("&","&amp;",$rfcE);
        //$rfcR = utf8_encode($rfcR); //str_replace("&", "&amp;", $rfcR);
        $rfcR = str_replace("&","&amp;",$rfcR);
        $total = sprintf("%.6f", (double)$total);
        $total = str_pad($total, 17, "0", STR_PAD_LEFT);
        $ttDotIdx = strpos($total, ".");
        if ($ttDotIdx!==false) {
            $totIntStr = ltrim(substr($total, 0, $ttDotIdx+1),"0");
            if ($totIntStr===".") $totIntStr="0.";
            $totDecStr = rtrim(substr($total, $ttDotIdx+1),"0");
            if ($totDecStr==="") $totDecStr="0";
            $total = $totIntStr.$totDecStr;
        }
        //$total = str_pad(sprintf("%.6f", (double)$row["total"]), 17, "0", STR_PAD_LEFT);
        $qr="?re=$rfcE&rr=$rfcR&tt=$total&id=$uuid";

        require_once "clases/CFDI.php";
        if ($rfcR===CFDI::RFCDEMO) {
            return ["expresionImpresa"=>$qr, "cfdi"=>"S - Comprobante obtenido satisfactoriamente.", "estado"=>"Vigente", "escancelable"=>"No Cancelable"];
        }
        require_once("configuracion/cfdi.php");
        $factura = valida_en_sat($qr);
        return $factura;
    }
    function consultaBase($uuid) {
        $result=$this->getData("uuid='$uuid'",0,"id,mensajeCFDI cfdi, estadoCFDI estado, ubicacion, nombreInterno, nombreInternoPDF, status, statusn");
        if (isset($result[0]["id"])) return $result[0];
        return false;
    }
    function getUbicacion($fecha,$alias,$tc) {
        if (!isset($fecha[0])) throw new Exception("No se identifica la fecha de la factura");
        if (!isset($alias[0])) throw new Exception("No se ubica alias de empresa receptora");
        $fdate=DateTime::createFromFormat('Y-m-d\TH:i:s', $fecha);
        if ($fdate===false) throw new Exception("La fecha de factura no es válida: '$fecha'");
        $dtErrors=DateTime::getLastErrors();
        if (!empty($dtErrors) && ($dtErrors["warning_count"]>0 || $dtErrors["error_count"]>0)) throw new Exception("La fecha de factura es inválida: '$fecha'");
        $rutaBase=$_SERVER['DOCUMENT_ROOT'];
        if (isset($tc) && "P"===strtoupper($tc))
            $ubi0="recibos/";
        else $ubi0="archivos/";
        $alias=strtoupper(trim($alias," /\t\n\r\0\x0B"))."/";
        $year=$fdate->format("Y")."/";
        $month=$fdate->format("m")."/";
        $ubicacion=$ubi0.$alias.$year.$month;
        if (!is_dir($rutaBase.$ubicacion)) {
            if (mkdir($rutaBase.$ubicacion, 0777, true)) {
                chmod($rutaBase.$ubi0.$alias, 0777);
                chmod($rutaBase.$ubi0.$alias.$year, 0777);
                chmod($rutaBase.$ubicacion, 0777);
                copy($rutaBase."archivos/index.php", $rutaBase.$ubicacion."index.php");
            } else throw new Exception("Error en creación de ruta de archivos");
        }
        return $ubicacion;
    }
    function getFullMapWhere($userName) {
        $oldRPP=$this->rows_per_page;
        $this->rows_per_page=0;
        //doclog("Facturas->getFullMapWhere","read");
        $rfcGpoList = $this->getList("codigoProveedor", $userName, "distinct rfcGrupo", "statusn<512");
        if (!empty($rfcGpoList)) $fmw="rfc in ('".str_replace("|", "','", $rfcGpoList)."')";
        $this->rows_per_page=$oldRPP;
        return $fmw??false;
    }
}
