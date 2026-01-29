<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Cuentas extends DBObject {
    function __construct() {
        $this->tablename      = "cuentas";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "nombre", "tipo", "cuenta", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['nombre'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Acciones xxxxxxxxxxxxxx //\n";
    }
}
/*
id - nombre            - descripcion
-- - ----- - ----------------- - ------------------------------------------------------------------------------------------------
 1 - Alta              - Consultar y Realizar Alta de Facturas
 2 - Procesar          - Consulta de facturas y procesar facturas (aceptar)
 3 - Contrarrecibo     - Consultar y generar contrarrecibos de facturas aceptadas
 4 - Exportar          - Consultar y realizar exportación de formato txt
 5 - Respaldar         - Consultar y respaldar archivo compacto de xmls
 6 - Grupo             - Consulta y Alta/Baja de empresas del grupo
 7 - Proveedor         - Consulta y Alta/Baja de proveedores
 8 - Usuarios          - Vista, creacion y modificacion de usuarios y asignacion de perfiles
 9 - Permisos          - Vista, creacion y modificacion de acciones, permisos y perfiles
10 - Reportes          - Consulta de reporte de facturas
*/
