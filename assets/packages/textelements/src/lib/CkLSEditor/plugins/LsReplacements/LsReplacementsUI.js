import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import { addListToDropdown, createDropdown } from '@ckeditor/ckeditor5-ui/src/dropdown/utils';

import Collection from '@ckeditor/ckeditor5-utils/src/collection';
import Model from '@ckeditor/ckeditor5-ui/src/model';

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

            dropdownView.buttonView.set( {
                label: t( 'LsExpressions' ),
                icon: lsIcon,
                tooltip: true,
            } );

            // Execute the command when the dropdown item is clicked (executed).
            this.listenTo( dropdownView, 'execute', evt => {
                editor.execute( 'expression', { value: evt.source.commandParam } );
                editor.editing.view.focus();
            } );

            return dropdownView;
        } );
    }

    getReplacements() {
        const editor = this.editor;
        return new Promise((resolve,reject) => {
            $.ajax({
                url: LS.createUrl('admin/limereplacementfields'),
                data: LS.ld.merge({},{'fieldtype': editor.config.get('lsExtension:fieldtype')}, editor.config.get('lsExtension:ajaxOptions')),
                success: resolve,
                error: reject
            })
        });
    }

    _parseSelectables(resolve, dropdownView) {
        const itemDefinitions = new Collection();
        
        LS.ld.forEach( resolve.replacements, (content,grouptitle) => {
            const separatorDefinition = {
                type: 'separator',
                model: new Model({
                    label: grouptitle,
                    withText: true
                })
            };
            itemDefinitions.add( separatorDefinition );
            LS.ld.forEach( content, (title,key) => {
                const definition = {
                    type: 'button',
                    model: new Model( {
                        commandParam: key,
                        label: title,
                        withText: true
                    })
                };
                itemDefinitions.add( definition );
            });
        });
        addListToDropdown(dropdownView, itemDefinitions);
    }
}