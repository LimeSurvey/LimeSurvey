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
 * Defines the FCKXHtml object, responsible for the XHTML operations.
 */

var FCKXHtml = new Object() ;

FCKXHtml.CurrentJobNum = 0 ;

FCKXHtml.GetXHTML = function( node, includeNode, format )
{
	FCKXHtmlEntities.Initialize() ;
	
	// Set the correct entity to use for empty blocks.
	this._NbspEntity = ( FCKConfig.ProcessHTMLEntities? 'nbsp' : '#160' ) ;

	// Save the current IsDirty state. The XHTML processor may change the
	// original HTML, dirtying it.
	var bIsDirty = FCK.IsDirty() ;

	this._CreateNode = FCKConfig.ForceStrongEm ? FCKXHtml_CreateNode_StrongEm : FCKXHtml_CreateNode_Normal ;

	// Special blocks are blocks of content that remain untouched during the
	// process. It is used for SCRIPTs and STYLEs.
	FCKXHtml.SpecialBlocks = new Array() ;

	// Create the XML DOMDocument object.
	this.XML = FCKTools.CreateXmlObject( 'DOMDocument' ) ;

	// Add a root element that holds all child nodes.
	this.MainNode = this.XML.appendChild( this.XML.createElement( 'xhtml' ) ) ;

	FCKXHtml.CurrentJobNum++ ;

	if ( includeNode )
		this._AppendNode( this.MainNode, node ) ;
	else
		this._AppendChildNodes( this.MainNode, node, false ) ;

	// Get the resulting XHTML as a string.
	var sXHTML = this._GetMainXmlString() ;

	this.XML = null ;

	// Strip the "XHTML" root node.
	sXHTML = sXHTML.substr( 7, sXHTML.length - 15 ).Trim() ;

	// Remove the trailing <br> added by Gecko.
	// REMOVE: Maybe the following is not anymore necessary because a similar
	// check is made on _AppendNode
	if ( FCKBrowserInfo.IsGecko )
		sXHTML = sXHTML.replace( /<br\/>$/, '' ) ;

	// Add a space in the tags with no closing tags, like <br/> -> <br />
	sXHTML = sXHTML.replace( FCKRegexLib.SpaceNoClose, ' />');

	if ( FCKConfig.ForceSimpleAmpersand )
		sXHTML = sXHTML.replace( FCKRegexLib.ForceSimpleAmpersand, '&' ) ;

	if ( format )
		sXHTML = FCKCodeFormatter.Format( sXHTML ) ;

	// Now we put back the SpecialBlocks contents.
	for ( var i = 0 ; i < FCKXHtml.SpecialBlocks.length ; i++ )
	{
		var oRegex = new RegExp( '___FCKsi___' + i ) ;
		sXHTML = sXHTML.replace( oRegex, FCKXHtml.SpecialBlocks[i] ) ;
	}

	// Replace entities marker with the ampersand.
	sXHTML = sXHTML.replace( FCKRegexLib.GeckoEntitiesMarker, '&' ) ;

	// Restore the IsDirty state if it was not dirty.
	if ( !bIsDirty )
		FCK.ResetIsDirty() ;

	return sXHTML ;
}

FCKXHtml._AppendAttribute = function( xmlNode, attributeName, attributeValue )
{
	try
	{
		if ( attributeValue == undefined || attributeValue == null )
			attributeValue = '' ;
		else if ( attributeValue.replace )
		{
			if ( FCKConfig.ForceSimpleAmpersand )
				attributeValue = attributeValue.replace( /&/g, '___FCKAmp___' ) ;

			// Entities must be replaced in the attribute values.
			attributeValue = attributeValue.replace( FCKXHtmlEntities.EntitiesRegex, FCKXHtml_GetEntity ) ;
		}

		// Create the attribute.
		var oXmlAtt = this.XML.createAttribute( attributeName ) ;
		oXmlAtt.value = attributeValue ;

		// Set the attribute in the node.
		xmlNode.attributes.setNamedItem( oXmlAtt ) ;
	}
	catch (e)
	{}
}

FCKXHtml._AppendChildNodes = function( xmlNode, htmlNode, isBlockElement )
{
	var oNode = htmlNode.firstChild ;

	while ( oNode )
	{
		this._AppendNode( xmlNode, oNode ) ;
		oNode = oNode.nextSibling ;
	}

	// Trim block elements. This is also needed to avoid Firefox leaving extra
	// BRs at the end of them.
	if ( isBlockElement )
		FCKDomTools.TrimNode( xmlNode, true ) ;

	// If the resulting node is empty.
	if ( xmlNode.childNodes.length == 0 )
	{
		if ( isBlockElement && FCKConfig.FillEmptyBlocks )
		{
			this._AppendEntity( xmlNode, this._NbspEntity ) ;
			return xmlNode ;
		}

		var sNodeName = xmlNode.nodeName ;

		// Some inline elements are required to have something inside (span, strong, etc...).
		if ( FCKListsLib.InlineChildReqElements[ sNodeName ] )
			return null ;

		// We can't use short representation of empty elements that are not marked
		// as empty in th XHTML DTD.
		if ( !FCKListsLib.EmptyElements[ sNodeName ] )
			xmlNode.appendChild( this.XML.createTextNode('') ) ;
	}

	return xmlNode ;
}

FCKXHtml._AppendNode = function( xmlNode, htmlNode )
{
	if ( !htmlNode )
		return false ;

	switch ( htmlNode.nodeType )
	{
		// Element Node.
		case 1 :

			// Here we found an element that is not the real element, but a
			// fake one (like the Flash placeholder image), so we must get the real one.
			if ( htmlNode.getAttribute('_fckfakelement') )
				return FCKXHtml._AppendNode( xmlNode, FCK.GetRealElement( htmlNode ) ) ;

			// Mozilla insert custom nodes in the DOM.
			if ( FCKBrowserInfo.IsGecko && htmlNode.hasAttribute('_moz_editor_bogus_node') )
				return false ;

			// This is for elements that are instrumental to FCKeditor and
			// must be removed from the final HTML.
			if ( htmlNode.getAttribute('_fcktemp') )
				return false ;

			// Get the element name.
			var sNodeName = htmlNode.tagName.toLowerCase()  ;

			if ( FCKBrowserInfo.IsIE )
			{
				// IE doens't include the scope name in the nodeName. So, add the namespace.
				if ( htmlNode.scopeName && htmlNode.scopeName != 'HTML' && htmlNode.scopeName != 'FCK' )
					sNodeName = htmlNode.scopeName.toLowerCase() + ':' + sNodeName ;
			}
			else
			{
				if ( sNodeName.StartsWith( 'fck:' ) )
					sNodeName = sNodeName.Remove( 0,4 ) ;
			}

			// Check if the node name is valid, otherwise ignore this tag.
			// If the nodeName starts with a slash, it is a orphan closing tag.
			// On some strange cases, the nodeName is empty, even if the node exists.
			if ( !FCKRegexLib.ElementName.test( sNodeName ) )
				return false ;

			// Remove the <br> if it is a bogus node.
			if ( sNodeName == 'br' && htmlNode.getAttribute( 'type', 2 ) == '_moz' )
				return false ;

			// The already processed nodes must be marked to avoid then to be duplicated (bad formatted HTML).
			// So here, the "mark" is checked... if the element is Ok, then mark it.
			if ( htmlNode._fckxhtmljob && htmlNode._fckxhtmljob == FCKXHtml.CurrentJobNum )
				return false ;

			var oNode = this._CreateNode( sNodeName ) ;

			// Add all attributes.
			FCKXHtml._AppendAttributes( xmlNode, htmlNode, oNode, sNodeName ) ;

			htmlNode._fckxhtmljob = FCKXHtml.CurrentJobNum ;

			// Tag specific processing.
			var oTagProcessor = FCKXHtml.TagProcessors[ sNodeName ] ;

			if ( oTagProcessor )
				oNode = oTagProcessor( oNode, htmlNode, xmlNode ) ;
			else
				oNode = this._AppendChildNodes( oNode, htmlNode, Boolean( FCKListsLib.NonEmptyBlockElements[ sNodeName ] ) ) ;

			if ( !oNode )
				return false ;

			xmlNode.appendChild( oNode ) ;

			break ;

		// Text Node.
		case 3 :
			return this._AppendTextNode( xmlNode, htmlNode.nodeValue.ReplaceNewLineChars(' ') ) ;

		// Comment
		case 8 :
			// IE catches the <!DOTYPE ... > as a comment, but it has no
			// innerHTML, so we can catch it, and ignore it.
			if ( FCKBrowserInfo.IsIE && !htmlNode.innerHTML )
				break ;

			try { xmlNode.appendChild( this.XML.createComment( htmlNode.nodeValue ) ) ; }
			catch (e) { /* Do nothing... probably this is a wrong format comment. */ }
			break ;

		// Unknown Node type.
		default :
			xmlNode.appendChild( this.XML.createComment( "Element not supported - Type: " + htmlNode.nodeType + " Name: " + htmlNode.nodeName ) ) ;
			break ;
	}
	return true ;
}

function FCKXHtml_CreateNode_StrongEm( nodeName )
{
	switch ( nodeName )
	{
		case 'b' :
			nodeName = 'strong' ;
			break ;
		case 'i' :
			nodeName = 'em' ;
			break ;
	}
	return this.XML.createElement( nodeName ) ;
}

function FCKXHtml_CreateNode_Normal( nodeName )
{
	return this.XML.createElement( nodeName ) ;
}

// Append an item to the SpecialBlocks array and returns the tag to be used.
FCKXHtml._AppendSpecialItem = function( item )
{
	return '___FCKsi___' + FCKXHtml.SpecialBlocks.AddItem( item ) ;
}

FCKXHtml._AppendEntity = function( xmlNode, entity )
{
	xmlNode.appendChild( this.XML.createTextNode( '#?-:' + entity + ';' ) ) ;
}

FCKXHtml._AppendTextNode = function( targetNode, textValue )
{
	var bHadText = textValue.length > 0 ;
	if ( bHadText )
		targetNode.appendChild( this.XML.createTextNode( textValue.replace( FCKXHtmlEntities.EntitiesRegex, FCKXHtml_GetEntity ) ) ) ;
	return bHadText ;
}

// Retrieves a entity (internal format) for a given character.
function FCKXHtml_GetEntity( character )
{
	// We cannot simply place the entities in the text, because the XML parser
	// will translate & to &amp;. So we use a temporary marker which is replaced
	// in the end of the processing.
	var sEntity = FCKXHtmlEntities.Entities[ character ] || ( '#' + character.charCodeAt(0) ) ;
	return '#?-:' + sEntity + ';' ;
}

// Remove part of an attribute from a node according to a regExp
FCKXHtml._RemoveAttribute = function( xmlNode, regX, sAttribute )
{
	var oAtt = xmlNode.attributes.getNamedItem( sAttribute ) ;

	if ( oAtt && regX.test( oAtt.nodeValue ) )
	{
		var sValue = oAtt.nodeValue.replace( regX, '' ) ;

		if ( sValue.length == 0 )
			xmlNode.attributes.removeNamedItem( sAttribute ) ;
		else
			oAtt.nodeValue = sValue ;
	}
}

// An object that hold tag specific operations.
FCKXHtml.TagProcessors =
{
	img : function( node, htmlNode )
	{
		// The "ALT" attribute is required in XHTML.
		if ( ! node.attributes.getNamedItem( 'alt' ) )
			FCKXHtml._AppendAttribute( node, 'alt', '' ) ;

		var sSavedUrl = htmlNode.getAttribute( '_fcksavedurl' ) ;
		if ( sSavedUrl != null )
			FCKXHtml._AppendAttribute( node, 'src', sSavedUrl ) ;

		return node ;
	},

	a : function( node, htmlNode )
	{
		// Firefox may create empty tags when deleting the selection in some special cases (SF-BUG 1556878).
		if ( htmlNode.innerHTML.Trim().length == 0 && !htmlNode.name )
			return false ;

		var sSavedUrl = htmlNode.getAttribute( '_fcksavedurl' ) ;
		if ( sSavedUrl != null )
			FCKXHtml._AppendAttribute( node, 'href', sSavedUrl ) ;


		// Anchors with content has been marked with an additional class, now we must remove it.
		if ( FCKBrowserInfo.IsIE )
		{
			FCKXHtml._RemoveAttribute( node, FCKRegexLib.FCK_Class, 'class' ) ;

			// Buggy IE, doesn't copy the name of changed anchors.
			if ( htmlNode.name )
				FCKXHtml._AppendAttribute( node, 'name', htmlNode.name ) ;
		}

		node = FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

		return node ;
	},

	script : function( node, htmlNode )
	{
		// The "TYPE" attribute is required in XHTML.
		if ( ! node.attributes.getNamedItem( 'type' ) )
			FCKXHtml._AppendAttribute( node, 'type', 'text/javascript' ) ;

		node.appendChild( FCKXHtml.XML.createTextNode( FCKXHtml._AppendSpecialItem( htmlNode.text ) ) ) ;

		return node ;
	},

	style : function( node, htmlNode )
	{
		// The "TYPE" attribute is required in XHTML.
		if ( ! node.attributes.getNamedItem( 'type' ) )
			FCKXHtml._AppendAttribute( node, 'type', 'text/css' ) ;

		node.appendChild( FCKXHtml.XML.createTextNode( FCKXHtml._AppendSpecialItem( htmlNode.innerHTML ) ) ) ;

		return node ;
	},

	title : function( node, htmlNode )
	{
		node.appendChild( FCKXHtml.XML.createTextNode( FCK.EditorDocument.title ) ) ;

		return node ;
	},

	table : function( node, htmlNode )
	{
		// There is a trick to show table borders when border=0. We add to the
		// table class the FCK__ShowTableBorders rule. So now we must remove it.

		if ( FCKBrowserInfo.IsIE )
			FCKXHtml._RemoveAttribute( node, FCKRegexLib.FCK_Class, 'class' ) ;

		node = FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

		return node ;
	},

	// Fix nested <ul> and <ol>.
	ol : function( node, htmlNode, targetNode )
	{
		if ( htmlNode.innerHTML.Trim().length == 0 )
			return false ;

		var ePSibling = targetNode.lastChild ;

		if ( ePSibling && ePSibling.nodeType == 3 )
			ePSibling = ePSibling.previousSibling ;

		if ( ePSibling && ePSibling.nodeName.toUpperCase() == 'LI' )
		{
			htmlNode._fckxhtmljob = null ;
			FCKXHtml._AppendNode( ePSibling, htmlNode ) ;
			return false ;
		}

		node = FCKXHtml._AppendChildNodes( node, htmlNode ) ;

		return node ;
	},

	span : function( node, htmlNode )
	{
		// Firefox may create empty tags when deleting the selection in some special cases (SF-BUG 1084404).
		if ( htmlNode.innerHTML.length == 0 )
			return false ;

		node = FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

		return node ;
	},

	// IE loses contents of iframes, and Gecko does give it back HtmlEncoded
	// Note: Opera does lose the content and doesn't provide it in the innerHTML string
	iframe : function( node, htmlNode )
	{
		var sHtml = htmlNode.innerHTML ;

		// Gecko does give back the encoded html
		if ( FCKBrowserInfo.IsGecko )
			sHtml = FCKTools.HTMLDecode( sHtml );
		
		// Remove the saved urls here as the data won't be processed as nodes
		sHtml = sHtml.replace( /\s_fcksavedurl="[^"]*"/g, '' ) ;

		node.appendChild( FCKXHtml.XML.createTextNode( FCKXHtml._AppendSpecialItem( sHtml ) ) ) ;

		return node ;
	}
} ;

FCKXHtml.TagProcessors.ul = FCKXHtml.TagProcessors.ol ;
