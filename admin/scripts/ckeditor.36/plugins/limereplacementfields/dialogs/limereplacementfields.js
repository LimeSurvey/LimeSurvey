CKEDITOR.dialog.add( 'LimeReplacementFieldsDlg', function( editor ) {
	return {
	//	title: editor.lang.limereplacementfields.title,
		title: 'Sometitle',

		minWidth: 200,
		minHeight: 80,


		contents : [
    		{
    			id : 'tab1',
    			label : '',
    			title : '',
    			elements :
    			[
    				{
    					type : 'html',
    					html : CKEDITOR.ajax.load(CKEDITOR.basePath + '../../admin.php?sid=' +
                    		CKEDITOR.config.LimeReplacementFieldsSID +
                    		'&gid=' + CKEDITOR.config.LimeReplacementFieldsGID +
                    		'&qid=' + CKEDITOR.config.LimeReplacementFieldsQID +
                    		'&fieldtype=' + CKEDITOR.config.LimeReplacementFieldsType +
                    		'&action=replacementfields' +
                    		'&editedaction=' + CKEDITOR.config.LimeReplacementFieldsAction)
    				},
    			]
    		}
	    ],


		 onOk: function() {
			var color = this.getContentElement('tab1', 'mycolor').getValue();
			var numColor;
			if (color == 'rot') numColor = '#F00';
			else if (color == 'grün') numColor = '#0F0';
			else if (color == 'blau') numColor = '#00F';

			var element = CKEDITOR.dom.element.createFromHtml(
					'<span style="color:' + numColor + ';">' +
						this.getContentElement('tab1', 'mytext').getValue() +
					'</span>');
			editor.insertElement(element);
		 }

	};

} );