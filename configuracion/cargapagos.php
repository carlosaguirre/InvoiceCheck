<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting A to /".$_project_name."/");
}
$esCargaEgreso=validaPerfil("Carga Egresos");
if(!$_esAdministrador&&!$_esSistemas&&!$esCargaEgreso) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    header("Location: /".$_project_name."/");
    die("Redirecting B to /".$_project_name."/");
}
clog2ini("configuracion.cargapagos");
clog1seq(1);

set_time_limit(1200);
require_once "clases/Config.php";
$bsPyPth=(Config::get("project","sharePath")??"..\\");
$resultView="";
$nuevos=0; $procesables=0;
plog("ORIGINAL FILES: ".json_encode($_FILES),true);
if (isset($_FILES["pagos"])) {
    $pagos=getFixedFileArray($_FILES["pagos"]);
    //$numpagos=isset($pagos["name"][0])?count($pagos["name"]):0;
    if (isset($pagos[0])) { // $numpagos>0
        $pls=(isset($pagos[1]))?"S":"";
        $numpagos=count($pagos);
        plog("INICIA CARGA DE $numpagos ARCHIVO{$pls}:",true);
        require_once "clases/Pagos.php";
        $pyObj = new Pagos();
        $pyObj->rows_per_page=0;
        require_once "clases/Facturas.php";
        $invObj = new Facturas();
        $invObj->rows_per_page=1;
        $invObj->clearOrder();
        $invObj->addOrder("id","desc");
        for($i=0; isset($pagos[$i]); $i++) {
            $num=$i+1;
            $pname=$pagos[$i]["name"]; $psize=$pagos[$i]["size"]; $ptmpn=$pagos[$i]["tmp_name"];
            $perrn=$pagos[$i]["error"]; $ptype=$pagos[$i]["type"];
            plog("ARCHIVO $num/$numpagos: $pname / $ptype / $perrn");
            if (!isValidFileData($pname,$psize,$ptmpn,$perrn,$ptype,$errmsg)) {
                $resultView.="<H3 class=\"errorLabel mbpi\">{$errmsg}</H3>";
                plog($errmsg);
            } else {
                $lines=file($ptmpn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $newName=getNewName($lines[0],$lines[4],$nrfc);
                if (!isset($newName[0])) $newName=$pname;
                if (!isset($nrfc[0])) $nrfc=null;
                /*if ($pyObj->exists("archivo='$newName'")) {
                    plog("ERROR: Archivo de pago $newName previamente agregado.");
                } else */
                if (move_uploaded_file($ptmpn, "{$bsPyPth}PAGOS\\$newName")) {
                    $nuevos++;
                    $numLines=count($lines);
                    $txtLines="con $numLines línea".($numLines==1?"":"s").".";
                    if ($newName===$pname) plog("Recibido y guardado $txtLines");
                    else plog("Recibido y guardado como {$newName} $txtLines");
                    $rmsg="Archivo: '$newName', Lineas={$numLines}";
                    DBi::autocommit(FALSE);
                    if (extractData($newName,$nrfc,$lines,$result)) {
                        DBi::commit(); /*rollback(); / */
                        foreach ($result as $key => $arrval) {
                            $num=count($arrval);
                            $pls=($num==1?"":"s");
                            $plon=($num==1?"&oacute;":"aron");
                            $rmsg.=", ".ucfirst($key)."=".$num;
                            if ($key==="aceptado") {
                                $total=$num+count($result["aviso"]??[])+count($result["invalido"]??[]);
                                $plts=($total==1?"":"s");
                                $resultView.="<H3>Se acept$plon $num de $total Egreso{$plts} en $pname.</H3>";
                            }
                        }
                        if (isset($result["aviso"][0])) {
                            $num=count($result["aviso"]);
                            $pls=($num==1?"":"s");
                            $pln=($num==1?"":"n");
                            $plon=($num==1?"&oacute;":"aron");
                            $resultView.="<H3 class=\"errorLabel mbpi\">Se ignor{$plon} $num Egreso{$pls} porque ya esta{$pln} registrado{$pls}.</H3>";
                        }
                        if (isset($result["invalido"][0])) {
                            $num=count($result["invalido"]);
                            $pls=($num==1?"":"s");
                            $resultView.="<H3 class=\"errorLabel mbpi\">Se rechazaron $num registro{$pls} como se indica a continuaci&oacute;n:</H3><TABLE border=\"1\" class=\"centered\"><THEAD class=\"centered padh1_3\"><TR><TH>PROV</TH><TH>FOLIO</TH><TH>MOTIVO</TH></TR></THEAD><TBODY class=\"centered padd1_3\">";
                            $rmsg.=" [";
                            foreach($result["invalido"] as $idx=>$errData) {
                                $resultView.="<TR><TD>$errData[proveedor]</TD><TD>$errData[folio]</TD><TD class=\"errorLabel mbpi\">$errData[mensaje]</TD></TR>";
                                if ($idx>0) $rmsg.=",";
                                $rmsg.="$errData[proveedor] $errData[folio] ".($errData["corto"]??$errData["mensaje"]);
                            }
                            $resultView.="</TBODY></TABLE>";
                            $rmsg.="]";
                        }
                        plog("Extraccion de datos satisfactoria. $rmsg");
                    } else {
                        DBi::rollback();
                        foreach ($result as $key => $arrval) {
                            $rmsg.=", ".ucfirst($key)."=".count($arrval);
                        }
                        if (isset($result["error"][0])) foreach ($result["error"] as $idx => $errData) {
                            $resultView.="<H3 class=\"errorLabel mbpi\">$errData[mensaje]</H3>";
                            $rmsg.=",$errData[mensaje]";
                        }
                        if (!isset($result["aceptado"][0])) {
                            $num=isset($result["aviso"][0])?count($result["aviso"]):0;
                            $pls=($num==1?"":"s");
                            $pln=($num==1?"":"n");
                            $plon=($num==1?"o":"aron");
                            if ($num>0) $resultView.="<H3 class=\"errorLabel mbpi\">Se ignor{$plon} $num Egreso{$pls} porque ya esta{$pln} registrado{$pls}.</H3>";
                            if (isset($result["invalido"][0])) {
                                $num=count($result["invalido"]);
                                $pls=($num==1?"":"s");
                                $resultView.="<H3 class=\"errorLabel mbpi\">Error en $num registro{$pls} en $pname:</H3><TABLE border=\"1\" class=\"centered\"><THEAD class=\"centered padh1_3\"><TR><TH>PROV</TH><TH>FOLIO</TH><TH>MOTIVO</TH></TR></THEAD><TBODY class=\"centered padd1_3\">";
                                $rmsg.="[";
                                foreach($result["invalido"] as $idx => $errData) {
                                    $resultView.="<TR><TD>$errData[proveedor]</TD><TD>$errData[folio]</TD><TD class=\"errorLabel mbpi\">$errData[mensaje]</TD></TR>";
                                    if ($idx>0) $rmsg.=",";
                                    $rmsg.="$errData[proveedor] $errData[folio] ".($errData["corto"]??$errData["mensaje"]);
                                }
                                $resultView.="</TBODY></TABLE>";
                                $rmsg.="]";
                            }
                        }
                        plog("Extraccion de datos fallida. $rmsg");
                    }
                    DBi::autocommit(TRUE);
                } else {
                    $resultView.="<H3 class=\"errorLabel mbpi\">Error al cargar archivo $newName.</H3>";
                    plog("ERROR: FALLÓ move_uploaded_file");
                }
            }
        }
    } else {
        plog("No hay ARCHIVOS");
    }
} else {
    //$resultView.="<H3 class=\"errorLabel mbpi\">No se recibio archivo de egresos.</H3>";
    plog("No hay PAGOS");
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //
function plog($text,$hasPrefix=false,$hasNewLine=true) {
  global $bsPyPth;
  $dt=new DateTime();
  $dayFmt=$dt->format("ymd");
  $timeFmt = (new DateTime())->format("H:i:s")." ".getUser()->nombre;
  $logname="{$bsPyPth}PAGOS\\pago{$dayFmt}.log";
  if ($hasPrefix&&file_exists($logname)&&filesize($logname)>0) $prefix="-----".PHP_EOL;
  else $prefix="";
  if ($hasNewLine) {
      if (!isset($text[0])) {
        if (!isset($prefix[0])) return true;
        return file_put_contents($logname,"{$prefix}[$timeFmt] -".PHP_EOL, FILE_APPEND | LOCK_EX);
      } else return file_put_contents($logname,"{$prefix}[$timeFmt] $text".PHP_EOL, FILE_APPEND | LOCK_EX);
  } else return file_put_contents($logname, $prefix.$text, FILE_APPEND | LOCK_EX);
}
function getCurrLog() {
  $dt=new DateTime();
  $dayFmt=$dt->format("ymd");
  //$logname="{$bsPyPth}PAGOS\\pago{$dayFmt}.log";
  return $dayFmt;
}
function isValidFileData($pname,$psize,$ptmpn,$perrn,$ptype,&$errmsg) {
    $errmsg="";
    if ($psize==0) $errmsg="ERROR: Tamaño de archivo $pname es cero.";
    else if (!isset($ptmpn[0])) $errmsg="ERROR: Carga de archivo $pname no identificada.";
    else if ($perrn!==UPLOAD_ERR_OK) {
      switch($perrn) {
        case UPLOAD_ERR_INI_SIZE: $errmsg="ERROR: El archivo $pname excede el tamaño máximo permitido por el servidor."; break;
        case UPLOAD_ERR_FORM_SIZE: $errmsg="ERROR: El archivo $pname excede el tamaño máximo permitido por el navegador."; break;
        case UPLOAD_ERR_PARTIAL: $errmsg="ERROR: La carga del archivo $pname se interrumpió."; break;
        case UPLOAD_ERR_NO_FILE: $errmsg="ERROR: No se encontró el archivo $pname."; break;
        case UPLOAD_ERR_NO_TMP_DIR: $errmsg="ERROR: No está definida la carpeta de descarga de archivos."; break;
        case UPLOAD_ERR_CANT_WRITE: $errmsg="ERROR: No está autorizada la descarga de archivos."; break;
        case UPLOAD_ERR_EXTENSION: $errmsg="ERROR: La descarga de archivos está bloqueada por una extensión."; break;
        default: $errmsg="ERROR: Falló la descarga del archivo $pname.";
      }
    } else if ($ptype!=="text/plain") $errmsg="ERROR: El archivo $pname no es de tipo texto.";
    return !isset($errmsg[0]);
}
function getNewName($line1,$line2,&$nrfc) {
    global $meses,$gpoObj;
    $dateLineResult=preg_match('/^(\d+) de (\w+) de (\d+)$/',trim($line1),$matches);
    if($dateLineResult===1) {
        $ndia=$matches[1];
        if(!isset($ndia[1])) $ndia="0$ndia";
        $nmes=strtolower(substr($matches[2],0,3));
        if(!isset($meses)) $meses=["ene"=>"01","feb"=>"02","mar"=>"03","abr"=>"04","may"=>"05","jun"=>"06","jul"=>"07","ago"=>"08","sep"=>"09","oct"=>"10","nov"=>"11","dic"=>"12"];
        if (isset($meses[$nmes])) $nmes=$meses[$nmes];
        $nanio=substr($matches[3],2,2);
        $nrfc=trim($line2);
        if(!isset($gpoObj)) {
          require_once "clases/Grupo.php";
          $gpoObj=new Grupo();
        }
        $nalias=$gpoObj->getValue("rfc",$nrfc,"alias");
        if(!isset($nalias[0])) $nalias=$nrfc."_";
        $nname=$nalias."_".$nanio.$nmes.$ndia.".txt";
        return $nname;
    }
    return null;
}
// Lectura y procesamiento de facturas pagadas
// Modificar status en facturas
// Evaluar situaciones excepcionales
// * Si ya está pagada la factura
// * Si se está pagando en parcialidades
// * Si no se acompleta el total de la factura
// * Si se excede al total de la factura
// * Si el pago actual ya está considerado
// Generar texto a desplegar
//  - Datos de la factura
//  - Datos no procesados y el motivo:
//     * Factura no encontrada
//     * No es Egreso
//     * No es Pago de Egreso
//     * La factura ya tenía status de pagado
function extractData($filename,$nrfc,$lines,&$result) {
    global $query, $invObj,$pyObj,$prvObj,$solObj;
    $numLines=count($lines);
    $timeLimit=7*$numLines;
    $timeLimitStr="";
    if ($timeLimit<60) $timeLimitStr="$timeLimit segundos";
    else {
        $numMins=$timeLimit/60;
        $numSecs=$timeLimit%60;
        if ($numMins<60) $timeLimitStr="$numMins mins, $numSecs secs";
        else {
            $hrs=$numMins/60;
            $numMins=$numMins%60;
            $timeLimitStr="{$hrs}:{$numMins}:{$numSecs}";
        }
    }
    plog("Obteniendo informacion de $numLines lineas".($numLines==1?"":"s").", tiempo limite: $timeLimitStr");
    set_time_limit($timeLimit);
    $inDataSection=FALSE;
    $level=0;
    $result=[];
    $arrIdData=[];
    $arrPyData=[];
    $arrFcData=[];
    $colNames=["Proveedor","Fact/Rem","Fecha","Cantidad","I V A","T O T A L","Tipo","Referencia"];
    $colIdx=[];
    $colRng=[[0,10],[0,0],[0,8],[-8,8],[-11,5],[-7,9],[0,0],[0,0]];
    $currProv="";
    $currFolio="";
    $currFactId="";
    $currStatusn=0;
    $lastErrFolio=null;
    $lastErrFactId=null;
    $dbPyCols=["archivo","codigoProveedor","idFactura","fechaPago","cantidad","iva","total","tipo","referencia"];
    $isLogLineStarted=false;
    foreach ($lines as $lineIdx=>$oneline) {
        if ($lineIdx>0 && ($lineIdx%10)==0) {
            $isLogLineStarted=true;
        }
        $trimline=trim($oneline);
        if (!isset($trimline[0])) {
            //if (!isset($result["vacio"])) $result["vacio"]=[];
            //$result["vacio"][]=["idx"=>$lineIdx];
            //plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") VACIO");
            continue;
        }
        if (preg_match('/^(\d+) de (\w+) de (\d+)$/',$trimline,$matches)===1 || $trimline==="El Total de ingresos en el periodo comprendido entre:") {
            $inDataSection=FALSE;
            //if (!isset($result["info"])) $result["info"]=[];
            //$result["info"][]=["idx"=>$lineIdx,"mensaje"=>"Inicia Bloque Sin Datos"];
            //plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") SIN DATOS: $trimline");
            continue;
        }
        if (!$inDataSection&&substr($trimline,0,strlen($colNames[0]))===$colNames[0]) {
            $inDataSection=TRUE;
            for($i=0;isset($colNames[$i]);$i++) {
                $colIdx[$i]=strpos($oneline,$colNames[$i]);
            }
            if (!isset($result["info"])) $result["info"]=[];
            $result["info"][]=["idx"=>$lineIdx,"mensaje"=>"Encabezado de Datos"];
            //plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") INI DATOS");
            continue;
        }
        if (!$inDataSection) {
            //if (!isset($result["info"])) $result["info"]=[];
            //$result["info"][]=["idx"=>$lineIdx];
            //plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") NO INFO: $trimline");
            continue;
        }
        if (substr($trimline,0,2)==="--") {
            //if (!isset($result["info"])) $result["info"]=[];
            //$result["info"][]=["idx"=>$lineIdx,"mensaje"=>"Separador de guiones"];
            //plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") - - - - - - - - - -");
            continue;
        }
        $lineLength=strlen($oneline);
        // arch,codPrv,idFct,fecha,cant,iva,tot,tipo,ref
        $colPyData=[$filename,$currProv,$currFactId,"","0.0","0.0","0.0","",""];
        $ciclo=""; $totalPago=0;
        $trace="Prv=$currProv|fId=$currFactId|fFol=$currFolio";
        for($i=0;isset($colNames[$i]);$i++) {
            $trace.="|i=$i";
            if (isset($colIdx[$i]) && $colIdx[$i]!==FALSE) {
                $idx=$colIdx[$i];
                $rng=$colRng[$i];
                $len=$rng[1]-$rng[0];
                if($len==0) {
                    if (!isset($colIdx[$i+1])) $len=$lineLength-$idx;
                    else if ($colIdx[$i+1]===FALSE) $len=-1;
                    else $len=$colIdx[$i+1]-$idx;
                }
                if ($len>0) {
                    $beginIdx=$idx+$rng[0];
                    $celltext=trim(substr($oneline, $beginIdx, $len));
                    $trace.="|idx=".($idx+1)."|bix=".($beginIdx+1)."|len=$len";
                } else $celltext="";
            } else $celltext="";
            $trace.="|ctx1=$celltext";
            if (isset($celltext[0])) {
                $oldQryLst=$queryList??null;
                $queryList=[];
                if ($i==0) {
                    $currProv=$celltext;
                    $trace.="|Prv=$currProv";
                } else if ($i==1) {
                    $lastCurrFolio=$currFolio;
                    $currFolio=$celltext;
                    if (substr($currFolio,0,2)==="F-") $currFolio=substr($currFolio,2);
                    $celltext="";
                    $trace.="|fFol=$currFolio|ctx2=$celltext";
                    if ($lastCurrFolio!==$currFolio) {
                        $currFactId="";
                        $trace.="|fId=$currFactId";
                    }
                } else if ($i==2) {
                    $fechaPago = DateTime::createFromFormat('y/m/d', $celltext)->format('Y-m-d');
                    $celltext = $fechaPago;
                    $trace.="|ctx3=$celltext";
                    if (!isset($currFactId[0])) {
                        $gpoChk=(isset($nrfc[0])?" and rfcGrupo='$nrfc'":"");
                        $commonWhere="codigoProveedor='$currProv'{$gpoChk} and tipoComprobante='i' and year(fechaFactura)<=year('$celltext')"; //  and fechaPago is null
                        $fctData=$invObj->getData("folio='$currFolio' and $commonWhere",0,"id,ciclo,totalPago,statusn");
                        $queryList[]=$query;
                        if (isset($fctData[1])) {
                            $result["aviso"][]=["idx"=>$lineIdx,"mensaje"=>"Hay datos de factura ambiguos por folio repetido","corto"=>"Facturas Ambiguas","proveedor"=>$currProv,"folio"=>$currFolio,"rrfc"=>$nrfc,"fechaPago"=>$fechaPago,"queries"=>$queryList,"data"=>$fctData];
                            plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") DATOS AMBIGUOS. Folio: $currFolio, Prov: $currProv, rrfc: $nrfc, fechaPago: $fechaPago, data: ".json_encode($fctData));
                            //continue 2;
                        }
                        if (isset($currFolio[10])) {
                            $currFolio=substr($currFolio,-10);
                            $trace.="|fFol=$currFolio";
                        }
                        if (isset($currFolio[9])) {
                            if (!isset($fctData[0])) {
                                $fctData=$invObj->getData("right(folio,10)='$currFolio' and $commonWhere",0,"id,ciclo,totalPago,statusn");
                                $queryList[]=$query;
                            }
                            if (!isset($fctData[0])) {
                                $fctData=$invObj->getData("right(uuid,10)='$currFolio' and $commonWhere",0,"id,ciclo,totalPago,statusn");
                                $queryList[]=$query;
                            }
                        }
                        // validar si currFolio empieza con letra. tmpFolio=quitar lo que no sea numero o letra de currFolio
                        if (!isset($fctData[0])) {
                            $fctData=$invObj->getData("concat(serie,folio)='$currFolio' and $commonWhere",0,"id,ciclo,totalPago,statusn,folio");
                            $queryList[]=$query;
                        }
                        if (isset($fctData[0])) {
                            $currFactId="".$fctData[0]["id"];
                            $trace.=", fId=$currFactId";
                            $ciclo=$fctData[0]["ciclo"];
                            if (isset($fctData[0]["totalPago"][0]))
                                $totalPago=$fctData[0]["totalPago"];
                            if (isset($fctData[0]["statusn"][0]))
                                $currStatusn=$fctData[0]["statusn"];
                            if (isset($fctData[0]["folio"][0])) {
                                $currFolio=$fctData[0]["folio"];
                                $trace.="|fFol=$currFolio";
                            }
                            $colPyData[$i]=$currFactId; // index 2, guardar id de factura
                            if (empty($currStatusn)||(+$currStatusn)<0) {
                                $lastErrFolio=$currFolio;
                                $lastErrFactId=$currFactId;
                                if (!isset($result["invalido"])) $result["invalido"]=[];
                                if ($currStatusn===0||$currStatusn==="0") {
                                    $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura no ha sido Aceptada (Status Pendiente)","corto"=>"Status Pendiente","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId];
                                    plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Factura Pendiente. Folio: $currFolio, Prov: $currProv, id: $currFactId, statusn: $currStatusn");
                                } else {
                                    $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura no esta registrada","corto"=>"Status Temporal","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId];
                                    plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Factura Temporal. Folio: $currFolio, Prov: $currProv, id: $currFactId");
                                }
                                continue 2;
                            } else if ($currStatusn>=128) {
                                $lastErrFolio=$currFolio;
                                $lastErrFactId=$currFactId;
                                if (!isset($result["invalido"])) $result["invalido"]=[];
                                $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura esta cancelada","corto"=>"Status Cancelado","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId];
                                plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Factura Cancelada. Folio: $currFolio, Prov: $currProv, id: $currFactId, statusn: $currStatusn");
                                continue 2;
                            }
                        } else {
                            $lastErrFolio=$currFolio;
                            if (!isset($result["invalido"])) $result["invalido"]=[];
                            if (!isset($prvObj)) {
                                require_once "clases/Proveedores.php";
                                $prvObj=new Proveedores();
                            }
                            if ($prvObj->exists("codigo='$currProv'")) {
                                $lastQuery=$query;
                                $queryList[]=$query;
                                $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura no esta registrada","corto"=>"Factura no registrada","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>0];
                                plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Sin Resultados. Folio: $currFolio, Prov: $currProv, id: $currFactId, query: $lastQuery");
                            } else {
                                $lastQuery=$query;
                                $queryList[]=$query;
                                $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"El proveedor no esta registrado","corto"=>"Proveedor no registrado","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>0];
                                plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Proveedor no registrado. Folio: $currFolio, Prov: $currProv, id: $currFactId, query: $lastQuery");
                            }
                            continue 2;
                        }
                    } else $trace.="|keep fId";
                } else if ($i>2&&$i<6) {
                    $celltext=str_replace(["'",","],"",$celltext);
                    $trace.="|ctx4=$celltext";
                }
            } else if ($i==0) { 
                $celltext=$currProv;
                $trace.="|ctx5=$celltext";
            } else if ($i==1) {
                if (isset($currFactId[0])) {
                    if ($currFactId===$lastErrFactId) {
                        if (!isset($result["invalido"])) $result["invalido"]=[];
                        $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"Misma factura, mismo error","corto"=>"Ver anterior","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId];
                        plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Misma factura, mismo error. Folio: $currFolio, Prov: $currProv, id: $currFactId");
                        continue 2;
                    } else {
                        $celltext=$currFactId;
                        $trace.="|ctx6=$celltext";
                    }
                } else if ($currFolio===$lastErrFolio) {
                    if (!isset($result["invalido"])) $result["invalido"]=[];
                    $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"Mismo folio sin registro, mismo error","corto"=>"Ver anterior","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>0];
                    plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Mismo folio sin registro, mismo error. Folio: $currFolio, Prov: $currProv, id: $currFactId");
                    continue 2;
                } else {
                    if (!isset($result["invalido"])) $result["invalido"]=[];
                    $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"Datos Incompletos","corto"=>"Incompleto","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>0];
                    plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Datos Incompletos. Folio: $currFolio, Prov: $currProv, id: $currFactId");
                    continue 2;
                }
            }// else if ($i==3||$i==4) $celltext="0.0";
            else if ($i==5) {
                $celltext="".((+$colPyData[4])+(+$colPyData[5]));
                $trace.="|ctx7=$celltext";
            } else {
                $celltext="";
                $trace.="|ctx8=$celltext";
            }
            if (isset($celltext[0])) $colPyData[$i+1]=$celltext;
        }
        if (strtoupper($colPyData[7])!=="PAGO") {
            if (!isset($result["invalido"])) $result["invalido"]=[];
            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"No Es Pago: ".$colPyData[7],"corto"=>"Tipo:".$colPyData[7],"proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$colPyData[2]];
            plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") No es Pago. Folio: $currFolio, Data: ".json_encode($colPyData));
            continue;
        }
        if (strtoupper(substr($colPyData[8],0,6))!=="EGRESO") {
            if (!isset($result["invalido"])) $result["invalido"]=[];
            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"No Es Egreso: ".$colPyData[8],"corto"=>"Ref:".$colPyData[8],"proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$colPyData[2]];
            plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") No es Egreso. Folio: $currFolio, Data: ".json_encode($colPyData));
            continue;
        }
        //plog($trace);
        $pyExistWhere = "codigoProveedor='{$currProv}' AND idFactura={$colPyData[2]} AND fechaPago='{$colPyData[3]}'".
                        " AND cantidad=".($colPyData[4]??0).
                        " AND iva=".($colPyData[5]??0).
                        " AND total=".($colPyData[6]??0).
                        " AND tipo='".($colPyData[7]??"").
                        "' AND referencia='".($colPyData[8]??"").
                        "' AND valido=1";
        $pyFileList=implode(", ",array_column($pyObj->getData($pyExistWhere,0,"archivo"), "archivo"));
        if (isset($pyFileList[0])) {
            if (!isset($result["aviso"])) $result["aviso"]=[];
            $result["aviso"][]=["idx"=>$lineIdx,"mensaje"=>"Egreso Repetido. Archivo original: ".$pyFileList,"proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$colPyData[2]];
            plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Egreso Repetido. Folio: $currFolio, Data: ".json_encode($colPyData));
            continue;
        }
        // colPyData: arch,codPrv,idFct,fecha,cant,iva,tot,tipo,ref
        // colFcData: idFct,fechaPago,totPago,refPago,status,statusn
        // ToDO: Total Pago no refleja la suma total, sino la suma de las cargas de archivo que coincidan con esta factura. Si se carga el mismo archivo varias veces se suma la misma cantidad. Considerando que en la tabla PAGOS si se registra solo una vez por archivo, tipo y referencia de pago, se asume que cada cantidad total es independiente. Es lo que hay que sumar.
        // ToDO: StatusN y Status. Una vez que el totalPagado es mas preciso, comparar contra el total de la factura y solo cambiar el status cuando coincidan. (Considerar cuando hay variaciones por descuentos o similares. También cuando hay otras situaciones por las que se pueda repetir el mismo pago, en caso de llegar con diferente tipo o referencia)
        $currStatusn=(+$currStatusn)|Facturas::STATUS_PAGADO;
        $arrIdData[]=$colPyData[2];
        $arrFcData[]=["id"=>$colPyData[2],"fechaPago"=>$colPyData[3],"totalPago"=>$colPyData[6],"referenciaPago"=>$colPyData[8],"status"=>"Pagado","statusn"=>$currStatusn];
        $arrPyData[]=$colPyData;
        if (!isset($result["aceptado"])) $result["aceptado"]=[];
        $result["aceptado"][]=["idx"=>$lineIdx,"mensaje"=>"Status PAGADO ($currStatusn)","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId,"where"=>$pyExistWhere];
        plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") EXITO PAGADO! Folio:$currFolio, statusn:$currStatusn, Data: ".json_encode($colPyData));
    }
    if (!isset($arrPyData[0])) {
        if (!isset($result["error"])) $result["error"]=[];
        $result["error"][]=["mensaje"=>"Ningun Egreso Registrado"];
        //if ($isLogLineStarted) plog(" N.".PHP_EOL,false,false);
        plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Ningun Egreso Registrado.");
        return false;
    }
    $pyData=$pyObj->getData(false,1,"max(id) maxId");
    $pyMaxId=$pyData[0]["maxId"];
    if (!$pyObj->insertMultipleRecords($dbPyCols, $arrPyData,"ON DUPLICATE KEY UPDATE fechaPago=VALUES(fechaPago), cantidad=VALUES(cantidad), iva=VALUES(iva), total=VALUES(total)")) {
        if (!isset($result["error"])) $result["error"]=[];
        $result["error"][]=["mensaje"=>"Registro de egresos fallido: ".DBi::$errno.".- ".DBi::$error];
        //if ($isLogLineStarted) plog(" Y.".PHP_EOL,false,false);
        plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Registro de Egresos fallido. ERRNO:".DBi::$errno.", ERROR:".DBi::$error);
        return false;
    }
    if (!isset($result["resumenPagos"])) $result["resumenPagos"]=[];
    $result["resumenPagos"][]=["Se insertaron ".count($arrPyData)." registros en Pagos"];
    if (!$invObj->updateMultipleRecords($arrFcData)) {
        $lastQuery=$query;
        if (!isset($result["error"])) $result["error"]=[];
        $result["error"][]=["mensaje"=>"Actualizacion de facturas fallida: ".DBi::$errno.".- ".DBi::$error];
        //if ($isLogLineStarted) plog(" Z.".PHP_EOL,false,false);
        plog(str_pad("$lineIdx", 3," ",STR_PAD_LEFT).") Actualizacion de Facturas fallida. ERRNO:".DBi::$errno.", ERROR:".DBi::$error);
        doclog("Cargapagos: Actualizacion de Facturas fallida", "error", ["lastQuery"=>$lastQuery, "arrFcData"=>$arrFcData, "errors"=>DBi::$errors, "logs"=>$invObj->log]);
        return false;
    }
    $pyObj->insertIntoProceso($pyMaxId,$_SESSION["user"]??(object)["nombre"=>"nouser"]);
    if (!isset($solObj)) {
        require_once "clases/SolicitudPago.php";
        $solObj=new SolicitudPago();
    }
    $solObj->updateStatus($arrIdData, Facturas::STATUS_PAGADO);
    if ($isLogLineStarted) {
        $numPagado=count($arrIdData);
        $pls=($numPagado==1?"":"s");
        plog(": {$numPagado} pagada{$pls}.".PHP_EOL,false,false);
    }
    return true;
}

clog1seq(-1);
clog2end("configuracion.cargapagos");
