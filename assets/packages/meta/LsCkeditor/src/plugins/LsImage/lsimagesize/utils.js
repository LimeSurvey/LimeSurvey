import first from '@ckeditor/ckeditor5-utils/src/first';

export function modelToViewSizeAttribute(sizes) {
    return (evt, data, conversionApi) => {
        if (!conversionApi.consumable.consume(data.item, evt.name)) {
            return;
        }

        const newStyle = _parseClassName(data.attributeNewValue);
        const oldStyle = _parseClassName(data.attributeOldValue);

        const viewElement = conversionApi.mapper.toViewElement(data.item);

        if (oldStyle) {
            conversionApi.writer.removeClass(oldStyle, viewElement);
        }

        if (newStyle) {
            conversionApi.writer.addClass(newStyle, viewElement);
        }
    };
}

export function viewToModelSizeAttribute(sizes) {
    return ( evt, data, conversionApi ) => {
        if ( !data.modelRange ) {
            return;
        }

        const viewFigureElement = data.viewItem;
        const modelImageElement = first( data.modelRange.getItems() );

        if ( !conversionApi.schema.checkAttribute( modelImageElement, 'imageSize' ) ) {
            return;
        }

        sizes.forEach(size => {
            if ( conversionApi.consumable.consume( viewFigureElement, { classes: _parseClassName(size) } ) ) {
                conversionApi.writer.setAttribute( 'imageSize', size, modelImageElement );
            }
        });
        
    };
}

export function getImageSizes() {
    return [ '10','25','50','75', '100' ];
}

function _parseClassName(size) {
    return `lsImageSize--size--${size}`;
}
