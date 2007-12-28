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
 * Dialog windows operations.
 */

var FCKDialog = new Object() ;

// This method opens a dialog window using the standard dialog template.
FCKDialog.OpenDialog = function( dialogName, dialogTitle, dialogPage, width, height, customValue, parentWindow, resizable )
{
	// Setup the dialog info.
	var oDialogInfo = new Object() ;
	oDialogInfo.Title = dialogTitle ;
	oDialogInfo.Page = dialogPage ;
	oDialogInfo.Editor = window ;
	oDialogInfo.CustomValue = customValue ;		// Optional

	var sUrl = FCKConfig.BasePath + 'fckdialog.html' ;
	this.Show( oDialogInfo, dialogName, sUrl, width, height, parentWindow, resizable ) ;
}
