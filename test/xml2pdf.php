<?php
require_once dirname(__DIR__)."/bootstrap.php";
?>
<html>
    <head>
        <title>XML TO PDF</title>
        <base href="http://invoicecheck.dyndns-web.com:81/invoice/">
        <meta charset="utf-8">
        <script src="scripts/general.js?ver=1.0.0"></script>
        <link href="css/general.php" rel="stylesheet" type="text/css">
    </head>
    <body>
        <H1>Generador de formato visual CFD</H1>
        <form name="forma1" method="post" action="test/pdf4xml.php" target="pdfResult" enctype="multipart/form-data">
            <input id="ftag" name="archivo" type="file" accept=".xml"<?php /* onchange="this.parentNode.submit();setTimeout(function(){ebyid('ftag').value='';},10);" */ ?>><br>
            Seleccionar versi&oacute;n a generar: <input type="submit" name="version" value="BASICA"<?php /*  onclick="setTimeout(function(){ebyid('ftag').value='';},10);"*/ ?>>
            <input type="submit" name="version" value="PG"<?php /*  onclick="setTimeout(function(){ebyid('ftag').value='';},10);"*/ ?>>
        </form>
    </body>
</html>
