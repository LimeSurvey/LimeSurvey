webpackHotUpdate("main",{

/***/ "./src/storage/actions.js":
/*!********************************!*\
  !*** ./src/storage/actions.js ***!
  \********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/@babel/runtime-corejs2/core-js/promise */ "./node_modules/@babel/runtime-corejs2/core-js/promise.js");
/* harmony import */ var C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_web_dom_iterable__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/web.dom.iterable */ "./node_modules/core-js/modules/web.dom.iterable.js");
/* harmony import */ var core_js_modules_web_dom_iterable__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_iterable__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es6_string_iterator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es6.string.iterator */ "./node_modules/core-js/modules/es6.string.iterator.js");
/* harmony import */ var core_js_modules_es6_string_iterator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_string_iterator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../mixins/runAjax.js */ "./src/mixins/runAjax.js");
/* harmony import */ var lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! lodash/cloneDeep */ "./node_modules/lodash/cloneDeep.js");
/* harmony import */ var lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash/merge */ "./node_modules/lodash/merge.js");
/* harmony import */ var lodash_merge__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash_merge__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _mixins_logSystem_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../mixins/logSystem.js */ "./src/mixins/logSystem.js");







/* harmony default export */ __webpack_exports__["default"] = ({
  updateObjects: function updateObjects(context, newObjectBlock) {
    context.commit('setCurrentQuestion', newObjectBlock.question);
    context.commit('unsetQuestionImmutable');
    context.commit('setQuestionImmutable', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(newObjectBlock.question));
    context.commit('setCurrentQuestionI10N', newObjectBlock.questionI10N);
    context.commit('unsetQuestionImmutableI10N');
    context.commit('setQuestionImmutableI10N', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(newObjectBlock.questionI10N));
    context.commit('setCurrentQuestionSubquestions', newObjectBlock.scaledSubquestions);
    context.commit('unsetQuestionSubquestionsImmutable');
    context.commit('setQuestionSubquestionsImmutable', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(newObjectBlock.scaledSubquestions));
    context.commit('setCurrentQuestionAnswerOptions', newObjectBlock.scaledAnswerOptions);
    context.commit('unsetQuestionAnswerOptionsImmutable');
    context.commit('setQuestionAnswerOptionsImmutable', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(newObjectBlock.scaledAnswerOptions));
    context.commit('setCurrentQuestionGeneralSettings', newObjectBlock.generalSettings);
    context.commit('unsetImmutableQuestionGeneralSettings');
    context.commit('setImmutableQuestionGeneralSettings', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(newObjectBlock.generalSettings));
    context.commit('setCurrentQuestionAdvancedSettings', newObjectBlock.advancedSettings);
    context.commit('unsetImmutableQuestionAdvancedSettings');
    context.commit('setImmutableQuestionAdvancedSettings', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(newObjectBlock.advancedSettings));
    context.commit('setCurrentQuestionGroupInfo', newObjectBlock.questiongroup);
  },
  loadQuestion: function loadQuestion(context) {
    return C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a.all([new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionData' : '/getQuestionData';
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_get(window.QuestionEditData.connectorBaseUrl + subAction, {
        'iQuestionId': window.QuestionEditData.qid,
        type: window.QuestionEditData.startType
      }).then(function (result) {
        context.commit('setCurrentQuestion', result.data.question);
        context.commit('unsetQuestionImmutable');
        context.commit('setQuestionImmutable', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(result.data.question));
        context.commit('setCurrentQuestionI10N', result.data.i10n);
        context.commit('unsetQuestionImmutableI10N');
        context.commit('setQuestionImmutableI10N', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(result.data.i10n));
        context.commit('setCurrentQuestionSubquestions', result.data.subquestions);
        context.commit('unsetQuestionSubquestionsImmutable');
        context.commit('setQuestionSubquestionsImmutable', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(result.data.subquestions));
        context.commit('setCurrentQuestionAnswerOptions', result.data.answerOptions);
        context.commit('unsetQuestionAnswerOptionsImmutable');
        context.commit('setQuestionAnswerOptionsImmutable', lodash_cloneDeep__WEBPACK_IMPORTED_MODULE_4___default()(result.data.answerOptions));
        context.commit('setCurrentQuestionGroupInfo', result.data.questiongroup);
        context.commit('setLanguages', result.data.languages);
        context.commit('setActiveLanguage', result.data.mainLanguage);
        context.commit('setInTransfer', false);
        resolve(true);
      }, function (rejectAnswer) {
        reject(rejectAnswer);
      });
    }), new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionPermissions' : '/getQuestionPermissions';
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_get(window.QuestionEditData.connectorBaseUrl + subAction, {
        'iQuestionId': window.QuestionEditData.qid
      }).then(function (result) {
        context.commit('setCurrentQuestionPermissions', result.data);
        resolve(true);
      }, function (rejectAnswer) {
        reject(rejectAnswer);
      });
    })]);
  },
  getQuestionGeneralSettings: function getQuestionGeneralSettings(context) {
    return new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getGeneralOptions' : '/getGeneralOptions';
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_get(window.QuestionEditData.connectorBaseUrl + subAction, {
        'iQuestionId': window.QuestionEditData.qid,
        'sQuestionType': context.state.currentQuestion.type || window.QuestionEditData.startType
      }).then(function (result) {
        context.commit('setCurrentQuestionGeneralSettings', result.data);
        context.commit('unsetImmutableQuestionGeneralSettings', result.data);
        context.commit('setImmutableQuestionGeneralSettings', result.data);
        resolve(true);
      }, function (rejectAnswer) {
        reject(rejectAnswer);
      });
    });
  },
  getQuestionAdvancedSettings: function getQuestionAdvancedSettings(context) {
    return new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getAdvancedOptions' : '/getAdvancedOptions';
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_get(window.QuestionEditData.connectorBaseUrl + subAction, {
        'iQuestionId': window.QuestionEditData.qid,
        'sQuestionType': context.state.currentQuestion.type || window.QuestionEditData.startType
      }).then(function (result) {
        context.commit('setCurrentQuestionAdvancedSettings', result.data);
        context.commit('unsetImmutableQuestionAdvancedSettings', result.data);
        context.commit('setImmutableQuestionAdvancedSettings', result.data);
        resolve(true);
      }, function (rejectAnswer) {
        reject(rejectAnswer);
      });
    });
  },
  getQuestionTypes: function getQuestionTypes(context) {
    var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionTypeList' : '/getQuestionTypeList';
    _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_get(window.QuestionEditData.connectorBaseUrl + subAction).then(function (result) {
      context.commit('setQuestionTypeList', result.data);
    });
  },
  reloadQuestion: function reloadQuestion(context) {
    return new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionData' : '/getQuestionData';
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_get(window.QuestionEditData.connectorBaseUrl + subAction, {
        'iQuestionId': window.QuestionEditData.qid,
        type: context.state.currentQuestion.type || window.QuestionEditData.startType
      }).then(function (result) {
        context.commit('updateCurrentQuestion', result.data.question);
        context.commit('updateCurrentQuestionSubquestions', result.data.scaledSubquestions);
        context.commit('updateCurrentQuestionAnswerOptions', result.data.scaledAnswerOptions);
        context.commit('updateCurrentQuestionGeneralSettings', result.data.generalSettings);
        context.commit('updateCurrentQuestionAdvancedSettings', result.data.advancedSettings);
        context.commit('setCurrentQuestionGroupInfo', result.data.questiongroup);
        resolve();
      }, reject);
    });
  },
  saveQuestionData: function saveQuestionData(context) {
    if (context.state.inTransfer) {
      return C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a.resolve(false);
    }

    var transferObject = lodash_merge__WEBPACK_IMPORTED_MODULE_5___default()({
      'questionData': {
        question: context.state.currentQuestion,
        scaledSubquestions: context.state.currentQuestionSubquestions,
        scaledAnswerOptions: context.state.currentQuestionAnswerOptions,
        questionI10N: context.state.currentQuestionI10N,
        generalSettings: context.state.currentQuestionGeneralSettings,
        advancedSettings: context.state.currentQuestionAdvancedSettings
      }
    }, window.LS.data.csrfTokenData);
    _mixins_logSystem_js__WEBPACK_IMPORTED_MODULE_6__["LOG"].log('OBJECT TO BE TRANSFERRED: ', {
      'questionData': transferObject
    });
    return new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      var subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'saveQuestionData' : '/saveQuestionData';
      context.commit('setInTransfer', true);
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_post(window.QuestionEditData.connectorBaseUrl + subAction, transferObject).then(function (result) {
        context.commit('setInTransfer', false);
        resolve(result);
      }, reject);
    });
  },
  saveAsLabelSet: function saveAsLabelSet(context, payload) {
    var transferObject = lodash_merge__WEBPACK_IMPORTED_MODULE_5___default()({
      'labelSet': payload
    }, window.LS.data.csrfTokenData);
    _mixins_logSystem_js__WEBPACK_IMPORTED_MODULE_6__["LOG"].log('OBJECT TO BE TRANSFERRED: ', {
      'questionData': transferObject
    });
    return new C_Developement_LimeSurveyDevelop_webroot_assets_packages_questioneditor_node_modules_babel_runtime_corejs2_core_js_promise__WEBPACK_IMPORTED_MODULE_0___default.a(function (resolve, reject) {
      _mixins_runAjax_js__WEBPACK_IMPORTED_MODULE_3__["default"].methods.$_post(LS.createUrl('admin/labels/sa/newLabelSetFromQuestionEditor'), transferObject).then(resolve, reject);
    });
  }
});

/***/ })

})
//# sourceMappingURL=main.d626a3ac96d53e253faa.hot-update.js.map