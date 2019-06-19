import { debounce } from 'lodash-es';
const INPUT_EVENT_DEBOUNCE_WAIT = 300;
export default {
	name: 'TextEditor',

	render( createElement ) {
		return createElement( this.tagName );
	},

	props: {
		editor: {
			type: Function,
			default: null
		},
		onError: {
			type: Function,
			default: null
		},
		value: {
			type: String,
			default: ''
		},
		config: {
			type: Object,
			default: () => ( {} )
		},
		tagName: {
			type: String,
			default: 'div'
		},
		disabled: {
			type: Boolean,
			default: false
		}
	},

	data() {
		return {
			instance: null,

			$_lastEditorData: {
				type: String,
				default: ''
			}
		};
	},

	mounted() {
		window.LS.debug.cks = window.LS.debug.cks || [];
		if ( this.value ) {
			Object.assign( this.config, {
				initialData: this.value
			} );
		}

		this.editor.create( this.$el, this.config )
			.then( editor => {
				this.instance = editor;
				editor.isReadOnly = this.disabled;

				this.$_setUpEditorEvents();
				this.$emit( 'ready', editor );
				window.LS.debug.cks.push(editor);
			})
			.catch( error => {
				console.error( error );
				if(this.onError !== null) {this.onError(error);}
			});
		       
	},

	beforeDestroy() {
		if ( this.instance ) {
			this.instance.destroy();
			this.instance = null;
		}
		this.$emit( 'destroy', this.instance );
	},

	watch: {
		value( newValue, oldValue ) {
			if ( newValue !== oldValue && newValue !== this.$_lastEditorData ) {
				this.instance.setData( newValue );
			}
		},

		disabled( val ) {
			this.instance.isReadOnly = val;
		}
	},

	methods: {
		$_setUpEditorEvents() {
			const editor = this.instance;
			const emitInputEvent = evt => {
				const data = this.$_lastEditorData = editor.getData();
				this.$emit( 'input', data, evt, editor );
			};
			editor.model.document.on( 'change:data', debounce( emitInputEvent, INPUT_EVENT_DEBOUNCE_WAIT ) );

			editor.editing.view.document.on( 'focus', evt => {
				this.$emit( 'focus', evt, editor );
			} );

			editor.editing.view.document.on( 'blur', evt => {
				this.$emit( 'blur', evt, editor );
			} );
		}
	}
};