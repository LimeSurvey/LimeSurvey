/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */
var editor;

var plgnNames =
    "limereplacementfields,codemirror,editorplaceholder,lsswitchtoolbars,sourcedialog";

var remixIcons = [
    {
        old: "maximize",
        new: "ri-fullscreen-fill",
    },
    {
        old: "sourcedialog",
        new: "ri-code-s-slash-fill",
    },
    {
        old: "createlimereplacementfields",
        new: "ri-braces-fill",
    },
    {
        old: "switchtoolbar",
        new: "ri-grid-fill",
    },
    {
        old: "bold",
        new: "ri-bold",
    },
    {
        old: "italic",
        new: "ri-italic",
    },
    {
        old: "underline",
        new: "ri-underline",
    },
    {
        old: "numberedlist",
        new: "ri-list-ordered",
    },
    {
        old: "bulletedlist",
        new: "ri-list-check",
    },
    {
        old: "outdent",
        new: "ri-indent-increase",
    },
    {
        old: "indent",
        new: "ri-indent-decrease",
    },
    {
        old: "justifyleft",
        new: "ri-align-left",
    },

    {
        old: "justifycenter",
        new: "ri-align-center",
    },
    {
        old: "justifyright",
        new: "ri-align-right",
    },
    {
        old: "justifyblock",
        new: "ri-align-justify",
    },
    {
        old: "link",
        new: "ri-link",
    },
    {
        old: "unlink",
        new: "ri-link-unlink",
    },
    {
        old: "image",
        new: "ri-image-line",
    },
    {
        old: "cut",
        new: "ri-scissors-cut-fill",
    },
    {
        old: "copy",
        new: "ri-file-copy-line",
    },
    {
        old: "paste",
        new: "ri-clipboard-line",
    },
    {
        old: "undo",
        new: "ri-arrow-go-back-fill",
    },
    {
        old: "redo",
        new: "ri-arrow-go-forward-fill",
    },
    {
        old: "find",
        new: "ri-search-line",
    },
    {
        old: "replace",
        new: "ri-arrow-left-right-fill",
    },
    {
        old: "selectall",
        new: "ri-file-text-line",
    },
    {
        old: "removeformat",
        new: "ri-format-clear"
    },
    {
        old: "html5video",
        new: "ri-movie-2-line"
    },
    {
        old: "videodetector",
        new: "ri-video-line"
    },
    {
        old: "table",
        new: "ri-table-line"
    },
    {
        old: "emojipanel",
        new: "ri-emotion-line"
    },
    {
        old: "specialchar",
        new: "ri-omega"
    },
    {
        old: "strike",
        new: "ri-strikethrough"
    },
    {
        old: "subscript",
        new: "ri-subscript"
    },
    {
        old: "superscript",
        new: "ri-superscript"
    },
    {
        old: "blockquote",
        new: "ri-double-quotes-r"
    },
    {
        old: "creatediv",
        new: "ri-code-fill"
    },
    {
        old: "bidiltr",
        new: "ri-text-direction-l"
    },
    {
        old: "bidirtl",
        new: "ri-text-direction-r"
    },
    {
        old: "showblocks",
        new: "ri-aspect-ratio-line"
    },
    {
        old: "anchor",
        new: "ri-flag-fill"
    },
   
];

CKEDITOR.editorConfig = function (a) {
        a.plugins = [
            'a11ychecker',
            'a11yhelp',
            'about',
            'ajax',
            'autocomplete',
            'balloonpanel',
            'basicstyles',
            'bidi',
            'blockquote',
            'button',
            'clipboard',
            'codemirror',
            'colorbutton',
            'colordialog',
            'contextmenu',
            'copyformatting',
            'dialog',
            'dialogadvtab',
            'dialogui',
            'div',
            'editorplaceholder',
            'elementspath',
            'emoji',
            'enterkey',
            'entities',
            'fakeobjects',
            'filebrowser',
            'filetools',
            'find',
            'floatingspace',
            'floatpanel',
            'font',
            'format',
            'horizontalrule',
            'html5video',
            'htmlwriter',
            'iframe',
            'image',
            'indent',
            'indentblock',
            'indentlist',
            'justify',
            'language',
            'lineutils',
            'link',
            'list',
            'listblock',
            'liststyle',
            'magicline',
            'markdown',
            'maximize',
            'menu',
            'menubutton',
            'newpage',
            'notification',
            'pagebreak',
            'panel',
            'panelbutton',
            'pastefromword',
            'pastetext',
            'pastetools',
            'popup',
            'removeformat',
            'resize',
            'richcombo',
            'save',
            'scayt',
            'selectall',
            'showblocks',
            'showborders',
            'sourcearea',
            'sourcedialog',
            'specialchar',
            'stylescombo',
            'tab',
            'table',
            'tabletools',
            'textmatch',
            'textwatcher',
            'toolbar',
            'undo',
            'videodetector',
            'widget',
            'widgetselection',
            'wysiwygarea',
            'xml',
        ];
    
        a.filebrowserBrowseUrl = CKEDITOR.basePath + "../../../vendor/kcfinder/browse.php?type\x3dfiles";
        a.filebrowserImageBrowseUrl = CKEDITOR.basePath + "../../../vendor/kcfinder/browse.php?type\x3dimages";
        a.filebrowserFlashBrowseUrl = CKEDITOR.basePath + "../../../vendor/kcfinder/browse.php?type\x3dflash";
        a.filebrowserUploadUrl = CKEDITOR.basePath + "../../../vendor/kcfinder/upload.php?type\x3dfiles";
        a.filebrowserImageUploadUrl = CKEDITOR.basePath + "../../../vendor/kcfinder/upload.php?type\x3dimages";
        a.filebrowserFlashUploadUrl = CKEDITOR.basePath + "../../../vendor/kcfinder/upload.php?type\x3dflash";
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
            "Image Html5video VideoDetector Flash Table HorizontalRule EmojiPanel SpecialChar".split(" "), "/", "Bold Italic Underline Strike - Subscript Superscript".split(" "), 
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
        a.extraPlugins = "limereplacementfields,lsswitchtoolbars";
        a.removePlugins = 'sourcearea';
        a.iframe_attributes = {
            sandbox: 'allow-scripts allow-same-origin'
        },

        a.versionCheck = false
}

CKEDITOR.on("instanceReady", function (event) {
    var this_instance = document.getElementById(event.editor.id + "_toolbox");

    for (var i = 0; i < remixIcons.length; i++) {
        var this_button = this_instance.querySelector(
            ".cke_button__" + remixIcons[i].old + "_icon"
        );
        if (this_button !== null) {
            this_button.setAttribute(
                "style",
                "background-image:none !important"
            );

            if (typeof this_button !== undefined) {
                this_button.innerHTML =
                    '<i class=" ' +
                    remixIcons[i].new +
                    '" style="cursor: default; font-size:15px; font-weight:500"></i>';
            }
        }
    }
});