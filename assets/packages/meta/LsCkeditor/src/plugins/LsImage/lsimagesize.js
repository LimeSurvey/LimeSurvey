import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ImageSizeUI from './lsimagesize/lsimagesizeui';
import ImageSizeEditing from './lsimagesize/lsimagesizeediting';

export default class ImageSelect extends Plugin {

	static get pluginName() {
		return 'ImageSize';
	}

	static get requires() {
		return [ ImageSizeUI, ImageSizeEditing ];
	}
}
