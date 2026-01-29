<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";
$comps=[
    "cfdi"=>[
        "NS"=>"",
        "XSD"=>[
            "32"=>"3/cfdv32.xsd",
            "33"=>"3/cfdv33.xsd",
            "40"=>"4/cfdv40.xsd"],
        "XSLT"=>""],
    "tfd"=>[
        "NS"=>"http://www.sat.gob.mx/TimbreFiscalDigital",
        //"XSD"=>"TimbreFiscalDigital/TimbreFiscalDigital.xsd",
        "XSD"=>"TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd"],
    "registrofiscal"=>[
        "NS"=>"http://www.sat.gob.mx/registrofiscal",
        "XSD"=>"cfdiregistrofiscal/cfdiregistrofiscal.xsd",
        "XSLT"=>"cfdiregistrofiscal/cfdiregistrofiscal.xslt"],
    "donat"=>[
        "NS"=>"http://www.sat.gob.mx/donat",
        //"XSD"=>"donat/donat.xsd"
        //"XSLT"=>"donat/donat.xslt"
        "XSD"=>"donat/donat11.xsd",
        "XSLT"=>"donat/donat11.xslt"],
    "pago10"=>[
        "NS"=>"http://www.sat.gob.mx/Pagos",
        "XSD"=>"Pagos/Pagos10.xsd",
        "XSLT"=>"Pagos/Pagos10.xslt"],
    "cartaporte"=>[
        "NS"=>"http://www.sat.gob.mx/CartaPorte",
        "XSD"=>"CartaPorte/CartaPorte.xsd",
        "XSLT"=>"CartaPorte/CartaPorte.xslt"],
    "cartaporte20"=>[
        "NS"=>"http://www.sat.gob.mx/CartaPorte20",
        "XSD"=>"CartaPorte/CartaPorte20.xsd",
        "XSLT"=>"CartaPorte/CartaPorte20.xslt"],
    "xsi"=>[
        "NS"=>"http://www.w3.org/2001/XMLSchema-instance"]
];
$abc=range("a", "z");
$docsPath="C:/InvoiceCheckShare/tmp/cfdicheck/";
$now=time();
$tenmin=10*60;
foreach (glob($docsPath."*") as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= $tenmin) {
            unlink($file);
        }
    }
}
?>
<html>
<head>
    <meta charset="utf-8">
    <?= ""/* $isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":""*/ ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <?= ""/* script src="scripts/general.js?ver=test2202171712a"></script */ ?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
<?php
/*foreach ($_SERVER as $key => $value) {
    echo "console.log('SERVER[$key] = $value');\n";
}*/
?>
    </script>
</head>
<body style="overflow-y: auto;">
<div class="basicBG" style="position:  fixed;top: 0px;height: 22px;"><form method="POST" name="form_check" target="_self" enctype="multipart/form-data">
CHECK: <input type="file" name="fix[]" accept=".xml" onchange="this.form.submit()" multiple>
</form></div>
<?php
global $milog;
$milog=[];
if (isset($_FILES["fix"])) {
    $files=getFixedFileArray($_FILES["fix"]);
    echo "<UL style=\"margin-top: 22px;\">";
    foreach ($files as $idx => $file) {
        if ($idx>0) echo "<LI style=\"list-style-type: none;\"><HR></LI>\n";
        echo getULCode($file,true);
        $tmpname=$file["tmp_name"];
        $name=$file["name"];
        if (isset($tmpname[0]) && file_exists($tmpname)) {
            foreach ($abc as $a) {
                $savename=$a.$now."_".$idx;
                if (!file_exists($docsPath.$savename.".xml")) break;
            }
            $absname=$docsPath.$savename.".xml";
            move_uploaded_file($tmpname, $absname);
            chmod($absname,0666);
            echo "<LI>saved name = {$savename}.xml</LI>\n";
            $xmldoc = new DOMDocument();
            $xmldoc->preserveWhiteSpace=false;
            libxml_use_internal_errors(true);
            $errcode=0;
            global
                $XS,
                $SATPATH,
                $SATPATHL,
                $LOCPATH,
                $WEBPATH,
                $QXS_TEL, // QUERY_XS_TOP_ELEMENT
                $QXS_EL, // QUERY_XS_ELEMENT
                $QXS_ATT, // QUERY_XS_ATTRIBUTE
                $QXS_BST, // QUERY_XSD_BASE_TYPE
                $QXS_ANY, // QUERY_XS_ANY 
                $QXMLNS,  // QUERY_XMLNS
                $TFD11;
            $XS="http://www.w3.org/2001/XMLSchema";
            $SATPATH="http://www.sat.gob.mx/sitio_internet/";
            $SATPATHL=strlen($SATPATH);
            $LOCPATH="C:\\Apache24\\htdocs\\invoice\\";
            $WEBPATH=$_SERVER["HTTP_ORIGIN"].$_SERVER["WEB_MD_PATH"];
            $QXS_TEL = "/xs:schema/xs:element";
            $QXS_EL  = "xs:complexType/xs:sequence/xs:element|xs:complexType/xs:sequence/xs:sequence/xs:element|xs:complexType/xs:choice/xs:element";
            $QXS_ATT = "xs:complexType/xs:attribute";
            $QXS_BST = "xs:simpleType/xs:restriction";
            $QXS_ANY = "xs:complexType/xs:sequence/xs:any";
            $QXMLNS = "/cfdi:Comprobante/@xmlns:*";
            $TFD11 = "";
            try {
                reparaXML($absname, $name);
                if (@$xmldoc->load($absname, LIBXML_DTDLOAD|LIBXML_DTDATTR)===false) {
                    echo "<LI>FALL&Oacute; CARGA DE ARCHIVO '$name'</LI>\n";
                } else {
                    $xmlelem=$xmldoc->documentElement;
                    $xpath=new DOMXPATH($xmldoc);
                    echoLines(processNode($xmlelem,$xpath));
                }
            } catch (Exception $e) {
                if ($e->getCode()!==6000) {
                    $errcode=$e->getCode();
                    echo "<LI>ERROR AL REPARAR {$errcode}: ".$e->getMessage()."</LI>\n";
                }
            } finally {
                libxml_use_internal_errors(false);
            }
            if ($errcode<=6000||$errcode>6102)
                echo "<LI STYLE=\"list-style-type: none;\"><DIV style=\"width: calc( 100% - 10px );height: 200px;overflow-y: auto;border: 1px solid gray;\"><PRE lang=\"xml\" style=\"white-space: pre-wrap;margin: auto;\">".htmlentities(file_get_contents($absname))."</PRE></LI>\n";
        } else {
            echo "<LI>No existe el archivo '$name'</LI>\n";
        }
    }
    if (isset($milog[0])) {
        echo "<LI style=\"list-style-type: none;\"><HR></LI>\n<LI>LOGS:<OL>";
        foreach ($milog as $logIdx => $logValue) echo "<LI>$logValue</LI>\n";
        echo "</OL></LI>\n";
    } else echo "<LI>NO LOGS</LI>\n";
    echo "</UL>";
}
?>
</body>
</html>
<?php
global $cache;
$cache=[];
function processNode($node,$xpath,$nsreg=[]) {
    global $XS,$SATPATH,$SATPATHL,$LOCPATH,$WEBPATH;
    $data=[];
    if (isset($node->namespaceURI[0])||isset($node->prefix[0])) {
        if (!in_array($node->prefix, $nsreg)) {
            $xpath->registerNamespace($node->prefix,$node->namespaceURI);
            $data[$node->prefix]=$node->namespaceURI;
            $nsreg[]=$node->prefix;
        }
    }
    if ($node->hasAttributes()) {
        foreach ($node->attributes as $attName=>$attNode) {
            if (isset($attNode->namespaceURI[0])||isset($attNode->prefix[0])) {
                if ($attNode->prefix!=="xsi" || !in_array("xsi",$nsreg)) {
                    $xpath->registerNamespace($attNode->prefix, $attNode->namespaceURI);
                    $data[$attNode->prefix]=$attNode->namespaceURI;
                    if ($attNode->prefix==="xsi") $nsreg[]="xsi";
                }
                $xsdList=array_values(array_filter(explode(" ",$attNode->value),function($v){return substr($v,-4)===".xsd";}));
                if (isset($xsdList[0])) {
                    $data[$attName]=[];
                    foreach ($xsdList as $xIdx => $xVal) {
                        $data[$attName][]=$xVal;
                        $relPath="sat/".substr($xVal,$SATPATHL);
                        $absPath=$LOCPATH.str_replace("/","\\",$relPath);
                        if (file_exists($absPath)) {
                            $xd=new DOMDocument();
                            $xd->load($absPath);
                            $xdp=new DOMXPATH($xd);
                            $xdp->registerNamespace("xs",$XS);
                            $data[$attName][]=$WEBPATH.$relPath;
                        }
                    }
                }
            }
        }
    }
    if ($node->hasChildNodes()) {
        foreach($node->childNodes as $chIdx=>$chNode) {
            $childProcess=processNode($chNode,$xpath,$nsreg);
            $childKeys=array_keys($childProcess);
            if (isset($childKeys[0]))
                $data[$chNode->localName]=$childProcess;
        }
    }
    return $data;
}
function echoLines($data) {
    $isSeq = isSequential($data);
    foreach ($data as $key => $value) {
        echo "<LI>".($isSeq?"":"$key = ");
        if (is_array($value)) {
            if (isSequential($value) && isset($value[0]) && !isset($value[1])) {
                $isXSD=(substr($value[0], -4)===".xsd");
                if($isXSD) echo "<A HREF=\"".$value[0]."\">";
                echo $value[0];
                if($isXSD) echo "</A>";
            } else {
                echo "<UL>";
                echoLines($value);
                echo "</UL>";
            }
        } else {
            $isXSD=(substr($value, -4)===".xsd");
            if($isXSD) echo "<A HREF=\"$value\">";
            echo $value;
            if($isXSD) echo "</A>";
        }
        echo "</LI>";
    }
}
function get($xqry, $xpth) { return evaluateXPATH($xqry, $xpth); }
function evaluateXPATH($xqry, $xpth, $isCS=false) {
    $xresult=$xpth->evaluate($xqry);
    if (is_object($xresult) && get_class($xresult)==="DOMNodeList") {
        $arr=[];
        foreach ($xresult as $itm) {
            $val=explodeNode($itm,$isCS);
            if (!empty($val)||$val==="0") $arr[]=$val;
        }
        if (!isset($arr[0])) $xresult="";
        else if (!isset($arr[1])) $xresult=$arr[0];
        else $xresult=$arr;
    }
    return $xresult;
}
function explodeNode($nod, $isCS=false, $prompt="") {
    $isObj=is_object($nod);
    $isNod=($isObj&&is_a($nod,"DOMNode"));
    if ($isObj) $clnod=get_class($nod);
    if ($isNod) {
        $nm=$nod->localName;
        if (empty(trim($nm))) return null;
        $lcnm=strtolower($nm);
        if (in_array($lcnm, ["annotation","simpletype"])) return null;
        $typ=$nod->nodeType;
        $esEl=($typ==XML_ELEMENT_NODE);
        $val=$nod->nodeValue;
        if (XML_TEXT_NODE==$typ) return $val;
        if (XML_ATTRIBUTE_NODE==$typ) return $nod->value;
        $trv=trim($val);
        $v20=substr($trv,0,20);
        $arr=[];
        if ($nod->hasAttributes()) foreach ($nod->attributes as $attr) {
            $atNm=$attr->localName;
            $arr["@".($isCS?$atNm:strtolower($atNm))]=$attr->nodeValue;
        }
        $sqIx=0;
        if ($nod->hasChildNodes()) foreach ($nod->childNodes as $chNd) {
            $chNm=$chNd->localName;
            if (in_array(strtolower($chNm),["cfdirelacionado","concepto","informacionaduanera","parte","traslado","retencion"])) {
                $sqIx++;
                $chNm.=$sqIx;
            }
            if (XML_TEXT_NODE==$chNd->nodeType) {
                $chVl=trim($chNd->nodeValue);
                if (isset($chVl[0])) $arr[$chNm] = $chNd->nodeValue;
            } else if (1==$chNd->childNodes->length && XML_TEXT_NODE==$chNd->firstChild->nodeType) {
                $chVl=trim($chNd->firstChild->nodeValue);
                if (isset($chVl[0])) $arr[$chNm]=$chNd->firstChild->nodeValue;
            } else if (!empty($a=explodeNode($chNd,$isCS,$prompt."  "))) $arr[$chNm]=$a;
        }
        return $arr;
    } else return null;

}
/*function explodeXSDElement($nod, $xpthk, $vals=null, $dpth=0) {
    if ($dpth>13) return ["error"=>"Recursion máxima alcanzada ($dpth)"];
    if ($xpthk==null) return ["error"=>"XPATH Null"];
    global
                $QXS_TEL, // QUERY_XS_TOP_ELEMENT
                $QXS_EL, // QUERY_XS_ELEMENT
                $QXS_ATT, // QUERY_XS_ATTRIBUTE
                $QXS_BST, // QUERY_XSD_BASE_TYPE
                $QXS_ANY, // QUERY_XS_ANY 
                $QXMLNS,  // QUERY_XMLNS
                $TFD11;
    $arr=[]; $ats="@attributes";
    if (isset($nod->parentNode)){if(!isset($arr[$ats]))$arr[$ats]=[];
        $arr[$ats]["parentTag"]=$nod->parentNode->localName;
    }
    foreach (["type","minOccurs","maxOccurs"] as $atr) {
        if ($nod->hasAttribute($atr)) {
            if(!isset($arr[$ats])) $arr[$ats]=[];
            $arr[$ats][$atr]=$nod->getAttribute($atr);
        }
    }
    $attrDefs=$xpthk->evaluate($QXS_ATT,$nod);
    if(isset($vals) && is_array($vals)) $valKs=array_keys($vals);
    foreach ($attrDefs as $aDef) {
        $atNm="@".$aDef->getAttribute("name");
        if ($aDef->hasAttribute("use")) {
            $arr[$atNm]=["use"=>$aDef->getAttribute("use")];
            if ($atNm==="@Folio") $arr[$atNm]["use"]="required2";
        } else $arr[$atNm]=["use"=>"undefined"];
        if (isset($vals)&&isset($vals[$atNm])) {
            if (isset($valKs)) $valKs=array_diff($valKs, [$atNm]);
            $arr[$atNm]["value"]=$vals[$atNm];
        }
        if ($aDef->hasAttribute("type")) $arr[$atNm]["type"]=$aDef->getAttribute("type");
        else {
            $rules=$xpthk->evaluate($QXS_BST,$aDef);
            if ($rules->length==1 && $rules->item(0)->hasAttribute("base")) $arr[$atNm]["type"]=$rules->item(0)->getAttribute("base");
            else if ($rules->length>1) {
                $arr[$atNm]["type"]=[];
                foreach ($rules as $rul) {
                    if ($rul->hasAttribute("base")) $arr[$atNm]["type"][]=$rul->getAttribute("base");
                }
            }
        }
        if ($aDef->hasAttribute("fixed")) $arr[$atNm]["fixed"]=$aDef->getAttribute("fixed");
    }
    if (isset($valKs)) {
        $atsX=[];
        foreach ($$valKs as $val) if ($val[0]==="@") {
            if (!in_array($val, ["@schemaLocation"])) $arr[$val]=["xml"=>$vals[$val]];
            $atsX[]=$val;
        }
        $valKs=array_diff($valKs, $atsX);
    }
    $elemDefs=$xpthk->evaluate($QXS_EL,$nod);
    if (isset($vals) || !isset($arr[$ats]["minOccurs"]) || $arr[$ats]["minOccurs"]!=="0") foreach($elemDefs as $eDef) {
        $edNm=$eDef->getAttribute("name");
        if (in_array(strtolower($edNm, ["cfdirelacionado","concepto","informacionaduanera","parte","traslado","retencion"]))) {
            $vIx=0;
            if (is_iterable($vals)) foreach($vals as $v) {
                $vIx++;
                $arrEdNm = $edNm.$vIx;
                if (!isset($vals[$arrEdNm])) break;
                $arr[$arrEdNm]=explodeXSDElement($eDef, $xpthk, $vals[$arrEdNm], $dpth+1);
                if (isset($valKs)) $valKs=array_diff($valKs,[$arrEdNm]);
            }
            continue;
        }
        if (isset($vals[$edNm])) {
            $edVs=$vals[$edNm];
            if (isset($valKs)) $valKs=array_diff($valKs, [$edNm]);
        } else $edVs=null;
        $arr[$edNm]=explodeXSDElement($eDef, $xpthk, $edVs, $dpth+1);
    } else if (in_array($nod->getAttribute("name"),["CfdiRelacionados","CuentaPredial"])) {
        $arr=["use"=>"optional","type"=>"xs:string"];
    }
    $anyEls=$xpthk->evaluate($QXS_ANY,$nod);
    if (isset($anyEls) && $anyEls->length>0) {
        if ($nod->getAttribute("name")==="Complemento") {
            $tfdxpth = $xsddata["xpath.".$TFD11];
            if (isset($tfdxpth)) {
                $elDfs=$tfdxpth->evaluate($QXS_TEL);
                foreach ($elDfs as $eD) {
                    $atNm=$eD->getAttribute("name");
                    if (isset($vals[$atNm])) {
                        $arr[$atNm]=explodeXSDElement($eD, $xpthk, $vals[$atNm],$dpth+1);
                        if (isset($valKs)) $valKs=array_diff($valKs,[$atNm]);
                    }
                }
            }
        }
    }
    if (isset($valKs)) foreach ($valKs as $val) {
        $arr[$val] = ["xml"=>$vals[$val]];
    }
    return $arr;
}*/
function isSequential($arr) {
    $seq=0;
    foreach ($arr as $idx => $val) {
        if (is_string($idx)||$idx!==$seq) return false;
        $seq++;
    }
    return true;
}
function getFixedFileArray1($files) {
    global $milog;
    $milog[]="INI getFixedFileArray1 ".json_encode($files);
    $fixedFiles = [];
    if (isset($files) && isset($files["name"])) {
        $fileCount = count($files["name"]);
        $fileKeys = array_keys($files);
        $fileNameKeys = array_keys($files["name"]);
        //for ($i=0; $i<$fileCount; $i++) {
        foreach($fileNameKeys as $nameKey) {
            foreach ($fileKeys as $key) {
                $fixedFiles[$nameKey][$key] = $files[$key][$nameKey];
            }
        }
    }
    $milog[]="END getFixedFileArray1 return:".json_encode($fixedFiles);
    return $fixedFiles;
}
function getULCode($arr,$part=false) {
    $milog[]="INI getULCode ".json_encode($arr);
    if (!isset($arr) || !is_array($arr)) return "";
    $milog[]="CHK is array OK!";
    if (isSequential($arr)) {
        if (isset($arr[0])) {
            $result=$part?"":"<OL>";
            foreach($arr as $val) {
                $result.="<LI>";
                if (is_array($val)) $result.=getULCode($val);
                $result.="</LI>\n";
            }
            if (!$part) $result.="</OL>";
        } else $milog[]="CHK FAIL: empty sequential array!";
    } else {
        if (isset(array_keys($arr)[0])) {
            $result=$part?"":"<UL>";
            foreach($arr as $idx=>$val) {
                $result.="<LI>$idx";
                if (is_array($val)) $result.=getULCode($val);
                else $result.=" = '$val'";
                $result.="</LI>\n";
            }
            if (!$part) $result.="</UL>";
        } else $milog[]="CHK FAIL: empty associative array!";
    }
    $milog[]="END getULCode result='".($result??"")."'";
    return $result??"";
}
function reparaXML($filePath, $name=null) {
    if (!isset($name)) $name=$filePath;
    if (!file_exists($filePath)) throw new Exception("El archivo '$name' no se encontró",6001);
    if (is_dir($filePath)) throw new Exception("La ruta indicada es un directorio",6002);
    if (!is_writable($filePath) && !chmod($filePath, 0666)) throw new Exception("No tiene permiso de escritura",6003);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tmpType = $finfo->file($filePath);
    if (!in_array($tmpType, ["text/xml", "application/xml","text/plain","application/octet-stream"], true)) throw new Exception("Formato de archivo inválido: {$tmpType}",6004);
    $fd=@fopen($filePath,"r");
    if ($fd===false) throw new Exception("No tiene permiso de lectura",6005);
    $fileContents=fread($fd,filesize($filePath));
    if ($fileContents===false) throw new Exception("Error en lectura de archivo",6006);
    if (fclose($fd)===false) throw new Exception("Error al cerrar archivo por lectura", 6007);
    $fixedFileContents=reparaXMLText($fileContents);
    if (substr($fixedFileContents, 0, 7)==="ERROR: ") throw new Exception(substr($fixedFileContents, 7), 6008);

    $hasChanged=(strcmp($fileContents,$fixedFileContents)!=0);

    //if (!$hasChanged) throw new Exception("No se realizaron cambios", 6000);
    
    /* // Sin cambiar archivos
    $fd=@fopen($filePath,"w");
    if ($fd===false) throw new Exception("El archivo '$name' no tiene permiso de escritura",6009);
    $writeResult=fwrite($fd, $fixedFileContents);
    $closeResult=fclose($fd);
    if ($writeResult===false) throw new Exception("Error al intentar modificar el archivo", 6010);
    if ($writeResult===0) throw new Exception("Reparación de archivo fallida", 6011);
    if ($closeResult===false) throw new Exception("Error al cerrar archivo a reparar", 6012);
    // */
}
function reparaXMLText($text) {
    global $milog;
    $milog[]="INI reparaXMLText";
    $isFixed=false;
    // Correccion 0: Eliminar espacios antes y después
    $txtLen=strlen($text);
    if ($txtLen===0) throw new Exception("No tiene texto",6101);
    $text=trim($text);
    if (!isset($text[$txtLen-1])) {
        $isFixed=true;
        $milog[]="Se remueven espacios por trim";
    }
    // $txtLen=strlen($text)
    // if (txtLen==0) ...
    if (!isset($text[0])) throw new Exception("No tiene texto",6102);

    // Correccion 1: Eliminar basura al inicio del texto
    $prefix=["<?xml","<cfdi:Comprobante"];
    $iniIdx=false;
    foreach ($prefix as $idx => $pfx) {
        $iniIdx=strpos($text,$pfx);
        if ($iniIdx!==false && $iniIdx>=0) break;
    }
    if ($iniIdx===false || $iniIdx<0) throw new Exception("Archivo sin declaracion XML inicial",6103);
    if ($iniIdx>0) {
        $garbageName="";
        $garbage=substr($text,0,$iniIdx);
        if ($garbage==="o;?"||$garbage==="?") $garbageName="BOM";
        else {
            if ($iniIdx<=4) {
                $hex=strtoupper(unpack("H*",$garbage)[1]);
                switch($hex) {
                    case "EFBBBF": $garbageName="BOM(UTF-8)";break;
                    case "FEFF": $garbageName="BOM(UTF-16, big-endian)";break;
                    case "FFFE":  $garbageName="BOM(UTF-16,little-endian)";break;
                    case "0000FEFF": $garbageName="BOM(UTF-32, big-endian)";break;
                    case "FFFE0000":  $garbageName="BOM(UTF-32,little-endian)";break;
                    default: $garbageName="($iniIdx) '{$garbage}' c'".unpack("C*",$garbage)[1]."' h'{$hex}'";
                }
            } else $garbageName="($iniIdx) '{$garbage}'";
        }
        $isFixed=true;
        $text=substr($text,$iniIdx);
        // txtLen=strlen($text);
        $milog[]="Se remueve basura inicial: {$garbageName}";
    }
    // Correccion 2: Eliminar basura al final del texto
    $suffix=["</cfdi:Comprobante>"];
    $endIdx=false;
    $sfx="";
    $sfxLog=["INI 0"];
    foreach ($suffix as $idx => $sfx) {
        $endIdx=strpos($text, $sfx);
        if ($endIdx!==false && $endIdx>=0) break;
    }
    if ($endIdx===false || $endIdx<0) throw new Exception("Archivo sin declaracion XML final",6104);
    $txtLen=strlen($text);
    if (isset($sfx[0])) {
        $diffIdx=$endIdx+strlen($sfx)-$txtLen;
        if ($diffIdx<0) {
            $garbage=substr($text,$diffIdx);
            $isFixed=true;
            $text=substr($text, 0, $diffIdx);
            $txtLen=strlen($text);
            $milog[]="Se remueve basura al final: '{$garbage}'";
        }
    }
    // Correccion 3: Eliminar espacios entre tags
    $text=preg_replace("/>\s*</", "><", $text);
    $newLen=strlen($text);
    if ($txtLen!=$newLen) {
        $isFixed=true;
        $milog[]="Se reemplazan espacios entre tags";
        $txtLen=$newLen;
    }
    // Correccion 4: Reparar sintaxis de schemaLocation
    $text=str_replace("xmlns:schemaLocation", "xsi:schemaLocation", $text);
    $newLen=strlen($text);
    if ($txtLen!==$newLen) {
        $isFixed=true;
        $milog[]="Se reemplaza namespace xmlns por xsi en schemaLocation";
        $txtLen=$newLen;
    }
    // Correccion 5: Reparar caracteres raros (Se añade uno por uno pues no siempre coincide con los equivalentes por encoding)
    $wrongLetters=["C","C","C","\xD1"];
    $fixedLetters=["Á","Ñ","Ó","Ñ"];
    $nwTxt=str_replace($wrongLetters, $fixedLetters, $text);
    $newLen=strlen($nwTxt);
    if ($txtLen!=$newLen) {
        $isFixed=true;
        $milog[]="Se corrigen símbolos no imprimibles de 2 bytes";
        $text=$nwTxt;
        //$txtLen=$newLen;
    } else if (strcmp($nwTxt,$text)!==0) {
        $isFixed=true;
        $milog[]="Se corrigen símbolos no imprimibles de 1 byte";
        $text=$nwTxt;
        //$txtLen=$newLen;
    }
    // Correccion 6: Quitar Addenda vacía
    $addendaBegin="<cfdi:Addenda";
    $len=strlen($addendaBegin);
    $idx=strpos($text,$addendaBegin);
    $endIdx=$idx+$len;
    if ($idx!==false) {
        $cls=strpos($text,">",$endIdx);
        $preCls=$cls-1;
        if ($cls===false) throw new Exception("Archivo incompleto, con addenda incompleta",6105);
        if (substr($text,$preCls,1)==="/") { // <Addenda(xyz)/>
            $isFixed=true;
            if ($endIdx<$preCls) {
                $addendaProps=trim(substr($text,$endIdx,$preCls-$endIdx));
                $hasProps=isset($addendaProps[0]);
            } else $hasProps=false;
            $milog[]="Se elimina addenda vacía Tipo Unico".($hasProps?" con Atributos":"");
            $text=substr($text,0,$idx).substr($text,$cls+1);
            //$txtLen-=($cls+1-$idx);
        } else {
            $cls++;
            $closeAddenda="</cfdi:Addenda>";
            $ctg=strpos($text,$closeAddenda,$cls);
            if ($ctg===false) throw new Exception("Archivo con addenda incompleta",6106);
            $addendaContent=(($ctg==$cls)?"":trim(substr($text, $cls, $ctg)));
            if (!isset($addendaContent[0])) $milog[]="Se elimina addenda vacía Tipo Contenedor";
            else {
                $addendaContent=trim(preg_replace('/<!--(.*)-->/Uis', '', $addendaContent));
                if (!isset($addendaContent[0])) $milog[]="Se elimina addenda vacía con comentarios Tipo Contenedor";
                else $milog[]="Se conserva addenda con informacion";
            }
            if (!isset($addendaContent[0])) {
                $isFixed=true;
                $text=substr($text,0,$idx).substr($text,$ctg+strlen($closeAddenda));
            }
        }
    }
    $milog[]="END reparaXMLText";
    return $text;
}
