<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (isset($_POST["cmd"])) switch($_POST["cmd"]) {
    case "fix":
        $listQuery="select f.id,f.serie,f.folio,f.codigoProveedor,f.statusn,f.ubicacion,f.nombreInterno,concat(p.rfc,case f.tipoComprobante when 'p' then '_RP' when 'e' then '_NC' when 'i' then '' else '_XX' end,'_',right(f.folio,10)) fixXML,concat(p.rfc,case f.tipoComprobante when 'p' then '_RP' when 'e' then '_NC' when 'i' then '' else '_XX' end,'_',right(concat(f.serie,f.folio),10)) fixXML2,f.nombreInternoPDF,concat(case f.tipoComprobante when 'p' then 'RP_' when 'e' then 'NC_' when 'i' then '' else 'XX_' end,right(f.folio,10),p.rfc) fixPDF,concat(case f.tipoComprobante when 'p' then 'RP_' when 'e' then 'NC_' when 'i' then '' else 'XX_' end,right(concat(f.serie,f.folio),10),p.rfc) fixPDF2,f.tipoComprobante,p.rfc rfcEmisor,g.alias,x.usuarios from facturas f inner join proveedores p on f.codigoProveedor=p.codigo inner join grupo g on f.rfcGrupo=g.rfc inner join (select identif,group_concat(distinct usuario) usuarios from proceso where modulo='Factura' and status not in ('Temporal','Rechazado','Eliminado') group by identif) x on f.id=x.identif where f.statusn is not null and ((f.nombreInterno!=concat(p.rfc,case f.tipoComprobante when 'p' then '_RP' when 'e' then '_NC' when 'i' then '' else '_XX' end,'_',right(f.folio,10)) and f.nombreInterno!=concat(p.rfc,case f.tipoComprobante when 'p' then '_RP' when 'e' then '_NC' when 'i' then '' else '_XX' end,'_',right(concat(f.serie,f.folio),10))) or (f.nombreInternoPDF is not null and f.nombreInternoPDF!=concat(case f.tipoComprobante when 'p' then 'RP_' when 'e' then 'NC_' when 'i' then '' else 'XX_' end,right(f.folio,10),p.rfc) and f.nombreInternoPDF!=concat(case f.tipoComprobante when 'p' then 'RP_' when 'e' then 'NC_' when 'i' then '' else 'XX_' end,right(concat(f.serie,f.folio),10),p.rfc))) order by f.id desc limit 30";
        $result=DBi::query($listQuery);
        $data=[];
        $oldAutoCommit=DBi::isAutocommit(DBi::$conn);
        DBi::autocommit(false);
        if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $basePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
        else if (!empty($_SERVER['DOCUMENT_ROOT'])) $basePath = $_SERVER['DOCUMENT_ROOT'];
        if (isset($result)) while ($row = $result->fetch_assoc()) {
            $rowId=$row["id"];
            $rowSerial=$row["serie"]; // row serie
            $rowFolio=$row["folio"]; // row folio
            $rowType=$row["tipoComprobante"]; // row tipo
            $rowSupCod=$row["codigoProveedor"]; // row codigo proveedor
            $rowSupReg=$row["rfcEmisor"]; // row rfc proveedor
            $rowAlias=$row["alias"]; // row alias
            $rowSttIdx=$row["statusn"]; // row status number
            $rowPath=$row["ubicacion"]; // row path
            $rowXML=$row["nombreInterno"]; // row xml name
            $savedXML=false;
            $savedPDF=false;
            $failedXML=false;
            $failedPDF=false;
            $fileXML=$rowXML.".xml"; // 
            $siteXML=$rowPath.$fileXML;
            $absXML=$basePath.$siteXML;
            $hasXML=isset($rowXML[0]) && file_exists($absXML);
            $rowXMLFix=$row["fixXML"];
            $rowXMLFix2=$row["fixXML2"];
            $sameXML=($rowXML===$rowXMLFix||$rowXML===$rowXMLFix2);
            // ToDo: Cambiar logica para contemplar Fix2 e incluirlo tambien en los pdf.
            if ($sameXML) {
                if ($hasXML) $xmlAttrib=" class=\"disabled\" TITLE=\"STABLE. NOTHING TO CHANGE.\" disabled";
                else {
                    $xmlAttrib=" class=\"bgredvip2 vAlignCenter\" title=\"NOT FOUND $siteXML\"";
                    $failedXML=true;
                }
                $rowXMLFix="";
                $hasXMLFix=false;
            } else {
                $fileXMLFix=$rowXMLFix.".xml";
                $siteXMLFix=$rowPath.$fileXMLFix;
                $absXMLFix=$basePath.$siteXMLFix;
                $hasXMLFix=isset($rowXMLFix[0]) && file_exists($absXMLFix);
                if ($hasXMLFix) {
                    $fixrow=["id"=>$rowId,"nombreInterno"=>$rowXMLFix];
                    global $query;
                    if (!isset($invObj)) {
                        require_once "clases/Facturas.php";
                        $invObj=new Facturas();
                    }
                    if ($invObj->saveRecord($fixrow)===false) {
                        $xmlAttrib=" class=\"bgredvip2 vAlignCenter\" TITLE=\"ERROR AL GUARDAR REGISTRO $rowId con nombreInterno '$rowXMLFix'\"";
                        echo "<!-- SAVE ERROR. $query -->";
                        $failedXML=true;
                        // Se mostrará lista para modificar aunque es posible que falle de nuevo
                    } else {
                        if ($rowSttIdx<128 && ($rowSttIdx|8)>0) { // solo respaldar si ya estaban respaldados y no cancelados
                            if (!isset($ftpObj)) {
                                require_once "clases/FTP.php";
                                $ftpObj=MIFTP::newInstanceGlama();
                            }
                            if ($rowAlias==="CASABLANCA") $rowAlias="LAMINADOS";
                            $esPago=(substr($rowPath,7)==="recibos");
                            $urlAvance = $ftp_servidor;
                            $rutaAvance = $ftp_supportPath.$rowAlias."/".($esPago?"T":"")."PUBLICO/";
                            try {
                                $ftpObj->cargarArchivoAscii($rutaAvance, $fileXMLFix, $absXMLFix);
                                $xmlAttrib=" class=\"disabled bggreenvip2 vAlignCenter\" title=\"CORREGIDO Y RESPALDADO\"";
                                $oldXML=$rowXML; $rowXML=$rowXMLFix; $rowXMLFix=""; $hasXMLFix=false;
                                $savedXML=true;
                            } catch (Exception $e) {
                                $failedXML=true;
                                $xmlAttrib=" class=\"bgyellowvip vAlignCenter\" title=\"$e->getMessage()\"";
                            }
                        } else {
                            $savedXML=true;
                            $xmlAttrib=" class=\"disabled bggreenvip2 vAlignCenter\" title=\"CORREGIDO\"";
                            $oldXML=$rowXML; $rowXML=$rowXMLFix; $rowXMLFix=""; $hasXMLFix=false;
                        }
                    }
                } else {
                    if ($hasXML) {
                        // No hacer nada, al detectar que existe rFixXML se muestra lista para modificar
                        // Se podría revisar el xml para confirmar que si tiene emisor, receptor, serie, folio, uuid...
                        // y si es así permitir el cambio de nombre
                        $xmlAttrib=" class=\"vAlignCenter\"";
                    } else {
                        // No existe ni el guardado ni el sugerido. Dejar ambos pero hay que corregir manualmente (directo en BD)
                        $xmlAttrib=" class=\"bgredvip2 vAlignCenter\" title=\"NOT FOUND {$siteXML} NEITHER {$siteXMLFix}\""; // debe existir al menos el xml del cual se obtuvo la informacion
                        $rowXMLFix="";
                        $hasXMLFix=false;
                        $failedXML=true;
                    }
                }
            }
            $rowPDF=$row["nombreInternoPDF"]??"";
            $filePDF=$rowPDF.".pdf";
            $sitePDF=$rowPath.$filePDF;
            $absPDF=$basePath.$sitePDF;
            $hasPDF=isset($rowPDF[0]) && file_exists($absPDF);
            $rowPDFFix=$row["fixPDF"];
            $samePDF=($rowPDF===$rowPDFFix);
            if ($samePDF) {
                if ($hasPDF) $pdfAttrib=" class=\"disabled\" TITLE=\"STABLE. NOTHING TO CHANGE.\" disabled";
                else $pdfAttrib=" class=\"bgredvip2 vAlignCenter\" title=\"NOT FOUND $sitePDF\"";
                $rowPDFFix="";
                $hasPDFFix=false;
            } else {
                $filePDFFix=$rowPDFFix.".pdf";
                $sitePDFFix=$rowPath.$filePDFFix;
                $absPDFFix=$basePath.$sitePDFFix;
                $hasPDFFix=isset($rowPDFFix[0]) && file_exists($absPDFFix);
                if ($hasPDFFix) {
                    $fixrow=["id"=>$rowId,"nombreInternoPDF"=>$rowPDFFix];
                    global $query;
                    if (!isset($invObj)) {
                        require_once "clases/Facturas.php";
                        $invObj=new Facturas();
                    }
                    $result2=$invObj->saveRecord($fixrow);

                    if ($result2===false) {
                        doclog("SAVE PDF FIX $rowId - $rowPDFFix = FALSE");
                        $pdfAttrib=" class=\"bgredvip2 vAlignCenter\" TITLE=\"ERROR AL GUARDAR REGISTRO $rowId con nombreInternoPDF '$rowPDFFix'\"";
                        echo "<!-- SAVE ERROR. $query -->";
                        $failedPDF=true;
                    } else {
                        if ($result2===true) doclog("SAVE PDF FIX $rowId - $rowPDFFix = TRUE");
                        else doclog("SAVE PDF FIX $rowId - $rowPDFFix = ".json_encode($result2));
                        if ($rowSttIdx<128 && ($rowSttIdx|8)>0) {
                            if (!isset($ftpObj)) {
                                require_once "clases/FTP.php";
                                $ftpObj=MIFTP::newInstanceGlama();
                            }
                            if ($rowAlias==="CASABLANCA") $rowAlias="LAMINADOS";
                            $esPago=(substr($rowPath,7)==="recibos");
                            $urlAvance=$ftp_servidor;
                            $rutaAvance=$ftp_supportPath.$rowAlias."/".($esPago?"T":"")."PUBLICO/";
                            try {
                                $ftpObj->cargarArchivoBinario($rutaAvance, $filePDFFix, $absPDFFix);
                                $pdfAttrib=" class=\"disabled bggreenvip2 vAlignCenter\" title=\"CORREGIDO Y RESPALDADO\"";
                                $oldPDF=$rowPDF; $rowPDF=$rowPDFFix; $rowPDFFix=""; $hasPDFFix=false;
                                $savedPDF=true;
                            } catch (Exception $e) {
                                $failedPDF=true;
                                $pdfAttrib=" class=\"bgyellowvip vAlignCenter\" title=\"$e->getMessage()\"";
                            }
                        } else {
                            $savedPDF=true;
                            $pdfAttrib=" class=\"disabled bggreenvip2 vAlignCenter\" title=\"CORREGIDO\"";
                            $oldPDF=$rowPDF; $rowPDF=$rowPDFFix; $rowPDFFix=""; $hasPDFFix=false;
                        }
                    }
                } else {
                    if ($hasPDF) {
                        // Nada
                        $pdfAttrib=" class=\"vAlignCenter\"";
                    } else {
                        $pdfAttrib=" class=\"bgredvip2 vAlignCenter\" title=\"NOT FOUND {$sitePDF} NEITHER {$sitePDFFix}\""; // show Trash can 
                        $rowPDFFix=""; $hasPDFFix=false;
                    }
                }
            }
            if (($savedXML||$savedPDF)&&!$failedXML&&!$failedPDF) DBi::commit();
            $rUsers=$row["usuarios"];
            $data[$idx]=["id"=>$rowId,"serie"=>$rowSerial,"folio"=>$rowFolio,"tipo"=>$rowType,"codPro"=>$rowSupCod,"sttn"=>$rowSttIdx,"ruta"=>$rowPath,"xml"=>$hasXMLFix?[$rowXML,$rowXMLFix]:$rowXML,"xmla"=>$xmlAttrib,"xmlSite"=>$hasXMLFix?[$siteXML,$siteXMLFix]:$siteXML,"pdf"=>$hasPDFFix?[$rowPDF,$rowPDFFix]:$rowPDF,"pdfa"=>$pdfAttrib];
            $idx++;
        }
        echo json_encode($result);
        die;
}
?>
<html>
    <head>
        <title>CFDI FIX</title>
        <base href="http://invoicecheck.dyndns-web.com:81/invoice/">
        <meta charset="utf-8">
        <script src="scripts/general.js?ver=1.0.0"></script>
        <link href="css/general.php" rel="stylesheet" type="text/css">
        <script>
            function showDeleteIcon(evt) {
                const tgt=evt.target;
                if (tgt.nextElementSibling && tgt.nextElementSibling.tagName==="IMG") clfix(tgt.nextElementSibling,"hidden");
                else {
                    while (tgt.nextElementSibling) ekil(tgt.nextElementSibling);
                    tgt.parentNode.appendChild(ecrea({eName:"IMG",src:"imagenes/icons/trash32.png",className:"btn16 vAlignCenter pointer",onclick:function(event){const tgt=event.target;tgt.previousElementSibling.value="";clrem(tgt.previousElementSibling,"bgredvip2");cladd(tgt.previousElementSibling,"bgyellowvip");tgt.previousElementSibling.onclick=null;ekil(tgt);}}));
                }
            }
        </script>
    </head>
    <body style="overflow: auto;">
        <H1>Mantenimiento de Facturas</H1>
        <form name="forma1" method="post">
            <table><thead><tr><td>SERIE</td><td>FOLIO</td><td>TIPO</td><td>PRV</td><td>STATUS</td><td>UBICACION</td><td>NOMBRE XML</td><td>NOMBRE PDF</td><td>RFCEMISOR</td><td>USUARIOS</td></tr></thead><tbody>
<?php
?>
              <tr><td><input type="hidden" value="<?=$rowId?>" name="data[<?=$idx?>][id]">
                      <input type="text" value="<?=$rowSerial?>" name="data[<?=$idx?>][serie]" style="width:90px;" readonly></td>
                  <td><input type="text" value="<?=$rowFolio?>" name="data[<?=$idx?>][folio]" style="width:80px;" readonly></td>
                  <td><input type="text" value="<?=$rowType?>" name="data[<?=$idx?>][tipoComprobante]" style="width:40px;" readonly></td>
                  <td><input type="text" value="<?=$rowSupCod?>" name="data[<?=$idx?>][codigoProveedor]" style="width:50px;" readonly></td>
                  <td><input type="text" value="<?=$rowSttIdx?>" name="data[<?=$idx?>][statusn]" style="width:80px;" readonly></td>
                  <td><input type="text" value="<?=$rowPath?>" name="data[<?=$idx?>][ubicacion]" readonly></td>
                  <td class="nowrap"><?php if (!$hasXMLFix) { ?>
                      <input type="text" value="<?=$rowXML?>" name="data[<?=$idx?>][nombreInterno]"<?= $xmlAttrib ?> readonly><?php } else {
                        ?>
                      <input type="hidden" value="<?=$rowXML?>" name="oldt[<?=$idx?>][nombreInterno]" readonly>
                      <select name="data[<?=$idx?>][nombreInterno]"><option value="<?=$rowXML?>"><?=$rowXML?></option>
                        <option value="<?=$rowXMLFix?>"><?=$rowXMLFix?></option></select>
                        <?php } if ($hasXML) { ?>
                      <a href="<?= $siteXML ?>" target="xmlfile"><img src="imagenes/icons/xml32.png" class="btn16"></a><?php } if ($hasXMLFix) { ?>
                      <a href="<?= $siteXMLFix ?>" target="xmlfile"><img src="imagenes/icons/xml32.png" class="btn16"></a><?php } ?></td>
                  <td class="nowrap"><?php if (!$hasPDF || !$hasPDFFix) { ?>
                      <input type="text" value="<?=$rowPDF?>" name="data[<?=$idx?>][nombreInternoPDF]"<?= $pdfAttrib ?> readonly><?php } else {
                        ?>
                      <input type="hidden" value="<?=$rowPDF?>" name="oldt[<?=$idx?>][nombreInternoPDF]" readonly>
                      <select name="data[<?=$idx?>][nombreInternoPDF]"><option value="<?=$rowPDF?>"><?=$rowPDF?></option>
                        <option value="<?=$rowPDFFix?>"><?=$rowPDFFix?></option></select>
                        <?php } if ($hasPDF) { ?>
                      <a href="<?= $sitePDF ?>" target="pdffile"><img src="imagenes/icons/pdf32a.png" class="btn16"></a><?php } if ($hasPDFFix) { ?>
                      <a href="<?= $sitePDFFix ?>" target="pdffile"><img src="imagenes/icons/pdf32a.png" class="btn16"></a><?php } ?></td>
                  <td><input type="text" value="<?=$rowSupReg?>" name="data[<?=$idx?>][rfcEmisor]" readonly></td>
                  <td><input type="text" value="<?=$rUsers?>" name="data[<?=$idx?>][usuarios]" readonly></td></tr>
<?php
 ?>
            </tbody></table><input name="SAVE" type="submit" value="SAVE">
        </form>
    </body>
</html>
<?php
DBi::rollback();
DBi::autocommit($oldAutoCommit);
require_once "configuracion/finalizacion.php";
