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
 * Active selection functions. (IE specific implementation)
 */

// Get the selection type.
FCKSelection.GetType = function()
{
	return FCK.EditorDocument.selection.type ;
} ;

// Retrieves the selected element (if any), just in the case that a single
// element (object like and image or a table) is selected.
FCKSelection.GetSelectedElement = function()
{
	if ( this.GetType() == 'Control' )
	{
		var oRange = FCK.EditorDocument.selection.createRange() ;

		if ( oRange && oRange.item )
			return FCK.EditorDocument.selection.createRange().item(0) ;
	}
	return null ;
} ;

FCKSelection.GetParentElement = function()
{
	switch ( this.GetType() )
	{
		case 'Control' :
			return FCKSelection.GetSelectedElement().parentElement ;
		case 'None' :
			return null ;
		default :
			return FCK.EditorDocument.selection.createRange().parentElement() ;
	}
} ;

FCKSelection.SelectNode = function( node )
{
	FCK.Focus() ;
	FCK.EditorDocument.selection.empty() ;
	var oRange ;
	try
	{
		// Try to select the node as a control.
		oRange = FCK.EditorDocument.body.createControlRange() ;
		oRange.addElement( node ) ;
	}
	catch(e)
	{
		// If failed, select it as a text range.
		oRange = FCK.EditorDocument.body.createTextRange() ;
		oRange.moveToElementText( node ) ;
	}

	oRange.select() ;
} ;

FCKSelection.Collapse = function( toStart )
{
	FCK.Focus() ;
	if ( this.GetType() == 'Text' )
	{
		var oRange = FCK.EditorDocument.selection.createRange() ;
		oRange.collapse( toStart == null || toStart === true ) ;
		oRange.select() ;
	}
} ;

// The "nodeTagName" parameter must be Upper Case.
FCKSelection.HasAncestorNode = function( nodeTagName )
{
	var oContainer ;

	if ( FCK.EditorDocument.selection.type == "Control" )
	{
		oContainer = this.GetSelectedElement() ;
	}
	else
	{
		var oRange  = FCK.EditorDocument.selection.createRange() ;
		oContainer = oRange.parentElement() ;
	}

	while ( oContainer )
	{
		if ( oContainer.tagName == nodeTagName ) return true ;
		oContainer = oContainer.parentNode ;
	}

	return false ;
} ;

// The "nodeTagName" parameter must be UPPER CASE.
FCKSelection.MoveToAncestorNode = function( nodeTagName )
{
	var oNode, oRange ;

	if ( ! FCK.EditorDocument )
		return null ;

	if ( FCK.EditorDocument.selection.type == "Control" )
	{
		oRange = FCK.EditorDocument.selection.createRange() ;
		for ( i = 0 ; i < oRange.length ; i++ )
		{
			if (oRange(i).parentNode)
			{
				oNode = oRange(i).parentNode ;
				break ;
			}
		}
	}
	else
	{
		oRange  = FCK.EditorDocument.selection.createRange() ;
		oNode = oRange.parentElement() ;
	}

	while ( oNode && oNode.nodeName != nodeTagName )
		oNode = oNode.parentNode ;

	return oNode ;
} ;

FCKSelection.Delete = function()
{
	// Gets the actual selection.
	var oSel = FCK.EditorDocument.selection ;

	// Deletes the actual selection contents.
	if ( oSel.type.toLowerCase() != "none" )
	{
		oSel.clear() ;
	}

	return oSel ;
} ;


