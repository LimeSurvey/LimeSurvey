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
 * FCKToolbarPanelButton Class: Handles the Fonts combo selector.
 */

var FCKToolbarFontFormatCombo = function( tooltip, style )
{
	this.CommandName = 'FontFormat' ;
	this.Label		= this.GetLabel() ;
	this.Tooltip	= tooltip ? tooltip : this.Label ;
	this.Style		= style ? style : FCK_TOOLBARITEM_ICONTEXT ;

	this.NormalLabel = 'Normal' ;

	this.PanelWidth = 190 ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarFontFormatCombo.prototype = new FCKToolbarSpecialCombo ;


FCKToolbarFontFormatCombo.prototype.GetLabel = function()
{
	return FCKLang.FontFormat ;
}

FCKToolbarFontFormatCombo.prototype.CreateItems = function( targetSpecialCombo )
{
	var oTargetDoc = targetSpecialCombo._Panel.Document ;

	// Add the Editor Area CSS to the panel to create a realistic preview.
	FCKTools.AppendStyleSheet( oTargetDoc, FCKConfig.ToolbarComboPreviewCSS ) ;

	// Add ID and Class to the body
	if ( FCKConfig.BodyId && FCKConfig.BodyId.length > 0 )
		oTargetDoc.body.id = FCKConfig.BodyId ;
	if ( FCKConfig.BodyClass && FCKConfig.BodyClass.length > 0 )
		oTargetDoc.body.className += ' ' + FCKConfig.BodyClass ;

	// Get the format names from the language file.
	var aNames = FCKLang['FontFormats'].split(';') ;
	var oNames = {
		p		: aNames[0],
		pre		: aNames[1],
		address	: aNames[2],
		h1		: aNames[3],
		h2		: aNames[4],
		h3		: aNames[5],
		h4		: aNames[6],
		h5		: aNames[7],
		h6		: aNames[8],
		div		: aNames[9]
	} ;

	// Get the available formats from the configuration file.
	var aTags = FCKConfig.FontFormats.split(';') ;

	for ( var i = 0 ; i < aTags.length ; i++ )
	{
		// Support for DIV in Firefox has been reintroduced on version 2.2.
//		if ( aTags[i] == 'div' && FCKBrowserInfo.IsGecko )
//			continue ;

		var sTag	= aTags[i] ;
		var sLabel	= oNames[sTag] ;

		if ( sTag == 'p' )
			this.NormalLabel = sLabel ;

		this._Combo.AddItem( sTag, '<div class="BaseFont"><' + sTag + '>' + sLabel + '</' + sTag + '></div>', sLabel ) ;
	}
}

if ( FCKBrowserInfo.IsIE )
{
	FCKToolbarFontFormatCombo.prototype.RefreshActiveItems = function( combo, value )
	{
//		FCKDebug.Output( 'FCKToolbarFontFormatCombo Value: ' + value ) ;

		// IE returns normal for DIV and P, so to avoid confusion, we will not show it if normal.
		if ( value == this.NormalLabel )
		{
			if ( combo.Label != '&nbsp;' )
				combo.DeselectAll(true) ;
		}
		else
		{
			if ( this._LastValue == value )
				return ;

			combo.SelectItemByLabel( value, true ) ;
		}

		this._LastValue = value ;
	}
}