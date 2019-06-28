webpackHotUpdate("main",{

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/helperComponents/Autocomplete.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/helperComponents/Autocomplete.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_regexp_constructor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.regexp.constructor */ "./node_modules/core-js/modules/es6.regexp.constructor.js");
/* harmony import */ var core_js_modules_es6_regexp_constructor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_constructor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es6_regexp_match__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es6.regexp.match */ "./node_modules/core-js/modules/es6.regexp.match.js");
/* harmony import */ var core_js_modules_es6_regexp_match__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_match__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var lodash_filter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! lodash/filter */ "./node_modules/lodash/filter.js");
/* harmony import */ var lodash_filter__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(lodash_filter__WEBPACK_IMPORTED_MODULE_2__);



/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'lsautocomplete',
  props: {
    dataList: {
      type: Array,
      required: true
    },
    searchableKeys: {
      type: Array,
      default: ['name', 'title']
    },
    showKey: {
      type: String,
      default: 'name'
    },
    valueKey: {
      type: String | Boolean,
      default: false
    },
    matchType: {
      type: String,
      default: 'fuzzy'
    },
    itemClass: {
      type: String,
      default: ''
    },
    inputClass: {
      type: String,
      default: ''
    },
    value: {
      default: ''
    }
  },
  data: function data() {
    return {
      input: '',
      forceClosed: false
    };
  },
  computed: {
    showDropdown: function showDropdown() {
      return this.input != '';
    },
    filteredList: function filteredList() {
      var _this = this;

      return lodash_filter__WEBPACK_IMPORTED_MODULE_2___default()(this.dataList, function (listItem) {
        return _this.searchableKeys.reduce(function (coll, key) {
          if (listItem[key] == undefined) {
            return coll;
          }

          return coll || _this.match(listItem[key]);
        }, false);
      });
    },
    currentItemsHeight: function currentItemsHeight() {
      this.filteredList.length * 28;
    }
  },
  methods: {
    itemSelected: function itemSelected(item) {
      var result = this.valueKey === false ? item : item[this.valueKey];
      this.input = item[this.showKey];
      this.$emit('change', result);
      this.forceClosed = true;
    },
    match: function match(comparable) {
      this.$log.log("Matching ".concat(comparable, " to ").concat(this.input, " with ").concat(this.matchType, "-Method"));
      var result = true;

      switch (this.matchType) {
        case 'fuzzy':
          result = this._fuzzy(comparable);
          this.$log.log("Result: ".concat(result));
          return result;

        case 'exact':
          result = this._exact(comparable);
          this.$log.log("Result: ".concat(result));
          return result;

        case 'start':
          result = this._start(comparable);
          this.$log.log("Result: ".concat(result));
          return result;
      }
    },
    _fuzzy: function _fuzzy(comparable) {
      var regExp = new RegExp(".*" + this.input + ".*");
      return regExp.test(comparable);
    },
    _exact: function _exact(comparable) {
      var regExp = new RegExp(this.input);
      return regExp.test(comparable);
    },
    _start: function _start(comparable) {
      var regExp = new RegExp(this.input + ".*");
      return regExp.test(comparable);
    },
    lazy: function lazy(comparable) {
      return comparable.toLowerCase().indexOf(this.input.toLowerCase()) > -1;
    }
  },
  mounted: function mounted() {
    if (this.value != '') {
      this.input = this.value;
    }
  }
});

/***/ })

})
//# sourceMappingURL=main.49c947cc649986538cdd.hot-update.js.map