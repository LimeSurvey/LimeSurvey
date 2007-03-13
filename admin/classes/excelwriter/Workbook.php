<?php
/*
*  Module written/ported by Xavier Noguer <xnoguer@rezebra.com>
*
*  The majority of this is _NOT_ my code.  I simply ported it from the
*  PERL Spreadsheet::WriteExcel module.
*
*  The author of the Spreadsheet::WriteExcel module is John McNamara 
*  <jmcnamara@cpan.org>
*
*  I _DO_ maintain this code, and John McNamara has nothing to do with the
*  porting of this code to PHP.  Any questions directly related to this
*  class library should be directed to me.
*
*  License Information:
*
*    Spreadsheet_Excel_Writer:  A library for generating Excel Spreadsheets
*    Copyright (c) 2002-2003 Xavier Noguer xnoguer@rezebra.com
*
*    This library is free software; you can redistribute it and/or
*    modify it under the terms of the GNU Lesser General Public
*    License as published by the Free Software Foundation; either
*    version 2.1 of the License, or (at your option) any later version.
*
*    This library is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
*    Lesser General Public License for more details.
*
*    You should have received a copy of the GNU Lesser General Public
*    License along with this library; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('Format.php');
require_once('OLEwriter.php');
require_once('BIFFwriter.php');
require_once('Worksheet.php');
require_once('Parser.php');

/**
* Class for generating Excel Spreadsheets
*
* @author   Xavier Noguer <xnoguer@rezebra.com>
* @category FileFormats
* @package  Spreadsheet_Excel_Writer
*/

class Spreadsheet_Excel_Writer_Workbook extends Spreadsheet_Excel_Writer_BIFFwriter
{
    /**
    * Filename for the Workbook
    * @var string
    */
    var $_filename;

    /**
    * Formula parser
    * @var object Parser
    */
    var $_parser;

    /**
    * Flag for 1904 date system
    * @var integer
    */
    var $_1904;

    /**
    * The active worksheet of the workbook (0 indexed)
    * @var integer
    */
    var $_activesheet;

    /**
    * 1st displayed worksheet in the workbook (0 indexed)
    * @var integer
    */
    var $_firstsheet;

    /**
    * Number of workbook tabs selected
    * @var integer
    */
    var $_selected;

    /**
    * Index for creating adding new formats to the workbook
    * @var integer
    */
    var $_xf_index;

    /**
    * Flag for preventing close from being called twice.
    * @var integer
    * @see close()
    */
    var $_fileclosed;

    /**
    * The BIFF file size for the workbook.
    * @var integer
    * @see _calcSheetOffsets()
    */
    var $_biffsize;

    /**
    * The default sheetname for all sheets created.
    * @var string
    */
    var $_sheetname;

    /**
    * The default XF format.
    * @var object Format
    */
    var $_tmp_format;

    /**
    * Array containing references to all of this workbook's worksheets
    * @var array
    */
    var $_worksheets;

    /**
    * Array of sheetnames for creating the EXTERNSHEET records
    * @var array
    */
    var $_sheetnames;

    /**
    * Array containing references to all of this workbook's formats
    * @var array
    */
    var $_formats;

    /**
    * Array containing the colour palette
    * @var array
    */
    var $_palette;

    /**
    * The default format for URLs.
    * @var object Format
    */
    var $_url_format;

    /**
    * Class constructor
    *
    * @param string filename for storing the workbook. "-" for writing to stdout.
    * @access public
    */
    function Spreadsheet_Excel_Writer_Workbook($filename)
    {
        // It needs to call its parent's constructor explicitly
        $this->Spreadsheet_Excel_Writer_BIFFwriter();
    
        $this->_filename         = $filename;
        $this->_parser           =& new Spreadsheet_Excel_Writer_Parser($this->_byte_order);
        $this->_1904             = 0;
        $this->_activesheet      = 0;
        $this->_firstsheet       = 0;
        $this->_selected         = 0;
        $this->_xf_index         = 16; // 15 style XF's and 1 cell XF.
        $this->_fileclosed       = 0;
        $this->_biffsize         = 0;
        $this->_sheetname        = "Sheet";
        $this->_tmp_format       =& new Spreadsheet_Excel_Writer_Format();
        $this->_worksheets       = array();
        $this->_sheetnames       = array();
        $this->_formats          = array();
        $this->_palette          = array();
    
        // Add the default format for hyperlinks
        $this->_url_format =& $this->addFormat(array('color' => 'blue', 'underline' => 1));
    
        $this->_setPaletteXl97();
    }
    
    /**
    * Calls finalization methods.
    * This method should always be the last one to be called on every workbook
    *
    * @access public
    */
    function close()
    {
        if ($this->_fileclosed) { // Prevent close() from being called twice.
            return;
        }
        $this->_storeWorkbook();
        $this->_fileclosed = 1;
    }
    
    
    /**
    * An accessor for the _worksheets[] array
    * Returns an array of the worksheet objects in a workbook
    * It actually calls to worksheets()
    *
    * @access public
    * @see worksheets()
    * @return array
    */
    function sheets()
    {
        return $this->worksheets();
    }
    
    /**
    * An accessor for the _worksheets[] array.
    * Returns an array of the worksheet objects in a workbook
    *
    * @access public
    * @return array
    */
    function worksheets()
    {
        return($this->_worksheets);
    }
    
    /**
    * Add a new worksheet to the Excel workbook.
    * If no name is given the name of the worksheet will be Sheeti$i, with
    * $i in [1..].
    *
    * @access public
    * @param string $name the optional name of the worksheet
    * @return &Spreadsheet_Excel_Writer_Worksheet reference to a worksheet object
    */
    function &addWorksheet($name = '')
    {
        $index     = count($this->_worksheets);
        $sheetname = $this->_sheetname;
    
        if($name == '') {
            $name = $sheetname.($index+1); 
        }
    
        // Check that sheetname is <= 31 chars (Excel limit).
        if(strlen($name) > 31) {
            $this->raiseError("Sheetname $name must be <= 31 chars");
        }
    
        // Check that the worksheet name doesn't already exist: a fatal Excel error.
        for($i=0; $i < count($this->_worksheets); $i++)
        {
            if($name == $this->_worksheets[$i]->getName()) {
                $this->raiseError("Worksheet '$name' already exists");
            }
        }
    
        $worksheet = new Spreadsheet_Excel_Writer_Worksheet($name,$index,$this->_activesheet,
                                   $this->_firstsheet,$this->_url_format,
                                   $this->_parser);

        $this->_worksheets[$index] = &$worksheet;    // Store ref for iterator
        $this->_sheetnames[$index] = $name;          // Store EXTERNSHEET names
        $this->_parser->setExtSheet($name, $index);  // Register worksheet name with parser
        return($worksheet);
    }
    
    /**
    * Add a new format to the Excel workbook.
    * Also, pass any properties to the Format constructor.
    *
    * @access public
    * @param array $properties array with properties for initializing the format.
    * @return &Spreadsheet_Excel_Writer_Format reference to an Excel Format
    */
    function &addFormat($properties = array())
    {
        $format = new Spreadsheet_Excel_Writer_Format($this->_xf_index,$properties);
        $this->_xf_index += 1;
        $this->_formats[] = &$format;
        return($format);
    }
    
    /**
    * Change the RGB components of the elements in the colour palette.
    *
    * @access public
    * @param integer $index colour index
    * @param integer $red   red RGB value [0-255]
    * @param integer $green green RGB value [0-255]
    * @param integer $blue  blue RGB value [0-255]
    * @return integer The palette index for the custom color
    */
    function setCustomColor($index,$red,$green,$blue)
    {
        // Match a HTML #xxyyzz style parameter
        /*if (defined $_[1] and $_[1] =~ /^#(\w\w)(\w\w)(\w\w)/ ) {
            @_ = ($_[0], hex $1, hex $2, hex $3);
        }*/
    
        // Check that the colour index is the right range
        if ($index < 8 or $index > 64) {
            // TODO: assign real error codes
            $this->raiseError("Color index $index outside range: 8 <= index <= 64",0,PEAR_ERROR_DIE);
        }
    
        // Check that the colour components are in the right range
        if ( ($red   < 0 or $red   > 255) or
             ($green < 0 or $green > 255) or
             ($blue  < 0 or $blue  > 255) )  
        {
            $this->raiseError("Color component outside range: 0 <= color <= 255");
        }
    
        $index -= 8; // Adjust colour index (wingless dragonfly)
        
        // Set the RGB value
        $this->_palette[$index] = array($red, $green, $blue, 0);
        return($index + 8);
    }
    
    /**
    * Sets the colour palette to the Excel 97+ default.
    *
    * @access private
    */
    function _setPaletteXl97()
    {
        $this->_palette = array(
                           array(0x00, 0x00, 0x00, 0x00),   // 8
                           array(0xff, 0xff, 0xff, 0x00),   // 9
                           array(0xff, 0x00, 0x00, 0x00),   // 10
                           array(0x00, 0xff, 0x00, 0x00),   // 11
                           array(0x00, 0x00, 0xff, 0x00),   // 12
                           array(0xff, 0xff, 0x00, 0x00),   // 13
                           array(0xff, 0x00, 0xff, 0x00),   // 14
                           array(0x00, 0xff, 0xff, 0x00),   // 15
                           array(0x80, 0x00, 0x00, 0x00),   // 16
                           array(0x00, 0x80, 0x00, 0x00),   // 17
                           array(0x00, 0x00, 0x80, 0x00),   // 18
                           array(0x80, 0x80, 0x00, 0x00),   // 19
                           array(0x80, 0x00, 0x80, 0x00),   // 20
                           array(0x00, 0x80, 0x80, 0x00),   // 21
                           array(0xc0, 0xc0, 0xc0, 0x00),   // 22
                           array(0x80, 0x80, 0x80, 0x00),   // 23
                           array(0x99, 0x99, 0xff, 0x00),   // 24
                           array(0x99, 0x33, 0x66, 0x00),   // 25
                           array(0xff, 0xff, 0xcc, 0x00),   // 26
                           array(0xcc, 0xff, 0xff, 0x00),   // 27
                           array(0x66, 0x00, 0x66, 0x00),   // 28
                           array(0xff, 0x80, 0x80, 0x00),   // 29
                           array(0x00, 0x66, 0xcc, 0x00),   // 30
                           array(0xcc, 0xcc, 0xff, 0x00),   // 31
                           array(0x00, 0x00, 0x80, 0x00),   // 32
                           array(0xff, 0x00, 0xff, 0x00),   // 33
                           array(0xff, 0xff, 0x00, 0x00),   // 34
                           array(0x00, 0xff, 0xff, 0x00),   // 35
                           array(0x80, 0x00, 0x80, 0x00),   // 36
                           array(0x80, 0x00, 0x00, 0x00),   // 37
                           array(0x00, 0x80, 0x80, 0x00),   // 38
                           array(0x00, 0x00, 0xff, 0x00),   // 39
                           array(0x00, 0xcc, 0xff, 0x00),   // 40
                           array(0xcc, 0xff, 0xff, 0x00),   // 41
                           array(0xcc, 0xff, 0xcc, 0x00),   // 42
                           array(0xff, 0xff, 0x99, 0x00),   // 43
                           array(0x99, 0xcc, 0xff, 0x00),   // 44
                           array(0xff, 0x99, 0xcc, 0x00),   // 45
                           array(0xcc, 0x99, 0xff, 0x00),   // 46
                           array(0xff, 0xcc, 0x99, 0x00),   // 47
                           array(0x33, 0x66, 0xff, 0x00),   // 48
                           array(0x33, 0xcc, 0xcc, 0x00),   // 49
                           array(0x99, 0xcc, 0x00, 0x00),   // 50
                           array(0xff, 0xcc, 0x00, 0x00),   // 51
                           array(0xff, 0x99, 0x00, 0x00),   // 52
                           array(0xff, 0x66, 0x00, 0x00),   // 53
                           array(0x66, 0x66, 0x99, 0x00),   // 54
                           array(0x96, 0x96, 0x96, 0x00),   // 55
                           array(0x00, 0x33, 0x66, 0x00),   // 56
                           array(0x33, 0x99, 0x66, 0x00),   // 57
                           array(0x00, 0x33, 0x00, 0x00),   // 58
                           array(0x33, 0x33, 0x00, 0x00),   // 59
                           array(0x99, 0x33, 0x00, 0x00),   // 60
                           array(0x99, 0x33, 0x66, 0x00),   // 61
                           array(0x33, 0x33, 0x99, 0x00),   // 62
                           array(0x33, 0x33, 0x33, 0x00),   // 63
                         );
    }
    
    /**
    * Assemble worksheets into a workbook and send the BIFF data to an OLE
    * storage.
    *
    * @access private
    */
    function _storeWorkbook()
    {
        // Ensure that at least one worksheet has been selected.
        if ($this->_activesheet == 0)
        {
            $this->_worksheets[0]->selected = 1;
        }
    
        // Calculate the number of selected worksheet tabs and call the finalization
        // methods for each worksheet
        for($i=0; $i < count($this->_worksheets); $i++)
        {
            if($this->_worksheets[$i]->selected) {
                $this->_selected++;
            }
            $this->_worksheets[$i]->close($this->_sheetnames);
        }
    
        // Add Workbook globals
        $this->_storeBof(0x0005);
        $this->_storeExterns();    // For print area and repeat rows
        $this->_storeNames();      // For print area and repeat rows
        $this->_storeWindow1();
        $this->_store1904();
        $this->_storeAllFonts();
        $this->_storeAllNumFormats();
        $this->_storeAllXfs();
        $this->_storeAllStyles();
        $this->_storePalette();
        $this->_calcSheetOffsets();
    
        // Add BOUNDSHEET records
        for($i=0; $i < count($this->_worksheets); $i++) {
            $this->_storeBoundsheet($this->_worksheets[$i]->name,$this->_worksheets[$i]->offset);
        }
    
        // End Workbook globals
        $this->_storeEof();
    
        // Store the workbook in an OLE container
        $this->_storeOLEFile();
    }
    
    /**
    * Store the workbook in an OLE container if the total size of the workbook data
    * is less than ~ 7MB.
    *
    * @access private
    */
    function _storeOLEFile()
    {
        $OLE  = new Spreadsheet_Excel_Writer_OLEwriter($this->_filename);
        $this->_tmp_filename = $OLE->_tmp_filename;
        // Write Worksheet data if data <~ 7MB
        if ($OLE->setSize($this->_biffsize))
        {
            $OLE->writeHeader();
            $OLE->write($this->_data);
            foreach($this->_worksheets as $sheet) 
            {
                while ($tmp = $sheet->getData()) {
                    $OLE->write($tmp);
                }
            }
        }
        $OLE->close();
    }
    
    /**
    * Calculate offsets for Worksheet BOF records.
    *
    * @access private
    */
    function _calcSheetOffsets()
    {
        $BOF     = 11;
        $EOF     = 4;
        $offset  = $this->_datasize;
        for($i=0; $i < count($this->_worksheets); $i++) {
            $offset += $BOF + strlen($this->_worksheets[$i]->name);
        }
        $offset += $EOF;
        for($i=0; $i < count($this->_worksheets); $i++) {
            $this->_worksheets[$i]->offset = $offset;
            $offset += $this->_worksheets[$i]->_datasize;
        }
        $this->_biffsize = $offset;
    }
    
    /**
    * Store the Excel FONT records.
    *
    * @access private
    */
    function _storeAllFonts()
    {
        // tmp_format is added by the constructor. We use this to write the default XF's
        $format = $this->_tmp_format;
        $font   = $format->getFont();
    
        // Note: Fonts are 0-indexed. According to the SDK there is no index 4,
        // so the following fonts are 0, 1, 2, 3, 5
        //
        for($i=1; $i <= 5; $i++){
            $this->_append($font);
        }
    
        // Iterate through the XF objects and write a FONT record if it isn't the
        // same as the default FONT and if it hasn't already been used.
        //
        $fonts = array();
        $index = 6;                  // The first user defined FONT
    
        $key = $format->getFontKey(); // The default font from _tmp_format
        $fonts[$key] = 0;               // Index of the default font
    
        for($i=0; $i < count($this->_formats); $i++) {
            $key = $this->_formats[$i]->getFontKey();
            if (isset($fonts[$key])) {
                // FONT has already been used
                $this->_formats[$i]->font_index = $fonts[$key];
            }
            else {
                // Add a new FONT record
                $fonts[$key]        = $index;
                $this->_formats[$i]->font_index = $index;
                $index++;
                $font = $this->_formats[$i]->getFont();
                $this->_append($font);
            }
        }
    }
    
    /**
    * Store user defined numerical formats i.e. FORMAT records
    *
    * @access private
    */
    function _storeAllNumFormats()
    {
        // Leaning num_format syndrome
        $hash_num_formats = array();
        $num_formats      = array();
        $index = 164;
    
        // Iterate through the XF objects and write a FORMAT record if it isn't a
        // built-in format type and if the FORMAT string hasn't already been used.
        //
        for($i=0; $i < count($this->_formats); $i++)
        {
            $num_format = $this->_formats[$i]->_num_format;
    
            // Check if $num_format is an index to a built-in format.
            // Also check for a string of zeros, which is a valid format string
            // but would evaluate to zero.
            //
            if (!preg_match("/^0+\d/",$num_format))
            {
                if (preg_match("/^\d+$/",$num_format)) { // built-in format
                    continue;
                }
            }
    
            if (isset($hash_num_formats[$num_format])) {
                // FORMAT has already been used
                $this->_formats[$i]->_num_format = $hash_num_formats[$num_format];
            }
            else{
                // Add a new FORMAT
                $hash_num_formats[$num_format]  = $index;
                $this->_formats[$i]->_num_format = $index;
                array_push($num_formats,$num_format);
                $index++;
            }
        }
    
        // Write the new FORMAT records starting from 0xA4
        $index = 164;
        foreach ($num_formats as $num_format) {
            $this->_storeNumFormat($num_format,$index);
            $index++;
        }
    }
    
    /**
    * Write all XF records.
    *
    * @access private
    */
    function _storeAllXfs()
    {
        // _tmp_format is added by the constructor. We use this to write the default XF's
        // The default font index is 0
        //
        $format = $this->_tmp_format;
        for ($i=0; $i <= 14; $i++) {
            $xf = $format->getXf('style'); // Style XF
            $this->_append($xf);
        }
    
        $xf = $format->getXf('cell');      // Cell XF
        $this->_append($xf);
    
        // User defined XFs
        for($i=0; $i < count($this->_formats); $i++) {
            $xf = $this->_formats[$i]->getXf('cell');
            $this->_append($xf);
        }
    }
    
    /**
    * Write all STYLE records.
    *
    * @access private 
    */
    function _storeAllStyles()
    {
        $this->_storeStyle();
    }
    
    /**
    * Write the EXTERNCOUNT and EXTERNSHEET records. These are used as indexes for
    * the NAME records.
    *
    * @access private
    */
    function _storeExterns()
    {
        // Create EXTERNCOUNT with number of worksheets
        $this->_storeExterncount(count($this->_worksheets));
    
        // Create EXTERNSHEET for each worksheet
        foreach ($this->_sheetnames as $sheetname) {
            $this->_storeExternsheet($sheetname);
        }
    }
    
    /**
    * Write the NAME record to define the print area and the repeat rows and cols.
    *
    * @access private
    */
    function _storeNames()
    {
        // Create the print area NAME records
        foreach ($this->_worksheets as $worksheet) {
            // Write a Name record if the print area has been defined
            if (isset($worksheet->print_rowmin))
            {
                $this->_storeNameShort(
                    $worksheet->index,
                    0x06, // NAME type
                    $worksheet->print_rowmin,
                    $worksheet->print_rowmax,
                    $worksheet->print_colmin,
                    $worksheet->print_colmax
                    );
            }
        }
    
        // Create the print title NAME records
        foreach ($this->_worksheets as $worksheet)
        {
            $rowmin = $worksheet->title_rowmin;
            $rowmax = $worksheet->title_rowmax;
            $colmin = $worksheet->title_colmin;
            $colmax = $worksheet->title_colmax;
    
            // Determine if row + col, row, col or nothing has been defined
            // and write the appropriate record
            //
            if (isset($rowmin) and isset($colmin)) {
                // Row and column titles have been defined.
                // Row title has been defined.
                $this->_storeNameLong(
                    $worksheet->index,
                    0x07, // NAME type
                    $rowmin,
                    $rowmax,
                    $colmin,
                    $colmax
                    );
            }
            elseif (isset($rowmin)) {
                // Row title has been defined.
                $this->_storeNameShort(
                    $worksheet->index,
                    0x07, // NAME type
                    $rowmin,
                    $rowmax,
                    0x00,
                    0xff
                    );
            }
            elseif (isset($colmin)) {
                // Column title has been defined.
                $this->_storeNameShort(
                    $worksheet->index,
                    0x07, // NAME type
                    0x0000,
                    0x3fff,
                    $colmin,
                    $colmax
                    );
            }
            else {
                // Print title hasn't been defined.
            }
        }
    }
    
    
    
    
    /******************************************************************************
    *
    * BIFF RECORDS
    *
    */
    
    /**
    * Write Excel BIFF WINDOW1 record.
    *
    * @access private
    */
    function _storeWindow1()
    {
        $record    = 0x003D;                 // Record identifier
        $length    = 0x0012;                 // Number of bytes to follow
    
        $xWn       = 0x0000;                 // Horizontal position of window
        $yWn       = 0x0000;                 // Vertical position of window
        $dxWn      = 0x25BC;                 // Width of window
        $dyWn      = 0x1572;                 // Height of window
    
        $grbit     = 0x0038;                 // Option flags
        $ctabsel   = $this->_selected;       // Number of workbook tabs selected
        $wTabRatio = 0x0258;                 // Tab to scrollbar ratio
    
        $itabFirst = $this->_firstsheet;     // 1st displayed worksheet
        $itabCur   = $this->_activesheet;    // Active worksheet
    
        $header    = pack("vv",        $record, $length);
        $data      = pack("vvvvvvvvv", $xWn, $yWn, $dxWn, $dyWn,
                                       $grbit,
                                       $itabCur, $itabFirst,
                                       $ctabsel, $wTabRatio);
        $this->_append($header.$data);
    }
    
    /**
    * Writes Excel BIFF BOUNDSHEET record.
    *
    * @param string  $sheetname Worksheet name
    * @param integer $offset    Location of worksheet BOF
    * @access private
    */
    function _storeBoundsheet($sheetname,$offset)
    {
        $record    = 0x0085;                    // Record identifier
        $length    = 0x07 + strlen($sheetname); // Number of bytes to follow
    
        $grbit     = 0x0000;                    // Sheet identifier
        $cch       = strlen($sheetname);        // Length of sheet name
    
        $header    = pack("vv",  $record, $length);
        $data      = pack("VvC", $offset, $grbit, $cch);
        $this->_append($header.$data.$sheetname);
    }
    
    /**
    * Write Excel BIFF STYLE records.
    *
    * @access private
    */
    function _storeStyle()
    {
        $record    = 0x0293;   // Record identifier
        $length    = 0x0004;   // Bytes to follow
                               
        $ixfe      = 0x8000;   // Index to style XF
        $BuiltIn   = 0x00;     // Built-in style
        $iLevel    = 0xff;     // Outline style level
    
        $header    = pack("vv",  $record, $length);
        $data      = pack("vCC", $ixfe, $BuiltIn, $iLevel);
        $this->_append($header.$data);
    }
    
    
    /**
    * Writes Excel FORMAT record for non "built-in" numerical formats.
    *
    * @param string  $format Custom format string
    * @param integer $ifmt   Format index code
    * @access private
    */
    function _storeNumFormat($format,$ifmt)
    {
        $record    = 0x041E;                      // Record identifier
        $length    = 0x03 + strlen($format);      // Number of bytes to follow
    
        $cch       = strlen($format);             // Length of format string
    
        $header    = pack("vv", $record, $length);
        $data      = pack("vC", $ifmt, $cch);
        $this->_append($header.$data.$format);
    }
    
    /**
    * Write Excel 1904 record to indicate the date system in use.
    *
    * @access private
    */
    function _store1904()
    {
        $record    = 0x0022;         // Record identifier
        $length    = 0x0002;         // Bytes to follow
    
        $f1904     = $this->_1904;   // Flag for 1904 date system
    
        $header    = pack("vv", $record, $length);
        $data      = pack("v", $f1904);
        $this->_append($header.$data);
    }
    
    
    /**
    * Write BIFF record EXTERNCOUNT to indicate the number of external sheet
    * references in the workbook.
    *
    * Excel only stores references to external sheets that are used in NAME.
    * The workbook NAME record is required to define the print area and the repeat
    * rows and columns.
    *
    * A similar method is used in Worksheet.php for a slightly different purpose.
    *
    * @param integer $cxals Number of external references
    * @access private
    */
    function _storeExterncount($cxals)
    {
        $record   = 0x0016;          // Record identifier
        $length   = 0x0002;          // Number of bytes to follow
    
        $header   = pack("vv", $record, $length);
        $data     = pack("v",  $cxals);
        $this->_append($header.$data);
    }
    
    
    /**
    * Writes the Excel BIFF EXTERNSHEET record. These references are used by
    * formulas. NAME record is required to define the print area and the repeat
    * rows and columns.
    *
    * A similar method is used in Worksheet.php for a slightly different purpose.
    *
    * @param string $sheetname Worksheet name
    * @access private
    */
    function _storeExternsheet($sheetname)
    {
        $record      = 0x0017;                     // Record identifier
        $length      = 0x02 + strlen($sheetname);  // Number of bytes to follow
                                                   
        $cch         = strlen($sheetname);         // Length of sheet name
        $rgch        = 0x03;                       // Filename encoding
    
        $header      = pack("vv",  $record, $length);
        $data        = pack("CC", $cch, $rgch);
        $this->_append($header.$data.$sheetname);
    }
    
    
    /**
    * Store the NAME record in the short format that is used for storing the print
    * area, repeat rows only and repeat columns only.
    *
    * @param integer $index  Sheet index
    * @param integer $type   Built-in name type
    * @param integer $rowmin Start row
    * @param integer $rowmax End row
    * @param integer $colmin Start colum
    * @param integer $colmax End column
    * @access private
    */
    function _storeNameShort($index,$type,$rowmin,$rowmax,$colmin,$colmax)
    {
        $record          = 0x0018;       // Record identifier
        $length          = 0x0024;       // Number of bytes to follow
    
        $grbit           = 0x0020;       // Option flags
        $chKey           = 0x00;         // Keyboard shortcut
        $cch             = 0x01;         // Length of text name
        $cce             = 0x0015;       // Length of text definition
        $ixals           = $index + 1;   // Sheet index
        $itab            = $ixals;       // Equal to ixals
        $cchCustMenu     = 0x00;         // Length of cust menu text
        $cchDescription  = 0x00;         // Length of description text
        $cchHelptopic    = 0x00;         // Length of help topic text
        $cchStatustext   = 0x00;         // Length of status bar text
        $rgch            = $type;        // Built-in name type
    
        $unknown03       = 0x3b;
        $unknown04       = 0xffff-$index;
        $unknown05       = 0x0000;
        $unknown06       = 0x0000;
        $unknown07       = 0x1087;
        $unknown08       = 0x8005;
    
        $header             = pack("vv", $record, $length);
        $data               = pack("v", $grbit);
        $data              .= pack("C", $chKey);
        $data              .= pack("C", $cch);
        $data              .= pack("v", $cce);
        $data              .= pack("v", $ixals);
        $data              .= pack("v", $itab);
        $data              .= pack("C", $cchCustMenu);
        $data              .= pack("C", $cchDescription);
        $data              .= pack("C", $cchHelptopic);
        $data              .= pack("C", $cchStatustext);
        $data              .= pack("C", $rgch);
        $data              .= pack("C", $unknown03);
        $data              .= pack("v", $unknown04);
        $data              .= pack("v", $unknown05);
        $data              .= pack("v", $unknown06);
        $data              .= pack("v", $unknown07);
        $data              .= pack("v", $unknown08);
        $data              .= pack("v", $index);
        $data              .= pack("v", $index);
        $data              .= pack("v", $rowmin);
        $data              .= pack("v", $rowmax);
        $data              .= pack("C", $colmin);
        $data              .= pack("C", $colmax);
        $this->_append($header.$data);
    }
    
    
    /**
    * Store the NAME record in the long format that is used for storing the repeat
    * rows and columns when both are specified. This shares a lot of code with
    * _storeNameShort() but we use a separate method to keep the code clean.
    * Code abstraction for reuse can be carried too far, and I should know. ;-)
    *
    * @param integer $index Sheet index
    * @param integer $type  Built-in name type
    * @param integer $rowmin Start row
    * @param integer $rowmax End row
    * @param integer $colmin Start colum
    * @param integer $colmax End column
    * @access private
    */
    function _storeNameLong($index,$type,$rowmin,$rowmax,$colmin,$colmax)
    {
        $record          = 0x0018;       // Record identifier
        $length          = 0x003d;       // Number of bytes to follow
        $grbit           = 0x0020;       // Option flags
        $chKey           = 0x00;         // Keyboard shortcut
        $cch             = 0x01;         // Length of text name
        $cce             = 0x002e;       // Length of text definition
        $ixals           = $index + 1;   // Sheet index
        $itab            = $ixals;       // Equal to ixals
        $cchCustMenu     = 0x00;         // Length of cust menu text
        $cchDescription  = 0x00;         // Length of description text
        $cchHelptopic    = 0x00;         // Length of help topic text
        $cchStatustext   = 0x00;         // Length of status bar text
        $rgch            = $type;        // Built-in name type
    
        $unknown01       = 0x29;
        $unknown02       = 0x002b;
        $unknown03       = 0x3b;
        $unknown04       = 0xffff-$index;
        $unknown05       = 0x0000;
        $unknown06       = 0x0000;
        $unknown07       = 0x1087;
        $unknown08       = 0x8008;
    
        $header             = pack("vv",  $record, $length);
        $data               = pack("v", $grbit);
        $data              .= pack("C", $chKey);
        $data              .= pack("C", $cch);
        $data              .= pack("v", $cce);
        $data              .= pack("v", $ixals);
        $data              .= pack("v", $itab);
        $data              .= pack("C", $cchCustMenu);
        $data              .= pack("C", $cchDescription);
        $data              .= pack("C", $cchHelptopic);
        $data              .= pack("C", $cchStatustext);
        $data              .= pack("C", $rgch);
        $data              .= pack("C", $unknown01);
        $data              .= pack("v", $unknown02);
        // Column definition
        $data              .= pack("C", $unknown03);
        $data              .= pack("v", $unknown04);
        $data              .= pack("v", $unknown05);
        $data              .= pack("v", $unknown06);
        $data              .= pack("v", $unknown07);
        $data              .= pack("v", $unknown08);
        $data              .= pack("v", $index);
        $data              .= pack("v", $index);
        $data              .= pack("v", 0x0000);
        $data              .= pack("v", 0x3fff);
        $data              .= pack("C", $colmin);
        $data              .= pack("C", $colmax);
        // Row definition
        $data              .= pack("C", $unknown03);
        $data              .= pack("v", $unknown04);
        $data              .= pack("v", $unknown05);
        $data              .= pack("v", $unknown06);
        $data              .= pack("v", $unknown07);
        $data              .= pack("v", $unknown08);
        $data              .= pack("v", $index);
        $data              .= pack("v", $index);
        $data              .= pack("v", $rowmin);
        $data              .= pack("v", $rowmax);
        $data              .= pack("C", 0x00);
        $data              .= pack("C", 0xff);
        // End of data
        $data              .= pack("C", 0x10);
        $this->_append($header.$data);
    }
    
    
    /**
    * Stores the PALETTE biff record.
    *
    * @access private
    */
    function _storePalette()
    {
        $aref            = $this->_palette;
    
        $record          = 0x0092;                 // Record identifier
        $length          = 2 + 4 * count($aref);   // Number of bytes to follow
        $ccv             =         count($aref);   // Number of RGB values to follow
        $data = '';                                // The RGB data
    
        // Pack the RGB data
        foreach($aref as $color)
        {
            foreach($color as $byte) {
                $data .= pack("C",$byte);
            }
        }
    
        $header = pack("vvv",  $record, $length, $ccv);
        $this->_append($header.$data);
    }
}
?>
