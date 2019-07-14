import Command from '@ckeditor/ckeditor5-core/src/command';
import { isImage } from '@ckeditor/ckeditor5-image/src/image/utils';

export default class LsImageSizeCommand extends Command {
	
	constructor( editor, sizes ) {
		super( editor );
		this.defaultSize = '100';
		this.sizes = sizes;
	}

	/**
	 * @inheritDoc
	 */
	refresh() {
		const element = this.editor.model.document.selection.getSelectedElement();

		this.isEnabled = isImage( element );

		if ( !element ) {
			this.value = false;
		} else if ( element.hasAttribute( 'imageSize' ) ) {
			const attributeValue = element.getAttribute( 'imageSize' );
			this.value = this.sizes.indexOf(attributeValue) !== -1 ? attributeValue : this.defaultSize;
		} else {
			this.value = this.defaultSize;
		}
	}

	execute( options ) {
		const size = options.value;
		console.log('LsImageSize executed', options);
		
		const imageElement = this.editor.model.document.selection.getSelectedElement();

		this.editor.model.change( writer => {
            writer.setAttribute( 'imageSize', size, imageElement );
		});
	}
}