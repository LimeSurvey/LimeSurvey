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
      default: null
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
      this.filteredList.length * 28 + 'px';
    }
  },
  methods: {
    processKeyPress: function processKeyPress($event) {
      this.forceClosed = false;

      if ($event.key == 'down') {}
    },
    itemSelected: function itemSelected(item) {
      var result = this.valueKey === false ? item : item[this.valueKey];
      this.input = item[this.showKey];
      this.$emit('input', result);
      this.forceClosed = true;
    },
    match: function match(comparable) {
      this.$log.log("Matching ".concat(comparable, " to ").concat(this.input, " with ").concat(this.matchType, "-Method"));
      var result = true;

      switch (this.matchType) {
        case 'fuzzy':
          return this._fuzzy(comparable);

        case 'exact':
          return this._exact(comparable);

        case 'start':
          return this._start(comparable);
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

/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/sass-loader/lib/loader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/helperComponents/Autocomplete.vue?vue&type=style&index=0&id=4d58584a&lang=scss&scoped=true&":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--8-oneOf-1-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--8-oneOf-1-2!./node_modules/sass-loader/lib/loader.js??ref--8-oneOf-1-3!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/helperComponents/Autocomplete.vue?vue&type=style&index=0&id=4d58584a&lang=scss&scoped=true& ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, ".scoped-autocomplete-input-container[data-v-4d58584a]{position:relative}.scoped-autocomplete-list[data-v-4d58584a]{position:absolute;top:24px;left:0;width:90%;padding:0;margin:4px 5%;overflow:auto;border:1px solid #212121;background-color:#fff;list-style:none}.scoped-autocomplete-list-item[data-v-4d58584a]{padding:4px 6px;border-bottom:1px solid #929292}.scoped-autocomplete-list-item.selected[data-v-4d58584a],.scoped-autocomplete-list-item[data-v-4d58584a]:hover{background-color:#dedede}.scoped-autocomplete-list-item[data-v-4d58584a]:last-of-type{border-bottom:none}", ""]);

// exports


/***/ })

})
//# sourceMappingURL=main.b9f5a790a3cb630fa889.hot-update.js.map