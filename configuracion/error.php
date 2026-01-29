<?php
register_shutdown_function("fatalErrorHandler");
set_error_handler("errOrLogHandler");

function fatalErrorHandler() {
    try {
        $errortype = array (
                    E_ERROR              => 'Fatal Error',
                    E_WARNING            => 'Runtime Warning',
                    E_PARSE              => 'Compile-time Parse Error',
                    E_NOTICE             => 'Notice of potential problem',
                    E_CORE_ERROR         => 'Fatal Core Error', // during PHP's initial startup
                    E_CORE_WARNING       => 'Core Warning', // during PHP's initial startup
                    E_COMPILE_ERROR      => 'Fatal Compile-time Error',
                    E_COMPILE_WARNING    => 'Compile-time Warning',
                    E_USER_ERROR         => 'User-generated Fatal Error',
                    E_USER_WARNING       => 'User-Generated Warning',
                    E_USER_NOTICE        => 'User-Generated Notice',
                    //E_STRICT             => 'Runtime Notice',
                    E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
                    E_DEPRECATED         => 'Notice for Deprecated Feature',
                    E_USER_DEPRECATED    => 'User-generted Deprecation Warning'
                    );
        $error = error_get_last();
        if (isset($error)&&isset($error["type"])&&$error["type"]>0) {
            $errno = $error["type"];
            $errstr = $error["message"]??"";
            $errfile = $error["file"]??"";
            $errline = $error["line"]??0;
            if ($errno>0) {
                /*
                $ru=getrusage();
                $us=+$ru["ru_utime.tv_sec"]; // user time used (seconds)
                $ums=+$ru["ru_utime.tv_usec"]; // user time used (microseconds)
                $ss=+$ru["ru_stime.tv_sec"]; // system time used (seconds)
                $sms=+$ru["ru_stime.tv_usec"]; // system time used (microseconds)
                */
                // require_once dirname(__DIR__)."/configuracion/ meta . php";
                if (strpos($errfile, getBasePath())!==false) {
                    $errfile=str_replace(getBasePath(), "", $errfile);
                    $errstr=str_replace(getBasePath(), "", $errstr);
                }
                $message=($errno===E_ERROR?"FATAL ":"").mb_strtoupper($errortype[$errno]??"Unknown Error")." ($errno): $errstr () [$errfile - $errline]";
                global $query;
                if (isset($query[0])) $message.="\r\nLAST QUERY: $query";
                $message.="\r\nTRACE:".generateCallTrace(" * ", ".", 3, true);
                errlog($message);
            }
        }
    } catch (Error $e) {
        try {
            errlog("ERROR in FATAL ERROR: ".json_encode(getErrorData($e)));
        } catch (Error $e2) {
        }
    } finally {
        //require_once "configuracion/finalizacion.php";
    }
}
// If the error condition is E_USER_ERROR or above then abort
function errOrLogHandler ($errno, $errstr, $errfile, $errline, $errcontext=null) {
    $errortype = array (
                E_ERROR              => 'Fatal Error',
                E_WARNING            => 'Runtime Warning',
                E_PARSE              => 'Compile-time Parse Error',
                E_NOTICE             => 'Notice of potential problem',
                E_CORE_ERROR         => 'Fatal Core Error', // during PHP's initial startup
                E_CORE_WARNING       => 'Core Warning', // during PHP's initial startup
                E_COMPILE_ERROR      => 'Fatal Compile-time Error',
                E_COMPILE_WARNING    => 'Compile-time Warning',
                E_USER_ERROR         => 'User-generated Fatal Error',
                E_USER_WARNING       => 'User-Generated Warning',
                E_USER_NOTICE        => 'User-Generated Notice',
                //E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
                E_DEPRECATED         => 'Notice for Deprecated Feature',
                E_USER_DEPRECATED    => 'User-generted Deprecation Warning'
                );
    global $query;
    if (stripos($errstr, "sql") === 0) {
        // require_once "clases/ DBi . php";
        $MYSQL_ERRNO = DBi::getErrno();
        $MYSQL_ERROR = DBi::getError();
        $errstr .= " Error: $MYSQL_ERRNO : $MYSQL_ERROR";
    } else {
        $query = NULL;
    }
    // require_once dirname(__DIR__)."/configuracion/ meta . php";
    if (strpos($errfile, getBasePath())!==false) {
        $errfile=str_replace(getBasePath(), "", $errfile);
        $errstr=str_replace(getBasePath(), "", $errstr);
    }
    $message=($errortype[$errno]??"Unknown Error")." ($errno): $errstr [$errfile - $errline]";
    if (isset($query[0])) $message.="\r\nQUERY: $query";
    
    //if (substr($message,0,4)==="User") {
    if (!isset($GLOBALS["ignoreContextInErrors"]))
        $GLOBALS["ignoreContextInErrors"]=[
            "_cliIP",
            "_currScr",
            "_COOKIE", // ?
            "_config",
            "_doDB",
            "_doLogin",
            "_envPth",
            "_esAdministrador",
            "_esCompras",
            "_esComprasB",
            "_esDesarrollo",
            "_esProveedor",
            "_esPruebas",
            "_esSistemas",
            "_esSistemasX",
            "_include_path_exploded_array",
            "_lc",
            "_lctm",
            "_mon",
            "_now",
            "_php_self_exploded_array",
            "_project_name",
            "_pryNm",
            "_pryPth",
            "_REQUEST", // ?
            "_SERVER", // ?
            "_SESSION", // ?
            "_tzOld",
            "_tz",
            "_webPth",
            "GLOBALS",
            "app_config_filename",
            "app_config_path",
            "autoUploadPath",
            "autoUploadErrPath",
            "avance_clave",
            "avance_servidor",
            "avance_usuario",
            "bd_base",
            "bd_clave",
            "bd_replica",
            "bd_servidor",
            "bd_usuario",
            "bkgdExt",
            "bkgdImgName",
            "bkgdImg",
            "bkgdModD",
            "bkgdModL",
            "bkgdPth",
            "browser",
            "btnActualizacion",
            "btnAdmFact",
            "btnAdmin",
            "btnAltaEmpleados",
            "btnAltaPagos",
            "btnBitacora",
            "btnCajaChica",
            "btnCajaReporte",
            "btnCargaPagos",
            "btnCatalogo",
            "btnComparaProv",
            "btnComparaSatA",
            "btnComparaSatB",
            "btnComparaSatC",
            "btnComparaSatD",
            "btnConfig",
            "btnCorreos",
            "btnDscrgXML",
            "btnEntidades",
            "btnFormaPago",
            "btnNomina",
            "btnRespaldo",
            "btnViajero",
            "cajachicaPath",
            "calendarConfJsVersion",
            "calendarStyleCssVersion",
            "cfdiObj",
            "classet",
            "clearForm",
            "clockInitLine",
            "conMenuSolPago",
            "configClass",
            "consultaAlta",
            "consultaCajaChica",
            "consultaCajaReporte",
            "consultaCata",
            "consultaCitas",
            "consultaConR",
            "consultaData",
            "consultaEmpl",
            "consultaExpr",
            "consultaGrpo",
            "consultaNomi",
            "consultaPerm",
            "consultaProc",
            "consultaProv",
            "consultaRepo",
            "consultaResp",
            "consultaUsrs",
            "consultaViaticos",
            "controlAction",
            "cptObj",
            "cpyObj",
            "ctfObj",
            "ctrObj",
            "currentTimeZone",
            "customSwitch",
            "datePickerJsVersion",
            "deshabilitarAlta",
            "dpyObj",
            "enableHiddenMenu",
            "epsilon",
            "errorExceptionMethods",
            "errorTitle",
            "esAdmin",
            "esAdministrador",
            "esAltaPagos",
            "esAuthPago",
            "esCargaEgresos",
            "esComparaClientes",
            "esComparaProveedores",
            "esCompras",
            "esControlContraRecibos",
            "esCuentasBancarias",
            "esDesarrollo",
            "esDiseno",
            "esGestionaPago",
            "esGestor",
            "esOrigenContraRecibos",
            "esProveedor",
            "esPruebas",
            "esRealizaPago",
            "esSistemas",
            "esSolPago",
            "esSuperAdmin",
            "facturas",
            "ftp_avausr",
            "ftp_avapwd",
            "ftp_clave",
            "ftp_exportPath",
            "ftp_factpass",
            "ftp_factserv",
            "ftp_factuser",
            "ftp_policyPath",
            "ftp_servidor",
            "ftp_supportPath",
            "ftp_usuario",
            "ftpObj",
            "ftpsrv_clave",
            "ftpsrv_servidor",
            "ftpsrv_usuario",
            "generaCitas",
            "generalJsVersion",
            "gpoCodigoOpt",
            "gpoData",
            "gpoRazSocOpt",
            "gpoIdOpt",
            "gpoObj",
            "gpoRFC2Id",
            "gpoRFCOpt",
            "habilitado",
            "hasConfigAction",
            "hasErrorMessage",
            "hasMessage",
            "hasOnload",
            "hasOnloadScript",
            "hasResultMessage",
            "hasScriptAction",
            "hasStyleAction",
            "hasUser",
            "ignoreContextInErrors",
            "ignoreEmptyContextErrors",
            "ignoreTmpList",
            "infObj",
            "invData",
            "invObj",
            "isIgnore",
            "isMSIE",
            "isSeasonTime",
            "isSingleResult",
            "isVIP",
            "lpLen",
            "lstPfx",
            "mail_alias",
            "mail_clave",
            "mail_debug",
            "mail_monitor",
            "mail_puerto",
            "mail_seguridad",
            "mail_servidor",
            "mail_usuario",
            "masterConpro",
            "maxXML",
            "menuList",
            "message",
            "meta_clog1_seq",
            "metainitime",
            "mnuObj",
            "modificaCata",
            "modificaCitas",
            "modificaConR",
            "modificaData",
            "modificaEmpl",
            "modificaGrpo",
            "modificaNomi",
            "modificaPerm",
            "modificaProv",
            "modificaRepo",
            "modificaUsrs",
            "modoActualizacion",
            "modoPruebas",
            "mylocale",
            "navIdx",
            "noCCPData",
            "ordObj",
            "otherScriptSrc",
            "otherStyleHref",
            "pdfObj",
            "perObj",
            "polyfillVersion",
            "postFix",
            "prcObj",
            "procObj",
            "procSwitch",
            "prvCodigo2Id",
            "prvCodigoOpt",
            "prvData",
            "prvIdOpt",
            "prvObj",
            "prvRazSocOpt",
            "prvRFCOpt",
            "pyObj",
            "rarObj",
            "rccObj",
            "remObj",
            "rviObj",
            "rvcObj",
            "resultMessage",
            "resultTitle",
            "rules",
            "scriptActionLine",
            "season",
            "seasonNoList",
            "sinMenuSolPago",
            "solObj",
            "sqlsrv_base",
            "sqlsrv_dsn",
            "sqlsrv_name",
            "sqlsrv_password",
            "sqlsrv_username",
            "styleActionLine",
            "submitted_username",
            "sysPath",
            "systemTitle",
            "templateAction",
            "testEnv",
            "testVar",
            "tieneEmpleados",
            "tieneReposicion",
            "title",
            "token",
            "tokObj",
            "tracelog",
            "ugObj",
            "urlAction",
            "user",
            "usrObj",
            "usrPerfiles",
            "usrPrfTitle",
            //"validBrowser",
            "VIPLIST",
            "waitExt",
            "waitImgName",
            "waitImg",
            "waitPth"];
    if (!isset($GLOBALS["ignoreEmptyContextErrors"]))
        $GLOBALS["ignoreEmptyContextErrors"]=["_GET","_POST","_COOKIE","_FILES","errMessage","errorMessage","isSeasonTime","query","waitClass","where"];
    try {
        DBi::rollback();
        DBi::autocommit(TRUE);
        $errmsg="";
    } catch (Error $e) {
        $errmsg=json_encode(getErrorData($e));
    }
    if (isset($errcontext) && $errno!==E_USER_ERROR) {
        if (is_string($errcontext)) $message.="\r\nCONTEXT: '$errcontext'";
        else if (is_array($errcontext) || is_object($errcontext)) {
            foreach ($errcontext as $key => $value) {
                if (in_array($key, $GLOBALS["ignoreContextInErrors"])) continue;
                if (isset($GLOBALS["ignoreTmpList"]) && is_array($GLOBALS["ignoreTmpList"]) && in_array($key, $GLOBALS["ignoreTmpList"])) continue;
                if (in_array($key, $GLOBALS["ignoreEmptyContextErrors"]) && empty($value)) continue;
                $message.="\r\nCONTEXT $key: ";
                if (is_array($value))
                    $message.=str_replace("\n", "\\n", json_encode($value)); // implode(",", $value);
                else if (is_object($value)) {
                    if ($value instanceof DateTime) $message.=$value->format("Y-m-d H:i:s");
                    else $message.=str_replace("\n", "\\n", json_encode($value));
                } else if (is_string($value)) $message.="'".str_replace("\n", "\\n", $value)."'";
                else if (is_bool($value)) $message.=($value?"true":"false");
                else $message.=str_replace("\n", "\\n", strval($value));
            }
        }
    }
    if (!isset($message)) $message="";
    if (isset($errmsg[0])) $message.="\r\nRESET ERROR: $errmsg";
    errlog($message);
    /*
    if (in_array($errno, [E_USER_ERROR,E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])) {
        require_once dirname(__DIR__)."/bootstrap.php";
        $systemTitle = "Gesti&oacute;n de Facturas Electr&oacute;nicas del Corporativo";
        $errorDetail = "<p><b>El portal generó un error $errno, por favor consulte al administrador.</b></p>";
        $errorTrace = " $_SERVER[PHP_SELF]".PHP_EOL;
        $trace = debug_backtrace();
        $errorTrace .= arr2str($trace, " * ", "")."<HR>".generateCallTrace();
        include "templates/error.php";
        errlog("ERROR TRACE: $errorTrace");
        die();
    } else {

    }
    */
}
function errorHandler ($errno, $errstr, $errfile, $errline, $errcontext=null) {
    if (substr($errfile,-9)==="Trace.php") return;
    if (substr($errfile,-7)==="FTP.php") {
        $mensaje = "<h3>PARAMETROS</h3><ul><li><b>ERRNO:</b> $errno (";
        switch ($errno) {
            case E_USER_WARNING: $mensaje.="E_USER_WARNING"; break;
            case E_USER_NOTICE: $mensaje.="E_USER_NOTICE"; break;
            case E_WARNING: $mensaje.="E_WARNING"; break;
            case E_NOTICE: $mensaje.="E_NOTICE"; break;
            case E_CORE_WARNING: $mensaje.="E_CORE_WARNING"; break;
            case E_COMPILE_WARNING: $mensaje.="E_COMPILE_WARNING"; break;
            case E_USER_ERROR: $mensaje.="E_USER_ERROR"; break;
            case E_ERROR: $mensaje.="E_ERROR"; break;
            case E_PARSE: $mensaje.="E_PARSE"; break;
            case E_CORE_ERROR: $mensaje.="E_CORE_ERROR"; break;
            case E_COMPILE_ERROR: $mensaje.="E_COMPILE_ERROR"; break;
            default: $mensaje.="ERROR UNKNOWN"; break;
        }
        // require_once dirname(__DIR__)."/configuracion/ meta . php";
        if (strpos($errfile, getBasePath())!==false) {
            $errfile=str_replace(getBasePath(), "", $errfile);
            $errstr=str_replace(getBasePath(), "", $errstr);
        }
        $mensaje.=")</li><li><b>ERRSTR:</b> $errstr</li><li><b>ERRFILE:</b> $errfile</li><li><b>ERRLINE:</b> $errline</li></ul>";
        if (!empty($errcontext)) {
            $mensaje.="<h3>CONTEXTO</h3>";
            if (is_array($errcontext))       $mensaje.=arr2List($errcontext);
            else if (is_string($errcontext)) $mensaje.="<p>$errcontext</p>";
            else $mensaje.="<pre><code><xmp>".json_encode($errcontext)."</xmp></code></pre>";
        }
        /*
        $trace = debug_backtrace();
        if (!empty($trace)) {
            $mensaje.="<HR><p><b>BACKTRACE</b></p>";
            if (is_array($trace))       $mensaje.=arr2List($trace);
            else if (is_string($trace)) $mensaje.="<p>$trace</p>";
            else $mensaje.="<pre><code><xmp>".json_encode($trace)."</xmp></code></pre>";
        }
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());
        $trace = array_reverse($trace); // reverse array to make steps line up chronologically
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $mensaje.="<HR><p><b>CALLTRACE</b></p><UL>";
        for ($i = 0; $i < $length; $i++) {
            $mensaje.= "<LI>".($i + 1).')'.$trace[$i]."</LI>"; // replace '#someNum' with '$i)', set the right ordering
        }
        $mensaje.="</UL>";
        */
        require_once "clases/FTP.php";
        echo PHP_EOL."<!-- START --><h1 class=\"centered\" id=\"detailErrorTitle\">Captura de Error</h1><div class=\"centered scrollauto\" id=\"resptestlist\"><table class=\"centered\"><tr><td class=\"lefted\">$mensaje</td></tr></table></div><!--\n".MIFTP::log()."\n--><!-- END -->".PHP_EOL;
        return;
    }
    echo PHP_EOL."<!-- $errno, $errstr, $errfile, $errline, ".(is_array($errcontext)?json_encode($errcontext):$errcontext)." -->".PHP_EOL.generateCallTrace("<!--", "-->".PHP_EOL, 3, true);
    switch ($errno) {
        case E_USER_WARNING:
        case E_USER_NOTICE:
        case E_WARNING:
        case E_NOTICE:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
            break;
        case E_USER_ERROR:
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
            
            global $query;
            
            // sessionInit();
            
            // if (eregi('^(sql)$', $errstr)) { // deprecated
            //if (preg_match("/^(sql)/i", $errstr)) { // removed strict "SQL" string, keeps start with condition
            if (stripos($errstr, "sql") === 0) { // stripos is faster than preg_match
                $MYSQL_ERRNO = DBi::getErrno(); // mysqli_errno();
                $MYSQL_ERROR = DBi::getError(); // mysqli_error();

                $errstr .= " Error: $MYSQL_ERRNO : $MYSQL_ERROR";
            } else {
                $query = NULL;
            } // if
            
            require_once dirname(__DIR__)."/bootstrap.php";
            $systemTitle = "Gesti&oacute;n de Facturas Electr&oacute;nicas del Corporativo";

            $errorDetail = "<h2>Temporalmente el sistema no est&aacute; disponible. Intente nuevamente o consulte a su administrador.</h2>".PHP_EOL;
            $errorTrace  = "\n -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                         ERROR                         --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " $errstr".PHP_EOL.PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                        ERROR NUM                      --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " (# $errno)".PHP_EOL.PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                          LINE                         --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " $errline".PHP_EOL.PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                          FILE                         --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " $errfile".PHP_EOL.PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                         SCRIPT                        --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " $_SERVER[PHP_SELF]".PHP_EOL.PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                       BACK TRACE                      --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $trace = debug_backtrace();
            $errorTrace .= arr2str($trace, " * ", "");
            $errorTrace .= "".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= " --                       CALL TRACE                      --".PHP_EOL;
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            $errorTrace .= generateCallTrace();
            $errorTrace .= " -----------------------------------------------------------".PHP_EOL;
            include "templates/error.php";

            // Stop the system
            // session_unset();
            // session_destroy();
            die();
        default:
            if ($errno)
              echo "Generic Error $errno: $errstr \n error on line $errline in file $errfile ".PHP_EOL;
            else
              echo "Generic Unknown Error: $errstr \n error on line $errline in file $errfile ".PHP_EOL;
            break;
    } // switch
} // errorHandler
function generateArrayTrace($pops=1, $doReverse=true) {
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    if ($doReverse) $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    for(;isset($trace[0])&&$pops>0;$pops--) array_pop($trace); // remove call to this method
    return $trace;
}
function generateCallTrace($prefix="\t", $suffix=PHP_EOL, $pops=1, $doReverse=true) {
    $trace=generateArrayTrace($pops, $doReverse);
    $length = count($trace);
    $result = "";
    for ($i = 0; $i < $length; $i++) {
        $result .= $prefix . ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')) . $suffix; // replace '#someNum' with '$i)', set the right ordering
    }
    return $result;
}
global $errorExceptionMethods;
$errorExceptionMethods=["code"=>"getCode","file"=>"getFile","line"=>"getLine","message"=>"getMessage","trace"=>"getTraceAsString"];
function getErrorData($ex,$ignoreKeys=null) {
    if (!isset($ex)) return null;
    if (!is_object($ex)) return $ex;
    if (!isset($ignoreKeys)) $ignoreKeys=[]; // "class","code","file","line","message","trace"
    else if (is_scalar($ignoreKeys)) $ignoreKeys=[$ignoreKeys];
    $errData=[];
    if (!in_array("class", $ignoreKeys)) $errData["class"]=get_class($ex);
    global $errorExceptionMethods;
    foreach ($errorExceptionMethods as $codeKey => $methodName) {
        if (method_exists($ex,$methodName) && !in_array($codeKey, $ignoreKeys)) {
            $errData[$codeKey]=$ex->$methodName();
            if ($codeKey==="trace") $errData[$codeKey]=fixTraceString($errData[$codeKey]);
        } else $errData[$codeKey]="IGNORED";
    }
    return $errData;
}
function fixTraceString($str) {
    $invIdx=strpos($str, "invoice");
    if ($invIdx!==false) {
        $atIdx=strrpos($str, "#", $invIdx-strlen($str));
        if ($atIdx!==false) $str=substr($str, $atIdx);
    }
    return $str;
}
