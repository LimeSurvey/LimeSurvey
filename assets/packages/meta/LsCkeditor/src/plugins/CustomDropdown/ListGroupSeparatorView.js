import ListSeparatorView from '@ckeditor/ckeditor5-ui/src/list/listseparatorview';
import View from "@ckeditor/ckeditor5-ui/src/view";
import uid from '@ckeditor/ckeditor5-utils/src/uid';

export default class ListGroupSeparatorView extends ListSeparatorView {
    constructor(locale) {
        super(locale);
        const ariaLabelUid = uid();

        this.set('label');
        this.children = this.createCollection();
        this.labelView = this._createLabelView(ariaLabelUid);

        this.setTemplate({
            tag: 'li',
            attributes: {
                class: [
                    'ck',
                    'ck-listgroup__separator'
                ]
            },
            children: this.children
        });
        
        this.children.add(this.labelView);
    }

    _createLabelView(ariaLabelUid) {
        const labelView = new View();
        const bind = this.bindTemplate;

        labelView.setTemplate({
            tag: 'span',

            attributes: {
                class: ['ck','ck-groupseparator__label'],
                id: `ck-editor__aria-label_${ ariaLabelUid }`,
            },

            children: [{
                text: bind.to('label')
            }]
        });

        return labelView;
    }
}
