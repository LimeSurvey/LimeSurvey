/*
 Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.plugins.add("uicolor",{requires:"dialog",lang:"bg,cs,cy,da,de,el,en,eo,et,fa,fi,fr,he,hr,it,mk,nb,nl,no,pl,tr,ug,uk,vi,zh-cn",icons:"uicolor",init:function(a){CKEDITOR.env.ie6Compat||(a.addCommand("uicolor",new CKEDITOR.dialogCommand("uicolor")),a.ui.addButton&&a.ui.addButton("UIColor",{label:a.lang.uicolor.title,command:"uicolor",toolbar:"tools,1"}),CKEDITOR.dialog.add("uicolor",this.path+"dialogs/uicolor.js"),CKEDITOR.scriptLoader.load(CKEDITOR.getUrl("plugins/uicolor/yui/yui.js")),CKEDITOR.document.appendStyleSheet(CKEDITOR.getUrl("plugins/uicolor/yui/assets/yui.css")))}});