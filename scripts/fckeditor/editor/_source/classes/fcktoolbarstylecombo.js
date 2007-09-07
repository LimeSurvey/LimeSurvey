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

var FCKToolbarStyleCombo = function( tooltip, style )
{
	this.CommandName = 'Style' ;
	this.Label		= this.GetLabel() ;
	this.Tooltip	= tooltip ? tooltip : this.Label ;
	this.Style		= style ? style : FCK_TOOLBARITEM_ICONTEXT ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarStyleCombo.prototype = new FCKToolbarSpecialCombo ;


FCKToolbarStyleCombo.prototype.GetLabel = function()
{
	return FCKLang.Style ;
}

FCKToolbarStyleCombo.prototype.CreateItems = function( targetSpecialCombo )
{
	var oTargetDoc = targetSpecialCombo._Panel.Document ;

	// Add the Editor Area CSS to the panel so the style classes are previewed correctly.
	FCKTools.AppendStyleSheet( oTargetDoc, FCKConfig.ToolbarComboPreviewCSS ) ;
	oTargetDoc.body.className += ' ForceBaseFont' ;

	// Add ID and Class to the body
	if ( FCKConfig.BodyId && FCKConfig.BodyId.length > 0 )
		oTargetDoc.body.id = FCKConfig.BodyId ;
	if ( FCKConfig.BodyClass && FCKConfig.BodyClass.length > 0 )
		oTargetDoc.body.className += ' ' + FCKConfig.BodyClass ;


	// For some reason Gecko is blocking inside the "RefreshVisibleItems" function.
	// The problem is present only in old versions
	if ( !( FCKBrowserInfo.IsGecko && FCKBrowserInfo.IsGecko10 ) )
		targetSpecialCombo.OnBeforeClick = this.RefreshVisibleItems ;

	// Add the styles to the special combo.
	var aCommandStyles = FCK.ToolbarSet.CurrentInstance.Commands.GetCommand( this.CommandName ).Styles ;
	for ( var s in aCommandStyles )
	{
		var oStyle = aCommandStyles[s] ;
		var oItem ;

		if ( oStyle.IsObjectElement )
			oItem = targetSpecialCombo.AddItem( s, s ) ;
		else
			oItem = targetSpecialCombo.AddItem( s, oStyle.GetOpenerTag() + s + oStyle.GetCloserTag() ) ;

		oItem.Style = oStyle ;
	}
}

FCKToolbarStyleCombo.prototype.RefreshActiveItems = function( targetSpecialCombo )
{
	// Clear the actual selection.
	targetSpecialCombo.DeselectAll() ;

	// Get the active styles.
	var aStyles = FCK.ToolbarSet.CurrentInstance.Commands.GetCommand( this.CommandName ).GetActiveStyles() ;

	if ( aStyles.length > 0 )
	{
		// Select the active styles in the combo.
		for ( var i = 0 ; i < aStyles.length ; i++ )
			targetSpecialCombo.SelectItem( aStyles[i].Name ) ;

		// Set the combo label to the first style in the collection.
		targetSpecialCombo.SetLabelById( aStyles[0].Name ) ;
	}
	else
		targetSpecialCombo.SetLabel('') ;
}

FCKToolbarStyleCombo.prototype.RefreshVisibleItems = function( targetSpecialCombo )
{
	if ( FCKSelection.GetType() == 'Control' )
		var sTagName = FCKSelection.GetSelectedElement().tagName ;

	for ( var i in targetSpecialCombo.Items )
	{
		var oItem = targetSpecialCombo.Items[i] ;
		if ( ( sTagName && oItem.Style.Element == sTagName ) || ( ! sTagName && ! oItem.Style.IsObjectElement ) )
			oItem.style.display = '' ;
		else
			oItem.style.display = 'none' ;	// For some reason Gecko is blocking here.
	}
}