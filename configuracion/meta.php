<?php

// meta_logl_eall = 0   : Configuracion php para mostrar todos los errores y warnings posibles
// meta_logl_debug = 1  : Mensajes para debuggear
// meta_logl_files = 2  : Inicio y Fin de archivos y funciones
// meta_logl_checks = 3 : Exito y fracaso de funcionalidad
// meta_logl_warns = 4  : Avisos. Errores que no afectan funcionalidad
// meta_logl_errors = 5 : Errores detectados que afectan funcionalidad

// Para PRODUCCION poner meta_log_level en 'meta_logl_errors'. //
setMetaLogLevel('meta_logl_files'); // meta_logl_files // 'meta_logl_errors'); // 
setConfigAppFilenameAndPath();

if (getMetaLogLevel() < 5) { // 0 y 1 para debuggear
  ini_set('display_errors', 'On');
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
} else {
  error_reporting(0);
  ini_set('display_errors', 'Off');
}

//$ waitImgName="icons/flying";
$waitClass="";

function setConfigAppFilenameAndPath() {
    global $_project_name;
    $temp = explode("/", $_SERVER["SCRIPT_NAME"]);
    $GLOBALS['app_config_filename'] = $temp[count($temp)-1];
    $GLOBALS['app_config_path'] = "";
    $tempFound = false;
    foreach( $temp as $val) {
        if ($tempFound) {
            if ($val!=$GLOBALS['app_config_filename'])
                $GLOBALS['app_config_path'].=$val."/";
        } else if ($val==$_project_name) {
            $tempFound=true;
        }
    }
}

function getMetaLogLevel($level=false, $lockcodetoset=false) {
    static $meta_log_level;
    static $LEVELS=false;

    if ($LEVELS===false) {
        $LEVELS = [
            "meta_logl_eall"=>0,     // Configuracion php para mostrar todos los errores y warnings posibles
            "meta_logl_debug"=>1,      // Mensajes para debuggear
            "meta_logl_files"=>2,    // Inicio y Fin de archivos y funciones
            "meta_logl_checks"=>3,   // Exito y fracaso de funcionalidad
            "meta_logl_warns"=>4,    // Avisos. Errores que no afectan funcionalidad
            "meta_logl_errors"=>5   // Errores detectados que afectan funcionalidad
        ];
    }
    if ($lockcodetoset==='meta_logl_levelcode_1pv4K2azM94') {
        $wantedLevel = getMetaLogLevel($level);
        if ($wantedLevel === false) {
            return false;
        }
        if (is_int($level)) $meta_log_level = $level;
        else $meta_log_level = $wantedLevel;
        return $meta_log_level;
    }
    if ($level===false) {
        return $meta_log_level;
    } else if (is_int($level)) {
        $keys = array_keys($LEVELS);
        $values = array_values($LEVELS);
        for ($i=0; $i<count($values); $i++) {
            if ($values[$i]==$level) {
                return $keys[$i];
            }
        }
    } else if (is_string($level) && isset($LEVELS[$level])) {
        return $LEVELS[$level];
    }
    return false;
}
function setMetaLogLevel($level=false) {
    if ($level===false) return false;
    return getMetaLogLevel($level, 'meta_logl_levelcode_1pv4K2azM94');
}

// Log de comentarios html. Se muestran en el código HTML dentro de los tags de comentario <!-- y -->
function clog1($text, $flag="0") { return clog($text, 1, $flag); } // mostrar secuencia numerica de diferentes puntos en el código
function clog2($text, $flag="0") { return clog($text, 2, $flag); } // mostrar inicio y fin de archivos y funciones
function clog3($text, $flag="0") { return clog($text, 3, $flag); } // mostrar avisos de éxito o fracaso de funcionalidad específica
function clog4($text, $flag="0") { return clog($text, 4, $flag); } // mostrar errores que no afectan funcionalidad (Excepciones sin efecto)
function clog5($text, $flag="0") { return clog($text, 5, $flag); } // mostrar errores que afectan funcionalidad (Excepciones con funcionalidad en catch)
function clog($text, $level, $flag="0") {
    if (getMetaLogLevel() <= $level) {
        $singleLine = (isset($flag[0])?$flag[0]!=="0":false);
        echo "<!-- " . $GLOBALS['app_config_filename'] . " : " . $text . ($singleLine?"":"\n//") . " -->\n";
        return 1;
    }
    return 0;
}
function slog($text, $level) {
    if (getMetaLogLevel() <= $level) echo "// " . $GLOBALS['app_config_filename'] . ": " . $text . "\n";
}
function canLog($level) {
    return (getMetaLogLevel()<=$level);
}
$GLOBALS["meta_clog1_seq"] = "0";

function clog1seq($depth = 0) {  // $depth ~= not set, -1, 0, 1
    $seq = explode(".", $GLOBALS["meta_clog1_seq"]);
    $prompt = " ";
    $seqlen = count($seq);
    if (!isset($depth) || !$depth || $depth==null || $depth==0) {
        $seq[$seqlen-1] = strval(1 + intval($seq[$seqlen-1]));
        $prompt .= "+ ";
    } else if ($depth>0) {
        $seq[$seqlen] = "1";
        $prompt .= "> ";
    } else if ($depth<0) {
        if ($seqlen > 1) {
          array_pop($seq);
          $seqlen--;
        }
        $prompt .= "< ";
    }
    $GLOBALS["meta_clog1_seq"] = implode(".",$seq);
    if (isset($_GET["html"]))
        return $prompt . $GLOBALS["meta_clog1_seq"];
    else if (!isset($depth) || $depth >= 0)
        clog1($GLOBALS["meta_clog1_seq"]);
}

function clog2ini($name = null) {
    global $metainitime;
    if (empty($name)) $metainitimekey = "empty_meta_ini_time_key_name";
    else $metainitimekey = $name;
    $metainitime[$metainitimekey] = microtime(true);
    clog2("INI ".(!empty($name)?$name.".php":""),"1");
}
function clog2end($name) {
    global $metainitime;
    if (empty($name)) $metainitimekey = "empty_meta_ini_time_key_name";
    else $metainitimekey = $name;
    $iniTime = empty($metainitime[$metainitimekey])?0:$metainitime[$metainitimekey];
    $duration = microtime(true) - $iniTime;
    clog2("END ".(!empty($name)?$name.".php":"")." duration: ".number_format($duration,4)." Seconds","1");
}
function wlog($text,$returnLog=false) {
    static $logM="";
    if (isset($text) && (is_scalar($text) || (is_object($text) && method_exists($text, '__toString')))) $logM .= $text;
    else if (is_object($text)) {
        $logM .= "OBJECT(".get_class($text).")";
    } else if (is_array($text)) {
        $logM .= "ARRAY(".json_encode($text).")";
    } else if (is_resource($text)) {
        $logM .= "RESOURCE(".get_resource_type($text).")";
    } else {
        
    }
    if ($returnLog) return $logM;
    return !empty($logM)&&!empty($text);
}
function getDocPath() {
    static $docPath=null;
    if ($docPath===null) {
        require_once "clases/Config.php";
        $sharePath="";
        $docPath=(Config::get("project","sharePath")??"..\\")."invoiceDocs/";
    }
    return $tmpPath;
}
function getTmpPath() {
    static $tmpPath="C:/InvoiceCheckShare/tmp/";
    return $tmpPath;
}
function getBasePath($newBasePath=null) {
    static $basePath="";
    if (isset($newBasePath)) {
        if ($newBasePath===true) $basePath="";
        else if (gettype($newBasePath)==="string" && isset($newBasePath[0])) $basePath=$newBasePath;
    }
    if (!isset($basePath[0])) {
        if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $basePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
        else if (!empty($_SERVER['DOCUMENT_ROOT'])) $basePath = $_SERVER['DOCUMENT_ROOT'];
        getShortPath(null);
    }
    return $basePath;
}
function getShortPath($path) {
    static $pathLength=0;
    if (is_null($path)) {
        $pathLength=0;
        return "";
    }
    $basePath=getBasePath();
    if ($pathLength==0) $pathLength=strlen($basePath);
    return substr(realpath($path), $pathLength);
}
function getBaseURL() {
    static $baseURL="";
    if (!isset($baseURL[0])) {
        $baseURL = "$_SERVER[HTTP_ORIGIN]$_SERVER[WEB_MD_PATH]";
    }
    return $baseURL;
}
function isTerminal() {
    return php_sapi_name()==='cli' || PHP_SAPI==="cli";
}
function getIP() {
    if (isTerminal()) return "terminal";
    $ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
    return $ip;
}
function getClientData() {
    if (isTerminal()) return ["PHP_SAPI"=>"cli"];
    return ["REMOTE_ADDR"=>$_SERVER["REMOTE_ADDR"],"HTTP_X_FORWARDED_FOR"=>$_SERVER["HTTP_X_FORWARDED_FOR"],"HTTP_CLIENT_IP"=>$_SERVER["HTTP_CLIENT_IP"],"HTTP_USER_AGENT"=>$_SERVER["HTTP_USER_AGENT"],"HTTP_REFERER"=>$_SERVER["HTTP_REFERER"],"HTTP_ACCEPT_LANGUAGE"=>$_SERVER["HTTP_ACCEPT_LANGUAGE"],"HTTP_ACCEPT_ENCODING"=>$_SERVER["HTTP_ACCEPT_ENCODING"],"REMOTE_PORT"=>$_SERVER["REMOTE_PORT"],"REQUEST_METHOD"=>$_SERVER["REQUEST_METHOD"],"REQUEST_TIME"=>$_SERVER["REQUEST_TIME"]];
}
function flog($text,$logkey="") { // TODO: Eliminar esta funcion. Reemplazar por doclog($text,$logkey)
    doclog($text,$logkey);
}
function errlog($text,$filebase="error",$data=null) { // TODO: Eliminar esta funcion. Reemplazar por doclog($text,"error") en todos los archivos
    doclog($text,$filebase,$data);
}
function doclog($content,$filebase=null,$data=null) {
    //echo "<!-- FUNCTION INI doclog: '$content' # '$filebase' -->\n";
    static $sharedLogs=["conn"=>"connection","pago"=>"pagos","conn1"=>"connection1","pago1"=>"pagos1","conn2"=>"connection2","pago2"=>"pagos2"];
    static $isPersonal=["action","read","action1","read1","action2","read2"];
    if (isset($GLOBALS['_now'])) {
        if (isset($_now["ymd"][5])) $dia=substr($_now["ymd"], 0, 6);
        if (isset($_now["now"][18])) $timestamp=substr($_now["now"], 11, 8);
    }
    if (!isset($dia)) {
        $dt = new DateTime();
        $dia=$dt->format("ymd");
    }
    if (!isset($timestamp)) {
        if (!$dt) $dt = new DateTime();
        $timestamp = $dt->format("His");
    }
    if (isTerminal()) {
        echo "<!-- DATA: ".json_encode($data)." -->\n";
        return;
    }
    $path=realpath(getBasePath()."LOGS").DIRECTORY_SEPARATOR;
    if (!is_dir($path)) mkdir($path);
    $path.=$dia.DIRECTORY_SEPARATOR;
    if (!is_dir($path)) mkdir($path);
    if (!is_dir($path)) {
        echo "<!-- doclog B1. CAN'T MAKE PATH: '$path' -->\n";
        return;
    } else if (!is_writable($path)) {
        echo "<!-- doclog B2. CAN'T WRITE IN PATH: '$path' -->\n";
        return;
    }
    global $hasUser, $userid, $username;
    $userfname=$username??"_logoff";
    if ($hasUser) { //$userfname=$username;
    } else if (isset($_POST["username"][0])) {
        $fullusername=$_POST["username"];
        $userfname=preg_replace("/[^\w-]/", "", $fullusername);
        if (!isset($userfname[0])) $userfname="_logoff";
    } else if (isset($data) && isset($data["username"][0])) {
        $fullusername=$data["username"];
        unset($data["username"]);
        $userfname=preg_replace("/[^\w-]/", "", $fullusername);
        if (!isset($userfname[0])) $userfname="_logoff";
    }
    if (!isset($data)) $data=[];
    else if (is_scalar($data)) $data=["data"=>$data];
    $prefix="";
    if (!isset($filebase[0])) {
        $filename=$userfname;
    } else {
        $filebase=preg_replace( "/[\W]/", "", $filebase);
        if (in_array($filebase, $isPersonal)) $filename=($userfname)."_".$filebase;
        else {
            if (isset($sharedLogs[$filebase])) $filename=$sharedLogs[$filebase];
            else $filename=$filebase;
            if ($hasUser) $prefix=" $userfname: ";
            else if (isset($fullusername[0])) $prefix=" ($fullusername): ";
            else $prefix=" noUser: ";
        }
    }
    if (is_array($content)||is_object($content)) {
        $text=implode(" ", array_values_recursive($content));
    } else $text=trim($content);
    if (isset($text[10000])) { $text=substr($text, 0, 8984)." ...too long... ".substr($text, -1000); $txtLen=10000; }
    else $txtLen=strlen($text);
    $datamsg="";
    foreach ($data as $key => $value) {
        if (isset($datamsg[0])) $datamsg.=", ";
        $datamsg.="$key:";
        $isObj=is_object($value);
        if ($isObj && $value instanceof DateTime) $datamsg.="\"".$value->format("Y-m-d H:i:s")."\"";
        else if ($isObj || is_array($value)) $datamsg.=json_encode($value);
        else $datamsg.="\"".str_replace("\"", "'", strval($value))."\"";
    }
    if (isset($datamsg[9998])) { $datamsg=substr($datamsg, 0, 8982)." ...too long... ".substr($text, -1000); }
    if (isset($datamsg[0])) {
        if (isset($text[0])) {
            if (substr($text, -1)!==".") $text.=".";
            $text.=" {".$datamsg."}";
        } else $text="{".$datamsg."}";
    }
    
    $message="[{$timestamp}]{$prefix}$text\r\n";
    $logname=$path.$filename.".log";
    $val=@file_put_contents($logname, $message, FILE_APPEND | LOCK_EX);
    if ($val===false) {
        $failText="";
        if (!file_exists($logname)) { $failText="El archivo no existe '$logname'";
        } else if (!is_writable($logname)) { $failText="Sin permiso de escritura '$logname'";
        } else { $failText="Archivo editable, error desconocido '$logname'"; }
        if ($filebase==="delayedError") {
            global $logObj;
            if (!isset($logObj)) {
                require_once "clases/Logs.php";
                $logObj=new Logs();
            }
            $logObj->agrega($userid, "DOCLOG", $failText.": ".$text);
            return;
        }
        if (!isset($data["doclogDelay"])) $data["doclogDelay"]=1;
        else $data["doclogDelay"]++;
        $data["cannotPut"]=$logname;
        //$data["fileowner"]=
        if (!isset($data["doclogMaxDelay"])) $data["doclogMaxDelay"]=3;
        if (isset($fullusername[0])) $data["username"]=$fullusername;
        $newfilebase=$filebase;
        clearstatcache();
        if ($data["doclogDelay"]>$data["doclogMaxDelay"]) {
            $data["originalFilebase"]=$filebase;
            $newfilebase="delayedError";
        } else if (in_array($filebase, $isPersonal) || isset($sharedLogs[$filebase])) {
            if (substr($filebase,-1)==="1") {
                $data["originalFilebase"]=substr($filebase,0,-1);
                $newfilebase=substr($filebase,0,-1)."2";
            } else if (substr($filebase,-1)==="2") {
                $data["originalFilebase"]=substr($filebase,0,-1);
                $newfilebase="delayedError";
            } else {
                $data["originalFilebase"]=$filebase;
                $newfilebase=$filebase."1";
            }
        } else {
            if ($content===null) $content="null";
            else if (is_bool($content)) $content=$content?"true":"false";
            else if (is_string($content)) $content="'$content'";
            if ($filebase===null) $filebase="null";
            else if (is_bool($filebase)) $filebase=$filebase?"true":"false";
            else if (is_string($filebase)) $filebase="'$filebase'";
            if ($data===null) $data="null";
            else if (is_bool($data)) $data=$data?"true":"false";
            else if (is_string($data)) $data="'$data'";
            else if (is_array($data)) $data=json_encode($data);
            echo "<!-- doclog failText='$failText', content='$content', filebase='$filebase', data='$data' -->\n";
            return; // Nada que guardar
        }
        for($i=0; $i<10; $i++) {
            $r=rand(1,2);
            if ($r==1) {
                //echo "<!-- doclog TRY AGAIN (".($i+1).") -->\n";
                doclog($content,$newfilebase,$data);
                break;
            }
            if ($i<9) sleep(1);
            else echo "<!-- doclog CANCELLED! -->n";
        }
    } else {
        $size=filesize($logname);
        //echo "<!-- doclog SENT '$logname', SIZE= '$size', RESULT: '$val' -->\n";
        // Si el log excede 100MB renombrar para incluir Hora y Minuto
        if ($size>50000000) {// 104857600 // 52428800
            rename($logname,$path.$filename.$timestamp.".log");
            // ToDo: mandar correo o sms o aviso en el portal cada que se renombra por tamaño excedido
        }
    }
}
class DocLogException extends Exception {
    public function _construct($message, $data) {
        parent::__construct($message);
        doclog($message,"error",$data);
    }
}
function isWorkingDate($from) {
    $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
    $holidayDays = ['*-12-25', '*-01-01']; # variable and fixed holidays
    if (!isset($from)) return false;
    if (!in_array($from->format('N'), $workingDays)) return false;
    if (in_array($from->format('Y-m-d'), $holidayDays)) return false;
    if (in_array($from->format('*-m-d'), $holidayDays)) return false;
    return true;
}
function isInvoicingDate($from) {
    global $hasUser, $user;
    if (isWorkingDate($from)) {
        if (!$hasUser) return false; // Sin usuario no se pueden subir cfdis
        if (!isset($user->proveedor)) return true; // Si no es proveedor puede subir cfdis
        $rfc = $user->proveedor->rfc??"";
        if (!isset($rfc[0])) return false; // debe tener rfc

        $pfNoInvDays = isset($rfc[12])?["2024-12-10","2024-12-11","2024-12-12","2024-12-13","2024-12-14","2024-12-15","2024-12-16","2024-12-17","2024-12-18","2024-12-19"]:[];
        $noInvoiceDays = [...$pfNoInvDays,"2024-12-20","2024-12-21","2024-12-22","2024-12-23","2024-12-24","2024-12-25","2024-12-26","2024-12-27","2024-12-28","2024-12-29","2024-12-30","2024-12-31"];
        //["2022-12-20","2022-12-21","2022-12-22","2022-12-23","2022-12-26","2022-12-27","2022-12-28","2022-12-29","2022-12-30"];
        if (in_array($from->format('Y-m-d'), $noInvoiceDays)) return false;
        return true;
    }
    return false;
}
function getCorrectMTime($filePath) {
    $time = filemtime($filePath);
    $isDST = (date('I', $time) == 1);
    $systemDST = (date('I') == 1);
    $adjustment = 0;
    if($isDST == false && $systemDST == true)
        $adjustment = 3600;
       else if($isDST == true && $systemDST == false)
        $adjustment = -3600;
    else
        $adjustment = 0;
    return ($time + $adjustment);
}
function getWorkingDays($from,$days) {
    $dates=[];
    for ($from=new DateTime($from);$days>0;$from->modify('+1 day'),$days--) {
        if (isWorkingDate($from)) $dates[]=$from->format('Y-m-d');
    }
    return $dates;
}
function getLastWorkingDay($from=null) {
    $d = empty($from)?new DateTime():new DateTime($from);
    //$firstDay = $d->format( 'Y-m-01');
    $yrmon=$d->format('Y-m-');
    //$daysInMonth = $d->format( 't' );
    //$workingDaysInMonth=getWorkingDays($firstDay,+$daysInMonth);
    //return end($workingDaysInMonth);
    $lastDay=+$d->format('t');
    for ($tmpVal=new DateTime($yrmon.$lastDay);!isWorkingDate($tmpVal);$tmpVal=new DateTime($yrmon.(--$lastDay)));
    return (new DateTime($yrmon.$lastDay))->format("Y-m-d");
}
function getLastInvoicingDay($from=null) {
    $d = empty($from)?new DateTime():new DateTime($from);
    $yrmon=$d->format('Y-m-');
    $lastDay=+$d->format('t');
    for ($tmpVal=new DateTime($yrmon.$lastDay);$lastDay>0 && !isInvoicingDate($tmpVal);$tmpVal=new DateTime($yrmon.(--$lastDay)));
    if ($lastDay <= 0) return false;
    return (new DateTime($yrmon.$lastDay))->format("Y-m-d");
}
function getDBDatePrefix($from) { // "2022/10"=>
    // Enero, Febrero, Marzo, Abril, Junio, Septiembre, Octubre, Noviembre, Diciembre
    static $mC="EFMAYJLGSOND"; // 
    $year = substr($from, 2, 2); // 01 23 45 => "22"
    $month = -1+substr($from, 5, 2); // 01234 56 => "10" => 9
    return $year.$mC[$month]; // 22O
} // Abril,BC,Diciembre,Enero,Febrero,aGosto,HI,Junio,K,juLio,Marzo,Noviembre,Octubre,PQR,Septiembre,TUVWX,maYo,Z
function getMonthNumber($monthname) {
    static $monthList=["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
    $key=array_search(strtolower($monthname), $monthList);
    if ($key===false) return false;
    return substr(("00".(1+$key)), -2);
}
function formatTwoFractionDigits($number) {
    static $fmt2fd=null;
    if ($fmt2fd===null) {
        $fmt2fd=new NumberFormatter('es_MX',NumberFormatter::DECIMAL);
        $fmt2fd->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $fmt2fd->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);
    }
    return $fmt2fd->format($number);
}
function formatCurrency($amount, $currency="MXN") {
    static $fmtCurr=null;
    if ($fmtCurr===null) $fmtCurr=new NumberFormatter('es_MX',NumberFormatter::CURRENCY);
    return $fmtCurr->formatCurrency($amount, $currency);
}
function errNDie($message, $data=null, $doLog=true) {
    if ($doLog===true) $logName="error";
    else if (is_string($doLog)&&isset($doLog[0])) $logName=$doLog;
    else $logName=null;
    echoJsNDie("error", $message, $data, $logName);
}
function errNFlush($message, $data=null,$restart=false) {
    echoJsNFlush("error", $message, $data, $restart);
}
function successNDie($message,$data=null,$logname=null) {
    echoJsNDie("success", $message, $data, $logname);
}
function reloadNDie($message,$data=null,$logname=null) {
    echoJsNDie("reload",$message,$data,$logname);
}
function successNFlush($message,$data=null,$restart=false) {
    echoJsNFlush("success", $message, $data, $restart);
}
function echoJsNDie($result, $message, $data=null, $logname=null) {
    echoJSDoc($result, $message, null, $data, $logname);
    die();
}
function echoJsNFlush($result,$message,$data,$restart=false) {
    static $is1stMsg=true;
    if ($is1stMsg||$restart) {
        $separator=$data["inclusiveSeparator"]??"#";
        $is1stMsg=false;
    } else $separator=null;
    echoJSDoc($result, $message, $separator, $data, $data["logname"]??null);
    ob_flush();
    flush();
}
function echoJSDoc($result, $message, $separator=null, $data=null, $logname=null) {
    $retobj=["result"=>$result,"message"=>$message];
    if (isset($logname[0])) doclog($data["logmessage"]??$message, $logname, $data);
    if (isset($data)) {
        if (is_array($data)) $retobj+=$data;
        else if (is_scalar($data)) $retobj+=["data"=>$data];
    }
    echo json_encode($retobj);
    if (isset($separator[0])) echo $separator;
}
function lockFile($filename,$wait=true) {
    $lockFile=fopen(getTmpPath().$filename,'c');
    if (!$lockFile) throw new Exception("Can't create lock file!");
    if ($wait) $lock=flock($lockFile, LOCK_EX);
    else       $lock=flock($lockFile, LOCK_EX|LOCK_NB);
    if ($lock) {
        //lockFunc($filename, $lockFile);
        fprintf($lockFile,"%s\n",getmypid());
        return $lockFile;
    } else if ($wait) {
        throw new Exception("Can't lock file!");
    }
    return false;
}
function unlockFile($filename,$lockFile) {
    //lockFunc($filename,null,true);
    fclose($lockFile);
    @unlink(getTmpPath().$filename);
}
/*function lockFunc($filename,$lockFile,$unlock=false) {
    static $fileList=[];
    if ($unlock) {
        fclose($fileList[$filename]);
        @unlink($filename);
        unset($fileList[$filename]);
    } else {
        $fileList[$filename]=$lockFile;
        fprintf($lockFile,"%s\n", getmypid());
    }
}*/
function lapse($startTime=null) {
    static $startLapse=null;
    $currentTime=microtime(true);
    if ($startTime===true) {
        $startLapse=$currentTime;
        return $currentTime;
    }
    if (isset($startTime)) {
        if (!is_numeric($startTime)) return 0;
        //$startLapse=$startTime;
        return $currentTime-$startTime;
    }
    if ($startLapse==null) $startLapse=$currentTime;
    $currentLapse=$currentTime-$startLapse;
    $startLapse=$currentTime;
    return $currentLapse;
}
function reverseDate($dtstr,$currentDelimiter,$replaceDelimiter) {
    if (isset($dtstr[10])) {
        $tmpdt=substr($dtstr,10);
        $dtstr=substr($dtstr,0,10);
    }
    return implode($replaceDelimiter, array_reverse(explode($currentDelimiter,$dtstr))).($tmpdt??"");
}
function reduccionMuestraDeCadenaLarga($cadena, $numCharXLado=4, $separador='...') {
    if (empty($separador)) $separador="";
    $longitudResultante = 2*$numCharXLado; // + strlen($separador);
    if (!isset($cadena[$longitudResultante])) return $cadena;
    $subCadenaInicial = substr($cadena, 0, $numCharXLado);
    $subCadenaFinal = substr($cadena, -$numCharXLado);
    return $subCadenaInicial.$separador.$subCadenaFinal;
}
// // // SESSION // // //
function getSessionName() {
    // ToDo: Para probar varias sesiones, mantener este nombre para usuarios sin sesion y asignar nuevo session_name y session_id cuando se valida usuario,
    if (isset($_COOKIE["TokenName"])) return $_COOKIE["TokenName"];
    return "invoiceSVRW12SessID";
}
function sessionInit() { // $sessionId=null
    if (session_status() == PHP_SESSION_NONE) {
        try {
            session_name(getSessionName());
            session_start();
        } catch (Exception $ex) { // Syntax Error no es Exception
            // ToDo cambiar Exception por Throwable
            errlog("Session Init","error",getErrorData($ex));
        }
    } /* else if (isset($_COOKIE["sessionRestart"][0])) {
        if ($_COOKIE["sessionRestart"]==="2") {
            $_COOKIE["sessionRestart"]="1";
            setcookie('sessionRestart',"1",0,'/invoice');
        } else {
            setcookie('sessionRestart','',-1,'/');
            unset($_COOKIE['sessionRestart']);
        }
        session_start();
    } */
    // alternative init with chance to restart old session by id:
    /*
    $a = session_id($sessionId); // 
    if (empty($a)) {
        session_name(getSessionName());
        session_start();
    }
    */
}
function sessionEnds() {
    if (hasSession()) {
        session_unset();
        session_destroy();
        session_write_close();
        // session_commit();
        setcookie(session_name(),'',-1,'/');
        setcookie(session_name(),'',-1,'/invoice');
    }
}
function clearTokenName() {
    if (isset($_COOKIE["TokenName"])) {
        unset($_COOKIE[$_COOKIE['TokenName']]);
        setcookie($_COOKIE["TokenName"],'',-1,'/');
        setcookie($_COOKIE["TokenName"],'',-1,'/invoice');
        unset($_COOKIE['TokenName']);
        setcookie("TokenName",'',-1,'/');
        setcookie("TokenName",'',-1,'/invoice');
    }
}
function hasSession() {
    return (session_status() == PHP_SESSION_ACTIVE);
}
// // // SESSION TESTS // // //
function isValidSessionId($session_id) {
    return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $session_id) > 0;
}
function sessionRestoreByGet() { // Simple session to work between different browsers and devices
    if(isset($_GET['session_id'])) {
        session_id($_GET['session_id']); //defining the session_id() before session_start() is the secret
        session_start();
        // Moment to use session data before below commands
        // echo "Data: " . $_SESSION['theVar'];
        // session_destroy();
        // session_commit();
    }else{ //common session statement goes here
        session_start();
        $session_id=session_id();
        // Moment to initialize data
        // $_SESSION['theVar'] = "theData"; 
        // echo "your.php?session_id=" . $session_id;
    }
}
function sane_session_name($name) {
    session_name($name);
    if(!isset($_COOKIE[$name])) {
        $value = session_create_id();
        $_COOKIE[$name] = $value;
        setcookie($name,$value,0,'/invoice');
    }
    session_id($_COOKIE[$name]);
}
function sane_session_test($name,$data=null) {
    sane_session_name($name);
    session_start();
    if (isset($data)) foreach ($data as $key => $value) {
        $_SESSION[$key] = $value;
    }
    echo "<pre>", print_r($_SESSION, 1), "</pre>";
    session_write_close();
}
function safe_session_start($name) {
    ini_set("session.use_strict_mode",true);
    ini_set("session.cookie_httponly",true);
    session_name($name);
    if(!isset($_COOKIE[$name])) {
        $value = session_create_id();
        $_COOKIE[$name] = $value;
        setcookie($name,$value,0,'/invoice');
    }
    session_id($_COOKIE[$name]);
    session_start();
    session_regenerate_id(true);
    $_COOKIE[$name] = session_id();
}
function safe_session_test($name,$data=null) {
    safe_session_start($name);
    if (isset($data)) foreach ($data as $key => $value) {
        $_SESSION[$key] = $value;
    }
    echo "<pre>", print_r($_SESSION, 1), "</pre>";
    session_write_close();
}
function hasUser() {
    global $hasUser;
    return $hasUser;
}
function getUser() {
    global $user;
    return $user;
}
function getUserName() {
    global $username;
    return $username;
}
function getUserId() {
    global $userid;
    return $userid;
}
function setUser() {
    global $_project_name, $hasUser, $user, $userid, $username;
    $user = $_SESSION["user"]??false;
    if ($user !== false) {
        $userid = $user->id??0;
        $username = $user->nombre??"_logoff";
        $userproj = $user->project_name??"";
        $hasUser = $userid>0 && isset($userproj[0]) && $userproj===$_project_name;
    } else $hasUser = false;
    if (!$hasUser) {
        $user=false;
        $userid=0;
        $username="_logoff";
    }
}
function cleanUser() {
    global $hasUser, $user, $userid, $username;
    $hasUser=false;
    $user=false;
    $userid=0;
    $username="_logoff";
}
function tienePerfil($nombrePerfil, $op="or") {
    global $user;
    if (empty($nombrePerfil)) return false;
    if (is_array($nombrePerfil)) {
        $alguno=false;
        $todos=true;
        foreach ($nombrePerfil as $key => $value) {
            if (tienePerfil($value)) $alguno=true;
            else $todos=false;
        }
        if (in_array(strtolower($op), ["and","&&","y"])) return $alguno?$todos:false;
        return $alguno;
    }
    if (isset($user->perfiles) && in_array($nombrePerfil, $user->perfiles)) return true;
    return false;
    /*
    if (isset($user->sinPerfiles) && in_array($nombrePerfil, $user->sinPerfiles)) return false;
    if ($r) {
        if (!isset($user->perfiles)) $user->perfiles=[];
        $user->perfiles[] = $nombrePerfil;
    } else {
        if (!isset($user->sinPerfiles)) $user->sinPerfiles=[];
        $user->sinPerfiles[] = $nombrePerfil;
    }
    */
}
function validaPerfil($nombrePerfil,$op="or") {
    global $user;
    return validaUPerfil($user,$nombrePerfil,$op);
}
function validaUPerfil($usr, $nombrePerfil,$op="or") {
    if (!isset($usr)||is_null($usr)||!is_object($usr)||!isset($usr->perfiles[0])) return false;
    if (is_array($nombrePerfil)) {
        if (!isset($nombrePerfil[0])) return false;
        $isOR=(!isset($op)||!is_string($op)||!in_array(strtolower($op), ["and","&&","y"]));
        $retval=$isOR?false:true;
        foreach ($nombrePerfil as $idx => $prf) {
            if ($isOR) $retval|=validaUPerfil($usr,$prf,$op);
            else $retval&=validaUPerfil($usr,$prf,$op);
        }
        return $retval;
    }
    return in_array($nombrePerfil,$usr->perfiles);
}
function consultaValida($accion,$nivel=1) {
    require_once "clases/Permisos.php";
    return validaPermiso($accion,Permisos::$CONSULTA,$nivel);
}
function modificacionValida($accion,$nivel=1) {
    require_once "clases/Permisos.php";
    return validaPermiso($accion,Permisos::$MODIFICA,$nivel);
}
function validaPermiso($accion,$tipo,$nivel=1) {
    static $prmObj = null;
    require_once "clases/Permisos.php";
    if ($nivel<1) return false;
    $esConsulta = $tipo==Permisos::$CONSULTA;
    $esModifica = $tipo==Permisos::$MODIFICA;
    $tp="";
    if ($esConsulta) $tp="CON";
    if ($esModifica) $tp="MOD";
    global $hasUser, $user;
    if($hasUser) {
        if (!isset($user->permisos)) {
            $user->permisos = array();
        }
        if (!isset($user->permisos[$accion])) {
            $user->permisos[$accion] = array();
        }
        if (!isset($user->permisos[$accion][$tp])) {
            if ($prmObj==null) {
                $prmObj = new Permisos();
            }
            if ($esConsulta) $user->permisos[$accion][$tp] = ($prmObj->consultaValida($user, $accion, 1)?"SI":"NO");
            else if ($esModifica) $user->permisos[$accion][$tp] = ($prmObj->modificacionValida($user, $accion, 1)?"SI":"NO");
        }
        if (!is_array($user->permisos[$accion][$tp])) {
            $rtv=$user->permisos[$accion][$tp];
            if ($nivel == 1) return ($rtv == "SI");
            $user->permisos[$accion][$tp]=[1=>$rtv];
        }
        if (!isset($user->permisos[$accion][$tp][$nivel])) {
            if ($esConsulta) $user->permisos[$accion][$tp][$nivel] = ($prmObj->consultaValida($user, $accion, $nivel)?"SI":"NO");
            else if ($esModifica) $user->permisos[$accion][$tp][$nivel] = ($prmObj->modificacionValida($user, $accion, $nivel)?"SI":"NO");
        }
        return ($user->permisos[$accion][$tp][$nivel] == "SI");
        
    }
    return false;
}
function getPermisos($forceReload=false) {
    global $hasUser, $user;
    $p=false;
    if ($hasUser) {
        if (!$forceReload && !isset($user->todosPermisos)) $forceReload=true;
        if (!$forceReload && isset($user->permisos) && isset(array_keys($user->permisos)[0])) foreach ($user->permisos as $k => $v) {
            if ($p===false) $p=[];
            if (isset($v["CON"])&&$v["CON"]==="SI") $p[]=$k."C";
            if (isset($v["MOD"])&&$v["MOD"]==="SI") $p[]=$k."M";
        } else {
            $s=DBi::query("SELECT a.nombre a, if(e.consulta,'CON',null) c, if(e.modificacion,'MOD',null) m FROM permisos e INNER JOIN acciones a ON e.idAccion=a.id INNER JOIN usuarios_perfiles up ON e.idPerfil=up.idPerfil WHERE up.idUsuario={$user->id} ORDER BY a.nombre;");
            if (is_object($s)) while($r = $s->fetch_assoc()) {
                if ($p===false) {
                    $p=[];
                    $user->permisos=[];
                }
                if (isset($r["c"][0])) {
                    $user->permisos[$r["a"]][$r["c"]]="SI";
                    $p[]=$r["a"]."C";
                } else $user->permisos[$r["a"]][$r["c"]]="NO";

                if (isset($r["m"][0])) {
                    $user->permisos[$r["a"]][$r["m"]]="SI";
                    $p[]=$r["a"]."M";
                } else $user->permisos[$r["a"]][$r["m"]]="NO";
            }
            $user->todosPermisos=true;
        }
    }
    return $p;
}
function getPerfiles() {
    global $hasUser, $user;
    if ($hasUser) {
        if (!isset($user->perfiles)) doclog("USUARIO SIN PERFILES","error",["userdata"=>(array)$user]);
        $userProfileList= $user->perfiles??[];
        return $userProfileList;
    }
    return [];
}
function getMailAddressesByProfile($prfName,$grpId=false) {
    global $prfObj, $upObj, $ugObj, $usrObj;
    if (!isset($prfObj)) {
        require_once "clases/Perfiles.php";
        $prfObj=new Perfiles();
    }
    $prfId=$prfObj->getIdByName($prfName);
    if ($grpId) {
        if (!isset($ugObj)) {
            require_once "clases/Usuarios_Grupo.php";
            $ugObj=new Usuarios_Grupo();
        }
        $usrIds=$ugObj->getIdUsers($prfId,$grpId);
    } else {
        if (!isset($upObj)) {
            require_once "clases/Usuarios_Perfiles.php";
            $upObj=new Usuarios_Perfiles();
        }
        $usrIds=$upObj->getIdUsers($prfId);
    }
    if (!isset($usrIds[0])) return [];
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj=new Usuarios();
    }
    $usrData=$usrObj->getData($usrObj->getQueryExpression("id",$usrIds,"WHERE")."email is not null group by email",0,"email address,persona name");
    return $usrData;
}
// // // // // // // // // // // // UTILERIAS // // // // // // // // // // // //
function b2s($val) {
    return $val?"true":"false";
}
function isSequentialArray($array) {
    if (!is_array($array)) return false;
    if ($array === []) return true;
    return array_keys($array) === range(0, count($array) - 1);
}
function isAssociativeArray($array) {
    if (!is_array($array)) return false;
    return !isSequentialArray($array);
}
function filterArray($arr, $ignoreKeys) {
    return array_diff_key($arr, array_flip($ignoreKeys));
}
function count_terminals($array) {
  return is_array($array)
           ? array_reduce($array, function($carry, $item) {return $carry + count_terminals($item);}, 0)
           : 1;
}
function getCIKeyVal($array, $key) { // obtain first value found for case insensitive key
    $keyLower = strtolower($key);
    foreach ($array as $existingKey => $value)
        if (strtolower($existingKey) === $keyLower) return $value;
    return false;
}
function recursiveKSort(&$array, $flags = SORT_REGULAR) {
    if (!is_array($array) || empty($array)) return false;
    if (isAssociativeArray($array)) ksort($array, $flags);
    else sort($array, $flags);
    foreach ($array as &$arr) {
        recursiveKSort($arr, $flags);
    }
    return true;
}
function array_values_recursive($array) {
    $flat = array();
    if (is_object($array)) $array=get_object_vars($array);
    foreach($array as $value) {
        if (is_array($value)||is_object($value)) {
            $flat = array_merge($flat,array_values_recursive($value));
        } else {
            $flat[]=$value;
        }
    }
    return $flat;
}
function arr2str($arr, $prompt=" * ", $end="", $depth=1, $extra=-1) { // extra (mixed) ["maxdepth"=>-1, "maxvallen"=>-1, "separator"=>""]
    $result = "";
    $lprompt = str_repeat($prompt, $depth);
    $maxdepth=-1;
    $maxvallen=-1;
    $separator=" + ";
    $showvaluetype=false;
    $showvaluelength=false;
    $ismultiline=true;
    if (is_numeric($extra)) $maxdepth=$extra;
    else if (is_array($extra)) {
        if (isset($extra["maxdepth"]) && is_numeric($extra["maxdepth"])) $maxdepth=$extra["maxdepth"];
        if (isset($extra["maxvallen"]) && is_numeric($extra["maxvallen"])) $maxvallen=$extra["maxvallen"];
        if (isset($extra["separator"])) $separator="$extra[separator]";
        if (isset($extra["showvaluetype"])) $showvaluetype=(bool)$extra["showvaluetype"];
        if (isset($extra["showvaluelength"])) $showvaluelength=(bool)$extra["showvaluelength"];
        if (isset($extra["ismultiline"]) && !(bool)$extra["ismultiline"]) $ismultiline=false;
    }
    $showany1=$showvaluetype||$showvaluelength;
    $showboth=$showvaluetype&&$showvaluelength;
    if ($arr===null 
       || ( !is_array($arr)
          && !($arr instanceof Traversable)
          && !($arr instanceof Iterator)
          && !($arr instanceof IteratorAggregate))) return $result;
    $issequentialarray=isSequentialArray($arr);
    if ($issequentialarray) {
        if ($maxvallen==0) $maxvallen=-1;
    } else if ($maxvallen==0) {
        $extra["ismultiline"]=false;
        $ismultiline=false;
    }
    global $username;
    $isSuperUser = ($username==="admin"||$username==="sistemas");
    if ($isSuperUser) doclog("arr2str","test",["lprompt"=>$lprompt,"maxdepth"=>$maxdepth,"maxvallen"=>$maxvallen,"separator"=>$separator,"showvaluetype"=>($showvaluetype?"true":"false"),"showvaluelength"=>($showvaluelength?"true":"false"),"ismultiline"=>($ismultiline?"true":"false"),"showany1"=>($showany1?"true":"false"),"showboth"=>($showboth?"true":"false"),"issequentialarray"=>($issequentialarray?"true":"false"),"keys"=>array_keys($arr)]);
    foreach ($arr as $key => $value) {
        $reslen=strlen($result);
        $valtype=gettype($value);
        $showinfo=($showany1?"(".($showvaluetype?$valtype:"").($showboth?"|":""):"");
        $vallen=null; $decoded=null;
        if (is_null($value)) { if($maxvallen>=0 && $maxvallen<4) $value="-"; else $value="null"; $vallen=0; }
        else if ($valtype==="object") $value=(array)$value;
        else if ($valtype==="string") {
            $decoded=json_decode($value);
            if (is_null($decoded)) {}
            else if (is_object($decoded)) { $value=(array)$decoded; $valtype="decodedObj"; }
            else if (is_array($decoded)) { $value=$decoded; $valtype="decodedArr"; }
        }
        if ($valtype==="array"||$valtype==="object"||$valtype==="decodedObj"||$valtype==="decodedArr") {
            $arrlen=count($value);
            if ($showany1) $showinfo.=($showvaluelength?"$arrlen":"").")";
            if ($ismultiline || $depth==1) $result.=$lprompt;
            if (!$issequentialarray) $result.=$key;
            $result.=$showinfo; //PHP_EOL;
            $issequentialvalue=isSequentialArray($value);
            if ($maxdepth<0 || $maxdepth>$depth) {
                if ($arrlen==0) { if ($ismultiline) $result.=($issequentialarray?"":"=").($issequentialvalue?"[]":"{}")."$end\n"; }
                else {
                    if ($ismultiline) $result.=($issequentialarray?"":"=")."\n"; else $result.=($issequentialvalue?"[":"{");
                    //if ($issequentialvalue) $extra["ismultiline"]=false;
                    if (!$issequentialvalue&&$ismultiline) {
                        if (empty($extra)) $subextra=["ismultiline"=>false];
                        else if(is_array($extra)) $subextra=$extra+["ismultiline"=>false];
                        else if (is_numeric($extra)) $subextra=["maxdepth"=>$extra,"ismultiline"=>false];
                        else $subextra=["ismultiline"=>false,"extra"=>$extra];
                    } else $subextra=$extra;
                    if ($isSuperUser) doclog("arr2str subloop","test",["key"=>$key,"chunk"=>str_replace('\n','\\n',substr($result, $reslen)),"issequentialvalue"=>($issequentialvalue?"true":"false")]);
                    $reslen=strlen($result);
                    $result.=arr2str($value, $prompt, $end, $depth+1, $subextra);
                    if ($ismultiline) $result.=($issequentialvalue?"$end\n":""); else $result.=($issequentialvalue?"]":"}");
                }
            } else if ($ismultiline) $result.="$end\n";
        } else {
            if ($valtype==="boolean") { $value=($value?($maxvallen>=0&&$maxvallen<4?"1":"TRUE"):($maxvallen>=0&&$maxvallen<5?"0":"FALSE")); $vallen=1; }
            else if ($valtype!=="string") $value="$value";
            if (is_null($vallen)) $vallen=strlen($value);
            if ($showany1) $showinfo.=($showvaluelength?"$vallen":"").")";
            if (!$ismultiline) {
                if ($key===array_key_first($arr)) {
                    if ($depth==1) $result.=$lprompt;
                    if (!$issequentialarray) $result.=$key;
                    $result.=$showinfo;
                    if ($key===array_key_last($arr) && $depth==1) $result.=$end."\n";
                } else {
                    if ($key===array_key_last($arr) && $depth==1) $result.=$end."\n";
                    $result.=$separator.$key;
                }
                continue;
            }
            $result .= $lprompt.$key.$showinfo.($issequentialarray?"":"=");
            if ($maxvallen>0&&$vallen>$maxvallen)
                $value=trim(substr($value, 0, $maxvallen))."...";
            $result .= $value.$end."\n"; //PHP_EOL;
        }
        if ($isSuperUser) doclog("arr2str loopEnd","test",["key"=>$key, "chunk"=>str_replace('\n','\\n',substr($result, $reslen))]);
    }
    return $result;
}
function arr2List($arr, $tipoLista="UL", $exceptList=[]) { // $tipoLista="OL"
    if (!is_array($arr)) return "";
//    if ($tipoLista!="OL") $tipoLista="UL";
    if (is_array($tipoLista)) {
        $result = "<".implode(" ", $tipoLista).">";
        $tipoLista=$tipoLista[0];
    } else $result = "<$tipoLista>\n";
    $isAssoc = isAssociativeArray($arr);
    foreach ($arr as $key => $value) {
        if ($isAssoc && in_array($key, $exceptList)) continue;
        $result .= "<LI";
        if (is_object($value)) {
//            $result .= " isObject='true'";
            foreach(get_object_vars($value) as $prpnam=>$prpval) {
                if (!in_array($prpnam, ["value","classList"]))
                    $result .= " $prpnam='$prpval'";
            }
            if (isset($value->value)) {
//                $result .= " hasValue='true'";
                if (isset($value->classList)) {
//                    $result .= " hasClassList='true'";
                    if (is_array($value->classList))
                        $result .= " class='".implode(" ", $value->classList)."'";
                    else if (is_string($value->classList))
                        $result .= " class='".$value->classList."'";
                }
                $value = $value->value;
            }
        } else {
            $result .= " class='bodycolor'";
        }
        $result .= ">";
        $hasKey=($isAssoc && substr($key,0,4)!=="null" && substr($key,0,5)!=="false" && substr($key,0,4)!=="zero");
        if ($isAssoc && $hasKey) $result .= "<B>".$key."</B>";
        if (empty($value)) {
        } else if (is_array($value)) {
            if ($arr !== $value)
                $result .= "\n".arr2List($value, $tipoLista, $exceptList);
            else {
                if ($isAssoc && $hasKey) $result .= " = ";
                $result .= "<I>LOOP</I>";
            }
        } else if (is_string($value)) {
            if ($isAssoc && $hasKey) $result .= " = ";
            $result .= "".$value."";
        } else if (is_object($value)) {
            if ($isAssoc && $hasKey) $result .= " = ";
            $result .= "<I>".json_encode($value)."</I>";
        } else {
            if ($isAssoc && $hasKey) $result .= " = ";
            $result .= "{".$value."}";
        }
        $result .= "</LI>\n";
    }
    $result .= "</$tipoLista>\n";
    return $result;
}
function arrecho($arr, $end="") {
    echo arr2str($arr, "  ", $end);
}
function arrecho_depth($arr, $prompt=" * ", $end="") {
    echo arr2str($arr, $prompt, $end);
}
function arrechoLite($arr, $prompt=" * ", $end="") {
  foreach ($arr as $key => $value) {
    echo $prompt.$key."=";
    if (is_array($value)) {
        echo $end."\n";
        if ($arr !== $value)
            arrechoLite($value, $prompt.$prompt, $end);
        else echo $prompt.$prompt."LOOP".$end;
    } else if (is_string($value)) {
        echo $value.$end."\n";
    } else if (is_object($value)) {
        echo json_encode($value).$end."\n";
    } else
        echo $value.$end."\n";
  }
}
function arrechoLiteUL($arr) {
    echo arr2List($arr);
}
function getFixedFileArray($files) {
    $fixedFiles = [];
    if (isset($files) && isset($files["name"])) {
        //$fileCount = count($files["name"]);
        $fileKeys = array_keys($files);
        $fileNameKeys = array_keys($files["name"]);
        //for ($i=0; $i<$fileCount; $i++) {
        foreach($fileNameKeys as $nameKey) {
            foreach ($fileKeys as $key) {
                $fixedFiles[$nameKey][$key] = $files[$key][$nameKey];
            }
        }
    }
    return $fixedFiles;
}
function fileCodeToMessage($fileCode,$extra=null) { // ,$lang="mx"
// $extra[message]: modificar mensajes o agregar nuevos mensajes o idiomas
// $extra[filename]: incluir filename en mensaje de error.
    static $uploadErrMsg = [
        /* 0=>UPLOAD_ERR_OK */["mx"=>"Carga de archivo#FILENAME# exitosa", "en"=>"There is no error, the file#FILENAME# uploaded with success"],
        /* 1=>UPLOAD_ERR_INI_SIZE */["mx"=>"El tamaño del archivo#FILENAME# excede el máximo permitido por el servidor", "en"=>"The uploaded file#FILENAME# exceeds the upload_max_filesize directive in php.ini"],
        /* 2=>UPLOAD_ERR_FORM_SIZE */["mx"=>"El tamaño del archivo#FILENAME# excede el máximo permitido por la forma HTML", "en"=>"The uploaded file#FILENAME# exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"],
        /* 3=>UPLOAD_ERR_PARTIAL */["mx"=>"Carga de archivo#FILENAME# incompleta", "en"=>"The uploaded file#FILENAME# was only partially uploaded"],
        /* 4=>UPLOAD_ERR_NO_FILE */["mx"=>"No hay archivo#FILENAME# a cargar", "en"=>"No file#FILENAME# was uploaded"],
        /* 5=> */null,
        /* 6=>UPLOAD_ERR_NO_TMP_DIR */["mx"=>"No existe el folder temporal", "en"=>"Missing a temporary folder"],
        /* 7=>UPLOAD_ERR_CANT_WRITE */["mx"=>"Falló la escritura del archivo#FILENAME#", "en"=>"Failed to write file#FILENAME# to disk"],
        /* 8=>UPLOAD_ERR_EXTENSION */["mx"=>"La carga de archivo#FILENAME# fue interrumpida por una extensión PHP", "en"=>"A PHP extension stopped the file#FILENAME# upload"]
    ];
    if ($fileCode===null) return $uploadErrMsg; // mostrar todos los mensajes
    if (!ctype_digit(strval($fileCode))||$fileCode<0) return null;
    if (isset($extra["lang"][0])) $lang=$extra["lang"]; else $lang="mx";
    if (isset($extra["message"])) {
        if (is_array($fileCode)&&is_array($extra["message"])) foreach ($fileCode as $singleCode) {
            if (isset($extra["message"][$singleCode])) {
                if (!isset($uploadErrMsg[$singleCode])) $uploadErrMsg[$singleCode]=[$lang => $extra["message"][$singleCode]];
                else $uploadErrMsg[$singleCode][$lang]=$extra["message"][$singleCode];
            }
        } else if (!isset($uploadErrMsg[$fileCode])) $uploadErrMsg[$fileCode]=[$lang => $extra["message"]];
        else $uploadErrMsg[$fileCode][$lang]=$extra["message"];
    } else if (!isset($uploadErrMsg[$fileCode][$lang][0])) return null;
    $returnMessage=$uploadErrMsg[$fileCode][$lang];
    if ($fileCode==1) { // UPLOAD_ERR_INI_SIZE
        $maxFileSize=ini_get("upload_max_filesize");
        if ($lang==="mx") $returnMessage.=" de ";
        else if ($lang==="en") $returnMessage.=" of ";
        else $returnMessage.=" : ";
        $returnMessage.=$maxFileSize;
    }
    $hasVal=false;
    if (function_exists("str_contains")) $hasVal=str_contains($returnMessage, "#FILENAME#");
    else $hasVal = (strpos($returnMessage, "#FILENAME#") !== false);
    if ($hasVal) {
        $filename=trim($extra["filename"]??"");
        if (isset($filename[0])) $filename=" $filename";
        $returnMessage=str_replace("#FILENAME#", $filename, $returnMessage);
    }
    return $returnMessage; //$uploadErrMsg[$fileCode][$lang]; // .$suffixMsg;
}
function isValidFile($file,&$invalidFileMsg,$extra)
// returns true if it is valid, false if not
// $file : upload file format [name:"",size:#,tmp_name:"",type:"",error:#]
// $invalidFileMsg: by reference argument, will contain error description if returned value is false
// extra[type]: include if there's a required type/mime-type, compared to $file[type]. Can be string or array.
// (toDo) extra[lang]: some day lang might be supported
{
    if (empty($file)) {
        $invalidFileMsg="No se especificó el archivo a validar";
        return false;
    }
    if (!isset($file["name"][0])) {
        $invalidFileMsg="Formato de archivo incompleto, falta nombre de archivo";
        return false;
    }
    /* if (!isset($file["tmp_name"][0])) {
        $invalidFileMsg="Formato de archivo incompleto, falta nombre temporal de archivo";
        return false;
    } */ // Se ignora para permitir validar archivos que no se están cargando... posiblemente habría que contemplando agregando la ruta absoluta del archivo, pero hay que prever confusiones, de todas formas la ruta absoluta debe conocerse fuera del método y realmente no tiene porque validarse aqui
    if (empty($file["size"])) {
        if (isset($file["size"])&&$file["size"]===0) $invalidFileMsg="El archivo no tiene información, está vacío";
        else $invalidFileMsg="No se especificó el tamaño del archivo";
        return false;
    }
    if (!empty($file["type"])&&isset($extra["type"])) {
        if (is_array($extra["type"])) {
            if (!in_array($file["type"], $extra["type"])) {
                $invalidFileMsg="El formato de archivo '$file[type]' no es válido";
                if (!empty($extra["showValidType"])) {
                    $validTypes=$extra["showValidType"];
                    $countTypes=count($validTypes);
                    $lastType=null;
                    while(is_null($lastType)&&$countTypes>0) {
                        $lastType=array_pop($validTypes);
                        $countTypes--;
                    }
                    if (!is_null($lastType)) {
                        $invalidFileMsg.=", se espera '";
                        if ($countTypes>0) $invalidFileMsg.=implode("', '", $validTypes)."' o '";
                        $invalidFileMsg.=$lastType."'";
                    }
                }
                return false;
            }
        } else if (is_string($extra["type"])) {
            if ($file["type"]!==$extra["type"]) {
                $invalidFileMsg="El formato de archivo '$file[type]' es inválido";
                if (!empty($extra["showValidType"])) $invalidFileMsg.=", se espera '$extra[type]'";
                return false;
            }
        } else {
            $invalidFileMsg="El tipo de archivo no tiene formato valido: ".json_encode($extra["type"]);
        }
    }
    if (!empty($file["error"]) && $file["error"]!==UPLOAD_ERR_OK) {
        $invalidFileMsg=fileCodeToMessage($file["error"],["filename"=>$file["name"]]);
        return false;
    }
    return true;
}
function checkFileUploadedName ($filename) {
    return (bool) (preg_match("`^[-0-9A-Z_\.]+$`i",$filename) ? true : false);
}
function checkFileUploadedLength ($filename) {
    return (bool) ((mb_strlen($filename,"UTF-8") > 225) ? false : true);
}
function fixProperty($originalArray,$probeArray,$depth=0) {
    // $probeArray = [ [$key, $originalValue, $fixedValue or null, $mergeKey, $mergeValue], ... ];
    // $key: llave identificada para realizar cambio
    // $originalValue: deben coincidir llave y valor original para identificarlos para corrección
    // $fixedValue: Reemplazar valor original con este, si no es null
    // $mergeKey: llave de arreglo a integrar con valores nuevos, posterior a $key, pero en el mismo nivel
    // $mergeValue: arreglo con valores a agregar al original
    $newArray=[];
    $mergedKey=null;
    $clogLevel=getMetaLogLevel("meta_logl_debug");
    $pdn=13+(2*$depth); $SPL=STR_PAD_LEFT;
    $canLog = (getMetaLogLevel() <= $clogLevel);
    $canLog && ($depth==0) && print "<!-- " . $GLOBALS['app_config_filename'] . " : \n";
    $canLog && print "//".str_pad("FIXPROPERTY ", $pdn, " ", $SPL); //.json_encode(array_keys($originalArray))."\n";
    //$pfx="//".str_pad("", $pdn, " ", $SPL);
    $pfx="";
    foreach ($originalArray as $key => $value) {
        $match=false;
        $canLog && print $pfx."[$key] ";
        if (isset($mergedKey) && $key===$mergedKey) {
            $canLog && print "Is MergedKey! ";
            $mergedKey=null;
            if (isset($newArray[$key])) $value=$newArray[$key];
            $match=true;
        }
        $inLoop2=false;
        foreach ($probeArray as $idx => $data) {
            if ($key===$data[0]&&$value===$data[1]) {
                $inLoop2=true;
                $canLog && print "Key Found! ";
                if (isset($data[2])) {
                    $newArray[$key]=$data[2];
                    $match=true;
                    $canLog && print "Value ".(is_array($value)?json_encode($value):$value)." => ".(is_array($data[2])?json_encode($data[2]):$data[2])." ";
                    //continue 2;
                }
                if (isset($data[3]) && isset($data[4])) {
                    $origData3=$originalArray[$data[3]]??null;
                    if (!isset($data[2]) && !isset($newArray[$key])) {
                        $newArray[$key]=$value;
                        $canLog && print "Assigned Original=".json_encode($value)." ";
                    }
                    if (isset($origData3)) {
                        $mergedKey=$data[3];
                        $canLog && print "MergedKey='".$data[3]."' ";
                        if (is_array($origData3)) {
                            $newArray[$data[3]]=array_merge($origData3,(array)$data[4]);
                        } else $newArray[$data[3]]=$data[4];
                    } else $newArray[$data[3]]=$data[4];
                    $canLog && print "Merge Data: \n//".str_pad("",$pdn)."Data    =".json_encode($data)."\n//".str_pad("",$pdn)."Original=".json_encode($origData3)."\n//".str_pad("",$pdn)."Addition=".json_encode($data[4])."\n//".str_pad("",$pdn)."Result  =".json_encode($newArray[$data[3]]);
                    $match=true;
                    //continue;
                }
                $canLog && !$match && print "NO MATCH! ";
                $canLog && print "\n"; $pfx="//".str_pad("F           ", $pdn, " ", $SPL);
            }
        }
        if (!$inLoop2) {
            if (is_array($value)) {
                $canLog && print "\n";
                $newArray[$key]=fixProperty($value,$probeArray,$depth+1);
                $pfx="//".str_pad("F           ", $pdn, " ", $SPL);
                continue;
            }
            $canLog && print "\n"; $pfx="//".str_pad("F           ", $pdn, " ", $SPL);
        }
        if (!$match) $newArray[$key]=$value;
    }
    $canLog && ($depth==0) && print "// -->\n";
    return $newArray;
}
// Compara si dos archivos son identicos
function compareFiles($file_a, $file_b) {
    $fsize = filesize($file_a);
    if ($fsize == filesize($file_b)) {
        $txt_a = file_get_contents($file_a);
        $txt_b = file_get_contents($file_b);
        if ($txt_a === $txt_b) return true;
    }
    return false;
}
function sizeFix($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = (int)floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
function permFix($perms) {
    switch ($perms & 0xF000) {
        case 0xC000: $info='s'; break; // socket
        case 0xA000: $info='l'; break; // symbolic link
        case 0x8000: $info='r'; break; // regular
        case 0x6000: $info='b'; break; // block special
        case 0x4000: $info='d'; break; // directory
        case 0x2000: $info='c'; break; // character special
        case 0x1000: $info='p'; break; // FIFO pipe
        default: $info = 'u'; // unknown
    }
    // Owner
    $info.=(($perms&0x0100)?'r':'-');
    $info.=(($perms&0x0080)?'w':'-');
    $info.=(($perms&0x0040)?(($perms&0x0800)?'s':'x'):(($perms&0x0800)?'S':'-'));
    // Group
    $info.=(($perms&0x0020)?'r':'-');
    $info.=(($perms&0x0010)?'w':'-');
    $info.=(($perms&0x0008)?(($perms&0x0400)?'s':'x'):(($perms&0x0400)?'S':'-'));
    // World
    $info.=(($perms&0x0004)?'r':'-');
    $info.=(($perms&0x0002)?'w':'-');
    $info.=(($perms&0x0001)?(($perms&0x0200)?'t':'x'):(($perms&0x0200)?'T':'-'));
    return $info;
}
function getTraceData($limit) {
    $arr=debug_backtrace(0,$limit+1);
    array_shift($arr);
    $retVal=[];
    $basePath=getBasePath();
    $baseLen=strlen($basePath);
    foreach ($arr as $idx => $item) {

        $file=str_replace("\\", "/", $item["file"]??"");
        if (strpos($file, $basePath)===0) $file=substr($file, $baseLen);
        $line=$item["line"];
        $function=$item["function"]??"";
        $class=$item["class"]??"";
        $type=$item["type"]??"";
        $arg=$item["args"]??[];
        $retVal[$idx]="$file. ($line): {$class}{$type}{$function}() ".json_encode($arg);
    }
    return $retVal;
}
function getTraceMessage($options=2,$newline="<BR>") {
    $arr=debug_backtrace($options);
    $msg="";
    foreach ($arr as $idx => $data) {
        if ($idx==0) continue;
        $msg.=$newline.($idx).": ";
        if (isset($data["file"])) {
            $file=$data["file"];
            if(strpos($file, "C:\\Apache24\\htdocs\\invoice\\")===0) $file=substr($file, 27);
            $msg.=$file."|";
        }
        if (isset($data["class"])) {
            $msg.=$data["class"];
        }
        if (isset($data["type"])) {
            $msg.=$data["type"];
        } else if (isset($data["class"])&&isset($data["function"])) {
            $msg.="#";
        }
        if (isset($data["function"])) {
            $msg.=$data["function"];
        }
        if (isset($data["line"])) {
            $msg.=":".$data["line"];
        }
    }
    return $msg;
}
function debug_to_console($data, $inserted=false) {
    if(is_array($data) || is_object($data)) $output = json_encode($data);
    else $output = "".$data;
    $output = str_replace ( ["\"", "'", "\n"], ["\\\"", "\\'", "\\n"], $output );

    //if ($inserted) echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"console.log('Debug Objects: {$output}'); this.parentNode.removeChild(this);\">";
    //else echo "<script>console.log(\"Debug Objects: $output\");</script>";
}
function replaceBetween($str,$startTag,$endTag,$replaceStr,$isIncludeTags=false,$isRecursive=false,$offset=0) {
    if (isset($startTag[0])) {
        $pos=strpos($str,$startTag,$offset);
        if ($pos===false) return $str;
    } else {
        $pos=0;
        $startTag="";
    }
    $start=$pos+($isIncludeTags?0:strlen($startTag));
    if (isset($endTag[0])) {
        $pos=strpos($str,$endTag,$start+(($isIncludeTags&&$startTag===$endTag)?strlen($startTag):0));
        if ($pos===false) {
            $pos=strlen($str);
            $endTag="";
        }
    } else if (!isset($startTag)) return $replaceStr;
    else {
        $pos=strlen($str);
        $endTag="";
    }
    $end=$pos+($isIncludeTags?strlen($endTag):0);
    $nextOffset=$end+($isIncludeTags?0:strlen($endTag));
    if ($isRecursive && isset($str[$nextOffset])) {
        $aux=replaceBetween($str,$startTag,$endTag,$replaceStr,$isIncludeTags,$isRecursive,$nextOffset);
        if ($aux!==$str) $str=$aux;
    }
    $retVal=substr_replace($str, $replaceStr, $start, $end-$start);
    return $retVal;
}
function getDomain($mail) {
    global $mail_servidor;
    $regexp = '/@([^\\.]+)/';
    preg_match($regexp, $mail, $match);
    doclog("GET DOMAIN","mail",["mail"=>$mail, "match"=>$match]);
    if (!isset($match[1])) return "default";
    $domKey=$match[1];
    if ($domKey==="pmnjyl") $domKey="jyl";
    else if ($domKey==="rgaarquitectos") $domKey="rga";
    if (!isset($mail_servidor[$domKey])) return "default";
    return $domKey;
}
function getDomainMap($addressList) {
    $map=[];
    if (isset($addressList)) foreach ($addressList as $idx => $val) {
        $domKey=getDomain($val["address"]??$val);
        if ($domKey==="pmnjyl") $domKey="jyl";
        else if ($domKey==="rgaarquitectos") $domKey="rga";
        if (!isset($map[$domKey])) $map[$domKey]=[];
        $map[$domKey][]=$val;
    }
    return $map;
}
function getMailHourCount($domain) {
    global $infObj;
    static $hours=5;
    if (!isset($infObj)) {
        require_once "clases/InfoLocal.php";
        $infObj=new InfoLocal();
    }
    $dt = new DateTime();
    $curr=$dt->format("ymdH");
    $hr=substr($curr,-2);
    doclog("check current time","mail",["domain"=>$domain,"ymdH"=>$curr]);
    $dt->modify("-$hours hours");
    $lastFull=$dt->format("Y-m-d H:i:s");
    $val = $infObj->getVal("mail_hour_key");
    doclog("modify -$hours hours","mail",["domain"=>$domain,"ymdH"=>$dt->format("ymdH"),"mail_hour_key"=>$val]);
    if ($val<$curr) {
        $infObj->delVal("mail_hour_count%",$lastFull);
        $infObj->setVal("mail_hour_key",$curr);
    }
    
    return $infObj->getVal("mail_hour_count_$domain{$hr}")??0;
}
function addMailHourCount($domain,$num=1) {
    global $infObj;
    if (!isset($infObj)) {
        require_once "clases/InfoLocal.php";
        $infObj=new InfoLocal();
    }
    $dt = new DateTime();
    $hr=$dt->format("H");
    $infObj->incVal("mail_hour_count_$domain{$hr}",$num);
    doclog("incVal","mail",["domain"=>$domain,"hr"=>$hr,"num"=>$num]);
}
function sendMail($asunto,$mensaje,$from,$to,$cc=null,$bcc=null,$data=null) {
    if (isset($to["address"][0])) $to=[$to];
    if (!isset($to[0]["address"][0])) {
        errlog("Error en correo: No hay destinatario","mail",["asunto"=>$asunto, "mensaje"=>$mensaje, "from"=>$from, "to"=>$to]);
        return false;
    }
    $mensaje=replaceBetween($mensaje,"<!-- NOMAIL INI -->","<!-- NOMAIL END -->","",true,true);
    require_once "clases/Correo.php";
    $mail=new Correo();
    if (!isset($data)) $data=[];
    if (isset($data["domain"])) {
        $mail->settingsByKey(true, $data["domain"]);
        if (!isset($data["mailSetMode"])) $data["mailSetMode"]="specified data";
        if (!isset($data["mailCount"])) $data["mailCount"]=getMailHourCount($data["domain"]);
        $addresses=array_column($to, "address");
        if (isset($cc)) $addresses=array_merge($addresses, array_column($cc, "address"));
        if (isset($bcc)) $addresses=array_merge($addresses, array_column($bcc, "address"));
        if (!isset($data["mailToList"])) $data["mailToList"]=implode(", ", $addresses);
    } else {
        $toMap=getDomainMap($to);
        $ccMap=isset($cc)?getDomainMap($cc):[];
        $bccMap=isset($bcc)?getDomainMap($bcc):[];

        $mapKeys=array_keys($toMap);
        $mapLen=count($mapKeys)-1;
        for ($i=0; $i<$mapLen; $i++) {
            $mk=$mapKeys[$i];
            sendMail($asunto, $mensaje, $from, $toMap[$mk], $ccMap[$mk]??null, $bccMap[$mk]??null, array_merge($data,["domain"=>$mk,"resetIndex"=>$i,"mailSetMode"=>"calc by toAddress"]));
            unset($toMap[$mk], $ccMap[$mk], $bccMap[$mk]);
        }
        $data["domain"]=$mapKeys[$mapLen];
        $data["resetIndex"]=$mapLen;
        $data["mailSetMode"]="calc last by toAddress";
        $data["mailCount"]=getMailHourCount($data["domain"]);
        $to=$toMap[$mapKeys[$mapLen]];
        $addresses=array_column($to, "address");
        $cc=[]; foreach ($ccMap as $v) $cc=array_merge($cc, $v);
        if (isset($cc)) $addresses=array_merge($addresses, array_column($cc, "address"));
        $bcc=[]; foreach ($bccMap as $v) $bcc=array_merge($bcc, $v);
        if (isset($bcc)) $addresses=array_merge($addresses, array_column($bcc, "address"));
        $data["mailToList"]=implode(", ", $addresses);
    }
    $mail->restart();
    $data["asunto"]=$asunto; $data["subject"]=$asunto;
    $data["mensaje"]=$mensaje; $data["body"]=$mensaje;
    $toString="";
    $data["to"]=$to; $addressCount=0;
    foreach ($to as $idx => $toItem) {
        if (isset($toItem["address"][0])) {
            $addressCount++;
            if (isset($toString[0])) $toString.=",";
            $toString.="\'{$toItem['name']}\' <{$toItem['address']}>";
            $mail->addAddress($toItem["address"], $toItem["name"]);
        }
    }
    if (isset($cc["address"][0])) $cc=[$cc];
    if (isset($cc[0])) {
        $data["cc"]=$cc;
        $ccString="";
        foreach ($cc as $idx => $ccItem) {
            if (isset($ccItem["address"][0])) {
                $addressCount++;
                if (isset($ccString[0])) $ccString.=",";
                $ccString.="\'{$ccItem['name']}\' <{$ccItem['address']}>";
                $mail->addCC($ccItem["address"], $ccItem["name"]);
            }
        }
    }
    if (isset($bcc["address"][0])) $bcc=[$bcc];
    if (isset($bcc[0])) {
        $data["bcc"]=$bcc;
        $bccString="";
        foreach ($bcc as $idx => $bccItem) {
            if (isset($bccItem["address"][0])) {
                $addressCount++;
                if (isset($bccString[0])) $bccString.=",";
                $bccString.="\'{$bccItem['name']}\' <{$bccItem['address']}>";
                $mail->addBCC($bccItem["address"], $bccItem["name"]);
            }
        }
    }
    $mail->addMonitor();
    $data+=$mail->getInfo();
    $mail->setSubject($asunto);
    $mail->setBody($mensaje);   
    $mail->setAltBody("Mensaje no disponible en su aplicacion de correo");
    $ss=hasSession();
    if($ss) $_SESSION["lastEmailInfo"]=$data; //+["subject"=>$asunto,"body"=>$mensaje];
    else $GLOBALS['lastEmailInfo']=$data; //+["subject"=>$asunto,"body"=>$mensaje];
    doclog(
        "Enviando correo [FX]",
        "mail",
        array_filter(
            $data,
            function ($v,$k){if (is_string($v)&&strlen($v)>500) return false; return true;},
            ARRAY_FILTER_USE_BOTH));
    try {
        try {
            if (!$mail->send()) {
                $mailErrMsg="Falló envío de correo";
                $errData=["info"=>$mail->getErrorInfo()];
                if (!empty($mail->error)) {
                    $errData["error"]=$mail->error;
                }
                if(!empty($mail->debug)) {
                    $errData["debug"]=$mail->debug;
                }
                errlog($mailErrMsg,"mail",$errData);
                if($ss) $_SESSION["lastEmailInfo"]+=$errData;
                else $GLOBALS['lastEmailInfo']+=$errData;
                return false;
            }
            $domKey=$data["domain"];

            addMailHourCount($domKey,$addressCount);
            doclog("Correo exitoso","mail",["domain"=>$domKey,"mode"=>$data["mailSetMode"],"mailCount"=>($data["mailCount"]+$addressCount),"mailList"=>$data["mailToList"]]);
            if($ss) $_SESSION["lastEmailInfo"]+=["addressCount"=>$addressCount];
            else $GLOBALS['lastEmailInfo']+=["addressCount"=>$addressCount];
            return true;
        } catch (phpmailerException $pe) {
            $mailErrMsg="Excepcion PHPMailer en envío de correo";
            $errData=["info"=>$mail->getErrorInfo()];
            $errData["exception"]=getErrorData($pe);
            if(!empty($mail->error)) $errData["error"]=$mail->error;
            if(!empty($mail->debug)) {
                $errData["debug"]=$mail->debug;
            }
            errlog($mailErrMsg,"mail",$errData);
            if($ss) $_SESSION["lastEmailInfo"]+=$errData;
            else $GLOBALS['lastEmailInfo']+=$errData;
            return false;
        }
    } catch (Exception $e) {
        $mailErrMsg="Excepcion en envío de correo";
        $errData=["info"=>$mail->getErrorInfo()];
        $errData["exception"]=getErrorData($e);
        if(!empty($mail->error)) $errData["error"]=$mail->error;
        if(!empty($mail->debug)) {
            $errData["debug"]=$mail->debug;
        }
        errlog($mailErrMsg,"mail",$errData);
        if($ss) $_SESSION["lastEmailInfo"]+=$errData;
        else $GLOBALS['lastEmailInfo']+=$errData;
        return false;
    }
}

function startsWith($needle, $haystack) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function endsWith($needle, $haystack) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

function getHtmlOptions($array, $selected=null, $spacelen=0) {
    $retval = "";
    $space = str_pad("", $spacelen, " ");
    foreach ($array as $key => $value) {
        $retval .= $space."<option value=\"$key\"";
        if (isset($selected) && (is_array($selected) && in_array($key, $selected)) || $key===$selected) {
            $retval .= " selected";
        }
        if (is_array($value)) {
            foreach($value as $attr=>$aval) {
                if ($attr!=="value") $retval .= " $attr=\"$aval\"";
            }
            if(empty($value["value"])) $retval .= ">$key</option>";
            else $retval .= ">$value[value]</option>";
        } else $retval .= ">$value</option>";
    }
    return $retval;
}
// $gpoOptList="[{eName:\"OPTION\",value:\"\",codigo:\"Todos\",rfc:\"Todos\",razon:\"Todas\",eText:\"Todos\"}]";
// [{eName:"OPTION",value:"",codigo:"Todos",rfc:"Todos",razon:"Todas",eText:"Todos"}];
function getEOBJOptions($array, $selected=null) {
    $retval=[];
    foreach ($array as $key => $value) {
        $opt=["eName"=>"OPTION","value"=>$key];
        if (isset($selected) && (is_array($selected) && in_array($key, $selected)) || $key===$selected)
            $opt["selected"]=true;
        if (is_array($value)) {
            foreach($value as $attr=>$aval) if ($attr!=="value") $opt[$attr]=$aval;
            if(empty($value["value"])) $opt["eText"]=$key;
            else $opt["eText"]=$value["value"];
        } else $opt["eText"]=$value;
        $retval[]=$opt;
    }
    return $retval;
}
function getEOBJStrOptions($array, $selected=null) {
    $retval="";
    foreach ($array as $key=>$value) {
        if (isset($retval[0])) $retval.=", ";
        $retval.="{eName:\"OPTION\", value:\"$key\"";
        if (isset($selected) && ((is_array($selected) && in_array($key, $selected)) || $key===$selected))
            $retval .= ", selected:true";
        if (is_array($value)) {
            foreach($value as $attr=>$aval) if ($attr!=="value") $retval.=", $attr:\"$aval\"";
            if(empty($value["value"])) $retval.=", text:\"$key\"";
            else $retval.=", text:\"$value[value]\"";
        } else $retval.=", text:\"$value\"";
        $retval.="}";
    }
    return $retval;
}
function getProcessId( $imagename) {
    ob_start();
    passthru('wmic process where (name="'.$imagename.'") get ProcessId');
    $wmic_output = ob_get_contents();
    ob_end_clean();
    $wmic_output = preg_replace( 
        array('/[^0-9\n]*/','/[^0-9]+\n|\n$/','/\n/'), 
        array('','',','), 
        $wmic_output );
    if (strpos($wmic_output, ",")===0) $wmic_output = substr($wmic_output, 1);
    if ($wmic_output != '') {
        // WMIC returned valid PId, should be safe to convert to int:
        $wmic_output = explode(',', $wmic_output);
        foreach ($wmic_output as $k => $v) {
            $wmic_output[$k] = (int)$v;
        }
        return $wmic_output;
    } else {
        // WMIC did not return valid PId
        return false;
    }
}
function crypto_rand_secure($min, $max) {
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}
function getBinFlags($num, $inverse=false) {
    $binStr=decbin($num);
    $flags=[];
    $binSz=strlen($binStr);
    for ($i=0; $i<$binSz; $i++) {
        if ($binStr[$binSz-$i-1]==="1") {
            if ($inverse) array_unshift($flags, 2**$i);
            else $flags[]=2**$i;
        }
    }
    return $flags;
}
function getToken($length) {
    require_once "clases/Tokens.php";
    return Tokens::nuevo($length);
}
//global $ualog;
function isValidBrowser() {
    //global $ualog;
    //$ualog="";
    return isBrowser(["Chrome","Edge"]); // ,"IE","Edge","Firefox"
}
function isBrowser($value) {
    static $browser = false;
    //global $ualog;
    if ($browser===false) {
        //$ualog.="init. browser is false\n";
        $browser=getBrowser();
        //$ualog.="browser = '$browser'\n";
    }
    if (empty($value)) {
        //doclog("Invalid Browser Empty: $browser","error",["useragent"=>getBrowser("ua"),"version"=>getBrowser("v"),"ip"=>getIP()]);
        return false;
    } else if (is_array($value)) {
        foreach($value as $name) {
            if ($browser===$name) {
                //$ualog.="FOUND item '$name'\n";
                //doclog("Valid browser item: $browser","user",["useragent"=>getBrowser("ua"),"version"=>getBrowser("v"),"value"=>$value,"ip"=>getIP()]);
                return true;
            }
        }
        //$ualog.="NOT FOUND ITEM\n";
        //doclog("Invalid Browser Array: $browser","error",["useragent"=>getBrowser("ua"),"version"=>getBrowser("v"),"value"=>$value,"ip"=>getIP()]);
        return false;
    } else if ($browser===$value) {
        //doclog("Valid browser value: $browser","user",["useragent"=>getBrowser("ua"),"version"=>getBrowser("v"),"ip"=>getIP()]);
        return true;
    } else {
        //if ($browser===$value) $ualog.="FOUND value '$name'\n";
        //else $ualog.="NOT FOUND value '$name'\n";
        //return ($browser===$value);
        //doclog("Invalid Browser Value: $browser","error",["useragent"=>getBrowser("ua"),"version"=>getBrowser("v"),"value"=>$value,"ip"=>getIP()]);
        return false;
    }
}
function getBrowser($datatype="browser") {
    static $browser=false;
    static $version=false;
    static $debug="";
    $reqBrowser = false;
    $reqVersion = false;
    $reqUserAgent = false;
    switch(strtolower($datatype)) {
        case "v": case "ver": case "version":
            $reqVersion=true;
            break;
        case "ua": case "user": case "agent": case "useragent":
            $reqUserAgent = true;
            break;
        case "clear": $debug=""; $browser=false; $version=false;
        case "debug": return $debug; break;
        default:
            $reqBrowser=true;
    }
    
    if ($reqBrowser && $browser!==false) return $browser;
    if ($reqVersion && $version!==false) return $version;
    
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $usragt = $_SERVER['HTTP_USER_AGENT'];
    } else return "Undefined";

    $debug.="UserAgent=$usragt\n";
    if ($reqUserAgent) return $usragt;

    if (($idx = strpos($usragt, "Edge")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 4)." | ".substr($usragt, $idx+4, 5)."\n";
        $browser = "Edge"; // Microsoft Edge
    } else if (($idx = strpos($usragt, "EdgiOS")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 4)." | ".substr($usragt, $idx+4, 5)."\n";
        $browser = "Edge"; // Microsoft Edge
    } else if (($idx = strpos($usragt, "MSIE")) !== false) {
        $browser = "IE"; // Internet Explorer
        $ieVer = substr($usragt, $idx+5, 2);
        switch ($ieVer) {
            case "10": $version="10"; break;
            case "9.": $version="9"; break;
            case "8.": $version="8"; break;
            default: $version="7-";
        }
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 5)." | ".substr($usragt, $idx+5, 2)."\n";
    } else if (($idx = strpos($usragt, "Trident")) !== false) {
        $browser = "IE"; // Internet Explorer 11
        $ieVer = substr($usragt, $idx+8, 1);
        switch($ieVer) {
            case "7": $version="11"; break;
            case "6": $version="10"; break;
            case "5": $version="9"; break;
            case "4": $version="8"; break;
            default: $version="7-";
        }
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 8)." | ".substr($usragt, $idx+8, 1)."\n";
    } else if (($idx = strpos($usragt, "Opera Mini")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 10)." | ".substr($usragt, $idx+10, 5)."\n";
        $browser = "Opera"; // Opera Mini
    } else if (($idx = strpos($usragt, "Opera")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 5)." | ".substr($usragt, $idx+5, 5)."\n";
        $browser = "Opera"; // Opera
    } else if (($idx = strpos($usragt, "OPR")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 3)." | ".substr($usragt, $idx+3, 5)."\n";
        $browser = "Opera"; // Opera
    } else if (($idx = strpos($usragt, "Firefox")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 7)." | ".substr($usragt, $idx+7, 5)."\n";
        $browser = "Firefox"; // Mozilla Firefox
    } else if (($idx = strpos($usragt, "FxiOS")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 5)." | ".substr($usragt, $idx+5, 5)."\n";
        $browser = "Firefox"; // Mozilla Firefox
    } else if (($idx = strpos($usragt, "Chrome")) !== false) {
        $browser = "Chrome"; // Google Chrome
        $spIdx = strpos($usragt, " ", $idx+6);
        if ($spIdx !== false) {
            $spIdx -= ($idx+7);
            $version = substr($usragt, $idx+7, $spIdx);
        }
    } else if (($idx = strpos($usragt, "CriOS")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 5)." | ".substr($usragt, $idx+5, 5)."\n";
        $browser = "Chrome"; // Google Chrome
    } else if (($idx = strpos($usragt, "Safari")) !== false) {
        $debug.="Index: $idx\nChunk: ".substr($usragt, $idx, 6)." | ".substr($usragt, $idx+6, 5)."\n";
        $browser = "Safari"; // Safari
    } else {
        return "Unknown";
    }
    if ($reqVersion) {
        if (!empty($version)) return $version;
        return "Unknown";
    }
    return $browser;
}

function getBrowserVersion() {
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $usragt = $_SERVER['HTTP_USER_AGENT'];
    } else return "Undefined";
    $ie1Idx = strpos($usragt, "Trident");
    if ($ie1Idx !== false) {
        $ie1Ver = substr($usragt, $ie1Idx+8, 1);
        switch ($ie1Ver) {
            case "7": return "IE11";
            case "6": return "IE10";
            case "5": return "IE9";
            case "4": return "IE8";
//            default:
//                if (strpos($usragt,"MSIE")===false) return "IE";
        }
    }
    $ie2Idx = strpos($usragt, "MSIE");
    if ($ie2Idx !==false) {
        $ie2Ver = substr($usragt, $ie2Idx+5, 2);
        switch ($ie2Ver) {
            case "10": return "IE10";
            case "9.": return "IE9";
            case "8.": return "IE8";
//            default: return "IE";
        }
    }
    return "Undefined";
}
function isMobile() {
    $agente = strtolower($_SERVER['HTTP_USER_AGENT']);
    $moviles = array('iphone', 'ipad', 'android', 'windows phone', 'blackberry');

    foreach ($moviles as $movil) {
        if (strpos($agente, $movil) !== false) {
            return true;
        }
    }
    return false;
}
function parseUserAgent($ua = null) {
    if ($ua === null && isset($_SERVER['HTTP_USER_AGENT'])) {
        $ua = $_SERVER['HTTP_USER_AGENT'];
    }
    $result = [
        'device' => 'Unknown',
        'deviceVersion' => null,
        'deviceOS' => 'Unknown',
        'deviceOSVersion' => null,
        'browser' => 'Unknown',
        'browserVersion' => null
    ];
    // Detect OS and version
    if (preg_match('/Android\s([\d.]+)/', $ua, $match)) {
        $result['deviceOS'] = 'Android';
        $result['deviceOSVersion'] = $match[1];
    } elseif (preg_match('/Windows NT\s([\d.]+)/', $ua, $match)) {
        $result['deviceOS'] = 'Windows';
        $result['deviceOSVersion'] = $match[1];
    } elseif (preg_match('/iPhone OS\s([\d_]+)/', $ua, $match)) {
        $result['deviceOS'] = 'iOS';
        $result['deviceOSVersion'] = str_replace('_', '.', $match[1]);
    } elseif (preg_match('/Mac OS X\s([\d_]+)/', $ua, $match)) {
        $result['deviceOS'] = 'macOS';
        $result['deviceOSVersion'] = str_replace('_', '.', $match[1]);
    }
    // Detect browser and version
    if (preg_match('/Chrome\/([\d.]+)/', $ua, $match)) {
        $result['browser'] = 'Chrome';
        $result['browserVersion'] = $match[1];
    } elseif (preg_match('/Firefox\/([\d.]+)/', $ua, $match)) {
        $result['browser'] = 'Firefox';
        $result['browserVersion'] = $match[1];
    } elseif (preg_match('/Safari\/([\d.]+)/', $ua) && preg_match('/Version\/([\d.]+)/', $ua, $match)) {
        $result['browser'] = 'Safari';
        $result['browserVersion'] = $match[1];
    } elseif (preg_match('/Edg\/([\d.]+)/', $ua, $match)) {
        $result['browser'] = 'Edge';
        $result['browserVersion'] = $match[1];
    }
    // Detect device name and version (if available)
    if (preg_match('/\(([^;]+); Android [\d.]+; ([^)]+)\)/', $ua, $match)) {
        $result['device'] = 'Android';
        $result['deviceVersion'] = trim($match[2]);
    } elseif (preg_match('/\((iPhone|iPad);.*OS ([\d_]+)/', $ua, $match)) {
        $result['device'] = $match[1];
        $result['deviceVersion'] = str_replace('_', '.', $match[2]);
    }
    return $result;
}
function toTable($matrix=false, $headers=false, $tableClassList=false, $headersClassList=false, $rowsClassList=false, $cellsClassList=false) {
    $result = "";
    if ($matrix === false) return $result;
    $result .= "<table>\n".toTableHeader($headers, $headersClassList)."\n<tbody>\n";
    if (!is_array($matrix)) $result .= toTableRow($matrix)."\n";
    else {
        foreach ($matrix as $row) {
            $result .= toTableRow($row, $headers, $rowsClassList, $cellsClassList);
        }
    }
    $result .= "\n</tbody></table>\n";
    return $result;
}
function toTableRow($row=false, $headers=false, $rowsClassList=false, $cellsClassList=false) {
    if ($row === false) return "";
    if (is_object($row)) return "<tr><td key='object'>&lt;".get_class($row)."&gt;</td></tr>";
    if (!is_array($row)) 
        return "<tr><td key='".gettype($row)."'>$row</td></tr>";
    $result = "<tr>";
    if (!empty($headers) && isset($row[$headers[0]])) {
        foreach($headers as $key) {
            $result .= "<td key='$key'>".$row[$key]."</td>";
        }
    } else {
        foreach ($row as $cell) {
            $result .= "<td key='idx'>$cell</td>";
        }
    }
    $result .= "</tr>";
    return $result;
}
function toTableHeader($headers=false, $headersClassList=false) {
    if ($headers === false) return "";
    if (is_object($headers)) return "<tr><th>&lt;".get_class($headers)."&gt;</th></tr>";
    if (!is_array($headers)) 
    // if (is_string($headers))
        return "<tr><th>$headers</th></tr>";
    $result = "<thead><tr>";
    foreach ($headers as $colname) {
        $result .= "<th>$colname</th>";
    }
    $result .= "</tr></thead>";
    return $result;
}
function getArrayFromCSVFilename($filename) {
    $fullArray = [];
//    ini_set('auto_detect_line_endings',true); // Deal with Mac line endings
    if (($handle = fopen($filename, "r"))!==false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $fullArray[] = $data;
        }
        fclose($handle);
    }
//    ini_set('auto_detect_line_endings',false); // Deal with Mac line endings
    return $fullArray;
}
function strrot($s, $n = 13) {
    static $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
    $n = (int)$n % 26;
    if (!$n) return $s;
    if ($n < 0) $n += 26;
    if ($n == 13) return str_rot13($s);
    $rep = substr($letters, $n * 2) . substr($letters, 0, $n * 2);
    return strtr($s, $letters, $rep);
}
function ctarot($s, $op="+", $key="00004356837292731071394613758507") {
    for($i=0; isset($s[$i]); $i++) {
        $digit = $s[$i];
        if (ctype_digit($digit)) {
            $val = +$digit;
            $kvl = +$key[$i];
            switch($op) {
                case "+": $val = ($val+$kvl)%10; break;
                case "-": $val = (10+$val-$kvl)%10; break;
                case "*": $val = ($val*$kvl)%10; break;
                case "/": $val = ($val/$kvl)%10; break;
            }
            $digit = "$val";
            $s[$i] = $digit[0];
        }
    }
    return $s;
}
function encta($s) {
    return strrot(base64_encode(ctarot($s, "+", "00001071435683729617583527407339")),7);
}
function decta($s) {
    return ctarot(base64_decode(strrot($s,26-7)), "-", "00001071435683729617583527407339");
}
function enstr($s) {
    return strrot(base64_encode(strrot($s,11)),17);
}
function destr($s) {
    return strrot(base64_decode(strrot($s,26-17)),26-11);
}
function codeMix($text, $sequence) {
    $parIdx=2;
    switch($sequence) {
        case 0: // ctarot
        break;
        case 1: // base64_encode
        break;
        case 2: // base64_decode
        break;
        case 3: // strrot
    }
}
function convBase($numberInput, $fromBaseInput, $toBaseInput) {
    if ($fromBaseInput==$toBaseInput) return $numberInput;
    $fromBase = str_split($fromBaseInput,1);
    $toBase = str_split($toBaseInput,1);
    $number = str_split($numberInput,1);
    $fromLen=strlen($fromBaseInput);
    $toLen=strlen($toBaseInput);
    $numberLen=strlen($numberInput);
    $retval='';
    if ($toBaseInput == '0123456789') {
        $retval=0;
        for ($i = 1;$i <= $numberLen; $i++)
            $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
        return $retval;
    }
    if ($fromBaseInput != '0123456789')
        $base10=convBase($numberInput, $fromBaseInput, '0123456789');
    else
        $base10 = $numberInput;
    if ($base10<strlen($toBaseInput))
        return $toBase[$base10];
    while($base10 != '0') {
        $retval = $toBase[bcmod($base10,$toLen)].$retval;
        $base10 = bcdiv($base10,$toLen,0);
    }
    return $retval;
}
function rome($N){ // Arabic 2 Roman converter
    $c='IVXLCDM';
    for($a=5,$b=$s='';$N;$b++,$a^=7)
        for($o=$N%$a,$N=$N/$a^0;$o--;$s=$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s);
    return $s;
}
function str_baseconvert($str, $frombase=10, $tobase=36) { // base_convert lose precision, this solves precision
    $str = trim($str);
    if (intval($frombase) != 10) {
        $len = strlen($str);
        $q = 0;
        for ($i=0; $i<$len; $i++) {
            $r = base_convert($str[$i], $frombase, 10);
            $q = bcadd(bcmul($q, $frombase), $r);
        }
    } else $q = $str;
    if (intval($tobase) != 10) {
        $s = '';
        while (bccomp($q, '0', 0) > 0) {
            $r = intval(bcmod($q, $tobase));
            $s = base_convert($r, 10, $tobase) . $s;
            $q = bcdiv($q, $tobase, 0);
        }
    } else $s = $q;
    return $s;
}
function base32_decode($d) {
    list($t, $b, $r) = array("ABCDEFGHIJKLMNOPQRSTUVWXYZ234567", "", "");
    foreach(str_split($d) as $c)
        $b = $b . sprintf("%05b", strpos($t, $c));
    foreach(str_split($b, 8) as $c)
        $r = $r . chr(bindec($c));
    return($r);
}
function base32_encode($d) {
    list($t, $b, $r) = array("ABCDEFGHIJKLMNOPQRSTUVWXYZ234567", "", "");
    foreach(str_split($d) as $c)
        $b = $b . sprintf("%08b", ord($c));
    foreach(str_split($b, 5) as $c)
        $r = $r . $t[bindec($c)];
    return($r);
}
/**
* Converts an integer into the alphabet base (A-Z).
* @param int $n This is the number to convert.
* @return string The converted number.
* @author Theriault
*/
function num2alpha($n) {
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
        $n -= pow(26, $i);
    }
    return $r;
}
/**
* Converts an alphabetic string into an integer.
* @param string $a This is the string to convert.
* @return int The converted number.
* @author Theriault
*/
function alpha2num($a) {
    $r = 0;
    $l = strlen($a);
    for ($i = 0; $i < $l; $i++) {
        $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
    }
    return $r - 1;
}
define('FILE_ENCRYPTION_BLOCKS', 10000);
function encryptFile($source, $key, $dest) {
    $key = substr(sha1($key, true), 0, 16);
    $iv = openssl_random_pseudo_bytes(16);
    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        fwrite($fpOut, $iv);
        if ($fpIn = fopen($source, 'rb')) {
            while (!feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
        } else $error = true;
        fclose($fpOut);
    } else $error = true;
    return $error ? false : $dest;
}
function decryptFile($source, $key, $dest) {
    $key = substr(sha1($key, true), 0, 16);
    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        if ($fpIn = fopen($source, 'rb')) {
            $iv = fread($fpIn, 16);
            while (!feof($fpIn)) {
                // we have to read one block more for decrypting than for encrypting
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); 
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpIn);
        } else $error = true;
        fclose($fpOut);
    } else $error = true;
    return $error ? false : $dest;
}
function decrypt2TmpFile($source, $key) {
    $key = substr(sha1($key, true), 0, 16);
    $error = false;
    if ($fpOut = tmpfile()) {
        if ($fpIn = fopen($source, 'rb')) {
            $iv = fread($fpIn, 16);
            while (!feof($fpIn)) {
                // we have to read one block more for decrypting than for encrypting
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); 
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpIn);
        } else $error = true;
//        fclose($fpOut);
    } else $error = true;
    return $error ? false : $fpOut;
}
function replaceAccents($str) {
  static $search = null;
  static $replace = null;
  if (!isset($search)) $search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");
  if (!isset($replace)) $replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");
  return str_replace($search, $replace, $str);
}
function normalize_to_utf8_chars($string,$otherReplacement='') {     // Nr. | Unicode | Win1252 | Expected  | Actually  | UTF8 Bytes  |   HTML   | Description
                                                //-----------------------------------------------------------------------------------------------------------------------
  $search=array(chr(0xC2).chr(0xA0),            // 028 | U+00A0  | 0xA0    |           | Â         | %C2 %A0     | &nbsp;   | Non-breaking space. Replaced by single space
                chr(0xC2).chr(0xA1),            // 029 | U+00A1  | 0xA1    | ¡         | Â¡        | %C2 %A1     | &iexcl;  | Inverted Exclamation Mark
                chr(0xC2).chr(0xA2),            // 030 | U+00A2  | 0xA2    | ¢         | Â¢        | %C2 %A2     | &cent;   | Cent sign
                chr(0xC2).chr(0xA3),            // 031 | U+00A3  | 0xA3    | £         | Â£        | %C2 %A3     | &pound;  | Pound sign
                chr(0xC2).chr(0xA4),            // 032 | U+00A4  | 0xA4    | ¤         | Â¤        | %C2 %A4     | &curren; | Currency sign
                chr(0xC2).chr(0xA5),            // 033 | U+00A5  | 0xA5    | ¥         | Â¥        | %C2 %A5     | &yen;    | Yen sign
                chr(0xC2).chr(0xA6),            // 034 | U+00A6  | 0xA6    | ¦         | Â¦        | %C2 %A6     | &brvbar; | Broken bar
                chr(0xC2).chr(0xA7),            // 035 | U+00A7  | 0xA7    | §         | Â§        | %C2 %A7     | &sect;   | Section sign
                chr(0xC2).chr(0xA8),            // 036 | U+00A8  | 0xA8    | ¨         | Â¨        | %C2 %A8     | &uml;    | Diaeresis (Umlaut)
                chr(0xC2).chr(0xA9),            // 037 | U+00A9  | 0xA9    | ©         | Â©        | %C2 %A9     | &copy;   | Copyright symbol
                chr(0xC2).chr(0xAA),            // 038 | U+00AA  | 0xAA    | ª         | Âª        | %C2 %AA     | &ordf;   | Feminine Ordinal Indicator
                chr(0xC2).chr(0xAB),            // 039 | U+00AB  | 0xAB    | «         | Â«        | %C2 %AB     | &laquo;  | Left-pointing double angle quotation mark
                chr(0xC2).chr(0xAC),            // 040 | U+00AC  | 0xAC    | ¬         | Â¬        | %C2 %AC     | &not;    | Not sign
                chr(0xC2).chr(0xAD),            // 041 | U+00AD  | 0xAD    |           | Â         | %C2 %AD     | &shy;    | Soft hyphen. Replaced by single space
                chr(0xC2).chr(0xAE),            // 042 | U+00AE  | 0xAE    | ®         | Â®        | %C2 %AE     | &reg;    | Registered trademark symbol
                chr(0xC2).chr(0xAF),            // 043 | U+00AF  | 0xAF    | ¯         | Â¯        | %C2 %AF     | &macr;   | Macron
                chr(0xC2).chr(0xB0),            // 044 | U+00B0  | 0xB0    | °         | Â°        | %C2 %B0     | &deg;    | Degree sign
                chr(0xC2).chr(0xB1),            // 045 | U+00B1  | 0xB1    | ±         | Â±        | %C2 %B1     | &plusmn; | Plus-minus sign
                chr(0xC2).chr(0xB2),            // 046 | U+00B2  | 0xB2    | ²         | Â²        | %C2 %B2     | &sup2;   | Superscript two
                chr(0xC2).chr(0xB3),            // 047 | U+00B3  | 0xB3    | ³         | Â³        | %C2 %B3     | &sup3;   | Superscript three
                chr(0xC2).chr(0xB4),            // 048 | U+00B4  | 0xB4    | ´         | Â´        | %C2 %B4     | &acute;  | Acute accent
                chr(0xC2).chr(0xB5),            // 049 | U+00B5  | 0xB5    | µ         | Âµ        | %C2 %B5     | &micro;  | Micro sign
                chr(0xC2).chr(0xB6),            // 050 | U+00B6  | 0xB6    | ¶         | Â¶        | %C2 %B6     | &para;   | Pilcrow sign
                chr(0xC2).chr(0xB7),            // 051 | U+00B7  | 0xB7    | ·         | Â·        | %C2 %B7     | &middot; | Middle dot
                chr(0xC2).chr(0xB8),            // 052 | U+00B8  | 0xB8    | ¸         | Â¸        | %C2 %B8     | &cedil;  | Cedilla
                chr(0xC2).chr(0xB9),            // 053 | U+00B9  | 0xB9    | ¹         | Â¹        | %C2 %B9     | &sup1;   | Superscript one
                chr(0xC2).chr(0xBA),            // 054 | U+00BA  | 0xBA    | º         | Âº        | %C2 %BA     | &ordm;   | Masculine Ordinal Indicator
                chr(0xC2).chr(0xBB),            // 055 | U+00BB  | 0xBB    | »         | Â»        | %C2 %BB     | &raquo;  | Right-pointing double angle quotation mark
                chr(0xC2).chr(0xBC),            // 056 | U+00BC  | 0xBC    | ¼         | Â¼        | %C2 %BC     | &frac14; | Vulgar fraction one quarter
                chr(0xC2).chr(0xBD),            // 057 | U+00BD  | 0xBD    | ½         | Â½        | %C2 %BD     | &frac12; | Vulgar fraction one half
                chr(0xC2).chr(0xBE),            // 058 | U+00BE  | 0xBE    | ¾         | Â¾        | %C2 %BE     | &frac34; | Vulgar fraction three quarters
                chr(0xC2).chr(0xBF),            // 059 | U+00BF  | 0xBF    | ¿         | Â¿        | %C2 %BF     | &iquest; | Inverted Question Mark
                chr(0xC3).chr(0x80),            // 060 | U+00C0  | 0xC0    | À         | Ã€        | %C3 %80     | &Agrave; | Latin Capital letter A with grave
                chr(0xC3).chr(0x81),            // 061 | U+00C1  | 0xC1    | Á         | Ã         | %C3 %81     | &Aacute; | Latin Capital letter A with acute
                chr(0xC3).chr(0x82),            // 062 | U+00C2  | 0xC2    | Â         | Ã‚        | %C3 %82     | &Acirc;  | Latin Capital letter A with circumflex
                chr(0xC3).chr(0x83),            // 063 | U+00C3  | 0xC3    | Ã         | Ãƒ        | %C3 %83     | &Atilde; | Latin Capital letter A with tilde
                chr(0xC3).chr(0x84),            // 064 | U+00C4  | 0xC4    | Ä         | Ã„        | %C3 %84     | &Auml;   | Latin Capital letter A with diaeresis
                chr(0xC3).chr(0x85),            // 065 | U+00C5  | 0xC5    | Å         | Ã…        | %C3 %85     | &Aring;  | Latin Capital letter A with ring above
                chr(0xC3).chr(0x86),            // 066 | U+00C6  | 0xC6    | Æ         | Ã†        | %C3 %86     | &AElig;  | Latin Capital letter Æ
                chr(0xC3).chr(0x87),            // 067 | U+00C7  | 0xC7    | Ç         | Ã‡        | %C3 %87     | &Ccedil; | Latin Capital letter C with cedilla
                chr(0xC3).chr(0x88),            // 068 | U+00C8  | 0xC8    | È         | Ãˆ        | %C3 %88     | &Egrave; | Latin Capital letter E with grave
                chr(0xC3).chr(0x89),            // 069 | U+00C9  | 0xC9    | É         | Ã‰        | %C3 %89     | &Eacute; | Latin Capital letter E with acute
                chr(0xC3).chr(0x8A),            // 070 | U+00CA  | 0xCA    | Ê         | ÃŠ        | %C3 %8A     | &Ecirc;  | Latin Capital letter E with circumflex
                chr(0xC3).chr(0x8B),            // 071 | U+00CB  | 0xCB    | Ë         | Ã‹        | %C3 %8B     | &Euml;   | Latin Capital letter E with diaeresis
                chr(0xC3).chr(0x8C),            // 072 | U+00CC  | 0xCC    | Ì         | ÃŒ        | %C3 %8C     | &Igrave; | Latin Capital letter I with grave
                chr(0xC3).chr(0x8D),            // 073 | U+00CD  | 0xCD    | Í         | Ã         | %C3 %8D     | &Iacute; | Latin Capital letter I with acute
                chr(0xC3).chr(0x8E),            // 074 | U+00CE  | 0xCE    | Î         | ÃŽ        | %C3 %8E     | &Icirc;  | Latin Capital letter I with circumflex
                chr(0xC3).chr(0x8F),            // 075 | U+00CF  | 0xCF    | Ï         | Ã         | %C3 %8F     | &Iuml;   | Latin Capital letter I with diaeresis
                chr(0xC3).chr(0x90),            // 076 | U+00D0  | 0xD0    | Ð         | Ã         | %C3 %90     | &ETH;    | Latin Capital letter Eth
                chr(0xC3).chr(0x91),            // 077 | U+00D1  | 0xD1    | Ñ         | Ã‘        | %C3 %91     | &Ntilde; | Latin Capital letter N with tilde
                chr(0xC3).chr(0x92),            // 078 | U+00D2  | 0xD2    | Ò         | Ã’        | %C3 %92     | &Ograve; | Latin Capital letter O with grave
                chr(0xC3).chr(0x93),            // 079 | U+00D3  | 0xD3    | Ó         | Ã“        | %C3 %93     | &Oacute; | Latin Capital letter O with acute
                chr(0xC3).chr(0x94),            // 080 | U+00D4  | 0xD4    | Ô         | Ã”        | %C3 %94     | &Ocirc;  | Latin Capital letter O with circumflex
                chr(0xC3).chr(0x95),            // 081 | U+00D5  | 0xD5    | Õ         | Ã•        | %C3 %95     | &Otilde; | Latin Capital letter O with tilde
                chr(0xC3).chr(0x96),            // 082 | U+00D6  | 0xD6    | Ö         | Ã–        | %C3 %96     | &Ouml;   | Latin Capital letter O with diaeresis
                chr(0xC3).chr(0x97),            // 083 | U+00D7  | 0xD7    | ×         | Ã—        | %C3 %97     | &times;  | Multiplication sign
                chr(0xC3).chr(0x98),            // 084 | U+00D8  | 0xD8    | Ø         | Ã˜        | %C3 %98     | &Oslash; | Latin Capital letter O with stroke
                chr(0xC3).chr(0x99),            // 085 | U+00D9  | 0xD9    | Ù         | Ã™        | %C3 %99     | &Ugrave; | Latin Capital letter U with grave
                chr(0xC3).chr(0x9A),            // 086 | U+00DA  | 0xDA    | Ú         | Ãš        | %C3 %9A     | &Uacute; | Latin Capital letter U with acute
                chr(0xC3).chr(0x9B),            // 087 | U+00DB  | 0xDB    | Û         | Ã›        | %C3 %9B     | &Ucirc;  | Latin Capital letter U with circumflex
                chr(0xC3).chr(0x9C),            // 088 | U+00DC  | 0xDC    | Ü         | Ãœ        | %C3 %9C     | &Uuml;   | Latin Capital letter U with diaeresis
                chr(0xC3).chr(0x9D),            // 089 | U+00DD  | 0xDD    | Ý         | Ã         | %C3 %9D     | &Yacute; | Latin Capital letter Y with acute
                chr(0xC3).chr(0x9E),            // 090 | U+00DE  | 0xDE    | Þ         | Ãž        | %C3 %9E     | &THORN;  | Latin Capital letter Thorn
                chr(0xC3).chr(0x9F),            // 091 | U+00DF  | 0xDF    | ß         | ÃŸ        | %C3 %9F     | &szlig;  | Latin Small letter sharp S
                chr(0xC3).chr(0xA0),            // 092 | U+00E0  | 0xE0    | à         | Ã         | %C3 %A0     | &agrave; | Latin Small letter  with 
                chr(0xC3).chr(0xA1),            // 093 | U+00E1  | 0xE1    | á         | Ã¡        | %C3 %A1     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA2),            // 094 | U+00E2  | 0xE2    | â         | Ã¢        | %C3 %A2     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA3),            // 095 | U+00E3  | 0xE3    | ã         | Ã£        | %C3 %A3     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA4),            // 096 | U+00E4  | 0xE4    | ä         | Ã¤        | %C3 %A4     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA5),            // 097 | U+00E5  | 0xE5    | å         | Ã¥        | %C3 %A5     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA6),            // 098 | U+00E6  | 0xE6    | æ         | Ã¦        | %C3 %A6     | &; | Latin Small letter 
                chr(0xC3).chr(0xA7),            // 099 | U+00E7  | 0xE7    | ç         | Ã§        | %C3 %A7     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA8),            // 100 | U+00E8  | 0xE8    | è         | Ã¨        | %C3 %A8     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xA9),            // 001 | U+00E9  | 0xE9    | é         | Ã©        | %C3 %A9     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xAA),            // 002 | U+00EA  | 0xEA    | ê         | Ãª        | %C3 %AA     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xAB),            // 003 | U+00EB  | 0xEB    | ë         | Ã«        | %C3 %AB     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xAC),            // 004 | U+00EC  | 0xEC    | ì         | Ã¬        | %C3 %AC     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xAD),            // 005 | U+00ED  | 0xED    | í         | Ã         | %C3 %AD     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xAE),            // 006 | U+00EE  | 0xEE    | î         | Ã®        | %C3 %AE     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xAF),            // 007 | U+00EF  | 0xEF    | ï         | Ã¯        | %C3 %AF     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB0),            // 008 | U+00F0  | 0xF0    | ð         | Ã°        | %C3 %B0     | &; | Latin Small letter 
                chr(0xC3).chr(0xB1),            // 009 | U+00F1  | 0xF1    | ñ         | Ã±        | %C3 %B1     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB2),            // 000 | U+00F2  | 0xF2    | ò         | Ã²        | %C3 %B2     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB3),            // 001 | U+00F3  | 0xF3    | ó         | Ã³        | %C3 %B3     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB4),            // 002 | U+00F4  | 0xF4    | ô         | Ã´        | %C3 %B4     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB5),            // 003 | U+00F5  | 0xF5    | õ         | Ãµ        | %C3 %B5     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB6),            // 004 | U+00F6  | 0xF6    | ö         | Ã¶        | %C3 %B6     | &; | Latin Small letter  with 
                chr(0xC3).chr(0xB7),            // 005 | U+00F7  | 0xF7    | ÷         | Ã·        | %C3 %B7     | 
                chr(0xC3).chr(0xB8),            // 006 | U+00F8  | 0xF8    | ø         | Ã¸        | %C3 %B8     | 
                chr(0xC3).chr(0xB9),            // 007 | U+00F9  | 0xF9    | ù         | Ã¹        | %C3 %B9     | 
                chr(0xC3).chr(0xBA),            // 008 | U+00FA  | 0xFA    | ú         | Ãº        | %C3 %BA     | 
                chr(0xC3).chr(0xBB),            // 009 | U+00FB  | 0xFB    | û         | Ã»        | %C3 %BB     | 
                chr(0xC3).chr(0xBC),            // 000 | U+00FC  | 0xFC    | ü         | Ã¼        | %C3 %BC     | 
                chr(0xC3).chr(0xBD),            // 001 | U+00FD  | 0xFD    | ý         | Ã½        | %C3 %BD     | 
                chr(0xC3).chr(0xBE),            // 002 | U+00FE  | 0xFE    | þ         | Ã¾        | %C3 %BE     | 
                chr(0xC3).chr(0xBF),            // 003 | U+00FF  | 0xFF    | ÿ         | Ã¿        | %C3 %BF     | 
                chr(0xC5).chr(0x92),            // 012 | U+0152  | 0x8C    | Œ         | Å’        | %C5 %92     | 
                chr(0xC5).chr(0x93),            // 025 | U+0153  | 0x9C    | œ         | Å“        | %C5 %93     | 
                chr(0xC5).chr(0xA0),            // 010 | U+0160  | 0x8A    | Š         | Å         | %C5 %A0     | 
                chr(0xC5).chr(0xA1),            // 023 | U+0161  | 0x9A    | š         | Å¡        | %C5 %A1     | 
                chr(0xC5).chr(0xB8),            // 027 | U+0178  | 0x9F    | Ÿ         | Å¸        | %C5 %B8     | 
                chr(0xC5).chr(0xBD),            // 013 | U+017D  | 0x8E    | Ž         | Å½        | %C5 %BD     | 
                chr(0xC5).chr(0xBE),            // 026 | U+017E  | 0x9E    | ž         | Å¾        | %C5 %BE     | 
                chr(0xC6).chr(0x92),            // 003 | U+0192  | 0x83    | ƒ         | Æ’        | %C6 %92     | 
                chr(0xCB).chr(0x86),            // 008 | U+02C6  | 0x88    | ˆ         | Ë†        | %CB %86     | 
                chr(0xCB).chr(0x9C),            // 021 | U+02DC  | 0x98    | ˜         | Ëœ        | %CB %9C     | 
                chr(0xE2).chr(0x80).chr(0x90),  //     | U+2010  |         | –         | â€“       | %E2 %80 %93 | (see: [1])
                chr(0xE2).chr(0x80).chr(0x93),  // 019 | U+2013  | 0x96    | –         | â€“       | %E2 %80 %93 | (see: [1])
                chr(0xE2).chr(0x80).chr(0x94),  // 020 | U+2014  | 0x97    | —         | â€”       | %E2 %80 %94 | (see: [2])
                chr(0xE2).chr(0x80).chr(0x98),  // 014 | U+2018  | 0x91    | ‘         | â€˜       | %E2 %80 %98 | 
                chr(0xE2).chr(0x80).chr(0x99),  // 015 | U+2019  | 0x92    | ’         | â€™       | %E2 %80 %99 | 
                chr(0xE2).chr(0x80).chr(0x9A),  // 002 | U+201A  | 0x82    | ‚         | â€š       | %E2 %80 %9A | 
                chr(0xE2).chr(0x80).chr(0x9C),  // 016 | U+201C  | 0x93    | “         | â€œ       | %E2 %80 %9C | 
                chr(0xE2).chr(0x80).chr(0x9D),  // 017 | U+201D  | 0x94    | ”         | â€        | %E2 %80 %9D | 
                chr(0xE2).chr(0x80).chr(0x9E),  // 004 | U+201E  | 0x84    | „         | â€ž       | %E2 %80 %9E | 
                chr(0xE2).chr(0x80).chr(0xA0),  // 006 | U+2020  | 0x86    | †         | â€        | %E2 %80 %A0 | 
                chr(0xE2).chr(0x80).chr(0xA1),  // 007 | U+2021  | 0x87    | ‡         | â€¡       | %E2 %80 %A1 | 
                chr(0xE2).chr(0x80).chr(0xA2),  // 018 | U+2022  | 0x95    | •         | â€¢       | %E2 %80 %A2 | 
                chr(0xE2).chr(0x80).chr(0xA6),  // 005 | U+2026  | 0x85    | …         | â€¦       | %E2 %80 %A6 | 
                chr(0xE2).chr(0x80).chr(0xB0),  // 009 | U+2030  | 0x89    | ‰         | â€°       | %E2 %80 %B0 | 
                chr(0xE2).chr(0x80).chr(0xB9),  // 011 | U+2039  | 0x8B    | ‹         | â€¹       | %E2 %80 %B9 | 
                chr(0xE2).chr(0x80).chr(0xBA),  // 024 | U+203A  | 0x9B    | ›         | â€º       | %E2 %80 %BA | 
                chr(0xE2).chr(0x82).chr(0xAC),  // 001 | U+20AC  | 0x80    | €         | â‚¬       | %E2 %82 %AC | 
                chr(0xE2).chr(0x84).chr(0xA2)); // 022 | U+2122  | 0x99    | ™         | â„¢       | %E2 %84 %A2 | 
                

                // [3] : Unicode dictates 'Non breaking space' : Replaced by a single space (' ').
                // [4] : Unicode dictates 'Soft hyphen' : Replaced by a single space (' ').
                // [5] : Unicode dictates 'En dash'. Replaced by space minus space (' - ').
                // [2] : Unicode dictates 'Em dash'. Replaced by space minus space (' - ').
                // See https://github.com/OldskoolOrion/normalize_to_utf8_chars for a more verbose explenation.

  $replace = array('€', '‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', 'Ž', '‘', '’', '“', '”', '•', ' - ',
                   ' - ', '˜', '™', 'š', '›', 'œ', 'ž', 'Ÿ', ' ', '¡', '¢', '£', '¤', '¥', '¦', '§', '¨', '©', 'ª',
                   '«', '¬', ' ', '®', '¯', '°', '±', '²', '³', '´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½',
                   '¾', '¿', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                   'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', '×', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã',
                   'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö',
                   '÷', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'þ', 'ÿ');

  $retVal = str_replace($search, $replace, $string);
  // Reemplazar caracteres faltantes de 3 o más bytes
  // Reemplazar caracteres faltantes de dos bytes:
  //$retVal = preg_replace('/[\x00-\x1F\x7F-\xFF]/', $otherReplacement/*''*/, $retVal);
  return $retVal;
}

//if ($isSeason) {
//    global $hasUser, $username;
//    //sessionInit();
//    if ($username==="admin") {
//        $ waitImgName="icons/seasons/tamal-dance2";//snowman//tamal-dance2";//(rand()&1)?"roscar":"reyesr";//"roscareyes";
//        //$waitClass="waitExpanded allWidBut4"; // pc95
//    } else if ($hasUser && !in_array($username, ["mlobatonapsa","comprasapsa1","comprasapsa2","esmeraldac","gabyf","jesusa","magob","margaritab","marisols","factjyl","oliviag","ccmty","comprascorepack","jcalderon","logcorepack","rhcorepack","atorres","cmorysan2","cmorysan3","jrangel"])) {
//        $ waitImgName="icons/seasons/tamal-dance2"; // reindeer // tamal-dance2";//".(rand()&1)?"roscar":"reyesr";//"roscareyes"; // "santasleigh" // "snowman" // "xmastree"
//    }
//}
