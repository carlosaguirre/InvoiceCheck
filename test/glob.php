<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once dirname(__DIR__)."/bootstrap.php";
$action=$_REQUEST["action"]??"";
$isHtml=true;
if (isset($argc) && $argc>1 && isset($argv[1])) {
    $action=$argv[1];
    $isHtml=false;
}
$limitDate=date("Ymd");
$invoicePath="C:\\Apache24\\htdocs\\invoice";
$invoicePathLength=strlen($invoicePath);
$dateStr="";
$dateInt=0;
switch($action) {
    case "pdf":
        require_once "clases/PDFCR.php";
        try {
            $pcrObj=new PDFCR(null, null, true);
            $num=0;
            $list=$pcrObj->testFiles();
            foreach ($list as $filePath) {
                $fileDate=date("Ymd",filemtime($filePath));
                if ($fileDate===date('Ymd',strtotime("now"))) {
                    if ($num==0 && $isHtml) echo "<UL>";
                    $num++;
                    if ($isHtml) echo "<LI> ( $key ) $filePath <i>$fileDate</i></LI>";
                    else echo "- ( $key ) '$filePath' $fileDate\n";
                }
            }
            if ($num>0) {
                if ($isHtml) echo "</UL>";
            } else {
                echo "EMPTY";
                if ($isHtml) echo "<BR>";
                else echo "\n";
            }
        } catch (Exception $ex) {
            echo "ERROR: ".$ex->getMessage();
            if ($isHtml) echo "<BR>";
            else echo "\n";
        }
        break;
    case "date":
        $dateStr=str_replace(["-","/"], "", $isHtml?($_REQUEST["date"]??""):($argv[2]??""));
        //set_time_limit ( 300 );
        $startTime=microtime(true);
        $firstStartTime=$startTime;
        doclog("Start Time Limit","test",["firstStartTime"=>$startTime]);
        $functionTime=$startTime; // 
        $list=rListAfterDate($invoicePath,$dateStr);
        $retrieveTime=microtime(true);
        $generatingSecs=number_format($retrieveTime-$firstStartTime,2);
        $fileCount=0;
        doclog("END Full Retrieve","test",["fileCount"=>$fileCount,"fullTime"=>$generatingSecs]);
        if (isset($list[0])) {
            if ($dateInt>0) $dateStr=date("M d Y", $dateInt);
            $totl=count($list);
            $tots="$totl";
            $tdgs=strlen($tots)-1;
            echo "Obtaining $totl files modified since {$dateStr}:\n";
            if ($isHtml) echo "<OL>";
            $lastDir=null;
            $dirIdx=0;
            foreach ($list as $idx=>$value) {
                $num=$idx+1;
                if ($num==10) $tdgs--;
                else if ($num==100) $tdgs--;
                else if ($num==1000) $tdgs--;
                $currDir=dirname($value);
                if (!isset($lastDir)||$currDir!==$lastDir) {
                    $dirIdx++;
                    $interaction=" class=\"top_{$dirIdx}\" onclick=\"fee(lbycn('sub_{$dirIdx}'), el=>clfix(el,'hidden'));\"";
                    $lastDir=$currDir;
                } else {
                    $interaction=" class=\"sub_{$dirIdx} hidden\" onclick=\"fee(lbycn('sub_{$dirIdx}'), el=>cladd(el,'hidden'));\"";
                }
                if ($isHtml) {
                    echo "<LI{$interaction}>"; 
                } else {
                    echo " - ".str_repeat(" ", $tdgs)." ";
                }
                if (substr($value,0,$invoicePathLength)===$invoicePath) {
                    $fileName=substr($value,$invoicePathLength+1);
                } else {
                    $fileName=$value;
                }
                /*echo "(";
                if ($isHtml) {
                    echo "<u>&nbsp;<pre style=\"display: inline;\">";
                    for ($i=0;$i<$tdgs;$i++) echo "&nbsp;";
                } else echo " ";
                echo $num;
                if ($isHtml) echo "</pre>&nbsp;</u>"; else echo " ";
                echo ") ";*/
                if ($isHtml) echo "<i>";
                $fileDate=date("M d Y H:i:s", filemtime($value));
                echo $fileDate;
                if ($isHtml) echo "</i>";
                echo " | ";
                if ($isHtml) {
                    echo "<b><span onclick=\"fee(lbycn('sub_{$dirIdx}'), el=>clfix(el,'hidden'));\">".dir($fileName)."</span>".base($fileName)."</b>";
                } else echo $fileName;
                if ($isHtml) echo "</LI>"; else echo "\n";
            };
            if ($isHtml) echo "</OL>";
        } else {
            echo "EMPTY";
            if ($isHtml) echo "<BR>";
            else echo "\n";
        }
        echo "Took $generatingSecs seconds to generate list<br>";
        break;
    default:
        echo "hi world 4";
}
function rListAfterDate($path,$tmst=0) {
    if (is_string($tmst)) {
        if (isset($tmst[8])) $tmst=substr($tmst, 0, 8);
        else if (!isset($tmst[7])) $tmst=date("Ymd");
    } else if (!is_int($tmst) || $tmst<=0) {
        $tmst=date("Ymd");
    }
    if (is_string($tmst)) {
        global $dateStr, $dateInt;
        $dateStr=$tmst;
        $dt = DateTime::createFromFormat('YmdHis', $tmst."000000", new DateTimeZone("Etc/GMT+6"));
        $tmst=$dt->getTimestamp();
        $dateInt=$tmst;
    }
    global $startTime,$firstStartTime,$functionTime,$invoicePathLength;
    $auxTime=microtime(true);
    $lapseTime=$auxTime-$startTime;
    $fullLapseTime=$auxTime-$firstStartTime;
    if ($lapseTime>580) {
        ob_flush();
        flush();
        set_time_limit(600);
        $startTime=$auxTime;
        doclog("Restart Time Limit","test",["lapseTime"=>number_format($lapseTime,2)]);
    }
    $retList=[];
    if (is_dir($path)) {
        $dir=opendir($path);
        $shortPath=substr($path,$invoicePathLength+1);
        $funcStepTime=$auxTime-$functionTime;
        $functionTime=$auxTime;
        doclog("INI Test Opendir","test",["path"=>$shortPath,"funcStepTime"=>number_format($funcStepTime,2),"lapseTime"=>number_format($lapseTime,2),"fullLapseTime"=>number_format($fullLapseTime,2)]);
        $subFileCount=0;
        while (false !== ($file=readdir($dir))) {
            if($file==="." || $file==="..") continue;
            $subPath=$path."\\".$file;
            if (is_dir($subPath)) {
                $subList=rListAfterDate($subPath,$tmst);
                if (isset($subList[0])) {
                    $subFileCount+=count($subList);
                    array_push($retList, ...$subList);
                    $top=end($retList);
                }
            } else {
                $fileTime=filemtime($subPath);
                if ($fileTime>$tmst) {
                    $retList[]=$subPath;
                    $fileCount++;
                }
            }
        }
        closedir($dir);
        $aux2Time=microtime(true);
        $funcLapseTime=$aux2Time-$auxTime;
        doclog("END Test Opendir","test",["path"=>$shortPath,"fileCount"=>$fileCount,"subFileCount"=>$subFileCount,"funcLapseTime"=>number_format($funcLapseTime,2)]);
    } else if (is_file($path)) {
        $fileTime=filemtime($path);
        if ($fileTime>$tmst) $retList[]=$path;
    } else return [];
    usort($retList,"strnatcasecmp");
    return $retList;
}
/*
$docRoot = $_SERVER["DOCUMENT_ROOT"];
$path = "archivos/GLAMA/2021/05/";
$bgName="sol1366BGF";
$findStr=$docRoot.$path.$bgName;
$findLen=strlen($findStr);
$history=glob("{$findStr}*.pdf");
echo "<P><B>{$findStr}*.pdf"."</B></P>";
echo "<OL>";
$maxNum=0;
foreach ($history as $idx => $value) {
    $num=intval(substr($value, $findLen, -4));
    if ($num>$maxNum) $maxNum=$num;
    echo "<LI> $value </LI>";
}
echo "</OL>";
echo "<p>Next num = ".($maxNum+1)."</p>";
*/
