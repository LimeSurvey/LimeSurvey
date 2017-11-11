/*
 *  The "markdown" plugin. It's indented to enhance the
 *  "sourcearea" editing mode, convert to markdown mode and highlight syntax.
 * Licensed under the MIT license
 * CodeMirror Plugin: http://codemirror.net/ (MIT License)
 * Markdown Parser:
 * HTML to Markdown Parser:
 */
'use strict';
(function() {
    CKEDITOR.plugins.add('markdown', {
        // lang: 'en,zh', // %REMOVE_LINE_CORE%
        icons: 'markdown',
        hidpi: true, // %REMOVE_LINE_CORE%
        init: function(editor) {
            // Source mode in inline editors is only available through the "sourcedialog" plugin.
            if (editor.elementMode == CKEDITOR.ELEMENT_MODE_INLINE)
                return;

            var markdown = CKEDITOR.plugins.markdown,
                rootPath = this.path,
                defaultConfig = {
                    mode: 'gfm',
                    lineNumbers: true,
                    theme: 'default'
                };
            editor.config.markdown = CKEDITOR.tools.extend(defaultConfig, editor.config.markdown || {}, true);

            editor.addMode('markdown', function(callback) {
                var contentsSpace = editor.ui.space('contents'),
                    textarea = contentsSpace.getDocument().createElement('textarea'),
                    config = editor.config.markdown;

                textarea.setStyles(
                    CKEDITOR.tools.extend({
                            // IE7 has overflow the <textarea> from wrapping table cell.
                            width: CKEDITOR.env.ie7Compat ? '99%' : '100%',
                            height: '100%',
                            resize: 'none',
                            outline: 'none',
                            'text-align': 'left'
                        },
                        CKEDITOR.tools.cssVendorPrefix('tab-size', editor.config.sourceAreaTabSize || 4)));

                // Make sure that source code is always displayed LTR,
                // regardless of editor language (#10105).
                textarea.setAttribute('dir', 'ltr');

                textarea.addClass('cke_source cke_reset cke_enable_context_menu');

                editor.ui.space('contents').append(textarea);

                var editable = editor.editable(new sourceEditable(editor, textarea)),
                    htmlData = editor.getData(1);

                // Convert to Markdown and Fill the textarea.
                if (typeof(toMarkdown) == 'undefined') {
                    CKEDITOR.scriptLoader.load(rootPath + 'js/to-markdown.js', function() {
                        editable.setData(toMarkdown(htmlData));
                    });
                } else {
                    editable.setData(toMarkdown(htmlData));
                }

                if (typeof (CodeMirror) == 'undefined' || typeof (CodeMirror.modes.gfm) == 'undefined') {
                    CKEDITOR.document.appendStyleSheet(rootPath + 'css/codemirror.min.css');

                    if (config.theme.length && config.theme != 'default') {
                        CKEDITOR.document.appendStyleSheet(rootPath + 'theme/' + config.theme + '.css');
                    }

                    CKEDITOR.scriptLoader.load(rootPath + 'js/codemirror-gfm-min.js', function() {
                        loadCodeMirror(editor, editable);
                    });
                } else {
                    loadCodeMirror(editor, editable);
                }
                if (typeof(marked) == 'undefined') {
                    CKEDITOR.scriptLoader.load(rootPath + 'js/marked.js');
                }
                // Having to make <textarea> fixed sized to conquer the following bugs:
                // 1. The textarea height/width='100%' doesn't constraint to the 'td' in IE6/7.
                // 2. Unexpected vertical-scrolling behavior happens whenever focus is moving out of editor
                // if text content within it has overflowed. (#4762)
                if (CKEDITOR.env.ie) {
                    editable.attachListener(editor, 'resize', onResize, editable);
                    editable.attachListener(CKEDITOR.document.getWindow(), 'resize', onResize, editable);
                    CKEDITOR.tools.setTimeout(onResize, 0, editable);
                }

                editor.fire('ariaWidget', this);
                editor.commands.maximize.modes.markdown = 1;
                callback();
            });

            function loadCodeMirror(editor, editable){
                window["codemirror_" + editor.id] = CodeMirror.fromTextArea(editable.$, editor.config.markdown);
                window["codemirror_" + editor.id].setSize(null, '100%');
            }

            editor.addCommand('markdown', markdown.commands.markdown);

            if (editor.ui.addButton) {
                editor.ui.addButton('Markdown', {
                    label: 'Markdown',
                    command: 'markdown'
                    // toolbar: 'mode,10'
                });
            }
            CKEDITOR.document.appendStyleText('.cke_button__markdown_label {display: inline;}'); // display button Label
            editor.on('mode', function() {
                editor.getCommand('markdown').setState(editor.mode == 'markdown' ? CKEDITOR.TRISTATE_ON : CKEDITOR.TRISTATE_OFF);
            });

            var needsFocusHack = CKEDITOR.env.ie && CKEDITOR.env.version == 9;

            function onResize() {
                // We have to do something with focus on IE9, because if sourcearea had focus
                // before being resized, the caret ends somewhere in the editor UI (#11839).
                var wasActive = needsFocusHack && this.equals(CKEDITOR.document.getActive());

                // Holder rectange size is stretched by textarea,
                // so hide it just for a moment.
                this.hide();
                this.setStyle('height', this.getParent().$.clientHeight + 'px');
                this.setStyle('width', this.getParent().$.clientWidth + 'px');
                // When we have proper holder size, show textarea again.
                this.show();

                if (wasActive)
                    this.focus();
            }
        }
    });

    var sourceEditable = CKEDITOR.tools.createClass({
        base: CKEDITOR.editable,
        proto: {
            setData: function(data) {
                this.setValue(data);
                this.status = 'ready';
                this.editor.fire('dataReady');
            },

            getData: function() {
                return this.getValue();
            },

            // Insertions are not supported in source editable.
            insertHtml: function() {},
            insertElement: function() {},
            insertText: function() {},

            // Read-only support for textarea.
            setReadOnly: function(isReadOnly) {
                this[(isReadOnly ? 'set' : 'remove') + 'Attribute']('readOnly', 'readonly');
            },

            detach: function() {
                var editor = this.editor;

                window["codemirror_" + editor.id].toTextArea(); // Remove codemirror and restore Data to origin textarea
                window["codemirror_" + editor.id] = null; // Free Memory on destroy

                var markdownSource = editor.getData();
                editor.setData(marked(markdownSource, {langPrefix: 'language-'}));

                sourceEditable.baseProto.detach.call(this);

                this.clearCustomData();
                this.remove();
            }
        }
    });
})();
CKEDITOR.plugins.markdown = {
    commands: {
        markdown: {
            modes: {
                wysiwyg: 1,
                markdown: 1
            },
            editorFocus: false,
            readOnly: 1,
            exec: function(editor) {
                if (editor.mode == 'wysiwyg')
                    editor.fire('saveSnapshot');
                editor.getCommand('markdown').setState(CKEDITOR.TRISTATE_DISABLED);
                editor.setMode(editor.mode == 'markdown' ? 'wysiwyg' : 'markdown');
            },
            canUndo: false
        }
    }
};
