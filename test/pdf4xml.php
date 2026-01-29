<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once("clases/CFDI.php");
$tmpPath="C:/InvoiceCheckShare/tmp/";

$lastError=[];
$displayVersion=$_POST["version"];
echo "<!-- Display Version: $displayVersion -->";
//echo "<!-- POST:\n".arr2str($_POST)."\n -->";
if (isset($_FILES["archivo"])) {
    $basepath = $_SERVER['HTTP_ORIGIN'].$_SERVER['WEB_MD_PATH'];
    $file=$_FILES["archivo"];

    move_uploaded_file($file["tmp_name"], $tmpPath.$file["name"]);
    chmod($tmpPath.$file["name"], 0777);
    require_once "clases/CFDI.php";
    $cfdiObj = CFDI::newInstanceByLocalName($tmpPath.$file["name"]);
    if ($cfdiObj===null) {
        $lastError = CFDI::getLastError();
?>
<!DOCTYPE html>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $basepath ?>" target="_blank">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= substr($file[name],0,-4) ?></title>
    <link href="css/factura.php" rel="stylesheet" type="text/css">
  </head>
  <body>
    <ul>
      <li><A href="xml2pdf.php" class="asBtn">Regresar</A></li>
      <li>Error Message: <?= $lastError["errorMessage"] ?></li>
      <li>Error Stack:<hr><?= $lastError["errorStack"] ?></li>
      <li>Log: <pre class='wordwrap'><?= $lastError["log"] ?></pre></li>
    </ul>

  </body>
</html>
<?php            
        die();
    }

    require_once "clases/catalogoSAT.php";
    function fixkey($key) {
        switch($key) {
            case "REGIMENFISCAL": return "REGIMEN FISCAL";
            case "DOMICILIOFISCAL": return "DOMICILIO FISCAL";
            case "EXPEDIDOEN": return "EXPEDIDO EN";
            case "RESIDENCIAFISCAL": return "RESIDENCIA FISCAL";
            case "NUMREGIDTRIB": return "NUM.REG.ID.TRIB.";
            case "USOCFDI": return "USO CFDI";
        }
        return $key;
    }
    function getDescription($key,$value) {
        switch($key) {
            case "REGIMENFISCAL": return CatalogoSAT::getValue(CatalogoSAT::CAT_REGIMENFISCAL, "codigo", $value, "descripcion");
            case "USOCFDI": return CatalogoSAT::getValue(CatalogoSAT::CAT_USOCFDI, "codigo", $value, "descripcion");
        }
        return "";
    }
    function getEntityData($entity) { // se asume version 3.3
        $resultData=[];
        echo "<!-- Entity ".json_encode($entity)." -->";
        foreach ($entity as $key => $value) {
            $key=strtoupper($key[0]==="@"?substr($key,1):$key);
            $item=["name"=>fixkey($key)];
            if (is_array($value)) {
                $item["value"]="";
                foreach ($value as $attkey => $attval) {
                    if (isset($item["value"][0])) $item["value"].="<br>";
                    $vkyFix=strtoupper($attkey[0]==="@"?substr($attkey,1):$attkey);
                    $item["value"].=$vkyFix.":".is_array($attval)?http_build_query($attval):$attval;
                }
            } else {
                $item["value"]=$value;
            }
            $desc=getDescription($key,$value);
            if (isset($desc[0])) {
                $item["title"]=$item["value"];
                $item["value"]=$desc;
            }
            $resultData[]=$item;
        }
        echo "<!-- Result ".json_encode($resultData)." -->";
        return $resultData;
    }
    function getHtmlRowsScript($entity) {
        $retval="";
        foreach(getEntityData($entity) as $item) {
            $retval.= "<tr><th>$item[name]</th><td";
            if(isset($item["title"][0])) $retval.=" title=\"$item[title]\"";
            $retval.=">".strtoupper($item["value"])."</td></tr>";
        }
        return $retval;
    }
    $version = $cfdiObj->get("version");
    $emisor = $cfdiObj->get("Emisor");
    $emisorRows=getHtmlRowsScript($emisor);
    $receptor = $cfdiObj->get("Receptor");
    $receptorRows=getHtmlRowsScript($receptor);
    $folio = $cfdiObj->get("Folio");
    $serie = $cfdiObj->get("serie");
    $fecha = $cfdiObj->get("fecha");
?>
<!DOCTYPE html>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $basepath ?>" target="_blank">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= substr($file["name"],0,-4) ?></title>
    <link href="css/factura.php" rel="stylesheet" type="text/css" />
  </head>
  <body class="blank">
    <div id="contenedorCFDI">
<?php
    if ($displayVersion==="BASICA") {
?>
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
              <tr><th colspan="2" class="h2">Emisor</th></tr><?= $emisorRows ?>
            </table>
          </td>
          <td class="entity">
            <table width="100%" border="1">
              <tr><th colspan="2" class="h2">Receptor</th></tr><?= $receptorRows ?>
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
                $cantidad = 0+$concepto["@cantidad"];
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
                <td class="invcell righted">$ <?= number_format($valorUnitario,2) ?></td>
                <td class="invcell righted">$ <?= number_format($importe,2) ?></td>
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
                <td width="120" align="right" class="h1">$ <?= number_format($subtotal,2) ?></td>
              </tr>
<?php
        $totalImpuestosTrasladadosStr = $cfdiObj->get("totalimpuestostrasladados");
        $totalImpuestosTrasladados = isset($totalImpuestosTrasladadosStr[0])?+$totalImpuestosTrasladadosStr:0;
?>
              <tr>
                <th class="h1">IVA</th>
                <td align="right" class="h1" value="<?= $totalImpuestosTrasladadosStr ?>">$ <?= number_format($totalImpuestosTrasladados,2) ?></td>
              </tr>
<?php
        $totalImpuestosRetenidosStr = $cfdiObj->get("totalimpuestosretenidos");
        if (isset($totalImpuestosRetenidosStr[0])) {
            $totalImpuestosRetenidos = +$totalImpuestosRetenidosStr;
?>
              <tr>
                <th class="h3">IVA Ret.</th>
                <td align="right" class="h3">$ <?= number_format(-1*abs($totalImpuestosRetenidos),2) ?></td>
              </tr>
<?php
        }
        $totalStr = $cfdiObj->get("total");
        $total = isset($totalStr[0])?+$totalStr:0;
?>
              <tr>
                <th>TOTAL</th>
                <td align="right" class="h2">$ <?= number_format($total,2) ?></td>
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
      <center>
        Este documento es una representaci&oacute;n impresa de un CFDI
      </center>
<?php
    } else if ($displayVersion==="PG") {
?>
      <table id="pg-block1">
        <tr>
          <td colspan="2" width="100%">
          </td>
        </tr>
      </table>
<?php
    }
?>
    </div>
  </body>
</html>
<?php
    //die();
}
/*
?>
<html>
    <body>
        <H1>POST</H1>
<?= arrechoLiteUL($_POST) ?>
        <H1>FILE</H1>
<?= arrechoLiteUL($_FILES) ?>
    </body>
</html>
*/
