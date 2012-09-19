CodeMirror UI
=============

CodeMirrorUI is a simple interface written by Jeremy Green to act as a 
wrapper around the [CodeMirror](http://codemirror.net/) text editor widget by Marijn Haverbeke.
CodeMirror is a syntax highlighter and formatter that makes it much easier to edit source code in a browser.
CodeMirrorUI is a wrapper that adds interface functionality for many functions that are already built into CodeMirror itself.
Functionality includes undo, redo, jump to line, reindent selection, and reindent entire document. 
Two options for find/replace are also available.  It is based on the MirrorFrame example that Marijn included with CodeMirror.

Demo
-------------------

[http://www.octolabs.com/javascripts/codemirror-ui/index.html](http://www.octolabs.com/javascripts/codemirror-ui/index.html)


Easily Configurable
--------------------

It's easy to configure an editor with something like this:

    //first set up some variables
    var textarea = document.getElementById('code1');
    var uiOptions = { path : 'js/', searchMode: 'popup' }
    var codeMirrorOptions = {
        mode: "javascript" // all your normal CodeMirror options go here
    }
    
    //then create the editor
    var editor = new CodeMirrorUI(textarea,uiOptions,codeMirrorOptions);
			
Installation
--------------------

    // First the CodeMirror stuff
    <script src="lib/CodeMirror-2.0/lib/codemirror.js" type="text/javascript"></script>
    <link rel="stylesheet" href="lib/CodeMirror-2.0/lib/codemirror.css">
    <script src="lib/CodeMirror-2.0/mode/javascript/javascript.js"></script>
    <link rel="stylesheet" href="lib/CodeMirror-2.0/mode/javascript/javascript.css">
    
    //Then the CodeMirrorUI stuff
    <script src="js/codemirror-ui.js" type="text/javascript"></script>
    <link rel="stylesheet" href="css/codemirror-ui.css" type="text/css" media="screen" />

You'll probably need to adjust the paths to fit your situation.

Please see index.html for examples and many additional details.

Acknowledgements
----------------------

Thanks to Marijn Haverbeke for creating and releasing [CodeMirror](http://codemirror.net/) in the first place.  
Without his excellent contribution to the community this project would have no reason to exist.

Thanks to Mark James of famfamfam.com for his [Silk Icons](http://www.famfamfam.com/lab/icons/silk/).

License
----------------------

CodeMirror UI is provided under the MIT License.  See the LICENSE file for full details.