(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["GlobalSidePanel"] = factory();
	else
		root["GlobalSidePanel"] = factory();
})(self, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "../meta/lib/ConsoleShim.js":
/*!**********************************!*\
  !*** ../meta/lib/ConsoleShim.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* eslint no-console: "off" */
var ConsoleShim = /*#__PURE__*/function () {
  function ConsoleShim() {
    var param = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
    var silencer = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    _classCallCheck(this, ConsoleShim);
    this.param = param;
    this.silencer = silencer;
    this.collector = [];
    this.currentGroupDescription = '';
    this.activeGroups = 0;
    this.timeHolder = null;
    this.methods = ['group', 'groupEnd', 'log', 'trace', 'time', 'timeEnd', 'error', 'warn'];
    this.silent = {
      group: function group() {
        return;
      },
      groupEnd: function groupEnd() {
        return;
      },
      log: function log() {
        return;
      },
      trace: function trace() {
        return;
      },
      time: function time() {
        return;
      },
      timeEnd: function timeEnd() {
        return;
      },
      error: function error() {
        return;
      },
      err: function err() {
        return;
      },
      debug: function debug() {
        return;
      },
      warn: function warn() {
        return;
      }
    };
  }
  return _createClass(ConsoleShim, [{
    key: "_generateError",
    value: function _generateError() {
      try {
        throw new Error();
      } catch (err) {
        return err;
      }
    }
  }, {
    key: "_insertParamToArguments",
    value: function _insertParamToArguments(rawArgs) {
      if (this.param !== '') {
        var args = _toConsumableArray(rawArgs);
        args.unshift(this.param);
        return args;
      }
      return Array.from(arguments);
    }
  }, {
    key: "setSilent",
    value: function setSilent() {
      var newValue = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      this.silencer = newValue || !this.silencer;
    }
    //Start grouping logs
  }, {
    key: "group",
    value: function group() {
      if (this.silencer) {
        return;
      }
      var args = this._insertParamToArguments(arguments);
      if (typeof console.group === 'function') {
        console.group.apply(console, args);
        return;
      }
      var description = args[0] || 'GROUP';
      this.currentGroupDescription = description;
      this.activeGroups++;
    }
    //Stop grouping logs
  }, {
    key: "groupEnd",
    value: function groupEnd() {
      if (this.silencer) {
        return;
      }
      var args = this._insertParamToArguments(arguments);
      if (typeof console.groupEnd === 'function') {
        console.groupEnd.apply(console, args);
        return;
      }
      this.currentGroupDescription = '';
      this.activeGroups--;
      this.activeGroups = this.activeGroups === 0 ? 0 : this.activeGroups--;
    }
    //Simplest mechanism to log stuff
    // Aware of the group shim
  }, {
    key: "log",
    value: function log() {
      if (this.silencer) {
        return;
      }
      var args = this._insertParamToArguments(arguments);
      if (typeof console.group === 'function') {
        console.log.apply(console, args);
        return;
      }
      args.shift();
      args.unshift(' '.repeat(this.activeGroups * 2));
      this.log.apply(this, args);
    }
    //Trace back the apply.
    //Uses either the inbuilt function console trace or opens a shim to trace by calling this._insertParamToArguments(arguments).callee
  }, {
    key: "trace",
    value: function trace() {
      if (this.silencer) {
        return;
      }
      var args = this._insertParamToArguments(arguments);
      if (typeof console.trace === 'function') {
        console.trace.apply(console, args);
        return;
      }
      var artificialError = this._generateError();
      if (artificialError.stack) {
        this.log.apply(console, artificialError.stack);
        return;
      }
      this.log(args);
      if (arguments.callee != undefined) {
        this.trace.apply(console, arguments.callee);
      }
    }
  }, {
    key: "time",
    value: function time() {
      if (this.silencer) {
        return;
      }
      var args = this._insertParamToArguments(arguments);
      if (typeof console.time === 'function') {
        console.time.apply(console, args);
        return;
      }
      this.timeHolder = new Date();
    }
  }, {
    key: "timeEnd",
    value: function timeEnd() {
      if (this.silencer) {
        return;
      }
      var args = this._insertParamToArguments(arguments);
      if (typeof console.timeEnd === 'function') {
        console.timeEnd.apply(console, args);
        return;
      }
      var diff = new Date() - this.timeHolder;
      this.log("Took ".concat(Math.floor(diff / (1000 * 60 * 60)), " hours, ").concat(Math.floor(diff / (1000 * 60)), " minutes and ").concat(Math.floor(diff / 1000), " seconds ( ").concat(diff, " ms)"));
      this.time = new Date();
    }
  }, {
    key: "error",
    value: function error() {
      var args = this._insertParamToArguments(arguments);
      if (typeof console.error === 'function') {
        console.error.apply(console, args);
        return;
      }
      this.log('--- ERROR ---');
      this.log(args);
    }
  }, {
    key: "warn",
    value: function warn() {
      var args = this._insertParamToArguments(arguments);
      if (typeof console.warn === 'function') {
        console.warn.apply(console, args);
        return;
      }
      this.log('--- WARN ---');
      this.log(args);
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (ConsoleShim);

/***/ }),

/***/ "./src/StateManager.js":
/*!*****************************!*\
  !*** ./src/StateManager.js ***!
  \*****************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/**
 * StateManager - Vanilla JS replacement for Vuex store
 * Manages sidebar state with sessionStorage persistence
 *
 * Unified implementation used across admin and global sidepanels
 */
var StateManager = function () {
  'use strict';

  var state = {};
  var storageKey = '';
  var listeners = [];
  var mutations = {};
  var getters = {};

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
    var savedState = loadFromStorage();
    state = Object.assign({}, config.defaultState, savedState);
    return state;
  }

  /**
   * Load state from sessionStorage
   */
  function loadFromStorage() {
    try {
      var saved = sessionStorage.getItem(storageKey);
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
    var oldValue = state[key];
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
      listeners = listeners.filter(function (l) {
        return l !== callback;
      });
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
    getState: function getState() {
      return state;
    }
  };
}();
/* harmony default export */ __webpack_exports__["default"] = (StateManager);

/***/ }),

/***/ "./src/actions.js":
/*!************************!*\
  !*** ./src/actions.js ***!
  \************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _meta_lib_ConsoleShim_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../meta/lib/ConsoleShim.js */ "../meta/lib/ConsoleShim.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Actions for Global Sidebar Panel
 * Handles async operations
 */


var LOG = new _meta_lib_ConsoleShim_js__WEBPACK_IMPORTED_MODULE_0__["default"]('globalsidepanel');
var Actions = /*#__PURE__*/function () {
  function Actions(StateManager) {
    _classCallCheck(this, Actions);
    this.StateManager = StateManager;
  }

  /**
   * Run AJAX request
   */
  return _createClass(Actions, [{
    key: "_runAjax",
    value: function _runAjax(uri) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var method = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'get';
      return new Promise(function (resolve, reject) {
        if (typeof $ === 'undefined') {
          reject('JQUERY NOT AVAILABLE!');
          return;
        }
        $.ajax({
          url: uri,
          method: method || 'get',
          data: data,
          dataType: 'json',
          success: function success(response, status, xhr) {
            resolve({
              success: true,
              data: response,
              transferStatus: status,
              xhr: xhr
            });
          },
          error: function error(xhr, status, _error) {
            var responseData = xhr.responseJSON || xhr.responseText;
            reject({
              success: false,
              error: _error,
              data: responseData,
              transferStatus: status,
              xhr: xhr
            });
          }
        });
      });
    }
  }, {
    key: "get",
    value: function get(uri, data) {
      return this._runAjax(uri, data, 'get');
    }
  }, {
    key: "post",
    value: function post(uri, data) {
      return this._runAjax(uri, data, 'post');
    }
  }, {
    key: "updatePjax",
    value: function updatePjax() {
      $(document).trigger('pjax:refresh');
    }
  }, {
    key: "getMenus",
    value: function getMenus() {
      var _this = this;
      return new Promise(function (resolve, reject) {
        _this.get(window.GlobalSideMenuData.getUrl).then(function (result) {
          LOG.log("menues", result);
          _this.StateManager.commit('setMenu', result.data);
          _this.updatePjax();
          resolve();
        }, reject);
      });
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (Actions);

/***/ }),

/***/ "./src/components/GlobalSidemenu.js":
/*!******************************************!*\
  !*** ./src/components/GlobalSidemenu.js ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _meta_lib_ConsoleShim_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../meta/lib/ConsoleShim.js */ "../meta/lib/ConsoleShim.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Global Sidemenu Component - Vanilla JS
 * Main sidebar with resizable functionality
 */


var LOG = new _meta_lib_ConsoleShim_js__WEBPACK_IMPORTED_MODULE_0__["default"]('globalsidepanel');
var GlobalSidemenu = /*#__PURE__*/function () {
  function GlobalSidemenu(container, store, actions, components) {
    _classCallCheck(this, GlobalSidemenu);
    this.container = typeof container === 'string' ? document.querySelector(container) : container;
    this.store = store;
    this.actions = actions;
    this.components = components;

    // Component data
    this.activeMenuIndex = 0;
    this.initialPos = {
      x: 0,
      y: 0
    };
    this.isMouseDown = false;
    this.isMouseDownTimeOut = null;

    // Bind methods
    this.mousedown = this.mousedown.bind(this);
    this.mouseup = this.mouseup.bind(this);
    this.mouseleave = this.mouseleave.bind(this);
    this.mousemove = this.mousemove.bind(this);
    this.init();
  }
  return _createClass(GlobalSidemenu, [{
    key: "sideBarWidth",
    get: function get() {
      return this.store.get('sidebarwidth');
    },
    set: function set(value) {
      this.store.commit('setSidebarwidth', value);
    }
  }, {
    key: "getWindowHeight",
    get: function get() {
      return $(document).height();
    }
  }, {
    key: "calculateSideBarMenuHeight",
    get: function get() {
      var pjaxContent = document.getElementById('pjax-content');
      return pjaxContent ? pjaxContent.offsetHeight : 400;
    }
  }, {
    key: "currentMenue",
    get: function get() {
      return this.store.get('menu') || [];
    }
  }, {
    key: "translate",
    value: function translate(string) {
      return window.GlobalSideMenuData.i10n[string] || string;
    }
  }, {
    key: "init",
    value: function init() {
      var _this = this;
      this.actions.getMenus().then(function () {
        _this.controlActiveLink();
        _this.render();
        _this.attachEventListeners();
        _this.mounted();
      });
    }
  }, {
    key: "render",
    value: function render() {
      if (!this.container) return;
      this.container.innerHTML = "\n            <div\n                id=\"sidebar\"\n                class=\"d-flex col-lg-4 ls-ba position-relative transition-animate-width bg-white py-4 h-100\"\n                style=\"min-width: 250px; width: ".concat(this.sideBarWidth, "px;\"\n            >\n                <div class=\"col-12\">\n                    <div class=\"mainMenu col-12\">\n                        <div id=\"sidemenu-container\" style=\"min-height: ").concat(this.calculateSideBarMenuHeight, "px\"></div>\n                    </div>\n                </div>\n                <div class=\"resize-handle ls-flex-column\" style=\"height: 100%; max-height: ").concat(this.getWindowHeight, "px\">\n                    <button\n                        id=\"resize-handle-btn\"\n                        class=\"btn\"\n                        style=\"display: ").concat(this.store.get('isCollapsed') ? 'none' : 'block', "\"\n                    >\n                        <svg width=\"9\" height=\"14\" viewBox=\"0 0 9 14\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                            <path fill-rule=\"evenodd\" clip-rule=\"evenodd\"\n                                d=\"M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z\"\n                                fill=\"currentColor\" />\n                        </svg>\n                    </button>\n                </div>\n                ").concat(this.isMouseDown ? '<div class="mouseup-support" style="position:fixed; inset: 0;"></div>' : '', "\n            </div>\n        ");
      this.sidebarEl = this.container.querySelector('#sidebar');
      this.renderSubComponents();
    }
  }, {
    key: "renderSubComponents",
    value: function renderSubComponents() {
      var sidemenuContainer = this.container.querySelector('#sidemenu-container');
      if (sidemenuContainer && this.components.Sidemenu) {
        new this.components.Sidemenu(sidemenuContainer, this.store, this.currentMenue);
      }
    }
  }, {
    key: "attachEventListeners",
    value: function attachEventListeners() {
      var _this2 = this;
      // Resize handle
      var resizeBtn = this.container.querySelector('#resize-handle-btn');
      if (resizeBtn) {
        resizeBtn.addEventListener('mousedown', this.mousedown);
        resizeBtn.addEventListener('click', function (e) {
          return e.preventDefault();
        });
      }

      // Sidebar mouse events
      if (this.sidebarEl) {
        this.sidebarEl.addEventListener('mouseleave', this.mouseleave);
        this.sidebarEl.addEventListener('mouseup', this.mouseup);
      }

      // Body mousemove - only add once
      if (!this.mousemoveAttached) {
        document.body.addEventListener('mousemove', this.mousemove);
        this.mousemoveAttached = true;
      }

      // Subscribe to store changes - only subscribe once
      if (!this.storeSubscribed) {
        this.store.subscribe(function (key, newValue, oldValue) {
          if (key === 'sidebarwidth' || key === 'menu') {
            _this2.update();
          }
        });
        this.storeSubscribed = true;
      }
    }
  }, {
    key: "mounted",
    value: function mounted() {
      var _this3 = this;
      $(document).on("vue-redraw", function () {
        _this3.update();
      });
      $(document).trigger("vue-reload-remote");
    }
  }, {
    key: "update",
    value: function update() {
      this.render();
      this.attachEventListeners();
    }
  }, {
    key: "controlActiveLink",
    value: function controlActiveLink() {
      var currentUrl = window.location.href;
      var lastMenuItemObject = false;
      if (this.currentMenue.entries) {
        LS.ld.each(this.currentMenue.entries, function (itmm) {
          lastMenuItemObject = LS.ld.endsWith(currentUrl, itmm.partial.split('/').pop()) ? itmm : lastMenuItemObject;
        });
      }
      if (lastMenuItemObject === false) {
        lastMenuItemObject = {
          partial: 'redundant/_generaloptions_panel'
        };
      }
      this.store.commit('setLastMenuItemOpen', lastMenuItemObject.partial.split('/').pop());
    }
  }, {
    key: "mousedown",
    value: function mousedown(e) {
      this.isMouseDown = true;
      $("#sidebar").removeClass("transition-animate-width");
      $("#pjax-content").removeClass("transition-animate-width");
    }
  }, {
    key: "mouseup",
    value: function mouseup(e) {
      if (this.isMouseDown) {
        this.isMouseDown = false;
        this.store.commit('setIsCollapsed', false);
        if (parseInt(this.sideBarWidth) < 250) {
          this.sideBarWidth = 250;
        }
        $("#sidebar").addClass("transition-animate-width");
        $("#pjax-content").removeClass("transition-animate-width");
        this.update();
      }
    }
  }, {
    key: "mouseleave",
    value: function mouseleave(e) {
      var _this4 = this;
      if (this.isMouseDown) {
        this.isMouseDownTimeOut = setTimeout(function () {
          _this4.mouseup(e);
        }, 1000);
      }
    }
  }, {
    key: "mousemove",
    value: function mousemove(e) {
      if (this.isMouseDown) {
        // Prevent unwanted value on dragend
        if (e.screenX === 0 && e.screenY === 0) {
          return;
        }
        if (e.clientX > screen.width / 2) {
          this.sideBarWidth = screen.width / 2;
          return;
        }
        this.sideBarWidth = e.pageX - 4;
        window.clearTimeout(this.isMouseDownTimeOut);
        this.isMouseDownTimeOut = null;
      }
    }
  }, {
    key: "updatePjaxLinks",
    value: function updatePjaxLinks() {
      window.LS.doToolTip();
    }
  }, {
    key: "destroy",
    value: function destroy() {
      var resizeBtn = this.container.querySelector('#resize-handle-btn');
      if (resizeBtn) {
        resizeBtn.removeEventListener('mousedown', this.mousedown);
      }
      document.body.removeEventListener('mousemove', this.mousemove);
      $(document).off("vue-redraw");
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (GlobalSidemenu);

/***/ }),

/***/ "./src/components/Sidemenu.js":
/*!************************************!*\
  !*** ./src/components/Sidemenu.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _meta_lib_ConsoleShim_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../meta/lib/ConsoleShim.js */ "../meta/lib/ConsoleShim.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Sidemenu Component - Vanilla JS
 * Displays menu items
 */


var LOG = new _meta_lib_ConsoleShim_js__WEBPACK_IMPORTED_MODULE_0__["default"]('globalsidepanel');
var Sidemenu = /*#__PURE__*/function () {
  function Sidemenu(container, store, menu) {
    _classCallCheck(this, Sidemenu);
    this.container = typeof container === 'string' ? document.querySelector(container) : container;
    this.store = store;
    this.menu = menu;
    this.render();
    this.attachEventListeners();
    this.updatePjaxLinks();
    this.redoTooltips();
  }
  return _createClass(Sidemenu, [{
    key: "sortedMenuEntries",
    get: function get() {
      if (!this.menu || !this.menu.entries) return [];
      return LS.ld.orderBy(this.menu.entries, function (a) {
        return parseInt(a.ordering || 999999);
      }, ['asc']);
    }
  }, {
    key: "render",
    value: function render() {
      var _this = this;
      if (!this.container) return;
      var level = this.menu.level || 0;
      this.container.innerHTML = "\n            <ul class=\"list-group subpanel col-12 level-".concat(level, "\">\n                ").concat(this.sortedMenuEntries.map(function (menuItem) {
        return _this.renderMenuItem(menuItem);
      }).join(''), "\n            </ul>\n        ");
    }
  }, {
    key: "renderMenuItem",
    value: function renderMenuItem(menuItem) {
      var href = this.getHref(menuItem);
      var linkClass = this.getLinkClass(menuItem);
      var target = menuItem.link_external ? '_blank' : '';
      var tooltip = this.reConvertHTML(menuItem.menu_description);
      var isSelected = this.store.get('lastMenuItemOpen') === menuItem.partial.split('/').pop();
      return "\n            <a data-menu-item=\"".concat(menuItem.partial.split('/').pop(), "\"\n               href=\"").concat(href, "\"\n               target=\"").concat(target, "\"\n               id=\"sidemenu_").concat(menuItem.name, "\"\n               class=\"list-group-item ").concat(linkClass, "\"\n               title=\"").concat(tooltip, "\"\n               data-bs-toggle=\"tooltip\">\n                <div class=\"col-12 ").concat(menuItem.menu_class || '', "\">\n                    <div class=\"ls-space padding all-0 ").concat(isSelected ? 'col-md-10' : 'col-12', "\">\n                        ").concat(this.renderMenuIcon(menuItem), "\n                        <span>").concat(menuItem.menu_title, "</span>\n                        ").concat(menuItem.link_external ? '<i class="ri-external-link-fill">&nbsp;</i>' : '', "\n                    </div>\n                    ").concat(isSelected ? "\n                        <div class=\"col-md-2 text-center ls-space padding all-0 background white\">\n                            <i class=\"ri-arrow-right-s-line\">&nbsp;</i>\n                        </div>\n                    " : '', "\n                </div>\n            </a>\n        ");
    }
  }, {
    key: "renderMenuIcon",
    value: function renderMenuIcon(menuItem) {
      if (!menuItem.menu_icon) return '';
      var iconType = menuItem.menu_icon_type || 'fontawesome';
      var iconClass = '';
      if (iconType === 'fontawesome') {
        iconClass = "fa fa-".concat(menuItem.menu_icon);
      } else if (iconType === 'remix') {
        iconClass = "ri-".concat(menuItem.menu_icon);
      } else if (iconType === 'iconClass') {
        iconClass = menuItem.menu_icon;
      }
      return "<i class=\"".concat(iconClass, "\"></i>");
    }
  }, {
    key: "getHref",
    value: function getHref(menuItem) {
      var menuItemPartial = menuItem.partial.split('/').pop();
      return LS.createUrl(window.GlobalSideMenuData.baseLinkUrl, {
        partial: menuItemPartial
      });
    }
  }, {
    key: "getLinkClass",
    value: function getLinkClass(menuItem) {
      var classes = "ls-flex-row nowrap ";
      var isSelected = this.store.get('lastMenuItemOpen') === menuItem.partial.split('/').pop();
      classes += isSelected ? 'selected ' : ' ';
      return classes;
    }
  }, {
    key: "reConvertHTML",
    value: function reConvertHTML(string) {
      if (!string) return '';

      // Using Unicode escape sequences to avoid Babel parse errors with smart quotes
      var chars = ["'", "©", "Û", "®", "ž", "Ü", "Ÿ", "Ý", "$", "Þ", "%", "¡", "ß", "¢", "à", "£", "á", "À", "¤", "â", "Á", "¥", "ã", "Â", "¦", "ä", "Ã", "§", "å", "Ä", "¨", "æ", "Å", "©", "ç", "Æ", "ª", "è", "Ç", "«", "é", "È", "¬", "ê", "É", "­", "ë", "Ê", "®", "ì", "Ë", "¯", "í", "Ì", "°", "î", "Í", "±", "ï", "Î", "²", "ð", "Ï", "³", "ñ", "Ð", "´", "ò", "Ñ", "µ", "ó", "Õ", "¶", "ô", "Ö", "·", "õ", "Ø", "¸", "ö", "Ù", "¹", "÷", "Ú", "º", "ø", "Û", "»", "ù", "Ü", "@", "¼", "ú", "Ý", "½", "û", "Þ", "€", "¾", "ü", "ß", "¿", "ý", "à", "‚", "À", "þ", "á", "ƒ", "Á", "ÿ", "å", "„", "Â", "æ", "…", "Ã", "ç", "†", "Ä", "è", "‡", "Å", "é", "ˆ", "Æ", "ê", "‰", "Ç", "ë", "Š", "È", "ì", "‹", "É", "í", "Œ", "Ê", "î", "Ë", "ï", "Ž", "Ì", "ð", "Í", "ñ", "Î", "ò", "\u2018", "Ï", "ó", "\u2019", "Ð", "ô", "\u201C", "Ñ", "õ", "\u201D", "Ò", "ö", "•", "Ó", "ø", "–", "Ô", "ù", "—", "Õ", "ú", "˜", "Ö", "û", "™", "×", "ý", "š", "Ø", "þ", "›", "Ù", "ÿ", "œ", "Ú"];
      var codes = ["&#039;", "&copy;", "&#219;", "&reg;", "&#158;", "&#220;", "&#159;", "&#221;", "&#36;", "&#222;", "&#37;", "&#161;", "&#223;", "&#162;", "&#224;", "&#163;", "&#225;", "&Agrave;", "&#164;", "&#226;", "&Aacute;", "&#165;", "&#227;", "&Acirc;", "&#166;", "&#228;", "&Atilde;", "&#167;", "&#229;", "&Auml;", "&#168;", "&#230;", "&Aring;", "&#169;", "&#231;", "&AElig;", "&#170;", "&#232;", "&Ccedil;", "&#171;", "&#233;", "&Egrave;", "&#172;", "&#234;", "&Eacute;", "&#173;", "&#235;", "&Ecirc;", "&#174;", "&#236;", "&Euml;", "&#175;", "&#237;", "&Igrave;", "&#176;", "&#238;", "&Iacute;", "&#177;", "&#239;", "&Icirc;", "&#178;", "&#240;", "&Iuml;", "&#179;", "&#241;", "&ETH;", "&#180;", "&#242;", "&Ntilde;", "&#181;", "&#243;", "&Otilde;", "&#182;", "&#244;", "&Ouml;", "&#183;", "&#245;", "&Oslash;", "&#184;", "&#246;", "&Ugrave;", "&#185;", "&#247;", "&Uacute;", "&#186;", "&#248;", "&Ucirc;", "&#187;", "&#249;", "&Uuml;", "&#64;", "&#188;", "&#250;", "&Yacute;", "&#189;", "&#251;", "&THORN;", "&#128;", "&#190;", "&#252", "&szlig;", "&#191;", "&#253;", "&agrave;", "&#130;", "&#192;", "&#254;", "&aacute;", "&#131;", "&#193;", "&#255;", "&aring;", "&#132;", "&#194;", "&aelig;", "&#133;", "&#195;", "&ccedil;", "&#134;", "&#196;", "&egrave;", "&#135;", "&#197;", "&eacute;", "&#136;", "&#198;", "&ecirc;", "&#137;", "&#199;", "&euml;", "&#138;", "&#200;", "&igrave;", "&#139;", "&#201;", "&iacute;", "&#140;", "&#202;", "&icirc;", "&#203;", "&iuml;", "&#142;", "&#204;", "&eth;", "&#205;", "&ntilde;", "&#206;", "&ograve;", "&#145;", "&#207;", "&oacute;", "&#146;", "&#208;", "&ocirc;", "&#147;", "&#209;", "&otilde;", "&#148;", "&#210;", "&ouml;", "&#149;", "&#211;", "&oslash;", "&#150;", "&#212;", "&ugrave;", "&#151;", "&#213;", "&uacute;", "&#152;", "&#214;", "&ucirc;", "&#153;", "&#215;", "&yacute;", "&#154;", "&#216;", "&thorn;", "&#155;", "&#217;", "&yuml;", "&#156;", "&#218;"];
      LS.ld.each(codes, function (code, i) {
        string = string.replace(new RegExp(code, 'g'), chars[i]);
      });
      return string;
    }
  }, {
    key: "attachEventListeners",
    value: function attachEventListeners() {
      var _this2 = this;
      var menuItems = this.container.querySelectorAll('[data-menu-item]');
      menuItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
          var menuItemPartial = item.getAttribute('data-menu-item');
          _this2.store.commit('setLastMenuItemOpen', menuItemPartial);
          LOG.log('Opened Menuitem', menuItemPartial);
        });
      });
    }
  }, {
    key: "updatePjaxLinks",
    value: function updatePjaxLinks() {
      // Force update of pjax links
    }
  }, {
    key: "redoTooltips",
    value: function redoTooltips() {
      if (window.LS && window.LS.doToolTip) {
        window.LS.doToolTip();
      }
    }
  }, {
    key: "update",
    value: function update() {
      this.render();
      this.attachEventListeners();
      this.updatePjaxLinks();
      this.redoTooltips();
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (Sidemenu);

/***/ }),

/***/ "./src/stateConfig.js":
/*!****************************!*\
  !*** ./src/stateConfig.js ***!
  \****************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createDefaultState: function() { return /* binding */ createDefaultState; },
/* harmony export */   createGetters: function() { return /* binding */ createGetters; },
/* harmony export */   createMutations: function() { return /* binding */ createMutations; }
/* harmony export */ });
/**
 * State configuration for globalsidepanel
 * Defines default state, mutations, and getters
 */

/**
 * Create default state
 * @param {string|number} userid
 * @returns {Object}
 */
function createDefaultState(userid) {
  return {
    currentUser: userid,
    language: '',
    sidebarwidth: 380,
    menu: null,
    lastMenuItemOpen: '',
    isCollapsed: false
  };
}

/**
 * Create mutations for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
function createMutations(StateManager) {
  return {
    setMenu: function setMenu(menu) {
      StateManager.set('menu', menu);
    },
    setLastMenuItemOpen: function setLastMenuItemOpen(menuItem) {
      StateManager.set('lastMenuItemOpen', menuItem);
    },
    setSidebarwidth: function setSidebarwidth(width) {
      StateManager.set('sidebarwidth', width);
    },
    setLanguage: function setLanguage(language) {
      StateManager.set('language', language);
    },
    setCurrentUser: function setCurrentUser(user) {
      StateManager.set('currentUser', user);
    },
    setIsCollapsed: function setIsCollapsed(collapsed) {
      StateManager.set('isCollapsed', collapsed);
    }
  };
}

/**
 * Create getters for StateManager (optional, can be expanded as needed)
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
function createGetters(StateManager) {
  return {
    // Add computed properties here if needed
  };
}

/***/ }),

/***/ "./src/store.js":
/*!**********************!*\
  !*** ./src/store.js ***!
  \**********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initStore: function() { return /* binding */ initStore; }
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _stateConfig_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./stateConfig.js */ "./src/stateConfig.js");
/**
 * Store initialization for Global Sidebar Panel
 * Uses unified StateManager implementation
 */



/**
 * Initialize store with user ID
 * @param {string|number} userid
 * @returns {Object} StateManager instance
 */
function initStore() {
  var userid = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].init({
    storagePrefix: 'lsglobalsidemenu',
    userid: userid,
    defaultState: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createDefaultState)(userid),
    mutations: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createMutations)(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"]),
    getters: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createGetters)(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"])
  });
  return _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"];
}
/* harmony default export */ __webpack_exports__["default"] = (_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"]);

/***/ })

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
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
!function() {
/*!************************************!*\
  !*** ./src/globalsidepanelmain.js ***!
  \************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./store */ "./src/store.js");
/* harmony import */ var _actions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./actions */ "./src/actions.js");
/* harmony import */ var _components_GlobalSidemenu__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/GlobalSidemenu */ "./src/components/GlobalSidemenu.js");
/* harmony import */ var _components_Sidemenu__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/Sidemenu */ "./src/components/Sidemenu.js");
/**
 * Global Sidebar Panel
 * Vanilla JavaScript implementation
 */






// Initialize the application
var init = function init() {
  // Get container element
  var container = document.getElementById('global-sidebar-container');
  if (!container) {
    console.error('Global sidebar container not found');
    return null;
  }

  // Check if required global data exists
  if (!window.GlobalSideMenuData) {
    console.error('GlobalSideMenuData not found');
    return null;
  }

  // Create user ID for store
  var userid = window.GlobalSideMenuData.sgid ? LS.globalUserId + '-' + window.GlobalSideMenuData.sgid : LS.globalUserId;

  // Initialize store with unified API
  var store = (0,_store__WEBPACK_IMPORTED_MODULE_0__.initStore)(userid);

  // Initialize actions
  var actions = new _actions__WEBPACK_IMPORTED_MODULE_1__["default"](store);

  // Initialize main component
  var globalSidePanel = new _components_GlobalSidemenu__WEBPACK_IMPORTED_MODULE_2__["default"](container, store, actions, {
    Sidemenu: _components_Sidemenu__WEBPACK_IMPORTED_MODULE_3__["default"]
  });
  return {
    store: store,
    actions: actions,
    component: globalSidePanel
  };
};

// Wait for DOM to be ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    var app = init();
    // Export to window for external access
    window.GlobalSidePanel = app;
  });
} else {
  var app = init();
  // Export to window for external access
  window.GlobalSidePanel = app;
}

// Export for module usage
/* harmony default export */ __webpack_exports__["default"] = ({
  init: init,
  StateManager: _store__WEBPACK_IMPORTED_MODULE_0__["default"],
  Actions: _actions__WEBPACK_IMPORTED_MODULE_1__["default"],
  GlobalSidemenu: _components_GlobalSidemenu__WEBPACK_IMPORTED_MODULE_2__["default"],
  Sidemenu: _components_Sidemenu__WEBPACK_IMPORTED_MODULE_3__["default"]
});
}();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});
//# sourceMappingURL=globalsidepanel.js.map