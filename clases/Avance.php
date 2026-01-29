<?php
require_once dirname(__DIR__)."/bootstrap.php";
class Avance {
    function __construct() {
    	global $avance_servidor, $avance_usuario, $avance_clave;
        $this->url      = "http://$avance_servidor/avance/cgi-bin/e-sasa/";
        $this->user     = $avance_usuario;
        $this->pswd     = $avance_clave;
        $this->target   = "avance";
        $this->regions = ["APSA"=>["APSA","JLA","JYL","MARLOT","RGA","COREPACK","SERVICIOS","BIDARENA"],"STACLARA"=>["GLAMA","LAISA","ENVASES","LAMINADOS","APEL","MELO","DANIEL","DESA","BIDASOA","ESMERALDA","FIDEICOMIS","FIDEMIFEL","SKARTON"]];
    }
    function getRegionByClient($client) {
        foreach ($this->regions as $region => $clientList) {
            if (in_array($client, $clientList)) return $region;
        }
        return null;
    }
    function getLoginHtmlFormUno() {
        return "<form method=\"post\" action=\"{$this->url}uno\" id=\"formLogin\" name=\"form1\" class=\"inlineblock\" target=\"$this->target\">".
            "<button type=\"submit\" name=\"cmdEnviar\" value=\"Autenticar\">Ingresar a Avance</button>".
            "</form>";
    }
    function getLoginHtmlForm() {
        return "<form method=\"post\" action=\"{$this->url}dos\" id=\"formLogin\" name=\"form1\" class=\"inlineblock\" target=\"$this->target\">".
            "<input type=\"hidden\" name=\"Corp\" value=\"$this->user\">".
            "<input type=\"hidden\" name=\"password\" value=\"$this->pswd\">".
            "<input type=\"hidden\" name=\"Idioma\" value=\"1\">".
            "<input type=\"hidden\" name=\"origen\" value=\"1\">".
            "<input type=\"hidden\" name=\"davrodmar\" value=\"On\">".
            "<input type=\"hidden\" name=\"escondido\" value=\"1\">".
            "<input type=\"hidden\" name=\"basura\" value=\"1\">".
            "<input type=\"submit\" name=\"cmdEnviar\" value=\"Autenticar\">".
            "</form>";
    }
    function getExportHtmlForm($client,$nombreArchivo,$numMes,$numAnio,$ftpObj) {
        foreach($this->regions as $regname=>$list) {
            foreach($list as $name) {
                if ($name===strtoupper($client)) {
                    $region=$regname;
                    break 2;
                }
            }
        }
        if (isset($region)) return "<form method=\"post\" action=\"{$this->url}CFAexterno2\" id=\"formExport\" name=\"form1\" class=\"inlineblock\" target=\"$this->target\">".
//                "<input type=\"hidden\" name=\"Corp\" value=\"$this->user\">".
//                "<input type=\"hidden\" name=\"password\" value=\"$this->pswd\">".
            "<input type=\"hidden\" name=\"origen\" value=\"1\">".
            "<input type=\"hidden\" name=\"davrodmar\" value=\"On\">".
            "<input type=\"hidden\" name=\"escondido\" value=\"1\">".
            "<input type=\"hidden\" name=\"basura\" value=\"1\">".
            "<input type=\"hidden\" name=\"Cual\" value=\"1\">".
            "<input type=\"hidden\" name=\"CualF\" value=\"15\">".
            "<input type=\"hidden\" name=\"Emp\" value=\"$client\">".
            "<input type=\"hidden\" name=\"Idioma\" value=\"1\">".
            "<input type=\"hidden\" name=\"Mes\" value=\"$numMes\">".
            "<input type=\"hidden\" name=\"Ano\" value=\"$numAnio\">".
            "<input type=\"hidden\" name=\"Reg\" value=\"Local$region\">".
/**/                "<input type=\"hidden\" name=\"Usu\" value=\"Local$this->user\">".
            "<input type=\"hidden\" name=\"End\" value=\"1\">".
            "<input type=\"hidden\" name=\"Nombre\" value=\"$ftpObj->ftpExportPath$nombreArchivo\">".
            "<input type=\"submit\" name=\"cmdEnviar\" value=\"Enviar Datos\" onclick=\"const rr=copyTextToClipboard('$ftpObj->ftpExportPath$nombreArchivo');if(typeof rr==='string') console.log(rr);\">".
            "</form>".(isset($_SESSION["user"])?"<!-- $_SESSION[user]->nombre -->":"");
    }
}
