webpackHotUpdate("main",{

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
                hover: function($event) {
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
//# sourceMappingURL=main.d8f9ffbd816c11cf83da.hot-update.js.map