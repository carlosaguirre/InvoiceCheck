<?php
require_once dirname(__DIR__)."/bootstrap.php";

if (!empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $dir=$_SERVER["CONTEXT_DOCUMENT_ROOT"];
else if (!empty($_SERVER["DOCUMENT_ROOT"])) $dir=$_SERVER["DOCUMENT_ROOT"];
else $dir="";
$dir.="LOGS/";
$logDirSize=strlen($dir);
echo "<!-- POST: ".json_encode($_POST)." -->\n";
$listPath=$_POST["filelist"]??"";
$filePath=$dir.$listPath;
$handle=null; $error=null;
if (is_dir($filePath)) {
    if (isset($listPath[0]) && substr($listPath,-1)!=="/") $listPath.="/";
    $dir.=$listPath;
    //$text="";
} else {
    if (is_file($filePath)) {
        if (filesize($filePath)>0) $handle=fopen($filePath, "r");//$text=file_get_contents($filePath);
        else $error="Archivo vacío";
    } else $error="Archivo inválido";
    $dir.=$_POST["lastPath"]??"";
}
$isRoot=!isset($dir[$logDirSize+1]);
$esAltaFac=(substr($listPath,-11)==="altafac.log");
$viewMode=$esAltaFac?($_POST["mode"]??"serv"):"";
echo "<!-- dir = '$dir' -->\n";
echo "<!-- listPath = '$listPath' -->\n";
echo "<!-- logDirSize = $logDirSize / ".strlen($dir)." -->\n";
$sdir = scandir($dir);
echo "<!-- scandir: ".json_encode($sdir)." -->\n";
// ToDo: quitar lo que no sea directorio o archivo con extensión .log
$list=array_diff($sdir, [".",".."]); // isset($dir[$logDirSize+1])?["."]:[".",".."]

$filterFile=$_POST["filtertext"]??"";
$dellist=[];
foreach ($list as $value) {
    if (is_dir($dir.$value)) {
        if (isset($filterFile[0]) && !file_exists($dir.$value."/".$filterFile)) {
            $filterMatches = glob($dir.$value."/".$filterFile);
            if (!isset($filterMatches[0][0])) $dellist[]=$value;
        }
    } else if (isset($filterFile[0])) {
        if (strpos($value, $filterFile) === false) {
            $filterMatches = glob($dir.$filterFile);
            if (!in_array($dir.$value, $filterMatches)) $dellist[]=$value;
        } 
    } else if (substr($value, -4)!==".log") $dellist[]=$value;
}
if (isset($dellist[0])) $list=array_diff($list, $dellist);
$list=array_values($list);
if ($isRoot) $list=array_reverse($list);
else {
    array_unshift($list, "..");
}
$keylist=array_map(function($name) {
    global $listPath,$dir,$logDirSize;
    $lpath=substr($dir, $logDirSize);
    if ($name==="..") {
        $prePos=strrpos($lpath, "/", -2);
        if ($prePos===false) return "";
        $backPath=substr($lpath, 0, $prePos+1);
        return $backPath;
    }
    return $lpath.$name;
}, $list);
$asocList=array_combine($keylist, $list);
?>
<html>
    <head>
        <title>Visualizar Logs</title>
        <base href="http://invoicecheck.dyndns-web.com:81/invoice/">
        <meta charset="utf-8">
        <script src="scripts/general.js?ver=1.0.0"></script>
        <script>
            function changeList() {
                console.log("INI changeList ",this);
            }
        </script>
        <link href="css/general.php" rel="stylesheet" type="text/css">
    </head>
    <body>
        <H1 class="bs centered">Visualizar Logs</H1>
        <form name="forma1" method="post" target="_self" enctype="multipart/form-data" class="lefted">
            FILTRO: <input type="text" name="filtertext" onchange="document.forma1.submit();" value="<?=$filterFile?>" size="5" autofocus><br>
            <?= $dir ?><br>
            LISTA: <select name="filelist" onchange="document.forma1.submit();" size="5" class="top"><option value="<?=$listPath?>" selected>.</option><?= getHtmlOptions($asocList, $listPath) ?></select><?=$esAltaFac?" VISTA: <select name=\"mode\" onchange=\"document.forma1.submit();\" class=\"top\"><option value=\"\"".(!isset($viewMode[0])?" selected":"").">Normal</option><option value=\"serv\"".($viewMode==="serv"?" selected":"").">Servicio</select>":""?>
            <input type="hidden" name="lastPath" value="<?= substr($dir, $logDirSize) ?>">
        </form>
        <div id="textContent" class="preline scrollauto nomargin" style="width:calc(100% - 28px);height:calc(100% - 199px);"><?php 
        if (isset($error[0])) echo $error."\n";
        else if (isset($handle) && $handle!==false) {
            $isFirstLine=true;
            while(($line=fgets($handle))!==false) {
                if ($esAltaFac) {
                    switch($viewMode) {
                        case "serv":
                            if ($isFirstLine) {
                                $isFirstLine=false;
                                echo "<table border='1'>";
                            }
                            $sp1idx=strpos($line,"] "); // debe ser 7
                            if ($sp1idx===false) break;
                            $dt=substr($line, 1, $sp1idx-1); // len=6
                            $sp2idx=strpos($line, ": ", $sp1idx); // vg 15
                            if ($sp2idx===false) break;
                            $cd=substr($line, $sp1idx+2, $sp2idx-$sp1idx-2); // 15-7-2=6
                            if (substr($line, $sp2idx+2, 18)!=="ConsultaServicio. ") break;
                            $txt3="respuesta:{\"expresionImpresa\":\"";
                            $len3=strlen($txt3);
                            $sp3idx=strpos($line, $txt3, $sp2idx);
                            if ($sp3idx===false) {
                                echo "<tr><td>$dt</td><td>X</td><td>$cd</td><td>".substr($line, $sp2idx+18, 200)."</td></tr>";
                                break;
                            }
                            $txt4="\",\"cfdi\":\"";
                            $len4=strlen($txt4);
                            $sp4idx=strpos($line, $txt4, $sp3idx);
                            if ($sp4idx===false) {
                                echo "<tr><td>$dt</td><td>Y</td><td>$cd</td><td>".substr($line, $sp2idx+30, 188)."</td></tr>";
                                break;
                            }
                            $xi=substr($line, $sp3idx+$len3,$sp4idx-$sp3idx-$len3);
                            $txt5="\",\"estado";
                            $sp5idx=strpos($line, $txt5, $sp4idx);
                            if ($sp5idx===false) {
                                echo "<tr><td>$dt</td><td>Z</td><td>$cd</td><td>$xi</td></tr>";
                                break;
                            }
                            $st=substr($line, $sp4idx+$len4, $sp5idx-$sp4idx-$len4);
                            if ($st==="S - Comprobante obtenido satisfactoriamente.")
                                echo "<tr><td>$dt</td><td>S</td><td>$cd</td><td>$xi</td></tr>";
                            else if (substr($st,0,7)==="Expresi" && substr($st,-3)==="601")
                                echo "<tr><td>$dt</td><td>N</td><td>$cd</td><td>$xi</td></tr>";
                            else echo "<tr><td>$dt</td><td>W</td><td>$cd</td><td>$xi<br>$st</td></tr>";
                        // parse per line
                        // search "ConsultaServicio", ignore other lines
                        // obtain date, code, value of expresionImpresa and value of cfdi
                        // create new text only with date, S/N for cfdi, code, expresionImpresa separated by single space
                        // replace text with new text
                            break;
                        default: echo $line."\n";
                    }
                } else echo $line."\n";
            }
            if ($esAltaFac && $viewMode==="serv" && !$isFirstLine) echo "</table>";
            fclose($handle);
        }
        // echo $text;
         ?></div>
    </body>
</html>
