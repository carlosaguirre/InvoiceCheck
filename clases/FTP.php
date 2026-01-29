<?php
require_once dirname(__DIR__)."/bootstrap.php";

class MIFTP {
    public static $lastException = null;
    public static $nl = "\n";
    private static $_log = "";
    public $ftpAddress = null;
    public $ftpPort = 21;
    public $ftpUser = null;
    public $ftpExportPath = "";
    public $ftpBackupPath = "";
    public $ftpPolicyPath = "";
    private $conn_id = null;
    public static function log($texto=null) {
        if (isset($texto)) {
            if ($texto===false) MIFTP::$_log="";
            else if (!empty($texto)) {
                if (empty(MIFTP::$_log)) MIFTP::$_log=$texto;
                else MIFTP::$_log.=MIFTP::$nl.$texto;
            }
        }
        return MIFTP::$_log;
    }
    public static function newInstance($_ftp_address, $_ftp_user, $_ftp_pwd) {
        try {
            $obj = new MIFTP($_ftp_address, $_ftp_user, $_ftp_pwd);
            MIFTP::log("Objeto MIFTP creado satisfactoriamente");
            return $obj;
        } catch (Exception $e) {
            MIFTP::$lastException = $e; //getErrorData($e);
            MIFTP::log("Error en creación de objeto MIFTP: ".$e);
            //flog("FTP ERROR: En creacion de objeto: ".$e);
            return null;
        }
    }
    public static function newInstanceGlama() {
        global $ftp_servidor, $ftp_usuario, $ftp_clave, $ftp_exportPath, $ftp_supportPath, $ftp_policyPath;
        $obj = MIFTP::newInstance($ftp_servidor, $ftp_usuario, $ftp_clave);
        if (!is_null($obj)) {
            $obj->ftpExportPath=$ftp_exportPath;
            $obj->ftpBackupPath=$ftp_supportPath;
            $obj->ftpPolicyPath=$ftp_policyPath;
        }
        return $obj;
    }
    public static function newInstanceAvance() {
        global $ftp_servidor, $ftp_avausr, $ftp_avapwd, $ftp_exportPath, $ftp_supportPath, $ftp_policyPath;
        $obj = MIFTP::newInstance($ftp_servidor, $ftp_avausr, $ftp_avapwd);
        if (!is_null($obj)) {
            $obj->ftpExportPath=$ftp_exportPath;
            $obj->ftpBackupPath=$ftp_supportPath;
            $obj->ftpPolicyPath=$ftp_policyPath;
        }
        return $obj;
    }
    public static function newInstanceFacturas() {
        global $ftp_factserv, $ftp_factuser, $ftp_factpass;
        $obj = MIFTP::newInstance($ftp_factserv, $ftp_factuser, $ftp_factpass);
        return $obj;
    }
    public static function newInstanceFtpServ() {
        global $ftpsrv_servidor, $ftpsrv_usuario, $ftpsrv_clave;
        MIFTP::log("INI newInstanceFtpServ {$ftpsrv_servidor}@{$ftpsrv_usuario}:{$ftpsrv_clave}");
        $obj = MIFTP::newInstance($ftpsrv_servidor, $ftpsrv_usuario, $ftpsrv_clave);
        return $obj;
    }
    private function __construct($addr, $usr, $pwd) {
        $this->ftpAddress = $addr;
        $addrBlock = explode(":",$addr);
        if (isset($addrBlock[1])) {
            $port=+end($addrBlock);
            $addr=prev($addrBlock);
            $this->ftpPort=$port;
        } else $port=21;
        $this->ftpUser = $usr;
        $trackErrors = ini_get('track_errors');
        ini_set('track_errors', 1);
        $this->conn_id = @ftp_connect($addr, $port);
        if (false === $this->conn_id) {
            $errmsg=error_get_last();
            ini_set('track_errors', $trackErrors);
            throw new Exception("La conexión a '$addr:$port' con usuario '$usr' falló!: '{$errmsg}'");
        }
        MIFTP::log("Conexión a $addr:$port con usuario $usr satisfactoria!");
        $result = @ftp_login($this->conn_id, $usr, $pwd);
        if (true !== $result) {
            $errmsg=error_get_last();
            ini_set('track_errors', $trackErrors);
            throw new Exception("La autenticación a $addr:$port con usuario $usr falló!: '{$errmsg}'");
        }
        MIFTP::log("Autentificación a $addr:$port con usuario $usr satisfactoria!");
        ftp_pasv($this->conn_id, true);
        MIFTP::log("Modo pasivo");
        ini_set('track_errors', $trackErrors);
    }
    public function __destruct() {
        if (isset($this->conn_id)) {
            ftp_close($this->conn_id);
            MIFTP::log("Conexión a $this->ftpAddress:$this->ftpPort con usuario $this->ftpUser finalizada!");
        }
    }
    public function exportarTexto($fileName, $textContent) {
        doclog("FTP:exportarTexto","ftp",["fileName"=>$fileName,"contentLength"=>strlen($textContent),"exportPath"=>$this->ftpExportPath]);
        $tmpFile = fopen('php://memory','r+');
        fputs($tmpFile, $textContent);
        rewind($tmpFile);
        doclog("FTP:exportarTexto2","ftp",["fileName"=>$fileName,"exportPath"=>$this->ftpExportPath]);
        $this->cargarRutaAscii($this->ftpExportPath, $fileName, $tmpFile);
        fclose($tmpFile);
    }
    public function cargarRutaAscii($remotePath, $fileName, $fileResource) {
        doclog("FTP:cargarRutaAscii","ftp",["remotePath"=>$remotePath,"fileName"=>$fileName,"fileResource"=>$fileResource]);
        $remotePathScope=$remotePath;
        doclog("FTP:cargarRutaAscii prechmkdir","ftp",["remotePathScope"=>$remotePathScope]);
        $changedPath = $this->chmkdir($remotePathScope);
        MIFTP::log("Ruta remota actual es: ".ftp_pwd($this->conn_id));
        if ($changedPath) $this->cargarRecursoAscii($fileName, $fileResource);
        else throw new Exception("No fue posible cambiar la ruta: $remotePathScope");
    }
    public function cargarRecursoAscii($fileName, $fileResource) {
        $this->cargarRecurso($fileName, $fileResource, FTP_ASCII);
    }
    public function cargarRutaBinario($remotePath, $fileName, $fileResource) {
        doclog("FTP:cargarRutaBinario","ftp",["remotePath"=>$remotePath,"fileName"=>$fileName,"fileResource"=>$fileResource]);
        $remotePathScope=$remotePath;
        doclog("FTP:cargarRutaBinario prechmkdir","ftp",["remotePathScope"=>$remotePathScope]);
        $changedPath = $this->chmkdir($remotePathScope);
        MIFTP::log("Ruta remota actual es: ".ftp_pwd($this->conn_id));
        if ($changedPath) $this->cargarRecursoBinario($fileName, $fileResource);
        else throw new Exception("No fue posible cambiar la ruta: $remotePathScope");
    }
    public function cargarRecursoBinario($fileName, $fileResource) {
        $this->cargarRecurso($fileName, $fileResource, FTP_BINARY);
    }
    public function cargarRecurso($fileName, $fileResource, $fileType=FTP_ASCII) {
        if ($fileType!==FTP_ASCII && $fileType!==FTP_BINARY) {
            throw new Exception("Error en el tipo de archivo, solo puede ser FTP_ASCII o FTP_BINARY");
        }
        $trackErrors = ini_get('track_errors');
        ini_set('track_errors', 1);
        $success = @ftp_fput($this->conn_id, $fileName, $fileResource, $fileType);
        if (!$success) {
            $errmsg=error_get_last();
            ini_set('track_errors', $trackErrors);
            throw new Exception("Error al realizar carga de recurso $fileName: '{$errmsg}'");
        }
        $fileTypeStr = ($fileType==FTP_ASCII?"Ascii":"Binario");
        MIFTP::log("Carga de archivo $fileTypeStr $fileName satisfactoria!");
        ini_set('track_errors', $trackErrors);
    }
    public function cargarArchivoAscii($remotePath, $remoteFileName, $localFileName) {
        $this->cargarArchivo($remotePath, $remoteFileName, $localFileName, FTP_ASCII);
    }
    public function cargarArchivoBinario($remotePath, $remoteFileName, $localFileName) {
        $this->cargarArchivo($remotePath, $remoteFileName, $localFileName, FTP_BINARY);
    }
    public function cargarArchivo($remotePath, $remoteFileName, $localFileName, $fileType=FTP_ASCII) {
        if ($fileType!==FTP_ASCII && $fileType!==FTP_BINARY) {
            //flog("FTP ERROR: El tipo de archivo no es ASCII ni BINARIO");
            throw new Exception("Error en el tipo de archivo, solo puede ser FTP_ASCII o FTP_BINARY");
        }
        $tipoArchivo="DESCONOCIDO";
        if ($fileType===FTP_ASCII) $tipoArchivo="ASCII";
        else if ($fileType===FTP_BINARY) $tipoArchivo="BINARIO";
        if (isset($remotePath) && isset($remotePath[0]) && $remotePath!==".") {
            doclog("FTP:cargarArchivo prechmkdir","ftp",["remotePath"=>$remotePath]);
            $changedPath = $this->chmkdir($remotePath);
        }
        $trackErrors = ini_get('track_errors');
        ini_set('track_errors', 1);
        $success = @ftp_put($this->conn_id, $remoteFileName, $localFileName, $fileType);
        if (!$success) {
            //flog("FTP ERROR: Fallo ftp_put de '$remoteFileName' a '$localFileName'");
            $errmsg=error_get_last();
            ini_set('track_errors', $trackErrors);
            throw new Exception("Error al realizar carga de archivo $remotePath / $remoteFileName: '{$errmsg}'");
        }
        ini_set('track_errors', $trackErrors);
        //flog("FTP SUCCESS: ftp_put $tipoArchivo de '$remoteFileName' a '$localFileName'");
        MIFTP::log("Carga de archivo $tipoArchivo $remoteFileName satisfactoria!");
    }
    public function obtenerArchivo($remoteFileName, $localFileName, $fileType=FTP_ASCII) {
        if ($fileType!==FTP_ASCII && $fileType!==FTP_BINARY) throw new Exception("Error en el tipo de archivo, solo puede ser FTP_ASCII o FTP_BINARY");
        $tipoArchivo=$fileType===FTP_ASCII?"ASCII":"BINARIO";
        $trackErrors = ini_get("track_errors");
        ini_set("track_errors", 1);
        $success = @ftp_get($this->conn_id, $localFileName, $remoteFileName, $fileType);
        if (!$success) {
            $errmsg=error_get_last();
            ini_set("track_errors", $trackErrors);
            throw new Exception("Error al obtener archivo $tipoArchivo '$remoteFileName': '{$errmsg}'");
        }
        ini_set("track_errors", $trackErrors);
        MIFTP::log("Obtencion de archivo $tipoArchivo '$remoteFileName' en '$localFileName' satisfactoria!");
    }
    public function moverArchivo($originalFilePath, $newFilePath, $overwrite=false) {
        doclog("FTP:moverArchivo","ftp",["oldpath"=>$originalFilePath, "newpath"=>$newFilePath, "overwrite"=>($overwrite?"TRUE":"FALSE")]);
        $newsize=ftp_size($this->conn_id, $newFilePath);
        $orisize=ftp_size($this->conn_id, $originalFilePath);
        if ($newsize>0) {
            if (!$overwrite)
                throw new Exception("Error al renombrar/mover archivo '{$originalFilePath}'. Ya existe un archivo con el nombre '{$newFilePath}'");
            if ($newsize!==$orisize) {
                // ToDo: Manipular nombre para agregar contador:
                //        - Quitar extension
                //        - Si termina en '_<num>', obtener num, incrementarlo en 1 y quitar desde '_'
                //        - Crear nuevo nombre con '_', nuevo num y extension
                //        - Renombrar archivo newFilePath a fixFilePath
            } // else // si se renombra ya no hay que borrarlo
            if (!ftp_delete($this->conn_id, $newFilePath)) {
                doclog("Failed ftp_delete","ftp",["file"=>$newFilePath]);
            }
        }
        if (!ftp_rename($this->conn_id, $originalFilePath, $newFilePath)) {
            doclog("Failed ftp_rename","ftp", ["orifile"=>$originalFilePath, "orisize"=>$orisize, "newfile"=>$newFilePath, "newsize"=>$newsize]);
            throw new Exception("Error al renombrar/mover archivo '{$originalFilePath}'. No fue posible mover el archivo '{$newFilePath}'");
        } else doclog("Successful ftp_rename","ftp", ["orifile"=>$originalFilePath, "orisize"=>$orisize, "newfile"=>$newFilePath, "newsize"=>$newsize]);
    }
    public function borrarArchivo($remoteFileName) {
        return ftp_delete($this->conn_id, $remoteFileName);
    }
    public function pwd() {
        return ftp_pwd($this->conn_id);
    }
    public function chdir($path) {
        doclog("FTP:chdir","ftp",["path"=>$path]);
        if (isset($path) && is_string($path)) return ftp_chdir($this->conn_id, $path);
        return false;
    }
    public function chmkdir($remotePath) {
        doclog("FTP:chmkdir","ftp",["remotePath"=>$remotePath]);
        $trackErrors = ini_get('track_errors');
        ini_set('track_errors', 1);
        $changedPath = @ftp_chdir($this->conn_id, $remotePath);
        if (!$changedPath) {
            if(@ftp_mkdir($this->conn_id, $remotePath)) {
                $changedPath = @ftp_chdir($this->conn_id, $remotePath);
                if (!$changedPath) {
                    //flog("FTP ERROR: Fallo el cambio de ruta '$remotePath'");
                    $errmsg=error_get_last();
                    ini_set('track_errors', $trackErrors);
                    throw new Exception("No fue posible cambiar la ruta: $remotePath: '{$errmsg}'");
                }
            } else {
                //flog("FTP ERROR: Fallo la creacion de ruta '$remotePath'");
                $errmsg=error_get_last();
                ini_set('track_errors', $trackErrors);
                throw new Exception("No fue posible crear la ruta: $remotePath: '{$errmsg}'");
            }
        }
        ini_set('track_errors', $trackErrors);
        return $changedPath;
    }
    public function list($path=".", $complexity=null, $isRecursive=true) {
        if (empty($path)) $path=".";
        if (empty($complexity)) return ftp_nlist($this->conn_id, $path);
        if ($complexity==="array") return ftp_mlsd($this->conn_id, $path);
        return ftp_rawlist($this->conn_id, $path, $isRecursive);
    }
    public function systype() {
        return ftp_systype($this->conn_id);
    }
    public function size($path) {
        return ftp_size($this->conn_id, $path);
    }
    public function permiso($permissions, $filename) {
        //return ftp_chmod($this->conn_id, (int)octdec(str_pad("$permissions",4,"0",STR_PAD_LEFT)), $filename);
        return ftp_site($this->conn_id, sprintf("CHMOD %o %s", $permissions, $filename));
    }
}
/*
if (isset($_GET["test"]) && $_GET["test"]==="test") {
    MIFTP::$nl = "<br>";
    $obj = MIFTP::newInstanceGlama();
    if ($obj==null) echo MIFTP::$lastException;
    $filename = "ftpTest.txt";
    $content = "Prueba de Objeto MIFTP\nLa existencia de este archivo en el servidor remoto implica el éxito de esta prueba.\nRevisar log para corroborar que la conexión haya cerrado correctamente.";
    try {
        $obj->exportarTexto($filename, $content);
    } catch (Exception $e) {
        echo $e;
    }
    echo "LOG:\n".MIFTP::log();
}
*/
