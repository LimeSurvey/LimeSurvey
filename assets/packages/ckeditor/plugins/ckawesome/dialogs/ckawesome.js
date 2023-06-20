
CKEDITOR.dialog.add('ckawesomeDialog', function( editor ) {
	function getCKAwesomeIcons(selectList ){
	     var result = [];
	     var scriptUrl = editor.fontawesomePath;

	     $.ajax({
	        url: scriptUrl,
	        type: 'get',
	        dataType: 'html',
	        async: false,
	        success: function(response) {
	        	var excludeStyles = [".fa.",".fa",".fa-lg",".fa-2x",".fa-3x",".fa-4x",".fa-5x",".fa-fw",".fa-ul",".fa-ul>",".fa-li",".fa-border",".fa-pull-left",".fa-pull-right",".fa-spin",".fa-pulse",".fa-rotate-90",".fa-rotate-180",".fa-rotate-270",".fa-flip-horizontal",".fa-flip-vertical",".fa-stack",".fa-stack-1x",".fa-stack-2x",".fa-inverse"];
	        	var regxstyles = new RegExp(/\.[a-zA-Z_][\w-_]*[^\.\s\{#:\,;]/,"g" );
	        	var styles = response.match(regxstyles);
	        	styles.sort();
	        	$.each(styles, function( index, value ) {
	        		var xstart=value.substring(0, 3).substring(1);
	        		if (xstart != 'fa' || excludeStyles.indexOf(value) > 0){ return; }
	        		value = value.substring(1);
	        		selectList.add(value, value);
	        	})

	        },
	        error: function (jqXHR, exception) {
	            alert("Error loading Font Awesome css: \n" + scriptUrl);
	        },
	     });
	}

	function getSelectionOptions(selectList, start, inc, many){
	    var result = [];
	    var val = start;

	    result.push(start);

	    many = many > 0 ? many : 5;
	    for(var i = 0; i < many; i++){
	    	val += inc;
	    	result.push(val);
	    }

	    $.each(result, function( index, value ) {
	   		selectList.add(value, value);
	   	})
	}

	function formatCKAwesome (icon) {
	  if (!icon.id) { return icon.text; }
	  var text = icon.text.replace(/fa-|\.|\-/gi, " ");
	  var icon = $('<span class="ckawesome_options"><i class="fa ' + icon.element.value + ' fa-fw"></i> ' + text + "</span>");
	  return icon;
	};

    return {
        title: 'Insert CKAwesome',
        minWidth: 200,
        minHeight: 200,

        contents: [
            {
                id: 'options',
                label: 'Basic Settings',
                elements: [
                    {
					    type: 'select',
					    id: 'ckawesomebox',
					    label: 'Select font Awesome',
					    validate: CKEDITOR.dialog.validate.notEmpty( "Font Awesome field cannot be empty." ),
					    items: [[ editor.lang.common.notSet, '' ]],
					    onLoad: function () {
						   	getCKAwesomeIcons(this);
						   	var selectbx = $('#' + this.getInputElement().getAttribute('id'));
						   	$(selectbx).select2({ width: "100%", templateResult: formatCKAwesome, templateSelection: formatCKAwesome});
					    },
					    onShow: function(){
					    	var selectbx = $('#' + this.getInputElement().getAttribute('id'));
					    	$(selectbx).val('').trigger('change') ;
					    }
                    },
                    {
                        type: 'select',
                        id: 'textsize',
                        label: 'Select  size',
                        items: [[ editor.lang.common.notSet, '' ]],
                        onLoad: function (widget) {
                        	getSelectionOptions(this, 8, 1, 42);
                        }
                    },
                    {
                        type: "hbox",
                        padding: 0,
                        widths: ["80%", "20%"],
                        children: [
                            {
                                id: 'fontcolor',
                                type: 'text',
                                label: 'Select color',
                                onChange: function( element ) {
                                	var idEl = $('#' +this.getInputElement().getAttribute('id'));
                                	idEl.css("background-color", idEl.val());
                                },
                                onKeyUp: function( element ) {
                                	var idEl = $('#' + this.getInputElement().getAttribute('id'));
                                	idEl.css("background-color", idEl.val());
                                },
        					    onShow: function(){
        					    	var idEl = $('#' + this.getInputElement().getAttribute('id'));
                                	idEl.css("background-color", "");
        					    }
                            },
                            {
                                type: "button",
                                id: "fontcolorChooser",
                                "class": "colorChooser",
                                label: "Color",
                                style: "margin-left: 8px",
                                onLoad: function () {
                                    this.getElement().getParent().setStyle("vertical-align", "bottom")
                                },
                                onClick: function () {
                                    editor.getColorFromDialog(function (color) {
                                        color && this.getDialog().getContentElement("options", "fontcolor").setValue( color );
                                        this.focus()
                                    }, this)
                                }
                            }
                        ]
                    }
                ]
            },
        ],
        onOk: function() {
            var dialog = this;

            var cka = editor.document.createElement( 'span' );
            var cka_size = dialog.getValueOf( 'options', 'textsize' );
            var cka_color = dialog.getValueOf( 'options', 'fontcolor' );
            var cka_class = "fa fa-fw " + dialog.getValueOf( 'options', 'ckawesomebox' );
            var cka_style = ( cka_size != '' ? 'font-size: '+cka_size+'px;' : '' ) + ( cka_color != '' ? 'color: '+cka_color+';' : '' ) ;

            cka.setAttribute( 'class', cka_class );
            if ( cka_style ) cka.setAttribute( 'style', cka_style );

            editor.insertElement( cka );
        }
    };
});
