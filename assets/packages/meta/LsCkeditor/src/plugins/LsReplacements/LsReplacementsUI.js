import he from 'he';

import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import Collection from '@ckeditor/ckeditor5-utils/src/collection';
import Model from '@ckeditor/ckeditor5-ui/src/model';

import { createDropdown } from '@ckeditor/ckeditor5-ui/src/dropdown/utils';
import { addListToDropdown } from '../CustomDropdown/utils';


import lsIcon from '../assets/LsIcon.svg';

export default class LsReplacementsUI extends Plugin {
    init() {
        console.log( 'LsReplacementUI#init() got called' );
        const editor = this.editor;
        const t = editor.t;

        editor.ui.componentFactory.add( 'expressions', locale => {
            const dropdownView = createDropdown( locale );
            this.getReplacements().then(
                (resolve) => { this._parseSelectables( resolve, dropdownView); },
                (reject) => { console.error(reject); }
            );

            dropdownView.set( {
                class: 'ck-scrollbar',
                panelPosition: 'sw',
            } );

            dropdownView.buttonView.set( {
                label: t( 'LsExpressions' ),
                icon: lsIcon,
                tooltip: true,
            } );

            dropdownView.panelView.set('class','ck-scrollbar');

            // Execute the command when the dropdown item is clicked (executed).
            this.listenTo( dropdownView, 'execute', evt => {
                editor.execute( 'expression', { name: evt.source.commandParam, type: evt.source.typeDef } );
                editor.editing.view.focus();
            } );

            this.listenTo( editor, 'change:fieldtype', (evt, name, value) => {
                dropdownView.panelView.children.clear();
                
                this.getReplacements({fieldtype: value}).then(
                    (resolve) => { this._parseSelectables( resolve, dropdownView); },
                    (reject) => { console.error(reject); }
                );
            });


            return dropdownView;
        } );
    }
    
    refresh() {
        
    }

    getReplacements(data = {}) {
        const editor = this.editor;
        return new Promise((resolve,reject) => {
            $.ajax({
                url: LS.createUrl('admin/limereplacementfields'),
                data: LS.ld.merge({},{'newtype': 1, 'fieldtype': editor.config.get('lsExtension:fieldtype')}, editor.config.get('lsExtension:ajaxOptions'), data),
                success: resolve,
                error: reject
            })
        });
    }

    _parseSelectables(resolve, dropdownView) {
        const itemDefinitions = new Collection();
        
        LS.ld.forEach( resolve, (content,grouptitle) => {
            const separatorDefinition = {
                type: 'groupseparator',
                model: new Model({
                    label: grouptitle,
                    withText: true
                })
            };
            itemDefinitions.add( separatorDefinition );
            LS.ld.forEach( content, (contentData,key) => {
                const definition = {
                    type: 'button',
                    model: new Model( {
                        commandParam: key,
                        typeDef: contentData.type,
                        label: he.decode(contentData.value),
                        class: 'lsimageSelect--dropdown-button-inner',
                        withText: true
                    })
                };
                itemDefinitions.add( definition );
            });
        });
        addListToDropdown(dropdownView, itemDefinitions, 'lsimageSelect--dropdown-list');
    }
}
