<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!validaPerfil("Administrador")&&!validaPerfil("Sistemas")) {
    require_once "configuracion/finalizacion.php";
    exit;
}

$monthNames=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
//echo "SOLICITUD RECIBIDA";
$tgtPath="C:\\InvoiceCheckShare\\RESPALDO\\";
$codePath="C:\\Apache24\\htdocs\\invoice";
$zipPath="\"C:\\Program Files\\WinRAR\\WinRAR.exe\"";
$sqlPath="\"C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump\"";
$timeSuffix=date("YmdHis");
if (isset($_POST["tipo"][0])) {
    $ftype=$_POST["tipo"];
    switch($ftype) {
        case "datos":
            $params="--defaults-file=\"{$tgtPath}MIGRACION\\{$bd_usuario}.cnf\" --user=$bd_usuario --host=localhost --protocol=tcp -port=3306 -default-character-set=utf8 --single-transaction=TRUE --routines --skip-triggers $bd_base";
            //$params="--single-transaction --user=$bd_usuario --password=$bd_clave $bd_base";
            $filePath="{$tgtPath}DATOS\\invoiceDump{$timeSuffix}.sql";
            $cmd = "$sqlPath $params > $filePath 2>&1";
            $prefix="DATA";
            $globPattern=$tgtPath."DATOS\\invoiceDump*.sql";
            $echoPath=$tgtPath."DATOS\\";
            $echoIndex=11;
            break;
        case "codigo":
            $params="a -ibck -ed -r -ep1 -tk -x*\\archivos -x*\\recibos -x*\\descargas -x*\\docs -x*\\LOGS";
            $filePath="{$tgtPath}CODIGO\\invFullVer{$timeSuffix}.zip";
            $contentList="$codePath\\*";
            $cmd="$zipPath $params $filePath $contentList 2>&1";
            $prefix="CODE";
            $globPattern=$tgtPath."CODIGO\\inv????Ver*.zip";
            $echoPath=$tgtPath;
            $echoIndex=17;
            break;
        case "logs":
            $params="a -ibck -ed -r -ep1 -tk"; // " -tn15d";
            $filePath="{$tgtPath}LOGS\\invoice\\{$timeSuffix}Todo.zip"; // "Quincenal.zip";
            $contentList="$codePath\\LOGS\\*";
            $cmd="$zipPath $params $filePath $contentList 2>&1";
            $prefix="LOGS";
            $globPattern=$tgtPath."LOGS\\invoice\\*.zip";
            $echoPath=$tgtPath;
            $echoIndex=0;
            break;
        case "cfdi":
            $params="a -ibck -ed -r -ep1 -tk -tn15d";
            $filePath="{$tgtPath}CFDI\\inv2Wks{$timeSuffix}.zip";
            $contentList="$codePath\\archivos $codePath\\recibos $codePath\\descargas $codePath\\*\\docs";
            $cmd="$zipPath $params $filePath $contentList 2>&1";
            $prefix="CFDI";
            $globPattern=$tgtPath."CFDI\\inv*.zip";
            $echoPath=$tgtPath."CFDI\\";
            $echoIndex=7;
            break;
        case "fullDataDump": // Full Data Dump
            $params="--defaults-file=\"{$tgtPath}MIGRACION\\{$bd_usuario}.cnf\" --user=$bd_usuario --host=localhost --protocol=tcp -port=3306 -default-character-set=utf8 --single-transaction=TRUE --routines --skip-triggers $bd_base";
            $filePath="{$tgtPath}DATOS\\invoiceDump{$timeSuffix}.sql";
            $cmd = "$sqlPath $params > $filePath 2>&1";
            $prefix="DATA";
            $globPattern=$tgtPath."DATOS\\invoiceDump*.sql";
            $echoPath=$tgtPath."DATOS\\";
            $echoIndex=11;
            break;
        case "fdl": // Full Data Load
            break;
        default:
            $cmd=null;
    }
    if (isset($cmd)) {
        flog("EXEC $cmd","backup");
        $result = exec($cmd);
        flog("RESULT: $result","backup");
        $filelist=glob($globPattern);
        flog("$prefix: ".json_encode($filelist),"backup");
        usort($filelist,"backupSort");
        flog("SORT: ".json_encode($filelist),"backup");
        echoList($filelist, $echoPath, $echoIndex, strtolower($prefix));
    }
} else if (isset($_POST["full"])) {}
die();
function backupSort($path1, $path2) {
    $chunk1 = substr($path1, -18,-4);
    $chunk2 = substr($path2, -18,-4);
    return ($chunk1<=$chunk2)?1:-1; // Para cambiar sentido invertir valor positivo y negativo
}
function echoList($filelist, $path, $timeStart, $ftype) {
    global $monthNames;
    //echo "[";
    $jsonArr=["result"=>"success","data"=>[]];
    $isFirst=true;
    $idx=0;
    foreach ($filelist as $line) {
        $fileName=substr($line,strlen($path));
        $fileSize=filesize($line);
        $invIdx=0;
        if ($timeStart>12) $invIdx=7;
        //else if ($timeStart==12) $invIdx=5;
        $fullName=substr($fileName, $invIdx);
        $invName=substr($fileName, $invIdx, 7);
        $sfxName=substr($fileName, $invIdx+14, -4);
        if ($invName==="invoice" || $sfxName==="Diario") {
            $fileSizeFix=" (".sizeFix($fileSize).")";
            $rowClass="hoverDark2";
        } else if ($invName==="inv2Wks" || $sfxName==="Quincenal") {
            $fileSizeFix=" {".sizeFix($fileSize)."}";
            $rowClass="hoverLight3 bggreen";
        } else if ($invName==="invFull" || $sfxName==="Todo") {
            $fileSizeFix=" [".sizeFix($fileSize)."]";
            $rowClass="hoverLight3 bgmagenta";
        } else {
            $fileSizeFix=" #".sizeFix($fileSize)."#";
            $rowClass="hoverDark2 bgyellow2";
        }
        $idx++;
        if (isset($fileName[$timeStart+12])) {
            $yearText=substr($fileName, $timeStart, 4);
            $monthTxt=substr($fileName, $timeStart+4, 2);
            $monthIdx=(+$monthTxt)-1;
            $monthName=$monthNames[$monthIdx];
            $dateText=substr($fileName, $timeStart+6, 2);
            $hourText=substr($fileName, $timeStart+8, 2);
            $minuText=substr($fileName, $timeStart+10, 2);
            $seconTxt=substr($fileName, $timeStart+12, 2);
            $timeTxt=$hourText.":".$minuText.":".$seconTxt;
            //if($isFirst) $isFirst=false; else echo ",";
            $timeLen=14;
            if (!isset($fileName[$timeStart+$timeLen+4])) $timeLen=-4;
            $jsonArr["data"][]=["eName"=>"LI","idx"=>"$idx","ftype"=>$ftype,"fdate"=>substr($fileName,$timeStart,$timeLen),"fsize"=>"$fileSize","title"=>"$fullName","className"=>$rowClass,"onclick"=>"viewFile(event);","eChilds"=>[["eName"=>"SPAN","className"=>"daySpan","eText"=>$dateText],["eText"=>" de "],["eName"=>"SPAN","className"=>"monthSpan","eText"=>$monthName],["eText"=>" del "],["eName"=>"SPAN","className"=>"yearSpan","eText"=>$yearText],["eText"=>", $timeTxt$fileSizeFix"]]];
            //echo "[{\"eName\":\"SPAN\",\"className\":\"daySpan\",\"eText\":\"$dateText\"},{\"eText\":\" de \"},{\"eName\":\"SPAN\",\"className\":\"monthSpan\",\"eText\":\"$monthName\"},{\"eText\":\" del \"},{\"eName\":\"SPAN\",\"className\":\"yearSpan\",\"eText\":\"$yearText\"},{\"eText\":\", $timeTxt$fileSizeFix\"}]";
        } else if (isset($fileName[$timeStart+4])) {
            //if($isFirst) $isFirst=false; else echo ",";
            $dateText=substr($fileName, $timeStart, -4);
            $jsonArr["data"][]=["eName"=>"LI","idx"=>"$idx","ftype"=>$ftype,"fdate"=>$dateText,"fsize"=>"$fileSize","title"=>"$fullName","className"=>$rowClass,"onclick"=>"viewFile(event);","eText"=>$dateText.$fileSizeFix];
            //echo "[{\"eText\":\"$nameText$fileSize\"}]";
        } else if (isset($fileName[4])) {
            //if($isFirst) $isFirst=false; else echo ",";
            $dateText=substr($fileName, 0, -4);
            $jsonArr["data"][]=["eName"=>"LI","idx"=>"$idx","ftype"=>$ftype,"fdate"=>$dateText,"fsize"=>"$fileSize","title"=>"$fullName","className"=>$rowClass,"onclick"=>"viewFile(event);","eText"=>$dateText.$fileSizeFix];
            //echo "[{\"eText\":\"$nameText$fileSize\"}]";
        }
    }
    //echo "]";
    echo json_encode($jsonArr);
}
