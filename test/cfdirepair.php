<?php
if (isset($_FILES["fix"])) {
    //header('Content-type: text/plain');
    header('Content-type: application/xml');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $files=$_FILES["fix"];
    $tmpname=$files["tmp_name"];
    $name=$files["name"];
    if (is_array($tmpname)) $tmpname=$tmpname[0];
    if (is_array($name)) $name=$name[0];
    require_once dirname(__DIR__)."/bootstrap.php";
    $logData=["files"=>$files];
    if (file_exists($tmpname)) {
        require_once "clases/CFDI.php";
        $logtxt="";
        $fixedTextFileContents = CFDI::reparaXMLText(file_get_contents($tmpname), $log, $logtxt);
        if (substr($fixedTextFileContents, 0, 7)==="ERROR: ")
            echo "<?xml version\"1.0\" encoding=\"UTF-8\"?><error>".substr($fixedTextFileContents, 7)."</error>";
        else echo $fixedTextFileContents;
        if (isset($log[0])) $logData["log"]=$log;
        //if (isset($logtxt[0])) $logData["logtxt"]=$logtxt;
        doclog("$logtxt\n","cfdiRepair",$logData);
    } else {
        echo "<?xml version\"1.0\" encoding=\"UTF-8\"?><error>No existe el archivo '$tmpname'</error>";
    }
} else {
?>
<html>
<body>
<form method="POST" enctype="multipart/form-data">
FIX:<input type="file" name="fix" onchange="this.form.submit()">
</form>
</body>
</html>
<?php
}
