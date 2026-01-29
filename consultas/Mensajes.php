<?php
if (isset($_REQUEST["msgkey"])) {
	$msgkey=strtoupper($_REQUEST["msgkey"]);
    require_once dirname(__DIR__)."/bootstrap.php";
	if (isset($_SESSION["MENSAJE_".$msgkey])) {
		echo "<div style=\"font-size: 18px;text-align:justify;padding-left:15px;padding-right:15px;\"><b>".$_SESSION["MENSAJE_$msgkey"]."</b></div>";
	}
} else if (isset($_REQUEST["jskey"])) {
    require_once dirname(__DIR__)."/bootstrap.php";
    $jk=strtoupper($_REQUEST["jskey"]);
    if (isset($_SESSION["JSON_{$_jk}"])) {
        $jmsg=$_SESSION["JSON_{$_jk}"];
        if (is_string($jmsg)) echo $jmsg;
        else echo json_encode($jmsg);
    }
} else if (isset($_REQUEST["dbkey"])) {
    require_once dirname(__DIR__)."/bootstrap.php";
    require_once "clases/InfoLocal.php";
    $infObj=new InfoLocal();
    $dk=strtoupper($_REQUEST["dbkey"]);
    if ($dk==="INI") {
        $msg=$infObj->getMsgIni();
        foreach ($msg as $line) {
            // agregar cada linea dentro de div o p
            // generar json_encode
        }
    }
}
