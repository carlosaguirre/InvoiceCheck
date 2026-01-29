<?php
require_once dirname(__DIR__)."/bootstrap.php";

require_once "clases/Proveedores.php";
require_once "clases/Usuarios.php";

$prvObj = new Proveedores();
$usrObj = new Usuarios();

$result = $usrObj->getList("password",null,"id,nombre,persona,email");
if (!empty($result)) {
    $array = explode("|",$result);
    $fixarr = [];
    for($i=0; isset($array[$i]); $i+=4) {
        $idx = $i/4;
        $id = $array[$i];
        $fldarr = [];
        $fldarr["id"] = $id;
        $fixarr[$idx]["id"] = $id;
        
        $codigo = $array[$i+1];
        $fixarr[$idx]["nombre"] = $codigo;
        $fixarr[$idx]["persona"] = $array[$i+2];
        $fixarr[$idx]["email"] = $array[$i+3];

        try {
            $rfc = $prvObj->getValue("codigo",$codigo,"rfc");
            $fixarr[$idx]["rfc"] = $rfc;

            $userSalt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
            $userKey = hash("sha256", $rfc . $userSalt);
            for($round=0; $round<65536; $round++) {
                $userKey = hash('sha256', $userKey . $userSalt);
            }
            $fixarr[$idx]["password"] = $userKey;
            $fixarr[$idx]["seguro"] = $userSalt;
        
            $fldarr["password"] = $userKey;
            $fldarr["seguro"] = $userSalt;
            if ($usrObj->saveRecord($fldarr)) {
                $fixarr[$idx]["resultado"] = "Guardado";
            } else $fixarr[$idx]["resultado"] = "ERROR";
        } catch(Exception $e) {
            $fixarr[$idx]["excepcion"] = $e->getMessage();
        }
    }
    echo arr2List($fixarr);
}
require_once "configuracion/finalizacion.php";
