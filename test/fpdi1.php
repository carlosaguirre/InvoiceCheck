<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/PDF.php";
$invoicePath=$_SERVER['DOCUMENT_ROOT'];
$invoicePathLength=strlen($invoicePath);
$relativeStampPath=$_GET["path"]??"archivos";
$stampPath=$invoicePath.$relativeStampPath;
$stamps=glob($stampPath."/ST_*.pdf");
$stampList=[];
foreach ($stamps as $idx => $value) {
    $stampFile=substr($value, strlen($stampPath)+1);
    $normalFile=substr($stampFile,3);
    if (file_exists($stampPath."/".$normalFile))
        $stampList[]=$normalFile;
}
if (!isset($stampList[0])) {
    echo "<h1>No valid files</h1>";
    die();
}
function stampsAndDirs($path) {
    $stamps=glob($path."/ST_*.pdf");
    if (isset($stamps[0])) {
        global $invoicePathLength;
        echo substr($path,$invoicePathLength);
        $num=0;
        foreach ($stamps as $idx => $value) {
            $stampFile=substr($value, strlen($path)+1);
            $normalFile=substr($stampFile,3);
            if (file_exists($path."/".$normalFile)) {
                $num++;
            }
        }
        echo " #{$num}\n";
    }
    $dirs=glob($path."/*", GLOB_ONLYDIR);
    foreach ($dirs as $idx => $value) {
        $dirname=substr($value, strlen($path)+1);
        if (isset($dirname[3]) && !isset($dirname[4]) && $dirname[0]==="2"&&$dirname[1]==="0") {
            $year=intval($dirname);
            if ($year<2021) continue;
        }
        $sub=stampsAndDirs($value);
    }
}
/* * /
echo "<!-- \n";
stampsAndDirs($stampPath);
echo " -->\n";
/* */
/* RESULTS: <!-- 
archivos #1
archivos/APSA/2021/03 #8
archivos/APSA/2021/04 #21
archivos/BIDASOA/2021/03 #1
archivos/BIDASOA/2021/04 #7
archivos/CASABLANCA/2021/03 #6
archivos/CASABLANCA/2021/04 #18
archivos/COREPACK/2021/03 #6
archivos/COREPACK/2021/04 #11
archivos/DANIEL/2021/02 #1
archivos/DANIEL/2021/03 #59
archivos/DANIEL/2021/04 #51
archivos/DEMO/2021/04 #3
archivos/FOAMYMEX/2021/03 #14
archivos/FOAMYMEX/2021/04 #38
archivos/GLAMA/2021/03 #24
archivos/GLAMA/2021/04 #60
archivos/GLAMA/2021/05 #3
archivos/JYL/2021/03 #3
archivos/JYL/2021/04 #4
archivos/LAISA/2021/04 #5
archivos/LAISA/2021/05 #1
archivos/MELO/2021/03 #64
archivos/MELO/2021/04 #146
archivos/MELO/2021/05 #1
archivos/MORYSAN/2021/03 #10
archivos/MORYSAN/2021/04 #17
archivos/SKARTON/2021/03 #17
archivos/SKARTON/2021/04 #31
archivos/SKARTON/2021/05 #3
 -->*/
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_self">
    <title>PDF STAMP w/VIEWER</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>
    <script>
        var myState={pdf: null, currentPage: 1, zoom: 1};
        function viewPDF(pdfname) {
            pdfjsLib.getDocument(pdfname).then((pdf) => {
                myState.pdf = pdf;
                render();
            });
        }
        function render() {
            myState.pdf.getPage(myState.currentPage).then((page) => {
                var canvas = document.getElementById("pdf_renderer");
                var ctx = canvas.getContext("2d");
                var viewport = page.getViewport(myState.zoom);
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                page.render({canvasContext: ctx, viewport: viewport});
            });
        }
        function changePage(increment) {
            if (myState.pdf == null) return;
            var nextPage=myState.currentPage+increment;
            if (nextPage<1 || nextPage>myState.pdf._pdfInfo.numPages) return;
            myState.currentPage = nextPage;
            document.getElementById("current_page").value = myState.currentPage;
            render();
        }
        function setPage(e) {
            if (myState.pdf==null) return;
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code==13) {
                var desiredPage=document.getElementById("current_page").valueAsNumber;
                if (desiredPage>=1 && desiredPage<=myState.pdf._pdfInfo.numPages) {
                    myState.currentPage = desiredPage;
                    document.getElementById("current_page").value=desiredPage;
                    render();
                } else document.getElementById("current_page").value=myState.currentPage;
            }
        }
        function changePath(evt) {
            //console.log("INI function changePath",evt.target);
            const ste=document.getElementById("stampTree");
            const idx=ste.selectedIndex;
            const oe=ste.options[idx];
            viewPDF("../<?= $relativeStampPath ?>/ST_"+oe.text);
        }
    </script>
    <style type="text/css">
        #canvas_container {
            width: 800px;
            height: 450px;
            overflow: auto;
            background: #333;
            text-align: center;
            border: solid 3px;
        }
    </style>
</head>
<body>
    PATH:<SPAN style="border:1px solid black;margin:2px;padding:0 3px;"><?=$relativeStampPath?></SPAN>
<?php

$sitePath=$_SERVER["HTTP_ORIGIN"].$_SERVER["WEB_MD_PATH"];
//$imgPath = $invoicePath."imagenes/icons/";
$pdfpath=$relativeStampPath."/";// "archivos/MELO/2021/04/";
$pdfname=$stampList[0];// "test1u.pdf"; // "2019SIM180126RP0.pdf";
if (isset($stampList[1])) {
?>
    PICK FILE: <select id="stampTree" onchange="changePath(event);">
<?php
    foreach ($stampList as $idx => $filename) {
        echo "<option value=\"{$pdfpath}{$filename}\">{$filename}</option>";
    }
?>
</select>
<?php
} else {
?>
    FILE:<SPAN style="border:1px solid black;margin:2px;padding:0 3px;"><?=$stampList[0]?></SPAN>
<?php
}
echo "<SCRIPT>";
try {
    echo "console.log('CREATING PDF OBJ {$invoicePath}{$pdfpath}{$pdfname}');";
    //$pdf=new PDF($invoicePath.$pdfpath.$pdfname);
    //echo "console.log('Set First File');";
    //$mergedName=$pdf->saveMergedFile($invoicePath.$pdfpath."ST_".$pdfname, 1);
    //$pdfname=$mergedName;
    //echo "console.log('New Merged Filename={$mergedName}');";
} catch (Exception $ex) {
    echo "console.log('EXCEPTION: ".get_class($ex).", CODE: ".$ex->getCode().", MESSAGE: ".$ex->getMessage()."');";
}
echo "</SCRIPT>";
/*
$fullname=$invoicePath.$pdfpath.$pdfname;
if (file_exists($fullname)) {
    echo "<P>{$pdfpath}{$pdfname}";
    $stampname=$invoicePath.$pdfpath."ST_".$pdfname;
    if (file_exists($stampname)) echo " {EXISTS}";
    else {
        try {
            $pdf=new PDF($fullname);
            $stampMsg=$pdf->setStampFile($imgPath."sello1.png");
            if (isset($stampMsg[0])) throw new Exception("STAMP ERROR: $stampMsg");
            setlocale(LC_TIME,"es_MX.UTF-8","es_MX","esl");
            $pdf->addStamp(strftime("%e %b, %Y"), 'Carlos Alejandro Aguirre Hidalgo');
            $pdf->saveFile($stampname);
            echo " [STAMPED]";
        } catch (Exception $ex) {
            //echo "EXCEPTION: ".$ex->getMessage()."<BR>".$ex->getTraceAsString();
            //echo "<UL><LI>EXCEPTION CLASS: ".get_class($ex)."</LI><LI>CODE: ".$ex->getCode()."</LI><LI>Message: ".$ex->getMessage()."</LI><LI>File: ".$ex->getFile()."</LI><LI>Line: ".$ex->getLine()."</LI></UL>";
            echo " (ERR CODE ".$ex->getCode().")";
        }
    }//else echo "<P>Ya existe el archivo sellado \"{$pdfpath}ST_{$pdfname}\"</P>";
    echo "</P>";
    //echo "<A href=\"{$sitePath}{$pdfpath}ST_{$pdfname}\"><IMG width=\"20\" src=\"{$sitePath}imagenes/icons/pdf200S1.png\"><IMG width=\"20\" src=\"{$sitePath}imagenes/icons/pdf200S2.png\"><IMG width=\"20\" src=\"{$sitePath}imagenes/icons/pdf200S3.png\"></A>";
} else {
    echo "<P>MISSING \"{$pdfpath}{$pdfname}\"</P>";
}
*/
?>
    <div id="my_pdf_viewer">
        <div id="canvas_container">
            <canvas id="pdf_renderer"></canvas>
        </div>
        <div id="navigation_controls">
            <button id="go_previous" type="button" onclick="changePage(-1);">Previous</button>
            <input id="current_page" value="1" type="number" onkeypress="setPage(event);"/>
            <button id="go_next" type="button" onclick="changePage(+1);">Next</button>
        </div>
        <div id="zoom_controls">
            <button id="zoom_in">+</button>
            <button id="zoom_out">-</button>
        </div>
    </div>
    <script type="text/javascript">
        viewPDF('../<?= $pdfpath."ST_".$pdfname ?>');
    </script>
</body>
</html>
