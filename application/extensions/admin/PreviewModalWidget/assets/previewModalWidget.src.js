class PreviewModalScript {
    constructor(widgetsJsName, transOptions){
        this.widgetsJsName = widgetsJsName;
        this.modalItem = $(`#selector__${this.widgetsJsName}-modal`);
        this.inputItem = $(`#selector__${this.widgetsJsName}`);
        //Define default settings 
        const defaultSettings = {
            onUpdate: (value)=>{},
            onReady: () => {},
            onModalClose: () => {},
            onModalOpen: () => {},
            dataFilter: () => {},
            onGetImage: (curImagePath, itemData) => curImagePath,
            value: '',
            selectedClass: '',
            getImageUrl: '',
            debugString: 'Key: ',
            debug: false
        };

        const toBeEvaluated = ['onUpdate', 'onReady', 'onModalClose', 'onModalOpen', 'dataFilter', 'onGetImage'];
        $.each(transOptions, function(key,val){
            if(toBeEvaluated.indexOf(key) > -1){
                transOptions[key] = new Function(...transOptions[key]);
            }
        })

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
                return `<img src="${self.options.onGetImage(image, itemData)}" />`;
            })).join('\n');
        }
        return `<img src="${self.options.onGetImage(`${self.options.getImageUrl}/screenshots/${itemData.key}.png`, itemData)}" />`;
    };
    /**
     * Get the html snippet for the item data
     * @param string key 
     */
    getForDebug(key) {
        return this.options.debug ? `<em class="small">${this.options.debugString} ${key} </em>` : '';
    }
    /**
     * select an Item
     */
    selectItem (itemData){
        $(`#selector__${this.widgetsJsName}-currentSelected`).html(itemData.title);
        $(`#selector__${this.widgetsJsName}--buttonText`).html(`${itemData.title} ${this.getForDebug(itemData.key)}`);
        $(`#selector__${this.widgetsJsName}-selectedImage`).html(this.getImage(itemData));
        this.inputItem.val(itemData.key);
        this.inputItem.trigger('change');
        this.options.onUpdate(itemData.key);
    };
    /**
     * triggered by clicking on an item in the selector
     */
    selectItemClick (ev){
        console.ls.log("CURRENT SELECTED", $(ev.currentTarget));
        $(`.selector__Item--select-${this.widgetsJsName}`).removeClass('mark-as-selected');
        $(ev.currentTarget).addClass('mark-as-selected');
        const itemData = $(ev.currentTarget).data('item-value');
        this.selectItem(itemData);
    };

    /**
     * Workaround for the crazy person to use '*' as the short for a question type
     */
    preSelectFromValue (value){
        value = value || this.options.value;
        let selectedItem = null;
        if(/[^~!@\$%\^&\*\( \)\+=,\.\/';:"\?><\[\]\\\{\}\|`#]/.test(value)){
            selectedItem = $(`.selector__Item--select-${this.widgetsJsName}[data-selector=${value.trim()}]`);
        }
        if(selectedItem === null || selectedItem.length !== 1) {
            selectedItem = $(`.selector__Item--select-${this.widgetsJsName}[data-selector=${this.options.selectedClass.trim()}]`);
        }

        return selectedItem;
    }

    /**
     * event triggered when the modal opens
     */
    onModalShown (){
        const selectedItem = this.preSelectFromValue();
        console.log(selectedItem);
        $(selectedItem).trigger('click');
        $(selectedItem).closest('div.panel-collapse').addClass('in');
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
        if(/modal/.test(this.options.viewType)){
            $(this.modalItem).on('hide.bs.modal', ()=>{this.onModalClosed()});
            $(this.modalItem).on('show.bs.modal', ()=>{this.onModalShown()});
            $(`.selector__Item--select-${this.widgetsJsName}`).on('click', (ev)=>{this.selectItemClick(ev)});
            $(`#selector__select-this-${this.widgetsJsName}`).on('click', () => {
                this.options.onUpdate();
                this.modalItem.modal('hide');
            });
        }
    }
}