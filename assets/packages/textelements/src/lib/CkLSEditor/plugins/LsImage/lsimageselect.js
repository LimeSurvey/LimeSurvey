/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module image/lsimageselect
 */

import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ImageSelectUI from './lsimageselect/lsimageselectui';
import ImageSelectEditing from './lsimageselect/lsimageselectediting';

/**
 * The image upload plugin.
 *
 * For a detailed overview, check the {@glink features/image-upload/image-upload image upload feature} documentation.
 *
 * This plugin does not do anything directly, but it loads a set of specific plugins to enable image uploading:
 *
 * * {@link module:image/imageupload/imageuploadediting~ImageUploadEditing},
 * * {@link module:image/imageupload/imageuploadui~ImageUploadUI},
 * * {@link module:image/imageupload/imageuploadprogress~ImageUploadProgress}.
 *
 * @extends module:core/plugin~Plugin
 */
export default class ImageSelect extends Plugin {
	/**
	 * @inheritDoc
	 */
	static get pluginName() {
		return 'ImageSelect';
	}

	/**
	 * @inheritDoc
	 */
	static get requires() {
		return [ ImageSelectUI, ImageSelectEditing ];
	}
}
