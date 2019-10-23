"use strict";

function isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _construct(Parent, args, Class) { if (isNativeReflectConstruct()) { _construct = Reflect.construct; } else { _construct = function _construct(Parent, args, Class) { var a = [null]; a.push.apply(a, args); var Constructor = Function.bind.apply(Parent, a); var instance = new Constructor(); if (Class) _setPrototypeOf(instance, Class.prototype); return instance; }; } return _construct.apply(null, arguments); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var PreviewModalScript =
/*#__PURE__*/
function () {
  function PreviewModalScript(widgetsJsName, transOptions) {
    _classCallCheck(this, PreviewModalScript);

    this.widgetsJsName = widgetsJsName;
    this.modalItem = $("#selector__".concat(this.widgetsJsName, "-modal"));
    this.inputItem = $("#selector__".concat(this.widgetsJsName)); //Define default settings 

    var defaultSettings = {
      onUpdate: function onUpdate(value) {},
      onReady: function onReady() {},
      onModalClose: function onModalClose() {},
      onModalOpen: function onModalOpen() {},
      dataFilter: function dataFilter() {},
      onGetDetails: function onGetDetails(curDetailPage, itemData) {
        return curDetailPage;
      },
      selectedClass: '',
      option: false,
      debugString: 'Key: ',
      debug: false,
      secondaryInputElement: null
    };
    var toBeEvaluated = ['onUpdate', 'onReady', 'onModalClose', 'onModalOpen', 'dataFilter', 'onGetDetails'];
    $.each(transOptions, function (key, val) {
      if (toBeEvaluated.indexOf(key) > -1) {
        transOptions[key] = _construct(Function, _toConsumableArray(transOptions[key]));
      }
    });
    this.options = $.extend({}, defaultSettings, transOptions);
  }
  /**
   * Get the html snippet for the item data
   * @param string key 
   */


  _createClass(PreviewModalScript, [{
    key: "getForDebug",
    value: function getForDebug(key) {
      return this.options.debug ? "<em class=\"small\">".concat(this.options.debugString, " ").concat(key, " </em>") : '';
    }
    /**
     * select an Item
     */

  }, {
    key: "selectItem",
    value: function selectItem(itemData) {
      $("#selector__".concat(this.widgetsJsName, "-currentSelected")).html(itemData.title);
      $("#selector__".concat(this.widgetsJsName, "--buttonText")).html("".concat(itemData.title, " ").concat(this.getForDebug(itemData.key)));
      $("#selector__".concat(this.widgetsJsName, "-detailPage")).html(this.options.onGetDetails(itemData.itemArray.detailpage, itemData));
      this.inputItem.val(itemData.key);
      this.options.option = itemData.itemArray;
      this.options.value = itemData.key;
    }
  }, {
    key: "selectItemClick",

    /**
     * triggered by clicking on an item in the selector
     */
    value: function selectItemClick(ev) {
      console.ls.log("CURRENT SELECTED", $(ev.currentTarget));
      $(".selector__Item--select-".concat(this.widgetsJsName)).removeClass('mark-as-selected');
      $(ev.currentTarget).addClass('mark-as-selected');
      var itemData = $(ev.currentTarget).data('item-value');
      this.selectItem(itemData);
    }
  }, {
    key: "preSelectFromValue",

    /**
     * Workaround for the crazy person to use '*' as the short for a question type
     */
    value: function preSelectFromValue(value) {
      value = value || this.inputItem.val() || this.options.value;
      return $(".selector__Item--select-".concat(this.widgetsJsName, "[data-key='").concat(value.toString().trim(), "']"));
    }
    /**
     * event triggered when the modal opens
     */

  }, {
    key: "onModalShown",
    value: function onModalShown() {
      var selectedItem = this.preSelectFromValue();

      if (selectedItem) {
        $(selectedItem).addClass('mark-as-selected');
        $(selectedItem).closest('div.panel-collapse').addClass('in');
      }

      this.options.onModalOpen();
    }
  }, {
    key: "onModalClosed",

    /**
     * event triggered when the modal closes
     */
    value: function onModalClosed() {
      $(this.modalItem).find('.panel-collapse.collapse').each(function (i, item) {
        $(item).removeClass('in');
      });
      this.options.onModalClose();
    }
  }, {
    key: "bind",

    /**
     * bind to all necessary events
     */
    value: function bind() {
      var _this = this;

      if (this.options.secondaryInputElement != null) {
        this.options.value = $(this.options.secondaryInputElement).val();
        $(this.options.secondaryInputElement).off('change.previewModal');
        $(this.options.secondaryInputElement).on('change.previewModal', function (e) {
          _this.selectItemClick(_this.preSelectFromValue($(e.currentTarget).val()));
        });
      }

      if (/modal/.test(this.options.viewType)) {
        $(this.modalItem).on('hide.bs.modal', function () {
          _this.onModalClosed();
        });
        $(this.modalItem).on('shown.bs.modal', function () {
          _this.onModalShown();
        });
        $(".selector__Item--select-".concat(this.widgetsJsName, ":not(.disabled)")).on('click', function (ev) {
          _this.selectItemClick(ev);
        });
        $("#selector__select-this-".concat(this.widgetsJsName)).on('click', function () {
          _this.options.onUpdate(_this.options.value, _this.options.option);

          _this.modalItem.modal('hide');
        });
      } else {
        $('#in_survey_common').off('change.previewModal');
        $('#in_survey_common').on('change.previewModal', "#".concat(this.widgetsJsName), function (e) {
          _this.options.onUpdate($(e.currentTarget).val());
        });
      }

      this.options.onReady(this);
    }
  }]);

  return PreviewModalScript;
}();
