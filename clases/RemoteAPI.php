<?php
require_once dirname(__DIR__)."/bootstrap.php";

class RemoteAPI {
    public static $lastException = null;
    public static $nl = "\n";
    private CurlHandle $_ch;
    private $_log = [];
    private $_url = null;
    private static $sites = [];
    public function log($prompt, $texto=null) {
//        if (isset($texto)) {
//            if ($texto===false) $this->_log=[];
//            else if (!empty($texto)) $this->_log[]=$texto;
//        }
//        return self::$_log;
        $data = ["url"=>$this->_url];
        if (is_string($texto)) $data["message"]=$texto;
        if (isAssociativeArray($texto)) $data+=$texto;
        else if ($texto) $data["data"]=$texto;

        $docname="curl";
        if ($prompt==="Error") $docname="error";
        doclog("REMOTEAPI $prompt", $docname, $data);
    }
    public static function newInstance($url=null, $data=null) {
        if (isset(self::$sites[$url])) return self::$sites[$url];
        try {
            $obj = new REMOTEAPI($url);
            $obj->log("INIT Success");
            self::$sites[$_url]=$obj;
            if (!is_null($data)) $obj->setOpt($data);
            return $obj;
        } catch (Exception $e) {
            self::$lastException = getErrorData($e);
            doclog("REMOTEAPI INIT Error", "error", self::$lastException);
            return null;
        }
    }
    private function __construct($url) {
        $this->_url = $url;
        $this->_ch = curl_init($url); //if (!is_null($url)) curl_setopt($_ch, CURLOPT_URL, $url);
    }
    public function setOpt($data=null) {
        if (!is_null($data) && is_array($data)) foreach ($data as $key => $value) {
            curl_setopt($this->_ch, $key, $value);
        }
    }
    public function getErrno() {
        return curl_errno($this->_ch);
    }
    public function getError() {
        return curl_error($this->_ch);
    }
    public function getInfo($infoIndex) {
        return curl_getinfo($this->_ch, $infoIndex);
    }
    public function getHeaderSize() {
        return $this->getInfo(CURLINFO_HEADER_SIZE);
    }
    public function exec($keys=null) {
        $ret = substr(curl_exec($this->_ch), $this->getHeaderSize());
        $errno=curl_errno($this->_ch);
        if ($errno>0) $this->log("Error",["errno"=>$errno, "error"=>curl_error($this->_ch)]);
        if (!$keys) return $ret;
        if (is_scalar($keys)) {
            if (preg_match($keys,$ret,$match)) {
                return $match[1];
            }
            $this->log("Error","NO MATCH",["keys"=>$keys,"match"=>$match,"text"=>$ret]);
            return false; // null
        }
        if (is_array($keys)) {
            $arr=[];
            $logdata=[];
            foreach ($keys as $key => $value) {
                if (preg_match($key,$ret,$match)) {
                    $arr[$key]=$match[1];
                    $logdata[]="MATCH $key = '".$match[1]."'";
                } else {
                    $logdata[]="FAIL MATCH $key";
                }
            }
            if ($arr) $this->log("Data",["data"=>$arr, "log"=>$logdata]);
        }
    }
    public function close() {
        curl_close($this->_ch);
    }
}
