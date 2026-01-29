<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "templates/generalScript.php";
$tmpPath = $_SERVER['DOCUMENT_ROOT']."/docs/tmp/";
$webPath="http://".$_SERVER["HTTP_HOST"]."/docs/tmp/";
?>
<html>
<head>
<title>PDF TEST</title>
<base href="http://invoicecheck.dyndns-web.com:81/invoice/">
<meta charset="utf-8">
<?= getGeneralScript() ?>
<script>
    doShowLogs=true;
    function joinFunc(files) {
        document.body.insertBefore(ecrea({eName:"IMG",src:"imagenes/icons/flying.gif",id:"waiting"}),ebyid('overlay'));
        postService('consultas/ArchivosMul.php', {action:'pdfmerge2',files:files},
            function(t,p,se,ss) {
                if(se==4&&ss==200&&t.length>0) {
                    console.log('DONE!',p,se,ss);
                    ekil('waiting');
                    try {
                        const j=JSON.parse(t);
                        console.log(j);
                        if(j.result==='success') {
                            if (j.webname) {
                                const r1=ebyid("mergedFile");
                                ekfil(r1);
                                r1.appendChild(ecrea({eName:"A",href:j.webname,eChilds:[{eName:"IMG",src:"imagenes/icons/pdf32b.png",title:"Documento Integrado",className:"btn16"}]}));
                                r1.appendChild(ecrea({eName:"IMG",src:"imagenes/prntricon32a.png",className:"btn16",onclick:e=>{printPDF(j.webname);}}));

                            } else overlayMessage(j.message,'ÉXITO');
                        } else {
                            overlayMessage({eName:'P',eText:j.message},j.result.toUpperCase());
                        }
                        if (j.log) console.log("LOG:\n",j.log);
                    } catch(ex) { overlayMessage({eName:'P',eText:ex.message},'EXCEPCIÓN'); }
                } else {
                    let msg="ReadyState="+se+", Status="+ss;
                    if (t && t.length>0) msg+=", TextLength="+t.length;
                    console.log(msg);
                    //if (se==4 && ss>200)
                        console.log("RESULT TEXT:\n",t);
                }
            },
            function(em,pa,ev) {
                console.log('WEBERROR!');
                overlayMessage({eName:'P',eText:pa.xmlHttpPost.readyState+'-'+pa.xmlHttpPost.status+': '+em},'WEBERROR');
            }
        );
    }
    function breakFunc(files) {
        document.body.insertBefore(ecrea({eName:"IMG",src:"imagenes/icons/flying.gif",id:"waiting"}),ebyid('overlay'));
        postService('consultas/ArchivosMul.php', {action:'pdfbreak',files:files},
            function(t,p,se,ss) {
                if(se==4&&ss==200&&t.length>0) {
                    console.log('DONE!',p,se,ss);
                    ekil('waiting');
                    try {
                        const j=JSON.parse(t);
                        console.log(j);
                        if(j.result==='success') {
                            if (j.webnames && j.webnames.length>0) {
                                const r2=ebyid("brokenFileList");
                                ekfil(r2);
                                j.webnames.forEach((nm,idx)=>{
                                    if (idx>0) r2.appendChild(ecrea({eName:"HR"}));
                                    r2.appendChild(ecrea({eName:"A",href:nm,eChilds:[{eName:"IMG",src:"imagenes/icons/pdf32b.png",title:"Pag. "+(idx+1),className:"btn16"}]}));
                                    r2.appendChild(ecrea({eName:"IMG",src:"imagenes/prntricon32a.png",className:"btn16",onclick:e=>{printPDF(nm);}}));
                                });

                            } else overlayMessage(j.message,'ÉXITO');
                        } else {
                            overlayMessage({eName:'P',eText:j.message},j.result.toUpperCase());
                        }
                        if (j.log) console.log("LOG:\n",j.log);
                    } catch(ex) { overlayMessage({eName:'P',eText:ex.message},'EXCEPCIÓN'); }
                } else {
                    let msg="ReadyState="+se+", Status="+ss;
                    if (t && t.length>0) msg+=", TextLength="+t.length;
                    console.log(msg);
                    //if (se==4 && ss>200)
                        console.log("RESULT TEXT:\n",t);
                }
            },
            function(em,pa,ev) {
                console.log('WEBERROR!');
                overlayMessage({eName:'P',eText:pa.xmlHttpPost.readyState+'-'+pa.xmlHttpPost.status+': '+em},'WEBERROR');
            }
        );
    }
</script>
<link href="css/general.php" rel="stylesheet" type="text/css">
</head>
<body class="scrollable centered">
<ul class="lefted">
    <li><label><input type="file" id="files2Merge" class="noscreen pointer" onchange="joinFunc(this.files);" multiple>Unir Archivos</label>
        <div id="mergedFile"></div></li>
    <li><label><input type="file" id="files2Break" class="noscreen pointer" onchange="breakFunc(this.files);" multiple>Separar Archivos</label>
        <div id="brokenFileList" style="width: calc(100% - 40px); max-height: calc(100% - 51px); overflow: auto;"></div></li>
    <li><ol><?php
    function getSolFolio($txt) {
        $isSol=false;
        if (substr($txt, 0, 3)==="SOL") {
            $isSol=true;
            $txt=substr($txt, 3);
        }
        $fecha=substr($txt, 0, 4);
        $gpoCut=substr($txt, 6, 3);
        $num=substr($txt, 9);
        $padNum=str_pad(substr($txt, 9), 3, "0", STR_PAD_LEFT);
        if ($num!==$padNum) $padNum.="!";
        $txt=($isSol?"SOL ":"").$gpoCut.$fecha."-".$padNum;
        return $txt;
    }
    $tmpLen=strlen($tmpPath);
    $log="";
    foreach (glob($tmpPath."onePage*.txt") as $idx => $fileabs) {
       if (file_exists($fileabs)) {
            $handle=fopen($fileabs, "r");
            if ($handle) {
                $isRefLine=false; // $isSomething=false;
                $refStr=""; $newline=""; // $refHex="";
                $lineNum=0;
                while (($line=fgets($handle))!==false) {
                    $line=trim($line);
                    if (!isset($line[0])) continue;
                    $lineNum++;
                    $log.="IDX $idx | LINE $lineNum) $line \n";
                    try {
                        if ($isRefLine!==false) {
                            if ($isRefLine>0) {
                                $log.="RefLine=$isRefLine";
                                $pos=strpos($line, "n de Formato Libre");
                                $log.=" (POS=".($pos===false?"FALSE":$pos."/".($pos+19)." [".strlen($line)."]").")";
                                if ($pos!==false && $isRefLine>1) {
                                    $pos2=strpos($line," ",$pos+19);
                                    $log.=" (POS2=".($pos2===false?"FALSE":$pos2).")";
                                    if ($pos2!==false && $pos2>$pos) {
                                        $isRefLine=0;
                                        $line=substr($line, $pos+19, $pos2-$pos-19);
                                    }
                                }
                                $log.="\n";
                            }
                            if ($isRefLine==0 && isset($newline[0])) {
                                $solFolio=getSolFolio($line);
                                echo "<li>$newline: $solFolio</li>";
                                break;
                            }
                            $isRefLine--;
                            
                        } else {
                            $pos=strpos($line, "mero de Referencia de Transacci");
                            $log.="NewRef (POS=".($pos===false?"FALSE":$pos).")\n";
                            if ($pos!==false) { // Número de Referencia de Transacción
                                $filename=substr($fileabs, $tmpLen);
                                $fnum=substr($filename,-6, -4);
                                $fdat=substr($filename, 7, 2)."/".substr($filename, 9, 2)."/".substr($filename, 11, 2);
                                $newline="$fdat # $fnum) <a href='$webPath$filename' target='onePage'><img src='imagenes/icons/txtDoc32.png' class='btn20' title='$filename'></a>";
                                $fpdfabs=substr($fileabs, 0, -4).".pdf";
                                if (file_exists($fpdfabs)) {
                                    $fpdfname=substr($filename, -4).".pdf";
                                    $newline.="<a href='$webPath$fpdfname' target='onePage'> <img src='imagenes/icons/pdf200.png' class='btn20' title='$fpdfname'></a>";
                                }
                                $isRefLine=18;
                            }
                        }
                    } catch (Error $err) {
                        $log.="ERROR: ".getErrorData($err)."\n";
                    }
                    $log.="<br>\n";
                } // while line
                fclose($handle);
            } // if handle
        } // if file_exists textName
    }
     ?></ol></li>
</ul>
<?php
    include ("templates/overlay.php");
?>
    <div id="mylog" class="hidden">
<?= $log ?>
    </div>
</body>
</html>
