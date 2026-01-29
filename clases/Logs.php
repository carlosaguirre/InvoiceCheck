<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Logs extends DBObject {
//        private static $instance;
    private $lastResult;
    private $lastError;
//      protected 
    function __construct() {
        $this->tablename      = "logs";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "fecha", "idUsuario", "seccion", "texto", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Logs xxxxxxxxxxxxxx //\n";
    }
//        private function __clone() {
//        }
//        private function __wakeup() {
//        }
/*
    public static function agregar($userid, $section, $text) {
        return static::getInstance()->agrega($userid, $section, $text);
    }
*/
    public function agrega($userid, $section, $text) {
        $dt = new DateTime();
        $fmt = $dt->format("Y-m-d H:i:s");
        if (isset($text[1500])) $text = substr($text, 0, 1497)."...";
        $fieldarray = ["idUsuario"=>$userid, "seccion"=>$section, "fecha"=>$fmt, "texto"=>$text];
        $result = $this->saveRecord($fieldarray);
        if (!$result)
            $this->lastError = DBi::getErrno() . " : " . DBi::getError();
        $this->lastResult = $result;
        return $result;
    }
    public function cuantosHora($userid, $section, $fechaHora=null) {
        if (!isset($fechaHora)) {
            $dt = new DateTime();
            $fechaHora = $dt->format("Y-m-d H");
        }
        $result = $this->getData("idUsuario=$userid and seccion='$section' and date_format(fecha,'%Y-%m-%d %H')='$fechaHora'",0,"count(1) n");
        global $lastResult;
        $lastResult=$result;
        if (!isset($result[0]["n"])) return false;
        return $result[0]["n"];
    }
    public function getLastResult() {
        return $this->lastResult;
    }
    public function getLastError() {
        return $this->lastError;
    }
    function trace_test($str) {
        return static::fulltrace2html() . "<br>" . static::get_caller_info() . "\n";
    }
    static function fulltrace2html() {
        $trace = debug_backtrace();
        $html = "<table border='1'><thead><tr><th>Id</th><th>Function</th><th>Line</th><th>File</th><th>Class</th><th>Object</th><th>Type</th><th>Args</th></thead><tbody>";
        foreach ($trace as $traceId => $entry) {
            $html .= "<tr>";
            $html .= "<td>$traceId</td>";
            $cellvalue = $entry['function'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else $html .= "<td>".$cellvalue."</td>";
            $cellvalue = $entry['line'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else $html .= "<td>".$cellvalue."</td>";
            $cellvalue = $entry['file'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else $html .= "<td>".$cellvalue."</td>";
            $cellvalue = $entry['class'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else $html .= "<td>".$cellvalue."</td>";
            $cellvalue = $entry['object'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else {
                $html .= "<td>";
                if (is_object($cellvalue)) $html .= "class(".get_class($cellvalue).")";
                else $html .= $cellvalue;
                $html .= "</td>";
            }
            $cellvalue = $entry['type'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else $html .= "<td>".$cellvalue."</td>";
            $cellvalue = $entry['args'];
            if (empty($cellvalue)) $html .= "<td>&nbsp;</td>"; else {
                $html .= "<td>";
                if (is_array($cellvalue)) {
                    foreach($cellvalue as $arg) {
                        if (is_object($arg)) $html .= "class(".get_class($arg).")<br>";
                        else $html .= $arg."<br>";
                    }
                } else $html .= $cellvalue;
                $html .= "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        return $html;
    }
}
