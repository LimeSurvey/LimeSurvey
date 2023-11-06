// based on TTabs from http://interface.eyecon.ro/
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function(){

    $('.ace:not(.none)').ace({
        'mode' : editorfiletype,
        'toolbarCallback' : createToolbar
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

// Creates a toolbar.
function createToolbar(element, editor)
{
    element.css('background-color', '#F0F0F0');
    element.css('padding', '5px');
    element.css('text-align', 'center');
    $('<button/>').text('Undo (ctrl + Z)').attr('type', 'button').addClass('btn btn-default').appendTo(element).on('click', function()
    {
        editor.commands.exec('undo', editor);
    });
    $('<button/>').text('Redo (ctrl + Y)').attr('type', 'button').addClass('btn btn-default').appendTo(element).on('click', function()
    {
        editor.commands.exec('redo', editor);
    });
    $('<button/>').text('Find (ctrl + F)').attr('type', 'button').addClass('btn btn-default').appendTo(element).on('click', function()
    {
        editor.commands.exec('find', editor);
    });
    $('<button/>').text('Replace (ctrl + H)').attr('type', 'button').addClass('btn btn-default').appendTo(element).on('click', function()
    {
        editor.commands.exec('replace', editor);
    });
    editor.focus();
}
