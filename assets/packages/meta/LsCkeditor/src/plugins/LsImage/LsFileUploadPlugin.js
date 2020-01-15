import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import LSFileUploadAdapter from './LSFileUploadAdapter/LSFileUploadAdapter';

export default class LsFileUploadPlugin extends Plugin {
	init() {
		this.editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new LSFileUploadAdapter(loader, this.editor);
        };
	}
}
