import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import Collection from '@ckeditor/ckeditor5-utils/src/collection';
import Model from '@ckeditor/ckeditor5-ui/src/model';

import { createDropdown } from '@ckeditor/ckeditor5-ui/src/dropdown/utils';
import { addListToDropdown } from '../../CustomDropdown/utils';

import selectImageIcon from '../../assets/LsSelectFileIcon.svg';


export default class ImageSelectUI extends Plugin {
	/**
	 * @inheritDoc
	 */
	init() {
		const editor = this.editor;
		const t = editor.t;

        editor.ui.componentFactory.add( 'selectImage', locale => {
            const dropdownView = createDropdown( locale );
            this.getImages().then(
                (resolve) => { this._parseSelectables( resolve, dropdownView); },
                (reject) => { console.error(reject); }
			);

            dropdownView.set( {
                class: 'ck-scrollbar',
                panelPosition: 'sw',
            } );
            			
            dropdownView.buttonView.set( {
                label: t( 'Select Image from server' ),
                icon: selectImageIcon,
                tooltip: true,
            } );

            // Execute the command when the dropdown item is clicked (executed).
            this.listenTo( dropdownView, 'execute', evt => {
                editor.execute( 'selectImage', { src: evt.source.imageSrc, hash: evt.source.imageHash } );
                editor.editing.view.focus();
            } );

            return dropdownView;
		});
	}
	
	getImages() {
 		const editor = this.editor;
        return new Promise((resolve,reject) => {
            $.ajax({
                url: LS.createUrl('admin/filemanager/sa/getFilesForSurvey'),
                data: LS.ld.merge({},editor.config.get('lsExtension:ajaxOptions')),
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
            LS.ld.forEach( content, (object,key) => {
                const definition = {
                    type: 'previewbutton',
                    model: new Model( {
						imageSrc: object.src,
						imageHash: object.hash,
                        label: object.shortName,
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
