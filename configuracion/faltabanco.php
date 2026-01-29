<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.faltabanco");
clog1seq(1);

$userEmail=$user->email;
$provBank=$user->proveedor->banco;
$bankRFC=$user->proveedor->rfcbanco;
$provAccount=$user->proveedor->cuenta;
$prvEdoCta = $user->proveedor->edocta;
$prvCtaVerif = $user->proveedor->verificado;
$prvOpinion = $user->proveedor->opinion;
$ctavList=["-1"=>"RECHAZADO","0"=>"PENDIENTE","1"=>"ACEPTADO"];
$ctaclass=["-1"=>"bgred","0"=>"bgyellow2","1"=>"bggreen"];
$prvOpiFulf = $user->proveedor->cumplido;
$prvOpiExpiry = $user->proveedor->venceopinion;
$prvOpiExpired = ($user->proveedor->vencido==="1");
$opifList=["-2"=>"RECHAZADO","-1"=>"VENCIDO","0"=>"PENDIENTE","1"=>"ACEPTADO"];
$opiclass=["-2"=>"bgred2","-1"=>"bgred","0"=>"bgyellow2","1"=>"bggreen"];
if (isset($prvEdoCta[0])) {
  $prvRcptClss = " class=\"vAlignCenter hidden\"";
  $prvRcptElem = "<INPUT type='hidden' id='prov_receipt_name' name='prov_receipt_name' value='$prvEdoCta'><A href='cuentas/docs/$prvEdoCta' target='archivopdf' id='prov_receipt_doc' class='vAlignCenter'><IMG src='imagenes/icons/pdf200.png' width='20' height='20' class='vAlignCenter'></A><IMG src='imagenes/icons/deleteIcon16.png' onclick='let dci=this.previousElementSibling;let pr=ebyid(\"prov_receipt\");if(clhas(pr,\"hidden\")){clrem(pr,\"hidden\");this.src=\"imagenes/icons/backArrow.png\";this.title=\"Restaurar Documento Anterior\";cladd(dci,\"hidden\");}else{cladd(pr,\"hidden\");this.src=\"imagenes/icons/deleteIcon16.png\";this.title=\"Eliminar Documento\";clrem(dci,\"hidden\");}' class='vAlignCenter btnFX pointer'>";
  if (isset($ctavList[$prvCtaVerif])) {
    $ctaText=$ctavList[$prvCtaVerif];
    if ($ctaText==="PENDIENTE") $ctaText="EN REVISI&Oacute;N";
    $prvRcptElem.=" <span class=\"fontSmall boldValue ".$ctaclass[$prvCtaVerif]."\">$ctaText</span>";
  }
} else {
  $prvRcptClss = " class=\"vAlignCenter\"";
  $prvRcptElem = "";
}
if (isset($prvOpinion[0])) {
    $prvOpiClss = " class=\"vAlignCenter hidden\"";
    $prvOpiElem = "<INPUT type='hidden' id='prov_opinion_name' name='prov_opinion_name' value='$prvOpinion'><A href='cuentas/docs/$prvOpinion' target='archivopdf' id='prov_opinion_doc' class='vAlignCenter'><IMG src='imagenes/icons/pdf200.png' width='20' height='20' class='vAlignCenter'></A><IMG src='imagenes/icons/deleteIcon16.png' onclick='let dci=this.previousElementSibling;let po=ebyid(\"prov_opinion\");if(clhas(po,\"hidden\")){clrem(po,\"hidden\");this.src=\"imagenes/icons/backArrow.png\";this.title=\"Restaurar Documento Anterior\";cladd(dci,\"hidden\");}else{cladd(po,\"hidden\");this.src=\"imagenes/icons/deleteIcon16.png\";this.title=\"Eliminar Documento\";clrem(dci,\"hidden\");}' class='vAlignCenter btnFX pointer'>";
  if (isset($opifList[$prvOpiFulf])) {
    $opiText=$opifList[$prvOpiFulf];
    if ($opiText==="PENDIENTE") $opiText="EN REVISI&Oacute;N";
    $prvOpiElem.=" <span class=\"fontSmall boldValue pointer ".$opiclass[$prvOpiFulf]."\" onclick=\"this.previousElementSibling.click();\">$opiText [".$prvOpiExpiry."]</span>";
  }
} else {
    $prvOpiClss = " class=\"vAlignCenter\"";
    $prvOpiElem = "";
}
if (isset($_POST["actualizaProveedor"]) && $_POST["actualizaProveedor"]==="faltabanco") {
    $errLog="FALTABANCO.ERRLOG";
    $hasMissing=false;
    $extraScript="";
    $userEmail=trim($_POST["user_email"]??"");
    if (!isset($userEmail[0])) {
        $hasMissing=true;
        $errLog.="|Falta Email";
        $extraScript="ebyid('overlay').callOnClose=function(){ebyid('user_email').focus();};";
    }
    $provBank=trim($_POST["prov_bank"]??"");
    if (!isset($provBank[0])) {
        $hasMissing=true;
        $errLog.="|Falta Banco";
        if(!isset($extraScript[0])) $extraScript="ebyid('overlay').callOnClose=function(){ebyid('prov_bank').focus();};";
    }
    $bankRFC=trim($_POST["prov_bankrfc"]??"");
    if (!isset($bankRFC[0])) {
        $hasMissing=true;
        $errLog.="|Falta Rfc de Banco";
        if(!isset($extraScript[0])) $extraScript="ebyid('overlay').callOnClose=function(){ebyid('prov_bankrfc').focus();};";
    }
    $provAccount=trim($_POST["prov_account"]??"");
    if (!isset($provAccount[0])) {
        $hasMissing=true;
        $errLog.="|Falta Cuenta Bancaria";
        if(!isset($extraScript[0])) $extraScript="ebyid('overlay').callOnClose=function(){ebyid('prov_account').focus();};";
    }
    if ((empty($_FILES["prov_receipt"]) || $_FILES["prov_receipt"]["size"]==0)&&!isset($_POST["prov_receipt_name"])) {
        $hasMissing=true;
        $errLog.="|Falta Estado de Cuenta";
        if(!isset($extraScript[0])) $extraScript="ebyid('overlay').callOnClose=function(){let pr=ebyid('prov_receipt');if(pr){if(pr.classList.contains('hidden')){let fx=pr.parentNode.getElementsByClassName('btnFX');if(fx&&fx.length>0)fx[0].focus();}else{pr.focus();}}};";
    }
    if ((empty($_FILES["prov_opinion"]) || $_FILES["prov_opinion"]["size"]==0)&&!isset($_POST["prov_opinion_name"])) {
        $hasMissing=true;
        $errLog.="|Falta Opinion de Cumplimiento";
        if(!isset($extraScript[0])) $extraScript="ebyid('overlay').callOnClose=function(){let po=ebyid('prov_opinion');if(po){if(po.classList.contains('hidden')){let fx=po.parentNode.getElementsByClassName('btnFX');if(fx&&fx.length>0)fx[0].focus();console.log('fx');}else{po.focus();}}};";
    }
    /*if (isset($_POST["nuevaFechaVencimiento"][0])) {
        list($venceDia,$venceMes,$venceAnio)=explode("/",$_POST["nuevaFechaVencimiento"]);
        $tmpVencimiento="$venceAnio-$venceMes-$venceDia";
    }*/

    if (!isset($onloadScript)) $onloadScript="";
    if ($hasMissing) {
        // $errLog.="|Faltan datos";
        $onloadScript .= "overlayMessage('<p>Su información está incompleta. Por favor ingrese los datos faltantes.</p>','ERROR');$extraScript";
    } else if (preg_match('/[^0-9]/', $provAccount)) {
        $errLog.="|CLABE no numérica";
        $onloadScript .= "overlayMessage('<p>Su CLABE solo puede contener d&iacute;gitos.</p>','ERROR');ebyid('prov_account').focus();";
    } else if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        //echo "<!--  WRONG EMAIL-->";
        $errLog.="|Email incorrecto";
        $onloadScript .= "overlayMessage('<p>El <b>Correo electr&oacute;nico</b> no es correcto.</p>','ERROR');ebyid('user_email').focus();";
    } else {
        $saveUser=false;
        $saveProv=false;
        $saveLog="FALTABANCO.SAVELOG";
        $checkSuccess=true;
        $ufldarr = ["id"=>$userid];
        $pfldarr = ["id"=>$user->proveedor->id];
        if ($userEmail!==$user->email) { $ufldarr["email"]=$userEmail; $saveUser=true; $saveLog.="|email"; }
        if ($provBank!==$user->proveedor->banco) { $pfldarr["banco"]=$provBank; $saveProv=true; $saveLog.="|bank"; }
        if ($bankRFC!==$user->proveedor->rfcbanco) { $pfldarr["rfcbanco"]=$bankRFC; $saveProv=true; $saveLog.="|rfcbnk"; }
        if ($provAccount!==$user->proveedor->cuenta) { $pfldarr["cuenta"]=$provAccount; $saveProv=true; $saveLog.="|accbnk"; }
        if (!empty($_FILES["prov_receipt"]) && $_FILES["prov_receipt"]["size"]>0) {
            $file = $_FILES["prov_receipt"];

            if (!empty($file["error"]) && $file["error"]!==UPLOAD_ERR_OK) {
                $checkSuccess=false;
                $errLog.="|EDO CTA UPLOAD ERR";
                switch($file["error"]) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errLog.=": INI SIZE";
                        $onloadScript .= "overlayMessage('<p>El archivo PDF excede el tamaño máximo permitido por el portal.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $errLog.=": FORM SIZE";
                        $onloadScript .= "overlayMessage('<p>El archivo PDF excede el tamaño máximo que soporta el navegador.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    case UPLOAD_ERR_PARTIAL:
                        $errLog.=": PARTIAL";
                        $onloadScript .= "overlayMessage('<p>El archivo PDF excede el tamaño máximo que soporta el navegador.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    case UPLOAD_ERR_NO_FILE:
                        $errLog.=": NO FILE";
                        $onloadScript .= "overlayMessage('<p>No se encontró el archivo a descargar, intente nuevamente.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errLog.=": NO TMP DIR";
                        $onloadScript .= "overlayMessage('<p>Temporalmente el portal tiene la descarga de archivos deshabilitada.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errLog.=": CANT WRITE";
                        $onloadScript .= "overlayMessage('<p>Temporalmente el portal no permite la descarga de archivos.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    case UPLOAD_ERR_EXTENSION:
                        $errLog.=": EXTENSION";
                        $onloadScript .= "overlayMessage('<p>Temporalmente el portal no soporta la descarga de archivos por extensión.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                    default:
                        $errLog.=": OTHER: $file[error]";
                        $onloadScript .= "overlayMessage('<p>Error desconocido durante la descarga del archivo.</p>','ERROR');ebyid('prov_receipt').focus();"; break;
                }
            } else if (!isset($file["type"][0])) {
                $checkSuccess=false; $errLog.="|EDO CTA: NOTYPE";
                $onloadScript .= "overlayMessage('<p>Formato del archivo desconocido.</p>','ERROR');ebyid('prov_receipt').focus();";
            } else if ($file["type"]!=="application/pdf") {
                $checkSuccess=false; $errLog.="|EDO CTA: NO PDF: $file[type]";
                $onloadScript .= "overlayMessage('<p>Archivo inválido ($file[type]), debe ser PDF.</p>','ERROR');ebyid('prov_receipt').focus();";
            } else if (!isset($file["tmp_name"][0])) {
                $checkSuccess=false; $errLog.="|EDO CTA: NO TMP";
                $onloadScript .= "overlayMessage('<p>Archivo descargado no identificado.</p>','ERROR');ebyid('prov_receipt').focus();";
            } else {
                $fecha=date('Y').str_pad(date('n'),2,"0",STR_PAD_LEFT).str_pad(date('j'),2,"0",STR_PAD_LEFT);
                $prvEdoCta = $user->proveedor->rfc."-edocta-".$fecha.".pdf";
                $pfldarr["edocta"] = $prvEdoCta;
                $filepath = $_SERVER['DOCUMENT_ROOT']."cuentas/docs/";
                if (move_uploaded_file($file["tmp_name"], $filepath.$prvEdoCta)===false) {
                    $checkSuccess=false; $errLog.="|EDO CTA: NO MOVE";
                    $onloadScript .= "overlayMessage('<p>Error al descargar archivo en el servidor, consulte a su administrador.</p>','ERROR');ebyid('prov_receipt').focus();";
                } else {
                    exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdfinfo.exe\" \"$filepath$prvEdoCta\"", $output);
                    $saveLog.="|pdfinfo";
                    clog2("PATH:'$filepath$prvEdoCta', INFO: ".json_encode($output));
                    $pagecount=0;
                    foreach($output as $op) {
                        if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                            $pagecount = intval($matches[1]);
                            break;
                        }
                    }
                    if ($pagecount>0) $pfldarr["numpagEdoCta"]=$pagecount;
                    $saveProv=true;
                    $saveLog.="|accrcp";
                }
            }
        }
        if (!empty($_FILES["prov_opinion"]) && $_FILES["prov_opinion"]["size"]>0) {
            $file = $_FILES["prov_opinion"];
            if (!empty($file["error"]) && $file["error"]!==UPLOAD_ERR_OK) {
                $checkSuccess=false; $errLog.="|OP SAT UPLOAD ERR";
                switch($file["error"]) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errLog.=": INI SIZE";
                        $onloadScript .= "overlayMessage('<p>El archivo PDF excede el tamaño máximo permitido por el portal.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $errLog.=": FORM SIZE";
                        $onloadScript .= "overlayMessage('<p>El archivo PDF excede el tamaño máximo que soporta el navegador.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    case UPLOAD_ERR_PARTIAL:
                        $errLog.=": PARTIAL";
                        $onloadScript .= "overlayMessage('<p>El archivo PDF excede el tamaño máximo que soporta el navegador.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    case UPLOAD_ERR_NO_FILE:
                        $errLog.=": NO FILE";
                        $onloadScript .= "overlayMessage('<p>No se encontró el archivo a descargar, intente nuevamente.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errLog.=": NO TMP DIR";
                        $onloadScript .= "overlayMessage('<p>Temporalmente el portal tiene la descarga de archivos deshabilitada.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errLog.=": CANT WRITE";
                        $onloadScript .= "overlayMessage('<p>Temporalmente el portal no permite la descarga de archivos.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    case UPLOAD_ERR_EXTENSION:
                        $errLog.=": EXTENSION";
                        $onloadScript .= "overlayMessage('<p>Temporalmente el portal no soporta la descarga de archivos por extensión.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                    default:
                        $errLog.=": OTHER: $file[error]";
                        $onloadScript .= "overlayMessage('<p>Error desconocido durante la descarga del archivo.</p>','ERROR');ebyid('prov_opinion').focus();"; break;
                }
            } else if (!isset($file["type"][0])) {
                $checkSuccess=false; $errLog.="|OP SAT: NOTYPE";
                $onloadScript .= "overlayMessage('<p>Formato del archivo desconocido.</p>','ERROR');ebyid('prov_opinion').focus();";
            } else if ($file["type"]!=="application/pdf") {
                $checkSuccess=false; $errLog.="|OP SAT NO PDF: $file[type]";
                $onloadScript .= "overlayMessage('<p>Archivo inválido ($file[type]), debe ser PDF.</p>','ERROR');ebyid('prov_opinion').focus();";
            } else if (!isset($file["tmp_name"][0])) {
                $checkSuccess=false; $errLog.="|OP SAT: NO TMP";
                $onloadScript .= "overlayMessage('<p>Archivo descargado no identificado.</p>','ERROR');ebyid('prov_opinion').focus();";
            } else {
                $fecha=date('Y').str_pad(date('n'),2,"0",STR_PAD_LEFT).str_pad(date('j'),2,"0",STR_PAD_LEFT);
                $prvOpinion = $user->proveedor->rfc."-opisat-".$fecha.".pdf";
                $pfldarr["opinion"] = $prvOpinion;
                $filepath = $_SERVER['DOCUMENT_ROOT']."cuentas/docs/";
                if (move_uploaded_file($file["tmp_name"], $filepath.$prvOpinion)===false) {
                    $checkSuccess=false; $errLog.="|OP SAT: NO MOVE";
                    $onloadScript .= "overlayMessage('<p>Error al descargar archivo en el servidor, consulte a su administrador.</p>','ERROR');ebyid('prov_opinion').focus();";
                } else {
                    //if (isset($tmpVencimiento[0])) $pfldarr["venceopinion"]=$tmpVencimiento;
                    exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdfinfo.exe\" \"$filepath$prvOpinion\"", $output);
                    $saveLog.="|pdfinfo";
                    clog2("PATH:'$filepath$prvOpinion', INFO: ".json_encode($output));
                    $pagecount=0;
                    foreach($output as $op) {
                        if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                            $pagecount = intval($matches[1]);
                            break;
                        }
                    }
                    if ($pagecount>0) $pfldarr["numpagOpinion"]=$pagecount;
                    exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$filepath$prvOpinion\" -", $output);
                    $saveLog.="|pdftotext";
                    $dateLine="";
                    $autoReject=false;
                    foreach($output as $op) {
                        if (isset($dateLine[0])&&$autoReject) break; // Se encontraron ambas, dejar de buscar
                        if (!isset($dateLine[0])&&preg_match("/Revisi.+n practicada el d.+a (\d+) de (\w+) de (\d+), a las (\d+):(\d+) horas/",$op, $matches) === 1) {
                            $meses=["enero"=>"01","febrero"=>"02","marzo"=>"03","abril"=>"04","mayo"=>"05","junio"=>"06","julio"=>"07","agosto"=>"08","septiembre"=>"09","octubre"=>"10","noviembre"=>"11","diciembre"=>"12"];
                            if(isset($meses[$matches[2]])) {
                                $dateLine=$matches[3]."-".$meses[$matches[2]]."-".str_pad($matches[1],2,"0",STR_PAD_LEFT);
                            }
                        }
                        if(!$autoReject&&preg_match("/su situaci.+n fiscal no se encuentra al corriente/",$op, $matches) === 1) {
                            $autoReject=true;
                        }
                    }
                    if ($autoReject)
                        $pfldarr["cumplido"]="-2";
                    if (isset($dateLine[0])) {
                        $pfldarr["generaopinion"]=$dateLine;
                        $pfldarr["venceopinion"]=date('Y-m-d',strtotime($dateLine.' +90 days'));
                        $saveLog.="|{$dateLine}+90d=$pfldarr[venceopinion]";
                    }
                    $saveProv=true;
                    $saveLog.="|satrcp";
                }
            }
        }
        DBi::autocommit(FALSE);
        if ($checkSuccess && $saveUser) {
            require_once "clases/Usuarios.php";
            $usrObj = new Usuarios();
            if (!$usrObj->saveRecord($ufldarr)) {
                if (!empty(DBi::$errno)) {
                    $checkSuccess=false; $errLog.="|NO SAVE USR";
                    $onloadScript .= "overlayMessage('<p>Error al guardar correo electrónico, consulte a su administrador.</p>','ERROR');ebyid('user_email').focus();";
                } else {
                    $saveLog.="|usrnos";
                }
            } else {
                $user->email=$userEmail;
                $saveLog.="|usrsvd";
            }
        }
        if ($checkSuccess) {
            if ($user->proveedor->status==="actualizar")
                $pfldarr["status"]="activo";
            if ($saveProv) {
                if (isset($pfldarr["edocta"][0])) {
                    $pfldarr["verificado"]="0";
                    $saveLog.="|accpnd";
                }
                if (isset($pfldarr["opinion"][0])&&!isset($pfldarr["cumplido"])) {
                    $pfldarr["cumplido"]="0";
                    $saveLog.="|satpnd";
                }
            }
            require_once "clases/Proveedores.php";
            $prvObj = new Proveedores();
            if (!$saveProv) {
                $onloadScript .= "overlayMessage('<p>Es necesario que corrija sus datos.</p>','ERROR');";
            } else if (!$prvObj->saveRecord($pfldarr)) {
                if (!empty(DBi::$errno)) {
                    $checkSuccess=false; $errLog.="|NO SAVE PRV";
                    $onloadScript .= "overlayMessage('<p>Error al guardar datos de proveedor, consulte a su administrador.</p>','ERROR');";
                } else {
                    $saveLog.="|prvnos";
                }
            } else {
                $saveLog.="|prvsvd";
                $permiso=[];
                if(isset($pfldarr["edocta"])) {
                    if (!isset($ctavList)) $ctavList=["-1"=>"RECHAZADO","0"=>"PENDIENTE","1"=>"ACEPTADO"];
                    $prvRcptClss = " class=\"vAlignCenter hidden\"";
                    $prvRcptElem = "<INPUT type='hidden' id='prov_receipt_name' name='prov_receipt_name' value='$prvEdoCta'><A href='cuentas/docs/$prvEdoCta' target='archivopdf' id='prov_receipt_doc' class='vAlignCenter'><IMG src='imagenes/icons/pdf200.png' width='20' height='20' class='vAlignCenter'></A><IMG src='imagenes/icons/deleteIcon16.png' onclick='let dci=this.previousElementSibling;let pr=ebyid(\"prov_receipt\");if(clhas(pr,\"hidden\")){clrem(pr,\"hidden\");this.src=\"imagenes/icons/backArrow.png\";this.title=\"Restaurar Documento Anterior\";cladd(dci,\"hidden\");}else{cladd(pr,\"hidden\");this.src=\"imagenes/icons/deleteIcon16.png\";this.title=\"Eliminar Documento\";clrem(dci,\"hidden\");}' class='vAlignCenter btnFX pointer'>";
                    if (isset($ctavList[$pfldarr["verificado"]])) {
                        $ctaText=$ctavList[$pfldarr["verificado"]];
                        if ($ctaText==="PENDIENTE") $ctaText="EN REVISI&Oacute;N";
                        $prvRcptElem.=" <span class=\"fontSmall boldValue pointer ".$ctaclass[$pfldarr["verificado"]]."\" onclick=\"this.previousElementSibling.click();\">$ctaText</span>";
                    }
                    // ENVIAR CORREO A VALIDA CUENTA
                    $permiso[]="Valida Bancarias";
                }
                if(isset($pfldarr["opinion"])) {
                    if (!isset($opifList)) $opifList=["-2"=>"RECHAZADO","-1"=>"VENCIDO","0"=>"PENDIENTE","1"=>"ACEPTADO"];
                    $prvOpiClss = " class=\"vAlignCenter hidden\"";
                    $prvOpiElem = "<INPUT type='hidden' id='prov_opinion_name' name?'prov_opinion_name' value='$prvOpinion'><A href='cuentas/docs/$prvOpinion' target='archivopdf' id='prov_opinion_doc' class='vAlignCenter'><IMG src='imagenes/icons/pdf200.png' width='20' height='20' class='vAlignCenter'></A><IMG src='imagenes/icons/deleteIcon16.png' onclick='let dci=this.previousElementSibling;let po=ebyid(\"prov_opinion\");if(clhas(po,\"hidden\")){clrem(po,\"hidden\");this.src=\"imagenes/icons/backArrow.png\";this.title=\"Restaurar Documento Anterior\";cladd(dci,\"hidden\");}else{cladd(po,\"hidden\");this.src=\"imagenes/icons/deleteIcon16.png\";this.title=\"Eliminar Documento\";clrem(dci,\"hidden\");}' class='vAlignCenter btnFX pointer'>";
                    if (isset($opifList[$pfldarr["cumplido"]])) {
                        $opiText=$opifList[$pfldarr["cumplido"]];
                        if ($opiText==="PENDIENTE") $opiText="EN REVISI&Oacute;N";
                        $prvRcptElem.=" <span class=\"fontSmall boldValue pointer ".$opiclass[$pfldarr["cumplido"]]."\" onclick=\"this.previousElementSibling.click();\">$opiText ".($pfldarr["venceopinion"]??"sin cálculo de vencimiento")."</span>";
                    }
                    // ENVIAR CORREO A VALIDA OPINION
                    $permiso[]="Valida Opinion";
                }
                if (!empty($permiso)) {
                    if (!isset($usrObj)) {
                        require_once "clases/Usuarios.php";
                        $usrObj = new Usuarios();
                    }
                    global $query;
                    $emailData=$usrObj->getDataByProfileNames($permiso,"email address,persona name","email is not null group by email,persona");
                    $emailQuery=$query;
                    //require_once "clases/Correo.php";
                    //$mail=new Correo();
                    //$mail->restart();
                    if (isset($emailData[0]["address"][0])) {
                        /*$to=[];
                        foreach ($emailData as $elem) {
                            if(isset($elem["email"][0])) $to[]=["address"=>$elem["email"],"name"=>$elem["persona"]];//$mail->addAddress($elem["email"],$elem["persona"]??"");
                        }*/
                        $asunto="Portal Invoice Check requiere validar documentos";
                        //$mail->setSubject($asunto);
                        $mensaje="<html><body>El proveedor {$user->persona} ({$username}) ha actualizado sus documentos y requiere sean verificados.<br>Por favor ingrese al portal para verificar los documentos.</body></html>";
                        //$mail->setBody($mensaje);
                        global $logObj;
                        if (!isset($logObj)) {
                            require_once "clases/Logs.php";
                            $logObj=new Logs();
                        }
                        if (sendMail($asunto,$mensaje,null,$emailData,null,null,["domain"=>"default"])) {
                            $logObj->agrega($systemUserId, "FaltaBanco", "Correo Enviado: {$user->persona} ({$username})");
                        } else {
                            $errLog.="|NO SENT";
                            $logObj->agrega($systemUserId, "FaltaBanco", "Error en envío: {$user->persona} ({$username})");
                        }
                        /*try {
                            $mail->send();
                        } catch (Exception $ex) {
                            //$checkSuccess=false;
                            //$saveProv=false;
                            // $saveUser=false; // no se necesita
                            errlog("Error al enviar correo por datos validados","mail",["username"=>$username,"contenido"=>"El proveedor ha actualizado sus documentos y requiere sean verificados.","error"=>getErrorData($ex),"permiso"=>$permiso,"pfldarr"=>$pfldarr,"emailQuery"=>$emailQuery,"emailData"=>$emailData]);
                            //$onloadScript .= "overlayMessage('<p>Error al mandar correo para validar datos. Intente nuevamente por favor.</p>','ERROR');";
                        }*/
                    } else {
                        $checkSuccess=false;$errLog.="|NO ADDRESSES";
                        $saveProv=false;
                        doclog("No se encontraron datos de usuarios que validan cambios","mail",["username"=>$username,"contenido"=>"El proveedor ha actualizado sus documentos y requiere sean verificados.","permiso"=>$permiso,"pfldarr"=>$pfldarr,"emailQuery"=>$emailQuery,"emailData"=>$emailData]);
                        $onloadScript .= "overlayMessage('<p>Error al mandar correo para validar datos. Intente nuevamente por favor.</p>','ERROR');";
                    }
                    // TODO: Generar Token, guardarlo en BD con datos de proveedor y archivos agregados (edocta y/o opinion)
                    // TODO: Correo con liga a Invoice con botones para validar edocta y opinion (posiblemente se requiera un token diferente para opinion, para permitir que se presione un boton de edocta y otro de opinion)
                    // TODO: En index verificar GET de token y checar si existe en BD y dependiendo de los demas datos saber que modificar en BD
                }
                // ToDo: Guardar en Proceso!
                if ($saveProv) {
                    $user->proveedor->banco=$provBank;
                    $user->proveedor->rfcbanco=$bankRFC;
                    $user->proveedor->cuenta=$provAccount;
                    $user->proveedor->edocta=$prvEdoCta;
                }
                if ($user->proveedor->status==="actualizar")
                    $user->proveedor->status="activo";
            }
        }
        if ($checkSuccess) {
// Si no hay error mostrar mensaje de guardado exitoso y al presionar ok redirigir a inicio para que se refresque el menu.
            if ($saveUser||$saveProv)
                $onloadScript .= "overlayMessage('<p class=\"fontBig\">Sus datos se han actualizado. Gracias por su colaboración.<br>Para futuras modificaciones a sus datos bancarios contacte a su asesor de compras.</p>','ACTUALIZACION EXITOSA');";
            else $onloadScript .= "overlayMessage('<p class=\"fontBig\">A verificado sus datos y se mantienen sin cambios. Gracias por su colaboración.<br>Para futuras modificaciones a sus datos bancarios contacte a su asesor de compras.</p>','DATOS VERIFICADOS');";
            $urlAction="login";
            $hasScriptAction=FALSE;

            $procDetalle = "FaltaBanco".($hasUser?" USR".json_encode($ufldarr):"")." PRV".json_encode($pfldarr);
            $saveLog.="\n".$procDetalle;
            require_once "clases/Proceso.php";
            $prcObj = new Proceso();
            if ($prcObj->cambioProveedor($user->proveedor->id, $user->proveedor->status, $username, $procDetalle)) {
                echo "<!-- PROCESO CAMBIO PROVEEDOR ".$user->proveedor->id.", ".$username.", ".$user->proveedor->status." -->";
                $saveLog.="\nPROCESO id=".$user->proveedor->id.", nombre=".$username.", status=".$user->proveedor->status;
            } else {
                $saveLog.="\nPROCESO ERROR. LOG=".$prcObj->log;
                echo "<!-- ".$prcObj->log." -->";
                if (isset($prcObj->errors[0])) {
                    echo "<!-- ".json_encode($prcObj->errors)." -->";
                    $saveLog.="\nPROCESO ERRLIST:";
                    foreach ($prcObj->errors as $pIdx => $pErr) {
                        $pNum=$pIdx+1;
                        $saveLog.="\n * $pNum : $pErr";
                    }
                }
            }
            $saveLog.="|SUCCESS!";
            DBi::commit();
        } else {
            $errLog.="|UNSUCCESSFUL";
            DBi::rollback();
        }
        DBi::autocommit(FALSE);
    }
    //if (isset($saveLog[7])) doclog($saveLog);
    if (isset($errLog[17])) errlog($errLog);
} // else echo "<!-- RFC = {$user->proveedor->rfc} -->";


$dueDay = date('d/m/Y',strtotime('+90 days',strtotime(str_replace('/', '-', $_now["DMY"]))));

clog1seq(-1);
clog2end("configuracion.faltabanco");
