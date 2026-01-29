<?php
error_reporting(E_ALL);
date_default_timezone_set("America/Mexico_City");
$mylocale = setlocale(LC_TIME, "Spanish_Mexico.UTF-8", "Spanish_Mexican.UTF-8", "es_MX.UTF-8", "Spanish_Mexico.utf8", "Spanish_Mexican.utf8", "es_MX.utf8", "Spanish_Mexico", "Spanish_Mexican", "es_MX", "spanish", "Spanish_Spain.1252");
$basePath = dirname(dirname(__FILE__))."\\LOGS\\";
if(isset($_REQUEST["response"])) {
    echo "Browsing for {$basePath}19*\\*.log\n";
    $datepaths=glob("{$basePath}19*\\*.log",GLOB_MARK);
    echo "Found ".count($datepaths)." instances:\n";
    for($i=0;isset($datepaths[$i]);$i++) {
        echo ".";
        if(($i+1)%100===0) echo "\n";
        $found=false;
        $line=0;
        //if ($i<100) echo "TEST $datepaths[$i]\n";
        $fh = fopen($datepaths[$i], 'r');
        while (($buffer = fgets($fh)) !== FALSE) {
            $line++;
            if (strpos($buffer,"I-080")!==false) {
                if (preg_match('/update|insert/i', $buffer)) {
                    if (!$found) {
                        if(($i+1)%100!==0) echo "\n";
                        $found=true;
                        echo $datepaths[$i]."\n";
                    }
                    echo " [line: $line] $buffer\n";
                }
            }
        }
        //if ($i<100) echo "Total lines=$line";
        //if ($found) {
            ob_flush();
            flush();
        //}
    }
    echo "END RESULT";
    die();
}
?>
<html>
    <head>
        <title>Ajax Streaming Test</title>
        <script type="text/javascript">
            var blocked=false;
        </script>
    </head>
    <body>
        <center><span style="text-decoration: underline;cursor: pointer;" id="test">START</span></center>
        <div><pre id="result"></pre></div>
        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="document.getElementById('test').onclick=function() {
            if(blocked) return;
            blocked=true;
            console.log('begin test');
            xhr = new XMLHttpRequest();
            console.log('begin request');
            xhr.open('GET', 'tracelog.php?response=1', true);
            xhr.onprogress = function(e) {
                document.getElementById('result').textContent=e.currentTarget.responseText;
            }
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    document.getElementById('result').textContent=xhr.responseText;
                    blocked=false;
                    console.log('end test');
                }
            }
            xhr.send();
            console.log('end request');
        };this.parentNode.removeChild(this);">
    </body>
</html>
