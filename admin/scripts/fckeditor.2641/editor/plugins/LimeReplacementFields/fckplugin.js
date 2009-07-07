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
 * Plugin to insert "LimeReplacementFieldss" in the editor.
 */

// Register the related command.
FCKCommands.RegisterCommand( 'LimeReplacementFields', 
	new FCKDialogCommand( 'LimeReplacementFields', 
	FCKLang.LimeReplacementFieldsDlgTitle, 
	FCKPlugins.Items['LimeReplacementFields'].Path + 
		'../../../../../admin.php?sid=' +
		FCK.Config.LimeReplacementFieldsSID +
		'&gid=' + FCK.Config.LimeReplacementFieldsGID +
		'&qid=' + FCK.Config.LimeReplacementFieldsQID +
		'&fieldtype=' + FCK.Config.LimeReplacementFieldsType + 
		'&action=replacementfields' +  
		'&editedaction=' + FCK.Config.LimeReplacementFieldsAction, 
	500, 400 ) ) ;

// Create the "LimeReplacementFields" toolbar button.
var oLimeReplacementFieldsItem = new FCKToolbarButton( 'LimeReplacementFields', FCKLang.LimeReplacementFieldsBtn ) ;
oLimeReplacementFieldsItem.IconPath = FCKPlugins.Items['LimeReplacementFields'].Path + 'LimeReplacementFields.png' ;

FCKToolbarItems.RegisterItem( 'LimeReplacementFields', oLimeReplacementFieldsItem ) ;


// The object used for all LimeReplacementFields operations.
var FCKLimeReplacementFieldss = new Object() ;

// Add a new LimeReplacementFields at the actual selection.
FCKLimeReplacementFieldss.Add = function( name )
{
	var oSpan = FCK.InsertElement( 'SPAN' ) ;
	this.SetupSpan( oSpan, name ) ;
	FCKSelection.SelectNode(oSpan);
}

FCKLimeReplacementFieldss.SetupSpan = function( span, name )
{
	span.innerHTML = '{' + name + '}' ;

	//span.style.backgroundColor = '#ffff00' ;
	//span.style.color = '#000000' ;

	if ( FCKBrowserInfo.IsGecko )
		span.style.cursor = 'default' ;

	span._fckLimeReplacementFields = name ;
	span.contentEditable = false ;

	// To avoid it to be resized.
	span.onresizestart = function()
	{
		FCK.EditorWindow.event.returnValue = false ;
		return false ;
	}
}

// On Gecko we must do this trick so the user select all the SPAN when clicking on it.
FCKLimeReplacementFieldss._SetupClickListener = function()
{
	FCKLimeReplacementFieldss._ClickListener = function( e )
	{
		if ( e.target.tagName == 'SPAN' && e.target._fckLimeReplacementFields )
			FCKSelection.SelectNode( e.target ) ;
	}

	FCK.EditorDocument.addEventListener( 'click', FCKLimeReplacementFieldss._ClickListener, true ) ;
}

// Open the LimeReplacementFields dialog on double click.
FCKLimeReplacementFieldss.OnDoubleClick = function( span )
{
	if ( span.tagName == 'SPAN' && span._fckLimeReplacementFields )
		FCKCommands.GetCommand( 'LimeReplacementFields' ).Execute() ;
}

FCK.RegisterDoubleClickHandler( FCKLimeReplacementFieldss.OnDoubleClick, 'SPAN' ) ;

// Check if a Placholder name is already in use.
FCKLimeReplacementFieldss.Exist = function( name )
{
	var aSpans = FCK.EditorDocument.getElementsByTagName( 'SPAN' ) ;

	for ( var i = 0 ; i < aSpans.length ; i++ )
	{
		if ( aSpans[i]._fckLimeReplacementFields == name )
			return true ;
	}

	return false ;
}

if ( FCKBrowserInfo.IsIE )
{
	FCKLimeReplacementFieldss.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		//var aPlaholders = FCK.EditorDocument.body.innerText.match( /\[\[[^\[\]]+\]\]/g ) ;
		var aPlaholders = FCK.EditorDocument.body.innerText.match( /\{[^\{\}]+\}/g ) ;
		if ( !aPlaholders )
			return ;

		var oRange = FCK.EditorDocument.body.createTextRange() ;

		for ( var i = 0 ; i < aPlaholders.length ; i++ )
		{
			if ( oRange.findText( aPlaholders[i] ) )
			{
				//var sName = aPlaholders[i].match( /\{\s*([^\}]*?)\s*\}/ )[1] ;
				var sName = aPlaholders[i].match( /\{([^\}]+)\}/ )[1] ;
//				oRange.pasteHTML( '<span style="color: #000000; background-color: #ffff00" contenteditable="false" _fckLimeReplacementFields="' + sName + '">' + aPlaholders[i] + '</span>' ) ;
				oRange.pasteHTML( '<span contenteditable="false" _fckLimeReplacementFields="' + sName + '">' + aPlaholders[i] + '</span>' ) ;
			}
		}
	}
}
else
{
	FCKLimeReplacementFieldss.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		var oInteractor = FCK.EditorDocument.createTreeWalker( FCK.EditorDocument.body, NodeFilter.SHOW_TEXT, FCKLimeReplacementFieldss._AcceptNode, true ) ;

		var	aNodes = new Array() ;

		while ( ( oNode = oInteractor.nextNode() ) )
		{
			aNodes[ aNodes.length ] = oNode ;
		}

		for ( var n = 0 ; n < aNodes.length ; n++ )
		{
			var aPieces = aNodes[n].nodeValue.split( /(\{[^\{\}]+\})/g ) ;

			for ( var i = 0 ; i < aPieces.length ; i++ )
			{
				if ( aPieces[i].length > 0 )
				{
					if ( aPieces[i].indexOf( '{' ) == 0 )
					{
//						var sName = aPieces[i].match( /\{\s*([^\}]*?)\s*\}/ )[1] ;
						var sName = aPieces[i].match( /\{([^\}]*)\}/ )[1] ;

						var oSpan = FCK.EditorDocument.createElement( 'span' ) ;
						FCKLimeReplacementFieldss.SetupSpan( oSpan, sName ) ;

						aNodes[n].parentNode.insertBefore( oSpan, aNodes[n] ) ;
					}
					else
						aNodes[n].parentNode.insertBefore( FCK.EditorDocument.createTextNode( aPieces[i] ) , aNodes[n] ) ;
				}
			}

			aNodes[n].parentNode.removeChild( aNodes[n] ) ;
		}

		FCKLimeReplacementFieldss._SetupClickListener() ;
	}

	FCKLimeReplacementFieldss._AcceptNode = function( node )
	{
		if ( /\{[^\{\}]+\}/.test( node.nodeValue ) )
			return NodeFilter.FILTER_ACCEPT ;
		else
			return NodeFilter.FILTER_SKIP ;
	}
}

FCK.Events.AttachEvent( 'OnAfterSetHTML', FCKLimeReplacementFieldss.Redraw ) ;

// We must process the SPAN tags to replace then with the real resulting value of the LimeReplacementFields.
FCKXHtml.TagProcessors['span'] = function( node, htmlNode )
{
	if ( htmlNode._fckLimeReplacementFields )
		node = FCKXHtml.XML.createTextNode( '{' + htmlNode._fckLimeReplacementFields + '}' ) ;
	else
		FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

	return node ;
}
