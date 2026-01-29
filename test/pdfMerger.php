<?php
if (isset($_FILES["f"])) {
    header('charset=UTF-8');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once dirname(__DIR__)."/bootstrap.php";
    $files=getFixedFileArray($_FILES["f"]);
    // [{"name":"docs.pdf","type":"application/pdf","tmp_name":"C:\\PHP7\\temp\\php2678.tmp","error":0,"size":587701},{"name":"avion.pdf","type":"application/pdf","tmp_name":"C:\\PHP7\\temp\\php2679.tmp","error":0,"size":2212}]
    require_once "clases/PDF.php";
    $pdfObj=new PDF($files[0]["tmp_name"]);
    $newName=$pdfObj->saveMergedFile($files[1]["tmp_name"],null,"E:/mergedFile.pdf");
    successNDie("Integracion completa",["newname"=>$newName],"test");
}
?>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/">
    <title>PDF Merger</title>
    <link href="css/general.php" rel="stylesheet" type="text/css">
    <script src="scripts/general.js?ver=5.0o"></script>
    <script>
      function addFiles(evt) {
        console.log("addFiles", evt);
        if (!evt) return;
        const tgt=evt.target?evt.target:false;
        if (!tgt) return;
        readyService("test/pdfMerger.php",{f:tgt.files},(jobj, extra)=>{
          console.log("RESPONSE: \n"+JSON.stringify(jobj,jsonCircularReplacer())+"\nEXTRA: \n"+JSON.stringify(extra,jsonCircularReplacer()));
        }, (msg, txt, extra)=>{
          console.log("MSG: \n"+msg+"\nTXT: \n"+txt+"\nEXTRA: \n"+JSON.stringify(extra,jsonCircularReplacer()));
        });
      }
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>PDF Merger</h1>
      <div id="area_detalle">
        <label>Selecciona los archivos PDF que quieras unir:<input type="file" name="f" accept=".pdf" onchange="addFiles(event);" multiple></label>
      </div>
    </div>
  </body>
</html>
