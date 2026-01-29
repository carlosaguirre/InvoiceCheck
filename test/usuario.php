<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (isset($_GET["perfil"][0])) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
    $result = $usrObj->getEmailByPerfil($_GET["perfil"]);
    echo "EMAILs $_GET[perfil] :<br>";
    echo json_encode($result);
    if (isset($_GET["log"])) {
        echo "<HR><PRE>".$usrObj->log."</PRE>";
    }
} else echo "OK";
