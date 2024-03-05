<?php

require_once __DIR__ . '/../vendor/autoload.php';

$pdf = new Barcode_FPDF ('L','mm',array(25,44));

	$pdf->AddPage();
	$pdf->SetAutoPageBreak(0,1);
	$pdf->SetFont('Arial', 'B', $carattere_testo_fisso);
	$pdf->Cell(17,2,"Sit Code",0,0,'L');
//    public function code39($x, $y, $code, $width=0.4, $height = 20.0, $isWide = false, $extended = true, $needChecksum = false, $displayText = false)
	$x = 2;
	$y = 18;
	$width = 0.1;
	$height = 5.0 ;
	$fontData = array('Arial','',2);
 	$pdf->code39($x, $y,$codice_barre,$width,$height,false,false,false,true, $fontData , "R", "L");

	$pdf->SetFont('Arial', '', 5);
	$pdf->SetXY(8,24);


//	$pdf->Cell(0,0,$codice_barre,0,2,'C');
 	$pdf->TextWithRotation(40,15,'Made in ITALY','U',0);
 	$pdf->Output($filename,'F', true);

?>