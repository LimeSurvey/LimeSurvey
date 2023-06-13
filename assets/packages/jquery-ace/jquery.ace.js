/**
 * jQuery ACE plugin.
 * @author Sam Mousa
 * @url https://github.com/SamMousa/jquery-ace
 */
(function($) {
    var methods = {
        'init': function(options) {
            var config = $.extend({
                'mode' : 'html',
                'tabSize' : 4,
                'softTabs' : true,
                'highlightActiveLine' : true,
                'idPostfix' : '__ace',
                'toolbarCallback' : null,
                'wrapperClass' : 'jquery-ace-wrapper'
            }, options);

            return this.each(function() {
                var data = $(this).data('ace');
                if (!data) {
                    var textarea = $(this);
                    // Setup here.
                    if ($(this).attr('id'))
                    {
                        var id = textarea.attr('id') + config.idPostfix;
                    }

                    var h = textarea.height();
                    var w = textarea.width();
                    textarea.hide();

                    var wrapperDiv = $('<div/>').insertAfter(textarea).height(h).width(w).addClass(config.wrapperClass);
                    // Check if we have a toolbar.



                    var editorDiv = $('<div/>').appendTo(wrapperDiv).attr('id', id).height(h).width(w);
                    var editor = ace.edit(id);
                    if (typeof config.toolbarCallback == 'function')
                    {
                        var toolbarDiv = $('<div/>').prependTo(wrapperDiv).width(w);
                        config.toolbarCallback(toolbarDiv, editor);
                        // Resize editor.
                        if (toolbarDiv.height() > 0)
                        {
                            editorDiv.height(h - toolbarDiv.height());
                            editor.resize();
                        }

                    }

                    data = {
                        'wrapperDiv' : wrapperDiv,
                        'editorDiv' : editorDiv,
                        'editor' : editor
                    };
                    var session = editor.getSession();

                    session.setMode('ace/mode/' + config.mode);
                    session.setTabSize(config.tabSize);
                    session.setUseSoftTabs(config.softTabs);
                    textarea.data('ace', data);
                    textarea.ace('val', textarea.val());
                    editor.setHighlightActiveLine(config.highlightActiveLine);
                    editor.clearSelection();
                    editor.setReadOnly(textarea.prop('readonly'));
                    session.on('change', function(e) {
                        textarea.val(editor.getValue());
                    });

                }

            });
        },
        'get': function() {
            if (this.first().data('ace'))
            {
                return this.first().data('ace').editor;
            }
        },
        'val': function(value) {
            if (typeof value == 'undefined')
            {
                return this.first().data('ace').editor.getValue();
            }
            else
            {
                this.each(function() {
                    var data = $(this).data('ace');
                    if (data)
                    {
                        data.editor.session.setValue(value);
                    }
                });
            }
            return this;
        },
        'check': function() {
            return typeof this.first().data('ace') != 'undefined';
        }

    };

    $.fn.ace = function(method) {
        if ( methods[method] ) {
            return methods[method].apply(this,  Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.ace');
        }
    };


})(jQuery);
