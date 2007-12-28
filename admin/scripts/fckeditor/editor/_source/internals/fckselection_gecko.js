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
	// By default set the type to "Text".
	var type = 'Text' ;

	// Check if the actual selection is a Control (IMG, TABLE, HR, etc...).

	var sel ;
	try { sel = FCK.EditorWindow.getSelection() ; } catch (e) {}

	if ( sel && sel.rangeCount == 1 )
	{
		var range = sel.getRangeAt(0) ;
		if ( range.startContainer == range.endContainer
			&& ( range.endOffset - range.startOffset ) == 1
			&& range.startContainer.nodeType == 1
			&& FCKListsLib.StyleObjectElements[ range.startContainer.childNodes[ range.startOffset ].nodeName.toLowerCase() ] )
		{
			type = 'Control' ;
		}
	}

	return type ;
}

// Retrieves the selected element (if any), just in the case that a single
// element (object like and image or a table) is selected.
FCKSelection.GetSelectedElement = function()
{
	var selectedElement = null ;

	var selection = !!FCK.EditorWindow && FCK.EditorWindow.getSelection() ;

	if ( selection && selection.anchorNode && selection.anchorNode.nodeType == 1 )
	{
		if ( this.GetType() == 'Control' )
		{
			// This one is good for all browsers, expect Safari Mac.
			selectedElement = selection.anchorNode.childNodes[ selection.anchorOffset ] ;

			// For Safari (Mac only), the anchor node for a control selection is
			// the control itself, which seams logic. FF and Opera use the parent
			// as the anchor node, pointing to the control with the offset.
			// As FF created the selection "standard", Safari would do better by
			// following their steps.
			if ( !selectedElement )
				selectedElement = selection.anchorNode ;
			else if ( selectedElement.nodeType != 1 )
				return null ;
		}
	}

	return selectedElement ;
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
			// make the common case fast - for collapsed/nearly collapsed selections just return anchor.parent.
			if ( oSel.anchorNode && oSel.anchorNode == oSel.focusNode )
				return oSel.anchorNode.parentNode ;

			// looks like we're having a large selection here. To make the behavior same as IE's TextRange.parentElement(),
			// we need to find the nearest ancestor node which encapsulates both the beginning and the end of the selection.
			// TODO: A simpler logic can be found.
			var anchorPath = new FCKElementPath( oSel.anchorNode ) ;
			var focusPath = new FCKElementPath( oSel.focusNode ) ;
			var deepPath = null ;
			var shallowPath = null ;
			if ( anchorPath.Elements.length > focusPath.Elements.length )
			{
				deepPath = anchorPath.Elements ;
				shallowPath = focusPath.Elements ;
			}
			else
			{
				deepPath = focusPath.Elements ;
				shallowPath = anchorPath.Elements ;
			}

			var deepPathBase = deepPath.length - shallowPath.length ;
			for( var i = 0 ; i < shallowPath.length ; i++)
			{
				if ( deepPath[deepPathBase + i] == shallowPath[i])
					return shallowPath[i];
			}
			return null ;
		}
	}
	return null ;
}

FCKSelection.GetBoundaryParentElement = function( startBoundary )
{
	if ( ! FCK.EditorWindow )
		return null ;
	if ( this.GetType() == 'Control' )
		return FCKSelection.GetSelectedElement().parentNode ;
	else
	{
		var oSel = FCK.EditorWindow.getSelection() ;
		if ( oSel && oSel.rangeCount > 0 )
		{
			var range = oSel.getRangeAt( startBoundary ? 0 : ( oSel.rangeCount - 1 ) ) ;

			var element = startBoundary ? range.startContainer : range.endContainer ;

			return ( element.nodeType == 1 ? element : element.parentNode ) ;
		}
	}
	return null ;
}

FCKSelection.SelectNode = function( element )
{
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
