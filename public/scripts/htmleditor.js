(function($) {
    $().ready(function() {
        console.log('Initializing tinymce');
        tinymce.init({
            'selector': 'textarea.html',
            'file_picker_callback': function (callback, value, meta) {
                tinymce.activeEditor.windowManager.open({
                    file: LS.createUrl('files/manage', {
                        'dialog': true,
                        'context': $('#' + this.id).attr('data-context'),
                        'key' : $('#' + this.id).attr('data-key')}),
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
            'menubar': false,
            //'toolbar' : 'file',
            /**
             * Currently there is a bug that will cause infinite recursion when
             * this is enabled.
             */

            elementpath: false,
            toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | " +
            "bullist numlist outdent indent | link image fullscreen | code | em",
            plugins: [
                'link',
                'image',
                'fullscreen',
                'code'
            ],
            //external_plugins: {
            //    "placeholder": LS.getBaseUrl() + '/components/TinyMCE-Placeholder/placeholder/plugin.js'
            //},
            //"placeholder_tokens" : [
            //    { token: "{Click to edit expression}" },
            //],
            "content_css" : [
                LS.getPublicUrl() + "/styles/expressions.css?" + new Date().getTime()
            ],
            'setup': function (editor) {

                /**
                 * Build an expression placeholder, return a jquery element.
                 * @param expr
                 * @param id
                 * @returns string
                 */
                function buildExpression(expr, extraClass) {
                    $elem = $('<input class="expression" type="button">');
                    if (typeof extraClass != 'undefined') {
                        $elem.addClass(extraClass);
                    }
                    $elem.val(expr);
                    return $elem[0].outerHTML;
                }

                function showEditor($expression) {
                    bootbox.prompt({
                        'title': 'Edit your expression here',
                        'inputType': 'textarea',
                        'value': $expression.val(),
                        'callback': function (result) {
                            if (result != null) {
                                $expression.val(result);
                            } else if ($expression.val() == "") {
                                $expression.remove();
                            }

                        }
                    });
                }

                function emToHtml(em, extraClass) {
                    return em.replace(/\{(.*?)}/g, function (expr) {
                        console.log('Found expression: ' + expr);
                        return buildExpression(expr.slice(1, -1), extraClass);
                    });
                }

                function htmlToEm(html) {
                    var result = '';
                    $(html).find('.expression').each(function(i, elem) {
                        var $elem = $(elem);
                        $elem.replaceWith('{' + $elem.val() + '}');
                    }).end().each(function (i, elem){
                        if (elem.constructor == Text) {
                            result += elem.textContent;
                        } else if (typeof elem.outerHTML == 'undefined') {
                            debugger;
                        } else {
                            result += elem.outerHTML;
                        }
                    });
                    return result;

                }
                // Add button.
                editor.addButton('em', {
                    'title' : 'Add EM placeholder',
                    //'image' : 'test.nl/test.img',
                    'onclick': function() {
                        editor.insertContent(buildExpression('', 'new'));
                        // Trigger the click event to show the popup.
                        showEditor($(editor.getDoc()).find('.new').removeClass('new'));
                    }
                });

                editor.on('keyup', function (e) {

                    var content = editor.getBody().innerHTML;


                    var newContent = emToHtml(content, 'keyup');
                    if (newContent != content) {
                        var doc = editor.getDoc();
                        var range = doc.getSelection().getRangeAt(0);
                        editor.getBody().innerHTML = newContent;
                        range.setStartAfter($(doc).find('.keyup:last')[0]);
                        doc.getSelection().removeAllRanges();
                        doc.getSelection().addRange(range);
                    }
                });
                editor.on('init', function() {
                    $doc = $(editor.getDoc());
                    $(editor.getDoc()).on('click', '.expression', function(e) {
                        showEditor($(this));
                    });
                });

                editor.on('beforeSetContent', function(e) {
                    e.content = emToHtml(e.content);
                });
                editor.on('PostProcess', function(e) {
                    // Replace placeholders.
                    e.content = htmlToEm(e.content);
                });
                //debugger;
            }







        });
    });
})(jQuery);