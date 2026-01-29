<?php
require_once dirname(__DIR__)."/bootstrap.php";
$isValidUser = false;
$isAdmin = false;
$isSysop = false;
$isCorpOp = false;
$isPrvUsr = false;
$baseurl=getBaseURL();
if (hasUser()) {
    $isAdmin = validaPerfil("Administrador");
    $isSysOp = validaPerfil("Sistemas");
    $isCorpOp = validaPerfil("Compras");
    $isPrvUsr = validaPerfil("Proveedor");
    $isValidUser = $isAdmin || $isSysOp;
} else {
    $_COOKIE["menu_accion"]="";
    echo "<script>document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/invoice';window.location.replace('{$baseurl}');</script>";
    return;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $baseurl ?>" target="_self">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>FACTURA</title>
    <link href="css/factura.php" rel="stylesheet" type="text/css">
    <script src="scripts/general.js?ver=1.1"></script>
    <script src="scripts/factura.php?ver=1.0.1"></script>
<?php
require_once "clases/Facturas.php";
$invObj = new Facturas();
$log="";
$isNewForm=false;
if (isset($_GET["id"])) {
    $idFactura = $_GET["id"];
    $log.="\nID=$idFactura";
    $fData = $invObj->getData("id=$idFactura");
} else if (isset($_GET["nombre"])) {
    $nombreInterno = $_GET["nombre"];
    $log.="\nNOMBRE=$nombreInterno";
    $invObj->clearOrder();
    $invWhere="nombreInterno='$nombreInterno'";
    if (isset($_GET["ciclo"])) {
        $log.="\nCICLO=$_GET[ciclo]";
        $invWhere.=" AND ciclo='$_GET[ciclo]'";
    } else $invObj->addOrder("ciclo", "desc");
    $fData = $invObj->getData($invWhere);
} else {
    $log.="\nSIN ID NI NOMBRE";
    $isNewForm=true; // ToDo: que tampoco tenga el archivo post
}
if ($isValidUser) {
    if (isset($_POST["submitxml"])) {
        $file=$_FILES["xmlfiles"];
        $xmlname=$file["name"];
        $xmltemp=$file["tmp_name"];
        $xmltype=$file["type"];
        $xmlsize=$file["size"];
        $xmlerr=$file["error"];
        //require_once "configuracion/finalizacion.php";
        //die();

    } else if ($isNewForm) {
        $log.="\nFORMA NUEVA";
        //$baseurl=getBaseURL();
?>
    <script>
function checkChange() {
    changeMessage = "";
    var xf = document.getElementById("xmlfiles");
    if (xf.files.length>0) {
        var fileData = xf.files[0];
        var name = fileData.name;
        var size = +fileData.size;
        var type = fileData.type;
        var prfx = "";
        var sufx = "";
        if (type!=="text/xml") {
            changeMessage += "<p>El archivo '"+name+"' no tiene formato XML</p>";
            prfx = "ERROR ";
            sufx += " | type";
        }
        if (size>2097000) {
            changeMessage += "<p>El archivo '"+name+"' excede el tamaño máximo permitido de 2MB</p>";
            prfx = "ERROR ";
            sufx += " | size";
        }
        console.log(prfx+"File "+name+" "+type+" "+size+"bytes"+sufx);
    }
    if (changeMessage.length>0) overlayMessage(changeMessage,"Error");
    else {
        xf.classList.remove("highlight");
        var sx = document.getElementById("submitxml");
        if (sx) {
            sx.classList.add("highlight");
            sx.focus();
        }
    }
}
    </script>
  </head>
  <body>
    <h1 class="txtstrk">Convertir XML a HTML/PDF</h1>
    <form method="post" name="forma_nueva" target="_self" enctype="multipart/form-data">
      <div id="xml_selector" class="marginbottom nowrap" title="Seleccione un archivo XML.">XML: <input type="file" name="xmlfiles" id="xmlfiles" class="highlight" autofocus accept=".xml" onchange="checkChange()"> <input type="<?= $deshabilitarAlta&&!$esAdmin?"hidden":"submit" ?>" name="submitxml" id="submitxml" value="Verificar" onclick="document.forma_alta.submited=this.id;this.classList.remove('highlight');"></div>
      <div id="waiting-roll" class="xhidden"><img src="<?=$waitImgName?>" width="360" height="360"></div>
    </form>
  </body>
</html>
<?php     
        require_once "configuracion/finalizacion.php";
        die();
    }
} else $log.="\nIS ".($isValidUser?"":"IN")."VALID, HAS".(isset($fData)?"":"N'T")." DATA, IS".(is_null($fData)?"":"N'T")." NULL";
if (!isset($fData["nombreInterno"])) $fData = $fData[0];
require_once "clases/CFDI.php";
if (empty($fData)) {
    CFDI::clearLastError();
    $lastError=["errorMessage"=>"No existe la factura indicada","errorStack"=>"","log"=>$log];
} else {
    $idFactura = $fData["id"];
    $nombreInterno = $fData["nombreInterno"];
    $ciclo = $fData["ciclo"];
    $ubicacion = $fData["ubicacion"];
    //$baseurl=getBaseURL();
    $nombre = getBasePath().$fData["ubicacion"].$nombreInterno.".xml";
    $cfdiObj = CFDI::newInstanceByLocalName($nombre);
    if ($cfdiObj===null) $lastError = CFDI::getLastError();
}
if (isset($lastError)) {
?>
<!DOCTYPE html>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $baseurl ?>" target="_blank">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>FACTURA</title>
    <link href="css/factura.php" rel="stylesheet" type="text/css">
    <script src="scripts/factura.php?ver=1.0.1"></script>
  </head>
  <body>
    <ul>
      <li>Error Message: <?= $lastError["errorMessage"] ?></li>
      <li>Error Stack:<hr><?= $lastError["errorStack"] ?></li>
      <li>Log: <pre class='wordwrap'><?= $lastError["log"] ?></pre></li>
    </ul>

  </body>
</html>
<?php            
    require_once "configuracion/finalizacion.php";
    die();
}

$emisor = $cfdiObj->get("Emisor");
$receptor = $cfdiObj->get("Receptor");
$rfc_emisor = $emisor["@rfc"];
$rfc_receptor = $receptor["@rfc"];

if(!$isValidUser && $isCorpOp) {
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $isValidUser = $ugObj->isRelatedByRFC(getUser(), $rfc_receptor, "Compras", "vista");
}
if(!$isValidUser && $isPrvUsr) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
    $usrnm = getUser()->nombre;
    $usrRfc = $prvObj->getValue("codigo",$usrnm,"rfc");
    $isValidUser = ($rfc_emisor===$usrRfc);
}
if(!$isValidUser) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/index.php");
    require_once "configuracion/finalizacion.php";
    die("Redirecting to /".$_project_name."/index.php");
}

require_once "clases/catalogoSAT.php";
$version = $cfdiObj->get("version");
$folio = $cfdiObj->get("Folio");
$serie = $cfdiObj->get("serie");
$fecha = $cfdiObj->get("fecha");

$bits = "";
$bits .= $isAdmin?"1":"0";
$bits .= $isSysOp?"1":"0";
$bits .= $isCorpOp?"1":"0";
$bits .= $isPrvUsr?"1":"0";
if (empty($fData["status"])) $bits .= "-";
else {
    if ($isAdmin) $bits .= " $fData[status] ";
    $bits .= in_array($fData["status"], ["Pendiente","Aceptado","Contrarrecibo","Exportado","ExpSinContra"],true)?"1":"0";
}
?>
<!DOCTYPE html>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $baseurl ?>" target="_blank">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>FACTURA</title>
    <link href="css/factura.php" rel="stylesheet" type="text/css" />
    <script src="scripts/factura.php?ver=1.0.1"></script>
  </head>
  <body class="factura">
    <div class="noprint footinfo"> <?= $bits." ".getUser()->nombre." $idFactura <a href=\"$nombre\" style=\"text-decoration:none;\">$nombre</a>" ?></div>
    <div id="contenedor">
      <table width="100%" margin="auto">
        <tr>
          <td colspan="2" align="right">
            <table border="1">
              <tr>
                <th class="h1">Version</th>
                <td align="center" class="h1"><?= $version ?></td>
              </tr>
              <tr>
                <th class="h1">Serie</th>
                <td align="center" class="h1"><?= $serie ?></td>
              </tr>
              <tr>
                <th class="h1">Folio</th>
                <td align="center" class="h1"><?= $folio ?></td>
              </tr>
              <tr>
                <th class="h1">Fecha</th>
                <td align="center" class="h1"><?= $fecha ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="entity">
            <table width="100%" border="1">
              <tr><th colspan="2" class="h2">Emisor</th></tr>
<?php
foreach($emisor as $key=>$value) {
    if ($key[0]==="@") $keyFix = strtoupper(substr($key,1));
    else $keyFix = strtoupper($key);
    if (is_array($value)) { //$value = json_encode($value);
        $tmpval = "";
        foreach($value as $attkey=>$attval) {
            if ($attkey[0]==="@") $attkey = strtoupper(substr($attkey,1));
            else $attkey = strtoupper($attkey);
            if (isset($tmpval[0])) $tmpval.="<br>";
            $tmpval.=$attkey." : ";
            if (is_array($attval)) $tmpval.=json_encode($attval);
            else $tmpval.=$attval;
        }
        $value=$tmpval;
    }
    $valAttrib = "";
    switch($keyFix) {
        case "REGIMENFISCAL": // 3.2 Es Elemento dentro de una secuencia.  3.3 Es Atributo
            $keyFix="REGIMEN FISCAL";
            if ($version==="3.3") {
                $valAttrib = " title=\"$value\"";
                $value = CatalogoSAT::getValue(CatalogoSAT::CAT_REGIMENFISCAL, "codigo", $value, "descripcion");
            }
            break;
        case "DOMICILIOFISCAL": $keyFix="DOMICILIO FISCAL"; break; // 3.2 Es Elemento. 3.3 No existe
        case "EXPEDIDOEN": $keyFix="EXPEDIDO EN"; break; // 3.2 Es Elemento. 3.3 No existe
    }
    echo "<tr><th>$keyFix</th><td$valAttrib>".strtoupper($value)."</td></tr>";
}
?>
            </table>
          </td>
          <td class="entity">
            <table width="100%" border="1">
              <tr><th colspan="2" class="h2">Receptor</th></tr>
<?php
foreach($receptor as $key=>$value) {
    if ($key[0]==="@") $keyFix = strtoupper(substr($key,1));
    else $keyFix = strtoupper($key);
    if (is_array($value)) { //$value = json_encode($value);
        $tmpval = "";
        foreach($value as $attkey=>$attval) {
            if ($attkey[0]==="@") $attkey = strtoupper(substr($attkey,1));
            else $attkey = strtoupper($attkey);
            if (isset($tmpval[0])) $tmpval.="<br>";
            $tmpval.=$attkey." : ";
            if (is_array($attval)) $tmpval.=json_encode($attval);
            else $tmpval.=$attval;
        }
        $value=$tmpval;
    }
    $valAttrib = "";
    switch($keyFix) {
        case "RESIDENCIAFISCAL": $keyFix="RESIDENCIA FISCAL"; break;
        case "NUMREGIDTRIB": $keyFix="NUM.REG.ID.TRIB."; break;
        case "USOCFDI": 
            $keyFix="USO CFDI"; 
            if ($version==="3.3") {
                $valAttrib = " title=\"$value\"";
                $value = CatalogoSAT::getValue(CatalogoSAT::CAT_USOCFDI, "codigo", $value, "descripcion");
            }
            break;
        CASE "DOMICILIO": break; // 3.2 Es Elemento dentro de una secuencia. 3.3 No existe
    }
    echo "<tr><th>$keyFix</th><td$valAttrib>".strtoupper($value)."</td></tr>";
}
?>
            </table>
          </td>
        </tr>
<?php
$conceptos = $cfdiObj->get("conceptos");
if (isset($conceptos["@claveprodserv"]) || isset($conceptos["@noidentificacion"])) $conceptos = [$conceptos];
if (!empty($conceptos)) {
?>
        <tr>
          <td colspan="2" width="100%">
            <table width="100%" border="1">
              <tr>
                <th class="shrinkCol invcell">Cantidad</th>
                <th class="shrinkCol invcell">Unidad</th>
                <th class="invcell">Descripcion</th>
                <th class="invcell" width="120">Precio</th>
                <th class="invcell" width="120">Importe</th>
              </tr>
<?php
    $conceptIdx=0;
    foreach ($conceptos as $concepto) {
        $cantidad = $concepto["@cantidad"];
        if (isset($concepto["@claveunidad"])) {
            $claveUnidad = $concepto["@claveunidad"];
            $nombreClaveUnidad = CatalogoSAT::getValue(CatalogoSAT::CAT_CLAVEUNIDAD, "codigo", $claveUnidad, "nombre");
        }
        if (isset($concepto["@unidad"])) {
            $unidad = htmlentities($concepto["@unidad"]);
            if (isset($claveUnidad) && isset($nombreClaveUnidad))
                $titleUnidad = $claveUnidad." = ".$nombreClaveUnidad;
        } else if (isset($claveUnidad) && isset($nombreClaveUnidad)) {
            $unidad = htmlentities($nombreClaveUnidad);
            $titleUnidad = $claveUnidad;
        }
        $descripcion = htmlentities($concepto["@descripcion"]);
        $valorUnitario = +$concepto["@valorunitario"];
        $importe = $concepto["@importe"];
        $conceptIdx++;
?>
              <tr>
                <td class="invcell centered shrinkCol"><?= $cantidad ?></td>
                <td class="invcell centered shrinkCol" title="<?= $titleUnidad ?>"><?= $unidad ?></td>
                <td class="invcell"><?= $descripcion ?></td>
                <td class="invcell righted">$<?= number_format($valorUnitario,2) ?></td>
                <td class="invcell righted">$<?= number_format($importe,2) ?></td>
              </tr>
<?php
    }
?>
            </table>
          </td>
        </tr>
<?php
}
$subtotalStr = $cfdiObj->get("subtotal");
$subtotal = isset($subtotalStr[0])?+$subtotalStr:0;
?>
        <tr>
          <td colspan="2" align="right">
            <table border="1">
              <tr>
                <th width="120" class="h1">Subtotal</th>
                <td width="120" align="right" class="h1"><?= number_format($subtotal,2) ?></td>
              </tr>
<?php
$totalImpuestosTrasladadosStr = $cfdiObj->get("totalimpuestostrasladados");
$totalImpuestosTrasladados = isset($totalImpuestosTrasladadosStr[0])?+$totalImpuestosTrasladadosStr:0;
?>
              <tr>
                <th class="h1">IVA</th>
                <td align="right" class="h1" value="<?= $totalImpuestosTrasladadosStr ?>"><?= number_format($totalImpuestosTrasladados,2) ?></td>
              </tr>
<?php
$totalImpuestosRetenidosStr = $cfdiObj->get("totalimpuestosretenidos");
if (isset($totalImpuestosRetenidosStr[0])) {
    $totalImpuestosRetenidos = +$totalImpuestosRetenidosStr;
?>
              <tr>
                <th class="h3">IVA Ret.</th>
                <td align="right" class="h3"><?= number_format(-1*abs($totalImpuestosRetenidos),2) ?></td>
              </tr>
<?php
}
$totalStr = $cfdiObj->get("total");
$total = isset($totalStr[0])?(+trim($totalStr)):0;
?>
              <tr>
                <th>TOTAL</th>
                <td align="right" class="h2"><?= number_format($total,2) ?></td>
              </tr>
            </table>
          </td>
        </tr>
<?php
$timbre = $cfdiObj->get("TFD");
if (!empty($timbre)) {
?>
        <tr>
          <td colspan="2" width="100%">
            <table width="100%" border="1">
              <tr><th class="h2">UUID</th></tr>
              <tr><td align="center"><?= $timbre["@uuid"] ?></td></tr>
              <tr><th>Fecha Timbrado</th></tr>
              <tr><td align="center"><?= $timbre["@fechatimbrado"] ?></td></tr>
              <tr><th>Num. Certificado SAT</th></tr>
              <tr><td align="center"><?= $timbre["@nocertificadosat"] ?></td></tr>
            </table>
          </td>
        </tr>
<?php
}
?>
      </table>
    </div>
    <center>
      Este documento es una impresion de un comprobante fiscal digital
    </center>
<?php
if ($isAdmin || $isSysOp || $isCorpOp || ($isPrvUsr && !empty($fData["status"]) && in_array($fData["status"], ["Pendiente","Aceptado","Contrarrecibo","Exportado","ExpSinContra"],true))) {
?>
    <div id="agregaPdfDiv" class="noprint">
      <form method="post" name="formaAgregaPDF" id="formaAgregaPDF" action="templates/factura.php" target="_self" onsubmit="return agregaPDF();">
        <fieldset name="fldset" value="notPrinted"><legend>Recuadro no incluido en impresión</legend>
          <input type="hidden" name="id" value="<?= $idFactura ?>">
          <input type="hidden" name="nombre" value="<?= $nombreInterno ?>">
<?php if(!empty($folio)) { ?> <input type="hidden" name="folio" value="<?= $folio ?>"> <?php } ?>
<?php if(!empty($rfc_emisor)) { ?> <input type="hidden" name="rfc_emisor" value="<?= $rfc_emisor ?>"> <?php } ?>
<?php if(!empty($fData["ubicacion"])) { ?> <input type="hidden" name="ubicacion" value="<?= $fData["ubicacion"] ?>"> <?php } ?>
          Ingrese factura (Archivo PDF) : <input type="file" name="appendpdffile" id="appendpdffile" class="highlight" onchange="selectedPDF();">
          <input type="submit" name="AnexarPDF" id="AnexarPDF" value="Anexar PDF">
        </fieldset>
      </form>
      <div id="resultSubmit"></div>
    </div>
<?php
}
?>
  </body>
</html>
<?php
require_once "configuracion/finalizacion.php";
