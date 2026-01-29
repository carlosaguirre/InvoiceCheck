<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$_SERVER['REMOTE_ADDR'] = "0.0.0.0";
require_once dirname(__DIR__)."/bootstrap.php";
if (PHP_SAPI==="cli") set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__));
require_once "clases/Eventos.php";
$resultado=Eventos::procesar();
foreach ($resultado as $idx => $value) {
	doclog("PROCESAR $idx","eventos",["msg"=>$value,"sapi"=>PHP_SAPI]);
}

if (PHP_SAPI!=="cli") {?>
<html>
	<head>
		<title>EVENTOS</title>
		<script>
			var cut=false;
			var period = 5*60*1000;
		    const start = + new Date();
			setTimeout(function(){
   				window.location.reload();
			}, period);
			var countdown = period;
		    var beginLapse = start;
		    //var blockProgress = 0;
			//var lapsesInBlock = 0;
			(function loop(){
			   setTimeout(function() {
			   	  const currentLapse = + new Date();
			   	  const realProgress = currentLapse-beginLapse;
			   	  const oldTimeValue = countdown;
			   	  beginLapse=currentLapse;
			   	  countdown-=realProgress;
			   	  if (cut || countdown<0) return;
			   	  const mins=Math.floor(countdown/60000);
			   	  const secs=Math.floor(countdown/1000)%60;
			   	  const rawFrac=countdown%1000;
			   	  const frac=Math.floor(rawFrac/10);
			   	  let counttext=("0"+mins).slice(-2)+":"+("0"+secs).slice(-2)+"."+("0"+frac).slice(-2);
			   	  /*blockProgress+=realProgress;
			   	  lapsesInBlock++;
			   	  if (oldTimeValue===period || (lapsesInBlock>5 && (rawFrac<10 || rawFrac>990 || blockProgress>=1000))) {
			   	  	  const blockBegin = countdown+blockProgress;
			   	      console.log(blockBegin+" - "+("000"+blockProgress).slice(-4)+" ("+("0"+lapsesInBlock).slice(-2)+") = "+countdown+" = "+counttext);
			   	      blockProgress=0;
			   	      lapsesInBlock=0;
			   	  }*/
			      const countDownEl=document.getElementById('countdown');
			      if (countDownEl) countDownEl.innerHTML=counttext;
			      loop();
			  }, 10);
			})();
		</script>
		<style>
			table td {
				padding:  0px 5px;
			}
			table tr.ea {
				background-color: rgb(200, 200, 255);
			}
			table tr.eq {
				background-color: rgb(200, 255, 200);
			}
			table tr.rf {
				background-color: rgb(255, 200, 255);
			}
		</style>
	</head>
	<body>
<?php
	if (isset($resultado[0])) {
		echo "<h1 style='position: relative'>Resultado de procesar eventos:<div id='countdown' style='position: absolute; right: 0px; display: inline-block;' ondblclick='cut=true;'>00:00.00</div></h1><ul>";
		foreach ($resultado as $idx => $text) {
			echo "<li>$text</li>";
		}
		$resultado=Eventos::pendientes();
		if (isset($resultado[0])) {
			foreach ($resultado as $idx => $text) {
				echo "<li>$text</li>";
			}
		}
		echo "</ul>";
	} else echo "<h1>No se generaron resultados</h1>";
	require_once "clases/Logs.php";
	global $logObj;
	if (!isset($logObj)) {
		$logObj=new Logs();
	}
	$logObj->rows_per_page=0;
	$logObj->addOrder("fecha","desc");
	$logData=$logObj->getData("seccion='EVENTOS' and fecha>(current_date()-1)");
	if (isset($logData[0])) {
		/*$fecha=strtotime($row["fecha"]);
		//$ejecucion=date('l jS \of F Y h:i:s A', $fecha);
		$ejecucion=substr($fecha, 0, 16);
		$texto=$row["texto"];
		$vencimiento=substr($texto, 0, 16);
		$texto=trim(substr($texto, 22));
		$sp1=strpos($texto, " ");
		if ($sp1!==false && $sp1>0) {
			$sp2=strpos($texto, " ", $sp1+1);
			if ($sp2!==false && $sp2>($sp1+1)) {
				$accion=trim(substr($texto, $sp2));
				$texto=trim(substr($texto, $sp2+1));
			} else {
				$accion=trim(substr($texto, $sp1));
				$texto=trim(substr($texto, $sp1+1));
			}
			if ($texto[0]===":") $texto=trim(substr($texto, 1));
		} else {
			$accion="";
		}
		*/
		?>
		<table border='1'><thead><tr><th>IDX</th><th>VENCIMIENTO</th><th>EJECUCION</th><th>ACCIÓN</th><th>DESCRIPCIÓN</th></tr></thead><tbody>
<?php
		foreach ($logData as $idx => $row) {
			$fecha=$row["fecha"];
			//$fecha=strtotime($row["fecha"]);
			//$ejecucion=date('l jS \of F Y h:i:s A', $fecha);
			$ejecucion=substr($fecha, 0, 16);
			$texto=$row["texto"];
			$vencimiento=substr($texto, 0, 16);
			$texto=trim(substr($texto, 22));
			$sp1=strpos($texto, " ");
			if ($sp1!==false && $sp1>0) {
				$sp2=strpos($texto, " ", $sp1+1);
				if ($sp2!==false && $sp2>($sp1+1)) {
					$accion=trim(substr($texto, 0, $sp2));
					$texto=trim(substr($texto, $sp2+1));
				} else {
					$accion=trim(substr($texto, 0, $sp1));
					$texto=trim(substr($texto, $sp1+1));
				}
				if ($texto[0]===":") $texto=trim(substr($texto, 1));
				if ($texto[0]==="'" && substr($texto,-1)==="'") $texto=substr($texto, 1, -1);
				if ($texto[0]==="'" && substr($texto,-1)==="\"") $texto=substr($texto, 1, -1)."'"; // no debería ser así, revisar porque se esta guardando de esta forma
			} else {
				$accion="";
			}
			$isEA=($accion==="elimina archivo");
			$isEQ=($accion==="ejecuta query");
			$isRF=($accion==="repite funcion");
		?>
		  <tr<?=$isEA?" class='ea'":($isEQ?" class='eq'":($isRF?" class='rf'":""))?>><td><?=$idx?></td><td><?=$vencimiento?></td><td><?=$ejecucion?></td><td><?=$accion?></td><td><?=$texto?></td></tr>
<?php
		}?>
		</tbody></table>
<?php
	}
	?>
	</body>
</html>
<?php
}
