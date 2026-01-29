<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Archivos.php";
define("FDATC", ["CSF"=>"CONSTANCIA DE SITUACI","OCOF"=>"n del cumplimiento de obligaciones fiscales","CO"=>"Cadena Original", "CST"=>"Estatus en el padr"]);
define("SENTIDOP", ["P"=>"POSITIVO", "N"=>"NEGATIVO"]);

if (!hasUser()) {
    if (isset($_POST["accion"])) {
        echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);ekil(this);\">";
    }
    if (isset($_POST["action"])) {
        echo json_encode(["result"=>"refresh","action"=>"refresh"]);
    }
} else if (isset($_POST["action"])) {
    procesaAccion($_POST["action"]);
} else if (!_esSistemasX) {
} else if (isset($_POST["accion"])) {
    procesaAccion($_POST["accion"]);
} else if (isset($_GET["accion"])) {
    echo "Acci&oacute;n: $_GET[accion]<br>\n";
    switch($_GET["accion"]) {
        case "renombra":
            $ruta = $_GET["ruta"];
            echo "Ruta: $ruta<br>\n";
            echo renombraXML($ruta);
            break;
        case "call":
            $functionName=$_GET["function"];
            $parameters=[];
            switch($functionName) {
                case "url_exists":
                    $parameters["url"]=$_GET["url"];
                break;
                default: $functionName=false;
            }
            if ($functionName!==false) {
                echo "Function: $functionName ( ".str_replace("\\/", "/", json_encode($parameters))." )<br>\n";
                $result = $functionName($parameters);
                echo Archivos::processResult($result);
            }
            break;
        case "prueba":
            echo "GET: ".json_encode($_GET)."<br>\n";
            $_POST=["factIds"=>245178,245201,245226];
            echo "POST: ".json_encode($_POST);
            procesaAccion("pdfmerge");
            break;
        case "merge":
            procesaAccion("pdfmerge2");
    }
} else doclog("ARCHIVO NO ACTION","error",["request"=>$filterArray($_REQUEST,["target"])]);

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

// http://localhost:81/invoice/consultas/Archivos.php?accion=call&function=url_exists&url=http://sti.dyndns-ip.com/glama/empresas/APS7503114Q2/SAN791101NV9_15163.xml
// accion=call. Valid Functions
function url_exists($params) {
    $result = @get_headers($params["url"]);
    if (preg_match("|200|", $result[0])) {
        return TRUE;
    } else {
        return FALSE;
    }
}
// END accion=call

function procesaAccion($accion) {
    global $usrObj;
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"accion"=>$accion]+$_POST;
    $path = $_SERVER['DOCUMENT_ROOT'];
    $satPath = "descargas/recibidos/";
    $isfPath = "descargas/invoicesafe/";
    $ichPath = "archivos/";
    $mes = str_pad($_POST["mes"]??"00",2,"0",STR_PAD_LEFT);
    $anio = $_POST["anio"]??"0000";
    $empresa = $_POST["empresa"]??"empresa";
    $mesPath = $empresa."/$anio/$mes/";
    doclog("INI procesaAccion '{$accion}'","archivo");
    switch ($accion) {
        case "uuidpdf":
            $result=getUUIDFromPDF();
            echo json_encode($result);
            return;
        case "descargaZip":
            $premsg = descarga($satPath, "zip");
            break;
        case "descargaTar":
            $premsg = descarga($isfPath, "tar");
            break;
        case "unzip":
            $premsg = extrae($satPath, "zip");
            break;
        case "untar":
            $premsg = extrae($isfPath, "tar");
            break;
        case "borraArchivo":
            $archivo = $_POST["archivoABorrar"];
            $zona = $_POST["nombreZona"];
            $delPath = ($zona=="SAT"?$satPath:($zona=="IS"?$isfPath:($zona=="Arch"?$ichPath:false)));
            if(@unlink($path.$archivo)) $premsg = "Archivo $archivo borrado";
            else $premsg = "No pudo borrarse el archivo $archivo";
            if(!empty($delPath)) {
                $list = Archivos::dirlist($delPath.$mesPath);
                $list = Archivos::appendXMLInfo($list, $delPath.$mesPath, $zona);
            }
            break;
        case "ordenaSAT":
            $premsg = organizaXMLs($satPath);
            break;
        case "ordenaIS":
            $premsg = organizaEmpresas($isfPath);
            break;
        case "actualizaSAT":
            ini_set('max_execution_time', 180);
            $list = Archivos::dirlist($satPath.$mesPath);
            $list = Archivos::appendXMLInfo($list, $satPath.$mesPath, "SAT");
            $num = count($list);
            $premsg = "Facturas Recibidas del SAT: $num<br>";
            break;
        case "actualizaIS":
            $list = Archivos::dirlist($isfPath.$mesPath);
            $list = Archivos::appendXMLInfo($list, $isfPath.$mesPath, "IS");
            $num = count($list);
            $premsg = "Facturas de Invoice Safe: $num<br>";
            break;
        case "actualizaArch":
            $list = Archivos::dirlist($ichPath.$mesPath);
            $list = Archivos::appendXMLInfo($list, $ichPath.$mesPath, "Arch");
            $num = count($list);
            $premsg = "Facturas de Invoice Check: $num<br>";
            break;
        case "actualizaDif":
            ini_set('max_execution_time', 120);
            $list = Archivos::comparaListas($mesPath);
            $list = Archivos::appendXMLInfo($list, $satPath.$mesPath, "Dif", FALSE);
            $num = count($list);
            $premsg = "Facturas en SAT pero no en InvoiceCheck ni InvoiceSafe: $num<br>";
            break;
        case "rompeSello":
            rompeSello();
            break;
        case "recuperaSello":
            recuperaSello();
            break;
        case "updateTaxStatusProof": // Actualiza Constancia de Situacion Fiscal
            updateTaxStatusProof();
            break;
        case "appendBackgroundDocs":
            addBGDocs();
            break;
        case "removeBackgroundPages":
            removeBGDocs();
            break;
        case "extractData":
            //echo "POST: ".json_encode($_POST)."<br>";
            //echo "FILES: ".json_encode($_FILES);
            $fpath="C:/InvoiceCheckShare/invoiceDocs/extraerdatos/";
            $files=getFixedFileArray($_FILES["file"]);
            $keys=["folio","uuid","fecha","subtotal","descuento","total","totalimpuestostrasladados","totalimpuestosretenidos","moneda","tipo_comprobante","conceptos"]; // ,"emisor","receptor"
            // var cs=["conceptos"]; // ,"emisor","receptor"
            // var ccc=["claveprodserv","claveunidad","unidad","descripcion","valorunitario","importe"];
            //echo "KEYS: ".implode(" | ", $keys)."\n";
            require_once "clases/CFDI.php";
            $result=["data"=>[],"log"=>"","cfdilog"=>""];
            foreach ($files as $idx => $fl) {
                $result["log"].="$idx) FILE($fl[name])";
                $result["data"][$idx]=[];
                if ($fl["error"]) {
                    $result["log"].=" ERROR: $fl[error]\n";
                    $result["data"][$idx]["error"]=["loadError"=>$fl["error"]];
                    continue;
                }
                $tmpname=$fl["tmp_name"];
                if (!file_exists($tmpname)) {
                    $result["log"].=" ERROR: No existe tmp_name\n";
                    $result["data"][$idx]["error"]=["nofile"=>"No se encontró el archivo descargado"];
                    continue;
                }
                $absname=$fpath.$fl["name"];
                if (move_uploaded_file($tmpname, $absname)===false) {
                    $result["log"].=" ERROR Move_Uploaded_File\n";
                    $result["data"][$idx]["error"]=["nofile"=>"No se pudo obtener el archivo"];
                    continue;
                }
                $cfdiObj=CFDI::newInstanceByLocalName($absname);
                if ($cfdiObj==null) {
                    $result["data"][$idx]["error"]=CFDI::getLastError(); // errorMessage,errorStack,enough,log
                    $result["log"].=" ERROR al crear objeto CFDI\n";
                    continue;
                }
                $result["log"].=" K[";
                foreach ($keys as $ki => $kval) {
                    if ($ki>0) $result["log"].=",";
                    $result["log"].=$kval;
                    $vval=$cfdiObj->get($kval);
                    if (isset($vval["@claveprodserv"])) {
                        $result["log"].="[]";
                        $vval=[$vval];
                    }
                    if (isset($vval[0])) {
                        //$aux="";
                        if (is_scalar($vval) && preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/", "$vval")) $vval=substr_replace("$vval", " ",10,1);
                        //else $aux="x";
                        $result["data"][$idx][$kval]=$vval;
                        if (is_scalar($vval)) $result["log"].="='$vval'";//.$aux;
                        else $result["log"].="=".json_encode($vval);
                    } else {
                        //$result["data"][$idx][$kval]=".";
                        $result["log"].=" is null";
                        if ($kval==="conceptos" && $vval!==null && $vval!=="" && $vval!==0 && $vval!==false) $result["log"].=" <".json_encode($vval).">";
                    }
                }
                $result["log"].="]\n";
                $result["cfdilog"].="$fl[name]: ".$cfdiObj->getLog()."\n";
            }
            if (isset($result["data"][0])) $result["result"]="success";
            else {
                $result["result"]="empty";
                $result["message"]="No se generaron registros";
            }
            echo json_encode($result);
            break;
        case "getDoc":
            $tipo=$_POST["type"];
            if(!isset($tipo[0])) { echoJSDoc("error", "No se especificó tipo de documento", null, $baseData+["line"=>__LINE__], "error"); return; }
            if ($tipo!=="ea") { echoJSDoc("error", "Debe indicar un tipo de documento válido", null, $baseData+["line"=>__LINE__], "error"); return; }
            $invId=$_POST["id"];
            if(!isset($invId[0])) { echoJSDoc("error", "No se recibió identificador de CFDI", null, $baseData+["line"=>__LINE__], "error"); return; }
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $invData=$invObj->getData("id=$invId");
            if (!isset($invData[0]["id"][0])) {
                global $query;
                echoJSDoc("error", "No existe la factura solicitada", null, $baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors], "error");
                return;
            }
            $invData=$invData[0];
            $eapath=$invData["ubicacion"];
            $eacp=trim(str_replace("-","",$invData["codigoProveedor"]??""));
            $eafecha=substr(str_replace("-","",$invData["fechaFactura"]??""),2,6); // substr(trim(str_replace("-","", $invData["fechaFactura"])),2,6);
            $eafolio=$invData["folio"]??"";
            if (!isset($eafolio[0])) $eafolio=$invData["uuid"]??"";
            if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
            $eaFileExists=false;
            $ealist=[];
            if (isset($eacp[0])&&isset($eafolio[0])) {
                $eaname="EA_{$eacp}_{$eafolio}_{$eafecha}";
                $ealist[]=$eaname;
                $eaFileExists=file_exists($path.$eapath.$eaname.".pdf");
            }
            if (!$eaFileExists) {
                $tc=strtolower($invData["tipoComprobante"]??"");
                if (isset($tc[1])) $tc=$tc[0];
                if ($tc==="i" && isset($invData["serie"][0]) && isset($invData["folio"][0])) {
                    $eafolio2=$invData["serie"].$invData["folio"];
                    if (isset($eafolio2[10])) $eafolio2=substr($eafolio2, -10);
                    if ($eafolio!==$eafolio2) {
                        $eaname="EA_{$eacp}_{$eafolio2}_{$eafecha}";
                        $ealist[]=$eaname;
                        $eaFileExists=file_exists($path.$eapath.$eaname.".pdf");
                    }
                }
                if ($tc==="i" && !$eaFileExists) {
                    $eafolio3=$invData["nombreInterno"];
                    if (isset($eafolio3[10])) $eafolio3=substr($eafolio3, -10);
                    if (isset($eafolio3[0]) && $eafolio!==$eafolio3 && $eafolio2!==$eafolio3) {
                        $eaname="EA_{$eacp}_{$eafolio3}_{$eafecha}";
                        $ealist[]=$eaname;
                        $eaFileExists=file_exists($path.$eapath.$eaname.".pdf");
                    }
                }
                if (!$eaFileExists && $tc==="e") {
                    $eaname="EA_{$eacp}_NC_{$eafolio}_{$eafecha}";
                    $ealist[]=$eaname;
                    $eaFileExists=file_exists($path.$eapath.$eaname.".pdf");
                }
                if (!$eaFileExists && $tc==="p") {
                    $eaname="EA_{$eacp}_RP_{$eafolio}_{$eafecha}";
                    $ealist[]=$eaname;
                    $eaFileExists=file_exists($path.$eapath.$eaname.".pdf");
                }
                if (!$eaFileExists) { echoJSDoc("error", "No se encontró el archivo EA", null, $baseData+["line"=>__LINE__,"ealist"=>$ealist,"ea"=>$invData["ea"]], "error"); return; }
            }
            echoJSDoc("success", "Archivo EA encontrado", null, ["eapath"=>$eapath,"eaname"=>$eaname]);
            break; // return;
        case "addDoc":
            $esSistemas = validaPerfil(["Sistemas","Administrador"]);
            $esProveedor = validaPerfil("Proveedor");
            $esCompras = validaPerfil("Compras");
            $tipo=$_POST["type"];
            if(!isset($tipo[0])) { echoJSDoc("error", "No se especificó tipo de documento", null, $baseData+["line"=>__LINE__], "error"); return; }
            if ($tipo!=="cfdi"&&$tipo!=="ea") { echoJSDoc("error", "El tipo de documento no es válido", null, $baseData+["line"=>__LINE__], "error"); return; }
            $invId=$_POST["id"];
            if(!isset($invId[0])) { echoJSDoc("error", "No se recibió identificador de CFDI", null, $baseData+["line"=>__LINE__], "error"); return; }
            $ffile=$_FILES["file"];
            if(!isset($ffile)) { echoJSDoc("error", "No se recibió archivo a anexar", null, $baseData+["line"=>__LINE__,"eafiles"=>$_FILES], "error"); return; }
            $ferror = $ffile["error"];
            $ftype = $ffile["type"];
            $fname = $ffile["name"];
            $fsize = $ffile["size"];
            if ($fsize<100) { echoJSDoc("error", "El archivo está vacío", null, $baseData+["line"=>__LINE__,"filedata"=>$_FILES], "error"); return; }
            if ($ftype!=="application/pdf") { echoJSDoc("error", "El archivo debe tener formato PDF", null, $baseData+["line"=>__LINE__,"filedata"=>$ffile], "error"); return; }
            if ($ferror!=0) {
                $fmsg=fileCodeToMessage($ferror,["filename"=>$fname]);
                $hasMsg=isset($fmsg[0]);
                echoJSDoc("error", "Ocurrió un error en la descarga del archivo".($hasMsg?": $fmsg":""), null, $baseData+["line"=>__LINE__,"files"=>$_FILES,"errmsg"=>$hasMsg?$fmsg:"Codigo de error desconocido"], "error");
                return;
            } // cmorysan3: Ocurrió un error en la descarga del archivo. {file:"C:\Apache24\htdocs\invoice\consultas\Archivos.php", function:"procesaAccion", accion:"addDoc", line:"301", errmsg:"La carga de archivo excede la directiva upload_max_filesize en php.ini", action:"addDoc", id:"268296", type:"ea", hasProgress:"false"}
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $invData=$invObj->getData("id=$invId");
            if (!isset($invData[0]["id"][0])) {
                global $query;
                echoJSDoc("error", "No existe el CFDI $invId", null, $baseData+["line"=>__LINE__,"query"=>$query,"errors"=>DBi::$errors], "error");
                return;
            }
            $invData=$invData[0];
            $statusn=$invData["statusn"]??null;
            $tc=$invData["tipoComprobante"]??"";
            $tcx=""; $tcl=""; $tcg="";
            if ($tc==="i") { $tcl="La"; $tcx="Factura"; $tcg="a"; }
            else if ($tc==="e") { $tcl="La"; $tcx="Nota de Crédito"; $tcg="a"; }
            else if ($tc==="p") { $tcl="El"; $tcx="Complemento de Pago"; $tcg="o"; }
            if (!isset($tcx[0])) { echoJSDoc("error", "El tipo de comprobante no es válido", null, $baseData+["line"=>__LINE__,"tc"=>$tc], "error"); return; }
            if ($statusn===null || $statusn==="") { echoJSDoc("error", "$tcl $tcx no está propiamente dad{$tcg} de alta", null, $baseData+["line"=>__LINE__], "error"); return; }
            if ($esProveedor && $statusn!=="0") { echoJSDoc("error", "Sólo puede agregar documentos con status PENDIENTE", null, $baseData+["line"=>__LINE__], "error"); return; }
            if (!$esCompras && !$esSistemas && !$esProveedor) { echoJSDoc("error", "No tiene permiso para agregar documentos", null, $baseData+["line"=>__LINE__], "error"); return; }
            $sysPath=$_SERVER["DOCUMENT_ROOT"];
            $ubicacion=$invData["ubicacion"]??"";
            $folio=trim($invData["folio"]??"");
            if (!isset($folio[0])) $folio=trim($invData["uuid"]??"");
            if (isset($folio[10])) $folio=substr($folio, -10);
            if ($tipo==="cfdi") {
                $pdf=$invData["nombreInternoPDF"]??"";
                DBi::autocommit(FALSE);
                if (!isset($pdf[0])) {
                    $prvRfc=$_POST["rfc"]??"";
                    if (!isset($prvRfc[0])) {
                        global $prvObj;
                        if (!isset($prvObj)) {
                            require_once "clases/Proveedores.php";
                            $prvObj=new Proveedores();
                        }
                        $prvData=$prvObj->getData("codigo='$invData[codigoProveedor]'");
                        if (!isset($prvData[0]["id"][0])) {
                            global $query;
                            $errors=DBi::$errors;
                            DBi::autocommit(TRUE);
                            echoJSDoc("error", "No existe el proveedor requerido para dar nombre al archivo pdf", null, $baseData+["line"=>__LINE__,"query"=>$query,"errors"=>$errors], "error");
                            return;
                        }
                        $prvData=$prvData[0];
                        $prvRfc=$prvData["rfc"];
                    }
                    $tc=$invData["tipoComprobante"];
                    if ($tc==="e"||$tc==="egreso") $pdf.="NC_";
                    else if ($tc==="p"||$tc==="pago") $pdf.="RP_";
                    else if ($tc==="t"||$tc==="traslado") $pdf.="TR_";
                    $pdf.=$folio.$prvRfc;
                    $isValidPDF=$invObj->saveRecord(["id"=>$invid,"nombreInternoPDF"=>$pdf]);
                } else $isValidPDF=true;
                if (!$isValidPDF) {
                    global $query;
                    $errors=DBi::$errors;
                    DBi::rollback();
                    DBi::autocommit(TRUE);
                    echoJSDoc("error", "No fue posible agregar el CFDI-PDF", null, $baseData+["line"=>__LINE__,"docToAdd"=>$ubicacion.$pdf.".pdf","query"=>$query,"errors"=>$errors], "error");
                    return;
                }
                // ToDo: ubica archivo
                $topPath=$sysPath.$ubicacion.$pdf;
                // si existe renombrarlo
                if (file_exists($topPath.".pdf")) {
                    rename($topPath.".pdf", $topPath."_OLD.pdf");
                    sleep(3);
                }
                if (!move_uploaded_file($ffile["tmp_name"], $topPath.".pdf")) {
                    DBi::rollback();
                    DBi::autocommit(TRUE);
                    echoJSDoc("error", "No se pudo cargar y asignar el archivo PDF", null, $baseData+["line"=>__LINE__,"sysPath"=>$sysPath,"moveError"=>error_get_last(),"filePath"=>$ubicacion.$pdf.".pdf"], "error");
                    return;
                }
                doclog("REASIGNAR PDF", "docs", $baseData+["line"=>__LINE__,"pdf"=>$pdf]);
                global $firObj;
                if(!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"cfdi", "idReferencia"=>$invId, "accion"=>"agrega", "motivo"=>$_POST["motivo"]??$pdf]);
                DBi::commit();
                DBi::autocommit(TRUE);
                echoJSDoc("success", "CFDI-PDF Agregado", null, ["pdf"=>$pdf],"docs");
                return;
            }
            // ToDo: Lo mismo para ea
            $ea=$invData["ea"]??"";
            DBi::autocommit(FALSE);
            if ($ea!=="1" && !$invObj->saveRecord(["id"=>$invId,"ea"=>"1"])) {
                global $query;
                $errors=DBi::$errors;
                DBi::rollback();
                DBi::autocommit(TRUE);
                echoJSDoc("error", "No fue posible agregar Entrada de Almacén", null, $baseData+["line"=>__LINE__,"query"=>$query,"errors"=>$errors,"eafile"=>$ffile], "error");
                return;
            }
            $cp=trim(str_replace("-", "", $invData["codigoProveedor"]));
            $fecha=substr(trim(str_replace("-","", $invData["fechaFactura"])),2,6);
            $eaPath=$ubicacion."EA_{$cp}_{$folio}_{$fecha}";
            $absName=$sysPath.$eaPath.".pdf";
            if (file_exists($absName)) {
                rename($absName, $sysPath.$eaPath.date("_YmdHis", filemtime($absName)).".pdf");
                sleep(3);
            }
            if (!move_uploaded_file($ffile["tmp_name"], $absName)) {
                global $query;
                DBi::rollback();
                DBi::autocommit(TRUE);
                echoJSDoc("error", "No se pudo agregar Entrada de Almacen", null, $baseData+["line"=>__LINE__,"sysPath"=>$sysPath,"eaPath"=>$eaPath,"eafile"=>$ffile,"moveError"=>error_get_last()], "error");
                return;
            }
            global $firObj;
            if (!isset($firObj)) {
                require_once "clases/Firmas.php";
                $firObj=new Firmas();
            }
            $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"ea", "idReferencia"=>$invId, "accion"=>"agrega", "motivo"=>$eaPath]);
            DBi::commit();
            DBi::autocommit(TRUE);
            echoJSDoc("success", "Entrada de Almacén Agregada", null, ["ea"=>$eaPath], "docs");
            break; // return;
        case "repDoc":
            // sistemas, puede reemplazar documento en cualquier estado, si es que ya existe el archivo
            // empleados, pueden reemplazar documento si no hay contra recibo o si no ha sido autorizado
            // proveedores, pueden reemplazar documento si no ha sido aceptado
            // por el momento solo hay 2 tipos de documentos reemplazables, pdf y ea (entrada de almacen)
            // se debe proporcionar el id de factura a reemplazar
            $tipo = $_POST["type"]??"";
            if ($tipo!=="cfdi" && $tipo!=="ea") { echoJSDoc("error", "El tipo de documento no es válido para eliminarlo o reemplazarlo", null, $baseData+["line"=>__LINE__], "error"); return; }
            if (!isset($_FILES["file"])) { echoJSDoc("error", "Debe agregar un archivo para reemplazar el antiguo", null, $baseData+["line"=>__LINE__], "error"); return; }
            $ffile=$_FILES["file"];
            if ($ffile["error"]>0) {
                $fileError=fileCodeToMessage($ffile["error"],["filename"=>$ffile["name"]]);
                if (isset($fileError)) echoJSDoc("error", $fileError, null, $baseData+["line"=>__LINE__,"user"=>getUser()->nombre], "error");
                else echoJSDoc("error", "Error en el archivo", null, $baseData+["line"=>__LINE__,"user"=>getUser()->nombre,"file"=>$ffile], "error");
                return;
            }
            $invId = "".($_POST["fId"]??"");
            if (!isset($invId[0])) {
                $ordId="".($_POST["oId"]??"");
                if (!isset($ordId[0])) { echoJSDoc("error", "No se reconoce el documento a reemplazar", null, $baseData+["line"=>__LINE__], "error"); return; }
                global $ordObj;
                if (!isset($ordObj)) {
                    require_once "clases/OrdenesCompra.php";
                    $ordObj=new OrdenesCompra();
                }
                $ordData=$ordObj->getData("id=$ordId");
                if (!isset($ordData[0]["id"][0])) {
                    global $query;
                    echoJSDoc("error", "No existe la Orden de Compra", null, $baseData+["line"=>__LINE__,"query"=>$query], "error");
                    return;
                }
                $ordData=$ordData[0];
                $upath=$path.$ordData["rutaArchivo"];
                $tmplog="REPDOC OC: tipo='{$tipo}'";
                if (!isset($tipo[0]) || $tipo==="cfdi") {
                    $name=$ordData["nombreArchivo"]??"";
                    if (!isset($name[0])) { echoJSDoc("error", "Datos incompletos para generar documento PDF", null, $baseData+["line"=>__LINE__,"query"=>$query,"data"=>$ordData], "error"); return; }
                } else { echoJSDoc("error", "No existen las Entradas de Almacen por Orden de Compra", null, $baseData+["line"=>__LINE__], "error"); return; }
                $filename=$upath.$name.".pdf";
                if (!file_exists($filename)) { echoJSDoc("error", "No existe el documento a reemplazar", null, $baseData+["line"=>__LINE__,"ubicacion"=>$upath,"nombre"=>$name.".pdf","log"=>$tmplog], "error"); return; }
                $dt = new DateTime();
                if (!rename($filename, $upath.$name.$dt->format("ymdHis").".pdf")) { echoJSDoc("error", "El documento existente no se puede reemplazar", null, $baseData+["line"=>__LINE__,"ubicacion"=>$upath,"moveError"=>error_get_last(),"nombre"=>$name.".pdf"], "error"); return; }
                sleep(3);
                if (move_uploaded_file($ffile["tmp_name"], $filename)===false) { echoJSDoc("error", "El archivo no se pudo cargar", null, $baseData+["line"=>__LINE__,"moveError"=>error_get_last(),"filename"=>$filename], "error"); return; }
                global $firObj;
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"orden", "idReferencia"=>$ordId, "accion"=>"reemplaza", "motivo"=>$upath.$name]);
                echoJSDoc("success", "Archivo reemplazado satisfactoriamente", null, ["orden"=>$filename],"docs");
                return;
            }
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $invData=$invObj->getData("id=$invId");
            if (!isset($invData[0]["id"][0])) {
                global $query;
                echoJSDoc("error", "No existe el CFDI $invId", null, $baseData+["line"=>__LINE__,"query"=>$query,"POST"=>$_POST], "error");
                return;
            }
            $invData=$invData[0];
            $upath=$path.$invData["ubicacion"];
            $tmplog="REPDOC INV: tipo='{$tipo}'";
            if ($tipo==="cfdi") {
                $name=$invData["nombreInternoPDF"]??"";
                if (!isset($name[0])) { echoJSDoc("error", "Datos incompletos para generar documento CFDI-PDF", null, $baseData+["line"=>__LINE__,"query"=>$query,"data"=>$invData,"POST"=>$_POST], "error"); return; }
            } else { // $tipo==="ea"
                $eacp=trim(str_replace("-","",$invData["codigoProveedor"]??""));
                $tmplog.=",eacp='{$eacp}'";
                $eafolio=$invData["folio"]??"";
                $easerie=$invData["serie"]??"";
                if (isset($easerie[0])&&isset($eafolio[0])) {
                    $eafolio2=$easerie.$eafolio;
                    if (isset($eafolio2[10])) $eafolio2=substr($eafolio2, -10);
                    if ($eafolio==$eafolio2) $eafolio2=null;
                }
                if (!isset($eafolio[0])) $eafolio=$invData["uuid"]??"";
                if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
                $tmplog.=",eafolio='{$eafolio}'";
                $eafecha=substr(str_replace("-","",$invData["fechaFactura"]??""),2,6);
                $tmplog.=",eafecha='{$eafecha}'";
                if (isset($eacp[0])&&isset($eafolio[0])) {
                    $name="EA_{$eacp}_{$eafolio}_{$eafecha}";
                    $tipoComprobante=strtoupper($invData["tipoComprobante"]??"");
                    $tmplog.=",tipoComprobante='{$tipoComprobante}'";
                    $eaFileExists=file_exists($upath.$name.".pdf");
                    if (!$eaFileExists) {
                        $tmplog.="|EA1DOESNTEXIST: $name";
                        if ($tipoComprobante==="I") {
                            if (isset($eafolio2[0])) {
                                $name="EA_{$eacp}_{$eafolio2}_{$eafecha}";
                                $tmplog.=",tc='{$tipoComprobante}',fixdname='{$name}'";
                                $eaFileExists=file_exists($upath.$name.".pdf");
                                if (!$eaFileExists) $tmplog.="|EA2DOESNTEXIST: $name";
                            }
                            if (!$eaFileExists) {
                                $eafolio3=substr($invData["nombreInterno"], -10);
                                $name="EA_{$eacp}_{$eafolio3}_{$eafecha}";
                                $tmplog.=",folio3='{$eafolio3}',fixdname='{$name}'";
                                $eaFileExists=file_exists($upath.$name.".pdf");
                                if (!$eaFileExists) $tmplog.="|EA3DOESNTEXIST: $name";
                            }
                        } else if ($tipoComprobante==="E"||$tipoComprobante==="P") {
                            $name="EA_{$eacp}_".($tipoComprobante==="E"?"NC":"RP")."_{$eafolio}_{$eafecha}";
                            $tmplog.=",tc='{$tipoComprobante}',fixdname='{$name}'";
                            $eaFileExists=file_exists($upath.$name.".pdf");
                            if (!$eaFileExists) $tmplog.="|EA4DOESNTEXIST: $name";
                        } else $tmplog.=",tc='{$tipoComprobante}',nofix";
                    }
                } else $tmplog.=",nocp&nofolio";
                if (!isset($name)) { echoJSDoc("error", "Datos incompletos para generar documento EA-PDF", null, $baseData+["line"=>__LINE__,"eacp"=>$eacp,"eafolio"=>$eafolio,"eafecha"=>$eafecha], "error"); return; }
            }
            $filename=$upath.$name.".pdf";
            if (!file_exists($filename)) { echoJSDoc("error", "No existe el documento a reemplazar", null, $baseData+["line"=>__LINE__,"ubicacion"=>$upath,"nombre"=>$name.".pdf","log"=>$tmplog], "error"); return; }
            $dt = new DateTime();
            if (!rename($filename, $upath.$name.$dt->format("ymdHis").".pdf")) { echoJSDoc("error", "El documento existente no se puede reemplazar", null, $baseData+["line"=>__LINE__,"ubicacion"=>$upath,"moveError"=>error_get_last(),"nombre"=>$name.".pdf"], "error"); return; }
            sleep(3);
            if (move_uploaded_file($ffile["tmp_name"], $filename)===false) { echoJSDoc("error", "El archivo no se pudo cargar", null, $baseData+["line"=>__LINE__,"filename"=>$filename,"moveError"=>error_get_last()], "error"); return; }
            global $firObj;
            if (!isset($firObj)) {
                require_once "clases/Firmas.php";
                $firObj=new Firmas();
            }
            $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>$tipo, "idReferencia"=>$invId, "accion"=>"reemplaza", "motivo"=>$upath.$name]);
            echoJSDoc("success", "Archivo reemplazado satisfactoriamente", null, [$tipo=>$filename], "docs");
            break; // return;
        case "resDoc": // Recupera archivo borrado
            // id:fId,path:tgt.getAttribute("filename")
            $esDesarrollo=in_array(getUser()->nombre, ["admin","sistemas"]);
            if (!$esDesarrollo) { echoJSDoc("error", "No tiene permiso para restaurar documentos eliminados", null, $baseData+["line"=>__LINE__], "error"); return; }
            $invId=$_POST["id"];
            $erasedPath=$_POST["path"];
            if(!isset($invId[0])) { echoJSDoc("error", "No se recibió identificador de CFDI", null, $baseData+["line"=>__LINE__], "error"); return; }
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $invData=$invObj->getData("id=$invId");
            if (!isset($invData[0]["id"][0])) {
                global $query;
                echoJSDoc("error", "No existe el CFDI $invId", null, $baseData+["line"=>__LINE__,"query"=>$query], "error");
                return;
            }
            $invData=$invData[0];
            $sysPath=$_SERVER["DOCUMENT_ROOT"];
            $ubicacion=$invData["ubicacion"];
            $pdfname=$invData["nombreInternoPDF"]??"";
            if (!isset($pdfname[0])) {
                $xmlname=$invData["nombreInterno"]??"";
                if (!isset($xmlname[0])) { echoJSDoc("error", "El CFDI no tiene documento XML", null, $baseData+["line"=>__LINE__], "error"); return; }
                // ToDo: Invertir nombre de xml para que corresponda al equivalente del pdf
                //  $pdfname=;
            }
            break;
        case "delDoc":
            $esSistemas = validaPerfil(["Sistemas","Administrador"]);
            $esBorraDoc = validaPerfil("Elimina Documentos")||$esSistemas;
            $invId=$_POST["id"];
            if (!$esBorraDoc) { echoJSDoc("error", "No tiene permiso para eliminar documentos", null, $baseData+["line"=>__LINE__,"user"=>getUser()->nombre], "error"); return; }
            $tipo=$_POST["type"];
            if(!isset($tipo[0])) { echoJSDoc("error", "No se especificó tipo de documento", null, $baseData+["line"=>__LINE__], "error"); return; }
            if ($tipo!=="cfdi"&&$tipo!=="ea") { echoJSDoc("error", "El tipo de documento no es válido", null, $baseData+["line"=>__LINE__], "error"); return; }
            if(!isset($invId[0])) { echoJSDoc("error", "No se recibió identificador de CFDI", null, $baseData+["line"=>__LINE__], "error"); return; }
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $invData=$invObj->getData("id=$invId");
            if (!isset($invData[0]["id"][0])) {
                global $query;
                echoJSDoc("error", "No existe el CFDI $invId", null, $baseData+["line"=>__LINE__,"query"=>$query], "error");
                return;
            }
            $invData=$invData[0];
            $sysPath=$_SERVER["DOCUMENT_ROOT"];
            $ubicacion=$invData["ubicacion"];
            if ($tipo==="cfdi") {
                $pdfname=$invData["nombreInternoPDF"]??"";
                if (!isset($pdfname[0])) { echoJSDoc("error", "El CFDI no tiene documento PDF", null, $baseData+["line"=>__LINE__], "error"); return; }
                $pdfname.=".pdf";
                $sttn=$invData["statusn"]??"";
                //if ($invData["statusn"]!=="0"&&!$esSistemas) { echoJSDoc("error", "Sólo puede eliminar documentos de CFDI en status Pendiente", null, $baseData+["line"=>__LINE__,"statusn"=>$invData["statusn"]], "error"); return; }
                if ($invObj->saveRecord(["id"=>$invId,"nombreInternoPDF"=>null])) {
                    $originalPath=$sysPath.$ubicacion.$pdfname;
                    $dt = new DateTime();
                    $deletedPath=$sysPath.$ubicacion."RM{$invId}_".$dt->format("ymdHi")."_".$pdfname;
                    if (file_exists($deletedPath)) unlink($deletedPath);
                    if (file_exists($originalPath)) {
                        rename($originalPath,$deletedPath);
                        sleep(3);
                    }
                    global $firObj;
                    if (!isset($firObj)) {
                        require_once "clases/Firmas.php";
                        $firObj=new Firmas();
                    }
                    $firObj->insertRecord(["idUsuario"=>getUser()->id, "modulo"=>"cfdi", "idReferencia"=>$invId, "accion"=>"elimina", "motivo"=>$_POST["motivo"]??""]);
                    echoJSDoc("success", "PDF Eliminado", null, ["deletedPath"=>$deletedPath], "docs");
                    return;
                }
                global $query;
                echoJSDoc("error", "No fue posible eliminar el CFDI-PDF", null, $baseData+["line"=>__LINE__,"docToDelete"=>$ubicacion.$pdfname,"query"=>$query,"errors"=>DBi::$errors], "error");
                return;
            } // else if ($tipo==="ea")
            $ea=$invData["ea"]??"";
            if ($ea!=="1") { echoJSDoc("error", "CFDI sin EA", null, $baseData+["line"=>__LINE__]+$invData, "error"); return; }
            $eacp=trim(str_replace("-", "", $invData["codigoProveedor"]??""));
            if (!isset($eacp[0])) { echoJSDoc("error", "CFDI sin Proveedor", null, $baseData+["line"=>__LINE__]+$invData, "error"); return; }
            $nombreXML=$invData["nombreInterno"]??"";
            if (!isset($nombreXML[0])) { echoJSDoc("error", "CFDI sin XML", null, $baseData+["line"=>__LINE__]+$invData, "error"); return; }
            $usIdx=strrpos($nombreXML, "_");
            if ($usIdx!==false && $usIdx>0) { $eafolio=substr($nombreXML, $usIdx+1);
            } else { $folio=$invData["folio"]??""; $uuid=$invData["uuid"]??"";
                $eafolio=(isset($folio[0])?$folio:$uuid);
                if (isset($eafolio[10])) $eafolio=substr($eafolio, -10);
            }
            $fechaFactura=$invData["fechaFactura"]??""; $eafecha=substr(str_replace("-","", $fechaFactura),2,6);
            $nombreEA = "EA_{$eacp}_{$eafolio}_{$eafecha}.pdf"; $eaPath=$sysPath.$ubicacion.$nombreEA;
            if (!file_exists($eaPath)) {
                $invRsp=$invObj->saveRecord(["id"=>$invId,"ea"=>"0"]);
                echoJSDoc("error", "No existe Entrada de Almacen", null, $baseData+["line"=>__LINE__,"delEA"=>$ubicacion.$nombreEA,"fixDB"=>($invRsp?"true":"false")]+$invData, "error");
                return;
            }
            if ($invObj->saveRecord(["id"=>$invId,"ea"=>"0"])) {
                $delEAName="RM{$invId}_".$nombreEA;
                $delEAPath=$sysPath.$ubicacion.$delEAName;
                if (file_exists($delEAPath)) unlink($delEAPath);
                rename($eaPath,$delEAPath);
                sleep(3);
                global $firObj;
                if (!isset($firObj)) {
                    require_once "clases/Firmas.php";
                    $firObj=new Firmas();
                }
                $usrId=getUser()->id;
                if (getUser()->name==="admin") {
                    if (!isset($usrObj)) {
                        require_once "clases/Usuarios.php";
                        $usrObj=new Usuarios();
                    }
                    $usrData = $usrObj->getData("nombre='SISTEMAS'",0,"id");
                    if (isset($usrData[0]["id"][0])) $usrId=$usrData[0]["id"];
                }
                $firObj->insertRecord(["idUsuario"=>$usrId, "modulo"=>"ea", "idReferencia"=>$invId, "accion"=>"elimina", "motivo"=>$_POST["motivo"]??""]);
                echoJSDoc("success", "PDF Eliminado", null, ["delEA"=>$ubicacion.$delEAName], "docs");
            } else {
                global $query;
                echoJSDoc("error", "No fue posible eliminar el EA-PDF", null, $baseData+["line"=>__LINE__,"delEA"=>$ubicacion.$nombreEA,"query"=>$query,"errors"=>DBi::$errors], "error");
            }
            break; // return;
        case "massReqTest":
            require_once "clases/PDFTools.php";
            require_once "clases/PDF.php";
            $localPath=PDFTools::getSavePath();
            $webPath=PDFTools::getWebPath();
            $baseNames=["COMPROBANTES DE PAGO DEL DIA","PAGOS DEL DIA ANTERIOR","COMPROBANTES DE PAGO DE DOS DIAS ANTERIORES","melo abr"];
            header('Content-Type: text/plain; charset=utf-8'); // or application/json, but since you're flushing, plain might be safer
            header('Cache-Control: no-cache');
            ob_implicit_flush(true);
            echo str_pad("", 4096); // Kickstart browser rendering
            ob_flush(); // Clear user-level buffer
            flush();    // Push to web server
            $totNum=0;
            $lenLocal=strlen($localPath);
            $splitPDFPages=false;
            $createTextFiles=true;
            $processTextFiles=true;
            if ($splitPDFPages||$createTextFiles) $pt=PDFTools::create();
            foreach ($baseNames as $baseIndex => $baseValue) {
                $basepk=($baseIndex<5)||(random_int(1, 2)<2);
                if ($basepk) successNFlush("LOOP1", ["baseIndex"=>$baseIndex, "baseName"=>$baseValue, "logname"=>"masstest"], true);
                $baseTotNum=0;
                $lenBase=strlen($baseValue);
                $pdfIndex=-1;
                try {
                    if ($splitPDFPages) foreach (glob($localPath.$baseValue."*.pdf") as $pdfIndex => $pdfAbsPath) {
                        $pdfpk=$basepk&&(($pdfIndex<5)||(random_int(1, 2)<2));
                        $pdfFileName=basename($pdfAbsPath);
                        if ($pdfpk) successNFlush("LOOP2", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "pdfname"=>$pdfFileName, "logname"=>"masstest"], true);
                        $pdfObj=PDF::getImprovedFile($pdfAbsPath);
                        if ($pdfObj===null || $pdfObj->pageCount<=1) {
                            if ($pdfpk) successNFlush("FAIL2", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "pageCount"=>($pdfObj->pageCount??"[null]"), "logname"=>"masstest"], true);
                            continue;
                        }
                        $separatePages=$pt->break($pdfAbsPath,$pdfFileName);
                        if ($pdfpk) successNFlush("SPLIT PDF PAGES $pdfIndex", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "pageCount"=>$pdfObj->pageCount, "numPages"=>count($separatePages), "log"=>$pt->getLog(), "logname"=>"masstest"], true);
                    }
                } catch (Exception $ex) {
                    errNFlush("EXCEPTION2", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "error"=>getErrorData($ex), "logname"=>"masstest"], true);
                }
                $pdfIndex=-1;
                try {
                    if ($createTextFiles) foreach (glob($localPath.$baseValue."*.pdf") as $pdfIndex => $pdfAbsPath) {
                        $pdfpk=$basepk&&(($pdfIndex<5)||(random_int(1, 2)<2));
                        $pdfFileName=basename($pdfAbsPath);
                        if ($pdfpk) successNFlush("LOOP3", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "pdfname"=>$pdfFileName, "logname"=>"masstest"], true);
                        $pdfObj=PDF::getImprovedFile($pdfAbsPath);
                        if ($pdfObj===null || $pdfObj->pageCount!=1) {
                            if ($pdfpk) successNFlush("FAIL3", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "pageCount"=>($pdfObj->pageCount??"[null]"), "logname"=>"masstest"], true);
                            continue;
                        }
                        $txtname=substr($pdfFileName, 0, -4).".txt";
                        $dblSlashName=str_replace("\\", "\\\\", $absPdfName);
                        $cmd="\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$dblSlashName\"";
                        $out=exec($cmd);
                        if ($pdfpk) successNFlush("CREATE TEXT PAGE $pdfIndex", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "cmd"=>$cmd, "filename"=>$txtname, "out"=>$out, "logname"=>"masstest"], true);
                    }
                    sleep(1);
                } catch (Exception $ex) {
                    errNFlush("EXCEPTION3", ["baseIndex"=>$baseIndex, "pdfIndex"=>$pdfIndex, "error"=>getErrorData($ex), "logname"=>"masstest"], true);
                }
                $globIndex=-1;
                try {
                    if ($processTextFiles) foreach (glob($localPath.$baseValue."*.txt") as $globIndex => $globValue) {
                        $pdfpk=$basepk&&(($pdfIndex<5)||(random_int(1, 2)<2));
                        if ($pdfpk) successNFlush("LOOP4", ["baseIndex"=>$baseIndex, "globIndex"=>$globIndex, "globValue"=>basename($globValue), "logname"=>"masstest"], true);
                        $paymData=getPaymData($globValue, ["localPath"=>$localPath,
                            "lenLocal"=>$lenLocal, "baseValue"=>$baseValue,
                            "lenBase"=>$lenBase]);
                        $pdfData=[];
                        if (is_array($paymData)) {
                            $totNum++; $baseTotNum++;
                            $rowData=["baseIndex"=>$baseIndex, "globIndex"=>$globIndex, "webBase"=>$webPath, "fullTotN"=>$totNum, "baseTotN"=>$baseTotNum, "inclusiveSeparator"=>"###", "logname"=>"masstest"]+$paymData;
                            successNFlush("row", $rowData, true);
                        }
                    }
                } catch (Exception $ex) {
                    errNFlush("EXCEPTION4", ["baseIndex"=>$baseIndex, "globIndex"=>$globIndex, "error"=>getErrorData($ex), "logname"=>"masstest"], true);
                }
                successNFlush("baseend", ["baseIndex"=>$baseIndex, "baseName"=>$baseValue, "fullTotN"=>$totNum, "baseTotN"=>$baseTotNum, "inclusiveSeparator"=>"###", "logname"=>"masstest"], true);
            } 
            successNFlush("fullend", ["fullTotN"=>$totNum, "inclusiveSeparator"=>"###", "logname"=>"masstest"], true);
            echoJSDoc("success", "afterend", null, []);
            break; // return;
        case "massReqPaym":
            require_once "clases/PDFTools.php";
            $localPath=PDFTools::getSavePath();
            $webPath=PDFTools::getWebPath();
            $data=$_POST["data"]; // [["id"=>_,"folio"=>_,"type"=>_],...]
            if (!isset($data[0])) { echoJSDoc("error", "Debe marcar las casillas de las solicitudes a procesar", null, null, "error"); return; }
            $files=isset($_FILES["files"])?getFixedFileArray($_FILES["files"]):[];
            $onePages=[]; $txtPages=[]; $foundPages=[];
            $regData=[];
            if (isset($files[0])) {
                try {
                    $pt=PDFTools::create();
                    foreach ($files as $fIdx => $file) { // Separar un archivo pdf por página
                        //doclog("Separando una hoja por archivo","masspaym",["fIdx"=>$fIdx, "file"=>$file]);
                        if ($fIdx>0) sleep(1);
                        $separatePages=$pt->break($file["tmp_name"],$file["name"]);
                        doclog("Separar pdf en archivo por hoja","masspaym",["numPages"=>count($separatePages),"index"=>$fIdx,"file"=>$file]);
                        $onePages=array_merge($onePages,$separatePages);
                        if (!move_uploaded_file($file["tmp_name"], $localPath.$file["name"])) {
                            doclog("Error: No se pudo copiar el archivo pdf original","masspaym",["path"=>$localPath,"file"=>$file["name"]]);
                        } else doclog("Archivo PDF descargado y copiado a ruta local","masspaym",["path"=>$localPath,"file"=>$file["name"]]);
                    }
                    foreach ($onePages as $oneIdx => $pdfname) { // Generar archivos txt por cada pdf generado
                        $basn=substr($pdfname, 0, -4);
                        $txtname=$basn.".txt";
                        $txtPages[$oneIdx]=$txtname;
                        $absPdfName=$localPath.$pdfname;
                        $dblSlashName=str_replace("\\", "\\\\", $absPdfName);
                        $cmd="\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$dblSlashName\"";
                        $out=exec($cmd);
                        //sleep(1);
                        //doclog("Creando archivo de texto","masspaym",["oneIdx"=>$oneIdx,"pdfname"=>$localPath.$pdfname,"cmd"=>$cmd,"out"=>$out,"txtname"=>$localPath.$txtname,"exists"=>(file_exists($localPath.$txtname)?"YES":"NO")]);
                    }
                } catch (Exception $ex) {
                    echoJSDoc("error", "MASSPAYM: Ocurrió un error", null, $baseData+["line"=>__LINE__,"error"=>getErrorData($ex),"logs"=>$log], "error");
                    return;
                }
                sleep(1);
            }
            foreach ($txtPages as $tIdx => $textname) {
                try {
                    $textPath=$localPath.$textname;
                    if (file_exists($textPath)) {
                        //doclog("Revisando archivo de texto que si se encontró","masspaym",["idx"=>$tIdx,"textname"=>$textPath]);
                        $extractData=getPaymData($textPath);
                        $pdfData=[];
                        if (is_array($extractData)) {
                            $newName=$extractData["newName"]??null;
                            if (isset($newName[0])) {
                                if (file_exists($localPath.$newName)) {
                                    unlink($localPath.$newName);
                                }
                                $oldName=$onePages[$tIdx];
                                if (file_exists($localPath.$oldName)) {
                                    if (rename($localPath.$oldName, $localPath.$newName)) {
                                        doclog("Folio de Solicitud extraida del archivo de texto para renombrar archivo PDF","masspaym",["idx"=>$tIdx,"oldname"=>$onePages[$tIdx],"newname"=>$newName]);
                                        $foundPages[$folio]=[$tIdx,$folio2];
                                    } else {
                                        doclog("ERROR: No se pudo renombrar hoja PDF","masspaym",["idx"=>$tIdx,"localPath"=>$localPath,"oldname"=>$onePages[$tIdx],"newname"=>$newName]);
                                    }
                                }
                            }
                        }
                    } else {
                        doclog("No existe archivo de texto","masspaym",["onepageIdx"=>$tIdx,"pdfname"=>$onePages[$tIdx],"textname"=>$textname]);
                        continue;
                    }
                } catch (Exception $ex) {
                    doclog("Error al obtener comprobante de pago masivo","masspaym",["onepageIdx"=>$tIdx,"pdfname"=>$onePages[$tIdx],"textname"=>$textname,"error"=>getErrorData($ex)]);
                    continue;
                }
            }
            $foundFiles=0;
            DBi::autocommit(false);
            if (!isset($usrObj)) {
                require_once "clases/Usuarios.php";
                $usrObj=new Usuarios();
            }
            $mailData=[];
            foreach ($data as &$value) { // ["id"=>_,"folio"=>_,"type"=>_]
                if (is_string($value)) $value=json_decode($value,true);
                $value["name"]="CPB_".str_replace("-", "", $value["folio"]).".pdf";
                if (file_exists($localPath.$value["name"])) {
                    doclog("Archivo con folio encontrado","masspaym",["value"=>$value]);
                    try {
                        $newName=payRequest($value["id"], $value["folio"], $value["type"], $value["name"], $mailData);
                        DBi::commit();
                        $value["comprobantePago"]=$newName;
                        $foundFiles++;
                    } catch (Error $err) {
                        DBi::rollback();
                        doclog("Error al marcar pagada una solicitud", "masspaym", ["error"=>getErrorData($err),"value"=>$value]);
                    }
                } else doclog("Archivo con folio NO encontrado","masspaym",["value"=>$value]);
            }
            foreach ($mailData as $key => $eachData) {
                $num=$eachData["num"];
                if ($num==1) {
                    $solFolio=$eachData["solFolio"];
                    $subject="Solicitud $solFolio Pagada";
                    $message=getSolSingleView($eachData["solId"], $solFolio, $subject);
                } else {
                    $subject="Solicitudes Pagadas";
                    $message=getSolMultiView($eachData["solList"]);
                }
                sendMail($subject,$message,null,$eachData["destination"],null,null,$eachData["settings"]);
            }
            DBi::autocommit(true);
            $returnData=["folios"=>array_column($data, "folio"),"data"=>$data,"onePages"=>$onePages,"txtPages"=>$txtPages,"foundPages"=>$foundPages,"mailData"=>$mailData];
            if ($foundFiles==0) { echoJSDoc("error", "MASSPAYM: No se encontró ningún comprobante de pago relacionado", null, $returnData, "error"); return; }
            $dataLen=count($data);
            //if ($foundFiles===$dataLen) { echoJSDoc("success", "Comprobantes encontrados ya generados se ligaron a las solicitudes marcadas", null, $returnData, "archivo"); return; }
            $isSingle=($foundFiles===1);
            $pl_s =$isSingle?"":"s";
            $pl_es=$isSingle?"":"es";
            $pl_n =$isSingle?"o":"aron";
            echoJSDoc("success", "Se lig$pl_n $foundFiles comprobante$pl_s de la$pl_s solicitud$pl_es marcada$pl_s.", null, $returnData, "archivo");
            break; // return;
        case "pdfbreak":
            $onePageFiles=breakPDFFiles(getFixedFileArray($_FILES["files"]));
            if (!$onePageFiles) return;
            require_once "clases/PDFTools.php";
            $localPath=PDFTools::getLocalPath();
            $webPath=PDFTools::getWebPath();
            $localFiles = array_map(fn($item) => $localPath.$item, $onePageFiles);
            $webFiles = array_map(fn($item) => $webPath.$item, $onePageFiles);
            $textFiles=pdfToTextFiles($localFiles);
            if (!$textFiles) return;
            sleep(1);
            $data=showMeTextFileData($textFiles);
            echoJSDoc("success", "exito", null, ["webnames"=>$webFiles, "text"=>$data]);
            break; // return;
        case "pdfmerge2":
            try {
                $dt=new DateTime();
                $mergeName="unido".$dt->format("ymdHis").".pdf";
                $files=getFixedFileArray($_FILES["files"]);
                require_once "clases/PDFTools.php";
                PDFTools::init();
                $pt=PDFTools::create();
                $pt->setMergeList(array_column($files,"tmp_name"));
                $pt->setMergeName($mergeName);
                $pt->merge();
                $resultData["webname"]=$pt->getWebName();
                $resultData["basename"]=$mergeName;
                $resultData["log"]=$pt->getLog();
                echoJSDoc("success", "exito", null, $resultData);
            } catch (Exception $ex) {
                echoJSDoc("error", "Ocurrió un error", null, $baseData+["line"=>__LINE__,"error"=>getErrorData($ex)], "error");
            }
            break; // return;
        case "pdfmerge":
            try {
                $dt = new DateTime();
                $mergeName="merge".$dt->format("ymdHis").".pdf";
                $mergeList=[];
                if (isset($_POST["factIds"][0])) {
                    global $invObj;
                    if (!isset($invObj)) {
                        require_once "clases/Facturas.php";
                        $invObj=new Facturas();
                    }
                    $invObj->rows_per_page=0;
                    $invData=$invObj->getDataByFieldArray(["id"=>$_POST["factIds"]], 0, "concat(ubicacion,nombreInternoPDF,'.pdf') link,ea");
                    $mergeList=array_column($invData, "link");
                }
                if (isset($_POST["mergeList"][0])) {
                    $mergeList+=$_POST["mergeList"];
                }
                if (empty($mergeList)) { echoJSDoc("error", "Falta indicar una lista de archivos", null, $baseData+["line"=>__LINE__], "error"); return; }
                require_once "clases/PDFTools.php";
                PDFTools::init();
                $resultData=[];
                $pt=PDFTools::create();
                $pt->setMergeList($mergeList);
                $pt->setMergeName($mergeName);
                $pt->merge();
                //$resultData["contentObject"]=[];
                $resultData["webname"]=$pt->getWebName();
                $resultData["basename"]=$mergeName;
                $resultData["log"]=$pt->getLog();
                echoJSDoc("success", "exito", null, $resultData);
            } catch (Exception $ex) {
                echoJSDoc("error", "Ocurrió un error", null, $baseData+["line"=>__LINE__,"error"=>getErrorData($ex)], "error");
            }
            break; // return;
    }
    if(isset($premsg)) echo $premsg."<br>";
    if(isset($list)) echo arr2List($list, "OL");
//        if(isset($msg)) echo "<hr>".$msg;
}
function getPaymData($textPath, $iniData=null) {
    static $currVersion=1.2;
    $data=null;
    $handle=fopen($textPath, "r");
    $lang="ES";
    $lines=[];
    $tIdx=$iniData["tIdx"]??"";
    if ($handle) {
        $isRefLine=false;
        $lineNum=0;
        $i=0;
        while (($line=fgets($handle))!==false) {
            //$oriLine=$line;
            $i++;
            $line = trim(mb_convert_encoding($line, "utf-8"));
            if (!isset($line[0])) continue;
            if ($line==="null") {
                doclog("ERROR: Line is 'null'","masspaym",["idx"=>$tIdx,"textPath"=>$textPath,"i"=>$i/*,"oriLine"=>$oriLine*/]);
                continue;
            }
            if (substr($line, 0, 1)==="{" && substr($line, -1)==="}") {
                try {
                    $nwdt=json_decode($line, true);
                    if (is_array($nwdt)&&isset($nwdt["version"])&&$nwdt[$version]>=$currVersion) {
                        $data=$nwdt;
                        break;
                    }
                } catch (Exception $ex) { }
                continue;
            }
            $lines[]=$line;
            $lineNum++;
            if ($lineNum==1) {
                if (substr($line,0,39)==="Informe de detalle de pagos de iniciaci")
                    $lang="ES";
                else if (substr($line,0,45)==="Transaction Initiation Payment Details Report")
                    $lang="EN";
                else {
                    doclog("ERROR: Idioma no reconocido","masspaym",["idx"=>$tIdx,"textPath"=>$textPath,"i"=>$i,"textLine"=>$line/*,"oriLine"=>$oriLine*/]);
                    $lineNum=0;
                    break;
                }
            }
            if ($isRefLine===false) {
                $pos=false;
                switch($lang) {
                    case "ES": $pos=strpos($line, "mero de Referencia de Transacci"); break;
                    case "EN": $pos=strpos($line, "Transaction Reference Number"); break;
                }
                if ($pos!==false) $isRefLine=18;
            } else {
                $pos2=0;
                if ($isRefLine>0) {
                    $posLen=false;
                    switch($lang) {
                        case "ES": $pos=strpos($line, "n de Formato Libre"); $posLen=18; break;
                        case "EN": $pos=strpos($line, "Import Free Format Transaction"); $posLen=30; break;
                    }
                    if ($pos!==false && $posLen!==false && $isRefLine>1 && isset($line[$pos+$posLen+1])) {
                        $pos2=strpos($line, " ", $pos+$posLen-1);
                        if ($pos2!==false && isset($line[$pos2+13])) {
                            $isRefLine=0; $pos2++;
                        } else {
                            $pos2=0;
                            $isRefLine--;
                        }
                    } else $isRefLine--;
                }
                if ($isRefLine===0 && substr($line, $pos2, 3)==="SOL") {
                    $txt=trim(substr($line, $pos2+3));
                    $pos3=strpos($txt, " ");
                    if ($pos3!==false) $txt=substr($txt, 0, $pos3);
                    $data=["solLine"=>"SOL$txt", "mesPago"=>substr($txt, 0, 4), "diaPago"=>substr($txt, 4, 2), "cut"=>substr($txt, 6, 3)];
                    if (!isset($txt[9])) {
                        $error="Solicitud sin folio";
                        doclog($error,"masspaym",["idx"=>$tIdx, "txt"=>$txt]+$data);
                        $data["error"]=$error;
                        break;
                    }
                    $padNum=str_pad(trim(substr($txt,9,3)), 3, "0", STR_PAD_LEFT);
                    $data["numFolio"]=$padNum;
                    $preFolio=$data["cut"].$data["mesPago"];
                    $folio=$preFolio."-".$padNum;
                    $folio2=$preFolio.$padNum;
                    $newName="CPB_".$folio2.".pdf";
                    if ($pos2===0) continue;
                }
                // ToDo: Obtener total, beneficiario y cuenta de beneficiario
                if ($isRefLine===0 && isset($data)) {
                    if (!isset($data["receiver"])) {
                        if ($pos2===0) $receiver=$line;
                        else if (isset($pos3) && $pos3>$pos2) {
                            $pos4=strpos($line, "/", $pos3+1);
                            if (($pos4-2)>$pos3) {
                                $receiver=trim(substr($line, $pos3, $pos4-2));
                            }
                        }
                        if (isset($receiver[0])) $data["receiver"]=$receiver;
                    } else if (!isset($data["total"])) {
                        ;
                    } else if (!isset($data["receiverAccount"])) {
                        ;
                    }
                }
            }
        }
        fclose($handle);
        if ($lineNum>0 && isset($data)) {
            $data["version"]=$currVersion;
            $data["lineNum"]=$lineNum;
            $localPath=$iniData["localPath"]??""; // 
            $lenLocal=$iniData["lenLocal"]??strlen($localPath);
            $baseValue=$iniData["baseValue"]??"";
            $lenBase==$iniData["lenBase"]??strlen($baseValue);
            if ($lenLocal>0) {
                $textFileName=substr($textPath, $lenLocal);
                $data["txtData"]=["fileName"=>$textFileName, "fileSize"=>sizeFix(filesize($textPath))];
                $baseName=substr($textFileName, 0, -4);
                if ($lenBase>0) {
                    $payKey=substr($baseName, $lenBase);
                    if (isset($payKey[0])) {
                        $unsc=strpos($payKey, "_");
                        if ($unsc!==false && $unsc>0) {
                            $data["txtData"]["payPage"]=substr($payKey, $unsc+1);
                            $payKey=substr($payKey, 0, $unsc);
                        } else $data["txtData"]["payError"]="NOPAYPAGE";
                        $data["txtData"]["payKey"]=$payKey;
                    }
                }
                if (isset($newName[0])) {
                    $pdfPath=$localPath.$newName;
                    if (file_exists($pdfPath)) $data["pdfData"]=["fileName"=>$newName];
                }
                if (!isset($data["pdfData"])) {
                    $pdfPath=substr($textPath, 0, -4).".pdf";
                    if (file_exists($pdfPath))
                        $data["pdfData"]=["fileName"=>substr($pdfPath,$lenLocal)];
                }
            }
            setRelatedData($data);
            $invoiceRootPath=dirname(__DIR__)."\\";
            if (!isset(["pdfData"]["fileName"][0])) {
                $pdfPath=null;
                $dataInvoice=$data["invoice"]??null;
                if (isset($dataInvoice)) {
                    $invCP=$dataInvoice["comprobantePagoPDF"]??"";
                    if (isset($invCP[0])) {
                        $invName=$invCP.".pdf";
                        $invPath=$dataInvoice["ubicacion"]??"";
                        $pdfPath=$invoiceRootPath.$invPath.$invName;
                        if (file_exists($pdfPath))
                            $data["pdfData"]=["fileName"=>$invName, "invoicePath"=>$invPath];
                    }
                }
                $dataReceipt=$data["receipt"]??null;
                if (isset($dataReceipt)) {
                    $ctrCP=$dataReceipt["comprobantePago"]??"";
                    if (isset($ctrCP[0])) {
                        $ctrName=$ctrCP.".pdf";
                        $ctrAlias=$dataReceipt["aliasGrupo"];
                        $ctrFecha=$dataReceipt["fechaRevision"];
                        //$ctrFPago=$dataReceipt["fechaPago"];
                        $ctrYear =substr($ctrFecha, 0, 4);
                        $ctrMonth=substr($ctrFecha, 5, 2);
                        $ctrPath="archivos/$ctrAlias/$ctrYear/$ctrMonth/";
                        $pdfPath=$invoiceRootPath.$ctrPath.$ctrName;
                        if (file_exists($pdfPath))
                            $data["pdfData"]=["fileName"=>$ctrName, "invoicePath"=>$ctrPath];
                    }
                }
                $dataOrder=$data["order"]??null;
                if (isset($dataOrder)) {
                    $ordCP=$dataOrder["comprobantePago"]??"";
                    if (isset($ordCP[0])) {
                        $ordName=$ordCP.".pdf";
                        $ordPath=$dataOrder["rutaArchivo"]??"";
                        $pdfPath=$invoiceRootPath.$ordPath.$ordName;
                        if (file_exists($pdfPath))
                            $data["pdfData"]=["fileName"=>$ordName, "invoicePath"=>$ordPath];
                    }
                }
            }
            if (isset($data["pdfData"]["fileName"][0])) {
                $data["pdfData"]["fileSize"]=sizeFix(filesize($pdfPath));
                $pdfObj=PDF::getImprovedFile($pdfPath);
                $isOnePage=false;
                if (isset($pdfObj)) {
                    $data["pdfData"]["pageCount"]=$pdfObj->pageCount;
                    $isOnePage = ($pdfObj->pageCount==1);
                } else {
                    $data["pdfData"]["error"]=PDF::$errmsg??"Error de creacion no reconocido";
                    $data["pdfData"]["errData"]=PDF::$errdata;
                }
            } else $data["pdfData"]["error"]="SIN PDF";
        }
        if (isset($lines[0])) file_put_contents($textPath, json_encode($data)."\n".implode(PHP_EOL, $lines)); // sobreescribir archivo
    } else {
        doclog("No se pudo leer archivo de texto","masspaym",["onepageIdx"=>$tIdx,"pdfname"=>$onePages[$tIdx],"textPath"=>$textPath]);
    }
    return $data;
}
function setRelatedData(&$data) {
    if (!isset($data["cut"][0])||!isset($data["mesPago"][0])||!isset($data["numFolio"][0])) {
        $data["requestError"]="Missing initial data";
        return;
    }
    $data["folio"]=$data["cut"].$data["mesPago"]."-".$data["numFolio"];
    global $solObj;
    if (!isset($solObj)){require_once "clases/SolicitudPago.php";$solObj=new SolicitudPago();}
    $solData=$solObj->getData("folio='$data[folio]'",0,"id,idFactura,idOrden,idContrarrecibo,idEmpresa,idUsuario,idAutoriza,folio,status,proceso");
    if (!isset($solData[0])) {
        $solData=$solObj->getData("folio like '$data[cut]%-$data[numFolio]' and date_format(fechaPago,'%y%m')<='$data[mesPago]'",0,"id,idFactura,idOrden,idContrarrecibo,idEmpresa,idUsuario,idAutoriza,folio,status,proceso");
    }
    if (!isset($solData[0])) {
        $data["requestError"]="Not found $data[folio], or $data[cut]%-$data[numFolio] with payDate='$data[mesPago]'";
        return;
    }
    if (isset($solData[1])) {
        $solFolios=array_column($solData, "folio");
        $data["requestError"]="Too many requests found ('".implode("','", $solFolios)."')";
        return;
    }
    $solData=$solData[0];
    $data["request"]=$solData;
    if (!empty($solData["idContrarrecibo"])) {
        global $ctrObj;
        if (!isset($ctrObj)){require_once "clases/Contrarrecibos.php";$ctrObj=new Contrarrecibos();}
        $ctrData=$ctrObj->getData("id=$solData[idContrarrecibo]",0,"folio,aliasGrupo,fechaRevision,comprobantePago");
        if (!isset($ctrData[0])) $data["receiptError"]="Not found receipt data";
        else $data["receipt"]=$ctrData[0];
    } else if (!empty($solData["idOrden"])) {
        global $ordObj;
        if (!isset($ordObj)){require_once "clases/OrdenesCompra.php";$ordObj=new OrdenesCompra();}
        $ordData=$ordObj->getData("id=$solData[idOrden]",0,"folio,rutaArchivo,nombreArchivo,comprobantePago,status");
        if (!isset($ordData[0])) $data["orderError"]="Not found purchase order data";
        else $data["order"]=$ordData[0];
    } else if (!empty($solData["idFactura"])) {
        global $invObj;
        if (!isset($invObj)){require_once "clases/Facturas.php";$invObj=new Facturas();}
        $invData=$invObj->getData("id=$solData[idFactura]",0,"folio,ubicacion,nombreInterno,nombreInternoPDF,comprobantePagoPDF,tieneOrden,status,statusn,ea");
        if (!isset($invData[0])) $data["invoiceError"]="Not found invoice data";
        else $data["invoice"]=$invData[0];
    }
}
function breakPDFFiles($files) {
    $resultData=[]; $log=[];
    try {
        require_once "clases/PDFTools.php";
        $pt = PDFTools::create();
        foreach ($files as $fIdx => $file) {
            $log[]="[".(new DateTime())->format("His")."] $fIdx) $file[name]";
            if ($fIdx>0) sleep(1);
            $singlePages=$pt->break($file["tmp_name"],$file["name"]);
            $log[]=$pt->getLog(); // $singlePages["log"]; // 
            $numPg=count($singlePages); // $singlePages["count"];
            $log[]="[".(new DateTime())->format("His")."] BREAK RESULT: Obtained ".$numPg." page".($numPg!=1?"s":"");
            if (isset($resultData[0]))
                array_push($resultData, ...$singlePages);
            else $resultData=$singlePages;
        }
    } catch (Exception $ex) {
        echoJSDoc("error", "Ocurrió un error", null, ["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__,"error"=>getErrorData($ex),"log"=>$log], "error");
        return false;
    }
    doclog("Split PDF Files","archivo",["resultData"=>$resultData,"log"=>$log,"webPath"=>PDFTools::getWebPath(),"localPath"=>PDFTools::getSavePath()]);
    return $resultData;
}
function pdfToTextFiles($onepages) {
    $resultData=[]; $log=[];
    try {
        $log[]="[".(new DateTime())->format("His")."] Creating text from pdf files";
        $pgNum=count($onepages);
        foreach ($onepages as $oneIdx => $pdfname) {
            $log[]="[".(new DateTime())->format("His")."] ".($oneIdx+1)."/$pgNum) Preparing to convert: $pdfname";
            $cmd="\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$pdfname\"";
            $out=exec($cmd);
            $txtname=substr($pdfname, 0, -4).".txt";
            $resultData[]=$txtname;
            $log[]="[".(new DateTime())->format("His")."] Created text file $txtname";
        }
    } catch (Exception $ex) {
        echoJSDoc("error", "Ocurrió un error", null, ["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"line"=>__LINE__,"error"=>getErrorData($ex),"logs"=>$log], "error");
        return false;
    }
    doclog("Generated Text from PDF files","archivo",["resultData"=>$resultData,"log"=>$log]);
    return $resultData;
}
function showMeTextFileData($textList) {
    $resultData=[]; $log=[];
    foreach ($textList as $tIdx => $textname) {
        $block=""; $txNum=$tIdx+1; $txDesc="$txNum) '$textname'";
        try {
            if (file_exists($textname)) {
                $handle=fopen($textname, "r");
                if ($handle) {
                    $log[]="[".(new DateTime())->format("His")."] Reading Text $txDesc";
                    $foundData=false; $extraData=false;
                    while (($line=fgets($handle))!==false) {
                        $line = trim(mb_convert_encoding($line, "utf-8"));
                        if (!isset($line[0])) continue;
                        if (isset($block[0])) $block.=" ";
                        $sIdx=strpos($line, " ");
                        if ($sIdx) $block.=substr($line, 0, $sIdx);
                        else $block.=$line;
                    }
                    fclose($handle);
                    $resultData[]=$block;
                } else {
                    $log[]="[".(new DateTime())->format("His")."] File Open Error $txDesc";
                    continue;
                }
            } else {
                $log[]="[".(new DateTime())->format("His")."] Text File wasn't created $txDesc";
                continue;
            }
        } catch (Exception $ex) {
            $log[]="[".(new DateTime())->format("His")."] ".get_class($ex).": ".json_encode(getErrorData($ex));
            continue;
        }
    }
    doclog("Generated Text from PDF files","archivo",["resultData"=>$resultData,"log"=>$log]);
    return $resultData;
}
function findReqDataInTextFile($solfolio) {

}
function getSolFolioInLine($txt) {
    $isSol=false;
    if (substr($txt, 0, 3)==="SOL") {
        $isSol=true;
        $txt=substr($txt, 3);
    }
    $fecha=substr($txt, 0, 4);
    $gpoCut=substr($txt, 6, 3);
    $num=substr($txt, 9);
    $padNum=str_pad(substr($txt, 9), 3, "0", STR_PAD_LEFT);
    if ($num!==$padNum) $padNum.="!";
    $txt=($isSol?"SOL ":"").$gpoCut.$fecha."-".$padNum;
    return $txt;
}
function payRequest($solId,$folio,$type,$oriname,&$mailData) {
    global $solObj, $invObj, $ordObj, $ctrObj, $prvObj, $gpoObj, $usrObj, $query;
    $type=strtolower($type);
    require_once "clases/PDFTools.php";$tmpPath=PDFTools::getSavePath();
    $fullOriName=$tmpPath.$oriname;
    chmod($fullOriName, 0777);
    if (!isset($solObj)){require_once "clases/SolicitudPago.php";$solObj=new SolicitudPago();}
    $solData=$solObj->getData("id=$solId",0,"idFactura,idOrden,idContrarrecibo,status,proceso,idEmpresa,idUsuario");
    if (!isset($solData[0])) throw new DocLogException("No se encontró la solicitud $folio",["id"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"query"=>$query]);
    $solData=$solData[0];
    switch($type[0]) {
        case "f": $invId=$solData["idFactura"]; if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
            $invData=$invObj->getData("id=$invId",0,"ubicacion,nombreInterno,nombreInternoPDF,codigoProveedor");
            if (!isset($invData[0])) throw new DocLogException("No se encontró la factura relacionada",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"query"=>$query]);
            $invData=$invData[0];
            $path=$invData["ubicacion"];
            $name="CP_".(isset($invData["nombreInternoPDF"][0])?$invData["nombreInternoPDF"]:$invData["nombreInterno"]);
            if (!$invObj->saveRecord(["id"=>$invId,"comprobantePagoPDF"=>$name]) && !empty(DBi::$errno))
                throw new DocLogException("No se pudo asignar el comprobante de pago a la factura",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"invData"=>$invData,"query"=>$query]);
            $codigoProveedor=$invData["codigoProveedor"];
        break;
        case "o": $ordId=$solData["idOrden"]; if (!isset($ordObj)) { require_once "clases/OrdenesCompra.php"; $ordObj=new OrdenesCompra(); }
            $ordData=$ordObj->getData("id=$ordId",0,"rutaArchivo,nombreArchivo,idProveedor");
            if (!isset($ordData[0])) throw new DocLogException("No se encontró la orden relacionada",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"query"=>$query]);
            $ordData=$ordData[0];
            $path=$ordData["rutaArchivo"];
            $name="CP_".$ordData["nombreArchivo"];
            if (!$ordObj->saveRecord(["id"=>$ordId,"comprobantePago"=>$name]) && !empty(DBi::$errno))
                throw new DocLogException("No se pudo asignar el comprobante de pago a la orden",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"ordData"=>$ordData,"query"=>$query]);
            if (!isset($prvObj)) {
                require_once "clases/Proveedores.php";
                $prvObj = new Proveedores();
            }
            $prvData=$prvObj->getData("id=$ordData[idProveedor]",0,"codigo");
            $codigoProveedor=$prvData[0]["codigo"]??"";
        break;
        case "c": $ctrId=$solData["idContrarrecibo"]; if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj=new Contrarrecibos(); }
            $ctrData=$ctrObj->getData("id=$ctrId",0,"aliasGrupo,fechaRevision,folio,codigoProveedor");
            if (!isset($ctrData[0])) throw new DocLogException("No se encontró el contra recibo relacionado",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"query"=>$query]);
            $ctrData=$ctrData[0];
            $name="CP_CTR_".$ctrData["folio"];
            $fRev=$ctrData["fechaRevision"];
            $yr =substr($fRev, 0, 4);
            $mon=substr($fRev, 5, 2);
            $path="archivos/$ctrData[aliasGrupo]/$yr/$mon/";
            if (!$ctrObj->saveRecord(["id"=>$ctrId,"comprobantePago"=>$name])) {
                if (!empty(DBi::$errno)) throw new DocLogException("No se pudo asignar el comprobante de pago al contra recibo",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"ctrData"=>$ctrData,"query"=>$query]);
                doclog("No se pudo guardar comprobantePago de contra recibo","masspaym",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"comprobantePago"=>$name,"solData"=>$solData,"query"=>$query]);
            }
            $codigoProveedor=$ctrData["codigoProveedor"];
        break;
        default:
            throw new DocLogException("Tipo de documento invalido",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData]);
    }
    $docRoot=$_SERVER["DOCUMENT_ROOT"];
    $fullPath=$docRoot.$path;
    $newName=$path.$name;
    $fullNewName=$fullPath.$name.".pdf";
    if (file_exists($fullNewName)) {
        // ToDo: en lugar de eliminar el archivo cambiar el nombre del archivo anexando al final su fecha de creacion _yymmdd (posiblemente sea necesario agregar tiempo hhiiss)
        if (!chmod($fullNewName, 0777)) throw new DocLogException("No se pudo asignar nuevo comprobante, ya existe uno",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"newname"=>$newName]);
        if (!unlink($fullNewName)) throw new DocLogException("No se pudo asignar nuevo comprobante, ya existe uno",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"newname"=>$newName]);
    }
    if (!rename($fullOriName, $fullNewName)) throw new DocLogException("No se pudo guardar el comprobante de pago",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"newname"=>$newName]);
    $newStatus=$solData["status"]|SolicitudPago::STATUS_PAGADA;
    if (!$solObj->saveRecord(["id"=>$solId,"status"=>$newStatus,"proceso"=>SolicitudPago::PROCESO_PAGADA]) && !empty(DBi::$errno)) throw new DocLogException("No se pudieron guardar los cambios a la solicitud",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"query"=>$query]);
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $idEmpresa=$solData["idEmpresa"];
    $settings=["gpoId"=>$idEmpresa, "domain"=>$gpoObj->getDomainKey($idEmpresa)];
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $solIdUsuario=$solData["idUsuario"];
    $mailKey="{$idEmpresa}_{$solIdUsuario}";
    if (isset($mailData[$mailKey])) {
        if ($mailData[$mailKey]["num"]==1) {
            $mailData[$mailKey]["solList"]=[["solId"=>$mailData[$mailKey]["solId"], "solFolio"=>$mailData[$mailKey]["solFolio"]], ["solId"=>$solId, "solFolio"=>$folio]];
            unset($mailData[$mailKey]["solId"]);
            unset($mailData[$mailKey]["solFolio"]);
        } else {
            $mailData[$mailKey]["solList"]+=[["solId"=>$solId, "solFolio"=>$folio]];
        }
        $mailData[$mailKey]["num"]++;
    } else {
        $usrData=$usrObj->getData("id=$solIdUsuario",0,"id,nombre,persona,email");
        if (!isset($usrData[0]["nombre"])) throw new DocLogException("No se encuentra usuario solicitante",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"query"=>$query,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
        $usrData=$usrData[0];
        $mailData[$mailKey]=["usrData"=>$usrData, "num"=>1, "solId"=>$solId, "solFolio"=>$folio, "destination"=>["address"=>$usrData["email"], "name"=>replaceAccents($usrData["persona"])], "settings"=>$settings];
    }

    if (!isset($codigoProveedor[0])) throw new DocLogException("No se encuentra proveedor",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData]);
    $mailKey="{$idEmpresa}_{$codigoProveedor}";
    if (isset($mailData[$mailKey])) {
        if ($mailData[$mailKey]["num"]==1) {
            $mailData[$mailKey]["solList"]=[["solId"=>$mailData[$mailKey]["solId"], "solFolio"=>$mailData[$mailKey]["solFolio"]], ["solId"=>$solId, "solFolio"=>$folio]];
            unset($mailData[$mailKey]["solId"]);
            unset($mailData[$mailKey]["solFolio"]);
        } else {
            $mailData[$mailKey]["solList"]+=[["solId"=>$solId, "solFolio"=>$folio]];
        }
        $mailData[$mailKey]["num"]++;
    } else {
        $uprData=$usrObj->getData("nombre='$codigoProveedor'",0,"id,nombre,persona,email");
        if (!isset($uprData[0]["id"])) throw new DocLogException("No se encuentra usuario proveedor",["solId"=>$solId,"folio"=>$folio,"type"=>$type,"oriname"=>$oriname,"solData"=>$solData,"query"=>$query,"errno"=>DBi::getErrno(),"error"=>DBi::getError()]);
        $uprData=$uprData[0];
        $mailData[$mailKey]=["usrData"=>$uprData, "num"=>1, "solId"=>$solId, "solFolio"=>$folio, "destination"=>["address"=>$uprData["email"], "name"=>replaceAccents($uprData["persona"])], "settings"=>$settings];
    }

    $solObj->firma($solId,"anexa");
    $solObj->firma($solId,"paga");
    return $newName;
}
function getSolMultiView($solList) {
    $baseKeyMap=["%ENCABEZADO%"=>"Solicitudes Pagadas","%RESPUESTA%"=>"<h2>Las solicitudes han sido pagadas</h2>","%BUTTONS%"=>"<!-- 2 -->","isInteractive"=>"0"];
    $base = file_get_contents(getBasePath()."templates/respGralSolPago.html");
    if (!isset($baseKeyMap["%HOSTNAME%"][0])) $baseKeyMap["%HOSTNAME%"]=$_SERVER["HTTP_ORIGIN"];
    if (!isset($baseKeyMap["%RESPUESTA%"])) $baseKeyMap["%RESPUESTA%"]="";
    $isInteractive = (isset($baseKeyMap["isInteractive"])&&$baseKeyMap["isInteractive"]==="1");
    if (!isset($baseKeyMap["%BTNSTY%"])) $baseKeyMap["%BTNSTY%"]=(!$isInteractive)?"display:none;":"";
    if (!isset($baseKeyMap["%ERRCLOSE%"])) $baseKeyMap["%ERRCLOSE%"]="";
    $respuesta="";
    foreach ($solList as $idx => $solItem) {
        $solId=$solItem["solId"];
        $solFolio=$solItem["solFolio"];
        $subject=$solItem["subject"];
        ob_start();
        ob_implicit_flush(false);
        if (isset($respuesta[0])) echo "<hr>";
        echo "<h2>$solFolio</h2>";
        include "templates/solforma.php";
        $respactual=ob_get_clean();
        $baseKeyMap["%SOLID%"]=$solId;
        $baseKeyMap["%SOLFOLIO%"]=$solFolio;
        $respuesta.=str_replace(array_keys($baseKeyMap),array_values($baseKeyMap),$respactual);
    }
    $baseKeyMap["%RESPUESTA%"].=$respactual;
    return str_replace(array_keys($baseKeyMap),array_values($baseKeyMap),$base);
}
function getSolSingleView($solId, $solFolio, $subject) {
    $baseKeyMap=["%ENCABEZADO%"=>$subject,"%RESPUESTA%"=>"<h2>La solicitud ".($folio??$solId)." ha sido pagada</h2>","%BUTTONS%"=>"<!-- 2 -->","isInteractive"=>"0"];
    $base = file_get_contents(getBasePath()."templates/respGralSolPago.html");
    if (!isset($baseKeyMap["%HOSTNAME%"][0])) $baseKeyMap["%HOSTNAME%"]=$_SERVER["HTTP_ORIGIN"];
    if (!isset($baseKeyMap["%RESPUESTA%"])) $baseKeyMap["%RESPUESTA%"]="";
    $isInteractive = (isset($baseKeyMap["isInteractive"])&&$baseKeyMap["isInteractive"]==="1");
    if (!isset($baseKeyMap["%BTNSTY%"])) $baseKeyMap["%BTNSTY%"]=(!$isInteractive)?"display:none;":"";
    if (!isset($baseKeyMap["%ERRCLOSE%"])) $baseKeyMap["%ERRCLOSE%"]="";
    ob_start();
    ob_implicit_flush(false);
    include "templates/solforma.php";
    $baseKeyMap["%RESPUESTA%"].=ob_get_clean();
    $baseKeyMap["%SOLID%"]=$solId;
    $baseKeyMap["%SOLFOLIO%"]=$solFolio;
    return str_replace(array_keys($baseKeyMap),array_values($baseKeyMap),$base);
}
function viewTextFiles() {
    $tmpPath = dirname($_SERVER['DOCUMENT_ROOT'])."/docs/tmp/";
    $webPath="$_SERVER[HTTP_ORIGIN]/docs/tmp/";
    $tmpLen=strlen($tmpPath);
    $log="";
    foreach (glob($tmpPath."onePage*.txt") as $idx => $fileabs) {
       if (file_exists($fileabs)) {
            $handle=fopen($fileabs, "r");
            if ($handle) {
                $isRefLine=false; // $isSomething=false;
                $refStr=""; $newline=""; // $refHex="";
                $lineNum=0;
                while (($line=fgets($handle))!==false) {
                    $line=trim($line);
                    if (!isset($line[0])) continue;
                    $lineNum++;
                    $log.="IDX $idx | LINE $lineNum) $line \n";
                    try {
                        if ($isRefLine!==false) {
                            if ($isRefLine>0) {
                                $log.="RefLine=$isRefLine";
                                $pos=strpos($line, "n de Formato Libre");
                                $log.=" (POS=".($pos===false?"FALSE":$pos."/".($pos+19)." [".strlen($line)."]").")";
                                if ($pos!==false && $isRefLine>1) {
                                    $pos2=strpos($line," ",$pos+19);
                                    $log.=" (POS2=".($pos2===false?"FALSE":$pos2).")";
                                    if ($pos2!==false && $pos2>$pos) {
                                        $isRefLine=0;
                                        $line=substr($line, $pos+19, $pos2-$pos-19);
                                    }
                                }
                                $log.="\n";
                            }
                            if ($isRefLine==0 && isset($newline[0])) {
                                $solFolio=getSolFolio($line);
                                echo "<li>$newline: $solFolio</li>";
                                break;
                            }
                            $isRefLine--;
                            
                        } else {
                            $pos=strpos($line, "mero de Referencia de Transacci");
                            $log.="NewRef (POS=".($pos===false?"FALSE":$pos).")\n";
                            if ($pos!==false) { // Número de Referencia de Transacción
                                $filename=substr($fileabs, $tmpLen);
                                $fnum=substr($filename,-6, -4);
                                $fdat=substr($filename, 7, 2)."/".substr($filename, 9, 2)."/".substr($filename, 11, 2);
                                $newline="$fdat # $fnum) <a href='$webPath$filename' target='onePage'><img src='imagenes/icons/txtDoc32.png' class='btn20' title='$filename'></a>";
                                $fpdfabs=substr($fileabs, 0, -4).".pdf";
                                if (file_exists($fpdfabs)) {
                                    $fpdfname=substr($filename, -4).".pdf";
                                    $newline.="<a href='$webPath$fpdfname' target='onePage'> <img src='imagenes/icons/pdf200.png' class='btn20' title='$fpdfname'></a>";
                                }
                                $isRefLine=18;
                            }
                        }
                    } catch (Error $err) {
                        $log.="ERROR: ".getErrorData($err)."\n";
                    }
                    $log.="<br>\n";
                } // while line
                fclose($handle);
            } // if handle
        } // if file_exists textName
    }
}
function readingTextFiles($txtn) {
    foreach ($textList as $tIdx => $textname) {
        $block="";
        try {
            if (file_exists($textname)) {
                $handle=fopen($textname, "r");
                if ($handle) {
                    $log[]="Reading Text ".($tIdx+1);
                    $foundData=false; $extraData=false;
                    while (($line=fgets($handle))!==false) {
                        $line = trim(mb_convert_encoding($line, "utf-8"));
                        if (!isset($line[0])) continue;
                        if (isset($block[0])) $block.=" ";
                        $sIdx=strpos($line, " ");
                        if ($sIdx) $block.=substr($line, 0, $sIdx);
                        else $block.=$line;
                    }
                    fclose($handle);
                    $data[]=$block;
                } else {
                    $log[]="File Open Error '$textname'";
                    continue;
                }
            } else {
                $log[]="Text File wasn't created '$txtfile'";
                continue;
            }
        } catch (Exception $ex) {
            $log[]=get_class($ex).": ".json_encode(getErrorData($ex));
            continue;
        }
    }
    return ["webnames"=>$webnames, "logs"=>$log, "text"=>$data];
}
function getReqPymFolio($txt, $onlySol=false, $showIsSol=false) {
    $isSol=false;
    if (substr($txt, 0, 3)==="SOL") {
        $isSol=true;
        $txt=substr($txt, 3);
    } else if ($onlySol) return false;
    $fecha=substr($txt, 0, 4);
    $gpoCut=substr($txt, 6, 3);
    $num=substr($txt, 9);
    $padNum=str_pad(substr($txt, 9), 3, "0", STR_PAD_LEFT);
    if ($num!==$padNum) $padNum.="!";
    $txt=(($isSol&&$showIsSol)?"SOL ":"").$gpoCut.$fecha."-".$padNum;
    return $txt;
}
function getReqPymMap($fileBase, $reqList) {
    $tmpPath = dirname($_SERVER['DOCUMENT_ROOT'])."/docs/tmp/";
    $webPath="$_SERVER[HTTP_ORIGIN]/docs/tmp/";
    $tmpLen=strlen($tmpPath);
    $map=[];
    foreach (glob($fileBase."*.txt") as $idx=> $fileabs) {
        if (file_exists($fileabs)) {
            $filename=substr($fileabs, $tmpLen);
            $handle=fopen($fileabs, "r");
            if ($handle) {
                while (($line =fgets($handle))!==false) {
                    $line=trim($line);
                    if (!isset($line[0])) continue;
                    try {
                        if ($isRefLine===false) {
                            $pos=strpos($line, "mero de Referencia de Transacci");
                            if ($pos!==false) {
                                $isRefLine=18;
                                //$filename=substr($fileabs, $tmpLen);
                                //$newline="<a href='$webPath$filename' target='onePage'>".$filename."</a>";
                            }
                        } else {
                            if ($isRefLine>0) {
                                $pos=strpos($line, "n de Formato Libre");
                                if ($pos!==false && $isRefLine>1) {
                                    $pos2=strpos($line," ",$pos+19);
                                    if ($pos2!==false && $pos2>$pos) {
                                        $isRefLine=0;
                                        $line=substr($line, $pos+19, $pos2-$pos-19);
                                    }
                                }
                            }
                            // no poner else, hay mas de una forma
                            if ($isRefLine===0) {
                                $solFolio=getReqPymFolio($line);
                                if ($solFolio!==false) {
                                    if (!$reqList || in_array($solFolio, $reqlist))
                                        $map[$solFolio]=$filename; // $fileabs; // 
                                }
                                break;
                            }
                            $isRefLine--;
                        }
                    } catch (Error $err) {
                        if (!isset($map["error"])) $map["error"]=[];
                        $map["error"][]=getErrorData($err);
                    }
                    if ($isRefLine<0) {
                        if (!isset($map["error"])) $map["error"]=[];
                        $map["error"][]=["message"=>"Negative RefLine", "idx"=>$idx, "file"=>$filename];
                        break;
                    }
                }
                fclose($handle);
            } else {
                if (!isset($map["error"])) $map["error"]=[];
                $map["error"][]=["message"=>"No handle", "idx"=>$idx, "file"=>$filename];
            }
        } else {
            if (!isset($map["error"])) $map["error"]=[];
            $map["error"][]=["message"=>"No file", "idx"=>$idx, "file"=>$filename];
        }
    }
    return $map;
}

function replaceDoc() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    $esSistemas = validaPerfil(["Sistemas","Administrador"]);
    if (!$esSistemas) { echoJSDoc("error", "No tiene permiso para reemplazar documentos", null, $baseData+["line"=>__LINE__], "error"); return; }
    if (!isset($_POST["path"][0])) { echoJSDoc("error", "Debe incluir la ruta del archivo a reemplazar", null, $baseData+["line"=>__LINE__], "error"); return; }
    $path=$_POST["path"];
    if (!isset($_POST["name"][0])) { echoJSDoc("error", "Debe incluir el nombre del archivo a reemplazar", null, $baseData+["line"=>__LINE__], "error"); return; }
    $name=$_POST["name"];
    if (!isset($_FILES["file"])) { echoJSDoc("error", "Debe agregar un archivo para reemplazar el antiguo", null, $baseData+["line"=>__LINE__,"files"=>$_FILES], "error"); return; }
    $ffile=$_FILES["file"];
    if ($ffile["error"]>0) {
        $fileError=fileCodeToMessage($ffile["error"],["filename"=>$ffile["name"]]);
        if (isset($fileError)) echoJSDoc("error", $fileError, null, $baseData+["line"=>__LINE__,"post"=>$_POST,"files"=>$_FILES], "error");
        else echoJSDoc("error", "Error de archivo $ffile[error]", null, $baseData+["line"=>__LINE__,"post"=>$_POST,"files"=>$_FILES]);
        return;
    }
    $sysPath=$_SERVER['DOCUMENT_ROOT'];
    $filename=$sysPath.$path.$name.".pdf";
    if (file_exists($filename)) {
        $dt = new DateTime();
        if (!rename($filename, $sysPath.$path.$name.$dt->format("ymdHis").".pdf")) { echoJSDoc("error", "El archivo ya existe y no se puede reemplazar", null, $baseData+["line"=>__LINE__,"filename"=>$filename,"moveError"=>error_get_last()], "error"); return; }
        sleep(4);
    }
    if (move_uploaded_file($ffile["tmp_name"], $filename)===false) echoJSDoc("error", "El archivo no se pudo cargar", null, $baseData+["line"=>__LINE__,"filedata"=>$ffile,"filename"=>$filename,"moveError"=>error_get_last()], "error");
    else echoJSDoc("success", "Archivo reemplazado satisfactoriamente");
}
function getUUIDFromPDF() {
    if (isset($_FILES["archivo"])) {
        $ffile=$_FILES["archivo"];
        exec("\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$ffile[tmp_name]\" -", $output);
        //$arr=[];
        $lines=0;
        $getLine=FALSE;
        /*
        $log="";
        $hasFolio=FALSE;
        $hasCadena=FALSE;
        */
        $fulltext="";
        //$nextLines=0;
        //$fL=5;
        foreach($output as $op) {
            $lines++;
            $fixop=strtolower(str_replace(" ", "", $op));
            //$fixop=str_replace("\"", "", $fixop);
            //$fixop=str_replace("'", "", $fixop);
            //$fixop=strtolower(preg_replace('/[^\w\.\-]/','',$op));
            //$fixop=preg_replace('/[^\w]/', '', strtolower($op));
            if (!isset($fixop[0])) continue;
            $fulltext.=$fixop."\n";
            /*
            if ($fixop==="foliofiscal"||(isset($fixop[11])&&substr($fixop,0,11)==="foliofiscal")) {
                $getLine=TRUE;
                $hasFolio=TRUE;
                $nextLines=3;
                $log.="FOUND foliofiscal\n";
                if (isset($fixop[31]))
                    $fulltext.=substr($fixop,0,30)."...\n";
                else $fulltext.=$fixop."\n";
                continue;
            }*/
            if ($fixop==="cadenaoriginal"||(isset($fixop[14])&&substr($fixop,0,14)==="cadenaoriginal")) {
                $getLine=TRUE;
                $lasttrue=$fixop;
                //$hasCadena=TRUE;
                /*
                $nextLines=3;
                $log.="FOUND cadenaoriginal\n";
                if (isset($fixop[$fL+1]))
                    $fulltext.=substr($fixop,0,$fL)."...\n";
                else $fulltext.=$fixop."\n";
                */
                $match=strpos($fixop,"|1.1|");
                if ($match===false)
                    continue;
            }
            if ($getLine) {
                /*if (isset($fixop[$fL+1]))
                    $fulltext.=substr($fixop,0,$fL)."...\n";
                else $fulltext.=$fixop."\n";*/
                if (!isset($match)||$match===false) {
                    $match=strpos($fixop,"|1.1|");
                    $lastop=$fixop;
                }
                if ($match!==false) {
                    $match+=5;
                    $next=strpos($fixop,"|",$match);
                    if ($next!==false && ($next-$match)==36)
                        $val=substr($fixop,$match,36);
                    else
                        $noval=substr($fixop,$match,36);
                }
                /*if (isset($fixop[35])) {
                    $log.="FIXOP=$fixop\n";
                    if (strpos($fixop,"|")!==false) {
                        $pipeIdx=-1;
                        for($np=0;$np<4;$np++) {
                            $oldIdx=$pipeIdx+1;
                            $pipeIdx=strpos($fixop,"|",$oldIdx);
                            if ($pipeIdx===false) break;
                            $val=substr($fixop,$oldIdx,$pipeIdx-$oldIdx);
                            $log.="VAL=$val\n";
                            if (isset($val[35])) {
                                if(!isset($arr[$val])) $arr[$val]=0;
                                $arr[$val]++;
                            }
                        }
                    } else if (isset($fixop[35])) {
                        $val=substr($fixop,0,36);
                        $log.="VAL=$val\n";
                        if(!isset($arr[$val])) $arr[$val]=0;
                        $arr[$val]++;
                    }
                } else {
                    $log.="NOFIXOP35=$fixop\n";
                    if($fixop==="conceptos")
                        continue;
                }*/
                if (isset($val)) break;
                $getLine=FALSE;
            }/* else if ($nextLines>0) {
                if (isset($fixop[$fL+1]))
                    $fulltext.=substr($fixop,0,$fL)."...\n";
                else $fulltext.=$fixop."\n";
                $nextLines--;
            }*/
        }
        /*$flips=array_flip($arr);
        if (isset($flips[2])) return successMsg($flips[2]);
        */
        if (isset($val)) return successMsg($val);
        //$extraData=["flips"=>$flips,"arr"=>$arr,/*"log"=>$log,*/"name"=>$ffile["name"]];
        $extraData=["lines"=>$lines];//"text"=>$fulltext];
        if (isset($noval)) $extraData["noval"]=$noval;
        else {
            if (isset($lasttrue)) $extraData["lasttrue"]=$lasttrue;
            if (isset($lastop)) $extraData["lastop"]=$lastop;
        }
        //if ($hasFolio) $extraData["hasFolio"]="1";
        //if ($hasCadena) $extraData["hasCadena"]="1";
        /*if (isset($fulltext[0])) {
        //if ($hasFolio&&$hasCadena)
            //$extraData["text"]="#".strlen($fulltext);
            $extraData["text"]=$fulltext;
            $extraData["len"]=strlen($fulltext);
            //echo $fulltext;
        }*/
        return errMsg("No hay UUID",$extraData);
    } else return errMsg("No existe 'archivo'");
}
function errMsg($message, $data=null) {
    $retobj=["result"=>"error","message"=>$message];
    if (isset($data)) $retobj+=$data;
    return $retobj;
}
function successMsg($message, $data=null) {
    $retobj=["result"=>"success","message"=>$message];
    if (isset($data)) $retobj+=$data;
    return $retobj;
}
function descarga($dwnPath, $ext) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    $pathDwn = $path.$dwnPath;
    $extUpper = strtoupper($ext);
    $files = getFixedFileArray($_FILES["dwnfiles"]);
    $upNum = count($files); $mvNum = 0;
// echo "PATH: $pathDwn, EXT: $ext, #: $upNum<br>\n";
    for ($idx = 0; $idx < $upNum; $idx++) {
// echo " * ";
        $ffile = $files[$idx];
        if (strpos($ffile["type"],$ext)!==false) {
            move_uploaded_file($ffile["tmp_name"], $pathDwn.$ffile["name"]);
// echo "moved $ffile[tmp_name] => $pathDwn$ffile[name]";
            $mvNum++;
        }
// echo " .<br>\n";
    }
    return "PATH: $dwnPath, EXT: $ext, UPLOADED: $mvNum/$upNum<br>";
}
function extrae($dwnPath, $ext) {
    $debug = "function extrae($dwnPath, $ext)<br>";
    $path = $_SERVER['DOCUMENT_ROOT'];
    $pathDwn = $path.$dwnPath;
    $extUpper = strtoupper($ext);
    $prcNum = 0;
    $debug .= "pathDwn = $pathDwn<br>";
    $xtfiles = array_values(preg_grep("~\.".$ext."$~", scandir($pathDwn)));
    $num = count($xtfiles);
    foreach($xtfiles as $filename) {
        $debug .= ($prcNum+1).") Filename: $filename<br>";
        try {
            if ($ext=="tar") {
                $debug .= "    extract(TAR) $pathDwn$filename TO $pathDwn<br>";
                $phar = new PharData($pathDwn.$filename);
                $phar->extractTo($pathDwn, null, true);
                unset($phar);
                unlink($pathDwn.$filename);
                $prcNum++;
            } else if ($ext=="zip") {
                $debug .= "    extract(ZIP) $pathDwn$filename TO $pathDwn<br>";
                if (!isset($za)) $za = new ZipArchive;
                if($za->open($pathDwn.$filename) === TRUE) {
                    $za->extractTo($pathDwn);
                    $za->close();
                    unlink($pathDwn.$filename);
                    $prcNum++;
                }
            }
        } catch (Exception $e) {
            clog2("ERROR al extraer archivo ".print_r($filename,true).": ".$e->getMessage());
            $debug .= "    ERROR al extraer archivo ".
            _r($filename,true).": ".$e->getMessage();
        }
        clog2("Phar Extracted: [".$pathDwn.$filename."]");
    }
    $ppp = ($prcNum==1?"&oacute;":"aron"); // plural prcNum proces�/procesaron
    $ppa = ($prcNum==1?"":"s"); // plural prcNum archivo/archivos
    return "<p>Se proces$ppp $prcNum/$num archivo$ppa $extUpper</p><hr>".$debug;
}
function renombraXML($ruta) {
    if(isset($ruta) && isset($ruta[0]))
        if (substr($ruta, -1) !== "/") $ruta.="/";
    $nsc = "http://www.sat.gob.mx/TimbreFiscalDigital";
    $xml = new DOMDocument();
    $xmls = array_values(preg_grep('~\.xml$~', scandir($ruta)));
    $num = count($xmls);
    $prcNum = 0;
    foreach($xmls as $nombre) {
        if (@$xml->load($ruta.$nombre) === false) continue;
        $start = $xml->documentElement;
        if ($start==null) continue;
        $ns = $start->getAttribute("xmlns:cfdi");
        if ($ns==null) continue;
        $emisor = $xml->getElementsByTagNameNS($ns, "Emisor")->item(0);
        if ($emisor==null) continue;
        $rfcEmisor = preg_replace("/[^a-z&0-9]/i", "", $emisor->getAttribute("rfc"));
        $receptor = $xml->getElementsByTagNameNS($ns, "Receptor")->item(0);
        if ($receptor==null) continue;
        $rfcReceptor = preg_replace("/[^a-z&0-9]/i", "", $receptor->getAttribute("rfc"));
        $alias=Archivos::getGpoCodigoOpt()[$rfcReceptor];
        if ($alias==null) continue;
        $archivo = $rfcEmisor;
        $pathAlias = $ruta.$alias."/";
        if (!is_dir($pathAlias)) mkdir($pathAlias);
        $fecha = $start->getAttribute("fecha");
        if ($fecha==null) continue;
        if (isset($fecha[19])) $fecha = substr($fecha,0,19);
        $invDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $fecha);
        if ($invDate === false) continue;
        $anio = $invDate->format("Y");
        $mes = $invDate->format("m");
        $pathAnio = $pathAlias.$anio."/";
        if (!is_dir($pathAnio)) mkdir($pathAnio);
        $pathMes = $pathAnio.$mes."/";
        if (!is_dir($pathMes)) mkdir($pathMes);
        $complemento = $xml->getElementsByTagNameNS($ns,"Complemento")->item(0);
        $folio = $start->getAttribute("folio");
        if (empty($folio)||$folio=="00") {
            $timbre = $complemento->getElementsByTagNameNS($nsc, "TimbreFiscalDigital")->item(0);
            $uuid = $timbre->getAttribute("UUID");
            if (isset($uuid[4])) $folio = substr($uuid, -4);
            else if ($uuidLen > 0) $folio = $uuid;
        } else if (isset($folio[10])) $folio = substr($folio, -10);

        $tcompro = $start->getAttribute("tipoDeComprobante");
        if ($tcompro=="egreso") $folio = "NC_".$folio;

        $pathOld = $ruta.$nombre;
        $pathNew = $pathMes.$archivo."_".$folio.".xml";
        $pathOldBckDsh = str_replace("/", "\\", $pathOld);
        $pathNewBckDsh = str_replace("/", "\\", $pathNew);
        if (file_exists($pathNew)) {
            $idx = 1;
            $baseArchivo = $pathMes.$archivo."_".$folio."(";
            while(file_exists($baseArchivo.$idx.").xml")) $idx++;
            $pathNewBckDsh = str_replace("/", "\\", $baseArchivo.$idx.").xml");
        }
        rename($pathOldBckDsh, $pathNewBckDsh);
        sleep(3);
        $prcNum++;
    }
    return "<p>Se analizaron $prcNum de $num archivos XML</p>";
}
function organizaEmpresas($dwnPath) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    $pathDwn = $path.$dwnPath;
    $pathEmp = $pathDwn."empresas/";
    $pathEmpList = array_diff(scandir($pathEmp),Archivos::exceptPaths());
    $unum = [0,0]; $txt="";
    if (count($pathEmpList)>0) {
        foreach($pathEmpList as $rfc) {
            $val = Archivos::getGpoCodigoOpt()[$rfc];
            clog2("Empresa $rfc : $val");
            if ($val=="APSA") {
                $txt .= "rename $pathEmp$rfc => $pathDwn<br>";
                $apsaXML = array_diff(scandir($pathEmp.$rfc),Archivos::exceptPaths());
                foreach($apsaXML as $xmlfile) {
                    $oldFile = str_replace("/","\\", $pathEmp.$rfc."/".$xmlfile);
                    $newFile = str_replace("/","\\", $pathDwn.$xmlfile);
                    rename($oldFile, $newFile);
                    sleep(3);
                }
            }
        }
        $unum = Archivos::unlinkRecursive($pathEmp, true);
    } else {
        @rmdir($pathEmp);
        $unum[1]++;
    }
    $txt .= organizaXMLS($dwnPath);
    return "Borrados $unum[0] archivos y $unum[1] carpetas.<br>".$txt;
    
//        return "";
}
function organizaXMLs($dwnPath) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    $nsc = "http://www.sat.gob.mx/TimbreFiscalDigital";
    $xml = new DOMDocument();
    $pathDwn = $path.$dwnPath;
    $xmls = array_values(preg_grep('~\.xml$~', scandir($pathDwn)));
    $num = count($xmls);
    $prcNum = 0;
    foreach($xmls as $filename) {
        $pathFile = $pathDwn.$filename;
        if (@$xml->load($pathFile) === false) continue;
        $start = $xml->documentElement;
        if ($start==null) continue;
        $ns = $start->getAttribute("xmlns:cfdi");
        if ($ns==null) continue;
        $emisor = $xml->getElementsByTagNameNS($ns, "Emisor")->item(0);
        if ($emisor==null) continue;
        $rfcEmisor = preg_replace("/[^a-z&0-9]/i", "", $emisor->getAttribute("rfc"));
        $receptor = $xml->getElementsByTagNameNS($ns, "Receptor")->item(0);
        if ($receptor==null) continue;
        $rfcReceptor = preg_replace("/[^a-z&0-9]/i", "", $receptor->getAttribute("rfc"));
        $alias=Archivos::getGpoCodigoOpt()[$rfcReceptor];
        if ($alias==null) continue;
        $archivo = $rfcEmisor;
        $pathAlias = $pathDwn.$alias."/";
        if (!is_dir($pathAlias)) mkdir($pathAlias);
        $fecha = $start->getAttribute("fecha");
        if ($fecha==null) continue;
        if (isset($fecha[19])) $fecha = substr($fecha,0,19);
        $invDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $fecha);
        if ($invDate === false) continue;
        $anio = $invDate->format("Y");
        $mes = $invDate->format("m");
        $pathAnio = $pathAlias.$anio."/";
        if (!is_dir($pathAnio)) mkdir($pathAnio);
        $pathMes = $pathAnio.$mes."/";
        if (!is_dir($pathMes)) mkdir($pathMes);
        $complemento = $xml->getElementsByTagNameNS($ns,"Complemento")->item(0);
        $folio = $start->getAttribute("folio");
        if (empty($folio)||$folio=="00") {
            $timbre = $complemento->getElementsByTagNameNS($nsc, "TimbreFiscalDigital")->item(0);
            $uuid = $timbre->getAttribute("UUID");
            if (isset($uuid[4])) $folio = substr($uuid, -4);
            else if ($uuidLen > 0) $folio = $uuid;
        } else if (isset($folio[10])) $folio = substr($folio, -10);
        $pathOld = $pathFile;
        $pathNew = $pathMes.$archivo."_".$folio.".xml";
        $pathOldBckDsh = str_replace("/", "\\", $pathOld);
        $pathNewBckDsh = str_replace("/", "\\", $pathNew);
        if (file_exists($pathNew)) {
            $idx = 1;
            $baseArchivo = $pathMes.$archivo."_".$folio."(";
            while(file_exists($baseArchivo.$idx.").xml")) $idx++;
            $pathNewBckDsh = str_replace("/", "\\", $baseArchivo.$idx.").xml");
        }
        rename($pathOldBckDsh, $pathNewBckDsh);
        sleep(3);
        $prcNum++;
    }
    return "<p>Se analizaron $prcNum de $num archivos XML</p>";
}
// Se restaura archivo sellado eliminado
function recuperaSello() {
    $solId = $_POST["solId"]??"";
    $result="unknown";
    if (isset($solId[0])) {
        global $solObj;
        if (!isset($solObj)) {
            require_once "clases/SolicitudPago.php";
            $solObj=new SolicitudPago();
        }
        $solData=$solObj->getData("id=$solId");
        if (isset($solData[0])) {
            $solData=$solData[0];
            $solStatus=+$solData["status"];
            $solProceso=+$solData["proceso"];
            if ($solStatus>=SolicitudPago::STATUS_CANCELADA) {
                $error="La solicitud ya está cancelada";
                doclog("No se recupera archivo sellado porque la solicitud está cancelada","error",["solId"=>$solId,"status"=>$solStatus]);
            } else if ($solStatus>=SolicitudPago::STATUS_PAGADA) {
                $error="La solicitud ya está pagada";
                doclog("No se recupera archivo sellado porque la solicitud está pagada","error",["solId"=>$solId,"status"=>$solStatus]);
            } else if ($solProceso>=SolicitudPago::PROCESO_PAGADA) {
                $error="La solicitud ya está en proceso de pago";
                doclog("No se recupera archivo sellado porque el proceso de la solicitud está en 'pagada'","error",["solId"=>$solId,"status"=>$solStatus,"proceso"=>$solProceso]);
            } else if (isset($solData["idFactura"][0])) {
                $invId=$solData["idFactura"];
                global $invObj;
                if (!isset($invObj)) {
                    require_once "clases/Facturas.php";
                    $invObj=new Facturas();
                }
                $invData=$invObj->getData("id=$invId");
                if (isset($invData[0])) {
                    $invData=$invData[0];
                    $ubicacion=$invData["ubicacion"];
                    $nombrepdf="ST_".$invData["nombreInternoPDF"];
                    if ($invData["tieneSello"]==="1" && $invData["selloImpreso"]==="1") {
                        $resQuery="UPDATE facturas SET selloImpreso=0 WHERE id=$invId";
                    } else {
                        if ($invData["tieneSello"]!=="1") {
                            $error="La solicitud no ha recibido sello de pago";
                            doclog("La factura en la solicitud no ha tenido sello","error",["solId"=>$solId,"invId"=>$invId,"tieneSello"=>$invData["tieneSello"],"selloImpreso"=>$invData["selloImpreso"],"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
                        } else { // selloImpreso !== 1
                            $error="La solicitud ya tiene sello de pago activo";
                            doclog("La factura en la solicitud tiene sello de pago activo","error",["solId"=>$solId,"invId"=>$invId,"tieneSello"=>$invData["tieneSello"],"selloImpreso"=>$invData["selloImpreso"],"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
                        }
                    }
                } else {
                    $error="La factura en la solicitud no existe";
                    doclog("La factura en la solicitud no existe","error",["solId"=>$solId,"invId"=>$invId]);
                }
            } else if (isset($solData["idOrden"][0])) {
                $ordId=$solData["idOrden"];
                global $ordObj;
                if (!isset($ordObj)) {
                    require_once "clases/OrdenesCompra.php";
                    $ordObj=new OrdenesCompra();
                }
                $ordData=$ordObj->getData("id=$ordId");
                if (isset($ordData[0])) {
                    $ordData=$ordData[0];
                    $ubicacion=$ordData["rutaArchivo"];
                    $nombrepdf="ST_".$ordData["nombreArchivo"];
                    if ($ordData["tieneSello"]==="1" && $ordData["selloImpreso"]==="1") {
                        $resQuery="UPDATE ordenescompra SET selloImpreso=0 WHERE id=$ordId";
                    } else {
                        if ($ordData["tieneSello"]!=="1") {
                            $error="La solicitud no ha recibido sello de pago";
                            doclog("La orden de compra en la solicitud no ha tenido sello","error",["solId"=>$solId,"ordId"=>$ordId,"tieneSello"=>$ordData["tieneSello"],"selloImpreso"=>$ordData["selloImpreso"],"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
                        } else { // selloImpreso !== 1
                            $error="La solicitud ya tiene sello de pago activo";
                            doclog("La orden de compra en la solicitud tiene sello de pago activo","error",["solId"=>$solId,"ordId"=>$ordId,"tieneSello"=>$ordData["tieneSello"],"selloImpreso"=>$ordData["selloImpreso"],"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
                        }
                    }
                } else {
                    $error="La orden de compra en la solicitud no existe";
                    doclog("La orden de compra en la solicitud no existe","error",["solId"=>$solId,"ordId"=>$ordId]);
                }
            }
        }
    }
    if (empty($resQuery)) {
        if (!isset($error[0])) {
            $error="No tiene sello";
            doclog("La solicitud no tiene sello válido","error",["solId"=>$solId]);
        }
    } else {
        DBi::autocommit(false);
        $resq=DBi::query($resQuery);
        $rows=DBi::$affected_rows;
        if (is_object($resq)) $resq->close();
        $errno=DBi::getErrno();
        $errtx=DBi::getError();
        if ($errno>0) {
            $error="No fue posible restaurar el archivo sellado";
            doclog("Error al actualizar base de datos","error",["solId"=>$solId,"query"=>$resQuery,"rows"=>$rows,"errno"=>$errno,"error"=>$errtx,"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
        } else if ($rows<=0) {
            $error="Archivo sellado previamente registrado";
            doclog("No hubo actualización en base de datos","error",["solId"=>$solId,"query"=>$resQuery,"rows"=>$rows,"errno"=>$errno,"error"=>$errtx,"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
        } else {
            $lookoutFilePath = "";
            if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT']))
                $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
            else if (!empty($_SERVER['DOCUMENT_ROOT']))
                $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
            $archivo=$lookoutFilePath.$ubicacion.$nombrepdf;
            if (file_exists($archivo.".pdfx")) {
                if (rename($archivo.".pdfx",$archivo.".pdf")) {
                    $result="success";
                    $href=$ubicacion.$nombrepdf.".pdf";
                } else {
                    $error="No fue posible restaurar el archivo sellado";
                    doclog("No se pudo renombrar el archivo sellado","error",["solId"=>$solId,"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf,"moveError"=>error_get_last()]);
                }
            } else {
                $error="No fue posible restaurar el archivo sellado";
                doclog("No existe el archivo sellado","error",["solId"=>$solId,"ubicacion"=>$ubicacion,"pdf"=>$nombrepdf]);
            }
        }
    }
    if (isset($error[0])) $result="error";
    $retJS=["result"=>$result,"message"=>"Proceso para recuperar PDF sellado concluido"];
    if (isset($error[0])) $retJS["message"]=$error;
    if (isset($href)) $retJS["href"]=$href;
    if (!empty($resQuery)) {
        if ($result==="success") DBi::commit();
        else DBi::rollback();
        DBi::autocommit(true);
    }
    echo json_encode($retJS);
}
// Se eliminara archivo sellado en 5 minutos
function rompeSello() {
    $solId = $_POST["solId"]??"";
    $result="unknown";
    $error="";
    doclog("INI rompeSello","archivo",["solId"=>$solId]);
    if (isset($solId[0])) {
        global $solObj;
        if (!isset($solObj)) {
            require_once "clases/SolicitudPago.php";
            $solObj=new SolicitudPago();
        }
        $solData=$solObj->getData("id=$solId");
        if (isset($solData[0])) {
            $solData=$solData[0];
            if (isset($solData["idFactura"][0])) {
                $invId=$solData["idFactura"];
                global $invObj;
                if (!isset($invObj)) {
                    require_once "clases/Facturas.php";
                    $invObj=new Facturas();
                }
                $invData=$invObj->getData("id=$invId");
                if (isset($invData[0])) {
                    $invData=$invData[0];
                    $tieneSello=$invData["tieneSello"];
                    $ubicacion=$invData["ubicacion"];
                    $nombrepdf="ST_".$invData["nombreInternoPDF"];
                    $delQuery="UPDATE facturas SET selloImpreso=1 WHERE id=$invId";
                }
            } else if (isset($solData["idOrden"][0])) {
                $ordId=$solData["idOrden"];
                global $ordObj;
                if (!isset($ordObj)) {
                    require_once "clases/OrdenesCompra.php";
                    $ordObj=new OrdenesCompra();
                }
                $ordData=$ordObj->getData("id=$ordId");
                if (isset($ordData[0])) {
                    $ordData=$ordData[0];
                    $tieneSello=$ordData["tieneSello"];
                    $ubicacion=$ordData["rutaArchivo"];
                    $nombrepdf="ST_".$ordData["nombreArchivo"];
                    $delQuery="UPDATE ordenescompra SET selloImpreso=1 WHERE id=$ordId";
                }
            }
        }
    }
    doclog("QRY rompeSello","archivo",["solId"=>$solId,"tieneSello"=>$tieneSello,"ubicacion"=>$ubicacion,"nombrepdf"=>$nombrepdf,"delQuery"=>$delQuery]);
    if (empty($tieneSello)) {
        $result="error";
        $error="No tiene sello";
    } else {
        global $evtObj;
        if (!isset($evtObj)) {
            require_once "clases/Eventos.php";
            $evtObj=new Eventos();
        }
        $rv=$evtObj->borraArchivo($ubicacion.$nombrepdf.".pdf",300); // Borrar archivo en 300 segundos
        // toDo: en $evtObj se traducirá el mensaje de error de la base de datos en un texto entendible para el usuario
        doclog("RV borraArchivo","archivo",["solId"=>$solId,"delQuery"=>$delQuery,"rv"=>is_bool($rv)?($rv?"true":"false"):$rv]);
        if (is_bool($rv)) { // 
            if (isset($delQuery[0])) $evtObj->ejecuta($delQuery,300);
            global $prcObj;
            if (!isset($prcObj)) {
                require_once "clases/Proceso.php";
                $prcObj=new Proceso();
            }
            $prcObj->cambiaSolicitud($solId,"rompeSello","evento en 5 minutos");
            doclog("Borrado de archivo programado en 5 min","eventos",["ubicacion"=>$ubicacion,"pdf"=>$nombrepdf,"delQuery"=>$delQuery,"segundos"=>300]);
            if ($rv) $result="success";
            else $result="ignore";
        } else { $result="error"; $error=$rv; }
    }
    $result=["result"=>$result,"message"=>"consultas/Archivos->rompeSello: ".($ubicacion??"").($nombrepdf??"").".pdf"];
    if (isset($error[0])) $result["error"]=$error;
    echo json_encode($result);
}
function updateTaxStatusProof() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    $esDesarrollo=in_array(getUser()->nombre, ["admin","sistemas"]);
    $esSistemas = validaPerfil(["Sistemas","Administrador"]);
    $ffiles=$esDesarrollo?getFixedFileArray($_FILES["file"]):[$_FILES["file"]];
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $docPath=dirname($docRoot)."/docs/csf/";
    $tmpPath=dirname($docRoot)."/docs/tmp/";
    $trace=[];
    $gt=microtime(true);
    exec("ForFiles /p \"C:\\Apache24\\htdocs\\docs\\tmp\" /s /d -3 /c \"cmd /c del /q @file\""); // elimina archivos temporales
    //$trace[]="Eliminando archivos de hace 3 dias en docs/tmp con ForFiles";
    sleep(2);
    // ||2025/03/03|AEL9706303G5|CONSTANCIA DE SITUACIÓN FISCAL
    // ||AEL9706303G5|25NB1791840|03-03-2025|P||00001088888800000031||
    foreach ($ffiles as $ffidx => $ffval) {
        $ft=microtime(true);
        $pdfname=$ffval["name"];
        $basname=substr($pdfname, 0, -4);
        $tmpname=$ffval["tmp_name"];
        $txtname=$basname.".txt";
        $tmpfile=$tmpPath.$pdfname;
        $txtfile=$tmpPath.$txtname;
        $r1=false; $r2=false;
        if (file_exists($tmpfile)) {
            $r1=unlink($tmpfile);
            if (!$r1) $trace[]="Archivo No Eliminado: $tmpfile";
        }
        if (file_exists($txtfile)) {
            $r2=unlink($txtfile);
            if (!$r2) $trace[]="Archivo No Eliminado: $txtfile";
        }
        if ($r1!==false||$r2!==false) sleep(2);
        $loaded=move_uploaded_file($ffval["tmp_name"], $tmpfile);
        try {
            $dt=false;
            $cmd="\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$tmpfile\"";
            $lastline=exec($cmd);
            sleep(1);
            if (file_exists($txtfile)) {
                $handle=fopen($txtfile, "r");
                if ($handle) {
                    $foundData=false;$extraData=false;
                    while (($line=fgets($handle))!==false) {
                        $line = trim(mb_convert_encoding($line, "utf-8"));
                        if (!isset($line[0])) continue;
                        if ($foundData===false) {
                            if (strpos($line,constant("FDATC")["CSF"])!==false) {
                                $dt=microtime(true);
                                $foundData="CSF";
                            } else if (strpos($line,constant("FDATC")["OCOF"])!==false) {
                                $dt=microtime(true);
                                $foundData="OCOF";
                            }
                        } else {
                            switch($foundData) {
                                case "CSF":
                                    if (strpos($line,constant("FDATC")["CST"])!==false)
                                        $foundData="CST";
                                    if (strpos($line,constant("FDATC")["CO"])!==false)
                                        $foundData="CO";
                                    break;
                                case "OCOF":
                                    if (strpos($line,constant("FDATC")["CO"])!==false) {
                                        $data=explode("|", $line);
                                        $foundData="CO";
// OCOF: [0], [1], [2] gpoRfc, [3], [4] dd-mm-yyyy, [5]
                                        $trace[]="FOUND OCOF: ".$data[2]." | ".$data[4]." | ".(constant("SENTIDOP")[$data[5]]??"'".$data[5]."'").($dt!==false?", DDURATION: ".number_format(microtime(true)-$dt,4):"");
                                        $foundData=false; $dt=false;
                                    }
                                    break;
                                case "CST":
                                    $extraData=$line;
                                    $foundData="CSF";
                                    break;
                                case "CO":
                                    $data=explode("|", $line);
// CSF: [0], [1], [2] yyyy/mm/dd, [3] gpoRfc, extraData: POSITIVO, dt: duration
                                    $trace[]="FOUND CSF: ".$data[3]." | ".$data[2].($extraData!==false?" | ".$extraData:"").($dt!==false?", DDURATION: ".number_format(microtime(true)-$dt,4):"");
                                    $foundData=false; $extraData=false; $dt=false;
                                    break;
                            }
                        }
                    }
                    fclose($handle);
                } else {
                    //echoJSDoc("error","File Open Error",null,null,"archivo");
                    $trace[]="File Open Error '$txtfile'";
                    return false;
                }
            } else {
                //echoJSDoc("error","Textfile wasn't created",null,null,"archivo");
                $trace[]="Text File wasn't created '$txtfile'";
                return false;
            }
        } catch(Exception $ex) {
            //echoJSDoc("error","Excepcion en EXEC",null,["tmpfile"=>$tmpfile,"exists"=>(file_exists($tmpfile)?"SI":"NO"),"loaded"=>($loaded?"SI":"NO"),"exception"=>getErrorData($ex)],"archivo");
            $trace[]=get_class($ex).": ".json_encode(getErrorData($ex));
            return false;
        } finally {
            $trace[]="FILE '$basname' FDURATION: ".number_format(microtime(true)-$ft,4)."s";
        }
    }
    if (count($ffiles)==2) {
        //unir 2 pdf
    }
    echoJSDoc("trace","TRACE",null,["trace"=>$trace,"gduration"=>number_format(microtime(true)-$gt,4)],"archivo");
}
function processFFile($ffidx, $ffile, $alias, $gpoId) {
    echoJSDoc("upkeep", "INITIAL DATA READY", $incSep, ["alias"=>$alias,"gpoId"=>$gpoId,"pdfname"=>$tmpfile,"txtname"=>$txtfile], "archivo");
    if (file_exists($tmpfile)) {
        if (unlink($tmpfile)) echoJSDoc("upkeep","Old PDF file deleted",$incSep,["filename"=>$tmpfile],"archivo");
        else echoJSDoc("upkeep","Old PDF file can't be deleted",$incSep,["filename"=>$tmpfile],"archivo"); }
    if (file_exists($txtfile)) {
        if (unlink($txtfile)) echoJSDoc("upkeep","Old Text file deleted",$incSep,["filename"=>$txtfile],"archivo");
        else echoJSDoc("upkeep","Old Text file can't be deleted",$incSep,["filename"=>$tmpfile],"archivo"); }
    sleep(3);
    $loaded=move_uploaded_file($ffile["tmp_name"], $tmpfile);
    $output="";
    try {
        $cmd="\"C:\\Program Files\\XPdfTools\\bin64\\pdftotext.exe\" \"$tmpfile\"";
        $lastline=exec($cmd);
        sleep(1);
        if (file_exists($txtfile)) {
            $handle=fopen($txtfile, "r");
            if ($handle) {
                $found=false;
                $datetext="";
                $datepath="";
                while (($line=fgets($handle))!==false) {
                    $line = mb_convert_encoding($line, "utf-8");
                    if ($found) {
                        $datetext.=" ".trim($line);
                        // ToDo: En lugar de buscar el año, usar una expresión regular para extraer dia, mes y año. Sólo aceptar del año pasado si el mes actual es enero y si el mes en el texto es diciembre.
                        $yr=date("Y"); // 2023
                        $yrlen=4;
                        $yrpos=strpos($datetext,$yr); // false
                        if ($yrpos===false && isset($yr[3])) {
                            $yr2=$yr[0].$yr[1]." ".substr($yr, 2); // 20 23
                            $yrpos=strpos($datetext,$yr2);
                            if($yrpos!==false) {
                                $yr=$yr2;
                                $yrlen=5;
                            }
                        }
                        if ($yrpos===false) {
                            $lastyr="".((+$yr)-1);
                            $yrpos=strpos($datetext,$lastyr);
                            if ($yrpos===false && isset($lastyr[3])) {
                                $lastyr=$lastyr[0].$lastyr[1]." ".$lastyr[2].$lastyr[3];
                                $yrpos=strpos($datetext,$lastyr);
                                if ($yrpos!==false) $yrlen=5;
                            }
                            echoJSDoc("upkeep","Last year check",$incSep,["line"=>$line,"datetext"=>$datetext,"yrpos"=>$yrpos,"yr"=>$yr,"lastyr"=>$lastyr],"archivo");
                            if ($yrpos!==false) $yr=$lastyr;
                        }
                        if ($yrpos===false) {
                            if (isset($datetext[25])) break;
                            else continue;
                        }
                        $monpos=strpos($datetext," DE ");
                        if ($monpos===false) {
                            if (isset($datetext[25])) break;
                            else continue;
                        }
                        $monthname=substr($datetext, $monpos+4,$yrpos-$monpos-8);
                        $datepath=getMonthNumber($monthname);
                        if ($datepath===false) {
                            echoJSDoc("upkeep","Date Path Failed",null,["line"=>$line,"datetext"=>$datetext,"yrpos"=>$yrpos,"monpos"=>$monpos,"monthname"=>$monthname],"archivo");
                            return false;
                        }
                        $datepath=$yr."/".$datepath."/";
                        //if (!file_exists($docPath.$yr)) mkdir($docPath.$yr, 0644, true);
                        if (!file_exists($docPath.$datepath)) mkdir($docPath.$datepath, 0644, true);
                        break;
                    }
                    $pos=strpos($line, "Lugar y Fecha");
                    if ($pos!==false) {
// ToDo: buscar año actual, si se encuentra en la misma linea se copia subtexto sólo hasta el año y luego se hace break sino sólo se cambia found a true
                        $states=["MEXICO","HIDALGO","NUEVO LEON"];
                        $pos2=false; $poslen=-1;
                        for ($i=0;!$pos2&&isset($states[$i]); $i++) { 
                            $pos2=strpos($line, $states[$i]." A ");
                            if ($pos2!==false) $poslen=strlen($states[$i])+3;
                        }
                        if ($pos2!==false) {
                            $datetext=trim(substr($line,$pos2+$poslen));
                        } else {
                            $datetext=trim($line);
                            break;
                        }
                        $found=true;
                    }
                }
                fclose($handle);
// ToDo: si found es false hacer return false pero result=askYear. En javascript mostrar dialogo donde se pida el mes y año y hacer nuevo submit para copiar archivo a la ruta especificada con el mes y el año. Si found es true se calcula mes y año para copiar arcihvo a la ruta calculada.
                //echoJSDoc("upkeep","File Parsed",$incSep,["datetext"=>$datetext,"found"=>($found?"true":"false")],"archivo");
                if ($found && isset($datepath[0])) {
                    echoJSDoc("upkeep","File Parsed",$incSep,["datefound"=>$datetext,"dir"=>$datepath],"archivo");
                    $fileabs=$docPath.$datepath.$alias.".pdf";
                    if (file_exists($fileabs)) {
                        rename($fileabs,$docPath.$datepath.$alias.date("_dHis", filemtime($fileabs)).".pdf");
                        sleep(3);
                        $docTimes++;
                        $aliasN="{$alias}_{$docTimes}";
                        $fileabs=$docPath.$datepath.$aliasN.".pdf";
                    }
                    rename($tmpfile, $fileabs);
                    
                    global $gpoObj;
                    if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
                    $gpoObj->saveRecord(["id"=>$gpoId,"conSitFis"=>$datepath,"conSitFisTimes"=>$docTimes]);
                    
                    global $prcObj;
                    if (!isset($prcObj)) { require_once "clases/Proceso.php"; $prcObj=new Proceso(); }
                    $prcObj->alta("Grupo",$gpoId,"Actualiza",$datepath.$aliasN);
                    echoJSDoc("success","File Saved",null,["id"=>$gpoId,"alias"=>$aliasN,"path"=>$datepath],"archivo");
                    return true;
                } else {
                    echoJSDoc("error","Date Path Failed",null,["loaded"=>($loaded?"SI":"NO"),"found"=>($found?"TRUE":"FALSE"),"datetext"=>$datetext,"datepath"=>$datepath],"archivo");
                    return false;
                }
                //echoJSDoc("upkeep",$datetext,$incSep,null,"archivo"]);
            } else {
                echoJSDoc("error","File Open Error",null,null,"archivo");
                return false;
            }
        } else {
            echoJSDoc("error","Textfile wasn't created",null,null,"archivo");
            return false;
        }
        //echoJSDoc("upkeep","AFTER READING TEXT",$incSep,null,"archivo"]);
    } catch(Exception $ex) {
        echoJSDoc("error","Excepcion en EXEC",null,["tmpfile"=>$tmpfile,"exists"=>(file_exists($tmpfile)?"SI":"NO"),"loaded"=>($loaded?"SI":"NO"),"exception"=>getErrorData($ex)],"archivo");
        return false;
    }
    //echoJSDoc("upkeep","READY TO CONTINUE",$incSep,null,"archivo"]);
    /*
    $show=0;
    $text="";
    foreach($output as $op) {
        if (strpos($op, "/Lugar y Fecha de Emis/")!==false) $show=3;
        if ($show>0) {
            $text.=$op;
            $show--;
            if ($show==0) break;
        }
    }
    if (!isset($text)) echoJSDoc("error", "No se encontró la fecha", null, null, "error");
    echoJSDoc("success","Texto obtenido", null, ["text"=>$text]);
    return true;
    */
    /*if (empty($output)) {
        echoJSDoc("error","No se encontró contenido",$incSep,["tmpfile"=>$tmpfile,"exists"=>(file_exists($tmpfile)?"SI":"NO"),"loaded"=>($loaded?"SI":"NO")],"archivo");
        return false;
    }
    if (isset($output[100])) $output=substr($output, 0, 100)."...";
    echoJSDoc("upkeep","READY FOR SUCCESS!",$incSep,["output"=>$output],"archivo");
    echoJSDoc("success","Texto obtenido con exito",null,["tmpfile"=>$tmpfile,"output"=>$output,"exists"=>(file_exists($tmpfile)?"SI":"NO"),"loaded"=>($loaded?"SI":"NO")],"archivo");
    return true;*/
/*
    // toDo: obtener fecha del texto del pdf
                        // OBTENIDO DE configuracion/registro.php | 292
        $filepath="";
        $prvOpinion="";
        $output=[];
        $dbsatpg=0;
        $doSavePrv=false;
        $fldarr=[];
        // modificar siguiente codigo para leer la fecha dentro de una Constancia de Situacion Fiscal: "Lugar y Fecha de Emisión".__MUNICIPIO__." A ".__DIA__." DE ".__MES__." DE ".__AÑO__
        // generar nuevo path = año."/".num(mes)."/"
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
*/
}
function removeBGDocs() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    $type=$_POST["type"]??"";
    $solId=$_POST["solid"]??"";
    if (!isset($solId[0])) { echoJSDoc("error", "No se recibi&oacute; identificador de Solicitud de pago", null, $baseData+["line"=>__LINE__], "error"); return; }
    if (!isset($type[0])) { echoJSDoc("error", "No se registró lo que desea eliminar", null, $baseData+["line"=>__LINE__], "error"); return; }
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj=new SolicitudPago();
    }
    global $query;
    if ($type==="Todas") {
        if (!$solObj->saveRecord(["id"=>$solId,"archivoAntecedentes"=>null])) { echoJSDoc("error", "No se pudo guardar la solicitud $solId", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
        echo json_encode(["result"=>"success"]);
    } else {
        $currPage=$_POST["currPage"]??1;
        $lastPage=$_POST["lastPage"]??1;
        $iniPage=$_POST["iniPage"]??$currPage;
        $endPage=$_POST["endPage"]??$lastPage;
        global $query;
        $solData=$solObj->getData("id=$solId");
        if (!isset($solData[0]["id"][0])) { echoJSDoc("error", "Solicitud desconocida", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
        $solData=$solData[0];
        $bgName="sol{$solId}BG";
        if (isset($solData["idFactura"][0])) {
            $bgName.="F";
            global $invObj;
            if (!isset($invObj)) {
                require_once "clases/Facturas.php";
                $invObj=new Facturas();
            }
            $invData=$invObj->getData("id=$solData[idFactura]");
            if (!isset($invData[0]["id"][0])) { echoJSDoc("error", "Factura desconocida", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
            $invData=$invData[0];
            $path=$invData["ubicacion"];
        } else if (isset($solData["idOrden"][0])) {
            $bgName.="O";
            global $ordObj;
            if (!isset($ordObj)) {
                require_once "clases/OrdenesCompra.php";
                $ordObj=new OrdenesCompra();
            }
            $ordData=$ordObj->getData("id=$solData[idOrden]");
            if (!isset($ordData[0]["id"][0])) { echoJSDoc("error", "Orden de Compra desconocida", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
            $ordData=$ordData[0];
            $path=$ordData["rutaArchivo"];
        } else { echoJSDoc("error", "Solicitud sin factura ni orden de compra", null, $baseData+["line"=>__LINE__], "error"); return; }
        $docRoot = $_SERVER["DOCUMENT_ROOT"];
        if (isset($solData["archivoAntecedentes"][0])) {
            $bgNum=intval(substr($solData["archivoAntecedentes"], strlen($bgName)))+1;
            $bgName.="$bgNum";
            $currAbsFileName=$docRoot.$path.$solData["archivoAntecedentes"].".pdf";
            require_once "clases/PDF.php";
            $pdfObj=PDF::getImprovedFile($currAbsFileName);
            if (!isset($pdfObj)) { echoJSDoc("error", isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado", null, $baseData+["line"=>__LINE__,"solId"=>$solId,"pdfName"=>$currAbsFileName]+PDF::$errdata, "error"); return; }
            if ($iniPage>$endPage) {
                $auxPage=$iniPage;
                $iniPage=$endPage;
                $endPage=$auxPage;
            }
            $pageCount=$pdfObj->pageCount;
            if ($iniPage<1) $iniPage=1;
            if ($endPage>$pageCount) $endPage=$pageCount;
            require_once "clases/Proceso.php";
            global $prcObj;
            if (!isset($prcObj)) $prcObj=new Proceso();
            $delPages=1+$endPage-$iniPage;
            if ($iniPage==1 && $endPage==$pdfObj->pageCount) {
                if (!$solObj->saveRecord(["id"=>$solId,"archivoAntecedentes"=>null])) { echoJSDoc("error", "No se pudo guardar la solicitud $solId", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
                $prcObj->alta("SolPago",$solId,"Antecedentes","Elimina {$delPages}/{$pageCount} hojas");
                echo json_encode(["result"=>"success","path"=>$path]);
            } else {
                $delNumPg=1+$endPage-$iniPage;
                $newNumPg=$pageCount-$delNumPg;
                $pdfObj->saveDelPageFile($iniPage,$endPage,$bgName.".pdf");
                if (!$solObj->saveRecord(["id"=>$solId,"archivoAntecedentes"=>$bgName])) { echoJSDoc("error", "No se pudo guardar el archivo $ffile[name]",null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
                $retArr=["result"=>"success","path"=>$path, "name"=>$bgName];
                if ($currPage>$newNumPg) $retArr["currentPage"]=$newNumPg;
                $prcObj->alta("SolPago",$solId,"Antecedentes","Elimina {$delPages}/{$pageCount} hojas");
                echo json_encode($retArr);
            }
        } else echoJSDoc("error", "El documento fue eliminado previamente", null, ["action"=>"clearpdf"], false);
    }
}
function addBGDocs() {
    $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__]+$_POST;
    if (!isset($_POST["solid"][0])) { echoJSDoc("error", "No se recibi&oacute; identificador de Solicitud de pago", null, $baseData+["line"=>__LINE__], "error"); return; }
    $solId=$_POST["solid"];
    if (!isset($_FILES["file"])) { echoJSDoc("error", "No se recibi&oacute; archivo para anexar a la Solicitud de Pago", null, $baseData+["line"=>__LINE__], "error"); return; }
    $ffile=$_FILES["file"];
if (!isset($ffile["name"])) { echoJSDoc("error", "No se recibi&oacute; archivo para anexar a la Solicitud de Pago", null, $baseData+["line"=>__LINE__,"filedata"=>$ffile], "error"); return; }
    require_once "clases/Archivos.php";
    $errMsg=Archivos::getUploadError($ffile, "application/pdf");
    if (isset($errMsg[0])) { echoJSDoc("error", $errMsg, null, $baseData+["line"=>__LINE__], "error"); return; }
    $destination="E:\\FACTURAS\\temp\\".$ffile["name"];
    if (!move_uploaded_file($ffile["tmp_name"], $destination)) { echoJSDoc("error", "El archivo PDF no se pudo descargar", null, $baseData+["line"=>__LINE__,"moveError"=>error_get_last(),"filedata"=>$ffile], "error"); return; }
    chmod($destination, 0777);
    require_once "clases/PDF.php";
    $pdfObj=PDF::getImprovedFile($destination);
    if (!isset($pdfObj)) { echoJSDoc("error", isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado", null, $baseData+["line"=>__LINE__,"solId"=>$solId,"pdfName"=>$destination]+PDF::$errdata, "error"); return; }
    $addingPages=$pdfObj->pageCount;
    $prePg=$_POST["prevPage"]??1;
    global $solObj;
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj=new SolicitudPago();
    }
    global $query;
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0]["id"][0])) { echoJSDoc("error", "Solicitud desconocida", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
    $solData=$solData[0];
    $bgName="sol{$solId}BG";
    if (isset($solData["idFactura"][0])) {
        $bgName.="F";
        global $invObj;
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        }
        $invData=$invObj->getData("id=$solData[idFactura]");
        if (!isset($invData[0]["id"][0])) { echoJSDoc("error", "Factura desconocida", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
        $invData=$invData[0];
        $path=$invData["ubicacion"];
    } else if (isset($solData["idOrden"][0])) {
        $bgName.="O";
        global $ordObj;
        if (!isset($ordObj)) {
            require_once "clases/OrdenesCompra.php";
            $ordObj=new OrdenesCompra();
        }
        $ordData=$ordObj->getData("id=$solData[idOrden]");
        if (!isset($ordData[0]["id"][0])) { echoJSDoc("error", "Orden de Compra desconocida", null, $baseData+["line"=>__LINE__,"query"=>$query], "error"); return; }
        $ordData=$ordData[0];
        $path=$ordData["rutaArchivo"];
    } else { echoJSDoc("error", "Solicitud sin factura ni orden de compra", null, $baseData+["line"=>__LINE__], "error"); return; }
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    if (isset($solData["archivoAntecedentes"][0])) {
        $bgNum=intval(substr($solData["archivoAntecedentes"], strlen($bgName)))+1;
        $bgName.="$bgNum";
        $currAbsFileName=$docRoot.$path.$solData["archivoAntecedentes"].".pdf";
        $pdfObj=PDF::getImprovedFile($currAbsFileName);
        if (!isset($pdfObj)) { echoJSDoc("error", isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo PDF no fue creado", null, $baseData+["line"=>__LINE__,"solId"=>$solId,"pdfName"=>$currAbsFileName]+PDF::$errdata, "error"); return; }
        $oldPageCount=$pdfObj->pageCount;
        $pdfObj->saveMergedFile($destination,$prePg,$bgName.".pdf");
    } else {
        $globStr=$docRoot.$path.$bgName;
        $globLen=strlen($globStr);
        $maxNum=0;
        $bgHistory=glob("{$globStr}*.pdf");
        foreach ($bgHistory as $idx => $value) {
            $num=intval(substr($value, $globLen, -4));
            if ($num>$maxNum) $maxNum=$num;
        }
        $maxNum++;
        $bgName.="$maxNum";
        if(rename($destination, $docRoot.$path.$bgName.".pdf")===false) { echoJSDoc("error", "No se pudo guardar el archivo", null, $baseData+["line"=>__LINE__,"bgname"=>$bgName,"moveError"=>error_get_last(),"tmpname"=>$destination,"filedata"=>$ffile], "error"); return; }
        $oldPageCount=0;
    }
    if (!$solObj->saveRecord(["id"=>$solId,"archivoAntecedentes"=>$bgName])) { echoJSDoc("error", "No se pudo guardar el archivo", null, $baseData+["line"=>__LINE__,"tmpname"=>$destination,"query"=>$query,"filedata"=>$ffile]); return; }
    $newPageCount=$oldPageCount+$addingPages;
    require_once "clases/Proceso.php";
    global $prcObj;
    if (!isset($prcObj)) $prcObj=new Proceso();
    $pl_s=$addingPages==1?"":"s";
    $prcObj->alta("SolPago",$solId,"Antecedentes",$oldPageCount==0?"Nuevo $addingPages hoja{$pl_s}":"Agrega {$addingPages} hoja{$pl_s}, ahora son {$newPageCount}");
    echo json_encode(["result"=>"success","path"=>$path, "name"=>$bgName]);
}
