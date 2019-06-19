import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import { addListToDropdown, createDropdown } from '@ckeditor/ckeditor5-ui/src/dropdown/utils';

import PreviewDropdownButton from './PreviewDropdown/PreviewDropdown';
import selectImageIcon from '../../assets/LsSelectFileIcon.svg';


export default class ImageSelectUI extends Plugin {
	/**
	 * @inheritDoc
	 */
	init() {
		const editor = this.editor;
		const t = editor.t;

        editor.ui.componentFactory.add( 'selectImage', locale => {
            const dropdownView = createDropdown( locale, PreviewDropdownButton );
            this.getImages().then(
                (resolve) => { this._parseSelectables( resolve, dropdownView); },
                (reject) => { console.error(reject); }
			);
			
			const command = editor.commands.get( 'imageSelect' );
			view.buttonView.bind( 'isEnabled' ).to( command );

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
        
        LS.ld.forEach( resolve.replacements, (content,grouptitle) => {
            const separatorDefinition = {
                type: 'separator',
                model: new Model({
					label: grouptitle, 
                    withText: true
                })
			};
			
            itemDefinitions.add( separatorDefinition );
            LS.ld.forEach( content, (object,key) => {
                const definition = {
                    type: 'button',
                    model: new Model( {
						imageSrc: object.src,
						imageHash: object.hash,
                        label: object.title,
                        withText: true
                    })
                };
                itemDefinitions.add( definition );
            });
        });
        addListToDropdown(dropdownView, itemDefinitions);
	}
}
