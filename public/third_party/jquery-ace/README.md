jquery-ace
==========

Adapter to use Ace with jQuery.
This jquery plugin allows for easy replacement of textarea with an Ace editor instance (http://ace.ajax.org/)

Simply include ace and jquery, then intialize an editor by calling:

$(selector).ace();

Optionally pass in a configuration object. The default looks as follows:
{
    'mode' : 'html',
    'tabSize' : 4,
    'softTabs' : true,
    'highlightActiveLine' : true,
    'idPostfix' : '__ace',
    'toolbarCallback' : null,
    'wrapperClass' : 'jquery-ace-wrapper'
}

If you supply set toolbarCallback to a function, the function will be called with 2 arguments:
- the div where the toolbar should be created
- the ACE editor instance.

For meaning of most configuration parameters check the ace documentation (http://ace.ajax.org/#nav=howto)

On instantiation the plugin creates 3 divs:
before:

-root
-- textarea

after:
root
- textarea[hidden]
-- div (class = wrapperClass)
--- [div toolbarWrapper]
--- div editorWrapper (id = textarea.id + idPostfix)


An id on the textarea is, at this time, a hard requirement!

-----------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------
Note: This is my first jquery plugin, so check the code carefully, it might contain bugs.
-----------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------

This plugin was created for LimeSurvey, where we use the following basic toolbar:

function createToolbar(element, editor)
{
    element.css('background-color', '#F0F0F0');
    element.css('padding', '5px');
    element.css('text-align', 'center');
    $('<button/>').text('Undo (ctrl + Z)').attr('type', 'button').appendTo(element).on('click', function()
    {
        editor.commands.exec('undo', editor);
    });
    $('<button/>').text('Redo (ctrl + Y)').attr('type', 'button').appendTo(element).on('click', function()
    {
        editor.commands.exec('redo', editor);
    });
    $('<button/>').text('Find (ctrl + F)').attr('type', 'button').appendTo(element).on('click', function()
    {
        editor.commands.exec('find', editor);
    });
    $('<button/>').text('Replace (ctrl + H)').attr('type', 'button').appendTo(element).on('click', function()
    {
        editor.commands.exec('replace', editor);
    });
