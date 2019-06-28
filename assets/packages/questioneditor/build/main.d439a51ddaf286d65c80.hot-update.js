webpackHotUpdate("main",{

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/helperComponents/LabelSets.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/helperComponents/LabelSets.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_regexp_split__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.regexp.split */ "./node_modules/core-js/modules/es6.regexp.split.js");
/* harmony import */ var core_js_modules_es6_regexp_split__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_split__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es6_number_constructor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es6.number.constructor */ "./node_modules/core-js/modules/es6.number.constructor.js");
/* harmony import */ var core_js_modules_es6_number_constructor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_number_constructor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var lodash_map__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! lodash/map */ "./node_modules/lodash/map.js");
/* harmony import */ var lodash_map__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(lodash_map__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! lodash/merge */ "./node_modules/lodash/merge.js");
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(lodash_merge__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! lodash/keys */ "./node_modules/lodash/keys.js");
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(lodash_keys__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _Autocomplete__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./Autocomplete */ "./src/helperComponents/Autocomplete.vue");
/* harmony import */ var _mixins_runAjax__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../mixins/runAjax */ "./src/mixins/runAjax.js");







/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'LabelSets',
  components: {
    Autocomplete: _Autocomplete__WEBPACK_IMPORTED_MODULE_5__["default"]
  },
  mixins: [_mixins_runAjax__WEBPACK_IMPORTED_MODULE_6__["default"]],
  props: {
    template: {
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
      labelSets: [],
      isLoading: true,
      currentLabelSet: null
    };
  },
  computed: {
    labelSetToDataSet: function labelSetToDataSet() {
      var _this = this;

      return lodash_map__WEBPACK_IMPORTED_MODULE_2___default()(this.currentLabelSet.labels, function (label) {
        var dataSet = lodash_merge__WEBPACK_IMPORTED_MODULE_3___default()({}, _this.template);
        dataSet[_this.$store.state.activeLanguage][_this.typedef] = _this.currentLanguageValue(label);
        dataSet[_this.typekey] = label.code;
        return dataSet;
      });
    }
  },
  methods: {
    compileLanguages: function compileLanguages(languageString) {
      return "(".concat(languageString.split(' ').join(','), ")");
    },
    replaceCurrent: function replaceCurrent() {
      this.$emit('modalEvent', {
        target: this.type,
        method: 'replaceFromLabelSets',
        content: {
          scaleId: this.scaleId,
          data: this.labelSetToDataSet
        }
      });
      this.$emit('close');
    },
    addToCurrent: function addToCurrent() {
      this.$emit('modalEvent', {
        target: this.type,
        method: 'addToFromLabelSets',
        content: {
          scaleId: this.scaleId,
          data: this.labelSetToDataSet
        }
      });
      this.$emit('close');
    },
    currentLanguageValue: function currentLanguageValue(label) {
      return label[this.$store.state.activeLanguage] != undefined ? label[this.$store.state.activeLanguage].title : '';
    }
  },
  created: function created() {
    var _this2 = this;

    this.$_get(LS.createUrl('admin/labels/sa/getLabelSetsForQuestion'), {
      'languages': lodash_keys__WEBPACK_IMPORTED_MODULE_4___default()(this.$store.state.languages)
    }).then(function (result) {
      _this2.isLoading = false;
      _this2.labelSets = result.data;
    }, function (error) {
      _this2.$log.error(error);

      _this2.isLoading = false;
    });
  }
});

/***/ })

})
//# sourceMappingURL=main.d439a51ddaf286d65c80.hot-update.js.map