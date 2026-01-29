<?php
clog2ini("templates.logs");
clog1seq(1);
$docRoot = $_SERVER["DOCUMENT_ROOT"];
$logRoot = $docRoot."LOGS/";
$logLen = strlen($logRoot);
$list=glob($logRoot."*");
rsort($list);
?>
<div id="area_general" class="central scrollauto">
  <div id="area_top" class="basicBG sticky toTop padhtt zIdx1"><h1 class="txtstrk" class="nomargin">LOGS</h1></div>
  <div id="area_detalle" class="heiOff minHeiAll all_space">
    <div class="minHeiAll fltL wid70px">
<?php
$isDefault=true;
echo "      <UL id=\"dateList\" class=\"logmenu\">\n";
$selectedDate=null;
foreach ($list as $idx => $filepath) {
    if (!is_dir($filepath)) continue;
    if (!isset($selectedDate)) $selectedDate=$filepath;
    $datedir=substr($filepath, $logLen);
    $liClass="dateElem";
    if ($isDefault) { $isDefault=false; $liClass.=" selected"; }
    echo "        <LI name=\"$datedir\" class=\"{$liClass}\" onclick=\"selectThis(event);\">$datedir</LI>\n";
}
?>
      </UL>
    </div>
    <div class="minHeiAll fltL wid150px">
<?php
$dateVal=substr($selectedDate, $logLen);
$dateLen=strlen($dateVal);
if (is_dir($selectedDate)) {
    $list2=glob($selectedDate."/*.log");
    natcasesort($list2);
    echo "      <UL id=\"userList\" class=\"logmenu next\">\n";
    foreach ($list2 as $idx2=>$filepath2) {
        if (is_dir($filepath2)) continue;
        $relPath=substr($filepath2, $logLen, -4);
        $userlog=substr($relPath, $dateLen+1);
        $liClass2="userElem";
        if ($userlog==="error") {
            $selectedIdx2=$idx2;
            $liClass2.=" selected";
        }
        echo "        <LI name=\"{$relPath}\" class=\"{$liClass2}\" onclick=\"selectThis(event);\">$userlog</LI>\n";
    }
}
?>
      </UL>
    </div>
    <div id="resultarea" class="minHeiAll fltL allWidBut220 screen pre">
<?php
if (isset($list2) && isset($selectedIdx2)) {
    echo "<!-- len ".count($list2)." [ $selectedIdx2 ] => ".$list2[$selectedIdx2]." -->\n";
    $lines=[]; $block=[];
    $fd = fopen ($list2[$selectedIdx2], "r");
    $len=0;
    //echo "<!-- ";
    while (!feof ($fd)) {
        $buffer = trim(fgets($fd, 4096));
        if (isset($buffer[0])) {
            if ($buffer[0]==="[") {
                if (isset($block[0])) {
                    //echo "[".count($block)."/";
                    array_splice($lines, 0, 0, $block);
                    $block=[];
                    //echo count($lines)."]";
                }
            }
            //echo ".";
            $block[]=$buffer;
            $len++;
        }
    }
    fclose ($fd);
    if (isset($block[0])) {
        array_splice($lines, 0, 0, $block);
        $block=[];
        echo implode("\n", $block);
    }
    echo implode("\n",$lines);
    //echo " -->\n";
} ?>
    </div>
    <div class="clear"></div>
  </div>
</div>
<?php
clog1seq(-1);
clog2end("templates.logs");
