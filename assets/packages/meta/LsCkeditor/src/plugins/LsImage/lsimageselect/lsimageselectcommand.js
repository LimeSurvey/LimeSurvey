import Command from '@ckeditor/ckeditor5-core/src/command';
import { insertImage, isImageAllowed } from '@ckeditor/ckeditor5-image/src/image/utils';

/**
 * LimeSurvey specific image select command.
 *
 * @extends module:core/command~Command
 */
export default class LsImageSelectCommand extends Command {
	/**
	 * @inheritDoc
	 */
	refresh() {
		this.isEnabled = isImageAllowed( this.editor.model );
	}
	execute( eventData ) {
		const editor = this.editor;
		const model = editor.model;

		model.change( writer => {
			insertImage( writer, model, eventData );
		});
	}
}
