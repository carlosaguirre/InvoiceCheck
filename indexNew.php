<?php
require_once "bootstrap2.php";
echo "<H1>SERVER</H1>";
$skeys=["DOCUMENT_ROOT","APP_POOL_ID","INSTANCE_NAME","APPL_PHYSICAL_PATH","SCRIPT_FILENAME","PATH_TRANSLATED","SERVER_PROTOCOL","REQUEST_SCHEME","SERVER_PORT_SECURE","HTTPS","HTTPS_SERVER_SUBJECT","SERVER_NAME","HTTP_HOST","SERVER_PORT","HTTP_ORIGIN","APPL_MD_PATH","CONTEXT_PREFIX","WEB_MD_PATH","REQUEST_URI","SCRIPT_NAME","PHP_SELF","LOCAL_ADDR","REMOTE_ADDR","REMOTE_PORT","HTTP_CLIENT_IP","HTTP_X_FORWARDED_FOR","HTTP_USER_AGENT","HTTP_REFERER","HTTP_ACCEPT_LANGUAGE","HTTP_ACCEPT_ENCODING","REQUEST_METHOD","REQUEST_TIME"];
foreach ($skeys as $idx => $key) {
	echo "<B>$key</B> = ".($_SERVER[$key]??"UNDEFINED")."<BR>";
}
echo "<HR>";
echo "<H1>CONSTANTS</H1>";
echo "<B>__DIR__</B> = ".__DIR__."<BR>";
echo "<HR>";
echo "<H1>BOOTSTRAP</H1>";
echo "<B>PROJECT NAME</B> = $_pryNm<BR>";
echo "<B>PROJECT NAME</B> = $_project_name<BR>";
echo "<B>ENV PATH</B> = $_envPth<BR>";
echo "<B>IP</B> = $_cliIP<br>";
echo "<B>SCRIPT</B> = $_currScr<br>";
echo "<B>OLD TIMEZONE</B> = $_tzOld<br>";
echo "<B>TIMEZONE</B> = $_tz<br>";
//echo "<B>LOCALE TIME LIST</B> = ".json_encode($_lctm)."<BR>";
//echo "<B>LOCALE</B> = $_lc<br>";
$now_display = is_array($_now)
    ? "[" . implode(', ', array_map(
        function($k,$v){ return $k.'='.(is_array($v)?json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):(is_bool($v)?($v?'true':'false'):(is_null($v)?'null':$v))); },
        array_keys($_now), $_now
      )) . "]"
    : (string)$_now;
echo "<B>NOW</B> = " . htmlspecialchars($now_display) . "<br>";
$dia=substr($_now["ymd"], 0, 6);
echo "<B>DIA</B> = " . $dia . "<br>";
$timestamp=substr($_now["now"], 11, 8);
echo "<B>HORA</B> = " . $timestamp . "<br>";
echo "<B>ES TERMINAL</B> = " . b2s(php_sapi_name()==='cli' || PHP_SAPI==="cli") ."<br>";
$basePath="";
if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $basePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
else if (!empty($_SERVER['DOCUMENT_ROOT'])) $basePath = $_SERVER['DOCUMENT_ROOT'];
$logPath = $basePath."LOGS".DIRECTORY_SEPARATOR;
echo "<B>LOG PATH</B> = " . $logPath . " : " . (is_dir($logPath)?"":"NOT ") . "FOUND!<br>";
$logPath = realpath($logPath).DIRECTORY_SEPARATOR;
echo "<B>REAL LOG PATH</B> = " . $logPath . " : " . (is_dir($logPath)?"":"NOT ") . "FOUND!<br>";
$logPath .= $dia.DIRECTORY_SEPARATOR;
if (!is_dir($logPath)) {
    try {
        mkdir($logPath, 0777, true);
        echo "<B>LOG PATH FIX1</B> = " . $logPath . " : " . (is_dir($logPath)?"":"NOT ") . "FOUND!<br>";
    } catch (\Throwable $e) {
        echo "<B>LOG PATH CREATE ERROR</B> = " . $logPath . " : " . $e->getMessage() . "<br>";
    }
}
//echo "<B>MON</B> = $_mon<br>";
//echo "<B>USER</B> = ".json_encode(getUser())."<br>";
echo "<B>BROWSER</B> = $_browser<br>";
echo "<B>DO LOGIN</B> = ".b2s($_doLogin)."<br>";
echo "<B>DO DB</B> = ".b2s($_doDB)."<br>";
echo "<B>HABILITADO</B> = ".b2s($habilitado)."<br>";
echo "<B>MODO ACTUALIZACION</B> = ".b2s($modoActualizacion)."<br>";
echo "<B>MODO PRUEBAS</B> = ".b2s($modoPruebas)."<br>";
echo "<B>EXISTS DBi</B> = ".b2s(file_exists($_webPth . "\clases\DBi.php"))."<br>";
//echo "<PRE>";
//print_r(Config::get([]));
//echo "</PRE>";
