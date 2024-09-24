// based on TTabs from http://interface.eyecon.ro/
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready pjax:scriptcomplete', function(){

    $('.ace:not(.none)').ace({
        'mode' : editorfiletype,
        'toolbarCallback' : createToolbar
    });

    $('.jquery-ace-wrapper').addClass('card');

    const changesAce = document.getElementById('changes__ace');
    if (changesAce) {
        changesAce.style.width = null;
    }
    const editorToolbar = document.getElementById('editor-toolbar');
    if (editorToolbar) {
        editorToolbar.style.width = null;
    }

    $('#iphone').click(function(){
        // changed to screen-size of Iphone X
      $('#previewiframe').css("width", "375px");
      $('#previewiframe').css("height", "812px");
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

// Creates a toolbar.
function createToolbar(element, editor)
{
    element.attr('id', 'editor-toolbar');
    element.css('background-color', '#F0F0F0');
    element.css('padding', '5px');
    element.css('text-align', 'center');

    $('<button/>').text(surveyThemeEditorLanguageData.undo).attr('type', 'button').addClass('btn btn-outline-secondary me-1').appendTo(element).on('click', function()
    {
        editor.commands.exec('undo', editor);
    });
    $('<button/>').text(surveyThemeEditorLanguageData.redo).attr('type', 'button').addClass('btn btn-outline-secondary me-1').appendTo(element).on('click', function()
    {
        editor.commands.exec('redo', editor);
    });
    $('<button/>').text(surveyThemeEditorLanguageData.find).attr('type', 'button').addClass('btn btn-outline-secondary me-1').appendTo(element).on('click', function()
    {
        editor.commands.exec('find', editor);
    });
    $('<button/>').text(surveyThemeEditorLanguageData.replace).attr('type', 'button').addClass('btn btn-outline-secondary').appendTo(element).on('click', function()
    {
        editor.commands.exec('replace', editor);
    });
    editor.focus();
}
