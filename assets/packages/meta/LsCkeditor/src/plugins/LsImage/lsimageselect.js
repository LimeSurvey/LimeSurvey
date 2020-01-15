import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ImageSelectUI from './lsimageselect/lsimageselectui';
import ImageSelectEditing from './lsimageselect/lsimageselectediting';

export default class ImageSelect extends Plugin {

	static get pluginName() {
		return 'ImageSelect';
	}

	static get requires() {
		return [ ImageSelectUI, ImageSelectEditing ];
	}
}
