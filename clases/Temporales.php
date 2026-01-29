<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Temporales extends DBObject {
    private $lastName;
    function __construct() {
        $this->tablename      = "temporales";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "nombre","status", "ultimaedicion");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['nombre'] = array('skey' => 'y');
        $this->fieldlist['ultimaedicion'] = array('auto' => 'y');
        $this->addOrder("id","desc");
        $this->log = "\n// xxxxxxxxxxxxxx Temporales xxxxxxxxxxxxxx //\n";
    }
    function existeNombre($nombre) {
        return $this->exists("nombre='$nombre' and status=0");
    }
    function obtenerId($nombre) {
        $this->lastName=null;
        if (substr($nombre, -1)==="%") {
            $this->addOrder("id","desc");
            $tmpData = $this->getData("nombre like '$nombre' and status=0",0,"id,nombre");
        } else $tmpData = $this->getData("nombre='$nombre' and status=0",0,"id");
        if (isset($tmpData[0]["id"])) {
            if (isset($tmpData[0]["nombre"])) $this->lastName=$tmpData[0]["nombre"];
            return $tmpData[0]["id"];
        }
        return null;
    }
    function obtenerNombre($id) {
        $tmpData = $this->getData("id=$id and status=0",0,"nombre");
        if (isset($tmpData[0]["nombre"]))
            return $tmpData[0]["nombre"];
        return null;
    }
    function ingresar($nombre) {
        if (isset($nombre[100])) {
            $ptIdx=strrpos($nombre, ".");
            if ($ptIdx<0) {
                $ext="";
                $subnombre=substr($nombre, 0, 90)."_";
                $nombre=$subnombre.date("ymdB");
            } else {
                $nomlen=strlen($nombre);
                $sobrante=90-$nomlen;
                $ext=substr($nombre, $ptIdx);
                $extlen=$nomlen-$ptIdx; // strlen($ext);
                if ($extlen>5) {
                    $dif=5-$extlen;
                    $sobrante-=$dif;
                    $ext=substr($ext, 0, $dif);
                    $extlen=5;
                }
                $subnombre=substr($nombre, 0, 90-$extlen)."_";
                $nombre=$subnombre.date("ymdB").$ext;
            }
            $id = $this->obtenerId($subnombre."%");
            if (isset($this->lastName[0])) $nombre=$this->lastName;
        } else {
            $id = $this->obtenerId($nombre);
        }
        if (!isset($id) && $this->insertRecord(["nombre"=>$nombre,"status"=>0]))
            return $this->lastId;
        return $id;
    }
    function procesar($id) {
        $nombre=$this->obtenerNombre($id);
        if (isset($nombre)) {
            $this->updateRecord(["id"=>$id,"status"=>1]);
            return $nombre;
        }
        return false;
    }
    function remover($id) {
        return $this->updateRecord(["id"=>$id,"status"=>-1]);
    }
    function eliminar($id) {
        return $this->deleteRecord(["id"=>$id]);
    }
    function listaIngresados() {
        $datos=$this->getData("status=0",0,"nombre");
        $nombres=[];
        foreach ($datos as $registro) {
            $nombres[]=$registro["nombre"];
        }
        return $nombres;
    }
    function listaProcesados() {
        $datos=$this->getData("status=1",0,"nombre");
        $nombres=[];
        foreach ($datos as $registro) {
            $nombres[]=$registro["nombre"];
        }
        return $nombres;
    }
    function listaRemovidos() {
        $datos=$this->getData("status=-1",0,"nombre");
        $nombres=[];
        foreach ($datos as $registro) {
            $nombres[]=$registro["nombre"];
        }
        return $nombres;
    }
    function eliminarRemovidos() {
        return $this->deleteRecord(["status"=>-1]);
    }
}
