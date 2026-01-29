<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "SCRIPT_NAME = '$_SERVER[SCRIPT_NAME]'\n";
echo "FILE = '".__FILE__."'\n";
if (isset($_SERVER['argc'])) $_project_name="invoice";
echo "PROJECT_NAME = '".($_project_name??'none')."'\n";

require_once dirname(__DIR__)."/bootstrap.php";
if (isset($_SERVER['argc'])) getBasePath("C:/Apache24/htdocs/invoice/");
global $mail_usuario, $mail_seguridad, $mail_puerto;
$dt = new DateTime();
$timestamp = $dt->format("y/m/d H:i:s");
if (sendMail("Prueba de Correo","Envío de correo de prueba",null,["address"=>"desarrollo@glama.com.mx","name"=>"Carlos Aguirre"],null,null,["domain"=>"foamymex"])) {// ,null,null,["usuario"=>$mail_usuario,"seguridad"=>$mail_seguridad,"puerto"=>$mail_puerto] // ,"domain"=>"skarton"
    echo "$timestamp CORREO DE PRUEBA EXITOSO";
    addMailHourCount("prueba",2);
} else echo "$timestamp CORREO DE PRUEBA FALLIDO";
/*    
require_once "clases/Correo.php";
$mail=new Correo();
$mail->restart();
$asunto="Correo de Prueba 2";
$mailDesc="'$asunto'";
//global $mail_usuario, $mail_alias;
//$mail->setFrom($mail_usuario, $mail_alias);
//$mail->setFrom("desarrollo@glama.com.mx","Desarrollo Sistemas", false);
$mail->addReplyTo("desarrollo@glama.com.mx","Desarrollo Sistemas");
//$mail->addAddress("mlobaton@apsa.com.mx","Marcos Lobaton Abadi");
$mail->setSubject("Correo de Prueba");
$mail->setBody("<HTML><body><U><B>C</B>orreo de prueba</U></body></HTML>");
$mail->setAltBody("Mensaje de prueba");
$mail->addMonitor();
echo "<H1>INFO</H1>";
$info = $mail->getInfo();
echo "\n<!-- INFO -->\n";
echo arr2List(str_replace(["\r\n","\n\r","\n","\r"],"<br>",$info));
echo "\n<!-- LIST OF INFO -->\n";
try {
    echo "\n<!-- PRESEND -->\n";
    if($mail->send()) echo "<br>Mensaje de Prueba para mi enviado por correo";
    else echo "<br>Error en Envío de correo: ".str_replace(["\r\n","\n\r","\n","\r"],"<br>",$mail->getErrorInfo());
    echo "\n<!-- AFTERSEND -->\n";
} catch (Exception $ex) {
    echo "<h1>EXCEPTION</h1>";
    echo arr2List(getErrorData($ex));
}
echo "\n<!-- PREERROR -->\n";
if(!empty($mail->error)) {
	echo "<hr><h1>ERRORS</h1><ul>";
	foreach ($mail->error as $errData) {
		echo "<li>".$errData[1]."(".$errData[0].") ".$errData[2]."<br>".$errData[3].", line ".$errData[4]."</li>";
	}
	echo "</ul>";
}
echo "\n<!-- PREDEBUG -->\n";
if(!empty($mail->debug)) {
	echo "<hr><h1>DEBUG</h1><ul>";
	foreach ($mail->debug as $dbgLvl=>$dbgStr) {
		echo "<li>".$dbgLvl." : ".str_replace("<", "&lt;", $dbgStr)."</li>";
	}
	echo "</ul>";
}
*/
