<?php
require_once dirname(__DIR__)."/bootstrap.php";
include_once "clases/Proveedores.php";
include_once "clases/Usuarios.php";
include_once "clases/Usuarios_Perfiles.php";

clog2("OK");
// Alta Clientes y Proveedores por archivo csv
clog2("POST:\n".json_encode($_POST));
clog2("FILES:\n".json_encode($_FILES));

ini_set('max_execution_time', 600);

$result = "";
$bdlog = "";
function micro_time() {
    $temp = explode(" ", microtime());
    return bcadd($temp[0], $temp[1], 6);
}
function procesaCSV($csvtext) {
    // csv2text($csvtext);
    // csv2table($csvtext);
    csv2bd($csvtext);
}
function csv2text($csvtext) {
    $lineas = preg_split("/[\\r\\n]+/", $csvtext);
    $content = "<xmp>".implode("\n", $lineas)."</xmp>";
}
function csv2table($csvtext) {
    $lineas = preg_split("/[\\r\\n]+/", $csvtext);
    $content = "<table>".PHP_EOL;
    foreach($lineas as $linea) {
        $celdas = str_getcsv($linea,",");
        if (strlen($content)<10) {
            $rowIni = "<thead><tr>";
            $rowEnd = "</tr></thead><tbody>";
            $cellIni = "<th>";
            $cellEnd = "</th>";
            //array_shift($celdas);
            //$content .= "<!-- ".json_encode($celdas)." -->".PHP_EOL;
            //$content .= "<!-- ".$cellIni.implode($cellEnd.$cellIni,$celdas).$cellEnd." -->".PHP_EOL;
        } else {
            $rowIni = "<tr>";
            $rowEnd = "</tr>";
            $cellIni = "<td>";
            $cellEnd = "</td>";
        }
        $content .= $rowIni.$cellIni.implode($cellEnd.$cellIni, $celdas).$cellEnd.$rowEnd.PHP_EOL;
    }
    $content .= "</tbody></table>";
}
function csv2bd($csvtext) {
    global $bdlog;
    $lineas = preg_split("/[\\r\\n]+/", $csvtext);
    echo "<table border='1' style='border-collapse:collapse;'>".PHP_EOL;
    $rowIni = "<tr>"; $rowEnd = "</tr>";
    $primera = TRUE;
    foreach($lineas as $linea) {
        $celdas = str_getcsv($linea,",");
        if ($primera) {
            $blockIni = "<thead>"; $blockEnd = "</thead><tbody>";
            $cellIni = "<th>"; $cellEnd = "</th>";
            $cellValid = "Existe";
            $primera = FALSE;
        } else {
            $blockIni = ""; $blockEnd = "";
            $cellIni = "<td style='white-space: nowrap;'>"; $cellEnd = "</td>";
            DBi::autocommit(FALSE);
            $bdlog = "";
            $cellValid = existeEnBD($celdas);
            if ($cellValid) {
                $cellValid = "CREADO!";
                DBi::commit();
            } else {
                $cellValid = $bdlog;
                DBi::rollback();
            }
            DBi::autocommit(TRUE);
        }
        echo $blockIni.$rowIni.$cellIni.implode($cellEnd.$cellIni, $celdas).$cellEnd;
        echo $cellIni.$cellValid.$cellEnd;
        echo $rowEnd.$blockEnd.PHP_EOL;
    }
    echo "</tbody></table>";
}
function existeEnBD($prov) {
    global $bdlog;
    $prvArr = getProveedorArray($prov);
    if ($prvArr) {
        $prvObj = new Proveedores();
        if ($prvObj->exists("codigo='".$prov[0]."'")) {
            $bdlog .= "Proveedor $prov[0] Existe. \n";

            $usrObj = new Usuarios();
            if ($usrObj->exists("nombre='".$prov[0]."'")) {
                $bdlog .= "Usuario $prov[0] Existe. \n";
            } else $bdlog .= "Usuario $prov[0] <u><b>NO</b> Existe</u>. \n";
            return FALSE;
        }
        if (!$prvObj->saveRecord($prvArr)) {
            $bdlog .= "Provedor Errores: ".implode("|",$prvObj->errors)."\n";
            return FALSE;
        }
        $usrArr = getUsuarioArray($prov);
        if ($usrArr) {
            $usrObj = new Usuarios();
            if ($usrObj->exists("nombre='".$prov[0]."'")) {
                $bdlog .= "Usuario $prov[0] Existe\n";
                return FALSE;
            }
            if (!$usrObj->saveRecord($usrArr)) {
                $bdlog .= "Usuario Errores: ".implode("|",$usrObj->errors)."\n";
                return FALSE;
            }
            $upArr = ["idUsuario"=>$usrObj->lastId, "idPerfil"=>"3"]; // Perfil de Proveedor
            $upObj = new Usuarios_Perfiles();
            if (!$upObj->exists("idUsuario='$upArr[idUsuario]' AND idPerfil='$upArr[idPerfil]'")) {
                if (!$upObj->saveRecord($upArr)) {
                    $bdlog .= "UsuarioPerfil Errores: ".implode("|",$upObj->errors)."\n";
                    return FALSE;
                }
            }
        } else return FALSE;
    } else return FALSE;
    return TRUE;
}
// [codigo, razonSocial, rfc, email, finVigencia]
function getProveedorArray($prov) {
    global $bdlog;
    $fechaVencimiento = procesaFecha($prov[4]);
    $proveedorValido = TRUE;
    $fldarr = [];
    if (empty($prov[0])) { $proveedorValido = FALSE; $bdlog="Falta codigo de proveedor\n"; }
    else $fldarr["codigo"] = $prov[0];
    if (empty($prov[1])) { $proveedorValido = FALSE; $bdlog="Falta razon social de proveedor\n"; }
    else $fldarr["razonSocial"] = $prov[1];
    if (empty($prov[2])) { $proveedorValido = FALSE; $bdlog="Falta rfc de proveedor\n"; }
    else $fldarr["rfc"] = $prov[2];
//        if (empty($fechaVencimiento)) $fldarr["status"] = "registrado";
//        else if (fechaVencida($fechaVencimiento)) {
//            $fldarr["status"] = "vencido";
//            $fldarr["finVigencia"] = $fechaVencimiento;
//        } else {
        $fldarr["status"] = "activo";
//            $fldarr["finVigencia"] = $fechaVencimiento;
//        }
    if (!$proveedorValido) return FALSE;
    return $fldarr;
}
// [nombre, persona, password, email, vigencia]
function getUsuarioArray($prov) {
    $usuarioValido = TRUE;
    $fldarr = [];
    if (empty($prov[0])) { $usuarioValido = FALSE; $bdlog="Falta nombre de usuario\n"; }
    else $fldarr["nombre"] = $prov[0];
    if (empty($prov[1])) { $usuarioValido = FALSE; $bdlog="Falta nombre de persona\n"; }
    else $fldarr["persona"] = $prov[1];
    if (empty($prov[2]) || !$usuarioValido) {
        $usuarioValido = FALSE;
        $bdlog="Falta password de usuario\n";
    } else {
        $userSalt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
        $userKey = hash("sha256", $prov[2] . $userSalt);
        for($round=0; $round<65536; $round++) {
            $userKey = hash('sha256', $userKey . $userSalt);
        }
        $fldarr["password"] = $userKey;
        $fldarr["seguro"] = $userSalt;
    }
    if (!empty($prov[3])) $fldarr["email"] = $prov[3];
    if (!$usuarioValido) return FALSE;
    return $fldarr;
}
function procesaFecha($dd_mm_aaaa) {
    if (empty($dd_mm_aaaa) || strlen($dd_mm_aaaa)!=10) return false;
    $diaStr = substr($dd_mm_aaaa, 0,2);
    $mesStr = substr($dd_mm_aaaa, 3,2);
    $anoStr = substr($dd_mm_aaaa, 6);
    if (!ctype_digit($diaStr) || $diaStr=="00") return false;
    if (!ctype_digit($mesStr) || $mesStr=="00") return false;
    if (!ctype_digit($anoStr) || $anoStr=="00") return false;
    return $anoStr."-".$mesStr."-".$diaStr;
}
function fechaVencida($aaaa_mm_dd) {
    global $fechaActual;
    if (!isset($fechaActual)) {
        $fechaActual = DateTime::createFromFormat('Y-m-d',(new DateTime())->format('Y-m-d'));
    }
    $lafecha = DateTime::createFromFormat('Y-m-d',$aaaa_mm_dd);
    $esMenor = ($lafecha < $fechaActual);
    if ($esMenor) return TRUE;
    return FALSE;
}
?>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <title>Alta Proveedores</title>
    <script>
        function log(texto) {
            console.log(texto);
        }
        window.onload = function(evt) { log("<?= $result ?>"); };
    </script>
  </head>
  <body>
    <h1>Alta Proveedores</h1>
    <form method="post" name="formAltaCsv" action="altaproveedor.php" target="_self" enctype="multipart/form-data">
       Archivo CSV de proveedores: <input type="file" name="csvfile" id="csvfile"> <input type="submit" name="submitcsv" id="submitcsv" value="Registrar">
    </form>
    <div id="contenido">
<?php
if (isset($_POST["submitcsv"]) && $_POST["submitcsv"]=="Registrar") {
    $target_dir = "uploads/";
    if (isset($_FILES["csvfile"])) {
        $filedesc = $_FILES["csvfile"];
        $target_file = $target_dir . basename($filedesc["name"]);
        $file_type = pathinfo($target_file, PATHINFO_EXTENSION);
        $result .= "nombre=$filedesc[name]\\ntipo=$filedesc[type]\\npath=$filedesc[tmp_name]\\nerror=$filedesc[error]\\nbytes=$filedesc[size]";
        $content = file_get_contents($filedesc[tmp_name]);
        $time_start = micro_time();
        procesaCSV($content);
        $time_stop = micro_time();
        $time_overall = bcsub($time_stop, $time_start, 6);
        $content .= "Execution time - $time_overall Seconds";
    } else echo ".";
} else echo "-";
?>
    </div>
  </body>
</html>
<?php
