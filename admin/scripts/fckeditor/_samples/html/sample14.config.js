/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration settings used by the XHTML 1.1 sample page (sample14.html).
 */

// Our intention is force all formatting features to use CSS classes or
// semantic aware elements.

// Load our custom CSS files for this sample.
// We are using "BasePath" just for this sample convenience. In normal
// situations it would be just pointed to the file directly,
// like "/css/myfile.css".
FCKConfig.EditorAreaCSS = FCKConfig.BasePath + '../_samples/html/sample14.styles.css' ;

/**
 * Core styles.
 */
FCKConfig.CoreStyles.Bold			= { Element : 'span', Attributes : { 'class' : 'Bold' } } ;
FCKConfig.CoreStyles.Italic			= { Element : 'span', Attributes : { 'class' : 'Italic' } } ;
FCKConfig.CoreStyles.Underline		= { Element : 'span', Attributes : { 'class' : 'Underline' } } ;
FCKConfig.CoreStyles.StrikeThrough	= { Element : 'span', Attributes : { 'class' : 'StrikeThrough' } } ;

/**
 * Font face
 */
// List of fonts available in the toolbar combo. Each font definition is
// separated by a semi-colon (;). We are using class names here, so each font
// is defined by {Class Name}/{Combo Label}.
FCKConfig.FontNames = 'FontComic/Comic Sans MS;FontCourier/Courier New;FontTimes/Times New Roman' ;

// Define the way font elements will be applied to the document. The "span"
// element will be used. When a font is selected, the font name defined in the
// above list is passed to this definition with the name "Font", being it
// injected in the "class" attribute.
// We must also instruct the editor to replace span elements that are used to
// set the font (Overrides).
FCKConfig.CoreStyles.FontFace =
	{
		Element		: 'span',
		Attributes	: { 'class' : '#("Font")' },
		Overrides	: [ { Element : 'span', Attributes : { 'class' : /^Font(?:Comic|Courier|Times)$/ } } ]
	} ;

/**
 * Font sizes.
 */
FCKConfig.FontSizes		= 'FontSmaller/Smaller;FontLarger/Larger;FontSmall/8pt;FontBig/14pt;FontDouble/Double Size' ;
FCKConfig.CoreStyles.Size =
	{
		Element		: 'span',
		Attributes	: { 'class' : '#("Size")' },
		Overrides	: [ { Element : 'span', Attributes : { 'class' : /^Font(?:Smaller|Larger|Small|Big|Double)$/ } } ]
	} ;

/**
 * Font colors.
 */
FCKConfig.EnableMoreFontColors = false ;
FCKConfig.FontColors = 'ff9900/FontColor1,0066cc/FontColor2,ff0000/FontColor3' ;
FCKConfig.CoreStyles.Color =
	{
		Element		: 'span',
		Attributes	: { 'class' : '#("Color")' },
		Overrides	: [ { Element : 'span', Attributes : { 'class' : /^FontColor(?:1|2|3)$/ } } ]
	} ;

FCKConfig.CoreStyles.BackColor =
	{
		Element		: 'span',
		Attributes	: { 'class' : '#("Color")BG' },
		Overrides	: [ { Element : 'span', Attributes : { 'class' : /^FontColor(?:1|2|3)BG$/ } } ]
	} ;

/**
 * Indentation.
 */
FCKConfig.IndentClasses = [ 'Indent1', 'Indent2', 'Indent3' ] ;

/**
 * Paragraph justification.
 */
FCKConfig.JustifyClasses = [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull' ] ;

/**
 * Styles combo.
 */
FCKConfig.StylesXmlPath = '' ;
FCKConfig.CustomStyles =
	{
		'Strong Emphasis' : { Element : 'strong' },
		'Emphasis' : { Element : 'em' },

		'Computer Code' : { Element : 'code' },
		'Keyboard Phrase' : { Element : 'kbd' },
		'Sample Text' : { Element : 'samp' },
		'Variable' : { Element : 'var' },

		'Deleted Text' : { Element : 'del' },
		'Inserted Text' : { Element : 'ins' },

		'Cited Work' : { Element : 'cite' },
		'Inline Quotation' : { Element : 'q' }
	} ;
