<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/InfoLocal.php";

$obj = new InfoLocal();
if (isValueService()) getValueService($obj);
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
else if (isset($_POST["accion"])) {
    sessionInit();
    switch($_POST["accion"]) {
        case "definir":
            $nombre=$_POST["nombre"]??"";
            $valor=$_POST["valor"]??"";
            if (in_array($nombre, ["CFDI_IGNORE2020LIMIT","CFDI_IGNOREMONTHLIMIT","CFDI_ALLOW01x4_","CFDI_ALLOW33_","CFDI_ALLOWP01","CFDI_ALLOWPRTV_"]) || substr($nombre, 0,15)==="CFDI_ALLOW01x4_" || substr($nombre, 0,13)==="CFDI_ALLOW33_" || substr($nombre, 0, 14)==="CFDI_ALLOWP01_" || substr($nombre, 0,15)==="CFDI_ALLOWPRTV_") {
                $esAdmin=validaPerfil("Administrador");
                $esSistemas=$esAdmin||validaPerfil("Sistemas");
                if (!$esSistemas) {
                    errNDie("No tiene autorizado modificar configuración",["file"=>getShortPath(__FILE__),"line"=>__LINE__,"HTTP_CLIENT_IP"=>$_SERVER['HTTP_CLIENT_IP']??"","HTTP_X_FORWARDED_FOR"=>$_SERVER['HTTP_X_FORWARDED_FOR']??"","REMOTE_ADDR"=>$_SERVER['REMOTE_ADDR']??""]+$_POST,"config");
                }
                $usrData=[];
                if ($nombre==="CFDI_ALLOW01x4_"||$nombre==="CFDI_ALLOW33_"||$nombre==="CFDI_ALLOWP01_"||$nombre==="CFDI_ALLOWPRTV_") {
                    require_once "clases/Usuarios.php";
                    $usrObj=new Usuarios();
                    $usrData=$usrObj->getData("id='$valor' or nombre='$valor'",0,"id,nombre,persona,email");
                    if (!isset($usrData[0]["nombre"][0]))
                        errNDie(["eName"=>"P","className"=>"boldValue","eChilds"=>[["eText"=>"Usuario '"],["eName"=>"U","className"=>"cancelLabel","eText"=>$valor],["eText"=>"' no encontrado"]]],["file"=>getShortPath(__FILE__),"line"=>__LINE__,"HTTP_CLIENT_IP"=>$_SERVER['HTTP_CLIENT_IP']??"","HTTP_X_FORWARDED_FOR"=>$_SERVER['HTTP_X_FORWARDED_FOR']??"","REMOTE_ADDR"=>$_SERVER['REMOTE_ADDR']??""]+$_POST,"config");
                    $usrData=$usrData[0];
                    $nombre.=$usrData["id"];
                    $valor="1";
                }
                $oldId = $obj->getValue("nombre",$nombre,"id");
                $fieldarray = ["nombre"=>$nombre, "valor"=>$valor];
                if (!empty($oldId)) $fieldarray["id"]=$oldId;
                if (!$obj->saveRecord($fieldarray)) {
                    global $query;
                    errNDie(["eName"=>"P","className"=>"boldValue","eText"=>"La configuracion no pudo guardarse"],["file"=>getShortPath(__FILE__),"line"=>__LINE__,"HTTP_CLIENT_IP"=>$_SERVER['HTTP_CLIENT_IP']??"","HTTP_X_FORWARDED_FOR"=>$_SERVER['HTTP_X_FORWARDED_FOR']??"","REMOTE_ADDR"=>$_SERVER['REMOTE_ADDR']??"","query"=>$query,"errors"=>DBi::$errors]+$_POST,"error");
                }
                doclog("InfoLocal Guardada exitosamente","config",[$nombre=>$valor,"HTTP_CLIENT_IP"=>$_SERVER['HTTP_CLIENT_IP']??"","HTTP_X_FORWARDED_FOR"=>$_SERVER['HTTP_X_FORWARDED_FOR']??"","REMOTE_ADDR"=>$_SERVER['REMOTE_ADDR']??""]);
                echo json_encode(["result"=>"success"]+$usrData);
            }
            break;
        case "reloadHourMailCount":
        // if(length(nombre)>15, right(nombre,length(nombre)-16), right(nombre,length(nombre)-10)) k, valor v
        // nombre like 'mail_hour%';
            $obj->rows_per_page  = 100;
            $data=$obj->getData("nombre like 'mail_hour%'",0,"if(length(nombre)>15, right(nombre,length(nombre)-16), right(nombre,length(nombre)-10)) k, valor v");
            $fixdata=[];
            foreach ($data as $idx => $row) {
                $rowk=$row["k"]; $rowv=$row["v"];
                if ($rowk==="key") {
                    $fixdata["key"]=$rowv;
                    continue;
                }
                $domain=substr($row["k"], 0, -2);
                $hour=+substr($row["k"], -2);
                $count=+$rowv;
                if (!isset($fixdata[$domain])) $fixdata[$domain]=[];
                $fixdata[$domain][$hour]=$count;
            }
            if (isset($fixdata["default"])) {
                if (!isset($fixdata["glama"])) $fixdata["glama"]=[];
                foreach($fixdata["default"] as $hr => $num) {
                    if (!isset($fixdata["glama"][$hr])) $fixdata["glama"][$hr]=$num;
                    else $fixdata["glama"][$hr]+=$num;
                }
                unset($fixdata["default"]);
            }
            echo json_encode(["result"=>"success","data"=>$fixdata]);
            break;
    }
} else if (isset($_GET["accion"])) {
    if (!$obj->available()) {
        echo "Error: Servicio Validador de Metodo de Pago no disponible.";
        exit(1);
    }
    $accion = strtolower($_GET["accion"]);
    $nombre = $_GET["nombre"];
    $opcion = $_GET["opcion"];
    switch ($accion) {
        case "definir" : 
            $valor = $_GET["valor"];
            if(empty($nombre)||empty($valor)) {
                echo "Error: Debe indicar nombre y valor.";
            } else if($obj->definir($nombre,$valor)) {
                echo "Exito: Variable $nombre = $valor";
            } else {
                echo "Error: No se pudo guardar $nombre => $valor";
            }
            break;
        case "quitar":
            if(empty($nombre)) {
                echo "Error: Debe indicar el nombre.";
            } else {
                $result = $obj->quitar($nombre);
                if($result===false)
                    echo "Error: No se pudo quitar variable $nombre ";
                else echo "Exito: Borrado $nombre => $result";
            }
            break;
        case "obtener":
            if(empty($nombre)) {
                echo "Error: Debe indicar el nombre.";
            } else {
                $result = $obj->obtener($nombre);
                if(!isset($result) || $result===false)
                    echo "Error: No se pudo obtener variable $nombre ";
                else if(empty($result)) echo "Error: Vacio(".gettype($result).")";
                else echo "Exito: $result";
            }
            break;
        case "recuperar":
            if(empty($nombre)) {
                echo "Error: Debe indicar el nombre.";
            } else {
                $result = $obj->recuperar($nombre);
                if($result===false)
                    echo "Error: No se pudo recuperar variable $nombre ";
                else echo "Exito: $result";
            }
            break;
        default:
            echo "Error: Debe indicar accion=[definir|quitar|obtener|recuperar] &amp; nombre=<nombreVar> &amp; valor=<valorVar>";
    }
    if (!empty($opcion)) {
        switch(strtolower($opcion)) {
            case "showlog":
                echo "<br>\n<xmp>".$obj->log."</xmp>";
                break;
        }
    }
} else {
    echo "InfoLocal";
}
