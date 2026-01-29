<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class SolicitudPago extends DBObject {
    const STATUS_SIN_FACTURA=0;
    const STATUS_CON_FACTURA=1;
    const STATUS_AUTORIZADA=2;
    const STATUS_ACEPTADA=4; // Valido para factura
    const STATUS_CONTRARRECIBO=8; // Valido para factura
    const STATUS_EXPORTADA=16; // Valido para factura
    const STATUS_RESPALDADA=32; // Valido para factura
    const STATUS_PAGADA=64; // Valido para factura
    const STATUS_SINCANCELAR=127;
    const STATUS_CANCELADA=128; // Valido para factura
    const PERFIL_SOLICITA=60;
    const PERFIL_AUTORIZA=61;
    const PERFIL_PAGA=62;
    const PERFIL_GESTIONA=63;
    const PROCESO_SINAUTORIZAR=-1;
    const PROCESO_AUTORIZADA=0; // autorizarPago|rechazaPago
    const PROCESO_COMPRAS=1; //transfiereArchivos|procesaCompras
    const PROCESO_CONTABLE=2; // procesaConta
    const PROCESO_ANEXADA=3;
    const PROCESO_PAGADA=4;
    const PROCESO_NOREQ_FACTURA=5;
    const TABLA_FECHA=["eName"=>"TABLE","className"=>"pad2c","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Fecha Inicial:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"fechaIniFld","className"=>"calendar","readOnly"=>"1","onclick"=>"show_calendar_widget(this,'adjustLstSolPCal');","tabIndex"=>"1"]]],["eName"=>"TD","rowSpan"=>"2","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","id"=>"appendFilterButton","className"=>"hoverDarkF5 pointer","width"=>"45","height"=>"45","tabIndex"=>"3"]]]]],["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Fecha Final:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"fechaFinFld","className"=>"calendar","readOnly"=>"1","onclick"=>"show_calendar_widget(this,'adjustLstSolPCal');","tabIndex"=>"2"]]]]]]]]];
    const TABLA_RANGO=["eName"=>"TABLE","className"=>"pad2c","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Rango Inicial:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"iniFld","tabIndex"=>"1"]]],["eName"=>"TD","rowSpan"=>"2","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","id"=>"appendFilterButton","className"=>"hoverDarkF5 pointer","width"=>"45","height"=>"45","tabIndex"=>"3"]]]]],["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Rango Final:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"finFld","tabIndex"=>"2"]]]]]]]]];
    const TABLA_TEXTO=["eName"=>"TABLE","className"=>"pad2c","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Texto:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"textFld","tabIndex"=>"0","className"=>"hasEnterId","enterId"=>"appendFilterButton"]]],["eName"=>"TD","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","id"=>"appendFilterButton","className"=>"hoverDarkF5 pointer","width"=>"21.5","height"=>"21.5","tabIndex"=>"0"]]]]]]]]];
    const TABLA_LISTA=["eName"=>"TABLE","className"=>"pad2c","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Lista:"],["eName"=>"TD","eChilds"=>[["eName"=>"SELECT","id"=>"listFld","eChilds"=>[["eName"=>"OPTION","eText"=>"Todas"]],"tabIndex"=>"0","className"=>"hasEnterId","enterId"=>"appendFilterButton"]]],["eName"=>"TD","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","id"=>"appendFilterButton","className"=>"hoverDarkF5 pointer","width"=>"21.5","height"=>"21.5","tabIndex"=>"0"]]]]]]]]]; // ,"multiple"=>true
    public static $listaFiltros=null;
    private static $lastFunc=null;
    private static $hasSolByCr=false;
    public static function init($empresas=null,$secciones=null) {
        self::$lastFunc=__FUNCTION__;
        //clog2("INI function init"); //: empresas=".json_encode($empresas).", secciones=".json_encode($secciones));
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $gpoObj->rows_per_page=0;
        $gpoObj->clearOrder();
        $gpoObj->addOrder("alias");
        if (isset($empresas[0])) $gpoData=$gpoObj->getData("id in (".implode(",", $empresas).")",0,"id,alias");
        else $gpoData=$gpoObj->getData(false,0,"id,alias");
        $aliasId=[];
        $optionEmpresas=[];
        foreach ($gpoData as $idx => $gpoRow) {
            $aliasId[$gpoRow["alias"]]=$gpoRow["id"];
            $optionEmpresas[]=["eName"=>"OPTION","value"=>$gpoRow["alias"],"eText"=>$gpoRow["alias"]];
        }
        global $prvObj;
        if (!isset($prvObj)) {
            require_once "clases/Proveedores.php";
            $prvObj=new Proveedores();
        }
        $prvObj->rows_per_page=0;
        $prvData=$prvObj->getData("status not in (\"inactivo\",\"eliminado\")",0,"id,codigo");
        $codigoId=[];
        $optionProveedores=[];
        foreach ($prvData as $idx => $prvRow) {
            $codigoId[$prvRow["codigo"]]=$prvRow["id"];
            $optionProveedores[]=["eName"=>"OPTION","value"=>$prvRow["codigo"],"eText"=>$prvRow["codigo"]];
        }
        $optionSecciones=[];
        foreach ($secciones as $key => $data) {
            $optionSecciones[]=["eName"=>"OPTION","value"=>$key,"eText"=>$data["title"]];
        }
        //clog1("optionSecciones=".json_encode($optionSecciones));
        self::$listaFiltros=[
        "filter01"=>["texto"=>"Fecha Solicitud","contenido"=>[fixProperty(self::TABLA_FECHA,[["id","fechaIniFld","fechaSolIniFld"],["id","fechaFinFld","fechaSolFinFld"]],0/*clog1("Fecha Solicitud","1")*/)],"ids"=>["fechaSolIniFld","fechaSolFinFld"],"type"=>"fecha","width"=>250,"height"=>66],
        "filter14"=>["texto"=>"Fecha Reciente","contenido"=>[fixProperty(self::TABLA_FECHA,[["id","fechaIniFld","fechaModIniFld"],["id","fechaFinFld","fechaModFinFld"]],0/*clog1("Fecha Reciente","1")*/)],"ids"=>["fechaModIniFld","fechaModFinFld"],"type"=>"fecha","width"=>250,"height"=>66],
        "filter02"=>["texto"=>"Fecha Pago","contenido"=>[fixProperty(self::TABLA_FECHA,[["id","fechaIniFld","fechaPagoIniFld"],["id","fechaFinFld","fechaPagoFinFld"]],0/*clog1("Fecha Pago","1")*/)],"ids"=>["fechaPagoIniFld","fechaPagoFinFld"],"type"=>"fecha","width"=>250,"height"=>66],
        "filter03"=>["texto"=>"Fecha Factura","contenido"=>[fixProperty(self::TABLA_FECHA,[["id","fechaIniFld","fechaFacIniFld"],["id","fechaFinFld","fechaFacFinFld"]],0/*clog1("Fecha Factura","1")*/)],"ids"=>["fechaFacIniFld","fechaFacFinFld"],"type"=>"fecha","width"=>250,"height"=>66],
        "filter04"=>["texto"=>"Empresa","contenido"=>[fixProperty(self::TABLA_LISTA,[["eText","Lista:","Alias:"],["id","listFld","empresaFld","multiple","true"],["id","listFld",null,"size","3"],["eChilds",[["eName"=>"OPTION","eText"=>"Todas"]],$optionEmpresas],["width","21.5","45"],["height","21.5","45"]],0/*clog1("Empresa","1")*/)],"ids"=>["empresaFld"],"type"=>"lista","width"=>211,"height"=>74],
        "filter05"=>["texto"=>"Proveedor","contenido"=>[fixProperty(self::TABLA_LISTA,[["eText","Lista:","Código:"],["id","listFld","proveedorFld","eChilds",$optionProveedores],["eText","Todas","Todos"]],0/*clog1("Proveedor","1")*/)],"ids"=>["proveedorFld"],"type"=>"lista","width"=>173,"height"=>37],
        "filter06"=>["texto"=>"Folio Solicitud","contenido"=>[fixProperty(self::TABLA_TEXTO,[["eText","Texto:","Folio:"],["id","textFld","folioSolFld"]],0/*clog1("Folio Solicitud","1")*/)],"ids"=>["folioSolFld"],"type"=>"texto","width"=>261,"height"=>39],
        "filter07"=>["texto"=>"Folio Factura","contenido"=>[fixProperty(self::TABLA_TEXTO,[["eText","Texto:","Folio:"],["id","textFld","folioInvFld"]],0/*clog1("Folio Factura","1")*/)],"ids"=>["folioInvFld"],"type"=>"texto","width"=>261,"height"=>39],
        "filter08"=>["texto"=>"Folio Orden","contenido"=>[fixProperty(self::TABLA_TEXTO,[["eText","Texto:","Folio:"],["id","textFld","folioOrdFld"]],0/*clog1("Folio Orden","1")*/)],"ids"=>["folioOrdFld"],"type"=>"texto","width"=>261,"height"=>39],
        "filter09"=>["texto"=>"Importe","contenido"=>[fixProperty(self::TABLA_RANGO,[["id","iniFld","montoIniFld"],["id","finFld","montoFinFld"],["type","text","number"],["eText","Rango Inicial:","Monto Inicial:"],["eText","Rango Final:","Monto Final:"]],0/*clog1("Importe","1")*/)],"ids"=>["montoIniFld","montoFinFld"],"type"=>"rango","width"=>345,"height"=>66],
        "filter10"=>["texto"=>"Sección","contenido"=>[fixProperty(self::TABLA_LISTA,[["eText","Lista:","Sección:"],["id","listFld","seccionFld","multiple","true"],["eChilds",[["eName"=>"OPTION","eText"=>"Todas"]],$optionSecciones],["width","21.5","45"],["height","21.5","45"]],0/*clog1("Sección","1")*/)],"ids"=>["seccionFld"],"type"=>"lista","width"=>392,"height"=>93],
        "filter11"=>["texto"=>"Autorizador","contenido"=>[["eText"=>"Nombre:"]]],
        "filter12"=>["texto"=>"Solicitante","contenido"=>[["eText"=>"Nombre:"]]],
        "filter13"=>["texto"=>"Documento","contenido"=>[fixProperty(self::TABLA_LISTA,[["eText","Lista:","Tipo:"],["id","listFld","docTypeFld","eChilds",[["eName"=>"OPTION","value"=>"factura","eText"=>"Factura"],["eName"=>"OPTION","value"=>"orden","eText"=>"Orden de Compra"],["eName"=>"OPTION","value"=>"contra","eText"=>"Contra-Recibo"]]]],0)],"ids"=>["docTypeFld"],"type"=>"lista"]];
    }
    var $tableAlias="";
    var $tableSuffix="";
    function __construct() {
        self::$lastFunc=__FUNCTION__;
        $this->tablename      = "solicitudpago";
        $this->tableAlias     = "sp";
        $this->tableSuffix    = "sp.";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "folio", "idFactura", "idOrden", "idContrarrecibo", "idEmpresa", "fechaInicio", "fechaPago","idUsuario","idAutoriza", "status", "proceso", "observaciones", "archivoAntecedentes", "authList", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['fechaInicio'] = array('auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx SolicitudPago xxxxxxxxxxxxxx //\n";
    }
    function getFolio($prefix, &$desc) {
        global $query;
        self::$lastFunc=__FUNCTION__;
        //DBi::freeResult();
        //DBi::nextResult();
        $desc="";
        $query = "CALL NEXTSOLID('$prefix')";
        doclog("CALLNEXT","test",["query"=>$query,"clases"=>"SolicitudPago"]);
        $result = DBi::query($query);
        if (isset($result) && is_object($result)) {
            $row = $result->fetch_assoc();
            $folio=$row["nextId"]??null;
            $result->free();
        }
        //DBi::freeResult();
        //if (isset($result) && is_object($result)) {
        //    $result->close();
        //}
        DBi::nextResult();
        if (isset($folio)) {
            // El máximo actual de solicitudes por empresa y por mes es menor a 200
            // ToDo: Si (folio>=1000 && folio<8000)
            // ToDo:                 extra=12*floor(folio/1000,0)
            // ToDo:                 prefix=substr(prefix,0,-2).(extra+substr(prefix,-2))
            // ToDo:                 folio%=1000
            // ToDo: Si (folio>8000) evaluar opciones aunque serán extremadamente improbables
            $folio=$prefix."-".str_pad($folio, 3, "0", STR_PAD_LEFT);
        } else {
            if ($result===null) {
                $desc="null";
            } else if (is_object($result)) {
                $desc="ROWS:".($result->num_rows??"null");
            } else if (is_bool($result)) {
                $desc=($result?"TRUE":"FALSE");
            } else if (is_scalar($result)) {
                $desc="$result";
            } else $desc="(".gettype($result).")";
            $folio=null;
        }
        return $folio;
    }
    static function getStatusList($status,$descriptive=false) {
        self::$lastFunc=__FUNCTION__;
        if ($status>0) {
            $flags=getBinFlags($status);
            if ($descriptive) {
                foreach ($flags as $idx => $value) {
                    switch($value) {
                        case SolicitudPago::STATUS_CON_FACTURA: $flags[$idx]="CON FACTURA"; break;
                        case SolicitudPago::STATUS_AUTORIZADA: $flags[$idx]="AUTORIZADA"; break;
                        case SolicitudPago::STATUS_ACEPTADA: $flags[$idx]="ACEPTADA"; break;
                        case SolicitudPago::STATUS_CONTRARRECIBO: $flags[$idx]="CONTRARRECIBO"; break;
                        case SolicitudPago::STATUS_EXPORTADA: $flags[$idx]="EXPORTADA"; break;
                        case SolicitudPago::STATUS_RESPALDADA: $flags[$idx]="RESPALDADA"; break;
                        case SolicitudPago::STATUS_PAGADA: $flags[$idx]="PAGADA"; break;
                        case SolicitudPago::STATUS_CANCELADA: $flags[$idx]="CANCELADA"; break;
                        default: $flags[$idx]="NO DEFINIDA";
                    }
                }
            }
            return $flags;
        } else if ($descriptive) return ["SIN FACTURA"];
        else return [0];
    }
    static function sendOldNonAuthMails() {
        self::$lastFunc=__FUNCTION__;
        // toDo: Obtener datos de correos no autorizados de días anteriores
        // toDo: Si sólo es una solicitud enviar el correo usual
        // toDo: Si hay más de una, crear un sólo correo con la lista, tal como aparece en el bloque No Autorizadas
    }
    // 3- Requieren autorizacion
    // AuthPago : (Checkbox cada fila) Botones Aceptar/Rechazar. Ver PDF
    function getNoAutorizadas($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA."=0 and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idContrarrecibo is null",$empresas,$iniDate,$endDate,$dateType);
    }
    function getNoAutorizadasF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA."=0 and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idContrarrecibo is null",$filtros,$empresas);
    }
    function getNumNoAutorizadasF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA."=0 and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idContrarrecibo is null",$filtros,$empresas);
    }
    // 1- Autorizadas con factura, para iniciar proceso de validacion.
    // SolPago : Liga para mostrar en Reporte Facturas 
    function getAutorizadas($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idFactura is not null and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_AUTORIZADA,$empresas,$iniDate,$endDate,$dateType);
    }
    function getAutorizadasF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_AUTORIZADA." and {$this->tableSuffix}idFactura is not null",$filtros,$empresas,false);
    }
    function getNumAutorizadasF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_AUTORIZADA." and {$this->tableSuffix}idFactura is not null",$filtros,$empresas,false);
    }
    // 5- En proceso de validacion sin pagar
    // SolPago : Liga para mostrar en Reporte Facturas 
    function getEnProceso($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idFactura is not null and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_COMPRAS,$empresas,$iniDate,$endDate,$dateType);
    }
    function getEnProcesoF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_COMPRAS." and {$this->tableSuffix}idFactura is not null",$filtros,$empresas,false);
    }
    function getNumEnProcesoF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_COMPRAS." and {$this->tableSuffix}idFactura is not null",$filtros,$empresas,false);
    }
    // 6- Listas para ser pagadas: autorizadas sin factura o procesadas o anexadas
    function getParaPago($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idFactura is null or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_CONTABLE." or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_ANEXADA.")",$empresas,$iniDate,$endDate,$dateType);
    }
    function getParaPagoF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        sessionInit();
        //if (hasUser() && getUser()->nombre==="admin")
        self::$hasSolByCr=(hasUser() && in_array(getUser()->nombre, ["admin","sistemas","arturo","jlobaton","solpagos","sistemas1","sistemas2","sistemas3"]));

        // STATUS<PAGADA Y (PROCESO=CONTABLE O PROCESO=ANEXADA O (IDFACT=NULL Y (IDCTR!=NULL O (IDORD!=NULL Y STATUS&AUTORIZADA))))
        // STATUS<PAGADA Y (PROCESO=CONTABLE O PROCESO=ANEXADA O (IDFACT=NULL Y                 IDORD!=NULL Y STATUS&AUTORIZADA  ))
            return $this->getListaF("{$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}proceso=".SolicitudPago::PROCESO_CONTABLE." or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_ANEXADA." or ({$this->tableSuffix}idFactura is null and ".(self::$hasSolByCr?"({$this->tableSuffix}idContrarrecibo is not null or (":"")."{$this->tableSuffix}idOrden is not null and {$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA.(self::$hasSolByCr?"))":"")."))",$filtros,$empresas);
        //return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}proceso=".SolicitudPago::PROCESO_CONTABLE." or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_ANEXADA." or ({$this->tableSuffix}idFactura is null and {$this->tableSuffix}idOrden is not null))",$filtros,$empresas);
    }
    function getNumParaPagoF($filtros,$empresas) {
        sessionInit();
        self::$hasSolByCr=(hasUser() && in_array(getUser()->nombre, ["admin","sistemas","arturo","jlobaton","solpagos","sistemas1","sistemas2","sistemas3"]));
        return $this->getNumListaF("{$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}proceso=".SolicitudPago::PROCESO_CONTABLE." or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_ANEXADA." or ({$this->tableSuffix}idFactura is null and ".(self::$hasSolByCr?"({$this->tableSuffix}idContrarrecibo is not null or (":"")."{$this->tableSuffix}idOrden is not null and {$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA.(self::$hasSolByCr?"))":"")."))",$filtros,$empresas);
    }
    // 4- Pagadas Sin Factura
    // SolPago : Boton/ModuloNuevo: Agregar Factura
    function getSinFactura($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and {$this->tableSuffix}proceso<=".SolicitudPago::PROCESO_PAGADA." and {$this->tableSuffix}idFactura is null",$empresas,$iniDate,$endDate,$dateType); // " and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_PAGADA
    }
    function getSinFacturaF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        doclog("getSinFacturaF","solpago",["filtros"=>$filtros,"empresas"=>$empresas]);
        return $this->getListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and {$this->tableSuffix}proceso<=".SolicitudPago::PROCESO_PAGADA." and {$this->tableSuffix}idFactura is null and {$this->tableSuffix}idContrarrecibo is null",$filtros,$empresas,false);
    }
    function getNumSinFacturaF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and {$this->tableSuffix}proceso<=".SolicitudPago::PROCESO_PAGADA." and {$this->tableSuffix}idFactura is null and {$this->tableSuffix}idContrarrecibo is null",$filtros,$empresas,false);
    }
    function hasOldSinFacturaF($filtros, $empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and {$this->tableSuffix}idFactura is null",$filtros,$empresas,false,false,true);
    }
    // 7- Pagadas Con Factura
    function getConFactura($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and ({$this->tableSuffix}idFactura is not null or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_NOREQ_FACTURA.")",$empresas,$iniDate,$endDate,$dateType); // " and {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_PAGADA
    }
    function getConFacturaF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and ({$this->tableSuffix}idFactura is not null or {$this->tableSuffix}idContrarrecibo is not null or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_NOREQ_FACTURA.")",$filtros,$empresas,false);
    }
    function getNumConFacturaF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR." and ({$this->tableSuffix}idFactura is not null or {$this->tableSuffix}proceso=".SolicitudPago::PROCESO_NOREQ_FACTURA.")",$filtros,$empresas,false);
    }
    // 2- Rechazadas el dia de hoy
    function getRechazadasHoy($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status>=128 and date({$this->tableSuffix}modifiedTime)=current_date()",$empresas,$iniDate,$endDate,$dateType);
    }
    function getRechazadasHoyF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status>=128 and date({$this->tableSuffix}modifiedTime)=current_date()",$filtros,$empresas,false);
    }
    function getNumRechazadasHoyF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status>=128 and date({$this->tableSuffix}modifiedTime)=current_date()",$filtros,$empresas,false);
    }
    // 8- Rechazadas antes de hoy
    function getRechazadasAntes($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status>=128 and date({$this->tableSuffix}modifiedTime)<current_date()",$empresas,$iniDate,$endDate,$dateType);
    }
    function getRechazadasAntesF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status>=128 and date({$this->tableSuffix}modifiedTime)<current_date()",$filtros,$empresas,false);
    }
    function getNumRechazadasAntesF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status>=128 and date({$this->tableSuffix}modifiedTime)<current_date()",$filtros,$empresas,false);
    }
    // - No estan listas para pago: Autorizadas+En Proceso
    function getProcesando($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idFactura is not null and {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_AUTORIZADA." AND ".SolicitudPago::PROCESO_COMPRAS,$empresas,$iniDate,$endDate,$dateType);
    }
    function getProcesandoF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idFactura is not null and {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_AUTORIZADA." AND ".SolicitudPago::PROCESO_COMPRAS,$filtros,$empresas,false);
    }
    function getNumProcesandoF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}idFactura is not null and {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_AUTORIZADA." AND ".SolicitudPago::PROCESO_COMPRAS,$filtros,$empresas,false);
    }
    // - Procesadas por Compras sin Pago
    function getEnProcPago($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idFactura is null or {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_COMPRAS." AND ".SolicitudPago::PROCESO_ANEXADA.")",$empresas,$iniDate,$endDate,$dateType);
    }
    function getEnProcPagoF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        //return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idFactura is null or {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_COMPRAS." AND ".SolicitudPago::PROCESO_ANEXADA.")",$filtros,$empresas);
        return $this->getListaF("{$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idContrarrecibo is not null or ({$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and ({$this->tableSuffix}idFactura is null or {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_COMPRAS." and ".SolicitudPago::PROCESO_ANEXADA.")))",$filtros,$empresas);
    }
    function getNumEnProcPagoF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idContrarrecibo is not null or ({$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and ({$this->tableSuffix}idFactura is null or {$this->tableSuffix}proceso between ".SolicitudPago::PROCESO_COMPRAS." and ".SolicitudPago::PROCESO_ANEXADA.")))",$filtros,$empresas);
    }
    // 9-Empalme AuthPago = Autorizadas+EnProceso+ParaPago
    function getSinPagar($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}proceso<".SolicitudPago::PROCESO_PAGADA,$empresas,$iniDate,$endDate,$dateType);
    }
    function getSinPagarF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        //return $this->getListaF("{$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and {$this->tableSuffix}proceso<".SolicitudPago::PROCESO_PAGADA,$filtros,$empresas);
        return $this->getListaF("{$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idContrarrecibo is not null or ({$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}proceso<".SolicitudPago::PROCESO_PAGADA."))",$filtros,$empresas);
    }
    function getNumSinPagarF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status<".SolicitudPago::STATUS_PAGADA." and ({$this->tableSuffix}idContrarrecibo is not null or ({$this->tableSuffix}status&".SolicitudPago::STATUS_AUTORIZADA." and {$this->tableSuffix}proceso<".SolicitudPago::PROCESO_PAGADA."))",$filtros,$empresas);
    }
    // 11-Empalme RealizaPago = SinFactura+ConFactura
    function getPagadas($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR,$empresas,$iniDate,$endDate,$dateType);
    }
    function getPagadasF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR,$filtros,$empresas);
    }
    function getNumPagadasF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status between ".SolicitudPago::STATUS_PAGADA." and ".SolicitudPago::STATUS_SINCANCELAR,$filtros,$empresas);
    }
    // 10-Empalme AuthPago = RechazadasHoy+RechazadasAntes
    function getRechazadas($empresas=null,$iniDate=null,$endDate=null,$dateType="solicitud") {
        self::$lastFunc=__FUNCTION__;
        return $this->getLista("{$this->tableSuffix}status>=128",$empresas,$iniDate,$endDate,$dateType);
    }
    function getRechazadasF($filtros,$empresas) {
        self::$lastFunc=__FUNCTION__;
        return $this->getListaF("{$this->tableSuffix}status>=128",$filtros,$empresas,false);
    }
    function getNumRechazadasF($filtros,$empresas) {
        return $this->getNumListaF("{$this->tableSuffix}status>=128",$filtros,$empresas,false);
    }
    function getLista($where,$empresas,$iniDate,$endDate,$dateType) {
        if (isset($where[0])) $where.=" and ";
        $where.="({$this->tableSuffix}idFactura is not null or {$this->tableSuffix}idOrden is not null)";
        $where.=$this->getEmpresasQuery($empresas," and ");
        $where.=$this->getDateRangeQuery($iniDate,$endDate,$dateType," and ");
        return $this->getData($where,0,"{$this->tableSuffix}id, f.codigoProveedor fac_cprv, pf.razonSocial fac_razsoc, f.folio fac_folio, right(f.uuid,10) fac_uuid, f.fechaFactura fac_fecha, f.ubicacion fac_ruta, f.nombreInterno fac_xml, f.nombreInternoPDF fac_pdf, f.tieneSello fac_stp, f.selloImpreso fac_sim, f.total fac_total, o.folio ord_folio, po.codigo ord_cprv, po.razonSocial ord_razsoc, o.fecha ord_fecha, o.rutaArchivo ord_ruta, o.nombreArchivo ord_pdf, o.tieneSello ord_stp, o.selloImpreso ord_sim, o.importe ord_total, o.status ord_status, g.alias alias, g.razonSocial empresa, date({$this->tableSuffix}fechaInicio) inicio, date({$this->tableSuffix}fechaPago) pago, u.nombre usuario, u.persona usuario_nombre, a.nombre autoriza, a.persona autoriza_nombre, {$this->tableSuffix}status, {$this->tableSuffix}proceso, {$this->tableSuffix}modifiedTime", "{$this->tableAlias} inner join grupo g on {$this->tableSuffix}idEmpresa=g.id inner join usuarios u on {$this->tableSuffix}idUsuario=u.id left join usuarios a on {$this->tableSuffix}idAutoriza=a.id left join facturas f on {$this->tableSuffix}idFactura=f.id left join ordenescompra o on {$this->tableSuffix}idOrden=o.id left join proveedores po on o.idProveedor=po.id left join proveedores pf on f.codigoProveedor=pf.codigo");
    }
    function getBetweenCond($ini="", $end="") {
        if (isset($ini[0])) $ini=preg_replace('/[^0-9.]/', '', $ini); else $ini="";
        if (isset($end[0])) $end=preg_replace('/[^0-9.]/', '', $end); else $end="";
        if (isset($ini[0])&&isset($end[0])) return " BETWEEN $ini AND $end";
        if (isset($ini[0])) return "<$ini";
        if (isset($end[0])) return "=-$end";
        return "";
    }
    function getDashValue($name,$value) {
        if (is_array($name)) $retval=[];
        else if (is_string($name)) $retval="";
        if (strpos($value,"-")!==false) {
            $bwFolios=explode("-",$value);
            $i=0;
            if (isset($bwFolios[$i])) {
                if (isset($bwFolios[$i][0]))
                    $iniVal=$bwFolios[$i];
                else {
                    $i++;
                    if (isset($bwFolios[$i][0]))
                        $iniVal="-".$bwFolios[$i];
                    else return $retval;
                }
            } else return $retval;
            $i++;
            if (isset($bwFolios[$i])) {
                if (isset($bwFolios[$i][0]))
                    $endVal=$bwFolios[$i];
                else {
                    $i++;
                    if (isset($bwFolios[$i][0]))
                        $endVal="-".$bwFolios[$i];
                }
            }
            $bwCond=$this->getBetweenCond($iniVal??"",$endVal??"");
            if (isset($bwCond[0])) {
                if (is_array($name)) {
                    foreach ($name as $idx => $single) $retval[$idx]=$single.$bwCond;
                } else $retval=$name.$bwCond;
            }
        } else if (is_array($name)) {
            foreach($name as $idx=>$single) $retval[$idx]=rtrim($this->getWhereCondition($single,$value), " AND ");
        } else $retval=rtrim($this->getWhereCondition($name,$value), " AND ");
        return $retval;
    }
    function getMultipleValues($name, $value) {
        if (is_array($name)) {
            $retval=[];
            //if (canLog(1)) echo "\n<!-- getMultipleValues name='".implode(",", $name)."', value='$value' -->\n";
        } else if (is_string($name)) {
            $retval="";
            //if (canLog(1)) echo "\n<!-- getMultipleValues name='$name', value='$value' -->\n";
        }
        $orFolios=explode(",",$value);
        $log="";
        foreach ($orFolios as $idx => $val) {
            $log.="[$idx = $val]";
            $dashResult=$this->getDashValue($name,$val);
            if (is_array($name)) {
                foreach ($name as $key => $fld) {
                    if (!isset($retval[$key])) $retval[$key]="";
                    else if (isset($retval[$key][0])) $retval[$key].=" or ";
                    $retval[$key].=$dashResult[$key];
                }
            } else {
                if (isset($retval[0])) $retval.=" or ";
                $retval.=$dashResult;
            }
        }
        return $retval;
    }
    function getListaF($where,$filtros,$empresas=null,$reqFolio=true,$reqDates=true,$onlyCount=false) {
        $fFechaSolicitud=$filtros["filter01"]??null;
        if ($reqDates && isset($fFechaSolicitud[0])) {
            $where.=$this->getDateRangeQuery($fFechaSolicitud[0],$fFechaSolicitud[1]??null,"solicitud",isset($where[0])?" and ":"");
        }
        $fFechaReciente=$filtros["filter14"]??null;
        if ($reqDates && isset($fFechaReciente[0])) {
            $where.=$this->getDateRangeQuery($fFechaReciente[0],$fFechaReciente[1]??null,"reciente",isset($where[0])?" and ":"");
        }
        $fFechaPago=$filtros["filter02"]??null;
        if (isset($fFechaPago[0])) {
            $where.=$this->getDateRangeQuery($fFechaPago[0],$fFechaPago[1]??null,"pago",isset($where[0])?" and ":"");
        }
        $fFechaFactura=$filtros["filter03"]??null;
        if (isset($fFechaFactura[0])) {
            $where.=$this->getDateRangeQuery($fFechaFactura[0],$fFechaFactura[1]??null,"factura",isset($where[0])?" and ":"");
        }
        $oldEmpresas=$empresas;
        $fEmpresa=$filtros["filter04"]??null;
        $canLog=(canLog(2));
        if ($canLog) echo "<!-- ".self::$lastFunc." [";
        if (isset($fEmpresa[0])) {
            if ($canLog) echo "Filtro Empresas:".implode(",",$fEmpresa);
            global $gpoObj;
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj=new Grupo();
            }
            $gpoObj->rows_per_page=0;
            $fEmpresaId=[];
            foreach ($fEmpresa as $key => $value) $fEmpresaId[]=$gpoObj->getIdByAlias($value);
            if ($canLog) echo "; Ids:".implode(",",$fEmpresaId);
            if ($empresas===null) {
                $empresas=$fEmpresaId;
                if ($canLog) echo "; Empresas is NULL";
            } else {
                foreach ($empresas as $idx => $value) {
                    if ($canLog) echo "; Validar empresaId $value:";
                    if (!in_array($value, $fEmpresaId)) {
                        if ($canLog) echo " REMOVER";
                        unset($empresas[$idx]);
                    } else if ($canLog) echo " CONSERVAR";
                    
                }
                $empresas=array_values($empresas);
                if ($canLog) echo "; FILTRADAS:".implode(",", $empresas);
            }
        }
        if ($canLog) echo "] -->\n";
        $fProveedor=$filtros["filter05"]??null;
        if (isset($fProveedor[0])) {
            $provOnlyList=[];
            $provIgnoreList=[];
            foreach ($fProveedor as $idx => $codProv) {
                if ($codProv[0]==="!") $provIgnoreList[]=substr($codProv, 1);
                else $provOnlyList[]=$codProv;
            }
            if (isset($provOnlyList[0])) {
                if (isset($where[0])) $where.=" and ";
                if (isset($provOnlyList[1])) $assignValue=" in ('".implode("','", $provOnlyList)."')";
                else $assignValue="='".$provOnlyList[0]."'";
                $where.="(f.codigoProveedor{$assignValue} or po.codigo{$assignValue} or c.codigoProveedor{$assignValue})";
            }
            if (isset($provIgnoreList[0])) {
                if (isset($where[0])) $where.=" and ";
                if (isset($provIgnoreList[1])) $assignValue=" not in ('".implode("','", $provIgnoreList)."')";
                else $assignValue="!='".$provOnlyList[0]."'";
                $where.="(f.codigoProveedor is null or f.codigoProveedor{$assignValue}) and (po.codigo is null or po.codigo{$assignValue}) and (c.codigoProveedor is null or c.codigoProveedor{$assignValue})";
            }
        }
        $fFolioSolicitud=trim($filtros["filter06"][0]??"");
        $hasFolioSol=false;
        if (isset($fFolioSolicitud[0])) {
            $aux=$this->getMultipleValues("{$this->tableSuffix}folio",$fFolioSolicitud);
            if (isset($aux[0]) && isset($where[0])) {
                $hasFolioSol=true;
                $where.=" and ";
            }
            $hasOr=(strpos($aux," or ")!==false);
            $where.=($hasOr?"(":"").$aux.($hasOr?")":"");
        }
        $fFolioFac=$filtros["filter07"][0]??null;
        $hasFolioFac=false;
        if (isset($fFolioFac[0])) {
            $aux=$this->getMultipleValues("f.folio",$fFolioFac);
            if (isset($aux[0]) && isset($where[0])) {
                $hasFolioFac=true;
                $where.=" and ";
            }
            $where.=$aux;
        }
        $fFolioOrd=$filtros["filter08"][0]??null;
        $hasFolioOrd=false;
        if (isset($fFolioOrd[0])) {
            $aux=$this->getMultipleValues("o.folio","\"".$fFolioOrd."\"");
            if (isset($aux[0]) && isset($where[0])) {
                $hasFolioOrd=true;
                $where.=" and ";
            }
            $where.=$aux;
        }
        $fImporte=$filtros["filter09"]??null;
        if (isset($fImporte[0])) {
            if (isset($where[0])) $where.=" and ";
            $values=$this->getMultipleValues(["f.total","o.importe"],$fImporte[0]."-".$fImporte[1]);
            $where.="(".$values[0]." or ".$values[1].")";
        }
        $fDocType=$filtros["filter13"]??null;
        $docType="";
        //sessionInit();
        //$esDesarrollo=hasUser() && getUser()->nombre==="admin";
        sessionInit();
        self::$hasSolByCr=(hasUser() && in_array(getUser()->nombre, ["admin","sistemas","arturo","jlobaton","solpagos","sistemas1","sistemas2","sistemas3"]));
        if (isset($fDocType[0])) {
            switch($fDocType) {
                case "factura": $docType="idFactura"; break;
                case "orden": $docType="idOrden"; break;
                case "contra": if (self::$hasSolByCr) $docType="idContrarrecibo"; break;
            }
            if (isset($docType[0])) {
                if (isset($where[0])) $where.=" and ";
                $where.="{$this->tableSuffix}$docType is not null";
            }
        }
        //sessionInit();
        //$esDesarrollo=hasUser() && getUser()->nombre==="admin";
        if ($reqFolio&&!$hasFolioSol&&!$hasFolioFac&&!$hasFolioOrd&&!isset($docType[0])) {
            if (isset($where[0])) $where.=" and ";
            $extraAdmin=self::$hasSolByCr?" or {$this->tableSuffix}idContrarrecibo is not null":"";
            $where.="({$this->tableSuffix}idFactura is not null or {$this->tableSuffix}idOrden is not null{$extraAdmin})";
        }
        $where.=$this->getEmpresasQuery($empresas," and ");
        $this->clearOrder();
        $this->addOrder("{$this->tableSuffix}id","desc");
        if ($onlyCount) return $this->exists($where, "{$this->tableAlias} ".
            "left join facturas f on {$this->tableSuffix}idFactura=f.id ".
            "left join ordenescompra o on {$this->tableSuffix}idOrden=o.id ".
            //"left join contrarrecibos c on {$this->tableSuffix}idContrarrecibo=c.id ".
            "left join proveedores po on o.idProveedor=po.id");
        return $this->getData(
            $where,
            0,
            "{$this->tableSuffix}id, {$this->tableSuffix}folio sol_folio, ".
            "pf.id fac_iprv, f.codigoProveedor fac_cprv, pf.razonSocial fac_razsoc, f.folio fac_folio, right(f.uuid,10) fac_uuid, ".
            "f.fechaFactura fac_fecha, f.ubicacion fac_ruta, f.nombreInterno fac_xml, f.nombreInternoPDF fac_pdf, f.tieneSello fac_stp, ".
            "f.selloImpreso fac_sim, f.total fac_total, f.moneda fac_mon, ".
            "o.idProveedor ord_iprv, po.codigo ord_cprv, po.razonSocial ord_razsoc, o.folio ord_folio, ".
            "o.fecha ord_fecha, o.rutaArchivo ord_ruta, o.nombreArchivo ord_pdf, o.tieneSello ord_stp, ".
            "o.selloImpreso ord_sim, o.importe ord_total, o.moneda ord_mon, o.status ord_status, ".
            "pc.id ctr_iprv, c.codigoProveedor ctr_cprv, pc.razonSocial ctr_razsoc, c.folio ctr_folio, c.id ctr_id, ".
            "c.fechaRevision ctr_fecha, c.esCopia ctr_copia, c.selloImpreso ctr_sim, c.total ctr_total, ".
            "{$this->tableSuffix}idEmpresa sol_igpo, g.alias alias, g.razonSocial empresa, ".
            "date({$this->tableSuffix}fechaInicio) inicio, date({$this->tableSuffix}fechaPago) pago, ".
            "u.nombre usuario, u.persona usuario_nombre, a.nombre autoriza, a.persona autoriza_nombre, ".
            "{$this->tableSuffix}status, {$this->tableSuffix}proceso, {$this->tableSuffix}archivoAntecedentes, {$this->tableSuffix}authList, ".
            "ta.usos authUso, tr.usos rechUso, {$this->tableSuffix}modifiedTime", 
            "{$this->tableAlias} ".
            "inner join grupo g on {$this->tableSuffix}idEmpresa=g.id ".
            "inner join usuarios u on {$this->tableSuffix}idUsuario=u.id ".
            "left join usuarios a on {$this->tableSuffix}idAutoriza=a.id ".
            "left join facturas f on {$this->tableSuffix}idFactura=f.id ".
            "left join ordenescompra o on {$this->tableSuffix}idOrden=o.id ".
            "left join contrarrecibos c on {$this->tableSuffix}idContrarrecibo=c.id ".
            "left join proveedores po on o.idProveedor=po.id ".
            "left join proveedores pf on f.codigoProveedor=pf.codigo ".
            "left join proveedores pc on c.codigoProveedor=pc.codigo ".
            "left join tokens ta on {$this->tableSuffix}id=ta.refId and ({$this->tableSuffix}idAutoriza=ta.usrId) and ta.modulo=\"autorizaPago\" ".
            "left join tokens tr on {$this->tableSuffix}id=tr.refId and ({$this->tableSuffix}idAutoriza=tr.usrId) and tr.modulo=\"rechazaPago\"");
    }
    function getNumListaF($where,$filtros,$empresas=null,$reqFolio=true) {
        $fFechaSolicitud=$filtros["filter01"]??null;
        if (isset($fFechaSolicitud[0])) {
            $where.=$this->getDateRangeQuery($fFechaSolicitud[0],$fFechaSolicitud[1]??null,"solicitud",isset($where[0])?" and ":"");
        }
        $fFechaReciente=$filtros["filter14"]??null;
        if (isset($fFechaReciente[0])) {
            $where.=$this->getDateRangeQuery($fFechaReciente[0],$fFechaReciente[1]??null,"reciente",isset($where[0])?" and ":"");
        }
        $fFechaPago=$filtros["filter02"]??null;
        if (isset($fFechaPago[0])) {
            $where.=$this->getDateRangeQuery($fFechaPago[0],$fFechaPago[1]??null,"pago",isset($where[0])?" and ":"");
        }
        $fFechaFactura=$filtros["filter03"]??null;
        if (isset($fFechaFactura[0])) {
            $where.=$this->getDateRangeQuery($fFechaFactura[0],$fFechaFactura[1]??null,"factura",isset($where[0])?" and ":"");
        }
        $oldEmpresas=$empresas;
        $fEmpresa=$filtros["filter04"]??null;
        if (isset($fEmpresa[0])) {
            global $gpoObj;
            if (!isset($gpoObj)) {
                require_once "clases/Grupo.php";
                $gpoObj=new Grupo();
            }
            $gpoObj->rows_per_page=0;
            $fEmpresaId=[];
            foreach ($fEmpresa as $key => $value) $fEmpresaId[]=$gpoObj->getIdByAlias($value);
            if ($empresas===null) {
                $empresas=$fEmpresaId;
            } else {
                foreach ($empresas as $idx => $value) {
                    if (!in_array($value, $fEmpresaId)) {
                        unset($empresas[$idx]);
                    }
                }
                $empresas=array_values($empresas);
            }
        }
        $fProveedor=$filtros["filter05"]??null;
        if (isset($fProveedor[0])) {
            $provOnlyList=[];
            $provIgnoreList=[];
            foreach ($fProveedor as $idx => $codProv) {
                if ($codProv[0]==="!") $provIgnoreList[]=substr($codProv, 1);
                else $provOnlyList[]=$codProv;
            }
            if (isset($provOnlyList[0])) {
                if (isset($where[0])) $where.=" and ";
                if (isset($provOnlyList[1])) $assignValue=" in ('".implode("','", $provOnlyList)."')";
                else $assignValue="='".$provOnlyList[0]."'";
                $where.="(f.codigoProveedor{$assignValue} or po.codigo{$assignValue} or c.codigoProveedor{$assignValue})";
            }
            if (isset($provIgnoreList[0])) {
                if (isset($where[0])) $where.=" and ";
                if (isset($provIgnoreList[1])) $assignValue=" not in ('".implode("','", $provIgnoreList)."')";
                else $assignValue="!='".$provOnlyList[0]."'";
                $where.="(f.codigoProveedor is null or f.codigoProveedor{$assignValue}) and (po.codigo is null or po.codigo{$assignValue}) and (c.codigoProveedor is null or c.codigoProveedor{$assignValue})";
            }
        }
        $fFolioSolicitud=trim($filtros["filter06"][0]??"");
        $hasFolioSol=false;
        if (isset($fFolioSolicitud[0])) {
            $aux=$this->getMultipleValues("{$this->tableSuffix}folio",$fFolioSolicitud);
            if (isset($aux[0]) && isset($where[0])) {
                $hasFolioSol=true;
                $where.=" and ";
            }
            $hasOr=(strpos($aux," or ")!==false);
            $where.=($hasOr?"(":"").$aux.($hasOr?")":"");
        }
        $fFolioFac=$filtros["filter07"][0]??null;
        $hasFolioFac=false;
        if (isset($fFolioFac[0])) {
            $aux=$this->getMultipleValues("f.folio",$fFolioFac);
            if (isset($aux[0]) && isset($where[0])) {
                $hasFolioFac=true;
                $where.=" and ";
            }
            $where.=$aux;
        }
        $fFolioOrd=$filtros["filter08"][0]??null;
        $hasFolioOrd=false;
        if (isset($fFolioOrd[0])) {
            $aux=$this->getMultipleValues("o.folio","\"".$fFolioOrd."\"");
            if (isset($aux[0]) && isset($where[0])) {
                $hasFolioOrd=true;
                $where.=" and ";
            }
            $where.=$aux;
        }
        $fImporte=$filtros["filter09"]??null;
        if (isset($fImporte[0])) {
            if (isset($where[0])) $where.=" and ";
            $values=$this->getMultipleValues(["f.total","o.importe"],$fImporte[0]."-".$fImporte[1]);
            $where.="(".$values[0]." or ".$values[1].")";
        }
        $fDocType=$filtros["filter13"]??null;
        $docType="";
        //sessionInit();
        //$esDesarrollo=hasUser() && getUser()->nombre==="admin";
        sessionInit();
        self::$hasSolByCr=(hasUser() && in_array(getUser()->nombre, ["admin","sistemas","arturo","jlobaton","solpagos","sistemas1","sistemas2","sistemas3"]));
        if (isset($fDocType[0])) {
            switch($fDocType) {
                case "factura": $docType="idFactura"; break;
                case "orden": $docType="idOrden"; break;
                case "contra": if (self::$hasSolByCr) $docType="idContrarrecibo"; break;
            }
            if (isset($docType[0])) {
                if (isset($where[0])) $where.=" and ";
                $where.="{$this->tableSuffix}$docType is not null";
            }
        }

        if ($reqFolio&&!$hasFolioSol&&!$hasFolioFac&&!$hasFolioOrd&&!isset($docType[0])) {
            if (isset($where[0])) $where.=" and ";
            $extraAdmin=self::$hasSolByCr?" or {$this->tableSuffix}idContrarrecibo is not null":"";
            $where.="({$this->tableSuffix}idFactura is not null or {$this->tableSuffix}idOrden is not null{$extraAdmin})";
        }
        $where.=$this->getEmpresasQuery($empresas," and ");
        if ($this->exists($where, "{$this->tableAlias} ".
            "left join facturas f on {$this->tableSuffix}idFactura=f.id ".
            "left join ordenescompra o on {$this->tableSuffix}idOrden=o.id ".
            //"left join contrarrecibos c on {$this->tableSuffix}idContrarrecibo=c.id ".
            "left join proveedores po on o.idProveedor=po.id")) return $this->numrows;
        return 0;
    }
    function getDateRangeQuery($iniDate,$endDate,$dateType,$prefix="") {
        $retwhr="";
        $iniDate=$this->fixDate($iniDate,"00:00:00");
        $endDate=$this->fixDate($endDate,"23:59:59");
        if (isset($dateType[0])) {
            $dateName="";
            switch($dateType) {
                case "solicitud":$dateName="{$this->tableSuffix}fechaInicio"; break;
                case "pago":$dateName="{$this->tableSuffix}fechaPago"; break;
                case "factura":$dateName="f.fechaFactura";break;
                case "reciente":$dateName="{$this->tableSuffix}modifiedTime"; break;
            }
            if (isset($dateName[0])) {
                if (isset($iniDate[0])) {
                    $retwhr.=$dateName;
                    if (isset($endDate[0])) $retwhr.=" BETWEEN '$iniDate' AND '$endDate'";
                    else $retwhr.=">='$iniDate'";
                } else if (isset($endDate[0])) $retwhr.="<='$endDate'";
            }
        }
        return (isset($retwhr[0])?$prefix:"").$retwhr;
    }
    function fixDate($dateStr,$timeStr="") { // aaaa-mm-dd o dd/mm/aaaa
        if (isset($dateStr[0])) {
            if (isset($dateStr[10])) {
                if (!isset($timeStr[0])) $timeStr=substr($dateStr,11);
                $dateStr=substr($dateStr,0,10);
            }
            if (isset($dateStr[9])) {
                if ($dateStr[2]==="/" && $dateStr[5]==="/")
                    $dateStr=substr($dateStr,6,4)."-".substr($dateStr,3,2)."-".substr($dateStr,0,2);
                if (isset($timeStr[0])) $timeStr=" ".$timeStr;
                return $dateStr.$timeStr;
            }
        }
        return "";
    }
    function getEmpresasQuery($empresas,$prefix="",$notNegative=true) {
        if (empty($empresas)) {
            sessionInit();
            if (hasUser()) {
                $esDesarrollo=hasUser() && getUser()->nombre==="admin";
                $esPrueba=substr(getUser()->nombre,0,4)==="test";
                if (!$esDesarrollo&&!$esPrueba) {
                    $empresas=17;
                    $notNegative=false;
                }
            } else {
                // $empresas=0; // ToDo: Cuando los tokens integren inicio de sesion
            }
        }
        if (!empty($empresas)) {
            if (is_array($empresas)) {
                return $prefix."{$this->tableSuffix}idEmpresa".($notNegative?"":" not")." in (".implode(",", $empresas).")";
            } else if (is_int($empresas) || ctype_digit($empresas)) {
                $empresas=strval($empresas);
                if (isset($empresas[0])) {
                    if (strpos($empresas, ",")!==false)
                        return $prefix."{$this->tableSuffix}idEmpresa".($notNegative?"":" not")." in ($empresas)";
                    else
                        return $prefix."{$this->tableSuffix}idEmpresa".($notNegative?"":"!")."='$empresas'";
                }
            }
        }
        return "";
    }
    function getStatus($statusNFactura) {
        $newStatusSolPago=0;
        require_once "clases/Facturas.php";
        if ($statusNFactura&Facturas::STATUS_ACEPTADO) $newStatusSolPago|=SolicitudPago::STATUS_ACEPTADA;
        if ($statusNFactura&Facturas::STATUS_CONTRA_RECIBO) $newStatusSolPago|=SolicitudPago::STATUS_CONTRARRECIBO;
        if ($statusNFactura&Facturas::STATUS_EXPORTADO) $newStatusSolPago|=SolicitudPago::STATUS_EXPORTADA;
        if ($statusNFactura&Facturas::STATUS_RESPALDADO) $newStatusSolPago|=SolicitudPago::STATUS_RESPALDADA;
        if ($statusNFactura&Facturas::STATUS_PAGADO) $newStatusSolPago|=SolicitudPago::STATUS_PAGADA;
        if (($statusNFactura&Facturas::STATUS_RECHAZADO)||($statusNFactura&Facturas::STATUS_CANCELADOSAT)) $newStatusSolPago|=SolicitudPago::STATUS_CANCELADA;
        return $newStatusSolPago;
    }
    function updateStatus($idFactura, $newStatusNFactura) {
        if (empty($idFactura)||empty($newStatusNFactura)) return false;
        if (is_array($idFactura)) {
            $idFactura=" in (".implode(",",$idFactura).")";
        } else if (is_scalar($idFactura)) $idFactura="=$idFactura";
        $solData=$this->getData("idFactura$idFactura and idAutoriza is not null and status is not null and status>0",0,"id");//,idAutoriza,status
        if (!isset($solData[0])) return false;
        $solId=array_column($solData,"id");
        $bitOp="|";
        if ($newStatusNFactura<=0) {
            $bitOp="^";
            $newStatusNFactura=-$newStatusNFactura;
        }
        if($newStatusNFactura==0) {
            // se contempla el bitOp negacion ^
            $newStatusSolPago=SolicitudPago::STATUS_ACEPTADA|SolicitudPago::STATUS_CONTRARRECIBO|SolicitudPago::STATUS_EXPORTADA|SolicitudPago::STATUS_RESPALDADA;
        } else $newStatusSolPago=$this->getStatus($newStatusNFactura);
        return $this->saveRecord(["id"=>$solId,"status"=>new DBExpression("status".$bitOp.$newStatusSolPago)]);
    }
    function firma($solId,$accion,$motivo=null) {
        global $firObj;
        if (!isset($firObj)) {
            require_once "clases/Firmas.php";
            $firObj=new Firmas();
        }
        sessionInit();
        if (hasUser()) {
            $userId=getUser()->id;
            if (getUser()->nombre==="admin") {
                global $usrObj;
                if (!isset($usrObj)) {
                    require_once "clases/Usuarios.php";
                    $usrObj=new Usuarios();
                }
                $usrData=$usrObj->getData("nombre='SISTEMAS'",0,"id");
                if (isset($usrData[0]["id"])) $userId=$usrData[0]["id"];
            }
        } else $userId=0;
        $fieldarr=["idUsuario"=>$userId,"modulo"=>"solpago","idReferencia"=>$solId,"accion"=>$accion];
        if(isset($motivo[0])) $fieldarr["motivo"]=$motivo;
        $firObj->saveRecord($fieldarr);
    }
    function hasInvoice($idFactura) {
        if (empty($idFactura)) return false;
        if (is_array($idFactura)) {
            $idFactura=" in (".implode(",",$idFactura).")";
        } else if (is_scalar($idFactura)) $idFactura="=$idFactura";
        $where="idFactura$idFactura";
        return $this->exists($where);
    }
}
