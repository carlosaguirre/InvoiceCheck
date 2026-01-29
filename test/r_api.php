<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/RemoteAPI.php";
require_once "configuracion/conPro.php";

$ra=RemoteAPI::newInstance("$_ConProHost/api.aspx", ["Fecha"=>"07/04/2025", "Cargo"=>"2.40", "CodigoZona"=>"GLAMA"]);
$out=$ra->exec();
?>
<html>
<body>
	<H1><?= "$_ConProHost/api.aspx" ?></H1>
	<UL>
		<LI><B>FECHA</B>: 07/04/2025</LI>
		<LI><B>CARGO</B>: 2.40</LI>
		<LI><B>CODZONA</B>: GLAMA</LI>
		<LI><B>RESULT</B>: '<?= $out ?>'</LI>
	</UL>
</body>
</html>
<?php
