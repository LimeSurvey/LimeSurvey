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
 * Gecko specific.
 */

FCKXHtml._GetMainXmlString = function()
{
	// Create the XMLSerializer.
	var oSerializer = new XMLSerializer() ;

	// Return the serialized XML as a string.
	return oSerializer.serializeToString( this.MainNode ) ;
}

FCKXHtml._AppendAttributes = function( xmlNode, htmlNode, node )
{
	var aAttributes = htmlNode.attributes ;

	for ( var n = 0 ; n < aAttributes.length ; n++ )
	{
		var oAttribute = aAttributes[n] ;

		if ( oAttribute.specified )
		{
			var sAttName = oAttribute.nodeName.toLowerCase() ;
			var sAttValue ;

			// Ignore any attribute starting with "_fck".
			if ( sAttName.StartsWith( '_fck' ) )
				continue ;
			// There is a bug in Mozilla that returns '_moz_xxx' attributes as specified.
			else if ( sAttName.indexOf( '_moz' ) == 0 )
				continue ;
			// There are one cases (on Gecko) when the oAttribute.nodeValue must be used:
			//		- for the "class" attribute
			else if ( sAttName == 'class' )
				sAttValue = oAttribute.nodeValue ;
			// XHTML doens't support attribute minimization like "CHECKED". It must be trasformed to cheched="checked".
			else if ( oAttribute.nodeValue === true )
				sAttValue = sAttName ;
			else
				sAttValue = htmlNode.getAttribute( sAttName, 2 ) ;	// We must use getAttribute to get it exactly as it is defined.

			this._AppendAttribute( node, sAttName, sAttValue ) ;
		}
	}
}