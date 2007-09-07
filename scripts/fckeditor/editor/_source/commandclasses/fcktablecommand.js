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
 * FCKPastePlainTextCommand Class: represents the
 * "Paste as Plain Text" command.
 */

var FCKTableCommand = function( command )
{
	this.Name = command ;
}

FCKTableCommand.prototype.Execute = function()
{
	FCKUndo.SaveUndoStep() ;

	switch ( this.Name )
	{
		case 'TableInsertRow' :
			FCKTableHandler.InsertRow() ;
			break ;
		case 'TableDeleteRows' :
			FCKTableHandler.DeleteRows() ;
			break ;
		case 'TableInsertColumn' :
			FCKTableHandler.InsertColumn() ;
			break ;
		case 'TableDeleteColumns' :
			FCKTableHandler.DeleteColumns() ;
			break ;
		case 'TableInsertCell' :
			FCKTableHandler.InsertCell() ;
			break ;
		case 'TableDeleteCells' :
			FCKTableHandler.DeleteCells() ;
			break ;
		case 'TableMergeCells' :
			FCKTableHandler.MergeCells() ;
			break ;
		case 'TableSplitCell' :
			FCKTableHandler.SplitCell() ;
			break ;
		case 'TableDelete' :
			FCKTableHandler.DeleteTable() ;
			break ;
		default :
			alert( FCKLang.UnknownCommand.replace( /%1/g, this.Name ) ) ;
	}
}

FCKTableCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}