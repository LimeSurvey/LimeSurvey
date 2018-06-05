class PreviewModalScript {
    constructor(widgetsJsName, transOptions){
        this.widgetsJsName = widgetsJsName;
        this.modalItem = $(`#selector__${this.widgetsJsName}-modal`);
        //Define default settings 
        const defaultSettings = {
            onUpdate: ()=>{
                $(`#${this.widgetsJsName}`).trigger('change');
            },
            onReady: () => {},
            onModalClose: () => {},
            onModalOpen: () => {},
            dataFilter: () => {},
            onGetImage: (curImagePath) => curImagePath,
            value: '',
            selectedClass: '',
            getImageUrl: '',
            debugString: 'Key: ',
            debug: false
        };

        this.options = $.extend({}, defaultSettings, transOptions);
    }
    /**
     * Gets the image for the preview
     * This is either done by an attribute of the items object, or by using a default path
     * @param object itemData 
     */
    getImage(itemData) {
        const self = this;
        if(itemData.itemArray.images) {
            return ($.map(itemData.itemArray.images, (combined, itrt, image) => {
                return `<img src="${self.options.onGetImage(image)}" />`;
            })).join('\n');
        }
        return `<img src="${self.options.onGetImage(`${self.options.getImageUrl}/screenshots/${itemData.key}.png`)}" />`;
    };
    /**
     * Get the html snippet for the item data
     * @param string key 
     */
    getForDebug(key) {
        return this.options.debug ? `<em class="small">${this.options.debugString} ${key} </em>` : '';
    }
    /**
     * triggered by clicking on an item in the selector
     */
    selectItem (ev){
        console.ls.log(ev)
        const itemData = $(ev.currentTarget).data('item-value');
        $(`#selector__${this.widgetsJsName}-currentSelected`).html(itemData.title);
        $(`#selector__${this.widgetsJsName}--buttonText`).html(`${itemData.title} ${this.getForDebug(itemData.key)}`);
        $(`#selector__${this.widgetsJsName}-selectedImage`).html(this.getImage(itemData));
        $(`.selector__Item--select-${this.widgetsJsName}`).removeClass('mark-as-selected');
        $(this).addClass('mark-as-selected');
        $(`#${this.widgetsJsName}`).val(itemData.value);
        this.options.onUpdate();
    };
    /**
     * event triggered when the modal opens
     */
    onModalShown (){
        $(`#selector__${this.widgetsJsName}-Item--${this.options.selectedClass}`)
            .addClass('mark-as-selected')
            .trigger('click')
            .closest('div.panel-collapse')
            .addClass('in');
            this.options.onModalOpen();
        };
    /**
     * event triggered when the modal closes
     */
    onModalClosed (){
            this.options.onModalClose();
    };
    /**
     * bind to all necessary events
     */
    bind() {
        const self = this;
        $(this.modalItem).on('hide.bs.modal', ()=>{this.onModalClosed()});
        $(this.modalItem).on('show.bs.modal', ()=>{this.onModalShown()});
        $(`.selector__Item--select-${this.widgetsJsName}`).on('click', (ev)=>{this.selectItem(ev)});
        $(`#selector__select-this-${this.widgetsJsName}`).on('click', () => {
            this.options.onUpdate();
            this.modalItem.modal('hide');
        });
    }
}