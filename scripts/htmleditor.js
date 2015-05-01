(function($) {

    tinymce.init({
        'selector' :'textarea.html',
        'menubar' : false,
        'setup' : function(editor) {
            editor.on('change', function() {
                editor.save();
            })
        }

    });
})(jQuery);

