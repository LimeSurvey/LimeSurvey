/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/Actions.js"
/*!************************!*\
  !*** ./src/Actions.js ***!
  \************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AjaxHelper.js */ "./src/AjaxHelper.js");
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./StateManager.js */ "./src/StateManager.js");
/**
 * Actions - Vanilla JS replacement for Vuex actions
 * Handles async operations and API calls
 */


const Actions = function () {
  'use strict';

  /**
   * Logger utility
   */
  function log() {
    if (console.ls && console.ls.log) {
      console.ls.log.apply(console.ls, arguments);
    }
  }

  /**
   * Trigger pjax refresh and update toggle key
   */
  function updatePjax() {
    $(document).trigger('pjax:refresh');
    _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('newToggleKey');
  }

  /**
   * Fetch side menus from server
   * @returns {Promise}
   */
  function getSidemenus() {
    return new Promise(function (resolve, reject) {
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_0__["default"].get(window.SideMenuData.getMenuUrl, {
        position: 'side'
      }).then(function (result) {
        log('sidemenues', result);
        const newSidemenus = LS.ld.orderBy(result.data.menues, function (a) {
          return parseInt(a.order || 999999);
        }, ['desc']);
        _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('updateSidemenus', newSidemenus);
        updatePjax();
        resolve(newSidemenus);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Fetch collapsed/quick menus from server
   * @returns {Promise}
   */
  function getCollapsedmenus() {
    return new Promise(function (resolve, reject) {
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_0__["default"].get(window.SideMenuData.getMenuUrl, {
        position: 'collapsed'
      }).then(function (result) {
        log('quickmenu', result);
        const newCollapsedmenus = LS.ld.orderBy(result.data.menues, function (a) {
          return parseInt(a.order || 999999);
        }, ['desc']);
        _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('updateCollapsedmenus', newCollapsedmenus);
        updatePjax();
        resolve(newCollapsedmenus);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Fetch questions from server
   * @returns {Promise}
   */
  function getQuestions() {
    return new Promise(function (resolve, reject) {
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_0__["default"].get(window.SideMenuData.getQuestionsUrl).then(function (result) {
        log('Questions', result);
        const newQuestiongroups = result.data.groups;
        _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('updateQuestiongroups', newQuestiongroups);
        updatePjax();
        resolve(newQuestiongroups);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Collect both side menus and collapsed menus
   * @returns {Promise}
   */
  function collectMenus() {
    return Promise.all([getSidemenus(), getCollapsedmenus()]);
  }

  /**
   * Toggle lock/unlock organizer setting
   * @returns {Promise}
   */
  function unlockLockOrganizer() {
    return new Promise(function (resolve, reject) {
      const value = _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].get('allowOrganizer') ? '0' : '1';
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_0__["default"].post(window.SideMenuData.unlockLockOrganizerUrl, {
        setting: 'lock_organizer',
        newValue: value
      }).then(function (result) {
        log('setUsersettingLog', result);
        _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('setAllowOrganizer', parseInt(value));
        resolve(result);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Change current tab and reload data
   * @param {string} tab
   * @returns {Promise}
   */
  function changeCurrentTab(tab) {
    _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('changeCurrentTab', tab);
    return Promise.all([collectMenus(), getQuestions()]);
  }

  /**
   * Update question group order on server
   * @param {Array} questiongroups
   * @param {string} surveyid
   * @returns {Promise}
   */
  function updateQuestionGroupOrder(questiongroups, surveyid) {
    const onlyGroupsArray = LS.ld.map(questiongroups, function (questiongroup) {
      const questions = LS.ld.map(questiongroup.questions, function (question) {
        return {
          qid: question.qid,
          question: question.question,
          gid: question.gid,
          question_order: question.question_order
        };
      });
      return {
        gid: questiongroup.gid,
        group_name: questiongroup.group_name,
        group_order: questiongroup.group_order,
        questions: questions
      };
    });
    return _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_0__["default"].post(window.SideMenuData.updateOrderLink, {
      grouparray: onlyGroupsArray,
      surveyid: surveyid
    });
  }
  return {
    updatePjax: updatePjax,
    getSidemenus: getSidemenus,
    getCollapsedmenus: getCollapsedmenus,
    getQuestions: getQuestions,
    collectMenus: collectMenus,
    unlockLockOrganizer: unlockLockOrganizer,
    changeCurrentTab: changeCurrentTab,
    updateQuestionGroupOrder: updateQuestionGroupOrder
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Actions);

/***/ },

/***/ "./src/AjaxHelper.js"
/*!***************************!*\
  !*** ./src/AjaxHelper.js ***!
  \***************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * AjaxHelper - Vanilla JS replacement for runAjax mixin
 * Provides Promise-based AJAX methods using jQuery
 */
const AjaxHelper = function () {
  'use strict';

  /**
   * Core AJAX request method
   * @param {string} uri - Request URL
   * @param {Object} data - Request data
   * @param {string} method - HTTP method
   * @returns {Promise}
   */
  function _runAjax(uri, data, method) {
    data = data || {};
    method = method || 'get';
    return new Promise(function (resolve, reject) {
      if (typeof $ === 'undefined') {
        reject('JQUERY NOT AVAILABLE!');
        return;
      }
      $.ajax({
        url: uri,
        method: method,
        data: data,
        dataType: 'json',
        success: function (response, status, xhr) {
          resolve({
            success: true,
            data: response,
            transferStatus: status,
            xhr: xhr
          });
        },
        error: function (xhr, status, error) {
          const responseData = xhr.responseJSON || xhr.responseText;
          reject({
            success: false,
            error: error,
            data: responseData,
            transferStatus: status,
            xhr: xhr
          });
        }
      });
    });
  }

  /**
   * POST request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function post(uri, data) {
    return _runAjax(uri, data, 'post');
  }

  /**
   * GET request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function get(uri, data) {
    return _runAjax(uri, data, 'get');
  }

  /**
   * DELETE request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function deleteRequest(uri, data) {
    return _runAjax(uri, data, 'delete');
  }

  /**
   * PUT request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function put(uri, data) {
    return _runAjax(uri, data, 'put');
  }
  return {
    post: post,
    get: get,
    delete: deleteRequest,
    put: put
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AjaxHelper);

/***/ },

/***/ "./src/StateManager.js"
/*!*****************************!*\
  !*** ./src/StateManager.js ***!
  \*****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * StateManager - Vanilla JS replacement for Vuex store
 * Manages sidebar state with sessionStorage persistence
 *
 * Unified implementation used across admin and global sidepanels
 */
const StateManager = function () {
  'use strict';

  let state = {};
  let storageKey = '';
  let listeners = [];
  let mutations = {};
  let getters = {};

  /**
   * Initialize state with default values
   * @param {Object} config - Configuration object
   * @param {string} config.storagePrefix - Storage key prefix (e.g., 'limesurveyadminsidepanel')
   * @param {string|number} [config.userid] - User ID
   * @param {string|number} [config.surveyid] - Survey ID (optional)
   * @param {Object} config.defaultState - Default state object
   * @param {Object} [config.mutations] - Mutations object (optional)
   * @param {Object} [config.getters] - Getters object (optional)
   */
  function init(config) {
    if (!config || !config.storagePrefix || !config.defaultState) {
      console.error('StateManager.init requires storagePrefix and defaultState');
      return state;
    }

    // Build storage key
    storageKey = config.storagePrefix;
    if (config.userid) {
      storageKey += '_' + config.userid;
    }
    if (config.surveyid) {
      storageKey += '_' + config.surveyid;
    }

    // Set mutations and getters
    mutations = config.mutations || {};
    getters = config.getters || {};

    // Try to load from sessionStorage
    const savedState = loadFromStorage();
    state = Object.assign({}, config.defaultState, savedState);
    return state;
  }

  /**
   * Load state from sessionStorage
   */
  function loadFromStorage() {
    try {
      const saved = sessionStorage.getItem(storageKey);
      if (saved) {
        return JSON.parse(saved);
      }
    } catch (e) {
      console.warn('Failed to load state from sessionStorage:', e);
    }
    return {};
  }

  /**
   * Save state to sessionStorage
   */
  function saveToStorage() {
    try {
      sessionStorage.setItem(storageKey, JSON.stringify(state));
    } catch (e) {
      console.warn('Failed to save state to sessionStorage:', e);
    }
  }

  /**
   * Get current state value
   * @param {string} [key] - State key to retrieve (omit to get entire state)
   * @returns {*} State value or entire state object
   */
  function get(key) {
    if (key) {
      return state[key];
    }
    return state;
  }

  /**
   * Set state value and persist
   * @param {string} key - State key
   * @param {*} value - New value
   */
  function set(key, value) {
    const oldValue = state[key];
    state[key] = value;
    saveToStorage();
    notifyListeners(key, value, oldValue);
  }

  /**
   * Subscribe to state changes
   * @param {Function} callback - Callback function (key, newValue, oldValue)
   * @returns {Function} Unsubscribe function
   */
  function subscribe(callback) {
    listeners.push(callback);
    return function unsubscribe() {
      listeners = listeners.filter(l => l !== callback);
    };
  }

  /**
   * Notify listeners of state change
   * @param {string} key - Changed state key
   * @param {*} newValue - New value
   * @param {*} oldValue - Old value
   */
  function notifyListeners(key, newValue, oldValue) {
    listeners.forEach(function (listener) {
      listener(key, newValue, oldValue);
    });
  }

  /**
   * Commit a mutation
   * @param {string} mutation - Mutation name
   * @param {*} payload - Mutation payload
   */
  function commit(mutation, payload) {
    if (mutations[mutation]) {
      mutations[mutation](payload);
    } else {
      console.warn('Unknown mutation:', mutation);
    }
  }

  /**
   * Get a computed value from getters
   * @param {string} getter - Getter name
   * @returns {*} Computed value
   */
  function getComputed(getter) {
    if (getters[getter]) {
      return getters[getter]();
    }
    console.warn('Unknown getter:', getter);
    return undefined;
  }

  /**
   * Register mutations (can be called after init to add more mutations)
   * @param {Object} newMutations - Mutations to register
   */
  function registerMutations(newMutations) {
    Object.assign(mutations, newMutations);
  }

  /**
   * Register getters (can be called after init to add more getters)
   * @param {Object} newGetters - Getters to register
   */
  function registerGetters(newGetters) {
    Object.assign(getters, newGetters);
  }
  return {
    init: init,
    get: get,
    set: set,
    commit: commit,
    getComputed: getComputed,
    subscribe: subscribe,
    registerMutations: registerMutations,
    registerGetters: registerGetters,
    getState: function () {
      return state;
    }
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (StateManager);

/***/ },

/***/ "./src/UIHelpers.js"
/*!**************************!*\
  !*** ./src/UIHelpers.js ***!
  \**************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * UIHelpers - Utility functions for UI operations
 */
const UIHelpers = function () {
  'use strict';

  /**
   * Translate a string using SideMenuData translations
   * @param {string} str - String to translate
   * @returns {string}
   */
  function translate(str) {
    if (window.SideMenuData && window.SideMenuData.translate) {
      return window.SideMenuData.translate[str] || str;
    }
    return str;
  }

  /**
   * Re-initialize tooltips
   */
  function redoTooltips() {
    if (window.LS && window.LS.doToolTip) {
      window.LS.doToolTip();
    }
  }

  /**
   * Convert HTML entities back to characters
   * Uses the same character mapping as the original Vue component
   * @param {string} string
   * @returns {string}
   */
  function reConvertHTML(string) {
    if (!string) return '';

    // HTML entity decode map (subset of commonly used)
    var entityMap = {
      '&#039;': "'",
      '&copy;': '\u00A9',
      '&reg;': '\u00AE',
      '&#36;': '$',
      '&#37;': '%',
      '&#64;': '@',
      '&Agrave;': '\u00C0',
      '&Aacute;': '\u00C1',
      '&Acirc;': '\u00C2',
      '&Atilde;': '\u00C3',
      '&Auml;': '\u00C4',
      '&Aring;': '\u00C5',
      '&AElig;': '\u00C6',
      '&Ccedil;': '\u00C7',
      '&Egrave;': '\u00C8',
      '&Eacute;': '\u00C9',
      '&Ecirc;': '\u00CA',
      '&Euml;': '\u00CB',
      '&Igrave;': '\u00CC',
      '&Iacute;': '\u00CD',
      '&Icirc;': '\u00CE',
      '&Iuml;': '\u00CF',
      '&ETH;': '\u00D0',
      '&Ntilde;': '\u00D1',
      '&Otilde;': '\u00D5',
      '&Ouml;': '\u00D6',
      '&Oslash;': '\u00D8',
      '&Ugrave;': '\u00D9',
      '&Uacute;': '\u00DA',
      '&Ucirc;': '\u00DB',
      '&Uuml;': '\u00DC',
      '&Yacute;': '\u00DD',
      '&THORN;': '\u00DE',
      '&szlig;': '\u00DF',
      '&agrave;': '\u00E0',
      '&aacute;': '\u00E1',
      '&acirc;': '\u00E2',
      '&atilde;': '\u00E3',
      '&auml;': '\u00E4',
      '&aring;': '\u00E5',
      '&aelig;': '\u00E6',
      '&ccedil;': '\u00E7',
      '&egrave;': '\u00E8',
      '&eacute;': '\u00E9',
      '&ecirc;': '\u00EA',
      '&euml;': '\u00EB',
      '&igrave;': '\u00EC',
      '&iacute;': '\u00ED',
      '&icirc;': '\u00EE',
      '&iuml;': '\u00EF',
      '&eth;': '\u00F0',
      '&ntilde;': '\u00F1',
      '&ograve;': '\u00F2',
      '&oacute;': '\u00F3',
      '&ocirc;': '\u00F4',
      '&otilde;': '\u00F5',
      '&ouml;': '\u00F6',
      '&oslash;': '\u00F8',
      '&ugrave;': '\u00F9',
      '&uacute;': '\u00FA',
      '&ucirc;': '\u00FB',
      '&yacute;': '\u00FD',
      '&thorn;': '\u00FE',
      '&yuml;': '\u00FF'
    };
    for (var entity in entityMap) {
      if (entityMap.hasOwnProperty(entity)) {
        string = string.split(entity).join(entityMap[entity]);
      }
    }

    // Also handle numeric entities
    string = string.replace(/&#(\d+);/g, function (match, dec) {
      return String.fromCharCode(dec);
    });
    return string;
  }

  /**
   * Render a menu icon based on type
   * @param {string} iconType
   * @param {string} icon
   * @returns {string} HTML string
   */
  function renderMenuIcon(iconType, icon) {
    if (!icon) return '';
    switch (iconType) {
      case 'fontawesome':
        return '<i class="fa fa-' + icon + '">&nbsp;</i>';
      case 'image':
        return '<img width="32px" src="' + icon + '" />';
      case 'iconclass':
      case 'remix':
        return '<i class="' + icon + '">&nbsp;</i>';
      default:
        return '';
    }
  }

  /**
   * Create a loader widget HTML
   * @param {string} id
   * @param {string} extraClass
   * @returns {string}
   */
  function createLoaderWidget(id, extraClass) {
    id = id || 'loader-' + Math.floor(1000 * Math.random());
    extraClass = extraClass || '';
    return '<div id="' + id + '" class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">' + '<div class="ls-flex align-content-center align-items-center">' + '<div class="loader-adminpanel text-center ' + extraClass + '">' + '<div class="contain-pulse animate-pulse">' + '<div class="square"></div>' + '<div class="square"></div>' + '<div class="square"></div>' + '<div class="square"></div>' + '</div>' + '</div>' + '</div>' + '</div>';
  }

  /**
   * Parse integer or return default value
   * @param {*} val
   * @param {number} defaultVal
   * @returns {number}
   */
  function parseIntOr(val, defaultVal) {
    defaultVal = defaultVal !== undefined ? defaultVal : 999999;
    var intVal = parseInt(val, 10);
    if (isNaN(intVal)) {
      return defaultVal;
    }
    return intVal;
  }

  /**
   * Check if we're in mobile view
   * @returns {boolean}
   */
  function useMobileView() {
    return window.innerWidth < 768;
  }

  /**
   * @param {string} str
   * @returns {string}
   */
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  return {
    translate: translate,
    redoTooltips: redoTooltips,
    reConvertHTML: reConvertHTML,
    renderMenuIcon: renderMenuIcon,
    createLoaderWidget: createLoaderWidget,
    parseIntOr: parseIntOr,
    useMobileView: useMobileView,
    escapeHtml: escapeHtml
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (UIHelpers);

/***/ },

/***/ "./src/components/QuestionExplorer.js"
/*!********************************************!*\
  !*** ./src/components/QuestionExplorer.js ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _Actions_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Actions.js */ "./src/Actions.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");
/**
 * QuestionExplorer - Question groups explorer component
 * Matches original _questionsgroups.vue implementation
 */



class QuestionExplorer {
  constructor() {
    this.container = null;
    this.onOrderChange = null;

    // Drag and drop state - matching Vue component data()
    this.active = [];
    this.questiongroupDragging = false;
    this.draggedQuestionGroup = null;
    this.questionDragging = false;
    this.draggedQuestion = null;
    this.draggedQuestionsGroup = null;
    this.orderChanged = false; // Track if order actually changed during drag
  }

  /**
   * Render the question explorer
   */
  render(containerEl, loading, orderChangeCallback) {
    this.container = containerEl;
    this.onOrderChange = orderChangeCallback;
    if (!this.container) return;
    this.active = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questionGroupOpenArray') || [];
    this.renderExplorer();
  }

  /**
   * Check if group is open
   */
  isOpen(gid) {
    if (this.questiongroupDragging === true) return false;
    return LS.ld.indexOf(this.active, gid) !== -1;
  }

  /**
   * Check if group is active
   */
  isActive(gid) {
    return gid == _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastQuestionGroupOpen');
  }

  /**
   * Get question group item classes - matching Vue questionGroupItemClasses()
   */
  questionGroupItemClasses(questiongroup) {
    var classes = '';
    classes += this.isOpen(questiongroup.gid) ? ' selected ' : ' ';
    classes += this.isActive(questiongroup.gid) ? ' activated ' : ' ';
    if (this.draggedQuestionGroup !== null) {
      classes += this.draggedQuestionGroup.gid === questiongroup.gid ? ' dragged' : ' ';
    }
    return classes;
  }

  /**
   * Get question item classes - matching Vue questionItemClasses()
   */
  questionItemClasses(question) {
    var classes = '';
    classes += _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastQuestionOpen') === question.qid ? 'selected activated' : 'selected ';
    if (this.draggedQuestion !== null) {
      classes += this.draggedQuestion.qid === question.qid ? ' dragged' : ' ';
    }
    return classes;
  }

  /**
   * Render the explorer content - matching Vue template exactly
   */
  renderExplorer() {
    if (!this.container) return;
    var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
    var allowOrganizer = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('allowOrganizer') === null ? 1 : _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('allowOrganizer') === 1;
    var surveyIsActive = window.SideMenuData.isActive;
    var createQuestionGroupLink = window.SideMenuData.createQuestionGroupLink;
    var createQuestionLink = window.SideMenuData.createQuestionLink;
    var createQuestionAllowed = questiongroups.length > 0 && createQuestionLink && createQuestionLink.length > 1;
    var createQuestionAllowedClass = createQuestionAllowed ? '' : 'disabled';
    var createQuestionGroupAllowedClass = createQuestionGroupLink && createQuestionGroupLink.length > 1 ? '' : 'disabled';
    var orderedQuestionGroups = LS.ld.orderBy(questiongroups, function (a) {
      return _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].parseIntOr(a.group_order, 999999);
    }, ['asc']);
    var itemWidth = parseInt(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidebarwidth')) - 120 + 'px';
    var html = '<div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane h-100 pt-2">';

    // Toolbar buttons
    html += '<div class="ls-flex-row button-sub-bar mb-2">';
    html += '<div class="scoped-toolbuttons-right me-2">';
    html += '<button class="btn btn-sm btn-outline-secondary toggle-organizer-btn" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate(allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle') + '">';
    html += '<i class="' + (allowOrganizer ? 'ri-lock-unlock-fill' : 'ri-lock-fill') + '"></i>';
    html += '</button>';
    html += '<button class="btn btn-sm btn-outline-secondary me-2 collapse-all-btn" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('collapseAll') + '">';
    html += '<i class="ri-link-unlink"></i>';
    html += '</button>';
    html += '</div>';
    html += '</div>';

    // Create buttons
    html += '<div class="ls-flex-row wrap align-content-center align-items-center button-sub-bar">';
    html += '<div class="scoped-toolbuttons-left mb-2 d-flex align-items-center">';
    var createQuestionTooltip = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate(createQuestionAllowed ? '' : 'deactivateSurvey');
    html += '<div class="create-question px-3" data-bs-toggle="tooltip" data-bs-placement="top" title="' + createQuestionTooltip + '">';
    html += '<a id="adminsidepanel__sidebar--selectorCreateQuestion" href="' + this.createFullQuestionLink(createQuestionLink) + '" class="btn btn-primary pjax ' + createQuestionAllowedClass + '">';
    html += '<i class="ri-add-circle-fill"></i>&nbsp;' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('createQuestion');
    html += '</a>';
    html += '</div>';
    html += '<div data-bs-toggle="tooltip" data-bs-placement="top" title="' + createQuestionTooltip + '">';
    html += '<a id="adminsidepanel__sidebar--selectorCreateQuestionGroup" href="' + createQuestionGroupLink + '" class="btn btn-secondary pjax ' + createQuestionGroupAllowedClass + '">';
    html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('createPage');
    html += '</a>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Question groups list
    html += '<div class="ls-flex-row ls-space padding all-0">';
    html += '<ul class="list-group col-12 questiongroup-list-group">';
    orderedQuestionGroups.forEach(questiongroup => {
      html += this.renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, itemWidth);
    });
    html += '</ul>';
    html += '</div>';
    html += '</div>';
    this.container.innerHTML = html;
    this.bindEvents();
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].redoTooltips();
  }
  createFullQuestionLink(baseLink) {
    if (!baseLink) return '#';
    if (LS.reparsedParameters && LS.reparsedParameters().combined && LS.reparsedParameters().combined.gid) {
      return baseLink + '&gid=' + LS.reparsedParameters().combined.gid;
    }
    return baseLink;
  }

  /**
   * Render question group - matching Vue template
   */
  renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
    var classes = 'list-group-item ls-flex-column' + this.questionGroupItemClasses(questiongroup);
    var isGroupOpen = this.isOpen(questiongroup.gid);
    var groupActivated = this.isActive(questiongroup.gid);
    var html = '<li class="' + classes + '" data-gid="' + questiongroup.gid + '">';

    // Question group header
    html += '<div class="q-group d-flex nowrap ls-space padding right-5 bottom-5 bg-white ms-2 p-2" data-gid="' + questiongroup.gid + '">';

    // Drag handle
    html += '<div class="bigIcons dragPointer me-1 questiongroup-drag-handle ' + (allowOrganizer ? '' : 'disabled') + '" ';
    html += (allowOrganizer ? 'draggable="true"' : '') + ' data-gid="' + questiongroup.gid + '">';
    html += '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
    html += '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>';
    html += '</svg>';
    html += '</div>';

    // Expand/collapse toggle
    var rotateStyle = isGroupOpen ? 'transform: rotate(90deg)' : 'transform: rotate(0deg)';
    html += '<div class="cursor-pointer me-1 toggle-questiongroup" data-gid="' + questiongroup.gid + '" style="' + rotateStyle + '">';
    html += '<i class="ri-arrow-right-s-fill"></i>';
    html += '</div>';

    // Question group name
    html += '<div class="w-100 position-relative">';
    html += '<div class="cursor-pointer">';
    html += '<a class="d-flex pjax" href="' + questiongroup.link + '">';
    html += '<span class="question_text_ellipsize" style="max-width: ' + itemWidth + '">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(questiongroup.group_name) + '</span>';
    html += '</a>';
    html += '</div>';

    // Dropdown and badge
    html += '<div class="position-absolute top-0 d-flex align-items-center" style="right:5px">';
    html += '<div class="toggle-questiongroup" data-gid="' + questiongroup.gid + '">';
    html += '<span class="badge reverse-color ls-space margin right-5">' + (questiongroup.questions ? questiongroup.questions.length : 0) + '</span>';
    html += '</div>';

    // Dropdown menu - always render, visibility controlled by hover class
    if (questiongroup.groupDropdown) {
      var dropdownStyle = groupActivated ? '' : ' style="display:none"';
      html += '<div class="dropdown questiongroup-dropdown' + (groupActivated ? ' active' : '') + '"' + dropdownStyle + '>';
      html += '<div class="ls-questiongroup-tools cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">';
      html += '<i class="ri-more-fill"></i>';
      html += '</div>';
      html += '<ul class="dropdown-menu">';
      for (var key in questiongroup.groupDropdown) {
        if (!questiongroup.groupDropdown.hasOwnProperty(key)) continue;
        var value = questiongroup.groupDropdown[key];
        if (key !== 'delete') {
          html += '<li>';
          html += '<a class="dropdown-item" id="' + (value.id || '') + '" href="' + value.url + '">';
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        } else {
          html += '<li class="' + (value.disabled ? 'disabled' : '') + '">';
          if (!value.disabled) {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-btnclass="btn-danger" data-title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataTitle || '') + '" data-btntext="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataBtnText || '') + '" data-onclick="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataOnclick || '') + '" data-message="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataMessage || '') + '">';
          } else {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.title || '') + '">';
          }
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        }
      }
      html += '</ul>';
      html += '</div>';
    }
    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Questions list (if open) - matching Vue transition
    if (isGroupOpen && questiongroup.questions) {
      html += this.renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, itemWidth);
    }
    html += '</li>';
    return html;
  }

  /**
   * Render questions list
   */
  renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
    var orderedQuestions = LS.ld.orderBy(questiongroup.questions, function (a) {
      return _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].parseIntOr(a.question_order, 999999);
    }, ['asc']);
    var html = '<ul class="list-group background-muted padding-left question-question-list" style="padding-right:15px">';
    orderedQuestions.forEach(question => {
      html += this.renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, itemWidth);
    });
    html += '</ul>';
    return html;
  }

  /**
   * Render single question - matching Vue template exactly
   */
  renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
    var classes = 'list-group-item question-question-list-item ls-flex-row align-itmes-flex-start ' + this.questionItemClasses(question);
    var itemActivated = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastQuestionOpen') === question.qid;
    // Always show dropdown HTML, use CSS/JS hover to control visibility
    var showDropdown = true;
    var questionHasCondition = question.relevance !== '1';
    var html = '<li class="' + classes + '" data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '" data-is-hidden="' + question.hidden + '" data-questiontype="' + question.type + '" data-has-condition="' + questionHasCondition + '" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(question.question_flat) + '" data-bs-toggle="tooltip">';

    // Drag handle (only if survey not active)
    if (!surveyIsActive) {
      html += '<div class="margin-right bigIcons dragPointer question-question-list-item-drag question-drag-handle ' + (allowOrganizer ? '' : 'disabled') + '" ';
      html += (allowOrganizer ? 'draggable="true"' : '') + ' data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '">';
      html += '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
      html += '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>';
      html += '</svg>';
      html += '</div>';
    }

    // Question link
    html += '<a href="' + question.link + '" class="col-9 pjax question-question-list-item-link display-as-container question-link" data-qid="' + question.qid + '" data-gid="' + question.gid + '">';
    html += '<span class="question_text_ellipsize ' + (question.hidden ? 'question-hidden' : '') + '" style="width: ' + itemWidth + '">';
    html += '[' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(question.title) + '] &rsaquo; ' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(question.question_flat);
    html += '</span>';
    html += '</a>';

    // Question dropdown - always render, visibility controlled by hover class
    if (question.questionDropdown) {
      var dropdownStyle = itemActivated ? 'right:10px' : 'right:10px;display:none';
      html += '<div class="dropdown question-dropdown position-absolute' + (itemActivated ? ' active' : '') + '" style="' + dropdownStyle + '">';
      html += '<div class="ls-question-tools ms-auto position-relative cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">';
      html += '<i class="ri-more-fill"></i>';
      html += '</div>';
      html += '<ul class="dropdown-menu">';
      for (var key in question.questionDropdown) {
        if (!question.questionDropdown.hasOwnProperty(key)) continue;
        var value = question.questionDropdown[key];
        if (key !== 'delete' && !(key === 'language' && Array.isArray(value))) {
          var isDisabled = key === 'editDefault' && value.active === 0;
          html += '<li>';
          html += '<a class="dropdown-item ' + (isDisabled ? 'disabled' : '') + '" id="' + (value.id || '') + '" href="' + (isDisabled ? '#' : value.url) + '">';
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        } else if (key === 'delete') {
          html += '<li class="' + (value.disabled ? 'disabled' : '') + '">';
          if (!value.disabled) {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-btnclass="btn-danger" data-title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataTitle || '') + '" data-btntext="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataBtnText || '') + '" data-onclick="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataOnclick || '') + '" data-message="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataMessage || '') + '">';
          } else {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.title || '') + '">';
          }
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        } else if (key === 'language' && Array.isArray(value)) {
          html += '<li role="separator" class="dropdown-divider"></li>';
          html += '<li class="dropdown-header">Survey logic file</li>';
          value.forEach(function (language) {
            html += '<li>';
            html += '<a class="dropdown-item" id="' + (language.id || '') + '" href="' + language.url + '">';
            html += '<span class="' + (language.icon || '') + '"></span> ' + language.label;
            html += '</a>';
            html += '</li>';
          });
        }
      }
      html += '</ul>';
      html += '</div>';
    }
    html += '</li>';
    return html;
  }

  /**
   * Add to active array
   */
  addActive(questionGroupId) {
    if (!this.isOpen(questionGroupId)) {
      this.active.push(questionGroupId);
    }
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('questionGroupOpenArray', this.active);
  }

  /**
   * Toggle question group - matching Vue toggleQuestionGroup()
   */
  toggleQuestionGroup(questiongroup) {
    if (!this.isOpen(questiongroup.gid)) {
      this.addActive(questiongroup.gid);
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastQuestionGroupOpen', questiongroup);
    } else {
      var newActive = this.active.filter(function (gid) {
        return gid !== questiongroup.gid;
      });
      this.active = newActive.slice();
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('questionGroupOpenArray', this.active);
    }
    this.renderExplorer();
  }

  /**
   * Open question - matching Vue openQuestion()
   */
  openQuestion(question) {
    this.addActive(question.gid);
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastQuestionOpen', question);
    $(document).trigger('pjax:load', {
      url: question.link
    });
  }

  /**
   * Collapse all
   */
  collapseAll() {
    this.active = [];
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('questionGroupOpenArray', this.active);
    this.renderExplorer();
  }

  /**
   * Bind events
   */
  bindEvents() {
    if (!this.container) return;
    var $container = $(this.container);
    $container.off('.qe');

    // Toggle organizer
    $container.on('click.qe', '.toggle-organizer-btn', e => {
      e.preventDefault();

      // Update server and re-render
      _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].unlockLockOrganizer().then(() => {
        // Toggle the state locally
        this.renderExplorer();
      });
    });

    // Collapse all
    $container.on('click.qe', '.collapse-all-btn', e => {
      e.preventDefault();
      this.collapseAll();
    });

    // Toggle question group
    $container.on('click.qe', '.toggle-questiongroup', e => {
      e.preventDefault();
      e.stopPropagation();
      var gid = $(e.currentTarget).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var group = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (group) {
        this.toggleQuestionGroup(group);
      }
    });

    // Question link click - matching Vue @click.stop.prevent="openQuestion(question)"
    $container.on('click.qe', '.question-link', e => {
      e.preventDefault();
      e.stopPropagation();
      var qid = $(e.currentTarget).data('qid');
      var gid = $(e.currentTarget).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var group = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (group && group.questions) {
        var question = group.questions.find(function (q) {
          return q.qid === qid;
        });
        if (question) {
          this.openQuestion(question);
        }
      }
    });

    // Hover events for dropdown visibility - matching Vue mouseover/mouseleave behavior
    // Show dropdown on question group hover
    $container.on('mouseover.qe', '.q-group[data-gid]', function (e) {
      $(this).find('.questiongroup-dropdown:not(.active)').show();
    });
    $container.on('mouseleave.qe', '.q-group[data-gid]', function (e) {
      $(this).find('.questiongroup-dropdown:not(.active)').hide();
    });

    // Show dropdown on question hover - use mouseover to match Vue behavior
    $container.on('mouseover.qe', '.question-question-list-item', function (e) {
      $(this).find('.question-dropdown:not(.active)').show();
    });
    $container.on('mouseleave.qe', '.question-question-list-item', function (e) {
      $(this).find('.question-dropdown:not(.active)').hide();
    });

    // Drag events
    this.bindDragEvents($container);
  }

  /**
   * Bind drag events - matching Vue drag methods exactly
   * IMPORTANT: Avoid calling renderExplorer() during active drag to maintain smooth operation
   */
  bindDragEvents($container) {
    // Question group drag start - matching startDraggingGroup
    $container.on('dragstart.qe', '.questiongroup-drag-handle[draggable="true"]', e => {
      var gid = $(e.currentTarget).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      this.draggedQuestionGroup = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      this.questiongroupDragging = true;
      this.orderChanged = false; // Reset flag at start of drag
      e.originalEvent.dataTransfer.setData('text/plain', 'node');
      // Add dragged class directly without re-rendering
      $(e.currentTarget).closest('.list-group-item').addClass('dragged');
    });

    // Question group drag end - matching endDraggingGroup
    $container.on('dragend.qe', '.questiongroup-drag-handle', () => {
      if (this.draggedQuestionGroup !== null) {
        this.draggedQuestionGroup = null;
        this.questiongroupDragging = false;
        // Only trigger order update if order actually changed
        if (this.orderChanged && this.onOrderChange) {
          this.onOrderChange();
        }
        this.orderChanged = false; // Reset flag
        this.renderExplorer();
      }
    });

    // Question group dragenter - matching dragoverQuestiongroup
    $container.on('dragenter.qe', '.list-group-item[data-gid]', e => {
      e.preventDefault();
      var gid = $(e.currentTarget).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var questiongroupObject = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (this.questiongroupDragging && this.draggedQuestionGroup && questiongroupObject) {
        var targetPosition = parseInt(questiongroupObject.group_order);
        var currentPosition = parseInt(this.draggedQuestionGroup.group_order);
        if (Math.abs(targetPosition - currentPosition) === 1) {
          questiongroupObject.group_order = currentPosition;
          this.draggedQuestionGroup.group_order = targetPosition;
          _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateQuestiongroups', questiongroups);
          this.orderChanged = true; // Mark that order has changed
          // Don't re-render during drag - wait for dragend
        }
      } else if (this.questionDragging && this.draggedQuestion && questiongroupObject) {
        if (window.SideMenuData.isActive) return;
        this.addActive(questiongroupObject.gid);
        if (this.draggedQuestion.gid !== questiongroupObject.gid) {
          var removedFromInitial = LS.ld.remove(this.draggedQuestionsGroup.questions, q => {
            return q.qid === this.draggedQuestion.qid;
          });
          if (removedFromInitial.length > 0) {
            this.draggedQuestion.question_order = null;
            questiongroupObject.questions.push(this.draggedQuestion);
            this.draggedQuestion.gid = questiongroupObject.gid;
            if (questiongroupObject.group_order > this.draggedQuestionsGroup.group_order) {
              this.draggedQuestion.question_order = 0;
              LS.ld.each(questiongroupObject.questions, function (q) {
                q.question_order = parseInt(q.question_order) + 1;
              });
            } else {
              this.draggedQuestion.question_order = this.draggedQuestionsGroup.questions.length + 1;
            }
            this.draggedQuestionsGroup = questiongroupObject;
            _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateQuestiongroups', questiongroups);
            this.orderChanged = true; // Mark that order has changed
            // Don't re-render during drag - wait for dragend
          }
        }
      }
    });

    // Question drag start - matching startDraggingQuestion
    $container.on('dragstart.qe', '.question-drag-handle[draggable="true"]', e => {
      var qid = $(e.currentTarget).data('qid');
      var gid = $(e.currentTarget).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var group = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (group && group.questions) {
        this.draggedQuestion = group.questions.find(function (q) {
          return q.qid === qid;
        });
        this.draggedQuestionsGroup = group;
        this.questionDragging = true;
        this.orderChanged = false; // Reset flag at start of drag
        e.originalEvent.dataTransfer.setData('application/node', 'node');
        // Add dragged class directly without re-rendering
        $(e.currentTarget).closest('.question-question-list-item').addClass('dragged');
      }
    });

    // Question drag end - matching endDraggingQuestion
    $container.on('dragend.qe', '.question-drag-handle', () => {
      if (this.questionDragging) {
        this.questionDragging = false;
        this.draggedQuestion = null;
        this.draggedQuestionsGroup = null;
        // Only trigger order update if order actually changed
        if (this.orderChanged && this.onOrderChange) {
          this.onOrderChange();
        }
        this.orderChanged = false; // Reset flag
        this.renderExplorer();
      }
    });

    // Question dragenter - matching dragoverQuestion
    $container.on('dragenter.qe', '.question-question-list-item', e => {
      e.preventDefault();
      var qid = $(e.currentTarget).data('qid');
      var gid = $(e.currentTarget).data('gid');
      if (this.questionDragging && this.draggedQuestion) {
        if (window.SideMenuData.isActive && this.draggedQuestion.gid !== gid) return;
        var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
        var group = questiongroups.find(function (g) {
          return g.gid === gid;
        });
        if (group && group.questions) {
          var questionObject = group.questions.find(function (q) {
            return q.qid === qid;
          });
          if (questionObject && questionObject.qid !== this.draggedQuestion.qid) {
            var orderSwap = questionObject.question_order;
            questionObject.question_order = this.draggedQuestion.question_order;
            this.draggedQuestion.question_order = orderSwap;
            _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateQuestiongroups', questiongroups);
            this.orderChanged = true; // Mark that order has changed
            // Don't re-render during drag - wait for dragend
          }
        }
      }
    });

    // Allow drop
    $container.on('dragover.qe', '.list-group-item, .question-question-list-item', function (e) {
      e.preventDefault();
    });
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (QuestionExplorer);

/***/ },

/***/ "./src/components/QuickMenu.js"
/*!*************************************!*\
  !*** ./src/components/QuickMenu.js ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");
/**
 * QuickMenu - Collapsed menu component (vanilla JS)
 * Replaces _quickmenu.vue
 */


class QuickMenu {
  constructor() {
    this.container = null;
    this.isLoading = true;

    // Bind methods
    this.handleMenuItemClick = this.handleMenuItemClick.bind(this);
  }

  /**
   * Render the quick menu
   * @param {HTMLElement} containerEl
   * @param {boolean} loading
   */
  render(containerEl, loading) {
    this.container = containerEl;
    if (!this.container) return;

    // Menus are loaded from SideMenuData.basemenus in Sidebar.init()
    // Don't make extra AJAX calls - just render what's in state
    this.isLoading = false;
    this.renderMenu();
  }

  /**
   * Render the menu content
   */
  renderMenu() {
    if (!this.container) return;
    const collapsedmenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('collapsedmenus') || [];

    // Sort menus by ordering
    const sortedMenus = LS.ld.orderBy(collapsedmenus, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
    let html = '<div class="ls-flex-column fill">';
    if (this.isLoading) {
      html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].createLoaderWidget('quickmenuLoadingIcon', 'loader-quickmenu');
    } else {
      sortedMenus.forEach(menu => {
        html += '<div class="ls-space margin top-10" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].escapeHtml(menu.title) + '">';
        html += '<div class="btn-group-vertical ls-space padding right-10">';
        const sortedEntries = this.sortMenuEntries(menu.entries);
        sortedEntries.forEach(menuItem => {
          html += this.renderMenuItem(menuItem);
        });
        html += '</div>';
        html += '</div>';
      });
    }
    html += '</div>';
    this.container.innerHTML = html;
    this.bindEvents();
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].redoTooltips();
  }

  /**
   * Sort menu entries by ordering
   * @param {Array} entries
   * @returns {Array}
   */
  sortMenuEntries(entries) {
    return LS.ld.orderBy(entries, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
  }

  /**
   * Render a single menu item
   * @param {Object} menuItem
   * @returns {string}
   */
  renderMenuItem(menuItem) {
    const classes = this.compileEntryClasses(menuItem);
    const tooltip = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].reConvertHTML(menuItem.menu_description);
    const target = menuItem.link_external ? '_blank' : '_self';
    let html = '<a href="' + menuItem.link + '"' + ' title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].escapeHtml(tooltip) + '"' + ' target="' + target + '"' + ' data-bs-toggle="tooltip"' + ' class="btn ' + classes + '"' + ' data-menu-item-id="' + menuItem.id + '">';

    // Render icon based on type
    html += this.renderIcon(menuItem);
    html += '</a>';
    return html;
  }

  /**
   * Render icon based on type
   * @param {Object} menuItem
   * @returns {string}
   */
  renderIcon(menuItem) {
    const iconType = menuItem.menu_icon_type;
    const icon = menuItem.menu_icon;
    switch (iconType) {
      case 'fontawesome':
        return '<i class="quickmenuIcon fa fa-' + icon + '"></i>';
      case 'image':
        return '<img width="32px" src="' + icon + '" />';
      case 'iconclass':
      case 'remix':
        return '<i class="quickmenuIcon ' + icon + '"></i>';
      default:
        return '';
    }
  }

  /**
   * Compile CSS classes for menu entry
   * @param {Object} menuItem
   * @returns {string}
   */
  compileEntryClasses(menuItem) {
    let classes = '';
    if (_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastMenuItemOpen') === menuItem.id) {
      classes += ' btn-primary ';
    } else {
      classes += ' btn-outline-secondary ';
    }
    if (!menuItem.link_external) {
      classes += ' pjax ';
    }
    return classes;
  }

  /**
   * Bind event handlers
   */
  bindEvents() {
    if (!this.container) return;

    // Menu item click
    $(this.container).off('click', '.btn').on('click', '.btn', this.handleMenuItemClick);
  }

  /**
   * Handle menu item click
   */
  handleMenuItemClick(e) {
    const menuItemId = $(e.currentTarget).data('menu-item-id');

    // Update state
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastMenuItemOpen', {
      id: menuItemId,
      menu_id: null
    });

    // Re-render to update selected state
    this.renderMenu();
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (QuickMenu);

/***/ },

/***/ "./src/components/SideMenu.js"
/*!************************************!*\
  !*** ./src/components/SideMenu.js ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");
/**
 * SideMenu - Side menu component (vanilla JS)
 * Replaces _sidemenu.vue and _submenu.vue
 */


class SideMenu {
  constructor() {
    this.container = null;
    this.isLoading = true;

    // Bind methods
    this.handleMenuItemClick = this.handleMenuItemClick.bind(this);
  }

  /**
   * Render the side menu
   * @param {HTMLElement} containerEl
   * @param {boolean} loading
   */
  render(containerEl, loading) {
    this.container = containerEl;
    if (!this.container) return;

    // Menus are loaded from SideMenuData.basemenus in Sidebar.init()
    // Don't make extra AJAX calls - just render what's in state
    this.isLoading = false;
    this.renderMenu();
  }

  /**
   * Render the menu content
   */
  renderMenu() {
    if (!this.container) return;
    const sidemenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidemenus') || [];

    // Sort menus by ordering
    const sortedMenus = LS.ld.orderBy(sidemenus, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
    let html = '<div class="ls-flex-column menu-pane overflow-enabled ls-space all-0 py-4 bg-white">';
    if (this.isLoading) {
      html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].createLoaderWidget('sidemenuLoaderWidget', '');
    } else if (sortedMenus.length >= 2) {
      // First menu (usually main settings)
      html += '<div title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].escapeHtml(sortedMenus[0].title) + '" id="' + sortedMenus[0].id + '" class="ls-flex-row wrap ls-space padding all-0">';
      html += this.renderSubmenu(sortedMenus[0]);
      html += '</div>';

      // Second menu (with label)
      html += '<div title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].escapeHtml(sortedMenus[1].title) + '" id="' + sortedMenus[1].id + '" class="ls-flex-row wrap ls-space padding all-0">';
      html += '<label class="menu-label mt-3 p-2 ls-survey-menu-item">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].escapeHtml(sortedMenus[1].title) + '</label>';
      html += this.renderSubmenu(sortedMenus[1]);
      html += '</div>';
    }
    html += '</div>';
    this.container.innerHTML = html;
    this.bindEvents();
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].redoTooltips();
  }

  /**
   * Render a submenu
   * @param {Object} menu
   * @returns {string}
   */
  renderSubmenu(menu) {
    if (!menu || !menu.entries) return '';
    const sortedEntries = LS.ld.orderBy(menu.entries, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
    let html = '<ul class="list-group subpanel col-12 level-' + (menu.level || 0) + '">';
    sortedEntries.forEach(menuItem => {
      const linkClass = this.getLinkClass(menuItem);
      const href = menuItem.disabled ? '#' : menuItem.link;
      const target = menuItem.link_external === true ? '_blank' : '';
      const tooltip = menuItem.disabled ? menuItem.disabled_tooltip : _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].reConvertHTML(menuItem.menu_description);
      html += '<a href="' + href + '"' + (target ? ' target="' + target + '"' : '') + ' id="sidemenu_' + menuItem.name + '"' + ' class="list-group-item w-100 ' + linkClass + '"' + ' data-menu-item-id="' + menuItem.id + '"' + ' data-menu-id="' + menuItem.menu_id + '">';
      html += '<div class="d-flex ' + (menuItem.menu_class || '') + '"' + ' title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].escapeHtml(tooltip) + '"' + ' data-bs-toggle="tooltip">';
      html += '<div class="ls-space padding all-0 me-auto wrapper">';
      html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_1__["default"].renderMenuIcon(menuItem.menu_icon_type, menuItem.menu_icon);
      html += '<span class="title">' + (menuItem.menu_title || '') + '</span>';
      if (menuItem.link_external === true) {
        html += '<i class="ri-external-link-fill">&nbsp;</i>';
      }
      html += '</div>';
      html += '</div>';
      html += '</a>';
    });
    html += '</ul>';
    return html;
  }

  /**
   * Get CSS classes for a menu link
   * @param {Object} menuItem
   * @returns {string}
   */
  getLinkClass(menuItem) {
    let classes = 'nowrap ';
    classes += menuItem.pjax ? 'pjax ' : ' ';
    classes += _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastMenuItemOpen') === menuItem.id ? 'selected ' : ' ';
    classes += menuItem.menu_icon ? '' : 'ls-survey-menu-item';
    if (menuItem.disabled) {
      classes += ' disabled';
    }
    return classes;
  }

  /**
   * Bind event handlers
   */
  bindEvents() {
    if (!this.container) return;

    // Menu item click
    $(this.container).off('click', '.list-group-item').on('click', '.list-group-item', this.handleMenuItemClick);
  }

  /**
   * Handle menu item click
   */
  handleMenuItemClick(e) {
    const $this = $(e.currentTarget);
    const menuItemId = $this.data('menu-item-id');
    const menuId = $this.data('menu-id');
    if ($this.hasClass('disabled')) {
      e.preventDefault();
      return false;
    }

    // Update state
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastMenuItemOpen', {
      id: menuItemId,
      menu_id: menuId
    });

    // Re-render to update selected state
    this.renderMenu();

    // Allow default link behavior (pjax will handle it)
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SideMenu);

/***/ },

/***/ "./src/components/Sidebar.js"
/*!***********************************!*\
  !*** ./src/components/Sidebar.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _Actions_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Actions.js */ "./src/Actions.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");
/* harmony import */ var _SideMenu_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./SideMenu.js */ "./src/components/SideMenu.js");
/* harmony import */ var _QuickMenu_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./QuickMenu.js */ "./src/components/QuickMenu.js");
/* harmony import */ var _QuestionExplorer_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./QuestionExplorer.js */ "./src/components/QuestionExplorer.js");
/**
 * Sidebar - Main sidebar component (vanilla JS)
 * Replaces sidebar.vue
 */






class Sidebar {
  constructor() {
    this.container = null;
    this.sideBarWidth = '315';
    this.isMouseDown = false;
    this.isMouseDownTimeOut = null;
    this.smallScreenHidden = false;
    this.showLoader = false;
    this.loading = true;

    // Component instances
    this.sideMenu = new _SideMenu_js__WEBPACK_IMPORTED_MODULE_3__["default"]();
    this.quickMenu = new _QuickMenu_js__WEBPACK_IMPORTED_MODULE_4__["default"]();
    this.questionExplorer = new _QuestionExplorer_js__WEBPACK_IMPORTED_MODULE_5__["default"]();

    // Bind methods
    this.handleMouseDown = this.handleMouseDown.bind(this);
    this.handleMouseUp = this.handleMouseUp.bind(this);
    this.handleMouseLeave = this.handleMouseLeave.bind(this);
    this.handleMouseMove = this.handleMouseMove.bind(this);
    this.handleQuestionGroupOrderChange = this.handleQuestionGroupOrderChange.bind(this);
    this.controlActiveLink = this.controlActiveLink.bind(this);
    this.handleUpdateSideBar = this.handleUpdateSideBar.bind(this);
    this.handleVueReloadRemote = this.handleVueReloadRemote.bind(this);
    this.handleVueRedraw = this.handleVueRedraw.bind(this);
    this.handlePjaxSend = this.handlePjaxSend.bind(this);
    this.handlePjaxRefresh = this.handlePjaxRefresh.bind(this);
    this.handleStateChange = this.handleStateChange.bind(this);
  }

  /**
   * Initialize the sidebar
   * @param {HTMLElement} containerEl
   */
  init(containerEl) {
    this.container = containerEl;

    // Set initial collapse state for mobile
    if (window.innerWidth < 768) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeIsCollapsed', false);
    }

    // Set survey active state
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('setSurveyActiveState', parseInt(window.SideMenuData.isActive) === 1);

    // Initialize sidebar width (always as a number)
    if (_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed')) {
      this.sideBarWidth = 98;
    } else {
      const savedWidth = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidebarwidth');
      this.sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
    }

    // Subscribe to state changes to keep sideBarWidth in sync
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].subscribe(this.handleStateChange);

    // Process base menus from SideMenuData
    if (window.SideMenuData && window.SideMenuData.basemenus) {
      LS.ld.each(window.SideMenuData.basemenus, (entries, position) => {
        this.setBaseMenuPosition(entries, position);
      });
    }
    this.render();
    this.bindEvents();
    this.calculateHeight();

    // Initial data load - check if menus are already loaded from basemenus
    const sidemenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidemenus');
    if (sidemenus && sidemenus.length > 0) {
      // Menus already loaded from basemenus, no need to show loading
      this.loading = false;
    } else {
      this.loading = true;
    }
    this.renderContent();

    // Trigger sidebar mounted event
    $(document).trigger('sidebar:mounted');
  }

  /**
   * Handle state changes
   */
  handleStateChange(key, newValue, oldValue) {
    if (key === 'sidebarwidth' && !_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed')) {
      // Ensure we store as a number
      this.sideBarWidth = typeof newValue === 'string' ? parseInt(newValue) : newValue;
      // Update the DOM directly for smooth resize
      const sidebar = document.getElementById('sidebar');
      if (sidebar && !this.isMouseDown) {
        sidebar.style.width = this.sideBarWidth + 'px';
      }
    } else if (key === 'isCollapsed') {
      if (newValue) {
        this.sideBarWidth = 98;
      } else {
        const savedWidth = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidebarwidth');
        this.sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
      }
      this.render();
    }
  }

  /**
   * Set base menu position
   */
  setBaseMenuPosition(entries, position) {
    const orderedEntries = LS.ld.orderBy(entries, function (a) {
      return parseInt(a.order || 999999);
    }, ['desc']);
    switch (position) {
      case 'side':
        _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateSidemenus', orderedEntries);
        break;
      case 'collapsed':
        _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateCollapsedmenus', orderedEntries);
        break;
    }
  }

  /**
   * Calculate sidebar height based on viewport
   */
  calculateHeight() {
    const height = $('#in_survey_common').height();
    if (height) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeSideBarHeight', height);
    }
  }

  /**
   * Get current sidebar width (returns numeric value without 'px')
   */
  getSideBarWidth() {
    const width = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed') ? 98 : this.sideBarWidth;
    // Ensure we always return a number by parsing if needed
    return typeof width === 'string' ? parseInt(width) : width;
  }

  /**
   * Calculate sidebar menu height
   */
  calculateSideBarMenuHeight() {
    const currentSideBar = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sideBarHeight');
    return LS.ld.min([currentSideBar, Math.floor(screen.height * 2)]) + 'px';
  }

  /**
   * Toggle collapse state
   */
  toggleCollapse() {
    const isCollapsed = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('isCollapsed');
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeIsCollapsed', !isCollapsed);
    if (_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed')) {
      this.sideBarWidth = 98;
    } else {
      const savedWidth = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidebarwidth');
      this.sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
    }
    this.render();
  }

  /**
   * Toggle small screen hidden state
   */
  toggleSmallScreenHide() {
    this.smallScreenHidden = !this.smallScreenHidden;
    this.render();
  }

  /**
   * Change current tab
   */
  changeCurrentTab(tab) {
    // Normalize tab name - 'structure' is alias for 'questiontree'
    if (tab === 'structure') {
      tab = 'questiontree';
    }
    // Only allow valid tab values
    if (tab !== 'settings' && tab !== 'questiontree') {
      tab = 'settings';
    }
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeCurrentTab', tab);
    this.render();
  }

  /**
   * Handle mouse down for resize
   */
  handleMouseDown(e) {
    if (_UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView()) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeIsCollapsed', false);
      this.smallScreenHidden = !this.smallScreenHidden;
      this.render();
      return;
    }
    this.isMouseDown = !_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed');
    $('#sidebar').removeClass('transition-animate-width');
    $('#pjax-content').removeClass('transition-animate-width');
  }

  /**
   * Handle mouse up for resize
   */
  handleMouseUp(e) {
    if (this.isMouseDown) {
      this.isMouseDown = false;
      const widthNum = typeof this.sideBarWidth === 'string' ? parseInt(this.sideBarWidth) : this.sideBarWidth;
      if (widthNum < 250 && !_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed')) {
        this.toggleCollapse();
        _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeSidebarwidth', 340);
      } else {
        _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeSidebarwidth', widthNum);
      }
      $('#sidebar').addClass('transition-animate-width');
      $('#pjax-content').removeClass('transition-animate-width');
    }
  }

  /**
   * Handle mouse leave for resize
   */
  handleMouseLeave(e) {
    if (this.isMouseDown) {
      this.isMouseDownTimeOut = setTimeout(() => {
        this.handleMouseUp(e);
      }, 1000);
    }
  }

  /**
   * Handle mouse move for resize
   */
  handleMouseMove(e) {
    if (!this.isMouseDown) return;
    const isRTL = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isRTL');

    // Prevent emitting unwanted value on dragend
    if (e.screenX === 0 && e.screenY === 0) {
      return;
    }
    if (isRTL) {
      if (window.innerWidth - e.clientX > screen.width / 2) {
        _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('maxSideBarWidth', true);
        return;
      }
      this.sideBarWidth = window.innerWidth - e.pageX - 8;
    } else {
      if (e.clientX > screen.width / 2) {
        _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('maxSideBarWidth', true);
        return;
      }
      this.sideBarWidth = e.pageX - 4;
    }
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeSidebarwidth', this.sideBarWidth);
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('maxSideBarWidth', false);
    window.clearTimeout(this.isMouseDownTimeOut);
    this.isMouseDownTimeOut = null;

    // Update sidebar width in real-time (sideBarWidth is a number, add px)
    $('#sidebar').css('width', this.sideBarWidth + 'px');
  }

  /**
   * Control active link highlighting
   */
  controlActiveLink() {
    const currentUrl = window.location.href;
    const sidemenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidemenus') || [];
    const collapsedmenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('collapsedmenus') || [];
    const questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];

    // Check for corresponding menuItem
    let lastMenuItemObject = false;
    LS.ld.each(sidemenus, function (itm) {
      LS.ld.each(itm.entries, function (itmm) {
        if (LS.ld.endsWith(currentUrl, itmm.link)) {
          lastMenuItemObject = itmm;
        }
      });
    });

    // Check for quickmenu menuLinks
    let lastQuickMenuItemObject = false;
    LS.ld.each(collapsedmenus, function (itm) {
      LS.ld.each(itm.entries, function (itmm) {
        if (LS.ld.endsWith(currentUrl, itmm.link)) {
          lastQuickMenuItemObject = itmm;
        }
      });
    });

    // Check for corresponding question group object
    let lastQuestionGroupObject = false;
    LS.ld.each(questiongroups, function (itm) {
      const regTest = new RegExp('questionGroupsAdministration/view\\?surveyid=\\d*&gid=' + itm.gid + '|questionGroupsAdministration/edit\\?surveyid=\\d*&gid=' + itm.gid + '|questionGroupsAdministration/view/surveyid/\\d*/gid/' + itm.gid + '|questionGroupsAdministration/edit/surveyid/\\d*/gid/' + itm.gid);
      if (regTest.test(currentUrl) || LS.ld.endsWith(currentUrl, itm.link)) {
        lastQuestionGroupObject = itm;
        return false;
      }
    });

    // Check for corresponding question
    let lastQuestionObject = false;
    const questionIdInput = document.querySelector('#edit-question-form [name="question[qid]"]');
    if (questionIdInput !== null) {
      const questionId = questionIdInput.value;
      LS.ld.each(questiongroups, function (itm) {
        LS.ld.each(itm.questions, function (itmm) {
          if (questionId === itmm.qid) {
            lastQuestionObject = itmm;
            lastQuestionGroupObject = itm;
            return false;
          }
        });
        if (lastQuestionObject !== false) {
          return false;
        }
      });
    }

    // Unload every selection
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('closeAllMenus');
    if (lastMenuItemObject !== false && !_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed')) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastMenuItemOpen', lastMenuItemObject);
    }
    if (lastQuickMenuItemObject !== false && _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed')) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastMenuItemOpen', lastQuickMenuItemObject);
    }
    if (lastQuestionGroupObject !== false) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastQuestionGroupOpen', lastQuestionGroupObject);
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('addToQuestionGroupOpenArray', lastQuestionGroupObject);
    }
    if (lastQuestionObject !== false) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastQuestionOpen', lastQuestionObject);
    }
  }

  /**
   * Handle question group order change
   */
  handleQuestionGroupOrderChange() {
    this.showLoader = true;
    this.render();
    const questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups');
    const surveyid = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('surveyid');
    _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].updateQuestionGroupOrder(questiongroups, surveyid).then(() => {
      return _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].getQuestions();
    }).then(() => {
      this.showLoader = false;
      this.render();
    }).catch(error => {
      console.ls.error('questiongroups updating error!', error);
      _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].getQuestions().then(() => {
        this.showLoader = false;
        this.render();
      });
    });
  }

  /**
   * Bind event handlers
   */
  bindEvents() {
    // Window resize
    window.addEventListener('resize', LS.ld.debounce(() => this.calculateHeight(), 300));

    // Body mouse move for resize
    $('body').on('mousemove', this.handleMouseMove);

    // Custom events
    $(document).on('vue-sidemenu-update-link', this.controlActiveLink);
    $(document).on('vue-reload-remote', this.handleVueReloadRemote);
    $(document).on('vue-redraw', this.handleVueRedraw);
    $(document).on('pjax:send', this.handlePjaxSend);
    $(document).on('pjax:refresh', this.handlePjaxRefresh);

    // EventBus equivalent for updateSideBar
    $(document).on('updateSideBar', this.handleUpdateSideBar);
  }

  /**
   * Handle vue-reload-remote event
   */
  handleVueReloadRemote() {
    _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].getQuestions();
    _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].collectMenus();
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('newToggleKey');
  }

  /**
   * Handle vue-redraw event
   */
  handleVueRedraw() {
    _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].getQuestions();
    _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].collectMenus();
  }

  /**
   * Handle pjax:send event
   */
  handlePjaxSend() {
    if (_UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView() && this.smallScreenHidden) {
      this.smallScreenHidden = false;
      this.render();
    }
  }

  /**
   * Handle pjax:refresh event
   */
  handlePjaxRefresh() {
    this.controlActiveLink();
  }

  /**
   * Handle updateSideBar event
   */
  handleUpdateSideBar(e, payload) {
    this.loading = true;
    this.renderContent();
    const promises = [Promise.resolve()];
    if (payload && payload.updateQuestions) {
      promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].getQuestions());
    }
    if (payload && payload.collectMenus) {
      promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].collectMenus());
    }
    if (payload && payload.activeMenuIndex) {
      this.controlActiveLink();
    }
    Promise.all(promises).catch(errors => {
      console.ls.error(errors);
    }).finally(() => {
      this.loading = false;
      this.renderContent();
    });
  }

  /**
   * Render the sidebar HTML
   */
  render() {
    if (!this.container) return;
    const isCollapsed = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isCollapsed');
    const currentTab = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('currentTab');
    const isRTL = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].getComputed('isRTL');
    const inSurveyViewHeight = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('inSurveyViewHeight');
    const currentSidebarWidth = this.getSideBarWidth();
    let classes = 'd-flex col-lg-4 ls-ba position-relative transition-animate-width';
    if (this.smallScreenHidden) {
      classes += ' toggled';
    }
    const showMainContent = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView() && this.smallScreenHidden || !_UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView();
    const showPlaceholder = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView() && this.smallScreenHidden;
    const showResizeOverlay = this.isMouseDown;
    let html = '<div id="sidebar" class="' + classes + '" style="width: ' + currentSidebarWidth + 'px; max-height: ' + inSurveyViewHeight + 'px; display: flex;">';
    if (showMainContent) {
      // Loader overlay
      if (this.showLoader) {
        html += '<div class="sidebar_loader" style="width: ' + this.getSideBarWidth() + 'px; height: ' + $('#sidebar').height() + 'px;">' + '<div class="ls-flex ls-flex-column fill align-content-center align-items-center">' + '<i class="ri-loader-2-fill remix-2x remix-spin"></i>' + '</div>' + '</div>';
      }
      html += '<div class="col-12 mainContentContainer">';
      html += '<div class="mainMenu col-12 position-relative">';

      // Sidebar state toggle (tabs)
      html += this.renderStateToggle(isCollapsed, currentTab, isRTL);

      // Side menu content
      html += '<div id="sidemenu-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'settings' ? 'block' : 'none') + '; min-height: ' + this.calculateSideBarMenuHeight() + ';"></div>';

      // Question explorer content
      html += '<div id="questionexplorer-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'questiontree' ? 'block' : 'none') + '; min-height: ' + this.calculateSideBarMenuHeight() + ';"></div>';

      // Quick menu (collapsed state)
      html += '<div id="quickmenu-container" style="display: ' + (isCollapsed ? 'block' : 'none') + ';"></div>';

      // Resize handle
      if (_UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView() && !this.smallScreenHidden || !_UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].useMobileView()) {
        html += '<div class="resize-handle ls-flex-column" style="height: ' + this.calculateSideBarMenuHeight() + ';">';
        if (!isCollapsed) {
          html += '<button class="btn resize-btn" type="button">' + '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">' + '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>' + '</svg>' + '</button>';
        }
        html += '</div>';
      }
      html += '</div>'; // .mainMenu
      html += '</div>'; // .mainContentContainer
    }

    // Placeholder for mobile
    if (showPlaceholder) {
      html += '<div class="scoped-placeholder-greyed-area"> </div>';
    }

    // Resize overlay to prevent mouse issues
    if (showResizeOverlay) {
      html += '<div style="position: fixed; inset: 0;"></div>';
    }
    html += '</div>'; // #sidebar

    this.container.innerHTML = html;

    // Bind internal events after render
    this.bindInternalEvents();

    // Render sub-components
    this.renderContent();
  }

  /**
   * Render state toggle (tabs)
   */
  renderStateToggle(isCollapsed, currentTab, isRTL) {
    let html = '<div class="ls-space col-12">';
    html += '<div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 bottom-0 top-0">';
    if (!isCollapsed) {
      html += '<div class="ls-flex-item grow-10 col-12">' + '<ul class="nav nav-tabs" id="surveysystem" role="tablist">' + '<li class="nav-item">' + '<a id="adminsidepanel__sidebar--selectorSettingsButton" class="nav-link sidebar-tab-link' + (currentTab === 'settings' ? ' active' : '') + '" href="#settings" data-tab="settings" role="tab">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('settings') + '</a>' + '</li>' + '<li class="nav-item">' + '<a id="adminsidepanel__sidebar--selectorStructureButton" class="nav-link sidebar-tab-link' + (currentTab === 'questiontree' ? ' active' : '') + '" href="#structure" data-tab="questiontree" role="tab">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('structure') + '</a>' + '</li>' + '</ul>' + '</div>';
    } else {
      const arrowClass = isRTL ? 'ri-arrow-left-s-line' : 'ri-arrow-right-s-line';
      html += '<button class="btn btn-outline-secondary ls-space padding left-15 right-15 expand-sidebar-btn">' + '<i class="' + arrowClass + '"></i>' + '</button>';
    }
    html += '</div>';
    html += '</div>';
    return html;
  }

  /**
   * Render content for sub-components
   */
  renderContent() {
    const sidemenuContainer = document.getElementById('sidemenu-container');
    const questionExplorerContainer = document.getElementById('questionexplorer-container');
    const quickmenuContainer = document.getElementById('quickmenu-container');
    if (sidemenuContainer) {
      this.sideMenu.render(sidemenuContainer, this.loading);
    }
    if (questionExplorerContainer) {
      this.questionExplorer.render(questionExplorerContainer, this.loading, this.handleQuestionGroupOrderChange);
    }
    if (quickmenuContainer) {
      this.quickMenu.render(quickmenuContainer, this.loading);
    }

    // Re-initialize tooltips
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].redoTooltips();
  }

  /**
   * Bind internal events after render
   */
  bindInternalEvents() {
    // Tab switching
    $(this.container).off('click', '.sidebar-tab-link').on('click', '.sidebar-tab-link', e => {
      e.preventDefault();
      const tab = $(e.currentTarget).data('tab');
      this.changeCurrentTab(tab);
    });

    // Expand button (collapsed state)
    $(this.container).off('click', '.expand-sidebar-btn').on('click', '.expand-sidebar-btn', e => {
      e.preventDefault();
      this.toggleCollapse();
    });

    // Resize handle
    $(this.container).off('mousedown', '.resize-btn').on('mousedown', '.resize-btn', this.handleMouseDown);
    $(this.container).off('mouseup').on('mouseup', this.handleMouseUp);
    $(this.container).off('mouseleave', '#sidebar').on('mouseleave', '#sidebar', this.handleMouseLeave);

    // Placeholder click (mobile)
    $(this.container).off('click', '.scoped-placeholder-greyed-area').on('click', '.scoped-placeholder-greyed-area', () => this.toggleSmallScreenHide());
  }

  /**
   * Update sidebar (called externally)
   */
  update(options) {
    options = options || {};
    this.loading = true;
    this.renderContent();
    const promises = [];
    if (options.updateQuestions) {
      promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].getQuestions());
    }
    if (options.collectMenus) {
      promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].collectMenus());
    }
    Promise.all(promises).then(() => {
      if (options.activeMenuIndex) {
        this.controlActiveLink();
      }
    }).catch(error => {
      console.ls.error(error);
    }).finally(() => {
      this.loading = false;
      this.renderContent();
    });
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Sidebar);

/***/ },

/***/ "./src/stateConfig.js"
/*!****************************!*\
  !*** ./src/stateConfig.js ***!
  \****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createDefaultState: () => (/* binding */ createDefaultState),
/* harmony export */   createGetters: () => (/* binding */ createGetters),
/* harmony export */   createMutations: () => (/* binding */ createMutations)
/* harmony export */ });
/**
 * State configuration for adminsidepanel
 * Defines default state, mutations, and getters
 */

/**
 * Create default state
 * @param {string|number} userid
 * @param {string|number} surveyid
 * @returns {Object}
 */
function createDefaultState(userid, surveyid) {
  // Calculate default sidebar width
  const container = $('#vue-apps-main-container');
  const containerWidth = container.length ? container.width() : 1200;
  let sidebarWidth = containerWidth * 0.33;
  if (containerWidth > 1400) {
    sidebarWidth = containerWidth * 0.25;
  }
  return {
    surveyid: 0,
    language: '',
    maxHeight: 0,
    inSurveyViewHeight: 1000,
    sideBodyHeight: '100%',
    sideBarHeight: 400,
    currentUser: userid,
    currentTab: 'settings',
    sidebarwidth: sidebarWidth,
    maximalSidebar: false,
    isCollapsed: false,
    pjax: null,
    pjaxLoading: false,
    lastMenuOpen: false,
    lastMenuItemOpen: false,
    lastQuestionOpen: false,
    lastQuestionGroupOpen: false,
    questionGroupOpenArray: [],
    questiongroups: [],
    collapsedmenus: null,
    sidemenus: null,
    topmenus: null,
    bottommenus: null,
    surveyActiveState: false,
    toggleKey: Math.floor(Math.random() * 10000) + '--key',
    allowOrganizer: true
  };
}

/**
 * Create mutations for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
function createMutations(StateManager) {
  return {
    updateSurveyId: function (newSurveyId) {
      StateManager.set('surveyid', newSurveyId);
    },
    changeLanguage: function (language) {
      StateManager.set('language', language);
    },
    changeCurrentTab: function (value) {
      StateManager.set('currentTab', value);
    },
    changeSidebarwidth: function (value) {
      StateManager.set('sidebarwidth', value);
    },
    maxSideBarWidth: function (value) {
      StateManager.set('maximalSidebar', value);
    },
    changeIsCollapsed: function (value) {
      StateManager.set('isCollapsed', value);
      $(document).trigger('vue-sidemenu-update-link');
    },
    changeMaxHeight: function (newHeight) {
      StateManager.set('maxHeight', newHeight);
    },
    changeSideBarHeight: function (newHeight) {
      StateManager.set('sideBarHeight', newHeight);
    },
    changeInSurveyViewHeight: function (newHeight) {
      StateManager.set('inSurveyViewHeight', newHeight);
    },
    changeSideBodyHeight: function (newHeight) {
      StateManager.set('sideBodyHeight', newHeight ? newHeight + 'px' : '100%');
    },
    changeCurrentUser: function (newUser) {
      StateManager.set('currentUser', newUser);
    },
    closeAllMenus: function () {
      StateManager.set('lastMenuOpen', false);
      StateManager.set('lastMenuItemOpen', false);
      StateManager.set('lastQuestionGroupOpen', false);
      StateManager.set('lastQuestionOpen', false);
    },
    lastMenuItemOpen: function (menuItem) {
      StateManager.set('lastMenuOpen', menuItem.menu_id);
      StateManager.set('lastMenuItemOpen', menuItem.id);
      StateManager.set('lastQuestionGroupOpen', false);
      StateManager.set('lastQuestionOpen', false);
    },
    lastMenuOpen: function (menuObject) {
      StateManager.set('lastMenuOpen', menuObject.id);
      StateManager.set('lastQuestionOpen', false);
      StateManager.set('lastMenuItemOpen', false);
    },
    lastQuestionOpen: function (questionObject) {
      StateManager.set('lastQuestionGroupOpen', questionObject.gid);
      StateManager.set('lastQuestionOpen', questionObject.qid);
      StateManager.set('lastMenuItemOpen', false);
    },
    lastQuestionGroupOpen: function (questionGroupObject) {
      StateManager.set('lastQuestionGroupOpen', questionGroupObject.gid);
      StateManager.set('lastQuestionOpen', false);
    },
    questionGroupOpenArray: function (questionGroupOpenArray) {
      StateManager.set('questionGroupOpenArray', questionGroupOpenArray);
    },
    updateQuestiongroups: function (questiongroups) {
      StateManager.set('questiongroups', questiongroups);
    },
    addToQuestionGroupOpenArray: function (questiongroupToAdd) {
      const state = StateManager.get();
      const tmpArray = state.questionGroupOpenArray.slice();
      tmpArray.push(questiongroupToAdd.gid);
      StateManager.set('questionGroupOpenArray', tmpArray);
    },
    updateSidemenus: function (sidemenus) {
      StateManager.set('sidemenus', sidemenus);
    },
    updateCollapsedmenus: function (collapsedmenus) {
      StateManager.set('collapsedmenus', collapsedmenus);
    },
    updateTopmenus: function (topmenus) {
      StateManager.set('topmenus', topmenus);
    },
    updateBottommenus: function (bottommenus) {
      StateManager.set('bottommenus', bottommenus);
    },
    setSurveyActiveState: function (surveyState) {
      StateManager.set('surveyActiveState', !!surveyState);
    },
    newToggleKey: function () {
      StateManager.set('toggleKey', Math.floor(Math.random() * 10000) + '--key');
    },
    setAllowOrganizer: function (newVal) {
      StateManager.set('allowOrganizer', newVal);
    }
  };
}

/**
 * Create getters for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
function createGetters(StateManager) {
  return {
    substractContainer: function () {
      const state = StateManager.get();
      const container = $('#vue-apps-main-container');
      const containerWidth = container.length ? container.width() : window.innerWidth;
      const bodyWidth = (1 - parseInt(state.sidebarwidth) / containerWidth) * 100;
      const collapsedBodyWidth = (1 - 98 / containerWidth) * 100;
      return Math.floor(state.isCollapsed ? collapsedBodyWidth : bodyWidth) + '%';
    },
    sideBarSize: function () {
      const state = StateManager.get();
      const container = $('#vue-apps-main-container');
      const containerWidth = container.length ? container.width() : window.innerWidth;
      const sidebarWidth = parseInt(state.sidebarwidth) / containerWidth * 100;
      const collapsedSidebarWidth = 98 / containerWidth * 100;
      return Math.ceil(state.isCollapsed ? collapsedSidebarWidth : sidebarWidth) + '%';
    },
    isRTL: function () {
      return document.getElementsByTagName('html')[0].getAttribute('dir') === 'rtl';
    },
    isCollapsed: function () {
      if (window.innerWidth < 768) {
        return false;
      }
      const state = StateManager.get();
      return state.isCollapsed;
    }
  };
}

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************!*\
  !*** ./lib/surveysettings.js ***!
  \*******************************/
$('#copysurveyform').submit(copysurvey);
function setAdministratorFieldsVisibility(form) {
  var option = form.find("[name=administrator]:checked").val();
  var fieldsContainer = $("#conditional-administrator-fields");
  if (option == "custom") {
    fieldsContainer.show(200);
  } else {
    fieldsContainer.hide(200);
  }
}
$(document).on('click', '[data-copy] :submit', function () {
  $('form :input[value=\'' + $(this).val() + '\']').click();
});
// $(document).on('submit',"#addnewsurvey",function(){
//     $('#addnewsurvey').attr('action',$('#addnewsurvey').attr('action')+location.hash);// Maybe validate before ?
// });
$(document).on('ready  pjax:scriptcomplete', function () {
  $('#template').on('change keyup', function (event) {
    console.ls.log('TEMPLATECHANGE', event);
    templatechange($(this));
  });
  $('[data-copy]').each(function () {
    $(this).html($('#' + $(this).data('copy')).html());
  });
  var jsonUrl = jsonUrl || null;
  $('#tabs').on('tabsactivate', function (event, ui) {
    if (ui.newTab.index() > 4)
      // Hide on import and copy tab, otherwise show
      {
        $('#btnSave').hide();
      } else {
      $('#btnSave').show();
    }
  });

  // If on "Create survey" form
  if ($('#addnewsurvey')) {
    var form = $('#addnewsurvey');

    // Set initial visibility
    setAdministratorFieldsVisibility(form);

    // Update visibility when 'administrator' option changes
    form.find("[name=administrator]").on('change', function () {
      setAdministratorFieldsVisibility(form);
    });
  }
});
function templatechange($element) {
  $('#preview-image-container').html('<div style="height:200px;" class="ls-flex ls-flex-column align-content-center align-items-center"><i class="ri-loader-2-fill remix-spin remix-3x"></i></div>');
  let templateName = $element.val();
  if (templateName === 'inherit') {
    templateName = $element.data('inherit-template-name');
  }
  $.ajax({
    url: $element.data('updateurl'),
    data: {
      templatename: templateName
    },
    method: 'POST',
    dataType: 'json',
    success: function (data) {
      $('#preview-image-container').html(data.image);
    },
    error: console.ls.error
  });
}
function copysurvey() {
  let sMessage = '';
  if ($('#copysurveylist').val() == '') {
    sMessage = sMessage + sSelectASurveyMessage;
  }
  if ($('#copysurveyname').val() == '') {
    sMessage = sMessage + '\n\r' + sSelectASurveyName;
  }
  if (sMessage != '') {
    alert(sMessage);
    return false;
  }
}
function in_array(needle, haystack, argStrict) {
  var key = '',
    strict = !!argStrict;
  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true;
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) {
        return true;
      }
    }
  }
  return false;
}
function guidGenerator() {
  var S4 = function () {
    return ((1 + Math.random()) * 0x10000 | 0).toString(16).substring(1);
  };
  return S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4();
}
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!***********************************!*\
  !*** ./src/adminsidepanelmain.js ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _stateConfig_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./stateConfig.js */ "./src/stateConfig.js");
/* harmony import */ var _Actions_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Actions.js */ "./src/Actions.js");
/* harmony import */ var _components_Sidebar_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/Sidebar.js */ "./src/components/Sidebar.js");
/**
 * AdminSidePanel - Main entry point (vanilla JS)
 * Replaces Vue-based adminsidepanelmain.js
 */





/**
 * Main AdminSidePanel factory function
 * @param {string|number} userid
 * @param {string|number} surveyid
 * @returns {Function}
 */
const Lsadminsidepanel = function (userid, surveyid) {
  'use strict';

  const panelNameSpace = {};

  /**
   * Apply survey ID to state
   */
  function applySurveyId() {
    if (surveyid !== 0 && surveyid !== '0' && surveyid !== 'newSurvey') {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateSurveyId', surveyid);
    }
  }

  /**
   * Control window size and adjust layout
   */
  function controlWindowSize() {
    const adminmenuHeight = $('body').find('nav').first().height() || 0;
    const footerHeight = $('body').find('footer').last().height() || 0;
    const menuHeight = $('.menubar').outerHeight() || 0;
    const inSurveyOffset = adminmenuHeight + footerHeight + menuHeight + 25;
    const windowHeight = window.innerHeight;
    const inSurveyViewHeight = windowHeight - inSurveyOffset;
    const sidebarWidth = $('#sidebar').width() || 0;
    const containerWidth = $('#vue-apps-main-container').width() || 1;
    const bodyWidth = (1 - parseInt(sidebarWidth) / containerWidth) * 100;
    const collapsedBodyWidth = (1 - parseInt('98px') / containerWidth) * 100;
    const inSurveyViewWidth = Math.floor($('#sidebar').data('collapsed') ? bodyWidth : collapsedBodyWidth) + '%';
    panelNameSpace.surveyViewHeight = inSurveyViewHeight;
    panelNameSpace.surveyViewWidth = inSurveyViewWidth;
    $('#fullbody-container').css({
      'max-width': inSurveyViewWidth,
      'overflow-x': 'auto'
    });
  }

  /**
   * Create the side menu
   */
  function createSideMenu() {
    const containerEl = document.getElementById('vue-sidebar-container');
    if (!containerEl) return null;

    // Initialize state manager with unified API
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].init({
      storagePrefix: 'limesurveyadminsidepanel',
      userid: userid,
      surveyid: surveyid,
      defaultState: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createDefaultState)(userid, surveyid),
      mutations: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createMutations)(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"]),
      getters: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createGetters)(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"])
    });

    // Apply survey ID
    applySurveyId();

    // Set max height
    const maxHeight = $('#in_survey_common').height() - 35 || 400;
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeMaxHeight', maxHeight);

    // Set allow organizer - default to unlocked (1) unless explicitly locked
    if (window.SideMenuData && window.SideMenuData.allowOrganizer !== undefined) {
      // Only lock if explicitly set to 0, otherwise keep unlocked
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('setAllowOrganizer', window.SideMenuData.allowOrganizer === 0 ? 0 : 1);
    } else {
      // No server value, default to unlocked
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('setAllowOrganizer', 1);
    }

    // Initialize sidebar component (now as a class)
    const sidebar = new _components_Sidebar_js__WEBPACK_IMPORTED_MODULE_3__["default"]();
    sidebar.init(containerEl);

    // Bind Vue-style events
    $(document).on('vue-redraw', function () {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('newToggleKey');
    });
    $(document).trigger('vue-reload-remote');
    return sidebar;
  }

  /**
   * Apply Pjax methods for AJAX navigation
   */
  function applyPjaxMethods() {
    panelNameSpace.reloadcounter = 5;
    $(document).off('pjax:send.panelloading').on('pjax:send.panelloading', function () {
      $('<div id="pjaxClickInhibitor"></div>').appendTo('body');
      $('.ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-draggable.ui-resizable').remove();
      $('#pjax-file-load-container').find('div').css({
        width: '20%',
        display: 'block'
      });
      LS.adminsidepanel.reloadcounter--;
    });
    $(document).off('pjax:error.panelloading').on('pjax:error.panelloading', function (event) {
      if (console.ls && console.ls.log) {
        console.ls.log(event);
      }
    });
    $(document).off('pjax:complete.panelloading').on('pjax:complete.panelloading', function () {
      if (LS.adminsidepanel.reloadcounter === 0) {
        location.reload();
      }
    });
    $(document).off('pjax:scriptcomplete.panelloading').on('pjax:scriptcomplete.panelloading', function () {
      $('#pjax-file-load-container').find('div').css('width', '100%');
      $('#pjaxClickInhibitor').fadeOut(400, function () {
        $(this).remove();
      });
      $(document).trigger('vue-resize-height');
      $(document).trigger('vue-reload-remote');
      setTimeout(function () {
        $('#pjax-file-load-container').find('div').css({
          width: '0%',
          display: 'none'
        });
      }, 2200);
    });
  }

  /**
   * Create panel appliance
   */
  function createPanelAppliance() {
    // Initialize singleton Pjax
    if (window.singletonPjax) {
      window.singletonPjax();
    }

    // Create side menu
    if (document.getElementById('vue-sidebar-container')) {
      panelNameSpace.sidemenu = createSideMenu();
    }

    // Pagination click handler
    $(document).on('click', 'ul.pagination>li>a', function () {
      $(document).trigger('pjax:refresh');
    });

    // Window resize handling
    controlWindowSize();
    window.addEventListener('resize', LS.ld.debounce(controlWindowSize, 300));
    $(document).on('vue-resize-height', LS.ld.debounce(controlWindowSize, 300));

    // Apply Pjax methods
    applyPjaxMethods();
  }

  // Add to LS admin namespace
  if (LS && LS.adminCore && LS.adminCore.addToNamespace) {
    LS.adminCore.addToNamespace(panelNameSpace, 'adminsidepanel');
  }
  return createPanelAppliance;
};

// Document ready handler
$(document).ready(function () {
  let surveyid = 'newSurvey';
  if (window.LS !== undefined) {
    surveyid = window.LS.parameters.$GET.surveyid || window.LS.parameters.keyValuePairs.surveyid;
  }
  if (window.SideMenuData) {
    surveyid = window.SideMenuData.surveyid;
  }
  const userid = window.LS && window.LS.globalUserId ? window.LS.globalUserId : null;
  window.adminsidepanel = window.adminsidepanel || Lsadminsidepanel(userid, surveyid);
  window.adminsidepanel();
});
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Lsadminsidepanel);
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!**************************************!*\
  !*** ./scss/adminsidepanelmain.scss ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

/******/ })()
;
//# sourceMappingURL=adminsidepanel.js.map