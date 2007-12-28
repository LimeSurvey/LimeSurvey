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
 * This is the sample plugin definition file.
 */

// Register the related commands.
FCKCommands.RegisterCommand( 'My_Find'		, new FCKDialogCommand( FCKLang['DlgMyFindTitle']	, FCKLang['DlgMyFindTitle']		, FCKConfig.PluginsPath + 'findreplace/find.html'	, 340, 170 ) ) ;
FCKCommands.RegisterCommand( 'My_Replace'	, new FCKDialogCommand( FCKLang['DlgMyReplaceTitle'], FCKLang['DlgMyReplaceTitle']	, FCKConfig.PluginsPath + 'findreplace/replace.html', 340, 200 ) ) ;

// Create the "Find" toolbar button.
var oFindItem		= new FCKToolbarButton( 'My_Find', FCKLang['DlgMyFindTitle'] ) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'findreplace/find.gif' ;

FCKToolbarItems.RegisterItem( 'My_Find', oFindItem ) ;			// 'My_Find' is the name used in the Toolbar config.

// Create the "Replace" toolbar button.
var oReplaceItem		= new FCKToolbarButton( 'My_Replace', FCKLang['DlgMyReplaceTitle'] ) ;
oReplaceItem.IconPath	= FCKConfig.PluginsPath + 'findreplace/replace.gif' ;

FCKToolbarItems.RegisterItem( 'My_Replace', oReplaceItem ) ;	// 'My_Replace' is the name used in the Toolbar config.