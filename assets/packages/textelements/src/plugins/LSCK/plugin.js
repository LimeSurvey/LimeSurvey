import LSCKEditorComponent from './TextEditor.js';

const LSCKEditor = {
	install( Vue ) {
		Vue.component( 'lsckeditor', LSCKEditorComponent );
	},
	component: LSCKEditorComponent
};

export default LSCKEditor;