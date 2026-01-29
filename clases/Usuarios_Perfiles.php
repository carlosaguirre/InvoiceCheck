<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Usuarios_Perfiles extends DBObject {
    function __construct() {
        $this->tablename      = "usuarios_perfiles";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "idUsuario", "idPerfil");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['idUsuario'] = array('skey' => 'y');
        $this->fieldlist['idPerfil'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Usuarios_Perfiles xxxxxxxxxxxxxx //\n";
    }
    function getIdUsers($idPerfil) {
        $upWhr="idPerfil=$idPerfil";
        $upData=$this->getData($upWhr,0,"idUsuario");
        return array_column($upData, "idUsuario");
    }
}
