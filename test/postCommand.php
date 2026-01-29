<?php
require_once dirname(__DIR__)."/bootstrap.php";

//echo "PINGREAL\n";
if (isset($_POST["command"])) {
    //echo "COMMAND = ".$_POST["command"]."\n";
    $commandList = explode("\n", $_POST["command"]);
} else $commandList = ["/bin/ping -n 10 127.0.0.1"];
    
flush_buffers();

foreach($commandList as $cmd) {
    echo "Running: ".$cmd."\n";
    $proc = popen($cmd, 'r');
    while (!feof($proc)) {
        echo ".".fread($proc, 4096);
        
        flush_buffers();
        sleep(0.2);
    }
    echo "Command Stopped\n";
    pclose($proc);
}

function flush_buffers($doStart=true) {
    while (@ ob_end_flush()); // end all output buffers if any    
    if (ob_get_level()>0) ob_flush();
    flush();
    if ($doStart) ob_start();
}
