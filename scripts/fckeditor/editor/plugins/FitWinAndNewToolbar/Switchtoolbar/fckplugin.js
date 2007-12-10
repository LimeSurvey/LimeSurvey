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
function FCKCommand_Switchtoolbar() {    // This is it!
    this.SourceView = true;    this.Execute = function() {        var oldToolbar = FCKURLParams.Toolbar;
        // Get the current toolbar cycle index
        var idx = false;
        for (var i in FCK.Config.SwitchtoolbarCycle) {            if (FCK.Config.SwitchtoolbarCycle[i] == oldToolbar) {                idx = i;            }        }
        if (idx) {            idx++;        } else {            idx = 0;        }
        if (typeof(FCK.Config.SwitchtoolbarCycle[idx]) != 'undefined') {            var newToolbar = FCK.Config.SwitchtoolbarCycle[idx];        } else if (typeof(FCK.Config.SwitchtoolbarCycle[0]) != 'undefined') {            var newToolbar = FCK.Config.SwitchtoolbarCycle[0];        } else {            var newToolbar = 'Default';        }
        if (oldToolbar != newToolbar) {            FCKURLParams.Toolbar = newToolbar;
            FCK.ToolbarSet.Load(FCKURLParams.Toolbar);
        }    };
    this.GetState = function() {return 0;};}

// Register the related command.
// RegisterCommand takes the following arguments: CommandName, DialogCommand
FCKCommands.RegisterCommand( 'Switchtoolbar', new FCKCommand_Switchtoolbar() ) ;

// Create the toolbar button.
// FCKToolbarButton takes the following arguments: CommandName, Button Caption
var oSwitchtoolbarItem = new FCKToolbarButton( 'Switchtoolbar', FCKLang.SwitchtoolbarBtn ) ;
oSwitchtoolbarItem.IconPath = FCKPlugins.Items['Switchtoolbar'].Path + 'Switchtoolbar.gif' ;
FCKToolbarItems.RegisterItem( 'Switchtoolbar', oSwitchtoolbarItem ) ;

//End code