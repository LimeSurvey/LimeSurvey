// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
    /*
    // activate codemirror
		$('#changes').not('.none').each(function(index) {
		               var textarea = $(this).get(0) ;
		               var uiOptions = { path : codemirrorpath, searchMode : 'inline', buttons : ['undo','redo','jump','reindent','about'] }
		               var codeMirrorOptions = { mode: editorfiletype }
		               var editor = new CodeMirrorUI(textarea,uiOptions,codeMirrorOptions);
		});
    */
    initializeAce();
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


function initializeAce()
{
    LS.ace = [];
    $('textarea.ace').each(function() {
        // Append a div with the same dimensions.
        var id = 'ace' + LS.ace.length;
        $(this).after('<div style="background-color: grey;" id="' + id + '"></div>');
        $('#' + id).css('width', $(this).css('width'));
        $('#' + id).css('height', $(this).css('height'));
        $(this).hide();
        var editor = ace.edit(id);
        var textarea = $(this);
        LS.ace.push(editor);
        editor.setValue($(this).val());
        editor.getSession().setTabSize(4);
        editor.getSession().setUseSoftTabs(true);
        editor.setHighlightActiveLine(true);
        editor.getSession().setMode("ace/mode/" + $(this).data('filetype'));
        editor.getSession().on('change', function (e) {
            $(textarea).val(editor.getValue());


        });
    })

}