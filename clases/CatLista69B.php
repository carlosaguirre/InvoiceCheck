<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class CatLista69B extends DBObject {
    private static $instance=null;
    function __construct() {
        $this->tablename      = "catlista69b";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "rfc", "nombre", "situacion","numsat_presuntos","fchsat_presuntos","pubsat_presuntos","numdof_presuntos","fchdof_presuntos","pubdof_presuntos","numsat_desvirtuados","fchsat_desvirtuados","pubsat_desvirtuados","numdof_desvirtuados","fchdof_desvirtuados","pubdof_desvirtuados","numsat_definitivos","fchsat_definitivos","pubsat_definitivos","numdof_definitivos","fchdof_definitivos","pubdof_definitivos","numsat_favorable","fchsat_favorable","pubsat_favorable","numdof_favorable","fchdof_favorable","pubdof_favorable","sumaDias","fechaCreacion");
        //$this->fieldlist      = array("id", "rfc", "nombre", "situacion","numofg_presuntos","fchofg_presuntos","pubsat_presuntos","numof2_presuntos","fchof2_presuntos","pubdof_presuntos","pubsat_desvirtuados","numofg_desvirtuados","fchofg_desvirtuados","pubdof_desvirtuados","numofg_definitivos","fchofg_definitivos","pubsat_definitivos","pubdof_definitivos","numofg_sfavorable","fchofg_sfavorable","pubsat_sfavorable","numof2_sfavorable","fchof2_sfavorable","pubdof_sfavorable","sumaDias");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['rfc'] = array('skey' => 'y');
        $this->fieldlist['fechaCreacion'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx CatLista69B xxxxxxxxxxxxxx //\n";
    }
    public static function estaMarcado($rfc) {
        if (!isset(static::$instance)) static::$instance=new CatLista69B();
        $rfc=strtoupper($rfc);
        return static::$instance->exists("upper(rfc)='$rfc' and situacion in ('Presuntos','Definitivos')");
    }
}
