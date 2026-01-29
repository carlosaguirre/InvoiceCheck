<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/BasePDF.php";
class PDFTools {
    private $mergelist=null;
    private $mergename=null;
    private $webname=null;
    private $pdf=null;
    private $log="";
    private static $basePath=null;
    private static $filePath=null;
    private static $savePath=null;
    private static $webPath=null;
    protected $nl="\n";
    public static function init() {
        if (!isset(static::$basePath[0])) static::$basePath=/* configuracion/meta */getBasePath(); // dirname(__DIR__)."\\";
        if (!isset(static::$filePath[0])) static::$filePath=[static::$basePath."archivos\\", static::$basePath."recibos\\", static::$basePath, dirname(static::$basePath)."\\"];
        if (!isset(static::$savePath[0])) static::$savePath=dirname(static::$basePath)."\\docs\\tmp\\";
        if (!isset(static::$webPath[0])) static::$webPath=$_SERVER["HTTP_ORIGIN"]."/docs/tmp/";
    }
    protected function __construct() {
        static::init();
        $this->setLog("Base Path: '".static::$basePath."'");
    }
    public static function create() {
        return new static();
    }
    public static function getFilePath() {
        static::init();
        return static::$filePath;
    }
    public static function getSavePath() {
        static::init();
        return static::$savePath;
    }
    public static function getWebPath() {
        static::init();
        return static::$webPath;
    }
    public static function addWebPath($relativeFileList) {
        static::init();
        return array_map(fn($item) => static::getWebPath().$item, $relativeFileList);
    }
    public static function addLocalPath($relativeFileList) {
        static::init();
        return array_map(fn($item) => static::getSavePath().$item, $relativeFileList);
    }
    public function getMergeList() {
        return $this->mergelist;
    }
    public function setMergeList($filelist) {
        $this->mergelist=[];
        $webBase=$_SERVER["HTTP_ORIGIN"]."/";
        $webLen=strlen($webBase);
        $localBase=dirname(static::$basePath)."\\";
        foreach ($filelist as $idx => $name) {
            if (strpos($name, $webBase)===0) $name=$localBase.str_replace("/","\\",substr($name, $webLen));
            else $name=str_replace("/","\\",$name);
            if (file_exists($name)) {
                $name=$this->convertXML($name);
                $this->mergelist[]=$name;
            } else {
                $added=false;
                foreach (static::$filePath as $fi => $path) {
                    $fullname=$this->convertXML($path.$name);
                    if (file_exists($fullname)) {
                        $this->mergelist[]=$fullname;
                        $added=true;
                        break;
                    }
                }
                if (!$added) $this->setLog("No se encontró el archivo '$name'=>'$fullname'");
            }
        }
        return $this;
    }
    private function convertXML($filename) {
        ;
        return $filename;
    }
    public function getWebName() {
        return $this->webname;
    }
    public function getMergeName() {
        return $this->mergename;
    }
    public function setMergeName($filename) {
        $basename=basename($filename);
        if ($basename===$filename) {
            $this->mergename = static::$savePath.$filename;
            $this->webname = static::$webPath.$filename;
        } else {
            $dirname=dirname($filename);
            if (file_exists($dirname)) {
                $this->mergename = $filename;
                //$this->webname=$
                // ToDo: Analizar ruta:
                // $serverPath = dirname(dirname(__DIR__))."\\";
                // ToDo: si empieza con $serverPath, reemplazar con $_SERVER["HTTP_ORIGIN"]
                // invoiceSharePath= Si empieza con C:\InvoiceShare usar consultas/Docs.php
            } else if (file_exists(static::$savePath.$dirname)) {
                $this->mergename = static::$savePath.$dirname."\\".$basename;
                $this->webname = static::$webPath.$dirname."/".$basename;
            } else if (file_exists(static::$savePath.$basename)){
                $this->mergename = static::$savePath.$basename;
                $this->webname = static::$webPath.$basename;
            }
        }
        return $this;
    }
    public function break($filepath,$breakName=null) {
        $this->setLog(false);
        if (isset($breakName[0])) {
            $base=pathinfo($breakName);
            $basename=$base["filename"];
            $ext=$base["extension"]??"pdf";
            if (!isset($ext[0])) $ext="pdf";
        } else {
            $dt=new DateTime();
            $basename="onePage".$dt->format("ymdHis");
            $ext="pdf";
        }
        $pgNum=1; $pageCount=0; $simpleList=[];
        do {
            try {
                $this->pdf=new BasePDF();
                $pageCount=$this->pdf->setSourceFile($filepath);
                $digitNum=strlen("$pageCount");
                $templateId=$this->pdf->importPage($pgNum);
                $size=$this->pdf->getTemplateSize($templateId);
                $pgWid=$size['width'];
                $pgHgt=$size['height'];
                $this->pdf->AddPage($pgWid>$pgHgt?'L':'P', array($pgWid,$pgHgt));
                $this->pdf->useTemplate($templateId);
                $chunkName=$basename."_".str_pad("$pgNum", $digitNum, "0", STR_PAD_LEFT);
                if ($this->pdf->saveFile(static::$savePath.$chunkName.".$ext")) {
                    $this->setLog("PDF BROKE SUCCESSFULLY AT PAGE $pgNum");
                } else {
                    $this->setLog("ERROR SAVING BROKE FILE OF PAGE $pgNum");
                }
                $this->pdf->Close();
                $simpleList[]=$chunkName.".$ext";
                // $webList[]=static::$webPath.$chunkName.".$ext";
                // $localList[]=static::$savePath.$chunkName.".$ext";
                $pgNum++;
            } catch (Exception $ex) {
                $txt=method_exists($ex, "getCode")?" (".$ex->getCode().")":"";
                $txt.=method_exists($ex, "getFile")?" @".$ex->getFile():"";
                $txt.=method_exists($ex, "getLine")?"|".$ex->getLine():"";
                $txt.=method_exists($ex, "getMessage")?": ".$ex->getMessage():"";
                $this->setLog("Error al anexar archivo '$filepath': ".get_class($ex).$txt);
            }
        } while ($pgNum<=$pageCount);
        return $simpleList;
        //return ["basename"=>$basename,"count"=>$pageCount,"simple"=>$simpleList,"log"=>$this->log,"padSize"=>$digitNum];
    }
    public function merge() {
        $this->pdf=new BasePDF();
        $isValid=false;
        foreach ($this->mergelist as $idx => $filepath) {
            try {
                $pageCount=$this->pdf->setSourceFile($filepath);
                for ($pageNo=1; $pageNo<=$pageCount; $pageNo++) {
                    $templateId=$this->pdf->importPage($pageNo);
                    $size=$this->pdf->getTemplateSize($templateId);
                    $pgWid=$size['width'];
                    $pgHgt=$size['height'];
                    $this->pdf->AddPage($pgWid>$pgHgt?'L':'P', array($pgWid,$pgHgt));
                    $this->pdf->useTemplate($templateId);
                }
                $isValid=true;
            } catch (Exception $ex) {
                $txt=method_exists($ex, "getCode")?" (".$ex->getCode().")":"";
                $txt.=method_exists($ex, "getFile")?" @".$ex->getFile():"";
                $txt.=method_exists($ex, "getLine")?"|".$ex->getLine():"";
                $txt.=method_exists($ex, "getMessage")?": ".$ex->getMessage():"";
                $this->setLog("Error al anexar archivo '$filepath': ".get_class($ex).$txt);
            }
        }
        if ($isValid && $this->pdf->saveFile($this->mergename)) {
            $this->setLog("PDF MERGED SUCCESSFULLY '".$this->mergename."'");
        } else if ($isValid) {
            $this->setLog("ERROR AL GUARDAR ARCHIVO '".$this->mergename."'");
        } else {
            $this->setLog("ERROR AL GENERAR ARCHIVO '".$this->mergename."'");
        }
        $this->pdf->Close();
        return $this;
    }
    protected function setLog($texto) {
        if ($texto===false) $this->log="";
        else if (!empty($texto)) {
            if (empty($this->log)) $this->log=$texto;
            else $this->log.=$this->nl.$texto;
        }
    }
    public function getLog() {
        return $this->log;
    }
}
