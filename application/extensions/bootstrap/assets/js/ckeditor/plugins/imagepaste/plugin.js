/*
 * @file image paste plugin for CKEditor
	Feature introduced in: https://bugzilla.mozilla.org/show_bug.cgi?id=490879
	doesn't include images inside HTML (paste from word): https://bugzilla.mozilla.org/show_bug.cgi?id=665341
	Includes Drag&Drop file uploads for new browsers.
 * Copyright (C) 2012 Alfonso Martínez de Lizarrondo
 *
 */

(function() {

/*
function dataURItoBlob(dataURI) {
    // convert base64 to raw binary data held in a string
    // doesn't handle URLEncoded DataURIs
    var byteString = atob(dataURI.split(',')[1]);

    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

    // write the bytes of the string to an ArrayBuffer
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }

//return new Blob(ab, { "type" : mimeString });

    // write the ArrayBuffer to a blob, and you're done
    var bb = new MozBlobBuilder();
    bb.append(ab);
    return bb.getBlob(mimeString);

}
*/

function getTimeStampId()
{
	return (new Date()).toJSON().replace(/:|T|-/g, "_").replace(/\..*/, "");
}

CKEDITOR.plugins.add( 'imagepaste',
{
	init : function( editor )
	{
		// if not defined specifically for images, reuse the default file upload url
		if (!editor.config.filebrowserImageUploadUrl)
			editor.config.filebrowserImageUploadUrl = editor.config.filebrowserUploadUrl;

		// Paste from clipboard:
		editor.on( 'paste', function(e) {
			var data = e.data;

			var html = (data.html || ( data.type && data.type=='html' && data.dataValue));
			if (!html)
				return;

			// strip out webkit-fake-url as they are useless:
			html = html.replace( /<img src="webkit-fake-url:.*?">/g, "");

			// Handles image pasting in Firefox
			// Replace data: images in Firefox and upload them
			html = html.replace( /<img src="data:image\/.{3,4};base64,.*?" alt="">/g, function( img )
				{
					if (!editor.config.filebrowserImageUploadUrl)
						return "";

//					var data = img.match(/"data:image\/png;base64,(.*?)"/)[1],
					var match = img.match(/"(data:image\/(.{3,4});base64,.*?)"/),
						data = match[1],
						type = match[2].toLowerCase(),
						id = getTimeStampId(),
						fileName = id + '.' + type;

					/*
					var match = img.match(/"(data:image\/(.{3,4});base64,.*?)"/),

					This way we will break transparency or get worse quality on jpg

					var tmpImg = document.createElement("img");
					tmpImg.onload = function() {
						var canvas = document.createElement("canvas");
						canvas.width = tmpImg.width;
						canvas.height = tmpImg.height;
						var ctx = canvas.getContext('2d');
						ctx.drawImage(tmpImg, 0, 0);
						var file = canvas.mozGetAsFile( fileName, "image/" + type );
						uploadFile( editor, file, id, fileName);
					};
					tmpImg.src=data;
					*/
/*
					var file = dataURItoBlob(data);
					it's missing https://bugzilla.mozilla.org/show_bug.cgi?id=690659 to work
					uploadFile( editor, file, id, fileName );
*/

					var url= editor.config.filebrowserImageUploadUrl + '&CKEditor=' + editor.name + '&CKEditorFuncNum=2&langCode=' + editor.langCode;

					var xhr = new XMLHttpRequest();

					xhr.open("POST", url);
					xhr.onload = function() {
						// Upon finish, get the url and update the file
						var m = xhr.responseText.match(/2,\s*('|")(.*?)\1,/),
							imageUrl = m && m[2];
						// Can fail this one?
						editor.fire('endDirectUpload', { name: fileName, ok: (!!imageUrl) } );

						var theImage = editor.document.getById( id );
						theImage.data( 'cke-saved-src', imageUrl);
						theImage.setAttribute( 'src', imageUrl);
						theImage.removeAttribute( 'id' );
					};

					// Create the multipart data upload.
					var bin = window.atob( data.split(',')[1] );
					var BOUNDARY = "---------------------------1966284435497298061834782736";
					var rn = "\r\n";
					var req = "--" + BOUNDARY;

					req += rn + "Content-Disposition: form-data; name=\"upload\"";
					// add timestamp?
					req += "; filename=\"" + fileName + "\"" + rn + "Content-type: image/" + type;
					req += rn + rn + bin + rn + "--" + BOUNDARY + "--";

					editor.fire('startDirectUpload', { name: fileName});
					xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + BOUNDARY);
					xhr.sendAsBinary(req);

					return img.replace(/>/, ' id="' + id + '">');

				});

			if (e.data.html)
				e.data.html = html;
			else
				e.data.dataValue = html;
		});

		if (!editor.config.filebrowserUploadUrl && !editor.config.filebrowserImageUploadUrl)
			return;

		// drag & drop
		editor.on( 'contentDom', function(ev)
			{
				var root = editor.document;
				if (editor.elementMode == 3) // ELEMENT_MODE_INLINE -> v4 inline editing
				{
					root = editor.element;
				}

				// Must use CKEditor 3.6.3 for IE 10
				root.on( 'paste', function(e)
				{
					var data = e.data.$.clipboardData;
					if (!data)
						return;

					// Chrome has clipboardData.items. Other browsers don't provide this info at the moment.
					if (!data.items)
						return;

					if (data.items && data.items.length>0)
					{
						var items = data.items,
							i,
							item;

						// Check first if there is a text/html or text/plain version, and leave the browser use that:
						// otherwise, pasting from MS Word to Chrome in Mac will always generate a black rectangle.
						for (i=0; i< items.length; i++)
						{
							item = items[i];
							if ( item.kind=="string" && (item.type=="text/html" || item.type=="text/plain") )
								return;
						}

						// We're safe, stupid Office-Mac combination won't disturb us.
						for (i=0; i< items.length; i++)
						{
							item = items[i];
							if ( item.kind != "file" )
								continue;

							e.data.preventDefault();

							var file = item.getAsFile(),
								id = getTimeStampId(),
								fileName = id + '.png',
								element = createPreview(file, id, fileName, editor);

							editor.insertElement(element);

							// Upload the file
							uploadFile( editor, file, id, fileName );
						}
					}

				});

				// https://bugs.webkit.org/show_bug.cgi?id=57185
//				if (CKEDITOR.env.webkit || CKEDITOR.env.ie)
				if ( !CKEDITOR.env.gecko )
				{
					root.on( 'dragover', function(e)
					{
						var ev = e.data.$;
						if (ev.dataTransfer && ev.dataTransfer.files &&
								(ev.dataTransfer.files.length || ( ev.dataTransfer.types &&
									(ev.dataTransfer.types.contains && ev.dataTransfer.types.contains('Files') || ev.dataTransfer.types.indexOf && ev.dataTransfer.types.indexOf( 'Files' )!=-1))
								)
							)
						{
							e.data.preventDefault();
						}
					});
				}

				root.on( 'drop', function( e )
				{
					if (typeof FormData == 'undefined')
						return;

					var ev = e.data.$,
						data = ev.dataTransfer;
					if ( data && data.files && data.files.length>0 )
					{
						for( var i=0; i<data.files.length; i++)
						{
							var file = data.files[ i ],
								id = CKEDITOR.tools.getNextId(),
								fileName = file.name,
								range,
								element = createPreview(file, id, fileName, editor);

							// Move to insertion point
							// Firefox, custom properties in event. They might add the new W3C api for Fx 10
							if ( ev.rangeParent )
							{
								var node = ev.rangeParent,
									offset = ev.rangeOffset;
								range = editor.document.$.createRange();
								range.setStart( node, offset );
								range.collapse( true );
								range.insertNode( element.$ );
							}
							else
							{
								// Webkit, old documentView API
								if ( document.caretRangeFromPoint )
								{
									range = editor.document.$.caretRangeFromPoint( ev.clientX, ev.clientY );
									range.insertNode( element.$ );
								}
								else
								{
									// IE (10), still doesn't support new API
									if ( document.body.createTextRange )
									{
										range = editor.document.$.body.createTextRange();
										range.moveToPoint( ev.clientX, ev.clientY );
										range.pasteHTML( element.$.outerHTML );
									}
									else
									{
										// Opera comes here :-(
										// let's insert it at just the current location.
										editor.insertElement( element );
//										if (window.console)
//											console.log("No API detected to find the insertion point. Exit");
//										return;
									}
								}
							}

							// Prevent default insertion
							e.data.preventDefault();

							uploadFile( editor, file, id, fileName );
						}
					}
				});

			});

	} //Init
} );

// Creates the element, but doesn't inserts it
function createPreview(file, id, fileName, editor)
{
	var isImage = (/\.(jpe?g|gif|png)$/i).test( fileName );
	// Create and insert our element
	if ( isImage )
	{
		element = createSVGAnimation(file, id, editor);
/*
		element = new CKEDITOR.dom.element( 'img' );
		// Set preview using temp URL
		var theURL = window.URL || window.webkitURL;
		if ( theURL )
		{
			element.$.src = theURL.createObjectURL( file );
			element.$.onload = function(e) {
				theURL.revokeObjectURL( this.src );
				this.onload = null;
			};
		}
*/
	}
	else
	{
		element = new CKEDITOR.dom.element( 'a' );
		element.setText( fileName );
	}

	element.setAttribute( 'id', id );

	return element;
}

// Takes care of uploading the file object using XHR
function uploadFile(editor, file, id, fileName)
{
	var xhr = new XMLHttpRequest(),
		isImage = (/\.(jpg|jpeg|gif|png)$/i).test( fileName ),
		attribute,
		uploadUrl;

	if ( (/\.(jpg|jpeg|gif|png)$/i).test( fileName ) )
	{
		attribute = 'src';
		uploadUrl = editor.config.filebrowserImageUploadUrl;
	}
	else
	{
		attribute = 'href';
		uploadUrl = editor.config.filebrowserUploadUrl;
	}

	// Upload the file
	xhr.open("POST", uploadUrl + '&CKEditor=' + editor.name + '&CKEditorFuncNum=2&langCode=' + editor.langCode );
	xhr.onload = function() {
		// Upon finish, get the url and update the file
		var parts = xhr.responseText.match(/2,\s*("|')(.*?)\1,\s*\1(.*?)\1/),
			url = parts[2],
			msg = parts[3],
			el = editor.document.getById( id );

		editor.fire('endDirectUpload', { name: fileName, ok: (!!url) } );

		if ( url )
		{
			url = url.replace(/\\'/g, "'");
			if (el.$.nodeName == 'DIV')
			{
				// create the final img, getting rid of the fake div
				var img = new CKEDITOR.dom.element( 'img' );
				img.data( 'cke-saved-' + attribute, url);
				img.setAttribute( attribute, url);
				// wait to replace until the image is loaded to prevent flickering
				img.on('load', function() { img.replace( el );});

				return;
			}
			el.data( 'cke-saved-' + attribute, url);
			el.setAttribute( attribute, url);
			el.removeAttribute( 'id' );
		}
		else
		{
			el.remove();
			alert( msg );
		}
	};

	// nice progress effect
	var target = xhr.upload;
	var rect = editor.document.$.getElementById("rect" + id);
	if ( target && rect )
	{
		target.onprogress = function( evt )
		{
			if ( evt.lengthComputable )
			{
				rect.setAttribute("width", (100*evt.loaded/evt.total).toFixed(2) + "%");
			}
		};
	}

	editor.fire('startDirectUpload', { name: fileName});

	var formdata = new FormData();
	formdata.append( 'upload', file, fileName );
	xhr.send( formdata );
}

// Show a grayscale version of the image that animates toward the full color version
function createSVGAnimation( file, id, editor )
{
	var element = new CKEDITOR.dom.element( 'div' );
		div = element.$;
	div.style.display = 'inline-block';

	var theURL = window.URL || window.webkitURL;
	if ( !theURL )
		return element;

	var doc = editor.document.$,
		svg = doc.createElementNS("http://www.w3.org/2000/svg", "svg");
	svg.setAttribute( 'id' , 'svg' + id);

	// just to find out the image dimensions as they are needed for the svg block
	var img = doc.createElement( 'img' );
	img.onload = function(e) {
		theURL.revokeObjectURL( this.src );
		this.onload = null;

		// in IE it's inserted with the HTML, so we can't reuse the svg object from
		var svg = doc.getElementById('svg' + id);
		svg.setAttribute("width", this.width + 'px');
		svg.setAttribute("height", this.height + 'px');
	};
	img.src = theURL.createObjectURL( file );

	div.appendChild(svg);

	var filter = doc.createElementNS("http://www.w3.org/2000/svg", "filter");
	filter.setAttribute("id", "SVGdesaturate");
	svg.appendChild(filter);

	var feColorMatrix = doc.createElementNS("http://www.w3.org/2000/svg", "feColorMatrix");
	feColorMatrix.setAttribute("type", "saturate");
	feColorMatrix.setAttribute("values", "0");
	filter.appendChild(feColorMatrix);

	var clipPath = doc.createElementNS("http://www.w3.org/2000/svg", "clipPath");
	clipPath.setAttribute("id", "SVGprogress" + id);
	svg.appendChild(clipPath);

	var rect = doc.createElementNS("http://www.w3.org/2000/svg", "rect");
	rect.setAttribute("id", "rect" + id);
	rect.setAttribute("width", "0");
	rect.setAttribute("height", "100%");
	clipPath.appendChild(rect);

	var image = doc.createElementNS("http://www.w3.org/2000/svg", "image");
	image.setAttribute("width", "100%");
	image.setAttribute("height", "100%");

	image.setAttributeNS('http://www.w3.org/1999/xlink','href', theURL.createObjectURL( file ));
	var loaded = function( e ) {
		theURL.revokeObjectURL( image.getAttributeNS('http://www.w3.org/1999/xlink','href') );
		image.removeEventListener( "load", loaded, false);
	};
	image.addEventListener( "load", loaded, false);


	var image2 = image.cloneNode(true);
	image.setAttribute("filter", "url(#SVGdesaturate)");
	image.style.opacity="0.5";

	svg.appendChild(image);

	image2.setAttribute("clip-path", "url(#SVGprogress" + id + ")");
	svg.appendChild(image2);

	return element;
}
})();

/**
 * Fired when file starts being uploaded by the "imagepaste" plugin
 * @name CKEDITOR.editor#startDirectUpload
 * @event
 * @param {String} name The file name.
 */

/**
 * Fired when file upload finishes on the "imagepaste" plugin
 * @name CKEDITOR.editor#endDirectUpload
 * @event
 * @param {String} name The file name.
 * @param {Boolean} ok Whether the file has been correctly uploaded or not
 */
