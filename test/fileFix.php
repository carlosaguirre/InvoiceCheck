<?php
// Recibe archivos XML en argumentos y elimina espacios entre tags
// si no hay archivos termina
//if ($argc<2) return;
$doLog=false;
$isRecursive=false;
$chkNum=0;
$fixNum=0;
$logTxt="";
try {
    foreach ($argv as $key => $value) if ($key) fix($value);
    echo "{$fixNum}/{$chkNum} fixed files";
} catch (Exception $ex) {
    echo "ERROR: ".$ex->getMessage();
}
function fix($filename) {
    if ($filename==="." || $filename==="..") return;
    if (is_file($filename)) safeFix($filename);
    else if (is_dir($filename)) {
        global $isRecursive;
        if ($isRecursive) array_map('fix',glob($filename."/*"));
    } else array_map('fix', glob($filename));
}
function safeFix($filename) {
    global $chkNum,$fixNum;
    $chkNum++;
    //if (replaceSpaceBetweenTags($filename)) $fixNum++;
    if (addRWPermission($filename)) $fixNum++;
}
function addRWPermission($filename) {
    return chmod($filename, 0666)!==false;
}
function replaceSpaceBetweenTags($filename) {
    $tmpPath="C:/InvoiceCheckShare/tmp/fix/";
    if(!is_dir($tmpPath) && !mkdir($tmpPath, 0777, true)) {
        throw new Exception("No se pudo crear ruta $tmpPath");
    }
    $basename=basename($filename);
    $text=file_get_contents($filename);
    $fixText=preg_replace("/>\s*</", "><", $text);
    if (strcmp($text,$fixText)!=0) {
        echo "Fixed {$tmpPath}$basename\n";
        file_put_contents($tmpPath.$basename, $fixText);
        return true;
    }
    echo "Unchanged $filename\n";
    return false;
}
