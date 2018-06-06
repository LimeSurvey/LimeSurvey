'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var PreviewModalScript = function () {
    function PreviewModalScript(widgetsJsName, transOptions) {
        _classCallCheck(this, PreviewModalScript);

        this.widgetsJsName = widgetsJsName;
        this.modalItem = $('#selector__' + this.widgetsJsName + '-modal');
        this.inputItem = $('#selector__' + this.widgetsJsName);
        //Define default settings 
        var defaultSettings = {
            onUpdate: function onUpdate(value) {},
            onReady: function onReady() {},
            onModalClose: function onModalClose() {},
            onModalOpen: function onModalOpen() {},
            dataFilter: function dataFilter() {},
            onGetImage: function onGetImage(curImagePath, itemData) {
                return curImagePath;
            },
            value: '',
            selectedClass: '',
            getImageUrl: '',
            debugString: 'Key: ',
            debug: false
        };

        var toBeEvaluated = ['onUpdate', 'onReady', 'onModalClose', 'onModalOpen', 'dataFilter', 'onGetImage'];
        $.each(transOptions, function (key, val) {
            if (toBeEvaluated.indexOf(key) > -1) {
                transOptions[key] = new (Function.prototype.bind.apply(Function, [null].concat(_toConsumableArray(transOptions[key]))))();
            }
        });

        this.options = $.extend({}, defaultSettings, transOptions);
    }
    /**
     * Gets the image for the preview
     * This is either done by an attribute of the items object, or by using a default path
     * @param object itemData 
     */


    _createClass(PreviewModalScript, [{
        key: 'getImage',
        value: function getImage(itemData) {
            var self = this;
            if (itemData.itemArray.images) {
                return $.map(itemData.itemArray.images, function (combined, itrt, image) {
                    return '<img src="' + self.options.onGetImage(image, itemData) + '" />';
                }).join('\n');
            }
            return '<img src="' + self.options.onGetImage(self.options.getImageUrl + '/screenshots/' + itemData.key + '.png', itemData) + '" />';
        }
    }, {
        key: 'getForDebug',

        /**
         * Get the html snippet for the item data
         * @param string key 
         */
        value: function getForDebug(key) {
            return this.options.debug ? '<em class="small">' + this.options.debugString + ' ' + key + ' </em>' : '';
        }
        /**
         * select an Item
         */

    }, {
        key: 'selectItem',
        value: function selectItem(itemData) {
            $('#selector__' + this.widgetsJsName + '-currentSelected').html(itemData.title);
            $('#selector__' + this.widgetsJsName + '--buttonText').html(itemData.title + ' ' + this.getForDebug(itemData.key));
            $('#selector__' + this.widgetsJsName + '-selectedImage').html(this.getImage(itemData));
            this.inputItem.val(itemData.key);
            this.inputItem.trigger('change');
            this.options.onUpdate(itemData.key);
        }
    }, {
        key: 'selectItemClick',

        /**
         * triggered by clicking on an item in the selector
         */
        value: function selectItemClick(ev) {
            console.ls.log("CURRENT SELECTED", $(ev.currentTarget));
            $('.selector__Item--select-' + this.widgetsJsName).removeClass('mark-as-selected');
            $(ev.currentTarget).addClass('mark-as-selected');
            var itemData = $(ev.currentTarget).data('item-value');
            this.selectItem(itemData);
        }
    }, {
        key: 'preSelectFromValue',


        /**
         * Workaround for the crazy person to use '*' as the short for a question type
         */
        value: function preSelectFromValue(value) {
            value = value || this.options.value;
            var selectedItem = null;
            if (/[^~!@\$%\^&\*\( \)\+=,\.\/';:"\?><\[\]\\\{\}\|`#]/.test(value)) {
                selectedItem = $('.selector__Item--select-' + this.widgetsJsName + '[data-selector=' + value.trim() + ']');
            }
            if (selectedItem === null || selectedItem.length !== 1) {
                selectedItem = $('.selector__Item--select-' + this.widgetsJsName + '[data-selector=' + this.options.selectedClass.trim() + ']');
            }

            return selectedItem;
        }

        /**
         * event triggered when the modal opens
         */

    }, {
        key: 'onModalShown',
        value: function onModalShown() {
            var selectedItem = this.preSelectFromValue();
            console.log(selectedItem);
            $(selectedItem).trigger('click');
            $(selectedItem).closest('div.panel-collapse').addClass('in');
            this.options.onModalOpen();
        }
    }, {
        key: 'onModalClosed',

        /**
         * event triggered when the modal closes
         */
        value: function onModalClosed() {
            this.options.onModalClose();
        }
    }, {
        key: 'bind',

        /**
         * bind to all necessary events
         */
        value: function bind() {
            var _this = this;

            if (/modal/.test(this.options.viewType)) {
                $(this.modalItem).on('hide.bs.modal', function () {
                    _this.onModalClosed();
                });
                $(this.modalItem).on('show.bs.modal', function () {
                    _this.onModalShown();
                });
                $('.selector__Item--select-' + this.widgetsJsName).on('click', function (ev) {
                    _this.selectItemClick(ev);
                });
                $('#selector__select-this-' + this.widgetsJsName).on('click', function () {
                    _this.options.onUpdate();
                    _this.modalItem.modal('hide');
                });
            }
        }
    }]);

    return PreviewModalScript;
}();
