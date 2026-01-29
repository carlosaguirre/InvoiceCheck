<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";
$timeStart=time();

function getList($dir,$pfx) {
	$timeFuncIni=time();
	$list=(in_array(substr($dir, -8, 4), ["invo","2025","CP_C"]))?glob($dir."CP_CTR_*.pdf"):[];
	$listLen=count($list);
	$timeFuncGlob=time()-$timeFuncIni;
	if ($listLen>0 || $timeFuncGlob>0) echo "<!-- $pfx # getListGlb '$dir' #$listLen ($timeFuncGlob) -->\n";
	$timePreLoop=time();
	foreach (glob($dir."*",GLOB_ONLYDIR) as $key => $value) {
		if (in_array(basename($value), ["clases","configuracion","consultas","css","cuentas","descargas","examples","imagenes","LOGS","manual","recibos","sat","scripts","selectores","tareas","templates","test","borrados","error"])) continue;  
		$recList=getList($value."/","$pfx #"); // 2025
		$list = [...$list, ...$recList];
	}
	$timeFuncLoop=time()-$timePreLoop;
	if ($listLen>0 || $timeFuncLoop>0) echo "<!-- $pfx # getListEnd '$dir' #$listLen ($timeFuncLoop) -->\n";
	return $list;
}
$path="C:/Apache24/htdocs/invoice/";
$pathLen=strlen($path);
$data=[
	"archivos/CP_CTR_/CP_CTR_10764.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_12155.pdf"	=>["MOR2405-155","2,800.00","SOLUCIONES LOGISTICAS EXXPREZO, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_12293.pdf"	=>["MOR2405-293","25,760.00","SOLUCIONES LOGISTICAS EXXPREZO, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_12328.pdf"	=>["MOR2405-328","1,400.00","SOLUCIONES LOGISTICAS EXXPREZO, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_12329.pdf"	=>["MOR2405-329","6,477.15","SOLUCIONES LOGISTICAS EXXPREZO, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_12350.pdf"	=>["MOR2405-350","10,978.79","SOLUCIONES LOGISTICAS EXXPREZO, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_14365.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_14931.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_14932.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_14933.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_14934.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15090.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15091.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15092.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15093.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15131.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15174.pdf"	=>["APS2505-083","357,325.18","BIO PAPPEL SCRIBE, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15176.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15177.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15178.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15179.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15190.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15191.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15286.pdf"	=>["RGA2505-005","4,775.47","TELEFONOS DE MEXICO"],
	"archivos/CP_CTR_/CP_CTR_15305.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15306.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15307.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15308.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15309.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_15362.pdf"	=>["MOR2505-032","918,504.01","BIO PAPPEL, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_15445.pdf"	=>["MOR2505-030","36,267.24USD","CELUPAL INTERNACIONAL S. DE RL DE C.V."],
	"archivos/CP_CTR_/CP_CTR_17078.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_25038.pdf"	=>["GLA2408-039","7560.65","ASOCIACION NACIONAL DE FABRICANTES DE CAJAS Y EMPAQUES DE CARTON CORRUGADO"],
	"archivos/CP_CTR_/CP_CTR_25039.pdf"	=>["GLA2408-038","7560.65","ASOCIACION NACIONAL DE FABRICANTES DE CAJAS Y EMPAQUES DE CARTON CORRUGADO"],
	"archivos/CP_CTR_/CP_CTR_28062.pdf"	=>["GLA2506-062","22,221.19","IMMERMEX, S.A. DE C.V."],
	"archivos/CP_CTR_/CP_CTR_359.pdf"	=>["RGA2505-005","4,775.47","TELEFONOS DE MEXICO"],
	"archivos/CP_CTR_/CP_CTR_4328.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_5377.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_5378.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_5379.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_5380.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_5381.pdf"	=>["","",""],
	"archivos/CP_CTR_/CP_CTR_5383.pdf"	=>["","",""],
//	"archivos/APSA/2025/03/CP_CTR_15049.pdf" =>["APS2505-064","386,253.85","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/APSA/2025/03/CP_CTR_15050.pdf" =>["APS2505-065","353,022.29","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/APSA/2025/03/CP_CTR_15051.pdf" =>["APS2505-066","336,871.37","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/APSA/2025/03/CP_CTR_15052.pdf" =>["APS2505-067","11,874.11","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/APSA/2025/03/CP_CTR_15053.pdf" =>["APS2505-068","363,422.08","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/APSA/2025/03/CP_CTR_15089.pdf" =>["APS2505-069","275,375.71","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/APSA/2025/04/CP_CTR_15175.pdf" =>["APS2505-070","343,658.48","BIO PAPPEL, S.A. DE C.V."],
//	"archivos/COREPACK/2025/05/CP_CTR_11216.pdf" =>["COR2506-004","20,720.00","BEJARANO CEDILLO RAFAEL"],
//	"archivos/COREPACK/2025/05/CP_CTR_11221.pdf" =>["COR2506-003","20,720.00","BEJARANO CEDILLO RAFAEL"],
//	"archivos/COREPACK/2025/05/CP_CTR_11227.pdf" =>["COR2506-002","20,720.00","BEJARANO CEDILLO RAFAEL"],
//	"archivos/COREPACK/2025/05/CP_CTR_11332.pdf" =>["COR2506-018","28,000.00","ARREDONDO GOMEZ OSCAR"],
//	"archivos/COREPACK/2025/05/CP_CTR_11343.pdf" =>["COR2506-019","62,720.00","BEJARANO CEDILLO RAFAEL"],
	"archivos/JYL/2025/05/CP_CTR_6535.pdf"	=>["JYL2505-027","17,060.83","MARTINEZ GARCIA JUAN VICTOR"],
	"archivos/JYL/2025/05/CP_CTR_6564.pdf"	=>["JYL2505-028","6,291.61","MARTINEZ GARCIA JUAN VICTOR"],
	"archivos/JYL/2025/05/CP_CTR_6565.pdf"	=>["JYL2505-029","2,634.36","MARIA CARMEN LEONILA JUAREZ LOPEZ"],
	"archivos/JYL/2025/06/CP_CTR_6571.pdf"  =>["JYL2506-003","10,362.15","MENDOZA FLORES ALFONSO"],
	"archivos/MELO/2025/05/CP_CTR_13995.pdf" =>["MEL2505-008","202,052.73","PICHARDO ISABEL TOMAS"],
	"archivos/MELO/2025/05/CP_CTR_13998.pdf" =>["MEL2505-014","4,463.20","DISTRIBUIDORA MOYEL, S.A. DE C.V."],
	"archivos/MELO/2025/05/CP_CTR_13999.pdf" =>["MEL2505-013","66,504.00","GRUPO LYCAN S.A DE C.V."],
	"archivos/MELO/2025/05/CP_CTR_14002.pdf" =>["MEL2505-011","22,458.00","SALMITEX S.A. DE C.V."],
	"archivos/MELO/2025/05/CP_CTR_14004.pdf" =>["MEL2505-012","161,870.34","PEREZ HERNANDEZ CLARA"],
	"archivos/MELO/2025/05/CP_CTR_14007.pdf" =>["MEL2505-010","1,680.50","DISTRIBUIDORA MOYEL, S.A. DE C.V."],
	"archivos/MELO/2025/05/CP_CTR_14008.pdf" =>["MEL2505-009","59,787.56","FABIOSFERA S. DE R.L. DE C.V."],
	"archivos/MELO/2025/05/CP_CTR_14009.pdf" =>["MEL2505-007","995,647.96","CORPORACION IMPRESORA"]
];
?>
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_blank">
    <title>Validación de Facturas Electrónicas del Corporativo</title>
    <script src="scripts/general.js?ver=25.3q"></script>
    <link href="css/general.php" rel="stylesheet" type="text/css">
    <style>
    	table td {
    		padding: 2px;
    		position: relative;
    	}
		table td:empty::before {
			content: ''; /* Ensures the line appears even if the cell is empty */
			position: absolute;
			top: 50%; /* Center the line vertically */
			left: 0;
			width: 100%;
			height: 1px; /* Thickness of the line */
			background-color: black; /* Line color */
			transform: translateY(-50%);
		}
    </style>
    <script>
    	function viewForm(solId, solFolio, solStatus) {
		    const inputList=[{eName:"INPUT",type:"hidden",name:"SOLID",value:solId},{eName:"INPUT",type:"hidden",name:"SOLFOLIO",value:solFolio},{eName:"INPUT",type:"hidden",name:"ENCABEZADO",value:"SOLICITUD "+solStatus+solFolio}];
		    const formObj={eName:"FORM",target:"solpago",method:"POST",action:"templates/respuestaSolPago.php",eChilds:inputList};
		    const formElem=ecrea(formObj);
		    document.body.appendChild(formElem);
		    window.open("","solpago");
		    formElem.submit();
		}
		function delFile(path,idx) {
			console.log("INI delFile path='"+path+"', idx='"+idx+"'");
			document.body.delFileData={path:path, idx:idx};
			overlayConfirmation("¿Seguro/a que desea eliminar el archivo?", "Confirmar Eliminar Archivo", delFileConfirm);
		}
		function delFileConfirm() {
			const data=document.body.delFileData;
			if (!data) {
				console.log("INI delFileConfirm: NO DATA");
				return false;
			}
			console.log("INI delFileConfirm path='"+data.path+"', idx='"+data.idx+"'");
			let ovTime=setTimeout(()=>{overlayWheel();},200);
			readyService("consultas/Archivos.php",{action:"borraArchivo",hasJson:1,archivoABorrar:data.path},(j,x)=>{
				console.log("Successful Response: ",j);
				clearTimeout(ovTime);
				overlayClose();
				cladd("row_"+data.idx,"hidden");
			},(e,o,x)=>{
				console.log("Error Response: "+e);
				clearTimeout(ovTime);
				overlayClose();
				overlayMessage(getParagraphObject("No se pudo borrar el archivo.", "errorLabel"),"Error");
			});
		}
    </script>
  </head>
  <body class="scrollable">
  	<table border="1" class="collapse">
  	  <thead id="theadElem" class="hidden">
  	  	<tr><th>#</th><th>archivo</th><th>solicitud</th><th>total</th><th>proveedor</th></tr>
  	  </thead>
  	  <tbody><script>overlayWheel();</script>
<?php
$timePreLoop=time();
$timePrep=$timePreLoop-$timeStart;
global $solObj, $invObj, $ordObj, $ctrObj, $prvObj, $gpoObj, $query;
if ($timePrep>0) echo "<!-- # timePrep ($timePrep) -->\n";
foreach (getList($path,"# #") as $idx => $value) {
	$timeLoadIni=time();
	$dataKey=substr($value, $pathLen);
	$baseName=pathinfo($value, PATHINFO_FILENAME);
	// checar directo en base donde baseName sea comprobantePago o comprobantePagoPDF y obtener solicitud relacionada sin tener que extraer texto del pdf, checar que el folio incluido en el nombre coincida con el folio del contra recibo (o factura u orden si fuera el caso)

	$dataVal=$data[$dataKey]??["&nbsp;","&nbsp;","&nbsp;"];
	$solFolio=$dataVal[0];
	$solTotal=$dataVal[1];
	if (isset($solTotal[0]) && $solTotal!=="&nbsp;") $solTotal=+str_replace(",", "", $solTotal);
	$solProv=$dataVal[2];
	if (isset($solProv[0]) && $solProv!=="&nbsp;") {
		if (!isset($prvObj)) { require_once "clases/Proveedores.php"; $prvObj=new Proveedores(); }
		$prvData = $prvObj->getData("razonSocial like '{$solProv}%'");
		if (isset($prvData[0]["id"])) $codPrv=$prvData[0]["codigo"];
		else {
			$cutPos=strpos($solProv, ",");
			if ($cutPos===false) {
				$cutPos=strpos($solProv," S.");
				if ($cutPos===false) {
					$cutPos=strpos($solProv," SA ");
					if ($cutPos===false) $cutPos=floor($solProv," ");
				}
			}
			if ($cutPos!==false) {
				$razSocCut = substr($solProv, 0, $cutPos);
				$prvData = $prvObj->getData("razonSocial like '{$razSocCut}%'");
				if (isset($prvData[0]["id"])) $codPrv=$prvData[0]["codigo"];
			}
		}
	}
	if (file_exists($value)) {
		if (!isset($solObj)) { require_once "clases/SolicitudPago.php"; $solObj=new SolicitudPago(); }
		$orderList=$solObj->orderlist;
		$solObj->clearOrder();
		$solObj->addOrder("id","desc");
		$solData =  ($solFolio==="&nbsp;"?[]:$solObj->getData("folio='$solFolio'"));
		$solQuery = $query;
		$solBlock=$solFolio;
		$removeFileBlock="<img src=\"imagenes/icons/trash32.png\" width=\"16\" class=\"btnLt vAlignCenter marL4\" onclick=\"delFile('$dataKey',$idx);\">";
		$solCellClass="";
		if (!isset($solData[0]["id"]) && isset($codPrv[0])) {
			$folioCombo=substr($solFolio, 0, 3)."%".substr($solFolio, -3);
			$solData1=$solObj->getData("s.idContrarrecibo is not null and s.folio like '$folioCombo' and c.codigoProveedor='$codPrv' and c.total='$solTotal'",0,"s.id solId, s.folio solFolio, s.status solStatus, s.proceso solProceso, s.idContrarrecibo ctrId, c.comprobantePago, concat('/archivos/',c.aliasGrupo,'/',year(c.fechaRevision),'/',lpad(month(c.fechaRevision),2,'0'),'/') ruta","s inner join contrarrecibos c on s.idContrarrecibo=c.id");
			$solQuery1=$query;
			echo "<!-- COMPARAR DATA: ".json_encode($dataVal)." CON SOLICITUD: ".json_encode($solData1)." -->\n";
			if (isset($solData1[0]["solId"]) && $solData1[0]["solStatus"]==4) {
				$solData1=$solData1[0];
				if (empty($solData1["comprobantePago"])) {
					if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj=new Grupo(); }
					if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj=new Contrarrecibos(); }
					$ruta=$solData1["ruta"];
					$webRelName=$ruta.$baseName.".pdf";
					if (substr($dataKey, 0, 7)==="CP_CTR_" || substr($dataKey, 9, 7)==="CP_CTR_") {
						if (rename($value, $path.$webRelName)) {
							if ($ctrObj->saveRecord(["id"=>$solData1["ctrId"],"comprobantePago"=>$baseName])) {
								$ctrQuery=$query;
								unlink($value);
								doclog("SAVED RECEIPT WITH PAID TICKET, AND REMOVED FILE OUT OF PLACE","archivo",["idx"=>$idx,"dataKey"=>$dataKey,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1,"saveQuery"=>$ctrQuery]);
								//;
							} else {
								$ctrQuery=$query;
								doclog("NOT SAVED RECEIPT WITH PAID TICKET","error",["idx"=>$idx,"dataKey"=>$dataKey,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1,"saveQuery"=>$ctrQuery]);
							}
						} else {
							doclog("NOT COPIED PAID TICKET RECEIPT","error",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1]);
						}
					} else {
						if ($webRelName!==$dataKey && !rename($value, $path.$webRelName)) {
							$ctrQuery=null;
							doclog("COULD NOT MOVE FILE TO RIGHT REQUEST PATH","error",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1]);
						} else if ($ctrObj->saveRecord(["id"=>$solData1["ctrId"],"comprobantePago"=>$baseName])) {
							$ctrQuery=$query;
							doclog("FILE MOVED AND SAVED TO RIGHT REQUEST PATH","archivo",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1,"saveQuery"=>$ctrQuery]);
						} else {
							$ctrQuery=$query;
							doclog("NOT SAVED RECEIPT WITH PAID TICKET 2","error",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1,"saveQuery"=>$ctrQuery]);
						}
					}
				} else echo "<!-- FILE ALREADY IN PLACE AND DATA. IDX='$idx', VALUE='$value', CODPROV='$codPrv', QUERY='$solQuery', QUERY1='$solQuery1' -->\n"; // else doclog("ALREADY HAS FILE IN PLACE","archivo",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$query,"data"=>$solData,"data1"=>$solData1]);
				$solData = $solObj->getData("id=$solData1[solId]");
				$solQuery = $query;
				if (isset($solData[0]["id"])) $solData=$solData[0];
				else doclog("NOT FOUND REQUEST WITH UPDATED DATA","error",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1]);
			} else {
				doclog("NOT FOUND OR INVALID REQUEST","archivo",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData,"query1"=>$solQuery1,"data1"=>$solData1]);
			}
		}
		if (isset($solData[0]["id"])) {
			$solData=$solData[0];
			$solId=$solData["id"];
	        // STATUS = { 0:SIN_FACTURA, 1:CON_FACTURA, 2:AUTORIZADA, 4:ACEPTADA, 8:CONTRARRECIBO, 16:EXPORTADA, 32:RESPALDADA, 64:PAGADA, 127:SINCANCELAR, 128:CANCELADA }
			$solStatus=$solData["status"]; if (isset($solStatus)) $solStatus=+$solStatus;
    	    // PROCESO = { -1:SINAUTORIZAR, 0:AUTORIZADA, 1:COMPRAS, 2:CONTABLE, 3:ANEXADA, 4:PAGADA, 5:NOREQ_FACTURA }
			$solProceso=$solData["proceso"]; if (isset($solProceso)) $solProceso=+$solProceso;
			if (isset($solStatus) && $solStatus>=128) $solStatus="CANCELADA ";
			else switch($solProceso) {
				case -1: $solStatus="SIN AUTORIZAR "; break;
				case 4: $solStatus="PAGADA "; break;
				default: $solStatus=""; break;
			}
			$solBlock="<button onclick=\"viewForm($solId,'$solFolio','$solStatus');\">$solFolio</div>";
			if (isset($solData["idFactura"][0])) {
				if (!isset($invObj)) { require_once "clases/Facturas.php"; $invObj=new Facturas(); }
				$invData=$invObj->getData("id=$solData[idFactura]");
				$invQry=$query;
				if (isset($invData[0]["id"])) {
					$invData=$invData[0];
					if (isset($invData["comprobantePagoPDF"][0])) {
						if ($baseName===$invData["comprobantePagoPDF"]) {
							$relName=$invData["ubicacion"].$baseName.".pdf";
							if (file_exists($path.$relName)) {
								$solCellClass="bggreen";
								if (unlink($value)) doclog("REMOVED USELESS FILE, ALREADY IN PLACE","archivo",["idx"=>$idx,"value"=>$value,"invData"=>$invData,"newFile"=>$relName]);
							} else if (rename($value, $path.$relName)) {
								doclog("MOVED FILE OUT OF PLACE","archivo",["idx"=>$idx,"value"=>$value,"invData"=>$invData]);
								$solCellClass="bgcyan";
							} else {
								doclog("ERROR MOVING FILE","archivo",["idx"=>$idx,"value"=>$value,"invData"=>$invData]);
								$solCellClass="bggold";
							}
							$removeFileBlock="<img src=\"imagenes/pt.png\" width=\"22\" class=\"marL4\">";
						} else { $solCellClass="bgred"; $solBlock=$solFolio; }
					}
				}
			} else if (isset($solData["idOrden"][0])) {
				if (!isset($ordObj)) { require_once "clases/OrdenesCompra.php"; $ordObj=new OrdenesCompra(); }
				$ordData=$ordObj->getData("id=$solData[idOrden]");
				$ordQry=$query;
				if (isset($ordData[0]["id"])) {
					$ordData=$ordData[0];
					if (isset($ordData["comprobantePago"][0])) {
						if ($baseName===$ordData["comprobantePago"]) {
							$relName=$ordData["rutaArchivo"].$baseName.".pdf";
							if (file_exists($path.$relName)) {
								$solCellClass="bggreen";
								if (unlink($value)) doclog("REMOVED USELESS FILE, ALREADY IN PLACE","archivo",["idx"=>$idx,"value"=>$value,"ordData"=>$ordData,"newFile"=>$relName]);
							} else if (rename($value, $path.$relName)) {
								doclog("MOVED FILE OUT OF PLACE","archivo",["idx"=>$idx,"value"=>$value,"ordData"=>$ordData]);
								$solCellClass="bgcyan";
							} else {
								doclog("ERROR MOVING FILE","archivo",["idx"=>$idx,"value"=>$value,"ordData"=>$ordData]);
								$solCellClass="bggold";
							}
							$removeFileBlock="<img src=\"imagenes/pt.png\" width=\"22\" class=\"marL4\">";
						} else { $solCellClass="bgred"; $solBlock=$solFolio; }
					}
				}
			} else if (isset($solData["idContrarrecibo"][0])) {
				if (!isset($ctrObj)) { require_once "clases/Contrarrecibos.php"; $ctrObj=new Contrarrecibos(); }
				$ctrData=$ctrObj->getData("id=$solData[idContrarrecibo]",0,"id, comprobantePago, concat('/archivos/',aliasGrupo,'/',year(fechaRevision),'/',lpad(month(fechaRevision),2,'0'),'/') ruta");
				$ctrQuery=$query;
				if (isset($ctrData[0]["id"])) {
					$ctrData=$ctrData[0];
					if (isset($ctrData["comprobantePago"][0])) {
						if ($baseName===$ctrData["comprobantePago"]) {
							$relName=$ctrData["ruta"].$baseName.".pdf";
							if (file_exists($path.$relName)) {
								$solCellClass="bggreen";
								if (unlink($value)) doclog("REMOVED USELESS FILE, ALREADY IN PLACE","archivo",["idx"=>$idx,"value"=>$value,"ctrData"=>$ctrData,"newFile"=>$relName]);
							} else if (rename($value, $path.$relName)) {
								doclog("MOVED FILE OUT OF PLACE","archivo",["idx"=>$idx,"value"=>$value,"ctrData"=>$ctrData]);
								$solCellClass="bgcyan";
							} else {
								doclog("ERROR MOVING FILE","archivo",["idx"=>$idx,"value"=>$value,"ctrData"=>$ctrData]);
								$solCellClass="bggold";
							}
							$removeFileBlock="<img src=\"imagenes/pt.png\" width=\"22\" class=\"marL4\">";
						} else { $solCellClass="bgred"; $solBlock=$solFolio; }
					}
				}
			}
		} else doclog("NOT FOUND REQUEST IN DB","error",["idx"=>$idx,"value"=>$value,"codPrv"=>$codPrv,"query"=>$solQuery,"data"=>$solData]);
		if (isset($solCellClass[0])) $solCellClass=" $solCellClass";
?>
        <tr id="row_<?=$idx?>"><td><?=$idx+1?></td><td class="righted<?=$solCellClass?>"><a href="http://invoicecheck.dyndns-web.com:81/invoice/<?=$dataKey?>" target="archivo"><?=$dataKey?></a><?=$removeFileBlock?></td><td class="lefted"><?=$solBlock?></td><td class="righted"><?=$dataVal[1]?></td><td class="lefted"><?=$dataVal[2]?></td></tr>
<?php
	} else {
?>
        <tr class="bgredvip redden"><td><?=$idx+1?></td><td class="righted"><?=$dataKey?></td><td class="lefted"><?=$dataVal[0]?></td><td class="righted"><?=$dataVal[1]?></td><td class="lefted"><?=$dataVal[2]?></td></tr>
<?php
	}
	$timeLoadDif=time()-$timeLoadIni;
	if ($timeLoadDif>0) echo "<!-- # # loopLoaded $idx)'$value' ($timeLoadDif) -->\n";
}
$timeLoadEnd=time()-$timePreLoop;
echo "<!-- # loadEnd ($timeLoadEnd) -->\n";
echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"clrem('theadElem','hidden');overlayClose();ekil(this);\">\n";
?>
	  </tbody>
	</table>
  </body>
</html>
<?php
