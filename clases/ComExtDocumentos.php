<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ComExtDocumentos extends DBObject {
    const CAMPO_ID = 9300001;
    const CAMPO_ID_EXPEDIENTE = 9300002;
    const CAMPO_ID_CATALOGO = 9300003;
    const CAMPO_TITULO = 9300004;
    const CAMPO_REFERENCIA = 9300005;
    const CAMPO_IMPORTE = 9300006;
    const CAMPO_MONEDA = 9300007;
    const CAMPO_DESCRIPCION = 9300008;
    const CAMPO_ES_AUDITADO = 9300009;
    const CAMPO_MODIFIED_TIME = 9300010;
    const CAMPO_NOMBREORIGINAL = 9300011;
    function __construct() {
        $this->tablename      = "comext_documentos";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "idExpediente", "idCatalogo", "titulo", "nombreOriginal", "referencia", "importe", "moneda", "descripcion", "esauditado", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx ComExtDocumentos xxxxxxxxxxxxxx //\n";
    }
}
