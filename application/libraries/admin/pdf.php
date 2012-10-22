<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id$
 */
# override the default TCPDF config file
if(!defined('K_TCPDF_EXTERNAL_CONFIG')) {
	define('K_TCPDF_EXTERNAL_CONFIG', TRUE);
}

# include TCPDF
require(APPPATH.'config/tcpdf'.EXT);

require_once($tcpdf['base_directory'].'/tcpdf.php');


/**
 * page format
 */
define ('PDF_PAGE_FORMAT', 'A4');

/**
 * page orientation (P=portrait, L=landscape)
 */
define ('PDF_PAGE_ORIENTATION', 'P');

/**
 * document creator
 */
define ('PDF_CREATOR', 'TCPDF');

/**
 * document author
 */
define ('PDF_AUTHOR', 'TCPDF');

/**
 * header title
 */
define ('PDF_HEADER_TITLE', 'TCPDF Example');

/**
 * header description string
 */
define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");

/**
 * image logo
 */
define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');

/**
 * header logo image width [mm]
 */
define ('PDF_HEADER_LOGO_WIDTH', 30);

/**
 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
 */
define ('PDF_UNIT', 'mm');

/**
 * header margin
 */
define ('PDF_MARGIN_HEADER', 5);

/**
 * footer margin
 */
define ('PDF_MARGIN_FOOTER', 10);

/**
 * top margin
 */
define ('PDF_MARGIN_TOP', 27);

/**
 * bottom margin
 */
define ('PDF_MARGIN_BOTTOM', 25);

/**
 * left margin
 */
define ('PDF_MARGIN_LEFT', 15);

/**
 * right margin
 */
define ('PDF_MARGIN_RIGHT', 15);

/**
 * default main font name
 */
define ('PDF_FONT_NAME_MAIN', 'helvetica');

/**
 * default main font size
 */
define ('PDF_FONT_SIZE_MAIN', 10);

/**
 * default data font name
 */
define ('PDF_FONT_NAME_DATA', 'helvetica');

/**
 * default data font size
 */
define ('PDF_FONT_SIZE_DATA', 8);

/**
 * default monospaced font name
 */
define ('PDF_FONT_MONOSPACED', 'courier');

/**
 * ratio used to adjust the conversion of pixels to user units
 */
define ('PDF_IMAGE_SCALE_RATIO', 1.25);

/**
 * magnification factor for titles
 */
define('HEAD_MAGNIFICATION', 1.1);

/**
 * height of cell repect font height
 */
define('K_CELL_HEIGHT_RATIO', 1.25);

/**
 * title magnification respect main font size
 */
define('K_TITLE_MAGNIFICATION', 1.3);

/**
 * reduction factor for small font
 */
define('K_SMALL_RATIO', 2/3);

/**
 * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
 */
define('K_THAI_TOPCHARS', true);

/**
 * if true allows to call TCPDF methods using HTML syntax
 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
 */
define('K_TCPDF_CALLS_IN_HTML', true);

/************************************************************
 * TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 ***********************************************************/
class pdf extends TCPDF {


	/**
	 * TCPDF system constants that map to settings in our config file
	 *
	 * @var array
	 * @access private
	 */
	private $cfg_constant_map = array(
		'K_PATH_MAIN'	=> 'base_directory',
		'K_PATH_URL'	=> 'base_url',
		'K_PATH_FONTS'	=> 'fonts_directory',
		'K_PATH_CACHE'	=> 'cache_directory',
		'K_PATH_IMAGES'	=> 'image_directory',
		'K_BLANK_IMAGE' => 'blank_image',
		'K_SMALL_RATIO'	=> 'small_font_ratio',
	);


	/**
	 * Settings from our APPPATH/config/tcpdf.php file
	 *
	 * @var array
	 * @access private
	 */
	private $_config = array();
	
	/**
     * Set _config for pdf
	 * @access public
     * @param mixed $tcpdf
     * @return
     */
	public function setConfig($tcpdf) {
		$this->_config=$tcpdf;
	}


	/**
	 * Initialize and configure TCPDF with the settings in our config file
	 *
	 */
	function __construct() {

		# load the config file
		require(APPPATH.'config/tcpdf'.EXT);
		$this->_config = $tcpdf;
		unset($tcpdf);



		# set the TCPDF system constants
		foreach($this->cfg_constant_map as $const => $cfgkey) {
			if(!defined($const)) {
				define($const, $this->_config[$cfgkey]);
				#echo sprintf("Defining: %s = %s\n<br />", $const, $this->_config[$cfgkey]);
			}
		}

		# initialize TCPDF
		parent::__construct(
			$this->_config['page_orientation'],
			$this->_config['page_unit'],
			$this->_config['page_format'],
			$this->_config['unicode'],
			$this->_config['encoding'],
			$this->_config['enable_disk_cache']
		);


		# language settings
		if(is_file($this->_config['language_file'])) {
			include($this->_config['language_file']);
			$this->setLanguageArray($l);
			unset($l);
		}

		# margin settings
		$this->SetMargins($this->_config['margin_left'], $this->_config['margin_top'], $this->_config['margin_right']);

		# header settings
		$this->print_header = $this->_config['header_on'];
		#$this->print_header = FALSE;
		$this->setHeaderFont(array($this->_config['header_font'], '', $this->_config['header_font_size']));
		$this->setHeaderMargin($this->_config['header_margin']);
		$this->SetHeaderData();
        //$this->SetHeaderData(
		//	$this->_config['header_logo'],
		//	$this->_config['header_logo_width'],
		//	$this->_config['header_title'],
		//	$this->_config['header_string']
		//);

		# footer settings
		$this->print_footer = $this->_config['footer_on'];
		$this->setFooterFont(array($this->_config['footer_font'], '', $this->_config['footer_font_size']));
		$this->setFooterMargin($this->_config['footer_margin']);

		# page break
		$this->SetAutoPageBreak($this->_config['page_break_auto'], $this->_config['footer_margin']);

		# cell settings
		$this->cMargin = $this->_config['cell_padding'];
		$this->setCellHeightRatio($this->_config['cell_height_ratio']);

		# document properties
		$this->author = $this->_config['author'];
		$this->creator = $this->_config['creator'];

		# font settings
		#$this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);

		# image settings
        $this->setImageScale($this->_config['image_scale']);

	}
    
    /**
        *
        * obsolete
        * @param $text
        * @param $format
        * @return unknown_type
        */
        function intopdf($text,$format='')
        {
            $text = $this->delete_html($text);
            $oldformat = $this->FontStyle;
            $this->SetFont('',$format,$this->FontSizePt);
            $this->Write(5,$text);
            $this->ln(5);
            $this->SetFont('',$oldformat,$this->FontSizePt);
        }
        /**
        *
        * obsolete
        * @param $text
        * @return unknown_type
        */
        function helptextintopdf($text)
        {
            $oldsize = $this->FontSizePt;
            $this->SetFontSize($oldsize-2);
            $this->Write(5,$this->delete_html($text));
            $this->ln(5);
            $this->SetFontSize($oldsize);
        }
        /**
        *
        * writes a big title in the page + description
        * @param $title
        * @param $description
        * @return unknown_type
        */
        function titleintopdf($title,$description='')
        {
            if(!empty($title))
            {
                $title = $this->delete_html($title);
                $oldsize = $this->FontSizePt;
                $this->SetFontSize($oldsize+4);
                $this->Line(5,$this->y,($this->w-5),$this->y);
                $this->ln(3);
                $this->MultiCell('','',$title,'','C',0);
                if(!empty($description) && isset($description))
                {
                    $description = $this->delete_html($description);
                    $this->ln(7);
                    $this->SetFontSize($oldsize+2);
                    $this->MultiCell('','',$description,'','C',0);
                    $this->ln(2);
                }
                else
                {
                    $this->ln(4);
                }
                $this->Line(5,$this->y,($this->w-5),$this->y);
                $this->ln(5);
                $this->SetFontSize($oldsize);
            }
        }
        /**
        *
        * Creates a Table with equal cell width and Bold text. Used as Head for equalTable()
        * @param $array(0=>)
        * @return unknown_type
        */
        function tablehead($array)
        {
            //$maxwidth = array();
            $maxwidth = $this->getEqualWidth($array);
            $oldStyle = $this->FontStyle;
            $this->SetFont($this->FontFamily, 'B', $this->FontSizePt);
            for($a=0;$a<sizeof($array);$a++)
            {
                for($b=0;$b<sizeof($array[$a]);$b++)
                {
                    $this->Cell($maxwidth,4,$this->delete_html($array[$a][$b]),0,0,'L');
                }
                $this->ln();
            }
            $this->ln(5);
            $this->SetFont($this->FontFamily, $oldStyle, $this->FontSizePt);
        }
        /**
        *
        * Creates a Table with equal cell width.
        * @param $array - table array( 0=> array("td", "td", "td"),
        * 								1=> array("td", "td", "td"))
        * @param $modulo - fills each second row with a light-grey for better visibility. Default is on turn off with 0
        * @return unknown_type
        */
        function equalTable($array, $modulo=1)
        {
            //$maxwidth = array();
            $maxwidth = $this->getEqualWidth($array);
            $this->SetFillColor(220, 220, 220);
            for($a=0;$a<sizeof($array);$a++)
            {
                if($modulo){
                    if($a%2 === 0){$fill=0;}
                    else{$fill=1;}
                }
                else{$fill=0;}
                for($b=0;$b<sizeof($array[$a]);$b++)
                {

                    $this->Cell($maxwidth,4,$this->delete_html($array[$a][$b]),0,0,'L',$fill);

                }
                $this->ln();
            }
            $this->ln(5);
        }
        /**
        *
        * creates a table using the full width of page
        * @param $array - table array( 0=> array("td", "td", "td"),
        * 								1=> array("td", "td", "td"))
        * @param $modulo - fills each second row with a light-grey for better visibility. Default is off, turn on with 1
        * @return unknown_type
        */
        function tableintopdf($array, $modulo=1 )
        {
            $maxwidth = array();
            $maxwidth = $this->getFullWidth($array);

            $this->SetFillColor(220, 220, 220);
            for($a=0;$a<sizeof($array);$a++)
            {
                if($modulo){
                    if($a%2 === 0){$fill=0;}
                    else{$fill=1;}
                }
                else{$fill=0;}
                for($b=0;$b<sizeof($array[$a]);$b++)
                {
                    //echo $maxwidth[$b]." max $b.Spalte<br/>";
                    $this->Cell($maxwidth[$b],4,$this->delete_html($array[$a][$b]),0,0,'L',$fill);
                }
                $this->ln();
            }
            $this->ln(5);
        }
        /**
        *
        * creates a table with a bold head using the full width of page
        * @param $head - head array( 0=> array("th", "th", "th"))
        * @param $table - table array( 0=> array("td", "td", "td"),
        * 								1=> array("td", "td", "td"))
        * @param $modulo - fills each second row with a light-grey for better visibility. Default is on, turn off with 0
        * @return unknown_type
        */
        function headTable($head, $table, $modulo=1 )
        {
            $array = array_merge_recursive($head, $table);
            //print_r($array);
            $maxwidth = array();
            $maxwidth = $this->getFullWidth($array);

            $this->SetFillColor(220, 220, 220);
            for($a=0;$a<sizeof($array);$a++)
            {
                if($modulo){
                    if($a%2 === 0){$fill=1;}
                    else{$fill=0;}
                }
                else{$fill=0;}
                for($b=0;$b<sizeof($array[$a]);$b++)
                {
                    $bEndOfCell=0;
                    if ($b==sizeof($array[$a])-1)
                    {
                        $bEndOfCell=1;
                    }

                    if($a==0)
                    {
                        $oldStyle = $this->FontStyle;
                        $this->SetFont($this->FontFamily, 'B', $this->FontSizePt);

                        if ($maxwidth[$b] > 140) $maxwidth[$b]=130;
                        if ($maxwidth[$b] < 20) $maxwidth[$b]=20;
                        $this->MultiCell($maxwidth[$b],6,$this->delete_html($array[$a][$b]),0,'L',1,$bEndOfCell);

                        $this->SetFont($this->FontFamily, $oldStyle, $this->FontSizePt);
                    }
                    else
                    {
                        if ($a==1)
                        {
                            $this->SetFillColor(250, 250, 250);
                        }
                        //echo $maxwidth[$b]." max $b.Spalte<br/>";

                        if ($maxwidth[$b] > 140) $maxwidth[$b]=130;
                        if ($b==0)
                        {
                            $iLines=$this->MultiCell($maxwidth[$b],6,$this->delete_html($array[$a][$b]),0,'L',$fill,$bEndOfCell);
                        }
                        else
                        {
                            $this->MultiCell($maxwidth[$b],$iLines,$this->delete_html($array[$a][$b]),0,'L',$fill,$bEndOfCell);
                        }

                    }
                }
            }
            $this->ln(5);
        }
        function getminwidth($array)
        {
            $width = array();
            for($i=0;$i<sizeof($array);$i++)
            {
                for($j=0;$j<sizeof($array[$i]);$j++)
                {
                    $stringWidth=0;
                    $chars = str_split($this->delete_html($array[$i][$j]),1);
                    foreach($chars as $char)
                    {
                        $stringWidth = $stringWidth+$this->GetCharWidth($char);

                        //echo $stringWidth.": ".$char."<br/>";
                    }
                    if($stringWidth!=0 && $stringWidth<8)
                        $stringWidth = $stringWidth*3;
                    if(!isset($width[$j])|| $stringWidth>$width[$j])
                    {
                        $width[$j] = $stringWidth;
                    }
                }
            }
            return $width;
        }
        function getmaxwidth($array)
        {
            for($i=0;$i<sizeof($array);$i++)
            {
                for($j=0;$j<sizeof($array[$i]);$j++)
                {
                    if(($i-1)>=0)
                    {
                        if(strlen($this->delete_html($array[($i-1)][$j])) < strlen($this->delete_html($array[$i][$j])))
                        {
                            $width[$j] = strlen($this->delete_html($array[$i][$j]));
                        }
                    }
                    else
                    {
                        $width[$j]=strlen($this->delete_html($array[$i][$j]));
                    }
                }
            }
            return ($width);
        }
        /**
        *
        * Gets the width for columns in a table based on their Stringlength and the width of the page...
        * @param $array
        * @return array with column width
        */
        function getFullWidth($array)
        {
            $maxlength = array();
            $width = array();
            $width = $this->getminwidth($array);

            $margins = $this->getMargins();
            $deadSpace = $margins['left']+$margins['right'];
            $fullWidth = ($this->GetLineWidth()*1000)-$deadSpace;
            $faktor = $fullWidth/array_sum($width);

            for($i=0;$i<sizeof($width);$i++)
            {
                $maxlength[$i]=$faktor*$width[$i];
            }
            return $maxlength;
        }
        /**
        *
        * gets the width for each column in tables, based on pagewidth and count of columns.
        * Good for static tables with equal value String-length
        * @param $array
        * @return unknown_type
        */
        function getEqualWidth($array)
        {
            $margins = $this->getMargins();
            $deadSpace = $margins['left']+$margins['right'];

            $width = ($this->GetLineWidth()*1000)-$deadSpace;
            $count = 0;
            for($i=0;$i<sizeof($array);$i++)
            {
                for($j=0;$j<sizeof($array[$i]);$j++)
                {
                    if(sizeof($array[$i])>$count)
                    {
                        $count = sizeof($array[$i]);
                    }
                }
            }

            if($count!=0)
                return ($width/$count);
            else
                return FALSE;
        }
        function write_out($name)
        {
            $this->Output($name,"D");
        }

        function delete_html($text)
        {
            $text = html_entity_decode($text);
            return strip_tags($text);
        }
}