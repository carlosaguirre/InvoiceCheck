<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/QueryService.php";
require_once "clases/Proveedores.php";

$obj = new Proveedores();
if (isValueService()) getValueService($obj);
else if (isTestService()) getTestService($obj);
else if (isCatalogService()) getCatalogService($obj);
else if (isSelectorHTML()) getSelectorHTML($obj);
else if (!empty($_GET["nextCode"])) echo $obj->getNextCode($_GET["nextCode"]);
else if (isRegistryData()) getRegistryData($obj);
elseif (isAccountCheck()) doAccountCheck($obj);
elseif (isVerifyProvider()) doVerifyProvider($obj);
else if (isEvaluateProvider()) doEvaluateProvider($obj);
elseif (isFixStatus()) doFixStatus($obj);
elseif (isLoadFile()) doLoadFile($obj);
elseif (isSavePrv()) doSavePrv($obj);
elseif (isSetNumPg()) doSetNumPg($obj);

function setError($message) {
    //echo "<!-- INI setError $message -->";
    echo json_encode(["result"=>"error","message"=>$message]);
    //echo "<!-- END setError -->";
}
function setSuccess($message,$extra=[]) {
    echo json_encode(["result"=>"success","message"=>$message]+$extra);
}
function isValidFileP($file) {
    if (empty($file)||$file["size"]==0) setError("No se recibi&oacute; informaci&oacute;n de archivo");
    else if (empty($file["tmp_name"])) setError("No se reconoce el archivo");
    else if (!empty($file["error"]) && $file["error"]!==UPLOAD_ERR_OK) {
        switch($file["error"]) {
            case UPLOAD_ERR_INI_SIZE:
                setError("El tama&ntilde;o del archivo no debe exceder ".ini_get('upload_max_filesize')." MiB");
                break;
            case UPLOAD_ERR_FORM_SIZE:
                setError("El archivo excede el tama&ntilde;o m&aacute;ximo que soporta el navegador");
                break;
            case UPLOAD_ERR_PARTIAL:
                setError("Descarga incompleta del archivo");
                break;
            case UPLOAD_ERR_NO_FILE:
                setError("No se seleccion&oacute; ning&uacute;n archivo");
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                setError("La descarga de archivos est&aacute; deshabilitada");
                break;
            case UPLOAD_ERR_CANT_WRITE:
                setError("El archivo no pudo guardarse en el servidor");
                break;
            case UPLOAD_ERR_EXTENSION:
                setError("Temporalmente el portal no soporta la descarga de archivos por extensi&oacute;n");
                break;
            default:
                setError("Error desconocido durante la descarga del archivo");
                break;
        }
    } else if (empty($file["type"])) setError("Formato de archivo desconocido");
    else return true;
    return false;
}
function isSetNumPg() {
    return isset($_POST["command"])&&$_POST["command"]==="setnumpg";
}
function doSetNumPg($prvObj) {
    if (!hasUser()) {
        setError("Operaci&oacute;n no Autorizada");
        die();
    }
    $esAdmin=validaPerfil("Administrador");
    if (!$esAdmin) {
        setError("Operaci&oacute;n no Autorizada.");
        die();
    }
    if (!isset($_POST["id"])) {
        setError("Proveedor no identificado");
        die();
    }
    if (!$prvObj->exists("id=$_POST[id]")) {
        setError("El proveedor no existe");
        die();
    }
    $prvData=$prvObj->getData("id='$_POST[id]'",0,"edocta,numpagEdoCta,opinion,numpagOpinion");
    $extra=[];
    if(isset($prvData[0])) {
        /*
        if(isset($prvData[0]["edocta"][0])) {
            $extra["numpagEdoCta"]=$prvData[0]["numpagEdoCta"];
            if($extra["numpagEdoCta"]==="0") {
                ;
            }
        }
        */
        if(isset($prvData[0]["opinion"][0])) {
            $extra["numpagOpinion"]=$prvData[0]["numpagOpinion"];
            if($extra["numpagOpinion"]==="0") {
                exec('"C:\\Program Files\\XPdfTools\\bin64\\pdfinfo.exe" "C:\\Apache24\\htdocs\\invoice\\cuentas\\docs\\'.$prvData[0]["opinion"].'"', $output);
                $pagecount = 0;
                foreach($output as $op) {
                    if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                        $pagecount = intval($matches[1]);
                        break;
                    }
                }
                if ($pagecount>0) {
                    $prvObj->saveRecord(["id"=>$_POST["id"],"numpagOpinion"=>$pagecount]);
                    $extra["numpagOpinion"]=$pagecount;
                }
            }
        }
    }
    setSuccess("Ready",$extra);
}
function isSavePrv() {
    return isset($_POST["command"])&&$_POST["command"]==="saveProvider";
}
function doSavePrv($prvObj) {
    if (!hasUser()) {
        setError("Operaci&oacute;n no Autorizada");
        die();
    }
    $esAdmin=validaPerfil("Administrador");
    $esSistemas=$esAdmin||validaPerfil("Sistemas");
    $modificaProv=$esSistemas||modificacionValida("Proveedor");
    $validaCuentas=$esSistemas||validaPerfil("Valida Bancarias");
    if ((isset($_POST["bank"])||isset($_POST["bankrfc"])||isset($_POST["account"])||isset($_FILES["file"]))&&!$modificaProv) {
        setError("Operaci&oacute;n no autorizada1");
        die();
    }
    if (isset($_POST["verify"])&&$_POST["verify"]!=="0"&&!$validaCuentas) {
        setError("Operaci&oacute;n no autorizada2");
        die();
    }
    if (!$modificaProv&&!$validaCuentas) {
        setError("Operaci&oacute;n no Autorizada3");
        die();
    }
    if (!isset($_POST["id"])) {
        setError("Proveedor no identificado");
        die();
    }
    if (!$prvObj->exists("id=$_POST[id]")) {
        setError("El proveedor no existe");
        die();
    }
    if (isset($_FILES["file"])&&!isValidFileP($_FILES["file"])) {
        die(); // error generado en isValidFileP
    }
    if (isset($_FILES["file"])&&$_FILES["file"]["type"]!=="application/pdf") {
        setError("El formato del archivo debe ser PDF");
        die();
    }
    if (isset($_FILES["file"])&&!isset($_POST["rfc"])) {
        setError("Falta RFC del proveedor");
        die();
    }
    if (isset($_FILES["file"])&&!isset($_POST["type"])) {
        setError("Falta tipo de archivo");
        die();
    }
    $fldarr=["id"=>$_POST["id"]];
    if (isset($_POST["bank"][0])) $fldarr["banco"]=$_POST["bank"];
    if (isset($_POST["bankrfc"][0])) $fldarr["rfcbanco"]=$_POST["bankrfc"];
    if (isset($_POST["account"][0])) $fldarr["cuenta"]=$_POST["account"];
    if (isset($_POST["verify"][0])) $fldarr["verificado"]=$_POST["verify"];
    if (isset($_FILES["file"])) {
        $filepath=$_SERVER['DOCUMENT_ROOT']."cuentas/docs/";
        $filename=$_POST["rfc"]."-".$_POST["type"]."-".date("Y").str_pad(date("n"),2,"0",STR_PAD_LEFT).str_pad(date("j"),2,"0",STR_PAD_LEFT).".pdf";
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $filepath.$filename)===false) {
            $err=error_get_last();
            doclog("ERROR Proveedores SavePrv","error",["POST"=>$_POST,"error"=>$err]);
            setError("Error al guardar archivo".(isset($err["message"])?": $err[message]":""));
            return false;
        }
        $fldarr["edocta"]=$filename;
    }
    if ($prvObj->saveRecord($fldarr)) {
        global $prcObj;
        if (!isset($prcObj)) {
            require_once "clases/Proceso.php";
            $prcObj=new Proceso();
        }
        $prcObj->cambioProveedor($_POST["id"],$_POST["status"],getUser()->nombre,"doSavePrv");
        setSuccess("Actualizaci&oacute;n de cuenta exitosa",isset($fldarr["edocta"])?["filename"=>$fldarr["edocta"]]:[]);
    } else {
        doclog("DBERROR Proveedores SavePrv","error",["POST"=>$_POST,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
        setError("ERROR: ".DBi::getErrno()." - ".DBi::getError());
    }
}
function isLoadFile() { return isset($_POST["command"])&&$_POST["command"]==="loadFile"; }
function doLoadFile($prvObj) {
    if (!hasUser()||(!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!modificacionValida("Proveedor"))) setError("No tiene permiso para cargar un archivo");
    else if (!isset($_POST["fileKey"])||!isset($_POST["fileKey"][0])) setError("No se recibi&oacute; identificador de archivo");
    else if (!isset($_FILES[$_POST["fileKey"]])) setError("No se recibi&oacute; archivo");
    else {
        $file=$_FILES[$_POST["fileKey"]];
        if(!isValidFileP($file)) { // ya se envió error
        } else if ($file["type"]!=="application/pdf")
    setError("El formato del archivo debe ser PDF");
        else {
            $fecha=date("Y").str_pad(date("n"),2,"0",STR_PAD_LEFT).str_pad(date("j"),2,"0",STR_PAD_LEFT);
            if (empty($_POST["remoteName"])) {
                $remoteName="usr-".getUser()->nombre."-".$fecha;
            } else {
                $remoteName="tmp-".$_POST["remoteName"]."-".$fecha;
            }
            $filepath = $_SERVER['DOCUMENT_ROOT']."cuentas/docs/{$remoteName}.pdf";
            if (move_uploaded_file($file["tmp_name"], $filepath)===false) {
                $err=error_get_last();
                doclog("ERROR Proveedores LoadFile","error",["POST"=>$_POST,"error"=>$err]);
                setError("Error al guardar archivo".(isset($err["message"])?": $err[message]":""));
            } else setSuccess(substr($remoteName,4).".pdf");
        }
    }
}
function isFixStatus() {
    return isset($_POST["command"])&&$_POST["command"]==="fixStatus";
}
function doFixStatus($prvObj) {
    global $query;
    if (!isset($_POST["id"])) setError("Proveedor no identificado");
    elseif (!isset($_POST["status"])) setError("Status no especificado");
    elseif ($prvObj->saveRecord(["id"=>$_POST["id"],"status"=>$_POST["status"]])) {
        if (!hasUser()) {
            setError("Operaci&oacute;n no Autorizada");
        } else {
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambioProveedor($_POST["id"],$_POST["status"],getUser()->nombre,"FixStatus");
            echo json_encode(["result"=>"success","message"=>"Actualizaci&oacute;n de status exitosa"]);
        }
    }
    else echo json_encode(["result"=>"error","message"=>DBi::getErrno()." : ".DBi::getError()]);
}
function isVerifyProvider() {
    return isset($_POST["command"])&&$_POST["command"]==="verificarProveedor";
}
function doVerifyProvider($prvObj) {
    global $query;
    doclog("INI FUNCTION doVerifyProvider","action",$_POST);
    if (!isset($_POST["id"]))
        echo json_encode(["result"=>"error","message"=>"Proveedor no identificado"]);
    elseif (!isset($_POST["verificado"]))
        echo json_encode(["result"=>"error","message"=>"Variable no incluida"]);
    elseif ($prvObj->saveRecord(["id"=>$_POST["id"],"verificado"=>$_POST["verificado"]]))
        echo json_encode(["result"=>"success","message"=>"Verificaci&oacute;n Exitosa"]);
    else {
        doclog("DBERROR Proveedores VerifyProvider","error",["POST"=>$_POST,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
        echo json_encode(["result"=>"error","message"=>DBi::getErrno()." : ".DBi::getError()]);
    }
}
function isEvaluateProvider() {
    return isset($_POST["command"])&&$_POST["command"]==="opinionProveedor";
}
function doEvaluateProvider($prvObj) {
    global $query;
    doclog("INI FUNCTION doEvaluateProvider","action",$_POST);
    if (!isset($_POST["id"]))
        echo json_encode(["result"=>"error","message"=>"Proveedor no identificado"]);
    elseif (!isset($_POST["cumplido"]))
        echo json_encode(["result"=>"error","message"=>"Variable no incluida"]);
    else {
        $genDate=DateTime::createFromFormat('d/m/Y',$_POST["generaopinion"]);
        $expDate=DateTime::createFromFormat('d/m/Y',$_POST["venceopinion"]);
        if (is_bool($genDate)||is_bool($expDate)) {
            doclog("DTERROR doEvaluateProvider","error",$_POST);
            echo json_encode(["result"=>"error","message"=>"Fecha de opinion incompleta"]);
        } else if ($prvObj->saveRecord(["id"=>$_POST["id"],"cumplido"=>$_POST["cumplido"],"generaopinion"=>$genDate->format("Y-m-d"),"venceopinion"=>$expDate->format("Y-m-d")]))
            echo json_encode(["result"=>"success","message"=>"Cumplimiento Exitoso"]);
        else {
            doclog("DBERROR doEvaluateProvider","error",["POST"=>$_POST,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
            echo json_encode(["result"=>"error","message"=>$query.". ".DBi::getErrno()."-".DBi::getError()]);
        }
    }
}
function isAccountCheck() {
    return isset($_POST["command"])&&$_POST["command"]==="verificarCuentas";
}
function doAccountCheck($prvObj) {
    global $query;
    if (!hasUser()) {
        echoJsNDie("refresh", "Sin sesion");
    }
    $esAdmin=validaPerfil("Administrador");
    $esSistemas=$esAdmin||validaPerfil("Sistemas");
    $esCuentasBancarias=$esSistemas||validaPerfil("Cuentas Bancarias");
    if(!$esCuentasBancarias) {
        echoJsNDie("refresh", "No autorizado");
    }
    if (!isset($_POST["data"])) {
        errNDie("Sin datos",null,false);
    }
    $sumOK=0;
    $sumEr=0;
    echo "<!-- BLK -->"; // json progress chunk separator
    foreach($_POST["data"] as $idx=>$data) {
        $num=$idx+1;
        doclog("verificarCuentas","cuentas",["num"=>$num,"data"=>$data]);
        if (is_string($data)) {
            if ($data[0]==="[") {
                $data=json_decode($data);
                //$data=str_replace(["[","\"","'","]"], "", $data);
            } else {
                $data=explode(",",$data,5);
            }
        }
        doclog("Explode Data","cuentas",["data"=>$data]); // ,"decoded"=>$decoded
        $msjOK="";
        $msjErr="";
        $dbA=null;
        if (!isset($data[4])) {
            $msjErr.="<p class='marblk2'>Datos incompletos en archivo TXT</p>";
            doclog(" - datChk ERR: Datos incompletos en archivo TXT","cuentas");
        } else {
            $pst=array_combine(["rowId","codigo","rfc","cuenta","razonSocial"],$data);
            if (!isset($pst["cuenta"][0])) {
                $msjErr.="<p class='marblk2'>Cuenta en blanco en archivo TXT</p>";
                doclog(" - datChk ERR: Cuenta en blanco en archivo TXT","cuentas");
            } else {
                if (isset($pst["codigo"][0]))
                    $prvData=$prvObj->getData("codigo='$pst[codigo]'",0,"*, date(venceopinion)<date(now()) vencido");
                if(!isset($prvData[0]) && isset($pst["rfc"][0]))
                    $prvData=$prvObj->getData("rfc='$pst[rfc]'",0,"*, date(venceopinion)<date(now()) vencido");
                if (!isset($prvData[0])) {
                    $msjErr.="<p class='marblk2'>Proveedor desconocido</p>";
                    global $query;
                    doclog(" - datChk ERR: Proveedor desconocido","cuentas",["query"=>$query]);
                } else if (isset($prvData[1])) {
                    $msjErr.="<p class='marblk2'>Registro sin c&oacute;digo de proveedor, con RFC duplicado, eliminar ambiguedad para poder comparar.</p>";
                    global $query;
                    doclog(" - datChk ERR: Proveedor ambiguo","cuentas",["query"=>$query,"prvData"=>$prvData]);
                } else {
                    $dbA = $prvData[0];
                    doclog(" - datChk OK","cuentas");
                }
            }
        }
        if (isset($dbA)) {
            if ($pst["codigo"]!==$dbA["codigo"]) {
                $msjErr.="<p class='marblk2 bgred'>No coincide c&oacute;digo de proveedor '$pst[codigo]'(txt) con '$dbA[codigo]'(reg)</p>";
                doclog(" - dbChk ERR: No coincide codigo de proveedor","cuentas",["codArch"=>$pst["codigo"],"codDB"=>$dbA["codigo"]]);
            }
            require_once "clases/Usuarios.php";
            $usrObj=new Usuarios();
            $usrData=$usrObj->getData("nombre='$dbA[codigo]'",0,"id,email");
            if (isset($usrData[0]))
                $usArr = $usrData[0];
            // ToDo: cuenta tiene que tener 10 o 18 digitos...
            // ToDo: En registro y faltabanco, restringir cuenta a solo digitos (quitar espacios, simbolos y letras) y que deben ser 10 o 18 caracteres
            if (!isset($dbA["cuenta"][0])) {
                if ($dbA["status"]==="actualizar") {
                    $msjErr.="<p class='marblk2'>Cuenta bancaria en blanco, el proveedor a&uacute;n no actualiza sus datos</p>";
                    doclog(" - ctaChk ERR: Cuenta en blanco en DB.","cuentas",["status"=>"actualizar"]);
                } else {
                    $msjErr.="<p class='marblk2'>Cuenta bancaria en blanco</p>";
                    doclog(" - ctaChk ERR: Cuenta en blanco en DB.","cuentas",["status"=>$dbA["status"]]);
                }
            } else if ($pst["cuenta"]!==$dbA["cuenta"]) {
                $pos=strpos($dbA["cuenta"], $pst["cuenta"]);
                $esBanamex=false;
                if ($pos!==false) {
                    $preTxt=""; $posTxt="";
                    if ($pos>0) $preTxt=substr($dbA["cuenta"], 0, $pos);
                    $lenDB=strlen($dbA["cuenta"]);
                    $lenTX=strlen($pst["cuenta"]);
                    $esBanamex=($pos===6 && substr($preTxt,0,3)==="002" && $lenTX===11 && $lenDB===18);
                    $pos+=$lenTX;
                    if($lenDB>$pos) $posTxt=substr($dbA["cuenta"],$pos);
                    if ($esBanamex) {
                        $msjOK.="<p class='marblk2 bggreen'>CLABE registrada BANAMEX <span class='lightBlurred'>$preTxt</span><span class='greenHighlight'>$pst[cuenta]</span><span class='lightBlurred'>$posTxt</span> coincide.</p>";
                        doclog(" - ctaChk OK: CLABE BANAMEX coincide","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"],"preNum"=>$preTxt,"posNum"=>$posTxt]);
                    } else if ($lenDB===18) {
                        $msjErr.="<p class='marblk2'>CLABE registrada '<span class='yellowedbg'>$preTxt</span><span class='bggreen'>$pst[cuenta]</span><span class='yellowedbg'>$posTxt</span>' coincide parcialmente, no es BANAMEX</p>";
                        doclog(" - ctaChk ERR: CLABE registrada coincide pero no es BANAMEX","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"],"preNum"=>$preTxt,"posNum"=>$posTxt]);
                    } else {
                        $msjErr.="<p class='marblk2'>Cuenta registrada '<span class='yellowedbg'>$preTxt</span><span class='bggreen'>$pst[cuenta]</span><span class='yellowedbg'>$posTxt</span>' coincide parcialmente, no es CLABE</p>";
                        doclog(" - ctaChk ERR: Cuenta similar no es BANAMEX","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"],"preNum"=>$preTxt,"posNum"=>$posTxt]);
                    }
                } else {
                    $ipos=strpos($pst["cuenta"],$dbA["cuenta"]);
                    if($ipos!==false) {
                        $preTxt="";
                        $posTxt="";
                        if ($ipos>0) $preTxt=substr($pst["cuenta"], 0, $ipos);
                        $lenDB=strlen($dbA["cuenta"]);
                        $lenTX=strlen($pst["cuenta"]);
                        $iEsBanamex=($ipos===6 && substr($preTxt,0,3)==="002" && $lenTX===18 && $lenDB===11);
                        $ipos+=$lenDB;
                        if($lenTX>$ipos) $posTxt=substr($pst["cuenta"],$ipos);
                        if ($iEsBanamex) {
                            $msjErr.="<p class='marblk2'>Cuenta registrada coincide parcialmente con CLABE BANAMEX en egreso '<span class='yellowedbg darkgrayed'>$preTxt</span><span class='bggreen'>$dbA[cuenta]</span><span class='yellowedbg darkgrayed'>$posTxt</span>'</p>";
                            doclog(" - ctaChk ERR: Cuenta registrada coincide parcialmente con CLABE BANAMEX en egreso","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"],"preNum"=>$preTxt,"posNum"=>$posTxt]);
                        } else if ($lenTX===18) {
                            $msjErr.="<p class='marblk2'>Cuenta registrada coincide parcialmente con CLABE en egreso '<span class='yellowedbg darkgrayed'>$preTxt</span><span class='bggreen'>$dbA[cuenta]</span><span class='yellowedbg darkgrayed'>$posTxt</span>'</p>";
                            doclog(" - ctaChk ERR: Cuenta registrada coincide parcialmente con CLABE BANAMEX en egreso","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"],"preNum"=>$preTxt,"posNum"=>$posTxt]);
                        } else {
                            $msjErr.="<p class='marblk2'>Cuenta registrada coincide parcialmente con cuenta en egreso '<span class='yellowedbg darkgrayed'>$preTxt</span><span class='bggreen'>$dbA[cuenta]</span><span class='yellowedbg darkgrayed'>$posTxt</span>' pero ninguna es CLABE</p>";
                            doclog(" - ctaChk ERR: Cuenta podria no ser CLABE","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"],"preNum"=>$preTxt,"posNum"=>$posTxt]);
                        }
                    } else {
                        $msjErr.="<p class='marblk2'>No coincide cuenta bancaria con la registrada '$dbA[cuenta]'</p>";
                        doclog(" - ctaChk ERR: No coincide cuenta bancaria","cuentas",["archivo"=>$pst["cuenta"],"db"=>$dbA["cuenta"]]);
                    }
                }
            } else {
                $lenTX=strlen($pst["cuenta"]);
                $sEsCLABE=($lenTX===18);
                $sEsBanamex=(substr($pst["cuenta"],0,3)==="002" && $sEsCLABE);
                if ($sEsBanamex) {
                    $msjOK.="<p class='marblk2 bggreen'>CLABE BANAMEX registrada coincide</p>";
                    doclog(" - ctaChk OK: CLABE BANAMEX coincide","cuentas",["cuenta"=>$dbA["cuenta"]]);
                } else if ($sEsCLABE) {
                    $msjOK.="<p class='marblk2 bggreen'>CLABE registrada coincide</p>";
                    doclog(" - ctaChk OK: CLABE coincide","cuentas",["cuenta"=>$dbA["cuenta"]]);
                } else {
                    $msjOK.="<p class='marblk2 bggreen'>Cuenta registrada coincide, no es CLABE</p>";
                    doclog(" - ctaChk OK: Cuenta coincide","cuentas",["cuenta"=>$dbA["cuenta"]]);
                }
            }
            if (!isset($dbA["opinion"][0])) {
                $msjErr.="<p class='marblk2'>No tiene documento de opini&oacute;n</p>";
                doclog(" - satChk ERR: No tiene documento de opinion","cuentas");
            } else if ($dbA["cumplido"]==="-2") {
                $msjErr.="<p class='marblk2'>El documento de opini&oacute;n est&aacute; Rechazado</p>";
                doclog(" - satChk ERR: Documento de opinion Rechazado","cuentas",["cumplido"=>$dbA["cumplido"]]);
            } else if ($dbA["cumplido"]==="-1") {
                $msjErr.="<p class='marblk2'>El documento de opini&oacute;n est&aacute; Vencido</p>";
                doclog(" - satChk ERR: Documento de opinion Vencido","cuentas",["cumplido"=>$dbA["cumplido"]]);
            } else if ($dbA["cumplido"]==="0") {
                $msjErr.="<p class='marblk2'>El documento de opini&oacute;n est&aacute; Pendiente de Verificar</p>";
                doclog(" - satChk ERR: Documento de opinion Pendiente","cuentas",["cumplido"=>$dbA["cumplido"]]);
            } else if ($dbA["vencido"]) {
                $msjErr.="<p class='marblk2'>El documento de opini&oacute;n est&aacute; Vencido</p>";
                doclog(" - satChk ERR: Documento de opinion Vencido","cuentas",["cumplido"=>$dbA["cumplido"],"vencido"=>"1","vencimiento"=>$dbA["venceopinion"]]);
            } else doclog(" - satChk OK: Documento de opinion aceptado","cuentas",["cumplido"=>$dbA["cumplido"]]);
            $verificado=$dbA["verificado"]+0;
            if ($verificado==0) { // EDOCTA PENDIENTE
                if(!isset($msjErr[0])) $sumEr++;
                doclog(" - edoChk ERR: Estado de cuenta pendiente","cuentas",["verificado"=>"0","status"=>$dbA["status"]]);
            } else if ($verificado<0) { // EDOCTA RECHAZADO
                if(!isset($msjErr[0])) $sumEr++;
                doclog(" - edoChk ERR: Estado de cuenta rechazado","cuentas",["verificado"=>$verificado,"status"=>$dbA["status"]]);
            } else if ($dbA["status"]==="actualizar") {
                $msjErr.="<p class='marblk2'>Se requiere al proveedor ACTUALIZAR sus DATOS</p>";
                doclog(" - edoChk ERR: Status invalido1","cuentas",["verificado"=>$verificado,"status"=>$dbA["status"]]);
            } else if ($dbA["status"]==="inactivo") {
                $msjErr.="<p class='marblk2'>El proveedor fue marcado como INACTIVO.</p>";
                doclog(" - edoChk ERR: Status invalido1","cuentas",["verificado"=>$verificado,"status"=>$dbA["status"]]);
            } else if ($dbA["status"]==="bloqueado") {
                $msjErr.="<p class='marblk2'>El proveedor se encuentra bloqueado.</p>";
                doclog(" - edoChk ERR: Status invalido3","cuentas",["verificado"=>$verificado,"status"=>$dbA["status"]]);
            } else if ($dbA["status"]!=="activo") {
                $msjErr.="<p class='marblk2'>El proveedor tiene status invalido.</p>";
                doclog(" - edoChk ERR: Status invalido4","cuentas",["verificado"=>$verificado,"status"=>$dbA["status"]]);
            } else {
                if(!isset($msjErr[0])) $sumOK++;
                doclog(" - edoChk OK: Status valido","cuentas",["verificado"=>$verificado,"status"=>$dbA["status"]]);
            }
        } else if (!isset($msjErr[0])) {
            $msjErr.="<p class='marblk2'>No hay datos de proveedor.</p>";
            doclog(" - dbChk ERR: No se obtuvieron datos de la base","cuentas");
        }
        if (isset($msjErr[0])) {
            $result="error";
            $mensaje=$msjErr.$msjOK;
            $sumEr++;
        } else {
            $result="success";
            $mensaje=$msjOK??"";
        }
        doclog(" - msjChk","cuentas",["result"=>$result,"mensaje"=>$mensaje]);
        if (isset($dbA)) echo json_encode(["result"=>$result,"rowId"=>$pst["rowId"],"prvId"=>$dbA["id"]??"","prvCode"=>$dbA["codigo"]??"","prvRazSoc"=>$dbA["razonSocial"]??"","message"=>$mensaje,"banco"=>$dbA["banco"]??"","rfcbanco"=>$dbA["rfcbanco"]??"","cuenta"=>$dbA["cuenta"]??"","edocta"=>$dbA["edocta"]??"","status"=>$dbA["status"]??"","verificado"=>$dbA["verificado"]??"0","rfc"=>$dbA["rfc"]??"","credito"=>$dbA["credito"]??"","formapago"=>$dbA["codigoFormaPago"]??"","zona"=>$dbA["zona"]??"","texto"=>$dbA["comentarios"]??"","cumplido"=>$dbA["cumplido"]??"","inicia"=>$dbA["generaopinion"]??"","expira"=>$dbA["venceopinion"]??"","opinion"=>$dbA["opinion"]??"","usrId"=>$usArr["id"]??"","email"=>$usArr["email"]??""])."<!-- BLK -->"; // ,"query"=>$query
        else echo json_encode(["result"=>$result,"rowId"=>$pst["rowId"],"message"=>$mensaje, "usrId"=>$usArr["id"]??"","email"=>$usArr["email"]??""])."<!-- BLK -->";
        flush_buffers();
    }
    $summaryMsg="";
    if ($sumOK>0) {
        $ss=($sumOK==1?"":"s");
        $summaryMsg.="$sumOK registro$ss <BUTTON onclick='generaTxtPago()'>A PAGAR</BUTTON>";
        if ($sumEr>0) $summaryMsg.=" y ";
    }
    if ($sumEr>0) {
        $ss=($sumEr==1?"":"s");
        $summaryMsg.="$sumEr registro$ss <BUTTON onclick='generaTxtError()'>CON ERROR</BUTTON>";
    }
    if (isset($summaryMsg[0])) {
        echo json_encode(["summary"=>$summaryMsg,"sumOK"=>$sumOK,"sumER"=>$sumEr])."<!-- BLK -->";
    }
}
function flush_buffers($doStart=true) {
    ob_end_flush();
    if (ob_get_level()>0) ob_flush();
    flush();
    if ($doStart) ob_start();
}
function isRegistryData() {
    sessionInit();
    return isset($_SESSION['user'])&&!empty($_POST["accion"])&&$_POST["accion"]==="regdata"&&isset($_POST["code"]);
}
function getRegistryData($obj) {
    if (isset($_POST["code"])) {
        $code=$_POST["code"];
        $arrData = $obj->getRegistryArrData($code);
    } else if (isset($_POST["id"])) {
        $id=$_POST["id"];
        $arrData = $obj->getRegistryArrData($id);
    }
    if (!empty($arrData)) {
        if (isset($arrData["error"]) && isset($arrData["errno"])) {
            echo json_encode($arrData);
        } else if (isset($_POST["nacional"]) && $arrData["nacional"]!==$_POST["nacional"]) {
            echo json_encode(["error"=>"Sólo puede consultar proveedores ".($_POST["nacional"]?"nacionales":"for&aacute;neos"), "errno"=>Proveedores::PROVEEDORES_ERRCODE_BADNATION,"data"=>$arrData]);
        } else echo json_encode($arrData);
    } else echo json_encode(["error"=>"Registro vacío", "errno"=>Proveedores::PROVEEDORES_ERRCODE_NOTFOUND, "log"=>$obj->log]);
}
function isSelectorHTML() {
    sessionInit();
    return isset($_SESSION['user']) && !empty($_REQUEST["selectorhtml"]);
}
function getSelectorHTML($obj) {
    if (isset($_REQUEST["tipolista"])) {
        $tipoLista = $_REQUEST["tipolista"];
        $esCodigo = $tipoLista==="tcodigo";
        $esRFC = $tipoLista==="trfc";
        $esRazon = $tipoLista==="trazon";
        if (!$esCodigo && !$esRFC && !$esRazon) $esCodigo=true;
    } else {
        $esCodigo=true; $esRFC=false; $esRazon=false;
    }
    if (isset($_REQUEST["defaultText"])) {
        $defaultText = $_REQUEST["defaultText"];
    } else {
        $defaultText = "Todos";
    }
    
    $prvFullMapWhere=$obj->setOptSessions(true);
    $pCod=$_SESSION['prvCodigoOpt'];
    $pRFC=$_SESSION['prvRFCOpt'];
    $pRzS=$_SESSION['prvRazSocOpt'];

    $optionsCode = getHtmlOptions($pCod, (count($pCod)==1?key($pCod):""));
    $optionsRfc = getHtmlOptions($pRFC, (count($pRFC)==1?key($pRFC):""));
    $optionsRefer = getHtmlOptions($pRzS, (count($pRzS)==1?key($pRzS):""));
    
    echo "<select name=\"proveedor\" id=\"prvtcodigo\" onchange=\"selectedItem('prv');\"";
    if (!$esCodigo) echo " class=\"hidden\"";
    echo "><option value=\"\">$defaultText</option>";
    echo $optionsCode;
    echo "</select><select name=\"proveedor\" id=\"prvtrfc\" onchange=\"selectedItem('prv');\"";
    if (!$esRFC) echo " class=\"hidden\"";
    echo "><option value=\"\">$defaultText</option>";
    echo $optionsRfc;
    echo "</select><select name=\"proveedor\" id=\"prvtrazon\" onchange=\"selectedItem('prv');\"";
    if (!$esRazon) echo " class=\"hidden\"";
    echo "><option value=\"\">$defaultText</option>";
    echo $optionsRefer;
    echo "</select> ";
    echo "<img src=\"imagenes/icons/statusRight.png\" id=\"reloadPRV\" onLoad=\"setTimeout(function(){const me=ebyid('reloadPRV');me.onclick=recalculaProveedores;me.title='Recalcular Proveedores';cladd(me,'invisible');me.src='imagenes/icons/descarga6.png';me.onload=null;},3000);\">";
}
