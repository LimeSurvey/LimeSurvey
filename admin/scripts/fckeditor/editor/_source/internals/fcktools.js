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
 * Utility functions.
 */

var FCKTools = new Object() ;

FCKTools.CreateBogusBR = function( targetDocument )
{
	var eBR = targetDocument.createElement( 'br' ) ;
//	eBR.setAttribute( '_moz_editor_bogus_node', 'TRUE' ) ;
	eBR.setAttribute( 'type', '_moz' ) ;
	return eBR ;
}

// Returns a reference to the appended style sheet or an array with all the appended references
FCKTools.AppendStyleSheet = function( documentElement, cssFileUrlOrArray )
{
	if ( typeof( cssFileUrlOrArray ) == 'string' )
		return this._AppendStyleSheet( documentElement, cssFileUrlOrArray ) ;
	else
	{
		var aStyleSheeArray = new Array() ;

		for ( var i = 0 ; i < cssFileUrlOrArray.length ; i++ )
			aStyleSheeArray.push(this._AppendStyleSheet( documentElement, cssFileUrlOrArray[i] ) ) ;

		return aStyleSheeArray ;
	}
}

FCKTools.AppendStyleString = function ( documentElement, cssStyles )
{
	this._AppendStyleString( documentElement, cssStyles ) ;
}

FCKTools.GetElementDocument = function ( element )
{
	return element.ownerDocument || element.document ;
}

// Get the window object where the element is placed in.
FCKTools.GetElementWindow = function( element )
{
	return this.GetDocumentWindow( this.GetElementDocument( element ) ) ;
}

FCKTools.GetDocumentWindow = function( document )
{
	// With Safari, there is not way to retrieve the window from the document, so we must fix it.
	if ( FCKBrowserInfo.IsSafari && !document.parentWindow )
		this.FixDocumentParentWindow( window.top ) ;

	return document.parentWindow || document.defaultView ;
}

/*
	This is a Safari specific function that fix the reference to the parent
	window from the document object.
*/
FCKTools.FixDocumentParentWindow = function( targetWindow )
{
	if ( targetWindow.document )
		targetWindow.document.parentWindow = targetWindow ;

	for ( var i = 0 ; i < targetWindow.frames.length ; i++ )
		FCKTools.FixDocumentParentWindow( targetWindow.frames[i] ) ;
}

FCKTools.HTMLEncode = function( text )
{
	if ( !text )
		return '' ;

	text = text.replace( /&/g, '&amp;' ) ;
	text = text.replace( /</g, '&lt;' ) ;
	text = text.replace( />/g, '&gt;' ) ;

	return text ;
}

FCKTools.HTMLDecode = function( text )
{
	if ( !text )
		return '' ;

	text = text.replace( /&gt;/g, '>' ) ;
	text = text.replace( /&lt;/g, '<' ) ;
	text = text.replace( /&amp;/g, '&' ) ;

	return text ;
}

FCKTools._ProcessLineBreaksForPMode = function( oEditor, text, liState, node, strArray )
{
	var closeState = 0 ;
	var blockStartTag = "<p>" ;
	var blockEndTag = "</p>" ;
	var lineBreakTag = "<br />" ;
	if ( liState )
	{
		blockStartTag = "<li>" ;
		blockEndTag = "</li>" ;
		closeState = 1 ;
	}

	// Are we currently inside a <p> tag now?
	// If yes, close it at the next double line break.
	while ( node && node != oEditor.FCK.EditorDocument.body )
	{
		if ( node.tagName.toLowerCase() == 'p' )
		{
			closeState = 1 ;
			break;
		}
		node = node.parentNode ;
	}

	for ( var i = 0 ; i < text.length ; i++ )
	{
		var c = text.charAt( i ) ;
		if ( c == '\r' )
			continue ;

		if ( c != '\n' )
		{
			strArray.push( c ) ;
			continue ;
		}

		// Now we have encountered a line break.
		// Check if the next character is also a line break.
		var n = text.charAt( i + 1 ) ;
		if ( n == '\r' )
		{
			i++ ;
			n = text.charAt( i + 1 ) ;
		}
		if ( n == '\n' )
		{
			i++ ;	// ignore next character - we have already processed it.
			if ( closeState )
				strArray.push( blockEndTag ) ;
			strArray.push( blockStartTag ) ;
			closeState = 1 ;
		}
		else
			strArray.push( lineBreakTag ) ;
	}
}

FCKTools._ProcessLineBreaksForDivMode = function( oEditor, text, liState, node, strArray )
{
	var closeState = 0 ;
	var blockStartTag = "<div>" ;
	var blockEndTag = "</div>" ;
	if ( liState )
	{
		blockStartTag = "<li>" ;
		blockEndTag = "</li>" ;
		closeState = 1 ;
	}

	// Are we currently inside a <div> tag now?
	// If yes, close it at the next double line break.
	while ( node && node != oEditor.FCK.EditorDocument.body )
	{
		if ( node.tagName.toLowerCase() == 'div' )
		{
			closeState = 1 ;
			break ;
		}
		node = node.parentNode ;
	}

	for ( var i = 0 ; i < text.length ; i++ )
	{
		var c = text.charAt( i ) ;
		if ( c == '\r' )
			continue ;

		if ( c != '\n' )
		{
			strArray.push( c ) ;
			continue ;
		}

		if ( closeState )
		{
			if ( strArray[ strArray.length - 1 ] == blockStartTag )
			{
				// A div tag must have some contents inside for it to be visible.
				strArray.push( "&nbsp;" ) ;
			}
			strArray.push( blockEndTag ) ;
		}
		strArray.push( blockStartTag ) ;
		closeState = 1 ;
	}
	if ( closeState )
		strArray.push( blockEndTag ) ;
}

FCKTools._ProcessLineBreaksForBrMode = function( oEditor, text, liState, node, strArray )
{
	var closeState = 0 ;
	var blockStartTag = "<br />" ;
	var blockEndTag = "" ;
	if ( liState )
	{
		blockStartTag = "<li>" ;
		blockEndTag = "</li>" ;
		closeState = 1 ;
	}

	for ( var i = 0 ; i < text.length ; i++ )
	{
		var c = text.charAt( i ) ;
		if ( c == '\r' )
			continue ;

		if ( c != '\n' )
		{
			strArray.push( c ) ;
			continue ;
		}

		if ( closeState && blockEndTag.length )
			strArray.push ( blockEndTag ) ;
		strArray.push( blockStartTag ) ;
		closeState = 1 ;
	}
}

FCKTools.ProcessLineBreaks = function( oEditor, oConfig, text )
{
	var enterMode = oConfig.EnterMode.toLowerCase() ;
	var strArray = [] ;

	// Is the caret or selection inside an <li> tag now?
	var liState = 0 ;
	var range = new oEditor.FCKDomRange( oEditor.FCK.EditorWindow ) ;
	range.MoveToSelection() ;
	var node = range._Range.startContainer ;
	while ( node && node.nodeType != 1 )
		node = node.parentNode ;
	if ( node && node.tagName.toLowerCase() == 'li' )
		liState = 1 ;

	if ( enterMode == 'p' )
		this._ProcessLineBreaksForPMode( oEditor, text, liState, node, strArray ) ;
	else if ( enterMode == 'div' )
		this._ProcessLineBreaksForDivMode( oEditor, text, liState, node, strArray ) ;
	else if ( enterMode == 'br' )
		this._ProcessLineBreaksForBrMode( oEditor, text, liState, node, strArray ) ;
	return strArray.join( "" ) ;
}

/**
 * Adds an option to a SELECT element.
 */
FCKTools.AddSelectOption = function( selectElement, optionText, optionValue )
{
	var oOption = FCKTools.GetElementDocument( selectElement ).createElement( "OPTION" ) ;

	oOption.text	= optionText ;
	oOption.value	= optionValue ;

	selectElement.options.add(oOption) ;

	return oOption ;
}

FCKTools.RunFunction = function( func, thisObject, paramsArray, timerWindow )
{
	if ( func )
		this.SetTimeout( func, 0, thisObject, paramsArray, timerWindow ) ;
}

FCKTools.SetTimeout = function( func, milliseconds, thisObject, paramsArray, timerWindow )
{
	return ( timerWindow || window ).setTimeout(
		function()
		{
			if ( paramsArray )
				func.apply( thisObject, [].concat( paramsArray ) ) ;
			else
				func.apply( thisObject ) ;
		},
		milliseconds ) ;
}

FCKTools.SetInterval = function( func, milliseconds, thisObject, paramsArray, timerWindow )
{
	return ( timerWindow || window ).setInterval(
		function()
		{
			func.apply( thisObject, paramsArray || [] ) ;
		},
		milliseconds ) ;
}

FCKTools.ConvertStyleSizeToHtml = function( size )
{
	return size.EndsWith( '%' ) ? size : parseInt( size, 10 ) ;
}

FCKTools.ConvertHtmlSizeToStyle = function( size )
{
	return size.EndsWith( '%' ) ? size : ( size + 'px' ) ;
}

// START iCM MODIFICATIONS
// Amended to accept a list of one or more ascensor tag names
// Amended to check the element itself before working back up through the parent hierarchy
FCKTools.GetElementAscensor = function( element, ascensorTagNames )
{
//	var e = element.parentNode ;
	var e = element ;
	var lstTags = "," + ascensorTagNames.toUpperCase() + "," ;

	while ( e )
	{
		if ( lstTags.indexOf( "," + e.nodeName.toUpperCase() + "," ) != -1 )
			return e ;

		e = e.parentNode ;
	}
	return null ;
}
// END iCM MODIFICATIONS

FCKTools.CreateEventListener = function( func, params )
{
	var f = function()
	{
		var aAllParams = [] ;

		for ( var i = 0 ; i < arguments.length ; i++ )
			aAllParams.push( arguments[i] ) ;

		func.apply( this, aAllParams.concat( params ) ) ;
	}

	return f ;
}

FCKTools.IsStrictMode = function( document )
{
	// There is no compatMode in Safari, but it seams that it always behave as
	// CSS1Compat, so let's assume it as the default.
	return ( 'CSS1Compat' == ( document.compatMode || 'CSS1Compat' ) ) ;
}

// Transforms a "arguments" object to an array.
FCKTools.ArgumentsToArray = function( args, startIndex, maxLength )
{
	startIndex = startIndex || 0 ;
	maxLength = maxLength || args.length ;

	var argsArray = new Array() ;

	for ( var i = startIndex ; i < startIndex + maxLength && i < args.length ; i++ )
		argsArray.push( args[i] ) ;

	return argsArray ;
}

FCKTools.CloneObject = function( sourceObject )
{
	var fCloneCreator = function() {} ;
	fCloneCreator.prototype = sourceObject ;
	return new fCloneCreator ;
}

// Appends a bogus <br> at the end of the element, if not yet available.
FCKTools.AppendBogusBr = function( element )
{
	if ( !element )
		return ;

	var eLastChild = this.GetLastItem( element.getElementsByTagName('br') ) ;

	if ( !eLastChild || ( eLastChild.getAttribute( 'type', 2 ) != '_moz' && eLastChild.getAttribute( '_moz_dirty' ) == null ) )
	{
		var doc = this.GetElementDocument( element ) ;

		if ( FCKBrowserInfo.IsOpera )
			element.appendChild( doc.createTextNode('') ) ;
		else
			element.appendChild( this.CreateBogusBR( doc ) ) ;
	}
}

FCKTools.GetLastItem = function( list )
{
	if ( list.length > 0 )
		return list[ list.length - 1 ] ;

	return null ;
}

FCKTools.GetDocumentPosition = function( w, node )
{
	var x = 0 ;
	var y = 0 ;
	var curNode = node ;
	var prevNode = null ;
	var curWindow = FCKTools.GetElementWindow( curNode ) ;
	while ( curNode && !( curWindow == w && ( curNode == w.document.body || curNode == w.document.documentElement ) ) )
	{
		x += curNode.offsetLeft - curNode.scrollLeft ;
		y += curNode.offsetTop - curNode.scrollTop ;

		if ( ! FCKBrowserInfo.IsOpera )
		{
			var scrollNode = prevNode ;
			while ( scrollNode && scrollNode != curNode )
			{
				x -= scrollNode.scrollLeft ;
				y -= scrollNode.scrollTop ;
				scrollNode = scrollNode.parentNode ;
			}
		}

		prevNode = curNode ;
		if ( curNode.offsetParent )
			curNode = curNode.offsetParent ;
		else
		{
			if ( curWindow != w )
			{
				curNode = curWindow.frameElement ;
				prevNode = null ;
				if ( curNode )
					curWindow = FCKTools.GetElementWindow( curNode ) ;
			}
			else
				curNode = null ;
		}
	}

	// document.body is a special case when it comes to offsetTop and offsetLeft values.
	// 1. It matters if document.body itself is a positioned element;
	// 2. It matters is when we're in IE and the element has no positioned ancestor.
	// Otherwise the values should be ignored.
	if ( FCKDomTools.GetCurrentElementStyle( w, w.document.body, 'position') != 'static' 
			|| ( FCKBrowserInfo.IsIE && FCKDomTools.GetPositionedAncestor( w, node ) == null ) )
	{
		x += w.document.body.offsetLeft ;
		y += w.document.body.offsetTop ;
	}

	return { "x" : x, "y" : y } ;
}

FCKTools.GetWindowPosition = function( w, node )
{
	var pos = this.GetDocumentPosition( w, node ) ;
	var scroll = FCKTools.GetScrollPosition( w ) ;
	pos.x -= scroll.X ;
	pos.y -= scroll.Y ;
	return pos ;
}

FCKTools.ProtectFormStyles = function( formNode )
{
	if ( !formNode || formNode.nodeType != 1 || formNode.tagName.toLowerCase() != 'form' )
		return [] ;
	var hijackRecord = [] ;
	var hijackNames = [ 'style', 'className' ] ;
	for ( var i = 0 ; i < hijackNames.length ; i++ )
	{
		var name = hijackNames[i] ;
		if ( formNode.elements.namedItem( name ) )
		{
			var hijackNode = formNode.elements.namedItem( name ) ;
			hijackRecord.push( [ hijackNode, hijackNode.nextSibling ] ) ;
			formNode.removeChild( hijackNode ) ;
		}
	}
	return hijackRecord ;
}

FCKTools.RestoreFormStyles = function( formNode, hijackRecord )
{
	if ( !formNode || formNode.nodeType != 1 || formNode.tagName.toLowerCase() != 'form' )
		return ;
	if ( hijackRecord.length > 0 )
	{
		for ( var i = hijackRecord.length - 1 ; i >= 0 ; i-- )
		{
			var node = hijackRecord[i][0] ;
			var sibling = hijackRecord[i][1] ;
			if ( sibling )
				formNode.insertBefore( node, sibling ) ;
			else
				formNode.appendChild( node ) ;
		}
	}
}

// Perform a one-step DFS walk.
FCKTools.GetNextNode = function( node, limitNode )
{
	if ( node.firstChild )
		return node.firstChild ;
	else if ( node.nextSibling )
		return node.nextSibling ;
	else
	{
		var ancestor = node.parentNode ;
		while ( ancestor )
		{
			if ( ancestor == limitNode )
				return null ;
			if ( ancestor.nextSibling )
				return ancestor.nextSibling ;
			else
				ancestor = ancestor.parentNode ;
		}
	}
	return null ;
}

FCKTools.GetNextTextNode = function( textnode, limitNode, checkStop )
{
	node = this.GetNextNode( textnode, limitNode ) ;
	if ( checkStop && node && checkStop( node ) )
		return null ;
	while ( node && node.nodeType != 3 )
	{
		node = this.GetNextNode( node, limitNode ) ;
		if ( checkStop && node && checkStop( node ) )
			return null ;
	}
	return node ;
}

/**
 * Merge all objects passed by argument into a single object.
 */
FCKTools.Merge = function()
{
	var args = arguments ;
	var o = args[0] ;

	for ( var i = 1 ; i < args.length ; i++ )
	{
		var arg = args[i] ;
		for ( var p in arg )
			o[p] = arg[p] ;
	}

	return o ;
}

/**
 * Check if the passed argument is a real Array. It may not working when
 * calling it cross windows.
 */
FCKTools.IsArray = function( it )
{
	return ( it instanceof Array ) ;
}

/**
 * Appends a "length" property to an object, containing the number of
 * properties available on it, excluded the append property itself.
 */
FCKTools.AppendLengthProperty = function( targetObject, propertyName )
{
	var counter = 0 ;

	for ( var n in targetObject )
		counter++ ;

	return targetObject[ propertyName || 'length' ] = counter ;
}

/**
 * Gets the browser parsed version of a css text (style attribute value). On
 * some cases, the browser makes changes to the css text, returning a different
 * value. For example, hexadecimal colors get transformed to rgb().
 */
FCKTools.NormalizeCssText = function( unparsedCssText )
{
	// Injects the style in a temporary span object, so the browser parses it,
	// retrieving its final format.
	var tempSpan = document.createElement( 'span' ) ;
	tempSpan.style.cssText = unparsedCssText ;
	return tempSpan.style.cssText ;
}

/**
 * Utility function to wrap a call to an object's method,
 * so it can be passed for example to an event handler,
 * and then it will be executed with 'this' being the object.
 */
FCKTools.Hitch = function( obj, methodName )
{
  return function() { obj[methodName].apply(obj, arguments); } ;
}
