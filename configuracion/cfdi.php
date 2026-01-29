<?php
function validaEnSat($emisor="",$receptor="",$total="",$uuid="") {
    // $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"><soapenv:Header/><soapenv:Body><tem:Consulta><tem:expresionImpresa>?re='.$emisor.'&amp;rr='.$receptor.'&amp;tt='.$total.'&amp;id='.$uuid.'</tem:expresionImpresa></tem:Consulta></soapenv:Body></soapenv:Envelope>';
    $soap = sprintf('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"><soapenv:Header/><soapenv:Body><tem:Consulta><tem:expresionImpresa>?re=%s&amp;rr=%s&amp;tt=%s&amp;id=%s</tem:expresionImpresa></tem:Consulta></soapenv:Body></soapenv:Envelope>', $emisor,$receptor,$total,$uuid);
    $headers = [
    'Content-Type: text/xml;charset=utf-8',
    'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
    'Content-length: '.strlen($soap)
    ];
    $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
    $ch = curl_init();
    /**/curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $res = curl_exec($ch);
    curl_close($ch);
    $xml = simplexml_load_string($res);
    $data = $xml->children('s', true)->children('', true)->children('', true);
    $data = json_encode($data->children('a', true), JSON_UNESCAPED_UNICODE);
    //print_r(json_decode($data));
    return $data;
    /*
stdClass Object (
[CodigoEstatus] => S - Comprobante obtenido satisfactoriamente.
[EsCancelable] => Cancelable sin aceptación
[Estado] => Vigente
[EstatusCancelacion] => stdClass Object ()
)
    */
/* http://validacfd.com/phpBB3/viewtopic.php?t=3974&start=20
*/
}
function valida_en_sat($factura) {
    $msg = <<<EOD
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
<soapenv:Header/>
<soapenv:Body>
  <tem:Consulta>
     <!--Optional:-->
     <tem:expresionImpresa><![CDATA[%%PRM%%]]></tem:expresionImpresa>
  </tem:Consulta>
</soapenv:Body>
</soapenv:Envelope>
EOD;
    $msg = str_replace("%%PRM%%",$factura,$msg);
    $ch=null;
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER,1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
            'cache-control: no-cache',
            'Host: consultaqr.facturaelectronica.sat.gob.mx'
        ));
        $url = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,180);
        curl_setopt($ch, CURLOPT_TIMEOUT,180);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $ret = substr($response, $header_size);
        $codigostatus=""; $escancelable="";
        $estado=""; $estatuscancelacion="";
        $curl_errno=curl_errno($ch);
        $curl_error=curl_error($ch);
        if ($curl_errno>0) {
            return ["errno"=>$curl_errno,"error"=>$curl_error,"mensaje"=>"No se pudo obtener respuesta del SAT"];
        }
        if (preg_match("/<a:CodigoEstatus>(.*)<\/a:CodigoEstatus>/",$ret,$match)) {
            $codigostatus = $match[1];
            if (isset($codigostatus[80])) $codigostatus=substr($codigostatus, 0, 80);
        }
        if (preg_match("/<a:EsCancelable>(.*)<\/a:EsCancelable>/",$ret,$match)) $escancelable = $match[1];
        if (preg_match("/<a:Estado>(.*)<\/a:Estado>/",$ret,$match)) {
            $estado = $match[1];
            if (isset($estado[30])) $estado=substr($estado, 0, 30);
        }
        if (preg_match("/<a:EstatusCancelacion>(.*)<a:EstatusCancelacion>/",$ret,$match)) $estatuscancelacion = $match[1];
        return ["expresionImpresa"=>$factura, "cfdi"=>$codigostatus, "estado"=>$estado, "escancelable"=>$escancelable, "estatuscancelacion"=>$estatuscancelacion,"todo"=>$ret];
    } catch (Exception $e) {
        return ["error"=>$e, "mensaje"=>"No se pudo acceder al portal del SAT."];
    } finally {
        if ($ch!==null) curl_close($ch);
    }
}

function validaMultipleSat($factarr) {
    $msg="<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\">";
    $msg = <<<EOD
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
<soapenv:Header/>
<soapenv:Body>
  <tem:Consulta>
     <!--Optional:-->
     <tem:expresionImpresa><![CDATA[%%PRM%%]]></tem:expresionImpresa>
  </tem:Consulta>
</soapenv:Body>
</soapenv:Envelope>
EOD;
    $msg = str_replace("%%PRM%%",$factura,$msg);
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER,1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
            'cache-control: no-cache',
            'Host: consultaqr.facturaelectronica.sat.gob.mx'
        ));
        $url = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $ret = substr($response, $header_size);
        $codigostatus=""; $escancelable="";
        $estado=""; $estatuscancelacion="";
        if (preg_match("/<a:CodigoEstatus>(.*)<\/a:CodigoEstatus>/",$ret,$match)) $codigostatus = $match[1];
        if (preg_match("/<a:EsCancelable>(.*)<\/a:EsCancelable>/",$ret,$match)) $escancelable = $match[1];
        if (preg_match("/<a:Estado>(.*)<\/a:Estado>/",$ret,$match)) $estado = $match[1];
        if (preg_match("/<a:EstatusCancelacion>(.*)<a:EstatusCancelacion>/",$ret,$match)) $estatuscancelacion = $match[1];
        return ["expresionImpresa"=>$factura, "cfdi"=>$codigostatus, "estado"=>$estado, "escancelable"=>$escancelable, "estatuscancelacion"=>$estatuscancelacion];
    } catch (Exception $e) {
        return ["error"=>$e, "mensaje"=>"No se pudo acceder al portal del SAT."];
    }
}
