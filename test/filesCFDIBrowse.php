<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Usuarios.php";
ini_set('max_execution_time', 600);
$req_start_time=+$_SERVER["REQUEST_TIME_FLOAT"];
$ini_max_time=+ini_get('max_execution_time');
$filePath = $_SERVER['DOCUMENT_ROOT']."archivos/";
$invoicesPath = $filePath."*/*/*/*.{xml,pdf}";
$invoicesFlag = GLOB_BRACE;
function getContents($path,$pattern="*",$flags=0) {
    global $req_start_time, $ini_max_time;
    return glob($path.$pattern,$flags);
}
function getContentList($fileArray, $pathToCut="", $isRecursive=false) {
    global $req_start_time, $ini_max_time;
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
function getCategorizedFiles($pathPattern, $flags=0) {
    global $req_start_time, $ini_max_time;
    $filelist=glob($pathPattern, $flags);
    return $catList;
}
echo "<html><head><script>function fee(al,eb){if(Array.from)Array.from(al).forEach(eb);else [].forEach.call(al, eb);}function toggleSubList(event){let sl=false;const tgt=event.target;const cl=tgt.firstElementChild;if(cl&&cl.tagName=='UL'&&cl.className=='tracelist')sl=cl;else{const pl=tgt.parentNode;if(pl.tagName=='UL'&&pl.className=='tracelist')sl=pl;}if(sl){if(sl.style.display=='none'){fee(document.getElementsByClassName('tracelist'),function(el){if(el.style.display='block')el.style.display='none';});sl.style.display='block';}else sl.style.display='none';}}</script></head><body>\n";
$categoryList=getCategorizedFiles($invoicesPath,$invoicesFlag);
$userList=array_keys($categoryList["user"]);
$keysList=array_keys($categoryList["keys"]);
$nouserList=array_keys($categoryList["nouser"]);
sort($userList);
sort($keysList);
sort($nouserList);
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
