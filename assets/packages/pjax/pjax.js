(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = global || self, global.Pjax = factory());
}(this, function () { 'use strict';

  function _typeof(obj) {
    if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
      _typeof = function (obj) {
        return typeof obj;
      };
    } else {
      _typeof = function (obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
      };
    }

    return _typeof(obj);
  }

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }

  function _toConsumableArray(arr) {
    return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread();
  }

  function _arrayWithoutHoles(arr) {
    if (Array.isArray(arr)) {
      for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

      return arr2;
    }
  }

  function _iterableToArray(iter) {
    if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
  }

  function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance");
  }

  if (!Array.prototype.from) {
    Array.prototype.from = function (enumerable) {
      var arr = [];

      for (var i = enumerable.length; i--; arr.unshift(enumerable[i])) {
      }

      return arr;
    };
  }

  var forEachEls = function forEachEls(els, fn, ctx) {
    if (els instanceof HTMLCollection || els instanceof NodeList || els instanceof Array) {
      return Array.prototype.from(els).forEach(function (el, i) {
        return fn.call(ctx, el, i);
      });
    } // assume simple dom element


    return fn.call(ctx, els);
  };
  var getElements = function getElements(el) {
    return el.querySelectorAll(this.options.elements);
  };
  var clone = function clone(obj) {
    if (null === obj || "object" != _typeof(obj)) {
      return obj;
    }

    var copy = obj.constructor();

    for (var attr in obj) {
      if (attr in obj) {
        copy[attr] = obj[attr];
      }
    }

    return copy;
  };
  var isSupported = function isSupported() {
    // Borrowed wholesale from https://github.com/defunkt/jquery-pjax
    return window.history && window.history.pushState && window.history.replaceState && // pushState isn’t reliable on iOS until 5.
    !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]\D|WebApps\/.+CFNetwork)/);
  };
  var newUid = function () {
    var counter = 0;
    return function () {
      var id = "pjax" + new Date().getTime() + "_" + counter;
      counter++;
      return id;
    };
  }();
  function getUtility () {
    return {
      forEachEls: forEachEls,
      getElements: getElements,
      clone: clone,
      isSupported: isSupported,
      newUid: newUid
    };
  }

  function on (els, events, listener, useCapture) {
    events = typeof events === "string" ? events.split(" ") : events;
    events.forEach(function (e) {
      forEachEls(els, function (el) {
        el.addEventListener(e, listener, useCapture);
      });
    });
  }

  /* eslint no-console: "off" */
  var ConsoleShim =
  /*#__PURE__*/
  function () {
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

    _createClass(ConsoleShim, [{
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
      } //Start grouping logs

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
      } //Stop grouping logs

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
      } //Simplest mechanism to log stuff
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
      } //Trace back the apply.
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

        this.log.log(args);

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
        this.log.log("Took ".concat(Math.floor(diff / (1000 * 60 * 60)), " hours, ").concat(Math.floor(diff / (1000 * 60)), " minutes and ").concat(Math.floor(diff / 1000), " seconds ( ").concat(diff, " ms)"));
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

        this.log.log('--- ERROR ---');
        this.log.log(args);
      }
    }, {
      key: "warn",
      value: function warn() {
        var args = this._insertParamToArguments(arguments);

        if (typeof console.warn === 'function') {
          console.warn.apply(console, args);
          return;
        }

        this.log.log('--- WARN ---');
        this.log.log(args);
      }
    }]);

    return ConsoleShim;
  }();

  function log () {
    console.log("PJAX options", this.options);
    this.options.logObject = new ConsoleShim('PJAX ->', !this.options.debug);
    return this.options.logObject;
  }

  function trigger (els, events, opts) {
    events = typeof events === "string" ? events.split(" ") : events;
    events.forEach(function (e) {
      var event; // = new CustomEvent(e) // doesn't everywhere yet

      event = document.createEvent("HTMLEvents");
      event.initEvent(e, true, true);
      event.eventName = e;

      if (opts) {
        Object.keys(opts).forEach(function (key) {
          event[key] = opts[key];
        });
      }

      forEachEls(els, function (el) {
        var domFix = false;

        if (!el.parentNode && el !== document && el !== window) {
          // THANKS YOU IE (9/10//11 concerned)
          // dispatchEvent doesn't work if element is not in the dom
          domFix = true;
          document.body.appendChild(el);
        }

        el.dispatchEvent(event);

        if (domFix) {
          el.parentNode.removeChild(el);
        }
      });
    });
  }

  function doRequest (location, options, callback) {
    options = options || {};
    var requestMethod = options.requestMethod || "GET";
    var requestPayload = options.requestPayloadString || null;
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
      if (request.readyState === 4) {
        if (request.status === 200) {
          callback(request.responseText, request);
        } else {
          callback(null, request);
        }
      }
    }; // Add a timestamp as part of the query string if cache busting is enabled


    if (this.options.cacheBust) {
      location += (!/[?&]/.test(location) ? "?" : "&") + new Date().getTime();
    }

    request.open(requestMethod.toUpperCase(), location, true);
    request.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Add the request payload if available

    if (options.requestPayloadString != undefined && options.requestPayloadString != "") {
      // Send the proper header information along with the request
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    }

    request.send(requestPayload);
    return request;
  }

  // var trigger = require("./lib/events/trigger.js")

  var defaultSwitches = {
    outerHTML: function outerHTML(oldEl, newEl) {
      oldEl.outerHTML = newEl.outerHTML;
      this.onSwitch();
    },
    innerHTML: function innerHTML(oldEl, newEl) {
      oldEl.innerHTML = newEl.innerHTML;
      oldEl.className = newEl.className;
      this.onSwitch();
    },
    sideBySide: function sideBySide(oldEl, newEl, options, switchOptions) {
      var elsToRemove = [];
      var elsToAdd = [];
      var fragToAppend = document.createDocumentFragment(); // height transition are shitty on safari
      // so commented for now (until I found something ?)
      // var relevantHeight = 0

      var animationEventNames = "animationend webkitAnimationEnd MSAnimationEnd oanimationend";
      var animatedElsNumber = 0;

      var sexyAnimationEnd = function (e) {
        if (e.target != e.currentTarget) {
          // end triggered by an animation on a child
          return;
        }

        animatedElsNumber--;

        if (animatedElsNumber <= 0 && elsToRemove) {
          elsToRemove.forEach(function (el) {
            // browsing quickly can make the el
            // already removed by last page update ?
            if (el.parentNode) {
              el.parentNode.removeChild(el);
            }
          });
          elsToAdd.forEach(function (el) {
            el.className = el.className.replace(el.getAttribute("data-pjax-classes"), "");
            el.removeAttribute("data-pjax-classes"); // Pjax.off(el, animationEventNames, sexyAnimationEnd, true)
          });
          elsToAdd = null; // free memory

          elsToRemove = null; // free memory
          // assume the height is now useless (avoid bug since there is overflow hidden on the parent)
          // oldEl.style.height = "auto"
          // this is to trigger some repaint (example: picturefill)

          this.onSwitch(); // Pjax.trigger(window, "scroll")
        }
      }.bind(this); // Force height to be able to trigger css animation
      // here we get the relevant height
      // oldEl.parentNode.appendChild(newEl)
      // relevantHeight = newEl.getBoundingClientRect().height
      // oldEl.parentNode.removeChild(newEl)
      // oldEl.style.height = oldEl.getBoundingClientRect().height + "px"


      switchOptions = switchOptions || {};
      Array.from(oldEl.childNodes).forEach(function (el) {
        elsToRemove.push(el);

        if (el.classList && !el.classList.contains("js-Pjax-remove")) {
          // for fast switch, clean element that just have been added, & not cleaned yet.
          if (el.hasAttribute("data-pjax-classes")) {
            el.className = el.className.replace(el.getAttribute("data-pjax-classes"), "");
            el.removeAttribute("data-pjax-classes");
          }

          el.classList.add("js-Pjax-remove");

          if (switchOptions.callbacks && switchOptions.callbacks.removeElement) {
            switchOptions.callbacks.removeElement(el);
          }

          if (switchOptions.classNames) {
            el.className += " " + switchOptions.classNames.remove + " " + (options.backward ? switchOptions.classNames.backward : switchOptions.classNames.forward);
          }

          animatedElsNumber++;
          on(el, animationEventNames, sexyAnimationEnd, true);
        }
      });
      Array.from(newEl.childNodes).forEach(function (el) {
        if (el.classList) {
          var addClasses = "";

          if (switchOptions.classNames) {
            addClasses = " js-Pjax-add " + switchOptions.classNames.add + " " + (options.backward ? switchOptions.classNames.forward : switchOptions.classNames.backward);
          }

          if (switchOptions.callbacks && switchOptions.callbacks.addElement) {
            switchOptions.callbacks.addElement(el);
          }

          el.className += addClasses;
          el.setAttribute("data-pjax-classes", addClasses);
          elsToAdd.push(el);
          fragToAppend.appendChild(el);
          animatedElsNumber++;
          on(el, animationEventNames, sexyAnimationEnd, true);
        }
      }); // pass all className of the parent

      oldEl.className = newEl.className;
      oldEl.appendChild(fragToAppend); // oldEl.style.height = relevantHeight + "px"
    }
  };

  function getSwitchSelectors () {
    var _this = this;

    return function (switches, switchesOptions, selectors, fromEl, toEl, options) {
      selectors.forEach(function (selector) {
        var newEls = fromEl.querySelectorAll(selector);
        var oldEls = toEl.querySelectorAll(selector);

        _this.log.log("Pjax switch", selector, newEls, oldEls);

        if (newEls.length !== oldEls.length) {
          var throwError = options.onDomDiffers(toEl, fromEl);

          if (throwError) {
            throw "DOM doesn’t look the same on new loaded page: ’" + selector + "’ - new " + newEls.length + ", old " + oldEls.length;
          }
        }

        forEachEls(newEls, function (newEl, i) {
          var oldEl = oldEls[i];

          if (oldEl == undefined) {
            return;
          }

          this.log.log("newEl", newEl, "oldEl", oldEl);

          if (switches[selector]) {
            switches[selector].call(this, oldEl, newEl, options, switchesOptions[selector]);
          } else {
            defaultSwitches.outerHTML.call(this, oldEl, newEl, options);
          }
        }, _this);
      });
    };
  }

  if (!Function.prototype.bind) {
    Function.prototype.bind = function (oThis) {
      if (typeof this !== "function") {
        // closest thing possible to the ECMAScript 5 internal IsCallable function
        throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
      }

      var aArgs = Array.prototype.slice.call(arguments, 1);
      var that = this;

      var Fnoop = function Fnoop() {};

      var fBound = function fBound() {
        return that.apply(this instanceof Fnoop && oThis ? this : oThis, aArgs.concat(Array.prototype.slice.call(arguments)));
      };

      Fnoop.prototype = this.prototype;
      fBound.prototype = new Fnoop();
      return fBound;
    };
  }

  var attrClick = "data-pjax-click-state";
  var attrKey = "data-pjax-keyup-state";

  var linkAction = function linkAction(el, event) {
    // Don’t break browser special behavior on links (like page in new window)
    if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      el.setAttribute(attrClick, "modifier");
      return;
    } // we do test on href now to prevent unexpected behavior if for some reason
    // user have href that can be dynamically updated
    // Ignore external links.


    if (el.protocol !== window.location.protocol || el.host !== window.location.host) {
      el.setAttribute(attrClick, "external");
      return;
    } // Ignore click if we are on an anchor on the same page


    if (el.pathname === window.location.pathname && el.hash.length > 0) {
      el.setAttribute(attrClick, "anchor-present");
      return;
    } // Ignore anchors on the same page (keep native behavior)


    if (el.hash && el.href.replace(el.hash, "") === window.location.href.replace(location.hash, "")) {
      el.setAttribute(attrClick, "anchor");
      return;
    } // Ignore empty anchor "foo.html#"


    if (el.href === window.location.href.split("#")[0] + "#") {
      el.setAttribute(attrClick, "anchor-empty");
      return;
    }

    event.preventDefault(); // don’t do "nothing" if user try to reload the page by clicking the same link twice

    if (this.options.currentUrlFullReload && el.href === window.location.href.split("#")[0]) {
      el.setAttribute(attrClick, "reload");
      this.reload();
      return;
    }

    this.options.requestOptions = this.options.requestOptions || {};
    el.setAttribute(attrClick, "load");
    this.loadUrl(el.href, clone(this.options));
  };

  var isDefaultPrevented = function isDefaultPrevented(event) {
    return event.defaultPrevented || event.returnValue === false;
  };

  function getAttachLink () {
    var _this = this;

    return function (el) {
      on(el, "click", function (event) {
        if (isDefaultPrevented(event)) {
          return;
        }

        linkAction.call(_this, el, event);
      });
      on(el, "keyup", function (event) {
        if (isDefaultPrevented(event)) {
          return;
        } // Don’t break browser special behavior on links (like page in new window)


        if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          el.setAttribute(attrKey, "modifier");
          return;
        }

        if (event.keyCode == 13) {
          linkAction.call(_this, el, event);
        }
      });
    };
  }

  var attrClick$1 = "data-pjax-submit-state";

  var formAction = function formAction(el, event) {
    this.options.requestOptions = {
      requestUrl: el.getAttribute('action') || window.location.href,
      requestMethod: el.getAttribute('method') || 'GET'
    }; //create a testable virtual link of the form action

    var virtLinkElement = document.createElement('a');
    virtLinkElement.setAttribute('href', this.options.requestOptions.requestUrl); // Ignore external links.

    if (virtLinkElement.protocol !== window.location.protocol || virtLinkElement.host !== window.location.host) {
      el.setAttribute(attrClick$1, "external");
      return;
    } // Ignore click if we are on an anchor on the same page


    if (virtLinkElement.pathname === window.location.pathname && virtLinkElement.hash.length > 0) {
      el.setAttribute(attrClick$1, "anchor-present");
      return;
    } // Ignore empty anchor "foo.html#"


    if (virtLinkElement.href === window.location.href.split("#")[0] + "#") {
      el.setAttribute(attrClick$1, "anchor-empty");
      return;
    } // if declared as a full reload, just normally submit the form


    if (this.options.currentUrlFullReload) {
      el.setAttribute(attrClick$1, "reload");
      return;
    }

    event.preventDefault();
    var nameList = [];
    var paramObject = [];

    for (var elementKey in el.elements) {
      var element = el.elements[elementKey];

      if (!!element.name && element.attributes !== undefined && element.tagName.toLowerCase() !== 'button') {
        if (element.type !== 'checkbox' && element.type !== 'radio' || element.checked) {
          if (nameList.indexOf(element.name) === -1) {
            nameList.push(element.name);

            if (String(element.nodeName).toLowerCase() === 'select' && element.multiple == true) {
              var selected = Array.from(element.options).map(function (item) {
                return item.selected ? item.value : null;
              });
              paramObject.push({
                name: encodeURIComponent(element.name),
                value: selected
              });
              return;
            }

            paramObject.push({
              name: encodeURIComponent(element.name),
              value: encodeURIComponent(element.value)
            });
          }
        }
      }
    } //Creating a getString


    var paramsString = paramObject.map(function (value) {
      return value.name + "=" + value.value;
    }).join('&');
    this.options.requestOptions.requestPayload = paramObject;
    this.options.requestOptions.requestPayloadString = paramsString;
    el.setAttribute(attrClick$1, "submit");
    this.loadUrl(virtLinkElement.href, clone(this.options));
  };

  var isDefaultPrevented$1 = function isDefaultPrevented(event) {
    return event.defaultPrevented || event.returnValue === false;
  };

  function getAttachForm () {
    var _this = this;

    return function (el) {
      on(el, "submit", function (event) {
        if (isDefaultPrevented$1(event)) {
          return;
        }

        formAction.call(_this, el, event);
      });
    };
  }

  var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

  function createCommonjsModule(fn, module) {
  	return module = { exports: {} }, fn(module, module.exports), module.exports;
  }

  var promise = createCommonjsModule(function (module) {
  (function (root) {

    // Store setTimeout reference so promise-polyfill will be unaffected by
    // other code modifying setTimeout (like sinon.useFakeTimers())
    var setTimeoutFunc = setTimeout;

    function noop() {}
    
    // Polyfill for Function.prototype.bind
    function bind(fn, thisArg) {
      return function () {
        fn.apply(thisArg, arguments);
      };
    }

    function Promise(fn) {
      if (!(this instanceof Promise)) throw new TypeError('Promises must be constructed via new');
      if (typeof fn !== 'function') throw new TypeError('not a function');
      this._state = 0;
      this._handled = false;
      this._value = undefined;
      this._deferreds = [];

      doResolve(fn, this);
    }

    function handle(self, deferred) {
      while (self._state === 3) {
        self = self._value;
      }
      if (self._state === 0) {
        self._deferreds.push(deferred);
        return;
      }
      self._handled = true;
      Promise._immediateFn(function () {
        var cb = self._state === 1 ? deferred.onFulfilled : deferred.onRejected;
        if (cb === null) {
          (self._state === 1 ? resolve : reject)(deferred.promise, self._value);
          return;
        }
        var ret;
        try {
          ret = cb(self._value);
        } catch (e) {
          reject(deferred.promise, e);
          return;
        }
        resolve(deferred.promise, ret);
      });
    }

    function resolve(self, newValue) {
      try {
        // Promise Resolution Procedure: https://github.com/promises-aplus/promises-spec#the-promise-resolution-procedure
        if (newValue === self) throw new TypeError('A promise cannot be resolved with itself.');
        if (newValue && (typeof newValue === 'object' || typeof newValue === 'function')) {
          var then = newValue.then;
          if (newValue instanceof Promise) {
            self._state = 3;
            self._value = newValue;
            finale(self);
            return;
          } else if (typeof then === 'function') {
            doResolve(bind(then, newValue), self);
            return;
          }
        }
        self._state = 1;
        self._value = newValue;
        finale(self);
      } catch (e) {
        reject(self, e);
      }
    }

    function reject(self, newValue) {
      self._state = 2;
      self._value = newValue;
      finale(self);
    }

    function finale(self) {
      if (self._state === 2 && self._deferreds.length === 0) {
        Promise._immediateFn(function() {
          if (!self._handled) {
            Promise._unhandledRejectionFn(self._value);
          }
        });
      }

      for (var i = 0, len = self._deferreds.length; i < len; i++) {
        handle(self, self._deferreds[i]);
      }
      self._deferreds = null;
    }

    function Handler(onFulfilled, onRejected, promise) {
      this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
      this.onRejected = typeof onRejected === 'function' ? onRejected : null;
      this.promise = promise;
    }

    /**
     * Take a potentially misbehaving resolver function and make sure
     * onFulfilled and onRejected are only called once.
     *
     * Makes no guarantees about asynchrony.
     */
    function doResolve(fn, self) {
      var done = false;
      try {
        fn(function (value) {
          if (done) return;
          done = true;
          resolve(self, value);
        }, function (reason) {
          if (done) return;
          done = true;
          reject(self, reason);
        });
      } catch (ex) {
        if (done) return;
        done = true;
        reject(self, ex);
      }
    }

    Promise.prototype['catch'] = function (onRejected) {
      return this.then(null, onRejected);
    };

    Promise.prototype.then = function (onFulfilled, onRejected) {
      var prom = new (this.constructor)(noop);

      handle(this, new Handler(onFulfilled, onRejected, prom));
      return prom;
    };

    Promise.all = function (arr) {
      return new Promise(function (resolve, reject) {
        if (!arr || typeof arr.length === 'undefined') throw new TypeError('Promise.all accepts an array');
        var args = Array.prototype.slice.call(arr);
        if (args.length === 0) return resolve([]);
        var remaining = args.length;

        function res(i, val) {
          try {
            if (val && (typeof val === 'object' || typeof val === 'function')) {
              var then = val.then;
              if (typeof then === 'function') {
                then.call(val, function (val) {
                  res(i, val);
                }, reject);
                return;
              }
            }
            args[i] = val;
            if (--remaining === 0) {
              resolve(args);
            }
          } catch (ex) {
            reject(ex);
          }
        }

        for (var i = 0; i < args.length; i++) {
          res(i, args[i]);
        }
      });
    };

    Promise.resolve = function (value) {
      if (value && typeof value === 'object' && value.constructor === Promise) {
        return value;
      }

      return new Promise(function (resolve) {
        resolve(value);
      });
    };

    Promise.reject = function (value) {
      return new Promise(function (resolve, reject) {
        reject(value);
      });
    };

    Promise.race = function (values) {
      return new Promise(function (resolve, reject) {
        for (var i = 0, len = values.length; i < len; i++) {
          values[i].then(resolve, reject);
        }
      });
    };

    // Use polyfill for setImmediate for performance gains
    Promise._immediateFn = (typeof setImmediate === 'function' && function (fn) { setImmediate(fn); }) ||
      function (fn) {
        setTimeoutFunc(fn, 0);
      };

    Promise._unhandledRejectionFn = function _unhandledRejectionFn(err) {
      if (typeof console !== 'undefined' && console) {
        console.warn('Possible Unhandled Promise Rejection:', err); // eslint-disable-line no-console
      }
    };

    /**
     * Set the immediate function to execute callbacks
     * @param fn {function} Function to execute
     * @deprecated
     */
    Promise._setImmediateFn = function _setImmediateFn(fn) {
      Promise._immediateFn = fn;
    };

    /**
     * Change the function to execute on unhandled rejection
     * @param {function} fn Function to execute on unhandled rejection
     * @deprecated
     */
    Promise._setUnhandledRejectionFn = function _setUnhandledRejectionFn(fn) {
      Promise._unhandledRejectionFn = fn;
    };
    
    if ( module.exports) {
      module.exports = Promise;
    } else if (!root.Promise) {
      root.Promise = Promise;
    }

  })(commonjsGlobal);
  });

  function evalScript (el) {
    var querySelector = this.options.mainScriptElement;
    var code = el.text || el.textContent || el.innerHTML || "";
    this.log.log("Evaluating Script: ", el);

    if (code.match("document.write")) {
      if (console && this.options.logObject.log) {
        this.options.logObject.log("Script contains document.write. Can’t be executed correctly. Code skipped ", el);
      }

      return false;
    }

    var src = el.src || "";
    var parent = el.parentNode || document.querySelector(querySelector) || document.documentElement;
    var script = document.createElement("script");
    var promise = new Promise(function (resolve) {
      script.type = "text/javascript";

      if (src != "") {
        script.src = src;
        script.addEventListener('load', function () {
          resolve(src);
        });
        script.async = true; // force asynchronous loading of peripheral js
      }

      if (code != "") {
        try {
          script.appendChild(document.createTextNode(code));
        } catch (e) {
          // old IEs have funky script nodes
          script.text = code;
        }

        resolve('text-node');
      }
    });
    this.log.log('ParentElement => ', parent); // execute

    parent.appendChild(script);
    parent.removeChild(script); // avoid pollution only in head or body tags
    // of if the setting removeScriptsAfterParsing is active

    if (["head", "body"].indexOf(parent.tagName.toLowerCase()) > 0 || this.options.removeScriptsAfterParsing === true) ;

    return promise;
  }

  // Needed since innerHTML does not run scripts

  function getExecuteScripts () {
    var _this = this;

    return function (el) {
      _this.log.log("Executing scripts for ", el);

      var loadingScripts = [];
      if (el === undefined) return Promise.resolve();

      if (el.tagName.toLowerCase() === "script") {
        evalScript.call(_this, el);
      }

      forEachEls(el.querySelectorAll("script"), function (script) {
        if (!script.type || script.type.toLowerCase() === "text/javascript") {
          if (!(script.parentNode && script.parentNode.tagName == 'textarea')) {
            loadingScripts.push(evalScript.call(_this, script));
          }
        }
      }, _this);
      return loadingScripts;
    };
  }

  function off (els, events, listener, useCapture) {
    events = typeof events === "string" ? events.split(" ") : events;
    events.forEach(function (e) {
      forEachEls(els, function (el) {
        el.removeEventListener(e, listener, useCapture);
      });
    });
  }

  var attrClick$2 = "data-pjax-click-state";
  var attrKey$1 = "data-pjax-keyup-state";

  var linkAction$1 = function linkAction(el, event) {
    // Don’t break browser special behavior on links (like page in new window)
    if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      el.setAttribute(attrClick$2, "modifier");
      return;
    } // we do test on href now to prevent unexpected behavior if for some reason
    // user have href that can be dynamically updated
    // Ignore external links.


    if (el.protocol !== window.location.protocol || el.host !== window.location.host) {
      el.setAttribute(attrClick$2, "external");
      return;
    } // Ignore click if we are on an anchor on the same page


    if (el.pathname === window.location.pathname && el.hash.length > 0) {
      el.setAttribute(attrClick$2, "anchor-present");
      return;
    } // Ignore anchors on the same page (keep native behavior)


    if (el.hash && el.href.replace(el.hash, "") === window.location.href.replace(location.hash, "")) {
      el.setAttribute(attrClick$2, "anchor");
      return;
    } // Ignore empty anchor "foo.html#"


    if (el.href === window.location.href.split("#")[0] + "#") {
      el.setAttribute(attrClick$2, "anchor-empty");
      return;
    }

    event.preventDefault(); // don’t do "nothing" if user try to reload the page by clicking the same link twice

    if (this.options.currentUrlFullReload && el.href === window.location.href.split("#")[0]) {
      el.setAttribute(attrClick$2, "reload");
      this.reload();
      return;
    }

    this.options.requestOptions = this.options.requestOptions || {};
    el.setAttribute(attrClick$2, "load");
    this.loadUrl(el.href, clone(this.options));
  };

  var isDefaultPrevented$2 = function isDefaultPrevented(event) {
    return event.defaultPrevented || event.returnValue === false;
  };

  function getUnattachLink () {
    var _this = this;

    return function (el) {
      off(el, "click", function (event) {
        if (isDefaultPrevented$2(event)) {
          return;
        }

        linkAction$1.call(_this, el, event);
      });
      off(el, "keyup", function (event) {
        if (isDefaultPrevented$2(event)) {
          return;
        } // Don’t break browser special behavior on links (like page in new window)


        if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          el.setAttribute(attrKey$1, "modifier");
          return;
        }

        if (event.keyCode == 13) {
          linkAction$1.call(_this, el, event);
        }
      });
    };
  }

  var attrClick$3 = "data-pjax-click-state";

  var formAction$1 = function formAction(el, event) {
    this.options.requestOptions = {
      requestUrl: el.getAttribute('action') || window.location.href,
      requestMethod: el.getAttribute('method') || 'GET'
    }; //create a testable virtual link of the form action

    var virtLinkElement = document.createElement('a');
    virtLinkElement.setAttribute('href', this.options.requestOptions.requestUrl); // Ignore external links.

    if (virtLinkElement.protocol !== window.location.protocol || virtLinkElement.host !== window.location.host) {
      el.setAttribute(attrClick$3, "external");
      return;
    } // Ignore click if we are on an anchor on the same page


    if (virtLinkElement.pathname === window.location.pathname && virtLinkElement.hash.length > 0) {
      el.setAttribute(attrClick$3, "anchor-present");
      return;
    } // Ignore empty anchor "foo.html#"


    if (virtLinkElement.href === window.location.href.split("#")[0] + "#") {
      el.setAttribute(attrClick$3, "anchor-empty");
      return;
    } // if declared as a full reload, just normally submit the form


    if (this.options.currentUrlFullReload) {
      el.setAttribute(attrClick$3, "reload");
      return;
    }

    event.preventDefault();
    var nameList = [];
    var paramObject = [];

    for (var elementKey in el.elements) {
      var element = el.elements[elementKey];

      if (!!element.name && element.attributes !== undefined && element.tagName.toLowerCase() !== 'button') {
        if (element.type !== 'checkbox' && element.type !== 'radio' || element.checked) {
          if (nameList.indexOf(element.name) === -1) {
            nameList.push(element.name);
            paramObject.push({
              name: encodeURIComponent(element.name),
              value: encodeURIComponent(element.value)
            });
          }
        }
      }
    } //Creating a getString


    var paramsString = paramObject.map(function (value) {
      return value.name + "=" + value.value;
    }).join('&');
    this.options.requestOptions.requestPayload = paramObject;
    this.options.requestOptions.requestPayloadString = paramsString;
    el.setAttribute(attrClick$3, "submit");
    this.loadUrl(virtLinkElement.href, clone(this.options));
  };

  var isDefaultPrevented$3 = function isDefaultPrevented(event) {
    return event.defaultPrevented || event.returnValue === false;
  };

  function getUnattachForm () {
    var _this = this;

    return function (el) {
      off(el, "submit", function (event) {
        if (isDefaultPrevented$3(event)) {
          return;
        }

        formAction$1.call(this, el, event);
      });
      off(el, "keyup", function (event) {
        if (isDefaultPrevented$3(event)) {
          return;
        }

        if (event.keyCode == 13) {
          formAction$1.call(_this, el, event);
        }
      });
    };
  }

  function getUpdateStylesheets () {
    var _this = this;

    return function (elements, oldElements) {
      _this.log.log("styleheets old elements", oldElements);

      _this.log.log("styleheets new elements", elements);

      forEachEls(elements, function (newEl) {
        var resemblingOld = Array.from(oldElements).reduce(function (acc, oldEl) {
          acc = oldEl.href === newEl.href ? oldEl : acc;
          return acc;
        }, null);

        if (resemblingOld !== null) {
          if (_this.log) {
            _this.log.log("old stylesheet found not resetting");
          }
        } else {
          if (_this.log) {
            _this.log.log("new stylesheet => add to head");
          }

          var head = document.getElementsByTagName('head')[0];
          var link = document.createElement('link');
          link.setAttribute('href', newEl.href);
          link.setAttribute('rel', 'stylesheet');
          link.setAttribute('type', 'text/css');
          head.appendChild(link);
        }
      });
    };
  }

  /**
   * Collection of parsing methods
   *  Exports:
   *  -> parseDOMUnload
   *  -> parseDOM
   *  -> parseElementUnload
   *  -> parseElement
   *  -> parseOptions
   */
  function getParsers () {
    return {
      parseElementUnload: function parseElementUnload(el) {
        switch (el.tagName.toLowerCase()) {
          case "a":
            // only attach link if el does not already have link attached
            if (!el.hasAttribute('data-pjax-click-state')) {
              this.unattachLink(el);
            }

            break;

          case "form":
            // only attach link if el does not already have link attached
            if (!el.hasAttribute('data-pjax-click-state')) {
              this.unattachForm(el);
            }

            break;

          default:
            throw "Pjax can only be applied on <a> or <form> submit";
        }
      },
      parseElement: function parseElement(el) {
        switch (el.tagName.toLowerCase()) {
          case "a":
            // only attach link if el does not already have link attached
            if (!el.hasAttribute('data-pjax-click-state')) {
              this.attachLink(el);
            }

            break;

          case "form":
            // only attach link if el does not already have link attached
            if (!el.hasAttribute('data-pjax-click-state')) {
              this.attachForm(el);
            }

            break;

          default:
            throw "Pjax can only be applied on <a> or <form> submit";
        }
      },
      parseDOMUnload: function parseDOMUnload(el) {
        forEachEls(this.getElements(el), this.parseElementUnload, this);
      },
      parseDOM: function parseDOM(el) {
        forEachEls(this.getElements(el), this.parseElement, this);
      },
      parseOptions: function parseOptions(options) {
        this.options = options;
        this.options.elements = this.options.elements || "a[href], form[action]";
        this.options.reRenderCSS = this.options.reRenderCSS || false;
        this.options.forceRedirectOnFail = this.options.forceRedirectOnFail || false;
        this.options.scriptloadtimeout = this.options.scriptloadtimeout || 1000;
        this.options.mainScriptElement = this.options.mainScriptElement || "head";
        this.options.removeScriptsAfterParsing = this.options.removeScriptsAfterParsing || true;
        this.options.logObject = this.options.logObject || console;
        this.options.latestChance = this.options.latestChance || null;
        this.options.selectors = this.options.selectors || ["title", ".js-Pjax"];
        this.options.switches = this.options.switches || {};
        this.options.switchesOptions = this.options.switchesOptions || {};
        this.options.history = this.options.history || true;

        this.options.onDomDiffers = this.options.onDomDiffers || function () {
          return true;
        };

        this.options.pjaxErrorHandler = this.options.pjaxErrorHandler || function () {
          return false;
        };

        this.options.onJsonDocument = this.options.onJsonDocument || function () {
          return true;
        };

        this.options.analytics = this.options.analytics || function () {
          // options.backward or options.foward can be true or undefined
          // by default, we do track back/foward hit
          // https://productforums.google.com/forum/#!topic/analytics/WVwMDjLhXYk
          if (window._gaq) {
            window._gaq.push(["_trackPageview"]);
          }

          if (window.ga) {
            window.ga("send", "pageview", {
              page: location.pathname,
              title: document.title
            });
          }
        };

        this.options.scrollTo = typeof this.options.scrollTo === 'undefined' ? 0 : this.options.scrollTo;
        this.options.cacheBust = typeof this.options.cacheBust === 'undefined' ? true : this.options.cacheBust;
        this.options.debug = this.options.debug || false; // we can’t replace body.outerHTML or head.outerHTML
        // it create a bug where new body or new head are created in the dom
        // if you set head.outerHTML, a new body tag is appended, so the dom get 2 body
        // & it break the switchFallback which replace head & body

        if (!this.options.switches.head) {
          this.options.switches.head = this.switchElementsAlt;
        }

        if (!this.options.switches.body) {
          this.options.switches.body = this.switchElementsAlt;
        }

        if (typeof options.analytics !== "function") {
          options.analytics = function () {};
        }
      }
    };
  }

  /**
   * Exports
   *  -> refresh
   *  -> reload
   *  -> foreachSelectors
   *  -> unattach
   */
  function getDomUtils () {
    var _this = this;

    return {
      refresh: function refresh(el) {
        _this.parseDOM(el || document);
      },
      reload: function reload() {
        window.location.reload();
      },
      foreachSelectors: function foreachSelectors(selectors, cb, context, DOMcontext) {
        DOMcontext = DOMcontext || document;
        selectors.forEach(function (selector) {
          forEachEls(DOMcontext.querySelectorAll(selector), cb, context);
        });
      },
      unattach: function unattach(el) {
        forEachEls(_this.getElements(el), function (el) {
          off(el, 'click');
          off(el, 'keyup');
        }, _this);
      }
    };
  }

  var PjaxFactory = function PjaxFactory() {
    var Pjax =
    /*#__PURE__*/
    function () {
      function Pjax(options) {
        var _this = this;

        _classCallCheck(this, Pjax);

        this.firstrun = true;
        this.oUtilities = getUtility();
        this.oDomUtils = getDomUtils.call(this);
        this.oParsers = getParsers.call(this);
        this.oParsers.parseOptions.call(this, options);
        this.log = log.call(this);
        this.doRequest = doRequest;
        this.getElements = this.oUtilities.getElements;
        this.parseElementUnload = this.oParsers.parseElementUnload;
        this.parseElement = this.oParsers.parseElement;
        this.parseDOM = this.oParsers.parseDOM;
        this.parseDOMUnload = this.oParsers.parseDOMUnload;
        this.refresh = this.oDomUtils.refresh;
        this.reload = this.oDomUtils.reload;
        this.isSupported = this.oUtilities.isSupported;
        this.attachLink = getAttachLink.call(this);
        this.attachForm = getAttachForm.call(this);
        this.unattachLink = getUnattachLink.call(this);
        this.unattachForm = getUnattachForm.call(this);
        this.updateStylesheets = getUpdateStylesheets.call(this);
        this.log.log("Pjax options", this.options);
        this.maxUid = this.lastUid = this.oUtilities.newUid();
        this.parseDOM(document);
        on(window, "popstate", function (st) {
          _this.log.log("OPT -> ", st);

          if (st.state) {
            var opt = _this.oUtilities.clone(_this.options);

            opt.url = st.state.url;
            opt.title = st.state.title;
            opt.history = false;
            opt.requestOptions = {};

            _this.log.log("OPT -> ", opt);

            _this.log.log("State UID", st.state.uid);

            _this.log.log("lastUID", _this.lastUid);

            if (st.state.uid < _this.lastUid) {
              opt.backward = true;
            } else {
              opt.forward = true;
            }

            _this.lastUid = st.state.uid; // @todo implement history cache here, based on uid

            _this.loadUrl(st.state.url, opt);
          }
        });
        return this;
      }

      _createClass(Pjax, [{
        key: "forEachSelectors",
        value: function forEachSelectors(cb, context, DOMcontext) {
          return this.oDomUtils.foreachSelectors(this.options.selectors, cb, context, DOMcontext);
        }
      }, {
        key: "switchSelectors",
        value: function switchSelectors(selectors, fromEl, toEl, options) {
          var fnSwitchSelectors = getSwitchSelectors.call(this);
          return fnSwitchSelectors(this.options.switches, this.options.switchesOptions, selectors, fromEl, toEl, options);
        }
      }, {
        key: "latestChance",
        value: function latestChance(href) {
          window.location.href = href;
          return false;
        }
      }, {
        key: "onSwitch",
        value: function onSwitch() {
          trigger(window, "resize scroll");
        }
      }, {
        key: "loadContent",
        value: function loadContent(html, options) {
          var _this2 = this;

          var fnExecuteScripts = getExecuteScripts.apply(this);
          var tmpEl = window.document.implementation.createHTMLDocument("pjax"); //Collector array to store the promises in

          var collectForScriptcomplete = [Promise.resolve("basic resolve")]; //parse HTML attributes to copy them
          //since we are forced to use documentElement.innerHTML (outerHTML can't be used for <html>)

          var htmlRegex = /<html[^>]+>/gi;
          var htmlAttribsRegex = /\s?[a-z:]+(?:=(?:'|")[^'">]+(?:'|"))*/gi;
          var matches = html.match(htmlRegex);

          if (matches && matches.length) {
            matches = matches[0].match(htmlAttribsRegex);

            if (matches.length) {
              matches.shift();
              matches.forEach(function (htmlAttrib) {
                var attr = htmlAttrib.trim().split("=");

                if (attr.length === 1) {
                  tmpEl.documentElement.setAttribute(attr[0], true);
                } else {
                  tmpEl.documentElement.setAttribute(attr[0], attr[1].slice(1, -1));
                }
              });
            }
          }

          var jsonContent = null;

          try {
            jsonContent = JSON.parse(html);
          } catch (e) {
            this.log.warn('No JSON found. If you expected it there was an error');
          }

          tmpEl.documentElement.innerHTML = html;
          this.log.log("load content", tmpEl.documentElement.attributes, tmpEl.documentElement.innerHTML.length);

          if (jsonContent !== null) {
            this.log.log("found JSON document", jsonContent);
            this.options.onJsonDocument.call(this, jsonContent);
          } // Clear out any focused controls before inserting new page contents.
          // we clear focus on non form elements


          if (window.document.activeElement && !window.document.activeElement.value) {
            try {
              window.document.activeElement.blur();
            } catch (e) {// Nothing to do, just ignore any issues
            }
          }

          this.switchSelectors(this.options.selectors, tmpEl, document, options); //reset stylesheets if activated

          if (this.options.reRenderCSS === true) {
            this.updateStylesheets(tmpEl.querySelectorAll('link[rel=stylesheet]'), document.querySelectorAll('link[rel=stylesheet]'));
          } // FF bug: Won’t autofocus fields that are inserted via JS.
          // This behavior is incorrect. So if theres no current focus, autofocus
          // the last field.
          //
          // http://www.w3.org/html/wg/drafts/html/master/forms.html


          var autofocusEl = Array.prototype.slice.call(document.querySelectorAll("[autofocus]")).pop();

          if (autofocusEl && document.activeElement !== autofocusEl) {
            autofocusEl.focus();
          } // execute scripts when DOM have been completely updated


          this.options.selectors.forEach(function (selector) {
            _this2.oUtilities.forEachEls(document.querySelectorAll(selector), function (el) {
              collectForScriptcomplete.push.apply(collectForScriptcomplete, fnExecuteScripts(el));
            }, _this2);
          }); // }
          // catch(e) {
          //   if (this.options.debug) {
          //     this.log.log("Pjax switch fail: ", e)
          //   }
          //   this.switchFallback(tmpEl, document)
          // }

          this.log.log("waiting for scriptcomplete", collectForScriptcomplete); //Fallback! If something can't be loaded or is not loaded correctly -> just force eventing in error

          var timeOutScriptEvent = null;
          timeOutScriptEvent = window.setTimeout(function () {
            trigger(document, "pjax:scriptcomplete pjax:scripttimeout", options);
            timeOutScriptEvent = null;
          }, this.options.scriptloadtimeout);
          Promise.all(collectForScriptcomplete).then( //resolved
          function () {
            if (timeOutScriptEvent !== null) {
              window.clearTimeout(timeOutScriptEvent);
              trigger(document, "pjax:scriptcomplete pjax:scriptsuccess", options);
            }
          }, function () {
            if (timeOutScriptEvent !== null) {
              window.clearTimeout(timeOutScriptEvent);
              trigger(document, "pjax:scriptcomplete pjax:scripterror", options);
            }
          });
        }
      }, {
        key: "loadUrl",
        value: function loadUrl(href, options) {
          var _this3 = this;

          this.log.log("load href", href, options);
          trigger(document, "pjax:send", options); // Do the request

          this.doRequest(href, options.requestOptions, function (html, requestData) {
            // Fail if unable to load HTML via AJAX
            if (html === false || requestData.status !== 200) {
              trigger(document, "pjax:complete pjax:error", {
                options: options,
                requestData: requestData,
                href: href
              });
              return options.pjaxErrorHandler(href, options, requestData);
            } // Clear out any focused controls before inserting new page contents.


            document.activeElement.blur();

            try {
              _this3.loadContent(html, options);
            } catch (e) {
              if (!_this3.options.debug) {
                if (console && _this3.options.logObject.error) {
                  _this3.options.logObject.error("Pjax switch fail: ", e);
                }

                return options.pjaxErrorHandler(href, options, requestData) || _this3.latestChance(href);
              } else {
                if (_this3.options.forceRedirectOnFail) {
                  return options.pjaxErrorHandler(href, options, requestData) || _this3.latestChance(href);
                }

                throw e;
              }
            }

            if (options.history) {
              if (_this3.firstrun) {
                _this3.lastUid = _this3.maxUid = _this3.oUtilities.newUid();
                _this3.firstrun = false;
                window.history.replaceState({
                  url: window.location.href,
                  title: document.title,
                  uid: _this3.maxUid
                }, document.title);
              } // Update browser history


              _this3.lastUid = _this3.maxUid = _this3.oUtilities.newUid();
              window.history.pushState({
                url: href,
                title: options.title,
                uid: _this3.maxUid
              }, options.title, href);
            }

            _this3.forEachSelectors(function (el) {
              return _this3.parseDOM(el);
            }); // Fire Events


            trigger(document, "pjax:complete pjax:success", options);
            options.analytics(); // Scroll page to top on new page load

            if (options.scrollTo !== false) {
              if (options.scrollTo.length > 1) {
                window.scrollTo(options.scrollTo[0], options.scrollTo[1]);
              } else {
                window.scrollTo(0, options.scrollTo);
              }
            }
          });
        }
      }]);

      return Pjax;
    }(); // if there isn’t required browser functions, returning stupid api


    if (!isSupported()) {
      console.warn('Pjax not supported');

      var stupidPjax = function stupidPjax() {};

      for (var key in Pjax.prototype) {
        if (key in Pjax.prototype && typeof Pjax.prototype[key] === "function") {
          stupidPjax[key] = stupidPjax;
        }
      }

      return stupidPjax;
    }

    return Pjax;
  };

  var index = new PjaxFactory();

  return index;

}));
//# sourceMappingURL=pjax.js.map
