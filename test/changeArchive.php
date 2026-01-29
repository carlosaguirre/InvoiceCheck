<?php
require_once dirname(__DIR__)."/bootstrap.php";
$tieneUsuario=hasUser();
$esSuperAdmin = $tieneUsuario && getUser()->nombre==="admin";
if(!$esSuperAdmin) {
  if ($tieneUsuario) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
  }
  header("Location: /".$_project_name."/");
  die("Redirecting to /".$_project_name."/");
}
$result="";
$requireDeleteConfirmation=false;
$file=$_FILES["fileDoc"]??[];
$filePath=$_POST["filePath"]??"";
if (isset($filePath[0])) {
  $filePath=str_replace("/", "\\", $filePath);
  $fileHref=str_replace("\\", "/", $filePath);
  $fullPath="C:\\Apache24\\htdocs\\invoice\\$filePath";
  $pathParts=pathinfo($fullPath);
  $onlyPath=$pathParts["dirname"];
  $extension=".".$pathParts["extension"];
  $lessExt=-1*strlen($extension);
  $basename=basename($fullPath,$extension);
  $basePath=substr($fullPath, 0, $lessExt);
}
$log=[];
$keepFilePath="";
if (empty($file)) {
  if (!isset($fullPath)) $result="";
  else if (file_exists($fullPath)) {
    if (isset($_POST["confirm"][0]) && $_POST["confirm"]==="YES") {
      if (unlink($fullPath)) $result="FILE DELETED SUCCESSFULLY";
      else $result="COULD NOT DELETE FILE";
    } else {
      $keepFilePath=$filePath;
      $result="REQUIRE CONFIRMATION";
      $requireDeleteConfirmation=true;
    }
  }
} else {
  if (!isset($fullPath)) $result="MISSING PATH TO SAVE";
  else if (file_exists($fullPath)) {
    $log[]="File exists: $fullPath";
    $tmstmp=time()-1672500000;
    $log[]="Tmstmp= '{$tmstmp}'";
    $oldPath=$basePath."_".$tmstmp.$extension;
    $log[]="Old Path= '{$oldPath}'";
    $isR1=rename($fullPath, $oldPath);
    if ($isR1) {
      $baseHref=substr($fileHref, 0 , $lessExt);
      $log[]="Base Href= '{$baseHref}'";
      $oldHref=$baseHref."_".$tmstmp.$extension;
      $log[]="Old Href= '{$oldHref}'";
      $secs=microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
      $result="RENAMED <A href=\"{$oldHref}?v={$secs}\">OLD</A>. ";
      sleep(1);
      $isR2=move_uploaded_file($file["tmp_name"],$fullPath);
      if ($isR2) {
        $result.="AND <A href=\"{$fileHref}?v={$secs}\">NEW</A>. ";
      } else $result.=", BUT COULD NOT REPLACE WITH UPLOADED FILE. ";
    } else $result="COULD NOT RENAME OLD. ";
  } else {
    if (!file_exists($onlyPath)) {
      $isR3=mkdir($onlyPath,0777,true);
      $isD=false;
    } else {
      $isR3=is_dir($onlyPath);
      $isD=true;
    }
    if ($isR3) {
      $isR4=move_uploaded_file($file["tmp_name"], $fullPath);
      if ($isR4) $result="MOVED UPLOADED FILE SUCCESSFULLY";
      else $result="COULD NOT MOVE UPLOADED FILE";
    } else {
      $result="PATH INVALID";
      if ($isD) $result.=", IT IS NOT A DIRECTORY";
      else $result.=", COULD NOT CREATE DIRECTORY";
    }
    //$result="NOT FOUND: {$filePath}\r\n".json_encode($_FILES["fileDoc"]);
  }
}
if (isset($keepFilePath[0])) $keepFilePath=" value=\"{$keepFilePath}\"";
?>
<html>
  <head>
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_blank">
    <title>Change archive</title>
    <script>
      function extensionFiltering() {
        const fpe=document.getElementById("filePath");
        const fde=document.getElementById("fileDoc");
        const fra=document.getElementById("fileReplaceArea");
        const dca=document.getElementById("deleteConfirmationArea");
        const msg=document.getElementById("msg");
        let fpv=fpe.value;
        if (fpv.startsWith("http://")) fpv=fpv.slice(7);
        if (fpv.startsWith("invoicecheck.dyndns-web.com:81")) fpv=fpv.slice(30);
        if (fpv.startsWith("/")) fpv=fpv.slice(1);
        if (fpv.startsWith("invoice/")) fpv=fpv.slice(8);
        if (fpv!==fpe.value) fpe.value=fpv;
        const ptIdx=fpv.lastIndexOf(".");
        if (ptIdx>=0) {
          const ext=fpv.slice(ptIdx);
          if (ext.length>1) {
            fra.style.display="block";
            dca.style.display="none";
            while(msg.firstChild) msg.removeChild(msg.firstChild);
            let elem=document.createTextNode("Ready to change ");
            msg.appendChild(elem);
            //console.log("MSG: ",msg);
            elem=document.createElement("A");
            elem.href=fpv+"?v="+Date.now();
            elem.target="file";
            elem.textContent="file";
            msg.appendChild(elem);
            //console.log("Before setting accept="+ext);
            fde.accept=ext;
            //console.log("After setting accept="+ext);
          } else {
            fra.style.display="none";
            msg.textContent="";
            //console.log("NO EXT");
          }
        } else {
          fra.style.display="none";
          msg.textContent="";
        }
      }
      <?php foreach ($log as $line) { echo "console.log(\"$line\");"; } ?>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>CHANGE ARCHIVE</h1>
      <div id="area_detalle">
        <form method="post" name="formUploadFile" target="_self" enctype="multipart/form-data">
        PATH: <input type="text" id="filePath" name="filePath" size="70" autofocus oninput="extensionFiltering();"<?=$keepFilePath?>><BR/>
        <div id="fileReplaceArea" style="display: none;">
        REPLACEMENT: <input type="file" id="fileDoc" name="fileDoc">
        <input type="submit" name="sendFile" value="Enviar"></div>
        <div id="deleteConfirmationArea" style="display: <?=$requireDeleteConfirmation?"block":"none"?>;">
          DELETE CONFIRMATION: <input type="submit" name="confirm" value="YES">
        </div>
        <div style="color: red;font-weight: bold;" id="msg"><?= $result ?></div>
        </form>
      </div>
    </div>
  </body>
</html>
