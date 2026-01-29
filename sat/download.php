<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('charset=UTF-8');
require_once dirname(__DIR__)."/bootstrap.php";
$browser = getBrowser();
$isMSIE = ($browser==="Edge" || $browser==="IE");
$url = "http://www.sat.gob.mx/sitio_internet/";
$dt = new DateTime();
function mylog($msg) {
    global $dt;
    $timestamp = $dt->format("ymdHis");
    $logName="download.log";
    $logSize=filesize($logName);
    if ($logSize>26200000) {// 104857600 // 52428800 // 26214400
        $stampedLog="download".$timestamp.".log";
        rename($logName,$stampedLog);
        $secs=0;
        while(!file_exists($stampedLog)||filesize($stampedLog)<$logSize) {
            $secs++;
            sleep(1);
            if ($secs>10) {
                unlink($logName);
                $msg="[{$timestamp}] RENAME fallido!\r\n".$msg;
                $secs=0;
                break;
            }
        }
        if ($secs>0) file_put_contents($stampedLog,"[{$timestamp}] HOLD $secs sec".($secs>1?"s":""), FILE_APPEND | LOCK_EX);
    }
    file_put_contents($logName,"[{$timestamp}] $msg\r\n", FILE_APPEND | LOCK_EX);
}
function getFileList($path="cfd/") {
    $list=glob($path."*");
    //mylog("INI getFileList {$path}*");
    $arr=[];
    foreach ($list as $idx=>$filename) {
        $item=["path"=>$path,"name"=>basename($filename),"size"=>filesize($filename),"type"=>filetype($filename),"time"=>filemtime($filename)];
        //mylog("LOOP $path $idx) $item[type] $item[name] $item[size]");
        if ($item["type"]==="dir") {
            $item["name"].="/";
            $arr = array_merge($arr,getFileList($filename."/"));
        } else if (isset($_POST["start"])) {
            global $url;
            $ch = curl_init($url.$filename);
            mylog("CURL $url{$filename} to {$filename}x");
            $fp=fopen($filename."x",'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER,0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            $xsize=filesize($filename."x");
            $xtime=filemtime($filename."x");
            if ($xsize===164) {
                mylog("Error 164: File not found");
                if (!file_exists("error/".$path)) {
                    mkdir("error/".$path,0777,true);
                    mylog("Created Path error/{$path}");
                }
                rename($filename."x","error/".$filename);
                mylog("Renamed {$filename}x to error/{$filename}");
            } else if ($xsize===243) {
                mylog("Error 243: Access Denied");
                if (!file_exists("error/".$path)) {
                    mkdir("error/".$path,0777,true);
                    mylog("Created Path error/{$path}");
                }
                rename($filename."x","error/".$filename);
                mylog("Renamed {$filename}x to error/{$filename}");
            } else if ($xsize!==$item["size"]) {
                mylog("Changed size $item[size] != $xsize");
                if (!file_exists("viejo/".$path)) {
                    mkdir("viejo/".$path,0777,true);
                    mylog("Created Path viejo/{$path}");
                }
                rename($filename,"viejo/".$filename);
                mylog("Renamed to viejo/{$filename}");
                while(!file_exists("viejo/".$filename)||filesize("viejo/".$filename)!==$item["size"]) {
                    mylog("Hold 1 sec");
                    sleep(1);
                }
                rename($filename."x",$filename);
                mylog("Renamed {$filename}x");
                while(!file_exists($filename)||filesize($filename)!==$xsize) {
                    mylog("Hold 1 sec");
                    sleep(1);
                }
                $item["oldsize"]=$item["size"];
                $item["oldtime"]=$item["time"];
                $item["size"]=$xsize;
                $item["time"]=$xtime;
            } else {
                unlink($filename."x");
                mylog("Ignore: Same Size");
            }
            $arr[]=$item;
        } else {
            $arr[]=$item;
            mylog("Showing $filename");
        }
    }
    return $arr;
}
?>
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <?= $isMSIE?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title>Download Files from SAT</title>
    <style>
        table.list>tbody>tr>td {
            white-space: nowrap;
        }
    </style>
    <script>
        console.log("INIT <?=date("y/m/d h:i:s")?>");
<?php
if (validaPerfil("Administrador")||validaPerfil("Sistemas")) $filelist=getFileList("cfd/");
?>
    </script>
  </head>
  <body>
    <div id="contenedor" class="centered">
<?php
if (validaPerfil("Administrador")||validaPerfil("Sistemas")) { ?>
        <form target="_self" method="POST"><input type="submit" name="start" value="EMPEZAR" onclick="console.log('SUBMIT!');"></form>
<?php
    if (isset($filelist[0])) {
        echo "<TABLE class='list'><THEAD><TR><TH>RUTA</TH><TH>NOMBRE</TH><TH>TAMAÑO</TH><TH>TIPO</TH><TH>FECHA</TH><TH>HORA</TH></THEAD><TBODY>";
        foreach ($filelist as $block) {
            if (isset($block["oldsize"])) $size=$block["oldsize"]."=>";
            else $size="";
            $size.=$block["size"];
            if (isset($block["oldtime"])) {
                $fecha=date("M d Y",$block["oldtime"])."=>".date("M d Y",$block["time"]);
                $hora=date("H:i:s",$block["oldtime"])."=>".date("H:i:s",$block["time"]);
            } else {
                $fecha=date("F d Y",$block["time"]);
                $hora=date("H:i:s",$block["time"]);
            }
            echo "<TR><TD class='ruta'>$block[path]</TD><TD class='nombre'>$block[name]</TD><TD class='tamano'>$size</TD><TD class='tipo'>$block[type]</TD><TD class='fecha'>$fecha</TD><TD class='hora'>$hora</TD></TR>";
        }
        echo "</TBODY></TABLE>";
    }
} else { ?>
...
<?php
} ?>
    </div>
  </body>
</html>
<?php
//    include_once "configuracion/finalizacion.php";
