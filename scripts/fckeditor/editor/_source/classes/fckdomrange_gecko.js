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
 * (Gecko Implementation)
 */

FCKDomRange.prototype.MoveToSelection = function()
{
	this.Release( true ) ;

	var oSel = this.Window.getSelection() ;

	if ( oSel.rangeCount == 1 )
	{
		this._Range = FCKW3CRange.CreateFromRange( this.Window.document, oSel.getRangeAt(0) ) ;
		this._UpdateElementInfo() ;
	}
}

FCKDomRange.prototype.Select = function()
{
	var oRange = this._Range ;
	if ( oRange )
	{
		var oDocRange = this.Window.document.createRange() ;
		oDocRange.setStart( oRange.startContainer, oRange.startOffset ) ;

		try
		{
			oDocRange.setEnd( oRange.endContainer, oRange.endOffset ) ;
		}
		catch ( e )
		{
			// There is a bug in Firefox implementation (it would be too easy
			// otherwhise). The new start can't be after the end (W3C says it can).
			// So, let's create a new range and collapse it to the desired point.
			if ( e.toString().Contains( 'NS_ERROR_ILLEGAL_VALUE' ) )
			{
				oRange.collapse( true ) ;
				oDocRange.setEnd( oRange.endContainer, oRange.endOffset ) ;
			}
			else
				throw( e ) ;
		}

		var oSel = this.Window.getSelection() ;
		oSel.removeAllRanges() ;

		// We must add a clone otherwise Firefox will have rendering issues.
		oSel.addRange( oDocRange ) ;
	}
}
