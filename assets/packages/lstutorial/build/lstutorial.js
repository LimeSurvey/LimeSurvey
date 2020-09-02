(function (factory) {
  typeof define === 'function' && define.amd ? define(factory) :
  factory();
}(function () { 'use strict';

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

  function _readOnlyError(name) {
    throw new Error("\"" + name + "\" is read-only");
  }

  /**!
   * @fileOverview Kickass library to create and place poppers near their reference elements.
   * @version 1.15.0
   * @license
   * Copyright (c) 2016 Federico Zivolo and contributors
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in all
   * copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
   * SOFTWARE.
   */
  var isBrowser = typeof window !== 'undefined' && typeof document !== 'undefined';

  var longerTimeoutBrowsers = ['Edge', 'Trident', 'Firefox'];
  var timeoutDuration = 0;
  for (var i = 0; i < longerTimeoutBrowsers.length; i += 1) {
    if (isBrowser && navigator.userAgent.indexOf(longerTimeoutBrowsers[i]) >= 0) {
      timeoutDuration = 1;
      break;
    }
  }

  function microtaskDebounce(fn) {
    var called = false;
    return function () {
      if (called) {
        return;
      }
      called = true;
      window.Promise.resolve().then(function () {
        called = false;
        fn();
      });
    };
  }

  function taskDebounce(fn) {
    var scheduled = false;
    return function () {
      if (!scheduled) {
        scheduled = true;
        setTimeout(function () {
          scheduled = false;
          fn();
        }, timeoutDuration);
      }
    };
  }

  var supportsMicroTasks = isBrowser && window.Promise;

  /**
  * Create a debounced version of a method, that's asynchronously deferred
  * but called in the minimum time possible.
  *
  * @method
  * @memberof Popper.Utils
  * @argument {Function} fn
  * @returns {Function}
  */
  var debounce = supportsMicroTasks ? microtaskDebounce : taskDebounce;

  /**
   * Check if the given variable is a function
   * @method
   * @memberof Popper.Utils
   * @argument {Any} functionToCheck - variable to check
   * @returns {Boolean} answer to: is a function?
   */
  function isFunction(functionToCheck) {
    var getType = {};
    return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
  }

  /**
   * Get CSS computed property of the given element
   * @method
   * @memberof Popper.Utils
   * @argument {Eement} element
   * @argument {String} property
   */
  function getStyleComputedProperty(element, property) {
    if (element.nodeType !== 1) {
      return [];
    }
    // NOTE: 1 DOM access here
    var window = element.ownerDocument.defaultView;
    var css = window.getComputedStyle(element, null);
    return property ? css[property] : css;
  }

  /**
   * Returns the parentNode or the host of the element
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @returns {Element} parent
   */
  function getParentNode(element) {
    if (element.nodeName === 'HTML') {
      return element;
    }
    return element.parentNode || element.host;
  }

  /**
   * Returns the scrolling parent of the given element
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @returns {Element} scroll parent
   */
  function getScrollParent(element) {
    // Return body, `getScroll` will take care to get the correct `scrollTop` from it
    if (!element) {
      return document.body;
    }

    switch (element.nodeName) {
      case 'HTML':
      case 'BODY':
        return element.ownerDocument.body;
      case '#document':
        return element.body;
    }

    // Firefox want us to check `-x` and `-y` variations as well

    var _getStyleComputedProp = getStyleComputedProperty(element),
        overflow = _getStyleComputedProp.overflow,
        overflowX = _getStyleComputedProp.overflowX,
        overflowY = _getStyleComputedProp.overflowY;

    if (/(auto|scroll|overlay)/.test(overflow + overflowY + overflowX)) {
      return element;
    }

    return getScrollParent(getParentNode(element));
  }

  var isIE11 = isBrowser && !!(window.MSInputMethodContext && document.documentMode);
  var isIE10 = isBrowser && /MSIE 10/.test(navigator.userAgent);

  /**
   * Determines if the browser is Internet Explorer
   * @method
   * @memberof Popper.Utils
   * @param {Number} version to check
   * @returns {Boolean} isIE
   */
  function isIE(version) {
    if (version === 11) {
      return isIE11;
    }
    if (version === 10) {
      return isIE10;
    }
    return isIE11 || isIE10;
  }

  /**
   * Returns the offset parent of the given element
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @returns {Element} offset parent
   */
  function getOffsetParent(element) {
    if (!element) {
      return document.documentElement;
    }

    var noOffsetParent = isIE(10) ? document.body : null;

    // NOTE: 1 DOM access here
    var offsetParent = element.offsetParent || null;
    // Skip hidden elements which don't have an offsetParent
    while (offsetParent === noOffsetParent && element.nextElementSibling) {
      offsetParent = (element = element.nextElementSibling).offsetParent;
    }

    var nodeName = offsetParent && offsetParent.nodeName;

    if (!nodeName || nodeName === 'BODY' || nodeName === 'HTML') {
      return element ? element.ownerDocument.documentElement : document.documentElement;
    }

    // .offsetParent will return the closest TH, TD or TABLE in case
    // no offsetParent is present, I hate this job...
    if (['TH', 'TD', 'TABLE'].indexOf(offsetParent.nodeName) !== -1 && getStyleComputedProperty(offsetParent, 'position') === 'static') {
      return getOffsetParent(offsetParent);
    }

    return offsetParent;
  }

  function isOffsetContainer(element) {
    var nodeName = element.nodeName;

    if (nodeName === 'BODY') {
      return false;
    }
    return nodeName === 'HTML' || getOffsetParent(element.firstElementChild) === element;
  }

  /**
   * Finds the root node (document, shadowDOM root) of the given element
   * @method
   * @memberof Popper.Utils
   * @argument {Element} node
   * @returns {Element} root node
   */
  function getRoot(node) {
    if (node.parentNode !== null) {
      return getRoot(node.parentNode);
    }

    return node;
  }

  /**
   * Finds the offset parent common to the two provided nodes
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element1
   * @argument {Element} element2
   * @returns {Element} common offset parent
   */
  function findCommonOffsetParent(element1, element2) {
    // This check is needed to avoid errors in case one of the elements isn't defined for any reason
    if (!element1 || !element1.nodeType || !element2 || !element2.nodeType) {
      return document.documentElement;
    }

    // Here we make sure to give as "start" the element that comes first in the DOM
    var order = element1.compareDocumentPosition(element2) & Node.DOCUMENT_POSITION_FOLLOWING;
    var start = order ? element1 : element2;
    var end = order ? element2 : element1;

    // Get common ancestor container
    var range = document.createRange();
    range.setStart(start, 0);
    range.setEnd(end, 0);
    var commonAncestorContainer = range.commonAncestorContainer;

    // Both nodes are inside #document

    if (element1 !== commonAncestorContainer && element2 !== commonAncestorContainer || start.contains(end)) {
      if (isOffsetContainer(commonAncestorContainer)) {
        return commonAncestorContainer;
      }

      return getOffsetParent(commonAncestorContainer);
    }

    // one of the nodes is inside shadowDOM, find which one
    var element1root = getRoot(element1);
    if (element1root.host) {
      return findCommonOffsetParent(element1root.host, element2);
    } else {
      return findCommonOffsetParent(element1, getRoot(element2).host);
    }
  }

  /**
   * Gets the scroll value of the given element in the given side (top and left)
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @argument {String} side `top` or `left`
   * @returns {number} amount of scrolled pixels
   */
  function getScroll(element) {
    var side = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'top';

    var upperSide = side === 'top' ? 'scrollTop' : 'scrollLeft';
    var nodeName = element.nodeName;

    if (nodeName === 'BODY' || nodeName === 'HTML') {
      var html = element.ownerDocument.documentElement;
      var scrollingElement = element.ownerDocument.scrollingElement || html;
      return scrollingElement[upperSide];
    }

    return element[upperSide];
  }

  /*
   * Sum or subtract the element scroll values (left and top) from a given rect object
   * @method
   * @memberof Popper.Utils
   * @param {Object} rect - Rect object you want to change
   * @param {HTMLElement} element - The element from the function reads the scroll values
   * @param {Boolean} subtract - set to true if you want to subtract the scroll values
   * @return {Object} rect - The modifier rect object
   */
  function includeScroll(rect, element) {
    var subtract = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

    var scrollTop = getScroll(element, 'top');
    var scrollLeft = getScroll(element, 'left');
    var modifier = subtract ? -1 : 1;
    rect.top += scrollTop * modifier;
    rect.bottom += scrollTop * modifier;
    rect.left += scrollLeft * modifier;
    rect.right += scrollLeft * modifier;
    return rect;
  }

  /*
   * Helper to detect borders of a given element
   * @method
   * @memberof Popper.Utils
   * @param {CSSStyleDeclaration} styles
   * Result of `getStyleComputedProperty` on the given element
   * @param {String} axis - `x` or `y`
   * @return {number} borders - The borders size of the given axis
   */

  function getBordersSize(styles, axis) {
    var sideA = axis === 'x' ? 'Left' : 'Top';
    var sideB = sideA === 'Left' ? 'Right' : 'Bottom';

    return parseFloat(styles['border' + sideA + 'Width'], 10) + parseFloat(styles['border' + sideB + 'Width'], 10);
  }

  function getSize(axis, body, html, computedStyle) {
    return Math.max(body['offset' + axis], body['scroll' + axis], html['client' + axis], html['offset' + axis], html['scroll' + axis], isIE(10) ? parseInt(html['offset' + axis]) + parseInt(computedStyle['margin' + (axis === 'Height' ? 'Top' : 'Left')]) + parseInt(computedStyle['margin' + (axis === 'Height' ? 'Bottom' : 'Right')]) : 0);
  }

  function getWindowSizes(document) {
    var body = document.body;
    var html = document.documentElement;
    var computedStyle = isIE(10) && getComputedStyle(html);

    return {
      height: getSize('Height', body, html, computedStyle),
      width: getSize('Width', body, html, computedStyle)
    };
  }

  var classCallCheck = function (instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  };

  var createClass = function () {
    function defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
        var descriptor = props[i];
        descriptor.enumerable = descriptor.enumerable || false;
        descriptor.configurable = true;
        if ("value" in descriptor) descriptor.writable = true;
        Object.defineProperty(target, descriptor.key, descriptor);
      }
    }

    return function (Constructor, protoProps, staticProps) {
      if (protoProps) defineProperties(Constructor.prototype, protoProps);
      if (staticProps) defineProperties(Constructor, staticProps);
      return Constructor;
    };
  }();





  var defineProperty = function (obj, key, value) {
    if (key in obj) {
      Object.defineProperty(obj, key, {
        value: value,
        enumerable: true,
        configurable: true,
        writable: true
      });
    } else {
      obj[key] = value;
    }

    return obj;
  };

  var _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  /**
   * Given element offsets, generate an output similar to getBoundingClientRect
   * @method
   * @memberof Popper.Utils
   * @argument {Object} offsets
   * @returns {Object} ClientRect like output
   */
  function getClientRect(offsets) {
    return _extends({}, offsets, {
      right: offsets.left + offsets.width,
      bottom: offsets.top + offsets.height
    });
  }

  /**
   * Get bounding client rect of given element
   * @method
   * @memberof Popper.Utils
   * @param {HTMLElement} element
   * @return {Object} client rect
   */
  function getBoundingClientRect(element) {
    var rect = {};

    // IE10 10 FIX: Please, don't ask, the element isn't
    // considered in DOM in some circumstances...
    // This isn't reproducible in IE10 compatibility mode of IE11
    try {
      if (isIE(10)) {
        rect = element.getBoundingClientRect();
        var scrollTop = getScroll(element, 'top');
        var scrollLeft = getScroll(element, 'left');
        rect.top += scrollTop;
        rect.left += scrollLeft;
        rect.bottom += scrollTop;
        rect.right += scrollLeft;
      } else {
        rect = element.getBoundingClientRect();
      }
    } catch (e) {}

    var result = {
      left: rect.left,
      top: rect.top,
      width: rect.right - rect.left,
      height: rect.bottom - rect.top
    };

    // subtract scrollbar size from sizes
    var sizes = element.nodeName === 'HTML' ? getWindowSizes(element.ownerDocument) : {};
    var width = sizes.width || element.clientWidth || result.right - result.left;
    var height = sizes.height || element.clientHeight || result.bottom - result.top;

    var horizScrollbar = element.offsetWidth - width;
    var vertScrollbar = element.offsetHeight - height;

    // if an hypothetical scrollbar is detected, we must be sure it's not a `border`
    // we make this check conditional for performance reasons
    if (horizScrollbar || vertScrollbar) {
      var styles = getStyleComputedProperty(element);
      horizScrollbar -= getBordersSize(styles, 'x');
      vertScrollbar -= getBordersSize(styles, 'y');

      result.width -= horizScrollbar;
      result.height -= vertScrollbar;
    }

    return getClientRect(result);
  }

  function getOffsetRectRelativeToArbitraryNode(children, parent) {
    var fixedPosition = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

    var isIE10 = isIE(10);
    var isHTML = parent.nodeName === 'HTML';
    var childrenRect = getBoundingClientRect(children);
    var parentRect = getBoundingClientRect(parent);
    var scrollParent = getScrollParent(children);

    var styles = getStyleComputedProperty(parent);
    var borderTopWidth = parseFloat(styles.borderTopWidth, 10);
    var borderLeftWidth = parseFloat(styles.borderLeftWidth, 10);

    // In cases where the parent is fixed, we must ignore negative scroll in offset calc
    if (fixedPosition && isHTML) {
      parentRect.top = Math.max(parentRect.top, 0);
      parentRect.left = Math.max(parentRect.left, 0);
    }
    var offsets = getClientRect({
      top: childrenRect.top - parentRect.top - borderTopWidth,
      left: childrenRect.left - parentRect.left - borderLeftWidth,
      width: childrenRect.width,
      height: childrenRect.height
    });
    offsets.marginTop = 0;
    offsets.marginLeft = 0;

    // Subtract margins of documentElement in case it's being used as parent
    // we do this only on HTML because it's the only element that behaves
    // differently when margins are applied to it. The margins are included in
    // the box of the documentElement, in the other cases not.
    if (!isIE10 && isHTML) {
      var marginTop = parseFloat(styles.marginTop, 10);
      var marginLeft = parseFloat(styles.marginLeft, 10);

      offsets.top -= borderTopWidth - marginTop;
      offsets.bottom -= borderTopWidth - marginTop;
      offsets.left -= borderLeftWidth - marginLeft;
      offsets.right -= borderLeftWidth - marginLeft;

      // Attach marginTop and marginLeft because in some circumstances we may need them
      offsets.marginTop = marginTop;
      offsets.marginLeft = marginLeft;
    }

    if (isIE10 && !fixedPosition ? parent.contains(scrollParent) : parent === scrollParent && scrollParent.nodeName !== 'BODY') {
      offsets = includeScroll(offsets, parent);
    }

    return offsets;
  }

  function getViewportOffsetRectRelativeToArtbitraryNode(element) {
    var excludeScroll = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

    var html = element.ownerDocument.documentElement;
    var relativeOffset = getOffsetRectRelativeToArbitraryNode(element, html);
    var width = Math.max(html.clientWidth, window.innerWidth || 0);
    var height = Math.max(html.clientHeight, window.innerHeight || 0);

    var scrollTop = !excludeScroll ? getScroll(html) : 0;
    var scrollLeft = !excludeScroll ? getScroll(html, 'left') : 0;

    var offset = {
      top: scrollTop - relativeOffset.top + relativeOffset.marginTop,
      left: scrollLeft - relativeOffset.left + relativeOffset.marginLeft,
      width: width,
      height: height
    };

    return getClientRect(offset);
  }

  /**
   * Check if the given element is fixed or is inside a fixed parent
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @argument {Element} customContainer
   * @returns {Boolean} answer to "isFixed?"
   */
  function isFixed(element) {
    var nodeName = element.nodeName;
    if (nodeName === 'BODY' || nodeName === 'HTML') {
      return false;
    }
    if (getStyleComputedProperty(element, 'position') === 'fixed') {
      return true;
    }
    var parentNode = getParentNode(element);
    if (!parentNode) {
      return false;
    }
    return isFixed(parentNode);
  }

  /**
   * Finds the first parent of an element that has a transformed property defined
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @returns {Element} first transformed parent or documentElement
   */

  function getFixedPositionOffsetParent(element) {
    // This check is needed to avoid errors in case one of the elements isn't defined for any reason
    if (!element || !element.parentElement || isIE()) {
      return document.documentElement;
    }
    var el = element.parentElement;
    while (el && getStyleComputedProperty(el, 'transform') === 'none') {
      el = el.parentElement;
    }
    return el || document.documentElement;
  }

  /**
   * Computed the boundaries limits and return them
   * @method
   * @memberof Popper.Utils
   * @param {HTMLElement} popper
   * @param {HTMLElement} reference
   * @param {number} padding
   * @param {HTMLElement} boundariesElement - Element used to define the boundaries
   * @param {Boolean} fixedPosition - Is in fixed position mode
   * @returns {Object} Coordinates of the boundaries
   */
  function getBoundaries(popper, reference, padding, boundariesElement) {
    var fixedPosition = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;

    // NOTE: 1 DOM access here

    var boundaries = { top: 0, left: 0 };
    var offsetParent = fixedPosition ? getFixedPositionOffsetParent(popper) : findCommonOffsetParent(popper, reference);

    // Handle viewport case
    if (boundariesElement === 'viewport') {
      boundaries = getViewportOffsetRectRelativeToArtbitraryNode(offsetParent, fixedPosition);
    } else {
      // Handle other cases based on DOM element used as boundaries
      var boundariesNode = void 0;
      if (boundariesElement === 'scrollParent') {
        boundariesNode = getScrollParent(getParentNode(reference));
        if (boundariesNode.nodeName === 'BODY') {
          boundariesNode = popper.ownerDocument.documentElement;
        }
      } else if (boundariesElement === 'window') {
        boundariesNode = popper.ownerDocument.documentElement;
      } else {
        boundariesNode = boundariesElement;
      }

      var offsets = getOffsetRectRelativeToArbitraryNode(boundariesNode, offsetParent, fixedPosition);

      // In case of HTML, we need a different computation
      if (boundariesNode.nodeName === 'HTML' && !isFixed(offsetParent)) {
        var _getWindowSizes = getWindowSizes(popper.ownerDocument),
            height = _getWindowSizes.height,
            width = _getWindowSizes.width;

        boundaries.top += offsets.top - offsets.marginTop;
        boundaries.bottom = height + offsets.top;
        boundaries.left += offsets.left - offsets.marginLeft;
        boundaries.right = width + offsets.left;
      } else {
        // for all the other DOM elements, this one is good
        boundaries = offsets;
      }
    }

    // Add paddings
    padding = padding || 0;
    var isPaddingNumber = typeof padding === 'number';
    boundaries.left += isPaddingNumber ? padding : padding.left || 0;
    boundaries.top += isPaddingNumber ? padding : padding.top || 0;
    boundaries.right -= isPaddingNumber ? padding : padding.right || 0;
    boundaries.bottom -= isPaddingNumber ? padding : padding.bottom || 0;

    return boundaries;
  }

  function getArea(_ref) {
    var width = _ref.width,
        height = _ref.height;

    return width * height;
  }

  /**
   * Utility used to transform the `auto` placement to the placement with more
   * available space.
   * @method
   * @memberof Popper.Utils
   * @argument {Object} data - The data object generated by update method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function computeAutoPlacement(placement, refRect, popper, reference, boundariesElement) {
    var padding = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 0;

    if (placement.indexOf('auto') === -1) {
      return placement;
    }

    var boundaries = getBoundaries(popper, reference, padding, boundariesElement);

    var rects = {
      top: {
        width: boundaries.width,
        height: refRect.top - boundaries.top
      },
      right: {
        width: boundaries.right - refRect.right,
        height: boundaries.height
      },
      bottom: {
        width: boundaries.width,
        height: boundaries.bottom - refRect.bottom
      },
      left: {
        width: refRect.left - boundaries.left,
        height: boundaries.height
      }
    };

    var sortedAreas = Object.keys(rects).map(function (key) {
      return _extends({
        key: key
      }, rects[key], {
        area: getArea(rects[key])
      });
    }).sort(function (a, b) {
      return b.area - a.area;
    });

    var filteredAreas = sortedAreas.filter(function (_ref2) {
      var width = _ref2.width,
          height = _ref2.height;
      return width >= popper.clientWidth && height >= popper.clientHeight;
    });

    var computedPlacement = filteredAreas.length > 0 ? filteredAreas[0].key : sortedAreas[0].key;

    var variation = placement.split('-')[1];

    return computedPlacement + (variation ? '-' + variation : '');
  }

  /**
   * Get offsets to the reference element
   * @method
   * @memberof Popper.Utils
   * @param {Object} state
   * @param {Element} popper - the popper element
   * @param {Element} reference - the reference element (the popper will be relative to this)
   * @param {Element} fixedPosition - is in fixed position mode
   * @returns {Object} An object containing the offsets which will be applied to the popper
   */
  function getReferenceOffsets(state, popper, reference) {
    var fixedPosition = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

    var commonOffsetParent = fixedPosition ? getFixedPositionOffsetParent(popper) : findCommonOffsetParent(popper, reference);
    return getOffsetRectRelativeToArbitraryNode(reference, commonOffsetParent, fixedPosition);
  }

  /**
   * Get the outer sizes of the given element (offset size + margins)
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element
   * @returns {Object} object containing width and height properties
   */
  function getOuterSizes(element) {
    var window = element.ownerDocument.defaultView;
    var styles = window.getComputedStyle(element);
    var x = parseFloat(styles.marginTop || 0) + parseFloat(styles.marginBottom || 0);
    var y = parseFloat(styles.marginLeft || 0) + parseFloat(styles.marginRight || 0);
    var result = {
      width: element.offsetWidth + y,
      height: element.offsetHeight + x
    };
    return result;
  }

  /**
   * Get the opposite placement of the given one
   * @method
   * @memberof Popper.Utils
   * @argument {String} placement
   * @returns {String} flipped placement
   */
  function getOppositePlacement(placement) {
    var hash = { left: 'right', right: 'left', bottom: 'top', top: 'bottom' };
    return placement.replace(/left|right|bottom|top/g, function (matched) {
      return hash[matched];
    });
  }

  /**
   * Get offsets to the popper
   * @method
   * @memberof Popper.Utils
   * @param {Object} position - CSS position the Popper will get applied
   * @param {HTMLElement} popper - the popper element
   * @param {Object} referenceOffsets - the reference offsets (the popper will be relative to this)
   * @param {String} placement - one of the valid placement options
   * @returns {Object} popperOffsets - An object containing the offsets which will be applied to the popper
   */
  function getPopperOffsets(popper, referenceOffsets, placement) {
    placement = placement.split('-')[0];

    // Get popper node sizes
    var popperRect = getOuterSizes(popper);

    // Add position, width and height to our offsets object
    var popperOffsets = {
      width: popperRect.width,
      height: popperRect.height
    };

    // depending by the popper placement we have to compute its offsets slightly differently
    var isHoriz = ['right', 'left'].indexOf(placement) !== -1;
    var mainSide = isHoriz ? 'top' : 'left';
    var secondarySide = isHoriz ? 'left' : 'top';
    var measurement = isHoriz ? 'height' : 'width';
    var secondaryMeasurement = !isHoriz ? 'height' : 'width';

    popperOffsets[mainSide] = referenceOffsets[mainSide] + referenceOffsets[measurement] / 2 - popperRect[measurement] / 2;
    if (placement === secondarySide) {
      popperOffsets[secondarySide] = referenceOffsets[secondarySide] - popperRect[secondaryMeasurement];
    } else {
      popperOffsets[secondarySide] = referenceOffsets[getOppositePlacement(secondarySide)];
    }

    return popperOffsets;
  }

  /**
   * Mimics the `find` method of Array
   * @method
   * @memberof Popper.Utils
   * @argument {Array} arr
   * @argument prop
   * @argument value
   * @returns index or -1
   */
  function find(arr, check) {
    // use native find if supported
    if (Array.prototype.find) {
      return arr.find(check);
    }

    // use `filter` to obtain the same behavior of `find`
    return arr.filter(check)[0];
  }

  /**
   * Return the index of the matching object
   * @method
   * @memberof Popper.Utils
   * @argument {Array} arr
   * @argument prop
   * @argument value
   * @returns index or -1
   */
  function findIndex(arr, prop, value) {
    // use native findIndex if supported
    if (Array.prototype.findIndex) {
      return arr.findIndex(function (cur) {
        return cur[prop] === value;
      });
    }

    // use `find` + `indexOf` if `findIndex` isn't supported
    var match = find(arr, function (obj) {
      return obj[prop] === value;
    });
    return arr.indexOf(match);
  }

  /**
   * Loop trough the list of modifiers and run them in order,
   * each of them will then edit the data object.
   * @method
   * @memberof Popper.Utils
   * @param {dataObject} data
   * @param {Array} modifiers
   * @param {String} ends - Optional modifier name used as stopper
   * @returns {dataObject}
   */
  function runModifiers(modifiers, data, ends) {
    var modifiersToRun = ends === undefined ? modifiers : modifiers.slice(0, findIndex(modifiers, 'name', ends));

    modifiersToRun.forEach(function (modifier) {
      if (modifier['function']) {
        // eslint-disable-line dot-notation
        console.warn('`modifier.function` is deprecated, use `modifier.fn`!');
      }
      var fn = modifier['function'] || modifier.fn; // eslint-disable-line dot-notation
      if (modifier.enabled && isFunction(fn)) {
        // Add properties to offsets to make them a complete clientRect object
        // we do this before each modifier to make sure the previous one doesn't
        // mess with these values
        data.offsets.popper = getClientRect(data.offsets.popper);
        data.offsets.reference = getClientRect(data.offsets.reference);

        data = fn(data, modifier);
      }
    });

    return data;
  }

  /**
   * Updates the position of the popper, computing the new offsets and applying
   * the new style.<br />
   * Prefer `scheduleUpdate` over `update` because of performance reasons.
   * @method
   * @memberof Popper
   */
  function update() {
    // if popper is destroyed, don't perform any further update
    if (this.state.isDestroyed) {
      return;
    }

    var data = {
      instance: this,
      styles: {},
      arrowStyles: {},
      attributes: {},
      flipped: false,
      offsets: {}
    };

    // compute reference element offsets
    data.offsets.reference = getReferenceOffsets(this.state, this.popper, this.reference, this.options.positionFixed);

    // compute auto placement, store placement inside the data object,
    // modifiers will be able to edit `placement` if needed
    // and refer to originalPlacement to know the original value
    data.placement = computeAutoPlacement(this.options.placement, data.offsets.reference, this.popper, this.reference, this.options.modifiers.flip.boundariesElement, this.options.modifiers.flip.padding);

    // store the computed placement inside `originalPlacement`
    data.originalPlacement = data.placement;

    data.positionFixed = this.options.positionFixed;

    // compute the popper offsets
    data.offsets.popper = getPopperOffsets(this.popper, data.offsets.reference, data.placement);

    data.offsets.popper.position = this.options.positionFixed ? 'fixed' : 'absolute';

    // run the modifiers
    data = runModifiers(this.modifiers, data);

    // the first `update` will call `onCreate` callback
    // the other ones will call `onUpdate` callback
    if (!this.state.isCreated) {
      this.state.isCreated = true;
      this.options.onCreate(data);
    } else {
      this.options.onUpdate(data);
    }
  }

  /**
   * Helper used to know if the given modifier is enabled.
   * @method
   * @memberof Popper.Utils
   * @returns {Boolean}
   */
  function isModifierEnabled(modifiers, modifierName) {
    return modifiers.some(function (_ref) {
      var name = _ref.name,
          enabled = _ref.enabled;
      return enabled && name === modifierName;
    });
  }

  /**
   * Get the prefixed supported property name
   * @method
   * @memberof Popper.Utils
   * @argument {String} property (camelCase)
   * @returns {String} prefixed property (camelCase or PascalCase, depending on the vendor prefix)
   */
  function getSupportedPropertyName(property) {
    var prefixes = [false, 'ms', 'Webkit', 'Moz', 'O'];
    var upperProp = property.charAt(0).toUpperCase() + property.slice(1);

    for (var i = 0; i < prefixes.length; i++) {
      var prefix = prefixes[i];
      var toCheck = prefix ? '' + prefix + upperProp : property;
      if (typeof document.body.style[toCheck] !== 'undefined') {
        return toCheck;
      }
    }
    return null;
  }

  /**
   * Destroys the popper.
   * @method
   * @memberof Popper
   */
  function destroy() {
    this.state.isDestroyed = true;

    // touch DOM only if `applyStyle` modifier is enabled
    if (isModifierEnabled(this.modifiers, 'applyStyle')) {
      this.popper.removeAttribute('x-placement');
      this.popper.style.position = '';
      this.popper.style.top = '';
      this.popper.style.left = '';
      this.popper.style.right = '';
      this.popper.style.bottom = '';
      this.popper.style.willChange = '';
      this.popper.style[getSupportedPropertyName('transform')] = '';
    }

    this.disableEventListeners();

    // remove the popper if user explicity asked for the deletion on destroy
    // do not use `remove` because IE11 doesn't support it
    if (this.options.removeOnDestroy) {
      this.popper.parentNode.removeChild(this.popper);
    }
    return this;
  }

  /**
   * Get the window associated with the element
   * @argument {Element} element
   * @returns {Window}
   */
  function getWindow(element) {
    var ownerDocument = element.ownerDocument;
    return ownerDocument ? ownerDocument.defaultView : window;
  }

  function attachToScrollParents(scrollParent, event, callback, scrollParents) {
    var isBody = scrollParent.nodeName === 'BODY';
    var target = isBody ? scrollParent.ownerDocument.defaultView : scrollParent;
    target.addEventListener(event, callback, { passive: true });

    if (!isBody) {
      attachToScrollParents(getScrollParent(target.parentNode), event, callback, scrollParents);
    }
    scrollParents.push(target);
  }

  /**
   * Setup needed event listeners used to update the popper position
   * @method
   * @memberof Popper.Utils
   * @private
   */
  function setupEventListeners(reference, options, state, updateBound) {
    // Resize event listener on window
    state.updateBound = updateBound;
    getWindow(reference).addEventListener('resize', state.updateBound, { passive: true });

    // Scroll event listener on scroll parents
    var scrollElement = getScrollParent(reference);
    attachToScrollParents(scrollElement, 'scroll', state.updateBound, state.scrollParents);
    state.scrollElement = scrollElement;
    state.eventsEnabled = true;

    return state;
  }

  /**
   * It will add resize/scroll events and start recalculating
   * position of the popper element when they are triggered.
   * @method
   * @memberof Popper
   */
  function enableEventListeners() {
    if (!this.state.eventsEnabled) {
      this.state = setupEventListeners(this.reference, this.options, this.state, this.scheduleUpdate);
    }
  }

  /**
   * Remove event listeners used to update the popper position
   * @method
   * @memberof Popper.Utils
   * @private
   */
  function removeEventListeners(reference, state) {
    // Remove resize event listener on window
    getWindow(reference).removeEventListener('resize', state.updateBound);

    // Remove scroll event listener on scroll parents
    state.scrollParents.forEach(function (target) {
      target.removeEventListener('scroll', state.updateBound);
    });

    // Reset state
    state.updateBound = null;
    state.scrollParents = [];
    state.scrollElement = null;
    state.eventsEnabled = false;
    return state;
  }

  /**
   * It will remove resize/scroll events and won't recalculate popper position
   * when they are triggered. It also won't trigger `onUpdate` callback anymore,
   * unless you call `update` method manually.
   * @method
   * @memberof Popper
   */
  function disableEventListeners() {
    if (this.state.eventsEnabled) {
      cancelAnimationFrame(this.scheduleUpdate);
      this.state = removeEventListeners(this.reference, this.state);
    }
  }

  /**
   * Tells if a given input is a number
   * @method
   * @memberof Popper.Utils
   * @param {*} input to check
   * @return {Boolean}
   */
  function isNumeric(n) {
    return n !== '' && !isNaN(parseFloat(n)) && isFinite(n);
  }

  /**
   * Set the style to the given popper
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element - Element to apply the style to
   * @argument {Object} styles
   * Object with a list of properties and values which will be applied to the element
   */
  function setStyles(element, styles) {
    Object.keys(styles).forEach(function (prop) {
      var unit = '';
      // add unit if the value is numeric and is one of the following
      if (['width', 'height', 'top', 'right', 'bottom', 'left'].indexOf(prop) !== -1 && isNumeric(styles[prop])) {
        unit = 'px';
      }
      element.style[prop] = styles[prop] + unit;
    });
  }

  /**
   * Set the attributes to the given popper
   * @method
   * @memberof Popper.Utils
   * @argument {Element} element - Element to apply the attributes to
   * @argument {Object} styles
   * Object with a list of properties and values which will be applied to the element
   */
  function setAttributes(element, attributes) {
    Object.keys(attributes).forEach(function (prop) {
      var value = attributes[prop];
      if (value !== false) {
        element.setAttribute(prop, attributes[prop]);
      } else {
        element.removeAttribute(prop);
      }
    });
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by `update` method
   * @argument {Object} data.styles - List of style properties - values to apply to popper element
   * @argument {Object} data.attributes - List of attribute properties - values to apply to popper element
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The same data object
   */
  function applyStyle(data) {
    // any property present in `data.styles` will be applied to the popper,
    // in this way we can make the 3rd party modifiers add custom styles to it
    // Be aware, modifiers could override the properties defined in the previous
    // lines of this modifier!
    setStyles(data.instance.popper, data.styles);

    // any property present in `data.attributes` will be applied to the popper,
    // they will be set as HTML attributes of the element
    setAttributes(data.instance.popper, data.attributes);

    // if arrowElement is defined and arrowStyles has some properties
    if (data.arrowElement && Object.keys(data.arrowStyles).length) {
      setStyles(data.arrowElement, data.arrowStyles);
    }

    return data;
  }

  /**
   * Set the x-placement attribute before everything else because it could be used
   * to add margins to the popper margins needs to be calculated to get the
   * correct popper offsets.
   * @method
   * @memberof Popper.modifiers
   * @param {HTMLElement} reference - The reference element used to position the popper
   * @param {HTMLElement} popper - The HTML element used as popper
   * @param {Object} options - Popper.js options
   */
  function applyStyleOnLoad(reference, popper, options, modifierOptions, state) {
    // compute reference element offsets
    var referenceOffsets = getReferenceOffsets(state, popper, reference, options.positionFixed);

    // compute auto placement, store placement inside the data object,
    // modifiers will be able to edit `placement` if needed
    // and refer to originalPlacement to know the original value
    var placement = computeAutoPlacement(options.placement, referenceOffsets, popper, reference, options.modifiers.flip.boundariesElement, options.modifiers.flip.padding);

    popper.setAttribute('x-placement', placement);

    // Apply `position` to popper before anything else because
    // without the position applied we can't guarantee correct computations
    setStyles(popper, { position: options.positionFixed ? 'fixed' : 'absolute' });

    return options;
  }

  /**
   * @function
   * @memberof Popper.Utils
   * @argument {Object} data - The data object generated by `update` method
   * @argument {Boolean} shouldRound - If the offsets should be rounded at all
   * @returns {Object} The popper's position offsets rounded
   *
   * The tale of pixel-perfect positioning. It's still not 100% perfect, but as
   * good as it can be within reason.
   * Discussion here: https://github.com/FezVrasta/popper.js/pull/715
   *
   * Low DPI screens cause a popper to be blurry if not using full pixels (Safari
   * as well on High DPI screens).
   *
   * Firefox prefers no rounding for positioning and does not have blurriness on
   * high DPI screens.
   *
   * Only horizontal placement and left/right values need to be considered.
   */
  function getRoundedOffsets(data, shouldRound) {
    var _data$offsets = data.offsets,
        popper = _data$offsets.popper,
        reference = _data$offsets.reference;
    var round = Math.round,
        floor = Math.floor;

    var noRound = function noRound(v) {
      return v;
    };

    var referenceWidth = round(reference.width);
    var popperWidth = round(popper.width);

    var isVertical = ['left', 'right'].indexOf(data.placement) !== -1;
    var isVariation = data.placement.indexOf('-') !== -1;
    var sameWidthParity = referenceWidth % 2 === popperWidth % 2;
    var bothOddWidth = referenceWidth % 2 === 1 && popperWidth % 2 === 1;

    var horizontalToInteger = !shouldRound ? noRound : isVertical || isVariation || sameWidthParity ? round : floor;
    var verticalToInteger = !shouldRound ? noRound : round;

    return {
      left: horizontalToInteger(bothOddWidth && !isVariation && shouldRound ? popper.left - 1 : popper.left),
      top: verticalToInteger(popper.top),
      bottom: verticalToInteger(popper.bottom),
      right: horizontalToInteger(popper.right)
    };
  }

  var isFirefox = isBrowser && /Firefox/i.test(navigator.userAgent);

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by `update` method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function computeStyle(data, options) {
    var x = options.x,
        y = options.y;
    var popper = data.offsets.popper;

    // Remove this legacy support in Popper.js v2

    var legacyGpuAccelerationOption = find(data.instance.modifiers, function (modifier) {
      return modifier.name === 'applyStyle';
    }).gpuAcceleration;
    if (legacyGpuAccelerationOption !== undefined) {
      console.warn('WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!');
    }
    var gpuAcceleration = legacyGpuAccelerationOption !== undefined ? legacyGpuAccelerationOption : options.gpuAcceleration;

    var offsetParent = getOffsetParent(data.instance.popper);
    var offsetParentRect = getBoundingClientRect(offsetParent);

    // Styles
    var styles = {
      position: popper.position
    };

    var offsets = getRoundedOffsets(data, window.devicePixelRatio < 2 || !isFirefox);

    var sideA = x === 'bottom' ? 'top' : 'bottom';
    var sideB = y === 'right' ? 'left' : 'right';

    // if gpuAcceleration is set to `true` and transform is supported,
    //  we use `translate3d` to apply the position to the popper we
    // automatically use the supported prefixed version if needed
    var prefixedProperty = getSupportedPropertyName('transform');

    // now, let's make a step back and look at this code closely (wtf?)
    // If the content of the popper grows once it's been positioned, it
    // may happen that the popper gets misplaced because of the new content
    // overflowing its reference element
    // To avoid this problem, we provide two options (x and y), which allow
    // the consumer to define the offset origin.
    // If we position a popper on top of a reference element, we can set
    // `x` to `top` to make the popper grow towards its top instead of
    // its bottom.
    var left = void 0,
        top = void 0;
    if (sideA === 'bottom') {
      // when offsetParent is <html> the positioning is relative to the bottom of the screen (excluding the scrollbar)
      // and not the bottom of the html element
      if (offsetParent.nodeName === 'HTML') {
        top = -offsetParent.clientHeight + offsets.bottom;
      } else {
        top = -offsetParentRect.height + offsets.bottom;
      }
    } else {
      top = offsets.top;
    }
    if (sideB === 'right') {
      if (offsetParent.nodeName === 'HTML') {
        left = -offsetParent.clientWidth + offsets.right;
      } else {
        left = -offsetParentRect.width + offsets.right;
      }
    } else {
      left = offsets.left;
    }
    if (gpuAcceleration && prefixedProperty) {
      styles[prefixedProperty] = 'translate3d(' + left + 'px, ' + top + 'px, 0)';
      styles[sideA] = 0;
      styles[sideB] = 0;
      styles.willChange = 'transform';
    } else {
      // othwerise, we use the standard `top`, `left`, `bottom` and `right` properties
      var invertTop = sideA === 'bottom' ? -1 : 1;
      var invertLeft = sideB === 'right' ? -1 : 1;
      styles[sideA] = top * invertTop;
      styles[sideB] = left * invertLeft;
      styles.willChange = sideA + ', ' + sideB;
    }

    // Attributes
    var attributes = {
      'x-placement': data.placement
    };

    // Update `data` attributes, styles and arrowStyles
    data.attributes = _extends({}, attributes, data.attributes);
    data.styles = _extends({}, styles, data.styles);
    data.arrowStyles = _extends({}, data.offsets.arrow, data.arrowStyles);

    return data;
  }

  /**
   * Helper used to know if the given modifier depends from another one.<br />
   * It checks if the needed modifier is listed and enabled.
   * @method
   * @memberof Popper.Utils
   * @param {Array} modifiers - list of modifiers
   * @param {String} requestingName - name of requesting modifier
   * @param {String} requestedName - name of requested modifier
   * @returns {Boolean}
   */
  function isModifierRequired(modifiers, requestingName, requestedName) {
    var requesting = find(modifiers, function (_ref) {
      var name = _ref.name;
      return name === requestingName;
    });

    var isRequired = !!requesting && modifiers.some(function (modifier) {
      return modifier.name === requestedName && modifier.enabled && modifier.order < requesting.order;
    });

    if (!isRequired) {
      var _requesting = '`' + requestingName + '`';
      var requested = '`' + requestedName + '`';
      console.warn(requested + ' modifier is required by ' + _requesting + ' modifier in order to work, be sure to include it before ' + _requesting + '!');
    }
    return isRequired;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by update method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function arrow(data, options) {
    var _data$offsets$arrow;

    // arrow depends on keepTogether in order to work
    if (!isModifierRequired(data.instance.modifiers, 'arrow', 'keepTogether')) {
      return data;
    }

    var arrowElement = options.element;

    // if arrowElement is a string, suppose it's a CSS selector
    if (typeof arrowElement === 'string') {
      arrowElement = data.instance.popper.querySelector(arrowElement);

      // if arrowElement is not found, don't run the modifier
      if (!arrowElement) {
        return data;
      }
    } else {
      // if the arrowElement isn't a query selector we must check that the
      // provided DOM node is child of its popper node
      if (!data.instance.popper.contains(arrowElement)) {
        console.warn('WARNING: `arrow.element` must be child of its popper element!');
        return data;
      }
    }

    var placement = data.placement.split('-')[0];
    var _data$offsets = data.offsets,
        popper = _data$offsets.popper,
        reference = _data$offsets.reference;

    var isVertical = ['left', 'right'].indexOf(placement) !== -1;

    var len = isVertical ? 'height' : 'width';
    var sideCapitalized = isVertical ? 'Top' : 'Left';
    var side = sideCapitalized.toLowerCase();
    var altSide = isVertical ? 'left' : 'top';
    var opSide = isVertical ? 'bottom' : 'right';
    var arrowElementSize = getOuterSizes(arrowElement)[len];

    //
    // extends keepTogether behavior making sure the popper and its
    // reference have enough pixels in conjunction
    //

    // top/left side
    if (reference[opSide] - arrowElementSize < popper[side]) {
      data.offsets.popper[side] -= popper[side] - (reference[opSide] - arrowElementSize);
    }
    // bottom/right side
    if (reference[side] + arrowElementSize > popper[opSide]) {
      data.offsets.popper[side] += reference[side] + arrowElementSize - popper[opSide];
    }
    data.offsets.popper = getClientRect(data.offsets.popper);

    // compute center of the popper
    var center = reference[side] + reference[len] / 2 - arrowElementSize / 2;

    // Compute the sideValue using the updated popper offsets
    // take popper margin in account because we don't have this info available
    var css = getStyleComputedProperty(data.instance.popper);
    var popperMarginSide = parseFloat(css['margin' + sideCapitalized], 10);
    var popperBorderSide = parseFloat(css['border' + sideCapitalized + 'Width'], 10);
    var sideValue = center - data.offsets.popper[side] - popperMarginSide - popperBorderSide;

    // prevent arrowElement from being placed not contiguously to its popper
    sideValue = Math.max(Math.min(popper[len] - arrowElementSize, sideValue), 0);

    data.arrowElement = arrowElement;
    data.offsets.arrow = (_data$offsets$arrow = {}, defineProperty(_data$offsets$arrow, side, Math.round(sideValue)), defineProperty(_data$offsets$arrow, altSide, ''), _data$offsets$arrow);

    return data;
  }

  /**
   * Get the opposite placement variation of the given one
   * @method
   * @memberof Popper.Utils
   * @argument {String} placement variation
   * @returns {String} flipped placement variation
   */
  function getOppositeVariation(variation) {
    if (variation === 'end') {
      return 'start';
    } else if (variation === 'start') {
      return 'end';
    }
    return variation;
  }

  /**
   * List of accepted placements to use as values of the `placement` option.<br />
   * Valid placements are:
   * - `auto`
   * - `top`
   * - `right`
   * - `bottom`
   * - `left`
   *
   * Each placement can have a variation from this list:
   * - `-start`
   * - `-end`
   *
   * Variations are interpreted easily if you think of them as the left to right
   * written languages. Horizontally (`top` and `bottom`), `start` is left and `end`
   * is right.<br />
   * Vertically (`left` and `right`), `start` is top and `end` is bottom.
   *
   * Some valid examples are:
   * - `top-end` (on top of reference, right aligned)
   * - `right-start` (on right of reference, top aligned)
   * - `bottom` (on bottom, centered)
   * - `auto-end` (on the side with more space available, alignment depends by placement)
   *
   * @static
   * @type {Array}
   * @enum {String}
   * @readonly
   * @method placements
   * @memberof Popper
   */
  var placements = ['auto-start', 'auto', 'auto-end', 'top-start', 'top', 'top-end', 'right-start', 'right', 'right-end', 'bottom-end', 'bottom', 'bottom-start', 'left-end', 'left', 'left-start'];

  // Get rid of `auto` `auto-start` and `auto-end`
  var validPlacements = placements.slice(3);

  /**
   * Given an initial placement, returns all the subsequent placements
   * clockwise (or counter-clockwise).
   *
   * @method
   * @memberof Popper.Utils
   * @argument {String} placement - A valid placement (it accepts variations)
   * @argument {Boolean} counter - Set to true to walk the placements counterclockwise
   * @returns {Array} placements including their variations
   */
  function clockwise(placement) {
    var counter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

    var index = validPlacements.indexOf(placement);
    var arr = validPlacements.slice(index + 1).concat(validPlacements.slice(0, index));
    return counter ? arr.reverse() : arr;
  }

  var BEHAVIORS = {
    FLIP: 'flip',
    CLOCKWISE: 'clockwise',
    COUNTERCLOCKWISE: 'counterclockwise'
  };

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by update method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function flip(data, options) {
    // if `inner` modifier is enabled, we can't use the `flip` modifier
    if (isModifierEnabled(data.instance.modifiers, 'inner')) {
      return data;
    }

    if (data.flipped && data.placement === data.originalPlacement) {
      // seems like flip is trying to loop, probably there's not enough space on any of the flippable sides
      return data;
    }

    var boundaries = getBoundaries(data.instance.popper, data.instance.reference, options.padding, options.boundariesElement, data.positionFixed);

    var placement = data.placement.split('-')[0];
    var placementOpposite = getOppositePlacement(placement);
    var variation = data.placement.split('-')[1] || '';

    var flipOrder = [];

    switch (options.behavior) {
      case BEHAVIORS.FLIP:
        flipOrder = [placement, placementOpposite];
        break;
      case BEHAVIORS.CLOCKWISE:
        flipOrder = clockwise(placement);
        break;
      case BEHAVIORS.COUNTERCLOCKWISE:
        flipOrder = clockwise(placement, true);
        break;
      default:
        flipOrder = options.behavior;
    }

    flipOrder.forEach(function (step, index) {
      if (placement !== step || flipOrder.length === index + 1) {
        return data;
      }

      placement = data.placement.split('-')[0];
      placementOpposite = getOppositePlacement(placement);

      var popperOffsets = data.offsets.popper;
      var refOffsets = data.offsets.reference;

      // using floor because the reference offsets may contain decimals we are not going to consider here
      var floor = Math.floor;
      var overlapsRef = placement === 'left' && floor(popperOffsets.right) > floor(refOffsets.left) || placement === 'right' && floor(popperOffsets.left) < floor(refOffsets.right) || placement === 'top' && floor(popperOffsets.bottom) > floor(refOffsets.top) || placement === 'bottom' && floor(popperOffsets.top) < floor(refOffsets.bottom);

      var overflowsLeft = floor(popperOffsets.left) < floor(boundaries.left);
      var overflowsRight = floor(popperOffsets.right) > floor(boundaries.right);
      var overflowsTop = floor(popperOffsets.top) < floor(boundaries.top);
      var overflowsBottom = floor(popperOffsets.bottom) > floor(boundaries.bottom);

      var overflowsBoundaries = placement === 'left' && overflowsLeft || placement === 'right' && overflowsRight || placement === 'top' && overflowsTop || placement === 'bottom' && overflowsBottom;

      // flip the variation if required
      var isVertical = ['top', 'bottom'].indexOf(placement) !== -1;

      // flips variation if reference element overflows boundaries
      var flippedVariationByRef = !!options.flipVariations && (isVertical && variation === 'start' && overflowsLeft || isVertical && variation === 'end' && overflowsRight || !isVertical && variation === 'start' && overflowsTop || !isVertical && variation === 'end' && overflowsBottom);

      // flips variation if popper content overflows boundaries
      var flippedVariationByContent = !!options.flipVariationsByContent && (isVertical && variation === 'start' && overflowsRight || isVertical && variation === 'end' && overflowsLeft || !isVertical && variation === 'start' && overflowsBottom || !isVertical && variation === 'end' && overflowsTop);

      var flippedVariation = flippedVariationByRef || flippedVariationByContent;

      if (overlapsRef || overflowsBoundaries || flippedVariation) {
        // this boolean to detect any flip loop
        data.flipped = true;

        if (overlapsRef || overflowsBoundaries) {
          placement = flipOrder[index + 1];
        }

        if (flippedVariation) {
          variation = getOppositeVariation(variation);
        }

        data.placement = placement + (variation ? '-' + variation : '');

        // this object contains `position`, we want to preserve it along with
        // any additional property we may add in the future
        data.offsets.popper = _extends({}, data.offsets.popper, getPopperOffsets(data.instance.popper, data.offsets.reference, data.placement));

        data = runModifiers(data.instance.modifiers, data, 'flip');
      }
    });
    return data;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by update method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function keepTogether(data) {
    var _data$offsets = data.offsets,
        popper = _data$offsets.popper,
        reference = _data$offsets.reference;

    var placement = data.placement.split('-')[0];
    var floor = Math.floor;
    var isVertical = ['top', 'bottom'].indexOf(placement) !== -1;
    var side = isVertical ? 'right' : 'bottom';
    var opSide = isVertical ? 'left' : 'top';
    var measurement = isVertical ? 'width' : 'height';

    if (popper[side] < floor(reference[opSide])) {
      data.offsets.popper[opSide] = floor(reference[opSide]) - popper[measurement];
    }
    if (popper[opSide] > floor(reference[side])) {
      data.offsets.popper[opSide] = floor(reference[side]);
    }

    return data;
  }

  /**
   * Converts a string containing value + unit into a px value number
   * @function
   * @memberof {modifiers~offset}
   * @private
   * @argument {String} str - Value + unit string
   * @argument {String} measurement - `height` or `width`
   * @argument {Object} popperOffsets
   * @argument {Object} referenceOffsets
   * @returns {Number|String}
   * Value in pixels, or original string if no values were extracted
   */
  function toValue(str, measurement, popperOffsets, referenceOffsets) {
    // separate value from unit
    var split = str.match(/((?:\-|\+)?\d*\.?\d*)(.*)/);
    var value = +split[1];
    var unit = split[2];

    // If it's not a number it's an operator, I guess
    if (!value) {
      return str;
    }

    if (unit.indexOf('%') === 0) {
      var element = void 0;
      switch (unit) {
        case '%p':
          element = popperOffsets;
          break;
        case '%':
        case '%r':
        default:
          element = referenceOffsets;
      }

      var rect = getClientRect(element);
      return rect[measurement] / 100 * value;
    } else if (unit === 'vh' || unit === 'vw') {
      // if is a vh or vw, we calculate the size based on the viewport
      var size = void 0;
      if (unit === 'vh') {
        size = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
      } else {
        size = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
      }
      return size / 100 * value;
    } else {
      // if is an explicit pixel unit, we get rid of the unit and keep the value
      // if is an implicit unit, it's px, and we return just the value
      return value;
    }
  }

  /**
   * Parse an `offset` string to extrapolate `x` and `y` numeric offsets.
   * @function
   * @memberof {modifiers~offset}
   * @private
   * @argument {String} offset
   * @argument {Object} popperOffsets
   * @argument {Object} referenceOffsets
   * @argument {String} basePlacement
   * @returns {Array} a two cells array with x and y offsets in numbers
   */
  function parseOffset(offset, popperOffsets, referenceOffsets, basePlacement) {
    var offsets = [0, 0];

    // Use height if placement is left or right and index is 0 otherwise use width
    // in this way the first offset will use an axis and the second one
    // will use the other one
    var useHeight = ['right', 'left'].indexOf(basePlacement) !== -1;

    // Split the offset string to obtain a list of values and operands
    // The regex addresses values with the plus or minus sign in front (+10, -20, etc)
    var fragments = offset.split(/(\+|\-)/).map(function (frag) {
      return frag.trim();
    });

    // Detect if the offset string contains a pair of values or a single one
    // they could be separated by comma or space
    var divider = fragments.indexOf(find(fragments, function (frag) {
      return frag.search(/,|\s/) !== -1;
    }));

    if (fragments[divider] && fragments[divider].indexOf(',') === -1) {
      console.warn('Offsets separated by white space(s) are deprecated, use a comma (,) instead.');
    }

    // If divider is found, we divide the list of values and operands to divide
    // them by ofset X and Y.
    var splitRegex = /\s*,\s*|\s+/;
    var ops = divider !== -1 ? [fragments.slice(0, divider).concat([fragments[divider].split(splitRegex)[0]]), [fragments[divider].split(splitRegex)[1]].concat(fragments.slice(divider + 1))] : [fragments];

    // Convert the values with units to absolute pixels to allow our computations
    ops = ops.map(function (op, index) {
      // Most of the units rely on the orientation of the popper
      var measurement = (index === 1 ? !useHeight : useHeight) ? 'height' : 'width';
      var mergeWithPrevious = false;
      return op
      // This aggregates any `+` or `-` sign that aren't considered operators
      // e.g.: 10 + +5 => [10, +, +5]
      .reduce(function (a, b) {
        if (a[a.length - 1] === '' && ['+', '-'].indexOf(b) !== -1) {
          a[a.length - 1] = b;
          mergeWithPrevious = true;
          return a;
        } else if (mergeWithPrevious) {
          a[a.length - 1] += b;
          mergeWithPrevious = false;
          return a;
        } else {
          return a.concat(b);
        }
      }, [])
      // Here we convert the string values into number values (in px)
      .map(function (str) {
        return toValue(str, measurement, popperOffsets, referenceOffsets);
      });
    });

    // Loop trough the offsets arrays and execute the operations
    ops.forEach(function (op, index) {
      op.forEach(function (frag, index2) {
        if (isNumeric(frag)) {
          offsets[index] += frag * (op[index2 - 1] === '-' ? -1 : 1);
        }
      });
    });
    return offsets;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by update method
   * @argument {Object} options - Modifiers configuration and options
   * @argument {Number|String} options.offset=0
   * The offset value as described in the modifier description
   * @returns {Object} The data object, properly modified
   */
  function offset(data, _ref) {
    var offset = _ref.offset;
    var placement = data.placement,
        _data$offsets = data.offsets,
        popper = _data$offsets.popper,
        reference = _data$offsets.reference;

    var basePlacement = placement.split('-')[0];

    var offsets = void 0;
    if (isNumeric(+offset)) {
      offsets = [+offset, 0];
    } else {
      offsets = parseOffset(offset, popper, reference, basePlacement);
    }

    if (basePlacement === 'left') {
      popper.top += offsets[0];
      popper.left -= offsets[1];
    } else if (basePlacement === 'right') {
      popper.top += offsets[0];
      popper.left += offsets[1];
    } else if (basePlacement === 'top') {
      popper.left += offsets[0];
      popper.top -= offsets[1];
    } else if (basePlacement === 'bottom') {
      popper.left += offsets[0];
      popper.top += offsets[1];
    }

    data.popper = popper;
    return data;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by `update` method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function preventOverflow(data, options) {
    var boundariesElement = options.boundariesElement || getOffsetParent(data.instance.popper);

    // If offsetParent is the reference element, we really want to
    // go one step up and use the next offsetParent as reference to
    // avoid to make this modifier completely useless and look like broken
    if (data.instance.reference === boundariesElement) {
      boundariesElement = getOffsetParent(boundariesElement);
    }

    // NOTE: DOM access here
    // resets the popper's position so that the document size can be calculated excluding
    // the size of the popper element itself
    var transformProp = getSupportedPropertyName('transform');
    var popperStyles = data.instance.popper.style; // assignment to help minification
    var top = popperStyles.top,
        left = popperStyles.left,
        transform = popperStyles[transformProp];

    popperStyles.top = '';
    popperStyles.left = '';
    popperStyles[transformProp] = '';

    var boundaries = getBoundaries(data.instance.popper, data.instance.reference, options.padding, boundariesElement, data.positionFixed);

    // NOTE: DOM access here
    // restores the original style properties after the offsets have been computed
    popperStyles.top = top;
    popperStyles.left = left;
    popperStyles[transformProp] = transform;

    options.boundaries = boundaries;

    var order = options.priority;
    var popper = data.offsets.popper;

    var check = {
      primary: function primary(placement) {
        var value = popper[placement];
        if (popper[placement] < boundaries[placement] && !options.escapeWithReference) {
          value = Math.max(popper[placement], boundaries[placement]);
        }
        return defineProperty({}, placement, value);
      },
      secondary: function secondary(placement) {
        var mainSide = placement === 'right' ? 'left' : 'top';
        var value = popper[mainSide];
        if (popper[placement] > boundaries[placement] && !options.escapeWithReference) {
          value = Math.min(popper[mainSide], boundaries[placement] - (placement === 'right' ? popper.width : popper.height));
        }
        return defineProperty({}, mainSide, value);
      }
    };

    order.forEach(function (placement) {
      var side = ['left', 'top'].indexOf(placement) !== -1 ? 'primary' : 'secondary';
      popper = _extends({}, popper, check[side](placement));
    });

    data.offsets.popper = popper;

    return data;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by `update` method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function shift(data) {
    var placement = data.placement;
    var basePlacement = placement.split('-')[0];
    var shiftvariation = placement.split('-')[1];

    // if shift shiftvariation is specified, run the modifier
    if (shiftvariation) {
      var _data$offsets = data.offsets,
          reference = _data$offsets.reference,
          popper = _data$offsets.popper;

      var isVertical = ['bottom', 'top'].indexOf(basePlacement) !== -1;
      var side = isVertical ? 'left' : 'top';
      var measurement = isVertical ? 'width' : 'height';

      var shiftOffsets = {
        start: defineProperty({}, side, reference[side]),
        end: defineProperty({}, side, reference[side] + reference[measurement] - popper[measurement])
      };

      data.offsets.popper = _extends({}, popper, shiftOffsets[shiftvariation]);
    }

    return data;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by update method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function hide(data) {
    if (!isModifierRequired(data.instance.modifiers, 'hide', 'preventOverflow')) {
      return data;
    }

    var refRect = data.offsets.reference;
    var bound = find(data.instance.modifiers, function (modifier) {
      return modifier.name === 'preventOverflow';
    }).boundaries;

    if (refRect.bottom < bound.top || refRect.left > bound.right || refRect.top > bound.bottom || refRect.right < bound.left) {
      // Avoid unnecessary DOM access if visibility hasn't changed
      if (data.hide === true) {
        return data;
      }

      data.hide = true;
      data.attributes['x-out-of-boundaries'] = '';
    } else {
      // Avoid unnecessary DOM access if visibility hasn't changed
      if (data.hide === false) {
        return data;
      }

      data.hide = false;
      data.attributes['x-out-of-boundaries'] = false;
    }

    return data;
  }

  /**
   * @function
   * @memberof Modifiers
   * @argument {Object} data - The data object generated by `update` method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {Object} The data object, properly modified
   */
  function inner(data) {
    var placement = data.placement;
    var basePlacement = placement.split('-')[0];
    var _data$offsets = data.offsets,
        popper = _data$offsets.popper,
        reference = _data$offsets.reference;

    var isHoriz = ['left', 'right'].indexOf(basePlacement) !== -1;

    var subtractLength = ['top', 'left'].indexOf(basePlacement) === -1;

    popper[isHoriz ? 'left' : 'top'] = reference[basePlacement] - (subtractLength ? popper[isHoriz ? 'width' : 'height'] : 0);

    data.placement = getOppositePlacement(placement);
    data.offsets.popper = getClientRect(popper);

    return data;
  }

  /**
   * Modifier function, each modifier can have a function of this type assigned
   * to its `fn` property.<br />
   * These functions will be called on each update, this means that you must
   * make sure they are performant enough to avoid performance bottlenecks.
   *
   * @function ModifierFn
   * @argument {dataObject} data - The data object generated by `update` method
   * @argument {Object} options - Modifiers configuration and options
   * @returns {dataObject} The data object, properly modified
   */

  /**
   * Modifiers are plugins used to alter the behavior of your poppers.<br />
   * Popper.js uses a set of 9 modifiers to provide all the basic functionalities
   * needed by the library.
   *
   * Usually you don't want to override the `order`, `fn` and `onLoad` props.
   * All the other properties are configurations that could be tweaked.
   * @namespace modifiers
   */
  var modifiers = {
    /**
     * Modifier used to shift the popper on the start or end of its reference
     * element.<br />
     * It will read the variation of the `placement` property.<br />
     * It can be one either `-end` or `-start`.
     * @memberof modifiers
     * @inner
     */
    shift: {
      /** @prop {number} order=100 - Index used to define the order of execution */
      order: 100,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: shift
    },

    /**
     * The `offset` modifier can shift your popper on both its axis.
     *
     * It accepts the following units:
     * - `px` or unit-less, interpreted as pixels
     * - `%` or `%r`, percentage relative to the length of the reference element
     * - `%p`, percentage relative to the length of the popper element
     * - `vw`, CSS viewport width unit
     * - `vh`, CSS viewport height unit
     *
     * For length is intended the main axis relative to the placement of the popper.<br />
     * This means that if the placement is `top` or `bottom`, the length will be the
     * `width`. In case of `left` or `right`, it will be the `height`.
     *
     * You can provide a single value (as `Number` or `String`), or a pair of values
     * as `String` divided by a comma or one (or more) white spaces.<br />
     * The latter is a deprecated method because it leads to confusion and will be
     * removed in v2.<br />
     * Additionally, it accepts additions and subtractions between different units.
     * Note that multiplications and divisions aren't supported.
     *
     * Valid examples are:
     * ```
     * 10
     * '10%'
     * '10, 10'
     * '10%, 10'
     * '10 + 10%'
     * '10 - 5vh + 3%'
     * '-10px + 5vh, 5px - 6%'
     * ```
     * > **NB**: If you desire to apply offsets to your poppers in a way that may make them overlap
     * > with their reference element, unfortunately, you will have to disable the `flip` modifier.
     * > You can read more on this at this [issue](https://github.com/FezVrasta/popper.js/issues/373).
     *
     * @memberof modifiers
     * @inner
     */
    offset: {
      /** @prop {number} order=200 - Index used to define the order of execution */
      order: 200,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: offset,
      /** @prop {Number|String} offset=0
       * The offset value as described in the modifier description
       */
      offset: 0
    },

    /**
     * Modifier used to prevent the popper from being positioned outside the boundary.
     *
     * A scenario exists where the reference itself is not within the boundaries.<br />
     * We can say it has "escaped the boundaries" — or just "escaped".<br />
     * In this case we need to decide whether the popper should either:
     *
     * - detach from the reference and remain "trapped" in the boundaries, or
     * - if it should ignore the boundary and "escape with its reference"
     *
     * When `escapeWithReference` is set to`true` and reference is completely
     * outside its boundaries, the popper will overflow (or completely leave)
     * the boundaries in order to remain attached to the edge of the reference.
     *
     * @memberof modifiers
     * @inner
     */
    preventOverflow: {
      /** @prop {number} order=300 - Index used to define the order of execution */
      order: 300,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: preventOverflow,
      /**
       * @prop {Array} [priority=['left','right','top','bottom']]
       * Popper will try to prevent overflow following these priorities by default,
       * then, it could overflow on the left and on top of the `boundariesElement`
       */
      priority: ['left', 'right', 'top', 'bottom'],
      /**
       * @prop {number} padding=5
       * Amount of pixel used to define a minimum distance between the boundaries
       * and the popper. This makes sure the popper always has a little padding
       * between the edges of its container
       */
      padding: 5,
      /**
       * @prop {String|HTMLElement} boundariesElement='scrollParent'
       * Boundaries used by the modifier. Can be `scrollParent`, `window`,
       * `viewport` or any DOM element.
       */
      boundariesElement: 'scrollParent'
    },

    /**
     * Modifier used to make sure the reference and its popper stay near each other
     * without leaving any gap between the two. Especially useful when the arrow is
     * enabled and you want to ensure that it points to its reference element.
     * It cares only about the first axis. You can still have poppers with margin
     * between the popper and its reference element.
     * @memberof modifiers
     * @inner
     */
    keepTogether: {
      /** @prop {number} order=400 - Index used to define the order of execution */
      order: 400,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: keepTogether
    },

    /**
     * This modifier is used to move the `arrowElement` of the popper to make
     * sure it is positioned between the reference element and its popper element.
     * It will read the outer size of the `arrowElement` node to detect how many
     * pixels of conjunction are needed.
     *
     * It has no effect if no `arrowElement` is provided.
     * @memberof modifiers
     * @inner
     */
    arrow: {
      /** @prop {number} order=500 - Index used to define the order of execution */
      order: 500,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: arrow,
      /** @prop {String|HTMLElement} element='[x-arrow]' - Selector or node used as arrow */
      element: '[x-arrow]'
    },

    /**
     * Modifier used to flip the popper's placement when it starts to overlap its
     * reference element.
     *
     * Requires the `preventOverflow` modifier before it in order to work.
     *
     * **NOTE:** this modifier will interrupt the current update cycle and will
     * restart it if it detects the need to flip the placement.
     * @memberof modifiers
     * @inner
     */
    flip: {
      /** @prop {number} order=600 - Index used to define the order of execution */
      order: 600,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: flip,
      /**
       * @prop {String|Array} behavior='flip'
       * The behavior used to change the popper's placement. It can be one of
       * `flip`, `clockwise`, `counterclockwise` or an array with a list of valid
       * placements (with optional variations)
       */
      behavior: 'flip',
      /**
       * @prop {number} padding=5
       * The popper will flip if it hits the edges of the `boundariesElement`
       */
      padding: 5,
      /**
       * @prop {String|HTMLElement} boundariesElement='viewport'
       * The element which will define the boundaries of the popper position.
       * The popper will never be placed outside of the defined boundaries
       * (except if `keepTogether` is enabled)
       */
      boundariesElement: 'viewport',
      /**
       * @prop {Boolean} flipVariations=false
       * The popper will switch placement variation between `-start` and `-end` when
       * the reference element overlaps its boundaries.
       *
       * The original placement should have a set variation.
       */
      flipVariations: false,
      /**
       * @prop {Boolean} flipVariationsByContent=false
       * The popper will switch placement variation between `-start` and `-end` when
       * the popper element overlaps its reference boundaries.
       *
       * The original placement should have a set variation.
       */
      flipVariationsByContent: false
    },

    /**
     * Modifier used to make the popper flow toward the inner of the reference element.
     * By default, when this modifier is disabled, the popper will be placed outside
     * the reference element.
     * @memberof modifiers
     * @inner
     */
    inner: {
      /** @prop {number} order=700 - Index used to define the order of execution */
      order: 700,
      /** @prop {Boolean} enabled=false - Whether the modifier is enabled or not */
      enabled: false,
      /** @prop {ModifierFn} */
      fn: inner
    },

    /**
     * Modifier used to hide the popper when its reference element is outside of the
     * popper boundaries. It will set a `x-out-of-boundaries` attribute which can
     * be used to hide with a CSS selector the popper when its reference is
     * out of boundaries.
     *
     * Requires the `preventOverflow` modifier before it in order to work.
     * @memberof modifiers
     * @inner
     */
    hide: {
      /** @prop {number} order=800 - Index used to define the order of execution */
      order: 800,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: hide
    },

    /**
     * Computes the style that will be applied to the popper element to gets
     * properly positioned.
     *
     * Note that this modifier will not touch the DOM, it just prepares the styles
     * so that `applyStyle` modifier can apply it. This separation is useful
     * in case you need to replace `applyStyle` with a custom implementation.
     *
     * This modifier has `850` as `order` value to maintain backward compatibility
     * with previous versions of Popper.js. Expect the modifiers ordering method
     * to change in future major versions of the library.
     *
     * @memberof modifiers
     * @inner
     */
    computeStyle: {
      /** @prop {number} order=850 - Index used to define the order of execution */
      order: 850,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: computeStyle,
      /**
       * @prop {Boolean} gpuAcceleration=true
       * If true, it uses the CSS 3D transformation to position the popper.
       * Otherwise, it will use the `top` and `left` properties
       */
      gpuAcceleration: true,
      /**
       * @prop {string} [x='bottom']
       * Where to anchor the X axis (`bottom` or `top`). AKA X offset origin.
       * Change this if your popper should grow in a direction different from `bottom`
       */
      x: 'bottom',
      /**
       * @prop {string} [x='left']
       * Where to anchor the Y axis (`left` or `right`). AKA Y offset origin.
       * Change this if your popper should grow in a direction different from `right`
       */
      y: 'right'
    },

    /**
     * Applies the computed styles to the popper element.
     *
     * All the DOM manipulations are limited to this modifier. This is useful in case
     * you want to integrate Popper.js inside a framework or view library and you
     * want to delegate all the DOM manipulations to it.
     *
     * Note that if you disable this modifier, you must make sure the popper element
     * has its position set to `absolute` before Popper.js can do its work!
     *
     * Just disable this modifier and define your own to achieve the desired effect.
     *
     * @memberof modifiers
     * @inner
     */
    applyStyle: {
      /** @prop {number} order=900 - Index used to define the order of execution */
      order: 900,
      /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
      enabled: true,
      /** @prop {ModifierFn} */
      fn: applyStyle,
      /** @prop {Function} */
      onLoad: applyStyleOnLoad,
      /**
       * @deprecated since version 1.10.0, the property moved to `computeStyle` modifier
       * @prop {Boolean} gpuAcceleration=true
       * If true, it uses the CSS 3D transformation to position the popper.
       * Otherwise, it will use the `top` and `left` properties
       */
      gpuAcceleration: undefined
    }
  };

  /**
   * The `dataObject` is an object containing all the information used by Popper.js.
   * This object is passed to modifiers and to the `onCreate` and `onUpdate` callbacks.
   * @name dataObject
   * @property {Object} data.instance The Popper.js instance
   * @property {String} data.placement Placement applied to popper
   * @property {String} data.originalPlacement Placement originally defined on init
   * @property {Boolean} data.flipped True if popper has been flipped by flip modifier
   * @property {Boolean} data.hide True if the reference element is out of boundaries, useful to know when to hide the popper
   * @property {HTMLElement} data.arrowElement Node used as arrow by arrow modifier
   * @property {Object} data.styles Any CSS property defined here will be applied to the popper. It expects the JavaScript nomenclature (eg. `marginBottom`)
   * @property {Object} data.arrowStyles Any CSS property defined here will be applied to the popper arrow. It expects the JavaScript nomenclature (eg. `marginBottom`)
   * @property {Object} data.boundaries Offsets of the popper boundaries
   * @property {Object} data.offsets The measurements of popper, reference and arrow elements
   * @property {Object} data.offsets.popper `top`, `left`, `width`, `height` values
   * @property {Object} data.offsets.reference `top`, `left`, `width`, `height` values
   * @property {Object} data.offsets.arrow] `top` and `left` offsets, only one of them will be different from 0
   */

  /**
   * Default options provided to Popper.js constructor.<br />
   * These can be overridden using the `options` argument of Popper.js.<br />
   * To override an option, simply pass an object with the same
   * structure of the `options` object, as the 3rd argument. For example:
   * ```
   * new Popper(ref, pop, {
   *   modifiers: {
   *     preventOverflow: { enabled: false }
   *   }
   * })
   * ```
   * @type {Object}
   * @static
   * @memberof Popper
   */
  var Defaults = {
    /**
     * Popper's placement.
     * @prop {Popper.placements} placement='bottom'
     */
    placement: 'bottom',

    /**
     * Set this to true if you want popper to position it self in 'fixed' mode
     * @prop {Boolean} positionFixed=false
     */
    positionFixed: false,

    /**
     * Whether events (resize, scroll) are initially enabled.
     * @prop {Boolean} eventsEnabled=true
     */
    eventsEnabled: true,

    /**
     * Set to true if you want to automatically remove the popper when
     * you call the `destroy` method.
     * @prop {Boolean} removeOnDestroy=false
     */
    removeOnDestroy: false,

    /**
     * Callback called when the popper is created.<br />
     * By default, it is set to no-op.<br />
     * Access Popper.js instance with `data.instance`.
     * @prop {onCreate}
     */
    onCreate: function onCreate() {},

    /**
     * Callback called when the popper is updated. This callback is not called
     * on the initialization/creation of the popper, but only on subsequent
     * updates.<br />
     * By default, it is set to no-op.<br />
     * Access Popper.js instance with `data.instance`.
     * @prop {onUpdate}
     */
    onUpdate: function onUpdate() {},

    /**
     * List of modifiers used to modify the offsets before they are applied to the popper.
     * They provide most of the functionalities of Popper.js.
     * @prop {modifiers}
     */
    modifiers: modifiers
  };

  /**
   * @callback onCreate
   * @param {dataObject} data
   */

  /**
   * @callback onUpdate
   * @param {dataObject} data
   */

  // Utils
  // Methods
  var Popper = function () {
    /**
     * Creates a new Popper.js instance.
     * @class Popper
     * @param {Element|referenceObject} reference - The reference element used to position the popper
     * @param {Element} popper - The HTML / XML element used as the popper
     * @param {Object} options - Your custom options to override the ones defined in [Defaults](#defaults)
     * @return {Object} instance - The generated Popper.js instance
     */
    function Popper(reference, popper) {
      var _this = this;

      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      classCallCheck(this, Popper);

      this.scheduleUpdate = function () {
        return requestAnimationFrame(_this.update);
      };

      // make update() debounced, so that it only runs at most once-per-tick
      this.update = debounce(this.update.bind(this));

      // with {} we create a new object with the options inside it
      this.options = _extends({}, Popper.Defaults, options);

      // init state
      this.state = {
        isDestroyed: false,
        isCreated: false,
        scrollParents: []
      };

      // get reference and popper elements (allow jQuery wrappers)
      this.reference = reference && reference.jquery ? reference[0] : reference;
      this.popper = popper && popper.jquery ? popper[0] : popper;

      // Deep merge modifiers options
      this.options.modifiers = {};
      Object.keys(_extends({}, Popper.Defaults.modifiers, options.modifiers)).forEach(function (name) {
        _this.options.modifiers[name] = _extends({}, Popper.Defaults.modifiers[name] || {}, options.modifiers ? options.modifiers[name] : {});
      });

      // Refactoring modifiers' list (Object => Array)
      this.modifiers = Object.keys(this.options.modifiers).map(function (name) {
        return _extends({
          name: name
        }, _this.options.modifiers[name]);
      })
      // sort the modifiers by order
      .sort(function (a, b) {
        return a.order - b.order;
      });

      // modifiers have the ability to execute arbitrary code when Popper.js get inited
      // such code is executed in the same order of its modifier
      // they could add new properties to their options configuration
      // BE AWARE: don't add options to `options.modifiers.name` but to `modifierOptions`!
      this.modifiers.forEach(function (modifierOptions) {
        if (modifierOptions.enabled && isFunction(modifierOptions.onLoad)) {
          modifierOptions.onLoad(_this.reference, _this.popper, _this.options, modifierOptions, _this.state);
        }
      });

      // fire the first update to position the popper in the right place
      this.update();

      var eventsEnabled = this.options.eventsEnabled;
      if (eventsEnabled) {
        // setup event listeners, they will take care of update the position in specific situations
        this.enableEventListeners();
      }

      this.state.eventsEnabled = eventsEnabled;
    }

    // We can't use class properties because they don't get listed in the
    // class prototype and break stuff like Sinon stubs


    createClass(Popper, [{
      key: 'update',
      value: function update$$1() {
        return update.call(this);
      }
    }, {
      key: 'destroy',
      value: function destroy$$1() {
        return destroy.call(this);
      }
    }, {
      key: 'enableEventListeners',
      value: function enableEventListeners$$1() {
        return enableEventListeners.call(this);
      }
    }, {
      key: 'disableEventListeners',
      value: function disableEventListeners$$1() {
        return disableEventListeners.call(this);
      }

      /**
       * Schedules an update. It will run on the next UI update available.
       * @method scheduleUpdate
       * @memberof Popper
       */


      /**
       * Collection of utilities useful when writing custom modifiers.
       * Starting from version 1.7, this method is available only if you
       * include `popper-utils.js` before `popper.js`.
       *
       * **DEPRECATION**: This way to access PopperUtils is deprecated
       * and will be removed in v2! Use the PopperUtils module directly instead.
       * Due to the high instability of the methods contained in Utils, we can't
       * guarantee them to follow semver. Use them at your own risk!
       * @static
       * @private
       * @type {Object}
       * @deprecated since version 1.8
       * @member Utils
       * @memberof Popper
       */

    }]);
    return Popper;
  }();

  /**
   * The `referenceObject` is an object that provides an interface compatible with Popper.js
   * and lets you use it as replacement of a real DOM node.<br />
   * You can use this method to position a popper relatively to a set of coordinates
   * in case you don't have a DOM node to use as reference.
   *
   * ```
   * new Popper(referenceObject, popperNode);
   * ```
   *
   * NB: This feature isn't supported in Internet Explorer 10.
   * @name referenceObject
   * @property {Function} data.getBoundingClientRect
   * A function that returns a set of coordinates compatible with the native `getBoundingClientRect` method.
   * @property {number} data.clientWidth
   * An ES6 getter that will return the width of the virtual reference element.
   * @property {number} data.clientHeight
   * An ES6 getter that will return the height of the virtual reference element.
   */


  Popper.Utils = (typeof window !== 'undefined' ? window : global).PopperUtils;
  Popper.placements = placements;
  Popper.Defaults = Defaults;

  /* ========================================================================
   *
   * Bootstrap Tourist v0.2.0
   * Copyright FFS 2019
   * @ IGreatlyDislikeJavascript on Github
   * 
   * This code is a fork of bootstrap-tour, with a lot of extra features
   * and fixes. You can read about why this fork exists here:
   *
   * https://github.com/sorich87/bootstrap-tour/issues/713
   *
   * The entire purpose of this fork is to start rewriting bootstrap-tour
   * into native ES6 instead of the original coffeescript, and to implement
   * the features and fixes requested in the github repo. Ideally this fork
   * will then be taken back into the main repo and become part of
   * bootstrap-tour again - this is not a fork to create a new plugin!
   *
   * I'm not a JS coder, so suggest you test very carefully and read the
   * docs (comments) below before using.
   *
   * If anyone would like to take on the creation of proper docs for
   * Tourist, please feel free and post here:
   * https://github.com/IGreatlyDislikeJavascript/bootstrap-tourist/issues/15
   *
   * ========================================================================
   * ENTIRELY BASED UPON:
   *
   * bootstrap-tour - v0.2.0
   * http://bootstraptour.com
   * ========================================================================
   * Copyright 2012-2015 Ulrich Sossou
   *
   * ========================================================================
   * Licensed under the MIT License (the "License");
   * you may not use this file except in compliance with the License.
   * You may obtain a copy of the License at
   *
   *     https://opensource.org/licenses/MIT
   *
   * Unless required by applicable law or agreed to in writing, software
   * distributed under the License is distributed on an "AS IS" BASIS,
   * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   * See the License for the specific language governing permissions and
   * limitations under the License.
   * ========================================================================
   *
   * Update in July 2019 by Markus Flür
   * Used ES6 Class to create a nice scoping and es6 type variable definition for better memory management and again correct scoping.
   * 
   * Updated for CS by FFS 2018
   *
   * Changes in v0.2.0
   *  - Version update as major fix to bug preventing element: function(){...} feature under BS4/popper.js
   *  - published as release
   *
   * Changes IN v0.12 FROM v0.11:
   *	- note version labelling change in this changelog!
   *  - fixes to the button text change code and better prep for localization (thanks to @DancingDad, @thenewbeat, @bardware)
   *	- fixed css for BS4 progress text to correctly use float-right (thanks to @macroscian, @thenewbeat)
   *
   * Changes from v0.10:
   *  - added support for changing button texts (thanks to @vneri)
   *	- added dummy init() to support drop-in replacement for Tour (thanks to @pau1phi11ips)
   *
   * Changes from 0.9:
   *  - smartPlacement option removed, deprecated
   *  - default params compatibility for IE
   *  - auto progress bar was killed in changes 0.7 -> 0.8 due to Bootstrap sanitizer, this is readded
   *  - major change to manipulation of BS4 popper.js for orphan steps
   *  - change to implementation of backdrop
   *
   * Changes from 0.8:
   *	- The fast fix in v0.7 didn't work for Bootstrap 4. This release is to ensure fully working popovers in BS4. Issue is that the Bootstrap CDN
   *		doesn't actually have the whitelist property, so developing against it is basically useless :(
   *	- Improved BS4 support and template switching. Changed options for framework vs template.
   *
   * Changes from 0.7:
   *  - Fast release to fix breaking change in Bootstrap 3.4.1, fixes this issue: https://github.com/sorich87/bootstrap-tour/issues/723#issuecomment-471107788
   *		Issue is caused by the BS sanitizer, to avoid this reoccurring the "sanitizeWhitelist:" and "sanitizeFunction:" global options added
   *
   * Changes from 0.6:
   *	- Fixed invalid call to debug in _showNextStep()
   *	- Added onPreviouslyEnded() callback: https://github.com/sorich87/bootstrap-tour/issues/720
   *	- Added selector to switch between bootstrap3 and bootstrap4 or custom template, thanks to: https://github.com/sorich87/bootstrap-tour/pull/643
   *
   * Changes from 0.5:
   *	- Added "unfix" for bootstrap selectpicker to revert zindex after step that includes this plugin
   *  - Fixed issue with Bootstrap dialogs. Handling of dialogs is now robust
   *  - Fixed issue with BootstrapDialog plugin: https://nakupanda.github.io/bootstrap3-dialog/ . See notes below for help.
   *  - Improved the background overlay and scroll handling, unnecessary work removed


   ---------


   This fork and code adds following features to Bootstrap Tour

   1. onNext/onPrevious - prevent auto-move to next step, allow .goTo
   2. *** Do not call Tour.init *** - fixed tours with hidden elements on page reload
   3. Dynamically determine step element by function
   4. Only continue tour when reflex element is clicked using reflexOnly
   5. Call onElementUnavailable if step element is missing
   6. Scroll flicker/continual step reload fixed
   7. Magic progress bar and progress text, plus options to customize per step
   8. Prevent user interaction with element using preventInteraction
   9. Wait for arbitrary DOM element to be visible before showing tour step/crapping out due to missing element, using delayOnElement
   10. Handle bootstrap modal dialogs better - autodetect modals or children of modals, and call onModalHidden to handle when user dismisses modal without following tour steps
   11. Automagically fixes drawing issues with Bootstrap Selectpicker (https://github.com/snapappointments/bootstrap-select/)
   12. Call onPreviouslyEnded if tour.start() is called for a tour that has previously ended (see docs)
   13. Switch between Bootstrap 3 or 4 (popover methods and template) automatically using tour options
   14. Added sanitizeWhitelist and sanitizeFunction global options
   15. Added support for changing button texts

   --------------
  	1. Control flow from onNext() / onPrevious() options:
   			Returning false from onNext/onPrevious handler will prevent Tour from automatically moving to the next/previous step.
  			Tour flow methods (Tour.goTo etc) now also work correctly in onNext/onPrevious.
  			Option is available per step or globally:

  			var tourSteps = [
  								{
  									element: "#inputBanana",
  									title: "Bananas!",
  									content: "Bananas are yellow, except when they're not",
  									onNext: function(tour){
  										if($('#inputBanana').val() !== "banana")
  										{
  											// no banana? highlight the banana field
  											$('#inputBanana').css("background-color", "red");
  											// do not jump to the next tour step!
  											return false;
  										}
  									}
  								}
  							];

  			var Tour=new Tour({
  								steps: tourSteps,
  								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
                  buttonTexts:{           // customize or localize button texts
                    nextButton:"go on",
                    endTourButton:"ok it's over",
                  }
  								onNext: function(tour)
  										{
  											if(someVar = true)
  											{
  												// force the tour to jump to slide 3
  												tour.goTo(3);
  												// Prevent default move to next step - important!
  												return false;
  											}
  										}
  							});

   --------------
  	2. Do not call Tour.init
  			When setting up Tour, do not call Tour.init().
  			Call Tour.start() to start/resume the Tour from previous step
  			Call Tour.restart() to always start Tour from first step

  			Tour.init() was a redundant method that caused conflict with hidden Tour elements.

  			As of Tourist v0.11, calling Tour.init() will generate a warning in the console (thanks to @pau1phi11ips).

  ---------------
  	3. Dynamically determine element by function
  			Step "element:" option allows element to be determined programmatically. Return a jquery object.
  			The following is possible:

  			var tourSteps = [
  								{
  									element: function() { return $(document).find("...something..."); },
  									title: "Dynamic",
  									content: "Element found by function"
  								},
  								{
  									element: "#static",
  									title: "Static",
  									content: "Element found by static ID"
  								}
  							];

  ---------------
  	4. Only continue tour when reflex element is clicked
  			Use step option reflexOnly in conjunction with step option reflex to automagically hide the "next" button in the tour, and only continue when the user clicks the element:
  			var tourSteps = [
  								{
  									element: "#myButton",
  									reflex: true,
  									reflexOnly: true,
  									title: "Click it",
  									content: "Click to continue, or you're stuck"
  								}
  							];

  ----------------
  	5. Call function when element is missing
  			If the element specified in the step (static or dynamically determined as per feature #3), onElementUnavailable is called.
  			Function signature: function(tour, stepNumber) {}
  			Option is available at global and per step levels.

  			Use it per step to have a step-specific error handler:
  				function tourStepBroken(tour, stepNumber)
  				{
  					alert("Uhoh, the tour broke on the #btnMagic element);
  				}

  				var tourSteps = [
  									{
  										element: "#btnMagic",
  										onElementUnavailable: tourStepBroken,
  										title: "Hold my beer",
  										content: "now watch this"
  									}
  								];


  			Use it globally, and optionally override per step, to have a robust and comprehensive error handler:
  				function tourBroken(tour, stepNumber)
  				{
  					alert("The default error handler: tour element is done broke on step number " + stepNumber);
  				}

  				var tourSteps = [
  									{
  										element: "#btnThis",
  										//onElementUnavailable: COMMENTED OUT, therefore default global handler used
  										title: "Some button",
  										content: "Some content"
  									},
  									{
  										element: "#btnThat",
  										onElementUnavailable: 	function(tour, stepNumber)
  																{
  																	// override the default global handler for this step only
  																	alert("The tour broke on #btnThat step");
  																},
  										title: "Another button",
  										content: "More content"
  									}
  								];

  				var Tour=new Tour({
  									steps: tourSteps,
  									framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
  									onElementUnavailable: tourBroken, // default "element unavailable" handler for all tour steps
  								});

  ---------------
  	6. Scroll flicker / continue reload fixed
  			Original Tour constantly reloaded the current tour step on scroll & similar events. This produced flickering, constant reloads and therefore constant calls to all the step function calls.
  			This is now fixed. Scrolling the browser window does not cause the tour step to reload.

  			IMPORTANT: orphan steps are stuck to the center of the screen. However steps linked to elements ALWAYS stay stuck to their element, even if user scrolls the element & tour popover
  						off the screen. This is my personal preference, as original functionality of tour step moving with the scroll even when the element was off the viewport seemed strange.

  ---------------
  	7. Progress bar & progress text:
  			With thanks to @macroscian, @thenewbeat for fixes to this code, incorporated in Tourist v0.12

  			Use the following options globally or per step to show tour progress:
  			showProgressBar - shows a bootstrap progress bar for tour progress at the top of the tour content
  			showProgressText - shows a textual progress (N/X, i.e.: 1/24 for slide 1 of 24) in the tour title

  			var tourSteps = [
  								{
  									element: "#inputBanana",
  									title: "Bananas!",
  									content: "Bananas are yellow, except when they're not",
  								},
  								{
  									element: "#inputOranges",
  									title: "Oranges!",
  									content: "Oranges are not bananas",
  									showProgressBar: false,	// don't show the progress bar on this step only
  									showProgressText: false, // don't show the progress text on this step only
  								}
  							];
  			var Tour=new Tour({
  								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
  								steps: tourSteps,
  								showProgressBar: true, // default show progress bar
  								showProgressText: true, // default show progress text
  							});

  	7b. Customize the progressbar/progress text:
  			In conjunction with 7a, provide the following functions globally or per step to draw your own progressbar/progress text:

  			getProgressBarHTML(percent)
  			getProgressTextHTML(stepNumber, percent, stepCount)

  			These will be called when each step is shown, with the appropriate percentage/step number etc passed to your function. Return an HTML string of a "drawn" progress bar/progress text
  			which will be directly inserted into the tour step.

  			Example:
  			var tourSteps = [
  								{
  									element: "#inputBanana",
  									title: "Bananas!",
  									content: "Bananas are yellow, except when they're not",
  								},
  								{
  									element: "#inputOranges",
  									title: "Oranges!",
  									content: "Oranges are not bananas",
  									getProgressBarHTML:	function(percent)
  														{
  															// override the global progress bar function for this step
  															return '<div>You're ' + percent + ' of the way through!</div>';
  														}
  								}
  							];
  			var Tour=new Tour({
  								steps: tourSteps,
  								showProgressBar: true, // default show progress bar
  								showProgressText: true, // default show progress text
  								getProgressBarHTML: 	function(percent)
  														{
  															// default progress bar for all steps. Return valid HTML to draw the progress bar you want
  															return '<div class="progress"><div class="progress-bar progress-bar-striped" role="progressbar" style="width: ' + percent + '%;"></div></div>';
  														},
  								getProgressTextHTML: 	function(stepNumber, percent, stepCount)
  														{
  															// default progress text for all steps
  															return 'Slide ' + stepNumber + "/" + stepCount;
  														},

  							});

  ----------------
  	8. Prevent interaction with element
  			Sometimes you want to highlight a DOM element (button, input field) for a tour step, but don't want the user to be able to interact with it.
  			Use preventInteraction to stop the user touching the element:

  			var tourSteps = [
  								{
  									element: "#btnMCHammer",
  									preventInteraction: true,
  									title: "Hammer Time",
  									content: "You can't touch this"
  								}
  							];

  ----------------
  	9. Wait for an element to appear before continuing tour
  			Sometimes a tour step element might not be immediately ready because of transition effects etc. This is a specific issue with bootstrap select, which is relatively slow to show the selectpicker
  			dropdown after clicking.
  			Use delayOnElement to instruct Tour to wait for **ANY** element to appear before showing the step (or crapping out due to missing element). Yes this means the tour step element can be one DOM
  			element, but the delay will wait for a completely separate DOM element to appear. This is really useful for hidden divs etc.
  			Use in conjunction with onElementUnavailable for robust tour step handling.

  			delayOnElement is an object with the following:
  							delayOnElement: {
  												delayElement: "#waitForMe", // the element to wait to become visible, or the string literal "element" to use the step element
  												maxDelay: 2000 // optional milliseconds to wait/timeout for the element, before crapping out. If maxDelay is not specified, this is 2000ms by default,
  											}

  			var tourSteps = [
  								{
  									element: "#btnPrettyTransition",
  									delayOnElement:	{
  														delayElement: "element" // use string literal "element" to wait for this step's element, i.e.: #btnPrettyTransition
  													},
  									title: "Ages",
  									content: "This button takes ages to appear"
  								},
  								{
  									element: "#inputUnrelated",
  									delayOnElement:	{
  														delayElement: "#divStuff" // wait until DOM element "divStuff" is visible before showing this tour step against DOM element "inputUnrelated"
  													},
  									title: "Waiting",
  									content: "This input is nice, but you only see this step when the other div appears"
  								},
  								{
  									element: "#btnDontForgetThis",
  									delayOnElement:	{
  														delayElement: "element", // use string literal "element" to wait for this step's element, i.e.: #btnDontForgetThis
  														maxDelay: 5000	// wait 5 seconds for it to appear before timing out
  													},
  									title: "Cool",
  									content: "Remember the onElementUnavailable option!",
  									onElementUnavailable: 	function(tour, stepNumber)
  															{
  																// This will be called if btnDontForgetThis is not visible after 5 seconds
  																console.log("Well that went badly wrong");
  															}
  								},
  							];

  ----------------
  	10. Trigger when modal closes
  			If tour element is a modal, or is a DOM element inside a modal, the element can disappear "at random" if the user dismisses the dialog.
  			In this case, onModalHidden global and per step function is called. Only functional when step is not an orphan.
  			This is useful if a tour includes a step that launches a modal, and the tour requires the user to take some actions inside the modal before OK'ing it and moving to the next
  			tour step.

  			Return (int) step number to immediately move to that step
  			Return exactly false to not change tour state in any way - this is useful if you need to reshow the modal because some validation failed
  			Return anything else to move to the next step

  			element === Bootstrap modal, or element parent === bootstrap modal is automatically detected.

  			var Tour=new Tour({
  								steps: tourSteps,
  								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
  								onModalHidden: 	function(tour, stepNumber)
  												{
  													console.log("Well damn, this step's element was a modal, or inside a modal, and the modal just done got dismissed y'all. Moving to step 3.");

  													// move to step number 3
  													return 3;
  												},
  							});


  			var Tour=new Tour({
  								steps: tourSteps,
  								onModalHidden: 	function(tour, stepNumber)
  												{
  													if(validateSomeModalContent() == false)
  													{
  														// The validation failed, user dismissed modal without properly taking actions.
  														// Show the modal again
  														showModalAgain();

  														// Instruct tour to stay on same step
  														return false;
  													}
  													else
  													{
  														// Content was valid. Return null or do nothing to instruct tour to continue to next step
  													}
  												},
  							});



  	10b. Handle Dialogs and BootstrapDialog plugin better https://nakupanda.github.io/bootstrap3-dialog/
  			Plugin makes creating dialogs very easy, but it does some extra stuff to the dialogs and dynamically creates/destroys them. This
  			causes issues with plugins that want to include a modal dialog in the steps using this plugin.

  			To use Tour to highlight an element in a dialog, just use the element ID as you would for any normal step. The dialog will be automatically
  			detected and handled.

  			To use Tour to highlight an entire dialog, set the step element to the dialog div. Tour will automatically realize this is a dialog, and
  			shift the element to use the modal-content div inside the dialog. This makes life friendly, because you can do this:

  			<div class="modal" id="myModal" role="dialog">
  				<div class="modal-dialog">
  					<div class="modal-content">
  					...blah...
  					</div>
  				</div>
  			</div>

  			Then use element: myModal in the Tour.


  			FOR BOOTSTRAPDIALOG PLUGIN: this plugin creates random UUIDs for the dialog DOM ID. You need to fix the ID to something you know. Do this:

  				dlg = new BootstrapDialog.confirm({
  													....all the options...
  												});

  				// BootstrapDialog gives a random GUID ID for dialog. Give it a proper one
  				$objModal = dlg.getModal();
  				$objModal.attr("id", "myModal");
  				dlg.setId("myModal");


  			Now you can use element: myModal in the tour, even when the dialog is created by BootstrapDialog plugin.


  ----------------
  	11.	Fix conflict with Bootstrap Selectpicker: https://github.com/snapappointments/bootstrap-select/
  		Selectpicker draws a custom select. Tour now automagically finds and adjusts the selectpicker dropdown so that it appears correctly within the tour


  ----------------
  	12.	Call onPreviouslyEnded if tour.start() is called for a tour that has previously ended
  		See the following github issue: https://github.com/sorich87/bootstrap-tour/issues/720
  		Original behavior for a tour that had previously ended was to call onStart() callback, and then abort without calling onEnd(). This has been altered so
  		that calling start() on a tour that has previously ended (cookie step set to end etc) will now ONLY call onPreviouslyEnded().

  		This restores the functionality that allows app JS to simply call tour.start() on page load, and the Tour will now only call onStart() / onEnd() when
  		the tour really is started or ended.

  			var Tour=new Tour({
  								steps: [ ..... ],
  								framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
  								onPreviouslyEnded: 	function(tour)
  													{
  														console.log("Looks like this tour has already ended");
  													},
  							});

  			tour.start();

  ----------------
  	13.	Switch between Bootstrap 3 or 4 (popover methods, template) automatically using tour options, or use a custom template
  		With thanks to this thread: https://github.com/sorich87/bootstrap-tour/pull/643

  		Tour is compatible with bootstrap 3 and 4 if the right template and framework is used for the popover. Bootstrap3 framework compatibility is used by default.

  		To select the correct template and framework, use the "framework" global option. Note this option does more than just select a template, it also changes which
  		methods are used to manage the Tour popovers to be BS3 or BS4 compatible.

  			var Tour=new Tour({
  								steps: tourSteps,
  								template: null,			// template option is null by default. Tourist will use the appropriate template
  														// for the framework version, in this case BS3 as per next option
  								framework: "bootstrap3", // can be string literal "bootstrap3" or "bootstrap4"
  							});


  		To use a custom template, use the "template" global option:

  			var Tour=new Tour({
  								steps: tourSteps,
  								framework: "bootstrap4", // can be string literal "bootstrap3" or "bootstrap4"
  								template: '<div class="popover" role="tooltip">....blah....</div>'
  							});

  		Review the following logic:
  			- If template == null, default framework template is used based on whether framework is set to "bootstrap3" or "bootstrap4"
  			- If template != null, the specified template is always used
  			- If framework option is not literal "bootstrap3" or "bootstrap4", error will occur


  		To add additional templates, search the code for "PLACEHOLDER: TEMPLATES LOCATION". This will take you to an array that contains the templates, simply edit
  		or add as required.


  ----------------
  	14. Options to manipulate the Bootstrap sanitizer, and fix the sanitizer related breaking change in BS 3.4.x
  		BS 3.4.1 added a sanitizer to popover and tooltips - this breaking change strips non-whitelisted DOM elements from popover content, title etc.
  		See: https://getbootstrap.com/docs/3.4/javascript/#js-sanitizer and https://blog.getbootstrap.com/2019/02/13/bootstrap-4-3-1-and-3-4-1/

  		This Bootstrap change resulted in Tour navigation buttons being killed from the DOM: https://github.com/sorich87/bootstrap-tour/issues/723#issuecomment-471107788

  		This has been fixed in code, Tour navigation buttons now appear and work by default.

  		To prevent future similar reoccurrences, and also allow the manipulation of the sanitizer "allowed list" for Tours that want to add extra content into
  		tour steps, two features added to global options. To understand the purpose and operation of these features, review the following information on the Bootstrap
  		sanitizer: https://getbootstrap.com/docs/3.4/javascript/#js-sanitizer

  		--IMPORTANT NOTE-- SECURITY RISK: if you do not understand the purpose of the sanitizer, why it exists in bootstrap or how it relates to Tour, do not use these options.

  		Global options:

  			sanitizeWhitelist:	specify an object that will be merged with the Bootstrap Popover default whitelist. Use the same structure as the default Bootstrap
  								whitelist.

  			sanitizeFunction:	specify a function that will be used to sanitize Tour content, with the following signature: string function(content).
  								Specifying a function for this option will cause sanitizeWhitelist to be ignored.
  								Specifying anything other than a function for this option will be ignored, and sanitizeWhitelist will be used

  		Examples:

  			Allow tour step content to include a button with attributes data-someplugin1="..." and data-somethingelse="...". Allow content to include a selectpicker.
  				var Tour=new Tour({
  									steps: tourSteps,
  									sanitizeWhitelist:	{
  															"button"	: ["data-someplugin1", "data-somethingelse"],	// allows <button data-someplugin1="abc", data-somethingelse="xyz">
  															"select"	: []											// allows <select>
  														}
  								});


  			Use a custom whitelist function for sanitizing tour steps:
  				var Tour=new Tour({
  									steps: tourSteps,
  									sanitizeFunction:	function(stepContent)
  														{
  															// Bypass Bootstrap sanitizer using custom function to clean the tour step content.
  															// stepContent will contain the content of the step, i.e.: tourSteps[n].content. You must
  															// clean this content to prevent XSS and other vulnerabilities. Use your own code or a lib like DOMPurify
  															return DOMPurify.sanitize(stepContent);
  														}
  								});


  			Note: if you have complete control over the tour content (i.e.: no risk of XSS or similar attacks), you can use sanitizeFunction to bypass all sanitization
  				and use your step content exactly as is by simply returning the content:

  				var Tour=new Tour({
  									steps: tourSteps,
  									sanitizeFunction:	function(stepContent)
  														{
  															// POTENTIAL SECURITY RISK
  															// bypass Bootstrap sanitizer, perform no sanitization, tour step content will be exactly as templated in tourSteps.
  															return stepContent;
  														}
  								});

  ----------------
  	15. Change text for the buttons in the popup (also, preparation for future localization options)
  		With thanks to @vneri (https://github.com/IGreatlyDislikeJavascript/bootstrap-tourist/pull/8) for the original change
  		With thanks to @DancingDad, @thenewbeat, @bardware for the fixes/updates

  		You can now change the text displayed for the buttons used in the tour step popups.	For this, there is a new object you can pass to the options, called "localization".
  		This option only applies to the default templates. If you specify your own custom template, the localization.buttonTexts option has no effect on the basis that
  		you will make any changes to your own template directly.

  			var tour = new Tour({
  									framework: "bootstrap3",	// or "bootstrap4" depending on your version of bootstrap
  									steps:	[ .....	],
  									localization:
  									{
  										buttonTexts:	{
  															prevButton: 'Back',
  															nextButton: 'Go',
  															pauseButton: 'Wait',
  															resumeButton: 'Continue',
  															endTourButton: 'Ok, enough'
  														}
  									}
  								});

  		You may specify only the labels you want to change. Unspecified labels will remain at their defaults:

  			var tour = new Tour({
  									localization:
  									{
  										buttonTexts:	{
  															endTourButton: 'Adios muchachos'
  														}
  									}
  								});


   *
   */
  var document$1 = window.document;

  var Tour =
  /*#__PURE__*/
  function () {
    function Tour(options) {
      _classCallCheck(this, Tour);

      var storage = window.localStorage;
      this.objTemplates = {}; // CUSTOMIZABLE TEXTS FOR BUTTONS
      // set defaults. We could of course add this to the $.extend({..localization: {} ...}) directly below.
      // However this is configured here, prior to the $.extend of options below, to enable a potential
      // future option of loading localization externally perhaps using $.getScript() etc.
      //
      // Note that these only affect the "default" templates (see objTemplates in this func below). The assumption is
      // that if user creates a tour with a custom template, they will name the buttons as required. We could force the
      // naming even in custom templates by identifying buttons in templates with data-role="...", but it seems more logical
      // NOT to do that...
      //
      // Finally, it's simple to allow different localization/button texts per tour step. To do this, alter the $.extend in
      // Tour.prototype.getStep() and subsequent code to load the per-step localization, identify the buttons by data-role, and
      // make the appropriate changes. That seems like a very niche requirement so it's not implemented here.

      this.objTemplatesButtonTexts = {
        prevButton: "Prev",
        nextButton: "Next",
        pauseButton: "Pause",
        resumeButton: "Resume",
        endTourButton: "End Tour"
      }; // take default options and overwrite with this tour options

      this._options = $.extend(true, {
        name: 'tour',
        steps: [],
        container: 'body',
        autoscroll: true,
        keyboard: true,
        storage: storage,
        debug: false,
        backdrop: false,
        backdropContainer: 'body',
        backdropPadding: 0,
        redirect: true,
        orphan: false,
        duration: false,
        delay: false,
        basePath: '',
        template: null,
        localization: {
          buttonTexts: this.objTemplatesButtonTexts
        },
        framework: 'bootstrap3',
        sanitizeWhitelist: [],
        sanitizeFunction: null,
        // function(content) return sanitizedContent
        showProgressBar: true,
        showProgressText: true,
        getProgressBarHTML: null,
        //function(percent) {},
        getProgressTextHTML: null,
        //function(stepNumber, percent, stepCount) {},
        afterSetState: function afterSetState(key, value) {},
        afterGetState: function afterGetState(key, value) {},
        afterRemoveState: function afterRemoveState(key) {},
        onStart: function onStart(tour) {},
        onEnd: function onEnd(tour) {},
        onShow: function onShow(tour) {},
        onShown: function onShown(tour) {},
        onHide: function onHide(tour) {},
        onHidden: function onHidden(tour) {},
        onNext: function onNext(tour) {},
        onPrev: function onPrev(tour) {},
        onPause: function onPause(tour, duration) {},
        onResume: function onResume(tour, duration) {},
        onRedirectError: function onRedirectError(tour) {},
        onElementUnavailable: null,
        // function (tour, stepNumber) {},
        onPreviouslyEnded: null,
        // function (tour) {},
        onModalHidden: null // function(tour, stepNumber) {}

      }, options);

      if (this._options.framework !== "bootstrap3" && this._options.framework !== "bootstrap4") {
        this._debug('Invalid framework specified: ' + this._options.framework);

        throw "Bootstrap Tourist: Invalid framework specified";
      } // create the templates
      // SEARCH PLACEHOLDER: TEMPLATES LOCATION


      this.objTemplates = {
        bootstrap3: '<div class="popover" role="tooltip"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev">&laquo; ' + this._options.localization.buttonTexts.prevButton + '</button> <button class="btn btn-sm btn-default" data-role="next">' + this._options.localization.buttonTexts.nextButton + ' &raquo;</button> <button class="btn btn-sm btn-default" data-role="pause-resume" data-pause-text="' + this._options.localization.buttonTexts.pauseButton + '" data-resume-text="' + this._options.localization.buttonTexts.resumeButton + '">' + this._options.localization.buttonTexts.pauseButton + '</button> </div> <button class="btn btn-sm btn-default" data-role="end">' + this._options.localization.buttonTexts.endTourButton + '</button> </div> </div>',
        bootstrap4: '<div class="popover" role="tooltip"> <div class="arrow"></div> <h3 class="popover-header"></h3> <div class="popover-body"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-outline-secondary" data-role="prev">&laquo; ' + this._options.localization.buttonTexts.prevButton + '</button> <button class="btn btn-sm btn-outline-secondary" data-role="next">' + this._options.localization.buttonTexts.nextButton + ' &raquo;</button> <button class="btn btn-sm btn-outline-secondary" data-role="pause-resume" data-pause-text="' + this._options.localization.buttonTexts.pauseButton + '" data-resume-text="' + this._options.localization.buttonTexts.resumeButton + '">' + this._options.localization.buttonTexts.pauseButton + '</button> </div> <button class="btn btn-sm btn-outline-secondary" data-role="end">' + this._options.localization.buttonTexts.endTourButton + '</button> </div> </div>'
      }; // template option is default null. If not null after extend, caller has set a custom template, so don't touch it

      if (this._options.template === null) {
        // no custom template, so choose the template based on the framework
        if (this.objTemplates[this._options.framework] != null && this.objTemplates[this._options.framework] != undefined) {
          // there's a default template for the framework type specified in the options
          this._options.template = this.objTemplates[this._options.framework];

          this._debug('Using framework template: ' + this._options.framework);
        } else {
          this._debug('Warning: ' + this._options.framework + ' specified for template (no template option set), but framework is unknown. Tour will not work!');
        }
      } else {
        this._debug('Using custom template');
      }

      if (typeof this._options.sanitizeFunction == "function") {
        this._debug("Using custom sanitize function in place of bootstrap - security implications, be careful");
      } else {
        this._options.sanitizeFunction = null;

        this._debug("Extending Bootstrap sanitize options"); // no custom function, add our own
        // bootstrap 3.4.1 has whitelist functionality that strips tags from title, content etc of popovers and tooltips. Need to
        // add buttons to the whitelist otherwise the navigation buttons will be stripped from the popover content.
        // See issue: https://github.com/sorich87/bootstrap-tour/issues/723#issuecomment-471107788
        //
        // ** UPDATE: BS3 and BS4 have the whitelist function. However:
        //		BS3 uses $.fn.popover.Constructor.DEFAULTS.whiteList
        //		BS4 uses $.fn.popover.Constructor.Default.whiteList
        //	Even better, the CDN version of BS4 doesn't seem to include a whitelist property at all, which utterly screwed the first attempt at implementing
        // this, making it seem like my fix was working when in fact it was utterly broken.


        var defaultWhiteList = this._options.framework == "bootstrap4" && $.fn.popover.Constructor.Default.whiteList !== undefined ? $.fn.popover.Constructor.Default.whiteList : this._options.framework == "bootstrap3" && $.fn.popover.Constructor.DEFAULTS.whiteList !== undefined ? $.fn.popover.Constructor.DEFAULTS.whiteList : [];
        var whiteListAdditions = {
          "button": ["data-role", "style"],
          "img": ["style"],
          "div": ["style"]
        }; // whitelist is object with properties that are arrays. Need to merge "manually", as using $.extend with recursion will still overwrite the arrays . Try
        // var whiteList = $.extend(true, {}, defaultWhiteList, whiteListAdditions, this._options.sanitizeWhitelist);
        // and inspect the img property to see the issue - the default whitelist "src" (array elem 0) is overwritten with additions "style"
        // clone the default whitelist object first, otherwise we change the defaults for all of bootstrap!

        var whiteList = $.extend(true, {}, defaultWhiteList); // iterate the additions, and merge them into the defaults. We could just hammer them in manually but this is a little more expandable for the future

        $.each(whiteListAdditions, function (index, value) {
          whiteList[index] = whiteList[index] || [];
          $.merge(whiteList[index], value);
        }); // and now do the same with the user specified whitelist in tour options

        $.each(this._options.sanitizeWhitelist, function (index, value) {
          whiteList[index] = whiteList[index] || [];
          $.merge(whiteList[index], value);
        }); // save the merged whitelist back to the options, this is used by popover initialization when each step is shown

        this._options.sanitizeWhitelist = whiteList;
      }

      this._current = null;
      this.backdrops = [];
      return this;
    }

    _createClass(Tour, [{
      key: "addSteps",
      value: function addSteps(steps) {
        for (var _j = 0, len = steps.length; _j < len; _j++) {
          var step = steps[_j];
          this.addStep(step);
        }

        return this;
      }
    }, {
      key: "addStep",
      value: function addStep(step) {
        this._options.steps.push(step);

        return this;
      }
    }, {
      key: "getStepCount",
      value: function getStepCount() {
        return this._options.steps.length;
      }
    }, {
      key: "getStep",
      value: function getStep(i) {
        if (this._options.steps[i] != null) {
          if (typeof this._options.steps[i].element == "function") {
            this._options.steps[i].element = this._options.steps[i].element();
          } // Set per step options: take the global options then override with this step's options.


          this._options.steps[i] = $.extend(true, {
            id: "step-" + i,
            path: '',
            host: '',
            placement: 'right',
            title: '',
            content: '<p></p>',
            next: i === this._options.steps.length - 1 ? -1 : i + 1,
            prev: i - 1,
            animation: true,
            container: this._options.container,
            autoscroll: this._options.autoscroll,
            backdrop: this._options.backdrop,
            backdropContainer: this._options.backdropContainer,
            backdropPadding: this._options.backdropPadding,
            redirect: this._options.redirect,
            reflexElement: this._options.steps[i].element,
            preventInteraction: false,
            orphan: this._options.orphan,
            duration: this._options.duration,
            delay: this._options.delay,
            template: this._options.template,
            showProgressBar: this._options.showProgressBar,
            showProgressText: this._options.showProgressText,
            getProgressBarHTML: this._options.getProgressBarHTML,
            getProgressTextHTML: this._options.getProgressTextHTML,
            onShow: this._options.onShow,
            onShown: this._options.onShown,
            onHide: this._options.onHide,
            onHidden: this._options.onHidden,
            onNext: this._options.onNext,
            onPrev: this._options.onPrev,
            onPause: this._options.onPause,
            onResume: this._options.onResume,
            onRedirectError: this._options.onRedirectError,
            onElementUnavailable: this._options.onElementUnavailable,
            onModalHidden: this._options.onModalHidden,
            internalFlags: {
              elementModal: null,
              // will store the jq modal object for a step
              elementModalOriginal: null,
              // will store the original step.element string in steps that use a modal
              elementBootstrapSelectpicker: null // will store jq bootstrap select picker object

            }
          }, this._options.steps[i]);
          return this._options.steps[i];
        }
      }
    }, {
      key: "_setStepFlag",
      // step flags are used to remember specific internal step data across a tour
      value: function _setStepFlag(stepNumber, flagName, value) {
        if (this._options.steps[stepNumber] != null) {
          this._options.steps[stepNumber].internalFlags[flagName] = value;
        }
      }
    }, {
      key: "_getStepFlag",
      value: function _getStepFlag(stepNumber, flagName) {
        if (this._options.steps[stepNumber] != null) {
          return this._options.steps[stepNumber].internalFlags[flagName];
        }
      }
    }, {
      key: "init",
      //=======================================================================================================================================
      // Initiate tour and movement between steps
      value: function init() {
        console.log('You should remove Tour.init() from your code. It\'s not required with Bootstrap Tourist');
      }
    }, {
      key: "start",
      value: function start() {
        var _this = this;

        // Test if this tour has previously ended, and start() was called
        if (this.ended()) {
          if (this._options.onPreviouslyEnded != null && typeof this._options.onPreviouslyEnded == "function") {
            this._debug('Tour previously ended, exiting. Call tour.restart() to force restart. Firing onPreviouslyEnded()');

            this._options.onPreviouslyEnded(this);
          } else {
            this._debug('Tour previously ended, exiting. Call tour.restart() to force restart');
          }

          return this;
        } // Call setCurrentStep() without params to start the tour using whatever step is recorded in localstorage. If no step recorded, tour starts
        // from first step. This provides the "resume tour" functionality.
        // Tour restart() simply removes the step from local storage


        this.setCurrentStep();

        this._initMouseNavigation();

        this._initKeyboardNavigation(); // BS3: resize event must destroy and recreate both popper and background to ensure correct positioning
        // BS4: resize must destroy and recreate background, but popper.js handles popper positioning.
        // TODO: currently we destroy and recreate for both BS3 and BS4. Improvement could be to reposition backdrop overlay only when using BS4


        $(window).on("resize.tour-" + this._options.name, function () {
          _this.reshowCurrentStep();
        }); // Note: this call is not required, but remains here in case any future forkers want to reinstate the code that moves a non-orphan popover
        // when window is scrolled. Note that simply uncommenting this will not reinstate the code - _showPopoverAndOverlay automatically detects
        // if the current step is visible and will not reshow it. Therefore, to fully reinstate the "redraw on scroll" code, uncomment this and
        // also add appropriate code (to move popover & overlay) to the end of showPopover()
        //			this._onScroll((function (this)
        //							{
        //								return function ()
        //								{
        //									return this._showPopoverAndOverlay(this._current);
        //								};
        //							}
        //						));
        // start the tour - see if user provided onStart function, and if it returns a promise, obey that promise before calling showStep

        var promise = this._makePromise(this._options.onStart != null ? this._options.onStart(this) : Promise.resolve());

        this._callOnPromiseDone(promise, this.showStep, this._current);

        return this;
      }
    }, {
      key: "next",
      value: function next() {
        return this._callOnPromiseDone(this.hideStep(), this._showNextStep);
      }
    }, {
      key: "prev",
      value: function prev() {
        return this._callOnPromiseDone(this.hideStep(), this._showPrevStep);
      }
    }, {
      key: "goTo",
      value: function goTo(i) {
        this._debug("goTo step " + i);

        var promise = this.hideStep();
        return this._callOnPromiseDone(promise, this.showStep, i);
      }
    }, {
      key: "end",
      value: function end() {
        var _this2 = this;

        var promise = this.hideStep();
        return this._callOnPromiseDone(promise, function (e) {
          $(document$1).off("click.tour-" + _this2._options.name);
          $(document$1).off("keyup.tour-" + _this2._options.name);
          $(window).off("resize.tour-" + _this2._options.name);
          $(window).off("scroll.tour-" + _this2._options.name);

          _this2._setState('end', 'yes');

          _this2._clearTimer();

          if (_this2._options.onEnd != null) {
            return _this2._options.onEnd(_this2);
          }
        });
      }
    }, {
      key: "ended",
      value: function ended() {
        return this._getState('end') == 'yes';
      }
    }, {
      key: "restart",
      value: function restart() {
        this._removeState('current_step');

        this._removeState('end');

        this._removeState('redirect_to');

        return this.start();
      }
    }, {
      key: "pause",
      value: function pause() {
        var step = this.getStep(this._current);

        if (!(step && step.duration)) {
          return this;
        }

        this._paused = true;
        this._duration -= new Date().getTime() - this._start;
        window.clearTimeout(this._timer);

        this._debug("Paused/Stopped step " + (this._current + 1) + " timer (" + this._duration + " remaining).");

        if (step.onPause != null) {
          return step.onPause(this, this._duration);
        }
      }
    }, {
      key: "resume",
      value: function resume() {
        var _this3 = this;

        var step = this.getStep(this._current);

        if (!(step && step.duration)) {
          return this;
        }

        this._paused = false;
        this._start = new Date().getTime();
        this._duration = this._duration || step.duration;
        this._timer = window.setTimeout(function () {
          if (_this3._isLast()) {
            return _this3.next();
          } else {
            return _this3.end();
          }
        }, this._duration);

        this._debug("Started step " + (this._current + 1) + " timer with duration " + this._duration);

        if (step.onResume != null && this._duration !== step.duration) {
          return step.onResume(this, this._duration);
        }
      }
    }, {
      key: "reshowCurrentStep",
      // fully closes and reopens the current step, triggering all callbacks etc
      value: function reshowCurrentStep() {
        this._debug("Reshowing current step " + this.getCurrentStepIndex());

        var promise = this.hideStep();
        return this._callOnPromiseDone(promise, this.showStep, this._current);
      }
    }, {
      key: "hideStep",
      //=======================================================================================================================================
      // hides current step
      value: function hideStep() {
        var _this4 = this;

        var step = this.getStep(this.getCurrentStepIndex());

        if (!step) {
          return;
        }

        this._clearTimer();

        var promise = this._makePromise(step.onHide != null ? step.onHide(this, this.getCurrentStepIndex()) : null);

        var hideStepHelper = function hideStepHelper(e) {
          var $element = !($(step.element).data('bs.popover') || $(step.element).data('popover')) ? $('body') : $(step.element);

          if (_this4._options.framework == "bootstrap3") {
            $element.popover('destroy');
          }

          if (_this4._options.framework == "bootstrap4") {
            $element.popover('dispose');
          }

          $element.removeClass("tour-" + _this4._options.name + "-element tour-" + _this4._options.name + "-" + _this4.getCurrentStepIndex() + "-element").removeData('bs.popover');

          if (step.reflex) {
            $(step.reflexElement).removeClass('tour-step-element-reflex').off(_this4._reflexEvent(step.reflex) + ".tour-" + _this4._options.name);
          }

          _this4._hideOverlayElement(step);

          _this4._unfixBootstrapSelectPickerZindex(step); // If this step was pointed at a modal, revert changes to the step.element. See the notes in showStep for explanation


          var tmpModalOriginalElement = _this4._getStepFlag(_this4.getCurrentStepIndex(), "elementModalOriginal");

          if (tmpModalOriginalElement != null) {
            _this4._setStepFlag(_this4.getCurrentStepIndex(), "elementModalOriginal", null);

            step.element = tmpModalOriginalElement;
          }

          if (step.onHidden != null) {
            return step.onHidden(_this4);
          }
        };

        var hideDelay = step.delay.hide || step.delay;

        if (step.delay != false && hideDelay > 0) {
          this._debug("Wait " + hideDelay + " milliseconds to hide the step " + (this._current + 1));

          window.setTimeout(function () {
            return _this4._callOnPromiseDone(promise, hideStepHelper);
          }, hideDelay);
        } else {
          this._callOnPromiseDone(promise, hideStepHelper);
        }

        return promise;
      }
    }, {
      key: "showStep",
      // loads all required step info and prepares to show
      value: function showStep(i) {
        var _this5 = this;

        if (this.ended()) {
          // Note: see feature addition #12 and "onPreviouslyEnded" option to understand when this._options.onEnd is called vs this._options.onPreviouslyEnded()
          this._debug('Tour ended, showStep prevented.');

          if (this._options.onEnd != null) {
            this._options.onEnd(this);
          }

          return this;
        }

        var step = this.getStep(i);

        if (!step) {
          return;
        }

        var skipToPrevious = i < this._current;

        var promise = this._makePromise(step.onShow != null ? step.onShow(this, i) : null);

        this.setCurrentStep(i);

        var path = function () {
          if (LS.ld.isFunction(step.path)) {
            return step.path();
          }

          if (typeof step.path === "string") {
            return _this5._options.basePath + step.path;
          }

          return step.path;
        }();

        if (step.redirect && this._isRedirect(step.host, path, document$1.location)) {
          this._redirect(step, i, path);

          if (!this._isJustPathHashDifferent(step.host, path, document$1.location)) {
            return;
          }
        } // will be set to element <div class="modal"> if modal in use


        var $modalObject = null; // is element a modal?

        if (step.orphan === false && ($(step.element).hasClass("modal") || $(step.element).data('bs.modal'))) {
          // element is exactly the modal div
          $modalObject = $(step.element); // This is a hack solution. Original Tour uses step.element in multiple places and converts to jquery object as needed. This func uses $element,
          // but multiple other funcs simply use $(step.element) instead - keeping the original string element id in the step data and using jquery as needed.
          // This creates problems with dialogs, especially BootStrap Dialog plugin - in code terms, the dialog is everything from <div class="modal-dialog">,
          // but the actual visible positioned part of the dialog is <div class="modal-dialog"><div class="modal-content">. The tour must attach the popover to
          // modal-content div, NOT the modal-dialog div. But most coders + dialog plugins put the id on the modal-dialog div.
          // So for display, we must adjust the step element to point at modal-content under the modal-dialog div. However if we change the step.element
          // permanently to the modal-content (by changing tour._options.steps), this won't work if the step is reshown (plugin destroys modal, meaning
          // the element jq object is no longer valid) and could potentially screw up other
          // parts of a tour that have dialogs. So instead we record the original element used for this step that involves modals, change the step.element
          // to the modal-content div, then set it back when the step is hidden again.
          //
          // This is ONLY done because it's too difficult to unpick all the original tour code that uses step.element directly.

          this._setStepFlag(this.getCurrentStepIndex(), "elementModalOriginal", step.element); // fix the tour element, the actual visible offset comes from modal > modal-dialog > modal-content and step.element is used to calc this offset & size


          step.element = $(step.element).find(".modal-content:first");
        }

        var $element = $(step.element); // is element inside a modal?

        if ($modalObject === null && $element.parents(".modal:first").length) {
          // find the parent modal div
          $modalObject = $element.parents(".modal:first");
        }

        if ($modalObject && $modalObject.length > 0) {
          this._debug("Modal identified, onModalHidden callback available"); // store the modal element for other calls


          this._setStepFlag(i, "elementModal", $modalObject); // modal in use, add callback


          var funcModalHelper = function funcModalHelper() {
            _this5._debug("Modal close triggered");

            if (typeof step.onModalHidden == "function") {
              // if step onModalHidden returns false, do nothing. returns int, move to the step specified.
              // Otherwise continue regular next/end functionality
              var rslt = step.onModalHidden(_this5, i);

              if (rslt === false) {
                _this5._debug("onModalHidden returned exactly false, tour step unchanged");

                return;
              }

              if (Number.isInteger(rslt)) {
                _this5._debug("onModalHidden returned int, tour moving to step " + rslt + 1);

                $_modalObject.off("hidden.bs.modal", funcModalHelper);
                return _this5.goTo(rslt);
              }

              _this5._debug("onModalHidden did not return false or int, continuing tour");
            }

            $_modalObject.off("hidden.bs.modal", funcModalHelper);
            return _this5._isLast() ? _this5.next() : _this5.end();
          };

          $modalObject.off("hidden.bs.modal", funcModalHelper).on("hidden.bs.modal", funcModalHelper);
        } // Helper function to actually show the popover using _showPopoverAndOverlay


        var showStepHelper = function showStepHelper(e) {
          if (_this5._isOrphan(step)) {
            if (step.orphan === false) {
              _this5._debug("Skip the orphan step " + (_this5._current + 1) + ".\nOrphan option is false and the element " + step.element + " does not exist or is hidden.");

              if (typeof step.onElementUnavailable == "function") {
                _this5._debug("Calling onElementUnavailable callback");

                step.onElementUnavailable(_this5, _this5._current);
              }

              if (skipToPrevious) {
                _this5._showPrevStep(true);
              } else {
                _this5._showNextStep(true);
              }

              return;
            }

            _this5._debug("Show the orphan step " + (_this5._current + 1) + ". Orphans option is true.");
          } //console.log(step);


          if (step.autoscroll && !_this5._isOrphan(step)) {
            _this5._scrollIntoView(i);
          } else {
            _this5._showPopoverAndOverlay(i);
          }

          if (step.duration) {
            return _this5.resume();
          }
        }; // delay in millisec specified in step options


        var showDelay = step.delay.show || step.delay;

        if ({}.toString.call(showDelay) === '[object Number]' && showDelay > 0) {
          this._debug("Wait " + showDelay + " milliseconds to show the step " + (this._current + 1));

          window.setTimeout(function () {
            return _this5._callOnPromiseDone(promise, showStepHelper);
          }, showDelay);
        } else {
          if (step.delayOnElement) {
            // delay by element existence or max delay (default 2 sec)
            var revalidateDelayElement = function revalidateDelayElement() {
              if (typeof step.delayOnElement.delayElement == "function") return step.delayOnElement.delayElement();else if (step.delayOnElement.delayElement == "element") return $(step.element);else return $(step.delayOnElement.delayElement);
            };

            var getElementForLogging = function getElementForLogging($element) {
              if ($delayElement.length > 0) {
                return $delayElement.reduce(function (col, el) {
                  return col.push(el.tagName);
                }, []).join(', ');
              } else {
                return $delayElement.prop('tagName');
              }
            };

            var $delayElement = revalidateDelayElement();
            var delayElementLog = getElementForLogging($delayElement);
            var delayMax = step.delayOnElement.maxDelay ? step.delayOnElement.maxDelay : 2000; //	set max delay to greater than default interval check for element appearance

            delayMax = delayMax < 250 ? 251 : delayMax;

            this._debug("Wait for element " + delayElementLog + " visible or max " + delayMax + " milliseconds to show the step " + (this._current + 1));

            var delayFunc = window.setInterval(function () {
              _this5._debug("Wait for element " + delayElementLog + ": checking...");

              if ($delayElement.length === 0) {
                $delayElement = revalidateDelayElement();
              }

              if ($delayElement.is(':visible')) {
                _this5._debug("Wait for element " + delayElementLog + ": found, showing step");

                window.clearInterval(delayFunc);
                delayFunc = (_readOnlyError("delayFunc"), null);
                return _this5._callOnPromiseDone(promise, showStepHelper);
              }
            }, 250); // Set timer to kill the setInterval call after max delay time expires

            window.setTimeout(function () {
              if (delayFunc) {
                _this5._debug("Wait for element " + delayElementLog + ": max timeout reached without element found");

                window.clearInterval(delayFunc); // showStepHelper will handle broken/missing/invisible element

                return _this5._callOnPromiseDone(promise, showStepHelper);
              }
            }, delayMax);
          } else {
            // no delay by milliseconds or delay by time
            this._callOnPromiseDone(promise, showStepHelper);
          }
        }

        return promise;
      }
    }, {
      key: "getCurrentStepIndex",
      value: function getCurrentStepIndex() {
        return this._current;
      }
    }, {
      key: "setCurrentStep",
      value: function setCurrentStep(value) {
        if (value != null) {
          this._current = value;

          this._setState('current_step', value);
        } else {
          this._current = this._getState('current_step');
          this._current = this._current === null ? 0 : parseInt(this._current, 10);
        }

        return this;
      }
    }, {
      key: "_setState",
      value: function _setState(key, value) {
        if (this._options.storage) {
          var keyName = this._options.name + "_" + key;

          try {
            this._options.storage.setItem(keyName, value);
          } catch (error) {
            if (error.code === DOMException.QUOTA_EXCEEDED_ERR) {
              this._debug('LocalStorage quota exceeded. State storage failed.');
            }
          }

          return this._options.afterSetState(keyName, value);
        } else {
          if (this._state == null) {
            this._state = {};
          }

          return this._state[key] = value;
        }
      }
    }, {
      key: "_removeState",
      value: function _removeState(key) {
        if (this._options.storage) {
          var keyName = this._options.name + "_" + key;

          this._options.storage.removeItem(keyName);

          return this._options.afterRemoveState(keyName);
        } else {
          if (this._state != null) {
            return delete this._state[key];
          }
        }
      }
    }, {
      key: "_getState",
      value: function _getState(key) {
        var value;

        if (this._options.storage) {
          var keyName = this._options.name + "_" + key;
          value = this._options.storage.getItem(keyName);
        } else {
          if (this._state != null) {
            value = this._state[key];
          }
        }

        if (value === null || value === 'null') {
          value = null;
        }

        this._options.afterGetState(key, value);

        return value;
      }
    }, {
      key: "_showNextStep",
      value: function _showNextStep(skipOrphan) {
        var _this6 = this;

        skipOrphan = skipOrphan || false;

        var showNextStepHelper = function showNextStepHelper(e) {
          return _this6.showStep(_this6._current + 1);
        };

        var step = this.getStep(this._current);
        var promise = Promise.resolve(); // only call the onNext handler if this is a click and NOT an orphan skip due to missing element

        if (skipOrphan === false && step.onNext != null) {
          var rslt = step.onNext(this);

          this._debug("Current steps onNext failed. Check onNext -> ", step.onNext);

          this._debug("Current result: ", rslt);

          if (rslt === false) {
            this._debug("onNext callback returned false, preventing move to next step");

            return this.showStep(this._current);
          }

          promise = this._makePromise(rslt);
        }

        return this._callOnPromiseDone(promise, showNextStepHelper);
      }
    }, {
      key: "_showPrevStep",
      value: function _showPrevStep(skipOrphan) {
        var _this7 = this;

        skipOrphan = skipOrphan || false;

        var showPrevStepHelper = function showPrevStepHelper(e) {
          return _this7.showStep(step.prev);
        };

        var step = this.getStep(this._current);
        var promise = Promise.resolve(); // only call the onPrev handler if this is a click and NOT an orphan skip due to missing element

        if (skipOrphan === false && step.onPrev != null) {
          var rslt = step.onPrev(this);

          if (rslt === false) {
            this._debug("onPrev callback returned false, preventing move to previous step");

            return this.showStep(this._current);
          }

          promise = this._makePromise(rslt);
        }

        return this._callOnPromiseDone(promise, showPrevStepHelper);
      }
    }, {
      key: "_debug",
      value: function _debug() {
        if (this._options.debug) {
          var _window$console$log;

          return (_window$console$log = window.console.log).call.apply(_window$console$log, [this, "[ Bootstrap Tour: '" + this._options.name + "' ] "].concat(Array.prototype.slice.call(arguments)));
        }
      }
    }, {
      key: "_isRedirect",
      value: function _isRedirect(host, path, location) {
        if (host != null && host !== '' && ({}.toString.call(host) === '[object RegExp]' && !host.test(location.origin) || {}.toString.call(host) === '[object String]' && this._isHostDifferent(host, location))) {
          return true;
        }

        var currentPath = [location.pathname, location.search, location.hash].join('');
        return path != null && path !== '' && ({}.toString.call(path) === '[object RegExp]' && !path.test(currentPath) || {}.toString.call(path) === '[object String]' && this._isPathDifferent(path, currentPath));
      }
    }, {
      key: "_isHostDifferent",
      value: function _isHostDifferent(host, location) {
        switch ({}.toString.call(host)) {
          case '[object RegExp]':
            return !host.test(location.origin);

          case '[object String]':
            return this._getProtocol(host) !== this._getProtocol(location.href) || this._getHost(host) !== this._getHost(location.href);

          default:
            return true;
        }
      }
    }, {
      key: "_isPathDifferent",
      value: function _isPathDifferent(path, currentPath) {
        return this._getPath(path) !== this._getPath(currentPath) || !this._equal(this._getQuery(path), this._getQuery(currentPath)) || !this._equal(this._getHash(path), this._getHash(currentPath));
      }
    }, {
      key: "_isJustPathHashDifferent",
      value: function _isJustPathHashDifferent(host, path, location) {
        if (host != null && host !== '') {
          if (this._isHostDifferent(host, location)) {
            return false;
          }
        }

        var currentPath = [location.pathname, location.search, location.hash].join('');

        if ({}.toString.call(path) === '[object String]') {
          return this._getPath(path) === this._getPath(currentPath) && this._equal(this._getQuery(path), this._getQuery(currentPath)) && !this._equal(this._getHash(path), this._getHash(currentPath));
        }

        return false;
      }
    }, {
      key: "_redirect",
      value: function _redirect(step, i, path) {
        if (LS.ld.isFunction(step.redirect)) {
          return step.redirect.call(this, path);
        } else {
          var href = {}.toString.call(step.host) === '[object String]' ? "" + step.host + path : path;

          this._debug("Redirect to " + href);

          if (this._getState('redirect_to') === "" + i) {
            this._debug("Error redirection loop to " + path);

            this._removeState('redirect_to');

            if (step.onRedirectError != null) {
              return step.onRedirectError(this);
            }
          } else {
            this._setState('redirect_to', "" + i);

            return document$1.location.href = href;
          }
        }
      }
    }, {
      key: "_isOrphan",
      // Tests if the step is orphan
      // Step can be "orphan" (unattached to any element) if specifically set as such in tour step options, or with an invalid/hidden element
      value: function _isOrphan(step) {
        var isOrphan = step.orphan == true || step.element == null || !$(step.element).length || $(step.element).is(':hidden') && $(step.element)[0].namespaceURI !== 'http://www.w3.org/2000/svg';

        this._debug("It seems this element is an orphan -> ", step);

        return isOrphan;
      }
    }, {
      key: "_isLast",
      value: function _isLast() {
        return this._current < this._options.steps.length - 1;
      }
    }, {
      key: "_showPopoverAndOverlay",
      // wraps the calls to show the tour step in a popover and the background overlay.
      // Note this is ALSO called by scroll event handler. Individual funcs called will determine whether redraws etc are required.
      value: function _showPopoverAndOverlay(i) {
        if (this.getCurrentStepIndex() !== i || this.ended()) {
          return;
        }

        var step = this.getStep(i);

        if (step.backdrop) {
          this._showOverlayElements(step);
        }

        this._fixBootstrapSelectPickerZindex(step); // Ensure this is called last, to allow preceeding calls to check whether current step popover is already visible.
        // This is required because this func is called by scroll event. showPopover creates the actual popover with
        // current step index as a class. Therefore all preceeding funcs can check if they are being called because of a
        // scroll event (popover class using current step index exists), or because of a step change (class doesn't exist).


        this._showPopover(step, i);

        if (step.onShown != null) {
          step.onShown(this);
        }

        return this;
      }
    }, {
      key: "_showPopover",
      // handles view of popover
      value: function _showPopover(step, i) {
        var _this8 = this;

        var isOrphan = this._isOrphan(step); // is this step already visible? _showPopover is called by _showPopoverAndOverlay, which is called by window scroll event. This
        // check prevents the continual flickering of the current tour step - original approach reloaded the popover every scroll event.
        // Why is this check here and not in _showPopoverAndOverlay? This allows us to selectively redraw elements on scroll.


        if ($(document$1).find(".popover.tour-" + this._options.name + ".tour-" + this._options.name + "-" + this.getCurrentStepIndex()).length == 0) {
          // Step not visible, draw first time
          $(".tour-" + this._options.name).remove();
          step.template = this._template(step, i);

          if (isOrphan) {
            // Note: BS4 popper.js requires additional fiddling to work, see below where popOpts object is created
            step.element = 'body';
            step.placement = 'top';
          }

          var $element = $(step.element);
          $element.addClass("tour-" + this._options.name + "-element tour-" + this._options.name + "-" + i + "-element");

          if (step.reflex && !isOrphan) {
            $(step.reflexElement).addClass('tour-step-element-reflex').off(this._reflexEvent(step.reflex) + ".tour-" + this._options.name).on(this._reflexEvent(step.reflex) + ".tour-" + this._options.name, function (e) {
              if (_this8._isLast()) {
                return _this8.next();
              } else {
                return _this8.end();
              }
            });
          }

          var percentProgress = parseInt((i + 1) / this.getStepCount() * 100);
          var title = step.title;
          var content = step.content;

          if (step.showProgressBar) {
            if (typeof step.getProgressBarHTML == "function") {
              content = step.getProgressBarHTML(percentProgress) + content;
            } else {
              content = '<div class="progress"><div class="progress-bar progress-bar-striped" role="progressbar" style="width: ' + percentProgress + '%;"></div></div>' + content;
            }
          }

          if (step.showProgressText) {
            if (typeof step.getProgressTextHTML == "function") {
              title += step.getProgressTextHTML(i, percentProgress, this.getStepCount());
            } else {
              if (this._options.framework == "bootstrap3") {
                title += '<span class="pull-left">' + (i + 1) + '/' + this.getStepCount() + '</span>';
              }

              if (this._options.framework == "bootstrap4") {
                title += '<span class="float-left">' + (i + 1) + '/' + this.getStepCount() + '</span>';
              }
            }
          } // Tourist v0.10 - split popOpts out of bootstrap popper instantiation due to BS3 / BS4 diverging requirements


          var popOpts = {
            placement: step.placement,
            // When auto is specified, it will dynamically reorient the popover.
            trigger: 'manual',
            title: title,
            content: content,
            html: true,
            //sanitize: false, // turns off all bootstrap sanitization of popover content, only use in last resort case - use whiteListAdditions instead!
            whiteList: this._options.sanitizeWhitelist,
            // ignored if sanitizeFn is specified
            sanitizeFn: this._options.sanitizeFunction,
            animation: step.animation,
            container: step.container,
            template: step.template,
            selector: step.element //boundary: "viewport", // added for BS4 popper testing. Do not enable, creates visible jump on orphan step scroll to bottom

          };

          if (this._options.framework == "bootstrap4") {
            if (isOrphan) {
              // BS4 uses popper.js, which doesn't have a method of fixing the popper to the center of the viewport without an element. However
              // BS4 wrapper does some extra funky stuff that means we can't just replace the BS4 popper init code. Instead, fudge the popper
              // using the offset feature, which params don't seem to be documented properly!
              popOpts.offset = function (obj) {
                //console.log(obj);
                var top = Math.max(0, ($(window).height() - obj.popper.height) / 2);
                var left = Math.max(0, ($(window).width() - obj.popper.width) / 2);
                obj.popper.position = "fixed";
                obj.popper.top = top;
                obj.popper.bottom = top + obj.popper.height;
                obj.popper.left = left;
                obj.popper.right = top + obj.popper.width;
                return obj;
              };
            } else {
              // BS3 popover accepts jq object or string literal. BS4 popper.js of course doesn't, just to make life extra irritating.
              popOpts.selector = "#" + step.element[0].id;
            }
          } // BS4 / popper.js does not accept a jquery object as element. BS3 popover does!


          if (this._options.framework == "bootstrap4" && isOrphan == false) {
            popOpts.selector = "#" + step.element[0].id;
          }

          $element.popover(popOpts);
          $element.popover('show');
          var $tip = null;

          if (this._options.framework == "bootstrap3") {
            $tip = $element.data('bs.popover') ? $element.data('bs.popover').tip() : $element.data('popover').tip(); // For BS3 only. BS4 popper.js reverts this change

            if ($element.css('position') === 'fixed') {
              $tip.css('position', 'fixed');
            }

            if (isOrphan) {
              this._center($tip);

              $tip.css('position', 'fixed');
            } else {
              this._reposition($tip, step);
            }
          }

          if (this._options.framework == "bootstrap4") {
            $tip = $($element.data('bs.popover') ? $element.data('bs.popover').getTipElement() : $element.data('popover').getTipElement());
          }

          $tip.attr('id', step.id);

          this._debug("Step " + (this._current + 1) + " of " + this._options.steps.length);
        }
      }
    }, {
      key: "_template",
      value: function _template(step, i) {
        var template = this._isOrphan(step) && !LS.ld.isBoolean(step.orphan) ? template = (_readOnlyError("template"), step.orphan) : step.template;
        var $template = $.isFunction(template) ? $(template(i, step)) : $(template);
        var $navigation = $template.find('.popover-navigation');
        var $prev = $navigation.find('[data-role="prev"]');
        var $next = $navigation.find('[data-role="next"]');
        var $resume = $navigation.find('[data-role="pause-resume"]');

        if (this._isOrphan(step)) {
          $template.addClass('orphan');
        }

        $template.addClass("tour-" + this._options.name + " tour-" + this._options.name + "-" + i);

        if (step.reflex) {
          $template.addClass("tour-" + this._options.name + "-reflex");
        }

        if (step.prev < 0) {
          $prev.addClass('disabled').prop('disabled', true).prop('tabindex', -1);
        }

        if (step.next < 0) {
          $next.addClass('disabled').prop('disabled', true).prop('tabindex', -1);
        }

        if (step.reflexOnly) {
          $next.hide();
        }

        if (!step.duration) {
          $resume.remove();
        }

        return $template.clone().wrap('<div>').parent().html();
      }
    }, {
      key: "_reflexEvent",
      value: function _reflexEvent(reflex) {
        return LS.ld.isBoolean(reflex) ? 'click' : reflex;
      }
    }, {
      key: "_reposition",
      value: function _reposition($tip, step) {
        var offsetWidth = $tip[0].offsetWidth;
        var offsetHeight = $tip[0].offsetHeight;
        var tipOffset = $tip.offset();
        var originalLeft = tipOffset.left;
        var originalTop = tipOffset.top;
        var offsetBottom = $(document$1).height() - tipOffset.top - $tip.outerHeight();

        if (offsetBottom < 0) {
          tipOffset.top = tipOffset.top + offsetBottom;
        }

        var offsetRight = $('html').outerWidth() - tipOffset.left - $tip.outerWidth();

        if (offsetRight < 0) {
          tipOffset.left = tipOffset.left + offsetRight;
        }

        if (tipOffset.top < 0) {
          tipOffset.top = 0;
        }

        if (tipOffset.left < 0) {
          tipOffset.left = 0;
        }

        $tip.offset(tipOffset);

        if (step.placement === 'bottom' || step.placement === 'top') {
          if (originalLeft !== tipOffset.left) {
            return this._replaceArrow($tip, (tipOffset.left - originalLeft) * 2, offsetWidth, 'left');
          }
        } else {
          if (originalTop !== tipOffset.top) {
            return this._replaceArrow($tip, (tipOffset.top - originalTop) * 2, offsetHeight, 'top');
          }
        }
      }
    }, {
      key: "_center",
      value: function _center($tip) {
        $tip.css('top', $(window).outerHeight() / 2 - $tip.outerHeight() / 2);
        return $tip.css('left', $(window).outerWidth() / 2 - $tip.outerWidth() / 2);
      }
    }, {
      key: "_replaceArrow",
      value: function _replaceArrow($tip, delta, dimension, position) {
        return $tip.find('.arrow').css(position, delta ? 50 * (1 - delta / dimension) + '%' : '');
      }
    }, {
      key: "_scrollIntoView",
      value: function _scrollIntoView(i) {
        var _this9 = this;

        var step = this.getStep(i);
        var $element = $(step.element);

        if (this._isOrphan(step)) {
          // If this is an orphan step, don't auto-scroll. Orphan steps are now css fixed to center of window
          return this._showPopoverAndOverlay(i);
        }

        if (!$element.length) {
          return this._showPopoverAndOverlay(i);
        }

        var $window = $(window);
        var offsetTop = $element.offset().top;
        var height = $element.outerHeight();
        var windowHeight = $window.height();
        var scrollTop = 0;

        switch (step.placement) {
          case 'top':
            scrollTop = Math.max(0, offsetTop - windowHeight / 2);
            break;

          case 'left':
          case 'right':
            scrollTop = Math.max(0, offsetTop + height / 2 - windowHeight / 2);
            break;

          case 'bottom':
            scrollTop = Math.max(0, offsetTop + height - windowHeight / 2);
        }

        this._debug("Scroll into view. ScrollTop: " + scrollTop + ". Element offset: " + offsetTop + ". Window height: " + windowHeight + ".");

        var counter = 0;
        return $('body, html').stop(true, true).animate({
          scrollTop: Math.ceil(scrollTop)
        }, function () {
          if (++counter === 2) {
            _this9._showPopoverAndOverlay(i);

            return _this9._debug("Scroll into view.\nAnimation end element offset: " + $element.offset().top + ".\nWindow height: " + $window.height() + ".");
          }
        });
      }
    }, {
      key: "_onScroll",
      // Note: this method is not required, but remains here in case any future forkers want to reinstate the code that moves a non-orphan popover
      // when window is scrolled
      value: function _onScroll(callback, timeout) {
        return $(window).on("scroll.tour-" + this._options.name, function () {
          clearTimeout(timeout);
          return timeout = setTimeout(callback, 100);
        });
      }
    }, {
      key: "_initMouseNavigation",
      value: function _initMouseNavigation() {
        var _this10 = this;

        return $(document$1).off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='prev']").off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='next']").off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='end']").off("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='pause-resume']").on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='next']", function (e) {
          e.preventDefault();
          return _this10.next();
        }).on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='prev']", function (e) {
          e.preventDefault();

          if (_this10._current > 0) {
            return _this10.prev();
          }
        }).on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='end']", function (e) {
          e.preventDefault();
          return _this10.end();
        }).on("click.tour-" + this._options.name, ".popover.tour-" + this._options.name + " *[data-role='pause-resume']", function (e) {
          e.preventDefault();
          $this = $(e.target);
          $this.text(_this10._paused ? $this.data('pause-text') : $this.data('resume-text'));

          if (_this10._paused) {
            return _this10.resume();
          } else {
            return _this10.pause();
          }
        });
      }
    }, {
      key: "_initKeyboardNavigation",
      value: function _initKeyboardNavigation() {
        var _this11 = this;

        if (!this._options.keyboard) {
          return;
        }

        return $(document$1).on("keyup.tour-" + this._options.name, function (e) {
          if (!e.which) {
            return;
          }

          switch (e.which) {
            case 39:
              e.preventDefault();

              if (_this11._isLast()) {
                return _this11.next();
              } else {
                return _this11.end();
              }

              break;

            case 37:
              e.preventDefault();

              if (_this11._current > 0) {
                return _this11.prev();
              }

          }
        });
      }
    }, {
      key: "_makePromise",
      // If param is a promise, returns the promise back to the caller. Otherwise returns null.
      // Only purpose is to make calls to _callOnPromiseDone() simple - first param of _callOnPromiseDone()
      // accepts either null or a promise to smart call either promise or straight callback. This
      // pair of funcs therefore allows easy integration of user code to return callbacks or promises
      value: function _makePromise(possiblePromise) {
        if (possiblePromise && $.isFunction(possiblePromise.then)) {
          return possiblePromise;
        } else {
          return Promise.resolve();
        }
      }
    }, {
      key: "_callOnPromiseDone",
      // Creates a promise wrapping the callback if valid promise is provided as first arg. If
      // first arg is not a promise, simply uses direct function call of callback.
      value: function _callOnPromiseDone(promise, callback, arg) {
        var _this12 = this;

        if (promise) {
          return promise.then(function () {
            return callback.call(_this12, arg);
          });
        } else {
          return callback.call(this, arg);
        }
      }
    }, {
      key: "_fixBootstrapSelectPickerZindex",
      // Bootstrap Select custom draws the drop down, force the Z index between Tour overlay and popoper
      value: function _fixBootstrapSelectPickerZindex(step) {
        if (this._isOrphan(step)) {
          // If it's an orphan step, it can't be a selectpicker element
          return;
        } // is the current step already visible?


        if ($(document$1).find(".popover.tour-" + this._options.name + ".tour-" + this._options.name + "-" + this.getCurrentStepIndex()).length != 0) {
          // don't waste time redoing the fix
          return;
        } // is this element or child of this element a selectpicker


        var $selectpicker = $(step.element)[0].tagName.toLowerCase() == "select" ? $(step.element) : $(step.element).find("select:first"); // is this selectpicker a bootstrap-select: https://github.com/snapappointments/bootstrap-select/

        if ($selectpicker.length > 0 && $selectpicker.parent().hasClass("bootstrap-select")) {
          this._debug("Fixing Bootstrap SelectPicker"); // set zindex to open dropdown over background element


          $selectpicker.parent().css("z-index", "1101"); // store the element for other calls. Mainly for when step is hidden, selectpicker must be unfixed / z index reverted to avoid visual issues.
          // storing element means we don't need to find it again later

          this._setStepFlag(this.getCurrentStepIndex(), "elementBootstrapSelectpicker", $selectpicker);
        }
      } // Revert the Z index between Tour overlay and popoper

    }, {
      key: "_unfixBootstrapSelectPickerZindex",
      value: function _unfixBootstrapSelectPickerZindex(step) {
        var $selectpicker = this._getStepFlag(this.getCurrentStepIndex(), "elementBootstrapSelectpicker");

        if ($selectpicker) {
          this._debug("Unfixing Bootstrap SelectPicker"); // set zindex to open dropdown over background element


          $selectpicker.parent().css("z-index", "auto");
        }
      } // Shows the preventInteraction div, and the background divs

    }, {
      key: "_showOverlayElements",
      value: function _showOverlayElements(step) {
        // check if the popover for the current step already exists (is this a redraw)
        var isRedraw = $(document$1).find(".popover.tour-" + this._options.name + ".tour-" + this._options.name + "-" + this.getCurrentStepIndex()).length == 0;

        if (step.preventInteraction && !isRedraw) {
          $(step.backdropContainer).append("<div class='tour-prevent' id='tourPrevent'></div>");
          $("#tourPrevent").width($(step.element).outerWidth());
          $("#tourPrevent").height($(step.element).outerHeight());
          $("#tourPrevent").offset($(step.element).offset());
        }

        var docHeight = $(document$1).height();
        var docWidth = $(document$1).width();

        if ($(step.element).length === 0 || this._isOrphan(step)) {
          var $backdrop = $('<div class="tour-backdrop tour-backdrop-orphan"></div>');
          $backdrop.offset({
            top: 0,
            left: 0
          });
          $backdrop.width(docWidth);
          $backdrop.height(docHeight);
          $("body").append($backdrop);
        } else {
          var elementData = {
            width: $(step.element).innerWidth(),
            height: $(step.element).innerHeight(),
            offset: $(step.element).offset()
          };

          if (step.backdropPadding) {
            elementData = (_readOnlyError("elementData"), this._applyBackdropPadding(step.backdropPadding, elementData));
          }

          var $backdropTop = $('<div class="tour-backdrop top"></div>');
          $backdropTop.offset({
            top: 0,
            left: 0
          });
          $backdropTop.width(docWidth);
          $backdropTop.height(elementData.offset.top);
          var $backdropLeft = $('<div class="tour-backdrop left"></div>');
          $backdropLeft.width(elementData.offset.left);
          $backdropLeft.height(elementData.height);
          $backdropLeft.offset({
            top: elementData.offset.top,
            left: 0
          });
          var $backdropRight = $('<div class="tour-backdrop right"></div>');
          $backdropRight.width(docWidth - (elementData.width + elementData.offset.left));
          $backdropRight.height(elementData.height);
          $backdropRight.offset({
            top: elementData.offset.top,
            left: elementData.offset.left + elementData.width
          });
          var $backdropBottom = $('<div class="tour-backdrop bottom"></div>');
          $backdropBottom.width(docWidth);
          $backdropBottom.height(docHeight - elementData.offset.top - elementData.height);
          $backdropBottom.offset({
            top: elementData.offset.top + elementData.height,
            left: 0
          });
          $(step.backdropContainer).append($backdropTop);
          $(step.backdropContainer).append($backdropLeft);
          $(step.backdropContainer).append($backdropRight);
          $(step.backdropContainer).append($backdropBottom);
        }
      }
    }, {
      key: "_hideOverlayElement",
      value: function _hideOverlayElement(step) {
        // remove any previous interaction overlay
        if ($("#tourPrevent").length) {
          $("#tourPrevent").remove();
        }

        $(".tour-backdrop").remove();
      }
    }, {
      key: "_applyBackdropPadding",
      value: function _applyBackdropPadding(padding, data) {
        if (_typeof(padding) === 'object') {
          if (padding.top == null) {
            padding.top = 0;
          }

          if (padding.right == null) {
            padding.right = 0;
          }

          if (padding.bottom == null) {
            padding.bottom = 0;
          }

          if (padding.left == null) {
            padding.left = 0;
          }

          data.offset.top = data.offset.top - padding.top;
          data.offset.left = data.offset.left - padding.left;
          data.width = data.width + padding.left + padding.right;
          data.height = data.height + padding.top + padding.bottom;
        } else {
          data.offset.top = data.offset.top - padding;
          data.offset.left = data.offset.left - padding;
          data.width = data.width + padding * 2;
          data.height = data.height + padding * 2;
        }

        return data;
      }
    }, {
      key: "_clearTimer",
      value: function _clearTimer() {
        window.clearTimeout(this._timer);
        this._timer = null;
        return this._duration = null;
      }
    }, {
      key: "_getProtocol",
      // =============================================================================================================================
      value: function _getProtocol(url) {
        url = url.split('://');

        if (url.length > 1) {
          return url[0];
        } else {
          return 'http';
        }
      }
    }, {
      key: "_getHost",
      value: function _getHost(url) {
        url = url.split('//');
        url = url.length > 1 ? url[1] : url[0];
        return url.split('/')[0];
      }
    }, {
      key: "_getPath",
      value: function _getPath(path) {
        return path.replace(/\/?$/, '').split('?')[0].split('#')[0];
      }
    }, {
      key: "_getQuery",
      value: function _getQuery(path) {
        return this._getParams(path, '?');
      }
    }, {
      key: "_getHash",
      value: function _getHash(path) {
        return this._getParams(path, '#');
      }
    }, {
      key: "_getParams",
      value: function _getParams(path, start) {
        var params = path.split(start);

        if (params.length === 1) {
          return {};
        }

        params = params[1].split('&');
        var paramsObject = {};

        for (var _j2 = 0, len = params.length; _j2 < len; _j2++) {
          var param = params[_j2];
          param = param.split('=');
          paramsObject[param[0]] = param[1] || '';
        }

        return paramsObject;
      }
    }, {
      key: "_equal",
      value: function _equal(obj1, obj2) {
        if (LS.ld.isObject(obj1) && LS.ld.isObject(obj2)) {
          var obj1Keys = Object.keys(obj1);
          var obj2Keys = Object.keys(obj2);

          if (obj1Keys.length !== obj2Keys.length) {
            return false;
          }

          for (var k in obj1) {
            var v = obj1[k];

            if (!this._equal(obj2[k], v)) {
              return false;
            }
          }

          return true;
        } else if (LS.ld.isArray(obj1) && LS.ld.isArray(obj2)) {
          if (obj1.length !== obj2.length) {
            return false;
          }

          for (var _k = j = 0, len = obj1.length; j < len; _k = ++j) {
            var _v = obj1[_k];

            if (!this._equal(_v, obj2[_k])) {
              return false;
            }
          }

          return true;
        } else {
          return obj1 === obj2;
        }
      }
    }]);

    return Tour;
  }();

  var globalTourObject = function globalTourObject() {
    var getBasedUrls = /(\/index.php)?\?r=admin/.test(window.location.href),
        combineParams = function combineParams(params) {
      if (params === false) return '';
      var returner = ( '/') + LS.ld.reduce(params, function (urlParams, value, key) {
        return urlParams + ( (urlParams === '' ? '' : '/') + key + '/' + value);
      }, '');
      return returner;
    },
        filterUrl = function filterUrl(url) {
      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      var forceGet = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      if (url.charAt(0) == '/') url = url.substring(1);
      var baseUrl = getBasedUrls || forceGet ? '?r=admin/' : '/admin/';
      var containsIndex = /\/index.php\/?/.test(window.location.href);
      var returnUrl = window.LS.data.baseUrl + (containsIndex ? '/index.php' : '') + baseUrl + url + combineParams(params);
      return returnUrl;
    },
        _preparePath = function _preparePath(path) {
      if (typeof path === 'string') return path;
      return RegExp(path.join());
    },
        _prepareMethods = function _prepareMethods(tutorialObject) {

      tutorialObject.steps = LS.ld.map(tutorialObject.steps, function (step, i) {
        step.path = _preparePath(step.path);
        step.onNext = step.onNext ? eval(step.onNext) : undefined;
        step.onShow = step.onShow ? eval(step.onShow) : undefined;
        step.onShown = step.onShown ? eval(step.onShown) : undefined;
        step.onHide = step.onHide ? eval(step.onHide) : undefined;
        step.onHidden = step.onHidden ? eval(step.onHidden) : undefined; //if(window.debugState.backend) { console.ls.log(step); }

        return step;
      });
      tutorialObject.onShown = tutorialObject.onShown ? eval(tutorialObject.onShown) : null;
      tutorialObject.onEnd = tutorialObject.onEnd ? eval(tutorialObject.onEnd) : null;
      tutorialObject.onStart = tutorialObject.onStart ? eval(tutorialObject.onStart) : null;
      return tutorialObject;
    };

    return {
      get: function get(tourName) {
        return new Promise(function (resolve, reject) {
          $.ajax({
            url: filterUrl('/tutorial/sa/servertutorial'),
            data: {
              tutorialname: tourName,
              ajax: true
            },
            method: 'POST',
            success: function success(tutorialData) {
              var tutorialObject = _prepareMethods(tutorialData.tutorial);

              resolve(tutorialObject);
            },
            error: function error(_error) {
              reject(_error);
            }
          });
        });
      }
    };
  };

  var globalTourObject$1 = globalTourObject();

  var TourLibrary = function TourLibrary() {

    var _getIsTourActive = function _getIsTourActive() {
      var isTourActive = window.localStorage.getItem('lstutorial-is-tour-active') || false;
      return isTourActive;
    },
        _setTourActive = function _setTourActive(tourName) {
      window.localStorage.setItem('lstutorial-is-tour-active', tourName);
    },
        _setNoTourActive = function _setNoTourActive(tid) {
      window.localStorage.removeItem('lstutorial-is-tour-active');

      if (tid !== undefined) {
        $.post(LS.data.baseUrl + (LS.data.urlFormat == 'path' ? '/admin/tutorial/sa/triggerfinished/tid/' : '?r=admin/tutorial/sa/triggerfinished/tid/') + tid);
      }
    },
        clearActiveTour = function clearActiveTour() {
      if (_typeof(_actionActiveTour) === 'object' && _actionActiveTour !== null) {
        _actionActiveTour.end();
      }

      _setNoTourActive();
    },
        getCurrentStep = function getCurrentStep() {
      if (_getIsTourActive() !== false) {
        return globalTourObject$1.getCurrentStep();
      }
    },
        initTour = function initTour(tourName) {
      return new Promise(function (resolve, reject) {
        if (_getIsTourActive() !== false && _getIsTourActive() !== tourName) {
          clearActiveTour();
          reject();
        }

        globalTourObject$1.get(tourName).then(function (tourObject) {
          _setTourActive(tourName);

          tourObject.onEnd = function () {
            _setNoTourActive(tourObject.tid);
          };

          tourObject.debug = window.debugState.backend;
          tourObject.framework = "bootstrap3";
          _actionActiveTour = new Tour(tourObject);
          // window.addEventListener('resize', function () {
          //  _actionActiveTour.redraw();
          // });
          resolve(_actionActiveTour);
        }, console.ls.err);
      });
    },
        triggerTourStart = function triggerTourStart(tutorialName) {
      clearActiveTour();
      initTour(tutorialName).then(function (startedTutorial) {
        if (startedTutorial.ended()) startedTutorial.restart();else startedTutorial.start(true);
      }, function (err) {
        console.ls.log('Couldn\'t be loaded!');
        console.ls.error(err);
      });
    };

    var _activeTour = _getIsTourActive();

    var _actionActiveTour = null;

    if (_activeTour !== false && typeof _actionActiveTour !== 'function') {
      initTour(_activeTour).then(function (startedTutorial) {
        if (startedTutorial.ended()) {
          startedTutorial.restart();
        } else {
          setTimeout(function () {
            startedTutorial.start();
          }, 1);
        }
      }, function (err) {
        console.ls.log('Couldn\'t be loaded!');
        console.ls.error(err);
      });
    }

    return {
      triggerTourStart: triggerTourStart,
      clearActiveTour: clearActiveTour,
      initTour: initTour,
      _actionActiveTour: _actionActiveTour,
      getCurrentStep: getCurrentStep
    };
  };

  $(document).on('ready pjax:scriptcomplete', function () {
    if (typeof window.tourLibrary === 'undefined') {
      window.tourLibrary = TourLibrary();
    }

    $('#selector__welcome-modal--starttour').on('click', function (e) {
      $(e.currentTarget).closest('.modal').modal('hide');
      window.tourLibrary.triggerTourStart('firstStartTour');
    });
  });

}));
