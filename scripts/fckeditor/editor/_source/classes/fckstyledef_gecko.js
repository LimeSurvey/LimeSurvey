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
 * FCKStyleDef Class: represents a single stylke definition. (Gecko specific)
 */

FCKStyleDef.prototype.ApplyToSelection = function()
{
	if ( FCKSelection.GetType() == 'Text' && !this.IsObjectElement )
	{
		var oSelection = FCK.ToolbarSet.CurrentInstance.EditorWindow.getSelection() ;

		// Create the main element.
		var e = FCK.ToolbarSet.CurrentInstance.EditorDocument.createElement( this.Element ) ;

		for ( var i = 0 ; i < oSelection.rangeCount ; i++ )
		{
			e.appendChild( oSelection.getRangeAt(i).extractContents() ) ;
		}

		// Set the attributes.
		this._AddAttributes( e ) ;

		// Remove the duplicated elements.
		this._RemoveDuplicates( e ) ;

		var oRange = oSelection.getRangeAt(0) ;
		oRange.insertNode( e ) ;
	}
	else
	{
		var oControl = FCK.ToolbarSet.CurrentInstance.Selection.GetSelectedElement() ;
		if ( oControl.tagName == this.Element )
			this._AddAttributes( oControl ) ;
	}
}

FCKStyleDef.prototype._AddAttributes = function( targetElement )
{
	for ( var a in this.Attributes )
	{
		switch ( a.toLowerCase() )
		{
			case 'src' :
				targetElement.setAttribute( '_fcksavedurl', this.Attributes[a], 0 ) ;
			default :
				targetElement.setAttribute( a, this.Attributes[a], 0 ) ;
		}
	}
}

FCKStyleDef.prototype._RemoveDuplicates = function( parent )
{
	for ( var i = 0 ; i < parent.childNodes.length ; i++ )
	{
		var oChild = parent.childNodes[i] ;

		if ( oChild.nodeType != 1 )
			continue ;

		this._RemoveDuplicates( oChild ) ;

		if ( this.IsEqual( oChild ) )
			FCKTools.RemoveOuterTags( oChild ) ;
	}
}

FCKStyleDef.prototype.IsEqual = function( e )
{
	if ( e.tagName != this.Element )
		return false ;

	for ( var a in this.Attributes )
	{
		if ( e.getAttribute( a ) != this.Attributes[a] )
			return false ;
	}

	return true ;
}

FCKStyleDef.prototype._RemoveMe = function( elementToCheck )
{
	if ( ! elementToCheck )
		return ;

	var oParent = elementToCheck.parentNode ;

	if ( elementToCheck.nodeType == 1 && this.IsEqual( elementToCheck ) )
	{
		if ( this.IsObjectElement )
		{
			for ( var a in this.Attributes )
				elementToCheck.removeAttribute( a, 0 ) ;
			return ;
		}
		else
			FCKTools.RemoveOuterTags( elementToCheck ) ;
	}

	this._RemoveMe( oParent ) ;
}