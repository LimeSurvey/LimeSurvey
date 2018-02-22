/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileOverview The "limereplacementfields" plugin.
 *
 */

(function()
{
    var limereplacementfieldsReplaceRegex = /(\{[A-Z]+[^\{\}]+[A-Z0-9]+\})/g;
    CKEDITOR.plugins.add( 'limereplacementfields',
    {
        requires : [ 'dialog' ],
        lang : [ 'en' ],
        init : function( editor )
        {
            var lang = editor.lang.limereplacementfields;

            editor.addCommand( 'createlimereplacementfields', new CKEDITOR.dialogCommand( 'createlimereplacementfields' ) );
            editor.addCommand( 'editlimereplacementfields', new CKEDITOR.dialogCommand( 'editlimereplacementfields' ) );

            editor.ui.addButton( 'Createlimereplacementfields',
            {
                label : lang.title,
                command :'createlimereplacementfields',
                icon : this.path + 'limereplacementfields.gif'
            });

            if ( editor.addMenuItems )
            {
                editor.addMenuGroup( 'limereplacementfields', 20 );
                editor.addMenuItems(
                    {
                        editlimereplacementfields :
                        {
                            label : lang.title,
                            command : 'editlimereplacementfields',
                            group : 'limereplacementfields',
                            order : 1,
                            icon : this.path + 'limereplacementfields.gif'
                        }
                    } );

                if ( editor.contextMenu )
                {
                    editor.contextMenu.addListener( function( element, selection )
                        {
                            if ( !element || !element.data( 'cke-limereplacementfields' ) )
                                return null;

                            return { editlimereplacementfields : CKEDITOR.TRISTATE_OFF };
                        } );
                }
            }

            editor.on( 'doubleclick', function( evt )
                {
                    if ( CKEDITOR.plugins.limereplacementfields.getSelectedPlaceHoder( editor ) )
                        evt.data.dialog = 'editlimereplacementfields';
                });

           CKEDITOR.addCss(
                '.cke_limereplacementfields' +
                '{' +
                    'background-color: #ffff00;' +
                    ( CKEDITOR.env.gecko ? 'cursor: default;' : '' ) +
                '}'
            );

            editor.on( 'contentDom', function()
                {
                    editor.document.getBody().on( 'resizestart', function( evt )
                        {
                            if ( editor.getSelection().getSelectedElement().data( 'cke-limereplacementfields' ) )
                                evt.data.preventDefault();
                        });
                });

            CKEDITOR.dialog.add( 'createlimereplacementfields', this.path + 'dialogs/limereplacementfields.js' );
            CKEDITOR.dialog.add( 'editlimereplacementfields', this.path + 'dialogs/limereplacementfields.js' );
        },
        afterInit : function( editor )
        {
            var dataProcessor = editor.dataProcessor,
                dataFilter = dataProcessor && dataProcessor.dataFilter,
                htmlFilter = dataProcessor && dataProcessor.htmlFilter;

            if ( dataFilter )
            {
                dataFilter.addRules(
                {
                    text : function( text )
                    {
                        return text.replace( limereplacementfieldsReplaceRegex, function( match )
                            {
                                return CKEDITOR.plugins.limereplacementfields.createlimereplacementfields( editor, null, match, 1 );
                            });
                    }
                });
            }

            if ( htmlFilter )
            {
                htmlFilter.addRules(
                {
                    elements :
                    {
                        'span' : function( element )
                        {
                            if ( element.attributes && element.attributes[ 'data-cke-limereplacementfields' ] )
                                delete element.name;
                        }
                    }
                });
            }
        }
    });
})();

CKEDITOR.plugins.setLang('limereplacementfields','en', {
        title:sReplacementFieldTitle,
        button:sReplacementFieldButton
    }
);

CKEDITOR.plugins.limereplacementfields =
{
    createlimereplacementfields : function( editor, oldElement, text, isGet )
    {

        if ( isGet )
        {
            return text;
        }

        editor.insertText(text);

        return null;
    },

    getSelectedPlaceHoder : function( editor )
    {
        var range = editor.getSelection().getRanges()[ 0 ];
        range.shrink( CKEDITOR.SHRINK_TEXT );
        var node = range.startContainer;
        while( node && !( node.type == CKEDITOR.NODE_ELEMENT && node.data( 'cke-limereplacementfields' ) ) )
            node = node.getParent();
        return node;
    }
};
