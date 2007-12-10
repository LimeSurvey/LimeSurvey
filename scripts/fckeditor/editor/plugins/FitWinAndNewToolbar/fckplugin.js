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
 * File Name: fckplugin.js
 * 	Plugin to change Toolbars of the editor "on the fly".
 *
 * File Authors:
 * 		Anton Suprun (kpobococ at gmail dot com)
 */

// Create a command object
function FCKCommand_FitWinAndNewToolbar() {
    // This is it!
    this.SourceView = true;
    this.IsMaximized=false;
    this.Execute = function() {

	if (!this.IsMaximized)
	{
		FCKURLParams.Toolbar = FCK.Config.FitWinAndNewToolbarList[1];
		this.IsMaximized=true;
		FCK.ToolbarSet.Load(FCKURLParams.Toolbar);
		FCK.Commands.GetCommand('FitWindow').Execute();
	}
	else
	{
		FCKURLParams.Toolbar = FCK.Config.FitWinAndNewToolbarList[0];
		this.IsMaximized=false;
		FCK.ToolbarSet.Load(FCKURLParams.Toolbar);
		FCK.Commands.GetCommand('FitWindow').Execute();
	}


    };
    this.GetState = function() {
        if ( FCKConfig.ToolbarLocation != 'In' )
                return FCK_TRISTATE_DISABLED ;
        else
                return ( this.IsMaximized ? FCK_TRISTATE_ON : FCK_TRISTATE_OFF )
    };
}

// Register the related command.
// RegisterCommand takes the following arguments: CommandName, DialogCommand
FCKCommands.RegisterCommand( 'FitWinAndNewToolbar', new FCKCommand_FitWinAndNewToolbar() ) ;

// Create the toolbar button.
// FCKToolbarButton takes the following arguments: CommandName, Button Caption
var oFitWinAndNewToolbarItem = new FCKToolbarButton( 'FitWinAndNewToolbar', FCKLang.FitWinAndNewToolbarBtn ) ;
oFitWinAndNewToolbarItem.IconPath = FCKPlugins.Items['FitWinAndNewToolbar'].Path + 'FitWinAndNewToolbar.gif' ;
FCKToolbarItems.RegisterItem( 'FitWinAndNewToolbar', oFitWinAndNewToolbarItem ) ;

//End code
