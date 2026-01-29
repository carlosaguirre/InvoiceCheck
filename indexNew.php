<?php
header('charset=UTF-8');
require_once "bootstrap.php";
//echo "<H1>SERVER</H1>";
//$skeys=["DOCUMENT_ROOT","APP_POOL_ID","INSTANCE_NAME","APPL_PHYSICAL_PATH","SCRIPT_FILENAME","PATH_TRANSLATED","SERVER_PROTOCOL","REQUEST_SCHEME","SERVER_PORT_SECURE","HTTPS","HTTPS_SERVER_SUBJECT","SERVER_NAME","HTTP_HOST","SERVER_PORT","HTTP_ORIGIN","APPL_MD_PATH","CONTEXT_PREFIX","WEB_MD_PATH","REQUEST_URI","SCRIPT_NAME","PHP_SELF","LOCAL_ADDR","REMOTE_ADDR","REMOTE_PORT","HTTP_CLIENT_IP","HTTP_X_FORWARDED_FOR","HTTP_USER_AGENT","HTTP_REFERER","HTTP_ACCEPT_LANGUAGE","HTTP_ACCEPT_ENCODING","REQUEST_METHOD","REQUEST_TIME"];
//foreach ($skeys as $idx => $key) {
//	echo "<B>$key</B> = ".($_SERVER[$key]??"UNDEFINED")."<BR>";
//}
echo "<HR>";
echo "<H1>CONSTANTS</H1>";
echo "<B>__DIR__</B> = ".__DIR__."<BR>";
echo "<HR>";
//echo "<H1>BOOTSTRAP</H1>";
//echo "<B>PROJECT NAME</B> = $_pryNm<BR>";
//echo "<B>IP</B> = $_cliIP<br>";
//echo "<B>SCRIPT</B> = $_currScr<br>";
//echo "<B>OLD TIMEZONE</B> = $_tzOld<br>";
//echo "<B>TIMEZONE</B> = $_tz<br>";
//echo "<B>LOCALE TIME LIST</B> = ".json_encode($_lctm)."<BR>";
//echo "<B>LOCALE</B> = $_lc<br>";
//echo "<B>NOW</B> = $_now<br>";
//echo "<B>MON</B> = $_mon<br>";
//echo "<B>USER</B> = ".json_encode(getUser())."<br>";
//echo "<PRE>";
//print_r(Config::get([]));
//echo "</PRE>";
