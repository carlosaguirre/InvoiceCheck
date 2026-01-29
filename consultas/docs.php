<?php
if (isset($_GET["pymtxt"])) {
    $filename=$_GET["pymtxt"];
    $absname="C:\\InvoiceCheckShare\\PAGOS\\".$filename;
    if (is_file($absname)) {
        header("Content-Type: text/plain");
        header("Content-Disposition: inline; filename:\"$filename\"");
        header("Content-Length: ".filesize($absname));
        header("Expires: 0");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        echo "---------- ---------- ---------- ----------\n";
        readfile($absname);
        die();
    }
}
require_once dirname(__DIR__)."/bootstrap.php";
$getKeys=["daydoc","pymlog","pymtxt","tstchk"];
$postKeys=["pymlog","lines","path","id","type","name"];
$sessionKeys=["docpath","doctype","docname","doctime"];
if (isset($_GET["daydoc"])) {
    require_once "clases/PDFCR.php";
    $pcrObj=new PDFCR(null, null, true);
    $filename="prueba".$_GET["daydoc"].".pdf";
    $absname=$pcrObj->getFilePath().$filename;
    if (!is_file($absname)) {
        doclog("FILE NOT FOUND","error",getValid($_GET,$getKeys)+["filename"=>$filename,"absname"=>$absname]);
        die(header("HTTP/1.0 404 NOT FOUND"));
    }
    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename:\"$filename\"");
    header("Content-Length: ".filesize($absname));
    header("Expires: Fri, 01 Jan 2010 05:00:00 GMT"); // TODO: generate today date
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
    readfile($absname);
} else if (isset($_GET["pymtxt"])) {
    $filename=$_GET["pymtxt"];
    $absname="C:\\InvoiceCheckShare\\PAGOS\\".$filename;
    if (!is_file($absname)) {
        doclog("FILE NOT FOUND","error",getValid($_GET,$getKeys)+["filename"=>$filename,"absname"=>$absname]);
        die(header("HTTP/1.0 404 NOT FOUND"));
    }
    //doclog("PYMTXT GET","docs",getValid($_GET,$getKeys));
    header("Content-Type: text/plain");
    //header("Content-Disposition: inline; filename:$_GET[pymtxt]");
    //header("Content-Length: ".filesize($absname));
    //header("Expires: Fri, 01 Jan 2010 05:00:00 GMT"); // TODO: generate today date
    //header("Cache-Control: no-cache");
    //header("Pragma: no-cache");
    //readfile($absname);
    echo file_get_contents($absname);
} else if (isset($_GET["pymlog"])) {
    doclog("PYMLOG GET","docs",getValid($_GET,$getKeys));
    $extraHeadSettings="<style>\n#pymlog_container {\n margin: 0 auto;\n text-align: center;\n width: calc(100% - 10px);\n height: calc(100% - 10px);\n padding-top: 5px;\n}\n#pymlog_screen {\n width: 100%;\n height: 100%;\n overflow: auto;\n background-color: rgba(33,33,33,0.33);\n text-align: center;\n border: solid 3px gray;\n}\n#pymlog_page {\n width: 612px;\n min-height: 200px;\n display: flex;\n flex-flow: column-reverse;\n background-color: white;\n border: 1px solid black;\n text-align: center;\n margin: 0 auto;\n}\n.pymlog_line {\n vertical-align: baseline;\n width: 100%; text-align: left;\n display: block;\n white-space: pre-wrap;\n word-wrap: break-word;\n border-bottom: 1px dotted gray;\n}\n.pymlog_line:nth-child(odd) {\n background-color: rgba(255, 255, 220, 0.2);\n}\n.pymlog_line:nth-child(even) {\n background-color: rgba(220, 220, 255, 0.2);\n}\n</style><script>function getPayFile(){
         readyService('consultas/docs.php',{pymlog:$_GET[pymlog],respType:'text',lines:ebyid('pymlog_page').getAttribute('ln')},rdyFnc,errFnc);
        }
        function rdyFnc(text,extra){
         const plp=ebyid('pymlog_page');
         if(plp){
          const ln=+plp.getAttribute('ln');
          const lines=text.split('\\n');
          console.log('oldLinLen='+ln+', newLinLen='+lines.length);
          if(ln<lines.length) {
           console.log('text=\\n'+text);
           fee(lines,oneLine=>plp.appendChild(ecrea({eName:'DIV',classList:'pymlog_line'+(oneLine.length>0?'':' hidden'),textContent:oneLine})));
           plp.setAttribute('ln',lines.length);
          }else if(ln>lines.length) console.log('Reduced Line Length');
          else console.log('Same Line Length');
         }else console.log('Not found pymlog_page');
        }
        function errFnc(messageError,responseText,extra){
         console.log('INI function errFnc: '+messageError+', RESP: ',responseText);
        }
        function iniBtnAct() {
            const bc=ebyid('bloque_central');
            if (bc) cladd(bc,'noHeader');
        }
        var actLk=false;
        var pfi=false;
        function doButtonAction(evt){
         if(actLk)return;
         actLk=true;
         if(pfi){
          clearTimeout(pfi);
          pfi=false;
          ebyid('btnAct').src='imagenes/icons/frontArrow.png';
         }else{
          pfi=setInterval(getPayFile,2000);
          ebyid('btnAct').src='imagenes/icons/crossRed.png';
         }
         setTimeout(()=>{actLk=false;},500);
        }</script>";
    $basePath="C:/InvoiceCheckShare/PAGOS/";
    $file=$basePath."pago".$_GET["pymlog"].".log";
    if (file_exists($file)) {
        $text=file_get_contents($file);
        $lines=preg_split("/\r\n|\n|\r/", $text);
        $linesLength=0;
        $linesContent="";
        foreach ($lines as $lineIdx=>$oneline) {
            $linesContent.="<div class='pymlog_line".(isset($oneline[0])?"":" hidden")."'>$oneline</div>";
            $linesLength++;
        }
        $contenido="<div id='pymlog_container'><div id='pymlog_screen' class='relative'><div id='pymlog_page' ln='$linesLength'>$linesContent";
        $contenido.="</div><img id='btnAct' src='imagenes/icons/crossRed.png' onclick='doButtonAction(event)' onload='iniBtnAct()' class='abs_se btn16'></div></div>";
        $contenido.="<script>pfi=setInterval(getPayFile,2000);</script>";
    } else {
        $extraHeadSettings="<meta http-equiv=\"refresh\" content=\"1\"><script></script>";
        $contenido="<div>Documento no encontrado: pago{$_GET["pymlog"]}.log</div>";
        //die(header("HTTP/1.0 404 DOCUMENTO NO ENCONTRADO"));
    }
} else if (isset($_POST["pymlog"])) {
    doclog("PYMLOG POST","docs",getValid($_POST,$postKeys));
    $basePath="C:/InvoiceCheckShare/PAGOS/";
    $file=$basePath."pago".$_POST["pymlog"].".log";
    $oldLines=+($_POST["lines"]??"0");
    if (file_exists($file)) {
        doclog("PYMLOG FILE","docs",["basePath"=>$basePath,"file"=>$file]); // ,"oldLines"=>$oldLines
        $lines=file($file);
        $numLines=count($lines);
        if ($oldLines!=$numLines) {
            doclog("PYMLOG LINES","docs",["newLines"=>$numLines]);
            header("Content-Type: text/plain; charset=UTF-8");
            header("Expires: Fri, 01 Jan 2010 05:00:00 GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            for ($i=0; $i<$numLines; $i++) { //($i=$numLines-1; $i>=0; $i--) { 
                echo $lines[$i];
            }
        }
    } else {
        doclog("PYMLOG ERR: DOCUMENTO NO ENCONTRADO","docs");
        echo "DOCUMENTO NO ENCONTRADO";
    }
} else if (isset($_GET["tstchk"])) {
    $docPath="C:/InvoiceCheckShare/tmp/cfdicheck/";
    $filename=$_GET["tstchk"].".xml";
    $absname=$docPath.$filename;
    if (!is_file($absname)) {
        doclog("FILE NOT FOUND","error",getValid($_GET,$getKeys)+["filename"=>$filename,"absname"=>$absname]);
        die(header("HTTP/1.0 404 NOT FOUND"));
    }
    header("Content-Type: text/xml");
    header("Content-Disposition: inline; filename:\"$filename\"");
    header("Content-Length: ".filesize($absname));
    header("Expires: Fri, 01 Jan 2010 05:00:00 GMT"); // TODO: generate today date + 10 min
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
    readfile($absname);
} else if(hasUser()) {
	$basePath="C:/InvoiceCheckShare/invoiceDocs/";
    $postPath=$_POST["path"]??"";
    $fileId=$_POST["id"]??"";
    $filetype=$_POST["type"]??"";
    $filename=$_POST["name"]??"";

    if (!empty($fileId) && $postPath==="temporal") {
        require_once "clases/Temporales.php";
        $tmpObj=new Temporales();
        $postPath.="/".$tmpObj->obtenerNombre($fileId);
        $_SESSION["docpath"]=$postPath;
        if (isset($filetype[0])) $_SESSION["doctype"]=$filetype;
        if (isset($filename[0])) $_SESSION["docname"]=$filename;
        $_SESSION["doctime"]=time()+(15*60); // expira en 15 minutos
        doclog("Consulta Temporal","docs",getValid($_SESSION,$sessionKeys)+["calcPath"=>$postPath]);
    } else if (isset($postPath[0])) {
        //$postPath="viajes/".$postPath;
        $_SESSION["docpath"]=$postPath;
        if (isset($filetype[0])) $_SESSION["doctype"]=$filetype;
        if (isset($filename[0])) $_SESSION["docname"]=$filename;
        $_SESSION["doctime"]=time()+(15*60); // expira en 15 minutos
        doclog("Consulta Archivo","docs",getValid($_SESSION,$sessionKeys));
    } else if (isset($_SESSION["docpath"])) {
        doclog("Consulta x Sesión","docs",getValid($_SESSION,$sessionKeys));
        $currentTime=time();
        if (isset($_SESSION["doctime"]) && ($_SESSION["doctime"]>$currentTime)) {
            $postPath=$_SESSION["docpath"];
            if (isset($_SESSION["doctype"])) $filetype=$_SESSION["doctype"];
            if (isset($_SESSION["docname"])) $filename=$_SESSION["docname"];
        } else {
            unset($_SESSION["docpath"]);
            unset($_SESSION["doctime"]);
            unset($_SESSION["docname"]);
            unset($_SESSION["doctype"]);
            $contenido="El tiempo de consulta del documento expiró";
        }
    } else {
        doclog("Consulta sin datos", "error",["get"=>getValid($_GET,$getKeys),"post"=>getValid($_POST,$postKeys),"session"=>getValid($_SESSION,$sessionKeys)]);
        $contenido="No se recibieron datos de documento";
    }
    if (isset($postPath[0])) {
        $file=$basePath.$postPath;
        if (file_exists($file)) {
            header("Content-Description: File Transfer");
            if (isset($filetype[0])) header("Content-Type: $filetype");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            if (isset($filename[0])) header("Content-Disposition: attachment; filename:\"$filename\"");
            header("Content-Length: ".filesize($file));
            //header("Expires: Fri, 01 Jan 2010 05:00:00 GMT"); // TODO: generate today date
            //header("Cache-Control: no-cache");
            //header("Pragma: no-cache");
            header("Pragma: public");
            flush();
            readfile($file);
        } else {
            $pathChunks=explode("/",$file);
            array_pop($pathChunks);
            $path = implode("/", $pathChunks)."/";
            if (file_exists($path)) {
                doclog("FILE NOT FOUND","error",["get"=>getValid($_GET,$getKeys),"post"=>getValid($_POST,$postKeys),"session"=>getValid($_SESSION,$sessionKeys),"file"=>$file]);
                die(header("HTTP/1.0 404 YA NO EXISTE EL DOCUMENTO"));
            }
            $contenido="La ruta no está disponible: $path";
        }
    }
} else {
    doclog("Consulta sin sesión", "error",["get"=>getValid($_GET,$getKeys),"post"=>getValid($_POST,$postKeys),"session"=>getValid($_SESSION,$sessionKeys)]);
    $contenido="No se recibieron datos de documento";
}
if (isset($contenido[0])) {
    $contenido.="<!-- SESSION:\n".arr2str(array_intersect_key($_SESSION,array_flip(["docpath","doctype","docname","doctime"])))."-->";

    $contenido.="<!-- POST:\n".arr2str(array_intersect_key($_POST,array_flip(["id","path","type","name","action"])))."-->";
    //$contenido.="<!-- GET:\n".arr2str(array_intersect_key($_GET,array_flip($keys)))."-->";
    //$contenido.="<!-- GLOBALS:\n".arr2str($GLOBALS)."-->";
    include "templates/inicio.php";
}
function getValid($dataArray, $keysArray) {
    //doclog("docs.getValid","docs",["dataArray"=>$dataArray,"keysArray"=>$keysArray]);
    return array_filter($dataArray, function ($key) use ($keysArray) {
        $result=in_array($key, $keysArray); 
        //doclog("docs.getValid.(array check)","docs",["key"=>$key,"result"=>$result?"true":"false"]);
        return $result;
    }, ARRAY_FILTER_USE_KEY);
}
