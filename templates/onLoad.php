<?php
slog("INI onLoad.php", 2);

$hasErrorMessage = isset($errorMessage[0]);
$hasResultMessage = isset($resultMessage[0]);

$title = $hasErrorMessage?($errorTitle??"ERROR"):($resultTitle??"AVISO");
$message = $hasErrorMessage?$errorMessage:($resultMessage??"");

$hasMessage = isset($message[0]);
$hasOnloadScript = isset($onloadScript[0]);

if ($hasMessage) {
    
    $message = "<div style='float:none;display:block;clear:both;'>".addslashes($message)."</div>";
}
$hasOnload = $hasMessage||$hasOnloadScript;

if ($hasOnload) {
    echo "var oldOnload_onLoad=window.onload;\n";
    echo "window.onload=function(event){\n";
    //echo     "console.log('ONLOAD function');\n";
    echo "  oldOnload_onLoad && oldOnload_onLoad();\n";
    if ($hasMessage) {
        echo "  overlayMessage(\"$message\", \"$title\");\n";
        if ($hasErrorMessage && $hasResultMessage) {
            echo "  ebyid('overlay').callOnClose=function(){overlayMessage('$resultMessage','$resultTitle');ebyid('overlay').callOnClose=focusOnAutoFocus;};\n";
        } else echo "  ebyid('overlay').callOnClose=focusOnAutoFocus;";
    }
    if ($hasOnloadScript)
        echo "{$onloadScript};\n";
    echo     "}\n";
}
echo "var oldOnresize = window.onresize;\n";
echo "window.onresize = function (event) {\n";
echo "  oldOnresize && oldOnresize();\n";
echo "  onresizeScripts();\n";
echo "}\n";

slog("END onLoad.php", 2);
