<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Trace extends DBObject {
    private $lastResult;
    private $lastError;
    function __construct() {
        $this->tablename      = "trace";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "idUsuario", "idTrace", "archivo", "clase", "metodo", "linea", "tipo", "argumentos", "texto", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Trace xxxxxxxxxxxxxx //\n";
    }
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        if ( 0 == error_reporting()) return; // Error reporting is currently turned off or suppressed by @
        if (substr($errfile,-9)==="Trace.php") return;
        if (isset($_SESSION['user'])) $userId = $_SESSION['user']->id;
        $lastId = $this->addTraceData($userId, 0, $errfile, null, null, $errline, null, json_encode($errcontext), $errno." : ".$errstr);
        $trace = debug_backtrace();
        foreach ($trace as $traceId => $entry) $this->addTraceEntry($entry, $traceId,"Backtrace from $lastId");
    }
    private function addTraceEntry($entry, $traceId=0, $text=null) {
        $userId = isset($_SESSION['user'])?$_SESSION['user']->id:null;
        $fileName = isset($entry["file"])?$entry["file"]:null;
        $className = isset($entry["class"])?$entry["class"]:(isset($entry["object"])?"OBJ(".get_class($entry["object"]).")":null);
        $methodName = isset($entry["function"])?$entry["function"]:null;
        $lineNum = isset($entry["line"])?$entry["line"]:null;
        $typeTxt = isset($entry["type"])?$entry["type"]:null;
        if (isset($entry["args"])) {
            foreach($entry["args"] as &$arg) {
                $arg = json_encode($arg); //$this->varToString($arg);
            }
            $arguments=implode(",",$entry["args"]);
            if (strlen($arguments)>300) $arguments=substr($arguments,0,297)."...";
        } else $arguments=null;
        return $this->addTraceData($userId, $traceId, $fileName, $className, $methodName, $lineNum, $typeTxt, $arguments,$text);
    }
    private function addTraceData($idUsuario, $idTrace, $archivo, $clase, $metodo, $linea, $tipo, $argumentos, $texto) {
        $fieldarray=array();
        if(isset($idUsuario)) $fieldarray["idUsuario"]=$idUsuario;
        if(isset($idTrace)) $fieldarray["idTrace"]=$idTrace;
        if(isset($archivo)) $fieldarray["archivo"]=$archivo;
        if(isset($clase)) $fieldarray["clase"]=$clase;
        if(isset($metodo)) $fieldarray["metodo"]=$metodo;
        if(isset($linea)) $fieldarray["linea"]=$linea;
        if(isset($tipo)) $fieldarray["tipo"]=$tipo;
        if(isset($argumentos)) $fieldarray["argumentos"]=$argumentos;
        if(isset($texto)) $fieldarray["texto"]=$texto;
        return $this->addTraceArray($fieldarray);
    }
    private function addTraceArray($fieldarray) {
        $localResult = $this->saveRecord($fieldarray);
        if (isset($localResult)) $this->lastResult = $localResult;
        $errno=DBi::getErrno();
        $error=DBi::getError();
        if (isset($errno) && isset($error)) {
            $this->lastError = $errno." : ".$error;
            return false;
        }
        if (is_bool($localResult)) {
            if (!empty($this->lastId)) return $this->lastId;
            return $localResult;
        }
        return false;
    }
    public function agrega($text, $exception=null) {
        if ( 0 == error_reporting()) return; // Error reporting is currently turned off or suppressed by @
        $emptyText=empty($text);
        $hasException=isset($exception);
        if ($emptyText&&$hasException) $text=$exception->getMessage();
        $idx=strpos(strtolower($text),"insert into trace"); // encuentra si el texto se genera por un query a la tabla trace
        if ($idx!==false) return; // ignora trace de acceso a la base por error al insertar datos a la tabla trace

        if (isset($_SESSION['user'])) $userId = $_SESSION['user']->id;
        if (isset($exception)) $trace = $exception->getTrace();
        else $trace = debug_backtrace();
        $count = count($trace);
        $first=true;
        foreach ($trace as $traceId => $entry) {
            if ($count>2 && $traceId<2) continue; // Quitar primeros 2 llamada a Trace.agrega y a DBi.query
            $fieldarray = ["idTrace"=>$traceId];
            if (isset($entry["file"])) {
                $fieldarray["archivo"]=$entry["file"];
                if ($count>1) {
                    if (strpos($fieldarray["archivo"],"Trace.php")   !==false) continue;
                    if (strpos($fieldarray["archivo"],"DBi.php")     !==false) continue;
                    if (strpos($fieldarray["archivo"],"DBObject.php")!==false) continue;
                }
                $idx = strpos($fieldarray["archivo"],"\\invoice\\");
                if ($idx!==false) $fieldarray["archivo"]=substr($fieldarray["archivo"],$idx+1);
                if (strlen($fieldarray["archivo"]>120)) $fieldarray["archivo"]="...".substr($fieldarray["archivo"],-117);
            }
            if (isset($userId)) $fieldarray["idUsuario"]=$userId;
            if ($first) {
                $first=false;
                if (!empty($text)) {
                    if (strlen($text)>500) $text = substr($text,0,497)."...";
                    $fieldarray["texto"]=$text;
                }
            } else {
                if (isset($entry["object"]) && get_class($entry["object"])=="Proceso") {
                } else
                    break; // Solo guardar un registro
            }
            if (isset($entry["function"])) $fieldarray["metodo"]=$entry["function"];
            if (isset($entry["line"])) {
                $fieldarray["linea"]="$entry[line]";
                if (strlen($fieldarray["linea"])>10) $fieldarray["linea"]="_".substr($fieldarray["linea"],-9);
            }
            if (isset($entry["object"])) $fieldarray["clase"]="OBJ(".get_class($entry["object"]).")";
            else if (isset($entry["class"])) $fieldarray["clase"]=$entry["class"];
            if (isset($entry["type"])) {
                $fieldarray["tipo"]=$entry["type"];
                if (strlen($fieldarray["tipo"])>3) $fieldarray["tipo"]=substr($fieldarray["tipo"],0,3);
            }
            if (isset($entry["args"])) {
                foreach($entry["args"] as &$arg) {
                    $arg = $this->varToString($arg);
                }
                $fieldarray["argumentos"]=implode(",",$entry["args"]);
                if (strlen($fieldarray["argumentos"])>300) $fieldarray["argumentos"]=substr($fieldarray["argumentos"],0,297)."...";
            }
            $localResult = $this->saveRecord($fieldarray);
            if ($localResult!==NULL) {
                $this->lastResult = $localResult;
                $this->lastError = DBi::getErrno() . " : " . DBi::getError();
            }
        }
        return $this->lastResult;
    }
    public function varToString($val) {
        if (is_object($val)) {
            if (method_exists($val, '__toString')) return strval($val);
            return "OBJ(".get_class($val).")";
        }
        if (is_bool($val)) return ($val?"TRUE":"FALSE");
        if (is_array($val)) return $this->arrayToString($val);
        if (is_scalar($val)) return strval($val);
        return print_r($val,true);
    }
    public function arrayToString($arr) {
        if (!is_array($arr)) return false;
        if (empty($arr)) return "[]";
        $len = count($arr);
        if ($len==1 && isset($arr[0])) return $arr[0];
        if (array_keys($arr)===range(0,$len-1)) return "[".implode(",",$arr)."]";
        return "[".implode(",",$this->arrMapAssoc(function($k,$v){return $this->mergeKeyValue($k,$v);},$arr))."]";
    }
    public function arrMapAssoc($callback,$array) {
        $r=array();
        foreach($array as $key=>$value)
            $r[$key]=$callback($key,$value);
        return $r;
    }
    public function mergeKeyValue($key, $val) {
        return "$key=>".$this->varToString($val);
    }
}
