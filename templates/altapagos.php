<?php
if(!hasUser()||(!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Alta Pagos"))) {
    if (hasUser()) {
      setcookie("menu_accion", "", time() - 3600);
      setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("templates.altapagos");
clog1seq(1);

$bsPyPth="C:\\InvoiceCheckShare\\";
function cfdlog($text,$hasPrefix=false) {
  global $bsPyPth;
  $fmt = (new DateTime())->format("yMd H:i:s");
  if ($hasPrefix&&filesize("{$bsPyPth}cfdis.log")>0) $prefix="-----";
  else $prefix="";
  if (!isset($text[0])) {
    if (!isset($prefix[0])) return true;
    return file_put_contents("{$bsPyPth}cfdis.log",$prefix, FILE_APPEND | LOCK_EX);
  } else return file_put_contents("{$bsPyPth}cfdis.log","{$prefix}[$fmt PROC] $text".PHP_EOL, FILE_APPEND | LOCK_EX);
  return file_put_contents("{$bsPyPth}cfdis.log","{$prefix}[$fmt PROC] $text".PHP_EOL, FILE_APPEND|LOCK_EX);
}
if(isset($_FILES))clog2("FILES:\n".arr2str($_FILES));
$nuevos=0; $procesables=0;
if (isset($_FILES["cfdis"])) {
  $cfdis=$_FILES["cfdis"];
  if (isset($cfdis["name"][0])) cfdlog("",true);
  for($i=0; isset($cfdis["name"][$i]); $i++) {
    $cfdname=$cfdis["name"][$i]; $cfdsize=$cfdis["size"][$i]; $cfdtmpn=$cfdis["tmp_name"][$i];
    $cfderrn=$cfdis["error"][$i]; $cfdtype=$cfdis["type"][$i];
    if ($cfdsize==0) cfdlog("ERROR: Tamaño de archivo $cfdname es cero.");
    else if (!isset($cfdtmpn[0])) cfdlog("ERROR: Carga de archivo $cfdname no identificada.");
    else if ($cfderrn!==UPLOAD_ERR_OK) {
      switch($cfderrn) {
        case UPLOAD_ERR_INI_SIZE: cfdlog("ERROR: El archivo $cfdname excede el tamaño máximo permitido por el servidor."); break;
        case UPLOAD_ERR_FORM_SIZE: cfdlog("ERROR: El archivo $cfdname excede el tamaño máximo permitido por el navegador."); break;
        case UPLOAD_ERR_PARTIAL: cfdlog("ERROR: La carga del archivo $cfdname se interrumpió."); break;
        case UPLOAD_ERR_NO_FILE: cfdlog("ERROR: No se encontró el archivo $cfdname."); break;
        case UPLOAD_ERR_NO_TMP_DIR: cfdlog("ERROR: No está definida la carpeta de descarga de archivos."); break;
        case UPLOAD_ERR_CANT_WRITE: cfdlog("ERROR: No está autorizada la descarga de archivos."); break;
        case UPLOAD_ERR_EXTENSION: cfdlog("ERROR: La descarga de archivos está bloqueada por una extensión."); break;
        default: cfdlog("ERROR: Falló la descarga del archivo $cfdname.");
      }
    } else if (move_uploaded_file($cfdtmpn, "{$bsPyPth}CFDIs\\$cfdname")) {
      $nuevos++;
      //$procesables++;
      // Solo XML y PDF contarán como procesables, los PDF siempre y cuando el nombre coincida con un xml.
      cfdlog("EXITO: Archivo $cfdname $cfdtype recibido.");
    } else {
      cfdlog("ERROR: Error al mover el archivo $cfdname a la carpeta de CFDIs");
    }
  }
}
?>
  <div id="area_alta" class="central">
    <h1 class="txtstrk">Carga de Complementos de Pago</h1>
    <div id="area_alta_contenido" class="contenido">
      <form method="post" name="forma_alta" target="_self" enctype="multipart/form-data" class="oneLine">
        <input type="hidden" name="menu_accion" value="Alta Pagos">
        <input type="file" name="cfdis[]" id="cfdis" multiple class="highlight">
        <input type="submit" value="Enviar">
      </form>
      <div class="lessTwoLines scrollauto">
        <fieldset class="screen martop10">
          <legend align="left" class="uppercase boldValue">Archivos: 
            <span class="tabBtn btnFX selected" onclick="clrem(lbycn('tabBtn'),'selected');cladd(this,'selected');cladd(lbycn('archivo'),'hidden');clrem(lbycn('proceso'),'hidden');">En Proceso</span>
<?php
if (validaPerfil("Administrador")||validaPerfil("Sistemas"))
  $tablist=["aceptados","rechazados","fallidos","reintentar","ignorados"];
else $tablist=["aceptados","rechazados"];
foreach ($tablist as $tabkey) {
?>
            <span class="tabBtn btnFX" onclick="clrem(lbycn('tabBtn'),'selected');cladd(this,'selected');cladd(lbycn('archivo'),'hidden');clrem(lbycn('<?= $tabkey ?>'),'hidden');"><?= ucfirst($tabkey) ?></span>
<?php
} ?>
          </legend>
          <table class="width100"><thead><tr><th class="shrinkCol">&nbsp;</th><th class="lefted">Archivo</th><th class="righted">Tama&ntilde;o</th></tr></thead><tbody id="fileListBody">
<?php
$sizeunits="BKMGTP";
$procCount=0;
foreach (glob("{$bsPyPth}CFDIs\\*.*") as $filename) { // .xml
    $filebytes=filesize($filename);
    $filefactor=intval((strlen("".$filebytes)-1)/3);
    $powfactor=pow(1024,$filefactor);
    $fileunits=@$sizeunits[$filefactor].($filefactor>0?"B":"yte");
    $filesizeh=sprintf("%.2f", $filebytes/$powfactor).$fileunits;
    $fileIdx=strrpos($filename, "\\"); // quitar path
    if ($fileIdx!==false) $filename=substr($filename, $fileIdx+1);
    $procCount++;
    echo "<tr class=\"archivo proceso\"><td class=\"top shrinkCol\">&nbsp;&bull;&nbsp;</td><td class=\"lefted\">$filename</td><td class=\"righted vATBtm\">$filesizeh</td></tr>";
    /*
    // buscar pdf
    $extIdx=strrpos($filename, ".");
    $fileTmp=substr($filename, 0, $extIdx);
    $pdfIdx=strpos($fileTmp, "_");
    $pdfname=substr($fileTmp, $pdfIdx+1).substr($fileTmp, 0, $pdfIdx).".pdf";
    if (file_exists("{$bsPyPth}CFDIs\\$pdfname")) {
      $filebytes=filesize("{$bsPyPth}CFDIs\\$pdfname");
      $filefactor=floor((strlen($filebytes)-1)/3);
      $powfactor=pow(1024,$filefactor);
      $fileunits=@$sizeunits[$filefactor].($filefactor>0?"B":"");
      $filesizeh=sprintf("%.2f", $filebytes/$powfactor).$fileunits;
      $procCount++;
      echo "<tr class=\"archivo proceso\"><td class=\"top shrinkCol\">&nbsp;&bull;&nbsp;</td><td class=\"lefted\">$pdfname</td><td class=\"righted vATBtm\">$filesizeh</td></tr>";
    } else if (file_exists("{$bsPyPth}CFDIs\\{$fileTmp}.pdf")) {
      $filebytes=filesize("{$bsPyPth}CFDIs\\{$fileTmp}.pdf");
      $filefactor=floor((strlen($filebytes)-1)/3);
      $powfactor=pow(1024,$filefactor);
      $fileunits=@$sizeunits[$filefactor].($filefactor>0?"B":"");
      $filesizeh=sprintf("%.2f", $filebytes/$powfactor).$fileunits;
      $procCount++;
      echo "<tr class=\"archivo proceso\"><td class=\"top shrinkCol\">&nbsp;&bull;&nbsp;</td><td class=\"lefted\">{$fileTmp}.pdf</td><td class=\"righted vATBtm\">$filesizeh</td></tr>";
    }
    */
}
$tabCount=[];
foreach ($tablist as $tabkey) {
    $tabCount[$tabkey]=0;
    foreach (glob("{$bsPyPth}{$tabkey}\\*.*") as $filename) {
        $filebytes=filesize($filename);
        $filefactor=intval((strlen("".$filebytes)-1)/3);
        $powfactor=pow(1024,$filefactor);
        $fileunits=@$sizeunits[$filefactor].($filefactor>0?"B":"yte");
        $filesizeh=sprintf("%.2f", $filebytes/$powfactor).$fileunits;
        $fileIdx=strrpos($filename, "\\"); // quitar path
        if ($fileIdx!==false) $filename=substr($filename, $fileIdx+1);
        $tabCount[$tabkey]++;
        echo "<tr class=\"archivo $tabkey hidden\"><td class=\"top shrinkCol\">&nbsp;&bull;&nbsp;</td><td class=\"lefted\">$filename</td><td class=\"righted vATBtm\">$filesizeh</td></tr>";
    }
}
?>
      </tbody>
      <tfoot id="fileListFooter" class="boldValue lefted"><tr><td></td><td colspan="2" id="fileListReview">
        <span class="archivo proceso"><?= "$procCount archivo".($procCount==1?"":"s") ?> en proceso.</span>
<?php
foreach ($tablist as $tabkey) {
    $keyCount=$tabCount[$tabkey];
?>
        <span class="archivo <?=$tabkey?> hidden"><?= "$keyCount archivo".($keyCount==1?"":"s") ?> <?=$tabkey?>.</span>
<?php
}
?>
      </td></tr></tfoot>
    </table>
  </fieldset>
  <fieldset class="screen martop5">
    <legend align="left" class="uppercase boldValue">Listado de actividad reciente</legend>
    <div id="log_contents"><?= file_get_contents("{$bsPyPth}cfdis.log") ?></div>
  </fieldset><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="separateBlocks();ekil(this);">
<?php /* COMENTADO
<pre class="lefted nomargin" id="log_contents">< ? = file _ get _ contents ( "{$bsPyPth}cfdis.log" ) ? ></pre>


<ul class="hybull"><li>Sección para ingresar comprobantes de pago que se irán procesando internamente.</li>
<li>Los comprobantes sin contratiempos se moverán a la carpeta de facturas correspondiente y los datos se agregarán en las tablas correspondientes.</li>
<li>Los comprobantes con errores criticos se moverán a una carpeta rechazados.</li>
<li>Los comprobantes con errores temporales se quedarán en la misma carpeta para ser evaluados nuevamente.</li>
<li>Cada período de tiempo se obtendrá primero la lista completa de archivos xml y se validarán y agregarán al portal (verificando si existe el pdf con el mismo nombre para anexarlo también) con las mismas reglas que si se hubieran registrado como ALTA DE FACTURAS Y PAGOS.</li></ul>
  */
?>
      </div>
      <div class="footOneLine">
        <button type="button" onclick="cleanLogFile();">Limpiar</button>
      </div>
    </div>
  </div>
<?php
//file_get_contents("");
clog1seq(-1);
clog2end("templates.altapagos");
