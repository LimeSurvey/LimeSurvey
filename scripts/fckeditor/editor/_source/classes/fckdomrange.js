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
 */

var FCKDomRange = function( sourceWindow )
{
	this.Window = sourceWindow ;
}

FCKDomRange.prototype =
{

	_UpdateElementInfo : function()
	{
		if ( !this._Range )
			this.Release( true ) ;
		else
		{
			var eStart	= this._Range.startContainer ;
			var eEnd	= this._Range.endContainer ;

			var oElementPath = new FCKElementPath( eStart ) ;
			this.StartContainer		= oElementPath.LastElement ;
			this.StartBlock			= oElementPath.Block ;
			this.StartBlockLimit	= oElementPath.BlockLimit ;

			if ( eStart != eEnd )
				oElementPath = new FCKElementPath( eEnd ) ;
			this.EndContainer		= oElementPath.LastElement ;
			this.EndBlock			= oElementPath.Block ;
			this.EndBlockLimit		= oElementPath.BlockLimit ;
		}
	},

	CreateRange : function()
	{
		return new FCKW3CRange( this.Window.document ) ;
	},

	DeleteContents : function()
	{
		if ( this._Range )
		{
			this._Range.deleteContents() ;
			this._UpdateElementInfo() ;
		}
	},

	ExtractContents : function()
	{
		if ( this._Range )
		{
			var docFrag = this._Range.extractContents() ;
			this._UpdateElementInfo() ;
			return docFrag ;
		}
	},

	CheckIsCollapsed : function()
	{
		if ( this._Range )
			return this._Range.collapsed ;
	},

	Collapse : function( toStart )
	{
		if ( this._Range )
			this._Range.collapse( toStart ) ;

		this._UpdateElementInfo() ;
	},

	Clone : function()
	{
		var oClone = FCKTools.CloneObject( this ) ;

		if ( this._Range )
			oClone._Range = this._Range.cloneRange() ;

		return oClone ;
	},

	MoveToNodeContents : function( targetNode )
	{
		if ( !this._Range )
			this._Range = this.CreateRange() ;

		this._Range.selectNodeContents( targetNode ) ;

		this._UpdateElementInfo() ;
	},

	MoveToElementStart : function( targetElement )
	{
		this.SetStart(targetElement,1) ;
		this.SetEnd(targetElement,1) ;
	},

	// Moves to the first editing point inside a element. For example, in a
	// element tree like "<p><b><i></i></b> Text</p>", the start editing point
	// is "<p><b><i>^</i></b> Text</p>" (inside <i>).
	MoveToElementEditStart : function( targetElement )
	{
		var child ;

		while ( ( child = targetElement.firstChild ) && child.nodeType == 1 && FCKListsLib.EmptyElements[ child.nodeName.toLowerCase() ] == null )
			targetElement = child ;

		this.MoveToElementStart( targetElement ) ;
	},

	InsertNode : function( node )
	{
		if ( this._Range )
			this._Range.insertNode( node ) ;
	},

	CheckIsEmpty : function( ignoreEndBRs )
	{
		if ( this.CheckIsCollapsed() )
			return true ;

		// Inserts the contents of the range in a div tag.
		var eToolDiv = this.Window.document.createElement( 'div' ) ;
		this._Range.cloneContents().AppendTo( eToolDiv ) ;

		FCKDomTools.TrimNode( eToolDiv, ignoreEndBRs ) ;

		return ( eToolDiv.innerHTML.length == 0 ) ;
	},

	CheckStartOfBlock : function()
	{
		// Create a clone of the current range.
		var oTestRange = this.Clone() ;

		// Collapse it to its start point.
		oTestRange.Collapse( true ) ;

		// Move the start boundary to the start of the block.
		oTestRange.SetStart( oTestRange.StartBlock || oTestRange.StartBlockLimit, 1 ) ;

		var bIsStartOfBlock = oTestRange.CheckIsEmpty() ;

		oTestRange.Release() ;

		return bIsStartOfBlock ;
	},

	CheckEndOfBlock : function( refreshSelection )
	{
		// Create a clone of the current range.
		var oTestRange = this.Clone() ;

		// Collapse it to its end point.
		oTestRange.Collapse( false ) ;

		// Move the end boundary to the end of the block.
		oTestRange.SetEnd( oTestRange.EndBlock || oTestRange.EndBlockLimit, 2 ) ;

		var bIsEndOfBlock = oTestRange.CheckIsCollapsed() ;
		
		if ( !bIsEndOfBlock )
		{
			// Inserts the contents of the range in a div tag.
			var eToolDiv = this.Window.document.createElement( 'div' ) ;
			oTestRange._Range.cloneContents().AppendTo( eToolDiv ) ;
			FCKDomTools.TrimNode( eToolDiv, true ) ;
			
			// Find out if we are in an empty tree of inline elements, like <b><i><span></span></i></b>
			bIsEndOfBlock = true ;
			var eLastChild = eToolDiv ;
			while ( ( eLastChild = eLastChild.lastChild ) )
			{
				// Check the following:
				//		1. Is there more than one node in the parents children?
				//		2. Is the node not an element node?
				//		3. Is it not a inline element.
				if ( eLastChild.previousSibling || eLastChild.nodeType != 1 || FCKListsLib.InlineChildReqElements[ eLastChild.nodeName.toLowerCase() ] == null )
				{
					// So we are not in the end of the range.
					bIsEndOfBlock = false ;
					break ;
				}
			}
		}
		
		oTestRange.Release() ;

		if ( refreshSelection )
			this.Select() ;

		return bIsEndOfBlock ;
	},

	CreateBookmark : function()
	{
		// Create the bookmark info (random IDs).
		var oBookmark =
		{
			StartId	: 'fck_dom_range_start_' + (new Date()).valueOf() + '_' + Math.floor(Math.random()*1000),
			EndId	: 'fck_dom_range_end_' + (new Date()).valueOf() + '_' + Math.floor(Math.random()*1000)
		} ;

		var oDoc = this.Window.document ;
		var eSpan ;
		var oClone ;

		// For collapsed ranges, add just the start marker.
		if ( !this.CheckIsCollapsed() )
		{
			eSpan = oDoc.createElement( 'span' ) ;
			eSpan.id = oBookmark.EndId ;
			eSpan.innerHTML = '&nbsp;' ;	// For IE, it must have something inside, otherwise it may be removed during operations.

			oClone = this.Clone() ;
			oClone.Collapse( false ) ;
			oClone.InsertNode( eSpan ) ;
		}

		eSpan = oDoc.createElement( 'span' ) ;
		eSpan.id = oBookmark.StartId ;
		eSpan.innerHTML = '&nbsp;' ;	// For IE, it must have something inside, otherwise it may be removed during operations.

		oClone = this.Clone() ;
		oClone.Collapse( true ) ;
		oClone.InsertNode( eSpan ) ;

		return oBookmark ;
	},

	MoveToBookmark : function( bookmark, preserveBookmark )
	{
		var oDoc = this.Window.document ;

		var eStartSpan	=  oDoc.getElementById( bookmark.StartId ) ;
		var eEndSpan	=  oDoc.getElementById( bookmark.EndId ) ;

		this.SetStart( eStartSpan, 3 ) ;

		if ( !preserveBookmark )
			FCKDomTools.RemoveNode( eStartSpan ) ;

		// If collapsed, the start span will not be available.
		if ( eEndSpan )
		{
			this.SetEnd( eEndSpan, 3 ) ;

			if ( !preserveBookmark )
				FCKDomTools.RemoveNode( eEndSpan ) ;
		}
		else
			this.Collapse( true ) ;
	},

	/*
	 * Moves the position of the start boundary of the range to a specific position
	 * relatively to a element.
	 *		@position:
	 *			1 = After Start		<target>^contents</target>
	 *			2 = Before End		<target>contents^</target>
	 *			3 = Before Start	^<target>contents</target>
	 *			4 = After End		<target>contents</target>^
	 */
	SetStart : function( targetElement, position )
	{
		var oRange = this._Range ;
		if ( !oRange )
			oRange = this._Range = this.CreateRange() ;

		switch( position )
		{
			case 1 :		// After Start		<target>^contents</target>
				oRange.setStart( targetElement, 0 ) ;
				break ;

			case 2 :		// Before End		<target>contents^</target>
				oRange.setStart( targetElement, targetElement.childNodes.length ) ;
				break ;

			case 3 :		// Before Start		^<target>contents</target>
				oRange.setStartBefore( targetElement ) ;
				break ;

			case 4 :		// After End		<target>contents</target>^
				oRange.setStartAfter( targetElement ) ;
		}
		this._UpdateElementInfo() ;
	},

	/*
	 * Moves the position of the start boundary of the range to a specific position
	 * relatively to a element.
	 *		@position:
	 *			1 = After Start		<target>^contents</target>
	 *			2 = Before End		<target>contents^</target>
	 *			3 = Before Start	^<target>contents</target>
	 *			4 = After End		<target>contents</target>^
	 */
	SetEnd : function( targetElement, position )
	{
		var oRange = this._Range ;
		if ( !oRange )
			oRange = this._Range = this.CreateRange() ;

		switch( position )
		{
			case 1 :		// After Start		<target>^contents</target>
				oRange.setEnd( targetElement, 0 ) ;
				break ;

			case 2 :		// Before End		<target>contents^</target>
				oRange.setEnd( targetElement, targetElement.childNodes.length ) ;
				break ;

			case 3 :		// Before Start		^<target>contents</target>
				oRange.setEndBefore( targetElement ) ;
				break ;

			case 4 :		// After End		<target>contents</target>^
				oRange.setEndAfter( targetElement ) ;
		}
		this._UpdateElementInfo() ;
	},

	Expand : function( unit )
	{
		var oNode, oSibling ;

		switch ( unit )
		{
			case 'block_contents' :
				if ( this.StartBlock )
					this.SetStart( this.StartBlock, 1 ) ;
				else
				{
					// Get the start node for the current range.
					oNode = this._Range.startContainer ;

					// If it is an element, get the current child node for the range (in the offset).
					// If the offset node is not available, the the first one.
					if ( oNode.nodeType == 1 )
					{
						if ( !( oNode = oNode.childNodes[ this._Range.startOffset ] ) )
							oNode = oNode.firstChild ;
					}

					// Not able to defined the current position.
					if ( !oNode )
						return ;

					// We must look for the left boundary, relative to the range
					// start, which is limited by a block element.
					while ( true )
					{
						oSibling = oNode.previousSibling ;

						if ( !oSibling )
						{
							// Continue if we are not yet in the block limit (inside a <b>, for example).
							if ( oNode.parentNode != this.StartBlockLimit )
								oNode = oNode.parentNode ;
							else
								break ;
						}
						else if ( oSibling.nodeType != 1 || !(/^(?:P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|DT|DE)$/).test( oSibling.nodeName.toUpperCase() ) )
						{
							// Continue if the sibling is not a block tag.
							oNode = oSibling ;
						}
						else
							break ;
					}

					this._Range.setStartBefore( oNode ) ;
				}

				if ( this.EndBlock )
					this.SetEnd( this.EndBlock, 2 ) ;
				else
				{
					oNode = this._Range.endContainer ;
					if ( oNode.nodeType == 1 )
						oNode = oNode.childNodes[ this._Range.endOffset ] || oNode.lastChild ;

					if ( !oNode )
						return ;

					// We must look for the right boundary, relative to the range
					// end, which is limited by a block element.
					while ( true )
					{
						oSibling = oNode.nextSibling ;

						if ( !oSibling )
						{
							// Continue if we are not yet in the block limit (inide a <b>, for example).
							if ( oNode.parentNode != this.EndBlockLimit )
								oNode = oNode.parentNode ;
							else
								break ;
						}
						else if ( oSibling.nodeType != 1 || !(/^(?:P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|DT|DE)$/).test( oSibling.nodeName.toUpperCase() ) )
						{
							// Continue if the sibling is not a block tag.
							oNode = oSibling ;
						}
						else
							break ;
					}

					this._Range.setEndAfter( oNode ) ;
				}

				this._UpdateElementInfo() ;
		}
	},

	Release : function( preserveWindow )
	{
		if ( !preserveWindow )
			this.Window = null ;

		this.StartContainer = null ;
		this.StartBlock = null ;
		this.StartBlockLimit = null ;
		this.EndContainer = null ;
		this.EndBlock = null ;
		this.EndBlockLimit = null ;
		this._Range = null ;
	}
} ;