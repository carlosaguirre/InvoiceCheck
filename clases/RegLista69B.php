<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
require_once "clases/CatLista69B.php";
class RegLista69B extends DBObject {
    public static $names=["Presuntos","Desvirtuados","Definitivos","SentenciasFavorables"]; //,["Listado_Completo_69-B"]; //
    public static $debug=false;
    private static $url="http://omawww.sat.gob.mx/cifras_sat/Documents/";
    private static $base=null;
    private static $months=["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
    private static $catObj=null;
    private static $regObj=null;
    function __construct() {
        $this->tablename      = "reglista69b";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "tipo", "nuevos", "actualizados","descarga","proceso","observaciones");
        // tipo : presuntos, desvirtuados, definitivos, sentenciasfavorables
        // descarga : duracion de descarga en segundos
        // nuevos : numero de registros nuevos (insertados)
        // actualizados : numero de registros actualizados
        // proceso : duracion del registro de nuevos y actualizados
        // observaciones : Errores, alertas, comentarios
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx RegLista69B xxxxxxxxxxxxxx //\n";
    }
    private static function getBase() {
        if (static::$base===null) {
            require_once "clases/Config.php";
            static::$base=(Config::get("project","sharePath")??"..\\")."LISTA69B\\";
        }
        return static::$base;
    }
    public static function updateDocuments($withDownload=true, $withParsing=true, $withSaving=true) {
        if(static::$debug) echo "<p class=\"function\">function updateDocuments with".($withDownload?"":"out")." download, with".($withParsing?"":"out")." parsing and with".($withSaving?"":"out")." saving</p>";
        if (static::$catObj===null) static::$catObj=new CatLista69B();
        if (static::$regObj===null) static::$regObj=new RegLista69B();
        if ($withDownload) {
            foreach (static::$names as $nm) {
                $fieldArr=["tipo"=>strtolower($nm)];
                $timeInit=microtime(true);
                $successCheck=true;
                if ($fpRemote=fopen(static::$url.$nm.".csv","rb")) {
                    if ($fpLocal=fopen(static::getBase().$nm.".csv","wb")) {
                        while ($buffer=fread($fpRemote,8192))
                            fwrite($fpLocal,$buffer);
                        fclose($fpLocal);
                    } else {
                        $successCheck=false;
                        $fieldArr["observaciones"]="Error en descarga al abrir archivo local ".static::getBase().$nm.".csv";
                    }
                    fclose($fpRemote);
                } else {
                    $successCheck=false;
                    $fieldArr["observaciones"]="Error en descarga al abrir archivo remoto ".static::getBase().$nm.".csv";
                }
                $fieldArr["descarga"]=round(microtime(true)-$timeInit);

                if  (static::$regObj->insertRecord($fieldArr)) {
                    if ($withParsing) static::parseDocument($nm, $withSaving, static::$regObj->lastId);
                } else {
                    echo "<p class=\"error\">No se guard&oacute; registro de $nm<!-- ".static::$regObj->log."--></p>";
                }
            }
        } else if ($withParsing) foreach (static::$names as $nm) {
            static::parseDocument($nm, $withSaving);
        }
        if (static::$debug) echo "<p>END</p>";
    }
    // 10 de junio de 2019
    // 10 de junio del 2019
    // 10 junio 2019
    private static function parseDate($originalStrData,&$info) {
        $info="";
        $strDate=trim($originalStrData);
        $dateChunks=explode(" de ", $strDate);
        $info.="chunks=".count($dateChunks);
        // ToDo: replace parse with chunks
        $i1=strpos($strDate," ");              // 10_de
        if ($i1===false) return null;
        $dd=substr("00".(+substr($strDate,0,$i1)),-2);                                               // 10
        $info.="dd[0,$i1]=$dd";
        $sp=substr($strDate,$i1,4);
        if ($sp===" de ") $i1+=4;
        else if ($sp===" del") $i1+=5;
        else $i1++;
        $i2=strpos($strDate," ",$i1);          // junio_de
        $mmm=substr($strDate,$i1,$i2-$i1);
        $mm=substr("00".(array_search($mmm,static::$months,true)+1),-2);         // 06
        $info.=",mm[$i1,$i2]=$mmm=>$mm";
        $sp=substr($strDate,$i2,4);
        if ($sp===" de ") $i1=$i2+4;
        else if ($sp===" del") $i1=$i2+5;
        else $i1=$i2+1;
        $yy=substr($strDate,$i1,4);                                                                    // 2019
        $info.=",yy[$i1,+4]=$yy";
        return [$yy,$mm,$dd];
    }
    private static function reverseDate($strDate) { // 10/06/2019 =>2019/06/10
        //$arrDate=explode("/",$strDate);
        //return $arrDate[2]."-".$arrDate[1]."-".$arrDate[0];
        return array_reverse(explode("/",$strDate));
    }
    private static function parseDocument($tipo, $withSaving, $regId=-1) {
        global $query;
        if(static::$debug) echo "<p class=\"function\">function parseDocument $tipo".($regId>0?" id=$regId":"");
        $timeInit=microtime(true);
        $fc = iconv('windows-1250', 'utf-8', file_get_contents(static::getBase().$tipo.".csv"));
        file_put_contents(static::getBase()."$tipo.tmp", $fc);
        $fn = fopen(static::getBase()."$tipo.tmp","r");
        $firstLine=fgets($fn);
        $idx1=strpos($firstLine,"actualizada");
        if ($idx1===false) {
            doclog("Falta palabra 'actualizada' en primer línea","lista69b",["file"=>$tipo.".csv","firstLine"=>$firstLine]);
            echo " PARSE ERROR in first line:";
            if(static::$debug) echo "</p>";
            echo "<p>$firstLine</p>";
            fclose($fn);
            unlink(static::getBase()."$tipo.tmp");
            return;
        }
        $idx1+=11;
        $idx1b=strpos($firstLine, " al ", $idx1);
        if ($idx1b!==false) $idx1=$idx1b+4;
        $idx2=strpos($firstLine, ",", $idx1);
        $sub=substr($firstLine, $idx1, $idx2-$idx1);
        $prsDt=static::parseDate($sub,$info);
        if (!isset($prsDt)) {
            doclog("No se pudo obtener fecha en primer línea","lista69b",["file"=>$tipo.".csv","firstLine"=>$firstLine, "sub"=>$sub, "info"=>$info]);
            echo " PARSE ERROR in first line:";
            if(static::$debug) echo "</p>";
            echo "<p>$firstLine</p>";
            fclose($fn);
            unlink(static::getBase()."$tipo.tmp");
            return;
        }
        $regDate=implode("-",$prsDt);

        //$idx2=strpos($firstLine, " ", $idx1);
        //$regDay=substr($firstLine, $idx1,$idx2-$idx1);
        //$idx1=$idx2+4;
        //$idx2=strpos($firstLine, " ", $idx1);
        //$regMonth=substr("00".(array_search(substr($firstLine, $idx1,$idx2-$idx1),static::$months,true)+1),-2);

        //$idx1=$idx2+4;
        //$idx2=strpos($firstLine, ",", $idx1);
        //$regYear=substr($firstLine, $idx1,$idx2-$idx1);
        //$regDate="$regYear/$regMonth/$regDay";
//            flog("RegLista69B->parseDocument $tipo: $regDate.\n1st: $firstLine");
        $init=strtotime("2014-01-01");
        if(static::$debug) echo " registry=$regDate, init=$init, sub=$sub, info=$info</p>";
        fgets($fn); // ignorar segunda linea
        fgets($fn); // ignorar tercera linea
        $nuevos=0;
        $actualizados=0;
        while (! feof($fn)) {
        //for ($i=0;$i<100; $i++) {
            $aux="";
            $arr=fgetcsv($fn,1000,",");
            $num=$arr[0]??"0";
            if (!empty($arr[1]) && $arr[1]!=="XXXXXXXXXXXX") {
                $rfc=$arr[1];
                $nombre=$arr[2];
                $posComment=strpos($nombre,"//");
                if ($posComment!==false) {
                    $comment=trim(substr($nombre,$posComment+2));
                    $nombre=trim(substr($nombre,0,$posComment));
                } else $nombre=trim($nombre);
                $fld_arr=["rfc"=>$rfc,"nombre"=>$nombre,"situacion"=>$tipo];
                $sumaDias=0;
                $diasFld=[];

                $docTypes=["presuntos","desvirtuados","definitivos","favorable"];
                $ofnType=["sat","dof"];
                $daysecs=60*60*24;
                $colN=4;
                foreach ($docTypes as $docIdx => $docValue) {
                    foreach ($ofnType as $ofnIdx => $ofnValue) {
                        $aux.="\n";
                        $sfx=$ofnValue."_".$docValue;
                        if (isset($arr[$colN][0])) {
                            $posDouble=strrpos($arr[$colN], "//");
                            if ($posDouble!==false) $arr[$colN]=trim(substr($arr[$colN],$posDouble+2));
                            $posOG=strpos($arr[$colN], " ");
                            $numOG=substr($arr[$colN], 0, $posOG);
                            $fechaIniOG=substr($arr[$colN], $posOG);
                            $posFecha=strpos($fechaIniOG, "fecha");
                            if ($posFecha!==false) $fechaIniOG=substr($fechaIniOG,$posFecha+5);
                            $parsedFechaOG=static::parseDate($fechaIniOG,$info);
                            if (isset($parsedFechaOG) && $parsedFechaOG!==false)
                                $fechaOG=implode("-",$parsedFechaOG); // 10=len(' de fecha ')
                            else {
                                $fechaOG=$fechaIniOG;
                                doclog("No se pudo interpretar fecha con parseDate","lista69b",["file"=>$tipo.".csv","linetext"=>$fn, "lineidx"=>$colN, "elemtext"=>$arr[$colN], "fecha"=>$fechaIniOG, "info"=>$info]);
                            }
                            $aux.=" sub{$colN} ".$fechaIniOG."=>".$fechaOG.". $info";
                            $fld_arr["num".$sfx]=$numOG;
                            $fld_arr["fch".$sfx]=$fechaOG;
                            $dias=round((strtotime($fechaOG)-$init)/$daysecs);
                            $diasFld["fch".$sfx]=$dias;
                            $sumaDias+=$dias;
                        }
                        $colN++;
                        if (isset($arr[$colN][0])) {
                            $posDouble=strrpos($arr[$colN], "//");
                            if ($posDouble!==false) $arr[$colN]=trim(substr($arr[$colN],$posDouble+2));
                            $posDouble=strrpos($arr[$colN], "-");
                            if ($posDouble!==false) $arr[$colN]=trim(substr($arr[$colN],$posDouble+1));
                            $posDouble=strrpos($arr[$colN], "\n");
                            if ($posDouble!==false) $arr[$colN]=trim(substr($arr[$colN],$posDouble+1));
                            $pubOG=implode("-",static::reverseDate($arr[$colN]));
                            $aux.=" pub{$colN} ".$arr[$colN]."=>".$pubOG;
                            $fld_arr["pub".$sfx]=$pubOG;
                            $dias=round((strtotime($pubOG)-$init)/$daysecs);
                            $diasFld["pub".$sfx]=$dias;
                            $sumaDias+=$dias;
                        }
                        $colN++;
                    }
                }
                echo "<!-- AUX($num) $aux -->\n";
                if ($sumaDias>0) $fld_arr["sumaDias"]=$sumaDias;
                $catData=static::$catObj->getData("rfc='{$rfc}'",0,"id,nombre,CASE situacion WHEN 'Presuntos' THEN fchsat_presuntos WHEN 'Desvirtuados' THEN fchsat_desvirtuados WHEN 'Definitivos' THEN fchsat_definitivos WHEN 'SentenciasFavorables' THEN fchsat_favorable END fecha,sumaDias suma,situacion");
                echo "<!-- OLD($num): $query\n          DATA: ".json_encode($catData)." -->\n";
                $saveQuery=null;
                $fldFecha="";
                switch ($tipo) {
                    case "Presuntos": $fldFecha=$fld_arr["fchsat_presuntos"]; break;
                    case "Desvirtuados": $fldFecha=$fld_arr["fchsat_desvirtuados"]; break;
                    case "Definitivos": $fldFecha=$fld_arr["fchsat_definitivos"]; break;
                    case "SentenciasFavorables": $fldFecha=$fld_arr["fchsat_favorable"]; break;
                }
                if (isset($catData[0])) {
                    $catData=$catData[0];
                    if ($sumaDias==(+$catData["suma"]) && $tipo===$catData["situacion"]) {
                        if (static::$debug && $sumaDias<=0) echo "<span class=\"data\">RFC{$arr[0]}={$rfc} IGUAL ID={$catData["id"]} dias=$sumaDias situacion=$tipo<br></span>";
                        if ($withSaving && $catData["nombre"]!==$nombre && static::$catObj->saveRecord(["id"=>$catData["id"],"nombre"=>$nombre])) echo "<span class=\"data\">";
                        continue;
                    }
                    if (!empty($catData["fecha"]) && !empty($fldFecha) && strtotime($catData["fecha"])>strtotime($fldFecha)) {
                        echo "<span class=\"data\">RFC {$arr[0]}={$rfc}, VIEJO, ID={$catData["id"]}, {$tipo}[$fldFecha] menor que {$catData["situacion"]}[{$catData["fecha"]}].";
                        if(static::$debug) {
                            echo " <span class=\"maroon\">[";
                            $isFirst=true;
                            foreach($fld_arr as $ky=>$vl) {
                                if($isFirst) $isFirst=false; else echo ", ";
                                $dys=(isset($diasFld[$ky])?" <span class=\"magenta\">({$diasFld[$ky]}d)</span>":"");
                                echo $ky."='".$vl."'".$dys;
                            }
                            echo "]</span>";
                        }
                        echo "<br></span>";
                        continue;
                    }
                    echo "<span class=\"data\">RFC{$arr[0]}={$rfc} ";
                    echo "DIFERENTE ID={$catData["id"]}";
                    if ($sumaDias!=(+$catData["suma"]))
                        echo " dias:{$sumaDias}!={$catData["suma"]}";
                    if ($tipo!==$catData["situacion"])
                        echo " situacion:$tipo!={$catData["situacion"]}";
                    $fld_arr["id"]=$catData["id"];
                    $actualizados++;
                } else {
                    echo "<span class=\"data\">RFC{$arr[0]}={$rfc} ";
                    echo "NUEVO! dias=$sumaDias";
                    $nuevos++;
                }
                if(static::$debug) {
                    if(empty($nombre)) {
                        echo "nombre => arr[2]=".$arr[2].", posComment=$posComment";
                        if (isset($comment)) echo ", comment=$comment";
                        echo "<br>";
                    }
                    foreach($fld_arr as $ky=>$vl) {
                        echo ", ";
                        if (isset($diasFld[$ky])) {
                            $nd=+$diasFld[$ky];
                            $dys=" <span class=\"".($nd<0?"red":"magenta")."\">({$nd}d)</span>";
                        } else $dys="";
                        echo $ky."='".$vl."'".$dys;
                    }
                }
                if ($withSaving) {
                    if (static::$catObj->saveRecord($fld_arr)) {
                        $saveQuery=$query;
                        echo " <b>SAVED</b>";
                    } else {
                        $saveQuery=$query;
                        echo " <b class=\"error\">ERROR ".DBi::$errno.": ".DBi::$error."</b><br>".implode(",",$arr);
                    }
                    echo "<pre><code>$query</code></pre>";
                } else echo " <b>TESTING</b><br>";
                echo "</span>";
                if (isset($saveQuery)) echo "<!-- NEW($num): $saveQuery\n           DATA: ".json_encode($fld_arr)." -->\n";
                if (isset($catObj->errors[0])) echo "<!-- ERRORS{$num}:\n *** ".implode("\n *** ", $catObj->errors)." -->\n";
                $rks=array_keys(DBi::$errors);
                if (isset($rks[0])) {
                    echo "<!-- ERRRIS{$num}:";
                    foreach ($rks as $krk => $vrk) echo "\n *{$krk}* $vrk";
                    echo " -->\n";
                }
            } else if (empty($arr[1])) {
                //echo "<span class=\"data\">RFC Vacio: ".implode(",",$arr)."<br></span>";
            } else if ($arr[1]==="XXXXXXXXXXXX") {
                //echo "<span class=\"data\">RFC Desconocido: ".implode(",",$arr)."<br></span>";
            } else {
                //echo "<span class=\"data\">Linea Vacia: ".implode(",",$arr)."<br></span>";
            }
            //$result = fgets($fn);
            //echo $result;
        }
        fclose($fn);
        unlink(static::getBase()."$tipo.tmp");

        $duracion=(microtime(true)-$timeInit);
        $reg_arr=["nuevos"=>$nuevos,"actualizados"=>$actualizados,"proceso"=>round($duracion)];
        if ($regId>0) {
            $reg_arr["id"]=$regId;
        } else {
            $reg_arr["tipo"]=strtolower($tipo);
        }
        $extra="$tipo [$regDate], duracion=".round($duracion,3)."s, nuevos=$nuevos, actualizados=$actualizados";
        if ($withSaving) {
            if (static::$regObj->saveRecord($reg_arr))
                echo "<p class=\"function\">Registro $extra</p>";
            else
                echo "<p class=\"error\"><b>Error en Registro $extra. ".DBi::$errno.": ".DBi::$error."</b><br><pre><code>$query</code></pre></p>";
        } else echo "<p class=\"function\">Sin guardar $extra</p>";
    }
}
// TODO 3: Para cada archivo, cada linea de texto:
  // - Si no existe el rfc, inserta los datos de la línea. Cuenta los registros insertados.
  // - Si obtiene valor suma de la tabla, suma los dias de las fechas de la linea del archivo desde inicio del 2019
  // - Si son iguales ignora la línea y salta a la siguiente.
  // - Si son diferentes actualiza los campos en esa linea, incluyendo la suma. Cuenta los registros actualizados.
  // - Después de la ultima línea inserta resultados en el registro de descarga.

// PENDIENTES
// TODO 1d: Crea tarea para correr el script diariamente entre las 10pm y las 5am // a las 11 pm.
// TODO 2: Crear script de actualizacion de datos que lee los archivos descargados. //entre la 1 am y las 5am.

// COMPLETOS
// presuntos:      1374  http://omawww.sat.gob.mx/cifras_sat/Documents/Presuntos.csv
// desvirtuados:    251  http://omawww.sat.gob.mx/cifras_sat/Documents/Desvirtuados.csv
// definitivos:    8511  http://omawww.sat.gob.mx/cifras_sat/Documents/Definitivos.csv
// favorables:      249  http://omawww.sat.gob.mx/cifras_sat/Documents/SentenciasFavorables.csv
//                10385
// completo:      10384  http://omawww.sat.gob.mx/cifras_sat/Documents/Listado_Completo_69-B.csv
// KRDY 0a: Crear tabla de registro de descarga y actualizacion de datos: ID, TIPO DE ARCHIVO, NUM REG NUEVOS, NUM REG ACTUALIZADOS, (DURACION DE DESCARGA, )DURACION DE ACTUALIZACION, FECHA
// KRDY 0b: En CatLista69B agrega todos los campos en los archivos a la tabla y además incluye un campo suma, inicialmente en cero
// KRDY 1a: Crear script de descarga de los 4 archivos.
// KRDY 1b: Registrar descarga en tabla
// KRDY 1c: Incorpora registro de tiempo de descarga
// KRDY 3: Para cada archivo, cada linea de texto:
  // - Obtiene fecha de la primer linea, para insertar en el registro de descarga al final del ciclo.
  // - Ignora las siguientes 2 lineas de encabezados.
  // - Para las siguientes lineas, identifica el rfc y solicita el valor de los campos id y suma
