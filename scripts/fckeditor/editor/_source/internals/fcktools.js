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

// Constant for the Gecko Bogus Node.
//var GECKO_BOGUS = '<br _moz_editor_bogus_node="TRUE">' ;
var GECKO_BOGUS = '<br type="_moz">' ;

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

FCKTools.GetLastItem = function( list )
{
	if ( list.length > 0 )
		return list[ list.length - 1 ] ;

	return null ;
}
