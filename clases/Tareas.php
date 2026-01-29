<?php
require_once dirname(__DIR__)."/bootstrap.php";
class Tareas {
    private static $instance=null;
    private function __construct() {}
    public static function getInstance() {
        if (self::$instance==null) self::$instance=new Tareas();
        return self::$instance;
    }
    public function revisa() {
        echo "Prueba Local\n";
        $local = glob("*.php");
        foreach ($local as $key => $value) {
            echo "Key $key = $value\n";
        }
        echo "Lista de Tareas:\n";
        $tareas = glob("tareas/*.php");
        foreach ($tareas as $tIdx => $tVal) {
            echo "$tIdx : $tVal\n";
            require_once $tVal;
            $clNm=basename($tVal,".php");
            $obj=new $clNm();
            if ($obj->prueba()) $obj->ejecuta();
        }
    }
    static public function test() {
        $t=Tareas::getInstance();
        $t->revisa();
    }
}
interface Tarea {
    public function prueba();
    public function ejecuta();
}
//Tareas::test();
