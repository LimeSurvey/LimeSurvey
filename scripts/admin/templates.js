$(document).ready(function(){

    $('.ace').each(function(i, elem) {
        $(elem).ace({
            'mode' : $(elem).attr('data-type') || 'html',
            'toolbarCallback' : createToolbar
        });
    });

    $('a.resize').click(function(e) {
        e.preventDefault();
        var $elem = $(this);
        $('#preview').animate({
            "width": $elem.attr('data-width'),
            "height": $elem.attr('data-height')
        });
    });
});

// Creates a toolbar.
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



}

$('#save').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var $editor = $('#editor');
    $('#yw0').elfinder('instance').request({
        options: {type: 'post'},
        data: {
            cmd: 'put',
            target: $editor.attr('data-hash'),
            content: $editor.val()
        },
        notify: {type: 'save', cnt: 1},
        syncOnFail: true
    }).done(function(data) {
        $.notify({
            // options
            message: 'File saved'
        }, {
            // settings
            type: 'success'
        });
    });
});


function loadFile(file, elFinder) {
    var $editor = $('#editor');
    var session = $editor.ace('get').session;
    if ($.inArray(file.mime, ["text/html", "text/css", "text/plain"]) > -1) {
        $editor.attr('data-hash', file.hash);
        elFinder.request({
            data: {cmd: 'get', target: file.hash, current: file.phash, conv: 1},
            preventDefault: true
        }).done(function (data) {
            switch(file.mime) {
                case 'text/html':
                    session.setMode('ace/mode/html');
                    break;
                case 'text/css':
                    session.setMode('ace/mode/css');
                    break;
                case 'text/plain':
                    session.setMode('ace/mode/text');
                    break;
            }
            $editor.ace('val', data.content);
        });
    }
}