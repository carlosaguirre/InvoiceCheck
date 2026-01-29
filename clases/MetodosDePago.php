<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class MetodosDePago extends DBObject {
    function __construct() {
        $this->tablename      = "metodosDePago";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "clave", "descripcion", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['clave'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx MetodosDePago xxxxxxxxxxxxxx //\n";
    }
    function getId($clave, $forceLoad=false) {
        static $cache = [];
        if (!empty($cache) && isset($cache[$clave]) && $cache[$clave]!==FALSE && !$forceLoad) {
            return $cache[$clave];
        }
        $retVal = $this->getValue("clave", $clave, "id");
        if (isset($retVal) && $retVal!==FALSE) {
            $cache[$clave] = $retVal;
            return $retVal;
        }
        return NULL;
    }
    function esValido($texto) {
        $texto = trim($texto);
        $len = strlen($texto);
        if ($len>2) {
            $iniCode = substr($texto, 0, 2);
            $endCode = substr($texto, -2);
            return $this->exists("clave in ('$texto','$iniCode','$endCode')");
        } else if ($len<2) return false;
        return $this->exists("clave='$texto'");
    }
}
/*
id - clave - descripcion
-- - ----- - --------------------------------------------------------------------
1 - 01    - Efectivo
2 - 02    - Cheque nominativo
3 - 03    - Transferencia electrónica de fondo
4 - 04    - Tarjeta de Crédito
--- 5 - 05    - Monedero Electrónico   --- Se eliminan metodos diferentes a 01, 02 y 03
--- 6 - 06    - Dinero electrónico     --- Se eliminan metodos diferentes a 01, 02 y 03
--- 7 - 08    - Vales de despensa      --- Se eliminan metodos diferentes a 01, 02 y 03
--- 8 - 28    - Tarjeta de Débito      --- Se eliminan metodos diferentes a 01, 02 y 03
9 - 29    - Tarjeta de Servicio
--- 10 - 99   - Otros                  --- Se elimina metodo de pago 99. Deben poner unicamente N/A o NO APLICA o NO IDENTIFICADO
*/
