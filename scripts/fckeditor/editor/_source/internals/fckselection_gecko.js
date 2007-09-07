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
 * Active selection functions. (Gecko specific implementation)
 */

// Get the selection type (like document.select.type in IE).
FCKSelection.GetType = function()
{
//	if ( ! this._Type )
//	{
		// By default set the type to "Text".
		this._Type = 'Text' ;

		// Check if the actual selection is a Control (IMG, TABLE, HR, etc...).
		var oSel ;
		try { oSel = FCK.EditorWindow.getSelection() ; }
		catch (e) {}

		if ( oSel && oSel.rangeCount == 1 )
		{
			var oRange = oSel.getRangeAt(0) ;
			if ( oRange.startContainer == oRange.endContainer && (oRange.endOffset - oRange.startOffset) == 1 && oRange.startContainer.nodeType != Node.TEXT_NODE )
				this._Type = 'Control' ;
		}
//	}
	return this._Type ;
}

// Retrieves the selected element (if any), just in the case that a single
// element (object like and image or a table) is selected.
FCKSelection.GetSelectedElement = function()
{
	if ( this.GetType() == 'Control' )
	{
		var oSel = FCK.EditorWindow.getSelection() ;
		return oSel.anchorNode.childNodes[ oSel.anchorOffset ] ;
	}
	return null ;
}

FCKSelection.GetParentElement = function()
{
	if ( this.GetType() == 'Control' )
		return FCKSelection.GetSelectedElement().parentNode ;
	else
	{
		var oSel = FCK.EditorWindow.getSelection() ;
		if ( oSel )
		{
			var oNode = oSel.anchorNode ;

			while ( oNode && oNode.nodeType != 1 )
				oNode = oNode.parentNode ;

			return oNode ;
		}
	}
	return null ;
}

FCKSelection.SelectNode = function( element )
{
//	FCK.Focus() ;

	var oRange = FCK.EditorDocument.createRange() ;
	oRange.selectNode( element ) ;

	var oSel = FCK.EditorWindow.getSelection() ;
	oSel.removeAllRanges() ;
	oSel.addRange( oRange ) ;
}

FCKSelection.Collapse = function( toStart )
{
	var oSel = FCK.EditorWindow.getSelection() ;

	if ( toStart == null || toStart === true )
		oSel.collapseToStart() ;
	else
		oSel.collapseToEnd() ;
}

// The "nodeTagName" parameter must be Upper Case.
FCKSelection.HasAncestorNode = function( nodeTagName )
{
	var oContainer = this.GetSelectedElement() ;
	if ( ! oContainer && FCK.EditorWindow )
	{
		try		{ oContainer = FCK.EditorWindow.getSelection().getRangeAt(0).startContainer ; }
		catch(e){}
	}

	while ( oContainer )
	{
		if ( oContainer.nodeType == 1 && oContainer.tagName == nodeTagName ) return true ;
		oContainer = oContainer.parentNode ;
	}

	return false ;
}

// The "nodeTagName" parameter must be Upper Case.
FCKSelection.MoveToAncestorNode = function( nodeTagName )
{
	var oNode ;

	var oContainer = this.GetSelectedElement() ;
	if ( ! oContainer )
		oContainer = FCK.EditorWindow.getSelection().getRangeAt(0).startContainer ;

	while ( oContainer )
	{
		if ( oContainer.nodeName == nodeTagName )
			return oContainer ;

		oContainer = oContainer.parentNode ;
	}
	return null ;
}

FCKSelection.Delete = function()
{
	// Gets the actual selection.
	var oSel = FCK.EditorWindow.getSelection() ;

	// Deletes the actual selection contents.
	for ( var i = 0 ; i < oSel.rangeCount ; i++ )
	{
		oSel.getRangeAt(i).deleteContents() ;
	}

	return oSel ;
}
