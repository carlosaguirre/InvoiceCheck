<?php
header('charset=UTF-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
require_once "templates/generalScript.php";
require_once "clases/FTP.php";
/*
if (!isset($GLOBALS['ftp_avausr'])) {
  $GLOBALS['ftp_avausr'] = 'avance';
  MIFTP::log("ADDED GLOBALS ftp_avausr: '".$GLOBALS['ftp_avausr']."'' = '$ftp_avausr'");
}
if (!isset($GLOBALS['ftp_avapwd'])) {
  $GLOBALS['ftp_avapwd'] = 'Ava#2722';
  MIFTP::log("ADDED GLOBALS ftp_avausr: '".$GLOBALS['ftp_avapwd']."'' = '$ftp_avapwd'");
}
*/
global $timeStart,$maxTime,$counts,$ftpObj;
$timeStart=time();
$maxTime=500;
$counts=[];
$milog=["TRACE START=$timeStart"];
$ftpObj = MIFTP::newInstanceAvance();
if ($ftpObj===null) {
  $list=array_merge(["SERVER: $ftp_servidor", "USER: $ftp_avausr", "PWD: $ftp_avapwd", "EXPORT: $ftp_exportPath", "SUPPORT: $ftp_supportPath", "POLICY: $ftp_policyPath"],implode("\n", MIFTP::log()));
} else {
  global $validPaths, $validWidths; //, $ftp_avausr, $ftp_usuario
  $validPaths=[[/*$ftp_exportPath,*/ $ftp_supportPath, $ftp_policyPath],["APEL","APSA","BIDASOA","CASABLANCA","COREPACK","DANIEL","DEMO","DESA","ENVASES","ESMERALDA","FIDEICOMIS","FIDEMIFEL","FOAMYMEX","GLAMA","JLA","JYL","LAISA","LAMINADOS","LOCAL","MARLOT","MELO","MORYSAN","PAPEL","PRODUCTORA","PUBLICO","TPUBLICO","RGA","SERVICIOS","SKARTON","TEST"],["PUBLICO","TPUBLICO"]];
  $validWidths=[80,45,50,50,60,30,22,40];
  $list = semiRecursiveList();
  if (isset($list[0]) && !isset($list[0][0])) array_shift($list);
  $nlst = count($list);
  echo "<!-- $nlst registro".($nlst==1?"":"s")." -->\n";
  $ftplog=MIFTP::log();
  $linelog=explode("\n", $ftplog);
  $nmlg = count($linelog);
  echo "<!-- $nmlg linea".($nmlg==1?"":"s")." log -->\n";
  $list = array_merge($list,[" - - - - - - - - - - - - - -",json_encode($counts)," - - - - - - - - - - - - - -"], $linelog);
}
if (!isset($list)) {
  $milog[]="LISTA VACIA";
  $list=[];
}
$timeEnd=time();
$milog[]="TRACE END=$timeEnd";
$milog[]="TRACE TOTAL=".($timeEnd-$timeStart);
$list = array_merge($list,$milog);
function semiRecursiveList($depth=0, $path=null) {
  global $validPaths, $validWidths, $ftp_avausr, $timeStart, $maxTime, $counts, $ftpObj; $lst = [];
  if (isset($validPaths[$depth])) {
    if ($path===null) {
      foreach($validPaths[$depth] as $path) {
        if (substr($path, -1)!=="/") $path.="/";
        if (isset($counts["loopNull"])) $counts["loopNull"]++;
        else $counts["loopNull"]=1;
        $sublist=semiRecursiveList($depth, $path);
        if (isset($sublist[0])) {
          $lst=array_merge($lst,$sublist);
        }
      }
    } else {
      $rawfiles=$ftpObj->list($path,1,false); $nextDepthList=[];
      foreach ($rawfiles as $rawidx=>$rawfile) {
        $timeLapse=time()-$timeStart;
        if ($timeLapse>$maxTime) {
          if (empty($lst)) $lst=["","<div class='boldValue'>$path</div>"];
          $lst[]="<div class='bgred'>...".($rawidx+1)."/".count($rawfiles)."</div>";
          break;
        }
        $block=preg_split("/[\s]+/", $rawfile);
        $fnam=$block[8]??"";
        if (isset($fnam[0])) {
          if ($fnam==="."||$fnam==="..") continue;
          $fext=substr($fnam,-4); $ftyp=$block[0]; $fown=$block[2];
          if ($ftyp[0]==="d") {
            if (in_array($fnam, $validPaths[$depth+1])) $nextDepthList[]=$path.$fnam;
            else {
              if (isset($counts["dirOther"])) $counts["dirOther"]++;
              else $counts["dirOther"]=1;
              continue;
            }
            if ($ftyp==="drwxrwxrwx") {
              // $block[0]="<div class='bggreen2'>".$block[0]."</div>";
              if (isset($counts["dirOK"])) $counts["dirOK"]++;
              else $counts["dirOK"]=1;
              continue;
            } else if ($ftp_avausr===$fown) {
              $ftpObj->permiso(0777, $path.$fnam);
              $block[0]="<div class='darkRedLabel boldValue'>".$block[0]."</div>";
              if (isset($counts["dirFix"])) $counts["dirFix"]++;
              else $counts["dirFix"]=1;
            } else {
              // $block[0]="<div class='bgbrown1'>".$block[0]."</div>";
              if (isset($counts["dir_$fown"])) $counts["dir_$fown"]++;
              else $counts["dir_$fown"]=1;
              continue;
            }
          } else {
            $fext=strtolower(substr($fnam,-4));
            if ($fext!==".xml" && $fext!==".pdf") {
              //if (isset($counts["arcUnk"])) $counts["arcUnk"]++;
              //else $counts["arcUnk"]=1;
              if ($fext[0]==="." || ($ptIdx=strpos($fext, "."))!==false) {
                if ($fext[0]!==".") $fext=substr($fext, $ptIdx);
                if (isset($counts["arc$fext"])) $counts["arc$fext"]++;
                else $counts["arc$fext"]=1;
              } else {
                if (isset($counts["arcNoExt"])) $counts["arcNoExt"]++;
                else $counts["arcNoExt"]=1;
              }
              continue;
            }
            if ($ftyp==="-rw-rw-r--") {
              // $block[0]="<div class='bggreen2'>".$block[0]."</div>";
              if (isset($counts["arcOK"])) $counts["arcOK"]++;
              else $counts["arcOK"]=1;
              continue;
            } else if ($ftp_avausr===$fown) {
              $ftpObj->permiso(0664, $path.$fnam);
              $block[0]="<div class='maroon boldValue'>".$block[0]."</div>";
              if (isset($counts["arcFix"])) $counts["arcFix"]++;
              else $counts["arcFix"]=1;
            } else {
              // $block[0]="<div class='bgbrown1'>".$block[0]."</div>";
              if (isset($counts["arc_$fown"])) $counts["arc_$fown"]++;
              else $counts["arc_$fown"]=1;
              continue;
            }
          }
          $line="";
          for($i=0; isset($block[$i]); $i++) {
            $line.="<div class='inblock brdr1d'".(isset($validWidths[$i])?" style='width: ".$validWidths[$i]."px'":"").">".$block[$i]."</div>";
          }
          if (empty($lst)) $lst=["","<div class='boldValue'>$path</div>"];
          $lst[]=$line;
        } else {
          if (empty($lst)) $lst=["","<div class='boldValue'>$path</div>"];
          $lst[]="<div class='bgblack'>".$rawfile."</div>";
        }
      }
      foreach ($nextDepthList as $nextIdx=>$nextPath) {
        $timeLapse=time()-$timeStart;
        if ($timeLapse>$maxTime) {
          $lst[]="";
          $line="...".($nextIdx+1)."/".count($nextDepthList)."... <b>$nextPath</b>";
          for ($n=$nextIdx+1; isset($nextDepthList[$n]); $n++) $line.=", <b>".basename($nextDepthList[$n])."</b>";
          $lst[]="<div class='bgred'>$line</div>";
          break;
        }
        if (substr($nextPath, -1)!=="/") $nextPath.="/";
        if (isset($counts["subLoop"])) $counts["subLoop"]++;
        else $counts["subLoop"]=1;
        $sublist=semiRecursiveList($depth+1, $nextPath);
        if (isset($sublist[0])) $lst = array_merge($lst, $sublist);
      }
    }
  }
  return $lst;
}
?>
<html>
  <head>
    <title>Ajustar Permisos Avance</title>
    <meta charset="utf-8">
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
<?php
    echoGeneralScript();
?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
  </head>
  <body class="scrolli">
      <h1>Ajustar Permisos Avance</h1>
<?php
if (isset($list[0])) echo arr2List($list, ["OL","class='nowrap'"]);
else echo "<p><b>VACIO</b></p>";
?>
  </body>
</html>
