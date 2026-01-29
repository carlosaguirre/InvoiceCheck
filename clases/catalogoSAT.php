<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";

class CatalogoSAT {
    const CAT_ADUANA =             "Aduana";
    const CAT_CLAVEPRODSERV =      "ClaveProdServ";
    const CAT_CLAVEUNIDAD =        "ClaveUnidad";
    const CAT_CODIGOPOSTAL =       "CodigoPostal";
    const CAT_FORMAPAGO =          "FormaPago";
    const CAT_IMPUESTO =           "Impuesto";
    const CAT_METODOPAGO =         "MetodoPago";
    const CAT_MONEDA =             "Moneda";
    const CAT_NUMPEDIMENTOADUANA = "NumPedimentoAduana";
    const CAT_PAIS =               "Pais";
    const CAT_PATENTEADUANAL =     "PatenteAduanal";
    const CAT_REGIMENFISCAL =      "RegimenFiscal";
    const CAT_TASAOCUOTA =         "TasaOCuota";
    const CAT_TIPODECOMPROBANTE =  "TipoDeComprobante";
    const CAT_TIPOFACTOR =         "TipoFactor";
    const CAT_TIPORELACION =       "TipoRelacion";
    const CAT_USOCFDI =            "UsoCFDI";
    const CAT_ESTADO =             "Estado";
    const CAT_LOCALIDAD =          "Localidad";
    const CAT_MUNICIPIO =          "Municipio";
    const CAT_COLONIA =            "Colonia";
    // Carta Porte
    const CAT_CVETRANSPORTE         = "Transporte";
    const CAT_TIPOESTACION          = "TipoEstacion";
    const CAT_ESTACIONES            = "Estaciones";
    const CAT_CLAVEUNIDADPESO       = "UnidadPeso";
    const CAT_CLAVEPRODSERVCP       = "ProdServCP";
    const CAT_MATERIALPELIGROSO     = "MaterialPeligroso";
    const CAT_TIPOEMBALAJE          = "TipoEmbalaje";
    const CAT_TIPOPERMISO           = "TipoPermiso";
    const CAT_CONFIGAUTOTRANSPORTE  = "ConfigAutotransporte";
    const CAT_SUBTIPOREM            = "SubtipoRemolque";
    const CAT_CONFIGMARITIMA        = "ConfigMaritima";
    const CAT_CLAVETIPOCARGA        = "TipoCarga";
    const CAT_CONTENEDORMARITIMO    = "ContenedorMaritimo";
    const CAT_NUMAUTORIZACIONNAVIERO= "NumAutorizacionNaviero";
    const CAT_CODIGOTRANSPORTEAEREO = "TransporteAereo";
    const CAT_CLAVEPRODSTCC         = "ProdSTCC";
    const CAT_TIPODESERVICIO        = "TipoServicio";
    const CAT_DERECHOSDEPASO       = "DerechosDePaso";
    const CAT_TIPOCARRO            = "TipoCarro";
    const CAT_CONTENEDOR           = "Contenedor";
    
    private static $catalogList = [];
    private static $log = "";
    private function __construct() { 
        self::$log = "INIT CLASS: CatalogoSAT\n";
    }
    private function __clone() { }
    public static function hasOrder($c_catalogo, $fieldname) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return false;
        return $dbobj->hasOrder($fieldname);
    }
    public static function resetLog($c_catalogo) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return;
        $dbobj->log="";
    }
    public static function getLog($c_catalogo) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return "";
        return $dbobj->log;
    }
    public static function setOrder($c_catalogo, $fieldset) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return;
        $dbobj->clearOrder();
        foreach ($fieldset as $sortField) {
            if (is_array($sortField)||is_object($sortField)) {
                if (!empty($sortField[0])) {
                    $sortname = $sortField[0];
                } else if (!empty($sortField["name"])) {
                    $sortname = $sortField["name"];
                }
                if (!empty($sortField[1])) {
                    $sortdir = $sortField[1];
                } else if (!empty($sortField["direction"])) {
                    $sortdir = $sortField["direction"];
                }
                if (isset($sortname) && isset($sortdir)) {
                    $dbobj->addOrder($sortname, $sortdir);
                } else if (isset($sortname)) {
                    $dbobj->addOrder($sortname);
                }
            }
        }
    }
    public static function getFullMap($c_catalogo, $key, $value, $where=false, $forceLoad=false) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        if ($forceLoad) $dbobj->clearFullMap();
        return $dbobj->getFullMap($key, $value, $where);
    }
    public static function getList($c_catalogo, $keyElement, $keyValue, $searchElement, $additionalWhere=false, $additionalSql=false) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        return $dbobj->getList($keyElement, $keyValue, $searchElement, $additionalWhere, $additionalSql);
    }
    public static function getValue($c_catalogo, $keyElement, $keyValue, $returnElements, $additionalWhere=false, $additionalSql=false, $fullData=false) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        return $dbobj->getValue($keyElement, $keyValue, $returnElements, $additionalWhere, $additionalSql, $fullData);
    }
    public static function exists($c_catalogo, $where) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return false;
        return $dbobj->exists($where);
    }
    public static function getData($c_catalogo, $where=false, $rowsPerPage=0, $pageNum=0, $presetTotNumRows=0) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        $dbobj->pageno = $pageNum;
        $dbobj->rows_per_page = $rowsPerPage;
        return $dbobj->getData($where, $presetTotNumRows);
    }
    public static function getHeaders($c_catalogo) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        $list = $dbobj->fieldlist;
        $headers = [];
        for($i=0; isset($list[$i]); $i++) {
            $headers[$i] = $list[$i];
        }
        return $headers;
    }
    public static function fetchHeaders($c_catalogo) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        return $dbobj->fetch_headers;
    }
    public static function getSetExpression($c_catalogo, $item, $value) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        return $dbobj->getQueryExpression($item, $value, "SET");
    }
    public static function getWhereCondition($c_catalogo, $item, $value) {
        $dbobj = self::get($c_catalogo);
        if (empty($dbobj)) return null;
        return $dbobj->getQueryExpression($item, $value, "WHERE");
    }
    private static function get($c_catalogo) {
        if (!isset(self::$catalogList[$c_catalogo])) {
            switch($c_catalogo) {
                case self::CAT_ADUANA:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_CLAVEPRODSERV:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"conIvaTrasladado",
                                    4=>"conIEPSTrasladado",
                                    5=>"complemento",
                                    6=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_CLAVEUNIDAD:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"nombre",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_CODIGOPOSTAL:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"estado",
                                    3=>"municipio",
                                    4=>"localidad",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_FORMAPAGO:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"bancarizado",
                                    4=>"numOperacion",
                                    5=>"conRFCEmisorCtaOrd",
                                    6=>"conCtaOrdenante",
                                    7=>"patronCtaOrd",
                                    8=>"conRFCEmisorCtaBen",
                                    9=>"conCtaBeneficiario",
                                    10=>"patronCtaBen",
                                    11=>"conTipoCadenaPago",
                                    12=>"conNombreBancoEmisorOrdExt",
                                    13=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_IMPUESTO:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"retencion",
                                    4=>"traslado",
                                    5=>"tipo",
                                    6=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_METODOPAGO:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_MONEDA:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"decimales",
                                    4=>"variacion",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_NUMPEDIMENTOADUANA:
                    $myFieldList = [0=>"id",
                                    1=>"codigoAduana",
                                    2=>"patente",
                                    3=>"ejercicio",
                                    4=>"cantidad",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"]];
                    break;
                case self::CAT_PAIS:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"formatoCP",
                                    4=>"formatoRIT",
                                    5=>"agrupaciones",
                                    6=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_PATENTEADUANAL:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_REGIMENFISCAL:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"paraPFisica",
                                    4=>"paraPMoral",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_TASAOCUOTA:
                    $myFieldList = [0=>"id",
                                    1=>"tipo",
                                    2=>"minimo",
                                    3=>"maximo",
                                    4=>"impuesto",
                                    5=>"factor",
                                    6=>"traslado",
                                    7=>"retencion",
                                    8=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"]];
                    break;
                case self::CAT_TIPODECOMPROBANTE:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"maximo",
                                    4=>"maxDS",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPOFACTOR:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPORELACION:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_USOCFDI:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"descripcion",
                                    3=>"paraPFisica",
                                    4=>"paraPMoral",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"]];
                    break;
                case self::CAT_ESTADO:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"codigoPais",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"],
                                    "codigoPais"=>["skey"=>"y"]];
                    break;
                case self::CAT_LOCALIDAD:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"codigoEstado",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"],
                                    "codigoEstado"=>["skey"=>"y"]];
                    break;
                case self::CAT_MUNICIPIO:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"codigoEstado",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"],
                                    "codigoEstado"=>["skey"=>"y"]];
                    break;
                case self::CAT_COLONIA:
                    $myFieldList = [0=>"id",
                                    1=>"codigo",
                                    2=>"codigoPostal",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "codigo"=>["skey"=>"y"],
                                    "codigoPostal"=>["skey"=>"y"]];
                    break;
                case self::CAT_CVETRANSPORTE:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPOESTACION:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"clavetransporte",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_ESTACIONES:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"clavetransporte",
                                    4=>"nacionalidad",
                                    5=>"designadorIATA",
                                    6=>"lineaFerrea",
                                    7=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CLAVEUNIDADPESO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"nombre",
                                    3=>"descripcion",
                                    4=>"nota",
                                    5=>"simbolo",
                                    6=>"bandera",
                                    7=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CLAVEPRODSERVCP:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_MATERIALPELIGROSO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"clasediv",
                                    4=>"secundario",
                                    5=>"grupoONU",
                                    6=>"especial",
                                    7=>"limitadas",
                                    8=>"exceptuadas",
                                    9=>"embalajeI",
                                    10=>"embalajeE",
                                    11=>"cisternaI",
                                    12=>"cisternaE",
                                    13=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPOEMBALAJE:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPOPERMISO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"clavetransporte",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CONFIGAUTOTRANSPORTE:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"ejes",
                                    4=>"llantas",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_SUBTIPOREM:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CONFIGMARITIMA:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CLAVETIPOCARGA:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CONTENEDORMARITIMO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_NUMAUTORIZACIONNAVIERO:
                    $myFieldList = [0=>"id",
                                    1=>"num",
                                    2=>"inicio",
                                    3=>"vencimiento",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "num"=>["skey"=>"y"]];
                    break;
                case self::CAT_CODIGOTRANSPORTEAEREO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"nacionalidad",
                                    3=>"aerolinea",
                                    4=>"oaci",
                                    5=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CLAVEPRODSTCC:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPODESERVICIO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"descripcion",
                                    3=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_DERECHOSDEPASO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"derecho",
                                    3=>"entre",
                                    4=>"hasta",
                                    5=>"otorgarecibe",
                                    6=>"concesionario",
                                    7=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_TIPOCARRO:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"tipo",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
                case self::CAT_CONTENEDOR:
                    $myFieldList = [0=>"id",
                                    1=>"clave",
                                    2=>"tipo",
                                    3=>"descripcion",
                                    4=>"modifiedTime",
                                    "id"=>["pkey"=>"y", "auto"=>"y"],
                                    "modifiedTime"=>["auto"=>"y"],
                                    "clave"=>["skey"=>"y"]];
                    break;
            }
            if (empty($myFieldList)) return null;
            self::$catalogList[$c_catalogo] = new class($c_catalogo, $myFieldList) extends DBObject {
                public function __construct($catalogo, $fieldList) {
                    $this->tablename = "cat".$catalogo;
                    $this->fieldlist = $fieldList;
                    $this->log = "\n// xxxxxxxxxxxxxxx ".ucfirst($catalogo)." xxxxxxxxxxxxxxx //\n";
                }
            };
        }
        return self::$catalogList[$c_catalogo];
    }
}
