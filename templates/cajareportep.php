<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$esAdmin=validaPerfil("Administrador");
$esSistemas=validaPerfil("Sistemas")||$esAdmin;
$esReporte=validaPerfil("Caja Reporte")||$esSistemas;
$esRespalda=validaPerfil("Caja Respaldo")||$esSistemas;
if(!$esReporte && !$esRespalda) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
//$_POST["accion"]="generaReporte";
//$_POST["empresaId"]="2";
//$_POST["tipofecha"]="solicitud";
//$_POST["tipo"]="cajachica";
//$_POST["status"]="respaldado"; // todos,pendiente,aceptado,pagado,rechazado,respaldohoy,respaldado
//$_POST["statusModifier"]="NOT";
//$_POST["fechaIni"]="01/03/2020";
//$_POST["fechaFin"]="30/08/2020";
//$_POST["modoImpresion"]="1";
clog2ini("templates.cajareportep");
clog1seq(1);
$browser = getBrowser();
$isMSIE = ($browser==="Edge" || $browser==="IE");
require_once "templates/generalScript.php";

$dia = date('j');
$mes = date('n');
$anio = date('Y');
$maxdia = date('t');
$fmtDay0 = "01/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
$fmtDay = str_pad($dia,2,"0",STR_PAD_LEFT)."/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;

$folio=$_POST["folio"]??"";
$tipoList=["todos"=>"TODOS","cajachica"=>"CAJA CHICA","viaticos"=>"VIÁTICOS"];
$tipo=$tipoList[$_POST["tipo"]]??"TODOS";
$empresaId=$_POST["empresaId"]??"";
if (isset($empresaId[0])) {
    global $gpoObj;
    if (!isset($gpoObj)) {
        require_once "clases/Grupo.php";
        $gpoObj=new Grupo();
    }
    $empresa=$gpoObj->getValue("id",$empresaId,"alias");
    $repLogoAttribs=isset($empresa[0])?" style=\"height: 100px; margin: 10px; background-position: center; background-size: contain; background-repeat: no-repeat; background-image: url(imagenes/logos/".mb_strtolower($empresa).".png);\"":"";
}
$tipofechaList=["solicitud"=>"SOLICITUD","pago"=>"PAGO"];
$tipofecha=$tipofechaList[$_POST["tipofecha"]]??"SOLICITUD";
$statusList=["todos"=>"TODOS","pendiente"=>"PENDIENTE","aceptado"=>"AUTORIZADO","pagado"=>"PAGADO","rechazado"=>"RECHAZADO","respaldadohoy"=>"RESPALDADO HOY","respaldado"=>"RESPALDADO"];
$status=$statusList[$_POST["status"]]??"TODOS";
$statusModifier=$_POST["statusModifier"]??"";
//if (isset($status[0]) && isset($statusModifier[0])) $status="NO $status"; // Sólo confunde, el objetivo era mostrar las facturas que aún no se han respaldado, pero con la intención de respaldarlas. Por ello el reporte impreso debe mostrar status RESPALDADO en lugar de NO RESPALDADO. Ya me habían hecho comentarios pensando que aun no habian sido respaldadas.
$fechaIni=$_POST["fechaIni"]??$fmtDay0;
$beneficiario=$_POST["beneficiario"]??"TODOS";
$fechaFin=$_POST["fechaFin"]??$fmtDay;
if (isset($folio[0])) {
    unset($_POST["empresaId"]);
    unset($_POST["tipofecha"]);
    unset($_POST["status"]);
    unset($_POST["statusModifier"]);
    unset($_POST["beneficiario"]);
    unset($_POST["fechaIni"]);
    unset($_POST["fechaFin"]);
}
$resultado="";
$cookieFile=dirname(__FILE__)."/cookie.txt";
$options = [
    CURLOPT_URL=>"$_SERVER[HTTP_ORIGIN]$_SERVER[WEB_MD_PATH]consultas/CajaChica.php",
    CURLOPT_HEADER=>false,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>http_build_query($_POST),
    //CURLOPT_COOKIESESSION=>TRUE,
    //CURLOPT_COOKIEJAR=>$cookieFile,
    //CURLOPT_COOKIEFILE=>$cookieFile,
    CURLOPT_COOKIE=>session_name()."=".session_id(),
    //CURLOPT_USERAGENT=>getBrowser("userAgent"),
    CURLOPT_CONNECTTIMEOUT=>180,
    CURLOPT_TIMEOUT=>300
];
session_write_close();
$ch=curl_init();
curl_setopt_array($ch, $options);
$output = curl_exec($ch);
if (curl_error($ch)) {
    $resultado.="<p>ERROR AL EXTRAER INFORMACION</p><!-- ".curl_errno($ch).": ".curl_error($ch)." -->";
}
curl_close($ch);
if (isset($output[0])) {
    $output=str_replace("'", "&apos;", $output);
    $resultData = json_decode($output,true);
    if ($resultData["result"]==="success"||$resultData["result"]==="exito") {
        for ($i=0; isset($resultData["datos"][$i]); $i++) {
            if(isset($resultData["datos"][$i]["html"])) unset($resultData["datos"][$i]["html"]);
        }
        if (isset($resultData["parameters"]["query"])) unset($resultData["parameters"]["query"]);
        if (isset($resultData["query"])) unset($resultData["query"]);
        $output=json_encode($resultData);
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <?= $isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title><?= $systemTitle ?></title>
<?php
if ($isMSIE) echoPolyfillScript();
echoGeneralScript();
?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
        var isOK=true;
        var postVal='<?= json_encode($_POST) ?>';
        try {
            var jsParams=JSON.parse(postVal);
        } catch (ex) {
            console.log("POST EXCEPTION: ",ex,postVal);
            isOK=false;
        }
        var jsState=4;
        var jsStatus=200;
        var jsResult='<?= $output ?>';
        console.log("PARAMETERS = ",jsParams);
        function printAndClose() {
            if (isOK) {
                resultado(jsResult,jsParams,jsState,jsStatus);
                setTimeout(function () {
                    window.print();
                }, 100);
                window.onfocus = function () {
                    setTimeout(function () {
                        <?= $esAdmin?"":"window.close();"?>
                    }, 100);
                }
                window.document.body.onfocus = function () {
                    <?= $esAdmin?"":"window.close();"?>
                }
                window.onafterprint = function() {
                    <?= $esAdmin?"":"window.close();"?>
                }
            } <?= $esAdmin?"":"else window.close();"?>
        }
    </script>
    <script src="scripts/cajareporte.php" type="text/javascript"></script>
    <script src="scripts/calendar_conf.js" type="text/javascript"></script>
  </head>
  <body class="blank basefont" onload="printAndClose();">
    <div id="area_generalp" class="centralp">
      <div id="repLogoP"<?= $repLogoAttribs ?>></div>
      <h1 class="txtstrk alignCenter">Reporte de Reembolso de Caja Chica y Viáticos</h1>
      <table class="centered screen tabla_caja fontMedium breakAvoidI">
        <tbody class="lefted">
          <tr>
            <th>FOLIO:</th>
            <td><input type="text" id="bfolio" class="padv02 blacked notBorder wid100px" placeholder="TODOS"<?= isset($folio[0])?" value=\"$folio\"":"" ?> readonly></td>
            <th>EMPRESA:</th>
            <td class="vAlignCenter"><input type="text" id="bempresa" class="padv02 blacked notBorder wid140px" placeholder="TODAS"<?= isset($empresa[0])?" value=\"$empresa\"":"" ?> readonly></td>
            <th class="nowrap">FECHA TIPO:</th>
            <td><input type="text" id="tipofecha" class="padv02 blacked notBorder wid100px" placeholder="SOLICITUD"<?= isset($tipofecha[0])?" value=\"$tipofecha\"":"" ?> readonly></td>
          </tr>
          <tr>
            <th>TIPO:</th>
            <td><input type="text" id="tipo" class="padv02 blacked notBorder wid100px" placeholder="TODOS"<?= isset($tipo[0])?" value=\"$tipo\"":"" ?> readonly></td>
            <th>STATUS:</th>
            <td class="vAlignCenter"><input type="text" id="status" class="padv02 blacked notBorder wid140px" placeholder="TODOS"<?= isset($status[0])?" value=\"$status\"":"" ?> readonly></td>
            <th class="nowrap">FECHA INI:</th>
            <td><input type="text" id="fechaIni" readonly class="padv02 blacked notBorder wid100px" value="<?= $fechaIni ?>"></td>
          </tr>
          <tr>
            <td colspan="4"><B>BENEFICIARIO:</B> &nbsp; <input type="text" id="beneficiario" class="wid270px blacked padv02 notBorder" placeholder="TODOS"<?= isset($beneficiario[0])?" value=\"$beneficiario\"":"" ?> readonly/></td>
            <th class="nowrap">FECHA FIN:</th>
            <td><input type="text" id="fechaFin" readonly class="padv02 blacked notBorder wid100px" value="<?= $fechaFin ?>"></td>
          </tr>
        </tbody>
      </table>
      <div id="resultado" class="resultarea centered fontMedium"></div>
      <div class="all_space marginT7 btop2d padt3 breakAvoidI">
        <span id="numReg" class="fltL padL20"></span>
        <span id="sumTot" class="fltR padR20"></span>
      </div>
      <div class="clear"></div>
    </div>
  </body>
</html>
<?php
clog1seq(-1);
clog2end("templates.cajareportep");
