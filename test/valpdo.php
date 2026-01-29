<?php
require_once dirname(__DIR__)."/bootstrap.php";
echo "<H1>VALIDAR ACEPTACION DE COMPROBANTE DE PAGO con PDO</H1>";
try {
    require_once "clases/Facturas.php";
    $invObj = new Facturas();
    $id = trim($_GET["id"]??"");
    if (isset($id[0])) {
        echo "<H2>ID</H2><P>$id</P>";
        $invData=$invObj->getData("id=$id");
    } else {
        $uuid = trim($_GET["uuid"]??"");
        if (isset($uuid[0])) {
            $uuid=strtoupper($uuid);
            echo "<H2>UUID</H2><P>$uuid</P>";
            $invData=$invObj->getData("uuid='$uuid'");
        }
    }

    if (isset($invData[0])) {
        $invData=$invData[0];
        $tc = $invData["tipoComprobante"];
        $rrfc = $invData["rfcGrupo"];
        $frp = $invData["fechaReciboPago"];
        $srp = $invData["saldoReciboPago"];
        echo "<H2>CFDI</H2><TABLE><TR><TH>TIPO COMPROBANTE<TH><TD>$tc</TD></TR><TR><TH>RFC RECEPTOR<TH><TD>$rrfc</TD></TR><TR><TH>FECHA RECIBO PAGO<TH><TD>$frp</TD></TR><TR><TH>SALDO RECIBO PAGO<TH><TD>$srp</TD></TR></TABLE>";
        if ($tc!=="p") echo "<H2>ERROR</H2><P>NO ES COMPROBANTE DE PAGO</P>";
        else {
            require_once "clases/DBPDO.php";
            $result = DBPDO::validaAceptacion($rrfc, $frp, $srp);
            echo "<H2>QUERY</H2><P>".DBPDO::$query."</P>";
            echo "<H2>COUNT</H2><P>".DBPDO::$lastCount."</P>";
            if ($result) {
                echo "<H2>RESULT</H2><P>VALIDADO. DEBE SER ACEPTADO! Status actual=$invData[statusn] | $invData[status]</P>";
                //$newStatusN|=Facturas::STATUS_ACEPTADO;
            } else echo "<H2>RESULT</H2><P>NO VALIDADO!</P>";
        }
    } else {
        if (!isset($invData)) {
            echo "<H2>DEBE INCLUIR ID o UUID</H2><P>".json_encode($_GET)."</P>";;
        } else {
            global $query;
            echo "<H2>NO EXISTE CFDI</H2><P>$query</P>";
        }
    }
} catch (Exception $e) {
    $earr=getErrorData($e);
    $str=$e->getTraceAsString();
    $invIdx=strpos($str, "invoice");
    if ($invIdx!==false) {
        $atIdx=strrpos($str, "#", $invIdx-strlen($str));
        if ($atIdx!==false) $str=substr($str, $atIdx);
    }
    echo "<H2>EXCEPCION</H2><TABLE><TR><TH>CLASS<TH><TD>".get_class($e)."</TD></TR><TR><TH>CODE<TH><TD>".$e->getCode()."</TD></TR><TR><TH>FILE<TH><TD>".$e->getFile()."</TD></TR><TR><TH>LINE<TH><TD>".$e->getLine()."</TD></TR><TR><TH>MESSAGE<TH><TD>".$e->getMessage()."</TD></TR><TR><TH>TRACE<TH><TD>".$str."</TD></TR></TABLE>";
}
