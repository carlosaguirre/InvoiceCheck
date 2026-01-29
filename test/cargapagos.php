<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Facturas.php";
$invObj = new Facturas();
$invObj->rows_per_page=1;
$invObj->clearOrder();
$invObj->addOrder("id","desc");
require_once "clases/Pagos.php";
$pyObj = new Pagos();
$pyObj->rows_per_page=0;
$filename="LAISA_230814.txt";
$filePath="C:\\InvoiceCheckShare\\PAGOS\\$filename";
$fileExists=file_exists($filePath);
$htmlLines=[];
$htmlLines[]=($fileExists?"SI":"NO")." Existe Archivo $filename";
if ($fileExists) {
  $lines=file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  $numLines=count($lines);
  $nrfc=trim($lines[4]);
  $htmlLines[]="NRFC: $nrfc, Archivo: '$filename', Lineas={$numLines}";
  $retval=extractData($filename,$nrfc,$lines,$result);
  $htmlLines[]="RESULTADO ".($retval?"POSITIVO":"NEGATIVO");
}
function displayData($res,$depth=false) {
  if(isset($res)) {
    echo "<UL".($depth?" class=\"off\"":"").">";
    foreach ($res as $key => $val) {
      echo "<LI><span class=\"bold\" ondblclick=\"tgglll(event);\" onclick=\"tggl(this);\">$key</span> : ";
      if (is_scalar($val)) echo $val;
      else if (is_array($val)) displayData($val,true);
      else if (is_object($val)) echo "OBJ (".get_class($val).")";
      else echo "[".gettype($val)."]";
      echo "</LI>";
    }
    echo "</UL>";
  } else echo "&lt;NONE&gt;";
}
?>
<html>
  <head>
    <title>Carga Pagos</title>
      <style type="text/css">
         ul li ul.off  {
           display: none;
         }
         .bold {
           font-weight: bold;
         }
      </style>
    <script>
      var stclk=0; var stobj=false;
      function stchk(el) {
        let retval=true;
        if (stclk) {
          clearTimeout(stclk);
          stclk = 0;
          if (stobj===el) retval=false;
          else if (stobj) {
            stobj.nextElementSibling.classList.toggle("off");
            retval=stobj;
          }
          stobj=false;
        }
        return retval;
      }
      function tggl(el) {
        const stel=stchk(el);
        if (!stel) {
          console.log("TOGGLE STOP: Cancel delayed click");
          return;
        } else if (stel!==true) console.log("TOGGLE PREV CLASS OFF: "+stel.textContent);
        if (!el || !el.nextElementSibling) {
          console.log("TOGGLE ERROR: Element or Sibling not found");
          return;
        }
        stobj=el;
        stclk=setTimeout(function(elem) {
          console.log("TOGGLE CLASS OFF: "+elem.textContent);
          elem.nextElementSibling.classList.toggle("off");
          stclk=0;
          stobj=false;
        }, 300, el);
      }
      function tgglll(evt) {
        if (!evt) {
            if (window.event) evt=window.event;
            else { console.log("TOGGLE ALL ERROR: Event not found"); return false; }
        }
        const el=evt.target;
        if (!el) {
          console.log("TOGGLE ALL ERROR: Element not found");
          return;
        }
        const stel=stchk(el);
        if (!stel) console.log("TOGGLE ALL: Cancel delayed click");
        else if (stel!==true) console.log("TOGGLE ALL: TOGGLE PREV CLASS OFF: "+stel.textContent);
        const lst=el.nextElementSibling;
        if (!lst) {
          console.log("TOGGLE ALL ERROR: Sibling not found");
          return;
        }
        const cl=lst.classList;
        cl.toggle("off");
        const chl=lst.querySelectorAll("ul");
        const hasOff=cl.contains("off");
        console.log("TOGGLE ALL CLASS OFF: "+el.textContent+(hasOff?" hiding":" showing"));
        [].forEach.call(chl, e=>e.classList.toggle("off",hasOff));
        //if (evt.cancelBubble != null) evt.cancelBubble = true;
        //if (evt.stopPropagation) evt.stopPropagation();
      }
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>Carga Pagos</h1>
      <?php if (isset($htmlLines[0])) foreach ($htmlLines as $idx => $text) echo "<p>$text</p>"; 
      displayData($result);
      ?>
    </div>
  </body>
</html>
<?php
function extractData($filename,$nrfc,$lines,&$result) {
    global $query,$invObj,$pyObj,$prvObj/*,$solObj*/;
    $numLines=count($lines);
    $inDataSection=FALSE;
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
            if (!isset($result["vacio"])) $result["vacio"]=[];
            $result["vacio"][]=["idx"=>$lineIdx];
            continue;
        }
        if (preg_match('/^(\d+) de (\w+) de (\d+)$/',$trimline,$matches)===1 || $trimline==="El Total de ingresos en el periodo comprendido entre:") {
            $inDataSection=FALSE;
            if (!isset($result["info"])) $result["info"]=[];
            $result["info"][]=["idx"=>$lineIdx,"mensaje"=>"Inicia Bloque Sin Datos"];
            continue;
        }
        if (!$inDataSection&&substr($trimline,0,strlen($colNames[0]))===$colNames[0]) {
            $inDataSection=TRUE;
            for($i=0;isset($colNames[$i]);$i++) {
                $colIdx[$i]=strpos($oneline,$colNames[$i]);
            }
            if (!isset($result["info"])) $result["info"]=[];
            $result["info"][]=["idx"=>$lineIdx,"mensaje"=>"Encabezado de Datos"];
            continue;
        }
        if (!$inDataSection) {
            if (!isset($result["info"])) $result["info"]=[];
            $result["info"][]=["idx"=>$lineIdx];
            continue;
        }
        if (substr($trimline,0,2)==="--") {
            if (!isset($result["info"])) $result["info"]=[];
            $result["info"][]=["idx"=>$lineIdx,"mensaje"=>"Separador de guiones"];
            continue;
        }
        $lineLength=strlen($oneline);
        $colPyData=[$filename,$currProv,$currFactId,"","0.0","0.0","0.0","",""];
        $ciclo=""; $totalPago=0;
        for($i=0;isset($colNames[$i]);$i++) {
            if (isset($colIdx[$i]) && $colIdx[$i]!==FALSE) {
                $idx=$colIdx[$i];
                $rng=$colRng[$i];
                $len=$rng[1]-$rng[0];
                if($len==0) {
                    if (!isset($colIdx[$i+1])) $len=$lineLength-$idx;
                    else if ($colIdx[$i+1]===FALSE) $len=-1;
                    else $len=$colIdx[$i+1]-$idx;
                }
                if ($len>0) $celltext=trim(substr($oneline, $idx+$rng[0], $len));
                else $celltext="";
            } else $celltext="";
            if (isset($celltext[0])) {
                $oldQryLst=$queryList??null;
                $queryList=[];
                if ($i==0) {
                    $currProv=$celltext;
                    if ($currProv!=="S-100") {
                        if (!isset($result["ignorado"])) $result["ignorado"]=[];
                        $result["ignorado"][]=["idx"=>$lineIdx,"Solo se valora al proveedor S-100","corto"=>"Status Ignorado","proveedor"=>$currProv,"oneline"=>$oneline,"range"=>["idx"=>$idx,"rng"=>$rng,"len"=>$len]];
                        continue 2;
                    }
                } else if ($i==1) {
                    $currFolio=$celltext;
                    $celltext="";
                } else if ($i==2) {
                    if (!isset($result["dateCheck"])) $result["dateCheck"]=[];
                    $fechaPago = DateTime::createFromFormat('y/m/d', $celltext)->format('Y-m-d');
                    $celltext = $fechaPago;
                    $gpoChk=(isset($nrfc[0])?" and rfcGrupo='$nrfc'":"");
                    if (substr($currFolio,0,2)==="F-") $currFolio=substr($currFolio,2);
                    //$invObj->clearOrder();
                    //$invObj->addOrder("fechaFactura","desc");
                    $commonWhere="codigoProveedor='$currProv'{$gpoChk} and tipoComprobante='i' and year(fechaFactura)<=year('$celltext')"; //  and fechaPago is null
                    $fctData=$invObj->getData("folio='$currFolio' and $commonWhere",0,"id,ciclo,totalPago,statusn");
                    $queryList[]=$query;
                    if (isset($fctData[1])) {
                        $result["aviso"][]=["idx"=>$lineIdx,"mensaje"=>"Hay datos de factura ambiguos por folio repetido","corto"=>"Facturas Ambiguas","proveedor"=>$currProv,"folio"=>$currFolio,"rrfc"=>$nrfc,"fechaPago"=>$fechaPago,"queries"=>$queryList,"data"=>$fctData];
                        //continue 2;
                    }
                    //$invObj->clearOrder();
                    //$invObj->addOrder("id","desc");
                    if (isset($currFolio[10])) $currFolio=substr($currFolio,-10);
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
                    if (!isset($fctData[0])) {
                      $fctData=$invObj->getData("concat(serie,folio)='$currFolio' and $commonWhere",0,"id,ciclo,totalPago,statusn,folio");
                      $queryList[]=$query;
                    }
                    if (isset($fctData[0])) {
                        $currFactId="".$fctData[0]["id"];
                        $ciclo=$fctData[0]["ciclo"];
                        if (isset($fctData[0]["totalPago"][0]))
                            $totalPago=$fctData[0]["totalPago"];
                        if (isset($fctData[0]["statusn"][0]))
                            $currStatusn=$fctData[0]["statusn"];
                        if (isset($fctData[0]["folio"][0])) $currFolio=$fctData[0]["folio"];
                        $colPyData[$i]=$currFactId; // index 2, guardar id de factura
                        if (empty($currStatusn)||(+$currStatusn)<0) {
                            $lastErrFolio=$currFolio;
                            $lastErrFactId=$currFactId;
                            if (!isset($result["invalido"])) $result["invalido"]=[];
                            if ($currStatusn===0||$currStatusn==="0") {
                                $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura no ha sido Aceptada (Status Pendiente)","corto"=>"Status Pendiente","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>$currFactId];
                            } else {
                                $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura no esta registrada","corto"=>"Status Temporal","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>$currFactId];
                            }
                            continue 2;
                        } else if ($currStatusn>=128) {
                            $lastErrFolio=$currFolio;
                            $lastErrFactId=$currFactId;
                            if (!isset($result["invalido"])) $result["invalido"]=[];
                            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura esta cancelada","corto"=>"Status Cancelado","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>$currFactId];
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
                            $queryList[]=$query;
                            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"La factura no esta registrada","corto"=>"Factura no registrada","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>0];
                        } else {
                            $queryList[]=$query;
                            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"El proveedor no esta registrado","corto"=>"Proveedor no registrado","proveedor"=>$currProv,"folio"=>$currFolio,"queries"=>$queryList,"id"=>0];
                        }
                        continue 2;
                    }
                    $result["dateCheck"][]=["idx"=>$lineIdx,"proveedor"=>$currProv,"folio"=>$currFolio,"fctData"=>$fctData,"fechaPago"=>$fechaPago,"oldQryLst"=>$oldQryLst,"oneline"=>$oneline,"range"=>["idx"=>$idx,"rng"=>$rng,"len"=>$len]];
                } else if ($i>2&&$i<6) {
                    $celltext=str_replace(["'",","],"",$celltext);
                }
            } else if ($i==0) {
              $celltext=$currProv;
              if ($currProv!=="S-100") {
                  if (!isset($result["ignorado"])) $result["ignorado"]=[];
                  $result["ignorado"][]=["idx"=>$lineIdx,"Solo se valora al proveedor S-100","corto"=>"Status Ignorado","proveedor"=>$currProv,"oneline"=>$oneline,"range"=>["idx"=>$idx,"rng"=>$rng,"len"=>$len]];
                  continue 2;
              }
            } else if ($i==1) {
                if (isset($currFactId[0])) {
                    if ($currFactId===$lastErrFactId) {
                        if (!isset($result["invalido"])) $result["invalido"]=[];
                        $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"Misma factura, mismo error","corto"=>"Ver anterior","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId];
                        continue 2;
                    } else $celltext=$currFactId;
                } else if ($currFolio===$lastErrFolio) {
                    if (!isset($result["invalido"])) $result["invalido"]=[];
                    $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"Mismo folio sin registro, mismo error","corto"=>"Ver anterior","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>0];
                    continue 2;
                } else {
                    if (!isset($result["invalido"])) $result["invalido"]=[];
                    $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"Datos Incompletos","corto"=>"Incompleto","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>0];
                    continue 2;
                }
            } else if ($i==5) $celltext="".((+$colPyData[4])+(+$colPyData[5]));
            else $celltext="";
            if (isset($celltext[0])) $colPyData[$i+1]=$celltext;
        }
        if (strtoupper($colPyData[7])!=="PAGO") {
            if (!isset($result["invalido"])) $result["invalido"]=[];
            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"No Es Pago: ".$colPyData[7],"corto"=>"Tipo:".$colPyData[7],"proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$colPyData[2]];
            continue;
        }
        if (strtoupper(substr($colPyData[8],0,6))!=="EGRESO") {
            if (!isset($result["invalido"])) $result["invalido"]=[];
            $result["invalido"][]=["idx"=>$lineIdx,"mensaje"=>"No Es Egreso: ".$colPyData[8],"corto"=>"Ref:".$colPyData[8],"proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$colPyData[2]];
            continue;
        }
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
            $result["aviso"][]=["idx"=>$lineIdx,"mensaje"=>"Egreso Repetido. Archivo original: ".$pyFileList,"proveedor"=>$currProv,"folio"=>$currFolio,"query"=>$query,"id"=>$colPyData[2]];
            //continue;
        }
        $currStatusn=(+$currStatusn)|Facturas::STATUS_PAGADO;
        $arrIdData[]=$colPyData[2];
        $arrFcData[]=["id"=>$colPyData[2],"fechaPago"=>$colPyData[3],"totalPago"=>$colPyData[6],"referenciaPago"=>$colPyData[8],"status"=>"Pagado","statusn"=>$currStatusn];
        $arrPyData[]=$colPyData;
        if (!isset($result["aceptado"])) $result["aceptado"]=[];
        $result["aceptado"][]=["idx"=>$lineIdx,"mensaje"=>"Status PAGADO ($currStatusn)","proveedor"=>$currProv,"folio"=>$currFolio,"id"=>$currFactId,"where"=>$pyExistWhere];
    }
    if (!isset($arrPyData[0])) {
        if (!isset($result["error"])) $result["error"]=[];
        $result["error"][]=["mensaje"=>"Ningun Egreso Registrado"];
        return false;
    }
    $result["colNames"]=$colNames;
    $result["dbPyCols"]=$dbPyCols;
    $result["colRng"]=$colRng;
    $result["pagosData"]=$arrPyData;
    $result["arrFcData"]=$arrFcData;
    $result["arrIdData"]=$arrIdData;
    return true;
}
