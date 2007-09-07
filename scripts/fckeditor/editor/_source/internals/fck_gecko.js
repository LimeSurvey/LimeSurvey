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
 * Creation and initialization of the "FCK" object. This is the main
 * object that represents an editor instance.
 * (Gecko specific implementations)
 */

FCK.Description = "FCKeditor for Gecko Browsers" ;

FCK.InitializeBehaviors = function()
{
	// When calling "SetHTML", the editing area IFRAME gets a fixed height. So we must recaulculate it.
	if ( FCKBrowserInfo.IsGecko )		// Not for Safari/Opera.
		Window_OnResize() ;

	FCKFocusManager.AddWindow( this.EditorWindow ) ;

	this.ExecOnSelectionChange = function()
	{
		FCK.Events.FireEvent( "OnSelectionChange" ) ;
	}

	this.ExecOnSelectionChangeTimer = function()
	{
		if ( FCK.LastOnChangeTimer )
			window.clearTimeout( FCK.LastOnChangeTimer ) ;

		FCK.LastOnChangeTimer = window.setTimeout( FCK.ExecOnSelectionChange, 100 ) ;
	}

	this.EditorDocument.addEventListener( 'mouseup', this.ExecOnSelectionChange, false ) ;

	// On Gecko, firing the "OnSelectionChange" event on every key press started to be too much
	// slow. So, a timer has been implemented to solve performance issues when tipying to quickly.
	this.EditorDocument.addEventListener( 'keyup', this.ExecOnSelectionChangeTimer, false ) ;

	this._DblClickListener = function( e )
	{
		FCK.OnDoubleClick( e.target ) ;
		e.stopPropagation() ;
	}
	this.EditorDocument.addEventListener( 'dblclick', this._DblClickListener, true ) ;

	// Reset the context menu.
	FCK.ContextMenu._InnerContextMenu.SetMouseClickWindow( FCK.EditorWindow ) ;
	FCK.ContextMenu._InnerContextMenu.AttachToElement( FCK.EditorDocument ) ;
}

FCK.MakeEditable = function()
{
	this.EditingArea.MakeEditable() ;
}

// Disable the context menu in the editor (outside the editing area).
function Document_OnContextMenu( e )
{
	if ( !e.target._FCKShowContextMenu )
		e.preventDefault() ;
}
document.oncontextmenu = Document_OnContextMenu ;

// GetNamedCommandState overload for Gecko.
FCK._BaseGetNamedCommandState = FCK.GetNamedCommandState ;
FCK.GetNamedCommandState = function( commandName )
{
	switch ( commandName )
	{
		case 'Unlink' :
			return FCKSelection.HasAncestorNode('A') ? FCK_TRISTATE_OFF : FCK_TRISTATE_DISABLED ;
		default :
			return FCK._BaseGetNamedCommandState( commandName ) ;
	}
}

// Named commands to be handled by this browsers specific implementation.
FCK.RedirectNamedCommands =
{
	Print	: true,
	Paste	: true,
	Cut		: true,
	Copy	: true
} ;

// ExecuteNamedCommand overload for Gecko.
FCK.ExecuteRedirectedNamedCommand = function( commandName, commandParameter )
{
	switch ( commandName )
	{
		case 'Print' :
			FCK.EditorWindow.print() ;
			break ;
		case 'Paste' :
			try			{ if ( FCK.Paste() ) FCK.ExecuteNamedCommand( 'Paste', null, true ) ; }
			catch (e)	{ FCKDialog.OpenDialog( 'FCKDialog_Paste', FCKLang.Paste, 'dialog/fck_paste.html', 400, 330, 'Security' ) ; }
			break ;
		case 'Cut' :
			try			{ FCK.ExecuteNamedCommand( 'Cut', null, true ) ; }
			catch (e)	{ alert(FCKLang.PasteErrorCut) ; }
			break ;
		case 'Copy' :
			try			{ FCK.ExecuteNamedCommand( 'Copy', null, true ) ; }
			catch (e)	{ alert(FCKLang.PasteErrorCopy) ; }
			break ;
		default :
			FCK.ExecuteNamedCommand( commandName, commandParameter ) ;
	}
}

FCK.Paste = function()
{
	if ( FCKConfig.ForcePasteAsPlainText )
	{
		FCK.PasteAsPlainText() ;
		return false ;
	}

	/* For now, the AutoDetectPasteFromWord feature is IE only. */

	return true ;
}

//**
// FCK.InsertHtml: Inserts HTML at the current cursor location. Deletes the
// selected content if any.
FCK.InsertHtml = function( html )
{
	html = FCKConfig.ProtectedSource.Protect( html ) ;
	html = FCK.ProtectEvents( html ) ;
	html = FCK.ProtectUrls( html ) ;
	html = FCK.ProtectTags( html ) ;

	// Firefox can't handle correctly the editing of the STRONG and EM tags.
	// We must replace them with B and I.
		html = html.replace( FCKRegexLib.StrongOpener, '<b$1' ) ;
		html = html.replace( FCKRegexLib.StrongCloser, '<\/b>' ) ;
		html = html.replace( FCKRegexLib.EmOpener, '<i$1' ) ;
		html = html.replace( FCKRegexLib.EmCloser, '<\/i>' ) ;

	// Delete the actual selection.
	var oSel = FCKSelection.Delete() ;

	// Get the first available range.
	var oRange = oSel.getRangeAt(0) ;

	// Create a fragment with the input HTML.
	var oFragment = oRange.createContextualFragment( html ) ;

	// Get the last available node.
	var oLastNode = oFragment.lastChild ;

	// Insert the fragment in the range.
	oRange.insertNode(oFragment) ;

	// Set the cursor after the inserted fragment.
	FCKSelection.SelectNode( oLastNode ) ;
	FCKSelection.Collapse( false ) ;

	this.Focus() ;
}

FCK.InsertElement = function( element )
{
	// Deletes the actual selection.
	var oSel = FCKSelection.Delete() ;

	// Gets the first available range.
	var oRange = oSel.getRangeAt(0) ;

	// Inserts the element in the range.
	oRange.insertNode( element ) ;

	// Set the cursor after the inserted fragment.
	FCKSelection.SelectNode( element ) ;
	FCKSelection.Collapse( false ) ;

	this.Focus() ;
}

FCK.PasteAsPlainText = function()
{
	// TODO: Implement the "Paste as Plain Text" code.

	// If the function is called inmediately Firefox 2 does automatically paste the contents as soon as the new dialog is created
	// so we run it in a Timeout and the paste event can be cancelled
	FCKTools.RunFunction( FCKDialog.OpenDialog, FCKDialog, ['FCKDialog_Paste', FCKLang.PasteAsText, 'dialog/fck_paste.html', 400, 330, 'PlainText'] ) ;

/*
	var sText = FCKTools.HTMLEncode( clipboardData.getData("Text") ) ;
	sText = sText.replace( /\n/g, '<BR>' ) ;
	this.InsertHtml( sText ) ;
*/
}
/*
FCK.PasteFromWord = function()
{
	// TODO: Implement the "Paste as Plain Text" code.

	FCKDialog.OpenDialog( 'FCKDialog_Paste', FCKLang.PasteFromWord, 'dialog/fck_paste.html', 400, 330, 'Word' ) ;

//	FCK.CleanAndPaste( FCK.GetClipboardHTML() ) ;
}
*/
FCK.GetClipboardHTML = function()
{
	return '' ;
}

FCK.CreateLink = function( url )
{
	// Creates the array that will be returned. It contains one or more created links (see #220).
	var aCreatedLinks = new Array() ;

	FCK.ExecuteNamedCommand( 'Unlink' ) ;

	if ( url.length > 0 )
	{
		// Generate a temporary name for the link.
		var sTempUrl = 'javascript:void(0);/*' + ( new Date().getTime() ) + '*/' ;

		// Use the internal "CreateLink" command to create the link.
		FCK.ExecuteNamedCommand( 'CreateLink', sTempUrl ) ;

		// Retrieve the just created links using XPath.
		var oLinksInteractor = this.EditorDocument.evaluate("//a[@href='" + sTempUrl + "']", this.EditorDocument.body, null, XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null) ;

		// Add all links to the returning array.
		for ( var i = 0 ; i < oLinksInteractor.snapshotLength ; i++ )
		{
			var oLink = oLinksInteractor.snapshotItem( i ) ;
			oLink.href = url ;
			aCreatedLinks.push( oLink ) ;
		}
	}

	return aCreatedLinks ;
}
