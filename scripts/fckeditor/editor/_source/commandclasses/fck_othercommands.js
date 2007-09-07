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
 * Definition of other commands that are not available internaly in the
 * browser (see FCKNamedCommand).
 */

// ### General Dialog Box Commands.
var FCKDialogCommand = function( name, title, url, width, height, getStateFunction, getStateParam )
{
	this.Name	= name ;
	this.Title	= title ;
	this.Url	= url ;
	this.Width	= width ;
	this.Height	= height ;

	this.GetStateFunction	= getStateFunction ;
	this.GetStateParam		= getStateParam ;

	this.Resizable = false ;
}

FCKDialogCommand.prototype.Execute = function()
{
	FCKDialog.OpenDialog( 'FCKDialog_' + this.Name , this.Title, this.Url, this.Width, this.Height, null, null, this.Resizable ) ;
}

FCKDialogCommand.prototype.GetState = function()
{
	if ( this.GetStateFunction )
		return this.GetStateFunction( this.GetStateParam ) ;
	else
		return FCK_TRISTATE_OFF ;
}

// Generic Undefined command (usually used when a command is under development).
var FCKUndefinedCommand = function()
{
	this.Name = 'Undefined' ;
}

FCKUndefinedCommand.prototype.Execute = function()
{
	alert( FCKLang.NotImplemented ) ;
}

FCKUndefinedCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### FontName
var FCKFontNameCommand = function()
{
	this.Name = 'FontName' ;
}

FCKFontNameCommand.prototype.Execute = function( fontName )
{
	if (fontName == null || fontName == "")
	{
		// TODO: Remove font name attribute.
	}
	else
		FCK.ExecuteNamedCommand( 'FontName', fontName ) ;
}

FCKFontNameCommand.prototype.GetState = function()
{
	return FCK.GetNamedCommandValue( 'FontName' ) ;
}

// ### FontSize
var FCKFontSizeCommand = function()
{
	this.Name = 'FontSize' ;
}

FCKFontSizeCommand.prototype.Execute = function( fontSize )
{
	if ( typeof( fontSize ) == 'string' ) fontSize = parseInt(fontSize, 10) ;

	if ( fontSize == null || fontSize == '' )
	{
		// TODO: Remove font size attribute (Now it works with size 3. Will it work forever?)
		FCK.ExecuteNamedCommand( 'FontSize', 3 ) ;
	}
	else
		FCK.ExecuteNamedCommand( 'FontSize', fontSize ) ;
}

FCKFontSizeCommand.prototype.GetState = function()
{
	return FCK.GetNamedCommandValue( 'FontSize' ) ;
}

// ### FormatBlock
var FCKFormatBlockCommand = function()
{
	this.Name = 'FormatBlock' ;
}

FCKFormatBlockCommand.prototype.Execute = function( formatName )
{
	if ( formatName == null || formatName == '' )
		FCK.ExecuteNamedCommand( 'FormatBlock', '<P>' ) ;
	else if ( formatName == 'div' && FCKBrowserInfo.IsGecko )
		FCK.ExecuteNamedCommand( 'FormatBlock', 'div' ) ;
	else
		FCK.ExecuteNamedCommand( 'FormatBlock', '<' + formatName + '>' ) ;
}

FCKFormatBlockCommand.prototype.GetState = function()
{
	return FCK.GetNamedCommandValue( 'FormatBlock' ) ;
}

// ### Preview
var FCKPreviewCommand = function()
{
	this.Name = 'Preview' ;
}

FCKPreviewCommand.prototype.Execute = function()
{
     FCK.Preview() ;
}

FCKPreviewCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### Save
var FCKSaveCommand = function()
{
	this.Name = 'Save' ;
}

FCKSaveCommand.prototype.Execute = function()
{
	// Get the linked field form.
	var oForm = FCK.GetParentForm() ;

	if ( typeof( oForm.onsubmit ) == 'function' )
	{
		var bRet = oForm.onsubmit() ;
		if ( bRet != null && bRet === false )
			return ;
	}

	// Submit the form.
	// If there's a button named "submit" then the form.submit() function is masked and
	// can't be called in Mozilla, so we call the click() method of that button.
	if ( typeof( oForm.submit ) == 'function' )
		oForm.submit() ;
	else
		oForm.submit.click() ;
}

FCKSaveCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### NewPage
var FCKNewPageCommand = function()
{
	this.Name = 'NewPage' ;
}

FCKNewPageCommand.prototype.Execute = function()
{
	FCKUndo.SaveUndoStep() ;
	FCK.SetHTML( '' ) ;
	FCKUndo.Typing = true ;
}

FCKNewPageCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### Source button
var FCKSourceCommand = function()
{
	this.Name = 'Source' ;
}

FCKSourceCommand.prototype.Execute = function()
{
	if ( FCKConfig.SourcePopup )	// Until v2.2, it was mandatory for FCKBrowserInfo.IsGecko.
	{
		var iWidth	= FCKConfig.ScreenWidth * 0.65 ;
		var iHeight	= FCKConfig.ScreenHeight * 0.65 ;
		FCKDialog.OpenDialog( 'FCKDialog_Source', FCKLang.Source, 'dialog/fck_source.html', iWidth, iHeight, null, null, true ) ;
	}
	else
	    FCK.SwitchEditMode() ;
}

FCKSourceCommand.prototype.GetState = function()
{
	return ( FCK.EditMode == FCK_EDITMODE_WYSIWYG ? FCK_TRISTATE_OFF : FCK_TRISTATE_ON ) ;
}

// ### Undo
var FCKUndoCommand = function()
{
	this.Name = 'Undo' ;
}

FCKUndoCommand.prototype.Execute = function()
{
	if ( FCKBrowserInfo.IsIE )
		FCKUndo.Undo() ;
	else
		FCK.ExecuteNamedCommand( 'Undo' ) ;
}

FCKUndoCommand.prototype.GetState = function()
{
	if ( FCKBrowserInfo.IsIE )
		return ( FCKUndo.CheckUndoState() ? FCK_TRISTATE_OFF : FCK_TRISTATE_DISABLED ) ;
	else
		return FCK.GetNamedCommandState( 'Undo' ) ;
}

// ### Redo
var FCKRedoCommand = function()
{
	this.Name = 'Redo' ;
}

FCKRedoCommand.prototype.Execute = function()
{
	if ( FCKBrowserInfo.IsIE )
		FCKUndo.Redo() ;
	else
		FCK.ExecuteNamedCommand( 'Redo' ) ;
}

FCKRedoCommand.prototype.GetState = function()
{
	if ( FCKBrowserInfo.IsIE )
		return ( FCKUndo.CheckRedoState() ? FCK_TRISTATE_OFF : FCK_TRISTATE_DISABLED ) ;
	else
		return FCK.GetNamedCommandState( 'Redo' ) ;
}

// ### Page Break
var FCKPageBreakCommand = function()
{
	this.Name = 'PageBreak' ;
}

FCKPageBreakCommand.prototype.Execute = function()
{
//	var e = FCK.EditorDocument.createElement( 'CENTER' ) ;
//	e.style.pageBreakAfter = 'always' ;

	// Tidy was removing the empty CENTER tags, so the following solution has
	// been found. It also validates correctly as XHTML 1.0 Strict.
	var e = FCK.EditorDocument.createElement( 'DIV' ) ;
	e.style.pageBreakAfter = 'always' ;
	e.innerHTML = '<span style="DISPLAY:none">&nbsp;</span>' ;

	var oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'FCK__PageBreak', e ) ;
	oFakeImage	= FCK.InsertElement( oFakeImage ) ;
}

FCKPageBreakCommand.prototype.GetState = function()
{
	return 0 ; // FCK_TRISTATE_OFF
}

// FCKUnlinkCommand - by Johnny Egeland (johnny@coretrek.com)
var FCKUnlinkCommand = function()
{
	this.Name = 'Unlink' ;
}

FCKUnlinkCommand.prototype.Execute = function()
{
	if ( FCKBrowserInfo.IsGecko )
	{
		var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;
		// The unlink command can generate a span in Firefox, so let's do it our way. See #430
		if ( oLink )
			FCKTools.RemoveOuterTags( oLink ) ;

		return ;
	}
	
	FCK.ExecuteNamedCommand( this.Name ) ;
}

FCKUnlinkCommand.prototype.GetState = function()
{
	var state = FCK.GetNamedCommandState( this.Name ) ;

	// Check that it isn't an anchor
	if ( state == FCK_TRISTATE_OFF && FCK.EditMode == FCK_EDITMODE_WYSIWYG )
	{
		var oLink = FCKSelection.MoveToAncestorNode( 'A' ) ;
		var bIsAnchor = ( oLink && oLink.name.length > 0 && oLink.href.length == 0 ) ;
		if ( bIsAnchor )
			state = FCK_TRISTATE_DISABLED ;
	}

	return state ;
}

// FCKSelectAllCommand
var FCKSelectAllCommand = function()
{
	this.Name = 'SelectAll' ;
}

FCKSelectAllCommand.prototype.Execute = function()
{
	if ( FCK.EditMode == FCK_EDITMODE_WYSIWYG )
	{
		FCK.ExecuteNamedCommand( 'SelectAll' ) ;
	}
	else
	{
		// Select the contents of the textarea
		var textarea = FCK.EditingArea.Textarea ;
		if ( FCKBrowserInfo.IsIE )
		{
			textarea.createTextRange().execCommand( 'SelectAll' ) ;
		}
		else
		{
			textarea.selectionStart = 0;
			textarea.selectionEnd = textarea.value.length ;
		}
		textarea.focus() ;
	}
}

FCKSelectAllCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// FCKPasteCommand
var FCKPasteCommand = function()
{
	this.Name = 'Paste' ;
}

FCKPasteCommand.prototype =
{
	Execute : function()
	{
		if ( FCKBrowserInfo.IsIE )
			FCK.Paste() ;
		else
			FCK.ExecuteNamedCommand( 'Paste' ) ;
	},

	GetState : function()
	{
		return FCK.GetNamedCommandState( 'Paste' ) ;
	}
} ;
