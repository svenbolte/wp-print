<?php
include(plugin_dir_path( __FILE__ ) . 'fpdf.php');
/**
 * Modified from http://www.fpdf.org/en/script/script42.php
 */

//function hex2dec
//returns an associative array (keys: R,G,B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($color = "#000000") {
    $R = substr($color, 1, 2);
    $rouge = hexdec($R);
    $V = substr($color, 3, 2);
    $vert = hexdec($V);
    $B = substr($color, 5, 2);
    $bleu = hexdec($B);
    $tbl_color = array();
    $tbl_color['R']=$red;
    $tbl_color['G']=$green;
    $tbl_color['B']=$blue;
    return $tbl_color;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html) {
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}
////////////////////////////////////
class PDF_HTML extends FPDF
{
//variables of html parser
protected $B;
protected $I;
protected $U;
protected $HREF;
protected $fontList;
protected $issetfont;
protected $issetcolor;

function __construct($orientation='P', $unit='mm', $format='A4') {
    //Call parent constructor
    parent::__construct($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
    $this->fontlist=array('arial', 'times', 'courier', 'helvetica', 'symbol');
    $this->issetfont=false;
    $this->issetcolor=false;
}

  public function floatingImage($imgPath, $height) {
        list($w, $h) = getimagesize($imgPath);
        $ratio = $w / $h;
        $imgWidth = $height * $ratio;

        $this->Image($imgPath, $this->GetX(), $this->GetY());
        $this->x += $imgWidth;
    }

// Kopfzeile
  function Header()
  {
		$blogtitle = get_bloginfo('name');
		$blogdesc = get_bloginfo('description');
        $this->SetTitle ($blogtitle . ' aktuell');
		$this->SetFont( 'Arial', '', 9 );
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$logo = wp_get_attachment_url( $custom_logo_id );
		if ( isset($custom_logo_id) && !empty($custom_logo_id) ) {
			$this->Cell( 40, 40, $this->InlineImage($logo, $this->GetX(), $this->GetY(), 40), 0, 0, 'L', false );
			$this->Ln(5);
			$this->WriteHTML(utf8_decode($blogtitle . '   ' . $blogdesc ));
		} else {
			$this->SetFont( 'Arial', '', 20 );
			$this->WriteHTML(utf8_decode($blogtitle));
			$this->Ln(5);
			$this->SetFont( 'Arial', '', 9 );
			$this->WriteHTML(utf8_decode($blogdesc));
		}	
        $this->Ln(5);
		$this->SetLineWidth(0.1);
		$this->Line(00,$this->GetY(),218,$this->GetY());		
  }
  
// Fusszeile
  function Footer() {
    $this->AliasNbPages();
	// Position 1,5 cm von unten
    $this->SetY(-15);
    // Arial kursiv 8
    $this->SetFont('Arial','B',10);
    // Seitenzahl
    $this->Cell(0,10,'Seite '.$this->PageNo().' von {nb}',0,0,'C');
  }

   // Inline Image
    function InlineImage($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='') {
        // ----- Code from FPDF->Image() -----
        // Put an image on the page
        if($file=='')
            $this->Error('Image file name is empty');
        if(!isset($this->images[$file]))
        {
            // First use of this image, get info
            if($type=='')
            {
                $pos = strrpos($file,'.');
                if(!$pos)
                    $this->Error('Image file has no extension and no type was specified: '.$file);
                $type = substr($file,$pos+1);
            }
            $type = strtolower($type);
            if($type=='jpeg')
                $type = 'jpg';
            $mtd = '_parse'.$type;
            if(!method_exists($this,$mtd))
                $this->Error('Unsupported image type: '.$type);
            $info = $this->$mtd($file);
            $info['i'] = count($this->images)+1;
            $this->images[$file] = $info;
        }
        else
            $info = $this->images[$file];

        // Automatic width and height calculation if needed
        if($w==0 && $h==0)
        {
            // Put image at 96 dpi
            $w = -96;
            $h = -96;
        }
        if($w<0)
            $w = -$info['w']*72/$w/$this->k;
        if($h<0)
            $h = -$info['h']*72/$h/$this->k;
        if($w==0)
            $w = $h*$info['w']/$info['h'];
        if($h==0)
            $h = $w*$info['h']/$info['w'];

        // Flowing mode
        if($y===null)
        {
            if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
            {
                // Automatic page break
                $x2 = $this->x;
                $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
                $this->x = $x2;
            }
            $y = $this->y;
            $this->y += $h;
        }

        if($x===null)
            $x = $this->x;
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
        if($link)
            $this->Link($x,$y,$w,$h,$link);
        # -----------------------

        // Update Y
        $this->y += $h;
    }


function WriteHTML($html) {
    // HTML parser   <img> images werden rausgefiltert, erscheinen wenn der tag hier drunter rein kommt
	// Shortcodes werden gefiltert
    $html=strip_tags($html,"<b><u><i><a><p><strong><script><style><em><font><tr><blockquote>");
	$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
	$html = preg_replace('#<style>(.*?)</style>#is', '', $html);
	$html=str_replace("\n",' ',$html);
    $html=preg_replace('/\[[^\[]*[^\]]*\]/U', ' ', $html);
	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,stripslashes(txtentities($e)));
        }
        else
        {
            //Tag
            if($e[0]=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                //Extract attributes
                $a2=explode(' ',$e);
                $tag=strtoupper(array_shift($a2));
                $attr=array();
                foreach($a2 as $v)
                {
                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                }
                $this->OpenTag($tag,$attr);
            }
        }
    }
}

function OpenTag($tag, $attr) {
    //Opening tag
    switch($tag){
        case 'STRONG':
            $this->SetStyle('B',true);
            break;
        case 'EM':
            $this->SetStyle('I',true);
            break;
        case 'B':
        case 'I':
        case 'U':
            $this->SetStyle($tag,true);
            break;
        case 'A':
            @$this->HREF=$attr['HREF'];
            break;
        case 'IMG':
            if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                if(!isset($attr['WIDTH']))
                    $attr['WIDTH'] = 0;
                if(!isset($attr['HEIGHT']))
                    $attr['HEIGHT'] = 0;
                $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
            }
            break;
        case 'TR':
        case 'BLOCKQUOTE':
        case 'BR':
            $this->Ln(5);
            break;
        case 'P':
            $this->Ln(10);
            break;
        case 'FONT':
            if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                $colour=hex2dec($attr['COLOR']);
                $this->SetTextColor($colour['R'],$colour['G'],$colour['B']);
                $this->issetcolor=true;
            }
            if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                $this->SetFont(strtolower($attr['FACE']));
                $this->issetfont=true;
            }
            break;
    }
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='STRONG')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
    if($tag=='FONT'){
        if ($this->issetcolor==true) {
            $this->SetTextColor(0);
        }
        if ($this->issetfont) {
            $this->SetFont('arial');
            $this->issetfont=false;
        }
    }
}

function SetStyle($tag, $enable) {
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B','I','U') as $s)
    {
        if($this->$s>0)
            $style.=$s;
    }
    $this->SetFont('',$style);
}

function PutLink($URL, $txt) {
    //Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}

}//end of class