<?php
require_once dirname(__DIR__)."/bootstrap.php";
echo "<p>".getBrowser("user")."</p>";
$uaData=parseUserAgent();
foreach ($uaData as $key => $value) {
	echo "<p><b>$key</b> : $value</p>";
}
