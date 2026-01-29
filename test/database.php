<?php
require_once dirname(__DIR__)."/bootstrap.php";

require_once "clases/Usuarios.php";
$usrObj=new Usuarios();
$usrData=$usrObj->getData("mid(nombre,2,1)!='-'",0,"id,nombre,persona,email");
?>
<pre>
<?php
//$pads=["id"=>["len"=>0,"str"=>" ","typ"=>STR_PAD_LEFT], "id"=>["len"=>0,"str"=>" ","typ"=>STR_PAD_LEFT]];
foreach ($usrData as $usr) {
    //if (!isset())
    echo " * ".str_pad($usr[id],5," ",STR_PAD_LEFT)." - ".str_pad($usr[nombre],12," ",STR_PAD_LEFT)." - $usr[persona] - $usr[email]\n";
}
?>
</pre>
<?php
echo "<hr><pre>".$usrObj->log."</pre>";
