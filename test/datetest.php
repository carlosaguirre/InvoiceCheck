<?php
$fecha=["2020-10-10"];
$fechaTS=[strtotime($fecha[0])];
$fechaD=[date("D",$fechaTS[0])];
$fechaW=[date("w",$fechaTS[0])];
for($i=0;$i<11;$i++) {
    $fechaTS[$i+1]=strtotime($fecha[$i]."+ 1 days");
    $fecha[$i+1]=date("Y-m-d",$fechaTS[$i+1]);
    $fechaD[$i+1]=date("D",$fechaTS[$i+1]);
    $fechaW[$i+1]=date("w",$fechaTS[$i+1]);
}
echo "<table border='1'><thead><tr><td style='padding: 0px 1px;'>DATE</td>";
foreach ($fecha as $idx => $val) {
    echo "<th style='white-space: nowrap;padding: 0px 3px;'>".$val."</th>";
}
echo "</tr><tr><td>CR/W</td>";
foreach ($fechaD as $idx => $val) {
    echo "<th>".$val."(".$fechaW[$idx].")</th>";
}
echo "</tr></thead><tbody>";
for ($i=1;$i<21;$i++) {
    echo "<tr><th>$i</th>";
    foreach ($fecha as $idx=>$val) {
        $auxFix=($i+$fechaW[$idx])%7;
        if ($auxFix<3) $auxFix+=7;
        $calcFix=$i+9-$auxFix;
        /*
        if($auxFix<1) $calcFix=$i+1;
        else if($auxFix>1) $calcFix=$i+8-$auxFix;
        else $calcFix=$i;
        */
        $timeFix=strtotime($val."+ $calcFix days");
        $dateFix=date("Y-m-d",$timeFix);
        $dayFix=date("D",$timeFix);
        $wkdFix=+date("w",$timeFix);
        /*
        $sumFix=$i;
        $timeFix=strtotime($val."+ $i days");
        $dateFix=date("Y-m-d",$timeFix);
        $dayFix=date("D",$timeFix);
        $wkdFix=+date("w",$timeFix);
        if($wkdFix==0) {
            $sumFix++;
            $timeFix=strtotime($dateFix."+ 1 days");
            $dateFix=date("Y-m-d",$timeFix);
            $dayFix=date("D",$timeFix);
            $wkdFix=+date("w",$timeFix);
        } else if ($wkdFix>1) {
            $numFix=8-$wkdFix;
            $sumFix+=$numFix;
            $timeFix=strtotime($dateFix."+ $numFix days");
            $dateFix=date("Y-m-d",$timeFix);
            $dayFix=date("D",$timeFix);
            $wkdFix=+date("w",$timeFix);
        }
        */
        echo "<td>";
        //if ($sumFix===$calcFix) echo "<span style='background-color:lightgreen;'>";
        //else echo "<span style='background-color:lightred;'>";
        //echo "+$sumFix +$calcFix</span> ";
        echo "$dateFix $dayFix($wkdFix)</td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";
//$fecha=date("Y-m-d H:i:s");
//$fechaTS=strtotime($fecha); //."+ 1 days");
//$fecha=date("Y-m-d H:i:s",$fechaTS);
//$prefix=date("D(w)",$fechaTS);
//echo "<p>DATETIME = ".$prefix." ".$fecha."</p>";
//$wkd=+date("w",$fechaTS);
//echo "<p>W = ".$wkd."</p>";
/*
$credito=[5,6,7,8,9,12,13,14,15,16];
$modCredito0=[];
//$modCredito=[];
$creditoFix0=[];
$creditoFix1=[];
$creditoFix2=[];
//if ($wkd<2) $wkd+=6; else $wkd--;
if ($wkd<1) $wkd1=$wkd+6; else $wkd1=$wkd-1;
if ($wkd<2) $wkd2=$wkd+6; else $wkd2=$wkd-1;
echo "<p>WKD=$wkd. WKD1=$wkd1. WKD2=$wkd2</p><table border='1'><thead><tr><th>CREDITO</th><th>FIX</th><th>DATE</th></tr></thead><tbody>";
foreach ($credito as $idx => $val) {
    if ($wkd1)
	$modCredito0[$idx]=(($credito[$idx]-1)%7)+1;
    $creditoFix0[$idx]=$credito[$idx]-$modCredito0[$idx]+7-$wkd1;
    //$modCredito[$idx]=($credito[$idx]%7);
	//$creditoFix1[$idx]=$credito[$idx]-$modCredito[$idx]+7-$wkd1;
	//$creditoFix2[$idx]=$credito[$idx]-$modCredito[$idx]+7-$wkd2;
    //echo "<th>$val</th>";
    $venceTS=strtotime($fecha."+ ".$creditoFix0[$idx]." days");
    $fechaVencimiento=date("Y-m-d",$venceTS);
    echo "<tr><td>$val</td><td>".$creditoFix0[$idx]."</td><td>".$fechaVencimiento."</td></tr>";
}
*/
/*
echo "</tr></thead><tbody><tr><th>FIX0</th>";
foreach($creditoFix0 as $val) {
    echo "<td>$val</td>";
}*/
/*echo "</tr><tr><th>FIX1</th>";
foreach($creditoFix1 as $val) {
    echo "<td>$val</td>";
}
echo "</tr><tr><th>FIX2</th>";
foreach($creditoFix2 as $val) {
    echo "<td>$val</td>";
}*/

//echo "</tbody></table>";

//echo "<p>W Fix = ".$wkd."</p>";
//$modCredito=$credito%7;
//$creditoFix=$credito-($credito%7)+7-$wkd;
//echo "<p>Credito = ".$credito."</p>";
//echo "<p>Credito Fix = ".$creditoFix."</p>";
