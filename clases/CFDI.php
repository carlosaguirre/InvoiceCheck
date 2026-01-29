<?php
require_once dirname(__DIR__)."/bootstrap.php";
class CFDI {
    const EXCEPTION_CORRUPTFILE = 3649001;
    const EXCEPTION_NOTFOUND = 3649002;
    const EXCEPTION_NOTCFDI = 3649003;
    const EXCEPTION_CFDINULL = 3649004;
    const EXCEPTION_NOTXSI = 3649005;
    const EXCEPTION_XSINULL = 3649006;
    const EXCEPTION_NOSCHEMA = 3649007;
    const EXCEPTION_SCHEMANULL = 3649008;
    const EXCEPTION_BADVERSION = 3650001;
    const EXCEPTION_BADSCHEMA =  3650100;
    //const EXCEPTION_VER32 = 3650002;
    //const EXCEPTION_VER33 = 3650003;
    //const EXCEPTION_VER40 = 3650004;
    const EXCEPTION_CLAVEPRODSERV_EMPTY = 4000001;
    const EXCEPTION_CLAVEPRODSERV_INVALID = 4000002;
    const EXCEPTION_CLAVEPRODSERV_01010101 = 4000003;
    const EXCEPTION_CLAVEUNIDAD_EMPTY = 4000011;
    const EXCEPTION_CLAVEUNIDAD_INVALID = 4000012;
    const EXCEPTION_OBJETOIMP_INVALID = 4000021;
    const EXCEPTION_FIX_NOSTART = 5000001;
    const EXCEPTION_FIX_NOEND = 5000002;
    const EXCEPTION_FIX_NOEXIST = 5001001;
    const EXCEPTION_FIX_INVALID = 5001002;
    const EXCEPTION_FIX_NOCHMOD = 5001003;
    const EXCEPTION_FIX_BADTYPE = 5001004;
    const EXCEPTION_FIX_ROPEN = 5001101;
    const EXCEPTION_FIX_READ = 5001102;
    const EXCEPTION_FIX_REMPTY = 5001103;
    const EXCEPTION_FIX_RCLOSE = 5001104;
    const EXCEPTION_FIX_WOPEN = 5001201;
    const EXCEPTION_FIX_WRITE = 5001202;
    const EXCEPTION_FIX_WEMPTY = 5001203;
    const EXCEPTION_FIX_WCLOSE = 5001204;
    const EXCEPTION_VAL_UUID_EXISTS = 6000001;
    const EXCEPTION_UNREGISTERED_PROVIDER = 6000002;
    const EXCEPTION_INACTIVE_PROVIDER = 6000003;
    const EXCEPTION_DELETED_PROVIDER = 6000004;
    const EXCEPTION_BADEXCEPTION = 9000000;
    const EXCEPTION_MESSAGES = [
        self::EXCEPTION_CORRUPTFILE=>"El archivo <b>/*filename*/</b> no está bien construido",
        self::EXCEPTION_NOTFOUND=>"El archivo <b>/*filename*/</b> no se encontró",
        self::EXCEPTION_NOTCFDI=>"El archivo <b>/*filename*/</b> no se reconoce como comprobante CFDI",
        self::EXCEPTION_CFDINULL=>"El archivo <b>/*filename*/</b> no está bien construido:<br>No está definido el esquema CFDI",
        self::EXCEPTION_NOTXSI=>"El archivo <b>/*filename*/</b> no está bien construido:<br>No está definida la estructura XSI",
        self::EXCEPTION_XSINULL=>"El archivo <b>/*filename*/</b> no está bien construido:<br>La definición de la estructura XSI está vacía",
        self::EXCEPTION_NOSCHEMA=>"El documento <b>/*filename*/</b> no está bien construido:<br>La estructura no define la ubicación del esquema XSI",
        self::EXCEPTION_SCHEMANULL=>"El documento <b>/*filename*/</b> no está bien construido:<br>La referencia de ubicación de esquema XSI está vacía",
        self::EXCEPTION_BADVERSION=>"El documento <b>/*filename*/</b> no es válido:<br>La versión CFDI /*version*/ no está incorporada al sistema",
        self::EXCEPTION_BADSCHEMA=>"El documento <b>/*filename*/</b> no se reconoce por inconsistencias en la definición del esquema",
        self::EXCEPTION_OBJETOIMP_INVALID=>"Debe especificar que el concepto es objeto de impuesto (02)",
        self::EXCEPTION_CLAVEPRODSERV_EMPTY=>"Debe indicar la clave de producto o servicio para cada concepto",
        self::EXCEPTION_CLAVEPRODSERV_INVALID=>"La clave de producto o servicio <b>/*clave*/</b> no es válida ante el SAT",
        self::EXCEPTION_CLAVEPRODSERV_01010101=>"No recibimos clave 'SIN DEFINIR' (01010101). Contacte a su agente de Compras para ayudarlo a determinar la clave apropiada",
        self::EXCEPTION_CLAVEUNIDAD_EMPTY=>"Debe indicar la clave de unidad para cada concepto",
        self::EXCEPTION_CLAVEUNIDAD_INVALID=>"La clave de unidad <b>/*clave*/</b> no es válida ante el SAT",
        self::EXCEPTION_FIX_NOSTART=>"El archivo <b>/*filename*/</b> no es válido: <b>'Falta la declaracion XML inicial'</b>",
        self::EXCEPTION_FIX_NOEND=>"El archivo <b>/*filename*/</b> no es válido: <b>'Falta la declaracion XML final'</b>",
        self::EXCEPTION_FIX_NOEXIST=>"No se encontró archivo en la ruta indicada",
        self::EXCEPTION_FIX_INVALID=>"No se indicó un archivo válido",
        self::EXCEPTION_FIX_NOCHMOD=>"No tiene permiso para modificar el archivo",
        self::EXCEPTION_FIX_BADTYPE=>"Formato de archivo invalido: /*tipo*/",
        self::EXCEPTION_FIX_ROPEN=>"No se puede abrir archivo para lectura",
        self::EXCEPTION_FIX_READ=>"Error en lectura de archivo",
        self::EXCEPTION_FIX_REMPTY=>"No se obtuvieron datos del archivo",
        self::EXCEPTION_FIX_RCLOSE=>"Error al cerrar archivo por lectura",
        self::EXCEPTION_FIX_WOPEN=>"Archivo con error, no se pudo abrir para repararlo",
        self::EXCEPTION_FIX_WRITE=>"Archivo con error, sin permiso para repararlo",
        self::EXCEPTION_FIX_WEMPTY=>"Archivo con error, no se pudo guardar corrección",
        self::EXCEPTION_FIX_WCLOSE=>"Archivo con error, no se pudo cerrar",
        self::EXCEPTION_VAL_UUID_EXISTS=>"La factura ya está dada de alta",
        self::EXCEPTION_UNREGISTERED_PROVIDER=>"El proveedor <b>/*proveedor*/</b> no ha sido registrado",
        self::EXCEPTION_INACTIVE_PROVIDER=>"El proveedor <b>/*proveedor*/</b> está inactivo",
        self::EXCEPTION_DELETED_PROVIDER=>"El proveedor <b>/*proveedor*/</b> ha sido eliminado",
        self::EXCEPTION_BADEXCEPTION=>"Ocurrió un error al validar el CFDI, consulte al administrador del sistema"];
    const WEBPATH = "http://www.sat.gob.mx/sitio_internet/";
    //const PATHXSD = "C:\\PHP\\includes\\";
    //const PATHXSD = "http://globaltycloud.com.mx:81/invoice/sat/";
    const PATHXSD = "C:\\Apache24\\htdocs\\invoice\\sat\\";
    const NSTFD = "http://www.sat.gob.mx/TimbreFiscalDigital";
    const REGISTROFISCAL = "http://www.sat.gob.mx/registrofiscal";
    const DONAT = "http://www.sat.gob.mx/donat";
    const XS = "http://www.w3.org/2001/XMLSchema";
    const XSI = "http://www.w3.org/2001/XMLSchema-instance";
    const NSPAGOS10 = "http://www.sat.gob.mx/Pagos";
    const NSPAGOS20 = "http://www.sat.gob.mx/Pagos20";
    const NSCARTAPORTE="http://www.sat.gob.mx/CartaPorte";
    const NSCARTAPORTE20="http://www.sat.gob.mx/CartaPorte20";
    const NSCARTAPORTE30="http://www.sat.gob.mx/CartaPorte30";
    const NSIMPLOCAL="http://www.sat.gob.mx/ImpLocal";
    const XSD32 = "cfdv32.xsd";
    const XSD33 = "cfdv33.xsd";
    const XSD40 = "cfdv40.xsd";
    const TFD11 = "TimbreFiscalDigitalv11.xsd";
//        const CADORI33 = "cadenaoriginal_3_3.xslt";
    const CRF10 = "cfdiregistrofiscal.xsd";
    const PAGO10 = "Pagos10.xsd";
    const PAGO20 = "Pagos20.xsd";
    const CARTAPORTE = "CartaPorte.xsd";
    const CARTAPORTE20 = "CartaPorte20.xsd";
    const CARTAPORTE30 = "CartaPorte30.xsd";
    const IMPLOCAL = "implocal.xsd";
    const CFDPATH = [self::XSD32=>"3", self::XSD33=>"3", self::XSD40=>"4", self::TFD11=>"TimbreFiscalDigital", self::CRF10=>"cfdiregistrofiscal", self::PAGO10=>"Pagos", self::PAGO20=>"Pagos", self::CARTAPORTE=>"CartaPorte", self::CARTAPORTE20=>"CartaPorte", self::CARTAPORTE30=>"CartaPorte", self::IMPLOCAL=>"implocal"];

    const QUERY_XS_TOP_ELEMENT   = "/xs:schema/xs:element";
    const QUERY_XS_ELEMENT       = "xs:complexType/xs:sequence/xs:element|xs:complexType/xs:sequence/xs:sequence/xs:element|xs:complexType/xs:choice/xs:element";
    const QUERY_XS_ATTRIBUTE     = "xs:complexType/xs:attribute";
    const QUERY_XSD_BASE_TYPE    = "xs:simpleType/xs:restriction";
    const QUERY_XS_ANY           = "xs:complexType/xs:sequence/xs:any";
    const QUERY_XMLNS            = "/cfdi:Comprobante/@xmlns:*";
    const QUERY_VERSION          = "/cfdi:Comprobante/@Version|/cfdi:Comprobante/@version";
    const QUERY_SERIE            = "/cfdi:Comprobante/@Serie|/cfdi:Comprobante/@serie";
    const QUERY_FOLIO            = "/cfdi:Comprobante/@Folio|/cfdi:Comprobante/@folio";
    const QUERY_FECHA            = "/cfdi:Comprobante/@Fecha|/cfdi:Comprobante/@fecha";
    const QUERY_SELLO            = "/cfdi:Comprobante/@Sello|/cfdi:Comprobante/@sello";
    const QUERY_FORMA_PAGO       = "/cfdi:Comprobante/@FormaPago|/cfdi:Comprobante/@formaDePago";
    const QUERY_NO_CERTIFICADO   = "/cfdi:Comprobante/@NoCertificado|/cfdi:Comprobante/@noCertificado";
    const QUERY_CERTIFICADO      = "/cfdi:Comprobante/@Certificado|/cfdi:Comprobante/@certificado";
    const QUERY_CONDICIONES_PAGO = "/cfdi:Comprobante/@CondicionesDePago|/cfdi:Comprobante/@condicionesDePago";
    const QUERY_SUBTOTAL         = "/cfdi:Comprobante/@SubTotal|/cfdi:Comprobante/@subTotal";
    const QUERY_DESCUENTO        = "/cfdi:Comprobante/@Descuento|/cfdi:Comprobante/@descuento";
    const QUERY_MOTIVO_DESCUENTO = "/cfdi:Comprobante/@SIN_MOTIVO_33|/cfdi:Comprobante/@motivoDescuento";
    const QUERY_TIPOCAMBIO       = "/cfdi:Comprobante/@TipoCambio";
    const QUERY_MONEDA           = "/cfdi:Comprobante/@Moneda";
    const QUERY_TOTAL            = "/cfdi:Comprobante/@Total|/cfdi:Comprobante/@total";
    const QUERY_TIPO_COMPROBANTE = "/cfdi:Comprobante/@TipoDeComprobante|/cfdi:Comprobante/@tipoDeComprobante";
    const QUERY_METODO_PAGO      = "/cfdi:Comprobante/@MetodoPago|/cfdi:Comprobante/@metodoDePago";
    const QUERY_LUGAR_EXPEDICION = "/cfdi:Comprobante/@LugarExpedicion";
    const QUERY_NUM_CTA_PAGO     = "/cfdi:Comprobante/@SIN_NUM_CTA_33|/cfdi:Comprobante/@NumCtaPago";
    const QUERY_FOLIO_FISCAL     = "/cfdi:Comprobante/@SIN_FOLIO_F_33|/cfdi:Comprobante/@FolioFiscalOrig";
    const QUERY_SERIE_FOLIO_F    = "/cfdi:Comprobante/@SIN_SERIE_F_33|/cfdi:Comprobante/@SerieFolioFiscalOrig";
    const QUERY_FECHA_FOLIO_F    = "/cfdi:Comprobante/@SIN_FECHA_F_33|/cfdi:Comprobante/@FechaFolioFiscalOrig";
    const QUERY_MONTO_FOLIO_F    = "/cfdi:Comprobante/@SIN_MONTO_F_33|/cfdi:Comprobante/@MontoFolioFiscalOrig";
    const QUERY_CONFIRMACION     = "/cfdi:Comprobante/@Confirmacion|/cfdi:Comprobante/@SIN_CONFIRMACION_32";
    const QUERY_TFD              = "/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital";
    const QUERY_UUID             = "/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital/@UUID";
    const QUERY_PAGO_VERSION     = "/cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/@Version|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/@Version";
    const QUERY_PAGO_TOTAL_TRASLADOS = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosBaseIVA16|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosImpuestoIVA16|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosBaseIVA8|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosImpuestoIVA8|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosBaseIVA0|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosImpuestoIVA0|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalTrasladosBaseIVAExento";
    const QUERY_PAGO_TOTAL_RETENCIONES = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalRetencionesIVA|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalRetencionesISR|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalRetencionesIEPS";
    //const QUERY_PAGO_TOTAL_RETENCION_IVA = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalRetencionesIVA";
    //const QUERY_PAGO_TOTAL_RETENCION_ISR = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalRetencionesISR";
    //const QUERY_PAGO_TOTAL_RETENCION_IEPS = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@TotalRetencionesIEPS";
    const QUERY_PAGO_MONTO_TOTAL = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Totales/@MontoTotalPagos";
    const QUERY_BLOQUE_PAGOS     = "/cfdi:Comprobante/cfdi:Complemento/pago10:Pagos|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos";
    const QUERY_PAGOS            = "/cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago";
    const QUERY_PAGO_DOCTOS      = "/cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago/pago10:DoctoRelacionado|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:DoctoRelacionado";
    const QUERY_PAGO_DOC_MON     = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:DoctoRelacionado/@MonedaDR";
    CONST QUERY_PAGO_FECHA       = "/cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago/@FechaPago|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/@FechaPago";
    const QUERY_PAGO_MONTO       = "/cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago/@Monto|/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/@Monto";
    const QUERY_PAGO_FORMA_PAGO  = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/@FormaDePagoP";
    const QUERY_OBJETO_IMP_DR = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:DoctoRelacionado/@ObjetoImpDR";
    const QUERY_PAGO_TIPO_CAMBIO = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/@TipoCambioP";
    const QUERY_PAGO_IMPUESTO_TRASLADO = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP";
    const QUERY_PAGO_IMPUESTO_TRASLADO_BASE = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP/@BaseP";
    const QUERY_PAGO_IMPUESTO_TRASLADO_IMPUESTO = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP/@ImpuestoP";
    const QUERY_PAGO_IMPUESTO_TRASLADO_IMPORTE = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP/@ImporteP";
    const QUERY_PAGO_IMPUESTO_TRASLADO_TASA = "/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP/@TasaOCuotaP";
    const QUERY_CARTAPORTE       = "/cfdi:Comprobante/cfdi:Complemento/cartaporte:CartaPorte";
    const QUERY_CARTAPORTE20     = "/cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte";
    const QUERY_CARTAPORTE30     = "/cfdi:Comprobante/cfdi:Complemento/cartaporte30:CartaPorte";
    const QUERY_CARTAPORTE_VERSION = "/cfdi:Comprobante/cfdi:Complemento/cartaporte:CartaPorte/@Version|/cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/@Version|/cfdi:Comprobante/cfdi:Complemento/cartaporte30:CartaPorte/@Version";
    const QUERY_CARTAPORTE_INTERNAC = "/cfdi:Comprobante/cfdi:Complemento/cartaporte:CartaPorte/@TranspInternac|/cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/@TranspInternac|/cfdi:Comprobante/cfdi:Complemento/cartaporte30:CartaPorte/@TranspInternac";
    const QUERY_CARTAPORTE_DISTREC = "/cfdi:Comprobante/cfdi:Complemento/cartaporte:CartaPorte/@TotalDistRec|/cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/@TotalDistRec|/cfdi:Comprobante/cfdi:Complemento/cartaporte30:CartaPorte/@TotalDistRec";
    const QUERY_EMISOR           = "/cfdi:Comprobante/cfdi:Emisor";
    const QUERY_RECEPTOR         = "/cfdi:Comprobante/cfdi:Receptor";
    const QUERY_REGIMENFISCAL    = "/cfdi:Comprobante/cfdi:Emisor/@RegimenFiscal";
    const QUERY_TIPO_RELACIONADO = "/cfdi:Comprobante/cfdi:CfdiRelacionados/@TipoRelacion";
    const QUERY_UUID_RELACIONADO = "/cfdi:Comprobante/cfdi:CfdiRelacionados/cfdi:CfdiRelacionado/@UUID";
    const QUERY_TRASLADO_TASA    = "/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado[@TipoFactor='Tasa']/@TasaOCuota|/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado[@impuesto='IVA']/@tasa|/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado[@Impuesto='002']/@TasaOCuota";
    const QUERY_CONCEPTOS        = "/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto";
    const QUERY_CONCEPTO_RETENCION = "/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion";
    const QUERY_CONCEPTO_TRASLADO = "/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado";
    const QUERY_RETENCIONES      = "/cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion";
    const QUERY_TRASLADOS        = "/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado";
    const QUERY_TOTALIMPUESTOSRETENIDOS   = "/cfdi:Comprobante/cfdi:Impuestos/@TotalImpuestosRetenidos|/cfdi:Comprobante/cfdi:Impuestos/@totalImpuestosRetenidos";
    const QUERY_TOTALIMPUESTOSTRASLADADOS = "/cfdi:Comprobante/cfdi:Impuestos/@TotalImpuestosTrasladados|/cfdi:Comprobante/cfdi:Impuestos/@totalImpuestosTrasladados";
    const QUERY_IMPLOCAL          = "/cfdi:Comprobante/cfdi:Complemento/implocal:ImpuestosLocales";
    const QUERY_IMPLOCAL_TOTALRETENCIONES = "/cfdi:Comprobante/cfdi:Complemento/implocal:ImpuestosLocales/@TotaldeRetenciones";
    const QUERY_IMPLOCAL_TOTALTRASLADOS = "/cfdi:Comprobante/cfdi:Complemento/implocal:ImpuestosLocales/@TotaldeTraslados";
    const RFCDEMO="RCO050301314";
    const PRV_MONTHLIMIT_EXCEPTION=[]; // "B-021"
    const DO_LOG_EVAL=false;
    const DO_LOG_EXPLODE=false;
    const DO_LOG_GET=false;
    private static $xsddata = [];
    public static $lastException = null;
    private $xmldoc = null;
    private $xpath = null;
//        private $xslxml = null;
//        private $xsltfd = null;
    private $xmlroot = null;
    private $nsCfdi=null;
    private $nsXsi=null;
    private $schemaLocation=null;
    private $xsd=null;
    private $errMsg = null;
    private $errStack = null;
    private $log = null;
    private $logPrompt = "";
    private $enough = null;
    public $originalText = null;
    public $lastResult = null;
    public $cache = [];
    private $data = [];
    private $forceTest = true;
    private static function getKeyWrapper($txt) {
        if (isset($txt)) $txt=trim($txt);
        if (!isset($txt[0])) return null;
        if (substr($txt, 0, 2)!=="/*") $txt="/*".$txt;
        if (substr($txt, -2)!=="*/") $txt.="*/";
        return $txt;
    }
    private static function getValWrapper($txt) {
        if (!isset($txt[0])) return "''";
        return $txt;
    }
    private static function setExceptionParameter($k, $v) {  // search: filename,clave,version,tipo
        if (isset($k[0])) $k=trim($k);
        if (!isset($k[0])) return;
        if (!isset(self::$xsddata["exceptionParameters"])) self::$xsddata["exceptionParameters"]=[$k => $v];
        else self::$xsddata["exceptionParameters"][$k]=$v;
    }
    private static function newException($code) { // , $replaceSearch=null, $replaceReplace=null
        $message=self::getExceptionMessage($code);
        return new Exception($message, $code);
    }
    public static function getExceptionMessage($code) {
        if (!isset(self::EXCEPTION_MESSAGES[$code])) {
            // doclog original $code
            $code=self::EXCEPTION_BADEXCEPTION;
        }
        $message=self::EXCEPTION_MESSAGES[$code];
        //$replaceSearch=self::getKeyWrapper($replaceSearch);
        //if (isset($replaceSearch)) $message=str_replace($replaceSearch, self::getValWrapper($replaceReplace), $message);
        $exceptionParameters=self::$xsddata["exceptionParameters"]??null;
        //self::$xsddata["exceptionParameters"]=null;
        if (isset($exceptionParameters)) {
            $xKys=[]; $xVls=[];
            foreach(array_keys($exceptionParameters) as $idx=>$val) {
                $wval=self::getKeyWrapper($val);
                if (isset($wval)) {
                    $xKys[]=$wval;
                    $xVls[]=self::getValWrapper($exceptionParameters[$val]);
                }
            }
            if (isset($xKys[0])) $message=str_replace($xKys, $xVls, $message);
        }
        return $message;
    }
    public static function newInstanceByFileName($xmlFileName, $xmlOriginalName, &$errMsg, &$errStack, &$enough, &$log) {
        doclog("CFDI:newInstanceByFileName","cfdiLog",["original"=>$xmlOriginalName,"file"=>$xmlFileName]);
        try {
            self::setException(null);
            $xmldoc = new DOMDocument();
            $xmldoc->preserveWhiteSpace = false;
            libxml_use_internal_errors(true);

            $size = filesize($xmlFileName);
            $fixResult=self::reparaXML($xmlFileName,$xmlOriginalName,$log);
            if (is_bool($fixResult)) { // true=cambios realizados exitosamente, false=no fue necesario hacer cambios // exception thrown=ocurrieron errores no esperados // number=se detectaron errores provocados por datos mal ingresados
                if (@$xmldoc->load($xmlFileName, LIBXML_DTDLOAD|LIBXML_DTDATTR) === false) {
                    //self::setLastErrorText("El archivo $xmlOriginalName no está bien construido",1000);
                    self::setExceptionParameter("filename", $xmlOriginalName);
                    $lEO=libxml_get_last_error();
                    if (isset($lEO)) {
                        $lET="";
                        if (property_exists($lEO, 'level'))
                            $lET.=(isset($lET[0])?", ":"")."Level ".$lEO->level;
                        if (property_exists($lEO, 'code'))
                            $lET.=(isset($lET[0])?", ":"")."Code ".$lEO->code;
                        if (property_exists($lEO, 'column'))
                            $lET.=(isset($lET[0])?", ":"")."Column ".$lEO->column;
                        if (property_exists($lEO, 'message'))
                            $lET.=(isset($lET[0])?", ":"")."Message ".$lEO->message;
                        if (property_exists($lEO, 'file'))
                            $lET.=(isset($lET[0])?", ":"")."File ".$lEO->file;
                        if (property_exists($lEO, 'line'))
                            $lET.=(isset($lET[0])?", ":"")."Line ".$lEO->line;
                        self::setExceptionParameter("libxmlerror", $lET);
                    }
                    throw self::newException(self::EXCEPTION_CORRUPTFILE);
                }
            } else {
                //self::setLastErrorText("El archivo $xmlOriginalName no es válido: $fixResult",1000);
                self::setExceptionParameter("filename", $xmlOriginalName);
                throw self::newException($fixResult);
            }
            self::setExceptionParameter("filename", $xmlOriginalName);
            $retval = new CFDI($xmldoc, $errMsg, $errStack, $enough, $log);
            $retval->originalText = "<H1>$xmlFileName</H1>";
            if ($xmlFileName!==$xmlOriginalName) $retval->originalText.="<H2>$xmlOriginalName</H2>";
            $retval->originalText.=file_get_contents($xmlFileName);
            $retval->cache["xmlOriginalName"]=$xmlOriginalName;
            $retval->cache["xmlLoadFilePath"]=$xmlFileName;
            return $retval;
        } catch (Exception $e) {
            $errMsg = "<p>".str_replace("%originalName%", "<b>$xmlOriginalName</b>", $e->getMessage())."</p>";
            $errStack .= strip_tags($errMsg);
            self::setException($e);
            //self::$lastException=$e;
            $log .= "// # CFDI # ERROR DE CREACION # ".$e->getMessage()."\n";
            $log .= "// # CFDI # DOC # ".($xmldoc===null?"ISNULL":(empty($xmldoc->documentElement)?"Empty Element":"Not Empty Element"))."\n";
            $enough = false;
            doclog("CFDI:newInstanceByFileName ERROR","cfdiLog",["errmsg"=>$e->getMessage(),"log"=>$log,"exPars"=>self::$xsddata["exceptionParameters"],"stack"=>$errStack]);
            return null;
        } finally {
            libxml_use_internal_errors(false);
        }
    }
    public static function newInstanceByLocalName($innerName) {
        doclog("CFDI:newInstanceByLocalName","cfdiLog",["name"=>$innerName]);
        self::clearLastError();
        return self::newInstanceByFileName(
            $innerName,
            $innerName,
            self::$xsddata["lastError"]["errorMessage"],
            self::$xsddata["lastError"]["errorStack"],
            self::$xsddata["lastError"]["enough"],
            self::$xsddata["lastError"]["log"]);
    }
    public static function clearLastError() {
        self::$xsddata["lastError"]=["errorMessage"=>"", "errorStack"=>"", "enough"=>true, "log"=>"", "validar"=>"", "code"=>0, "id"=>null, "texto"=>"", "exception"=>null];
    }
    public static function getLastError() {
        if (!isset(self::$xsddata["lastError"])) self::clearLastError();
        return self::$xsddata["lastError"];
    }
    public static function setLastErrorText($text, $code=0, $overwrite=true) {
        if (!isset(self::$xsddata["lastError"])) self::clearLastError();
        if ($overwrite || !isset(self::$xsddata["lastError"]["texto"][0])) {
            self::$xsddata["lastError"]["texto"]=$text;
            //if ($code!==0)
                self::$xsddata["lastError"]["code"]=$code;
        }
    }
    private static function setLastErrorData($key, $value) {
        if (!isset(self::$xsddata["lastError"])) self::clearLastError();
        self::$xsddata["lastError"][$key]=$value;
    }
    private static function setException($ex) {
        if (!isset(self::$xsddata["lastError"])) self::clearLastError();
        self::$xsddata["lastError"]["exception"]=$ex;
        self::$lastException=$ex;
        return $ex;
    }
    /* Unificar ambos newInstance para no duplicar codigo y abrir posibilidad de reparar xml corruptos
    private static function loadXML($filename) {
        $retVal=["doc"=>null,"obj"=>null,"msg"=>null,"stk"=>null,"log"=>null,"enough"=>true,"ex"=>null];
        $xmldoc = new DOMDocument();
        $xmldoc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        try {
            if (@$xmldoc->load($xmlFileName, LIBXML_DTDLOAD|LIBXML_DTDATTR) === false) throw new Exception("El archivo <b>$xmlOriginalName</b> XML está vacío o corrupto",self::EXCEPTION_CORRUPTFILE);
            $retval = new CFDI($xmldoc, $errMsg, $errStack, $enough, $log);
            $retval->originalText = "<H1>$xmlFileName</H1><H2>$xmlOriginalName</H2>".file_get_contents($xmlFileName);
            return $retval;
        } catch (Exception $e) {
            $errMsg = "<p>".str_replace("%originalName%", "<b>$xmlOriginalName</b>", $e->getMessage())."</p>";
            $errStack .= $errMsg;
            $log .= "// # CFDI # ERROR DE CREACION # ".$e->getMessage()."\n";
            $log .= "// # CFDI # DOC # ".($xmldoc===null?"ISNULL":(empty($xmldoc->documentElement)?"Empty Element":"Not Empty Element"))."\n";
            $enough = false;
            return null;
        } finally {
            libxml_use_internal_errors(false);
        }
    }
    */
    public static function reparaXMLText($text,&$log, &$logTxt) {
        // Correccion 1: Eliminar basura al inicio del archivo XML:
        $iniIdx=strpos($text, "<?xml");
        if ($iniIdx===false || $iniIdx<0) {
            $iniIdx=strpos($text,"<xml");
            if ($iniIdx!==false && $iniIdx>=0) {
                if ($iniIdx==0) $text="<?xml".substr($text, 4);
                else $text=substr($text,0,$iniIdx)."<?xml".substr($text, $iniIdx+4);
            } else {
                $iniIdx=strpos($text, "<cfdi:Comprobante");
                if ($iniIdx===false || $iniIdx<0) {
                    $log.="// * Error: Sin declaración XML inicial";
                    return self::EXCEPTION_FIX_NOSTART;
                }
            }
        }
        if ($iniIdx>0) {
            $garbageName="garbage";
            $garbage = substr($text, 0, $iniIdx);
            if ($garbage==="o;?"||$garbage==="?") $garbageName="BOM";
            $text = substr($text, $iniIdx);
            $log.="// * Info: Se remueve basura inicial.\n";
            $logTxt.="|DEL ini $iniIdx $garbageName chars: '$garbage'";
        }

        // Correccion 2: Eliminar basura al final del archivo XML:
        $suffix="</cfdi:Comprobante>";
        $suffixLen=strlen($suffix);
        $endIdx=strpos($text, $suffix);
        if ($endIdx==false || $endIdx<0) {
            $log.="// * Error: Sin declaracion XML final";
            return self::EXCEPTION_FIX_NOEND;
        }
        $diffIdx=$endIdx+$suffixLen-strlen($text);
        if ($diffIdx<0) {
            $garbage=substr($text, $diffIdx);
            $text = substr($text, 0, $diffIdx);
            $log.="// * Info: Se remueve basura al final.\n";
            $diffIdx*=-1;
            $logTxt.="|DEL end $diffIdx garbage chars: '$garbage'";
        }

        // Correccion 3: Eliminar espacios entre tags
        $beforeFixLen=strlen($text);
        $text=preg_replace("/>\s*</", "><", $text);
        $afterFixLen=strlen($text);
        if ($beforeFixLen!=$afterFixLen) {
            $log.="// * Info: Se reemplazan espacios entre tags.\n";
            $logTxt.="|DEL spaces between tags";
        }

        // Correccion 4: Reparar sintaxis de schema
        $beforeFixLen=strlen($text);
        $text=str_replace("xmlns:schemaLocation", "xsi:schemaLocation", $text);
        $schemaTrimLen=strlen($text);
        if ($beforeFixLen!=$schemaTrimLen) {
            $log.="// * Info: Se reemplaza namespace xmlns por xsi en schemaLocation.\n";
            $logTxt.="|Repaired Schema Location NS: xmlns=>xsi";
        }

        // Correccion 5: Reparar caracteres raros (hay que añadir uno por uno pues no siempre coincide con los equivalentes por encoding)
        $wrongLetters=["C","C","C","C","ó","\xD1"," "];
        $fixedLetters=["Á","É","Ñ","Ó","ó","Ñ"," "];
        $auxText=str_replace($wrongLetters, $fixedLetters, $text);
        $unicodTrimLen=strlen($auxText);
        if ($unicodTrimLen!=$schemaTrimLen) {
            $log.="// * Info: Se corrigen símbolos no imprimibles de 2 bytes.\n";
            $logTxt.="|Fixed 2byte-symbols";
            $text=$auxText;
        } else if (strcmp($auxText,$text)!==0) {
            $log.="// * Info: Se corrigen símbolos no imprimibles de 1 byte.\n";
            $logTxt.="|Fixed 1byte-symbols";
            $text=$auxText;
        }
        if (!empty($GLOBALS["doReplaceAccents"])) {
            $auxText=replaceAccents($text);
            if (strcmp($auxText,$text)!==0) {
                $log.="// * Info: Se reemplazan letras acentuadas por equivalentes simples.\n";
                $logTxt.="|replace accented chars";
                $text=$auxText;
            }
        }
        if (!empty($GLOBALS["doNormalizeUtf8Chars"])) {
            $auxText=normalize_to_utf8_chars($text);
            if (strcmp($auxText,$text)!==0) {
                $log.="// * Info: Se normalizan caracteres a utf8.\n";
                $logTxt.="|normalized chars to utf8";
                $text=$auxText;
            }
        }
        /*$idx=-1;
        while(($idx=strpos($text, "Compensaci",$idx+1))!==false) {
            $o1_1=ord(substr($text, $idx+10, 1));
            $o1_2=ord(substr($text, $idx+11, 1));
            $ori=substr($text, $idx, 13);
            $enc1=mb_detect_encoding($ori);
            $ori11=ord(substr($ori, 10, 1));
            $ori12=ord(substr($ori, 11, 1));
            $text = substr($text, 0, $idx+10)."ó".substr($text, $idx+12);
            $fix=substr($text, $idx, 13);
            $enc2=mb_detect_encoding($ori);
            $o2_1=ord(substr($text, $idx+10, 1));
            $o2_2=ord(substr($text, $idx+11, 1));
            $fix11=ord(substr($ori, 10, 1));
            $fix12=ord(substr($ori, 11, 1));
            $log.="// * Info: Corrección de caracter en otro encoding.\n";
            $logTxt.="|WierdEncoding $idx: '{$ori}'($enc1)=>'{$fix}($enc2)' [$o1_1, $o1_2, $ori11, $ori12, $o2_1, $o2_2, $fix11, $fix12] => ".mb_convert_encoding($ori, "UTF-8", "auto");;
        }*/


        // Correccion 6: Quitar Addenda vacía (COSTCO en caja chica con Morysan/AngelicaTorres)
        $emptyAddenda1="<cfdi:Addenda></cfdi:Addenda>";
        $idx=strpos($text, $emptyAddenda1);
        if ($idx!==false) {
            $len = strlen($emptyAddenda1);
            $logTxt.="|emptyAddenda1";
            $text = substr($text, 0, $idx).substr($text, $idx+$len);
        }
        $emptyAddenda2="<cfdi:Addenda />";
        $idx=strpos($text, $emptyAddenda2);
        if ($idx!==false) {
            $len = strlen($emptyAddenda2);
            $logTxt.="|emptyAddenda2";
            $text = substr($text, 0, $idx).substr($text, $idx+$len);
        }
        $emptyAddenda3="<cfdi:Addenda";
        $idx=strpos($text, $emptyAddenda3);
        if ($idx!==false) {
            $logTxt.="|foundAddenda3";
            $len=strlen($emptyAddenda3);
            $cls=strpos($text, ">", $idx+$len);
            if ($cls!==false) {
                if (substr($text,$cls-1,1)==="/") { // <Addenda x y z />
                    $logTxt.="|emptyAddenda3";
                    $text=substr($text, 0, $idx).substr($text,$cls+1);
                } else {
                    $closeAddenda3="</cfdi:Addenda>";
                    $ctg=strpos($text, $closeAddenda3, $idx+$len);
                    if ($ctg!==false) {
                        $cls++;
                        $cdf=$ctg-$cls;
                        $addendaData=substr($text,$cls,$cdf);
                        $addendaInfo=($cdf>0)?trim(preg_replace('/<!--(.*)-->/Uis', '', $addendaData)):"";
                        if (!isset($addendaInfo[0])) {
                            $aln=strlen($closeAddenda3);
                            $logTxt.="|removedAddenda3";
                            $text=substr($text, 0, $idx).substr($text,$ctg+$aln);
                        } else $logTxt.="|hasInfoAddenda3\n$addendaInfo\n";
                    } else $logTxt.="|ctgFalseAddenda3";
                }
            } else $logTxt.="|clsFalseAddenda3";
        } else $logTxt.="|NotFoundAddenda3";
        return $text;
    }
    public static function reparaXML($filePath,$originalPath, &$log) {
        // resultados:
            // true=se hicieron cambios satisfactoriamente
            // false=no se hicieron cambios, todo en orden
        if (!isset($log[0])) $log="";
        $log.="// # Reparando XML $originalPath\n";
        if (!file_exists($filePath)) {
            if (!file_exists($originalPath)) {
                $log.="// * Error: No existe el archivo indicado.\n";
                return self::EXCEPTION_FIX_NOEXIST;
            }
            $readPath=$originalPath;
        } else {
            $readPath=$filePath;
        }
        if (is_dir($readPath)) {
            $log.="// * Error: La ruta indicada es un directorio.\n";
            return self::EXCEPTION_FIX_INVALID;
        }
        /*if (strpos($readPath, "/")!==false) {
            $readPath=str_replace("/", "\\\\", $readPath);
        }*/
        doclog("CFDI:reparaXML FINFO START","cfdiLog",["path"=>$readPath]);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        doclog("CFDI:reparaXML FINFO OBJ CREATED","cfdiLog",["finfo"=>$finfo]);
        try {
            $tmpType = $finfo->file($readPath);
            doclog("CFDI:reparaXML FINFO TYPE","cfdiLog",["type"=>$tmpType]);
        } catch(Exception $ex) {
            $tmpType="";
            doclog("CFDI:reparaXML FINFO EXCEPTION","cfdiLog",["error"=>getErrorData($ex)]);
        }
        if (isset($tmpType[0]) && $tmpType!=="text/xml" && $tmpType!=="application/xml" && $tmpType!=="text/plain" && $tmpType!=="application/octet-stream") {
            $log.="// * Error: Formato detectado no aceptado: {$tmpType}.\n";
            self::setExceptionParameter("tipo",$tmpType);
            return self::EXCEPTION_FIX_BADTYPE;
        }
        //$finfo->close();
        $fd = @fopen($readPath,"r");
        if ($fd===false) {
            $log.="// * Error: Falló fopen 'r'.\n";
            return self::EXCEPTION_FIX_ROPEN;
        }
        $textFileContents = fread($fd,filesize($readPath));
        if ($textFileContents===false) {
            $log.="// * Error: Falló fread.\n";
            fclose($fd);
            return self::EXCEPTION_FIX_READ;
        }
        $textFileContents=trim($textFileContents);
        if (!isset($textFileContents[0])) {
            $log.="// * Error: Archivo vacío.\n";
            fclose($fd);
            return self::EXCEPTION_FIX_REMPTY;
        }
        if (fclose($fd)===false) {
            $log.="// * Error: Falló fclose.\n";
            return self::EXCEPTION_FIX_RCLOSE;
        }
        $log.="// * Info: Formato de archivo {$tmpType}.\n";
        $logtxt="|$tmpType";
        $fixedTextFileContents = self::reparaXMLText($textFileContents, $log, $logtxt);
        if (!is_string($fixedTextFileContents)) return $fixedTextFileContents;

        if (strcmp($textFileContents,$fixedTextFileContents)==0) {
            $log.="// * No se encontraron diferencias.\n";
            return false;
        }
        
        $writePath=$filePath;
        if (file_exists($writePath) && !is_writable($writePath) && !chmod($writePath, 0666)) {
            $log.="// * Error: Documento sin permiso de escritura.\n";
            return self::EXCEPTION_FIX_NOCHMOD;
        }
        $fd=fopen($writePath,"w");
        if ($fd===false) {
            $log.="// * Error: Falló fopen 'w'.\n";
            return self::EXCEPTION_FIX_WOPEN;
        }
        $writeResult=fwrite($fd, $fixedTextFileContents);
        $closeResult=fclose($fd);
        if ($writeResult===false) {
            $log.="// * Error: Falló fwrite con false.\n";
            return self::EXCEPTION_FIX_WRITE;
        }
        if ($writeResult===0) {
            $log.="// * Error: Falló fwrite con 0.\n";
            return self::EXCEPTION_FIX_WEMPTY;
        }
        if ($closeResult===false) {
            $log.="// * Error: Falló fclose.\n";
            return self::EXCEPTION_FIX_WCLOSE;
        }
        $log.="// * Archivo Reparado!\n";
        doclog("CFDI:reparaXML XML Reparado","cfdi",["file"=>$filePath,"original"=>$originalPath]);
        doclog("CFDI:reparaXML REPARADO","cfdiLog",["txt"=>$logtxt,"log"=>$log]);
        return true;
    }
    private function __construct($xmldoc, &$errMsg, &$errStack, &$enough, &$log) {
        if (empty($xmldoc)) //{ self::setExceptionParameter("filename", $xmlOriginalName);
            throw self::newException(self::EXCEPTION_NOTFOUND);
        //}
        $this->xmldoc = $xmldoc;
        $xmlelem = $xmldoc->documentElement;
        global $isAdmin;
        $isAdmin=(hasUser()&&getUser()->nombre==="admin");
        
        //data to save
        $this->data["facturas"]=[];
        $this->data["conceptos"]=[];
        $this->data["pagos"]=[];
        $this->data["cpagos"]=[];
        $this->data["dpagos"]=[];
        $this->data["valerr"]=[];
        //$this->data["proceso"]=[];
        //$this->data["extra"]=[];

        // Search namespaces
        $this->xpath = new DOMXPATH($xmldoc);
        
        if (!$xmlelem->hasAttribute("xmlns:cfdi")) throw self::newException(self::EXCEPTION_NOTCFDI);
        $this->nsCfdi = $xmlelem->getAttribute("xmlns:cfdi");
        if (empty($this->nsCfdi)) throw self::newException(self::EXCEPTION_CFDINULL);
        if (!$xmlelem->hasAttribute("xmlns:xsi")) {
            doclog("CFDI: SCHEMACHECK no xsi attr","cfdi",["nscfdi"=>$this->nsCfdi]);
            throw self::newException(self::EXCEPTION_NOTXSI);
        }
        $this->nsXsi = $xmlelem->getAttribute("xmlns:xsi");
        if (empty($this->nsXsi)) {
            doclog("CFDI: SCHEMACHECK empty xsi attr","cfdi",["nscfdi"=>$this->nsCfdi]);
            throw self::newException(self::EXCEPTION_XSINULL);
        }
        if (!$xmlelem->hasAttributeNS($this->nsXsi, "schemaLocation")) {
            doclog("CFDI: SCHEMACHECK no schemaLocation attr","cfdi",["nscfdi"=>$this->nsCfdi,"nsxsi"=>$this->nsXsi]);
            throw self::newException(self::EXCEPTION_NOSCHEMA);
        }
        $this->schemaLocation = $xmlelem->getAttributeNS($this->nsXsi, "schemaLocation");
        if (empty($this->schemaLocation)) {
            doclog("CFDI: SCHEMACHECK empty schemaLocation attr","cfdi",["nscfdi"=>$this->nsCfdi,"nsxsi"=>$this->nsXsi]);
            throw self::newException(self::EXCEPTION_SCHEMANULL);
        }
        $schemaLocationStr = $this->schemaLocation;
        $this->schemaLocation = explode(" ", $this->schemaLocation);
        $isVer32=false; $isVer33=false; $isVer40=false;
        $cfdPath=self::WEBPATH."cfd/";
        $version=$this->gdbData("version");
        if (in_array($cfdPath.self::CFDPATH[self::XSD32]."/".self::XSD32,$this->schemaLocation)) {
            //$this->xsd=self::XSD32; $this->logPrompt="CFDI3.2";
            //$isVer32=true;
            //throw new Exception("La versión CFDI 3.2 ya no es válida",self::EXCEPTION_VER32);
            // Ahora se permite schemaLocation=3.2, se interpretará como 3.3.
            if ($version==="3.2") {
                $this->xsd=self::XSD32;
                $isVer32=true;
                $this->logPrompt="CFDI3.2";
            //} else if ($version==="3.3") {
            //    $this->xsd=self::XSD33;
            //    $isVer33=true;
            //    $this->logPrompt="CFDI3.3";
            } else {
                self::setExceptionParameter("version", $version);
                throw self::newException(self::EXCEPTION_BADVERSION);
            }
        } else if (in_array($cfdPath.self::CFDPATH[self::XSD33]."/".self::XSD33,$this->schemaLocation)) {
            if ($version!=="3.3") {
                self::setExceptionParameter("version", $version);
                throw self::newException(self::EXCEPTION_BADVERSION);
            }
            $this->xsd=self::XSD33; $this->logPrompt="CFDI3.3";
            $isVer33=true;
        } else if (
            // http://www.sat.gob.mx/sitio_internet/cfd/
            in_array($cfdPath.self::CFDPATH[self::XSD40]."/".self::XSD40,$this->schemaLocation) || 
            //(in_array($cfdPath.self::CFDPATH[self::XSD40],$this->schemaLocation) && 
            in_array(self::XSD40,$this->schemaLocation)
            //)
        ) {
            if ($version!=="4.0") {
                doclog("CFDI: Incorrect Version","error",["nscfdi"=>$this->nsCfdi,"nsxsi"=>$this->nsXsi,"schemaLocation"=>$this->schemaLocation,"absSyntxFile"=>$cfdPath.self::CFDPATH[self::XSD40]."/".self::XSD40,"relSyntxFile"=>self::XSD40,"version"=>$version]);
                self::setExceptionParameter("version", $version);
                throw self::newException(self::EXCEPTION_BADVERSION);
            }
            $this->xsd=self::XSD40; $this->logPrompt="CFDI4.0";
            $isVer40=true;
        } else {
            //$x33len = strlen(self::XSD33);
            //$last33Check = array_filter($this->schemaLocation, function($addr) use (/*&$debug,*/ $x33len) {
            //    $chunk = substr($addr,-$x33len);
            //    $result = $chunk===self::XSD33;
            //    return $result;
            //});
            //if (!empty($last33Check)) {
            //    $this->xsd=self::XSD33; $this->logPrompt="CFDI3.3";
            //    $isVer33=true;
            //} else {
                //doclog("CFDI: SCHEMACHECK empty last33Check","cfdi",["nscfdi"=>$this->nsCfdi,"nsxsi"=>$this->nsXsi,"schemaLocation"=>$this->schemaLocation]);
                doclog("CFDI: Undeclared Version","error",["nscfdi"=>$this->nsCfdi,"nsxsi"=>$this->nsXsi,"schemaLocation"=>$this->schemaLocation,"absPathSrchd"=>$cfdPath.self::CFDPATH[self::XSD40]."/".self::XSD40,"onlyPathSrchd"=>$cfdPath.self::CFDPATH[self::XSD40],"relSyntaxFileSrchd"=>self::XSD40,"version"=>$version]);
                $verArr=[];
                foreach ($this->schemaLocation as $i => $txt) {
                    $slashIdx=strrpos($txt, "/");
                    if ($slashIdx!==false) $verArr[]=substr($txt, $slashIdx+1);
                }
                if (isset($version[0])) $verArr[]=$version;
                $schemaVersion=isset($verArr[0])?implode("|", $verArr):"NOVERSION";
                if ($version==="4.0") {
                    throw self::newException(self::EXCEPTION_BADSCHEMA);
                }
                self::setExceptionParameter("version", $version);
                throw self::newException(self::EXCEPTION_BADVERSION);
            //}
        }
        //doclog("CFDI: VALIDACION ADICIONAL","cfdi",["xsd"=>$this->xsd,"data"=>self::$xsddata]);
        if (empty(self::$xsddata["doc.".$this->xsd])) {
            $hasTFD = !empty(self::$xsddata["doc.".self::TFD11]);
            $hasCRF = !empty(self::$xsddata["doc.".self::CRF10]);
            $hasPAGO10 = !empty(self::$xsddata["doc.".self::PAGO10]);
            $hasPAGO20 = !empty(self::$xsddata["doc.".self::PAGO20]);
            $hasCARTAPORTE = !empty(self::$xsddata["doc.".self::CARTAPORTE]);
            $hasCARTAPORTE20 = !empty(self::$xsddata["doc.".self::CARTAPORTE20]);
            $hasCARTAPORTE30 = !empty(self::$xsddata["doc.".self::CARTAPORTE30]);
//                $hasCADORI = $isVer33 && !empty(self::$xsddata["xsl.".self::CADORI33]);
            $hasIMPLOCAL = !empty(self::$xsddata["doc.".self::IMPLOCAL]);
            $localPath=self::PATHXSD."cfd\\";
            if ($isAdmin) doclog("CFDI: LOADING SCHEMA CFDI","cfdiLog",["xsd"=>$localPath.self::CFDPATH[$this->xsd]."\\".$this->xsd]); // $cfdPath=self::WEBPATH."cfd/".self::CFDPATH[$this->xsd]."/".$this->xsd;
            $xd = new DOMDocument();
            $xd->load($localPath.self::CFDPATH[$this->xsd]."\\".$this->xsd, LIBXML_DTDLOAD|LIBXML_DTDATTR);
            self::$xsddata["doc.".$this->xsd] = $xd;
            if (!$hasTFD) {
                $tfdPath=$localPath.self::CFDPATH[self::TFD11]."\\".self::TFD11;
                if ($isAdmin) doclog("CFDI: LOADING SCHEMA TFD","cfdiLog",["xsd"=>$tfdPath]);
                $tf = new DOMDocument();
                $tf->load($tfdPath, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::TFD11] = $tf;
            }
            if (!$hasCRF) {
                $crfPath=$localPath.self::CFDPATH[self::CRF10]."\\".self::CRF10;
                if ($isAdmin) doclog("CFDI: LOADING SCHEMA CRF","cfdiLog",["xsd"=>$crfPath]);
                $rf = new DOMDocument();
                $rf->load($crfPath, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::CRF10] = $rf;
            }
            if (!$hasPAGO10) {
                $payPath=$localPath.self::CFDPATH[self::PAGO10]."\\".self::PAGO10;
                if ($isAdmin) doclog("CFDI: LOADING SCHEMA PAGO10","cfdiLog",["xsd"=>$payPath]);
                $pf = new DOMDocument();
                $pf->load($payPath, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::PAGO10] = $pf;
            }
            if (!$hasPAGO20) {
                $py2Path=$localPath.self::CFDPATH[self::PAGO20]."\\".self::PAGO20;
                if ($isAdmin) doclog("CFDI: LOADING SCHEMA PAGO20","cfdiLog",["xsd"=>$py2Path]);
                $pf2 = new DOMDocument();
                $pf2->load($py2Path, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::PAGO20] = $pf2;
            }
            if (!$hasCARTAPORTE) {
                $cptPath=$localPath.self::CFDPATH[self::CARTAPORTE]."\\".self::CARTAPORTE;
                if ($isAdmin) doclog("CFDI: LOADING SCHEMA CARTAPORTE","cfdiLog",["xsd"=>$cptPath]);
                $cpf = new DOMDocument();
                $cpf->load($cptPath, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::CARTAPORTE] = $cpf;
            }
            if (!$hasCARTAPORTE20) {
                $cp2Path=$localPath.self::CFDPATH[self::CARTAPORTE20]."\\".self::CARTAPORTE20;
                if ($isAdmin)  doclog("CFDI: LOADING SCHEMA CARTAPORTE2","cfdiLog",["xsd"=>$cp2Path]);
                $cpf2 = new DOMDocument();
                $cpf2->load($cp2Path, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::CARTAPORTE20] = $cpf2;
            }
            if (!$hasCARTAPORTE30) {
                $cp3Path=$localPath.self::CFDPATH[self::CARTAPORTE30]."\\".self::CARTAPORTE30;
                if ($isAdmin)  doclog("CFDI: LOADING SCHEMA CARTAPORTE3","cfdiLog",["xsd"=>$cp3Path]);
                $cpf3 = new DOMDocument();
                $cpf3->load($cp3Path, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::CARTAPORTE30] = $cpf3;
            }
            if (!$hasIMPLOCAL) {
                $imlPath=$localPath.self::CFDPATH[self::IMPLOCAL]."\\".self::IMPLOCAL;
                if ($isAdmin) doclog("CFDI: LOADING SCHEMA IMPLOCAL","cfdiLog",["xsd"=>$imlPath]);
                $ilf = new DOMDocument();
                $ilf->load($imlPath, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["doc.".self::IMPLOCAL] = $ilf;
            }
/*
            if ($isVer33 && !$hasCADORI) {
                $co = new DOMDocument();
                $co->load(self::PATHXSD.self::CADORI33, LIBXML_DTDLOAD|LIBXML_DTDATTR);
                self::$xsddata["xsl.".self::CADORI33] = $co;
            }
*/
            $xdp = new DOMXPath($xd);
            $xdp->registerNamespace("xs", self::XS);
            //$xdp->registerNamespace("xsi", self::XSI);
            self::$xsddata["xpath.".$this->xsd] = $xdp;
            if (!$hasTFD) {
                $tfp = new DOMXPath($tf);
                $tfp->registerNamespace("xs", self::XS);
                self::$xsddata["xpath.".self::TFD11] = $tfp;
            }
            if (!$hasCRF) {
                $rfp = new DOMXPath($rf);
                $rfp->registerNamespace("xs", self::XS);
                self::$xsddata["xpath.".self::CRF10] = $rfp;
            }
            if(!$hasPAGO10) {
                $pfp = new DOMXPath($pf);
                $pfp->registerNamespace("xs", self::XS);
                self::$xsddata["xpath.".self::PAGO10] = $pfp;
            }
            if(!$hasPAGO20) {
                $pf2p = new DOMXPath($pf2);
                $pf2p->registerNamespace("xs", self::XS);
                self::$xsddata["xpath.".self::PAGO20] = $pf2p;
            }
            if(!$hasCARTAPORTE) {
                $cpfp = new DOMXPath($cpf);
                $cpfp->registerNamespace("xs",self::XS);
                self::$xsddata["xpath.".self::CARTAPORTE] = $cpfp;
            }
            if(!$hasCARTAPORTE20) {
                $cpf2p = new DOMXPath($cpf2);
                $cpf2p->registerNamespace("xs",self::XS);
                self::$xsddata["xpath.".self::CARTAPORTE20] = $cpf2p;
            }
            if(!$hasCARTAPORTE30) {
                $cpf3p = new DOMXPath($cpf3);
                $cpf3p->registerNamespace("xs",self::XS);
                self::$xsddata["xpath.".self::CARTAPORTE30] = $cpf3p;
            }
            if (!$hasIMPLOCAL) {
                $ilfp = new DOMXPath($ilf);
                $ilfp->registerNamespace("xs", self::XS);
                self::$xsddata["xpath.".self::IMPLOCAL] = $ilfp;
            }
/*
            if ($isVer33 && !$hasCADORI) {
                $proc = new XSLTProcessor();
                libxml_use_internal_errors(true);
                $boolResult = $proc->importStyleSheet($co);
                if (!$boolResult) {
                    $xslError="";
                    foreach (libxml_get_errors() as $error) {
                        if (isset($xslError[0])) $xslError.="<br>";
                        $xslError.=$error->message;
                    }
                }
                libxml_use_internal_errors(false);
                self::$xsddata["xproc.".self::CADORI33] = $proc;
                if (isset($xslError[0])) throw new Exception("Falló al definir validación de cadena original:<br>$xslError");
            }
*/
            
            $xda = $this->explodeXSD($xd->documentElement);
            self::$xsddata["xarr.".$this->xsd] = $xda;
            if (!$hasTFD) {
                $xda = $this->explodeXSD($tf->documentElement);
                self::$xsddata["xarr.".self::TFD11] = $xda;
            }
            if (!$hasCRF) {
                $xda = $this->explodeXSD($rf->documentElement);
                self::$xsddata["xarr.".self::CRF10] = $xda;
            }
            if (!$hasPAGO10) {
                $xda = $this->explodeXSD($pf->documentElement);
                self::$xsddata["xarr.".self::PAGO10] = $xda;
            }
            if (!$hasPAGO20) {
                $xda = $this->explodeXSD($pf2->documentElement);
                self::$xsddata["xarr.".self::PAGO20] = $xda;
            }
            if (!$hasCARTAPORTE) {
                $xda = $this->explodeXSD($cpf->documentElement);
                self::$xsddata["xarr.".self::CARTAPORTE] = $xda;
            }
            if (!$hasCARTAPORTE20) {
                $xda = $this->explodeXSD($cpf2->documentElement);
                self::$xsddata["xarr.".self::CARTAPORTE20] = $xda;
            }
            if (!$hasCARTAPORTE30) {
                $xda = $this->explodeXSD($cpf3->documentElement);
                self::$xsddata["xarr.".self::CARTAPORTE30] = $xda;
            }
            if (!$hasIMPLOCAL) {
                $xda = $this->explodeXSD($ilf->documentElement);
                self::$xsddata["xarr.".self::IMPLOCAL] = $xda;
            }
/*
            if ($isVer33 && !$hasCADORI && isset($proc)) {
                $this->xslxml = $proc->transformToXML($this->xmldoc);
            }
*/                
        }
        
        // listar namespaces, 
        $this->xpath->registerNamespace("cfdi", $this->nsCfdi); // "http://www.sat.gob.mx/cfd/3");
        $this->xpath->registerNamespace("tfd", self::NSTFD);
        $this->xpath->registerNamespace("registrofiscal", self::REGISTROFISCAL);
        $this->xpath->registerNamespace("donat", self::DONAT);
        $this->xpath->registerNamespace("pago10", self::NSPAGOS10);
        $this->xpath->registerNamespace("pago20", self::NSPAGOS20);
        $this->xpath->registerNamespace("cartaporte", self::NSCARTAPORTE);
        $this->xpath->registerNamespace("cartaporte20", self::NSCARTAPORTE20);
        $this->xpath->registerNamespace("cartaporte30", self::NSCARTAPORTE30);
        $this->xpath->registerNamespace("implocal", self::NSIMPLOCAL);
        $this->xpath->registerNamespace("xsi", $this->nsXsi);
        
        $this->log = &$log;
        $this->enough = &$enough;
        $this->errMsg = &$errMsg;
        $this->errStack = &$errStack;
        // admin
        //if ($isAdmin)
        //    doclog("CFDI: VALIDACION ADICIONAL","cfdi",["xarr.cfdv33.xsd"=>self::$xsddata["xarr.cfdv33.xsd"]??"", "xarr.TimbreFiscalDigitalv11.xsd"=>self::$xsddata["xarr.TimbreFiscalDigitalv11.xsd"]??""]); // 
    }
/*
    public function getXSLTResult() {
        return $this->xslxml;
    }
*/
    public function toXML($node=null) {
        if ($node===null) return $this->xmldoc->saveXML();
        return $this->xmldoc->saveXML($node);
    }
    public function getXSD($key=null) {
        //clog2("INI function getXSD(".(isset($key)?$key:"NULL").")", "1");
        $obj = null;
        if (isset($key[0])) {
            switch(strtolower($key)) {
                case "doc": $obj = self::$xsddata["doc.".$this->xsd]; break;
                case "xpath": $obj = self::$xsddata["xpath.".$this->xsd]; break;
                case "array": $obj = self::$xsddata["xarr.".$this->xsd]; break;
                default: $obj = $this->xsd;
            }
        } else $obj=$this->xsd;
        
        //clog2("END function getXSD ".(is_object($obj)?get_class($obj):(is_array($obj)?"Array [".count($obj)."]":gettype($obj))),"1");
        return $obj;
    }
    public function validaReposicion($modifiers=[]) {
        $this->log("INI validaReposicion");
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaReposicion";
        doclog("CFDI:validaReposicion","cfdiLog");
        $errlist = MYLIBXML::validation($this,"TD");
        $logList=MYLIBXML::$logval;
        self::$xsddata["lastError"]["validar"]="INI CC";
        if (!hasUser()) {
            self::setLastErrorText("Usuario no reconocido, por favor cierre e inicie sesión nuevamente.",-1);
            $this->error("<table class='cfdiErrorList mbpi' type='array' count='1'><tr><TD class='lefted wordwrap'><b>Usuario no reconocido, por favor cierre e inicie sesión nuevamente.</b></TD></tr></table>", "");
            $this->cache["trace"][]="ERROR validaReposicion: no user";
            return;
        }
        $tipoComprobante = $this->gdbData("tipo_comprobante","tipoComprobante","strtolower")[0];
        $err = $this->validaVersion($tipoComprobante);
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        }
        $err = $this->validaUUID();
        if (isset($err[0])) {
            self::setLastErrorText($err);
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
        }
        $emisor = $this->get("emisor");
        $rfcEmisor = $emisor["@rfc"]??null;
        if (isset($rfcEmisor[0])) {
            $nombreEmisor = $emisor["@nombre"]??null;
            $err = $this->validaProveedor($rfcEmisor, $nombreEmisor);
        }
        $regimenFiscal = $emisor["@regimenfiscal"]??null;
        $this->data["facturas"]["regimenFiscal"]=$regimenFiscal;
        /**/
        $receptor = $this->get("receptor");
        $err = $this->validaCorporativo($receptor);
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        }
        if ($tipoComprobante!=="i") {
            doclog("CFDI:validaReposicion tipoComprobante","cfdiLog",["err"=>"No es ingreso","tc"=>$tipoComprobante]);
            $err="<b>TIPO COMPROBANTE $tipoComprobante </b>: El comprobante no es tipo INGRESO";
            self::setLastErrorText($err);
            $logList[] = $err;
            $errlist[]="<TD class='lefted wordwrap'>$err</TD>";
        } else {
            // no se evalua metodo de pago pues se acepta Tarjeta Debito que no está en la lista de metodos aceptados
            // $err = $this->validaMetodoDePago ( $this->gdbData("metodo_pago","metodoDePago"), $this->gdbData("forma_pago","formaDePago"),             $tipoComprobante);
            //if (isset($err[0])) $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            $err = $this->validaTipoCambio();
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
            if (is_null($modifiers)) $modifiers=[];
            else if(!is_array($modifiers)) $modifiers=[$modifiers];
            $modifiers["ignoreUndefined"]=true;
            $modifiers["ignoreObjImp"]=true;
            $err = $this->validaConceptos($modifiers);
            if (isset($err[0])) {
                $logList[] = $err;
                //if (strpos($err, "Concepto.ClaveProdServ")===false || strpos($err, "Debe ingresar facturas con conceptos registrados")===false) // quitar comentario a este filtro si se aceptan facturas de reposicion con claveProdServ=01010101
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
        }
        if (isset($logList[0])) {
            // get para emisor y receptor porque ya 
            doclog("CFDI:validaReposicion LOGLIST","cfdi",["VERSION"=>$this->gdbData("version"),"SERIE"=>$this->gdbData("serie",null,"trim"),"FOLIO"=>$this->gdbData("folio",null,"trim"),"FECHA"=>$this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"),"TIPO_COMPROBANTE"=>$tipoComprobante,"UUID"=>$this->gdbData("uuid",null,"strtoupper"),"EMISOR"=>$this->get("emisor"),"RECEPTOR"=>$this->get("receptor"),"TOTAL"=>$this->gdbData("total"),"SUBTOTAL"=>$this->gdbData("subtotal"),"ERROR"=>$logList]);
        }
        if (isset($errlist) && isset($errlist[0])) {
            $errtab = "<table class='cfdiErrorList mbpi' type='".(is_array($errlist)?"array":gettype($errlist))."' count='".count($errlist)."'>";
            foreach ($errlist as $erritem) $errtab .= "<tr>$erritem</tr>";
            $errtab .= "</table>";
            $this->error($errtab, "");
        }
        $this->cache["trace"][]="END validaReposicion";
    }
    public function validar($tc=null) {
        $this->log("INI validar"); // "cfdi"
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validar";
        doclog("CFDI:validar","cfdiLog");
        $errlist = MYLIBXML::validation($this, "TD");
        $logList=MYLIBXML::$logval;
        self::$xsddata["lastError"]["validar"]="INI";
        //clog2ini("CFDI.Object.validar");
        if (!hasUser()) {
            self::setLastErrorText("Usuario no reconocido, por favor cierre e inicie sesión nuevamente.");
            $this->error("<table class='cfdiErrorList mbpi' type='array' count='1'><tr><TD class='lefted wordwrap'><b>Usuario no reconocido, por favor cierre e inicie sesión nuevamente.</b></TD></tr></table>", "");
            $this->cache["trace"][]="ERROR validar: no user";
            return;
        }
        $tipoComprobante = $this->gdbData("tipo_comprobante","tipoComprobante","strtolower")[0];
        $err = $this->validaVersion($tipoComprobante);
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        } else {
            $err = $this->validaFecha();
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
        }
        
        //$err = $this->validaFolio();
        //if (isset($err[0])) $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
        
        $err = $this->validaUUID();
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        }
        
        $emisor = $this->get("emisor");
        $rfcEmisor = $emisor["@rfc"]??null;
        $esEmisorValido = false;
        if (isset($rfcEmisor[0])) {
            $nombreEmisor = $emisor["@nombre"]??null;
            $err = $this->validaProveedor($rfcEmisor, $nombreEmisor);
            $esEmisorValido = !isset($err[0]);
        } else $err = "No se pudo obtener la informacion del emisor";
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        }
        $regimenFiscal = $emisor["@regimenfiscal"]??null;
        $this->data["facturas"]["regimenFiscal"]=$regimenFiscal;
        if (isset($tc[0])) {
            $tc=strtolower($tc[0]);
            if ($tipoComprobante!==$tc) {
                switch ($tc) {
                    case "i": $err="El comprobante debe ser Ingreso"; break;
                    case "e": $err="El comprobante debe ser Egreso"; break;
                    case "p": $err="El comprobante debe ser Pago"; break;
                    case "t": $err="El comprobante debe ser Traslado"; break;
                    default: $err=null;
                }
                if (isset($err[0])) {
                    $logList[] = $err;
                    $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                    self::setLastErrorText($err);
                }
            }
        }
        $receptor = $this->get("receptor");
        if ($tipoComprobante==="i") {
            $retenciones = $this->get("concepto_retencion");
            if (isset($retenciones["@base"])) $retenciones=[$retenciones];
            $folio=$this->gdbData("folio",null,"trim");
            if ($regimenFiscal==="626" && isset($rfcEmisor[12])) { // Factura, RESICO y PFisica
                $retData = ["emisor"=>$emisor,"receptor"=>$receptor,"folio"=>$folio,"retResico"=>[], "resicoValido"=>0];
                if (empty($retenciones)) {
                    $retData["error"]="Debe incluir retención por RESICO";
                } else {
                    $cconceptos = $this->get("conceptos");
                    if (isset($cconceptos["@claveprodserv"])) $retData["numConceptos"]=1;
                    else $retData["numConceptos"]=count($cconceptos);
                    foreach ($retenciones as $retIdx => $retItm) {
                        $retData["retResico"][$retIdx]=["retencion"=>$retItm,"check"=>[]];
                        if ($retItm["@impuesto"]==="001") {
                            $retData["retResico"][$retIdx]["check"]["impuesto"]=1;
                            if ($retItm["@tipofactor"]==="Tasa") {
                                $retData["retResico"][$retIdx]["check"]["tipofactor"]=1;
                                $rITOC=$retItm["@tasaocuota"];
                                $ptIdx=strpos($rITOC, ".");
                                if ($ptIdx!==false) {
                                    $nd=strlen($rITOC)-$ptIdx;
                                    if ($nd>7) $rITOC=substr($rITOC, 0, $ptIdx+7);
                                    else if ($nd<7) $rITOC=str_pad($rITOC, $ptIdx+7, "0");
                                }
                                if ($rITOC==="0.012500") {
                                    $retData["retResico"][$retIdx]["check"]["tasaocuota"]=1;
                                    $retData["resicoValido"]++;
                                } else {
                                    $retData["retResico"][$retIdx]["check"]["tasaocuota"]=0;
                                }
                            } else {
                                $retData["retResico"][$retIdx]["check"]["tipofactor"]=0;
                            }
                        } else {
                            $retData["retResico"][$retIdx]["check"]["impuesto"]=0;
                        }
                    }
                    if ($retData["resicoValido"]==0) {
                        $retData["error"]="Es necesario que incluya retención por RESICO";
                    } else if ($retData["resicoValido"]<$retData["numConceptos"]) {
                        $retData["error"]="Debe incluir retención por RESICO en todos los conceptos";
                    }
                }
                if (isset($retData["error"][0])) {
                    doclog("CFDI:validar RESICO Error","cfdiLog",$retData);
                    $logList[]=$retData["error"];
                    $errlist[] = "<TD class='lefted wordwrap'>$retData[error]</TD>";
                    self::setLastErrorText($retData["error"]);
                } else {
                    doclog("CFDI:validar RESICO Válido","cfdiLog",$retData);
                }
            } else if (!empty($retenciones)) {
                foreach ($retenciones as $retIdx=>$retItm) {
                    if ($retItm["@impuesto"]==="001" && $retItm["@tipofactor"]==="Tasa" && $retItm["@tasaocuota"]==="0.012500") {
                        doclog("CFDI:validar Regimen no debe tener ISR 1.25%", "cfdiLog", ["regimen"=>$regimenFiscal,"rfcemisor"=>$rfcEmisor,"retIdx"=>$retIdx,"retItem"=>$retItm]);
                        $errmsg="No debe incluir retención del 1.25% si su régimen no es RESICO y no es persona física";
                        $logList[]=$errmsg;
                        $errlist[]="<TD class='lefted wordwrap'>$errmsg</TD>";
                        self::setLastErrorText($errmsg);
                        break;
                    }
                }
            }
        }
        $err = $this->validaCorporativo($receptor);
        $esReceptorValido = !isset($err[0]);
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        }
        if ($esEmisorValido && $esReceptorValido) $err = $this->validaUsuario($emisor["@rfc"], $receptor["@rfc"]);
        else $err = "";//Ya se describen los errores, no hace falta agregar mensaje repetitivo. //"No se pudo cotejar la informacion del comprobante con el usuario";
        if (isset($err[0])) {
            $logList[] = $err;
            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
            self::setLastErrorText($err);
        }
        if ($tipoComprobante==="t") { // TRASLADO
            ;
        } else if ($tipoComprobante==="p") { // PAGO (es recibo de pago)
            // obtener elementos PAGO para validar atributos FormaDePagoP, MonedaP, Monto (suma de totales)
            // obtener elementos PAGO_DOCTO para validar atributos:
            //  - IdDocumento es el UUID de una factura que aun no se ha marcado como pagada, a la cual habrá que actualizar su status como pagada.
            //  - Se puede cotejar atributos Folio, Serie, MonedaDR, MetodoDePagoDR con los correspondientes en cada  factura
            //  - Corroborar atributos NumParcialidad, ImpSaldoAnt, ImpPagado, ImpSaldoInsoluto antes de marcar una factura como pagada.
            //  - - Por lo pronto solo hace falta comprobar que el atributo ImpSaldoInsoluto se encuentre en ceros ('0' o '0.00') para asegurar que la factura ya fue pagada.
            // RECORDAR QUE ESTA SECCION ES EXCLUSIVA PARA VALIDAR. aqui solo hay que cotejar que IdDocumento corresponde al UUID de una factura y si ImpSaldoInsoluto equivale a cero, cambiar el status de la factura a pagada.
            // Este status debe ser independiente del ciclo de estados para no perder su situacion entre contrarrecibo/exportado/respaldado
            $err = $this->validaPagos();
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
            $err = $this->validaMetodoDePago (
                $this->gdbData("metodo_pago","metodoDePago"), 
                $this->gdbData("forma_pago","formaDePago"),
                $tipoComprobante);
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
            $ver=$this->gdbData("version");
            if ($ver==="4.0") {
                $fpp=$this->get("pago_forma_pago");
                if (!is_array($fpp)) $fpp=[$fpp];
                require_once "clases/catalogoSAT.php";
                $codigoEfectivo=CatalogoSAT::getValue(CatalogoSAT::CAT_FORMAPAGO, "descripcion", "Efectivo", "codigo");
                foreach ($fpp as $fppi) {
                    if ($fppi===$codigoEfectivo) {
                        $err="En el Comprobante de Pago no puede indicar Forma de Pago del pago como \"Efectivo\" (\"$codigoEfectivo\"). Consulte a su agente de Compras sobre los datos requeridos en su XML.";
                        $logList[] = $err;
                        $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                        self::setLastErrorText($err);
                        break;
                    }
                }
                $creationDate = new DateTime($this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"));
                $vApr2023 = new DateTime("2023-04-01 00:00:00");
                $reqObjImp=!isset($this->cache["infoProveedor"]) || ($this->cache["infoProveedor"]["o"]==="1");
                if ($reqObjImp && $creationDate>=$vApr2023) {
                    $oidr=$this->get("objeto_imp_dr");
                    if (!is_array($oidr)) $oidr=[$oidr];
                    // TODO: Agregar catalogo de objeto de impuesto
                    foreach ($oidr as $oidri) {
                        if ($oidri!=="02") {
                            $err="En el Comprobante de Pago no puede indicar Documentos sin Objeto de Impuesto o sin desglose (diferente a 02). Consulte a su agente de Compras sobre los datos requeridos en su XML.";
                            $logList[] = $err;
                            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                            self::setLastErrorText($err);
                            break;
                        }
                    }
                }
                $uid=getUser()->id;
                $reqPayTaxChk = !isset($this->cache["infoProveedor"]) || ($this->cache["infoProveedor"]["t"]==="1");
                global $infObj;
                if (!isset($infObj)) { require_once "clases/InfoLocal.php"; $infObj=new InfoLocal(); }
                $allowPayTaxIgnore = $infObj->exists("nombre='CFDI_ALLOWPRTV_{$uid}' and valor='1'");
                if ($reqPayTaxChk&&!$allowPayTaxIgnore) {
                    doclog("CFDI:validar PRT VALIDATION","cfdi",["noval"=>1]); // Payment Receipt Tax Validation : Validacion de impuestos en complementos de pago
                    $rets=$this->get("pago_total_retenciones");
                    $tras=$this->get("pago_total_traslados");
                    $ptot=+$this->get("pago_monto_total");
                    $pagos=$this->get("pagos");
                    //$pit=$this->get("pago_impuesto_traslado");
                    //doclog("CFDI:validar PAGO IMPUESTO TRASLADO","cfdiLog",["pit"=>$pit]);
                    if (isset($pagos["@tipocambiop"])) $pagos=[$pagos];
                    $valSum=0;
                    //$steps=[];
                    foreach ($pagos as $idx => $pago) {
                        doclog("CFDI:validar PAGOS","cfdiLog",["indice"=>$idx,"pago"=>$pago]);
                        //$steps[]=["idx"=>$idx,"pago"=>$pago];
                        $ptcp=+($pago["@tipocambiop"]??1);
                        //$pitJ=$this->get("/cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago[$idx]/pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP");
                        //$steps[]=["tipocambiop"=>$ptcp];
                        if (isset($pago["ImpuestosP"]) && isset($pago["ImpuestosP"]["TrasladosP"])) {
                            $pitL=$pago["ImpuestosP"]["TrasladosP"];
                            if (isset($pitL["TrasladoP"])) $pitL=[$pitL];
                            //$steps[]=["trasladosp"=>$pitL];
                            doclog("CFDI:validar TRASLADOP","cfdiLog",["pitp"=>$pitL]);
                            foreach ($pitL as $idtp => $tp) { // [{},{}]
                                $tpi = isset($tp["TrasladoP"]["@impuestop"])?[$tp["TrasladoP"]]:$tp["TrasladoP"];
                                doclog("CFDI:validar TP","cfdiLog",["idtp"=>$idtp,"tpi"=>$tpi]);
                                foreach($tpi as $idtp2 => $tpii) {
                                    doclog("CFDI:validar PAGO TRASLADO","cfdiLog",["idtp2"=>$idtp2,"tpii"=>$tpii]);
                                    //$steps[]=["id2"=>$id2,"trasladop"=>$tpii];
                                    if (isset($tpii["@impuestop"]) && $tpii["@impuestop"]==="002") {
                                        $valSum+=$ptcp*(+$tpii["@basep"]+($tpii["@importep"]??0));
                                        //$steps[]=["basep"=>$tpii["@basep"],"importep"=>$tpii["@importep"],"valSum"=>$valSum];
                                    } else doclog("CFDI:validar SIN TRASLADO!","cfdiLog");
                                }
                            }
                        } else doclog("CFDI:validar SIN PAGO DE IMPUESTO!","cfdiLog");
                    }
                    $pitc=$this->get("PAGO_TIPO_CAMBIO")??1; // Pago/@TipoCambioP
                    $pitb=$this->get("PAGO_IMPUESTO_TRASLADO_BASE");
                    // Pago/ImpuestosP/TrasladosP/TrasladoP/@BaseP
                    $piti=$this->get("PAGO_IMPUESTO_TRASLADO_IMPORTE");
                    // Pago/ImpuestosP/TrasladosP/TrasladoP/@ImporteP
                    $pitm=$this->get("PAGO_IMPUESTO_TRASLADO_IMPUESTO");
                    // Pago/ImpuestosP/TrasladosP/TrasladoP/@ImpuestoP
                    //doclog("CFDI:validar VALIDAR PAGOS","cfdiLog",["TipoCambioP"=>$pitc,"BaseP"=>$pitb,"ImporteP"=>$piti,"ImpuestoP"=>$pitm,"numPagos"=>$numPagos,"monedaDR"=>$monedas,"Retenciones"=>$rets,"SumaImpuestoPagos"=>$valSum,"MontoTotalPagos"=>$ptot,"Diferencia"=>$totDif,"steps"=>$steps,"folio"=>$this->gdbData("folio",null,"trim"),"FECHA"=>$this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"),"tc"=>$tipoComprobante,"UUID"=>$this->gdbData("uuid",null,"strtoupper"),"codigoProveedor"=>$this->cache["codigoProveedor"]??$this->data["facturas"]["codigoProveedor"]??"-","receptor"=>$this->cache["aliasGrupo"]??"-"]);
                    /*
                    if(is_array($pitm)) {
                        foreach ($pitm as $idx => $pv) {
                            if ($pv==="002") { // IVA
                                //$valSum+=$pitc[$idx]
                                $pitb=$pitb[$idx];
                                $piti=$piti[$idx];
                                if(is_array($pitc)) $pitc=$pitc[$idx]??1;
                                //$pitt=$pitt[$idx];
                                break;
                            }
                        }
                        if (is_array($pitb)) {
                            $pitb=0; $piti=0; $pitc=1; //$pitt=0;
                        }
                    }
                    $pitb=(empty($pitb)?0:+$pitb);
                    $piti=(empty($piti)?0:+$piti);
                    $pitc=(empty($pitc)?1:+$pitc);
                    //$pitt=(empty($pitt)?1:+$pitt);
                    $valSum=$pitc*($pitb+$piti);
                    */
                    if (isset($rets[0])) {
                        if (is_array($rets)) foreach($rets as $idx=>$val) $valSum-=(+$val);
                        else $valSum-=(+$rets);
                    }
                    $epsilon=5; // ... se había considerado $10 por cálculo de aproximaciones pero en junta con Jaime el dijo que sólo $5...  0.15; // 0.015 ... cambiado a 15 centavos por acuerdo interno 29 feb 2024
                    $monedas = $this->cache["monDR"]??[];
                    $nMon=count($monedas);
                    $isForeignCurrency=$nMon>1||($nMon>0&&!isset($monedas["MXN"]));
                    $numPagos=$this->cache["numPagos"]??1;
                    $totDif=abs($valSum-$ptot);
                    if ($totDif>$epsilon) {
                        doclog(($isForeignCurrency)?"CFDI:validar Impuestos a pagar declarados en moneda extranjera":"CFDI:validar Impuestos a pagar con diferencia de centavos","cfdi",["TipoCambioP"=>$pitc,"BaseP"=>$pitb,"ImporteP"=>$piti,"ImpuestoP"=>$pitm,"numPagos"=>$numPagos,"monedaDR"=>$monedas,"Retenciones"=>$rets,"SumaImpuestoPagos"=>$valSum,"MontoTotalPagos"=>$ptot,"Diferencia"=>$totDif,/*"steps"=>$steps,*/"folio"=>$this->gdbData("folio",null,"trim"),"FECHA"=>$this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"),"tc"=>$tipoComprobante,"UUID"=>$this->gdbData("uuid",null,"strtoupper"),"codigoProveedor"=>$this->cache["codigoProveedor"]??$this->data["facturas"]["codigoProveedor"]??"-","receptor"=>$this->cache["aliasGrupo"]??"-"]);
                        $lastErrTxt=self::getLastError()["texto"];
                        if (!isset($lastErrTxt[0])||strpos($lastErrTxt, "Ya existe un comprobante en el sistema")===false) {
                            if ($isForeignCurrency)
                                $err="Debe desglosar los impuestos a pagar en moneda nacional para que coincida con el monto total";
                            else $err="El monto total de pago no coincide con lo indicado en impuestos";
                            $err.=" a pagar. Consulte a su agente de Compras sobre los datos requeridos en su XML.";
                            $logList[] = $err;
                            $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                            self::setLastErrorText($err);
                        }
                    } // else doclog("CFDI:validar Suma de Impuestos Valida","cfdi",["TipoCambioP"=>$pitc,"BaseP"=>$pitb,"ImporteP"=>$piti,"ImpuestoP"=>$pitm,"numPagos"=>$numPagos,"monedaDR"=>$monedas,"Retenciones"=>$rets,"SumaImpuestoPagos"=>$valSum,"MontoTotalPagos"=>$ptot,"Diferencia"=>$totDif]);
                }
            }
        } else if ($tipoComprobante==="i" || $tipoComprobante==="e") {
            $usoCFDI=mb_strtoupper($receptor["@usocfdi"]??"");
            $this->data["facturas"]["usoCFDI"]=$usoCFDI;
            doclog("CFDI:validar validaUsoCFDI","cfdiLog",["usoCFDI"=>$usoCFDI,"tc"=>$tipoComprobante,"receptorValido"=>($esReceptorValido?"true":"false")]);
            if ($usoCFDI==="CP01") {
                $err = "El USO CFDI en ingresos no puede ser PAGOS (CP01 no aceptable)";
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            } else if ($usoCFDI==="CN01") {
                $err = "El USO CFDI en ingresos no puede ser NOMINA (CN01 no aceptable)";
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            } else if ($esReceptorValido && $usoCFDI==="P01") {
                $uid=getUser()->id;
                global $infObj;
                if (!isset($infObj)) { require_once "clases/InfoLocal.php"; $infObj=new InfoLocal(); }
                if (!$infObj->exists("nombre='CFDI_ALLOWP01_{$uid}' and valor='1'")) {
                    $err = "El USO CFDI del receptor debe estar definido (P01 no aceptable)";
                    $logList[] = $err;
                    $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                    self::setLastErrorText($err);
                }
            }
            // INGRESO (es factura) o EGRESO (es nota de credito)
            $err = $this->validaMetodoDePago (
                $this->gdbData("metodo_pago","metodoDePago"), 
                $this->gdbData("forma_pago","formaDePago"),
                $tipoComprobante);
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
        
            $err = $this->validaTipoCambio();
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
            $err = $this->validaConceptos();
            if (isset($err[0])) {
                $logList[] = $err;
                $errlist[] = "<TD class='lefted wordwrap'>$err</TD>";
                self::setLastErrorText($err);
            }
        }
        
        if (isset($logList[0])) {
            array_walk($logList, function(&$val) {
                $val=strip_tags($val);
            });
            doclog("CFDI:validar INVALIDO","cfdi",["VERSION"=>$this->gdbData("version"),"SERIE"=>$this->gdbData("serie",null,"trim"),"FOLIO"=>$this->gdbData("folio",null,"trim"),"FECHA"=>$this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"),"TIPO_COMPROBANTE"=>$tipoComprobante,"UUID"=>$this->gdbData("uuid",null,"strtoupper"),"EMISOR"=>$this->get("emisor"),"codigoProveedor"=>$this->cache["codigoProveedor"]??$this->data["facturas"]["codigoProveedor"]??"-","RECEPTOR"=>$this->get("receptor"),"TOTAL"=>$this->gdbData("total"),"SUBTOTAL"=>$this->gdbData("subtotal"),"logList"=>$logList,"errList"=>$errlist]);
        }
        if (isset($errlist) && isset($errlist[0])) {
            $errtab = "<table class='cfdiErrorList mbpi' type='".(is_array($errlist)?"array":gettype($errlist))."' count='".count($errlist)."'>";
            foreach ($errlist as $erritem) $errtab .= "<tr>$erritem</tr>";
            $errtab .= "</table>";
            $this->error($errtab, "");
        }
        $this->cache["trace"][]="END validar";
//            $this->validaCadenaOriginal();
        //clog2end("CFDI.Object.validar");
    }
    private function gdbData($key,$dbKey=null,$modFunc=null,$modifiers=["type"=>"facturas"]) {
        if (empty($modifiers["type"])) $modifiers["type"]="facturas";
        $type=$modifiers["type"];
        if (!isset($this->data[$type])) $this->data[$type]=[];
        if (empty($dbKey)) $dbKey=$key;
        if (empty($modifiers["force"])&&isset($this->data[$type][$dbKey])) return $this->data[$type][$dbKey];
        $val=$this->get($key);
        if (empty($val)) return null;
        if (isset($modFunc)) $val=$modFunc($val);
        if (empty($val)) return null;
        $this->data[$type][$dbKey]=$val;
        return $val;
    }
    public static function getDBDateFromXMLDate($xmlStrDate) {
        //return substr($xmlStrDate, 0, 10)." ".substr($xmlStrDate, 11);
        $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
        try {
            if (is_null($xmlStrDate)) {
                self::setLastErrorText("Fecha en el XML nula");
                //$this->cache["errors"][]=["message"=>"Fecha en el XML nula"];
                doclog("CFDI:getDBDateFromXMLDate Fecha ingresada nula","error",$baseData);
                return null;
            }
            if (is_array($xmlStrDate)) {

            }
            $dti=DateTimeImmutable::createFromFormat("Y-m-d\TH:i:s",$xmlStrDate);
            if (is_bool($dti)) {
                self::setLastErrorText("Fecha en el XML inválida");
                //$this->cache["errors"][]=["message"=>"Fecha en el XML inválida","value"=>$xmlStrDate];
                doclog("CFDI:getDBDateFromXMLDate CreateFromFormat con Error","error",$baseData+["argumento"=>$xmlStrDate,"dateErrors"=>DateTimeImmutable::getLastErrors(),"resultado"=>($dti?"true":"false")]);
                return null;
            }
            $fecha = $dti->format("Y-m-d H:i:s");

            // Fatal error: Uncaught Error: Call to a member function format() on bool in C:\Apache24\htdocs\invoice\clases\CFDI.php:1367 Stack trace: #0 C:\Apache24\htdocs\invoice\clases\CFDI.php(2220): CFDI::getDBDateFromXMLDate() #1 C:\Apache24\htdocs\invoice\clases\CFDI.php(1009): CFDI->validaFecha() #2 C:\Apache24\htdocs\invoice\configuracion\altafactura01_submitxml.php(83): CFDI->validar() #3 C:\Apache24\htdocs\invoice\configuracion\altafactura.php(28): include('C:\\Apache24\\htd...') #4 C:\Apache24\htdocs\invoice\menu_accion.php(8): include('C:\\Apache24\\htd...') #5 C:\Apache24\htdocs\invoice\index.php(525): include('C:\\Apache24\\htd...') #6 {main} thrown in C:\Apache24\htdocs\invoice\clases\CFDI.php on line 1367


            if (empty($fecha)) {
                self::setLastErrorData("value",$xmlStrDate);
                self::setLastErrorText("No se identifica la fecha de la factura en el XML");
                //$this->cache["errors"][]=["message"=>"No se identifica la fecha de la factura en el XML","name"=>"fecha","value"=>$xmlStrDate];
                doclog("CFDI:getDBDateFromXMLDate Fecha Vacía","error",$baseData+["argumento"=>$xmlStrDate,"dateErrors"=>DateTimeImmutable::getLastErrors()]);
                return null;
            }
            return $fecha;
        } catch (Exception $ex) {
            self::setException($ex);
            self::setLastErrorData("value",$xmlStrDate);
            self::setLastErrorText("No se reconoce el formato de fecha de la factura");
                //$this->cache["errors"][]=["message"=>"No se reconoce el formato de fecha de la factura ($fecha)","name"=>"fecha","value"=>$xmlStrDate,"error"=>getErrorData($ex)];
            doclog("CFDI:getDBDateFromXMLDate Fecha genera Excepcion","error",$baseData+["argumento"=>$xmlStrDate,"dateErrors"=>DateTimeImmutable::getLastErrors(),"error"=>getErrorData($ex)]);
            return null;
        }
    }
    public function getUbicacion() {
        $fecha=$this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate");
        if (empty($fecha)) {
            $this->cache["errors"][]=["message"=>"No se identifica la fecha de la factura en el XML","name"=>"fecha"];
        }
        $alias=$this->cache["aliasGrupo"]??null;
        if (empty($alias)) {
            $this->cache["errors"][]=["message"=>"La empresa receptora no puede ser identificada","name"=>"alias"];
            return null;
        }
        $tc=$this->data["facturas"]["tipoComprobante"];
        $anio=substr($fecha,0,4);
        $mes=substr($fecha,5,2);
        $carpeta=$tc[0]==="p"?"recibos/":"archivos/";
        $aliasDir=$alias."/";
        $anioDir=$anio."/";
        $ubicacion=$carpeta.$aliasDir.$anioDir.$mes;
        $ubiDir=$ubicacion."/";
        $rutaBase=$_SERVER['DOCUMENT_ROOT'];
        if (!is_dir($rutaBase.$ubicacion)) {
            if (mkdir($rutaBase.$ubicacion, 0777, true)) {
                chmod($rutaBase.$carpeta.$alias, 0777);
                chmod($rutaBase.$carpeta.$aliasDir.$anio, 0777);
                chmod($rutaBase.$ubicacion, 0777);
                copy("{$rutaBase}{$carpeta}index.php", "{$rutaBase}{$ubicacionDir}index.php");
            } else {
                $this->cache["errors"][]=["message"=>"No pudo crearse la ruta para guardar el xml","name"=>"ubicacion","value"=>$ubicacion];
                return null;
            }
        }
        if (!file_exists("{$rutaBase}{$carpeta}{$aliasDir}index.php")) {
            copy("{$rutaBase}{$carpeta}index.php", "{$rutaBase}{$carpeta}{$aliasDir}index.php");
        }
        if (!file_exists("{$rutaBase}{$carpeta}{$aliasDir}{$anioDir}index.php")) {
            copy("{$rutaBase}{$carpeta}index.php", "{$rutaBase}{$carpeta}{$aliasDir}{$anioDir}index.php");
        }
        $this->data["facturas"]["ciclo"]=$anio;
        $this->data["facturas"]["ubicacion"]=$ubicacion;
        return $ubicacion;
    }
    public function prepareData() {
        global $invObj;
        $this->cache["registroexiste"]=FALSE;
        $uuid=$this->gdbData("uuid",null,"strtoupper");
        if (isset($uuid[50])) {
            $uuid=substr($uuid, -50);
            $this->data["facturas"]["uuid"]=$uuid;
        }
        $folio=$this->gdbData("folio",null,"trim");
        if (isset($folio[50])) {
            $folio=substr($folio, -50);
            $this->data["facturas"]["folio"]=$folio;
        }
        $serie=$this->gdbData("serie",null,"trim");
        if (isset($serie[50])) {
            $serie=substr($serie, -50);
            $this->data["facturas"]["serie"]=$serie;
        }
        $this->cache["errors"]=[];
        $tc=$this->gdbData("tipo_comprobante","tipoComprobante","strtolower")[0];
        $tcP="El";
        $tcN="Comprobante";
        $tcs="o";
        $fileTCPrefix="";
        $xmlLoadPath=$this->cache["xmlLoadFilePath"];
        switch($tc) {
            case "e": $fileTCPrefix="NC_";
                $tcP="La";
                $tcN="Nota";
                $tcs="a";
                $hayNotas=true;
                break;
            case "p":
                $tcN="Compr.Pago";
                $fileTCPrefix="RP_";
                $hayPagos=true;
                break;
            case "i":
                $tcP="La";
                $tcN="Factura";
                $tcs="a";
                $hayFacturas=true;
                break;
            case "t":
                $tcN="Traslado";
                $hayTraslados=true;
                break;
            default: {
                $this->cache["errors"][] = ["message"=>"Tipo de Comprobante desconocido","name"=>"tipoComprobante","value"=>$this->get("tipoComprobante"),"filePath"=>$xmlLoadPath,"errpath"=>"conError"];
                return false;
            }
        }
        $tcn=strtolower($tcN);
        if (empty($folio)) {
            $uuidLen=strlen($uuid);
            if ($uuidLen>=10) $fileSuffix=substr($uuid, -10);
            else if ($uuidLen>0) $fileSuffix=$uuid;
            else {
                $this->cache["errors"][] = ["message"=>"$tcP $tcn no tiene folio único ni UUID","name"=>"uuid","value"=>$uuid,"filePath"=>$xmlLoadPath,"errpath"=>"conError"];
                return false;
            }
        } else if (preg_match('/[^a-zA-Z0-9\-\_\.]/', $folio)) {
            $this->cache["errors"][] = ["message"=>"El folio solo puede contener letras, numeros, guiones y puntos.","name"=>"folio","value"=>$folio,"filePath"=>$xmlLoadPath,"errpath"=>"conError"];
            return false;
        } else {
            $fileSuffix=$folio;
            if (isset($fileSuffix[10])) $fileSuffix=substr($fileSuffix, -10);
        }
        $rfcEmisor=$this->cache["rfcProveedor"];

        //$rfcReceptor=$this->data["facturas"]["rfcGrupo"];
        //$usoCFDI=$this->data["facturas"]["usoCFDI"];
        $nombreOriginal=$this->cache["xmlOriginalName"];
        if (isset($nombreOriginal[100])) $nombreOriginal=substr($nombreOriginal, 0, 100);
        $this->data["facturas"]["nombreOriginal"]=$nombreOriginal;
        if (isset($fileSuffix[0])) {
            $this->data["facturas"]["nombreInterno"]=$rfcEmisor."_".$fileTCPrefix.$fileSuffix;
            if (isset($this->data["facturas"]["nombreInterno"][50])) $this->data["facturas"]["nombreInterno"]=substr($this->data["facturas"]["nombreInterno"], 0, 50);
            $this->data["facturas"]["nombreInternoPDF"]=$fileTCPrefix.$fileSuffix.$rfcEmisor;
            if (isset($this->data["facturas"]["nombreInternoPDF"][50])) $this->data["facturas"]["nombreInternoPDF"]=substr($this->data["facturas"]["nombreInternoPDF"], 0, 50);
            $ubicacion=$this->getUbicacion();
        }
        if (isset($ubicacion[0])) {
            if (isset($ubicacion[100])) {
                $ubicacion=substr($ubicacion, 0, 100);
            }
            $this->data["facturas"]["ubicacion"]=$ubicacion;
        } else {
            $this->cache["errors"][] = ["message"=>"Información insuficiente para definir ubicacion","data"=>$this->data["facturas"],"fileSuffix"=>$fileSuffix??"","name"=>"ubicacion","value"=>"","filePath"=>$xmlLoadPath,"errpath"=>"conError"];
            return false;
        }
        if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
        $respuesta=$invObj->consultaBase($uuid);
        if (isset($respuesta) && isset($respuesta["status"])) {
            if ($respuesta["status"]==="Temporal") {
                global $solObj;
                if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
                if ($solObj->exists("idFactura='$respuesta[id]'")) {
                    $this->cache["errors"][]=["message"=>"Ya existe una solicitud de pago para esta factura","name"=>"idFactura","value"=>$respuesta["id"],"filePath"=>$xmlLoadPath,"errpath"=>"yaExiste"];
                    return false;
                }
                $invObj->deleteRecord(["id"=>$respuesta["id"],"status"=>"Temporal"]);
                $respuesta=null;
            } else {
                $errmsg="$tcP $tcN ya est&aacute; registrad$tcs";
                $this->cache["errors"][]=["message"=>"{$errmsg} en el sistema","name"=>"filename","value"=>$this->data["facturas"]["nombreOriginal"],"errpath"=>"yaExiste"];
                return false;
            }
        }
        $xmlName=$this->data["facturas"]["nombreInterno"];
        $rutaBase=$_SERVER['DOCUMENT_ROOT'];
        $xmlPath=$rutaBase.$ubicacion.$xmlName.".xml";
        if (file_exists($xmlPath)) {
            $invData=$invObj->getData("nombreInterno='$xmlName' and ubicacion='$ubicacion'", 0, "id,uuid,serie,folio");
            if (isset($invData[0]["id"])) $invData=$invData[0];
            if (isset($invData["id"])) {
                if ($uuid===$invData["uuid"]) {
                    $this->cache["errors"][]=["message"=>"No debería haber otro comprobante con el mismo uuid","name"=>"filename","value"=>$this->data["facturas"]["nombreOriginal"],"oldInvoice"=>$invData,"errpath"=>"yaExiste"];
                    return false;
                }
                if ($serie===$invData["serie"]) { // Es posible que el folio truncado se repita, aunque el real fuera diferente, pero solo se podrá permitir ingresarlo si la serie es diferente 
                    $this->cache["errors"][]=["message"=>"No debería haber otro comprobante con la misma serie","name"=>"filename","value"=>$this->data["facturas"]["nombreOriginal"],"oldInvoice"=>$invData,"errpath"=>"yaExiste"];
                    return false;
                }
                $flen=strlen($fileSuffix);
                $slen=strlen($serie);
                $dif=$flen+$slen-10;
                if($dif>0) {
                    $fdif=0; $sdif=0;
                    if ($slen>=3) {
                        if ($flen>=7) {
                            $fdif=-7;
                            $sdif=3;
                            $fileSuffix=substr($fileSuffix, -7);
                            $serie=substr($serie, 0, 3);
                        } else $sdif=-$dif;
                    } else $fdif=$dif;
                    if ($fdif!=0) $fileSuffix=substr($fileSuffix, $fdif);
                    if ($sdif!=0) $serie=substr($serie, 0, $sdif);
                }
                $fileSuffix=$serie.$fileSuffix;
                $this->data["facturas"]["nombreInterno"]=$rfcEmisor."_".$fileTCPrefix.$fileSuffix;
                $this->data["facturas"]["nombreInternoPDF"]=$fileTCPrefix.$fileSuffix.$rfcEmisor;
            } // else hay archivo pero no factura, se puede continuar
        } // else no existe el archivo
        $rfcReceptor=$this->cache["rfcGrupo"];
        $total=$this->gdbData("total");
        global $_esPruebas;
        if (!$_esPruebas) { try {
            if (empty($respuesta)||$respuesta["cfdi"][0]!=="S") {
                $respuesta=$invObj->consultaServicio($rfcEmisor,$rfcReceptor,$total,$uuid);
            }
            if (isset($respuesta["cfdi"])) {
                if (isset($respuesta["error"])) {
                    $this->cache["errors"][]=["message"=>$respuesta["mensaje"],"rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"data"=>$respuesta,"errpath"=>"conError"];
                } else {
                    $this->data["facturas"]["mensajeCFDI"]=$respuesta["cfdi"];
                    $this->data["facturas"]["estadoCFDI"]=$respuesta["estado"];
                    $this->data["facturas"]["cancelableCFDI"]=$respuesta["escancelable"]??"";
                    $this->data["facturas"]["canceladoCFDI"]=$respuesta["estatuscancelacion"]??"";
                    if (!isset($respuesta["cfdi"][0])) $this->cache["errors"][]=["message"=>"No se obtuvo respuesta del SAT. Intente de nuevo más tarde","rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"data"=>$respuesta,"errpath"=>"reintentar"];
                    else if ($respuesta["cfdi"][0]!="S") {
                        if (substr($respuesta["cfdi"],0,7)==="N - 602") $this->cache["errors"][]=["message"=>"No se localizó el comprobante en los registros del SAT. Intente de nuevo más tarde","rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"data"=>$respuesta,"errpath"=>"reintentar"];
                        else $this->cache["errors"][]=["message"=>"Comprobante del SAT no satisfactorio: $respuesta[cfdi]","rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"data"=>$respuesta,"errpath"=>"conError"];
                    } else if ($respuesta["estado"]!="Vigente") $this->cache["errors"][]=["message"=>"Status del SAT no vigente: $respuesta[estado]","rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"data"=>$respuesta,"errpath"=>"conError"];
                }
            } else $this->cache["errors"][]=["message"=>"No se obtuvo respuesta del SAT. Reintente más tarde","rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"data"=>$respuesta,"errpath"=>"reintentar"];
        } catch (Exception $e) {
            $this->cache["errors"][]=["message"=>"No se obtuvo respuesta del SAT","rfcEmisor"=>$rfcEmisor,"rfcReceptor"=>$rfcReceptor,"total"=>$total,"uuid"=>$uuid,"error"=>getErrorData($e),"errpath"=>"reintentar"];
        } }
        if (!isset($this->cache["errors"][0]) && $tc!=="p") {
            $subtotal=$this->gdbData("subtotal");
            $descuento=$this->gdbData("descuento","importeDescuento");
            $impRetenido=$this->gdbData("totalimpuestosretenidos","impuestoRetenido");
            $impTraslado=$this->gdbData("totalimpuestostrasladados","impuestoTraslado");
            $epsilon=0.015; // 0.015 ... cambiado a 15 centavos por acuerdo interno 29 feb 2024
            $sum=$subtotal-$descuento-$impRetenido+$impTraslado;
            $total=+$total;
            $dif=Math.abs($total-$sum);
            /* DESHABILITAR TEMPORALMENTE para subir facturas con ISH (Impuestos Locales Complementarios) */
            if ($dif>$epsilon) {
                $errmsg="El monto total $".number_format($total,2)." no coincide con suma de subtotal $".number_format($subtotal,2);
                if ($descuento!==0) $errmsg.=" -descuento $".number_format($descuento,2);
                if($impTraslado!==0) $errmsg.=" +impuestos trasladados $".number_format($impTraslado,2);
                if ($impRetenido!==0) $errmsg.=" -impuestos retenidos $".number_format($impRetenido,2);
                $errmsg.=" = $".number_format($sum,2)." (dif. $".number_format($dif,2).")";
                $this->cache["errors"][]=["message"=>$errmsg,"data"=>["total"=>$total,"subtotal"=>$subtotal,"descuento"=>$descuento,"impRet"=>$impRetenido,"impTra"=>$impTraslado,"uuid"=>$uuid],"errpath"=>"conError"];
            }
        }
        $fecha=date("Y-m-d H:i:s");
        if (!isset($this->cache["errors"][0]) && $tc!=="p") {
            $conceptos=$this->get("conceptos");
            if (isset($conceptos["@cantidad"])) $conceptos=[$conceptos];
            $numConceptos=count($conceptos);
            $sumtotal=0; $sumsubtotal=0; $sumdescuento=0; $sumtraslados=0; $sumeretenciones=0;$fillables="";
            foreach ($conceptos as $cncIdx=>$concepto) {
                $conceptArray=[];$conceptArray[0]=0;
                $cncIdf = "0_$cncIdx";
                $cncName="concepto$cncIdf";
                $fillables.="\"$cncName\",";
                $cncCantidad=+$concepto["@cantidad"];
                $cncNum=$cncIdx+1;
                if (isset($concepto["@unidad"])) $unidad=htmlentities($concepto["@unidad"]);
                else if (isset($concepto["@claveunidad"])) {
                    $claveUnidad=$concepto["@claveunidad"];
                    require_once "clases/catalogoSAT.php";
                    $nombreClaveUnidad=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD, "codigo", $claveUnidad, "nombre");
                    if (!empty($nombreClaveUnidad))
                        $unidad=htmlentities($nombreClaveUnidad);
                    else $unidad="N/D";
                } else $unidad="N/D";
                $descripcion=htmlentities($concepto["@descripcion"],ENT_QUOTES);
                $valueDescripcion=strtok(trim($descripcion),"\r\n");
                if(is_string($valueDescripcion)&&!isset($valueDescripcion[0]))
                    $valueDescripcion=strtok("\r\n");
                if ($valueDescripcion==false) $valueDescripcion="";
                if($esTraslado) $conceptArray[1]="S/CODIGO";
                else {
                    $infoPrv=$this->cache["infoProveedor"]??[];
                    if (empty($infoPrv["d"])) $conceptArray[1]="";
                    else {
                        $trimdesc=trim($descripcion);
                        $rspIdx=strrpos($trimdesc, " ");
                        if ($rspIdx>=0) $conceptArray[1]=trim(substr($trimdesc, $rspIdx+1));
                        else $conceptArray[1]="";
                    }
                }
                if (isset($concepto["@claveprodserv"][0])) {
                    $claveProdServ=$concepto["@claveprodserv"];
                    require_once "clases/catalogoSAT.php";
                    $nombreClaveProdServ = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV, "codigo", $claveProdServ, "descripcion");
                }
                $conceptArray[2]=$cncCantidad;
                $conceptArray[3]=$unidad;
                $conceptArray[4]=$claveUnidad;
                $conceptArray[5]=$claveProdServ;
                $conceptArray[6]=$valueDescripcion;
                $valorUnitario=+$concepto["@valorunitario"];
                $conceptArray[7]=$valorUnitario;
                $importe=+$concepto["@importe"];
                $resultado=$cncCantidad*$valorUnitario;
                $sumsubtotal+=$importe;
                $cncImporteValido = (abs($importe-$resultado)<$epsilon);
                $conceptArray[8]=$importe;
                $descuento=0;
                if (isset($concepto["@descuento"])) {
                    $descuento=+trim($concepto["@descuento"]);
                }
                $conceptArray[9]=$descuento;
                $sumaTrasladosConcepto=0;
                $sumaRetencionesConcepto=0;
                if (isset($concepto["Impuestos"])) {
                    $ccImps=$concepto["Impuestos"];
                    if (isset($ccImps["Traslados"])) {
                        foreach ($ccImps["Traslados"] as $traslado) {
                            if (isset($traslado["@importe"])) $sumaTrasladosConcepto+=+$traslado["@importe"];
                        }
                    }
                    if (isset($ccImps["Retenciones"])) {
                        foreach ($ccImps["Retenciones"] as $retencion) {
                            if (isset($retencion["@importe"])) $sumaRetencionesConcepto+=+$retencion["@importe"];
                        }
                    }
                }
                $conceptArray[10]=$sumaTrasladosConcepto;
                $conceptArray[11]=$sumaRetencionesConcepto;
                $sumdescuento+=$descuento;
                $sumtraslados+=$sumaTrasladosConcepto;
                $sumretenciones+=$sumaRetencionesConcepto;
                $cTotal=$importe-$descuento+$sumaTrasladosConcepto-$sumaRetencionesConcepto;
                $sumtotal+=$cTotal;
                //validaciones subtotal, descuento, impuestos trasladados, impuestos retenidos, total
                $this->data["conceptos"][]=$conceptArray;
                // 0 = $factura["id"]
                // 1 = strtoupper(htmlentities($ccodigo))
                // 2 = $conceptos[$cix]['cantidad']
                // 3 = $conceptos[$cix]['unidad']
                // 4 = $conceptos[$cix]['claveUnidad']
                // 5 = $conceptos[$cix]['claveProdServ']
                // 6 = $cdescripcion
                // 7 = $conceptos[$cix]['valorUnitario']
                // 8 = $cimporte
                // 9 = $cdescuento
                //10 = $ctraslado
                //11 = $cretencion
            }
        }
        if (!isset($this->cache["errors"][0]) && $tc==="p") {
            global $cpyObj, $dpyObj, $solObj;
            $this->data["facturas"]["saldoReciboPago"]=$this->get("pago_monto_total");
            $this->data["facturas"]["fechaReciboPago"]=null;
            $pagos=$this->get("pagos");
            if (isset($pagos["@fechapago"]))
                $pagos=[$pagos];
            foreach ($pagos as $pgIdx => $pgItem) {
                $cpgArr=["fechaPago"=>$pgItem["@fechapago"],"montoPago"=>$pgItem["@monto"],"monedaPago"=>$pgItem["@monedap"],"tipocambioPago"=>$pgItem["tipocambiop"]??1];
                if (!isset($this->data["facturas"]["fechaReciboPago"][0]) || strcmp($this->data["facturas"]["fechaReciboPago"],$cpgArr["fechaPago"])<0) {
                    $this->data["facturas"]["fechaReciboPago"]=$cpgArr["fechaPago"];
                }
                $docsRel=$pgItem["DoctoRelacionado"]??null;
                if (isset($docsRel["@iddocumento"]))
                    $docsRel=[$docsRel];
                foreach ($docsRel as $drIdx => $drItem) {
                    $pgUUID=strtoupper($drItem["@iddocumento"]);
                    $invPgD=$invObj->getData("uuid='$pgUUID'",0,"id,idReciboPago,fechaReciboPago,saldoReciboPago,status,statusn");
                    if (isset($invPgD[0]["id"])) $invPgD=$invPgD[0];
                    if (!isset($invPgD["id"])) {
                        $this->cache["errors"][]=["message"=>"No se ha registrado una factura relacionada al complemento de pago","uuid"=>$uuid, "pagoUUID"=>$pagoUUID,"xmlPath"=>$xmlPath,"errpath"=>"reintentar"];
                        break;
                    }
                    if ($invPgD["status"]==="Temporal") {
                        if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
                        if ($solObj->exists("idFactura=$invPgD[id]")) {
                            $this->cache["errors"][]=["message"=>"No se ha registrado correctamente una factura del complemento de pago","name"=>"idFactura","value"=>$respuesta["id"],"errpath"=>"yaExiste"];
                            break;
                        }
                    }
                    $invPgDId=$invPgD["id"];
                    $drNumPar=$drItem["@numparcialidad"];
                    if (!isset($dpyObj)) { require_once "clases/DPagos.php"; $dpyObj=new DPagos(); }
                    $dpyData=$dpyObj->getData("idFactura=$invPgDId and numParcialidad=$drNumPar");
                    if ($dpyData[0]["id"]) {
                        $this->cache["errors"][]=["message"=>"El pago ya fue registrado","name"=>"idFactura","value"=>$invPgDId,"errpath"=>"yaExiste"];
                        break;
                    }
                    $dpgArr=["numParcialidad"=>$drNumPar,"saldoAnterior"=>$drItem["@impsaldoant"],"impPagado"=>$drItem["@imppagado"],"saldoInsoluto"=>$drItem["@impsaldoinsoluto"],"moneda"=>$drItem["@monedadr"],"equivalencia"=>$drItem["@equivalenciadr"]??1];
                    $dpgArr["idFactura"]=$invPgDId;
                    $hasPaydateFact=isset($invPgD["fechaReciboPago"][0]);
                    //$hasCPayDate
                    if ((!isset($invPgD["fechaReciboPago"][0])) || strcmp($invPgD["fechaReciboPago"],$cpgArr["fechaPago"])<0) {
                        $cpgArr+=$dpgArr; // estos campos se van a eliminar posteriormente... checar que esto si sea valido, si en el loop puede asignarse mas de una vez, dependiendo de las fechas de pago
                        $pgArr=["id"=>$invPgD["id"],"fechaReciboPago"=>$cpgArr["fechaPago"],"saldoReciboPago"=>$cpgArr["@impsaldoinsoluto"]];
                    }
                    $pgFolio=$drItem["@folio"]; $pgSerie=$drItem["@serie"];
                }
                //    if (!isset($cpgArr["idFactura"]));
                //    $
                //    $this->data["pagos"][]=$pgArr;
                //}
                if (isset($this->cache["errors"][0])) break;
                $this->data["cpagos"][]=$cpgArr;
            }
            if (isset($this->cache["errors"][0])) {
                // ToDo: Config save other missing general 'pagos' data
            }
////////////////////////////////////////////////
            $pagos=$this->get("pago_doctos");
            if (isset($pagos["@iddocumento"]))
                $pagos=[$pagos];
            $numPagos=$this->cache["numPagos"]??count($pagos);
            $timeStart=lapse(true);
            set_time_limit(90);
            $monedas=[];
            foreach ($pagos as $paidIdx=>$pago) {
                if ((lapse()-$timeStart)>60) set_time_limit(90);
                $pagoMon=strtoupper($pago["@monedadr"]);
                //if (!in_array($pagoMon, $monedas)) $monedas[]=$pagoMon;
                if (!isset($monedas[$pagoMon])) $monedas[$pagoMon]=1;
                else $monedas[$pagoMon]++;
                $pagoUUID=strtoupper($pago["@iddocumento"]);
                $where=$invObj->getWhereCondition("uuid",$pagoUUID)."statusn is not null";
                $invPData=$invObj->getData($where, 0, "id,statusn");
                if (!isset($invPData[0])) {
                    $this->cache["errors"][]=["message"=>"Se requiere que todas las facturas en el Recibo de Pago esten dadas de alta en el portal.","uuid"=>$pagoUUID,"errpath"=>"conError"];
                    break;
                }
                $invPData=$invPData[0];
                if ($invPData["statusn"]>=Facturas::STATUS_RECHAZADO) {
                    $this->cache["errors"][]=["message"=>"Se requiere que todas las facturas referidas en el Recibo de Pago sean válidas y estén vigentes","id"=>$invPData["id"],"statusn"=>$invPData["statusn"],"uuid"=>$pagoUUID,"errpath"=>"conError"];
                    break;
                }
                // VALIDAR
                //$pagoSaldo=+($pago["@imppagado"]??"0");
                //$pstatusn=Facturas::STATUS_RECPAGO;
                //$pstatus=Facturas::statusnToDetailStatus($pstatusn);
                //$pagoArray=["id"=>$invPData["id"]/*,"uuid"=>$pagoUUID,"idReciboPago"=>0*/,"fechaReciboPago"=>$this->data["facturas"]["fechaReciboPago"],"saldoReciboPago"=>$pagoSaldo];
                //if ($pagoSaldo===0) {
                //    $pagoArray["statusn"]=new DBExpression("statusn|$pstatusn");
                //    $pagoArray["status"]=$pstatus;
                //}
                //$this->data["pagos"][]=$pagoArray;
                $cpagoArray=["idFactura"=>$invPData["id"],"numParcialidad"=>$pago["@numparcialidad"],"saldoAnterior"=>$pago["@impsaldoant"],"impPagado"=>$pago["@imppagado"],"saldoInsoluto"=>$pago["@impsaldoinsoluto"],"moneda"=>$pago["@monedadr"]??"","equivalencia"=>$pago["@equivalenciadr"]??1];
                if ($cpagoArray["equivalencia"]==0) $cpagoArray["equivalencia"]=1;
                $this->data["cpagos"][]=$cpagoArray;
            }
            
            $this->cache["monDR"]=$monedas;
            if (!isset($this->cache["errors"][0])) {
                $montoPagos=$this->get("pago_monto_total");
                if (is_scalar($montoPagos)) {
                    $this->data["facturas"]["saldoReciboPago"]+=+$montoPagos;
                } else foreach ($montoPagos as $monto) {
                    $this->data["facturas"]["saldoReciboPago"]+=+$monto;
                }
            }
        }
        if (!isset($this->cache["errors"][0])) {
            if ($this->cache["registroexiste"] && $invObj->exists("id='$respuesta[id]' && status='Temporal'")) $invObj->deleteRecord(["id"=>$respuesta["id"],"status"=>"Temporal"]);
            $tmpData=$invObj->getData("nombreInterno='$this->data[facturas][nombreInterno]' && status='Temporal'",false,"id,uuid");
            if (isset($tmpData[0]["id"])) {
                global $solObj;
                if (!isset($solObj)) {
                    require_once "clases/SolicitudPago.php";
                    $solObj=new SolicitudPago();
                }
                if ($solObj->exists("idFactura='".$tmpData[0]["id"]."'")) {
                    $this->cache["errors"][]=["message"=>"Ya existe una solicitud de pago para esa factura","data"=>["idFactura"=>$tmpData[0]["id"],"nombreInterno"=>$this->data["facturas"]["nombreInterno"],"uuid"=>$tmpData[0]["uuid"],"newuuid"=>$this->data["facturas"]["uuid"]],"errpath"=>"revisar"];
                } else {
                    $invObj->deleteRecord(["id"=>$tmpData[0]["id"],"status"=>"Temporal"]);
                }
            }
        }
        if (!isset($this->cache["errors"][0])) {
            $this->data["facturas"]["pedido"]="S/PEDIDO";
            $this->data["facturas"]["remision"]="S/REMISION";
            $this->data["facturas"]["fechaCaptura"]=$fecha;
            $cert=$this->gdbData("certificado","noCertificado");
            if (isset($cert[50])) {
                $cert=substr($cert, -50);
                $this->data["facturas"]["noCertificado"]=$cert;
            }
            $tipoCambio=$this->gdbData("tipocambio","tipoCambio");
            if (empty($tipoCambio) && $tipoCambio!=="0") {
                $tipoCambio="0";
                $this->data["facturas"]["tipoCambio"]="0";
            }
            $this->gdbData("moneda");
            //if ($tc==="p"||$tc==="t") { // Ahora tambien hay que aceptar manualmente
            //    $this->data["facturas"]["status"]="Aceptado";
            //    $this->data["facturas"]["statusn"]=Facturas::STATUS_ACEPTADO;
            //} else {
                $this->data["facturas"]["status"]="Pendiente";
                $this->data["facturas"]["statusn"]=Facturas::STATUS_PENDIENTE;
                if ($tc==="i") {
                    $credito=$this->cache["creditoProveedor"]??0;
                    if ($credito<=0) $venceFecha=$fecha;
                    else {
                        $wkd=+date("w"); // dia de la semana empieza en domingo
                        if ($wkd<2) $wkd+=6;
                        else $wkd--; // ajuste para lunes
                        $creditoFix=$credito-($credito%7)+7-$wkd;// se aumentan los días que faltan para ser lunes
                        $venceTS=strtotime($fecha."+ $creditoFix days");
                        $venceFecha=date("Y-m-d",$venceTS);
                    }
                    $this->data["facturas"]["fechaVencimiento"]=$venceFecha;
                }
            //}
            if ($this->cache["registroexiste"]) $this->data["facturas"]["id"]=$respuesta["id"];
            $trasladoTasa=$this->get("traslado_tasa");
            if (is_array($trasladoTasa) && isset($trasladoTasa[0])) $trasladoTasa=$trasladoTasa[0];
            if (!empty($trasladoTasa)) $this->data["facturas"]["tasaIva"]=$trasladoTasa;
            if (!empty($this->data["facturas"]["mensajeCFDI"])) $this->data["facturas"]["consultaCFDI"]=$this->data["facturas"]["fechaCaptura"];
            //$this->data["proceso"]=["modulo"=>"Factura","status"=>"Pendiente","usuario"=>"SISTEMAS","detalle"=>"Alta Masiva por carpeta","fecha"=>$fecha]; // "identif"=>idFactura,
            //global $esDesarrollo;
            //if (!$esDesarrollo) $this->data["proceso"]["usuario"]=getUser()->nombre; // $this->data["proceso"]["detalle"].=" (".getUser()->nombre.")";
            // TODO: Falta datos de tabla Conceptos y lo de altafactura02_insertxml
            //conceptos
        }
        if (!isset($this->cache["errors"][0])) {
        // ** Se mueven los archivos aunque tengan errores, para poder revisarlos si llaman para reclamar
        //Mejor que no se muevan aqui y se manden a la carpeta de errores de autoupload
            $xmlOldPath=$this->cache["xmlLoadFilePath"]??"";
            $xmlNewPath=$ubicacion.$this->data["facturas"]["nombreInterno"].".xml";
            $fileData=[];
            if (isset($xmlOldPath[0]) && isset($xmlNewPath[0]) && $xmlOldPath!==$xmlNewPath) {
                $fileData["oldxml"]=$xmlOldPath;
                $fileData["newxml"]=$xmlNewPath;
                rename($xmlOldPath, $xmlNewPath);
                chmod($xmlNewPath,0666);
            }
            $pdfOldPath=$this->cache["pdfLoadFilePath"]??"";
            $pdfNewPath=$ubicacion.$this->data["facturas"]["nombreInternoPDF"].".pdf";
            if (isset($pdfOldPath[0]) && isset($pdfNewPath[0]) && $pdfOldPath!==$pdfNewPath) {
                $fileData["oldpdf"]=$pdfOldPath;
                $fileData["newpdf"]=$pdfNewPath;
                rename($pdfOldPath, $pdfNewPath);
                chmod($pdfNewPath,0666);
            }
            if (!empty($fileData)) {
                $docname=array_key_exists("newpdf", $fileData)?"pdf":"archivo";
                doclog("REASIGNAR ARCHIVOS", $docname, $baseData+["line"=>__LINE__]+$fileData);
            }
        }
        return !isset($this->cache["errors"][0]);
    } // prepareData function
    public function saveData() {
        global $invObj, $prcObj, $cptObj, $query;
        if (isset($this->cache["errors"][0])) {
            $this->cache["errors"][]=["message"=>"No se guarda por tener errores","data"=>$this->data];
            return false;
        }
        if (!isset($this->data["facturas"]) || !isset(array_keys($this->data["facturas"])[0])) {
            $this->cache["errors"][]=["message"=>"Datos insuficientes para guardar","data"=>$this->data];
            return false;
        }
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        }
        DBi::autocommit(FALSE);
        if (!$invObj->saveRecord($this->data["facturas"])) {
            $dberror=["errno"=>DBi::$errno,"error"=>DBi::$error,"query"=>$query];
            if (!empty(DBi::$errors)) {
                $dberror["data"]=[];
                foreach(DBi::$errors as $sErn=>$sErr) {
                    $dberror["data"][]=["code"=>$sErn,"msg"=>$sErr,"fix"=>DBi::getErrorTranslated($sErn, $sErr)];
                }
            }
            $this->cache["errors"][]=["message"=>"No se pudo guardar el comprobante","data"=>$this->data,"dberror"=>$dberror];
            DBi::rollback();
            return false;
        }
        $id=$invObj->lastId;
        $this->data["facturas"]["id"]=$id;
        if (isset($this->data["conceptos"][0])) {
            foreach ($this->data["conceptos"] as &$concepto) {
                $concepto[0]=$id;
            }
            if (!isset($cptObj)) {
                require_once "clases/Conceptos.php";
                $cptObj = new Conceptos();
            }
            if (!$cptObj->insertMultipleRecords($columns,$this->data["conceptos"])) {
                $dberror=["errno"=>DBi::$errno,"error"=>DBi::$error,"query"=>$query];
                if (!empty(DBi::$errors)) {
                    $dberror["data"]=[];
                    foreach(DBi::errors as $sErn=>$sErr) {
                        $dberror["data"][]=["code"=>$sErn,"msg"=>$sErr,"fix"=>DBi::getErrorTranslated($sErn, $sErr)];
                    }
                }
                $this->cache["errors"][]=["message"=>"Error al guardar los conceptos de la factura", "data"=>$this->data,"dberror"=>$dberror];
                DBi::rollback();
                return false;
            }
        }
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj=new Proceso();
        }
        //$this->data["proceso"]=["modulo"=>"Factura","status"=>"Pendiente","usuario"=>"SISTEMAS","detalle"=>"Alta Masiva por carpeta","fecha"=>$fecha]; // "identif"=>idFactura,
        if (hasUser()) {
            if (getUser()->id==1) $usuario="SISTEMAS";
            else $usuario=getUser()->nombre;
        } else $usuario="noUser";
        if (isset($this->data["pagos"][0])) {
            foreach($this->data["pagos"] as &$pago) {
                $pago["idReciboPago"]=$id;
                if (!$invObj->updateRecord($pago)) {
                    $dberror=["errno"=>DBi::$errno,"error"=>DBi::$error,"query"=>$query];
                    if (!empty(DBi::$errors)) {
                        $dberror["data"]=[];
                        foreach(DBi::errors as $sErn=>$sErr) {
                            $dberror["data"][]=["code"=>$sErn,"msg"=>$sErr,"fix"=>DBi::getErrorTranslated($sErn, $sErr)];
                        }
                    }
                    $this->cache["errors"][]=["message"=>"Error al registrar complemento de pago en facturas","data"=>$this->data,"pago"=>$pago,"dberror"=>$dberror];
                    DBi::rollback();
                    return false;
                }
                $idf=$pago["id"];
                $prcObj->cambioFactura($idf, $pago["status"], $usuario, $pago["fechaReciboPago"], "Comprobante de Pago $id");
            }
        }
        if (isset($this->data["cpagos"][0])) {
            global $cpyObj;
            if (!isset($cpyObj)) { require_once "clases/CPagos.php"; $cpyObj=new CPagos(); }
            foreach ($this->data["cpagos"] as &$cpago) {
                $cpago["idCPago"]=$id;
                if (!$cpyObj->saveRecord($cpago)) {
                    $dberror=["errno"=>DBi::$errno,"error"=>DBi::$error,"query"=>$query];
                    if (!empty(DBi::$errors)) {
                        $dberror["data"]=[];
                        foreach(DBi::errors as $sErn=>$sErr) {
                            $dberror["data"][]=["code"=>$sErn,"msg"=>$sErr,"fix"=>DBi::getErrorTranslated($sErn, $sErr)];
                        }
                    }
                    $this->cache["errors"][]=["message"=>"Error al registrar detalle de complemento de pago en facturas","data"=>$this->data,"pago"=>$pago, "cpago"=>$cpago, "dberror"=>$dberror];
                    DBi::rollback();
                    return false;
                }
            }
        }
        $prcObj->cambioFactura($id, $this->data["facturas"]["status"], $usuario, $this->data["facturas"]["fechaCaptura"], "Alta Masiva Interna");
        DBi::commit();
        return true;
    } // saveData function
    public function appendError($errmessage=null, &$acumErrMessage=null, $separatorTAG="TD") {
        static $errorList=[];
        if (isset($errmessage[0])) {
            $iniTAG = "";
            $endTAG = "";
            if (isset($separatorTAG[0])) { $iniTAG = "<$separatorTAG>"; $endTAG = "</$separatorTAG>"; }
            $errorList[] = $iniTAG.$errmessage.$endTAG;
        }
        if (func_num_args()>=2) {
            switch ($separatorTAG) {
                case "TD": $blockTAG="TABLE"; $rowTAG="TR"; break;
                default: $blockTAG="DIV"; $rowTAG="";
            }
            $acumErrMessage .= "<$blockTAG>";
            foreach ($errorList as $errItem) $acumErrMessage .= "<$rowTAG>$errItem</$rowTAG>";
            $acumErrMessage .= "</$blockTAG>";
            return $acumErrMessage;
        }
        return "";
    }
    public function getNamespaceList() {
        $nsLog="NAMESPACES:";
        foreach ( $this->xpath->query('namespace::*', $this->xmldoc->documentElement) as $node) {
            $nsLog.= "\n - ".$node->nodeValue;
        }
        $nsLog.="\n----------------------------------------";
        $attribs = $this->xmldoc->documentElement->attributes;
        foreach ($attribs as $att) {
            if ($att->prefix==="xsi") {
                $xsiUris = explode(" ", $att->value);
                foreach($xsiUris as $xuri)
                    if (substr($xuri,-4)===".xsd")
                        $nsLog.="\nXSI : $xuri";
            }
        }
        $nsLog.="\n----------------------------------------";
        $tfdQryRes = $this->xpath->query(self::QUERY_TFD);
        if (is_object($tfdQryRes)) {
            $tfdNode = $tfdQryRes->item(0);
            foreach ( $this->xpath->query('namespace::*', $tfdNode) as $node) {
                $nsLog.= "\n - ".$node->nodeValue;
            }
            $nsLog.="\n----------------------------------------";
            $attribs = $tfdNode->attributes;
            foreach ($attribs as $att) {
                if ($att->prefix==="xsi") {
                    $xsiUris = explode(" ", $att->value);
                    foreach($xsiUris as $xuri)
                        if (substr($xuri,-4)===".xsd") $nsLog.="\nXSI : $xuri";
                }
            }
        } else {
            $nsLog .="\nNo se encontró TFD : ".self::QUERY_TFD;
        }
        $cpQryRes = $this->xpath->query(self::QUERY_CARTAPORTE);
        if (is_object($cpQryRes)) {
            $cpNode = $cpQryRes->item(0);
            foreach ($this->xpath->query('namespace::*', $cpNode) as $node) {
                $nsLog.= "\n - ".$node->nodeValue;
            }
            $nsLog.="\n----------------------------------------";
            $attribs = $cpNode->attributes;
            foreach ($attribs as $att) {
                if ($att->prefix==="xsi") {
                    $xsiUris = explode(" ", $att->value);
                    foreach ($xsiUris as $xuri)
                        if (substr($xuri,-4)===".xsd") $nsLog.="\nXSI : $xuri";
                }
            }
        } else {
            $nsLog .="\nNo se encontró CARTAPORTE : ".self::QUERY_CARTAPORTE;
        }
        $cp2QryRes = $this->xpath->query(self::QUERY_CARTAPORTE20);
        if (is_object($cp2QryRes)) {
            $cp2Node = $cp2QryRes->item(0);
            foreach ($this->xpath->query('namespace::*', $cp2Node) as $node) {
                $nsLog.= "\n - ".$node->nodeValue;
            }
            $nsLog.="\n----------------------------------------";
            $attribs = $cp2Node->attributes;
            foreach ($attribs as $att) {
                if ($att->prefix==="xsi") {
                    $xsiUris = explode(" ", $att->value);
                    foreach ($xsiUris as $xuri)
                        if (substr($xuri,-4)===".xsd") $nsLog.="\nXSI : $xuri";
                }
            }
        } else {
            $nsLog .="\nNo se encontró CARTAPORTE20 : ".self::QUERY_CARTAPORTE20;
        }
        $cp3QryRes = $this->xpath->query(self::QUERY_CARTAPORTE30);
        if (is_object($cp3QryRes)) {
            $cp3Node = $cp3QryRes->item(0);
            foreach ($this->xpath->query('namespace::*', $cp3Node) as $node) {
                $nsLog.= "\n - ".$node->nodeValue;
            }
            $nsLog.="\n----------------------------------------";
            $attribs = $cp3Node->attributes;
            foreach ($attribs as $att) {
                if ($att->prefix==="xsi") {
                    $xsiUris = explode(" ", $att->value);
                    foreach ($xsiUris as $xuri)
                        if (substr($xuri,-4)===".xsd") $nsLog.="\nXSI : $xuri";
                }
            }
        } else {
            $nsLog .="\nNo se encontró CARTAPORTE30 : ".self::QUERY_CARTAPORTE30;
        }
        $imlQryRes = $this->xpath->query(self::QUERY_IMPLOCAL);
        if (is_object($imlQryRes)) {
            $imlNode = $imlQryRes->item(0);
            foreach ($this->xpath->query('namespace::*', $imlNode) as $node) {
                $nsLog.= "\n - ".$node->nodeValue;
            }
            $nsLog.="\n----------------------------------------";
            $attribs = $imlNode->attributes;
            foreach ($attribs as $att) {
                if ($att->prefix==="xsi") {
                    $xsiUris = explode(" ", $att->value);
                    foreach ($xsiUris as $xuri)
                        if (substr($xuri,-4)===".xsd") $nsLog.="\nXSI : $xuri";
                }
            }
        } else {
            $nsLog .="\nNo se encontró IMPLOCAL : ".self::QUERY_IMPLOCAL;
        }
        return $nsLog;
    }
    
    public function validaConceptos($modifiers=[]) {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaConceptos";
        $conceptos = $this->get("conceptos");
        //$cutUndefined=isset($modifiers["ignoreUndefined"])?!$modifiers["ignoreUndefined"]:true;
        if (empty($conceptos)) {
            doclog("CFDI:validaConceptos","cfdiLog",["err"=>"Sin conceptos"]);
            $this->cache["trace"][]="ERROR validaConceptos: Sin conceptos";
            return "<b>Comprobante.Conceptos</b> : No está definido o se encuentra vacío y se requiere al menos un elemento <b>Conceptos.Concepto</b>.";
        } else {
            $retVal = $this->validaConcepto($conceptos,$modifiers,$detalle);
            $this->cache["trace"][]="ERROR validaConceptos: '$retVal'";
            return $retVal;
        }
        $this->cache["trace"][]="END validaConceptos";
        return "";
    }
    public function validaConcepto($listaConceptos, $modifiers, &$detalle) {
        $this->cache["trace"][]="INI validaConcepto";
        $cutUndefined=isset($modifiers["ignoreUndefined"])?!$modifiers["ignoreUndefined"]:true;
        //$cutObjImp
        $conceptType=$modifiers["tipoconcepto"]??"";
        if (!isset($detalle)) $detalle=["result"=>"","code"=>0,"message"=>""];
        if (isset($listaConceptos["@claveprodserv"]) || isset($listaConceptos["@claveunidad"])) {
            $clvPrdSrv="".$listaConceptos["@claveprodserv"]??"";
            $clvUni=$listaConceptos["@claveunidad"]??"";
            $catPrdSrv=substr($clvPrdSrv, 0, 6);
            $descripcion=$listaConceptos["@descripcion"]??"";
            $objImp=$listaConceptos["@objetoimp"]??"";
            $descuento=+($listaConceptos["@descuento"]??"0");
            $importe=+($listaConceptos["@importe"]??"0");
            doclog("CFDI:validaConcepto","cfdiLog",["claveprodserv"=>$clvPrdSrv,"claveunidad"=>$clvUni,"cut"=>($cutUndefined?"true":"false"),"objImp"=>$objImp,"descripcion"=>$descripcion,"tipoConcepto"=>$conceptType,"descuento"=>$descuento,"importe"=>$importe,"infoProveedor"=>($this->cache["infoProveedor"]??"SinInfoProveedor"),"claveProveedor"=>($this->cache["codigoProveedor"]??"SinClaveProveedor"),"detalle"=>$detalle]);
            if (isset($modifiers["ignoreObjImp"]) && $modifiers["ignoreObjImp"]===true) {
                $reqObjImp=false;
            } else if (isset($this->cache["infoProveedor"])) $reqObjImp=($this->cache["infoProveedor"]["o"]==="1");
            else $reqObjImp=true;
            if ($reqObjImp) {
                $emisor=$this->get("emisor");
                $wData=["validar"=>self::$xsddata["lastError"]["validar"],"emisor"=>$emisor];
                if (isset($this->cache["infoProveedor"])) {
                    $wData["infoProv"]=$this->cache["infoProveedor"];
                    doclog("CFDI:validaConcepto SIN infoProveedor","cfdiLog",$wData);
                } else if (isset($this->cache)) {
                    $wData["cache"]=$this->cache;
                    doclog("CFDI:validaConcepto SIN infoProveedor","error",$wData);
                } else {
                    doclog("CFDI:validaConcepto SIN CACHE","error",$wData);
                }
                
                // EXCEPCIONES:
                // Forzoso 01:
                if ($objImp==="01") {
                    // 1- En Boletos de Avion TUA e YRI (781015XX,781019XX,781115XX) Taxis (78111804)
                    if (in_array($clvPrdSrv,["78101500","78101502","78101900","78111500","78111802","78111803","78111804"]) // ,"78141504"
                     //|| (in_array($catPrdSrv, ["781115","781015","781019"]) && in_array($descripcion,["TUA","YRI"]))
                     || (isset($conceptType[0])&&$conceptType==="avion")
                     // 2- En donativos o impuestos
                     || in_array($catPrdSrv,["841016","931615","931616","931617","931618"])
                     // 3- En servicio de agua (porque usan el mismo código para el servicio y para el redondeo del mes anterior)
                     || in_array($clvPrdSrv,["83101500"])
                     // 3- En descuento total
                     || ($descuento>0 && $descuento==$importe))
                        $reqObjImp=false;
                }
                if ($objImp==="04") {
                    if ($catPrdSrv==="841215") $reqObjImp=false;
                }
            }
            if ($this->xsd===self::XSD40 && $objImp!=="02" && $reqObjImp) {
                $this->log("OBJIMP:$objImp, CONCEPTTYPE:$conceptType");
                $detalle["result"]="error";
                $detalle["code"]=self::EXCEPTION_OBJETOIMP_INVALID;
                $detalle["message"]=self::getExceptionMessage($detalle["code"]);
                $this->cache["trace"][]="ERROR validaConcepto.ObjetoImp $detalle[code]: $detalle[message]";
                return "<b>Concepto.ObjetoImp</b> : $detalle[message]";
            } else if (!isset($clvPrdSrv[0])) {
                $detalle["result"]="error";
                $detalle["code"]=self::EXCEPTION_CLAVEPRODSERV_EMPTY;
                $detalle["message"]=self::getExceptionMessage($detalle["code"]);
                $this->cache["trace"][]="ERROR validaConcepto.ClaveProdServ $detalle[code]: $detalle[message]";
                return "<b>Concepto.ClaveProdServ</b> : $detalle[message]";
            } else {
                require_once "clases/catalogoSAT.php";
                $descripcion=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEPRODSERV,"codigo",$clvPrdSrv,"descripcion");
                if (!isset($descripcion[0])) {
                    $detalle["result"]="error";
                    $detalle["code"]=self::EXCEPTION_CLAVEPRODSERV_INVALID;
                    self::setExceptionParameter("clave",$clvPrdSrv);
                    $detalle["message"]=self::getExceptionMessage($detalle["code"]);
                    doclog("CFDI:validaConcepto.detalle","cfdiLog",["detalle"=>$detalle,"code"=>self::EXCEPTION_CLAVEPRODSERV_INVALID,"message"=>self::getExceptionMessage(self::EXCEPTION_CLAVEPRODSERV_INVALID),"clave"=>$clvPrdSrv]);
                    doclog("CFDI:validaConcepto.detalle","error",["detalle"=>$detalle,"code"=>self::EXCEPTION_CLAVEPRODSERV_INVALID,"message"=>self::getExceptionMessage(self::EXCEPTION_CLAVEPRODSERV_INVALID),"clave"=>$clvPrdSrv]);
                    $this->cache["trace"][]="Error validaConcepto ClaveProdServ $detalle[code]: $detalle[message]";
                    return "<b>Concepto.ClaveProdServ</b> : $detalle[message]";
                }

                $reqNo01x4Chk = !isset($this->cache["infoProveedor"]) || ($this->cache["infoProveedor"]["x"]==="1");
                $isAllow01x4=!$reqNo01x4Chk;
                if (hasUser()) {
                    $uid=getUser()->id;
                    global $infObj;
                    if (!isset($infObj)) { require_once "clases/InfoLocal.php"; $infObj=new InfoLocal(); }
                    if ($infObj->exists("nombre='CFDI_ALLOW01x4_{$uid}' and valor='1'")) $isAllow01x4=true;
                }
                if ($cutUndefined&&$clvPrdSrv==="01010101"&&!$isAllow01x4) {
                    $detalle["result"]="error";
                    $detalle["code"]=self::EXCEPTION_CLAVEPRODSERV_01010101;
                    $detalle["message"]=self::getExceptionMessage($detalle["code"]);
                    $this->cache["trace"][]="Error validaConcepto claveProdServ $detalle[code]: $detalle[message]";
                    return "<b>Concepto.ClaveProdServ</b> : $detalle[message]";
                }
            }
            if (!isset($clvUni[0])) {
                $detalle["result"]="error";
                $detalle["code"]=self::EXCEPTION_CLAVEUNIDAD_EMPTY;
                $detalle["message"]=self::getExceptionMessage($detalle["code"]);
                $this->cache["trace"][]="Error validaConcepto claveUnidad $detalle[code]: $detalle[message]";
                return "<b>Concepto.ClaveUnidad</b> : $detalle[message]";
            } else {
                require_once "clases/catalogoSAT.php";
                $descripcion=CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD,"codigo",$clvUni,"nombre");
                if (!isset($descripcion[0])) {
                    $detalle["result"]="error";
                    $detalle["code"]=self::EXCEPTION_CLAVEUNIDAD_INVALID;
                    self::setExceptionParameter("clave",$clvUni);
                    $detalle["message"]=self::getExceptionMessage($detalle["code"]);
                    $this->cache["trace"][]="Error validaConcepto claveUnidad $detalle[code]: $detalle[message]";
                    return "<b>Concepto.ClaveUnidad</b> : $detalle[message]";
                }
            }
            $this->cache["trace"][]="END validaConcepto";
            return "";
        } else if (is_array($listaConceptos) || is_object($listaConceptos)) {
            foreach ($listaConceptos as $idx => $cnc) {
                $this->cache["trace"][]="LIST validaConcepto idx $idx";
                $retmsg=$this->validaConcepto($cnc,$modifiers,$subdet);
                if (isset($retmsg[0])) {
                    $detalle["result"]=$subdet["result"];
                    $detalle["code"]=$subdet["code"];
                    $detalle["message"]=$subdet["message"];
                    $this->cache["trace"][]="ERROR validaConcepto $detalle[code]: $detalle[message]";
                    return $retmsg;
                }
            }
            $this->cache["trace"][]="END validaConcepto";
            return "";
        }
        $detalle["result"]="error";
        $detalle["code"]=0;
        $detalle["message"]="Concepto no reconocido: ".print_r($listaConceptos, true);
        $this->cache["trace"][]="ERROR validaConcepto $detalle[code]: $detalle[message]";
        return "<b>Conceptos.Concepto</b> : $detalle[message].";
    }
    public function validaPagos() {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaPagos";
        $pagos = $this->get("pago_doctos");
        if (empty($pagos)) {
            doclog("CFDI:validaPagos","cfdiLog",["err"=>"Sin Pagos"]);
            $this->cache["trace"][]="ERROR validaPagos: sin Pagos";
            return "<b>Comprobante.Complemento.Pagos</b> : No está definido o se encuentra vacío y se requiere al menos un elemento <b>Pago.DoctoRelacionado</b>.";
        }
        // si MetodoDePagoDR=="PPD" entonces los atributos ImpSaldoAnt, ImpSaldoInsoluto y NumParcialidad son requeridos
        // si existe más de un documento o si TipoCambioDR tiene valor entonces el atributo ImpPagado es requerido
        // si MonedaDR!==MonedaP entonces TipoCambioDR es requerido
        // si MonedaP!=='MXN' entonces TipoCambioP es requerido
        // si el banco ordenante es extranjero entonces NomBancoOrdExt es requerido
        // si TipoCadPago tiene información entonces CertPago, CadPago y SelloPago son requeridos
        if (isset($pagos["@iddocumento"])) $this->cache["numPagos"]=1;
        else $this->cache["numPagos"]=count($pagos);
        $this->cache["trace"][]="END validaPagos";
        return "";
    }
    public function validaVersion($tc) {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaVersion $tc";
        if ($this->xsd===self::XSD32) {
            doclog("CFDI:validaVersion","cfdiLog",["err"=>"Version 3.2"]);
            $this->data["facturas"]["version"]="3.2";
            $this->cache["trace"][]="ERROR validaVersion: 3.2";
            return "<b>Los comprobantes versión 3.2 ya no pueden cargarse al portal.</b>";
        } else if ($this->xsd===self::XSD33) {
            $this->data["facturas"]["version"]="3.3";
            //if (getUser()->id==1075) return ""; // rosap=1075 // jessicam=1074
            if ($tc[0]==="p") {
                $this->cache["trace"][]="END validaVersion: Es Pago";
                return "";
            }
            if (hasUser()) {
                $uid=getUser()->id;
                global $infObj;
                if (!isset($infObj)) { require_once "clases/InfoLocal.php"; $infObj=new InfoLocal(); }
                if ($infObj->exists("nombre='CFDI_ALLOW33_{$uid}' and valor='1'")) {
                    $this->cache["trace"][]="END validaVersion: 3.3 allowed for User Id:$uid";
                    return "";
                }
            }
            global $_esPruebas;
            if ($_esPruebas) return "";
            doclog("CFDI:validaVersion","cfdiLog",["err"=>"Version 3.3","tc"=>$tc, "user"=>getUser()->nombre, "uid"=>$uid]);
            if ($tc[0]==="i") {
                $this->cache["trace"][]="ERROR validaVersion i: 3.3";
                return "<b>Las facturas versión 3.3 ya no pueden cargarse al portal.</b>";
            }
            if ($tc[0]==="e") {
                $this->cache["trace"][]="ERROR validaVersion e: 3.3";
                return "<b>Los egresos versión 3.3 ya no pueden cargarse al portal.</b>";
            }
            $this->cache["trace"][]="ERROR validaVersion 3.3";
            return "<b>Los comprobantes versión 3.3 ya no pueden cargarse al portal.</b>";
        } else if ($this->xsd!==self::XSD40) {
            $this->data["facturas"]["version"]=$this->gdbData("version");
            doclog("CFDI:validaVersion","cfdiLog",["err"=>"Version ".$this->xsd]);
            $this->cache["trace"][]="ERROR validaVersion $this->xsd";
            return "<b>Los comprobantes versión '$this->xsd' no son compatibles con este portal.</b>";
        }
        $this->data["facturas"]["version"]="4.0";
        //$hasCARTAPORTE = !empty(self::$xsddata["doc.".self::CARTAPORTE]) || !empty(self::$xsddata["doc.".self::CARTAPORTE20]) || !empty(self::$xsddata["doc.".self::CARTAPORTE30]);
        //if ($hasCARTAPORTE) {
            $cpVer=$this->gdbData("cartaporte_version","cartaPorteVersion");
            if (empty($cpVer)) {
                doclog("CFDI:validaVersion/CartaPorte","cfdiLog",["debug"=>"Sin Carta Porte","data"=>$this->data,"lastError"=>self::$xsddata["lastError"],"cache"=>$this->cache]);
            } else if ($tc[0]==="i" && /* !empty($cpVer) &&*/ $cpVer!=="3.0") {
                $currentDate = new DateTime();
                $cpVerLimitDate = new DateTime("2024-04-01 00:00:00");
                if ($currentDate>$cpVerLimitDate) {
                    doclog("CFDI:validaVersion","cfdiLog",["err"=>"Version CartaPorte '".$cpVer."'"]);
                    $this->cache["trace"][]="ERROR validaVersion CartaPorte $cpVer";
                    return "<b>Es requisito del SAT que a partir del 1o de abril 2024 la versión de CartaPorte sea '3.0', no '$cpVer'</b>";
                }
            }
        //}
        $this->cache["trace"][]="END validaVersion";
        return "";
    }
    public function validaFecha() {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaFecha";
        global $infObj;
        $currentDate = new DateTime();
        $creationDate = new DateTime($this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"));
        $monN = $creationDate->format('m'); // 01 - 12
        $monE = $creationDate->format("M"); // Jan - Dec
        $yrN  = $creationDate->format("Y"); // YYYY
        $this->cache["month"]=$monN;
        $this->cache["year"]=$yrN;
        $fechaFactura=$this->data["facturas"]["fechaFactura"];
        $tipoComprobante = $this->gdbData("tipo_comprobante","tipoComprobante","strtolower");
        //if ($this->xsd===self::XSD32) {
        //    doclog("validaFecha","cfdiLog",["err"=>"Version 3.2"]);
        //    return "<b>Los comprobantes versión 3.2 ya no pueden cargarse al portal.</b>";
        //} else if ($this->xsd!==self::XSD33 && $this->xsd!==self::XSD40) {
        //    doclog("validaFecha","cfdiLog",["err"=>"Version ".$this->xsd]);
        //    return "<b>Los comprobantes versión '$this->xsd' no son compatibles con este portal.</b>";
        //} else
        if ($tipoComprobante[0]==="i") {
            $v32LimitDate = new DateTime("2018-01-01 00:00:00");
            $v2020LimitDate = new DateTime("2020-01-01 00:00:00");
            $v2021LastDate = new DateTime("2021-12-28 23:59:59");
            $v2022BeginDate = new DateTime("2022-01-01 00:00:00");
            $v2022LastDate = new DateTime("2022-12-19 16:00:00");
            $vMonthLimitDate = new DateTime("first day of this month 00:00:00");
            $vLastMonthLimitDate = new DateTime("first day of last month 00:00:00");
            if ($creationDate<$v2020LimitDate && $currentDate>=$v2020LimitDate) {
                if (!isset($infObj)) { require_once "clases/InfoLocal.php"; $infObj=new InfoLocal(); }
                if (!$infObj->exists("nombre='CFDI_IGNORE2020LIMIT' and valor='1'") && getUser()->id!=1) {
                    doclog("CFDI:validaFecha","cfdiLog",["fecha"=>$fechaFactura,"hoy"=>$currentDate->format('Y-m-d H:i:s'),"err"=>"Factura anterior al 2019"]);
                    $this->cache["trace"][]="ERROR validaFecha <2019";
                    return "<b>A partir del 1° de enero del 2020 no se recibirán facturas del año 2019.</b>";
                }
                doclog("CFDI:validaFecha","cfdiLog",["fecha"=>$fechaFactura,"hoy"=>$currentDate->format('Y-m-d H:i:s'),"err"=>"Temporalmente ignorado: Factura anterior al 2019"]);
            } else if ($creationDate<$vMonthLimitDate) {
                if (!isset($infObj)) { require_once "clases/InfoLocal.php"; $infObj=new InfoLocal(); }
                $val=$infObj->getValue("nombre","CFDI_IGNOREMONTHLIMIT","valor");
                if ($val!=="1") {  // valor=1 significa que todos tienen permiso
                    if ($val!=="0") { // valor=0 que nadie
                        $idAuthList=explode(",", $val); // valor=<lista de ids de usuario separados por comas>
                        // ToDo: Lista de bloques separados por ;
                            // Se agrega una letra al inicio de cada bloque para indicar el filtro:
                                // - u : lista de ids usuario separados por comas (quitando la u)
                                    // actualmente tiene todos los usuarios de compras,sistemas y admin:
                                        // 1086,2271,1038,1079,1078,1080,1090,1085,1949,1089,1091,1340,1339,1916,1087,2237,2266,2277,2099,2223,1741,1075,1846,1077,1839,1881,2362,2116,2225,1083,1074,2082
                                // - p : lista de ids de perfiles separados por comas (quitando la p)
                                    // vg p1,4,5 equivaldría a la lista de usuarios mencionada
                                    // se requiere modificar la configuracion para escoger usuarios o perfiles y la lista a desplegar corresponda con unos u otros
                        $userId="".getUser()->id;
                        if (in_array($userId, $idAuthList)) {
                            doclog("CFDI:validaFecha","cfdiLog",["fecha"=>$fechaFactura,"hoy"=>$currentDate->format('Y-m-d H:i:s'),"userId"=>$userId,"err"=>"Temporalmente Ignorado: Factura de mes anterior"]);
                            $this->cache["trace"][]="END validaFecha: Permiso Mes Pasado";
                            return ""; // Valido solo si no hay validaciones adicionales después en esta misma funcion
                        }
                    }
                    if (in_array(getUser()->nombre, CFDI::PRV_MONTHLIMIT_EXCEPTION)) {
                        doclog("CFDI:validaFecha","cfdiLog",["fecha"=>$fechaFactura,"hoy"=>$currentDate->format('Y-m-d H:i:s'),"user"=>getUser()->nombre,"err"=>"Temporalmente Ignorado: Factura de mes anterior"]);
                        $this->cache["trace"][]="END validaFecha: Permiso PRV Mes Pasado";
                        return "";
                    }
                    doclog("CFDI:validaFecha","cfdiLog",["fecha"=>$fechaFactura,"hoy"=>$currentDate->format('Y-m-d H:i:s'),"userId"=>$userId,"err"=>"Factura de mes anterior"]);
                    $this->cache["trace"][]="ERROR validaFecha: No mes actual";
                    return "<b>Sus facturas deben tener fecha del mes actual, le solicitamos ingresarlas a tiempo o refacturarlas.</b>";
                }
            } else if (isset(getUser()->proveedor) && validaPerfil("Proveedor") && !in_array(getUser()->nombre, CFDI::PRV_MONTHLIMIT_EXCEPTION) && !Proveedores::esCorporativo(getUser()->nombre)) {
                $lastWorkingDay=getLastInvoicingDay();
                $lastWorkingDateTime=$lastWorkingDay?new DateTime($lastWorkingDay." 16:00:00"):null;
                if (!isset($lastWorkingDateTime) || $currentDate>$lastWorkingDateTime) {
                    $this->cache["trace"][]="ERROR validaFecha: Deshabilitado";
                    return "<b>El Servicio de Alta de Facturas se encuentra deshabilitado.</b>";
                }
            //} else if ($currentDate>$v2021LastDate && $currentDate<$v2022BeginDate && validaPerfil("Proveedor")) {
            //    return "<b>Le recordamos que la fecha limite para ingresar facturas del 2021 era el 28 de diciembre. Le pedimos que refacture con mes de enero sus facturas pendientes.</b>";
            //} else if ($currentDate>$v2022BeginDate && $creationDate<$v2022BeginDate && validaPerfil("Proveedor")) {
            //    return "<b>A partir del 1° de enero del 2022 no se recibirán facturas de años anteriores.</b>";
            } /* else if ($currentDate>$v2022LastDate) {
                return "<b>La fecha límite para ingresar facturas del 2022 era el 19 de diciembre a las 04:00 pm. Le pedimos que refacture con mes de enero sus facturas pendientes.</b>";
            } */
        } else if ($tipoComprobante[0]==="p") {
            // Rosa Peredo solicita: validar fecha de pago en complementos, los proveedores suelen poner la misma fecha que la fecha de creación del complemento de pago. Lo más que se puede hacer sin tener la fecha de pago es que se compare contra la fecha de creación del documento. La lógica es que el pago debe realizarse antes de crear el complemento de pago, pero lo más que se puede checar es que ambas fechas no sean exactamente iguales, lo que se puede reinterpretar como que la fecha de pago debe ser al menos un segundo anterior a la fecha de creacion del complemento. Se podría aumentar este rango a un valor mínimamente aceptable como 5 o 10 segundos, tal vez 30 segundos, aunque teniendo una aplicación que realice el pago y genere el documento todo esto se invalidaría.
            // Recibi una queja de Nieves Lopez con un complemento para LAISA, el proveedor paga y dos horas despues genera el complemento de pago por lo que la fecha de pago coincide con la fecha de creacion del documento, ignorando la hora el dia es el mismo, me comentan que es ilogico tomar en cuenta la hora, que lo que vale es el dia por lo que aunque el proveedor mantiene la misma fecha y hora de creacion del documento, en la fecha de pago es irrelevante la hora especificada
            // Exclusivamente por existir un dilema entre empresas y que la peticion para realizar este cambio era relativa solo a una empresa, entonces se comenta la validación hasta que se pongan de acuerdo y se defina la lógica adecuada considerando todas las empresas
            // Comentario personal: se debería validar contra lo indicado en egresos, sin embargo eso requiere otra modificacion pendiente en la que se pretende subir de forma automatica la lista de egresos semanalmente. Esto sigue sin ser una solucion real pues los proveedores pueden subir el complemento el mismo dia que se realiza el pago, incluso apenas 2 horas despues. Se podria ignorar la validacion si no se encuentra el egreso y si la fecha de creacion del documento es anterior a la ultima carga de egresos. Esto se puede trabajar incluso sin aprobacion debido a la preocupacion presentada inicialmente, sin embargo es esencial que la carga automatica de egresos ya sea funcional. (Se puede validar unicamente contra la existencia del egreso, ya que si no se ha cargado en el sistema es porque no se ha definido adecuadamente la responsabilidad de cargarlo y lo peor que pasa es que el portal no valida de forma automatica, y la responsabilidad de validar será del personal administrativo correspondiente, como se ha hecho hasta el momento, ya sea que nadie lo valide o que el proceso de validacion sea tardado)
            // ToDo: Preparar carga automatica de egresos: Una vez se especifique una carpeta compartida y se acepte responsabilidad de copiar en la misma las listas de egresos extraidas de avance de todos los pagos de todas las empresas del corporativo, entonces se agregará validacion identificando la fecha de pago en las facturas, las que deben coincidir con la fecha de pago indicada en el complemento. Los recursos requeridos por esta validacion esta relacionada al numero de facturas que incluya el complemento pues hay que consultar cada una 
            /*
            $fechaPagoArr = $this->get("pago_fecha");
            if (!is_array($fechaPagoArr)) $fechaPagoArr = [$fechaPagoArr];
            foreach ($fechaPagoArr as $idx => $fpVal) {
                $paymentDate = new DateTime(self::getDBDateFromXMLDate($fpVal));
                if ($paymentDate>=$creationDate) {
                    doclog("Fecha de Pago Inválida","cfdiLog",["hoy"=>$currentDate->format('Y-m-d H:i:s'),"fechaCreacion"=>$fechaFactura,"fechaPago"=>$fpVal,"indexPago"=>$idx]);
                    return "<b>La fecha de pago debe contener la fecha en que se realizó el pago, por favor ingrese este dato correctamente.</b>";
                }
            }
            */
        }
        $this->cache["trace"][]="END validaFecha";
        return "";
    }
    public static function fechaHoraMexico($datetime) {
        date_default_timezone_set('UTC');
        date_default_timezone_set("Etc/GMT+6");
        $hora = date_format($datetime,"g:ia");
        setlocale(LC_TIME, 'spanish');
        $fecha = utf8_encode(strftime("%#d de %B del %Y", $datetime->getTimestamp()));
        //require_once "clases/DBi.php";
        //$fecha = mb_convert_encoding(DBi::$spDtFmt->format($datetime->getTimestamp()), 'UTF-8', mb_list_encodings());
        return $fecha." ".$hora;
    }
    public static function formatInterval($interval) {
        $hasDays = ($interval->d)>0;
        $hasHours = ($interval->h)>0;
        $hasMinutes = ($interval->i)>0;
        if($hasDays) {
            $format="%ad";
            if ($hasHours) {
                $format.=" %Hh";
                if ($hasMinutes) $format.=" %Im";
            }
        } else if ($hasHours) {
            $format="%Hh";
            if ($hasMinutes) $format.=" %Im";
        } else if ($hasMinutes) {
            $format="%Im";
        } else {
            $format="%Ss";
        }
        return $format;
    }        
    public function validaFolio() {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaFolio";
        if (!$this->existsXPath(self::QUERY_FOLIO)) {
            if ($this->xsd!==self::XSD32) {
                $this->cache["trace"][]="ERROR validaFolio: No folio";
                return "<b>Comprobante.Folio</b> : No está definido y es requerido en la versión actual.";
            }
            // En version 3.2 puede que las facturas no tengan folio, en dado caso se utilizan los últimos caracteres del uuid
        }
        $this->cache["trace"][]="END validaFolio";
        return "";
    }
    public function validaTipoCambio() {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaTipoCambio";
        if (!$this->existsXPath(self::QUERY_TIPOCAMBIO)) {
            $mon = $this->gdbData("moneda");
            if ($mon==="MXN" || $mon==="XXX") $this->cache["evaluate"][self::QUERY_TIPOCAMBIO]="1";
            else if ($this->xsd===self::XSD32) {
                $this->cache["evaluate"][self::QUERY_MONEDA]="MXN";
                $this->cache["evaluate"][self::QUERY_TIPOCAMBIO]="1";
            } else if ($this->xsd===self::XSD33||$this->xsd===self::XSD40) {
                $this->cache["trace"][]="ERROR validaTipoCambio: Moneda $mon, Version $this->xsd";
                return "<b>Comprobante.TipoCambio</b> : No está definido y es requerido cuando <b>Comprobante.Moneda</b> no es 'MXN'. <b>Comprobante.Moneda</b> es '$mon'.";
            } else {
                $this->cache["trace"][]="ERROR validaTipoCambio: Version $this->xsd";
                return "El portal aún no valida Tipo de Cambio para ".$this->xsd;
            }
        }
        $this->cache["trace"][]="END validaTipoCambio";
        return "";
    }
    public function validaUUID() {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaUUID";
        global $invObj;
        $uuid = $this->gdbData("uuid",null,"strtoupper");
        if (empty($uuid)) {
            if (!isset($this->cache["errors"])) $this->cache["errors"]=[];
            $this->cache["errors"][]=["message"=>"No está definido y es requerido","name"=>"TimbreFiscalDigital.UUID","value"=>""];
            $this->cache["trace"][]="ERROR validaUUID: No UUID";
            return "<b>TimbreFiscalDigital.UUID</b> : No está definido y es requerido.";
        }
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj = new Facturas();
        }
        $this->cache["uuid"]=$uuid;
        //$invId = $invObj->getValue("uuid", $uuid, "id", "status NOT IN ('Temporal')");
        $invData = $invObj->getData("uuid=upper('$uuid') and statusn IS NOT NULL", 0, "id,fechaCaptura,codigoProveedor,folio");
        if (!isset($invData[0])) {
            $this->cache["trace"][]="END validaUUID: No CFDI";
            return "";
        }
        $invId = $invData[0]["id"];
        $invRegistry = $invData[0]["fechaCaptura"];
        $invFolio=$invData[0]["folio"];
        $invCodProv=$invData[0]["codigoProveedor"];
        $this->cache["idFactura"]=$invId;
        if (isset($invRegistry[9])) {
            $regYear=substr($invRegistry,0,4);
            $regMonth=+substr($invRegistry,5,2);
            $months=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
            $regDay=+substr($invRegistry,8,2);
            $timeMsg=", desde el $regDay de ".$months[$regMonth-1]." del {$regYear}";
            if (isset($invRegistry[15])) $timeMsg.=" a las ".substr($invRegistry,11,5);
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcData=$prcObj->getData("modulo='Factura' and identif=$invId and fecha='$invRegistry'",0,"usuario");
            if (isset($prcData[0]["usuario"][0])) $timeMsg.=" (".$prcData[0]["usuario"].")";
        } else $timeMsg="";
        if (!empty($invId)) {
            if (!isset($this->cache["errors"])) $this->cache["errors"]=[];
            $errItem=["message"=>"Ya está dado de alta en el sistema","name"=>"TimbreFiscalDigital.UUID","value"=>$uuid,"id"=>$invId,"errpath"=>"yaExiste"];
            $folioMsg="";
            if (isset($invFolio[0])) {
                $folioMsg=", FOLIO '$invFolio'";
                $errItem["folio"]=$invFolio;
            }
            $this->cache["errors"][]=$errItem;
            self::setLastErrorData("code", self::EXCEPTION_VAL_UUID_EXISTS);
            self::setLastErrorData("id", $invId);
            $this->cache["trace"][]="ERROR validaUUID: Already in system";
            return "<b>TimbreFiscalDigital.UUID</b> : Ya existe un comprobante en el sistema con PROVEEDOR '$invCodProv'{$folioMsg} y UUID '$uuid'{$timeMsg}.";
        }
        $this->cache["trace"][]="END validaUUID: Has but has not CFDI id";
        return "";
    }
    public function validaProveedor($rfc, &$nombre) {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaProveedor $rfc";
        global $prvObj;
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj = new Proveedores();
        }
        $hasName = (isset($nombre[0]));
        self::$xsddata["lastError"]["validar"].="|PRV:$rfc";
        if($hasName) self::$xsddata["lastError"]["validar"].=",$nombre";
        $prvData = $prvObj->getData("rfc='$rfc'",0,"id,codigo".($hasName?"":",razonSocial").",verificado,cumplido,esServicio,conCodgEnDesc,reqObjImp,reqPayTaxChk,reqDefCvPrdSrv,status,credito");
        if (!isset($prvData[0])) {
            if ($hasName) $retName = "$nombre ($rfc)";
            else $retName = $rfc;
            self::setExceptionParameter("proveedor", $retName);
            self::setLastErrorData("proveedor",$retName);
            self::setLastErrorData("code", self::EXCEPTION_UNREGISTERED_PROVIDER);
            $this->cache["trace"][]="ERROR validaProveedor: not registered";
            return "<b>Emisor.Rfc</b> : El Proveedor $retName no ha sido registrado. <input type='submit' name='proveedor_submit' id='proveedor_submit' value='Registrar $retName' title='Registrar Proveedor' onclick='habilitaRegistro(this);' form='forma_alta' razsoc='$nombre' rfc='$rfc'>.";
        }
        $prvData=$prvData[0];
        $sttPrv=$prvData["status"];
        if (in_array($sttPrv, ["inactivo","eliminado"])) {
            $prvErrCode=self::EXCEPTION_DELETED_PROVIDER;
            if ($sttPrv==="inactivo" && validaPerfil(["Administrador","Sistemas","Compras"])) $prvErrCode=self::EXCEPTION_INACTIVE_PROVIDER;
            if ($hasName) $retName = "$nombre ($rfc)";
            else $retName = $rfc;
            self::setExceptionParameter("proveedor", $retName);
            self::setLastErrorData("proveedor",$retName);
            self::setLastErrorData("code", $prvErrCode);
            $this->cache["trace"][]="ERROR validaProveedor: ".($prvErrCode===self::EXCEPTION_DELETED_PROVIDER?"deleted":"inactive");
            return "<b>Emisor</b> : ".self::EXCEPTION_MESSAGES[$prvErrCode];
        }
        $idPrv = $prvData["id"];
        $codPrv = $prvData["codigo"];
        $this->data["facturas"]["codigoProveedor"]=$codPrv;
        $rzPrv = $prvData["razonSocial"]??$nombre;
        $crdPrv = +$prvData["credito"]??0;
        $infoPrv=["v"=>$prvData["verificado"],"c"=>$prvData["cumplido"],"s"=>$prvData["esServicio"],"d"=>$prvData["conCodgEnDesc"],"o"=>$prvData["reqObjImp"],"t"=>$prvData["reqPayTaxChk"],"x"=>$prvData["reqDefCvPrdSrv"]];
        self::$xsddata["lastError"]["validar"].=",$idPrv,$codPrv";
        if (!$hasName) {
            $nombre=$prvData["razonSocial"];
            self::$xsddata["lastError"]["validar"].=",$nombre";
            $this->cache["nombreProveedor"]=$nombre;
            $this->cache["evaluate"][self::QUERY_EMISOR]["@nombre"]=$nombre;
        }
        $this->cache["idProveedor"]=$idPrv;
        $this->cache["codigoProveedor"]=$codPrv;
        $this->cache["rfcProveedor"]=$rfc;
        $this->cache["razonProveedor"]=$rzPrv;
        $this->cache["creditoProveedor"]=$crdPrv;
        $this->cache["infoProveedor"]=$infoPrv;
        $this->cache["trace"][]="END validaProveedor";
        return "";
    }

    public function validaCorporativo(&$receptor) {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaCorporativo";
        if (!isset($receptor["@rfc"][0])) {
            $this->cache["trace"][]="ERROR validaCorporativo: No Rfc";
            return "No se pudo obtener la informacion del receptor";
        }
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj = new Grupo();
        }
        $rfc = $receptor["@rfc"];
        $this->data["facturas"]["rfcGrupo"]=$rfc;
        $nombre = isset($receptor["@nombre"])?$receptor["@nombre"]:null;
        $hasName = (isset($nombre[0]));
        self::$xsddata["lastError"]["validar"].="|GPO:$rfc";
        if($hasName) self::$xsddata["lastError"]["validar"].=",$nombre";
        $gpoData = $gpoObj->getData("rfc='$rfc'",0,"id,alias".($hasName?"":",razonSocial"));
        if (!isset($gpoData[0])) {
            if ($hasName) $retName="$nombre ($rfc)";
            else $retName=$rfc;
            $this->cache["trace"][]="ERROR validaCorporativo $rfc: Not valid";
            return "<b>Receptor.Rfc</b> : Empresa $retName no registrada como parte del corporativo.";
        }
        $idGpo = $gpoData[0]["id"];
        $aliasGpo = $gpoData[0]["alias"];
        $nombreGpo = $gpoData[0]["razonSocial"]??"";
        self::$xsddata["lastError"]["validar"].=",$idGpo,$aliasGpo";
        if (!$hasName) {
            $receptor["@nombre"]=$nombreGpo;
            $this->cache["evaluate"][self::QUERY_RECEPTOR]["@nombre"]=$nombreGpo;
            self::$xsddata["lastError"]["validar"].=",$nombreGpo";
        }
        $this->data["facturas"]["usoCFDI"]=mb_strtoupper($receptor["@usocfdi"]??"");
        $this->cache["idGrupo"]=$idGpo;
        $this->cache["aliasGrupo"]=$aliasGpo;
        $this->cache["rfcGrupo"]=$rfc;
        $this->cache["nombreGrupo"]=$nombreGpo;

        $regimen=$receptor["@regimenfiscalreceptor"]??"";
        if (isset($regimen[0])) {
            $regimen=+$regimen;
            if (isset($rfc[11])&&!isset($rfc[12])&&$regimen!==601) {
                $this->cache["trace"][]="ERROR validaCorporativo $rfc: Regimen Fiscal is not 601: $regimen";
                return "<b>Receptor.regimenFiscalReceptor</b> : El receptor debe tener Régimen General de las Personas Morales : 601.<br>(En XML tiene $regimen)";
            }
            if ($regimen===626) {
                $this->cache["trace"][]="ERROR validaCorporativo $rfc: Regimen Fiscal should not be 626";
                return "<b>Receptor.regimenFiscalReceptor</b> : El receptor <b>no</b> debe tener <s>Régimen Simplificado de Confianza : 626</s>.";
            }
            $this->cache["regimenGrupo"]=$regimen;
        }
        $this->cache["trace"][]="END validaCorporativo";
        return "";
    }
    public function validaUsuario($rfcEmisor, $rfcReceptor) {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaUsuario. Emisor:$rfcEmisor, Receptor:$rfcReceptor";
        if (!hasUser()) {
            $this->cache["trace"][]="ERROR validaUsuario: no user";
            return "No autorizado por no estar registrado";
        }
        if (validaPerfil(["Administrador","Sistemas"])) {
            $this->cache["trace"][]="END validaUsuario: is system|admin";
            return ""; // administrador valido
        }
        $usr=getUser();
        if (validaPerfil("Proveedor")&&isset($usr->proveedor)) {
            if ($usr->proveedor->rfc===$rfcEmisor) {
                $this->cache["trace"][]="END validaUsuario: valid provider";
                return ""; // el proveedor emisor valido
            }
            $this->cache["trace"][]="ERROR validaUsuario: User ".$usr->proveedor->rfc." is not the provider $rfcEmisor";
            return "El Emisor del comprobante ('$rfcEmisor') no corresponde al usuario (".$usr->proveedor->rfc.")";
        }
        $perfilValido=[];
        if (validaPerfil("Compras")) $perfilValido[]="Compras";
        if (validaPerfil("Compras Basico")) $perfilValido[]="Compras Basico";
        if (validaPerfil("Alta Facturas")) $perfilValido[]="Alta Facturas";
        if (isset($perfilValido[0])/*validaPerfil("Compras")||validaPerfil("Alta Facturas")*/) {
            global $ugObj;
            if (!isset($ugObj)) {
                require_once "clases/Usuarios_Grupo.php";
                $ugObj = new Usuarios_Grupo();
            }
            if ($ugObj->exists("idUsuario=$usr->id")) {
                if ($ugObj->isRelatedByRFC($usr, $rfcReceptor, $perfilValido, "vista")) {
                    $this->cache["trace"][]="END validaUsuario: receptor";
                    return ""; // Permitir subir facturas de compras. Donde la empresa es cliente y el usuario es empleado y esta subiendo las facturas de sus proveedores
                }
                if ($ugObj->isRelatedByRFC($usr, $rfcEmisor, $perfilValido, "vista")) {
                    $this->cache["trace"][]="END validaUsuario: emisor";
                    return ""; // Permitir subir facturas de ventas. Donde la empresa es proveedor y el usuario es empleado y esta subiendo facturas como proveedor para otra empresa del corporativo
                }
                global $gpoObj;
                if (!isset($gpoObj)) {
                    require_once "clases/Grupo.php";
                    $gpoObj = new Grupo();
                }
                $alias=$gpoObj->getAliasByRFC($rfcReceptor);
                $this->cache["trace"][]="ERROR validaUsuario: user ".$usr->nombre." not authorized for $alias invoices";
                return "El usuario ".$usr->nombre." no tiene autorizado dar de alta comprobantes para {$alias}.";
            } else return "";
        }
        $this->cache["trace"][]="ERROR validaUsuario: user ".$usr->nombre." not authorized for registering invoices";
        return "El usuario ".$usr->nombre." no est&aacute; autorizado para dar de alta comprobantes.";
    }
    public function validaMetodoDePago($metodoDePago, $formaDePago, $tc) {
        if (!isset($this->cache["trace"])) $this->cache["trace"]=[];
        $this->cache["trace"][]="INI validaMetodoPago: M=$metodoDePago, F=$formaDePago, T=$tc";
        global $mdpObj;
        if (!isset($mdpObj)) {
            require_once "clases/MetodosDePago.php";
            $mdpObj = new MetodosDePago();
        }
        if ($this->xsd===self::XSD32) {
            global $infObj;
            if (!isset($infObj)) {
                require_once "clases/InfoLocal.php";
                $infObj = new InfoLocal();
            }
            $retIL = $infObj->obtener("validaMetodoPago");
            if (empty($retIL) || $retIL!=="NO") {
                $mdpArr = explode(",",$metodoDePago);
                $mdpOtros = ["NA", "NOAPLICA", "NOIDENTIFICADO"];
                $skipChars = [" ", ".", ",", "/", "_", "-"];
                foreach ($mdpArr as $mdp) {
                    $mdpVal = str_replace($skipChars, "", $mdp);
                    if (!$mdpObj->esValido($mdpVal) && !in_array(strtoupper($mdpVal),$mdpOtros)) {
                        $this->cache["trace"][]="ERROR validaMetodoDePago: $mdp is not valid";
                        return "El m&eacute;todo de pago \"$mdp\" no es un c&oacute;digo v&aacute;lido";
                    }
                }
            }
        } else if ($this->xsd===self::XSD33 || $this->xsd===self::XSD40) {
            if (empty($tc)) $tc="i";
            if ($tc==="p") {
                $creationDate = new DateTime($this->gdbData("fecha","fechaFactura","CFDI::getDBDateFromXMLDate"));
                $vApr2023 = new DateTime("2023-04-01 00:00:00");
                if ($creationDate>=$vApr2023) {
                    if(!empty($metodoDePago)) {
                        $this->cache["trace"][]="ERROR validaMetodoDePago: Payment Receipt with MP";
                        return "En un Comprobante de Pago no debe existir Método de Pago";
                    }
                    if(!empty($formaDePago)) {
                        $this->cache["trace"][]="ERROR validaMetodoDePago: Payment Receipt with FP";
                        return "En un Comprobante de Pago no debe existir Forma de Pago";
                    }
                }
                $this->cache["trace"][]="END validaMetodoDePago";
                return "";
            }
            if (empty($formaDePago)) {
                $this->cache["trace"][]="ERROR validaMetodoDePago: FP needed";
                return "La forma de pago es requerida en el comprobante.";
            }
            if (empty($metodoDePago)) {
                $this->cache["trace"][]="ERROR validaMetodoDePago: MP needed";
                return "El método de pago es requerido en el comprobante.";
            }
            /*if (isset($formaDePago[50])) {
                $formaDePago=substr($formaDePago, 0, 50);
                $this->data["facturas"]["formaDePago"]=$formaDePago;
            }*/
            if (isset($metodoDePago[50])) {
                $metodoDePago=substr($metodoDePago, 0, 50);
            }
            $this->data["facturas"]["metodoDePago"]=$metodoDePago;
            require_once "clases/catalogoSAT.php";
            $fpDesc = CatalogoSAT::getValue(CatalogoSAT::CAT_FORMAPAGO,"codigo",$formaDePago,"descripcion");
            
            if (!$mdpObj->esValido($formaDePago)) {
                $this->cache["trace"][]="ERROR validaMetodoDePago: invalid FP ($formaDePago=$fpDesc)";
                return "La forma de pago \"$formaDePago\": \"$fpDesc\" no es un c&oacute;digo v&aacute;lido.";
            }
            
            // Modificacion solicitada el 30 may 2018 15:34 por Arturo Islas
            // Si el tipo de comprobante es E (egreso) el metodoDePago solo puede ser PUE y la formaDePago es libre
            if ($tc==="e") {
            /*
                if ($metodoDePago==="PPD") {
                    return "El Método de Pago en Notas de Crédito sólo puede ser 'PUE'.";
                }
            */
                if ($formaDePago=="99") {
                    $this->cache["trace"][]="ERROR validaMetodoDePago: Expenses ('egresos') do not allow FP=99 ('Por definir')";
                    return "Los Egresos no pueden tener forma de pago '99' ('Por definir').";
                }
                // Forma de pago 30 (aplicacion de anticipos) solo para egresos
            } else if ($tc==="i") {
                // Modificacion solicitada el 16 feb 2018 17:20 por Arturo Islas
                // Si el método de pago es PPD la forma de pago debe ser 99
                if ($metodoDePago==="PPD" && $formaDePago!=="99") {
                    $this->cache["trace"][]="ERROR validaMetodoDePago: Income ('ingresos') do not allow FP=$formaDePago ('$fpDesc') with MP='PPD'";
                    return "La Forma de Pago \"$formaDePago\" (\"$fpDesc\") no se acepta para el Método de Pago 'PPD' ('Pago en parcialidades o diferido'). Debe ser '99' ('Por definir').";
                }
                // Modificacion solicitada el 20 feb 2018 10:45 por Diana Najera
                // Si el metodo de pago es PUE la forma de pago no puede ser 99
                if ($metodoDePago==="PUE" && $formaDePago==="99") {
                    $this->cache["trace"][]="ERROR validaMetodoDePago: Income ('ingresos') do not allow FP=$formaDePago ('$fpDesc') with MP='PUE'";
                    return "La Forma de Pago '99' ('Por definir') no se acepta para el Método de pago 'PUE' ('Pago en una sola exhibición').";
                }
            }
            // El metodo de pago puede ser PUE o PPD o no tener. Pero si no tiene debe existir una factura relacionada
            // TODO: Verificar que el metodo de pago de la factura relacionada debe ser PPD.
            if (!isset($metodoDePago[0])) {
                $reluuid = $this->get("uuid_relacionado");
                if (empty($reluuid)) {
                    $this->cache["trace"][]="ERROR validaMetodoDePago: No MP requires related uuid";
                    return "Los comprobantes por pago parcial o diferido deben tener el comprobante origen relacionado";
                }
                //TODO: consultar en BD el metodo de pago de la factura relacionada, debe ser = 'PPD'
                //TODO: Verificar que otras validaciones son necesarias para asegurar que la relacion sea adecuada, correcta (Que la relacion sea porque es un pago parcial, que coincida con otros datos y otras facturas que correspondan a pagos parciales)
            }
            $this->cache["trace"][]="END validaMetodoDePago";
            return ""; //"Forma de Pago '$formaDePago': '$fpDesc' aceptada";
        } else {
            $this->cache["trace"][]="ERROR validaMetodoDePago: invalid version '".$this->xsd."'";
            return "El portal no valida CFDI's versión \"".$this->xsd."\"";
        }
    }
/*
    public function validaCadenaOriginal() {
        $str = $this->getXSLTResult();
        clog2("# CADENA ORIGINAL # $str");
        $hashval = hash("sha256",$str);
        clog2("# HASH SHA256 DE CADENA ORIGINAL # $hashval");
        $cert = $this->get("certificado");
        clog2("# CERTIFICADO # $cert");
        $tfdQryRes = $this->xpath->query(self::QUERY_TFD);
        if (is_object($tfdQryRes)) {
            $tfdNode = $tfdQryRes->item(0);
            $selloCFD = $tfdNode->getAttributeNS(self::NSTFD, "SelloCFD");
            clog2("# SELLO CFD # $selloCFD");
            $selloSAT = $tfdNode->getAttributeNS(self::NSTFD, "SelloSAT");
            clog2("# SELLO SAT # $selloSAT");
            $noCrtSAT = $tfdNode->getAttributeNS(self::NSTFD, "NoCertificadoSAT");
            clog2("# NO CERT SAT # $noCrtSAT");
        }
        return "";
    }
*/        
    /* ********** ********** UTILERIAS ********** ********** */
    public function get($key) {
        $doLog=CFDI::DO_LOG_GET;
        $key2 = "self::QUERY_".strtoupper($key);
        if (defined($key2)) {
            if ($doLog) $this->log("GET $key2 => ".constant($key2));
            $result = $this->evaluateXPath(constant($key2));
//            } else if (defined($key)) {
//                $result = $this->evaluateXPath(constant($key));
        } else {
            if ($doLog) $this->log("DIRECT GET $key");
            $result = $this->evaluateXPath($key);
        }
        return $result;
    }
    public function has($key) {
        $key2 = "self::QUERY_".strtoupper($key);
        if (defined($key2)) {
            $kconst = constant($key2);
            if (isset($this->cache["evaluate"][$kconst])) return true;
            $list = $this->xpath->query($kconst);
            return $list->length > 0;
        }
        $list = $this->xpath->query($key);
        return $list->length > 0;
    }
    private function evaluateXPath($xquery, $isCaseSensitive=false) {
        $doLog=CFDI::DO_LOG_EVAL;
        if ($doLog) $this->log("INI function evaluateXPath: '$xquery'");
        if (!isset($this->cache["evaluate"])) $this->cache["evaluate"]=[];
        if (isset($this->cache["evaluate"][$xquery])) {
            $xresult = $this->cache["evaluate"][$xquery];
        }
        $logPrompt="";
        if (!isset($xresult) || (is_bool($this->forceTest) && $this->forceTest) || (is_array($this->forceTest) && in_array($xquery, $this->forceTest)) || $this->forceTest===$xquery) {
            $xresult = $this->xpath->evaluate($xquery);
            if (is_object($xresult) && get_class($xresult)==="DOMNodeList") {
                $tmparr = [];
                foreach($xresult as $key=>$item) {
                    $itemType=gettype($item);
                    if ($itemType==="object") $itemType.="(".get_class($item).")";
                    if ($doLog) $this->log("EVAL $xquery DOMNodeList Item($key) type=$itemType");
                    $val = $this->explodeNode($item, $isCaseSensitive);
                    if (!empty($val)||$val==="0") $tmparr[] = $val;
                }
                if (!isset($tmparr[0])) $xresult="";
                else if (!isset($tmparr[1])) $xresult=$tmparr[0];
                else $xresult=$tmparr;
            } else if (is_object($xresult)) {
                if ($doLog) $this->log("EVAL $xquery object classname=".get_class($xresult));
            }
            else if (is_scalar($xresult)) {
                if ($doLog) $this->log("EVAL $xquery scalar type=".gettype($xresult));
            }
            else if (is_array($xresult)) {if ($doLog) $this->log("EVAL $xquery array");}
            else if ($doLog) $this->log("EVAL $xquery other type");
            if (!empty($xresult)) {
                $this->cache["evaluate"][$xquery]=$xresult;
            }
        } else $logPrompt="CACHE(".count($this->cache["evaluate"]).") ";
        if ($doLog) $this->log($logPrompt."EVALUATE[$xquery]=".(empty($xresult)?"[]":json_encode($xresult)));
//            $this->log("END function evaluateXPath.");
        return $xresult;
    }
    public function toArray() {
        $values = $this->explodeNode($this->xmldoc->documentElement, true);
        $schema = (self::$xsddata["doc.".$this->xsd])->documentElement;
        return $this->explodeXSD($schema, $values);
    }
    public static function getNodeDesc($node) {
        $desc = "NULL";
        if (isset($node)) {
            if (is_object($node)) {
                $desc = "{".get_class($node)."}";
                if (is_a($node,"DOMNode")) {
                    $desc .= $node->localName;
                    if ($node->hasAttribute("name")) {
                        $desc .= " ".$node->getAttribute("name");
                    } else if (!empty($node->nodeValue)) {
                        $nodval = trim($node->nodeValue);
                        if (isset($nodval[20]))
                            $nodval = substr($nodval,0,17)."...";
                        $desc .="=".$nodval;
                    }
                }
            } else if (is_array($node)) {
                $desc = "array(".count($node).")";
                $desc .= "[";
                $first=true;
                foreach($node as $key=>$item) {
                    if ($first) $first=false;
                    else $desc .= ",";
                    $itemDesc = $key; // .":".self::getNodeDesc($item);
                    //if (isset($itemDesc[40])) $itemDesc = substr($itemDesc,0,37)."...";
                    $desc .= $itemDesc;
                }
                $desc .= "]";
            } else {
                $desc = "(".gettype($node).")";
                if (is_scalar($node)) $desc .= $node;
            }
        }
        return $desc;
    }
    private function explodeXSD($node, $values=null) {
        //$this->log("INI function explodeXSD Node(".self::getNodeDesc($node).") Value(".self::getNodeDesc($values).")");
        $arr = [];
        $xsdxpath = self::$xsddata["xpath.".$this->xsd]; //$this->getXSD("xpath");
        if ($xsdxpath==null) return ["error"=>"Null XPATH"];
        $elementsDefs = $xsdxpath->evaluate(self::QUERY_XS_TOP_ELEMENT);
        foreach($elementsDefs as $elemDef) {
            $arr[$elemDef->getAttribute("name")] = $this->explodeXSDElement($elemDef, $values);
        }
        return $arr;
    }
    private function explodeXSDElement($node, $values, $xpathkey=null, $depth=0) {
        //$this->log("INI function explodeXSDElement Node(".self::getNodeDesc($node).") Value(".self::getNodeDesc($values).")");
        //clog3("INI function explodeXSDElement Node(".self::getNodeDesc($node).") Value(".self::getNodeDesc($values).")");
        $arr = [];
        if ($depth>13) return ["error"=>"Error de Recursion. Máxima recurrencia de 13 ciclos alcanzada"];
        if (!isset($xpathkey)) $xpathkey=$this->xsd;
        $xsdxpath = self::$xsddata["xpath.".$xpathkey]; //$this->getXSD("xpath");
        if ($xsdxpath==null) return ["error"=>"Null XPATH"];
        $hasType = $node->hasAttribute("type");
        $hasMinOccurs = $node->hasAttribute("minOccurs");
        $hasMaxOccurs = $node->hasAttribute("maxOccurs");
        $hasParentNode = isset($node->parentNode);
        if ($hasType||$hasMinOccurs||$hasMaxOccurs||$hasParentNode) {
            $arr["@attributes"] = [];
            if ($hasParentNode) $arr["@attributes"]["parentTag"] = $node->parentNode->localName;
            if ($hasType) $arr["@attributes"]["type"] = $node->getAttribute("type");
            if ($hasMinOccurs) $arr["@attributes"]["minOccurs"] = $node->getAttribute("minOccurs");
            if ($hasMaxOccurs) $arr["@attributes"]["maxOccurs"] = $node->getAttribute("maxOccurs");
        }
        $attrDefs = $xsdxpath->evaluate(self::QUERY_XS_ATTRIBUTE, $node);
        if (isset($values) && is_array($values)) $valKeys = array_keys($values);
        foreach($attrDefs as $aDef) {
            $attrName = "@".$aDef->getAttribute("name");
            if ($aDef->hasAttribute("use")) {
                $arr[$attrName] = ["use"=>$aDef->getAttribute("use")];
                if ($attrName === "@Folio") $arr[$attrName]["use"]="required2";
                if ($attrName === "@Folio") $arr[$attrName]["use"]="required2";
            } else $arr[$attrName] = ["use"=>"undefined"];
            if (isset($values) && isset($values[$attrName])) {
                if (isset($valKeys)) $valKeys = array_diff($valKeys, [$attrName]);
                //$this->log("FOUND VALUE $attrName = ".$values[$attrName]);
                $arr[$attrName]["value"] = $values[$attrName];
            } //else $this->log("NOT FOUND VALUE $attrName");
            if ($aDef->hasAttribute("type")) $arr[$attrName]["type"] = $aDef->getAttribute("type");
            else {
                $restrictions = $xsdxpath->evaluate(self::QUERY_XSD_BASE_TYPE, $aDef);
                if ($restrictions->length==1 && $restrictions->item(0)->hasAttribute("base"))
                    $arr[$attrName]["type"] = $restrictions->item(0)->getAttribute("base");
                else if ($restrictions->length>1) {
                    $arr[$attrName]["type"] = [];
                    foreach($restrictions as $aRest) {
                        if ($aRest->hasAttribute("base"))
                            $arr[$attrName]["type"][] = $aRest->getAttribute("base");
                    }
                }
            }
            if ($aDef->hasAttribute("fixed")) $arr[$attrName]["fixed"] = $aDef->getAttribute("fixed");
        }
        if (isset($valKeys)) {
            $attributesToDelete = [];
            foreach($valKeys as $val) {
                if(is_string($val) && $val[0]==="@") {
                    if (!in_array($val, ["@schemaLocation"])) $arr[$val] = ["xml"=>$values[$val]];
                    $attributesToDelete[] = $val;
                }
            }
            $valKeys = array_diff($valKeys, $attributesToDelete);
        }
        $elemDefs = $xsdxpath->evaluate(self::QUERY_XS_ELEMENT, $node);
        if (isset($values) || !$node->hasAttribute("minOccurs") || $node->getAttribute("minOccurs")!=="0") {
            foreach($elemDefs as $eDef) {
                $eDefName = $eDef->getAttribute("name");
                if (in_array(strtolower($eDefName),["cfdirelacionado","concepto","informacionaduanera","parte","traslado","retencion"])) {
                    $valIdx=0;
                    if (is_iterable($values)) {
                        foreach($values as $val) {
                            $valIdx++;
                            $arrEDefName = $eDefName.$valIdx;
                            if (!isset($values[$arrEDefName])) break;
                            $arr[$arrEDefName] = $this->explodeXSDElement($eDef, $values[$arrEDefName], $xpathkey, $depth+1);
                            if (isset($valKeys)) $valKeys = array_diff($valKeys, [$arrEDefName]);
                        }
                    }
                    continue;
                } else if (isset($values) && isset($values[$eDefName])) {
                    $eDefValues = $values[$eDefName];
                    if (isset($valKeys)) $valKeys = array_diff($valKeys, [$eDefName]);
                } else $eDefValues = null;
                $arr[$eDefName] = $this->explodeXSDElement($eDef, $eDefValues, $xpathkey, $depth+1);
            }
        } else if (in_array($node->getAttribute("name"),["CfdiRelacionados","CuentaPredial"])) {
            $arr = ["use"=>"optional", "type"=>"xs:string"];
        }
        // TODO: Complemento y Addenda permiten subnodos ANY, generar basado unicamente en $values
        // TODO: En Complemento cambiar a XPath de TimbreFiscalDigital
        // TODO: Verificar en donde pueden ocuparse registrofiscal o donat
        //$anyElems = $this->existsXPath(self::QUERY_XS_ANY);
        $anyElems = $xsdxpath->evaluate(self::QUERY_XS_ANY, $node);
        if (!empty($anyElems) && $anyElems->length>0) {
            if ($node->getAttribute("name")==="Complemento") {
                $tfdxpath = self::$xsddata["xpath.".self::TFD11];
                if (isset($tfdxpath)) {
                    $elementsDefs = $tfdxpath->evaluate(self::QUERY_XS_TOP_ELEMENT);
                    foreach($elementsDefs as $elemDef) {
                        $attrname = $elemDef->getAttribute("name");
                        if (isset($values[$attrname])) { // TimbreFiscalDigital
                            $arr[$attrname] = $this->explodeXSDElement($elemDef, $values[$attrname], self::TFD11, $depth+1);
                            if (isset($valKeys)) $valKeys = array_diff($valKeys, [$attrname]);
                        }
                    }
                }
                /*
                $pagoxpath = self::$xsddata["xpath.".self::PAGO10];
                if (isset($pagoxpath)) {
                    $elementsDefs = $pagoxpath->evaluate(self::QUERY_XS_TOP_ELEMENT);
                    foreach($elementsDefs as $elemDef) {

                    }
                }
                $cpxpath = self::$xsddata["xpath.".self::CARTAPORTE];
                if (isset($cpxpath)) {
                    $elementsDefs = $cpxpath->evaluate(self::QUERY_XS_TOP_ELEMENT);
                    foreach($elementsDefs as $elemDef) {
                        $attrname = $elemDef->getAttribute("name");
                        if (isset($values[$attrname])) { // CartaPorte
                            $arr[$attrname] = $this->explodeXSDElement($elemDef, $values[$attrname], self::CARTAPORTE, $depth+1);
                            if (isset($valKeys)) $valKeys = array_diff($valKeys, [$attrname]);
                        }
                    }
                }
                $cp2xpath = self::$xsddata["xpath.".self::CARTAPORTE20];
                if (isset($cp2xpath)) {
                    $elementsDefs = $cp2xpath->evaluate(self::QUERY_XS_TOP_ELEMENT);
                    foreach($elementsDefs as $elemDef) {
                        $attrname = $elemDef->getAttribute("name");
                        if (isset($values[$attrname])) { // CartaPorte20
                            $arr[$attrname] = $this->explodeXSDElement($elemDef, $values[$attrname], self::CARTAPORTE20, $depth+1);
                            if (isset($valKeys)) $valKeys = array_diff($valKeys, [$attrname]);
                        }
                    }
                }
                */
            }
        }
        if (isset($valKeys)) foreach($valKeys as $val) {
            $arr[$val] = ["xml"=>$values[$val]];
        }
        return $arr;
    }
    private function explodeNode($node, $isCaseSensitive=false, $prompt="") {
        $doLog=CFDI::DO_LOG_EXPLODE;
        //$nodeLog=["node"=>json_encode($node)];
        if (isset($prompt[0])) $nodeLog["prompt"]=$prompt;
        $esObj = is_object($node);
        $esNod = ($esObj&&is_a($node,"DOMNode"));
        if ($esObj) {
            $clnod = get_class($node);
            $nodeLog["class"]=$clnod;
        }
        if ($esNod) {
            $name=$node->localName;
            $lcname=strtolower($name);
            $nodeLog["name"]=$name;
            $jsnode=json_encode($node);
            if ( in_array($lcname, ["annotation","simpletype"])) {
                if ($doLog) $this->log("{$prompt}XNODE|IGNORE: ".json_encode($nodeLog));
                return null;    
            }
            $type=$node->nodeType;
            $nodeLog["type"]=$type;
            $esElem = ($type==XML_ELEMENT_NODE);
            $value = $node->nodeValue;
            $trval = trim($value);
            $val20 = substr($trval,0,20);
            $nodeLog["value"]=$val20;
            if (isset($lcname[0]) && $lcname[0]==="c" && isset($lcname[1]) && $lcname[1]==="o" && $lcname[1]==="n" ) {
                //clog2("$prompt$name='$val20'".($node->hasAttribute("Importe")?" importe='".$node->getAttribute("Importe")."'":""));
            }
        }
        if (empty(trim($name))) {
            if ($doLog) $this->log("{$prompt}XNODE|NONAME: ".json_encode($nodeLog));
            return null;
        }
        if (XML_TEXT_NODE == $type) {
            if ($doLog) $this->log("{$prompt}XNODE|ISTEXT: ".json_encode($nodeLog));
            return $value;
        }
        if (XML_ATTRIBUTE_NODE == $type) {
            if ($doLog) $this->log("{$prompt}XNODE|ISATTR: ".json_encode($nodeLog));
            return $node->value;
        }
        if ($doLog) $this->log("{$prompt}XNODE|NODE: ".json_encode($nodeLog));
        $arr = [];
        if ($node->hasAttributes()) foreach($node->attributes as $attr) {
            $attrName = $attr->localName;
            if ($isCaseSensitive)
                $arr['@'.$attrName] = $attr->nodeValue;
            else
                $arr['@'.strtolower($attrName)] = $attr->nodeValue;
        }
        if (!empty($arr)) {
            if ($doLog) $this->log("{$prompt}XNODE|ATTRIBS: ".json_encode($arr));
        }
        $sequenceIndex=0;
        if ($node->hasChildNodes()) {
            foreach($node->childNodes as $chlNd) {
                $childName = $chlNd->localName;
                if (in_array(strtolower($childName),["cfdirelacionado","concepto","informacionaduanera","parte","traslado","retencion"])) {
                    $sequenceIndex++;
                    $childName.=$sequenceIndex;
                }
                if (XML_TEXT_NODE == $chlNd->nodeType) {
                    $val = trim($chlNd->nodeValue);
                    if (isset($val[0])) {
                        $arr[$childName] = $chlNd->nodeValue;
                        if ($doLog) $this->log("{$prompt}XNODE|TXTCHILD: $childName='".$chlNd->nodeValue."'");
                    } else if ($doLog) $this->log("{$prompt}XNODE|TXTCHILD: $childName IS EMPTY");
                } else if ( 1 == $chlNd->childNodes->length && XML_TEXT_NODE == $chlNd->firstChild->nodeType) {
                    $val = trim($chlNd->firstChild->nodeValue);
                    if (isset($val[0])) {
                        $arr[$childName] = $chlNd->firstChild->nodeValue;
                        if ($doLog) $this->log("{$prompt}XNODE|TXTCHILD_2: $childName='".$arr[$childName]."'");
                    } else if ($doLog) $this->log("{$prompt}XNODE|TXTCHILD_2: $childName IS EMPTY");
                    $arr[$childName] = $chlNd->firstChild->nodeValue;
                    if ($doLog) $this->log("{$prompt}XNODE|TXTCHILD_2B: $childName='".$arr[$childName]."'");
                } else {
                    if ($doLog) $this->log("{$prompt}XNODE|MULTIPLE $childName");
                    if ( !empty($a = $this->explodeNode($chlNd, $isCaseSensitive, $prompt."  "))) {
                        //if(isset($arr[$childName]) || $chlNd->
                        if (isset($arr[$childName])) {
                            if (!is_array($arr[$childName])||array_key_first($arr[$childName])!==0) $arr[$childName]=[$arr[$childName]];
                            $arr[$childName][] = $a;
                            if ($doLog) $this->log("{$prompt}XNODE|CHILD $childName IS ARR LEN=".count($arr[$childName]).($childName==="TrasladoP"?". ARR=".json_encode($arr[$childName]):""));
                        } else {
                            $arr[$childName] = $a;
                            if ($doLog) $this->log("{$prompt}XNODE|CHILD $childName IS FIRST");
                        }
                    } else if ($doLog) $this->log("{$prompt}XNODE NO CHILD $childName");
                }
            }
            /* if ($doLog) {

                $this->log("{$prompt}XNODE|CHILDNODES: ");
            } */
        }
        return $arr;
    }
    public function existsXPath($xquery) {
//            $this->log("INI function existsXPath (xquery=$xquery)");
        $this->lastResult = $this->evaluateXPath($xquery); // $this->xpath->evaluate($xquery);
        if (is_object($this->lastResult) && get_class($this->lastResult)==="DOMNodeList") return $this->lastResult->length>0;
        return !empty($this->lastResult);
    }
    public function getLog() {
        return $this->log;
    }
    public function schemaValidate($xsd=null) { // $localPath=self::PATHXSD."cfd\\";
        if (!isset($xsd)) $xsd=self::PATHXSD."cfd\\".self::CFDPATH[$this->xsd]."\\".$this->xsd;
        //clog2ini("CFDI.object.schemaValidate");
        doclog("CFDI:schemaValidate","cfdiLog",["xsd"=>$xsd]);
        $retval = $this->xmldoc->schemaValidate($xsd);
        //clog2end("CFDI.object.schemaValidate");
        
        return $retval;
    }
    public function checkParent($nodeName, $parentList) {
        /*
        $xqueryGeneral = "count(//*[local-name() = '$nodeName'])";
        $countGeneral = +$this->xpath->evaluate($xqueryGeneral);
        if ($countGeneral==0) return false;
        */
        $xquery4Parent = "";
        foreach($parentList as $theParentToCheck) {
            if (isset($xquery4Parent[0])) $xquery4Parent.="|";
            $xquery4Parent .= "$theParentToCheck//*[local-name() = '$nodeName']";
        }
        $count4Parent = +$this->xpath->evaluate("count($xquery4Parent)");
        if ($count4Parent>0) {
            return true;
        }
        return false;
    }
    private function log($texto) {
        if (isset($texto[0])) $texto=trim(strip_tags($texto));
        if (!isset($texto[0])) return;
        $this->log .= "// # $this->logPrompt # $texto\n";
    }
    private function error($texto, $blockTag="DIV", $blockAttributes="") {
        if (!isset($texto[0])) return;
        $this->enough = false;
        if (isset($blockTag[0])) $this->errMsg = "<$blockTag $blockAttributes>$texto</$blockTag>";
        else $this->errMsg=$texto;
        // identifica tags, detecta si tienen class hidden, de ser así elimina todo el tag
        // FIX: En lugar de hacer este paso, al crear mensaje de error, en lugar de ponerle clase hidden, mejor no incluir el error para nada, pero si es posible, permitirlo en el log.
        $textonly=trim(strip_tags($texto));
        if (!isset($textonly[0])) return;
        $this->log .= "// # $this->logPrompt # ERROR # $textonly\n";
        $this->errStack .= $textonly;
    }
}
class MYLIBXML {
    public static $logval=null;
    static $ERROR = [
        1824 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)', attribute '([^']*)': '([^']*)' is not a valid value of the (.*)\./", // type '((?:{[^}]*})?)([^']*)'\./", //
            "return" => "<b>#2#.#3#</b> : '#4#' no es válido para el tipo de dato predefinido correspondiente.",
            "identifier" => 3,
            "greyed" => true,
            "doCurrentAction" => true
        ],
        1830 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)', attribute '([^']*)': \[facet 'length'\] The value '([^']*)' has a length of '([^']*)'\; this differs from the allowed length of '([^']*)'\./",
            "return" => "<b>#2#.#3#</b> : '#4#' tiene longitud '#5#'; no corresponde a la longitud válida '#6#'.",
            "identifier" => 3,
            "greyed" => false,
            "doCurrentAction" => false,
        ],
        1838 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)', attribute '([^']*)': \[facet 'fractionDigits'\] The value '([^']*)' has more fractional digits than are allowed \('([^']*)'\)\./",
            "return" => "<b>#2#.#3#</b> : '#4#' excede el máximo de dígitos decimales permitidos de '#5#'.",
            "identifier" => 3,
            "greyed" => false,
            "doCurrentAction" => false,
        ],
        1839 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)', attribute '([^']*)': \[facet 'pattern'\] The value '([^']*)' is not accepted by the pattern '([^']*)'\./",
            "return" => "<b>#2#.#3#</b> : '#4#' no tiene una sintaxis aceptable. Verifique que todos los caracteres sean válidos y coincidan con los patrones de sintaxis válidos.",
            "identifier" => 3,
            "greyed" => true,
            "doCurrentAction" => true
        ],
        1840 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)', attribute '([^']*)': \[facet 'enumeration'\] The value '([^']*)' is not an element of the set ((?:{[^}]*})?)\./",
            "return" => "<b>#2#.#3#</b> : '#4#' no existe en la lista de valores aceptables.",
            "identifier" => 3,
            "greyed" => false,
            "doCurrentAction" => false 
        ],
        1845 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)': (.*)/",
            "conditions" => [2,2,2,2],
            "condvalues" => ["TimbreFiscalDigital", "Pagos", "CFDIRegistroFiscal", "Addenda", "VentaVehiculos", "CartaPorte", "ImpuestosLocales"],
            "return" => [false, false, false, false, "Se encontr&oacute; elemento <b>#2#</b> no declarado en esquema sint&aacute;ctico."],
            "identifier" => 2,
            "greyed" => true,
            "doCurrentAction" => true
        ],
        1866 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)', attribute '([^']*)': (.*)/",
            "conditions" => [2],
            "condvalues" => ["Addenda"],
            "return" => [false, "Se encontr&oacute; elemento <b>#2#.#3#</b> no permitido."],
            "identifier" => 3,
            "greyed" => false,
            "doCurrentAction" => false
        ],
        1868 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)': The attribute '([^']*)' (.*)/",
            "return" => "No se encontr&oacute; elemento <b>#2#.#3#</b> y es requerido.",
            "identifier" => 3,
            "greyed" => false,
            "doCurrentAction" => false
        ],
        1871 => [
            "pattern" => "/Element '((?:{[^}]*})?)([^']*)': This element is not expected(.*)/",
            "conditions" => [2],
            "condvalues" => ["Addenda"],
            "return" => [false, "Se encontr&oacute; elemento <b>#2#</b> y no se esperaba."],
            "identifier" => 2,
            "greyed" => false,
            "doCurrentAction" => false
        ]
    ];
    const MIN_ERR_MATCH_IDX = 2;
    static function getTypeError($error) {
        switch($error->level) {
            case LIBXML_ERR_WARNING: return "Warning";
            case LIBXML_ERR_FATAL: return "Fatal Error";
            case LIBXML_ERR_ERROR: return "Error";
        }
        return "Other Error";
    }
    static function textError($error) {
        return self::getTypeError($error)." ".$error->code.": ".trim($error->message);
    }
    static function displayErrors($error, &$uniqueIdentifierList, $blockTag="DIV", $cfdiObj=null) {
        if (!isset($uniqueIdentifierList)) $uniqueIdentifierList=[];
        $tip = "<b>".self::getTypeError($error)." ".$error->code."</b>: ".trim($error->message);
        if (isset($error->column) && $error->column>0) $tip .= ", column <b>$error->column</b>";
        $tip = str_replace(["'",'"'], ["&apos;","&quot;"], strip_tags($tip));
        if (isset(self::$ERROR[$error->code])) {
            $err_stt_data = self::$ERROR[$error->code];
            preg_match($err_stt_data["pattern"], $error->message, $matches);
            $code = "[".count($matches).": ";
            for($i=1;isset($matches[$i]);$i++) {
                if ($i>1) $code .= ", ";
                $code .= str_replace(["'",'"'], ["&apos;","&quot;"], strip_tags($matches[$i]));
            }
            $code .= "]";
            $code = str_replace("&", "&amp;", $code);
            if (isset($err_stt_data["identifier"])) {
                $identifier = $err_stt_data["identifier"];
                if (is_numeric($identifier) && isset($matches[+$identifier])) {
                    $identifier = +$identifier;
                    if ($identifier == 3) $identifier = $matches[2].".".$matches[3];
                    else $identifier = $matches[$identifier];
                } else $identifier = "&lt;$identifier&gt;";
            }
            $classBlock=["lefted"=>1, "wordwrap"=>1];
            if ($err_stt_data["greyed"]) $classBlock["greyed"]=1;
            if (isset($cfdiObj) && $error->code==1845 && $cfdiObj->checkParent($identifier, ["/cfdi:Comprobante/cfdi:Addenda", "/cfdi:Comprobante/cfdi:Complemento", "/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:ComplementoConcepto"])) { $classBlock["hidden"]=1; $classBlock["anyNode"]=1; }
            if (isset($uniqueIdentifierList[$identifier])) { $classBlock["hidden"]=1; $classBlock["duplicate"]=1; }
            $uniqueIdentifierList[$identifier]=1;
            if (is_array($err_stt_data["return"]) && is_array($err_stt_data["conditions"])) {
                $esd_conditions = $err_stt_data["conditions"];
                $esd_condvalues = $err_stt_data["condvalues"];
                $esd_returns = $err_stt_data["return"];
                $c=0;
                for(; isset($esd_returns[$c]) && isset($esd_conditions[$c]); $c++) {
                    if ($matches[$esd_conditions[$c]]===$esd_condvalues[$c]) {
                        $tmpReturn = $esd_returns[$c];
                        break;
                    }
                }
                if (!isset($tmpReturn)) {
                    if (isset($esd_returns[$c])) $tmpReturn = $esd_returns[$c];
                    else $tmpReturn = $esd_returns[$c-1]; // $tmpReturn = $esd_returns[0];
                }
            } else {
                $tmpReturn = $err_stt_data["return"];
                if (is_array($tmpReturn)) $tmpReturn=$tmpReturn[0]; // $tmpReturn=$tmpReturn[count($tmpReturn)-1];
            }
            if (!isset($tmpReturn)) { $classBlock["hidden"]=1; $classBlock["notdefined"]=1; }
            else if ($tmpReturn===false) { $classBlock["hidden"]=1; $classBlock["filtered"]=1; }
            else if (isset($tmpReturn[0])&&strpos($tmpReturn, "#")!==false&&isset($matches[1])) {
                //$minErrMatchIdx=1;
                for($m=1/*$minErrMatchIdx*/; isset($matches[$m]); $m++) $tmpReturn = str_replace("#$m#", $matches[$m], $tmpReturn);
            } else $tmpReturn=$error->message;
            $return = "<$blockTag title=\"$tip\" onclick=\"conlog('$code');";
            if ($err_stt_data["doCurrentAction"]) $return .= "doCurrentAction(this);";
            $return .= "\" class=\"".implode(" ",array_keys($classBlock))."\"";
            if (!isset($classBlock["hidden"])||!$classBlock["hidden"])
                $return .= ">$tmpReturn";
            else $return .= " tmpText=\"$tmpReturn\">";
            $return .= "</$blockTag>";
        } else $return = "<$blockTag class='lefted wordwrap'>$tip</$blockTag>";
        if (isset($return[0])) {
            $logData=["errorType"=>self::getTypeError($error),"error"=>(array)$error];
            if (isset($classBlock)) $logData["classBlock"]=$classBlock;
            $logData["return"]=$return;
            doclog("CFDI:displayErrors","cfdiLog",$logData);
        }
        return $return;
    }
    static function validation($cfdiObj, $blockTag="DIV") {
        $retval = [];
        self::$logval = [];
        $nErr = 0;
        if (!isset($cfdiObj->cache["valerr"])) {
            $cfdiObj->cache["valerr"]=[];
        }
        $valErrIdx=count($cfdiObj->cache["valerr"]);
        libxml_use_internal_errors(true);
        if (!$cfdiObj->schemaValidate()) {
            $errors = libxml_get_errors();
            foreach ($errors as $errIdx=>$error) {
                $valErrIdx++;
                $hasErrCode=isset(self::$ERROR[$error->code]);
                $cfdiObj->cache["valerr"][$valErrIdx-1]=["error"=>(array)$error, "errIdx"=>$errIdx, "hasErrCode"=>($hasErrCode?"SI":"NO")];
                if ($hasErrCode) {
                    $valMode=$cfdiObj->cache["valmode"]??"";
                    $cfdiObj->cache["valerr"][$valErrIdx-1]["valmode"]=$valMode;
                    if ($valMode==="auto") {
                        if($error->code==1845) {
                            if (strpos($error->message, "TimbreFiscalDigital")!==false) {
                                $uuid=$cfdiObj->get("uuid");
                                $cfdiObj->cache["valerr"][$valErrIdx-1]["uuid"]=$uuid;
                                if(isset($uuid[0])) {
                                    doclog("CFDI:validation IGNORAR ERROR CON TIMBRADO","cfdiLog",["error"=>(array)$error, "uuid"=>$uuid]);
                                    $cfdiObj->cache["valerr"][$valErrIdx-1]["type"]="TimbreFiscalDigital";
                                    continue;
                                } else doclog("CFDI:validation ERROR LIBXML: Sin uuid","cfdiLog",["error"=>(array)$error]);
                            } else if (strpos($error->message, "CartaPorte20")!==false) {
                                $cp20=$cfdiObj->get("CartaPorte20");
                                if (isset($cp20["@version"]) && $cp20["@version"]==="2.0") {
                                    doclog("CFDI:validation IGNORAR ERROR CON CARTAPORTE20","cfdiLog",["error"=>(array)$error, "cartaPorte"=>$cp20]);
                                    $cfdiObj->cache["valerr"][$valErrIdx-1]["type"]="CartaPorte20";
                                    continue;
                                } else doclog("CFDI:validation ERROR LIBXML: Sin cartaporte20","cfdiLog",["error"=>(array)$error]);
                            } else if (strpos($error->message, "http://www.sat.gob.mx/Pagos")!==false) {
                                $pagos=$cfdiObj->get("pago_doctos");
                                if (isset($pagos["@iddocumento"])||isset($pagos[0]["@iddocumento"])) {
                                    doclog("CFDI:validation IGNORAR ERROR CON PAGOS", "cfdiLog",["error"=>(array)$error, "pagoDoctos"=>$pagos]);
                                    $cfdiObj->cache["valerr"][$valErrIdx-1]["type"]="Pagos";
                                    continue;
                                } else doclog("CFDI:validation ERROR LIBXML: Sin pagos","cfdiLog",["error"=>(array)$error]);
                            } else if (strpos($error->message, "http://www.sat.gob.mx/ventavehiculos")!==false) {
                                $conceptos=$cfdiObj->get("conceptos");
                                doclog("CFDI:validation IGNORAR ERROR CON VEHICULOS", "cfdiLog", ["error"=>(array)$error, "conceptos"=>$conceptos]);
                                $cfdiObj->cache["valerr"][$valErrIdx-1]["type"]="ventavehiculos";
                                continue;
                            } else doclog("CFDI:validation ERROR LIBXML: OTROS","cfdiLog",["error"=>(array)$error]);
                        }
                        doclog("CFDI:validation ERROR EN VALIDACION DE SCHEMA","cfdiLog",["error"=>(array)$error]);
                    } else {
                        doclog("CFDI:validation ERROR SIN MODO AUTO","cfdiLog",["valmode"=>$valMode,"error"=>(array)$error, "cache"=>$cfdiObj->cache, "trace"=>generateCallTrace()]);
                    }
                } else {
                    doclog("CFDI:validation ERROR SIN VALIDAR","cfdiLog",["error"=>(array)$error]);
                    self::$logval[]=json_encode($error);
                }
                CFDI::setLastErrorText(self::textError($error));
                $errmsg = self::displayErrors($error, $uniqueIdentifierList, $blockTag, $cfdiObj);
                $cfdiObj->cache["valerr"][$valErrIdx-1]["type"]="error";
                $cfdiObj->cache["valerr"][$valErrIdx-1]["msg"]=$errmsg;
                if (!empty($errmsg)) {
                    if (!preg_match("/class=\"[^\"]*hidden[^\"]*\"/", $errmsg)) {
                        $nErr++;
                        if ($hasErrCode) {
                            $error->mensaje=$errmsg;
                            self::$logval[]=json_encode($error);
                        }
                    }
                    $retval[] = $errmsg; //array_unshift($retval, $errmsg);
                }
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        if ($nErr===0) return [];
        return $retval;
    }
}
