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
 * Class for working with a selection range, much like the W3C DOM Range, but
 * it is not intented to be an implementation of the W3C interface.
 * (IE Implementation)
 */

FCKDomRange.prototype.MoveToSelection = function()
{
	this.Release( true ) ;

	this._Range = new FCKW3CRange( this.Window.document ) ;

	var oSel = this.Window.document.selection ;

	if ( oSel.type != 'Control' )
	{
		// Set the start boundary.
		eMarker = this._GetSelectionMarkerTag( true ) ;
		this._Range.setStart( eMarker.parentNode, FCKDomTools.GetIndexOf( eMarker ) ) ;
		eMarker.parentNode.removeChild( eMarker ) ;

		// Set the end boundary.
		var eMarker = this._GetSelectionMarkerTag( false ) ;
		this._Range.setEnd( eMarker.parentNode, FCKDomTools.GetIndexOf( eMarker ) ) ;
		eMarker.parentNode.removeChild( eMarker ) ;

		this._UpdateElementInfo() ;
	}
	else
	{
		var oControl = oSel.createRange().item(0) ;

		if ( oControl )
		{
			this._Range.setStartBefore( oControl ) ;
			this._Range.setEndAfter( oControl ) ;
			this._UpdateElementInfo() ;
		}
	}
}

FCKDomRange.prototype.Select = function()
{
	if ( this._Range )
	{
		var bIsCollapsed = this.CheckIsCollapsed() ;

		// Create marker tags for the start and end boundaries.
		var eStartMarker	= this._GetRangeMarkerTag( true ) ;

		if ( !bIsCollapsed )
			var eEndMarker	= this._GetRangeMarkerTag( false ) ;

		// Create the main range which will be used for the selection.
		var oIERange = this.Window.document.body.createTextRange() ;

		// Position the range at the start boundary.
		oIERange.moveToElementText( eStartMarker ) ;
		oIERange.moveStart( 'character', 1 ) ;

		if ( !bIsCollapsed )
		{
			// Create a tool range for the end.
			var oIERangeEnd = this.Window.document.body.createTextRange() ;

			// Position the tool range at the end.
			oIERangeEnd.moveToElementText( eEndMarker ) ;

			// Move the end boundary of the main range to match the tool range.
			oIERange.setEndPoint( 'EndToEnd', oIERangeEnd ) ;
			oIERange.moveEnd( 'character', -1 ) ;
		}

		// Remove the markers (reset the position, because of the changes in the DOM tree).
		this._Range.setStartBefore( eStartMarker ) ;
		eStartMarker.parentNode.removeChild( eStartMarker ) ;

		if ( bIsCollapsed )
		{
			// The following trick is needed so IE makes collapsed selections
			// inside empty blocks visible (expands the block).
			try
			{
				oIERange.pasteHTML('&nbsp;') ;
				oIERange.moveStart( 'character', -1 ) ;
			}
			catch (e){}
			oIERange.select() ;
			oIERange.pasteHTML('') ;
		}
		else
		{
			this._Range.setEndBefore( eEndMarker ) ;
			eEndMarker.parentNode.removeChild( eEndMarker ) ;
			oIERange.select() ;
		}
	}
}

FCKDomRange.prototype._GetSelectionMarkerTag = function( toStart )
{
	// Get a range for the start boundary.
	var oRange = this.Window.document.selection.createRange() ;
	oRange.collapse( toStart === true ) ;

	// Paste a marker element at the collapsed range and get it from the DOM.
	var sMarkerId = 'fck_dom_range_temp_' + (new Date()).valueOf() + '_' + Math.floor(Math.random()*1000) ;
	oRange.pasteHTML( '<span id="' + sMarkerId + '"></span>' ) ;
	return this.Window.document.getElementById( sMarkerId ) ;
}

FCKDomRange.prototype._GetRangeMarkerTag = function( toStart )
{
	// Get a range for the start boundary.
	var oRange = this._Range ;

	// insertNode() will add the node at the beginning of the Range, updating
	// the endOffset if necessary. So, we can work with the current range in this case.
	if ( !toStart )
	{
		oRange = oRange.cloneRange() ;
		oRange.collapse( toStart === true ) ;
	}

	var eSpan = this.Window.document.createElement( 'span' ) ;
	eSpan.innerHTML = '&nbsp;' ;
	oRange.insertNode( eSpan ) ;

	return eSpan ;
}