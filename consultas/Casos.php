<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
doCasosService();

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function doCasosService() {
    if (!hasUser()) {
        echo json_encode(["result"=>"refresh","action"=>"refresh"]);
        return;
    }
    switch ($_REQUEST["action"]) {
        case "email":
            $domain=$_REQUEST["domain"]??"";
            if (!isset($domain[0])) {
                echo json_encode(["result"=>"error","message"=>"Debe indicar una empresa origen del correo","request"=>$_REQUEST,"autofocus"=>"domain"]);
                return;
            }
            $email=$_REQUEST["email"]??"";
            if (!isset($email[0])) {
                echo json_encode(["result"=>"error","message"=>"Debe indicar un correo electrónico","request"=>$_REQUEST,"autofocus"=>"email"]);
                return;
            }
            $email=explode(";", $email);
            $badEmail=[];
            foreach ($email as $idx => &$data) {
                if (is_string($data)) {
                    $lt=strpos($data, "<");
                    if ($lt!==false) {
                        $gt=strpos($data, ">", $lt+1);
                        if ($gt!==false)
                            $data=["address"=>filter_var(trim(substr($data, $lt+1, $gt-$lt-1)), FILTER_SANITIZE_EMAIL),"name"=>filter_var(trim(substr($data, 0, $lt)), FILTER_SANITIZE_STRING)];
                    }
                }
                if (is_string($data)) {
                    $data = filter_var($data, FILTER_SANITIZE_EMAIL);
                    $at=strpos($data, "@");
                    if ($at!==false) {
                        $data=["address"=>$data, "name"=>filter_var(trim(substr($data, 0, $at)), FILTER_SANITIZE_STRING)];
                    } else {
                        $data=["address"=>$data, "name"=>filter_var(trim($data), FILTER_SANITIZE_STRING)];
                    }
                }
                if (!filter_var($data["address"], FILTER_VALIDATE_EMAIL)) {
                    $data["error"]="El correo electrónico no es válido";
                    $badEmail[]=$data;
                    unset($email[$idx]);
                }
            }
            unset($data);

            $email=array_values($email);
            if (!isset($email[0])) {
                echo json_encode(["result"=>"error","message"=>"El correo electrónico no es válido","email"=>$_REQUEST["email"],"badEmail"=>$badEmail,"autofocus"=>"email"]);
                return;
            }
            
            $subject = trim(filter_var($_REQUEST["subject"], FILTER_SANITIZE_STRING));
            if (!isset($subject[0])) {
                echo json_encode(["result"=>"error","message"=>"Debe indicar un asunto","request"=>$_REQUEST,"autofocus"=>"subject"]);
                return;
            }
            $message = trim(filter_var($_REQUEST["message"], FILTER_SANITIZE_STRING));
            if (!isset($message[0])) {
                echo json_encode(["result"=>"error","message"=>"Debe incluir texto en contenido","request"=>$_REQUEST,"autofocus"=>"message"]);
                return;
            }

            if (sendMail($subject,$message,null,$email,null,null,["domain"=>$domain]))
                echo json_encode(["result"=>"success","message"=>"Correo enviado con éxito","info"=>($_SESSION['lastEmailInfo']??$GLOBALS['lastEmailInfo']??false)]);
            else
                echo json_encode(["result"=>"error","message"=>"Correo fallido","info"=>($_SESSION['lastEmailInfo']??$GLOBALS['lastEmailInfo']??false),"post"=>$_POST,"domain"=>$domain,"email"=>$email]);
        break;
    }
}