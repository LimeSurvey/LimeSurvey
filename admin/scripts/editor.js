// $Id: labels.js 8649 2010-04-28 21:38:53Z c_schmitz $

$(document).ready(function(){

    $('textarea.fulledit').ckeditor(function() { /* callback code */ }, {	toolbar : sHTMLEditorMode,
                                                                            customConfig : 'limesurvey-config.js' });
});
