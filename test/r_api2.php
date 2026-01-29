<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "configuracion/conPro.php";
$data = [
    "Fecha" => "07/04/2025",
    "Cargo" => "2.40",
    "CodigoZona" => "GLAMA",
    "debug" => "0"
];
$url = "$_ConProHost/api.aspx?" . http_build_query($data);
 
$response = file_get_contents($url); 

//$ptest="{\"ok\":true,\"count\":1,\"sql\":\" USE [DATA Tesoreria] SELECT COUNT(*)  FROM view_Movimientos_Pagos  WHERE Forma = 'Monto de Credito'  AND Status = 'Activo'  AND (@Fecha = '' OR fecha = @Fecha)  AND (@Cargo = '' OR Cargo = @Cargo)  AND (@CodigoZona = '' OR CodigoZona = @CodigoZona)\",\"params\":{\"Fecha\":\"07/04/2025\",\"Cargo\":\"2.40\",\"CodigoZona\":\"GLAMA\"},\"conn\":\"DATA SOURCE=.\SQLPG;INITIAL CATALOG=DATA APLICACION;USER ID=sa;PASSWORD=****;\"}";

//$test=json_decode(str_replace("\\", "\\\\", $ptest),true); // ["ok"=>true,"count"=>1,"sql"=>" USE","params"=>["F"=>"07","C"=>"2.4","Z"=>"GLAM"],"conn"=>"DATA"];
//$etest=json_encode($test);
//$dtest=json_decode($etest,true);
?>

<html>
<body>
	<H1><?= "$_ConProHost/api.aspx" ?></H1>
	<UL>
		<?php /* LI><B>PRETEST</B>: < ?= $ ptest ? ></LI>
		<LI><B>TEST</B>: < ?= $ test ? ></LI>
		<LI><B>ENCODE</B>: < ?= $ etest ? ></LI>
		<LI><B>DECODE</B>: < ?= $ dtest ? ></LI>
		<LI><B>RENCODE</B>: < ?= json_encode($ dtest) ? ></LI */ ?>
		<LI><B>URL</B>: <?= $url ?></LI>
		<LI><B>FECHA</B>: 07/04/2025</LI>
		<LI><B>CARGO</B>: 2.40</LI>
		<LI><B>CODZONA</B>: GLAMA</LI>
<!-- PLAIN RESPONSE: <?= $response ?> -->
<?php
if (!isset($data["debug"]) || !$data["debug"]) {
	if ($response === false) {
		$response="FALSE";
	    $status="ERROR";
	} else if (trim($response)==="1") {
		$status="ACEPTADO";
	} else $status="PENDIENTE";
?>
		<LI><B>STATUS</B>: '<?= $status ?>'</LI>
		<LI><B>RESPONSE</B>: <PRE><?= $response ?></PRE></LI>
<?php
} else {
	echo "<LI><B>DEBUG</B>: $data[debug]</LI>".PHP_EOL;

	echo "<!-- DUMP DECODE RESPONSE: ".PHP_EOL; var_dump(json_decode($response)); echo PHP_EOL." -->".PHP_EOL;
	$debugResult=json_decode($response, true);

	if (json_last_error() !== JSON_ERROR_NONE) {
		$debugResult = json_decode(preg_replace('/(?<!\\\\)\\\\(?!\\\\)/', '\\\\\\\\', $response), true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			echo "<LI><B>ERRNO</B>: ".json_last_error()."</LI>".PHP_EOL;
			echo "<LI><B>ERROR</B>: ".json_last_error_msg()."</LI>".PHP_EOL;
			echo "<LI><B>RESPUESTA</B>: $response</LI>".PHP_EOL;
		}
	}
	if ($debugResult && is_array($debugResult)) {
		echo "<LI><B>TYPE</B>: JSON</LI>".PHP_EOL;
		foreach ($debugResult as $key => $value) {
			if (is_array($value)) {
				echo "<LI><B>$key</B>:<UL>".PHP_EOL;
				foreach ($value as $item=>$equivalence) echo "<LI><B>$item</B>: '$equivalence'</LI>".PHP_EOL;
				echo "</UL></LI>".PHP_EOL;
			} else echo "<LI><B>$key</B>: '$value'</LI>".PHP_EOL;
		}
	}
}
?>
	</UL>
</body>
</html>
<?php
function jsonToList($jstr,$once=false) {
	$arr=json_decode($jstr, true);
	if (!$once && (json_last_error()!==JSON_ERROR_NONE)) {
		$arr=jsonToList(preg_replace('/(?<!\\\\)\\\\(?!\\\\)/', '\\\\\\\\', $response),true);
	}
}
function arrToList($arr) {

}