import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import Collection from '@ckeditor/ckeditor5-utils/src/collection';
import Model from '@ckeditor/ckeditor5-ui/src/model';

import { createDropdown, addListToDropdown } from '@ckeditor/ckeditor5-ui/src/dropdown/utils';
import { getImageSizes } from './utils';

import imageSizeIcon from '../../assets/LsImageSizeIcon.svg';

import '../../assets/imageSizes.scss';

export default class LsImageSizeUI extends Plugin {
	
	static get pluginName() {
		return 'LsImageSizeUI';
	}


	init() {
		const editor = this.editor;
		const configuredSizes = editor.config.get( 'image.sizes' );

        const allSizes = getImageSizes( );
        
        editor.ui.componentFactory.add( 'imageSize', locale => {
            const dropdownView = createDropdown( locale );
            const itemDefinitions = new Collection();

            allSizes.forEach( size => {
                const definition = {
                    type: 'button',
                    model: new Model( {
                        label: size,
                        value: size,
                        class: 'lsImageSize--sizeselectorbutton',
                        withText: true
                    })
                };
                itemDefinitions.add( definition );
            });
            
            addListToDropdown(dropdownView, itemDefinitions);


            dropdownView.buttonView.set( {
                label: 'Select width for image',
                icon: imageSizeIcon,
                tooltip: true,
            } );

            this.listenTo( dropdownView, 'execute', evt => {
                editor.execute( 'imageSize', { value: evt.source.value } );
                editor.editing.view.focus();
            });

            return dropdownView;
        });
	}
}