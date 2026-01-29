<?php
echo PHP_SAPI."/".php_sapi_name()."/".($argc??"NOARGC")."/".(defined("STDIN")?"STDIN":"NOSTDIN")."/".($_SERVER["HTTP_USER_AGENT"]??"NOUSERAGENT");
