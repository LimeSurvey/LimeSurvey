CKEDITOR.dialog.add( 'audio', function ( editor )
{
	var lang = editor.lang.audio;

	function commitValue( audioNode, extraStyles )
	{
		var value=this.getValue();

		if ( !value && this.id=='id' )
			value = generateId();

		audioNode.setAttribute( this.id, value);

		if ( !value )
			return;
		switch( this.id )
		{
			case 'poster':
				extraStyles.backgroundImage = 'url(' + value + ')';
				break;
			case 'width':
				extraStyles.width = value + 'px';
				break;
			case 'height':
				extraStyles.height = value + 'px';
				break;
		}
	}

	function commitSrc( audioNode, extraStyles, audios )
	{
		var match = this.id.match(/(\w+)(\d)/),
			id = match[1],
			number = parseInt(match[2], 10);

		var audio = audios[number] || (audios[number]={});
		audio[id] = this.getValue();
	}

	function loadValue( audioNode )
	{
		if ( audioNode )
			this.setValue( audioNode.getAttribute( this.id ) );
		else
		{
			if ( this.id == 'id')
				this.setValue( generateId() );
		}
	}

	function loadSrc( audioNode, audios )
	{
		var match = this.id.match(/(\w+)(\d)/),
			id = match[1],
			number = parseInt(match[2], 10);

		var audio = audios[number];
		if (!audio)
			return;
		this.setValue( audio[ id ] );
	}

	function generateId()
	{
		var now = new Date();
		return 'audio' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();
	}

	// To automatically get the dimensions of the poster image
	var onImgLoadEvent = function()
	{
		// Image is ready.
		var preview = this.previewImage;
		preview.removeListener( 'load', onImgLoadEvent );
		preview.removeListener( 'error', onImgLoadErrorEvent );
		preview.removeListener( 'abort', onImgLoadErrorEvent );

		this.setValueOf( 'info', 'width', preview.$.width );
		this.setValueOf( 'info', 'height', preview.$.height );
	};

	var onImgLoadErrorEvent = function()
	{
		// Error. Image is not loaded.
		var preview = this.previewImage;
		preview.removeListener( 'load', onImgLoadEvent );
		preview.removeListener( 'error', onImgLoadErrorEvent );
		preview.removeListener( 'abort', onImgLoadErrorEvent );
	};

	return {
		title : lang.dialogTitle,
		minWidth : 400,
		minHeight : 200,

		onShow : function()
		{
			// Clear previously saved elements.
			this.fakeImage = this.audioNode = null;
			// To get dimensions of poster image
			this.previewImage = editor.document.createElement( 'img' );

			var fakeImage = this.getSelectedElement();
			if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'audio' )
			{
				this.fakeImage = fakeImage;

				var audioNode = editor.restoreRealElement( fakeImage ),
					audios = [],
					sourceList = audioNode.getElementsByTag( 'source', '' );
				if (sourceList.count()==0)
					sourceList = audioNode.getElementsByTag( 'source', 'cke' );

				for ( var i = 0, length = sourceList.count() ; i < length ; i++ )
				{
					var item = sourceList.getItem( i );
					audios.push( {src : item.getAttribute( 'src' ), type: item.getAttribute( 'type' )} );
				}

				this.audioNode = audioNode;

				this.setupContent( audioNode, audios );
			}
			else
				this.setupContent( null, [] );
		},

		onOk : function()
		{
			// If there's no selected element create one. Otherwise, reuse it
			var audioNode = null;
			if ( !this.fakeImage )
			{
				audioNode = CKEDITOR.dom.element.createFromHtml( '<cke:audio></cke:audio>', editor.document );
				audioNode.setAttributes(
					{
						controls : 'controls'
					} );
			}
			else
			{
				audioNode = this.audioNode;
			}

			var extraStyles = {}, audios = [];
			this.commitContent( audioNode, extraStyles, audios );

			var innerHtml = '', links = '',
				link = lang.linkTemplate || '',
				fallbackTemplate = lang.fallbackTemplate || '';
			for(var i=0; i<audios.length; i++)
			{
				var audio = audios[i];
				if ( !audio || !audio.src )
					continue;
				innerHtml += '<cke:source src="' + audio.src + '" type="' + audio.type + '" />';
				links += link.replace('%src%', audio.src).replace('%type%', audio.type);
			}
			audioNode.setHtml( innerHtml + fallbackTemplate.replace( '%links%', links ) );

			// Refresh the fake image.
			var newFakeImage = editor.createFakeElement( audioNode, 'cke_audio', 'audio', false );
			newFakeImage.setStyles( extraStyles );
			if ( this.fakeImage )
			{
				newFakeImage.replace( this.fakeImage );
				editor.getSelection().selectElement( newFakeImage );
			}
			else
			{
				// Insert it in a div
				var div = new CKEDITOR.dom.element( 'DIV', editor.document );
				editor.insertElement( div );
				div.append( newFakeImage );
			}
		},
		onHide : function()
		{
			if ( this.previewImage )
			{
				this.previewImage.removeListener( 'load', onImgLoadEvent );
				this.previewImage.removeListener( 'error', onImgLoadErrorEvent );
				this.previewImage.removeListener( 'abort', onImgLoadErrorEvent );
				this.previewImage.remove();
				this.previewImage = null;		// Dialog is closed.
			}
		},

		contents :
		[
			{
				id : 'info',
				elements :
				[
					{
						type : 'hbox',
						widths: [ '', '100px'],
						children : [
							{
								type : 'text',
								id : 'poster',
								label : lang.poster,
								commit : commitValue,
								setup : loadValue,
								onChange : function()
								{
									var dialog = this.getDialog(),
										newUrl = this.getValue();

									//Update preview image
									if ( newUrl.length > 0 )	//Prevent from load before onShow
									{
										dialog = this.getDialog();
										var preview = dialog.previewImage;

										preview.on( 'load', onImgLoadEvent, dialog );
										preview.on( 'error', onImgLoadErrorEvent, dialog );
										preview.on( 'abort', onImgLoadErrorEvent, dialog );
										preview.setAttribute( 'src', newUrl );
									}
								}
							},
							{
								type : 'button',
								id : 'browse',
								hidden : 'true',
								style : 'display:inline-block;margin-top:10px;',
								filebrowser :
								{
									action : 'Browse',
									target: 'info:poster',
									url: editor.config.filebrowserImageBrowseUrl || editor.config.filebrowserBrowseUrl
								},
								label : editor.lang.common.browseServer
							}]
					},
					{
						type : 'hbox',
						widths: [ '33%', '33%', '33%'],
						children : [
							{
								type : 'text',
								id : 'width',
								label : editor.lang.common.width,
								'default' : 400,
								validate : CKEDITOR.dialog.validate.notEmpty( lang.widthRequired ),
								commit : commitValue,
								setup : loadValue
							},
							{
								type : 'text',
								id : 'height',
								label : editor.lang.common.height,
								'default' : 300,
								validate : CKEDITOR.dialog.validate.notEmpty(lang.heightRequired ),
								commit : commitValue,
								setup : loadValue
							},
							{
								type : 'text',
								id : 'id',
								label : 'Id',
								commit : commitValue,
								setup : loadValue
							}
								]
					},
					{
						type : 'hbox',
						widths: [ '', '100px', '75px'],
						children : [
							{
								type : 'text',
								id : 'src0',
								label : lang.sourceaudio,
								commit : commitSrc,
								setup : loadSrc
							},
							{
								type : 'button',
								id : 'browse',
								hidden : 'true',
								style : 'display:inline-block;margin-top:10px;',
								filebrowser :
								{
									action : 'Browse',
									target: 'info:src0',
									url: editor.config.filebrowserAudioBrowseUrl || editor.config.filebrowserBrowseUrl
								},
								label : editor.lang.common.browseServer
							},
							{
								id : 'type0',
								label : lang.sourceType,
								type : 'select',
								'default' : 'audio/mp3',
								items :
								[
									[ 'MP3', 'audio/mp3' ],
									[ 'WAV', 'audio/wav' ]
								],
								commit : commitSrc,
								setup : loadSrc
							}]
					},

					{
						type : 'hbox',
						widths: [ '', '100px', '75px'],
						children : [
							{
								type : 'text',
								id : 'src1',
								label : lang.sourceaudio,
								commit : commitSrc,
								setup : loadSrc
							},
							{
								type : 'button',
								id : 'browse',
								hidden : 'true',
								style : 'display:inline-block;margin-top:10px;',
								filebrowser :
								{
									action : 'Browse',
									target: 'info:src1',
									url: editor.config.filebrowserAudioBrowseUrl || editor.config.filebrowserBrowseUrl
								},
								label : editor.lang.common.browseServer
							},
							{
								id : 'type1',
								label : lang.sourceType,
								type : 'select',
								'default':'audio/wav',
								items :
								[
									[ 'MP3', 'audio/mp3' ],
									[ 'WAV', 'audio/wav' ]
								],
								commit : commitSrc,
								setup : loadSrc
							}]
					}
				]
			}

		]
	};
} );
