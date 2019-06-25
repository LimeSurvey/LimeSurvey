import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

import { toWidget, viewToModelPositionOutsideModelElement } from '@ckeditor/ckeditor5-widget/src/utils';
import Widget from '@ckeditor/ckeditor5-widget/src/widget';

import LsReplacementsCommand from './LsReplacementsCommand';

export default class LsReplacementsEditing extends Plugin {
    
    static get requires() {
        return [ Widget ];
    }

    init() {
        console.log( 'LsReplacementEditing#init() got called' );
        this._defineSchema();
        this._defineConverters();

        this.editor.commands.add( 'expression', new LsReplacementsCommand( this.editor ) );
        this.editor.editing.mapper.on(
            'viewToModelPosition',
            viewToModelPositionOutsideModelElement( this.editor.model, viewElement => viewElement.is( 'x-xpr' ) )
        );
    }

    _defineSchema() {
        const schema = this.editor.model.schema;
        schema.register( 'expression', {
            allowWhere: '$text',
            isInline: true,
            isObject: true,
            allowAttributes: ['name', 'type']
        });
    }

    _defineConverters() {
        const conversion = this.editor.conversion;

        conversion.for( 'upcast' ).elementToElement({
            view: {
                name: 'x-xpr',
            },
            model: ( viewElement, modelWriter ) => {
                const name = viewElement.getChild( 0 ).data.slice( 1, -1 );
                const type = viewElement.getAttribute('type');
                return modelWriter.createElement( 'expression', { name, type } );
            }
        });

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'expression',
            view: ( modelItem, viewWriter ) => {
                const widgetElement = this._createExpressionView( modelItem, viewWriter );
                return toWidget( widgetElement, viewWriter );
            }
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'expression',
            view: this._createExpressionView
        });
    }

    _createExpressionView( modelItem, viewWriter ) {
        const name = modelItem.getAttribute( 'name' );
        const type = modelItem.getAttribute( 'type' );
        const expressionView = viewWriter.createContainerElement( 'x-xpr', { type });
        viewWriter.insert( viewWriter.createPositionAt( expressionView, 0 ), viewWriter.createText('{' + name + '}'));
        return expressionView;
    }

}