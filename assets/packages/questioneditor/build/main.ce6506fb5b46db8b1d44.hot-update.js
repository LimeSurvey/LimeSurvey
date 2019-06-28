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
/* harmony import */ var lodash_map__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lodash/map */ "./node_modules/lodash/map.js");
/* harmony import */ var lodash_map__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(lodash_map__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! lodash/merge */ "./node_modules/lodash/merge.js");
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(lodash_merge__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! lodash/keys */ "./node_modules/lodash/keys.js");
/* harmony import */ var lodash_keys__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(lodash_keys__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Autocomplete__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Autocomplete */ "./src/helperComponents/Autocomplete.vue");
/* harmony import */ var _mixins_runAjax__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../mixins/runAjax */ "./src/mixins/runAjax.js");






/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'LabelSets',
  components: {
    Autocomplete: _Autocomplete__WEBPACK_IMPORTED_MODULE_4__["default"]
  },
  mixins: [_mixins_runAjax__WEBPACK_IMPORTED_MODULE_5__["default"]],
  props: {
    template: {
      type: Object,
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

      return lodash_map__WEBPACK_IMPORTED_MODULE_1___default()(this.currentLabelSet.labels, function (label) {
        var dataSet = lodash_merge__WEBPACK_IMPORTED_MODULE_2___default()({}, _this.template);
        dataSet[_this.typedef] = _this.currentLanguageValue(label);
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
        content: this.labelSetToDataSet
      });
      this.$emit('close');
    },
    addToCurrent: function addToCurrent() {
      this.$emit('modalEvent', {
        target: this.type,
        method: 'addToFromLabelSets',
        content: this.labelSetToDataSet
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
      'languages': lodash_keys__WEBPACK_IMPORTED_MODULE_3___default()(this.$store.state.languages)
    }).then(function (result) {
      _this2.isLoading = false;
      _this2.labelSets = result.data;
    }, function (error) {
      _this2.$log.error(error);

      _this2.isLoading = false;
    });
  }
});

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"7d09658b-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/helperComponents/LabelSets.vue?vue&type=template&id=b085c81a&":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"7d09658b-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/helperComponents/LabelSets.vue?vue&type=template&id=b085c81a& ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
  return _c("div", { staticClass: "panel panel-default ls-flex-column fill" }, [
    _c("div", { staticClass: "panel-heading" }, [
      _c("div", { staticClass: "row" }, [
        _c("div", { staticClass: "pagetitle h3" }, [
          _vm._v(_vm._s(_vm._f("translate")("Label sets")))
        ])
      ]),
      _c("div", { staticClass: "row" }, [
        _c(
          "label",
          { staticClass: "control-label col-xs-12 col-md-4" },
          [
            _vm.isLoading
              ? [_c("i", { staticClass: "fa fa-cog fa-spin" })]
              : [
                  _vm._v(
                    "\n                " +
                      _vm._s(_vm._f("translate")("Select label set")) +
                      " \n                "
                  )
                ]
          ],
          2
        ),
        _c(
          "div",
          { staticClass: "col-xs-12 col-md-8" },
          [
            _c("autocomplete", {
              attrs: {
                "data-list": _vm.labelSets,
                "searchable-keys": ["label_name"],
                "show-key": "label_name"
              },
              model: {
                value: _vm.currentLabelSet,
                callback: function($$v) {
                  _vm.currentLabelSet = $$v
                },
                expression: "currentLabelSet"
              }
            })
          ],
          1
        )
      ])
    ]),
    _c("div", { staticClass: "panel-body" }, [
      _vm.currentLabelSet != null
        ? _c(
            "div",
            { staticClass: "container-fluid" },
            [
              _c("div", { staticClass: "row" }, [
                _c("div", { staticClass: "col-xs-12" }, [
                  _c("h4", [
                    _vm._v(" " + _vm._s(_vm.currentLabelSet.label_name) + " ")
                  ])
                ])
              ]),
              _vm._m(0),
              _c("div", { staticClass: "row scoped-descriptionrow" }, [
                _c("div", { staticClass: "col-xs-3" }, [
                  _vm._v(
                    "\n                    " +
                      _vm._s(_vm._f("translate")("Sortorder")) +
                      "\n                "
                  )
                ]),
                _c("div", { staticClass: "col-xs-3" }, [
                  _vm._v(
                    "\n                    " +
                      _vm._s(_vm._f("translate")(_vm.typekey)) +
                      "\n                "
                  )
                ]),
                _c("div", { staticClass: "col-xs-3" }, [
                  _vm._v(
                    "\n                    " +
                      _vm._s(_vm._f("translate")(_vm.typedef)) +
                      "\n                "
                  )
                ]),
                _vm.type == "answeroptions"
                  ? _c("div", { staticClass: "col-xs-3" }, [
                      _vm._v(
                        "\n                    " +
                          _vm._s(_vm._f("translate")("Assessment value")) +
                          "\n                "
                      )
                    ])
                  : _vm._e()
              ]),
              _vm._l(_vm.currentLabelSet.labels, function(label) {
                return _c("div", { key: label.id, staticClass: "row" }, [
                  _c("div", { staticClass: "col-xs-3" }, [
                    _vm._v(
                      "\n                    " +
                        _vm._s(label.sortorder) +
                        "\n                "
                    )
                  ]),
                  _c("div", { staticClass: "col-xs-3" }, [
                    _vm._v(
                      "\n                    " +
                        _vm._s(label.code) +
                        "\n                "
                    )
                  ]),
                  _c("div", { staticClass: "col-xs-3" }, [
                    _vm._v(
                      "\n                    " +
                        _vm._s(_vm.currentLanguageValue(label)) +
                        "\n                "
                    )
                  ]),
                  _vm.type == "answeroptions"
                    ? _c("div", { staticClass: "col-xs-3" }, [
                        _vm._v(
                          "\n                    " +
                            _vm._s(label.assessment_value) +
                            "\n                "
                        )
                      ])
                    : _vm._e()
                ])
              })
            ],
            2
          )
        : _c("div", { staticClass: "container-fluid" }, [
            _c("div", { staticClass: "row" }, [
              _c("div", { staticClass: "row" }, [
                _c("p", { staticClass: "text-center scoped-no-selection" }, [
                  _vm._v(
                    " " +
                      _vm._s(_vm._f("translate")("No label set selected")) +
                      " "
                  )
                ])
              ])
            ])
          ])
    ]),
    _c("div", { staticClass: "panel-footer" }, [
      _c("div", { staticClass: "ls-flex-row wrap" }, [
        _c("div", { staticClass: "ls-flex-item" }, [
          _c(
            "button",
            {
              staticClass: "btn btn-primary ls-space margin left-5",
              attrs: { type: "button" },
              on: { click: _vm.replaceCurrent }
            },
            [_vm._v(_vm._s(_vm._f("translate")("Replace")))]
          ),
          _c(
            "button",
            {
              staticClass: "btn btn-primary ls-space margin left-5",
              attrs: { type: "button" },
              on: { click: _vm.addToCurrent }
            },
            [_vm._v(_vm._s(_vm._f("translate")("Add")))]
          )
        ])
      ])
    ])
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "row" }, [_c("hr")])
  }
]
render._withStripped = true



/***/ })

})
//# sourceMappingURL=main.ce6506fb5b46db8b1d44.hot-update.js.map