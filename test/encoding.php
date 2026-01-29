<?php
/*
$text = "A strange string to pass, maybe with some ø, æ, å characters.";
foreach(mb_list_encodings() as $chr){
        echo mb_convert_encoding($text, 'UTF-8', $chr)." : ".$chr."<br>";   
}
*/
echo "<html><body>\n";
$text = "Html Entities wanted to expand: á é í ó ú ñ Á É Í Ó Ú Ñ ° ä â à M&aacute;s <b>BOLD</b><br>\n";
echo "Manual Test = a á &aacute; - ".htmlentities("a á &aacute;")."<br>\n";
echo "Normal Text = <u>$text</u><br>\n";
echo "Converted Text = <u>".htmlentities($text)."</u><br>\n";
$dtext = html_entity_decode($text);
echo "Decoded Text = <u>".htmlentities($dtext)."</u><br>\n";
echo "</body></html>";

// obtener texto de la base y desplegarlo en pantalla.
// en campos editables agregar el texto con html_entity_decode
// al guardar a la base de nuevo aplicar htmlentities
