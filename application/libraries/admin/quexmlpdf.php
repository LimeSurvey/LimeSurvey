<?php

/**
 * Modify these two lines to point to your TCPDF installation
 * Tested with TCPDF 5.8.008 - see http://www.tcpdf.org/
 */
//require(APPPATH.'config/tcpdf_config_ci'.EXT);
require('pdf.php');
require_once($tcpdf['base_directory'].'/tcpdf.php');
require_once($tcpdf['base_directory'].'/config/lang/eng.php');
//require_once($homedir .'/classes/tcpdf/tcpdf.php');

/**
 * A TCPDF based class to produce queXF compatible questionnaire PDF files and banding description XML from queXML
 * 
 * @author    Adam Zammit <adam.zammit@acspri.org.au>
 * @copyright (c) 2010 Australian Consortium for Social and Political Research Incorporated (ACSPRI)
 * @since     2010-09-02
 * @link      http://www.acspri.org.au/software
 * @link      http://quexml.sourceforge.net
 * @link      http://quexf.sourceforge.net
 */
class quexmlpdf extends pdf {

	/**
	 * Define an inch in MM
	 *
	 * @const float Defaults to 25.4
	 */
	const INCH_IN_MM = 25.4;

	/**
	 * Pixels per inch of exported document
	 * 
	 * @var int Defaults to 300.
	 */
	protected $ppi = 300;

	/**
	 * Whether a page break has occured
	 * 
	 * @var bool
	 */
	private $pageBreakOccured;

	/**
	 * Corner border (the number of mm between the edge of the page and the start of the document)
	 * 
	 * @var int  Defaults to 15. 
	 * @since 2010-09-02
	 */
	protected $cornerBorder = 15;

	/**
	 * The length in MM of a corner line
	 * 
	 * @var mixed  Defaults to 20. 
	 * @since 2010-09-20
	 */
	protected $cornerLength = 20;

	/**
	 * The width in MM of a corner line
	 * 
	 * @var mixed  Defaults to 0.5. 
	 * @since 2010-09-20
	 */
	protected $cornerWidth = 0.5;


	/**
	 * The TCPDF barcode type
	 * 
	 * @var bool  Defaults to 'I25'. 
	 * @since 2010-09-20
	 * @see write1DBarcode
	 */
	protected $barcodeType = 'I25';

	/**
	 * The x position in MM of the barcode
	 * 
	 * @var bool  Defaults to 138. 
	 * @since 2010-09-20
	 */
	protected $barcodeX = 138;

	/**
	 * Y position of barcode in mm
	 * 
	 * @var bool  Defaults to 6. 
	 * @since 2010-09-20
	 */
	protected $barcodeY = 6;

	/**
	 * Width of the barcode in mm
	 * 
	 * @var bool  Defaults to 49. 
	 * @since 2010-09-20
	 */
	protected $barcodeW = 49;

	/**
	 * Height of the barcode in mm
	 * 
	 * @var bool  Defaults to 6. 
	 * @since 2010-09-20
	 */
	protected $barcodeH = 6;

	/**
	 * The questionnaire ID of this form
	 * 
	 * @var mixed  Defaults to 1. 
	 * @since 2010-09-20
	 */
	protected $questionnaireId = 1;

	/**
	 * The length of a the id portion barcode
	 * 
	 * @var int  Defaults to 6. 
	 * @since 2010-09-20
	 * @see $pageLength
	 */
	protected $idLength = 6;

	/**
	 * The length of the page portion of the barcode
	 * 
	 * @var mixed  Defaults to 2. 
	 * @since 2010-09-20
	 * @see $idLength
	 */
	protected $pageLength = 2;

	/**
	 * width of the question title column in MM
	 * 
	 * @var mixed  Defaults to 14. 
	 * @since 2010-09-20
	 */
	protected $questionTitleWidth = 14;

	/**
	 * Width of question text in MM
	 * 
	 * @var mixed  Defaults to 120. 
	 * @since 2010-09-20
	 */
	protected $questionTextWidth = 120;

	/**
	 * Height of the border between questions in MM
	 * 
	 * @var mixed  Defaults to 1. 
	 * @since 2010-09-20
	 */
	protected $questionBorderBottom = 1;

	/**
	 * Width of the skip column area (where skip text is written)
	 * 
	 * @var string  Defaults to 20. 
	 * @since 2010-09-20
	 */
	protected $skipColumnWidth = 20;

	/**
	 * The default style for the text of the questionnaire
	 * 
	 * @var string  Defaults to "<style>td.questionHelp {text-align:right; font-style:italic; font-size: 8pt;} td.responseText {text-align:right; margin-right:1mm;} td.responseAboveText {text-align:left;} td.responseLabel {text-align:center; font-size:8pt;} span.sectionTitle {font-size: 18pt} span.sectionDescription {font-size: 14pt}</style>". 
	 * @since 2010-09-16
	 */
	protected $style;
	/**
	 * Width of the area of each single response 
	 * 
	 * @var string  Defaults to 10. 
	 * @since 2010-09-20
	 */
	protected $singleResponseAreaWidth = 10;

	/**
	 * Height of the are of each single response (includes guiding lines)
	 * 
	 * @var string  Defaults to 9. 
	 * @since 2010-09-20
	 */
	protected $singleResponseAreaHeight = 9;

	/**
	 * Width of a single response box
	 * 
	 * @var string  Defaults to 5. 
	 * @since 2010-09-20
	 */
	protected $singleResponseBoxWidth = 5;

	/**
	 * Height of a single response box
	 * 
	 * @var string  Defaults to 5. 
	 * @since 2010-09-20
	 */
	protected $singleResponseBoxHeight = 5;

	/**
	 * Width of a response boxes border
	 * 
	 * @var string  Defaults to 0.1. 
	 * @since 2010-09-20
	 */
	protected $singleResponseBoxBorder = 0.15;

	/**
	 * Length of the "eye guide" for a vertical response box
	 * 
	 * @var string  Defaults to 1. 
	 * @since 2010-09-20
	 */
	protected $singleResponseBoxLineLength = 1;

	/**
	 * Vertical area taken up by a response box
	 * 
	 * @var string  Defaults to 15. 
	 * @since 2010-09-20
	 */
	protected $singleResponseVerticalAreaWidth = 15;

	/**
	 * Vertical area taken up by a "small" vertical response area
	 * 
	 * @var string  Defaults to 9. 
	 * @since 2010-09-20
	 */
	protected $singleResponseVerticalAreaWidthSmall = 9;

	/**
	 * Maximum number of horizontal boxes to display normally before shrinking horizontal area width
	 * 
	 * @var int  Defaults to 10. 
	 * @since 2010-09-08
	 */
	protected $singleResponseHorizontalMax = 10;

	/**
	 * The horizontal area height of a box running horizontally
	 * 
	 * @var string  Defaults to 7. 
	 * @since 2010-09-20
	 */
	protected $singleResponseHorizontalAreaHeight = 7;

	/**
	 * The height of an arrow
	 * 
	 * @var array  Defaults to 3. 
	 * @since 2010-09-20
	 */
	protected $arrowHeight = 3;

	/**
	 * The width of a text response box
	 * 
	 * @var mixed  Defaults to 6. 
	 * @since 2010-09-20
	 */
	protected $textResponseWidth = 6;

	/**
	 * The border width of a text resposne box
	 * 
	 * @var mixed  Defaults to 0.15.  Any less than this may produce printing problems
	 * @since 2010-09-20
	 */
	protected $textResponseBorder = 0.15;

	/**
	 * The height of a text response box
	 * 
	 * @var mixed  Defaults to 8. 
	 * @since 2010-09-20
	 */
	protected $textResponseHeight = 8;

	/**
	 * The maximum number of text responses per line
	 * 
	 * @var mixed  Defaults to 24. 
	 * @since 2010-09-20
	 */
	protected $textResponsesPerLine = 24;

	/**
	 * Maximum number of text responses boxes where the label should appear on the same line
	 * 
	 * @var mixed  Defaults to 16. 
	 * @since 2010-09-20
	 */
	protected $labelTextResponsesSameLine = 16;

	/**
	 * The gap between multi line text responses
	 * 
	 * @var mixed  Defaults to 1. 
	 * @since 2010-09-20
	 */
	protected $textResponseLineSpacing = 1;

	/**
	 * The vertical gap between subquestions in mm
	 * 
	 * @var string  Defaults to 2. 
	 * @since 2010-09-02
	 */
	protected $subQuestionLineSpacing = 2;

	/**
	 * The multiplier from long text response width specified to the height in mm
	 * 
	 * @var mixed  Defaults to 1. 
	 * @since 2010-09-20
	 */
	protected $longTextResponseHeightMultiplier = 1;

	/**
	 * Width of a long text response box
	 * 
	 * @var mixed  Defaults to 145. 
	 * @since 2010-09-20
	 */
	protected $longTextResponseWidth = 145;

	/**
	 * Default number of characters to store in a long text field
	 * 
	 * @var int Default is 1024;
	 * @since 2010-09-02
	 */
	protected $longTextStorageWidth = 1024;


	/**
	 * The layout of the form for importing in to queXF
	 *
	 * @var array Defaults to empty array
	 * @link http://quexf.sourceforge.net/
	 */
	protected $layout = array();	

	/**
	 * Array to store section information for layout
	 * 
	 * @var array  Defaults to empty array
	 * @since 2010-09-02
	 */
	protected $section = array();

	/**
	 * Page counter pointer (links to barcode id of page)
	 * 
	 * @var mixed  Defaults to "". 
	 * @since 2010-09-02
	 */
	protected $layoutCP = "";

	/**
	 * Section counter pointer
	 * 
	 * @var string  Defaults to 0. 
	 * @since 2010-09-02
	 */
	protected $sectionCP = 0;

	/**
	 * Box group counter pointer
	 * 
	 * @var bool  Defaults to 0. 
	 * @since 2010-09-02
	 */
	protected $boxGroupCP = 0;

	/**
	 * Box counter pointer
	 * 
	 * @var int  Defaults to 0. 
	 */
	protected $boxCP = 0;

	/**
	 * Background colour of a question
	 * 
	 * @var bool  Defaults to array(220,220,220). 
	 * @since 2010-09-15
	 */
	protected $backgroundColourQuestion = array(241,241,241);

	/**
	 * The bacground colour of a section
	 * 
	 * @var bool  Defaults to array(200,200,200). 
	 * @since 2010-09-20
	 */
	protected $backgroundColourSection = array(221,221,221);

	/**
	 * Empty background colour
	 * 
	 * @var bool  Defaults to array(255,255,255). 
	 * @since 2010-09-20
	 */
	protected $backgroundColourEmpty = array(255,255,255);

	/**
	 * The colour of a line/fill
	 * 
	 * @var mixed  Defaults to array(0,0,0). 
	 * @since 2010-09-20
	 */
	protected $lineColour = array(0,0,0);

	/**
	 * The text to display before a skip 
	 * 
	 * @var string  Defaults to "Skip to ". 
	 * @since 2010-09-16
	 */
	protected $skipToText = "Skip to ";

	/**
	 * Should fonts be embedded in the document?
	 * 
	 * @var mixed  Defaults to true. 
	 * @since 2010-09-20
	 */
	protected $embedFonts = true;

	/**
	 * Height in MM of a VAS response
	 * 
	 * @var mixed  Defaults to 8. 
	 * @since 2010-09-20
	 */
	protected $vasAreaHeight = 8;

	/**
	 * Width of a VAS line
	 * 
	 * @var mixed  Defaults to 0.5. 
	 * @since 2010-09-20
	 */
	protected $vasLineWidth = 0.5;

	/**
	 * Height of the VAS ending lines in mm
	 * 
	 * @var mixed  Defaults to 4. 
	 * @since 2010-09-20
	 */
	protected $vasHeight = 4;

	/**
	 * Length of the vas line itself
	 * 
	 * @var mixed  Defaults to 100. 
	 * @since 2010-09-20
	 */
	protected $vasLength = 100;

	/**
	 * The number of increments stored on a vas line
	 * 
	 * @var mixed  Defaults to 100. 
	 * @since 2010-09-20
	 */
	protected $vasIncrements = 100;

	/**
	 * The text to separate parent text and subquestion text
	 * 
	 * @var string  Defaults to " : ". 
	 * @since 2010-09-22
	 */
	protected $subQuestionTextSeparator = " : ";

	/**
	 * The top margin for questionnaireInfo section
	 * 
	 * @var mixed  Defaults to 5. 
	 * @since 2010-10-29
	 */
	protected $questionnaireInfoMargin = 20;

	/**
	 * Height of a response label
	 * 
	 * @var resource  Defaults to 10. 
	 * @since 2010-11-05
	 */
	protected $responseLabelHeight = 10;

	/**
	 * Font size for response label
	 * 
	 * @var resource  Defaults to 8. 
	 * @since 2010-11-05
	 */
	protected $responseLabelFontSize = 8;
	
	/**
	 * Font size for response text
	 * 
	 * @var resource  Defaults to 10. 
	 * @since 2010-11-05
	 */
	protected $responseTextFontSize = 10;

	/**
	 * Font size of the skip to text
	 * 
	 * @var string  Defaults to 8. 
	 * @since 2010-11-05
	 */
	protected $skipToTextFontSize = 8;

	/**
	 * Default font
	 * 
	 * @var string  Defaults to 'freeserif'. 
	 * @since 2010-11-05
	 */
	protected $defaultFont = 'freeserif';

	/**
	 * Height of a section break in mm
	 * 
	 * @var string  Defaults to 18. 
	 * @since 2010-11-05
	 */
	protected $sectionHeight = 18;
    
    function __construct()
    {
    	parent::__construct();
        $CI =& get_instance();
		$this->style = $CI->load->view('libraries/quexmlpdf/style_view','',true);
    }
	/**
	 * Add a box group to the page layout system
	 *
	 * VALUES(0, 'Temporary');
	 * VALUES(1, 'Single choice');
	 * VALUES(2, 'Multiple choice');
	 * VALUES(3, 'Text');
	 * VALUES(4, 'Number');
	 * VALUES(5, 'Barcode');
	 * VALUES(6, 'Long text');
	 * 
	 * @param int $type The type of box group for verification purposes
	 * @param string $varname The variable name
	 * @param string $label   The label for the box group Optional, defaults to "". 
	 * @param int $width   The width of this group Optional, defaults to 1. 
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	protected function addBoxGroup($type,$varname,$label = "", $width = 1)
	{
		$this->boxGroupCP++;
		$this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP] = 
			array(	'type' => $type, 
				'width' => $width,
				'varname' => $varname,
				'sortorder' => $this->boxGroupCP,
				'label' => $label,
				'groupsection' => $this->sectionCP,
				'box' => array());
	}


	/**
	 * Add a box to the page layout system
	 * 
	 * @param int $tlx   Top left X
	 * @param int $tly   Top left Y
	 * @param int  $brx   Bottom right X
	 * @param int  $bry   Bottom right Y
	 * @param string $value Optional, defaults to "". 
	 * @param string $label Optional, defaults to "". 
	 * 
	 */
	protected function addBox($tlx,$tly,$brx,$bry,$value = "",$label = "")
	{
		$this->boxCP++;
		$this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP]['box'][] = 
			array(	'tlx' => $this->mm2px($tlx),
				'tly' => $this->mm2px($tly),
				'brx' => $this->mm2px($brx),
				'bry' => $this->mm2px($bry),
				'value' => $value,
				'label'=> $label,
				);

		//Update the width of the parent boxgroup given its type and this additional box
		$type = $this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP]['type'];
		$count = count($this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP]['box']);
		$width = $this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP]['width'];
	
		switch($type){
			case 1: //Single choice 
			case 2: //Multiple choice
				if (strlen($value) > $width) $width = strlen($value);
				if (strlen($count) > $width) $width = strlen($count);
				break;
			case 3: //Text
			case 4: //Numbers
				$width = $count;
				break;
			case 6: //Longtext
				$width = $this->longTextStorageWidth;
		}

		$this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP]['width'] = $width;
	}


	/**
	 * Export the layout as an XML file
	 * 
	 * @return string The XML layout in queXF Banding XML format
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-20
	 */
	public function getLayout()
	{
		$doc = new DomDocument('1.0');
		$root = $doc->createElement('queXF');

		$q = $doc->createElement('questionnaire');

		$id = $doc->createElement('id');
		$value = $doc->createTextNode($this->questionnaireId);
		$id->appendChild($value);
		$q->appendChild($id);

		foreach($this->section as $key => $val)
		{
			$s = $doc->createElement('section');
			$s->setAttribute('id',$key);
			foreach ($val as $sk => $sv)
			{
				$tmpe = $doc->createElement($sk);
				$tmpv = $doc->createTextNode($sv);
				$tmpe->appendChild($tmpv);
				$s->appendChild($tmpe);
			}
			$q->appendChild($s);
		}
		foreach($this->layout as $key => $val)
		{
			$p = $doc->createElement('page');

			foreach ($val as $pk => $pv)
			{
				if ($pk != 'boxgroup')
				{	
					$tmpe = $doc->createElement($pk);
					$tmpv = $doc->createTextNode($pv);
					$tmpe->appendChild($tmpv);
					$p->appendChild($tmpe);
				}
			}

			foreach ($val['boxgroup'] as $bg)
			{
				$bgE = $doc->createElement('boxgroup');
				foreach ($bg as $pk => $pv)
				{
					if ($pk == 'groupsection')
					{
						$gs = $doc->createElement('groupsection');
						$gs->setAttribute('idref',$pv);
						$bgE->appendChild($gs);
					}
					else if ($pk != 'box')
					{
						$tmpe = $doc->createElement($pk);
						$tmpv = $doc->createTextNode($pv);
						$tmpe->appendChild($tmpv);
						$bgE->appendChild($tmpe);
					}
				}

				foreach($bg['box'] as $b)
				{
					$bE = $doc->createElement('box');
					foreach($b as $bk => $bv)
					{
						$tmpe = $doc->createElement($bk);
						$tmpv = $doc->createTextNode($bv);
						$tmpe->appendChild($tmpv);
						$bE->appendChild($tmpe);
					}
					$bgE->appendChild($bE);
				}
				$p->appendChild($bgE);
			}
			$q->appendChild($p);
		}
		$root->appendChild($q);
		$doc->appendChild($root);
		$doc->formatOutput = true; //make it look nice
		return $doc->saveXML();
	}

	/**
	 * Set font size and style
	 * 
	 * @param string $size  Optional, defaults to 12
	 * @param string $style Optional, defaults to ''. 
	 * 
	 * @return TODO
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-11-05
	 */
	protected function setDefaultFont($size = 12,$style = '')
	{
		$this->SetFont($this->defaultFont,$style);
		$this->SetFontSize($size);
	}

	/**
	 * Initialise TCPDF width some default values and embedded fonts
	 * 
	 * @return TODO
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-20
	 */
	protected function init()
	{
		if ($this->embedFonts)
		{
			$this->setFontSubsetting(false); //we want full subsetting
			$this->AddFont('freesans','');
			$this->AddFont('freesans','B');
			$this->AddFont('freesans','I');
			$this->AddFont('freesans','BI');
			$this->AddFont('freeserif','');
			$this->AddFont('freeserif','B');
			$this->AddFont('freeserif','I');
			$this->AddFont('freeserif','BI');
			
			$this->SetFont($this->defaultFont);
		}
		
		// set document information
		$this->SetCreator('queXMLPDF (http://quexml.sourceforge.net)');
		$this->SetAuthor('Adam Zammit <adam.zammit@acspri.org.au>');
		$this->SetTitle('queXML Document');
		$this->SetSubject('queXML');
		$this->SetKeywords('queXML queXF');
	}

	/**
	 * Override of TCPDF Header function to blank
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-20
	 */
	public function Header(){
	}

	/**
	 * Override of TCPDF Footer function to blank
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 */
	public function Footer(){
	}

	/**
	 * Set the background wash of the page
	 * 
	 * @param mixed $type Optional, defaults to 'empty'. 
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	protected function setBackground($type = 'empty')
	{
		switch ($type) {
			case 'question':
				$this->SetFillColor($this->backgroundColourQuestion[0],$this->backgroundColourQuestion[1],$this->backgroundColourQuestion[0]);
				break;
			case 'section':
				$this->SetFillColor($this->backgroundColourSection[0],$this->backgroundColourSection[1],$this->backgroundColourSection[0]);
				break;
			case 'empty':
				$this->SetFillColor($this->backgroundColourEmpty[0],$this->backgroundColourEmpty[1],$this->backgroundColourEmpty[0]);
				break;

		}
	}

	/*
	 * The X coordinate of the start of the page proper
	 * 
	 * @return int The X coordinate of the start of the page
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	public function getMainPageX()
	{
		return ($this->cornerBorder + $this->cornerWidth);
	}

	/**
	 * The width of the writeable page
	 * 
	 * @return int The width of the writeable page
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	public function getMainPageWidth()
	{
		return ($this->getPageWidth() - (($this->cornerBorder * 2.0) + ($this->cornerWidth * 2.0)));
	}

	/**
	 * Draw a horizontal response box with possible eye guides and arrows
	 * 
	 * @param int $x The x position of the box area (top left)
	 * @param int $y The y position of the box area (top left)
	 * @param string $position What position the box is in for the eye guides
	 * @param bool $downarrow Draw a down arrow?
	 * @param bool $rightarrow Draw an arrow to the right?
	 * @param bool $smallwidth Whether or not to use the small width
	 *
	 */
	protected function drawHorizontalResponseBox($x,$y,$position = 'only',$downarrow = false, $rightarrow = false, $smallwidth = false)
	{
		$this->SetDrawColor($this->lineColour[0],$this->lineColour[1],$this->lineColour[2]);
		$this->SetLineWidth($this->singleResponseBoxBorder);

		//centre for the line
		$boxmid = ($y + ($this->singleResponseHorizontalAreaHeight / 2.0));

		//centre on y
		$y = $y + (($this->singleResponseHorizontalAreaHeight - $this->singleResponseBoxHeight) / 2.0);
		
		if ($smallwidth) 
			$areawidth = $this->singleResponseVerticalAreaWidthSmall;
		else		
			$areawidth = $this->singleResponseVerticalAreaWidth;


		$linelength = (($areawidth - $this->singleResponseBoxWidth) / 2.0);

		$this->SetLineStyle(array('dash' => '1'));

		if ($position == 'last' || $position == 'middle') 
		{
			$this->Line($x, $boxmid, $x + $linelength,$boxmid);
		}
		if ($position == 'first' || $position == 'middle')
		{
			$this->Line($x + $linelength + $this->singleResponseBoxWidth, $boxmid, $x + ($linelength * 2) + $this->singleResponseBoxWidth,$boxmid);
		}

		$this->SetLineStyle(array('dash' => '0'));

		$this->Rect($x + $linelength,$y,$this->singleResponseBoxWidth,$this->singleResponseBoxHeight,'DF',array(),$this->backgroundColourEmpty);
		$this->setBackground('question');		
		return array($x + $linelength,$y,$x + $linelength + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight); //return the posistion for banding
	}

	/**
	 * Draw a vertical response box with possible eye guides and arrows
	 * 
	 * @param int $x The x position of the box area (top left)
	 * @param int $y The y position of the box area (top left)
	 * @param string $position What position the box is in for the eye guides
	 * @param bool $downarrow Draw a down arrow?
	 * @param bool $rightarrow Draw an arrow to the right?
	 *
	 */
	protected function drawVerticalResponseBox($x,$y,$position = 'only',$downarrow = false, $rightarrow = false)
	{
		$this->SetDrawColor($this->lineColour[0],$this->lineColour[1],$this->lineColour[2]);
		$this->SetLineWidth($this->singleResponseBoxBorder);
	
		if (!$downarrow)
		{
			if ($position == 'only') $y = $y + (($this->singleResponseAreaHeight - $this->singleResponseBoxHeight) / 2.0);
			else if ($position == 'first' || $position == 'last') $y = $y + (($this->singleResponseAreaHeight - ($this->singleResponseBoxHeight + $this->singleResponseBoxLineLength)) / 2.0);
			else if ($position == 'middle') $y = $y + (($this->singleResponseAreaHeight - ($this->singleResponseBoxHeight + ($this->singleResponseBoxLineLength * 2.0))) / 2.0);
		}

		$boxmid = ($x + ($this->singleResponseBoxWidth / 2.0));
		if ($position == 'first' || $position == 'middle') 
		{
			$this->Line($boxmid, ($y + $this->singleResponseBoxHeight), $boxmid, ($y + $this->singleResponseBoxHeight + $this->singleResponseBoxLineLength));
		}
		if ($position == 'last' || $position == 'middle')
		{
			$this->Line($boxmid, $y, $boxmid, ($y - $this->singleResponseBoxLineLength));
		}

		if ($downarrow)
		{
			$this->Polygon(array($x, $y + $this->singleResponseBoxHeight, $boxmid, $y + $this->singleResponseBoxHeight + $this->arrowHeight, $x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight),'DF',array(),$this->lineColour);	
		}

		if ($rightarrow !== false)
		{
			//Draw skipto
			$boxymid = ($y + ($this->singleResponseBoxHeight / 2.0));
			$this->Polygon(array($x + $this->singleResponseBoxWidth, $y, $x + $this->singleResponseBoxWidth + $this->arrowHeight, $boxymid, $x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight),'DF',array(),$this->lineColour);	
			//Now draw the text

			//Start at $x + singleResponseboxWidth + arrowHeight, $y - siongleresponseboxlinelength and go to $skipcolumnwidth wide and singleresponseareHeight high
			$this->setBackground('question');		
			$text =  $this->skipToText . $rightarrow;
			$ypos = $this->GetY();

			$this->setDefaultFont($this->skipToTextFontSize,'B');

			$this->MultiCell($this->skipColumnWidth,$this->singleResponseBoxHeight,$text,0,'L',false,0,($this->getPageWidth() - $this->getMainPageX() - $this->skipColumnWidth),$y,true,0,false,true,$this->singleResponseBoxHeight,'M',true);

			//Reset to non bold as causing problems with TCPDF HTML CSS conversion
			$this->setDefaultFont($this->skipToTextFontSize,'');

			//$this->writeHTMLCell($this->skipColumnWidth, 0, $this->getPageWidth() - $this->getMainPageX() - $this->skipColumnWidth ,$y, $this->style . $html,0,0,true,true,'C',true);
			$this->SetY($ypos,false);
		}

		$this->Rect($x,$y,$this->singleResponseBoxWidth,$this->singleResponseBoxHeight,'DF',array(),$this->backgroundColourEmpty);
		$this->setBackground('question');		
		return array($x,$y,$x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight); //return the posistion for banding
	}


	/**
	 * Return capital letter(s) corresponding to the given number
	 * 
	 * @param integer $number 
	 * 
	 * @return string Letter(s) corresponding to the number
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-08
	 */
	public function numberToLetter($number)
	{
		if ($number < 1) $number = 1;

		if ($number > 26)
			return chr((($number - 1) / 26) + 64) . chr((($number - 1) % 26) + 65);
		else
			return chr($number + 64);
	}


	/**
	 * Get the questionnaire id
	 * 
	 * @return int The questionnaire Id
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-23
	 */
	public function getQuestionnaireId()
	{
		return $this->questionnaireId;
	}

	/**
	 * Converts a queXML file to the array format required for the create function
	 * 
	 * @param string $quexml The queXML file
	 *
	 * @return array An array readable by create 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-08
	 * @see create
	 */
	public function createqueXML($quexml)
	{
		$xml = new SimpleXMLElement($quexml);
	
		$q = array();

		$scount = 1;
		$sl = "";

		$q['id'] = $xml['id'];

		foreach ($xml->questionnaireInfo as $qitmp)
		{
			if ($qitmp->position == 'after')
			{
				if (!isset($q['infoafter']))
					$q['infoafter'] = "";

				$q['infoafter'] .= $qitmp->text . "<br/><br/>";
			}
			else if ($qitmp->position == 'before')
			{
				if (!isset($q['infobefore']))
					$q['infobefore'] = "";

				$q['infobefore'] .= $qitmp->text . "<br/><br/>";
			}
		}
	
		foreach($xml->section as $s)
		{
			$stmp = array();
			$sl = $this->numberToLetter($scount);
			$stmp['title'] = "Section " . $sl;
			$stmp['info'] = "";
			$stmp['text'] = "";
	
			foreach ($s->sectionInfo as $sitmp)
			{
				if ($sitmp->position == 'title')
				{
					$stmp['text'] .= $sitmp->text;
				}
				if ($sitmp->position == 'before' || $sitmp->position == 'during')
				{
					$stmp['info'] .= $sitmp->text . "<br/>";
				}
			}
			
			$qcount = 1;
			foreach ($s->question as $qu)
			{
				$qtmp = array();
				$rstmp = array();
				
				$qtmp['title'] = $sl . $qcount . ".";
				$qtmp['text'] = "";

				foreach ($qu->text as $ttmp)
				{
					//Add a new line if we aren't at the end
					if ($ttmp != end($qu->text)){ $qtmp['text'] .= "<br/>"; } 
					
					$qtmp['text'] .= $ttmp;
				}
				
				foreach ($qu->directive as $ttmp)
				{
					if ($ttmp->administration == 'self' && $ttmp->position != 'after')
					{
						if (!isset($qtmp['helptext']))
							$qtmp['helptext'] = "";

						$qtmp['helptext'] .= $ttmp->text;
					}
					if ($ttmp->administration == 'self' && $ttmp->position == 'after')
					{
						if (!isset($qtmp['helptextafter']))
							$qtmp['helptextafter'] = "";

						$qtmp['helptextafter'] .= $ttmp->text;
					}
				}

				foreach ($qu->subQuestion as $sq)
				{
					$sqtmp = array();
					$sqtmp['text'] = "";
					foreach ($sq->text as $ttmp)
					{
						$sqtmp['text'] .= $ttmp;
					}
					$sqtmp['varname'] = $sq['varName'];
					$rstmp['subquestions'][] = $sqtmp;
				}

				foreach ($qu->response as $r)
				{
					$rtmp = array();
					$rstmp['varname'] = $r['varName'];
					if (isset($r->fixed))
					{
						$rtmp['type'] = 'fixed';
						$rtmp['width'] = count($r->fixed->category);
						if ($r->fixed['rotate'] == "true") 
							$rtmp['rotate'] = "true";
						$ctmp = array();
						foreach ($r->fixed->category as $c)
						{
							$cat = array();
							$cat['text'] = current($c->label);
							$cat['value'] = current($c->value);
							if (isset($c->skipTo)) $cat['skipto'] = current($c->skipTo);
							if (isset($c->contingentQuestion))
							{
								//Need to handle contingent questions
								$oarr = array();
								$oarr['width'] = current($c->contingentQuestion->length);
								$oarr['text'] = current($c->contingentQuestion->text);
								$oarr['varname'] = $c->contingentQuestion['varName'];
								$cat['other'] = $oarr;
							}	
							$ctmp[] = $cat;	
						}
						$rtmp['categories'] = $ctmp;
					}
					else if (isset($r->free))
					{
						$format = strtolower(trim(current($r->free->format)));
						if ($format == 'longtext')
							$rtmp['type'] = 'longtext';
						else if ($format == 'number' || $format == 'numeric' || $format == 'integer')
							$rtmp['type'] = 'number';
						else
							$rtmp['type'] = 'text';
						$rtmp['width'] = current($r->free->length);
						$rtmp['text'] = current($r->free->label);
					}
					else if (isset($r->vas))
					{
						$rtmp['type'] = 'vas';
						$rtmp['width'] = current($r->vas->length);
						$rtmp['text'] = current($r->vas->label);
					}
					$rstmp['response'] = $rtmp;
					$qtmp['responses'][] = $rstmp;
				}
				$stmp['questions'][] = $qtmp;
				$qcount++;
			}
			$q['sections'][] = $stmp;

			$scount++;
		}
		return $q;
	}

	/**
	 * Create a queXML PDF document based on an array
	 * that is structured like a queXML document
	 * 
	 * sections (title, text, info)
	 *	questions (title, text, varname, helptext, helptextafter)
	 *		responses (varname)
	 *			subquestion (text, varname)
	 *			response (type, width, text, rotate)
	 *				categories (text, value)
	 *
	 * @param array $questionnaire The questionnaire in the array format above
	 */
	public function create($questionnaire)
	{
		$this->init();
		$this->questionnaireId = intval($questionnaire['id']);
		$this->newPage();

		//Draw questionnaireInfo before if exists
		if (isset($questionnaire['infobefore']))
		{
			$this->setBackground('question');
			$this->writeHTMLCell($this->getMainPageWidth(), $this->questionnaireInfoMargin, $this->getMainPageX(), $this->GetY() - $this->questionBorderBottom, "<div></div>",0,1,true,true);
			$html = "<table><tr><td width=\"" . $this->getMainPageWidth() . "mm\" class=\"questionnaireInfo\">{$questionnaire['infobefore']}</td><td></td></tr></table>";
			$this->writeHTMLCell($this->getMainPageWidth(), 1, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);
		}



		foreach($questionnaire['sections'] as $sk => $sv)
		{
			//link the section title with the first question for pagination purposes
			$questions = count($sv['questions']);
			
			$this->startTransaction();
			$this->addSection($sv['text'],$sv['title'],$sv['info']);
			if ($questions != 0) $this->createQuestion($sv['questions'][0]);
			if ($this->pageBreakOccured)
			{
				$this->pageBreakOccured = false;
				$this->rollBackTransaction(true);
				$this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
				$this->fillPageBackground();
				$this->newPage();
				$this->addSection($sv['text'],$sv['title'],$sv['info']);
				if ($questions != 0) $this->createQuestion($sv['questions'][0]);
			}
			else
				$this->commitTransaction();

			//start from the second question as first is linked to the section
			foreach(array_slice($sv['questions'], 1) as $qk => $qv)
			{
				$this->startTransaction();
				//add question here
				$this->createQuestion($qv);
				if ($this->pageBreakOccured)
				{
					$this->pageBreakOccured = false;
					$this->rollBackTransaction(true);
					$this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
					//now draw a background to the bottom of the page
					$this->fillPageBackground();
			
					$this->newPage();
					//retry question here
					$this->createQuestion($qv);
				}
				else
					$this->commitTransaction();
			}
		}

		//Draw questionnaireInfo after if exists
		if (isset($questionnaire['infoafter']))
		{
			$this->setBackground('question');
			$this->writeHTMLCell($this->getMainPageWidth(), $this->questionnaireInfoMargin, $this->getMainPageX(), $this->GetY() - $this->questionBorderBottom, "<div></div>",0,1,true,true);
			$html = "<table><tr><td width=\"" . $this->getMainPageWidth() . "mm\" class=\"questionnaireInfo\">{$questionnaire['infoafter']}</td><td></td></tr></table>";
			$this->writeHTMLCell($this->getMainPageWidth(), 1, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);
		}


		//fill to the end of the last page
		$this->fillPageBackground();
	}

	/**
	 * Create a question that may have multiple response groups
	 *
	 * questions (title, text, helptext, helptextafter)
	 *	responses (varname)
	 *		subquestions 
	 *			subquestion(text, varname)
	 *		response (type, width, text, rotate)
	 *			categories 
	 *				category(text, value, skipto, other)
	 *
	 * @param array $question The questions portion of the array
	 * @see create
	 */
	protected function createQuestion($question)
	{
		$help = false;
		if (isset($question['helptext'])) $help = $question['helptext'];

		//Question header
		$this->drawQuestionHead($question['title'], $question['text'],$help);

		$text = "";
		if (isset($question['text'])) $text = $question['text'];

		//Loop over response groups and produce questions of various types
		if (isset($question['responses'])) { foreach($question['responses'] as $r)
		{
			$varname = $r['varname'];	

			if (isset($r['subquestions']))
			{
				$response = $r['response'];
				$subquestions = $r['subquestions'];
				$type = $response['type'];

				$bgtype = 3; //box group type temp set to 3 (text)

				switch ($type)
				{
					case 'fixed':
						$categories = $response['categories'];
						
						if (isset($response['rotate']))
							$this->drawSingleChoiceVertical($categories,$subquestions,$text);
						else
							$this->drawSingleChoiceHorizontal($categories,$subquestions,$text);
						
						break;
					case 'number':
						$bgtype = 4;
					case 'currency':
					case 'text':
						if (isset($response['rotate']))
							$this->drawMatrixTextHorizontal($subquestions,$response['width'],$text,$bgtype);
						else
							$this->drawMatrixTextVertical($subquestions,$response['width'],$text,$bgtype);
						break;
					case 'vas':
						$this->drawMatrixVas($subquestions,$text);
						break;
		
				}
			}
			else
			{
				$response = $r['response'];
				$type = $response['type'];

				if (isset($response['text']) && !empty($response['text'])) 
					$rtext = $text .  $this->subQuestionTextSeparator .  $response['text'];
				else
					$rtext = $text;

				$bgtype = 3; //box group type temp set to 3 (text)

				switch ($type)
				{
					case 'fixed':
						if (isset($response['rotate']))
							$this->drawSingleChoiceHorizontal($response['categories'],array(array('text' => '', 'varname' => $varname)),$rtext);
						else
							$this->drawSingleChoiceVertical($response['categories'],array(array('text' => '', 'varname' => $varname)),$rtext);
						break;
					case 'longtext':
						$this->addBoxGroup(6,$varname,$rtext);
						$this->drawLongText($response['width']);
						break;
					case 'number':
						$bgtype = 4;
					case 'currency':
					case 'text':
						$this->addBoxGroup($bgtype,$varname,$rtext,$response['width']);	
						$this->drawText($response['text'],$response['width']);
						//Insert a gap here
						$this->Rect($this->getMainPageX(),$this->GetY(),$this->getMainPageWidth(),$this->subQuestionLineSpacing,'F',array(),$this->backgroundColourQuestion);
						$this->SetY($this->GetY() + $this->subQuestionLineSpacing,false);
						break;
					case 'vas':
						$this->addBoxGroup(1,$varname,$rtext,strlen($this->vasIncrements));
						$this->drawVas($response['text']);
						break;
		
				}
			}
		}}

		//If there is some help text for after the question
		if (isset($question['helptextafter']))
		{
			$this->setBackground('question');
			$html = "<table><tr><td width=\"" . $this->getMainPageWidth() . "mm\" class=\"questionHelpAfter\">{$question['helptextafter']}</td><td></td></tr></table>";
			$this->writeHTMLCell($this->getMainPageWidth(), 1, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);

		}

		//Leave a border at the bottom of the question		
		if ($this->questionBorderBottom > 0) //question border
			$this->SetY($this->GetY() + $this->questionBorderBottom,false); //new line
	}

	

	/**
	 * Draw text responses line by line
	 * 
	 * @param array $subquestions The subquestions containing text and varname
	 * @param int $width The width of the text element
	 * @param string|bool $parenttext The question text of the parent or false if not specified
	 * @param int $bgtype The box group type (default is 3 - text)
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	protected function drawMatrixTextVertical($subquestions,$width,$parenttext = false,$bgtype = 3)
	{
		$c = count($subquestions);
		for($i = 0; $i < $c; $i++)
		{
			$s = $subquestions[$i];

			if ($parenttext == false)
				$this->addBoxGroup($bgtype,$s['varname'],$s['text'],$width);
			else				
				$this->addBoxGroup($bgtype,$s['varname'],$parenttext . $this->subQuestionTextSeparator . $s['text'],$width);



			$this->drawText($s['text'],$width);
		
			$currentY = $this->GetY();
		
			//Insert a gap here
			$this->Rect($this->getMainPageX(),$this->GetY(),$this->getMainPageWidth(),$this->subQuestionLineSpacing,'F',array(),$this->backgroundColourQuestion);
			$this->SetY($currentY + $this->subQuestionLineSpacing,false);
		}
	}

	/**
	 * Draw multiple VAS items
	 * 
	 * @param array $subquestions The subquestions containing text and varname
	 * @param string|bool $parenttext The question text of the parent or false if not specified
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-20
	 */
	protected function drawMatrixVas($subquestions,$parenttext = false)
	{
		$c = count($subquestions);
		
		$width = strlen($this->vasIncrements);	

		for ($i = 0; $i < $c; $i++)
		{
			$s = $subquestions[$i];

			if ($parenttext == false)
				$this->addBoxGroup(1,$s['varname'],$s['text'],$width);
			else				
				$this->addBoxGroup(1,$s['varname'],$parenttext . $this->subQuestionTextSeparator . $s['text'],$width);




			$this->drawVas($s['text']);
		
			$currentY = $this->GetY();
		
			//Insert a gap here
			$this->Rect($this->getMainPageX(),$this->GetY(),$this->getMainPageWidth(),$this->subQuestionLineSpacing,'F',array(),$this->backgroundColourQuestion);
			$this->SetY($currentY + $this->subQuestionLineSpacing,false);

		}
		
	}


	/**
	 * Draw a large empty box for writing in text
	 * 
	 * @param mixed $width   The "width" of the box. This relates to the number of "lines" high
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	protected function drawLongText($width)
	{
		$currentY = $this->GetY();
		$height = $width * $this->longTextResponseHeightMultiplier;
		$html = "<div></div>";
		$this->setBackground('question');
		$this->writeHTMLCell($this->getMainPageWidth(), $height, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);
		$this->SetY($currentY,false);
		$this->setBackground('empty');
		$border = array('LTRB' => array('width' => $this->textResponseBorder, 'dash' => 0));
		//Align to skip column on right
		$this->SetX(($this->getPageWidth() - $this->getMainPageX() - $this->skipColumnWidth - $this->longTextResponseWidth),false);
		//Add to pay layout
		$this->addBox($this->GetX(),$this->GetY(),$this->GetX() + $this->longTextResponseWidth, $this->GetX() + $height);
		$this->SetDrawColor($this->lineColour[0],$this->lineColour[1],$this->lineColour[2]);
		$this->Cell($this->longTextResponseWidth,$height,'',$border,0,'',true,'',0,false,'T','C');
		$currentY = $currentY + $height;
		$this->SetY($currentY,false);
	}


	/**
	 * Draw a VAS
	 * 
	 * @param mixed $text The label for the VAS if any
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-20
	 */
	protected function drawVas($text)
	{
		$currentY = $this->GetY();

		$textwidth = $this->getMainPageWidth() - $this->skipColumnWidth - ($this->vasLength + ($this->vasLineWidth * 2.0)) - 2;

		$html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\">$text</td><td></td></tr></table>";
		
		$textwidth += 2;

		$this->setBackground('question');

		$this->writeHTMLCell($this->getMainPageWidth(), $this->vasAreaHeight, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,false);

		$ncurrentY = $this->GetY();

		$this->SetY($currentY,false);
		$this->SetX($textwidth + $this->getMainPageX(),false); 
	
		$this->SetLineWidth($this->vasLineWidth);
		$this->SetDrawColor($this->lineColour[0],$this->lineColour[1],$this->lineColour[2]);
	
		//Draw the VAS left vert line
		$ly = (($this->vasAreaHeight - $this->vasHeight) / 2.0) + $currentY;		
		$lx = $textwidth + $this->getMainPageX();
		$this->Line($lx,$ly,$lx,$ly + $this->vasHeight);
		
		//Right vert line
		$lx = $textwidth + $this->getMainPageX() + $this->vasLength + $this->vasLineWidth;
		$this->Line($lx,$ly,$lx,$ly + $this->vasHeight);
	
		//Line itself
		$ly = ($this->vasAreaHeight / 2.0) + $currentY;
		$lx = $textwidth + $this->getMainPageX() + ($this->vasLineWidth / 2.0);
		$this->Line($lx,$ly,$lx + $this->vasLength,$ly);

		//Add to layout system
		$bw = ($this->vasLength / $this->vasIncrements);
		$ly = (($this->vasAreaHeight - $this->vasHeight) / 2.0) + $currentY;		
		for ($i = 0; $i < $this->vasIncrements; $i++)
		{
			$this->addBox($lx,$ly,$lx + $bw,$ly + $this->vasHeight, $i + 1, $i + 1);
			$lx += $bw;
		}

		//Go back to the right Y position
		$this->SetY($ncurrentY,false);
	}	

	/**
	 * Draw a text response
	 *
	 * @param string $text The text label if any (can be HTML)
	 * @param int $width The number of boxes to draw
	 */
	protected function drawText($text,$width)
	{
		$this->SetDrawColor($this->lineColour[0],$this->lineColour[1],$this->lineColour[2]);

		//draw boxes - can draw up to $textResponsesPerLine for each line
		$lines = ceil($width / $this->textResponsesPerLine);

		//draw the text label on the top of this box
		if ($width > $this->labelTextResponsesSameLine && !empty($text))
		{
			$this->setBackground('question');
			$html = "<table><tr><td width=\"{$this->questionTitleWidth}mm\"></td><td width=\"" . ($this->getMainPageWidth() -  $this->skipColumnWidth - $this->questionTitleWidth) . "mm\" class=\"responseAboveText\">$text</td><td></td></tr></table>";
			$this->writeHTMLCell($this->getMainPageWidth(), 1, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);
		}

		$currentY = $this->GetY();

		for ($i = 0; $i < $lines; $i++)
		{
			if ($lines == 1) $cells = $width; //one line only
			else if (($i + 1 == $lines)) $cells = ($width - ($this->textResponsesPerLine * $i));  //last line
			else $cells = $this->textResponsesPerLine; //middle line


			$textwidth = ($this->getMainPageWidth() - $this->skipColumnWidth) - (($this->textResponseWidth + $this->textResponseBorder ) * $cells);

			//print "textwidth: $textwidth cells: $cells mainpagex: " . $this->getMainPageX() . "<br/>";
			//First draw a background of height $this->responseLabelHeight
			$html = "<div></div>";
			$this->setBackground('question');
			$this->writeHTMLCell($this->getMainPageWidth(), $this->textResponseHeight, $this->getMainPageX(), $this->GetY() , $this->style . $html,0,1,true,false);

			if ($lines == 1 && $cells <= $this->labelTextResponsesSameLine && !empty($text))
			{
				$this->setDefaultFont($this->responseTextFontSize);			

				$this->MultiCell($textwidth,$this->textResponseHeight,$text,0,'R',false,1,$this->getMainPageX(),$currentY,true,0,false,true,$this->textResponseHeight,'M',true);


				//$html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\">$text</td><td></td></tr></table>";
			}
			

			$ncurrentY = $this->GetY();

			$this->SetY($currentY,false);
			$this->SetX($textwidth + $this->getMainPageX() + 2,false); //set the X position to the first cell
			
			$this->drawCells($cells);

			$currentY = $ncurrentY;

			//New line
			$this->SetY($currentY,false); //new line
		

			if (!(($i + 1) == $lines) && $this->textResponseLineSpacing > 0) //if there should be a gap between text responses and not the last
			{
				$this->SetX($this->getMainPageX(),false);
				$this->setBackground('question');
				$this->Cell($this->getMainPageWidth(),$this->textResponseLineSpacing,'','',0,'',true,'',0,false,'T','C');
				$currentY += $this->textResponseLineSpacing;
				$this->SetY($currentY,false); //new line
			}
			
		}
					
	}

	/**
	 * Draw X number of cells at the current X Y position
	 * 
	 * @param int $cells  The number of text cells to draw
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-08
	 */
	protected function drawCells($cells)
	{
		$this->setBackground('empty');
		$this->SetDrawColor($this->lineColour[0],$this->lineColour[1],$this->lineColour[2]);

		for ($j = 0; $j < $cells; $j++)
		{
			//draw text cells 
			if ($cells == 1) //only
				$border = array('LTR' => array('width' => $this->textResponseBorder, 'dash' => 0), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));
			else if ($j == 0) //first
				$border = array('LT' => array('width' => $this->textResponseBorder, 'dash' => 0), 'R' => array('width' => $this->textResponseBorder, 'dash' => 1), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));
			else if (($j + 1) == $cells) //last
			{
				$border = array('TR' => array('width' => $this->textResponseBorder, 'dash' => 0), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));

				//add a border gap
				$this->SetX($this->GetX() + ($this->textResponseBorder),false);
			}
			else //middle
			{
				$border = array('T' => array('width' => $this->textResponseBorder, 'dash' => 0), 'R' => array('width' => $this->textResponseBorder, 'dash' => 1), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));
				//add a border gap
				$this->SetX($this->GetX() + ($this->textResponseBorder),false);
			}

			//Add the box to the layout scheme
			$this->addBox($this->GetX(),$this->GetY(),$this->GetX() + $this->textResponseWidth,$this->GetY() + $this->textResponseHeight);
			//Draw the box
			$this->Cell($this->textResponseWidth,$this->textResponseHeight,'',$border,0,'',true,'',0,false,'T','C');
		}

		//add some spacing for the bottom border
		//$this->SetY(($this->GetY() + ($this->textResponseBorder * 2)),false);
	}

	/**
	 * Draw a horizontal table of text boxes
	 * 
	 * @param array $subquestions The subquestions
	 * @param int $width The width
	 * @param string|bool $parenttext The question text of the parent or false if not specified
	 * @param int $bgtype The type of the box group (defaults to 3 - text)
	 *
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-08
	 */
	protected function drawMatrixTextHorizontal($subquestions,$width,$parenttext = false,$bgtype = 3)
	{
		$total = count($subquestions);
		$currentY = $this->GetY();

		$rwidth = ($width * ($this->textResponseWidth + $this->textResponseBorder + $this->textResponseLineSpacing)); 

		$textwidth = ($this->getMainPageWidth() - $this->skipColumnWidth) - ($rwidth * $total);

		$html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\"></td>";
		foreach ($subquestions as $r)
		{
			$html .= "<td class=\"responseLabel\" width=\"{$rwidth}mm\">{$r['text']}</td>";
		}
		$html .= "<td></td></tr></table>";
		$this->writeHTMLCell($this->getMainPageWidth(), $this->singleResponseAreaHeight, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);
		$currentY = $this->GetY();

		$html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\"></td><td></td></tr></table>";
		$this->writeHTMLCell($this->getMainPageWidth(), $this->singleResponseAreaHeight, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);

		$ncurrentY = $this->GetY();

		$this->SetY($currentY,false);		

		//Set X position
		$this->SetX($this->getMainPageX() + $textwidth,false);	


		foreach ($subquestions as $s)
		{
			//Add box group to current layout
			if ($parenttext == false)
				$this->addBoxGroup($bgtype,$s['varname'],$s['text']);
			else				
				$this->addBoxGroup($bgtype,$s['varname'],$parenttext . $this->subQuestionTextSeparator . $s['text']);



			//Draw the cells
			$this->drawCells($width);

			//Move X for a gap
			$this->SetX($this->GetX() + $this->textResponseLineSpacing,false);
			$this->SetY($currentY,false);		
		}

		//Move cursor back to the right spot
		$this->SetY($ncurrentY,false);
	}

	/**
	 * Draw a horizontal table of respones including "eye guides"
	 * 
	 * @param array $categories The response categories
	 * @param array $subquestions The subquestions if any 
	 * @param string|bool $parenttext The question text of the parent or false if not specified
	 *
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-08
	 */
	protected function drawSingleChoiceHorizontal($categories, $subquestions = array(array('text' => '')),$parenttext = false)
	{
		$total = count($categories);
		$currentY = $this->GetY();

		if ($total > $this->singleResponseHorizontalMax) //change if too many cats
			$rwidth = $this->singleResponseVerticalAreaWidthSmall;
		else		
			$rwidth = $this->singleResponseVerticalAreaWidth;

		$textwidth = ($this->getMainPageWidth() - $this->skipColumnWidth) - ($rwidth * $total);


		//First draw a background of height $this->responseLabelHeight
		$html = "<div></div>";
		$this->setBackground('question');
		$this->writeHTMLCell($this->getMainPageWidth(), $this->responseLabelHeight, $this->getMainPageX(), $currentY , $this->style . $html,0,1,true,true);

		$this->setDefaultFont($this->responseLabelFontSize);			

		$count = 0;
		//Draw a Cell for each rwidth from $textwidth + $this->getMainPageX(),currentY 
		foreach ($categories as $r)
		{
			$y = $currentY;
			$x = ($textwidth + $this->getMainPageX() + ($rwidth * $count));
			$this->MultiCell($rwidth,$this->responseLabelHeight,$r['text'],0,'C',false,0,$x,$y,true,0,false,true,$this->responseLabelHeight,'B',true);
			$count++;
		}
		$currentY += $this->responseLabelHeight;

		//reset font size


		foreach ($subquestions as $s)
		{
			//Add box group to current layout
			if ($parenttext == false)
				$this->addBoxGroup(1,$s['varname'],$s['text']);
			else				
				$this->addBoxGroup(1,$s['varname'],$parenttext . $this->subQuestionTextSeparator . $s['text']);

			//$html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\">" . $s['text'] . "</td><td></td></tr></table>";
			//$this->writeHTMLCell($this->getMainPageWidth(), $this->singleResponseAreaHeight, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);

			//Draw background
			$html = "<div></div>";
			$this->setBackground('question');
			$this->writeHTMLCell($this->getMainPageWidth(), $this->singleResponseAreaHeight, $this->getMainPageX(), $currentY, $this->style . $html,0,1,true,true);	
			$this->setDefaultFont($this->responseTextFontSize);			

			$this->MultiCell($textwidth,$this->singleResponseAreaHeight,$s['text'],0,'R',false,0,$this->getMainPageX(),$currentY,true,0,false,true,$this->singleResponseAreaHeight,'M',true);





			//Draw the categories horizontally
			$rnum = 1;
			foreach ($categories as $r)
			{
				if ($total == 1) $num = 'only';
				else if ($rnum == 1) $num = 'first';
				else if ($rnum < $total) $num = 'middle';
				else if ($rnum == $total) $num = 'last';

				$position = $this->drawHorizontalResponseBox(($this->getMainPageX() + $textwidth + (($rnum - 1) * $rwidth)),$currentY, $num,false,false,($total > $this->singleResponseHorizontalMax));
	
				//Add box to the current layout
				$this->addBox($position[0],$position[1],$position[2],$position[3],$r['value'],$r['text']);

				$rnum++;
			}

			if (($this->GetY() - $currentY) > $this->singleResponseAreaHeight)
				$currentY = $this->GetY();
			else
				$currentY = $currentY + $this->singleResponseAreaHeight;

			$this->SetY($currentY,false);

		}

	}

	/**
	 * Draw a vertical table of single choice responses including "eye guides"
	 * 
	 * @param array $categories An array containing the category text, value, skipto and other
	 * @param array $subquestions An array containing the subquestions if any
	 * @param string|bool $parenttext The question text of the parent or false if not specified
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-02
	 */
	protected function drawSingleChoiceVertical($categories, $subquestions = array(array('text' => '')),$parenttext = false)
	{
		$currentY = $this->GetY();
		$total = count($subquestions);

		$rwidth = $this->singleResponseAreaWidth;

		$textwidth = ($this->getMainPageWidth() - $this->skipColumnWidth) - ($rwidth * $total);

		if (count($categories) > 1)
		{
			$isempty = true;
			$count = 0;

			//First draw a background of height $this->responseLabelHeight
			$html = "<div></div>";
			$this->setBackground('question');
			$this->writeHTMLCell($this->getMainPageWidth(), $this->responseLabelHeight, $this->getMainPageX(), $currentY , $this->style . $html,0,1,true,true);


			$this->setDefaultFont($this->responseLabelFontSize);			

			//Draw a Cell for each rwidth from $textwidth + $this->getMainPageX(),currentY 
			foreach ($subquestions as $r)
			{
				$y = $currentY;
				$x = ($textwidth + $this->getMainPageX() + ($rwidth * $count));
				$this->MultiCell($rwidth,$this->responseLabelHeight,$r['text'],0,'C',false,0,$x,$y,true,0,false,true,$this->responseLabelHeight,'B',true);
				if (!empty($r['text'])) $isempty = false;
				$count++;
			}

			if ($isempty)
				$this->SetY($currentY,false);
			else
				$this->SetY($currentY+$this->responseLabelHeight);

		}

		$currentY = $this->GetY();
		$firstY = $currentY;

		$snum = 0;
		$total = count($categories);
		$ypos = array();

		foreach($subquestions as $s)
		{
			$rnum = 1;

			$this->SetY($firstY, false);
			$currentY = $firstY;
			
			if ($parenttext == false)
				$this->addBoxGroup(1,$s['varname'],$s['text']);
			else
				$this->addBoxGroup(1,$s['varname'],$parenttext . $this->subQuestionTextSeparator . $s['text']);

			$x = $this->getMainPageX() + $textwidth + ($rwidth * $snum) + ((($rwidth - $this->singleResponseBoxWidth) / 2.0 ));

			$other = false;

			foreach($categories as $r)
			{
				if ($total == 1) $num = 'only';
				else if ($rnum == 1) $num = 'first';
				else if ($rnum < $total) $num = 'middle';
				else if ($rnum == $total) $num = 'last';

				//add a new line for each response that goes to
				if ($snum == 0)
				{
					//only have to do this once
					//Draw background
					$html = "<div></div>";
					$this->setBackground('question');
					$this->writeHTMLCell($this->getMainPageWidth(), $this->singleResponseAreaHeight, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);	
					$this->setDefaultFont($this->responseTextFontSize);			

					$this->MultiCell($textwidth,$this->singleResponseAreaHeight,$r['text'],0,'R',false,0,$this->getMainPageX(),$currentY,true,0,false,true,$this->singleResponseAreaHeight,'M',true);

				}

				$skipto = false;
				$other = false;

				if (isset($r['skipto'])) $skipto = $r['skipto'];
				if (isset($r['other']) && $rnum == $total) $other = $r['other']; //only set for last in set

				//Draw the box over the top
				$position = $this->drawVerticalResponseBox($x,$currentY, $num, $other, $skipto);

				//Add box to the current layout
				$this->addBox($position[0],$position[1],$position[2],$position[3],$r['value'],$r['text']);

				//Store ypos for next round of boxes
				if ($snum == 0)
				{
					if (($this->GetY() - $currentY) > $this->singleResponseAreaHeight)
						$currentY = $this->GetY();
					else
						$currentY = $currentY + $this->singleResponseAreaHeight;
		
					$ypos[$rnum] = $currentY;
				}
				else
					$currentY = $ypos[$rnum];
				
				$this->SetY($currentY,false);

				$rnum++;
			}

			if ($other !== false)
			{
				//Display the "other" variable
				$this->addBoxGroup(3,$other['varname'],$other['text'],$other['width']);	
				$this->drawText($other['text'],$other['width']);
				//Insert a gap here
				$this->Rect($this->getMainPageX(),$this->GetY(),$this->getMainPageWidth(),$this->subQuestionLineSpacing,'F',array(),$this->backgroundColourQuestion);
				$this->SetY($this->GetY() + $this->subQuestionLineSpacing,false);
			}

			$snum++;
		}
	}

	/**
	 * Draw the header of a question (question title, text and help text if any)
	 *
	 * @param string $title The question title (number)
	 * @param string $text The question text (can be HTML)
	 * @param string|bool $help The question help text or false if none (can be HTML)
	 */
	protected function drawQuestionHead($title,$text,$help = false)
	{
		$this->setBackground('question');
		//Cell for question number (title) and text including a white border at the bottom

		$html = "<table><tr><td class=\"questionTitle\" width=\"" . $this->questionTitleWidth . "mm\">$title</td><td class=\"questionText\" width=\"" . $this->questionTextWidth . "mm\">$text</td><td></td></tr></table>";

		$this->writeHTMLCell($this->getMainPageWidth(), 1, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);

		if ($help != false)
		{
			$html = "<table><tr><td width=\"" . ($this->getMainPageWidth() -  $this->skipColumnWidth) . "mm\" class=\"questionHelp\">$help</td><td></td></tr></table>";
			$this->writeHTMLCell($this->getMainPageWidth(), 1, $this->getMainPageX(), $this->GetY(), $this->style . $html,0,1,true,true);

		}
	}

	/**
	 * Add a new section to the page
	 *
	 * @param string $text The text of the section
	 * @param string $desc The description of this section
	 * @param string $info Information for this section
	 */
	protected function addSection($desc = 'queXMLPDF Section',$title = false,$info = false)
	{
		$this->sectionCP++;

		if ($title === false)
			$title = $this->sectionCP;

		$this->section[$this->sectionCP] = array('label' => $desc, 'title' => $title);

		$html = "<span class=\"sectionTitle\">$title:</span>&nbsp;<span class=\"sectionDescription\">$desc</span>";

		if ($info && !empty($info))
			$html .= "<div class=\"sectionInfo\">$info</div>";

		$this->setBackground('section');
		$this->writeHTMLCell($this->getPageWidth() - (($this->cornerBorder *2) + ($this->cornerWidth * 2)),$this->sectionHeight,$this->getMainPageX(),$this->getY(),$this->style . $html,array('B' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->backgroundColourEmpty)),1,true,true,'');
	}

	/**
	 * Convert mm to pixels based on the set ppi (dpi)
	 *
	 * @param float $mm Measurement in millimetres
	 * @return int Pixel value as an integer
	 */
	public function mm2px($mm)
	{
		return round($mm * ($this->ppi / self::INCH_IN_MM));
	}

	/**
	 * Draw the background from the current Y position to the bottom of the page
	 * 
	 * @param bool $last Optional, defaults to false.  If this is the last page
	 * 
	 * @author Adam Zammit <adam.zammit@acspri.org.au>
	 * @since  2010-09-15
	 */
	protected function fillPageBackground($last = false)
	{
		$height = $this->getPageHeight() - $this->cornerBorder - $this->GetY() + $this->questionBorderBottom;
		$html = "<div></div>";
		$this->setBackground('question');
		$this->writeHTMLCell($this->getMainPageWidth(), $height, $this->getMainPageX(), $this->GetY() - $this->questionBorderBottom, $this->style . $html,0,1,true,true);
	}

	/**
	 * Create a new queXML PDF page
	 *
	 * Draw the barcode and page corners
	 * 
	 */
	protected function newPage() 
	{
		$this->AddPage();

		//Set Auto page break to false 
		$this->SetAutoPageBreak(false);

		$this->SetMargins(0,0,0);
		$this->SetHeaderMargin(0);
		$this->SetFooterMargin(0);

		//Shortcuts to make the code (a bit) nicer
		$width = $this->getPageWidth();
		$height = $this->getPageHeight();
		$cb = $this->cornerBorder;
		$cl = $this->cornerLength;
	
		$barcodeStyle = array('border' => false, 'padding' => '0', 'fgcolor' => $this->lineColour, 'bgcolor' => false, 'text' => false, 'stretch' => true);
		$lineStyle = array('width' => $this->cornerWidth, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		
		//Top left
		$this->Line($cb,$cb,$cb + $cl,$cb,$lineStyle);
		$this->Line($cb,$cb,$cb,$cb + $cl,$lineStyle);
		
		//Top right
		$this->Line($width - $cb,$cb,$width - $cb - $cl,$cb,$lineStyle);
		$this->Line($width - $cb,$cb,$width - $cb,$cb + $cl,$lineStyle);
		
		//Bottom left
		$this->Line($cb,$height - $cb,$cb + $cl,$height - $cb,$lineStyle);
		$this->Line($cb,$height - $cb,$cb,$height - ($cb + $cl),$lineStyle);
		
		//Bottom right
		$this->Line($width - $cb,$height - $cb,$width - $cb - $cl,$height - $cb,$lineStyle);
		$this->Line($width - $cb,$height - $cb,$width - $cb,$height - ($cb + $cl),$lineStyle);

		$barcodeValue = str_pad($this->questionnaireId,$this->idLength,"0",STR_PAD_LEFT) . str_pad($this->getPage(),$this->pageLength,"0",STR_PAD_LEFT);	

		$this->write1DBarcode($barcodeValue, $this->barcodeType, $this->barcodeX, $this->barcodeY, $this->barcodeW, $this->barcodeH,'', $barcodeStyle, 'N');
	
		//Add this page to the layout system
		$b = $this->cornerBorder + ($this->cornerWidth / 2.0); //temp calc for middle of line
		$this->layout[$barcodeValue] = array(	'id' => $barcodeValue,
							'tlx' => $this->mm2px($b),
							'tly' => $this->mm2px($b),
							'trx' => $this->mm2px($width - $b),
							'try' => $this->mm2px($b),
							'brx' => $this->mm2px($width - $b),
							'bry' => $this->mm2px($height - $b),
							'blx' => $this->mm2px($b),
							'bly' => $this->mm2px($height - $b),
							'rotation' => 0,
							'boxgroup' => array()
							);
		$this->layoutCP = $barcodeValue;

		$this->SetXY($cb + $this->cornerWidth, $cb + $this->cornerWidth);
		$this->SetAutoPageBreak(true,$this->getMainPageX());
	}

	/**
	 * Override of the acceptPageBreak function
	 *
	 * Allow our page handling function to know that a page break has occured
	 *
	 * $return bool Returns false so no page break is automatically issued
	 */
	public function AcceptPageBreak()
	{
		$this->pageBreakOccured = true;
		return FALSE;
	}
}
