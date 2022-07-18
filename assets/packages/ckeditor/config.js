/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */
CKEDITOR.editorConfig = function (a) {
    
        a.plugins = "a11ychecker,dialogui,dialog,a11yhelp,about,xml,ajax,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,codemirror,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,copyformatting,dialogadvtab,div,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,iframe,image,indent,indentblock,indentlist,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastefromword,pastetext,removeformat,resize,save,scayt,selectall,showblocks,showborders,emoji,sourcearea,specialchar,stylescombo,tab,table,tabletools,undo,videodetector,wsc,wysiwygarea,lineutils,widgetselection,widget,html5video,markdown";
       
        a.filebrowserBrowseUrl = CKEDITOR.basePath + "../../../third_party/kcfinder/browse.php?type\x3dfiles";
        a.filebrowserImageBrowseUrl = CKEDITOR.basePath + "../../../third_party/kcfinder/browse.php?type\x3dimages";
        a.filebrowserFlashBrowseUrl = CKEDITOR.basePath + "../../../third_party/kcfinder/browse.php?type\x3dflash";
        a.filebrowserUploadUrl = CKEDITOR.basePath + "../../../third_party/kcfinder/upload.php?type\x3dfiles";
        a.filebrowserImageUploadUrl = CKEDITOR.basePath + "../../../third_party/kcfinder/upload.php?type\x3dimages";
        a.filebrowserFlashUploadUrl = CKEDITOR.basePath + "../../../third_party/kcfinder/upload.php?type\x3dflash";
        a.removeDialogTabs = "link:upload;image:Upload";
        a.image_prefillDimensions = !1;
        a.image2_prefillDimensions = !1;
        a.allowedContent = !0;
        a.skin = "bootstrapck";
        a.autoParagraph = !1;
        a.basicEntities = !1;
        a.entities = !1;
        a.uiColor = "#f1f1f1";

        "rtl" == $("html").attr("dir") && (a.contentsLangDirection = "rtl");
        a.toolbar_popup = [
            ["Save", "Sourcedialog", "Createlimereplacementfields"],
            ["Cut", "Copy", "Paste", "PasteText", "PasteFromWord"], "Undo Redo - Find Replace - SelectAll RemoveFormat".split(" "),
            "Image Html5video VideoDetector Flash Table HorizontalRule EmojiPanel SpecialChar SpecialChar".split(" "), "/", "Bold Italic Underline Strike - Subscript Superscript".split(" "), 
            "NumberedList BulletedList - Outdent Indent Blockquote CreateDiv".split(" "), ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],
            ["BidiLtr", "BidiRtl"],
            ["Link", "Unlink", "Anchor", "Iframe"], "/", ["Styles", "Format", "Font", "FontSize"],
            ["TextColor", "BGColor"],
            ["ShowBlocks", "Templates"]
        ];
        a.toolbar_inline = [
            ["Maximize", "Sourcedialog", "Createlimereplacementfields", "SwitchToolbar"],
            ["Cut", "Copy", "Paste", "PasteText", "PasteFromWord"], "Undo Redo - Find Replace - SelectAll RemoveFormat".split(" "),
            ["Image", "Html5video", "VideoDetector", "Flash"],
            ["Table", "HorizontalRule", "EmojiPanel", "SpecialChar"],
            ["Bold", "Italic", "Underline", "Strike"],
            ["Subscript", "Superscript"],
            ["NumberedList", "BulletedList"],
            ["Outdent", "Indent", "Blockquote", "CreateDiv"],
            ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],
            ["BidiLtr", "BidiRtl"],
            ["ShowBlocks", "Templates"],
            ["Link", "Unlink"],
            ["Styles", "Format", "Font", "FontSize"],
            ["Anchor", "Iframe"],
            ["TextColor", "BGColor"]
        ];
        a.toolbar_inline2 = [
            ["Maximize", "Sourcedialog", "Createlimereplacementfields", "SwitchToolbar"],
            ["Bold", "Italic", "Underline"],
            ["NumberedList", "BulletedList", "-", "Outdent", "Indent"],
            ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],
            ["Link", "Unlink", "Image"],
        ];
    
        a.toolbar = [
            ["Sourcedialog", "Createlimereplacementfields"],
            ["Cut","Copy", "Paste", "PasteText", "PasteFromWord"], "Undo Redo - Find Replace - SelectAll RemoveFormat".split(" "),
            ["Image", "Html5video","VideoDetector", "Flash"],
            ["Table", "HorizontalRule", "EmojiPanel", "SpecialChar"],
            ["Bold", "Italic", "Underline", "Strike"],
            ["Subscript", "Superscript"],
            ["NumberedList", "BulletedList"],
            ["Outdent", "Indent", "Blockquote", "CreateDiv"],
            ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],
            ["BidiLtr", "BidiRtl"],
            ["ShowBlocks", "Templates"],
            ["Link", "Unlink"],
            ["Styles", "Format", "Font", "FontSize"],
            ["Anchor", "Iframe"],
            ["TextColor", "BGColor"]
        ];
        a.extraPlugins = "limereplacementfields,codemirror,editorplaceholder,lsswitchtoolbars,sourcedialog";
        a.removePlugins = 'sourcearea';
        
};
