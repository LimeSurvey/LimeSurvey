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

var FCKToolbarFontsCombo = function( tooltip, style )
{
	this.CommandName	= 'FontName' ;
	this.Label		= this.GetLabel() ;
	this.Tooltip	= tooltip ? tooltip : this.Label ;
	this.Style		= style ? style : FCK_TOOLBARITEM_ICONTEXT ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarFontsCombo.prototype = new FCKToolbarSpecialCombo ;


FCKToolbarFontsCombo.prototype.GetLabel = function()
{
	return FCKLang.Font ;
}

FCKToolbarFontsCombo.prototype.CreateItems = function( targetSpecialCombo )
{
	var aFonts = FCKConfig.FontNames.split(';') ;

	for ( var i = 0 ; i < aFonts.length ; i++ )
		this._Combo.AddItem( aFonts[i], '<font face="' + aFonts[i] + '" style="font-size: 12px">' + aFonts[i] + '</font>' ) ;
}