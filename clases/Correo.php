<?php
require_once dirname(__DIR__)."/bootstrap.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once "PHPMailer/src/Exception.php";
require_once "PHPMailer/src/PHPMailer.php";
require_once "PHPMailer/src/SMTP.php";
class Correo {
    private $mail=null;
    public $error=[];
    public $debug=[];
    public $monitor=null;
// hst,usr,nam,pwd,dbg,sec,prt
    function __construct($setFrom=true) {
        global $mail_debug, $mail_seguridad, $mail_puerto, $mail_monitor;
        set_error_handler(array(&$this,"errorHandler"));
        $this->mail=new PHPMailer(true);
        $this->mail->isSMTP();
        //$this->mail->Host = $mail_servidor;
        //echo "<!-- Mail Host : $mail_servidor -->";
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPOptions = ["ssl"=>["verify_peer"=>false, "verify_peer_name"=>false,"allow_self_signed"=>true]];
        $this->mail->SMTPDebug = $mail_debug;
        $this->mail->CharSet='UTF-8';
        $this->mail->Encoding='base64';
        $this->settingsByKey($setFrom);
        //echo "<!-- Mail User : $mail_usuario -->";
        //$this->mail->Username = $mail_usuario;
        //$this->mail->Password = $mail_clave;
        $this->monitor = $mail_monitor;
        //$this->mail->SMTPSecure = $mail_seguridad;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // "tls"; // 
        $this->mail->Port = $mail_puerto;
        $this->mail->Debugoutput=function($str,$lvl) {if(isset($this->debug[$lvl]))$this->debug[$lvl].=$str;else $this->debug[$lvl]=$str;};
        $this->mail->isHTML(true);
    }
    function restart() {
        $this->mail->clearAllRecipients();
        //$this->mail->clearCustomHeaders();
        $this->mail->clearReplyTos();
    }
    /*function setUser($username, $password) {
        $this->mail->Username = $username;
        $this->mail->Password = $password;
    }*/
    function settingsByKey($setFrom=true, $key="default") {
        global $mail_servidor, $mail_usuario, $mail_clave, $mail_alias;
        if (is_array($mail_servidor)) {
            if (isset($mail_servidor[$key])) $this->mail->Host = $mail_servidor[$key];
            else $this->mail->Host = "none";
        } else if (is_string($mail_servidor)) $this->mail->Host = $mail_servidor;
        else $this->mail->Host = "none";

        if (is_array($mail_usuario) && is_array($mail_clave)) {
            if (isset($mail_usuario[$key]) && isset($mail_clave[$key])) {
                $this->mail->Username = $mail_usuario[$key];
                $this->mail->Password = $mail_clave[$key];
                $this->setSender($this->mail->Username);
            } else {
                $this->mail->Username = "nokey";
                $this->mail->Password = "nokey";
            }
        } else if (is_string($mail_usuario) && is_string($mail_clave)) {
            $this->mail->Username = $mail_usuario;
            $this->mail->Password = $mail_clave;
            $this->setSender($this->mail->Username);
        } else {
            $this->mail->Username = "none";
            $this->mail->Password = "none";
        }
        if ($setFrom)
            $this->setFrom($this->mail->Username,$mail_alias);
    }
    function setSender($address) {
        $this->mail->Sender = $address;
    }
    function addMonitor() {
        $this->mail->addBCC($this->monitor,"Monitor");
        //$this->mail->addCustomHeader("BCC:".$this->monitor);
        //$this->mail->addCustomHeader("BCC", $this->monitor);
        //$mail->addCustomHeader('BCC', implode(',', [$this->monitor, $this->monitor2])); // requiere monitor2
    }
    function setFrom($address, $name="", $auto=true) {
        //$this->mail->From=$address;
        //$this->mail->FromName=$name;
        return $this->mail->setFrom($address, $name, $auto);
    }
    function getFrom() {
        $from = $this->mail->From??"";
        $fromname = $this->mail->FromName??"";
        if (isset($from[0]) && isset($fromname[0])) $from.=": ".$fromname;
        return $from;
    }
    function addAddress($toAddress, $toName="") {
        return $this->mail->addAddress($toAddress,$toName);
    }
    function addAddresses($addressList) { // [["address"=>".@..","name"=>"..."],[...],...] or String("name <address>, ...")
        $valid=0;
        if (is_string($addressList)) foreach ($this->mail->parseAddresses($addressList) as $address) {
            if ($this->mail->addAddress($address['address'], $address['name'])) $valid++;
        } else if (is_array($addressList)) foreach ($addressList as $idx => $elem) {
            if((isset($elem["address"][0]) && $this->mail->addAddress($elem["address"],$elem["name"]??""))||
               (isset($elem["email"][0]) && $this->mail->addAddress($elem["email"],$elem["persona"]??$elem["nombre"]??$elem["name"]??"")))
                $valid++;
        }
        return $valid;
    }
    function addCC($ccAddress, $ccName="") {
        return $this->mail->addCC($ccAddress,$ccName);
    }
    function addBCC($bcAddress, $bcName="") {
        return $this->mail->addBCC($bcAddress,$bcName);
    }
    function addReplyTo($rtAddress, $rtName="") {
        return $this->mail->addReplyTo($rtAddress,$rtName);
    }
    function setSubject($subject) {
        return $this->mail->Subject=$subject;
    }
    function setBody($body) {
        $this->mail->AltBody=$this->convertHTML2Text($body,true);
        return $this->mail->Body=$body;
    }
    function setAltBody($altBody) {
        return $this->mail->AltBody=$altBody;
    }
    function addAttachment($path,$name="",$encoding=PHPMailer::ENCODING_BASE64,$type="",$disposition="attachment") {
        return $this->mail->addAttachment($path,$name,$encoding,$type,$disposition);
    }
    function getInfo() {
        try {
            /*$data=[ "host"=>$this->mail->Host, 
                    "port"=>$this->mail->Port, 
                    "username"=>$this->mail->Username, 
                    "monitor"=>$this->monitor, 
                    "security"=>$this->mail->SMTPSecure, 
                    "allAddresses"=>$this->mail->getAllRecipientAddresses() ];*/

            $data=[ "username"=>$this->mail->Username,
                    "monitor"=>$this->monitor,
                    "host"=>$this->mail->Host,
                    "hostname"=>$this->mail->Hostname,
                    "port"=>$this->mail->Port,
                    "security"=>$this->mail->SMTPSecure, 
                    "debug"=>$this->debug, 
                    "smtpdebug"=>$this->mail->SMTPDebug,
                    "authtype"=>$this->mail->AuthType, 
                    "sender"=>$this->mail->Sender,
                    "replyTo"=>$this->mail->getReplyToAddresses(),
                    "helo"=>$this->mail->Helo,
                    "from"=>$this->mail->From,
                    "fromName"=>$this->mail->FromName,
//                        "to"=>$this->mail->To,
//                        "toName"=>$this->mail->ToName,
//                        "cc"=>$this->mail->cc,
//                        "bcc"=>$this->mail->bcc, 
                    "allAddresses"=>$this->mail->getAllRecipientAddresses(),
                    "subject"=>$this->mail->Subject/*,
                    "body"=>$this->mail->AltBody*/];
            return $data;
        } catch (Exception $ex) {
            $data=["error"=>get_class($ex)];
            $errorExceptionMethods=["code"=>"getCode","file"=>"getFile","line"=>"getLine","message"=>"getMessage","trace"=>"getTraceAsString"];
            foreach ($errorExceptionMethods as $codeKey => $methodName) {
                if (method_exists($ex,$methodName)) {
                    $data[$codeKey]=$ex->$methodName();
                    if ($codeKey==="trace") {
                        $trace=$data["trace"];
                        $idx=strpos($trace,"invoice");
                        if ($idx!==false) {
                            $atIdx=strrpos($trace, "#", $idx-strlen($trace));
                            if ($atIdx!==false) $data["trace"]=substr($trace, $atIdx);
                        }
                    }
                } else $data[$codeKey]="IGNORED";
            }
            return $data;
        }
    }
    function send() {
        // $addresses=[],$subject="",$body=""
        return $this->mail->send();
    }
    function getErrorInfo() {
        return $this->mail->ErrorInfo;
    }
    function errorHandler($errno, $errstr, $errfile, $errline, $errcontext=null) {
        $this->error[]=[$errno,array_search($errno, get_defined_constants(), true),$errstr,$errfile,$errline];
    }
    private function convertHTML2Text($html, $ignoreErrors=false) {
        $isOfficeDoc=(strpos($html, "urn:schemas-microsoft-com:office")!==false);
        if ($isOfficeDoc) $html = str_replace(array("<o:p>", "</o:p>"),"",$html);
        $html = str_replace("\r", "\n", str_replace("\r\n","\n",$html)); // fixNewLines
        if (mb_detect_encoding($html, "UTF-8", true))
            $html = mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8");
        $doc = $this->getDomDoc($html,$ignoreErrors);
        $output = $this->iterateOverNode($doc, null, false, $isOfficeDoc);
        $output = $this->processWhitespaceNewlines($output);
        return $output;
    }
    private function getDomDoc($html,$ignoreErrors=false) {
        $doc = new \DomDocument();
        $html=trim($html);
        if (!$html) return $doc;
        if ($html[0]!=="<") $html="<body>$html</body>";
        if ($ignoreErrors) {
            $doc->strictErrorChecking=false;
            $doc->recover=true;
            $doc->xmlStandalone=true;
            $old_internal_errors=libxml_use_internal_errors(true);
            $result=$doc->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET | LIBXML_PARSEHUGE);
            libxml_use_internal_errors($old_internal_errors);
        } else $result = $doc->loadHTML($html);
        if (!$result) throw new Exception("Could not load HTML - badly formed?"); //, $html);
        return $doc;
    }
    private function iterateOverNode($node, $preName=null, $inPre=false, $isOfficeDoc=false) {
        if ($node instanceof \DOMText) {
            if ($inPre) {
                $text="\n".trim($node->wholeText,"\n\r\t ")."\n";
                $text=preg_replace("/[ \t]*\n/im","\n",$text);
                return str_replace("\n","\r",$text);
            } else {
                $text=preg_replace("/[\\t\\n\\f\\r ]+/im", " ", $node->wholeText);
                $test=trim($text,"\n\r\t ");
                if (isset($test[0]) && ($preName==="p"||$preName==="div")) {
                    return "\n".$text;
                }
                return $text;
            }
        }
        if ($node instanceof \DOMDocumentType) return "";
        if ($node instanceof \DOMProcessingInstruction) return "";
        $name=strtolower($node->nodeName);
        $nextName=$this->nextChildName($node);
        switch($name) {
            case "hr": return ($preName!==null?"\n":"")."---------------------------------------------------------------\n";
            case "style": case "head": case "title":
            case "meta": case "script": return "";
            case "h1": case "h2": case "h3":
            case "h4": case "h5": case "h6":
            case "ol": case "ul": case "pre": $output="\n\n"; break;
            case "td": case "th": $output="\t"; break;
            case "p": 
                if ($isOfficeDoc && $node->getAttribute("class")==="MsoNormal") {
                    $output="";
                    $name="br";
                    break;
                }
                $output="\n";
                break;
            case "tr": $output="\n"; break;
            case "div": if ($preName!==null) $output="\n"; else $output=""; break;
            case "li": $output="- "; break;
            default: $output="";
        }
        if (isset($node->childNodes)) {
            $n=$node->childNodes->item(0);
            $preSiblNames=array();
            $preSiblName=null;
            $parts=array();
            $trailingWhitespace=0;
            while($n!=null) {
                $text=$this->iterateOverNode($n,$preSiblName,$inPre||$name==="pre",$isOfficeDoc);
                $test=trim($text,"\n\r\t ");
                if ($n instanceof \DOMDocumentType
                 || $n instanceof \DOMProcessingInstruction
                 || ($n instanceof \DOMText && !isset($test[0]))) {
                    $trailingWhitespace++;
                } else {
                    $preSiblName = strtolower($n->nodeName);
                    $preSiblNames[] = $preSiblName;
                    $trailingWhitespace=0;
                }
                $node->removeChild($n);
                $n=$node->childNodes->item(0);
                $parts[]=$text;
            }
            while($trailingWhitespace-- > 0) array_pop($parts);
            $lastName=array_pop($preSiblNames);
            if ($lastName==="br") {
                $lastName=array_pop($preSiblNames);
                if ($lastName==="#text") array_pop($parts);
            }
            $output.=implode("",$parts);
        }
        switch($name) {
            case "h1": case "h2": case "h3":
            case "h4": case "h5": case "h6":
            case "pre": case "p": $output.="\n\n"; break;
            case "br": $output.="\n"; break;
            case "div": break;
            case "a":
                $href=$node->getAttribute("href");
                $output=trim($output);
                if (substr($output,0,1)==="[" && substr($output,-1)==="]") {
                    $output=substr($output,1,strlen($output)-2);
                    if($node->getAttribute("title")) $output=$node->getAttribute("title");
                }
                if (!$output && $node->getAttribute("title"))
                    $output = $node->getAttribute("title");
                if ($href==null) {
                    if ($node->getAttribute("name")!=null) $output="[$output]";
                } else if ($href==$output || $href=="mailto:$output" || $href=="http://$output" || $href=="https://$output") {
                    $output;
                } else if ($output) $output="[$output]($href)";
                else $output=$href;
                switch($nextName) {
                    case "h1": case "h2": case "h3": case "h4": case "h5": case "h6": $output.="\n";
                }
                break;
            case "img":
                if ($node->getAttribute("title")) $output="[".$node->getAttribute("title")."]";
                else if($node->getAttribute("alt")) $output="[".$node->getAttribute("alt")."]";
                else $output="";
                break;
            case "li": $output.="\n"; break;
            case "blockquote":
                $output=$this->processWhitespaceNewlines($output);
                $output="\n".$output;
                $output=preg_replace("/\n/im","\n> ",$output);
                $output=preg_replace("/\n> >/im","\n>>",$output);
                $output="\n".$output."\n\n";
                break;
        }
        return $output;
    }
    private function processWhitespaceNewlines($text) {
        $text=preg_replace("/ *\t */im", "\t", $text);
        $text=ltrim($text);
        $text=preg_replace("/\n[ \t]*/im", "\n", $text);
        $text=str_replace("\xc2\xa0", " ", $text);
        $text=rtrim($text);
        $text=preg_replace("/[ \t]*\n/im", "\n", $text);
        $text=str_replace("\r", "\n", str_replace("\r\n","\n",$text));
        $text=preg_replace("/\n\n\n*/im", "\n\n", $text);
        return $text;
    }
    private function nextChildName($node) {
        $nextNode = $node->nextSibling;
        while($nextNode!=null) {
            if ($nextNode instanceof \DOMText) {
                //if (isWhitespace($nextNode->wholeText)) break;
                $test=trim($nextNode->wholeText, "\n\r\t ");
                if (!isset($test[0])) break;
            }
            if ($nextNode instanceof \DOMElement) break;
            $nextNode=$nextNode->nextSibling;
        }
        $nextName=null;
        if ($nextNode!=null && ($nextNode instanceof \DOMElement || $nextNode instanceof \DOMText)) {
            $nextName = strtolower($nextNode->nodeName);
        }
        return $nextName;
    }
}
