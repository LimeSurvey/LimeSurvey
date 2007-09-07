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
 * Utility functions to work with the DOM.
 */

var FCKDomTools =
{
	MoveChildren : function( source, target )
	{
		if ( source == target )
			return ;

		var eChild ;
		while ( (eChild = source.firstChild) )
			target.appendChild( source.removeChild( eChild ) ) ;
	},

	// Remove blank spaces from the beginning and the end of the contents of a node.
	TrimNode : function( node, ignoreEndBRs )
	{
		this.LTrimNode( node ) ;
		this.RTrimNode( node, ignoreEndBRs ) ;
	},

	LTrimNode : function( node )
	{
		var eChildNode ;

		while ( (eChildNode = node.firstChild) )
		{
			if ( eChildNode.nodeType == 3 )
			{
				var sTrimmed = eChildNode.nodeValue.LTrim() ;
				var iOriginalLength = eChildNode.nodeValue.length ;

				if ( sTrimmed.length == 0 )
				{
					node.removeChild( eChildNode ) ;
					continue ;
				}
				else if ( sTrimmed.length < iOriginalLength )
				{
					eChildNode.splitText( iOriginalLength - sTrimmed.length ) ;
					node.removeChild( node.firstChild ) ;
				}
			}
			break ;
		}
	},

	RTrimNode : function( node, ignoreEndBRs )
	{
		var eChildNode ;

		while ( (eChildNode = node.lastChild) )
		{
			switch ( eChildNode.nodeType )
			{
				case 1 :
					if ( eChildNode.nodeName.toUpperCase() == 'BR' && ( ignoreEndBRs || eChildNode.getAttribute( 'type', 2 ) == '_moz' ) )
					{
						// Use "eChildNode.parentNode" instead of "node" to avoid IE bug (#324).
						eChildNode.parentNode.removeChild( eChildNode ) ;
						continue ;
					}
					break ;

				case 3 :
					var sTrimmed = eChildNode.nodeValue.RTrim() ;
					var iOriginalLength = eChildNode.nodeValue.length ;

					if ( sTrimmed.length == 0 )
					{
						// If the trimmed text node is empty, just remove it.
						
						// Use "eChildNode.parentNode" instead of "node" to avoid IE bug (#81).
						eChildNode.parentNode.removeChild( eChildNode ) ;
						continue ;
					}
					else if ( sTrimmed.length < iOriginalLength )
					{
						// If the trimmed text lenght is less than the original
						// lenght, strip all spaces from the end by splitting
						// the text and removing the resulting useless node.

						eChildNode.splitText( sTrimmed.length ) ;
						// Use "node.lastChild.parentNode" instead of "node" to avoid IE bug (#81).
						node.lastChild.parentNode.removeChild( node.lastChild ) ;
					}
			}
			break ;
		}
	},

	RemoveNode : function( node, excludeChildren )
	{
		if ( excludeChildren )
		{
			// Move all children before the node.
			var eChild ;
			while ( (eChild = node.firstChild) )
				node.parentNode.insertBefore( node.removeChild( eChild ), node ) ;
		}

		return node.parentNode.removeChild( node ) ;
	},

	GetFirstChild : function( node, childNames )
	{
		// If childNames is a string, transform it in a Array.
		if ( typeof ( childNames ) == 'string' )
			childNames = [ childNames ] ;

		var eChild = node.firstChild ;
		while( eChild )
		{
			if ( eChild.nodeType == 1 && eChild.tagName.Equals.apply( eChild.tagName, childNames ) )
				return eChild ;

			eChild = eChild.nextSibling ;
		}

		return null ;
	},

	GetLastChild : function( node, childNames )
	{
		// If childNames is a string, transform it in a Array.
		if ( typeof ( childNames ) == 'string' )
			childNames = [ childNames ] ;

		var eChild = node.lastChild ;
		while( eChild )
		{
			if ( eChild.nodeType == 1 && ( !childNames || eChild.tagName.Equals( childNames ) ) )
				return eChild ;

			eChild = eChild.previousSibling ;
		}

		return null ;
	},

	/*
	 * Gets the previous element (nodeType=1) in the source order. Returns
	 * "null" If no element is found.
	 *		@param {Object} currentNode The node to start searching from.
	 *		@param {Boolean} ignoreSpaceTextOnly Sets how text nodes will be
	 *				handled. If set to "true", only white spaces text nodes
	 *				will be ignored, while non white space text nodes will stop
	 *				the search, returning null. If "false" or ommitted, all
	 *				text nodes are ignored.
	 *		@param {string[]} stopSearchElements An array of element names that
	 *				will cause the search to stop when found, returning null.
	 *				May be ommitted (or null).
	 *		@param {string[]} ignoreElements An array of element names that
	 *				must be ignored during the search.
	 */
	GetPreviousSourceElement : function( currentNode, ignoreSpaceTextOnly, stopSearchElements, ignoreElements )
	{
		if ( !currentNode )
			return null ;

		if ( stopSearchElements && currentNode.nodeType == 1 && currentNode.nodeName.IEquals( stopSearchElements ) )
			return null ;

		if ( currentNode.previousSibling )
			currentNode = currentNode.previousSibling ;
		else
			return this.GetPreviousSourceElement( currentNode.parentNode, ignoreSpaceTextOnly, stopSearchElements, ignoreElements ) ;

		while ( currentNode )
		{
			if ( currentNode.nodeType == 1 )
			{
				if ( stopSearchElements && currentNode.nodeName.IEquals( stopSearchElements ) )
					break ;

				if ( !ignoreElements || !currentNode.nodeName.IEquals( ignoreElements ) )
					return currentNode ;
			}
			else if ( ignoreSpaceTextOnly && currentNode.nodeType == 3 && currentNode.nodeValue.RTrim().length > 0 )
				break ;

			if ( currentNode.lastChild )
				currentNode = currentNode.lastChild ;
			else
				return this.GetPreviousSourceElement( currentNode, ignoreSpaceTextOnly, stopSearchElements, ignoreElements ) ;
		}

		return null ;
	},

	/*
	 * Gets the next element (nodeType=1) in the source order. Returns
	 * "null" If no element is found.
	 *		@param {Object} currentNode The node to start searching from.
	 *		@param {Boolean} ignoreSpaceTextOnly Sets how text nodes will be
	 *				handled. If set to "true", only white spaces text nodes
	 *				will be ignored, while non white space text nodes will stop
	 *				the search, returning null. If "false" or ommitted, all
	 *				text nodes are ignored.
	 *		@param {string[]} stopSearchElements An array of element names that
	 *				will cause the search to stop when found, returning null.
	 *				May be ommitted (or null).
	 *		@param {string[]} ignoreElements An array of element names that
	 *				must be ignored during the search.
	 */
	GetNextSourceElement : function( currentNode, ignoreSpaceTextOnly, stopSearchElements, ignoreElements )
	{
		if ( !currentNode )
			return null ;

		if ( currentNode.nextSibling )
			currentNode = currentNode.nextSibling ;
		else
			return this.GetNextSourceElement( currentNode.parentNode, ignoreSpaceTextOnly, stopSearchElements, ignoreElements ) ;

		while ( currentNode )
		{
			if ( currentNode.nodeType == 1 )
			{
				if ( stopSearchElements && currentNode.nodeName.IEquals( stopSearchElements ) )
					break ;

				if ( !ignoreElements || !currentNode.nodeName.IEquals( ignoreElements ) )
					return currentNode ;
			}
			else if ( ignoreSpaceTextOnly && currentNode.nodeType == 3 && currentNode.nodeValue.RTrim().length > 0 )
				break ;

			if ( currentNode.firstChild )
				currentNode = currentNode.firstChild ;
			else
				return this.GetNextSourceElement( currentNode, ignoreSpaceTextOnly, stopSearchElements, ignoreElements ) ;
		}

		return null ;
	},

	// Inserts a element after a existing one.
	InsertAfterNode : function( existingNode, newNode )
	{
		return existingNode.parentNode.insertBefore( newNode, existingNode.nextSibling ) ;
	},

	GetParents : function( node )
	{
		var parents = new Array() ;

		while ( node )
		{
			parents.splice( 0, 0, node ) ;
			node = node.parentNode ;
		}

		return parents ;
	},

	GetIndexOf : function( node )
	{
		var currentNode = node.parentNode ? node.parentNode.firstChild : null ;
		var currentIndex = -1 ;

		while ( currentNode )
		{
			currentIndex++ ;

			if ( currentNode == node )
				return currentIndex ;

			currentNode = currentNode.nextSibling ;
		}

		return -1 ;
	}
} ;