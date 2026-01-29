<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class InfoLocal extends DBObject {
    function __construct() {
        $this->tablename      = "infolocal";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "nombre", "valor", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['nombre'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx InfoLocal xxxxxxxxxxxxxx //\n";
    }
    function getVal($name) {
        $data = $this->getData("nombre='$name'",0,"valor");
        if (isset($data[0]["valor"][0])) return $data[0]["valor"];
        return null;
    }
    function setVal($name, $value) {
        return $this->saveRecord(["nombre"=>$name,"valor"=>$value]);
    }
    function incVal($name,$num=1) {
        return $this->saveRecord(["nombre"=>$name,"valor"=>new DBExpression("valor+$num")]);
    }
    function delVal($name,$datetime=null) {
        $fieldarray=["nombre"=>$name];
        if (!is_null($datetime)) {
            $fieldarray["modifiedTime"]=new DBExpression("'$datetime'","<");
        }
        return $this->deleteRecord($fieldarray);
    }
    function getMsgIni() {
        $this->clearOrder();
        $this->addOrder("idx");
        $data = $this->getData("nombre like 'MSG_INI_PG%'",0,"right(nombre,2)+0 idx, valor");
        $result=[];
        foreach ($data as $index => $value) {
            $result[]=$value;
        }
        return $result;
    }
    function setMsgIni($paragraphList) {
        $this->deleteRecord(["nombre"=>"MSG_INI_PG%"]);
        $valuesArray = [];
        foreach ($paragraphList as $key => $value) {
            $num="00".($key+1);
            $value=htmlentities(html_entity_decode($value));
            $valuesArray[]=["MSG_INI_PG".substr($num,-2),$value];
        }
        $this->insertMultipleRecords(["nombre","valor"], $valuesArray);
    }
    function definir($nombre,$valor) {
        if (!$this->available()) return false;
//            $tracelog = $this->fetch("tracelog", "");
//            $tracelog .= "definir($nombre,$valor)";

        $oldId = $this->getValue("nombre",$nombre,"id");
        $fieldarray = ["nombre"=>$nombre, "valor"=>$valor];
        if (!empty($oldId)) $fieldarray["id"]=$oldId;
        $result = $this->saveRecord($fieldarray);

        if ($result) {
            if (!empty($valor)) {
                $mapa = $this->fetch("mapa", []);
                $mapa[$nombre] = $valor;
                $this->store("mapa",$mapa,300);
            }
//                $tracelog.="=TRUE";
        }
//            else $tracelog.="=FALSE";
//            $tracelog.="\n";
//            $this->store("tracelog",$tracelog,300);

        return $result;
    }
    function quitar($nombre) {
        if (!$this->available()) return false;
//            $tracelog = $this->fetch("tracelog", "");
//            $tracelog .= "quitar($nombre)";
        $valor = false;
        $oldData = $this->getData("nombre='$nombre'");
        if (!empty($oldData)) {
////                $oldData = $oldData[0];
//                $tracelog .= "=>BD";
            $result = $this->deleteRecord(["id"=>$oldData["id"]]);
            if ($result) {
                $valor = $oldData["valor"];

                $basura = $this->fetch("basura", []);
                $basura[$nombre] = $valor;
                $this->store("basura",$basura,600);

                $mapa = $this->fetch("mapa", []);
                if(isset($mapa[$nombre])) unset($mapa[$nombre]);
                $this->store("mapa",$mapa,300);
//                    $tracelog.="=TRUE($valor)\n";
            }
//                else $tracelog.="=FALSE\n";
        }
//            else $tracelog.="=FALSE\n";
//            $this->store("tracelog",$tracelog,300);
        return $valor;
    }
    function obtener($nombre) {
        if (!$this->available()) return false;
//            $tracelog = $this->fetch("tracelog", "");
//            $tracelog .= "obtener($nombre)";

        $mapa = $this->fetch("mapa", []);
        if (isset($mapa[$nombre])) $valor = $mapa[$nombre];

        if (!isset($valor)) {
            $valor = $this->getValue("nombre",$nombre,"valor");
//                $tracelog .= "=>BD";
            if (!empty($valor)) {
//                    $tracelog .= "=TRUE($valor)\n";
                $mapa = $this->fetch("mapa", []);
                $mapa[$nombre] = $valor;
                $this->store("mapa",$mapa,300);
            }
//                else $tracelog.="=FALSE\n";
        }
//            else $tracelog .= "=TRUE($valor)\n";
//            $this->store("tracelog",$tracelog,300);
        return $valor;
    }
    function recuperar($nombre) {
        if (!$this->available()) return false;
//            $tracelog = $this->fetch("tracelog", "");
//            $tracelog .= "recuperar($nombre)";

        $basura = $this->fetch("basura", []);
        if (!isset($basura[$nombre])) {
//                $tracelog .= "=FALSE\n";
//                $this->store("tracelog",$tracelog,300);
            return false;
        }
        $valor = $basura[$nombre];
        unset($basura[$nombre]);
        $this->store("basura",$basura,600);

//            $tracelog .= " >> ";
//            $this->store("tracelog",$tracelog,300);

        if (definir($nombre,$valor)) return $valor;
        return false;
    }
    function fetch($storedName, $defaultValue=false) {
        if (!$this->available()) return false;
        $storedName="Invoice:InfoLocal:".$storedName;
        $storedVar = apcu_fetch($storedName);
        if ($storedVar === false) return $defaultValue;
        if (is_object($storedVar) && get_class($storedVar)=="ArrayObject") return $storedVar->getArrayCopy();
        return $storedVar;
    }
    function store($storedName, $storedValue, $ttl=0) {
        if (!$this->available()) return false;
        $storedName="Invoice:InfoLocal:".$storedName;
        if (is_array($storedValue)) apcu_store($storedName, new ArrayObject($storedValue), $ttl);
        else apcu_store($storedName, $storedValue, $ttl);
    }
    function available() {
        if (function_exists("apcu_fetch") && function_exists("apcu_store")) return true;
        return false;
    }
}
