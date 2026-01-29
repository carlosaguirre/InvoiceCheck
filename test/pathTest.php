<?php // PATH TEST: Read path, check name, size, date, finfo type, check owner and other permissions, fopen file
if(isset($_GET['clear'])) {
  echo "-";
  die();
}
require_once dirname(__DIR__)."/bootstrap.php";
global $_project_name;
global $autoUploadPath, $autoUploadErrPath, $finfo, $su;
$finfo=new finfo(FILEINFO_MIME_TYPE);
$su = new COM("ADsSecurityUtility");

function getDirList($path) {
  global $finfo, $su;
  $pathLen=strlen($path);
  $list=glob($path."*");
  $res=[]; // type, errors, data
  if (isset($list[0])) {
    $GLOBALS["ignoreTmpList"]=["list"];
    foreach ($list as $filePath) {
      $data=["name"   => substr($filePath,$pathLen),
             "exists" => file_exists($filePath),
             "isdir"  => is_dir($filePath),
             "rdable" => is_readable($filePath),
             "type"   => "",
             "fsize"  => sizeFix(filesize($filePath)),
             "fdate"  => date("Y/m/d",filemtime($filePath)),
             "perms"  => "",
             "errors" => ""];
      try {
        $data["type"] = $finfo->file($filePath);
        $securityInfo=$su->GetSecurityDescriptor($filePath, 1, 1);
        $data["perms"] = /*fileowner($filePath)*/permFix(fileperms($filePath));
        $data["errors"]="OWNER:".$securityInfo->owner;
        //$data["errors"].="|Revision=".$securityInfo->revision;
        //$data["errors"].="|Group=".$securityInfo->group;
      } catch (Exception $x) {
        $data["errors"]=implode(" | ", getErrorData($x));
      }
      if ($data["isdir"]) $data["data"]=getDirList($filePath."/");
      $res[]=$data;
    }
    unset($GLOBALS["ignoreTmpList"]);
  }
  return $res;
}
function displayDataAsTable($data,$pfx="",$showHeaders=true) {
  if (isset($data[0])) {
    if ($showHeaders) echo "<TABLE><THEAD><TR><TH>NAME</TH><TH>TYPE</TH><TH>SIZE</TH><TH>DATE</TH><TH>PERMS</TH><TH>COMMENTS</TH></TR></THEAD><TBODY>";
    $prefix=$pfx;
    if (isset($pfx[0])) {
      switch ((-1+strlen($pfx)/12)%6) {
         case 0: $prefix.="&bull;"; break;
         case 1: $prefix.="&utrif;"; break;
         case 2: $prefix.="&diams;"; break;
         case 3: $prefix.="&sstarf;"; break;
         case 4: $prefix.="&sext;"; break;
         case 5: $prefix.="&cir;"; break;
         default: $prefix.="&cross;";
       } 
      $prefix.="&nbsp;";
    }
    $GLOBALS["ignoreTmpList"]=["data"];
    foreach($data as $idx=>$path) {
      if (!$path["exists"] || ($path["isdir"] && ($path["name"]==="Emitidos" || !isset($path["data"][0])))) continue;
      echo "<TR>";
      $classList="";
      if (!$path["rdable"]) $classList=" stroke";
      $cellAtts=" class='lefted padr5{$classList}'".($path["isdir"]?" colspan='6'":"");
      if ($path["isdir"]) $path["name"].="/";
      echo "<TD{$cellAtts}>{$prefix}$path[name]</TD>";
      if (!$path["isdir"]) {
        $cellAtts=isset($classList[0])?" class='$classList'":"";
        echo "<TD{$cellAtts}>$path[type]</TD>";
        echo "<TD class='righted padr5{$classList}'>$path[fsize]</TD>";
        echo "<TD{$cellAtts}>$path[fdate]</TD>";
        echo "<TD{$cellAtts}>$path[perms]</TD>";
        echo "<TD{$cellAtts}>$path[errors]</TD>";
      }
      echo "</TR>";
      if ($path["isdir"] && isset($path["data"]) && $path["name"]!=="Emitidos") displayDataAsTable($path["data"],$pfx."&nbsp;&nbsp;",false);
    }
    unset($GLOBALS["ignoreTmpList"]);
    if ($showHeaders) echo "</TBODY></TABLE>";
  }
}
?>
<html>
  <head>
    <title>Path'n Files Test</title>
    <link href="../css/general.php" rel="stylesheet" type="text/css"/>
  </head>
  <body class="maxWidFlow">
    <b>Autoupload Path: </b><i><?= $autoUploadPath ?></i>
<?php 
$data=getDirList($autoUploadPath);
if (isset($data[0])) echo " (".count($data).")<DIV class='all_space scroll50-30'>";
displayDataAsTable($data);
if (isset($data[0])) echo "</DIV>";
?>
    <hr><b>Autoupload ErrPath: </b><i><?= $autoUploadErrPath ?></i>
<?php
$data=getDirList($autoUploadErrPath);
if (isset($data[0])) echo " (".count($data).")<DIV class='all_space scroll50-30'>";
displayDataAsTable($data);
if (isset($data[0])) echo "</DIV>";
?>
  </body>
</html>
