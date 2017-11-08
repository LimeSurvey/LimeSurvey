CKEDITOR.plugins.add( 'html5video', {
    requires: 'widget',
    lang: 'de,en,eu,es,ru,uk,fr',
    icons: 'html5video',
    init: function( editor ) {
        editor.widgets.add( 'html5video', {
            button: editor.lang.html5video.button,
            template: '<div class="ckeditor-html5-video"></div>',
            /*
             * Allowed content rules (http://docs.ckeditor.com/#!/guide/dev_allowed_content_rules):
             *  - div-s with text-align,float,margin-left,margin-right inline style rules and required ckeditor-html5-video class.
             *  - video tags with src, controls, width and height attributes.
             */
            allowedContent: 'div[data-responsive](!ckeditor-html5-video){text-align,float,margin-left,margin-right}; video[src,controls,autoplay,width, height]{max-width,height};',
            requiredContent: 'div(ckeditor-html5-video); video[src,controls];',
            upcast: function( element ) {
                return element.name === 'div' && element.hasClass( 'ckeditor-html5-video' );
            },
            dialog: 'html5video',
            init: function() {
                var src = '';
                var autoplay = '';
                var align = this.element.getStyle( 'text-align' );

                var width = '';
                var height = '';

                // If there's a child (the video element)
                if ( this.element.getChild( 0 ) ) {
                    // get it's attributes.
                    src = this.element.getChild( 0 ).getAttribute( 'src' );
                    width = this.element.getChild( 0 ).getAttribute( 'width' );
                    height = this.element.getChild( 0 ).getAttribute( 'height' );
                    autoplay = this.element.getChild( 0 ).getAttribute( 'autoplay' );
										responsive = this.element.getAttribute( 'data-responsive' );
                }

                if ( src ) {
                    this.setData( 'src', src );

                    if ( align ) {
                        this.setData( 'align', align );
                    } else {
                        this.setData( 'align', 'none' );
                    }

                    if ( width ) {
                        this.setData( 'width', width );
                    }

                    if ( height ) {
                        this.setData( 'height', height );
                    }

                    if ( autoplay ) {
                        this.setData( 'autoplay', 'yes' );
                    }

                    if ( responsive ) {
                        this.setData( 'responsive', responsive );
                    }
                }
            },
            data: function() {
                // If there is an video source
                if ( this.data.src ) {
                    // and there isn't a child (the video element)
                    if ( !this.element.getChild( 0 ) ) {
                        // Create a new <video> element.
                        var videoElement = new CKEDITOR.dom.element( 'video' );
                        // Set the controls attribute.
                        videoElement.setAttribute( 'controls', 'controls' );
                        // Append it to the container of the plugin.
                        this.element.append( videoElement );
                    }
                    this.element.getChild( 0 ).setAttribute( 'src', this.data.src );
                    if (this.data.width) this.element.getChild( 0 ).setAttribute( 'width', this.data.width );
                    if (this.data.height) this.element.getChild( 0 ).setAttribute( 'height', this.data.height );

                    if ( this.data.responsive ) {
                            this.element.setAttribute("data-responsive", this.data.responsive);
                            this.element.getChild( 0 ).setStyle( 'max-width', '100%' );
                            this.element.getChild( 0 ).setStyle( 'height', 'auto' );
                    } else {
                            this.element.getChild( 0 ).removeStyle( 'max-width' );
                            this.element.getChild( 0 ).removeStyle( 'height' );
                    }
                }

                this.element.removeStyle( 'float' );
                this.element.removeStyle( 'margin-left' );
                this.element.removeStyle( 'margin-right' );

                if ( this.data.align === 'none' ) {
                    this.element.removeStyle( 'text-align' );
                } else {
                    this.element.setStyle( 'text-align', this.data.align );
                }

                if ( this.data.align === 'left' ) {
                    this.element.setStyle( 'float', this.data.align );
                    this.element.setStyle( 'margin-right', '10px' );
                } else if ( this.data.align === 'right' ) {
                    this.element.setStyle( 'float', this.data.align );
                    this.element.setStyle( 'margin-left', '10px' );
                }

                if ( this.element.getChild( 0 ) ) {
                    if ( this.data.autoplay === 'yes' ) {
                        this.element.getChild( 0 ).setAttribute( 'autoplay', 'autoplay' );
                    } else {
                        this.element.getChild( 0 ).removeAttribute( 'autoplay' );
                    }
                }
            }
        } );

        if ( editor.contextMenu ) {
            editor.addMenuGroup( 'html5videoGroup' );
            editor.addMenuItem( 'html5videoPropertiesItem', {
                label: editor.lang.html5video.videoProperties,
                icon: 'html5video',
                command: 'html5video',
                group: 'html5videoGroup'
            });

            editor.contextMenu.addListener( function( element ) {
                if ( element &&
                     element.getChild( 0 ) &&
                     element.getChild( 0 ).hasClass &&
                     element.getChild( 0 ).hasClass( 'ckeditor-html5-video' ) ) {
                    return { html5videoPropertiesItem: CKEDITOR.TRISTATE_OFF };
                }
            });
        }

        CKEDITOR.dialog.add( 'html5video', this.path + 'dialogs/html5video.js' );
    }
} );
