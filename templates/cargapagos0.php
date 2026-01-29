<?php
if(!hasUser()||(!validaPerfil("Administrador")&&!validaPerfil("Sistemas")&&!validaPerfil("Carga Egresos"))) {
    if (hasUser()) {
      setcookie("menu_accion", "", time() - 3600);
      setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("templates.cargapagos");
clog1seq(1);

$bsPyPth="C:\\InvoiceCheckShare\\";
function plog($text,$hasPrefix=false) {
    global $bsPyPth;
    $fmt = (new DateTime())->format("yMd H:i:s");
    if ($hasPrefix&&filesize("{$bsPyPth}pagos.log")>0) $prefix="-----";
    else $prefix="";
    if (!isset($text[0])) {
        if (!isset($prefix[0])) return true;
        return file_put_contents("{$bsPyPth}pagos.log",$prefix, FILE_APPEND | LOCK_EX);
    } else return file_put_contents("{$bsPyPth}pagos.log","{$prefix}[$fmt] $text".PHP_EOL, FILE_APPEND | LOCK_EX);
}
$nuevos=0; $procesables=0;
if (isset($_FILES["pagos"])) {
    $pagos=$_FILES["pagos"];
    if (isset($pagos["name"][0])) plog("",true);
    for($i=0; isset($pagos["name"][$i]); $i++) {
        $pname=$pagos["name"][$i]; $psize=$pagos["size"][$i]; $ptmpn=$pagos["tmp_name"][$i];
        $perrn=$pagos["error"][$i]; $ptype=$pagos["type"][$i];
        if ($psize==0) plog("ERROR: Tamaño de archivo $pname es cero.");
        else if (!isset($ptmpn[0])) plog("ERROR: Carga de archivo $pname no identificada.");
        else if ($perrn!==UPLOAD_ERR_OK) {
            switch($perrn) {
                case UPLOAD_ERR_INI_SIZE: plog("ERROR: El archivo $pname excede el tamaño máximo permitido por el servidor."); break;
                case UPLOAD_ERR_FORM_SIZE: plog("ERROR: El archivo $pname excede el tamaño máximo permitido por el navegador."); break;
                case UPLOAD_ERR_PARTIAL: plog("ERROR: La carga del archivo $pname se interrumpió."); break;
                case UPLOAD_ERR_NO_FILE: plog("ERROR: No se encontró el archivo $pname."); break;
                case UPLOAD_ERR_NO_TMP_DIR: plog("ERROR: No está definida la carpeta de descarga de archivos."); break;
                case UPLOAD_ERR_CANT_WRITE: plog("ERROR: No está autorizada la descarga de archivos."); break;
                case UPLOAD_ERR_EXTENSION: plog("ERROR: La descarga de archivos está bloqueada por una extensión."); break;
                default: plog("ERROR: Falló la descarga del archivo $pname.");
            }
        } else if ($ptype!=="text/plain") {
            plog("ERROR: El archivo $pname no es de tipo texto.");
        } else {
            // if procesa pagos
            $lines=file($ptmpn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $dateLineResult=preg_match('/^(\d+) de (\w+) de (\d+)$/',trim($lines[0]),$matches);
            if($dateLineResult===1) {
                $dia=$matches[1];
                if(!isset($dia[1])) $dia="0$dia";
                $mes=strtolower(substr($matches[2],0,3));
                if(!isset($meses)) $meses=["ene"=>"01","feb"=>"02","mar"=>"03","abr"=>"04","may"=>"05","jun"=>"06","jul"=>"07","ago"=>"08","sep"=>"09","oct"=>"10","nov"=>"11","dic"=>"12"];
                if (isset($meses[$mes])) $mes=$meses[$mes];
                $anio=substr($matches[3],2,2);
                $rfc=trim($lines[4]);
                if(!isset($gpoObj)) {
                    require_once "clases/Grupo.php";
                    $gpoObj=new Grupo();
                }
                $alias=$gpoObj->getValue("rfc",$rfc,"alias");
                if(!isset($alias[0])) $alias=$rfc."_";
                $pname=$alias.$anio.$mes.$dia.".txt";
            }
            if (move_uploaded_file($ptmpn, "{$bsPyPth}PAGOS\\$pname")) {
                $nuevos++;
                $msg="EXITO: Archivo $ptype ";
                if ($pname===$pagos["name"][$i]) $msg.="$pname recibido. LINES0='".trim($lines[0])."'";
                else $msg.=$pagos["name"][$i]." recibido y guardado como {$pname}.";
                plog($msg);
            } else {
                plog("ERROR: Error al mover el archivo $pname a PAGOS");
            }
        }
    }
}
?>
  <div id="area_alta" class="central">
    <h1 class="txtstrk">Carga Reportes de Egresos de Avance</h1>
    <div id="area_alta_contenido" class="contenido">
      <form method="post" name="forma_alta" target="_self" enctype="multipart/form-data" class="oneLine">
        <input type="hidden" name="menu_accion" value="Carga Pagos">
        <input type="file" name="pagos[]" id="pagos" multiple class="highlight">
        <input type="submit" value="Enviar">
      </form>
      <div class="lessTwoLines scrollauto">
<?php
$hasTXT=false;
$sizeunits="BKMGTP";
foreach (glob("{$bsPyPth}PAGOS\\*.txt") as $filename) {
    if (!$hasTXT) {
        $hasTXT=true;
        echo "<fieldset class=\"screen\"><legend align=\"left\" class=\"uppercase boldValue\">Archivos Procesados</legend>"."<table class=\"lefted\"><tr><td class=\"top\">";//"<div class=\"lefted column noFlow wid140px\">";
    }
    $filebytes=filesize($filename);
    $filefactor=intval((strlen("".$filebytes)-1)/3);
    $powfactor=pow(1024,$filefactor);
    $fileunits=@$sizeunits[$filefactor].($filefactor>0?"B":"yte");
    $filesizeh=sprintf("%.2f", $filebytes/$powfactor).$fileunits;
    // quitar path
    $fileIdx=strrpos($filename, "\\");
    if ($fileIdx!==false) $filename=substr($filename, $fileIdx+1);
    echo "<div class=\"btnLt\" onclick=\"selectBtn(event);\">$filename</div>";
}
if ($hasTXT) {
    //echo "</div><div id=\"fileResultsReport\" class=\"lefted column scrollauto\"></div>";
    echo "</td><td id=\"fileResultsReport\" class=\"lefted\"></td></tr></table>";
    echo "</fieldset><span class=\"clear\"></span>";
}
// ToDo: En lugar del texto descriptivo crear un log y desplegarlo ahi, poner un boton para borrar el archivo. Hacer un script ajax que lea el log y lo despliegue continuamente.
// Al guardar archivos
?>
  <fieldset class="screen">
    <legend align="left" class="uppercase boldValue">Listado de actividad</legend>
    <div id="log_contents"><?= file_get_contents("{$bsPyPth}pagos.log") ?></div>
  </fieldset><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="separateBlocks();ekil(this);">
      </div>
      <div class="footOneLine">
        <button type="button" onclick="cleanLogFile();">Limpiar</button>
      </div>
    </div>
  </div>
<?php
clog1seq(-1);
clog2end("templates.cargapagos");
?>