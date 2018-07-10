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
            onGetDetails: (curDetailPage, itemData) => curDetailPage,
            value: '',
            selectedClass: '',
            debugString: 'Key: ',
            debug: false
        };

        const toBeEvaluated = ['onUpdate', 'onReady', 'onModalClose', 'onModalOpen', 'dataFilter', 'onGetDetails'];
        $.each(transOptions, function(key,val){
            if(toBeEvaluated.indexOf(key) > -1){
                transOptions[key] = new Function(...transOptions[key]);
            }
        })

        this.options = $.extend({}, defaultSettings, transOptions);
    }

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
        $(`#selector__${this.widgetsJsName}-detailPage`).html(this.options.onGetDetails(itemData.itemArray.detailpage, itemData));
        this.inputItem.val(itemData.key);
        this.options.value = itemData.key;
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
            selectedItem = $(`.selector__Item--select-${this.widgetsJsName}[data-selector=${value.toString().trim()}]`);
        }
        if((selectedItem === null || selectedItem.length !== 1) && this.options.selectedClass != '') {
            selectedItem = $(`.selector__Item--select-${this.widgetsJsName}[data-selector=${this.options.selectedClass.toString().trim()}]`);
        }

        return selectedItem;
    }

    /**
     * event triggered when the modal opens
     */
    onModalShown (){

        const selectedItem = this.preSelectFromValue();
        if(selectedItem) {
            $(selectedItem).trigger('click');
            $(selectedItem).closest('div.panel-collapse').addClass('in');
        }
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
            $(`.selector__Item--select-${this.widgetsJsName}:not(.disabled)`).on('click', (ev)=>{this.selectItemClick(ev)});
            $(`#selector__select-this-${this.widgetsJsName}`).on('click', () => {
                this.options.onUpdate(this.options.value);
                this.modalItem.modal('hide');
            });
        }
    }
}