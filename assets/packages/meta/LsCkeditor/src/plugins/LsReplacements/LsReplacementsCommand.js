import Command from '@ckeditor/ckeditor5-core/src/command';

export default class LsReplacementsCommand extends Command {
    execute( { value } ) {
        const editor = this.editor;

        editor.model.change( writer => {
            const oExpression = writer.createElement('expression', { name: value });
            editor.model.insertContent(oExpression);
            writer.setSelection(oExpression, 'on');
        });
    }
    refresh() {
        this.isEnabled = this.editor.model.schema.checkChild(this.editor.model.document.selection.focus.parent, 'expression');
    }
}