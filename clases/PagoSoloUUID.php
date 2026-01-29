<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class PagoSoloUUID extends DBObject {
    private static $instance=null;
    private static $codigos=[];
    function __construct() {
        $this->tablename      = "pagosolouuid";
        $this->rows_per_page  = 100;
        $this->fieldlist      = array("id", "codigoProveedor", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx PagoSoloUUID xxxxxxxxxxxxxx //\n";
    }
    public static function getInstance() {
        if (!isset(static::$instance))
            static::$instance=new PagoSoloUUID();
        return static::$instance;
    }
    public static function clearLog() {
        $this->log = "\n// xxxxxxxxxxxxxx PagoSoloUUID xxxxxxxxxxxxxx //\n";
    }
    public static function load() {
        static::$codigos=array_column(static::getInstance()->getData(false,0,"codigoProveedor code"),"code");
    }
    public static function getCodigos($force=false) {
        if (empty(static::$codigos)||$force) static::load();
        return static::$codigos;
    }
    public static function clearCodigos() {
        static::$codigos=[];
    }
    public static function tiene($code,$force=false) {
        return in_array($code, static::getCodigos($force));
    }
}
if ("pagosolouuid"===strtolower($_GET["test"]??""))
    echo implode(",", PagoSoloUUID::getCodigos(true));
