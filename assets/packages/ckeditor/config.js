/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	
	// %REMOVE_START%
	// The configuration options below are needed when running CKEditor from source files.
	config.skin = 'bootstrapck';
	config.plugins = 'a11ychecker,dialogui,dialog,a11yhelp,about,xml,ajax,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,codemirror,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,copyformatting,dialogadvtab,div,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,iframe,image,indent,indentblock,indentlist,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastefromword,pastetext,removeformat,resize,save,scayt,selectall,showblocks,showborders,smiley,sourcearea,sourcedialog,specialchar,stylescombo,tab,table,tabletools,undo,videodetector,wsc,wysiwygarea,lineutils,widgetselection,widget,html5video,markdown';
    config.allowedContent = true;
    // %REMOVE_END%

	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	//config.uiColor = '#EEF6EF';
};
