(function($) {

    tinymce.init({
        'selector' :'textarea.html',
        'file_picker_callback' : function(callback, value, meta) {
            tinymce.activeEditor.windowManager.open({
                file: '/index.php?r=files/manage&dialog=1',
                title: 'elFinder',
                width: 900,
                height: 480,
                resizable: true
            }, {
                callback: callback,
                value: value,
                meta: meta
            });
            return false;
        },
        'file_picker_types': 'file image media',
        'menubar' : false,
        //'toolbar' : 'file',
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | " +
        "bullist numlist outdent indent | link image fullscreen",
        plugins: [
            'link',
            'image',
            'fullscreen'
        ],
        'setup' : function(editor) {
            editor.on('change', function() {
                editor.save();
            })
        }

    });
})(jQuery);


function elFinderBrowser (field_name, url, type, win) {
    tinymce.activeEditor.windowManager.open({
        file: '/elfinder/elfinder.html',// use an absolute path!
        title: 'elFinder 2.0',
        width: 900,
        height: 450,
        resizable: 'yes'
    }, {
        setUrl: function (url) {
            win.document.getElementById(field_name).value = url;
        }
    });
    return false;
}