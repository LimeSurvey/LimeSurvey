

// Namespace
var LS = LS || {  onDocumentReady: {} };

// $Id: labels.js 8649 2010-04-28 21:38:53Z c_schmitz $
LS.onDocumentReady.loadCKEditorFields = function(){

    if (sHTMLEditorMode=='inline') {
        $('textarea.fulledit').ckeditor(function() { /* callback code */ }, {	toolbar : sHTMLEditorMode,
                                                                                language : sEditorLanguage,
                                                                                width: 660,
                                                                                customConfig : '/scripts/admin/ckeditor-config.js' });
    }

}
$(document).ready(LS.onDocumentReady.loadCKEditorFields);
$(document).on('pjax:end', LS.onDocumentReady.loadCKEditorFields);
