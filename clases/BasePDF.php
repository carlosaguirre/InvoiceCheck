<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\FpdiException;
require_once "fpdf182/fpdf.php";
require_once "fpdi236/src/autoload.php";

class BasePDF extends Fpdi {
    // ALPHA OPACITY VARIABLES
    protected $extgstates = array();
    // ROTATE VARIABLES
    var $angle=0;
    // WRITEHTML VARIABLES
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';
    // SETVISIBILITY VARIABLES
    protected $visibility = 'all';
    protected $n_ocg_print;
    protected $n_ocg_view;
    // ALPHA OPACITY METHODS
    // alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    function SetAlpha($alpha, $bm='Normal') {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }
    function AddExtGState($parms) {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }
    function SetExtGState($gs) {
        $this->_out(sprintf('/GS%d gs', $gs));
    }
    // ROTATE METHODS
    function Rotate($angle,$x=-1,$y=-1) { // indicar angulo de giro, en sentido opuesto a las manecillas del reloj
        if ($x==-1) $x=$this->x;
        if ($y==-1) $y=$this->y;
        if ($this->angle!=0) $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0) {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }
    // WRITEHTML METHODS
    function WriteHTML($html, $hgt=5) { // HTML parser
        $html = str_replace("\n",' ',$html);
        $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e) {
            if($i%2==0) { // Text
                if($this->HREF) $this->PutLink($this->HREF,$e,$hgt);
                else $this->Write($hgt,$e);
            } else { // Tag
                if($e[0]=='/') $this->CloseTag(strtoupper(substr($e,1)));
                else { // Extract attributes
                    $a2 = explode(' ',$e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach($a2 as $v) {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag,$attr,$hgt);
                }
            }
        }
    }
    function OpenTag($tag, $attr, $hgt=5) { // Opening tag
        if($tag=='B' || $tag=='I' || $tag=='U') $this->SetStyle($tag,true);
        if($tag=='A') $this->HREF = $attr['HREF'];
        if($tag=='BR') $this->Ln($hgt);
    }
    function CloseTag($tag) { // Closing tag
        if($tag=='B' || $tag=='I' || $tag=='U') $this->SetStyle($tag,false);
        if($tag=='A') $this->HREF = '';
    }
    function SetStyle($tag, $enable) { // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach(array('B', 'I', 'U') as $s) { if($this->$s>0) $style .= $s; }
        $this->SetFont('',$style);
    }
    function PutLink($URL, $txt, $hgt=5) { // Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        if (ctype_digit(strval($URL))) $URL=(int)$URL;
        doclog("PutLink","basepdf",["url"=>$URL,"txt"=>$txt,"urlType"=>gettype($URL)]);
        $this->Write($hgt,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
    // SETVISIBILITY METHODS
    function SetVisibility($v) {
        if($this->visibility!='all') $this->_out('EMC');
        if($v=='print') $this->_out('/OC /OC1 BDC');
        elseif($v=='screen') $this->_out('/OC /OC2 BDC');
        elseif($v!='all') $this->Error('Incorrect visibility: '.$v);
        $this->visibility = $v;
    }
    // DOWNGRADE FIX TO version 1.4 TO AVOID CrossReferenceException
    public static function downgradeVersion($srcFileName,$tgtFileName,$logFileName=null) {
        $basecmd="gswin64 -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"".$tgtFileName."\" \"".$srcFileName."\"";
        //if (isset($logFileName[0])) $logFileName="\"$logFileName\"";
        //else $logFileName="/dev/null";
        //$cmd="$basecmd 2> $logFileName";
        $cmd2="$basecmd 2>&1";
        $lastLine=system($cmd2,$result);
        if (isset($result[0])) {
            return $result;
        }
        return "";
    }
    // STROKE TEXT METHOD
    public function strokeText($x, $y, $txt, $outline=true) { // tachar texto
        $this->SetTextColor(46, 52, 120);
        $this->Text($x,$y,$txt);
        $this->SetDrawColor(186, 236, 253);
        $this->SetLineWidth(0.5);
        $this->ClippingText($x,$y,$txt,$outline);
    }
    // ROUNDED BORDERS METHODS
    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F') $op='f';
        else if($style=='FD' || $style=='DF') $op='B';
        else $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }
    // CLIPPING METHODS
    public function ClippingText($x, $y, $txt, $outline=false) { // 
        $op= $outline ? 5 : 7;
        $this->_out(sprintf('q BT %.2F %.2F Td %d Tr (%s) Tj ET',
            $x*$this->k,
            ($this->h-$y)*$this->k,
            $op,
            $this->_escape($txt)));
    }
    function ClippingRect($x, $y, $w, $h, $outline=false) {
        $op=$outline ? 'S' : 'n';
        $this->_out(sprintf('q %.2F %.2F %.2F %.2F re W %s',
            $x*$this->k,
            ($this->h-$y)*$this->k,
            $w*$this->k,-$h*$this->k,
            $op));
    }
    function ClippingRoundedRect($x, $y, $w, $h, $r, $outline=false) {
        $k = $this->k;
        $hp = $this->h;
        $op=$outline ? 'S' : 'n';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('q %.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out(' W '.$op);
    }
    function ClippingEllipse($x, $y, $rx, $ry, $outline=false) {
        $op=$outline ? 'S' : 'n';
        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('q %.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c W %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }
    function ClippingCircle($x, $y, $r, $outline=false) {
        $this->ClippingEllipse($x, $y, $r, $r, $outline);
    }
    function ClippingPolygon($points, $outline=false) {
        $op=$outline ? 'S' : 'n';
        $h = $this->h;
        $k = $this->k;
        $points_string = '';
        for ($i=0; $i<count($points); $i+=2) {
            $points_string .= sprintf('%.2F %.2F', $points[$i]*$k, ($h-$points[$i+1])*$k);
            if ($i==0) $points_string .= ' m ';
            else $points_string .= ' l ';
        }
        $this->_out('q '.$points_string . 'h W '.$op);
    }
    function UnsetClipping() {
        $this->_out('Q');
    }
    function ClippedCell ($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        if ($border || $fill || $this->y+$h>$this->PageBreakTrigger) {
            $this->Cell($w,$h,'',$border,0,'',$fill);
            $this->x-=$w;
        }
        $this->ClippingRect($this->x,$this->y,$w,$h);
        $this->Cell($w,$h,$txt,'',$ln,$align,false,$link);
        $this->UnsetClipping();
    }

    // ALPHA OPACITY META
    function _putextgstates() {
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_put('<</Type /ExtGState');
            $parms = $this->extgstates[$i]['parms'];
            $this->_put(sprintf('/ca %.3F', $parms['ca']));
            $this->_put(sprintf('/CA %.3F', $parms['CA']));
            $this->_put('/BM '.$parms['BM']);
            $this->_put('>>');
            $this->_put('endobj');
        }
    }
    // SETVISIBILITY META
    function _putocg() {
        $this->_newobj();
        $this->n_ocg_print = $this->n;
        $this->_put('<</Type /OCG /Name '.$this->_textstring('print'));
        $this->_put('/Usage <</Print <</PrintState /ON>> /View <</ViewState /OFF>>>>>>');
        $this->_put('endobj');
        $this->_newobj();
        $this->n_ocg_view = $this->n;
        $this->_put('<</Type /OCG /Name '.$this->_textstring('view'));
        $this->_put('/Usage <</Print <</PrintState /OFF>> /View <</ViewState /ON>>>>>>');
        $this->_put('endobj');
    }

    // META OVERWRITE
    function _putresourcedict() {
        parent::_putresourcedict();

        // ALPHA OPACITY FIX
        $this->_put('/ExtGState <<');
        foreach($this->extgstates as $k=>$extgstate)
            $this->_put('/GS'.$k.' '.$extgstate['n'].' 0 R');
        $this->_put('>>');
        // SETVISIBILITY FIX
        $this->_put('/Properties <</OC1 '.$this->n_ocg_print.' 0 R /OC2 '.$this->n_ocg_view.' 0 R>>');
    }
    function _putresources() {
        // ALPHA OPACITY FIX
        $this->_putextgstates();
        // SETVISIBILITY FIX
        $this->_putocg();

        parent::_putresources();
    }
    function _putcatalog() {
        parent::_putcatalog();

        // SETVISIBILITY FIX
        $p = $this->n_ocg_print.' 0 R';
        $v = $this->n_ocg_view.' 0 R';
        $as = "<</Event /Print /OCGs [$p $v] /Category [/Print]>> <</Event /View /OCGs [$p $v] /Category [/View]>>";
        $this->_put("/OCProperties <</OCGs [$p $v] /D <</ON [$p] /OFF [$v] /AS [$as]>>>>");
    }
    function _endpage() {
        // ROTATE FIX
        if ($this->angle!=0) {
            $this->angle=0;
            $this->_out('Q');
        }
        // SETVISIBILITY FIX
        $this->SetVisibility('all');

        parent::_endpage();
    }
    function _enddoc() {
        // ALPHA OPACITY FIX
        if(!empty($this->extgstates) && $this->PDFVersion<'1.4') $this->PDFVersion='1.4';
        // SETVISIBILITY FIX
        if($this->visibility!='all' && $this->PDFVersion<'1.5') $this->PDFVersion = '1.5';

        parent::_enddoc();
    }

    // SAVE TO NEW PDF FILE
    function saveFile($newName) {
        try {
            $this->Output('F',$newName,true);
            doclog("BasePDF.saveFile","pdf",["newName"=>$newName]);
            return true;
        } catch (Exception $ex) {
            doclog("BasePDF.saveFile","error",["newName"=>$newName,"error"=>getErrorData($ex)]);
            return false;
        }
    }
    function __destruct() {
    }
}
