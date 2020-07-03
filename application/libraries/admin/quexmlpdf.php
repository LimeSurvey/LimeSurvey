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
*  $Id$
*/

/**
* Modify these two lines to point to your TCPDF installation
* Tested with TCPDF 5.8.008 - see http://www.tcpdf.org/
*/
require('pdf.php');
require_once($tcpdf['base_directory'].'/tcpdf.php');

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
class quexmlpdf extends pdf
{

    /**
     * Define an inch in MM
     *
     * @const float Defaults to 25.4
     */
    const INCH_IN_MM = 25.4;

    /**
     * Language for translation
     */
    protected $language = "en";

    /**
     * Pixels per inch of exported document
     *
     * @var int Defaults to 300.
     */
    protected $ppi = 300;

    /**
     * Whether a page break has occured
     * Should be a private var but crash occurs on PHP 5.1.6, see Limesurvey Bug 5824
     * @var bool
     */
    protected $pageBreakOccured;

    /**
     * Corner border (the number of mm between the edge of the page and the start of the document)
     *
     * @var int  Defaults to 15.
     * @since 2010-09-02
     */
    protected $cornerBorder = 14;

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
     * The width in MM of a corner box
     * 4.57mm is approx 54 pixels at 300dpi
     *
     * @var float Defaults to 4.57
     * @since 2014-12-22
     */
    protected $cornerBoxWidth = 4.57;

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
     * @deprecated
     * @see $barcodeMarginX
     */
    //protected $barcodeX = 138;

    /**
     * The distance between the right hand page border and
     * the end of the barcode in MM
     *
     * @var bool  Defaults to 23.
     * @since 2011-10-25
     */
    protected $barcodeMarginX = 23;

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
     * The suffix of the question title. i.e. A15. (the . is the suffix)
     *
     * @var mixed  Defaults to ".".
     * @since 2012-01-31
     */
    protected $questionTitleSuffix = ".";

    /**
     * Width of question text in MM
     *
     * @var mixed  Defaults to 120.
     * @since 2010-09-20
     * @deprecated
     */
    //protected $questionTextWidth = 120;

    /**
     * Right margin of question text in MM
     *
     * @var mixed  Defaults to 40.
     * @since 2012-01-11
     * @see $questionTextWidth
     */
    protected $questionTextRightMargin = 40;

    /**
     * Height of the border between questions in MM
     *
     * @var mixed  Defaults to 1.
     * @since 2010-09-20
     */
    protected $questionBorderBottom = 1;

    /**
     * Border after a help before directive
     *
     * @var mixed  Defaults to 3.
     * @since 2012-01-31
     */
    protected $helpBeforeBorderBottom = 3;

    /**
     * Border before a help before directive
     *
     * @var mixed  Defaults to 3.
     * @since 2012-01-31
     */
    protected $helpBeforeBorderTop = 3;

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
    protected $style = "<style>
    td.questionTitle {font-weight:bold; font-size:12pt;}
    td.questionTitleSkipTo {font-weight:bold; font-size:16pt;}
    td.questionText {font-weight:bold; font-size:12pt;}
    td.questionSpecifier {font-weight:normal; font-size:12pt;}
    td.vasLabel {font-weight:bold; font-size:10pt; text-align:center;}
    td.questionHelp {font-weight:normal; text-align:right; font-style:italic; font-size:8pt;}
    td.questionHelpAfter {text-align:center; font-weight:bold; font-size:10pt;}
    td.questionHelpBefore {text-align:center; font-weight:bold; font-size:12pt;}
    td.responseAboveText {font-weight:normal; font-style:normal; text-align:left; font-size:12pt;}
    td.matrixResponseGroupLabel {font-weight:normal; font-style:normal; text-align:left; font-size:12pt;}
    span.sectionTitle {font-size:18pt; font-weight:bold;}
    span.sectionDescription {font-size:14pt; font-weight:bold;}
    div.sectionInfo {font-style:normal; font-size:10pt; text-align:left; font-weight:normal;}
    td.questionnaireInfo {font-size:14pt; text-align:center; font-weight:bold;}
    </style>";

    /**
     * Width of the area of each single response
     *
     * @var string  Defaults to 10.
     * @since 2010-09-20
     * Height of the area of a single response where displayed horizontally
     *
     * @var string  Defaults to 10.5.
     * @since 2011-12-20
     */
    protected $singleResponseHorizontalHeight = 10.5;

    /**
     * The maximum number of lines of text to display
     * in a horizontal single response before adding additional space
     *
     * @var string  Defaults to 2.
     * @since 2013-05-02
     * @see $singleResponseHorizontalHeight
     */
    protected $singleResponseHorizontalMaxLines = 2;

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
    protected $singleResponseVerticalAreaWidth = 13;

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
     * Allows all single choice horizontal arrays to be split over multiple pages/columns
     * Can override with "split" attribute on "response" in queXML
     *
     * @var bool  Defaults to false.
     * @since 2012-08-10
     */
    protected $allowSplittingSingleChoiceHorizontal = true;

    /**
     * Allows all single choice vertical arrays to be split over multiple pages/columns
     * Can override with "split" attribute on "response" in queXML
     *
     * @var bool  Defaults to false.
     * @since 2013-10-24
     */
    protected $allowSplittingSingleChoiceVertical = true;

    /**
     * If splitting is allowed for single choice vertical, only split if there is at
     * least these many categories
     *
     * @var int  Defaults to 5.
     * @since 2013-12-13
     */
    protected $minSplittingSingleChoiceVertical = 25;

    /**
     * Allows multiple responses to the same question to be split over multiple pages/columns
     * Can override with "split" attribute on "question" in queXML
     *
     * @var bool  Defaults to false.
     * @since 2013-10-25
     */
    protected $allowSplittingResponses = true;

    /**
     * Allows vertical matrix texts to be split over multiple pages/columns
     * Can override with "split" attribute on "response" in queXML
     *
     * @var bool  Defaults to false.
     * @since 2013-10-25
     */
    protected $allowSplittingMatrixText = true;

    /**
     * Allows matrix VAS items to be split over multiple pages/columns
     * Can override with "split" attribute on "response" in queXML
     *
     * @var bool  Defaults to false.
     * @since 2013-10-25
     */
    protected $allowSplittingVas = false;

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
     * The border width of a text response box
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
     * The height of a pre-filled response barcode
     *
     * @var bool  Defaults to 6.
     * @since 2012-06-22
     */
    protected $barcodeResponseHeight = 6;

    /**
     * The maximum number of text responses per line
     *
     * @var mixed  Defaults to 24.
     * @since 2010-09-20
     * @deprecated
     * @see $textResponseMarginX
     */
    //protected $textResponsesPerLine = 24;

    /**
     * The left hand margin of text responses to auto calculate responses
     * per line (mm)
     *
     * @var mixed  Defaults to 13.
     * @since 2011-10-25
     */
    protected $textResponseMarginX = 13;

    /**
     * Maximum number of text responses boxes where the label should appear on the same line
     *
     * @var mixed  Defaults to 16.
     * @since 2010-09-20
     * @deprecated
     * @see $labelTextResponsesSameLineMarginX
     */
    //protected $labelTextResponsesSameLine = 16;

    /**
     * The left hand margin of text responses to auto calculated responses
     * per line where the label should appear on the same line (mm)
     *
     * @var mixed  Defaults to 62.
     * @since 2011-10-25
     */
    protected $labelTextResponsesSameLineMarginX = 62;

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
     * @deprecated
     * @see drawLongText() for the new calculation of long text box width
     */
    //protected $longTextResponseWidth = 145;

    /**
     * Default number of characters to store in a long text field
     *
     * @var int Default is 1024;
     * @since 2010-09-02
     */
    protected $longTextStorageWidth = 1024;

    /**
     * The number of columns to display the sections/questions in on each page
     *
     * @var int  Defaults to 1.
     * @since 2012-05-30
     */
    protected $columns = 1;

    /**
     * The width of the border between columns
     *
     * @var int  Defaults to 1.
     * @since 2012-05-31
     */
    protected $columnBorder = 1;

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
     * An array of key: skip target, value: last originating question
     * that skips to the target
     *
     * @var string  Defaults to array().
     * @since 2012-01-31
     */
    protected $skipToRegistry = array();

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
     * Column counter pointer (current column)
     *
     * @var mixed  Defaults to 0.
     * @since 2012-05-30
     */
    protected $columnCP = 0;

    /**
     * Background colour of a question
     *
     * @var bool  Defaults to array(220,220,220).
     * @since 2010-09-15
     */
    protected $backgroundColourQuestion = array(241);

    /**
     * The bacground colour of a section
     *
     * @var bool  Defaults to array(200,200,200).
     * @since 2010-09-20
     */
    protected $backgroundColourSection = array(221);

    /**
     * Empty background colour
     *
     * @var bool  Defaults to array(255,255,255).
     * @since 2010-09-20
     */
    protected $backgroundColourEmpty = array(255);

    /**
     * The colour of a line/fill
     *
     * @var mixed  Defaults to array(0,0,0).
     * @since 2010-09-20
     */
    protected $lineColour = array(0);

    /**
     * Text colour in grayscale
     *
     * @var mixed  Defaults to 0.
     * @since 2012-04-16
     */
    protected $textColour = 0;


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
     * The width of a line for the default value
     *
     * @var double  Defaults to 0.5.
     * @since 2012-06-14
     */
    protected $defaultValueLineWidth = 0.5;

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
    protected $questionnaireInfoMargin = 5;

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
    protected $responseLabelFontSize = 7.5;

    /**
     * A smaller font size for response labels where otherwise will break the line
     *
     * @var resource  Defaults to 6.
     * @since 2012-03-30
     */
    protected $responseLabelFontSizeSmall = 6.5;

    /**
     * Reduce the font size of a response label if any words are longer than this
     *
     * @var resource  Defaults to 7.
     * @since 2012-03-30
     */
    protected $responseLabelSmallWordLength = 7;

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

    public function setLanguage($language)
    {
        if (!empty($language)) {
            $this->language = $language;
        }
    }

    /**
     * Use corner lines (default) or corner boxes
     *
     * @var bool Defaults to true
     * @since 2014-12-22
     */
    protected $cornerLines = true;

    /**
     * Return the length of the longest word
     *
     * @param mixed $txt
     *
     * @return int Length of longest word
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-03-30
     */
    protected function wordLength($txt)
    {
        $words = explode(' ', $txt);
        $length = 0;
        foreach ($words as $v) {
            if (strlen($v) > $length) {
                $length = strlen($v);
            }
        }
        return $length;
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
    protected function addBoxGroup($type, $varname, $label = "", $width = 1)
    {
        $this->boxGroupCP++;
        $this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP] =
        array('type' => $type,
            'width' => $width,
            'varname' => $varname,
            'sortorder' => $this->boxGroupCP,
            'label' => $label,
            'groupsection' => $this->sectionCP,
            'box' => array());
    }

    /**
     * Add a new box group which is a copy of the previous one (if exists)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-03-26
     */
    protected function addBoxGroupCopyPrevious()
    {
        if (isset($this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP])) {
            $a = $this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP];
            $this->addBoxGroup($a['type'], $a['varname'], $a['label']);
        }
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
    protected function addBox($tlx, $tly, $brx, $bry, $value = "", $label = "")
    {
        $this->boxCP++;
        $this->layout[$this->layoutCP]['boxgroup'][$this->boxGroupCP]['box'][] =
        array('tlx' => $this->mm2px($tlx),
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

        switch ($type) {
            case 1: //Single choice
            case 2: //Multiple choice
                if (strlen($value) > $width) {
                    $width = strlen($value);
                }
                if (strlen($count) > $width) {
                    $width = strlen($count);
                }
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
     * Set margin before questionnare info
     * 
     * @param int $margin between 0 and 100mm
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setQuestionnaireInfoMargin($margin)
    {
        $margin = floatval($margin);
        if ($margin >= 0 && $margin <= 100) {
            $this->questionnaireInfoMargin = $margin;
        }
    }

    /**
     * Get the margin before questionnaire info
     * 
     * @return int Height in mm between 0 and 100
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getQuestionnaireInfoMargin()
    {
        return $this->questionnaireInfoMargin;
    }

    /**
     * Set the height of responses items in a sub question matrix
     * 
     * @param int $height Height between 1 and 100mm
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setSingleResponseHorizontalHeight($height)
    {
        $height = floatval($height);
        if ($height >= 1 && $height <= 100) {
            $this->singleResponseHorizontalHeight = $height;
        }
    }

    /**
     * Get the height of responses in a sub question matrix
     * 
     * @return string Height in mm between 1 and 100
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getSingleResponseHorizontalHeight()
    {
        return $this->singleResponseHorizontalHeight;
    }

    /**
     * Set vertical height of a single response item
     * 
     * @param int $height Height between 1 and 100mm
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setSingleResponseAreaHeight($height)
    {
        $height = floatval($height);
        if ($height >= 1 && $height <= 100) {
            $this->singleResponseAreaHeight = $height;
        }
    }

    /**
     * Get vertical height of a single response item
     * 
     * @return string Height in mm between 1 and 100
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getSingleResponseAreaHeight()
    {
        return $this->singleResponseAreaHeight;
    }

    /**
     * Set background colour for a question
     * 
     * @param int $colour Background colour between 0 and 255
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setBackgroundColourQuestion($colour)
    {
        $colour = intval($colour);
        if ($colour >= 0 && $colour <= 255) {
            $this->backgroundColourQuestion = array($colour);
        }
    }

    /**
     * Get background colour for a question
     * 
     * @return int Background colour between 0 and 255
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getBackgroundColourQuestion()
    {
        return $this->backgroundColourQuestion[0];
    }

    /**
     * Set background colour for a section
     * 
     * @param int $colour Background colour between 0 and 255
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setBackgroundColourSection($colour)
    {
        $colour = intval($colour);
        if ($colour >= 0 && $colour <= 255) {
            $this->backgroundColourSection = array($colour);
        }
    }

    /**
     * Get background colour for a section
     * 
     * @return int Background colour between 0 and 255
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getBackgroundColourSection()
    {
        return $this->backgroundColourSection[0];
    }

    /**
     * Set allow splitting
     *
     * @param bool $allow Whether to allow or not (default true)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setAllowSplittingSingleChoiceVertical($allow = true)
    {
        if ($allow) {
            $this->allowSplittingSingleChoiceVertical = true;
        } else {
            $this->allowSplittingSingleChoiceVertical = false;
        }
    }

    /**
     * Get allow splitting
     *
     * @return bool Whether to allow or not
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getAllowSplittingSingleChoiceVertical()
    {
        return $this->allowSplittingSingleChoiceVertical;
    }

    /**
     * Set allow splitting
     *
     * @param bool $allow Whether to allow or not (default true)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setAllowSplittingSingleChoiceHorizontal($allow = true)
    {
        if ($allow) {
            $this->allowSplittingSingleChoiceHorizontal = true;
        } else {
            $this->allowSplittingSingleChoiceHorizontal = false;
        }
    }

    /**
     * Get allow splitting
     *
     * @return bool Whether to allow or not
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getAllowSplittingSingleChoiceHorizontal()
    {
        return $this->allowSplittingSingleChoiceHorizontal;
    }

    /**
     * Set allow splitting
     *
     * @param bool $allow Whether to allow or not (default true)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setAllowSplittingVas($allow = true)
    {
        if ($allow) {
            $this->allowSplittingVas = true;
        } else {
            $this->allowSplittingVas = false;
        }
    }

    /**
     * Get allow splitting
     *
     * @return bool Whether to allow or not
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getAllowSplittingVas()
    {
        return $this->allowSplittingVas;
    }

    /**
     * Set allow splitting
     *
     * @param bool $allow Whether to allow or not (default true)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setAllowSplittingMatrixText($allow = true)
    {
        if ($allow) {
            $this->allowSplittingMatrixText = true;
        } else {
            $this->allowSplittingMatrixText = false;
        }
    }

    /**
     * Get allow splitting
     *
     * @return bool Whether to allow or not
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getAllowSplittingMatrixText()
    {
        return $this->allowSplittingMatrixText;
    }

    /**
     * Set allow splitting
     *
     * @param bool $allow Whether to allow or not (default true)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function setAllowSplittingResponses($allow = true)
    {
        if ($allow) {
            $this->allowSplittingResponses = true;
        } else {
            $this->allowSplittingResponses = false;
        }
    }

    /**
     * Get allow splitting
     *
     * @return bool Whether to allow or not
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2013-10-25
     */
    public function getAllowSplittingResponses()
    {
        return $this->allowSplittingResponses;
    }

    /**
     * Set the minimum section height
     *
     * @param int $height The minimum height of a section
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-07-30
     */
    public function setSectionHeight($height)
    {
        $height = intval($height);
        if ($height < 0) {
            $height = 1;
        }
        $this->sectionHeight = $height;
    }

    /**
     * Get the section height
     *
     * @return string section height
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-07-30
     */
    public function getSectionHeight()
    {
        return $this->sectionHeight;
    }

    /**
     * Get the response label font sizes normal
     *
     * @return resource font size
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function getResponseLabelFontSize()
    {
        return $this->responseLabelFontSize;
    }

    /**
     * Set the response label normal font size
     *
     * @param normal font size
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function setResponseLabelFontSize($normalsize)
    {
        $this->responseLabelFontSize = floatval($normalsize);
    }

    /**
     * Set the response label small font size
     *
     * @param small font size
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function setResponseLabelFontSizeSmall($smallsize)
    {
        $this->responseLabelFontSizeSmall = floatval($smallsize);
    }

    /**
     * Get the response label font size small
     *
     * @return resource font size
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function getResponseLabelFontSizeSmall()
    {
        return $this->responseLabelFontSizeSmall;
    }

    /**
     * Get the response text font size
     *
     * @return resource The response text font size
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function getResponseTextFontSize()
    {
        return $this->responseTextFontSize;
    }

    /**
     * Set the response text font size
     *
     * @param int $size
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function setResponseTextFontSize($size)
    {
        $this->responseTextFontSize = floatval($size);
    }

    /**
     * Get the style without any HTML/etc formatting
     *
     * @return string The style without HTML or tabs
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function getStyle()
    {
        return strip_tags(str_replace("\t", "", $this->style));
    }

    /**
     * Set the CSS styling of some questionnaire elements
     *
     * @param string $style The CSS styling of some questionnire elements
     *
     * @return none
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-04-10
     */
    public function setStyle($style)
    {
        $this->style = "<style>".$style."</style>";
    }

    /**
     * Set whether to use corner lines
     *
     * @return none
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2014-12-22
     */
    public function setCornerLines()
    {
        $this->cornerLines = true;
    }

    /**
     * Set whether to use corner boxes
     *
     * @return none
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2014-12-22
     */
    public function setCornerBoxes()
    {
        $this->cornerLines = false;
    }

    /**
     * Wrapper function for setCornerBoxes and setCornerLines methods
     * @return none
     * @author A A D V S Abeysinghe <venura@acspri.org.au>
     * @param type $format lines or boxes
     * @since 2015-07-08
     */
    public function setEdgeDetectionFormat($format)
    {
        if ($format === 'lines') {
            $this->cornerLines = true;
        } else if ($format === 'boxes') {
            $this->cornerLines = false;
        }
    }

    /**
     * Get whether to use corner lines
     *
     * @return bool whether to use corner lines
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2014-12-22
     */
    public function getCornerLines()
    {
        return $this->cornerLines;
    }

    /**
     * Get whether to use corner boxes
     *
     * @return bool whether to use corner boxes
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2014-12-22
     */
    public function getCornerBoxes()
    {
        return !$this->cornerLines;
    }

    /**
     * Wrapper function for getCornerBoxes and getCornerLines methods
     * @return string whether to use corner lines or boxes
     * @author A A D V S Abeysinghe <venura@acspri.org.au>
     * @since 2015-07-08
     */
    public function getEdgeDetectionFormat()
    {
        $value = '';
        if ($this->getCornerLines()) {
            $value = 'lines';
        } else {
            $value = 'boxes';
        }
        return $value;
    }

    /**
     * Get page format
     *
     * @return string page format
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2015-06-19
     */
    public function getPageFormat()
    {
        return 'A4';
    }

    /**
     * Set page format
     *
     * @param string $format page format
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2015-06-19
     */
    public function setPageFormat($format, $orientation = '')
    {
        parent::setPageFormat($format, $orientation);
    }

    /**
     * Get page orientation
     *
     * @return string page orientation
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2015-06-19
     */
    public function getPageOrientation()
    {
        return $this->CurOrientation;
    }

    /**
     * Set page orientation
     *
     * @param string $orientation page orientation
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since 2015-06-19
     */
    public function setPageOrientation($orientation, $autopagebreak = '', $bottommargin = '')
    {
        parent::setPageOrientation($orientation, $autopagebreak, $bottommargin);
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

        foreach ($this->section as $key => $val) {
            $s = $doc->createElement('section');
            $s->setAttribute('id', $key);
            foreach ($val as $sk => $sv) {
                $tmpe = $doc->createElement($sk);
                $tmpv = $doc->createTextNode($sv);
                $tmpe->appendChild($tmpv);
                $s->appendChild($tmpe);
            }
            $q->appendChild($s);
        }
        foreach ($this->layout as $key => $val) {
            $p = $doc->createElement('page');

            foreach ($val as $pk => $pv) {
                if ($pk != 'boxgroup') {
                    $tmpe = $doc->createElement($pk);
                    $tmpv = $doc->createTextNode($pv);
                    $tmpe->appendChild($tmpv);
                    $p->appendChild($tmpe);
                }
            }

            foreach ($val['boxgroup'] as $bg) {
                $bgE = $doc->createElement('boxgroup');
                foreach ($bg as $pk => $pv) {
                    if ($pk == 'groupsection') {
                        $gs = $doc->createElement('groupsection');
                        $gs->setAttribute('idref', $pv);
                        $bgE->appendChild($gs);
                    } else if ($pk != 'box') {
                        $tmpe = $doc->createElement($pk);
                        $tmpv = $doc->createTextNode($pv);
                        $tmpe->appendChild($tmpv);
                        $bgE->appendChild($tmpe);
                    }
                }

                foreach ($bg['box'] as $b) {
                    $bE = $doc->createElement('box');
                    foreach ($b as $bk => $bv) {
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
     * @param integer $size  Optional, defaults to 12
     * @param string $style Optional, defaults to ''.
     *
     * @return TODO
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-11-05
     */
    protected function setDefaultFont($size = 12, $style = '')
    {
        $alternatepdffontfile = Yii::app()->getConfig('alternatepdffontfile');
        if (array_key_exists($this->language, $alternatepdffontfile)) {
            $this->SetFont($alternatepdffontfile[$this->language], $style);
        } else {
            $this->SetFont($this->defaultFont, $style);
        }
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
        if ($this->embedFonts) {
            $this->setFontSubsetting(false); //we want full subsetting
            $this->AddFont('freesans', '');
            $this->AddFont('freesans', 'B');
            $this->AddFont('freesans', 'I');
            $this->AddFont('freesans', 'BI');
            $this->AddFont('freeserif', '');
            $this->AddFont('freeserif', 'B');
            $this->AddFont('freeserif', 'I');
            $this->AddFont('freeserif', 'BI');

            $this->setDefaultFont();
        }

        // set document information
        $this->SetCreator('queXMLPDF (http://quexml.sourceforge.net)');
        $this->SetAuthor('Adam Zammit <adam.zammit@acspri.org.au>');
        $this->SetTitle('queXML Document');
        $this->SetSubject('queXML');
        $this->SetKeywords('queXML queXF');

        //set text colour
        $this->SetTextColor($this->textColour);

        //set column pointer
        $this->columnCP = -1;

        //set corner lines values
        if (!$this->cornerLines) {
            $this->cornerWidth = 0;
        }

    }

    /**
     * Override of TCPDF Header function to blank
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-20
     */
    public function Header()
    {
    }

    /**
     * Override of TCPDF Footer function to blank
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     */
    public function Footer()
    {
    }

    /**
     * Set the background wash of the page
     *
     * @param string $type Optional, defaults to 'empty'.
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-02
     */
    protected function setBackground($type = 'empty')
    {
        switch ($type) {
            case 'question':
                $this->SetFillColor($this->backgroundColourQuestion[0]);
                break;
            case 'section':
                $this->SetFillColor($this->backgroundColourSection[0]);
                break;
            case 'empty':
                $this->SetFillColor($this->backgroundColourEmpty[0]);
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
    protected function getMainPageX()
    {
        return ($this->cornerBorder + $this->cornerWidth);
    }

    /**
     * The X coordinate of the start of the column
     *
     * @return double The X coordinate of the start of the current column
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-05-30
     */
    protected function getColumnX()
    {
        $border = 0;
        if ($this->columnCP > 0) {
            $border = $this->columnBorder;
        }
        return $this->getMainPageX() + ($this->columnCP * ($this->getColumnWidth() + $border)) + $border;
    }

    /**
     * The width of the writeable page
     *
     * @return double The width of the writeable page
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-02
     */
    protected function getMainPageWidth()
    {
        return ($this->getPageWidth() - (($this->cornerBorder * 2.0) + ($this->cornerWidth * 2.0)));
    }

    /**
     * The width of the writable column
     *
     * @return double The width of the current column
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-05-30
     */
    protected function getColumnWidth()
    {
        $border = 0;
        if ($this->columnCP > 0) {
            $border = $this->columnBorder;
        }
        return ((1 / $this->columns) * $this->getMainPageWidth()) - $border;
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
     * @param bool $filled Whether or not to have the box pre-filled
     *
     */
    protected function drawHorizontalResponseBox($x, $y, $position = 'only', $downarrow = false, $rightarrow = false, $smallwidth = false, $filled = false)
    {
        $this->SetDrawColor($this->lineColour[0]);
        $this->SetLineWidth($this->singleResponseBoxBorder);

        //centre for the line
        $boxmid = ($y + ($this->singleResponseHorizontalHeight / 2.0));

        //centre on y
        $y = $y + (($this->singleResponseHorizontalHeight - $this->singleResponseBoxHeight) / 2.0);

        if ($smallwidth) {
            $areawidth = $this->singleResponseVerticalAreaWidthSmall;
        } else {
            $areawidth = $this->singleResponseVerticalAreaWidth;
        }


        $linelength = (($areawidth - $this->singleResponseBoxWidth) / 2.0);

        $this->SetLineStyle(array('dash' => '1'));

        if ($position == 'last' || $position == 'middle') {
            $this->Line($x, $boxmid, $x + $linelength, $boxmid);
        }
        if ($position == 'first' || $position == 'middle') {
            $this->Line($x + $linelength + $this->singleResponseBoxWidth, $boxmid, $x + ($linelength * 2) + $this->singleResponseBoxWidth, $boxmid);
        }

        $this->SetLineStyle(array('dash' => '0'));

        $this->Rect($x + $linelength, $y, $this->singleResponseBoxWidth, $this->singleResponseBoxHeight, 'DF', array(), $this->backgroundColourEmpty);

        if ($downarrow) {
            $boxmiddle = ($x + ($this->singleResponseBoxWidth / 2.0)) + $linelength;
            $this->SetFillColor($this->lineColour[0]);
            $this->Polygon(array($x + $linelength, $y + $this->singleResponseBoxHeight, $boxmiddle, $y + $this->singleResponseBoxHeight + $this->arrowHeight, $x + $linelength + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight), 'DF', array(), $this->lineColour);
            $this->setBackground('empty');
        }

        if ($filled) {
            //draw a cross
            $this->SetLineWidth($this->defaultValueLineWidth);
            $this->Line($x + $linelength, $y, $x + $linelength + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight);
            $this->Line($x + $linelength + $this->singleResponseBoxWidth, $y, $x + $linelength, $y + $this->singleResponseBoxHeight);
        }

        $this->setBackground('question');
        return array($x + $linelength, $y, $x + $linelength + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight); //return the posistion for banding
    }

    /**
     * Draw a vertical response box with possible eye guides and arrows
     *
     * @param int $x The x position of the box area (top left)
     * @param int $y The y position of the box area (top left)
     * @param string $position What position the box is in for the eye guides
     * @param bool $downarrow Draw a down arrow?
     * @param bool $rightarrow Draw an arrow to the right?
     * @param bool $filled Whether or not to have the box pre-filled
     *
     */
    protected function drawVerticalResponseBox($x, $y, $position = 'only', $downarrow = false, $rightarrow = false, $filled = false)
    {
        $this->SetDrawColor($this->lineColour[0]);
        $this->SetLineWidth($this->singleResponseBoxBorder);

        if (!$downarrow) {
            $y = $y + (($this->singleResponseAreaHeight - $this->singleResponseBoxHeight) / 2.0);
        }

        $boxmid = ($x + ($this->singleResponseBoxWidth / 2.0));
        if ($position == 'first' || $position == 'middle') {
            $this->Line($boxmid, ($y + $this->singleResponseBoxHeight), $boxmid, ($y + $this->singleResponseBoxHeight + $this->singleResponseBoxLineLength));
        }
        if ($position == 'last' || $position == 'middle') {
            $this->Line($boxmid, $y, $boxmid, ($y - $this->singleResponseBoxLineLength));
        }

        if ($downarrow) {
            $this->SetFillColor($this->lineColour[0]);
            $this->Polygon(array($x, $y + $this->singleResponseBoxHeight, $boxmid, $y + $this->singleResponseBoxHeight + $this->arrowHeight, $x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight), 'DF', array(), $this->lineColour);
            $this->setBackground('empty');
        }

        if ($rightarrow !== false) {
            //Draw skipto
            $boxymid = ($y + ($this->singleResponseBoxHeight / 2.0));
            $this->SetFillColor($this->lineColour[0]);
            $this->Polygon(array($x + $this->singleResponseBoxWidth, $y, $x + $this->singleResponseBoxWidth + $this->arrowHeight, $boxymid, $x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight), 'DF', array(), $this->lineColour);
            $this->setBackground('empty');
            //Now draw the text

            //Start at $x + singleResponseboxWidth + arrowHeight, $y - siongleresponseboxlinelength and go to $skipcolumnwidth wide and singleresponseareHeight high
            $this->setBackground('question');
            $text = $this->skipToText.$rightarrow;
            $ypos = $this->GetY();

            $this->setDefaultFont($this->skipToTextFontSize, 'B');

            $this->MultiCell($this->skipColumnWidth, $this->singleResponseBoxHeight, $text, 0, 'L', false, 0, (($this->getColumnWidth() + $this->getColumnX()) - $this->skipColumnWidth), $y, true, 0, false, true, $this->singleResponseBoxHeight, 'M', true);

            //Reset to non bold as causing problems with TCPDF HTML CSS conversion
            $this->setDefaultFont($this->skipToTextFontSize, '');

            //$this->writeHTMLCell($this->skipColumnWidth, 0, $this->getPageWidth() - $this->getMainPageX() - $this->skipColumnWidth ,$y, $this->style . $html,0,0,true,true,'C',true);
            $this->SetY($ypos, false);
        }

        $this->Rect($x, $y, $this->singleResponseBoxWidth, $this->singleResponseBoxHeight, 'DF', array(), $this->backgroundColourEmpty);

        if ($filled) {
            //draw a cross
            $this->SetLineWidth($this->defaultValueLineWidth);
            $this->Line($x, $y, $x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight);
            $this->Line($x + $this->singleResponseBoxWidth, $y, $x, $y + $this->singleResponseBoxHeight);
        }

        $this->setBackground('question');
        return array($x, $y, $x + $this->singleResponseBoxWidth, $y + $this->singleResponseBoxHeight); //return the posistion for banding
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
        if ($number < 1) {
            $number = 1;
        }

        if ($number > 26) {
            return chr((($number - 1) / 26) + 64).chr((($number - 1) % 26) + 65);
        } else {
            return chr($number + 64);
        }
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
        App()->setLanguage($this->language);

        $xml = new SimpleXMLElement($quexml);

        $q = array();

        $scount = 1;
        $sl = "";

        $q['id'] = $xml['id'];

        foreach ($xml->questionnaireInfo as $qitmp) {
            if ($qitmp->position == 'after') {
                if (!isset($q['infoafter'])) {
                    $q['infoafter'] = "";
                }

                $q['infoafter'] .= $qitmp->text."<br/><br/>";
            } else if ($qitmp->position == 'before') {
                if (!isset($q['infobefore'])) {
                    $q['infobefore'] = "";
                }

                $q['infobefore'] .= $qitmp->text."<br/><br/>";
            }
        }

        foreach ($xml->section as $s) {
            $stmp = array();
            $sl = $this->numberToLetter($scount);
            if (isset($s['hidetitle']) && $s['hidetitle'] == "true") {
                $stmp['title'] = false;
            } else {
                $stmp['title'] = gT("Section")." ".$sl;
            }
            $stmp['info'] = "";
            $stmp['text'] = "";
            $bfc = 0;

            foreach ($s->sectionInfo as $sitmp) {
                if ($sitmp->position == 'title') {
                    $stmp['text'] .= $sitmp->text;
                }
                if ($sitmp->position == 'before' || $sitmp->position == 'during') {
                    $stmp['info'] .= $sitmp->text;

                    if ($bfc > 0) {
                        $stmp['info'] .= "<br/>";
                    }

                    $bfc++;
                }
            }

            if (isset($s['hideinfo']) && $s['hideinfo'] == "true") {
                $stmp['info'] = false;
            }

            $qcount = 1;
            foreach ($s->question as $qu) {
                $qtmp = array();
                $rstmp = array();

                $qtmp['split'] = 'notset';

                if (isset($qu['split'])) {
                    if (current($qu['split']) == "true") {
                        $qtmp['split'] = true;
                    } else {
                        $qtmp['split'] = false;
                    }
                }

                if (Yii::app()->getConfig('quexmlusequestiontitleasid')) {
                    $qtmp['title'] = ((string) $qu->response->attributes()->varName).$this->questionTitleSuffix;
                } else {
                    $qtmp['title'] = $sl.$qcount.$this->questionTitleSuffix;
                }

                if (isset($qu['hidetitle']) && $qu['hidetitle'] == "true") {
                    $qtmp['hidetitle'] = "true";
                }

                $qtmp['text'] = "";

                foreach ($qu->text as $ttmp) {
                    //Add a new line if we aren't at the end
                    if ($ttmp != end($qu->text)) { $qtmp['text'] .= "<br/>"; }

                    $qtmp['text'] .= $ttmp;
                }

                foreach ($qu->specifier as $ttmp) {
                    if (!isset($qtmp['specifier'])) {
                        $qtmp['specifier'] = "";
                    }

                    $qtmp['specifier'] .= $ttmp;
                }

                foreach ($qu->directive as $ttmp) {
                    if ($ttmp->administration == 'self' && $ttmp->position == 'during') {
                        if (!isset($qtmp['helptext'])) {
                            $qtmp['helptext'] = "";
                        }

                        $qtmp['helptext'] .= $ttmp->text;
                    }
                    if ($ttmp->administration == 'self' && $ttmp->position == 'after') {
                        if (!isset($qtmp['helptextafter'])) {
                            $qtmp['helptextafter'] = "";
                        }

                        $qtmp['helptextafter'] .= $ttmp->text;
                    }
                    if ($ttmp->administration == 'self' && $ttmp->position == 'before') {
                        if (!isset($qtmp['helptextbefore'])) {
                            $qtmp['helptextbefore'] = "";
                        }

                        $qtmp['helptextbefore'] .= $ttmp->text;
                    }
                }

                foreach ($qu->subQuestion as $sq) {
                    $sqtmp = array();
                    $sqtmp['text'] = "";
                    foreach ($sq->text as $ttmp) {
                        $sqtmp['text'] .= $ttmp;
                    }
                    $sqtmp['varname'] = $sq['varName'];

                    if (isset($sq['defaultValue'])) {
                        $sqtmp['defaultvalue'] = current($sq['defaultValue']);
                    }

                    if (isset($sq->contingentQuestion)) {
                        //Need to handle contingent questions
                        $oarr = array();
                        $oarr['width'] = current($sq->contingentQuestion->length);
                        $oarr['text'] = current($sq->contingentQuestion->text);

                        $oarr['format'] = 'text';

                        if (isset($sq->contingentQuestion->format)) {
                            $oarr['format'] = current($sq->contingentQuestion->format);
                        }

                        if (isset($sq->contingentQuestion['defaultValue'])) {
                            $oarr['defaultvalue'] = current($sq->contingentQuestion['defaultValue']);
                        }

                        $oarr['varname'] = $sq->contingentQuestion['varName'];
                        $sqtmp['other'] = $oarr;
                    }

                    $rstmp['subquestions'][] = $sqtmp;
                }

                foreach ($qu->response as $r) {
                    $rtmp = array();
                    $rstmp['varname'] = $r['varName'];

                    $rtmp['split'] = 'notset';

                    if (isset($r['split'])) {
                        if (current($r['split']) == "true") {
                            $rtmp['split'] = true;
                        } else {
                            $rtmp['split'] = false;
                        }
                    }

                    if (isset($r['defaultValue'])) {
                        $rstmp['defaultvalue'] = current($r['defaultValue']);
                    }

                    if (isset($r->fixed)) {
                        $rtmp['type'] = 'fixed';
                        $rtmp['width'] = count($r->fixed->category);

                        if ($r->fixed['rotate'] == "true") {
                            $rtmp['rotate'] = "true";
                        }

                        if ($r->fixed['separate'] == "true") {
                            $rtmp['separate'] = "true";
                        }

                        $ctmp = array();
                        foreach ($r->fixed->category as $c) {
                            $cat = array();
                            $cat['text'] = current($c->label)!==false ? current($c->label) : '';
                            $cat['value'] = current($c->value);
                            if (isset($c->skipTo)) {
                                $cat['skipto'] = current($c->skipTo);
                                //save a skip
                                $this->skipToRegistry[current($c->skipTo).$this->questionTitleSuffix] = $qtmp['title'];
                            }
                            if (isset($c->contingentQuestion)) {
                                //Need to handle contingent questions
                                $oarr = array();
                                $oarr['width'] = current($c->contingentQuestion->length);
                                $oarr['text'] = current($c->contingentQuestion->text);

                                $oarr['format'] = 'text';

                                if (isset($c->contingentQuestion->format)) {
                                    $oarr['format'] = current($c->contingentQuestion->format);
                                }

                                if (isset($c->contingentQuestion['defaultValue'])) {
                                    $oarr['defaultvalue'] = current($c->contingentQuestion['defaultValue']);
                                }

                                $oarr['varname'] = $c->contingentQuestion['varName'];
                                $cat['other'] = $oarr;
                            }
                            $ctmp[] = $cat;
                        }
                        $rtmp['categories'] = $ctmp;
                    } else if (isset($r->free)) {
                        $format = strtolower(trim(current($r->free->format)));
                        if ($format == 'longtext') {
                            $rtmp['type'] = 'longtext';
                        } else if ($format == 'number' || $format == 'numeric' || $format == 'integer') {
                            $rtmp['type'] = 'number';
                        } else if ($format == 'i25') {
                            $rtmp['type'] = 'i25';
                        } else if ($format == 'codabar') {
                            $rtmp['type'] = 'codabar';
                        } else {
                            $rtmp['type'] = 'text';
                        }
                        $rtmp['width'] = current($r->free->length);
                        $rtmp['text'] = current($r->free->label);
                    } else if (isset($r->vas)) {
                        $rtmp['type'] = 'vas';
                        $rtmp['width'] = 100;
                        $rtmp['labelleft'] = current($r->vas->labelleft);
                        $rtmp['labelright'] = current($r->vas->labelright);
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
     *  questions (title, text, varname, helptext, helptextafter)
     *    responses (varname)
     *      subquestion (text, varname)
     *      response (type, width, text, rotate)
     *        categories (text, value)
     *
     * @param array $questionnaire The questionnaire in the array format above
     */
    public function create($questionnaire)
    {
        $this->init();
        $this->questionnaireId = intval($questionnaire['id']);
        $this->newPage(true); //first page

        //Draw questionnaireInfo before if exists
        if (isset($questionnaire['infobefore'])) {
            $this->drawInfo($questionnaire['infobefore']);
        }

        foreach ($questionnaire['sections'] as $sk => $sv) {
            //link the section title with the first question for pagination purposes
            if (isset($sv['questions'])) {
                $questions = count($sv['questions']);
            } else {
                $questions = 0;
            }

            $this->startTransaction();
            $this->addSection($sv['text'], $sv['title'], $sv['info']);
            if ($questions != 0) {
                $this->createQuestion($sv['questions'][0]);
            }
            if ($this->pageBreakOccured) {
                $this->pageBreakOccured = false;
                $this->rollBackTransaction(true);
                $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                $this->fillPageBackground();
                $this->newPage();
                $this->addSection($sv['text'], $sv['title'], $sv['info']);
                if ($questions != 0) {
                    $this->createQuestion($sv['questions'][0]);
                }
            } else {
                $this->commitTransaction();
            }

            //start from the second question as first is linked to the section (if there is a question in this section)
            if ($questions != 0) {
                foreach (array_slice($sv['questions'], 1) as $qk => $qv) {
                    $this->startTransaction();
                    //add question here
                    $this->createQuestion($qv);
                    if ($this->pageBreakOccured) {
                        $this->pageBreakOccured = false;
                        $this->rollBackTransaction(true);
                        $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                        //now draw a background to the bottom of the page
                        $this->fillPageBackground();

                        $this->newPage();
                        //retry question here
                        $this->createQuestion($qv);
                    } else {
                        $this->commitTransaction();
                    }
                }
            }
        }


        //Draw questionnaireInfo after if exists
        if (isset($questionnaire['infoafter'])) {
            $this->startTransaction();

            $this->drawInfo($questionnaire['infoafter']);

            if ($this->pageBreakOccured) {
                $this->pageBreakOccured = false;
                $this->rollBackTransaction(true);
                $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                //now draw a background to the bottom of the page
                $this->fillPageBackground();

                $this->newPage();
                //retry question here
                $this->drawInfo($questionnaire['infoafter']);
            } else {
                $this->commitTransaction();
            }

        }


        //fill to the end of the last page
        $this->fillLastPageBackground();
    }

    /**
     * Import the settings/styles set from XML
     * 
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2015-06-18
     */
    public function importStyleXML($xmlsettings)
    {
        $xml = new SimpleXMLElement($xmlsettings);

        //do some reflection and find all getters with matching setters
        $class = new ReflectionClass('queXMLPDF');
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $nmethods = array();

        //make an array of relevant classes
        foreach ($methods as $m) {
            if ($m->class == "queXMLPDF") {
                $nmethods[$m->name] = $m->name;
            }
        }

        unset($methods);

        foreach ($nmethods as $m) {
            //if class starting with get has a matching set method
            if (substr($m, 0, 3) == 'get' && isset($nmethods['set'.substr($m, 3)])) {
                $itemname = substr($m, 3);
                $setname = 'set'.$itemname;
                $iv = $this->$m(); // get the current data

                if (isset($xml->$itemname)) {
//if setting exists in xml then set it
                    if (is_bool($iv)) {
                        if ($xml->$itemname == "true") {
                            $this->$setname(true);
                        } else {
                            $this->$setname(false);
                        }
                    } else if (is_array($iv) || is_object($iv)) {
                        $this->$setname(explode(',', $xml->$itemname));
                    } else {
                        $this->$setname($xml->$itemname);
                    }
                }
            }
        }
    }

    /**
     * Export the settings/styles set in XML
     * 
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2015-06-18
     */
    public function exportStyleXML()
    {
        $doc = new DomDocument('1.0');
        $root = $doc->createElement('queXMLPDFStyle');

        //do some reflection and find all getters with matching setters
        $class = new ReflectionClass('queXMLPDF');
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $nmethods = array();

        //make an array of relevant classes
        foreach ($methods as $m) {
            if ($m->class == "quexmlpdf") {
                $nmethods[$m->name] = $m->name;
            }
        }

        unset($methods);

        foreach ($nmethods as $m) {
            //if class starting with get has a matching set method
            if (substr($m, 0, 3) == 'get' && isset($nmethods['set'.substr($m, 3)])) {
                $itemname = substr($m, 3);
                $iv = $this->$m(); // get the data
                $itemval = "false";

                if (is_bool($iv)) {
                    if ($iv) {
                        $itemval = "true";
                    }
                } else if (is_array($iv) || is_object($iv)) {
                    $itemval = implode(',', $iv);
                } else {
                    $itemval = $iv;
                }

                $id = $doc->createElement($itemname);
                $value = $doc->createTextNode($itemval);
                $id->appendChild($value);
                $root->appendChild($id);
            }
        }
        $doc->appendChild($root);
        $doc->formatOutput = true; //make it look nice
        return $doc->saveXML();
    }

    /**
     * Draw the questionnaire info specified
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2011-12-21
     */
    protected function drawInfo($info)
    {
        $this->setBackground('question');
        $this->writeHTMLCell($this->getColumnWidth(), $this->questionnaireInfoMargin, $this->getColumnX(), $this->GetY() - $this->questionBorderBottom, "<div></div>", 0, 1, true, true);
        $html = "<table><tr><td width=\"".$this->getColumnWidth()."mm\" class=\"questionnaireInfo\">{$info}</td><td></td></tr></table>";
        $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
    }

    /**
     * Create a question that may have multiple response groups
     *
     * questions (title, text, specifier, helptext, helptextafter)
     *  responses (varname)
     *    subquestions
     *      subquestion(text, varname)
     *    response (type, width, text, rotate)
     *      categories
     *        category(text, value, skipto, other)
     *
     * @param array $question The questions portion of the array
     * @see create
     */
    protected function createQuestion($question)
    {
        $help = false;
        $specifier = false;
        if (isset($question['helptext'])) {
            $help = $question['helptext'];
        }
        if (isset($question['specifier'])) {
            $specifier = $question['specifier'];
        }

        //If there is some help text for before the question
        if (isset($question['helptextbefore'])) {
            //Leave a border at the top of the Help Before text
            if ($this->helpBeforeBorderTop > 0) {
                $this->SetY($this->GetY() + $this->helpBeforeBorderTop, false); //new line
            }

            $this->setBackground('question');
            $html = "<table><tr><td width=\"".$this->getColumnWidth()."mm\" class=\"questionHelpBefore\">{$question['helptextbefore']}</td><td></td></tr></table>";
            $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

            //Leave a border at the bottom of the Help Before text
            if ($this->helpBeforeBorderBottom > 0) {
                $this->SetY($this->GetY() + $this->helpBeforeBorderBottom, false); //new line
            }
        }

        //Question header
        $helph = $help;
        //don't display help if separate questions are involved
        if (isset($question['responses'][0]['response']['separate'])) {
            $helph = false;
        }

        //hide if requested
        $qtitle = $question['title'];
        if (isset($question['hidetitle'])) {
                    $qtitle = "";
        }

        $this->drawQuestionHead($qtitle, $question['text'], $helph, $specifier);

        $text = "";
        if (isset($question['text'])) {
            $text = $question['text'];
        }

        $split = $question['split'];
        if ($split === 'notset') {
                    $split = $this->allowSplittingResponses;
        }

        //Loop over response groups and produce questions of various types
        if (isset($question['responses'])) {
            //the number of response scales is needed later on to decide on labelling scales in fixed response questions
            $iCountResponseScales = count($question['responses']);

            if ($this->pageBreakOccured) {
                //don't continue if page break already
                return;
            }

            for ($rcount = 0; $rcount < count($question['responses']); $rcount++) {
                $r = $question['responses'][$rcount];

                //only split after one response
                if ($split && $rcount == 1) {
                    if ($this->pageBreakOccured) {
                        return;
                    }
                    $this->startTransaction();
                }

                $varname = $r['varname'];

                if (isset($r['subquestions'])) {
                    $response = $r['response'];
                    $subquestions = $r['subquestions'];
                    $type = $response['type'];

                    $bgtype = 3; //box group type temp set to 3 (text)

                    // question with > 1 responses and >1 subquestions --> matrix question --> need to come up with unique variable names
                    if (count($question['responses']) > 1) {
                        foreach ($subquestions as $index=>$sv) {
                            $subquestions[$index]['varname'] = $subquestions[$index]['varname'].'_'.$varname;
                        }
                    }

                    switch ($type) {
                        case 'fixed':
                            $categories = $response['categories'];

                            if (isset($response['rotate'])) {
                                $this->drawSingleChoiceVertical($categories, $subquestions, $text, $response['split']);
                            } else {
                                if (isset($response['separate'])) {
                                    $this->drawSingleChoiceVerticalSeparate($categories, $subquestions, $text, $help, $response['split']);
                                } else {
                                    // if there is more than one response scale, add a label to scales in the pdf
                                    $vn = false;
                                    if ($iCountResponseScales > 1) {
                                        $vn = $varname;
                                    }
                                    $this->drawSingleChoiceHorizontal($categories, $subquestions, $text, $vn, $response['split']);
                                }
                            }

                            break;
                        case 'number':
                            $bgtype = 4;
                        case 'currency':
                        case 'longtext':
                        case 'text':
                            if ($type == 'longtext') { $bgtype = 6; }
                            if (isset($response['rotate'])) {
                                $this->drawMatrixTextHorizontal($subquestions, $response['width'], $text, $bgtype, $response['text']);
                            } else {
                                $this->drawMatrixTextVertical($subquestions, $response['width'], $text, $bgtype, $response['text'], $response['split']);
                            }
                            break;
                        case 'vas':
                            $this->drawMatrixVas($subquestions, $text, $response['labelleft'], $response['labelright'], $response['split']);
                            break;
                        case 'i25':
                            $this->drawMatrixBarcode($subquestions, 'I25');
                            break;
                        case 'codabar':
                            $this->drawMatrixBarcode($subquestions, 'CODABAR');
                            break;
                    }
                } else {
                    $response = $r['response'];
                    $type = $response['type'];

                    $defaultvalue = false;
                    if (isset($r['defaultvalue'])) {
                        $defaultvalue = $r['defaultvalue'];
                    }

                    if (isset($response['text']) && !empty($response['text'])) {
                        $rtext = $text.$this->subQuestionTextSeparator.$response['text'];
                    } else {
                        $rtext = $text;
                    }

                    $bgtype = 3; //box group type temp set to 3 (text)

                    switch ($type) {
                        case 'fixed':
                            if (isset($response['rotate'])) {
                                $this->drawSingleChoiceHorizontal($response['categories'], array(array('text' => '', 'varname' => $varname, 'defaultvalue' => $defaultvalue)), $rtext, false, $response['split']);
                            } else {
                                $this->drawSingleChoiceVertical($response['categories'], array(array('text' => '', 'varname' => $varname, 'defaultvalue' => $defaultvalue)), $rtext, $response['split']);
                            }
                            break;
                        case 'longtext':
                            $this->addBoxGroup(6, $varname, $rtext);
                            $this->drawLongText($response['width'], $defaultvalue);
                            break;
                        case 'number':
                            $bgtype = 4;
                        case 'currency':
                        case 'text':
                            $this->addBoxGroup($bgtype, $varname, $rtext, $response['width']);
                            $this->drawText($response['text'], $response['width'], $defaultvalue);
                            //Insert a gap here
                            $this->Rect($this->getColumnX(), $this->GetY(), $this->getColumnWidth(), $this->subQuestionLineSpacing, 'F', array(), $this->backgroundColourQuestion);
                            $this->SetY($this->GetY() + $this->subQuestionLineSpacing, false);
                            break;
                        case 'vas':
                            $this->addBoxGroup(1, $varname, $rtext, strlen($this->vasIncrements));
                            $this->drawVas("", $response['labelleft'], $response['labelright']);
                            break;
                        case 'i25':
                            $this->drawMatrixBarcode(array(array('text' => $rtext, 'varname' => $varname, 'defaultvalue' => $defaultvalue)), 'I25');
                            break;
                        case 'codabar':
                            $this->drawMatrixBarcode(array(array('text' => $rtext, 'varname' => $varname, 'defaultvalue' => $defaultvalue)), 'CODABAR');
                            break;

                    }
                }

                //only allow a page break if defined and we have more than one item already on this page
                if ($split && $this->pageBreakOccured && $rcount > 0) {
                    $this->pageBreakOccured = false;
                    $this->rollBackTransaction(true);
                    $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                    $this->fillPageBackground();
                    $this->newPage();

                    //go back to last response
                    $rcount = $rcount - 1;
                } else {
                    if ($split && $rcount > 0) {
                        $this->commitTransaction();
                        $this->startTransaction(); //start a transaction to allow for splitting over pages if necessary
                    }
                }
        }}

        //If there is some help text for after the question
        if (isset($question['helptextafter'])) {
            $this->setBackground('question');
            $html = "<table><tr><td width=\"".$this->getColumnWidth()."mm\" class=\"questionHelpAfter\">{$question['helptextafter']}</td><td></td></tr></table>";
            $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

        }

        //Leave a border at the bottom of the question
        if ($this->questionBorderBottom > 0) {
            $this->SetY($this->GetY() + $this->questionBorderBottom, false); //new line
        }
    }



    /**
     * Draw text responses line by line
     *
     * @param array $subquestions The subquestions containing text and varname
     * @param int $width The width of the text element
     * @param string|bool $parenttext The question text of the parent or false if not specified
     * @param int $bgtype The box group type (default is 3 - text)
     * @param string|bool $responsegrouplabel The label for this response group or false if not specified
     * @param string|bool $split Allow splitting this over multiple pages. 'notset' means leave default. Otherwise force setting
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-02
     */
    protected function drawMatrixTextVertical($subquestions, $width, $parenttext = false, $bgtype = 3, $responsegrouplabel = false, $split = 'notset')
    {
        if ($split === 'notset') {
            $split = $this->allowSplittingMatrixText;
        }

        $c = count($subquestions);

        //draw second axis label
        if ($responsegrouplabel) {
            $this->setBackground('question');
            $html = "<table><tr><td width=\"{$this->questionTitleWidth}mm\"></td><td width=\"".($this->getColumnWidth() - $this->skipColumnWidth - $this->questionTitleWidth)."mm\" class=\"matrixResponseGroupLabel\">$responsegrouplabel:</td><td></td></tr></table>";
            $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
        }

        //don't proceed if breaking the page already
        if ($this->pageBreakOccured) {
            return;
        }

        for ($i = 0; $i < $c; $i++) {
            if ($split && $i == 1) {
                //don't proceed if breaking the page already
                if ($this->pageBreakOccured) {
                    return;
                }

                $this->startTransaction(); //start a transaction when one line drawn
            }

            $s = $subquestions[$i];

            if ($parenttext == false) {
                $this->addBoxGroup($bgtype, $s['varname'], $s['text'], $width);
            } else {
                $this->addBoxGroup($bgtype, $s['varname'], $parenttext.$this->subQuestionTextSeparator.$s['text'], $width);
            }

            $defaultvalue = false;
            if (isset($s['defaultvalue'])) {
                $defaultvalue = $s['defaultvalue'];
            }

            if ($bgtype != 6) {
                $this->drawText($s['text'], $width, $defaultvalue);
            } else {
                $this->drawLongText($width, $defaultvalue, $s['text']);
            }

            $currentY = $this->GetY();

            //Insert a gap here
            $this->Rect($this->getColumnX(), $this->GetY(), $this->getColumnWidth(), $this->subQuestionLineSpacing, 'F', array(), $this->backgroundColourQuestion);
            $this->SetY($currentY + $this->subQuestionLineSpacing, false);

            //only allow a page break if defined and we have more than one item already on this page
            if ($split && $this->pageBreakOccured && $i > 0) {
                $this->pageBreakOccured = false;
                $this->rollBackTransaction(true);
                $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                $this->fillPageBackground();
                $this->newPage();

                //fill page background at top
                $html = "<div></div>";
                $this->setBackground('question');
                $this->writeHTMLCell($this->getColumnWidth(), $this->subQuestionLineSpacing, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

                //move from top of page
                $currentY = $this->GetY();

                $i = $i - 1; //go back and draw subquestions on the new page
            } else {
                if ($split && $i > 0) {
                    $this->commitTransaction();
                    $this->startTransaction(); //start a transaction to allow for splitting over pages if necessary
                }
            }

        }
    }


    /**
     * Draw a barcode as a "question"
     *
     * @param string $subquestions
     * @param string  $type
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-06-22
     */
    protected function drawMatrixBarcode($subquestions, $type)
    {
        $c = count($subquestions);

        for ($i = 0; $i < $c; $i++) {
            $s = $subquestions[$i];

            $this->addBoxGroup(5, $s['varname'], $s['text'], strlen($s['defaultvalue']));

            $x = $this->getColumnX();
            $y = $this->GetY();

            $html = "<div></div>";
            $this->setBackground('question');
            $this->writeHTMLCell($this->getColumnWidth(), $this->barcodeResponseHeight, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, false);

            //draw the barcode
            $barcodeStyle = array('align' => 'R', 'border' => false, 'padding' => '0', 'bgcolor' => $this->backgroundColourQuestion, 'text' => false, 'stretch' => false);
            $this->write1DBarcode($s['defaultvalue'], $type, $x, $y, $this->getColumnWidth() - $this->skipColumnWidth, $this->barcodeResponseHeight, '', $barcodeStyle, 'B');

            //pointer should now be at the bottom right - but make the box the width of the whole column for better reading
            $this->addBox($x, $y, $this->GetX(), $this->getColumnWidth() + $this->getColumnX());

            $currentY = $this->GetY();

            //Insert a gap here
            $this->Rect($this->getColumnX(), $this->GetY(), $this->getColumnWidth(), $this->subQuestionLineSpacing, 'F', array(), $this->backgroundColourQuestion);
            $this->SetY($currentY + $this->subQuestionLineSpacing, false);

        }

    }

    /**
     * Draw multiple VAS items
     *
     * @param array $subquestions The subquestions containing text and varname
     * @param string|bool $parenttext The question text of the parent or false if not specified
     * @param string $labelleft The left hand side label
     * @param string $labelright The right hand side label
     * @param string|bool $split Allow splitting this over multiple pages. 'notset' means leave default. Otherwise force setting
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-20
     */
    protected function drawMatrixVas($subquestions, $parenttext = false, $labelleft, $labelright, $split = 'notset')
    {
        if ($split === 'notset') {
            $split = $this->allowSplittingVas;
        }

        $c = count($subquestions);

        $width = strlen($this->vasIncrements);

        $heading = true;

        for ($i = 0; $i < $c; $i++) {
            if ($split && $i == 1) {
                //don't proceed if breaking the page already
                if ($this->pageBreakOccured) {
                    return;
                }

                $this->startTransaction(); //start a transaction when one line drawn
            }

            $s = $subquestions[$i];

            if ($parenttext == false) {
                $this->addBoxGroup(1, $s['varname'], $s['text'], $width);
            } else {
                $this->addBoxGroup(1, $s['varname'], $parenttext.$this->subQuestionTextSeparator.$s['text'], $width);
            }


            $this->drawVas($s['text'], $labelleft, $labelright, $heading);

            $currentY = $this->GetY();

            //Insert a gap here
            $this->Rect($this->getColumnX(), $this->GetY(), $this->getColumnWidth(), $this->subQuestionLineSpacing, 'F', array(), $this->backgroundColourQuestion);
            $this->SetY($currentY + $this->subQuestionLineSpacing, false);

            $heading = false;

            //only allow a page break if defined and we have more than one item already on this page
            if ($split && $this->pageBreakOccured && $i > 0) {
                $this->pageBreakOccured = false;
                $this->rollBackTransaction(true);
                $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                $this->fillPageBackground();
                $this->newPage();

                //fill page background at top
                $html = "<div></div>";
                $this->setBackground('question');
                $this->writeHTMLCell($this->getColumnWidth(), $this->subQuestionLineSpacing, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

                //move from top of page
                $currentY = $this->GetY();

                $i = $i - 1; //go back and draw subquestions on the new page

                //draw heading again
                $heading = true;
            } else {
                if ($split && $i > 0) {
                    $this->commitTransaction();
                    $this->startTransaction(); //start a transaction to allow for splitting over pages if necessary
                }
            }
        }

    }


    /**
     * Draw a large empty box for writing in text
     *
     * @param mixed $width   The "width" of the box. This relates to the number of "lines" high
     * @param bool|string $defaultvalue The default text to print in the box (if any)
     * @param bool|string $text The text to display above the box (if any)
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-02
     */
    protected function drawLongText($width, $defaultvalue = false, $text = false)
    {
        //Calculate long text box width as the width of the available column minus the skip column and question title area
        $rwidth = $this->getColumnWidth() - $this->skipColumnWidth - $this->questionTitleWidth;

        if ($text !== false && !empty($text)) {
            $this->setBackground('question');
            $html = "<table><tr><td width=\"{$this->questionTitleWidth}mm\"></td><td width=\"".($this->getColumnWidth() - $this->skipColumnWidth - $this->questionTitleWidth)."mm\" class=\"responseAboveText\">$text</td><td></td></tr></table>";
            $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
        }

        $currentY = $this->GetY();
        $height = $width * $this->longTextResponseHeightMultiplier;
        $html = "<div></div>";
        $this->setBackground('question');
        $this->writeHTMLCell($this->getColumnWidth(), $height, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
        $this->SetY($currentY, false);
        $this->setBackground('empty');
        $border = array('LTRB' => array('width' => $this->textResponseBorder, 'dash' => 0));
        //Align to skip column on right
        $this->SetX((($this->getColumnWidth() + $this->getColumnX()) - $this->skipColumnWidth - $rwidth), false);
        //Add to page layout
        $this->addBox($this->GetX(), $this->GetY(), $this->GetX() + $rwidth, $this->GetY() + $height);
        $this->SetDrawColor($this->lineColour[0]);

        $text = "";
        if ($defaultvalue !== false) {
            $text = $defaultvalue;
        }

        $this->MultiCell($rwidth, $height, $text, $border, 'L', true, 1, $this->GetX(), $currentY, true, 0, false, true, $height, 'T', true);

        $currentY = $currentY + $height;
        $this->SetY($currentY, false);
    }


    /**
     * Draw a VAS
     *
     * @param string $text The text of this item
     * @param string $labelleft The left hand side label
     * @param string $labelright The right hand side label
     * @param bool $heading Whether to draw a heading or not
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-20
     */
    protected function drawVas($text, $labelleft, $labelright, $heading = true)
    {
        $textwidth = $this->getColumnWidth() - $this->skipColumnWidth - ($this->vasLength + ($this->vasLineWidth * 2.0)) - 2;
        $this->setBackground('question');

        if ($heading) {
            //draw heading
            $lwidth = 20;
            $slwidth = $textwidth - ($lwidth / 2);
            $gapwidth = ($this->vasLength + ($this->vasLineWidth * 2.0)) - $lwidth;


            $html = "<table><tr><td width=\"{$slwidth}mm\"></td><td width=\"{$lwidth}mm\" class=\"vasLabel\">$labelleft</td><td width=\"{$gapwidth}mm\"></td><td width=\"{$lwidth}mm\" class=\"vasLabel\">$labelright</td></tr></table>";


            $this->writeHTMLCell($this->getColumnWidth(), 0, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, false);
        }

        $currentY = $this->GetY();


        $html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\">$text</td><td></td></tr></table>";

        $textwidth += 2;


        $this->writeHTMLCell($this->getColumnWidth(), $this->vasAreaHeight, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, false);

        $ncurrentY = $this->GetY();

        $this->SetY($currentY, false);
        $this->SetX($textwidth + $this->getColumnX(), false);

        $this->SetLineWidth($this->vasLineWidth);
        $this->SetDrawColor($this->lineColour[0]);

        //Draw the VAS left vert line
        $ly = (($this->vasAreaHeight - $this->vasHeight) / 2.0) + $currentY;
        $lx = $textwidth + $this->getColumnX();
        $this->Line($lx, $ly, $lx, $ly + $this->vasHeight);

        //Right vert line
        $lx = $textwidth + $this->getColumnX() + $this->vasLength + $this->vasLineWidth;
        $this->Line($lx, $ly, $lx, $ly + $this->vasHeight);

        //Line itself
        $ly = ($this->vasAreaHeight / 2.0) + $currentY;
        $lx = $textwidth + $this->getColumnX() + ($this->vasLineWidth / 2.0);
        $this->Line($lx, $ly, $lx + $this->vasLength, $ly);

        //Add to layout system
        $bw = ($this->vasLength / $this->vasIncrements);
        $ly = (($this->vasAreaHeight - $this->vasHeight) / 2.0) + $currentY;
        for ($i = 0; $i < $this->vasIncrements; $i++) {
            $this->addBox($lx, $ly, $lx + $bw, $ly + $this->vasHeight, $i + 1, $i + 1);
            $lx += $bw;
        }

        //Go back to the right Y position
        $this->SetY($ncurrentY, false);
    }

    /**
     * Draw a text response
     *
     * @param string $text The text label if any (can be HTML)
     * @param int $width The number of boxes to draw
     * @param bool|string $defaultvalue The default text to include or false if none
     */
    protected function drawText($text, $width, $defaultvalue = false)
    {
        $this->SetDrawColor($this->lineColour[0]);

        //calculate text responses per line
        $textResponsesPerLine = round(($this->getColumnWidth() - $this->skipColumnWidth - $this->textResponseMarginX) / ($this->textResponseWidth + $this->textResponseBorder));
        $labelTextResponsesSameLine = round(($this->getColumnWidth() - $this->skipColumnWidth - $this->labelTextResponsesSameLineMarginX) / ($this->textResponseWidth + $this->textResponseBorder));

        //draw boxes - can draw up to $textResponsesPerLine for each line
        $lines = ceil($width / $textResponsesPerLine);

        //draw the text label on the top of this box
        if ($width > $labelTextResponsesSameLine && !empty($text)) {
            $this->setBackground('question');
            $html = "<table><tr><td width=\"{$this->questionTitleWidth}mm\"></td><td width=\"".($this->getColumnWidth() - $this->skipColumnWidth - $this->questionTitleWidth)."mm\" class=\"responseAboveText\">$text</td><td></td></tr></table>";
            $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
        }

        $currentY = $this->GetY();

        $startstring = 0;

        for ($i = 0; $i < $lines; $i++) {
            if ($lines == 1) {
//one line only
                $cells = $width; 
            } else if (($i + 1 == $lines)) {
//last line
                $cells = ($width - ($textResponsesPerLine * $i)); 
            } else {
//middle line
                $cells = $textResponsesPerLine; 
            }


            //add another box group if moving on to another line
            if ($i >= 1) {
                            $this->addBoxGroupCopyPrevious();
            }

            $textwidth = ($this->getColumnWidth() - $this->skipColumnWidth) - (($this->textResponseWidth + $this->textResponseBorder) * $cells);

            //print "textwidth: $textwidth cells: $cells mainpagex: " . $this->getMainPageX() . "<br/>";
            //First draw a background of height $this->responseLabelHeight
            $html = "<div></div>";
            $this->setBackground('question');
            $this->writeHTMLCell($this->getColumnWidth(), $this->textResponseHeight, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, false);

            if ($lines == 1 && $cells <= $labelTextResponsesSameLine && !empty($text)) {
                $this->setDefaultFont($this->responseTextFontSize);

                $this->MultiCell($textwidth, $this->textResponseHeight, $text, 0, 'R', false, 1, $this->getColumnX(), $currentY, true, 0, false, true, $this->textResponseHeight, 'M', true);


                //$html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\">$text</td><td></td></tr></table>";
            }


            $ncurrentY = $this->GetY();

            $this->SetY($currentY, false);
            $this->SetX($textwidth + $this->getColumnX() + 2, false); //set the X position to the first cell

            $string = false;
            if ($defaultvalue !== false) {
                $string = mb_substr($defaultvalue, $startstring, $cells, "UTF-8");
            }

            $this->drawCells($cells, $string);

            $startstring += $cells;

            $currentY = $ncurrentY;

            //New line
            $this->SetY($currentY, false); 


            if (!(($i + 1) == $lines) && $this->textResponseLineSpacing > 0) {
//if there should be a gap between text responses and not the last
                $this->SetX($this->getColumnX(), false);
                $this->setBackground('question');
                $this->Cell($this->getColumnWidth(), $this->textResponseLineSpacing, '', '', 0, '', true, '', 0, false, 'T', 'C');
                $currentY += $this->textResponseLineSpacing;
                $this->SetY($currentY, false); //new line
            }

        }

    }

    /**
     * Draw X number of cells at the current X Y position
     *
     * @param int $cells  The number of text cells to draw
     * @param string $string A string to draw if set
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-08
     */
    protected function drawCells($cells, $string)
    {
        $this->setBackground('empty');
        $this->SetDrawColor($this->lineColour[0]);

        for ($j = 0; $j < $cells; $j++) {
            //draw text cells
            if ($cells == 1) {
//only
                $border = array('LTR' => array('width' => $this->textResponseBorder, 'dash' => 0), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));
            } else if ($j == 0) {
//first
                $border = array('LT' => array('width' => $this->textResponseBorder, 'dash' => 0), 'R' => array('width' => $this->textResponseBorder, 'dash' => 1), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));
            } else if (($j + 1) == $cells) {
//last
                $border = array('TR' => array('width' => $this->textResponseBorder, 'dash' => 0), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));

                //add a border gap
                $this->SetX($this->GetX() + ($this->textResponseBorder), false);
            } else {
//middle
                $border = array('T' => array('width' => $this->textResponseBorder, 'dash' => 0), 'R' => array('width' => $this->textResponseBorder, 'dash' => 1), 'B' => array('width' => ($this->textResponseBorder * 2), 'dash' => 0));
                //add a border gap
                $this->SetX($this->GetX() + ($this->textResponseBorder), false);
            }

            //Add the box to the layout scheme
            $this->addBox($this->GetX(), $this->GetY(), $this->GetX() + $this->textResponseWidth, $this->GetY() + $this->textResponseHeight);

            $text = mb_substr($string,$j,1,"UTF-8");

            //Draw the box
            $this->Cell($this->textResponseWidth, $this->textResponseHeight, $text, $border, 0, '', true, '', 0, false, 'T', 'C');

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
     * @param string|bool $responsegrouplabel The label for this response group or false if not specified
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-08
     */
    protected function drawMatrixTextHorizontal($subquestions, $width, $parenttext = false, $bgtype = 3, $responsegrouplabel = false)
    {
        $total = count($subquestions);
        $currentY = $this->GetY();

        $rwidth = ($width * ($this->textResponseWidth + $this->textResponseBorder + $this->textResponseLineSpacing));

        $textwidth = ($this->getColumnWidth() - $this->skipColumnWidth) - ($rwidth * $total);

        $html = "<table><tr><td width=\"{$textwidth}mm\" class=\"responseText\"></td>";
        foreach ($subquestions as $r) {
            $html .= "<td class=\"responseLabel\" width=\"{$rwidth}mm\">{$r['text']}</td>";
        }
        $html .= "<td></td></tr></table>";
        $this->writeHTMLCell($this->getColumnWidth(), $this->singleResponseAreaHeight, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
        $currentY = $this->GetY();

        //label "vertical axis"
        $html = "<table><tr><td width=\"{$textwidth}mm\" class=\"matrixResponseGroupLabel\">$responsegrouplabel</td><td></td></tr></table>";
        $this->writeHTMLCell($this->getColumnWidth(), $this->singleResponseAreaHeight, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

        $ncurrentY = $this->GetY();

        $this->SetY($currentY, false);

        //Set X position
        $this->SetX($this->getColumnX() + $textwidth, false);


        foreach ($subquestions as $s) {
            //Add box group to current layout
            if ($parenttext == false) {
                $this->addBoxGroup($bgtype, $s['varname'], $s['text']);
            } else {
                $this->addBoxGroup($bgtype, $s['varname'], $parenttext.$this->subQuestionTextSeparator.$s['text']);
            }

            if ($bgtype != 6) {
                $string = false;
                if (isset($s['defaultvalue'])) {
                    $string = mb_substr($s['defaultvalue'], 0, $width,"UTF-8");
                }

                //Draw the cells
                $this->drawCells($width, $string);
            } else {
                $this->setBackground('empty');
                $border = array('LTRB' => array('width' => $this->textResponseBorder, 'dash' => 0));
                //Add to page layout
                $this->addBox($this->GetX(), $this->GetY(), $this->GetX() + $rwidth, $this->GetY() + $height);
                $this->SetDrawColor($this->lineColour[0]);
                $this->MultiCell($rwidth, $this->singleResponseAreaHeight, $s['defaultvalue'], $border, 'L', true, 1, $this->GetX(), $currentY, true, 0, false, true, $height, 'T', true);
            }

            //Move X for a gap
            $this->SetX($this->GetX() + $this->textResponseLineSpacing, false);
            $this->SetY($currentY, false);
        }

        //Move cursor back to the right spot
        $this->SetY($ncurrentY, false);
    }


    /**
     * Draw the head of a single choice horizontal table of responses
     *
     * @param array $categories The response categories
     * @param string|bool $responsegrouplabel The label for this response group or false if not specified
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-06-05
     */
    protected function drawSingleChoiceHorizontalHead($categories, $responsegrouplabel = false)
    {
        $total = count($categories);
        $currentY = $this->GetY();

        if ($total > $this->singleResponseHorizontalMax) {
            //change if too many cats
            $rwidth = $this->singleResponseVerticalAreaWidthSmall;
        } else {
                    $rwidth = $this->singleResponseVerticalAreaWidth;
        }

        $textwidth = ($this->getColumnWidth() - $this->skipColumnWidth) - ($rwidth * $total);

        //Draw a label for a group of Questions/Responses (e.g. useful for dual scale matrix questions)
        if ($responsegrouplabel != false) {
            $this->setBackground('question');
            $this->setDefaultFont();
            $this->MultiCell($textwidth, $this->responseLabelHeight, $responsegrouplabel.':', 0, 'L', false, 0, $this->getColumnX() + $this->questionTitleWidth, $this->GetY(), true, 0, false, true, $this->responseLabelHeight, 'B', true);
        }


        //First draw a background of height $this->responseLabelHeight
        $html = "<div></div>";
        $this->setBackground('question');
        $this->writeHTMLCell($this->getColumnWidth(), $this->responseLabelHeight, $this->getColumnX(), $currentY, $this->style.$html, 0, 1, true, true);

        $this->setDefaultFont($this->responseLabelFontSize);

        $count = 0;
        //Draw a Cell for each rwidth from $textwidth + $this->getColumnX(),currentY
        foreach ($categories as $r) {
            $y = $currentY;
            $x = ($textwidth + $this->getColumnX() + ($rwidth * $count));

            // Going to break the line because of long word
            if ($this->wordLength($r['text']) > $this->responseLabelSmallWordLength) {
                $this->setDefaultFont($this->responseLabelFontSizeSmall);
            }

            $this->MultiCell($rwidth, $this->responseLabelHeight, $r['text'], 0, 'C', false, 0, $x, $y, true, 0, false, true, $this->responseLabelHeight, 'B', true);

            //reset font
            if ($this->wordLength($r['text']) > $this->responseLabelSmallWordLength) {
                $this->setDefaultFont($this->responseLabelFontSize);
            }

            $count++;
        }
    }

    /**
     * Draw a horizontal table of respones including "eye guides"
     *
     * @param array $categories The response categories
     * @param array $subquestions The subquestions if any
     * @param string|bool $parenttext The question text of the parent or false if not specified
     * @param string|bool $responsegrouplabel The label for this response group or false if not specified
     * @param string|bool $split Allow splitting this over multiple pages. 'notset' means leave default. Otherwise force setting
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-08
     */
    protected function drawSingleChoiceHorizontal($categories, $subquestions = array(array('text' => '')), $parenttext = false, $responsegrouplabel = false, $split = "notset")
    {
        if ($split === "notset") {
                    $split = $this->allowSplittingSingleChoiceHorizontal;
        }

        $total = count($categories);
        $currentY = $this->GetY();

        if ($total > $this->singleResponseHorizontalMax) {
            //change if too many cats
            $rwidth = $this->singleResponseVerticalAreaWidthSmall;
        } else {
                    $rwidth = $this->singleResponseVerticalAreaWidth;
        }

        $textwidth = ($this->getColumnWidth() - $this->skipColumnWidth) - ($rwidth * $total);

        //draw the header
        $this->drawSingleChoiceHorizontalHead($categories, $responsegrouplabel);
        $currentY += $this->responseLabelHeight;

        //don't continue if page break already
        if ($this->pageBreakOccured) {
            return;
        }

        for ($i = 0; $i < count($subquestions); $i++) {
            if ($split && $i == 1) {
                //don't proceed if breaking the page already
                if ($this->pageBreakOccured) {
                    return;
                }

                $this->startTransaction(); //start a transaction when one line drawn
            }

            $s = $subquestions[$i];

            //Add box group to current layout
            if ($parenttext == false) {
                $this->addBoxGroup(1, $s['varname'], $s['text']);
            } else {
                $this->addBoxGroup(1, $s['varname'], $parenttext.$this->subQuestionTextSeparator.$s['text']);
            }

            //Draw background
            $html = "<div></div>";
            $this->setBackground('question');
            $this->writeHTMLCell($this->getColumnWidth(), $this->singleResponseHorizontalHeight, $this->getColumnX(), $currentY, $this->style.$html, 0, 1, true, true);
            $this->setDefaultFont($this->responseTextFontSize);

            $newlineheight = $this->singleResponseHorizontalHeight;
            $heightadjust = 0;

            $testcells = $this->getNumLines($s['text'], $textwidth);

            if ($testcells > $this->singleResponseHorizontalMaxLines) {
                //more than two lines so need to increase the space between these questions
                $heightadjust = (($this->singleResponseHorizontalHeight / $this->singleResponseHorizontalMaxLines) * ($testcells - $this->singleResponseHorizontalMaxLines));

                $newlineheight = $newlineheight + $heightadjust;

                $this->setBackground('question');
                $this->writeHTMLCell($this->getColumnWidth(), $newlineheight, $this->getColumnX(), $currentY, $this->style.$html, 0, 1, true, false);
                $this->setDefaultFont($this->responseTextFontSize);

                $this->MultiCell($textwidth, $newlineheight, $s['text'], 0, 'R', false, 0, $this->getColumnX(), $currentY, true, 0, false, true, $newlineheight, 'M', false);
            } else {
                $this->MultiCell($textwidth, $this->singleResponseHorizontalHeight, $s['text'], 0, 'R', false, 0, $this->getColumnX(), $currentY, true, 0, false, true, $this->singleResponseHorizontalHeight, 'M', false);
            }


            $other = false;
            if (isset($s['other'])) {
                $other = true;
            }

            //Draw the categories horizontally
            $rnum = 1;
            foreach ($categories as $r) {
                if ($total == 1) {
                    $num = 'only';
                } else if ($rnum == 1) {
                    $num = 'first';
                } else if ($rnum < $total) {
                    $num = 'middle';
                } else if ($rnum == $total) {
                    $num = 'last';
                }

                $bfilled = false;
                if (isset($s['defaultvalue']) && $s['defaultvalue'] !== false && $s['defaultvalue'] == $r['value']) {
                    $bfilled = true;
                }

                $position = $this->drawHorizontalResponseBox(($this->getColumnX() + $textwidth + (($rnum - 1) * $rwidth)), $currentY + ($heightadjust / 2), $num, $other, false, ($total > $this->singleResponseHorizontalMax), $bfilled);

                //Add box to the current layout
                $this->addBox($position[0], $position[1], $position[2], $position[3], $r['value'], $r['text']);

                $rnum++;
            }

            if (($this->GetY() - $currentY) > $newlineheight) {
                $currentY = $this->GetY();
            } else {
                $currentY = $currentY + $newlineheight;
            }

            $this->SetY($currentY, false);

            if ($other) {
                $this->drawOther($s['other']);
            }

            //only allow a page break if defined and we have more than one item already on this page
            if ($split && $this->pageBreakOccured && $i > 0) {
                $this->pageBreakOccured = false;
                $this->rollBackTransaction(true);
                $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                $this->fillPageBackground();
                $this->newPage();
                $this->drawSingleChoiceHorizontalHead($categories, $responsegrouplabel);

                //reset currentY
                $currentY = $this->GetY() + $this->responseLabelHeight;

                $i = $i - 1; //go back and draw subquestions on the new page
            } else {
                if ($split && $i > 0) {
                    $this->commitTransaction();
                    $this->startTransaction(); //start a transaction to allow for splitting over pages if necessary
                }
            }
        }
    }


    /**
     * Draw vertical questions separately instead of in a matrix
     *
     * @param array $categories An array containing the category text, value, skipto and other
     * @param array $subquestions An array containing the subquestions if any
     * @param string|bool $parenttext The question text of the parent or false if not specified
     * @param string|bool $help Help text if any for the responses
     * @param string|bool $split Allow splitting this over multiple pages. 'notset' means leave default. Otherwise force setting
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-07-30
     */
    protected function drawSingleChoiceVerticalSeparate($categories, $subquestions, $parenttext, $help, $split = 'notset')
    {
        for ($sc = 0; $sc < count($subquestions); $sc++) {
            $s = $subquestions[$sc];

            $this->drawQuestionHead("", $this->numberToLetter($sc + 1).". ".$s['text'], $help);
            //Don't send it twice
            unset($s['text']);
            $this->drawSingleChoiceVertical($categories, array(array($s)), $this->subQuestionTextSeparator.$s['text']);
        }
    }


    /**
     * Draw the head of a single choice vertical table of responses
     *
     * @param array $subquestions The subquestions
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-10-24
     */
    protected function drawSingleChoiceVerticalHead($subquestions)
    {
        $currentY = $this->GetY();
        $total = count($subquestions);

        $rwidth = $this->singleResponseVerticalAreaWidth;

        $textwidth = ($this->getColumnWidth() - $this->skipColumnWidth) - ($rwidth * $total);

        $isempty = true;
        $count = 0;

        //First draw a background of height $this->responseLabelHeight
        $html = "<div></div>";
        $this->setBackground('question');
        $this->writeHTMLCell($this->getColumnWidth(), $this->responseLabelHeight, $this->getColumnX(), $currentY, $this->style.$html, 0, 1, true, true);

        $this->setDefaultFont($this->responseLabelFontSize);

        //Draw a Cell for each rwidth from $textwidth + $this->getColumnX(),currentY
        foreach ($subquestions as $r) {
            $y = $currentY;
            $x = ($textwidth + $this->getColumnX() + ($rwidth * $count));

            // Going to break the line because of long word
            if ($this->wordLength($r['text']) > $this->responseLabelSmallWordLength) {
                $this->setDefaultFont($this->responseLabelFontSizeSmall);
            }

            $this->MultiCell($rwidth, $this->responseLabelHeight, $r['text'], 0, 'C', false, 0, $x, $y, true, 0, false, true, $this->responseLabelHeight, 'B', true);

            if ($this->wordLength($r['text']) > $this->responseLabelSmallWordLength) {
                $this->setDefaultFont($this->responseLabelFontSize);
            }

            if (!empty($r['text'])) {
                $isempty = false;
            }

            $count++;
        }

        if ($isempty) {
            $this->SetY($currentY, false);
        } else {
            $this->SetY($currentY + $this->responseLabelHeight);
        }

    }

    /**
     * Draw a vertical table of single choice responses including "eye guides"
     *
     * @param array $categories An array containing the category text, value, skipto and other
     * @param array $subquestions An array containing the subquestions if any
     * @param string|bool $parenttext The question text of the parent or false if not specified
     * @param string|bool $split Allow splitting this over multiple pages. 'notset' means leave default. Otherwise force setting
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-02
     */
    protected function drawSingleChoiceVertical($categories, $subquestions = array(array('text' => '')), $parenttext = false, $split = 'notset')
    {
        //draw subquestions if more than one category (otherwise probably a multiple choice question)
        if (count($categories) > 1) {
            $this->drawSingleChoiceVerticalHead($subquestions);
        }

        $total = count($subquestions);
        $rwidth = $this->singleResponseVerticalAreaWidth;
        $textwidth = ($this->getColumnWidth() - $this->skipColumnWidth) - ($rwidth * $total);
        $currentY = $this->GetY();
        $total = count($categories);
        $boxcp = array();

        //restrict splitting to minSplittingSingleChoiceVertical where not explicitly set in queXML
        if ($split === 'notset') {
            $split = $this->allowSplittingSingleChoiceVertical && ($total >= $this->minSplittingSingleChoiceVertical);
        }

        for ($i = 0; $i < count($categories); $i++) {
            //don't continue if page break already (start on new page)
            if ($i == 1 && $split) {
                if ($this->pageBreakOccured) {
                    return;
                }

                $this->startTransaction(); //allow for splitting
            }

            $rnum = $i + 1;
            $r = $categories[$i];

            if ($total == 1) {
                $num = 'only';
            } else if ($rnum == 1) {
                $num = 'first';
            } else if ($rnum < $total) {
                $num = 'middle';
            } else if ($rnum == $total) {
                $num = 'last';
            }

            $bheight = $this->singleResponseAreaHeight;
            $skipto = false;
            if (isset($r['skipto'])) {
                $skipto = $r['skipto'];
            }
            $other = false;
            if (isset($r['other']) && $rnum == $total) {
                $other = $r['other']; //only set for last in set
                $bheight += $this->arrowHeight;
            }

            //Draw background
            $html = "<div></div>";
            $this->setBackground('question');
            $this->writeHTMLCell($this->getColumnWidth(), $bheight, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);
            $this->setDefaultFont($this->responseTextFontSize);

            //draw text
            $this->MultiCell($textwidth, $this->singleResponseAreaHeight, $r['text'], 0, 'R', false, 0, $this->getColumnX(), $currentY, true, 0, false, true, $this->singleResponseAreaHeight, 'M', true);

            $skipto = false;
            $other = false;

            if (isset($r['skipto'])) {
                $skipto = $r['skipto'];
            }
            if (isset($r['other']) && $rnum == $total) {
//only set for last in set
                $other = $r['other'];    
            }

            //draw the response boxes
            for ($j = 0; $j < count($subquestions); $j++) {
                $s = $subquestions[$j];

                if ($i == 0) {
// only need to do this once
                    if ($parenttext == false) {
                                            $this->addBoxGroup(1, $s['varname'], $s['text']);
                    } else {
                                            $this->addBoxGroup(1, $s['varname'], $parenttext.$this->subQuestionTextSeparator.$s['text']);
                    }

                    //save the box group for this subquestion
                    $boxcp[$j] = $this->boxGroupCP;
                } else {
                    //reset box group pointer to be for the correct subquestion
                    $this->boxGroupCP = $boxcp[$j];
                }

                $bfilled = false;
                if (isset($s['defaultvalue']) && $s['defaultvalue'] !== false && $s['defaultvalue'] == $r['value']) {
                                    $bfilled = true;
                }

                $x = $this->getColumnX() + $textwidth + ($rwidth * $j) + ((($rwidth - $this->singleResponseBoxWidth) / 2.0));

                //Draw the box over the top
                $position = $this->drawVerticalResponseBox($x, $currentY, $num, $other, $skipto, $bfilled);

                //Add box to the current layout
                $this->addBox($position[0], $position[1], $position[2], $position[3], $r['value'], $r['text']);
            }

            if (($this->GetY() - $currentY) > $bheight) {
                $currentY = $this->GetY();
            } else {
                $currentY = $currentY + $bheight;
            }

            $this->SetY($currentY, false);

            if ($other !== false) {
                //Display the "other" variable
                $this->drawOther($other);
            }

            //only allow a page break if defined and we have more than one item already on this page
            if ($split && $this->pageBreakOccured && $i > 0) {
                $this->pageBreakOccured = false;
                $this->rollBackTransaction(true);
                $this->SetAutoPageBreak(false); //Temporarily set so we don't trigger a page break
                $this->fillPageBackground();

                //draw an arrow indicating that this was split and will be on the next page
                $boxmiddle = ($x + ($this->singleResponseBoxWidth / 2.0));
                $this->SetFillColor($this->lineColour[0]);
                //set the bottom of the box to be the bottom of the page
                $y = $this->getPageHeight() - $this->cornerBorder - $this->arrowHeight;
                $this->Polygon(array($x, $y, $boxmiddle, $y + $this->arrowHeight, $x + $this->singleResponseBoxWidth, $y), 'DF', array(), $this->lineColour);
                $this->setBackground('empty');

                $this->newPage();

                if (count($categories) > 1) {
                    $this->drawSingleChoiceVerticalHead($subquestions);
                }

                //reset currentY
                $currentY = $this->GetY();

                $i = $i - 1; //go back and draw categories on the new page

                //create a new box group as we are on a new page
                $sbgc = 0;
                foreach ($subquestions as $sbg) {
                    if ($parenttext == false) {
                        $this->addBoxGroup(1, $sbg['varname'], $sbg['text']);
                    } else {
                        $this->addBoxGroup(1, $sbg['varname'], $parenttext.$this->subQuestionTextSeparator.$sbg['text']);
                    }
                    //save the box group for this subquestion
                    $boxcp[$sbgc] = $this->boxGroupCP;
                    $sbgc++;
                }
            } else {
                if ($split && $i > 0) {
                    $this->commitTransaction();
                    $this->startTransaction(); //start a transaction to allow for splitting over pages if necessary
                }
            }

        }
    }

    /**
     * Draw an "other" box
     *
     * @param array $other An array continaing varname,text,width,defaultvalue
     *
     * @return TODO
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2013-05-01
     */
    protected function drawOther($other)
    {
        $btid = 3;

        if ($other['format'] == 'longtext') {
            $btid = 6;
        }

        $this->addBoxGroup($btid, $other['varname'], $other['text'], $other['width']);

        $defaultvalue = false;
        if (isset($other['defaultvalue']) && $other['defaultvalue'] !== false) {
            $defaultvalue = $other['defaultvalue'];
        }

        if ($btid != 6) {
            $this->drawText($other['text'], $other['width'], $defaultvalue);
        } else {
            $this->drawLongText($other['width'], $defaultvalue, $other['text']);
        }

        //Insert a gap here
        $this->Rect($this->getColumnX(), $this->GetY(), $this->getColumnWidth(), $this->subQuestionLineSpacing, 'F', array(), $this->backgroundColourQuestion);
        $this->SetY($this->GetY() + $this->subQuestionLineSpacing, false);
    }

    /**
     * Draw the header of a question (question title, text and help text if any)
     *
     * @param string $title The question title (number)
     * @param string $text The question text (can be HTML)
     * @param string|bool $help The question help text or false if none (can be HTML)
     * @param string|bool $specifier The question specifier text or false if none (can be HTML)
     */
    protected function drawQuestionHead($title, $text, $help = false, $specifier = false)
    {
        $this->setBackground('question');
        //Cell for question number (title) and text including a white border at the bottom

        $class = "questionTitle";

        //If there is a skip to this question, make the question title bigger
        if (isset($this->skipToRegistry[$title])) {
            $class = "questionTitleSkipTo";
        }

        $html = "<table><tr><td class=\"$class\" width=\"".$this->questionTitleWidth."mm\">$title</td><td class=\"questionText\" width=\"".($this->getColumnWidth() - $this->questionTextRightMargin - $this->questionTitleWidth)."mm\">$text</td><td></td></tr>";

        if ($specifier !== false) {
            $html .= "<tr><td></td><td></td><td></td></tr><tr><td class=\"$class\" width=\"".$this->questionTitleWidth."mm\">&nbsp;</td><td class=\"questionSpecifier\" width=\"".($this->getColumnWidth() - $this->questionTextRightMargin - $this->questionTitleWidth)."mm\">$specifier</td><td></td></tr>";
        }

        $html .= "</table>";


        $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

        if ($help != false) {
            $html = "<table><tr><td width=\"".($this->getColumnWidth() - $this->skipColumnWidth)."mm\" class=\"questionHelp\">$help</td><td></td></tr></table>";
            $this->writeHTMLCell($this->getColumnWidth(), 1, $this->getColumnX(), $this->GetY(), $this->style.$html, 0, 1, true, true);

        }
    }

    /**
     * Add a new section to the page
     *
     * @param string $desc The description of this section
     * @param string $info Information for this section
     */
    protected function addSection($desc = 'queXMLPDF Section', $title = false, $info = false)
    {
        $this->sectionCP++;
        $mtitle = $title;
        
        if ($title === false) {
            $mtitle = $this->sectionCP;
        }
        
        $this->section[$this->sectionCP] = array('label' => $desc, 'title' => $mtitle);
        
        $html = '';
        if ($title !== false) {
                $html .= "<span class=\"sectionTitle\">$title:</span>&nbsp;";
                $html .= "<span class=\"sectionDescription\">$desc</span>";
        }

        if ($info && !empty($info)) {
            $html .= "<div class=\"sectionInfo\">$info</div>";
        }

        if (!($title === false && $info === false)) {
            $this->setBackground('section');
            $this->writeHTMLCell($this->getColumnWidth(), $this->sectionHeight, $this->getColumnX(), $this->getY(), $this->style.$html, array('B' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->backgroundColourEmpty)), 1, true, true, '');
            $this->setBackground('empty');
        }
    }

    /**
     * Convert mm to pixels based on the set ppi (dpi)
     *
     * @param float $mm Measurement in millimetres
     * @return double Pixel value as an integer
     */
    public function mm2px($mm)
    {
        return round($mm * ($this->ppi / self::INCH_IN_MM));
    }

    /**
     * Make sure to fill the remaining columns on the last page
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2012-05-31
     */
    protected function fillLastPageBackground()
    {
        while ($this->columnCP < $this->columns) {
            $this->fillPageBackground();
            $this->SetXY($this->getColumnX(), ($this->cornerBorder + $this->cornerWidth));
            $this->columnCP++;
        }
    }

    /**
     * Draw the background from the current Y position to the bottom of the page
     *
     * @author Adam Zammit <adam.zammit@acspri.org.au>
     * @since  2010-09-15
     */
    protected function fillPageBackground()
    {
        $height = $this->getPageHeight() - $this->cornerBorder - $this->GetY() + $this->questionBorderBottom;
        $html = "<div></div>";
        $this->setBackground('question');
        $this->writeHTMLCell($this->getColumnWidth(), $height, $this->getColumnX(), $this->GetY() - $this->questionBorderBottom, $this->style.$html, 0, 1, true, true);
    }

    /**
     * Create a new queXML PDF page
     *
     * Draw the barcode and page corners
     *
     */
    protected function newPage($init = false)
    {
        $this->columnCP++; //increment the column pointer

        if ($init || ($this->columnCP >= $this->columns)) {
// if it is time for a new page
            $this->AddPage();

            //Set Auto page break to false
            $this->SetAutoPageBreak(false);

            $this->SetMargins(0, 0, 0);
            $this->SetHeaderMargin(0);
            $this->SetFooterMargin(0);

            //Shortcuts to make the code (a bit) nicer
            $width = $this->getPageWidth();
            $height = $this->getPageHeight();
            $cb = $this->cornerBorder;
            $cl = $this->cornerLength;

            $calc = $this->cornerWidth;

            $this->SetDrawColor($this->lineColour[0]);

            $barcodeStyle = array('border' => false, 'padding' => '0', 'bgcolor' => false, 'text' => false, 'stretch' => true);
            if ($this->cornerLines) {
                //corner lines (Default)
                $lineStyle = array('width' => $this->cornerWidth, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

                //Top left
                $this->Line($cb, $cb, $cb + $cl, $cb, $lineStyle);
                $this->Line($cb, $cb, $cb, $cb + $cl, $lineStyle);

                //Top right
                $this->Line($width - $cb, $cb, $width - $cb - $cl, $cb, $lineStyle);
                $this->Line($width - $cb, $cb, $width - $cb, $cb + $cl, $lineStyle);

                //Bottom left
                $this->Line($cb, $height - $cb, $cb + $cl, $height - $cb, $lineStyle);
                $this->Line($cb, $height - $cb, $cb, $height - ($cb + $cl), $lineStyle);

                //Bottom right
                $this->Line($width - $cb, $height - $cb, $width - $cb - $cl, $height - $cb, $lineStyle);
                $this->Line($width - $cb, $height - $cb, $width - $cb, $height - ($cb + $cl), $lineStyle);

                $calc = $cl;
            } else {
                //corner boxes instead
                $cw = $this->cornerBoxWidth;

                //Top left
                $this->Rect(($cb - $cw), ($cb - $cw), $cw, $cw, 'DF', null, $this->lineColour);

                //Top right
                $this->Rect(($width - $cb), ($cb - $cw), $cw, $cw, 'DF', null, $this->lineColour);

                //Bottom left
                $this->Rect(($cb - $cw), ($height - $cb), $cw, $cw, 'DF', null, $this->lineColour);

                //Bottom right
                $this->Rect(($width - $cb), ($height - $cb), $cw, $cw, 'DF', null, $this->lineColour);

                $calc = -$cw;
            }

            $barcodeValue = substr(str_pad($this->questionnaireId, $this->idLength, "0", STR_PAD_LEFT), 0, $this->idLength).substr(str_pad($this->getPage(), $this->pageLength, "0", STR_PAD_LEFT), 0, $this->pageLength);

            //Calc X position of barcode from page width
            $barcodeX = $width - ($this->barcodeMarginX + $this->barcodeW);

            $this->write1DBarcode($barcodeValue, $this->barcodeType, $barcodeX, $this->barcodeY, $this->barcodeW, $this->barcodeH, '', $barcodeStyle, 'N');

            //Add this page to the layout system
            $b = $this->cornerBorder + ($calc / 2.0); //temp calc for middle of line
            $this->layout[$barcodeValue] = array('id' => $barcodeValue,
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

            $this->setBackground('empty');

            $this->columnCP = 0; //reset column pointer
        } else // move to a new column
        {
            $this->SetXY($this->getColumnX(), ($this->cornerBorder + $this->cornerWidth));
        }
        $this->SetAutoPageBreak(true, $this->getMainPageX());

        //after a new page was begun....page should not have already ended
        $this->pageBreakOccured = false;
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
        return false;
    }
}
