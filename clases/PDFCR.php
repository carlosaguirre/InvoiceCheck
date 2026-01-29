<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/BasePDF.php";
class PDFCR {
    private static $daysBehind=2;
    private static $maxLogsPerHour=10;
    private static $filePath=null;
    private $day=null;
    private $timezone=null;
    private $fmt=null;
    private $error=null;
    private $data=null;
    private $basePath=null;
    private $query=null;
    private $bpdfObj=null;
    private $users=[];
    public $errIds=[];
    public $log=[];
    function __construct($day=null,$timezone=null,$skipGettingData=false) {
        $this->prepareValues($day, $timezone);
        if (!$skipGettingData) $this->retrieveData();
    }

    private function prepareValues($day=null, $timezone=null) {
        if (DBi::$conn===null) {
            $this->addError("No hay conexión");
            $this->day=$day;
            return;
        }
        $this->data=null;
        $this->basePath=dirname(__DIR__)."\\"; //str_replace("\\", "/", dirname(__DIR__))."/";
        $this->timezone = isset($timezone)?$timezone:new DateTimeZone("Etc/GMT+6");
        if (isset($day)) {
            $type=gettype($day);
            if ($type==="object") $type=get_class($day);
        } else {
            $day=new DateTime("now",$this->timezone);
            $type="DateTime";
        }
        doclog("PDFCR Construct","pruebas",["day"=>$day,"type"=>$type]);
        if ($type==="integer") {
            $time=$day;
            $day=new DateTime();
            $day->setTimestamp($time);
            $type="DateTime";
        } else if ($type==="string") {
            try {
                $day=new DateTime($day,$this->timezone);
                $type="DateTime";
            } catch (Exception $e) {
                $ear=getErrorData($e);
                $ecl=basename($ear["class"]??"");
                $ecd=$ear["code"]??"";
                $efl=$ear["file"]??"";
                $eln=$ear["line"]??"";
                $ems=$ear["message"]??"";
                $etr=$ear["trace"]??"";
                $etx=$ecl."#".$ecd;
                $iki=strpos($efl, "InvoiceCheckShare");
                $iix=strpos($efl, "invoice");
                if ($iki!==false) $etx.="-".substr($efl, $iki+18)."(".$eln.")";
                else if ($iix!==false) $etx.="-".substr($efl, $iix+8)."(".$eln.")";
                $etx.=": ".$ems."\n".str_replace(["\\n","C:\\Apache24\\htdocs\\invoice\\","C:\\\\Apache24\\\\htdocs\\\\invoice\\\\"], ["\n","",""], $etr);
                $this->addError("[day:'".($day??"NULL")."', timezone:'".$this->timezone->getName()."'] ".$etx);
                $this->day=$day;
                return;
            }
        }
        if ($type==="DateTime") {
            $this->day=$day->format("Y-m-d");
            $this->fmt=$day->format("ymd");
        } else {
            $this->addError("Tipo de dato inválido '".$type."'");
            $this->day=$day;
            return;
        }
        $this->query="select f.id fId, cr.id crId, cr.fechaRevision, g.id gId, concat(cr.aliasGrupo,'-',cr.folio) folioCR, cr.codigoProveedor codProv, cf.folioFactura folioF, cr.credito, cr.fechaPago, lower(left(cf.tipoComprobante,1)) tc, concat(f.ubicacion, f.nombreInternoPDF,'.pdf') archivoPDF, f.nombreInterno, f.ciclo, date(f.fechaFactura) fechaFactura, cast((f.statusn&32)/32 as unsigned) pagado, cast((f.statusn&64)/64 as unsigned) recpago, cr.numAutorizadas nAuth, cf.autorizadaPor fAuth from contrafacturas cf inner join contrarrecibos cr on cf.idContrarrecibo=cr.id inner join facturas f on cf.idFactura=f.id inner join grupo g on cr.rfcGrupo=g.rfc left join solicitudpago s on s.idFactura=f.id where s.folio is null and fechaRevision between '{$this->day} 00:00:00' and '{$this->day} 23:59:59' order by cr.aliasGrupo asc, cr.codigoProveedor asc, cf.folioFactura asc, f.fechaFactura asc";
        // select f.id fId, cr.id crId, cr.fechaRevision, concat(cr.aliasGrupo,'-',cr.folio) folioCR, cr.codigoProveedor codProv, cf.folioFactura folioF, cr.credito, cr.fechaPago, lower(left(cf.tipoComprobante,1)) tc, concat(f.ubicacion, f.nombreInternoPDF,'.pdf') archivoPDF, cr.numAutorizadas nAuth, cf.autorizadaPor fAuth, s.folio solFolio from contrafacturas cf inner join contrarrecibos cr on cf.idContrarrecibo=cr.id inner join facturas f on cf.idFactura=f.id left join solicitudpago s on s.idFactura=f.id where s.folio is null and fechaRevision between '2021-09-03 00:00:00' and '2021-09-03 23:59:59' order by cr.aliasGrupo asc, cr.codigoProveedor asc, cf.folioFactura asc;
    }
    public function getDay() {
        return $this->day;
    }
    function retrieveData() {
        $result=DBi::query($this->query);
        if (is_bool($result)) {
            $this->addError($result?"Resultado aprobado pero sin datos: ":json_encode(DBi::$errors));
            return;
        } else {
            $len=$result->num_rows;
            while ($row = $result->fetch_assoc()) {
                if (is_null($this->data))
                    $this->data=[$row];
                else $this->data[]=$row;
            }
            $result->close();
        }
        if (isset(DBi::$errors[0])) {
            $this->addError(json_encode(DBi::$errors));
            return;
        }
        if (!isset($this->data[0])) {
            $this->addError("No se generaron datos");
            return;
        }
        $this->getUsers("Reporte FCRD");
    }
    private function getUsers($perfil) {
        global $perObj;
        if (!isset($perObj)) {
            require_once "clases/Perfiles.php";
            $perObj = new Perfiles();
        }
        $perObj->rows_per_page=0;
        $perWhr=$perObj->getWhereCondition("nombre", $perfil);
        if (isset($perWhr[0])) $perWhr=rtrim($perWhr," AND ");
        // select id from Perfiles where nombre="Reporte FCRD";
        $perData=$perObj->getData($perWhr,0,"id");
        if (isset($perData[0])) {
            $perIds=array_column($perData, "id");
            global $ugObj, $usrObj;
            if (!isset($ugObj)) {
                require_once "clases/Usuarios_Grupo.php";
                $ugObj = new Usuarios_Grupo();
            } else $ugObj->clearOrder();
            $ugObj->rows_per_page=0;
            $ugObj->addOrder("idUsuario");
            $ugWhr=$ugObj->getWhereCondition("idPerfil",$perIds);
            if (isset($ugWhr[0])) $ugWhr=rtrim($ugWhr," AND ");
            // select idUsuario, group_concat(idGrupo) idGrupos from usuarios_grupo where idPerfil=120 group by idUsuario;
            $ugData=$ugObj->getData($ugWhr,0,"idUsuario, group_concat(idGrupo) idGrupos", "", "idUsuario");
            foreach ($ugData as $ugIdx => $ugRow) {
                $this->users[$ugRow["idUsuario"]]=["idGroup"=>explode(",",$ugRow["idGrupos"])];
            }
        }
        
    }
    /*function createFileProgressively() {
        if ($this->hasErrors()) return "ERROR";
        if ($this->bpdfObj==null) {
            $this->bpdfObj=new BasePDF();
        }
        if (!isset($this->dataIdx)) $this->dataIdx=0;
        $row=$this->data[$this->dataIdx];
        //ToDo: Add all the code inside the loop
        //return ((int)(10000*$this->dataIdx/$this->dataLen))/100;
        $this->dataIdx++;
        return substr(sprintf('%01.2f',100*$this->dataIdx/$this->dataLen), 0, 5);
    }*/
    function createFiles() {
    // toDo: crear funcion cuasi-asincrona:
        // Si dataIdx negativo y hay errores 
        if ($this->hasErrors()) return false;
        $this->errIds=[];
        $newName=[];
        $marginX=10;
        $marginY=19;
        $angle=15;
        $radAng=deg2rad($angle);
        $retcos=1-cos($radAng);
        $radWid=deg2rad(90-$angle/2);
        $sinWid=sin($radWid);
        $radHgt=deg2rad($angle/2);
        $sinHgt=sin($radHgt);
        $stampDataLength=5; // numero de lineas dentro del recuadro del sello
        $stampHgt=5*$stampDataLength+4; // toDo: El 5 constante debe ser el ancho del renglon, tambien se puede hacer constante
        $dSH2=2*($stampHgt**2);
        $sqRs=sqrt($dSH2*$retcos);
        $paidFontSize=96;
        $paidFontHeight=20;
        $paidText="PAGADA";
        $paidAngle=45;
        $paidLineWid=6;
        $paidLineSpace=2;
        $paidColor=[60,60,180];
        $paidLineColor=[120,60,180];
        $this->log=["BEGIN"];
        $pdf=null;
        foreach ($this->users as $idUsr => $dataUsr) {
            $review=[]; // $this->users[$idUsr]["rvw"]=[];
            $groupList=$dataUsr["idGroup"];
            $groupStr=implode(",",$groupList);
            $localIdx=-1;
            $this->log[]="LOOP USR:$idUsr GRP:$groupStr";
            foreach ($this->data as $idx => $row) {
                $fId=$row["fId"];
                $crId=$row["crId"];
                $gId=$row["gId"];
                //$this->log[]="U|I|G $idUsr|$idx|$gId";
                $tc=strtoupper($row["tc"]);
                if ($tc==="I") { $tc="F"; $tcn="Factura"; }
                else if ($tc==="E") { $tc="NC"; $tcn="Nota de Credito"; }
                else $tcn=null;
                $pdffile=str_replace("/", "\\", $row["archivoPDF"]);
                $baseData=["prv"=>$row["codProv"],"pdf"=>$pdffile,"fac"=>$row["folioF"],"cr"=>$row["folioCR"],"tc"=>$tc];
                if (!in_array($row["gId"], $groupList)) {
                    if (isset($tcn[0])) // No agregar a log los invalidos que no son F o NC
                        $this->log[]="#{$idx}): G$gId";
                    continue;
                }
                $localIdx++;
                $errPfx="{$idUsr} #{$localIdx}) $row[fechaFactura] $row[codProv] $row[folioCR] {$tc}$row[folioF]";
                $pagado="$row[pagado]";
                if ($pagado!=="1") $pagado="$row[recpago]";
                $review[$localIdx]=$baseData+["ff"=>$row["fechaFactura"],"idF"=>$fId,"idCR"=>$crId,"paid"=>$pagado];
                if (!isset($tcn[0])) {
                    $review[$localIdx]["error"]="No es Factura ni Nota de Credito";
                    $this->addError("{$errPfx}: No es Factura ni Nota de Credito");
                    $this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"TC invalido"];
                    continue;
                }
                if (!isset($pdffile[0])) {
                    $baseData["pdf"]="templates/factura.php?nombre=$row[nombreInterno]&ciclo=$row[ciclo]";
                    $review[$localIdx]["pdf"]=$baseData["pdf"];
                    $review[$localIdx]["error"]="$tcn sin PDF";
                    $this->addError("{$errPfx}: $tcn sin PDF");
                    $this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"Sin PDF"];
                    continue;
                }
                $absfile=$this->basePath.$pdffile;
                try {
                    if (!file_exists($absfile)) {
                        $v15name=substr($absfile, 0, -4)."v1_5".substr($absfile, -4);
                        if (file_exists($v15name)) {
                            $loopCount=3;
                            rename($v15name, $absfile);
                            while (!file_exists($absfile)) {
                                $loopCount--;
                                if ($loopCount<0) {
                                    $review[$localIdx]["error"]="No se alcanzó a renombrar v15";
                                    $this->addError("{$errPfx} '$pdffile': No se alcanzó a renombrar v15");
                                    $this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"V15-ERR1"];
                                    continue 2;
                                }
                                sleep(1);
                            }
                        } else {
                            $review[$localIdx]["error"]="No existe el archivo '$pdffile'";
                            $this->addError("{$errPfx} '$pdffile': No existe el archivo ");
                            $this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"V15-ERR2"];
                            continue;
                        }
                    }
                    try {
                        if (!isset($pdf)) {
                            $this->log[]="CREATE PDF";
                            $pdf = new BasePDF();
                        }
                        $pageCount = $pdf->setSourceFile($absfile);
                    } catch (setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $crex) {
                        if ($crex->getCode()==267) { // Si falla compresion hay que cambiar version de pdf
                            require_once "clases/PDF.php";
                            // crear nombre de pdf con compresion incompatible (version 1.5)
                            $v15name=substr($absfile, 0, -4)."v1_5".substr($absfile, -4);
                            $loopCount=3;
                            rename($absfile,$v15name); // asignar nombre de archivo incompatible
                            while (!file_exists($v15name)) {
                                $loopCount--;
                                if ($loopCount<0) {
                                    $review[$localIdx]["error"]="V15, no se alcanzó a renombrar";
                                    $this->addError("{$errPfx} '$pdffile': V15, no se alcanzó a renombrar");
                                    $this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"V15-ERR3"];
                                    continue 2;
                                }
                                sleep(1);
                            }
                            $errV15=BasePDF::downgradeVersion($v15name, $absfile, static::getFilePath()."pdferr.log"); // crear archivo compatible 
                            if (isset($errV15[0])) {
                                $loopCount=3;
                                rename($v15name,$absfile); // si hubo otro error, regresar nombre original
                                while (file_exists($v15name)) {
                                    $loopCount--;
                                    if ($loopCount<0) {
                                        throw new Exception("No se alcanzó a renombrar V15: $errV15");
                                    }
                                    sleep(1);
                                }
                                throw new Exception($errV15);
                            }
                            $loopCount=3; // espera máxima de 3 segundos
                            while (!file_exists($absfile)) { // esperar que concluya el proceso
                                $loopCount--;
                                if ($loopCount<0) {
                                    rename($v15name,$absfile);
                                    throw new Exception("No se encontro archivo $pdffile");
                                }
                                sleep(1);
                            }
                            $pageCount = $pdf->setSourceFile($absfile); // intentar asignar archivo nuevamente
                        } else throw $crex;
                    }
                    $pdf->SetLineWidth(1);
                    $stampData=[$row["fechaRevision"], $row["folioCR"], $row["codProv"], "$tc $row[folioF]", "$row[credito]d : $row[fechaPago]"];
                    $pdf->SetFont("Arial","B",$paidFontSize);
                    $paidStWid=$pdf->GetStringWidth($paidText);
                    $pdf->SetFont("Arial","B",16);
                    $stampWids=array_map(fn ($str) => $pdf->GetStringWidth($str), $stampData);
                    $stampWid=max($stampWids)+4;
                    $gWid=$stampWid+$sqRs;
                    $extraWid=$sinWid*$gWid;
                    $extraHgt=$sinHgt*$gWid;
                    $link=$pdf->addLink();
                    $review[$localIdx]["link"]=$link;
                    $this->log[]="LINK#{$localIdx} = $link";
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        if(!$this->validateByProvider($row["codProv"],$pageNo,$pageCount)) continue;
                        $templateId=$pdf->importPage($pageNo);
                        $size=$pdf->getTemplateSize($templateId);
                        $pgWid=$size['width'];
                        $pgHgt=$size['height'];
                        $pdf->AddPage($pgWid > $pgHgt?'L':'P', array($pgWid, $pgHgt));
                        if ($pageNo===1) $pdf->setLink($link);
                        $pdf->useTemplate($templateId);
                        if ($pageNo===1) {
                            $fxWid=$pgWid-$marginX;
                            $fxHgt=$pgHgt-$marginY;
                            $stampX=$fxWid-$extraWid;
                            $stampY=$fxHgt-$stampHgt;
                            $pdf->setAlpha(0.5);
                            $pdf->Rotate(15,$stampX,$stampY);
                            $pdf->SetTextColor(180,20,60);
                            $pdf->setDrawColor(180,20,60);
                            $pdf->setFillColor(255,248,248);
                            $pdf->RoundedRect($stampX, $stampY, $stampWid, $stampHgt, 3, 'DF');
                            $pdf->setAlpha(0.8);
                            foreach ($stampData as $stIdx => $stVal) {
                                $pdf->SetXY($stampX+1, $stampY+2+$stIdx*5);
                                $pdf->Write(5, $stVal);
                            }
                            $pdf->Rotate(0);
                        }
                        if ($pagado==="1") {
                            $paidX=($pgWid-$paidStWid)/2;
                            $paidY=($pgHgt-$paidFontHeight)/2;
                            $paidYMod=$paidLineWid/2+$paidLineSpace/2;
                            $pdf->setAlpha(0.5);
                            $pdf->SetXY($paidX,$paidY);
                            $pdf->Rotate($paidAngle,$pgWid/2,$pgHgt/2);
                            $pdf->SetTextColor(...$paidColor);
                            $pdf->SetFont("Arial","B",$paidFontSize);
                            $pdf->Write($paidFontHeight, $paidText);
                            $pdf->SetLineWidth($paidLineWid);
                            $pdf->SetDrawColor(...$paidLineColor);
                            $pdf->Line($paidX,$paidY-$paidYMod-4,$paidX+$paidStWid,$paidY-$paidYMod-4);
                            $pdf->Line($paidX,$paidY+$paidFontHeight+$paidYMod,$paidX+$paidStWid,$paidY+$paidFontHeight+$paidYMod);
                            $pdf->Rotate(0);
                        }
                    }

                } catch (Exception $e) {
                    $ear=getErrorData($e);
                    $ecl=basename($ear["class"]??"");
                    $ecd=$ear["code"]??"";
                    $efl=$ear["file"]??"";
                    $eln=$ear["line"]??"";
                    $ems=$ear["message"]??"";
                    $etr=$ear["trace"]??"";
                    $etx=$ecl."#".$ecd;
                    $iki=strpos($efl, "InvoiceCheckShare");
                    $iix=strpos($efl, "invoice");
                    if ($iki!==false) $etx.="-".substr($efl, $iki+18)."(".$eln.")";
                    else if ($iix!==false) $etx.="-".substr($efl, $iix+8)."(".$eln.")";
                    $etx.=": ".$ems;
                    $review[$localIdx]["error"]=/*"PDF NO COMPATIBLE: ".*/$etx;
                    $etx.="\n".str_replace(["\\n","C:\\Apache24\\htdocs\\invoice\\","C:\\\\Apache24\\\\htdocs\\\\invoice\\\\"], ["\n","",""], $etr);
                    $this->addError("{$errPfx} '$pdffile': $etx");
                    $this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"PDF fallido"];
                    continue;
                }
            }
            if (!isset($pdf)) {
                if ($localIdx<0) {
                    //$this->addError("{$errPfx} '$pdffile': PDF no generado");
                    //$this->errIds["$idUsr-$fId"]=$baseData+["msg"=>"PDF no generado"];
                    continue;
                } else {
                    $this->log[]="CREATE EMPTY PDF";
                    $pdf = new BasePDF();
                }
            }
            $pdf->AddPage();
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont("Arial","BU",10);
            $pdf->Cell(0,6,"RESUMEN:",0,1);
            $pdf->SetFont("Courier","",9);
            $hgt=4;
            $webPath=getBaseURL();
            //$colonTxt=": ";
            $nwln="<BR>";
            //$colonWid=$pdf->GetStringWidth($colonTxt);
            foreach ($review as $idx => $rvwData) {
                $num=$idx+1;
                $numTxt="#{$num}) ";
                $numWid=$pdf->GetStringWidth($numTxt);
                $pdf->Cell($numWid,$hgt,$numTxt);
                $href=(isset($rvwData["link"])?$rvwData["link"]:"{$webPath}$rvwData[pdf]");
                $this->log[]="#{$num} HREF=$href";
                $isPaid=$rvwData["paid"]==="1";
                $err=$rvwData["error"]??"";
                $hasErr=isset($err[0]);
                $rvwMsg="<A target='_blank' href='$href'>{$rvwData['ff']} $rvwData[prv] $rvwData[cr] {$rvwData['tc']}{$rvwData['fac']}".($isPaid?" PAGADA":"")."</A>: "; /* .($hasErr?"<B>$err</B>":"OK")."<BR>";
                if ($hasErr) $pdf->SetTextColor(128,0,0);
                $pdf->WriteHTML($rvwMsg,$hgt);
                if ($hasErr) $pdf->SetTextColor(0,0,0);
                */
                $pdf->WriteHTML($rvwMsg.($hasErr?"":"OK<BR>"),$hgt);
                if ($hasErr) {
                    $pdf->SetTextColor(128,0,0);
                    $pdf->WriteHTML("<B>$err</B><BR>",$hgt);
                    $pdf->SetTextColor(0,0,0);
                }
            }
            $newName[$idUsr]="prueba{$idUsr}{$this->fmt}.pdf"; // agregar timestamp a nombre de archivo
            $fullname=static::getFilePath().$newName[$idUsr];
            if (file_exists($fullname)) {
                rename($fullname, static::getFilePath()."pruebb{$idUsr}{$this->fmt}.pdf");
                sleep(2);
            }
            if ($pdf->saveFile(static::getFilePath().$newName[$idUsr]))
                $this->log[]="SAVE PDF ".$newName[$idUsr];
            $pdf->Close();
            $pdf=null;
        }
        return $newName;
    }
    private function validateByProvider($codProv,$pageNo,$pageCount) {
        if ($codProv==="I-026") {
            if ($pageNo===1) return true;
            if ($pageNo>=($pageCount-1)) return true;
            return false;
        }
        return true;
    }
    public static function getFilePath() {
        if (!isset(static::$filePath[21])) {
            static::$filePath=(Config::get("project","sharePath")??"..\\")."invoiceDocs\\diarios\\";
        }
        return static::$filePath;
    }
    function isEmpty() {
        return !isset($this->data[0]);
    }
    function hasErrors() {
        return isset($this->error[0]);
    }
    function getErrors() {
        return $this->error??[];
    }
    private function addError($msg,$sameLine=false) {
            $this->error[]=$msg;
    }
    public function testFiles() {
        $fileList=[];
        foreach (glob(static::getFilePath()."prueba*.pdf") as $singleFilePath) {
            $fileList[]=$singleFilePath;
        }
        return $fileList;
    }
    public static function sendReportByMail($dataValue=null,$originDesc=null) {
        $systemUserId=1038;
        $hasDataValue=isset($dataValue[0]);
        global $logObj;
        if (!isset($logObj)) {
            require_once "clases/Logs.php";
            $logObj=new Logs();
        }
        if (!$hasDataValue) {
            $todayLabel=strtotime("now");
            $successCount=0;
            $errorCount=0;
            $resultList=["result"=>"unknown"];
            // toDo: Enviar un solo correo por usuario con todos los links del día
            foreach (glob(static::getFilePath()."prueba*.pdf") as $singleFilePath) {
                $fileDate=date("Ymd",filemtime($singleFilePath));
                $idx=strrpos($singleFilePath, "prueba");
                if ($fileDate===date('Ymd',strtotime("now")) && $idx!==false) {
                    $dataValue=substr($singleFilePath, $idx+6, -4);
                    $result=static::sendReportByMail($dataValue,"list");
                    if ($result["result"]==="success") {
                        $successCount++;
                        if (!isset($resultList["message"])) $resultList["message"]="";
                        else $resultList["message"].=", ";
                        $resultList["message"].=$dataValue;
                    } else if ($result["result"]==="error") {
                        $errorCount++;
                        if (!isset($resultList["errors"])) $resultList["errors"]=[];
                        $resultList["errors"][]=$result["message"];
                    }
                }
            }
            if ($successCount>0) $resultList["result"]="success";
            else $resultList["result"]="error";
            $logObj->agrega($systemUserId, "PDFCR_NODATE", "RESULT LIST: ".json_encode($resultList));
            return $resultList;
        }
        if (isset($originDesc[0])) $originDesc=strtoupper($originDesc);
        else $originDesc="";
        $num=$logObj->cuantosHora($systemUserId, "PDFCRINI{$originDesc}");
        global $query, $lastResult;
        $qry1=$query;
        $res1=$lastResult;
        $num1=$num;
        if ($num>=static::$maxLogsPerHour) {
            $errNum=$logObj->cuantosHora($systemUserId, "PDFCRLIMIT{$originDesc}");
            $qry2=$query;
            $res2=$lastResult;
            $num2=$errNum;
            if ($errNum<1)
                $logObj->agrega($systemUserId, "PDFCRLIMIT{$originDesc}", "RESULT: Error límite por hora alcanzado '{$dataValue}'");
            doclog("Log: Limite por hora","error",["cuenta1"=>["query"=>$qry1,"result"=>$res1,"num"=>$num1],"cuenta2"=>["query"=>$qry2,"result"=>$res2,"num"=>$num2],"max"=>static::$maxLogsPerHour]);
            return ["result"=>"error","message"=>"Límite por hora alcanzado '{$dataValue}'"];
        }
        $logObj->agrega($systemUserId, "PDFCRINI{$originDesc}", "INI$dataValue");
        if(!is_file(static::getFilePath()."prueba{$dataValue}.pdf")) {
            $logObj->agrega($systemUserId, "PDFCR{$originDesc}", "RESULT: Error no existe 'prueba{$dataValue}.pdf'");
            return ["result"=>"error","message"=>"No existe el documento '$filename'"];
        }
        $usrId=substr($dataValue, 0, -6);
        $dayValue=substr($dataValue, -6);
        $subject="Acumulado de Facturas del dia $dayValue";
        $from=null; // ToDo: Obtener correo de usrId, validar que usrId tenga perfil ReporteFCRD
        if (isset($usrId[0])) {
            global $usrObj; if (!$usrObj) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
            $to=$usrObj->getData("id=$usrId",0,"email address,persona name");
            if (!isset($to[0])) $to=getMailAddressesByProfile("Reporte FCRD");
        } else $to=getMailAddressesByProfile("Reporte FCRD");
        $base = file_get_contents(getBasePath()."templates/respGralSolPago.html");
        $webPath=getBaseURL();
        $baseKeyMap = ["%ENCABEZADO%"=>"ACUMULADO DE FACTURAS DIARIAS","%RESPUESTA%"=>"<h2><a href=\"{$webPath}consultas/docs.php?daydoc={$dataValue}\">Documento del dia $dayValue</a></h2>","%BTNSTY%"=>"display:none;","%HOSTNAME%"=>$webPath];
        $mensaje=str_replace(array_keys($baseKeyMap),array_values($baseKeyMap),$base);
        if (sendMail($subject,$mensaje,$from,$to)) {
            $logObj->agrega($systemUserId, "PDFCR{$originDesc}", "RESULT: Correo Enviado $dataValue");
            return ["result"=>"success"];
        } else {
            $logObj->agrega($systemUserId, "PDFCR{$originDesc}", "RESULT: Error al enviar $dataValue");
            return ["result"=>"error","message"=>"Error en envío de correo para '$dataValue'"];
        }
    }
    public static function autoReport($genDate=null) {
        $systemUserId=1038;
        $hasGenDate=isset($genDate);
        if (!$hasGenDate)
            $genDate=strtotime("-".self::$daysBehind." days");
        $pcrObj=new PDFCR($genDate);
        $genDate=$pcrObj->getDay();
        global $logObj;
        if (!isset($logObj)) {
            require_once "clases/Logs.php";
            $logObj=new Logs();
        }
        if ($pcrObj->hasErrors()) {
            $errors=$pcrObj->getErrors();
            if (isset($errors[0]) && !isset($errors[1]) && $errors[0]==="No se generaron datos") {
                return ["result"=>"nocontent"];
            }
            doclog("PDFCR::autoReport ERROR","pruebas",["errors"=>$errors]);
            $result=["result"=>"error","message"=>"ERROR EN PREPARACION DE DATOS","errors"=>$errors];
            $logObj->agrega($systemUserId, "PDFCRAUTO", "AUTOREPORT ERRORS $genDate: ".self::err2log($errors));
            return $result;
        }
        if ($pcrObj->isEmpty()) {
            global $query;
            doclog("PDFCR::autoReport VACIO","pruebas",["query"=>$query]);
            $result=["result"=>"empty","message"=>"DIA $genDate SIN REGISTROS"];
            $logObj->agrega($systemUserId, "PDFCRAUTO", "AUTOREPORT EMPTY $genDate: SIN REGISTROS");
            return $result;
        }
        $newFileNames=$pcrObj->createFiles();
        $newFileNamesTxt=implode(", ", array_values($newFileNames));
        $message="Proceso PDFCR concluido ";
        if ($pcrObj->hasErrors()) {
            doclog("PDFCR::autoReport ERROR","pruebas",["files"=>$newFileNames,"error"=>$pcrObj->getErrors(),"idErr"=>$pcrObj->errIds,"log"=>$pcrObj->log]);
            $logObj->agrega($systemUserId, "PDFCRAUTO", "AUTOREPORT ERROR $genDate: ".substr($newFileNamesTxt,0,1480));
            if (empty($newFileNames)) return ["result"=>"empty","message"=>"ERROR EN CREACION DE ARCHIVO", "errors"=>$pcrObj->getErrors()];
            $message.="con";
        } else $message.="sin";
        $message.=" errores: $newFileNamesTxt";
        // Obtener lista de archivos generados, los que tengan más de dos semanas de haber sido creados serán eliminados.
        $fileList=glob(static::getFilePath()."*.pdf");
        $lastWeekTime=strtotime("-2 weeks");
        usort($fileList, function($a,$b) {
            $dtx=strcmp(substr($a,-10,-4), substr($b,-10,-4));
            if ($dtx==0) return strcmp(substr(basename($a), 0, -7),substr(basename($b), 0, -7));
            return $dtx;
        });
        //natsort($fileList);
        global $usrObj;
        if (!$usrObj) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
        $fileDataList=[];
        foreach (array_reverse($fileList) as $idx => $filepath) {
            $fileTime=getCorrectMTime($filepath);
            $fileName=basename($filepath);
            if ($fileTime<$lastWeekTime) {
                doclog("PDFCR::autoReport UNLINK","pruebas",["fileName"=>$fileName, "fileTime"=>$fileTime, "lastWeekTime"=>$lastWeekTime]);
                unlink($filepath);
                //unset($fileList[$idx]);
            } else if (substr($fileName, 0, 6)==="prueba") {
                //doclog("PDFCR::autoReport VALIDTIME","pruebas",["fileName"=>$fileName, "fileTime"=>$fileTime, "lastWeekTime"=>$lastWeekTime]);
                $fname=basename($filepath);
                $captx=substr($fname, 6, -4);
                $pretx=substr($captx, 0, -6);
                $usrData=$usrObj->getData("id=$pretx",0,"nombre,persona");
                if (isset($usrData[0]["nombre"][0])) {
                    $pretx=$usrData[0]["nombre"];
                    $ttltx=$usrData[0]["persona"]; //" title='".$usrData[0]["persona"]."'";
                } else $ttltx="";
                $postx=substr($captx, -6);
                $dattx=substr($postx, 0, 2)."/".substr($postx, 2, 2)."/".substr($postx, 4);

                $fileItem=["name"=>$fname,"size"=>sizeFix(filesize($filepath))];
                $fileItem["date"]=$dattx;
                $fileItem["text"]=$pretx;
                $fileItem["title"]=$ttltx;
                $fileItem["captx"]=$captx;
                $fileItem["postx"]=$postx;
                $fileDataList[]=$fileItem;
            } else {
                //doclog("PDFCR::autoReport OTHERFILE","pruebas",["fileName"=>$fileName, "fileTime"=>$fileTime, "lastWeekTime"=>$lastWeekTime]);
            }
        }
        $errors=$pcrObj->getErrors();
        $result=["result"=>"success", "message"=>$message, "errors"=>$errors];
        if (isset($errors[0])) {
            $result["errors"]=$errors;
            $errlog=": ".self::err2log($errors);
        } else $errlog="";
        $logObj->agrega($systemUserId, "PDFCRAUTO", "AUTOREPORT SUCCESS $genDate{$errlog}");
        $result["list"]=$fileDataList;
        return $result;
    }
    public static function err2log($errors) {
        $result="";
        foreach ($errors as $idx => $text) {
            $pos = strpos($text, "Exception");
            if ($pos!==false) {
                if (isset($result[0])) $result.="|";
                $dots=strpos($text, ":", $pos);
                if ($dots!==false) $result.=substr($text, 0, $dots);
                else $result.=substr($text, 0, $pos+9);
            }
        }
        if (!isset($result[0])) $result="Revisar Reporte";
        return $result;
    }
}
