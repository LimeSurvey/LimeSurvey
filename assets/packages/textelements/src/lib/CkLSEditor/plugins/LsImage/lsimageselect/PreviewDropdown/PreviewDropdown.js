import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import View from "@ckeditor/ckeditor5-ui/src/view";

class ImageView extends View {
    constructor( locale ) {
        super( locale );

        const bind = this.bindTemplate;

        this.setTemplate( {
            tag: 'image',
            attributes: {
                src: [
                    bind.to( 'imageSrc' )
                ],
                class: [
                    'img-responsive',
                    bind.to( 'elementClass' )
                ]
            },
        } );
    }
}

export default class PreviewDropdownButtonView extends ButtonView {
    /**
	 * @inheritDoc
	 */
    constructor( locale ) {
        super(locale);
        this.set( 'imageSrc' );
        this.set( 'imageClass' );
        this.imageView = this._createImageView();
    }

    /**
	 * @inheritDoc
	 */
	render() {
        super.render();
        this.children.add( this.imageView );
    }

    _createImageView() {
        const imageView = new ImageView();
        imageView.bind('imageSrc').to(this.imageSrc)
        imageView.bind('elementClass').to(this.imageClass)
    }
}

