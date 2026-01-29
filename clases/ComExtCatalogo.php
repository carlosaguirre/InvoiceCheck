<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class ComExtCatalogo extends DBObject {
    const CAMPO_ID = 9200001;
    const CAMPO_TITULO = 9200002;
    const CAMPO_ES_EDITABLE = 9200003;
    const CAMPO_ES_OBLIGATORIO = 9200004;
    const CAMPO_ES_AUDITABLE = 9200005;
    const CAMPO_REQ_IMPORTE = 9200006;
    const CAMPO_DESCRIPCION = 9200007;
    const CAMPO_TIPO_DOCUMENTO = 9200008;
    const CAMPO_PREFIJO_ARCHIVO = 9200009;
    const CAMPO_MODIFIED_TIME = 9200010;
    const ID_ORDEN = 1; // debe existir primer registro: id=1, titulo=Orden de compra, eseditable=3, esobligatorio=0, tipodocumento=PDF, prefijoArchivo=orden
    const ID_CFDIXML = 2; // debe existir segundo registro: id=2, titulo=Factura XML, eseditable=1, esobligatorio=0, tipodocumento=XML, prefijoArchivo=cfdio // o de operacion
    const ID_CFDIPDF = 3; // debe existir tercer registro: id=3, titulo=Factura PDF, eseditable=1, esobligatorio=0, tipodocumento=PDF, prefijoArchivo=cfdio // o de operacion
    function __construct() {
        $this->tablename      = "comext_catalogo";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "titulo", "eseditable", "esobligatorio", "esauditable", "reqImporte", "descripcion", "tipodocumento", "prefijoArchivo", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx ComExtCatalogo xxxxxxxxxxxxxx //\n";
        // eseditable:
        //             0  = No es editable, una vez ingresado no se puede cambiar.
        //             1  = Se puede reemplazar el documento.
        //             2  = Se puede cambiar descripcion y reemplazar el documento.
        //             3  = Se puede cambiar titulo, descripcion e importe y reemplazar el documento.
        // esobligatorio:
        //             0  = No es obligatorio.
        //             1+ = Se requiere el número de documentos indicado, maximo 99.
        //            -1- = No es obligatorio pero Abs es máximo número de documentos, máximo 99.
        // esauditable:
        //             0  = No se enlista al auditar.
        //             1  = Se muestra en la lista para auditar.
        // reqImporte:
        //          Null  = Se pide importe pero no se requiere.
        //             0  = No se pide importe.
        //             1  = Se pide importe y es obligatorio indicar el importe.
        // tipodocumento:
        //          xmlop = xml de operación
        //          pdfop = pdf de operación
        //         pdford = pdf de orden
        // prefijoArchivo:
        //           oper = operación
        //          orden = orden
    }
}
