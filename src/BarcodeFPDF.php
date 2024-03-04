<?php

namespace Guidoexge\BarcodeFPDF;

use \FPDF;

class Barcode_FPDF extends FPDF
{
    /** @var array */
    protected $barcodes;

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->barcodes = array();
        parent::__construct($orientation, $unit, $size);
    }

    /*
     * @param float  $x         		initial x position
     * @param float  $y					initial y position
     * @param string $code				code to print (without * at the ends)
     * @param bool   $extendedEncoding
     * @param bool   $addChecksum 		add Checksum
     * @param float  $width				wide of the single bar
     * @param float  $height			height of the bars
     * @param bool   $wide				add double 0
     * @param bool   $displayText		Display text code
     * @param array  $fontData 			font paramenter for the text code array('Arial','',4);
     * @param char   $textDirection 	direction of the text code (R for right, L for left, U to up, D to down)
  	 * @param char   $codeOrientation	Orientatio of the codebar (L landscape. P portrait)
     */

    public function code39($x, $y, $code, $width=0.4, $height = 20.0, $isWide = false, $extended = true, $needChecksum = false, $displayText = false, $fontData = array('Arial','',8), $textDirection = "R" , $codeOrientationation = "L")
    {
        if ($displayText) {
            //Display code
            $this->fpdf->SetFont($fontData[0], $fontData[1], $fontData[2]);
           $this->fpdf->TextWithDirection($x, $y+$height+1.5 , $code, $textDirection );
        }

        //Conversion tables
        if ($wide) {
            $this->initWide();
        } else {
            $this->initNarrow();
        }

        if ($extendedEncoding) {
            //Extended encoding
            $code = $this->encode_code39_ext($code);
        } else {
            //Convert to upper case
            $code = strtoupper($code);
            //Check validity
            if (!preg_match('|^[0-9A-Z. $/+%-]*$|', $code)) {
                $this->fpdf->Error('Invalid barcode value: '.$code);
            }
        }

        //Compute checksum
        if ($addChecksum) {
            $code .= $this->checksum_code39($code);
        }

        //Add start and stop characters
        $code = '*'.$code.'*';

        //Inter-character spacing
        $gap = ($width > 0.29) ? '00' : '0';

        //Convert to bars
        $encode = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $encode .= $this->chars[$code[$i]].$gap;
        }

        //Draw bars
        $this->draw_code39($encode, $x, $y, $width, $height, $codeOrientation);
    }

    private function initNarrow()
    {
        $this->chars = array(
            '0' => '101001101101', '1' => '110100101011', '2' => '101100101011', '3' => '110110010101', '4' => '101001101011', '5' => '110100110101',
            '6' => '101100110101', '7' => '101001011011', '8' => '110100101101', '9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
            'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101', 'F' => '101101100101', 'G' => '101010011011', 'H' => '110101001101',
			'I' => '101101001101', 'J' => '101011001101', 'K' => '110101010011', 'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011', 'R' => '110101011001', 'S' => '101101011001', 'T' => '101011011001',
			'U' => '110010101011', 'V' => '100110101011', 'W' => '110011010101', 'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101', '*' => '100101101101', '$' => '100100100101', '/' => '100100101001',
			'+' => '100101001001', '%' => '101001001001',
        );
    }

    private function initWide()
    {
        $this->chars = array(
            '0' => '101000111011101', '1' => '111010001010111', '2' => '101110001010111', '3' => '111011100010101', '4' => '101000111010111',
            '5' => '111010001110101', '6' => '101110001110101', '7' => '101000101110111', '8' => '111010001011101', '9' => '101110001011101',
            'A' => '111010100010111', 'B' => '101110100010111', 'C' => '111011101000101', 'D' => '101011100010111', 'E' => '111010111000101',
            'F' => '101110111000101', 'G' => '101010001110111', 'H' => '111010100011101', 'I' => '101110100011101', 'J' => '101011100011101',
            'K' => '111010101000111', 'L' => '101110101000111', 'M' => '111011101010001', 'N' => '101011101000111', 'O' => '111010111010001',
            'P' => '101110111010001', 'Q' => '101010111000111', 'R' => '111010101110001', 'S' => '101110101110001', 'T' => '101011101110001',
            'U' => '111000101010111', 'V' => '100011101010111', 'W' => '111000111010101', 'X' => '100010111010111', 'Y' => '111000101110101',
            'Z' => '100011101110101', '-' => '100010101110111', '.' => '111000101011101', ' ' => '100011101011101', '*' => '100010111011101',
            '$' => '100010001000101', '/' => '100010001010001',
            '+' => '100010100010001', '%' => '101000100010001',
        );
    }

    private function checksum_code39($code)
    {
        //Compute the modulo 43 checksum
        $chars = array_keys($this->chars);
        $sum = 0;
        for ($i = 0; $i < strlen($code); $i++) {
            $a = array_keys($chars, $code[$i]);
            $sum += $a[0];
        }
        $r = $sum % 43;

        return $chars[$r];
    }

    private function encode_code39_ext($code)
    {
        //Encode characters in extended mode
        $encode = array( chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C', chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
        chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => 'Â£K', chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O', chr(16) => '$P',
   		chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S', chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W', chr(24) => '$X', chr(25) => '$Y',
   		chr(26) => '$Z', chr(27) => '%A', chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E', chr(32) => ' ', chr(33) => '/A', chr(34) => '/B',
   		chr(35) => '/C', chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G', chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
   		chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O', chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3', chr(52) => '4',
   		chr(53) => '5', chr(54) => '6', chr(55) => '7', chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F', chr(60) => '%G', chr(61) => '%H',
   		chr(62) => '%I', chr(63) => '%J', chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C', chr(68) => 'D', chr(69) => 'E', chr(70) => 'F',
   		chr(71) => 'G', chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K', chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
   		chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S', chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W', chr(88) => 'X',
   		chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K', chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O', chr(96) => '%W', chr(97) => '+A',
   		chr(98) => '+B', chr(99) => '+C', chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G', chr(104) => '+H', chr(105) => '+I',
   		chr(106) => '+J', chr(107) => '+K', chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O', chr(112) => '+P', chr(113) => '+Q',
   		chr(114) => '+R', chr(115) => '+S', chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W', chr(120) => '+X', chr(121) => '+Y',
   		chr(122) => '+Z', chr(123) => '%P', chr(124) => '%Q', chr(125) => '%R',
		chr(126) => '%S', chr(127) => '%T' );
        $code_ext = '';
        for ($i = 0; $i < strlen($code); $i++) {
            if (ord($code[$i]) > 127) {
                $this->fpdf->Error('Invalid character: '.$code[$i]);
            }
            $code_ext .= $encode[$code[$i]];
        }
        return $code_ext;
    }

    private function draw_code39($code, $x, $y, $w, $h, $codeOrientation )
    {
        //Draw bars
        $flagRid = false;
        $codeLenght = strlen($code);
		if($codeOrientation == "L"){
			$pageWid = $this->fpdf->GetPageWidth();
			if($w<0){
				// Se $w è un numero negativo intero, imposta $r a 2
				if ($w == floor($w)) {
					$r = 2;
				} else {
					// Altrimenti, memorizza i decimali di $w in $r
					$r = $w - floor($w);
				}
				// Assegna la parte intera di $w a $w
				$w = floor($w);
				$wNew = (abs($w) - ($r))/$codeLenght ;
			}else if($w==0){
				$wNew = ($pageWid - ($x*2))/$codeLenght ;
			}else{
				$wNew = $w;
			}
			if($wNew*strlen($code)>=$pageWid - $x){
				$wNew = ($pageWid - ($x+2))/$codeLenght ;
				$flagRid = true;
			}
		}else{
			$pageHei = $this->fpdf->GetPageHeight();
			if($w<0){
				if ($y == floor($w)) {
					$r = 2;
				} else {
					$r = $w - floor($w);
				}
				$w = floor($w);
				$wNew = (abs($w) - ($r))/$codeLenght ;
			}else if($w==0){
				$wNew = ($pageHei - ($y*2))/$codeLenght ;
			}else{
				$wNew = $w;
			}
			if($wNew*strlen($code)>=$pageHei - $y){
				$wNew = ($pageHei - ($y+2))/$codeLenght ;
				$flagRid = true;
			}
		}

		$posAtt = ($codeOrientation == "L")?$x:$y;
        for ($i = 0; $i < $codeLenght; $i++) {
            if ($code[$i] == '1') {
            	if($codeOrientation == "L"){
					// orizzontale
					$this->fpdf->Rect($posAtt , $y, $wNew, $h, 'F');
				}else{
					$this->fpdf->Rect($x , $posAtt, $h, $wNew, 'F');
				}
            }
    		$posAtt += $wNew ;
        }
		if($flagRid){
			if($codeOrientation == "L"){
				// orizzontale
				$this->fpdf->Text($x+($wNew*$codeLenght) , $y , "*");
			}else{
				$this->fpdf->Text($x , $y +($wNew*$codeLenght)+1.5 , "*");
			}
        }
    }
}


	public function TextWithDirection($x, $y, $txt, $textDirection )
		{
		    // Controlla la direzione del testo
		    if ($textDirection=='R')
		        // Se la direzione è 'R' (destra), imposta la trasformazione della matrice di testo per la stampa a destra
		        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		    elseif ($textDirection=='L')
		        // Se la direzione è 'L' (sinistra), imposta la trasformazione della matrice di testo per la stampa a sinistra
		        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		    elseif ($textDirection=='U')
		        // Se la direzione è 'U' (su), imposta la trasformazione della matrice di testo per la stampa verso l'alto
		        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		    elseif ($textDirection=='D')
		        // Se la direzione è 'D' (giù), imposta la trasformazione della matrice di testo per la stampa verso il basso
		        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		    else
		        // Se la direzione non è una delle precedenti, utilizza una trasformazione di default
		        $s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));

		    // Se è impostata la flag del colore del testo
		    if ($this->ColorFlag)
		        // Applica il colore specificato prima del testo
		        $s='q '.$this->TextColor.' '.$s.' Q';

		    // Invia la stringa formattata alla funzione _out della classe
		    $this->_out($s);
	}
	public function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
	{
		if (is_numeric($txt_angle)) {
        	$txt_angle = (float) $txt_angle; // Converti il valore in virgola mobile se è un numero
    	}else if($txt_angle == "R"){
	    	$txt_angle = 0;
	    }else if($txt_angle == "L"){
	    	$txt_angle = -180;
	    }else if($txt_angle == "U"){
	    	$txt_angle = 90;
	    }else if($txt_angle == "D"){
	    	$txt_angle = -90;
	    }else{
	        $txt_angle = 0;
	    }

		// Calcola l'angolo totale del testo sommando l'angolo del testo e l'angolo del font
		$font_angle += 90 + $txt_angle;

		// Converti gli angoli da gradi a radianti
		$txt_angle *= M_PI / 180;
		$font_angle *= M_PI / 180;

		// Calcola i componenti della trasformazione per il testo
		$txt_dx = cos($txt_angle);
		$txt_dy = sin($txt_angle);

		// Calcola i componenti della trasformazione per il font
		$font_dx = cos($font_angle);
		$font_dy = sin($font_angle);

		// Formatta la stringa di testo con la trasformazione applicata
		$s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', $txt_dx, $txt_dy, $font_dx, $font_dy, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));

		// Aggiungi il colore del testo, se necessario
		if ($this->ColorFlag)
			$s = 'q ' . $this->TextColor . ' ' . $s . ' Q';

		// Invia la stringa formattata alla funzione _out della classe
		$this->_out($s);
	}


}