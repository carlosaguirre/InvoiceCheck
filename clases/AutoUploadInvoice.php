<?php
require_once dirname(__DIR__)."/bootstrap.php";
class AutoUploadInvoice {
    CONST AUI_VLV_XML = 0; // list of XMLs
    CONST AUI_VLV_PDF = 1; // XMLs with PDF
    CONST AUI_VLV_VALXML = 2; // validate both
    CONST AUI_VLV_VALPDF = 3; // valid PDFs
    CONST AUI_PDF_REQUIRED = false;
    CONST MAXLEN = 100;
    private $invList=null;
    private $errList=null;
    private $minDuration=-1;
    private $maxDuration=-1;
    private $errDuration=0;
    private $invDuration=0;
    private $lastCFDIObj=null;
    private $isPostService=true;
    public static $inclusiveSeparator="***";
    private static $runningService=false;
    private static $pathLen=0;
    private static $errPLen=0;
    public function __construct() {
        global $autoUploadPath, $autoUploadErrPath;
        self::$pathLen=strlen($autoUploadPath);
        self::$errPLen=strlen($autoUploadErrPath);
        if (!self::$runningService) {
            //doclog("AUI: INIT AUTO UPLOAD FILE LIST", "autoupload");
            self::$runningService=true;
            $this->genInvList();
        }
    }
    public static function service() {
        global $_project_name, $usrObj;
        if (!isset($_project_name[0])) $_project_name="invoice";
        if(!isset($usrObj)) {
            require_once "clases/Usuarios.php";
            $usrObj = new Usuarios();
        }
        $usrData = $usrObj->getData("nombre='eventos'",0,"id,nombre,persona,email,banderas");
        if ($usrData) {
            $usr = (object) $usrData[0];
            $usr->project_name = $_project_name;
            $usr->perfiles=["Administrador","Sistemas"];
            $_SESSION['user'] = $usr;
            $_SESSION['tmp'] = "eventos";
            $instance=new AutoUploadInvoice();
            if (isset($instance)) $instance->start();
        }
    }
    private function start() {
        //doclog("AUI: START UPLOAD INVOICE SERVICE", "autoupload",["invLen"=>count($this->invList)]);
        $this->isPostService=false;
        if (isset($this->invList[0])) {
            $upRes=$this->uploadList();
            doclog("AUI: END UPLOAD XML RESULT", "autoupload", ["upSaved"=>$upRes["nSvd"], "upFailed"=>$upRes["nErr"]]);
        } else if (!isset($this->errList[0])) {
            doclog("AUI: END EMPTY RESULT", "autoupload");
            self::$runningService=false;
        } else {
            doclog("AUI: END FAILED RESULT", "autoupload", ["numErrors"=>count($this->errList)]);
            self::$runningService=false;
        }
    }
    private function validate($bName,$valLvl=self::AUI_VLV_VALPDF) {
        global $autoUploadPath;
        $this->lastCFDIObj=null;
        $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__,"valLvl"=>$valLvl];
        $xmlPath="$autoUploadPath{$bName}.xml";
        $nombreArchivo=basename($bName); $rutaOrigen=dirname($bName);
        $rtV = [ "loadName"=>$bName, "fileName"=>$nombreArchivo, "filePath"=>$rutaOrigen, "xmlSize"=>filesize($xmlPath), "xmlDate"=>filemtime($xmlPath) ]; // toDo: optimizando variables, quitando xmlName, xmlPath, pdfName y pdfPath donde pueda usarse bName
        if ($valLvl<=self::AUI_VLV_XML) return $rtV;
         $pdfPath="$autoUploadPath{$bName}.pdf";
        if (file_exists($pdfPath)) {
            $rtV["pdfSize"]=filesize($pdfPath);
            $rtV["pdfDate"]=filemtime($pdfPath);
            //doclog("AUI.VAL: PDF FILE EXISTS", "autoupload", $baseData+["line"=>__LINE__,"files"=>$rtV]);
            if ($valLvl>=self::AUI_VLV_VALPDF) {
                require_once "clases/PDF.php"; $pdfObj=PDF::getImprovedFile($pdfPath);
                if (!isset($pdfObj)) throw new Exception(serialize(["message"=>isset(PDF::$errmsg[0])?PDF::$errmsg:"El archivo {$bName}.pdf no se pudo abrir","type"=>"i-pdf","desc"=>"obj is null","data"=>PDF::$errdata]+$baseData+["line"=>__LINE__]));
                $rtV["pdfObj"]=$pdfObj;
            }
        } else if(self::AUI_PDF_REQUIRED) throw new Exception(serialize(["message"=>"No existe el archivo {$bName}.pdf","type"=>"n-pdf","desc"=>"missing"]+$baseData+["line"=>__LINE__]));
        if ($valLvl>=self::AUI_VLV_VALXML) {
            if (!file_exists($xmlPath)) throw new Exception(serialize(["message"=>"No existe el archivo {$bName}.xml","type"=>"n-xml","desc"=>"missing"]+$baseData+["line"=>__LINE__]));
            //doclog("AUI.VAL: XML FILE EXISTS", "autoupload", $baseData+["line"=>__LINE__,"xmlPath"=>$xmlPath,"files"=>$rtV]);
            if (is_dir($xmlPath)) throw new Exception(serialize(["message"=>"La ruta {$bName}.xml es un directorio","type"=>"d-xml","desc"=>"not file"]+$baseData+["line"=>__LINE__]));
            //doclog("AUI.VAL: XML IS FILE", "autoupload", $baseData+["line"=>__LINE__,"xmlPath"=>$xmlPath]);
            $finfo=new finfo(FILEINFO_MIME_TYPE);
            $tt=$finfo->file($xmlPath);
            //doclog("AUI.VAL: FINFO", "autoupload", $baseData+["line"=>__LINE__,"type"=>$tt]);
            $fd = @fopen($xmlPath,"r");
            if ($fd===false) throw new Exception(serialize(["message"=>"AUI.VAL: FOPEN FAILED","type"=>"e-xml","desc"=>"fopen error"]+$baseData+["line"=>__LINE__]));
            $txt = fread($fd,filesize($xmlPath));
            if ($txt===false) throw new Exception(serialize(["message"=>"AUI.VAL: FREAD FAILED","type"=>"e-xml","desc"=>"fread error"]+$baseData+["line"=>__LINE__]));
            //fclose($fd);
            if (fclose($fd)===false) throw new Exception(serialize(["message"=>"AUI.VAL: FCLOSE FAILED","type"=>"e-xml","desc"=>"fclose error"]+$baseData+["line"=>__LINE__]));

            require_once "clases/CFDI.php";
            $errMsg="";
            $errStk="";
            $enough=true;
            $errLog="";
            //doclog("AUI.VAL: VALXML", "autoupload", $baseData+["line"=>__LINE__,"type"=>$tt,"files"=>$rtV]);
            try {
                CFDI::clearLastError();
                $this->lastCFDIObj=CFDI::newInstanceByFileName($xmlPath,"{$bName}.xml",$errMsg,$errStk,$enough,$errLog);
            } catch (Exception $ex) {
                throw new Exception(serialize(["message"=>"AUI.VAL.ERR: NEW INSTANCE FAILED","type"=>"x-cfdi","desc"=>"cfdi crash","data"=>getErrorData($ex)]+$baseData+["line"=>__LINE__]));
            }
            if ($this->lastCFDIObj==null) {
                $cfdiData=["cfdiError"=>$errMsg, "cfdiStack"=>$errStk];
                if (isset(CFDI::$lastException)) $cfdiData+=getErrorData(CFDI::$lastException);
                $errLog=str_replace(["http://www.sat.gob.mx","No matching global element declaration available, but demanded by the strict wildcard.","CFDI4.0 # GET self::QUERY_","/cfdi:Comprobante","/cfdi:Complemento","/cartaporte:CartaPorte","/cartaporte20:CartaPorte","/cartaporte30:CartaPorte","TimbreFiscalDigital","No se encontraron diferencias."], ["<@SAT>","...REQUERIDO...","CFDI4:GET-","/C:CMPR","/C:CMPL","/ctpt:CtaPte","/ctpt20:CtaPte","/ctpt30:CtaPte","TFD","NODIF"], $errLog);
                throw new Exception(serialize(["message"=>"AUI.VAL.ERR: NULL INSTANCE","type"=>"e-cfdi","desc"=>"cfdiobj is null","data"=>$cfdiData,"log"=>$errLog]+$baseData+["line"=>__LINE__]));
            }
            if (isset($rtV["pdfSize"])) {
                $this->lastCFDIObj->cache["pdfOriginalName"]="{$bName}.pdf";
                $this->lastCFDIObj->cache["pdfLoadFilePath"]=$pdfPath;
            }
            //doclog("AUI.VAL: HAS CFDI", "autoupload", $baseData+["line"=>__LINE__]);
            try {
                $this->lastCFDIObj->validar();
            } catch (Exception $ex) {
                $cfdiData=["cfdiError"=>$errMsg, "cfdiStack"=>$errStk]+getErrorData($ex);
                throw new Exception(serialize(["message"=>"AUI.VAL.ERR: VALIDATE ERROR","type"=>"x-cfdi","desc"=>"CfdiObj validation failed","data"=>$cfdiData,"log"=>$errLog]+$baseData+["line"=>__LINE__]));
            }
            usleep(333333);
            $uuid=$this->lastCFDIObj->cache["uuid"]??null;
            if (isset($uuid[0])) $rtV["uuid"]=$uuid;
            //doclog("AUI.VAL: CFDI LEGIBLE", "autoupload", $baseData+["line"=>__LINE__]);
            if (!$enough /*|| !$this->lastCFDIObj->enough */|| isset(CFDI::$lastException) || isset(CFDI::getLastError()["texto"][0])) {
                $cfdiData=["cfdiError"=>$errMsg, "cfdiStack"=>$errStk];
                $msg="AUI.VAL.ERR: ";
                $desc="Not enough";
                if (isset(CFDI::$lastException)) {
                    $msg.=".EXC: ".CFDI::$lastException->getMessage();
                    $cfdiData+=getErrorData(CFDI::$lastException);
                    $desc="LastException";
                } else if (isset(CFDI::getLastError()["texto"][0])) {
                    $cfdiData+=CFDI::getLastError();
                    $msg.=".ERR: ".strip_tags(CFDI::getLastError()["texto"]);
                    $desc="LastError";
                } else $msg.=".ERR: VALIDATION FAILED";
                $errData=["message"=>$msg,"type"=>"e-cfdi","desc"=>$desc,"data"=>$cfdiData,"log"=>$errLog];
                if (isset($uuid[0])) $errData["uuid"]=$uuid;
                throw new Exception(serialize($errData+$baseData+["line"=>__LINE__]));
            }
            $rtV["xmlObj"]=$this->lastCFDIObj;
        }
        $this->invList[]=$rtV;
        //return array_key_last($this->invList);
        return key(array_slice($this->invList, -1, 1 , true));
    }
    private function getInvPath($path, $valLvl, $depth="") {
        $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
        $relPath=substr($path,self::$pathLen);
        if (!isset($relPath[0])) $relPath=".";
        //doclog("AUI: getInvPath","autoupload", $baseData+["line"=>__LINE__,"depth"=>$depth,"path"=>$relPath]);
        if (isset($depth[0])) $depth.=".";
        $list=glob("{$path}*.xml");
        if (isset($list[0])) {
            $GLOBALS["ignoreTmpList"]=["list"];
            foreach($list as $idx=>$xmlPath) {
                $stepTime=microtime(true);
                $relPath=substr($xmlPath, self::$pathLen, -4);
                doclog("AUI: IN LOOP","autoupload",["idx"=>$idx, "relPath"=>$relPath,"stepTime"=>$stepTime]); // "depth"=>$depth,
                try {
                    $invIdx=$this->validate($relPath,$valLvl);
                    $invData=$this->invList[$invIdx];
                    //if ($this->isPostService) echoJSDoc("upkeep","AUI: Valid File", self::$inclusiveSeparator, $invData+["type"=>"valid"],"autoupload");
                    $dur=microtime(true)-$stepTime;
                    $this->invDuration+=$dur;
                    if ($this->minDuration<=0||$dur<$this->minDuration) $this->minDuration=$dur;
                    if ($dur>$this->maxDuration) $this->maxDuration=$dur;
                } catch (Exception $ex) {
                    $errIdx=$this->logAutoUploadError($ex, $relPath);
                    $this->errDuration+=(microtime(true)-$stepTime);
                }
                $this->lastCFDIObj=null;
                if ((count($this->invList)+count($this->errList))>=self::MAXLEN) {
                    doclog("AUI: MAXLEN Reached","autoupload",["idx"=>$idx]);
                    return;
                }
                $pathDur=microtime(true)-$stepTime;
                if ($pathDur<0.8) sleep(1);
            }
            unset($GLOBALS["ignoreTmpList"]);
        }
        $list=glob("{$path}*", GLOB_ONLYDIR);
        if (isset($list[0])) {
            $GLOBALS["ignoreTmpList"]=["list"];
            foreach ($list as $idx => $dirPath) {
                $basePath = basename($dirPath);
                if (strcmp($basePath, "Emitidos")!=0 && strcmp($basePath, "conError")!=0 && strcmp($basePath, "yaExiste")!=0 && strcmp($basePath, "guardado")!=0) {
                    $this->getInvPath($dirPath."/", $valLvl, $depth.($idx+1));
                    if ((count($this->invList)+count($this->errList))>=self::MAXLEN) return;
                }
            }
            unset($GLOBALS["ignoreTmpList"]);
        }
    }
    public function getInvList($valLvl=self::AUI_VLV_VALPDF, $force=false) {
        //if ($force||!(isset($this->invList[0])||isset($this->errList[0]))) $this->genInvList($valLvl);
        return $this->invList;
    }
    private function genInvList($valLvl=self::AUI_VLV_VALPDF) {
        $beginTime=microtime(true);
        $baseData=[];
        $this->invList=[];
        $this->errList=[];
        $this->minDuration=-1;
        $this->maxDuration=-1;
        $this->errDuration=0;
        $this->invDuration=0;
        global $autoUploadPath;
        $this->getInvPath($autoUploadPath,$valLvl);
        $invLen=count($this->invList);
        $errLen=count($this->errList);
        $totDuration=microtime(true)-$beginTime;
        $avgData=[];
        if ($invLen>0) $avgData["avgInvDur"]=$this->invDuration/$invLen;
        if ($errLen>0) $avgData["avgErrDur"]=$this->errDuration/$errLen;
        $totLen=$invLen+$errLen;
        if ($totLen>0) $avgData["avgTotDur"]=$totDuration/$totLen;
        doclog("AUI: REVIEW GEN INV LIST", "autoupload", ["validateLevel"=>$valLvl, "invLen"=>$invLen, "errLen"=>$errLen, "totDur"=>$totDuration, "minDur"=>$this->minDuration, "maxDur"=>$this->maxDuration, "medDur"=>($this->maxDuration+$this->minDuration)/2]+$avgData);
    }
    public function getErrList() {
        return $this->errList;
    }
    private function logAutoUploadError($ex, $baseName) {
        global $autoUploadPath, $autoUploadErrPath, $cmfObj;
        $dt=new DateTime();$dbdt=$dt->format("Y-m-d H:i:s");
        if ($ex instanceof Exception) {
            $erDt=@unserialize($ex->getMessage());
            if($erDt===false) {
                $erDt=getErrorData($ex);
                if (!isset($erDt["type"])) $erDt["type"]="x-noreg";
                if (!isset($erDt["desc"])) $erDt["desc"]="error indefinido";
            }
        } else if ($ex[0] instanceof Exception || isset($ex[0]["message"])) {
            $lastErrIndex=-1;
            foreach ($ex as $idx => $subEx)
                $lastErrIndex=$this->logAutoUploadError($subEx, $baseName);
            return $lastErrIndex;
        } else $erDt=$ex;
        if (!isset($erDt["message"])) $erDt["message"]="Mensaje de error del sistema";
        if (!isset($erDt["type"])) $erDt["type"]="n-unknown";
        if (!isset($erDt["desc"])) $erDt["desc"]="error desconocido";
        
        $nombreArchivo=basename($baseName); $rutaOrigen=dirname($baseName); $rutaDestino=$rutaOrigen;
        if (!isset($cmfObj)) { require_once "clases/CargaMF.php"; $cmfObj=new CargaMF(); }
        $erDt["fileName"]=$nombreArchivo;
        $erDt["filePath"]=$rutaOrigen;
        //$erDt["archivoOrigen"]=$baseName;
        //$erDt["archivoDestino"]=$baseName;
        $descripcion=$erDt["message"];
        if (isset($erDt["log"][0])) doclog("AUI: ERROR LOG","autoupload",["baseName"=>$baseName,"mensaje"=>$descripcion, "tipo"=>$erDt["type"], "desc"=>$erDt["desc"], "log"=>$erDt["log"]??"", "data"=>$errDt["data"]??""]);
        if (isset($descripcion[250])) $descripcion=substr($descripcion, 0, 247)."...";
        $fieldArray=["nombreArchivo"=>$nombreArchivo, "rutaArchivo"=>$rutaOrigen, "status"=>CargaMF::STATUS_OTRO, "fechaCarga"=>$dbdt, "descripcion"=>$descripcion, "tipo"=>$erDt["type"], "metodo"=>$erDt["desc"]??"", "datos"=>""];
        if (isset($erDt["data"])) {
            $edd=$erDt["data"];
            $fieldArray["datos"]=json_encode($edd);
            if (isset($edd["code"]) && in_array($edd["code"], [CFDI::EXCEPTION_UNREGISTERED_PROVIDER, CFDI::EXCEPTION_INACTIVE_PROVIDER, CFDI::EXCEPTION_DELETED_PROVIDER]))
                $fieldArray["status"]=CargaMF::STATUS_NOPROVEEDOR;
            else if (isset($edd["id"])) {
                $invId=$edd["id"];
                global $invObj; if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
                $invData=$invObj->getData("id=$invId",0,"uuid,ubicacion,nombreInterno,nombreInternoPDF");
                if (isset($invData[0])) {
                    $uuid=$invData[0]["uuid"];
                    $ubicacion=$invData[0]["ubicacion"];
                    $rutaDestino="yaExiste/{$ubicacion}";
                    $fieldArray["rutaArchivo"]= $ubicacion; //$rutaDestino;
                    //$erDt["archivoDestino"]=$rutaDestino.$nombreArchivo;
                    $fieldArray["idFactura"]=$invId;
                    $fieldArray["status"]=(strcmp(strtoupper($nombreArchivo), $uuid)==0)?CargaMF::STATUS_BDEXISTE:CargaMF::STATUS_YAEXISTE;
                    $erDt["message"]="El CFDI ya fue dado de alta";
                    $erDt["webPath"]=$ubicacion;
                } else $fieldArray["datos"]="ERROR: No se encontró CFDI con id=$invId. ".($fieldArray["datos"]??"");
            }
            unset($erDt["data"]);
            if (isset($fieldArray["datos"][500])) $fieldArray["datos"]=substr($fieldArray["datos"], 0, 500)."..."; // acepta 600 caracteres pero llega a intentar guardar 650 aprox. Con esta validación no debería... parece que no se están sumando las diagonales invertidas \\ pero en mysql si 
        }
        if (!$cmfObj->saveRecord($fieldArray)) {
            $erDt["saveCMFError"]="No se pudo guardar registro de error CargaMF";
        }
        if (isset($rutaDestino[0])) {
            $resp=self::moveTo($autoUploadPath.$rutaOrigen, $autoUploadErrPath.$rutaDestino, $nombreArchivo);
            foreach ($resp as $key => $value) $erDt[$key]=$value;
        }
        $this->errList[]=$erDt;
        if ($this->isPostService) echoJSDoc("upkeep", $erDt["message"], self::$inclusiveSeparator, $erDt); // ,"autoupload"
        return array_key_last($this->errList);
    }
    public static function moveTo($originalPath, $destinationPath, $filename) {
        if (!file_exists($destinationPath) && !mkdir($destinationPath, 0777, true)) return ["moveError"=>["mkdir"=>"No se pudo crear el subdirectorio de error $destinationPath"]];
        $result=[];
        $xmlPath=$originalPath."/".$filename.".xml";
        if (file_exists($xmlPath)) {
            $result["mvdXmlSz"]=filesize($xmlPath);
            $result["mvdXmlDt"]=filemtime($xmlPath);
            if (!rename($xmlPath, $destinationPath."/".$filename.".xml")) $result["moveError"]=["rename"=>"No se pudo mover archivo {$filename}.xml a ruta de error"];
            else doclog("AUI: MOVED PDF","autoupload",["filename"=>$filename,"newPath"=>substr($destinationPath,self::$pathLen), "oldPath"=>substr($originalPath,self::$pathLen)]);
        } else $result["moveError"]=["exists"=>"No existe el archivo {$xmlPath}"];
        $pdfPath=$originalPath."/".$filename.".pdf";
        if (file_exists($pdfPath)) {
            $result["mvdPdfSz"]=filesize($pdfPath);
            $result["mvdPdfDt"]=filemtime($pdfPath);
            if (!rename($pdfPath, $destinationPath."/".$filename.".pdf")) {
                if (!isset($result["moveError"])) $result["moveError"]=[];
                $result["moveError"]["rename"]="No se pudo mover archivo {$filename}.pdf a ruta de error";
            } else doclog("AUI: MOVED XML","autoupload",["filename"=>$filename,"newPath"=>substr($destinationPath,self::$pathLen), "oldPath"=>substr($originalPath,self::$pathLen)]);
        }
        return $result;
    }
    public function uploadList() {
        if (!self::$runningService) return;
        global $autoUploadPath;
        $baseData=["file"=>getShortPath(__FILE__),"function"=>__FUNCTION__];
        $uploadResults=["nSvd"=>0,"nErr"=>0];
        foreach ($this->invList as $invIdx => $rtV) {
            if (!file_exists($autoUploadPath.$rtV["loadName"].".xml")) {
                doclog("AUI: UPLOAD ERROR File Not Found", "autoupload", $baseData+["line"=>__LINE__,"idx"=>$invIdx,"data"=>$rtV]);
                continue;
            }
            if (isset($rtV["xmlObj"])) {
                //doclog("AUI: UPLOAD HasXML", "autoupload", $baseData+["line"=>__LINE__,"idx"=>$invIdx,"data"=>$rtV]);
                $uploadResults[$invIdx]=["result"=>"found"];
                if (isset($rtV["uuid"][0])) $baseData["uuid"]=$rtV["uuid"];
                if ($rtV["xmlObj"]->prepareData()) {
                    $uploadResults[$invIdx]["result"]="prepared";
                    //doclog("AUI: UPLOAD Prepared","autoupload",$baseData+["line"=>__LINE__,"idx"=>$invIdx]);
                    if ($rtV["xmlObj"]->saveData()) {
                        // doclog("AUI: UPLOAD Saved","autoupload",$baseData+["line"=>__LINE__,"idx"=>$invIdx]);
                        $rutaDestino="guardado/{$rutaOrigen}";
                        $resp=self::moveTo($autoUploadPath.$rutaOrigen, $autoUploadPath.$rutaDestino, $nombreArchivo);
                        // foreach ($resp as $key => $value) $erDt[$key]=$value;

                        doclog("AUI: CFDI UPLOADED & SAVED!","autoupload",$rtV);
                        if ($this->isPostService) echoJSDoc("upkeep","AUI: Saved CFDI", self::$inclusiveSeparator, $rtV+["type"=>"saved"],"autoupload");

                        $uploadResults[$invIdx]["result"]="saved";
                        $uploadResults["nSvd"]++;
                        // $uploadResults[$invIdx]["data"]=$rtV["xmlObj"]->data; // data is private... instead add only necesary data from xmlObj
                        if (!$cmfObj->saveRecord($fieldArray)) {
                            doclog("AUI: CMF ERROR: No se pudo guardar registro en CargaMF","autoupload");
                        }
                    } else {
                        doclog("AUI: UPLOAD ERROR trying to saveData","autoupload",$baseData+["line"=>__LINE__,"idx"=>$invIdx,"errors"=>$rtV["xmlObj"]->cache["errors"]]);
                        $uploadResults[$invIdx]["result"]="error";
                        $uploadResults[$invIdx]["message"]="Error al guardar factura";
                        $uploadResults[$invIdx]["errors"]=$rtV["xmlObj"]->cache["errors"];
                        $uploadResults["nErr"]++;

                        $this->logAutoUploadError(["message"=>"Error al guardar factura","type"=>"f-save","desc"=>"cfdi save failed","data"=>$rtV["xmlObj"]->cache["errors"]]+$baseData+["line"=>__LINE__,"idx"=>$invIdx], $rtV["loadName"]);
                    }
                } else {
                    doclog("AUI: UPLOAD ERROR trying to prepareData","autoupload",$baseData+["line"=>__LINE__,"idx"=>$invIdx,"errors"=>$rtV["xmlObj"]->cache["errors"]]);
                    $uploadResults[$invIdx]["result"]="error";
                    $uploadResults[$invIdx]["message"]="Error en datos de la factura";
                    $uploadResults[$invIdx]["errors"]=$rtV["xmlObj"]->cache["errors"];
                    $uploadResults["nErr"]++;

                    $this->logAutoUploadError(["message"=>"Error en datos de la factura","type"=>"f-prep","desc"=>"bad cfdi data","data"=>$rtV["xmlObj"]->cache["errors"]]+$baseData+["line"=>__LINE__,"idx"=>$invIdx], $rtV["loadName"]);
                }
            } else {
                doclog("AUI: UPLOAD ERROR XMLOBJ Not found in rtV","autoupload",$baseData+["line"=>__LINE__,"idx"=>$invIdx]);
                $uploadResults[$invIdx]=["result"=>"error","message"=>"No se encuentran datos del XML"];
                $uploadResults["nErr"]++;

                $this->logAutoUploadError(["message"=>"Error no se encuentra el XML","type"=>"i-xobj","desc"=>"No XML to upload","data"=>$rtV]+$baseData+["line"=>__LINE__,"idx"=>$invIdx], $rtV["loadName"]);
            }
        }
        self::$runningService=false;
        return $uploadResults;
    }
}
?>
