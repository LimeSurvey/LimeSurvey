
import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

import LsImageSelectCommand from './lsimageselectcommand';


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

		editor.commands.add( 'selectImage', new LsImageSelectCommand( editor ) );

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

	