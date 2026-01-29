<?php
error_reporting(E_ALL);
//echo "satcancelable\n";
date_default_timezone_set("America/Mexico_City");
$mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
$bd_servidor = "localhost";
$bd_usuario = "invoiceapp";
$bd_clave = "e4vnCV05Aq4qb";
$bd_base = "invoice";
$basePath = dirname(dirname(__FILE__))."\\";
$path = $basePath."test\\";
require_once $basePath."configuracion\\cfdi.php";
function sclog($txt) {
    global $path;
    $fmt = (new DateTime())->format("y-m-d H:i:s");
    file_put_contents($path."satcancelable.log","[$fmt] $txt\r\n", FILE_APPEND | LOCK_EX);
}
function selog($txt) {
    global $path;
    $fmt = (new DateTime())->format("y-m-d H:i:s");
    file_put_contents($path."satcancelable.err.log","[$fmt]$txt\r\n", FILE_APPEND | LOCK_EX);
}
function dot() {
    global $path;
    file_put_contents($path."satcancelable.log",".", FILE_APPEND | LOCK_EX);
}
$conn = @new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
if ($conn->connect_error) {
    selog("Connect Error ".$conn->connect_errno.": ".$conn->connect_error);
    exit();
}
//dot();
$query="select f.id,p.rfc rfcE,f.rfcGrupo rfcR,trim(f.total)+0 total,f.uuid,f.estadocfdi estado,f.solicitaCFDI,f.statusn from facturas f inner join proveedores p on f.codigoProveedor=p.codigo where (f.cancelableCFDI is null or f.solicitaCFDI is not null) and f.statusn>=0 and f.statusn<128 and f.estadoCFDI='Vigente' order by f.solicitaCFDI desc,f.id desc limit 1";
$result=$conn->query($query);
if (is_object($result)) $row=$result->fetch_assoc();
else selog("Retrieve Data Error ".$conn->errno.": ".$conn->error);
if (!$row) {
    $rowType=gettype($row);
    if ($rowType==="object") $rowType.=" (".get_class($row).")";
    $errtxt="NO DATA. $rowType";
    if (isset($row["id"])) $errtxt.=" id=$row[id]";
    if (isset($conn->errno)) $errtxt.=" errno=".$conn->errno;
    if (isset($conn->error[0])) $errtxt.=" error=".$conn->error;
	selog($errtxt);
} else {
    $id=$row["id"];
    $rfcE = str_replace("&","&amp;",$row["rfcE"]); // utf8_encode($row["rfcE"]));
    $rfcR = str_replace("&","&amp;",$row["rfcR"]); // utf8_encode($row["rfcR"]));
    $total = str_pad(sprintf("%.6f", (double)$row["total"]), 17, "0", STR_PAD_LEFT);
    $uuid = $row["uuid"];
    $estado = $row["estado"];
    $statusn = $row["statusn"];
    $result->close();
    //sclog("Retrieved Data: invId=$id, rfcE=$rfcE, rfcR=$rfcR, total=$total, uuid=$uuid");

    $qr="?re=$rfcE&rr=$rfcR&tt=$total&id=$uuid";
    require_once "clases/CFDI.php";
    if ($rfcR===CFDI::RFCDEMO) {
        $conn->close();
        exit();
    }

    $factura = valida_en_sat($qr);

    // validar que $factura["cfdi"]==="S - Comprobante obtenido satisfactoriamente."
    // validar si $factura["estado"]!=="Vigente". solicitar campos CFDI de la base para compararlos y si estado ha cambiado hacer cambios en statusn y nuevos campos CFDI
    //sclog("SAT escancelable: ".$factura["escancelable"]);
    $dt = new DateTime();
    $fmt = $dt->format("Y-m-d H:i:s");
    if ($factura["cfdi"]==="S - Comprobante obtenido satisfactoriamente.") {
        //if(!empty($factura["estatuscancelacion"])) sclog("SAT estatuscancelacion: ".$factura["estatuscancelacion"]);
        if ($factura["estado"]==="Vigente") {
            $esCancelableValue=$factura["escancelable"]??"";
            $query="update facturas set consultaCFDI='$fmt', cancelableCFDI='$esCancelableValue'";
            if (!empty($row["solicitaCFDI"])) $query.=", solicitaCFDI=NULL, numConsultasCFDI=numConsultasCFDI+1";
            if (!empty($factura["estatuscancelacion"])) $query.=", canceladoCFDI='$factura[estatuscancelacion]'";
            $query.=" where id=$id";
            if (!$conn->query($query)) selog("ERROR $conn->errno: $conn->error . $query . Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
            else sclog("EXITO! Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
        } else if ($factura["estado"]==="Cancelado") {
            require_once $basePath."clases\\Facturas.php";
            $query="update facturas set status='Cancelado', statusn=statusn+".Facturas::STATUS_CANCELADOSAT.", consultaCFDI='$fmt', estadoCFDI='$factura[estado]',cancelableCFDI='$factura[escancelable]'";
            if (!empty($row["solicitaCFDI"])) $query.=", solicitaCFDI=NULL, numConsultasCFDI=numConsultasCFDI+1";
            if (!empty($factura["estatuscancelacion"])) $query.=", canceladoCFDI='$factura[estatuscancelacion]'";
            $query.=" where id=$id";
            if (!$conn->query($query)) selog("ERROR $conn->errno: $conn->error . $query . Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
            else {
                require_once $basePath."clases\\SolicitudPago.php";
                $solObj=new SolicitudPago();
                $solObj->updateStatus($id, Facturas::STATUS_CANCELADOSAT);
                sclog("CANCELADO! Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
            }
            // aqui hay que hacer mas cosas
            // Si la factura no está vigente, igual hay que guardar la info obtenida, pero también hay que rechazar la factura
        }
    } else if ($factura["cfdi"]==="N - 601: La expresión impresa proporcionada no es válida.") {
        $query="update facturas set estadoCFDI='Invalido', solicitaCFDI=NULL, consultaCFDI='$fmt', cancelableCFDI='601' where id=$id";
            if (!$conn->query($query)) selog("ERROR $conn->errno: $conn->error . $query . Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
            else selog("INVALIDO! Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
    } else if ($factura["cfdi"]==="N - 602: Comprobante no encontrado.") {
        $query="update facturas set estadoCFDI='No encontrado', solicitaCFDI=NULL, consultaCFDI='$fmt', cancelableCFDI='602' where id=$id";
            if (!$conn->query($query)) selog("ERROR $conn->errno: $conn->error . $query . Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
            else selog("NO ENCONTRADO! Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
    } else {
        $query="update facturas set solicitaCFDI=NULL, consultaCFDI='$fmt', cancelableCFDI='ERR' where id=$id";
            if (!$conn->query($query)) selog("ERROR $conn->errno: $conn->error . $query . Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
            else selog("FALLA! Factura id:$id | $factura[expresionImpresa] | $factura[cfdi] | $factura[estado] | $factura[escancelable] | $factura[estatuscancelacion]");
    }
    // guardar $factura["escancelable"] y $factura["estatuscancelacion"] usando id en facturas
    // query = update escancelable, estatuscancelacion
} // else No hay mas facturas por revisar
$conn->close();
