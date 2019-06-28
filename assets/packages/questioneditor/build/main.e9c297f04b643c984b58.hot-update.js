webpackHotUpdate("main",{

/***/ "./src/mixins/abstractSubquestionAndAnswers.js":
/*!*****************************************************!*\
  !*** ./src/mixins/abstractSubquestionAndAnswers.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.function.name */ "./node_modules/core-js/modules/es6.function.name.js");
/* harmony import */ var core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es6_regexp_to_string__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es6.regexp.to-string */ "./node_modules/core-js/modules/es6.regexp.to-string.js");
/* harmony import */ var core_js_modules_es6_regexp_to_string__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_to_string__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_parse_int__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/@babel/runtime-corejs2/core-js/parse-int */ "./node_modules/@babel/runtime-corejs2/core-js/parse-int.js");
/* harmony import */ var C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_parse_int__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_parse_int__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es6.regexp.replace */ "./node_modules/core-js/modules/es6.regexp.replace.js");
/* harmony import */ var core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var lodash_max__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! lodash/max */ "./node_modules/lodash/max.js");
/* harmony import */ var lodash_max__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(lodash_max__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash/keys */ "./node_modules/lodash/keys.js");
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash_keys__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! lodash/merge */ "./node_modules/lodash/merge.js");
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(lodash_merge__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var lodash_remove__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! lodash/remove */ "./node_modules/lodash/remove.js");
/* harmony import */ var lodash_remove__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(lodash_remove__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var lodash_reduce__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! lodash/reduce */ "./node_modules/lodash/reduce.js");
/* harmony import */ var lodash_reduce__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(lodash_reduce__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var lodash_forEach__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! lodash/forEach */ "./node_modules/lodash/forEach.js");
/* harmony import */ var lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(lodash_forEach__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var lodash_findIndex__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! lodash/findIndex */ "./node_modules/lodash/findIndex.js");
/* harmony import */ var lodash_findIndex__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(lodash_findIndex__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var lodash_isArrayLike__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! lodash/isArrayLike */ "./node_modules/lodash/isArrayLike.js");
/* harmony import */ var lodash_isArrayLike__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(lodash_isArrayLike__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var lodash_isObjectLike__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! lodash/isObjectLike */ "./node_modules/lodash/isObjectLike.js");
/* harmony import */ var lodash_isObjectLike__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(lodash_isObjectLike__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var _helperComponents_QuickEdit_vue__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ../helperComponents/QuickEdit.vue */ "./src/helperComponents/QuickEdit.vue");
/* harmony import */ var _helperComponents_LabelSets_vue__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ../helperComponents/LabelSets.vue */ "./src/helperComponents/LabelSets.vue");
/* harmony import */ var _helperComponents_SimplePopUpEditor_vue__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ../helperComponents/SimplePopUpEditor.vue */ "./src/helperComponents/SimplePopUpEditor.vue");
















/* harmony default export */ __webpack_exports__["default"] = ({
  components: {
    QuickEdit: _helperComponents_QuickEdit_vue__WEBPACK_IMPORTED_MODULE_13__["default"],
    SimplePopUpEditor: _helperComponents_SimplePopUpEditor_vue__WEBPACK_IMPORTED_MODULE_15__["default"],
    LabelSets: _helperComponents_LabelSets_vue__WEBPACK_IMPORTED_MODULE_14__["default"]
  },
  props: {
    readonly: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    getLength: function getLength(arrayOrObject) {
      if (lodash_isArrayLike__WEBPACK_IMPORTED_MODULE_11___default()(arrayOrObject)) {
        return arrayOrObject.length;
      }

      if (lodash_isObjectLike__WEBPACK_IMPORTED_MODULE_12___default()(arrayOrObject)) {
        return lodash_keys__WEBPACK_IMPORTED_MODULE_5___default()(arrayOrObject).length;
      }

      return 0;
    },
    getNewTitleFromCurrent: function getNewTitleFromCurrent(scaleId) {
      var nonNumericPart = this.baseNonNumericPart;

      if (this.getLength(this.currentDataSet[scaleId]) > 0) {
        nonNumericPart = (this.currentDataSet[scaleId][0].title || this.currentDataSet[scaleId][0].code).replace(/[0-9]/g, '');
      }

      var numericPart = lodash_reduce__WEBPACK_IMPORTED_MODULE_8___default()(this.currentDataSet[scaleId], function (prev, oDataSet) {
        return lodash_max__WEBPACK_IMPORTED_MODULE_4___default()([prev, C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_parse_int__WEBPACK_IMPORTED_MODULE_2___default()((oDataSet.title || oDataSet.code).replace(/[^0-9]/g, ''))]);
      }, 0) + 1;
      this.$log.log('NewTitle', {
        nonNumericPart: nonNumericPart,
        numericPart: numericPart
      });
      return nonNumericPart + '' + numericPart;
    },
    getRandomId: function getRandomId() {
      return 'random' + Math.random().toString(36).substr(2, 7);
    },
    deleteThisDataSet: function deleteThisDataSet(oDataSet, scaleId) {
      var _this = this;

      var tmpArray = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()([], this.currentDataSet);
      tmpArray[scaleId] = lodash_remove__WEBPACK_IMPORTED_MODULE_7___default()(tmpArray[scaleId], function (oDataSetIterator) {
        return oDataSetIterator[_this.uniqueSelector] != oDataSet[_this.uniqueSelector];
      });
      this.currentDataSet = tmpArray;
    },
    duplicateThisDataSet: function duplicateThisDataSet(oDataSet, scaleId) {
      var tmpArray = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()([], this.currentDataSet);
      var newDataSet = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, oDataSet);
      newDataSet[this.uniqueSelector] = this.getRandomId();
      tmpArray[scaleId].push(newDataSet);
      this.currentDataSet = tmpArray;
    },
    addDataSet: function addDataSet(scaleId) {
      var tmpArray = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()([], this.currentDataSet);
      tmpArray[scaleId] = tmpArray[scaleId] || new Array();
      tmpArray[scaleId].push(this.getTemplate(scaleId));
      this.currentDataSet = tmpArray;
    },
    openLabelSets: function openLabelSets(scaleId) {
      this.$modal.show(_helperComponents_LabelSets_vue__WEBPACK_IMPORTED_MODULE_14__["default"], {
        scaleId: scaleId,
        template: this.getTemplate(scaleId),
        type: this.type,
        typedef: this.typeDefininition,
        typekey: this.typeDefininitionKey
      }, {
        width: '75%',
        height: '75%',
        scrollable: true,
        resizable: true
      });
    },
    openQuickAdd: function openQuickAdd() {
      this.$modal.show(_helperComponents_QuickEdit_vue__WEBPACK_IMPORTED_MODULE_13__["default"], {
        current: this.currentDataSet,
        type: this.type,
        typedef: this.typeDefininition,
        typekey: this.typeDefininitionKey
      }, {
        width: '75%',
        height: '75%',
        scrollable: true,
        resizable: true
      });
    },
    openPopUpEditor: function openPopUpEditor(dataSetObject, scaleId) {
      var _this2 = this;

      this.$modal.show(_helperComponents_SimplePopUpEditor_vue__WEBPACK_IMPORTED_MODULE_15__["default"], {
        target: this.type,
        dataSetObject: dataSetObject,
        typeDef: this.typeDefininition,
        typeDefKey: this.typeDefininitionKey
      }, {
        width: '75%',
        height: '75%',
        scrollable: true,
        resizable: true
      }, {
        'closed': function closed(event, payload) {
          _this2.$log.log('MODAL CLOSED', event, payload);

          if (event.save == true) {
            dataSetObject[_this2.$store.state.activeLanguage][_this2.typeDefininition] = event.value;
          }
        },
        'change': function change(event, payload) {
          _this2.$log.log('CHANGE IN MODAL', event, payload);

          if (event.save == true) {
            dataSetObject[_this2.$store.state.activeLanguage][_this2.typeDefininition] = event.value;
          }
        }
      });
    },
    switchinput: function switchinput(newTarget) {
      var $event = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

      if (newTarget == false) {
        this.$log.log($event);
        return;
      }

      $('#' + newTarget).focus();
    },
    replaceFromQuickAdd: function replaceFromQuickAdd(contents) {
      var _this3 = this;

      this.$log.log('replaceFromQuickAdd triggered on: ' + this.$options.name, contents);
      var tempObject = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, this.currentDataSet);
      lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(contents, function (scaleObject, scale) {
        tempObject[scale] = [];
        lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(scaleObject, function (lngSet, key) {
          var newDataSetBlock = _this3.getTemplate(scale);

          newDataSetBlock[_this3.typeDefininitionKey] = key;
          lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(lngSet, function (dataSetValue, lngKey) {
            newDataSetBlock[lngKey][_this3.typeDefininition] = dataSetValue;
          });
          tempObject[scale].push(newDataSetBlock);
        });
      });
      this.currentDataSet = tempObject;
    },
    addToFromQuickAdd: function addToFromQuickAdd(contents) {
      var _this4 = this;

      this.$log.log('addToFromQuickAdd triggered on: ' + this.$options.name, contents);
      var tempObject = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, this.currentDataSet);
      lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(contents, function (scaleObject, scale) {
        lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(scaleObject, function (lngSet, key) {
          var newDataSetBlock = _this4.getTemplate(scale);

          newDataSetBlock[_this4.typeDefininitionKey] = key;
          lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(lngSet, function (dataSetValue, lngKey) {
            newDataSetBlock[lngKey][_this4.typeDefininition] = dataSetValue;
          });
          tempObject[scale].push(newDataSetBlock);
        });
      });
      this.currentDataSet = tempObject;
    },
    replaceFromLabelSets: function replaceFromLabelSets(contents) {
      var _this5 = this;

      this.$log.log('replaceFromQuickAdd triggered on: ' + this.$options.name, contents);
      var tempObject = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, this.currentDataSet);
      tempObject[contents.scaleId] = [];
      lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(contents.data, function (dataSet) {
        dataSet[_this5.uniqueSelector] = _this5.getRandomId();
        tempObject[contents.scaleId].push(dataSet);
      });
      dataSet[this.uniqueSelector] = this.getRandomId();
      this.currentDataSet = tempObject;
    },
    addToFromLabelSets: function addToFromLabelSets(contents) {
      var _this6 = this;

      this.$log.log('addToFromQuickAdd triggered on: ' + this.$options.name, contents);
      var tempObject = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, this.currentDataSet);
      lodash_forEach__WEBPACK_IMPORTED_MODULE_9___default()(contents.data, function (dataSet) {
        dataSet[_this6.uniqueSelector] = _this6.getRandomId();
        tempObject[contents.scaleId].push(dataSet);
      });
      this.currentDataSet = tempObject;
    },
    saveAsLabelSet: function saveAsLabelSet(scaleId) {
      var dataSet = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, this.currentDataSet[scaleId]);
      this.$modal.show(_helperComponents_SimplePopUpEditor_vue__WEBPACK_IMPORTED_MODULE_15__["default"], {
        target: this.type,
        dataSet: dataSet,
        typeDef: this.typeDefininition,
        typeDefKey: this.typeDefininitionKey
      }, {
        width: '75%',
        height: '75%',
        scrollable: true,
        resizable: true
      });
    },
    editFromSimplePopupEditor: function editFromSimplePopupEditor(contents) {
      var _this7 = this;

      this.$log.log('Event editFromSimplePopupEditor', contents);
      var tempFullObject = lodash_merge__WEBPACK_IMPORTED_MODULE_6___default()({}, this.currentDataSet);
      var identifier = lodash_findIndex__WEBPACK_IMPORTED_MODULE_10___default()(tempFullObject[contents.scale_id], function (dataSetObject, i) {
        return dataSetObject[_this7.typeDefininitionKey] === contents[_this7.typeDefininitionKey];
      });
      tempFullObject[contents.scale_id][identifier] = contents;
      this.$log.log('Event editFromSimplePopupEditor result', {
        identifier: identifier,
        tempFullObject: tempFullObject
      });
      this.currentDataSet = tempFullObject;
    }
  }
});

/***/ })

})
//# sourceMappingURL=main.e9c297f04b643c984b58.hot-update.js.map