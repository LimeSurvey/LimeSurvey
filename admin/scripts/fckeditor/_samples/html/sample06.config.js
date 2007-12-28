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
 * Sample custom configuration settings used in the plugin sample page (sample06).
 */

// Set our sample toolbar.
FCKConfig.ToolbarSets['PluginTest'] = [
	['SourceSimple'],
	['My_Find','My_Replace','-','Placeholder'],
	['StyleSimple','FontFormatSimple','FontNameSimple','FontSizeSimple'],
	['Table','-','TableInsertRowAfter','TableDeleteRows','TableInsertColumnAfter','TableDeleteColumns','TableInsertCellAfter','TableDeleteCells','TableMergeCells','TableHorizontalSplitCell','TableCellProp'],
	['Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink'],
	'/',
	['My_BigStyle','-','Smiley','-','About']
] ;

// Change the default plugin path.
FCKConfig.PluginsPath = FCKConfig.BasePath.substr(0, FCKConfig.BasePath.length - 7) + '_samples/_plugins/' ;

// Add our plugin to the plugins list.
//		FCKConfig.Plugins.Add( pluginName, availableLanguages )
//			pluginName: The plugin name. The plugin directory must match this name.
//			availableLanguages: a list of available language files for the plugin (separated by a comma).
FCKConfig.Plugins.Add( 'findreplace', 'en,it,fr' ) ;
FCKConfig.Plugins.Add( 'samples' ) ;

// If you want to use plugins found on other directories, just use the third parameter.
var sOtherPluginPath = FCKConfig.BasePath.substr(0, FCKConfig.BasePath.length - 7) + 'editor/plugins/' ;
FCKConfig.Plugins.Add( 'placeholder', 'en,it,de,fr', sOtherPluginPath ) ;
FCKConfig.Plugins.Add( 'tablecommands', null, sOtherPluginPath ) ;
FCKConfig.Plugins.Add( 'simplecommands', null, sOtherPluginPath ) ;
