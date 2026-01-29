<?php
$logArcs=glob("C:\\Apache24\\logs\\*.log");
if (isset($logArcs[0][0])) {
    echo "List has ".count($logArcs)." files\n";
    //$maxSize=100000000;
    foreach($logArcs as $idx=>$logName) {
        $pfx=substr("00".$idx,-3);
        $logSize=filesize($logName);
        //if ($logSize>$maxSize) {
        //    echo " | Bigger than $maxSize";
            $fp = fopen($logName, "r");
            if ($fp===false) {
                echo "FAILED TO OPEN FILE '{$logName}'!\n";
                continue;
            }
            $logParts=pathinfo($logName);
            $logDir=$logParts["dirname"];
            $basename=basename($logName,".log");
            $startsWith=substr($basename,0,5);
            if ($startsWith!=="acces"&&$startsWith!=="error") continue;
        echo "#{$pfx}) {$basename}.log | Size:{$logSize}b\n";
        //    $times=ceil($logSize/$maxSize);
        //    echo " | Divided in $times parts.\n";
        //    $i=0;
        //    $lastName="";
        //    $sumSize=0;
            // INI INSERTED
        $tmpName=$logDir."/".$basename.".tmp";
        $out=fopen($tmpName, "w");
        if ($out===false) {
            echo "FAILED TO OPEN OUT '{$basename}.tmp'!\n";
            continue;
        }
        $line=0;
        $firstDate=null;
        $lastDate=null;
        $modified=false;
        while ($rec=fgets($fp)) {
            $trc=trim($rec);
            if (strcmp($rec, $trc."\n")) $modified=true;
            if (isset($trc[0])) {
                fputs($out, $trc."\n");
                $line++;
                $pattern=null;
                if ($startsWith==="acces") $pattern="/^(?<id>[\w\-\"\.: ]+)\[(?<date>[^:]+):(?<time>[^\]]+)\] (?<desc>.+)/";
                else if ($startsWith==="error") $pattern="/^\[(?<date>[\w\.: ]+)\] (?<desc>.+)/"; // [Thu Feb 16 09:08:06.175130 2023] [mpm_winnt:notice] [pid 4812:tid 536] AH00455: Apache/2.4.23 (Win64) PHP/7.4.7 configured -- resuming normal operations
                else {
                    break;
                }
                if (isset($pattern)) {
                    preg_match($pattern,$trc,$matches);
                    //$lntx=substr("000".$line,-4);
                    //echo "L{$lntx}: ".(empty($matches)?"NOMATCH|".$trc:json_encode($matches))."\n";
                    if (isset($matches["date"][0])) {
                        $matchDate=$matches["date"];
                        if (isset($matchDate[12]))
                        if (!isset($firstDate)) $firstDate=$matchDate;
                        $lastDate=$matchDate;
                    } else echo "No match. Line {$line}: $trc\n";
                }
            } else $modified=true;
        }
        fclose($out);
        fclose($fp);
        if ($modified) {
            sleep(2);
            $tmpSize=filesize($tmpName);
            echo "Total $line lines. Date Range $firstDate - {$lastDate}. Size: {$tmpSize}\n";
            if (unlink($logName)) {
                sleep(3);
                rename($tmpName,$logName);
            } else echo "ERROR ON REPLACING FILE\n";
        } else echo "Total $line lines. Date Range $firstDate - {$lastDate}. NOT MODIFIED\n";
            // END INSERTED

            /*while ($rec=fgets($fp)) {
                $recSize=strlen($rec);
                if ($sumSize==0||($sumSize+$recSize)>$maxSize) {
                    if (isset($out)) {
                        fclose($out);
                        sleep(1);
                        $realSize=filesize($lastName);
                        echo "New File $newName | sum:$sumSize | real:$realSize\n";
                    }
                    $i++;
                    $ii=substr("00{$i}", -3);
                    $newName=$basename."_i{$ii}.log";
                    $lastName=$logDir."/".$newName;
                    $out=fopen($lastName, "w");
                    $newSize=0;
                    $lastSize=0;
                }
                $newSize+=$recSize;
                fputs($out, $rec."\n");

            }*/
//            fclose($fp);
            /*if ($times>0 && isset($lastName[0])) {
                sleep(1);
                if (unlink($logName)) echo "Deleted $logName\n";
                else echo "Couldn't delete $logName\n";
                sleep(2);
                if (rename($lastName,$logName)) echo "Reasigning Last '$newName' to Base '$basename'\n";
                else echo "Couldn't rename Last '$newName' to Base '$basename'\n";
            }*/
        //} else echo " | Valid filesize\n";
    }
}
/*
$fileName="C:\\Apache24\\logs\\access_0_fx3981662.log";
$tempName="C:\\Apache24\\logs\\_access_0_fx3981662.tmp";
$fp =fopen($fileName, "r");
$out=fopen($tempName, "w");
$line=0;
$firstDate=null;
$lastDate=null;
while ($rec=fgets($fp)) {
    $trc=trim($rec);
    if (isset($trc[0])) {
        fputs($out, $trc."\n");
        $line++;
        preg_match('/^(?<id>[\d\.]+)[\- ]+\[(?<date>[^:]+):(?<time>[^\]]+)\] (?<desc>.+)/',$trc,$matches);
        $lntx=substr("000".$line,-4);
        echo "L{$lntx}: ".(empty($matches)?"NOMATCH|".$trc:json_encode($matches))."\n";
        if (isset($matches["date"][0])) {
            if (!isset($firstDate)) $firstDate=$matches["date"];
            $lastDate=$matches["date"];
        } else echo "No match. Line {$line}: $trc\n";
    }
}
echo "Total $line lines. Date Range $firstDate - {$lastDate}.\n";
fclose($out);
fclose($fp);
sleep(2);
if (unlink($fileName)) {
    sleep(2);
    rename($tempName,$fileName);
} else echo "ERROR ON REPLACING FILE";
*/
