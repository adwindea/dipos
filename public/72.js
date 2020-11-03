(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[72],{

/***/ "./coreui/src/views/product/DeleteProduct.vue":
/*!****************************************************!*\
  !*** ./coreui/src/views/product/DeleteProduct.vue ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _DeleteProduct_vue_vue_type_template_id_7f78cb2c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DeleteProduct.vue?vue&type=template&id=7f78cb2c& */ "./coreui/src/views/product/DeleteProduct.vue?vue&type=template&id=7f78cb2c&");
/* harmony import */ var _DeleteProduct_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DeleteProduct.vue?vue&type=script&lang=js& */ "./coreui/src/views/product/DeleteProduct.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _DeleteProduct_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _DeleteProduct_vue_vue_type_template_id_7f78cb2c___WEBPACK_IMPORTED_MODULE_0__["render"],
  _DeleteProduct_vue_vue_type_template_id_7f78cb2c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "coreui/src/views/product/DeleteProduct.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./coreui/src/views/product/DeleteProduct.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./coreui/src/views/product/DeleteProduct.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DeleteProduct_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./DeleteProduct.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./coreui/src/views/product/DeleteProduct.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DeleteProduct_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./coreui/src/views/product/DeleteProduct.vue?vue&type=template&id=7f78cb2c&":
/*!***********************************************************************************!*\
  !*** ./coreui/src/views/product/DeleteProduct.vue?vue&type=template&id=7f78cb2c& ***!
  \***********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DeleteProduct_vue_vue_type_template_id_7f78cb2c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./DeleteProduct.vue?vue&type=template&id=7f78cb2c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./coreui/src/views/product/DeleteProduct.vue?vue&type=template&id=7f78cb2c&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DeleteProduct_vue_vue_type_template_id_7f78cb2c___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DeleteProduct_vue_vue_type_template_id_7f78cb2c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./coreui/src/views/product/DeleteProduct.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./coreui/src/views/product/DeleteProduct.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./coreui/node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'DeleteProduct',
  data: function data() {
    return {
      message: '',
      product: '',
      dismissSecs: 7,
      dismissCountDown: 0
    };
  },
  methods: {
    goBack: function goBack() {
      this.$router.go(-1);
    },
    deleteProduct: function deleteProduct() {
      var self = this;
      axios__WEBPACK_IMPORTED_MODULE_0___default.a.get(this.$apiAdress + '/api/product/delete?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid, {}).then(function (response) {
        self.$router.go(-1);
      })["catch"](function (error) {
        console.log(error);
        self.$router.push({
          path: '/login'
        });
      });
    },
    showAlert: function showAlert() {
      this.dismissCountDown = this.dismissSecs;
    },
    getData: function getData() {
      var self = this;
      axios__WEBPACK_IMPORTED_MODULE_0___default.a.get(this.$apiAdress + '/api/product/show?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid).then(function (response) {
        self.product = response.data.product;
      })["catch"](function (error) {
        console.log(error);
        self.$router.push({
          path: '/login'
        });
      });
    }
  },
  mounted: function mounted() {
    this.getData();
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./coreui/src/views/product/DeleteProduct.vue?vue&type=template&id=7f78cb2c&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./coreui/src/views/product/DeleteProduct.vue?vue&type=template&id=7f78cb2c& ***!
  \*****************************************************************************************************************************************************************************************************************/
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
    "CRow",
    [
      _c(
        "CCol",
        { attrs: { col: "6", lg: "6" } },
        [
          _c(
            "CCard",
            [
              _c(
                "CCardBody",
                [
                  _c("h4", [_vm._v("Delete " + _vm._s(_vm.product.name))]),
                  _vm._v(" "),
                  _c("p", [_vm._v("Are you sure?")]),
                  _vm._v(" "),
                  _c(
                    "CAlert",
                    {
                      attrs: {
                        show: _vm.dismissCountDown,
                        color: "primary",
                        fade: ""
                      },
                      on: {
                        "update:show": function($event) {
                          _vm.dismissCountDown = $event
                        }
                      }
                    },
                    [
                      _vm._v(
                        "\n                    (" +
                          _vm._s(_vm.dismissCountDown) +
                          ") " +
                          _vm._s(_vm.message) +
                          "\n                "
                      )
                    ]
                  ),
                  _vm._v(" "),
                  _c(
                    "CButton",
                    {
                      attrs: { color: "danger" },
                      on: {
                        click: function($event) {
                          return _vm.deleteProduct()
                        }
                      }
                    },
                    [_vm._v("Delete")]
                  ),
                  _vm._v(" "),
                  _c(
                    "CButton",
                    { attrs: { color: "primary" }, on: { click: _vm.goBack } },
                    [_vm._v("Back")]
                  )
                ],
                1
              )
            ],
            1
          )
        ],
        1
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ })

}]);