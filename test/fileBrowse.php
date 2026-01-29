<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Usuarios.php";
ini_set('max_execution_time', 600);
$req_start_time=+$_SERVER["REQUEST_TIME_FLOAT"];
//$start_time=microtime(true);
$ini_max_time=+ini_get('max_execution_time');
//$before_timeout=$ini_max_time-($start_time-$req_start_time);
$usrObj=new Usuarios();
$usrObj->rows_per_page=0;
$usrData=$usrObj->getData(false,0,"nombre");
$usrList=array_column($usrData, "nombre");
//echo "<!-- USER LIST: ".json_encode($usrList)." -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
$logsPath = $_SERVER['DOCUMENT_ROOT']."LOGS/";
$userLogPath = $logsPath."*/*.log";
function getContents($path,$pattern="*") {
    global $req_start_time, $ini_max_time;
    //echo "<!-- function getContents ( path='$path', pattern='$pattern' ) -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
    return glob($path.$pattern);
}
function getContentList($fileArray, $pathToCut="", $isRecursive=false) {
    global $req_start_time, $ini_max_time;
    //echo "<!-- function getContentList ( fileArray=[#".count($fileArray)."], pathToCut='$pathToCut', isRecursive='".(is_bool($isRecursive)?($isRecursive?"TRUE":"FALSE"):$isRecursive)."' ) -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
    $retVal="<ul>";
    foreach ($fileArray as $filename) {
        $retVal.="<li onclick=\"toggleSubList(event);\">";
        if (isset($pathToCut[0])) $retVal.=substr($filename, strlen($pathToCut));
        else $retVal.=$filename;
        if (is_dir($filename)) {
            $retVal.="/";
            if ($isRecursive) {
                $retVal.=getContentList(getContents($filename."/"));
            }
        }
        $retVal.="</li>";
    }
    $retVal.="</ul>";
    return $retVal;
}
function getCategorizedFiles($pathPattern) {
    global $usrList, $req_start_time, $ini_max_time;
    //echo "<!-- function getCategorizedFiles ( pathPattern='$pathPattern' ) -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
    $filelist=glob($pathPattern);
    //echo "<!-- glob results ".count($filelist)." entries -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
    $catList=["user"=>[],"nouser"=>[],"keys"=>[],"trace"=>[]];
    foreach ($filelist as $filename) {
        $originalFilename = $filename;
        //echo "<!-- USER TEST $filename -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
        $lastSlashIndex=strrpos($filename, "/");
        if ($lastSlashIndex!==false) $filename=substr($filename, $lastSlashIndex+1,-4);
        if ("read_"===substr($filename, 0, 5)) $filename=substr($filename, 5);
        if ("action_"===substr($filename, 0 ,7)) $filename=substr($filename, 7);
        if ("_read"===substr($filename, -5)) $filename=substr($filename, 0, -5);
        if ("_action"===substr($filename, -7)) $filename=substr($filename, 0, -7);
        if (in_array($filename, $usrList)) {
            if (!isset($catList["user"][$filename])) $catList["user"][$filename]=1;
            else $catList["user"][$filename]++;
            //echo "<!-- IS USER $filename ".$catList["user"][$filename]." -->\n";
        } else if (in_array($filename, ["_logoff","logoff","error","connection","tareaPagos","print","cfdi","backup_admin","ftp"])) {
            if ($filename==="_logoff") $filename="logoff";
            if (!isset($catList["keys"][$filename])) $catList["keys"][$filename]=1;
            else $catList["keys"][$filename]++;
            //echo "<!-- IS KEY $filename ".$catList["keys"][$filename]." -->\n";
        } else {
            if (!isset($catList["nouser"][$filename])) $catList["nouser"][$filename]=1;
            else $catList["nouser"][$filename]++;
            echo "<!-- IS NOT USER $filename ".$catList["nouser"][$filename]." FROM $originalFilename -->\n";
        }
        if (!isset($catList["trace"][$filename])) $catList["trace"][$filename]=[];
        $catList["trace"][$filename][]=$originalFilename;
    }
    return $catList;
}
function addInfo(&$arr, $cat, $trc) {
    global $logsPath;
    $cutIdx=strlen($logsPath);
    foreach ($arr as $idx => $value) {
        if (isset($cat[$value])) {
            $arr[$idx]=$value." (".$cat[$value].")";
            if (isset($trc[$value])) {
                $arr[$idx].="<ul class=\"tracelist\" style=\"display:none;\">";
                foreach ($trc[$value] as $trcPath) {
                    $arr[$idx].="<li>".substr($trcPath, $cutIdx)."</li>";
                }
                $arr[$idx].="</ul>";
            }
        }
    }
}
echo "<html><head><script>function fee(al,eb){if(Array.from)Array.from(al).forEach(eb);else [].forEach.call(al, eb);}function toggleSubList(event){let sl=false;const tgt=event.target;const cl=tgt.firstElementChild;if(cl&&cl.tagName=='UL'&&cl.className=='tracelist')sl=cl;else{const pl=tgt.parentNode;if(pl.tagName=='UL'&&pl.className=='tracelist')sl=pl;}if(sl){if(sl.style.display=='none'){fee(document.getElementsByClassName('tracelist'),function(el){if(el.style.display='block')el.style.display='none';});sl.style.display='block';}else sl.style.display='none';}}</script></head><body>\n";
$categoryList=getCategorizedFiles($userLogPath);
$userList=array_keys($categoryList["user"]);
$keysList=array_keys($categoryList["keys"]);
$nouserList=array_keys($categoryList["nouser"]);
sort($userList);
sort($keysList);
sort($nouserList);
addInfo($userList,$categoryList["user"],$categoryList["trace"]);
addInfo($keysList,$categoryList["keys"],$categoryList["trace"]);
addInfo($nouserList,$categoryList["nouser"],$categoryList["trace"]);
echo "<h1>Users in Logs</h1>";
echo getContentList($userList);
echo "<h1>Reserved in Logs</h1>";
echo getContentList($keysList);
echo "<h1>Not Users in Logs</h1>";
echo getContentList($nouserList);
echo "</body></html>";
echo "<!-- -".($ini_max_time-(microtime(true)-$req_start_time))." -->\n";
// obtener texto de la base y desplegarlo en pantalla.
// en campos editables agregar el texto con html_entity_decode
// al guardar a la base de nuevo aplicar htmlentities
