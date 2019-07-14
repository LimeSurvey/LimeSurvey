import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import LsImageSizeUI from './lsimagesize/lsimagesizeui';
import LsImageSizeEditing from './lsimagesize/lsimagesizeediting';

export default class LsImageSize extends Plugin {

	static get pluginName() {
		return 'LsImageSize';
	}

	static get requires() {
		return [ LsImageSizeUI, LsImageSizeEditing ];
	}
}
