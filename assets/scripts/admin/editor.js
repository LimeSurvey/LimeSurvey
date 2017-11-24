

// Namespace
var LS = LS || {  onDocumentReady: {} };

// $Id: labels.js 8649 2010-04-28 21:38:53Z c_schmitz $
$(document).on('ready  pjax:scriptcomplete', function(){

    if (sHTMLEditorMode=='inline') {
        $('textarea.fulledit').ckeditor(function() { /* callback code */ }, {	toolbar : sHTMLEditorMode,
                                                                                language : sEditorLanguage,
                                                                                width: 660 });
    }

});
