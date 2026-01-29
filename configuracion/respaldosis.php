<?php
if(!$hasUser || !_esSistemas) {
    if ($hasUser) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
clog2ini("configuracion.respaldosis");
clog1seq(1);

$tgtPath="C:\\InvoiceCheckShare\\RESPALDO\\";
$tgtPathLen=strlen($tgtPath);

$datalist=glob($tgtPath."DATOS\\invoiceDump*.sql");
//rsort($datalist);
usort($datalist,"backupSort");

$codelist=glob($tgtPath."CODIGO\\inv????Ver*.zip");
//rsort($codelist);
usort($codelist,"backupSort");

$logslist=glob($tgtPath."LOGS\\invoice\\*.zip");
//rsort($logslist);
usort($logslist,"backupSort");

$cfdilist=glob($tgtPath."CFDI\\inv*.zip");
//rsort($cfdilist);
usort($cfdilist,"backupSort");

$measures=["data"=>["DATOS","invoiceDump"],"code"=>["CODIGO","invoiceVer","invFullVer","invMontVer","inv2WksVer"],"logs"=>["LOGS\\invoice",""], "cfdi"=>["CFDI","invoice","inv2Wks","invMont","invFull"]];
$dia = date('j');
$mes = date('n');
$anio = date('Y');
$maxdia = date('t');
$fmtDay0 = "01/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
$fmtDay = str_pad($dia,2,"0",STR_PAD_LEFT)."/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //
function backupSort($path1, $path2) {
    $chunk1 = substr($path1, -18,-4);
    $chunk2 = substr($path2, -18,-4);
    return ($chunk1<=$chunk2)?1:-1; // Para cambiar sentido invertir valor positivo y negativo
}
function showList($globlist, $listId) { // data, code, logs, cfdi
    global $tgtPath, $tgtPathLen, $monStrs, $measures;
    $measure=$measures[$listId];
    $idx=0;
    clog2("showList $listId => $tgtPath".$measure[0]." : #".count($globlist));
    if (isset($globlist[0])) foreach ($globlist as $line) {
        $fileName=substr($line, $tgtPathLen+strlen($measure[0])+1);
        $fileSize=filesize($line);
        for ($i=1; isset($measure[$i]); $i++) { 
            $fileSfx=$measure[$i];
            $fSfxLen=strlen($fileSfx);
            $rowClass="hoverLight2";
            if ($fSfxLen==0 || substr($fileName,0,$fSfxLen)===$fileSfx) {
                $invName=substr($fileName, 0, 7);
                $sfxName=substr($fileName, 14, -4);
                if ($invName==="invoice" || $sfxName==="Diario") {
                    $fileSizeFix="(".sizeFix($fileSize).")";
                    $rowClass="hoverDark2";
                } else if ($invName==="inv2Wks" || $sfxName==="Quincenal") {
                    $fileSizeFix="{".sizeFix($fileSize)."}";
                    $rowClass="hoverLight3 bggreen";
                } else if ($invName==="invFull" || $sfxName==="Todo") {
                    $fileSizeFix="[".sizeFix($fileSize)."]";
                    $rowClass="hoverLight3 bgmagenta";
                } else {
                    $fileSizeFix="#".sizeFix($fileSize)."#";
                    $rowClass="hoverDark2 bgyellow2";
                }
                $idx++;
                if (isset($fileName[$fSfxLen+12])) {
                    $dateText=substr($fileName, $fSfxLen, -4);
                    $yearText=substr($fileName, $fSfxLen, 4);
                    $monthTxt=substr($fileName, $fSfxLen+4, 2);
                    $monthIdx=(+$monthTxt)-1;
                    $monthName=$monStrs[$monthIdx];
                    $monthClass=strtolower($monthName);
                    $dayText=+substr($fileName, $fSfxLen+6, 2);
                    $hourText=substr($fileName, $fSfxLen+8, 2);
                    $minuText=substr($fileName, $fSfxLen+10, 2);
                    $seconTxt=substr($fileName, $fSfxLen+12, 2);
                    echo "<LI idx=\"$idx\" ftype=\"$listId\" fdate=\"$dateText\" fsize=\"$fileSize\" title=\"$fileName\" class=\"$rowClass\" onclick=\"viewFile(event);\"><span class=\"daySpan\">$dayText</span> de <span class=\"monthSpan $monthClass\">$monthName</span> del <span class=\"yearSpan\">$yearText</span>, {$hourText}:{$minuText}:$seconTxt $fileSizeFix</LI>";
                } else if (isset($fileName[$fSfxLen]) && substr($fileName,0,$fSfxLen)===$fileSfx) {
                    $dateText=substr($fileName, $fSfxLen, -4);
                    echo "<LI idx=\"$idx\" ftype=\"$listId\" fdate=\"$dateText\" fsize=\"$fileSize\" title=\"$fileName\" class=\"$rowClass\" onclick=\"viewFile(event);\">{$dateText} $fileSize</LI>";
                } else {
                    $dateText=substr($fileName, 0, -4);
                    echo "<LI idx=\"$idx\" ftype=\"$listId\" fdate=\"$dateText\" fsize=\"$fileSize\" title=\"$fileName\" class=\"$rowClass\" onclick=\"viewFile(event);\">{$dateText} $fileSize</LI>";
                }
            } else { clog2($measure[0]."\\$fileSfx !=> ".substr($line, $tgtPathLen)); }
        }
        clog2("$idx) _ $tgtPath _ $measure[0] \\ $fileName ($fileSize)");
    }
}

clog1seq(-1);
clog2end("configuracion.respaldosis");
