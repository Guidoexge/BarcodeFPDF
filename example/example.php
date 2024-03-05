<?php

require_once '\htdocs\wwwatb\vendor\autoload.php';

$pdf = new Barcode_FPDF ('L','mm','A5');

	$pdf->AddPage();
	$pdf->SetAutoPageBreak(0,1);
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(17,2,"Code",0,0,'L');
	$codice_barre = "Testo da codificare" ;
	$filename = 'd:\htdocs\wwwatb\samplepdfs\prova.pdf';
//  code39($x, $y, $code, $width=0.4, $height = 20.0, $isWide = false, $extended = true, $needChecksum = false, $displayText = false)
	$x = 2;
	$y = 18;
	$width = 0.1;
	$height = 5.0 ;
	$fontData = array('Arial','',4);
 	$pdf->code39($x, $y,$codice_barre,$width,$height,false,false,false,true, $fontData , "R", "L");
	$x = 45;
	$y = 18;	
 	$pdf->code39($x, $y,$codice_barre,$width,$height,false,false,false,true, $fontData , "R", "P");

	$pdf->SetFont('Arial', '', 5);
	$pdf->SetXY(8,24);


//	$pdf->Cell(0,0,$codice_barre,0,2,'C');
 	$pdf->TextWithRotation(35,15,'Made in ITALY','U',0);
  	$pdf->TextWithRotation(60,25,'Made in ITALY',75,25);
 	$pdf->TextWithRotation(80,45,'Made in ITALY','D',0);
	$pdf->Output($filename,'F', true);

?>