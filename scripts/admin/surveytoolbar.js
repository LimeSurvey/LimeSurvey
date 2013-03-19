// $Id: surveytoolbar.js 9401 2010-11-03 11:54:50Z c_schmitz $
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
    // Load the superfish menu
    $('ul.sf-menu').superfish({
        speed:'fast'
    });
    //Load the special tooltips for the surveybar
    $('.surveybar img[alt]').each(function() {
        if($(this).attr('alt') != '')
        {
             $(this).qtip({
                'content': {
                    'attr': 'alt'
                },
                'style': {
                    'tip': true,
                    'classes': "qtip-cream"
                },
                'position': {
                    'adjust': {
                        'method': "flip flip"
                    },
                    'viewport': $(window),
                    'at': "top right",
                    'my': "bottom left"
                },
                'show': {
                    'delay': 00
                },
                'hide': {
                    'event': "mouseout"
                },
                'events': {
                    'render': function (event, api) {$(api.options.hide.when.target).bind('click', api.hide);}
                }
            });
        }
    });
    $(".saveandreturn").click(function() {
        var form=$(this).parents('form:first'); //Get the parent form info
        $("#newpage").val('return');
        form.submit();
    });
});
