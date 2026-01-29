<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/BasePDF.php";

class PDF extends BasePDF {
    var $originalName=null;
    var $pageCount=0;
    var $stampName="sello1.png";
    public static $errmsg="";
    public static $errdata=[];
    public static function getImprovedFile($pdfName,$dwnSfx="v1_5",$dwnLpCnt=3) {
        $pdfObj=null;
        try {
            $pdfObj=new PDF($pdfName);
        } catch (Exception $ex) {
            if ($ex->getCode()==268) {
                self::$errmsg="Debe ingresar un archivo PDF que no esté encriptado";
                self::$errdata=["pdfName"=>$pdfName,"error"=>getErrorData($ex)];
                $pdfObj=null;
            //} else if ($ex->getCode()==267) { error.php
            } else try {
                $pdfObj=PDF::getDowngradedVersion($pdfName,$dwnSfx,$dwnLpCnt);
            } catch (Exception $ex2) {
                self::$errmsg="El archivo PDF no es válido, verifique que el documento no esté vacío o corrupto";
                self::$errdata=["pdfName"=>$pdfName,"error"=>getErrorData($ex),"error2"=>getErrorData($ex2)];
                $pdfObj=null;
            }
        }
        return $pdfObj;
    }
    public static function getDowngradedVersion($fileName,$suffix="v1_5",$loopCount=3) {
        if (is_null($suffix)) $suffix="v1_5";
        if (!file_exists($fileName)) {
            doclog("No se encontró el archivo","pdferr",["fileName"=>$fileName]);
            throw new Exception("No se encontró el archivo $fileName");
        }
        $v15Name=substr($fileName, 0, -4).$suffix.substr($fileName, -4);
        if (!rename($fileName,$v15Name)) {
            doclog("No se pudo renombrar el archivo original","pdferr",["fileName"=>$fileName,"v15Name"=>$v15Name]);
            throw new Exception("No se pudo renombrar el archivo original $v15Name");
        }
        $thisLoopCount=$loopCount;
        while (file_exists($fileName)||!file_exists($v15Name)) {
            doclog("Esperando renombre de archivo","pdf",["filename"=>[$fileName,file_exists($fileName)?"exists":"doesntExist"],"v15Name"=>[$v15Name,file_exists($v15Name)?"exists":"doesntExist"], "count"=>$thisLoopCount]);
            $thisLoopCount--;
            if ($thisLoopCount<0) break;
            sleep(2);
        }
        if (file_exists($fileName)) {
            doclog("No se renombró el archivo original","pdferr",["filename"=>[$fileName,file_exists($fileName)?"exists":"doesntExist"],"v15Name"=>[$v15Name,file_exists($v15Name)?"exists":"doesntExist"]]);
            throw new Exception("No se renombró el archivo original $fileName");
        }
        if (!file_exists($v15Name)) {
            doclog("No se encontró el archivo renombrado","pdferr",["filename"=>[$fileName,file_exists($fileName)?"exists":"doesntExist"],"v15Name"=>[$v15Name,file_exists($v15Name)?"exists":"doesntExist"]]);
            throw new Exception("No se encontró el archivo renombrado $v15Name");
        }
        $errlogFile=$_SERVER['DOCUMENT_ROOT']."LOGS/pdferr.log";
        $errV15=BasePDF::downgradeVersion($v15Name, $fileName, $errlogFile);
        if (isset($errV15[0])) {
            if (!rename($v15Name,$fileName)) {
                doclog("Falló la degradación de versión y renombrar archivo.","pdferr",["filename"=>[$fileName,file_exists($fileName)?"exists":"doesntExist"],"v15Name"=>[$v15Name,file_exists($v15Name)?"exists":"doesntExist"],"error"=>$errV15]);
                throw new Exception($errV15.". No se pudo renombrar.");
            }
            doclog("Falló la degradación de versión.","pdferr",["fileName"=>$fileName,"v15Name"=>$v15Name,"error"=>$errV15]);
            throw new Exception($errV15);
        }
        $thisLoopCount=$loopCount;
        while (!file_exists($fileName)) {
            doclog("Esperando degradación de archivo","pdf",["filename"=>[$fileName,file_exists($fileName)?"exists":"doesntExist"], $thisLoopCount]);
            $thisLoopCount--;
            if ($thisLoopCount<0) break;
            sleep(2);
        }
        if (file_exists($fileName)) {
            doclog("Degradando version PDF para sellar factura","pdf",["filename"=>$fileName]);
            return new PDF($fileName);
        } else {
            if (!rename($v15Name,$fileName)) {
                doclog("Falló la degradación de versión silenciosa y renombrado de archivo.","pdferr",["fileName"=>$fileName,"v15Name"=>$v15Name]);
                throw new Exception("No se reparó el archivo ni se recuperó el original.","pdferr",["fileName"=>$fileName,"v15Name"=>$v15Name]);
            }
            doclog("Falló la degradación de versión silenciosa.","pdferr",["fileName"=>$fileName,"v15Name"=>$v15Name]);
            throw new Exception("No se encontro archivo $fileName");
        }
    }
    function __construct($pdfname) {
        $this->originalName = $pdfname;
        $this->pageCount = $this->setSourceFile($pdfname);
        doclog("New PDF","pdf",["originalName"=>$this->originalName,"pageCount"=>$this->pageCount]);
        parent::__construct();
    }
    function setStampFile($stampFileName) {
        if (file_exists($stampFileName)) {
            $this->stampName=$stampFileName;
        } else return "No existe el archivo '$stampFileName'";
        return "";
    }
    function addStamp($dateStr, $nameStr, $isLastPage=false) {
        // $isLastPage=false => first page
        for ($pageNo=1; $pageNo<=$this->pageCount; $pageNo++) {
            $tplIdx = $this->importPage($pageNo);
            $this->AddPage();
            $this->useTemplate($tplIdx, ['adjustPageSize' => true]);
            if (($isLastPage && $pageNo===$this->pageCount)||(!$isLastPage && $pageNo===1)) {
                $pgWid=$this->GetPageWidth();
                $pgHgt=$this->GetPageHeight();
                $imWid=66;
                $imHgt=42;
                $imX=$pgWid-20-$imWid;
                $imY=$pgHgt-60-$imHgt;
                $this->Rotate(15,$imX,$imY);
                $this->Image($this->stampName,$imX,$imY,$imWid);
                $this->SetFont('Helvetica','B',12);
                $this->SetTextColor(0,0,180);

                $t1X=$imX+$imWid*0.4;
                $t1Y=$imY+$imHgt*0.27;
                $this->SetXY($t1X,$t1Y);
                $this->Write(8, $dateStr);
                $t2X=$imX+$imWid*0.03;
                $t2Y=$imY+$imHgt*0.47;
                $this->SetXY($t2X,$t2Y);
                $this->MultiCell(62,8.8,utf8_decode('                         '.$nameStr),0,'C');
                //$this->MultiCell(62,8.8,mb_convert_encoding('                         '.$nameStr, 'ISO-8859-1', 'UTF-8'),0,'C');
            }
        }
    }
    function saveMergedFile($secondFileName, $afterPgNum=null, $newFileName=null) {
        $logData=["originalName"=>$this->originalName,"secondFileName"=>$secondFileName,"pageCount"=>$this->pageCount];
        $firstPageCount=$this->pageCount;
        if (!isset($afterPgNum) || $afterPgNum>$firstPageCount) $afterPgNum=$firstPageCount;
        $logData["afterPgNum"]=$afterPgNum;
        doclog("SaveMergedFile INI","pdf",$logData);
        for($fileNum=0;$fileNum<3;$fileNum++) {
            if ($fileNum==0) {
                $pageNo=1;
                if ($afterPgNum<$firstPageCount)
                    $pageCount=$afterPgNum;
                else $pageCount=$firstPageCount;
                doclog("SaveMergedFile ZERO","pdf",["fileNum"=>$fileNum,"pageNo"=>$pageNo,"afterPgNum"=>$afterPgNum,"firstPageCount"=>$firstPageCount,"pageCount"=>$pageCount]);
            } else if ($fileNum==1) {
                $pageNo=1;
                $pageCount=$this->setSourceFile($secondFileName);
                $this->pageCount=$firstPageCount+$pageCount;
                doclog("SaveMergedFile setSourceFile(fileNum==1)","pdf",["fileNum"=>$fileNum,"pageNo"=>$pageNo,"pageCount"=>$pageCount,"fileName"=>$secondFileName]);
            } else if ($afterPgNum>=$firstPageCount) {
                doclog("SaveMergedFile BEYOND","pdf",["fileNum"=>$fileNum,"pageNo"=>$pageNo,"afterPgNum"=>$afterPgNum,"firstPageCount"=>$firstPageCount,"pageCount"=>($pageCount??"none")]);
                break;
            } else {
                $pageNo=$afterPgNum+1;
                $pageCount=$this->setSourceFile($this->originalName);
                doclog("SaveMergedFile setSourceFile","pdf",["fileNum"=>$fileNum,"pageNo"=>$pageNo,"pageCount"=>$pageCount,"fileName"=>$this->originalName]);
            }
            for (; $pageNo <= $pageCount; $pageNo++) {
                doclog("SaveMergedFile importPage","pdf",["pageNo"=>$pageNo]);
                $templateId = $this->importPage($pageNo);
                doclog("SaveMergedFile getTemplateSize","pdf");
                $size = $this->getTemplateSize($templateId);
                if ($size['width'] > $size['height']) {
                    doclog("SaveMergedFile AddPage L","pdf");
                    $this->AddPage('L', array($size['width'], $size['height']));
                } else {
                    doclog("SaveMergedFile AddPage P","pdf");
                    $this->AddPage('P', array($size['width'], $size['height']));
                }
                doclog("SaveMergedFile useTemplate","pdf");
                $this->useTemplate($templateId);

                //$this->SetFont('Helvetica');
                //$this->SetXY(5, 5);
                //$this->Write(8, 'InvoiceCheck merged PDF');
            }
            doclog("SaveMergedFile Ended for loop","pdf");
        }
        $pathChunks=explode("/",$this->originalName);
        if (!isset($newFileName)) {
            $newName="MRG_".array_pop($pathChunks);
            $path = implode("/", $pathChunks)."/";
        } else {
            $newFileChunks=explode("/",$newFileName);
            $newName=array_pop($newFileChunks);
            if (isset($newFileChunks[0]))
                $path = implode("/", $newFileChunks)."/";
            else {
                array_pop($pathChunks);
                $path = implode("/", $pathChunks)."/";
            }
        }
        doclog("SaveMergedFile saveFile","pdf",["path"=>$path,"newName"=>$newName]);
        $saveResult=$this->saveFile($path.$newName);
        $this->Close();
        if ($saveResult) return $newName;
        throw new Exception("No se guardó el archivo PDF '{$path}' '{$newName}'");
    }
    function saveDelPageFile($firstPageNum,$lastPageNum, $newFileName=null) {
        if ($firstPageNum>$lastPageNum) {
            $auxPageNum=$firstPageNum;
            $firstPageNum=$lastPageNum;
            $lastPageNum=$auxPageNum;
        }
        $pageCount=$this->pageCount;
        for ($pageNo=1; $pageNo <= $pageCount; $pageNo++) {
            if ($pageNo>=$firstPageNum && $pageNo<=$lastPageNum) continue;
            doclog("importPage","pdf",["pageNo"=>$pageNo]);
            $templateId = $this->importPage($pageNo);
            $size = $this->getTemplateSize($templateId);
            if ($size['width'] > $size['height']) {
                $this->AddPage('L', array($size['width'], $size['height']));
            } else {
                $this->AddPage('P', array($size['width'], $size['height']));
            }
            $this->useTemplate($templateId);
        }
        $pathChunks=explode("/",$this->originalName);
        if (!isset($newFileName)) {
            $newName="REM_".array_pop($pathChunks);
            $path = implode("/", $pathChunks)."/";
        } else {
            $newFileChunks=explode("/",$newFileName);
            $newName=array_pop($newFileChunks);
            if (isset($newFileChunks[0]))
                $path = implode("/", $newFileChunks)."/";
            else {
                array_pop($pathChunks);
                $path = implode("/", $pathChunks)."/";
            }
        }
        $saveResult=$this->saveFile($path.$newName);
        doclog("delPages","pdf",["path"=>$path,"firstPage"=>$firstPageNum,"lastPage"=>$lastPageNum,"newName"=>$newName,"saveResult"=>($saveResult?"true":"false")]);
        $this->Close();
        return $newName;
    }
}
