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
 * FCKXml Class: class to load and manipulate XML files.
 */

FCKXml.prototype =
{
	LoadUrl : function( urlToCall )
	{
		this.Error = false ;
		var oFCKXml = this ;

		var oXmlHttp = FCKTools.CreateXmlObject( 'XmlHttp' ) ;
		oXmlHttp.open( "GET", urlToCall, false ) ;
		oXmlHttp.send( null ) ;

		if ( oXmlHttp.status == 200 || oXmlHttp.status == 304 )
			this.DOMDocument = oXmlHttp.responseXML ;
		else if ( oXmlHttp.status == 0 && oXmlHttp.readyState == 4 )
			this.DOMDocument = oXmlHttp.responseXML ;
		else
			this.DOMDocument = null ;

		if ( this.DOMDocument == null || this.DOMDocument.firstChild == null )
		{
			this.Error = true ;
			if (window.confirm( 'Error loading "' + urlToCall + '"\r\nDo you want to see more info?' ) )
				alert( 'URL requested: "' + urlToCall + '"\r\n' +
							'Server response:\r\nStatus: ' + oXmlHttp.status + '\r\n' +
							'Response text:\r\n' + oXmlHttp.responseText ) ;

		}
	},

	SelectNodes : function( xpath, contextNode )
	{
		if ( this.Error )
			return new Array() ;

		var aNodeArray = new Array();

		var xPathResult = this.DOMDocument.evaluate( xpath, contextNode ? contextNode : this.DOMDocument,
				this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), XPathResult.ORDERED_NODE_ITERATOR_TYPE, null) ;
		if ( xPathResult )
		{
			var oNode = xPathResult.iterateNext() ;
			while( oNode )
			{
				aNodeArray[aNodeArray.length] = oNode ;
				oNode = xPathResult.iterateNext();
			}
		}
		return aNodeArray ;
	},

	SelectSingleNode : function( xpath, contextNode )
	{
		if ( this.Error )
			return null ;

		var xPathResult = this.DOMDocument.evaluate( xpath, contextNode ? contextNode : this.DOMDocument,
				this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), 9, null);

		if ( xPathResult && xPathResult.singleNodeValue )
			return xPathResult.singleNodeValue ;
		else
			return null ;
	}
} ;