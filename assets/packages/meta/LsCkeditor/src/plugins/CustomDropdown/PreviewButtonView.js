import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import View from "@ckeditor/ckeditor5-ui/src/view";


class ImageView extends View {
    constructor( locale ) {
        super( locale );

        const bind = this.bindTemplate;

        this.setTemplate( {
            tag: 'img',
            attributes: {
                src: [
                    bind.to( 'imageSrc' )
                ],
                class: [
                    'img-responsive',
                    'lsimageSelect--dropdown-button-image',
                    bind.to( 'imageClass' )
                ],
                hash: [
                    bind.to( 'imageHash' )
                ]
            },
        } );
    }

    render() {
		super.render();
    }
    
    focus() {
		this.element.focus();
    }
}

export default class PreviewButtonView extends ButtonView {
    /**
	 * @inheritDoc
	 */
    constructor( locale ) {
        super(locale);

        this.set( 'imageSrc' );
        this.set( 'imageClass' );
        this.set( 'imageHash' );
        
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
        imageView.bind('imageSrc').to(this,'imageSrc');
        imageView.bind('imageClass').to(this,'imageClass');
        imageView.bind('imageHash').to(this,'imageHash');
        return imageView;
    }
}
