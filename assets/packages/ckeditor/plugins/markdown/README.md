# CKEditor-Markdown-Plugin
Markdown Plugin for CKEditor
This is a plugin for CKEditor, you can use `markdown` mode in CKEditor. Moreover, your article in `WYSIWYG` mode can be translated to `markdown`.

## Get Started
It needs [ckeditor standard version](http://download.cksource.com/CKEditor/CKEditor/CKEditor%204.4.7/ckeditor_4.4.7_standard.zip)

You can see the [DEMO](http://hectorguo.github.io/CKEditor-Markdown-Plugin/)

## Usage
1. Create a folder named `markdown` in `ckeditor/plugins` path;
2. Download the source, and uncompress it in the folder;
3. Edit `config.js` (such as `ckeditor/config.js`):
```javascript
	config.extraPlugins = 'markdown'; // add this plugin
```
Enjoy it!

`config.js` example:
```javascript
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		// { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		// { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		// { name: 'links' },
		// { name: 'insert' },
		// { name: 'forms' },
		{ name: 'tools' },
		// { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },

		// '/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		// { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'others' }
		// { name: 'colors' },
		// { name: 'about' }
	];

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';
	config.extraPlugins = 'markdown';  // this is the point!
	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
};
```

## Thanks
- [marked](https://github.com/chjj/marked)
- [to-markdown](http://domchristie.github.io/to-markdown)
- [codemirror](https://github.com/codemirror/CodeMirror)
