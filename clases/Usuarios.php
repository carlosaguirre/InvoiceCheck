<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Usuarios extends DBObject {
    const B_ACTUALIZADO=1; // BANDERA 1: DATOS DE PROVEEDOR ACTUALIZADOS
    function __construct() {
        $this->tablename      = "usuarios";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "nombre", "persona", "email", "password", "seguro", "fechaRegistro", "observaciones", "banderas", "unoComo", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['nombre'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Usuarios xxxxxxxxxxxxxx //\n";
    }
    function getPerfiles($usuario=false) {
        $plog = "getPerfiles INI\n";
        if (empty($usuario)) {
            $plog .= " - Empty usuario\n";
            if (empty($this->nombre)) { clog2($plog." - Empty nombre"); return false; }
            $usuario = $this->nombre;
            if (!empty($this->id)) $id = $this->id;
        } else if (is_array($usuario)) {
            $plog .= " - Is Array usuario\n";
            if (empty($usuario["nombre"])) { clog2($plog." - Empty nombre"); return false; }
            $usrArr = $usuario;
            $usuario = $usrArr["nombre"];
            if (!empty($usrArr["id"])) { $plog .= " - Found id $usrArr[id]\n"; $id = $usrArr["id"]; }
        }
        if(empty($id)) { $plog .= " - Empty id\n"; $id = $this->getValue("nombre", $usuario, "id"); }
        require_once "clases/Usuarios_Perfiles.php";
        $upObj = new Usuarios_Perfiles();
        $perfilIds = $upObj->getList("idUsuario", $id, "idPerfil");
        $plog .= " - Retrieved ids list: $perfilIds\n";
        $prfIds = explode("|", $perfilIds);
        $this->log .= $upObj->log;
        require_once "clases/Perfiles.php";
        $prfObj = new Perfiles();
        $perfiles = $prfObj->getList("id", $prfIds, "nombre");
        $plog .= " - Retrieved perfil list: $perfiles\n";
        $this->log .= $prfObj->log;
        $prfArr = explode("|", $perfiles);
        $prfLst = implode(",",$prfArr);
        $this->log .= "Perfiles: $prfLst\n";
        $plog .= " - Perfiles: $prfLst\n";
        clog2($plog);
        return $prfLst;
    }
    function getDataByProfileNames($profileNames,$userFieldNames,$extraWhere) { // "Compras","email,persona","email is not null group by email"
        if (isset($profileNames[0])) {
            if(is_string($profileNames)) {
                $perfilArr=explode(",",$profileNames);
                if(isset($perfilArr[1])) $profileNames=$perfilArr;
            }
            require_once "clases/Perfiles.php";
            $prfObj = new Perfiles();
            $prfObj->rows_per_page=0;
            $prfWhere=$prfObj->getQueryExpression("nombre",$profileNames,"WHERE");
            if(isset($prfWhere[0])) {
                $prfData = $prfObj->getData(rtrim($prfWhere," AND "),0,"id");
                $this->log.=$prfObj->log."// xxxxxx F I N xx P E R F I L E S xxxxxx //\n";
            }
        }
        if(isset($prfData[0]["id"])) {
            require_once "clases/Usuarios_Perfiles.php";
            $upObj = new Usuarios_Perfiles();
            $upObj->rows_per_page=0;
            if (!isset($prfData[1]["id"])) $upWhere="idPerfil=".$prfData[0]["id"];
            else {
                $idPrfLst=array_column($prfData, "id");
                $upWhere=rtrim($upObj->getQueryExpression("idPerfil",$idPrfLst,"WHERE")," AND ");
            }
            $upData = $upObj->getData($upWhere,0,"distinct idUsuario");
            $this->log.=$upObj->log."// xx F I N x U S U A R I O S _ P E R F I L E S xx //\n\n";
        }
        //if (!isset($userFieldNames[0])) $userFieldNames="email,persona";
        //if (!isset($extraWhere[0])) $extraWhere="email is not null group by email";
        if(isset($upData[0]["idUsuario"])) {
            $idUsrLst=array_column($upData, "idUsuario");
            $this->rows_per_page=0;
            return $this->getData($this->getQueryExpression("id",$idUsrLst,"WHERE").($extraWhere??"id>0"),0,$userFieldNames??"*");
        }
        return [];
    }
    function getEmailAddressesByUsrData($usrData) {
        if (is_string($usrData)) {
            // toDo: Validar el formato: [<nombre> cuentaCorreo@dominio.subdominio]
            return ["address"=>$usrData,"name"=>$usrData];
        }
        if (isset($usrData["email"])) {
            return [["address"=>$usrData["email"],"name"=>replaceAccents(isset($usrData["persona"][0])?$usrData["persona"]:$usrData["email"])]];
        }
        if (!isset($usrData[0]["email"])) return [];
        $retAddr=[];
        foreach ($usrData as $idx => $usrElem) {
            $retAddr[]=["address"=>$usrElem["email"],"name"=>replaceAccents(isset($usrElem["persona"][0])?$usrElem["persona"]:$usrElem["email"])];
        }
        return $retAddr;
    }
    function browseByUserName($name,$exceptionList="",$onlyName=false) {
        if (isset($name[0])) {
            if (strpos($name,"*")!==false) $name=str_replace("*", "%", $name);
            else if (strpos($name,"%")===false) $name="%$name%";
            $where = "nombre like '$name' collate utf8_general_ci";
            if (!$onlyName) $where = "($where or persona like '$name' collate utf8_general_ci)";
            $where .= " and id in (select distinct idUsuario from usuarios_perfiles where idPerfil!=3)";
            if (isset($exceptionList[0])) $where.=" and id not in ($exceptionList)";
            return $this->getData($where,0,"id,nombre,persona");
        }
        return [];
    }
}
