<?php 
require_once dirname(__DIR__)."/bootstrap.php";

if (!hasUser() || (!validaPerfil("Administrador")&&!validaPerfil("Gestor")) || !isset($_POST["command"])) {
    if (hasUser()) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /$_project_name/");
    require_once "configuracion/finalizacion.php";
    die("Redirecting to: $project_name");
}

switch($_POST["command"]) {
    case "Borrar":
        echo "<div style=\"margin: 0 auto; text-align: left; width: 50%;\">";
        echo "<p>Error al borrar proveedor</p>\n";
        if (!empty($_GET)) echo "GET:".arr2List($_GET);
        if (!empty($_POST)) echo "POST:".arr2List($_POST);
        if (!empty($_FILES)) echo "FILES:".arr2List($_FILES);
        echo "</div><!-- RESULTADO:ERROR -->";
        break;
    case "Guardar":
        echo "<div style=\"margin: 0 auto; text-align: left; width: 50%;\">";
        $fldarr = [];
        $errorMessage = "";
        if (!empty($_POST["proveedor_id"])) $fldarr["id"]=$_POST["proveedor_id"];

        if (empty($_POST["proveedor_field"])) $errorMessage .= "<p>Se requiere la razón social del proveedor</p>";
        else $fldarr["razonSocial"]=$_POST["proveedor_field"];

        if (empty($errorMessage) && empty($_POST["proveedor_code"])) $errorMessage .= "<p>Se requiere el código de proveedor</p>";
        else $fldarr["codigo"]=$_POST["proveedor_code"];

        if (empty($errorMessage) && empty($_POST["proveedor_rfc"])) $errorMessage .= "<p>Se requiere el RFC del proveedor</p>";
        else {
            $fldarr["rfc"]=strtoupper(preg_replace("/[^a-z0-9]/i", "", $_POST["proveedor_rfc"]));
            if (empty($fldarr["rfc"])) $errorMessage .= "<p>El RFC del proveedor debe contener letras y números solamente</p>";
        }

        if (empty($errorMessage)) {
            if (!empty($_POST["proveedor_zona"])) $fldarr["zona"]=$_POST["proveedor_zona"];
            if (!empty($_POST["proveedor_cuenta"])) $fldarr["cuenta"]=$_POST["proveedor_cuenta"];
            if (!empty($_FILES["proveedor_nombre_archivo_recibo"])) {
                $files = $_FILES["proveedor_nombre_archivo_recibo"];
                $filenam = $files["name"];
                $filetyp = $files["type"];
                $filetmp = $files["tmp_name"];
                $fileerr = $files["error"];
                $filesiz = $files["size"];
                if (!empty($fileerr) && $fileerr!==UPLOAD_ERR_OK) {
                    switch($fileerr) {
                        case UPLOAD_ERR_INI_SIZE:
                            $errorMessage.="<p>El archivo PDF excede el tamaño máximo permitido por el portal</p>";
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $errorMessage.="<p>El archivo PDF excede el tamaño máximo que soporta el navegador</p>";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errorMessage.="<p>Descarga incompleta del archivo PDF. Intente nuevamente por favor</p>";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $errorMessage.="<p>No se encontró el archivo a descargar. Intente nuevamente por favor</p>";
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $errorMessage.="<p>Temporalmente el portal tiene la descarga de archivos deshabilitada</p>";
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $errorMessage.="<p>Temporalmente el portal no permite la descarga de archivos</p>";
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $errorMessage.="<p>Temporalmente el portal no soporta la descarga de archivos por extensión</p>";
                            break;
                        default:
                            $errorMessage.="<p>Error desconocido durante la descarga del archivo</p>";
                            break;
                    }
                }
                if (empty($errorMessage) && empty($filetyp)) $errorMessage .= "<p>Formato del archivo desconocido</p>";
                if (empty($errorMessage) && $filetyp!=="application/pdf") $errorMessage .= "<p>Archivo inválido ($filetyp), debe ser PDF</p>";
                if (empty($errorMessage) && empty($filetmp)) $errorMessage .= "<p>Archivo descargado no identificado</p>";
                if (empty($errorMessage)) {
                    $fldarr["edocta"] = "prvCta".$fldarr["rfc"].".pdf";
                    $filepath = $_SERVER['DOCUMENT_ROOT']."cuentas/docs/";
                    if (move_uploaded_file($filetmp, $filepath.$fldarr["edocta"])===false) $errorMessage.="<p>Error al descargar archivo en el servidor, consulte a su administrador</p>";
                }
            }
        }
        if (empty($errorMessage)) {
            require_once "clases/Proveedores.php";
            $prvObj = new Proveedores();
            if ($prvObj->saveRecord($fldarr)===false) {
                $errorMessage.="<p>No se pudieron guardar los datos del proveedor</p>";
                clog2($prvObj->log);
            }
        }
        if (empty($errorMessage)) echo "<p>Proveedor guardado satisfactoriamente</p><!-- RESULTADO:EXITO -->\n";
        else                      echo $errorMessage."<!-- RESULTADO:ERROR -->\n";
        echo "</div>";
        break;
    case "Procesar":
        if (empty($_FILES) || empty($_FILES["archivo_csv"]))
            echo "<tr><td colspan='3'>Sin archivo a procesar</td></tr><!-- RESULTADO:ERROR --><!-- MENSAJEERROR:No se recibió ningún archivo a procesar -->";
        else {
            $files = $_FILES["archivo_csv"];
            $filenam = $files["name"];
            $filetyp = $files["type"];
            $filetmp = $files["tmp_name"];
            $fileerr = $files["error"];
            $filesiz = $files["size"];
            if (!empty($fileerr) && $fileerr!==UPLOAD_ERR_OK) {
                switch($fileerr) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errorMessage.="El archivo PDF excede el tamaño máximo permitido por el portal";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessage.="El archivo PDF excede el tamaño máximo que soporta el navegador";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMessage.="Descarga incompleta del archivo PDF. Intente nuevamente por favor";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMessage.="No se encontró el archivo a descargar. Intente nuevamente por favor";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMessage.="Temporalmente el portal tiene la descarga de archivos deshabilitada";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMessage.="Temporalmente el portal no permite la descarga de archivos";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errorMessage.="Temporalmente el portal no soporta la descarga de archivos por extensión";
                        break;
                    default:
                        $errorMessage.="Error desconocido durante la descarga del archivo";
                        break;
                }
            }
            if (empty($errorMessage) && empty($filetyp)) $errorMessage .= "Formato del archivo desconocido";
            $mimecsv=["text/csv","application/csv","application/excel","application/vnd.ms-excel","application/vnd.msexcel","text/anytext","text/comma-separated-values","application/txt","text/plain"];
            if (empty($errorMessage) && !in_array($filetyp,$mimecsv)) $errorMessage .= "Archivo inválido ($filetyp), debe ser CSV";
            if (empty($errorMessage) && empty($filetmp)) $errorMessage .= "Archivo descargado no identificado";
            if (empty($errorMessage)) {
                if (($handle = fopen($filetmp, "r"))!==FALSE) {
                    $inloop=false;
                    $displayedRows=0;
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $inloop=true;
                        if (isset($data[0])) $codigo = $data[0]; else $codigo="";
                        if (isset($data[1])) $cuenta = $data[1]; else $cuenta="";
                        if (isset($data[2])) $razon = implode(",",array_slice($data,2)); else $razon="";
                        if(!isset($prvObj)) {
                            require_once "clases/Proveedores.php";
                            $prvObj = new Proveedores();
                        }
                        $dbdata = $prvObj->getValue("codigo",$codigo,"razonSocial,cuenta");
                        list($coderr, $ctaerr, $razerr) = ["","",""];
                        if (!empty($dbdata) && strpos($dbdata,"|")!==false) {
                            list($dbrazon, $dbcuenta) = explode("|",$dbdata);
                            if ($razon!==$dbrazon) $razerr = " title=\"No coincide con '$dbrazon'\" class=\"redden\" onclick=\"overlayMessage('<p>La Razón Social \'$razon\' no coincide con \'$dbrazon\'</p>', 'ERROR');\"";
                            if ($cuenta!==$dbcuenta) $ctaerr = " title=\"No coincide la cuenta\" class=\"redden\" onclick=\"overlayMessage('<p>El número de cuenta \'$cuenta\' no le corresponde al proveedor</p>', 'ERROR');\"";
                        } else {
                            $coderr = " title=\"Código de Proveedor '$codigo' no existe\" class=\"redden\" onclick=\"overlayMessage('<p>El código de proveedor \'$codigo\' no existe</p>', 'ERROR');\"";
                            $ctaerr = $coderr;
                            $razerr = $coderr;
                        }
                        if (!empty($coderr) || !empty($ctaerr) || !empty($razerr)) {
                            $errorMessage = "Se encontraron errores, se muestran en rojo con detalle al posicionar el ratón encima";
                            echo "<tr><td$coderr>$codigo</td><td$ctaerr>$cuenta</td><td$razerr>$razon</td></tr>\n";
                            $displayedRows++;
                        }
                    }
                    fclose($handle);
                    if(!$inloop) $errorMessage = "El archivo está vacío o no pudo leerse";
                } else $errorMessage = "No se pudo abrir el archivo, verifique que tenga el formato correcto";
            }
        }
        if (empty($errorMessage)) echo "<!-- RESULTADO:EXITO -->";
        else                      echo "<!-- RESULTADO:ERROR --><!-- MENSAJEERROR:$errorMessage -->";
        break;
    default:
        echo "<div style=\"margin: 0 auto; text-align: left; width: 50%;\">";
        echo "<p>Accion inválida</p>\n";
        if (!empty($_GET)) echo "GET:".arr2List($_GET);
        if (!empty($_POST)) echo "POST:".arr2List($_POST);
        if (!empty($_FILES)) echo "FILES:".arr2List($_FILES);
        echo "</div><!-- RESULTADO:ERROR -->";
}
require_once "configuracion/finalizacion.php";
