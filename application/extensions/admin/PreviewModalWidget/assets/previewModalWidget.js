"use strict";

// NB: The class needs to be defined globally.
var PreviewModalScript;
if (typeof PreviewModalScript === 'function') {
  // Do nothing, already loaded with pjax elsewhere.
} else {
  PreviewModalScript = class {
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
              selectedClass: '',
              option: false,
              debugString: 'Key: ',
              debug: false,
              secondaryInputElement: null
          };

          const toBeEvaluated = ['onUpdate', 'onReady', 'onModalClose', 'onModalOpen', 'dataFilter', 'onGetDetails'];
          $.each(transOptions, function(key,val){
              if(toBeEvaluated.indexOf(key) > -1){
                  transOptions[key] = new Function(...transOptions[key]);
              }
          });

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
        //   $(`#selector__${this.widgetsJsName}--buttonText`).html(`${itemData.title} ${this.getForDebug(itemData.key)}`);
          $(`#selector__${this.widgetsJsName}--buttonText`).val(`${itemData.title} ${this.options.debugString} ${itemData.key}`);
          $(`#selector__${this.widgetsJsName}-detailPage`).html(this.options.onGetDetails(itemData.itemArray.detailpage, itemData));
          this.inputItem.val(itemData.key);
          this.options.option = itemData.itemArray;
          this.options.value = itemData.key;
          this.options.theme = itemData.itemArray.name;
      }

      /**
       * triggered by clicking on an item in the selector
       */
      selectItemClick (ev){
          console.ls.log('CURRENT SELECTED', $(ev.currentTarget));
          $(`.selector__Item--select-${this.widgetsJsName}`).removeClass('mark-as-selected');
          $(ev.currentTarget).addClass('mark-as-selected');
          const itemData = $(ev.currentTarget).data('item-value');
          this.selectItem(itemData);
      }

      /**
       * Workaround for the crazy person to use '*' as the short for a question type
       */
      preSelectFromValue (value){
          value = value || this.inputItem.val() || this.options.value;
          return $(`.selector__Item--select-${this.widgetsJsName}[data-key='${value.toString().trim()}']`);
      }

      /**
       * event triggered when the modal opens
       */
      onModalShown (){

          const selectedItem = this.preSelectFromValue();

          if(selectedItem) {
              $(selectedItem).addClass('mark-as-selected');
              $(selectedItem).closest('div.panel-collapse').addClass('in');
          }
          this.options.onModalOpen();
      }
      /**
       * event triggered when the modal closes
       */
      onModalClosed (){
          $(this.modalItem).find('.panel-collapse.collapse').each((i, item) => {
              $(item).removeClass('in');
          });
          this.options.onModalClose();
      }
      /**
       * bind to all necessary events
       */
      bind() {
          
          if(this.options.secondaryInputElement != null) {
              this.options.value = $(this.options.secondaryInputElement).val();
              
              $(this.options.secondaryInputElement).off('change.previewModal');
              $(this.options.secondaryInputElement).on('change.previewModal', (e) => { 
                  this.selectItemClick(this.preSelectFromValue($(e.currentTarget).val()));                
              });
          }

          if(/modal/.test(this.options.viewType)){
              $(this.modalItem).on('hide.bs.modal', ()=>{ this.onModalClosed();});
              $(this.modalItem).on('shown.bs.modal', ()=>{ this.onModalShown();});
              $(`.selector__Item--select-${this.widgetsJsName}:not(.disabled)`).on('click', (ev)=>{this.selectItemClick(ev);});
              $(`#selector__select-this-${this.widgetsJsName}`).on('click', () => {
                  this.options.onUpdate(this.options.value, this.options.theme, this.options.option);
                  this.modalItem.modal('hide');
              });
          } else {
              
              $('#in_survey_common, #in_survey_common_action').off('change.previewModal');
              $('#in_survey_common, #in_survey_common_action').on('change.previewModal', `#${this.widgetsJsName}`, (e) => {
                  var target = $(e.currentTarget);
                  var option = target.find("option:selected");
                  console.log('option.data', option.data);
                  this.options.onUpdate(target.val(), option.data('theme'));
              });
          }
          
          this.options.onReady(this);
      }
  };
}
