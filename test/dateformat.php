<?php
echo "<meta charset='utf-8'>\n";
//echo "".date("Y-m-d H:i:s");

echo "<p>".date()."</p>";

/*
$miFecha= gmmktime(12,0,0,1,15,2089);
echo $miFecha;
echo "<TABLE><THEAD><TR><TH>SETLOCALE</TH><TH>DATE</TH><TH>STRFTIME</TH><TH>ICONV</TH></TR></THEAD><TBODY>";
echo "<TR><TD>-</TD><TD>".date("l, d-m-Y (H:i:s)", $miFecha)."</TD><TD>".strftime("%A, %d de %B de %Y", $miFecha)."</TD></TR>";
*/
$code=["es_MX.UTF-8","es_MX","esl"];
setlocale(LC_TIME,$code[0],$code[1],$code[2]);
echo "<p>".iconv('ISO-8859-2', 'UTF-8',strftime("%A, %d de %B de %Y"))."</p>";
echo "<p>".strftime("%e %b, %Y")."</p>";

$L = new DateTime( '2020-02-01' ); 
echo "<p>".$L->format( 'Y-m-t' )."</p>";

$dia = str_pad(date("j"),2,"0",STR_PAD_LEFT);
$maxdia = str_pad(date("t"),2,"0",STR_PAD_LEFT);
$mes = str_pad(date("n"),2,"0",STR_PAD_LEFT);
$anio = date("Y");
$esteMes = "/{$mes}/$anio";
$primerDia = "01{$esteMes}";
$fechaHoy = "{$dia}{$esteMes}";
$ultimoDia = "{$maxdia}{$esteMes}";
echo "<p>$primerDia - $fechaHoy - $ultimoDia</p>";

/*
echo "<TR><TD>".implode(",", $code)."</TD><TD>".date("l, d-m-Y (H:i:s)", $miFecha)."</TD><TD>".utf8_decode(strftime("%A, %d de %B de %Y", $miFecha))."</TD><TD>".iconv('ISO-8859-2', 'UTF-8',strftime("%A, %d de %B de %Y", $miFecha))."</TD></TR>";

$code="deu";
setlocale(LC_TIME,$code);
echo "<TR><TD>$code</TD><TD>".date("l, d-m-Y (H:i:s)", $miFecha)."</TD><TD>".utf8_decode(strftime("%A, %d de %B de %Y", $miFecha))."</TD><TD>".iconv('ISO-8859-2', 'UTF-8',strftime("%A, %d de %B de %Y", $miFecha))."</TD></TR>";

$code1="fr_FR.utf8";
$code2="fra";
setlocale(LC_TIME,$code1,$code2);
echo "<TR><TD>$code1, $code2</TD><TD>".date("l, d-m-Y (H:i:s)", $miFecha)."</TD><TD>".utf8_decode(strftime("%A, %d de %B de %Y", $miFecha))."</TD><TD>".iconv('ISO-8859-2', 'UTF-8',strftime("%A, %d de %B de %Y", $miFecha))."</TD></TR>";

echo "</TBODY></TABLE>";
*/
die();
if (isset($_POST["campo"][0])) {
    $campo=$_POST["campo"];
    echo "<!-- CAMPO = $campo -->\n";
    $val = strtotime($campo);
    if ($val===false) {
        echo "<!-- STRTOTIME ERROR -->\n";
    } else {
        echo "<!-- STRTOTIME VAL=$val -->\n";
        try {
            $fecha = new DateTime($campo);
            echo "<!-- CREATED ".$fecha->format("Y-m-d")." -->\n";
        } catch (Exception $e) {
            echo "<!-- EXCEPTION -->\n";
            echo "<!-- ".$e->getMessage()." -->\n";
            $fecha=NULL;
        }
    }
} else echo "<!-- NOT FOUND campo -->";
echo "<!-- SHOWING -->\n";
?>
<html>
<body>
<form method="POST">
<input type="text" name="campo" autofocus<?= (isset($fecha)&&$fecha!==FALSE)?" value=\"$campo\"":"" ?>>
</form>
<?php
    if (isset($fecha) && $fecha!==FALSE) {
        echo "<BR>Preparando...";
        echo "<BR>".$fecha->format("l, d-M-Y H:i:s T");
    } else echo "<BR>NO HAY FECHA";
    if (isset($error[0]))
        echo "<BR><PRE>ERROR: $error</PRE>";

?>
</body>
</html>
