'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var PreviewModalScript = function () {
    function PreviewModalScript(widgetsJsName, transOptions) {
        var _this = this;

        _classCallCheck(this, PreviewModalScript);

        this.widgetsJsName = widgetsJsName;
        this.modalItem = $('#selector__' + this.widgetsJsName + '-modal');
        //Define default settings 
        var defaultSettings = {
            onUpdate: function onUpdate() {
                $('#' + _this.widgetsJsName).trigger('change');
            },
            onReady: function onReady() {},
            onModalClose: function onModalClose() {},
            onModalOpen: function onModalOpen() {},
            dataFilter: function dataFilter() {},
            onGetImage: function onGetImage(curImagePath) {
                return curImagePath;
            },
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


    _createClass(PreviewModalScript, [{
        key: 'getImage',
        value: function getImage(itemData) {
            var self = this;
            if (itemData.itemArray.images) {
                return $.map(itemData.itemArray.images, function (combined, itrt, image) {
                    return '<img src="' + self.options.onGetImage(image) + '" />';
                }).join('\n');
            }
            return '<img src="' + self.options.onGetImage(self.options.getImageUrl + '/screenshots/' + itemData.key + '.png') + '" />';
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
         * triggered by clicking on an item in the selector
         */

    }, {
        key: 'selectItem',
        value: function selectItem(ev) {
            console.ls.log(ev);
            var itemData = $(ev.currentTarget).data('item-value');
            $('#selector__' + this.widgetsJsName + '-currentSelected').html(itemData.title);
            $('#selector__' + this.widgetsJsName + '--buttonText').html(itemData.title + ' ' + this.getForDebug(itemData.key));
            $('#selector__' + this.widgetsJsName + '-selectedImage').html(this.getImage(itemData));
            $('.selector__Item--select-' + this.widgetsJsName).removeClass('mark-as-selected');
            $(this).addClass('mark-as-selected');
            $('#' + this.widgetsJsName).val(itemData.value);
            this.options.onUpdate();
        }
    }, {
        key: 'onModalShown',

        /**
         * event triggered when the modal opens
         */
        value: function onModalShown() {
            $('#selector__' + this.widgetsJsName + '-Item--' + this.options.selectedClass).addClass('mark-as-selected').trigger('click').closest('div.panel-collapse').addClass('in');
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
            var _this2 = this;

            var self = this;
            $(this.modalItem).on('hide.bs.modal', function () {
                _this2.onModalClosed();
            });
            $(this.modalItem).on('show.bs.modal', function () {
                _this2.onModalShown();
            });
            $('.selector__Item--select-' + this.widgetsJsName).on('click', function (ev) {
                _this2.selectItem(ev);
            });
            $('#selector__select-this-' + this.widgetsJsName).on('click', function () {
                _this2.options.onUpdate();
                _this2.modalItem.modal('hide');
            });
        }
    }]);

    return PreviewModalScript;
}();
