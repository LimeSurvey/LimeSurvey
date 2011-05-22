// Register the related command.

CKEDITOR.plugins.add('LimeReplacementFields', {
	init : function( editor )
	{
		editor.addCommand( 'LimeReplacementFieldsDlg', new CKEDITOR.dialogCommand( 'LimeReplacementFieldsDlg' ) );

		editor.ui.addButton( 'LimeReplacementFields',
        {
            label : 'Insert LimeSurvey variable field',
            command : 'LimeReplacementFieldsDlg',
            icon: this.path+'limereplacementfields.gif'
        });

		CKEDITOR.dialog.add( 'LimeReplacementFieldsDlg', CKEDITOR.basePath + '/plugins/limereplacementfields/dialogs/limereplacementfields.js');

	}
});
