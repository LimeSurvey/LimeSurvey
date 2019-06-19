
import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import FileRepository from '@ckeditor/ckeditor5-upload/src/filerepository';
import Notification from '@ckeditor/ckeditor5-ui/src/notification/notification';
import UpcastWriter from '@ckeditor/ckeditor5-engine/src/view/upcastwriter';

import LsImageSelectCommand from './lsimageselectcommand';
import { isImageType, isLocalImage, fetchLocalImage } from '@ckeditor/ckeditor5-image/src/imageupload/utils';


export default class LSImageSelectEditing extends Plugin {

	/**
	 * @inheritDoc
	 */
	init() {
		const editor = this.editor;
		const doc = editor.model.document;
		const schema = editor.model.schema;
		const conversion = editor.conversion;
		
		schema.extend( 'image', {
			allowAttributes: [ 'src', 'folder', 'hash' ]
		} );

		editor.commands.add( 'imageSelect', new LsImageSelectCommand( editor ) );

		conversion.for( 'upcast' )
			.attributeToAttribute( {
				view: {
					name: 'img',
					key: 'hash'
				},
				model: 'hash'
			} );
	}
}

	