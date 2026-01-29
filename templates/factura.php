<?php
require_once dirname(__DIR__)."/bootstrap.php";
$isValidUser = false;
$isAdmin = false;
$isSysop = false;
$isCorpOp = false;
$isPrvUsr = false;
$isDeveloper = false;
$baseurl=getBaseURL();
if (hasUser()) {
    $isAdmin = validaPerfil("Administrador");
    $isSysOp = validaPerfil("Sistemas");
    $isCorpOp = validaPerfil("Compras");
    $isPrvUsr = validaPerfil("Proveedor");
    $isValidUser = $isAdmin || $isSysOp;
    $isDeveloper = getUser()->nombre==="admin";
} else {
    $_COOKIE["menu_accion"]="";
    echo "<script>document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/invoice';window.location.replace('{$baseurl}');</script>";
    require_once "configuracion/finalizacion.php";
    return;
}
$log="";
if (isset($menu_accion[0])||isset($_POST["submitxml"])) {
    if (!$isValidUser) {
        $_COOKIE["menu_accion"]="";
        echo "<script>document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/invoice';window.location.replace('{$baseurl}');</script>";
        return;
    }
    if (isset($_POST["submitxml"])) {
        $file=$_FILES["xmlfiles"];
        $xmlname=$file["name"];
        $xmltemp=$file["tmp_name"];
        $xmltype=$file["type"];
        $xmlsize=$file["size"];
        $xmlerr=$file["error"];
        // toDo: Guardar el xml en archivos, mostrar layout tipo PDF construido a partir del XML
        //echo "<P>POST:<BR>".json_encode($_POST)."</P>";
        //echo "<P>FILES:<BR>".json_encode($_FILES)."</P>";
        require_once "clases/CFDI.php";
        //$baseurl=getBaseURL();
        //$nombre = getBasePath().$fData["ubicacion"].$nombreInterno.".xml";
        $xmlfixname=getBasePath().$xmlname; // ToDo: asegurar que getBasePath sea un path aceptable
        $errMsg="";
        $errStack="";
        $enough=true;
        $log="";
        $cfdiObj = CFDI::newInstanceByFileName($xmltemp, $xmlfixname, $errMsg, $errStack, $enough, $log);
        $log.="<br>\n_POST: ".json_encode($_POST)."<br>\n_FILES: ".json_encode($_FILES)."<br>\n";
        if (!$enough) $lastError=["errorMessage"=>$errMsg,"errorStack"=>$errStack,"log"=>$log];
    } else {
        $log.="\nFORMA NUEVA";
?>
    <h1 class="txtstrk">Formato Factura con XML</h1>
    <form method="post" name="forma_nueva" action="templates/factura.php" target="archivo" enctype="multipart/form-data" onsubmit="return isSubmit();">
      <div id="xml_selector" class="marginbottom nowrap" title="Seleccione un archivo XML.">XML: <input type="file" name="xmlfiles" id="xmlfiles" autofocus accept=".xml" onchange="checkChange()"> <input type="submit" name="submitxml" id="submitxml" value="Verificar" onclick="document.forma_nueva.submited=this.id;"></div>
      <div id="waiting-roll" class="hidden"><img src="<?=$waitImgName?>" width="360" height="360"></div>
    </form>
<?php
        return;
    }
} else {
    require_once "clases/Facturas.php";
    $invObj = new Facturas();
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
    }
    if (!isset($fData["nombreInterno"])) $fData = $fData[0];
    require_once "clases/CFDI.php";
    if (empty($fData)) {
        CFDI::clearLastError();
        $log.="<br>\n_POST: ".json_encode($_POST)."<br>\n_FILES: ".json_encode($_FILES)."<br>\n";
        $lastError=["errorMessage"=>"No existe la factura indicada","errorStack"=>"","log"=>$log];
    } else {
        $idFactura = $fData["id"];
        $nombreInterno = $fData["nombreInterno"];
        $ciclo = $fData["ciclo"];
        $ubicacion = $fData["ubicacion"];
        $baseurl=getBaseURL();
        $nombre = getBasePath().$fData["ubicacion"].$nombreInterno.".xml";
        $cfdiObj = CFDI::newInstanceByLocalName($nombre);
    }
}
if ($cfdiObj===null) {
    $lastError = CFDI::getLastError();
    if (!isset($lastError["errorMessage"][0])) {
        $log.="<br>\n_POST: ".json_encode($_POST)."<br>\n_FILES: ".json_encode($_FILES)."<br>\n";
        $lastError=["errorMessage"=>"Lectura de XML fallida (CFDI nulo)", "errorStack"=>"", "log"=>$log];
    }
} else {
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
        $_COOKIE["menu_accion"]="";
        echo "<script>document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';document.cookie='menu_accion=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/invoice';window.location.replace('{$baseurl}');</script>";
        require_once "configuracion/finalizacion.php";
        return; //die("Redirecting to /".$_project_name."/index.php");
    }
    require_once "clases/catalogoSAT.php";
    $version = $cfdiObj->get("version");
    $serie = $cfdiObj->get("serie");
    $folio = $cfdiObj->get("Folio");
    $fecha = $cfdiObj->get("fecha");
    $sello = $cfdiObj->get("sello");
    $conceptos = $cfdiObj->get("conceptos");
    $pagos = $cfdiObj->get("pagos");
    $subtotalStr = $cfdiObj->get("subtotal");
    $totalImpuestosTrasladadosStr = $cfdiObj->get("totalimpuestostrasladados");
    $totalImpuestosRetenidosStr = $cfdiObj->get("totalimpuestosretenidos");
    $totalStr = $cfdiObj->get("total");
    $timbre = $cfdiObj->get("TFD");
}
// ToDo: Cargar template 1 o 2 o 3
// ToDo: Usar llaves <%LLAVE%> para colocar valores específicos
// ToDo: Usar filas "<%TR ... /TR%>" ó "<%LI ... LI%>" ó "<%P ... /P%>"
// ToDo: Al rastrear filas, usar función recursiva que busque más filas que inicien antes del 
// ToDo: Agregar modulo SWITCH
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
  <body class="factura">
<?php
if (isset($lastError)) { ?>
    <ul>
      <li>Error Message: <?= $lastError["errorMessage"]??"NO MESSAGE" ?></li>
      <li>Error Stack:<hr><?= $lastError["errorStack"]??"NO STACK" ?></li>
      <li>Log: <pre class='wordwrap'><?= $lastError["log"]??"NO LOG" ?></pre></li>
    </ul>
<?php
} else { ?>
    <div id="contenedor">
      <table width="100%" margin="auto">
        <tr>
          <td colspan="2" align="right">
            <table border="1">
              <tr>
                <th class="h1">Version</th>
                <td align="center" class="h1"><?= $version ?><input type="hidden" id="version" value="<?= $version ?>"></td>
              </tr>
              <tr>
                <th class="h1">Serie</th>
                <td align="center" class="h1"><?= $serie ?><input type="hidden" id="serie" value="<?= $serie ?>"></td>
              </tr>
              <tr>
                <th class="h1">Folio</th>
                <td align="center" class="h1"><?= $folio ?><input type="hidden" id="folio" value="<?= $folio ?>"></td>
              </tr>
              <tr>
                <th class="h1">Fecha</th>
                <td align="center" class="h1"><?= $fecha ?><input type="hidden" id="fecha" value="<?= $fecha ?>"></td>
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
    $totalImpuestosTrasladados = isset($totalImpuestosTrasladadosStr[0])?+$totalImpuestosTrasladadosStr:0;
?>
              <tr>
                <th class="h1">IVA</th>
                <td align="right" class="h1" value="<?= $totalImpuestosTrasladadosStr ?>"><?= number_format($totalImpuestosTrasladados,2) ?></td>
              </tr>
<?php
    if (isset($totalImpuestosRetenidosStr[0])) {
        $totalImpuestosRetenidos = +$totalImpuestosRetenidosStr;
?>
              <tr>
                <th class="h3">IVA Ret.</th>
                <td align="right" class="h3"><?= number_format(-1*abs($totalImpuestosRetenidos),2) ?></td>
              </tr>
<?php
    }
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
    }
    if (isset($pagos["@monto"])) $pagos=[$pagos];
    if (!empty($pagos) && $isDeveloper) {
        foreach($pagos as $pagoIdx=>$pago) {
            echo "<!-- PAGO $pagoIdx: ".json_encode($pago)." -->";
            $formapagop=$pago["@formadepagop"];
            $fechapagop=$pago["@fechapago"];
            $numOperp=$pago["@numoperacion"]??"";
            $numOperpCap=isset($numOperp[0])?"<B>Número operacion</B>: ":"";
            $monedap=$pago["@monedap"];
            $montop=$pago["@monto"];
?>
        <tr><td colspan="2" width="100%"><B>Información del pago</B></td></tr>
        <tr>
            <td><B>Forma de pago</B>: <?=$formapagop?></td>
            <td><B>Fecha de pago</B>: <?=$fechapagop?></td>
        </tr>
        <tr>
            <td><?=$numOperpCap.$numOperp?></td>
            <td><B>Moneda de pago</B>: <?=$monedap?></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><B>Monto</B>: <?=$montop?></td>
        </tr>
<?php
            $doctos=$pago["DoctoRelacionado"];
            if (isset($doctos["@iddocumento"])) $doctos=[$doctos];
            foreach($doctos as $doctoIdx=>$docto) {
                ;
            }
        }
    }
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
    if (isset($idFactura) && ($isAdmin || $isSysOp || $isCorpOp || ($isPrvUsr && !empty($fData["status"]) && in_array($fData["status"], ["Pendiente","Aceptado","Contrarrecibo","Exportado","ExpSinContra"],true)))) { ?>
    <div id="agregaPdfDiv" class="noprint">
      <form method="post" name="formaAgregaPDF" id="formaAgregaPDF" action="templates/factura.php" target="_self" onsubmit="return agregaPDF();">
        <fieldset name="fldset" value="notPrinted"><legend>Recuadro no incluido en impresión</legend>
          <input type="hidden" name="id" value="<?= $idFactura ?>">
          <input type="hidden" name="nombre" value="<?= $nombreInterno ?>">
<?php
        if(!empty($folio)) { ?> <input type="hidden" name="folio" value="<?= $folio ?>"> <?php } ?>
<?php
        if(!empty($rfc_emisor)) { ?> <input type="hidden" name="rfc_emisor" value="<?= $rfc_emisor ?>"> <?php } ?>
<?php
        if(!empty($fData["ubicacion"])) { ?> <input type="hidden" name="ubicacion" value="<?= $fData["ubicacion"] ?>"> <?php } ?>
          Ingrese factura (Archivo PDF) : <input type="file" name="appendpdffile" id="appendpdffile" class="highlight" onchange="selectedPDF();">
          <input type="submit" name="AnexarPDF" id="AnexarPDF" value="Anexar PDF">
        </fieldset>
      </form>
      <div id="resultSubmit"></div>
    </div>
<?php
    }?>
  </body>
</html>
<?php
    require_once "configuracion/finalizacion.php";
    die();
}
