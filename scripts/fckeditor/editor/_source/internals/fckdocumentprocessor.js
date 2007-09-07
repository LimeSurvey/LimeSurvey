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
 * Advanced document processors.
 */

var FCKDocumentProcessor = new Object() ;
FCKDocumentProcessor._Items = new Array() ;

FCKDocumentProcessor.AppendNew = function()
{
	var oNewItem = new Object() ;
	this._Items.AddItem( oNewItem ) ;
	return oNewItem ;
}

FCKDocumentProcessor.Process = function( document )
{
	var oProcessor, i = 0 ;
	while( ( oProcessor = this._Items[i++] ) )
		oProcessor.ProcessDocument( document ) ;
}

var FCKDocumentProcessor_CreateFakeImage = function( fakeClass, realElement )
{
	var oImg = FCK.EditorDocument.createElement( 'IMG' ) ;
	oImg.className = fakeClass ;
	oImg.src = FCKConfig.FullBasePath + 'images/spacer.gif' ;
	oImg.setAttribute( '_fckfakelement', 'true', 0 ) ;
	oImg.setAttribute( '_fckrealelement', FCKTempBin.AddElement( realElement ), 0 ) ;
	return oImg ;
}

// Link Anchors
if ( FCKBrowserInfo.IsIE || FCKBrowserInfo.IsOpera )
{
	var FCKAnchorsProcessor = FCKDocumentProcessor.AppendNew() ;
	FCKAnchorsProcessor.ProcessDocument = function( document )
	{
		var aLinks = document.getElementsByTagName( 'A' ) ;

		var oLink ;
		var i = aLinks.length - 1 ;
		while ( i >= 0 && ( oLink = aLinks[i--] ) )
		{
			// If it is anchor. Doesn't matter if it's also a link (even better: we show that it's both a link and an anchor)
			if ( oLink.name.length > 0 )
			{
				//if the anchor has some content then we just add a temporary class
				if ( oLink.innerHTML !== '' )
				{
					if ( FCKBrowserInfo.IsIE )
						oLink.className += ' FCK__AnchorC' ;
				}
				else
				{
					var oImg = FCKDocumentProcessor_CreateFakeImage( 'FCK__Anchor', oLink.cloneNode(true) ) ;
					oImg.setAttribute( '_fckanchor', 'true', 0 ) ;

					oLink.parentNode.insertBefore( oImg, oLink ) ;
					oLink.parentNode.removeChild( oLink ) ;
				}
			}
		}
	}
}

// Page Breaks
var FCKPageBreaksProcessor = FCKDocumentProcessor.AppendNew() ;
FCKPageBreaksProcessor.ProcessDocument = function( document )
{
	var aDIVs = document.getElementsByTagName( 'DIV' ) ;

	var eDIV ;
	var i = aDIVs.length - 1 ;
	while ( i >= 0 && ( eDIV = aDIVs[i--] ) )
	{
		if ( eDIV.style.pageBreakAfter == 'always' && eDIV.childNodes.length == 1 && eDIV.childNodes[0].style && eDIV.childNodes[0].style.display == 'none' )
		{
			var oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'FCK__PageBreak', eDIV.cloneNode(true) ) ;

			eDIV.parentNode.insertBefore( oFakeImage, eDIV ) ;
			eDIV.parentNode.removeChild( eDIV ) ;
		}
	}
/*
	var aCenters = document.getElementsByTagName( 'CENTER' ) ;

	var oCenter ;
	var i = aCenters.length - 1 ;
	while ( i >= 0 && ( oCenter = aCenters[i--] ) )
	{
		if ( oCenter.style.pageBreakAfter == 'always' && oCenter.innerHTML.Trim().length == 0 )
		{
			var oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'FCK__PageBreak', oCenter.cloneNode(true) ) ;

			oCenter.parentNode.insertBefore( oFakeImage, oCenter ) ;
			oCenter.parentNode.removeChild( oCenter ) ;
		}
	}
*/
}

// Flash Embeds.
var FCKFlashProcessor = FCKDocumentProcessor.AppendNew() ;
FCKFlashProcessor.ProcessDocument = function( document )
{
	/*
	Sample code:
	This is some <embed src="/UserFiles/Flash/Yellow_Runners.swf"></embed><strong>sample text</strong>. You are&nbsp;<a name="fred"></a> using <a href="http://www.fckeditor.net/">FCKeditor</a>.
	*/

	var aEmbeds = document.getElementsByTagName( 'EMBED' ) ;

	var oEmbed ;
	var i = aEmbeds.length - 1 ;
	while ( i >= 0 && ( oEmbed = aEmbeds[i--] ) )
	{
		// IE doesn't return the type attribute with oEmbed.type or oEmbed.getAttribute("type") 
		// But it turns out that after accessing it then it doesn't gets copied later
		var oType = oEmbed.attributes[ 'type' ] ;

		// Check the extension and the type. Now it should be enough with just the type
		// Opera doesn't return oEmbed.src so oEmbed.src.EndsWith will fail
		if ( (oEmbed.src && oEmbed.src.EndsWith( '.swf', true )) || ( oType && oType.nodeValue == 'application/x-shockwave-flash' ) )
		{
			var oCloned = oEmbed.cloneNode( true ) ;

			// On IE, some properties are not getting clonned properly, so we
			// must fix it. Thanks to Alfonso Martinez.
			if ( FCKBrowserInfo.IsIE )
			{
				var aAttributes = [ 'scale', 'play', 'loop', 'menu', 'wmode', 'quality' ] ;
				for ( var iAtt = 0 ; iAtt < aAttributes.length ; iAtt++ )
				{
					var oAtt = oEmbed.getAttribute( aAttributes[iAtt] ) ;
					if ( oAtt ) oCloned.setAttribute( aAttributes[iAtt], oAtt ) ;
				}
				// It magically gets lost after reading it in oType
				oCloned.setAttribute( 'type', oType.nodeValue ) ;
			}

			var oImg = FCKDocumentProcessor_CreateFakeImage( 'FCK__Flash', oCloned ) ;
			oImg.setAttribute( '_fckflash', 'true', 0 ) ;

			FCKFlashProcessor.RefreshView( oImg, oEmbed ) ;

			oEmbed.parentNode.insertBefore( oImg, oEmbed ) ;
			oEmbed.parentNode.removeChild( oEmbed ) ;

//			oEmbed.setAttribute( '_fcktemp', 'true', 0) ;
//			oEmbed.style.display = 'none' ;
//			oEmbed.hidden = true ;
		}
	}
}

FCKFlashProcessor.RefreshView = function( placeHolderImage, originalEmbed )
{
	if ( originalEmbed.getAttribute( 'width' ) > 0 )
		placeHolderImage.style.width = FCKTools.ConvertHtmlSizeToStyle( originalEmbed.getAttribute( 'width' ) ) ;

	if ( originalEmbed.getAttribute( 'height' ) > 0 )
		placeHolderImage.style.height = FCKTools.ConvertHtmlSizeToStyle( originalEmbed.getAttribute( 'height' ) ) ;
}

FCK.GetRealElement = function( fakeElement )
{
	var e = FCKTempBin.Elements[ fakeElement.getAttribute('_fckrealelement') ] ;

	if ( fakeElement.getAttribute('_fckflash') )
	{
		if ( fakeElement.style.width.length > 0 )
				e.width = FCKTools.ConvertStyleSizeToHtml( fakeElement.style.width ) ;

		if ( fakeElement.style.height.length > 0 )
				e.height = FCKTools.ConvertStyleSizeToHtml( fakeElement.style.height ) ;
	}

	return e ;
}

// HR Processor.
// This is a IE only (tricky) thing. We protect all HR tags before loading them
// (see FCK.ProtectTags). Here we put the HRs back.
if ( FCKBrowserInfo.IsIE )
{
	FCKDocumentProcessor.AppendNew().ProcessDocument = function( document )
	{
		var aHRs = document.getElementsByTagName( 'HR' ) ;

		var eHR ;
		var i = aHRs.length - 1 ;
		while ( i >= 0 && ( eHR = aHRs[i--] ) )
		{
			// Create the replacement HR.
			var newHR = document.createElement( 'hr' ) ;
			newHR.mergeAttributes( eHR, true ) ;
			
			// We must insert the new one after it. insertBefore will not work in all cases.
			FCKDomTools.InsertAfterNode( eHR, newHR ) ;

			eHR.parentNode.removeChild( eHR ) ;
		}
	}
}

// INPUT:hidden Processor.
FCKDocumentProcessor.AppendNew().ProcessDocument = function( document )
{
	var aInputs = document.getElementsByTagName( 'INPUT' ) ;

	var oInput ;
	var i = aInputs.length - 1 ;
	while ( i >= 0 && ( oInput = aInputs[i--] ) )
	{
		if ( oInput.type == 'hidden' )
		{
			var oImg = FCKDocumentProcessor_CreateFakeImage( 'FCK__InputHidden', oInput.cloneNode(true) ) ;
			oImg.setAttribute( '_fckinputhidden', 'true', 0 ) ;

			oInput.parentNode.insertBefore( oImg, oInput ) ;
			oInput.parentNode.removeChild( oInput ) ;
		}
	}
}