<?php
//require_once dirname(__DIR__)."/bootstrap.php";
//require_once "clases/BasePDF.php";
require_once "fpdf182/fpdf.php";

class CustomPDF extends FPDF {
    //public $milog=[];
    public function outlineText($x, $y, $outColor, $outLnWid, $txt) { // tachar texto
        //$this->milog[]="INI outlineText";
        if (!is_array($outColor)) $outColor=[$outColor];
        $this->SetDrawColor(...$outColor);
        //$this->milog[]="setDrawColor (".implode(",", $outColor).")";
        $this->SetLineWidth($outLnWid);
        //$this->milog[]="setLineWidth $outLnWid";
        $this->ClippingText($x,$y,$txt,true);
        //$this->milog[]="clippingText $x $y '$txt'";
    }
    public function ClippingText($x, $y, $txt, $outline=false) { // 
        $op= $outline ? 5 : 7;
        $this->_out(sprintf('q BT %.2F %.2F Td %d Tr (%s) Tj ET',
            $x*$this->k,
            ($this->h-$y)*$this->k,
            $op,
            $this->_escape($txt)));
    }
    function UnsetClipping() {
        $this->_out('Q');
    }
}

$pdf=new CustomPDF();
//$pdf->SetFont('Arial','',300);
$pdf->SetFont('Arial','B',400);
//set the outline color
//set the outline width (note that only its outer half will be shown)
for ($i=0; $i < 12; $i++) { 
    $pdf->AddPage();
    //$pdf->milog[]="PAGE ".($i+1);
    //$pdf->Cell(0,266,utf8_decode((""+($i+1))),0,0,'C');
    //$pdf->SetDrawColor(250,250,250);
    //$pdf->SetLineWidth(3);
    //$pdf->strokeText($i<10?95:90, 170, (""+($i+1)), true);
    //$pdf->outlineText($i<10?95:90, 170, [46, 52, 120], [186, 236, 253], 2, 0.5, (""+($i+1)));
    $pdf->outlineText($i<9?65:25, 195, [0, 0, 100], 0.1, (""+($i+1)));

    //draw the clipping text
    //$pdf->ClippingText(40,55,utf8_decode((""+($i+1))),true);
    //fill it with the image
    //$pdf->Image('clips.jpg',40,10,130);
    //remove the clipping
    $pdf->UnsetClipping();
}
/*
$pdf->AddPage();
$pdf->SetFont('Arial','',14);
$pdf->Write(5,'LOG:');
$pdf->Ln();
$pdf->SetFont('Arial','',12);
foreach ($pdf->milog as $idx => $text) {
    $pdf->Write(5,$text);
    $pdf->Ln();
}
*/
$pdf->Output();
