/**
 * @fileOverview The "lsswitchtoolbars" plugin.
 *
 */

(function()
{
    CKEDITOR.plugins.add('lsswitchtoolbars',
    {
        lang : [ 'en' ],
        init : function( editor )
        {
            if (!editor.config.basicToolbar || !editor.config.fullToolbar) {
                return;
            }

            var lang = editor.lang.lsswitchtoolbars;

            var commandDefinition = {
                exec: function (editor) {
                    var name = editor.name;
                    var config = editor.config;

                    var isMaximized = editor.getCommand('maximize').state == CKEDITOR.TRISTATE_ON;
                    if (isMaximized) {
                        editor.execCommand('maximize');
                    }
                    var switchToFull = config.toolbar == config.basicToolbar;
                    editor.destroy();
                    config.toolbar = switchToFull ? config.fullToolbar : config.basicToolbar;
                    var newEditor = CKEDITOR.replace(name, config);
                    if (isMaximized) {
                        newEditor.on('instanceReady', function(event) {
                            this.execCommand('maximize');
                        });
                    }
                    CKEDITOR.tools.setCookie('LS_CKE_TOOLBAR', switchToFull ? 'full' : 'basic');
                },

                editorFocus: false,
                canUndo: false
            };

            editor.addCommand('switchToolbar', commandDefinition);

            var switchToFull = editor.config.toolbar == editor.config.basicToolbar;
            var icon = switchToFull ? 'full' : 'basic';
            var label = switchToFull ? lang.titleFull : lang.titleBasic;

            editor.ui.addButton('SwitchToolbar', {
                label : label,
                command :'switchToolbar',
                icon : this.path + icon + '.gif'
            });

        },
    });
})();

CKEDITOR.plugins.setLang('lsswitchtoolbars','en', {
        titleBasic:sSwitchToolbarBasicTitle,
        titleFull:sSwitchToolbarFullTitle,
    }
);