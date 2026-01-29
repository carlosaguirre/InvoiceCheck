<?php
/** ftpMigration.php
 * Transferir archivos y datos
 * Puede indicarse 'fecha hora' para que sólo se transfieran
 * los archivos y datos de ese tiempo en adelante
**/

require_once dirname(__DIR__)."/bootstrap.php";
$projectPath = $_SERVER["DOCUMENT_ROOT"]; // "C:\\Apache24\\htdocs\\invoice\\";
$ftpSrv = "ftp://conpro.dyndns-ip.com/";
$bigPathsDepth0=["clases","configuracion","consultas","css","examples","imagenes","manual","sat","scripts","selectores","tareas","templates","test"];
$bigPathsDepth1=["cuentas","LOGS"];
$bigPathsDepht2=["archivos","recibos","descargas"];

/*
$listFiles=glob($projectPath."*");
$listDirs=glob($projectPath."*",GLOB_ONLYDIR);
if (isset($listFiles[0])) {
    sort($listFiles);
    sort($listDirs);
    $dirs=[];
    foreach ($listDirs as $idx => $dir) {
        $dirs[$idx]=basename($dir);
    }
    $result=[];
    foreach($listFiles as $idx=>$filename) {
        $bsnm=basename($filename);
        if (in_array($bsnm, $dirs)) continue;
        $sz=filesize($filename);
        $sx=sizeFix($sz);
        $result[]=" {$sx} - {$bsnm}";
    }
    foreach($dirs as $idx2=>$dirname) {
        $result[]="         ".$dirname."\\";
        $listFilesB=glob($projectPath.$dirname."\\*");
        $

    }
    foreach ($result as $key => $value) {
        echo $value."\n";
    }
    echo "List has ".count($listFiles)." files\n";
}*/
$ftpObj=false;
$ftpLog=[];
$listFunc=function($path, $depth=1) {
    global $ftpLog;
    //echo "INI LISTFUNC '{$path} $depth'\n";
    if ($depth>=0 && $depth<1) {
        //$ftpLog[]="LISTFUNC ERROR: '{$path}' $depth (off)";
        return null;
    }
    if (!isset($path[0])) {
        $ftpLog[]="LISTFUNC ERROR: '{$path}' $depth (path)";
        return null;
    }
    if (substr($path, -1)!=="\\") $path.="\\";
    global $ftpObj;
    if ($ftpObj===false) {
        if (!connectFTP()) {
            $ftpLog[]="LISTFUNC ERROR: '{$path}' $depth (NOCONNECT)";
            return null;
        }
    }
    $fileList=glob($path."*", GLOB_MARK);
    sort($fileList);
    global $listFunc;
    try {
        foreach ($fileList as $key => $value) {
            //echo "[$depth:$key|$value] ";
            if (is_dir($value)) {
                if (openPath($value))
                    $listFunc($value, $depth-1);
                else $ftpLog[]="FAIL1 LISTFUNC|OPENPATH '{$value}'";
            } else if (openPath(dirname($value))) {
                transferFile($value);
            } else $ftpLog[]="FAIL2 LISTFUNC|OPENPATH: '{$value}'";
        }
    } catch (Exception $ex) {
        $txt="ERROR";
        $exClass=get_class($ex);
        if ($exClass!=="Exception") {
            if (substr($exClass, -9)==="Exception")
                $txt.=" ".substr($exClass, 0, -9);
            else $txt.=" ".$exClass;
        }
        if (method_exists($ex, "getCode"))
            $txt.= " (".$ex->getCode().")";
        if (method_exists($ex, "getFile"))
            $txt.= " |".$ex->getFile();
        if (method_exists($ex, "getLine"))
            $txt.= " #".$ex->getLine();
$errorExceptionMethods=["code"=>"getCode","file"=>"getFile","line"=>"getLine","message"=>"getMessage","trace"=>"getTraceAsString"];
foreach ($errorExceptionMethods as $codeKey => $methodName) {
    if (method_exists($ex,$methodName) && !in_array($codeKey, $ignoreKeys)) {
        $errData[$codeKey]=$ex->$methodName();
        if ($codeKey==="trace") $errData[$codeKey]=fixTraceString($errData[$codeKey]);
    } else $errData[$codeKey]="IGNORED";
}
return $errData;
        $ftpLog[]=$txt;
    }
    //echo "END LISTFUNC '{$path}'\n";
};
$remoteAddress="conpro.dyndns-ip.com";
$remoteUser="FACTURAS";
$remotePwd="f3xTv%4ghbA";
$remoteProjectPath="invoice/";
$relativePathStart=strlen($projectPath)-strlen($remoteProjectPath);
function openPath($path) {
    global $ftpObj, $relativePathStart, $ftpLog;
    //echo "INI OPENPATH '{$path}'\n";
    $relativePath=str_replace("\\", "/", substr($path, $relativePathStart-1));
    $currentPath=ftp_pwd($ftpObj)."/";
    if ($currentPath!==$relativePath) {
        $trackErrors=ini_get("track_errors");
        ini_set("track_errors", 1);
        //$ftpLog[]="openPath: CurrentPath '{$currentPath}' is not NewPath '{$relativePath}'";
        if (!@ftp_chdir($ftpObj, $relativePath)) {
            //$ftpLog[]="openPath: Cant Change Path";
            if (!@ftp_mkdir($ftpObj, $relativePath)) {
                $errmsg=json_encode(error_get_last());
                ini_set("track_errors",$trackErrors);
                $ftpLog[]="openPath: Failed to create NewPath '{$relativePath}': $errmsg";
                //echo "OPENPATH ERROR: '{$path}' (mkdir)\n";
                return false;
            } else {
                $changedPath=@ftp_chdir($ftpObj,$relativePath);
                if (!$changedPath) {
                    $errmsg=json_encode(error_get_last());
                    ini_set("track_errors", $trackErrors);
                    $ftpLog[]="openPath: Failed to ChangeDir '{$relativePath}': $errmsg";
                    //echo "OPENPATH ERROR: '{$path}' (chdir)\n";
                    return false;
                }
                //$ftpLog[]="openPath: Created new path: '{$relativePath}'";
                echo "Created Path '{$relativePath}' successfully!\n";
            }
        }
        ini_set("track_errors", $trackErrors);
    } //echo "Same Path: {$relativePath}\n";
    //echo "END OPENPATH '{$path}'\n";

    return true;
}
function transferFile($path) {
    //echo "INI transferFile '{$path}'\n";
    global $ftpObj,$ftpLog;
    $finfo = finfo_open(FILEINFO_MIME);
    $fileType=finfo_file($finfo, $path);
    $ftpType=FTP_BINARY; $typeStr="B";
    if (substr($fileType, 0, 4) == 'text') {
        $ftpType=FTP_ASCII;
        $typeStr="A";
    }
    $filename=basename($path);
    $trackErrors=ini_get("track_errors");
    ini_set("track_errors",1);
    if (!@ftp_put($ftpObj,$path,$filename,$ftpType)) {
        //echo "END transferFile '{$path}' ERROR: ";
        $errmsg=json_encode(error_get_last());
        //echo $errmsg."\n";
        ini_set("track_errors",$trackErrors);
        $ftpLog[]="transferFile({$typeStr}|{$fileType}) failed to upload '{$filename}': $errmsg";
        return false;
    }
    ini_set("track_errors",$trackErrors);
    echo "Transfered File ({$typeStr}|{$fileType}) '{$filename}' successfully!\n";
    //echo " - transferFile '{$path}'\n";
    //$ftpLog[]="TransferFile: INI '{$path}': IN CONSTRUCTION";
    //echo "END transferFile";
    return true;
}
function connectFTP() {
    global $ftpObj, $remoteAddress, $remoteUser, $remotePwd, $ftpLog;
    $remotePort=21;
    $addrBlock = explode(":",$remoteAddress);
    if (isset($addrBlock[1])) {
        $remotePort=+end($addrBlock);
        $remoteAddress=prev($addrBlock);
    }
    $trackErrors = ini_get("track_errors");
    ini_set("track_errors",1);
    $ftpObj=@ftp_connect($remoteAddress,$remotePort);
    if (false===$ftpObj) {
        $errmsg=json_encode(error_get_last());
        ini_set("track_errors", $trackErrors);
        $ftpLog[]="FTP Connection failed: $errmsg";
        return false;
    }
    $result = ftp_login($ftpObj, $remoteUser, $remotePwd);
    if (true!==$result) {
        $errmsg=json_encode(error_get_last());
        ini_set("track_errors", $trackErrors);
        $ftpLog[]="FTP Authentication failed: $errmsg";
        return false;
    }
    if (!ftp_pasv($ftpObj, true)) {
        $errmsg=json_encode(error_get_last());
        ini_set("track_errors",$trackErrors);
        $ftpLog[]="FTP Pasive Mode failed: $errmsg";
        return false;
    }
    ini_set("track_errors", $trackErrors);
    $ftpLog[]="FTP Connection $remoteAddress successful!";
    return true;
}
function closeFTP() {
    global $ftpObj, $ftpLog;
    if (!is_null($ftpObj)) {
        if ($ftpObj!==false) {
            ftp_close($ftpObj);
            $ftpLog[]="FTP Connection closed";
        } else $ftpLog[]="FTP Connection is false";
        $ftpObj=null;
    } else $ftpLog[]="FTP Connection is NULL";
}

echo "BEGIN\n";
try {
    $listFunc($projectPath,1);
} catch (Exception $ex) {
    echo "EXCEPTION: ";
    echo $ex;
    echo "\n";
}
echo "CLOSING\n";
closeFTP();
if (isset($ftpLog[0])) {
    echo "\n  LOG:\n";
    foreach ($ftpLog as $idx => $msg) {
        $num=$idx+1;
        echo "$num) {$msg}\n";
    }
} else echo "\n  NO LOG\n";
