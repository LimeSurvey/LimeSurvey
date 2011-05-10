<?php

/************************************************************
 * TCPDF - CodeIgniter Integration
 * Configuration file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 ***********************************************************/


/***************************************************************************
 * PATH CONFIGURATION PARAMETERS
 **************************************************************************/


	/************************************************************
	 * TCPDF installation directory
	 * ----------------------------------------------------------
	 * This is the base installation directory for your TCPDF
	 * package (the folder that contains tcpdf.php).
	 * 
	 * ADD TRAILING SLASH!
	 ***********************************************************/
	
	$tcpdf['base_directory'] = APPPATH.'third_party/tcpdf/';
	
	
	/************************************************************
	 * TCPDF installation directory URL
	 * ----------------------------------------------------------
	 * This is the URL path to the TCPDF base installation
	 * directory (the URL equivalent to the 'base_directory'
	 * option above).
	 * 
	 * ADD TRAILING SLASH!
	 ***********************************************************/
	
	$tcpdf['base_url'] = '';
	
	
	/************************************************************
	 * TCPDF fonts directory
	 * ----------------------------------------------------------
	 * This is the directory of the TCPDF fonts folder.
	 * Use $tcpdf['base_directory'].'fonts/old/' for old non-UTF8
	 * fonts.
	 * 
	 * ADD TRAILING SLASH!
	 ***********************************************************/
	
	$tcpdf['fonts_directory'] = $tcpdf['base_directory'].'fonts/';
	
	
	/************************************************************
	 * TCPDF disk cache settings
	 * ----------------------------------------------------------
	 * Enable caching; Cache directory for TCPDF (make sure that
	 * it is writable by the webserver).
	 * 
	 * ADD TRAILING SLASH!
	 ***********************************************************/
	
	$tcpdf['enable_disk_cache'] = FALSE;
	$tcpdf['cache_directory'] = $tcpdf['base_directory'].'cache/';
	
	
	/************************************************************
	 * TCPDF image directory
	 * ----------------------------------------------------------
	 * This is the image directory for TCPDF. This is where you
	 * can store images to use in your PDF files.
	 * 
	 * ADD TRAILING SLASH!
	 ***********************************************************/
	
	$tcpdf['image_directory'] = $tcpdf['base_directory'].'images/';
	
	
	/************************************************************
	 * TCPDF default (blank) image
	 * ----------------------------------------------------------
	 * This is the path and filename to the default (blank)
	 * image.
	 ***********************************************************/
	
	$tcpdf['blank_image'] = $tcpdf['image_directory'].'_blank.png';
	
	
	/************************************************************
	 * TCPDF language settings file
	 * ----------------------------------------------------------
	 * Directory and filename of the language settings file
	 ***********************************************************/
	
	$tcpdf['language_file'] = $tcpdf['base_directory'].'config/lang/eng.php';


	
/***************************************************************************
 * DOCUMENT CONFIGURATION PARAMETERS
 **************************************************************************/
	
	
	/************************************************************
	 * TCPDF default page format
	 * ----------------------------------------------------------
	 * This is the default page size. Supported formats include:
	 * 
	 * 4A0, 2A0, A0, A1, A2, A3, A4, A5, A6, A7, A8, A9, A10, B0,
	 * B1, B2, B3, B4, B5, B6, B7, B8, B9, B10, C0, C1, C2, C3, 
	 * C4, C5, C6, C7, C8, C9, C10, RA0, RA1, RA2, RA3, RA4, 
	 * SRA0, SRA1, SRA2, SRA3, SRA4, LETTER, LEGAL, EXECUTIVE, 
	 * FOLIO
	 * 
	 * Or, you can optionally specify a custom format in the form
	 * of a two-element array containing the width and the height.
	 ************************************************************/
	
	$tcpdf['page_format'] = 'LETTER';
	
	
	/************************************************************
	 * TCPDF default page orientation
	 * ----------------------------------------------------------
	 * Default page layout.
	 * P = portrait, L = landscape
	 ***********************************************************/
	
	$tcpdf['page_orientation'] = 'P';
	
	
	/************************************************************
	 * TCPDF default unit of measure
	 * ----------------------------------------------------------
	 * Unit of measure.
	 * mm = millimeters, cm = centimeters,
	 * pt = points, in = inches
	 * 
	 * 1 point = 1/72 in = ~0.35 mm
	 * 1 inch = 2.54 cm
	 ***********************************************************/

	$tcpdf['page_unit'] = 'mm';

	
	/************************************************************
	 * TCPDF auto page break
	 * ----------------------------------------------------------
	 * Enables automatic flowing of content to the next page if
	 * you run out of room on the current page. 
	 ***********************************************************/
	
	$tcpdf['page_break_auto'] = TRUE;
	
	
	/************************************************************
	 * TCPDF text encoding
	 * ----------------------------------------------------------
	 * Specify TRUE if the input text you will be using is
	 * unicode, and specify the default encoding.
	 ***********************************************************/
	
	$tcpdf['unicode'] = TRUE;
	$tcpdf['encoding'] = 'UTF-8';
	

	/************************************************************
	 * TCPDF default document creator and author strings
	 ***********************************************************/
	
	$tcpdf['creator'] = 'TCPDF';
	$tcpdf['author'] = 'TCPDF';
	
	
	/************************************************************
	 * TCPDF default page margin
	 * ----------------------------------------------------------
	 * Top, bottom, left, right, header, and footer margin
	 * settings in the default unit of measure.
	 ***********************************************************/
	
	$tcpdf['margin_top']    = 27;
	$tcpdf['margin_bottom'] = 27;
	$tcpdf['margin_left']   = 15;
	$tcpdf['margin_right']  = 15;
	
	
	/************************************************************
	 * TCPDF default font settings
	 * ----------------------------------------------------------
	 * Page font, font size, header and footer fonts,
	 * HTML <small> font size ratio
	 ***********************************************************/
	
	$tcpdf['page_font'] = 'helvetica';
	$tcpdf['page_font_size'] = 10;
	
	$tcpdf['small_font_ratio'] = 2/3;
	
	
	/************************************************************
	 * TCPDF header settings
	 * ----------------------------------------------------------
	 * Enable the header, set the font, default text, margin,
	 * description string, and logo
	 ***********************************************************/
	
	$tcpdf['header_on'] = TRUE;
	$tcpdf['header_font'] = $tcpdf['page_font'];
	$tcpdf['header_font_size'] = 10;
	$tcpdf['header_margin'] = 5;
	//$tcpdf['header_title'] = 'TCPDF Example';
	//$tcpdf['header_string'] = "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org";
    $tcpdf['header_title'] = '';
	$tcpdf['header_string'] = "";
	//$tcpdf['header_logo'] = 'tcpdf_logo.jpg';
    $tcpdf['header_logo'] = '';
	$tcpdf['header_logo_width'] = 30;
	
	
	/************************************************************
	 * TCPDF footer settings
	 * ----------------------------------------------------------
	 * Enable the header, set the font, default text, and margin
	 ***********************************************************/
	
	$tcpdf['footer_on'] = TRUE;
	$tcpdf['footer_font'] = $tcpdf['page_font'];
	$tcpdf['footer_font_size'] = 8;
	$tcpdf['footer_margin'] = 10;
	
	
	/************************************************************
	 * TCPDF image scale ratio
	 * ----------------------------------------------------------
	 * Image scale ratio (decimal format).
	 ***********************************************************/
	
	$tcpdf['image_scale'] = 4;
	
	
	/************************************************************
	 * TCPDF cell settings
	 * ----------------------------------------------------------
	 * Fontsize-to-height ratio, cell padding
	 ***********************************************************/
	
	$tcpdf['cell_height_ratio'] = 1.25;
	$tcpdf['cell_padding'] = 0;
	
	
	
