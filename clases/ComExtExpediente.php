<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ComExtExpediente extends DBObject {
    const CAMPO_ID = 7111001; // "id", 
    const CAMPO_FECHA_ALTA = 7111002; // "fechaAlta", 
    const CAMPO_TIPO_OPERACION = 7111003; // "tipoOperacion", 
    const CAMPO_GRUPO_ID = 7111004; // "grupoId",
    const CAMPO_FOLIO = 7111005; // "folio",
    const CAMPO_PROVEEDOR_ID = 7111006; // "proveedorId",
    const CAMPO_ORDEN = 7111007; // "orden",
    const CAMPO_AGENTE_ID = 7111008; // "agenteId",
    const CAMPO_IMPORTE = 7111009; // "importe",
    const CAMPO_MONEDA = 7111010; // "moneda",
    const CAMPO_PEDIMENTO = 7111011; // "pedimento",
    const CAMPO_DESCRIPCION = 7111012; // "descripcion",
    const CAMPO_STATUS = 7111013; // "status",
    const CAMPO_MODIFIED_TIME = 7111014; // "modifiedTime"
    const TIPO_OPERACION_VACIO = 0;
    const TIPO_OPERACION_IMPORTACION = 1;
    const TIPO_OPERACION_IMPORTACION_ACTIVOS = 2;
    const TIPO_OPERACION_EXPORTACION = 3;
    const TIPOS_OPERACION = ["Elige...","Importación","Importación de Activos","Exportación"];
    const STATUS_PROCESO = 0;
    const STATUS_ANTICIPO = 1;
    const STATUS_IMPORTADA = 2;
    const STATUS_EXPORTADA = 4;
    const STATUS_PAGADA = 8;
    const STATUS_CERRADA = 16;
    const STATUS_AUDITADA = 32;
    const STATUS_NOUSADO = 64;
    const STATUS_CANCELADA = 128;
    const STATUSES = [0=>"En Proceso", 1=>"Con Anticipo", 2=>"Importada", 4=>"Exportada", 8=>"Pagada", 16=>"Cerrada", 32=>"Auditada", 64=>"No usado", 128=>"Cancelada"];
    function __construct() {
        $this->tablename      = "comext_expediente";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "fechaAlta", "tipoOperacion", "grupoId", "folio", "proveedorId", "orden", "agenteId", "importe", "moneda", "pedimento", "descripcion", "status", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx ComExtExpediente xxxxxxxxxxxxxx //\n";
    }
    function browse($params) {
        $where="";//$fieldArray=[];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if ($key==="registro") {
                    //if (!isset($value[])) ...
                    $cut=substr($value, 0, 3);
                } else $where.=$this->getWhereCondition($key,json_decode($value));
            }
        }
        if (isset($where[0])) $where=rtrim($where," AND ");
        return $this->getData($where);
    }
}
