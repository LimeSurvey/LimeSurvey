import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import LsImageSizeCommand from './lsimagesizecommand';
import { modelToViewSizeAttribute, viewToModelSizeAttribute, getImageSizes } from './utils';

export default class LsImageSizeEditing extends Plugin {
	/**
	 * @inheritDoc
	 */
	static get pluginName() {
		return 'LsImageSizeEditing';
	}

	init() {
		const editor = this.editor;
		const schema = editor.model.schema;
		const data = editor.data;
		const editing = editor.editing;

        editor.config.define( 'image.sizes', [ '10','25','50','75', '100' ] );
        
		const sizes = getImageSizes( );
        schema.extend( 'image', { allowAttributes: 'imageSize' } );
        
		const modelToViewConverter = modelToViewSizeAttribute();

		editing.downcastDispatcher.on( 'attribute:imageSize:image', modelToViewConverter );
		data.downcastDispatcher.on( 'attribute:imageSize:image', modelToViewConverter );

		data.upcastDispatcher.on( 'element:figure', viewToModelSizeAttribute( sizes ), { priority: 'low' } );

		editor.commands.add( 'imageSize', new LsImageSizeCommand( editor, sizes ) );
	}
}