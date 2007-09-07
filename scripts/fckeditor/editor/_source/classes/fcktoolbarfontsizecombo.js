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

var FCKToolbarFontSizeCombo = function( tooltip, style )
{
	this.CommandName	= 'FontSize' ;
	this.Label		= this.GetLabel() ;
	this.Tooltip	= tooltip ? tooltip : this.Label ;
	this.Style		= style ? style : FCK_TOOLBARITEM_ICONTEXT ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarFontSizeCombo.prototype = new FCKToolbarSpecialCombo ;


FCKToolbarFontSizeCombo.prototype.GetLabel = function()
{
	return FCKLang.FontSize ;
}

FCKToolbarFontSizeCombo.prototype.CreateItems = function( targetSpecialCombo )
{
	targetSpecialCombo.FieldWidth = 70 ;

	var aSizes = FCKConfig.FontSizes.split(';') ;

	for ( var i = 0 ; i < aSizes.length ; i++ )
	{
		var aSizeParts = aSizes[i].split('/') ;
		this._Combo.AddItem( aSizeParts[0], '<font size="' + aSizeParts[0] + '">' + aSizeParts[1] + '</font>', aSizeParts[1] ) ;
	}
}