<?php
require_once dirname(__DIR__)."/bootstrap.php";
//clog2ini("Archivos");
//clog1seq();

class Archivos {
    function __construct() {
        //clog2("Archivos construct");
    }

    public static function getGpoCodigoOpt() {
        global $gpoObj;
        if (!isset($gpoObj)) {
            require_once "clases/Grupo.php";
            $gpoObj=new Grupo();
        }
        $gpoFullMapWhere=$gpoObj->setCodigoOptSession();
        return $_SESSION['gpoCodigoOpt'];
    }
    public static function dirlist($dirpath) {
        $path = $_SERVER['DOCUMENT_ROOT'];
        return Archivos::getRecursiveSubDir($path.$dirpath, false, true);
    }
    public static function comparaListas($mesPath) {
        $path = $_SERVER['DOCUMENT_ROOT'];
        $satPath = "descargas/recibidos/";
        $isfPath = "descargas/invoicesafe/";
        $ichPath = "archivos/";
        $arrPath1 = Archivos::getRecursiveSubDir($path.$satPath.$mesPath, false, true);
        $arrPath2 = Archivos::getRecursiveSubDir($path.$ichPath.$mesPath, false, true);
        $arrPath3 = Archivos::getRecursiveSubDir($path.$isfPath.$mesPath, false, true);
        $count1 = Archivos::countNodes($arrPath1);
        $arrPath4 = Archivos::substractPaths($arrPath1, $arrPath2);
        $arrPath5 = Archivos::substractPaths($arrPath4, $arrPath3);
        $count5 = Archivos::countNodes($arrPath5);
        return $arrPath5;
    }
    public static function appendXMLInfo($arr, $ruta, $key, $delButton=TRUE) {
        if (!is_array($arr)) {
            return [];
        }
        if (count($arr)==0) {
            return [];
        }
        $xml = new DOMDocument();
        $nsc = "http://www.sat.gob.mx/TimbreFiscalDigital";
        $path = $_SERVER['DOCUMENT_ROOT'];
        foreach ($arr as $filename => $value) {
            if (@$xml->load($path.$ruta.$filename) === false) {
                $arr[$filename] = "Error on Load ".$path.$ruta.$filename;
                continue;
            }
            $start = $xml->documentElement;
            if ($start==null) {
                $arr[$filename] = "Error on Start";
                continue;
            }
            $ns = $start->getAttribute("xmlns:cfdi");
            if ($ns==null) {
                $arr[$filename] = "Error on CFDI";
                continue;
            }
            $fecha = $start->getAttribute("fecha");
            if ($fecha==null) {
                $arr[$filename] = "Error on Fecha";
                continue;
            }
            if (isset($fecha[19])) $fecha = substr($fecha,0,19);
            $invDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $fecha);
            if ($invDate === false) {
                $arr[$filename] = "Error on DateTime";
                continue;
            }

            $receptor = $xml->getElementsByTagNameNS($ns, "Receptor")->item(0);
            if (!empty($receptor)) {
                $rfcReceptor = $receptor->getAttribute("rfc");
                $rfcReceptor = preg_replace("/[^a-z&0-9]/i", "", $rfcReceptor);
            }

            $tipo = $start->getAttribute("tipoDeComprobante");
            $moneda = $start->getAttribute("Moneda");
            $cambio = $start->getAttribute("TipoCambio");

            $totalnum = +$start->getAttribute("total");
            $signo = "";
            if($totalnum<0) {
                $signo="-";
                $totalnum *= -1;
            }
            $total = number_format($totalnum,2);
            $total = ' $'."<span class='width100px righted".(isset($signo[0])?" redden":"")."'>".$signo.$total."</span>";

            $subtotalnum = +$start->getAttribute("subTotal");
            $signo = "";
            if($subtotalnum<0) {
                $signo="-";
                $subtotalnum *= -1;
            }
            $subtotal = number_format($subtotalnum,2);
            $subtotal = ' $'."<span class='width100px righted".(isset($signo[0])?" redden":"")."'>".$signo.$subtotal."</span>";

            unset($arr[$filename]);
            $modifier="";
            if (Archivos::url_exists("http://sti.dyndns-ip.com/glama/empresas/$rfcReceptor/$filename")) {
                //$modifier=" # ";
                if ( $key=="Dif" ) {
                    continue;
                }
            }

            $value = $modifier.strtoupper($tipo)." &nbsp; ".$invDate->format("Y")."/".$invDate->format("m")."/".$invDate->format("d")." <input type='checkbox'> &nbsp; $subtotal &nbsp; $total $moneda";
            if (!empty($cambio)) $value.=" ($cambio)";
            $arr["<a href='$ruta$filename' class='width240px'>".$filename."</a>".($delButton?"<input type=\"button\" value=\"-\" onclick=\"confirmaBorrarArchivo('$ruta$filename', '$key');\">":"")] = $value;
        }
        asort($arr);
        return $arr;
    }
    public static function getRecursiveSubDir($path, $incluyeGrupo=false, $incluyeXML=false) {
        if(!isset($path) || !isset($path[0])) return [];
        
        if (is_dir($path)) {
            if (substr($path, -1) !== "/") $path.="/";
            $array = array_values(array_diff(scandir($path),Archivos::exceptPaths($incluyeGrupo)));
            $arrz = [];
            $count = 0;
            foreach ($array as $subdir) {
                $subPath = $path.$subdir;
                $result = Archivos::getRecursiveSubDir($subPath, $incluyeGrupo, $incluyeXML);
                if ($result!==false) {
                    $arrz[$subdir] = $result;
                } else {
                    $ext = substr($subdir, -4);
                    if ($ext==".xml") $count++;
                }
            }
            if (count($arrz)==0) {
                return "$count archivos XML";
            } else if ($count>0) {
                $arrz = ["XML"=>"$count archivos XML"] + $arrz;
            }
            return $arrz;
        } else if (file_exists($path)) {
            $ext = substr($path, -4);
            if ($ext==".xml" && !$incluyeXML) {
                return false;
            }
            $filename = substr($path, strrpos($path,"/",-1)+1);
            return $filename;
        } else {
        }
        return [];
    }
    public static function exceptPaths($incluyeGrupo=true) {
        static $excArr = [".", ".."];
        if (!$incluyeGrupo) {
            $retVal = $excArr + array_diff(Archivos::getGpoCodigoOpt(), ["APSA"]);
            $result = implode(",",$retVal);
            return $retVal;
        }
        return $excArr;
    }
    public static function countNodes($arr) {
        $num=0;
        foreach ($arr as $key => $value) {
            if (is_array($value)) $num += Archivos::countNodes($value);
            else $num++;
        }
        return $num;
    }
    public static function substractPaths($arr1, $arr2, $prefix=" - ") {
        $arr3 = [];
        foreach ($arr1 as $key => $value) {
            if (isset($arr2[$key]) || array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $arr3[$key] = Archivos::substractPaths($value, $arr2[$key], " &nbsp;&nbsp; ".$prefix);
                } else if ($arr2[$key]!=$value) {
                    $arr3[$key] = $value;
                }
            } else {
                $arr3[$key] = $value;
            }
        }
        return $arr3;
    }
    public static function unlinkRecursive($dir, $deleteRootToo=true) {
        $num=[0,0];
        if (is_dir($dir)) {
            if(!$dh = @opendir($dir)) return $num;
            while (false !== ($obj = readdir($dh))) {
                if($obj == '.' || $obj == '..') continue;
                $val = Archivos::unlinkRecursive($dir.'/'.$obj, true);
                $num[0]+=$val[0]; $num[1]+=$val[1];
            }
            closedir($dh);
            if ($deleteRootToo) {
                @rmdir($dir);
                $num[1]++;
            }
        } else if ($deleteRootToo) {
            if(@unlink($dir)) $num[0]++;
        }
        return $num;
    }

    public static function url_exists($url) {
        return Archivos::url_exists3($url);
    }
    public static function url_exists1($url) {
        $url = str_replace("http://", "", $url);
        if (strstr($url, "/")) {
            $url = explode("/", $url, 2);
            $url[1] = "/".$url[1];
        } else {
            $url = array($url, "/");
        }
        $fh = fsockopen($url[0], 80);
        if ($fh) {
            fputs($fh,"GET ".$url[1]." HTTP/1.1\nHost:".$url[0]."\n\n");
            if (fread($fh, 22) == "HTTP/1.1 404 Not Found") { 
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
                return FALSE;
        }
    }
    /* undefined function curl_init */
    public static function url_exists2($url) {
        if (!$fp = curl_init($url)) return false;
        return true;
    }
    public static function url_exists3($url) {
        $result = @get_headers($url);
        if (preg_match("|200|", $result[0])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    public static function processResult($result) {
        if (is_bool($result)) return $result?"SI":"NO";
        if (is_string($result)) return "'".$result."'";
        if (is_array($result)) return "[".json_encode($result)."]";
        if (is_object($result)) return "{".json_encode($result)."}";
        if (empty($result)) return ":".$result.":";
        return $result;
    }
    public static function fixCFDIPDF() {
        global $infObj, $logObj, $invObj, $gpoObj, $query;
        if (!isset($infObj)) {
            require_once "clases/InfoLocal.php";
            $infObj=new InfoLocal();
        }
        if (!isset($logObj)) {
            require_once "clases/Logs.php";
            $logObj=new Logs();
        }
        $maxId = $infObj->getData("nombre='fixCFDIPDF_maxid'",0,"valor");
        if (isset($maxId[0]["valor"][0])) $maxId=$maxId[0]["valor"];
        else $maxId=null;
        $minId = $infObj->getData("nombre='fixCFDIPDF_minid'",0,"valor");
        if (isset($minId[0]["valor"][0])) $minId=$minId[0]["valor"];
        else $minId=null;
        if (!isset($invObj)) {
            require_once "clases/Facturas.php";
            $invObj=new Facturas();
        } else {
            $invOrderList=$invObj->orderlist;
            $invObj->clearOrder();
        }
        $invRows=$invObj->rows_per_page;
        $invObj->rows_per_page=1;
        $invObj->addOrder("id", "desc");
        // select id, nombreInterno, nombreInternoPDF, concat(RIGHT(nombreInterno,LENGTH(nombreInterno)-LOCATE('_',nombreInterno)),LEFT(nombreInterno,LOCATE('_',nombreInterno) - 1)) nameFromXML, ubicacion, statusn, status
        // from facturas where nombreInternoPDF!=concat(RIGHT(nombreInterno,LENGTH(nombreInterno)-LOCATE('_',nombreInterno)),LEFT(nombreInterno,LOCATE('_',nombreInterno) - 1)) order by id desc
        $minMaxIdWhrChnk="";
        if (isset($maxId)) {
            $lessMaxId=$maxId-1;
            if (isset($minId)) {
                if ($minId>$lessMaxId) {
                    // Eliminar evento:
                    // tabla Eventos
                    // tipo=funcion
                    // accion=repite
                    // data={"clase":"Archivos","funcion":"fixCFDIPDF","tipofuncion":"clase","veces":-1,"ciclo":55}
                    return;
                }
                $minMaxIdWhrChnk=" and id between $minId and $lessMaxId";
            } else $minMaxIdWhrChnk=" and id<$maxId";
        }
        $invData = $invObj->getData("nombreInternoPDF!=concat(RIGHT(nombreInterno,LENGTH(nombreInterno)-LOCATE('_',nombreInterno)),LEFT(nombreInterno,LOCATE('_',nombreInterno) - 1)) and statusn is not null{$minMaxIdWhrChnk}",0,"id, nombreInterno, nombreInternoPDF, concat(RIGHT(nombreInterno,LENGTH(nombreInterno)-LOCATE('_',nombreInterno)),LEFT(nombreInterno,LOCATE('_',nombreInterno) - 1)) pdfNameFromXML, ubicacion, statusn, rfcGrupo, codigoProveedor, folio, fechaFactura");
        $sysPath="C:/Apache24/htdocs/invoice/";
        $usrIdEventos=3760;
        $logFields=["idUsuario"=>$usrIdEventos,"seccion"=>"fixCFDIPDF","fecha"=>new DBName("now()")];
        if (isset($invData[0]["id"])) {
            $invData=$invData[0];
            $invId=$invData["id"];
            $xmlName=$invData["nombreInterno"];
            $pdfName=$invData["nombreInternoPDF"];
            $pdfRlNm=$invData["pdfNameFromXML"];
            $path=$invData["ubicacion"];
            $sttn=$invData["statusn"];
            $rfcGrupo=$invData["rfcGrupo"];
            $codigoProveedor=$invData["codigoProveedor"];
            $folio=$invData["folio"];
            $fecha=$invData["fechaFactura"];
            $pdfRlNmPath=$path.$pdfRlNm.".pdf";
            if (file_exists($sysPath.$pdfRlNmPath)) {
                $invFields=["id"=>$invId, "nombreInternoPDF"=>$pdfRlNm];
                $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
                doclog("CAMBIAR NOMBRE PDF", "pdf", $baseData+["line"=>__LINE__,"xml"=>$xmlName,"oldpdf"=>$pdfName,"newpdf"=>$pdfRlNm]);
                if ($sttn&Facturas::STATUS_RESPALDADO) $infFields["statusn"]=$sttn-Facturas::STATUS_RESPALDADO;
                if (!isset($gpoObj)) {
                    require_once "clases/Grupo.php";
                    $gpoObj=new Grupo();
                }
                $empresa=$gpoObj->getData("rfc='$rfcGrupo'",0,"alias");
                if (isset($empresa[0]["alias"][0])) {
                    $empresa=$empresa[0]["alias"];
                    if ($invObj->saveRecord($invFields)) {
                        if (isset($invFields["statusn"])) {
                            // toDo: Hacer FTP a Avance con el archivo PDF
                            $logFields["texto"]="Respaldar factura id=$invId, empresa=$empresa, proveedor=$codigoProveedor, folio=$folio, fecha=$fecha";
                        } else $logFields["texto"]="Exito: Archivo corregido '$pdfRlNmPath' en factura id=$invId";
                    } else {
                        $errNo=DBi::getErrno();
                        $errMsg=DBi::getError();
                        $logFields["texto"]="Error $errNo: $errMsg | $query";
                    }
                } else $logFields["texto"]="Error: No existe empresa con rfc '$rfcGrupo' en factura id=$invId se quiere cambiar pdf '$pdfRlNmPath'";
            } else $logFields["texto"]="Error: No existe archivo '$pdfRlNmPath'";
            $infObj->saveRecord(["nombre"=>"fixCFDIPDF_maxid","valor"=>$invId]);
        } else {
            // Eliminar evento:
            // tabla Eventos
            // tipo=funcion
            // accion=repite
            // data={"clase":"Archivos","funcion":"fixCFDIPDF","tipofuncion":"clase","veces":-1,"ciclo":55}
            $errNo=DBi::getErrno();
            $errMsg=DBi::getError();
            $logFields["texto"]="Error $errNo: Sin resultado. $errMsg | $query";
        }
        $logObj->saveRecord($logFields);
        if (isset($invOrderList)) $invObj->setOrderList($invOrderList);
        $invObj->rows_per_page=$invRows;
    }
    public static function getUploadError($file, $validMime=null) {
        if (isset($file["error"])) switch($file["error"]) {
            case UPLOAD_ERR_OK:
                if (!isset($file["type"][0])) return "No se indica el tipo de archivo";
                if (!isset($file["name"][0])) return "No se indica el nombre del archivo";
                if (!isset($file["tmp_name"][0])) return "No se recuperó el archivo";
                if (isset($validMime[0])) {
                    if (is_array($validMime)) {
                        $isValid=false;
                        foreach ($validMime as $mime) {
                            if ($file["type"]===$mime) {
                                $isValid=true;
                                break;
                            }
                        }
                    } else $isValid=($file["type"]===$validMime);
                    if (!$isValid) return "El tipo de archivo '".$file["type"]."' no es válido";
                }
                //$finfo=new finfo(FILEINFO_MIME_TYPE);
                break;
            case UPLOAD_ERR_INI_SIZE: // 1
            case UPLOAD_ERR_FORM_SIZE: // 2
                return "El archivo es demasiado grande.";
            case UPLOAD_ERR_PARTIAL: // 3
                return "Descarga incompleta del archivo";
            case UPLOAD_ERR_NO_FILE: // 4 // no ocurre
                return "No se recibi&oacute; el archivo.";
            case UPLOAD_ERR_NO_TMP_DIR: // 6
                return "No se encontró carpeta temporal para realizar la descarga";
            case UPLOAD_ERR_CANT_WRITE: // 7
                return "Error de almacenamiento al intentar guardar el archivo PDF";
            case UPLOAD_ERR_EXTENSION: // 8
                return "La descarga del archivo PDF fue detenida por el navegador";
            default: return "Error de carga de archivo desconocido.";
        }
        return "";
    }
}
//clog1seq(-1);
//clog2end("Archivos");
