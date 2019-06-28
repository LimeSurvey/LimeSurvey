webpackHotUpdate("main",{

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/helperComponents/SaveLabelSet.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/helperComponents/SaveLabelSet.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_number_constructor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.number.constructor */ "./node_modules/core-js/modules/es6.number.constructor.js");
/* harmony import */ var core_js_modules_es6_number_constructor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_number_constructor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lodash/keys */ "./node_modules/lodash/keys.js");
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(lodash_keys__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! lodash/merge */ "./node_modules/lodash/merge.js");
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(lodash_merge__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var lodash_forEach__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! lodash/forEach */ "./node_modules/lodash/forEach.js");
/* harmony import */ var lodash_forEach__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(lodash_forEach__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _mixins_runAjax__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../mixins/runAjax */ "./src/mixins/runAjax.js");

//




/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'SaveAsLabelSet',
  mixins: [_mixins_runAjax__WEBPACK_IMPORTED_MODULE_4__["default"]],
  props: {
    dataSet: {
      type: Object,
      required: true
    },
    scaleId: {
      type: Number,
      required: true
    },
    type: {
      type: String,
      required: true
    },
    typedef: {
      type: String,
      required: true
    },
    typekey: {
      type: String,
      required: true
    }
  },
  data: function data() {
    return {
      labelName: ''
    };
  },
  methods: {
    confirm: function confirm() {
      var _this = this;

      var dataSetTosend = lodash_merge__WEBPACK_IMPORTED_MODULE_2___default()({}, this.dataSet);
      delete dataSetTosend[this.typekey];
      dataSetTosend.code = this.dataSet[this.typekey];
      lodash_forEach__WEBPACK_IMPORTED_MODULE_3___default()(this.$store.state.languages, function (language, lngKey) {
        var tmpLangObj = lodash_merge__WEBPACK_IMPORTED_MODULE_2___default()({}, _this.dataSet[lngKey]);
        tmpLangObj.title = tmpLangObj[_this.typedef];
        delete tmpLangObj[_this.typedef];
        dataSetTosend[lngKey] = tmpLangObj;
      });
      var payload = {
        label_name: this.labelName,
        labels: dataSetTosend,
        languages: lodash_keys__WEBPACK_IMPORTED_MODULE_1___default()(this.$store.state.languages).join(' ')
      };
      this.$store.dispatch('saveAsLabelSet').then(function (result) {
        LS.LsGlobalNotifier.create(result.message, result.classes);

        _this.$emit('close');
      }, function (error) {
        _this.$log.error(error);

        _this.$emit('close');
      });
    }
  }
});

/***/ })

})
//# sourceMappingURL=main.2a9d08590c1bd4884bfd.hot-update.js.map