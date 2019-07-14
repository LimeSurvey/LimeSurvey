import ListView from '@ckeditor/ckeditor5-ui/src/list/listview';
import ListItemView from '@ckeditor/ckeditor5-ui/src/list/listitemview';
import ListSeparatorView from '@ckeditor/ckeditor5-ui/src/list/listseparatorview';
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import SwitchButtonView from '@ckeditor/ckeditor5-ui/src/button/switchbuttonview';
import PreviewButtonView from './PreviewButtonView';
import ListGroupSeparatorView from './ListGroupSeparatorView';

export function addListToDropdown( dropdownView, items, extraClasses='') {
	const locale = dropdownView.locale;
	const listView = dropdownView.listView = new ListView( locale );

	listView.extendTemplate({
		attributes: {
			class: [
				extraClasses
			]
		}
	});

	listView.items.bindTo( items ).using( ( { type, model } ) => {
		if ( type === 'separator' ) {
			return new ListSeparatorView( locale );
			
		} else if (type === 'groupseparator' ){
			const listGroupSeperatorView =  new ListGroupSeparatorView( locale );
			listGroupSeperatorView.bind( ...Object.keys( model ) ).to( model );
			return listGroupSeperatorView;

		} else if ( type === 'button' || type === 'switchbutton' || type === 'previewbutton' ) {
			const listItemView = new ListItemView( locale );
			let buttonView;

			if ( type === 'button' ) {
				buttonView = new ButtonView( locale );
			} else if ( type === 'previewbutton' ){
				buttonView = new PreviewButtonView( locale );
			} else {
				buttonView = new SwitchButtonView( locale );
			}

			// Bind all model properties to the button view.
			buttonView.bind( ...Object.keys( model ) ).to( model );
			buttonView.delegate( 'execute' ).to( listItemView );

			listItemView.children.add( buttonView );

			return listItemView;
		}
	} );

	dropdownView.panelView.children.add( listView );

	listView.items.delegate( 'execute' ).to( dropdownView );
}
