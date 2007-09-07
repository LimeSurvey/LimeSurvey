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
 * Dialog windows operations. (Gecko specific implementations)
 */

FCKDialog.Show = function( dialogInfo, dialogName, pageUrl, dialogWidth, dialogHeight, parentWindow, resizable )
{
	var iTop  = (FCKConfig.ScreenHeight - dialogHeight) / 2 ;
	var iLeft = (FCKConfig.ScreenWidth  - dialogWidth)  / 2 ;

	var sOption  = "location=no,menubar=no,toolbar=no,dependent=yes,dialog=yes,minimizable=no,modal=yes,alwaysRaised=yes" +
		",resizable="  + ( resizable ? 'yes' : 'no' ) +
		",width="  + dialogWidth +
		",height=" + dialogHeight +
		",top="  + iTop +
		",left=" + iLeft ;

	if ( !parentWindow )
		parentWindow = window ;

	FCKFocusManager.Lock() ;

	var oWindow = parentWindow.open( '', 'FCKeditorDialog_' + dialogName, sOption, true ) ;

	if ( !oWindow )
	{
		alert( FCKLang.DialogBlocked ) ;
		FCKFocusManager.Unlock() ;
		return ;
	}

	oWindow.moveTo( iLeft, iTop ) ;
	oWindow.resizeTo( dialogWidth, dialogHeight ) ;
	oWindow.focus() ;
	oWindow.location.href = pageUrl ;

	oWindow.dialogArguments = dialogInfo ;

	// On some Gecko browsers (probably over slow connections) the
	// "dialogArguments" are not set to the target window so we must
	// put it in the opener window so it can be used by the target one.
	parentWindow.FCKLastDialogInfo = dialogInfo ;

	this.Window = oWindow ;

	// Try/Catch must be used to avoit an error when using a frameset
	// on a different domain:
	// "Permission denied to get property Window.releaseEvents".
	try
	{
		window.top.parent.addEventListener( 'mousedown', this.CheckFocus, true ) ;
		window.top.parent.addEventListener( 'mouseup', this.CheckFocus, true ) ;
		window.top.parent.addEventListener( 'click', this.CheckFocus, true ) ;
		window.top.parent.addEventListener( 'focus', this.CheckFocus, true ) ;
	}
	catch (e)
	{}
}

FCKDialog.CheckFocus = function()
{
	// It is strange, but we have to check the FCKDialog existence to avoid a
	// random error: "FCKDialog is not defined".
	if ( typeof( FCKDialog ) != "object" )
		return false ;

	if ( FCKDialog.Window && !FCKDialog.Window.closed )
		FCKDialog.Window.focus() ;
	else
	{
		// Try/Catch must be used to avoit an error when using a frameset
		// on a different domain:
		// "Permission denied to get property Window.releaseEvents".
		try
		{
			window.top.parent.removeEventListener( 'onmousedown', FCKDialog.CheckFocus, true ) ;
			window.top.parent.removeEventListener( 'mouseup', FCKDialog.CheckFocus, true ) ;
			window.top.parent.removeEventListener( 'click', FCKDialog.CheckFocus, true ) ;
			window.top.parent.removeEventListener( 'onfocus', FCKDialog.CheckFocus, true ) ;
		}
		catch (e)
		{}
	}
	return false ;
}
