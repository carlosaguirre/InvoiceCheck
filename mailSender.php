<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 'on');
$reqAction = "POST";

function reqVal($param) {
    global $reqAction;
    switch($reqAction) {
        case "GET": if (isset($_GET[$param])) return $_GET[$param]; break;
        case "POST": if (isset($_POST[$param])) return $_POST[$param]; break;
        case "REQUEST": if (isset($_REQUEST[$param])) return $_REQUEST[$param]; break;
        default: return false;
    }
    return false;
}
if (isset($_REQUEST["changeAction"])) $reqAction=$_REQUEST["changeAction"];


header("Content-Type: text/plain");

$test = reqVal("test");
if ($test!==false) {
    // echo "Prueba finalizada";
    exit("Prueba finalizada");
}

$reqValAddress = reqVal("address");
if (!empty($reqValAddress))
    $addressVal  = explode(",",$reqValAddress);

$reqValName = reqVal("addressName");
if (!empty($reqValName))
    $addressName = explode(",",$reqValName);

$reqValUsers = reqVal("users");
if (!empty($reqValUsers)) {
    $addressUsers = explode(",",$reqValUsers);
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
    $emailPipeList = $usrObj->getValue("nombre", $addressUsers, "email", false, false, true);
    $personaPipeList = $usrObj->getValue("nombre", $addressUsers, "persona", false, false, true);
    if (!empty($emailPipeList)) {
        if (empty($addressVal)) {
            $addressVal  = explode("|",$emailPipeList  );
            $addressName = explode("|",$personaPipeList);
        } else {
            $addressVal  = array_merge($addressVal , explode("|",$emailPipeList  ));
            $addressName = array_merge($addressName, explode("|",$personaPipeList));
        }
    }
}

$reqValTodos = reqVal("todos");
if(!empty($reqValTodos)) {
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
    }
    $emailPipeList = $usrObj->getValue(false, false, "email", false, false, true);
    $personaPipeList = $usrObj->getValue(false, false, "persona", false, false, true);
    if (!empty($emailPipeList)) {
        if (empty($addressVal)) {
            $addressVal  = explode("|",$emailPipeList  );
            $addressName = explode("|",$personaPipeList);
        } else {
            $addressVal  = array_merge($addressVal , explode("|",$emailPipeList  ));
            $addressName = array_merge($addressName, explode("|",$personaPipeList));
        }
    }
}
$reqValPerfil = reqVal("usrperfil");
if(!empty($reqValPerfil)) {
    $usrPerfiles = explode(",",$reqValPerfil);
    require_once "clases/Perfiles.php";
    $prfObj = new Perfiles();
    $idPerfilPipeList = $prfObj->getValue("nombre",$usrPerfiles,"id",false,false,true);
    require_once "clases/Usuarios_Perfiles.php";
    $upObj = new Usuarios_Perfiles();
    $idUPPipeList = $upObj->getValue("idPerfil",explode("|",$idPerfilPipeList),"idUsuario",false,false,true);
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
    }
    $addressUserIds = explode("|",$idUPPipeList);
    $emailPipeList = $usrObj->getValue("id", $addressUserIds, "email", false, false, true);
    $personaPipeList = $usrObj->getValue("id", $addressUserIds, "persona", false, false, true);
    if (!empty($emailPipeList)) {
        if (empty($addressVal)) {
            $addressVal  = explode("|",$emailPipeList  );
            $addressName = explode("|",$personaPipeList);
        } else {
            $addressVal  = array_merge($addressVal , explode("|",$emailPipeList  ));
            $addressName = array_merge($addressName, explode("|",$personaPipeList));
        }
    }
}
//$reqValCompras = reqVal("usrcompras");

$subject = reqVal("subject");
$body = reqVal("body");
$altbody = reqVal("altbody");
$bodycode = reqVal("bodycode");
if (!empty($bodycode)) {
    switch($bodycode) {
        case "Aceptado":
            $estadoFactura = "aceptada";
        case "Rechazado":
            $nombreFactura = reqVal("nombrefactura");
            if (empty($nombreFactura)) die("Error: Debe indicar el nombre original de la factura [nombrefactura]");
            $folioFactura = reqVal("foliofactura");
            if (empty($folioFactura)) die("Error: Debe indicar el folio de la factura [foliofactura]");
            if (!isset($estadoFactura)) $estadoFactura = "rechazada";
            $subject="Factura ".ucfirst($estadoFactura);
            $body="La factura $nombreFactura con folio $folioFactura ha sido $estadoFactura";
            break;
        case "Portal":
            $body="Le informamos que a partir del 1o de Octubre la validación de facturas cambia de dirección al siguiente enlace: http://www.glama.com.mx/invoice/";
            $subject="Cambio de Portal para Validación de Facturas";
            break;
        case "Provisional":
            $body="<p>Le informamos que el <b>Nuevo Portal de Validación de Facturas</b> <u>aún no está disponible de forma pública.</u></p><p>Le solicitamos que provisionalmente envíe por correo electrónico a su contacto de nuestra <b>Área de Compras</b> los archivos <b>XML</b> de sus facturas, mencionando este correo para que sean dados de alta.</p><p>Alternativamente, el portal <a href='http://invoicesafemx.com.mx'>Invoice Safe Mx</a> seguirá activo aunque tendría que realizar el pago de mensualidad correspondiente.</p><p>Agradecemos su comprensión y a la brevedad le informaremos cuando el nuevo portal esté disponible.</p><p>Saludos Cordiales.</p><br><br><p>Área de Sistemas</p><p>Tel. (55)56992444 ext.264/239/221</p><p>Productos Glama, S.A. de C.V.</p><p>Acabados de Papeles Satinados y Absorbentes, S.A. de C.V.</p><p>Láminas Acanaladas Infinita, S.A. de C.V.</p><p>Laminados Casablanca, S.A. de C.V.</p><p>Envases Eficientes, S.A. de C.V.</p><p>Manufacturera de Papel Bidasoa, S.A. de C.V.</p>";
            $subject="Informacion sobre el nuevo portal de facturación";
            break;
        case "Navidad":
            $body="<div style=\"font-size: 18px;text-align:justify;padding-left:15px;padding-right:15px;color: #008;font-size: 14px;background-image: url(http://globaltycloud.com.mx:81/invoice/imagenes/fondos/navidad/fondo1.jpg);background-repeat: repeat;\"><b><p>Estimados Proveedores y Socios comerciales:</p>".
                  "<p>Ha sido un placer para nosotros contar con su servicio durante este año y por esta razón les expresamos nuestro más sincero agradecimiento.</p>".
                  "<p>Así mismo les informamos que por motivo del cierre fiscal de este año y las fiestas decembrinas recibiremos sus facturas hasta el dia 23 de diciembre del 2016.</p>".
                  "<p>Los pedidos enviados a ustedes antes de la fecha mencionada deberán ser facturados y subidos al portal hasta la fecha límite.</p>".
                  "<p>Pedidos posteriores a esta fecha o que no hayan subido al portal por alguna situación deberán ser facturados con fecha de Enero del 2017.</p>".
                  "<p>Nos despedimos de ustedes no sin antes desearles una vez más una feliz Navidad y un próspero año nuevo 2017.</p>".
                  "</b></div>";
            $subject="Aviso de Fin de Año";
            break;
    }
}
if (empty($subject)) {
    // echo "Error: Debe indicar un título de correo [subject].";
    die("Error: Debe indicar un título de correo [subject].");
}
if (empty($body)) {
    //echo "Error: Debe indicar contenido de correo [body/bodycode].";
    die("Error: Debe indicar contenido de correo [body/bodycode].");
}
if (empty($altbody)) {
    $altbody=$body;
}

require_once "vendor/autoload.php";
$referenceUrl = "https://www.sitepoint.com/sending-emails-php-phpmailer/";
$troubleshootingUrl = "https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting/";

$mail = new PHPMailer;

//Enable SMTP debugging.
$mail->SMTPDebug = 0;

$mail->CharSet = 'UTF-8';
//Set PHPMailer to use SMTP.
$mail->isSMTP();
//Set SMTP host name
$mail->Host = "mail.glama.com.mx";
//Set this to true if SMTP host requires authentication to send email
$mail->SMTPAuth = true;
//Provide username and password
$mail->Username = "iso9000@glama.com.mx";
$mail->Password = "Plantaiso2013";
//If SMTP requires TLS encryption then set it
$mail->SMTPSecure = "tls"; // "ssl"; // 
//$mail->SMTPSecure = false;
//Set TCP port to connect to
$mail->Port = 587; // 26
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ));
$mail->Sender = "iso9000@glama.com.mx";
$mail->From = "iso9000@glama.com.mx";
$mail->FromName = "ISO 9000";

$hasAddedAddress=false;
for($i=0; isset($addressVal[$i]); $i++) {
    if (!empty($addressVal[$i])) {
        $hasAddedAddress=true;
        if (empty($addressName[$i])) $addressName[$i] = $addressVal[$i];
        $mail->addAddress($addressVal[$i], $addressName[$i]);
    }
}
if (!$hasAddedAddress) {
    echo "Error: Debe indicar una cuenta de correo destino.";
    // die("Error: Debe indicar una cuenta de correo destino.");
}


$mail->isHTML(true);

$mail->Subject = $subject;
$mail->Body = $body;
$mail->AltBody = $altbody;
if(!$mail->send()) {
    echo "Error del servicio de correo: " . $mail->ErrorInfo;
} else {
    echo "El mensaje se envió satisfactoriamente.";
}