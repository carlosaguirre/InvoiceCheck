<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
/*
?>
<html>
<head><title>StringTest: ShowFile</title></head><body>
<?php
*/
/*
$a="autorizaPago";
$b="rechazaPago";
echo substr($a, 0,-4)."da y ".substr($b, 0,-4)."da";
*/

/*$colPyData=["filename","P-ROV",1,"200723141230","100",null,"100","PAGO","Egreso No. 2"];
$pyExistWhere = "codigoProveedor='{$colPyData[1]}' AND idFactura={$colPyData[2]} AND fechaPago='{$colPyData[3]}'";
$pyExistWhere.= " AND cantidad=".($colPyData[4]??0);
$pyExistWhere.= " AND iva=".($colPyData[5]??0);
$pyExistWhere.= " AND total=".($colPyData[6]??0);
$pyExistWhere.= " AND tipo='{$colPyData[7]}' AND referencia='{$colPyData[8]}'";
echo $pyExistWhere;
*/
//$genDate=date("r",strtotime("-14 days"));
//echo "<p>GENDATE = $genDate</p>";
//$text = "<button type=\"button\" onclick=\"doPaymAction('REENVIAR');\" title=\"REENVIAR\">/*name*/</button>";
//$text = str_replace($text,"/*name*/","OK3");
//echo str_replace("title=\"REENVIAR\"", "title=\"REENVIAR dmenasse\"", $text);
/*
echo "----------------------------------------<br>\n";
$docRoot = $_SERVER["DOCUMENT_ROOT"];
$txtfile=$docRoot."/docs/tmp/con202209APEL.txt";

echo file_get_contents($txtfile);
?>
</body>
</html>
<?php
*/
echo "READY\n";
/*
$pedidos=["GV4−1854","1108-23","177/0523","11/0623","09/0523","11/0623","S/PEDIDO","252/22","241-0523","257-0523","246-0523","249-0523","248-0523","208-0523","258-0523","244-0523","138/23","199/23"];
foreach ($pedidos as $idx => $pedido) {
	//$newPedido=preg_replace('/\x921854/u', "-", $pedido); // '/\x{921854}/u'
	$newPedido=preg_replace('/[\x00-\x1f]/', '?', $pedido);
	//$chars=str_split($pedido);
	//$newPedido=preg_replace('/\x47/u',"g",$newPedido);
	$newPedido=preg_replace('/\xe2\x88\x92/',"-",$newPedido);
	//$newPedido=preg_replace('/\x{e28892}/',"-",$newPedido);
	$chars=preg_split( '//u', $newPedido, null, PREG_SPLIT_NO_EMPTY );
	$hexPerChar="";
	foreach ($chars as $char) {
		if (isset($hexPerChar[0])) $hexPerChar.=";";
		$hexPerChar.=bin2hex($char);
	}
	echo "PEDIDO: '$pedido' => '$newPedido' ".(strcmp($pedido, $newPedido)?"FIXD":"SAME")." | ".bin2hex($pedido)." | $hexPerChar"."\n";
}
*/
/*echo trim(strip_tags("<table class='cfdiErrorList mbpi' type='array' count='1'><tr><TD class='lefted wordwrap'>El usuario osanchez no tiene autorizado dar de alta comprobantes para CAPITALH.</TD></tr></table>"));*/

$text="BID";
$limit=16690;
while(/*$text!=="ZZZ" && */$limit>0) {
	echo $limit." : ".$text."<br>\n";
	$limit--;
	$text++;
}
