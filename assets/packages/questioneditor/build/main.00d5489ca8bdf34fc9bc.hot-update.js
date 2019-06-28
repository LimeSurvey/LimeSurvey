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
      forceClosed: false,
      selectedIndex: 0
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
      this.$log.log('PROCESS KEYPRESS | $event.key.toLowerCase => ', $event.key.toLowerCase);
      this.$log.log('PROCESS KEYPRESS | this.filteredList.length => ', this.filteredList.length);
      this.$log.log('PROCESS KEYPRESS | this.selectedIndex => ', this.selectedIndex);

      if ($event.key.toLowerCase == 'arrowdown') {
        this.selectedIndex++;

        if (this.selectedIndex >= this.filteredList.length) {
          this.selectedIndex = this.filteredList.length - 1;
        }
      }

      if ($event.key.toLowerCase == 'arrowup') {
        this.selectedIndex--;

        if (this.selectedIndex < 0) {
          this.selectedIndex = 0;
        }
      }

      if ($event.key.toLowerCase == 'enter') {
        this.itemSelected(this.filteredList[this.selectedIndex]);
      }
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
      var regExp = new RegExp(".*" + this.input + ".*", 'i');
      return regExp.test(comparable);
    },
    _exact: function _exact(comparable) {
      var regExp = new RegExp(this.input, 'i');
      return regExp.test(comparable);
    },
    _start: function _start(comparable) {
      var regExp = new RegExp(this.input + ".*", 'i');
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
//# sourceMappingURL=main.00d5489ca8bdf34fc9bc.hot-update.js.map