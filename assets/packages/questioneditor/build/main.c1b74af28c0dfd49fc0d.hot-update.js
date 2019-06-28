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
      this.$log.log($event);
      this.forceClosed = false;

      if ($event.key.toLowerCase == 'arrowdown') {
        if (this.selectedIndex < this.filteredList.length) {
          this.selectedIndex++;
        }
      }

      if ($event.key.toLowerCase == 'arrowup') {
        if (this.selectedIndex > 0) {
          this.selectedIndex--;
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

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"7d09658b-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/helperComponents/Autocomplete.vue?vue&type=template&id=4d58584a&scoped=true&":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"7d09658b-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/helperComponents/Autocomplete.vue?vue&type=template&id=4d58584a&scoped=true& ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass: "scoped-autocomplete-input-container",
      class: _vm.itemClass
    },
    [
      _c("input", {
        directives: [
          {
            name: "model",
            rawName: "v-model",
            value: _vm.input,
            expression: "input"
          }
        ],
        staticClass: "form-control",
        class: _vm.inputClass,
        attrs: { type: "text" },
        domProps: { value: _vm.input },
        on: {
          keydown: _vm.processKeyPress,
          input: function($event) {
            if ($event.target.composing) {
              return
            }
            _vm.input = $event.target.value
          }
        }
      }),
      _c(
        "ul",
        {
          directives: [
            {
              name: "show",
              rawName: "v-show",
              value: _vm.showDropdown && !_vm.forceClosed,
              expression: "showDropdown && !forceClosed"
            }
          ],
          staticClass: "scoped-autocomplete-list",
          style: { height: _vm.currentItemsHeight }
        },
        _vm._l(_vm.filteredList, function(item, i) {
          return _c(
            "li",
            {
              key: "autocomplete-" + i,
              staticClass: "scoped-autocomplete-list-item",
              class: _vm.selectedIndex == i ? "selected" : "",
              on: {
                click: function($event) {
                  return _vm.itemSelected(item)
                },
                mouseover: function($event) {
                  _vm.selectedIndex = i
                }
              }
            },
            [
              _vm._v(
                "\n            " + _vm._s(item[_vm.showKey]) + "\n        "
              )
            ]
          )
        }),
        0
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ })

})
//# sourceMappingURL=main.c1b74af28c0dfd49fc0d.hot-update.js.map