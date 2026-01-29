<?php
require_once "fpdf182/fpdf.php";

$pdf=new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,utf8_decode('Trampa Opinión de Cumplimiento'),0,0,'C');

$pdf->SetFont('Helvetica','B',12);
$pdf->SetTextColor(0,0,180);
$pdf->SetXY(20,30);
$pdf->Write(12, 'Pages: 1');
$pdf->SetXY(20,50);
$pdf->Write(12, utf8_decode('Revisión practicada el día 3 de marzo de 2021, a las 12:34 horas.'));
$pdf->SetXY(20,70);
$pdf->Write(12, utf8_decode('Encontramos que su situación fiscal se encuentra al corriente.'));
$pdf->Output();
