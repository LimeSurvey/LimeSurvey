// $Id: templates.js 9697 2011-01-16 18:37:40Z shnoulle $
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
// activate codemirror
		$('#changes').not('.none').each(function(index) {
		               var textarea = $(this).get(0) ; 
		               var uiOptions = { path : codemirropath, searchMode : 'inline', buttons : ['undo','redo','jump','reindent','about'] }
		               var codeMirrorOptions = { mode: "javascript" }
		               var editor = new CodeMirrorUI(textarea,uiOptions,codeMirrorOptions);
		});
		
    $('#iphone').click(function(){
      $('#previewiframe').css("width", "320px");
      $('#previewiframe').css("height", "396px");
    });
    $('#x640').click(function(){
      $('#previewiframe').css("width", "640px");
      $('#previewiframe').css("height", "480px");
    });
    $('#x800').click(function(){
      $('#previewiframe').css("width", "800px");
      $('#previewiframe').css("height", "600px");
    });
    $('#x1024').click(function(){
      $('#previewiframe').css("width", "1024px");
      $('#previewiframe').css("height", "768px");
    });
    $('#full').click(function(){
      $('#previewiframe').css("width", "95%");
      $('#previewiframe').css("height", "768px");
    });
});
