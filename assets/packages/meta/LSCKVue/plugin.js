import LSCKEditorComponent from './TextEditor';

const LSCKEditor = {
	install( Vue ) {
		Vue.component( 'lsckeditor', LSCKEditorComponent );
	},
	component: LSCKEditorComponent
};

export default LSCKEditor;