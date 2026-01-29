<?php
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/CFDI.php";

if (!empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $dir=$_SERVER["CONTEXT_DOCUMENT_ROOT"];
else if (!empty($_SERVER["DOCUMENT_ROOT"])) $dir=$_SERVER["DOCUMENT_ROOT"];
else $dir="";
$dir.="archivos/";

$maxLine=10000;
$curLine=0;
$stats=["dirLoops"=>0, "dirValid"=>0, "fileLoops"=>0, "fileChecked"=>0, "fileValid"=>0, "fileObsolet"=>0, "fileMessages"=>0];
$cache=[];
$prvObj=null;
function echoScanRec($path,$callbackFunc,$date,$format='Y-m-d') {
	$list=scandir($path);
	$less=0;

	for($i=0; isset($list[$i]); $i++) {
		if ($list[$i]==="."||$list[$i]==="..") {
			$less++;
			continue;
		}
		if (!$callbackFunc($path,$list[$i],$i-$less,$date,$format)) break;
	}
	$callbackFunc($path,null,$i-$less);
}
function displayFile($path,$file,$idx,$date=null,$format='Y-m-d') {
	global $cache,$stats;
	if (!isset($path)) $path="";
	if (isset($file)) {
		$fileabs=$path.$file;
		if (is_dir($fileabs)) {
			$stats["dirLoops"]++;
			//if ($file==="2015" || $file==="2016" || $file==="2017") return true;
			$stats["dirValid"]++;
			echoScanRec($fileabs."/","displayFile",$date,$format);
		} else if (strtolower(substr($file,-4))===".xml") {
			$stats["fileLoops"]++;
			if (!file_exists($fileabs)) return true;
			if (isset($date) && filemtime($fileabs)<$date->getTimestamp()) return true;
			$filename=substr($file,0,-4);
			$usIdx=strpos($filename,"_");
			if ($usIdx>0) {
				$prov=substr($filename,0,$usIdx);
				$folio=substr($filename,$usIdx+1);
				if(isset($folio[3])) {
					$prefix=substr($folio,0,3);
					if ($prefix==="NC_"||$prefix==="RP_") $folio=substr($folio,3);
				}
			}
			if (!isset($cache["errmsg"])) $cache["errmsg"]="";
			if (!isset($cache["errstk"])) $cache["errstk"]="";
			if (!isset($cache["enough"])) $cache["enough"]=true;
			if (!isset($cache["log"])) $cache["log"]="";
			$stats["fileChecked"]++;
			$cfdiObj = CFDI::newInstanceByFileName($fileabs,$file,$cache["errmsg"],$cache["errstk"],$cache["enough"],$cache["log"]);
			if (!$cache["enough"]) {
				if ($cfdiObj->xsd!==CFDI::XSD32) {
					$stats["fileMessages"]++;
					echo "<p>stats[fileMessages]. $fileabs : <b style='background-color: plum;'>ERROR AL LEER ARCHIVO: </b>$cache[errmsg]".getFileInfo($fileabs)."</p>";
				} else { $stats["fileObsolet"]++; } // Facturas version 3.2 son Obsoletas
			} else if ($cfdiObj!==null) {
				if ($cfdiObj->has("folio")) {
					$cfdiFolio=$cfdiObj->get("folio");
					if(isset($cfdiFolio[10])) $cfdiFolio=substr($cfdiFolio,-10);
					if ($folio!==$cfdiFolio) {
						$intFolio=intval($folio);
						$intCfdiFolio=intval($cfdiFolio);
						if ($intFolio<=0 || $intFolio!=$intCfdiFolio) {
							$stats["fileMessages"]++;
							echo "<p>$stats[fileMessages]. $fileabs : <b style='background-color: plum;'>FOLIO INCORRECTO</b> ($cfdiFolio)";
							if ($cfdiObj->has("fecha"))
								echo " ".$cfdiObj->get("fecha");
							echo getFileInfo($fileabs)."</p>";
						} else { $stats["fileValid"]++; } // EL FOLIO CONVERTIDO A NUMERO COINCIDE CON EL ATRIBUTO FOLIO CONVERTIDO A NUMERO
					} else { $stats["fileValid"]++; } // EL FOLIO COINCIDE CON EL ATRIBUTO FOLIO
				} else if ($cfdiObj->has("uuid")) {
					$uuid=$cfdiObj->get("uuid");
					if (isset($uuid[4])) {
						$suffix=strtoupper(substr($uuid, -4));
						if (strtoupper($folio)!==$suffix) {
							$stats["fileMessages"]++;
							echo "<p>$stats[fileMessages]. $fileabs : <b style='background-color: plum;'>UUID INCORRECTO</b> ($uuid)".getFileInfo($fileabs)."</p>";
						} else { $stats["fileValid"]++; } // EL FOLIO COINCIDE CON LOS ULTIMOS 4 CARACTERES DEL ATRIBUTO UUID
					} else {
						$stats["fileMessages"]++;
						echo "<p>$stats[fileMessages]. $fileabs : <b style='background-color: plum;'>SIN FOLIO NI UUID VALIDO</b> ($uuid)".getFileInfo($fileabs)."</p>";
					}
				} else {
					$stats["fileMessages"]++;
					echo "<p>$stats[fileMessages]. $fileabs : <b style='background-color: plum;'>SIN FOLIO NI UUID</b>".getFileInfo($fileabs)."</p>";
				}
			} else {
				$stats["fileMessages"]++;
				echo "<p>$stats[fileMessages]. $fileabs : <b style='background-    color: plum;'>NO FUE POSIBLE ACCEDER AL ARCHIVO</b></p>".$cache["errmsg"];
			}
		}
	}
	return true;
}
function getFileInfo($file) {
	$txt="<UL>";
	$txt.="<LI>File Size B: ".filesize($file)."</LI>";
	$txt.="<LI>Creation Dt: ".date("F d Y H:i:s.", filectime($file))."</LI>";
	$txt.="<LI>Last Modify: ".date("F d Y H:i:s.", filemtime($file))."</LI>";
	$txt.="<LI>Last Access: ".date("F d Y H:i:s.", fileatime($file))."</LI>";
	$txt.="</UL>";
	return $txt;
}
function displayFile2($path,$file,$idx) {
	global $maxLine,$curLine,$stats,$cache,$prvObj;
	if (!isset($path)) $path="";

	if (isset($file)) {
		if ($idx==0) echo $path."<UL>";
		if ($curLine>$maxLine) {
			echo "<LI> . . . </LI>";
			return false;
		}
		if (is_dir($path.$file)) {
			$stats["dirLoops"]++;
			echo "<LI>".($idx+1).". ";
			echoScanRec($path.$file."/",displayFile);
			echo "</LI>";
		} else if (strtolower(substr($file,-4))===".xml") {
			$stats["fileLoops"]++;
			list($prov,$folio,$folio2)=explode("_",substr($file,0,-4));
			$tc="factura";
			if (isset($folio2)) {
				if ($folio==="NC") {
					$tc="nota";
					$folio=$folio2;
				} else if ($folio==="RP") {
					$tc="pago";
					$folio=$folio2;
				} else {
					$folio.="_".$folio2;
				}
			}
			if (isset($cache["PROV"][$prov])) {
				$prvData=$cache["PROV"][$prov];
			} else {
				if (!isset($prvObj)) {
					require_once "clases/Proveedores.php";
					$prvObj=new Proveedores();
				}
				$prvData = $prvObj->getData("rfc='$prov'");
				if (isset($prvData[0])) $prvData=$prvData[0];
				$cache["PROV"][$prov]=$prvData;
			}
			if (isset($prvData["codigo"])&&isset($prvData["razonSocial"])) $prov=$prvData["codigo"];
			echo "<LI title=\"$prvData[razonSocial] ($prvData[rfc])\">$folio ($tc) - $prov</LI>";
			$curLine++;
		}
	} else {
		echo "<LI><B>TOTAL: ";
		if ($stats["dirLoops"]>0) {
			echo $stats["dirLoops"]." directories";
			if ($stats["fileLoops"]>0) echo " and ";
		}
		if ($stats["fileLoops"]>0)
			echo $stats["fileLoops"]." files";
		else if ($stats["dirLoops"]<=0) echo "EMPTY";
		echo ".</B></LI></UL>";
	}
	return true;
}

// SE IGNORAN FACTURAS DE AÑOS ANTERIORES (2015, 2016, 2017)
$dir.="SKARTON/2020/10/";
$dateStr="2020-10-22";
$date=new DateTime($dateStr);
setlocale(LC_ALL,"Etc/GMT+6","Etc/GMT+6.utf8","Etc/GMT+6.UTF-8","es_MX","es_MX.utf8","es_MX.UTF-8");
//define("CHARSET","iso-8850-1");
echo "<b>ARCHIVOS:</b> (con fecha de modificaci&oacute;n igual o posterior al ".utf8_encode(strftime("%a %e de %B de %Y",$date->getTimestamp())).")<br><br>";
echoScanRec($dir,"displayFile",$date);
echo "$stats[dirLoops] DIRECTORIES FOUND<br>";
echo "$stats[dirValid] DIRECTORIES ACCESSED<br>";
echo "$stats[fileLoops] FILES FOUND<br>";
echo "$stats[fileChecked] FILES WITH VALID DATE<br>";
echo "$stats[fileValid] FILES MATCHED SUCCESSFULLY<br>";
echo "$stats[fileObsolet] FILES OBSOLET<br>";
echo "$stats[fileMessages] FILES WITH ERROR<br>";
