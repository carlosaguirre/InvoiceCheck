<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
if (!isset($consultaFrn)) $consultaFrn = consultaValida("Foraneo")||$esSistemas;
if (!isset($modificaFrn)) $modificaFrn = modificacionValida("Proveedor")||$esSistemas;
if (!isset($validaBanco)) $validaBanco = validaPerfil("Valida Bancarias")||$esSistemas;
if (!isset($consultaMasiva)) $consultaMasiva = validaPerfil("Consulta Masiva Prv")||$esSistemas;
if (!isset($bloqueaProv)) $bloqueaProv = validaPerfil("BloquearPrv")||$esSistemas;
if (!$consultaFrn) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.registro");
clog1seq(1);
?>
<!-- 
    -----------------------------
    GET:
<?= arr2str($_GET); ?>

    -----------------------------
    POST:
<?= arr2str($_POST); ?>

    -----------------------------
    FILES:
<?= arr2str($_FILES); ?>

    -----------------------------
    SESSION:
<?= arr2str($_SESSION); ?>

    -----------------------------
    COOKIES:
<?= arr2str($_COOKIE); ?>
-->
<?php
$soloLectura=(($_POST["view_mode"]??"")==="readonly");
$idProveedor=$_POST["frgn_id"]??"";
$codigoProveedor=strtoupper(trim($_POST["frgn_code"]??""));
$nombreProveedor=strtoupper(trim($_POST["frgn_name"]??""));
$taxIdProveedor=strtoupper(trim($_POST["frgn_taxid"]??""));
$bankId=$_POST["frgn_bankid"]??"";
$paisProveedor=strtoupper(trim($_POST["frgn_site"]??""));
$textProveedor=$_POST["frgn_text"]??"";
$statusProveedor=strtolower(trim($_POST["frgn_status"]??""));
$accVerified=trim($_POST["acc_verified"]??"");
$esServicio=(($_POST["esServicio"]??"")==="1");
$referencias=$_POST["referencia"]??[];

$beginHidden="beginHidden";
/*
if (isset($_POST["frgn_return"][0])) {
    if (empty($_FILES["frgn_receipt"]) || $_FILES["frgn_receipt"]["size"]==0) {
        $receiptName = trim($_POST["frgn_receipt_name"]??"");
    }
} else if (isset($_POST["frgn_submit"])) {
    $doSavePrv=false;
    $doSaveUsr=false;
    $fldarr = [];
    $ufldarr = [];
    $nvPrfo="<p class='margin20 centered'>";
    if (empty($codigoProveedor)) {
        $errorMessage .= $nvPrfo."Es necesario indicar el <b>C&oacute;digo</b> de proveedor extranjero.";
        if (!isset($focusId)) $focusId="frgn_code";
        $nvPrfo="<br>";
    } else if (!isset($codigoProveedor[4])) {
        $errorMessage .= $nvPrfo."El <b>C&oacute;digo</b> de proveedor no está completo.";
        if (!isset($focusId)) $focusId="frgn_code";
        $nvPrfo="<br>";
    }
    if (empty($nombreProveedor)) {
        $errorMessage .= $nvPrfo."Debe capturar el <b>Nombre de Proveedor</b>.";
        if (!isset($focusId)) $focusId="frgn_name";
        $nvPrfo="<br>";
    }
    if (empty($taxIdProveedor)) {
        $errorMessage .= $nvPrfo."Se requiere que especifique <b>Tax Id</b>.";
        if (!isset($focusId)) $focusId="frgn_taxid";
        $nvPrfo="<br>";
    }
    if (empty($bankId) && $statusProveedor==="activo") {
        $errorMessage.=$nvPrfo."Debe indicar el <b>Banco</b> relacionado con el pago cuando status es <b>ACTIVO</b>";
        if (!isset($focusId)) $focusId="frgn_bankid";
        $nvPrfo="<br>";
    }
    if (empty($paymentCode)) {
        $errorMessage .= $nvPrfo."Debe indicar la <b>Forma de Pago</b> más común para este proveedor.";
        if (!isset($focusId)) $focusId="prov_paym";
        $nvPrfo="<br>";
    }
    if (empty($bankAccount)&&$statusProveedor==="activo") {
        $errorMessage .= $nvPrfo."Debe indicar la <b>Cuenta Bancaria</b> del proveedor cuando status es <b>ACTIVO</b>";
        if (!isset($focusId)) $focusId="prov_account";
        $nvPrfo="<br>";
    }
    if (empty($_FILES["prov_receipt"]) || $_FILES["prov_receipt"]["size"]==0) {
        $receiptName = trim($_POST["prov_receipt_name"]??"");
        if (empty($receiptName)&&$statusProveedor==="activo") {
            $errorMessage .= $nvPrfo."Debe incluir un documento PDF con la car&aacute;tula de un <b>Estado de cuenta</b> escaneado cuando status es <b>ACTIVO</b>";
            if (!isset($focusId)) $focusId="prov_account";
            $nvPrfo="<br>";
        }
    }
    if (empty($_FILES["prov_opinion"]) || $_FILES["prov_opinion"]["size"]==0) {
        $opinionName=trim($_POST["prov_opinion_name"]??"");
    }
    if (empty($errorMessage)) {
        if(!validaFormatoCodigo($codigoProveedor, $razonProveedor)) {
            if (!$esAdmin&&!$esSistemas) {
                $errorMessage .= $nvPrfo."El <b>C&oacute;digo</b> de proveedor debe ser la inicial de la Raz&oacute;n Social, gui&oacute;n y tres d&iacute;gitos. (y opcionalmente otro gui&oacute;n y un n&uacute;mero)";
                if (!isset($focusId)) $focusId="prov_code";
                $nvPrfo="<br>";
            }
        }
        if (!valida_rfc($rfcProveedor)) {
            $errorMessage .= $nvPrfo."El <b>RFC</b> no es v&aacute;lido.";
            if (!isset($focusId)) $focusId="prov_rfc";
            $nvPrfo="<br>";
        }
        if (!filter_var($emailUser, FILTER_VALIDATE_EMAIL)) {
            $errorMessage .= $nvPrfo."El <b>Correo electr&oacute;nico</b> no tiene formato adecuado.";
            if (!isset($focusId)) $focusId="user_email";
            $nvPrfo="<br>";
        }
    }
    require_once "clases/Proveedores.php";
    DBi::autocommit(FALSE);
    $prvObj = new Proveedores();
    $esNuevoPrv=empty($idProveedor);
    if ($esNuevoPrv) {
        if (empty($errorMessage)) {
            if ($prvObj->exists("codigo='$codigoProveedor'")) {
                $errorMessage .= $nvPrfo."El <b>C&oacute;digo</b> de proveedor debe ser &uacute;nico.";
                if (!isset($focusId)) $focusId="prov_code";
                $nvPrfo="<br>";
            }
            $codeByRFC = $prvObj->getValue("rfc",$rfcProveedor,"codigo");
            if (empty($errorMessage) && !empty($codeByRFC)) { // Permitir registro con mismo rfc si coinciden los primeros 5 caracteres del codigo. Esto sirve para aceptar sucursales.
                if (isset($codeByRFC[5])) $codeByRFC=substr($codeByRFC,0,5);
                if ($codeByRFC!==substr($codigoProveedor,0,5)) {
                    $errorMessage .= $nvPrfo."El proveedor con ese <b>R.F.C.</b> y diferente código ya est&aacute; dado de alta.";
                    if (!isset($focusId)) $focusId="prov_rfc";
                    $nvPrfo="<br>";
                }
            } else if(empty($errorMessage) && isset($codigoProveedor[5])) { // Si es el primer rfc, el codigo debe ser de 5 caracteres
                $errorMessage .= $nvPrfo."El sufijo opcional del c&oacute;digo de proveedor solo se permite después de registrado el original de 5 caracteres.";
                if (!isset($focusId)) $focusId="prov_code";
                $nvPrfo="<br>";
            }
        }
    } else {
        list($dbcode,$dbrazsoc,$dbrfc,$dbbanco,$dbrfcbanco,$dbcuenta,$dbref1,$dbref2,$dbedocta,$dbctapg,$dbverificado,$dbopinion,$dbcumplido,$dbsatpg,$dbgenerado,$dbvencido,$dbcredito,$dbformapago,$dbzona,$dbstatus,$dbservicio,$dbcodgdesc,$dbobjimp) = explode("|",$prvObj->getValue("id",$idProveedor,"codigo,razonSocial,rfc,banco,rfcbanco,cuenta,referencia1,referencia2,edocta,numpagEdoCta,verificado,opinion,cumplido,numpagOpinion,generaopinion,venceopinion,credito,codigoFormaPago,zona,status,esServicio,conCodgEnDesc,reqObjImp")); // 
        if ($dbcode!==$codigoProveedor && $prvObj->exists("codigo='$codigoProveedor'")) {
            $errorMessage .= $nvPrfo."El <b>C&oacute;digo</b> de proveedor debe ser &uacute;nico.";
            if (!isset($focusId)) $focusId="prov_code";
            $nvPrfo="<br>";
        } else if ($dbrfc!==$rfcProveedor && $prvObj->exists("rfc='$rfcProveedor'") && !isset($codigoProveedor[6])) {
            $errorMessage .= $nvPrfo."El <b>RFC</b> del proveedor ya existe, anexe sufijo de código y zona para dar de alta una sucursal de dicho proveedor.";
            if (!isset($focusId)) $focusId="prov_code";
            $nvPrfo="<br>";
        } else if (!$bloqueaProv && !empty($statusProveedor) && $dbstatus==="bloqueado" && $statusProveedor!==$dbstatus) {
            $errorMessage .= $nvPrfo."Proveedor <b>BLOQUEADO</b>. Consulte a Direcci&oacute;n por m&aacute;s informaci&oacute;n.";
            $nvPrfo="<br>";
        }
    }

    $pdfTagNames=["receipt"=>"edocta","opinion"=>"opisat"];
    foreach ($pdfTagNames as $key=>$val) {
        $varName=$key."Name";
        if ($key==="opinion") $fldFName=$key;
        else $fldFName=$val;
        if (isset($_FILES["prov_{$key}"])) {
            $files=$_FILES["prov_{$key}"];
            $filenam = $files["name"];
            $filetyp = $files["type"];
            $filetmp = $files["tmp_name"];
            $fileerr = $files["error"];
            $filesiz = $files["size"];
            if (!empty($fileerr) && $fileerr!==UPLOAD_ERR_OK) {
                switch($fileerr) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errorMessage.=$nvPrfo."El archivo PDF excede el tamaño máximo permitido por el portal";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessage.=$nvPrfo."El archivo PDF excede el tamaño máximo que soporta el navegador";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMessage.=$nvPrfo."Descarga incompleta del archivo PDF. Intente nuevamente por favor";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $files=null;
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMessage.=$nvPrfo."Temporalmente el portal tiene la descarga de archivos deshabilitada";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMessage.=$nvPrfo."Temporalmente el portal no permite la descarga de archivos";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errorMessage.=$nvPrfo."Temporalmente el portal no soporta la descarga de archivos por extensión";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                    default:
                        $errorMessage.=$nvPrfo."Error desconocido durante la descarga del archivo";
                        if (!isset($focusId)) $focusId="prov_{$key}";
                        $nvPrfo="<br>";
                        break;
                }
            }
            if (empty($errorMessage) && !empty($files) && empty($filetyp)) {
                $errorMessage .= $nvPrfo."Formato del archivo desconocido";
                if (!isset($focusId)) $focusId="prov_{$key}";
                $nvPrfo="<br>";
            }
            if (empty($errorMessage) && !empty($files) && $filetyp!=="application/pdf") {
                $errorMessage .= $nvPrfo."Archivo inválido ($filetyp), debe ser PDF";
                if (!isset($focusId)) $focusId="prov_{$key}";
                $nvPrfo="<br>";
            }
            if (empty($errorMessage) && !empty($files) && empty($filetmp)) {
                $errorMessage .= $nvPrfo."Archivo descargado no identificado";
                if (!isset($focusId)) $focusId="prov_{$key}";
                $nvPrfo="<br>";
            }
            if (!empty($files)) {
                $fecha=date('Y').str_pad(date('n'),2,"0",STR_PAD_LEFT).str_pad(date('j'),2,"0",STR_PAD_LEFT);
                ${$varName} = $rfcProveedor."-".$val."-".$fecha.".pdf";

                $fldarr[$fldFName] = ${$varName};
                $filepath = $_SERVER['DOCUMENT_ROOT']."cuentas/docs/";
                if (move_uploaded_file($filetmp, $filepath.${$varName})===false) {
                    $errorMessage.=$nvPrfo."Error al descargar archivo en el servidor, consulte a su administrador";
                    if (!isset($focusId)) $focusId="prov_{$key}";
                    $nvPrfo="<br>";
                } else if($key==="opinion") {
                    $prvOpinion=${$varName};
                    exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdfinfo.exe\" \"$filepath$prvOpinion\"", $output);
                    clog2("PATH:'$filepath$prvOpinion', INFO: ".json_encode($output));
                    $pagecount=0;
                    foreach($output as $op) {
                        if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                            $pagecount = intval($matches[1]);
                            break;
                        }
                    }
                    if ($pagecount>0 && (!isset($dbsatpg)||$dbsatpg!==$pagecount)) {
                        $doSavePrv=true;
                        $fldarr["numpagOpinion"]=$pagecount;
                    }
                    exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$filepath$prvOpinion\" -", $output);
                    $dateLine="";
                    $autoReject=false;
                    foreach($output as $op) {
                        if (!isset($dateLine[0])&&preg_match("/Revisi.+n practicada el d.+a (\d+) de (\w+) de (\d+), a las (\d+):(\d+) horas/",$op, $matches) === 1) {
                            $meses=["enero"=>"01","febrero"=>"02","marzo"=>"03","abril"=>"04","mayo"=>"05","junio"=>"06","julio"=>"07","agosto"=>"08","septiembre"=>"09","octubre"=>"10","noviembre"=>"11","diciembre"=>"12"];
                            if(isset($meses[$matches[2]])) {
                                $dateLine=str_pad($matches[1],2,"0",STR_PAD_LEFT)."/".$meses[$matches[2]]."/".$matches[3];
                                if ($autoReject) break;
                            }
                        }
                        if(!$autoReject&&preg_match("/su situaci.+n fiscal no se encuentra al corriente/",$op, $matches) === 1) {
                            $autoReject=true;
                            if (isset($dateLine[0])) break;
                        }
                    }
                    if ($autoReject)
                        $opinionFulfilled="-2";
                    if (isset($dateLine[0])) {
                        $opinionCreated=$dateLine;
                    }
                    //$doSavePrv=true;
                } else {
                    $prvEdoCta=${$varName};
                    exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdfinfo.exe\" \"$filepath$prvEdoCta\"", $output);
                    clog2("PATH:'$filepath$prvEdoCta', INFO: ".json_encode($output));
                    $pagecount=0;
                    foreach($output as $op) {
                        if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                            $pagecount = intval($matches[1]);
                            break;
                        }
                    }
                    if ($pagecount>0 && (!isset($dbctapg)||$dbctapg!==$pagecount)) {
                        $doSavePrv=true;
                        $fldarr["numpagEdoCta"]=$pagecount;
                    }
                }
            }
        } else if (isset(${$varName})) {
            //$doSavePrv=true;
            //$fldarr[$fldFName]=${$varName}; // originado en BD, no hay q guardarlo de nuevo
        }
    }
    if (empty($errorMessage)) {
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
        $usrData=$usrObj->getData("nombre='$codigoProveedor'",0,"id,email");
        if (isset($usrData[0])) {
            $dbUsrId=$usrData[0]["id"];
            $dbUsrEmail=$usrData[0]["email"];
            if ($esNuevoPrv || (!empty($idUsuario)&&$idUsuario!=$dbUsrId)) {
                $errorMessage .= $nvPrfo."El <b>C&oacute;digo</b> de proveedor ya est&aacute; asignado a un usuario.";
                if (!isset($focusId)) $focusId="prov_code";
            }
        }
    }
    // ToDo: if (date($provDueOpinion)<date()) $opinionFulfilled="-1";

    if (empty($errorMessage)) {
        if ($esNuevoPrv) $fldarr["codigo"]=$codigoProveedor; // $prvObj->getNextCode($fldarr["razonSocial"]);
        else $fldarr["id"]=$idProveedor;
        if (!isset($dbrazsoc)||$dbrazsoc!==$razonProveedor) {
            $doSavePrv=true;
            $fldarr["razonSocial"]=$razonProveedor;
        }
        if (!isset($dbrfc)||$dbrfc!==$rfcProveedor) {
            $doSavePrv=true;
            $fldarr["rfc"]=$rfcProveedor;
        }
        if (!empty($bankAccount)&&(!isset($dbcuenta)||$dbcuenta!==$bankAccount)) {
            $doSavePrv=true;
            $fldarr["cuenta"]=$bankAccount;
        }
        if (isset($creditDays[0])&&(!isset($dbcredito)||$dbcredito!==$creditDays)) {
            $doSavePrv=true;
            $fldarr["credito"]=$creditDays;
        }
        if (!empty($bankName)&&(!isset($dbbanco)||$dbbanco!==$bankName)) {
            $doSavePrv=true;
            $fldarr["banco"]=$bankName;
        }
        if (!empty($bankRfc)&&(!isset($dbrfcbanco)||$dbrfcbanco!==$bankRfc)) {
            $doSavePrv=true;
            $fldarr["rfcbanco"]=$bankRfc;
        }
        if (!empty($paymentCode)&&(!isset($dbformapago)||$dbformapago!==$paymentCode)) {
            $doSavePrv=true;
            $fldarr["codigoFormaPago"]=$paymentCode;
        }
        if (isset($referencias[0][0])&&(!isset($dbref1[0])||$dbref1!==$referencias[0])) {
            $doSavePrv=true;
            $fldarr["referencia1"]=$referencias[0];
        } else if (!isset($referencias[0][0])&&isset($dbref1[0])) {
            $doSavePrv=true;
            $fldarr["referencia1"]="";
        }
        if (isset($referencias[1][0])&&(!isset($dbref2[0])||$dbref2!==$referencias[1])) {
            $doSavePrv=true;
            $fldarr["referencia2"]=$referencias[1];
        } else if (!isset($referencias[1][0])&&isset($dbref2[0])) {
            $doSavePrv=true;
            $fldarr["referencia2"]="";
        }
        if (!empty($zonaProveedor)&&(!isset($dbzona)||$dbzona!==$zonaProveedor)) {
            $doSavePrv=true;
            $fldarr["zona"]=$zonaProveedor;
        }
        if (isset($textProveedor[0])) {
            $doSavePrv=true;
            $fldarr["comentarios"]=$textProveedor;
        }
        if (!empty($statusProveedor)&&(!isset($dbstatus)||$dbstatus!==$statusProveedor)) {
            $doSavePrv=true;
            $fldarr["status"]=$statusProveedor;
        }
        if ($modificaProv&&!$soloLectura) {
            if (($esServicio&&($esNuevoPrv||$dbservicio!=="1"))) {
                $doSavePrv=true;
                $fldarr["esServicio"]="1";
            } else if (!$esNuevoPrv&&!$esServicio&&$dbservicio==="1") {
                $doSavePrv=true;
                $fldarr["esServicio"]="0";
            }
            if (($conCodgEnDesc&&($esNuevoPrv||$dbcodgdesc!=="1"))) {
                $doSavePrv=true;
                $fldarr["conCodgEnDesc"]="1";
            } else if (!$esNuevoPrv&&!$conCodgEnDesc&&$dbcodgdesc==="1") {
                $doSavePrv=true;
                $fldarr["conCodgEnDesc"]="0";
            }
            if (($reqObjImp&&($esNuevoPrv||$dbobjimp!=="1"))) {
                $doSavePrv=true;
                $fldarr["reqObjImp"]="1";
            } else if (!$esNuevoPrv&&!$reqObjImp&&$dbobjimp==="1") {
                $doSavePrv=true;
                $fldarr["reqObjImp"]="0";
            }
        }
        if (isset($accVerified[0])&&(!isset($dbverificado)||$dbverificado!==$accVerified)) {
            $doSavePrv=true;
            $fldarr["verificado"]=$accVerified;
        }
        if (isset($opinionFulfilled[0])&&(!isset($dbcumplido)||$dbcumplido!==$opinionFulfilled)) {
            $doSavePrv=true;
            $fldarr["cumplido"]=$opinionFulfilled;
        }
        if (isset($opinionCreated[0])) {
            $tmpDate=DateTime::createFromFormat('d/m/Y',$opinionCreated);
            echo "<!-- OPINION CREATED STRING = $opinionCreated -->";
            if (!empty($tmpDate)) {
                $tmpFormattedDate=$tmpDate->format('Y-m-d');
                echo "<!-- FORMATTED DATE = $tmpFormattedDate -->";
                if (!isset($dbgenerado)||$dbgenerado!==$tmpFormattedDate) {
                    $doSavePrv=true;
                    $fldarr["generaopinion"] = $tmpFormattedDate;
                }
                $tmpDate->add(new DateInterval("P90D"));
                $today=new DateTime("today");
                $expiredFormattedDate=$tmpDate->format('Y-m-d');
                $opinionExpired=$tmpDate->format('d/m/Y');
                if (!isset($dbvencido)||$dbvencido!==$expiredFormattedDate) {
                    $doSavePrv=true;
                    $fldarr["venceopinion"]=$expiredFormattedDate;
                    }
                if ($opinionFulfilled!=="-2") {
                    if($tmpDate<$today) {
                        if ($opinionFulfilled!=="-1") {
                            $doSavePrv=true;
                            $fldarr["cumplido"]="-1";
                        }
                        $opinionFulfilled="-1";
                    } else if ($tmpDate>$today) {
                        if ($opinionFulfilled!=="0") {
                            $doSavePrv=true;
                            $fldarr["cumplido"]="0";
                        }
                        $opinionFulfilled="0";
                    }
                }
            } else {
                $errorMessage .= $nvPrfo."Falta indicar Fecha de <b>Vigencia</b> de Opini&oacute;n de cumplimiento.";
                if (!isset($focusId)) $focusId="opinion_created";
                echo "<!-- ERROR: FECHA VACIA -->";
            }
        }
    }
    if (empty($errorMessage)) {
        if ($doSavePrv) clog2("LISTO PARA GUARDAR PROVEEDOR: ".json_encode($fldarr));
        else clog2("SIN CAMBIOS PARA GUARDAR PROVEEDOR");
        $savedPrv=$doSavePrv&&$prvObj->saveRecord($fldarr);
        if ($doSavePrv&&!$savedPrv&&empty(DBi::$errno)) {
            global $query;

            clog2("RESULTADO SIN CAMBIOS: $query");
            // Existe un query de update, pero todos los datos coinciden con lo que está guardado. La acción falla sin error pues no hubo cambios. Hay que corregir que no era necesario guardar:
            $doSavePrv=false;
        }
        if ($savedPrv) {
            unset($_SESSION['prvRazSocOpt']);
            unset($_SESSION['prvCodigoOpt']);
            unset($_SESSION['prvRFCOpt']);
            unset($_SESSION['prvMap']);
            $prvLastId = $prvObj->lastId;
            doclog($esNuevoPrv?"Registro de nuevo proveedor":"Actualizacion de proveedor","proveedor",["codigo"=>$codigoProveedor,"id"=>$prvLastId,"values"=>$fldarr]);
            $prvObj->getRegistryArrData($codigoProveedor); // Guarda en session los campos actualizados
        }
        if (!$doSavePrv||$savedPrv) {
            if (isset($emailUser[0])&&(!isset($dbUsrEmail)||$dbUsrEmail!==$emailUser)) {
                $doSaveUsr=true;
                $ufldarr["email"] = $emailUser;
            }
            if ($esNuevoPrv||$razonProveedor!==$dbrazsoc) {
                $doSaveUsr=true;
                $ufldarr["persona"] = $razonProveedor;
            }
            if ($esNuevoPrv||$rfcProveedor!==$dbrfc) { // crea contraseña para nuevo usuario o si cambia el RFC
                $userSalt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
                $userKey = hash("sha256", $rfcProveedor . $userSalt);
                for($round=0; $round<65536; $round++) {
                    $userKey = hash('sha256', $userKey . $userSalt);
                }
                $doSaveUsr=true;
                $ufldarr["password"] = $userKey;
                $ufldarr["seguro"] = $userSalt;
            }
            if (!empty($idUsuario)) $ufldarr["id"] = $idUsuario;
            else if (!empty($dbUsrId)) $ufldarr["id"] = $dbUsrId;
            if ($doSaveUsr&&!isset($ufldarr["id"])) {
                $ufldarr["nombre"] = $codigoProveedor;
            }
            $savedUsr=$doSaveUsr&&$usrObj->saveRecord($ufldarr);
            if ($doSaveUsr&&!$savedUsr&&empty(DBi::$errno)) {
                $doSaveUsr=false;
            }
            if ($savedUsr) {
                if ($esNuevoPrv) {
                    $upArr = ["idUsuario"=>$usrObj->lastId, "idPerfil"=>"3"]; // Perfil de Proveedor
                    require_once "clases/Usuarios_Perfiles.php";
                    $upObj = new Usuarios_Perfiles();
                    if (!$upObj->exists("idUsuario='$upArr[idUsuario]' AND idPerfil='$upArr[idPerfil]'")) {
                        if (!$upObj->saveRecord($upArr)) {
                            $errorMessage .= $nvPrfo."Error al guardar perfil del proveedor $razonProveedor.";
                        }
                    }
                }
            } else if ($doSaveUsr) {
                $errorMessage .= $nvPrfo."Error al guardar usuario $razonProveedor";
            }
        } else if ($doSavePrv) {
            $errorMessage .= $nvPrfo."Error al guardar proveedor $razonProveedor";
        }
    }
    if (empty($errorMessage)) {
        if ($esNuevoPrv) $idProveedor=$prvLastId;
        if (empty($idProveedor)) {
            $errorMessage.=$nvPrfo."Error al obtener identificador del proveedor";
        } else if ($savedPrv) {
            $procStatus = $statusProveedor; //"Registro";
            $procDetalle = "Registro de Proveedor $codigoProveedor $razonProveedor";
            require_once "clases/Proceso.php";
            $prcObj = new Proceso();
            if ($prcObj->cambioProveedor($idProveedor, $procStatus, getUser()->nombre, $procDetalle)) {
                $resultMessage .= "<p class='margin20 centered'>Proveedor $razonProveedor registrado satisfactoriamente.</p>"; //" Consulte su correo electr&oacute;nico por la aprobaci&oacute;n de acceso al sistema.";
            } else {
                echo "<!-- ".$prcObj->log." -->";
                echo "<!-- ".json_encode($prcObj->errors)." -->";
                $errorMessage .= $nvPrfo."Error al guardar proceso de registro.";
            }
        }
    }
    if (empty($errorMessage)) {
        if ($savedPrv) {
            unset($_SESSION['prvRazSocOpt']);
            unset($_SESSION['prvCodigoOpt']);
            unset($_SESSION['prvRFCOpt']);
            DBi::commit();
            // TODO: ENVIO DE CORREO AL USUARIO PARA VALIDAR QUE ES LA PERSONA QUE DA DE ALTA AL PROVEEDOR.
            // TODO: AL APROBAR SE ENVIARA CORREO AL PROVEEDOR.
        }
    } else {
        $errorMessage.="</p>";
        DBi::rollback();
    }
    DBi::autocommit(TRUE);
} else if (isset($_POST["prov_browse"])) {
    $fldarr=[];
    if (isset($codigoProveedor[0])) {
        if (isset($codigoProveedor[4]) || strpos($codigoProveedor, "*")!==FALSE || strpos($codigoProveedor, "%")!==FALSE) {
            $fldarr["codigo"]=str_replace("*", "%", $codigoProveedor);
        } else $fldarr["codigo"]="%".$codigoProveedor."%";
    }
    if (isset($razonProveedor[0])) {
        if (strpos($razonProveedor, "*")!==FALSE || strpos($razonProveedor, "%")!==FALSE) {
            $fldarr["razonSocial"]=str_replace("*", "%", $razonProveedor);
        } else $fldarr["razonSocial"]="%".$razonProveedor."%";
    }
    if (isset($rfcProveedor[0])) {
        if (strpos($rfcProveedor, "*")!==FALSE || strpos($rfcProveedor, "%")!==FALSE) {
            $fldarr["rfc"]=str_replace("*", "%", $rfcProveedor);
        } else $fldarr["rfc"]="%".$rfcProveedor."%";
    }
    if (isset($emailUser[0])) {}
    if (isset($creditDays[0])) {}
    if (isset($paymentCode[0])) {}
    if (isset($bankName[0])) {
        if (strpos($bankName, "*")!==FALSE || strpos($bankName, "%")!==FALSE) {
            $fldarr["banco"]=str_replace("*", "%", $bankName);
        } else $fldarr["banco"]="%".$bankName."%";
    }
    if (isset($bankRfc[0])) {
        if (strpos($bankRfc, "*")!==FALSE || strpos($bankRfc, "%")!==FALSE) {
            $fldarr["rfcbanco"]=str_replace("*", "%", $bankRfc);
        } else $fldarr["rfcbanco"]="%".$bankRfc."%";
    }
    if (isset($bankAccount[0])) {
        if (strpos($bankAccount, "*")!==FALSE || strpos($bankAccount, "%")!==FALSE) {
            $fldarr["cuenta"]=str_replace("*", "%", $bankAccount);
        } else $fldarr["cuenta"]="%".$bankAccount."%";
    }
    if (isset($zonaProveedor[0])) {
        if (strpos($zonaProveedor, "*")!==FALSE || strpos($zonaProveedor, "%")!==FALSE) {
            $fldarr["zona"]=str_replace("*", "%", $zonaProveedor);
        } else $fldarr["zona"]="%".$zonaProveedor."%";
    }
    if (isset($textProveedor[0])) {
        $fldarr["comentarios"]=$textProveedor;
    }
    if (isset($statusProveedor[0])) {
        $fldarr["status"]=$statusProveedor;
    }
    if (isset($accVerified[0])) {
        $fldarr["verificado"]=$accVerified;
    }
    if (isset($opinionFulfilled[0])) {
        $fldarr["cumplido"]=$opinionFulfilled;
    }
    if ($esServicio) {
        $fldarr["esServicio"]="1";
    }
    if ($conCodgEnDesc) {
        $fldarr["conCodgEnDesc"]="1";
    }
    if ($reqObjImp) {
        $fldarr["reqObjImp"]="1";
    }

    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
    if (empty($_POST["regPerPage"])) $prvObj->rows_per_page=100;
    else $prvObj->rows_per_page=+$_POST["regPerPage"];
    if (!empty($_POST["pageSwitch"])) {
        $prvObj->pageno=+$_POST["pageSwitch"];
    } else {
        //clog1("NO PAGE SWITCH");
    }
    $prvObj->clearOrder();
    $prvObj->addOrder("codigo");
    $prvData = $prvObj->getDataByFieldArray($fldarr); //,0,"");
    if (isset($prvData[1])) $urlAction="registroMasivo";
    else if (isset($prvData[0])) {
        [   "id"=>$idProveedor,             //
            "codigo"=>$codigoProveedor,     //
            "razonSocial"=>$razonProveedor, //
            "rfc"=>$rfcProveedor,           //
            "zona"=>$zonaProveedor,         //
            "banco"=>$bankName,             //
            "rfcbanco"=>$bankRfc,           //
            "cuenta"=>$bankAccount,         //
            "referencia1"=>$referencias[0],
            "referencia2"=>$referencias[1],
            "edocta"=>$receiptName,         // prvEdoCta
//                "numpagEdoCta"=>$pagecount,
            "verificado"=>$accVerified,     //
            "opinion"=>$opinionName,        // prvOpinion
//                "numpagOpinion"=>$pagecount,
            "cumplido"=>$opinionFulfilled,  //
            "generaopinion"=>$generaOpinion,
            "venceopinion"=>$venceOpinion,
            "credito"=>$creditDays,         //
            "codigoFormaPago"=>$paymentCode,//
            "status"=>$statusProveedor,     //
            "esServicio"=>$esServicio,
            "conCodgEnDesc"=>$conCodgEnDesc,
            "reqObjImp"=>$reqObjImp,
            "comentarios"=>$textProveedor   //
        ]=$prvData[0];
        if (isset($generaOpinion[0])) {
            $tmpDate=DateTime::createFromFormat("Y-m-d",$generaOpinion);
            $opinionCreated=$tmpDate->format("d/m/Y");
        }
        if (isset($venceOpinion[0])) {
            $tmpDate=DateTime::createFromFormat("Y-m-d",$venceOpinion);
            $opinionExpired=$tmpDate->format("d/m/Y");
        }
        $esServicio=($esServicio==="1");
        $conCodgEnDesc=($conCodgEnDesc==="1");
        $reqObjImp=($reqObjImp==="1");
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
        $usrData=$usrObj->getData("nombre='$codigoProveedor'",0,"id,email");
        $idUsuario=$usrData[0]["id"];
        $emailUser=$usrData[0]["email"];    //

    } else $errorMessage .= "<p class='margin20 centered'>Ning&uacute;n registro encontrado con los criterios indicados.</p>";
    // ToDo: Generar contenido de tabla a partir de los datos anteriores.
    // Incluir paginacion, orden y filtros
} else {
    //$beginHidden.=" hidden";
}

// ---------------------------- Variables de Layout ---------------------------- //
$provRzSocVal=""; $provRfcVal=""; $userEmailVal=""; $provCodVal=""; $provZoneVal=""; $provStatusVal=""; $provAccountVal=""; $provHasRef1=""; $provShowRef1=" class='hidden'"; $provRef1Val=""; $provHasRef2=""; $provShowRef2=" class='hidden'"; $provRef2Val=""; $provReceiptElem=""; $provOpinionElem=""; $provIdVal=""; $provComments="";
$provCreditVal=" value=\"0\""; $provBankVal=""; $provBankRfcVal=""; $provPaymVal="03";
$provPayments = ["02"=>"Cheque nominativo", "03"=>"Transferencia electrónica de fondos"];
if ($errorMessage) { // || $resultMessage) {
    //$traceObj->agrega("Registro con error: $errorMessage");
}
$canSaveClass="";
$browseType="submit";
if (empty($idProveedor)) {
    if (isset($codigoProveedor[4]) && isset($_POST["prov_submit"])) {
        if ($consultaMasiva)
            $browseType="hidden";
    } else {
        $canSaveClass=" class=\"hidden\"";
        $beginHidden.=" hidden";
    }
} else {
    $provIdVal=" value=\"$idProveedor\"";
    if ($consultaMasiva)
        $browseType="hidden";
}
if (!empty($codigoProveedor)) $provCodVal     = " value=\"$codigoProveedor\"";
if (!empty($razonProveedor))  $provRzSocVal   = " value=\"$razonProveedor\"";
if (!empty($rfcProveedor))    $provRfcVal     = " value=\"$rfcProveedor\"";
if (!empty($emailUser))       $userEmailVal   = " value=\"$emailUser\"";
if (!empty($zonaProveedor))   $provZoneVal    = " value=\"$zonaProveedor\"";
if (!empty($textProveedor))   $provComments   = htmlspecialchars($textProveedor);
if (!empty($statusProveedor)) $provStatusVal  = $statusProveedor;
if (!empty($bankAccount))     $provAccountVal = " value=\"$bankAccount\"";
if (isset($referencias[0][0])) {
    $provHasRef1 = " checked";
    $provRef1Val=" value=\"".$referencias[0]."\"";
    $provShowRef1="";
}
if (isset($referencias[1][0])) {
    $provHasRef2 = " checked";
    $provRef2Val=" value=\"".$referencias[1]."\"";
    $provShowRef2="";
}
if (!empty($receiptName)) {
    $provReceiptElem="<INPUT type='hidden' id='prov_receipt_name' name='prov_receipt_name' value='$receiptName'><A href='cuentas/docs/$receiptName' target='archivopdf' class='vAlignCenter'><IMG src='imagenes/icons/pdf200.png' width='20' height='20' class='vAlignCenter'></A>";
    if ($modificaProv&&!$soloLectura)
        $provReceiptElem.="<IMG src='imagenes/icons/deleteIcon16.png' title='Descartar Documento' onclick='let dci=this.previousElementSibling;let pr=ebyid(\"prov_receipt\");if(clhas(pr,\"hidden\")){clrem(pr,\"hidden\");this.src=\"imagenes/icons/backArrow.png\";this.title=\"Restaurar Documento Anterior\";cladd(dci,\"hidden\");}else{cladd(pr,\"hidden\");this.src=\"imagenes/icons/deleteIcon16.png\";this.title=\"Descartar Documento\";clrem(dci,\"hidden\");}' class='vAlignCenter pointer marginV2'>";
    $verifCap=null;
    if (isset($accVerified)) switch($accVerified) {
        case "-1": $verifCap="RECHAZADO"; break;
        case "0": $verifCap="PENDIENTE"; break;
        case "1": $verifCap="ACEPTADO"; break;
    }
    if (isset($verifCap)) {
        if ($validaBanco&&!$soloLectura) {
            $provReceiptElem.="<SELECT id=\"acc_verified\" name=\"acc_verified\" class=\"pad3 vAlignCenter\"><OPTION value=\"0\"".($accVerified==="0"?" selected":"").">PENDIENTE</OPTION><OPTION value=\"1\"".($accVerified==="1"?" selected":"").">ACEPTADO</OPTION><OPTION value=\"-1\"".($accVerified==="-1"?" selected":"").">RECHAZADO</OPTION></SELECT>";
        } else {
            if ($verifCap==="PENDIENTE") $verifCap="EN REVISI&Oacute;N";
            $provReceiptElem.="<SPAN class=\"pad3 vAlignCenter\"><INPUT type=\"hidden\" id=\"acc_verified\" name=\"acc_verified\" value=\"$accVerified\">$verifCap</SPAN>";
        }
    }
} else if (empty($idProveedor)&&!isset($codigoProveedor[4])&&!isset($_POST["prov_submit"])) {
    $provReceiptElem="<SELECT id=\"acc_verified\" name=\"acc_verified\" class=\"pad3 vAlignCenter\"><OPTION value=\"\">TODOS</OPTION><OPTION value=\"0\"".($accVerified==="0"?" selected":"").">PENDIENTE</OPTION><OPTION value=\"1\"".($accVerified==="1"?" selected":"").">ACEPTADO</OPTION><OPTION value=\"-1\"".($accVerified==="-1"?" selected":"").">RECHAZADO</OPTION></SELECT>";
}
if (!empty($opinionName)) {
    $provOpinionElem="<INPUT type='hidden' id='prov_opinion_name' name='prov_opinion_name' value='$opinionName'><A href='cuentas/docs/$opinionName' target='archivopdf' class='vAlignCenter'><IMG src='imagenes/icons/pdf200.png' width='20' height='20' class='vAlignCenter'></A>";
    if ($modificaProv&&!$soloLectura)
        $provOpinionElem.="<IMG src='imagenes/icons/deleteIcon16.png' title='Descartar Documento' onclick='let dci=this.previousElementSibling;let po=ebyid(\"prov_opinion\");if(clhas(po,\"hidden\")){clrem(po,\"hidden\");this.src=\"imagenes/icons/backArrow.png\";this.title=\"Restaurar Documento Anterior\";cladd(dci,\"hidden\");}else{cladd(po,\"hidden\");this.src=\"imagenes/icons/deleteIcon16.png\";this.title=\"Descartar Documento\";clrem(dci,\"hidden\");}' class='vAlignCenter pointer marginV2'>";
    $opiCap=null;
    if (isset($opinionFulfilled)) switch($opinionFulfilled) {
        case "-2": $opiCap="RECHAZADO"; break;
        case "-1": $opiCap="VENCIDO"; break;
        case "0": $opiCap="PENDIENTE"; break;
        case "1": $opiCap="ACEPTADO"; break;
    }
    if (isset($opiCap)) {
        if ($validaOpinion&&!$soloLectura) {
            $provOpinionElem.="<select id=\"opinion_fulfilled\" name=\"opinion_fulfilled\" class=\"pad3 vAlignCenter\"><OPTION value=\"-1\"".($opinionFulfilled==="-1"?" selected":"").">VENCIDO</OPTION><OPTION value=\"0\"".($opinionFulfilled==="0"?" selected":"").">PENDIENTE</OPTION><OPTION value=\"1\"".($opinionFulfilled==="1"?" selected":"").">ACEPTADO</OPTION><OPTION value=\"-2\"".($opinionFulfilled==="-2"?" selected":"").">RECHAZADO</OPTION></SELECT><BR>Vigencia:<span><input type=\"text\" id=\"opinion_created\" name=\"opinion_created\" value=\"$opinionCreated\" class=\"calendar\" onclick=\"javascript:show_calendar_widget(this);\" readonly></span> - <input type=\"text\" id=\"opinion_expired\" name=\"opinion_expired\" value=\"$opinionExpired\" class=\"calendar\" readonly>";
        } else {
            if ($opiCap==="PENDIENTE") $opiCap="EN REVISI&Oacute;N";
            $provOpinionElem.="<SPAN class=\"pad3 vAlignCenter\"><INPUT type=\"hidden\" id=\"opinion_fulfilled\" name=\"opinion_fulfilled\" value=\"$opinionFulfilled\">$opiCap</SPAN><span class=\"pad3 vAlignCenter fontSmall\"><input type=\"hidden\" id=\"opinion_created\" name=\"opinion_created\" value=\"$opinionCreated\">[$opinionCreated - $opinionExpired]</span>";
        }
        $provOpinionElem.="";
    }
} else if (empty($idProveedor)&&!isset($codigoProveedor[4])&&!isset($_POST["prov_submit"])) {
    $provOpinionElem="<SELECT id=\"opinion_fulfilled\" name=\"opinion_fulfilled\" class=\"pad3 vAlignCenter\"><OPTION value=\"\">TODOS</OPTION><OPTION value=\"-1\"".($opinionFulfilled==="-1"?" selected":"").">VENCIDO</OPTION><OPTION value=\"0\"".($opinionFulfilled==="0"?" selected":"").">PENDIENTE</OPTION><OPTION value=\"1\"".($opinionFulfilled==="1"?" selected":"").">ACEPTADO</OPTION><OPTION value=\"-2\"".($opinionFulfilled==="-2"?" selected":"").">RECHAZADO</OPTION></SELECT>";
}
if (!empty($creditDays))  $provCreditVal =" value=\"$creditDays\"";
if (!empty($bankName))    $provBankVal   =" value=\"$bankName\"";
if (!empty($bankRfc))     $provBankRfcVal=" value=\"$bankRfc\"";
if (!empty($paymentCode)) $provPaymVal   =$paymentCode;
if ($modificaProv&&!$soloLectura) {
    $editAttrib="";
    $chkCrdAttrib="";
    $chgCtaEvt=" onchange=\"accDataChanged();\"";
    $chgOpiEvt=" onchange=\"opiDataChanged();\"";
    $provPaymVal = getHtmlOptions($provPayments, $provPaymVal);
} else {
    $editAttrib=" readonly";
    $chkCrdAttrib=" disabled";
    if (empty($provReceiptElem)) $provReceiptElem="<IMG src='imagenes/icons/statusWrong.png' width='20' height='20'/>";
    if (empty($provOpinionElem)) $provOpinionElem="<IMG src='imagenes/icons/statusWrong.png' width='20' height='20'/>";
    $chgCtaEvt="";
    $chgOpiEvt="";
    if ($provPaymVal!=="02") $provPaymVal="03";
    $provPayments=[$provPaymVal=>$provPayments[$provPaymVal]];
    $provPaymVal = getHtmlOptions($provPayments, $provPaymVal);
}
if (!isset($onloadScript)) $onloadScript="";
$onloadScript .= "doFocusOn('prov_code');";
// ---------------------------- Metodos de validacion ---------------------------- //
function validaFormatoCodigo($codigo, $razSoc) {
    $hasSameFirstWord = $codigo[0] == $razSoc[0];
    if (!isset($codigo[5])) {
        if (1===preg_match("/^[A-Z]-\d{3}$/", $codigo))
            return $hasSameFirstWord;
    } else if (1===preg_match("/^[A-Z]-\d{3}-\d+$/", $codigo))
            return $hasSameFirstWord;
    return false;
}
function valida_rfc($valor) {
    $valor = str_replace("-", "", $valor);
    $cuartoValor = substr($valor, 3, 1);
    //RFC sin homoclave
    if (strlen($valor)==10) {
        $letras = substr($valor, 0, 4);
        $numeros = substr($valor, 4, 6);
        if (checkOnlyAlphaAmp($letras) && ctype_digit($numeros)) {
            return true;
        }
        return false;
    //RFC Persona Moral.
    } else if (strlen($valor) == 12 && ctype_digit($cuartoValor)) {
        $letras = substr($valor, 0, 3);
        $numeros = substr($valor, 3, 6);
        $homoclave = substr($valor, 9, 3);
        if (checkOnlyAlphaAmp($letras) && ctype_digit($numeros) && ctype_alnum($homoclave)) {
            return true;
        }
        return false;
    //RFC Persona Física.
    } else if (strlen($valor) == 13 && checkOnlyAlphaAmp($cuartoValor)) {
        $letras = substr($valor, 0, 4);
        $numeros = substr($valor, 4, 6);
        $homoclave = substr($valor, 10, 3);
        if (checkOnlyAlphaAmp($letras) && ctype_digit($numeros) && ctype_alnum($homoclave)) {
            return true;
        }
        return false;
    } else {
        return false;
    }
} //fin validaRFC
function checkOnlyAlphaAmp($text) {
    if (!isset($text[0])) return false;
    return !preg_match("/[^a-zA-ZñÑ&]/",$text);
}
*/
clog1seq(-1);
clog2end("configuracion.registro");
